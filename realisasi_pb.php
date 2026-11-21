<?php require_once __DIR__ . '/koneksi_db.php';
/**
 * PB PerKelompok langkah 3: pemindahbukuan simpanan pokok/wajib <-> simpanan sukarela untuk anggota yang dipilih
 * di pb_list.php. Jenis PB (Debet/Kredit) dan simpanan (pokok/wajib) diambil dari sesi yang diisi pb_kel.php.
 * Logika dan urutan query sama dengan versi lama; hanya query (prepared statement) yang berubah.
 * Perbaikan 2026-10-31 (perintah user), lihat PEKERJAAN_TERTUNDA.md bagian 17:
 *   - nominal diambil per CIF (bulat[CIF]); versi lama memakai isian ke-i untuk anggota tercentang ke-i
 *   - jurnal_masuk.kode_transaksi diisi kode transaksi PB (versi lama kosong)
 *   - tanpa pilihan kembali ke pb_kel.php (versi lama ke form.php yang tidak ada)
 * Perbaikan 2026-11-03 (perintah user): nominal dicek terhadap saldo simpanan yang didebet (tidak boleh minus).
 * Perbaikan 2026-11-20 (perintah user, §17):
 *   - jurnal_masuk.nomor_jurnal dibuat otomatis (auto increment). Dulu max(jurnal_umum unit)+1 yang sama untuk semua
 *     anggota dan bentrok dengan nomor jurnal_masuk yang sudah ada, sehingga baris jurnal_masuk gagal tersimpan.
 *   - per anggota: kunci per CIF (cek saldo + tulis) dan transaksi DB; bila ada query gagal, simpanan/tabel_transaksi
 *     dibatalkan dan baris MyISAM (simpanan_pokok/wajib, jurnal_masuk) dengan kode transaksi PB itu dihapus, lalu
 *     pesan GAGAL (dulu tetap "Suksess").
 *   - cegah klik ganda / kirim ulang: token sekali pakai dari pb_list.php.
 * Perbaikan 2026-11-21 (perintah user, §17): jenis PB dari form pb_list.php (sesi hanya untuk form lama tanpa jenis);
 * jenis di luar Debet/Kredit x pokok/wajib ditolak tanpa proses.
 * Rekening, nominal, TYPE, dan isi data untuk PB yang berhasil tidak berubah.
 */
require_once __DIR__ . '/inc/notif.php';
session_start();
ob_start();
$userid=isset($_SESSION['userid']) ? $_SESSION['userid'] : null;
$tgl_system=isset($_SESSION['stgl_system']) ? $_SESSION['stgl_system'] : null;
$tgl_input=date("Y-m-d");
$buss_date=$tgl_system;
//cek apakah user sudah login
if(!isset($_SESSION['stgl_system'])){
notif_pergi('Maaf, tanggal system belum di setup', 'param_tgl.php', 'warning');

   /* redirect ditangani notif_pergi */
 exit; }
//cek apakah user sudah login
if(!isset($_SESSION['userid'])){
   header("location:index.html");
 exit; }

//cek level user
if($_SESSION['level']!="unit"){
   header("location:index.html");
 exit; }
db_ni();
include "function.php";

/** Jalankan query tulis; semua parameter dikirim sebagai teks (seperti nilai '$var' di query versi lama). */
function qs_pb($sql, ...$nilai)
{
    return db_jalankan($sql, str_repeat('s', count($nilai)), ...array_map(function ($v) {
        return $v === null ? '' : (string) $v;
    }, $nilai));
}

$tanggal=$tgl_system;
// 2026-11-21 (perintah user, §17): jenis PB dari form pb_list.php (daftar yang sedang diisi). Dulu dari sesi = pilihan
// Search TERAKHIR, sehingga Search di tab lain membalik arah PB tab ini. Form lama tanpa jenis -> sesi seperti dulu.
if (isset($_POST['baki']) || isset($_POST['simpan'])) {
	$baki = isset($_POST['baki']) ? (string) $_POST['baki'] : '';
	$simpan = isset($_POST['simpan']) ? (string) $_POST['simpan'] : '';
} else {
	$simpan=isset($_SESSION['simpans']) ? $_SESSION['simpans'] : null;
	$baki=isset($_SESSION['bakis']) ? $_SESSION['bakis'] : null;
}
$nom=isset($_POST['bulat']) ? $_POST['bulat'] : null;

$checbox = isset($_POST['cekbox']) ? $_POST['cekbox'] : null;
// 2026-11-20: token sekali pakai dari pb_list.php; kiriman kedua (klik ganda / muat ulang / tombol kembali) ditolak
require_once __DIR__ . '/inc/csrf.php';
if ($checbox && !token_sekali_pakai('pb_token', isset($_POST['pb_token']) ? $_POST['pb_token'] : '')) {
	notif_pergi('Data PB ini sudah diproses atau halaman sudah kedaluwarsa. Silakan cari kelompok lagi.', 'pb_kel.php', 'warning');
	exit;
}
// jenis PB harus salah satu pilihan menu (Debet/Kredit, pokok/wajib); selain itu tidak ada yang diproses
if ($checbox && !(in_array((string) $baki, array('Debet', 'Kredit'), true) && in_array((string) $simpan, array('pokok', 'wajib'), true))) {
	notif_pergi('Jenis Pemindah Bukuan tidak dikenali, tidak ada data yang diproses. Silakan cari kelompok lagi.', 'pb_kel.php', 'warning');
	exit;
}
if ($checbox) {
	for ($i=0; $i <sizeof($checbox);$i++){
		$kkel = db_satu("select no_anggota,code_kel,cif,unit,Cust_Short_name,cao FROM loan where  cif =?", 's', (string) $checbox[$i]);
		$code_kel = $kkel ? $kkel['code_kel'] : null;
		$cif = $kkel ? $kkel['cif'] : null;
		$ciff = $cif;
		$unit = $kkel ? $kkel['unit'] : null;
		// nominal milik anggota ini (form mengirim bulat[CIF])
		$nominal = isset($nom[(string) $checbox[$i]]) ? $nom[(string) $checbox[$i]] : null;

		$kodetr=7;
		$kodetr=acakangkahuruf($kodetr);
		$kode_trans="BU/".$unit.$kodetr;

		// semantik PHP 5: isian kosong / bukan angka dianggap 0 (dilewati)
		// Perbaikan 2026-11-03 (perintah user): nominal PB tidak boleh negatif dan tidak boleh melebihi saldo simpanan yang
		// didebet (Debet: simpanan pokok/wajib; Kredit: simpanan sukarela, rumus saldo sama dengan menu Penarikan),
		// supaya saldo tidak menjadi minus. Anggota yang tidak lolos dilewati tanpa mencatat apa pun.
		$tabel_sumber = $baki=="Debet" ? ($simpan=="pokok" ? 'simpanan_pokok' : 'simpanan_wajib') : 'simpanan';
		$nama_sumber = $baki=="Debet" ? ($simpan=="pokok" ? 'simpanan pokok' : 'simpanan wajib') : 'simpanan sukarela';
		$saldo_sumber = 0;
		// 2026-11-20: kunci per CIF sejak cek saldo sampai data tersimpan (dua PB bersamaan tidak lolos cek saldo yang sama)
		$kunci_pb = 'nurinsani_pb_' . (string) $ciff;
		$terkunci = false;
		if (compat_num($nominal) > 0) {
			$k = db_satu("SELECT GET_LOCK(?, 15) AS ok", 's', $kunci_pb);
			$terkunci = $k && (int) $k['ok'] === 1;
		}
		if (compat_num($nominal) > 0 && !$terkunci) {
			notif_pesan("Pemindah Bukuan an $cif GAGAL: data anggota sedang diproses, coba lagi");
			continue;
		}
		if (compat_num($nominal) > 0) {
			$s = $tabel_sumber == 'simpanan'
				? db_satu("select sum(KREDIT)- sum(DEBIT) as saldo FROM simpanan where cif =? and type not in ('04','05')", 's', (string) $ciff)
				: db_satu("select SUM(KREDIT-DEBIT) as saldo from $tabel_sumber where cif =?", 's', (string) $ciff);
			$saldo_sumber = compat_num($s ? $s['saldo'] : null);
		}

		if (compat_num($nominal)==0){
			$data=0;
		}elseif (compat_num($nominal) < 0){
			notif_pesan("Pemindah Bukuan an $cif GAGAL: nominal tidak boleh minus");
		}elseif (compat_num($nominal) > $saldo_sumber){
			notif_pesan("Pemindah Bukuan an $cif GAGAL: nominal " . number_format(compat_num($nominal)) . " melebihi saldo $nama_sumber " . number_format($saldo_sumber));
		}else{
			$cao = $kkel ? $kkel['cao'] : null;
			$norek = $kkel ? $kkel['no_anggota'] : null;

			// 2026-11-20: nomor jurnal_masuk otomatis (dulu max(jurnal_umum unit)+1, bentrok -> baris gagal tersimpan)
			mysqli_begin_transaction(db_ni());
			$gagal_awal = db_jumlah_gagal();

			$catat = function ($tabel, $debit, $type, $kredit, $keterangan) use ($buss_date, $norek, $unit, $ciff, $code_kel, $userid, $cao, $kode_trans) {
				$tabel = in_array($tabel, array('simpanan', 'simpanan_pokok', 'simpanan_wajib'), true) ? $tabel : 'simpanan';
				qs_pb("Insert into $tabel(BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,kode_transaksi)VALUES(?,?,?,?,?,?,?,?,?,?,'',?,?)",
					$buss_date, $norek, $unit, $ciff, $code_kel, $debit, $type, $kredit, $userid, $keterangan, $cao, $kode_trans);
			};
			$jurnal = function ($rek_debet, $rek_kredit, $keterangan) use ($tgl_system, $unit, $kode_trans, $nominal, $userid) {
				// versi lama: kode_transaksi kosong (variabel $kode_transaksi tidak pernah diisi)
				qs_pb("insert into jurnal_masuk(kode_transaksi,tanggal_selesai,unit) values(?,?,?)", $kode_trans, $tgl_system, $unit);
				qs_pb("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
					values(?,?,?,?,'Bukti SYSTEM',?,?,?)", $unit, $kode_trans, $rek_debet, $tgl_system, $keterangan, $nominal, $userid);
				qs_pb("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
					values(?,?,?,?,'Bukti SYSTEM',?,?,?)", $unit, $kode_trans, $rek_kredit, $tgl_system, $keterangan, $nominal, $userid);
			};

			if ($baki=="Debet"){
				// simpanan pokok/wajib didebet, simpanan sukarela dikredit
				if($simpan=="pokok"){
					$gl='3102000';
					$keterangan="PB Debet Pokok $cif";
					$catat('simpanan_pokok', $nominal, '04', '0', $keterangan);
					$catat('simpanan', '0', '01', $nominal, $keterangan);
				}else{
					$gl='3202000';
					$keterangan="PB Wajib  $cif";
					$catat('simpanan_wajib', $nominal, '05', '0', $keterangan);
					$catat('simpanan', '0', '01', $nominal, $keterangan);
				}
				$jurnal($gl, '2101000', $keterangan);
			}else{
				// simpanan sukarela didebet, simpanan pokok/wajib dikredit
				if($simpan=="pokok"){
					$gl='3102000';
					$keterangan="PB  Pokok $cif";
					$catat('simpanan_pokok', '0', '04', $nominal, $keterangan);
					$catat('simpanan', $nominal, '01', '0', $keterangan);
				}else{
					$gl='3202000';
					$keterangan="PB Wajib  $cif";
					$catat('simpanan_wajib', '0', '04', $nominal, $keterangan);
					$catat('simpanan', $nominal, '01', '0', $keterangan);
				}
				$jurnal('2101000', $gl, $keterangan);
			}
			if (db_jumlah_gagal() > $gagal_awal) {
				// simpanan & tabel_transaksi (InnoDB) dibatalkan; baris MyISAM PB ini dihapus lewat kode transaksinya
				mysqli_rollback(db_ni());
				foreach (array('simpanan_pokok', 'simpanan_wajib', 'jurnal_masuk') as $t_myisam) {
					db_jalankan("DELETE FROM $t_myisam WHERE kode_transaksi = ?", 's', (string) $kode_trans);
				}
				notif_pesan("Pemindah Bukuan an $cif GAGAL, data tidak disimpan");
			} else {
				mysqli_commit(db_ni());
				notif_pesan("Pemindah Bukuan an ".addslashes((string) $cif)." Suksess");
			}
		}
		if ($terkunci) {
			db_satu("SELECT RELEASE_LOCK(?) AS ok", 's', $kunci_pb);
		}
	}

	echo "<script language='javascript'>document.location.href='pb_kel.php'</script>";

}else {
			notif_pergi('anda belum memilih data.', 'pb_kel.php');
	}

?>
