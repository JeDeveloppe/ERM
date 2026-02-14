<?php

namespace App\Controller\Site;

use App\Entity\Collaborator;
use App\Form\CollaboratorType;
use App\Repository\CollaboratorRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/collaborateurs', name: 'app_collaborator_')]
class CollaboratorController extends AbstractController
{
    #[Route('/', name: 'index', methods: ['GET', 'POST'])]
    public function index(
        Request $request,
        EntityManagerInterface $entityManager,
        CollaboratorRepository $collaboratorRepository
    ): Response {

        // 1. Récupération de la liste (uniquement ceux de l'utilisateur connecté)
        $collaborators = $collaboratorRepository->findBy(['owner' => $this->getUser()]);

        // 2. Gestion de la création d'un nouveau collaborateur
        $collaborator = new Collaborator();
        $form = $this->createForm(CollaboratorType::class, $collaborator);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // On définit l'utilisateur actuel comme propriétaire
            $collaborator->setOwner($this->getUser());

            $entityManager->persist($collaborator);
            $entityManager->flush();

            $this->addFlash('success', 'Collaborateur ajouté avec succès.');

            return $this->redirectToRoute('app_collaborator_index');
        }

 

        return $this->render('site/collaborator/index.html.twig', [
            'collaborators' => $collaborators,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Collaborator $collaborator, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CollaboratorType::class, $collaborator);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Compteurs mis à jour pour ' . $collaborator->getFirstName());

            return $this->redirectToRoute('app_collaborator_index');
        }

        return $this->render('site/collaborator/edit.html.twig', [
            'collaborator' => $collaborator,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/delete', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, Collaborator $collaborator, EntityManagerInterface $entityManager): Response
    {
        // Vérification de sécurité avec le jeton CSRF
        if ($this->isCsrfTokenValid('delete' . $collaborator->getId(), $request->request->get('_token'))) {
            $entityManager->remove($collaborator);
            $entityManager->flush();

            $this->addFlash('danger', 'Le collaborateur a été supprimé.');
        }

        return $this->redirectToRoute('app_collaborator_index');
    }
}
