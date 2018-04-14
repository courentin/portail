# Installation et Mise à jour

## Installation

- Vérifier que qu'une version supérieure à 7.1.3 de PHP est installé : `php -v`
- Installer [composer](https://getcomposer.org/download/)
- Installer les packages avec `composer install` (attention à être dans le bon dossier)
- Copier `.env.example` en `.env` et spécifier les identifiants de connexions à la base de données (par exemple localhost)
- Lancer les commances suivantes :
    + Suppression du cache : `php artisan config:clear`
    + Création de la clé : `php artisan key:generate`
- Créer la base de données `portail` à la mano
- Lancer la commande suivante : `php artisan migrate:fresh`
- Pour populer la BDD : `php artisan db:seed`
- Pour générer la documentation de l'api : `php artisan api:generate --routePrefi="api/*"`
- Lancer l'application via :
    + Artisan : `php artisan serve` et aller sur http://localhost:8000
    + Wamp : aller directement sur le dossier `public` de l'installation via Wamp
- Ça part !


## Mise à jour

- Mettre à jour les packages php avec `composer update` et `composer install`
- Mettre à jour les packages npm avec `npm install`
- Relancer les migrations avec `php artisan migrate:fresh --seed`