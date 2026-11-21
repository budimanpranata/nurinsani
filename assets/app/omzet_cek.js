/* Cek Omzet & Porsi Nisbah (view_omzet_cek.php): pencarian dan halaman tabel di browser; centang CIF untuk Hitung ulang.
   Mencentang satu baris ikut mencentang semua baris CIF yang sama (juga di halaman tabel lain). Saat dikirim, CIF terpilih
   dari semua halaman ditambahkan ke form sebagai cif[]. */
$(function () {
    var tabel = $('#cekTable').DataTable({
        order: [],
        pageLength: 25,
        columnDefs: [{ orderable: false, targets: 0 }],
        language: {
            search: 'Cari:',
            lengthMenu: 'Tampilkan _MENU_ data',
            zeroRecords: 'Tidak ada data',
            info: 'Menampilkan _START_ - _END_ dari _TOTAL_ data',
            infoEmpty: 'Tidak ada data',
            infoFiltered: '(disaring dari _MAX_ data)',
            paginate: { next: '>', previous: '<' }
        }
    });

    function cifTerpilih() {
        var daftar = {};
        tabel.$('input.pilih-cif:checked').each(function () { daftar[this.value] = true; });
        return Object.keys(daftar);
    }
    function perbarui() {
        var n = cifTerpilih().length;
        $('#jumlah-cif').text(n);
        $('#tombol-hitung').prop('disabled', n === 0);
    }

    $('#cekTable').on('change', 'input.pilih-cif', function () {
        var cif = this.value, centang = this.checked;
        tabel.$('input.pilih-cif').filter(function () { return this.value === cif; }).prop('checked', centang);
        perbarui();
    });

    $('#form-hitung').on('submit', function () {
        var form = $(this);
        form.find('input[name="cif[]"]').remove();
        cifTerpilih().forEach(function (c) {
            $('<input type="hidden" name="cif[]">').val(c).appendTo(form);
        });
    });
    perbarui();
});
