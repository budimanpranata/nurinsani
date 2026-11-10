<?php
/**
 * Notifikasi SweetAlert untuk halaman PROSES/REDIRECT (yang tidak memakai halaman_awal(),
 * mis. log.php, Logout.php, inputlog.php, dan halaman form-proses lama). Halaman-halaman ini
 * hanya meng-echo "<script>alert()</script>" sehingga alert()-nya masih bawaan browser karena
 * bundle SweetAlert belum dimuat.
 *
 *   require_once __DIR__ . '/inc/notif.php';
 *   notif_pergi('Password salah', 'index.html', 'error'); exit;   // popup lalu pindah halaman
 *   notif_pesan('Data tersimpan', 'success');                     // popup saja, tetap di halaman
 *
 * Keduanya meng-echo satu blok <script> mandiri yang:
 *   - memuat bundle SweetAlert sendiri (lazy-load) bila belum ada, lalu tampilkan popup tema hijau;
 *   - FALLBACK ke alert() bawaan bila bundle gagal dimuat.
 * Karena hanya <script> (tanpa <!DOCTYPE>/<head>), AMAN dipasang di mana saja: awal, tengah, atau
 * akhir output halaman. notif_pergi pindah ke $url SETELAH popup ditutup (meniru alert() yang
 * menahan redirect); notif_pesan tidak pindah halaman.
 *
 * $ikon: 'success'|'error'|'warning'|'info'|'question'. Kosong/null = ditebak dari teks
 * (sama dengan assets/app/notif.js) supaya konsisten dengan notif lain.
 */
require_once __DIR__ . '/tampilan.php'; // url_app()

/** Cetak blok <script> notifikasi SweetAlert; $url null = tanpa pindah halaman.
 *  $js_tutup = potongan JS (dikendalikan developer, BUKAN input user) yang dijalankan saat popup ditutup
 *  sebagai ganti redirect (mis. "top.location.href='...'"). */
function notif_cetak($pesan, $url, $ikon, $js_tutup = null)
{
    $pesan = (string) $pesan;
    $flag = JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP;
    $valid = array('success', 'error', 'warning', 'info', 'question');
    $ikon = in_array($ikon, $valid, true) ? $ikon : '';
    $css = function_exists('url_app') ? url_app('assets/vendor/sweetalert2-11/sweetalert2.min.css') : 'assets/vendor/sweetalert2-11/sweetalert2.min.css';
    $js = function_exists('url_app') ? url_app('assets/vendor/sweetalert2-11/sweetalert2.min.js') : 'assets/vendor/sweetalert2-11/sweetalert2.min.js';
    ?>
<script>
(function () {
    var pesan = <?php echo json_encode($pesan, $flag); ?>;
    var url = <?php echo json_encode($url, $flag); ?>;
    var ikon = <?php echo json_encode($ikon, $flag); ?>;
    var cssHref = <?php echo json_encode($css, $flag); ?>;
    var jsSrc = <?php echo json_encode($js, $flag); ?>;
    var jsTutup = <?php echo json_encode($js_tutup === null ? '' : (string) $js_tutup, $flag); ?>;
    function pergi() {
        if (jsTutup) { try { (new Function(jsTutup))(); } catch (e) {} return; }
        if (!url) { return; }
        if (url === 'HISTORY_BACK') { history.back(); return; }
        window.location.href = url;
    }
    function tebak() {
        var t = pesan.toLowerCase();
        if (/(gagal|tidak|salah|belum|ditolak|invalid|maaf|wajib|harus|error|kosong)/.test(t)) { return 'error'; }
        if (/(berhasil|tersimpan|sukses|selesai|disimpan|diperbarui|dihapus|keluar)/.test(t)) { return 'success'; }
        return 'info';
    }
    function tampil() {
        var ic = ikon || tebak();
        if (window.Swal) {
            window.Swal.fire({
                html: pesan.replace(/[&<>"']/g, function (c) {
                    return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
                }).replace(/\r?\n/g, '<br>'),
                icon: ic,
                confirmButtonText: 'OK',
                confirmButtonColor: '#2f6b4f',
                allowOutsideClick: false,
                allowEscapeKey: false,
                heightAuto: false
            }).then(pergi);
        } else {
            alert(pesan);
            pergi();
        }
    }
    if (window.Swal) { tampil(); return; }
    try {
        var kepala = document.head || document.documentElement;
        if (!document.querySelector('link[data-notif-swal]')) {
            var l = document.createElement('link');
            l.rel = 'stylesheet';
            l.href = cssHref;
            l.setAttribute('data-notif-swal', '1');
            kepala.appendChild(l);
        }
        var s = document.createElement('script');
        s.src = jsSrc;
        s.onload = tampil;
        s.onerror = function () { alert(pesan); pergi(); };
        kepala.appendChild(s);
    } catch (e) {
        alert(pesan);
        pergi();
    }
})();
</script>
<?php
}

/** Tampilkan popup SweetAlert lalu pindah ke $url setelah ditutup (biasanya diikuti exit). */
function notif_pergi($pesan, $url, $ikon = null)
{
    notif_cetak($pesan, (string) $url, $ikon);
}

/** Tampilkan popup SweetAlert saja (tetap di halaman, tanpa pindah). */
function notif_pesan($pesan, $ikon = null)
{
    notif_cetak($pesan, null, $ikon);
}

/** Tampilkan popup SweetAlert lalu jalankan potongan JS $js_tutup saat ditutup (developer-controlled,
 *  mis. "top.location.href='index.html'"). Untuk redirect non-standar (top.location, dsb). */
function notif_pergi_js($pesan, $js_tutup, $ikon = null)
{
    notif_cetak($pesan, null, $ikon, $js_tutup);
}
