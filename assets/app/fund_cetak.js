/* Cetak Bilyet / Cetak Advis level fund (fund/view/view_cetak_bilyet.php, view_advise.php): DataTables di sisi browser;
   sesudah tombol Print (PDF di tab baru) daftar dimuat ulang saat user kembali ke tab ini, karena deposito yang sudah
   dicetak berstatus 'Print' dan tidak lagi masuk daftar. */
$(function () {
    $('#tabel_cetak').DataTable({
        pageLength: 10,
        columnDefs: [
            { orderable: false, targets: [0, 8] }
        ],
        order: [[1, 'asc']],
        language: {
            search: 'Cari:',
            lengthMenu: 'Tampilkan _MENU_ baris',
            info: 'Baris _START_–_END_ dari _TOTAL_',
            infoEmpty: 'Tidak ada data',
            infoFiltered: '(disaring dari _MAX_ data)',
            zeroRecords: 'Data tidak ditemukan',
            emptyTable: 'Tidak ada data yang menunggu dicetak',
            paginate: { first: 'Awal', last: 'Akhir', next: '›', previous: '‹' }
        }
    });
    var sudahCetak = false;
    $(document).on('click', '[data-cetak]', function () {
        sudahCetak = true;
    });
    document.addEventListener('visibilitychange', function () {
        if (sudahCetak && document.visibilityState === 'visible') {
            window.location.reload();
        }
    });
});
