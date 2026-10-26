<?php require_once __DIR__ . '/php8_compat.php';
session_start();
include "conn2.php";
$koneksi=open_connection2();
$userid=$_SESSION['userid'];
//cek apakah user sudah login
$tgl_system=$_SESSION['stgl_system'];
//cek apakah user sudah login
if(!isset($_SESSION['userid'])){
   header("location:index.html");
}

//cek level user
if($_SESSION['level']!="unit"){
   header("location:index.html");
}


$result = mysql_query("SELECT * FROM user WHERE userid='$userid'");
while($row = mysql_fetch_array($result)){
	$user = $row['userid'] ;
	$nama =$row['nama'] ;
	$password= $row ['pass'];
}
?>
<?php

$passlama = $_POST['passlama'];
$passbaru = $_POST['passbaru'];
$passlagi = $_POST['passlagi'];
$password = md5($passlagi);
$username = $userid;

if(empty($passlama)||empty($passbaru)||empty($username)){
        
        echo "<script>alert('Maaf data Anda belum lengkap');</script>";
         echo "<script language='javascript'>document.location.href='Gantipass.php'</script>";}
        else {
$cekuser = "select * from user where userid= '$userid'";
$querycekuser = mysql_query($cekuser);
$count =  mysql_num_rows($querycekuser);

if ($count >= 1){

$updatepassword = "update user set pass = '$password' where userid = '$userid'";

$updatequery = mysql_query($updatepassword);

if($updatequery){

"Password telah diganti menjadi $passbaru";
        echo "<script>alert('Password anda telah di ganti ');</script>";
 echo "<script language='javascript'>document.location.href='index.html'</script>";

session_destroy();
exit();



}
else{
        echo "<script>alert('Maaf periksa kembali password anda ');</script>";
         echo "error";}

}
}

?>
