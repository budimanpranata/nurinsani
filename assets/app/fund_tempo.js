/* Data Jatuh Tempo level fund (fund/view/view_tempo.php): DataTables di sisi browser (data per bulan sudah difilter server). */
$(function () {
    $('#tabel_tempo').DataTable({
        pageLength: 10,
        columnDefs: [
            { orderable: false, targets: [0, 9] }
        ],
        order: [[7, 'asc']],
        language: {
            search: 'Cari:',
            lengthMenu: 'Tampilkan _MENU_ baris',
            info: 'Baris _START_–_END_ dari _TOTAL_',
            infoEmpty: 'Tidak ada data',
            infoFiltered: '(disaring dari _MAX_ data)',
            zeroRecords: 'Data tidak ditemukan',
            emptyTable: 'Tidak ada deposito jatuh tempo',
            paginate: { first: 'Awal', last: 'Akhir', next: '›', previous: '‹' }
        }
    });
});
