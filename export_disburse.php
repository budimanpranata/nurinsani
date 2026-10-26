<?php require_once __DIR__ . '/php8_compat.php'; 
session_start();
$userid=$_SESSION['userid'];
$buss_date1=$_SESSION['sbuss1'];
$buss_date2=$_SESSION['sbuss2'];
if(!isset($_SESSION['userid'])){
   header("location:index.html");
}
if($_SESSION['level']!="unit"){
   header("location:index.html");
}
header("Content-type: application/vnd-ms-excel");
header("Content-Disposition: attachment; filename=NERACA.xls");
	
	//untuk waktu
	$tanggal=date("y-m-d");
	
	//untuk koneksi database
	include "conn2.php";
$koneksi=open_connection2();
	 include "indo_tgl.php";
	$code_kel=$_GET['code_kel'];
$sql4=mysql_query("Select nama_kel,code_kel FROM kelompok WHERE code_kel='$code_kel'");
$kode= mysql_fetch_array($sql4);
$nkel=$kode['nama_kel'];

$sql1=mysql_query("Select nama,kode_branch FROM user WHERE userid='$userid'");
$dsmo= mysql_fetch_array($sql1);
$nama_sales=$dsmo['nama'];
$kode_branch=$dsmo['kode_branch'];

$sql2=mysql_query("Select person.nama, branch.unit,branch.alamat FROM branch inner join person on person.unit = branch.kode_branch WHERE kode_branch='$kode_branch'");
$br= mysql_fetch_array($sql2);
$nama_unit=$br['unit'];
$alamat=$br['alamat'];
$nama_um=$br['nama'];

$sqlm=mysql_query("Select tgl_murab FROM calon_loan WHERE unit='$code_kel'");
$mur= db_fetch_array($sqlm);
$tgl_mur=$mur['tgl_murab'];
$tgl_mur=tgl_indo($tgl_mur);
$buss_date=date("y-m-d");

	
?>
<table width="100%">
  <tr bgcolor="#669900">
  <td height="30" colspan="12" nowrap><div align="left" class="style9 style3 style1 style1"><strong>Rekap Approval </strong></div></td>
  </tr>
</table>
<p>
	<div class="post">
	<div class="entry">
			<?php
					?>
					
			<div class="container">
				<table>
			<tr> 
				
					<th>No</th>
					<th>Unit</th>
					<th>Tanggal Murabahah</th>
					<th>Norek</th>
					<th>Nama Anggota</th>
					<th>Kelompok</th>
					<th>CIF</th>
					<th>Flafond</th>
					<th>Tenor</th>
					<th>Nama AO</th>
					
					
					</tr>
			
			
					
			
			<?php
			$no=0;

$sql=mysql_query("select Contract_Date,Plafond, tenor,loan_detail.cif,Cust_Short_name,nama_ao,no_anggota,loan_detail.cao,angsuran,suffic,loan_detail.unit,nama_kel from loan_detail
inner join kelompok on kelompok.code_kel = loan_detail.code_kel
inner join ao on ao.cao = loan_detail.cao 
where Contract_Date BETWEEN '$buss_date1' AND '$buss_date2' and unit='$userid' ");
while($result= mysql_fetch_array($sql)){
	$no ++;

$tgl_murab=tgl_indo ($result['Contract_Date']);
$unit=$result['unit'];
$Plafond=$result['Plafond'];
$tenor=$result['tenor'];
$no_anggota=$result['no_anggota'];
$suffic=$result['suffic'];
$nama_kel=$result['nama_kel'];
$cif=$result['cif'];
$cao=$result['cao'];
$nama_ao=$result['nama_ao'];


$Cust_Short_name=$result['Cust_Short_name'];




			
				?>
				<tr>
					<td><?php echo $no;?></td>
					<td><?php echo sprintf ($unit);?></td>
					<td><?php echo $tgl_murab;?></td>
					<td><?php echo sprintf($no_anggota);?></td>
					<td><?php echo $Cust_Short_name;?></td>
					<td><?php echo $nama_kel; ?></td>
					<td><?php echo $cif; ?></td>					
					<td><?php echo number_format(compat_num($Plafond),0,",","."); ?></td>
					<td><?php echo $tenor; ?></td>					
					<td><?php echo $nama_ao; ?></td>		
									
				</tr>
				<?php
			}
			?>
			</thead>
			
			</table>
			</div>
			</p>
	</div>
	</div>
