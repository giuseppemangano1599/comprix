<?php
// Script che effettua la chiamata AJAX che verifica la disponibilità dell'username e/o dell'e-mail in tempo reale
require_once "../includes/config.php";

// Controlla che provengano solo richieste POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../index.php");
    exit;
}

header("Content-Type: application/json");

/* Funzione che serve a controllare se l'username o l'e-mail sono disponibili
 * per la registrazione del nuovo utente 
 */
function verificaDisponibilita($pdo, $campo, $valore) {
    if ($campo === "email") {
        $valore = strtolower($valore);
    }

    /* Effettua una query usando l'operatore UNION per cercare in entrambe le tabelle utenti
     * Ho scelto di usare SELECT 1 perché è più efficiente, la query restituisce 1 se il valore è presente,
     * SELECT * restituirebbe una tabella intera, appesantento il carico computazionale.
     */
    $sql = "SELECT 1 FROM cliente WHERE $campo = :valore_c 
            UNION ALL 
            SELECT 1 FROM negoziante WHERE $campo = :valore_n 
            LIMIT 1";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([":valore_c" => $valore, ":valore_n" => $valore]);

    return !$stmt->fetch();
}

// Risposta di default
$risposta = [
    "successo" => true,
    "disponibile" => false,
    "messaggio" => "Richiesta non valida."
];

$campo = $_POST["campo"] ?? "";
$valore = trim($_POST["valore"] ?? "");

// Array di soli valori ammessi nei campi per aumentare la sicurezza ed evitare un'eventuale SQL Injection
$campi_ammessi = ["username", "email"];

if ($campo === "email") {
    $valore = strtolower($valore);
}

// Effettua una catena di validazioni dell'input prima di passarli alla query che fa il controllo della disponibilità
if (empty($campo) || !in_array($campo, $campi_ammessi, true)) {
    $risposta["messaggio"] = "Il campo da verificare non è valido.";
} elseif (empty($valore)) {
    $risposta["messaggio"] = "Il valore del campo non può essere vuoto.";
} elseif ($campo === "email" && !filter_var($valore, FILTER_VALIDATE_EMAIL)) {
    $risposta["messaggio"] = "E-mail non valida.";
} elseif ($campo === "username" && !preg_match("/^[a-zA-Z0-9._]{3,50}$/", $valore)) {
    $risposta["messaggio"] = "L'username può contenere solo lettere, numeri, punto e underscore e deve essere tra 3 e 50 caratteri.";
} else {
    try {
        if (verificaDisponibilita($pdo, $campo, $valore)) {
            // Se il valore è disponibile
            $risposta = [
                "successo" => true,
                "disponibile" => true,
                "messaggio"   => ucfirst($campo) . " disponibile."
            ];
        } else {
            // Se il valore è non è disponibile
            $risposta = [
                "successo" => true,
                "disponibile" => false,
                "messaggio"   => ucfirst($campo) . " già in uso."
            ];
        }
    } catch (Exception $e) {
        $risposta["messaggio"] = "Si è verificato un errore durante la verifica. Riprova più tardi.";
    }
}

echo json_encode($risposta);
exit;