<?php require_once __DIR__ . '/../php8_compat.php';
session_start();
ob_start();
//error_reporting(0);
$userid=$_SESSION['userid'];
$tgl_system=$_SESSION['stgl_system'];
include "../conn2.php";
include "../function.php";
$koneksi=open_connection2();
$level_area=mysql_fetch_array(mysql_query("select area from level where userid='$userid'"));
$area=$level_area['area'];


//cek apakah user sudah login

if(!isset($_SESSION['userid'])){
   header("location:index.html");
 exit; }
if($_SESSION['level']!="ho"){
   header("location:../index.html");
 exit; }

?>
    <script type="text/javascript" src="../jquery.js"></script>
    <script>
    function suggest(inputString){
        if(inputString.length == 0) {
            $('#suggestions').fadeOut();
        } else {
        $('#country').addClass('load');
            $.post("autosuggest_cif.php", {queryString: ""+inputString+""}, function(data){
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
    function fill3(thisValue) {
        $('#code_kel').val(thisValue);
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
        <link href="../../style.css" rel="stylesheet" type="text/css" media="screen" />
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
    .style10 {
    font-family: Verdana, Arial, Helvetica, sans-serif;
    font-size: 12px;
}

    .style11 {font-size: small}
    </style>
</head>

<table width="100%" cellpadding="1" bordercolor="#CCCCCC" bgcolor="#669900">
  <tr>
    <td height="20" bordercolor="#FFFFFF" bgcolor="#669900" class="style3 style9">Mutasi CIF antar AP
    <div align="right"></div></td>
  </tr>
</table>

<table width="428" border="1" align="center">
</table>
  <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" name="postform">  <table width="543" border="0" align="center">
<tr bgcolor="#669900">
  <td height="10" colspan="4" nowrap><div align="center" class="style3 style9">Input Data </div></td>
  </tr>

<tr>
    <td width="203" height="10" nowrap bgcolor="#B3B3FF"><div align="left" class="style5 style6">NAMA/CIF </div></td>
    <td width="16" nowrap>        <div align="center"><strong>:</strong></div></td>
    <td width="310" colspan="2" nowrap>
     <div id="suggest">
                       <input type="text" onKeyUp="suggest(this.value);" name="kode_rekening"  onBlur="fill2();" id="kode" size="15"/>
                       <div class="suggestionsBox" id="suggestions" style="display: none;"> <img src="arrow.png" style="position: relative; top: -12px; left: 30px;" alt="upArrow" />
                       <div class="suggestionList" id="suggestionsList"> &nbsp; </div>
                       </div>
        
      <input type="text" disabled="disabled" name="nama_rekening" onBlur="fill();" id="country" />
</div></tr>
<tr>
  <td height="10" nowrap bgcolor="#B3B3FF"><span class="style7">AP Tujuan </span></td>
  <td nowrap><div align="center"><strong>:</strong></div></td>
  <td colspan="2" nowrap><select name="select" id="select" size="1">
  <option selected>Pilih Salah satu</option>
                  <?php
              			$query = "select * from branch where code_region='$area' order by kode_branch ";
					$result = mysql_query($query);
					while ($kanwil=mysql_fetch_array($result))
					{
			  		  	echo "<option value=$kanwil[0]>$kanwil[1]</option>";
					}
              		echo"</select>";?>
    </select></td>
</tr>
<tr>
  <td height="10" colspan="4"><label></label></td>
  </tr>
<tr bgcolor="#B3B3FF">
  <td height="10">&nbsp; </td>
  <td><div align="center"></div></td>
  <td colspan="2"><input name="search" type="submit" id="submit2" value="Proses" size="40"></td>
</tr>
</table>
</form>
<iframe width=174 height=189 name="gToday:normal:calender/normal.js" id="gToday:normal:calender/normal.js" src="calender/ipopeng.htm" scrolling="no" frameborder="0" style="visibility:visible; z-index:999; position:absolute; top:-500px; left:-500px;">
</iframe>

<table border="4" bgcolor="" width="100%" class="table1">
  <tr class="table1">
    <th>CIF</th>
    <th>NAMA</th>
    <th>AP</th>
    <th>OS</th>
    <th>AP TUJUAN</th>
   
  </tr>
  <?php
  $cif=$_POST['kode_rekening'];
  $ap_tujuan=$_POST['select'];
  
            
  If (isset($_POST ['search']))
{
if (empty($cif)||empty($ap_tujuan)){
    echo "<script>alert('Maaf, isi cif yang benar');</script>";
         echo "<script language='javascript'>document.location.href='mutasi_cif.php'</script>";}
    
    else{
		
			$anggota=mysql_query("select anggota.CUST_SHORT_NAME,anggota.CIF,os, branch.unit from anggota 
			inner join loan on loan.cif = anggota.cif 
			Inner join branch on branch.kode_branch = anggota.unit
			where anggota.cif='$cif'");
		$query=mysql_query("update anggota set unit ='$ap_tujuan',userid='$ap_tujuan' where cif='$cif'");
		$query=mysql_query("update loan set unit ='$ap_tujuan',userid='$ap_tujuan' where cif='$cif'");
		$query=mysql_query("update simpanan set unit ='$ap_tujuan',userid='$ap_tujuan' where cif='$cif'");
		$query=mysql_query("update simpanan_pokok set unit ='$ap_tujuan', userid='$ap_tujuan' where cif='$cif'");
		$query=mysql_query("update simpanan_wajib set unit ='$ap_tujuan', userid='$ap_tujuan' where cif='$cif'");
$query=mysql_query("update calon_loan set unit ='$ap_tujuan',userid='$ap_tujuan' where cif='$cif'");
		
		

    }    
  ?>
 
  <?php
    
    //menampilkan data
    while($data = mysql_fetch_array($anggota)){

//menghitung hitung
//$birthdate = $data['Birth_date'];
//$age = ((time() - strtotime($birthdate))/(60*60*24*365.2425));

        ?>
  <tr>
    <td class="style6"><?php echo $data['CIF']; ?></td>
    <td class="style6"><?php echo $data['CUST_SHORT_NAME'];  ?></td>
	<td class="style6"><?php echo $data['unit'];  ?></td>
	
    <td class="style6"><?php echo number_format (compat_num($data['os'])); ?></td>
    <td class="style6"><?php echo $ap_tujuan; ?></td>
    
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


