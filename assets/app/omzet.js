/* Omzet dan Nisbah (view_omzet.php): alur sama dengan versi lama, data dari ajax/ajax_omzet.php, simpan lewat crud_omzet.php. */
$(function () {
    var aksi = '';
    var modalForm = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalForm'));
    var modalView = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalView'));
    var form = document.getElementById('formData');

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

    // --- TABEL OMZET UTAMA ---
    // server-side: hanya halaman yang tampil yang diambil (dulu seluruh omzet unit dimuat sekaligus)
    var table = $('#omzetTable').DataTable({
        processing: true,
        serverSide: true,
        searchDelay: 400,
        ajax: { url: 'ajax/ajax_omzet.php?type=omzet', dataSrc: 'data' },
        columns: [
            { data: 'id', render: teks },
            { data: 'tanggal', render: teks },
            { data: 'nama_kel', render: teks },
            { data: 'nama_ao', render: teks },
            { data: 'cif', render: teks },
            { data: 'nama', render: teks },
            { data: 'omzet', render: teks, className: 'text-end' },
            {
                data: null, orderable: false, searchable: false, className: 'text-center text-nowrap',
                render: function (data) {
                    return '<button type="button" class="btn btn-outline-warning btn-sm" data-edit="' + teks(data.id) + '">Edit</button> ' +
                        '<button type="button" class="btn btn-outline-danger btn-sm" data-hapus="' + teks(data.id) + '">Hapus</button>';
                }
            }
        ],
        pageLength: 10,
        language: $.extend({ processing: 'Memuat data…' }, bahasa)
    });

    // pesan dari crud_omzet.php selain "ok" (mis. CIF bukan anggota unit ini) ditampilkan
    var tampilkanPesan = function (res) {
        if (res && res != 'ok') alert(res);
    };

    // --- SIMPAN DATA (Tambah/Edit) ---
    $(form).on('submit', function (e) {
        e.preventDefault();
        $.ajax({
            url: 'crud_omzet.php',
            type: 'POST',
            data: $(this).serialize() + '&aksi=' + aksi,
            success: function (res) {
                if (res == 'ok') {
                    modalForm.hide();
                    form.reset();
                    table.ajax.reload();
                } else {
                    tampilkanPesan(res);
                }
            }
        });
    });

    // --- TAMBAH DATA ---
    $('#tombol-tambah').on('click', function () {
        aksi = 'tambah';
        $('#modalTitle').text('Tambah Data');
        form.reset();
        modalForm.show();
    });

    // --- EDIT DATA ---
    $('#omzetTable').on('click', '[data-edit]', function () {
        aksi = 'edit';
        $('#modalTitle').text('Edit Data');
        $.post('crud_omzet.php', { id: $(this).attr('data-edit'), aksi: 'get' }, function (res) {
            var data = JSON.parse(res);
            if (!data) {
                alert('Data omzet tidak ditemukan di unit ini');
                return;
            }
            $('#id').val(data.id);
            $('#tanggal').val(data.tanggal);
            $('#cif').val(data.cif);
            $('#nominal').val(data.nominal);
            modalForm.show();
        }, 'text');
    });

    // --- HAPUS DATA ---
    $('#omzetTable').on('click', '[data-hapus]', function () {
        var id = $(this).attr('data-hapus');
        if (confirm('Yakin ingin menghapus data ini?')) {
            $.post('crud_omzet.php', { id: id, aksi: 'hapus' }, function (res) {
                if (res == 'ok') table.ajax.reload(); else tampilkanPesan(res);
            }, 'text');
        }
    });

    // --- LIHAT NISBAH (MUSYAROKAH) ---
    $('#tombol-nisbah').on('click', function () {
        modalView.show();
        $('#viewTable').DataTable({
            destroy: true,
            ajax: { url: 'ajax/ajax_omzet.php?type=calonloan', dataSrc: 'data' },
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
