<?php

namespace App\Controller\Site;

use App\Entity\CollaboratorAbsence;
use App\Form\CollaboratorAbsenceType;
use App\Repository\CollaboratorAbsenceRepository;
use App\Repository\CollaboratorRepository;
use App\Service\CollaboratorService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/collaborateurs/absences', name: 'app_collaborator_absence_')]
class CollaboratorAbsenceController extends AbstractController
{
    #[Route('/', name: 'index', methods: ['GET', 'POST'])]
    public function index(
        Request $request,
        EntityManagerInterface $entityManager,
        CollaboratorAbsenceRepository $absenceRepository,
        CollaboratorRepository $collaboratorRepository,
        CollaboratorService $rhService
    ): Response {
        // --- GESTION DU FORMULAIRE D'AJOUT ---
        $absence = new CollaboratorAbsence();
        $form = $this->createForm(CollaboratorAbsenceType::class, $absence);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $absence->setOwner($this->getUser());
            $entityManager->persist($absence);
            $entityManager->flush();

            $this->addFlash('success', 'L\'absence a été enregistrée et le décompte mis à jour.');
            return $this->redirectToRoute('app_collaborator_absence_index');
        }

        // --- LOGIQUE DE CALCUL ET GROUPAGE ---
        
        // 1. Récupération des absences (le Repository doit trier par date début ASC)
        $absencesRaw = $absenceRepository->findAbsencesForCurrentYear($this->getUser());

        // 2. Groupage par collaborateur
        $groupedAbsences = [];
        foreach ($absencesRaw as $a) {
            $collabName = $a->getCollaborator()->getFirstName() . ' ' . $a->getCollaborator()->getLastName();
            $groupedAbsences[$collabName][] = $a;
        }

        // 3. Transformation des données pour l'UX
        $finalData = [];
        foreach ($groupedAbsences as $collabName => $absences) {
            // APPEL AU SERVICE : Calcule les durées réelles (Règle des 5 samedis incluse)
            $durations = $rhService->calculateGroupDurations($absences);
            
            $totalYearDecompte = 0;
            $items = [];

            foreach ($absences as $a) {
                $duration = $durations[$a->getId()] ?? 0;
                $totalYearDecompte += $duration;

                $items[] = [
                    'absence'  => $a,
                    'duration' => $duration
                ];
            }

            $finalData[$collabName] = [
                'entries'   => $items,
                'total'     => $totalYearDecompte,
                'saturdays' => $rhService->calculateSaturdaysForProgress($absences, $durations)
            ];
        }

        return $this->render('site/collaborator/absence/index.html.twig', [
            'finalData' => $finalData,
            'collaborators' => $collaboratorRepository->findAll(),
            'absenceForm'        => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, CollaboratorAbsence $absence, EntityManagerInterface $entityManager): Response
    {
        if ($absence->getOwner() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(CollaboratorAbsenceType::class, $absence);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Absence mise à jour.');

            return $this->redirectToRoute('app_collaborator_absence_index');
        }

        return $this->render('site/collaborator/absence/edit.html.twig', [
            'absence' => $absence,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/delete', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, CollaboratorAbsence $absence, EntityManagerInterface $entityManager): Response
    {
        if ($absence->getOwner() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        if ($this->isCsrfTokenValid('delete' . $absence->getId(), $request->request->get('_token'))) {
            $entityManager->remove($absence);
            $entityManager->flush();
            $this->addFlash('danger', 'Absence supprimée.');
        }

        return $this->redirectToRoute('app_collaborator_absence_index');
    }
}
