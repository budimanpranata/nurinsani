/* Form Entry Calon Anggota (new2.php). */
(function () {
    'use strict';

    var form = document.getElementById('form-entry');
    if (!form) return;
    var f = form.elements;

    /* ---------- validasi: urutan & pesan sama dengan versi lama ---------- */
    var wajib = [
        ['branch', 'KODE Branch Masih kosong'],
        ['id', 'NOMOR KTP Masih kosong'],
        ['code_kel', 'KODE Kelopok Masih kosong'],
        ['cif', 'CIF Masih kosong'],
        ['nama', 'Nama Masih kosong'],
        ['tempat_lahir', 'TEMPAT LAHIR Masih kosong'],
        ['wali', 'NAMA PENJAMIN masih kosong'],
        ['tgl_lahir', 'TANGGAL LAHIR masih kosong'],
        ['no_tlp', 'NOMOR TLP masih kosong'],
        ['tlp_penjamin', 'NOMOR TLP Penjamin masih kosong'],
        ['alamat', 'ALAMAT masih kosong'],
        ['npwp', 'NPWP masih kosong'],
        ['rt', 'RT/RW masih kosong'],
        ['ibu', 'IBU KANDUNG masih kosong'],
        ['kd_pos', 'KODE POS masih kosong'],
        ['tgl_skep', 'TANGGAL WAKALAH masih kosong'],
        ['tgl_akad', 'TANGGAL AKAD MURABAHAH masih kosong'],
        ['lurah', 'LURAH masih kosong'],
        ['kec', 'KECAMATAN masih kosong'],
        ['kota', 'KOTA masih kosong'],
        ['pekerjaan', 'Pekerjaan masih kosong'],
        ['id_expire', 'id_expire masih kosong'],
        ['pla', 'PEMBIAYAAN masih kosong'],
        ['pendidikan', 'Pendidikan Belum di pilih'],
        ['usaha', 'Usaha Belum di pilih']
    ];
    var pesan = document.getElementById('pesan-validasi');
    var tombol = document.getElementById('tombol-simpan');

    form.addEventListener('submit', function (e) {
        form.querySelectorAll('.is-invalid').forEach(function (el) { el.classList.remove('is-invalid'); });
        for (var i = 0; i < wajib.length; i++) {
            var el = f[wajib[i][0]];
            if (el && el.value.trim() === '') {
                e.preventDefault();
                el.classList.add('is-invalid');
                pesan.textContent = wajib[i][1];
                pesan.classList.remove('d-none');
                el.focus();
                return;
            }
        }
        pesan.classList.add('d-none');
        tombol.disabled = true;
        tombol.querySelector('.spinner-border').classList.remove('d-none');
    });

    /* ---------- kalkulator pembiayaan (sama dengan calc() lama) ---------- */
    // tambahan setoran per tenor & plafond; selain itu setoran = angsuran
    var tambahanSetoran = {
        25: { 1000000: 5800, 1500000: 5200, 2000000: 5600, 2500000: 5000, 3000000: 5400, 3500000: 5800, 4000000: 5200,
              4500000: 5600, 5000000: 5000, 5500000: 5400, 6000000: 5800, 6500000: 5200, 7000000: 5600 },
        35: { 1000000: 5571, 1500000: 5857, 2000000: 5143, 2500000: 5429, 3000000: 5714, 3500000: 5000, 4000000: 5286,
              4500000: 5571, 5000000: 5857, 5500000: 5143, 6000000: 5429, 6500000: 5714, 7000000: 5000 },
        50: { 1000000: 5800, 1500000: 5200, 2000000: 5600, 2500000: 5000, 3000000: 5400, 3500000: 5800, 4000000: 5200,
              4500000: 5400, 5000000: 5000, 5500000: 5400, 6000000: 5200, 6500000: 5200, 7000000: 5600 }
    };
    var marginTenor = { 35: 24, 50: 36 }; // selain itu 18

    function toRp(angka) {
        var s = String(parseInt(angka, 10) || 0).replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        return 'Rp. ' + s + ',00';
    }

    function formatRupiah(nilai) {
        var angka = String(nilai).replace(/\D/g, '');
        return angka ? 'Rp. ' + angka.replace(/\B(?=(\d{3})+(?!\d))/g, '.') : '';
    }

    function hitung() {
        var tenor = parseInt(f.tenor.value, 10);
        var mar = marginTenor[tenor] || 18;
        var pla = parseFloat(f.pla.value) || 0;
        var pokok = pla / tenor;
        var margin = pla * (mar / 100);
        var baghas = margin / tenor;
        var tambah = (tambahanSetoran[tenor] || {})[pla] || 0;

        f.rate.value = mar;
        f.simpanan.value = toRp(pla * 5 / 100);
        f.tot_margin.value = toRp(margin);
        f.angsuran.value = toRp(Math.round(baghas + pokok));
        f.harga_jual.value = toRp(pla + margin);
        f.setoran.value = toRp(Math.round(baghas + pokok + tambah));
        document.getElementById('format-pla').textContent = formatRupiah(f.pla.value);
        document.getElementById('format-num').textContent = formatRupiah(f.num.value);
    }

    ['pla', 'tenor', 'num'].forEach(function (nama) {
        f[nama].addEventListener('input', hitung);
        f[nama].addEventListener('change', hitung);
    });
    hitung();

    /* ---------- dokumen pembiayaan dari server berkas ---------- */
    var urlBerkas = 'http://rmc.nurinsani.co.id:8474/apimobcol/cek_berkas_pembiayaan.php';

    function pesanBerkas(jenis, teks) {
        var kotak = document.getElementById('img' + jenis);
        var em = document.createElement('em');
        em.textContent = teks;
        kotak.textContent = '';
        kotak.appendChild(em);
    }

    function gambarBerkas(jenis, url) {
        var kotak = document.getElementById('img' + jenis);
        var a = document.createElement('a');
        a.href = url;
        a.target = '_blank';
        a.rel = 'noopener';
        var img = document.createElement('img');
        img.src = url;
        img.alt = jenis;
        img.className = 'img-thumbnail';
        img.style.maxWidth = '120px';
        a.appendChild(img);
        kotak.textContent = '';
        kotak.appendChild(a);
    }

    document.getElementById('cari-dokumen').addEventListener('click', function () {
        var nik = f.id.value.trim();
        if (!nik) {
            alert('Isi NIK dulu!');
            f.id.focus();
            return;
        }
        ['ktp', 'kk', 'penjamin', 'usaha'].forEach(function (jenis) {
            pesanBerkas(jenis, 'Memuat...');
            fetch(urlBerkas + '?nik=' + encodeURIComponent(nik) + '&type=' + jenis)
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    if (!data.valid) return pesanBerkas(jenis, 'NIK tidak terdaftar');
                    if (data.url && /^https?:\/\//i.test(data.url)) return gambarBerkas(jenis, data.url);
                    pesanBerkas(jenis, 'Tidak ada file');
                })
                .catch(function () { pesanBerkas(jenis, 'Gagal load'); });
        });
    });
})();
