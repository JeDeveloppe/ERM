<?php

namespace App\Service;

use App\Entity\ApiLog;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ApiLogService
{

    public function __construct(
        private EntityManagerInterface $em,
        private Security $security,
        private UrlGeneratorInterface $urlGenerator
    )
    {
    }
    
    public function saveApiLog(string $service, int $startTime, string $route, string $requestStatus, ?string $errorMessage = null): void
    {

        $user = $this->security->getUser();
        $endTime = microtime(true);
        $durationInSeconds = $endTime - $startTime;
        $endpoint = $this->urlGenerator->generate($route, [], UrlGeneratorInterface::ABSOLUTE_URL);

        $log = new ApiLog();
        $log
            ->setService($service)
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