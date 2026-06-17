/* Script jQuery per la gestione della pagina dei prodotti.
 * Gestisce l'apertura e la chiusura del modale di conferma dell'eliminazione
 * del prodotto, passando i dati (ID e nome) al form interno del modale.
 */
$(document).ready(function () {
    const $modale = $("#delete-modal");
    const $id_prodotto_modale = $("#modal-product-id");
    const $nome_prodotto_modale = $("#modal-product-name");

    // Apre il modale quando si clicca sul bottone "Elimina"
    $(".delete-trigger").on("click", function () {
        const id = $(this).data("id");
        const nome = $(this).data("name");
        $id_prodotto_modale.val(id);
        $nome_prodotto_modale.text(nome);
        $modale[0].showModal();
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