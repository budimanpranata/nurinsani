<?php require_once __DIR__ . '/php8_compat.php';
session_start();
ob_start();
$userid=$_SESSION['userid'];
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

$sql1=mysql_query("Select nama,kode_branch FROM user WHERE userid='$userid'");
$dsmo= mysql_fetch_array($sql1);
$unit=$dsmo['kode_branch'];	
$query = "Select * from calon_loan where unit ='$unit' and status_app in ('APPROVE','MUSYAROKAH') order by code_kel, Cust_Short_name ";
$result = mysql_query($query);
?>
<form action="realisasi_batal.php" method="post">
    <table border="1" style="margin: 10px 0px" class="table table-striped" >
            <tr>
                <th><span class="badge"> PILIH</th>
                <th><span class="badge"> KDE KELOMPOK</th>
<th><span class="badge"> CIF</th>
                <th><span class="badge">NAMA</th>
                <th><span class="badge">JATUH TEMPO</th>
			    <th><span class="badge">PEMBIAYAAN</th>
			    <th><span class="badge">MARGIN</th>
			    <th><span class="badge">ANGSURAN</th>
			    <th><span class="badge">TANGGAL MURABAHAH</th>
				<th><span class="badge">UNIT</th>
				 
			            </tr>
    <?php 
    while ($data = mysql_fetch_array($result)) {
    ?>
            <tr>
                <td><input type="checkbox" id="cekbox" name="cekbox[]" value="<?php echo $data['cif'] ?>"/></td>
                <td><?php echo $data['code_kel'] ?></td>
<td><?php echo $data['cif'] ?></td>
				<td><?php echo $data['Cust_Short_name'] ?></td>
                <td><?php echo tgl_indo ($data['maturity_date']) ?></td>
				<td><?php echo number_format (compat_num($data['Plafond'])) ?></td>
				<td><?php echo number_format (compat_num($data['bagi_hasil'])) ?></td>
				<td><?php echo number_format (compat_num($data['angsuran'])) ?></td>
             	<td><?php echo tgl_indo ($data['tgl_murab']) ?></td>
				<td><?php echo $data['unit'] ?></td>
            </tr>
    
    <?php

	}

    ?>
    </table>
    <br />
    <input type="button" class="alert-success" onclick="cek(this.form.cekbox)" value="Select All" />
    <input type="button" class="btn-danger" onclick="uncek(this.form.cekbox)" value="Clear All" />
	<input type="hidden" name="ds" id "ds" value=" <?php echo $ds ?>" >
    <input name="submit" type="submit" class="btn-success" value="Batal" />
		
	    <br />
		
</form>
