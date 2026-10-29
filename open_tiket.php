<?php require_once __DIR__ . '/php8_compat.php';
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

?>
<script type="text/javascript">
 function wajibAngka(evt) {
 var charCode = (evt.which) ? evt.which : event.keyCode
 if (charCode > 31 && (charCode < 48 || charCode > 57))
 return false;
 }
</script>
    <script type="text/javascript" src="jquery.js"></script>
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
<link rel="stylesheet" href="tabel.css" />
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<style type="text/css">
<script type="text/javascript" src="jquery.js"></script>
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
    <td height="20" bordercolor="#FFFFFF" bgcolor="#2F6B4F" class="style3 style9">Open Tiket
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
       
      <input type="text"  name="nama_rekening" onBlur="fill();" id="country" />
</div>
</tr>
<tr>
  <td height="10" nowrap bgcolor="#B3B3FF"><div align="left" class="style7">Bukti Jurnal</div></td>
  <td nowrap>
   <div align="center"><strong>:      </strong></div></td>
  <td colspan="2" nowrap><input name="bu" type="text" id="bu" value=""  size="20"/>  </td>
</tr>

<tr>
  <td height="10" nowrap bgcolor="#B3B3FF"><div align="left" class="style7">Nominal</div></td>
  <td nowrap>
   <div align="center"><strong>:      </strong></div></td>
  <td colspan="2" nowrap><input name="nominal" type="text" id="nominal" value="" onKeyPress="return wajibAngka(event)" autocomplete="off" onKeyUp="document.getElementById('format').innerHTML = formatCurrency(this.value);" size="20"/>                     <span id="format"></span></td>
</tr>
<tr>
                  <td width="100" height="15"  nowrap bgcolor="#B3B3FF"><div align="left" class="style10">Tanggal Transaksi</td>
                    <td width="10" nowrap>   <div align="center"><strong>:</strong></div></td>                 
                 <td colspan="2" nowrap><input type="text" name="tanggal_transaksi" size="15" value="<?php if(empty($_POST['tanggal_transaksi'])){ echo $tanggal;}else{ echo $_POST['tanggal_transaksi']; }?>"/>
                    <a href="javascript:void(0)" onClick="if(self.gfPop)gfPop.fPopCalendar(document.postform.tanggal_transaksi);return false;" ><img src="calender/calender.jpeg" alt="" name="popcal" width="34" height="29" border="0" align="absmiddle" id="popcal" /></a>                 
                </td>

<tr>
  <td height="10" nowrap bgcolor="#B3B3FF"><div align="left" class="style7">Keterangan</div></td>
  <td nowrap>
   <div align="center"><strong>:      </strong></div></td>
  <td colspan="2" nowrap><textarea  rows="15" cols="40" name="ket" type="text" id="ket" /></textarea>  </tr>
<tr>
  <td height="10" nowrap bgcolor="#B3B3FF"><span class="style7">Jenis Transaksi </span></td>
  <td nowrap><div align="center"><strong>:</strong></div></td>
  <td colspan="2" nowrap><select name="select" id="select" size="1">
    <option>Pilih Salah satu</option>
    <option value="01">Simpanan</option>
    <option value="03">Setoran Angsuran</option>
    <option value="04">Setoran Angsuran Wo</option>
    <option value="05">Jurnal</option>
<option value="06">Murabahah</option>
<option value="07">Wakalah</option>
<option value="08">Tunggakan</option>
<option value="09">Lainnya</option>

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
    <th>Nomial</th>
    <th>Desc</th>
    <th>Status</th>
  </tr>
  <?php
  include "conn2.php";
  include "function.php";
  $koneksi=open_connection2();
  $cif=$_POST ['kode_rekening'];
  $nominal=$_POST['nominal'];
  $tanggal_transaksi=$_POST['tanggal_transaksi'];
  $bu=$_POST['bu'];
  $nama=mysql_real_escape_string($_POST['nama_rekening']);
  $unit=$userid;


  $jenis_transaksi=$_POST['select'];
 
  $deskripsi=mysql_real_escape_string($_POST['ket']);

 
$no_tiket=7;
$no_tiket=acakangkahuruf($no_tiket);
            

       
           
  If (isset($_POST ['search']))
{

if (empty($cif)||empty($jenis_transaksi)||empty($nominal)){
    echo "<script>alert('Maaf,.isi Nomor REKENING dan Jenis Transaksi yang benar');</script>";
         echo "<script language='javascript'>document.location.href='open_tiket.php'</script>";}
   
    else{

        $input ="Insert into tiket (no_tiket,tanggal_transaksi,cif,nama,bu,nominal,jenis_transaksi,deskripsi,unit,status_tiket)VALUES('$no_tiket','$tanggal_transaksi','$cif','$nama','$bu','$nominal','$jenis_transaksi','$deskripsi','$unit','OPEN')";
        $inputtrnsk = mysql_query($input);


        $select = mysql_query("SELECT * from tiket  where status_tiket='OPEN' ");
                   
     
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
    <td class="style6"><?php echo $data['cif']; ?></td>
    <td class="style6"><?php echo $data['nama'];  ?></td>
    <td class="style6"><?php echo tgl_indo ($data['created_at']);?></td>
    <td class="style6"><?php echo number_format (compat_num($data['nominal'])); ?></td>
    <td class="style6"><?php echo $data['deskripsi']; ?></td>
    <td class="style6"><?php echo $data['status_tiket']; ?></td>
   
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
