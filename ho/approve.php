<?php require_once __DIR__ . '/../php8_compat.php';
session_start();
ob_start();
$limit=10;
$userid=$_SESSION['userid'];
include "config.php";
$buss_date = date("y-m-d");
if(!isset($_SESSION['userid'])){
   header("location:../index.html");//jika belum login jangan lanjut..
}

// Fungsi untuk menampilkan progress bar
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
	<head>
		<title>Sales Feedback</title>
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
		<meta charset="utf-8">

		<link href="css/bootstrap.min.css" rel="stylesheet">	
		<link href="css/font-awesome.min.css" rel="stylesheet">	
	<link href="css/style.css" rel="stylesheet">	

		<script src="js/jquery.min.js"></script>
		<script src="js/bootstrap.min.js"></script>
		<script src="js/jquery.form.js"></script>

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
	
		<div class="col-md-offset-1 col-md-10 col-md-offset-1">

			<b>DATA RENCANA KUNJUNGAN</b>
			
			
			<table class="table table-bordered" >
				<tr>
					<th><span class="badge">NAMA SALES</span></th>
					<th><span class="badge">TANGGAL KUNJUNGAN</span></th>
					<th><span class="badge">NOPEN</span></th>
					<th><span class="badge">NAMA</span></th>
					<th><span class="badge">ALAMAT</span></th>
					<th><span class="badge">KOTA</span></th>
					<th><span class="badge">KELURAHAN</span></th>
					<th><span class="badge">FEED BACK</span></th>
<th><span class="badge">KETERANGAN</span></th>
<th><span class="badge">APPROVE</span></th>
					<th style="text-align:center"><span class="badge">DETAIL</span></th>
				</tr>

				<?php $sql = $conn->query("Select NOPEN,feed_back.NAMA as NAMA,TGL_KUNJUNGAN,person.nama as NAMA1,feed_back.ALAMAT As ALAMAT,KOTA,LURAH, FEEB_BACK, KET, APPROVE
 From feed_back
 INNER JOIN person on person.NIK= feed_back.KODE_SALES
 WHERE person.NIK_atasan='$userid' AND APPROVE in ('PENDING','UPDATE')"); ?>

					<?php while ( $r = $sql->fetch_assoc() ) { ?>

						<tr><td><?php echo $r['NAMA1'] ?></td>
							<td><?php echo $r['TGL_KUNJUNGAN'] ?></td>
							<td><?php echo $r['NOPEN'] ?></td>							
							<td><?php echo $r['NAMA'] ?></td>
							<td><?php echo $r['ALAMAT'] ?></td>
							<td><?php echo $r['KOTA'] ?></td>
							<td><?php echo $r['LURAH'] ?></td>
							<td><?php echo $r['FEEB_BACK'] ?></td>
							<td><?php echo $r['KET'] ?></td>
							<td><?php echo $r['APPROVE'] ?></td>
							<td style="text-align:center;width:160px">

								<a  class="btn btn-xs btn-warning" href="javascript:;"
									data-nopen="<?php echo $r['NOPEN'] ?>"
									data-nama="<?php echo $r['NAMA'] ?>"
									data-tgl="<?php echo $r['TGL_LAHIR'] ?>"
									data-usia="<?php echo $r['USIA'] ?>"
									data-alamat="<?php echo $r['ALAMAT'] ?>"
									data-kota="<?php echo $r['KOTA'] ?>"
									data-kel="<?php echo $r['KELURAHAN'] ?>"
									data-bayar="<?php echo $r['GBAYAR'] ?>"
									data-toggle="modal" data-target="#edit-data">

									<i class="lebel-success"></i> Approve

								</a>

								<a class="btn btn-xs btn-danger" href="javascript:;" data-nopen="<?php echo $r['NOPEN']?>" data-toggle="modal" data-target="#modal-konfirmasi"><i class="alert-success"></i>Clear dari Feedback</a>
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

				<form id="form-data" method="post" action="engine_aprove.php?p=tambah">

					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title">Tambah Data</h4>
					</div>

					<div class="modal-body">

						  <fieldset>

				   		    <div class="form-group">
						      <label for="nopen">NOPEN</label>
						      <input type="text" name="nopen" class="form-control" placeholder="Masukkan NOPEN">
						    </div>
							 <div class="form-group">
						      <label for="kun">TANGGAL KUNJUNGAN</label>
						      <input type="text" name="kun" class="form-control" placeholder="Masukkan TANGGAL KUNJUNGAN">
						    </div>

						    <div class="form-group">
						      <label for="kds">KODE SALES</label>
						      <input type="text" name="kds" class="form-control" placeholder="Masukkan KODE_SALES">
						    </div>

						    <div class="form-group">
						      <label for="inn">TIME IN</label>
						      <input type="text" name="inn" class="form-control" placeholder="Masukkan TIME IN">
						    </div>

						    <div class="form-group">
						      <label for="out">TIME OUT</label>
						      <input type="text" name="out" class="form-control" placeholder="Masukkan TIME OUT">
						    </div>
 							 <div class="form-group">
						      <label for="nama">NAMA </label>
						      <input type="text" name="nama" class="form-control" placeholder="Masukkan NAMA">
						    </div>
							 <div class="form-group">
						      <label for="alamat">ALAMAT</label>
						      <input type="text" name="alamat" class="form-control" placeholder="Masukkan ALAMAT">
						    </div>
							 <div class="form-group">
						      <label for="lurah">LURAH</label>
						      <input type="text" name="lurah" class="form-control" placeholder="Masukkan KELURAHAN">
						    </div>
							 <div class="form-group">
						      <label for="out">KOTA</label>
						      <input type="text" name="kota" class="form-control" placeholder="Masukkan KOTA">
						    </div>
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

				<form id="form-data" method="post" action="engine_approve.php?p=update">

					<input type="hidden" name="nopen" id="nopen">

					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title">APPROVE Data Kunjungan</h4>
					</div>

					<div class="modal-body">

						  <fieldset>
					 				  
						    <div class="form-group">
						      <label for="approve">APPROVE</label>
						      <input type="text" name="approve" id="approve" class="form-control"  placeholder="STATUS APPROVE">
						    </div>
		    
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
					Data akan di kelurakan dari sales feedback ?
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
			var nopen = div.data('nopen') 

			var modal = $(this)

			// Mengisi atribut href pada tombol ya yang kita berikan id hapus-true pada modal.
			modal.find('#hapus-true').attr("href","engine_approve.php?p=hapus&nopen="+nopen); 

		});


		// Untuk sunting
		$('#edit-data').on('show.bs.modal', function (event) {
			var div = $(event.relatedTarget) // Tombol dimana modal di tampilkan

			var nopen 		= div.data('nopen');
			var approve 	= div.data('approve');
			
			var modal 	= $(this)

			// Isi nilai pada field
			modal.find('#nopen').attr("value",nopen);
			modal.find('#approve').attr("value",approve);
			
			// Membuat combobox terpilih berdasarkan jenis kelamin yg tersimpan saat pengeditan
			
			});

	});

</script>

</body>
</html>