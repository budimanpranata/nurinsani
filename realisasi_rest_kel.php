<?php require_once __DIR__ . '/koneksi_db.php'; 
session_start();
ob_start();
$userid=$_SESSION['userid'];
$tgl_system=$_SESSION['stgl_system'];
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
mysql_select_db('nur_insani');
include "function.php";

$tanggal=$tgl_system;
$simpan=$_SESSION['simpans'];	
$baki=$_SESSION['bakis'];	
$nom=$_POST['bulat'];			
			
$checbox = $_POST['cekbox'];

if (isset($_POST["realisasi"])){
  // "Save Changes" clicked

if ($checbox) {
	for ($i=0; $i <sizeof($checbox);$i++){
		$kel = mysql_query ("select maturity_date,no_anggota,anggota.cif,loan.code_kel,loan.unit,angsuran,loan.Cust_Short_name,loan.cif,os,loan.cao,saldo_margin,status_app FROM loan 
		left join anggota on anggota.cif=loan.cif
		where  loan.cif ='".$checbox[$i]."'");
		$data = mysql_fetch_array($kel);

		$mat=$data['maturity_date'];
		$cif=$data['cif'];
		$angsur=$data['angsuran'];
		$hari=$data['hari'];
		
		
		$row_tunggak = mysql_fetch_array(mysql_query("select sum(KREDIT-DEBIT) as saldo_tunggak from tunggakan where cif ='".$checbox[$i]."' ")) or die(mysql_error());
 $saldo_tunggak=$row_tunggak['saldo_tunggak'];     
$tunggak=$saldo_tunggak;

$ft =$tunggak/$angsur;
		
		$tgl_m    = strtotime($tgl_system);
        $maturity_date_res =  date("Y-m-d", strtotime("+$ft WEEK", $tgl_m));
		
$cek_tanggal= mysql_query ("SELECT COUNT(tgl_libur) as libur from param_libur where tgl_libur between '$mat' and '$maturity_date_res' and Hari ='$hari'");
        while($data_libur = mysql_fetch_array($cek_tanggal)){
            $libur =$data_libur['libur'];
           
           
        }

if($libur>0){
$ft =$ft+$libur;


        $tgl_m    = strtotime($tgl_system);
        $maturity_date_res =  date("Y-m-d", strtotime("+$ft WEEK", $tgl_m));



}else{
$maturity_date_res=$maturity_date_res;
}

@mysql_query ("update loan set maturity_date ='$maturity_date_res', gol='1', deal_produk ='1' where cif ='$cif'");
@mysql_query ("delete from tunggakan where cif ='$cif'");
@mysql_query ("delete from temp_eod where cif ='$cif'")or die(mysql_error());




echo "<script>alert('Restrurisasi an $cif Suksess');</script>";
		
		
	
		
		
		
		
		
		
		
	

            echo "<script language='javascript'>document.location.href='rest_kel.php'</script>";
	}	
	
}else {
			echo '<script>alert("anda belum memilih data."); window.location="rest_kel.php"</script>';
	}

  
  
} 
?>

