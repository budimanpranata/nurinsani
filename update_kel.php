<?php require_once __DIR__ . '/php8_compat.php';
session_start();
require_once "conn2.php";
$koneksi=open_connection2();

$userid=$_SESSION['userid'];
$kel= $_POST['code_kel'];
$ds= $_POST['cao'];
$alamat= $_POST['alamat'];
$nama= mysql_real_escape_string($_POST['nama']);
$hari= mysql_real_escape_string($_POST['hari']);

//cek apakah user sudah login
if(!isset($_SESSION['userid'])){
   header("location:index.html");
 exit; }


$data = mysql_query ("select kode_branch from user where userid ='$userid'");
$r = mysql_fetch_array($data);
$branch=$r['kode_branch'];


//mysqli_autocommit($koneksi,FALSE);   
$input=mysql_query ("replace into kelompok(code_kel,code_unit,nama_kel,alamat,cao,cif) values('$kel','$branch','$nama','$alamat','$ds','0')");
$input=mysql_query ("Update loan set cao ='$ds', hari ='$hari' where code_kel='$kel'");
$input=mysql_query ("Update anggota set cao ='$ds' where code_kel='$kel'");
$input=mysql_query ("Update simpanan set CAO ='$ds' where code_kel='$kel'");
$input=mysql_query ("Update simpanan_pokok set CAO ='$ds' where code_kel='$kel'");
$input=mysql_query ("Update simpanan_wajib set CAO ='$ds' where code_kel='$kel'");
$input=mysql_query ("Update tunggakan set CAO ='$ds' where CODE_KEL='$kel'");

//mysqli_commit($koneksi);

			if($input){ 
	
	echo "<script> alert ('data kelompk sudah Update ')</script>";

   echo "<script language='javascript'>document.location.href='kelompok.php'</script>";

	} else {
          echo "gagal input <a href=\"javascript:history.back()\">kembali</a>";
        } 
	
	
	

?>
