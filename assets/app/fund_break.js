/* Data Break level fund (fund/view/view_break.php): DataTables di sisi browser. */
$(function () {
    $('#tabel_break').DataTable({
        pageLength: 10,
        columnDefs: [
            { orderable: false, targets: [0, 8] }
        ],
        order: [[6, 'asc']],
        language: {
            search: 'Cari:',
            lengthMenu: 'Tampilkan _MENU_ baris',
            info: 'Baris _START_–_END_ dari _TOTAL_',
            infoEmpty: 'Tidak ada data',
            infoFiltered: '(disaring dari _MAX_ data)',
            zeroRecords: 'Data tidak ditemukan',
            emptyTable: 'Tidak ada deposito yang menunggu Break',
            paginate: { first: 'Awal', last: 'Akhir', next: '›', previous: '‹' }
        }
    });
});
