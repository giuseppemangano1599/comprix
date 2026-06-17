<?php
// Script che effettua la chiamata AJAX per il recupero della domanda segreta
require_once "../includes/config.php";

// Controlla che provengano solo richieste POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../index.php");
    exit;
}

// Specifica che i dati ricevuti sono codificati in JSON
header("Content-Type: application/json");

// Risposta di default
$risposta = [
    "success" => false,
    "message" => "La richiesta non è valida."
];

$username_o_email = trim($_POST["username_or_email"] ?? "");
$tipo_utente = $_POST["user_type"] ?? "";

// Catena di validazioni prima di interrogare il database
if (empty($username_o_email) || empty($tipo_utente)) {
    $risposta["message"] = "Tutti i campi sono obbligatori.";
} elseif (!in_array($tipo_utente, ["cliente", "negoziante"], true)) {
    $risposta["message"] = "Il tipo di account non è valido.";
} else {
    try {
        // Query per recuperare la domanda segreta.
        $stmt = $pdo->prepare("SELECT domanda_segreta
                               FROM $tipo_utente
                               WHERE username = ?
                               OR email = ?");
        $stmt->execute([$username_o_email, strtolower($username_o_email)]);
        $utente = $stmt->fetch();

        if ($utente && !empty($utente["domanda_segreta"])) {
            // Se l'utente esiste
            $risposta = [
                "success" => true,
                "domanda" => $utente["domanda_segreta"]
            ];
        } else {
            // Se l'utente non viene trovato o non ha impostato la domanda
            $risposta["message"] = "L'utente non è stato trovato o la domanda segreta non è stata impostata.";
        }
    } catch (Exception $e) {
        $risposta["message"] = "Si è verificato un errore durante il recupero della domanda segreta. Riprova più tardi.";
    }
}

echo json_encode($risposta);
exit;