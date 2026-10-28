<?php require_once __DIR__ . '/../../php8_compat.php';
session_start();
ob_start();
$userid=$_SESSION['userid'];
include "../../config.php";
$buss_date = date("y-m-d");
$tgl=$_GET['jatuh'];
if(!isset($_SESSION['userid'])){
   header("location:../../index.html");//jika belum login jangan lanjut..
 exit; }


// Fungsi untuk menampilkan progress bar
include "../../conn2.php";
include "../../indo_tgl.php";
$koneksi=open_connection2();
$data = mysql_query ("select kode_branch from user where userid ='$userid'");
$r = mysql_fetch_array($data);
$unit=$r['kode_branch'];

$jurnal_keluar=mysql_fetch_array(mysql_query("SELECT max(nomor_jurnal) FROM jurnal_umum where unit ='$unit' ORDER BY tanggal_selesai DESC"));
			$nomor_jurnal=$jurnal_keluar[0]+1;
			$kode_transaksi="KK/".$unit .$nomor_jurnal;
function set_progress($val=0){

	$data = "<div class='progress-container' style='display:none'>
			
				<div class='progress'>
					  <div class='progress-bar progress-bar-info progress-bar-striped active' role='progressbar' aria-valuenow='0' aria-valuemin='0' aria-valuemax='100' style='width: ". $val. "%'>
					  </div>
				</div>

			</div>";

	return $data;

}

?>
<!DOCTYPE html>
<html>
<style>
	#result {
		height:20px;
		font-size:12px;
		font-family:Arial, Helvetica, sans-serif;
		color:#333;
		padding:5px;
		margin-bottom:10px;
		background-color:#FFFF99;
	}
	#country{
		padding:3px;
		border:1px #CCC solid;
		font-size:12px;
	}
	.suggestionsBox {
		position: absolute;
		left: 20px;
		top:280px;
		margin: 26px 0px 0px 0px;
		width: 400px;
		padding:0px;
		background-color:#669900;
		border-top: 3px solid #669900;
		color: #fff;
	}
	.suggestionList {
		margin: 0px;
		padding: 0px;
	}
	.suggestionList ul li {
		list-style:none;
		margin: 0px;
		padding: 6px;
		border-bottom:1px dotted #666;
		cursor: pointer;
	}
	.suggestionList ul li:hover {
		background-color: #b3b3ff;
		color:#000;
	}
	ul {
		font-family:Arial, Helvetica, sans-serif;
		font-size:11px;
		color:#FFF;
		padding:0;
		margin:0;
	}
	
	.load{
	background-image:url(loader.gif);
	background-position:right;
	background-repeat:no-repeat;
	}
	
	#suggest {
		position:relative;
	}
	</style>
	<head>
		<title>Monitoring</title>
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
		<meta charset="utf-8">

		<link href="../../css/bootstrap.min.css" rel="stylesheet">	
		<link href="../../css/font-awesome.min.css" rel="stylesheet">	
	<link href="../../css/style.css" rel="stylesheet">	

		<script src="../../js/jquery.min.js"></script>
		<script src="../../js/bootstrap.min.js"></script>
		<script src="../../js/jquery.form.js"></script>

	    <style type="text/css">
<!--
.style4 {color: #F0F0F0}
-->
        </style>
	</head>

<body>

	<section class="container">
	
		<h2><center></center></h2>
		<hr />
	
		<div class="col-md-offset-0 col-md-10 col-md-offset-1">

			<b>Monitoring Daily Disbursement</b>
			
			
			<table class="table table-bordered" >
				<tr>
					<th><span class="badge">NOREK</span></th>
					<th><span class="badge">NAMA</span></th>
					<th><span class="badge">NOMINAL </span></th>
<th><span class="badge">TGL KONTRAK</span></th>

					<th><span class="badge">TGL JATUH TEMPO</span></th>
<th><span class="badge">TENOR</span></th>
					<th><span class="badge">BAGI HASIL</span></th>
					<th><span class="badge">STATUS</span></th>
					<th style="text-align:center"><span class="badge">DETAIL</span></th>
				</tr>

				<?php $sql = $conn->query("select dep_realisasi.norek,CUST_SHORT_NAME,jumlah_bayar as nominal,tgl_bayar,tgl_jatuh_tempo,tenor,jumlah_bayar,status from dep_realisasi
				left join deposan on  deposan.cif = dep_realisasi.cif
				left join deposito on deposito.norek=dep_realisasi.norek 
				"); ?>

					<?php while ( $r = $sql->fetch_assoc() ) { 

					if ($r['status']=='PENEMPATAN'){
						$bayar=0;
						$ket='PENEMPATAN';

					}

					elseif ($r['status']=='BREAK'){
						$ket="PEMBAYARAN BREAK PENEMPATAN $norek";
					
					}else{
						$bayar =$r['nominal'];
						$ket='BAGI HASIL';
					}
$tanggal=$r['tgl_jatuh_tempo'];
					?>

						<tr>
							<td><?php echo $r['norek'] ?></td>
							<td><?php echo $r['CUST_SHORT_NAME'] ?></td>
							<td><?php echo number_format (compat_num($r['nominal'])) ?></td>
<td><?php echo tgl_indo($r['tgl_jatuh_tempo']) ?></td>							
<td><?php echo tgl_indo($r['tgl_jatuh_tempo']) ?></td>
<td><?php echo $r['tenor'] ?></td>
							
							<td><?php echo number_format (compat_num($bayar)) ?></td>
							<td><?php echo $r['status'] ?></td>
							<td style="text-align:center;width:160px">

								<a  class="btn btn-xs btn-warning" href="javascript:;"
									data-id="<?php echo $r['norek'] ?>"
									data-cif="<?php echo $kode_transaksi ?>"
									data-code_kel="<?php echo $r['CODE_KEL'] ?>"
									data-nama="<?php echo $r['CUST_SHORT_NAME'] ?>"
									data-jatuh="<?php echo $r['tgl_bayar'] ?>"
									data-nomi="<?php echo $r['nominal'] ?>"
									data-status="<?php echo $r['status'] ?>"
									data-toggle="modal" data-target="#edit-data">

									<i class="fa fa-pencil"></i> Disburse

								</a>

								
							</td>
						</tr>

				<?php } ?>

			</table>

			<!-- Pesan jika telah melakukan aksi -->
			<?php if ( isset( $_SESSION['info'] ) ) { ?>

				<div style="width:320px;background:#eee;border-left:5px solid #46b8da;padding:10px;"> 
					Berhasil <?php echo $_SESSION['info'] ?> Data
				</div>

			<?php unset( $_SESSION['info'] ); } ?>

		</div>

	</section>



	<!-- Modal tambah data -->
	<div id="tambah-data" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">

				<form id="form-data" method="post" action="engine_update.php?p=tambah">

					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title">Tambah Data</h4>
					</div>

					<div class="modal-body">

						  <fieldset>

								  </fieldset>

						<?php echo set_progress(); ?>

					</div>

					<div class="modal-footer">
						<button class="btn btn-info btn-submit"><i class="fa fa-save"></i> Simpan</button>
						<button class="btn btn-default" data-dismiss="modal"><i class="fa fa-close"></i> Batal</button>
					</div>

				</form>

			</div>
		</div>
	</div>

	<!-- Modal edit data -->
	<div id="edit-data" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">

				<form id="form-data" method="post" action="../transaksi/engine_update_dep.php?p=update">

					<input type="hidden" name="id" id="id">
					

					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title">Posting Ke Jurnal</h4>
					</div>
					
					<div class="modal-body">
				
						  <fieldset>
							<tr>
						    <div class="form-group">
						         <td><label for="norek">ID TRANSAKSI</label></td>
						     <td> <input type="text"  name="norek" id="norek"  value="<?php echo $kode_transaksi; ?>" class="form-control" placeholder="" size="10" maxlength="6"/></td>
						    </div>
							 <div class="form-group">
						       <td><label for="jatuh">TANGGAL</label></td>
						    <td> <input type="text"  name="jatuh" id="jatuh" value="<?php echo $tanggal; ?>" class="input-group date form-control"  date="" placeholder="0000-00-00" size="20" maxlength="6"/></td>
						    </div>
							</tr>
							<tr>
							  <div class="form-group">
						     <td><label for="ket">KETERANGAN JURNAL</label></td>
						      <td><input type="text" name="ket" id="ket" class="form-control" value="<?php echo $ket; ?> " placeholder="Masukkan KODE AO" size="20" maxlength="50"/></td>
						    </div>
							 <div class="form-group">
						     <td> <label for="code">REKENING</label></td> 
						    <input type="text" onKeyUp="suggest(this.value);" name="code"  onBlur="fill2();" id="kode" size="15" class="form-control span8 tip"> 
											<div class="suggestionsBox" id="suggestions" style="display: none;"> <img src="arrow.png" style="position: relative; top: -12px; left: 30px;" alt="upArrow">
											<div class="suggestionList" id="suggestionsList"> 
											</div>
											</div>
						    </div>
							</tr>
							<tr>
						    <div class="form-group"> 
						    <td><label for="nomi">JUMLAH</label></td>
						    <td><input type="text" name="nomi" id="nomi" class="form-control"><td>
					
							</tr>
					
						  </fieldset>
						
						<?php echo set_progress(); ?>
						
					</div>

					<div class="modal-footer">
						<button class="btn btn-info btn-submit"><i class="fa fa-save"></i> Simpan</button>
						<button class="btn btn-default" data-dismiss="modal"><i class="fa fa-close"></i> Batal</button>
					</div>

				</form>

			</div>
		</div>
	</div>

	<!-- modal konfirmasi-->
	<div id="modal-konfirmasi" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">

				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title">Konfirmasi</h4>
				</div>

				<div class="modal-body" style="background:#d9534f;color:#fff">
					Anda tidak bisa mengakses menu ini, silahkan hubungi atasan anda,..
				</div>

				<div class="modal-footer">
					<a href="javascript:;" class="btn btn-danger" id="hapus-true">Ya</a>
					<button type="button" class="btn btn-default" data-dismiss="modal">Tidak</button>
				</div>

			</div>
		</div>
	</div>

	<!-- Modal peringatan jika field belum terisi sempurna -->
	<div id="modal-peringatan" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-sm modal-warning">
			<div class="modal-content">

				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title" id="title">Peringatan</h4>
				</div>

				<div class="modal-body" style="background: #d9534f; color: #fff;">
					<div id="pesan-required-field"></div>
				</div>

				<div class="modal-footer">

					<center><button type="button" class="btn btn-default" data-dismiss="modal">OK</button></center>

				</div>

			</div>
		</div>
	</div>

<script>

	// Fungsi untuk pengiriman form baca dokumentasinya di https://github.com/malsup/form/
	function set_ajax(identifier){
		
		var options = {
			beforeSend: function() {

				$(".progress-container").show();
				$(".btn-submit").attr("disabled",""); // Membuat button submit jadi tidak bisa terklik
			 
			},
			uploadProgress: function(event, position, total, percentComplete) {

				$(".progress-bar").attr('style','width'+percentComplete+'%');

			},
			success:function(data, textStatus, jqXHR,ui) {

				if ( data.trim() == "Sukses" ) {

					$(".progress-bar").attr('style','width:100%');
					setTimeout(function(){ location.reload() }, 2000);

				} else {

					$(".progress-container").hide();
					$("#pesan-required-field").html(data);
					$("#modal-peringatan").modal('show');
					$(".btn-submit").removeAttr('disabled','');
				}

			},
			error: function(jqXHR, textStatus, errorThrown){

				$(".progress-container").hide();
				$("#pesan-required-field").html('Gagal Memproses data<br/> textStatus='+textStatus+', errorThrown='+errorThrown);
				$("#modal-peringatan").modal('show');
			}
		 
		};
		 
		// kirim form dengan opsi yang telah dibuat diatas
		$("#"+identifier).ajaxForm(options);
	}

	$(function(){

		// Untuk pengiriman form tambah
		set_ajax('tambah-data');

		// Untuk pengiriman form sunting
		set_ajax('edit-data');

		// Hapus
		$('#modal-konfirmasi').on('show.bs.modal', function (event) {
			var div = $(event.relatedTarget) // Tombol dimana modal di tampilkan

			// Untuk mengambil nilai dari data-id="" yang telah kita tempatkan pada link hapus
			var id = div.data('id') 

			var modal = $(this)

			// Mengisi atribut href pada tombol ya yang kita berikan id hapus-true pada modal.
			modal.find('#hapus-true').attr("href","transaksi/engine_update_dep.php?p=hapus&id="+id); 

		});


		// Untuk sunting
		$('#edit-data').on('show.bs.modal', function (event) {
			var div = $(event.relatedTarget) // Tombol dimana modal di tampilkan

			var id 			= div.data('id');
			var norek 		= div.data('norek');
			var jatuh 		= div.data('jatuh');
			var code_kel	= div.data('code_kel');
			var nomi 		= div.data('nomi');
			var inn 		= div.data('inn');
			
			var modal 	= $(this)

			// Isi nilai pada field
			modal.find('#id').attr("value",id);
			modal.find('#norek').attr("value",norek);
			modal.find('#jatuh').attr("value",jatuh);
			modal.find('#code_kel').attr("value",code_kel);
			modal.find('#nomi').attr("value",nomi);
			modal.find('#inn').attr("value",inn);
			
			// Membuat combobox terpilih berdasarkan jenis kelamin yg tersimpan saat pengeditan
	
		});

	});

</script>
<script src="../../js/bootstrap-datepicker.js"></script>
	<script>
	$('.date').datepicker({
		format: 'yyyy-mm-dd',
	})
	</script>
	
	
	<script type="text/javascript">
       
	function suggest(inputString){
		if(inputString.length == 0) {
			$('#suggestions').fadeOut();
		} else {
		$('#country').addClass('load');
			$.post("../autosuggest_kp.php", {queryString: ""+inputString+""}, function(data){
				if(data.length >0) {
					$('#suggestions').fadeIn();
					$('#suggestionsList').html(data);
					$('#country').removeClass('load');
				}
			});
		}
	}
	
	function fill(thisValue) {
		$('#country').val(thisValue);
		setTimeout("$('#suggestions').fadeOut();", 100);
	}
	
	function fill2(thisValue) {
		$('#kode').val(thisValue);
		setTimeout("$('#suggestions').fadeOut();", 100);
	}
	
	</script>
</body>
</html>
