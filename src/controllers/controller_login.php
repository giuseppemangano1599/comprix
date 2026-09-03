<?php
/* Controller che gestisce l'autenticazione dell'utente al sito, 
 * i dati del form vengono puliti e validati e, se i controlli passano, 
 * l'utente viene autenticato e può navigare nella parte protetta del sito, 
 * potendo effettuare ordini se cliente, vendere oggetti se negoziante.
 */
require_once "../includes/config.php";
$errore = "";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../index.php");
    exit;
}

if (!csrfValido()) {
    $_SESSION["error"] = "Richiesta non valida o scaduta. Riprova.";
    header("Location: ../auth/login.php");
    exit;
}

$username_o_email = trim($_POST["username_or_email"] ?? "");
$password = $_POST["password"] ?? "";

// Catena di validazioni lato server che previene input malevoli o malformati
if (empty($username_o_email) || empty($password)) {
    $errore = "Tutti i campi sono obbligatori.";
} elseif (strlen($username_o_email) > 254 || strlen($password) > 64) {
    $errore = "Uno dei campi inseriti è troppo lungo.";
} else {
    try {
        // Ricerca in entrambe le tabelle con un'unica query tramite l'operatore UNION
        $sql = "SELECT id, username, password_hash, 'cliente' AS tipo_utente 
                FROM cliente 
                WHERE username = :user_c OR email = :email_c
                UNION ALL
                SELECT id, username, password_hash, 'negoziante' AS tipo_utente 
                FROM negoziante 
                WHERE username = :user_n OR email = :email_n
                LIMIT 1";

        $stmt = $pdo->prepare($sql);

        $parametri = [
            ":user_c"   => $username_o_email,
            ":email_c"  => strtolower($username_o_email),
            ":user_n"   => $username_o_email,
            ":email_n"  => strtolower($username_o_email)
        ];

        $stmt->execute($parametri);
        $utente = $stmt->fetch();

        // Verifica della password se l'utente risulta nel database
        if ($utente && password_verify($password, $utente["password_hash"])) {
            // Nuovo ID di sessione contro la fixation (conserva i dati di sessione)
            session_regenerate_id(true);
            // Imposta le variabili di sessione per l'autenticazione
            $_SESSION["user_id"] = $utente["id"];
            $_SESSION["username"] = $utente["username"];
            $_SESSION["user_type"] = $utente["tipo_utente"];

            if ($utente["tipo_utente"] === "cliente") {
                header("Location: ../index.php");
            } else {
                header("Location: ../seller/pannello_negoziante.php");
            }
            exit;
        } else {
            $errore = "Le credenziali non sono valide. Riprova.";
        }
    } catch (PDOException $e) {
        $errore = "Si è verificato un errore durante l'autenticazione. Riprova più tardi.";
    }
}

/* Gestione del reindirizzamento in caso di successo o di errore.
 * Salva i messaggi di errore nella sessione.
 */
if (!empty($errore)) {
    // Salva il messaggio di errore nella sessione
    $_SESSION["error"] = $errore;
    $_SESSION["dati_form"] = [
        "username_or_email" => $username_o_email
    ];
}

// Torna sempre alla pagina di accesso dell'utente
header("Location: ../auth/login.php");
exit;
