<?php require_once __DIR__ . '/../php8_compat.php'; require_once __DIR__ . '/cek_admin.php';
session_start();
include "../conn2.php";
$userid=$_SESSION['userid'];
 $koneksi=open_connection2();
 $buss_date=date("y-m-d");

//cek apakah user sudah login
if(!isset($_SESSION['userid'])){
   header("location:../index.html");//jika belum login jangan lanjut..
 exit; }
if($_SESSION['level']!="admin"){
  echo "<script>alert('Anda Buka Admin');</script>";
         echo "<script language='javascript'>document.location.href='../index.html'</script>";
 exit; }
?>
<html>
<head>
<title>Tambah User</title>
<style type="text/css">
<!--
.header1 {color: #ffffff; background: #669900; font-weight: bold; font-family: Tahoma, Verdana; font-size: 13px; margin: 0px;}
.style5 {color: #FF0000}
.style9 {color: #FF0000; font-size: 14px; }
.style13 {font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 14px; }
-->
</style>
</head>
<body>
	<form action="adduser.php" method="POST" name="Gantipass" target="_self" id="Gantipass">
  <table border='1' width='65%' cellpadding='2'  cellspacing='2' bgcolor="#FFFFFF">
  </table>
  <table width="100%" border="0" cellpadding="3" cellspacing="0" bgcolor="#4682B4">
    <tr>
      <td class="header1" width="100%">Tambah User </td>
    </tr>
  </table>
  <table border='0' width='65%' cellpadding='2'  cellspacing='2' bgcolor="#FFFFFF">
    <caption>&nbsp;
    </caption>
  </table>
  <table width='100%' border='0' cellpadding='2'  cellspacing='2' bordercolor="#ECE9D8" bgcolor="#EAEAEA">
    <tr>
      <td width="25%" bgcolor="#CCCCCC"><span class="style13">User Name</span><span class="style9">* </span></td>
      <td width="75%" bgcolor="#CCCCCC"><input name="username" type="text" id="username" size="30" maxlength="50"/></td>
    </tr>
    <tr>
      <td><span class="style13">Password <span class="style5">* </span></span></td>
      <td><input name="pass" type="password" id="pass" size="30" maxlength="15"/></td>
    </tr>
    <tr>
      <td bgcolor="#CCCCCC"><span class="style13"><strong>Confirmasi</strong> Password <span class="style5">*</span></span></td>
      <td bgcolor="#CCCCCC"><input name="conf_pass" type="text" id="conf_pass" size="30" maxlength="15"/></td>
    </tr>
    <tr>
      <td><span class="style13">Level <span class="style5">*</span></span></td>
      <td><select name="level" id="level">
        <option value="admin" selected>admin</option>
        <option value="user">user</option>
        <option value="lead">lead</option>
        <option value="smo">mr</option>
        <option value="spvs">ao</option>
        <option value="kp">kp</option>
      </select></td>
    
<tr>
      <td bgcolor="#CCCCCC"><span class="style13">Name<span class="style5">* </span></span></td>
      <td bgcolor="#CCCCCC"><input name="nama" type="text" id="nama" size="30"/></td>
    </tr>

  <tr>
      <td><span class="style13">Branch Code <span class="style5">* </span></span></td>
      <td><input name="branch" type="text" id="branch" size="30" maxlength="4"/></td>
    </tr>
</tr>
    <tr>
      <td bgcolor="#CCCCCC"></td>
      <td bgcolor="#CCCCCC"><input name="kirim" type="submit" class="header1" id="kirim" value="Add User">
          <label>
          <input type="reset" name="Reset" id="button" value="Batal">
        </label></td>
    </tr>
  </table>
</form>
</body>
</html>


