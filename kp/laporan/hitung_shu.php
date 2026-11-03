<?php require_once __DIR__ . '/../../koneksi_db.php';
/**
 * Menu kp > Hitung SHU (juga tujuan setelah Posting Ulang, posting_neraca_bulan.php): hitung ulang saldo tabel_master
 * kantor pusat (unit 1010), isi SHU tahun berjalan di rekening 3902000, lalu salin rekening rugi-laba ke tabel_rugi_laba
 * (sumber laporan Rugi-Laba kp). Tombol Proses hanya muncul bila tidak ada transaksi 1010 yang belum diposting dalam
 * rentang Posting Ulang (sesi tgl_awal_pos / tgl_akhir_pos).
 * Dimigrasi 2026-11-03: tampilan Bootstrap 5, prepared statement, wajib login (dulu tanpa cek), proses lalu kembali ke
 * halaman ini dengan pesan sama; struktur mengikuti laporan/hitung_shu.php (unit). Rumus sama dengan versi lama kp,
 * yang berbeda dengan versi unit:
 *   - unit selalu 1010; AKTIVA saldo_akhir = sisa_debet - sisa_kredit, PASIVA = sisa_kredit - sisa_debet (sisa dari
 *     Posting Ulang), tanpa perhitungan rekening admin
 * Satu penyesuaian sama dengan versi unit: 4 update saldo_jalan yang di versi lama dijalankan ulang untuk setiap rekening
 * rugi-laba kini dijalankan sekali (hasilnya sama). tabel_master MyISAM, jadi tanpa transaksi DB (sama dengan versi lama).
 */
require_once __DIR__ . '/../../inc/tampilan.php';
require_once __DIR__ . '/../../inc/cek_login.php';
wajib_login();
ob_start();
$tgl_awal = isset($_SESSION['tgl_awal_pos']) ? (string) $_SESSION['tgl_awal_pos'] : '';
$tgl_akhir = isset($_SESSION['tgl_akhir_pos']) ? (string) $_SESSION['tgl_akhir_pos'] : '';
$unit = '1010';

if (isset($_POST['hitung_shu'])) {
    $pesan_alert = array();
    $gagal = array();
    db_jalankan("UPDATE tabel_master set saldo_awal ='0',saldo_akhir='0',mut_debet='0',mut_kredit='0',sisa_debet='0',sisa_kredit='0' where LINE_BALANCE ='HEADING' and unit =? and kode_rekening not in ('2500000')", 's', $unit);
    db_jalankan("UPDATE tabel_master set saldo_akhir='0' where LINE_BALANCE ='DETAIL' and unit =?", 's', $unit);
    db_jalankan("update tabel_master set saldo_akhir = sisa_debet - sisa_kredit where gr_neraca ='AKTIVA' and unit =?", 's', $unit);
    db_jalankan("update tabel_master set saldo_akhir = sisa_kredit - sisa_debet where gr_neraca ='PASIVA' and unit =?", 's', $unit);
    //Pendapatan
    db_jalankan("update tabel_master set saldo_akhir = sisa_kredit - sisa_debet where kode_rekening between '40000' and '42399' and unit =?", 's', $unit);
    //Biaya
    db_jalankan("update tabel_master set saldo_akhir = sisa_debet - sisa_kredit where kode_rekening between '50000' and '56999' and unit =?", 's', $unit);
    //Pendapatan Lainnya
    db_jalankan("update tabel_master set saldo_akhir = sisa_kredit - sisa_debet where kode_rekening between '57000' and '57501' and unit =?", 's', $unit);
    //BEBAN
    $update = db_jalankan("update tabel_master set saldo_akhir = sisa_debet - sisa_kredit where kode_rekening between '58000' and '59999' and unit =?", 's', $unit);
    if ($update) {
        $pesan_alert[] = 'update Saldo awal saldo akhir Sukses ';
    }

    // jumlahkan ke rekening induk: gr_head dulu, lalu gr_sub (urutan versi lama)
    foreach (array('gr_head', 'gr_sub') as $kolom_grup) {
        $grup = db_semua("Select $kolom_grup as kode_rekening,sum(mut_debet) as mut_debet, sum(mut_kredit) as mut_kredit,
            sum(saldo_awal) as saldo_awal, sum(saldo_akhir) as saldo_akhir from tabel_master where unit =? and $kolom_grup > 0 group by $kolom_grup ", 's', $unit);
        foreach ($grup as $data) {
            db_jalankan("UPDATE tabel_master set saldo_awal =?,saldo_akhir=?,mut_debet=?,mut_kredit=? where kode_rekening =? and unit =?", 'ssssss',
                (string) $data['saldo_awal'], (string) $data['saldo_akhir'], (string) $data['mut_debet'], (string) $data['mut_kredit'],
                (string) $data['kode_rekening'], $unit);
        }
    }

    ///////////////////////// HITUNG SHU /////////////////////
    $ada_rugi_laba = false;
    foreach (db_semua("select kode_rekening,posisi,sisa_debet,sisa_kredit from tabel_master where unit =?", 's', $unit) as $row) {
        if ($row['posisi'] == 'rugi-laba') {
            db_jalankan("update tabel_master set rl_debet=?, rl_kredit=? where kode_rekening=? and unit =?", 'ssss',
                (string) $row['sisa_debet'], (string) $row['sisa_kredit'], (string) $row['kode_rekening'], $unit);
            $ada_rugi_laba = true;
        } else {
            db_jalankan("update tabel_master set nrc_debet=?, nrc_kredit=? where kode_rekening=?  and unit =?", 'ssss',
                (string) $row['sisa_debet'], (string) $row['sisa_kredit'], (string) $row['kode_rekening'], $unit);
        }
    }
    // versi lama menjalankan 4 update ini di dalam loop untuk setiap rekening rugi-laba; hasilnya sama bila sekali
    if ($ada_rugi_laba) {
        db_jalankan("update tabel_master set saldo_jalan = mut_kredit - mut_debet where kode_rekening between '40000' and '42399' and unit =?", 's', $unit);
        db_jalankan("update tabel_master set saldo_jalan = mut_debet - mut_kredit where kode_rekening between '50000' and '56999' and unit =?", 's', $unit);
        db_jalankan("update tabel_master set saldo_jalan = mut_kredit - mut_debet where kode_rekening between '57000' and '57501' and unit =?", 's', $unit);
        db_jalankan("update tabel_master set saldo_jalan = mut_debet - mut_kredit where kode_rekening between '58000' and '58999' and unit =?", 's', $unit);
    }

    //update rugi laba debet dan neraca kredit dengan SHU
    $saldo = function ($kode) use ($unit) {
        return db_satu("select saldo_jalan,saldo_awal,saldo_akhir from tabel_master where unit =? and kode_rekening=?", 'ss', $unit, $kode);
    };
    $nilai = function ($baris, $kolom) {
        return compat_num($baris ? $baris[$kolom] : null);
    };
    $total_pendapan = $saldo('40000');
    $total_biaya = $saldo('50000');
    $total_pendapan_n = $saldo('57000');
    $total_beban = $saldo('58000');
    $shu_ops_jalan = $nilai($total_pendapan, 'saldo_jalan') - $nilai($total_biaya, 'saldo_jalan');
    $shu_ops_akhir = $nilai($total_pendapan, 'saldo_akhir') - $nilai($total_biaya, 'saldo_akhir');
    $shu_non_ops_jalan = $nilai($total_pendapan_n, 'saldo_jalan') - $nilai($total_beban, 'saldo_jalan');
    $shu_ops_non_akhir = $nilai($total_pendapan_n, 'saldo_akhir') - $nilai($total_beban, 'saldo_akhir');
    $total_biaya_beba = $nilai($total_biaya, 'saldo_akhir') + $nilai($total_beban, 'saldo_akhir');
    $total_pendapantan_dan_n = $nilai($total_pendapan_n, 'saldo_akhir') + $nilai($total_pendapan, 'saldo_akhir');
    $shu_sb_pajak_jalan = $shu_ops_jalan + $shu_non_ops_jalan;
    $shu_sb_pajak_akhir = $shu_ops_akhir + $shu_ops_non_akhir;
    $update_shu = db_jalankan("update tabel_master set  mut_debet=?,mut_kredit=?,saldo_akhir=?, saldo_jalan=? where kode_rekening='3902000' and unit =?", 'sssss',
        (string) $total_biaya_beba, (string) $total_pendapantan_dan_n, (string) $shu_sb_pajak_akhir, (string) $shu_sb_pajak_jalan, $unit);
    if ($update_shu) {
        $pesan_alert[] = 'update Total SHU Sukses ';
    } else {
        $gagal[] = 'Update total SHU (rekening 3902000) gagal: ' . db_error_terakhir();
    }

    ///////////////////HITUNG LABA-RUGI////////////////////
    if (db_jalankan("delete from tabel_rugi_laba where unit =?", 's', $unit)) {
        db_jalankan("INSERT INTO tabel_rugi_laba SELECT * FROM tabel_master where gr_neraca='rugi-laba' and unit =?", 's', $unit);
    } else {
        $gagal[] = 'Hapus data lama tabel_rugi_laba gagal: ' . db_error_terakhir();
    }
    $_SESSION['pesan_shu'] = array('alert' => $pesan_alert, 'gagal' => $gagal, 'berhasil' => (bool) $update_shu);
    header('Location: hitung_shu.php');
    exit;
}

$hasil = isset($_SESSION['pesan_shu']) ? $_SESSION['pesan_shu'] : null;
unset($_SESSION['pesan_shu']);
// tombol Proses hanya bila semua transaksi 1010 dalam rentang Posting Ulang sudah diposting
$cek = db_satu("select count(*) as n from tabel_transaksi where keterangan_posting='' and unit =? and tanggal_transaksi between ? and ?",
    'sss', $unit, $tgl_awal, $tgl_akhir);
$cek_posting = $cek ? (int) $cek['n'] : 0;

halaman_awal('Hitung Rugi-Laba');
if ($hasil) {
    foreach ($hasil['alert'] as $p) {
        echo "<script>alert(" . json_encode($p) . ");</script>";
    }
}
?>
<div class="card card-ni">
  <div class="card-header">Hitung Rugi-Laba</div>
  <div class="card-body text-center">
    <p class="text-muted fst-italic mb-1">Proses ini adalah proses untuk menghasilkan laporan keuangan yaitu menghitung Rugi Laba.</p>
    <p class="text-muted fst-italic">Proses bisa dilakukan setelah semua data diposting.</p>
    <?php if ($cek_posting === 0): ?>
    <form action="hitung_shu.php" method="post" name="postform" id="myform">
      <button type="submit" name="hitung_shu" value="Proses" class="btn btn-ni px-4" data-konfirmasi="Apakah Anda Yakin?">Proses</button>
    </form>
    <?php endif; ?>
    <?php if ($hasil): ?>
      <?php foreach ($hasil['gagal'] as $g): ?>
      <div class="alert alert-danger mt-3 mb-0"><?php echo h($g); ?></div>
      <?php endforeach; ?>
      <?php if ($hasil['berhasil']): ?>
      <div class="alert alert-success alert-dismissible fade show mt-3 mb-0" role="alert">
        <strong>Sukses</strong> SHU berhasil di Hitung
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
      <?php endif; ?>
    <?php endif; ?>
  </div>
</div>
<?php
halaman_akhir();
