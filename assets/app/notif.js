/* Notifikasi bersama Nur Insani memakai SweetAlert2.
 * Dimuat di <head> SETELAH sweetalert2.min.js supaya:
 *   - window.alert(...) lama (ratusan halaman) otomatis tampil sebagai modal SweetAlert,
 *     tanpa mengubah file-file itu. Harus di <head> agar sudah aktif sebelum <script>alert()</script>
 *     di body dijalankan browser.
 *   - konfirmasi data-konfirmasi="Pesan?" (link/tombol) tampil sebagai dialog Ya/Batal SweetAlert.
 * Warna mengikuti tema aplikasi (hijau --ni-hijau).
 */
(function () {
    'use strict';

    // SweetAlert belum termuat (mis. halaman lama tanpa library): biarkan alert/confirm bawaan.
    if (typeof window.Swal === 'undefined') { return; }

    var HIJAU = '#2f6b4f';
    var MERAH = '#b02a37';

    function escapeHtml(s) {
        return String(s).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }

    /* Tebak jenis pesan dari isinya supaya ikon cocok (tanpa mengubah teks aslinya). */
    function ikonPesan(teks) {
        var t = teks.toLowerCase();
        if (/(gagal|tidak|salah|belum|ditolak|invalid|maaf|wajib|harus|error|kosong)/.test(t)) { return 'error'; }
        if (/(berhasil|tersimpan|sukses|selesai|disimpan|diperbarui|dihapus)/.test(t)) { return 'success'; }
        return 'info';
    }

    /* Ganti alert() bawaan: tampil sebagai modal SweetAlert (newline -> baris baru). */
    window.alert = function (pesan) {
        pesan = (pesan === null || pesan === undefined) ? '' : String(pesan);
        window.Swal.fire({
            html: escapeHtml(pesan).replace(/\r?\n/g, '<br>'),
            icon: ikonPesan(pesan),
            confirmButtonText: 'OK',
            confirmButtonColor: HIJAU,
            heightAuto: false
        });
    };

    /* Konfirmasi data-konfirmasi: tahan aksi, tanya dulu, lanjutkan kalau "Ya".
     * Fase capture supaya mendahului onclick/submit; setelah "Ya" tombol/link diklik ulang
     * (el.click()) sehingga name+value tombol submit tetap terkirim seperti aksi asli. */
    document.addEventListener('click', function (e) {
        var el = e.target.closest ? e.target.closest('[data-konfirmasi]') : null;
        if (!el) { return; }
        if (el.__konfirmasiLolos) { el.__konfirmasiLolos = false; return; }
        e.preventDefault();
        e.stopPropagation();
        var pesan = el.getAttribute('data-konfirmasi') || 'Lanjutkan?';
        window.Swal.fire({
            html: escapeHtml(pesan).replace(/\r?\n/g, '<br>'),
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya',
            cancelButtonText: 'Batal',
            confirmButtonColor: HIJAU,
            cancelButtonColor: MERAH,
            reverseButtons: true,
            heightAuto: false
        }).then(function (hasil) {
            if (hasil.isConfirmed) {
                el.__konfirmasiLolos = true;
                el.click();
            }
        });
    }, true);
})();
