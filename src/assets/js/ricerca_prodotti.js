/* Script scritto injQuery per la ricerca dei prodotti.
 * effettua chiamate AJAX per far sì che la ricerca avvenga dinamicamente 
 * nella stessa pagina.
 */
$(document).ready(function () {

    const $griglia = $("#products-grid");
    const $paginazione = $("#pagination-container");
    const $infoRisultati = $("#search-info");
    
    const $formRicerca = $("#form-ricerca");
    const $inputCerca = $("#q");
    const $selectCategoria = $("#category");
    const $btnReset = $("#btn-reset");

    // Variabile che imposta un lieve ritardo tra la pressione di un tasto e la chiamata AJAX
    let timeoutRicerca;

    // Ultima richiesta in corso: una nuova ricerca annulla la precedente,
    // così risposte arrivate fuori ordine non sovrascrivono i risultati attuali
    let richiestaCorrente = null;

    // Effettua la chiamata AJAX per caricare i prodotti
    function caricaProdotti(pagina = 1) {
        // Recupera valori attuali
        const testo = $inputCerca.val().trim();
        const cat = $selectCategoria.val();

        if (richiestaCorrente) richiestaCorrente.abort();

        const richiesta = $.ajax({
            url: "../ajax/ajax_ricerca_prodotti.php",
            type: "GET",
            dataType: "json",
            data: {
                q: testo,
                category: cat,
                page: pagina
            },
            success: function (risposta) {

                if (!risposta.successo) {
                    mostraErrore("Si è verificato un errore nel caricamento dei prodotti.");
                    return;
                }

                aggiornaInfoRisultati(risposta.paginazione.totale_prodotti);
                renderizzaGriglia(risposta.prodotti);
                renderizzaPaginazione(risposta.paginazione);
            },
            error: function (_, stato) {
                // L'annullamento volontario non è un errore: la risposta superata va ignorata
                if (stato === "abort") return;
                mostraErrore("Si è verificato un errore di comunicazione con il server. Riprova più tardi.");
            },
            complete: function () {
                if (richiestaCorrente === richiesta) richiestaCorrente = null;
            }
        });

        richiestaCorrente = richiesta;
    }

    function renderizzaGriglia(prodotti) {
        if (prodotti.length === 0) {
            $griglia.html('<div class="alert alert-info" style="grid-column: 1/-1;">Nessun prodotto trovato con i filtri correnti.</div>');
            $paginazione.empty();
            return;
        }

        let html = "";
        prodotti.forEach(function (p) {
            const id = escapeHtml(p.id);
            const nome = escapeHtml(p.nome);
            const categoria = escapeHtml(p.categoria_nome);
            let foto = p.foto ? "../" + escapeHtml(p.foto) : "../assets/img/nessuna_foto.png";
            let prezzo = parseFloat(p.prezzo).toFixed(2).replace(".", ",") + " €";

            html += `
                <article class="product-card">
                    <a href="dettaglio_prodotto.php?id=${id}" class="card-img-link">
                        <img src="${foto}" alt="${nome}" class="product-image" loading="lazy">
                    </a>
                    <div class="product-info">
                        <small class="text-muted">${categoria}</small>
                        <h3><a href="dettaglio_prodotto.php?id=${id}">${nome}</a></h3>
                        <div class="product-price">${prezzo}</div>
                        <a href="dettaglio_prodotto.php?id=${id}" class="btn-secondary btn-sm">Dettagli</a>
                    </div>
                </article>
            `;
        });
        $griglia.html(html);
    }

    function renderizzaPaginazione(dati) {
        const tot = dati.totale_pagine;
        const curr = dati.pagina_corrente;
        let html = "";

        if (tot > 1) {
            html = '<ul class="pagination">';
            for (let i = 1; i <= tot; i++) {
                let active = (i === curr) ? "active" : "";
                html += `<li><button type="button" class="page-link ${active}" data-page="${i}">${i}</button></li>`;
            }
            html += "</ul>";
        }
        $paginazione.html(html);
    }

    function aggiornaInfoRisultati(totale) {
        if ($infoRisultati.length) {
            $infoRisultati.html(`Trovati <strong>${totale}</strong> prodotti`);
        }
    }

    function mostraErrore(msg) {
        $griglia.html(`<div class="alert alert-error">${msg}</div>`);
    }

    // Effettua una ricerca in tempo reale mentre si scrive (con un ritardo di 1000ms per non fare troppe richieste)
    $inputCerca.on("input", function () {
        clearTimeout(timeoutRicerca);
        timeoutRicerca = setTimeout(function () {
            caricaProdotti(1);
        }, 1000);
    });

    // Quando si cambia la categoria
    $selectCategoria.on("change", function () {
        caricaProdotti(1);
    });

    // Quando si cambia la pagina
    $(document).on("click", ".page-link", function () {
        let pagina = $(this).data("page");
        caricaProdotti(pagina);
    });

    // Reset dei filtri
    $btnReset.on("click", function (e) {
        e.preventDefault();
        $inputCerca.val("");
        $selectCategoria.val("0");
        caricaProdotti(1);
    });

    // Caricamento iniziale all'apertura della pagina
    caricaProdotti(1);
});