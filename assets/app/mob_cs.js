/**
 * Mobile Collection > Transaksi CS: tabel server-side dari mob-ajax-grid-data_trns.php
 * (konfigurasi sama dengan versi lama di mob_cs.php).
 */
$(document).ready(function () {
    $('#lookup').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            url: "mob-ajax-grid-data_trns.php", // json datasource
            type: "post",
            error: function () {  // error handling
                $(".lookup-error").html("");
                $("#lookup").append('<tbody class="employee-grid-error"><tr><th colspan="3">No data found in the server</th></tr></tbody>');
                $("#lookup_processing").css("display", "none");
            }
        }
    });
});
