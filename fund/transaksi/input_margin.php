<?php require_once __DIR__ . '/../../php8_compat.php'; 
ini_set('display_errors',1);
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
			  $cif="$unit$nextNoUrut";

			   ?>
<!DOCTYPE html>
<html lang="en">
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
                        <h3 class="panel-title"><i class="icon-user"></i>Parameter Margin Nur Insani</h3> 
                        </div>
                        <div class="panel-body">
                            <?php
			if(isset($_POST['input'])){
				$perode_bulan = $_POST['bulan'];
				$nominal = $_POST['nominal'];
				$jenis   = $_POST['jenis'];
                $nisbah=$_POST['nisbah'];
				$rasio_cor=100-$nisbah;
				
				if ($nisbah=="DP20"){
					$dp="DP20";
					$rate="15";
					}elseif($nisbah=="DP19"){
					$dp="DP19";
					$rate="14";
					}elseif($nisbah=="DP18"){
					$dp="DP18";
					$rate="13";
					}elseif($nisbah=="DP17"){
					$dp="DP17";
					$rate="11";
					}elseif($nisbah=="DP16"){
					$dp="DP16";
					$rate="11";
					}elseif($nisbah=="DP15"){
					$dp="DP15";
					$rate="11";
					}elseif($nisbah=="DP14"){
					$dp="DP14";
					$rate="11";
					}elseif($nisbah=="DP13"){
					$dp="DP13";
					$rate="11";
					}elseif($nisbah=="DP12"){
					$dp="DP12";
					$rate="11";
					}
					
				if ($jenis =="WD"){
					$tahun = substr($perode_bulan,0,4);
					$bulan=substr($perode_bulan,4,3);
					$code_bulan="WD$tahun$bulan";
				}elseif($jenis =="DP"){
					$tahun = substr($perode_bulan,0,4);
					$bulan=substr($perode_bulan,4,3);
					$code_bulan="$dp$tahun$bulan";
					
				}elseif($jenis =="MC"){
					$tahun = substr($perode_bulan,0,4);
					$bulan=substr($perode_bulan,4,3);
					$code_bulan="MC$tahun$bulan";
					
				}elseif($jenis =="EMC"){
					$tahun = substr($perode_bulan,0,4);
					$bulan=substr($perode_bulan,4,3);
					$code_bulan="EMC$tahun$bulan";
					
				}
				
				$dep=substr($dp,2,2);

				
				$cek = db_query($koneksi, "SELECT * FROM param_deposito WHERE code_bulan='$nim'");
				if(db_num_rows($cek) == 0){
						$insert = db_query($koneksi, "INSERT INTO param_deposito(code_bulan,code_jenis, nominal, buss_date, rate)
															VALUES('$code_bulan','$jenis','$nominal', '$perode_bulan','$rate')") or die(mysqli_error());
						$insert_rasio = db_query($koneksi, "INSERT INTO param_rasio(code_bulan,code_jenis, nominal, buss_date,rasio_invest,rate)
															VALUES('$code_bulan','$jenis','$nominal','$perode_bulan','$dep','$rate')") or die(mysqli_error());
															
					$insert_par = db_query($koneksi, "INSERT INTO param_dep(code_bulan,code_jenis, nominal, buss_date)
															VALUES('$code_bulan','$jenis','$nominal', '$perode_bulan')") or die(mysqli_error());
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
            Input Parameter margin Nur Insani
            </blockquote>
                         <form name="form1" id="form1" class="form-horizontal row-fluid" action="input_margin.php" method="POST" >
										<div class="control-group">
											<label class="control-label" for="basicinput">Kode Bulan</label>
											<div class="controls">
												<input type="text" name="nim" id="nim" placeholder="Tidak perlu di isi" value="<?php echo $nim; ?>" class="form-control span8 tip" readonly="readonly">
											</div>
										</div>

										<div class="control-group">
											<label class="control-label" for="basicinput">Jenis Dana</label>
											<div class="controls">
											<select name="jenis" id ="jenis" class="form-control span8 tip" required>
											<option value="">Pilih Dana</option>
											<option value="WD">TABUNGAN WADIAH</option>
											<option value="DP">DEPOSITO MUDHARABAH</option>
											<option value="MC">MICRO CAPITAL</option>
											<option value="EMC">ESCROW MICRO CAPITAL</option>
 								
											</select> 
											</div>
										</div>
										
										<div id="tombol" class="hasil">
											<div class="control-group">
											<label class="control-label" for="basicinput">Nisbah Mudarobah</label>
											<div class="controls">
											<select name="nisbah" id ="nisbah" class="form-control span8 tip" >
											<option value="">Pilih Nisbah Mudarobah</option>
											<option value="DP20">Nisbah 20%</option>
											<option value="DP19">Nisbah 19%</option>
											<option value="DP18">Nisbah 18%</option>
											<option value="DP17">Nisbah 17%</option>
											<option value="DP16">Nisbah 16%</option>
											<option value="DP15">Nisbah 15%</option>
												<option value="DP14">Nisbah 14%</option>
													<option value="DP13">Nisbah 13%</option>
														<option value="DP12">Nisbah 12%</option>
 								
											</select> 											
										</div>
										</div>
										</div>
										

										<div class="control-group">
											<label class="control-label" for="basicinput">Nominal</label>
											<div class="controls">
												<input type="text" name="nominal" id="nominal" placeholder="isi Nominal" class="form-control span8 tip" required>
											</div>
										</div>
										
										<div class="control-group">
											<label class="control-label" for="basicinput">Perode Bulan</label>
											<div class="controls">
												<input name="bulan" id="bulan" class="input-group date form-control span8 tip" type="text"  date="" placeholder="yyyy/mm/dd" required />
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
$(document).ready(function(){
$('.hasil').hide();
$('#tombol').hide();
$('#jenis').change(function(){
if($('#jenis').val()=='WD'){
$('.hasil').hide();	
$('#tombol').hide();
$('#mitra').hide();
}else if($('#jenis').val()=='MC'){
$('.hasil').hide();
$('#tombol').hide();
$('#mitra').hide();
}else if($('#jenis').val()=='EMC'){
$('.hasil').hide();
$('#tombol').hide();
$('#mitra').hide();
}else if($('#jenis').val()=='DP'){
$('.hasil').show();
$('#tombol').show();
$('#mitra').show();
}
});
});
</script>
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
						url :"../view/ajax-grid-data_margin.php", // json datasource
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
