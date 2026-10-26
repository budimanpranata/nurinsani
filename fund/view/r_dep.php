<?php require_once __DIR__ . '/../../php8_compat.php'; 
session_start();
ob_start();
		$userid=$_SESSION['userid'];
include "../conn.php";
$norekk=$_GET['kd'];
	$today=date("Y-m-d");
	$buss_date=$today;
	$k = db_query ($koneksi,"select kode_branch AS unit FROM user where userid='$userid'");
			  $kbranch = db_fetch_array($k);
			  $unit = $kbranch['unit'];
$dcountt = db_query ($koneksi,"select * from deposito where norek ='$norekk'");
						$hitt = db_fetch_array($dcountt);
								$nominal = $hitt['nominal'];
								$cif=$hitt['cif'];
								$tenor=$hitt['tenor'];
								$jenis_simpanan=$hitt['jenis_simpanan'];
								$aro=$hitt['aro'];
								$rasio=$hitt['rasio'];
								$nomi=$hitt['nominal'];
								$eq_rate=$hitt['eq_rate'];
	
			 
			 
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
                        <h3 class="panel-title"><i class="icon-user"></i>Realisasi Jatuh Tempo</h3> 
                        </div>
                        <div class="panel-body">
                            <?php
			if(isset($_POST['input'])){
				$norek=$_POST['norek'];
				$perode_bulan  = $_POST['bulan'];
				$jenis   = $_POST['jenis'];
				$jenis_aro=$_POST['jenis_aro'];
				$nisbah   = $_POST['nisbah'];
				$eq_rate   = $_POST['eq_rate'];
				$baghas   = $_POST['baghas'];
				$jenis_break   = $_POST['jenis_break'];
				$add_baghas= $_POST['add_baghas'];
						  
			$dcount = db_query ($koneksi,"select * from deposito where norek ='$norek'");
						$hit = db_fetch_array($dcount);
								$nominal = $hit['nominal'];
								$cif=$hit['cif'];
								$tenor=$hit['tenor'];
								$jenis_simpanan=$hit['jenis_simpanan'];
								$aro=$hit['aro'];
								$rasio=$hit['rasio'];
								$suffix=$hit['suffix'];
								$suffix=$suffix+1;
								$nomi=$hit['nominal'];
								$eq_rate=$hit['eq_rate'];
				
				//if ($tenor =='6'){
                    //$sima="1010-2203000";
                //}elseif ($tenor =='3') {
                    //$sima="1010-2202000";
                //}else {
                    //$sima="1010-2204000";
                //}


 if ($tenor =='6'){
                    $sima="1010-2203000";
                }elseif ($tenor =='3') {
                    $sima="1010-2202000";
                }elseif($tenor=='12') {
                    $sima="1010-2204000";
                }elseif($tenor =='1'){
		$sima ="1010-2201000";
		}else{
		$sima = "1010-2206000";
		
		}				
				
				//Create Kode Transaksi
				$jurnal=db_fetch_array(db_query($koneksi,"SELECT max(nomor_jurnal) FROM jurnal_umum where unit='$unit' ORDER BY tanggal_selesai DESC"));
				$nomor=$jurnal[0]+1;
				$trns="BU/".$unit .$nomor;
				
				if ($jenis =='PK'){
				$query=db_query($koneksi,"insert into jurnal_umum(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values('$trns','$kode_transaksi','$buss_date','$unit')");
				
				//debet dari smka kredit ke titipan
				$cr_dep=db_query($koneksi,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
					values('$unit','$trns','$sima','$buss_date','Bukti Umum','$keterangan_transaksi','$nomi','$userid')");
				$deb_dep=db_query($koneksi,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
					values('$unit','$trns','1010-2851000','$buss_date','Bukti Umum','$keterangan_transaksi','$nomi','$userid')");
				
				//debet dari titipan kredit ke simka
				$deb_dep=db_query($koneksi,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
					values('$unit','$trns','1010-2851000','$buss_date','Bukti Umum','$keterangan_transaksi','$nomi','$userid')");
				$cr_dep=db_query($koneksi,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
					values('$unit','$trns','$sima','$buss_date','Bukti Umum','$keterangan_transaksi','$nomi','$userid')");
				
				
				$maturity_date =  date ("Y-m-d", strtotime("+$tenor month", strtotime($perode_bulan)));
				$awal_hari = $perode_bulan;
				$akhir_hari = $maturity_date;
				$x=0;
				while (strtotime($awal_hari) < strtotime($akhir_hari)) {
				$x ++;
				$awal_hari= date ("Y-m-d", strtotime("+1 month", strtotime($awal_hari)));
				$detail= db_query($koneksi,"INSERT INTO deposito_detail (id,id_deposito,cif,ke,tgl_jatuh_tempo,tgl_bayar,jumlah_nisbah,rek_tujuan,ket) values('$d','$norek','$cif','$x','$awal_hari','$awal_hari','0','$norek','$next_schedule')") or die(mysqli_error());
					$update = db_query($koneksi, "update deposito set star_date='$awal_hari',contract_date ='$awal_hari',maturity_date='$maturity_date',suffix='$suffix',rasio='$add_baghas', status_print='ADVIS' where norek='$norek'");
					}
				
							
				}
				elseif($jenis=='BREAK'){
					if ($jenis_break=='bayar'){
				   	$update = db_query($koneksi, "insert into dep_break SELECT * FROM deposito where norek ='$norek' ");
					$update = db_query($koneksi, "update dep_break set maturity_date='$perode_bulan' where norek ='$norek' ");
					}
						else {
							
				
				$keterangan_transaksi="BREAK Dana Norek $norek";
				$query=db_query($koneksi,"insert into jurnal_umum(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values('$trns','$kode_transaksi','$buss_date','$unit')");
				
				$cr_dep=db_query($koneksi,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
					values('$unit','$trns','$sima','$buss_date','Bukti System','$keterangan_transaksi','$nomi','$userid')");
				$deb_dep=db_query($koneksi,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
					values('$unit','$trns','1010-2851000','$buss_date','Bukti System','$keterangan_transaksi','$nomi','$userid')");
								
				$update = db_query($koneksi, " insert into dep_titip select * from deposito where norek='$norek'");
				$update = db_query($koneksi, " delete from deposito where norek='$norek'");
				
				
				 $maturity_date =  date ("Y-m-d", strtotime("+$tenor month", strtotime($perode_bulan)));
				$awal_hari = $perode_bulan;
				$akhir_hari = $maturity_date;
				$x=0;
				while (strtotime($awal_hari) < strtotime($akhir_hari)) {
				$x ++;
				$awal_hari= date ("Y-m-d", strtotime("+1 month", strtotime($awal_hari)));
				$detail= db_query($koneksi,"INSERT DELAYED INTO deposito_detail (id,id_deposito,cif,ke,tgl_jatuh_tempo,tgl_bayar,jumlah_nisbah,rek_tujuan,ket) values('$d','$norek','$cif','$x','$awal_hari','$awal_hari','0','$norek','$next_schedule')") or die(mysqli_error());
					$update = db_query($koneksi, "update deposito set star_date='$awal_hari',contract_date ='$awal_hari',maturity_date='$maturity_date',suffix='$suffix',nominal='$nominal', status_print='ADVIS' where norek='$norek'");

			  
								
			}
			}
					

			
			}else{
			$dcount = db_query ($koneksi,"select * from deposito where norek ='$norek'");
			  $hit = db_fetch_array($dcount);
			  $nominal = $hit['nominal'];
			  $cif=$hit['cif'];
			  $tenor=$hit['tenor'];
			  $jenis_simpanan=$hit['jenis_simpanan'];
			  $aro=$hit['aro'];
			  $rasio=$hit['rasio'];
			  $q_rate=$hit['eq_rate'];


			  
			  $es = db_query ($koneksi,"select sum(KREDIT)-sum(DEBIT) as saldo from escrow_deposito where norek ='$norek'");
			  $data_saldo = db_fetch_array($es);
			  $saldo_aro=$data_saldo ['saldo'];
			  $new_nominal=$nominal+$saldo_aro;
			  
			  if ($enis_aro=='ARO-bulan'){
				  			  $maturity_date =  date ("Y-m-d", strtotime("+$tenor month", strtotime($perode_bulan)));
				$awal_hari = $perode_bulan;
				$akhir_hari = $maturity_date;
				$x=0;
				while (strtotime($awal_hari) < strtotime($akhir_hari)) {
				$x ++;
				$awal_hari= date ("Y-m-d", strtotime("+1 month", strtotime($awal_hari)));
				$detail= db_query($koneksi,"INSERT DELAYED INTO deposito_detail (id,id_deposito,cif,ke,tgl_jatuh_tempo,tgl_bayar,jumlah_nisbah,rek_tujuan,ket) values('$d','$norek','$cif','$x','$awal_hari','$awal_hari','0','$norek','$next_schedule')") or die(mysqli_error());
					

			  
								
			}
				  
				  $total_simka=$nominal;
				  $keterangan_transaksi="Perpanjangan Dana Norek $norek";
								
				
				  
				$update = db_query($koneksi, "update deposito set star_date='$perode_bulan',contract_date ='$perode_bulan',maturity_date='$maturity_date',suffix='$suffix',aro='ARO-bulan', status_print='ADVIS' where norek='$norek'");  
			  
			  
			  
			  }else{
				   $total_simka=$new_nominal;
				  $keterangan_transaksi="Perpanjangan Dana Norek $norek";
				$query=db_query($koneksi,"insert into jurnal_umum(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values('$trns','$kode_transaksi','$buss_date','$unit')");
				
				//debet dari smka kredit ke titipan
				$cr_dep=db_query($koneksi,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
					values('$unit','$trns','$sima','$buss_date','Bukti System','$keterangan_transaksi','$total_simka','$userid')");
				$deb_dep=db_query($koneksi,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
				values('$unit','$trns','1010-2851000','$buss_date','Bukti System','$keterangan_transaksi','$total_simka','$userid')");
				
				//debet dari titipan kredit ke simka
				$deb_dep=db_query($koneksi,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
					values('$unit','$trns','1010-2851000','$buss_date','Bukti System','$keterangan_transaksi','$total_simka','$userid')");
				$cr_dep=db_query($koneksi,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
					values('$unit','$trns','$sima','$buss_date','Bukti System','$keterangan_transaksi','$total_simka','$userid')");
				
				
				$update = db_query($koneksi, "update deposito set star_date='$perode_bulan',contract_date ='$perode_bulan',maturity_date='$maturity_date',suffix='$suffix', aro='ARO-jatpo', status_print='OK',eq_rate='$eq_rate',rasio='$nisbah',nominal='$new_nominal' where norek='$norek'");  
			 
			  }
			  
			  		


			
			}
				if($update){
						echo '<div class="alert alert-success alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>Data Berhasil Di Simpan.</div>';
					
						}else{
							echo '<div class="alert alert-danger alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>Ups, Data Gagal Di simpan !</div>';
						}
			
			
			}
			?>
            
            <blockquote>
            Input Data
            </blockquote>
                         <form name="form1" id="form1" class="form-horizontal row-fluid" action="r_dep.php" method="POST" >
										<div class="control-group">
											<label class="control-label" for="basicinput">Kode Rekening</label>
											<div class="controls">
												<input type="text" name="norek" id="norek" placeholder="Tidak perlu di isi" value="<?php echo $norekk; ?>" class="form-control span8 tip" readonly="readonly">
											</div>
										</div>

										<div class="control-group">
											<label class="control-label" for="basicinput">Tanggal Realisasi</label>
											<div class="controls">
												<input name="bulan" id="bulan" class="input-group date form-control span8 tip" type="text"  date="" placeholder="yyyy/mm/dd" required />
											</div>
										</div>
										<div class="control-group">
										<label class="control-label" for="basicinput">Jenis Realisasi</label>
											<div class="controls">
											<select name="jenis" id ="jenis" class="form-control span8 tip" required>
											<option value="">Jenis Realisasi</option>
											<option value="PK">Aro</option>
											<option value="BREAK">Break</option>
											<option value="ARO-ARO">ARO-ARO</option>
											
											</select> 
											</div>
											</div>
										<div class="control-group">
											<label class="control-label" for="basicinput">Adjust Baghas</label>
											<div class="controls">
												<input type="text" name="add_baghas" id="add_baghas" placeholder="Isi Baghas" value="<?php echo $rasio; ?>" class="form-control span8 tip">
											</div>
										</div>



											<div class="control-group">
											<div id="tombol_break" class="hasil_break">
											<label class="control-label" for="basicinput">Jenis Break</label>
											<div class="controls">
											<select name="jenis_break" id ="jenis_break" class="form-control span8 tip" >
											<option value="">Pilih Jenis Break</option>
											<option value="bayar">Bayarkan</option>
											<option value="titip">Titipan</option>
											
 								
											</select> 											
										</div>
										</div>
										</div>
										
																			
											<div class="control-group">
											<div id="tombol" class="hasil">
											<label class="control-label" for="basicinput">Jenis ARO</label>
											<div class="controls">
											<select name="jenis_aro" id ="jenis_aro" class="form-control span8 tip" >
											<option value="">Pilih ARO</option>
											<option value="ARO_bulan">Bulan</option>
											<option value="ARO_jatpo">Jatuh Tempo</option>
											
 								
											</select> 											
										</div>
										</div>
										</div>
										<div class="control-group">
										<div id="tombol_nomina" class="hasil_nominal">
											<label class="control-label" for="basicinput">Nominal</label>
											<div class="controls">
												<input type="text" name="new_nomi" id="new_nomi" placeholder="Isi Nominal" value="<?php echo $nomi; ?>" class="form-control span8 tip">
											</div>
										</div>
										</div>
											
										<div class="control-group">
										<div id="tombol_nisbah" class="hasil_nisbah">
											<label class="control-label" for="basicinput">Nisbah</label>
											<div class="controls">
												<input type="text" name="bisbah" id="nisbah" placeholder="Isi Nisbah" value="<?php echo $rasio; ?>" class="form-control span8 tip">
											</div>
										</div>	
										</div>
										<div class="control-group">
										<div id="tombol_eq" class="hasil_eq">
											<label class="control-label" for="basicinput">Eq Rate</label>
											<div class="controls">
												<input type="text" name="eq_rate" id="eq_rate" placeholder="Isi Eq Rate" value="<?php echo $eq_rate; ?>" class="form-control span8 tip">
											</div>
										</div>
										</div>
										
										<div class="control-group">
										<div id="tombol_baghas" class="hasil_baghas">
											<label class="control-label" for="basicinput">Total Baghas</label>
											<div class="controls">
												<input type="text" name="baghas" id="baghas" placeholder="Isi Total Baghas" value="<?php echo $tot_baghas; ?>" class="form-control span8 tip">
											</div>
										</div>
										
										
										<div class="control-group">
											<div class="controls">
												<button type="submit" name="input" id="input" class="btn btn-sm btn-primary">Simpan</button>
                                               <a href="../view/view_tempo.php" class="btn btn-sm btn-danger">Kembali</a>
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
						url :"../view/view_tempo.php", // json datasource
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
	<script>
$(document).ready(function(){
$('.hasil').hide();
$('#tombol').hide();

$('.hasil_nisbah').hide();
$('#tombol_nisbah').hide();

$('.hasil_eq').hide();
$('#tombol_eq').hide();

$('.hasil_baghas').hide();
$('#tombol_baghas').hide();

$('.hasil_nominal').hide();
$('#tombol_nominal').hide();

$('.hasil_break').hide();
$('#tombol_break').hide();


$('#jenis').change(function(){
if($('#jenis').val()=='PK'){
$('.hasil').hide();	
$('#tombol').hide();
$('#mitra').hide();
}else if($('#jenis').val()=='BREAK'){
$('.hasil').hide();
$('#tombol').hide();
$('#mitra').hide();

$('.hasil_nisbah').hide();
$('#tombol_nisbah').hide();

$('.hasil_eq').hide();
$('#tombol_eq').hide();

$('.hasil_baghas').hide();
$('#tombol_baghas').hide();

$('.hasil_nominal').hide();
$('#tombol_nominal').hide();

$('.hasil_break').show();
$('#tombol_break').show();


}else if($('#jenis').val()=='ARO-ARO'){
$('.hasil').show();
$('#tombol').show();
$('#mitra').show();
$('.hasil_break').hide();
$('#tombol_break').hide();
}
});
});

$(document).ready(function(){
$('.hasil_nisbah').hide();
$('#tombol_nisbah').hide();

$('.hasil_eq').hide();
$('#tombol_eq').hide();

$('.hasil_baghas').hide();
$('#tombol_baghas').hide();

$('.hasil_nominal').hide();
$('#tombol_nominal').hide();

$('.hasil_break').hide();
$('#tombol_break').hide();

$('#jenis_aro').change(function(){
if($('#jenis_aro').val()=='ARO_bulan'){
$('.hasil_baghas').hide();	
$('.hasil_eq').hide();	
$('.hasil_nisbah').hide();	
$('.hasil_nominal').hide();	
$('.hasil_break').hide();
$('#tombol_break').hide();

}else if($('#jenis_aro').val()=='ARO_jatpo'){
$('.hasil_baghas').show();
$('#tombol_baghas').show();
$('.hasil_eq').show();
$('#tombol_eq').show();
$('.hasil_nisbah').show();
$('#tombol_nisbahs').show();
$('.hasil_nominal').show();
$('#tombol_nominal').show();
$('.hasil_break').hide();
$('#tombol_break').hide();


}
});
});
</script>
 </body>
