<?php require_once __DIR__ . '/../../php8_compat.php';
session_start();
ob_start();
$userid=$_SESSION['userid'];
$tgl_system=$_SESSION['stgl_system'];
//cek apakah user sudah login
if(!isset($_SESSION['stgl_system'])){
echo "<script>alert('Maaf,.tanggal system belum di setup');</script>";

   header("location:param_tgl.php");
 exit; }
if(!isset($_SESSION['userid'])){
   header("location:index.html");
 exit; }
if($_SESSION['level']!="unit"){
   header("location:index.html");
 exit; }
 include "../conn2.php";
  include "../function.php";
  $koneksi=open_connection2();
  
$kel = mysql_query ("select kode_branch from user where userid='$userid'");
$kkel = mysql_fetch_array($kel);
$unit=$kkel['kode_branch'];

	$norek=$_POST ['kode_rekening'];
   $bulan =$_POST['bulan'];
	$tahun =$_POST['tahun'];


$select = mysql_query("select saldo_awal,saldo_akhir,kode_rekening,nama_rekening from tabel_master_konsol WHERE kode_rekening ='$norek' and unit ='$unit' and month(tanggal_awal)='$bulan' and Year(tanggal_awal)='$tahun'");
            while($row = mysql_fetch_array($select)){
                //$next_schedule=$row['next_schedule'];
                $saldo_awal=$row['saldo_awal'];
				$kode_rekening=$row['kode_rekening'];
				$nama_rekening=$row['nama_rekening'];
				$saldo_akhir=$row['saldo_akhir'];
				
                 }	  
?>
<script type="text/javascript">
 function wajibAngka(evt) {
 var charCode = (evt.which) ? evt.which : event.keyCode
 if (charCode > 31 && (charCode < 48 || charCode > 57))
 return false;
 }
</script>
    <script type="text/javascript" src="../jquery.js"></script>
    <script>
    function suggest(inputString){
        if(inputString.length == 0) {
            $('#suggestions').fadeOut();
        } else {
        $('#country').addClass('load');
            $.post("../autosuggesst.php", {queryString: ""+inputString+""}, function(data){
                if(data.length >0) {
                    $('#suggestions').fadeIn();
                    $('#suggestionsList').html(data);
                    $('#country').removeClass('load');
                }
            });
        }
    }
   
    function fill(thisValue) {
        $('#country').val(thisValue);
        setTimeout("$('#suggestions').fadeOut();", 100);
    }
   
    function fill2(thisValue) {
        $('#kode').val(thisValue);
        setTimeout("$('#suggestions').fadeOut();", 100);
    }
   
    </script>
<script>
    function formatCurrency(num) {
    num = num.toString().replace(/\$|\,/g,'');
    num = num.toString().replace(/[^0-9]/g,'');
    if(isNaN(num))
    num = "0";
    sign = (num == (num = Math.abs(num)));
    num = Math.floor(num*100+0.50000000001);
    cents = num%100;
    num = Math.floor(num/100).toString();
    if(cents<10)
    cents = "0" + cents;
    for (var i = 0; i < Math.floor((num.length-(1+i))/3); i++)
    num = num.substring(0,num.length-(4*i+3))+'.'+
    num.substring(num.length-(4*i+3));
    return (((sign)?'':'-') + 'Rp. ' + num + '');
    }
    </script>
   
</script>


<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Transaksi NI</title>
<link rel="stylesheet" href="../tabel.css" />
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<style type="text/css">
<script type="text/javascript" src="../jquery.js"></script>
        <link href="../style.css" rel="stylesheet" type="text/css" media="screen" />
        <link rel="stylesheet" href="../css/bootstrap.min.css"/>
<script>

<!--
.style3 {    color: #FFFFFF;
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
a {
    font-size: 12px;
}
.style9 {
    font-size: 16px;
    font-family: Verdana, Arial, Helvetica, sans-serif;
}
-->

    #result {
        height:20px;
        font-size:12px;
        font-family:Arial, Helvetica, sans-serif;
        color:#333;
        padding:5px;
        margin-bottom:10px;
        background-color:#FFFF99;
    }
    #country{
        padding:3px;
        border:1px #CCC solid;
        font-size:12px;
    }
    .suggestionsBox {
        position: absolute;
        left: 0px;
        top:40px;
        margin: 26px 0px 0px 0px;
        width: 200px;
        padding:0px;
        background-color:#999999;
        border-top: 3px solid #999999;
        color: #fff;
    }
    .suggestionList {
        margin: 0px;
        padding: 0px;
    }
    .suggestionList ul li {
        list-style:none;
        margin: 0px;
        padding: 6px;
        border-bottom:1px dotted #666;
        cursor: pointer;
    }
    .suggestionList ul li:hover {
        background-color: #FC3;
        color:#000;
    }
    ul {
        font-family:Arial, Helvetica, sans-serif;
        font-size:11px;
        color:#FFF;
        padding:0;
        margin:0;
    }
   
    .load{
    background-image:url(loader.gif);
    background-position:right;
    background-repeat:no-repeat;
    }
   
    #suggest {
        position:relative;
    }
    .style9 {
    font-size: 16px;
    font-family: Verdana, Arial, Helvetica, sans-serif;
}
.style3 {    color: #FFFFFF;
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
.style12 {font-family: sans-serif}
</style>
</head>

<table width="100%" cellpadding="1" bordercolor="#CCCCCC" bgcolor="#2F6B4F">
  <tr>
    <td height="20" bordercolor="#FFFFFF" bgcolor="#2F6B4F" class="style3 style9">Transaksi Setoran dan Penarikan
    <div align="right"></div></td>
  </tr>
</table>

<table width="428" border="1" align="center">
</table>
<form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" name="postform">  <table width="543" border="0" align="center">
<tr bgcolor="#2F6B4F">
  <td height="10" colspan="4" nowrap><div align="center" class="style3 style9">Input Data </div></td>
  </tr>
  <tr>
  <td width="206" height="10" nowrap bgcolor="#B3B3FF">Bulan dan Tahun Pencarian</td>
    <td width="16" nowrap>        <div align="center"><strong>:</strong></div></td>
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
</tr>
<tr>
    <td width="203" height="10" nowrap bgcolor="#B3B3FF"><div align="left" class="style5 style6">No Perkiraan</div></td>
    <td width="16" nowrap>        <div align="center"><strong>:</strong></div></td>
    <td width="310" colspan="2" nowrap>
     <div id="suggest">
                       <input type="text" onKeyUp="suggest(this.value);" name="kode_rekening" autocomplete="off" onBlur="fill2();" id="kode" size="15"/>
                       <div class="suggestionsBox" id="suggestions" style="display: none;"> <img src="arrow.png" style="position: relative; top: -12px; left: 30px;" alt="upArrow" />
                       <div class="suggestionList" id="suggestionsList"> &nbsp; </div>
                       </div>
       
      <input type="text" disabled="disabled" name="nama_rekening" onBlur="fill();" id="country" />
</div>
</tr>

<tr>
  <td height="10" colspan="4">&nbsp;</td>
  </tr>
<tr bgcolor="#B3B3FF">
  <td height="10">&nbsp; </td>
  <td><div align="center"></div></td>
  <td colspan="2"><input name="search" type="submit" id="submit2" value="Cari" size="40"></td>
</tr>
</table>
</form>
<iframe width=174 height=189 name="gToday:normal:../calender/normal.js" id="gToday:normal:../calender/normal.js" src="../calender/ipopeng.htm" scrolling="no" frameborder="0" style="visibility:visible; z-index:999; position:absolute; top:-500px; left:-500px;">
</iframe>
<table class="table1"
<tr class="table1">

</tr>
<table >
  <tr>
    <td width="143"><span class="style12">No Perkiraan </span></td>
    <td width="24">:</td>
    <td width="151"><?php echo $kode_rekening; ?></td>
  </tr>
  <tr>
    <td><span class="style12">Nama Perkiraan </span></td>
    <td>:</td>
    <td><?php echo $nama_rekening; ?></td>
  </tr>
  <tr>
    <td><span class="style12">Saldo Awal </span></td>
    <td>:</td>
    <td><?php echo $saldo_awal; ?></td>
  </tr>
  <tr>
    <td><span class="style12">Saldo Akhir </span></td>
    <td>:</td>
    <td><?php echo $saldo_akhir; ?></td>
  </tr>
</table>
<table border="2" bgcolor="" width="100%" class="table1">


  <tr class="table1">
 
    
    <th>Tanggal </th>
    <th>No Bukti</th>
	<th>Keterangan</th>
    <th>Debet</th>
    <th>Kredit</th>
	<th>Saldo</th>
  </tr>
  <?php
 
  //$buss_date=date("Y-m-d H:i:s");
$buss_date=$tgl_system;
 
                            
           
  If (isset($_POST ['search']))
	 
{

		$bulan_lalu=$bulan-1;
	
        $select = mysql_query("select normal from coa WHERE kode_rek ='$norek'");
            while($row = mysql_fetch_array($select)){
                //$next_schedule=$row['next_schedule'];
                $normal=$row['normal'];
			}
			var_dump($normal);
	if(mysql_num_rows($select)){
if (empty($norek)){
//if (empty($norek)||empty($bulan)||empty($tahun)){

    echo "<script>alert('Maaf, Bulan,tahun atau Nomor Perkiraan belum di isi');</script>";
         echo "<script language='javascript'>document.location.href='buku_besars.php'</script>";}
   
    else{
                   
			$select = mysql_query("select saldo_awal from tabel_master_konsol WHERE kode_rekening ='$norek' and unit ='$unit' and month(tanggal_awal)='$bulan' and Year(tanggal_awal)='$tahun'");
            while($row = mysql_fetch_array($select)){
                //$next_schedule=$row['next_schedule'];
                $saldo_awal=$row['saldo_awal'];
				
                 }	  
				   if($normal=="debet"){
					
					$select1 = mysql_query("SET @bal = '$saldo_awal';");          
     $select = mysql_query("SELECT tanggal_transaksi as tanggal,kode_transaksi as no_bukti,keterangan_transaksi,debet,kredit,@bal:=(@bal+debet)-kredit as balance FROM tabel_transaksi
        where kode_rekening='$norek' and unit ='$unit' and month(tanggal_transaksi)='$bulan' and year(tanggal_transaksi)='$tahun' order by id_transaksi");
				   }else{
					$select1 = mysql_query("SET @bal ='$saldo_awal';");     
	 $select = mysql_query("SELECT tanggal_transaksi as tanggal,kode_transaksi as no_bukti,keterangan_transaksi,debet,kredit,@bal:=(@bal+kredit)-debet as balance FROM tabel_transaksi
        where kode_rekening='$norek' and unit ='$unit' and month(tanggal_transaksi)='$bulan' and year(tanggal_transaksi)='$tahun'  order by id_transaksi");
					   
				   }
				   
      
    }
	
      }
            else{
            echo "<script>alert('Maaf,Data tidak ditemukan');</script>";
            echo "<script language='javascript'>document.location.href='buku_besar.php'</script>";
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
    <td class="style6"><?php echo $data['tanggal']; ?></td>
    <td class="style6"><?php echo $data['no_bukti'];  ?></td>
	    <td class="style6"><?php echo $data['keterangan_transaksi'];  ?></td>
	<td class="style6"><?php echo number_format (compat_num($data['debet'])); ?></td>
    <td class="style6"><?php echo number_format (compat_num($data['kredit'])); ?></td>
    <td class="style6"><?php echo number_format (compat_num($data['balance'])); ?></td>
   
  </tr>
  <?php
       
    }
    }

    ?>
 
</table>
<p>&nbsp;</p>

 <iframe width=174 height=189 name="gToday:normal:calender/normal.js" id="gToday:normal:calender/normal.js" src="calender/ipopeng.htm" scrolling="no" frameborder="0" style="visibility:visible; z-index:999; position:absolute; top:-500px; left:-500px;">
</iframe>


</body>

</html>
