/**
 * Export Neraca Konsolidasi level kp (kp/laporan/konsolidasi.php) dengan indikator progres.
 * Tombol: <a data-konsol-export="neraca_konsol_export.php" data-pesan="Konfirmasi?">; tanpa JavaScript link biasa tetap jalan.
 * Alur: mulai -> buat (laporan dijalankan di server) sambil memantau status tiap 0,5 detik -> unduh file.
 */
(function () {
    var modalEl = document.getElementById('modal-konsol');
    if (!modalEl) { return; }
    var modal = new bootstrap.Modal(modalEl, { backdrop: 'static', keyboard: false });
    var bar = modalEl.querySelector('.progress-bar');
    var teks = modalEl.querySelector('[data-konsol-teks]');
    var tutup = modalEl.querySelector('[data-konsol-tutup]');
    var berjalan = false;

    function setel(persen, pesan, gaya) {
        persen = Math.max(0, Math.min(100, Math.round(persen)));
        bar.style.width = persen + '%';
        bar.textContent = persen + '%';
        bar.parentNode.setAttribute('aria-valuenow', persen);
        bar.className = 'progress-bar' + (gaya ? ' ' + gaya : ' progress-bar-striped progress-bar-animated');
        teks.textContent = pesan;
    }

    function jsonDari(r) {
        return r.json().catch(function () { return {}; }).then(function (j) {
            if (!r.ok) { throw new Error(j.pesan || ('Server menjawab ' + r.status)); }
            return j;
        });
    }

    function kirim(url, data) {
        return fetch(url, { method: 'POST', body: new URLSearchParams(data), credentials: 'same-origin' }).then(jsonDari);
    }

    function lama(mulai) {
        var d = Math.floor((Date.now() - mulai) / 1000);
        return Math.floor(d / 60) + ':' + ('0' + (d % 60)).slice(-2);
    }

    document.addEventListener('click', function (e) {
        var a = e.target.closest('a[data-konsol-export]');
        if (!a) { return; }
        e.preventDefault();
        if (berjalan || !window.confirm(a.getAttribute('data-pesan') || 'Lanjutkan?')) { return; }
        var url = a.getAttribute('data-konsol-export');
        var mulai = Date.now();
        var pantau = null;
        berjalan = true;
        tutup.classList.add('d-none');
        setel(0, 'Menyiapkan laporan…');
        modal.show();

        kirim(url, { aksi: 'mulai' }).then(function (j) {
            var job = j.job;
            var total = j.total || 1;
            pantau = setInterval(function () {
                fetch(url + '?aksi=status&job=' + encodeURIComponent(job), { credentials: 'same-origin' }).then(jsonDari).then(function (s) {
                    if (!s.selesai && !s.pesan) {
                        // sisakan 1% untuk menyimpan file
                        setel(Math.min(99, s.query / total * 100), 'Menghitung neraca konsolidasi… ' + s.query + ' dari ' + total + ' langkah (' + lama(mulai) + ')');
                    }
                }).catch(function () { /* status sesekali gagal tidak menghentikan proses */ });
            }, 500);
            return kirim(url, { aksi: 'buat', job: job }).then(function () { return job; });
        }).then(function (job) {
            clearInterval(pantau);
            setel(100, 'Selesai (' + lama(mulai) + '). File consol.xls sedang diunduh.', 'bg-success');
            window.location.href = url + '?aksi=unduh&job=' + encodeURIComponent(job);
        }).catch(function (err) {
            clearInterval(pantau);
            setel(100, 'Gagal: ' + err.message, 'bg-danger');
        }).then(function () {
            berjalan = false;
            tutup.classList.remove('d-none');
        });
    });
})();
