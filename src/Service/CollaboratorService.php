<?php

namespace App\Service;

use App\Entity\Collaborator;
use App\Repository\CollaboratorAbsenceRepository;

class CollaboratorService
{
    public function __construct(
        private CollaboratorAbsenceRepository $absenceRepository
    ) {}

    /**
     * Retourne la liste des jours fériés pour une année donnée.
     */
    public function getHolidays(int $year): array
    {
        $holidays = [
            "$year-01-01",
            "$year-05-01",
            "$year-05-08",
            "$year-07-14",
            "$year-08-15",
            "$year-11-01",
            "$year-11-11",
            "$year-12-25",
        ];

        $easterTimestamp = easter_date($year);
        $holidays[] = date('Y-m-d', strtotime('+1 day', $easterTimestamp));  // Lundi de Pâques
        $holidays[] = date('Y-m-d', strtotime('+39 days', $easterTimestamp)); // Ascension
        $holidays[] = date('Y-m-d', strtotime('+50 days', $easterTimestamp)); // Lundi de Pentecôte

        return $holidays;
    }

    /**
     * Vérifie si une date précise est un jour férié.
     */
    private function isHoliday(\DateTimeInterface $date): bool
    {
        $yearHolidays = $this->getHolidays((int)$date->format('Y'));
        return in_array($date->format('Y-m-d'), $yearHolidays);
    }

    /**
     * Calcule les durées réelles en excluant Dimanches ET Jours Fériés.
     * Pour les CP, on n'inclut le samedi que s'il n'est pas férié et dans le quota de 5.
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

                // 1. Si c'est un dimanche ou un jour férié, on ne décompte JAMAIS
                if ($dayOfWeek === 7 || $this->isHoliday($date)) {
                    continue;
                }

                // 2. Gestion spécifique du Samedi (Jour 6)
                if ($dayOfWeek === 6) {
                    if ($absence->getType() === 'CP' && $saturdayQuota < 5) {
                        $daysToDeduct++;
                        $saturdayQuota++;
                    }
                } else {
                    // 3. Jour de semaine classique (Lun-Ven) non férié
                    $daysToDeduct++;
                }
            }
            $durations[$absence->getId()] = $daysToDeduct;
        }

        return $durations;
    }

    /**
     * Calcule le quota de samedis pour le calendrier, en ignorant les samedis fériés.
     */
    public function getSaturdaysQuotaForCollaborators(array $collaborators, int $year, \DateTimeInterface $startDate): array
    {
        $quotas = [];
        $startOfYear = new \DateTime("$year-01-01");

        foreach ($collaborators as $collab) {
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
                if ($count >= 5) break;

                $period = new \DatePeriod(
                    $absence->getStartDate(),
                    new \DateInterval('P1D'),
                    (clone $absence->getEndDate())->modify('+1 day')
                );

                foreach ($period as $date) {
                    // On ne compte le samedi que s'il n'est pas férié
                    if ($date->format('N') == 6 && !$this->isHoliday($date)) {
                        $count++;
                    }
                }
            }
            $quotas[$collab->getId()] = min($count, 5);
        }

        return $quotas;
    }

    public function calculateSaturdaysForProgress(array $absences): int
    {
        $saturdaysImpacted = 0;

        foreach ($absences as $a) {
            if ($a->getType() !== 'CP' || !$a->getStartDate() || !$a->getEndDate()) continue;

            $period = new \DatePeriod($a->getStartDate(), new \DateInterval('P1D'), (clone $a->getEndDate())->modify('+1 day'));
            foreach ($period as $date) {
                // Un samedi férié ne doit pas impacter la jauge des 5 samedis décomptés
                if ($date->format('N') == 6 && !$this->isHoliday($date)) {
                    $saturdaysImpacted++;
                }
            }
        }
        return min($saturdaysImpacted, 5);
    }

    public function getFullDecompte(Collaborator $collaborator, array $absences): array
    {
        $durations = $this->calculateGroupDurations($absences);
        $totalCpInitial = $collaborator->getVacationInitial() + $collaborator->getSeniorityLeaveInitial();

        $stats = [
            'CONGÉS PAYÉS' => ['total' => $totalCpInitial, 'pris' => 0, 'color' => 'primary'],
            'RTT' => ['total' => $collaborator->getRttInitial(), 'pris' => 0, 'color' => 'warning'],
            'JTT' => ['total' => $collaborator->getRecoveryBalanceInitial(), 'pris' => 0, 'color' => 'info'],
        ];

        foreach ($absences as $absence) {
            $type = strtoupper($absence->getType());
            $duration = $durations[$absence->getId()] ?? 0;

            if ($type === 'CP' || $type === 'SENIORITY') {
                $stats['CONGÉS PAYÉS']['pris'] += $duration;
            } elseif (isset($stats[$type])) {
                $stats[$type]['pris'] += $duration;
            }
        }

        foreach ($stats as $type => $data) {
            $stats[$type]['restant'] = $data['total'] - $data['pris'];
            $stats[$type]['percent'] = $data['total'] > 0 ? ($data['pris'] / $data['total'] * 100) : 0;
        }

        return $stats;
    }
}
