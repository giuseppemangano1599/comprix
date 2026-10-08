/* Script scritto in jQuery che effettua una chiamata AJAX per controllare
 * la disponibilità dell'username e dell'e-mail durante la compilazione del form.
 * Viene effettuato un controllo anche sulla corrispondenza tra la password e la conferma.
 * Viene anche gestito il comportamento dell'interruttore "Mostra password".
 * Questo script non effettua la validazione del form perché HTML permette già 
 * di applicare delle regole per la validazione lato client (tra cui la possibilità 
 * di inserire espressioni regolari) e per questo tipo di form, i controlli sarebbero
 * ridondanti senza avere un effettivo beneficio.
 * Sebbene HTML si possa aggirare, anche JavaScript è disattivabile dal browser
 * e quindi il controllo più severo viene affidato lato server 
 * a "controller_registrazione.php" che è stato predisposto per bloccare 
 * eventuali input malevoli che potrebbero causare XSS e/o SQL Injection.
 */
$(document).ready(function () {
    // Funzione AJAX per controllare la disponibilità dell'username e dell'e-mail
    function verificaDisponibilita(input_da_verificare) {
        const $input = $(input_da_verificare);
        const campo = $input.attr("name"); // "username" o "email"
        const valore = $input.val().trim();
        const $feedback = $input.siblings("small");

        /* checkValidity effettua un controllo sul formato controllando gli attributi del form.
         * Se il formato non è valido (es. e-mail non valida), evita di fare la chiamata AJAX.
         */
        if (!$input[0].checkValidity()) {
            return;
        }

        $.ajax(
            {
                url: "../ajax/ajax_controlla_disponibilita.php",
                type: "POST",
                dataType: "json",
                data: {
                    campo: campo,
                    valore: valore
                },
                success: function (risposta) {
                    if (!risposta.successo) {
                        $feedback.text("Si è verificato un errore di comunicazione con il server. Riprova più tardi.").css("color", "red");
                        return;
                    }

                    if (risposta.disponibile) {
                        $input[0].setCustomValidity("");
                        $feedback.text(risposta.messaggio).css("color", "green");
                        $input.removeClass("is-invalid").addClass("is-valid");
                    } else {
                        $input[0].setCustomValidity(risposta.messaggio);
                        $feedback.text(risposta.messaggio).css("color", "red");
                        $input.removeClass("is-valid").addClass("is-invalid");
                    }
                },
                error: function () {
                    $feedback.text("Si è verificato un errore di comunicazione con il server. Riprova più tardi.").css("color", "red");
                },
            }
        );
    }


    // Controlla quando l'utente esce dal campo (blur)
    $("#username, #email").on("blur", function () {
        verificaDisponibilita(this);
    });


    // Resetta il feedback mentre l'utente scrive
    $("#username, #email").on("input", function () {
        this.setCustomValidity("");
        $(this).removeClass("is-valid is-invalid");
        $(this).siblings("small").text("");
    });


    // Controlla appena viene digitato un carattere (blocca anche l'invio se non coincidono)
    $("#password, #confirm-password").on("input", function () {
        confrontaPassword("#password", "#confirm-password", "#confirm-password-feedback", true);
    });

    impostaTogglePassword("#show-password", "#password");
    impostaTogglePassword("#show-confirm-password", "#confirm-password");
});
