<?php
// Pagina per modificare un metodo di pagamento
require_once "../includes/config.php";

// Reindirizza l'utente se non è autenticato e/o non è un cliente
if (!verificaCliente()) {
    header("Location: ../index.php");
    exit;
}

$id_pagamento = (int)($_GET["id"] ?? 0);
if ($id_pagamento <= 0) {
    header("Location: profilo.php");
    exit;
}

// Recupera i messaggi di successo o errore dalla sessione e pulisce le variabili per evitare messaggi persistenti
$errore = $_SESSION["error"] ?? "";
unset($_SESSION["error"]);
$successo = $_SESSION["success"] ?? "";
unset($_SESSION["success"]);

$dati_form = $_SESSION["dati_form"] ?? [];
unset($_SESSION["dati_form"]);

// Mappa dei metodi di pagamento per visualizzarli correttamente nel form
$etichette_pagamento = [
    "carta_credito" => "Carta di credito",
    "carta_debito"  => "Carta di debito",
    "paypal"        => "PayPal",
    "bonifico"      => "Bonifico bancario",
    "contrassegno"  => "Contrassegno (alla consegna)"
];

// Caricamento dei dati associati al metodo di pagamento
if (empty($dati_form)) {
    $stmt = $pdo->prepare("SELECT * FROM metodo_pagamento WHERE id = ? AND id_cliente = ?");
    $stmt->execute([$id_pagamento, $_SESSION["user_id"]]);
    $pagamento = $stmt->fetch();

    if (!$pagamento) {
        $_SESSION["error"] = "Il metodo di pagamento non è stato trovato.";
        header("Location: profilo.php");
        exit;
    }
    /* Decodifica il JSON restituito dalla colonna "dati_pagamento"
    e lo unisce nell'array per popolare il form*/
    $dettagli = json_decode($pagamento["dati_pagamento"], true);
    $dati_form = array_merge($pagamento, $dettagli);

    $nome_visualizzato = $etichette_pagamento[$pagamento["tipo"]];
} else {
    $nome_visualizzato = $etichette_pagamento[$dati_form["tipo"]];
    $dati_form["id"] = $id_pagamento;
}


$nome_pagina = "Modifica il metodo di pagamento";
include "../includes/header.php";
?>

<div class="product-form-container">
    <header>
        <h2>Modifica il metodo di pagamento</h2>
    </header>

    <?php if ($successo): ?>
        <div class="alert alert-success"><?= h($successo) ?></div>
    <?php endif; ?>
    <?php if ($errore): ?>
        <div class="alert alert-error"><?= h($errore) ?></div>
    <?php endif; ?>

    <form method="POST" action="../controllers/controller_pagamenti.php">
        <input type="hidden" name="azione" value="aggiorna">
        <input type="hidden" name="id_pag" value="<?= h($dati_form["id"]) ?>">

        <div class="form-group">
            <label for="descrizione">Descrizione</label>
            <input type="text"
                id="descrizione"
                name="descrizione"
                class="form-control"
                maxlength="70"
                value="<?= h($dati_form["descrizione"] ?? "") ?>"
                required>
        </div>

        <div class="form-group">
            <label>Tipo di pagamento</label>
            <input type="text"
                class="form-control"
                value="<?= h($nome_visualizzato) ?>"
                disabled>

            <!-- Campo nascosto con ID specifico per far scattare il JS che mostra i campi giusti -->
            <input type="hidden"
                id="select_tipo_pagamento"
                name="tipo"
                value="<?= h($dati_form["tipo"]) ?>">
        </div>

        <div id="box_carta" class="payment-fields">
            <div class="form-group">
                <label for="nome_titolare">Titolare della carta</label>
                <input type="text"
                    id="nome_titolare"
                    name="nome_titolare"
                    class="form-control"
                    value="<?= h($dati_form["nome_titolare"]) ?? "" ?>">
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
                        value="<?= h($dati_form["cvv"] ?? "") ?>">
                </div>
            </div>
        </div>

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
                <label for="campo_iban">IBAN</label>
                <input type="text"
                    id="campo_iban"
                    name="iban"
                    class="form-control input-uppercase"
                    minlength="15"
                    maxlength="34"
                    pattern="[A-Z0-9]{15,34}"
                    value="<?= h($dati_form["iban"]?? "") ?>">
            </div>
            <div class="form-group">
                <label for="intestatario">Intestatario del conto</label>
                <input type="text"
                    id="intestatario"
                    name="intestatario"
                    class="form-control"
                    value="<?= h($dati_form["intestatario"] ?? "") ?>">
            </div>
        </div>

        <div id="box_contrassegno" class="payment-fields">
            <p style="color: #666; font-style: italic; background: #fff; padding: 10px; border: 1px solid #ccc;">
                Effettuerai il pagamento in contanti alla consegna. Nessun dato aggiuntivo è richiesto.
            </p>
        </div>

        <div class="form-actions" style="margin-top: 30px; display: flex; gap: 10px;">
            <button type="submit" class="btn-primary" style="font-weight: bold;">Salva le modifiche</button>
            <a href="profilo.php" class="btn-secondary" style="font-weight: bold;">Torna al profilo</a>
        </div>
    </form>
</div>

<script src="../assets/js/profilo.js"></script>

<?php include "../includes/footer.php"; ?>