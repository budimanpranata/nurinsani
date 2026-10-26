<?php require_once __DIR__ . '/koneksi_db.php'; 
//error_reporting(0);
session_start();  
ob_start();  
$userid=$_SESSION['userid'];
//ekspor xls
function xlsBOF() {
echo pack("ssssss", 0x809, 0x8, 0x0, 0x10, 0x0, 0x0);
return;
}

function xlsEOF() {
echo pack("ss", 0x0A, 0x00);
return;
}

function xlsWriteNumber($Row, $Col, $Value) {
echo pack("sssss", 0x203, 14, $Row, $Col, 0x0);
echo pack("d", $Value);
return;
}
 
function xlsWriteLabel($Row, $Col, $Value ) {
$L = strlen($Value);
echo pack("ssssss", 0x204, 8 + $L, $Row, $Col, 0x0, $L);
echo $Value;
return;
}
?>
<?php 
//memanggil fungsi


//koneksi ke database dan jalankan query
db_mobcol();
mysql_select_db('mobcol');

//ambil query dari sql yang dikirim oleh form
$sql = "SELECT tagihan_pelunasan.tgl_tagih as tgl_tagih,tagihan_pelunasan.nama_kel as nama_kel,nama_ao,pb,ke,tagihan_pelunasan.code_kel as kode_kel,twm,simpanan_pokok, simpanan_wajib, tagihan_pelunasan.nama as nama, tagihan_pelunasan.cif as cif, tagihan_pelunasan.bulat as bulat, pelunasan.nominal as nominal, status_trans from tagihan_pelunasan left join pelunasan on pelunasan.cif = tagihan_pelunasan.cif
left join ao on ao.cao = tagihan_pelunasan.cao where tagihan_pelunasan.unit ='$userid'
";


// masukkan sql ke dalam query
$result = mysql_query($sql);
!$result?die(mysql_error()):'';

//pengaturan nama file
$namaFile = "cs_export.xls";
//pengaturan judul data
$judul = "REPORT CS PELUNASAN KSPPS NUR INSANI";
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
xlsWriteLabel($tablehead,$kolomhead++,"Tgl Tagih");  
xlsWriteLabel($tablehead,$kolomhead++,"KELOMPOK");  
xlsWriteLabel($tablehead,$kolomhead++,"KOde Kelompok"); 
xlsWriteLabel($tablehead,$kolomhead++,"AO");      
xlsWriteLabel($tablehead,$kolomhead++,"CIF"); 
xlsWriteLabel($tablehead,$kolomhead++,"NAMA");  
xlsWriteLabel($tablehead,$kolomhead++,"twm");
xlsWriteLabel($tablehead,$kolomhead++,"pokok");
xlsWriteLabel($tablehead,$kolomhead++,"wajib");
xlsWriteLabel($tablehead,$kolomhead++,"KE");
xlsWriteLabel($tablehead,$kolomhead++,"PB");
            
xlsWriteLabel($tablehead,$kolomhead++,"Tagihan");
xlsWriteLabel($tablehead,$kolomhead++,"Nyata");
xlsWriteLabel($tablehead,$kolomhead++,"status Transaksi");



while ($data = mysql_fetch_array($result))
{
$kolombody = 0;

//gunakan xlsWriteNumber untuk penulisan nomor dan xlsWriteLabel untuk penulisan string
xlsWriteNumber($tablebody,$kolombody++,$nourut);
xlsWriteLabel($tablebody,$kolombody++,$data['tgl_tagih']);
xlsWriteLabel($tablebody,$kolombody++,$data['nama_kel']);
xlsWriteLabel($tablebody,$kolombody++,$data['kode_kel']);
xlsWriteLabel($tablebody,$kolombody++,$data['nama_ao']);
xlsWriteLabel($tablebody,$kolombody++,$data['cif']);
xlsWriteLabel($tablebody,$kolombody++,$data['nama']);
xlsWriteNumber($tablebody,$kolombody++,$data['twm']);
xlsWriteNumber($tablebody,$kolombody++,$data['simpanan_pokok']);
xlsWriteNumber($tablebody,$kolombody++,$data['simpanan_wajib']);
xlsWriteLabel($tablebody,$kolombody++,$data['ke']);
xlsWriteLabel($tablebody,$kolombody++,$data['pb']);

xlsWriteNumber($tablebody,$kolombody++,$data['bulat']);
xlsWriteNumber($tablebody,$kolombody++, $data['nominal']);
xlsWriteLabel($tablebody,$kolombody++, $data['status_trans']);

$tablebody++;
$nourut++;
}

xlsEOF();
exit();
?>

