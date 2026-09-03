<?php
/* Questa è la pagina principale dell'e-commerce Comprix.
 * È il punto di accesso per chiunque voglia visitare il sito ed esplorare i prodotti.
 * In questa pagina è possibile visualizzare gli ultimi prodotti caricati
 * dai vari negozianti e navigare le varie sezioni tramite la navbar.
 */

require_once "includes/config.php";

$nome_pagina = "Home";

// Recupera gli ultimi 12 prodotti caricati nel database, ordinati per id decrescente, mostrando categoria e la prima foto
$stmt = $pdo->query(
    "SELECT p.id, p.nome, p.marca, p.prezzo, p.descrizione, p.quantita_disponibile, c.nome AS categoria_nome, 
    (SELECT percorso_file FROM foto_prodotto WHERE id_prodotto = p.id LIMIT 1) AS foto 
    FROM prodotto AS p 
    JOIN categoria AS c ON p.id_categoria = c.id
    WHERE p.quantita_disponibile > 0
    ORDER BY p.id DESC 
    LIMIT 12"
);

$prodotti = $stmt->fetchAll();
?>

<?php
// Header e navbar condivisi (prefisso "" perché siamo nella root del sito)
$prefisso_asset = "";
include "includes/header.php";
?>
        <section class="welcome-banner">
            <h1>Benvenuto su <?= NOME_SITO ?>!</h1>
            <p>Il tuo marketplace di fiducia per comprare e vendere online.</p>
        </section>

        <section>
            <h2>Ultimi prodotti arrivati</h2>
            <?php if (empty($prodotti)): ?>
                <p>Al momento non ci sono prodotti disponibili. Torna presto a trovarci!</p>
            <?php else: ?>
                <div class="products-grid">
                    <?php foreach ($prodotti as $prodotto): ?>
                        <?php
                        // Se il prodotto non ha delle immagini associate
                        $percorso_foto = $prodotto["foto"] ? $prodotto["foto"] : "assets/img/nessuna_foto.png";

                        // Se non c'è l'immagine segnaposto locale, si usa un segnaposto online
                        if (!$prodotto["foto"]) {
                            $percorso_foto = "https://dummyimage.com/300x200/cff4fc/000000.png&text=Nessuna+foto";
                        }
                        ?>

                        <article class="product-card">
                            <a href="products/dettaglio_prodotto.php?id=<?= $prodotto["id"] ?>">
                                <img src="<?= h($percorso_foto) ?>" alt="<?= h($prodotto["nome"]) ?>" class="product-image">
                            </a>

                            <div class="product-info">
                                <small><?= h($prodotto["categoria_nome"]) ?></small>
                                <h3>
                                    <a href="products/dettaglio_prodotto.php?id=<?= $prodotto["id"] ?>">
                                        <?= h($prodotto["nome"]) ?>
                                    </a>
                                </h3>
                                <div class="product-price"><?= prezzoEuro($prodotto["prezzo"]) ?></div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

<?php include "includes/footer.php"; ?>