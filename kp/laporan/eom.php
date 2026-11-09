<?php require_once __DIR__ . '/../../koneksi_db.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../../inc/cek_login.php';
wajib_level('kp');
/**
 * Menu kp > EOM (Hitung Rugi-Laba akhir bulan) untuk tanggal sistem, atas tabel_master_konsol (konsolidasi tanggal itu)
 * dan tabel_master kantor pusat:
 *   1. jurnal "Reversal Pajak Bulan Lalu" (BS/BL-1010-<tgl>): debet 1010-2881000 / kredit 1010-59999 sebesar saldo_awal
 *      59999 konsol unit 0000; nolkan 1010-59999 di konsol (unit 0000) dan tabel_master (unit 1010)
 *   2. SHU sebelum pajak semua unit konsol (40000-50000 + 57000-58000) + 56211/56212/56311/56312; taksiran pajak:
 *      persen = 4.800.000.000 / pendapatan; 12,5% x (SHU x persen) + 25% x sisanya; < 1 -> 0
 *   3. 1010-3902000 = SHU sebelum pajak unit 0000 - taksiran; 1010-2881000 dan 1010-59999 = taksiran; 1010-2800000,
 *      1010-58000, 1010-58400 dijumlah ulang dari gr_sub/gr_head (konsol unit 0000), lalu nilai yang sama ke tabel_master
 *   4. jurnal "Perhitungan Pajak <tgl>" (BS/-1010-<tgl>): debet 1010-2881000 / kredit 1010-59999 sebesar taksiran
 *   Semua jurnal keterangan_posting 'EOM'.
 * Dimigrasi 2026-11-03: tampilan Bootstrap 5, prepared statement, proses lalu kembali ke halaman ini dengan pesan sama
 * ("Proses Update Selesai!", "Porses posting Jurnal Sukses"); wajib login (dulu tanpa cek apa pun); var_dump dibuang.
 * Perhitungan, urutan, dan rekening sama dengan versi lama, termasuk:
 *   - pemeriksaan "belum diposting" memakai bulan 4 tahun 2019 tetap
 *   - update tabel_master untuk 2881000/59999/2800000/58000/58400 disaring tanggal_awal = tanggal sistem
 *   - proses ulang menambah jurnal lagi (tanpa pencegahan dobel); tanpa transaksi DB (tabel_master MyISAM)
 * Satu penyesuaian PHP 8: pendapatan 0/kosong membuat pembagian persen error fatal di PHP 8; kini persen = 0 seperti
 * hasil PHP 5 (pembagian dengan nol -> false).
 */
require_once __DIR__ . '/../../inc/tampilan.php';
require_once __DIR__ . '/../../inc/cek_login.php';
wajib_login();
ob_start();
$userid = (string) $_SESSION['userid'];
$tgl_system = isset($_SESSION['stgl_system']) ? (string) $_SESSION['stgl_system'] : '';
$tgl_cari = $tgl_system;

/** saldo_akhir rekening ringkasan konsol tanggal itu; $unit null = semua unit. */
function saldo_konsol($kode, $tgl, $unit = null)
{
    $r = $unit === null
        ? db_satu("select sum(saldo_akhir) as saldo_akhir from tabel_master_konsol where kode_rekening=? and tanggal_awal=?", 'ss', $kode, $tgl)
        : db_satu("select sum(saldo_akhir) as saldo_akhir from tabel_master_konsol where kode_rekening=? and tanggal_awal=? and unit =?", 'sss', $kode, $tgl, $unit);
    return compat_num($r ? $r['saldo_akhir'] : null);
}

/** Jumlah saldo_akhir/mut_debet/mut_kredit konsol unit 0000 untuk satu kelompok (gr_sub / gr_head). */
function jumlah_grup($kolom, $nilai, $tgl)
{
    $r = db_satu("select sum(saldo_akhir) as saldo_akhir,sum(mut_debet) as mut_debet, sum(mut_kredit) as mut_kredit from tabel_master_konsol
        where $kolom=? and unit ='0000' and tanggal_awal=?", 'ss', $nilai, $tgl);
    return array((string) ($r ? $r['saldo_akhir'] : ''), (string) ($r ? $r['mut_debet'] : ''), (string) ($r ? $r['mut_kredit'] : ''));
}

if (isset($_POST['hitung_shu'])) {
    $total_pajak = db_satu("select sum(saldo_awal) as saldo_awal,sum(saldo_awal) as saldo_akhir from tabel_master_konsol
        where kode_rekening='59999' and tanggal_awal=? and unit ='0000'", 's', $tgl_cari);
    $saldo_pajak = (string) ($total_pajak ? $total_pajak['saldo_awal'] : '');
    $ket = "Reversal Pajak Bulan Lalu";
    $kode_transaksi = "BS/BL-1010-$tgl_cari";
    db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,keterangan_posting,id_admin)
        values('1010',?,'1010-2881000',?,'Bukti System',?,?,'EOM',?)", 'sssss', $kode_transaksi, $tgl_cari, $ket, $saldo_pajak, $userid);
    db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,keterangan_posting,id_admin)
        values('1010',?,'1010-59999',?,'Bukti System',?,?,'EOM',?)", 'sssss', $kode_transaksi, $tgl_cari, $ket, $saldo_pajak, $userid);
    db_jalankan("UPDATE tabel_master_konsol set saldo_akhir ='0',mut_debet='0',mut_kredit='0' where rekening_gl in ('1010-59999') and unit ='0000' and tanggal_awal=?", 's', $tgl_cari);
    db_jalankan("UPDATE tabel_master set saldo_akhir ='0',mut_debet='0',mut_kredit='0' where rekening_gl in ('1010-59999') and unit ='1010'");

    // SHU sebelum pajak semua unit konsolidasi
    $pendapatan = saldo_konsol('40000', $tgl_cari);
    $shu_sb_pajak = ($pendapatan - saldo_konsol('50000', $tgl_cari)) + (saldo_konsol('57000', $tgl_cari) - saldo_konsol('58000', $tgl_cari));
    // SHU sebelum pajak kantor pusat (unit 0000)
    $shu_sb_pajak_p = (saldo_konsol('40000', $tgl_cari, '0000') - saldo_konsol('50000', $tgl_cari, '0000'))
        + (saldo_konsol('57000', $tgl_cari, '0000') - saldo_konsol('58000', $tgl_cari, '0000'));
    $sbg_jamu = db_satu("select sum(saldo_akhir) as saldo_akhir from tabel_master_konsol where kode_rekening in('56211','56212','56311','56312') and tanggal_awal=?", 's', $tgl_cari);
    $shu_sbp = $shu_sb_pajak + compat_num($sbg_jamu ? $sbg_jamu['saldo_akhir'] : null);
    $param_pajak = 4800000000;
    // PHP 5: pembagian dengan nol -> false (0); PHP 8 melempar DivisionByZeroError
    $persen = $pendapatan != 0 ? ($param_pajak / $pendapatan) : 0;
    $taksiran_dapat = ($shu_sbp * $persen);
    $taksiran_tidak = $shu_sbp - $taksiran_dapat;
    $estimasi_taksiran = 0.125 * $taksiran_dapat + (25 / 100) * $taksiran_tidak;
    if ($estimasi_taksiran < 1) {
        $estimasi_taksiran = 0;
    }
    $shu_sb_pajak_p = $shu_sb_pajak_p - $estimasi_taksiran;
    $est = (string) $estimasi_taksiran;
    $shu_p = (string) $shu_sb_pajak_p;

    db_jalankan("UPDATE tabel_master_konsol set saldo_akhir =? where rekening_gl='1010-3902000' and tanggal_awal=?", 'ss', $shu_p, $tgl_cari);
    db_jalankan("UPDATE tabel_master_konsol set saldo_akhir =?,mut_kredit=?,mut_debet=saldo_awal where rekening_gl='1010-2881000' and tanggal_awal=?", 'sss', $est, $est, $tgl_cari);
    db_jalankan("UPDATE tabel_master_konsol set saldo_akhir =?,mut_debet=?,mut_kredit=? where rekening_gl='1010-59999' and tanggal_awal=?", 'ssss', $est, $est, $saldo_pajak, $tgl_cari);
    db_jalankan("UPDATE tabel_master_konsol set saldo_akhir ='0',mut_debet='0',mut_kredit='0' where rekening_gl in ('1010-2800000') and unit ='0000' and tanggal_awal=?", 's', $tgl_cari);
    $s_280 = jumlah_grup('gr_sub', '2800000', $tgl_cari);
    db_jalankan("UPDATE tabel_master_konsol set saldo_akhir =?,mut_debet=?, mut_kredit=? where rekening_gl='1010-2800000' and tanggal_awal=?", 'ssss', $s_280[0], $s_280[1], $s_280[2], $tgl_cari);
    db_jalankan("UPDATE tabel_master_konsol set saldo_akhir ='0',mut_debet='0',mut_kredit='0' where rekening_gl in ('1010-58000','1010-58400') and unit ='0000' and tanggal_awal=?", 's', $tgl_cari);
    $s_580 = jumlah_grup('gr_head', '58000', $tgl_cari);
    db_jalankan("UPDATE tabel_master_konsol set saldo_akhir =?,mut_debet=?, mut_kredit=? where rekening_gl='1010-58000' and tanggal_awal=?", 'ssss', $s_580[0], $s_580[1], $s_580[2], $tgl_cari);
    $s_584 = jumlah_grup('gr_sub', '58400', $tgl_cari);
    db_jalankan("UPDATE tabel_master_konsol set saldo_akhir =?,mut_debet=?, mut_kredit=? where rekening_gl='1010-58400' and tanggal_awal=?", 'ssss', $s_584[0], $s_584[1], $s_584[2], $tgl_cari);

    db_jalankan("UPDATE tabel_master set saldo_akhir =? where rekening_gl='1010-3902000'", 's', $shu_p);
    db_jalankan("UPDATE tabel_master set saldo_akhir =?,mut_kredit=?,mut_debet=saldo_awal where rekening_gl='1010-2881000' and tanggal_awal=?", 'sss', $est, $est, $tgl_cari);
    db_jalankan("UPDATE tabel_master set saldo_akhir =?,mut_debet=?,mut_kredit=? where rekening_gl='1010-59999' and tanggal_awal=?", 'ssss', $est, $est, $saldo_pajak, $tgl_cari);
    db_jalankan("UPDATE tabel_master set saldo_akhir ='0',mut_debet='0',mut_kredit='0' where rekening_gl in ('1010-2800000') and unit ='1010' and tanggal_awal=?", 's', $tgl_cari);
    db_jalankan("UPDATE tabel_master set saldo_akhir =?,mut_debet=?, mut_kredit=? where rekening_gl='1010-2800000' and tanggal_awal=?", 'ssss', $s_280[0], $s_280[1], $s_280[2], $tgl_cari);
    db_jalankan("UPDATE tabel_master set saldo_akhir ='0',mut_debet='0',mut_kredit='0' where rekening_gl in ('1010-58000','1010-58400') and unit ='1010' and tanggal_awal=?", 's', $tgl_cari);
    db_jalankan("UPDATE tabel_master set saldo_akhir =?,mut_debet=?, mut_kredit=? where rekening_gl='1010-58000' and tanggal_awal=?", 'ssss', $s_580[0], $s_580[1], $s_580[2], $tgl_cari);
    $update = db_jalankan("UPDATE tabel_master set saldo_akhir =?,mut_debet=?, mut_kredit=? where rekening_gl='1010-58400' and tanggal_awal=?", 'ssss', $s_584[0], $s_584[1], $s_584[2], $tgl_cari);
    $pesan = array('Proses Update Selesai!');
    if ($update) {
        $ket = "Perhitungan Pajak $tgl_cari";
        $kode_transaksi = "BS/-1010-$tgl_cari";
        db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,keterangan_posting,id_admin)
            values('1010',?,'1010-2881000',?,'Bukti System',?,?,'EOM',?)", 'sssss', $kode_transaksi, $tgl_cari, $ket, $est, $userid);
        db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,keterangan_posting,id_admin)
            values('1010',?,'1010-59999',?,'Bukti System',?,?,'EOM',?)", 'sssss', $kode_transaksi, $tgl_cari, $ket, $est, $userid);
        $pesan[] = 'Porses posting Jurnal Sukses ';
    } else {
        $pesan[] = db_error_terakhir();
    }
    $_SESSION['pesan_eom'] = $pesan;
    header('Location: eom.php');
    exit;
}

$pesan = isset($_SESSION['pesan_eom']) ? $_SESSION['pesan_eom'] : null;
unset($_SESSION['pesan_eom']);
// versi lama: pemeriksaan "belum diposting" memakai bulan 4 tahun 2019 tetap
$n = db_satu("select count(*) as n from tabel_transaksi where keterangan_posting='' and unit ='1010' and month(tanggal_transaksi)='4' and year(tanggal_transaksi)='2019'");
$belum_posting = $n ? (int) $n['n'] : 0;

halaman_awal('Hitung Rugi-Laba');
?>
<?php if ($pesan): ?>
<div class="alert alert-success"><?php echo h(implode(' ', $pesan)); ?></div>
<script><?php foreach ($pesan as $p): ?>alert(<?php echo json_encode($p); ?>);<?php endforeach; ?></script>
<?php endif; ?>
<div class="card card-ni">
  <div class="card-header">Hitung Rugi-Laba</div>
  <div class="card-body text-center">
    <p class="mb-1">Tanggal Periode EOM <strong><?php echo h($tgl_system); ?></strong></p>
    <p class="text-muted fst-italic mb-1">Proses ini adalah proses untuk menghasilkan laporan keuangan yaitu menghitung Rugi Laba.</p>
    <p class="text-muted fst-italic">Proses bisa dilakukan setelah semua data diposting.</p>
    <?php if ($belum_posting === 0): ?>
    <form action="eom.php" method="post" name="postform" id="myform">
      <button type="submit" name="hitung_shu" value="Proses" class="btn btn-ni px-4" data-konfirmasi="Apakah Anda Yakin?">Proses</button>
    </form>
    <?php endif; ?>
  </div>
</div>
<?php
halaman_akhir();
