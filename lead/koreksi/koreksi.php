<?php require_once __DIR__ . '/../../php8_compat.php';
session_start();
$userid=$_SESSION['userid'];
$tgl_system=$_SESSION['stgl_system'];
if(!isset($_SESSION['userid'])){
   header("location:index.html");
}
if($_SESSION['level']!="lead"){
   header("location:index.html");
}
if(!isset($_SESSION['stgl_system'])){
echo "<script>alert('Maaf,.tanggal system belum di setup');</script>";
   }
include "config.php";
include "header.php";


?>
<p>

<a href="create.php" class="btn btn-primary btn-md"><span class="glyphicon glyphicon-plus" aria-hidden="true"></span> Add Data</a>
 
</p>
<table id="ghatable" class="display table table-bordered table-stripe" cellspacing="0" width="100%">
<thead>
     <tr>
          <th>unit</th>
          <th>tanggal</th>
          <th>kode_transaksi</th>
          <th>kode_rekening</th>
          <th>keterangan</th>
		  <th>Debet</th>
		  <th>Kredit</th>
          <th>Action</th>
     </tr>
</thead>
<tbody>
<?php
$res = $mysqli->query("SELECT id_transaksi,tabel_transaksi.unit,tanggal_transaksi,kode_transaksi,kode_rekening,keterangan_transaksi,debet,kredit FROM tabel_transaksi left join branch on branch.kode_branch = tabel_transaksi.unit  where branch.code_area ='$userid' and tanggal_transaksi='$tgl_system'");
while ($row = $res->fetch_assoc()):
?>
     <tr>
          <td><?php echo $row['unit'] ?></td>
          <td><?php echo $row['tanggal_transaksi'] ?></td>
          <td><?php echo $row['kode_transaksi'] ?></td>
		  <td><?php echo $row['kode_rekening'] ?></td>
          <td><?php echo $row['keterangan_transaksi'] ?></td>
          <td><?php echo $row['debet'] ?></td>
		  <td><?php echo $row['kredit'] ?></td>
		  
          <td>
          <a href="update.php?u=<?php echo $row['id_transaksi'] ?>"><span class="glyphicon glyphicon-pencil" aria-hidden="true"></span> Edit</a>
          <a onclick="return confirm('Are you want deleting data')" href="delete.php?d=<?php echo $row['kode_transaksi'] ?>"><span class="glyphicon glyphicon-trash" aria-hidden="true"></span> Delete</a>
          </td>
     </tr>
<?php
endwhile;
?>
</tbody>
</table>     
<?php
include "footer.php";
?>

