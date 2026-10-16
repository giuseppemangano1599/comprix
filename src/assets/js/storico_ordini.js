/* Script in jQuery per l'annullamento di un ordine.
 * Gestisce il modale di conferma dell'annullamento di un ordine.
 */

$(document).ready(function () {
    const $modale = $("#modale_annulla_ordine");
    const $inputId = $("#input_id_ordine_annulla");

    // Gestione chiusura del modale
    $(".chiudi-modale").on("click", function () {
        $modale[0].close();
    });

    // Gestione del modale per annullare l'ordine
    $(".trigger-annulla-ordine").click(function (e) {
        e.preventDefault();
        var idOrdine = $(this).data("id");
        $inputId.val(idOrdine);
        if ($modale[0]) $modale[0].showModal();
    });

    // Chiude il modale cliccando se si clicca fuori dalla finestrella
    $modale.on("click", function (e) {
        if (e.target === this) {
            this.close();
        }
    });

});