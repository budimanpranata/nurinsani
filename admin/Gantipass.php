<?php require_once __DIR__ . '/../php8_compat.php'; require_once __DIR__ . '/cek_admin.php';
session_start();
include "../conn2.php";
$koneksi=open_connection2();
$userid=$_SESSION['userid'];
$result = mysql_query("SELECT * FROM user WHERE userid='$userid'");
while($row = mysql_fetch_array($result)){
	$user = $row['userId'] ;
	$nama =$row['nama'] ;
	$password= $row ['pass'];
}
?>
<html>
<head>
<title>Change Password</title>
<style type="text/css">
<!--
.header1 {color: #ffffff; background: #4682B4; font-weight: bold; font-family: Tahoma, Verdana; font-size: 13px; margin: 0px;}
.style5 {color: #FF0000}
.style9 {color: #FF0000; font-size: 14px; }
.style13 {font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 14px; }
.style14 {
	color: #FFFFFF;
	font-weight: bold;
}
-->
</style>
</head>
<body>
	<form action="changepass.php" method="POST" name="Gantipass" target="_self" id="Gantipass">
  <table border='1' width='65%' cellpadding='2'  cellspacing='2' bgcolor="#FFFFFF">
  </table>
  <table width="100%" cellpadding="1" bordercolor="#CCCCCC" bgcolor="#CCCCCC">
    <tr>
      <td height="20" bordercolor="#FFFFFF" bgcolor="#2F6B4F" class="style3 style8"><span class="style14">Cari Data Disbursement
          </span>
      <div align="right"></div></td>
    </tr>
  </table>
  <table border='0' width='65%' cellpadding='2'  cellspacing='2' bgcolor="#FFFFFF">
    <caption>&nbsp;
    </caption>
  </table>
  <table width='100%' border='0' cellpadding='2'  cellspacing='2' bordercolor="#ECE9D8" bgcolor="#EAEAEA">
    <tr>
      <td width="25%" bgcolor="#CCCCCC"><span class="style13">User Name</span><span class="style9">* </span></td>
      <td width="75%" bgcolor="#CCCCCC"><input name="userid" type="text" id="userid" value="<?php echo $userid; ?>" size="30" disabled="disabled" maxlength="50"/></td>
    </tr>
    <tr>
      <td><span class="style13">Password <strong>Lama</strong>   <span class="style5">* </span></span></td>
      <td><input name="passlama" type="password" id="passlama" size="30" maxlength="50" autocomplete="current-password"/></td>
    </tr>
    <tr>
      <td bgcolor="#CCCCCC"><span class="style13">Password <strong>Baru</strong>  <span class="style5">* </span></span></td>
      <td bgcolor="#CCCCCC"><input name="passbaru" type="password" id="passbaru" size="30" maxlength="50" autocomplete="new-password"/> <small>minimal 8 karakter</small></td>
    </tr>
    <tr>
      <td><span class="style13"><strong>Confirmasi</strong> Password  <span class="style5">* </span></span></td>
      <td><input name="passlagi" type="password" id="passlagi" size="30" maxlength="50" autocomplete="new-password"/></td>
    </tr>
    <tr>
      <td bgcolor="#CCCCCC"></td>
      <td bgcolor="#CCCCCC"><input name="kirim" type="submit" id="kirim" value="change">
          <label>
          <input type="reset" name="Reset" id="button" value="Batal">
        </label></td>
    </tr>
  </table>
</form>
</body>
</html>


