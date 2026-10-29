/* JS bersama aplikasi Nur Insani (tanpa jQuery). */
(function () {
    'use strict';

    /**
     * Autocomplete:
     *   <div class="autocomplete">
     *     <input data-autocomplete="ajax/cari_kelompok.php" data-isi-nilai="code_kel"
     *            data-isi-label="code_kel,nama_kel" data-isi-target='{"#nama_kel":"nama_kel"}'>
     *   </div>
     * Endpoint menerima ?q=... dan mengembalikan array JSON objek.
     */
    function pasangAutocomplete(input) {
        var url = input.getAttribute('data-autocomplete');
        var kolomNilai = input.getAttribute('data-isi-nilai');
        var kolomLabel = (input.getAttribute('data-isi-label') || kolomNilai).split(',');
        var target = JSON.parse(input.getAttribute('data-isi-target') || '{}');
        var menu = document.createElement('div');
        menu.className = 'dropdown-menu';
        input.parentNode.appendChild(menu);
        input.setAttribute('autocomplete', 'off');

        var timer = null;
        var permintaan = 0;
        var aktif = -1;

        function tutup() {
            menu.classList.remove('show');
            aktif = -1;
        }

        function pilih(data) {
            input.value = data[kolomNilai];
            Object.keys(target).forEach(function (selector) {
                var el = document.querySelector(selector);
                if (!el) return;
                var nilai = data[target[selector]];
                el.value = nilai === null || nilai === undefined ? '' : nilai;
                el.dispatchEvent(new Event('change', { bubbles: true }));
            });
            tutup();
        }

        function tampilkan(hasil) {
            menu.innerHTML = '';
            if (!hasil.length) {
                tutup();
                return;
            }
            hasil.forEach(function (data) {
                var item = document.createElement('button');
                item.type = 'button';
                item.className = 'dropdown-item';
                item.textContent = kolomLabel.map(function (k) { return data[k]; }).join(' - ');
                item.addEventListener('mousedown', function (e) {
                    e.preventDefault();
                    pilih(data);
                });
                item._data = data;
                menu.appendChild(item);
            });
            menu.classList.add('show');
        }

        input.addEventListener('input', function () {
            var q = input.value.trim();
            clearTimeout(timer);
            if (!q) {
                tutup();
                return;
            }
            timer = setTimeout(function () {
                var ke = ++permintaan;
                fetch(url + (url.indexOf('?') < 0 ? '?' : '&') + 'q=' + encodeURIComponent(q), { credentials: 'same-origin' })
                    .then(function (r) { return r.ok ? r.json() : []; })
                    .then(function (hasil) { if (ke === permintaan) tampilkan(hasil); })
                    .catch(function () { tutup(); });
            }, 250);
        });

        input.addEventListener('keydown', function (e) {
            var item = menu.querySelectorAll('.dropdown-item');
            if (!menu.classList.contains('show') || !item.length) return;
            if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
                e.preventDefault();
                aktif = (aktif + (e.key === 'ArrowDown' ? 1 : -1) + item.length) % item.length;
                item.forEach(function (el, i) { el.classList.toggle('active', i === aktif); });
            } else if (e.key === 'Enter' && aktif >= 0) {
                e.preventDefault();
                pilih(item[aktif]._data);
            } else if (e.key === 'Escape') {
                tutup();
            }
        });

        input.addEventListener('blur', tutup);
    }

    /** Konfirmasi sebelum membuka link / mengirim form: data-konfirmasi="Pesan?" */
    document.addEventListener('click', function (e) {
        var el = e.target.closest('a[data-konfirmasi], button[data-konfirmasi]');
        if (el && !window.confirm(el.getAttribute('data-konfirmasi'))) {
            e.preventDefault();
        }
    });

    /**
     * Pencarian baris tabel:
     *   <input type="search" data-cari-tabel="#tabel-anggota" data-cari-info="#info-cari">
     * Baris <tbody> yang tidak memuat teks yang diketik disembunyikan.
     */
    function pasangCariTabel(input) {
        var tabel = document.querySelector(input.getAttribute('data-cari-tabel'));
        var info = document.querySelector(input.getAttribute('data-cari-info'));
        if (!tabel) return;
        var baris = Array.prototype.slice.call(tabel.tBodies[0].rows).filter(function (tr) {
            return !tr.hasAttribute('data-baris-kosong');
        });
        var kosong = document.createElement('tr');
        kosong.innerHTML = '<td class="text-center text-muted py-3"></td>';
        kosong.firstChild.colSpan = tabel.tHead.rows[0].cells.length;
        kosong.hidden = true;
        tabel.tBodies[0].appendChild(kosong);

        function saring() {
            var kata = input.value.trim().toLowerCase().split(/\s+/).filter(Boolean);
            var tampil = 0;
            baris.forEach(function (tr) {
                var teks = tr.textContent.toLowerCase();
                var cocok = kata.every(function (k) { return teks.indexOf(k) >= 0; });
                tr.hidden = !cocok;
                if (cocok) tampil++;
            });
            kosong.hidden = tampil > 0 || !baris.length;
            kosong.firstChild.textContent = 'Tidak ada data yang cocok dengan "' + input.value.trim() + '".';
            if (info) info.textContent = kata.length ? tampil + ' dari ' + baris.length + ' data' : baris.length + ' data';
        }

        input.addEventListener('input', saring);
        input.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                input.value = '';
                saring();
            }
        });
        saring();
    }

    /* Isian huruf besar: <input data-huruf-besar>; hanya angka: <input data-hanya-angka> */
    document.addEventListener('input', function (e) {
        var el = e.target;
        if (!el.matches || !el.matches('input')) return;
        var awal = el.value;
        var baru = awal;
        if (el.hasAttribute('data-huruf-besar')) baru = baru.toUpperCase();
        if (el.hasAttribute('data-hanya-angka')) baru = baru.replace(/\D/g, '');
        if (baru !== awal) {
            var pos = el.selectionStart - (awal.length - baru.length);
            el.value = baru;
            try { el.setSelectionRange(pos, pos); } catch (err) { /* tipe input tanpa seleksi */ }
        }
    });

    document.querySelectorAll('[data-autocomplete]').forEach(pasangAutocomplete);
    document.querySelectorAll('[data-cari-tabel]').forEach(pasangCariTabel);
})();
