/* Funzioni condivise dai vari script del sito (solo helper richiamati dalle pagine).
 * Va caricato prima dello script specifico della pagina.
 */

// Escape minimo per i dati provenienti dal server prima di inserirli nel DOM.
function escapeHtml(valore) {
    return String(valore).replace(/[&<>"']/g, function (c) {
        return { "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" }[c];
    });
}

// Aggiorna il badge del carrello nella navbar (lo nasconde se il conteggio è zero).
function aggiornaBadgeCarrello(conteggio) {
    if (conteggio > 0) {
        $("#cart-count-badge").text(conteggio).removeClass("hidden");
    } else {
        $("#cart-count-badge").addClass("hidden");
    }
}

// Collega un checkbox "mostra password" al suo campo di input.
function impostaTogglePassword(selettoreCheckbox, selettoreCampo) {
    $(selettoreCheckbox).on("change", function () {
        $(selettoreCampo).attr("type", $(this).is(":checked") ? "text" : "password");
    });
}

// Confronta password e conferma mostrando il feedback.
// Con bloccaInvio=true impedisce anche l'invio del form se non coincidono.
function confrontaPassword(selettorePassword, selettoreConferma, selettoreFeedback, bloccaInvio) {
    const pass = $(selettorePassword).val();
    const $confirm = $(selettoreConferma);
    const conf = $confirm.val();
    const $feedback = $(selettoreFeedback);

    // Se la conferma è vuota, pulisci il feedback
    if (conf === "") {
        $confirm.removeClass("is-valid is-invalid");
        if (bloccaInvio) $confirm[0].setCustomValidity("");
        $feedback.text("");
        return;
    }

    if (pass !== conf) {
        $confirm.removeClass("is-valid").addClass("is-invalid");
        if (bloccaInvio) $confirm[0].setCustomValidity("Le password non coincidono");
        $feedback.text("Le password non coincidono").css("color", "red");
    } else {
        $confirm.removeClass("is-invalid").addClass("is-valid");
        if (bloccaInvio) $confirm[0].setCustomValidity("");
        $feedback.text("Le password coincidono").css("color", "green");
    }
}
