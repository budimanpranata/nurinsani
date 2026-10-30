<?php require_once __DIR__ . '/koneksi_db.php';
/**
 * Batal Wakalah / Musyarakah: proses anggota yang dipilih di batal_waka.php.
 * Logika dan urutan proses sama dengan versi lama; hanya query (prepared statement) yang berubah.
 */
date_default_timezone_set('Asia/Jakarta');
session_start();
require_once __DIR__ . '/inc/cek_login.php';
wajib_login();
ob_start();
	$userid=$_SESSION['userid'];
	$tanggal=date("Y-m-d");
$tgl_system=$_SESSION['stgl_system'];

include "function.php";



$cekbox = isset($_POST['cekbox']) ? $_POST['cekbox'] : null;
if ($cekbox) {
       foreach ($cekbox as $value) {

		     $row = db_satu("select DATE_ADD(tgl_wakalah,INTERVAL 7 DAY) as tgl_murab,calon_loan.buss_date,calon_loan.code_kel,no_anggota,calon_loan.Cust_Short_name, status_app,
			calon_loan.Deal_type,suffix,bagi_hasil,Tenor,Plafond,os,angsuran,pokok,ijaroh,bulat,ke,usaha,calon_loan.unit,tgl_wakalah,hari,calon_loan.cao,calon_loan.USERID,calon_loan.status,calon_loan.cif
			 from calon_loan
			 inner join anggota on anggota.cif = calon_loan.cif
			 WHERE calon_loan.cif =?", 's', (string) $value);
			if (!$row) die(); // versi lama berhenti (or die) bila data tidak ada
			$no_anggota=$row['no_anggota'];
			$tgl_wakalah= $row['tgl_wakalah'];
			$cif=$row['cif'];
			$Plafond=$row['Plafond'];
			$bagi_hasil=$row['bagi_hasil'];
			$piutang=compat_num($Plafond) + compat_num($bagi_hasil);
			$tgl_murab=$row['tgl_murab'];
			$buss_date=$row['buss_date'];
			$code_kel=$row['code_kel'];
			$Cust_Short_name=$row['Cust_Short_name'];
			$Deal_type=$row['Deal_type'];
			$suffix=$row['suffix'];
			$Tenor=$row['Tenor'];
			$os=$row['os'];
			$angsuran=$row['angsuran'];
			$pokok=$row['pokok'];
			$ijaroh=$row['ijaroh'];
			$bulat=$row['bulat'];
			$ke=$row['ke'];
			$ke=((int)$suffix)+1;
			$usaha=$row['usaha'];
			$unit=$row['unit'];
			$hari=$row['hari'];
			$cao=$row['cao'];
			$status_app=$row['status_app'];
			$USERID=$row['USERID'];
			$status='ANGGOTA';
			$norek="$unit$no_anggota";
			$kodet=5;
			$kodet=acakangkahuruf($kodet);
			$kode_tran="BU/".$unit.$kodet;
			db_jalankan("delete from `barang` WHERE id_barang =?", 's', (string) $no_anggota) or die(mysqli_error(db_ni()));
			// perintah user 2026-10-30: update berdasarkan CIF (versi lama keliru membandingkan no_anggota dengan CIF)
			db_jalankan("update loan set status='CALON' WHERE cif =?", 's', (string) $cif) or die(mysqli_error(db_ni()));
			$keterangan_transaksi="Realisasi Pembatalan Wakalah An $Cust_Short_name";
$keterangan_transaksi1="Realisasi Pembatalan MUSYARAKAH An $Cust_Short_name";

			// $nomor_jurnal tidak pernah diisi (versi lama), tersimpan sebagai string kosong
			db_jalankan("insert into jurnal_umum(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values('',?,?,?)", 'sss',
				(string) $kode_tran, (string) $tgl_system, (string) $unit);

if($status_app =='MUSYAROKAH'){
			db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
			values(?,?,'2500000',?,'Bukti SYSTEM',?,?,?)", 'ssssss',
				(string) $unit, (string) $kode_tran, (string) $tgl_system, (string) $keterangan_transaksi1, (string) $Plafond, (string) $userid);

			db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
			values(?,?,'1120000',?,'Bukti SYSTEM',?,?,?)", 'ssssss',
				(string) $unit, (string) $kode_tran, (string) $tgl_system, (string) $keterangan_transaksi1, (string) $Plafond, (string) $userid);
			db_jalankan("update `calon_loan` set status_app='BATAL_MUS' WHERE cif =?", 's', (string) $cif) or die(mysqli_error(db_ni()));


}else{

			db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
			values(?,?,'1120000',?,'Bukti SYSTEM',?,?,?)", 'ssssss',
				(string) $unit, (string) $kode_tran, (string) $tgl_system, (string) $keterangan_transaksi, (string) $Plafond, (string) $userid);

			db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
			values(?,?,'1431000',?,'Bukti SYSTEM',?,?,?)", 'ssssss',
				(string) $unit, (string) $kode_tran, (string) $tgl_system, (string) $keterangan_transaksi, (string) $Plafond, (string) $userid);
			db_jalankan("update `calon_loan` set status_app='BATAL' WHERE cif =?", 's', (string) $cif) or die(mysqli_error(db_ni()));

}

		}
				echo '<script>alert("Pembatalan sudah direalisasikan."); window.location="batal_waka.php"</script>';

			}
			else {
					echo '<script>alert("anda belum memilih data."); window.location="batal_waka.php"</script>';
		}

?>
