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
<link href="css/font-awesome.min.css" rel="stylesheet">	

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
 If ($_GET ['code_kel'])
{
	$code_kel=$_GET['code_kel'];
$date=date('Y-m-d');	
$query = "Select * from loan where code_kel='$code_kel' ";
$result = mysql_query($query);
?>
<form action="yrd_auto.php" method="post">
	<div class = "panel panel-default">
	  
    <table border="0.9" style="margin: 7px 10px" class="table table-striped" >
            <tr>
                <th><span class="badge"> PILIH</th>
                <th><span class="badge"> NOPEN</th>
                <th><span class="badge">NAMA</th>
               <th><span class="badge">JATUH TEMPO</th>
			   <th><span class="badge">BUNGA</th>
			    <th><span class="badge">POKOK</th>
			      <th><span class="badge">PRODUK</th>
				 <th><span class="badge">TANGGAL LUNAS</th>
				 <th><span class="badge">UNIT</th>
				 <th><span class="badge">ANGSURAN</th>
			            </tr>
    <?php 
    while ($data = mysql_fetch_array($result)) {
    ?>
            <tr>
                <td><input type="checkbox" id="cekbox" name="cekbox[]" value="<?php echo $data['no_anggota'] ?>"/></td>
                <td><?php echo $data['code_kel'] ?></td>
				<td><?php echo $data['Cust_Short_name'] ?></td>
                <td><?php echo tgl_indo ($data['tgl_wakalah']) ?></td>
				<td><?php echo number_format (compat_num($data['ijaroh'])) ?></td>
				<td><?php echo number_format (compat_num($data['pokok'])) ?></td>
             	<td><?php echo $data['hari'] ?></td>
				<td><?php echo tgl_indo ($data['tgl_murab']) ?></td>
				<td><?php echo $data['unit'] ?></td>
				<td> <input type="text" name="margin" value ="<?php echo number_format (compat_num($data['pokok'])); ?>" id="margin" size="6" maxlength="6"/> </td>
            </tr>
    
    <?php
    }
	}

    ?>
    </table>
    <br />
    <input type="button" class="alert-success" onclick="cek(this.form.cekbox)" value="Select All" />
    <input type="button" class="btn-danger" onclick="uncek(this.form.cekbox)" value="Clear All" />
	<input type="hidden" name="ds" id "ds" value=" <?php echo $ds ?>" >
    <input name="submit" type="submit" class="btn-success" value="Realisasi Tagihan" />
	</div>
		</div>
			
	    <br />
		
</form>
