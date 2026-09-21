/* Script scritto in jQuery per la pagina di dettaglio prodotto.
 * Gestisce le immagini, l'aggiunta al carrello e le recensioni.
 */
$(document).ready(function () {
    // Funzione che mostra un messaggio di feedback nel box delle recensioni
    function mostraMessaggioRecensione(successo, messaggio) {
        const $msg = $("#msg-recensione");

        // Rimuove le classi precedenti e assegna quella corretta
        $msg.removeClass("alert-success alert-danger")
            .addClass(successo ? "alert-success" : "alert-danger")
            .text(messaggio)
            .fadeIn()
            .delay(3000)
            .fadeOut();
    }

    // Funzione che mostra un messaggio di feedback per l'aggiunta di un prodotto nel carrello
    function mostraMessaggioCarrello(successo, messaggio) {
        const $msg = $("#feedback-carrello");
        const colore = successo ? "green" : "red";

        $msg.text(messaggio)
            .css("color", colore)
            .show()
            .delay(3000)
            .fadeOut();
    }

    // Cambia l'immagine del prodotto cliccando sulle miniature
    $(".thumbnail").on("click", function () {
        $("#main-image").attr("src", $(this).attr("src"));
    });

    // Apertura del modale immagine ingrandita
    $(document).on("click", ".foto-ingrandibile", function () {
        $("#img-ingrandita").attr("src", $(this).attr("src"));
        const dialogo_immagine = $("#modale-immagine")[0];
        if (dialogo_immagine) dialogo_immagine.showModal();
    });

    // Chiusura dei modali tramite il bottone dedicato
    $(".chiudi-modale").on("click", function () {
        $(this).closest("dialog")[0].close();
    });


    // Aggiunta dei prodotti nel carrello
    $("#btn-aggiungi-carrello").on("click", function () {
        const $bottone = $(this);
        const id_prodotto = $bottone.data("id");
        const quantita_scelta = $("#quantita_da_aggiungere").val();

        $bottone.prop("disabled", true).text("Attendere...");

        $.ajax({
            url: "../controllers/controller_ajax_carrello.php",
            type: "POST",
            dataType: "json",
            data: {
                azione: "aggiungi",
                id_prodotto: id_prodotto,
                quantita: quantita_scelta,
                csrf_token: $("meta[name=csrf-token]").attr("content")
            },
            success: function (risposta) {
                if (risposta.successo) {
                    mostraMessaggioCarrello(true, "Il prodotto è stato aggiunto nel carrello!");

                    aggiornaBadgeCarrello(risposta.dati.conteggio_carrello);
                } else {
                    mostraMessaggioCarrello(false, risposta.messaggio);
                }
            },
            error: function () {
                mostraMessaggioCarrello(false, "Si è verificato un errore di comunicazione con il server. Riprova più tardi.");
            },
            complete: function () {
                $bottone.prop("disabled", false).text("Aggiungi al carrello");
            }
        });
    });


    // Aggiunta di una recensione
    $("#form-aggiungi-recensione").on("submit", function (e) {
        e.preventDefault();

        const dati_form = new FormData(this);
        const $bottone = $(this).find("button[type=submit]");

        $bottone.prop("disabled", true).text("Pubblicazione...");

        $.ajax({
            url: "../controllers/controller_ajax_recensione.php",
            type: "POST",
            data: dati_form,
            processData: false,
            contentType: false,
            dataType: "json",
            success: function (risposta) {
                if (risposta.successo) {
                    // Nasconde il form, mostra il messaggio di successo e aggiunge la recensione
                    $("#box-aggiungi-recensione").slideUp();
                    $("#contenitore-recensioni").load(location.href + " #contenitore-recensioni > *");
                    mostraMessaggioRecensione(true, "Grazie! La tua recensione è stata pubblicata.");
                } else {
                    mostraMessaggioRecensione(false, risposta.messaggio);
                }
            },
            error: function () {
                mostraMessaggioRecensione(false, "Si è verificato un errore di comunicazione con il server. Riprova più tardi.");
            },
            complete: function () {
                $bottone.prop("disabled", false).text("Pubblica la recensione");
            }
        });
    });


    // Eliminazione di una recensione
    $(document).on("click", ".trigger-elimina-recensione", function () {
        $("#id-recensione-elimina").val($(this).data("id"));
        const dialogo_elimina = $("#modale-elimina-recensione")[0];
        if (dialogo_elimina) dialogo_elimina.showModal();
    });

    $("#form-elimina-recensione").on("submit", function (e) {
        e.preventDefault();
        const id_recensione = $("#id-recensione-elimina").val();

        $.ajax({
            url: "../controllers/controller_ajax_recensione.php",
            type: "POST",
            data: $(this).serialize(),
            dataType: "json",
            success: function (risposta) {
                $("#modale-elimina-recensione")[0].close();

                if (risposta.successo) {
                    // Rimuove dalla pagina la recensione
                    $("#card-recensione-" + id_recensione).fadeOut(300, function () {
                        $(this).remove();

                        // Aggiorna il contatore delle recensioni
                        let numero_attuale = parseInt($("#conta-recensioni").text(), 10);
                        $("#conta-recensioni").text(Math.max(0, numero_attuale - 1));

                        // Ripristina il form per aggiungere una recensione
                        $("#box-aggiungi-recensione").slideDown();
                        $("#form-aggiungi-recensione")[0].reset();

                        // Nasconde eventuali vecchi messaggi prima di mostrare il nuovo
                        $("#msg-recensione").hide();
                    });
                } else {
                    mostraMessaggioRecensione(false, risposta.messaggio);
                }
            },
            error: function () {
                $("#modale-elimina-recensione")[0].close();
                mostraMessaggioRecensione(false, "Si è verificato un errore durante l'eliminazione. Riprova.");
            }
        });
    });


    // Modifica di una recensione
    $(document).on("click", ".trigger-modifica-recensione", function () {
        const id_recensione = $(this).data("id");

        $.ajax({
            url: "../controllers/controller_ajax_recensione.php",
            type: "POST",
            data: { 
                azione: "recupera",
                id_recensione: id_recensione,
                csrf_token: $("meta[name=csrf-token]").attr("content")
            },
            dataType: "json",
            success: function (risposta) {
                if (risposta.successo) {
                    const dati = risposta.dati;

                    $("#id-recensione-modifica").val(dati.id);
                    $("#mod-valutazione").val(dati.valutazione);
                    $("#mod-testo").val(dati.testo);

                    // Gestione delle foto nel modale
                    const $container_foto = $("#container-foto-modifica");
                    $container_foto.empty();

                    if (dati.foto && dati.foto.length > 0) {
                        dati.foto.forEach(function (f) {
                            $container_foto.append(`
                                <div style="text-align:center; border:1px solid #eee; padding:5px;">
                                    <img src="../${escapeHtml(f.percorso_file)}" style="width:50px; height:50px; object-fit:cover; display:block; margin-bottom:5px;">
                                    <label style="font-size:11px; color:red; cursor:pointer;">
                                        <input type="checkbox" name="foto_da_eliminare[]" value="${escapeHtml(f.id)}"> Elimina
                                    </label>
                                </div>
                            `);
                        });
                    } else {
                        $container_foto.html('<span style="font-size:13px; color:#999;">Nessuna foto presente.</span>');
                    }

                    const dialogo_modifica = $("#modale-modifica-recensione")[0];
                    if (dialogo_modifica) dialogo_modifica.showModal();
                } else {
                    mostraMessaggioRecensione(false, risposta.messaggio);
                }
            },
            error: function () {
                mostraMessaggioRecensione(false, "Si è verificato un errore durante il recupero dei dati della recensione. Riprova.");
            }
        });
    });

    // Invio del form della modifica della recensione
    $("#form-modifica-recensione").on("submit", function (e) {
        e.preventDefault();
        const dati_form = new FormData(this);

        $.ajax({
            url: "../controllers/controller_ajax_recensione.php",
            type: "POST",
            data: dati_form,
            processData: false,
            contentType: false,
            dataType: "json",
            success: function (risposta) {
                $("#modale-modifica-recensione")[0].close();

                if (risposta.successo) {
                    $("#contenitore-recensioni").load(location.href + " #contenitore-recensioni > *");
                    mostraMessaggioRecensione(true, "La recensione è stata modificata con successo!");
                } else {
                    mostraMessaggioRecensione(false, risposta.messaggio);
                }
            },
            error: function () {
                $("#modale-modifica-recensione")[0].close();
                mostraMessaggioRecensione(false, "Si è verificato un errore durante la modifica della recensione. Riprova.");
            }
        });
    });

});