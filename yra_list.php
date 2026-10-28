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
$buss_date1= $_POST['buss_date1'];
$buss_date2= $_POST['buss_date2'];

$gb=rtrim('gb1');


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
 If ($_GET ['code_kel']){
$code_kel=$_GET ['code_kel'];
$tanggal=$_GET ['tanggal'];

$date=date('Y-m-d');	
$query = "Select * from calon_loan where code_kel='$code_kel' and status_app='MURAB' and tgl_akad ='$tanggal'";
$result = mysql_query($query);
?>
<form action="realisasi_pinjaman.php" method="post">
    <table border="1" style="margin: 10px 0px" class="table table-striped" >
            <tr>
                <th><span class="badge"> PILIH</th>
<th><span class="badge"> No</th>
                <th><span class="badge"> KDE KELOMPOK</th>
                <th><span class="badge">NAMA</th>
                <th><span class="badge">JATUH TEMPO</th>
			    <th><span class="badge">PEMBIAYAAN</th>
			    <th><span class="badge">MARGIN</th>
			    <th><span class="badge">ANGSURAN</th>
			    <th><span class="badge">TANGGAL MURABAHAH</th>
				<th><span class="badge">UNIT</th>
				 
			            </tr>
    <?php 
$no=0;
    while ($data = mysql_fetch_array($result)) {
$unit=$data['unit'];
$tot_pla +=$data['Plafond'];
$margin +=$data['bagi_hasil'];
$angsuran +=$data['angsuran'];
$no++;
    ?>
            <tr>
                <td><input type="checkbox" id="cekbox" name="cekbox[]" value="<?php echo $data['no_anggota'] ?>"/></td>
<td><?php echo $no ?></td>
                <td><?php echo $data['no_anggota'] ?></td>
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
	}

    ?>
<?php
       
        ///$total=mysql_query("select sum(Plafond) as tot_pla,sum(bagi_hasil) as margin,sum(angsuran) as angsuran from calon_loan where code_kel ='$code_kel' and unit ='$unit' and status_app ='MURAB' group by code_kel ");
        //while($row_tran=mysql_fetch_array($total)){
            //$tot_pla=$row_tran['tot_pla'];
            //$margin=$row_tran['margin'];
            //$angsuran=$row_tran['angsuran'];
        //}
            ?>
    <tr>
                <td colspan="5"><div align="center"><strong>TOTAL TRANSAKSI</strong></div></td>
                <td align="right"><strong><?php echo number_format(compat_num($tot_pla)); ?></strong></td>
                <td align="right"><strong><?php echo number_format(compat_num($margin)); ?></strong></td>
                <td align="right"><strong><?php echo number_format(compat_num($angsuran)); ?></strong></td>
                <td align="right"><strong><?php ; ?></strong></td>
                <td align="right"><strong><?php ; ?></strong></td>
            </tr>
    </table>
    <br />
    <input type="button" class="alert-success" onclick="cek(this.form.cekbox)" value="Select All" />
    <input type="button" class="btn-danger" onclick="uncek(this.form.cekbox)" value="Clear All" />
	<input type="hidden" name="ds" id "ds" value=" <?php echo $ds ?>" >
    <input name="submit" type="submit" class="btn-success" value="Realisasi Pembiayaan" />
		
	    <br />
		
</form>
