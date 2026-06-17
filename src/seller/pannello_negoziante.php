<?php
/* Pagina che fa da pannello principale del negoziante,
 * da qui si può decidere se aggiugere un prodotto o
 * visualizzare quelli già esistenti.
 */
require_once "../includes/config.php";

// Se l'utente non è autenticato e non è un negoziante
if (!verificaNegoziante()) {
    header("Location: ../index.php");
    exit;
}

$nome_pagina = "Pannello del negoziante";
include "../includes/header.php";
?>

<section>
    <header>
        <h1 style="margin:0;">Pannello del negoziante</h1>
        <p>Benvenuto, <strong><?= h($_SESSION["username"]) ?></strong>.</p>
    </header>

    <div class="dashboard-grid">

        <a href="aggiungi_prodotto.php" class="dashboard-card">
            <img src="../assets/img/icona_agg_prod.png" alt="Aggiungi un nuovo prodotto">
            <h3>Aggiungi un nuovo prodotto</h3>
            <p>Metti in vendita un nuovo articolo.</p>
        </a>

        <a href="gestione_prodotti.php" class="dashboard-card">
            <img src="../assets/img/icona_tuoi_prod.png" alt="I tuoi prodotti">
            <h3>I tuoi prodotti</h3>
            <p>Visualizza, modifica o elimina i tuoi prodotti.</p>
        </a>
    </div>
</section>

<?php include "../includes/footer.php"; ?>