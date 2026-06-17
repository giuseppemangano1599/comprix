<?php
/* Controller che gestisce il reset della password tramite la domanda segreta.
 * Si occupa di verificare la risposta segreta (hash) e aggiornare la password.
 * La parte di recupero della domanda è delegata ad ajax/ajax_pw_dimenticata.php
 */
require_once "../includes/config.php";

$errore = "";
$successo = "";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../index.php");
    exit;
}

// Vengono presi e puliti i dati dal form per il recupero della password
$username_o_email = trim($_POST["username_or_email"] ?? "");
$risposta_utente = trim($_POST["secret_answer"] ?? "");
$nuova_password = $_POST["new_password"] ?? "";
$conferma_password = $_POST["confirm_password"] ?? "";
$tipo_utente = $_POST["user_type"] ?? "";

// Catena di validazioni lato server che previene input malevoli o malformati
if (empty($username_o_email) || empty($risposta_utente) || empty($nuova_password) || !in_array($tipo_utente, ["cliente", "negoziante"], true)) {
    $errore = "Tutti i campi sono obbligatori.";
} elseif ($nuova_password !== $conferma_password) {
    $errore = "Le password non coincidono.";
} elseif (!preg_match("/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,64}$/", $nuova_password)) {
    $errore = "La password deve contenere almeno una lettera minuscola, una maiuscola, un numero e essere tra 8 e 64 caratteri";
} else {
    try {
        // Query che recupera l'utente per verificare l'hash della risposta segreta
        $sql = "SELECT id, risposta_segreta_hash, 'cliente' as tipo_utente 
                FROM cliente 
                WHERE username = :user_c OR email = :email_c
                UNION ALL
                SELECT id, risposta_segreta_hash, 'negoziante' as tipo_utente 
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

        if ($utente && password_verify($risposta_utente, $utente["risposta_segreta_hash"])) {
            // Se la risposta è corretta, procede all'aggiornamento
            $pdo->beginTransaction();

            try {
                $nuova_pw_hs = password_hash($nuova_password, PASSWORD_DEFAULT);

                /* Controllo di sicurezza nel quale si forza il nome della tabella sui valori noti ("cliente" o "negoziante")
                 * Sto usando $utente["tipo_utente"] (quindi dal database) e $_POST["user_type"] perché (ad esempio) si potrebbe alterare la
                 * richiesta POST e quindi anziché aggiornare nella tabella "cliente" con id = 5 si potrebbe iniettare il nome della tabella 
                 * "negoziante" e quindi la password verrebbe cambiata quest'ultima tipologia di utente
                 */
                $nome_tabella = ($utente["tipo_utente"] === "cliente" || $utente["tipo_utente"] === "negoziante") ? $utente["tipo_utente"] : null;

                if ($nome_tabella) {
                    // Aggiornamento della password
                    $stmt_update = $pdo->prepare("UPDATE $nome_tabella SET password_hash = ? WHERE id = ?");
                    $stmt_update->execute([$nuova_pw_hs, $utente["id"]]);
                    $pdo->commit();
                    $successo = "La password è stata aggiornata con successo! Ora puoi effettuare il login.";
                }
            } catch (PDOException $e) {
                $pdo->rollBack();
                throw $e;
            }
        } else {
            $errore = "La risposta alla domanda segreta è errata o l'utente non esiste.";
        }
    } catch (PDOException $e) {
        $errore = "Si è verificato un errore durante l'aggiornamento delle credenziali. Riprova più tardi.";
    } catch (Exception $e) {
        $errore = "Si è verificato un errore durante l'aggiornamento delle credenziali. Riprova più tardi.";
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
}

// Torna sempre alla pagina per il recupero della password
header("Location: ../auth/pw_dimenticata.php");
exit;