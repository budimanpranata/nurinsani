/* Form pengajuan pembiayaan (pengajuan_ex.php). */
(function () {
    'use strict';

    var modal = document.getElementById('form-pengajuan');
    var form = document.getElementById('form-data');
    if (!modal || !form) return;

    var pesan = document.getElementById('pesan-error');
    var tombol = document.getElementById('tombol-simpan');
    var spinner = tombol.querySelector('.spinner-border');
    var harga = form.querySelectorAll('.harga-barang');
    var total = document.getElementById('total-barang');
    var progress = document.getElementById('progress-simpan');
    var bar = progress.querySelector('.progress-bar');
    var progressTeks = document.getElementById('progress-teks');
    var progressPersen = document.getElementById('progress-persen');
    var jalanPelan = null;

    /** Atur progress bar; persen null = sembunyikan. */
    function setProgress(persen, teks) {
        clearInterval(jalanPelan);
        if (persen === null) {
            progress.classList.add('d-none');
            return;
        }
        persen = Math.round(persen);
        progress.classList.remove('d-none');
        bar.style.width = persen + '%';
        bar.parentNode.setAttribute('aria-valuenow', persen);
        progressPersen.textContent = persen + '%';
        if (teks) progressTeks.textContent = teks;
        bar.classList.toggle('bg-info', persen < 100);
        bar.classList.toggle('bg-success', persen >= 100);
    }

    /** Selama menunggu jawaban server, naikkan bar perlahan sampai 90%. */
    function tungguServer(dari) {
        var persen = dari;
        setProgress(persen, 'Memproses pengajuan...');
        jalanPelan = setInterval(function () {
            persen += (90 - persen) * 0.08;
            bar.style.width = persen + '%';
            progressPersen.textContent = Math.round(persen) + '%';
        }, 200);
    }

    function tampilkanError(teks) {
        pesan.textContent = '';
        teks.split('\n').forEach(function (baris, i) {
            if (i) pesan.appendChild(document.createElement('br'));
            pesan.appendChild(document.createTextNode(baris));
        });
        pesan.classList.remove('d-none');
        pesan.scrollIntoView({ block: 'nearest' });
    }

    function sibuk(ya) {
        tombol.disabled = ya;
        spinner.classList.toggle('d-none', !ya);
    }

    // isi form dari tombol "Ajukan" yang diklik
    modal.addEventListener('show.bs.modal', function (e) {
        var data = e.relatedTarget.dataset;
        form.reset();
        ['id', 'cif', 'nama', 'kds', 'pla'].forEach(function (k) {
            form.elements[k].value = data[k] || '';
        });
        total.textContent = '0';
        pesan.classList.add('d-none');
        setProgress(null);
        sibuk(false);
    });

    // total harga barang -> Plafond
    harga.forEach(function (el) {
        el.addEventListener('input', function () {
            var jumlah = 0;
            harga.forEach(function (h) { jumlah += parseInt(h.value, 10) || 0; });
            total.textContent = jumlah.toLocaleString('id-ID');
            if (jumlah > 0) form.elements.pla.value = jumlah;
        });
    });

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        pesan.classList.add('d-none');
        sibuk(true);
        setProgress(0, 'Mengirim data...');

        var xhr = new XMLHttpRequest();
        xhr.open('POST', form.action);
        // tahap kirim data = 0-50%
        xhr.upload.addEventListener('progress', function (ev) {
            if (ev.lengthComputable) setProgress(ev.loaded / ev.total * 50, 'Mengirim data...');
        });
        xhr.upload.addEventListener('load', function () { tungguServer(50); });
        xhr.addEventListener('load', function () {
            var teks = xhr.responseText.trim();
            if (xhr.status === 200 && teks === 'Sukses') {
                setProgress(100, 'Pengajuan berhasil disimpan');
                setTimeout(function () { location.reload(); }, 2000);
            } else {
                setProgress(null);
                tampilkanError(teks || 'Gagal memproses data (HTTP ' + xhr.status + ').');
                sibuk(false);
            }
        });
        xhr.addEventListener('error', function () {
            setProgress(null);
            tampilkanError('Gagal memproses data: koneksi ke server terputus.');
            sibuk(false);
        });
        xhr.send(new FormData(form));
    });
})();
