<?php require_once __DIR__ . '/../../koneksi_db.php'; 
//memanggil fungsi
include 'fungsi.php';

//koneksi ke database dan jalankan query
db_ni();
mysql_select_db('nur_insani');

//ambil query dari sql yang dikirim oleh form
$sql = isset($_POST['sql'])?$_POST['sql']:'';

// masukkan sql ke dalam query
$result = mysql_query($sql);
!$result?die(mysql_error()):'';

//pengaturan nama file
$namaFile = "fund.xls";
//pengaturan judul data
$judul = "NOMINATIVE DEPOSITO KSPPS NUR INSANI";
//baris berapa header tabel di tulis
$tablehead = 2;
//baris berapa data mulai di tulis
$tablebody = 3;
//no urut data
$nourut = 1;

//penulisan header
header("Pragma: public");
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0,pre-check=0");
header("Content-Type: application/force-download");
header("Content-Type: application/octet-stream");
header("Content-Type: application/download");
header("Content-Disposition: attachment;filename=".$namaFile."");
header("Content-Transfer-Encoding: binary ");


xlsBOF();
 
xlsWriteLabel(0,0,$judul);  

$kolomhead = 0;
xlsWriteLabel($tablehead,$kolomhead++,"NOMOR");
xlsWriteLabel($tablehead,$kolomhead++,"NOREK"); 
xlsWriteLabel($tablehead,$kolomhead++,"BILYET");             
xlsWriteLabel($tablehead,$kolomhead++,"CIF"); 
xlsWriteLabel($tablehead,$kolomhead++,"NAMA");  
xlsWriteLabel($tablehead,$kolomhead++,"ALAMAT");            
xlsWriteLabel($tablehead,$kolomhead++,"NOMINAL");
xlsWriteLabel($tablehead,$kolomhead++,"START DATE");
xlsWriteLabel($tablehead,$kolomhead++,"JATUH TEMPO");
xlsWriteLabel($tablehead,$kolomhead++,"JANGKA WAKTU");
xlsWriteLabel($tablehead,$kolomhead++,"RASIO");
xlsWriteLabel($tablehead,$kolomhead++,"TABUNGAN");

while ($data = mysql_fetch_array($result))
{
$kolombody = 0;

//gunakan xlsWriteNumber untuk penulisan nomor dan xlsWriteLabel untuk penulisan string
xlsWriteNumber($tablebody,$kolombody++,$nourut);
xlsWriteLabel($tablebody,$kolombody++,$data['norek']);
xlsWriteLabel($tablebody,$kolombody++,$data['bilyet']);
xlsWriteLabel($tablebody,$kolombody++,$data['cif']);
xlsWriteLabel($tablebody,$kolombody++,$data['CUST_SHORT_NAME']);
xlsWriteLabel($tablebody,$kolombody++,$data['HADDRESS1']);
xlsWriteNumber($tablebody,$kolombody++,$data['nominal']);
xlsWriteLabel($tablebody,$kolombody++,$data['star_date']);
xlsWriteLabel($tablebody,$kolombody++,$data['maturity_date']);
xlsWriteLabel($tablebody,$kolombody++,$data['tenor']);
xlsWriteLabel($tablebody,$kolombody++,$data['rasio']);
xlsWriteNumber($tablebody,$kolombody++,$data['saldo']);
$tablebody++;
$nourut++;
}

xlsEOF();
exit();
?>
