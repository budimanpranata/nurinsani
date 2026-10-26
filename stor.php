<?php require_once __DIR__ . '/php8_compat.php';
session_start();
ob_start();
$userid=$_SESSION['userid'];
$tgl_system=$_SESSION['stgl_system'];
//cek apakah user sudah login
if(!isset($_SESSION['stgl_system'])){
echo "<script>alert('Maaf,.tanggal system belum di setup');</script>";

   header("location:param_tgl.php");
}
if(!isset($_SESSION['userid'])){
   header("location:index.html");
}
if($_SESSION['level']!="unit"){
   header("location:index.html");
}

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

<table width="100%" cellpadding="1" bordercolor="#CCCCCC" bgcolor="#669900">
  <tr>
    <td height="20" bordercolor="#FFFFFF" bgcolor="#669900" class="style3 style9">Transaksi Setoran dan Penarikan
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
    <td width="203" height="10" nowrap bgcolor="#B3B3FF"><div align="left" class="style5 style6">No Rerek</div></td>
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
    <option value="01">Setoran Tunai Simpanan Wajib/Pokok</option>
    <option value="02">Tarik Tunai </option>
    <option value="03">Setoran Angsuran</option>
<option value="04">Pemindah Bukuan</option>
<option value="05">Setoran Angsuran Wo</option>
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
  <td height="10" nowrap bgcolor="#B3B3FF"><span class="style7">Jenis Simpanan </span></td>
  <td nowrap><div align="center"><strong>:</strong></div></td>
  <td colspan="2" nowrap><select name="simpan" id="simpan" size="1">
  
    <option value="pokok">Pokok</option>
    <option value="wajib">wajib </option>
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
  include "conn2.php";
  include "function.php";
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

  $kel = mysql_query ("select CODE_KEL,CIF,UNIT,CUST_SHORT_NAME,CAO FROM anggota where  norek ='$norek'");
$kkel = mysql_fetch_array($kel);
$code_kel = $kkel['CODE_KEL'];
$ciff = $kkel['CIF'];
$unit=$kkel['UNIT'];
$nama=mysql_real_escape_string ($kkel['CUST_SHORT_NAME']);
$ds=$kkel['CAO'];

$kodetr=7;
            $kodetr=acakangkahuruf($kodetr);
            $kode_trans="BU/".$unit.$kodetr;

        $saldo=$row_sal['saldo'];
        $select = mysql_query("select DATE_ADD(next_schedule,INTERVAL 7 DAY) as next_schedule,unit,code_kel,no_anggota,cif,ijaroh,Plafond,pokok,os,angsuran,bulat,TIMESTAMPDIFF(WEEK,tgl_murab,next_schedule) AS RUN_TENOR1,run_tenor,TIMESTAMPDIFF(WEEK,tgl_murab,CURDATE()) AS ke,cao  from loan  WHERE cif ='$ciff'");
            while($row = mysql_fetch_array($select)){
                //$next_schedule=$row['next_schedule'];
                $no_anggota=$row['no_anggota'];
                $pokok=$row['pokok'];
                $os=$row['os'];
                $bulat=$row['bulat'];
                $run_tenor=$row['run_tenor'];
                $pb=$run_tenor + 1;
                $next_tenor=$run_tenor + 2;
                $unit=$row['unit'];
                $angsuran=$row['angsuran'];
                $ijaroh=$row['ijaroh'];
               
                $ke=$row['ke'];
                $cif=$row['cif'];
                $cao=$row['cao'];
                $wajib_kh=$bulat - $angsuran;
            }
           
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
$cek_wajib = mysql_query("select SUM(KREDIT-DEBIT) as wajib from simpanan_wajib where cif ='$ciff'");
$cwajib = mysql_fetch_array($cek_wajib);
$wajib = $cwajib['wajib'];
$cek_pokok = mysql_query("select SUM(KREDIT-DEBIT) as pokok from simpanan_pokok where cif ='$ciff'");
$cpokok = mysql_fetch_array($cek_pokok);
$pokok = $cpokok['pokok'];

if ($pokok == 0 && $wajib >= 0)  {
                $in_pokok=50000;
               $ket="Setoran simpanan Pokok an $nama";
                    $input ="Insert into simpanan_pokok (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,blok,kode_transaksi)VALUES('$tgl_system','$norek','$unit','$ciff','$code_kel',0,'04','$in_pokok','$userid','Simpanan Pokok','','$cao','4','$kode_trans')";
                    $inputtrnsk = mysql_query($input);

                    @mysql_query("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                    values('$unit','$kode_trans','1120000','$tgl_system','Bukti SYSTEM','$ket','$in_pokok','$userid')");
                    @mysql_query("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                    values('$unit','$kode_trans','3102000','$tgl_system','Bukti SYSTEM','$ket','$in_pokok','$userid')");
              
            //HITUNG SIA SIMPANAN JIKA SISA > 0 MAKA SIMPAN DI WADIAH
                $sisa = $nominal - $in_pokok;
                if($sisa>0){
                   $ket="Setoran simpanan 5% an $nama";
                        $input ="Insert into simpanan_wajib (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,blok,kode_transaksi)VALUES('$tgl_system','$norek','$unit','$ciff','$code_kel',0,'05','$sisa','$userid','PB Simpanan Wajib','','$cao','1','$kode_trans')";
                        $inputtrnsk = mysql_query($input);
                        mysql_query("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                        values('$unit','$kode_trans','1120000','$tgl_system','Bukti SYSTEM','$ket1','$sisa','$userid')");

                        mysql_query("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                        values('$unit','$kode_trans','3202000','$tgl_system','Bukti SYSTEM','$ket1','$sisa','$userid')");
                  
                }
              
            }
          
            //JIKA PUNYA POKOK DA BELUM MEMPUNYA WAJIB
            elseif($pokok == 50000 && $wajib >= 0){
                $ket="Setoran simpanan 5% an $nama";

                    $input ="Insert into simpanan_wajib (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,blok,kode_transaksi)VALUES('$tgl_system','$norek','$unit','$ciff','$code_kel',0,'05','$nominal','$userid','PB Simpanan Wajib','','$cao','1','$kode_trans')";
                        $inputtrnsk = mysql_query($input);


                    @mysql_query("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                    values('$unit','$kode_trans','1120000','$tgl_system','Bukti SYSTEM','$ket','$nominal','$userid')");
                    @mysql_query("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                    values('$unit','$kode_trans','3202000','$tgl_system','Bukti SYSTEM','$ket','$nominal','$userid')");
              

              
            }
           
           
            //JIKA BELUMPUNYA WAJIB DAN POKOK SPLIT SIMPANANT KE POKOK DAN WAJIB
            elseif($pokok == 0 && $wajib == 0){
                $in_pokok=50000;
               $ket="Setoran simpanan 5% an $nama";
                   
                    $input ="Insert into simpanan_pokok (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,blok,kode_transaksi)VALUES('$tgl_system','$norek','$unit','$ciff','$code_kel',0,'04','$in_pokok','$userid','Simpanan Pokok','','$cao','4','$kode_trans')";
                    $inputtrnsk = mysql_query($input);
                    @mysql_query("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                    values('$unit','$kode_trans','1120000','$tgl_system','Bukti SYSTEM','$ket','$in_pokok','$userid')");
                    @mysql_query("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                    values('$unit','$kode_trans','3102000','$tgl_system','Bukti SYSTEM','$ket','$in_pokok','$userid')");
              

              
                //HITUNG JIKA SALDO CUKU UNTUK POKOK KRDIT KE POKOK
                    $sisa_pokok = $nominal - $in_pokok;
                    if ($sisa_pokok>0){
                        $ket="Setoran simpanan 5% an $nama";
                   

                        //$input ="Insert into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,blok,tgl_input,kode_transaksi)VALUES('$tgl_system','$norek','$unit','$ciff','$code_kel',0,'05','$wajib_in','$userid','PB Simpanan Wajib','','$cao','1','$tanggal','$kode_trans')";
                        //$inputtrnsk = mysql_query($input);
                        //$input ="Insert into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,blok,tgl_input,kode_transaksi)VALUES('$tgl_system','$norek','$unit','$ciff','$code_kel','$wajib_in','05','0','$userid','PB Simpanan Wajib','','$cao','1','$tanggal','$kode_trans')";
                        //$inputtrnsk = mysql_query($input);

                        $input ="Insert into simpanan_wajib (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,blok,kode_transaksi)VALUES('$tgl_system','$norek','$unit','$ciff','$code_kel',0,'05','$sisa_pokok','$userid','PB Simpanan Wajib','','$cao','1','$kode_trans')";
                        $inputtrnsk = mysql_query($input);
                    @mysql_query("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                    values('$unit','$kode_trans','1120000','$tgl_system','Bukti SYSTEM','$ket','$wajib_in','$userid')");
                    @mysql_query("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                    values('$unit','$kode_trans','3202000','$tgl_system','Bukti SYSTEM','$ket','$wajib_in','$userid')");
                    } 
                   
            }else {
                $ket1="Wadiah $nama";
              
                   $input ="Insert into simpanan_wajib (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,blok,kode_transaksi)VALUES('$tgl_system','$norek','$unit','$ciff','$code_kel',0,'05','$sisa_pokok','$userid','PB Simpanan Wajib','','$cao','1','$kode_trans')";
                        $inputtrnsk = mysql_query($input);
                    mysql_query("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                    values('$unit','$kode_trans','1120000','$tgl_system','Bukti SYSTEM','$ket1','$nominal','$userid')");

                    mysql_query("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                    values('$unit','$kode_trans','3202000','$tgl_system','Bukti SYSTEM','$ket1','$nominal','$userid')");
           
              
            }              
    echo "<script>alert('Nominal sudah ditambahkan pada rekening $norek !!!');</script>";
    $select1 = mysql_query("SET @bal = 0;");
    $select = mysql_query("SELECT simpanan_wajib.NOREK,simpanan_wajib.BUSS_DATE,anggota.CUST_SHORT_NAME,DEBIT,KREDIT,@bal:=(@bal+KREDIT)-DEBIT as balance FROM simpanan_wajib
    left join anggota on anggota.cif = simpanan_wajib.cif
    where simpanan_wajib.cif='$ciff' order by simpanan_wajib.REFF");     
break;
       case "02":
      $ket =$keterangan;
$area = mysql_query ("select code_area FROM branch where kode_branch ='$unit'");
$c_area = mysql_fetch_array($area);
$code_area=$c_area['code_area'];
     $SA = mysql_query ("select sum(KREDIT)- sum(DEBIT) as SALDO  FROM simpanan where  cif ='$ciff' and type not in ('04','05')");
     $SAL = mysql_fetch_array($SA);
     $SALDO = $SAL['SALDO'];
        if ($SALDO < $nominal){
            echo "<script>alert('Maaf,.Saldo Anda Tidak Mencukupi saldo anda adalah $SALDO');</script>";
            echo "<script language='javascript'>document.location.href='stor.php'</script>";
        }
        else{
            $input ="Insert into temp_tarik (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,KREDIT,TYPE,DEBIT,USERID,KET,REFF,CAO,blok,tgl_input,kode_transaksi)VALUES('$tgl_system','$norek','$unit','$ciff','$code_kel',0,'01','$nominal','$userid','$ket','','$ds','$code_area','$tanggal','$kode_trans')";

            $inputtrnsk = mysql_query($input);
            if($inputtrnsk){
                $select1 = mysql_query("SET @bal = 0;");
                $select = mysql_query("SELECT simpanan.NOREK,simpanan.BUSS_DATE,anggota.CUST_SHORT_NAME,DEBIT,KREDIT,@bal:=(@bal+KREDIT)-DEBIT as balance FROM simpanan
                join anggota on anggota.CIF = simpanan.cif
                where simpanan.cif='$ciff' order by simpanan.REFF");
                @mysql_query("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
            values('$unit','$kode_trans','1120000','$tgl_system','Bukti Umum','$ket','$nominal','$userid')");
            @mysql_query("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                    values('$unit','$kode_trans','2101000','$tgl_system','Bukti Umum','$ket','$nominal','$userid')");

            }
            else{
            echo "<script>alert('Maaf,.Gagal Input );</script>";
            echo "<script language='javascript'>document.location.href='stor.php'</script>";
            }
        }   
    break;
     case "03":
     $ket1="Setoran Angsuran An $nama";
     $input ="Insert into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,blok,tgl_input,kode_transaksi)VALUES('$tgl_system','$norek','$unit','$ciff','$code_kel',0,'01','$nominal','$userid','$ket1','','$cao','1','$tanggal','$kode_trans')";
            $inputtrnsk = mysql_query($input);
            if($inputtrnsk){
            $ket="Setoran Angsuran An $nama";
               
                mysql_query("Update  tabungan_detail set saldo =(select(sum(KREDIT)-SUM(DEBIT)) as saldo from simpanan where NOREK='$norek') where no_anggota  ='$norek'") or die(mysql_error("GAGAL INPUT"));
               

                $jurnal_umum=mysql_fetch_array(mysql_query("SELECT max(nomor_jurnal) FROM jurnal_umum where unit ='$unit' ORDER BY tanggal_selesai DESC"));
                $nomor_jurnal=$jurnal_umum[0]+1;
                //pencatatan du jurnal realasisi Piutang Murabahaha total jumlah Pokok dan margin
                mysql_query("insert into jurnal_masuk(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values('$nomor_jurnal','$kode_trans','$tgl_system','$unit')");
                mysql_query("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                values('$unit','$kode_trans','1120000','$tgl_system','Bukti SYSTEM','$ket','$nominal','$userid')");
                
                mysql_query("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                values('$unit','$kode_trans','2101000','$tgl_system','Bukti SYSTEM','Simpanan Pokok','$nominal','$userid')");
                //mysql_query("Update  tunggakan set margin =margin - '$ijaroh', pokok =pokok-'$pokok'where no_anggota  ='$norek'") or die(mysql_error("GAGAL INPUT"));
               echo "<script>alert('Nominal sudah ditambahkan pada rekening $norek');</script>";
                $select1 = mysql_query("SET @bal = 0;");
    $select = mysql_query("SELECT simpanan.NOREK,simpanan.BUSS_DATE,anggota.CUST_SHORT_NAME,DEBIT,KREDIT,@bal:=(@bal+KREDIT)-DEBIT as balance FROM simpanan
    join anggota on anggota.cif = simpanan.cif
    where simpanan.cif='$ciff' order by simpanan.REFF");
                
            }
            else{
            echo "<script>alert('Maaf,.Porses Input Gagal');</script>";
            echo "<script language='javascript'>document.location.href='stor.php'</script>";
            }
     break;


    case "04":
     if ($baki=="Debet"){
                $jurnal_umum=mysql_fetch_array(mysql_query("SELECT max(nomor_jurnal) FROM jurnal_umum where unit ='$unit' ORDER BY tanggal_selesai DESC"));
                $nomor_jurnal=$jurnal_umum[0]+1;
                if($simpan=="pokok"){
                    $gl='3102000';
                      $input ="Insert into simpanan_pokok(BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,kode_transaksi)VALUES('$buss_date','$norek','$unit','$ciff','$code_kel','$nominal','04','0','$userid','$keterangan','','$cao','$kode_trans')";
          $inputtrnsk = mysql_query($input);
          //mysql_query("Update  simpanan set type ='01' where cif  ='$ciff' and type ='04'") or die(mysql_error("GAGAL INPUT"));
           //mysql_query("Update  simpanan set type ='01' where cif  ='$ciff' and type ='02'") or die(mysql_error("GAGAL INPUT"));
                   $input ="Insert into simpanan(BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,kode_transaksi)VALUES('$buss_date','$norek','$unit','$ciff','$code_kel','0','01','$nominal','$userid','$keterangan','','$cao','$kode_trans')";
          $inputtrnsk = mysql_query($input);

                }else{
                    $gl='3202000';
                    $input ="Insert into simpanan_wajib(BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,kode_transaksi)VALUES('$buss_date','$norek','$unit','$ciff','$code_kel','$nominal','05','0','$userid','$keterangan','','$cao','$kode_trans')";
                    $inputtrnsk = mysql_query($input);
                     //mysql_query("Update  simpanan set type ='01' where cif  ='$ciff' and type ='05'") or die(mysql_error("GAGAL INPUT"));
   $input ="Insert into simpanan(BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,kode_transaksi)VALUES('$buss_date','$norek','$unit','$ciff','$code_kel',0,'01','$nominal','$userid','$keterangan','','$cao','$kode_trans')";
          $inputtrnsk = mysql_query($input);

                }
                //pencatatan d jurnal realasisi Piutang Murabahaha total jumlah Pokok dan margin
                mysql_query("insert into jurnal_masuk(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values('$nomor_jurnal','$kode_transaksi','$tgl_system','$unit')");
                mysql_query("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                values('$unit','$kode_trans','$gl','$tgl_system','Bukti SYSTEM','$keterangan','$nominal','$userid')");
                mysql_query("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                values('$unit','$kode_trans','2101000','$tgl_system','Bukti SYSTEM','$keterangan','$nominal','$userid')");
                //mysql_query("Update  tunggakan set margin =margin - '$ijaroh', pokok =pokok-'$pokok'where no_anggota  ='$norek'") or die(mysql_error("GAGAL INPUT"));
                $select1 = mysql_query("SET @bal = 0;");          
        
    }else{
        $jurnal_umum=mysql_fetch_array(mysql_query("SELECT max(nomor_jurnal) FROM jurnal_umum where unit ='$unit' ORDER BY tanggal_selesai DESC"));
                $nomor_jurnal=$jurnal_umum[0]+1;
                if($simpan=="pokok"){
                    $gl='3102000';
                    $input ="Insert into simpanan_pokok(BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,kode_transaksi)VALUES('$buss_date','$norek','$unit','$ciff','$code_kel',0,'04','$nominal','$userid','$keterangan','','$cao','$kode_trans')";
          $inputtrnsk = mysql_query($input);

$input ="Insert into simpanan(BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,kode_transaksi)VALUES('$buss_date','$norek','$unit','$ciff','$code_kel','$nominal','01',0,'$userid','$keterangan','','$cao','$kode_trans')";
          $inputtrnsk = mysql_query($input);
//$input ="Insert into simpanan(BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO)VALUES('$buss_date','$norek','$unit','$ciff','$code_kel',0,'04','$nominal','$userid','$keterangan','','$cao')";
          //$inputtrnsk = mysql_query($input);
        
        
                }else{
                    $gl='3202000';
                    $input ="Insert into simpanan_wajib(BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,kode_transaksi)VALUES('$buss_date','$norek','$unit','$ciff','$code_kel',0,'04','$nominal','$userid','$keterangan','','$cao','$kode_trans')";
          $inputtrnsk = mysql_query($input);
$input ="Insert into simpanan(BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,kode_transaksi)VALUES('$buss_date','$norek','$unit','$ciff','$code_kel','$nominal','01',0,'$userid','$keterangan','','$cao','$kode_trans')";
          $inputtrnsk = mysql_query($input);
//$input ="Insert into simpanan(BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO)VALUES('$buss_date','$norek','$unit','$ciff','$code_kel',0,'04','$nominal','$userid','$keterangan','','$cao')";
          //$inputtrnsk = mysql_query($input);

                }
                //pencatatan d jurnal realasisi Piutang Murabahaha total jumlah Pokok dan margin
                mysql_query("insert into jurnal_masuk(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values('$nomor_jurnal','$kode_transaksi','$tgl_system','$unit')");
                mysql_query("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                values('$unit','$kode_trans','2101000','$tgl_system','Bukti SYSTEM','$ket','$nominal','$userid')");
                mysql_query("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                values('$unit','$kode_trans','$gl','$tgl_system','Bukti SYSTEM','Simpanan Pokok','$nominal','$userid')");
                //mysql_query("Update  tunggakan set margin =margin - '$ijaroh', pokok =pokok-'$pokok'where no_anggota  ='$norek'") or die(mysql_error("GAGAL INPUT"));
                $select1 = mysql_query("SET @bal = 0;");          
      
    }
echo "<script>alert('Pemindah Bukuan an $cif Suksess');</script>";
     $select = mysql_query("SELECT simpanan.NOREK,simpanan.BUSS_DATE,anggota.CUST_SHORT_NAME,DEBIT,KREDIT,@bal:=(@bal+KREDIT)-DEBIT as balance FROM simpanan
    join anggota on anggota.NOREK = simpanan.NOREK
    where simpanan.NOREK='$norek' order by simpanan.REFF");
     break;

     case "05":
        $ket1="Setoran Angsuran WO An $nama";
                      if($ket1){
               $ket="Setoran Angsuran WO An $nama";
               $ket_pb = "PB Angusran WO An $nama";
                  
                   mysql_query("Update  tabungan_detail set saldo =(select(sum(KREDIT)-SUM(DEBIT)) as saldo from simpanan where NOREK='$norek') where no_anggota  ='$norek'") or die(mysql_error("GAGAL INPUT"));
                  
   
                   $jurnal_umum=mysql_fetch_array(mysql_query("SELECT max(nomor_jurnal) FROM jurnal_umum where unit ='$unit' ORDER BY tanggal_selesai DESC"));
                   $nomor_jurnal=$jurnal_umum[0]+1;
                   //pencatatan du jurnal SImpana
                   mysql_query("insert into jurnal_masuk(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values('$nomor_jurnal','$kode_trans','$tgl_system','$unit')");
                   
                   mysql_query("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                   values('$unit','$kode_trans','1120000','$tgl_system','Bukti SYSTEM','$ket','$nominal','$userid')");                 
                   
                   
                   
                   mysql_query("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                   values('$unit','$kode_trans','42356','$tgl_system','Bukti SYSTEM','$ket','$nominal','$userid')");


                   mysql_query("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                   values('$unit','$kode_trans','9910000','$tgl_system','Bukti SYSTEM','$ket','$nominal','$userid')");
                   
                   mysql_query("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                   values('$unit','$kode_trans','9141000','$tgl_system','Bukti SYSTEM','$ket','$nominal','$userid')");



                   $query=mysql_query("update loan_wo set os=os - $nominal  WHERE cif ='$cif'") or die(mysql_error());

                   


                  

                  echo "<script>alert('Nominal sudah ditambahkan pada rekening $norek');</script>";
                   $select1 = mysql_query("SET @bal = 0;");
       $select = mysql_query("SELECT simpanan.NOREK,simpanan.BUSS_DATE,anggota.CUST_SHORT_NAME,DEBIT,KREDIT,@bal:=(@bal+KREDIT)-DEBIT as balance FROM simpanan
       join anggota on anggota.cif = simpanan.cif
       where simpanan.cif='$ciff' order by simpanan.REFF");
                   
               }
               else{
               echo "<script>alert('Maaf,.Porses Input Gagal');</script>";
               echo "<script language='javascript'>document.location.href='stor.php'</script>";
               }
        break;     
      default:
    }
        }
 }
            else{
            echo "<script>alert('Maaf,Nomor Rekening tidak ditemukan');</script>";
            echo "<script language='javascript'>document.location.href='stor.php'</script>";
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
