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

## Sviluppo futuro
Il progetto non è da considerarsi definitivo e sarà via via aggiornato nel tempo per migliorarne le funzionalità, la sicurezza e la struttura del codice.