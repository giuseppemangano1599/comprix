<?php
/* Pagina per la gestione dei prodotti del negoziante.
 * I prodotti vengono caricati e visualizzati in tabella.
 * È possibile visualizzare i dettagli, modificarli o eliminare un prodotto.
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

// Recupera tutti i prodotti del negoziante (valutare se fare una view?)
$stmt = $pdo->prepare(
   "SELECT p.id, p.nome, p.marca, p.prezzo, p.quantita_disponibile, c.nome AS categoria_nome
    FROM prodotto AS p
    JOIN categoria c ON p.id_categoria = c.id
    WHERE p.id_negoziante = ?
    ORDER BY p.id DESC"
);
$stmt->execute([$_SESSION["user_id"]]);
$prodotti = $stmt->fetchAll();

$nome_pagina = "Gestisci i tuoi prodotti";
include "../includes/header.php";
?>

<section>
    <header style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h1>I tuoi prodotti</h1>
        <a href="aggiungi_prodotto.php" class="btn-primary"style="width:auto;">Aggiungi un prodotto</a>
    </header>

    <?php if ($successo): ?>
        <div class="alert alert-success"><?= h($successo) ?></div>
    <?php endif; ?>
    
    <?php if ($errore): ?>
        <div class="alert alert-error"><?= h($errore) ?></div>
    <?php endif; ?>

    <?php if (empty($prodotti)): ?>
        <div style="text-align: center; margin-top: 40px;">
            <p>Non hai ancora nessun prodotto in vendita.</p>
            <a href="aggiungi_prodotto.php" class="btn-primary">Inizia ora</a>
        </div>
    <?php else: ?>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Prodotto</th>
                        <th>Categoria</th>
                        <th>Marca</th>
                        <th>Prezzo</th>
                        <th>Stato</th>
                        <th>Azioni</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($prodotti as $prodotto): ?>
                        <tr>
                            <td><strong><?= h($prodotto["nome"]) ?></strong></td>
                            <td><?= h($prodotto["marca"]) ?></td>                           
                            <td><?= h($prodotto["categoria_nome"]) ?></td>
                            <td><?= prezzoEuro($prodotto["prezzo"]) ?></td>
                            <td>
                                <?php if ($prodotto["quantita_disponibile"] > 0): ?>
                                    <span class="badge badge-success">Disponibile (<?= $prodotto["quantita_disponibile"] ?>)</span>
                                <?php else: ?>
                                    <span class="badge badge-error">Esaurito</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="table-actions">
                                    <a href="../products/dettaglio_prodotto.php?id=<?= $prodotto["id"] ?>" class="btn-secondary btn-sm">Vedi</a>
                                    <a href="modifica_prodotto.php?id=<?= $prodotto["id"] ?>" class="btn-primary btn-sm">Modifica</a>
                                    <button type="button"
                                        class="btn-secondary btn-sm delete-trigger"
                                        data-id="<?= $prodotto["id"] ?>"
                                        data-name="<?= h($prodotto["nome"]) ?>"
                                        style="background-color: #ffe6e6; color: #d32f2f; border-color: #ffcccc;">
                                        Elimina
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <div class="auth-links" style="margin-top: 20px; text-align: left;">
            <p><a href="pannello_negoziante.php">&larr; Torna al pannello</a></p>
        </div>
    <?php endif; ?>
</section>

<dialog id="delete-modal" class="modal">
    <div class="modal-content">
        <h3>Elimina il prodotto</h3>
        <p>Sei sicuro di voler eliminare "<strong id="modal-product-name"></strong>"?</p>
        <p class="text-danger" style="font-size: 0.9em;">Questa azione non può essere annullata.</p>

        <form method="POST" action="../controllers/controller_elimina_prodotto.php">
            <input type="hidden" name="delete_product_id" id="modal-product-id">
            <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">

            <div class="form-actions" style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px;">
                <button type="button" class="btn-secondary close-modal">Annulla</button>
                <button type="submit" class="btn-primary" style="background-color: #d32f2f; border-color: #d32f2f;">Elimina definitivamente</button>
            </div>
        </form>
    </div>
</dialog>

<script src="../assets/js/gestione_prodotti.js"></script>
<?php include "../includes/footer.php"; ?>