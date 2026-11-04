<?php
/**
 * Enkripsi NIK & alamat yang SUDAH ADA (tahap 1, PEKERJAAN_TERTUNDA.md bagian 87): tabel deposan + baris anggota
 * pasangan deposan (no dan CIF sama). Sekali jalan, aman diulang (kolom yang sudah 'enc:v1:' dilewati).
 *
 *   php db/enkripsi/migrasi_data.php                        mode UJI: hanya menghitung, tidak menulis apa pun
 *   php db/enkripsi/migrasi_data.php --jalankan --sudah-backup
 *   tambahan: --db=nama_database (bawaan: konfigurasi nur_insani di koneksi_db.php / koneksi_db.lokal.php)
 *
 * Syarat sebelum --jalankan di server: (1) backup database, (2) db/enkripsi/2026-11-04_enkripsi_nik_alamat.sql sudah
 * dijalankan, (3) kode baru terpasang, (4) kunci 'enkripsi' prod ada di koneksi_db.lokal.php DAN cadangannya tersimpan
 * terpisah. Tanpa kunci yang sama, data yang sudah dienkripsi tidak dapat dibuka lagi.
 * Pengaman: hanya CLI; uji bolak-balik kunci sebelum mulai; tiap nilai didekripsi ulang dan dicocokkan sebelum ditulis;
 * UPDATE memakai nilai lama di WHERE agar tidak menimpa data yang sedang diedit; anggota dalam transaksi per 200 baris.
 */
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("Hanya untuk dijalankan lewat CLI.\n");
}
require __DIR__ . '/../../koneksi_db.php';

$opsi = array('jalankan' => false, 'sudah-backup' => false, 'db' => null);
foreach (array_slice($argv, 1) as $a) {
    if ($a === '--jalankan') {
        $opsi['jalankan'] = true;
    } elseif ($a === '--sudah-backup') {
        $opsi['sudah-backup'] = true;
    } elseif (strncmp($a, '--db=', 5) === 0) {
        $opsi['db'] = substr($a, 5);
    } else {
        exit("Opsi tidak dikenal: $a\n");
    }
}
if ($opsi['db'] !== null) {
    $GLOBALS['DB_CONFIG']['nur_insani']['name'] = $opsi['db'];
}
if ($opsi['jalankan'] && !$opsi['sudah-backup']) {
    exit("--jalankan wajib disertai --sudah-backup (pastikan database sudah dibackup).\n");
}
require __DIR__ . '/../../inc/enkripsi.php';

$tulis = $opsi['jalankan'];
$nama_db = $GLOBALS['DB_CONFIG']['nur_insani']['name'];
echo ($tulis ? "MODE JALANKAN" : "MODE UJI (tidak menulis)") . " - database: $nama_db\n";

// kunci & skema
try {
    $uji = random_bytes(8) . 'NIK 3201';
    if (dekripsi_data(enkripsi_data($uji)) !== $uji || strlen(indeks_nik('3201')) !== 64) {
        exit("Uji bolak-balik kunci gagal.\n");
    }
} catch (Throwable $e) {
    exit($e->getMessage() . "\n");
}
foreach (array('deposan', 'anggota') as $t) {
    $k = db_satu("select column_type from information_schema.columns where table_schema=database() and table_name=? and column_name='ID_NUMBER_IDX'", 's', $t);
    $p = db_satu("select character_maximum_length as n from information_schema.columns where table_schema=database() and table_name=? and column_name='ID_NUMBER'", 's', $t);
    if (!$k || !$p || (int) $p['n'] < 255) {
        exit("Skema $t belum diubah: jalankan dulu db/enkripsi/2026-11-04_enkripsi_nik_alamat.sql\n");
    }
}

/**
 * Siapkan nilai baru satu baris: kolom teks dienkripsi (yang sudah terenkripsi/kosong dilewati), IDX dari NIK asli.
 * Hasil array(kolom => nilai baru) hanya untuk kolom yang berubah; berhenti bila verifikasi gagal.
 */
function siapkan_baris($row, $kolom_teks, $kolom_nik)
{
    $baru = array();
    foreach ($kolom_teks as $k) {
        $v = $row[$k];
        if ($v === null || $v === '' || data_terenkripsi($v)) {
            continue;
        }
        $e = enkripsi_data($v);
        if (dekripsi_data($e) !== (string) $v || strlen($e) > 255) {
            throw new RuntimeException("Verifikasi gagal ($k, panjang " . strlen($e) . ")");
        }
        $baru[$k] = $e;
    }
    $idx = indeks_nik($row[$kolom_nik]);
    $idx = $idx === '' ? null : $idx;
    if ($row['ID_NUMBER_IDX'] !== $idx) {
        $baru['ID_NUMBER_IDX'] = $idx;
    }
    return $baru;
}

/** UPDATE satu baris dengan nilai lama di WHERE (tidak menimpa perubahan dari luar); hasil jumlah baris berubah. */
function perbarui($tabel, $kunci, $row, $baru)
{
    $set = array();
    $where = array();
    $param = array();
    foreach ($baru as $k => $v) {
        $set[] = "`$k`=?";
        $param[] = $v;
    }
    foreach ($kunci as $k) {
        $where[] = "`$k`=?";
        $param[] = (string) $row[$k];
    }
    foreach (array_keys($baru) as $k) {
        if ($row[$k] === null) {
            $where[] = "`$k` IS NULL";
        } else {
            $where[] = "`$k`=BINARY ?";
            $param[] = (string) $row[$k];
        }
    }
    $db = db_ni();
    $st = $db->prepare("UPDATE `$tabel` SET " . implode(',', $set) . " WHERE " . implode(' AND ', $where));
    $tipe = str_repeat('s', count($param));
    if (!$st || !$st->bind_param($tipe, ...$param) || !$st->execute()) {
        throw new RuntimeException("UPDATE $tabel gagal: " . ($st ? $st->error : $db->error));
    }
    return $st->affected_rows;
}

$ringkas = array();
$proses = array(
    'deposan' => array(
        'sql' => "select no, CIF, ID_NUMBER, ID_NUMBER_IDX, KTP_SATU, KTP_DUA, HADDRESS1, HADDRESS2, HADDRESS3 from deposan order by no",
        'teks' => array('ID_NUMBER', 'KTP_SATU', 'KTP_DUA', 'HADDRESS1', 'HADDRESS2', 'HADDRESS3'),
        'kunci' => array('no'),
    ),
    'anggota' => array(
        // hanya pasangan deposan: no dan CIF sama dengan deposan (dibuat oleh + Data Perorangan)
        'sql' => "select a.no, a.CIF, a.ID_NUMBER, a.ID_NUMBER_IDX, a.HADDRESS1 from anggota a join deposan d on d.no = a.no and d.CIF = a.CIF order by a.CIF",
        'teks' => array('ID_NUMBER', 'HADDRESS1'),
        'kunci' => array('CIF', 'no'),
    ),
);
try {
    foreach ($proses as $tabel => $cfg) {
        $baris = db_semua($cfg['sql']);
        $r = array('baris' => count($baris), 'perlu' => 0, 'kolom' => 0, 'ditulis' => 0, 'lewat_berubah' => 0);
        $db = db_ni();
        $dalam_transaksi = false;
        foreach ($baris as $i => $row) {
            $baru = siapkan_baris($row, $cfg['teks'], 'ID_NUMBER');
            if (!$baru) {
                continue;
            }
            $r['perlu']++;
            $r['kolom'] += count($baru);
            if (!$tulis) {
                continue;
            }
            if ($tabel === 'anggota' && !$dalam_transaksi) {
                $db->begin_transaction();
                $dalam_transaksi = true;
            }
            $n = perbarui($tabel, $cfg['kunci'], $row, $baru);
            $r[$n === 1 ? 'ditulis' : 'lewat_berubah']++;
            if ($dalam_transaksi && $r['ditulis'] % 200 === 0) {
                $db->commit();
                $dalam_transaksi = false;
            }
        }
        if ($dalam_transaksi) {
            $db->commit();
        }
        $ringkas[$tabel] = $r;
    }
} catch (Throwable $e) {
    if (isset($dalam_transaksi) && $dalam_transaksi) {
        db_ni()->rollback();
    }
    exit("BERHENTI: " . $e->getMessage() . "\nBaris yang sudah ditulis tetap terenkripsi dan terbaca; perbaiki penyebab lalu jalankan ulang.\n");
}

foreach ($ringkas as $t => $r) {
    printf("%-8s baris %d | perlu dienkripsi %d (%d kolom) | ditulis %d | dilewati karena berubah saat proses %d\n",
        $t, $r['baris'], $r['perlu'], $r['kolom'], $r['ditulis'], $r['lewat_berubah']);
}
// pemeriksaan akhir: sisa nilai yang belum terenkripsi dan NIK yang tidak bisa dibuka
$sisa = db_satu("select sum(ID_NUMBER<>'' and ID_NUMBER not like 'enc:v1:%') nik, sum(HADDRESS1<>'' and HADDRESS1 not like 'enc:v1:%') alamat,
    sum(ID_NUMBER<>'' and ID_NUMBER_IDX is null) tanpa_idx from deposan");
printf("deposan sisa belum terenkripsi: NIK %d, alamat %d, NIK tanpa indeks %d\n", $sisa['nik'], $sisa['alamat'], $sisa['tanpa_idx']);
$rusak = 0;
foreach (db_semua("select ID_NUMBER, HADDRESS1 from deposan union all select a.ID_NUMBER, a.HADDRESS1 from anggota a join deposan d on d.no=a.no and d.CIF=a.CIF") as $row) {
    foreach (array('ID_NUMBER', 'HADDRESS1') as $k) {
        if (data_terenkripsi($row[$k]) && dekripsi_data($row[$k]) === null) {
            $rusak++;
        }
    }
}
echo "nilai terenkripsi yang tidak bisa dibuka dengan kunci ini: $rusak\n";
echo $tulis ? "Selesai.\n" : "Mode uji selesai, tidak ada yang ditulis. Jalankan dengan --jalankan --sudah-backup untuk menulis.\n";
