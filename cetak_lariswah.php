<?php require_once __DIR__ . '/php8_compat.php';
session_start();
ob_start();
$userid=$_SESSION['userid'];
$tgl_jatuh_tempo=$_SESSION['jatuh'];
$cao=$_SESSION['code_cao'];
//cek apakah user sudah login
if(!isset($_SESSION['userid'])){
   header("location:index.html");
}

//cek level user
if($_SESSION['level']!="unit"){
   header("location:index.html");
}
 include "conn2.php";
 include "indo_tgl.php";
 $koneksi=open_connection2();	
$code_kel = $_GET['code_kel']; 



$sql1=mysql_query("Select nama,kode_branch FROM user WHERE userid='$userid'");
$dsmo= mysql_fetch_array($sql1);
$kode_branch=$dsmo['kode_branch'];


@mysql_query("delete from total_laris where unit='$kode_branch'");

$sql2=mysql_query("Select * FROM branch WHERE kode_branch='$kode_branch'");
$br= mysql_fetch_array($sql2);
$nama_unit=$br['unit'];
$alamat=$br['alamat'];

$sql4=mysql_query("Select nama_kel,calon_loan.code_kel,next_schedule,calon_loan.cao, tgl_akad  FROM calon_loan 
left join kelompok on kelompok.code_kel = calon_loan.code_kel WHERE calon_loan.code_kel='$code_kel' and status_app in('MURAB','MUSYAROKAH')");
$cd= mysql_fetch_array($sql4);
$nama_kel=$cd['nama_kel'];
$cao=$cd['cao'];
$hari=$cd['next_schedule'];
$tgl_akad=$cd['tgl_akad'];
$hari2=tgl_indo($tgl_akad);
$day = date('D', strtotime($tgl_akad));
$dayList = array(
	'Sun' => 'Minggu',
	'Mon' => 'Senin',
	'Tue' => 'Selasa',
	'Wed' => 'Rabu',
	'Thu' => 'Kamis',
	'Fri' => 'Jumat',
	'Sat' => 'Sabtu'
);
$sql111=mysql_query("Select nama_ao FROM ao WHERE cao='$cao'");
$ca= mysql_fetch_array($sql111);
$nama_sales=$ca['nama_ao'];



$buss_date=date("y-m-d");
//cek apakah user sudah login
if(!isset($_SESSION['userid'])){
   header("location:../index.html");//jika belum login jangan lanjut..
}

//cek level user
if($_SESSION['level']!="unit"){
     header("location:../index.html");//jika bukan user jangan lanjut
}

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
$pdf=new FPDF ('P','mm','A4');
$pdf->AddPage();
//$pdf=new FPDF('L','mm', array(216, 330));
$pdf->SetFont ('Arial','',10);
$pdf->setTopMargin(15);
$pdf->setLeftMargin(12);
$pdf->setFillColor(255,255,255);
$pdf->SetFont('Arial','B',10);
$pdf->multicell(0,5,'KOPERASI SIMPAN PINJAM DAN PEMBIAYAAN SYARIAH NUR INSANI AREA PEMASARAN '.$nama_unit.'',0,'J',1);
$pdf->multicell(0,5,'ALAMAT '.$alamat.'',0,'J',1);
$pdf->SetFont('Arial','BU',9);
$pdf->multicell(0,5,'DAFTAR SIMPANAN 5 %',0,'J',1);
$pdf->Ln(4);
$pdf->SetFont('ARIAL','',10);
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
$pdf->Cell(40,5,'Area Pemasaran',0,0,'L');
$pdf->Cell(40,5,': '.$nama_unit,0,0,'L');
$pdf->Ln(5);
$pdf->Cell(40,5,'Kode Kelompok',0,0,'L');
$pdf->Cell(40,5,': '.$code_kel,0,0,'L');
$pdf->Ln(10);
$pdf->Ln(10);



$pdf->SetFont('ARIAL','B',9);
$pdf->Cell(8,7,'No',1,0,'C');
$pdf->Cell(25,7,'No.Rek',1,0,'C');
$pdf->Cell(47,7,'Nama Anggota',1,0,'C');
$pdf->Cell(25,7,'Plafond',1,0,'C');
$pdf->Cell(25,7,'Simpanan 5%',1,0,'C');
$pdf->Cell(25,7,'TTD',1,0,'C');
$pdf->Ln();
$no=0;
$tot_blt=0;
$sql=mysql_query("select cif,no_anggota,Plafond as tot_pla,cust_Short_name,tgl_wakalah from calon_loan
where code_kel='$code_kel' and status_app in ('MURAB','MUSYAROKAH')");

		
		 
while($result= mysql_fetch_array($sql)){
	$no ++;
 $cust_Short_name= $result['cust_Short_name'];
 $norek= $result['no_anggota'];
 $Pla= $result['tot_pla'];  
 $tgl_wakalah= tgl_indo($result['tgl_wakalah']); 
$Plafond= number_format (compat_num($result['tot_pla']));  
$persen= number_format (compat_num($result['tot_pla']*5/100));
$persen1=  ($result['tot_pla']*5/100);
$tot_persen +=$persen1;
$cif=$result['cif'];
$sal_simpan= mysql_fetch_array(mysql_query("select sum(KREDIT - DEBIT) as saldo from simpanan WHERE cif='$cif' and type not in ('04','05')"));
$saldo =$sal_simpan['saldo'];
$ft=$Pla*(5/100);
$pensiun=number_format(compat_num($ft));
if ($saldo<$ft){
$nyata =$saldo - $ft;	
$nyata=abs(compat_num($nyata));
}elseif($saldo>$ft){
	$nyata=0;
	$nyata=abs(compat_num($nyata));
}else{
	$nyata=$ft;
	$nyata=abs(compat_num($nyata));
}
@mysql_query("insert into total_laris values('$kode_brach','$nyata')");

//query dan arraying
$pdf->SetFont('Arial','',8);
$pdf->Cell(8,8,''.$no,1,'L',1);
$pdf->Cell(25,8,''.$norek,1,'L',1);
$pdf->Cell(47,8,''.$cust_Short_name,1,'L',1);
$pdf->Cell(25,8,''.$Plafond,1,0,'R');
$pdf->Cell(25,8,''.$persen,1,0,'R',1);
$pdf->Cell(25,8,'',1,0,'R',1);
$pdf->Ln();
}
$sql2=mysql_query("select sum(Plafond) as totpla, sum(Plafond *(5/100)) as persen
from calon_loan
where code_kel='$code_kel' and status_app in ('MURAB','MUSYAROKAH') group by code_kel");
while($result1= mysql_fetch_array($sql2)){

$tot_angsur+=$result1['tot_angsur'];
$totpla+=$result1['totpla'];
$laris=5/100 * $totpla; 
$persen=$result1['persen'];
$biaya=$result1['biaya'];
$tot_blt +=$result1['tot_bulat'];

}
$sql3=mysql_query("select sum(KREDIT - DEBIT) as saldo from simpanan
where code_kel='$code_kel' and type not in ('04','05') and cif in (select cif from calon_loan) group by code_kel");
while($result3= mysql_fetch_array($sql3)){

$saldo1+=$result3['saldo'];

}

$sql4=mysql_query("select sum(total) as total_laris from total_laris where unit ='$kode_branch' ");
while($result4= mysql_fetch_array($sql4)){

$laris+=$result4['total_laris'];

}


$pdf->SetFont('ARIAL','B',8);
$pdf->Cell(80,5,'Total',1,0,'C');
$pdf->Cell(25,5,''.number_format (compat_num($totpla)),1,0,'R');
$pdf->Cell(25,5,''.number_format (compat_num($tot_persen)),1,0,'R');

$pdf->Ln(9);
$pdf->SetFont('ARIAL','B',8);
$pdf->Cell(40,5,'Ketua Kelompok',0,0,'L');
$pdf->Cell(75,5,'Marketing Manager ',0,0,'C');
$pdf->Ln(20,1);
$pdf->Cell(60,5,'(_______________)',0,0,'L');
$pdf->Cell(60,5,'(_______________)',0,0,'L');

$pdf->Ln(9);


//$pdf->AutoPrint(true);
 
$pdf->Output();

?>ks
