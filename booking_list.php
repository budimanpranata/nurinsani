<?php require_once __DIR__ . '/php8_compat.php';
session_start();
ob_start();
$userid=$_SESSION['userid'];
$code_kel=$_GET['code_kel'];
date_default_timezone_set('Asia/Jakarta');
//cek apakah user sudah login
if(!isset($_SESSION['userid'])){
   header("location:index.html");
 exit; }

//cek level user
if($_SESSION['level']!="unit"){
   header("location:index.html");
 exit; }  
// Tampilan Bootstrap 5 (2026-10-31, permintaan user): hanya HTML/CSS yang diganti.
// Kode PHP daftar tunggakan dan proses (booking_debet_kel.php) sengaja dibiarkan sama dengan versi lama.
require_once __DIR__ . '/inc/tampilan.php';
halaman_awal('Booking Debet');
?>
<?php  

  
include "conn2.php";
include "indo_tgl.php";
$koneksi=open_connection2(); 
//ambil data dari database
$result = mysql_query(" Select kode_branch from user where userid='$userid'");
while($row = mysql_fetch_array($result)){
$unit=$row['kode_branch'];
}

$date=date('Y-m-d');	
$query = "
select 
tunggakan.unit,
tunggakan.cif,
Cust_Short_name,
nama_kel,
angsuran,
run_tenor,
deal_produk,
os,
saldo_margin,
ROUND(sum(KREDIT-DEBIT)/angsuran) as ft,
ROUND(sum(KREDIT-DEBIT)) as nominal,
bulat ,
loan.code_kel as code_kel,
loan.cao as cao,
'0' as twm,
bulat,
plafond
from tunggakan
inner join loan on loan.cif = tunggakan.cif
left join kelompok on kelompok.code_kel = loan.code_kel
Where loan.unit='$unit' and os > 0 and kelompok.code_kel='$code_kel' and loan.cif not in (select cif from booking_debet )
group by tunggakan.cif
HAVING sum(KREDIT-DEBIT) > 0
";
$result = mysql_query($query);
?>
<form action="booking_debet_kel.php" method="post" name="myform" id="form-booking">
  <div class="table-responsive">
    <table class="table table-sm table-bordered table-ni table-hover align-middle">
      <thead>
        <tr>
          <th class="text-center">Pilih</th>
          <th>Cif</th>
          <th>Nama</th>
          <th>Kelompok</th>
          <th class="text-end">Pembiayaan</th>
          <th class="text-end">Angsuran</th>
          <th class="text-end">Setoran</th>
          <th class="text-end">Tunggakan</th>
          <th class="text-end">TWM</th>
          <th class="text-end">Total Simpanan</th>
          <th class="text-end">Sisa</th>
        </tr>
      </thead>
      <tbody>
    <?php 
    while ($data = mysql_fetch_array($result)) {
		$cif =$data['cif'];
        $bulat =$data['bulat'];
        $deal_produk =$data['deal_produk'];
        $angsuran =$data['angsuran'];
         $minggon = $bulat-$angsuran;
        $cek_tunggakan = mysql_query("SELECT SUM(KREDIT - DEBIT) AS saldo from tunggakan where cif ='$cif'");
		$data_tunggkan= mysql_fetch_array($cek_tunggakan);
		$tunggakan=$data_tunggkan['saldo'];
        $ft=$tunggakan/$angsuran;

          if($deal_produk=='2'){	
			$angsuran=$angsuran + 2500;
			$ijaroh = $ijaroh + 2500;
            $tunggakan = $tunggakan + (2500 * $ft);


		}else{
			
		$angsuran=$angsuran ;
		$ijaroh = $ijaroh;
        $tunggakan = $tunggakan;


		}
        
        $run_tenor=$data['run_tenor'];
        $total_twm = $minggon * $run_tenor;

		$cek_saldo = mysql_query("SELECT SUM(KREDIT - DEBIT) AS saldo from simpanan where cif ='$cif' and TYPE not in ('04','05')");
		$data_saldo= mysql_fetch_array($cek_saldo);
		$saldo=$data_saldo['saldo'];

      
        

    ?>
            <tr>
                <td class="text-center"><input type="checkbox" class="form-check-input" name="cekbox[]" value="<?php echo $data['cif'] ?>"/></td>
                <td><?php echo $data['cif'] ?></td>
				<td><?php echo $data['Cust_Short_name'] ?></td>
                <td><?php echo $data['nama_kel'] ?></td>
				<td class="text-end"><?php echo number_format(compat_num($data['os']))?></td>
				<td class="text-end"><?php echo number_format (compat_num($data['angsuran'])) ?></td>
				<td class="text-end"><?php echo number_format (compat_num($data['bulat'])) ?></td>
                <td class="text-end"><?php echo number_format (compat_num($tunggakan)) ?></td>
                <td class="text-end"><?php echo number_format (compat_num($total_twm)) ?></td>
                <td class="text-end"><?php echo number_format (compat_num($saldo)) ?></td>
                <td class="text-end"><?php echo number_format (compat_num($saldo-$total_twm)) ?></td>
				 
				
            

            </tr>
    
    <?php
    
	}

    ?>
      </tbody>
    </table>
  </div>
  <div class="d-flex flex-wrap gap-2">
    <button type="button" class="btn btn-outline-secondary btn-sm" data-pilih-semua="1">Select All</button>
    <button type="button" class="btn btn-outline-danger btn-sm" data-pilih-semua="0">Clear All</button>
    <button name="submit" type="submit" value="Booking Debet" class="btn btn-ni btn-sm ms-auto px-4">Booking Debet</button>
  </div>
</form>
<script>
// pilih / hapus semua centang (pengganti cek()/uncek() versi lama)
document.querySelectorAll('[data-pilih-semua]').forEach(function (b) {
    b.addEventListener('click', function () {
        var nilai = b.getAttribute('data-pilih-semua') === '1';
        document.querySelectorAll('#form-booking input[name="cekbox[]"]').forEach(function (c) {
            c.checked = nilai;
        });
    });
});
</script>
<?php
halaman_akhir();
