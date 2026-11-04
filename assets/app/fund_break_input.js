/* Input Break (fund/transaksi/input_break.php): format ribuan titik pada input [data-rupiah] dan total Margin + SIMKA. */
(function () {
    var kotak = Array.prototype.slice.call(document.querySelectorAll('[data-rupiah]'));
    var total = document.getElementById('total_break');
    if (!kotak.length) {
        return;
    }
    function angka(v) {
        return parseInt(String(v).replace(/\D/g, ''), 10) || 0;
    }
    function format(n) {
        return String(n).replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }
    function segarkan() {
        if (total) {
            total.textContent = format(kotak.reduce(function (s, el) { return s + angka(el.value); }, 0));
        }
    }
    kotak.forEach(function (el) {
        el.addEventListener('input', function () {
            var bersih = el.value.replace(/\D/g, '');
            el.value = bersih === '' ? '' : format(parseInt(bersih, 10));
            segarkan();
        });
    });
    segarkan();
})();
