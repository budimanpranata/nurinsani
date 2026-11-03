/* Koreksi level kp (kp/view_koreksi.php): daftar jurnal 2 hari terakhir, Edit dan Hapus; permintaan sama dengan versi
   lama (GET ajax_koreksi.php?type=koreksi, POST crud_koreksi.php aksi=get/edit/hapus). */
$(function () {
    var modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalForm'));
    var aksi = '';

    function teks(v) {
        return $('<div>').text(v === null || v === undefined ? '' : v).html();
    }

    // server-side: hanya satu halaman yang diambil; cari/urut/halaman dikerjakan ajax_koreksi.php di database
    var table = $('#omzetTable').DataTable({
        processing: true,
        serverSide: true,
        searchDelay: 500,
        ajax: { url: 'ajax_koreksi.php?type=koreksi', type: 'POST' },
        columns: [
            { data: 'id' },
            { data: 'tanggal' },
            { data: 'unit' },
            { data: 'kode_transaksi' },
            { data: 'kode_rekening' },
            { data: 'keterangan_transaksi' },
            { data: 'jenis_transaksi' },
            { data: 'debet', className: 'text-end' },
            { data: 'kredit', className: 'text-end' },
            {
                data: null, orderable: false, className: 'text-center text-nowrap',
                render: function (data) {
                    return '<button type="button" class="btn btn-outline-warning btn-sm" data-edit="' + teks(data.id) + '">Edit</button> '
                        + '<button type="button" class="btn btn-outline-danger btn-sm" data-hapus="' + teks(data.id) + '">Hapus</button>';
                }
            }
        ],
        columnDefs: [{ targets: [0, 1, 2, 3, 4, 5, 6, 7, 8], render: function (v) { return teks(v); } }],
        pageLength: 10,
        language: {
            processing: 'Memuat data…',
            search: 'Cari:',
            lengthMenu: 'Tampilkan _MENU_ data',
            zeroRecords: 'Tidak ada data',
            info: 'Menampilkan _START_ - _END_ dari _TOTAL_ data',
            infoEmpty: 'Tidak ada data',
            infoFiltered: '(disaring dari _MAX_ data)',
            paginate: { first: 'Awal', last: 'Akhir', next: '›', previous: '‹' }
        }
    });

    // --- SIMPAN DATA (Edit) ---
    $('#formData').on('submit', function (e) {
        e.preventDefault();
        $.post('crud_koreksi.php', $(this).serialize() + '&aksi=' + aksi, function (res) {
            if (res == 'ok') {
                modal.hide();
                $('#formData')[0].reset();
                table.ajax.reload(null, false);
            }
        });
    });

    // --- EDIT DATA ---
    $('#omzetTable').on('click', '[data-edit]', function () {
        aksi = 'edit';
        $.post('crud_koreksi.php', { id: $(this).attr('data-edit'), aksi: 'get' }, function (res) {
            var data = typeof res === 'string' ? JSON.parse(res) : res;
            $('#id').val(data.id);
            $('#tanggal').val(data.tanggal);
            $('#kode_rekening').val(data.kode_rekening);
            $('#kode_transaksi').val(data.kode_transaksi);
            $('#debet').val(data.debet);
            $('#kredit').val(data.kredit);
            modal.show();
        });
    });

    // --- HAPUS DATA ---
    $('#omzetTable').on('click', '[data-hapus]', function () {
        if (confirm('Yakin ingin menghapus data ini?')) {
            $.post('crud_koreksi.php', { id: $(this).attr('data-hapus'), aksi: 'hapus' }, function (res) {
                if (res == 'ok') { table.ajax.reload(null, false); }
            });
        }
    });
});
