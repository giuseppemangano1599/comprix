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
    try {
        // Ordine da "confermato" a "spedito"
        $pdo->exec("UPDATE ordine 
                    SET stato = 'spedito' 
                    WHERE stato = 'confermato' 
                    AND data_ordine <= NOW() - INTERVAL $sec_spedito SECOND");

        // Ordine da "spedito" a "consegnato"
        $pdo->exec("UPDATE ordine
                    SET stato = 'consegnato' 
                    WHERE stato = 'spedito' 
                    AND data_ordine <= NOW() - INTERVAL $sec_consegnato SECOND");

    } catch (Exception $e) {
        die("Errore durante la connessione al database: " . $e->getMessage());
    }
}
?>