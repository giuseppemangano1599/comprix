<?php
/* Controller che gestisce l'eliminazione di un prodotto da parte del negoziante.
 * Verifica che il prodotto appartenga al negoziante autenticato prima di eliminarlo. */
require_once "../includes/config.php";

$errore = "";
$successo = "";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../index.php");
    exit;
}

$id_prodotto = (int)($_POST["delete_product_id"] ?? 0);

// Validazione dell'ID del prodotto da eliminare
if ($id_prodotto <= 0) {
    $errore = "L'ID del prodotto non è valido.";
} else {
    try {
        $pdo->beginTransaction();

        // Recupera le foto associate al prodotto
        $stmt_foto = $pdo->prepare("SELECT fp.percorso_file 
                                    FROM foto_prodotto AS fp
                                    JOIN prodotto AS p ON fp.id_prodotto = p.id
                                    WHERE p.id = ? AND p.id_negoziante = ?");
        $stmt_foto->execute([$id_prodotto, $_SESSION["user_id"]]);
        $foto_prodotto = $stmt_foto->fetchAll(PDO::FETCH_COLUMN);

        $stmt_foto_rec = $pdo->prepare("SELECT fr.percorso_file 
                                        FROM foto_recensione AS fr
                                        JOIN recensione AS r ON fr.id_recensione = r.id
                                        JOIN prodotto AS p ON r.id_prodotto = p.id
                                        WHERE p.id = ? AND p.id_negoziante = ?");
        $stmt_foto_rec->execute([$id_prodotto, $_SESSION["user_id"]]);
        $foto_recensioni = $stmt_foto_rec->fetchAll(PDO::FETCH_COLUMN);

        // Elimina solo se il prodotto appartiene al negoziante autenticato
        $stmt = $pdo->prepare("DELETE FROM prodotto 
                               WHERE id = ?
                               AND id_negoziante = ?");
        $stmt->execute([$id_prodotto, $_SESSION["user_id"]]);

        // Utilizza il metodo rowCount() perché la query con il comando DELETE non fallisce anche se cancella 0 righe
        if ($stmt->rowCount() > 0) {

            // Cancella le foto associate al prodotto
            foreach ($foto_prodotto as $foto) {
                $percorso_completo = "../" . $foto;
                if (file_exists($percorso_completo)) {
                    unlink($percorso_completo);
                }
            }

            // Cancella foto delle recensioni associate al prodotto
            foreach ($foto_recensioni as $file) {
                $percorso_completo = "../" . $file;
                if (file_exists($percorso_completo)) {
                    unlink($percorso_completo);
                }
            }

            $pdo->commit();
            $successo = "Il prodotto e le relative foto sono stati eliminati con successo.";
        } else {
            $pdo->rollBack();
            $errore = "Non è stato possibile eliminare il prodotto. Potrebbe non appartenere al tuo negozio.";
        }
    } catch (PDOException $e) {
        $errore = "Si è verificato un errore durante l'eliminazione del prodotto. Riprova più tardi.";
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
}

// Torna sempre alla pagina di gestione dei prodotti
header("Location: ../seller/gestione_prodotti.php");
exit;
