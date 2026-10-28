<?php require_once __DIR__ . '/php8_compat.php';
session_start();
$userid=$_SESSION['userid'];
$kel= trim($_POST['code_kel']);
$ds= trim($_POST['cao']);
$alamat= $_POST['alamat'];
$nama= $_POST['nama'];


unset ($_SESSION['tgl_wakalah']);
unset ($_SESSION['stgl_akad']);

//cek apakah user sudah login
if(!isset($_SESSION['userid'])){
   header("location:index.html");
 exit; }

include "conn2.php";
$koneksi=open_connection2();
$data = mysql_query ("select kode_branch from user where userid ='$userid'");
$r = mysql_fetch_array($data);
$branch=$r['kode_branch'];

$query = mysql_query("SELECT 1 FROM kelompok WHERE code_kel = '$kel' ");
$check = mysql_fetch_array($query);
if ($check[0]) {

// jika data ditemukan
echo "<script> alert ('data sudah ada dalam database, isi form dengan kode Kelompok yang berbeda ')</script>";

   echo "<script language='javascript'>document.location.href='simulasi.php'</script>";
		 }

        else {
$_SESSION['cao']=$ds;
$_SESSION['code_kel']=$kel;

$sqlkel= "insert into kelompok(code_kel,code_unit,nama_kel,alamat,cao,cif) values('$kel','$branch','$nama','$alamat','$ds','0')";
$input = mysql_query($sqlkel);


			if($input){ 
	
	echo "<script> alert ('data kelompk sudah disimpan, proses selanjutnya menambah anggota kelompok $nama_kel ')</script>";

   echo "<script language='javascript'>document.location.href='new2.php'</script>";

	} else {
          echo "gagal input <a href=\"javascript:history.back()\">kembali</a>";
        } 
	
	
	}

?>
