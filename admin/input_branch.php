<?php require_once __DIR__ . '/../php8_compat.php';
session_start();
include "conn2.php";
$koneksi=open_connection2();
//cek apakah user sudah login
if(!isset($_SESSION['userid'])){
   header("location:index.html");
}

//cek level user
if($_SESSION['level']!="admin"){
   header("location:index.html");
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
		<title></title>
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
		<meta charset="utf-8">

		<link href="css/bootstrap.min.css" rel="stylesheet">	
		<link href="css/style.css" rel="stylesheet">	

		<script src="js/jquery.min.js"></script>
		<script src="js/bootstrap.min.js"></script>
		<script src="js/jquery.form.js"></script>

	    <style type="text/css">
<!--
.style1 {color: #CCCCCC}
.style14 {
	color: #FFFFFF;
	font-weight: bold;
	font-size: 14px;
}
-->
        </style>
	</head>

<body>

	<section class="container">
	
		<center>
		  <table width="100%" border="0" cellpadding="3" cellspacing="0" bgcolor="#4682B4">
            <tr>
              <td width="100%" bgcolor="#776a56" class="style1"><span class="style14">Entry Data Cabang </span></td>
            </tr>
          </table>
		</center>
		<hr />
	
		<div class="col-md-offset-1 col-md-10 col-md-offset-1">

			<button class="btn btn-xs pull-right" data-branch='0' data-toggle="modal" data-target="#tambah-data">+ Tambah Data</button> <br /><br />
			
			<table width="756" class="table table-bordered">
				<tr>
					<th>Code Branch </th>
					<th>Nama Branch </th>
					<th>Alamat</th>
					<th>GL</th>
					<th style="text-align:center">Update</th>
				</tr>

				<?php $sql =mysql_query ("SELECT * FROM branch"); 

					?>

					<?php while($r = mysql_fetch_array($sql)){ ?>

						<tr>
							<td><?php echo $r['kode_branch'] ?></td>
							<td><?php echo $r['nama'] ?></td>
							<td style="text-align:center"><?php echo $r['alamat'] ?></td>
							<td><?php echo $r['GL'] ?></td>
							<td style="text-align:center;width:160px">

								<a  class="btn btn-xs btn-warning" href="javascript:;"
									data-branch="<?php echo $r['kode_branch'] ?>"
									data-nama="<?php echo $r['nama'] ?>"
									data-alamat="<?php echo $r['alamat'] ?>"
									data-gl="<?php echo $r['gl'] ?>"
									data-toggle="modal" data-target="#edit-data">

									<i class="fa fa-pencil"></i> Sunting

								</a>

								<a class="btn btn-xs btn-danger" href="javascript:;" data-branch="<?php echo $r['kode_branch'] ?>" data-toggle="modal" data-target="#modal-konfirmasi"><i class="fa fa-trash"></i> Hapus</a>
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

				<form id="form-data" method="post" action="engine.php?p=tambah">

					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title">Tambah Data</h4>
					</div>

					<div class="modal-body">

						  <fieldset>

						    <div class="form-group">
						      <label for="branch">Kode Branch </label>
						      <input type="text" name="branch" class="form-control" placeholder="Masukkan Kode Branch">
						    </div>

						    <div class="form-group">
						      <label for="nama">Nama Branch </label>
						      <input type="text" name="nama" class="form-control" placeholder="Masukkan Nama">
						    </div>

						    <div class="form-group">
						      <label for="alamat">Alamat</label>
						      <input type="text" name="alamat" class="form-control" placeholder="Masukkan Alamat">
						    </div>
					       <div class="form-group">
						      <label for="gl">GL</label>
						      <input type="text" name="gl" class="form-control" placeholder="Masukkan gl">
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

				<form id="form-data" method="post" action="engine.php?p=update">

					<input type="hidden" name="branch" id="branch">

					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title">Edit Data</h4>
					</div>

					<div class="modal-body">

						  <fieldset>

						    <div class="form-group">
						      <label for="branch">Kode Branch </label>
						      <input type="text" name="branch" id="branch" class="form-control" placeholder="Masukkan NIM">
						    </div>

						    <div class="form-group">
						      <label for="nama">Nama Branch </label>
						      <input type="text" id="nama" name="nama" class="form-control" placeholder="Masukkan Nama">
						    </div>

						    <div class="form-group">
						      <label for="alamat">Alamat</label>
						      <input type="text" id="alamat" name="alamat" class="form-control" placeholder="Masukkan Alamat">
						    </div>
					       <div class="form-group">
						      <label for="gl">GL</label>
						      <input type="text" name="gl" class="form-control" placeholder="Masukkan Alamat">
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
			var branch = div.data('branch') 

			var modal = $(this)

			// Mengisi atribut href pada tombol ya yang kita berikan id hapus-true pada modal.
			modal.find('#hapus-true').attr("href","engine.php?p=hapus&branch="+branch); 

		});


		// Untuk sunting
		$('#edit-data').on('show.bs.modal', function (event) {
			var div = $(event.relatedTarget) // Tombol dimana modal di tampilkan

			var branch 	= div.data('branch');
			var nama 	= div.data('nama');
			var gl 	= div.data('gl');
			var alamat 	= div.data('alamat');

			var modal 	= $(this)

			// Isi nilai pada field
			modal.find('#branch').attr("value",branch);
			modal.find('#nama').attr("value",nama);
			modal.find('#gl').attr("value",gl);
			modal.find('#alamat').attr("value",alamat);

			// Membuat combobox terpilih berdasarkan jenis kelamin yg tersimpan saat pengeditan
			);

		});

	});

</script>

</body>
</html>