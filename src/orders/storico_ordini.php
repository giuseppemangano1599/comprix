<?php
/* Pagina per la visualizzazione dello storico degli ordini effettuati dal cliente.
 * Gli ordini vengono caricati e visualizzati in una tabella.
 * È possibile visualizzare i dettagli, o annullare un ordine.
 */
require_once "../includes/config.php";
require_once "../includes/simulatore_ordini.php"; 

if (!verificaCliente()) {
    header("Location: ../auth/login.php");
    exit;
}

$id_utente = $_SESSION["user_id"];

// Recupera gli ordini con una left per avere il nome del metodo di pagamento
$sql_ordini = "SELECT o.*, mp.descrizione AS nome_metodo, mp.tipo AS tipo_metodo
               FROM ordine AS o
               LEFT JOIN metodo_pagamento AS mp ON o.id_metodo_pagamento = mp.id
               WHERE o.id_cliente = ? 
               ORDER BY o.data_ordine DESC";

$stmt = $pdo->prepare($sql_ordini);
$stmt->execute([$id_utente]);
$lista_ordini = $stmt->fetchAll();

// Recupera i messaggi di successo o errore dalla sessione e pulisce le variabili per evitare messaggi persistenti
$errore = flash("error");
$successo = flash("success");

$nome_pagina = "I miei ordini";
include "../includes/header.php";
?>

<div class="container">

    <div class="search-header">
        <h1>I miei ordini</h1>
        <p>Storico acquisti di <strong><?= h($_SESSION["username"]) ?></strong>.</p>
    </div>

    <?php if ($successo): ?>
        <div class="alert alert-success"><?= h($successo) ?></div>
    <?php endif; ?>

    <?php if ($errore): ?>
        <div class="alert alert-error"><?= h($errore) ?></div>
    <?php endif; ?>

    <?php if (empty($lista_ordini)): ?>
        <div class="welcome-banner">
            <h3>Non hai ancora effettuato ordini.</h3>
            <div class="about-cta">
                <a href="../products/ricerca_prodotti.php" class="btn-primary">Vai ai prodotti</a>
            </div>
        </div>
    <?php else: ?>
        
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Data / ID</th>
                        <th>Prodotti</th>
                        <th>Spedizione & Pagamento</th>
                        <th>Totale</th>
                        <th>Stato</th>
                        <th>Azioni</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($lista_ordini as $ordine): ?>
                        <?php
                            // Effettua una query per i prodotti di questo specifico ordine
                            $stmt_prod = $pdo->prepare("SELECT nome_prodotto, quantita_aggiunta FROM elemento_ordine WHERE id_ordine = ?");
                            $stmt_prod->execute([$ordine["id"]]);
                            $prodotti = $stmt_prod->fetchAll();
                        ?>
                        <tr>
                            <td>
                                <strong>#<?= $ordine["id"] ?></strong><br>
                                <span style="font-size: 0.85em; color: #555;">
                                    <?= date("d/m/Y", strtotime($ordine["data_ordine"])) ?><br>
                                    <?= date("H:i", strtotime($ordine["data_ordine"])) ?>
                                </span>
                            </td>
                            
                            <td>
                                <ul>
                                    <?php foreach ($prodotti as $p): ?>
                                        <li>
                                            <strong><?= $p["quantita_aggiunta"] ?>x</strong> <?= h($p["nome_prodotto"]) ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </td>

                            <td>
                                <strong><?= h($ordine["nome_destinatario"] . " " . $ordine["cognome_destinatario"]) ?></strong><br>
                                <span style="font-size: 0.9em;">
                                    <?= h($ordine["indirizzo_destinatario"]) ?><br>
                                    <?= h($ordine["citta_destinatario"]) ?> (<?= h($ordine["provincia_destinatario"]) ?>)<br>
                                </span>
                                <hr>
                                <span style="font-size: 0.9em;">
                                    <?= h($ordine["nome_metodo"] ?? "Metodo rimosso") ?>
                                </span>
                            </td>

                            <td>
                                <span class="product-price">
                                    <?= prezzoEuro($ordine["totale"]) ?>
                                </span>
                            </td>

                            <td>
                                <?= h(ucfirst($ordine["stato"])) ?>
                            </td>

                            <td style="text-align: center;">
                                <?php if ($ordine["stato"] === "confermato"): ?>
                                    <button class="btn-secondary btn-sm text-danger trigger-annulla-ordine" 
                                            data-id="<?= $ordine["id"] ?>">
                                            Annulla
                                    </button>
                                <?php else: ?>
                                    <span style="font-size: 0.8em; color: #ccc;">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<dialog id="modale_annulla_ordine" class="auth-container" style="margin: auto; max-width: 400px;">
    <h3 class="text-danger" style="margin-top: 0;">Annulla l'ordine</h3>
    <p>Sei sicuro di voler annullare l'ordine?</p>
    <p style="font-size: 0.9em;">L'importo verrà rimborsato e i prodotti torneranno disponibili.</p>

    <form method="POST" action="../controllers/controller_ordini.php">
        <input type="hidden" name="azione" value="annulla_ordine">
        <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
        <input type="hidden" name="id_ordine" id="input_id_ordine_annulla">

        <div class="form-group" style="margin-top: 20px; display: flex; gap: 10px; justify-content: flex-end;">
            <button type="button" class="btn-secondary chiudi-modale" style="width: auto;">Indietro</button>
            <button type="submit" class="btn-primary" style="width: auto; background-color: #dc3545; border-color: #dc3545; color: white;">Conferma</button>
        </div>
    </form>
</dialog>

<script src="../assets/js/storico_ordini.js"></script>
<?php include "../includes/footer.php"; ?>