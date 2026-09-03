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
const BASE_URL = "http://localhost:8080/";


// Definizione dell'ambiente di sviluppo o produzione, utile per la gestione degli errori e dei log
const ENVIRONMENT = "development"; // Cambiare con "production" quando si è in produzione


// Configurazione della gestione degli errori in base all'ambiente
if (ENVIRONMENT === "development") {
    ini_set("display_errors", 1);
    ini_set("display_startup_errors", 1);
    error_reporting(E_ALL);
} else {
    ini_set("display_errors", 0);
    ini_set("display_startup_errors", 0);
    error_reporting(E_ALL);
    ini_set("log_errors", 1);

    // Se la cartella logs non esiste, la crea al volo con i permessi corretti
    if (!is_dir(ROOT_PATH . "logs/")) {
        mkdir(ROOT_PATH . "logs/", 0755, true);
    }

    ini_set("error_log", ROOT_PATH . "logs/php_error.log");
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
 * Configurazione della costante che definisce l'array delle domande segrete 
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


// Parametri sicuri del cookie di sessione (vanno impostati prima di session_start)
$cookie_sicuro = (!empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off");
session_set_cookie_params([
    "lifetime" => 0,
    "path" => "/",
    "secure" => $cookie_sicuro,
    "httponly" => true,
    "samesite" => "Lax"
]);

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
 * Funzione alias di htmlspecialchars, preconfigurata con i flag di sicurezza ottimali
 * (ENT_QUOTES e ENT_HTML5) e codifica forzata in UTF-8. È progettata per gestire
 * in modo sicuro anche valori nulli.
 *
 * @param string|null $string La stringa grezza da sanificare.
 * @return string La stringa sicura pronta per essere stampata a video.
 */
function h(?string $string): string {
    return htmlspecialchars($string ?? "", ENT_QUOTES | ENT_HTML5, "UTF-8");
}


/**
 * Restituisce il token CSRF della sessione, creandolo se assente.
 * Va stampato come campo hidden "csrf_token" in ogni form che muta stato.
 *
 * @return string Il token CSRF della sessione corrente.
 */
function csrfToken(): string {
    if (empty($_SESSION["csrf_token"])) {
        $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
    }
    return $_SESSION["csrf_token"];
}


/**
 * Verifica il token CSRF inviato tramite POST contro quello di sessione.
 *
 * @return bool Restituisce true se il token è presente e valido, false altrimenti.
 */
function csrfValido(): bool {
    return isset($_POST["csrf_token"], $_SESSION["csrf_token"])
        && hash_equals($_SESSION["csrf_token"], (string)$_POST["csrf_token"]);
}


/**
 * Verifica se l'utente corrente ha una sessione di autenticazione attiva.
 * Controlla l'esistenza delle chiavi essenziali della sessione (user_id e user_type)
 * per determinare se l'utente ha effettuato il login.
 *
 * @return bool Restituisce true se l'utente è loggato, false altrimenti.
 */
function utenteAutenticato(): bool {
    return isset($_SESSION["user_id"]) && isset($_SESSION["user_type"]);
}


/**
 * Verifica se l'utente autenticato è un cliente.
 * Controlla l'esistenza delle chiavi essenziali della sessione tramite utenteAutenticato,
 * successivamente controlla che il contenuto di user_type sia una stringa contenente "cliente"
 * per determinare il tipo di utente.
 *
 * @return bool Restituisce true se l'utente è un cliente, false altrimenti.
 */
function verificaCliente(): bool {
    return utenteAutenticato() && $_SESSION["user_type"] === "cliente";
}


/**
 * Verifica se l'utente autenticato è un negoziante.
 * Controlla l'esistenza delle chiavi essenziali della sessione tramite utenteAutenticato,
 * successivamente controlla che il contenuto di user_type sia una stringa contenente "negoziante"
 * per determinare il tipo di utente.
 *
 * @return bool Restituisce true se l'utente è un negoziante, false altrimenti.
 */
function verificaNegoziante(): bool {
    return utenteAutenticato() && $_SESSION["user_type"] === "negoziante";
}


/**
 * Formatta un valore numerico in valuta Euro (€).
 * Trasforma il numero nel formato italiano con due decimali, 
 * virgola come separatore dei decimali e punto per le migliaia.
 *
 * @param float $price Il prezzo grezzo da formattare.
 * @return string Il prezzo formattato con il simbolo dell'Euro (es. "€ 1.234,56").
 */
function prezzoEuro(float $price): string {
    return "€ " . number_format($price, 2, ",", ".");
}


/**
 * Legge un messaggio flash dalla sessione e lo rimuove (lettura singola).
 *
 * @param string $chiave La chiave del messaggio ("error", "success").
 * @return string Il messaggio o stringa vuota se assente.
 */
function flash(string $chiave): string {
    $valore = $_SESSION[$chiave] ?? "";
    unset($_SESSION[$chiave]);
    return is_string($valore) ? $valore : "";
}


/**
 * Legge i dati del form salvati in sessione dopo un errore e li rimuove.
 *
 * @return array I dati per ripopolare il form o array vuoto se assenti.
 */
function datiPrecedenti(): array {
    $dati = $_SESSION["dati_form"] ?? [];
    unset($_SESSION["dati_form"]);
    return is_array($dati) ? $dati : [];
}


/**
 * Elimina un file caricato dagli upload (stessa semantica di prima:
 * cancella solo se il file esiste).
 *
 * @param string $percorso_relativo Percorso relativo alla docroot (es. "uploads/products/x.jpg").
 * @return void
 */
function eliminaFileUpload(string $percorso_relativo): void {
    $percorso_completo = "../" . $percorso_relativo;
    if (file_exists($percorso_completo)) {
        unlink($percorso_completo);
    }
}


/**
 * Restituisce il numero totale degli articoli nel carrello del cliente nel badge sulla navbar.
 * Verifica se l'utente è sia loggato che un cliente, successivamente interroga la base di dati
 * per richiedere il numero di articoli presenti nel carrello del cliente.
 * 
 * @param PDO $pdo L'istanza attiva della connessione al database.
 * @return int Il numero di articoli presenti nel carrello del cliente
 */
function articoliCarrelloNavbar(PDO $pdo): int {
    // Solo i clienti possono avere un (e unico) carrello
    if (!verificaCliente()) {
        return 0;
    }

    $stmt = $pdo->prepare("SELECT totale_elementi FROM carrello WHERE id_cliente = :id_cliente");
    $stmt->execute(["id_cliente" => $_SESSION["user_id"]]);

    // Restituisce il totale degli articoli presenti o 0 se carrello è vuoto
    return ($stmt->fetchColumn() ?: 0);
}


/**
 * Interroga la base di dati e restituisce, in ordine alfabetico, tutte le categorie
 * dei prodotti presenti, viene utilizzata sia per i filtri in "ricerca_prodotti.php",
 * sia nell'aggiunta/modifica di un prodotto da parte del negoziante.
 * 
 * @param PDO $pdo L'istanza attiva della connessione al database.
 * @return array L'array multidimensionale con dentro altri array associativi
 * che contengono le categorie dei prodotti salvate nella base di dati
 */
function recuperaCategorie(PDO $pdo): array {
    $stmt = $pdo->query("SELECT id, nome FROM categoria ORDER BY nome");
    return $stmt->fetchAll();
}


/**
 * Esegue una query SQL preparata in modo sicuro gestendo automaticamente
 * i parametri e il tipo di ritorno in base all'operazione.
 *
 * @param PDO $pdo L'istanza attiva della connessione al database.
 * @param string $sql La stringa SQL da eseguire (con i placeholder ? o :nome).
 * @param array $params Un array opzionale di parametri da iniettare nella query.
 * @return PDOStatement $stmt Restituisce l'oggetto che contiene la query eseguita, 
 * o il numero di righe modificate per UPDATE/DELETE.
 */
function dbQuery(PDO $pdo, string $sql, array $params = []): PDOStatement {
    // Prepara la query
    $stmt = $pdo->prepare($sql);
    
    // Esegue la query passando l'array di parametri
    $stmt->execute($params);

    return $stmt;
}
