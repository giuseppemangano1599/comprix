<?php
/* Pagina del carrello dell'utente.
 * Visualizza i prodotti presenti nel carrello e da 
 * la possibilità all'utente di procedere al checkout.
 * Permette l'aumento e la diminuzione della quantità di 
 * un prodotto nel carrello o la sua rimozione.
 */
require_once "../includes/config.php";

if (!verificaCliente()) {
    header("Location: ../auth/login.php");
    exit;
}

$id_utente = $_SESSION["user_id"];

// Recupera gli elementi del carrello
$stmt = $pdo->prepare("SELECT ec.id_prodotto, ec.quantita_aggiunta, p.nome, p.prezzo, p.marca, p.quantita_disponibile,
                    (SELECT percorso_file FROM foto_prodotto WHERE id_prodotto = p.id LIMIT 1) AS foto
                    FROM elemento_carrello ec
                    JOIN carrello AS c ON ec.id_carrello = c.id
                    JOIN prodotto AS p ON ec.id_prodotto = p.id
                    WHERE c.id_cliente = ?
                    ORDER BY ec.id DESC
");

$stmt->execute([$id_utente]);
$elementi_carrello = $stmt->fetchAll();

// Recupera il totale del carrello
$stmt = $pdo->prepare("SELECT totale_prezzo FROM carrello WHERE id_cliente = ?");
$stmt->execute([$id_utente]);
$totale_carrello = $stmt->fetchColumn() ?: 0;

$nome_pagina = "Il tuo carrello";
include "../includes/header.php";
?>

<section>
    <h1>Il tuo carrello</h1>

    <div id="msg-carrello" class="alert" style="display:none; margin-bottom: 20px;"></div>

    <div id="carrello-vuoto" style="<?= empty($elementi_carrello) ? "text-align:center; padding:50px;" : "display:none; text-align:center; padding:50px;" ?>">
        <h2>Il tuo carrello è vuoto</h2>
        <p>Non hai ancora aggiunto nulla.</p>
        <a href="../products/ricerca_prodotti.php" class="btn-primary" style="width:auto; margin-top:10px;">Inizia lo shopping</a>
    </div>

    <?php if (!empty($elementi_carrello)): ?>
        <div class="cart-layout">

            <div class="cart-main">
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Prodotto</th>
                                <th>Prezzo</th>
                                <th>Quantità</th>
                                <th>Totale</th>
                                <th>Azioni</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($elementi_carrello as $elemento):
                                $percorso_immagine = $elemento["foto"] ? "../" . h($elemento["foto"]) : "https://dummyimage.com/50x50/cff4fc/000000.png&text=Nessuna+foto";
                                $subtotale = $elemento["prezzo"] * $elemento["quantita_aggiunta"];
                            ?>
                                <tr id="row-<?= $elemento["id_prodotto"] ?>">
                                    <td>
                                        <div style="display:flex; align-items:center; gap:10px;">
                                            <img src="<?= $percorso_immagine ?>" style="width:50px; height:50px; object-fit:cover; border:1px solid #ccc;">
                                            <div>
                                                <strong><a href="../products/dettaglio_prodotto.php?id=<?= $elemento["id_prodotto"] ?>"><?= h($elemento["nome"]) ?></a></strong><br>
                                                <small><?= h($elemento["marca"]) ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?= prezzoEuro($elemento["prezzo"]) ?></td>
                                    <td>
                                        <div style="display:flex; align-items:center;">
                                            <button type="button" class="btn-secondary btn-sm qty-btn"
                                                data-action="decrease"
                                                data-id="<?= $elemento["id_prodotto"] ?>">-</button>

                                            <input type="text" class="qty-input"
                                                id="qty-<?= $elemento["id_prodotto"] ?>"
                                                value="<?= $elemento["quantita_aggiunta"] ?>" readonly>

                                            <button type="button" class="btn-secondary btn-sm qty-btn"
                                                data-action="increase"
                                                data-id="<?= $elemento["id_prodotto"] ?>"
                                                data-max="<?= $elemento["quantita_disponibile"] ?>">+</button>
                                        </div>
                                        <small style="font-size:11px; color:#666;">Max: <?= $elemento["quantita_disponibile"] ?></small>
                                    </td>
                                    <td id="subtotal-<?= $elemento["id_prodotto"] ?>"><strong><?= prezzoEuro($subtotale) ?></strong></td>
                                    <td>
                                        <button type="button" class="btn-secondary btn-sm trigger-rimuovi"
                                            data-id="<?= $elemento["id_prodotto"] ?>"
                                            style="background:#f8d7da; color:red; border-color:#f5c6cb;">Rimuovi</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <a href="../products/ricerca_prodotti.php" style="display:inline-block; margin-top:15px;">&larr; Continua gli acquisti</a>
            </div>

            <aside class="cart-sidebar">
                <div class="summary-box">
                    <h3 style="margin-top:0;">Riepilogo ordine</h3>
                    <div class="summary-row">
                        <span>Subtotale:</span>
                        <span id="cart-subtotal"><?= prezzoEuro($totale_carrello) ?></span>
                    </div>
                    <div class="summary-row">
                        <span>Spedizione:</span>
                        <span style="color:green;">Gratis</span>
                    </div>
                    <div class="summary-total">
                        <span>Totale:</span>
                        <span id="cart-total"><?= prezzoEuro($totale_carrello) ?></span>
                    </div>

                    <a href="checkout.php" class="btn-primary" style="margin-top:20px;">Procedi al checkout</a>
                </div>
            </aside>
        </div>
    <?php endif; ?>
</section>

<dialog id="modale-rimozione" class="modal">
    <div class="modal-content">
        <h3>Rimuovi il prodotto</h3>
        <p>Sei sicuro di voler rimuovere questo articolo dal carrello?</p>
        <div class="form-actions form-actions-end">
            <button type="button" class="btn-secondary chiudi-modale">Annulla</button>
            <button type="button" id="conferma-rimozione-btn" class="btn-primary" style="background:#dc3545; border-color:#dc3545;">Rimuovi</button>
        </div>
    </div>
</dialog>

<script src="../assets/js/common.js"></script>
<script src="../assets/js/carrello.js"></script>
<?php include "../includes/footer.php"; ?>