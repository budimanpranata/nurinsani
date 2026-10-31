/**
 * Export .xlsx bertahap dengan indikator progres (dipakai tombol Rekap di halaman report).
 * Tombol: <button data-export-xlsx data-export-url="endpoint.php" data-export-satuan="baris">.
 * Modal: #modal-export berisi .progress-bar, [data-export-teks], [data-export-tutup].
 * Alur ke endpoint: mulai -> proses (berulang sampai habis) -> rakit -> unduh.
 * Endpoint proses boleh mengirim "tahap" (mis. nama sheet) untuk ditampilkan di teks progres.
 */
(function () {
    var tombol = document.querySelector('[data-export-xlsx]');
    if (!tombol) { return; }
    var url = tombol.getAttribute('data-export-url');
    var satuan = tombol.getAttribute('data-export-satuan') || 'baris';
    var modalEl = document.getElementById('modal-export');
    var modal = new bootstrap.Modal(modalEl, { backdrop: 'static', keyboard: false });
    var bar = modalEl.querySelector('.progress-bar');
    var teks = modalEl.querySelector('[data-export-teks]');
    var tutup = modalEl.querySelector('[data-export-tutup]');
    var berjalan = false;

    function setel(persen, pesan, gaya) {
        persen = Math.max(0, Math.min(100, Math.round(persen)));
        bar.style.width = persen + '%';
        bar.textContent = persen + '%';
        bar.parentNode.setAttribute('aria-valuenow', persen);
        bar.className = 'progress-bar' + (gaya ? ' ' + gaya : ' progress-bar-striped progress-bar-animated');
        teks.textContent = pesan;
    }

    function angka(n) {
        return Number(n).toLocaleString('id-ID');
    }

    function kirim(data) {
        var body = new URLSearchParams(data);
        return fetch(url, { method: 'POST', body: body, credentials: 'same-origin' }).then(function (r) {
            return r.json().catch(function () { return {}; }).then(function (j) {
                if (!r.ok) { throw new Error(j.pesan || ('Server menjawab ' + r.status)); }
                return j;
            });
        });
    }

    // tabel total per sheet sesudah export selesai (bila endpoint mengirim "ringkasan")
    var wadahRingkasan = modalEl.querySelector('[data-export-ringkasan]');
    function tampilRingkasan(daftar, kolomServer) {
        if (!wadahRingkasan) { return; }
        wadahRingkasan.textContent = '';
        if (!daftar || !daftar.length) { return; }
        var kolom = kolomServer && kolomServer.length ? kolomServer.slice() : [['nama', 'Sheet'], ['rekening', 'Rekening'],
            ['saldo_awal', 'Saldo Awal'], ['mutasi_debet', 'Mutasi Debet'], ['mutasi_kredit', 'Mutasi Kredit'], ['saldo_akhir', 'Saldo Akhir']];
        // kolom pembanding neraca bila endpoint mengirimnya (null = tidak tersedia, mis. periode EOM)
        if (!kolomServer && daftar.some(function (d) { return 'neraca' in d; })) {
            kolom.push(['neraca', 'Neraca (pembanding)'], ['selisih', 'Selisih']);
        }
        var tabel = document.createElement('table');
        tabel.className = 'table table-sm table-bordered table-ni align-middle mb-0 small';
        var kepala = tabel.createTHead().insertRow();
        kolom.forEach(function (k, i) {
            var th = document.createElement('th');
            th.textContent = k[1];
            if (i > 0) { th.className = 'text-end'; }
            kepala.appendChild(th);
        });
        var badan = tabel.createTBody();
        daftar.forEach(function (d) {
            var tr = badan.insertRow();
            kolom.forEach(function (k, i) {
                var td = tr.insertCell();
                var v = d[k[0]];
                td.textContent = i === 0 || typeof v === 'string' ? v : (v === null || v === undefined ? '-' : angka(v));
                if (i === 0 && d.coa) { td.textContent = v + ' (' + d.coa + ')'; }
                if (i > 0) { td.className = 'text-end'; }
                if (k[0] === 'selisih' && v) { td.className += ' text-danger fw-semibold'; }
            });
        });
        var judul = document.createElement('div');
        judul.className = 'fw-semibold small mt-3 mb-1';
        judul.textContent = 'Total nominatif (sama dengan baris TOTAL di file)';
        wadahRingkasan.appendChild(judul);
        var bungkus = document.createElement('div');
        bungkus.className = 'table-responsive';
        bungkus.appendChild(tabel);
        wadahRingkasan.appendChild(bungkus);
    }

    function gagal(e) {
        berjalan = false;
        setel(100, 'Export gagal: ' + e.message, 'bg-danger');
        tutup.classList.remove('d-none');
        tombol.disabled = false;
    }

    tombol.addEventListener('click', function () {
        if (berjalan) { return; }
        berjalan = true;
        tombol.disabled = true;
        tutup.classList.add('d-none');
        tampilRingkasan(null);
        setel(0, 'Menyiapkan data…');
        modal.show();

        kirim({ aksi: 'mulai' }).then(function (m) {
            var job = m.job, total = m.total, jumlah = 0;
            if (m.berikut) { setel(0, m.berikut); }
            function tahap() {
                return kirim({ aksi: 'proses', job: job }).then(function (p) {
                    if (typeof p.persen === 'number') {
                        // endpoint menghitung persen sendiri dan memberi teks langkah berikutnya
                        jumlah = p.jumlah || 0;
                        setel(p.persen, p.berikut || p.teks || '');
                    } else {
                        jumlah = p.selesai_baris;
                        var persen = total > 0 ? p.selesai_baris / total * 95 : 95;
                        setel(persen, 'Memproses ' + angka(p.selesai_baris) + ' dari ' + angka(total) + ' ' + satuan + '…');
                    }
                    return p.habis ? null : tahap();
                });
            }
            return tahap().then(function () {
                setel(97, 'Membuat file Excel…');
                return kirim({ aksi: 'rakit', job: job });
            }).then(function (r) {
                setel(100, 'Selesai: ' + angka(r.jumlah || jumlah || total || 0) + ' ' + satuan + '. File sedang diunduh.', 'bg-success');
                tampilRingkasan(r.ringkasan, r.ringkasan_kolom);
                window.location.href = r.unduh;
                berjalan = false;
                tombol.disabled = false;
                tutup.classList.remove('d-none');
            });
        }).catch(gagal);
    });
})();
