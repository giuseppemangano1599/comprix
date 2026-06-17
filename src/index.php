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

<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($nome_pagina) ? h($nome_pagina) : "Home" ?> - <?= NOME_SITO ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="icon" type="image/x-icon" href="favicon.ico">
    <script src="https://code.jquery.com/jquery-3.7.1.js" integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4=" crossorigin="anonymous"></script>
</head>

<body>
    <header>
        <div class="container">
            <nav>
                <a href="index.php" class="logo">
                    <img src="logo.png" alt="Logo" class="logo-img">
                    <?= NOME_SITO ?>
                </a>

                <div class="nav-links">
                    <ul>
                        <li><a href="products/ricerca_prodotti.php">Prodotti</a></li>
                        <li><a href="about.php">Chi siamo</a></li>

                        <?php if (utenteAutenticato()): ?>

                            <?php if (verificaCliente()): ?>
                                <li>
                                    <?php
                                    $elementi_carrello_nav = articoliCarrelloNavbar($pdo);
                                    // Se il carrello ha degli articoli, allora spunta il badge del contatore, altrimenti il badge sarà nascosto tramite l'attributo "hidden"
                                    $badge_carrello = $elementi_carrello_nav > 0 ? "" : "hidden";
                                    ?>
                                    <a href="cart/carrello.php" class="cart-icon-container" title="Carrello">
                                        <img src="assets/img/icona_carrello.png" alt="Carrello" class="cart-icon-img">
                                        <span id="cart-count-badge" class="cart-badge <?= $badge_carrello ?>"><?= $elementi_carrello_nav ?></span>
                                    </a>
                                </li>
                            <?php endif; ?>

                            <li class="nav-separator"></li>

                            <li><strong>Ciao, <?= h($_SESSION["username"]) ?></strong></li>

                            <?php if (verificaCliente()): ?>
                                <li><a href="orders/storico_ordini.php">I miei ordini</a></li>
                            <?php endif; ?>

                            <li><a href="account/profilo.php">Profilo</a></li>

                            <?php if (verificaNegoziante()): ?>
                                <li><a href="seller/pannello_negoziante.php">Pannello</a></li>
                            <?php endif; ?>

                            <li><a href="controllers/controller_logout.php" class="text-danger">Esci</a></li>

                        <?php else: ?>
                            <li><a href="auth/login.php">Accedi</a></li>
                            <li><a href="auth/registrazione.php" class="btn-primary">Registrati</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
            </nav>
        </div>
    </header>

    <main class="container">
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
    </main>

    <footer>
        <div class="container">
            <p>&copy; 2025 <?= NOME_SITO ?>. Progetto universitario di BDD/LPW a cura di Giuseppe Mangano (mat. XXXXXX).</p>
        </div>
    </footer>
</body>

</html>