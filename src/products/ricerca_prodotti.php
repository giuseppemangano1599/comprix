<?php
/* Pagina per la ricerca dei prodotti.
 * La ricerca è dinamica, l'utente, mentre ricerca un prodotto,
 * non ricarica la pagina, ma si aggiorna dinamicamente tramite AJAX
 * che fornisce i risultati di ricerca in tempo reale.
 * La logica è gestita lato client da ricerca_prodotti.js che chiama 
 * ajax_ricerca_prodotti.php.
 */
require_once "../includes/config.php";

$categorie = recuperaCategorie($pdo);

$nome_pagina = "Ricerca prodotti";
include "../includes/header.php";
?>

<div class="search-layout">
    
    <aside class="search-sidebar">
        <h3>Filtri</h3>
        <form id="form-ricerca">
            
            <div class="form-group">
                <label for="q">Cerca:</label>
                <input type="search"
                    id="q"
                    name="q"
                    class="form-control"
                    placeholder="Es. Smartphone...">
            </div>

            <div class="form-group">
                <label for="category">Categoria:</label>
                <select id="category" name="category" class="form-control">
                    <option value="0">Tutte le categorie</option>
                    <?php foreach ($categorie as $categoria): ?>
                        <option value="<?= $categoria["id"] ?>">
                            <?= h($categoria["nome"]) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-actions">
                <button id="btn-reset" class="btn-secondary" style="width: 100%;">Azzera i filtri</button>
            </div>
        </form>
    </aside>

    <section class="search-results">
        <header class="search-header">
            <h2>Risultati</h2>
            <p id="search-info">Risultati</p>
        </header>

        <div id="products-grid" class="products-grid">
            </div>

        <nav aria-label="Navigazione pagine" id="pagination-container" class="pagination-container">
            </nav>
    </section>
</div>

<script src="../assets/js/common.js"></script>
<script src="../assets/js/ricerca_prodotti.js"></script>

<?php include "../includes/footer.php"; ?>