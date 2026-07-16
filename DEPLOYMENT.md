# Déploiement de la bêta privée Reservix

Ce document décrit le premier déploiement exploitable de Reservix. Il ne crée aucun compte fournisseur, n'achète aucun domaine et ne déclenche aucune mise en production.

## Cible retenue

- Hébergement : Laravel Cloud, plan Starter au début.
- Environnements : `staging` séparé de `production`.
- Base de données : Laravel MySQL géré, avec une base distincte par environnement.
- Tâches asynchrones : file gérée Laravel Cloud pour les emails et SMS.
- Email transactionnel : Postmark par SMTP.
- SMS : Twilio Messaging Service avec un numéro sans frais vérifié pour le Canada.
- Déploiement production : manuel, depuis `main`, uniquement après validation du staging.

Le staging peut suivre la branche de la PR pendant la recette. Après fusion, il doit suivre `main`. La production suit toujours `main` et l'auto-déploiement reste désactivé pendant la bêta privée.

## Comptes et décisions nécessaires

Avant de créer l'infrastructure, le propriétaire du projet doit fournir ou approuver :

1. le domaine Reservix et l'adresse email d'expédition ;
2. un compte Laravel Cloud relié au dépôt GitHub ;
3. un compte Postmark avec le domaine d'envoi authentifié ;
4. un compte Twilio, un Messaging Service et un expéditeur conforme aux pays ciblés ;
5. une limite mensuelle de dépenses ;
6. l'identité légale, la juridiction et les textes de confidentialité/conditions à faire valider.

Ne jamais copier une clé secrète dans GitHub, un commit, un ticket ou ce document. Les secrets appartiennent aux variables chiffrées de chaque environnement.

## Configuration Laravel Cloud

Créer deux environnements avec des ressources indépendantes :

| Élément | Staging | Production |
| --- | --- | --- |
| Branche initiale | branche de la PR | `main` |
| Déploiement | manuel | manuel |
| Base | MySQL dédiée, données de test | MySQL dédiée, données réelles |
| Sauvegardes | 7 jours | 7 jours minimum + test de restauration |
| File | gérée, worker minimal | gérée, worker minimal |
| Email | flux serveur de test | flux transactionnel de production |
| SMS | destinataires de test | destinations autorisées uniquement |

Commande de build :

```bash
composer install --no-dev --prefer-dist --optimize-autoloader && npm ci && npm run build && php artisan optimize
```

Commande de déploiement :

```bash
php artisan migrate --force
```

La file doit traiter les notifications avec au moins trois tentatives. Toute notification épuisant ses tentatives doit rester visible dans le tableau des tâches échouées et être examinée avant d'être relancée.

## Variables d'environnement

Laravel Cloud injecte les variables de connexion de la base et de la file gérées. Ajouter les variables applicatives suivantes dans chaque environnement :

```ini
APP_NAME=Reservix
APP_ENV=production
APP_DEBUG=false
APP_URL=https://reservix.example
APP_LOCALE=fr

MAIL_MAILER=smtp
MAIL_SCHEME=tls
MAIL_HOST=smtp.postmarkapp.com
MAIL_PORT=587
MAIL_USERNAME=secret
MAIL_PASSWORD=secret
MAIL_FROM_ADDRESS=confirmations@reservix.example
MAIL_FROM_NAME=Reservix

SMS_DRIVER=twilio
TWILIO_ACCOUNT_SID=secret
TWILIO_AUTH_TOKEN=secret
TWILIO_MESSAGING_SERVICE_SID=secret
TWILIO_FROM=
```

Utiliser `APP_ENV=staging` et le domaine de staging dans l'environnement de recette. L'adresse `APP_URL` doit être exacte : les liens de confirmation et d'annulation en dépendent.

## Contrôles avant déploiement

Exécuter dans le build ou sur une copie de recette :

```bash
php artisan test --compact
npm run build
vendor/bin/pint --test
```

Après le déploiement staging :

```bash
php artisan reservix:health-check --require-sms
```

Avant l'ouverture de la production :

```bash
php artisan reservix:health-check --production --require-sms
```

Vérifier aussi `GET /up`, puis compléter `MANUAL_QA.md` sur mobile et ordinateur.

## Recette obligatoire

- Créer, modifier et annuler une réservation sans double réservation.
- Vérifier les délais, l'horizon de réservation, l'intervalle des créneaux et la désactivation des réservations.
- Vérifier les affectations service/prestataire et les absences partielles ou journalières.
- Recevoir l'email client, l'email établissement et le SMS client sur de vraies boîtes/numéros de test.
- Vérifier les liens de confirmation et d'annulation issus des messages.
- Envoyer un retour depuis l'espace établissement et le traiter dans l'administration.
- Confirmer l'isolation entre deux établissements.
- Inspecter les journaux et le tableau des tâches échouées.
- Restaurer un instantané de la base de production dans une base temporaire avant l'ouverture au public.

## Procédure de mise en production

1. Faire approuver la PR et le compte rendu de recette.
2. Fusionner la PR dans `main`.
3. Créer un instantané manuel de la base de production.
4. Déployer explicitement le SHA validé sur le staging, puis sur la production.
5. Exécuter le contrôle de santé production.
6. Tester une réservation réelle avec email et SMS.
7. Surveiller journaux, files et erreurs pendant au moins 30 minutes.

En cas d'échec, ne pas purger la file. Désactiver les réservations publiques depuis les paramètres métier, corriger ou revenir au dernier déploiement sain, puis reprendre les tâches échouées après diagnostic.

## Critères d'ouverture de la bêta

La bêta ne peut être ouverte que si :

- les tests et le build sont verts sur le SHA déployé ;
- le contrôle de santé production est vert ;
- le domaine, TLS, Postmark et Twilio sont validés ;
- une restauration de sauvegarde a été testée ;
- les mentions de confidentialité et conditions ont été approuvées ;
- un responsable sait désactiver les réservations, consulter les erreurs et relancer une notification échouée ;
- l'ouverture a reçu une approbation explicite du propriétaire du projet.
