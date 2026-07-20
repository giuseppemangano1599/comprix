<?php
/* Controller per la gestione dei metodi di pagamento.
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
$tipo = $_POST["tipo"] ?? "";
$id_pagamento = isset($_POST["id_pag"]) ? (int)$_POST["id_pag"] : 0;

// Array che conterrà i dati da convertire in JSON
$dati_json = [];

if ($azione === "inserisci" || $azione === "aggiorna") {

    if (empty($descrizione) || strlen($descrizione) > 70) {
        $errore = "Inserisci una descrizione valida (es. 'Carta personale', sono consentiti un massimo di  70 caratteri).";
    } elseif (!in_array($tipo, ["carta_credito", "carta_debito", "paypal", "bonifico", "contrassegno"])) {
        $errore = "Il tipo di pagamento non è valido.";
    }

    if (empty($errore)) {
        if ($tipo === "carta_credito" || $tipo === "carta_debito") {
            $numero = str_replace(" ", "", $_POST["numero_carta"] ?? "");
            $scadenza = $_POST["data_scadenza"] ?? "";
            $cvv = $_POST["cvv"] ?? "";
            $titolare = trim($_POST["nome_titolare"] ?? "");

            if (!preg_match("/^\d{16}$/", $numero)) {
                $errore = "Il numero carta non è valido, deve essere composto da 16 cifre.";
            } elseif (!preg_match("/^\d{2}\/\d{2}$/", $scadenza)) {
                $errore = "La data di scadenza non è valida (Usa formato MM/AA).";
            } elseif (!preg_match("/^\d{3,4}$/", $cvv)) {
                $errore = "Il codice CVV non è valido, deve essere composto da 3 o 4 cifre.";
            } elseif (empty($titolare)) {
                $errore = "Il nome del titolare è obbligatorio.";
            }
            
            // Validazione della scadenza della carta
            if (empty($errore)) {
                $parti = explode("/", $scadenza);
                $mese = (int)$parti[0];
                $anno = (int)$parti[1];

                // Controlla se il mese è valido
                if ($mese < 1 || $mese > 12) {
                    $errore = "Mese scadenza non valido.";
                } else {
                    // Ottiene il mese e l'anno correnti
                    $mese_corrente = (int)date("m");
                    $anno_corrente = (int)date("y");

                    // Controlla se la carta è scaduta
                    if ($anno < $anno_corrente || ($anno == $anno_corrente && $mese < $mese_corrente)) {
                        $errore = "La carta è scaduta. Inserisci una carta valida.";
                    }
                }
            }

            // Preparazione dei dati JSON: solo ultime 4 cifre, scadenza e titolare.
            // Il CVV è validato ma mai conservato; il PAN intero non lascia mai questa richiesta.
            $dati_json = [
                "numero_carta" => substr($numero, -4),
                "data_scadenza" => $scadenza,
                "nome_titolare" => $titolare
            ];
        } elseif ($tipo === "paypal") {
            $email_pp = trim($_POST["email_paypal"] ?? "");
            if (!filter_var($email_pp, FILTER_VALIDATE_EMAIL)) {
                $errore = "L'e-mail PayPal non è valida.";
            }
            $dati_json = ["email_paypal" => $email_pp];
        } elseif ($tipo === "bonifico") {
            $iban = strtoupper(str_replace(" ", "", $_POST["iban"] ?? ""));
            $intestatario = trim($_POST["intestatario"] ?? "");

            if (!preg_match("/^[A-Z0-9]{15,34}$/", $iban)) {
                $errore = "Il codice IBAN non è valido.";
            } elseif (empty($intestatario)) {
                $errore = "L'intestatario del conto èp obbligatorio.";
            }
            $dati_json = ["iban" => $iban, "intestatario" => $intestatario];
        } elseif ($tipo === "contrassegno") {
            $dati_json = ["note" => "Pagamento alla consegna"];
        }
    }
}

if (empty($errore)) {
    try {
        $json_str = json_encode($dati_json);

        if ($azione === "inserisci") {
            $stmt = $pdo->prepare("INSERT INTO metodo_pagamento (id_cliente, tipo, descrizione, dati_pagamento) VALUES (?, ?, ?, ?)");
            $stmt->execute([$id_utente, $tipo, $descrizione, $json_str]);
            $successo = "Il metodo di pagamento è stato aggiunto.";
        } elseif ($azione === "aggiorna") {
            $check = $pdo->prepare("SELECT id FROM metodo_pagamento WHERE id = ? AND id_cliente = ?");
            $check->execute([$id_pagamento, $id_utente]);

            if ($check->fetch()) {
                $stmt = $pdo->prepare("UPDATE metodo_pagamento SET descrizione = ?, dati_pagamento = ? WHERE id = ?");
                $stmt->execute([$descrizione, $json_str, $id_pagamento]);
                $successo = "Il metodo di pagamento è stato aggiornato.";
            } else {
                $errore = "Si è verificato un errore durante l'aggiornamento. Riprova più tardi.";
            }
        } elseif ($azione === "elimina") {
            $check = $pdo->prepare("SELECT id FROM metodo_pagamento WHERE id = ? AND id_cliente = ?");
            $check->execute([$id_pagamento, $id_utente]);

            if ($check->fetch()) {
                $stmt = $pdo->prepare("DELETE FROM metodo_pagamento WHERE id = ?");
                $stmt->execute([$id_pagamento]);
                $successo = "Il metodo di pagamento è stato eliminato";
            } else {
                $errore = "Si è verificato un errore durante l'eliminazione. Riprova più tardi.";
            }
        }
    } catch (PDOException $e) {
        $errore = "Si è verificato un errore durante l'esecuzione dell'azione richiesta. Riprova più tardi.";
    }
}

/* Gestione del reindirizzamento in caso di successo o di errore.
 * Salva i messaggi di successo o errore nella sessione
 */
if (!empty($successo)) {
    $_SESSION["success"] = $successo;
    if ($azione === "aggiorna") {
        header("Location: ../account/modifica_pagamento.php?id=" . $id_pagamento);
    } else {
        header("Location: ../account/profilo.php");
    }
    exit;
}

if (!empty($errore)) {
    $_SESSION["error"] = $errore;
    if ($azione !== "elimina") {
        // PAN e CVV non vengono mai conservati in sessione
        $dati_sicuri = $_POST;
        unset($dati_sicuri["numero_carta"], $dati_sicuri["cvv"]);
        $_SESSION["dati_form"] = $dati_sicuri;
    }
    if ($azione === "aggiorna") {
        header("Location: ../account/modifica_pagamento.php?id=" . $id_pagamento);
    } elseif ($azione === "inserisci") {
        header("Location: ../account/aggiungi_pagamento.php");
    } else {
        header("Location: ../account/profilo.php");
    }
    exit;
}

// Torna sempre alla pagina del profilo dell'utente
header("Location: ../account/profilo.php");
exit;
