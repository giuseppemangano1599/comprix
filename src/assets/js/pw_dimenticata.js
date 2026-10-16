/* Script jQuery per la gestione del recupero password Single-Page.
 * Gestisce la richiesta AJAX al click su "Continua" e la visualizzazione 
 * condizionale dei campi successivi.
 */
$(document).ready(function () {
    
    // Gestisce il click sul bottone "Continua"
    $("#btn-continua").on("click", function () {
        const $btn = $(this);
        const tipoUtente = $("#user-type").val();
        const $inputUtente = $("#username-or-email");
        const credUtente = $inputUtente.val().trim();
        const $feedbackUtente = $("#user-feedback");
        const $feedbackTipo = $("#type-feedback");
        const $step2 = $("#step-2-container");

        // Reset visivo errori
        $inputUtente.removeClass("is-invalid is-valid");
        $feedbackUtente.text("");
        $feedbackTipo.text("");

        // Controlli di validità prima della chiamata AJAX
        if (!tipoUtente) {
            $inputUtente.addClass("is-invalid");
            $feedbackTipo.text("Seleziona il tipo di account.").css("color", "red");
            return;
        }
        if (credUtente.length === 0) {
            $inputUtente.addClass("is-invalid");
            $feedbackUtente.text("Inserisci l'username o l'e-mail.").css("color", "red");
            return;
        }

        // Viene effettuata la chiamata AJAX
        $.ajax({
            url: "../ajax/ajax_pw_dimenticata.php",
            type: "POST",
            dataType: "json",
            data: {
                action: "get_domanda",
                user_type: tipoUtente,
                username_or_email: credUtente
            },
            success: function (resp) {
                if (resp.successo) {
                    // Mostra il secondo step
                    $("#testo-domanda").text(resp.domanda);
                    
                    // Blocca i campi superiori per evitare modifiche incoerenti
                    $("#user-type, #username-or-email").prop("readonly", true);
                    $btn.hide();

                    // Mostra e abilita la seconda parte
                    $step2.slideDown();
                    $("#step-2-container input").prop("disabled", false);
                    
                    $inputUtente.addClass("is-valid");
                } else {
                    // Se l'utente non è stato trovato ma evita di dire cosa è sbagliato
                    $inputUtente.addClass("is-invalid");
                    $feedbackUtente.text("I dati sono errati, riprova.").css("color", "red");
                    $btn.prop("disabled", false).text("Continua");
                }
            },
            error: function () {
                $feedbackUtente.text("Si è verificato un errore di comunicazione con il server. Riprova più tardi.").css("color", "red");
                $btn.prop("disabled", false).text("Continua");
            }
        });
    });

    $("#new-password, #confirm-password").on("input", function () {
        confrontaPassword("#new-password", "#confirm-password", "#confirm-password-feedback", false);
    });

    impostaTogglePassword("#show-password", "#new-password");
    impostaTogglePassword("#show-confirm-password", "#confirm-password");
});