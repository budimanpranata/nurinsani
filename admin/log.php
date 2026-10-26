<?php require_once __DIR__ . '/../php8_compat.php';
include "conn.php";
function antiinjection($data){
  $filter_sql = mysql_real_escape_string(stripslashes(strip_tags(htmlspecialchars($data,ENT_QUOTES))));
  return $filter_sql;
  }
$koneksi=open_connection();
session_start();
$userid = $_POST['userid'];
$psw = $_POST ['psw'];
$password = md5($pws);
$op = isset($_GET['op']) ? $_GET['op'] : '';
if($op=="in"){
    $cek = mysql_query("SELECT * FROM user WHERE username='$userid' AND password='$psw'");
    if(mysql_num_rows($cek)==1){//jika berhasil akan bernilai 1
        $c = mssql_fetch_array($cek);
        $_SESSION['userid'] = $c['userid'];
        $_SESSION['leve'] = $c['level'];
        if($c['level']=="lead"){
		 $update_log = mssql_query("UPDATE user SET status ='1' WHERE username= '$userid'");
		 header("location:lead/homelead.php");
		 }else if($c['level']=="bom"){
		 header("location:bom/homebom.php");
		 }else if($c['level']=="administra"){
		 header("location:Admin/admin.php");		 
        }else if($c['level']=="user"){
		$update_log = mssql_query("UPDATE user SET status ='1' WHERE username= '$userid'");
		include ("redirect.php");
		     }
			 
    }else{
         echo "<script>alert('Maaf User name atau password salah');</script>";
         echo "<script language='javascript'>document.location.href='index.html'</script>";
    }
}else if($op=="out"){
    unset($_SESSION['userid']);
    unset($_SESSION['level']);
    header("location:index.html");
}


?>
