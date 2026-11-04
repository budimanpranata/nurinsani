/* View Realisasi Baghas level fund (fund/view/view_baghas.php): DataTables server-side dari serverSide.php, saringan
   Status dikirim sebagai parameter status. */
$(function () {
    var teks = function (v) {
        // tampilkan sebagai teks (bukan HTML)
        return v === null || v === undefined ? '' : String(v)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    };
    var tabel = $('#tabel_baghas').DataTable({
        processing: true,
        serverSide: true,
        searchDelay: 400,
        ajax: {
            url: 'serverSide.php',
            data: function (d) {
                d.status = $('#status').val();
            }
        },
        columns: [
            { data: 'norek', render: teks },
            { data: 'nama', render: teks },
            { data: 'ke', render: teks, className: 'text-end' },
            { data: 'nisbah', render: teks, className: 'text-end' },
            { data: 'tempo', render: teks },
            { data: 'bayar', render: teks },
            { data: 'status', render: teks }
        ],
        order: [[0, 'asc']],
        pageLength: 10,
        language: {
            processing: 'Memuat data…',
            search: 'Cari:',
            lengthMenu: 'Tampilkan _MENU_ baris',
            info: 'Baris _START_–_END_ dari _TOTAL_',
            infoEmpty: 'Tidak ada data',
            infoFiltered: '(disaring dari _MAX_ data)',
            zeroRecords: 'Data tidak ditemukan',
            emptyTable: 'Tidak ada data bagi hasil',
            paginate: { first: 'Awal', last: 'Akhir', next: '›', previous: '‹' }
        }
    });
    $('#status').on('change', function () {
        tabel.ajax.reload();
    });
});
