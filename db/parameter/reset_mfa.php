<?php
/**
 * Reset MFA darurat lewat CLI di server (PEKERJAAN_TERTUNDA.md bagian 101), mis. admin sendiri kehilangan HP
 * (MFA akun sendiri tidak bisa di-reset dari menu Admin > Reset Password).
 *
 *   php db/parameter/reset_mfa.php                         daftar user + status MFA (tidak menulis)
 *   php db/parameter/reset_mfa.php admin "HP hilang"        hapus MFA user admin; login berikutnya memindai QR baru
 *
 * Dicatat di riwayat_reset_password (admin = 'CLI', jenis = 'MFA').
 */
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("Hanya untuk dijalankan lewat CLI.\n");
}
require_once __DIR__ . '/../../koneksi_db.php';
require_once __DIR__ . '/../../inc/mfa.php';

$userid = isset($argv[1]) ? trim($argv[1]) : '';
if ($userid === '') {
    foreach (db_semua("select user.userid, user.level, user_mfa.didaftar, user_mfa.terakhir_masuk, user_mfa.kunci_sampai
        from user left join user_mfa on user_mfa.userid = user.userid order by user.level, user.userid") as $b) {
        printf("%-10s %-9s %s\n", $b['userid'], $b['level'], $b['didaftar'] ? 'MFA aktif sejak ' . $b['didaftar']
            . ($b['kunci_sampai'] ? ' (kunci_sampai ' . $b['kunci_sampai'] . ')' : '') : 'belum daftar');
    }
    exit("\nPakai: php db/parameter/reset_mfa.php <userid> \"alasan\"\n");
}
$alasan = isset($argv[2]) ? trim($argv[2]) : '';
if ($alasan === '') {
    fwrite(STDERR, "Alasan wajib diisi, mis.: php db/parameter/reset_mfa.php $userid \"HP hilang\"\n");
    exit(1);
}
$user = db_satu("select userid, nama from user where userid = ?", 's', $userid);
if (!$user) {
    fwrite(STDERR, "User $userid tidak ditemukan.\n");
    exit(1);
}
if (mfa_hapus($user['userid']) !== 1) {
    echo "User {$user['userid']} belum mendaftarkan MFA, tidak ada yang di-reset.\n";
    exit(0);
}
db_jalankan("insert into riwayat_reset_password (waktu, admin, userid, jenis, alasan, ip) values (now(), 'CLI', ?, 'MFA', ?, '')",
    'ss', $user['userid'], mb_substr($alasan, 0, 255));
echo "MFA {$user['userid']} - {$user['nama']} sudah di-reset. Login berikutnya user memindai QR code baru.\n";
