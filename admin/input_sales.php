<?php require_once __DIR__ . '/../php8_compat.php'; require_once __DIR__ . '/cek_admin.php';
session_start();
ob_start();
$userid=$_SESSION['userid'];
include "config.php";
$buss_date = date("y-m-d");
$NIK_atasan=$_GET['sales'];
if(!isset($_SESSION['userid'])){
   header("location:../index.html");//jika belum login jangan lanjut..
 exit; }

//cek level user
if($_SESSION['level']!="admin"){
     header("location:../index.html");//jika bukan user jangan lanjut
 exit; }

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

			<b>DATA KARYAWAN </b>
			
			<button class="btn btn-xs pull-right" data-id='0' data-toggle="modal" data-target="#tambah-data">+ Add Data</button> <br /><br />
			
			<table class="table table-bordered" >
				<tr>
					<th><span class="badge">NIK</span></th>
					<th><span class="badge">NAMA</span></th>
					<th><span class="badge">ALAMAT</span></th>
					<th><span class="badge">TANGGAL LAHIR</span></th>
					<th><span class="badge">JABATAN</span></th>
					<th><span class="badge">NO TLP</span></th>
					<th><span class="badge">NIK ATASAN</span></th>
					<th><span class="badge">STATUS</span></th>
					<th style="text-align:center"><span class="badge">DETAIL</span></th>
				</tr>

				<?php $sql = $conn->query("Select NIK,nama,tgl_lahir,alamat,jabatan,NIK_atasan,No_tlp,tmt,gender,status from person where NIK_atasan='$NIK_atasan'"); ?>

					<?php while ( $r = $sql->fetch_assoc() ) { ?>

						<tr>
							<td><?php echo $r['NIK'] ?></td>							
							<td><?php echo $r['nama'] ?></td>
							<td><?php echo $r['alamat'] ?></td>
							<td><?php echo $r['tgl_lahir'] ?></td>
							<td><?php echo $r['jabatan'] ?></td>
							<td><?php echo $r['No_tlp'] ?></td>
							<td><?php echo $r['NIK_atasan'] ?></td>
							<td><?php echo $r['status'] ?></td>
							<td style="text-align:center;width:160px">

								<a  class="btn btn-xs btn-warning" href="javascript:;"
									data-nik="<?php echo $r['NIK'] ?>"
									data-nama="<?php echo $r['nama'] ?>"
									data-alamat="<?php echo $r['alamat'] ?>"
									data-lahir="<?php echo $r['tgl_lahir'] ?>"
									data-jabatan="<?php echo $r['jabatan'] ?>"
									data-tlp="<?php echo $r['No_tlp'] ?>"
									data-tmt="<?php echo $r['tmt'] ?>"
									data-gender="<?php echo $r['gender'] ?>"
									data-atas="<?php echo $r['NIK_atasan'] ?>"
									data-status="<?php echo $r['status'] ?>"
									data-toggle="modal" data-target="#edit-data">

									<i class="fa fa-pencil"></i> Update

								</a>

								<a class="btn btn-xs btn-danger" href="javascript:;" data-nik="<?php echo $r['NIK']?>" data-toggle="modal" data-target="#modal-konfirmasi"><i class="fa fa-trash"></i> Hapus</a>
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

				<form id="form-data" method="post" action="engine_smo.php?p=tambah">

					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title">Tambah Data</h4>
					</div>

					<div class="modal-body">

						  <fieldset>

				   		    <div class="form-group">
						      <label for="nik">NIK</label>
						      <input type="text" name="nik" class="form-control" placeholder="Masukkan NIK">
						    </div>

							 <div class="form-group">
						      <label for="nama">NAMA</label>
						      <input type="text" name="nama" class="form-control" placeholder="Masukkan NAMA">
						    </div>
							 <div class="form-group">
						      <label for="alamat">ALAMAT</label>
						      <input type="text" name="alamat" class="form-control" placeholder="Masukkan ALAMAT">
						    </div>
						    <div class="form-group">
						      <label for="lahir">TANGGAL LAHIR</label>
						      <input type="text" name="lahir" class="form-control" placeholder="Masukkan TANGGAL LAHIR">
						    </div>

						    <div class="form-group">
						      <label for="tlp">NO TLP</label>
						      <input type="text" name="tlp" class="form-control" placeholder="Masukkan TLP">
						    </div>

						    <div class="form-group">
						      <label for="status">STATUS</label>
						      <input type="text" name="status" class="form-control" placeholder="Masukkan STATUS">
						    </div>
 							 <div class="form-group">
						      <label for="gender">Jenis Feedback</label>
						      <select name="gender" id ="gender" class="form-control">
						        <option value="">Pilih Jenis Kelamin</option>
						        <option value="L">Laki-Laki</option>
						        <option value="P">Perempuan</option>
 								
						  </select> 
						 </div>
							 <div class="form-group">
						      <label for="atas">NIK ATASAN</label>
						      <input type="text" name="atas" class="form-control" placeholder="Masukkan NIK ATASAN">
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

				<form id="form-data" method="post" action="engine_smo.php?p=update">

					<input type="hidden" name="nik" id="nik">

					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title">Update Data sales</h4>
					</div>

					<div class="modal-body">

						  <fieldset>

						   <fieldset>
							 <div class="form-group">
						      <label for="nama">NAMA</label>
						      <input type="text" name="nama" id="nama" class="form-control" placeholder="Masukkan NAMA">
						    </div>
							<div class="form-group">
						      <label for="alamat">NAMA</label>
						      <input type="text" name="alamat" id="alamat" class="form-control" placeholder="Masukkan NAMA">
						    </div>
						    <div class="form-group">
						      <label for="lahir">TANGGAL LAHIR</label>
						      <input type="text" name="lahir" id="lahir" class="form-control" placeholder="Masukkan TANGGAL LAHIR">
						    </div>

						    <div class="form-group">
						      <label for="tlp">NO TLP</label>
						      <input type="text" name="tlp" id="tlp" class="form-control" placeholder="Masukkan TLP">
						    </div>

						    <div class="form-group">
						      <label for="status">STATUS</label>
						      <input type="text" name="status" id="status" class="form-control" placeholder="Masukkan STATUS">
						    </div>
 							 <div class="form-group">
						      <label for="gender">Jenis Feedback</label>
						      <select name="gender" id ="gender" class="form-control">
						        <option value="">Pilih Jenis Kelamin</option>
						        <option value="L">Laki-Laki</option>
						        <option value="P">Perempuan</option>
 								
						  </select> 
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
					Apakah Anda yakin ingin menghapus data ini?
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
			var nik = div.data('nik') 

			var modal = $(this)

			// Mengisi atribut href pada tombol ya yang kita berikan id hapus-true pada modal.
			modal.find('#hapus-true').attr("href","engine_smo.php?p=hapus&nik="+nik); 

		});


		// Untuk sunting
		$('#edit-data').on('show.bs.modal', function (event) {
			var div = $(event.relatedTarget) // Tombol dimana modal di tampilkan

			var nik 		= div.data('nik');
			var nama 	= div.data('nama');
			var alamat 	= div.data('alamat');
			var lahir 	= div.data('lahir');
			var tmt 	= div.data('tmt');
			var tlp 	= div.data('tlp');			
			var status 	= div.data('status');
			var gender 	= div.data('gender');
			var modal 	= $(this)

			// Isi nilai pada field
			modal.find('#nik').attr("value",nik);
			modal.find('#nama').attr("value",nama);
			modal.find('#alamat').attr("value",alamat);
			modal.find('#lahir').attr("value",lahir);
			modal.find('#status').attr("value",status);
			modal.find('#tlp').attr("value",tlp);	
			// Membuat combobox terpilih berdasarkan jenis kelamin yg tersimpan saat pengeditan
			
			modal.find('#gender option').each(function(){
				  if ($(this).val() == gender )
				    $(this).attr("selected","selected");
			});

		});

	});

</script>

</body>
</html>