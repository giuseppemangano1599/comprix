/* Script jQuery per la gestione della pagina del profilo, 
 * degli indirizzi di spedizione e dei pagamenti.
 */
$(document).ready(function() {
        
    // Modale per l"eliminazione dell"indirizzo di spedizione
    const $modale_indirizzo = $("#delete-addr-modal");
    const $id_indirizzo_input = $("#modal-addr-id");

    $(".delete-addr-trigger").on("click", function() {
        const id = $(this).data("id");
        $id_indirizzo_input.val(id);
        $modale_indirizzo[0].showModal();
    });

    const $modale_pagamento = $("#delete-pay-modal");
    const $id_pagamento_input = $("#modal-pay-id");

    $(".delete-pay-trigger").on("click", function() {
        const id = $(this).data("id");
        $id_pagamento_input.val(id);
        $modale_pagamento[0].showModal();
    });

    // Chiude i modali
    $(".close-modal").on("click", function() {
        $(this).closest("dialog")[0].close();
    });

    // Chiude il modale cliccando sullo sfondo
    $("dialog").on("click", function(e) {
        if (e.target === this) {
            this.close();
        }
    });

    // Formattazione automatica degli input

    // Forza il maiuscolo per le province e gli IBAN
    $(".input-uppercase, #campo_provincia, #campo_iban").on("input", function() {
        $(this).val($(this).val().toUpperCase());
    });

    // Formattazione della data di scadenza (MM/AA) con slash automatico
    $("#campo_scadenza").on("input", function(e) {
        const input = $(this).val();
        
        // Se l'utente sta cancellando, non aggiungere lo slash
        if (e.originalEvent.inputType === "deleteContentBackward") return;

        // Aggiunge lo slash dopo il secondo carattere
        if (input.length === 2 && !input.includes("/")) {
            $(this).val(input + "/");
        }
    });

    // Mostra i campi relativi ai tipi di pagamento in base al tipo selezionato
    function gestisciCampiPagamento() {
        const tipo = $("#select_tipo_pagamento").val();
        
        $(".payment-fields").hide();
        
        /* Rimuove l'attributo "required" da tutti gli input nascosti
         * per evitare errori di validazione HTML5 su campi invisibili
         */
        $(".payment-fields input").prop("required", false);

        // Mostra il blocco giusto in base al tipo
        if (tipo === "carta_credito" || tipo === "carta_debito") {
            $("#box_carta").show();
            $("#box_carta input").prop("required", true);
        
        } else if (tipo === "paypal") {
            $("#box_paypal").show();
            $("#box_paypal input").prop("required", true);
        
        } else if (tipo === "bonifico") {
            $("#box_bonifico").show();
            $("#box_bonifico input").prop("required", true);
        
        } else if (tipo === "contrassegno") {
            $("#box_contrassegno").show();
        }
    }

    // Quando l'utente seleziona un metodo di pagamento
    $("#select_tipo_pagamento").change(function() {
        gestisciCampiPagamento();
    });

    if ($("#select_tipo_pagamento").length > 0) {
        gestisciCampiPagamento();
    }
});