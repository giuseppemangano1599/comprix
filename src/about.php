<?php
// Pagina statica "Chi siamo" che presenta in modo semplice l'e-commerce.
require_once "includes/config.php";

$nome_pagina = "Chi siamo";
?>

<?php
// Header e navbar condivisi (prefisso "" perché siamo nella root del sito)
$prefisso_asset = "";
include "includes/header.php";
?>
        <section class="about-section">
            <h1>Chi siamo</h1>
            <p>Siamo <strong><?= NOME_SITO ?></strong>, una piattaforma di e-commerce italiana che mette fa da tramite tra negozi e persone in maniera sicura</p>
            <p>Tu compra o vendi, noi spediamo! </p>
            <p>Le spedizioni sono e saranno sempre <strong>gratuite</strong>!</p>

            <h3>Perché scegliere <?= NOME_SITO ?>?</h3>
            <ul class="about-features-list">
                <li><strong>Spedizioni gratuite</strong> - Su tutti gli ordini</li>
                <li><strong>Acquisti sicuri</strong> - Pagamenti protetti</li>
                <li><strong>Supporto clienti</strong> - Siamo qui per aiutarti</li>
            </ul>

            <div class="about-cta">
                <a href="products/ricerca_prodotti.php" class="btn-primary">Scopri i prodotti</a>
                <?php if (!utenteAutenticato()): ?>
                    <a href="auth/registrazione.php" class="btn-secondary">Registrati</a>
                <?php endif; ?>
            </div>
        </section>

<?php include "includes/footer.php"; ?>