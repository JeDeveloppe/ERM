<?php

namespace App\Controller\Site;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ApiController extends AbstractController
{
    #[Route('/api/cities', name: 'api_cities', methods: ['GET'])]
        public function __invoke(Request $request, HttpClientInterface $httpClient): JsonResponse
    {
        // 1. Récupère le terme de recherche du champ d'autocomplétion
        $query = $request->query->get('query');

        if (!$query) {
            return $this->json([]);
        }

        // 2. Fait une requête vers l'API externe en demandant les coordonnées
        try {
            $response = $httpClient->request('GET', 'https://geo.api.gouv.fr/communes', [
                'query' => [
                    'nom' => $query,
                    'fields' => 'codesPostaux,centre', // Ajout du champ 'centre' pour les coordonnées GPS
                    'limit' => 10,
                    'format' => 'json'
                ]
            ]);

            $cities = $response->toArray();
        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Erreur lors de la récupération des données de l\'API.'
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }

        // 3. Transforme les données de l'API dans le format attendu
        $results = array_map(function ($city) {
            $postalCode = isset($city['codesPostaux'][0]) ? $city['codesPostaux'][0] : '';
            $coordinates = isset($city['centre']['coordinates']) ? $city['centre']['coordinates'] : [0, 0];

            // La valeur du champ sera une chaîne JSON encodée contenant toutes les infos
            $value = json_encode([
                'name' => $city['nom'],
                'postalCode' => $postalCode,
                'coordinates' => $coordinates,
            ]);

            // Le texte affiché à l'utilisateur reste lisible
            $text = $city['nom'] . ' (' . $postalCode . ')';

            return [
                'value' => $value,
                'text' => $text,
            ];
        }, $cities);

        // 4. Renvoie les résultats au format JSON
        return $this->json($results);
    }
}
