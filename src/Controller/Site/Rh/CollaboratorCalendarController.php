<?php

namespace App\Controller\Site\Rh;

use App\Repository\CollaboratorRepository;
use App\Repository\CollaboratorAbsenceRepository;
use App\Service\CollaboratorService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/collaborateurs', name: 'app_collaborator_calendar_')]
class CollaboratorCalendarController extends AbstractController
{
    #[Route('/calendrier', name: 'index')]
    public function index(
        Request $request, 
        CollaboratorRepository $collabRepo,
        CollaboratorAbsenceRepository $absenceRepo,
        CollaboratorService $collabService 
    ): Response {
        // 1. Gestion des dates (Mois et Année)
        $month = (int) ($request->query->get('month') ?? date('m'));
        $year  = (int) ($request->query->get('year') ?? date('Y'));
        
        // Sécurité sur le mois
        if ($month < 1 || $month > 12) { 
            $month = (int)date('m'); 
        }

        $startDate = new \DateTime("$year-$month-01");
        $endDate = (clone $startDate)->modify('last day of this month');
        
        // 2. Génération de la liste des jours pour la grille du calendrier
        $daysInMonth = [];
        $period = new \DatePeriod(
            $startDate, 
            new \DateInterval('P1D'), 
            (clone $endDate)->modify('+1 day')
        );
        
        foreach ($period as $date) {
            $daysInMonth[] = clone $date;
        }

        // 3. Récupération des collaborateurs appartenant à l'utilisateur connecté
        $collaborators = $collabRepo->findBy(['owner' => $this->getUser()]);

        // 4. Récupération des jours fériés via le service (pour affichage et calculs)
        $holidays = $collabService->getHolidays($year);

        // 5. CALCUL DES QUOTAS DE SAMEDIS (Déjà consommés avant ce mois)
        // La méthode du service prend désormais en compte les samedis fériés
        $quotasSamedis = $collabService->getSaturdaysQuotaForCollaborators(
            $collaborators, 
            $year, 
            $startDate
        );

        // Injection du quota dans chaque objet collaborateur pour Twig
        foreach ($collaborators as $collaborator) {
            $collaborator->saturdaysTaken = $quotasSamedis[$collaborator->getId()] ?? 0;
        }

        // 6. Récupération des absences de tous les collaborateurs pour la période donnée
        $absences = $absenceRepo->findAbsencesByMonth(
            $this->getUser(), 
            $startDate, 
            $endDate
        );

        // 7. Rendu de la vue avec toutes les variables nécessaires
        return $this->render('site/collaborator/calendar/index.html.twig', [
            'collaborators' => $collaborators,
            'absences'      => $absences,
            'daysInMonth'   => $daysInMonth,
            'holidays'      => $holidays, // Variable cruciale pour l'affichage Twig
            'currentMonth'  => $startDate,
            'prevMonth'     => (clone $startDate)->modify('-1 month'),
            'nextMonth'     => (clone $startDate)->modify('+1 month'),
        ]);
    }
}