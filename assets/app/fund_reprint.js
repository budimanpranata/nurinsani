/* Reprint bilyet/advis level fund (fund/transaksi/input_reprint.php): tombol Simpan dikunci sesudah dikirim (unggah
   dokumen bisa lama; cegah catatan ganda), dan sesudah tombol Cetak (PDF di tab baru) halaman dimuat ulang saat user
   kembali ke tab ini, karena catatan reprint yang dipakai berubah menjadi DICETAK. */
(function () {
    var form = document.querySelector('form[enctype="multipart/form-data"]');
    var tombol = document.getElementById('tombol_simpan');
    if (form && tombol) {
        form.addEventListener('submit', function () {
            // tombol dikunci sesudah nilai name=simpan ikut terkirim
            setTimeout(function () {
                tombol.disabled = true;
                tombol.textContent = 'Menyimpan…';
            }, 0);
        });
    }
    var sudahCetak = false;
    document.addEventListener('click', function (e) {
        if (e.target.closest('[data-cetak]')) {
            sudahCetak = true;
        }
    });
    document.addEventListener('visibilitychange', function () {
        if (sudahCetak && document.visibilityState === 'visible') {
            window.location.href = window.location.href;
        }
    });
})();
