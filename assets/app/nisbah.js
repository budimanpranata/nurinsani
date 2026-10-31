/* Monitoring Nisbah (view_nisbah.php): tabel sama dengan versi lama, data dari ajax/ajax_nisbah.php. */
$(function () {
    var modalView = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalView'));

    var teks = function (v) {
        // tampilkan sebagai teks (bukan HTML)
        return v === null || v === undefined ? '' : String(v)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    };
    var bahasa = {
        search: 'Cari:',
        lengthMenu: 'Tampilkan _MENU_ data',
        zeroRecords: 'Tidak ada data',
        info: 'Menampilkan _START_ - _END_ dari _TOTAL_ data',
        infoEmpty: 'Tidak ada data',
        infoFiltered: '(disaring dari _MAX_ data)',
        loadingRecords: 'Memuat data…',
        paginate: { next: '>', previous: '<' }
    };

    // --- TABEL NISBAH (jadwal musyarakah) ---
    // server-side: hanya halaman yang tampil yang diambil (dulu seluruh jadwal unit dimuat sekaligus)
    $('#nisbahTable').DataTable({
        processing: true,
        serverSide: true,
        searchDelay: 400,
        ajax: { url: 'ajax/ajax_nisbah.php?type=omzet', dataSrc: 'data' },
        columns: [
            { data: 'id', render: teks },
            { data: 'tanggal', render: teks },
            { data: 'nama_kel', render: teks },
            { data: 'nama_ao', render: teks },
            { data: 'cif', render: teks },
            { data: 'nama', render: teks },
            { data: 'cicilan', render: teks, className: 'text-end' },
            { data: 'nisbah', render: teks, className: 'text-end' },
            { data: 'nisbah_anggota', render: teks, className: 'text-end' }
        ],
        pageLength: 10,
        language: $.extend({ processing: 'Memuat data…' }, bahasa)
    });

    // --- LIHAT NISBAH (MUSYAROKAH) ---
    $('#tombol-nisbah').on('click', function () {
        modalView.show();
        $('#viewTable').DataTable({
            destroy: true,
            ajax: { url: 'ajax/ajax_nisbah.php?type=calonloan', dataSrc: 'data' },
            columns: [
                { data: 'nama_kel', render: teks },
                { data: 'nama_ao', render: teks },
                { data: 'cif', render: teks },
                { data: 'Cust_Short_name', render: teks },
                { data: 'Plafond', render: teks, className: 'text-end' },
                { data: 'nisbah', render: teks, className: 'text-end' },
                { data: 'omzet', render: teks, className: 'text-end' }
            ],
            language: bahasa
        });
    });
});
