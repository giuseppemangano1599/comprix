<?php
/* Pagina per il recupero della password tramite domanda segreta.
 * Qui l'utente, attraverso diversi passaggi può recuperare la password.
 * La pagina si aggiorna dinamicamente e recupera la domanda segreta associata
 * all'utente tramite una chiamata AJAX permettendo il recupero ed 
 * evitando il reindirizzamento in altre pagine.
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
$errore = $_SESSION["error"] ?? "";
unset($_SESSION["error"]);
$successo = $_SESSION["success"] ?? "";
unset($_SESSION["success"]);

$nome_pagina = "Recupero della password";

include_once "../includes/header.php";
?>

<div class="auth-container">
    <header>
        <h2>Recupero della password</h2>
    </header>

    <?php if ($successo): ?>
        <div class="alert alert-success"><?= h($successo) ?></div>
        <a href="login.php" class="btn-primary">Vai al login</a>
    <?php else: ?>

        <?php if ($errore): ?>
            <div class="alert alert-error"><?= h($errore) ?></div>
        <?php endif; ?>

        <form method="POST" action="../controllers/controller_pw_dimenticata.php" id="fp-form">

            <fieldset id="step-1-container">
                <div class="form-group">
                    <label for="user-type">Tipo di account:</label>
                    <select id="user-type" name="user_type" required class="form-control">
                        <option value="" disabled selected>Seleziona...</option>
                        <option value="cliente">Cliente</option>
                        <option value="negoziante">Negoziante</option>
                    </select>
                    <small id="type-feedback"></small>
                </div>

                <div class="form-group">
                    <label for="username-or-email">Username o e-mail:</label>
                    <input type="text"
                        id="username-or-email"
                        name="username_or_email"
                        class="form-control"
                        placeholder="Inserisci il tuo username o la tua e-mail"
                        required>
                    <small id="user-feedback"></small>
                </div>

                <button type="button" id="btn-continua" class="btn-secondary">Continua</button>
            </fieldset>

            <div id="step-2-container" style="display: none">
                <fieldset>
                    <legend>Sicurezza</legend>
                    <p>Domanda segreta: <strong id="testo-domanda">...</strong></p>

                    <div class="form-group">
                        <label for="secret-answer">La tua risposta:</label>
                        <input type="text"
                            id="secret-answer"
                            name="secret_answer"
                            class="form-control"
                            placeholder="Inserisci la tua risposta segreta"
                            required
                            disabled>
                    </div>

                </fieldset>

                <fieldset>
                    <legend>Nuova password</legend>

                    <div class="form-group">
                        <label for="new-password">Nuova password:</label>
                        <input type="password"
                            id="new-password"
                            name="new_password"
                            class="form-control"
                            pattern="(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,64}"
                            required disabled>
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
                            required disabled>
                        <small id="confirm-password-feedback"></small>
                    </div>

                    <div class="form-group-checkbox">
                        <input type="checkbox" id="show-confirm-password">
                        <label for="show-confirm-password">Mostra password</label>
                    </div>

                </fieldset>

                <button type="submit" class="btn-primary">Reimposta password</button>
            </div>

            <div class="auth-links">
                <p><a href="login.php">Annulla e torna al login</a></p>
            </div>
        </form>
    <?php endif; ?>

</div>

<script src="../assets/js/pw_dimenticata.js"></script>

<?php include "../includes/footer.php"; ?>