<?php
/* Pagina per l'aggiunta di un nuovo prodotto del negoziante
 * Il negoziante in questa pagina può aggiungere un singolo prodotto e
 * le informazioni e le foto associate ad esso.
 */
require_once "../includes/config.php";

// Reindirizza l'utente se non è autenticato e/o non è un negoziante
if (!verificaNegoziante()) {
    header("Location: ../index.php");
    exit;
}

// Recupera i messaggi di successo o errore dalla sessione e pulisce le variabili per evitare messaggi persistenti
$errore = $_SESSION["error"] ?? "";
unset($_SESSION["error"]);
$successo = $_SESSION["success"] ?? "";
unset($_SESSION["success"]);

// Recupera i dati dal form in caso di errore per ripopolare i campi
$dati_form = $_SESSION["dati_form"] ?? [];
unset($_SESSION["dati_form"]);

// Recupera le categorie disponibili
$categorie = recuperaCategorie($pdo);

$nome_pagina = "Aggiungi un prodotto";
include "../includes/header.php";
?>

<div class="product-form-container">
    <header>
        <h2>Aggiungi un nuovo prodotto</h2>
    </header>

    <?php if ($successo): ?>
        <div class="alert alert-success"><?= h($successo) ?></div>
        <div>
            <a href="aggiungi_prodotto.php" class="btn-primary">Aggiungi un altro prodotto</a>
            <a href="gestione_prodotti.php" class="btn-secondary">Torna alla lista dei prodotti</a>
        </div>
    <?php else: ?>

        <?php if ($errore): ?>
            <div class="alert alert-error"><?= h($errore) ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" action="../controllers/controller_aggiungi_prodotto.php">

            <div class="form-group">
                <label for="name">Nome del prodotto:</label>
                <input type="text"
                    id="name"
                    name="name"
                    class="form-control"
                    maxlength="100"
                    value="<?= h($dati_form["name"] ?? "") ?>"
                    required>
            </div>

            <div class="form-group">
                <label for="brand">Marca:</label>
                <input type="text"
                    id="brand"
                    name="brand"
                    class="form-control"
                    maxlength="100"
                    value="<?= h($dati_form["brand"] ?? "") ?>"
                    required>
            </div>
            
            <div class="form-group">
                <label for="category">Categoria:</label>
                <select id="category" name="category" class="form-control" required>
                    <option value="" disabled selected>Seleziona...</option>
                    <?php foreach ($categorie as $categoria): ?>
                        <option value="<?= $categoria["id"] ?>" <?= (($dati_form["category"] ?? "") == $categoria["id"]) ? "selected" : "" ?>>
                            <?= h($categoria["nome"]) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-row">
                <div class="form-col form-group">
                    <label for="price">Prezzo (€):</label>
                    <input type="number"
                        id="price"
                        name="price"
                        class="form-control"
                        step="0.01"
                        min="0.01"
                        placeholder="0.00"
                        value="<?= h($dati_form["price"] ?? "") ?>"
                        required>
                </div>
                <div class="form-col form-group">
                    <label for="quantity">Quantità:</label>
                    <input type="number"
                        id="quantity"
                        name="quantity"
                        class="form-control"
                        min="0"
                        value="<?= h($dati_form["quantity"] ?? "") ?>"
                        required>
                </div>
            </div>

            <div class="form-group">
                <label for="description">Descrizione:</label>
                <textarea id="description"
                    name="description"
                    rows="5"
                    class="form-control"><?= h($dati_form["description"] ?? "") ?></textarea>
            </div>

            <div class="form-group">
                <label for="photos">Foto del prodotto (MAX 5MB):</label>
                <input type="file"
                    id="photos"
                    name="photos[]"
                    class="form-control"
                    multiple accept="image/*">
            </div>

            <div class="form-actions" style="margin-top: 30px; display: flex; gap: 10px;">
                <button type="submit" class="btn-primary" style="font-weight: bold; background-color:#5bcd9a;">Aggiungi</button>
                <a href="gestione_prodotti.php" class="btn-secondary" style="font-weight: bold; background-color:#ff8099;">Annulla</a>
            </div>
        </form>
    <?php endif; ?>
</div>

<?php include "../includes/footer.php"; ?>