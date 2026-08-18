# ERM MAPS
Display all shops from ERM in France.


***
## PREMIÈRE INSTALLATION (nouveau serveur)

### SE CONNECTER AU SERVEUR
    ssh...\
    password

### SE DEPLACER DANS LE DOSSIER OU L'ON VA METTRE LE PROJET, PUIS CLONER LE DEPOT
    git clone https://github.com/JeDeveloppe/ERM.git {nom du projet}
    cd {nom du projet}

### INSTALLER COMPOSER DANS LE PROJET
    curl -sS https://getcomposer.org/installer

Cela permet d'utiliser la commande:  /usr/bin/php8.2-cli\
Exemple: /usr/bin/php8.2-cli bin/console d:m:m

### FICHIERS À TRANSFÉRER MANUELLEMENT (FTP/SCP) - jamais dans Git
Ces fichiers sont volontairement exclus du dépôt (`.gitignore`) et n'existent
donc que sur ta machine ou l'ancien serveur - il faut les transférer à la main
à chaque nouvelle installation :
- `.env` et `.env.prod` (secrets, config de prod)
- `import/centres.csv`, `import/cgo.csv`, `import/ct.csv`,
  `import/rattachements_ct_cds.csv`, `import/experts_telematique.csv`,
  `import/competences_telematique.csv`, `import/csv/` (données réelles ERM,
  contiennent des informations personnelles - jamais publiées sur GitHub)

Tout le reste (dont `public/`, `import/department_region_erm.csv`) est déjà
dans le dépôt Git, rien à transférer en plus.

### INSTALLER LES DEPENDANCES DU PROJET
    /usr/bin/php8.2-cli composer.phar install --no-dev --optimize-autoloader

### FAIRE TOURNER LES MIGRATIONS OU METTRE LA BDD EN PLACE
    /usr/bin/php8.2-cli bin/console d:m:m

    /usr/bin/php8.2-cli bin/console d:s:u --force

### ON INITIALISE LE PROJET (charge les CSV de import/ en base)
    /usr/bin/php8.2-cli bin/console app:initdatabase

### ON COMPILE LES ASSETS (AssetMapper, pas de build Sass)
    /usr/bin/php8.2-cli bin/console cache:clear --env=prod
    /usr/bin/php8.2-cli bin/console asset-map:compile

### SUR HEBERGEUR
    faire pointer l'espace web sur le dossier public


***
## MISE À JOUR (déployer une nouvelle version sur un serveur déjà installé)

    ssh vers le serveur
    cd {nom du projet}
    git pull origin master
    /usr/bin/php8.2-cli composer.phar install --no-dev --optimize-autoloader
    /usr/bin/php8.2-cli bin/console d:m:m --no-interaction
    /usr/bin/php8.2-cli bin/console cache:clear --env=prod
    /usr/bin/php8.2-cli bin/console asset-map:compile

Ne PAS relancer `app:initdatabase --reset` sur la prod : ça vide les tables
de jointure (rôles, formations, véhicules...) pour les reconstruire depuis
les CSV, ce qui n'a de sens qu'après une mise à jour des fichiers `import/*.csv`
eux-mêmes.