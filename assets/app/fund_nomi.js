/* Data Nominatif deposito level fund (fund/view/view_nomi.php): DataTables di sisi browser. */
$(function () {
    $('#tabel_nomi').DataTable({
        pageLength: 10,
        columnDefs: [
            { orderable: false, targets: [0] }
        ],
        order: [[1, 'asc']],
        language: {
            search: 'Cari:',
            lengthMenu: 'Tampilkan _MENU_ baris',
            info: 'Baris _START_–_END_ dari _TOTAL_',
            infoEmpty: 'Tidak ada data',
            infoFiltered: '(disaring dari _MAX_ data)',
            zeroRecords: 'Data tidak ditemukan',
            emptyTable: 'Tidak ada data deposito',
            paginate: { first: 'Awal', last: 'Akhir', next: '›', previous: '‹' }
        }
    });
});
