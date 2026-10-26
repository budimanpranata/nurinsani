<?php require_once __DIR__ . '/../../php8_compat.php';
session_start();
ob_start();
$userid=$_SESSION['userid'];
//cek apakah user sudah login
if(!isset($_SESSION['userid'])){
   header("location:index.html");
}

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
    <td height="20" bordercolor="#FFFFFF" bgcolor="#669900" class="style3 style8">Report PPAP
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
  <td height="10" nowrap bgcolor="#B3B3FF"><span class="style7">Jenis Kolektabilitas </span></td>
  <td nowrap><div align="center"><strong>:</strong></div></td>
  <td colspan="2" nowrap><select name="select" size="1">
    <option>Pilih Salah satu</option>
    <option value="01">Lancar </option>
    <option value="02">Kurang Lancar </option>
    <option value="03">Diragukan</option>
    <option value="04">Macet</option>
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
    <th width="47">NoA</th>
    <th width="100">Plafond</th>
    <th width="100">Saldo Pinjaman</th>
    <th width="70">Rekap</th>
    <th width="7">Excel</th>

  </tr>
  <?php
  include "../conn2.php";
  include "../function.php";
  $koneksi=open_connection2();
  $tgl=$_POST['tgl'];
  $select=$_POST['select'];
  $buss_date=date("Ymd");
    $userrr=mysql_query("select kode_branch from user where userid='$userid'");
           
            while($id_user=mysql_fetch_array($userrr)){
           
                $unit=$id_user['kode_branch'];
            }
           
  If (isset($_POST ['search']))
{
if (empty($tgl)||empty($select)){
    echo "<script>alert('Maaf,.isi Tanggal  !!!');</script>";
         echo "<script language='javascript'>document.location.href='ppap.php'</script>";}
   
    else{

mysql_query("update loan
set gol=(SELECT
CASE
WHEN sum(KREDIT-DEBIT)/angsuran between 1 and 3 then '1'
WHEN sum(KREDIT-DEBIT)/angsuran between 4 and 6 then '2'
WHEN sum(KREDIT-DEBIT)/angsuran between 7 and 12 then '3'
WHEN sum(KREDIT-DEBIT)/angsuran > 12 then '4'
else '1' end as gol
FROM
tunggakan
where loan.cif=tunggakan.CIF
) where loan.unit ='$unit'");
       
     
$cek_matur=mysql_query("Select loan.cif,angsuran,ROUND(sum(KREDIT-DEBIT)/angsuran) as ft, Cust_Short_name,datediff('$tgl',maturity_date) as lama_jatpo from loan
inner JOIN tunggakan
on loan.cif=tunggakan.CIF
WHERE loan.unit ='$unit' and maturity_date < '$tgl' and os >0
GROUP BY loan.CIF ");
while($data_matur=mysql_fetch_array($cek_matur)){
$cif=$data_matur['cif'];
$a=$data_matur['ft'];
$b=$data_matur['lama_jatpo'];
//cek lama Jatpo
$c=30;

if ($a<=6){
$gol='2';   

    if($b>60){
    $gol='4';
    }else{
        $gol='2';
}
}
elseif($a<=12){
$gol='3';

if($b>60){
    $gol='4';
}else{
        $gol='3';
}
}
elseif($a>12){
$gol='4';

if($b>60){
    $gol='4';
}else{
        $gol='4';
}   
}else {
    $gol='10';
}

if ($a<4){
    $gol='10';
}
   
    mysql_query("update loan set gol='$gol' where cif ='$cif'");
            }
           
mysql_query("update loan
set gol=(SELECT
CASE
WHEN sum(KREDIT-DEBIT)/angsuran > 0 and datediff('$tgl',maturity_date) between 1 and 30 then '2'
WHEN sum(KREDIT-DEBIT)/angsuran > 0 and datediff('$tgl',maturity_date) between 31 and 60 then '3'
WHEN sum(KREDIT-DEBIT)/angsuran > 0 and datediff('$tgl',maturity_date) > 60 then '4'
else '4' end as gol
FROM
tunggakan
where loan.cif=tunggakan.CIF
) where loan.unit ='$unit' and maturity_date < '$tgl' and gol='10' ");  
//mysql_query("update loan
//set gol=(SELECT
//CASE
//WHEN sum(KREDIT-DEBIT)/angsuran > 0 and datediff('$tgl',last_payment) between 120 and 180 then '2'
//WHEN sum(KREDIT-DEBIT)/angsuran between 4 and 6 then '2'
//WHEN sum(KREDIT-DEBIT)/angsuran > 0 and datediff('$tgl',last_payment) between 181 and 360 then '3'
//WHEN sum(KREDIT-DEBIT)/angsuran between 7 and 11 then '3'
//WHEN sum(KREDIT-DEBIT)/angsuran > 0 and datediff('$tgl',last_payment) > 360 then '4'
//WHEN sum(KREDIT-DEBIT)/angsuran > 0 and datediff('$tgl',maturity_date) > 60 then '4'
//WHEN sum(KREDIT-DEBIT)/angsuran > 11 then '4'

//else '2' end as gol
//FROM
//tunggakan
//where loan.cif=tunggakan.CIF
//) where loan.unit ='$unit' and maturity_date < '$tgl' ");



        $_SESSION['stgl_npl'] = $tgl;
        $_SESSION['jenis_npl'] = $select;

    switch($select)
    {
     case "01":
    //$cek_saldo= mysql_fetch_array(mysql_query("SELECT sum(saldo_kredit) - sum(debet) as saldo from rek_loan where unit ='$unit' "));
    $data_noa= mysql_fetch_array(mysql_query("SELECT count(cif) as noa, sum(Plafond) as pla,sum(os-saldo_margin) as saldo from loan where loan.unit ='$unit' and OS >0 and gol='1'"));
    $noa=$data_noa['noa'];
    $saldo=$data_noa['saldo'];
    $pla=$data_noa['pla'];
    break;
     
    case "02":
   
    $data_noa= mysql_fetch_array(mysql_query("SELECT count(cif) as noa, sum(Plafond) as pla,sum(os-saldo_margin) as saldo from loan where loan.unit ='$unit' and OS >0 and gol='2'"));
    $noa=$data_noa['noa'];
    $saldo=$data_noa['saldo'];
    $pla=$data_noa['pla'];
    break;
   
    case "03":
    //$cek_saldo= mysql_fetch_array(mysql_query("SELECT sum(nominal) as saldo, datediff('$tgl', tgl_tunggak) as selisih from tunggakan where datediff('$tgl', tgl_tunggak) between 121 AND 365 AND unit  ='$unit' group by cif "));
    $data_noa= mysql_fetch_array(mysql_query("SELECT count(cif) as noa, sum(Plafond) as pla,sum(os-saldo_margin) as saldo  from loan where loan.unit ='$unit' and OS >0 and gol='3'"));
    $noa=$data_noa['noa'];
    $saldo=$data_noa['saldo'];
    $pla=$data_noa['pla'];
    break;
   
    case "04":
    //$cek_saldo= mysql_fetch_array(mysql_query("SELECT sum(nominal) as saldo, datediff('$tgl', tgl_tunggak) as selisih from tunggakan where datediff('$tgl', tgl_tunggak) >365 AND unit  ='$unit' group by cif "));
    $data_noa= mysql_fetch_array(mysql_query("SELECT count(cif) as noa, sum(Plafond) as pla,sum(os-saldo_margin) as saldo from loan where loan.unit ='$unit' and OS > 0 and gol='4'"));
    $noa=$data_noa['noa'];
    $saldo=$data_noa['saldo'];
    $pla=$data_noa['pla'];
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
    <td class="style6"><?php echo $unit; ?></td>
    <td class="style6"><?php echo $noa;  ?></td>
    <td class="style6"><?php echo number_format (compat_num($pla));?></td>
    <td class="style6"><?php echo number_format (compat_num($saldo)); ?></td>
    <td><div align="center"><a href="rekap_ppap.php?nopen=<?php echo $unit;?>" class="style6" onClick="return confirm('Apakah Anda yakin ?')"><strong>Rekap</strong></a></div></td>
    <td><div align="center"><a href="export_npl.php?nopen=<?php echo $unit;?>" class="style6" onClick="return confirm('Apakah Anda yakin ?')"><strong>Download</strong></a></div></td>
 
   
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
