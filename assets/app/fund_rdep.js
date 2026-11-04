/* Realisasi Jatuh Tempo (fund/view/r_dep.php): tampilkan field sesuai Jenis Realisasi / Jenis ARO (data-untuk), dan
   jadikan pilihan Jenis Break / Jenis ARO wajib bila jenisnya dipilih. */
(function () {
    var jenis = document.getElementById('jenis');
    var aro = document.getElementById('jenis_aro');
    var brk = document.getElementById('jenis_break');
    if (!jenis) {
        return;
    }
    function segarkan() {
        var j = jenis.value;
        var a = j === 'ARO-ARO' ? aro.value : '';
        document.querySelectorAll('[data-untuk]').forEach(function (el) {
            var u = el.getAttribute('data-untuk');
            el.classList.toggle('d-none', u !== j && u !== a);
        });
        brk.required = j === 'BREAK';
        aro.required = j === 'ARO-ARO';
    }
    jenis.addEventListener('change', segarkan);
    aro.addEventListener('change', segarkan);
    segarkan();
})();
