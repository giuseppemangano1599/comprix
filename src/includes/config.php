<?php
/**
 * File di configurazione del sito.
 *
 * Viene incluso in ogni pagina del sito tramite l'header e importa alcune 
 * costanti e funzioni che sono utilizzate all'interno di altre pagine.
 *
 * @author   Giuseppe Mangano
 * @version  1.0.0
 * @since    2026-06-17
 */

// Definizione del percorso assoluto della root del progetto, utile per includere file in modo sicuro
const ROOT_PATH = __DIR__ . '/../';
const BASE_URL = 'http://localhost:8080/';

// Definizione dell'ambiente di sviluppo o produzione, utile per la gestione degli errori e dei log
define('ENVIRONMENT', 'development'); // Cambiare in "production" in produzione

// Configurazione della gestione degli errori in base all'ambiente
if (ENVIRONMENT === "development") {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(E_ALL);
    ini_set('log_errors', 1);

    // Se la cartella logs non esiste, la crea al volo con i permessi corretti
    if (!is_dir(ROOT_PATH . 'logs/')) {
        mkdir(ROOT_PATH . 'logs/', 0755, true);
    }

    ini_set('error_log', ROOT_PATH . 'logs/php_error.log');
}

// Configurazione delle costanti per la connessione alla base di dati
const DB_HOST = "db";
const DB_NAME = "comprix_db";
const DB_USER = "developer";
const DB_PASS = "password";

/** 
 * Configurazione della costante che definisce il nome del sito, viene combinata
 * a "$nome_pagina" per cambiare dinamicamente il titolo della pagina. 
 */
const NOME_SITO = "Comprix";


/**
 * Configurazione della costante che definisce lìarray delle domande segrete 
 * di sicurezza per il recupero delle credenziali. 
 */
const DOMANDE_SEGRETE = [
    "Qual era il nome del tuo primo animale domestico?",
    "Qual è il cognome da nubile di tua madre?",
    "In quale città sei nato?",
    "Qual era il tuo soprannome da bambino?",
    "Qual era il nome della tua scuola elementare?",
    "Qual è il titolo del tuo film preferito?",
    "Qual era il nome della prima band o artista che hai visto dal vivo?",
    "Qual è il mese di nascita di tuo fratello/sorella?",
    "Qual è il quartiere dove hai vissuto durante la tua infanzia?",
    "Qual è il tuo cibo preferito?",
    "Qual è il secondo nome di tuo padre?",
    "Qual è il modello della tua prima automobile?",
    "Qual è il nome della tua squadra del cuore?"
];


// Connessione alla base di dati tramite PDO
try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);
} catch (PDOException $e) {
    if (ENVIRONMENT === "development") {
        die("Errore durante la connessione al database: " . $e->getMessage());
    } else {
        error_log("Errore durante la connessione al database: " . $e->getMessage());
        die("Si è verificato un errore. Per favore riprova più tardi.");
    }
}


// Avvia una nuova sessione se non è già attiva prevenendo possibili errori
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


/**
 * Qui vengono definite alcune funzioni globali
 * che sono riutilizzate in diverse pagine del sito.
 */


/**
 * Sanifica una stringa per l'output in HTML, prevenendo attacchi XSS.
 * * Funzione alias di htmlspecialchars, preconfigurata con i flag di sicurezza ottimali
 * (ENT_QUOTES e ENT_HTML5) e codifica forzata in UTF-8. È progettata per gestire
 * in modo sicuro anche valori nulli.
 *
 * @param string|null $string La stringa grezza da sanificare.
 * @return string La stringa sicura pronta per essere stampata a video.
 */
function h(?string $string): string {
    return htmlspecialchars($string ?? "", ENT_QUOTES | ENT_HTML5, "UTF-8");
}


// Funzione che verifica se un utente è autenticato o meno
function utenteAutenticato() {
    return isset($_SESSION["user_id"]) && isset($_SESSION["user_type"]);
}


// Funzione che verifica se l'utente autenticato è un cliente
function verificaCliente() {
    return utenteAutenticato() && $_SESSION["user_type"] === "cliente";
}


// Funzione che verifica se l'utente autenticato è un negoziante
function verificaNegoziante() {
    return utenteAutenticato() && $_SESSION["user_type"] === "negoziante";
}


// Funzione che formatta il prezzo in Euro
function prezzoEuro($price) {
    return "€ " . number_format($price, 2, ",", ".");
}


// Funzione che restituisce il numero totale degli articoli nel carrello del cliente nel badge sulla navbar
function articoliCarrelloNavbar($pdo) {
    // Solo i clienti possono avere un (e unico) carrello
    if (!verificaCliente()) {
        return 0;
    }

    $stmt = $pdo->prepare("SELECT totale_elementi FROM carrello WHERE id_cliente = :id_cliente");
    $stmt->execute(["id_cliente" => $_SESSION["user_id"]]);

    // Restituisce il totale degli articoli presenti o 0 se carrello è vuoto
    return ($stmt->fetchColumn() ?: 0);
}


/* Funzione che serve a recuperare tutte le categorie dei prodotti,
 * è utilizzata per i filtri in "ricerca_prodotti.php" e nell'aggiunta/modifica di
 * un prodotto da parte del negoziante */
function recuperaCategorie($pdo) {
    $stmt = $pdo->query("SELECT id, nome FROM categoria ORDER BY nome");
    return $stmt->fetchAll();
}