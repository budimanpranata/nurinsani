<?php require_once __DIR__ . '/php8_compat.php';
session_start();
$userid=$_SESSION['userid'];
include "indo_tgl.php";
date_default_timezone_set('Asia/Jakarta');
//cek apakah user sudah login
$tgl_system=$_SESSION['stgl_system'];
//cek apakah user sudah login
if(!isset($_SESSION['stgl_system'])){
echo "<script>alert('Maaf,.tanggal system belum di setup');</script>";

   header("location:param_tgl.php");
 exit; }
if(!isset($_SESSION['userid'])){
   header("location:index.html");
 exit; }

// Tampilan Bootstrap 5 (2026-10-31, permintaan user): hanya HTML/CSS yang diganti.
// Kode PHP pencarian dan daftar booking sengaja dibiarkan sama dengan versi lama.
require_once __DIR__ . '/inc/tampilan.php';
halaman_awal('Transaksi Booking Debet');
?>
<script type="text/javascript" src="jquery.js"></script>
<script>
	// saran kelompok (versi lama, autosuggest.php)
	function suggest(inputString){
		if(inputString.length == 0) {
			$('#suggestions').fadeOut();
		} else {
		$('#country').addClass('load');
			$.post("autosuggest.php", {queryString: ""+inputString+""}, function(data){
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
<style>
  /* daftar saran versi lama (HTML <ul><li>) */
  .saran-lama { position: relative; }
  .saran-lama .suggestionsBox { position: absolute; left: 0; right: 0; top: 100%; z-index: 1050; margin-top: 2px;
    background: var(--bs-body-bg, #fff); border: 1px solid var(--bs-border-color, #dee2e6); border-radius: .375rem;
    box-shadow: 0 .5rem 1rem rgba(0,0,0,.15); max-height: 260px; overflow-y: auto; }
  .saran-lama .suggestionList ul { list-style: none; margin: 0; padding: 0; }
  .saran-lama .suggestionList li { padding: .4rem .75rem; cursor: pointer; font-size: .875rem; border-bottom: 1px solid var(--bs-border-color, #eee); }
  .saran-lama .suggestionList li:hover { background: #e8f2ec; }
</style>
<div class="card card-ni">
  <div class="card-header">Kriteria Pencarian</div>
  <div class="card-body">
    <form method="post" action="<?php echo h($_SERVER['PHP_SELF']); ?>" name="postform" class="form-ni" autocomplete="off">
      <div class="row g-2 mb-3">
        <label for="kode" class="col-sm-4 col-form-label">Kelompok</label>
        <div class="col-sm-8 saran-lama">
          <input type="text" onKeyUp="suggest(this.value);" name="kelompok" autocomplete="off" onBlur="fill2();" id="kode" class="form-control form-control-sm">
          <div class="suggestionsBox" id="suggestions" style="display: none;">
            <div class="suggestionList" id="suggestionsList"></div>
          </div>
        </div>
      </div>
      <div class="text-end">
        <button name="search" type="submit" id="submit2" value="Search" class="btn btn-ni btn-sm px-4">Search</button>
      </div>
    </form>
  </div>
</div>

<div class="table-responsive mt-3">
<table class="table table-sm table-bordered table-ni align-middle">
  <thead>
  <tr>
    <th>Unit</th>
    <th>Kelompok</th>
    <th class="text-end">NOA</th>
    <th>Nama AO</th>
    <th class="text-end">Setoran</th>
    <th>Tgl</th>
    <th class="text-center">Detail</th>
  </tr>
  </thead>
  <tbody>
  <?php
  include "conn2.php";
  $koneksi=open_connection2();
  $kelompok=$_POST['kelompok'];
  $Nama=$_POST['nama'];
  $tanggl=$_POST ['tanggal'];
$buss_date=date("Y-m-d");
  
$data = mysql_query ("select kode_branch from user where userid ='$userid'");
$r = mysql_fetch_array($data);
$branch=$r['kode_branch'];

  If ($_POST ['search'])
{
if (empty($kelompok)){
	echo "<script>alert('Maaf,isi Kode Kelompok Yang Benar!');</script>";
         echo "<script language='javascript'>document.location.href='booking_debet.php'</script>";}
	else{
	$select = mysql_query ("select count(no_anggota)as noa,sum(bulat) as tagihan,unit,kelompok.nama_kel,loan.cao,next_schedule from loan 
	inner join kelompok on kelompok.code_kel = loan.code_kel
	where loan.code_kel ='$kelompok' AND loan.unit='$branch' and os > 0 and unit not in (select unit from psbb)  
	group by loan.code_kel order by loan.code_kel
	");
	
	if(mysql_num_rows($select)==0){
			
			echo "<script>alert ('Maaf,Kelompok $nopen tidak ditemukan'); </script>";
         echo "<script language='javascript'>document.location.href='booking_debet.php' </script>";


		 }
		
 	//menampilkan data
	while($data = mysql_fetch_array($select)){

//menghitung hitung
//$birthdate = $data['Birth_date'];
//$age = ((time() - strtotime($birthdate))/(60*60*24*365.2425));

		?>
  <tr>
    <td><?php echo $data['unit']; ?></td>
	<td><?php echo $data['nama_kel'];  ?></td>
	<td class="text-end"><?php echo $data['noa']; ?></td>
    <td><?php echo $data['cao']; ?></td>
	<td class="text-end"><?php echo number_format (compat_num($data['tagihan'])); ?></td>
	<td><?php echo tgl_indo ($data['next_schedule']);?></td>
	     <td class="text-center"><a href="booking_list.php?code_kel=<?php echo $_POST['kelompok'];?>" class="btn btn-ni btn-sm" onClick="return confirm('Anda akan melakukan Setoran Kelompok ?')">Proses</a></td>
  </tr>
  <?php
		
	}
	}
	}
	else{


		

		


	
		?>
  <tr>
    
  
  </tr>
  <?php
  	$select = mysql_query ("select count(no_anggota)as noa,sum(bulat) as tagihan,unit,kelompok.nama_kel,loan.cao,next_schedule from loan 
	inner join kelompok on kelompok.code_kel = loan.code_kel
	INNER JOIN booking_debet ON booking_debet.cif = loan.cif
	where  loan.unit='$branch' and os > 0 and unit not in (select unit from psbb)
	group by loan.unit  order by loan.unit
	");

	while($data = mysql_fetch_array($select)){
  ?>
	<tr>
	<td><?php echo $data['unit']; ?></td>
	<td><?php echo $data['nama_kel'];  ?></td>
	<td class="text-end"><?php echo $data['noa']; ?></td>
	<td><?php echo $data['cao']; ?></td>
	<td class="text-end"><?php echo number_format (compat_num($data['tagihan'])); ?></td>
	<td><?php echo tgl_indo ($data['next_schedule']);?></td>
<td class="text-center"><a href="javascript:;" class="btn btn-outline-success btn-sm" onClick="window.open('export_booking.php','scrollwindow','top=200,left=300,width=800,height=500');">Excel</a></td>
  </tr>
  <?php
	}
	?>
	
  <?php
		
	
			
	}
	?>
  
  </tbody>
</table>
</div>
<?php
halaman_akhir();
