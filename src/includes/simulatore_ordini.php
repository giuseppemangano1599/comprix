<?php
/* Questo script funge da simulatore dell'avanzamento degli ordini.
 * Data la natura del progetto, gli ordini sono finti ma per dare al sito 
 * una parvenza di verosimiglianza ho deciso di creare questo script. 
 * Viene incluso in "storico_ordini.php" e viene eseguito al caricamento della pagina degli ordini. 
 * Controlla gli ordini e ne fa avanzare lo stato 
 * automaticamente basandosi sul tempo trascorso dalla creazione dell'ordine.
 */

// Configurazione dei tempi di simulazione (in secondi per test rapidi)
$sec_spedito = 30;    // Tempo per passare da "confermato" a "spedito"
$sec_consegnato = 60; // Tempo per passare da "spedito" a "consegnato"

if (isset($pdo)) {
    // Solo gli ordini del cliente corrente, mai quelli altrui.
    // $id_utente potrebbe non essere ancora definita nell'ordine di inclusione:
    // si ripiega sulla sessione, gli anonimi restano esclusi (null).
    $id_cliente_sim = $id_utente ?? $_SESSION["user_id"] ?? null;

    if ($id_cliente_sim === null) {
        return;
    }

    try {
        // Ordine da "confermato" a "spedito" (solo del cliente corrente)
        $stmt_sped = $pdo->prepare("UPDATE ordine
                    SET stato = 'spedito'
                    WHERE stato = 'confermato'
                    AND id_cliente = ?
                    AND data_ordine <= NOW() - INTERVAL $sec_spedito SECOND");
        $stmt_sped->execute([$id_cliente_sim]);

        // Ordine da "spedito" a "consegnato" (solo del cliente corrente)
        $stmt_cons = $pdo->prepare("UPDATE ordine
                    SET stato = 'consegnato'
                    WHERE stato = 'spedito'
                    AND id_cliente = ?
                    AND data_ordine <= NOW() - INTERVAL $sec_consegnato SECOND");
        $stmt_cons->execute([$id_cliente_sim]);

    } catch (Exception $e) {
        // Il simulatore non deve mai rompere la pagina che lo include
        error_log("Simulatore ordini: " . $e->getMessage());
    }
}
?>