<?php require_once __DIR__ . '/php8_compat.php';  
session_start();  
ob_start();
$nopen = $_GET['nopen'];  
include "conn2.php";
include "indo_tgl.php";
$koneksi=open_connection2();
$userid=$_SESSION['userid'];
$result = mysql_query(" Select userid,nama from user where userid='$userid'");
while($row = mysql_fetch_array($result)){
$nama1=$row['nama'];

}

$query1= mysql_query("select No,stgloan.BUSS_DATE,CIF,stgloan.CUST_SHORT_NAME,stgloan.BRANCH,stgloan.DEAL_TYPE,stgloan.TENOR AS TENOR,stgloan.PLAFOND AS PLAFOND,JENIS_PENS,HADDRESS1,HADDRESS2,HADDRESS3,HADDRESS4,HPOST_CODE,stgloan.NOREK as NOREK,
MOBILE_PHONE,HOME_PHONE,GENDER,stgloan.BIRTH_DATE,ID_NUMBER,CITIZENSHIP,MARITAL_STATUS,RELIGION,MOTHER_NAME,NPWP,SOURCE_INCOME,EDUCATION,TEMPAT_LAHIR,ID_EXPIRED,stgloan.CONTRACT_DATE,MATURITY_DATE,stgloan.Rel_Ob_Mgr_1,smo.smo as SMO,loan_ps.asuransi as asuransi,stgcasa.KODE_JIWA as KODE_JIWA, stgcasa.NOSKEP as NOSKEP,stgcasa.PENERBIT as PENERBIT, stgcasa.TGL_SKEP as TGL_SKEP,
loan_ps.bersih as bersih,loan_ps.btl as btl,loan_ps.angsuran as angsuran,branch.nama as BRANCH_NAME,Suku_Bunga_flat AS BUNGA,loan_ps.Gaji_Bersih AS GAJI,loan_ps.DAPEN as DAPEN, stgloan.APPROVE as APPROVE 
FROM stgloan
inner join smo on stgloan.Rel_Ob_Mgr_1=smo.kode_ds 
inner join loan_ps on loan_ps.No_Pegawai=stgloan.No_Pegawai 
inner join branch on stgloan.BRANCH=branch.kode_branch
inner join stgcasa on stgcasa.NO_PENSIUN=stgloan.No_Pegawai
inner join user on user.userid=stgloan.approve
where stgloan.No_Pegawai ='$nopen' ");  
while ($data1 = mysql_fetch_array($query1)){ 
  $nama=$data1['CUST_SHORT_NAME'];
  $branch=$data1['BRANCH'];
$type=$data1['DEAL_TYPE'];
$PLA= $data1['PLAFOND'];
$tenor=$data1['TENOR'];
$jenis=$data1['JENIS_PENS'];
$kode_pos=$data1['HPOST_CODE'];
$cif=$data1['CIF'];
$gender=$data1['GENDER'];
$ID_NUMBER =$data1['ID_NUMBER'];
$ID_EXPIRED=tgl_indo ($data1['ID_EXPIRED']);  
$Rel_Ob_Mgr_1=$data1['Rel_Ob_Mgr_1'];
$HADDRESS1=$data1['HADDRESS1'];
$HADDRESS2=$data1['HADDRESS2'];
$HADDRESS3=$data1['HADDRESS3'];
$HADDRESS4=$data1['HADDRESS4'];
$TEMPAT_LAHIR =$data1['TEMPAT_LAHIR'];
$BIRTH_DATE=$data1['BIRTH_DATE'];
$MOBILE_PHONE=$data1['MOBILE_PHONE'];
$EDUCATION=$data1['EDUCATION'];
$NPWP=$data1['NPWP'];
$MARITAL_STATUS=$data1['MARITAL_STATUS'];
$ibu=$data1['MOTHER_NAME'];
$JENIS_PENS=$data1['JENIS_PENS'];
$smo=$data1['SMO'];
$matur1=$data1['MATURITY_DATE'];
$asuransi=number_format (compat_num($data1['asuransi']));
$btl =number_format (compat_num($data1['btl']));
$bersih=number_format (compat_num($data1['bersih']));
$angsuran= number_format (compat_num($data1['angsuran']));
$BRANCH_NAME=$data1['BRANCH_NAME'];
$flat=$data1['BUNGA'];
$CONTRACT_DATE=$data1['CONTRACT_DATE'];
$No=$data1['No'];
$GAJI=number_format (compat_num($data1['GAJI']));
$KODE_JIWA=$data1['KODE_JIWA'];
$NOSKEP=$data1['NOSKEP'];
$PENERBIT=$data1['PENERBIT'];
$TGL_SKEP=tgl_indo ($data1['TGL_SKEP']);
$DAPEN=$data1['DAPEN'];
$NOREK=$data1['NOREK'];
$atasan=$data1['APPROVE'];
} 	
$date=tgl_indo($CONTRACT_DATE);
$BIRTH_DATE=tgl_indo($BIRTH_DATE);
$lahir="$TEMPAT_LAHIR, $BIRTH_DATE";
$matur=tgl_indo($matur1);
$BIRTH_DATE=tgl_indo($$BIRTH_DATE);
$bank='KCP';
$nama_bank="$bank $BRANCH_NAME";
$bunga = ($flat/100);
$tambah = ($bunga/12)+1; 
$lama = - $tenor;
$pgt = pow($tambah,$lama);
$anuitas = ($bunga*$PLA/12)/(1-$pgt);
$hasil=number_format(compat_num($anuitas),2);
$PLA1=number_format(compat_num($PLA));
    

?>  

<?php
require ('fpdf17/fpdf.php');
//$pdf=new FPDF ('P','mm','A4');
//$pdf=new FPDF('P','in',array(15,12)); 
//$pdf=new FPDF('P','mm', array(216, 330));
$pdf=new FPDF('P','mm', array(241, 279));
$pdf->addPage();
$pdf->SetFont ('Arial','B',9);
 $pdf->Ln(6);
$pdf->setX(11);
$pdf->setFillColor(255,255,255);
$pdf-> Cell (0,5,'PERJANJIAN JUAL BELI ( AL MURABAAHAH)',0,0,'C',1);
$pdf->Ln(6);
$pdf->cell(0,5,'BISMILLAHIRRAHMAANIRROHIIM',0,0,'C',1);
$pdf->Ln(6);
$pdf->cell(0,5,'Hai Orang Orang Yang beriman penuhilah akad-akad perjanjian itu (QS. Al-Maidah 1)',0,0,'C',1);
$pdf->Ln(6);
$pdf->cell(0,5,'Cukupkanlah takaran jangan kamu menjadi orang-orang yang merugikan (Q.S Asy-syu\'a-ra 181)',0,0,'C',1);
$pdf->Ln(6);
$pdf->cell(0,5,'Dan Allah SWT telah menghalalkan Jual-beli dan mengharamkan Riba (Q.S Albaqarah 275)',0,0,'C',1);
$pdf->Ln(6);
$pdf->SetFont ('Arial','',9);
$pdf->cell(40,5,'Kreditur',0,'L',1);
$pdf->cell(50,5,':'.$nama_bank,0,'L',1);
$pdf->cell(30,5,'Kode Cabang',0,'L',1);
$pdf->cell(20,5,':'.$branch,0,'L',1);
$pdf->Ln(4);
$pdf->cell(40,5,'Kode Sales ',0,'L',1);
$pdf->cell(50,5,': '.$Rel_Ob_Mgr_1,0,'L',1);
$pdf->cell(30,5,'Nama Sales',0,'L',1);
$pdf->cell(20,5,':'.$smo,0,'L',1);
$pdf->Ln(6);
$pdf->SetFont ('Arial','B',9);
$pdf->cell(0,5,'DATA PRIBADI',1,0,'L',1);
$pdf->Ln(6);
$pdf->SetFont ('Arial','',9);
$pdf->cell(40,5,'Nama Debitur sesuai KTP',0,'L',1);
$pdf->cell(50,5,': '.$nama,0,'L',1);
$pdf->cell(30,5,'Jenis Kelamon L/p',0,'L',1);
$pdf->cell(20,5,': '.$gender,0,'L',1);
$pdf->Ln(6);
$pdf->cell(40,5,'Nmor KTP',0,'L',1);
$pdf->cell(50,5,':'.$ID_NUMBER,0,'L',1);
$pdf->cell(30,5,'Berlaku S/d',0,'L',1);
$pdf->cell(20,5,':'.$ID_EXPIRED,0,'L',1);
$pdf->Ln(6);
$pdf->cell(40,5,'Alamat Sesaui KTP',0,'L',1);
$pdf->cell(50,5,':'.$HADDRESS1,0,'L',1);
$pdf->Ln(6);
$pdf->cell(40,5,'Kelurahan',0,'L',1);
$pdf->cell(50,5,':'.$HADDRESS2,0,'L',1);
$pdf->cell(30,5,'Kecamatan',0,'L',1);
$pdf->cell(20,5,':'.$HADDRESS3,0,'L',1);
$pdf->Ln(6);
$pdf->cell(40,5,'Kota',0,'L',1);
$pdf->cell(50,5,':'.$HADDRESS4,0,'L',1);
$pdf->cell(30,5,'Kode Pos',0,'L',1);
$pdf->cell(20,5,':'.$kode_pos,0,'L',1);

$pdf->Ln(6);
$pdf->cell(40,5,'Tempat Tanggal Lahir',0,'L',1);
$pdf->cell(50,5,': '.$lahir,0,'L',1);
$pdf->cell(30,5,'No. Tlp HP	',0,'L',1);
$pdf->cell(20,5,':'.$MOBILE_PHONE,0,'L',1);
$pdf->Ln(6);
$pdf->cell(40,5,'Pendidikan',0,'L',1);
$pdf->cell(50,5,':'.$EDUCATION,0,'L',1);
$pdf->cell(30,5,'No. NPMW ',0,'L',1);
$pdf->cell(20,5,':'.$NPWP,0,'L',1);
$pdf->Ln(6);
$pdf->cell(40,5,'Status Perkawinan',0,'L',1);
$pdf->cell(50,5,':'.$MARITAL_STATUS,0,'L',1);
$pdf->cell(30,5,'Nama Ibu Kandung',0,'L',1);
$pdf->cell(20,5,':'.$ibu,0,'L',1);
$pdf->Ln(6);
$pdf->SetFont ('Arial','B',9);
$pdf->cell(0,5,'DATA PENSIUNAN',1,0,'L',1);
$pdf->Ln(5);
$pdf->SetFont ('Arial','',9);
$pdf->cell(40,5,'Jenis Pensiun :',0,'L',1);
$pdf->cell(50,5,': '.$JENIS_PENS,0,'L',1);

$pdf->Ln(5);


$pdf->cell(40,5,'Kode Jiwa',0,'L',1);
$pdf->cell(50,5,': '.$KODE_JIWA,0,'L',1);
$pdf->Ln(5);
$pdf->cell(40,5,'Nomor Skep Pensiun',0,'L',1);
$pdf->cell(50,5,': '.$NOSKEP,0,'L',1);
$pdf->Ln(5);
$pdf->cell(40,5,'Penerbit',0,'L',1);
$pdf->cell(50,5,': '.$PENERBIT,0,'L',1);
$pdf->Ln(5);
$pdf->cell(40,5,'Tanggal SKEP ',0,'L',1);
$pdf->cell(50,5,': '.$TGL_SKEP,0,'L',1);
$pdf->Ln(5);
$pdf->cell(40,5,'Pengelola Dana Pensiun',0,'L',1);
$pdf->cell(50,5,': '.$DAPEN,0,'L',1);

$pdf->Ln(5);
$pdf->cell(40,5,'Nama Kantor Bayar',0,'L',1);
$pdf->cell(50,5,': '.$nama_bank,0,'L',1);
$pdf->Ln(6);
$pdf->SetFont ('Arial','B',9);
$pdf->cell(0,5,'DATA KEUANGAN',1,0,'L',1);
$pdf->Ln(5);
$pdf->SetFont ('Arial','',9);
$pdf->cell(40,5,'Manfaat Pensiun',0,'L',1);
$pdf->cell(50,5,': '.$GAJI,0,'L',1);

$pdf->Ln(5);
$pdf->cell(40,5,'Pendapatan Sendiri',0,'L',1);
$pdf->cell(50,5,': '.$GAJI,0,'L',1);

$pdf->Ln(5);
$pdf->cell(40,5,'Pendapatan Pasangan: '.'0',0,'L',1);
$pdf->cell(50,5,': 0'.'0',0,'L',1);
$pdf->Ln(5);
$pdf->cell(40,5,'Total Pendapatan /bulan',0,'L',1);
$pdf->cell(50,5,': '.$GAJI,0,'L',1);
$pdf->Ln(6);
$pdf->SetFont ('Arial','B',9);
$pdf->cell(0,5,'STRUKTUR KREDIT' ,1,0,'L',1);
$pdf->Ln(5);
$pdf->SetFont ('Arial','',9);
$pdf->cell(0,5,'Kode Produk : '.$type,0,'L',1);
$pdf->Ln(5);
$pdf->cell(40,5,'Nasabah',0,'L',1);
$pdf->cell(50,5,': '.$nama,0,'L',1);
$pdf->cell(30,5,'Jenis Pengajuan',0,'L',1);
$pdf->cell(20,5,': NEW',0,'L',1);
$pdf->Ln(5);
$pdf->cell(40,5,'Jenis Penggunaan ',0,'L',1);
$pdf->cell(50,5,': ',0,'L',1);
$pdf->Ln(5);
$pdf->cell(40,5,'Sektor Ekonomi: ',0,'L',1);
$pdf->cell(50,5,': ',0,'L',1);

$pdf->Ln(5);
$pdf->cell(40,5,'Jangka Waktu Kredit',0,'L',1);
$pdf->cell(15,5,': '.$tenor,0,'L',1);
$pdf->cell(35,5,'Bulan',0,'L',1);
$pdf->cell(30,5,'Sampai dengan:',0,'L',1);
$pdf->cell(20,5,': '.$matur,0,'L',1);

$pdf->Ln(5);
$pdf->cell(40,5,'Suku Bunga: ',0,'L',1);
$pdf->cell(15,5,': '.$flat,0,'L',1);
$pdf->cell(35,5,'%',0,'L',1);

$pdf->Ln(5);
$pdf->cell(40,5,'Angsuran dimulai tanggal',0,'L',1);
$pdf->cell(50,5,':'.$BUSSDATE,0,'L',1);
$pdf->cell(30,5,'Sampai dengan',0,'L',1);
$pdf->cell(20,5,''.$matur,0,'L',1);
$pdf->Ln(5);
$pdf->cell(40,5,'Kepesertaan asuransi: ',0,'L',1);
$pdf->cell(50,5,': '.$ass,0,'L',1);
$pdf->Ln(5);
$pdf->cell(70,5,'Jumlah Kredit Yang disetujui Biaya Kredit',0,'L',1);
$pdf->cell(30,5,': Rp',0,'R',1);
$pdf->cell(30,5,''.$PLA1,0,'L',1);
$pdf->Ln(4);
$pdf->cell(70,5,'Tata Laksana ',0,'L',1);
$pdf->cell(30,5,': Rp ',0,'L',1);
$pdf->cell(20,5,''.$btl,0,0,'R',1);
$pdf->Ln(4);
$pdf->cell(70,5,'Administrasi ',0,'L',1);
$pdf->cell(30,5,': Rp',0,'R',1);
$pdf->cell(20,5,''.$adm,0,0,'R',1);
$pdf->Ln(4);
$pdf->cell(70,5,'Premi Asuransi ',0,'L',1);
$pdf->cell(30,5,': Rp',0,'R',1);
$pdf->cell(20,5,''.$asuransi,0,0,'R',1);
$pdf->Ln(4);
$pdf->cell(70,5,'Materai ',0,'L',1);
$pdf->cell(30,5,': Rp',0,'R',1);
$pdf->cell(20,5,''.$materai,0,0,'R',1);
$pdf->Ln(4);
$pdf->cell(70,5,'Total ',0,'L',1);
$pdf->cell(30,5,': Rp',0,'R',1);
$pdf->cell(20,5,''.$total,0,0,'R',1);
$pdf->Ln(12);
$pdf->SetFont ('Arial','B',9);
$pdf->cell(0,5,'PERSETUJUAN KREDIT' ,1,0,'L',1);
$pdf->Ln(6);
$pdf->SetFont ('Arial','',9);
$pdf->cell (70,40,'',1,0,'L',1);
$pdf->Ln(0);
$pdf->cell (70,5,'Petugas BANK ',0,'',1);
$pdf->cell (125,20,'Tanda Tangan',1,'0',1);
$pdf->Ln(5);
$pdf->cell (30,5,'User ID ',0,'L',1);
$pdf->cell (20,5,':'.$userid,0,'L',1);
$pdf->Ln(5);
$pdf->cell (30,5,'Nama ',0,'L',1);
$pdf->cell (20,5,':'.$nama1,0,'L',1);
$pdf->Ln(5);
$pdf->cell (30,5,'Tanggal ',0,'L',1);
$pdf->cell (20,5,':'.$date,0,'L',1);
$pdf->Ln(5);
$pdf->cell (70,5,'PEJABAT BANK',0,'L',1);
$pdf->cell (125,20,'Tanda Tangan',1,'0',1);
$pdf->Ln(5);
$pdf->cell (30,5,'Nama ',0,'L',1);
$pdf->cell (20,5,':'.$atasan,0,'L',1);
$pdf->Ln(5);
$pdf->cell (30,5,'Tanggal ',0,'L',1);
$pdf->cell (20,5,':'.$date,0,'L',1);

//$pdf-> Write (12,'User ID: ');
$pdf->Ln(30);
$pdf->SetFont ('Arial','B',9);
$pdf->cell(0,5,'PERJANJIAN KREDIT' ,1,0,'C',1);
$pdf->Ln(6);
$pdf->SetFont ('Arial','',9);
$pdf->multicell(0,5,'KREDITUR dan DEBITUR telah saling setuju dan sepakat untuk membuat Perjanjian Kredit ini dengan ketentuan dan persyaratan sebagai berikut : ',0,'J',1);
$pdf->Ln(6);
$pdf->MultiCell(0, 5, '1.	Berdasrkan persyaratan dan ketentuan yang tertuang dalam Struktur Kredit dan Perjanjian Kredit Pensiun – syarat dan ketuan umum,KREDITUR setuju untuk memberikan Fasilitas Kredit Pensiun kepada DEBITUR dan DEBITUR setuju untuk menerima Fasilitas Kredit Pensiun dari Kreditur.','');
$pdf->MultiCell(0,5,'');
$pdf->MultiCell(0, 5, '2.	DEBITUR dengan ini telah setuju dan menerima Struktur Kredit  sebagai mana yang tertuang dalam Persetujuan Kredit diatas. Struktur Kredit tersebut tunduk dan merupakan satu kesatuan dan bagian yang tidak terpisahkan dari perjanjian kredit dan Perjanjian Kredit Pensiun – Syarat dan Ketentuan Umum.','');
$pdf->MultiCell(0,5,'');
$pdf->MultiCell(0,5,  '3.	Debitur dengan ini menyatakan tunduk dan terikat pada Dokumen Kredit.','');
$pdf->MultiCell(0,5,'');
$pdf->MultiCell(0,5	,'4.	Terhadap Perjanjian Kredit dan Perjanjian Kredit Pensiun - Syarat dan Ketentuan Umum ini DEBITUR dan KREDITR setuju untuk memilih tempat kedudukan hukum yang tetap dan seumumnya di Kantor Kepaniteraan Pengadilan Negeri Jakarta Selatan tanpa mengurangi hak dan kewenangan KREDITR untuk memohon pelaksanaan (eksekusi) atau mengajukan tuntutan/gugatan hukum terhadaap DEBITUR dimuka pengadilan lain dalam wilayah Republik Indonesia.','');
$pdf->MultiCell(0,5,'');
$pdf->MultiCell(0,5,'5.	Dengan menandatangani Perjanjian Kredit ini, DEBITUR menyatakan telah menerima informasi, membaca , memahami serta setuju atas Struktur Kredit , Perjanjian Kredit – Syarat dan Ketentuan Umum, dan oleh karenanya DEBITUR mengikatkan diri terhadap Struktur Kredit,Perjanjian Kredit dan Perjanjian Kredit Pensiun  - Syarat dan Ketentuan Umum termasuk perubahan dan/atau perbaharuannya yang akan di beritahukan melalui kantor KREDITUR dan/atau melalui surat yang akan dikirimkan ke alamat DEBITUR diatas dan perubahan tersebut mengikat DEBITUR.','');
$pdf->MultiCell(0,5,'');
$pdf->MultiCell(0,5,'6.	Perjanjian Kredit ini merupakan tanda terima yang sah dari DEBITUR atau pencairan Fasilitas Kredit Pensiun.','');
$pdf->MultiCell(0,5,'');
$pdf->MultiCell(0,5,'7.	Persetujuan & Perjanjian Kredit Pensiun ini merupakan satu kesatuan dan bagian yang tidak terpisahkan dari Perjanjian Kredit Pensiun - Syarat dan Ketentuan Umum.','');
$pdf->MultiCell(0,5,'');
$pdf->MultiCell(0,5,'8.	Kecuali ditentuan lain dalam Perjanjian Kredit , seluruh definisi di dalam perjanjian kredit memiliki pengertian yang sama dengan definisi yang terdapat dalam Perjanjian Kredit Pensiun Syarat dan Ketentuan Umum.','');
$pdf->MultiCell(0,5,'9.	Debitur dengan ini menyatakan dan membrikan kuasa:
a. Semua informasi dan data dalam  Persetujuan dan Perjanjian Kredit Pensiun ini adalah lengkap dan benar dan dengan ini DEBITUR memberikan kuasa kepada KREDITR untuk melakukan pemeriksaan terhadap data dan/atau dokumen tersebut dengan cara apapun yang di anggap layak untuk KREDITUR.
b. KREDITUR telah memberikan penjelasan kepada DEBITUR mengenai karakteristik kredit pensiun yang akan DEBITUR manfaatkan dan DEBITUR telah mengerti dan memahami segala konsekuensinya, termasuk manfaat, resiko dan biaya-biaya yang timbuk terkait dengan Kredit Pensiun tersebut.
','');
$pdf->MultiCell(0,5,'');
$pdf->MultiCell(0,5,'10.	Jumlah Kredit yang disetujui KREDITU akan dikreditkan ke rekening DEBITUR di : ','');
$pdf->MultiCell(0,5,'');
$pdf->cell(70,5,'Nama Bank',0,'L',1);
$pdf->cell(3,5,':',0,'L',1);
$pdf->cell(30,5,''.$nama_bank,0,'R',1);
$pdf->Ln(5);
$pdf->cell(70,5,'Nama Peilik Rekening',0,'L',1);
$pdf->cell(3,5,':',0,'L',1);
$pdf->cell(20,5,''.$nama,0,'R',1);
$pdf->Ln(5);
$pdf->cell(70,5,'Nomor Rekenig ',0,'L',1);
$pdf->cell(3,5,':',0,'L',1);
$pdf->cell(20,5,''.$NOREK,0,'l',1);
$pdf->Ln(5);

$pdf->MultiCell(0,5,'11.	Demikian Perjanjian Kredit ini ditandatangani Oleh Debitur dengan penuh kesadaran dan tanggung jawab, tanpa adanya tekanan dari pihak manapun pada tanggal sebagaimana di sebutkan pada Perjanjian Kredit ini.','');
$pdf->SetFont ('Arial','B',9);
$pdf->cell(80,5,'PIHAK DEBITUR',1,0,'C',1);
$pdf->SetFont ('Arial','B',9);
$pdf->cell(90,5,'PIHAK KREDITUR',1,0,'C',1);
$pdf->Ln(5);
$pdf->SetFont ('Arial','',9);
$pdf->cell(80,5,'Tandatangan (Sesaui KTP)',0,0,'C',1);
$pdf->SetFont ('Arial','',9);
$pdf->cell(90,5,'Pejabat Bank ',0,0,'C',1);

$pdf->Ln(20);
$pdf->SetFont ('Arial','',9);
$pdf->cell(80,5,''.$nama,1,0,'C',1);
$pdf->SetFont ('Arial','',9);
$pdf->cell(90,5,''.$atasan,1,0,'C',1);
$pdf->Ln(6);

$pdf->cell(80,5,'Tanggal'.$date,1,0,'C',1);
$pdf->cell(90,5,'Tanggal:'.$date,1,0,'C',1);

$pdf->Ln(40);


//$pdf->MULTICELL(0,6,$data[0],0,'J',1);   	
//$pdf->MULTICELL(0,6,$data[1],0,'J',1);
$pdf->SetFont ('Arial','B',9);
 $pdf->Ln(6);
$pdf->setX(11);
$pdf->setFillColor(255,255,255);
$pdf-> Cell (0,5,'SCHEDULE ANGSURAN KREDIT PENSIUN',0,0,'C',1);
$pdf->Ln(6);
$pdf->SetFont ('Arial','',9);
$pdf->cell(60,5,'Nasabah',0,'L',1);
$pdf->SetFont ('Arial','',9);
$pdf->cell(50,5,':'.$nama,0,'L',1);
$pdf->Ln(4);
$pdf->cell(60,5,'Tanggal Lunas',0,'L',1);
$pdf->SetFont ('Arial','',9);
$pdf->cell(50,5,':'.$matur,0,'L',1);
$pdf->Ln(4);
$pdf->cell(60,5,'Plafond',0,'L',1);
$pdf->SetFont ('Arial','',9);
$pdf->cell(50,5,':'.$PLA1,0,'L',1);
$pdf->Ln(8);

$pdf->SetFont ('Arial','B',9);
$pdf->Cell(20,5,'Bulan Ke',1,0,'C',1); // cell dengan panjang 1
$pdf->Cell(50,5,'Plafond',1,0,'C',1); // cell dengan panjang 1
$pdf->Cell(40,5,'Pokok',1,0,'C',1); // cell dengan panjang 2
$pdf->Cell(30,5,'Bunga',1,0,'C',1); // cell dengan panjang 3
$pdf->Cell(40,5,'saldo',1,0,'C',1); // cell dengan panjang 3
$pdf->Ln(6);

$bk=0;
$akhir=$PLA;  
 do {
    $bk++;
    $b=($akhir*$bunga)/12;
    $angsur=($anuitas-$b);
    $akhir=($akhir-$angsur);
    $hab=($akhir+$angsur);
    $hb=number_format(compat_num($b),2);
    $hsur=number_format(compat_num($angsur),2);
    $hhir=number_format(compat_num($akhir),2);
    $hhab=number_format(compat_num($hab),2);
$pdf->SetFont ('Arial','',9);
$pdf->Cell(20,5,''.$bk,1,0,'L',1); // cell dengan panjang 1
  $pdf->Cell(50,5,''.$hhab,1,0,'L',1); // cell dengan panjang 1
  $pdf->Cell(40,5,''.$hb,1,0,'L',1); // cell dengan panjang 2
  $pdf->Cell(30,5,''.$hsur,1,0,'L',1); // cell dengan panjang 3
  $pdf->Cell(40,5,''.$hhir,1,0,'L',1); // cell dengan panjang 3
  $pdf->Ln(6);
//$pdf->multicell(10,6,$bk, 1,0,'J',1);
//$pdf->multicell(70,6,$hhab,1,0,'J',1);   	
//$pdf->multicell(40,6, $hb,1,0,'J',1);
//$pdf->multicell(30,6,$hsur,1,0,'J',1);
//$pdf->multicell(40,6,$hhir,1,0,'J',1);   	
  
 }
while ($bk<$tenor);
$pdf->Output();
?> 
