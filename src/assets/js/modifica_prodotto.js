/* Script jQuery per la gestione della pagina di modifica prodotto.
 * Gestisce l'apertura e la chiusura del modale di conferma dell'eliminazione
 * della foto del prodotto, passando l'id al form interno del modale.
 */
$(document).ready(function () {
    const $modale = $("#delete-photo-modal");
    const $idFotoModale = $("#modal-photo-id");

    // Apre il modale quando si clicca sulla "X" di una foto
    $(".delete-photo-trigger").on("click", function () {
        const idFoto = $(this).data("id");
        $idFotoModale.val(idFoto);
        if ($modale[0]) $modale[0].showModal();
    });

    // Chiude il modale cliccando su Annulla
    $(".chiudi-modale").on("click", function () {
        $modale[0].close();
    });

    // Chiude il modale cliccando se si clicca fuori dalla finestrella
    $modale.on("click", function (e) {
        if (e.target === this) {
            this.close();
        }
    });
});