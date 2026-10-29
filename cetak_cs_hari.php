<?php require_once __DIR__ . '/koneksi_db.php';
session_start();
ob_start();
$userid=$_SESSION['userid'];
$tgl_jatuh_tempo=$_SESSION['jatuh'];
$cao=$_SESSION['code_cao'];
//cek apakah user sudah login
if(!isset($_SESSION['userid'])){
   header("location:index.html");
 exit; }

//cek level user
if($_SESSION['level']!="unit"){
   header("location:index.html");
 exit; }
 include "conn2.php";
 include "indo_tgl.php";
 $koneksi=open_connection2();	
$code_kel=$_GET['kode_kel'];

$ca = db_satu("Select nama_ao FROM ao WHERE cao=?", 's', (string) $cao);
$nama_sales=$ca['nama_ao'];


$dsmo = db_satu("Select nama,kode_branch FROM user WHERE userid=?", 's', (string) $userid);
$kode_branch=$dsmo['kode_branch'];

$br = db_satu("Select * FROM branch WHERE kode_branch=?", 's', (string) $kode_branch);
$nama_unit=$br['unit'];
$alamat=$br['alamat'];

$cd = db_satu("Select nama_kel,loan.code_kel,next_schedule  FROM loan 
inner join kelompok on kelompok.code_kel = loan.code_kel WHERE loan.code_kel=?", 's', (string) $code_kel);
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
if(!isset($_SESSION['userid'])){
   header("location:../index.html");//jika belum login jangan lanjut..
 exit; }

//cek level user
if($_SESSION['level']!="unit"){
     header("location:../index.html");//jika bukan user jangan lanjut
 exit; }

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
$pdf=new FPDF ('L','mm','A4');
$pdf->AddPage();
//$pdf=new FPDF('L','mm', array(216, 330));
$pdf->setX(11);
$pdf->setFillColor(255,255,255);


$pdf->SetFont('Arial','B',9);

//membuat kop tabel
$pdf->SetFont('Arial','B',10);
$pdf->multicell(0,5,'KOPERASI SIMPAN PINJAM DAN PEMBIAYAAN SYARIAH NUR INSANI AREA PEMASARAN '.$nama_unit.'',0,'J',1);
$pdf->multicell(0,5,'ALAMAT '.$alamat.'',0,'J',1);
$pdf->SetFont('Arial','BU',9);
$pdf->multicell(0,5,'DAFTAR TAGIHAN PEMBIAYAAN',0,'J',1);
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
$pdf->SetFont('ARIAL','',9);
$pdf->Cell(8,5,'No',1,0,'C');
$pdf->Cell(25,5,'No.Rek',1,0,'C');
$pdf->Cell(47,5,'Nama Anggota',1,0,'C');
$pdf->Cell(16,5,'Plafond',1,0,'C');
$pdf->Cell(15,5,'Margin',1,0,'C');
$pdf->Cell(9,5,'KE',1,0,'C');
 $pdf->Cell(7,5,'PB',1,0,'C');
$pdf->Cell(20,5,'Pembiayaan',1,0,'C');
$pdf->Cell(20,5,'T W M',1,0,'C');
$pdf->Cell(15,5,'P & W',1,0,'C');
$pdf->Cell(9,5,'FT',1,0,'C');
$pdf->Cell(15,5,'Tunggakan',1,0,'C');
$pdf->Cell(15,5,'Angsuran',1,0,'C');
$pdf->Cell(18,5,'Setoran',1,0,'C');

$pdf->Cell(20,5,'Nyata',1,0,'C');
$pdf->Cell(13,5,'TandaTerima',1,0,'C');
$pdf->Cell(7,5,'Prf',1,0,'C');
$pdf->Ln();
$no=0;
$tot_blt=0;
$sql = db_semua("select loan.Plafond as tot_pla,sum(bulat)as tot_bulat,loan.unit,ao.nama_ao, loan.cif,ke,run_tenor,nama_kel,loan.cao, loan.cust_Short_name,loan.Plafond,loan.bagi_hasil,sum(loan.bagi_hasil) as baghas,bulat,os,loan.angsuran,sum(loan.angsuran)tot_angsur,loan.bulat,deal_produk
 from loan
LEFT join anggota on anggota.CIF = loan.cif
LEFT join kelompok on kelompok.code_kel=loan.code_kel

LEFT join ao on ao.cao=loan.cao where loan.code_kel=? AND loan.hari=? and loan.cao=? and os > 0 group by loan.cif", 'sss', (string) $code_kel, (string) $dayList[$day], (string) $cao);

if(count($sql)==0){
$sql = db_semua("select loan.Plafond as tot_pla,sum(bulat)as tot_bulat,loan.unit,ao.nama_ao, loan.cif,ke,run_tenor,sum(KREDIT)-sum(DEBIT) as saldo,nama_kel,loan.cao, loan.cust_Short_name,loan.Plafond,loan.bagi_hasil,sum(loan.bagi_hasil) as baghas,bulat,os,loan.angsuran,sum(loan.angsuran)tot_angsur,loan.bulat
 from loan
LEFT join anggota on anggota.CIF = loan.cif
left join kelompok on kelompok.code_kel=loan.code_kel
left join simpanan on simpanan.cif=loan.cif
LEFT join pinjaman_detail_jatpo on loan.no_anggota=pinjaman_detail_jatpo.id_pinjam
LEFT join ao on ao.cao=loan.cao where loan.code_kel=? AND pinjaman_detail_jatpo.tgl_jatuh_tempo=? and loan.cao=? and os > 0 group by loan.cif", 'sss', (string) $code_kel, (string) $tgl_jatuh_tempo, (string) $cao);
		
		 }



foreach ($sql as $result){
	$no ++;
 $TGL_KUNJUNGAN = $result['nama_ao'];
$cif = $result['cif'];
$cao = $result['cao'];
$del_produk= $result['deal_produk'];
if($del_produk==2){
$angsur = $result['angsuran'] + 2500;
}else{
$angsur=$result['angsuran'];
}
$t=$result['angsuran'] + 2500;
$cust_Short_name= $result['cust_Short_name'];
$Plafond= number_format (compat_num($result['Plafond']));  
$angsuran = number_format (compat_num($angsur));
$bulat = number_format (compat_num($result['bulat']));
$unit = $result['unit'];
$ke = $result['ke'];
$bulat = number_format (compat_num($result['bulat']));
//$saldo = number_format ($result['saldo']);
$run_tenor = $result['run_tenor'];
$os =number_format (compat_num($result['os']));
$norek="$cao-$cif";
$bagi_hasil = number_format(compat_num($result['bagi_hasil']));
$cek_pokok= db_satu("select sum(KREDIT)- sum(DEBIT) as pokok from tunggakan WHERE cif=? and type='01'", 's', (string) $cif);
$tunggak_pokok =$cek_pokok['pokok'];
$cek_margin= db_satu("select sum(KREDIT)- sum(DEBIT) as margin from tunggakan WHERE cif=? and type='02'", 's', (string) $cif);
$tunggak_margin =$cek_margin['margin'];
$cek_saldo= db_satu("select sum(KREDIT - DEBIT) as saldo from simpanan WHERE cif=? and type not in ('04','05')", 's', (string) $cif);
$saldo =number_format(compat_num($cek_saldo['saldo']));

$cek_saldo_p= db_satu("select sum(KREDIT - DEBIT) as saldo from simpanan_pokok WHERE cif=?", 's', (string) $cif);
$saldo_p =$cek_saldo_p['saldo'];

$cek_saldo_w= db_satu("select sum(KREDIT - DEBIT) as saldo from simpanan_wajib WHERE cif=?", 's', (string) $cif);
$saldo_w =$cek_saldo_w['saldo'];

$pw=number_format(compat_num($saldo_p + $saldo_w));

$tunggak=$tunggak_pokok + $tunggak_margin;
$tg=number_format(compat_num($tunggak));
$ftt=$tunggak/$t;
$ft=number_format(compat_num($ftt));
$p=number_format(compat_num($ftt + $run_tenor + 1));

//query dan arraying
$pdf->SetFont('Arial','',9);
$pdf->Cell(8,5,''.$no,1,'L',1);
$pdf->Cell(25,5,''.$norek,1,'L',1);
$pdf->Cell(47,5,''.$cust_Short_name,1,'L',1);
$pdf->Cell(16,5,''.$Plafond,1,0,'R');
$pdf->Cell(15,5,''.$bagi_hasil,1,'R',1);
$pdf->Cell(9,5,''.$run_tenor,1,'L',1);
$pdf->Cell(7,5,''.$p,1,'L',1);
$pdf->Cell(20,5,''.$os,1,0,'R');
$pdf->Cell(20,5,''.$saldo,1,0,'R');
$pdf->Cell(15,5,''.$pw,1,0,'R');
$pdf->Cell(9,5,''.$ft,1,0,'R');
$pdf->Cell(15,5,''.$tg,1,'L',1);
$pdf->Cell(15,5,''.$angsuran,1,0,'R');
$pdf->Cell(18 ,5,''.$bulat,1,0,'R');

$pdf->Cell(20,5,'',1,'L',1);
$pdf->Cell(13,5,'',1,'L',1);
$pdf->Cell(7,5,'',1,'L',1);

$pdf->Ln();
}
$sql2 = db_semua("select sum(os) as biaya,sum(Plafond) as totpla,sum(loan.bagi_hasil) as baghas,sum(loan.angsuran)tot_angsur,sum(bulat)as tot_bulat, count(cif) as noa
from loan
where code_kel=? and cao=? and os > 1 AND hari=? group by code_kel", 'sss', (string) $code_kel, (string) $cao, (string) $dayList[$day]);
foreach ($sql2 as $result1){
$noa = $result1['noa']*2500;
$tot_angsur+=$result1['tot_angsur'];
$tot_angsur=ceil(compat_num($tot_angsur+($noa)));
$totpla+=$result1['totpla'];
$baghas=$result1['baghas'];
$biaya=$result1['biaya'];
$tot_blt +=$result1['tot_bulat'];

}
$sql3 = db_semua("select sum(KREDIT - DEBIT) as saldo from simpanan
inner join loan on loan.cif = simpanan.cif 
where  loan.code_kel =? and os > 1 and type not in ('04','05') AND hari=? ", 'ss', (string) $code_kel, (string) $dayList[$day]);
foreach ($sql3 as $result3){

$saldo1+=$result3['saldo'];

}


$pdf->SetFont('ARIAL','',9);
$pdf->Cell(80,5,'Jumlah',1,0,'C');
$pdf->Cell(16,5,''.number_format (compat_num($totpla)),1,0,'R');
$pdf->Cell(15,5,''.number_format (compat_num($baghas)),1,0,'R');
$pdf->Cell(16,5,'',1,0,'R');
$pdf->Cell(20,5,''.number_format (compat_num($biaya)),1,0,'R');
$pdf->Cell(20,5,''.number_format (compat_num($saldo1)),1,0,'R');
$pdf->Cell(15,5,'',1,0,'R');
$pdf->Cell(9,5,'',1,0,'R');
$pdf->Cell(15,5,'',1,0,'R');
$pdf->Cell(15,5,''.number_format (compat_num($tot_angsur)),1,0,'R');
$pdf->Cell(18,5,''.number_format (compat_num($tot_blt)),1,0,'R');

$pdf->Cell(20,5,'',1,0,'R');
$pdf->Ln(9);
$pdf->SetFont('ARIAL','',10);
$pdf->Cell(160,5,'DISERAHKAN OLEH',0,0,'L');
$pdf->Cell(40,5,'DIPERIKSA OLEH ',0,0,'C');
$pdf->Cell(40,5,'DIKETAHUI OLEH ',0,0,'C');
$pdf->Ln(20,1);
$pdf->Cell(40,5,'Ketua Kelompok',0,0,'L');
$pdf->Cell(120,5,'SAO/AO',0,0,'L');
$pdf->Cell(40,5,'Marketing Manager',0,0,'C');
$pdf->Cell(40,5,'BM',0,0,'C');
$pdf->Ln(9);


//$pdf->AutoPrint(true);
 
$pdf->Output();

?>
