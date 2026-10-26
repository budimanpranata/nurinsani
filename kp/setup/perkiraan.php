<?php require_once __DIR__ . '/../../php8_compat.php'; 
session_start();
include "../conn2.php";
include "../../indo_tgl.php";
include "../encryption/function.php";

	$koneksi=open_connection2();
	$user=mysql_query("select kode_branch from user where userid='$userid'");
			
			while($id_user=mysql_fetch_array($user)){
			
				$unitt=$id_user['kode_branch'];
			}
if($_SESSION['userid'])
{
?>
	
	<head>
  <title>KSPPS Nurinsani</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
  <link rel="stylesheet" href="css/style.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">	
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.3.0/css/datepicker.css" rel="stylesheet" type="text/css" />
<link href="//maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.3.0/js/bootstrap-datepicker.js"></script> 


    <link rel="stylesheet" type="text/css" src="https://cdn.datatables.net/buttons/1.5.1/css/buttons.dataTables.min.css"/>


    <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
    <script src="https://cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.5.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.5.1/js/buttons.flash.min.js"></script>
   <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.32/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.32/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.5.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.5.1/js/buttons.print.min.js"></script>

   <script type="text/javascript">

$(document).ready(function() {
    $('#ghatable').DataTable( {
			
        dom: 'Bfrtip',
        buttons: [
            'copy', 'csv', 'excel', 'pdf', 'print'
        ]
    } );
} );
</script>

  
</head>

<body>
        <script type="text/javascript">
          
			$(function () {
                $('#tgl_akhir').datepicker({
				autoclose: true, 
				todayHighlight: true,
				format: "yyyy-mm-dd",
				todayBtn: true,	
				  });;
				});		
        </script>
<style>
 .table-condensed{
  font-size: 8px;
}
</style>
  <div class="panel panel-success">
    <div class="panel-heading">System Prameter</div>
    <div class="panel-body">
   

<title>Setup Coa</title>
<link rel="stylesheet" href="../tabel.css" />

<div class="container">
<div class="panel panel-success">
  <div class="panel-body">
	<div class="col-xs-10">
	<form action="?<?php echo paramEncrypt('page=./setup/perkiraan')?>" method="post" name="form"  class="form-horizontal">
	<div class="post">
		<div class="entry">
		<div class="form-group">
			<label for="inputEmail" class="control-label col-xs-2">Kode COA</label>
			<div class="col-xs-8">				
				<div>
				<input name="kode_rekening" type="text" class="form-control" placeholder="COA"/>
				
				</div>				
			</div>
		</div>
		<div class="form-group">
			<label for="inputEmail" class="control-label col-xs-2">Nama Rekening</label>
			<div class="col-xs-8">				
				<div>
				<input name="nama_rekening" type="text" class="form-control" placeholder="Nama Rekening"/>
				
				</div>				
			</div>
		</div>
		<div class="form-group">
			<label for="inputEmail" class="control-label col-xs-2">Normal balance</label>
			<div class="col-xs-8">				
				
				<select class="form-control" name="normal">
					<option value="debet">Debet</option>
					<option value="kredit">Kredit</option>
				</select>
				
			
			</div>
		</div>
		
		<div class="form-group">
			<label for="inputEmail" class="control-label col-xs-2">Group Header</label>
			<div class="col-xs-8">	
			<select class="form-control" name="gr_sub" id="gr_sub">		
				 <option selected>Pilih Salah satu</option>
				 <?php
              		$query = "select gr_sub,nama_rek from coa where gr_sub > 1 group by gr_sub  ";
					$result = mysql_query($query);
					while ($kanwil=mysql_fetch_array($result))
					{
			  		  	echo "<option value=$kanwil[0]>$kanwil[0]-$kanwil[1]</option>";
					}
              		echo"</select>";?>
				
			
			</div>
		</div>
		         
					
		<div class="form-group">
			<label for="inputEmail" class="control-label col-xs-2">Posisi</label>
			<div class="col-xs-8">				
				
				<select name="posisi" class="form-control">
					<option value="PASIVA">Pasiva</option>
					<option value="AKTIVA">Aktiva</option>
					<option value="rugi-laba">Rugi Laba</option>
<option value="admin">Administratif</option>

				</select>
				
			
			</div>
		</div>
		<div class="form-group">
			<label for="inputPassword" class="control-label col-xs-2">Tangggal Buka</label>
			<div class="col-xs-8">
				<div class='input-group date' id='tgl_akhir'>
				<input name="tgl_akhir" type="text" class="form-control" placeholder="Tangggal Akhir"/>
				<span class="input-group-addon">
				<span class="glyphicon glyphicon-calendar"></span>
				</span>
				</div>	
			</div>
		</div>
		
		
		
		<div class="form-group">
			<div class="col-xs-offset-2 col-xs-8">
				<input type="submit" value="Simpan"  class="btn btn-primary"/>
			</div>
		</div>

	</div>
	</div>
</div>
	
	
		
			
			</form>
			
			
			<table id="ghatable" class="display table table-bordered table-stripe">
			<thead>
			<tr>
				<th>Kode Rekening</th>
				<th>Nama Rekening</th>
				<th>Posisi</th>
				<th>Normal</th>
			</tr>
			</thead>
			<tbody>
			<?php
			//$total=mysql_fetch_array(mysql_query("select sum(awal_debet) as tot_awal_debet,sum(awal_kredit) as tot_awal_kredit from coa order by kode_rekening asc"));
			$query=mysql_query("select * from coa order by kode_rek asc");
			while($row=mysql_fetch_array($query)){
				
				?>
				<tr>
					<td align="center"><?php echo $row['kode_rek'];?></td><td><?php echo $row['nama_rek'];?></td>
					<td><?php echo $row['posisi'];?></td><td><?php echo $row['normal'];?></td>
				</tr>
				<?php
				
			}
			?>
							
			</tbody>
</table>  
			
			</p>
		</div>
	</div>
	
	</div>
     </div>
      
    </div>
 
    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
      </body>
</html>
	
	<?php
	if(isset($_POST['kode_rekening'])){
	
		$kode_rekening=$_POST['kode_rekening'];
		$nama_rekening=ucwords($_POST['nama_rekening']);
		$normal=$_POST['normal'];
		$gr_neraca=$_POST['posisi'];
		$gr_sub=$_POST['gr_sub'];
		$ambil_neraca=substr($kode_rekening,0,2);
		$ambil_laba=substr($kode_rekening,0,4);
		
		
		
		if($gr_neraca=='PASIVA'){
		$posisi='Neraca';
		
		}elseif($gr_neraca=='AKTIVA'){
			$posisi='Neraca';
			
			
		}elseif($gr_neraca=='Rugi-laba'){
			$posisi='Rugi-laba';
			
			
		}else{
			$posisi='admin';
			
		}

		
		$query=mysql_query("select unit from tabel_master group by unit");
while($data = mysql_fetch_array($query)){
$unit = $data['unit'];
		$rek_gl="$unit-$kode_rekening";
		
		
		mysql_query("insert into tabel_master(rekening_gl,kode_rekening,nama_rekening,tanggal_awal,awal_debet,awal_kredit,posisi,normal,unit,gr_sub,gr_neraca,LINE_BALANCE) 
							values('$rek_gl','$kode_rekening','$nama_rekening','$tanggal','$awal_debet','$awal_kredit','$posisi','$normal','$unit','$gr_sub','$gr_neraca','DETAIL')");
}		

$query=mysql_query("insert into coa(kode_rek,nama_rek,posisi,normal,LINE_BALANCE) 
							values('$kode_rekening','$nama_rekening','$posisi','$normal','DETAIL')");

		if($query){
			echo "<script>alert('Sukses di simpan');</script>";
			?><script language="javascript">document.location.href="?<?php echo paramEncrypt('page=./setup/perkiraan')?>"</script><?php
		}else{
			echo mysql_error();
				echo "<script>alert('Gagal di simpan No Gl sudah tersedia');</script>";
		}
	
	}else{
		unset($_POST['kode_rekening']);
	}
	?>
	
<?php 
}else{
	echo "Forbidden Access!";
}
?>
