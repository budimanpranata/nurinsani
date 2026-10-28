<?php require_once __DIR__ . '/../koneksi_db.php';
session_start();
ob_start();
$userid=$_SESSION['userid'];
//cek apakah user sudah login
if(!isset($_SESSION['userid'])){
   header("location:index.html");
 exit; }

?>
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
<title>Database Pensiun</title>
<link rel="stylesheet" href="../tabel.css" />
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<style type="text/css">
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
</style>
</head>

<table width="100%" cellpadding="1" bordercolor="#CCCCCC" bgcolor="#669900">
  <tr>
    <td height="20" bordercolor="#FFFFFF" bgcolor="#669900" class="style3 style8">Report Mobcol
    <div align="right"></div></td>
  </tr>
</table>

<table width="428" border="1" align="center">
</table>
  <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" name="postform">  <table width="502" border="0" align="center">
<tr bgcolor="#669900">
  <td height="10" colspan="4" nowrap><div align="center" class="style3 style9">Kriteria Pencarian </div></td>
  </tr>
<tr>
  <td height="10" nowrap bgcolor="#B3B3FF"><span class="style7">Tanggal Pencarian </span></td>
  <td nowrap><div align="center"><strong>:</strong></div></td>
  <td colspan="2" nowrap><input name="tgl" type="text" id="tgl" value="" size="20"/>
    <a href="javascript:void(0)" onClick="if(self.gfPop)gfPop.fPopCalendar(document.postform.tgl);return true;" ><img src="../images/calendarIcon.jpg" alt="" name="popcal" width="17" height="17" border="0" align="absmiddle" id="popcal" /></a></td>
</tr>
<tr>
  <td height="10" nowrap bgcolor="#B3B3FF"><span class="style7">Jenis Report </span></td>
  <td nowrap><div align="center"><strong>:</strong></div></td>
  <td colspan="2" nowrap><select name="select" size="1">
    <option>Pilih Salah satu</option>
    <option value="01">CS </option>
    <option value="02">Non Cs Lima Persen </option>
    <option value="03">Non Cs Lebaran</option>
    <option value="04">Non Cs Penarikan</option>
    <option value="05">Non Cs Tunggakan</option>
    <option value="06">Non Cs Pelunasan</option>
<option value="07">Non Cs WO</option>
<option value="08">Omzet</option>

  </select></td>
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

<table width="100%" class="table1">
  <tr class="header1">
    <th width="48">Unit</th>
    <th width="48">Jenis Report</th>
    <th width="47">NoA</th>
    <th width="100">Tagihan</th>
    <th width="100">Tertagih</th>
    <th width="7">Excel</th>

  </tr>
  <?php
  db_mobcol();
  mysql_select_db('mobcol');
  include "../function.php";

  $tgl=$_POST['tgl'];
  $select=$_POST['select'];
  $buss_date=date("Ymd");
               
  If (isset($_POST ['search']))
{
if (empty($tgl)||empty($select)){
    echo "<script>alert('Maaf,.isi Tanggal  !!!');</script>";
         echo "<script language='javascript'>document.location.href='ppap.php'</script>";}
   
    else{



        $_SESSION['stgl_npl'] = $tgl;
        $_SESSION['jenis_npl'] = $select;

    switch($select)
    {
     case "01":
      $jenis ='CS';
    //$cek_saldo= mysql_fetch_array(mysql_query("SELECT sum(saldo_kredit) - sum(debet) as saldo from rek_loan where unit ='$unit' "));
    $data_noa= mysql_fetch_array(mysql_query("SELECT count(cif) as noa, sum(bulat) as tagihan,sum(bayar) as tertagih from eod_tagihan_cs where unit ='$userid' and tgl_tagih ='$tgl' 
    "));
    $noa=$data_noa['noa'];
    $tagihan=$data_noa['tagihan'];
    $tertagih=$data_noa['tertagih'];
    break;
     
    case "02":
   $jenis='Non Cs Lima Persen';
    $data_noa= mysql_fetch_array(mysql_query("SELECT count(cif) as noa, sum(bulat) as tagihan,sum(bayar) as tertagih from eod_tagihan_lima_persen where unit ='$userid' and tgl_tagih ='$tgl'"));
    $noa=$data_noa['noa'];
    $tagihan=$data_noa['tagihan'];
    $tertagih=$data_noa['tertagih'];
    break;
   
    case "03":
      $jenis='Non Cs Lebaran';
    //$cek_saldo= mysql_fetch_array(mysql_query("SELECT sum(nominal) as saldo, datediff('$tgl', tgl_tunggak) as selisih from tunggakan where datediff('$tgl', tgl_tunggak) between 121 AND 365 AND unit  ='$unit' group by cif "));
    $data_noa= mysql_fetch_array(mysql_query("SELECT count(cif) as noa, sum(bulat) as tagihan,sum(bayar) as tertagih from eod_tagihan_lebaran where unit ='$userid' and tgl_tagih ='$tgl'"));
    $noa=$data_noa['noa'];
    $tagihan=$data_noa['tagihan'];
    $tertagih=$data_noa['tertagih'];
    break;
   
    case "04":
      $jenis='Non Cs Penarikan';
    //$cek_saldo= mysql_fetch_array(mysql_query("SELECT sum(nominal) as saldo, datediff('$tgl', tgl_tunggak) as selisih from tunggakan where datediff('$tgl', tgl_tunggak) >365 AND unit  ='$unit' group by cif "));
    $data_noa= mysql_fetch_array(mysql_query("SELECT count(cif) as noa, sum(bulat) as tagihan,sum(bayar) as tertagih from eod_tagihan_penarikan where unit ='$userid' and tgl_tagih ='$tgl'"));
    $noa=$data_noa['noa'];
    $tagihan=$data_noa['tagihan'];
    $tertagih=$data_noa['tertagih'];
     break;

     case "05":
      $jenis='Non Cs Tunggakan';
      //$cek_saldo= mysql_fetch_array(mysql_query("SELECT sum(nominal) as saldo, datediff('$tgl', tgl_tunggak) as selisih from tunggakan where datediff('$tgl', tgl_tunggak) >365 AND unit  ='$unit' group by cif "));
      $data_noa= mysql_fetch_array(mysql_query("SELECT count(cif) as noa, sum(bulat) as tagihan,sum(bayar) as tertagih from eod_tagihan_tunggakan where unit ='$userid' and tgl_tagih ='$tgl'"));
      $noa=$data_noa['noa'];
      $tagihan=$data_noa['tagihan'];
      $tertagih=$data_noa['tertagih'];
       break;
    case "06":
      $jenis='Non Cs Pelunasan';
        //$cek_saldo= mysql_fetch_array(mysql_query("SELECT sum(nominal) as saldo, datediff('$tgl', tgl_tunggak) as selisih from tunggakan where datediff('$tgl', tgl_tunggak) >365 AND unit  ='$unit' group by cif "));
        $data_noa= mysql_fetch_array(mysql_query("SELECT count(cif) as noa, sum(bulat) as tagihan,sum(bayar) as tertagih from eod_tagihan_pelunasan where unit ='$userid' and tgl_tagih ='$tgl'"));
        $noa=$data_noa['noa'];
        $tagihan=$data_noa['tagihan'];
        $tertagih=$data_noa['tertagih'];
         break;
 case "07":
      $jenis='Non Cs Wo';
        //$cek_saldo= mysql_fetch_array(mysql_query("SELECT sum(nominal) as saldo, datediff('$tgl', tgl_tunggak) as selisih from tunggakan where datediff('$tgl', tgl_tunggak) >365 AND unit  ='$unit' group by cif "));
        $data_noa= mysql_fetch_array(mysql_query("SELECT count(cif) as noa, sum(os) as os,sum(nominal) as tertagih from eod_wo left join ao on ao.cao = eod_wo.cao where kode_unit ='$userid' and tgl_tagih ='$tgl'"));
        $noa=$data_noa['noa'];
        $tagihan=$data_noa['os'];
        $tertagih=$data_noa['tertagih'];
         break;
 case "08":
      $jenis='Non Cs Wo';
        //$cek_saldo= mysql_fetch_array(mysql_query("SELECT sum(nominal) as saldo, datediff('$tgl', tgl_tunggak) as selisih from tunggakan where datediff('$tgl', tgl_tunggak) >365 AND unit  ='$unit' group by cif "));
        $data_noa= mysql_fetch_array(mysql_query("SELECT count(cif) as noa, '0' as os, sum(omzet) as tertagih from eod_omzet left join ao on ao.cao = eod_omzet.cao where kode_unit ='$userid' and tgl_tagih ='$tgl'"));
        $noa=$data_noa['noa'];
        $tagihan=$data_noa['os'];
        $tertagih=$data_noa['tertagih'];
         break;
      default:
    }
        }

   
  ?>
 
  <?php
   
    //menampilkan data


//menghitung hitung
//$birthdate = $data['Birth_date'];
//$age = ((time() - strtotime($birthdate))/(60*60*24*365.2425));

        ?>
  <tr>
    <td class="style6"><?php echo $userid; ?></td>
    <td class="style6"><?php echo $jenis; ?></td>
    <td class="style6"><?php echo $noa;  ?></td>
    <td class="style6"><?php echo number_format (compat_num($tagihan));?></td>
    <td class="style6"><?php echo number_format (compat_num($tertagih)); ?></td>
    <td><div align="center"><a href="export_mobcol.php?unit=<?php echo $userid;?>&tgl=<?php echo $tgl;?>&jenis=<?php echo $select;?>" class="style6" onClick="return confirm('Apakah Anda yakin ?')"><strong>Download</strong></a></div></td>
 
   
  </tr>
  <?php
       
   
    }
    ?>
 
</table>
<p>&nbsp;</p>

 <iframe width=174 height=189 name="gToday:normal:calender/normal.js" id="gToday:normal:calender/normal.js" src="calender/ipopeng.htm" scrolling="no" frameborder="0" style="visibility:visible; z-index:999; position:absolute; top:-500px; left:-500px;">
</iframe>
</body>
</html>
