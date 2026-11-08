<?php require_once __DIR__ . '/../koneksi_db.php';
/**
 * Proses Realisasi Bagi Hasil (POST dari view/view_jatuh.php): jatuh_tempo (Y-m-d), cekbox[] (norek).
 * Per deposito (hitungan inc/bagi_hasil_deposito.php, rekening dan urutan sama dengan versi lama):
 *   ARO_Bulan / ARO_bulan (huruf besar/kecil tidak dibedakan, juga ARO_jatpo) : tabel_transaksi debet 1010-41001 baghas, 1010-51101 bonus / kredit simpanan berjangka menurut tenor
 *               (1010-2202000/2203000/2204000/2206000) bayar, 1010-2423000 pajak; jurnal_umum; deposito nominal + bayar,
 *               status_print ADVIS, last_payment
 *   ARO_jatpo : debet 41001, 51101 / kredit 1010-2102000, 2423000; jurnal_umum; escrow_deposito KREDIT; last_payment
 *   lainnya   : dep_realisasi BAGI_HASIL (dibayar lewat Disburse kp); debet 1010-41004, 1010-51102 / kredit
 *               1010-2837000, 2423000; jurnal_umum; last_payment
 * Tanggal transaksi = tanggal jatuh tempo.
 * Dimigrasi 2026-11-04: prepared statement, kembali ke view_jatuh.php dengan pesan.
 * Perbaikan atas perintah user 2026-11-04:
 *   - tanggal transaksi dari form (dulu $_SESSION['jatuh'] yang hanya terisi lewat Search: dari daftar hari ini
 *     tanggal jurnal kosong, atau terpakai tanggal pencarian sebelumnya)
 *   - ARO_Bulan/ARO_jatpo menandai jadwalnya (deposito_detail ket 'REALISASI', tgl_bayar, jumlah_nisbah) sehingga
 *     tidak bisa direalisasi dua kali (dulu tetap tampil dan bisa dibukukan ulang, ARO_Bulan menambah nominal lagi);
 *     proses dikunci dan jadwal dicek ulang di dalam kunci
 *   - satu nomor bukti per deposito BU/-<unit>-<6 digit> (inc/nomor_bukti.php), jurnal_umum satu kali per deposito
 *     (dulu satu kode BU/<unit><max nomor_jurnal+1> untuk semua deposito terpilih, jurnal_umum dicatat berulang)
 *   - tabel_transaksi + penanda jadwal dalam transaksi; gagal -> deposito itu dibatalkan dengan pesan
 */
date_default_timezone_set('Asia/Jakarta');
session_start();
ob_start();
require_once __DIR__ . '/../inc/cek_login.php';
wajib_login();
if ((isset($_SESSION['level']) ? $_SESSION['level'] : '') != "admin") {
    header("location:../../index.html");
    exit;
}
require_once __DIR__ . '/../inc/nomor_bukti.php';
require_once __DIR__ . '/../inc/bagi_hasil_deposito.php';

$userid = (string) $_SESSION['userid'];
// menu Posting pindah ke level admin (2026-11-07, perintah user): unit jurnal tetap unit fund, bukan unit user yang login
$unit = '1010';

/** Bukukan realisasi satu deposito (dipanggil di dalam nb_kunci); hasil null bila berhasil, atau pesan galat. */
function realisasi_satu($norek, $tanggal, $unit, $userid)
{
    $rows = bhd_daftar($tanggal, $norek);
    if (!$rows) {
        return "$norek: tidak ada jadwal yang belum direalisasi pada tanggal ini.";
    }
    $row = $rows[0];
    $x = bhd_hitung($row);
    // perbaikan 2026-11-04 (perintah user): jenis ARO dikenali tanpa membedakan huruf besar/kecil; penempatan menyimpan
    // 'ARO_bulan' sedangkan versi lama memeriksa 'ARO_Bulan', sehingga ARO bulanan diproses sebagai bagi hasil biasa
    // (dep_realisasi / 1010-2837000) dan nominalnya tidak pernah bertambah
    $aro = strtolower((string) $row['aro']);
    $cif = (string) $row['cif'];
    $tenor = (string) $row['tenor'];
    $baghas = (string) $x['baghas'];
    $bonus = (string) $x['bonus'];
    $pajak = (string) $x['pajak'];
    $bayar = (string) $x['bayar'];
    $ket = "Realisasi Pembayaran Bagi Hasil an " . $row['CUST_SHORT_NAME'];

    if ($aro == 'aro_bulan') {
        if ($tenor == '6') {
            $kredit = "1010-2203000";
        } elseif ($tenor == '3') {
            $kredit = "1010-2202000";
        } elseif ($tenor == '12') {
            $kredit = "1010-2204000";
        } else {
            $kredit = "1010-2206000";
        }
        $rek = array('1010-41001', '1010-51101', $kredit);
    } elseif ($aro == 'aro_jatpo') {
        $rek = array('1010-41001', '1010-51101', '1010-2102000');
    } else {
        $rek = array('1010-41004', '1010-51102', '1010-2837000');
    }
    $aro_saja = $aro == 'aro_bulan' || $aro == 'aro_jatpo';
    $kode = nb_baru('BU', $unit);

    // dep_realisasi (MyISAM) lebih dulu seperti versi lama: id_realisasi = norek + ke (kunci utama)
    if (!$aro_saja) {
        $id_realisasi = $norek . $row['ke'];
        if (!db_jalankan("insert into dep_realisasi(id_realisasi,norek,cif,jumlah_bayar,tgl_bayar,tgl_jatuh_tempo,norek_tujuan,ket,status)
                VALUES(?,?,?,?,?,?,?,'EVVI','BAGI_HASIL')", 'sssssss',
                $id_realisasi, $norek, $cif, $bayar, $tanggal, (string) $row['tgl_jatuh_tempo'], (string) $row['norek_tujuan'])) {
            return "$norek: gagal mencatat realisasi. " . db_error_terakhir();
        }
    }

    $db = db_ni();
    $gagal_awal = db_jumlah_gagal();
    $db->begin_transaction();
    $isi = array(array($rek[0], 'debet', $baghas), array($rek[1], 'debet', $bonus), array($rek[2], 'kredit', $bayar),
        array('1010-2423000', 'kredit', $pajak));
    foreach ($isi as $t) {
        db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,{$t[1]},id_admin)
            values(?,?,?,?,'Bukti System',?,?,?)", 'sssssss', $unit, $kode, $t[0], $tanggal, $ket, $t[2], $userid);
    }
    if ($aro_saja) {
        db_jalankan("update deposito_detail set ket='REALISASI', tgl_bayar=?, jumlah_nisbah=? where id_deposito=? and tgl_jatuh_tempo=? and ket=''",
            'ssss', $tanggal, (string) round($x['bayar']), $norek, $tanggal);
    }
    if (db_jumlah_gagal() > $gagal_awal) {
        $galat = db_error_terakhir();
        $db->rollback();
        if (!$aro_saja) {
            db_jalankan("delete from dep_realisasi where id_realisasi=?", 's', $id_realisasi);
        }
        return "$norek: gagal dibukukan. " . $galat;
    }
    $db->commit();

    db_jalankan("insert into jurnal_umum(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values('',?,?,?)", 'sss', $kode, $tanggal, $unit);
    if ($aro == 'aro_bulan') {
        db_jalankan("update deposito set nominal=nominal + ?, status_print='ADVIS', last_payment=? where norek=?", 'sss', $bayar, $tanggal, $norek);
    } elseif ($aro == 'aro_jatpo') {
        db_jalankan("insert into escrow_deposito (BUSS_DATE,NOREK,UNIT,CIF,DEBIT,TYPE,KREDIT,USERID,KET,kode_transaksi)
            VALUES(?,?,?,?,'0','03',?,?,?,?)", 'ssssssss', $tanggal, $norek, $unit, $cif, $bayar, $userid, $ket, $kode);
        db_jalankan("update deposito set last_payment=? where norek=?", 'ss', $tanggal, $norek);
    } else {
        db_jalankan("update deposito set last_payment=? where norek=?", 'ss', $tanggal, $norek);
    }
    return null;
}

$jatuh = isset($_POST['jatuh_tempo']) ? substr(trim((string) $_POST['jatuh_tempo']), 0, 10) : '';
$d = DateTime::createFromFormat('!Y-m-d', $jatuh);
$cekbox = isset($_POST['cekbox']) && is_array($_POST['cekbox']) ? array_unique(array_map('strval', $_POST['cekbox'])) : array();
$pesan = array();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: view/view_jatuh.php');
    exit;
} elseif (!$d || $d->format('Y-m-d') !== $jatuh) {
    $pesan[] = array('danger', 'Tanggal jatuh tempo tidak valid.');
} elseif (!$cekbox) {
    $pesan[] = array('warning', 'anda belum memilih data.');
} elseif (!nb_kunci('BU', $unit)) {
    $pesan[] = array('danger', 'Proses lain sedang berjalan, silakan ulangi.');
} else {
    $berhasil = 0;
    foreach ($cekbox as $norek) {
        $g = realisasi_satu($norek, $jatuh, $unit, $userid);
        if ($g === null) {
            $berhasil++;
        } else {
            $pesan[] = array('danger', $g);
        }
    }
    nb_lepas('BU', $unit);
    if ($berhasil > 0) {
        array_unshift($pesan, array('success', "Data sudah direalisasikan ($berhasil deposito)."));
    }
}
$_SESSION['pesan_jatuh'] = $pesan;
header('Location: view/view_jatuh.php' . ($d && $d->format('Y-m-d') === $jatuh ? '?tanggal=' . $jatuh : ''));
exit;
