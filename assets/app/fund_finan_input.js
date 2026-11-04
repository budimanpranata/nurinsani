/* Tambah Param Financial (fund/transaksi/input_finan.php): pratinjau Kode Bulan (NI + tahun + "-bulan" dari Periode
   Dari, sama dengan server) dan jumlah hari periode (s.d - dari, yang dipakai Posting Parameter). */
(function () {
    var dari = document.getElementById('bulan');
    var sd = document.getElementById('bulan_dua');
    var kode = document.getElementById('kode_bulan');
    var hari = document.getElementById('jumlah_hari');
    var form = document.getElementById('form_finan');
    var pola = /^\d{4}-\d{2}-\d{2}$/;

    function segarkan() {
        kode.value = pola.test(dari.value) ? 'NI' + dari.value.substring(0, 4) + dari.value.substring(4, 7) : '';
        if (pola.test(dari.value) && pola.test(sd.value)) {
            var n = Math.round((Date.parse(sd.value) - Date.parse(dari.value)) / 86400000);
            hari.textContent = '(' + n + ' hari)';
        } else {
            hari.textContent = '';
        }
    }
    dari.addEventListener('change', segarkan);
    sd.addEventListener('change', segarkan);
    segarkan();
    form.addEventListener('submit', function () {
        var tombol = form.querySelector('button[type=submit]');
        // dikunci sesudah nilai name=input ikut terkirim
        setTimeout(function () { tombol.disabled = true; }, 0);
    });
})();
