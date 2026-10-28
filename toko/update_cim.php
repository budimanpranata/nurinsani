<?php require_once __DIR__ . '/../php8_compat.php';
date_default_timezone_set('Asia/Jakarta');
session_start();
ob_start();
		$kel=$_SESSION['code_kel'];
		$userid=$_SESSION['userid'];
		//cek apakah user sudah login
if(!isset($_SESSION['userid'])){
   header("location:../index.html");
	 exit; }
	
//cek level user
if($_SESSION['level']!="toko"){
   header("location:../index.html");
 exit; }
include "function.php";
include "conn2.php";
		$koneksi	=open_connection2();
		$code_kel	= $_POST['code_kel'];
		$cao		= $_POST['code_ao'];
		$branch 	= $_POST['branch'];
		$cif 		= $_POST ['cif'];
		var_dump($userid);
		$nama 		= mysql_real_escape_string($_POST['nama']);
		$nama1 		= mysql_real_escape_string($_POST['wali']);
		$alamat 	= $_POST['alamat'];
		$rt 		= $_POST['rt'];
		$notlp 		= $_POST['no_tlp'];
		$home_tlp 	= $_POST['tlp_penjamin'];
		$tgl_lahir	= $_POST['tgl_lahir'];
		$tgl_wa= $_POST['tgl_skep'];
		$tgl_akad	= $_POST['tgl_akad'];
		$ibu 		= $_POST['ibu'];
		$buss_date	= date("Y-m-d H:m:s");
		$usaha		= $_POST['usaha'];
		$agama 		= $_POST['agama'];
		$negara 	= $_POST['negara'];
		$pendidikan = $_POST['pendidikan'];
		$kerja = $_POST['pekerjaan'];
		$npwp 		= $_POST['npwp'];
		$jenis_kel	= $_POST['jenis_kel'];
		$kode_pos 	= $_POST['kd_pos'];
		$lurah 		= $_POST['lurah'];
		$kec 		= $_POST['kec'];
		$rekomen 	= $_POST['num'];
		$kota 		= $_POST['kota'];
		$status_kawin=$_POST['status_kawin'];
		$id_expire	= $_POST['id_expire'];
		$ktp		=$_POST['id'];
		$pendapatan	= $_POST['sumber'];
		$pendidikan	= $_POST['pendidikan'];
		$tempat_lahir=$_POST['tempat_lahir'];
		$norek		= $_POST['norek'];
		$no_anggota	= $_POST['no_anggota'];
		$nama_usaha = $_POST['nama_usaha'];

		
							
					$calon_anggota= "update member set 
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
					WARIS='$nama1',	
					UNIT='$userid'								
					where id_cust='$cif' ";
					$i = mysql_query($calon_anggota);
					
					$update_loan= "update musawamah set code_kel='$code_kel',cao='$cao',nama_usaha='$nama_usaha',unit='$userid' where id_member ='$cif'";
					$b = mysql_query($update_loan);
						
											
								if(($i)||($b)){ 
	
							echo "<script> alert ('data Member $nama $cif sudah di Update ')</script>";

						echo "<script language='javascript'>document.location.href='view_data_n.php'</script>";

				} else {
					echo "gagal input <a href=\"javascript:history.back()\">kembali</a>";
			} 
	
		
	
?>
