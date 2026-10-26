<?php require_once __DIR__ . '/koneksi_db.php';
session_start();
ob_start();
//error_reporting(E_ALL);
//ini_set('display_errors',1);

$userid=$_SESSION['userid'];
$tgl_system=$_SESSION['stgl_system'];
//cek apakah user sudah login

if(!isset($_SESSION['userid'])){
   header("location:index.html");
}
if($_SESSION['level']!="unit"){
   header("location:../index.html");
}

?>
    <script type="text/javascript" src="jquery.js"></script>
    <script>
    function suggest(inputString){
        if(inputString.length == 0) {
            $('#suggestions').fadeOut();
        } else {
        $('#country').addClass('load');
            $.post("autosuggesst_rek_mob.php", {queryString: ""+inputString+""}, function(data){
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
<script type="text/javascript">
 function wajibAngka(evt) {
 var charCode = (evt.which) ? evt.which : event.keyCode
 if (charCode > 31 && (charCode < 48 || charCode > 57))
 return false;
 }
</script>


<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Pull Data</title>
<link rel="stylesheet" href="tabel.css" />
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<style type="text/css">
<script type="text/javascript" src="jquery.js"></script>
        <link href="style.css" rel="stylesheet" type="text/css" media="screen" />
        <link rel="stylesheet" href="css/bootstrap.min.css"/>
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
    <td height="20" bordercolor="#FFFFFF" bgcolor="#669900" class="style3 style9">Pull Data
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
  <td height="10" nowrap bgcolor="#B3B3FF"><span class="style7">Jenis Pull Data </span></td>
  <td nowrap><div align="center"><strong>:</strong></div></td>
  <td colspan="2" nowrap><select name="select" id="select" size="1">
    <option>Pilih Salah satu</option>
    <option value="01">Kelompok</option>
    <option value="02">Perorangan </option>
    </select></td>
</tr>
<tr>
    <td width="203" height="10" nowrap bgcolor="#B3B3FF"><div align="left" class="style5 style6">Kode Kelompok/CIF </div></td>
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
  <td height="10" nowrap bgcolor="#B3B3FF"><span class="style7">Jenis Transaksi </span></td>
  <td nowrap><div align="center"><strong>:</strong></div></td>
  <td colspan="2" nowrap><select name="transaksi" id="transaksi" size="1">
    <option>Pilih Salah satu</option>
    <option value="lima">5%</option>
    <option value="lebaran">Lebaran </option>
    <option value="pelunasan">Pelunsan </option>
<option value="pelunasan19">Pelunasan Ke 19 </option>
<option value="pelunasanDO">Pelunasan DO </option>
<option value="pelunasanRestMargin">Pelunasan Rest Pokok Margin </option>
    <option value="penarikan">Penarikan </option>
<option value="MeninggalDunia">Meninggal Dunia </option>

    </select></td>
</tr>

<tr class="nilai">
  <td height="10" nowrap bgcolor="#B3B3FF"><div align="left" class="style7">Nominal</div></td>
  <td nowrap>
   <div align="center"><strong>:      </strong></div></td>
  <td colspan="2" nowrap><input name="nominal" type="text" id="nominal" value="" onKeyPress="return wajibAngka(event)" autocomplete="off" onKeyUp="document.getElementById('format').innerHTML = formatCurrency(this.value);" size="20"/>                     <span id="format"></span></td>
</tr>
</tr>

  <td height="10" nowrap bgcolor="#B3B3FF"><span class="style7">Tanggal Tagih </span></td>
  <td nowrap><div align="center"><strong>:</strong></div></td>
  <td colspan="2" nowrap><input name="tgl_tagih" type="text" id="tanggal" value="" size="20"/>
    <a href="javascript:void(0)" onClick="if(self.gfPop)gfPop.fPopCalendar(document.postform.tanggal);return true;" ><img src="images/calendarIcon.jpg" alt="" name="popcal" width="17" height="17" border="0" align="absmiddle" id="popcal" /></a></td>
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
    <th>Norek</th>
    <th>Nama</th>
    <th>TGL Transaksi </th>
    <th>Jenis Pull Data</th>
    <th>Nominal</th>
    <th>aksi</th>

  </tr>
  <?php
  include "conn2.php";
  include "function.php";

  //$host_dua   ="185.201.9.210";
  //$user_dua ="adminni";
  $host_dua   ="76.13.20.176";
$user_dua ="admin";
  $password_dua ="";
  $db_dua ="mobcol";
 
  $config_dua =  db_mobcol();
  if(mysqli_connect_errno())
  {
  echo'Gagal :'.mysqli_connect_error();
  }
  
  $koneksi=open_connection2();
  $cif_kel=$_POST['kode_rekening'];
  $pilih=$_POST['select'];
  $tanggal=date("Y-m-d");
  $transaksi=$_POST['transaksi'];
  $tgl_tagih= $_POST['tgl_tagih'];
  $nominal =$_POST['nominal'];
  

 
            
  if (isset($_POST ['search']))
{
if (empty($cif_kel)||empty($pilih)||empty($transaksi)){
    echo "<script>alert('Maaf,.isi Nomor kode kelompok dan Jenis Transaksi yang benar ya!');</script>";
         echo "<script language='javascript'>document.location.href='mob_pull_data.php'</script>";}
    
    else{
        
    switch($pilih)
    {
     case "01":
        $cek = mysql_query("SELECT unit, calon_loan.code_kel as code_kel, calon_loan.cif as cif, calon_loan.cao as cao,saldo_margin,Plafond,
        no_anggota as norek,pokok, ijaroh, angsuran, hari, bulat, os, Cust_Short_name, nama_kel
        FROM calon_loan left join kelompok on calon_loan.code_kel = kelompok.code_kel WHERE  calon_loan.code_kel ='$cif_kel' ");

     if($transaksi == 'lima'){  
                
        if(mysql_num_rows($cek)>=1){                
                
            while ($row=mysql_fetch_array($cek)){
                $unit=mysql_real_escape_string($row['unit']);
                $tgl_tagih=$tgl_tagih;
                $code_kel=mysql_real_escape_string($row['code_kel']);
                $cif=mysql_real_escape_string($row['cif']);
                $cao=mysql_real_escape_string($row['cao']);
                $norek=mysql_real_escape_string($row['norek']);
                $angsuran_pokok=mysql_real_escape_string($row['pokok']);
                $angsuran_margin=mysql_real_escape_string($row['ijaroh']);
                $angsuran=mysql_real_escape_string($row['angsuran']);
                $bayar=mysql_real_escape_string($row['Plafond'] * (5/100));
                $status_realisasi='';
                $pb='0';
                $ke = '0';
                $tunggakan='0';
                $hari=mysql_real_escape_string($row['hari']);
                $twm='0';
                $bulat=$bayar;
                $simpanan_wajib='0';
                $simpanan_pokok ='0';
                $os=mysql_real_escape_string($row['os']);
                $nama=mysql_real_escape_string($row['Cust_Short_name']);
                $nama_kel=mysql_real_escape_string($row['nama_kel']);
                $saldo_margin=mysql_real_escape_string($row['saldo_margin']);
                $plafond=mysql_real_escape_string($row['Plafond']);

             
            
            
            
                $Data_lima[] = "('$unit', '$tgl_tagih', '$code_kel','$cif','$cao','$norek', '$angsuran_pokok','$angsuran_margin','$angsuran','$bayar','$status_realisasi','$pb','$ke','$tunggakan','$hari','$twm','$bulat','$simpanan_wajib','$simpanan_pokok','$os','$nama','$nama_kel','$saldo_margin','$plafond')";
                $Data_pull[] = "('$unit', '$tgl_tagih', '$code_kel','$cif','$cao','$norek', '$angsuran_pokok','$angsuran_margin','$angsuran','$bayar','$status_realisasi','$pb','$ke','$tunggakan','$hari','$twm','$bulat','$simpanan_wajib','$simpanan_pokok','$os','$nama','$nama_kel','$saldo_margin','$plafond','lima')";
            
            }

            $sql = "insert INTO tagihan_lima_persen(unit, tgl_tagih,code_kel,cif,cao,norek,angsuran_pokok,angsuran_margin,angsuran,bayar,status_realisasi,pb,ke,tunggakan,hari,twm,bulat,simpanan_wajib,simpanan_pokok,os,nama,nama_kel,saldo_margin,plafond ) values ";
            $sql .= implode(',', $Data_lima);
            db_query($config_dua, $sql) or die(mysqli_error($config_dua));

            $sql = "insert INTO pull_data(unit, tgl_tagih,code_kel,cif,cao,norek,angsuran_pokok,angsuran_margin,angsuran,bayar,status_realisasi,pb,ke,tunggakan,hari,twm,bulat,simpanan_wajib,simpanan_pokok,os,nama,nama_kel,saldo_margin,plafond,jenis_pull ) values ";
            $sql .= implode(',', $Data_pull);
            db_query($config_dua, $sql) or die(mysqli_error($config_dua));
          


            echo "<script>alert('Pull Data 5% sukses');</script>";
            $select = db_query($config_dua,"SELECT * from pull_data where unit =$userid and tgl_tagih > '$tgl_system'");
            }else{
                echo "<script>alert('Gagal Pull Data !!!');</script>";
            }
        
        }elseif($transaksi =='lebaran'){

            if(mysql_num_rows($cek)>=1){                
                
                while ($row=mysql_fetch_array($cek)){
                    $unit=mysql_real_escape_string($row['unit']);
                    $tgl_tagih=$tgl_tagih;
                    $code_kel=mysql_real_escape_string($row['code_kel']);
                    $cif=mysql_real_escape_string($row['cif']);
                    $cao=mysql_real_escape_string($row['cao']);
                    $norek=mysql_real_escape_string($row['norek']);
                    $angsuran_pokok=mysql_real_escape_string($row['pokok']);
                    $angsuran_margin=mysql_real_escape_string($row['ijaroh']);
                    $angsuran=mysql_real_escape_string($row['angsuran']);
                    $bayar=mysql_real_escape_string($row['bulat']);
                    $status_realisasi='';
                    $pb='0';
                    $ke = '0';
                    $tunggakan='0';
                    $hari=mysql_real_escape_string($row['hari']);
                    $twm='0';
                    $bulat=mysql_real_escape_string($row['bulat']);
                    $simpanan_wajib='0';
                    $simpanan_pokok ='0';
                    $os=mysql_real_escape_string($row['os']);
                    $nama=mysql_real_escape_string($row['Cust_Short_name']);
                    $nama_kel=mysql_real_escape_string($row['nama_kel']);
                    $saldo_margin=mysql_real_escape_string($row['saldo_margin']);
                    $plafond=mysql_real_escape_string($row['Plafond']);
    
                 
                
                
                
                    $Data_lima[] = "('$unit', '$tgl_tagih', '$code_kel','$cif','$cao','$norek', '$angsuran_pokok','$angsuran_margin','$angsuran','$bayar','$status_realisasi','$pb','$ke','$tunggakan','$hari','$twm','$bulat','$simpanan_wajib','$simpanan_pokok','$os','$nama','$nama_kel','$saldo_margin','$plafond')";
                    $Data_pull[] = "('$unit', '$tgl_tagih', '$code_kel','$cif','$cao','$norek', '$angsuran_pokok','$angsuran_margin','$angsuran','$bayar','$status_realisasi','$pb','$ke','$tunggakan','$hari','$twm','$bulat','$simpanan_wajib','$simpanan_pokok','$os','$nama','$nama_kel','$saldo_margin','$plafond','lebaran')";
                
                }
    
                $sql = "insert INTO tagihan_lebaran(unit, tgl_tagih,code_kel,cif,cao,norek,angsuran_pokok,angsuran_margin,angsuran,bayar,status_realisasi,pb,ke,tunggakan,hari,twm,bulat,simpanan_wajib,simpanan_pokok,os,nama,nama_kel,saldo_margin,plafond ) values ";
                $sql .= implode(',', $Data_lima);
                db_query($config_dua, $sql) or die(mysqli_error($config_dua));
    
                $sql = "insert INTO pull_data(unit, tgl_tagih,code_kel,cif,cao,norek,angsuran_pokok,angsuran_margin,angsuran,bayar,status_realisasi,pb,ke,tunggakan,hari,twm,bulat,simpanan_wajib,simpanan_pokok,os,nama,nama_kel,saldo_margin,plafond,jenis_pull ) values ";
                $sql .= implode(',', $Data_pull);
                db_query($config_dua, $sql) or die(mysqli_error($config_dua));
              
    
    
                echo "<script>alert('Pull Data lebaran sukses');</script>";
                $select = db_query($config_dua,"SELECT * from pull_data where unit =$userid and tgl_tagih > '$tgl_system'");
                }else{
                    echo "<script>alert('Gagal Pull Data !!!');</script>";
                }
            
       
        }elseif($transaksi =='pelunasan'){
        $cek = mysql_query("SELECT unit, loan.code_kel as code_kel, loan.cif as cif, loan.cao as cao,saldo_margin,Plafond,
        no_anggota as norek,pokok, ijaroh, angsuran, hari, bulat, os, Cust_Short_name, nama_kel
        FROM loan left join kelompok on loan.code_kel = kelompok.code_kel WHERE  loan.code_kel ='$cif_kel' AND os> 0 ");


        if(mysql_num_rows($cek)>=1){                
            
            while ($row=mysql_fetch_array($cek)){
                $unit=mysql_real_escape_string($row['unit']);
                $tgl_tagih=$tgl_tagih;
                $code_kel=mysql_real_escape_string($row['code_kel']);
                $cif=mysql_real_escape_string($row['cif']);
                $row_sal= mysql_fetch_array(mysql_query("select(sum(KREDIT) - sum(DEBIT))as saldo from simpanan WHERE cif ='$cif' and type not in ('04','05')")) or die(mysql_error());
                $saldo=$row_sal['saldo'];
                if($saldo==null){
                    $saldo=0;
                }else{
                    $saldo = $saldo;
                }
                $cao=mysql_real_escape_string($row['cao']);
                $norek=mysql_real_escape_string($row['norek']);
                $angsuran_pokok=mysql_real_escape_string($row['pokok']);
                $angsuran_margin=mysql_real_escape_string($row['ijaroh']);
                $angsuran=mysql_real_escape_string($row['angsuran']);
                $os=mysql_real_escape_string($row['os']);
                $bayar=mysql_real_escape_string($row['bulat']);

                $sisa = $saldo - $os;
                if  ($saldo < $os ){
                    $bayar = 0;                    
                }else{
                    $bayar =$sisa;
                }

                $status_realisasi='';
                $pb='0';
                $ke = '0';
                $tunggakan='0';
                $hari=mysql_real_escape_string($row['hari']);
                $twm=$saldo;
                $bulat=mysql_real_escape_string($row['bulat']);
                $simpanan_wajib='0';
                $simpanan_pokok ='0';                
                $nama=mysql_real_escape_string($row['Cust_Short_name']);
                $nama_kel=mysql_real_escape_string($row['nama_kel']);
                $saldo_margin=mysql_real_escape_string($row['saldo_margin']);
                $plafond=mysql_real_escape_string($row['Plafond']);

             
            
            
            
                $Data_lima[] = "('$unit', '$tgl_tagih', '$code_kel','$cif','$cao','$norek', '$angsuran_pokok','$angsuran_margin','$angsuran','$bayar','$status_realisasi','$pb','$ke','$tunggakan','$hari','$twm','$bulat','$simpanan_wajib','$simpanan_pokok','$os','$nama','$nama_kel','$saldo_margin','$plafond')";
                $Data_pull[] = "('$unit', '$tgl_tagih', '$code_kel','$cif','$cao','$norek', '$angsuran_pokok','$angsuran_margin','$angsuran','$bayar','$status_realisasi','$pb','$ke','$tunggakan','$hari','$twm','$bulat','$simpanan_wajib','$simpanan_pokok','$os','$nama','$nama_kel','$saldo_margin','$plafond','pelunasan')";
            
            }
            

            $sql = "insert INTO tagihan_pelunasan(unit, tgl_tagih,code_kel,cif,cao,norek,angsuran_pokok,angsuran_margin,angsuran,bayar,status_realisasi,pb,ke,tunggakan,hari,twm,bulat,simpanan_wajib,simpanan_pokok,os,nama,nama_kel,saldo_margin,plafond ) values ";
            $sql .= implode(',', $Data_lima);
            db_query($config_dua, $sql) or die(mysqli_error($config_dua));

            $sql = "insert INTO pull_data(unit, tgl_tagih,code_kel,cif,cao,norek,angsuran_pokok,angsuran_margin,angsuran,bayar,status_realisasi,pb,ke,tunggakan,hari,twm,bulat,simpanan_wajib,simpanan_pokok,os,nama,nama_kel,saldo_margin,plafond,jenis_pull ) values ";
            $sql .= implode(',', $Data_pull);
            db_query($config_dua, $sql) or die(mysqli_error($config_dua));
          


            echo "<script>alert('Pull Data Pelunasan sukses');</script>";
            $select = db_query($config_dua,"SELECT * from pull_data where unit =$userid and tgl_tagih > '$tgl_system'" );
            }else{
                echo "<script>alert('Gagal Pull Data !!!');</script>";
            }

}elseif($transaksi =='pelunasanRestMargin'){
        $cek = mysql_query("SELECT unit, loan.code_kel as code_kel, loan.cif as cif, loan.cao as cao,saldo_margin,Plafond,
        no_anggota as norek,pokok, ijaroh, angsuran, hari, bulat, os, Cust_Short_name, nama_kel
        FROM loan left join kelompok on loan.code_kel = kelompok.code_kel WHERE  loan.code_kel ='$cif_kel' AND os> 0 ");


        if(mysql_num_rows($cek)>=1){                
            
            while ($row=mysql_fetch_array($cek)){
                $unit=mysql_real_escape_string($row['unit']);
                $tgl_tagih=$tgl_tagih;
                $code_kel=mysql_real_escape_string($row['code_kel']);
                $cif=mysql_real_escape_string($row['cif']);
                $row_sal= mysql_fetch_array(mysql_query("select(sum(KREDIT) - sum(DEBIT))as saldo from simpanan WHERE cif ='$cif' and type not in ('04','05')")) or die(mysql_error());
                $saldo=$row_sal['saldo'];
                if($saldo==null){
                    $saldo=0;
                }else{
                    $saldo = $saldo;
                }
                $cao=mysql_real_escape_string($row['cao']);
                $norek=mysql_real_escape_string($row['norek']);
                $angsuran_pokok=mysql_real_escape_string($row['pokok']);
                $angsuran_margin=mysql_real_escape_string($row['ijaroh']);
                $angsuran=mysql_real_escape_string($row['angsuran']);
                $os=mysql_real_escape_string($row['os']);
                $bayar=mysql_real_escape_string($row['bulat']);

                $sisa = $saldo - $os;
                if  ($saldo < $os ){
                    $bayar = 0;                    
                }else{
                    $bayar =$sisa;
                }

                $status_realisasi='';
                $pb='0';
                $ke = '0';
                $tunggakan='0';
                $hari=mysql_real_escape_string($row['hari']);
                $twm=$saldo;
                $bulat=mysql_real_escape_string($row['bulat']);
                $simpanan_wajib='0';
                $simpanan_pokok ='0';                
                $nama=mysql_real_escape_string($row['Cust_Short_name']);
                $nama_kel=mysql_real_escape_string($row['nama_kel']);
                $saldo_margin=mysql_real_escape_string($row['saldo_margin']);
                $plafond=mysql_real_escape_string($row['Plafond']);

             
            
            
            
                $Data_lima[] = "('$unit', '$tgl_tagih', '$code_kel','$cif','$cao','$norek', '$angsuran_pokok','$angsuran_margin','$angsuran','$bayar','$status_realisasi','$pb','$ke','$tunggakan','$hari','$twm','$bulat','$simpanan_wajib','$simpanan_pokok','$os','$nama','$nama_kel','$saldo_margin','$plafond')";
                $Data_pull[] = "('$unit', '$tgl_tagih', '$code_kel','$cif','$cao','$norek', '$angsuran_pokok','$angsuran_margin','$angsuran','$bayar','$status_realisasi','$pb','$ke','$tunggakan','$hari','$twm','$bulat','$simpanan_wajib','$simpanan_pokok','$os','$nama','$nama_kel','$saldo_margin','$plafond','pelunasanRestMargin')";
            
            }
            

            $sql = "insert INTO tagihan_pelunasan(unit, tgl_tagih,code_kel,cif,cao,norek,angsuran_pokok,angsuran_margin,angsuran,bayar,status_realisasi,pb,ke,tunggakan,hari,twm,bulat,simpanan_wajib,simpanan_pokok,os,nama,nama_kel,saldo_margin,plafond ) values ";
            $sql .= implode(',', $Data_lima);
            db_query($config_dua, $sql) or die(mysqli_error($config_dua));

            $sql = "insert INTO pull_data(unit, tgl_tagih,code_kel,cif,cao,norek,angsuran_pokok,angsuran_margin,angsuran,bayar,status_realisasi,pb,ke,tunggakan,hari,twm,bulat,simpanan_wajib,simpanan_pokok,os,nama,nama_kel,saldo_margin,plafond,jenis_pull ) values ";
            $sql .= implode(',', $Data_pull);
            db_query($config_dua, $sql) or die(mysqli_error($config_dua));
          


            echo "<script>alert('Pull Data Pelunasan Rest Pokok Margin sukses');</script>";
            $select = db_query($config_dua,"SELECT * from pull_data where unit =$userid and tgl_tagih > '$tgl_system'" );
            }else{
                echo "<script>alert('Gagal Pull Data !!!');</script>";
            }

        
   }elseif($transaksi =='pelunasan19'){
        $cek = mysql_query("SELECT unit, loan.code_kel as code_kel, loan.cif as cif, loan.cao as cao,saldo_margin,Plafond,
        no_anggota as norek,pokok, ijaroh, angsuran, hari, bulat, os, Cust_Short_name, nama_kel
        FROM loan left join kelompok on loan.code_kel = kelompok.code_kel WHERE  loan.code_kel ='$cif_kel' AND os> 0 ");


        if(mysql_num_rows($cek)>=1){                
            
            while ($row=mysql_fetch_array($cek)){
                $unit=mysql_real_escape_string($row['unit']);
                $tgl_tagih=$tgl_tagih;
                $code_kel=mysql_real_escape_string($row['code_kel']);
                $cif=mysql_real_escape_string($row['cif']);
                $row_sal= mysql_fetch_array(mysql_query("select(sum(KREDIT) - sum(DEBIT))as saldo from simpanan WHERE cif ='$cif' and type not in ('04','05')")) or die(mysql_error());
                $saldo=$row_sal['saldo'];
                if($saldo==null){
                    $saldo=0;
                }else{
                    $saldo = $saldo;
                }
                $cao=mysql_real_escape_string($row['cao']);
                $norek=mysql_real_escape_string($row['norek']);
                $angsuran_pokok=mysql_real_escape_string($row['pokok']);
                $angsuran_margin=mysql_real_escape_string($row['ijaroh']);
                $angsuran=mysql_real_escape_string($row['angsuran']);
                $os=mysql_real_escape_string($row['os']);
                $bayar=mysql_real_escape_string($row['bulat']);

                $sisa = $saldo - $os;
                if  ($saldo < $os ){
                    $bayar = 0;                    
                }else{
                    $bayar =$sisa;
                }

                $status_realisasi='';
                $pb='0';
                $ke = '0';
                $tunggakan='0';
                $hari=mysql_real_escape_string($row['hari']);
                $twm=$saldo;
                $bulat=mysql_real_escape_string($row['bulat']);
                $simpanan_wajib='0';
                $simpanan_pokok ='0';                
                $nama=mysql_real_escape_string($row['Cust_Short_name']);
                $nama_kel=mysql_real_escape_string($row['nama_kel']);
                $saldo_margin=mysql_real_escape_string($row['saldo_margin']);
                $plafond=mysql_real_escape_string($row['Plafond']);

             
            
            
            
                $Data_lima[] = "('$unit', '$tgl_tagih', '$code_kel','$cif','$cao','$norek', '$angsuran_pokok','$angsuran_margin','$angsuran','$bayar','$status_realisasi','$pb','$ke','$tunggakan','$hari','$twm','$bulat','$simpanan_wajib','$simpanan_pokok','$os','$nama','$nama_kel','$saldo_margin','$plafond')";
                $Data_pull[] = "('$unit', '$tgl_tagih', '$code_kel','$cif','$cao','$norek', '$angsuran_pokok','$angsuran_margin','$angsuran','$bayar','$status_realisasi','$pb','$ke','$tunggakan','$hari','$twm','$bulat','$simpanan_wajib','$simpanan_pokok','$os','$nama','$nama_kel','$saldo_margin','$plafond','pelunasan19')";
            
            }
            

            $sql = "insert INTO tagihan_pelunasan(unit, tgl_tagih,code_kel,cif,cao,norek,angsuran_pokok,angsuran_margin,angsuran,bayar,status_realisasi,pb,ke,tunggakan,hari,twm,bulat,simpanan_wajib,simpanan_pokok,os,nama,nama_kel,saldo_margin,plafond ) values ";
            $sql .= implode(',', $Data_lima);
            db_query($config_dua, $sql) or die(mysqli_error($config_dua));

            $sql = "insert INTO pull_data(unit, tgl_tagih,code_kel,cif,cao,norek,angsuran_pokok,angsuran_margin,angsuran,bayar,status_realisasi,pb,ke,tunggakan,hari,twm,bulat,simpanan_wajib,simpanan_pokok,os,nama,nama_kel,saldo_margin,plafond,jenis_pull ) values ";
            $sql .= implode(',', $Data_pull);
            db_query($config_dua, $sql) or die(mysqli_error($config_dua));
          


            echo "<script>alert('Pull Data Pelunasan sukses');</script>";
            $select = db_query($config_dua,"SELECT * from pull_data where unit =$userid and tgl_tagih > '$tgl_system'" );
            }else{
                echo "<script>alert('Gagal Pull Data !!!');</script>";
            }

    }elseif($transaksi =='pelunasanDO'){
        $cek = mysql_query("SELECT unit, loan.code_kel as code_kel, loan.cif as cif, loan.cao as cao,saldo_margin,Plafond,
        no_anggota as norek,pokok, ijaroh, angsuran, hari, bulat, os, Cust_Short_name, nama_kel
        FROM loan left join kelompok on loan.code_kel = kelompok.code_kel WHERE  loan.code_kel ='$cif_kel' AND os> 0 ");


        if(mysql_num_rows($cek)>=1){                
            
            while ($row=mysql_fetch_array($cek)){
                $unit=mysql_real_escape_string($row['unit']);
                $tgl_tagih=$tgl_tagih;
                $code_kel=mysql_real_escape_string($row['code_kel']);
                $cif=mysql_real_escape_string($row['cif']);
                $row_sal= mysql_fetch_array(mysql_query("select(sum(KREDIT) - sum(DEBIT))as saldo from simpanan WHERE cif ='$cif' and type not in ('04','05')")) or die(mysql_error());
                $saldo=$row_sal['saldo'];
                if($saldo==null){
                    $saldo=0;
                }else{
                    $saldo = $saldo;
                }
                $cao=mysql_real_escape_string($row['cao']);
                $norek=mysql_real_escape_string($row['norek']);
                $angsuran_pokok=mysql_real_escape_string($row['pokok']);
                $angsuran_margin=mysql_real_escape_string($row['ijaroh']);
                $angsuran=mysql_real_escape_string($row['angsuran']);
                $os=mysql_real_escape_string($row['os']);
                $bayar=mysql_real_escape_string($row['bulat']);

                $sisa = $saldo - $os;
                if  ($saldo < $os ){
                    $bayar = 0;                    
                }else{
                    $bayar =$sisa;
                }

                $status_realisasi='';
                $pb='0';
                $ke = '0';
                $tunggakan='0';
                $hari=mysql_real_escape_string($row['hari']);
                $twm=$saldo;
                $bulat=mysql_real_escape_string($row['bulat']);
                $simpanan_wajib='0';
                $simpanan_pokok ='0';                
                $nama=mysql_real_escape_string($row['Cust_Short_name']);
                $nama_kel=mysql_real_escape_string($row['nama_kel']);
                $saldo_margin=mysql_real_escape_string($row['saldo_margin']);
                $plafond=mysql_real_escape_string($row['Plafond']);

             
            
            
            
                $Data_lima[] = "('$unit', '$tgl_tagih', '$code_kel','$cif','$cao','$norek', '$angsuran_pokok','$angsuran_margin','$angsuran','$bayar','$status_realisasi','$pb','$ke','$tunggakan','$hari','$twm','$bulat','$simpanan_wajib','$simpanan_pokok','$os','$nama','$nama_kel','$saldo_margin','$plafond')";
                $Data_pull[] = "('$unit', '$tgl_tagih', '$code_kel','$cif','$cao','$norek', '$angsuran_pokok','$angsuran_margin','$angsuran','$bayar','$status_realisasi','$pb','$ke','$tunggakan','$hari','$twm','$bulat','$simpanan_wajib','$simpanan_pokok','$os','$nama','$nama_kel','$saldo_margin','$plafond','pelunasanDO')";
            
            }
            

            $sql = "insert INTO tagihan_pelunasan(unit, tgl_tagih,code_kel,cif,cao,norek,angsuran_pokok,angsuran_margin,angsuran,bayar,status_realisasi,pb,ke,tunggakan,hari,twm,bulat,simpanan_wajib,simpanan_pokok,os,nama,nama_kel,saldo_margin,plafond ) values ";
            $sql .= implode(',', $Data_lima);
            db_query($config_dua, $sql) or die(mysqli_error($config_dua));

            $sql = "insert INTO pull_data(unit, tgl_tagih,code_kel,cif,cao,norek,angsuran_pokok,angsuran_margin,angsuran,bayar,status_realisasi,pb,ke,tunggakan,hari,twm,bulat,simpanan_wajib,simpanan_pokok,os,nama,nama_kel,saldo_margin,plafond,jenis_pull ) values ";
            $sql .= implode(',', $Data_pull);
            db_query($config_dua, $sql) or die(mysqli_error($config_dua));
          


            echo "<script>alert('Pull Data Pelunasan DO sukses');</script>";
            $select = db_query($config_dua,"SELECT * from pull_data where unit =$userid and tgl_tagih > '$tgl_system'" );
            }else{
                echo "<script>alert('Gagal Pull Data !!!');</script>";
            }

    }elseif($transaksi =='penarikan'){
        $cek = mysql_query("SELECT unit, loan.code_kel as code_kel, loan.cif as cif, loan.cao as cao,saldo_margin,Plafond,deal_produk
        no_anggota as norek,pokok, ijaroh, angsuran, hari, bulat, os, Cust_Short_name, nama_kel
        FROM loan left join kelompok on loan.code_kel = kelompok.code_kel WHERE  loan.code_kel ='$cif_kel' ");


        if(mysql_num_rows($cek)>=1){                
            
            while ($row=mysql_fetch_array($cek)){
                $unit=mysql_real_escape_string($row['unit']);
                $tgl_tagih=$tgl_tagih;
                $code_kel=mysql_real_escape_string($row['code_kel']);
                $cif=mysql_real_escape_string($row['cif']);
$ijaroh=$row['ijaroh'];
		    $deal_produk=$row['deal_produk'];
		    if($deal_produk=='3'){
$pengurang = $ijaroh + 2500;
}else{
$pengurang =0;
}
                $row_sal= mysql_fetch_array(mysql_query("select(sum(KREDIT) - sum(DEBIT))as saldo from simpanan WHERE cif ='$cif' and type not in ('04','05')")) or die(mysql_error());
                $saldo=$row_sal['saldo'];
                if($saldo==null){
                    $saldo=0;
                }else{
                    $saldo = $saldo-$pengurang;
                }
                $cao=mysql_real_escape_string($row['cao']);
                $norek=mysql_real_escape_string($row['norek']);
                $angsuran_pokok=mysql_real_escape_string($row['pokok']);
                $angsuran_margin=mysql_real_escape_string($row['ijaroh']);
                $angsuran=mysql_real_escape_string($row['angsuran']);
                $os=mysql_real_escape_string($row['os']);
		
                if($nominal > $saldo){
                    $saldo = $saldo;
                }else{
                    $saldo = $nominal;
                }


if($os>0){
 $saldo = $saldo;

}else{
$saldo = $nominal;

}

                $bayar=$saldo;

                $status_realisasi='';
                $pb='0';
                $ke = '0';
                $tunggakan='0';
                $hari=mysql_real_escape_string($row['hari']);
                $twm=$saldo;
                $bulat=$saldo;
                $simpanan_wajib='0';
                $simpanan_pokok ='0';                
                $nama=mysql_real_escape_string($row['Cust_Short_name']);
                $nama_kel=mysql_real_escape_string($row['nama_kel']);
                $saldo_margin=mysql_real_escape_string($row['saldo_margin']);
                $plafond=mysql_real_escape_string($row['Plafond']);

             
            
            
            
                $Data_lima[] = "('$unit', '$tgl_tagih', '$code_kel','$cif','$cao','$norek', '$angsuran_pokok','$angsuran_margin','$angsuran','$bayar','$status_realisasi','$pb','$ke','$tunggakan','$hari','$twm','$bulat','$simpanan_wajib','$simpanan_pokok','$os','$nama','$nama_kel','$saldo_margin','$plafond')";
                $Data_pull[] = "('$unit', '$tgl_tagih', '$code_kel','$cif','$cao','$norek', '$angsuran_pokok','$angsuran_margin','$angsuran','$bayar','$status_realisasi','$pb','$ke','$tunggakan','$hari','$twm','$bulat','$simpanan_wajib','$simpanan_pokok','$os','$nama','$nama_kel','$saldo_margin','$plafond','penarikan')";
            
            }
            

            $sql = "insert INTO tagihan_penarikan(unit, tgl_tagih,code_kel,cif,cao,norek,angsuran_pokok,angsuran_margin,angsuran,bayar,status_realisasi,pb,ke,tunggakan,hari,twm,bulat,simpanan_wajib,simpanan_pokok,os,nama,nama_kel,saldo_margin,plafond ) values ";
            $sql .= implode(',', $Data_lima);
            db_query($config_dua, $sql) or die(mysqli_error($config_dua));

            $sql = "insert INTO pull_data(unit, tgl_tagih,code_kel,cif,cao,norek,angsuran_pokok,angsuran_margin,angsuran,bayar,status_realisasi,pb,ke,tunggakan,hari,twm,bulat,simpanan_wajib,simpanan_pokok,os,nama,nama_kel,saldo_margin,plafond,jenis_pull ) values ";
            $sql .= implode(',', $Data_pull);
            db_query($config_dua, $sql) or die(mysqli_error($config_dua));
          


            echo "<script>alert('Pull Data Pelunasan sukses');</script>";
            $select = db_query($config_dua,"SELECT * from pull_data where unit =$userid and tgl_tagih > '$tgl_system'");
            }else{
                echo "<script>alert('Gagal Pull Data !!!');</script>";
            }
        
   
    }

     break;
           case "02":
            $cek = mysql_query("SELECT unit, calon_loan.code_kel as code_kel, calon_loan.cif as cif, calon_loan.cao as cao,saldo_margin,Plafond,
            no_anggota as norek,pokok, ijaroh, angsuran, hari, bulat, os, Cust_Short_name, nama_kel
            FROM calon_loan left join kelompok on calon_loan.code_kel = kelompok.code_kel WHERE  calon_loan.cif ='$cif_kel' ");
		var_dump($cif_kel);
    
         if($transaksi == 'lima'){  
                    
            if(mysql_num_rows($cek)>=1){                
                    
                while ($row=mysql_fetch_array($cek)){
                    $unit=mysql_real_escape_string($row['unit']);
                    $tgl_tagih=$tgl_tagih;
                    $code_kel=mysql_real_escape_string($row['code_kel']);
                    $cif=mysql_real_escape_string($row['cif']);
                    $cao=mysql_real_escape_string($row['cao']);
                    $norek=mysql_real_escape_string($row['norek']);
                    $angsuran_pokok=mysql_real_escape_string($row['pokok']);
                    $angsuran_margin=mysql_real_escape_string($row['ijaroh']);
                    $angsuran=mysql_real_escape_string($row['angsuran']);
                    $bayar=mysql_real_escape_string($row['Plafond'] * (5/100));
                    $status_realisasi='';
                    $pb='0';
                    $ke = '0';
                    $tunggakan='0';
                    $hari=mysql_real_escape_string($row['hari']);
                    $twm='0';
                    $bulat=$bayar;
                    $simpanan_wajib='0';
                    $simpanan_pokok ='0';
                    $os=mysql_real_escape_string($row['os']);
                    $nama=mysql_real_escape_string($row['Cust_Short_name']);
                    $nama_kel=mysql_real_escape_string($row['nama_kel']);
                    $saldo_margin=mysql_real_escape_string($row['saldo_margin']);
                    $plafond=mysql_real_escape_string($row['Plafond']);
    
                 
                
                
                
                    $Data_lima[] = "('$unit', '$tgl_tagih', '$code_kel','$cif','$cao','$norek', '$angsuran_pokok','$angsuran_margin','$angsuran','$bayar','$status_realisasi','$pb','$ke','$tunggakan','$hari','$twm','$bulat','$simpanan_wajib','$simpanan_pokok','$os','$nama','$nama_kel','$saldo_margin','$plafond')";
                    $Data_pull[] = "('$unit', '$tgl_tagih', '$code_kel','$cif','$cao','$norek', '$angsuran_pokok','$angsuran_margin','$angsuran','$bayar','$status_realisasi','$pb','$ke','$tunggakan','$hari','$twm','$bulat','$simpanan_wajib','$simpanan_pokok','$os','$nama','$nama_kel','$saldo_margin','$plafond','lima')";
                
                }
    
                $sql = "insert INTO tagihan_lima_persen(unit, tgl_tagih,code_kel,cif,cao,norek,angsuran_pokok,angsuran_margin,angsuran,bayar,status_realisasi,pb,ke,tunggakan,hari,twm,bulat,simpanan_wajib,simpanan_pokok,os,nama,nama_kel,saldo_margin,plafond ) values ";
                $sql .= implode(',', $Data_lima);
                db_query($config_dua, $sql) or die(mysqli_error($config_dua));
    
                $sql = "insert INTO pull_data(unit, tgl_tagih,code_kel,cif,cao,norek,angsuran_pokok,angsuran_margin,angsuran,bayar,status_realisasi,pb,ke,tunggakan,hari,twm,bulat,simpanan_wajib,simpanan_pokok,os,nama,nama_kel,saldo_margin,plafond,jenis_pull ) values ";
                $sql .= implode(',', $Data_pull);
                db_query($config_dua, $sql) or die(mysqli_error($config_dua));
              
    
    
                echo "<script>alert('Pull Data 5% sukses');</script>";
                $select = db_query($config_dua,"SELECT * from pull_data where unit =$userid and tgl_tagih > '$tgl_system'");
                }else{
                    echo "<script>alert('Gagal Pull Data !!!');</script>";
                }
            
            }elseif($transaksi =='lebaran'){
    
                if(mysql_num_rows($cek)>=1){                
                    
                    while ($row=mysql_fetch_array($cek)){
                        $unit=mysql_real_escape_string($row['unit']);
                        $tgl_tagih=$tgl_tagih;
                        $code_kel=mysql_real_escape_string($row['code_kel']);
                        $cif=mysql_real_escape_string($row['cif']);
                        $cao=mysql_real_escape_string($row['cao']);
                        $norek=mysql_real_escape_string($row['norek']);
                        $angsuran_pokok=mysql_real_escape_string($row['pokok']);
                        $angsuran_margin=mysql_real_escape_string($row['ijaroh']);
                        $angsuran=mysql_real_escape_string($row['angsuran']);
                        $bayar=mysql_real_escape_string($row['bulat']);
                        $status_realisasi='';
                        $pb='0';
                        $ke = '0';
                        $tunggakan='0';
                        $hari=mysql_real_escape_string($row['hari']);
                        $twm='0';
                        $bulat=mysql_real_escape_string($row['bulat']);
                        $simpanan_wajib='0';
                        $simpanan_pokok ='0';
                        $os=mysql_real_escape_string($row['os']);
                        $nama=mysql_real_escape_string($row['Cust_Short_name']);
                        $nama_kel=mysql_real_escape_string($row['nama_kel']);
                        $saldo_margin=mysql_real_escape_string($row['saldo_margin']);
                        $plafond=mysql_real_escape_string($row['Plafond']);
        
                     
                    
                    
                    
                        $Data_lima[] = "('$unit', '$tgl_tagih', '$code_kel','$cif','$cao','$norek', '$angsuran_pokok','$angsuran_margin','$angsuran','$bayar','$status_realisasi','$pb','$ke','$tunggakan','$hari','$twm','$bulat','$simpanan_wajib','$simpanan_pokok','$os','$nama','$nama_kel','$saldo_margin','$plafond')";
                        $Data_pull[] = "('$unit', '$tgl_tagih', '$code_kel','$cif','$cao','$norek', '$angsuran_pokok','$angsuran_margin','$angsuran','$bayar','$status_realisasi','$pb','$ke','$tunggakan','$hari','$twm','$bulat','$simpanan_wajib','$simpanan_pokok','$os','$nama','$nama_kel','$saldo_margin','$plafond','lebaran')";
                    
                    }
        
                    $sql = "insert INTO tagihan_lebaran(unit, tgl_tagih,code_kel,cif,cao,norek,angsuran_pokok,angsuran_margin,angsuran,bayar,status_realisasi,pb,ke,tunggakan,hari,twm,bulat,simpanan_wajib,simpanan_pokok,os,nama,nama_kel,saldo_margin,plafond ) values ";
                    $sql .= implode(',', $Data_lima);
                    db_query($config_dua, $sql) or die(mysqli_error($config_dua));
        
                    $sql = "insert INTO pull_data(unit, tgl_tagih,code_kel,cif,cao,norek,angsuran_pokok,angsuran_margin,angsuran,bayar,status_realisasi,pb,ke,tunggakan,hari,twm,bulat,simpanan_wajib,simpanan_pokok,os,nama,nama_kel,saldo_margin,plafond,jenis_pull ) values ";
                    $sql .= implode(',', $Data_pull);
                    db_query($config_dua, $sql) or die(mysqli_error($config_dua));
                  
        
        
                    echo "<script>alert('Pull Data lebaran sukses');</script>";
                    $select = db_query($config_dua,"SELECT * from pull_data where unit =$userid and tgl_tagih > '$tgl_system'");
                    }else{
                        echo "<script>alert('Gagal Pull Data !!!');</script>";
                    }
                
           
            }elseif($transaksi =='pelunasan'){
            $cek = mysql_query("SELECT unit, loan.code_kel as code_kel, loan.cif as cif, loan.cao as cao,saldo_margin,Plafond,
            no_anggota as norek,pokok, ijaroh, angsuran, hari, bulat, os, Cust_Short_name, nama_kel
            FROM loan left join kelompok on loan.code_kel = kelompok.code_kel WHERE  loan.cif ='$cif_kel' AND os> 0 ");
    
    
            if(mysql_num_rows($cek)>=1){                
                
                while ($row=mysql_fetch_array($cek)){
                    $unit=mysql_real_escape_string($row['unit']);
                    $tgl_tagih=$tgl_tagih;
                    $code_kel=mysql_real_escape_string($row['code_kel']);
                    $cif=mysql_real_escape_string($row['cif']);
                    $row_sal= mysql_fetch_array(mysql_query("select(sum(KREDIT) - sum(DEBIT))as saldo from simpanan WHERE cif ='$cif' and type not in ('05','04')")) or die(mysql_error());
                    $saldo=$row_sal['saldo'];
                    if($saldo==null){
                        $saldo=0;
                    }else{
                        $saldo = $saldo;
                    }
                    $cao=mysql_real_escape_string($row['cao']);
                    $norek=mysql_real_escape_string($row['norek']);
                    $angsuran_pokok=mysql_real_escape_string($row['pokok']);
                    $angsuran_margin=mysql_real_escape_string($row['ijaroh']);
                    $angsuran=mysql_real_escape_string($row['angsuran']);
                    $os=mysql_real_escape_string($row['os']);
                    $bayar=mysql_real_escape_string($row['bulat']);
    
                    $sisa = $saldo - $os;
                    if  ($saldo < $os ){
                        $bayar = 0;                    
                    }else{
                        $bayar =$sisa;
                    }
    
                    $status_realisasi='';
                    $pb='0';
                    $ke = '0';
                    $tunggakan='0';
                    $hari=mysql_real_escape_string($row['hari']);
                    $twm=$saldo;
                    $bulat=mysql_real_escape_string($row['bulat']);
                    $simpanan_wajib='0';
                    $simpanan_pokok ='0';                
                    $nama=mysql_real_escape_string($row['Cust_Short_name']);
                    $nama_kel=mysql_real_escape_string($row['nama_kel']);
                    $saldo_margin=mysql_real_escape_string($row['saldo_margin']);
                    $plafond=mysql_real_escape_string($row['Plafond']);
    
                 
                
                
                
                    $Data_lima[] = "('$unit', '$tgl_tagih', '$code_kel','$cif','$cao','$norek', '$angsuran_pokok','$angsuran_margin','$angsuran','$bayar','$status_realisasi','$pb','$ke','$tunggakan','$hari','$twm','$bulat','$simpanan_wajib','$simpanan_pokok','$os','$nama','$nama_kel','$saldo_margin','$plafond')";
                    $Data_pull[] = "('$unit', '$tgl_tagih', '$code_kel','$cif','$cao','$norek', '$angsuran_pokok','$angsuran_margin','$angsuran','$bayar','$status_realisasi','$pb','$ke','$tunggakan','$hari','$twm','$bulat','$simpanan_wajib','$simpanan_pokok','$os','$nama','$nama_kel','$saldo_margin','$plafond','pelunasan')";
                
                }
                
    
                $sql = "insert INTO tagihan_pelunasan(unit, tgl_tagih,code_kel,cif,cao,norek,angsuran_pokok,angsuran_margin,angsuran,bayar,status_realisasi,pb,ke,tunggakan,hari,twm,bulat,simpanan_wajib,simpanan_pokok,os,nama,nama_kel,saldo_margin,plafond ) values ";
                $sql .= implode(',', $Data_lima);
                db_query($config_dua, $sql) or die(mysqli_error($config_dua));
    
                $sql = "insert INTO pull_data(unit, tgl_tagih,code_kel,cif,cao,norek,angsuran_pokok,angsuran_margin,angsuran,bayar,status_realisasi,pb,ke,tunggakan,hari,twm,bulat,simpanan_wajib,simpanan_pokok,os,nama,nama_kel,saldo_margin,plafond,jenis_pull ) values ";
                $sql .= implode(',', $Data_pull);
                db_query($config_dua, $sql) or die(mysqli_error($config_dua));
              
    
    
                echo "<script>alert('Pull Data Pelunasan sukses');</script>";
                $select = db_query($config_dua,"SELECT * from pull_data where unit =$userid and tgl_tagih > '$tgl_system'");
                }else{
                    echo "<script>alert('Gagal Pull Data !!!');</script>";
                }


  }elseif($transaksi =='pelunasanDO'){
            $cek = mysql_query("SELECT unit, loan.code_kel as code_kel, loan.cif as cif, loan.cao as cao,saldo_margin,Plafond,
            no_anggota as norek,pokok, ijaroh, angsuran, hari, bulat, os, Cust_Short_name, nama_kel
            FROM loan left join kelompok on loan.code_kel = kelompok.code_kel WHERE  loan.cif ='$cif_kel' AND os> 0 ");
    
    
            if(mysql_num_rows($cek)>=1){                
                
                while ($row=mysql_fetch_array($cek)){
                    $unit=mysql_real_escape_string($row['unit']);
                    $tgl_tagih=$tgl_tagih;
                    $code_kel=mysql_real_escape_string($row['code_kel']);
                    $cif=mysql_real_escape_string($row['cif']);
                    $row_sal= mysql_fetch_array(mysql_query("select(sum(KREDIT) - sum(DEBIT))as saldo from simpanan WHERE cif ='$cif' and type not in ('05','04')")) or die(mysql_error());
                    $saldo=$row_sal['saldo'];
                    if($saldo==null){
                        $saldo=0;
                    }else{
                        $saldo = $saldo;
                    }
                    $cao=mysql_real_escape_string($row['cao']);
                    $norek=mysql_real_escape_string($row['norek']);
                    $angsuran_pokok=mysql_real_escape_string($row['pokok']);
                    $angsuran_margin=mysql_real_escape_string($row['ijaroh']);
                    $angsuran=mysql_real_escape_string($row['angsuran']);
                    $os=mysql_real_escape_string($row['os']);
                    $bayar=mysql_real_escape_string($row['bulat']);
    
                    $sisa = $saldo - $os;
                    if  ($saldo < $os ){
                        $bayar = 0;                    
                    }else{
                        $bayar =$sisa;
                    }
    
                    $status_realisasi='';
                    $pb='0';
                    $ke = '0';
                    $tunggakan='0';
                    $hari=mysql_real_escape_string($row['hari']);
                    $twm=$saldo;
                    $bulat=mysql_real_escape_string($row['bulat']);
                    $simpanan_wajib='0';
                    $simpanan_pokok ='0';                
                    $nama=mysql_real_escape_string($row['Cust_Short_name']);
                    $nama_kel=mysql_real_escape_string($row['nama_kel']);
                    $saldo_margin=mysql_real_escape_string($row['saldo_margin']);
                    $plafond=mysql_real_escape_string($row['Plafond']);
    
                 
                
                
                
                    $Data_lima[] = "('$unit', '$tgl_tagih', '$code_kel','$cif','$cao','$norek', '$angsuran_pokok','$angsuran_margin','$angsuran','$bayar','$status_realisasi','$pb','$ke','$tunggakan','$hari','$twm','$bulat','$simpanan_wajib','$simpanan_pokok','$os','$nama','$nama_kel','$saldo_margin','$plafond')";
                    $Data_pull[] = "('$unit', '$tgl_tagih', '$code_kel','$cif','$cao','$norek', '$angsuran_pokok','$angsuran_margin','$angsuran','$bayar','$status_realisasi','$pb','$ke','$tunggakan','$hari','$twm','$bulat','$simpanan_wajib','$simpanan_pokok','$os','$nama','$nama_kel','$saldo_margin','$plafond','pelunasanDO')";
                
                }
                
    
                $sql = "insert INTO tagihan_pelunasan(unit, tgl_tagih,code_kel,cif,cao,norek,angsuran_pokok,angsuran_margin,angsuran,bayar,status_realisasi,pb,ke,tunggakan,hari,twm,bulat,simpanan_wajib,simpanan_pokok,os,nama,nama_kel,saldo_margin,plafond ) values ";
                $sql .= implode(',', $Data_lima);
                db_query($config_dua, $sql) or die(mysqli_error($config_dua));
    
                $sql = "insert INTO pull_data(unit, tgl_tagih,code_kel,cif,cao,norek,angsuran_pokok,angsuran_margin,angsuran,bayar,status_realisasi,pb,ke,tunggakan,hari,twm,bulat,simpanan_wajib,simpanan_pokok,os,nama,nama_kel,saldo_margin,plafond,jenis_pull ) values ";
                $sql .= implode(',', $Data_pull);
                db_query($config_dua, $sql) or die(mysqli_error($config_dua));
              
    
    
                echo "<script>alert('Pull Data Pelunasan sukses');</script>";
                $select = db_query($config_dua,"SELECT * from pull_data where unit =$userid and tgl_tagih > '$tgl_system'");
                }else{
                    echo "<script>alert('Gagal Pull Data !!!');</script>";
                }


  }elseif($transaksi =='pelunasanRestMargin'){
            $cek = mysql_query("SELECT unit, loan.code_kel as code_kel, loan.cif as cif, loan.cao as cao,saldo_margin,Plafond,
            no_anggota as norek,pokok, ijaroh, angsuran, hari, bulat, os, Cust_Short_name, nama_kel
            FROM loan left join kelompok on loan.code_kel = kelompok.code_kel WHERE  loan.cif ='$cif_kel' AND os> 0 ");
    
    
            if(mysql_num_rows($cek)>=1){                
                
                while ($row=mysql_fetch_array($cek)){
                    $unit=mysql_real_escape_string($row['unit']);
                    $tgl_tagih=$tgl_tagih;
                    $code_kel=mysql_real_escape_string($row['code_kel']);
                    $cif=mysql_real_escape_string($row['cif']);
                    $row_sal= mysql_fetch_array(mysql_query("select(sum(KREDIT) - sum(DEBIT))as saldo from simpanan WHERE cif ='$cif' and type not in ('05','04')")) or die(mysql_error());
                    $saldo=$row_sal['saldo'];
                    if($saldo==null){
                        $saldo=0;
                    }else{
                        $saldo = $saldo;
                    }
                    $cao=mysql_real_escape_string($row['cao']);
                    $norek=mysql_real_escape_string($row['norek']);
                    $angsuran_pokok=mysql_real_escape_string($row['pokok']);
                    $angsuran_margin=mysql_real_escape_string($row['ijaroh']);
                    $angsuran=mysql_real_escape_string($row['angsuran']);
                    $os=mysql_real_escape_string($row['os']);
                    $bayar=mysql_real_escape_string($row['bulat']);
    
                    $sisa = $saldo - $os;
                    if  ($saldo < $os ){
                        $bayar = 0;                    
                    }else{
                        $bayar =$sisa;
                    }
    
                    $status_realisasi='';
                    $pb='0';
                    $ke = '0';
                    $tunggakan='0';
                    $hari=mysql_real_escape_string($row['hari']);
                    $twm=$saldo;
                    $bulat=mysql_real_escape_string($row['bulat']);
                    $simpanan_wajib='0';
                    $simpanan_pokok ='0';                
                    $nama=mysql_real_escape_string($row['Cust_Short_name']);
                    $nama_kel=mysql_real_escape_string($row['nama_kel']);
                    $saldo_margin=mysql_real_escape_string($row['saldo_margin']);
                    $plafond=mysql_real_escape_string($row['Plafond']);
    
                 
                
                
                
                    $Data_lima[] = "('$unit', '$tgl_tagih', '$code_kel','$cif','$cao','$norek', '$angsuran_pokok','$angsuran_margin','$angsuran','$bayar','$status_realisasi','$pb','$ke','$tunggakan','$hari','$twm','$bulat','$simpanan_wajib','$simpanan_pokok','$os','$nama','$nama_kel','$saldo_margin','$plafond')";
                    $Data_pull[] = "('$unit', '$tgl_tagih', '$code_kel','$cif','$cao','$norek', '$angsuran_pokok','$angsuran_margin','$angsuran','$bayar','$status_realisasi','$pb','$ke','$tunggakan','$hari','$twm','$bulat','$simpanan_wajib','$simpanan_pokok','$os','$nama','$nama_kel','$saldo_margin','$plafond','pelunasanRestMargin')";
                
                }
                
    
                $sql = "insert INTO tagihan_pelunasan(unit, tgl_tagih,code_kel,cif,cao,norek,angsuran_pokok,angsuran_margin,angsuran,bayar,status_realisasi,pb,ke,tunggakan,hari,twm,bulat,simpanan_wajib,simpanan_pokok,os,nama,nama_kel,saldo_margin,plafond ) values ";
                $sql .= implode(',', $Data_lima);
                db_query($config_dua, $sql) or die(mysqli_error($config_dua));
    
                $sql = "insert INTO pull_data(unit, tgl_tagih,code_kel,cif,cao,norek,angsuran_pokok,angsuran_margin,angsuran,bayar,status_realisasi,pb,ke,tunggakan,hari,twm,bulat,simpanan_wajib,simpanan_pokok,os,nama,nama_kel,saldo_margin,plafond,jenis_pull ) values ";
                $sql .= implode(',', $Data_pull);
                db_query($config_dua, $sql) or die(mysqli_error($config_dua));
              
    
    
                echo "<script>alert('Pull Data Pelunasan Rest Pokok Margin sukses');</script>";
                $select = db_query($config_dua,"SELECT * from pull_data where unit =$userid and tgl_tagih > '$tgl_system'");
                }else{
                    echo "<script>alert('Gagal Pull Data !!!');</script>";
                }



}elseif($transaksi =='pelunasan19'){

 $cek_run_tenor= mysql_fetch_array(mysql_query("select run_tenor,angsuran from loan  WHERE cif ='$cif_kel' ")) or die(mysql_error());
                $run_tenor=$cek_run_tenor['run_tenor'];
$angsuran = $cek_run_tenor['angsuran'];
$cek_tunggak= mysql_fetch_array(mysql_query("select sum(KREDIT)- sum(DEBIT) as tunggak from tunggakan WHERE cif ='$cif_kel'"));
        $ada_tunggak=$cek_tunggak['tunggak']; 
        $ft=round(compat_num($ada_tunggak/$angsuran));  
  
            $cek = mysql_query("SELECT unit, loan.code_kel as code_kel, loan.cif as cif, loan.cao as cao,saldo_margin,Plafond,
            no_anggota as norek,pokok, ijaroh, angsuran, hari, bulat, os, Cust_Short_name, nama_kel
            FROM loan left join kelompok on loan.code_kel = kelompok.code_kel WHERE  loan.cif ='$cif_kel' AND os> 0 ");

	if($run_tenor<=18){
	
	if($ft<=0){
    
    
            if(mysql_num_rows($cek)>=1){                
                
                while ($row=mysql_fetch_array($cek)){
                    $unit=mysql_real_escape_string($row['unit']);
                    $tgl_tagih=$tgl_tagih;
                    $code_kel=mysql_real_escape_string($row['code_kel']);
                    $cif=mysql_real_escape_string($row['cif']);
                    $row_sal= mysql_fetch_array(mysql_query("select(sum(KREDIT) - sum(DEBIT))as saldo from simpanan WHERE cif ='$cif' and type not in ('04','05')")) or die(mysql_error());
                    $saldo=$row_sal['saldo'];
                    if($saldo==null){
                        $saldo=0;
                    }else{
                        $saldo = $saldo;
                    }
                    $cao=mysql_real_escape_string($row['cao']);
                    $norek=mysql_real_escape_string($row['norek']);
                    $angsuran_pokok=mysql_real_escape_string($row['pokok']);
                    $angsuran_margin=mysql_real_escape_string($row['ijaroh']);
                    $angsuran=mysql_real_escape_string($row['angsuran']);
                    $os=mysql_real_escape_string($row['os']);
                    $bayar=mysql_real_escape_string($row['bulat']);
    
                    $sisa = $saldo - $os;
                    if  ($saldo < $os ){
                        $bayar = 0;                    
                    }else{
                        $bayar =$sisa;
                    }
    
                    $status_realisasi='';
                    $pb='0';
                    $ke = '0';
                    $tunggakan='0';
                    $hari=mysql_real_escape_string($row['hari']);
                    $twm=$saldo;
                    $bulat=mysql_real_escape_string($row['bulat']);
                    $simpanan_wajib='0';
                    $simpanan_pokok ='0';                
                    $nama=mysql_real_escape_string($row['Cust_Short_name']);
                    $nama_kel=mysql_real_escape_string($row['nama_kel']);
                    $saldo_margin=mysql_real_escape_string($row['saldo_margin']);
                    $plafond=mysql_real_escape_string($row['Plafond']);
    
                 
                
                
                
                    $Data_lima[] = "('$unit', '$tgl_tagih', '$code_kel','$cif','$cao','$norek', '$angsuran_pokok','$angsuran_margin','$angsuran','$bayar','$status_realisasi','$pb','$ke','$tunggakan','$hari','$twm','$bulat','$simpanan_wajib','$simpanan_pokok','$os','$nama','$nama_kel','$saldo_margin','$plafond')";
                    $Data_pull[] = "('$unit', '$tgl_tagih', '$code_kel','$cif','$cao','$norek', '$angsuran_pokok','$angsuran_margin','$angsuran','$bayar','$status_realisasi','$pb','$ke','$tunggakan','$hari','$twm','$bulat','$simpanan_wajib','$simpanan_pokok','$os','$nama','$nama_kel','$saldo_margin','$plafond','pelunasan19')";
                
                }
                
    
                $sql = "insert INTO tagihan_pelunasan(unit, tgl_tagih,code_kel,cif,cao,norek,angsuran_pokok,angsuran_margin,angsuran,bayar,status_realisasi,pb,ke,tunggakan,hari,twm,bulat,simpanan_wajib,simpanan_pokok,os,nama,nama_kel,saldo_margin,plafond ) values ";
                $sql .= implode(',', $Data_lima);
                db_query($config_dua, $sql) or die(mysqli_error($config_dua));
    
                $sql = "insert INTO pull_data(unit, tgl_tagih,code_kel,cif,cao,norek,angsuran_pokok,angsuran_margin,angsuran,bayar,status_realisasi,pb,ke,tunggakan,hari,twm,bulat,simpanan_wajib,simpanan_pokok,os,nama,nama_kel,saldo_margin,plafond,jenis_pull ) values ";
                $sql .= implode(',', $Data_pull);
                db_query($config_dua, $sql) or die(mysqli_error($config_dua));
              
    
    
                echo "<script>alert('Pull Data Pelunasan sukses');</script>";
                $select = db_query($config_dua,"SELECT * from pull_data where unit =$userid and tgl_tagih > '$tgl_system'");
                }else{
                    echo "<script>alert('Gagal Pull Data !!!');</script>";
                }
	}else{
 echo "<script>alert('Maaf CIF tidak masuk kategori diskon margin');</script>";

}
}else{
 echo "<script>alert('Maaf CIF tidak masuk kategori diskon margin');</script>";
}


            
       
        }elseif($transaksi =='penarikan'){
            $cek = mysql_query("SELECT unit, loan.code_kel as code_kel, loan.cif as cif, loan.cao as cao,saldo_margin,Plafond,deal_produk,
            no_anggota as norek,pokok, ijaroh, angsuran, hari, bulat, os, Cust_Short_name, nama_kel
            FROM loan left join kelompok on loan.code_kel = kelompok.code_kel WHERE  loan.cif ='$cif_kel' ");
    
    
            if(mysql_num_rows($cek)>=1){                
                
                while ($row=mysql_fetch_array($cek)){
                    $unit=mysql_real_escape_string($row['unit']);
                    $tgl_tagih=$tgl_tagih;
                    $code_kel=mysql_real_escape_string($row['code_kel']);
		    $ijaroh=$row['ijaroh'];
		    $deal_produk=$row['deal_produk'];
		    if($deal_produk=='3'){
$pengurang = $ijaroh + 2500;
}else{
$pengurang =0;
}
                    $cif=mysql_real_escape_string($row['cif']);
                    $row_sal= mysql_fetch_array(mysql_query("select(sum(KREDIT) - sum(DEBIT))as saldo from simpanan WHERE cif ='$cif' and type not in ('04','05')")) or die(mysql_error());
                    $saldo=$row_sal['saldo'];
                    if($saldo==null){
                        $saldo=0;
                    }else{
                        $saldo = $saldo-$pengurang;
                    }
                    $cao=mysql_real_escape_string($row['cao']);
                    $norek=mysql_real_escape_string($row['norek']);
                    $angsuran_pokok=mysql_real_escape_string($row['pokok']);
                    $angsuran_margin=mysql_real_escape_string($row['ijaroh']);
                    $angsuran=mysql_real_escape_string($row['angsuran']);
                    $os=mysql_real_escape_string($row['os']);
                    if($nominal > $saldo){
                        $saldo = $saldo;
                    }else{
                        $saldo = $nominal;
                    }


if($os>0){
 $saldo = $saldo;

}else{
$saldo = $nominal;

}
                    $bayar=$saldo;
    
                    
                    $status_realisasi='';
                    $pb='0';
                    $ke = '0';
                    $tunggakan='0';
                    $hari=mysql_real_escape_string($row['hari']);
                    $twm=$saldo;
                    $bulat=$saldo;
                    $simpanan_wajib='0';
                    $simpanan_pokok ='0';                
                    $nama=mysql_real_escape_string($row['Cust_Short_name']);
                    $nama_kel=mysql_real_escape_string($row['nama_kel']);
                    $saldo_margin=mysql_real_escape_string($row['saldo_margin']);
                    $plafond=mysql_real_escape_string($row['Plafond']);
    
                 
                
                
                
                    $Data_lima[] = "('$unit', '$tgl_tagih', '$code_kel','$cif','$cao','$norek', '$angsuran_pokok','$angsuran_margin','$angsuran','$bayar','$status_realisasi','$pb','$ke','$tunggakan','$hari','$twm','$bulat','$simpanan_wajib','$simpanan_pokok','$os','$nama','$nama_kel','$saldo_margin','$plafond')";
                    $Data_pull[] = "('$unit', '$tgl_tagih', '$code_kel','$cif','$cao','$norek', '$angsuran_pokok','$angsuran_margin','$angsuran','$bayar','$status_realisasi','$pb','$ke','$tunggakan','$hari','$twm','$bulat','$simpanan_wajib','$simpanan_pokok','$os','$nama','$nama_kel','$saldo_margin','$plafond','penarikan')";
                
                }
                
    
                $sql = "insert INTO tagihan_penarikan(unit, tgl_tagih,code_kel,cif,cao,norek,angsuran_pokok,angsuran_margin,angsuran,bayar,status_realisasi,pb,ke,tunggakan,hari,twm,bulat,simpanan_wajib,simpanan_pokok,os,nama,nama_kel,saldo_margin,plafond ) values ";
                $sql .= implode(',', $Data_lima);
                db_query($config_dua, $sql) or die(mysqli_error($config_dua));
    
                $sql = "insert INTO pull_data(unit, tgl_tagih,code_kel,cif,cao,norek,angsuran_pokok,angsuran_margin,angsuran,bayar,status_realisasi,pb,ke,tunggakan,hari,twm,bulat,simpanan_wajib,simpanan_pokok,os,nama,nama_kel,saldo_margin,plafond,jenis_pull ) values ";
                $sql .= implode(',', $Data_pull);
                db_query($config_dua, $sql) or die(mysqli_error($config_dua));
              
    
    
                echo "<script>alert('Pull Data penarikan sukses');</script>";
                $select = db_query($config_dua,"SELECT * from pull_data where unit =$userid and tgl_tagih > '$tgl_system'");
                }else{
                    echo "<script>alert('Gagal Pull Data !!!');</script>";
                }
            
       
        }


     break;
      default:
    }
        }

    
  ?>
 
  <?php
    
    $select = db_query($config_dua,"SELECT * from pull_data where unit =$userid and tgl_tagih >= '$tgl_system' ");
    while($data = db_fetch_array($select)){


        ?>
  <tr>
    <td class="style6"><?php echo $data['norek']; ?></td>
    <td class="style6"><?php echo $data['nama'];  ?></td>
    <td class="style6"><?php echo tgl_indo ($data['tgl_tagih']);?></td>
    <td class="style6"><?php echo $data['jenis_pull']; ?></td>
    <td class="style6"><?php echo number_format (compat_num($data['bayar'])); ?></td>
    <td><div align="center"><a href="delete.php?cif=<?php echo $data['cif'];?>&jenis=<?php echo $data['jenis_pull'];?>" class="style6" onClick="return confirm('Apakah Anda yakin?')"><strong><img src="Image/delete.png" width="26" height="22" border="0" longdesc="cetak"></strong></a></div></td>
  </tr>
    
  </tr>
  <?php
        
    }
    }else{
     
    $select = db_query($config_dua,"SELECT * from pull_data where unit =$userid and tgl_tagih >= '$tgl_system'");
    while($data = db_fetch_array($select)){


        ?>
  <tr>
    <td class="style6"><?php echo $data['norek']; ?></td>
    <td class="style6"><?php echo $data['nama'];  ?></td>
    <td class="style6"><?php echo tgl_indo ($data['tgl_tagih']);?></td>
    <td class="style6"><?php echo $data['jenis_pull']; ?></td>
    <td class="style6"><?php echo number_format (compat_num($data['bayar'])); ?></td>
    <td><div align="center"><a href="delete.php?cif=<?php echo $data['cif'];?>&jenis=<?php echo $data['jenis_pull'];?>" class="style6" onClick="return confirm('Apakah Anda yakin?')"><strong><img src="Image/delete.png" width="26" height="22" border="0" longdesc="cetak"></strong></a></div></td>
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
$('.nilai').hide();
$('#nominal').hide();

$('#transaksi').change(function(){
if($('#transaksi').val()=='penarikan'){
    $('.nilai').show();
$('#nominal').show();
}
});
});
</script>

</body>

</html>


