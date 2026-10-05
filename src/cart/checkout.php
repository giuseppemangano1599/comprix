<?php
/* Pagina per il checkout dell'ordine dell'utente.
 * Visualizza gli indirizzi di spedizione e i metodi di
 * pagamento già salavti dall'utente tramite la pagina personale
 * del profilo e da la possibilità all'utente di finalizzare l'ordine.
 */
require_once "../includes/config.php";

// Reindirizza l'utente se non è autenticato e/o non è un cliente
if (!verificaCliente()) {
    header("Location: ../auth/login.php");
    exit;
}

$id_utente = $_SESSION["user_id"];

// Calcola il totale del carrello per mostrare il riepilogo
$stmt = $pdo->prepare("SELECT totale_prezzo FROM carrello WHERE id_cliente = ?");
$stmt->execute([$id_utente]);
$totale = $stmt->fetchColumn();

// Se il carrello è vuoto, rimanda alla ricerca dei prodotti
if (!$totale) {
    header("Location: ../products/ricerca_prodotti.php");
    exit;
}

// Recupera gli indirizzi e i metodi di pagamento salvati
$stmt_indirizzi = $pdo->prepare("SELECT * FROM indirizzo_spedizione WHERE id_cliente = ?");
$stmt_indirizzi->execute([$id_utente]);
$indirizzi = $stmt_indirizzi->fetchAll();

$stmt_pagamenti = $pdo->prepare("SELECT * FROM metodo_pagamento WHERE id_cliente = ?");
$stmt_pagamenti->execute([$id_utente]);
$pagamenti = $stmt_pagamenti->fetchAll();

// Verifica se l'utente può procedere all'acquisto
$puo_procedere = (count($indirizzi) > 0 && count($pagamenti) > 0);

// Recupera i messaggi di errore dalla sessione e pulisce la variabile per evitare messaggi persistenti
$errore = flash("error");

$nome_pagina = "Checkout dell'ordine";
include "../includes/header.php";
?>

<section>
    <h1>Riepilogo dell'ordine</h1>
    <p>Seleziona i dettagli per la spedizione e il pagamento.</p>

    <?php if ($errore): ?>
        <div class="alert alert-error"><?= h($errore); ?></div>
    <?php endif; ?>

    <form id="form_checkout" action="../controllers/controller_checkout.php" method="POST">
        <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">

        <div style="display: flex; gap: 30px; flex-wrap: wrap;">

            <div style="flex: 2; min-width: 300px;">

                <div style="border: 1px solid #ddd; padding: 20px; margin-bottom: 20px; border-radius: 5px;">
                    <h3>1. Indirizzo di spedizione</h3>

                    <?php if (count($indirizzi) > 0): ?>
                        <div class="form-group">
                            <label for="select_indirizzi">Scegli un indirizzo salvato:</label>
                            <select name="id_indirizzo_salvato" id="select_indirizzi" required class="form-control checkout-select">
                                <option value="">Seleziona un indirizzo di spedizione</option>
                                <?php foreach ($indirizzi as $indirizzo): ?>
                                    <option value="<?= $indirizzo["id"] ?>">
                                        [<?= h($indirizzo["descrizione"]) ?>] - <?= h($indirizzo["nome"] ." " . $indirizzo["cognome"]) ?> - <?= h($indirizzo["indirizzo"]) ?> - <?= h($indirizzo["citta"]) ?> (<?= h($indirizzo["provincia"]) ?>) <?= h($indirizzo["cap"]) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php else: ?>
                        <div class="warning-box">
                            <strong>Attenzione:</strong> Non hai indirizzi di spedizione salvati.
                            <br>
                            <a href="../account/profilo.php" class="link-underline">Vai al tuo profilo per aggiungerne uno</a> prima di continuare.
                        </div>
                    <?php endif; ?>
                </div>

                <div style="border: 1px solid #ddd; padding: 20px; border-radius: 5px;">
                    <h3>2. Metodo di pagamento</h3>

                    <?php if (count($pagamenti) > 0): ?>
                        <div class="form-group">
                            <label for="select_pagamenti">Scegli un metodo di pagamento salvato:</label>
                            <?php
                            // Mappatura delle etichette per visualizzare i nomi dei pagamenti in modo più elegante
                            $etichette_pagamento = [
                                "carta_credito" => "Carta di credito",
                                "carta_debito"  => "Carta di debito",
                                "paypal"        => "PayPal",
                                "bonifico"      => "Bonifico bancario",
                                "contrassegno"  => "Contrassegno (alla consegna)"
                            ];
                            ?>

                            <select name="id_pagamento_salvato" id="select_pagamenti" required class="form-control checkout-select">
                                <option value="">Seleziona un metodo di pagamento</option>
                                <?php foreach ($pagamenti as $pagamento): ?>
                                    <?php 
                                        // Cerca il nome nella mappa
                                        $nome_visualizzato = $etichette_pagamento[$pagamento["tipo"]];
                                    ?>
                                    <option value="<?= $pagamento["id"] ?>">
                                        [<?= h($pagamento["descrizione"]) ?>] - <?= h($nome_visualizzato) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php else: ?>
                        <div class="warning-box">
                            <strong>Attenzione:</strong> Non hai metodi di pagamento salvati.
                            <br>
                            <a href="../account/profilo.php" class="link-underline">Vai al tuo profilo per aggiungerne uno</a> prima di continuare.
                        </div>
                    <?php endif; ?>
                </div>

            </div>

            <div style="flex: 1; min-width: 250px;">
                <div style="background-color: #f9f9f9; padding: 25px; border: 1px solid #ddd; border-radius: 5px; position: sticky; top: 20px;">
                    <h3>Totale dell'ordine</h3>
                    <hr>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                        <span>Importo totale da pagare:</span>
                        <p style="color: green; margin: 0;"><strong><?= prezzoEuro($totale) ?></strong></p>
                    </div>

                    <?php if ($puo_procedere): ?>
                        <button type="submit" id="btn_conferma" class="btn-primary" style="width: 100%; padding: 15px; font-size: 1.1em; cursor: pointer; background-color: #007bff; color: white; border: none; border-radius: 5px;">
                            Conferma e paga
                        </button>
                    <?php else: ?>
                        <button type="button" disabled style="width: 100%; padding: 15px; font-size: 1.1em; background-color: #ccc; color: #666; border: none; border-radius: 5px; cursor: not-allowed;">
                            Aggiungi le informazioni per procedere
                        </button>
                        <p style="font-size: 0.9em; margin-top: 10px; color: #666;">
                            Manca l'indirizzo o il metodo di pagamento.
                        </p>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </form>
</section>

<?php include "../includes/footer.php"; ?>