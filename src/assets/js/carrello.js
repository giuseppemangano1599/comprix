/* Script scritto in jQuery per la pagina del carrello.
 * Gestisce le operazioni di aggiunta e rimozione di un prodotto.
 */
$(document).ready(function () {
    // Variabile per memorizzare l'ID del prodotto da rimuovere tramite il modale
    let idDaRimuovere = 0;

    // Mostra il messaggio di feedback (verde/rosso) in alto
    function mostraMessaggio(successo, messaggio) {
        const $msg = $("#msg-carrello");
        $msg.removeClass("alert-success alert-error")
            .addClass(successo ? "alert-success" : "alert-error")
            .text(messaggio)
            .show()
            .delay(3000)
            .fadeOut();
    }

    // Una sola operazione alla volta: i click rapidi durante una richiesta vengono ignorati
    let richiestaInCorso = false;

    // Aggiorna una riga del carrello (o la rimuove se la quantità è 0)
    function aggiornaRigaCarrello(idProdotto, nuovaQuantita, $riga) {
        if (richiestaInCorso) return;
        richiestaInCorso = true;

        // Se stiamo per rimuovere (quantita 0), usiamo l'azione "rimuovi" per pulizia,
        // altrimenti "aggiorna" per cambiare la quantità.
        const azione = (nuovaQuantita <= 0) ? "rimuovi" : "aggiorna";

        $.ajax({
            url: "../controllers/controller_ajax_carrello.php",
            type: "POST",
            dataType: "json",
            data: {
                azione: azione,
                id_prodotto: idProdotto,
                quantita: nuovaQuantita,
                csrf_token: $("meta[name=csrf-token]").attr("content")
            },
            success: function (risposta) {
                if (risposta.successo) {
                    if (nuovaQuantita <= 0) {
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
                        $("#qty-" + idProdotto).val(nuovaQuantita);
                        $("#subtotal-" + idProdotto).empty().append($("<strong>").text(risposta.dati.totale_riga));
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
            },
            complete: function () {
                richiestaInCorso = false;
            }
        });
    }


    // Gestisce i bottoni della quantità
    $(".qty-btn").on("click", function () {
        const azione = $(this).data("action");
        const id = $(this).data("id");
        const max = $(this).data("max");
        const $riga = $("#row-" + id);
        let quantitaAttuale = parseInt($("#qty-" + id).val(), 10);

        if (azione === "increase") {
            if (quantitaAttuale < max) {
                aggiornaRigaCarrello(id, quantitaAttuale + 1, $riga);
            } else {
                mostraMessaggio(false, "Hai aggiunto la quantità massima disponibile.");
            }
        } else if (azione === "decrease") {
            if (quantitaAttuale > 1) {
                aggiornaRigaCarrello(id, quantitaAttuale - 1, $riga);
            } else {
                // Se la quantità è 1 e si clicca meno, apre il modale di conferma
                idDaRimuovere = id;
                const dialogoConferma = $("#modale-rimozione")[0];
                if (dialogoConferma) dialogoConferma.showModal();
            }
        }
    });
    

    // Gestisce la rimozione di un prodotto compreso il modale di conferma
    $(".trigger-rimuovi").on("click", function () {
        idDaRimuovere = $(this).data("id");
        const dialogoRimozione = $("#modale-rimozione")[0];
        if (dialogoRimozione) dialogoRimozione.showModal();
    });

    $("#conferma-rimozione-btn").on("click", function () {
        const $riga = $("#row-" + idDaRimuovere);
        $("#modale-rimozione")[0].close();
        aggiornaRigaCarrello(idDaRimuovere, 0, $riga);
    });

    $(".chiudi-modale").on("click", function () {
        $(this).closest("dialog")[0].close();
        idDaRimuovere = 0; 
    });

    // Chiude il modale cliccando sullo sfondo
    $("dialog").on("click", function (e) {
        if (e.target === this) {
            this.close();
            idDaRimuovere = 0;
        }
    });

});