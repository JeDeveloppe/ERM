<?php

namespace App\Controller\Site;

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
        // 1. Gestion des dates
        $month = (int) ($request->query->get('month') ?? date('m'));
        $year  = (int) ($request->query->get('year') ?? date('Y'));
        if ($month < 1 || $month > 12) { $month = (int)date('m'); }

        $startDate = new \DateTime("$year-$month-01");
        $endDate = (clone $startDate)->modify('last day of this month');
        
        // 2. Génération des jours pour les colonnes Twig
        $daysInMonth = [];
        $period = new \DatePeriod($startDate, new \DateInterval('P1D'), (clone $endDate)->modify('+1 day'));
        foreach ($period as $date) {
            $daysInMonth[] = clone $date;
        }

        // 3. Récupération des collaborateurs
        $collaborators = $collabRepo->findBy(['owner' => $this->getUser()]);

        // --- 4. CALCUL MASSIF DES QUOTAS (1 SEULE REQUÊTE SQL) ---
        // On récupère les quotas de samedis consommés AVANT le début du mois affiché
        $quotasSamedis = $collabService->getSaturdaysQuotaForCollaborators(
            $collaborators, 
            $year, 
            $startDate
        );

        // On injecte les quotas dans les objets collaborateurs
        foreach ($collaborators as $collaborator) {
            $collaborator->saturdaysTaken = $quotasSamedis[$collaborator->getId()] ?? 0;
        }

        // 5. Récupération des absences à afficher sur le calendrier
        $absences = $absenceRepo->findAbsencesByMonth(
            $this->getUser(), 
            $startDate, 
            $endDate
        );

        return $this->render('site/collaborator/calendar/index.html.twig', [
            'collaborators' => $collaborators,
            'absences'      => $absences,
            'daysInMonth'   => $daysInMonth,
            'currentMonth'  => $startDate,
            'prevMonth'     => (clone $startDate)->modify('-1 month'),
            'nextMonth'     => (clone $startDate)->modify('+1 month'),
        ]);
    }
}