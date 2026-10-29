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

?>
<script type="text/javascript">
 function wajibAngka(evt) {
 var charCode = (evt.which) ? evt.which : event.keyCode
 if (charCode > 31 && (charCode < 48 || charCode > 57))
 return false;
 }
</script>
    <script type="text/javascript" src="../../jquery.js"></script>
    <script>
    function suggest(inputString){
        if(inputString.length == 0) {
            $('#suggestions').fadeOut();
        } else {
        $('#country').addClass('load');
            $.post("autosuggesst_rek.php", {queryString: ""+inputString+""}, function(data){
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
<link rel="stylesheet" href="../../tabel.css" />
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<style type="text/css">
<script type="text/javascript" src="../../jquery.js"></script>
        <link href="../../style.css" rel="stylesheet" type="text/css" media="screen" />
        <link rel="stylesheet" href="../../css/bootstrap.min.css"/>
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

<table width="100%" cellpadding="1" bordercolor="#CCCCCC" bgcolor="#2F6B4F">
  <tr>
    <td height="20" bordercolor="#FFFFFF" bgcolor="#2F6B4F" class="style3 style9">Transaksi Setoran dan Penarikan Simpanan Umum
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
    <td width="203" height="10" nowrap bgcolor="#B3B3FF"><div align="left" class="style5 style6">No Rekening</div></td>
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
  <td height="10" nowrap bgcolor="#B3B3FF"><div align="left" class="style7">Nominal</div></td>
  <td nowrap>
   <div align="center"><strong>:      </strong></div></td>
  <td colspan="2" nowrap><input name="nama" type="text" id="nominal" value="" onKeyPress="return wajibAngka(event)" autocomplete="off" onKeyUp="document.getElementById('format').innerHTML = formatCurrency(this.value);" size="20"/>                     <span id="format"></span></td>
</tr>
<tr>
  <td height="10" nowrap bgcolor="#B3B3FF"><div align="left" class="style7">Keterangan</div></td>
  <td nowrap>
   <div align="center"><strong>:      </strong></div></td>
  <td colspan="2" nowrap><input name="ket" type="text" id="ket"  size="30"/>  </tr>
<tr>
  <td height="10" nowrap bgcolor="#B3B3FF"><span class="style7">Jenis Transaksi </span></td>
  <td nowrap><div align="center"><strong>:</strong></div></td>
  <td colspan="2" nowrap><select name="select" id="select" size="1">
    <option>Pilih Salah satu</option>
    <option value="01">Setor Tunai </option>
    <option value="02">Tarik Tunai </option>
    <option value="03">Setoran Angsuran</option>
<option value="04">Pemindah Bukuan</option>
  </select></td>
</tr>
<tr>
  <td height="10" nowrap bgcolor="#B3B3FF"><span class="style7">Jenis Pemindah Bukuan </span></td>
  <td nowrap><div align="center"><strong>:</strong></div></td>
  <td colspan="2" nowrap><select name="baki" id="baki" size="1">
  
    <option value="Debet">Debet</option>
    <option value="Kredit">Kredit </option>
    </select></td>
</tr>
<tr>
  <td height="10" nowrap bgcolor="#B3B3FF"><span class="style7">Jenis Transaksi PB </span></td>
  <td nowrap><div align="center"><strong>:</strong></div></td>
  <td colspan="2" nowrap><select name="simpan" id="simpan" size="1">
  
    <option value="tunai">Tunai</option>
    <option value="gl">GL </option>
    </select></td>
</tr>

<tr>
  <td height="10" colspan="4">&nbsp;</td>
  </tr>
<tr bgcolor="#B3B3FF">
  <td height="10">&nbsp; </td>
  <td><div align="center"></div></td>
  <td colspan="2"><input name="search" type="submit" id="submit2" value="Input" size="40"></td>
</tr>
</table>
</form>
<iframe width=174 height=189 name="gToday:normal:calender/normal.js" id="gToday:normal:calender/normal.js" src="calender/ipopeng.htm" scrolling="no" frameborder="0" style="visibility:visible; z-index:999; position:absolute; top:-500px; left:-500px;">
</iframe>

<table border="2" bgcolor="" width="100%" class="table1">
  <tr class="table1">
    <th>NOREK</th>
    <th>NAMA</th>
    <th>TGL TRANSAKSI </th>
    <th>DEBIT</th>
    <th>KREDIT</th>
    <th>SALDO</th>
  </tr>
  <?php
  include "../../conn2.php";
  include "../../function.php";
  $koneksi=open_connection2();
  $norek=$_POST ['kode_rekening'];
  $nominal=$_POST['nama'];
  $baki=$_POST['baki'];
  $simpan=$_POST['simpan'];
  $tanggal=date("Y-m-d");
  $pilih=$_POST['select'];
  //$buss_date=date("Y-m-d H:i:s");
$buss_date=$tgl_system;
  $keterangan=mysql_real_escape_string($_POST['ket']);

  $kel = mysql_query ("select deposito.CIF,deposito.UNIT,CUST_SHORT_NAME FROM deposito left join deposan on deposan.cif = deposito.cif where  norek ='$norek'");
$kkel = mysql_fetch_array($kel);
$code_kel = $kkel['CODE_KEL'];
$ciff = $kkel['CIF'];
$unit=$kkel['UNIT'];
$nama=mysql_real_escape_string ($kkel['CUST_SHORT_NAME']);

$kodetr=7;
            $kodetr=acakangkahuruf($kodetr);
            $kode_trans="BU/".$unit.$kodetr;

        
           
  If (isset($_POST ['search']))
{
	if(mysql_num_rows($kel)){
if (empty($norek)||empty($pilih)||empty($nominal)){
    echo "<script>alert('Maaf,.isi Nomor REKENING dan Jenis Transaksi yang benar');</script>";
         echo "<script language='javascript'>document.location.href='stor.php'</script>";}
   
    else{
    switch($pilih)
    {
     case "01":
$cek_saldo = mysql_query("select SUM(KREDIT-DEBIT) as saldo from escrow_deposito where norek ='$norek'");
$cwajib = mysql_fetch_array($cek_saldo);
$saldo = $cwajib['saldo'];

                $ket1="Setor Simpanan  an $nama";
               
                   $input ="Insert into escrow_deposito (BUSS_DATE,NOREK,UNIT,CIF,DEBIT,TYPE,KREDIT,USERID,KET,REFF,kode_transaksi)VALUES('$tgl_system','$norek','$unit','$ciff',0,'02','$nominal','$userid','$ket1','','$kode_trans')";
                        $inputtrnsk = mysql_query($input);
						
						
                    mysql_query("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                    values('$unit','$kode_trans','1120000','$tgl_system','Bukti SYSTEM','$ket1','$nominal','$userid')");

                    mysql_query("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                    values('$unit','$kode_trans','2102000','$tgl_system','Bukti SYSTEM','$ket1','$nominal','$userid')");
			
               
                           
    echo "<script>alert('Nominal sudah dikreditkan pada rekening $norek !!!');</script>";
    $select1 = mysql_query("SET @bal = 0;");
    $select = mysql_query("SELECT escrow_deposito.NOREK,escrow_deposito.BUSS_DATE,deposan.CUST_SHORT_NAME,DEBIT,KREDIT,@bal:=(@bal+KREDIT)-DEBIT as balance FROM escrow_deposito
    left join deposan on deposan.cif = escrow_deposito.cif
    where escrow_deposito.cif='$ciff' order by escrow_deposito.REFF");

   
     break;
       case "02":
      $ket =$keterangan;
$area = mysql_query ("select code_area FROM branch where kode_branch ='$unit'");
$c_area = mysql_fetch_array($area);
$code_area=$c_area['code_area'];
     $SA = mysql_query ("select sum(KREDIT)- sum(DEBIT) as SALDO  FROM escrow_deposito where  cif ='$ciff'");
     $SAL = mysql_fetch_array($SA);
	 
     $SALDO = $SAL['SALDO'];
     

	 if ($SALDO < $nominal){
            echo "<script>alert('Maaf,.Saldo Tidak Mencukupi saldo adalah $SALDO');</script>";
            echo "<script language='javascript'>document.location.href='stor.php'</script>";
        }
        else{
            $ket1="Penarikan Simpanan  an $nama";
               
                 
                    
					

                   $input ="Insert into escrow_deposito (BUSS_DATE,NOREK,UNIT,CIF,DEBIT,TYPE,KREDIT,USERID,KET,REFF,kode_transaksi)VALUES('$tgl_system','$norek','$unit','$ciff','$nominal','02',0,'$userid','$ket1','','$kode_trans')";
                        
			
               
                           
    echo "<script>alert('Nominal sudah didebet pada rekening $norek !!!');</script>";
    
            $inputtrnsk = mysql_query($input);
            if($inputtrnsk){
                $select1 = mysql_query("SET @bal = 0;");
    $select = mysql_query("SELECT escrow_deposito.NOREK,escrow_deposito.BUSS_DATE,deposan.CUST_SHORT_NAME,DEBIT,KREDIT,@bal:=(@bal+KREDIT)-DEBIT as balance FROM escrow_deposito
    left join deposan on deposan.cif = escrow_deposito.cif
    where escrow_deposito.cif='$ciff' order by escrow_deposito.REFF");

                mysql_query("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                    values('$unit','$kode_trans','2102000','$tgl_system','Bukti SYSTEM','$ket1','$nominal','$userid')");
					
					mysql_query("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                    values('$unit','$kode_trans','1120000','$tgl_system','Bukti SYSTEM','$ket1','$nominal','$userid')");
            }
            else{
            echo "<script>alert('Maaf,.Gagal Input );</script>";
            echo "<script language='javascript'>document.location.href='view_stor..php'</script>";
            }
        }   
    break;
     case "03":
     
            echo "<script>alert('Maaf,Menu ini belum tersedia');</script>";
            echo "<script language='javascript'>document.location.href='view_stor.php'</script>";
            
     break;


    case "04":
 echo "<script>alert('Maaf,Menu ini belum tersedia');</script>";
            echo "<script language='javascript'>document.location.href='view_stor.php'</script>";
     break;
      default:
    }
        }
 }
            else{
            echo "<script>alert('Maaf,Nomor Rekening tidak ditemukan');</script>";
            echo "<script language='javascript'>document.location.href='view_stor.php'</script>";
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
    <td class="style6"><?php echo $data['NOREK']; ?></td>
    <td class="style6"><?php echo $data['CUST_SHORT_NAME'];  ?></td>
    <td class="style6"><?php echo tgl_indo ($data['BUSS_DATE']);?></td>
    <td class="style6"><?php echo number_format (compat_num($data['DEBIT'])); ?></td>
    <td class="style6"><?php echo number_format (compat_num($data['KREDIT'])); ?></td>
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
<script>
$(document).ready(function(){
$('.hasil').hide();
$('#tombol').hide();
$('#baki').hide();
$('#simpan').hide();
$('#select').change(function(){
if($('#select').val()=='01'){
$('.hasil').hide();   
$('#tombol').hide();
$('#baki').hide();
$('#simpan').hide();
}else if($('#select').val()=='02'){
$('.hasil').hide();
$('#tombol').hide();
$('#baki').hide();
$('#simpan').hide();
}else if($('#select').val()=='03'){
$('.hasil').hide();
$('#tombol').hide();
$('#baki').hide();
$('#simpan').hide();
}else if($('#select').val()=='04'){
$('.hasil').show();
$('#tombol').show();
$('#baki').show();
$('#simpan').show();
}
});
});
</script>

</body>

</html>
