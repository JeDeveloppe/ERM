<?php

namespace App\Service;

use App\Entity\Shop;
use App\Entity\Person;
use App\Entity\RoleErm;
use App\Repository\CgoRepository;
use App\Repository\RoleErmRepository;
use App\Repository\ShopRepository;
use App\Repository\TechnicianFonctionRepository;
use App\Repository\TechnicianFormationsRepository;
use App\Repository\PersonRepository;
use App\Repository\TechnicianVehicleRepository;
use Doctrine\ORM\EntityManagerInterface;
use League\Csv\Reader;
use League\Csv\Statement;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class PersonService
{
    public function __construct(
        #[Autowire('%kernel.project_dir%')] private string $projectDir,
        private EntityManagerInterface $em,
        private ShopRepository $shopRepository,
        private PersonRepository $personRepository,
        private TechnicianFormationsRepository $technicianFormationsRepository,
        private TechnicianVehicleRepository $technicianVehicleRepository,
        private CgoRepository $cgoRepository,
        private TechnicianFonctionRepository $technicianFonctionRepository,
        private RoleErmRepository $roleErmRepository,
        private MapsService $mapsService
        ){
    }

    // ==========================================================
    // DR / AO / RZ / RAVL - des personnes sans centre fixe, rôle sur toute une
    // région/zone (ex-Manager)
    // ==========================================================

    public function importDR(SymfonyStyle $io): void
    {
        $io->title('Importation des DR');

        $totals = $this->readCsvFile($this->projectDir . '/import/centres.csv');
        $role = $this->roleErmRepository->findOneBy(['name' => RoleErm::DR]);

        $io->progressStart(count($totals));

        foreach ($totals as $array) {
            $io->progressAdvance();

            $name = trim($array['Nom DR'] ?? '');
            if ($name === '') {
                continue;
            }

            $person = $this->personRepository->findOneBy(['name' => $name]) ?? $this->newPersonWithDefaults();
            $person->setName($name)->setFirstName($person->getFirstName() ?? 'A définir');
            if ($role) {
                $person->addRole($role);
            }

            // Flush ligne par ligne : un même DR supervise plusieurs centres, il faut
            // que le SELECT de la ligne suivante retrouve celui qu'on vient de créer.
            $this->em->persist($person);
            $this->em->flush();
        }

        $io->progressFinish();
        $io->success('Importation terminée');
    }

    public function importAO(SymfonyStyle $io): void
    {
        $io->title('Importation des AO');

        $totals = $this->readCsvFile($this->projectDir . '/import/centres.csv');
        $role = $this->roleErmRepository->findOneBy(['name' => RoleErm::AO]);

        $io->progressStart(count($totals));

        foreach ($totals as $array) {
            $io->progressAdvance();

            $name = trim($array['Nom AO'] ?? '');
            if ($name === '') {
                continue;
            }

            $person = $this->personRepository->findOneBy(['name' => $name]) ?? $this->newPersonWithDefaults();
            $person->setName($name)->setFirstName($person->getFirstName() ?? 'A définir');
            if ($role) {
                $person->addRole($role);
            }

            // Flush ligne par ligne : un même AO supervise plusieurs centres, il faut
            // que le SELECT de la ligne suivante retrouve celui qu'on vient de créer.
            $this->em->persist($person);
            $this->em->flush();
        }

        $io->progressFinish();
        $io->success('Importation terminée');
    }

    public function importRAVL_RZ(SymfonyStyle $io): void
    {
        $io->title('Importation des RAVL et RZ');

        $totals = $this->readCsvFile($this->projectDir . '/import/centres.csv');

        $io->progressStart(count($totals));

        foreach ($totals as $array) {
            $io->progressAdvance();

            $rawName = trim($array['Nom R. Zone ou RA VL'] ?? '');
            if ($rawName === '') {
                continue;
            }

            $roleName = null;
            if (preg_match('/RA\s?VL/', $rawName)) {
                $roleName = RoleErm::RAVL;
            } elseif (preg_match('/RZ/', $rawName)) {
                $roleName = RoleErm::RZ;
            }

            // Le nom porte le rôle entre parenthèses ("DUCALCON (RZ)") - on le retire
            // du nom affiché puisque le rôle est déjà porté séparément par RoleErm.
            $name = trim(preg_replace('/\s*\([^)]*\)\s*$/', '', $rawName) ?? $rawName);

            $person = $this->personRepository->findOneBy(['name' => $name]) ?? $this->newPersonWithDefaults();
            $person->setName($name)->setFirstName($person->getFirstName() ?? 'A définir');

            if ($roleName) {
                $role = $this->roleErmRepository->findOneBy(['name' => $roleName]);
                if ($role) {
                    $person->addRole($role);
                }
            }

            // Flush ligne par ligne : un même RZ/RAVL supervise plusieurs centres, il
            // faut que le SELECT de la ligne suivante retrouve celui qu'on vient de créer.
            $this->em->persist($person);
            $this->em->flush();
        }

        $io->progressFinish();
        $io->success('Importation terminée');
    }

    // ==========================================================
    // CT (ex-TechnicalAdvisor) - une personne avec le rôle CT
    // ==========================================================

    public function importCTs(SymfonyStyle $io): void
    {
        $io->title('Importation des CT');

        $totals = $this->readCsvFile($this->projectDir . '/import/ct.csv');
        $ctRole = $this->roleErmRepository->findOneBy(['name' => RoleErm::CT]);

        $io->progressStart(count($totals));

        foreach ($totals as $array) {
            $io->progressAdvance();
            $person = $this->createOrUpdateCT($array, $ctRole, $io);
            if ($person !== null) {
                // Flush ligne par ligne : un même CT peut apparaître sur plusieurs lignes,
                // il faut que le SELECT de la ligne suivante retrouve celui qu'on vient de créer.
                $this->em->persist($person);
                $this->em->flush();
            }
        }

        $io->progressFinish();
        $io->success('Importation terminée');
    }

    private function createOrUpdateCT(array $array, ?RoleErm $ctRole, SymfonyStyle $io): ?Person
    {
        $shop = $this->shopRepository->findOneByCm($array['CM Admin']);
        if (!$shop) {
            $io->warning('Aucun centre pour le CM Admin "' . $array['CM Admin'] . '" (CT: ' . $array['Nom'] . ' ' . $array['Prénom'] . ') - ligne ignorée');

            return null;
        }

        $manager = $this->personRepository->findAoForCtImportation($array['Nom AO']);
        if (!$manager) {
            $io->warning('Aucun manager AO pour "' . $array['Nom AO'] . '" - création d\'une personne provisoire');
            $manager = $this->newPersonWithDefaults();
            $manager->setFirstName('A définir')->setName($array['Nom AO']);
            $aoRole = $this->roleErmRepository->findOneBy(['name' => RoleErm::AO]);
            if ($aoRole) {
                $manager->addRole($aoRole);
            }
            $this->em->persist($manager);
            $this->em->flush($manager);
        }

        $person = $this->personRepository->findOneBy(['name' => $array['Nom']]) ?? $this->newPersonWithDefaults($shop);

        $person
            ->setName($array['Nom'])
            ->setFirstName($array['Prénom'])
            ->setEmail($array['email'])
            ->setPhone($array['Tél mobile'])
            ->setManager($manager)
            ->setShop($shop)
            ->setZoneColor($this->mapsService->randomHexadecimalColor())
            ;
        if ($ctRole) {
            $person->addRole($ctRole);
        }

        return $person;
    }

    public function importWorkForShops(SymfonyStyle $io): void
    {
        $io->title('Importation des rattachements CT <-> centres');

        $totals = $this->readCsvFile($this->projectDir . '/import/rattachements_ct_cds.csv');

        $io->progressStart(count($totals));

        foreach ($totals as $arrayTotal) {
            $io->progressAdvance();

            if (($arrayTotal['Métier'] ?? '') !== 'CT') {
                continue;
            }

            $person = $this->personRepository->findOneBy(['name' => $arrayTotal['Nom']]);
            if (!$person) {
                $io->warning('Aucun CT trouvé pour "' . $arrayTotal['Nom'] . ' ' . $arrayTotal['Prénom'] . '" - ligne ignorée');
                continue;
            }

            $shop = $this->shopRepository->findOneByCm($arrayTotal['CM']);
            if (!$shop) {
                $io->warning('Aucun centre pour le CM "' . $arrayTotal['CM'] . '" (rattachement CT: ' . $arrayTotal['Nom'] . ') - ligne ignorée');
                continue;
            }

            $person->addWorkForShop($shop);
            $this->em->persist($person);
        }

        $this->em->flush();

        $io->progressFinish();
        $io->success('Importation terminée');
    }

    // ==========================================================
    // RCS - la personne responsable du centre (rôle RCS)
    // ==========================================================

    public function importRCS(SymfonyStyle $io): void
    {
        $io->title('Importation des RCS');

        $totals = $this->readCsvFile($this->projectDir . '/import/centres.csv');
        $rcsRole = $this->roleErmRepository->findOneBy(['name' => RoleErm::RCS]);

        $io->progressStart(count($totals));

        foreach ($totals as $array) {
            $io->progressAdvance();
            $person = $this->createOrUpdateRCS($array, $rcsRole, $io);
            if ($person !== null) {
                // Flush ligne par ligne : un même RCS supervise souvent plusieurs centres,
                // il faut que le SELECT de la ligne suivante retrouve celui qu'on vient de créer.
                $this->em->persist($person);
                $this->em->flush();
            }
        }

        $io->progressFinish();
        $io->success('Importation terminée');
    }

    private function createOrUpdateRCS(array $array, ?RoleErm $rcsRole, SymfonyStyle $io): ?Person
    {
        $surname = trim($array['Nom RCS'] ?? '');
        if ($surname === '') {
            // Pas de RCS renseigné pour ce centre, rien à importer sur cette ligne.
            return null;
        }

        $shop = $this->shopRepository->findOneByCm($array['CM']);
        if (!$shop) {
            $io->warning('Aucun centre pour le CM "' . $array['CM'] . '" (RCS: ' . $surname . ') - ligne ignorée');

            return null;
        }

        $person = $this->personRepository->findOneBy(['name' => $surname]) ?? $this->newPersonWithDefaults($shop);

        $person
            ->setName($surname)
            ->setFirstName($array['Prénom RCS'] ?? 'A définir')
            ->setShop($shop);
        if ($rcsRole) {
            $person->addRole($rcsRole);
        }

        if ($this->looksLikeEmail(trim($array['email'] ?? ''))) {
            $person->setEmail(trim($array['email']));
        }
        if ($this->looksLikePhone(trim($array['Tél mobile resp.'] ?? ''))) {
            $person->setPhone(trim($array['Tél mobile resp.']));
        }

        return $person;
    }

    // ==========================================================
    // Experts télématique - une personne avec le rôle TECHNICIEN TELEMATIQUE
    // ==========================================================

    public function importTelematicExperts(SymfonyStyle $io): void
    {
        $io->title('Importation des experts télématique');

        $totals = $this->readCsvFile($this->projectDir . '/import/experts_telematique.csv');
        $telematicRole = $this->roleErmRepository->findOneBy(['name' => RoleErm::TECHNICIEN_TELEMATIQUE]);

        $io->progressStart(count($totals));

        foreach ($totals as $row) {
            $io->progressAdvance();

            $person = $this->resolvePersonForTelematicRow($row, $io, true);
            if ($person === null) {
                continue;
            }

            if ($telematicRole) {
                $person->addRole($telematicRole);
            }

            $email = trim($row['Email TM '] ?? '');
            if ($this->looksLikeEmail($email)) {
                $person->setEmail($email);
            }

            $phone = trim($row['Téléphone Portable '] ?? '');
            if ($this->looksLikePhone($phone)) {
                $person->setPhone($phone);
            }

            $this->em->persist($person);
        }

        $this->em->flush();

        $io->progressFinish();
        $io->success('Importation terminée');
    }

    public function importTelematicCompetences(SymfonyStyle $io): void
    {
        $io->title('Importation des compétences télématique');

        $totals = $this->readCsvFile($this->projectDir . '/import/competences_telematique.csv');
        $formations = $this->technicianFormationsRepository->findAll();

        $io->progressStart(count($totals));

        foreach ($totals as $row) {
            $io->progressAdvance();

            $person = $this->resolvePersonForTelematicRow($row, $io, false);
            if ($person === null) {
                continue;
            }

            foreach ($formations as $formation) {
                if (!array_key_exists($formation->getName(), $row)) {
                    continue;
                }
                $this->testFormationIsOkorKo($formation->getName(), $row[$formation->getName()], $person, $io);
            }

            $vehicleName = trim($row['Véhicule'] ?? '');
            if ($vehicleName !== '') {
                $vehicle = $this->technicianVehicleRepository->findOneBy(['name' => $vehicleName]);
                if ($vehicle) {
                    $person->setVehicle($vehicle);
                }
            }

            $informations = trim($row["INFOS \nDIVERS"] ?? '');
            if ($informations !== '') {
                $person->setInformations($informations);
            }

            $this->em->persist($person);
        }

        $this->em->flush();

        $io->progressFinish();
        $io->success('Importation terminée');
    }

    /**
     * Retrouve (ou crée, si $createIfMissing) le Person correspondant à une ligne des
     * fichiers "Expert télématique"/"Compétence Expert télématique", en résolvant le centre
     * par CM et la personne par son nom de famille (dernier mot de la colonne "Télématique VI
     * TM1", après avoir retiré les suffixes entre parenthèses type "(CT)" qui indiquent un
     * chevauchement avec un CT déjà importé via ct.csv).
     */
    private function resolvePersonForTelematicRow(array $row, SymfonyStyle $io, bool $createIfMissing): ?Person
    {
        $cm = trim($row['CM'] ?? '');
        if ($cm === '') {
            $io->warning('Ligne télématique sans CM - ignorée');

            return null;
        }

        $shop = $this->shopRepository->findOneByCm($cm);
        if (!$shop) {
            $io->warning('Aucun centre pour le CM "' . $cm . '" - ligne télématique ignorée');

            return null;
        }

        [$firstName, $surname] = $this->extractFirstNameAndSurname($row["Télématique VI\nTM1"] ?? '');
        if ($surname === '') {
            $io->warning('Ligne télématique sans nom exploitable pour le CM "' . $cm . '" - ignorée');

            return null;
        }

        // experts_telematique.csv et competences_telematique.csv n'ont pas le même ordre
        // Nom/Prénom d'une ligne à l'autre ("CANIAC Thomas" vs "Thomas Caniac" pour le même
        // CM) : on matche donc par centre + au moins un mot de nom en commun, plutôt que par
        // nom de famille exact.
        $rowWords = $this->normalizedWordSet($firstName . ' ' . $surname);

        $person = null;
        foreach ($this->personRepository->findBy(['shop' => $shop]) as $candidate) {
            $candidateWords = $this->normalizedWordSet($candidate->getFirstName() . ' ' . $candidate->getName());
            if (count(array_intersect($rowWords, $candidateWords)) > 0) {
                $person = $candidate;
                break;
            }
        }

        if (!$person) {
            if (!$createIfMissing) {
                $io->warning('Aucun technicien "' . $firstName . ' ' . $surname . '" trouvé pour les compétences (pas encore importé via la liste des experts) - ligne ignorée');

                return null;
            }
            $person = $this->newPersonWithDefaults($shop);
            $person->setName($surname);
            if ($firstName !== '') {
                $person->setFirstName($firstName);
            }
        }

        $person->setShop($shop);

        return $person;
    }

    /**
     * @return string[]
     */
    private function normalizedWordSet(string $value): array
    {
        $clean = preg_replace('/\s*\([^)]*\)\s*/', ' ', $value) ?? $value;
        $transliterator = \Transliterator::create('Any-Latin; Latin-ASCII');
        if ($transliterator !== null) {
            $clean = $transliterator->transliterate($clean);
        }
        $clean = preg_replace('/[^a-zA-Z ]/', ' ', $clean) ?? $clean;
        $words = array_filter(array_map('trim', explode(' ', mb_strtolower($clean))));

        return array_values(array_unique($words));
    }

    private function testFormationIsOkorKo(string $formationName, ?string $okOrKo, Person $person, SymfonyStyle $io): void
    {
        $formationEntity = $this->technicianFormationsRepository->findOneBy(['name' => $formationName]);
        $m400vl = $this->technicianFormationsRepository->findOneBy(['name' => 'M400+HMI+CAN VL']);

        if ($m400vl) {
            $person->addTechnicianFormation($m400vl); //?ajout de la formation m400+HMI+CAN VL par default à tout le monde
        }

        if ($formationEntity) {
            if (trim((string) $okOrKo) === 'OK' || trim((string) $okOrKo) === 'oui' || trim((string) $okOrKo) === 'OUI') {
                $person->addTechnicianFormation($formationEntity);
            }
        } else {
            $io->warning('Formation "' . $formationName . '" introuvable en base');
        }
    }

    // ==========================================================
    // Helpers communs
    // ==========================================================

    /**
     * Crée une nouvelle Person avec le rôle TM par défaut. $shop est optionnel : les
     * rôles manager (DR/AO/RZ/RAVL/RCGO...) n'ont pas de centre fixe.
     */
    public function newPersonWithDefaults(?Shop $shop = null): Person
    {
        $person = new Person();
        $person
            ->setShop($shop)
            ->setEmail('A définir')
            ->setPhone('A définir')
        ;

        if ($shop) {
            $cgos = $shop->getCgos();
            $cgo = (!$cgos || count($cgos) === 0) ? $this->cgoRepository->findOneBy(['cm' => 3429]) : $cgos[0];
            $person
                ->setControledByCgo($cgo)
                ->setVehicle($this->technicianVehicleRepository->findOneBy(['name' => 'SANS VEHICULE']));
        }

        $tmRole = $this->roleErmRepository->findOneBy(['name' => RoleErm::TM]);
        if ($tmRole) {
            $person->addRole($tmRole);
        }

        return $person;
    }

    /**
     * experts_telematique.csv contient des colonnes d'en-tête vides en fin de ligne
     * (export Excel), ce qui fait planter League\Csv en mode setHeaderOffset() classique
     * ("duplicate column names"). On dédoublonne donc l'en-tête nous-mêmes.
     *
     * @return array<int, array<string, string>>
     */
    private function readCsvFile(string $path): array
    {
        $csv = Reader::createFromPath($path, 'r');
        $header = $csv->fetchOne(0);

        $seen = [];
        foreach ($header as $i => $name) {
            $name = $name === '' ? ('colonne_' . $i) : $name;
            if (isset($seen[$name])) {
                $name .= '_' . $i;
            }
            $seen[$name] = true;
            $header[$i] = $name;
        }

        $rows = [];
        foreach ((new Statement())->offset(1)->process($csv) as $record) {
            $record = array_values($record);
            if (count($record) < count($header)) {
                $record = array_pad($record, count($header), '');
            } elseif (count($record) > count($header)) {
                $record = array_slice($record, 0, count($header));
            }
            $rows[] = array_combine($header, $record);
        }

        return $rows;
    }

    /**
     * "Cédric Jadas" -> ['Cédric', 'Jadas'] ; "Martial  PAVY (CT)" -> ['Martial', 'PAVY']
     * Le dernier mot est considéré comme le nom de famille (convention observée dans les
     * fichiers "Expert télématique", à l'inverse de ct.csv/centres.csv qui ont Nom et
     * Prénom dans des colonnes séparées).
     */
    private function extractFirstNameAndSurname(string $rawName): array
    {
        $clean = preg_replace('/\s*\([^)]*\)\s*$/', '', trim($rawName)) ?? '';
        $clean = trim(preg_replace('/\s+/', ' ', $clean) ?? '');

        if ($clean === '') {
            return ['', ''];
        }

        $words = explode(' ', $clean);
        $surname = array_pop($words);
        $firstName = implode(' ', $words);

        return [$firstName, $surname];
    }

    private function looksLikeEmail(string $value): bool
    {
        return $value !== '' && filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }

    private function looksLikePhone(string $value): bool
    {
        if ($value === '') {
            return false;
        }
        $digits = preg_replace('/\D/', '', $value) ?? '';

        return strlen($digits) >= 9;
    }
}
