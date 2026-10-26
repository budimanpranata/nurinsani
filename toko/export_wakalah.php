<?php require_once __DIR__ . '/../php8_compat.php'; 
session_start();
$userid=$_SESSION['userid'];
$tgl_mrb=$_SESSION['smrb'];

if(!isset($_SESSION['userid'])){
   header("location:index.html");
}
if($_SESSION['level']!="unit"){
   header("location:index.html");
}
header("Content-type: application/vnd-ms-excel");
header("Content-Disposition: attachment; filename=rekap_wakalh.xls");
	
	//untuk waktu
	$tanggal=date("y-m-d");
	
	//untuk koneksi database
	include "conn2.php";
	 include "indo_tgl.php";
$koneksi2=open_connection2();
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
  <td height="30" colspan="12" nowrap><div align="left" class="style9 style3 style1 style1"><strong>Rekap Wakalah </strong></div></td>
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
<th>CIF</th>
			 	 	<th>Nama Anggota</th>
					<th>Kelompok</th>
					<th>Nama Suami</th>
					<th>Alamat</th>
					<th>Jenis Usaha</th>
					<th>Pengajuan AO</th>
					<th>Tanggal PK</th>
					<th>TENOR</th>
					<th>Sebelumnya</th>
					<th>Disetujui</th>
					<th>Pinjaman Ke</th>						
					</tr>
			
			
					
			
			<?php
			$no=0;
$tot_blt=0;
  $sql=mysql_query("select nama_kel,calon_loan.unit,calon_loan.cif as CIF,calon_loan.suffix,calon_loan.cust_Short_name,HADDRESS1,(select nama_usaha from bidang_usaha where code_bidang=calon_loan.usaha) as nama_usaha,WARIS,calon_loan.Plafond as rek,nama_kel,loan.Plafond as sebelum,calon_loan.tgl_murab,calon_loan.tenor,calon_loan.code_kel
from calon_loan
inner join anggota on anggota.cif = calon_loan.cif
inner join kelompok on kelompok.code_kel = calon_loan.code_kel
left join loan on loan.cif=calon_loan.cif
where calon_loan.USERID='$userid' AND calon_loan.tgl_murab='$tgl_mrb' and calon_loan.status_app ='PENDING' and calon_loan.status_usia <> 'YA' order by nama_kel ");

$sql2=mysql_query("select sum(calon_loan.Plafond) as total, count(calon_loan.cif) as noa,nama_kel,nama_ao,calon_loan.code_kel from calon_loan
inner join kelompok on kelompok.code_kel = calon_loan.code_kel
inner join ao on ao.cao=calon_loan.cao
where calon_loan.USERID='$userid' AND calon_loan.tgl_murab='$tgl_mrb' and status_app ='PENDING' and calon_loan.status_usia <> 'YA' group by nama_kel order by nama_kel ");

$sql3=mysql_query("select sum(calon_loan.Plafond) as total_pla, count(cif) as total_noa from calon_loan
where calon_loan.USERID='$userid' AND calon_loan.tgl_murab='$tgl_mrb' and status_app ='PENDING' and calon_loan.status_usia <> 'YA'");
while($result= mysql_fetch_array($sql)){
	$no ++;
 $TGL_KUNJUNGAN = $result['nama_ao'];
$cif = $result['CIF'];
$cao = $result['cao'];

$cust_Short_name= $result['cust_Short_name'];
$Plafond= $result['rek'];  
$angsuran = number_format (compat_num($result['angsuran']));
$bulat = number_format (compat_num($result['bulat']));
$unit = $result['unit'];
$ke = $result['ke'];
$suffix = $result['suffix'];
$bulat = number_format (compat_num($result['bulat']));
$saldo = $result['sebelum'];
$run_tenor = $result['run_tenor'];
$os =number_format (compat_num($result['os']));
$norek="$unit-$cao-$cif";
$bagi_hasil = number_format(compat_num($result['bagi_hasil']));
$tot_blt +=($result['tot_bulat']);
$tot_pla=$result['tot_pla'];
$alamat=$result['HADDRESS1'];
$waris=$result['WARIS'];
$usaha=$result['nama_usaha'];
$tenor=$result['tenor'];
$nama_kel=$result['nama_kel'];
$tgl_murab=tgl_indo ($result['tgl_murab']);

			
				?>
				<tr>
					<td><?php echo $no;?></td>
<td><?php echo $cif;?></td>
					<td><?php echo $cust_Short_name;?></td>
					
					<td><?php echo $nama_kel; ?></td>
					<td><?php echo $waris; ?></td>					
					<td><?php echo $alamat; ?></td>
					<td><?php echo $usaha; ?></td>					
					<td><?php echo number_format(compat_num($Plafond),2,",","."); ?></td>
					<td><?php echo $tgl_murab; ?></td>		
					<td><?php echo $tenor; ?></td>
					<td><?php echo number_format(compat_num($saldo),2,",","."); ?></td>	
					<td><?php echo number_format(compat_num($Plafond),2,",","."); ?></td>
					<td><?php echo $suffix; ?></td>					
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
