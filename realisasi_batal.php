<?php require_once __DIR__ . '/koneksi_db.php';
/**
 * Batal Wakalah / Musyarakah: proses anggota yang dipilih di batal_waka.php.
 * Logika dan urutan proses sama dengan versi lama; hanya query (prepared statement) yang berubah.
 * Perbaikan 2026-11-20 (perintah user, §9):
 *   - CIF yang tidak ada / bukan APPROVE-MUSYAROKAH di unit user (sama dengan isi daftar batal_waka.php, mis. sudah
 *     dibatalkan karena klik ganda) dilewati dan disebut di pesan. Dulu proses berhenti (die) tanpa pesan di tengah daftar,
 *     dan CIF yang sudah BATAL bisa dibatalkan lagi (jurnal ganda).
 *   - per anggota dalam satu transaksi DB; bila ada query gagal, anggota itu dibatalkan seluruhnya dan disebut di pesan.
 * Rekening jurnal, nominal, dan perubahan status untuk pembatalan yang berhasil tidak berubah.
 */
date_default_timezone_set('Asia/Jakarta');
require_once __DIR__ . '/inc/notif.php';
session_start();
require_once __DIR__ . '/inc/cek_login.php';
wajib_level('unit');
require_once __DIR__ . '/inc/cek_login.php';
wajib_login();
ob_start();
	$userid=$_SESSION['userid'];
	$tanggal=date("Y-m-d");
$tgl_system=$_SESSION['stgl_system'];

include "function.php";



$cekbox = isset($_POST['cekbox']) ? $_POST['cekbox'] : null;
if ($cekbox) {
	$r_unit = db_satu("SELECT kode_branch FROM user WHERE userid = ?", 's', (string) $userid);
	$unit_user = $r_unit ? (string) $r_unit['kode_branch'] : '';
	$dilewati = array();
	$gagal_cif = array();
       foreach ($cekbox as $value) {

		     $row = db_satu("select DATE_ADD(tgl_wakalah,INTERVAL 7 DAY) as tgl_murab,calon_loan.buss_date,calon_loan.code_kel,no_anggota,calon_loan.Cust_Short_name, status_app,
			calon_loan.Deal_type,suffix,bagi_hasil,Tenor,Plafond,os,angsuran,pokok,ijaroh,bulat,ke,usaha,calon_loan.unit,tgl_wakalah,hari,calon_loan.cao,calon_loan.USERID,calon_loan.status,calon_loan.cif
			 from calon_loan
			 inner join anggota on anggota.cif = calon_loan.cif
			 WHERE calon_loan.cif =? AND calon_loan.unit = ? AND status_app IN ('APPROVE','MUSYAROKAH')", 'ss', (string) $value, $unit_user);
			// 2026-11-20: dulu die() (berhenti tanpa pesan, anggota berikutnya tidak diproses); kini dilewati & disebut di pesan
			if (!$row) { $dilewati[] = (string) $value; continue; }
			mysqli_begin_transaction(db_ni());
			$gagal_awal = db_jumlah_gagal();
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
			db_jalankan("delete from `barang` WHERE id_barang =?", 's', (string) $no_anggota);
			// perintah user 2026-10-30: update berdasarkan CIF (versi lama keliru membandingkan no_anggota dengan CIF)
			db_jalankan("update loan set status='CALON' WHERE cif =?", 's', (string) $cif);
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
			db_jalankan("update `calon_loan` set status_app='BATAL_MUS' WHERE cif =?", 's', (string) $cif);


}else{

			db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
			values(?,?,'1120000',?,'Bukti SYSTEM',?,?,?)", 'ssssss',
				(string) $unit, (string) $kode_tran, (string) $tgl_system, (string) $keterangan_transaksi, (string) $Plafond, (string) $userid);

			db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
			values(?,?,'1431000',?,'Bukti SYSTEM',?,?,?)", 'ssssss',
				(string) $unit, (string) $kode_tran, (string) $tgl_system, (string) $keterangan_transaksi, (string) $Plafond, (string) $userid);
			db_jalankan("update `calon_loan` set status_app='BATAL' WHERE cif =?", 's', (string) $cif);

}
			if (db_jumlah_gagal() > $gagal_awal) {
				mysqli_rollback(db_ni());
				$gagal_cif[] = (string) $cif;
			} else {
				mysqli_commit(db_ni());
			}

		}
				$pesan_batal = 'Pembatalan sudah direalisasikan.';
				if ($dilewati) {
					$pesan_batal .= "\n\nTidak diproses (tidak ada / sudah dibatalkan / bukan unit Anda):\n" . implode("\n", $dilewati);
				}
				if ($gagal_cif) {
					$pesan_batal .= "\n\nGAGAL, data tidak disimpan:\n" . implode("\n", $gagal_cif);
				}
				notif_pergi($pesan_batal, 'batal_waka.php', ($dilewati || $gagal_cif) ? 'warning' : null);

			}
			else {
					notif_pergi('anda belum memilih data.', 'batal_waka.php');
		}

?>
