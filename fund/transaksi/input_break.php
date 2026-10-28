<?php require_once __DIR__ . '/../../php8_compat.php'; 
session_start();
ob_start();
        $userid=$_SESSION['userid'];
        if(!isset($_SESSION['userid'])){
   header("location:../index.html");
 exit; }

include "../conn.php";
$code=$_GET['kd'];
	$today=date("Ymd");
	$buss_date=$today;
	$k = db_query ($koneksi,"select kode_branch AS unit FROM user where userid='$userid'");
			  $kbranch = db_fetch_array($k);
			  $unit = $kbranch['unit'];
	var_dump($code);		  
	$dcount = db_query ($koneksi,"select nominal,eq_rate,maturity_date,DATE_ADD(maturity_date, INTERVAL -1 Month),
	datediff(maturity_date,DATE_ADD(maturity_date, INTERVAL -1 Month)) as hari
	from dep_break where norek ='$code'");
			  $hit = db_fetch_array($dcount);
			  $nominal_break = $hit['nominal'];
			  $tgl_brek = $hit['maturity_date'];
			  $hari = $hit['hari'];
			  $eq_rate=$hit['eq_rate'];
			  $kali_eq=$eq_rate/100;
			  $bayar_rate=($nominal_break*$kali_eq)*$hari/365;
			  $gross_rate=$bayar_rate/0.9;
			  $pajak=$gross_rate-$bayar_rate;
		      $jlbayar_rate=floor(compat_num($gross_rate-$pph4_rate));
$saldo = db_query ($koneksi,"SELECT sum(KREDIT)-sum(DEBIT) as saldo FROM escrow_deposito where NOREK='$code' group by NOREK");
			  $total_saldo = db_fetch_array($saldo);
			  $saldo_tab = $total_saldo['saldo'];
if($saldo_tab>0){
$saldo_tab=$saldo_tab + $jlbayar_rate;
}else{
$saldo_tab=$saldo_tab;
}

			  
			  
			 
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
				 
				$bijak = str_replace ('.','',$_POST['nominal']);
				$jenis   = $_POST['jenis'];
				$norek   = $_POST['norek'];
				$simka = str_replace ('.','',$_POST['simka']);
				$nominal_break= $bijak + $simka;
				if($bijak=$jlbayar_rate){
				$pajak=$pajak;	
				}else{
				$gross=$bijak/90*100;
				$pajak=$gross-$bijak;	
				}
				
             
				$dcount = db_query ($koneksi,"select * from dep_break where norek ='$norek'");
			  $hit = db_fetch_array($dcount);
			  $nomi = $hit['nominal'];
			  $tenor = $hit['tenor'];
			  $cif= $hit['cif'];
			  
			  			  
			  
			  if ($tenor =='6'){
					$sima="1010-2203000";
				}elseif ($tenor =='3') {
					$sima="1010-2202000";
				}elseif($tenor =='12') {
					$sima="1010-2204000";
				}elseif($tenor =='1'){
					$sima ="1010-2201000";
				}else{
					$sima ="1010-2206000";				
				}
			  				
				if($bijak>0){
				$nominal=$nomi+$bijak;
				$jurnal=db_fetch_array(db_query($koneksi,"SELECT max(nomor_jurnal) FROM jurnal_umum where unit='$unit' ORDER BY tanggal_selesai DESC"));
				$nomor=$jurnal[0]+1;
				$trns="BU/".$unit .$nomor;
				$keterangan_transaksi="Break Penempatan Dana Norek $norek";
				$query=db_query($koneksi,"insert into jurnal_umum(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values('$trns','$kode_transaksi','$buss_date','$unit')");
				
				$cr_dep=db_query($koneksi,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
				values('$unit','$trns','$sima','$perode_bulan','Bukti System','$keterangan_transaksi','$nominal_break','$userid')");
	
				$deb_dep=db_query($koneksi,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
				values('$unit','$trns','1010-2851000','$perode_bulan','Bukti System','$keterangan_transaksi','$nominal_break','$userid')");
				
				
				
				$deb_dep=db_query($koneksi,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
				values('$unit','$trns','1010-41001','$perode_bulan','Bukti System','$keterangan_transaksi','$gross','$userid')");
				$cr_dep=db_query($koneksi,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
				alues('$unit','$trns','1010-2852000','$perode_bulan','Bukti System','$keterangan_transaksi','$bijak','$userid')");
				
				$cr_dep=db_query($koneksi,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
				values('$unit','$trns','1010-2423000','$perode_bulan','Bukti System','$keterangan_transaksi','$pajak','$userid')");
								
				$deb_dep=db_query($koneksi,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
				values('$unit','$trns','1010-41001','$perode_bulan','Bukti System','$keterangan_transaksi','$bijakb','$userid')");
				$cr_dep=db_query($koneksi,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
				values('$unit','$trns','1010-2102000','$perode_bulan','Bukti System','$keterangan_transaksi','$bijak','$userid')");

$input_es=db_query($konesi,"insert  into escrow_deposito (BUSS_DATE,NOREK,UNIT,CIF,DEBIT,TYPE,KREDIT,USERID,KET,REFF,kode_transaksi)VALUES('$perode_bulan','$norek','1010','$cif','0','03','$bijak','$userid','Bagi Hasil','','$trns')");	
				}else{
							
				$nominal=$nomi+$saldo_tab+$bijak;
				
				$jurnal=db_fetch_array(db_query($koneksi,"SELECT max(nomor_jurnal) FROM jurnal_umum where unit='$unit' ORDER BY tanggal_selesai DESC"));
				$nomor=$jurnal[0]+1;
				$trns="BU/".$unit .$nomor;
				$keterangan_transaksi="Break Penempatan Dana Norek $norek";
				$query=db_query($koneksi,"insert into jurnal_umum(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values('$trns','$kode_transaksi','$buss_date','$unit')");
				
				$cr_dep=db_query($koneksi,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
				values('$unit','$trns','$sima','$perode_bulan','Bukti System','$keterangan_transaksi','$nominal_break','$userid')");

				$deb_dep=db_query($koneksi,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
				values('$unit','$trns','1010-2852000','$perode_bulan','Bukti System','$keterangan_transaksi','$nominal_break','$userid')");
				
				
//jangan di ubah				
$in_dep= db_query($koneksi,"insert  into dep_realisasi(id_realisasi,norek,cif,jumlah_bayar,tgl_bayar,tgl_jatuh_tempo,norek_tujuan,ket,status)VALUES('$norek','$norek','$cif','$nominal_break','$perode_bulan','$maturity_date','$norek_tujuan','EVVI','BREAK')");
				}
				
				if(($in_dep)||($query)||($cr_dep)||($deb_dep)){
					echo '<div class="alert alert-success alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>Data Berhasil Di Simpan.</div>';
					$del1= db_query($koneksi, "insert into audit_dep_realisasi select  * from dep_realisasi WHERE norek='$norek'");
					$delete_realisasi= db_query($koneksi, "delete FROM dep_break WHERE norek='$norek'");
					}else{
						echo '<div class="alert alert-danger alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>Ups, Data Gagal Di simpan !</div>';
						}
			}
				
			
			?>b
            
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
												<input name="bulan" id="bulan" class="input-group date form-control span8 tip" type="text"  date="" placeholder="yyyy/mm/dd" value="<?php echo $tgl_brek;?>" required />
											</div>
										</div>
										<div class="control-group">
											<label class="control-label" for="basicinput">Margin </label>
											<div class="controls">	
												<input type="text" name="nominal" id="nominal" placeholder="Nominal Margin Kebijakan" value="<?php echo number_format (compat_num($jlbayar_rate), '0', ',', '.')?>"class="form-control span8 tip" required>
											
										</div>
										</div>
<div class="control-group">
											<label class="control-label" for="basicinput">Simpanan Umum </label>
											<div class="controls">	
												<input type="text" name="simpanan" id="nominal" placeholder="Nominal Margin Kebijakan" value="<?php echo number_format (compat_num($saldo_tab), '0', ',', '.')?>"class="form-control span8 tip" required>
											
										</div>
										</div>
<div class="control-group">
											<label class="control-label" for="basicinput">SIMKA </label>
											<div class="controls">	
												<input type="text" name="simka" id="nominal" placeholder="Nominal Margin Kebijakan" value="<?php echo number_format (compat_num($nominal_break), '0', ',', '.')?>"class="form-control span8 tip" required>
											
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
	<script type="text/javascript">

    /* Tanpa Rupiah */
    var nominal = document.getElementById('nominal');
    nominal.addEventListener('keyup', function(e)
    {
        nominal.value = formatRupiah(this.value);
    });
   
    /* Dengan Rupiah */
    var dengan_rupiah = document.getElementById('dengan-rupiah');
    dengan_rupiah.addEventListener('keyup', function(e)
    {
        dengan_rupiah.value = formatRupiah(this.value, 'Rp. ');
    });
   
    /* Fungsi */
    function formatRupiah(angka, prefix)
    {
        var number_string = angka.replace(/[^,\d]/g, '').toString(),
            split    = number_string.split(','),
            sisa     = split[0].length % 3,
            rupiah     = split[0].substr(0, sisa),
            ribuan     = split[0].substr(sisa).match(/\d{3}/gi);
           
        if (ribuan) {
            separator = sisa ? '.' : '';
            rupiah += separator + ribuan.join('.');
        }
       
        rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
        return prefix == undefined ? rupiah : (rupiah ? 'Rp. ' + rupiah : '');
    }

      </script>
    </body>
