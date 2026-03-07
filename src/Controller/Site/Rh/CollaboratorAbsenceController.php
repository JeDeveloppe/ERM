<?php

namespace App\Controller\Site\Rh;

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

        $user = $this->getUser();

        // --- GESTION DU FORMULAIRE D'AJOUT ---
        $absence = new CollaboratorAbsence();
        $form = $this->createForm(CollaboratorAbsenceType::class, $absence, [
            'user' => $user
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $absence->setOwner($this->getUser());
            $entityManager->persist($absence);
            $entityManager->flush();

            $this->addFlash('success', 'L\'absence a été enregistrée et le décompte mis à jour.');
            return $this->redirectToRoute('app_collaborator_absence_index');
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addFlash('danger', 'Une erreur est survenue lors de l\'enregistrement de l\'absence.');
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
            // On récupère l'entité Collaborator (via la première absence du groupe)
            $collaborator = $absences[0]->getCollaborator();

            // 1. APPEL AU SERVICE : On récupère le tableau global des compteurs (CP, RTT, etc.)
            $stats = $rhService->getFullDecompte($collaborator, $absences);

            // 2. On récupère les durées individuelles pour l'affichage de la liste
            $durations = $rhService->calculateGroupDurations($absences);

            $items = [];
            foreach ($absences as $a) {
                $items[] = [
                    'absence'  => $a,
                    'duration' => $durations[$a->getId()] ?? 0
                ];
            }

            // 3. On assemble tout dans finalData
            $finalData[$collabName] = [
                'entries'   => $items,
                'stats'     => $stats, // Contient CP, RTT, RECOVERY, SENIORITY (initial, consommé, restant)
                'saturdays' => $rhService->calculateSaturdaysForProgress($absences)
            ];
        }

        ksort($finalData);
        
        return $this->render('site/collaborator/absence/index.html.twig', [
            'finalData' => $finalData,
            'collaborators' => $collaboratorRepository->findBy(['owner' => $user]),
            'absenceForm'        => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, CollaboratorAbsence $absence, EntityManagerInterface $entityManager): Response
    {
        if ($absence->getOwner() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(CollaboratorAbsenceType::class, $absence, [
            'user' => $this->getUser()
        ]);
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
