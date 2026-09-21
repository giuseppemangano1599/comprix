/* Script jQuery per la gestione della pagina di modifica prodotto.
 * Gestisce l'apertura e la chiusura del modale di conferma dell'eliminazione
 * della foto del prodotto, passando l'id al form interno del modale.
 */
$(document).ready(function () {
    const $modale = $("#delete-photo-modal");
    const $id_foto_modale = $("#modal-photo-id");

    // Apre il modale quando si clicca sulla "X" di una foto
    $(".delete-photo-trigger").on("click", function () {
        const id_foto = $(this).data("id");
        $id_foto_modale.val(id_foto);
        if ($modale[0]) $modale[0].showModal();
    });

    // Chiude il modale cliccando su Annulla
    $(".close-modal").on("click", function () {
        $modale[0].close();
    });

    // Chiude il modale cliccando se si clicca fuori dalla finestrella
    $modale.on("click", function (e) {
        if (e.target === this) {
            this.close();
        }
    });
});