<?php
// Script che viene richiamato da AJAX per ricercare dinamicamente i prodotti
require_once "../includes/config.php";

// Controlla che provengano solo richieste POST
if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    header("Location: ../index.php");
    exit;
}

// Specifica che i dati ricevuti sono codificati in JSON
header('Content-Type: application/json');

// Recupera i dati inviati dalla ricerca
$testo_cercato = trim($_GET["q"] ?? "");
$id_categoria = (int)($_GET["category"] ?? 0);

// Gestione della pagina corrente
$pagina = isset($_GET["page"]) ? (int)$_GET["page"] : 1;
if ($pagina < 1) $pagina = 1;

// Impostazioni per la paginazione
$prodotti_per_pagina = 12;
$offset = ($pagina - 1) * $prodotti_per_pagina;

// Prepara i filtri per la query
$filtri_sql = [];
$parametri = [];

// Se l'utente ha scritto qualcosa, cerca nel nome prodottoo nella descrizione nel nome della categoria
if (!empty($testo_cercato)) {
    $filtri_sql[] = "(p.nome LIKE ? OR p.descrizione LIKE ? OR c.nome LIKE ?)";
    $parametri[] = "%$testo_cercato%";
    $parametri[] = "%$testo_cercato%";
    $parametri[] = "%$testo_cercato%";
}

// Se l'utente ha selezionato una categoria specifica dal menu a tendina
if ($id_categoria > 0) {
    $filtri_sql[] = "p.id_categoria = ?";
    $parametri[] = $id_categoria;
}

// Costruzione della clausola WHERE
$stringa_where = "";
if (!empty($filtri_sql)) {
    $stringa_where = "WHERE " . implode(" AND ", $filtri_sql);
}

try {
    // Query per la paginazione
    $sql_conteggio = "SELECT COUNT(*) 
                      FROM prodotto AS p 
                      JOIN categoria AS c ON p.id_categoria = c.id 
                      $stringa_where";
                      
    $stmt_conteggio = $pdo->prepare($sql_conteggio);
    $stmt_conteggio->execute($parametri);
    $totale_prodotti = $stmt_conteggio->fetchColumn();
    
    $totale_pagine = ceil($totale_prodotti / $prodotti_per_pagina);

    // Query per il recupero dei prodotti
    $sql_prodotti = "SELECT p.id, p.nome, p.prezzo, c.nome AS categoria_nome, 
                        (SELECT percorso_file FROM foto_prodotto WHERE id_prodotto = p.id LIMIT 1) AS foto
                     FROM prodotto AS p 
                     JOIN categoria AS c ON p.id_categoria = c.id
                     $stringa_where
                     ORDER BY p.id DESC 
                     LIMIT $prodotti_per_pagina OFFSET $offset";

    $stmt = $pdo->prepare($sql_prodotti);
    $stmt->execute($parametri);
    $lista_prodotti = $stmt->fetchAll();

    echo json_encode([
        "successo" => true,
        "prodotti" => $lista_prodotti,
        "paginazione" => [
            "totale_prodotti" => $totale_prodotti,
            "totale_pagine" => $totale_pagine,
            "pagina_corrente" => $pagina
        ]
    ]);

} catch (PDOException $e) {
    echo json_encode([
        "successo" => false,
        "message" => "Si è verificato un errore nel caricamento dei prodotti."
    ]);
}
exit;