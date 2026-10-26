<?php require_once __DIR__ . '/../php8_compat.php';
session_start();
ob_start();
$userid=$_SESSION['userid'];
$tgl_jatuh_tempo=$_SESSION['jatuh'];
$cao=$_SESSION['code_cao'];
//cek apakah user sudah login
if(!isset($_SESSION['userid'])){
   header("location:../index.html");
}

//cek level user
if($_SESSION['level']!="toko"){
   header("location:../index.html");
}
 include "../conn2.php";
 include "../indo_tgl.php";
 $koneksi=open_connection2();	
$code_kel=$_GET['kode_kel'];

$sql111=mysql_query("Select nama_ao FROM ao_toko WHERE cao='$cao'");
$ca= mysql_fetch_array($sql111);
$nama_sales=$ca['nama_ao'];


$sql1=mysql_query("Select nama,kode_branch FROM user WHERE userid='$userid'");
$dsmo= mysql_fetch_array($sql1);
$kode_branch=$dsmo['kode_branch'];

$sql2=mysql_query("Select * FROM branch WHERE kode_branch='$kode_branch'");
$br= mysql_fetch_array($sql2);
$nama_unit=$br['unit'];
$alamat=$br['alamat'];

$sql4=mysql_query("Select musawamah.code_kel,nama_kel  FROM musawamah 
inner join kelompok_toko on kelompok_toko.code_kel = musawamah.code_kel WHERE musawamah.code_kel='$code_kel'");
$cd= mysql_fetch_array($sql4);
$nama_kel=$cd['nama_kel'];
$hari=$cd['next_schedule'];
$hari2=tgl_indo($tgl_jatuh_tempo);
$day = date('D', strtotime($tgl_jatuh_tempo));
$dayList = array(
	'Sun' => 'Minggu',
	'Mon' => 'Senin',
	'Tue' => 'Selasa',
	'Wed' => 'Rabu',
	'Thu' => 'Kamis',
	'Fri' => 'Jumat',
	'Sat' => 'Sabtu'
);


$buss_date=date("y-m-d");
//cek apakah user sudah login


require ('fpdf17/fpdf.php');
class PDF_JavaScript extends FPDF {

    var $javascript;
    var $n_js;

    function IncludeJS($script) {
        $this->javascript=$script;
    }

    function _putjavascript() {
        $this->_newobj();
        $this->n_js=$this->n;
        $this->_out('<<');
        $this->_out('/Names [(EmbeddedJS) '.($this->n+1).' 0 R]');
        $this->_out('>>');
        $this->_out('endobj');
        $this->_newobj();
        $this->_out('<<');
        $this->_out('/S /JavaScript');
        $this->_out('/JS '.$this->_textstring($this->javascript));
        $this->_out('>>');
        $this->_out('endobj');
    }

    function _putresources() {
        parent::_putresources();
        if (!empty($this->javascript)) {
            $this->_putjavascript();
        }
    }

    function _putcatalog() {
        parent::_putcatalog();
        if (!empty($this->javascript)) {
            $this->_out('/Names <</JavaScript '.($this->n_js).' 0 R>>');
        }
    }
}
class PDF_AutoPrint extends PDF_JavaScript
{
function AutoPrint($dialog=false)
{
    //Open the print dialog or start printing immediately on the standard printer
    $param=($dialog ? 'true' : 'false');
    $script="print($param);";
    $this->IncludeJS($script);
}

function AutoPrintToPrinter($server, $printer, $dialog=false)
{
    //Print on a shared printer (requires at least Acrobat 6)
    $script = "var pp = getPrintParams();";
    if($dialog)
        $script .= "pp.interactive = pp.constants.interactionLevel.full;";
    else
        $script .= "pp.interactive = pp.constants.interactionLevel.automatic;";
    $script .= "pp.printerName = '\\\\\\\\".$server."\\\\".$printer."';";
    $script .= "print(pp);";
    $this->IncludeJS($script);
}
}
//$pdf=new PDF_AutoPrint('L','mm', array(216, 275));
//$pdf=new FPDF ('L','mm','A4');
//$pdf->AddPage();
//$pdf=new FPDF('L','mm', array(216, 330));
//$pdf->setX(11);
//$pdf->setFillColor(255,255,255);

$pdf=new FPDF('L','mm', array(218, 279));
$pdf->addPage();
$pdf->setTopMargin(15);
//$pdf->setLeftMargin(12);
$pdf->setLeftMargin(2);
$pdf->setFillColor(255,255,255);


$pdf->SetFont('Arial','B',9);

//membuat kop tabel
$pdf->SetFont('Arial','B',8);
$pdf->multicell(0,5,'KOPERASI JASA KEUANGAN SYARIAH NUR INSANI UNIT '.$nama_unit.'',0,'J',1);
$pdf->multicell(0,5,'ALAMAT '.$alamat.'',0,'J',1);
$pdf->SetFont('Arial','BU',8);
$pdf->multicell(0,5,'DAFTAR TAGIHAN PEMBIAYAAN',0,'J',1);
$pdf->Ln(4);
$pdf->SetFont('ARIAL','B',8);
$pdf->Cell(40,5,'Nama Kelompok',0,0,'L');
$pdf->Cell(40,5,': '.$nama_kel,0,0,'L');
$pdf->Ln(5);
$sp="/";
$pdf->Cell(40,5,'Hari/Tanggal',0,0,'L');
$pdf->Cell(40,5,': '.$dayList[$day] .$sp .$hari2,0,0,'L');
$pdf->Ln(5);
$pdf->Cell(40,5,'Nama Petugas',0,0,'L');
$pdf->Cell(40,5,':'.$nama_sales,0,0,'L');
$pdf->Ln(5);
$pdf->Cell(40,5,'UNIT',0,0,'L');
$pdf->Cell(40,5,': '.$nama_unit,0,0,'L');
$pdf->Ln(5);
$pdf->Cell(40,5,'Kode Kelompok',0,0,'L');
$pdf->Cell(40,5,': '.$code_kel,0,0,'L');
$pdf->Ln(10);
$pdf->SetFont('ARIAL','B',8);
$pdf->Cell(8,5,'No',1,0,'C');
$pdf->Cell(25,5,'No.Rek',1,0,'C');
$pdf->Cell(47,5,'Nama Nasabah',1,0,'C');
$pdf->Cell(16,5,'Plafond',1,0,'C');
$pdf->Cell(9,5,'KE',1,0,'C');
 $pdf->Cell(7,5,'PB',1,0,'C');
$pdf->Cell(20,5,'Pembiayaan',1,0,'C');
$pdf->Cell(20,5,'Titipan',1,0,'C');
$pdf->Cell(9,5,'FT',1,0,'C');
$pdf->Cell(15,5,'Tunggakan',1,0,'C');
$pdf->Cell(15,5,'Angsuran',1,0,'C');


$pdf->Cell(15,5,'Nyata',1,0,'C');
$pdf->Cell(10,5,'Absen',1,0,'C');
$pdf->Cell(7,5,'prf',1,0,'C');
$pdf->Ln();
$no=0;
$tot_blt=0;
$sql=mysql_query("select musawamah.Plafond as tot_pla,sum(bulat)as tot_bulat,musawamah.unit,ao_toko.nama_ao,musawamah.id_member,run_tenor,musawamah.cao, musawamah.cust_Short_name,musawamah.Plafond,musawamah.bagi_hasil,sum(musawamah.bagi_hasil) as baghas,bulat,os,musawamah.angsuran,sum(musawamah.angsuran)tot_angsur,musawamah.bulat
 from musawamah
LEFT join member on member.id_cust = musawamah.id_member
left join kelompok_toko on kelompok_toko.code_kel=musawamah.code_kel
LEFT join ao_toko on ao_toko.cao=musawamah.cao where musawamah.code_kel='$code_kel' AND musawamah.hari='$dayList[$day]' and musawamah.cao='$cao' and os > 0 group by musawamah.id_member");

while($result= mysql_fetch_array($sql)){
	$no ++;
 $TGL_KUNJUNGAN = $result['nama_ao'];
$cif = $result['id_member'];
$cao = $result['cao'];
$t=$result['angsuran'];
$cust_Short_name= $result['cust_Short_name'];
$Plafond= number_format (compat_num($result['Plafond']));  
$angsuran = number_format (compat_num($result['angsuran']));
$bulat = number_format (compat_num($result['bulat']));
$unit = $result['unit'];
$ke = $result['ke'];
$bulat = number_format (compat_num($result['bulat']));
$saldo = number_format (compat_num($result['saldo']));
$run_tenor = $result['run_tenor'];
$os =number_format (compat_num($result['os']));
$norek="$cif";
$bagi_hasil = number_format(compat_num($result['bagi_hasil']));
$cek_saldo= mysql_fetch_array(mysql_query("select sum(KREDIT - DEBIT) as saldo from list_toko WHERE id_member='$cif'"));
$saldo =number_format (compat_num($cek_saldo['saldo']));


//query dan arraying
$pdf->SetFont('Arial','',8);
$pdf->Cell(8,5,''.$no,1,'L',1);
$pdf->Cell(25,5,''.$norek,1,'L',1);
$pdf->Cell(47,5,''.$cust_Short_name,1,'L',1);
$pdf->Cell(16,5,''.$Plafond,1,0,'R');
$pdf->Cell(9,5,''.$run_tenor,1,'L',1);
$pdf->Cell(7,5,''.$ke,1,'L',1);
$pdf->Cell(20,5,''.$os,1,0,'R');
$pdf->Cell(20,5,''.$saldo,1,0,'R');
$pdf->Cell(9,5,''.$ft,1,0,'R');
$pdf->Cell(15,5,''.$tg,1,'L',1);
$pdf->Cell(15,5,''.$angsuran,1,0,'R');

$pdf->Cell(15,5,'',1,'L',1);
$pdf->Cell(10,5,'',1,'L',1);
$pdf->Cell(7,5,'',1,'L',1);

$pdf->Ln();
}
$sql2=mysql_query("select sum(os) as biaya,sum(Plafond) as totpla,sum(musawamah.bagi_hasil) as baghas,sum(musawamah.angsuran)tot_angsur,sum(bulat)as tot_bulat
from musawamah
where code_kel='$code_kel' and cao='$cao' and os > 1 group by code_kel");
while($result1= mysql_fetch_array($sql2)){

$tot_angsur+=$result1['tot_angsur'];
$tot_angsur=ceil(compat_num($tot_angsur));
$totpla+=$result1['totpla'];
$baghas=$result1['baghas'];
$biaya=$result1['biaya'];
$tot_blt +=$result1['tot_bulat'];

}
$sql3=mysql_query("select sum(KREDIT - DEBIT) as saldo from list_toko
inner join musawamah on musawamah.id_member = list_toko.id_member 
where  musawamah.code_kel ='$code_kel' and os >0 ");
while($result3= mysql_fetch_array($sql3)){

$saldo1+=$result3['saldo'];

}


$pdf->SetFont('ARIAL','B',8);
$pdf->Cell(80,5,'Jumlah',1,0,'C');
$pdf->Cell(16,5,''.number_format (compat_num($totpla)),1,0,'R');

$pdf->Cell(16,5,'',1,0,'R');
$pdf->Cell(20,5,''.number_format (compat_num($biaya)),1,0,'R');
$pdf->Cell(20,5,''.number_format (compat_num($saldo1)),1,0,'R');
$pdf->Cell(9,5,'',1,0,'R');
$pdf->Cell(15,5,'',1,0,'R');
$pdf->Cell(15,5,''.number_format (compat_num($tot_angsur)),1,0,'R');


$pdf->Ln(9);
$pdf->SetFont('ARIAL','B',8);
$pdf->Cell(160,5,'DISERAHKAN OLEH',0,0,'L');
$pdf->Cell(40,5,'DIPERIKSA OLEH ',0,0,'C');
$pdf->Cell(40,5,'DIKETAHUI OLEH ',0,0,'C');
$pdf->Ln(20,1);
$pdf->Cell(40,5,'Ketua Kelompok',0,0,'L');
$pdf->Cell(120,5,'SAO/AO',0,0,'L');
$pdf->Cell(40,5,'Unit Manager',0,0,'C');
$pdf->Cell(40,5,'GLH',0,0,'C');
$pdf->Ln(9);


//$pdf->AutoPrint(true);
 
$pdf->Output();

?>
