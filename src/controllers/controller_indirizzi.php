<?php
/* Controller per la gestione degli indirizzi di spedizione.
 * Gestisce le operazioni di creazione, modifica ed eliminazione.
 */
require_once "../includes/config.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../account/profilo.php");
    exit;
}

if (!verificaCliente()) {
    header("Location: ../index.php");
    exit;
}

$id_utente = $_SESSION["user_id"];
$azione = $_POST["azione"] ?? "";
$errore = "";
$successo = "";

// Vengono presi e puliti i dati dal form
$descrizione = trim($_POST["descrizione"] ?? "");
$nome = trim($_POST["nome"] ?? "");
$cognome = trim($_POST["cognome"] ?? "");
$indirizzo = trim($_POST["indirizzo"] ?? "");
$citta = trim($_POST["citta"] ?? "");
$provincia = strtoupper(trim($_POST["provincia"] ?? ""));
$cap = trim($_POST["cap"] ?? "");
$telefono = trim($_POST["telefono"] ?? "");
$id_indirizzo = isset($_POST["id_indirizzo"]) ? (int)$_POST["id_indirizzo"] : 0;

// Catena di validazioni lato server che previene input malevoli o malformati
if ($azione === "inserisci" || $azione === "aggiorna") {
    if (empty($descrizione)) {
        $errore = "Inserisci una descrizione, sono consentiti un massimo di 70 caratteri.";
    } elseif (empty($nome) || !preg_match("/^[A-Za-zÀ-ÿ'\.\-\s]{1,50}$/u", $nome)) {
        $errore = "Il nome non è valido, sono consentiti un massimo di 50 caratteri.";
    } elseif (empty($cognome) || !preg_match("/^[A-Za-zÀ-ÿ'\.\-\s]{1,50}$/u", $cognome)) {
        $errore = "Il cognome non è valido, sono consentiti un massimo di 50 caratteri.";
    } elseif (empty($indirizzo) || !preg_match("/^[A-Za-z0-9À-ÿ\s,.'\-°\/()#]{3,150}$/u", $indirizzo)) {
        $errore = "L'indirizzo non è valido, sono consentiti un massimo di 150 caratteri.";
    } elseif (empty($citta) || !preg_match("/^[A-Za-zÀ-ÿ'\.\-\s]{1,50}$/u", $citta)) {
        $errore = "La città non è valida, sono consentiti un massimo di 50 caratteri.";
    } elseif (!preg_match("/^[A-Z]{2}$/", $provincia)) {
        $errore = "La provincia non è valida, usa la sigla di 2 lettere (es. PI).";
    } elseif (!preg_match("/^\d{5}$/", $cap)) {
        $errore = "Il CAP non è valido, deve essere di 5 cifre.";
    } elseif (!preg_match("/^[\d\+\-\s]{6,20}$/", $telefono)) {
        $errore = "Il numero di telefono non è valido.";
    }
}

// Se non ci sono errori di validazione, procede con l'azione richiesta
if (empty($errore)) {
    try {
        if ($azione === "inserisci") {
            $sql = "INSERT INTO indirizzo_spedizione (id_cliente, nome, cognome, indirizzo, citta, provincia, cap, telefono, descrizione) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$id_utente, $nome, $cognome, $indirizzo, $citta, $provincia, $cap, $telefono, $descrizione]);
            $successo = "Il nuovo indirizzo è stato inserito con successo!";
        } elseif ($azione === "aggiorna") {
            $check = $pdo->prepare("SELECT id FROM indirizzo_spedizione WHERE id = ? AND id_cliente = ?");
            $check->execute([$id_indirizzo, $id_utente]);

            if ($check->fetch()) {
                $sql = "UPDATE indirizzo_spedizione SET nome = ?, cognome = ?, indirizzo = ?, citta = ?, provincia = ?, cap = ?, telefono = ?, descrizione = ? WHERE id = ? AND id_cliente = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$nome, $cognome, $indirizzo, $citta, $provincia, $cap, $telefono, $descrizione, $id_indirizzo, $id_utente]);
                $successo = "L'indirizzo è stato aggiornato con successo!";
            } else {
                $errore = "L'indirizzo non è stato trovato.";
            }
        } elseif ($azione === "elimina") {
            // Logica di eliminazione
            $check = $pdo->prepare("SELECT id FROM indirizzo_spedizione WHERE id = ? AND id_cliente = ?");
            $check->execute([$id_indirizzo, $id_utente]);

            if ($check->fetch()) {
                $stmt = $pdo->prepare("DELETE FROM indirizzo_spedizione WHERE id = ?");
                $stmt->execute([$id_indirizzo]);
                $successo = "L'indirizzo è stato eliminato con successo!";
            } else {
                $errore = "L'indirizzo non è stato trovato.";
            }
        }
    } catch (PDOException $e) {
        $errore = "Si è verificato un errore durante l'esecuzione dell'azione richiesta. Riprova più tardi.";
    } catch (Exception $e){
        $errore = "Si è verificato un errore durante l'esecuzione dell'azione richiesta. Riprova più tardi.";
    }
}


/* Gestione del reindirizzamento in caso di successo o di errore.
 * Salva i messaggi di successo o errore nella sessione
 */
if (!empty($successo)) {
    $_SESSION["success"] = $successo;
    if ($azione === "aggiorna") {
        header("Location: ../account/modifica_indirizzo.php?id=" . $id_indirizzo);
    } else {
        header("Location: ../account/profilo.php");
    }
    exit;
}

if (!empty($errore)) {
    $_SESSION["error"] = $errore;
    if ($azione !== "elimina") {
        $_SESSION["dati_form"] = $_POST;
    }
    if ($azione === "aggiorna") {
        header("Location: ../account/modifica_indirizzo.php?id=" . $id_indirizzo);
    } elseif ($azione === "inserisci") {
        header("Location: ../account/aggiungi_indirizzo.php");
    } else {
        header("Location: ../account/profilo.php");
    }
    exit;
}

// Torna sempre alla pagina del profilo dell'utente
header("Location: ../account/profilo.php");
exit;
