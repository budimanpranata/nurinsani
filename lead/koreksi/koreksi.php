<?php require_once __DIR__ . '/../../php8_compat.php';
require_once __DIR__ . '/../../inc/csrf.php';
require_once __DIR__ . '/../../inc/notif.php';
session_start();
$userid=$_SESSION['userid'];
$tgl_system=$_SESSION['stgl_system'];
if(!isset($_SESSION['userid'])){
   header("location:index.html");
 exit; }
if($_SESSION['level']!="lead"){
   header("location:index.html");
 exit; }
if(!isset($_SESSION['stgl_system'])){
notif_pesan('Maaf, tanggal system belum di setup', 'warning');
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
          <form method="post" action="delete.php" style="display:inline" onsubmit="return confirm('Are you want deleting data')"><?php echo csrf_field(); ?><input type="hidden" name="d" value="<?php echo htmlspecialchars($row['kode_transaksi'], ENT_QUOTES); ?>"><button type="submit" class="btn btn-link btn-sm" style="padding:0;border:0;vertical-align:baseline"><span class="glyphicon glyphicon-trash" aria-hidden="true"></span> Delete</button></form>
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

