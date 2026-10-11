<?php
/* Pagina di dettaglio del prodotto.
 * Visualizza le informazioni, le foto e le recensioni associate ad esso.
 * Permette l'aggiunta al carrello e la gestione delle recensioni.
 */
require_once "../includes/config.php";

$id_prodotto = (int)($_GET["id"] ?? 0);

// Se l'ID del prodotto non è valido, torna alla home
if ($id_prodotto <= 0) {
    header("Location: ../index.php");
    exit;
}

// Recupera i dati del prodotto dal database
$stmt = $pdo->prepare("SELECT p.*, c.nome AS categoria_nome, n.username AS venditore_username
                       FROM prodotto AS p 
                       JOIN categoria AS c ON p.id_categoria = c.id 
                       JOIN negoziante AS n ON p.id_negoziante = n.id 
                       WHERE p.id = ?");
$stmt->execute([$id_prodotto]);
$prodotto = $stmt->fetch();

// Se il prodotto non è presente nel database, torna alla home
if (!$prodotto) {
    header("Location: ../index.php");
    exit;
}

// Recupera le foto del prodotto associate
$stmt = $pdo->prepare("SELECT percorso_file FROM foto_prodotto WHERE id_prodotto = ?");
$stmt->execute([$id_prodotto]);
$foto_prodotto = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Recupera le recensioni associate al prodotto
$stmt = $pdo->prepare("SELECT r.id, r.valutazione, r.testo, r.data_recensione, r.id_cliente, c.username 
                       FROM recensione AS r 
                       JOIN cliente AS c ON r.id_cliente = c.id 
                       WHERE r.id_prodotto = ? 
                       ORDER BY r.data_recensione DESC");
$stmt->execute([$id_prodotto]);
$recensioni = $stmt->fetchAll();

// Per ogni recensione, recupera le foto allegate ad essa
foreach ($recensioni as $indice=> $recensione) {
    $stmt_foto = $pdo->prepare("SELECT id, percorso_file
                                FROM foto_recensione
                                WHERE id_recensione = ?");
    $stmt_foto->execute([$recensione["id"]]);
    
    $recensioni[$indice]["foto_recensione"] = $stmt_foto->fetchAll();
}

// Controlla per vedere se l'utente può recensire il prodotto (perché l'ha acquistato e gli è stato consegnato)
$puo_recensire = false;
$ha_gia_recensito = false;
$id_utente = $_SESSION["user_id"] ?? 0;

if (verificaCliente()) {
    // Controlla se l'utente ha già scritto una recensione per il prodotto
    foreach ($recensioni as $r) {
        if ($r["id_cliente"] == $id_utente) {
            $ha_gia_recensito = true;
            break;
        }
    }

    // Se non ha recensito, controlla se ha acquistato il prodotto (e se è stato consegnato)
    if (!$ha_gia_recensito) {
        $stmt_acquisto = $pdo->prepare("SELECT 1 
                                        FROM ordine AS o 
                                        JOIN elemento_ordine AS eo ON o.id = eo.id_ordine 
                                        WHERE o.id_cliente = ? 
                                        AND eo.id_prodotto = ? 
                                        AND o.stato = 'consegnato' 
                                        LIMIT 1");
        $stmt_acquisto->execute([$id_utente, $id_prodotto]);

        // Se la query trova qualcosa, allora può recensirlo
        if ($stmt_acquisto->fetch()) {
            $puo_recensire = true;
        }
    }
}

$nome_pagina = h($prodotto["nome"]);
include "../includes/header.php";
?>

<div class="product-detail-layout">
    <div class="detail-gallery">
        <?php if (!empty($foto_prodotto)): ?>
            <img src="../<?= h($foto_prodotto[0]) ?>" id="main-image" class="main-image" alt="Foto principale">
            <div class="thumbnails">
                <?php foreach ($foto_prodotto as $src): ?>
                    <img src="../<?= h($src) ?>" class="thumbnail" alt="Miniatura">
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <img src="../assets/img/nessuna_foto.png" class="main-image" alt="Nessuna foto disponibile">
        <?php endif; ?>
    </div>

    <div class="detail-info">
        <h1><?= h($prodotto["nome"]) ?></h1>
        <p><strong>Venditore:</strong> <?= h($prodotto["venditore_username"]) ?></p>
        <p><strong>Marca:</strong> <?= h($prodotto["marca"]) ?></p>
        <p><strong>Categoria:</strong> <?= h($prodotto["categoria_nome"]) ?></p>
        
        <div class="detail-price"><?= prezzoEuro($prodotto["prezzo"]) ?></div>

        <div class="stock-box">
            <?php if ($prodotto["quantita_disponibile"] > 0): ?>
                <p style="color: green; font-weight: bold;">Disponibile (<?= $prodotto["quantita_disponibile"] ?> pezzi)</p>
                
                <?php if (verificaCliente()): ?>
                    <div class="add-to-cart-form">
                        <input type="number" id="quantita_da_aggiungere" value="1" min="1" max="<?= $prodotto["quantita_disponibile"] ?>" class="form-control" style="width: 70px;">
                        <button type="button" class="btn-primary" id="btn-aggiungi-carrello" data-id="<?= $id_prodotto ?>">Aggiungi al carrello</button>
                    </div>
                    <div id="feedback-carrello" role="status" style="margin-top:10px; font-weight:bold; display:none;"></div>
                <?php elseif (verificaNegoziante()): ?>
                    <p><em>Al momento gli account "negoziante" non possono acquistare prodotti. Iscriviti come cliente!</em></p>
                <?php else: ?>
                    <a href="../auth/login.php" class="btn-secondary">Accedi come cliente per acquistare</a>
                <?php endif; ?>

            <?php else: ?>
                <p class="text-danger font-bold">Prodotto esaurito!</p>
            <?php endif; ?>
        </div>

        <h3>Descrizione</h3>
        <p><?=h($prodotto["descrizione"]) ?></p>
    </div>
</div>

<section class="detail-reviews">
    <div class="reviews-grid">

        <div class="reviews-list" id="contenitore-recensioni">
            <h2>Recensioni (<span id="conta-recensioni"><?= count($recensioni) ?></span>)</h2>

            <?php if (empty($recensioni)): ?>
                <p id="msg-nessuna-recensione">Non ci sono ancora recensioni per questo prodotto.</p>
            <?php endif; ?>

            <?php foreach ($recensioni as $recensione): ?>
                <div class="review-card" id="card-recensione-<?= $recensione["id"] ?>">
                    <div class="review-header">
                        <strong><?= h($recensione["username"]) ?></strong>
                        <span class="stars" role="img" aria-label="Valutazione <?= $recensione["valutazione"] ?> su 5">
                            <?php 
                            for ($indice= 1; $indice<= 5; $indice++) {
                                echo ($indice<= $recensione["valutazione"]) ? "★" : "☆";
                            }
                            ?>
                        </span>
                    </div>
                    <small><?= date("d/m/Y", strtotime($recensione["data_recensione"])) ?></small>

                    <p class="testo-recensione"><?= h($recensione["testo"]) ?></p>

                    <div class="review-photos" style="display:flex; gap:5px;">
                        <?php foreach ($recensione["foto_recensione"] as $foto): ?>
                            <img src="../<?= h($foto["percorso_file"]) ?>" 
                                 class="foto-ingrandibile"
                                 style="width:50px; height:50px; object-fit:cover; border:1px solid #ccc; cursor:pointer;" 
                                 alt="Foto recensione">
                        <?php endforeach; ?>
                    </div>

                    <?php if (verificaCliente() && $id_utente == $recensione["id_cliente"]): ?>
                        <div style="text-align:right; margin-top:10px;">
                            <button type="button"
                                class="btn-primary btn-sm trigger-modifica-recensione"
                                data-id="<?= $recensione["id"] ?>">
                                Modifica
                            </button>

                            <button type="button"
                                class="btn-secondary btn-sm trigger-elimina-recensione"
                                data-id="<?= $recensione["id"] ?>"
                                style="background:#f8d7da; border-color:#f5c6cb; color:#721c24;">
                                Elimina
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="review-form-container" id="box-aggiungi-recensione" style="<?= $ha_gia_recensito ? "display:none;" : "" ?>">
            <?php if ($puo_recensire): ?>
                <h3>Scrivi la tua recensione</h3>
                <form id="form-aggiungi-recensione" enctype="multipart/form-data">
                    <input type="hidden" name="azione" value="aggiungi">
                    <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
                    <input type="hidden" name="id_prodotto" value="<?= $id_prodotto ?>">

                    <div class="form-group">
                        <label for="valutazione">Voto:</label>
                        <select name="valutazione" id="valutazione" class="form-control" required>
                            <option value="5">5 - Eccellente</option>
                            <option value="4">4 - Buono</option>
                            <option value="3">3 - Medio</option>
                            <option value="2">2 - Scarso</option>
                            <option value="1">1 - Pessimo</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="testo">La tua opinione:</label>
                        <textarea name="testo" id="testo" class="form-control" rows="3" required></textarea>
                    </div>

                    <div class="form-group">
                        <label for="foto">Foto (opzionale):</label>
                        <input type="file" name="foto[]" id="foto" class="form-control" multiple accept="image/*">
                    </div>

                    <button type="submit" class="btn-primary">Pubblica recensione</button>
                </form>
            <?php elseif (verificaCliente()): ?>
                <p>Per recensire questo prodotto devi prima acquistarlo e riceverlo.</p>
            <?php else: ?>
                <p>Accedi come cliente per scrivere una recensione.</p>
            <?php endif; ?>
        </div>
        
        <div id="msg-recensione" class="alert alert-success" role="status" style="display:none;">
            Grazie! La tua recensione è stata pubblicata.
        </div>

    </div>
</section>

<dialog id="modale-elimina-recensione" class="modal" aria-labelledby="titolo-elimina-recensione">
    <div class="modal-content">
        <h3 id="titolo-elimina-recensione">Conferma eliminazione recensione</h3>
        <p>Sei sicuro di voler eliminare questa recensione?</p>
        <form id="form-elimina-recensione">
            <input type="hidden" name="azione" value="elimina">
            <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
            <input type="hidden" name="id_recensione" id="id-recensione-elimina">
            
            <div class="form-actions form-actions-end">
                <button type="button" class="btn-secondary chiudi-modale">Annulla</button>
                <button type="submit" class="btn-primary" style="background:#dc3545; color:white; border-color:#dc3545;">Elimina</button>
            </div>
        </form>
    </div>
</dialog>

<dialog id="modale-modifica-recensione" class="modal" aria-labelledby="titolo-modifica-recensione" style="max-width: 500px; width:90%;">
    <div class="modal-content">
        <h3 id="titolo-modifica-recensione">Modifica la recensione</h3>
        <form id="form-modifica-recensione" enctype="multipart/form-data">
            <input type="hidden" name="azione" value="modifica">
            <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
            <input type="hidden" name="id_recensione" id="id-recensione-modifica">

            <div class="form-group">
                <label for="mod-valutazione">Voto:</label>
                <select name="valutazione" id="mod-valutazione" class="form-control">
                    <option value="5">5 - Eccellente</option>
                    <option value="4">4 - Buono</option>
                    <option value="3">3 - Medio</option>
                    <option value="2">2 - Scarso</option>
                    <option value="1">1 - Pessimo</option>
                </select>
            </div>

            <div class="form-group">
                <label for="mod-testo">Testo:</label>
                <textarea name="testo" id="mod-testo" class="form-control" rows="3" required></textarea>
            </div>

            <div class="form-group">
                <p><strong>Foto (seleziona per eliminare):</strong></p>
                <div id="container-foto-modifica" style="display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 10px;">
                </div>
            </div>

            <div class="form-group">
                <label for="mod-foto">Aggiungi nuove foto:</label>
                <input type="file" name="foto[]" id="mod-foto" class="form-control" multiple accept="image/*">
            </div>

            <div class="form-actions form-actions-end">
                <button type="button" class="btn-secondary chiudi-modale">Annulla</button>
                <button type="submit" class="btn-primary">Salva le modifiche</button>
            </div>
        </form>
    </div>
</dialog>

<dialog id="modale-immagine" aria-label="Anteprima immagine" style="padding: 0; border: 1px solid #000; max-width: 90%; max-height: 90%;">
    <img src="" id="img-ingrandita" 
         style="max-width: 100%; max-height: 80vh; display: block;" 
         alt="Ingrandimento">
    
    <div style="padding: 10px; display: flex; justify-content: flex-end; background: #fff;">
        <button class="btn-secondary chiudi-modale">Chiudi</button>
    </div>  
</dialog>


<script src="../assets/js/common.js"></script>
<script src="../assets/js/dettaglio_prodotto.js"></script>
<?php include "../includes/footer.php"; ?>