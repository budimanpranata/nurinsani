<?php require_once __DIR__ . '/php8_compat.php';
session_start();
ob_start();
$userid=$_SESSION['userid'];
$tgl=$_SESSION['stgl_system'];
//cek apakah user sudah login
if(!isset($_SESSION['userid'])){
   header("location:index.html");
 exit; }

//cek level user
if($_SESSION['level']!="lead"){
   header("location:index.html");
 exit; }  
?>
<link rel="stylesheet" href="css/bootstrap.min.css"/>
<script>
function cek(cekbox){
    for(i=0; i < cekbox.length; i++){
        cekbox[i].checked = true;
    }
}
function uncek(cekbox){
    for(i=0; i < cekbox.length; i++){
        cekbox[i].checked = false;
    }
}
</script>
<?php  

  
include "conn2.php";
include "indo_tgl.php";
$koneksi=open_connection2(); 
//ambil data dari database


 If ($_GET ['area']){
$area=$_GET ['area'];


$date=date('Y-m-d');	
$query = "Select param_tgl.unit as unit,branch.unit as nama_unit,param_tgl from param_tgl inner join branch on branch.kode_branch =param_tgl.unit 
where code_area='$userid'";
$result = mysql_query($query);
?>
<form action="param_tgl_proses.php" method="post">
    <table border="1" style="margin: 10px 0px" class="table table-striped" >
            <tr>
                <th><span class="badge"> PILIH</th>
				<th><span class="badge"> No</th>
                <th><span class="badge"> Nama Unit</th>
                <th><span class="badge">Param Tgl Aktif</th>
                <th><span class="badge">Param Tgl Baru</th>
			    
				 
			            </tr>
    <?php 
$no=0;
    while ($data = mysql_fetch_array($result)) {
$nama_unit=$data['nama_unit'];
$unit=$data['unit'];

$param_tgl=$data['param_tgl'];

$no++;
    ?>
            <tr>
                <td><input type="checkbox" id="cekbox" name="cekbox[]" value="<?php echo $data['unit'] ?>"/></td>
				<td><?php echo $no ?></td>
                <td><?php echo $nama_unit ?></td>
				<td><?php echo tgl_indo($param_tgl) ?></td>
                <td><?php echo tgl_indo($tgl) ?></td>
				
            </tr>
    
    <?php
    }
	}

    ?>

    
	</table>
    <br/>
    <input type="button" class="alert-success" onclick="cek(this.form.cekbox)" value="Select All" />
    <input type="button" class="btn-danger" onclick="uncek(this.form.cekbox)" value="Clear All" />
	<input name="submit" type="submit" class="btn-success" value="Proses Ganti Tgl" />
		
	    <br />
		
</form>
