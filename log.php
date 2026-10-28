<?php require_once __DIR__ . '/php8_compat.php';
include "conn2.php";
$koneksi=open_connection2();
function antiinjection($data){
  $filter_sql = mysql_real_escape_string(stripslashes(strip_tags(htmlspecialchars($data,ENT_QUOTES))));
  return $filter_sql;
  }

// Batas percobaan login: MAKS_GAGAL kali gagal per userid + IP, lalu dikunci LAMA_KUNCI detik.
define('MAKS_GAGAL', 5);
define('LAMA_KUNCI', 900);
function file_gagal_login($userid){
  $dir = sys_get_temp_dir() . '/nurinsani_login';
  if (!is_dir($dir)) { @mkdir($dir, 0700, true); }
  return $dir . '/' . md5(strtolower($userid) . '|' . $_SERVER['REMOTE_ADDR']);
  }
function data_gagal_login($userid){
  $d = @json_decode(@file_get_contents(file_gagal_login($userid)), true);
  if (!is_array($d) || time() - $d['waktu'] > LAMA_KUNCI) { $d = array('jumlah' => 0, 'waktu' => time()); }
  return $d;
  }
function catat_gagal_login($userid){
  $d = data_gagal_login($userid);
  $d['jumlah']++;
  $d['waktu'] = time();
  @file_put_contents(file_gagal_login($userid), json_encode($d));
  }
function kembali_ke_login($pesan){
  echo "<script>alert('$pesan');</script>";
  echo "<script language='javascript'>document.location.href='index.html'</script>";
  exit;
  }

session_start();
$userid = antiinjection(isset($_POST['userid']) ? $_POST['userid'] : '');
$psw = antiinjection(isset($_POST['psw']) ? $_POST['psw'] : '');
$pass = md5($psw);
$op = isset($_GET['op']) ? $_GET['op'] : '';
$buss_date=date("y-m-d");

if (!isset($_SESSION['userid'])){
if($op=="in"){
    if ($userid == '' || $psw == '') {
        kembali_ke_login('Maaf User name atau password salah');
    }
    if (data_gagal_login($userid)['jumlah'] >= MAKS_GAGAL) {
        kembali_ke_login('Terlalu banyak percobaan login gagal. Coba lagi dalam ' . (LAMA_KUNCI / 60) . ' menit.');
    }
    $cek = mysql_query("SELECT datediff(current_date(), last_update) as selisih,level,userid FROM user WHERE userid='$userid' AND pass='$pass'");
    if(mysql_num_rows($cek)!=1){//jika berhasil akan bernilai 1
        catat_gagal_login($userid);
        kembali_ke_login('Maaf User name atau password salah');
    }
    @unlink(file_gagal_login($userid));
    $c = mysql_fetch_array($cek);

    $cek_tgl = mysql_fetch_array(mysql_query("SELECT param_tgl FROM param_tgl WHERE unit ='$userid'")) or die(mysql_error());
    $cek_eod = mysql_fetch_array(mysql_query("SELECT param_tgl FROM param_tgl WHERE nama_param_tgl ='EOD_MOB'")) or die(mysql_error());
$tgl_eod =$cek_eod['param_tgl'];
$tgl=$cek_tgl['param_tgl'];
 echo "<script>alert('Parameter tanggal Unit adalah .$tgl.');</script>";

if($c['selisih']>=30){
        // hanya boleh membuka halaman ganti password, belum dianggap login
        session_regenerate_id(true);
        $_SESSION['ganti_pass_userid'] = $c['userid'];
echo "<script>alert('Password Expired ');</script>";
         echo "<script language='javascript'>document.location.href='Gantipass.php'</script>";
}
elseif($tgl==$tgl_eod){

echo "<script>alert('Parameter tanggal libur masih aktif');</script>";
         echo "<script language='javascript'>document.location.href='index.html'</script>";
}else{
        // login berhasil: id session baru supaya id lama (fixation) tidak bisa dipakai
        session_regenerate_id(true);
        $_SESSION['stgl_system']=$tgl;
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
}else if($c['level']=="audit"){
		 header("location:audit/fin.php");
}else if($c['level']=="pengurus"){
		 header("location:pengurus/pengurus.php");

		 }else if($c['level']=="admin"){
		 header("location:admin/admin.php");
        	 }else if($c['level']=="ho"){
		 header("location:ho/ho.php");
        	 }else if($c['level']=="dwh"){
		 header("location:dwh/dwh.php");
	 	 }else if($c['level']=="toko"){
		 header("location:toko/toko.php");
        }else if($c['level']=="unit"){
		$update_log = mysql_query("UPDATE user SET status ='1',tgl_open='$buss_date' WHERE userid= '$userid'");
		include ("redirect.php");
		     }
	}
}else if($op=="out"){
    unset($_SESSION['userid']);
    unset($_SESSION['level']);
    header("location:index.html");
}
}else{

echo "<script>alert('Maaf User Sedang Login ');</script>";
         echo "<script language='javascript'>document.location.href='index.html'</script>";
}
?>
