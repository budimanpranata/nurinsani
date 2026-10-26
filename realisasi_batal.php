<?php require_once __DIR__ . '/koneksi_db.php'; 
date_default_timezone_set('Asia/Jakarta');
session_start();
ob_start();
	$userid=$_SESSION['userid'];
	$tanggal=date("Y-m-d");
$tgl_system=$_SESSION['stgl_system'];

db_ni();
mysql_select_db('nur_insani');
include "function.php";

 
			
$cekbox = $_POST['cekbox'];
if ($cekbox) {
       foreach ($cekbox as $value) {
					 
		     $row = mysql_fetch_array(mysql_query("select DATE_ADD(tgl_wakalah,INTERVAL 7 DAY) as tgl_murab,calon_loan.buss_date,calon_loan.code_kel,no_anggota,calon_loan.Cust_Short_name, status_app,
			calon_loan.Deal_type,suffix,bagi_hasil,Tenor,Plafond,os,angsuran,pokok,ijaroh,bulat,ke,usaha,calon_loan.unit,tgl_wakalah,hari,calon_loan.cao,calon_loan.USERID,calon_loan.status,calon_loan.cif
			 from calon_loan  
			 inner join anggota on anggota.cif = calon_loan.cif
			 WHERE calon_loan.cif ='" . $value . "'")) or die(mysql_error());
        //$row = mysql_fetch_array(mysql_query("select * from calon_loan  WHERE no_anggota ='" . $value . "'")) or die(mysql_error());
			$no_anggota=$row['no_anggota'];
			$tgl_wakalah= $row['tgl_wakalah'];
			$cif=$row['cif'];
			$Plafond=$row['Plafond'];
			$bagi_hasil=$row['bagi_hasil'];
			$piutang=$Plafond + $bagi_hasil;
			$tgl_murab=$row['tgl_murab'];
			$buss_date=$row['buss_date'];
			$code_kel=$row['code_kel'];
			$Cust_Short_name=mysql_real_escape_string($row['Cust_Short_name']);
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
			//@mysql_query ("delete from `pinjaman_detail`  WHERE id_pinjam ='$no_anggota'")or die(mysql_error());
			@mysql_query ("delete from `barang`  WHERE id_barang ='$no_anggota'")or die(mysql_error());
			@mysql_query ("update loan set status='CALON'  WHERE no_anggota ='" . $value . "'")or die(mysql_error());
			$keterangan_transaksi="Realisasi Pembatalan Wakalah An $Cust_Short_name";
$keterangan_transaksi1="Realisasi Pembatalan MUSYARAKAH An $Cust_Short_name";
			
			@mysql_query("insert into jurnal_umum(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values('$nomor_jurnal','$kode_tran','$tgl_system','$unit')");

if($status_app =='MUSYAROKAH'){
@mysql_query("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)values			('$unit','$kode_tran','2500000','$tgl_system','Bukti SYSTEM','$keterangan_transaksi1','$Plafond','$userid')");

			@mysql_query("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)values('$unit','$kode_tran','1120000','$tgl_system','Bukti SYSTEM','$keterangan_transaksi1','$Plafond','$userid')");
@mysql_query ("update  `calon_loan` set status_app='BATAL_MUS' WHERE cif ='$cif'")or die(mysql_error());


}else{

@mysql_query("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)values			('$unit','$kode_tran','1120000','$tgl_system','Bukti SYSTEM','$keterangan_transaksi','$Plafond','$userid')");

			@mysql_query("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)values('$unit','$kode_tran','1431000','$tgl_system','Bukti SYSTEM','$keterangan_transaksi','$Plafond','$userid')");
@mysql_query ("update  `calon_loan` set status_app='BATAL' WHERE cif ='$cif'")or die(mysql_error());

}
			
			//@mysql_query("insert into tabungan_detail (no_anggota,cif,saldo,unit,account_open)VALUES ('$no_anggota','$cif','$saldo','$unit','$tgl_wakalah')");
						
		}
				echo '<script>alert("Pembatalan sudah direalisasikan."); window.location="batal_waka.php"</script>';
    
			}  
			else {
					echo '<script>alert("anda belum memilih data."); window.location="batal_waka.php"</script>';
		}

?>
