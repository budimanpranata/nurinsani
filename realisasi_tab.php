<?php require_once __DIR__ . '/koneksi_db.php'; 
session_start();
ob_start();
$userid=$_SESSION['userid'];
$tgl_system=$_SESSION['stgl_system'];
$tgl_input=date("Y-m-d");
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
mysql_select_db('nur_insani');
include "function.php";

$tanggal=$tgl_system;
			
			
$cekbox = $_POST['cekbox'];
if ($cekbox) {
       foreach ($cekbox as $value) {
		   
		$select = mysql_query("select DATE_ADD(next_schedule,INTERVAL 7 DAY) as next_schedule,Cust_Short_name,unit,code_kel,no_anggota,cif,ijaroh,Plafond,pokok,os,angsuran,bulat,TIMESTAMPDIFF(WEEK,tgl_murab,next_schedule) AS RUN_TENOR,TIMESTAMPDIFF(WEEK,tgl_murab,CURDATE()) AS ke,cao  from loan  WHERE cif ='" . $value . "'");
			while($row = mysql_fetch_array($select)){
				$next_schedule=$row['next_schedule'];
				$no_anggota=$row['no_anggota'];
				$pokok=$row['pokok'];
				$os=$row['os'];
				$bulat=$row['bulat'];
				$Cust_Short_name=mysql_real_escape_string($row['Cust_Short_name']);
				$RUN_TENOR=$row['RUN_TENOR'];
				$unit=$row['unit'];
				$angsuran=$row['angsuran'];
				$ijaroh=$row['ijaroh'];
				$code_kel=$row['code_kel'];
				$ke=$row['ke'];
				$cif=$row['cif'];
				$cao=$row['cao'];
				$ket="Setoran Angsuran $Cust_Short_name $cif";
				$ket_wadiah="Setoran Wadiah $Cust_Short_name $cif";
				
				$kode_tr=2;
				$kode_tr=acakangkahuruf($kode_tr);	  
				$kode_transaksi="BU/".$unit .$kode_tr;
				
				$wajib_kh=$bulat - $angsuran;
				
				$kodet=7;
				$kodet=acakangkahuruf($kodet);
				$kode_trans="BU/".$unit .$kodet .$kode_tr;
				
				@mysql_query("insert  into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,tgl_input,kode_transaksi)VALUES('$tanggal','$no_anggota','$unit','$cif','$code_kel','0','01','$angsuran','$userid','$ket','','$cao','$tgl_input','$kode_trans') ") or die(mysql_error("GAGAL INPUT"));
				@mysql_query("insert  into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,blok,tgl_input,kode_transaksi)VALUES('$tanggal','$no_anggota','$unit','$cif','$code_kel','0','01','$wajib_kh','$userid','$ket_wadiah','','$cao','1','$tgl_input','$kode_trans') ") or die(mysql_error("GAGAL INPUT"));
				
				//$jurnal_umum=mysql_fetch_array(mysql_query("SELECT max(nomor_jurnal) FROM jurnal_umum  ORDER BY tanggal_selesai DESC"));
				//$nomor_jurnal=$jurnal_umum[0]+1;
				//pencatatan du jurnal realasisi Piutang Murabahaha total jumlah Pokok dan margin
				//@mysql_query("insert into jurnal_masuk(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values('$nomor_jurnal','$kode_transaksi','$tanggal','$unit')");
				//@mysql_query("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
				//values('$unit','$kode_transaksi','1120000','$tanggal','KAS MASUK','$ket','$bulat','$userid')");
				
				$jurnal_umum=mysql_fetch_array(mysql_query("SELECT max(nomor_jurnal) FROM jurnal_masuk where unit ='$unit' ORDER BY tanggal_selesai DESC"));
				$nomor_jurnal=$jurnal_umum[0]+1;
				
				//jurnak kas
				@mysql_query("insert into jurnal_masuk(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values('$nomor_jurnal','$kode_transaksi','$tanggal','$unit')");
				@mysql_query("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
				values('$unit','$kode_trans','1120000','$tanggal','Bukti SYSTEM','$ket','$bulat','$userid')");
				//jurnak ke simpanan pokok
				@mysql_query("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
				values('$unit','$kode_trans','2101000','$tanggal','Bukti SYSTEM','$ket_wadiah','$bulat','$userid')");

//@mysql_query("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
				//values('$unit','$kode_trans','$cif','$tanggal','Bukti Umum','Tabungan','$angsuran','$userid')");


				mysql_query("Update  tabungan_detail set saldo =(select(sum(KREDIT)-SUM(DEBIT)) as saldo from simpanan where cif='$cif') where cif  ='$cif'") or die(mysql_error("GAGAL INPUT"));
	
				}
			}
			echo '<script>alert("Tabungan sudah direalisasikan."); window.location="yrs_list.php"</script>';
		
		
	}else {
			echo '<script>alert("anda belum memilih data."); window.location="yrs_list.php"</script>';
	}

?>
