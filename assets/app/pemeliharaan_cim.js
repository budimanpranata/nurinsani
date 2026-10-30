/* Form Pemeliharaan CIM (dim.php): validasi, kalkulator, format rupiah, dan cari dokumen — sama dengan versi lama. */
(function () {
    'use strict';

    var form = document.getElementById('form-cim');
    if (!form) return;
    var f = form.elements;

    /* ---------- validasi: urutan & pesan alert sama dengan validateForm() lama ---------- */
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
        ['tgl_skep', 'TANGGA WAKALAH masih kosong'],
        ['lurah', 'TANGGA WAKALAH masih kosong'], // teks versi lama
        ['kec', 'KECAMATAN masih kosong'],
        ['kota', 'KOTA masih kosong'],
        ['pekerjaan', 'Pekerjaan masih kosong'],
        ['id_expire', ' id_expire masih kosong'],
        ['pla', ' PEMBIAYAAN masih kosong'],
        ['pendidikan', 'Pendidikan Belum di pilih', 'pilih'],
        ['usaha', 'Usaha Belum di pilih', 'pilih'],
        ['nama_usaha', ' NAMA USAHA masih kosong', '', 'pla'], // versi lama memindahkan fokus ke Harga Beli
        ['suffix', ' PEMBIYAAN KE masih kosong']
    ];

    form.addEventListener('submit', function (e) {
        for (var i = 0; i < wajib.length; i++) {
            var el = f[wajib[i][0]];
            if (!el) continue;
            // select: versi lama memeriksa selectedIndex == "" (pilihan pertama "Pilih Salah satu")
            var kosong = wajib[i][2] === 'pilih' ? el.selectedIndex === 0 : el.value === '';
            if (kosong) {
                e.preventDefault();
                alert(wajib[i][1]);
                (f[wajib[i][3]] || el).focus();
                return;
            }
        }
    });

    /* ---------- kalkulator: sama dengan perhitungan server di update_log.php (param_biaya) ---------- */
    var dataKalkulator = JSON.parse(document.getElementById('data-kalkulator').textContent);
    var infoParameter = document.getElementById('info-parameter');

    function hitung() {
        var pla = Number(f.pla.value);
        var tenor = Number(f.tenor.value);
        // simpanan khusus 5% tetap seperti sebelumnya
        f.simpanan.value = isFinite(pla) ? Math.round(pla * 5 / 100) : 0;

        var parameter = dataKalkulator.biaya[tenor + '|' + pla];
        if (!f.pla.value || !f.tenor.value || !parameter) {
            f.angsuran.value = '';
            f.tot_margin.value = '';
            f.rate.value = '';
            infoParameter.className = 'small text-end mb-2 text-danger';
            infoParameter.textContent = (f.pla.value && f.tenor.value) ? 'Parameter Pembiayaan tidak ditemukan' : '';
            return;
        }
        var persenMargin = parameter[0];
        var akad = dataKalkulator.akad[f.branch.value];
        var pokok = pla / tenor;
        var marginPerMinggu = (pla * persenMargin / 100) / tenor;
        var angsuran, totalMargin;
        if (akad === 'MURAB') {
            angsuran = pokok + marginPerMinggu;
            totalMargin = pla * persenMargin / 100 + 2500 * tenor;
        } else {
            // akad selain MURAB (musyarakah): angsuran + 2.500, margin 0
            angsuran = pokok + marginPerMinggu + 2500;
            totalMargin = 0;
        }
        f.angsuran.value = Math.round(angsuran);
        f.tot_margin.value = Math.round(totalMargin);
        f.rate.value = persenMargin;
        infoParameter.className = 'small text-end mb-2 text-body-secondary';
        infoParameter.textContent = 'Akad ' + (akad || '-') + ' · margin ' + persenMargin + '% · pokok ' +
            Math.round(pokok).toLocaleString('de-DE') + ' + margin ' + Math.round(marginPerMinggu).toLocaleString('de-DE') + ' per minggu';
    }
    // dihitung ulang saat plafond/tenor/unit berubah, termasuk saat diisi otomatis dari pencarian anggota (event change)
    form.querySelectorAll('[data-hitung]').forEach(function (el) {
        el.addEventListener('focus', hitung);
        el.addEventListener('input', hitung);
        el.addEventListener('change', hitung);
        el.addEventListener('blur', hitung);
    });
    f.branch.addEventListener('input', hitung);
    f.branch.addEventListener('change', hitung);

    /* ---------- format rupiah di bawah Rekomendasi / Harga Beli (formatCurrency lama) ---------- */
    var format = document.getElementById('format');
    function rupiah(nilai) {
        var angka = String(nilai).replace(/[^0-9]/g, '');
        return 'Rp. ' + (angka === '' ? '0' : Number(angka).toLocaleString('de-DE'));
    }
    form.querySelectorAll('[data-format-rupiah]').forEach(function (el) {
        el.addEventListener('keyup', function () { format.textContent = rupiah(el.value); });
    });

    /* ---------- cari dokumen berkas (API yang sama dengan versi lama) ---------- */
    document.getElementById('cari-dokumen').addEventListener('click', function () {
        var nik = f.id.value.trim();
        if (nik === '') {
            alert('Isi NIK dulu!');
            return;
        }
        ['ktp', 'kk', 'penjamin', 'usaha'].forEach(function (type) {
            var wadah = document.getElementById('img' + type);
            fetch('http://rmc.nurinsani.co.id:8474/apimobcol/cek_berkas.php?nik=' + encodeURIComponent(nik) + '&type=' + type)
                .then(function (res) { return res.json(); })
                .then(function (data) {
                    wadah.textContent = '';
                    if (data.valid) {
                        if (data.url) {
                            var a = document.createElement('a');
                            a.href = data.url;
                            a.target = '_blank';
                            var img = document.createElement('img');
                            img.src = data.url;
                            img.width = 120;
                            img.className = 'img-fluid border rounded';
                            a.appendChild(img);
                            wadah.appendChild(a);
                        } else {
                            wadah.innerHTML = '<em>Tidak ada file</em>';
                        }
                    } else {
                        wadah.innerHTML = '<em>NIK tidak terdaftar</em>';
                    }
                })
                .catch(function (err) {
                    wadah.innerHTML = '<em>Gagal load</em>';
                    console.error(err);
                });
        });
    });
})();
