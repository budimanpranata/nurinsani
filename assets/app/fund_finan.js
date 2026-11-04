/* Param Financial level fund (fund/view/view_finan.php): DataTables server-side dari ajax-grid-data_finan.php; tombol
   Hapus (hanya periode terbaru) meminta konfirmasi lalu mengirim form POST #form_hapus. */
$(function () {
    var teks = function (v) {
        // tampilkan sebagai teks (bukan HTML)
        return v === null || v === undefined ? '' : String(v)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    };
    $('#tabel_finan').DataTable({
        processing: true,
        serverSide: true,
        searchDelay: 400,
        ajax: { url: 'ajax-grid-data_finan.php' },
        columns: [
            { data: 'kode', render: teks },
            { data: 'nominal', render: teks, className: 'text-end' },
            { data: 'margin', render: teks, className: 'text-end' },
            { data: 'bulan', render: teks },
            { data: 'sd', render: teks },
            { data: 'hari', render: teks, className: 'text-end' },
            {
                data: null, orderable: false, searchable: false, className: 'text-center',
                render: function (d) {
                    return d.hapus ? '<button type="button" class="btn btn-sm btn-outline-danger" data-hapus="' + teks(d.kode) + '">Hapus</button>' : '';
                }
            }
        ],
        order: [[0, 'desc']],
        pageLength: 10,
        language: {
            processing: 'Memuat data…',
            search: 'Cari:',
            lengthMenu: 'Tampilkan _MENU_ baris',
            info: 'Baris _START_–_END_ dari _TOTAL_',
            infoEmpty: 'Tidak ada data',
            infoFiltered: '(disaring dari _MAX_ data)',
            zeroRecords: 'Data tidak ditemukan',
            emptyTable: 'Belum ada parameter financial',
            paginate: { first: 'Awal', last: 'Akhir', next: '›', previous: '‹' }
        }
    });
    $(document).on('click', '[data-hapus]', function () {
        var kode = $(this).attr('data-hapus');
        if (window.confirm('Hapus parameter financial ' + kode + '?')) {
            $('#hapus_kode').val(kode);
            $('#form_hapus').trigger('submit');
        }
    });
});
