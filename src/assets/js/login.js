// Script scritto in jQuery per mostrare la password a schermo durante l'accesso al sito
$(document).ready(function () {
    // Gestisce l'interruttore per mostrare o nascondere la password
    $("#show-password").on("change", function () {
        $("#password").attr("type", $(this).is(":checked") ? "text" : "password");
    });
});