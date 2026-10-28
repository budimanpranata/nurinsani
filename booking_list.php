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
?>


<link rel="stylesheet" href="css/bootstrap.min.css"/>
<script>
function cek(cekbox){
    for(i=0; i < cekbox.length; i++){
        cekbox[i].checked = true;
    }
}
function uncek(cekbox){
    for(i=0; i < cekbox.length; i++){
        cekbox[i].checked = false;
    }
}
</script>
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
<div class="main">
<form action="booking_debet_kel.php" method="post" name="myform">
    <table border="1" style="margin: 10px 0px" class="table table-striped" >
            <tr>
               <th><span class="badge"> Pilih</th>
               <th><span class="badge"> Cif</th>
               <th><span class="badge">Nama</th>
               <th><span class="badge">Kelompok</th>
			   <th><span class="badge">Pembiayaan</th>
			   <th><span class="badge">Angsuran</th>
			   <th><span class="badge">Setoran</th>
               <th><span class="badge">Tunggakan</th>
			   <th><span class="badge">TWM</th>
                <th><span class="badge">Total Simpanan</th>
               <th><span class="badge">Sisa</th>
			   
			  
				
			            </tr>
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
                <td><input type="checkbox" id="cekbox" name="cekbox[]" value="<?php echo $data['cif'] ?>"/></td>
                <td><?php echo $data['cif'] ?></td>
				<td><?php echo $data['Cust_Short_name'] ?></td>
                <td><?php echo $data['nama_kel'] ?></td>
				<td><?php echo number_format(compat_num($data['os']))?></td>
				<td><?php echo number_format (compat_num($data['angsuran'])) ?></td>
				<td><?php echo number_format (compat_num($data['bulat'])) ?></td>
                <td><?php echo number_format (compat_num($tunggakan)) ?></td>
                <td><?php echo number_format (compat_num($total_twm)) ?></td>
                <td><?php echo number_format (compat_num($saldo)) ?></td>
                <td><?php echo number_format (compat_num($saldo-$total_twm)) ?></td>
				 
				
            

            </tr>
    
    <?php
    
	}

    ?>
<tr>
	<td></td>
<td></td>
<td></td>
<td></td>
<td></td>
<td></td>

</tr>
    </table>
    <br />
    <input type="button" class="alert-success" onclick="cek(this.form.cekbox)" value="Select All" />
    <input type="button" class="btn-danger" onclick="uncek(this.form.cekbox)" value="Clear All" />
    <input name="submit" type="submit" class="btn-success" value="Booking Debet" />
		
	    <br />
		
</form>
</div>
<script type="text/javascript">
getTotal();
</script>
