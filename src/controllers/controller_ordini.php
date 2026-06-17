<?php
/* Controller che gestisce l'annullamento di un prodotto.
 * Si sarebbe potuto integrare a controller_checkout.php ma
 * avrebbe richiesto la ristrutturazione di tutto il file. 
 */
require_once "../includes/config.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../auth/index.php");
    exit;
}

$id_utente = $_SESSION["user_id"];
$azione = $_POST['azione'] ?? '';

if ($azione === "annulla_ordine") {
    $id_ordine = (int)$_POST['id_ordine'];

    $sql = "UPDATE ordine 
            SET stato = 'annullato' 
            WHERE id = ? 
            AND id_cliente = ? 
            AND stato = 'confermato'";
            
    $stmt = $pdo->prepare($sql);
    $esito = $stmt->execute([$id_ordine, $id_utente]);

    // Controlla se l'ordine è stato effettivamente annullato
    if ($stmt->rowCount() > 0) {
        $_SESSION["success"] = "L'ordine è stato annullato con successo.";
    } else {
        // Se rowCount è 0 vuol dire che l'ordine è già stato spedito e/o consegnato.
        $_SESSION["error"] = "Impossibile annullare l'ordine, potrebbe essere già stato spedito, contatta l'assistenza";
    }
}

// Torna alla pagina dello storico degli ordini
header("Location: ../orders/storico_ordini.php");
exit;
?>