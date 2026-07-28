<?php
/* Controller che gestisce la registrazione dell'utente al sito, 
 * i dati del form vengono puliti e validati e, se i controlli passano, 
 * l'utente viene registrato nel database.
 */
require_once "../includes/config.php";

$errore = "";
$successo = "";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../index.php");
    exit;
}

// Vengono presi e puliti i dati dal form di registrazione
$username = trim($_POST["username"] ?? "");
$email = strtolower(trim($_POST["email"] ?? ""));
$password = $_POST["password"] ?? "";
$conferma_password = $_POST["confirm_password"] ?? "";
$tipo_utente = $_POST["user_type"] ?? "";
$domanda_segreta = $_POST["secret_question"] ?? "";
$risposta_segreta = $_POST["secret_answer"] ?? "";

// Catena di validazioni lato server che previene input malevoli o malformati
if (empty($username) || empty($email) || empty($password) || !in_array($tipo_utente, ["cliente", "negoziante"], true) || empty($domanda_segreta) || empty($risposta_segreta)) {
    $errore = "Tutti i campi sono obbligatori.";
} elseif (!preg_match("/^[a-zA-Z0-9._]{3,50}$/", $username)) {
    $errore = "L'username può contenere solo lettere, numeri, punto e underscore e deve essere tra 3 e 50 caratteri.";
} elseif (strlen($email) > 254) {
    $errore = "L'indirizzo e-mail è troppo lungo, sono consentiti un massimo di 254 caratteri.";
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || !preg_match("/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/", $email)) {
    $errore = "L'indirizzo e-mail inserito non è valido.";
} elseif (!preg_match("/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,64}$/", $password)) {
    $errore = "La password deve contenere almeno una lettera minuscola, una maiuscola, un numero e essere tra 8 e 64 caratteri.";
} elseif ($password !== $conferma_password) {
    $errore = "Le password non coincidono.";
} elseif (!in_array($domanda_segreta, DOMANDE_SEGRETE)) {
    $errore = "La domanda segreta non è valida.";
} elseif (!preg_match("/^.{1,100}$/s", $risposta_segreta)) {
    $errore = "La risposta segreta deve essere compresa tra 1 e 100 caratteri.";
} else {
    /* Controllo dell'unicità dell'username e dell'e-mail.
    * Si effettua comunque un controllo nonostante ci sia "ajax_controlla_disponibilita.php"
    * poiché potrebbe essere bypassato disattivando JavaScript dal browser. */
    try {
        // Query che cerca sia nella tabella cliente che negoziante
        // (placeholder distinti: con prepare nativi lo stesso nome riusato due volte dà HY093)
        $sql = "SELECT username, email
                FROM cliente
                WHERE username = :username_c OR email = :email_c
                UNION ALL
                SELECT username, email
                FROM negoziante
                WHERE username = :username_n OR email = :email_n
                LIMIT 1";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([":username_c" => $username, ":email_c" => $email, ":username_n" => $username, ":email_n" => $email]);

        // Se trova una riga, significa che un valore in quel campo è già registrato
        $esistente = $stmt->fetch();

        if ($esistente) {
            if ($esistente["username"] === $username) {
                $errore = "Questo username è già registrato. Scegline un altro.";
            } elseif (strtolower($esistente["email"]) === $email) {
                $errore = "Questo indirizzo e-mail è già registrato. Scegline un altro.";
            } else {
                $errore = "I dati inseriti risultano già registrati.";
            }
        }
    } catch (Exception $e) {
        $errore = "Si è verificato un errore durante la verifica. Riprova più tardi.";
    }

    /* Registrazione dell'utente nel database se non vengono rilevati errori.
    * La registrazione viene effettuata con una transazione per sicurezza affinché 
    * si eviti il raro ma possibile caso in cui due utenti si registrino con
    * (ad esempio) lo stesso username o la stessa e-mail. */
    if (empty($errore)) {
        try {
            $pdo->beginTransaction();

            // Hashing della password e della risposta segreta
            $hs_password = password_hash($password, PASSWORD_DEFAULT);
            $hs_risposta = password_hash($risposta_segreta, PASSWORD_DEFAULT);

            // Controllo di sicurezza nel quale si forza il nome della tabella sui valori noti ("cliente" o "negoziante")
            $nome_tabella = ($tipo_utente === "cliente" || $tipo_utente === "negoziante") ? $tipo_utente : null;

            if ($nome_tabella) {
                // Inserimento del nuovo utente
                $stmt = $pdo->prepare("INSERT INTO $nome_tabella (username, email, password_hash, domanda_segreta, risposta_segreta_hash) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$username, $email, $hs_password, $domanda_segreta, $hs_risposta]);
                $pdo->commit();
                $successo = "La registrazione è avvenuta con successo! Ora puoi effettuare il login.";
            } else {
                $errore = "Si è verificato un errore durante la registrazione. Riprova più tardi.";
            }
        } catch (PDOException $e) {
            $pdo->rollBack();
            $errore = "Si è verificato un errore durante la registrazione. Riprova più tardi.";
        }
    }
}

/* Gestione del reindirizzamento in caso di successo o di errore.
 * Salva i messaggi di successo o errore nella sessione
 */
if (!empty($successo)) {
    $_SESSION["success"] = $successo;
}

if (!empty($errore)) {
    $_SESSION["error"] = $errore;
    // Salva i dati ricevuti tramite POST nella sessione (tranne la password e la risposta segreta) per ripopolare il form
    $_SESSION["dati_form"] = [
        "username" => $username,
        "email" => $email,
        "user_type" => $tipo_utente,
        "secret_question" => $domanda_segreta,
    ];
}

// Torna sempre alla pagina di registrazione dell'utente
header("Location: ../auth/registrazione.php");
exit;
