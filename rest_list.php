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
//if(empty($ds)||empty($limit)||empty($sd)||empty($lurah)){
        
        //echo "<script>alert('Maaf data Anda belum lengkap');</script>";
         //echo "<script language='javascript'>document.location.href='view.php'</script>";}
        //else {
		
				//$query = mysql_query("SELECT COUNT(NOPEN) as counter FROM feed_back WHERE FEEB_BACK= 'P' AND KODE_SALES='$ds'");
				//$check = mysql_fetch_array($query);
				//$feed=$check['counter'];
				//}
//if ($feed>=10) {

// jika data ditemukan
//echo "<script> alert ('Maaf anda tidak bisa membuka menu ini, silahkan Update SALES FEEDBACK terlebih dahulu')</script>";

   //echo "<script language='javascript'>document.location.href='view_feed.php'</script>";
		 //}

        //else {

//$query = "SELECT NOPEN,NAMA,ALAMAT,KOTA,KELURAHAN,TGL_LAHIR,USIA,PLAFOND,GBAYAR FROM potensi WHERE NOT EXISTS (Select NOPEN From feed_back WHERE feed_back.NOPEN=tempfeed.NOPEN) AND KOTA like '%$kota%' AND KELURAHAN like '%$lurah%' AND GBAYAR='$gb' ORDER By ALAMAT LIMIT $limit, $sd  ";

$date=date('Y-m-d');	
$query = "Select loan.cif,unit,Plafond,maturity_date,ijaroh,bagi_hasil,bulat,pokok,Cust_Short_name,kelompok.nama_kel,(os-saldo_margin) as pokok, saldo_margin, os from loan
inner join kelompok on kelompok.code_kel = loan.code_kel
Where kelompok.code_kel='$code_kel' and loan.unit='$unit' and os >0";
$result = mysql_query($query);
?>
<form action="realisasi_rest_kel.php" method="post">
    <table border="1" style="margin: 10px 0px" class="table table-striped" >
            <tr>
               <th><span class="badge"> Pilih</th>
               <th><span class="badge"> Cif</th>
               <th><span class="badge">Nama</th>
               <th><span class="badge">Kelompok</th>
			   <th><span class="badge">Pembiayaan</th>
			   <th><span class="badge">Saldo Margin</th>
			   <th><span class="badge">Pokok</th>
			   <th><span class="badge">Jatpo</th>
			   
			  
				
			            </tr>
    <?php 
    while ($data = mysql_fetch_array($result)) {
		$cif =$data['cif'];
		
    ?>
            <tr>
                <td><input type="checkbox" id="cekbox" name="cekbox[]" value="<?php echo $data['cif'] ?>"/></td>
                <td><?php echo $data['cif'] ?></td>
				<td><?php echo $data['Cust_Short_name'] ?></td>
                <td><?php echo $data['nama_kel'] ?></td>
				<td><?php echo number_format(compat_num($data['os']))?></td>
				<td><?php echo number_format (compat_num($data['saldo_margin'])) ?></td>
				<td><?php echo number_format (compat_num($data['os']-$data['saldo_margin'])) ?></td>
				<td><?php echo tgl_indo($data['maturity_date']) ?></td>
				 
				
            

            </tr>
    
    <?php
    
	}

    ?>
    </table>
    <br />
    <input type="button" class="alert-success" onclick="cek(this.form.cekbox)" value="Select All" />
    <input type="button" class="btn-danger" onclick="uncek(this.form.cekbox)" value="Clear All" />
    <input name="realisasi" type="submit" class="btn-success" value="realisasi" />
	
	
		
	    <br />
		
</form>
