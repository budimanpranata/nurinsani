<?php require_once __DIR__ . '/koneksi_db.php'; 
session_start();
ob_start();
date_default_timezone_set('Asia/Jakarta');
$userid=$_SESSION['userid'];
$tgl_system=$_SESSION['stgl_system'];
$tgl_input=date("Y-m-d");
$buss_date=$tgl_system;
var_dump($tgl_system);
//cek apakah user sudah login
if(!isset($_SESSION['stgl_system'])){
echo "<script>alert('Maaf,.tanggal system belum di setup');</script>";

   header("location:param_tgl.php");
}
//cek apakah user sudah login
if(!isset($_SESSION['userid'])){
   header("location:index.html");
}

//cek level user
if($_SESSION['level']!="unit"){
   header("location:index.html");
}



$con = db_ni();
if (mysqli_connect_errno()) {
	echo "Failed to connect to MySQL: " . mysqli_connect_error();
	exit;
  }

// Turn autocommit off
mysqli_autocommit($con,FALSE);



include "function.php";

$tanggal=$tgl_system;
$simpan=$_SESSION['simpans'];	
$baki=$_SESSION['bakis'];	
$nom=$_POST['bulat'];			
			
$checbox = $_POST['cekbox'];
if ($checbox) {
	for ($i=0; $i <sizeof($checbox);$i++){
		
		$kel =db_query ($con,"select loan.cif,no_anggota as norek,run_tenor,last_payment,loan.code_kel,loan.unit,angsuran,status_app,loan.Cust_Short_name,loan.cao,bulat,tgl_murab,pokok,os,ijaroh,deal_produk FROM loan 
		left join anggota on anggota.cif=loan.cif
		where  loan.cif ='".$checbox[$i]."'");
		$data = db_fetch_array($kel);

		$norek=$data['norek'];
        $cif=$data['cif'];
		$saldo_margin=$data['saldo_margin'];
		$pokok=$os-$saldo_margin;
		$unit=$data ['unit'];
		$angsuran=$data ['angsuran'];
		$angsuran_pokok=$data ['pokok'];
		$ijaroh=$data ['ijaroh'];
		$code_kel=$data['code_kel'];
		$ke = $data['run_tenor'];
		$cao=$data['cao'];
		$ciff=$data['cif'];
        $last_payment=$data['last_payment'];
		$deal_produk=$data['deal_produk'];
        $tgl_murab=$data['tgl_murab'];
		$os=$data['os'];
		$status_app=$data['status_app'];
		$run_tenor=$data['run_tenor'];
		$pb=$run_tenor + 1;
		$next_tenor=$run_tenor + 2;

		$angsuran=$data['angsuran'];
		$bulat=$data['bulat'];


		$cek_tunggak= db_fetch_array(db_query($con,"select sum(KREDIT)- sum(DEBIT) as tunggak from tunggakan WHERE cif ='".$checbox[$i]."'"));
        $ada_tunggak=$cek_tunggak['tunggak']; 
		$ft=round(compat_num($ada_tunggak/$angsuran));  
		
		if($deal_produk=='2'){
			$simpanan_minggon_asli=$bulat - $angsuran;
			$simpanan_minggon=$bulat - $angsuran - 2500;
			$simpan_duaribu = '2500';		
			$angsuran=$angsuran + 2500;
			$ijaroh = $ijaroh + 2500;


	$setoran=$nom[$i] - $simpanan_minggon_asli;

		}else{
			
		$simpanan_minggon=$bulat - $angsuran;
		$simpan_duaribu = '0';		

		$setoran=$nom[$i] - $simpanan_minggon;
		$angsuran=$angsuran ;
		$ijaroh = $ijaroh;


		}

             $input ="Insert into booking_debet (cif,simpanan,tunggakan,tgl_booking,status_booking)
	 VALUES('$cif','$simpanan','$ada_tunggak','$tgl_system','Booking Debet')";
	  $inputtrnsk = db_query ($con,$input);
		

		
		
	}

	if (!mysqli_commit($con)) {
		echo "Transaksi Gagal commit Silahkan ulangi lagi";
		exit();
	  }
echo "<script>alert('Booking Debet sukses di simpan');</script>";
echo "<script language='javascript'>document.location.href='book_debet.php'</script>";
	
}else {
			echo '<script>alert("anda belum memilih data."); window.location="form.php"</script>';
	}

?>
