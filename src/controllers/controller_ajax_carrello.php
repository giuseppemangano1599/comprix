<?php
/* Controller AJAX per la gestione del carrello.
 * Il controller è ibridato alle chiamate AJAX poiché le azioni sono dinamiche 
 * e restituiscono una risposta immediata senza dover ricaricare la pagina.
 * Gestisce aggiunta, modifica quantità e rimozione prodotti all'interno del carrello.
 */
require_once '../includes/config.php';

// Controlla che provengano solo richieste POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../index.php");
    exit;
}

header('Content-Type: application/json');

// Inizializzazione risposta standard
$risposta = [
    "successo" => false,
    "messaggio" => "Richiesta non valida",
    "dati" => []
];

$errore = "";
$azione = $_POST["azione"] ?? "";

$id_utente = $_SESSION["user_id"];

try {
    // Recupero o creo l'ID del carrello per l'utente corrente
    $stmt = $pdo->prepare("SELECT id FROM carrello WHERE id_cliente = ?");
    $stmt->execute([$id_utente]);
    $id_carrello = $stmt->fetchColumn();

    // Aggiunge uno o più elementi nel carrello
    if ($azione === "aggiungi") {

        $id_prodotto = (int)($_POST["id_prodotto"] ?? 0);
        $quantita = (int)($_POST["quantita"] ?? 1);

        if ($id_prodotto <= 0 || $quantita <= 0) {
            $errore = "I dati del prodotto non sono validi.";
        }

        if (empty($errore)) {
            $pdo->beginTransaction();

            // Controlla se il prodotto è già nel carrello
            $stmt = $pdo->prepare("SELECT quantita_aggiunta FROM elemento_carrello WHERE id_carrello = ? AND id_prodotto = ?");
            $stmt->execute([$id_carrello, $id_prodotto]);
            $qta_esistente = $stmt->fetchColumn();

            // Se c'è già lo stesso prodotto, aggiorna la quantità
            if ($qta_esistente) {
                $nuova_qta = $qta_esistente + $quantita;
                $stmt_upd = $pdo->prepare("UPDATE elemento_carrello SET quantita_aggiunta = ? WHERE id_carrello = ? AND id_prodotto = ?");
                $stmt_upd->execute([$nuova_qta, $id_carrello, $id_prodotto]);
            } else {
                // Altrimenti lo aggiunge
                $stmt_ins = $pdo->prepare("INSERT INTO elemento_carrello (id_carrello, id_prodotto, quantita_aggiunta) VALUES (?, ?, ?)");
                $stmt_ins->execute([$id_carrello, $id_prodotto, $quantita]);
            }

            $pdo->commit();
            $risposta["successo"] = true;
            $risposta["messaggio"] = "Il prodotto è stato aggiunto nel carrello!";
        } else {
            $risposta["messaggio"] = $errore;
        }
    }

    // Aggiorna la quantità di un prodotto nel carrello (viene utilizzata nella pagina del carrello)
    elseif ($azione === "aggiorna") {
        $id_prodotto = (int)($_POST["id_prodotto"] ?? 0);
        $quantita = (int)($_POST["quantita"] ?? 0);

        if ($id_prodotto <= 0) {
            $errore = "L'ID del prodotto non è valido.";
        }

        if (empty($errore)) {
            $pdo->beginTransaction();

            if ($quantita <= 0) {
                // Se la quantità è 0, rimuove il prodotto
                $stmt_delete = $pdo->prepare("DELETE FROM elemento_carrello WHERE id_carrello = ? AND id_prodotto = ?");
                $stmt_delete->execute([$id_carrello, $id_prodotto]);
                $risposta["messaggio"] = "Il prodotto è stato rimosso dal carrello.";
            } else {
                // Aggiorna la quantità
                $stmt_upd = $pdo->prepare("UPDATE elemento_carrello SET quantita_aggiunta = ? WHERE id_carrello = ? AND id_prodotto = ?");
                $stmt_upd->execute([$quantita, $id_carrello, $id_prodotto]);

                // Calcolo del totale
                $stmt_prz = $pdo->prepare("SELECT prezzo FROM prodotto WHERE id = ?");
                $stmt_prz->execute([$id_prodotto]);
                $prezzo_unitario = $stmt_prz->fetchColumn();

                $risposta["dati"]["totale_riga"] = prezzoEuro($prezzo_unitario * $quantita);
                $risposta["messaggio"] = "Il totale del carrello è stato aggiornato.";
            }

            $pdo->commit();
            $risposta["successo"] = true;
        } else {
            $risposta["messaggio"] = $errore;
        }
    }

    // Rimuove un prodotto dal carrello
    elseif ($azione === "rimuovi") {
        $id_prodotto = (int)($_POST["id_prodotto"] ?? 0);

        if ($id_prodotto > 0) {
            $pdo->beginTransaction();
            $stmt_delete = $pdo->prepare("DELETE FROM elemento_carrello WHERE id_carrello = ? AND id_prodotto = ?");
            $stmt_delete->execute([$id_carrello, $id_prodotto]);
            $pdo->commit();

            $risposta["successo"] = true;
            $risposta["messaggio"] = "Il prodotto è stato rimosso dal carrello.";
        } else {
            $risposta["messaggio"] = "L'ID del prodotto non è valido.";
        }
    } else {
        $risposta["messaggio"] = "Azione non valida.";
    }

    /* Recupero dei dati aggiornati (comune a tutte le azioni)
     * Legge i totali dalla tabella carrello che sono stati aggiornati
     */
    $stmt_totali = $pdo->prepare("SELECT totale_elementi, totale_prezzo FROM carrello WHERE id = ?");
    $stmt_totali->execute([$id_carrello]);
    $dati_carrello = $stmt_totali->fetch();

    $risposta["dati"]["conteggio_carrello"] = (int)($dati_carrello["totale_elementi"] ?? 0);
    $risposta["dati"]["totale_carrello"] = prezzoEuro($dati_carrello["totale_prezzo"] ?? 0);
} catch (PDOException $e) {
    $pdo->rollBack();
    $risposta["messaggio"] = "Si è verificato un errore durante l'esecuzione dell'azione richiesta. Riprova più tardi.";
} catch (Exception $e) {
    $risposta["messaggio"] = "Si è verificato un errore durante l'esecuzione dell'azione richiesta. Riprova più tardi.";
}

echo json_encode($risposta);
exit;
