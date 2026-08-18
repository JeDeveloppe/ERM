<?php

namespace App\Controller\Site;

use App\Service\MapsService;
use Symfony\UX\Map\Point;
use Symfony\UX\Map\Marker;
use Symfony\UX\Map\InfoWindow;
use App\Core\UxMap\CustomIcon as Icon;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Page vitrine publique (portfolio) : aucune donnée réelle, tout est fictif
 * et codé en dur ci-dessous - ne touche jamais la base de données ni les
 * repositories métier, pour garantir qu'aucune donnée client ne peut fuiter
 * par cette page accessible sans connexion.
 */
class DemoController extends AbstractController
{
    /**
     * Coordonnées de villes réelles (données géographiques publiques) associées
     * à des noms de centres et des personnes 100% fictifs.
     */
    private const CENTERS = [
        ['name' => 'Atelier Alpha Nord',     'city' => 'Lille',             'lat' => 50.6292, 'lng' => 3.0573,  'class' => 'VI'],
        ['name' => 'Garage Central Capitale','city' => 'Paris',             'lat' => 48.8566, 'lng' => 2.3522,  'class' => 'MX'],
        ['name' => 'Point Service Rhône',    'city' => 'Lyon',              'lat' => 45.7640, 'lng' => 4.8357,  'class' => 'VI'],
        ['name' => 'Station Sud Méditerranée','city' => 'Marseille',        'lat' => 43.2965, 'lng' => 5.3698,  'class' => 'VL'],
        ['name' => 'Relais Ouest Atlantique','city' => 'Nantes',            'lat' => 47.2184, 'lng' => -1.5536, 'class' => 'VL'],
        ['name' => 'Comptoir Est Alsace',    'city' => 'Strasbourg',        'lat' => 48.5734, 'lng' => 7.7521,  'class' => 'MX'],
        ['name' => 'Atelier Sud-Ouest',      'city' => 'Toulouse',          'lat' => 43.6047, 'lng' => 1.4442,  'class' => 'VI'],
        ['name' => 'Dépôt Gironde',          'city' => 'Bordeaux',          'lat' => 44.8378, 'lng' => -0.5792, 'class' => 'VL'],
        ['name' => 'Station Bretagne',       'city' => 'Rennes',            'lat' => 48.1173, 'lng' => -1.6778, 'class' => 'VI'],
        ['name' => 'Point Service Auvergne', 'city' => 'Clermont-Ferrand',  'lat' => 45.7772, 'lng' => 3.0870,  'class' => 'MX'],
    ];

    /**
     * Villes de référence proposées pour la recherche (peuvent être différentes
     * des villes où se trouvent les centres démo, comme dans l'app réelle).
     */
    private const SEARCH_CITIES = [
        'Paris' => [48.8566, 2.3522],
        'Lyon' => [45.7640, 4.8357],
        'Marseille' => [43.2965, 5.3698],
        'Toulouse' => [43.6047, 1.4442],
        'Nice' => [43.7102, 7.2620],
        'Nantes' => [47.2184, -1.5536],
        'Strasbourg' => [48.5734, 7.7521],
        'Montpellier' => [43.6108, 3.8767],
        'Bordeaux' => [44.8378, -0.5792],
        'Lille' => [50.6292, 3.0573],
        'Rennes' => [48.1173, -1.6778],
        'Reims' => [49.2583, 4.0317],
        'Dijon' => [47.3220, 5.0415],
        'Angers' => [47.4784, -0.5632],
        'Clermont-Ferrand' => [45.7772, 3.0870],
    ];

    private const CLASS_COLORS = [
        'VI' => '#0d6efd',
        'VL' => '#198754',
        'MX' => '#fd7e14',
    ];

    public function __construct(private MapsService $mapsService)
    {
    }

    #[Route('/vitrine', name: 'app_demo_showcase', methods: ['GET'])]
    public function showcase(Request $request): Response
    {
        $searchCity = $request->query->get('ville');
        $results = null;

        if ($searchCity !== null && isset(self::SEARCH_CITIES[$searchCity])) {
            [$originLat, $originLng] = self::SEARCH_CITIES[$searchCity];

            $results = array_map(
                fn(array $center) => $center + [
                    'distance' => $this->haversineDistanceKm($originLat, $originLng, $center['lat'], $center['lng']),
                ],
                self::CENTERS
            );

            usort($results, fn($a, $b) => $a['distance'] <=> $b['distance']);
            $results = array_slice($results, 0, 5);
        }

        $map = $this->buildMap();

        return $this->render('site/demo/showcase.html.twig', [
            'title' => 'Démo publique - ERM Maps',
            'map' => $map,
            'searchCities' => array_keys(self::SEARCH_CITIES),
            'selectedCity' => $searchCity,
            'results' => $results,
        ]);
    }

    private function buildMap(): \Symfony\UX\Map\Map
    {
        $map = $this->mapsService->generationUxMapWithBaseOptions();

        foreach (self::CENTERS as $center) {
            $color = self::CLASS_COLORS[$center['class']] ?? '#6c757d';

            $map->addMarker(new Marker(
                position: new Point($center['lat'], $center['lng']),
                title: $center['name'],
                icon: Icon::ux('solar:garage-bold')->width(22)->height(22)->color($color),
                infoWindow: new InfoWindow(
                    headerContent: $center['name'],
                    content: '<span class="badge" style="background-color:' . $color . '">' . $center['class'] . '</span> ' . $center['city'],
                ),
                extra: ['markerColor' => $color],
            ));
        }

        return $map;
    }

    private function haversineDistanceKm(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadiusKm = 6371;

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return $earthRadiusKm * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}
