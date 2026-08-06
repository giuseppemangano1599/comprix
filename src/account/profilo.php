<?php
/* Pagina di gestione del profilo dell'utente.
 * Visualizza i dati personali e le tabelle per
 * gli indirizzi di spedizione e metodi di pagamento.
 */
require_once "../includes/config.php";

// Reindirizza l'utente se non è autenticato
if (!utenteAutenticato()) {
    header("Location: ../auth/login.php");
    exit;
}

// Recupera i dati dell'utente
$id_utente = $_SESSION["user_id"];
$tipo_utente = $_SESSION["user_type"];
$stmt = $pdo->prepare("SELECT username, email FROM $tipo_utente WHERE id = ?");
$stmt->execute([$id_utente]);
$dati_utente = $stmt->fetch();

// Recupera liste per la visualizzazione degli indirizzi e dei pagamenti del cliente
$indirizzi = [];
$pagamenti = [];

if (verificaCliente()) {
    $stmt_indirizzi = $pdo->prepare("SELECT * FROM indirizzo_spedizione WHERE id_cliente = ?");
    $stmt_indirizzi->execute([$id_utente]);
    $indirizzi = $stmt_indirizzi->fetchAll();

    $stmt_pagamenti = $pdo->prepare("SELECT * FROM metodo_pagamento WHERE id_cliente = ?");
    $stmt_pagamenti->execute([$id_utente]);
    $pagamenti = $stmt_pagamenti->fetchAll();
}

// Recupera i messaggi di successo o errore dalla sessione e pulisce le variabili per evitare messaggi persistenti
$errore = $_SESSION["error"] ?? "";
unset($_SESSION["error"]);
$successo = $_SESSION["success"] ?? "";
unset($_SESSION["success"]);

$nome_pagina = "Gestione del profilo";

include "../includes/header.php";
?>

<div class="container">
    <div style="border-bottom: 2px solid #000; margin-bottom: 20px;">
        <h1>Il tuo profilo</h1>
        <p>Bentornato, <strong><?= h($dati_utente["username"]) ?></strong></p>
    </div>

    <?php if ($successo): ?>
        <div class="alert alert-success"><?= h($successo) ?></div>
    <?php endif; ?>
    <?php if ($errore): ?>
        <div class="alert alert-error"><?= h($errore) ?></div>
    <?php endif; ?>

    <div class="profile-layout">
        <aside class="profile-sidebar">
            <div class="profile-card">
                <h3>Informazioni dell'account</h3>
                <form action="../controllers/controller_modifica_profilo.php" method="POST">
                    <input type="hidden" name="azione" value="aggiorna_dati">
                    <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">

                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text"
                            id="username"
                            name="username"
                            class="form-control"
                            minlength="3"
                            maxlength="50"
                            pattern="[a-zA-Z0-9._]{3,50}"
                            value="<?= h($dati_utente["username"]) ?>"
                            required>
                    </div>

                    <div class="form-group">
                        <label for="email">E-mail</label>
                        <input type="email"
                            id="email"
                            name="email"
                            class="form-control"
                            maxlength="254"
                            value="<?= h($dati_utente["email"]) ?>"
                            required>
                    </div>
                    <button type="submit" class="btn-primary btn-sm">Aggiorna le tue informazioni</button>
                </form>
            </div>

            <div class="profile-card">
                <h3>Sicurezza</h3>
                <div>
                    <h4>Cambia la password</h4>
                    <form action="../controllers/controller_modifica_profilo.php" method="POST">
                        <input type="hidden" name="azione" value="aggiorna_sicurezza">
                        <input type="hidden" name="tipo_sicurezza" value="password">
                        <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">

                        <div class="form-group">
                            <input type="password"
                                id="old-password"
                                name="old_password"
                                class="form-control"
                                minlength="8"
                                maxlength="64"
                                pattern="(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,64}"
                                placeholder="Password attuale"
                                required>

                            <input type="password"
                                id="new-password"
                                name="new_password"
                                class="form-control"
                                minlength="8"
                                maxlength="64"
                                pattern="(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,64}"
                                placeholder="Nuova password"
                                required>

                            <input type="password"
                                id="confirm-password"
                                name="confirm_password"
                                class="form-control"
                                minlength="8"
                                maxlength="64"
                                pattern="(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,64}"
                                placeholder="Conferma nuova password"
                                required>
                        </div>
                        <button type="submit" class="btn-secondary btn-sm">Cambia password</button>
                    </form>
                </div>

                <hr>

                <div>
                    <h4>Domanda segreta</h4>
                    <form action="../controllers/controller_modifica_profilo.php" method="POST">
                        <input type="hidden" name="azione" value="aggiorna_sicurezza">
                        <input type="hidden" name="tipo_sicurezza" value="domanda">
                        <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">

                        <div class="form-group">
                            <select name="domanda_segreta" class="form-control" required>
                                <option value="">Scegli una domanda...</option>
                                <?php foreach (DOMANDE_SEGRETE as $domanda): ?>
                                    <option value="<?= h($domanda) ?>"><?= h($domanda) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <input type="text"
                                id="risposta-segreta"
                                name="risposta_segreta"
                                class="form-control"
                                minlength="2"
                                maxlenght="100"
                                placeholder="La tua risposta segreta"
                                required>
                            <input type="password"
                                id="verify-password"
                                name="verify_password"
                                class="form-control"
                                placeholder="Password per confermare"
                                required>
                        </div>
                        <button type="submit" class="btn-secondary btn-sm">Cambia domanda</button>
                    </form>
                </div>
            </div>

            <!-- DANGER ZONE -->
            <div class="profile-card danger-zone">
                <h3>Eliminazione account</h3>
                <form id="form_delete_account" action="../controllers/controller_modifica_profilo.php" method="POST">
                    <input type="hidden" name="azione" value="cancella_account">
                    <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
                    <input type="password"
                        id="delete-account-password"
                        name="delete_account_password"
                        class="form-control"
                        placeholder="Password per confermare"
                        required>
                    <button type="submit" class="btn-secondary btn-sm text-danger">Conferma l'eliminazione</button>
                </form>
            </div>
        </aside>

        <div class="profile-main">

            <?php if (verificaCliente()): ?>

                <section style="margin-bottom: 40px;">
                    <header style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; border-bottom: 1px solid #eee; padding-bottom: 10px;">
                        <h2 style="margin: 0; font-size: 20px;">I tuoi indirizzi di spedizione</h2>
                        <a href="aggiungi_indirizzo.php" class="btn-primary" style="width: auto;">Aggiungi un indirizzo</a>
                    </header>

                    <?php if (empty($indirizzi)): ?>
                        <p style="font-style: italic; color: #666;">Non hai ancora salvato nessun indirizzo.</p>
                    <?php else: ?>
                        <div class="table-container">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Descrizione</th>
                                        <th>Destinatario</th>
                                        <th>Indirizzo</th>
                                        <th>Città</th>
                                        <th>Azioni</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($indirizzi as $indirizzo): ?>
                                        <tr>
                                            <td><?= h($indirizzo["descrizione"]) ?></td>
                                            <td>
                                                <strong><?= h($indirizzo["nome"] . " " . $indirizzo["cognome"]) ?></strong><br>
                                                <small><?= h($indirizzo["telefono"]) ?></small>
                                            </td>
                                            <td><?= h($indirizzo["indirizzo"]) ?></td>
                                            <td><?= h($indirizzo["citta"]) ?> (<?= h($indirizzo["provincia"]) ?>)<br><small>CAP: <?= h($indirizzo["cap"]) ?></small></td>
                                            <td>
                                                <div class="table-actions">
                                                    <a href="modifica_indirizzo.php?id=<?= $indirizzo["id"] ?>" class="btn-primary btn-sm">Modifica</a>
                                                    <button type="button"
                                                        class="btn-secondary btn-sm delete-addr-trigger"
                                                        data-id="<?= $indirizzo["id"] ?>"
                                                        style="background-color: #ffe6e6; color: #d32f2f; border-color: #ffcccc;">
                                                        Elimina
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </section>

                <section>
                    <header style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; border-bottom: 1px solid #eee; padding-bottom: 10px;">
                        <h2 style="margin: 0; font-size: 20px;">I tuoi metodi di pagamento</h2>
                        <a href="aggiungi_pagamento.php" class="btn-primary" style="width: auto;">Aggiungi un metodo</a>
                    </header>

                    <?php if (empty($pagamenti)): ?>
                        <p style="font-style: italic; color: #666;">Non hai ancora salvato nessun metodo di pagamento.</p>
                    <?php else: ?>
                        <div class="table-container">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Descrizione</th>
                                        <th>Tipo</th>
                                        <th>Dettagli</th>
                                        <th>Azioni</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($pagamenti as $pagamento):
                                        $dati = json_decode($pagamento["dati_pagamento"], true);
                                        $info_extra = "";

                                        // Formattazione dei dettagli
                                        if ($pagamento["tipo"] == "carta_credito" || $pagamento["tipo"] == "carta_debito") {
                                            $info_extra = "**** " . substr($dati["numero_carta"], -4);
                                        } elseif ($pagamento["tipo"] == "paypal") {
                                            $info_extra = h($dati["email_paypal"]);
                                        } elseif ($pagamento["tipo"] == "bonifico") {
                                            $info_extra = h($dati["intestatario"]);
                                        }
                                    ?>
                                        <tr>
                                            <td><strong><?= h($pagamento["descrizione"]) ?></strong></td>
                                            <td><?= ucfirst(str_replace("_", " ", $pagamento["tipo"])) ?></td>
                                            <td><small><?= $info_extra ?></small></td>
                                            <td>
                                                <div class="table-actions">
                                                    <a href="modifica_pagamento.php?id=<?= $pagamento["id"] ?>" class="btn-primary btn-sm">Modifica</a>
                                                    <button type="button"
                                                        class="btn-secondary btn-sm delete-pay-trigger"
                                                        data-id="<?= $pagamento["id"] ?>"
                                                        style="background-color: #ffe6e6; color: #d32f2f; border-color: #ffcccc;">
                                                        Elimina
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </section>

            <?php endif; ?>

        </div>
    </div>
</div>

<dialog id="delete-addr-modal" class="modal">
    <div class="modal-content">
        <h3>Eliminazione indirizzo di spedizione</h3>
        <p>Sei sicuro di voler eliminare questo indirizzo?</p>
        <form method="POST" action="../controllers/controller_indirizzi.php">
            <input type="hidden" name="azione" value="elimina">
            <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
            <input type="hidden" name="id_indirizzo" id="modal-addr-id">
            <div class="form-actions" style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px;">
                <button type="button" class="btn-secondary close-modal">Annulla</button>
                <button type="submit" class="btn-primary" style="background-color: #d32f2f; border-color: #d32f2f;">Elimina</button>
            </div>
        </form>
    </div>
</dialog>

<dialog id="delete-pay-modal" class="modal">
    <div class="modal-content">
        <h3>Eliminazione metodo di pagamento</h3>
        <p>Sei sicuro di voler eliminare questo metodo?</p>
        <form method="POST" action="../controllers/controller_pagamenti.php">
            <input type="hidden" name="azione" value="elimina">
            <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
            <input type="hidden" name="id_pag" id="modal-pay-id">
            <div class="form-actions" style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px;">
                <button type="button" class="btn-secondary close-modal">Annulla</button>
                <button type="submit" class="btn-primary" style="background-color: #d32f2f; border-color: #d32f2f;">Elimina</button>
            </div>
        </form>
    </div>
</dialog>

<script src="../assets/js/profilo.js"></script>
<?php include "../includes/footer.php"; ?>