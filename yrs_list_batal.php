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
$query = "Select distinct rek_loan.no_anggota,rek_loan.tgl_realisasi,rek_loan.unit,Plafond,ijaroh,bagi_hasil,bulat,pokok,Cust_Short_name,kelompok.nama_kel from rek_loan
inner join loan on loan.no_anggota = rek_loan.no_anggota 
inner join kelompok on kelompok.code_kel = loan.code_kel
Where kelompok.code_kel='$code_kel' and rek_loan.unit='$unit' and os >0 group by rek_loan.no_anggota ";
$result = mysql_query($query);
?>
<form action="realisasi_tag_batal.php" method="post">
    <table border="1" style="margin: 10px 0px" class="table table-striped" >
            <tr>
               <th><span class="badge"> Pilih</th>
               <th><span class="badge"> No Anggota</th>
               <th><span class="badge">Nama</th>
               <th><span class="badge">Unit</th>
			   <th><span class="badge">Kelompok</th>
			   <th><span class="badge">Pembiayaan</th>
			   <th><span class="badge">Margin</th>
			   <th><span class="badge">Storan</th>
			   <th><span class="badge">Pokok</th>
				<th><span class="badge">Margin Mingguan</th>
				<th><span class="badge">Reff</th>
				
			            </tr>
    <?php 
    while ($data = mysql_fetch_array($result)) {
    ?>
            <tr>
                <td><input type="checkbox" id="cekbox" name="cekbox[]" value="<?php echo $data['no_anggota'] ?>"/></td>
                <td><?php echo $data['no_anggota'] ?></td>
				<td><?php echo $data['Cust_Short_name'] ?></td>
                <td><?php echo $data['unit'] ?></td>
				<td><?php echo $data['nama_kel']?></td>
				<td><?php echo number_format (compat_num($data['Plafond'])) ?></td>
				<td><?php echo number_format (compat_num($data['bagi_hasil'])) ?></td>
				<td><?php echo number_format (compat_num($data['bulat'])) ?></td>
             	<td><?php echo number_format (compat_num($data['pokok'])) ?></td>
				<td><?php echo number_format (compat_num($data['ijaroh']))?></td>
				<td><?php echo number_format (compat_num($data['ref'])) ?></td>
            </tr>
    
    <?php
    
	}

    ?>
    </table>
    <br />
    <input type="button" class="alert-success" onclick="cek(this.form.cekbox)" value="Select All" />
    <input type="button" class="btn-danger" onclick="uncek(this.form.cekbox)" value="Clear All" />
    <input name="submit" type="submit" class="btn-success" value="Realisasi Tagihan" />
		
	    <br />
		
</form>
