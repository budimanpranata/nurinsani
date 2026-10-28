<?php require_once __DIR__ . '/koneksi_db.php'; 
session_start();
ob_start();
$userid=$_SESSION['userid'];
$tanggal=$_SESSION['stgl_system'];
$tgl_input=date("Y-m-d");
$buss_date=$tgl_system;
$kel =$_GET['code_kel'];
$code_kel=$_POST['kel'];
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


$nom=$_POST['bulat'];			
			
$checbox = $_POST['cekbox'];


//if (isset($_POST['cekbox'])){
if($checbox){

	for ($i=0; $i <sizeof($checbox);$i++){
		$kel = mysql_query ("select hari,tenor,maturity_date,bulat,ijaroh,pokok,no_anggota,os,saldo_margin,loan.code_kel,loan.unit,angsuran,loan.Cust_Short_name,loan.cif,loan.cao,status_app FROM loan 
		left join anggota on anggota.cif=loan.cif
		where  loan.cif ='".$checbox[$i]."'");
		$data = mysql_fetch_array($kel);

		$mat=$data['maturity_date'];
		$cif=$data['cif'];
		$angsur=$data['angsuran'];
		$bulat=$data['bulat'];
		$pokok=$data['pokok'];
		$ijaroh=$data['ijaroh'];
		$kelompok=$data['code_kel'];
		$tenor=$data['tenor'];
		$no_anggota=$data['no_anggota'];
		
		$unit=$data['unit'];		
		$hari=$data['hari'];
		$pembiayaan=$data['os'];
		$saldo_margin=$data['saldo_margin'];
$status_app=$data['status_app'];
		
		
		
		
		
		




if ($nom[$i]==0){
		$data=0;
			
		}else{
			
			
			
			
			$tenor_baru=$pembiayaan/$nom[$i];
			$ijaroh_baru=$saldo_margin/$tenor_baru;
			$pokok_baru = $nom[$i] - $ijaroh_baru;
			$angsuran_baru =$nom[$i]; 
$kodetr=8;
            $kodetr=acakangkahuruf($kodetr);
            $kode_trans="BH/".$unit.$kodetr;
	
		$tgl_m    = strtotime($tanggal);
        $maturity_date_res =  date("Y-m-d", strtotime("+$tenor_baru WEEK", $tgl_m));
			
			
				switch ($status_app) {
					case "REST_POKOK_MARGIN":
mysql_query ("delete from  rest_covid where cif ='$cif'")or die(mysql_error());	
mysql_query ("insert into rest_covid values('','$tanggal','$cif','$angsur','$pokok','$ijaroh','$bulat','$tenor','$mat','$angsuran_baru','$pokok_baru','$ijaroh_baru','$angsuran_baru','$tenor_baru','$maturity_date_res','$kelompok')")or die(mysql_error());
mysql_query ("update loan set angsuran='$angsuran_baru', pokok='$pokok_baru',ijaroh='$ijaroh_baru',bulat='$angsuran_baru',gol='1',run_tenor ='0', ke='1',maturity_date ='$maturity_date_res',tgl_akad ='$tanggal',status_app='REST_ANGSURAN',Tenor ='$tenor_baru', deal_produk ='1' where cif ='$cif'")or die(mysql_error());
mysql_query ("delete from tunggakan where cif ='$cif'")or die(mysql_error());
$update =mysql_query ("delete from pinjaman_detail where cif ='$cif'")or die(mysql_error());
$update =mysql_query ("delete from temp_eod where cif ='$cif'")or die(mysql_error());
$ket ="Restrukturisasi Rest pokok margin ke REST_ANGSURAN ";

@mysql_query("insert into jurnal_umum(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values('','$kode_trans','$tanggal','$unit')");
//Realisasi Piutang Margin	
mysql_query("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
				values('$unit','$kode_trans','1413000','$tanggal','Bukti Umum','$ket','$pembiayaan','$userid')");
mysql_query("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
				values('$unit','$kode_trans','1421000','$tanggal','Bukti Umum','$ket','$saldo_margin','$userid')");
//Realisasi Piutang Margin	
mysql_query("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
	values('$unit','$kode_trans','1423000','$tanggal','Bukti Umum','$ket','$saldo_margin','$userid')");
//Realiasasi Murabahah Debit pada Piutang Murabahah total jumlah Pokok dan margin 
mysql_query("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
				values('$unit','$kode_trans','1411000','$tanggal','Bukti Umum','$ket','$pembiayaan','$userid')");

break;
case "REST":

echo "<script>alert('Maaf Status REST tidak bisa di Restrturisasi lagi');</script>";
        echo "<script language='javascript'>document.location.href='rest_covid.php'</script>"; 
break;
case "COVID":
mysql_query ("delete from  rest_covid where cif ='$cif'")or die(mysql_error());	
mysql_query ("insert into rest_covid values('','$tgl_system','$cif','$angsur','$pokok','$ijaroh','$bulat','$tenor','$mat','$angsuran_baru','$pokok_baru','$ijaroh_baru','$angsuran_baru','$tenor_baru','$maturity_date_res','$kelompok')")or die(mysql_error());
mysql_query ("update loan set angsuran='$angsuran_baru', pokok='$pokok_baru',ijaroh='$ijaroh_baru',bulat='$angsuran_baru',gol='1',run_tenor ='0', ke='1',maturity_date ='$maturity_date_res',tgl_akad ='$tanggal',status_app='REST_ANGSURAN',Tenor ='$tenor_baru', deal_produk ='1' where cif ='$cif'")or die(mysql_error());
mysql_query ("delete from tunggakan where cif ='$cif'")or die(mysql_error());
$update =mysql_query ("delete from pinjaman_detail where cif ='$cif'")or die(mysql_error());
$update =mysql_query ("delete from temp_eod where cif ='$cif'")or die(mysql_error());
$ket ="Restrukturisasi Rest pokok margin ke REST_ANGSURAN ";
break;
case "MURAB":
mysql_query ("delete from  rest_covid where cif ='$cif'")or die(mysql_error());	
mysql_query ("insert into rest_covid values('','$tgl_system','$cif','$angsur','$pokok','$ijaroh','$bulat','$tenor','$mat','$angsuran_baru','$pokok_baru','$ijaroh_baru','$angsuran_baru','$tenor_baru','$maturity_date_res','$kelompok')")or die(mysql_error());
mysql_query ("update loan set angsuran='$angsuran_baru', pokok='$pokok_baru',ijaroh='$ijaroh_baru',bulat='$angsuran_baru',gol='1',run_tenor ='0', ke='1',maturity_date ='$maturity_date_res',tgl_akad ='$tanggal',status_app='REST_ANGSURAN',Tenor ='$tenor_baru',deal_produk ='1' where cif ='$cif'")or die(mysql_error());
mysql_query ("delete from tunggakan where cif ='$cif'")or die(mysql_error());
$update =mysql_query ("delete from pinjaman_detail where cif ='$cif'")or die(mysql_error());
$update =mysql_query ("delete from temp_eod where cif ='$cif'")or die(mysql_error());
$ket ="Restrukturisasi Rest pokok margin ke REST_ANGSURAN ";
break;
default:
		
}
		
		}	
		

		
echo "<script>alert('Restrurisasi an $kel Suksess');</script>";
        echo "<script language='javascript'>document.location.href='rest_covid.php'</script>";
	
		
}	
}else {
			echo '<script>alert("anda belum memilih data."); window.history.back();</script>';
	}

  
 
			
			
                     
        
				


?>

