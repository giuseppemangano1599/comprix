<?php
// Pagina di registrazione per gli utenti che vogliono registrarsi sul sito
require_once "../includes/config.php";

// Effettua un reindirizzamento automatico se l'utente è già autenticato
if (verificaNegoziante()) {
    header("Location: ../seller/pannello_negoziante.php");
    exit;
} elseif (verificaNegoziante()) {
    header("Location: ../index.php");
    exit;
}

// Recupera i messaggi di successo o errore dalla sessione e pulisce le variabili per evitare messaggi persistenti
$errore = flash("error");
$successo = flash("success");

// Recupera i dati del form dalla sessione per ripopolare i campi dopo un errore
$dati_form = datiPrecedenti();

$nome_pagina = "Registrati";

include "../includes/header.php";
?>

<div class="auth-container">
    <header>
        <h2>Crea un nuovo account</h2>
    </header>

    <?php if ($successo): ?>
        <div class="alert alert-success"><?= h($successo) ?></div>
        <a href="login.php" class="btn-primary">Vai al login</a>
    <?php else: ?>

        <?php if ($errore): ?>
            <div class="alert alert-error"><?= h($errore) ?></div>
        <?php endif; ?>

        <form method="POST" action="../controllers/controller_registrazione.php" id="register-form">
            <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">

            <fieldset>
                <legend>Tipo di account</legend>
                <div class="form-group">
                    <label for="user-type">Voglio registrarmi come:</label>
                    <select id="user-type" name="user_type" required class="form-control">
                        <option value="" disabled selected>Seleziona un tipo...</option>
                        <option value="cliente" <?= h($dati_form["user_type"] ?? "") === "cliente" ? "selected" : "" ?>>Cliente</option>
                        <option value="negoziante" <?= h($dati_form["user_type"] ?? "") === "negoziante" ? "selected" : "" ?>>Negoziante</option>
                    </select>
                </div>
            </fieldset>

            <fieldset>
                <legend>Dati di accesso</legend>
                <div class="form-group">
                    <label for="username">Username:</label>
                    <input type="text"
                        id="username"
                        name="username"
                        class="form-control"
                        minlength="3"
                        maxlength="50"
                        pattern="[a-zA-Z0-9._]{3,50}"
                        placeholder="Scegli un username"
                        value="<?= h($dati_form["username"] ?? "") ?>"
                        required>
                    <small id="username-feedback"></small>
                </div>

                <div class="form-group">
                    <label for="email">E-mail:</label>
                    <input type="email"
                        id="email"
                        name="email"
                        class="form-control"
                        maxlength="254"
                        placeholder="latua@email.it"
                        value="<?= h($dati_form["email"] ?? "") ?>"
                        required>
                    <small id="email-feedback"></small>
                </div>

                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password"
                        id="password"
                        name="password"
                        class="form-control"
                        minlength="8"
                        maxlength="64"
                        pattern="(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,64}"
                        placeholder="Inserisci una password"
                        required>
                    <small>Minimo: 8 caratteri, una lettera minuscola, una lettera maiuscola e un numero.</small>
                </div>

                <div class="form-group-checkbox">
                    <input type="checkbox" id="show-password">
                    <label for="show-password">Mostra password</label>
                </div>

                <div class="form-group">
                    <label for="confirm-password">Conferma password:</label>
                    <input type="password"
                        id="confirm-password"
                        name="confirm_password"
                        class="form-control"
                        minlength="8"
                        maxlength="64"
                        placeholder="Ripeti la password"
                        required>
                    <small id="confirm-password-feedback"></small>
                </div>

                <div class="form-group-checkbox">
                    <input type="checkbox" id="show-confirm-password">
                    <label for="show-confirm-password">Mostra password</label>
                </div>
            </fieldset>

            <fieldset>
                <legend>Domanda di sicurezza</legend>
                <p>Ti serve per recuperare la password se la dimentichi.</p>

                <div class="form-group">
                    <label for="secret-question">Domanda segreta:</label>
                    <select id="secret-question" name="secret_question" required class="form-control">
                        <option value="" disabled selected>Scegli una domanda...</option>
                        <?php foreach (DOMANDE_SEGRETE as $domanda): ?>
                            <option value="<?= h($domanda) ?>" <?= ($dati_form["secret_question"] ?? "") === $domanda ? "selected" : "" ?>><?= h($domanda) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="secret-answer">La tua risposta:</label>
                    <input type="text"
                        id="secret-answer"
                        name="secret_answer"
                        class="form-control"
                        minlength="2"
                        maxlength="100"
                        placeholder="La tua risposta segreta"
                        required>
                </div>
            </fieldset>

            <button type="submit" class="btn-primary">Registrati</button>
        </form>

        <div class="auth-links">
            <p>Hai già un account? <a href="login.php">Accedi qui</a></p>
        </div>
    <?php endif; ?>
</div>

<script src="../assets/js/registrazione.js"></script>

<?php include "../includes/footer.php"; ?>