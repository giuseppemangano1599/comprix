/* Script scritto injQuery per la ricerca dei prodotti.
 * effettua chiamate AJAX per far sì che la ricerca avvenga dinamicamente 
 * nella stessa pagina.
 */
$(document).ready(function () {

    const $griglia = $("#products-grid");
    const $paginazione = $("#pagination-container");
    const $info_risultati = $("#search-info");
    
    const $form_ricerca = $("#form-ricerca");
    const $input_cerca = $("#q");
    const $select_categoria = $("#category");
    const $btn_reset = $("#btn-reset");

    // Variabile che imposta un lieve ritardo tra la pressione di un tasto e la chiamata AJAX
    let timeout_ricerca;

    // Effettua la chiamata AJAX per caricare i prodotti
    function caricaProdotti(pagina = 1) {
        // Recupera valori attuali
        const testo = $input_cerca.val().trim();
        const cat = $select_categoria.val();

        $.ajax({
            url: "../ajax/ajax_ricerca_prodotti.php",
            type: "GET",
            dataType: "json",
            data: {
                q: testo,
                category: cat,
                page: pagina
            },
            success: function (risposta) {

                if (!risposta.success) {
                    mostraErrore("Si è verificato un errore nel caricamento dei prodotti.");
                    return;
                }

                aggiornaInfoRisultati(risposta.paginazione.totale_prodotti);
                renderizzaGriglia(risposta.prodotti);
                renderizzaPaginazione(risposta.paginazione);
            },
            error: function () {
                mostraErrore("Si è verificato un errore di comunicazione con il server. Riprova più tardi.");
            }
        });
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
        if ($info_risultati.length) {
            $info_risultati.html(`Trovati <strong>${totale}</strong> prodotti`);
        }
    }

    function mostraErrore(msg) {
        $griglia.html(`<div class="alert alert-error">${msg}</div>`);
    }

    // Effettua una ricerca in tempo reale mentre si scrive (con un ritardo di 1000ms per non fare troppe richieste)
    $input_cerca.on("input", function () {
        clearTimeout(timeout_ricerca);
        timeout_ricerca = setTimeout(function() {
            caricaProdotti(1);
        }, 1000);
    });

    // Quando si cambia la categoria
    $select_categoria.on("change", function () {
        caricaProdotti(1);
    });

    // Quando si cambia la pagina
    $(document).on("click", ".page-link", function () {
        let pagina = $(this).data("page");
        caricaProdotti(pagina);
    });

    // Reset dei filtri
    $btn_reset.on("click", function (e) {
        e.preventDefault();
        $input_cerca.val("");
        $select_categoria.val("0");
        caricaProdotti(1);
    });

    // Caricamento iniziale all'apertura della pagina
    caricaProdotti(1);
});