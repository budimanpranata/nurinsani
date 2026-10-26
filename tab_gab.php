<?php require_once __DIR__ . '/php8_compat.php';
session_start();
ob_start();
$userid=$_SESSION['userid'];
include "config.php";
$buss_date = date("y-m-d");
$code_kel=$_GET['code_kel'];
if(!isset($_SESSION['userid'])){
   header("location:index.html");//jika belum login jangan lanjut..
}


// Fungsi untuk menampilkan progress bar
include "conn2.php";
$koneksi=open_connection2();
$data = mysql_query ("select kode_branch from user where userid ='$userid'");
$r = mysql_fetch_array($data);
$branch=$r['kode_branch'];
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
		<title>SETOR TABUNGAN KELOMPOK</title>
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
		<hr >
	
		<div class="col-md-offset-0 col-md-10 col-md-offset-1">

			<b>DATA SETORAN KELOMPOK</b>
			
			
			<table class="table table-striped" >
				<tr>
					<th><span class="badge">UNIT</span></th>
					<th><span class="badge">NO ANGGOTA</span></th>
					<th><span class="badge">KODE KELOMPOK</span></th>
					<th><span class="badge">NAMA</span></th>
					<th><span class="badge">PLAFOND</span></th>
					<th><span class="badge">MARGIN</span></th>
					<th><span class="badge">ANGSURAN</span></th>
					<th><span class="badge">JUMLAH TABUNGAN</span></th>
					<th style="text-align:center"><span class="badge">DETAIL</span></th>
				</tr>

				<?php $sql = $conn->query("SELECT(sum(DEBIT)-sum(KREDIT)) as saldo,anggota.CIF,anggota.UNIT,anggota.CUST_SHORT_NAME,anggota.no,anggota.CODE_KEL,loan.Plafond,angsuran,bagi_hasil,suffix,anggota.cao FROM anggota 
				inner join loan on loan.no_anggota =anggota.no
				left join simpanan on simpanan.norek=loan.no_anggota
				where anggota.CODE_KEL='$code_kel' and anggota.UNIT ='$branch' group by loan.no_anggota"); ?>

					<?php while ( $r = $sql->fetch_assoc() ) { ?>

						<tr>
							<td><?php echo $r['UNIT'] ?></td>
							<td><?php echo $r['no'] ?></td>
							<td><?php echo $r['CODE_KEL'] ?></td>
							<td><?php echo $r['CUST_SHORT_NAME'] ?></td>							
							<td><?php echo number_format (compat_num($r['Plafond'])) ?></td>
							<td><?php echo number_format (compat_num($r['bagi_hasil'])) ?></td>
							<td><?php echo number_format (compat_num($r['angsuran'])) ?></td>
							<td><?php echo number_format (compat_num($r['saldo'])) ?></td>
							<td style="text-align:center;width:160px">

								<a  class="btn btn-xs btn-warning" href="javascript:;"
									data-id="<?php echo $r['no'] ?>"
									data-cif="<?php echo $r['CIF'] ?>"
									data-code_kel="<?php echo $r['CODE_KEL'] ?>"
									data-nama="<?php echo $r['CUST_SHORT_NAME'] ?>"
									data-kds="<?php echo $r['cao'] ?>"
									data-pla="<?php echo $r['Plafond'] ?>"
									
									data-toggle="modal" data-target="#edit-data">

									<i class="fa fa-pencil"></i> Setor

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

				<form id="form-data" method="post" action="engine_update_stor.php?p=tambah">

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

				<form id="form-data" method="post" action="engine_update_stor.php?p=update">

					<input type="hidden" name="id" id="id">

					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title">Input Setoran Kelompok</h4>
					</div>
					
					<div class="modal-body">
						
						  <fieldset>
							<tr>
						    <div class="form-group">
						         <td><label for="cif">CIF</label></td>
						     <td> <input type="text"  name="cif" id="cif" class="form-control" placeholder="Masukkan NO ANGGOTA" size="10" maxlength="6"/></td>
						    </div>
							 <div class="form-group">
						       <td><label for="nama">Nama</label></td>
						    <td> <input type="text"  name="nama" id="nama" class="form-control" placeholder="" size="20" maxlength="6"/></td>
						    <br>
							</div>
							</tr>
							<div class="form-group">
						     <td> <label for="setor">SETORAN</label></td> 
						     <td> <input type="text" name="setor" id="setor" class="form-control" placeholder="Masukkan STORAN" size="10" maxlength="6"/><td>
						    </div>
							</tr>
							 
						  </fieldset>
						
						<?php echo set_progress(); ?>
						
					</div>
 </table>
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
			modal.find('#hapus-true').attr("href","engine_update_stor.php?p=hapus&id="+id); 

		});


		// Untuk sunting
		$('#edit-data').on('show.bs.modal', function (event) {
			var div = $(event.relatedTarget) // Tombol dimana modal di tampilkan

			var id 			= div.data('id');
			var cif 		= div.data('cif');
			var kds 		= div.data('kds');
			var code_kel	= div.data('code_kel');
			var nama 		= div.data('nama');
			var inn 		= div.data('inn');
			
			var modal 	= $(this)

			// Isi nilai pada field
			modal.find('#id').attr("value",id);
			modal.find('#cif').attr("value",cif);
			modal.find('#kds').attr("value",kds);
			modal.find('#code_kel').attr("value",code_kel);
			modal.find('#nama').attr("value",nama);
			modal.find('#inn').attr("value",inn);
			
			// Membuat combobox terpilih berdasarkan jenis kelamin yg tersimpan saat pengeditan
	
		});

	});

</script>
<script src="js/bootstrap-datepicker.js"></script>
	<script>
	$('.date').datepicker({
		format: 'yyyy-mm-dd',
	})
	</script>
	
	<script>
function sum() {
      var txtFirstNumberValue = document.getElementById('harga_satu').value;
      var txtSecondNumberValue = document.getElementById('harga_dua').value;
	    var txtThreeNumberValue = document.getElementById('harga_tiga').value;
		  var txtFourNumberValue = document.getElementById('harga_empat').value;
		var txtFiveNumberValue= document.getElementById('harga_lima').value;
		var txtSixNumberValue= document.getElementById('harga_enam').value;
		var txtSevenNumberValue= document.getElementById('harga_tujuh').value;
		var txtEightNumberValue= document.getElementById('harga_delapan').value;
		var txtNineNumberValue= document.getElementById('harga_sembilan').value;
		var txtTenNumberValue= document.getElementById('harga_sepuluh').value;
	      var result = parseInt(txtFirstNumberValue) + parseInt(txtSecondNumberValue) + parseInt(txtThreeNumberValue) + parseInt(txtFourNumberValue) + parseInt(txtFiveNumberValue) + parseInt(txtSixNumberValue) + parseInt(txtSevenNumberValue) + parseInt(txtEightNumberValue) + parseInt(txtNineNumberValue) + parseInt(txtTenNumberValue);
      if (!isNaN(result)) {
         document.getElementById('pla').value = result;
      }
}
</script>
</body>
</html>