<?php require_once __DIR__ . '/../koneksi_db.php';
/**
 * Menu Transaksi > Jurnal Masuk (kas masuk).
 *   Simpan  : catat sisi kredit pada rekening COA yang dipilih.
 *   Selesai : catat sisi debet kas 1120000 dan nomor jurnal_masuk.
 *   Batal   : hapus satu baris transaksi (link terenkripsi).
 * Logika dan urutan proses sama dengan versi lama; hanya tampilan dan query (prepared statement) yang berubah.
 * Saran rekening memakai ajax/cari_coa.php (pengganti autosuggesst.php).
 * Perbaikan 2026-10-31 (perintah user), lihat PEKERJAAN_TERTUNDA.md bagian 19:
 *   - Batal hanya menghapus baris milik user ini pada nomor bukti yang sedang dibuka
 *   - debet kas saat Selesai = saldo kredit - debet baris unit sendiri di DB (versi lama: kredit baris terakhir saja)
 *   - TOTAL menampilkan jumlah kredit (versi lama selalu 0)
 *   - Selesai ditolak bila belum ada transaksi atau sudah selesai
 *   - Simpan memeriksa rekening (harus ada di COA, DETAIL), tanggal, dan jumlah
 *   - tanggal default = tanggal sistem (versi lama: tanggal hari ini)
 */
require_once __DIR__ . '/../inc/tampilan.php';
session_start();
$userid=isset($_SESSION['userid']) ? $_SESSION['userid'] : null;
if(!isset($_SESSION['userid'])){
   header("location:../index.html");
 exit; }

//cek level user
if($_SESSION['level']!="unit"){
   header("location:../index.html");
 exit; }

	include "../encryption/function.php";

	//untuk waktu: tanggal sistem (versi lama: date("y-m-d"), tanggal hari ini)
	$tanggal=!empty($_SESSION['stgl_system']) ? $_SESSION['stgl_system'] : date("Y-m-d");

$unit = null;
foreach (db_semua("select kode_branch from user where userid=?", 's', (string) $userid) as $id_user) {
    $unit=$id_user['kode_branch'];
}

			//jurnal baru. cari nomor paling besar yaitu nomor jurnal terakhir
			$j = db_satu("SELECT max(nomor_jurnal) AS n FROM jurnal_masuk where unit=? ORDER BY tanggal_selesai DESC", 's', (string) $unit);
			$nomor_jurnal=compat_num($j ? $j['n'] : null)+1;
			$kode_transaksi="KM/".$unit .$nomor_jurnal;
// nomor bukti yang tampil di form dihitung sebelum proses simpan (sama seperti versi lama)
$kode_bukti_form = $kode_transaksi;
$tanggal_form = empty($_POST['tanggal_transaksi']) ? $tanggal : $_POST['tanggal_transaksi'];
$keterangan_form = isset($_POST['keterangan_jurnal']) ? $_POST['keterangan_jurnal'] : '';

// pesan/script dari proses ditampilkan di bawah form, seperti versi lama
ob_start();

			//untuk menyimpan transaksi
			if(isset($_POST['simpan'])){

				$kode_transaksi=isset($_POST['kode_bukti']) ? $_POST['kode_bukti'] : null;
				$tanggal_transaksi=isset($_POST['tanggal_transaksi']) ? $_POST['tanggal_transaksi'] : null;
				$keterangan_transaksi=isset($_POST['keterangan_transaksi']) ? $_POST['keterangan_transaksi'] : null;
				$kode_rekening=isset($_POST['kode_rekening']) ? $_POST['kode_rekening'] : null;
				$keterangan_jurnal=isset($_POST['keterangan_jurnal']) ? $_POST['keterangan_jurnal'] : null;

				//untuk jurnal kas masuk, semua transaksi ada sisi KREDIT
				$jumlah_dk=ucwords((string) (isset($_POST['jumlah_dk']) ? $_POST['jumlah_dk'] : ''));

				$salah = array();
				if (!db_satu("select kode_rek from coa where kode_rek=? and LINE_BALANCE='DETAIL'", 's', (string) $kode_rekening)) {
					$salah[] = 'Rekening tidak ditemukan di COA (pilih dari daftar saran)';
				}
				if (trim((string) $tanggal_transaksi) === '') { $salah[] = 'Tanggal belum diisi'; }
				if (!is_numeric($jumlah_dk) || $jumlah_dk <= 0) { $salah[] = 'Jumlah harus angka lebih dari 0'; }
				if ($salah) {
					$query = false;
					echo "<script>alert(" . json_encode(implode("\n", $salah) . "\nTransaksi tidak disimpan.") . ");</script>";
				} else
				$query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
									values(?,?,?,?,'Kas Masuk',?,?,?)", 'sssssss', (string) $unit, (string) $kode_transaksi, (string) $kode_rekening,
					(string) $tanggal_transaksi, (string) $keterangan_transaksi, (string) $jumlah_dk, (string) $userid);
				// (versi lama mencetak mysql_error() bila gagal; kegagalan kini dicatat di log PHP oleh db_jalankan)
			}

			//untuk menyelesaikan transaksi
			if(isset($_POST['selesai'])){

				$kode_transaksi=isset($_POST['kode_bukti']) ? $_POST['kode_bukti'] : null;
				$nomor_jurnal=isset($_POST['nomor_jurnal']) ? $_POST['nomor_jurnal'] : null;
				$tgl_transaksi=isset($_POST['tanggal_selesai']) ? $_POST['tanggal_selesai'] : null;
				$keterangan_jurnal=isset($_POST['keterangan_jurnal']) ? $_POST['keterangan_jurnal'] : null;

				//karena kas masuk lawan transaksinya adalah kas, maka kas adalah 1120000 yang ada di sisi DEBET.
				$kode_rekening="1120000";
				// debet kas = saldo kredit baris unit sendiri pada nomor bukti ini (versi lama: kredit baris terakhir saja)
				$saldo = db_satu("select sum(kredit)-sum(debet) as saldo from tabel_transaksi where kode_transaksi=? and id_admin=? and unit=?", 'sss',
					(string) $kode_transaksi, (string) $userid, (string) $unit);
				$debet_kas = compat_num($saldo ? $saldo['saldo'] : null);
				$sudah = db_satu("select count(*) as n from jurnal_masuk where kode_transaksi=?", 's', (string) $kode_transaksi);
				if ($sudah && $sudah['n'] > 0) {
					echo "<script>alert('Nomor bukti " . h($kode_transaksi) . " sudah diselesaikan.');</script>";
				} elseif ($debet_kas <= 0) {
					echo "<script>alert('Belum ada transaksi yang perlu diselesaikan.');</script>";
				} else {
					if (trim((string) $keterangan_jurnal) === '') {
						// keterangan diambil dari baris terakhir bila field tersembunyi kosong (mis. setelah Batal)
						$akhir = db_satu("select keterangan_transaksi from tabel_transaksi where kode_transaksi=? and id_admin=? and unit=? order by id_transaksi desc limit 1", 'sss',
							(string) $kode_transaksi, (string) $userid, (string) $unit);
						$keterangan_jurnal = $akhir ? $akhir['keterangan_transaksi'] : $keterangan_jurnal;
					}
					$query_transaksi=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
										values(?,?,?,?,'Kas Masuk',?,?,?)", 'sssssss', (string) $unit, (string) $kode_transaksi, $kode_rekening,
						(string) $tgl_transaksi, (string) $keterangan_jurnal, (string) $debet_kas, (string) $userid);

					// nomor_jurnal ' ' -> nomor otomatis (auto increment) seperti versi lama
					db_jalankan("insert into jurnal_masuk(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values(' ',?,?,?)", 'sss',
						(string) $kode_transaksi, (string) $tgl_transaksi, (string) $unit);

					if($query_transaksi){
						?><script language="javascript">document.location.href="?<?php echo paramEncrypt('page=../transaksi/kas_masuk')?>"</script><?php
					}
				}
			}

			//untuk mendecode url yang di enrypsi
			$var=strpos($_SERVER['REQUEST_URI'], '?') !== false ? decode($_SERVER['REQUEST_URI']) : array();

			//pecahkan nilai array
			$mode=isset($var['mode']) ? $var['mode'] : null;
			$id_transaksi=isset($var['id_transaksi']) ? $var['id_transaksi'] : null;

			if($mode=='delete'){
				// hanya baris milik user ini pada nomor bukti yang sedang dibuka (versi lama: id apa pun)
				db_jalankan("delete from tabel_transaksi where id_transaksi=? and id_admin=? and kode_transaksi=?", 'sss',
					(string) $id_transaksi, (string) $userid, (string) $kode_transaksi);
			}

$pesan_proses = ob_get_clean();

			//untuk menampilkan transaksi
$baris_transaksi = array();
$tot_kredit = null;
foreach (db_semua("select * from tabel_transaksi where kode_transaksi=? and id_admin=?", 'ss', (string) $kode_transaksi, (string) $userid) as $row) {
    // versi lama membaca angka sebagai teks; perbandingan !== "0" di bawah bergantung pada itu
    $kredit = $row['kredit'] === null ? null : (string) $row['kredit'];
    $tgl_transaksi = $row['tanggal_transaksi'];
    $tot_kredit = compat_num($tot_kredit) + compat_num($kredit);
    $baris_transaksi[] = array(
        'kode_rekening' => $row['kode_rekening'],
        'keterangan_transaksi' => $row['keterangan_transaksi'],
        'kredit' => $kredit,
        'id_transaksi' => $row['id_transaksi'],
    );
}

$aksi_form = '?' . paramEncrypt('page=../transaksi/kas_masuk');
halaman_awal('Jurnal Masuk');
?>
<div class="card card-ni">
  <div class="card-header">Kriteria Input</div>
  <div class="card-body">
    <form action="<?php echo h($aksi_form); ?>" method="post" name="postform" class="form-ni" autocomplete="off">
      <input type="hidden" name="kode_bukti" value="<?php echo h($kode_bukti_form); ?>">
      <div class="row g-2 mb-2">
        <label class="col-sm-4 col-form-label">Nomor Transaksi</label>
        <div class="col-sm-8"><input type="text" class="form-control form-control-sm" disabled value="<?php echo h($kode_bukti_form); ?>"></div>
      </div>
      <div class="row g-2 mb-2">
        <label for="tanggal_transaksi" class="col-sm-4 col-form-label">Tanggal</label>
        <div class="col-sm-8"><input type="date" name="tanggal_transaksi" id="tanggal_transaksi" class="form-control form-control-sm" value="<?php echo h($tanggal_form); ?>" required></div>
      </div>
      <div class="row g-2 mb-2">
        <label for="keterangan_jurnal" class="col-sm-4 col-form-label">Keterangan Jurnal</label>
        <div class="col-sm-8"><input type="text" name="keterangan_jurnal" id="keterangan_jurnal" class="form-control form-control-sm" value="<?php echo h($keterangan_form); ?>" autofocus></div>
      </div>
      <div class="row g-2 mb-2">
        <label for="kode" class="col-sm-4 col-form-label">Rekening</label>
        <div class="col-sm-3 autocomplete">
          <input type="text" name="kode_rekening" id="kode" class="form-control form-control-sm" placeholder="kode / nama rekening" required
                 data-autocomplete="../ajax/cari_coa.php" data-isi-nilai="kode_rek" data-isi-label="kode_rek,nama_rek"
                 data-isi-target='{"#country":"nama_rek"}'>
        </div>
        <div class="col-sm-5"><input type="text" id="country" class="form-control form-control-sm" disabled></div>
      </div>
      <div class="row g-2 mb-2">
        <label for="keterangan_transaksi" class="col-sm-4 col-form-label">Keterangan Transaksi</label>
        <div class="col-sm-8"><input type="text" name="keterangan_transaksi" id="keterangan_transaksi" class="form-control form-control-sm" value="<?php echo h($keterangan_form); ?>"></div>
      </div>
      <div class="row g-2 mb-3">
        <label for="jumlah_dk" class="col-sm-4 col-form-label">Jumlah (Rp)</label>
        <div class="col-sm-4"><input type="text" name="jumlah_dk" id="jumlah_dk" class="form-control form-control-sm text-end" inputmode="numeric" autocomplete="off" required></div>
        <div class="col-sm-4 col-form-label text-muted" id="format"></div>
      </div>
      <div class="text-end">
        <button type="submit" name="simpan" value="Simpan" class="btn btn-ni btn-sm px-4">Simpan</button>
      </div>
    </form>
  </div>
</div>
<?php echo $pesan_proses; ?>

<div class="table-responsive">
  <table class="table table-sm table-bordered table-ni align-middle">
    <thead>
      <tr><th>Kode Rekening</th><th>Keterangan</th><th class="text-end">Kredit</th><th class="text-center">Action</th></tr>
    </thead>
    <tbody>
    <?php foreach ($baris_transaksi as $b): ?>
      <tr>
        <td><?php echo h($b['kode_rekening']); ?></td>
        <td><?php echo h($b['keterangan_transaksi']); ?></td>
        <td class="text-end"><?php if ($b['kredit'] !== "0") { echo number_format(compat_num($b['kredit']), 2, '.', ','); } ?></td>
        <td class="text-center"><a href="?<?php echo h(paramEncrypt('page=../transaksi/kas_masuk&mode=delete&id_transaksi=' . $b['id_transaksi'])); ?>" class="btn btn-outline-danger btn-sm" data-konfirmasi="Apakah Anda yakin?">Batal</a></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
    <tfoot>
      <tr class="fw-bold">
        <td colspan="2" class="text-center">TOTAL</td>
        <td class="text-end"><?php echo number_format(compat_num($tot_kredit), 2, '.', ','); ?></td>
        <td></td>
      </tr>
    </tfoot>
  </table>
</div>

<form action="<?php echo h($aksi_form); ?>" method="post" name="form" class="text-end">
  <input type="hidden" name="tanggal_selesai" value="<?php if (empty($_POST['tanggal_transaksi'])) { echo h($tanggal); } else { echo h($_POST['tanggal_transaksi']); } ?>">
  <input type="hidden" name="kode_bukti" value="<?php echo h($kode_transaksi); ?>">
  <input type="hidden" name="nomor_jurnal" value="<?php echo h($nomor_jurnal); ?>">
  <input type="hidden" name="keterangan_jurnal" value="<?php echo h(isset($keterangan_jurnal) ? $keterangan_jurnal : ''); ?>">
  <input type="hidden" name="tgl_transaksi" value="<?php echo h(isset($tgl_transaksi) ? $tgl_transaksi : ''); ?>">
  <button type="submit" name="selesai" value="Selesai" class="btn btn-ni btn-sm px-4" data-konfirmasi="Apakah Anda Yakin?">Selesai</button>
</form>
<script>
// tampilan Rp. 1.234 di samping isian jumlah (seperti formatCurrency versi lama)
document.getElementById('jumlah_dk').addEventListener('keyup', function () {
    var n = Number(this.value.replace(/[$,]/g, ''));
    if (isNaN(n)) { n = 0; }
    var tanda = n < 0 ? '-' : '';
    var bulat = Math.floor(Math.abs(n) * 100 + 0.50000000001);
    bulat = Math.floor(bulat / 100).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    document.getElementById('format').textContent = tanda + 'Rp. ' + bulat;
});
</script>
<?php
halaman_akhir();
