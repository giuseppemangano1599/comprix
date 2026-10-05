<?php
// Pagina per aggiungere un nuovo metodo di pagamento.
require_once "../includes/config.php";

// Reindirizza l'utente se non è autenticato e/o non è un cliente
if (!verificaCliente()) {
    header("Location: ../index.php");
    exit;
}

// Recupera i messaggi di successo o errore dalla sessione e pulisce le variabili per evitare messaggi persistenti
$errore = flash("error");
$dati_form = datiPrecedenti();

$nome_pagina = "Aggiungi un nuovo metodo di pagamento";
include "../includes/header.php";
?>

<div class="product-form-container">
    <header>
        <h2>Aggiungi un metodo di pagamento</h2>
    </header>

    <?php if ($errore): ?>
        <div class="alert alert-error"><?= h($errore) ?></div>
    <?php endif; ?>

    <form method="POST" action="../controllers/controller_pagamenti.php">
        <input type="hidden" name="azione" value="inserisci">
        <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">

        <div class="form-group">
            <label for="descrizione">Descrizione</label>
            <input type="text"
                id="descrizione"
                name="descrizione"
                class="form-control"
                placeholder="Carta personale"
                maxlength="70"
                value="<?= h($dati_form["descrizione"] ?? "") ?>"
                required>
        </div>

        <div class="form-group">
            <label for="select_tipo_pagamento">Tipo di pagamento</label>
            <select
                id="select_tipo_pagamento"
                name="tipo"
                class="form-control"
                required>
                <option value="carta_credito" <?= ($dati_form["tipo"] ?? "") == "carta_credito" ? "selected" : "" ?>>Carta di credito</option>
                <option value="carta_debito" <?= ($dati_form["tipo"] ?? "") == "carta_debito" ? "selected" : "" ?>>Carta di debito</option>
                <option value="paypal" <?= ($dati_form["tipo"] ?? "") == "paypal" ? "selected" : "" ?>>PayPal</option>
                <option value="bonifico" <?= ($dati_form["tipo"] ?? "") == "bonifico" ? "selected" : "" ?>>Bonifico bancario</option>
                <option value="contrassegno" <?= ($dati_form["tipo"] ?? "") == "contrassegno" ? "selected" : "" ?>>Contrassegno (alla consegna)</option>
            </select>
        </div>

        <div id="box_carta" class="payment-fields">
            <div class="form-group">
                <label for="nome_titolare">Titolare della carta</label>
                <input type="text"
                    id="nome_titolare"
                    name="nome_titolare"
                    class="form-control"
                    value="<?= h($dati_form["nome_titolare"] ?? "") ?>">
            </div>
            <div class="form-group">
                <label for="numero_carta">Numero della carta</label>
                <input type="text"
                    id="numero_carta"
                    name="numero_carta"
                    class="form-control"
                    maxlength="16"
                    pattern="\d{16}"
                    value="<?= h($dati_form["numero_carta"] ?? "") ?>">
            </div>
            <div class="form-row">
                <div class="form-col form-group">
                    <label for="campo_scadenza">Scadenza (MM/AA)</label>
                    <input type="text"
                        id="campo_scadenza"
                        name="data_scadenza"
                        class="form-control"
                        maxlength="5"
                        pattern="\d{2}/\d{2}"
                        placeholder="MM/AA"
                        value="<?= h($dati_form["data_scadenza"] ?? "") ?>">
                </div>
                <div class="form-col form-group">
                    <label for="cvv">CVV</label>
                    <input type="text"
                        id="cvv"
                        name="cvv"
                        class="form-control"
                        maxlength="4"
                        pattern="\d{3,4}"
                        value=""
                        autocomplete="off">
                    <small>Serve solo alla registrazione e non viene conservato; della carta restano ultime 4 cifre, scadenza e titolare.</small>
                </div>
            </div>
        </div>

        <!-- SEZIONE PAYPAL -->
        <div id="box_paypal" class="payment-fields">
            <div class="form-group">
                <label for="email_paypal">E-mail PayPal</label>
                <input type="email"
                    id="email_paypal"
                    name="email_paypal"
                    class="form-control"
                    value="<?= h($dati_form["email_paypal"] ?? "") ?>">
            </div>
        </div>

        <div id="box_bonifico" class="payment-fields">
            <div class="form-group">
                <label for="intestatario">Intestatario del conto</label>
                <input type="text"
                    id="intestatario"
                    name="intestatario"
                    class="form-control"
                    value="<?= h($dati_form["intestatario"] ?? "") ?>">
            </div>
            <div class="form-group">
                <label for="campo_iban">IBAN</label>
                <input type="text"
                    id="campo_iban"
                    name="iban"
                    class="form-control input-uppercase"
                    minlength="15"
                    maxlength="34"
                    pattern="[A-Z0-9]{15,34}"
                    value="<?= h($dati_form["iban"] ?? "") ?>">
            </div>
        </div>

        <div id="box_contrassegno" class="payment-fields">
            <p class="note-box">
                Pagherai in contanti direttamente al corriere al momento della consegna.
            </p>
        </div>

        <div class="form-actions form-actions-form">
            <button type="submit" class="btn-primary font-bold">Salva il metodo</button>
            <a href="profilo.php" class="btn-secondary font-bold">Annulla</a>
        </div>
    </form>
</div>

<script src="../assets/js/profilo.js"></script>

<?php include "../includes/footer.php"; ?>