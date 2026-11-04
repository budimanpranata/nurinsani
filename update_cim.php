<?php require_once __DIR__ . '/php8_compat.php';
date_default_timezone_set('Asia/Jakarta');
session_start();
ob_start();
		$kel=$_SESSION['code_kel'];
		$userid=$_SESSION['userid'];
		//cek apakah user sudah login
if(!isset($_SESSION['userid'])){
   header("location:index.html");
	 exit; }
	
//cek level user
if($_SESSION['level']!="unit"){
   header("location:index.html");
 exit; }
include "function.php";
include "conn2.php";
		$koneksi	=open_connection2();
		$code_kel	= mysql_real_escape_string(trim($_POST['code_kel']));
		$cao		= mysql_real_escape_string(trim($_POST['code_ao']));
		$branch 	= mysql_real_escape_string($_POST['branch']);
		$cif 		= mysql_real_escape_string($_POST ['cif']);
		$nama 		= mysql_real_escape_string($_POST['nama']);
		$hari 		= mysql_real_escape_string($_POST['hari']);
		$nama1 		= mysql_real_escape_string($_POST['wali']);
		$alamat 	= mysql_real_escape_string($_POST['alamat']);
		$rt 		= mysql_real_escape_string($_POST['rt']);
		$notlp 		= mysql_real_escape_string($_POST['no_tlp']);
		$home_tlp 	= mysql_real_escape_string($_POST['tlp_penjamin']);
		$tgl_lahir	= mysql_real_escape_string($_POST['tgl_lahir']);
		$tgl_wa= mysql_real_escape_string($_POST['tgl_skep']);
		$tgl_akad	= mysql_real_escape_string($_POST['tgl_akad']);
		$ibu 		= mysql_real_escape_string($_POST['ibu']);
		$buss_date	= date("Y-m-d H:m:s");
		$usaha		= mysql_real_escape_string($_POST['usaha']);
		$agama 		= mysql_real_escape_string($_POST['agama']);
		$negara 	= mysql_real_escape_string($_POST['negara']);
		$pendidikan = mysql_real_escape_string($_POST['pendidikan']);
		$kerja = mysql_real_escape_string($_POST['pekerjaan']);
		$npwp 		= mysql_real_escape_string($_POST['npwp']);
		$jenis_kel	= mysql_real_escape_string($_POST['jenis_kel']);
		$kode_pos 	= mysql_real_escape_string(trim($_POST['kd_pos']));
		$lurah 		= mysql_real_escape_string($_POST['lurah']);
		$kec 		= mysql_real_escape_string($_POST['kec']);
		$rekomen 	= mysql_real_escape_string($_POST['num']);
		$kota 		= mysql_real_escape_string($_POST['kota']);
		$status_kawin= mysql_real_escape_string($_POST['status_kawin']);
		$id_expire	= mysql_real_escape_string($_POST['id_expire']);
		$ktp		= mysql_real_escape_string(trim($_POST['id']));
		$pendapatan	= mysql_real_escape_string($_POST['sumber']);
		$pendidikan	= mysql_real_escape_string($_POST['pendidikan']);
		$tempat_lahir= mysql_real_escape_string($_POST['tempat_lahir']);
		$norek		= mysql_real_escape_string($_POST['norek']);
		$no_anggota	= mysql_real_escape_string($_POST['no_anggota']);
		$nama_usaha = mysql_real_escape_string($_POST['nama_usaha']);
$ke = mysql_real_escape_string($_POST['ke']);

		
							
					$calon_anggota= "update anggota set 
					CODE_KEL='$code_kel',
					CAO='$cao',
					CUST_SHORT_NAME='$nama',
					HADDRESS1='$alamat',
					HADDRESS2='$lurah',
					HADDRESS3= '$kec',
					HPOST_CODE='$kode_pos',
					HADDRESS4='$kota',
					HADDRESS5='$rt',
					MOBILE_PHONE='$notlp',
					HOME_PHONE='$home_tlp',
					BIRTH_DATE='$tgl_lahir',
					ID_NUMBER='$ktp',
					MOTHER_NAME='$ibu',
					NPWP='$npwp',
					CITIZENSHIP='$tempat_lahir',
					ID_EXPIRED='$id_expire',
					WARIS='$nama1'														
					where CIF='$cif' ";
					$i = mysql_query($calon_anggota);
					
					$update_loan= "update loan set code_kel='$code_kel',cao='$cao',nama_usaha='$nama_usaha',maturity_date='$tgl_akad',ke='$ke',hari='$hari',Cust_Short_name='$nama' where cif ='$cif'";
					$b = mysql_query($update_loan);
					
$update_tunggak= "update tunggakan set CODE_KEL='$code_kel',CAO='$cao' where CIF ='$cif'";
					$c = mysql_query($update_tunggak);
											
								if(($i)||($b)){ 
	
							echo "<script> alert ('data Anggota $nama $cif sudah di Update ')</script>";

						echo "<script language='javascript'>document.location.href='view_data_n.php'</script>";

				} else {
					echo "gagal input <a href=\"javascript:history.back()\">kembali</a>";
			} 
	
		
	
?>
