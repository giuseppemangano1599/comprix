/* Script jQuery per la gestione del recupero password Single-Page.
 * Gestisce la richiesta AJAX al click su "Continua" e la visualizzazione 
 * condizionale dei campi successivi.
 */
$(document).ready(function () {
    
    // Gestisce il click sul bottone "Continua"
    $("#btn-continua").on("click", function () {
        const $btn = $(this);
        const tipo_utente = $("#user-type").val();
        const $input_utente = $("#username-or-email");
        const cred_utente = $input_utente.val().trim();
        const $feedback_utente = $("#user-feedback");
        const $feedback_tipo = $("#type-feedback");
        const $step2 = $("#step-2-container");

        // Reset visivo errori
        $input_utente.removeClass("is-invalid is-valid");
        $feedback_utente.text("");
        $feedback_tipo.text("");

        // Controlli di validità prima della chiamata AJAX
        if (!tipo_utente) {
            $input_utente.addClass("is-invalid");
            $feedback_tipo.text("Seleziona il tipo di account.").css("color", "red");
            return;
        }
        if (cred_utente.length === 0) {
            $input_utente.addClass("is-invalid");
            $feedback_utente.text("Inserisci l'username o l'e-mail.").css("color", "red");
            return;
        }

        // Viene effettuata la chiamata AJAX
        $.ajax({
            url: "../ajax/ajax_pw_dimenticata.php",
            type: "POST",
            dataType: "json",
            data: {
                action: "get_domanda",
                user_type: tipo_utente,
                username_or_email: cred_utente
            },
            success: function (resp) {
                if (resp.success) {
                    // Mostra il secondo step
                    $("#testo-domanda").text(resp.domanda);
                    
                    // Blocca i campi superiori per evitare modifiche incoerenti
                    $("#user-type, #username-or-email").prop("readonly", true);
                    $btn.hide();

                    // Mostra e abilita la seconda parte
                    $step2.slideDown();
                    $("#step-2-container input").prop("disabled", false);
                    
                    $input_utente.addClass("is-valid");
                } else {
                    // Se l'utente non è stato trovato ma evita di dire cosa è sbagliato
                    $input_utente.addClass("is-invalid");
                    $feedback_utente.text("I dati sono errati, riprova.").css("color", "red");
                    $btn.prop("disabled", false).text("Continua");
                }
            },
            error: function () {
                $feedback_utente.text("Si è verificato un errore di comunicazione con il server. Riprova più tardi.").css("color", "red");
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