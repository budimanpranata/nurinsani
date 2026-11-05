<?php
/**
 * Konversi password login md5 polos -> bcrypt dari md5 (inc/password.php), PEKERJAAN_TERTUNDA.md bagian 93.
 * Password user TIDAK berubah (tetap login dengan password yang sama); hanya cara simpannya. Sekali jalan, aman diulang
 * (baris yang sudah bcrypt dilewati).
 *
 *   php db/parameter/konversi_password.php                              mode UJI: hanya menghitung, tidak menulis
 *   php db/parameter/konversi_password.php --jalankan --sudah-backup    konversi
 *   php db/parameter/konversi_password.php --kembalikan --sudah-backup  kembali ke md5 dari cadangan user_pass_md5
 *   tambahan: --db=nama_database (bawaan: konfigurasi nur_insani di koneksi_db.php / koneksi_db.lokal.php)
 *
 * Urutan di server: (1) backup tabel user, (2) db/parameter/2026-11-05_password_hash.sql, (3) pasang kode baru,
 * (4) --jalankan, (5) coba login. Kode baru tetap menerima md5 polos, jadi user tidak gagal login di antara (3) dan (4).
 * --kembalikan hanya untuk keadaan darurat SESUDAH kode lama dipasang kembali; user yang sudah mengganti/di-reset
 * passwordnya sesudah konversi dilewati (passwordnya tidak bisa dikembalikan ke md5).
 * Pengaman: hanya CLI; hash md5 dicadangkan ke user_pass_md5 sebelum ditimpa; tiap hash baru diverifikasi sebelum
 * ditulis; UPDATE memakai nilai lama di WHERE; last_update = last_update supaya hitungan kedaluwarsa 30 hari tidak
 * ter-reset (kolom itu ON UPDATE CURRENT_TIMESTAMP).
 */
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("Hanya untuk dijalankan lewat CLI.\n");
}
require __DIR__ . '/../../koneksi_db.php';

$opsi = array('jalankan' => false, 'kembalikan' => false, 'sudah-backup' => false, 'db' => null);
foreach (array_slice($argv, 1) as $a) {
    if ($a === '--jalankan' || $a === '--kembalikan' || $a === '--sudah-backup') {
        $opsi[substr($a, 2)] = true;
    } elseif (strncmp($a, '--db=', 5) === 0) {
        $opsi['db'] = substr($a, 5);
    } else {
        exit("Opsi tidak dikenal: $a\n");
    }
}
if ($opsi['db'] !== null) {
    $GLOBALS['DB_CONFIG']['nur_insani']['name'] = $opsi['db'];
}
if ($opsi['jalankan'] && $opsi['kembalikan']) {
    exit("Pilih salah satu: --jalankan atau --kembalikan.\n");
}
if (($opsi['jalankan'] || $opsi['kembalikan']) && !$opsi['sudah-backup']) {
    exit("Wajib disertai --sudah-backup (pastikan tabel user sudah dibackup).\n");
}
require __DIR__ . '/../../inc/password.php';

$db = db_ni();
echo 'Database: ' . $GLOBALS['DB_CONFIG']['nur_insani']['name'] . "\n";
$kolom = db_satu("select character_maximum_length as n from information_schema.columns
    where table_schema = database() and table_name = 'user' and column_name = 'pass'");
if (!$kolom || (int) $kolom['n'] < 60) {
    exit("Kolom user.pass belum diperbesar: jalankan dulu db/parameter/2026-11-05_password_hash.sql\n");
}
if (!db_satu("select 1 as ada from information_schema.tables where table_schema = database() and table_name = 'user_pass_md5'")) {
    exit("Tabel user_pass_md5 belum ada: jalankan dulu db/parameter/2026-11-05_password_hash.sql\n");
}

/** UPDATE user.pass dengan pengaman nilai lama; true bila tepat satu baris berubah. */
function ganti_pass($db, $userid, $lama, $baru)
{
    $st = $db->prepare("update user set pass = ?, last_update = last_update where userid = ? and pass = ?");
    $st->bind_param('sss', $baru, $userid, $lama);
    return $st->execute() && $st->affected_rows === 1;
}

$total = db_satu("select count(*) as n from user");
$md5 = array();
$bcrypt = 0;
$lain = array();
foreach (db_semua("select userid, pass from user order by userid") as $r) {
    if (preg_match('/^[0-9a-f]{32}$/i', $r['pass'])) {
        $md5[] = $r;
    } elseif (strncmp($r['pass'], '$2y$', 4) === 0) {
        $bcrypt++;
    } else {
        $lain[] = $r['userid'];
    }
}
echo "User: {$total['n']} | md5 polos: " . count($md5) . " | sudah bcrypt: $bcrypt | format lain (tidak disentuh): " . count($lain)
    . ($lain ? ' (' . implode(', ', $lain) . ')' : '') . "\n";

if ($opsi['kembalikan']) {
    $ok = 0;
    $lewat = array();
    foreach (db_semua("select u.userid, u.pass as sekarang, b.pass as md5 from user u join user_pass_md5 b on b.userid = u.userid
        where u.pass like '\$2y\$%'") as $r) {
        if (!password_cocok_md5($r['md5'], $r['sekarang'])) {
            $lewat[] = $r['userid'];
            continue;
        }
        if (ganti_pass($db, $r['userid'], $r['sekarang'], strtolower($r['md5']))) {
            $ok++;
        } else {
            $lewat[] = $r['userid'];
        }
    }
    echo "Dikembalikan ke md5: $ok | dilewati (password sudah diganti/di-reset sesudah konversi): " . count($lewat)
        . ($lewat ? ' (' . implode(', ', $lewat) . ')' : '') . "\n";
    exit;
}

if (!$opsi['jalankan']) {
    echo "Mode UJI: tidak ada yang ditulis. Tambahkan --jalankan --sudah-backup untuk konversi.\n";
    exit;
}

$cadang = $db->prepare("insert into user_pass_md5 (userid, pass, waktu) values (?, ?, now())
    on duplicate key update pass = values(pass), waktu = values(waktu)");
$ok = 0;
$gagal = array();
foreach ($md5 as $r) {
    $lama = strtolower($r['pass']);
    $baru = password_buat_dari_md5($lama);
    if (!password_cocok_md5($lama, $baru)) {
        $gagal[] = $r['userid'] . ' (verifikasi)';
        continue;
    }
    $cadang->bind_param('ss', $r['userid'], $r['pass']);
    if (!$cadang->execute()) {
        $gagal[] = $r['userid'] . ' (cadangan: ' . $cadang->error . ')';
        continue;
    }
    if (ganti_pass($db, $r['userid'], $r['pass'], $baru)) {
        $ok++;
    } else {
        $gagal[] = $r['userid'] . ' (update)';
    }
}
$sisa = db_satu("select count(*) as n from user where pass not like '\$2y\$%'");
echo "Dikonversi: $ok | gagal: " . count($gagal) . ($gagal ? ' (' . implode(', ', $gagal) . ')' : '')
    . " | belum bcrypt sekarang: {$sisa['n']}\n";
