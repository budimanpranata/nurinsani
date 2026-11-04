/* fund/view/view_jatuh.php: pilih semua deposito, jumlah terpilih, tombol Realisasi aktif bila ada yang dipilih. */
(function () {
    var semua = document.getElementById('pilih_semua');
    var tombol = document.getElementById('tombol_realisasi');
    var info = document.getElementById('jumlah_pilih');
    if (!tombol) {
        return;
    }
    var kotak = function () {
        return Array.prototype.slice.call(document.querySelectorAll('.pilih-deposito'));
    };
    var segarkan = function () {
        var n = kotak().filter(function (c) { return c.checked; }).length;
        info.textContent = n + ' dipilih';
        tombol.disabled = n === 0;
        semua.checked = n > 0 && n === kotak().length;
        semua.indeterminate = n > 0 && n < kotak().length;
    };
    semua.addEventListener('change', function () {
        kotak().forEach(function (c) { c.checked = semua.checked; });
        segarkan();
    });
    kotak().forEach(function (c) { c.addEventListener('change', segarkan); });
    segarkan();
})();
