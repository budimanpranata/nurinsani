/* Data Master Deposan level fund (fund/view/master_fund.php): DataTables server-side, permintaan sama dengan versi
   lama (POST ke ajax-grid-data.php). */
$(function () {
    $('#lookup').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: 'ajax-grid-data.php',
            type: 'post'
        },
        columnDefs: [
            { className: 'text-center text-nowrap', orderable: false, targets: 6 }
        ],
        language: {
            processing: 'Memuat data…',
            search: 'Cari:',
            lengthMenu: 'Tampilkan _MENU_ baris',
            info: 'Baris _START_–_END_ dari _TOTAL_',
            infoEmpty: 'Tidak ada data',
            infoFiltered: '(disaring dari _MAX_ data)',
            zeroRecords: 'Data tidak ditemukan',
            paginate: { first: 'Awal', last: 'Akhir', next: '›', previous: '‹' }
        }
    });
});
