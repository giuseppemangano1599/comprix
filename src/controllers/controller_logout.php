<?php
/* Controller che gestisce la disconnessione dell'utente.
 * Termina completamente la sessione e reindirizza alla homepage del sito.
 */

require_once "../includes/config.php";

if (utenteAutenticato()){
    // Rimuove tutte le variabili di sessione e distrugge la sessione corrente sul server
    session_unset();
    session_destroy();

    // Dopodiché avvia una sessione nuova
    session_start();
}

// Reindirizza l'utente sealla homepage del sito
header("Location: ../index.php");
exit;
?>