<?php require_once __DIR__ . '/../koneksi_db.php';
/**
 * Menu Transaksi > Posting jurnal.
 *   Daftar  : transaksi unit user pada tanggal sistem yang belum diposting, dikelompokkan per rekening.
 *   Posting : tambah mutasi debet/kredit ke tabel_master, hitung ulang sisa debet/kredit per rekening unit,
 *             lalu tandai transaksi 'Post'.
 * Logika dan urutan proses sama dengan versi lama; hanya tampilan dan query (prepared statement) yang berubah.
 * Perbaikan 2026-10-31 (perintah user), lihat PEKERJAAN_TERTUNDA.md bagian 23:
 *   - posting ditolak seluruhnya bila ada kode rekening yang belum ada di COA atau di tabel_master unit
 *   - transaksi ditandai Post satu per satu dan mutasi hanya ditambahkan untuk yang berhasil ditandai di proses itu
 *     (klik ganda / proses bersamaan tidak menggandakan mutasi); tombol POSTING juga dikunci sesudah diklik
 */
require_once __DIR__ . '/../inc/tampilan.php';
session_start();
$userid=isset($_SESSION['userid']) ? $_SESSION['userid'] : null;
if(!isset($_SESSION['userid'])){
   header("location:../index.html");
 exit; }
$tgl_system=isset($_SESSION['stgl_system']) ? $_SESSION['stgl_system'] : null;
//cek apakah user sudah login
if(!isset($_SESSION['stgl_system'])){
echo "<script>alert('Maaf,.tanggal system belum di setup');</script>";

   header("location:../param_tgl.php");
 exit; }

//cek level user
if($_SESSION['level']!="unit"){
   header("location:../index.html");
 exit; }

	include "../encryption/function.php";
	include"../indo_tgl.php";

	//untuk waktu (tanggal_posting, format lama yy-mm-dd)
	$tanggal=date("y-m-d");

$unitt = null;
foreach (db_semua("select kode_branch from user where userid=?", 's', (string) $userid) as $id_user) {
    $unitt=$id_user['kode_branch'];
}

// daftar dan total dibaca sebelum proses posting (versi lama menampilkan daftar dulu, baru memproses)
$baris_transaksi = db_semua("select tanggal_transaksi,tabel_transaksi.kode_rekening,sum(debet) as debet,sum(kredit) as kredit,nama_rek,keterangan_transaksi from tabel_transaksi inner join coa on coa.kode_rek = tabel_transaksi.kode_rekening  where  keterangan_posting='' and tabel_transaksi.unit =? and tanggal_transaksi=?  Group By kode_rekening",
    'ss', (string) $unitt, (string) $tgl_system);
$total = db_satu("select sum(debet) as tot_debet, sum(kredit) as tot_kredit from tabel_transaksi where  keterangan_posting='' and unit =? and tanggal_transaksi=? order by kode_rekening  asc",
    'ss', (string) $unitt, (string) $tgl_system);
$cek = db_satu("select count(*) as n from tabel_transaksi where keterangan_posting='' and unit =? and tanggal_transaksi=?", 'ss', (string) $unitt, (string) $tgl_system);
$cek_posting = $cek ? (int) $cek['n'] : 0;

//untuk mendecode url yang di enrypsi (pesan status sesudah posting)
$var=strpos($_SERVER['REQUEST_URI'], '?') !== false ? decode($_SERVER['REQUEST_URI']) : array();
$status = isset($var['status']) ? $var['status'] : null;

ob_start();
	if(isset($_POST['posting'])){

		$antrian = db_semua("select id_transaksi,kode_rekening,debet,kredit,unit from tabel_transaksi where keterangan_posting='' and unit =? and tanggal_transaksi=?", 'ss', (string) $unitt, (string) $tgl_system);

		// cek rekening dulu: kode yang tidak ada di COA atau di tabel_master unit membuat posting ditolak seluruhnya
		// (versi lama tetap menandai Post, sehingga mutasinya hilang)
		$kurang = array();
		$sudah_dicek = array();
		foreach ($antrian as $a) {
			$kunci = $a['unit'] . '|' . $a['kode_rekening'];
			if (isset($sudah_dicek[$kunci])) {
				continue;
			}
			$sudah_dicek[$kunci] = true;
			if (!db_satu("select kode_rek from coa where kode_rek=?", 's', (string) $a['kode_rekening'])) {
				$kurang[] = "Rekening " . $a['kode_rekening'] . " belum ada di COA";
			}
			if (!db_satu("select kode_rekening from tabel_master where kode_rekening=? and unit=?", 'ss', (string) $a['kode_rekening'], (string) $a['unit'])) {
				$kurang[] = "Rekening " . $a['kode_rekening'] . " belum ada di tabel_master unit " . $a['unit'];
			}
		}
	if ($kurang) {
		echo "<script>alert(" . json_encode("Posting dibatalkan, tidak ada transaksi yang diposting.\n\n" . implode("\n", $kurang)
			. "\n\nLengkapi rekening tersebut lalu ulangi posting.") . ");</script>";
	} else {

		///////////////////////// HITUNG MUTASI /////////////////////
		// tiap transaksi ditandai Post satu per satu; mutasi hanya ditambahkan bila penandaan itu berhasil di proses ini,
		// sehingga klik ganda / proses bersamaan tidak menambah mutasi dua kali
		$tandai = db_ni()->prepare("update tabel_transaksi set tanggal_posting=?, keterangan_posting='Post' where id_transaksi=? and keterangan_posting=''");
		foreach ($antrian as $row_hit_mut) {
			$id = (string) $row_hit_mut['id_transaksi'];
			$tgl_post = (string) $tanggal;
			$tandai->bind_param('ss', $tgl_post, $id);
			if (!$tandai->execute() || $tandai->affected_rows !== 1) {
				continue;
			}
			db_jalankan("update tabel_master set mut_debet=mut_debet+?, mut_kredit=mut_kredit+? where kode_rekening=? and unit =?", 'ssss',
				(string) $row_hit_mut['debet'], (string) $row_hit_mut['kredit'], (string) $row_hit_mut['kode_rekening'], (string) $row_hit_mut['unit']);
		}

		///////////////////////// HITUNG SISA /////////////////////
		foreach (db_semua("select kode_rekening,normal,awal_debet,awal_kredit,mut_debet,mut_kredit from tabel_master where unit =?", 's', (string) $unitt) as $row_hit_sisa) {
			$normal=$row_hit_sisa['normal'];
			$kode_rekening=$row_hit_sisa['kode_rekening'];
			$awal_debet=compat_num($row_hit_sisa['awal_debet']);
			$awal_kredit=compat_num($row_hit_sisa['awal_kredit']);
			$mutasi_debet=compat_num($row_hit_sisa['mut_debet']);
			$mutasi_kredit=compat_num($row_hit_sisa['mut_kredit']);

			if($normal=="debet"){
				$hitung_sisa_debet=($awal_debet+$mutasi_debet)-$mutasi_kredit;
				if($hitung_sisa_debet<0){
					db_jalankan("update tabel_master set sisa_debet=0, sisa_kredit=? where kode_rekening=? and unit =?", 'sss',
						(string) abs($hitung_sisa_debet), (string) $kode_rekening, (string) $unitt);
				}else{
					db_jalankan("update tabel_master set sisa_debet=?, sisa_kredit='0' where kode_rekening=? and unit =?", 'sss',
						(string) $hitung_sisa_debet, (string) $kode_rekening, (string) $unitt);
				}
			}

			if($normal=="kredit"){
				$hitung_sisa_kredit=($awal_kredit-$mutasi_debet)+$mutasi_kredit;
				if($hitung_sisa_kredit<0){
					db_jalankan("update tabel_master set sisa_debet=?, sisa_kredit='0' where kode_rekening=? and unit =?", 'sss',
						(string) abs($hitung_sisa_kredit), (string) $kode_rekening, (string) $unitt);
				}else{
					db_jalankan("update tabel_master set sisa_debet=0, sisa_kredit=? where kode_rekening=? and unit =?", 'sss',
						(string) $hitung_sisa_kredit, (string) $kode_rekening, (string) $unitt);
				}
			}
		}

		////////////////////////// STATUS POSTING //////////////////////////////
		// (status Post sudah diubah per transaksi di atas)
		?><script language="javascript">document.location.href="?<?php echo paramEncrypt('page=./transaksi/posting&status=Proses Posting Selesai')?>"</script><?php
	}
	}
$pesan_proses = ob_get_clean();

halaman_awal('Proses Posting Jurnal');
?>
<div class="table-responsive">
  <table class="table table-sm table-bordered table-ni align-middle">
    <thead>
      <tr><th>Tanggal</th><th class="text-center">Kode Rekening</th><th>Nama Rekening</th><th>Keterangan</th><th class="text-end">Debet</th><th class="text-end">Kredit</th></tr>
    </thead>
    <tbody>
    <?php foreach ($baris_transaksi as $row_tran): ?>
      <tr>
        <td><?php echo h(tgl_indo($row_tran['tanggal_transaksi'])); ?></td>
        <td class="text-center"><?php echo h($row_tran['kode_rekening']); ?></td>
        <td><?php echo h($row_tran['nama_rek']); ?></td>
        <td><?php echo h($row_tran['keterangan_transaksi']); ?></td>
        <td class="text-end"><?php echo number_format(compat_num($row_tran['debet']), 2, '.', ','); ?></td>
        <td class="text-end"><?php echo number_format(compat_num($row_tran['kredit']), 2, '.', ','); ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
    <tfoot>
      <tr class="fw-bold">
        <td colspan="4" class="text-center">TOTAL TRANSAKSI</td>
        <td class="text-end"><?php echo number_format(compat_num($total ? $total['tot_debet'] : null)); ?></td>
        <td class="text-end"><?php echo number_format(compat_num($total ? $total['tot_kredit'] : null)); ?></td>
      </tr>
    </tfoot>
  </table>
</div>

<div class="text-center">
  <?php //untuk mengakhiri posting dan memberi tanda posting ?>
  <?php if ($cek_posting !== 0): ?>
  <form action="?<?php echo h(paramEncrypt('page=../transaksi/posting')); ?>" method="post" name="postform" class="mb-2" id="form-posting">
    <button type="submit" name="posting" value="POSTING JURNAL" class="btn btn-ni px-4" data-konfirmasi="Apakah Anda Yakin?">POSTING JURNAL</button>
  </form>
  <?php endif; ?>
  <?php if ($status !== null && $status !== ''): ?>
  <div class="text-muted"><?php echo h($status); ?></div>
  <?php endif; ?>
</div>
<?php echo $pesan_proses; ?>
<script>
// cegah klik ganda: form hanya terkirim sekali, tombol dikunci sesudah terkirim
(function () {
    var form = document.getElementById('form-posting');
    if (!form) { return; }
    var terkirim = false;
    form.addEventListener('submit', function (e) {
        if (terkirim) { e.preventDefault(); return; }
        terkirim = true;
        var tombol = form.querySelector('button[name="posting"]');
        // dikunci sesudah submit berjalan agar nilai tombol tetap ikut terkirim
        setTimeout(function () { tombol.disabled = true; tombol.textContent = 'Memproses…'; }, 0);
    });
})();
</script>
<?php
halaman_akhir();
