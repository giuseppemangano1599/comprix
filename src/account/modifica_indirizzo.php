<?php
// Pagina per modificare un indirizzo di spedizione esistente.
require_once "../includes/config.php";

// Reindirizza l'utente se non è autenticato e/o non è un cliente
if (!verificaCliente()) {
    header("Location: ../index.php");
    exit;
}

$id_indirizzo = (int)($_GET["id"] ?? 0);
if ($id_indirizzo <= 0) {
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

// Caricamento dei dati associati all'indirizzo
if (empty($dati_form)) {
    $stmt = $pdo->prepare("SELECT * FROM indirizzo_spedizione WHERE id = ? AND id_cliente = ?");
    $stmt->execute([$id_indirizzo, $_SESSION['user_id']]);
    $indirizzo = $stmt->fetch();

    if (!$indirizzo) {
        $_SESSION["error"] = "L'indirizzo non è stato trovato.";
        header("Location: profilo.php");
        exit;
    }

    $dati_form = $indirizzo;

} else {
    $dati_form["id"] = $id_indirizzo;
}

$nome_pagina = "Modifica l'indirizzo";
include "../includes/header.php";
?>

<div class="product-form-container">
    <header>
        <h2>Modifica l'indirizzo</h2>
    </header>

    <?php if ($successo): ?>
        <div class="alert alert-success"><?= h($successo) ?></div>
    <?php endif; ?>
    <?php if ($errore): ?>
        <div class="alert alert-error"><?= h($errore) ?></div>
    <?php endif; ?>

    <form method="POST" action="../controllers/controller_indirizzi.php">
        <input type="hidden" name="azione" value="aggiorna">
        <input type="hidden" name="id_indirizzo" value="<?= $dati_form['id'] ?>">

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


        <div class="form-row">
            <div class="form-col form-group">
                <label for="nome">Nome</label>
                <input type="text"
                    id="nome"
                    name="nome"
                    class="form-control"
                    minlength="1"
                    maxlength="50"
                    pattern="[A-Za-zÀ-ÿ'\.\-\s]{1,50}"
                    value="<?= h($dati_form["nome"] ?? "") ?>"
                    required>
            </div>
            <div class="form-col form-group">
                <label for="cognome">Cognome</label>
                <input type="text"
                    id="cognome"
                    name="cognome"
                    class="form-control"
                    minlength="1"
                    maxlength="50"
                    pattern="[A-Za-zÀ-ÿ'\.\-\s]{1,50}"
                    value="<?= h($dati_form["cognome"] ?? "") ?>"
                    required>
            </div>
        </div>

        <div class="form-group">
            <label for="indirizzo">Indirizzo e numero civico</label>
            <input type="text"
                id="indirizzo"
                name="indirizzo"
                class="form-control"
                minlength="3"
                maxlength="150"
                pattern="[a-zA-Z0-9À-ÿ\s,.'\-°\/()#]+"
                value="<?= h($dati_form["indirizzo"] ?? "") ?>"
                required>
        </div>

        <div class="form-row">
            <div class="form-col form-group">
                <label for="citta">Città</label>
                <input type="text"
                    id="citta"
                    name="citta"
                    class="form-control"
                    minlength="2"
                    maxlength="50"
                    pattern="[A-Za-zÀ-ÿ'\.\-\s]{1,50}"
                    value="<?= h($dati_form["citta"] ?? "") ?>"
                    required>
            </div>
            <div class="form-col form-group">
                <label for="provincia">Provincia</label>
                <input type="text"
                    id="provincia"
                    name="provincia"
                    class="form-control input-uppercase"
                    minlength="2"
                    maxlength="2"
                    pattern="[A-Za-z]{2}"
                    value="<?= h($dati_form["provincia"] ?? "") ?>"
                    required>
            </div>
            <div class="form-col form-group">
                <label for="cap">CAP</label>
                <input type="text"
                    id="cap"
                    name="cap"
                    class="form-control"
                    minlength="5"
                    maxlength="5"
                    pattern="\d{5}"
                    value="<?= h($dati_form["cap"] ?? "") ?>"
                    required>
            </div>
        </div>

        <div class="form-group">
            <label for="telefono">Telefono</label>
            <input type="tel"
                id="telefono"
                name="telefono"
                class="form-control"
                minlength="6"
                maxlength="20"
                pattern="[\d\+\-\s]{6,20}"
                value="<?= h($dati_form["telefono"] ?? "") ?>"
                required>
        </div>

        <div class="form-actions" style="margin-top: 30px; display: flex; gap: 10px;">
            <button type="submit" class="btn-primary" style="font-weight: bold;">Salva le modifiche</button>
            <a href="profilo.php" class="btn-secondary" style="font-weight: bold;">Torna al profilo</a>
        </div>
    </form>
</div>

<script src="../assets/js/profilo.js"></script>

<?php include "../includes/footer.php"; ?>