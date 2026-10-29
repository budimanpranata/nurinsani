<?php require_once __DIR__ . '/../php8_compat.php';
session_start();  
ob_start();
$iduser=$_SESSION['userid'];
if(!isset($_SESSION['userid'])){
   header("location:../index.html");
 exit; }

//cek level user
if($_SESSION['level']!="lead"){
   header("location:../index.html");
 exit; }


?>
<style type="text/css">
<!--
.header1 {color: #ffffff; background: #2F6B4F; font-weight: bold; font-family: Tahoma, Verdana; font-size: 13px; margin: 0px;}
.style1 {color: #CCCCCC}
.style5 {color: #FF0000}
.style9 {color: #FF0000; font-size: 14px; }
.style13 {font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 14px; }
.style14 {	color: #FFFFFF;
	font-weight: bold;
}
-->
</style>
<title>List approve</title>
  <table width="100%" border="0" cellpadding="3" cellspacing="0" bgcolor="#2F6B4F">
    <tr>
      <td width="100%" bgcolor="#2F6B4F"><span class="style14">Approve Dokumen </span></td>
    </tr>
  </table>
  </p>
  <?php
include "../conn2.php";
include "../indo_tgl.php";
$koneksi=open_connection2();

$area = mysql_query ("select kode_branch FROM user where userid ='$iduser'");
$c_area = mysql_fetch_array($area);
$code_area=$c_area['kode_branch'];

$tgl_trn = $_POST ['tgl_trn'];
$nopen= $_POST ['nopen'];
	if($_GET['a'] == 'process' && $_POST['approve']){
		$idlist = $_POST['approve'];
	if($_POST['approve_sub']){
		$action = 'approve';
			}elseif($_POST['reject']){
				$action = 'reject';
	}

?>
  <form action="<?php echo $_SERVER['PHP_SELF'] ?>?a=<?php echo $action; ?>" method="post">
<table style="font-size:11px;font-family:'arial';text-align:center;" border='1' width="80%" align='center' >
    <tr class="header1">
    <th>No Transaksi </th>
    <th class="header1">Unit </th>
    <th class="header1">Cif</th>
<th class="header1">Nama</th>
    <th class="header1">Tanggal Transaksi</th>
    <th class="header1">No Anggota</th>
	<th class="header1"><span class="header1">Nominal</span></th>
	<th class="header1"><span class="header1">Usia</span></th>
  </tr>


  <?php

foreach( $idlist as $key => $value){
	$data = mysql_query("SELECT calon_loan.unit as unit,cif,buss_date,no_anggota,plafond,Cust_Short_name as nama FROM calon_loan inner join branch on branch.kode_branch = calon_loan.unit WHERE  status_usia ='YA' and branch.code_area ='$code_area' AND calon_loan.cif='" . $value . "'") or die(mysql_error());
	if(mysql_num_rows($data) == 0){
		$data =mysql_query("SELECT temp_tarik.unit as unit ,temp_tarik.cif,temp_tarik.buss_date,temp_tarik.norek as no_anggota, sum(debit) as plafond, type as Usia,CUST_SHORT_NAME as nama FROM temp_tarik inner join anggota on anggota.cif=temp_tarik.cif inner join branch on branch.kode_branch = temp_tarik.unit WHERE branch.code_area ='$code_area' and temp_tarik.cif ='" . $value . "' group by temp_tarik.norek") or die(mysql_error());
	}
while($row = mysql_fetch_array($data)){
echo " <tr>";
echo " <td align=center><input type=\"hidden\" value=\"" . $row['cif'] . "\" name=\"approve[]\" />" . $row['cif'] . "</td>";
echo " <td align=center>" . $row['unit'] . "</td>";
echo " <td align=center>" . $row['cif'] . "</td>";
echo " <td align=left>" . $row['nama'] . "</td>";
echo " <td align=center>" . tgl_indo($row['buss_date']) . "</td>";
echo " <td align=center>" . $row['no_anggota'] . "</td>";
echo " <td align=center>" . number_format (compat_num($row['plafond'])) . "</td>";
echo " <td align=center>" . $row['Usia'] . "</td>";
echo " </tr>";
}
}
?>

  <tr class="style1">
    <td colspan='8' align=center bgcolor="#2F6B4F"><input type="submit" value="Approve" name="confirm" />
    <input type="submit" value="Cancel" name="cancel" /></td>
  </tr>
</table>
<center>
</center>
<?php
}elseif($_GET['a'] == 'approve' && $_POST['approve']){
$idlist = $_POST['approve'];
	if($_POST['cancel']){
	$cancel = 'true';
}elseif($_POST['confirm']){
foreach( $idlist as $key => $value){

$data_tgl = mysql_query("SELECT unit,cif,buss_date,norek as no_anggota FROM temp_tarik WHERE cif='" . $value . "' ") or die(mysql_error());
	
while($tgl_transaksi = mysql_fetch_array($data_tgl)){
	$tanggal_transaksi=$tgl_transaksi['buss_date'];
}
// ^^ COPY THE DATA ^^
//CHANGE 'approved' INTO THE NAME OF THE TABLE CONTAINING THE APPROVED APPLICATIONS
//ADD ANY OTHER FIELDS THAT ARE NECESSARY TO THE LISTS
$hasil=mysql_query("Update calon_loan SET status_usia ='DEVIASI' WHERE cif='" . $value . "'") or die(mysql_error());
if(mysql_num_rows($hasil) == 0){
mysql_query("insert into simpanan select * from temp_tarik WHERE cif='" . $value . "'") or die(mysql_error());	
mysql_query("delete from temp_tarik WHERE cif='" . $value . "'") or die(mysql_error());
mysql_query("update tabel_transaksi set keterangan_posting=' ' WHERE keterangan_posting='" . $value . "' and tanggal_transaksi ='$tanggal_transaksi'") or die(mysql_error());

}


// ^^ DELETE THE OLD DATA ^^
}
echo "Aplikasi Sudah di Setujui.";
}

}elseif($_GET['a'] == 'reject' && $_POST['approve']){

$idlist = $_POST['approve'];

if($_POST['cancel']){
$cancel = 'true';
}elseif($_POST['confirm']){
foreach( $idlist as $key => $value){
$suskes=mysql_query("DELETE FROM calon_loan WHERE cif='" . $value . "'") or die(mysql_error());
if(mysql_num_rows($result) == 0){
mysql_query("delete from temp_tarik WHERE norek='" . $value . "'") or die(mysql_error());
}	
// ^^ DELETE THE REJECTED APPLICATION DATA ^^
}
echo "Applications successfully rejected.";
}

}
if(!$_GET['a'] || $cancel || !$_POST['approve']){
?>
<form action="<?php echo $_SERVER['PHP_SELF'] ?>?a=process" method="post"><center>
  <table style="font-size:11px;font-family:'arial';text-align:center;" border='1' width="80%" align='center' >
    <tr class="header1">
    <th class="header1"><span class="header1">Approve / Reject </span></th>
    <th class="header1">Kode Cabang </th>
    <th class="header1"><span class="header1">Cif</span></th>
<th class="header1"><span class="header1">Nama</span></th>
  	<th class="header1"><span class="header1">Tanggal Transaksi</span></th>
  	<th class="header1"><span class="header1">Norek</span></th>
	<th class="header1"><span class="header1">Nominal</span></th>
	<th class="header1"><span class="header1">Status</span></th>
  </tr>

<?php

$result = mysql_query("SELECT calon_loan.unit as unit ,cif,buss_date,no_anggota,plafond,Cust_Short_name as nama FROM calon_loan inner join branch on branch.kode_branch = calon_loan.unit WHERE tgl_wakalah='$tgl_trn' AND status_usia='YA' and code_area ='$code_area' OR cif='$nopen' AND status_usia='YA' and code_area='$code_area'") or die(mysql_error());
if(mysql_num_rows($result) == 0){
$result = mysql_query("SELECT temp_tarik.unit,temp_tarik.cif,temp_tarik.buss_date,temp_tarik.norek as no_anggota, sum(debit) as plafond, type as Usia,CUST_SHORT_NAME as nama FROM temp_tarik left join anggota on anggota.cif=temp_tarik.cif WHERE temp_tarik.buss_date='$tgl_trn' and blok='$code_area' OR temp_tarik.cif='$nopen' and blok='$code_area' group by temp_tarik.cif") or die(mysql_error());
	}


while($row = mysql_fetch_array($result)){
echo " <tr>";
echo " <td align=center><input type=\"checkbox\" value=\"" . $row['cif'] . "\" name=\"approve[]\" /></td>";
echo " <td align=center>" . $row['unit'] . "</td>";
echo " <td align=center>" . $row['cif'] . "</td>";
echo " <td align=left>" . $row['nama'] . "</td>";
echo " <td align=center>" . tgl_indo($row['buss_date']) . "</td>";
echo " <td align=center>" . $row['no_anggota'] . "</td>";
echo " <td align=center>" . number_format(compat_num($row['plafond'])) . "</td>";
echo " <td align=center>" . $row['Usia'] . "</td>";

echo " </tr>";
}


?>

<tr>
<td colspan='8' align=center bgcolor="#FFFFCC" class="header1"><input type="submit" value="Lanjut" name="approve_sub" /> 
<input type="submit" value="Reject" name="reject" /></td>
</tr>
</table>
</center>
</form>
<?php
}
?>
