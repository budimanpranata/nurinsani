<?php require_once __DIR__ . '/../../koneksi_db.php';
/**
 * Proses tombol Disburse di Monitoring > Approval level kp (kp/view/view_approval.php), dipanggil lewat AJAX.
 * ?p=update, POST unit, jatuh (tanggal = tgl wakalah), murab (tgl murabahah), nomi (jumlah), code (rekening bank), ket,
 * checkbox (Split).
 *   - kode transaksi KK/1010 + 5 karakter acak (seperti realisasi unit: BU/<unit> + acakangkahuruf), dicek unik
 *   - jurnal_umum unit 1010 dan unit tujuan dengan kode yang sama
 *   - tabel_transaksi unit 1010 : debet RAK unit (1010-<branch.GL unit>) / kredit rekening bank
 *   - tabel_transaksi unit      : debet 1120000 / kredit 2500000
 *   - calon_loan PENDING unit + tgl_wakalah + tgl_murab: status_app APPROVE, atau tetap PENDING bila Split dicentang
 * Jawaban teks: "Sukses" atau daftar kesalahan (HTML) yang ditampilkan halaman.
 * Rekening sama dengan versi lama; query prepared statement. Wajib login (versi lama tanpa cek login).
 * Perbaikan 2026-11-03 (perintah user, PEKERJAAN_TERTUNDA.md bagian 57):
 *   - kode transaksi dibuat di server dan unik (dulu dihitung saat halaman dibuka sehingga beberapa Disburse memakai
 *     kode yang sama)
 *   - proses dikunci (GET_LOCK) dan pengajuan PENDING dicek di dalam kunci: klik ganda / proses ulang pengajuan yang
 *     sudah APPROVE ditolak
 *   - unit harus terdaftar di branch dengan GL (dulu jurnal 1010 tercatat dengan rekening kosong); rekening harus
 *     rekening bank unit 1010 (gr_sub 1310000/1330000, sama dengan daftar saran); tanggal harus valid; jumlah angka
 *     bulat > 0 dan tidak melebihi total plafond pengajuan
 *   - status diubah per unit + tgl_wakalah + tgl_murab sesuai baris yang diklik (dulu per unit + tgl_wakalah saja)
 *   - jurnal_umum juga dicatat untuk unit tujuan; jurnal dan status dalam satu transaksi DB (rollback bila gagal)
 * Dibuang: cabang ?p=hapus (tidak punya tombol pembuka, hanya menampilkan pesan lalu redirect ke halaman yang tidak ada).
 */
require_once __DIR__ . '/../../inc/tampilan.php';
session_start();
ob_start();
require_once __DIR__ . '/../../inc/cek_login.php';
wajib_login('json');
$userid = (string) $_SESSION['userid'];
include "../../function.php";

$p = isset($_GET['p']) ? (string) $_GET['p'] : '';
$pos = function ($k) {
    return isset($_POST[$k]) ? trim((string) $_POST[$k]) : '';
};
$tanggal_valid = function ($t) {
    $d = DateTime::createFromFormat('!Y-m-d', substr($t, 0, 10));
    return $d && $d->format('Y-m-d') === substr($t, 0, 10);
};

switch ($p) {
    default:
        echo "Halaman tidak ditemukan";
        break;

    case "tambah":
        break;

    case "update":
        $unitt = $pos('unit');
        $tgl = $pos('jatuh');
        $buss_date = $tgl;
        $murab = $pos('murab');
        $nomi = $pos('nomi');
        $ket = $pos('ket');
        $rek = $pos('code');
        $status_app = isset($_POST['checkbox']) ? 'PENDING' : 'APPROVE';

        $err = array();
        if ($tgl === '') {
            $err[] = "<h5>* TANGGAL Masih Kosong</h5>";
        } elseif (!$tanggal_valid($tgl)) {
            $err[] = "<h5>* TANGGAL tidak valid</h5>";
        }
        if ($murab === '' || !$tanggal_valid($murab)) {
            $err[] = "<h5>* Data pengajuan tidak lengkap, muat ulang halaman</h5>";
        }
        if ($rek === '') {
            $err[] = "<h5>* REKENING GL Masih Kosong</h5>";
        } elseif (!db_satu("SELECT 1 as ada FROM tabel_master WHERE rekening_gl=? and unit ='1010' and gr_sub in ('1310000','1330000')", 's', $rek)) {
            $err[] = "<h5>* REKENING " . h($rek) . " tidak terdaftar sebagai rekening bank unit 1010</h5>";
        }
        if ($nomi === '') {
            $err[] = "<h5>* NOMINAL Masih Kosong</h5>";
        } elseif (!preg_match('/^[0-9]+$/', $nomi) || (int) $nomi <= 0) {
            $err[] = "<h5>* NOMINAL harus angka lebih dari 0</h5>";
        }
        $branch = db_satu("select kode_branch,GL from branch WHERE kode_branch = ?", 's', $unitt);
        if (!$branch || trim((string) $branch['GL']) === '') {
            $err[] = "<h5>* UNIT " . h($unitt) . " tidak terdaftar atau belum punya rekening RAK</h5>";
        }
        if ($err) {
            foreach ($err as $val) {
                echo $val;
            }
            break;
        }
        $rak_unit = "1010-" . $branch['GL'];

        $kunci = db_satu("SELECT GET_LOCK('nurinsani_kp_approval', 15) as ok");
        if (!$kunci || (int) $kunci['ok'] !== 1) {
            echo "<h5>* Sistem sedang memproses transaksi lain, coba lagi</h5>";
            break;
        }
        $lepas = function () {
            db_satu("SELECT RELEASE_LOCK('nurinsani_kp_approval') as ok");
        };

        // pengajuan yang diklik harus masih PENDING (dicek di dalam kunci supaya klik ganda tidak tercatat dua kali)
        $aju = db_satu("select count(*) as noa, coalesce(sum(plafond),0) as pla from calon_loan
            where status_app='PENDING' and unit=? and tgl_wakalah=? and tgl_murab=?", 'sss', $unitt, $tgl, $murab);
        if (!$aju || (int) $aju['noa'] === 0) {
            $lepas();
            echo "<h5>* Pengajuan unit " . h($unitt) . " tanggal " . h(substr($tgl, 0, 10)) . " tidak ditemukan atau sudah diproses</h5>";
            break;
        }
        if ((float) $nomi > (float) $aju['pla']) {
            $lepas();
            echo "<h5>* NOMINAL melebihi total plafond pengajuan (" . number_format((float) $aju['pla']) . ")</h5>";
            break;
        }

        // kode unik KK/1010 + 5 karakter acak (pola realisasi unit)
        $kode_transaksi = '';
        for ($i = 0; $i < 20 && $kode_transaksi === ''; $i++) {
            $calon = "KK/1010" . acakangkahuruf(5);
            if (!db_satu("select 1 as ada from tabel_transaksi where kode_transaksi=? limit 1", 's', $calon)
                && !db_satu("select 1 as ada from jurnal_umum where kode_transaksi=? limit 1", 's', $calon)) {
                $kode_transaksi = $calon;
            }
        }
        if ($kode_transaksi === '') {
            $lepas();
            echo "<h5>* Gagal membuat kode transaksi, coba lagi</h5>";
            break;
        }

        $db = db_ni();
        mysqli_begin_transaction($db);
        $gagal_awal = db_jumlah_gagal();
        $transaksi = function ($unit_tt, $rekening, $kolom) use ($kode_transaksi, $buss_date, $ket, $nomi, $userid) {
            db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,$kolom,id_admin)
                values(?,?,?,?,'Bukti Umum',?,?,?)", 'sssssss', $unit_tt, $kode_transaksi, $rekening, $buss_date, $ket, $nomi, $userid);
        };
        $transaksi('1010', $rak_unit, 'debet');
        $transaksi('1010', $rek, 'kredit');
        $transaksi($unitt, '1120000', 'debet');
        $transaksi($unitt, '2500000', 'kredit');
        db_jalankan("Update calon_loan set status_app=? where status_app='PENDING' and unit =? and tgl_wakalah=? and tgl_murab=?", 'ssss', $status_app, $unitt, $tgl, $murab);
        // jurnal_umum (MyISAM, tidak ikut rollback) dicatat terakhir; nomor_jurnal '' -> nomor otomatis (versi lama)
        if (db_jumlah_gagal() === $gagal_awal) {
            db_jalankan("insert into jurnal_umum(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values('',?,?,'1010')", 'ss', $kode_transaksi, $buss_date);
            db_jalankan("insert into jurnal_umum(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values('',?,?,?)", 'sss', $kode_transaksi, $buss_date, $unitt);
        }
        if (db_jumlah_gagal() > $gagal_awal) {
            mysqli_rollback($db);
            $lepas();
            echo "<h5>* GAGAL menyimpan, data tidak disimpan. Error: " . h(db_error_terakhir()) . "</h5>";
            break;
        }
        mysqli_commit($db);
        $lepas();

        $_SESSION['info'] = "Mengubah (kode " . $kode_transaksi . ")";
        echo "Sukses";
        break;
}

ob_end_flush();
