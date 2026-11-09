// mfa.php: gambar QR code pendaftaran (di browser, kunci tidak dikirim ke luar) dan cegah kirim dua kali.
(function () {
    var qr = document.getElementById('mfa-qr');
    if (qr && typeof qrcode === 'function') {
        var q = qrcode(0, 'M');
        q.addData(qr.getAttribute('data-uri'));
        q.make();
        qr.innerHTML = q.createSvgTag({ cellSize: 5, margin: 2, scalable: false });
    }

    var form = document.getElementById('form-mfa');
    var kode = document.getElementById('kode');
    var kirim = false;
    kode.addEventListener('input', function () {
        kode.value = kode.value.replace(/[^0-9 ]/g, '');
    });
    form.addEventListener('submit', function (e) {
        if (kirim) {
            e.preventDefault();
            return;
        }
        kirim = true;
        var tombol = document.getElementById('kirim-mfa');
        tombol.querySelector('.spinner-border').hidden = false;
        tombol.classList.add('disabled');
        tombol.setAttribute('aria-disabled', 'true');
    });
    window.addEventListener('pageshow', function () {
        kirim = false;
        var tombol = document.getElementById('kirim-mfa');
        tombol.classList.remove('disabled');
        tombol.removeAttribute('aria-disabled');
        tombol.querySelector('.spinner-border').hidden = true;
    });
})();
