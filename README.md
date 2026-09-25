# Portage Laravel — C.E.S. Container

Ce dossier est un **overlay applicatif** à copier dans un projet Laravel 11/12 neuf. Il contient le domaine métier, les contrôleurs, vues, routes, services, assets et la migration des nouveaux paramètres.

## Fonctionnalités
- Boutique, fiche conteneur et panier en session.
- Checkout sécurisé côté serveur.
- Paiement exclusivement par **virement bancaire**.
- Modal des coordonnées bancaires après validation.
- Emails client et administration.
- Email de notification configurable pour les contacts.
- Administration : conteneurs, catégories, services, clients, commandes, messages, paramètres.
- Suivi des statuts de commande et restauration/réservation du stock lors d’une annulation/réactivation.
- Validation du stock et des prix depuis la base, jamais depuis le panier client.
- Durée de location configurable et prise en compte dans le total.

## Installation
1. Créer un projet Laravel 11/12 neuf avec Composer.
2. Copier le contenu de ce dossier à la racine du projet Laravel.
3. Configurer `.env` : application, MySQL et SMTP.
4. Importer le SQL existant `u951490661_conteneur (1).sql`.
5. Ajouter l'alias middleware `admin` vers `App\Http\Middleware\AdminMiddleware::class` (Laravel 11/12 : `bootstrap/app.php`).
6. Exécuter `php artisan migrate`.
7. Exécuter `php artisan db:seed --class=SystemSettingsSeeder`.
8. Vérifier `storage:link` avec `php artisan storage:link` si les uploads sont servis depuis `storage/app/public`.
9. Se connecter avec un administrateur existant de la table `administrateurs`.
10. Dans **Administration > Paramètres**, renseigner les coordonnées bancaires et les deux adresses email de notification.

## Paiement
Stripe n'est pas utilisé. Le parcours crée une commande et un paiement avec `mode_paiement = virement` et `statut_paiement = en_attente`. L'administrateur confirme ensuite le règlement avec la référence du virement.

## Base de données
Le SQL existant reste la source de vérité des tables métier. La migration fournie ajoute uniquement les paramètres bancaires et notifications dans `parametres_systeme`.

## Version 6 — finalisation métier
- Correction du total des lignes pour les locations : prix journalier × quantité × durée.
- Recherche et fiche client avec historique des commandes.
- Le prénom est obligatoire au checkout, conformément au formulaire client du site.
- Le suivi des commandes reste transactionnel et protégé par contrôle de stock.

## Lancer sur XAMPP (Windows)

### 1. Prérequis
- XAMPP avec Apache, MySQL et PHP 8.2+.
- Composer installé sur Windows.
- Vérifier dans CMD : `php -v` et `composer -V`.

### 2. Copier le projet
Décompresser le dossier `laravel_portage` dans :
`C:\xampp\htdocs\ces-container`

Le fichier `public` est le dossier web. Avec Apache/XAMPP, l'URL recommandée est :
`http://localhost/ces-container/public`

Pour une URL propre, il est préférable de créer un VirtualHost Apache pointant directement vers `C:/xampp/htdocs/ces-container/public`.

### 3. Installer les dépendances
Ouvrir CMD dans `C:\xampp\htdocs\ces-container` :

```text
composer install
copy .env.example .env
php artisan key:generate
php artisan storage:link
```

### 4. Créer la base MySQL
1. Démarrer Apache et MySQL dans XAMPP.
2. Ouvrir phpMyAdmin : `http://localhost/phpmyadmin`
3. Créer une base nommée `ces_container` en `utf8mb4`.
4. Importer le dump SQL existant fourni avec le projet source.
5. Modifier `.env` si le nom de base, l'utilisateur ou le mot de passe MySQL sont différents.

Exemple XAMPP classique :
```text
DB_DATABASE=ces_container
DB_USERNAME=root
DB_PASSWORD=
```

### 5. Paramétrage email local
Par défaut, le projet utilise `MAIL_MAILER=log` afin que l'absence de SMTP ne bloque pas les commandes pendant les tests.
Pour un vrai envoi, renseigner un SMTP dans `.env`.

### 6. E-mail Hostinger en production
Dans le `.env` de production (sur le serveur, jamais dans Git), utiliser les paramètres SMTP Hostinger suivants :

```text
MAIL_MAILER=smtp
MAIL_SCHEME=smtps
MAIL_HOST=smtp.hostinger.com
MAIL_PORT=465
MAIL_USERNAME=adresse complète de la boîte Hostinger
MAIL_PASSWORD=mot de passe de cette boîte
MAIL_FROM_ADDRESS=la même adresse de boîte
MAIL_FROM_NAME="C.E.S. Container"
```

Dans **Administration > Paramètres**, l'adresse principale d'envoi doit être la même boîte Hostinger authentifiée. Configurez les deux destinataires des messages de contact et le destinataire des commandes dans cette page, puis exécutez `php artisan config:clear` après toute modification du `.env` de production. Hostinger documente aussi `smtp.hostinger.com:587` avec STARTTLS comme alternative si le port 465 échoue.

Pour cette alternative, remplacer `MAIL_SCHEME=smtps` par `MAIL_SCHEME=smtp` et `MAIL_PORT=465` par `MAIL_PORT=587`.

Les messages envoyés depuis le formulaire sont enregistrés dans la table des messages et transmis aux destinataires configurés ; les réponses admin et les notifications de commande partent par SMTP. Les e-mails envoyés directement à une adresse Hostinger sont reçus dans la boîte Hostinger (Webmail ou client IMAP `imap.hostinger.com:993`, SSL). L'application Laravel ne relève pas cette boîte et n'importe pas automatiquement ces e-mails dans son espace admin.

### 7. Lancer
Option A, avec le serveur Laravel :
```text
php artisan serve
```
Puis ouvrir `http://127.0.0.1:8000`.

Option B, avec Apache XAMPP :
ouvrir `http://localhost/ces-container/public`.

### 8. Administration
La connexion admin est accessible via :
`/admin/login`

Utiliser un compte administrateur existant dans la base SQL importée.

### 9. Première vérification
Après installation :
```text
php artisan route:list
php artisan config:clear
php artisan cache:clear
```
Puis tester : accueil → boutique → panier → commande → modal bancaire → administration → paiement.
