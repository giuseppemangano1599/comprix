<?php // Questo file contiene l'header e la navbar comuni a tutte le pagine del sito.
// Prefisso dei path: "../" per le pagine nelle sottocartelle, "" per index.php e about.php in root.
$pref = $prefisso_asset ?? "../";
?>

<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($nome_pagina) ? h($nome_pagina) : "Home" ?> - <?= NOME_SITO ?></title>
    <link rel="stylesheet" href="<?= $pref ?>assets/css/style.css">
    <link rel="icon" type="image/x-icon" href="<?= $pref ?>favicon.ico">
    <meta name="csrf-token" content="<?= h(csrfToken()) ?>">
    <script src="<?= $pref ?>assets/js/jquery-3.7.1.js" integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4=" crossorigin="anonymous"></script>
</head>

<body>
    <header>
        <div class="container">
            <nav>
                <a href="<?= $pref ?>index.php" class="logo">
                    <img src="<?= $pref ?>logo.png" alt="Logo" class="logo-img">
                    <?= NOME_SITO ?>
                </a>

                <div class="nav-links">
                    <ul>
                        <li><a href="<?= $pref ?>products/ricerca_prodotti.php">Prodotti</a></li>
                        <li><a href="<?= $pref ?>about.php">Chi siamo</a></li>

                        <?php if (utenteAutenticato()): ?>

                            <?php if (verificaCliente()): ?>
                                <li>
                                    <?php
                                    $elementi_carrello_nav = articoliCarrelloNavbar($pdo);
                                    // Se il carrello ha degli articoli, allora spunta il badge del contatore, altrimenti il badge sarà nascosto tramite l'attributo "hidden"
                                    $badge_carrello = $elementi_carrello_nav > 0 ? "" : "hidden";
                                    ?>
                                    <a href="<?= $pref ?>cart/carrello.php" class="cart-icon-container" title="Carrello">
                                        <img src="<?= $pref ?>assets/img/icona_carrello.png" alt="Carrello" class="cart-icon-img">
                                        <span id="cart-count-badge" class="cart-badge <?= $badge_carrello ?>"><?= $elementi_carrello_nav ?></span>
                                    </a>
                                </li>
                            <?php endif; ?>

                            <li class="nav-separator"></li>

                            <li><strong>Ciao, <?= h($_SESSION["username"]) ?></strong></li>

                            <?php if (verificaCliente()): ?>
                                <li><a href="<?= $pref ?>orders/storico_ordini.php">I miei ordini</a></li>
                            <?php endif; ?>

                            <li><a href="<?= $pref ?>account/profilo.php">Profilo</a></li>

                            <?php if (verificaNegoziante()): ?>
                                <li><a href="<?= $pref ?>seller/pannello_negoziante.php">Pannello</a></li>
                            <?php endif; ?>

                            <li><a href="<?= $pref ?>controllers/controller_logout.php" class="text-danger">Esci</a></li>

                        <?php else: ?>
                            <li><a href="<?= $pref ?>auth/login.php">Accedi</a></li>
                            <li><a href="<?= $pref ?>auth/registrazione.php" class="btn-primary">Registrati</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
            </nav>
        </div>
    </header>

    <main class="container">