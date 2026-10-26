<?php require_once __DIR__ . '/php8_compat.php';
session_start();  
ob_start();
ini_set('display_errors',1);
include "conn2.php";
include "indo_tgl.php";
$koneksi=open_connection2();
$tgl_mrb=$_SESSION['smrb'];
$userid=$_SESSION['userid'];
$unit = $userid;


$ckel= mysql_query(" Select cao  from kelompok where code_unit='2004'");
$cckel= mysql_fetch_array($ckel);
$cao=$cckel['cao'];


$dataum= mysql_query(" Select Nama as um,jabatan from person  
where unit='$unit'");
$um= mysql_fetch_array($dataum);
$nama_um=$um['um'];
$jabatan=$um['jabatan'];
//$feed = 1;

$pot = mysql_query("select calon_loan.cif as CIF,bidang_usaha.nama_usaha usaha_anggota, maturity_date,calon_loan.Cust_Short_name,tgl_wakalah,Plafond,bagi_hasil,Tenor,angsuran,ID_NUMBER,HADDRESS1,HADDRESS2,HADDRESS3,HADDRESS4,month(tgl_akad) as bulan,year(tgl_akad) as tahun, ELT(WEEKDAY(tgl_akad)+1, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu') AS hari, day(tgl_akad) as hari_angka,tgl_akad,bulat
 from calon_loan left join anggota on anggota.cif = calon_loan.cif 
 left join bidang_usaha on bidang_usaha.code_bidang = calon_loan.usaha
 where calon_loan.unit='$userid' and calon_loan.status_app='MUSYAROKAH' and tgl_akad='$tgl_mrb' order by calon_loan.code_kel,calon_loan.cust_Short_name");
$page=0;
require ('fpdf17/fpdf.php');
$pdf=new FPDF ('P','mm','A4');
$pdf->SetMargins(10, 10, 10);
$pdf->SetAutoPageBreak(true, 10);

while($p = mysql_fetch_array($pot)){
$page++;
$pdf->addPage();
$nama=$p['Cust_Short_name'];
$plafond=$p['Plafond'];
$pla=number_format (compat_num($p['Plafond']));
$tenor =$p['Tenor'];
$cif =$p['CIF'];
$ktp =$p['ID_NUMBER'];
$hari=$p['hari'];
$hari_angka=$p['hari_angka'];
$usaha_anggota=$p['usaha_anggota'];
$bulan=$p['bulan'];
$bulat=number_format(compat_num($p['bulat']));
$tahun_full=$p['tahun'];
$row_sal= mysql_fetch_array(mysql_query("select nisbah from nisbah WHERE cif ='$cif'")) or die(mysql_error());
$nisbah=$row_sal['nisbah'];
$persen_nisbah = number_format(compat_num($nisbah * 100), 2) . ' %';
$komplementer = 1 - $nisbah; // Hasil: 0.925 (92.5%)
$persen_koprasi = number_format(compat_num($komplementer * 100), 2) . ' %';

//$tahun=substr($tahun,2);
$nomor="$userid.$cif.$hari_angka$bulan$tahun_full";
$angsuran =number_format (compat_num($p['angsuran']));
$untung =$p['bagi_hasil'];
$margin = number_format (compat_num($p['bagi_hasil']));
$alamat1=$p['HADDRESS1'];
$alamat2=$p['HADDRESS2'];
$alamat3=$p['HADDRESS3'];
$alamat4=$p['HADDRESS4'];
$alamat ="$alamat1 $alamat2 $alamat3 $alamat4";
$akad=$p['tgl_akad'];
$tgl_akad=tgl_indo($akad);
$tgl_jatpo=tgl_indo($p['maturity_date']);
$piutang =$plafond + $untung;
$pdf->cell(75,5,' ',0,'L',1);
$pdf->Image('Image/nii.png');
$pdf->Ln(1);
$pdf->SetFont('Courier','B',12);
$pdf->Cell (0, 6, 'PEMBIAYAAN AKAD MUSYARAKAH', 0, 1,"C");
$pdf->SetFont('Courier','',12);
$pdf->Cell(0, 6, 'Nomor : '.$nomor.'', 0 , 1,"C");

$pdf->Ln(1);
$pdf->SetFont('Courier','',11);
$pdf->MultiCell(0, 6, "Dengan memohon petunjuk dan ridha Allah, pada hari ini $hari tanggal $tgl_akad, yang bertanda tangan di bawah ini :", 0);

$pdf->Ln(1);
$pdf->MultiCell(0, 6, "Nama $nama_um jabatan Marketing Manager (MM) dalam hal ini bertindak untuk dan atas nama Koperasi Simpan Pinjam dan Pembiayaan Syariah (KSPPS) Nur Insani, yang berkedudukan di kota Tangerang Selatan berdasarkan Akta Pendirian No 02 tanggal 7 bulan Mei tahun 2012 melalui notaries Neilly Iralita Iswari, SH, MSi, MKn dan telah memperoleh persetujuan Kementerian Koperasi dan Usaha Kecil dan Menengah No. 1024/BH/M.KUM.2/XI/2012, serta dengan perubahan-perubahannya, selanjutnya disebut sebagai PIHAK PERTAMA.", 0);

$pdf->Ln(1);
$pdf->write(6, "Nyonya ");
$pdf->SetFont('Courier','B',11);
$pdf->write(6, $nama);
$pdf->SetFont('Courier','',11);
$pdf->write(6," bertempat tinggal di $alamat Pemegang KTP dengan NIK $ktp selanjutnya disebut sebagai PIHAK KEDUA.", 0);
$pdf->Ln(6);
$pdf->MultiCell(0, 6, "PIHAK PERTAMA dan PIHAK KEDUA selanjutnya bersama-sama disebut 'Para Pihak', sepakat untuk membuat dan menandatangani pembiayaan Musyarakah, selanjutnya disebut 'Akad' dengan syarat dan ketentuan sebagai berikut:", 0);
$pdf->Ln(1);
$pdf->SetFont('Courier','B',11);
$pdf->Cell(0, 6, "PASAL 1", 0, 1,'C');
$pdf->SetFont('Courier','B',10);
$pdf->Cell(0, 6, "KETENTUAN POKOK AKAD", 0, 1,'C');
$pdf->SetFont('Courier','',11);
$pdf->MultiCell(0, 6, "1. Ketentuan-ketentuan pokok Akad ini meliputi sebagai berikut : :
Jenis Usaha                        : $usaha_anggota
Modal Pihak Pertama                : $pla
Persentase Nisbah Pihak Pertama    : $persen_nisbah
Persentase Nisbah Pihak Kedua      : $persen_koprasi
Jumlah Setoran Mingguan            : $bulat
Hari Pembayaran Setoran            : $hari
Jangka Waktu Pembiayaan            : $tenor Minggu
Tanggal Jatuh Tempo Pembiayaan     : $tgl_jatpo
", 0);
$pdf->MultiCell(0, 6, "Setoran mingguan terdiri atas porsi pemgembalian modal,bagihasil pihak pertama, dana ta'awun, dan tabungan wajib mingguan ", 0);
$pdf->MultiCell(0, 6, "2. Ta'awun adalah dana bersama yang dikelola oleh Pihak Pertama untuk kemaslahatan anggota ", 0);

$pdf->Ln(1);
$pdf->SetFont('Courier','B',11);
$pdf->Cell(0, 6, "PASAL 2", 0, 1,"C");
$pdf->SetFont('Courier','B',11);
$pdf->Cell(0, 6, "JENIS USAHA", 0, 1,"C");
$pdf->SetFont('Courier','',11);
$pdf->MultiCell(0, 6, "Usaha sebagaimana dimaksud Pasal 1 adalah aktifitas yang tidak bertentangan dengan prinsip-prinsip syariah.", 0);

$pdf->Ln(1);
$pdf->SetFont('Courier','B',11);
$pdf->Cell(0, 6, "PASAL 3", 0, 1,"C");
$pdf->SetFont('Courier','B',11);
$pdf->Cell(0, 6, "MODAL PIHAK PERTAMA", 0, 1,"C");
$pdf->SetFont('Courier','',10);
$pdf->MultiCell(0, 6, "PIHAK KEDUA meminta kepada PIHAK PERTAMA untuk menyediakan tambahan modal. PIHAK PERTAMA memberikan modal kepada PIHAK KEDUA sesuai dengan pertimbangan PIHAK PERTAMA.", 0);

$pdf->Ln(1);
$pdf->SetFont('Courier','B',11);
$pdf->Cell(0, 6, "PASAL 4", 0, 1,"C");
$pdf->SetFont('Courier','B',12);
$pdf->Cell(0, 6, "SYARAT PEMBERIAN MODAL", 0, 1,"C");
$pdf->SetFont('Courier','',11);
$pdf->MultiCell(0, 6, "PIHAK KEDUA telah menyerahkan semua persyaratan yang diminta kepada PIHAK PERTAMA;\nPIHAK PERTAMA dan PIHAK KEDUA telah menandatangani akad ini.", 0);

$pdf->MultiCell(0, 6, "PIHAK KEDUA dengan ini menyatakan menerima modal secara sah dan PIHAK PERTAMA adalah partner usaha.", 0);

$pdf->Ln(1);
$pdf->SetFont('Courier','B',11);
$pdf->Cell(0, 6, "PASAL 5", 0, 1,"C");
$pdf->SetFont('Courier','B',11);
$pdf->Cell(0, 6, "KUASA", 0, 1,"C");
$pdf->SetFont('Courier','',11);
$pdf->MultiCell(0, 6, "PIHAK PERTAMA memberikan kuasa kepada PIHAK KEDUA untuk menjalankan usaha sesuai rencana. PIHAK KEDUA bertindak sebagai wakil usaha.", 0);

$pdf->Ln(1);
$pdf->SetFont('Courier','B',11);
$pdf->Cell(0, 6, "PASAL 6", 0, 1,'C');
$pdf->SetFont('Courier','B',11);
$pdf->Cell(0, 6, "SURAT PERNYATAAN SUKA RELA", 0, 1,'C');
$pdf->SetFont('Courier','',11);
$pdf->MultiCell(0, 6, "
- Akan membayar angsuran tepat waktu sesuai kesepakatan.
- Tidak menggunakan pembiayaan ini untuk pihak lain. Bila melanggar, tetap wajib membayar.
- Tidak melibatkan pihak luar untuk mangkir. Bila dilanggar, siap dilaporkan ke pihak berwajib.
", 0);

$pdf->Ln(1);
$pdf->SetFont('Courier','B',11);
$pdf->Cell(0, 6, "PASAL 7", 0, 1,'C');
$pdf->SetFont('Courier','B',11);
$pdf->Cell(0, 6, "PERHITUNGAN AKHIR PELUNASAN", 0, 1,'C');
$pdf->SetFont('Courier','',11);
$pdf->MultiCell(0, 6, "Pada akhir pelunasan akan dihitung ulang. Jika bagi hasil berlebih, dikembalikan ke PIHAK KEDUA. Jika kurang, PIHAK KEDUA dapat menambahkannya secara sukarela.", 0);

$pdf->Ln(1);
$pdf->SetFont('Courier','B',10);
$pdf->Cell(0, 6, "PASAL 8", 0, 1,'C');
$pdf->SetFont('Courier','B',11);
$pdf->Cell(0, 6, "TANDA TERIMA UANG", 0, 1,'C');
$pdf->SetFont('Courier','',11);
$pdf->MultiCell(0, 6, "Dengan menandatangani Akad ini, PIHAK KEDUA mengakui telah menerima uang dari PIHAK PERTAMA yang besarnya sesuai dengan yang tercantum dalam pasal 1 Akad ini yang sekaligus sebagai bukti Tanda Terima Uang.", 0);

$pdf->Ln(1);
$pdf->SetFont('Courier','B',11);
$pdf->Cell(0, 6, "PASAL 9", 0, 1,'C');
$pdf->SetFont('Courier','B',11);
$pdf->Cell(0, 6, "PASAL TAMBAHAN", 0, 1,'C');
$pdf->SetFont('Courier','',11);
$pdf->MultiCell(0, 6, "Hal-hal yang belum diatur akan dibuat secara terpisah dan menjadi satu kesatuan dengan akad ini.", 0);

$pdf->Ln(1);
$pdf->MultiCell(0, 6, "Demikian perjanjian ini dibuat dan ditandatangani di $alamat4 pada hari $hari tanggal $tgl_akad.", 0);
$pdf->Ln(1);

$pdf->Cell(100, 6, "PIHAK PERTAMA", 0, 0, 'C');
$pdf->Cell(0, 6, "PIHAK KEDUA", 0, 1, 'C');

$pdf->Ln(28);
$pdf->Cell(100, 6, "($nama_um)", 0, 0, 'C');
$pdf->Cell(0, 6, "($nama)", 0, 1, 'C');
$pdf->Ln(1);
$pdf->cell(100,3,'Marketing Manager',0,0,'C');
$pdf->Ln(8);
$pdf->cell(100,6,'KETUA KELOMPOK',0,0,'C');
$pdf->Ln(20);
$pdf->Cell(100, 6, "(_______________________)", 0, 0, 'C');
$pdf->Ln(1);



}
$pdf->Output();
?>
