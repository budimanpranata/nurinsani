/* Approval Pengajuan (data_approval.php): DataTables server-side dengan protokol lama (sAjaxSource, POST),
   pengaturan kolom dan pencarian per kolom (footer) sama dengan versi lama. */
$(function () {
    var dTable = $('#example').DataTable({
        bProcessing: true,
        bServerSide: true,
        sAjaxSource: 'server_side_approval.php',
        sServerMethod: 'POST',
        columnDefs: [
            { orderable: false, targets: 0, searchable: false },
            { orderable: true, targets: [1, 2, 3, 4, 5, 6], searchable: true },
            { className: 'text-end', targets: [4, 5, 6] },
            { orderable: false, className: 'text-center', targets: 7 }
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

    // pencarian per kolom di baris paling bawah
    dTable.columns().every(function () {
        var kolom = this;
        $('input', this.footer()).on('keyup change', function () {
            if (kolom.search() !== this.value) {
                kolom.search(this.value).draw();
            }
        });
    });
});
