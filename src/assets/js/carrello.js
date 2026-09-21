/* Script scritto in jQuery per la pagina del carrello.
 * Gestisce le operazioni di aggiunta e rimozione di un prodotto.
 */
$(document).ready(function () {
    // Variabile per memorizzare l'ID del prodotto da rimuovere tramite il modale
    let id_da_rimuovere = 0;

    // Mostra il messaggio di feedback (verde/rosso) in alto
    function mostraMessaggio(successo, messaggio) {
        const $msg = $("#msg-carrello");
        $msg.removeClass("alert-success alert-danger")
            .addClass(successo ? "alert-success" : "alert-danger")
            .text(messaggio)
            .show()
            .delay(3000)
            .fadeOut();
    }

    // Aggiorna una riga del carrello (o la rimuove se la quantità è 0)
    function aggiornaRigaCarrello(id_prodotto, nuova_quantita, $riga) {
        // Se stiamo per rimuovere (quantita 0), usiamo l'azione "rimuovi" per pulizia,
        // altrimenti "aggiorna" per cambiare la quantità.
        const azione = (nuova_quantita <= 0) ? "rimuovi" : "aggiorna";

        $.ajax({
            url: "../controllers/controller_ajax_carrello.php",
            type: "POST",
            dataType: "json",
            data: {
                azione: azione,
                id_prodotto: id_prodotto,
                quantita: nuova_quantita,
                csrf_token: $("meta[name=csrf-token]").attr("content")
            },
            success: function (risposta) {
                if (risposta.successo) {
                    if (nuova_quantita <= 0) {
                        // Effetto dissolvenza e rimozione riga
                        $riga.fadeOut(300, function () {
                            $(this).remove();
                            // Se il carrello è vuoto, la tabella sparisce
                            if ($("tbody tr").length === 0) {
                                $(".cart-layout").hide();
                                $("#carrello-vuoto").show();
                            }
                        });
                        mostraMessaggio(true, "Il prodotto è stato rimosso dal carrello.");
                    } else {
                        // Aggiorna gli input e il subtotale della riga
                        $("#qty-" + id_prodotto).val(nuova_quantita);
                        $("#subtotal-" + id_prodotto).empty().append($("<strong>").text(risposta.dati.totale_riga));
                    }

                    // Aggiorna i totali globali
                    $("#cart-total, #cart-subtotal").text(risposta.dati.totale_carrello);

                    // Aggiorna il badge del carrello nella navbar
                    aggiornaBadgeCarrello(risposta.dati.conteggio_carrello);
                } else {
                    mostraMessaggio(false, risposta.messaggio);
                }
            },
            error: function () {
                mostraMessaggio(false, "Si è verificato un errore di comunicazione con il server. Riprova più tardi.");
            }
        });
    }


    // Gestisce i bottoni della quantità
    $(".qty-btn").on("click", function () {
        const azione = $(this).data("action");
        const id = $(this).data("id");
        const max = $(this).data("max");
        const $riga = $("#row-" + id);
        let quantita_attuale = parseInt($("#qty-" + id).val(), 10);

        if (azione === "increase") {
            if (quantita_attuale < max) {
                aggiornaRigaCarrello(id, quantita_attuale + 1, $riga);
            } else {
                mostraMessaggio(false, "Hai aggiunto la quantità massima disponibile.");
            }
        } else if (azione === "decrease") {
            if (quantita_attuale > 1) {
                aggiornaRigaCarrello(id, quantita_attuale - 1, $riga);
            } else {
                // Se la quantità è 1 e si clicca meno, apre il modale di conferma
                id_da_rimuovere = id;
                const dialogo_conferma = $("#modale-rimozione")[0];
                if (dialogo_conferma) dialogo_conferma.showModal();
            }
        }
    });
    

    // Gestisce la rimozione di un prodotto compreso il modale di conferma
    $(".trigger-rimuovi").on("click", function () {
        id_da_rimuovere = $(this).data("id");
        const dialogo_rimozione = $("#modale-rimozione")[0];
        if (dialogo_rimozione) dialogo_rimozione.showModal();
    });

    $("#conferma-rimozione-btn").on("click", function () {
        const $riga = $("#row-" + id_da_rimuovere);
        $("#modale-rimozione")[0].close();
        aggiornaRigaCarrello(id_da_rimuovere, 0, $riga);
    });

    $(".chiudi-modale").on("click", function () {
        $(this).closest("dialog")[0].close();
        id_da_rimuovere = 0; 
    });

});