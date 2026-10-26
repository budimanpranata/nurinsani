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
			  $nextNoUrut = $dlastNoUrut + 1;
			  $nextNoTransaksi = $today.sprintf('%04s', $nextNoUrut);
			  $nim="$unit$nextNoTransaksi";
			  $cif="$unit$nextNoUrut";
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
                        <h3 class="panel-title"><i class="icon-user"></i>Parameter Financing Nur Insani</h3> 
                        </div>
                        <div class="panel-body">
                            <?php
			if(isset($_POST['input'])){
				$perode_bulan  = $_POST['bulan'];
				$nominal = $_POST['nominal'];
				$margin=$_POST['margin'];;
                $tahun = substr($perode_bulan,0,4);
					$bulan=substr($perode_bulan,4,3);
					$code_bulan="NI$tahun$bulan";
					$sd=$_POST['bulan_dua'];
								
				$cek = db_query($koneksi, "SELECT * FROM param_finan WHERE code_param='$nim	'");
				if(db_num_rows($cek) == 0){
						$insert = db_query($koneksi, "INSERT INTO param_finan(code_param, nomi_murab, marigin_murab,buss_date,dari,sd)
															VALUES('$code_bulan','$nominal','$margin','$perode_bulan','$perode_bulan','$sd')") or die(mysqli_error());
						if($insert){
							echo '<div class="alert alert-success alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>Data Berhasil Di Simpan.</div>';
						}else{
							echo '<div class="alert alert-danger alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>Ups, Data Gagal Di simpan !</div>';
						}
				}else{
					echo '<div class="alert alert-danger alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>NIM Sudah Ada..!</div>';
				}
			}
			?>
            
            <blockquote>
            Input Parameter Financing Nur Insani
            </blockquote>
                         <form name="form1" id="form1" class="form-horizontal row-fluid" action="input_finan.php" method="POST" >
										<div class="control-group">
											<label class="control-label" for="basicinput">Kode Bulan</label>
											<div class="controls">
												<input type="text" name="nim" id="nim" placeholder="Tidak perlu di isi" value="<?php echo $nim; ?>" class="form-control span8 tip" readonly="readonly">
											</div>
										</div>

										<div class="control-group">
											<label class="control-label" for="basicinput">Murabahah Financing</label>
											<div class="controls">
												<input type="text" name="nominal" id="nominal" placeholder="Nominal" class="form-control span8 tip" required>
											</div>											</div>
										<div class="control-group">
											<label class="control-label" for="basicinput">Margin Murabahah</label>
											<div class="controls">
												<input type="text" name="margin" id="margin" placeholder="Margin Murabahah" class="form-control span8 tip" required>
											</div>
										</div>											
											
										<div class="control-group">
											<label class="control-label" for="basicinput">Perode Dari</label>
											<div class="controls">
												<input name="bulan" id="bulan" class="input-group date form-control span8 tip" type="text"  date="" placeholder="yyyy/mm/dd" required />
											</div>
										</div>
									<div class="control-group">
											<label class="control-label" for="basicinput">Perode S.d </label>
											<div class="controls">
												<input name="bulan_dua" id="bulan_dua" class="input-group date form-control span8 tip" type="text"  date="" placeholder="yyyy/mm/dd" required />
											</div>
										</div>

										<div class="control-group">
											<div class="controls">
												<button type="submit" name="input" id="input" class="btn btn-sm btn-primary">Simpan</button>
                                               <a href="../view/view_finan.php" class="btn btn-sm btn-danger">Kembali</a>
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
        <script>
        $(document).ready(function() {
				var dataTable = $('#lookup').DataTable( {
					"processing": true,
					"serverSide": true,
					"ajax":{
						url :"../view/ajax-grid-data_finan.php", // json datasource
						type: "post",  // method  , by default get
						error: function(){  // error handling
							$(".lookup-error").html("");
							$("#lookup").append('<tbody class="employee-grid-error"><tr><th colspan="3">No data found in the server</th></tr></tbody>');
							$("#lookup_processing").css("display","none");
							
						}
					}
				} );
			} );
        </script>
      
    </body>
