<?php

namespace App\Service;

use App\Entity\CollaboratorAbsence;
use App\Repository\CollaboratorAbsenceRepository;

class CollaboratorService
{
    public function __construct(
        private CollaboratorAbsenceRepository $absenceRepository
    ) {}

    /**
     * Utilisé pour le Calendrier : Calcule les samedis déjà consommés AVANT le mois affiché.
     */
    public function getSaturdaysQuotaForCollaborators(array $collaborators, int $year, \DateTimeInterface $startDate): array
    {
        $quotas = [];
        $startOfYear = new \DateTime("$year-01-01");

        foreach ($collaborators as $collab) {
            // Récupère les CP entre le 1er janvier et le début du mois sélectionné
            $absencesPrecedentes = $this->absenceRepository->createQueryBuilder('a')
                ->where('a.collaborator = :collab')
                ->andWhere('a.type = :type')
                ->andWhere('a.startDate >= :startOfYear')
                ->andWhere('a.startDate < :startOfMonth')
                ->setParameter('collab', $collab)
                ->setParameter('type', 'CP')
                ->setParameter('startOfYear', $startOfYear)
                ->setParameter('startOfMonth', $startDate)
                ->orderBy('a.startDate', 'ASC')
                ->getQuery()
                ->getResult();

            $count = 0;
            foreach ($absencesPrecedentes as $absence) {
                if ($count >= 5) break; // Optimisation : on s'arrête si le quota est plein

                $period = new \DatePeriod(
                    $absence->getStartDate(),
                    new \DateInterval('P1D'),
                    (clone $absence->getEndDate())->modify('+1 day')
                );

                foreach ($period as $date) {
                    if ($date->format('N') == 6) { // Samedi
                        $count++;
                    }
                }
            }
            $quotas[$collab->getId()] = min($count, 5);
        }

        return $quotas;
    }

    /**
     * Utilisé pour l'historique/liste : Calcule les durées réelles pour un groupe d'absences
     * en respectant la chronologie du quota de 5 samedis.
     */
    public function calculateGroupDurations(array $absences): array
    {
        $durations = [];
        $saturdayQuota = 0;

        foreach ($absences as $absence) {
            if (!$absence->getStartDate() || !$absence->getEndDate()) {
                $durations[$absence->getId()] = 0;
                continue;
            }

            $end = (clone $absence->getEndDate())->modify('+1 day');
            $period = new \DatePeriod($absence->getStartDate(), new \DateInterval('P1D'), $end);

            $daysToDeduct = 0;
            foreach ($period as $date) {
                $dayOfWeek = (int)$date->format('N');

                if ($dayOfWeek === 7) continue; // On ignore le dimanche

                if ($dayOfWeek === 6) { // Samedi
                    if ($absence->getType() === 'CP' && $saturdayQuota < 5) {
                        $daysToDeduct++;
                        $saturdayQuota++;
                    }
                } else {
                    $daysToDeduct++;
                }
            }
            $durations[$absence->getId()] = $daysToDeduct;
        }

        return $durations;
    }

    /**
     * Calcule la déduction exacte d'une absence unique (si besoin hors groupe).
     */
    public function calculateDeduction(CollaboratorAbsence $absence, int $alreadyCountedSaturdays = 0): array
    {
        $daysToDeduct = 0;
        $saturdaysAddedToQuota = 0;

        $period = new \DatePeriod(
            $absence->getStartDate(),
            new \DateInterval('P1D'),
            (clone $absence->getEndDate())->modify('+1 day')
        );

        foreach ($period as $date) {
            $dayOfWeek = (int)$date->format('N');

            if ($dayOfWeek !== 7) { 
                if ($dayOfWeek === 6) { 
                    if ($absence->getType() === 'CP' && ($alreadyCountedSaturdays + $saturdaysAddedToQuota < 5)) {
                        $daysToDeduct++;
                        $saturdaysAddedToQuota++;
                    }
                } else {
                    $daysToDeduct++;
                }
            }
        }

        return [
            'daysToDeduct' => $daysToDeduct,
            'saturdaysAddedToQuota' => $saturdaysAddedToQuota
        ];
    }

    /**
     * Calcule le nombre de samedis impactés pour l'affichage de la jauge UX
     * (Basé sur le calendrier réel des absences fournies)
     */
    public function calculateSaturdaysForProgress(array $absences): int
    {
        $saturdaysImpacted = 0;

        foreach ($absences as $a) {
            // Seuls les Congés Payés (CP) comptent pour cette règle
            if ($a->getType() !== 'CP' || !$a->getStartDate() || !$a->getEndDate()) {
                continue;
            }

            $start = $a->getStartDate();
            $end = (clone $a->getEndDate())->modify('+1 day');

            if ($end > $start) {
                $period = new \DatePeriod($start, new \DateInterval('P1D'), $end);
                foreach ($period as $date) {
                    // On compte chaque samedi (Jour 6 de la semaine)
                    if ($date->format('N') == 6) {
                        $saturdaysImpacted++;
                    }
                }
            }
        }

        // On plafonne à 5 pour la jauge, car au-delà, ils ne sont plus décomptés
        return min($saturdaysImpacted, 5);
    }
}