/* Tambah Param Margin (fund/transaksi/input_margin.php): Nisbah Mudarobah hanya untuk Deposito Mudharabah (DP), dan
   pratinjau Kode Bulan dengan rumus yang sama dengan server: awalan (WD/MC/EMC, atau kode nisbah DP12-DP20) + tahun
   + "-bulan" dari periode, mis. DP202026-11. */
(function () {
    var jenis = document.getElementById('jenis');
    var nisbah = document.getElementById('nisbah');
    var kotak = document.getElementById('kotak_nisbah');
    var bulan = document.getElementById('bulan');
    var kode = document.getElementById('kode_bulan');
    var form = document.getElementById('form_margin');

    function segarkan() {
        var dp = jenis.value === 'DP';
        kotak.classList.toggle('d-none', !dp);
        nisbah.required = dp;
        var awalan = dp ? nisbah.value : jenis.value;
        kode.value = awalan && /^\d{4}-\d{2}-\d{2}$/.test(bulan.value) ? awalan + bulan.value.substring(0, 4) + bulan.value.substring(4, 7) : '';
    }
    [jenis, nisbah, bulan].forEach(function (el) {
        el.addEventListener('change', segarkan);
    });
    segarkan();
    form.addEventListener('submit', function () {
        var tombol = form.querySelector('button[type=submit]');
        // dikunci sesudah nilai name=input ikut terkirim
        setTimeout(function () { tombol.disabled = true; }, 0);
    });
})();
