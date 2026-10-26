<?php require_once __DIR__ . '/../php8_compat.php';
session_start();
ob_start();
include "conn2.php";
$koneksi=open_connection2();
function antiinjection($data){
  $filter_sql = mysql_real_escape_string(stripslashes(strip_tags(htmlspecialchars($data,ENT_QUOTES))));
  return $filter_sql;
  }

$userid = antiinjection($_POST['userid']);
$psw = antiinjection($_POST['psw']);
$pass = md5($psw);
$op = isset($_GET['op']) ? $_GET['op'] : '';

if (!isset($_SESSION['userid'])){
if($op=="in"){
    $cek = mysql_query("SELECT * FROM user WHERE userid='$userid' AND pass='$pass'");
    $cek_tgl = mysql_fetch_array(mysql_query("SELECT param_tgl FROM param_tgl  WHERE unit  ='$userid'")) or die(mysql_error());
$_SESSION['stgl_system']=$cek_tgl['param_tgl']; 
$tgl=$_SESSION['stgl_system'];   
 echo "<script>alert('Parameter tanggal Unit adalah .$tgl.');</script>";
if(mysql_num_rows($cek)==1){//jika berhasil akan bernilai 1
        $c = mysql_fetch_array($cek);
        $_SESSION['userid'] = $c['userid'];
        $_SESSION['level'] = $c['level'];
        if($c['level']=="lead"){
		 $update_log = mysql_query("UPDATE user SET status ='1' WHERE userid= '$userid'");
		 header("location:lead/homelead.php");
		 }else if($c['level']=="gfh"){
		 header("location:gfh/homesmo.php");
		}else if($c['level']=="um"){
		 header("location:um/homesms.php");
		 }else if($c['level']=="fund"){
		 header("location:fund/fund.php");
		 }else if($c['level']=="afh"){
		 header("location:afh/homeao.php");
		 }else if($c['level']=="kp"){
		 header("location:kp/kp.php");
		 }else if($c['level']=="admin"){
		 header("location:admin/admin.php");	
		}else if($c['level']=="accounting"){
		 header("location:account/account.php");	 		 
         }else if($c['level']=="ho"){
		 header("location:ho/ho.php");		 
        }else if($c['level']=="dwh"){
		 header("location:dwh/dwh.php");
		}else if($c['level']=="toko"){
		 header("location:toko/toko.php");		 
        
		}else if($c['level']=="unit"){
		$update_log = mysql_query("UPDATE user SET status ='1' WHERE userid= '$userid'");
		include ("redirect.php");
		     }
			 
    }else{
         echo "<script>alert('Maaf User name atau password salah');</script>";
         echo "<script language='javascript'>document.location.href='index.html'</script>";
    }
}else if($op=="out"){
    unset($_SESSION['userid']);
    unset($_SESSION['level']);
	unset($_SESSION['stgl_system']);
    header("location:index.html");
}
}else{

echo "<script>alert('Maaf User Sedang Login ');</script>";
         echo "<script language='javascript'>document.location.href='index.html'</script>";
}
?>
