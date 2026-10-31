<?php require_once __DIR__ . '/../koneksi_db.php';
/**
 * Menu Transaksi > Jurnal Keluar (kas keluar).
 *   Simpan  : catat sisi debet. Jenis "Stor Ke KP" (01) juga mencatat pasangan jurnal di unit 1010 (KP) dan rekening 2500000.
 *   Selesai : catat sisi kredit kas 1120000 dan nomor jurnal_keluar.
 *   Batal   : hapus satu baris transaksi (link terenkripsi).
 * Logika dan urutan proses sama dengan versi lama; hanya tampilan dan query (prepared statement) yang berubah.
 * Versi lama tidak punya cek login; sekarang wajib login.
 * Perbaikan 2026-10-31 (perintah user), lihat PEKERJAAN_TERTUNDA.md bagian 18:
 *   - Batal hanya menghapus baris milik user ini pada nomor bukti yang sedang dibuka
 *   - kredit kas saat Selesai = saldo debet - kredit baris unit sendiri di DB (bukan dari field tersembunyi)
 *   - Selesai ditolak bila belum ada transaksi atau sudah selesai
 *   - Simpan memeriksa rekening, jenis transaksi, tanggal, dan jumlah
 */
require_once __DIR__ . '/../inc/tampilan.php';
session_start();
require_once __DIR__ . '/../inc/cek_login.php';
wajib_login();
$userid=$_SESSION['userid'];
	include "../encryption/function.php";
	//untuk waktu
	$tanggal=isset($_SESSION['stgl_system']) ? $_SESSION['stgl_system'] : null;

$unit = null;
$kode_rek = null;
foreach (db_semua("select kode_branch,GL from branch where kode_branch=?", 's', (string) $userid) as $id_user) {
    $unit=$id_user['kode_branch'];
    $kode_rek=$id_user['GL'];
}

			//jurnal baru. cari nomor paling besar yaitu nomor jurnal terakhir
			$j = db_satu("SELECT max(nomor_jurnal) AS n FROM jurnal_keluar where unit=? ORDER BY tanggal_selesai DESC", 's', (string) $unit);
			$nomor_jurnal=compat_num($j ? $j['n'] : null)+1;
			$kode_transaksi="KK/".$unit .$nomor_jurnal;
// nomor bukti yang tampil di form dihitung sebelum proses simpan (sama seperti versi lama)
$kode_bukti_form = $kode_transaksi;
$tanggal_form = empty($_POST['tanggal_transaksi']) ? $tanggal : $_POST['tanggal_transaksi'];
$keterangan_form = isset($_POST['keterangan_jurnal']) ? $_POST['keterangan_jurnal'] : '';

/** Insert satu baris tabel_transaksi (sisi debet atau kredit); semua nilai dikirim sebagai teks seperti versi lama. */
function catat_kas_keluar($unit, $kode_transaksi, $kode_rekening, $tanggal, $keterangan, $sisi, $jumlah, $userid)
{
    $kolom = $sisi === 'debet' ? 'debet' : 'kredit';
    return db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,$kolom,id_admin)
                        values(?,?,?,?,'Kas Keluar',?,?,?)", 'sssssss', (string) $unit, (string) $kode_transaksi, (string) $kode_rekening,
        (string) $tanggal, (string) $keterangan, (string) $jumlah, (string) $userid);
}

// rekening tujuan dan jenis transaksi yang boleh dipilih (sama dengan pilihan di form)
$rekening_tujuan = array('1311000' => 'BSI', '1332000' => 'Mandiri Pedurungan', '1333000' => 'BRI', '1335000' => 'Mandiri',
    '1963000' => 'Selisih Kas Kurang', '2472000' => 'Selisih Kas Lebih');
$jenis_transaksi = array('01' => 'Stor Ke KP', '02' => 'Lainnya');

// pesan/script dari proses ditampilkan di bawah form, seperti versi lama
ob_start();

			//untuk menyimpan transaksi
			if(isset($_POST['simpan'])){

				$kode_transaksi=isset($_POST['kode_bukti']) ? $_POST['kode_bukti'] : null;
				$tanggal_transaksi=isset($_POST['tanggal_transaksi']) ? $_POST['tanggal_transaksi'] : null;
				$kode_rekening=isset($_POST['kode_rekening']) ? $_POST['kode_rekening'] : null;
				$select=isset($_POST['select']) ? $_POST['select'] : null;
				$keterangan_jurnal=isset($_POST['keterangan_jurnal']) ? $_POST['keterangan_jurnal'] : null;

				//untuk jurnal kas keluar, semua transaksi ada sisi DEBET
				$jumlah_dk=ucwords((string) (isset($_POST['jumlah_dk']) ? $_POST['jumlah_dk'] : ''));
				$salah = array();
				if (!isset($rekening_tujuan[(string) $kode_rekening])) { $salah[] = 'Rekening Tujuan belum dipilih'; }
				if (!isset($jenis_transaksi[(string) $select])) { $salah[] = 'Jenis Transaksi belum dipilih'; }
				if (trim((string) $tanggal_transaksi) === '') { $salah[] = 'Tanggal belum diisi'; }
				if (!is_numeric($jumlah_dk) || $jumlah_dk <= 0) { $salah[] = 'Jumlah harus angka lebih dari 0'; }
				if ($salah) {
					$query = false;
					echo "<script>alert(" . json_encode(implode("\n", $salah) . "\nTransaksi tidak disimpan.") . ");</script>";
				}elseif ($select=='01'){
					// setoran ke KP: pasangan jurnal di unit 1010 (rekening tujuan vs GL unit), lalu debet 2500000 di unit sendiri
					$query=catat_kas_keluar('1010', $kode_transaksi, "1010-$kode_rekening", $tanggal_transaksi, $keterangan_jurnal, 'debet', $jumlah_dk, $userid);
					$query=catat_kas_keluar('1010', $kode_transaksi, "1010-$kode_rek", $tanggal_transaksi, $keterangan_jurnal, 'kredit', $jumlah_dk, $userid);
					$query=catat_kas_keluar($unit, $kode_transaksi, '2500000', $tanggal_transaksi, $keterangan_jurnal, 'debet', $jumlah_dk, $userid);
				}else{
					$query=catat_kas_keluar($unit, $kode_transaksi, $kode_rekening, $tanggal_transaksi, $keterangan_jurnal, 'debet', $jumlah_dk, $userid);
				}
				// (versi lama mencetak mysql_error() bila gagal; kegagalan kini dicatat di log PHP oleh db_jalankan)
			}

			//untuk menyelesaikan transaksi
			if(isset($_POST['selesai'])){

				$kode_transaksi=isset($_POST['kode_bukti']) ? $_POST['kode_bukti'] : null;
				$nomor_jurnal=isset($_POST['nomor_jurnal']) ? $_POST['nomor_jurnal'] : null;
				$tanggal_selesai=isset($_POST['tanggal_selesai']) ? $_POST['tanggal_selesai'] : null;
				$keterangan_jurnal=isset($_POST['keterangan_jurnal']) ? $_POST['keterangan_jurnal'] : null;
				//karena kas keluar lawan transaksinya adalah kas, maka kas adalah 1120000 yang ada di sisi KREDIT.
				$kode_rekening="1120000";
				// kredit kas = saldo baris unit sendiri pada nomor bukti ini (Lainnya: rekening tujuan, Stor Ke KP: 2500000);
				// versi lama memakai field tersembunyi yang bisa kosong/dobel setelah Batal atau muat ulang
				$saldo = db_satu("select sum(debet)-sum(kredit) as saldo, count(*) as n from tabel_transaksi where kode_transaksi=? and id_admin=? and unit=?", 'sss',
					(string) $kode_transaksi, (string) $userid, (string) $unit);
				$kredit_kas = compat_num($saldo ? $saldo['saldo'] : null);
				$sudah = db_satu("select count(*) as n from jurnal_keluar where kode_transaksi=?", 's', (string) $kode_transaksi);
				if ($sudah && $sudah['n'] > 0) {
					echo "<script>alert('Nomor bukti " . h($kode_transaksi) . " sudah diselesaikan.');</script>";
				} elseif ($kredit_kas <= 0) {
					echo "<script>alert('Belum ada transaksi yang perlu diselesaikan.');</script>";
				} else {
					if (trim((string) $keterangan_jurnal) === '') {
						// keterangan diambil dari baris terakhir bila field tersembunyi kosong (mis. setelah Batal)
						$akhir = db_satu("select keterangan_transaksi from tabel_transaksi where kode_transaksi=? and id_admin=? and unit=? order by id_transaksi desc limit 1", 'sss',
							(string) $kode_transaksi, (string) $userid, (string) $unit);
						$keterangan_jurnal = $akhir ? $akhir['keterangan_transaksi'] : $keterangan_jurnal;
					}
					$query_transaksi=catat_kas_keluar($unit, $kode_transaksi, $kode_rekening, $tanggal_selesai, $keterangan_jurnal, 'kredit', $kredit_kas, $userid);
					// nomor_jurnal '' -> nomor otomatis (auto increment) seperti versi lama
					db_jalankan("insert into jurnal_keluar(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values('',?,?,?)", 'sss',
						(string) $kode_transaksi, (string) $tanggal_selesai, (string) $unit);

					if($query_transaksi){
						?><script language="javascript">document.location.href="?<?php echo paramEncrypt('page=../transaksi/kas_keluar')?>"</script><?php
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
$tot_debet = null;
foreach (db_semua("select * from tabel_transaksi where kode_transaksi=? and id_admin=?", 'ss', (string) $kode_transaksi, (string) $userid) as $row) {
    // versi lama membaca angka sebagai teks; perbandingan !== "0" di bawah bergantung pada itu
    $debet = $row['debet'] === null ? null : (string) $row['debet'];
    $cr = $row['kredit'] === null ? null : (string) $row['kredit'];
    $tot_debet = compat_num($tot_debet) + compat_num($debet);
    $baris_transaksi[] = array(
        'kode_rekening' => $row['kode_rekening'],
        'keterangan_transaksi' => $row['keterangan_transaksi'],
        'debet' => $debet,
        'kredit' => $cr,
        'id_transaksi' => $row['id_transaksi'],
    );
}

$aksi_form = '?' . paramEncrypt('page=../transaksi/kas_keluar');
halaman_awal('Jurnal Keluar');
?>
<div class="card card-ni">
  <div class="card-header">Kriteria Input</div>
  <div class="card-body">
    <form action="<?php echo h($aksi_form); ?>" method="post" name="postform" class="form-ni" autocomplete="off">
      <input type="hidden" name="kode_bukti" value="<?php echo h($kode_bukti_form); ?>">
      <div class="row g-2 mb-2">
        <label class="col-sm-4 col-form-label">Nomor Bukti</label>
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
        <label for="kode" class="col-sm-4 col-form-label">Rekening Tujuan</label>
        <div class="col-sm-8">
          <select name="kode_rekening" id="kode" class="form-select form-select-sm" required>
            <option value="">Pilih Salah satu</option>
            <?php foreach ($rekening_tujuan as $kode_rek_tujuan => $nama_rek_tujuan): ?>
            <option value="<?php echo h($kode_rek_tujuan); ?>"><?php echo h($nama_rek_tujuan); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
      <div class="row g-2 mb-2">
        <label for="select" class="col-sm-4 col-form-label">Jenis Transaksi</label>
        <div class="col-sm-8">
          <select name="select" id="select" class="form-select form-select-sm" required>
            <option value="">Pilih Salah satu</option>
            <?php foreach ($jenis_transaksi as $kode_jenis => $nama_jenis): ?>
            <option value="<?php echo h($kode_jenis); ?>"><?php echo h($nama_jenis); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
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
      <tr><th>Kode Rekening</th><th>Keterangan</th><th class="text-end">Debet</th><th class="text-end">Kredit</th><th class="text-center">Action</th></tr>
    </thead>
    <tbody>
    <?php foreach ($baris_transaksi as $b): ?>
      <tr>
        <td><?php echo h($b['kode_rekening']); ?></td>
        <td><?php echo h($b['keterangan_transaksi']); ?></td>
        <td class="text-end"><?php if ($b['debet'] !== "0") { echo number_format(compat_num($b['debet']), 2, '.', ','); } ?></td>
        <td class="text-end"><?php if ($b['kredit'] !== "0") { echo number_format(compat_num($b['kredit']), 2, '.', ','); } ?></td>
        <td class="text-center"><a href="?<?php echo h(paramEncrypt('page=../transaksi/kas_keluar&mode=delete&id_transaksi=' . $b['id_transaksi'])); ?>" class="btn btn-outline-danger btn-sm" data-konfirmasi="Apakah Anda yakin?">Batal</a></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
    <tfoot>
      <tr class="fw-bold">
        <?php // versi lama: label TOTAL mencakup 3 kolom sehingga total debet tampil di kolom Kredit ?>
        <td colspan="3" class="text-center">TOTAL</td>
        <td class="text-end"><?php echo number_format(compat_num($tot_debet), 2, '.', ','); ?></td>
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
