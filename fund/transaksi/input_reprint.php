<?php require_once __DIR__ . '/../../php8_compat.php'; 
session_start();
ob_start();
		$userid=$_SESSION['userid'];
include "../conn.php";
	$today=date("Ymd");
	$k = db_query ($koneksi,"select kode_branch AS unit FROM user where userid='$userid'");
			  $kbranch = db_fetch_array($k);
			  $unit = $kbranch['unit'];
			  
	$dcount = db_query ($koneksi,"select max(no) AS last FROM deposan where  no LIKE '%$today%'");
			  $hit = db_fetch_array($dcount);
			  $lastNoTransaksi = $hit['last'];
			  $lastNoUrut = substr($lastNoTransaksi, 12, 4);
			  $nextNoUrut = (int) $lastNoUrut + 1;
			  $nextNoTransaksi = $today.sprintf('%04s', $nextNoUrut);
			  $nim="$unit$nextNoTransaksi";
			  $cif="$nextNoTransaksi";
			   ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <!-- css table datatables/dataTables -->
	    <link rel="stylesheet" href="datatables/dataTables.bootstrap.css"/>
        <link type="text/css" href="../css/bootstrap.css" rel="stylesheet">
        <link type="text/css" href="../bootstrap/css/bootstrap.min.css" rel="stylesheet"> 
        <link type="text/css" href="../bootstrap/css/bootstrap-responsive.min.css" rel="stylesheet">
        <link type="text/css" href="../css/theme.css" rel="stylesheet">
        <link type="text/css" href="../images/icons/css/font-awesome.css" rel="stylesheet">
        <link type="text/css" href='http://fonts.googleapis.com/css?family=Open+Sans:400italic,600italic,400,600'
            rel='stylesheet'>
        
        <script src="../scripts/jquery-1.9.1.min.js" type="text/javascript"></script>
        <script src="../scripts/jquery-ui-1.10.1.custom.min.js" type="text/javascript"></script>
    </head>
    <body>
                

            <div class="container">
                <div class="row">
                    <div class="span12">
                        <div class="content"><div class="panel panel-default">
                        <div class="panel-heading">
                        <h3 class="panel-title"><i class="icon-user"></i>Reprint Bilyet dan Advis Nur Insani</h3> 
                        </div>
                        <div class="panel-body">
                            <?php
			if(isset($_POST['input'])){
				$reprint		= $_POST['reprint'];
				$norek   		= $_POST['tempat_lahir'];
				$nomor_surat  	= $_POST['tanggal_lahir'];
				$status			= $_POST['status'];
				
				
				$cek = db_query($koneksi, "SELECT * FROM dep_reprint WHERE ID_NUMBER='$kpt'");
				if(db_num_rows($cek) == 0){
						$insert = db_query($koneksi, "INSERT INTO dep_reprint(id,norek,cif,status,keterangan,no_ket)
															VALUES('$id','$norek','$cif','$status','$keterangan','$nomor_surat')") or die(mysqli_error());
						if($insert){
							echo '<div class="alert alert-success alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>Data Berhasil Di Simpan.</div>';
						}else{
							echo '<div class="alert alert-danger alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>Ups, Data Gagal Di simpan !</div>';
						}
				}else{
					echo '<div class="alert alert-danger alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>NOMOR KTP Sudah Ada..!</div>';
				}
			}
			?>
            
            <blockquote>
            Input Data Reprint Nur Insani
            </blockquote>
                         <form name="form1" id="form1" class="form-horizontal row-fluid" action="input_reprint.php" method="POST" >
										<div class="control-group">
											<label class="control-label" for="basicinput">No</label>
											<div class="controls">
												<input type="text" name="norek" id="norek" placeholder="Tidak perlu di isi" value="<?php echo $nim; ?>" class="form-control span8 tip" readonly="readonly">
											</div>
										</div>
										<div class="control-group">
										<label class="control-label" for="basicinput">Jeni Cetak</label>
											<div class="controls">
											<select name="reprint" id ="reprint" class="form-control span8 tip" required>
											<option value="">Pilih Jenis Reprint</option>
											<option value="1">Bilyet</option>
											<option value="2">Advis</option>
																	
											</select> 
										</div>
										</div>
										<div class="control-group">
											<label class="control-label" for="basicinput">Nomor Surat Kehilangan</label>
											<div class="controls">
												<input type="text" name="nomor_surat" id="nomor_surat" placeholder="Nomor Surat Kehilangan" class="form-control span8 tip" required>
											</div>
										</div>
										<div class="control-group">
											<label class="control-label" for="basicinput">Upload Dokumen</label>
											<div class="controls">
											  <input type="text" name="userImage[]" id="userImage[]" placeholder="Nomor Surat Kehilangan" class="form-control span8 tip" required>
											</div>
										</div>
										
										<div class="control-group">
											<div class="controls">
												<button type="submit" name="input" id="input" class="btn btn-sm btn-primary">Simpan</button>
                                               <a href="../view/master_reprint.php" class="btn btn-sm btn-danger">Kembali</a>
											</div>
										</div>
									</form>
                        </div>
                        <!--/.content-->
                    </div>
                    <!--/.span9-->
                </div>
            </div>
            <!--/.container-->
        
        <!--/.wrapper--><br />
       <script src="../js/bootstrap-datepicker.js"></script>
       		
	
<script>
	$('.date').datepicker({
		format: 'yyyy-mm-dd',
	})
	</script>
	
  
        <script src="../bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
        
        <script src="../datatables/jquery.dataTables.js"></script>
        <script src="../datatables/dataTables.bootstrap.js"></script>
     </style>
<script src="js/jquery-2.1.1.min.js" type="text/javascript"></script>
<script type="text/javascript">
$(document).ready(function (e) {
	$("#uploadForm").on('submit',(function(e) {
		e.preventDefault();
		$.ajax({
        	url: "upload_img.php",
			type: "POST",
			data:  new FormData(this),
			contentType: false,
    	    cache: false,
			processData:false,
			success: function(data){
			$("#gallery").html(data);
		    },
		  	error: function(){} 	        
	   });
	}));
});
</script>
      
    </body>
