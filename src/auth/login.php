<?php
/* Pagina di autenticazione per gli utenti registrati al sito
 * Gli utenti possono accedere sia tramite l'e-mail sia tramite l'username.
 */
require_once "../includes/config.php";

// Effettua un reindirizzamento automatico se l'utente è già autenticato
if (verificaNegoziante()) {
    header("Location: ../seller/pannello_negoziante.php");
    exit;
} elseif (verificaCliente()) {
    header("Location: ../index.php");
    exit;
}

// Recupera i messaggi di successo o errore dalla sessione e pulisce le variabili per evitare messaggi persistenti
$errore = flash("error");
$successo = flash("success");

// Recupera i dati del form dalla sessione per ripopolare i campi dopo un errore
$dati_form = datiPrecedenti();

$nome_pagina = "Accedi";

include_once "../includes/header.php";
?>

<div class="auth-container">
    <header>
        <h2>Accedi a <?= NOME_SITO ?></h2>
    </header>

    <?php if ($errore): ?>
        <div class="alert alert-error"><?= h($errore) ?></div>
    <?php endif; ?>

    <form method="POST" action="../controllers/controller_login.php" id="login-form">
        <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">

        <div class="form-group">
            <label for="username-or-email">Username o e-mail:</label>
            <input type="text"
                id="username-or-email"
                name="username_or_email"
                class="form-control"
                minlength="3"
                maxlength="254"
                placeholder="Il tuo username o la tua e-mail"
                value="<?= h($dati_form["username_or_email"] ?? "") ?>"
                required>
            <small id="uoe-feedback"></small>
        </div>

        <div class="form-group">
            <label for="password">Password:</label>
            <input type="password"
                id="password"
                name="password"
                class="form-control"
                minlength="8"
                maxlength="64"
                placeholder="La tua password"
                required>
        </div>

        <div class="form-group-checkbox">
            <input type="checkbox" id="show-password">
            <label for="show-password">Mostra password</label>
        </div>

        <button type="submit" class="btn-primary">Accedi</button>
    </form>

    <div class="auth-links">
        <p>Non hai un account? <a href="registrazione.php">Registrati qui</a></p>
        <p><a href="pw_dimenticata.php">Hai dimenticato la password?</a></p>
    </div>
</div>

<script src="../assets/js/common.js"></script>
<script src="../assets/js/login.js"></script>

<?php include_once "../includes/footer.php"; ?>