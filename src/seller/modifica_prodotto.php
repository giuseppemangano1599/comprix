<?php
/* Pagina per la modifica di un prodotto esistente.
 * Permette di cambiare i dettagli associati ad esso
 * e di gestire le foto (aggiunta o rimozione).
 */
require_once "../includes/config.php";

// Reindirizza l'utente se non è autenticato e/o non è un negoziante
if (!verificaNegoziante()) {
    header("Location: ../index.php");
    exit;
}

$id_prodotto = (int)($_GET["id"] ?? 0);
if ($id_prodotto <= 0) {
    header("Location: gestione_prodotti.php");
    exit;
}

// Recupera i messaggi di successo o errore dalla sessione e pulisce le variabili per evitare messaggi persistenti
$errore = flash("error");
$successo = flash("success");

// Recupera i dati del form dalla sessione per ripopolare i campi dopo un errore
$dati_form = datiPrecedenti();

// Recupera i dati del prodotto
$stmt = $pdo->prepare("SELECT * FROM prodotto WHERE id = ? AND id_negoziante = ?");
$stmt->execute([$id_prodotto, $_SESSION["user_id"]]);
$prodotto = $stmt->fetch();

if (!$prodotto) {
    $_SESSION["error"] = "Il prodotto che vuoi modificare non è stato trovato. Riprova.";
    header("Location: gestione_prodotti.php");
    exit;
}

// Se non ci sono dati di ripopolamento (primo caricamento della pagina), usa quelli recuperati dalla query
if (empty($dati_form)) {
    $dati_form = $prodotto;
}

// Se presenti, recupera le foto associate
$stmt_foto = $pdo->prepare("SELECT id, percorso_file FROM foto_prodotto WHERE id_prodotto = ?");
$stmt_foto->execute([$id_prodotto]);
$foto_associate = $stmt_foto->fetchAll();

$categorie = recuperaCategorie($pdo);
$nome_pagina = "Modifica il prodotto";
include "../includes/header.php";
?>

<div class="product-form-container">
    <header style="border-bottom: 1px solid #eee; margin-bottom: 20px;">
        <h2>Modifica: <?= h($prodotto["nome"]) ?></h2>
    </header>

    <?php if ($successo): ?>
        <div class="alert alert-success"><?= h($successo) ?></div>
    <?php endif; ?>
    <?php if ($errore): ?>
        <div class="alert alert-error"><?= h($errore) ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" action="../controllers/controller_modifica_prodotto.php">
        <input type="hidden" name="action" value="update_product">
        <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
        <input type="hidden" name="id_prodotto" value="<?= $id_prodotto ?>">

        <div class="form-group">
            <label for="nome">Nome del prodotto:</label>
            <input type="text"
                id="nome"
                name="nome"
                class="form-control"
                value="<?= h($dati_form["nome"] ?? "") ?>"
                required>
        </div>

        <div class="form-group">
            <label for="marca">Marca:</label>
            <input type="text"
                id="marca"
                name="marca"
                class="form-control"
                value="<?= h($dati_form["marca"] ?? "") ?>"
                required>
        </div>
        
        <div class="form-group">
            <label for="id_categoria">Categoria:</label>
            <select id="id_categoria" name="id_categoria" class="form-control" required>
                <?php foreach ($categorie as $categoria): ?>
                    <option value="<?= $categoria["id"] ?>"
                        <?= ($dati_form["id_categoria"] == $categoria["id"]) ? "selected" : "" ?>>
                        <?= h($categoria["nome"]) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label for="prezzo">Prezzo (€):</label>
                <input type="number"
                    id="prezzo"
                    name="prezzo"
                    class="form-control"
                    step="0.01"
                    value="<?= h($dati_form["prezzo"] ?? "") ?>"
                    required>
            </div>
            <div class="form-group">
                <label for="quantita_disponibile">Quantità:</label>
                <input type="number"
                    id="quantita_disponibile"
                    name="quantita_disponibile"
                    class="form-control"
                    value="<?= h($dati_form["quantita_disponibile"] ?? "") ?>"
                    required>
            </div>
        </div>

        <div class="form-group">
            <label for="descrizione">Descrizione:</label>
            <textarea id="descrizione"
                name="descrizione"
                rows="5"
                class="form-control"><?= h($dati_form["descrizione"] ?? "") ?></textarea>
        </div>

        <div class="form-group">
            <p><strong>Foto del prodotto:</strong></p>
            <?php if (!empty($foto_associate)): ?>
                <div class="edit-photos-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(100px, 1fr)); gap: 10px; margin-bottom: 15px;">
                    <?php foreach ($foto_associate as $foto): ?>
                        <div class="photo-wrapper" style="position: relative;">
                            <img src="../<?= h($foto["percorso_file"]) ?>" style="width: 100%; height: 100px; object-fit: cover; border-radius: 4px; border: 1px solid #ccc;">
                            <button type="button"
                                class="delete-photo-trigger"
                                data-id="<?= $foto["id"] ?>"
                                aria-label="Elimina foto"
                                style="position: absolute; top: -5px; right: -5px; background: #d32f2f; color: white; border: none; border-radius: 50%; width: 24px; height: 24px; cursor: pointer; display: flex; align-items: center; justify-content: center;">
                                x
                            </button>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p>Nessuna foto caricata per questo prodotto.</p>
            <?php endif; ?>

            <div class="form-group">
                <label for="photos">Aggiungi nuove foto (MAX 5MB):</label>
                <input type="file"
                    id="photos"
                    name="photos[]"
                    class="form-control"
                    multiple accept="image/*">
            </div>
        </div>

        <div class="form-actions form-actions-form">
            <button type="submit" class="btn-primary" style="font-weight: bold; background-color:#5bcd9a;">Salva le modifiche</button>
            <a href="gestione_prodotti.php" class="btn-secondary" style="font-weight: bold; background-color:#ff8099;">Torna ai tuoi prodotti</a>
        </div>
    </form>
</div>

<dialog id="delete-photo-modal" class="modal" aria-labelledby="titolo-elimina-foto">
    <div class="modal-content">
        <h3 id="titolo-elimina-foto">Elimina la foto</h3>
        <p>Sei sicuro di voler rimuovere questa immagine?</p>

        <form method="POST" action="../controllers/controller_modifica_prodotto.php">
            <input type="hidden" name="action" value="delete_photo">
            <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
            <input type="hidden" name="id_prodotto" value="<?= $id_prodotto ?>">
            <input type="hidden" name="id_foto" id="modal-photo-id">

            <div class="form-actions form-actions-end">
                <button type="button" class="btn-secondary chiudi-modale">Annulla</button>
                <button type="submit" class="btn-primary btn-danger">Elimina</button>
            </div>
        </form>
    </div>
</dialog>

<script src="../assets/js/modifica_prodotto.js"></script>
<?php include "../includes/footer.php"; ?>