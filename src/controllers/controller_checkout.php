<?php
/* Controller che gestisce il checkout e la creazione dell'ordine,
 * i dati vengono validati e, se i controlli passano, l'ordine viene creato
 * e il carrello svuotato.
 */
require_once "../includes/config.php";

$errore = "";
$successo = "";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../index.php");
    exit;
}

if (!verificaCliente()) {
    header("Location: ../index.php");
    exit;
}

if (!csrf_valido()) {
    $_SESSION["error"] = "Richiesta non valida o scaduta. Riprova.";
    header("Location: ../cart/checkout.php");
    exit;
}

// Recupero i dati dal form del checkout
$id_indirizzo = $_POST["id_indirizzo_salvato"] ?? "";
$id_pagamento = $_POST["id_pagamento_salvato"] ?? "";
$id_cliente = $_SESSION["user_id"];

// Validazione preliminare dei campi
if (empty($id_indirizzo) || empty($id_pagamento)) {
    $errore = "Devi selezionare un indirizzo di spedizione e un metodo di pagamento.";
} else {
    try {
        /* Verifica che l'indirizzo esista e appartenga all'utente
         * Recupera anche i dati per lo storico dell'ordine (nome, via, città, ecc.)
        */
        $stmt_indirizzo = $pdo->prepare("SELECT * FROM indirizzo_spedizione WHERE id = ? AND id_cliente = ?");
        $stmt_indirizzo->execute([$id_indirizzo, $id_cliente]);
        $dati_indirizzo = $stmt_indirizzo->fetch();

        // Verifica che il metodo di pagamento esista e appartenga all'utente
        $stmt_pagamento = $pdo->prepare("SELECT id FROM metodo_pagamento WHERE id = ? AND id_cliente = ?");
        $stmt_pagamento->execute([$id_pagamento, $id_cliente]);
        $dati_pagamento = $stmt_pagamento->fetch();

        if (!$dati_indirizzo) {
            $errore = "L'indirizzo di spedizione selezionato non è valido.";
        } elseif (!$dati_pagamento) {
            $errore = "Il metodo di pagamento selezionato non è valido.";
        } else {
            // Recupera articoli del carrello per calcolare totale e verificare stock
            $sql_carrello = "SELECT ec.id_prodotto, ec.quantita_aggiunta, p.prezzo, p.nome, p.quantita_disponibile, p.id_negoziante, c.id as id_carrello
                             FROM elemento_carrello AS ec 
                             JOIN carrello AS c ON ec.id_carrello = c.id 
                             JOIN prodotto AS p ON ec.id_prodotto = p.id
                             WHERE c.id_cliente = ?";
            $stmt_carrello = $pdo->prepare($sql_carrello);
            $stmt_carrello->execute([$id_cliente]);
            $prodotti = $stmt_carrello->fetchAll();

            if (empty($prodotti)) {
                $errore = "Il tuo carrello è vuoto.";
            } else {
                // Controlla la disponibilità dei prodotti nel magazzino prima di iniziare la transazione
                foreach ($prodotti as $prodotto) {
                    if ($prodotto["quantita_aggiunta"] > $prodotto["quantita_disponibile"]) {
                        $errore = "Il prodotto '" . $prodotto["nome"] . "' non è più disponibile nella quantità richiesta.";
                        break; 
                    }
                }
            }
        }
    } catch (PDOException $e) {
        $errore = "Si è verificato un errore durante la verifica dei dati relativi all'ordine. Riprova più tardi.";
    }

    /* Registrazione dell'ordine nel database se non vengono rilevati errori.
    * Viene effettuata una transazione per sicurezza affinché 
    * si eviti il caso in cui venga effettuato un ordine senza inserire 
    * i dati di pagamento e l'indirizzo di spedizione o viceversa.
    */
    if (empty($errore)) {
        try {
            $pdo->beginTransaction();

            // Calcola il totale dell'ordine
            $totale_ordine = 0;
            foreach ($prodotti as $prodotto) {
                $totale_ordine += ($prodotto["prezzo"] * $prodotto["quantita_aggiunta"]);
            }

            // Inserimento nella tabella ordine (con snapshot dei dati indirizzo)
            $sql_insert_ordine = "INSERT INTO ordine (
                id_cliente, id_metodo_pagamento, id_indirizzo_spedizione, totale, stato, 
                nome_destinatario, cognome_destinatario, telefono_destinatario, 
                indirizzo_destinatario, citta_destinatario, cap_destinatario, provincia_destinatario
            ) VALUES (
                :id_cliente, :id_pagamento, :id_indirizzo, :totale, 'confermato',
                :nome, :cognome, :telefono, 
                :indirizzo, :citta, :cap, :provincia
            )";

            $stmt_ins_ord = $pdo->prepare($sql_insert_ordine);
            $stmt_ins_ord->execute([
                ":id_cliente" => $id_cliente,
                ":id_pagamento" => $id_pagamento,
                ":id_indirizzo" => $id_indirizzo,
                ":totale" => $totale_ordine,
                ":nome" => $dati_indirizzo["nome"],
                ":cognome" => $dati_indirizzo["cognome"],
                ":telefono" => $dati_indirizzo["telefono"],
                ":indirizzo" => $dati_indirizzo["indirizzo"],
                ":citta" => $dati_indirizzo["citta"],
                ":cap" => $dati_indirizzo["cap"],
                ":provincia" => $dati_indirizzo["provincia"]
            ]);

            $id_nuovo_ordine = $pdo->lastInsertId();

            // Inserimento dei dettagli dell'ordine
            $sql_dettaglio = "INSERT INTO elemento_ordine (id_ordine, id_prodotto, id_negoziante, nome_prodotto, prezzo_unitario, quantita_aggiunta) 
                              VALUES (:id_ordine, :id_prodotto, :id_negoziante, :nome, :prezzo, :quantita)";
            $stmt_dettaglio = $pdo->prepare($sql_dettaglio);

            foreach ($prodotti as $prodotto) {
                $stmt_dettaglio->execute([
                    ":id_ordine" => $id_nuovo_ordine,
                    ":id_prodotto" => $prodotto["id_prodotto"],
                    ":id_negoziante" => $prodotto["id_negoziante"],
                    ":nome" => $prodotto["nome"],
                    ":prezzo" => $prodotto["prezzo"],
                    ":quantita" => $prodotto["quantita_aggiunta"]
                ]);
            }

            // Svuotamento del carrello
            if (isset($prodotti[0]["id_carrello"])) {
                $stmt_del = $pdo->prepare("DELETE FROM elemento_carrello WHERE id_carrello = ?");
                $stmt_del->execute([$prodotti[0]["id_carrello"]]);
            }

            $pdo->commit();
            $successo = "L'ordine è stato effettuato con successo!";

        } catch (PDOException $e) {
            $pdo->rollBack();
            // Controllo se l'errore deriva da un trigger SQL (es. stock insufficiente rilevato dal DB)
            $errore = "Si è verificato un errore durante la creazione dell'ordine";
        }
    }
}

/* Gestione del reindirizzamento */
if (!empty($successo)) {
    $_SESSION["success"] = $successo;
    // Reindirizza allo storico degli ordini
    header("Location: ../orders/storico_ordini.php");
    exit;
}

if (!empty($errore)) {
    $_SESSION["error"] = $errore;
    // Torna alla pagina di checkout in caso di errore
    header("Location: ../cart/checkout.php");
    exit;
}

// Torna sempre alla pagina di checkout in qualsiasi altro caso
header("Location: ../cart/checkout.php");
exit;
