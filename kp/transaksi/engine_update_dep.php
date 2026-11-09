<?php require_once __DIR__ . '/../../koneksi_db.php';
/**
 * Proses tombol Disburse di Monitoring > Daily Deposito level kp (kp/view/view_bayar_dep.php), dipanggil lewat AJAX.
 * ?p=update, POST id (norek), jatuh (tanggal), nomi (jumlah), code (rekening bank/GL), ket. Status dari dep_realisasi:
 *   PENEMPATAN : jurnal_umum (2x) + tabel_transaksi kredit 1010-2851000 / debet rekening; realisasi dipindah ke audit,
 *                deposito status_print OK
 *   BREAK      : tabel_transaksi debet 1010-2852000 / kredit rekening; nisbah deposito_detail dibayar; realisasi dipindah
 *                ke audit; deposito DIHAPUS
 *   lainnya    : bagi hasil. ARO-ARO -> escrow_deposito KREDIT dan nominal deposito bertambah; selain itu tabel_transaksi
 *                debet 1010-2837000 / kredit rekening. Nisbah deposito_detail dibayar, realisasi ke audit, status_print OK
 * Jawaban teks: "Sukses" atau daftar kesalahan (HTML) yang ditampilkan halaman.
 * Rekening dan urutan sama dengan versi lama; query diubah ke prepared statement. Wajib login (versi lama tanpa cek login).
 * Perbaikan 2026-11-03 (perintah user, PEKERJAAN_TERTUNDA.md bagian 51):
 *   - ditolak bila norek tidak punya data dep_realisasi (dulu tetap "Sukses" dan mencatat jurnal BU/ kosong); proses
 *     dikunci (GET_LOCK) dan realisasi dicek di dalam kunci, sehingga klik ganda tidak mencatat dua kali
 *   - PENEMPATAN dan BREAK mencatat jurnal_umum satu kali (dulu dua kali untuk kode yang sama)
 *   - jumlah harus angka bulat > 0, tanggal harus valid, rekening harus rekening bank unit 1010 (gr_sub 1310000/1330000,
 *     sama dengan daftar saran)
 */
require_once __DIR__ . '/../../inc/tampilan.php';
session_start();
require_once __DIR__ . '/../../inc/cek_login.php';
wajib_level('kp');
ob_start();
require_once __DIR__ . '/../../inc/cek_login.php';
wajib_login('json');
$userid = (string) $_SESSION['userid'];
include "../../function.php";

$unit = '';
foreach (db_semua("select kode_branch from user WHERE userid = ?", 's', $userid) as $r) {
    $unit = (string) $r['kode_branch'];
}
$p = isset($_GET['p']) ? (string) $_GET['p'] : '';
$pos = function ($k) {
    return isset($_POST[$k]) ? (string) $_POST[$k] : '';
};

switch ($p) {
    default:
        echo "Halaman tidak ditemukan";
        break;

    case "tambah":
        break;

    case "update":
        $norek = $pos('id');
        $buss_date = $pos('jatuh');
        $nomi = $pos('nomi');
        $rek = $pos('code');
        $ket = $pos('ket');
        $tgl = $pos('jatuh');
        // versi lama: unit jurnal selalu 1010
        $unit = '1010';

        $err = array();
        if (empty($tgl)) {
            $err[] = "<h5>* TANGGAL Masih Kosong</h5>";
        } else {
            $d = DateTime::createFromFormat('!Y-m-d', substr($tgl, 0, 10));
            if (!$d || $d->format('Y-m-d') !== substr($tgl, 0, 10)) { $err[] = "<h5>* TANGGAL tidak valid</h5>"; }
        }
        if (empty($rek)) {
            $err[] = "<h5>* REKENING GL Masih Kosong</h5>";
        } elseif (!db_satu("SELECT 1 as ada FROM tabel_master WHERE rekening_gl=? and unit ='1010' and gr_sub in ('1310000','1330000')", 's', $rek)) {
            $err[] = "<h5>* REKENING " . h($rek) . " tidak terdaftar sebagai rekening bank unit 1010</h5>";
        }
        if (empty($nomi)) {
            $err[] = "<h5>* NOMINAL Masih Kosong</h5>";
        } elseif (!preg_match('/^[0-9]+$/', $nomi) || (int) $nomi <= 0) {
            $err[] = "<h5>* NOMINAL harus angka lebih dari 0</h5>";
        }

        if ($err) {
            foreach ($err as $val) {
                echo $val;
            }
            break;
        }

        $kunci = db_satu("SELECT GET_LOCK('nurinsani_kp_disburse_dep', 15) as ok");
        if (!$kunci || (int) $kunci['ok'] !== 1) {
            echo "<h5>* Sistem sedang memproses transaksi lain, coba lagi</h5>";
            break;
        }
        // status dan id realisasi dari baris terakhir realisasi rekening ini (versi lama)
        $posisi = null;
        $id_realisasi = null;
        foreach (db_semua("select status,id_realisasi from dep_realisasi WHERE norek = ?", 's', $norek) as $st) {
            $posisi = $st['status'];
            $id_realisasi = $st['id_realisasi'];
        }
        if ($id_realisasi === null) {
            db_satu("SELECT RELEASE_LOCK('nurinsani_kp_disburse_dep') as ok");
            echo "<h5>* Data realisasi rekening " . h($norek) . " tidak ditemukan atau sudah diproses</h5>";
            break;
        }
        $kode_transaksi = "BU/" . $id_realisasi;
        $jurnal_umum = function () use ($kode_transaksi, $buss_date, $unit) {
            // nomor_jurnal '' -> nomor otomatis (versi lama)
            db_jalankan("insert into jurnal_umum(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values('',?,?,?)", 'sss', $kode_transaksi, $buss_date, $unit);
        };
        $transaksi = function ($rekening, $kolom, $jenis) use ($unit, $kode_transaksi, $buss_date, $ket, $nomi) {
            db_jalankan("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,$kolom,id_admin)
                values(?,?,?,?,?,?,?,'userf')", 'sssssss', $unit, $kode_transaksi, $rekening, $buss_date, $jenis, $ket, $nomi);
        };
        $pindah_audit = function () use ($norek) {
            db_jalankan("insert into audit_dep_realisasi select * from dep_realisasi where norek =?", 's', $norek);
            db_jalankan("delete from dep_realisasi where norek =?", 's', $norek);
        };
        $bayar_nisbah = function () use ($nomi, $tgl, $norek) {
            db_jalankan("update deposito_detail set jumlah_nisbah =?, ket ='BAYAR', tgl_bayar =? where tgl_jatuh_tempo =? and id_deposito=?", 'ssss', $nomi, $tgl, $tgl, $norek);
        };

        if ($posisi == 'PENEMPATAN') {
            // jurnal_umum cukup satu kali (versi lama dua kali untuk kode yang sama)
            $jurnal_umum();
            $transaksi('1010-2851000', 'kredit', 'Bukti Umum');
            $transaksi($rek, 'debet', 'Bukti Umum');
            $pindah_audit();
            db_jalankan("update deposito set status_print='OK' where norek=?", 's', $norek);
        } elseif ($posisi == 'BREAK') {
            // jurnal_umum cukup satu kali (versi lama dua kali untuk kode yang sama)
            $jurnal_umum();
            $transaksi('1010-2852000', 'debet', 'Bukti Umum');
            $transaksi($rek, 'kredit', 'Bukti Umum');
            $bayar_nisbah();
            $pindah_audit();
            db_jalankan("delete from deposito where norek =?", 's', $norek);
        } else {
            $aro = null;
            $cif = null;
            foreach (db_semua("select * from deposito WHERE norek = ?", 's', $norek) as $st_aro) {
                $aro = $st_aro['aro'];
                $cif = $st_aro['cif'];
            }
            if ($aro == 'ARO-ARO') {
                db_jalankan("insert into escrow_deposito (BUSS_DATE,NOREK,UNIT,CIF, DEBIT, TYPE,KREDIT,USERID,KET)
                    values(?,?,?,?,'0','01',?,?,'ARO')", 'ssssss', $buss_date, $norek, $unit, (string) $cif, $nomi, $userid);
                $bayar_nisbah();
                db_jalankan("update deposito set nominal=nominal+? where norek=?", 'ss', $nomi, $norek);
                $pindah_audit();
                db_jalankan("update deposito set status_print='OK' where norek=?", 's', $norek);
            } else {
                $jurnal_umum();
                $transaksi('1010-2837000', 'debet', 'BS');
                $transaksi($rek, 'kredit', 'BS');
                $bayar_nisbah();
                $pindah_audit();
                db_jalankan("update deposito set status_print='OK' where norek=?", 's', $norek);
            }
        }

        db_satu("SELECT RELEASE_LOCK('nurinsani_kp_disburse_dep') as ok");
        $_SESSION['info'] = "Mengubah";
        echo "Sukses";
        break;

    case "hapus":
        echo "Maaf ada tidak bisa mengkases menu ini silahkan hubungi atasan anda";
        $_SESSION['info'] = "GAGAL";
        header("location:view/view_jatuh.php");
        break;
}

ob_end_flush();
