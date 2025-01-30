<?php

namespace App\Controller;

use App\Form\SearchShopsByCityType;
use App\Service\CgoService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class DistanceController extends AbstractController
{
    public function __construct(
        private CgoService $cgoService,
    ){}

    #[Route('/search-distance', name: 'app_search_distance')]
    public function searchDistance(Request $request): Response
    {
        // $user = $this->security->getUser();
        // $departmentsCollection = $user->getMyDepartments();
        // $myDepartments = $departmentsCollection->toArray();


        // //si aucun departements
        // if($myDepartments == null){
        //     $this->addFlash('warning', 'Le CGO ne semble pas avoir de départements rattachés...');
        //     return $this->redirectToRoute('admin', [], Response::HTTP_SEE_OTHER);
        // }

        // $departments = [];

        // foreach($myDepartments as $myDepartment){
        //     array_push($departments, $myDepartment->getDepartment());
        // }

        // //on cherche les centres rattachés au cgo
        // $myShops = $user->getMyShops();

        // //si aucun centre en ligne ou créé
        // if($myShops == null){
        //     $this->addFlash('warning', 'Le CGO ne semble pas avoir de centre rattachés...');
        //     return $this->redirectToRoute('admin', [], Response::HTTP_SEE_OTHER);
        // }

        $formSearchByCity = $this->createForm(SearchShopsByCityType::class, null, []);
        $formSearchByCity->handleRequest($request);

        // $formSearchByGpsPoints = $this->createForm(SearchDistancesByGpsPointsType::class, null);
        // $formSearchByGpsPoints->handleRequest($request);

        if($formSearchByCity->isSubmitted() && $formSearchByCity->isValid()) {
            $cityOfIntervention = $formSearchByCity->get('city')->getData();


            $datas = $this->cgoService->getDistances($cityOfIntervention);
        }

        // if($formSearchByGpsPoints->isSubmitted() && $formSearchByGpsPoints->isValid()){

        //     $interventionLongitude = $formSearchByGpsPoints->get('interventionLongitude')->getData();
        //     $interventionLatitude = $formSearchByGpsPoints->get('interventionLatitude')->getData() ;

        //     $datas = $this->cgoService->distanceLogic($myShops, $interventionLongitude,$interventionLatitude);

        // } 

        return $this->render('site/distance/distance.html.twig', [
            'formSearchByCity' => $formSearchByCity->createView(),
            // 'formSearchByGpsPoints' => $formSearchByGpsPoints->createView(),
            'datas' => $datas ?? null,
            'title' => 'Recherche de distance'
        ]);
    }
}
