<?php require_once __DIR__ . '/../../koneksi_db.php'; 
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
db_ni();
mysql_select_db('nur_insani');

//ambil query dari sql yang dikirim oleh form
$sql = "SELECT snorek,scif,sunit, CUST_SHORT_NAME,type, sum(KREDIT - DEBIT) as SALDO
FROM simpanan_kp left join anggota on simpanan_kp.scif = anggota.CIF where TYPE ='06' GROUP BY SNOREK,TYPE
";


// masukkan sql ke dalam query
$result = mysql_query($sql);
!$result?die(mysql_error()):'';

//pengaturan nama file
$namaFile = "khusus.xls";
//pengaturan judul data
$judul = "NOMINATIVE SIMPANAN KSPPS NUR INSANI";
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
xlsWriteLabel($tablehead,$kolomhead++,"CIF"); 
xlsWriteLabel($tablehead,$kolomhead++,"NAMA");  
            
xlsWriteLabel($tablehead,$kolomhead++,"SALDO");
xlsWriteLabel($tablehead,$kolomhead++,"JENIS SIMPANAN");


while ($data = mysql_fetch_array($result))
{
$kolombody = 0;

//gunakan xlsWriteNumber untuk penulisan nomor dan xlsWriteLabel untuk penulisan string
xlsWriteNumber($tablebody,$kolombody++,$nourut);
xlsWriteLabel($tablebody,$kolombody++,$data['snorek']);
xlsWriteLabel($tablebody,$kolombody++,$data['scif']);
xlsWriteLabel($tablebody,$kolombody++,$data['CUST_SHORT_NAME']);
xlsWriteNumber($tablebody,$kolombody++,$data['SALDO']);
xlsWriteLabel($tablebody,$kolombody++, 'UMUM');
$tablebody++;
$nourut++;
}

xlsEOF();
exit();
?>

