<?php require_once __DIR__ . '/koneksi_db.php';
/**
 * PB PerKelompok langkah 3: pemindahbukuan simpanan pokok/wajib <-> simpanan sukarela untuk anggota yang dipilih
 * di pb_list.php. Jenis PB (Debet/Kredit) dan simpanan (pokok/wajib) diambil dari sesi yang diisi pb_kel.php.
 * Logika dan urutan query sama dengan versi lama; hanya query (prepared statement) yang berubah.
 * Perbaikan 2026-10-31 (perintah user), lihat PEKERJAAN_TERTUNDA.md bagian 17:
 *   - nominal diambil per CIF (bulat[CIF]); versi lama memakai isian ke-i untuk anggota tercentang ke-i
 *   - jurnal_masuk.kode_transaksi diisi kode transaksi PB (versi lama kosong)
 *   - tanpa pilihan kembali ke pb_kel.php (versi lama ke form.php yang tidak ada)
 */
session_start();
ob_start();
$userid=isset($_SESSION['userid']) ? $_SESSION['userid'] : null;
$tgl_system=isset($_SESSION['stgl_system']) ? $_SESSION['stgl_system'] : null;
$tgl_input=date("Y-m-d");
$buss_date=$tgl_system;
//cek apakah user sudah login
if(!isset($_SESSION['stgl_system'])){
echo "<script>alert('Maaf,.tanggal system belum di setup');</script>";

   header("location:param_tgl.php");
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
$simpan=isset($_SESSION['simpans']) ? $_SESSION['simpans'] : null;
$baki=isset($_SESSION['bakis']) ? $_SESSION['bakis'] : null;
$nom=isset($_POST['bulat']) ? $_POST['bulat'] : null;

$checbox = isset($_POST['cekbox']) ? $_POST['cekbox'] : null;
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
		if (compat_num($nominal)==0){
			$data=0;
		}else{
			$cao = $kkel ? $kkel['cao'] : null;
			$norek = $kkel ? $kkel['no_anggota'] : null;

			$j = db_satu("SELECT max(nomor_jurnal) AS n FROM jurnal_umum where unit =? ORDER BY tanggal_selesai DESC", 's', (string) $unit);
			$nomor_jurnal = compat_num($j ? $j['n'] : null) + 1;

			$catat = function ($tabel, $debit, $type, $kredit, $keterangan) use ($buss_date, $norek, $unit, $ciff, $code_kel, $userid, $cao, $kode_trans) {
				$tabel = in_array($tabel, array('simpanan', 'simpanan_pokok', 'simpanan_wajib'), true) ? $tabel : 'simpanan';
				qs_pb("Insert into $tabel(BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,kode_transaksi)VALUES(?,?,?,?,?,?,?,?,?,?,'',?,?)",
					$buss_date, $norek, $unit, $ciff, $code_kel, $debit, $type, $kredit, $userid, $keterangan, $cao, $kode_trans);
			};
			$jurnal = function ($rek_debet, $rek_kredit, $keterangan) use ($nomor_jurnal, $tgl_system, $unit, $kode_trans, $nominal, $userid) {
				// versi lama: kode_transaksi kosong (variabel $kode_transaksi tidak pernah diisi)
				qs_pb("insert into jurnal_masuk(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values(?,?,?,?)", $nomor_jurnal, $kode_trans, $tgl_system, $unit);
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
			echo "<script>alert('Pemindah Bukuan an ".addslashes((string) $cif)." Suksess');</script>";
		}
	}

	echo "<script language='javascript'>document.location.href='pb_kel.php'</script>";

}else {
			echo '<script>alert("anda belum memilih data."); window.location="pb_kel.php"</script>';
	}

?>
