<?php
/* Controller per la gestione dei dati dell'account utente.
 * Gestisce le operazioni di modifica delle credenziali ed eliminazione dell'account.
 */
require_once "../includes/config.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../account/profilo.php");
    exit;
}

if (!utenteAutenticato()) {
    header("Location: ../auth/login.php");
    exit;
}

$tipo_utente = $_SESSION["user_type"] ?? "";
if (!in_array($tipo_utente, ["cliente", "negoziante"], true)) {
    header("Location: ../auth/login.php");
    exit;
}

$id_utente = $_SESSION["user_id"];
$azione = $_POST["azione"] ?? "";
$errore = "";
$successo = "";

// Vengono presi e puliti i dati dal form

// Dati per aggiorna_dati
$username = trim($_POST["username"] ?? "");
$email = strtolower(trim($_POST["email"] ?? ""));

// Dati per aggiorna_sicurezza
$tipo_sicurezza = $_POST["tipo_sicurezza"] ?? "";
$vecchia_password = $_POST["old_password"] ?? "";
$nuova_password = $_POST["new_password"] ?? "";
$confirm_password = $_POST["confirm_password"] ?? "";
$verifica_password = $_POST["verify_password"] ?? "";
$domanda = $_POST["domanda_segreta"] ?? "";
$risposta = trim($_POST["risposta_segreta"] ?? "");

// Dati per cancella_account
$cancellazione = $_POST["delete_account_password"] ?? "";


// Catena di validazioni lato server che previene input malevoli o malformati
if ($azione === "aggiorna_dati") {
    if (empty($username) || !preg_match("/^[a-zA-Z0-9._]{3,20}$/", $username)) {
        $errore = "L'username può contenere solo lettere, numeri, punto e underscore e deve essere tra 3 e 50 caratteri.";
    } elseif (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errore = "L'indirizzo e-mail inserito non è valido.";
    }
} elseif ($azione === "aggiorna_sicurezza") {
    if ($tipo_sicurezza === "password") {
        if (empty($vecchia_password)) {
            $errore = "Devi inserire la tua password attuale.";
        } elseif (!preg_match("/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,64}$/", $nuova_password)) {
            $errore = "La password deve contenere almeno una lettera minuscola, una maiuscola, un numero e essere tra 8 e 64 caratteri.";
        } elseif ($nuova_password !== $confirm_password) {
            $errore = "Le password non coincidono.";
        }
    } elseif ($tipo_sicurezza === "domanda") {
        if (empty($verifica_password)) {
            $errore = "Devi inserire la password per confermare.";
        } elseif (!in_array($domanda, DOMANDE_SEGRETE)) {
            $errore = "La domanda di sicurezza non è valida.";
        } elseif (empty($risposta)) {
            $errore = "La risposta segreta deve essere compresa tra 1 e 100 caratteri.";
        }
    } else {
        $errore = "Si è verificato un errore durante l'esecuzione dell'azione richiesta. Riprova più tardi.";
    }
} elseif ($azione === "cancella_account") {
    if (empty($cancellazione)) {
        $errore = "Inserisci la password per confermare l'eliminazione dell'account.";
    }
} else {
    $errore = "Azione non riconosciuta.";
}


// Se non ci sono errori di validazione, procede con l'azione richiesta
if (empty($errore)) {
    try {
        if ($azione === "aggiorna_dati") {
            // Controllo della disponibilità dell'username
            $sql = "SELECT id FROM $tipo_utente WHERE (username = ? OR email = ?) AND id != ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$username, $email, $id_utente]);
            
            if ($stmt->fetch()) {
                $errore = "L'username o l'e-mail sono già in uso da un altro utente.";
            } else {
                $sql_upd = "UPDATE $tipo_utente SET username = ?, email = ? WHERE id = ?";
                $stmt_upd = $pdo->prepare($sql_upd);
                $stmt_upd->execute([$username, $email, $id_utente]);

                $_SESSION["username"] = $username; 
                $successo = "I tuoi dati personali sono stati aggiornati con successo!";
            }

        } elseif ($azione === "aggiorna_sicurezza") {
            $stmt = $pdo->prepare("SELECT password_hash FROM $tipo_utente WHERE id = ?");
            $stmt->execute([$id_utente]);
            $utente = $stmt->fetch();

            if ($tipo_sicurezza === "password") {
                if (!password_verify($vecchia_password, $utente["password_hash"])) {
                    $errore = "La password attuale non è corretta.";
                } else {
                    $new_hash = password_hash($nuova_password, PASSWORD_DEFAULT);
                    $stmt_upd = $pdo->prepare("UPDATE $tipo_utente SET password_hash = ? WHERE id = ?");
                    $stmt_upd->execute([$new_hash, $id_utente]);
                    $successo = "La password è stat modificata con successo!";
                }
            } elseif ($tipo_sicurezza === "domanda") {
                if (!password_verify($verifica_password, $utente["password_hash"])) {
                    $errore = "La password è errata, non è stato possibile cambiare la domanda segreta.";
                } else {
                    $risposta_hash = password_hash($risposta, PASSWORD_DEFAULT);
                    $stmt_upd = $pdo->prepare("UPDATE $tipo_utente SET domanda_segreta = ?, risposta_segreta_hash = ? WHERE id = ?");
                    $stmt_upd->execute([$domanda, $risposta_hash, $id_utente]);
                    $successo = "La domanda segreta è stata aggiornata!";
                }
            }

        } elseif ($azione === "cancella_account") {
            $stmt = $pdo->prepare("SELECT password_hash FROM $tipo_utente WHERE id = ?");
            $stmt->execute([$id_utente]);
            $hash = $stmt->fetchColumn();

            if (password_verify($cancellazione, $hash)) {
                $stmt_del = $pdo->prepare("DELETE FROM $tipo_utente WHERE id = ?");
                $stmt_del->execute([$id_utente]);                
                session_destroy();
                header("Location: ../index.php");
                exit;
            } else {
                $errore = "La password è errata, non è stato possibile procedere con l'eliminazione";
            }
        }
    } catch (PDOException $e) {
        $errore = "Si è verificato un errore durante l'esecuzione dell'azione richiesta. Riprova più tardi.";
    } catch (Exception $e) {
        $errore = "Si è verificato un errore durante l'esecuzione dell'azione richiesta. Riprova più tardi.";
    }
}


/* Gestione del reindirizzamento in caso di successo o di errore.
 * Salva i messaggi di successo o errore nella sessione
 */
if (!empty($successo)) {
    $_SESSION["success"] = $successo;
    header("Location: ../account/profilo.php");
    exit;
}

if (!empty($errore)) {
    $_SESSION["error"] = $errore;
    header("Location: ../account/profilo.php");
    exit;
}

// Torna sempre alla pagina del profilo dell'utente
header("Location: ../account/profilo.php");
exit;