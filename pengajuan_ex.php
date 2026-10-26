<?php require_once __DIR__ . '/php8_compat.php';
session_start();
ob_start();

date_default_timezone_set('Asia/Jakarta');

$userid=$_SESSION['userid'];
$sakad=$_SESSION['sakadd'];
$wakal=$_SESSION['swakall'];
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
		<title>Pengajuan Pembiayaan Anggota</title>
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
	
		<div class="col-md-offset-0 col-md-10 col-md-offset-1">

			<b>PENGAJUAN PEMBIAYAAN KELOMPOK</b>
			
			
			<table class="table table-bordered" >
				<tr>
					<th><span class="badge">UNIT</span></th>
					<th><span class="badge">CIF</span></th>
					<th><span class="badge">KODE KELOMPOK</span></th>
					<th><span class="badge">NAMA</span></th>
					<th><span class="badge">PLAFOND SEBELUMNYA</span></th>
					<th><span class="badge">ANGSURAN</span></th>
					<th><span class="badge">MARGIN</span></th>
					<th><span class="badge">PINJAMAN KE</span></th>
					<th style="text-align:center"><span class="badge">DETAIL</span></th>
				</tr>

				<?php $sql = $conn->query("SELECT anggota.CIF as dim,anggota.UNIT,anggota.CUST_SHORT_NAME,anggota.no,anggota.CODE_KEL,loan.Plafond,angsuran,bagi_hasil,tenor,suffix,loan.cao FROM anggota 
				inner join loan on loan.cif =anggota.cif
				where anggota.CODE_KEL='$code_kel' and anggota.UNIT ='$branch' and anggota.status='ANGGOTA' "); ?>

					<?php while ( $r = $sql->fetch_assoc() ) { ?>

						<tr>
							<td><?php echo $r['UNIT'] ?></td>
							<td><?php echo $r['dim'] ?></td>
							<td><?php echo $r['CODE_KEL'] ?></td>
							<td><?php echo $r['CUST_SHORT_NAME'] ?></td>							
							<td><?php echo number_format (compat_num($r['Plafond'])) ?></td>
							<td><?php echo number_format (compat_num($r['angsuran'])) ?></td>
							<td><?php echo number_format (compat_num($r['bagi_hasil'])) ?></td>
							<td><?php echo $r['suffix'] ?></td>
							<td style="text-align:center;width:160px">

								<a  class="btn btn-xs btn-warning" href="javascript:;"
									data-id="<?php echo $r['no'] ?>"
									data-cif="<?php echo $r['dim'] ?>"
									
									data-nama="<?php echo $r['CUST_SHORT_NAME'] ?>"
									data-kds="<?php echo $r['cao'] ?>"
									data-pla="<?php echo $r['Plafond'] ?>"
									data-toggle="modal" data-target="#edit-data">

									<i class="fa fa-pencil"></i> Ajukan

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



<div id="edit-data" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="editDataLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document"> <!-- Tambahkan modal-lg agar lebih lebar -->
    <div class="modal-content">
      <form id="form-data" method="post" action="engine_update.php?p=update">
        <input type="hidden" name="id" id="id">
        
        <div class="modal-header">
          <h5 class="modal-title" id="editDataLabel">Update Data Pengajuan Pembiayaan</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>

        <div class="modal-body">
          <!-- Contoh 1 field -->
          <div class="form-group">
            <label for="nama">Nama</label>
            <input type="text" name="nama" id="nama" class="form-control" maxlength="40" placeholder="Nama">
          </div>

          <div class="form-group">
            <label for="cif">CIF</label>
            <input type="text" name="cif" id="cif" class="form-control" maxlength="6">
          </div>

          <div class="form-group">
            <label for="tenor">Tenor</label>
            <input type="text" name="tenor" id="tenor" class="form-control" maxlength="6">
          </div>

          <div class="form-group">
            <label for="kds">Kode AO</label>
            <input type="text" name="kds" id="kds" class="form-control" maxlength="6" placeholder="Masukkan KODE AO">
          </div>

          <div class="form-group">
            <label for="pla">Plafond</label>
            <input type="text" name="pla" id="pla" class="form-control" maxlength="7" placeholder="Masukkan PLAFOND">
          </div>

          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="inn">Wakalah</label>
              <input type="date" name="inn" id="inn" class="form-control" value="<?php echo $wakal; ?>">
            </div>
            <div class="form-group col-md-6">
              <label for="akad">Akad</label>
              <input type="date" name="akad" id="akad" class="form-control" value="<?php echo $sakad; ?>">
            </div>
          </div>

          <!-- Barang & Harga (gunakan loop di PHP jika bisa) -->
          <?php for ($i = 1; $i <= 10; $i++): ?>
            <div class="form-row">
              <div class="form-group col-md-6">
                <label for="barang_<?php echo $i; ?>">Barang <?php echo $i; ?></label>
                <input type="text" name="barang_<?php echo $i; ?>" id="barang_<?php echo $i; ?>" class="form-control" maxlength="40" placeholder="Nama Barang <?php echo $i; ?>">
              </div>
              <div class="form-group col-md-6">
                <label for="harga_<?php echo $i; ?>">Harga</label>
                <input type="text" name="harga_<?php echo $i; ?>" id="harga_<?php echo $i; ?>" class="form-control" maxlength="10" onkeyup="sum();" placeholder="Harga Barang <?php echo $i; ?>">
              </div>
            </div>
          <?php endfor; ?>

          <div class="form-group">
            <label for="jenisAkad">Jenis Akad</label>
            <select name="jenisAkad" id="jenisAkad" class="form-control" required>
        <option value="">-- Pilih Akad --</option>
        <?php
		  $sql = mysql_query("SELECT jenis_akad, nama_akad 
                            FROM jenis_akad 
                            WHERE unit='$userid' 
                            ORDER BY nama_akad ASC");

         if (mysql_num_rows($sql) > 0) {
            while ($row = mysql_fetch_array($sql)) {
                echo "<option value='" . $row['jenis_akad'] . "'>" . $row['nama_akad'] . "</option>";
            }
        } else {
            echo "<option value=''>Data tidak ada</option>";
        }

      
        ?>
    </select>
          </div>

          <?php echo set_progress(); ?>
        </div>

        <div class="modal-footer">
          <button type="submit" class="btn btn-info"><i class="fa fa-save"></i> Simpan</button>
          <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fa fa-close"></i> Batal</button>
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
			modal.find('#hapus-true').attr("href","engine_update.php?p=hapus&id="+id); 

		});


		// Untuk sunting
		$('#edit-data').on('show.bs.modal', function (event) {
			var div = $(event.relatedTarget) // Tombol dimana modal di tampilkan

			var id 			= div.data('id');
			var cif 		= div.data('cif');
			var kds 		= div.data('kds');
			
			var pla 		= div.data('pla');
			var inn 		= div.data('inn');
			var nama 		=div.data('nama'); 
			
			var modal 	= $(this)

			// Isi nilai pada field
			modal.find('#id').attr("value",id);
			modal.find('#cif').attr("value",cif);
			modal.find('#kds').attr("value",kds);
			
			modal.find('#pla').attr("value",pla);
			modal.find('#inn').attr("value",inn);
			modal.find('#nama').attr("value",nama);
			
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
