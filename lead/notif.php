<?php require_once __DIR__ . '/../php8_compat.php';
session_start(); 
ob_start();
include "conn.php";
include "conn2.php";
$koneksi=open_connection();
$koneksi2=open_connection2();
$userid=$_SESSION['userid'];
$result = mssql_query("select 
nama,tlp FROM tabeluser where UserId ='$userid'");
  while($row = mssql_fetch_array($result)){
$nama=$row['nama'];
$tlp=$row['tlp'];
}

?>
<style type="text/css">
<!--
.style1 {
	color: #FFFFFF;
	font-weight: bold;
}
-->
</style>

<div class="container">
    <div class="form-horizontal well">
    <table width="100%" cellpadding="1" bordercolor="#CCCCCC" bgcolor="#CCCCCC">
      <tr>
        <td height="20" bordercolor="#FFFFFF" bgcolor="#776a56" class="style3 style8"><span class="style1">Kirim SMS Disbursement</span>
          <div align="right"></div></td>
      </tr>
    </table>            
<fieldset>
                <legend style="font-size:12px;"><i class=" icon-user"></i>&nbsp; Upload Disburse Nnew</legend>
                <?php
//proses input 
if (isset($_POST['upload'])) {    
// menggunakan class phpExcelReader
include "excel_reader2.php";
 
// membaca file excel yang diupload
$data = new Spreadsheet_Excel_Reader($_FILES['userfile']['tmp_name']);
 
// membaca jumlah baris dari data excel
$baris = $data->rowcount($sheet_index=0);
// nilai awal counter untuk jumlah data yang sukses dan yang gagal diimport
$sukses = 0;
$gagal = 0;

 
// import data excel mulai baris ke-2 (karena baris pertama adalah nama kolom)
for ($i=2; $i<=$baris; $i++)
{
  // membaca data per-kolom
 
 $kode_ds= $data->val($i,6);
 $nama_smo= $data->val($i,7);
$nama_sms= $data->val($i,8);
$nama_bsm= $data->val($i,9);
$nama_ash= $data->val($i,10);
$data_noa= $data->val($i,11);
$data_vol= $data->val($i,12);
$buss_date = date("Y-m-d");
$sql= "INSERT INTO psr (buss_date,kode_ds,nama_smo,nama_sms,nama_bsm,ash,noa,vol) VALUES ('$buss_date','$kode_ds','$nama_smo','$nama_sms','$nama_bsm','$nama_ash','$data_noa','$data_vol')";
 $smsblast=mysql_query($sql);
if ($smsblast) $sukses++;
  else $gagal++;
}
if ($sukses){
$query="select count(noa) as jumlah, sum(vol) as Volume, psr.kode_ds as smo, smo.no_tlp as tlp from psr inner join smo on psr.kode_ds = smo.kode_ds group by psr.kode_ds";
$hasil = mysql_query($query);
	  while ($data = mysql_fetch_array($hasil))
	  {
		$no_tlp = $data['tlp'];
		$noa = $data['jumlah'];
		$vol = $data['volume'];
		$smo = $data['smo'];
	  
$selisih=7-$noa;
$pesan="Anda sampai saat ini mendapatkan $noa dengan total Volume Rp.$vol masih kurang $selisih NoA";
$sql= "INSERT INTO outbox (DestinationNumber, TextDecoded, CreatorID) VALUES ('$no_tlp', '$pesan', 'Gammu')";
$kirimsms=mysql_query($sql);
}
}
else{
echo "gagal";
}


echo "<h2>Proses import data ke Outbox selesai.</h2>";
echo "<p>Jumlah data yang sukses diimport : ".$sukses."<br>";
echo "Jumlah data yang gagal diimport : ".$gagal."</p>";

}


// tampilan status sukses dan gagal
?>
        <div style="font:9px/24px Verdana,Arial,Helvetica,sans-serif;">
            <form method="post" enctype="multipart/form-data" name="upload" action="">
                <fieldset>    
                    <label>Silakan Pilih File (xls): </label>
                        <input name="userfile" type="file" class="btn">
                        <span class="help-block">&nbsp;</span>
                        <input type="submit" name="upload" value="import" onclick="return confirm('Apakah Anda yakin dengan data ini?')"  class="btn btn-primary" />
              </fieldset>
            </form>
        </div>
    </div>
</div>
</fieldset>