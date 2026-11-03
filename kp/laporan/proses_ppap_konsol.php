<?php require_once __DIR__ . '/../../koneksi_db.php';
/**
 * Menu kp > Hitung PPAP.
 *   Proses PPAP        : fetch POST ke progres_ppap.php (hitung ulang loan.gol semua unit dan isi report_ppap), lalu muat ulang.
 *   Posting Jurnal PPAP: untuk setiap baris report_ppap yang belum diposting dan selisihnya bukan 0, buat jurnal
 *                        tabel_transaksi tanggal sistem, kode BU/<unit><8 acak>:
 *                        status 1 -> debet 54131 / kredit 1512000 sebesar selisih; selain itu -> debet 1512000 / kredit 42131
 *                        sebesar -selisih; lalu report_ppap.status_posting='post' untuk unit itu.
 * Dimigrasi 2026-11-03: tampilan Bootstrap 5, prepared statement, posting lalu kembali ke halaman ini dengan pesan sama
 * ("update Total SHU Sukses"); indikator proses memakai spinner Bootstrap (dulu gambar GIF dari internet). Tanggal periode
 * kini tampil (dulu variabelnya kosong). Logika sama dengan versi lama, kecuali (perintah user 2026-11-03):
 *   - Posting Jurnal PPAP dulu selalu error fatal: acakangkahuruf() di file ini memanggil teks sebagai fungsi
 *     ($karakter($pos)); kini memakai acakangkahuruf() dari function.php (8 karakter acak)
 * Perbaikan lain atas perintah user 2026-11-03: hanya level kp; posting hanya baris yang belum diposting dan selisih
 * bukan 0 (dulu semua baris, termasuk yang sudah 'post' -> klik dua kali = jurnal dobel; selisih 0 -> jurnal 0), dalam
 * satu transaksi DB; teks "Proses ini akan melakukan blokir userid" dibuang (tidak ada proses blokir).
 */
require_once __DIR__ . '/../../inc/tampilan.php';
session_start();
ob_start();
$userid = isset($_SESSION['userid']) ? $_SESSION['userid'] : null;
if (!isset($_SESSION['stgl_system'])) {
    echo "<script>alert('Maaf,.tanggal system belum di setup');</script>";
    header("location:../../param_tgl.php");
    exit;
}
if (!isset($_SESSION['userid'])) {
    header("location:../../index.html");
    exit;
}
if ($_SESSION['level'] != 'kp') {
    header("location:../../index.html");
    exit;
}
$tanggal_transaksi = (string) $_SESSION['stgl_system'];
include __DIR__ . '/../../function.php';

if (isset($_POST['proses_ppap'])) {
    $_SESSION['progress'] = 0;
    $db = db_ni();
    $gagal_awal = db_jumlah_gagal();
    $db->begin_transaction();
    $jumlah = 0;
    foreach (db_semua("select no, unit, jumlah_selisih, status from report_ppap where status_posting is null and jumlah_selisih <> 0 for update") as $row_hit_mut) {
        $jumlah++;
        $unit = (string) $row_hit_mut['unit'];
        $nominal = $row_hit_mut['jumlah_selisih'];
        $kode_transaksi = "BU/" . $unit . acakangkahuruf(8);
        if ($row_hit_mut['status'] == '1') {
            db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                values(?,?,'54131',?,'Bukti System','Biaya PPA Umum-Piutang-Murabahah',?,?)", 'sssss',
                $unit, $kode_transaksi, $tanggal_transaksi, (string) $nominal, (string) $userid);
            db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                values(?,?,'1512000',?,'Bukti System','PPA Umum-PYD Piutang Murabahah',?,?)", 'sssss',
                $unit, $kode_transaksi, $tanggal_transaksi, (string) $nominal, (string) $userid);
        } else {
            $nominal = -1 * compat_num($nominal);
            db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                values(?,?,'1512000',?,'Bukti System','PPA Umum-PYD Piutang Murabahah',?,?)", 'sssss',
                $unit, $kode_transaksi, $tanggal_transaksi, (string) $nominal, (string) $userid);
            db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                values(?,?,'42131',?,'Bukti System','Pdptn-Koreksi PPA-Piutang-Murabahah',?,?)", 'sssss',
                $unit, $kode_transaksi, $tanggal_transaksi, (string) $nominal, (string) $userid);
        }
        db_jalankan("update report_ppap set status_posting ='post' where no =?", 's', (string) $row_hit_mut['no']);
    }
    if (db_jumlah_gagal() > $gagal_awal) {
        $db->rollback();
        $_SESSION['pesan_ppap'] = 'Posting Jurnal PPAP gagal dan dibatalkan: ' . db_error_terakhir();
    } else {
        $db->commit();
        $_SESSION['pesan_ppap'] = $jumlah ? 'update Total SHU Sukses ' : 'Tidak ada PPAP yang perlu diposting';
    }
    header('Location: proses_ppap_konsol.php');
    exit;
}

$pesan = isset($_SESSION['pesan_ppap']) ? $_SESSION['pesan_ppap'] : null;
unset($_SESSION['pesan_ppap']);
$report = db_semua("select no,akan_dibentuk, sdh_dibentuk, jumlah_selisih,status, report_ppap.unit, branch.unit as ap from report_ppap
    left join branch on branch.kode_branch = report_ppap.unit
    where status_posting is null");

halaman_awal('Proses PPAP');
?>
<?php if ($pesan !== null): ?>
<div class="alert alert-<?php echo stripos($pesan, 'gagal') !== false ? 'danger' : 'success'; ?>"><?php echo h($pesan); ?></div>
<script>alert(<?php echo json_encode($pesan); ?>);</script>
<?php endif; ?>
<div class="card card-ni">
  <div class="card-header">Proses PPAP</div>
  <div class="card-body text-center">
    <p class="mb-1">Tanggal Periode PPAP <strong><?php echo h($tanggal_transaksi); ?></strong></p>
    <p class="text-muted fst-italic">Proses bisa Setelah Neraca Balance.</p>
    <form action="progres_ppap.php" method="post" id="myform">
      <button type="submit" class="btn btn-ni px-4" id="tombol-proses">Proses PPAP</button>
    </form>
    <div class="mt-3 d-none" id="loadingIndicator" aria-live="polite">
      <div class="spinner-border text-success" role="status"></div>
      <div class="small text-muted mt-1">Menghitung PPAP semua unit…</div>
    </div>
  </div>
</div>

<form action="proses_ppap_konsol.php" method="post" name="postform">
  <div class="table-responsive mt-3">
    <table class="table table-sm table-bordered table-ni align-middle">
      <thead>
        <tr>
          <th>No</th><th>Kode AP</th><th>AP</th>
          <th class="text-end">Akan dibentuk</th><th class="text-end">Sudah dibentuk</th><th class="text-end">Selisih</th>
          <th>Status Jurnal</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($report as $row): ?>
        <tr>
          <td><?php echo h($row['no']); ?></td>
          <td><?php echo h($row['unit']); ?></td>
          <td><?php echo h($row['ap']); ?></td>
          <td class="text-end"><?php echo number_format(compat_num($row['akan_dibentuk'])); ?></td>
          <td class="text-end"><?php echo number_format(compat_num($row['sdh_dibentuk'])); ?></td>
          <td class="text-end"><?php echo number_format(compat_num($row['jumlah_selisih'])); ?></td>
          <td><?php echo $row['status'] == '1' ? 'Selisih lebih besar' : 'Selisih lebih Kecil'; ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <div class="text-center">
    <button type="submit" name="proses_ppap" value="Posting Jurnal PPAP" class="btn btn-ni px-4" data-konfirmasi="Apakah Anda Yakin akan proses PPAP?">Posting Jurnal PPAP</button>
  </div>
</form>

<script>
document.getElementById('myform').addEventListener('submit', function (event) {
    event.preventDefault();
    var tombol = document.getElementById('tombol-proses');
    var loadingIndicator = document.getElementById('loadingIndicator');
    tombol.disabled = true;
    loadingIndicator.classList.remove('d-none');
    fetch('progres_ppap.php', { method: 'POST', body: new FormData(this), credentials: 'same-origin' })
        .then(function (response) { return response.json(); })
        .then(function (data) {
            loadingIndicator.classList.add('d-none');
            if (data.success) {
                setTimeout(function () { window.location.href = 'proses_ppap_konsol.php'; }, 1000);
            } else {
                tombol.disabled = false;
                alert('Error: ' + data.message);
            }
        })
        .catch(function (error) {
            loadingIndicator.classList.add('d-none');
            tombol.disabled = false;
            console.error('Error:', error);
            alert('Error: Server tidak merespon.');
        });
});
</script>
<?php
halaman_akhir();
