<?php require_once __DIR__ . '/../php8_compat.php';
session_start();
$userid=$_SESSION['userid'];
 include "../conn2.php";
  $koneksi=open_connection2();
//cek apakah user sudah login
if(!isset($_SESSION['userid'])){
   header("location:../index.html");//jika belum login jangan lanjut..
}
?>
<?php
$pass = $_POST['pass'];
$password = md5($pass);
$username = $_POST['username'];
$nama = $_POST['nama'];
$level =$_POST ['level'];
$branch =$_POST ['branch'];

if (isset($username) && isset($password) && isset($level)) {
  $insert = "insert into user set userid = '$username', pass = '$password', level = '$level', nama = '$nama',kode_branch='$branch'";
  if (mysql_query($insert))
  {
    echo "<h3>User berhasil ditambahkan</h3>";
  }
  else {
    echo "<h3>User gagal ditambahkan</h3>";
  }
}

?>