<?php require_once __DIR__ . '/../koneksi_db.php'; 
//error_reporting(0);
session_start();  
ob_start();  
$userid=$_SESSION['userid'];
$tgl=$_GET['tgl'];
$jenis=$_GET['jenis'];
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

switch($jenis)
{
 case "01":
    $sql = "SELECT eod_tagihan_cs.tgl_tagih as tgl_tagih,eod_tagihan_cs.nama_kel as nama_kel,nama_ao,pb,ke,eod_tagihan_cs.code_kel as kode_kel,os, plafond, twm,simpanan_pokok, simpanan_wajib, eod_tagihan_cs.nama as nama, eod_tagihan_cs.cif as cif, eod_tagihan_cs.bulat as bulat, eod_tagihan_cs.bayar as nominal,status_realisasi as status_trans from eod_tagihan_cs 
left join ao on ao.cao = eod_tagihan_cs.cao where eod_tagihan_cs.unit ='$userid' and eod_tagihan_cs.tgl_tagih='$tgl'";
 $namaFile = "cs_export.xls";
 //pengaturan judul data
 $judul = "REPORT CS KSPPS NUR INSANI";  
break;

case "02":
    $namaFile = "non_c_limapersen_export.xls";
//pengaturan judul data
$judul = "REPORT NON CS LIMA PERSEN KSPPS NUR INSANI";
    $sql = "SELECT eod_tagihan_lima_persen.tgl_tagih as tgl_tagih,eod_tagihan_lima_persen.nama_kel as nama_kel,nama_ao,pb,ke,eod_tagihan_lima_persen.code_kel as kode_kel,os,plafond,twm,simpanan_pokok, simpanan_wajib, eod_tagihan_lima_persen.nama as nama, eod_tagihan_lima_persen.cif as cif, eod_tagihan_lima_persen.bulat as bulat, eod_tagihan_lima_persen.bayar as nominal,status_realisasi as status_trans from eod_tagihan_lima_persen 
left join ao on ao.cao = eod_tagihan_lima_persen.cao where eod_tagihan_lima_persen.unit ='$userid' and eod_tagihan_lima_persen.tgl_tagih='$tgl'";
  
break;
case "03":
    $namaFile = "non_cs_lebaran_export.xls";
//pengaturan judul data
$judul = "REPORT NON CS LEBARAN KSPPS NUR INSANI";
    $sql = "SELECT eod_tagihan_lebaran.tgl_tagih as tgl_tagih,eod_tagihan_lebaran.nama_kel as nama_kel,nama_ao,pb,ke,eod_tagihan_lebaran.code_kel as kode_kel,os,plafond,twm,simpanan_pokok, simpanan_wajib, eod_tagihan_lebaran.nama as nama, eod_tagihan_lebaran.cif as cif, eod_tagihan_lebaran.bulat as bulat, eod_tagihan_lebaran.bayar as nominal,status_realisasi as status_trans from eod_tagihan_lebaran 
left join ao on ao.cao = eod_tagihan_lebaran.cao where eod_tagihan_lebaran.unit ='$userid' and eod_tagihan_lebaran.tgl_tagih='$tgl'";
break;
case "04":
    $namaFile = "non_cs_penarikan_export.xls";
//pengaturan judul data
$judul = "REPORT NON CS PENARIKAN KSPPS NUR INSANI";
    $sql = "SELECT eod_tagihan_penarikan.tgl_tagih as tgl_tagih,eod_tagihan_penarikan.nama_kel as nama_kel,nama_ao,pb,ke,eod_tagihan_penarikan.code_kel as kode_kel,os,plafond,twm,simpanan_pokok, simpanan_wajib, eod_tagihan_penarikan.nama as nama, eod_tagihan_penarikan.cif as cif, eod_tagihan_penarikan.bulat as bulat, eod_tagihan_penarikan.bayar as nominal,status_realisasi as status_trans from eod_tagihan_penarikan 
left join ao on ao.cao = eod_tagihan_penarikan.cao where eod_tagihan_penarikan.unit ='$userid' and eod_tagihan_penarikan.tgl_tagih='$tgl'";
 
break;
case "05":
$namaFile = "non_cs_tunggakan_export.xls";
//pengaturan judul data
$judul = "REPORT NON TUNGGAKAN KSPPS NUR INSANI";
    $sql = "SELECT eod_tagihan_tunggakan.tgl_tagih as tgl_tagih,eod_tagihan_tunggakan.nama_kel as nama_kel,nama_ao,pb,ke,eod_tagihan_tunggakan.code_kel as kode_kel,os,plafond,twm,simpanan_pokok, simpanan_wajib, eod_tagihan_tunggakan.nama as nama, eod_tagihan_tunggakan.cif as cif, eod_tagihan_tunggakan.bulat as bulat, eod_tagihan_tunggakan.bayar as nominal,status_realisasi as status_trans from eod_tagihan_tunggakan 
left join ao on ao.cao = eod_tagihan_tunggakan.cao where eod_tagihan_tunggakan.unit ='$userid' and eod_tagihan_tunggakan.tgl_tagih='$tgl'";
 
  
break;

case "06":
    $namaFile = "non_cs_pelunasan_export.xls";
//pengaturan judul data
$judul = "REPORT NON CS PELUNASAN KSPPS NUR INSANI";
    $sql = "SELECT eod_tagihan_pelunasan.tgl_tagih as tgl_tagih,eod_tagihan_pelunasan.nama_kel as nama_kel,nama_ao,pb,ke,eod_tagihan_pelunasan.code_kel as kode_kel,os, plafond, twm,simpanan_pokok, simpanan_wajib, eod_tagihan_pelunasan.nama as nama, eod_tagihan_pelunasan.cif as cif, eod_tagihan_pelunasan.bulat as bulat, eod_tagihan_pelunasan.bayar as nominal,status_realisasi as status_trans from eod_tagihan_pelunasan 
left join ao on ao.cao = eod_tagihan_pelunasan.cao where eod_tagihan_pelunasan.unit ='$userid' and eod_tagihan_pelunasan.tgl_tagih='$tgl'";
 
  
break;

case "07":
    $namaFile = "non_cs_wo_export.xls";
//pengaturan judul data
$judul = "REPORT NON CS WO KSPPS NUR INSANI";
    $sql = "SELECT tgl_tagih,nama, nama_kel,kode_kel,nama_ao,eod_wo.cif as cif, os as bulat, nominal from eod_wo
left join ao on ao.cao = eod_wo.cao where ao.kode_unit ='$userid' and tgl_tagih='$tgl'";
 
  
break;

 case "08":
    $sql = "SELECT eod_tagihan_cs.tgl_tagih as tgl_tagih,eod_tagihan_cs.nama_kel as nama_kel,nama_ao,pb,ke,eod_tagihan_cs.code_kel as kode_kel,os, plafond, twm,simpanan_pokok, simpanan_wajib, eod_tagihan_cs.nama as nama, eod_tagihan_cs.cif as cif, eod_tagihan_cs.bulat as bulat, eod_tagihan_cs.bayar as nominal,status_realisasi as status_trans, omzet from eod_tagihan_cs 
left join ao on ao.cao = eod_tagihan_cs.cao 
left join eod_omzet on eod_omzet.cif = eod_tagihan_cs.cif 
where eod_tagihan_cs.unit ='$userid' and eod_tagihan_cs.tgl_tagih='$tgl' and eod_omzet.tgl_tagih='$tgl' ";

 $namaFile = "omzet_export.xls";
 //pengaturan judul data
 $judul = "REPORT OMZET KSPPS NUR INSANI";  
break;
default:
    }
 
 

 
// masukkan sql ke dalam query
$result = mysql_query($sql);
!$result?die(mysql_error()):'';
 
//pengaturan nama file

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
xlsWriteLabel($tablehead,$kolomhead++,"Plafond");
xlsWriteLabel($tablehead,$kolomhead++,"Os");  
xlsWriteLabel($tablehead,$kolomhead++,"twm");
xlsWriteLabel($tablehead,$kolomhead++,"pokok");
xlsWriteLabel($tablehead,$kolomhead++,"wajib");
xlsWriteLabel($tablehead,$kolomhead++,"KE");
xlsWriteLabel($tablehead,$kolomhead++,"PB");
            
xlsWriteLabel($tablehead,$kolomhead++,"Tagihan");
xlsWriteLabel($tablehead,$kolomhead++,"Nyata");
xlsWriteLabel($tablehead,$kolomhead++,"Omzet");

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
xlsWriteLabel($tablebody,$kolombody++,$data['plafond']);
xlsWriteLabel($tablebody,$kolombody++,$data['os']);
xlsWriteNumber($tablebody,$kolombody++,$data['twm']);
xlsWriteNumber($tablebody,$kolombody++,$data['simpanan_pokok']);
xlsWriteNumber($tablebody,$kolombody++,$data['simpanan_wajib']);
xlsWriteLabel($tablebody,$kolombody++,$data['ke']);
xlsWriteLabel($tablebody,$kolombody++,$data['pb']);
 
xlsWriteNumber($tablebody,$kolombody++,$data['bulat']);
xlsWriteNumber($tablebody,$kolombody++, $data['nominal']);
xlsWriteNumber($tablebody,$kolombody++, $data['omzet']);
xlsWriteLabel($tablebody,$kolombody++, $data['status_trans']);
 
$tablebody++;
$nourut++;
}
 
xlsEOF();
exit();
?>
