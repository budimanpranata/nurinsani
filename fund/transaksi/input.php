<?php require_once __DIR__ . '/../../php8_compat.php'; 
session_start();
ob_start();
        $userid=$_SESSION['userid'];
        if(!isset($_SESSION['userid'])){
   header("location:../index.html");
}

include "../conn.php";
	$today=date("Ymd");
	$k = db_query ($koneksi,"select kode_branch AS unit FROM user where userid='$userid'");
			  $kbranch = db_fetch_array($k);
			  $unit = $kbranch['unit'];
			var_dump($unit);  
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
				$tempat_lahir  = $_POST['tempat_lahir'];
				$tanggal_lahir = $_POST['tanggal_lahir'];
				$alamat        = $_POST['alamat'];
                $notelp        = $_POST['notelp'];
				$norek			= $_POST['norek'];
				$nama_bank		= $_POST['bank'];
				$ktp		= $_POST['ktp'];
				$npwp		= $_POST['npwp'];
				
				$cek = db_query($koneksi,"SELECT * FROM deposan WHERE ID_NUMBER='$ktp'");
				if(db_num_rows($cek) == 0){


$empat ="04";
$norek04 ="$cif$empat";
$lima ="05";
$norek05 ="$cif$lima";
$enam ="06";
$norek06 ="$cif$enam";
$tujuh ="07";
$norek07 ="$cif$tujuh";

 $insert= db_query($koneksi,"Insert into simpanan_kp(BUSS_DATE,SNOREK,SUNIT,SCIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,blok,tgl_input,kode_transaksi)VALUES('$today','$norek04','$unit','$cif','$code_kel',0,'04',0,'$userid','$keterangan_transaksi','','$cao','1','$today','$trns')");
 $insert= db_query($koneksi,"Insert into simpanan_kp(BUSS_DATE,SNOREK,SUNIT,SCIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,blok,tgl_input,kode_transaksi)VALUES('$today','$norek05','$unit','$cif','$code_kel',0,'05',0,'$userid','$keterangan_transaksi','','$cao','1','$today','$trns')");
 $insert= db_query($koneksi,"Insert into simpanan_kp(BUSS_DATE,SNOREK,SUNIT,SCIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,blok,tgl_input,kode_transaksi)VALUES('$today','$norek06','$unit','$cif','$code_kel',0,'06',0,'$userid','$keterangan_transaksi','','$cao','1','$today','$trns')");
 $insert= db_query($koneksi,"Insert into simpanan_kp(BUSS_DATE,SNOREK,SUNIT,SCIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,blok,tgl_input,kode_transaksi)VALUES('$today','$norek07','$unit','$cif','$code_kel',0,'07',0,'$userid','$keterangan_transaksi','','$cao','1','$today','$trns')");
$insert = db_query($koneksi,"INSERT INTO anggota(no,CIF,unit,CUST_SHORT_NAME,BIRTH_DATE, HADDRESS1, MOBILE_PHONE,ID_NUMBER,NPWP)
															VALUES('$nim','$cif','$unit','$nama', '$tanggal_lahir', '$alamat', '$notelp','$ktp','$npwp')") or die(mysqli_error());
				
$insert = db_query($koneksi,"INSERT INTO deposan(no,CIF,unit,CUST_SHORT_NAME, TEMPAT_LAHIR, BIRTH_DATE, HADDRESS1, MOBILE_PHONE,norek_tujuan,nama_bank,ID_NUMBER,NPWP)
															VALUES('$nim','$cif','$unit','$nama', '$tempat_lahir', '$tanggal_lahir', '$alamat', '$notelp','$norek','$nama_bank','$ktp','$npwp')") or die(mysqli_error());
						if($insert){
							echo '<div class="alert alert-success alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>Data Berhasil Di Simpan.</div>';
						}else{
							echo '<div class="alert alert-danger alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>Ups, Data Gagal Di simpan !</div>';
						}
				}else{
					echo '<div class="alert alert-danger alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>Maaf, GAgal..!</div>';
				}
			}
			?>
            
            <blockquote>
            Input Data Deposan Nur Insani
            </blockquote>
                         <form name="form1" id="form1" class="form-horizontal row-fluid" action="input.php" method="POST" >
										<div class="control-group">
											<label class="control-label" for="basicinput">No</label>
											<div class="controls">
												<input type="text" name="nim" id="nim" placeholder="Tidak perlu di isi" value="<?php echo $nim; ?>" class="form-control span8 tip" readonly="readonly">
											</div>
										</div>
										<div class="control-group">
											<label class="control-label" for="basicinput">Nomor KTP</label>
											<div class="controls">
												<input type="text" name="ktp" id="ktp" placeholder="Nomor KTP" class="form-control span8 tip" required>
											</div>
										</div>

										<div class="control-group">
											<label class="control-label" for="basicinput">Nama</label>
											<div class="controls">
												<input type="text" name="nama" id="nama" placeholder="Nama Deposan" class="form-control span8 tip" required>
											</div>
										</div>

										<div class="control-group">
											<label class="control-label" for="basicinput">Tempat Lahir</label>
											<div class="controls">
												<input type="text" name="tempat_lahir" id="tempat_lahir" placeholder="Tempat Lahir" class="form-control span8 tip" required>
											</div>
										</div>

										<div class="control-group">
											<label class="control-label" for="basicinput">Tanggal Lahir</label>
											<div class="controls">
												<input name="tanggal_lahir" id="tanggal_lahir" class="input-group date form-control span8 tip" type="text"  date="" placeholder="yyyy/mm/dd" required />
											</div>
										</div>

										<div class="control-group">
											<label class="control-label" for="basicinput">Alamat</label>
											<div class="controls">
												<input name="alamat" id="alamat" class=" form-control span8 tip" type="text" placeholder="Alamat" required />
											</div>
										</div>
<div class="control-group">
											<label class="control-label" for="basicinput">NPWP</label>
											<div class="controls">
												<input name="npwp" id="npwp" class=" form-control span8 tip" type="text" placeholder="NPWP" required />
											</div>
										</div>
                                        
                                        <div class="control-group">
											<label class="control-label" for="basicinput">No Telepon</label>
											<div class="controls">
												<input name="notelp" id="notelp" class=" form-control span8 tip" type="text" placeholder="No Telepon" required />
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
											<div class="controls">
												<button type="submit" name="input" id="input" class="btn btn-sm btn-primary">Simpan</button>
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
