/* Form approve pengajuan (new4.php): validasi isian wajib, huruf besar, wajib angka, dan format rupiah
   sama dengan versi lama. */
(function () {
    var form = document.getElementById('form-approve');

    // isian wajib dan pesannya, urutan sama dengan validateForm() versi lama
    var wajib = [
        ['branch', 'KODE Branch Masih kosong'],
        ['id', 'NOMOR KTP Masih kosong'],
        ['code_kel', 'KODE Kelopok Masih kosong'],
        ['cif', 'CIF Masih kosong'],
        ['nama', 'Nama Masih kosong'],
        ['tempat_lahir', 'TEMPAT LAHIR Masih kosong'],
        ['wali', 'NAMA PENJAMIN masih kosong'],
        ['tgl_lahir', 'TANGGAL LAHIR masih kosong'],
        ['no_tlp', 'NOMOR TLP masih kosong'],
        ['tlp_penjamin', 'NOMOR TLP Penjamin masih kosong'],
        ['alamat', 'ALAMAT masih kosong'],
        ['npwp', 'NPWP masih kosong'],
        ['rt', 'RT/RW masih kosong'],
        ['ibu', 'IBU KANDUNG masih kosong'],
        ['kd_pos', 'KODE POS masih kosong'],
        ['tgl_skep', 'TANGGAL WAKALAH masih kosong'],
        ['tgl_akad', 'TANGGAL AKAD MURABAHAH masih kosong'],
        ['lurah', 'LURAH masih kosong'],
        ['kec', 'KECAMATAN masih kosong'],
        ['kota', 'KOTA masih kosong'],
        ['pekerjaan', 'Pekerjaan masih kosong'],
        ['id_expire', ' id_expire masih kosong'],
        ['pla', ' PEMBIAYAAN masih kosong']
    ];

    document.getElementById('tombol-approve').addEventListener('click', function () {
        for (var i = 0; i < wajib.length; i++) {
            var el = form.elements[wajib[i][0]];
            if (el.value === '') {
                alert(wajib[i][1]);
                el.focus();
                return;
            }
        }
        form.submit();
    });

    // huruf besar saat mengetik
    form.querySelectorAll('[data-huruf-besar]').forEach(function (el) {
        el.addEventListener('keyup', function () {
            el.value = el.value.toUpperCase();
        });
    });

    // hanya angka
    form.querySelectorAll('[data-wajib-angka]').forEach(function (el) {
        el.addEventListener('keypress', function (evt) {
            var kode = evt.which || evt.keyCode;
            if (kode > 31 && (kode < 48 || kode > 57)) {
                evt.preventDefault();
            }
        });
    });

    // tampilan rupiah di bawah isian Disetujui (versi lama: formatCurrency ke #format)
    function formatRupiah(num) {
        num = num.toString().replace(/[^0-9]/g, '');
        if (num === '') {
            num = '0';
        }
        return 'Rp. ' + num.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }
    form.querySelectorAll('[data-format-rupiah]').forEach(function (el) {
        el.addEventListener('keyup', function () {
            document.getElementById('format').textContent = formatRupiah(el.value);
        });
    });
})();
