<?php require_once __DIR__ . '/php8_compat.php';
session_start();
//cek apakah user sudah login
if(!isset($_SESSION['userid'])){
     header("location:index.html");//jika belum login jangan lanjut..
 exit; }
include "conn2.php";
$koneksi=open_connection2();
$userid=$_SESSION['userid'];
$data = mysql_query ("select kode_branch from user where userid ='$userid'");
$r = mysql_fetch_array($data);
$branch=$r['kode_branch'];

$today=date("Ymd");
$count = mysql_query ("select count(code_kel) AS last FROM kelompok where code_unit ='$branch'");
$hit = mysql_fetch_array($count);
$lastNoTransaksi = $hit['last'];
$lastNoUrut = $lastNoTransaksi;
$nextNoUrut = $lastNoUrut + 1;
$nextNoTransaksi = sprintf('%04s', $nextNoUrut);
$cif="$branch-$nextNoTransaksi";
?>
<html>
<head>
<script type="text/javascript" charset="utf-8">
function validateForm() 
{
 var okSoFar=true 
 with (document.postform)
 {
  if (code_kel.value=="" && okSoFar)
  {
    okSoFar=false
    alert("Masukkan KODE KELOMPOK")
    code_kel.focus()
  }
  if (nama.value=="" && okSoFar)
  {
    okSoFar=false
	alert("Masukkan NAMA KELOMPOK")
    nama_kel.focus()
  }
      if (okSoFar==true)  submit();
 }
}

		</script>

<title>Cari</title>
	
	       <style type="text/css">
	<!--
.header1 {color: #ffffff; background: #4682B4; font-weight: bold; font-family: Tahoma, Verdana; font-size: 13px; margin: 0px;}
.style14 {	color: #FFFFFF;
	font-weight: bold;
}
.style1 {color: #CCCCCC}
.style15 {
	color: #000000;
	font-style: italic;
}
.style16 {	color: #FF0000;
	font-weight: bold;
}
.style19 {color: #CCCCCC; font-size: 12px; }
-->
       </style>
</head>

<body onLoad="document.postform.elements['nopen'].focus();">
<table width="100%" border="0" cellpadding="3" cellspacing="0" bgcolor="#4682B4">
  <tr>
    <td width="100%" bgcolor="#669900" class="style14">Pendaftaran Kelompok dan Calon Anggota </td>
  </tr>
</table>
<form action="input_kel.php" method="post" name="postform">
  <table width="1043" border="0">
<tr>
    <td width="263" bgcolor="#CCCCCC">Kode Kelompok <span class="style16">* </span></td>
    <td width="349" bgcolor="#CCCCCC"><span class="style1">
      <input name="code_kel" type="text" id="code_kel" value="<?php echo $cif; ?>" size="30" maxlength="18" readonly/>
    </span></td>
    <td width="417" colspan="2" bgcolor="#CCCCCC"><input name="cari" type="button" class="upload"onClick="javascript:validateForm()" value="Simpan"></td>
</tr>
<tr>
    <td bgcolor="#E8E8E8">Kode AO <span class="style16">* </span></td>
    <td bgcolor="#E8E8E8"><span class="style1">
     <select name="cao" id ="cao" type="text">
		  <option value="">- Select Kode AO -</option>
	  <?php
              		$query = "select * from ao where kode_unit='$branch' ";
					$result = mysql_query($query);
					while ($kanwil=mysql_fetch_array($result))
					{
			  		  	echo "<option value=$kanwil[0]>$kanwil[1]</option>";
					}
              		echo"</select>";?>
      </span></td>
<td colspan="2" bgcolor="#E8E8E8">&nbsp;</td>
</tr>
<tr>
    <td bgcolor="#CCCCCC">Nama Kelompok <span class="style16">* </span></td>
    <td bgcolor="#CCCCCC"><span class="style1">
      <input name="nama" type="text" id="nama" size="30" maxlength="30"/>
    </span></td>
    <td colspan="2" bgcolor="#CCCCCC">&nbsp;</td>
</tr>
<tr>
    <td bgcolor="#E8E8E8">Alamat Kelompok <span class="style16">* </span></td>
    <td bgcolor="#E8E8E8"><span class="style1">
     <textarea name="alamat" type="text" id="alamat" size="55"/></textarea>
	 <td colspan="2" bgcolor="#E8E8E8">&nbsp;</td>
</td>
</table>

</form>
<iframe width=174 height=189 name="gToday:normal:calender/normal.js" id="gToday:normal:calender/normal.js" src="calender/ipopeng.htm" scrolling="no" frameborder="0" style="visibility:visible; z-index:999; position:absolute; top:-500px; left:-500px;">
</iframe>
</body>
</html>
