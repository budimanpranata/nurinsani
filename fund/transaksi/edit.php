<?php require_once __DIR__ . '/../../php8_compat.php'; include "../conn.php"; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Nur Insani</title>
        <!-- css table datatables/dataTables -->
	    <link rel="stylesheet" href="../datatables/dataTables.bootstrap.css"/>
        
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
                        <h3 class="panel-title"><i class="icon-user"></i> Data Deposan Nur Insani</h3> 
                        </div>
                        <div class="panel-body">
                       
            <!-- /navbar-inner -->
        </div><br />

            <div class="container">
                <div class="row">
                    <div class="span12">
                        <div class="content">
                            <?php
           $kd = $_GET['kd'];
			$sql = db_query($koneksi, "SELECT * FROM deposan WHERE CIF='$kd'");
			if(db_num_rows($sql) == 0){
				header("Location: master_fund.php");
			}else{
				$row = db_fetch_assoc($sql);
			}
			?>
            
            <blockquote>
            Update Data Depsosan Nur Insani
            </blockquote>
                         <form name="form1" id="form1" class="form-horizontal row-fluid" action="update-edit.php" method="POST" >
										<div class="control-group">
											<label class="control-label" for="basicinput">no</label>
											<div class="controls">
												<input type="text" name="nim" id="nim" value="<?php echo $row['no']; ?>" placeholder="Tidak perlu di isi" class="form-control span8 tip" readonly="readonly">
											</div>
										</div>
<div class="control-group">
											<label class="control-label" for="basicinput">KTP/SIM</label>
											<div class="controls">
												<input type="text" name="ktp" id="ktp" value="<?php echo $row['ID_NUMBER']; ?>" placeholder="KTP" class="form-control span8 tip" required>
											</div>
										</div>


										<div class="control-group">
											<label class="control-label" for="basicinput">Nama Deposan</label>
											<div class="controls">
												<input type="text" name="nama" id="nama" value="<?php echo $row['CUST_SHORT_NAME']; ?>" placeholder="Nama Mahasiswa" class="form-control span8 tip" required>
											</div>
										</div>

										<div class="control-group">
											<label class="control-label" for="basicinput">Tanggal Lahir</label>
											<div class="controls">
												<input name="tanggal_lahir" id="tanggal_lahir" value="<?php echo $row['BIRTH_DATE']; ?>" class="form-control span8 tip" type="text" placeholder="yyyy/mm/dd" required />
											</div>
										</div>

										<div class="control-group">
											<label class="control-label" for="basicinput">Alamat</label>
											<div class="controls">
												<input name="alamat" id="alamat" value="<?php echo $row['HADDRESS1']; ?>" class="form-control span8 tip" type="text" placeholder="Alamat" required />
											</div>
										</div>
                                        
                                        <div class="control-group">
											<label class="control-label" for="basicinput">No Telepon</label>
											<div class="controls">
												<input name="notelp" id="notelp" value="<?php echo $row['MOBILE_PHONE']; ?>" class=" form-control span8 tip" type="text" placeholder="No Telepon" required />
											</div>
										</div>
										<div class="control-group">
											<label class="control-label" for="basicinput">Norek Tujuan</label>
											<div class="controls">
												<input name="norek_tujuan" id="norek_tujuan" value="<?php echo $row['norek_tujuan']; ?>" class=" form-control span8 tip" type="text" placeholder="No Telepon" required />
											</div>
										</div>
										<div class="control-group">
											<label class="control-label" for="basicinput">Nama Bank</label>
											<div class="controls">
												<input name="bank" id="bank" value="<?php echo $row['nama_bank']; ?>" class=" form-control span8 tip" type="text" placeholder="No Telepon" required />
											</div>
										</div>
										<div class="control-group">
											<div class="controls">
												<input type="submit" name="update" id="update" value="Update" class="btn btn-sm btn-primary"/>
                                               <a href="../view/master_fund.php" class="btn btn-sm btn-danger">Kembali</a>
											</div>
										</div>
									</form>
                        </div>
                        <!--/.content-->
                    </div>
                    <!--/.span9-->
                </div>
            </div>
		</div>
            <!--/.container-->
        
        <!--/.wrapper--><br />
       
        <script>
	//options method for call datepicker
	$(".input-group.date").datepicker({ autoclose: true, todayHighlight: true });
	
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
						url :"../view/ajax-grid-data.php", // json datasource
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
