<?php
/* Controller che gestisce la aggiunta di un prodotto sul sito da parte del negoziante, 
 * i dati del form per inserire il prodotto vengono puliti e validati 
 * e, se i controlli passano, il prodotto viene registrato nel database. 
 */
require_once "../includes/config.php";

$errore = "";
$successo = "";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../index.php");
    exit;
}

if (!verificaNegoziante()) {
    header("Location: ../index.php");
    exit;
}

// Vengono validati i dati dal form di per aggiungere il prodotto
$nome = trim($_POST["name"] ?? "");
$marca = trim($_POST["brand"] ?? "");
$descrizione = trim($_POST["description"] ?? "");
$prezzo = (float)str_replace(",", ".", $_POST["price"] ?? 0);
$quantita = (int)($_POST["quantity"] ?? 0);
$id_categoria = (int)($_POST["category"] ?? 0);
$foto_associate = $_FILES["photos"] ?? [];

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

// Validazione dei file caricati per vedere se rispettano i formati
if (empty($errore) && !empty($foto_associate["name"][0])) {
    $formati_ammessi = ["image/jpeg", "image/png", "image/gif"];
    $dimensione_massima = 5 * 1024 * 1024; // 5 MB

    foreach ($foto_associate["tmp_name"] as $indice => $temp_nome) {
        if ($foto_associate["error"][$indice] === UPLOAD_ERR_OK) {
            if ($foto_associate["size"][$indice] > $dimensione_massima) {
                $errore = "Una delle foto del prodotto caricate è troppo grande (massimo 5MB).";
                break;
            }

            if (!in_array(mime_content_type($temp_nome), $formati_ammessi)) {
                $errore = "Il formato del file non è valido. Sono accettati soltanto i formati .jpg/jpeg, .png e .gif.";
                break;
            }
        } elseif ($foto_associate["error"][$indice] !== UPLOAD_ERR_NO_FILE) {
            $errore = "Si è verificato un errore durante il caricamento di una delle foto. Riprova.";
            break;
        }
    }
}

/* Inserimento del prodotto nel database se non vengono rilevati errori.
* L'inserimento viene effettuato con una transazione per sicurezza affinché 
* si eviti che il prodotto venga inserito ma le foto associate no o viceversa.
*/
if (empty($errore)) {
    try {
        $pdo->beginTransaction();

        // Inserisce il prodotto nel database
        $sql = "INSERT INTO prodotto (
            id_negoziante, 
            id_categoria, 
            marca, 
            nome, 
            prezzo, 
            descrizione, 
            quantita_disponibile
            ) VALUES (
            :id_negoziante, 
            :id_categoria, 
            :marca, 
            :nome, 
            :prezzo, 
            :descrizione, 
            :quantita
        )";

        $parametri = [
            ":id_negoziante" => $_SESSION["user_id"],
            ":id_categoria"  => $id_categoria,
            ":marca"         => $marca,
            ":nome"          => $nome,
            ":prezzo"        => $prezzo,
            ":descrizione"   => $descrizione,
            ":quantita"      => $quantita
        ];

        $stmt = $pdo->prepare($sql);
        $stmt->execute($parametri);
        $id_prodotto = $pdo->lastInsertId();
        
        // Carica le foto associate al prodotto
        if (!empty($foto_associate["name"][0])) {
            $cartella_foto = "../uploads/products/";
            if (!is_dir($cartella_foto)) {
                mkdir($cartella_foto, 0755, true);
            }

            foreach ($foto_associate["tmp_name"] as $indice => $temp_nome) {
                if ($foto_associate["error"][$indice] === UPLOAD_ERR_OK) {
                    $estensione = pathinfo($foto_associate["name"][$indice], PATHINFO_EXTENSION);
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
        $successo = "Il prodotto è stato aggiunto con successo!";
    } catch (PDOException $e) {
        $pdo->rollBack();
        $errore = "Si è verificato un errore durante l'inserimento del prodotto. Riprova più tardi.";
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
    $_SESSION["dati_form"] = $_POST;
}

// Torna sempre alla pagina di aggiunta del prodotto
header("Location: ../seller/aggiungi_prodotto.php");
exit;
