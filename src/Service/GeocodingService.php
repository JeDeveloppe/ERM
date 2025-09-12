<?php

namespace App\Service;

use App\Entity\ApiLog;
use App\Entity\City;
use App\Entity\Shop;
use App\Repository\CgoRepository;
use App\Repository\CityRepository;
use App\Repository\ManagerClassRepository;
use App\Repository\ShopRepository;
use App\Repository\ManagerRepository;
use App\Repository\ZoneErmRepository;
use App\Repository\RegionErmRepository;

use App\Repository\ShopClassRepository;
use App\Repository\TechnicianRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class GeocodingService
{
    private const NOMINATIM_API_URL = 'https://nominatim.openstreetmap.org/search.php';
    private const OSRM_API_URL = 'http://router.project-osrm.org/route/v1/driving/%s,%s;%s,%s';

    public function __construct(
        private EntityManagerInterface $em,
        private ShopClassRepository $shopClassRepository,
        private ZoneErmRepository $zoneErmRepository,
        private ManagerRepository $managerRepository,
        private CityRepository $cityRepository,
        private CgoRepository $cgoRepository,
        private RegionErmRepository $regionErmRepository,
        private ManagerClassRepository $managerClassRepository,
        private MapsService $mapsService,
        private ShopRepository $shopRepository,
        private HttpClientInterface $client,
        private TechnicianRepository $technicianRepository,
        private HttpClientInterface $httpClient,
        private Security $security
        ){
    }

    /**
     * @param City $cityOfIntervention
     * @param Shop $shop
     * @return array
     */
    public function getDistancesBeetweenDepannageAndShopWithTomTom(City $cityOfIntervention, Shop $shop): array
    {
        $interventionLatitude = $cityOfIntervention->getLatitude();
        $interventionLongitude = $cityOfIntervention->getLongitude();
        
        $shopLatitude = $shop->getCity()->getLatitude();
        $shopLongitude = $shop->getCity()->getLongitude();

        // 1. Construct the API endpoint cleanly
        $endpoint = sprintf(
            'https://api.tomtom.com/routing/1/calculateRoute/%s,%s:%s,%s/json',
            $interventionLatitude,
            $interventionLongitude,
            $shopLatitude,
            $shopLongitude
        );

        // 2. Use a try-catch block for robust error handling
        try {
            $response = $this->client->request('GET', $endpoint, [
                'query' => [
                    'key' => $_ENV['TOMTOM_API_KEY'],
                ],
            ]);

            // If the request was not successful, it will throw an exception
            $response->getStatusCode();

            $array_reponse = $response->toArray();
            
            // 3. Add checks for missing data
            if (!isset($array_reponse['routes'][0]['summary'])) {
                // You can log this or return a specific error structure
                // For this example, we return a default failure array
                return [
                    'shop'     => $shop,
                    'distance' => null,
                    'duration' => null,
                    'error'    => 'TomTom API response missing route summary.'
                ];
            }

            $summary = $array_reponse['routes'][0]['summary'];

            return [
                'shop'     => $shop,
                'distance' => $summary['lengthInMeters'],
                'duration' => $summary['travelTimeInSeconds'],
            ];

        } catch (\Exception $e) {
            // Log the error
            // $this->logger->error('TomTom API request failed: ' . $e->getMessage());
            
            // Return an array with error information
            return [
                'shop'     => $shop,
                'distance' => null,
                'duration' => null,
                'error'    => 'TomTom API request failed: ' . $e->getMessage()
            ];
        }
    }

    public function testDistance(City $cityOfIntervention, Shop $shop, string $unit, int $rayonOfIntervention):bool 
    {
        $lat1 = $cityOfIntervention->getLatitude();
        $lon1 = $cityOfIntervention->getLongitude();
        $lat2 = $shop->getCity()->getLatitude();
        $lon2 = $shop->getCity()->getLongitude();

        if (($lat1 == $lat2) && ($lon1 == $lon2)) {
            $rayon = 0;
        }
        else {
            $theta = $lon1 - $lon2;
            $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
            $dist = acos($dist);
            $dist = rad2deg($dist);
            $miles = $dist * 60 * 1.1515;
            $unit = strtoupper($unit);
        
            if($unit == "K") {
                $rayon = ($miles * 1.609344);
            } else if ($unit == "N") {
                $rayon = ($miles * 0.8684);
            } else {
                $rayon =  $miles;
            }
        }

        if($rayon <= $rayonOfIntervention){
            return true;
        }else{
            return false;
        }
    }

    public function getDistancesBeetweenDepannageAndShopWithOSRM(City $cityOfIntervention, Shop $shop): array
    {
        // OSRM utilise les coordonnées dans l'ordre : longitude, latitude
        $interventionLongitude = $cityOfIntervention->getLongitude();
        $interventionLatitude = $cityOfIntervention->getLatitude();
        
        $shopLongitude = $shop->getCity()->getLongitude();
        $shopLatitude = $shop->getCity()->getLatitude();

        // 1. Construct the API endpoint
        $endpoint = sprintf(
            self::OSRM_API_URL,
            $interventionLongitude,
            $interventionLatitude,
            $shopLongitude,
            $shopLatitude
        );

        // Démarrer un chronomètre pour la durée de la requête
        $startTime = microtime(true);
        $requestStatus = 'unknown';
        $errorMessage = null;

        try {
            $response = $this->client->request('GET', $endpoint);
            $requestStatus = $response->getStatusCode();
            $array_reponse = $response->toArray();
            
            // La gestion des erreurs reste la même
            if (!isset($array_reponse['code']) || $array_reponse['code'] !== 'Ok') {
                $requestStatus = 'error';
                $errorMessage = $array_reponse['message'] ?? 'Unknown OSRM error';

                return [
                    'shop'     => $shop,
                    'distance' => null,
                    'duration' => null,
                    'error'    => $errorMessage
                ];
            }

            if (!isset($array_reponse['routes'][0]['duration'])) {
                $requestStatus = 'error';
                $errorMessage = 'OSRM API response missing route data.';

                return [
                    'shop'     => $shop,
                    'distance' => null,
                    'duration' => null,
                    'error'    => $errorMessage
                ];
            }

            $route = $array_reponse['routes'][0];

            $requestStatus = 'success';
            return [
                'shop'     => $shop,
                'distance' => $route['distance'],
                'duration' => $route['duration'],
            ];

        } catch (\Exception $e) {
            $requestStatus = 'failed';
            $errorMessage = $e->getMessage();
            
            return [
                'shop'     => $shop,
                'distance' => null,
                'duration' => null,
                'error'    => 'OSRM API request failed: ' . $e->getMessage()
            ];
        } finally {

            $user = $this->security->getUser();

            $endTime = microtime(true);
            $durationInSeconds = $endTime - $startTime;

            $log = new ApiLog();
            $log
                ->setService('OSRM')
                ->setEndPoint($endpoint)
                ->setStatus($requestStatus)
                ->setErrorMessage($errorMessage)
                ->setUser($user)
                ->setLoggedAt(new \DateTimeImmutable('now', new \DateTimeZone('Europe/Paris')))
                ->setDuration($durationInSeconds);
            $this->em->persist($log);
            $this->em->flush();
        }
    }

}