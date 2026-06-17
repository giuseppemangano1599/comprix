<?php
/* Controller che gestisce la modifica di un prodotto del negoziante.
 * Gestisce due azioni distinte tramite il parametro "action" inviato tramite POST:
 * - update_product: aggiorna le informazioni del prodotto, comprese le foto;
 * - delete_photo: rimuove una foto specifica associata al prodotto.
 */
require_once "../includes/config.php";


if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../index.php");
    exit;
}

$errore = "";
$successo = "";

$azione = $_POST["action"] ?? "";
$id_prodotto = (int)($_POST["id_prodotto"] ?? 0);

// Viene validato l'ID del prodotto che si vuole modificare
if ($id_prodotto <= 0) {
    $_SESSION["error"] = "L'ID prodotto non è valido.";
    header("Location: ../seller/gestione_prodotti.php");
    exit;
}

// Verifica se il prodotto esiste e appartiene al negoziante
$stmt = $pdo->prepare("SELECT id FROM prodotto WHERE id = ? AND id_negoziante = ?");
$stmt->execute([$id_prodotto, $_SESSION["user_id"]]);
if (!$stmt->fetch()) {
    $_SESSION["error"] = "Il prodotto non è stato trovato oppure non ti appartiene.";
    header("Location: ../seller/gestione_prodotti.php");
    exit;
}

// Eliminazione delle foto associate
if ($azione === "delete_photo") {
    $id_foto = (int)($_POST["id_foto"] ?? 0);

    if ($id_foto > 0) {
        try {
            // Verifica che la foto appartenga a un prodotto del negoziante
            $stmt = $pdo->prepare(
                "SELECT fp.id, fp.percorso_file 
                 FROM foto_prodotto AS fp
                 JOIN prodotto p ON fp.id_prodotto = p.id
                 WHERE fp.id = ? AND p.id_negoziante = ?"
            );
            $stmt->execute([$id_foto, $_SESSION["user_id"]]);
            $foto = $stmt->fetch();

            // Se la foto esiste
            if ($foto) {
                // Elimina il file
                $percorso_completo = "../" . $foto["percorso_file"];
                if (file_exists($percorso_completo)) {
                    unlink($percorso_completo);
                }

                // Elimina il record dal database
                $stmt_delete = $pdo->prepare("DELETE FROM foto_prodotto WHERE id = ?");
                $stmt_delete->execute([$id_foto]);

                $successo = "La foto è stata eliminata con successo.";
            } else {
                $errore = "La foto non è trovata.";
            }
        } catch (PDOException $e) {
            $errore = "Si è verificato un errore durante l'eliminazione della foto. Riprova più tardi";
        }
    } else {
        $errore = "ID foto non valido.";
    }
}

// Aggiornamento dei dati di un prodotto
elseif ($azione === "update_product") {
    // Recupera gli input del form passati tramite POST
    $nome = trim($_POST["nome"] ?? "");
    $marca = trim($_POST["marca"] ?? "");
    $descrizione = trim($_POST["descrizione"] ?? "");
    $prezzo = (float)str_replace(",", ".", $_POST["prezzo"] ?? 0);
    $quantita = (int)($_POST["quantita_disponibile"] ?? 0);
    $id_categoria = (int)($_POST["id_categoria"] ?? 0);
    $nuove_foto = $_FILES["photos"] ?? [];

    // Catena di validazioni lato server che viene effettuata al fine di evitare input malevoli
    if (empty($nome)) {
        $errore = "Il nome del prodotto è obbligatorio.";
    } elseif (strlen($nome) > 100) {
        $errore = "Il nome del prodotto è troppo lungo (massimo 100 caratteri).";
    } elseif (empty($marca)) {
        $errore = "La marca del prodotto è obbligatoria";
    } elseif (strlen($marca) > 100) {
        $errore = "Il nome della marca del prodotto è troppo lungo (massimo 100 caratteri).";
    } elseif ($prezzo < 0) { // Il negoziante potrebbe voler inserire prodotti gratuiti
        $errore = "Il prezzo deve essere maggiore o uguale a zero.";
    } elseif ($quantita < 0) { // Il negoziante potrebbe voler inserire una quantità di 0 per il prodotto per poi aggiornarla quando torna in vendita
        $errore = "La quantità non può essere negativa.";
    } elseif ($id_categoria <= 0) {
        $errore = "Seleziona una categoria valida.";
    }

    // Validazione dei file caricati per vedere se rispettano la dimensione e i formati
    if (empty($errore) && !empty($nuove_foto["name"][0])) {
        $formati_ammessi = ["image/jpeg", "image/png", "image/gif"];
        $dimensione_massima = 5 * 1024 * 1024; // 5MB

        foreach ($nuove_foto["tmp_name"] as $indice => $temp_nome) {
            if ($nuove_foto["error"][$indice] === UPLOAD_ERR_OK) {
                if ($nuove_foto["size"][$indice] > $dimensione_massima) {
                    $errore = "Una delle foto del prodotto che stai caricando è troppo grande (massimo 5MB).";
                    break;
                }
                if (!in_array(mime_content_type($temp_nome), $formati_ammessi)) {
                    $errore = "Il formato del file non è valido. Sono accettati soltanto i formati .jpg/jpeg, .png e .gif.";
                    break;
                }
            }
        }
    }

    /* Aggiornamento dei dati del prodotto nel database se non vengono rilevati errori.
    * L'aggiornamento viene effettuato con una transazione per sicurezza affinché 
    * si eviti che il prodotto venga modificato ma le foto associate no o viceversa.
    */
    if (empty($errore)) {
        try {
            $pdo->beginTransaction();

            // Aggiorna i dati del il prodotto nel database
            $sql = "UPDATE prodotto
                    SET nome = :nome,
                        marca = :marca,
                        descrizione = :descrizione,
                        prezzo = :prezzo,
                        quantita_disponibile = :quantita,
                        id_categoria = :id_categoria
                    WHERE id = :id_prodotto 
                    AND id_negoziante = :id_negoziante";
            $stmt = $pdo->prepare($sql);

            $parametri = [
                ":nome"           => $nome,
                ":marca"          => $marca,
                ":descrizione"    => $descrizione,
                ":prezzo"         => $prezzo,
                ":quantita"       => $quantita,
                ":id_categoria"   => $id_categoria,
                ":id_prodotto"    => $id_prodotto,
                ":id_negoziante"  => $_SESSION["user_id"]
            ];

            $stmt->execute($parametri);

            // Carica le nuove foto associate al prodotto
            if (!empty($nuove_foto["name"][0])) {
                $cartella_foto = "../uploads/products/";
                if (!is_dir($cartella_foto)) {
                    mkdir($cartella_foto, 0755, true);
                }

                foreach ($nuove_foto["tmp_name"] as $indice => $temp_nome) {
                    if ($nuove_foto["error"][$indice] === UPLOAD_ERR_OK) {
                        $estensione = pathinfo($nuove_foto["name"][$indice], PATHINFO_EXTENSION);
                        $nome_file = "prod_" . $id_prodotto . "_" . uniqid() . "." . $estensione;
                        $percorso_file = $cartella_foto . $nome_file;

                        if (move_uploaded_file($temp_nome, $percorso_file)) {
                            $stmt = $pdo->prepare("INSERT INTO foto_prodotto (id_prodotto, percorso_file) VALUES (?, ?)");
                            $stmt->execute([$id_prodotto, "uploads/products/" . $nome_file]);
                        }
                    }
                }
            }

            $pdo->commit();
            $successo = "Il prodotto è stato aggiornato con successo!";
        } catch (PDOException $e) { 
            $pdo->rollBack();
            $errore = "Si è verificato un errore durante l'aggiornamento dei dati del prodotto. Riprova più tardi.";
        } catch (Exception $e){
            $pdo->rollBack();
            $errore = "Si è verificato un errore durante l'aggiornamento dei dati del prodotto. Riprova più tardi.";
        }
    }
}

/* Gestione del reindirizzamento in caso di successo o di errore.
 * Salva i messaggi di successo o errore nella sessione
 */
if (!empty($successo)) {
    $_SESSION["success"] = $successo;
}

if (!empty($errore)) {
    $_SESSION["error"] = $errore;
    // Salva i dati ricevuti tramite POST nella sessione per ripopolare il form
    if ($azione === "update_product") {
        $_SESSION["dati_form"] = $_POST;
    }
}

// Torna sempre alla pagina di modifica dello stesso prodotto
header("Location: ../seller/modifica_prodotto.php?id=" . $id_prodotto);
exit;
