<?php require_once __DIR__ . '/../koneksi_db.php';
/**
 * Menu Approve > Buka Blokir (level lead): buka / tutup blokir simpanan per kelompok (01) atau perorangan/CIF (02),
 * lalu tampilkan mutasi simpanan kelompok/CIF itu dengan saldo berjalan.
 *   tutup : TYPE semua baris simpanan (blok='1') jadi '02'
 *   buka  : TYPE baris yang '02' (blok='1') jadi '01'
 * Logika sama dengan versi lama; hanya tampilan dan query (prepared statement) yang berubah. Saran CIF/kelompok dari
 * ajax/cari_blokir.php (query sama dengan lead/autosuggesst_rek.php). var_dump($jenis_blokir) sisa debug dihapus.
 * Perilaku lama yang dipertahankan dicatat di PEKERJAAN_TERTUNDA.md bagian 39.
 */
require_once __DIR__ . '/../inc/tampilan.php';
session_start();
ob_start();
$userid = isset($_SESSION['userid']) ? $_SESSION['userid'] : null;
//cek apakah user sudah login
if (!isset($_SESSION['userid'])) {
    header("location:index.html");
    exit;
}
if ($_SESSION['level'] != "lead") {
    header("location:../index.html");
    exit;
}
include "../indo_tgl.php";

$cif_kel = isset($_POST['kode_rekening']) ? (string) $_POST['kode_rekening'] : '';
$pilih = isset($_POST['select']) ? (string) $_POST['select'] : '';
$jenis_blokir = isset($_POST['jenis']) ? (string) $_POST['jenis'] : '';

$skrip = array();
$select = array();
if (isset($_POST['search'])) {
    if (empty($cif_kel) || empty($pilih) || empty($jenis_blokir)) {
        $skrip[] = "alert('Maaf,.isi Nomor kode kelompok dan Jenis Transaksi yang benar  !!!');";
        // versi lama mengarah ke buka_bokir.php (salah ketik, halaman tidak ada) - dipertahankan
        $skrip[] = "document.location.href='buka_bokir.php';";
    } else {
        // 01 = per kelompok (code_kel), 02 = perorangan (cif); pilihan lain tidak memproses apa pun
        $kolom = $pilih == '01' ? 'code_kel' : ($pilih == '02' ? 'cif' : null);
        if ($kolom !== null) {
            $teks = addslashes($cif_kel);
            if ($jenis_blokir == 'buka') {
                db_jalankan("update simpanan set type ='01', blok ='1' where $kolom =? and type ='02' and blok ='1' ", 's', $cif_kel);
                $cek = db_satu("SELECT count(*) as n FROM simpanan WHERE type='01' AND $kolom =? AND blok='1'", 's', $cif_kel);
                $berhasil = $cek && (int) $cek['n'] >= 1;
                $skrip[] = $berhasil ? "alert('Blokir Rekening $teks" . ($pilih == '01' ? '  ' : ' ') . "Sudah di buka !!!');" : "alert('Gagal Buka Blokir !!!');";
            } else {
                db_jalankan("update simpanan set type ='02', blok ='1' where $kolom =? and blok ='1'", 's', $cif_kel);
                $cek = db_satu("SELECT count(*) as n FROM simpanan WHERE type='02' AND $kolom =? and blok ='1'", 's', $cif_kel);
                $berhasil = $cek && (int) $cek['n'] >= 1;
                $skrip[] = $berhasil ? "alert('Rekening $teks  Sudah di Blokir !!!');" : "alert('Gagal TUTUP Blokir !!!');";
            }
            if ($berhasil) {
                // saldo berjalan @bal dihitung MySQL seperti versi lama (koneksi yang sama)
                db_jalankan("SET @bal = 0;");
                $select = db_semua("SELECT simpanan.NOREK,simpanan.BUSS_DATE,anggota.CUST_SHORT_NAME,TYPE,DEBIT,KREDIT,@bal:=(@bal+KREDIT)-DEBIT as balance FROM simpanan
                    join anggota on anggota.cif = simpanan.cif
                    where simpanan.$kolom=? order by simpanan.REFF", 's', $cif_kel);
            }
        }
    }
}

halaman_awal('Buka Blokir Simpanan');
foreach ($skrip as $s) {
    echo "<script>$s</script>";
}
?>
<div class="card card-ni mb-3">
  <div class="card-header">Input Data</div>
  <div class="card-body">
    <form method="post" action="buka_blokir.php" name="postform" class="row g-3" autocomplete="off">
      <div class="col-sm-6">
        <label class="form-label" for="select">Group Blokir</label>
        <select name="select" id="select" class="form-select">
          <option>Pilih Salah satu</option>
          <option value="01">Kelompok</option>
          <option value="02">Perorangan </option>
        </select>
      </div>
      <div class="col-sm-6">
        <label class="form-label" for="kode">Kode Kelompok/CIF</label>
        <div class="autocomplete position-relative">
          <input type="text" name="kode_rekening" id="kode" class="form-control"
                 data-autocomplete="../ajax/cari_blokir.php" data-isi-nilai="cif" data-isi-label="label"
                 data-isi-target='{"#country":"nama"}'>
        </div>
      </div>
      <div class="col-sm-6">
        <label class="form-label" for="country">Nama</label>
        <input type="text" disabled="disabled" name="nama_rekening" id="country" class="form-control">
      </div>
      <div class="col-sm-6">
        <span class="form-label d-block">Jenis Blokir</span>
        <div class="form-check form-check-inline mt-1">
          <input class="form-check-input" name="jenis" type="radio" value="buka" id="jenis-buka">
          <label class="form-check-label" for="jenis-buka">Buka</label>
        </div>
        <div class="form-check form-check-inline">
          <input class="form-check-input" name="jenis" type="radio" value="tutup" id="jenis-tutup">
          <label class="form-check-label" for="jenis-tutup">Tutup</label>
        </div>
      </div>
      <div class="col-12">
        <button name="search" type="submit" id="submit2" value="Input" class="btn btn-ni">Input</button>
      </div>
    </form>
  </div>
</div>

<div class="card card-ni mw-100">
  <div class="table-responsive">
    <table class="table table-sm table-bordered table-ni align-middle mb-0">
      <thead>
        <tr>
          <th>NOREK</th>
          <th>NAMA</th>
          <th>TGL TRANSAKSI</th>
          <th>JENIS BLOKIR</th>
          <th class="text-end">DEBIT</th>
          <th class="text-end">KREDIT</th>
          <th class="text-end">SALDO</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($select as $data): ?>
        <tr>
          <td><?php echo h($data['NOREK']); ?></td>
          <td><?php echo h($data['CUST_SHORT_NAME']); ?></td>
          <td><?php echo h(tgl_indo($data['BUSS_DATE'])); ?></td>
          <td><?php echo h($data['TYPE']); ?></td>
          <td class="text-end"><?php echo number_format(compat_num($data['DEBIT'])); ?></td>
          <td class="text-end"><?php echo number_format(compat_num($data['KREDIT'])); ?></td>
          <td class="text-end"><?php echo number_format(compat_num($data['balance'])); ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php
halaman_akhir();
