<?php require_once __DIR__ . '/php8_compat.php';
session_start();
include "conn2.php";
?>
<html>
<head>

<style type="text/css">
<!--
.header1 {color: #ffffff; background: #2F6B4F; font-weight: bold; font-family: Tahoma, Verdana; font-size: 13px; margin: 0px;}
.style1 {
	color: #FFFFFF;
	font-weight: bold;
}
-->
</style>
<body>
<table width="100%" border="0" cellpadding="3" cellspacing="0" bgcolor="#2F6B4F">
  <tr>
    <td width="100%" bgcolor="#2F6B4F"><span class="style1">Home</span></td>
  </tr>
</table>
</body>
</html>
<?php
$koneksi=open_connection2();
$userid=$_SESSION['userid'];
$result = mysql_query("SELECT * FROM user WHERE userid='$userid'");
while($row = mysql_fetch_array($result)){
  $cek_os = mysql_fetch_array(mysql_query("SELECT sum(os) as os ,sum(saldo_margin) as saldo_margin FROM loan  WHERE unit  ='$userid' group by unit")) ;
  $os= $cek_os['os']; 
  $margin =$cek_os['saldo_margin'];

  $cek_coa_os = mysql_fetch_array(mysql_query("SELECT saldo_akhir  FROM tabel_master  WHERE unit  ='$userid' and kode_rekening ='1411000'")) ;
  $coa_1413= $cek_coa_os['saldo_akhir'];

  $cek_coa_os_rest = mysql_fetch_array(mysql_query("SELECT saldo_akhir  FROM tabel_master  WHERE unit  ='$userid' and kode_rekening ='1413000'")) ;
  $coa_1411= $cek_coa_os_rest['saldo_akhir'];

  $coa_os = $coa_1413 + $coa_1411;  

  if($os == $coa_os ){
    $status_os ="Done";
  }else{
    $status_os ="Tidak Sesuai";
    $warna_belakang = "#99FFFF";
  }

  $cek_coa_margin = mysql_fetch_array(mysql_query("SELECT saldo_akhir  FROM tabel_master  WHERE unit  ='$userid' and kode_rekening ='1423000'")) ;
  $coa_margin= $cek_coa_margin['saldo_akhir'];

  if($margin == $coa_margin ){
    $status_margin ="Done";
  }else{
    $status_margin ="Tidak Sesuai";
    $warna_belakang = "#99FFFF";
  }

  $cek_coa_pokok = mysql_fetch_array(mysql_query("SELECT saldo_akhir  FROM tabel_master  WHERE unit  ='$userid' and kode_rekening ='3102000'")) ;
  $coa_pokok= $cek_coa_pokok['saldo_akhir'];
  $cek_simpanan_pokok = mysql_fetch_array(mysql_query("SELECT sum(KREDIT-DEBIT) as simpanan_pokok  FROM simpanan_pokok  WHERE unit  ='$userid' group by unit")) ;
  $pokok= $cek_simpanan_pokok['simpanan'];
  if($pokok == $coa_pokok ){
    $status_pokok ="Done";
  }else{
    $status_pokok ="Tidak Sesuai";
    $warna_belakang = "#99FFFF";
  }

  $cek_coa_wajib = mysql_fetch_array(mysql_query("SELECT saldo_akhir  FROM tabel_master  WHERE unit  ='$userid' and kode_rekening ='3202000'")) ;
  $coa_wajib= $cek_coa_wajib['saldo_akhir'];
  $cek_simpanan_wajib = mysql_fetch_array(mysql_query("SELECT sum(KREDIT-DEBIT) as simpanan_wajib  FROM simpanan_wajib  WHERE unit  ='$userid' group by unit")) ;
  $wajib= $cek_simpanan_wajib['simpanan'];
  if($wajib == $coa_wajib ){
    $status_wajib ="Done";
  }else{
    $status_wajib ="Tidak Sesuai";
    $warna_belakang = "#99FFFF";
  }

  $cek_coa_wadiah = mysql_fetch_array(mysql_query("SELECT saldo_akhir  FROM tabel_master  WHERE unit  ='$userid' and kode_rekening ='2101000'")) ;
  $coa_wadiah= $cek_coa_wadiah['saldo_akhir'];
  $cek_simpanan = mysql_fetch_array(mysql_query("SELECT sum(KREDIT-DEBIT) as simpanan  FROM simpanan  WHERE unit  ='$userid' group by unit")) ;
  $simpanan= $cek_simpanan['simpanan'];
  if($simpanan == $coa_wadiah ){
    $status_wadiah ="Done";
  }else{
    $status_wadiah ="Tidak Sesuai";
    $warna_belakang = "#99FFFF";
  }



echo "<h>Selamat Datang ".$row['nama']. " Di Sistem Informasi KSPPS NurInsani </h>";
echo "<br>";
echo "<table width=\"500\" border=\"0\" cellpadding=\"3\" cellspacing=\"1\" bgcolor=\"#ffffff\">";
echo "<tr bgcolor=\"#4F9A72\">";
echo '<tr><th>Jenis Transaksi </th><th>Nominatif</th><th>Neraca</th> <th>status</th></tr>';
echo "<tr><td>Outstanding </td><td>".number_format(compat_num($os))."</td><td align ='right'> ".number_format(compat_num($coa_os))."</td> <td bgcolor=\"$warna_belakang\"\n> ".$status_os."</td> </tr>";
echo "<tr><td>Saldo Margin</td><td>".number_format(compat_num($margin))."</td><td align ='right'>".number_format(compat_num($coa_margin))."</td> <td bgcolor=\"$warna_belakang\"\n> ".$status_margin." </td></tr>";
echo "<tr><td>Simpanan Wadiah</td><td>".number_format(compat_num($simpanan))."</td><td align ='right'> ".number_format(compat_num($coa_wadiah))."</td><td bgcolor=\"$warna_belakang\"\n> ".$status_wadiah." </td></tr>";
echo "<tr><td>Simpanan Pokok</td><td>".number_format(compat_num($pokok))."</td><td align ='right'>".number_format(compat_num($coa_pokok))."</td><td bgcolor=\"$warna_belakang\"\n> ".$status_pokok."</td></tr>";
echo "<tr><td>Simpanan Wajib</td><td>".number_format(compat_num($wajib))."</td><td align ='right'>".number_format(compat_num($coa_wajib))." </td> <td bgcolor=\"$warna_belakang\"\n> ".$status_wajib." </td></tr>";
echo "</table>";

}


//cek apakah user sudah login
if(!isset($_SESSION['userid'])){
    die("Anda belum login");//jika belum login jangan lanjut..
}

//cek level user
if($_SESSION['level']!="unit"){
    die("Anda bukan user");//jika bukan user jangan lanjut
}
?>	

