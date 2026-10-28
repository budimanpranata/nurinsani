<?php require_once __DIR__ . '/../../php8_compat.php'; require_once __DIR__ . '/../cek_admin.php';

session_start();



//cek apakah user sudah login

if(!isset($_SESSION['userid'])){

    die("Maaf Sesion anda habis,  Anda harus login lembali");//jika belum login jangan lanjut..

}



//cek level user

if($_SESSION['level']!="admin"){

    die("Anda bukan user");//jika bukan user jangan lanjut

}

?>	

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"

"http://www.w3.org/TR/html4/loose.dtd">

<html>

<head>

<title>Cetak Tagihan</title>

<link rel="stylesheet" href="../tabel.css" />

<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">

<style type="text/css">

<!--

.style3 {	color: #FFFFFF;

	font-size: 13px;

	font-weight: bold;

}

.header1 {color: #ffffff; background: #4682B4; font-weight: bold; font-family: Tahoma, Verdana; font-size: 13px; margin: 0px;}

body {

	background-image: url();

}

a:link {

	text-decoration: none;

}

a:visited {

	text-decoration: none;

}

a:hover {

	text-decoration: none;

}

a:active {

	text-decoration: none;

}

.style5 {font-family: Verdana, Arial, Helvetica, sans-serif}

.style6 {font-size: 12px}

.style7 {font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 12px; }

.style8 {font-size: 14px}

a {

	font-size: 12px;

}

.style9 {

	font-size: 16px;

	font-family: Verdana, Arial, Helvetica, sans-serif;

}

-->

</style>

</head>

<script>

$(document).ready(function(){
$('.hasil').hide();
$('#tombol').hide();
$('#status').change(function(){
if($('#status').val()=='CURRENT'){
$('.hasil').hide();	
$('#tombol').hide();
$('#bulan').hide();
$('#tahun').hide();
}else if($('#status').val()=='EOM'){
$('.hasil').show();
$('#tombol').show();
$('#tahun').show();
$('#bulan').show();
}
});
});

</script>



<table width="100%">

  <tr bgcolor="#669900">

  <td height="10" colspan="4" nowrap><div align="left" class="style3 style9">Nominative Kredit</div></td>

  </tr>

</table>



<table width="428" border="1" align="center">

</table>

  <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" name="form">  <table width="566" border="0" align="center">

<tr bgcolor="#669900">

  <td height="10" colspan="4" nowrap><div align="center" class="style3 style9">Criteria Pencarian </div></td>

  </tr>

<tr>

  <td width="206" height="10" nowrap bgcolor="#B3B3FF">Status Nominative</td>

  <td width="11" nowrap>        <td width="335" colspan="2" nowrap><span class="style5 style6">status</span>

      <select name="status" id="status">

        <option value="">- Select Status -</option>

        <option value="CURRENT">CURRENT</option>

        <option value="EOM">EOM</option>

             

</select>

 </td>

</tr>
  <tr>
<form>
  <div id="tombol" class="hasil">
    <td width="206" height="10" nowrap bgcolor="#B3B3FF">Bulan dan Tahun Pencarian</td>
    <td width="11" nowrap> 
    <td width="335" colspan="2" nowrap><span class="style5 style6">Bulan</span>
      <select name="bulan" id="bulan">
        <option value="">- Select Month -</option>
        <option value="1">Jan</option>
        <option value="2">Feb</option>
        <option value="3">Mar</option>
        <option value="4">Apr</option>
        <option value="5">May</option>
        <option value="6">Jun</option>
        <option value="7">Jul</option>
        <option value="8">Aug</option>
        <option value="9">Sep</option>
        <option value="10">Oct</option>
        <option value="11">Nov</option>
        <option value="12">Des</option>
      </select>      
	  <span class="style5 style6">Tahun</span>     
       <select name="tahun" id="tahun">
		<option selected="selected">Tahun</option>

		<?php
		for($i=date('Y'); $i>=date('Y')-0; $i-=1){
		echo"<option value='$i'> $i </option>";
	}

		?>

</select>

</td>

</tr></div></form>

  <td colspan="2" nowrap>&nbsp;</td>

</tr>

<tr>

  <td height="10" colspan="4">&nbsp;</td>

  </tr>

<tr bgcolor="#B3B3FF">

  <td height="10">&nbsp; </td>

    <td colspan="2"><input name="search" type="submit" id="submit2" value="Cari Report" size="40"></td>

</tr>

</table>

</form>

  <p>

    <iframe width=174 height=189 name="gToday:normal:calender/normal.js" id="gToday:normal:calender/normal.js" src="calender/ipopeng.htm" scrolling="no" frameborder="0" style="visibility:visible; z-index:999; position:absolute; top:-500px; left:-500px;"> </iframe>

  </p>



  <table width="948" class="table1">

    <tr class="header1">

      <th width="48"><div align="center">BRANCH</div></th>

      <th width="99"><div align="center">TOTAL NOA</div></th>

      <th width="194"><div align="center">TOTAL OUTSTANDING</div></th>

            <th width="194"><div align="center">CETAK NOMINATIVE</div></th>

	        <th width="194"><div align="center">EXPORT</div></th>

    </tr>

    <?php

 include "../conn2.php";

$koneksi2=open_connection2();
$userid=$_SESSION['userid'];
	$userrr=mysql_query("select kode_branch from user where userid='$userid'");
			
			while($id_user=mysql_fetch_array($userrr)){
			
				$unit=$id_user['kode_branch'];
			}


  $bulan1=$_POST ['bulan'];

  $tahun1=$_POST['tahun'];

  $tanggl=$_POST ['tanggal'];

  $status=$_POST['status'];  

  

  If (isset ($_POST ['search']))

{

if (empty($status)){

	

	echo "<script>alert('Maaf,Isi Status Terlebih Dahulu  !!!');</script>";

         echo "<script language='javascript'>document.location.href='Kr.php'</script>";}

	else{

	$_SESSION['bulan'] = $bulan1;

	$_SESSION['tahun'] = $tahun1;

	$_SESSION['status'] = $status;
	$_SESSION['unt']= $unit;

	}if ($status =='CURRENT'){

	$select = mysql_query ("Select COUNT(loan.cif) AS NOA,SUM(os) AS os, unit From loan where unit ='$unit'

	Group By unit");

 //Where Month (buss_date)='$bulan1' AND Year (buss_date)='$tahun1'

		}

		else{

			$select = mysql_query ("Select COUNT(data_loan.cif) AS NOA,SUM(os) AS os, unit From data_loan

		 Where Month(data_loan.buss_date)='$bulan1' AND Year(data_loan.buss_date)='$tahun1'Group By unit");

			

		 }

	

	if(mysql_num_rows($select)==0){

			echo "<script>alert('Maaf...data tidak ditemukan!!!');</script>";

         echo "<script language='javascript'>document.location.href='../report/Kr.php' </script>";

		 }

		



		?>



    <?php

	

	//menampilkan data

	while($data = mysql_fetch_array($select)){


//menghitung hitung

//$birthdate = $data['Birth_date'];

//$age = ((time() - strtotime($birthdate))/(60*60*24*365.2425));



		?>

    <tr>

      <td class="style6"><?php echo $data['unit']; ?></td>	

      <td class="style6"><?php echo $data['NOA'];  ?></td>

      <td class="style6"><?php echo number_format (compat_num($data['os']));  ?></td>

      <td><div align="center"><a href="/cetak_tag.php?type=<?php echo $tipe;?>" class="style6" onClick="return confirm('Apakah Anda yakin?')"><strong>Cetak</strong></a></div></td>

    <td><div align="center"><a href="javascript:;" ><img src="../excel-icon.jpeg" width="16" height="15" border="0" onClick="window.open('export_nomi.php','scrollwindow','top=200,left=300,width=800,height=500');"><strong> Rekap </strong></a></div>

</td>

	</tr>

    <?php

		

	}

	}

	?>

</table>

  <iframe width=174 height=189 name="gToday:normal:calender/normal.js" id="gToday:normal:calender/normal.js" src="calender/ipopeng.htm" scrolling="no" frameborder="0" style="visibility:visible; z-index:999; position:absolute; top:-500px; left:-500px;">

</iframe>


</body>

</html>

