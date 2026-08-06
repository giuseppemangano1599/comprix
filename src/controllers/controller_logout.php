<?php
/* Controller che gestisce la disconnessione dell'utente.
 * Termina completamente la sessione e reindirizza alla homepage del sito.
 */

require_once "../includes/config.php";

if (utenteAutenticato()){
    // Rimuove tutte le variabili di sessione e distrugge la sessione corrente sul server
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), "", time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
    }
    session_unset();
    session_destroy();

    // Dopodiché avvia una sessione nuova con un ID inedito
    session_start();
    session_regenerate_id(true);
}

// Reindirizza l'utente all'homepage del sito
header("Location: ../index.php");
exit;
?>
