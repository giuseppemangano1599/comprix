<?php
/* Controller AJAX per la gestione delle recensioni.
 * Il controller è ibridato alle chiamate AJAX poiché le azioni sono dinamiche 
 * e restituiscono una risposta immediata senza dover ricaricare la pagina.
 * Gestisce le azioni di aggiunta, modifica, eliminazione e recupero dei dati.
 * Restituisce risposte in formato JSON.
 */
require_once "../includes/config.php";

// Controlla che provengano solo richieste POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../index.php");
    exit;
}

header("Content-Type: application/json");

// Inizializzazione risposta standard
$risposta = [
    "successo" => false,
    "messaggio" => "Richiesta non valida"
];

$errore = "";
$successo = "";

$azione = $_POST["azione"] ?? "";

if (!verificaCliente()) {
    $risposta["messaggio"] = "Devi effettuare l'accesso come cliente per gestire le recensioni.";
    echo json_encode($risposta);
    exit;
}

if (!csrfValido()) {
    $risposta["messaggio"] = "Richiesta non valida o scaduta. Ricarica la pagina e riprova.";
    echo json_encode($risposta);
    exit;
}

$id_utente = $_SESSION["user_id"];

try {
    // Aggiunge una recensione
    if ($azione === "aggiungi") {
        
        $id_prodotto = (int)($_POST["id_prodotto"] ?? 0);
        $valutazione = (int)($_POST["valutazione"] ?? 0);
        $testo = trim($_POST["testo"] ?? "");
        $foto_caricate = $_FILES["foto"] ?? [];

        // Validazione degli input
        if ($id_prodotto <= 0) {
            $errore = "L'ID del prodotto non è valido.";
        } elseif ($valutazione < 1 || $valutazione > 5) {
            $errore = "La valutazione deve essere compresa tra 1 e 5.";
        } elseif (empty($testo)) {
            $errore = "Il testo della recensione è obbligatorio.";
        }

        // Controlla se esiste già una recensione dello stesso prodotto fatta dall'utente
        if (empty($errore)) {
            $stmt = $pdo->prepare("SELECT id FROM recensione WHERE id_cliente = ? AND id_prodotto = ?");
            $stmt->execute([$id_utente, $id_prodotto]);
            if ($stmt->fetch()) {
                $errore = "Hai già recensito questo prodotto.";
            }
        }

        // Controlla che l'utente abbia acquistato il prodotto e che l'ordine sia consegnato (come il trigger tr_recensioni_solo_prodotti_acquistati)
        if (empty($errore)) {
            $stmt = $pdo->prepare("SELECT 1 FROM elemento_ordine AS eo INNER JOIN ordine AS o ON eo.id_ordine = o.id WHERE o.id_cliente = ? AND eo.id_prodotto = ? AND o.stato = 'consegnato' LIMIT 1");
            $stmt->execute([$id_utente, $id_prodotto]);
            if (!$stmt->fetch()) {
                $errore = "Puoi recensire solo prodotti che hai acquistato (e che ti sono stati consegnati).";
            }
        }

        // Validazione dei file caricati per vedere se rispettano la dimensione e i formati
        if (empty($errore) && !empty($foto_caricate["name"][0])) {
            $formati_ammessi = ["image/jpeg", "image/png", "image/gif"];
            $estensioni_ammesse = ["jpg", "jpeg", "png", "gif"];
            $dimensione_massima = 5 * 1024 * 1024; // 5MB

            foreach ($foto_caricate["tmp_name"] as $indice => $temp_nome) {
                if ($foto_caricate["error"][$indice] === UPLOAD_ERR_OK) {
                    if ($foto_caricate["size"][$indice] > $dimensione_massima) {
                        $errore = "Una delle foto della recensione che stai caricando è troppo grande (massimo 5MB).";
                        break;
                    }
                    $mime_file = mime_content_type($temp_nome);
                    $estensione_file = strtolower(pathinfo($foto_caricate["name"][$indice], PATHINFO_EXTENSION));
                    // @ evita il warning su file non immagine, che romperebbe la risposta JSON
                    $info_immagine = @getimagesize($temp_nome);

                    if (!in_array($mime_file, $formati_ammessi, true)
                        || !in_array($estensione_file, $estensioni_ammesse, true)
                        || $info_immagine === false
                        || !in_array($info_immagine["mime"], $formati_ammessi, true)
                        || $info_immagine["mime"] !== $mime_file
                    ) {
                        $errore = "Il formato del file non è valido. Sono accettati soltanto i formati .jpg/jpeg, .png e .gif.";
                        break;
                    }
                }
            }
        }

        // Inserimento della recensione nel database
        if (empty($errore)) {
            $pdo->beginTransaction();

            $sql = "INSERT INTO recensione (id_cliente, id_prodotto, valutazione, testo) VALUES (?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$id_utente, $id_prodotto, $valutazione, $testo]);
            $id_recensione = $pdo->lastInsertId();

            // Caricamento delle foto associate alla recensione
            if (!empty($foto_caricate["name"][0])) {
                $cartella_foto = "../uploads/reviews/";
                if (!is_dir($cartella_foto)) {
                    mkdir($cartella_foto, 0755, true);
                }

                foreach ($foto_caricate["tmp_name"] as $indice => $temp_nome) {
                    if ($foto_caricate["error"][$indice] === UPLOAD_ERR_OK) {
                        // Estensione derivata dal MIME verificato, mai dal nome originale
                        $estensioni_da_mime = ["image/jpeg" => "jpg", "image/png" => "png", "image/gif" => "gif"];
                        $mime_reale = mime_content_type($temp_nome);
                        if (!isset($estensioni_da_mime[$mime_reale])) {
                            continue;
                        }
                        $nome_file = "rev_" . $id_recensione . "_" . uniqid() . "." . $estensioni_da_mime[$mime_reale];
                        $percorso_file = $cartella_foto . $nome_file;

                        if (move_uploaded_file($temp_nome, $percorso_file)) {
                            $stmt_foto = $pdo->prepare("INSERT INTO foto_recensione (id_recensione, percorso_file) VALUES (?, ?)");
                            $stmt_foto->execute([$id_recensione, "uploads/reviews/" . $nome_file]);
                        }
                    }
                }
            }

            $pdo->commit();
            $risposta["successo"] = true;
            $risposta["messaggio"] = "La recensione è stata pubblicata con successo!";
        } else {
            $risposta["messaggio"] = $errore;
        }

    } 
    
    // Modifica una recensione
    elseif ($azione === "modifica") {
        
        $id_recensione = (int)($_POST["id_recensione"] ?? 0);
        $valutazione = (int)($_POST["valutazione"] ?? 0);
        $testo = trim($_POST["testo"] ?? "");
        $foto_da_eliminare = $_POST["foto_da_eliminare"] ?? []; 
        $nuove_foto = $_FILES["foto"] ?? [];

        // Validazione degli input
        if ($id_recensione <= 0) {
            $errore = "L'ID della recensione non è valido.";
        } elseif ($valutazione < 1 || $valutazione > 5) {
            $errore = "La valutazione deve essere compresa tra 1 e 5.";
        } elseif (empty($testo)) {
            $errore = "Il testo della recensione è obbligatorio.";
        }

        // Controlla se esiste la recensione del prodotto appartiene all'utente che la vuole modificare
        if (empty($errore)) {
            $stmt = $pdo->prepare("SELECT id FROM recensione WHERE id = ? AND id_cliente = ?");
            $stmt->execute([$id_recensione, $id_utente]);
            if (!$stmt->fetch()) {
                $errore = "La recensione non è stata trovata.";
            }
        }

        // Validazione dei file caricati per vedere se rispettano la dimensione e i formati
        if (empty($errore) && !empty($nuove_foto["name"][0])) {
            $formati_ammessi = ["image/jpeg", "image/png", "image/gif"];
            $estensioni_ammesse = ["jpg", "jpeg", "png", "gif"];
            $dimensione_massima = 5 * 1024 * 1024;

            foreach ($nuove_foto["tmp_name"] as $indice => $temp_nome) {
                if ($nuove_foto["error"][$indice] === UPLOAD_ERR_OK) {
                    if ($nuove_foto["size"][$indice] > $dimensione_massima) {
                        $errore = "Una delle foto della recensione che stai caricando è troppo grande (massimo 5MB).";
                        break;
                    }
                    $mime_file = mime_content_type($temp_nome);
                    $estensione_file = strtolower(pathinfo($nuove_foto["name"][$indice], PATHINFO_EXTENSION));
                    // @ evita il warning su file non immagine, che romperebbe la risposta JSON
                    $info_immagine = @getimagesize($temp_nome);

                    if (!in_array($mime_file, $formati_ammessi, true)
                        || !in_array($estensione_file, $estensioni_ammesse, true)
                        || $info_immagine === false
                        || !in_array($info_immagine["mime"], $formati_ammessi, true)
                        || $info_immagine["mime"] !== $mime_file
                    ) {
                        $errore = "FIl formato del file non è valido. Sono accettati soltanto i formati .jpg/jpeg, .png e .gif.";
                        break;
                    }
                }
            }
        }

        // Aggiornamento della recensione del database
        if (empty($errore)) {
            $pdo->beginTransaction();

            $sql = "UPDATE recensione SET valutazione = ?, testo = ? WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$valutazione, $testo, $id_recensione]);

            // Eliminazione delle foto selezionate
            if (!empty($foto_da_eliminare) && is_array($foto_da_eliminare)) {
                foreach ($foto_da_eliminare as $id_foto) {
                    $stmt_path = $pdo->prepare("SELECT percorso_file FROM foto_recensione WHERE id = ? AND id_recensione = ?");
                    $stmt_path->execute([$id_foto, $id_recensione]);
                    $percorso = $stmt_path->fetchColumn();

                    if ($percorso) {
                        eliminaFileUpload($percorso);
                        $stmt_delete = $pdo->prepare("DELETE FROM foto_recensione WHERE id = ?");
                        $stmt_delete->execute([$id_foto]);
                    }
                }
            }

            // Caricamento delle nuove foto
            if (!empty($nuove_foto["name"][0])) {
                $cartella_foto = "../uploads/reviews/";
                if (!is_dir($cartella_foto)) {
                    mkdir($cartella_foto, 0755, true);
                }

                foreach ($nuove_foto["tmp_name"] as $indice => $temp_nome) {
                    if ($nuove_foto["error"][$indice] === UPLOAD_ERR_OK) {
                        // Estensione derivata dal MIME verificato, mai dal nome originale
                        $estensioni_da_mime = ["image/jpeg" => "jpg", "image/png" => "png", "image/gif" => "gif"];
                        $mime_reale = mime_content_type($temp_nome);
                        if (!isset($estensioni_da_mime[$mime_reale])) {
                            continue;
                        }
                        $nome_file = "rev_" . $id_recensione . "_" . uniqid() . "." . $estensioni_da_mime[$mime_reale];
                        $percorso_file = $cartella_foto . $nome_file;

                        if (move_uploaded_file($temp_nome, $percorso_file)) {
                            $stmt_foto = $pdo->prepare("INSERT INTO foto_recensione (id_recensione, percorso_file) VALUES (?, ?)");
                            $stmt_foto->execute([$id_recensione, "uploads/reviews/" . $nome_file]);
                        }
                    }
                }
            }

            $pdo->commit();
            $risposta["successo"] = true;
            $risposta["messaggio"] = "La recensione è stata aggiornata correttamente.";
        } else {
            $risposta["messaggio"] = $errore;
        }

    } 

    // Eliminazione di una recensione
    elseif ($azione === "elimina") {
        
        $id_recensione = (int)($_POST["id_recensione"] ?? 0);

        if ($id_recensione <= 0) {
            $errore = "L'ID della recensione non è valido.";
        } else {
            $stmt = $pdo->prepare("SELECT id FROM recensione WHERE id = ? AND id_cliente = ?");
            $stmt->execute([$id_recensione, $id_utente]);
            if (!$stmt->fetch()) {
                $errore = "La recensione non è stata trovata.";
            }
        }

        if (empty($errore)) {
            // Recupera le foto per la cancellazione fisica dei file
            $stmt_foto = $pdo->prepare("SELECT percorso_file FROM foto_recensione WHERE id_recensione = ?");
            $stmt_foto->execute([$id_recensione]);
            $lista_foto = $stmt_foto->fetchAll(PDO::FETCH_COLUMN);

            foreach ($lista_foto as $file) {
                eliminaFileUpload($file);
            }

            $stmt_delete = $pdo->prepare("DELETE FROM recensione WHERE id = ?");
            $stmt_delete->execute([$id_recensione]);

            $risposta["successo"] = true;
            $risposta["messaggio"] = "La recensione è stata eliminata con successo.";
        } else {
            $risposta["messaggio"] = $errore;
        }

    } 
    
    // Recupera i dati di una recensione per inserirli nel modale per la modifica
    elseif ($azione === "recupera") {

        $id_recensione = (int)($_POST["id_recensione"] ?? 0);

        if ($id_recensione > 0) {
            $stmt = $pdo->prepare("SELECT id, valutazione, testo FROM recensione WHERE id = ? AND id_cliente = ?");
            $stmt->execute([$id_recensione, $id_utente]);
            $dati_recensione = $stmt->fetch();

            if ($dati_recensione) {
                $stmt_foto = $pdo->prepare("SELECT id, percorso_file FROM foto_recensione WHERE id_recensione = ?");
                $stmt_foto->execute([$id_recensione]);
                $dati_recensione["foto"] = $stmt_foto->fetchAll();

                $risposta["successo"] = true;
                $risposta["dati"] = $dati_recensione;
            } else {
                $risposta["messaggio"] = "La recensione non è stata trovata.";
            }
        } else {
            $risposta["messaggio"] = "L'ID della recensione non è valido.";
        }

    } else {
        $risposta["messaggio"] = "Azione non riconosciuta.";
    }

} catch (PDOException $e) {
    $pdo->rollBack();
    $risposta["messaggio"] = "Si è verificato un errore durante l'esecuzione dell'azione richiesta. Riprova più tardi.";
} catch (Exception $e) {
    $pdo->rollBack();
    $risposta["messaggio"] = "Si è verificato un errore durante l'esecuzione dell'azione richiesta. Riprova più tardi.";
}

echo json_encode($risposta);
exit;