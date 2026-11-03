/* Setup Parameter > Tambah Coa level kp (kp/setup/perkiraan.php): daftar COA dengan DataTables (cari, urut, halaman),
   pengganti DataTables CDN versi lama. */
$(function () {
    $('#ghatable').DataTable({
        order: [[0, 'asc']],
        language: {
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
