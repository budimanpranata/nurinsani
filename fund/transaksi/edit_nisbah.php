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
                        <h3 class="panel-title"><i class="icon-user"></i> Data Nisbah  Nur Insani</h3> 
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
			$sql = db_query($koneksi, "SELECT norek,rasio,eq_rate,deposito.nama_bank,norek_bank,CUST_SHORT_NAME
            FROM deposito 
            left join deposan on deposan.CIF = deposito.cif
            WHERE norek='$kd'");
			if(db_num_rows($sql) == 0){
				header("Location: master_fund.php");
			}else{
				$row = db_fetch_assoc($sql);
			}
			?>
            
            <blockquote>
            Update Data Nisbah Nur Insani
            </blockquote>
                         <form name="form1" id="form1" class="form-horizontal row-fluid" action="update_nisbah.php" method="POST" >
										<div class="control-group">
											<label class="control-label" for="basicinput">Norek</label>
											<div class="controls">
												<input type="text" name="norek" id="norek" value="<?php echo $row['norek']; ?>" placeholder="Tidak perlu di isi" class="form-control span8 tip" readonly="readonly">
											</div>
										<div class="control-group">
											<label class="control-label" for="basicinput">Nama Deposan</label>
											<div class="controls">
												<input type="text" name="nama" id="nama" value="<?php echo $row['CUST_SHORT_NAME']; ?>" placeholder="Nama Deposan" class="form-control span8 tip" required>
											</div>
										</div>

										
										<div class="control-group">
											<label class="control-label" for="basicinput">Nisbah</label>
											<div class="controls">
												<input name="rasio" id="rasio" value="<?php echo $row['rasio']; ?>" class=" form-control span8 tip" type="text" placeholder="Rasio Nisbah" required />
											</div>
										</div>
                                        <div class="control-group">
											<label class="control-label" for="basicinput">Rate</label>
											<div class="controls">
												<input name="eq_rate" id="eq_rate" value="<?php echo $row['eq_rate']; ?>" class=" form-control span8 tip" type="text" placeholder="Eq Rate" required />
											</div>
										</div>
										
                                        <div class="control-group">
											<label class="control-label" for="basicinput">Jenis Hitung</label>
											<div class="controls">
											<select name="cara_hitung" id ="cara_hitung" class="form-control span8 tip" required>
											<option value="">Pilih Hitung</option>
											<option value="1">Nisbah</option>
											<option value="2">Rate</option>
																		
											</select> 
											</div>
										</div>
                                        <div class="control-group">
											<label class="control-label" for="basicinput">Nama Bank Tujuan</label>
											<div class="controls">
												<input name="nama_bank" id="nama_bank" value="<?php echo $row['nama_bank']; ?>" class=" form-control span8 tip" type="text" placeholder="Nama Bank" required />
											</div>
										</div>
                                        <div class="control-group">
											<label class="control-label" for="basicinput">Norek</label>
											<div class="controls">
												<input name="norek_bank" id="norek_bank" value="<?php echo $row['norek']; ?>" class=" form-control span8 tip" type="text" placeholder="No Rekening" required />
											</div>
										</div>

                                    
										<div class="control-group">
											<div class="controls">
												<input type="submit" name="update" id="update" value="Update" class="btn btn-sm btn-primary"/>
                                               <a href="../view/master_trans.php" class="btn btn-sm btn-danger">Kembali</a>
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
       
      
    </body>
