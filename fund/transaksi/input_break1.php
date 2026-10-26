<?php require_once __DIR__ . '/../../php8_compat.php'; 
session_start();
ob_start();
        $userid=$_SESSION['userid'];
        if(!isset($_SESSION['userid'])){
   header("location:../index.html");
}

include "../conn.php";
$code=$_GET['kd'];
	$today=date("Ymd");
	$buss_date=$today;
	$k = db_query ($koneksi,"select kode_branch AS unit FROM user where userid='$userid'");
			  $kbranch = db_fetch_array($k);
			  $unit = $kbranch['unit'];
			  
	$dcount = db_query ($koneksi,"select * from dep_break where norek ='$code'");
			  $hit = db_fetch_array($dcount);
			  $nominal = $hit['nominal'];
			 
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
                        <h3 class="panel-title"><i class="icon-user"></i>Input Data Break Nur Insani</h3> 
                        </div>
                        <div class="panel-body">
                            <?php
			if(isset($_POST['input'])){

				$perode_bulan  = $_POST['bulan'];
				 
				$bijak = $_POST['nominal'];
				$jenis   = $_POST['jenis'];
				$norek   = $_POST['norek'];
				$gross=$bijak/90*100;
				$pajak=$gross-$bijak;
             
				$dcount = db_query ($koneksi,"select * from dep_break where norek ='$norek'");
			  $hit = db_fetch_array($dcount);
			  $nomi = $hit['nominal'];
			  $tenor = $hit['tenor'];
			  $cif= $hit['cif'];
			  
			  	$saldo = db_query ($koneksi,"SELECT sum(KREDIT)-sum(DEBIT) as saldo FROM escrow_deposito where NOREK='$norek' group by NOREK");
			  $total_saldo = db_fetch_array($saldo);
			  $saldo_tab = $total_saldo['saldo'];
			  
			  
			  if ($tenor =='6'){
					$sima="1010-2203000";
				}elseif ($tenor =='3') {
					$sima="1010-2202000";
				}else {
					$sima="1010-2204000";
				}
			  				
				if($bijak>0){
				$nominal=$nomi+$saldo_tab+$bijak;
				$jurnal=db_fetch_array(db_query($koneksi,"SELECT max(nomor_jurnal) FROM jurnal_umum where unit='$unit' ORDER BY tanggal_selesai DESC"));
				$nomor=$jurnal[0]+1;
				$trns="BU/".$unit .$nomor;
				$keterangan_transaksi="Break Penempatan Dana Norek $norek";
				$query=db_query($koneksi,"insert into jurnal_umum(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values('$trns','$kode_transaksi','$buss_date','$unit')");
				$deb_dep=db_query($koneksi,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
				values('$unit','$trns','1010-2851000','$perode_bulan','Bukti System','$keterangan_transaksi','$nominal','$userid')");
				$cr_dep=db_query($koneksi,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
				values('$unit','$trns','$sima','$perode_bulan','Bukti System','$keterangan_transaksi','$nominal','$userid')");
				$in_dep= db_query($koneksi,"insert  into dep_realisasi(id_realisasi,norek,cif,jumlah_bayar,tgl_bayar,tgl_jatuh_tempo,norek_tujuan,ket,status)VALUES('$norek','$norek','$cif','$nominal','$tgl','$maturity_date','$norek_tujuan','EVVI','BREAK')");
				
				
				$deb_dep=db_query($koneksi,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
				values('$unit','$trns','1010-41001','$perode_bulan','Bukti System','$keterangan_transaksi','$gross','$userid')");
				$cr_dep=db_query($koneksi,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
				values('$unit','$trns','1010-2852000','$perode_bulan','Bukti System','$keterangan_transaksi','$bijak','$userid')");
				
				$cr_dep=db_query($koneksi,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
				values('$unit','$trns','1010-2423000','$perode_bulan','Bukti System','$keterangan_transaksi','$pajak','$userid')");
				$in_dep= db_query($koneksi,"insert  into dep_realisasi(id_realisasi,norek,cif,jumlah_bayar,tgl_bayar,tgl_jatuh_tempo,norek_tujuan,ket,status)VALUES('$norek','$norek','$cif','$bijak','$tgl','$maturity_date','$norek_tujuan','EVVI','BREAK')");	
					
				}else{
							
				//$nominal=$nomi+$saldo_tab+$bijak;
				
				$jurnal=db_fetch_array(db_query($koneksi,"SELECT max(nomor_jurnal) FROM jurnal_umum where unit='$unit' ORDER BY tanggal_selesai DESC"));
				$nomor=$jurnal[0]+1;
				$trns="BU/".$unit .$nomor;
				$keterangan_transaksi="Break Penempatan Dana Norek $norek";
				$query=db_query($koneksi,"insert into jurnal_umum(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values('$trns','$kode_transaksi','$buss_date','$unit')");
				
				$cr_dep=db_query($koneksi,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
				values('$unit','$trns','$sima','$perode_bulan','Bukti System','$keterangan_transaksi','$nomi','$userid')");

$deb_dep=db_query($koneksi,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
				values('$unit','$trns','1010-2852000','$perode_bulan','Bukti System','$keterangan_transaksi','$nomi','$userid')");

				$in_dep= db_query($koneksi,"insert  into dep_realisasi(id_realisasi,norek,cif,jumlah_bayar,tgl_bayar,tgl_jatuh_tempo,norek_tujuan,ket,status)VALUES('$norek','$norek','$cif','$nomi','$tgl','$maturity_date','$norek_tujuan','EVVI','BREAK')");
				}
				
				if(($in_dep)||($query)||($cr_dep)||($deb_dep)){
					echo '<div class="alert alert-success alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>Data Berhasil Di Simpan.</div>';
					$del1= db_query($koneksi, "insert into audit_dep_realisasi select  * from dep_realisasi WHERE norek='$norek'");
					$delete_realisasi= db_query($koneksi, "delete FROM dep_break WHERE norek='$norek'");
					}else{
						echo '<div class="alert alert-danger alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>Ups, Data Gagal Di simpan !</div>';
						}
			}
				
			
			?>
            
            <blockquote>
            Input Data
            </blockquote>
                         <form name="form1" id="form1" class="form-horizontal row-fluid" action="input_break.php" method="POST" >
										<div class="control-group">
											<label class="control-label" for="basicinput">Kode Transaksi</label>
											<div class="controls">
												<input type="text" name="norek" id="norek" placeholder="Tidak perlu di isi" value="<?php echo $code; ?>" class="form-control span8 tip" readonly="readonly">
											</div>
										</div>

										<div class="control-group">
											<label class="control-label" for="basicinput">Tanggal Break</label>
											<div class="controls">
												<input name="bulan" id="bulan" class="input-group date form-control span8 tip" type="text"  date="" placeholder="yyyy/mm/dd" required />
											</div>
										</div>
										<div class="control-group">
											<label class="control-label" for="basicinput">Margin Kebijakan </label>
											<div class="controls">	
												<input type="text" name="nominal" id="nominal" placeholder="Nominal Margin Kebijakan" class="form-control span8 tip" required>
											<div class="checkbox"> <input type="checkbox"  name="check1" value="" checked > Include Simpanan </div>
											</div>
										</div>
										</div>
										
										<div class="control-group">
											<div class="controls">
												<button type="submit" name="input" id="input" class="btn btn-sm btn-primary">Bayar</button>
                                               <a href="../view/view_break.php" class="btn btn-sm btn-danger">Kembali</a>
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
						url :"../view/break_view.php", // json datasource
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
