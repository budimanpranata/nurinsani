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
                        <h3 class="panel-title"><i class="icon-user"></i> Data Deposan Nur Insani</h3> 
                        </div>
                        <div class="panel-body">
                            <?php
			if(isset($_POST['input'])){
				$nama		   = $_POST['nama'];
				$npwp  = $_POST['npwp'];
				$norek			= $_POST['norek'];
				$nama_bank		= $_POST['bank'];
				$alamat			= $_POST['alamat'];
				$ktp_satu		= $_POST['ktp'];
				$ktp_dua		= $_POST['ktp_dua'];
				$nama_satu = $_POST['nama_satu'];
				$nama_dua = $_POST['nama_dua'];
				$alamat_satu=$_POST['alamat_satu'];
				$alamat_dua = $_POST['alamat_dua'];
				$jabatan_satu=$_POST['jabatan_satu'];
				$jabatan_dua = $_POST['jabatan_dua'];
				
				$cek = db_query($koneksi, "SELECT * FROM deposan WHERE ID_NUMBER='$ktp'");
				if(db_num_rows($cek) == 0){
						$insert = db_query($koneksi, "INSERT INTO deposan(no,CIF,unit,CUST_SHORT_NAME,NAMA_SATU,NAMA_DUA,KTP_SATU,KTP_DUA,HADDRESS1,HADDRESS2,HADDRESS3,norek_tujuan,nama_bank,ID_NUMBER,JENIS,NPWP)
															VALUES('$nim','$cif','$unit','$nama','$nama_satu', '$nama_dua','$ktp_satu','$ktp_dua','$alamat','$alamat_satu','$alamat_dua','$bank','$ktp','PERUSHAAN','$npwp')") or die(mysqli_error());
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
            Input Data Perusahaan
            </blockquote>
                         <form name="form1" id="form1" class="form-horizontal row-fluid" action="input.php" method="POST" >
										<div class="control-group">
											<label class="control-label" for="basicinput">No</label>
											<div class="controls">
												<input type="text" name="nim" id="nim" placeholder="Tidak perlu di isi" value="<?php echo $nim; ?>" class="form-control span8 tip" readonly="readonly">
											</div>
										</div>
										
										<div class="control-group">
											<label class="control-label" for="basicinput">Nama Perusahaan</label>
											<div class="controls">
												<input type="text" name="nama" id="nama" placeholder="Nama Perusahaan" class="form-control span8 tip" required>
											</div>
										</div>
										<div class="control-group">
											<label class="control-label" for="basicinput">NPWP</label>
											<div class="controls">
												<input type="text" name="npwp" id="npwp" placeholder="NPWP Perusahaan" class="form-control span8 tip" required>
											</div>
										</div>
										 <div class="control-group">
											<label class="control-label" for="basicinput">No Rekening Bank Lain</label>
											<div class="controls">
												<input name="norek" id="norek" class=" form-control span8 tip" type="text" placeholder="No Rekening Pencairan Baghas" required />
											</div>
										</div>
										 <div class="control-group">
											<label class="control-label" for="basicinput">Nama Bank</label>
											<div class="controls">
												<input name="bank" id="bank" class=" form-control span8 tip" type="text" placeholder="Nama Bank" required />
											</div>
										</div>
			<div class="control-group">
			<blockquote>
            Susunan Pengurus
            </blockquote>
			</div>							<div class="control-group">
											<label class="control-label" for="basicinput">NIK</label>
											<div class="controls">
												<input type="text" name="ktp" id="ktp" placeholder="Nomor KTP" class="form-control span8 tip" required>
											</div>
										</div>

										<div class="control-group">
											<label class="control-label" for="basicinput">Nama</label>
											<div class="controls">
												<input type="text" name="nama_satu" id="nama_satu" placeholder="Nama Deposan" class="form-control span8 tip" required>
											</div>
										</div>

											<div class="control-group">
											<label class="control-label" for="basicinput">Alamat</label>
											<div class="controls">
												<input name="alamat_satu" id="alamat_satu" class=" form-control span8 tip" type="text" placeholder="Alamat" required />
											</div>
										</div>
                                        
                                        <div class="control-group">
											<label class="control-label" for="basicinput">Bertindak Sebagai</label>
											<div class="controls">
												<input name="jabatan_satu" id="jabatan_dua" class=" form-control span8 tip" type="text" placeholder="Jabatan" required />
											</div>
										</div>
										<div class="control-group">
											<label class="control-label" for="basicinput">NIK</label>
											<div class="controls">
												<input type="text" name="ktp_dua" id="ktp_dua" placeholder="Nomor KTP" class="form-control span8 tip" required>
											</div>
										</div>

                                       <div class="control-group">
											<label class="control-label" for="basicinput">Nama</label>
											<div class="controls">
												<input type="text" name="nama_dua" id="nama_dua" placeholder="Nama Deposan" class="form-control span8 tip" required>
											</div>
										</div>

											<div class="control-group">
											<label class="control-label" for="basicinput">Alamat</label>
											<div class="controls">
												<input name="alamat_dua" id="alamat_dua" class=" form-control span8 tip" type="text" placeholder="Alamat" required />
											</div>
										</div>
                                        
                                        <div class="control-group">
											<label class="control-label" for="basicinput">Bertindak Sebagai</label>
											<div class="controls">
												<input name="jabatan_dua" id="jabatan_dua" class=" form-control span8 tip" type="text" placeholder="Jabatan" required />
											</div>
										</div>
										<div class="control-group">
											<div class="controls">
												<button type="submit" name="input" id="input" class="btn btn-sm btn-primary">Simpan</button>
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
