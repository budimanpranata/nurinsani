<?php require_once __DIR__ . '/../php8_compat.php';
include "conn.php";
function antiinjection($data){
  $filter_sql = mssql_real_escape_string(stripslashes(strip_tags(htmlspecialchars($data,ENT_QUOTES))));
  return $filter_sql;
  }
$koneksi=open_connection();
session_start();
$userid = $_POST['userid'];
$psw = $_POST ['psw'];
$op = isset($_GET['op']) ? $_GET['op'] : '';
if($op=="in"){
    $cek = mssql_query("SELECT * FROM tabeluser WHERE userId='$userid' AND pass='$psw'");
    if(mssql_num_rows($cek)==1){//jika berhasil akan bernilai 1
        $c = mssql_fetch_array($cek);
        $_SESSION['userid'] = $c['userid'];
        $_SESSION['level'] = $c['level'];
        if($c['level']=="css"){
		 header("location:css/homecss.php");
		 }else if($c['level']=="bom"){
		 header("location:bom/homebom.php");
		 }else if($c['level']=="administra"){
		 header("location:Admin/admin.php");		 
        }else if($c['level']=="user"){
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
