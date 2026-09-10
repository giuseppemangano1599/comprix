# Comprix

## Preambolo
Questo progetto è stato realizzato per l'esame di Basi di Dati e Laboratorio di Programmazione Web durante l'anno accademico 2024/2025.

## Descrizione del progetto
Comprix è un prototipo di applicazione web per la gestione di un servizio di e-commerce. La piattaforma consente ai negozianti di registrarsi e gestire un proprio catalogo di prodotti, e ai clienti di cercare, acquistare e recensire tali prodotti. 

Il sistema interagisce con una base di dati relazionale ed è stato sviluppato in PHP, HTML, CSS e JavaScript (con l'utilizzo di jQuery e AJAX).

## Architettura e migrazione
Inizialmente il progetto è stato sviluppato e testato utilizzando l'ambiente locale XAMPP secondo le specifiche dei docenti. Successivamente, è stata effettuata una migrazione completa su Docker per isolare e modernizzare l'infrastruttura. 

L'attuale configurazione Docker separa i servizi in contenitori distinti:
- Un server web Apache con supporto PHP e le estensioni necessarie (PDO e MySQLi) configurato tramite Dockerfile.
- Un database basato su MariaDB.
- Un'interfaccia di gestione phpMyAdmin.

Versioni pinnate (build riproducibili): `php:8.2-apache`, `mariadb:11.4`, `phpmyadmin:5.2`.
MariaDB 11.4 LTS è richiesto dalle funzioni JSON usate dai trigger
(`db/dump.sql`); il dump originale era tarato su PHP 8.2 / MariaDB 10.4.

## Avvio rapido

Prerequisiti: Docker con plugin Compose, porte libere 8080, 8081, 3306.

```bash
docker compose up --build
```

- Sito: http://localhost:8080
- phpMyAdmin: http://localhost:8081
- MySQL dall'host: `localhost:3306`

Credenziali database: utente `developer` / `password`, root / `root`,
database `comprix_db`. Dentro i container l'host DB è `db`
(`DB_HOST` in `src/includes/config.php`); `localhost` vale solo dall'host.

## Account demo (solo sviluppo locale)

Dal seed (`db/dump.sql`): clienti `jack_sparrow`, `deliaaa`;
negozianti `angolotecnologico`, `lacasadimarti`, `libreria.delcorso`,
`nonsolo_sport`. Password e risposte segrete: vedi "Dati di prova" nella
relazione (`relazione_progetto_BDD_LPW.pdf`).

## Database e seed

`./db:/docker-entrypoint-initdb.d`: `db/dump.sql` viene importato solo al
primo avvio, a volume `db_data` vuoto. Per resettare (cancella tutti i dati):

```bash
docker compose down -v && docker compose up --build
```

Dopo un cambio di immagine MariaDB il reset è obbligatorio: un datadir
creato da una versione più recente non si avvia su una precedente.

## Note operative

- `src/` è la docroot (`./src:/var/www/html`); `src/uploads/` contiene sia
  le immagini di esempio sia i nuovi upload: non cancellarla, non committare
  upload di test.
- `ENVIRONMENT` in `src/includes/config.php`: `development` mostra gli
  errori, `production` li scrive in `src/logs/php_error.log`. Nessun `.env`:
  le credenziali stanno in `config.php` e `docker-compose.yml`.
- Nessuna suite di test/lint: verificare con
  `docker compose exec web php -l <file>` e prova manuale nel browser.
- jQuery 3.7.1 è caricato da CDN: serve connettività per le parti AJAX.

## Sviluppo futuro
Il progetto non è da considerarsi definitivo e sarà via via aggiornato nel tempo per migliorarne le funzionalità, la sicurezza e la struttura del codice.