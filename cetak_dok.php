<?php require_once __DIR__ . '/php8_compat.php';  
session_start();  
ob_start();
$nopen = abs((int)$_GET['nopen']);  
include "conn2.php";
$koneksi=open_connection2();
$userid=$_SESSION['userid'];
$result = mssql_query(" Select userid,nama from user where userid='$userid'");
while($row = mysql_fetch_array($result)){
$nama=$row['nama'];
}


$query= mssql_query("select Branch.Branch as kode_cabang,Branch.Keterangan as Cabang,No_Pegawai,Outstanding as OS,konstan_payment, Haddress1,Haddress3,Haddress4,Cust_Full_name
FROM LNMOE 
Inner Join Branch ON LNMOE.Branch=Branch.Branch
where No_Pegawai ='$nopen' ");  
$data   = mssql_fetch_array($query); 
  $nama=$data['Cust_Full_name'];
  $sisa=$data['OS'];
  $alamat=$data['Haddress1'];
  $alamat2=$data['Haddress3'];
  $alamat3=$data['Haddress4'];
  $branch=$data['Cabang'];
  $kode_cabang=$data['kode_cabang'];
  
$querysmo  = mssql_query("SELECT * FROM smo WHERE KDS='$kode_ds'");  
$ds   = mssql_fetch_array($querysmo);  
$smo=$ds['smo'];
$tlp_smo=$ds['tlp_smo'];

?>  
<html xmlns="http://www.w3.org/1999/xhtml"> <!-- Bagian halaman HTML yang akan konvert -->  
<head>  
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />  
<title><?php echo $data['no_Pegawai']; ?></title>  
<link rel="stylesheet" href="../simulasii/simulasi/tabel.css" />
</head>  
<body> 
<?php  
echo "<p align='center'> <b> AKTA PENGAKUAN PINJAMAN DAN KUASA</b></p>";
echo "<p align='center'> <b>(Untuk Pensiunan)</b></p>";
echo '<table border="1">  
  <tr>  
    <td width="700"><b>Nomor Aplikasi Kredit Pensiun Terdahulu</b></td>  
    </tr>
  </table>'; 
echo '<table border="0">  
 <tr>
    <td width="400">Kreditur</td>
    <td width="600">Kode Cabang</td>
     </tr>
  <tr>
    <td width="400">Cabang :</td>
    </tr>
  <tr>
     <td width="400">Alamat Cabang :</td>
    </tr>
  <tr>
     <td width="400">Kode Sales :</td>
      <td width="800">Nama Sales :</td>
     </tr>
   </table>'; 
 echo '<table border="1">  
  <tr>  
    <td width="700"><b>Data Pribadi</b></td>  
    </tr>
  </table>'; 
 echo "<b>A. Data Debitur</b>";
echo '<table border="0">  
 <tr>
    <td width="400">Nama Debitur Sesuai KTP</td>
    <td width="800">Jenis Kelamin</td>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td width="400">Nomor KTP :</td>
	<td width="800">Berlaku s.d</td>
    </tr>
 <tr>
    <td width="400">Alamat Sesuai KTP :</td>
    </tr>
  <tr>
    <td width="400">Kelurahan :</td>
	<td width="800">Kecamatan</td>
	</tr>
<tr>
    <td width="400">Kota :</td>
	<td width="800">Kode Pos : </td>
	</tr>
<tr>
    <td width="400">Nomor Tlp Rumah :</td>
	<td width="800">Nomor Tlp HP  : </td>
	</tr>
<tr>
    <td width="400">Tempat Tanggal Lahir :</td>
	<td width="800">Usia  : </td>
	</tr>
<tr>
    <td width="400">Pendidikan:</td>
	<td width="800">Nomor NPWP  : </td>
	</tr>
<tr>
    <td width="400">Status Perkawinan :</td>
	<td width="800">Nama Gadis Ibu Kandung  : </td>
	</tr>
 <tr>
 </tr>  </table>';
 echo '<table border="1">  
  <tr>  
    <td width="700"><b>Data Pensiun</b></td>  
    </tr>
  </table>'; 
 echo '<table border="0">  
 <tr>
    <td width="400">Jenis Pensiun</td>
    <td width="800">Kode Jiwa</td>
    </tr>
  <tr>
    <td width="400">Nomor Skep Pensiun :</td>
	<td width="800">Penerbit :</td>
    </tr>
 <tr>
    <td width="400">Tanggal SKEP :</td>
    </tr>
  <tr>
    <td width="400">Nama Pengelola Dana Pensiun :</td>
	<td width="800">Nama Kantor Bayar : </td>
	</tr>
  </table>';
 echo '<table border="1">  
  <tr>  
    <td width="700"><b>Data Keuangan</b></td>  
    </tr>
  </table>'; 
 echo '<table border="0">  
 <tr>
    <td width="400">Manfaat THT</td>
    <td width="800">Manfaat Pensiun</td>
    </tr>
  <tr>
    <td width="400">Pendapatan Tambahan :</td>
	<td width="800">Pendapatan Sendiri :</td>
    </tr>
  <tr>
    <td width="400">b.Pendapatan Pasangan:</td>
<td width="800"><b>Total Pendapatan Per Bulan:</b></td>
    </tr>
  </table>';
  echo '<table border="1">  
  <tr>  
    <td width="700"><b>STRUKTUR KREDIT</b></td>  
    </tr>
  </table>'; 
 echo '<table border="0">  
 <tr>
    <td width="400">Kode Produk</td>
    <td width="800">Jenis Pengajuan</td>
    </tr>
  <tr>
    <td width="400">Jenis Penggunaan:</td>
	<td width="800">Sektor Ekonomi :</td>
    </tr>
 <tr>
    <td width="400">Jangka Waktu Kredit :</td>
 	<td width="800">Suku Bunga:</td>
    </tr>
<tr>
    <td width="400">Denda Pelunasan Sebelum Jatuh Tempo:</td>
    </tr>
<tr>
    <td width="400">Angsuran Perbulan:</td>
    </tr>
<tr>
    <td width="400">Kepesetaan Asuransi:</td>
    </tr>
<tr>
    <td width="400"><b>Jumlah Kredit Yang di Setujui Biaya Kredit:</b></td>
    </tr>
<tr>
    <td width="400">Biaya Tata Laksana:</td>
 <td width="800">Administrasi:</td>
    </tr>
<tr>
    <td width="400">Premi Asuransi:</td>
 	<td width="800">Materai:</td>
    </tr>
 </table>';
?>    
</body>  
</html><!-- Akhir halaman HTML yang akan di konvert -->  
<?php  
$filename="btpn-".$nopen.".pdf"; //
//==========================================================================================================  
//==========================================================================================================  
$content = ob_get_clean();  
$content = '<page style="font-family: freeserif">'.nl2br($content).'</page>';  
 require_once(dirname(__FILE__).'/html2pdf/html2pdf.class.php');  
 try  
 {  
  $html2pdf = new HTML2PDF('P','A4','en', false, 'ISO-8859-15',array(10, 0, 4, 0));  
  $html2pdf->setDefaultFont('Arial');  
  $html2pdf->writeHTML($content, isset($_GET['vuehtml']));  
  $html2pdf->Output($filename);  
 }  
 catch(HTML2PDF_exception $e) { echo $e; }  

?> 