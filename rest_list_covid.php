<?php require_once __DIR__ . '/php8_compat.php';
session_start();
ob_start();
$userid=$_SESSION['userid'];
$code_kel=$_GET['code_kel'];
//cek apakah user sudah login
if(!isset($_SESSION['userid'])){
   header("location:index.html");
}

//cek level user
if($_SESSION['level']!="unit"){
   header("location:index.html");
}  
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
$query = "Select loan.cif,unit,Plafond,ijaroh,angsuran,bulat, bagi_hasil,bulat,pokok,Cust_Short_name,kelompok.nama_kel,(os-saldo_margin) as pokok, saldo_margin, os from loan
inner join kelompok on kelompok.code_kel = loan.code_kel
Where kelompok.code_kel='$code_kel' and loan.unit='$unit' and os >0";
$result = mysql_query($query);
?>
<form action="<?php echo "realisasi_rest_kel_covid.php?code_kel=".$code_kel?>" method="post">
    <table border="1" style="margin: 10px 0px" class="table table-striped" >
            <tr>
               <th><span class="badge"> Pilih</th>
               <th><span class="badge"> Cif</th>
               <th><span class="badge">Nama</th>
               <th><span class="badge">Kelompok</th>
			   <th><span class="badge">Pembiayaan</th>
			   <th><span class="badge">Angsuran</th>
			   <th><span class="badge">Setoran</th>
			   <th><span class="badge">Kemampuan Bayar</th>
			   
			  
				
			            </tr>
    <?php 
    while ($data = mysql_fetch_array($result)) {
		$cif =$data['cif'];
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
				 <td><input type="text" id="bulat" tabindex="1" name="bulat[]" /></td>
				 
				
            

            </tr>
    
    <?php
    
	}

    ?>
    </table>
    <br />
    <input type="button" class="alert-success" onclick="cek(this.form.cekbox)" value="Select All" />
    <input type="button" class="btn-danger" onclick="uncek(this.form.cekbox)" value="Clear All" />
	<input type="hidden" name="kel" value="<?php echo $code_kel;?>"/>
    <input name="submit" type="submit" class="btn-success" value="Realisasi" />
		
	    <br />
		
</form>
