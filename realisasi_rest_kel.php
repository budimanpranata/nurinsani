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
include "function.php";

$tanggal=$tgl_system;
$simpan=$_SESSION['simpans'];	
$baki=$_SESSION['bakis'];	
$nom=isset($_POST['bulat']) ? $_POST['bulat'] : null;			
			
$checbox = isset($_POST['cekbox']) ? $_POST['cekbox'] : null;

if (isset($_POST["realisasi"])){
  // "Save Changes" clicked

if ($checbox) {
	for ($i=0; $i <sizeof($checbox);$i++){
		$kel = db_semua("select maturity_date,no_anggota,anggota.cif,loan.code_kel,loan.unit,angsuran,loan.Cust_Short_name,loan.cif,os,loan.cao,saldo_margin,status_app FROM loan 
		left join anggota on anggota.cif=loan.cif
		where  loan.cif =?", 's', (string) $checbox[$i]);
		$data = $kel ? $kel[0] : false;

		$mat=$data['maturity_date'];
		$cif=$data['cif'];
		$angsur=$data['angsuran'];
		$hari=$data['hari'];
		
		
		$row_tunggak=db_satu("select sum(KREDIT-DEBIT) as saldo_tunggak from tunggakan where cif =? ", 's', (string) $checbox[$i]) or die(mysql_error());
 $saldo_tunggak=$row_tunggak['saldo_tunggak'];     
$tunggak=$saldo_tunggak;

$ft =compat_bagi($tunggak, $angsur); // PHP 5: pembagi 0/kosong -> false, bukan error
		
		$tgl_m    = strtotime($tgl_system);
        $maturity_date_res =  date("Y-m-d", strtotime("+$ft WEEK", $tgl_m));
		
$cek_tanggal = db_semua("SELECT COUNT(tgl_libur) as libur from param_libur where tgl_libur between ? and ? and Hari =?", 'sss', (string) $mat, (string) $maturity_date_res, (string) $hari);
        foreach ($cek_tanggal as $data_libur){
            $libur =$data_libur['libur'];
           
           
        }

if($libur>0){
$ft =$ft+$libur;


        $tgl_m    = strtotime($tgl_system);
        $maturity_date_res =  date("Y-m-d", strtotime("+$ft WEEK", $tgl_m));



}else{
$maturity_date_res=$maturity_date_res;
}

db_jalankan("update loan set maturity_date =?, gol='1', deal_produk ='1' where cif =?", 'ss', (string) $maturity_date_res, (string) $cif);
db_jalankan("delete from tunggakan where cif =?", 's', (string) $cif);
db_jalankan("delete from temp_eod where cif =?", 's', (string) $cif) or die(mysql_error());




echo "<script>alert('Restrurisasi an $cif Suksess');</script>";
		
		
	
		
		
		
		
		
		
		
	

            echo "<script language='javascript'>document.location.href='rest_kel.php'</script>";
	}	
	
}else {
			echo '<script>alert("anda belum memilih data."); window.location="rest_kel.php"</script>';
	}

  
  
} 
?>

