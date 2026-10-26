<?php require_once __DIR__ . '/php8_compat.php'; 
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
header("Content-Disposition: attachment; filename=rekap_booking.xls");
	

	//untuk koneksi database
	include "conn2.php";
	 include "indo_tgl.php";
$koneksi2=open_connection2();
	
?>
<table width="100%">
  <tr bgcolor="#669900">
  <td height="30" colspan="12" nowrap><div align="left" class="style9 style3 style1 style1"><strong>Rekap Booking Debet </strong></div></td>
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
			 	 	<th>Nama Anggota</th>
					<th>Kelompok</th>
					<th>Cif</th>
					<th>tgl_book</th>
					<th>Status</th>
					</tr>
			
			
					
			
			<?php
			$no=0;

$sql=mysql_query("select unit,kelompok.nama_kel,loan.cao,tgl_booking, status_booking,loan.cif,Cust_Short_name as nama from loan 
	inner join kelompok on kelompok.code_kel = loan.code_kel
	INNER JOIN booking_debet ON booking_debet.cif = loan.cif
	where  loan.unit='$userid' and os > 0 and unit not in (select unit from psbb)
    order by loan.code_kel, tgl_booking desc
	");
while($result= mysql_fetch_array($sql)){
	$no ++;
$cust_Short_name= $result['nama'];
$nama_kel= $result['nama_kel'];  
$cif = $result['cif'];
$tgl_book = $result['tgl_booking'];
$status = $result['status_booking'];


			
				?>
				<tr>
					<td><?php echo $no;?></td>

					<td><?php echo $cust_Short_name;?></td>					
					<td><?php echo $nama_kel; ?></td>
					<td><?php echo $cif; ?></td>					
					<td><?php echo $tgl_book; ?></td>
					<td><?php echo $status; ?></td>					
					
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
