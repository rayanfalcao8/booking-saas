# Plan de test et de lancement Reservix

Ce plan organise la validation de Reservix en trois environnements. Il ne déclenche aucun déploiement : chaque passage d’un environnement au suivant exige une approbation explicite.

## 1. Test local sur Mac

### Préparer l’environnement

Reservix exige Node.js `20.19+` ou `22.12+`. Node.js 22 LTS est le choix conseillé pour éviter les avertissements Vite.

Avec `nvm` :

```bash
nvm install 22
nvm use 22
node --version
```

Depuis le dossier du projet :

```bash
cd "/Users/rayanfalcao/Library/Application Support/Herd/config/valet/Sites/booking-saas"
composer install
npm ci
php artisan optimize:clear
php artisan migrate --seed
```

Ne pas lancer `migrate:fresh` sur une base contenant des données à conserver. Pour une recette entièrement isolée, créer une base locale dédiée puis renseigner ses accès dans `.env`.

### Configurer l’URL et la file

Le domaine Herd doit être reporté exactement dans `.env`, par exemple :

```ini
APP_ENV=local
APP_DEBUG=true
APP_URL=http://booking-saas.test
QUEUE_CONNECTION=database
```

Dans deux terminaux séparés :

```bash
npm run dev
```

```bash
php artisan queue:work --tries=3 --backoff=60
```

La file est obligatoire : les confirmations email et SMS sont asynchrones.

### Tester les emails dans Mailpit

Installer une seule fois Mailpit sur macOS, puis démarrer le service :

```bash
brew install mailpit
brew services start mailpit
```

Configurer `.env` :

```ini
MAIL_MAILER=smtp
MAIL_SCHEME=null
MAIL_HOST=127.0.0.1
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_FROM_ADDRESS=no-reply@reservix.test
MAIL_FROM_NAME=Reservix
```

Les messages sont visibles sur `http://127.0.0.1:8025`. Après un changement de `.env`, exécuter `php artisan optimize:clear` puis redémarrer le worker.

### Tester les SMS sans coût

Pour valider le contenu et le déclenchement sans envoyer de vrai SMS :

```ini
SMS_DRIVER=log
```

Créer une réservation avec un numéro au format international, par exemple `+14185550123`, puis consulter `storage/logs/laravel.log`. Une entrée `SMS notification` doit contenir le destinataire, le texte et le lien de confirmation.

### Tester un vrai SMS Twilio

Utiliser uniquement un compte Twilio de test et un numéro personnel autorisé. Conserver les secrets dans `.env`, jamais dans Git :

```ini
SMS_DRIVER=twilio
TWILIO_ACCOUNT_SID=AC...
TWILIO_AUTH_TOKEN=...
TWILIO_MESSAGING_SERVICE_SID=MG...
TWILIO_FROM=
```

`TWILIO_FROM` peut remplacer le Messaging Service SID si un numéro d’envoi Twilio est utilisé. Les destinataires doivent être saisis au format E.164. Après modification, vider le cache de configuration et redémarrer le worker.

### Parcours local obligatoire

1. Ouvrir `/`, puis vérifier la navigation sur mobile et ordinateur.
2. Ouvrir `/b/maison-kinks-braids/book`.
3. Parcourir service, date, créneau et coordonnées ; revenir modifier chaque étape.
4. Confirmer la réservation et vérifier la page de récapitulatif.
5. Vérifier l’email client, l’email établissement et le SMS simulé ou réel.
6. Ouvrir les liens de confirmation et d’annulation reçus.
7. Vérifier le rendez-vous dans `/app/bookings`.
8. Envoyer un retour dans l’espace entreprise et le traiter dans `/admin`.

Avant de quitter la phase locale :

```bash
vendor/bin/pint --test
php artisan test --compact
npm run build
php artisan reservix:health-check
```

Critère de sortie : build et tests verts, parcours complet réussi dans deux tailles d’écran, notifications observées, aucune tâche inattendue dans `failed_jobs`.

## 2. Staging privé

Le staging utilise sa propre URL, sa propre base et ses propres secrets. Il peut suivre la branche de la PR tant que celle-ci reste en recette.

Configuration minimale :

- `APP_ENV=staging`, `APP_DEBUG=false` et `APP_URL` égal au domaine HTTPS réel ;
- base MySQL dédiée, jamais une copie connectée à la production ;
- worker de file actif ;
- flux Postmark de test ou serveur staging séparé ;
- Twilio limité à une liste de numéros internes ;
- déploiement manuel uniquement.

Après chaque déploiement :

```bash
php artisan migrate --force
php artisan optimize
php artisan reservix:health-check --require-sms
```

Exécuter ensuite `MANUAL_QA.md` avec au moins deux entreprises de démonstration et conserver : SHA testé, navigateur, largeur d’écran, identifiants des emails/SMS et anomalies observées.

Critère de sortie : CI verte sur le SHA déployé, recette fonctionnelle signée, email et SMS réels reçus, isolation de deux entreprises confirmée, sauvegarde et restauration testées, zéro erreur bloquante.

## 3. Production et lancement

La production suit uniquement `main`. L’auto-déploiement reste désactivé pendant la bêta privée.

Ordre de lancement :

1. approuver la PR et fusionner le SHA validé ;
2. créer l’environnement de production et ses secrets sans réutiliser ceux du staging ;
3. connecter domaine, TLS, Postmark et Twilio ;
4. créer un instantané de base ;
5. déployer manuellement le même SHA que celui validé ;
6. exécuter `php artisan reservix:health-check --production --require-sms` ;
7. effectuer une réservation réelle de bout en bout ;
8. ouvrir à un petit groupe pilote ;
9. surveiller journaux, file et tâches échouées pendant au moins 30 minutes.

Avant l’ouverture, les conditions d’utilisation, la politique de confidentialité, le responsable du support, la limite de dépenses email/SMS et la procédure d’incident doivent être approuvés.

En cas d’incident, désactiver les réservations publiques depuis les paramètres de l’entreprise, conserver la file et les journaux, revenir au dernier déploiement sain, puis relancer uniquement les tâches diagnostiquées.
