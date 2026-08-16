<?php

namespace App\Controller\Site\Prime;

use App\Form\PrimeForPeopleType;
use App\Repository\PrimelevelRepository;
use App\Service\PrimeLevelService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PrimeController extends AbstractController
{
    private int $primeMax;

    public function __construct(
        private PrimeLevelService $primeLevelService,
        private PrimelevelRepository $primelevelRepository,

    )
    {
        $this->primeMax = 600;
    }

    #[Route('/prime/calcul-prime-mensuelle', name: 'app_prime')]
public function prime(Request $request): Response
{
    $versionsAvailable = $this->primelevelRepository->findAllVersions();
    
    // 1. On définit les versions par défaut (les 2 dernières)
    $vKeys = array_keys($versionsAvailable);
    $defaultNew = $vKeys[0] ?? null;   // La plus récente (ex: 2026_1)
    $defaultOld = $vKeys[1] ?? $vKeys[0] ?? null; // La précédente (ex: 2025)
    $bonus = 0;
    $deltaReel = 0;

    // 2. Initialisation des données pour l'affichage initial (Tableaux)
    $primeLevelsOld = $this->primelevelRepository->findBy(['version' => $defaultOld], ['start' => 'ASC']);
    $primeLevelsNew = $this->primelevelRepository->findBy(['version' => $defaultNew], ['start' => 'ASC']);

    // 3. On pré-remplit le formulaire avec ces versions
    $form = $this->createForm(PrimeForPeopleType::class, [
        'versionOld' => $defaultOld,
        'versionNew' => $defaultNew,
    ], ['versions' => $versionsAvailable]);

    // Initialisation des autres variables à null
    $primeByPersonOld = $primeByPersonNew = null;
    $primeLevelOld = $primeLevelNew = null;
    $nextLevel = $infosForNextLevel = null;
    $divider = null;

    if($request->isMethod('POST')) {
        $form->handleRequest($request); // Plus propre que submit() manuel

        if($form->isSubmitted() && $form->isValid()) {
            // On récupère les versions choisies par l'utilisateur
            $vOld = $form->get('versionOld')->getData();
            $vNew = $form->get('versionNew')->getData();
            $bonus = $form->get('compensationBonus')->getData() ?? 0;

            // On met à jour les tableaux avec les choix du formulaire
            $primeLevelsOld = $this->primelevelRepository->findBy(['version' => $vOld], ['start' => 'ASC']);
            $primeLevelsNew = $this->primelevelRepository->findBy(['version' => $vNew], ['start' => 'ASC']);

            $divider = $form->get('divider')->getData();
            $fullPs = $form->get('fullPs')->getData();
            $psByPersonZone = $this->primeLevelService->getPsByPerson($fullPs, $divider);

            // Calcul de la différence réelle incluant le bonus
            $totalNewWithBonus = $primeByPersonNew + $bonus;
            $deltaReel = $totalNewWithBonus - $primeByPersonOld;

            // Calculs
            $primeLevelOld = $this->primeLevelService->getPrimeLevel($psByPersonZone, $vOld);
            $primeByPersonOld = $primeLevelOld ? $this->primeLevelService->calculateValuePrimeByPerson($psByPersonZone, $primeLevelOld, $this->primeMax) : 0;

            $primeLevelNew = $this->primeLevelService->getPrimeLevel($psByPersonZone, $vNew);
            $primeByPersonNew = $primeLevelNew ? $this->primeLevelService->calculateValuePrimeByPerson($psByPersonZone, $primeLevelNew, $this->primeMax) : 0;

            if($primeByPersonNew < $this->primeMax){
                $nextLevel = $this->primeLevelService->getPrimeLevel($primeLevelNew->getEnd(), $vNew);
                if ($nextLevel) {
                    $infosForNextLevel = $this->primeLevelService->returnInfosForNextLevel($divider, $fullPs, $nextLevel, $this->primeMax);
                }
            }
        }
    }

    return $this->render('site/prime/prime.html.twig', [
        'title' => 'Calcul prime mensuelle',
        'form' => $form->createView(),
        'primeByPersonOld' => $primeByPersonOld,
        'primeByPersonNew' => $primeByPersonNew,
        'primeLevelOld' => $primeLevelOld,
        'primeLevelNew' => $primeLevelNew,
        'primeLevelsOld' => $primeLevelsOld,
        'primeLevelsNew' => $primeLevelsNew,
        'nextLevel' => $nextLevel,
        'infosForNextLevel' => $infosForNextLevel,
        'bonus' => $bonus ?? 0,
        'deltaReel' => $deltaReel,
        'divider' => $divider // Important pour le calcul des seuils dans le tableau
    ]);
}
}
