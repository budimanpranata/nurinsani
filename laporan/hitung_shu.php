<?php require_once __DIR__ . '/../koneksi_db.php';
/**
 * Menu Proses Rugi Laba (juga Hitung SHU di menu kp/toko): hitung ulang saldo tabel_master unit, isi SHU tahun
 * berjalan di rekening 3902000, lalu salin rekening rugi-laba ke tabel_rugi_laba (sumber laporan Rugi Laba).
 * Tombol Proses hanya muncul bila semua transaksi unit sudah diposting.
 * Logika, urutan, dan rumus sama dengan versi lama (keputusan user: hanya migrasi kode); hanya tampilan dan query
 * (prepared statement) yang berubah. Satu-satunya penyesuaian: 4 update saldo_jalan yang di versi lama dijalankan ulang
 * untuk setiap rekening rugi-laba kini dijalankan sekali saja (hasilnya sama, tidak bergantung pada baris).
 * Tabel_master MyISAM, jadi proses tidak bisa dibungkus transaksi DB (sama dengan versi lama).
 */
require_once __DIR__ . '/../inc/tampilan.php';
session_start();
$userid = isset($_SESSION['userid']) ? $_SESSION['userid'] : null;
//cek apakah user sudah login (versi lama tanpa cek login; level tidak dibatasi karena menu juga dipakai kp/toko)
if (!isset($_SESSION['userid'])) {
    header("location:../index.html");
    exit;
}

include "../encryption/function.php";

$unit = null;
foreach (db_semua("select kode_branch from user where userid=?", 's', (string) $userid) as $id_user) {
    $unit = $id_user['kode_branch'];
}
$unit = (string) $unit;

$pesan_alert = array();
$berhasil = false;
$gagal = array();
if (isset($_POST['hitung_shu'])) {

    db_jalankan("UPDATE tabel_master set saldo_awal ='0',saldo_akhir='0',mut_debet='0',mut_kredit='0',sisa_debet='0',sisa_kredit='0' where LINE_BALANCE ='HEADING' and unit =? and kode_rekening not in ('2500000')", 's', $unit);
    db_jalankan("UPDATE tabel_master set saldo_akhir='0' where LINE_BALANCE ='DETAIL' and unit =?", 's', $unit);

    db_jalankan("update tabel_master set saldo_akhir = saldo_awal + mut_debet - mut_kredit where gr_neraca ='AKTIVA' and unit =?", 's', $unit);

    //ADMIN
    db_jalankan("update tabel_master set saldo_akhir = saldo_awal + mut_debet - mut_kredit where gr_neraca ='admin' and unit =? and normal ='debet'", 's', $unit);
    db_jalankan("update tabel_master set saldo_akhir = saldo_awal + mut_kredit - mut_debet where gr_neraca ='admin' and unit =? and normal ='kredit'", 's', $unit);

    //PASIVA
    db_jalankan("update tabel_master set saldo_akhir = saldo_awal + mut_kredit - mut_debet  where gr_neraca ='PASIVA' and unit =?", 's', $unit);

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
            //update rugi laba
            db_jalankan("update tabel_master set rl_debet=?, rl_kredit=? where kode_rekening=? and unit =?", 'ssss',
                (string) $row['sisa_debet'], (string) $row['sisa_kredit'], (string) $row['kode_rekening'], $unit);
            $ada_rugi_laba = true;
        } else {
            //update neraca
            db_jalankan("update tabel_master set nrc_debet=?, nrc_kredit=? where kode_rekening=?  and unit =?", 'ssss',
                (string) $row['sisa_debet'], (string) $row['sisa_kredit'], (string) $row['kode_rekening'], $unit);
        }
    }
    // versi lama menjalankan 4 update ini di dalam loop untuk setiap rekening rugi-laba; hasilnya sama bila sekali
    if ($ada_rugi_laba) {
        //Pendapatan
        db_jalankan("update tabel_master set saldo_jalan = mut_kredit - mut_debet where kode_rekening between '40000' and '42399' and unit =?", 's', $unit);
        //Biaya
        db_jalankan("update tabel_master set saldo_jalan = mut_debet - mut_kredit where kode_rekening between '50000' and '56999' and unit =?", 's', $unit);
        //Pendapatan Lainnya
        db_jalankan("update tabel_master set saldo_jalan = mut_kredit - mut_debet where kode_rekening between '57000' and '57501' and unit =?", 's', $unit);
        //BEBAN
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
        $berhasil = true;
    } else {
        $gagal[] = 'Update total SHU (rekening 3902000) gagal.';
    }

    ///////////////////HITUNG LABA-RUGI////////////////////
    //hapus tabel sementara yang lama, lalu isi ulang dari tabel_master
    if (db_jalankan("delete from tabel_rugi_laba where unit =?", 's', $unit)) {
        db_jalankan("INSERT INTO tabel_rugi_laba SELECT * FROM tabel_master where gr_neraca='rugi-laba' and unit =?", 's', $unit);
    } else {
        $gagal[] = 'Hapus data lama tabel_rugi_laba gagal.';
    }
}

// tombol Proses hanya bila semua transaksi unit sudah diposting
$cek = db_satu("select count(*) as n from tabel_transaksi where keterangan_posting='' and unit =?", 's', $unit);
$cek_posting = $cek ? (int) $cek['n'] : 0;

halaman_awal('Proses Rugi Laba');
foreach ($pesan_alert as $pesan) {
    echo "<script>alert(" . json_encode($pesan) . ");</script>";
}
?>
<div class="card card-ni">
  <div class="card-body">
    <p class="text-muted fst-italic mb-1">Proses ini adalah proses untuk menghasilkan laporan keuangan yaitu menghitung Rugi Laba.</p>
    <p class="text-muted fst-italic">Proses bisa dilakukan setelah semua data diposting.</p>
    <?php if ($cek_posting === 0): ?>
    <form action="?<?php echo paramEncrypt('page=../laporan/hitung_shu') ?>" method="post" name="postform" id="myform">
      <button type="submit" name="hitung_shu" value="Proses" class="btn btn-primary" onclick="return confirm('Apakah Anda Yakin?')">Proses</button>
    </form>
    <?php endif; ?>
    <?php foreach ($gagal as $g): ?>
    <div class="alert alert-danger mt-3 mb-0"><?php echo h($g); ?></div>
    <?php endforeach; ?>
    <?php if ($berhasil): ?>
    <div class="alert alert-success alert-dismissible fade show mt-3 mb-0" role="alert">
      <strong>Sukses</strong> SHU berhasil di Hitung
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>
  </div>
</div>
<?php
halaman_akhir();
