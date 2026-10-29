<?php require_once __DIR__ . '/koneksi_db.php';
session_start();
ob_start();
$userid=$_SESSION['userid'];
$tgl_jatuh_tempo=$_SESSION['jatuh'];
$cao=$_SESSION['code_cao'];
 date_default_timezone_set('Asia/Jakarta');
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
$code_kel = $_GET['code_kel']; 



$dsmo = db_satu("Select nama,kode_branch FROM user WHERE userid=?", 's', (string) $userid);
$kode_branch=$dsmo['kode_branch'];

$dsmo = db_satu("Select nama_akad FROM jenis_akad WHERE unit=?", 's', (string) $kode_branch);
$jenis_akad=$dsmo['nama_akad'];


db_jalankan("delete from total_laris where unit=?", 's', (string) $kode_branch);

$br = db_satu("Select * FROM branch WHERE kode_branch=?", 's', (string) $kode_branch);
$nama_unit=$br['unit'];
$alamat=$br['alamat'];


$cd = db_satu("Select nama_kel,calon_loan.code_kel,next_schedule,calon_loan.cao, tgl_akad  FROM calon_loan 
left join kelompok on kelompok.code_kel = calon_loan.code_kel WHERE calon_loan.code_kel=?", 's', (string) $code_kel);
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
$ca = db_satu("Select nama_ao FROM ao WHERE cao=?", 's', (string) $cao);
$nama_sales=$ca['nama_ao'];

$um = db_satu(" Select Nama as um,jabatan from person where unit=?", 's', (string) $kode_branch);
$nama_um=$um['um'];
$jabatan=$um['jabatan'];

$nomor=$kode_branch."-".$tgl_akad."-".$code_kel;

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
$pdf=new FPDF ('P','mm','A4');
$pdf->AddPage();
//$pdf=new FPDF('L','mm', array(216, 330));
$pdf->SetFont ('Arial','',10);
$pdf->setTopMargin(15);
$pdf->setLeftMargin(12);
$pdf->setFillColor(255,255,255);

$pdf->SetFont('Arial','B',12);
$pdf->Cell(0,5,'PERJANJIAN PEMBERIAN JAMINAN (KAFALAH)',0,1,'C');
$pdf->SetFont('Arial','',10);
$pdf->Cell(0,5,'Nomor : '.$nomor,0,1,'C');
$pdf->Ln(5);

$pdf->MultiCell(0,5,'Perjanjian Pemberian Jaminan (Kafalah) (selanjutnya disebut Perjanjian) dibuat dan ditandatangani pada tanggal '.$hari2.' oleh dan antara:',0,'J');
$pdf->Ln(2);

$pdf->Cell(5,5,'I.',0,0,'L');
$pdf->MultiCell(0,5,'Nama '.$nama_um.' jabatan '.$jabatan.' dalam hal ini bertindak untuk dan atas nama Koperasi Simpan Pinjam dan Pembiayaan Syariah (KSPPS) Nur Insani, yang berkedudukan di Kota Tangerang Selatan, berdasarkan Akta Pendirian No. 02 Tanggal 7 Bulan Mei Tahun 2012 melalui Notaris Neilly Iralita Iswari, S.H., M.Si., M.Kn. dan telah memperoleh pengesahan Kementerian Koperasi dan Usaha Kecil dan Menengah no. 1024/BH/M.KUM.2/XI/2012, serta dengan perubahan-perubahannya, selanjutnya disebut sebagai PIHAK PERTAMA.',0,'J');
$pdf->Ln(2);

$pdf->Cell(5,5,'II.',0,1,'L');

$sql = db_semua("select cif,no_anggota,Plafond as tot_pla,cust_Short_name,tgl_wakalah from calon_loan
where code_kel=? and status_app in ('MURAB','MUSYAROKAH','PENDING') order by cust_Short_name", 's', (string) $code_kel);
$members = array();
foreach ($sql as $result){
    $members[] = $result['cust_Short_name'];
}

$total_members = count($members);
$max_rows = 10;
$cols = max(1, ceil(compat_num($total_members / $max_rows)));

$pdf->SetFont('Arial','B',9);
for ($c = 0; $c < $cols; $c++) {
    $pdf->Cell(8,5,'No',1,0,'C');
    $pdf->Cell(50,5,'Nama',1,0,'C');
}
$pdf->Ln();

$pdf->SetFont('Arial','',9);
for ($i = 0; $i < $max_rows; $i++) {
    for ($c = 0; $c < $cols; $c++) {
        $idx = $i + ($c * $max_rows);
        if ($idx < $total_members) {
            $pdf->Cell(8, 5, $idx + 1, 1, 0, 'C');
            $pdf->Cell(50, 5, isset($members[$idx]) ? substr($members[$idx], 0, 25) : '', 1, 0, 'L');
        } else {
            $pdf->Cell(8, 5, '', 1, 0, 'C');
            $pdf->Cell(50, 5, '', 1, 0, 'L');
        }
    }
    $pdf->Ln();
}

$pdf->Ln(2);
$pdf->SetFont('Arial','',10);
$pdf->Cell(5,5,'',0,0,'L');
$pdf->MultiCell(0,5,'Selanjutnya disebut sebagai PIHAK KEDUA',0,'L');
$pdf->Ln(2);

$pdf->MultiCell(0,5,'Selanjutnya PIHAK PERTAMA dan PIHAK KEDUA secara bersama-sama disebut sebagai PARA PIHAK.',0,'J');
$pdf->Ln(2);

$pdf->MultiCell(0,5,'PARA PIHAK dengan ini terlebih dahulu menerangkan sebagai berikut:',0,'J');
$pdf->Cell(5,5,'-',0,0,'L');
$pdf->MultiCell(0,5,'Bahwa PIHAK KEDUA dengan ini menyatakan dan mengakui bahwa masing-masing anggotanya telah mengajukan permohonan pembiayaan kepada PIHAK PERTAMA dalam skema Pembiayaan '.$jenis_akad.', yang telah dituangkan dalam Akad '.$jenis_akad.' yang ditandatangani secara terpisah oleh masing-masing anggota. Seluruh Akad '.$jenis_akad.' tersebut dinyatakan sah, berlaku, dan mengikat PARA PIHAK sesuai dengan ketentuan yang diperjanjikan.',0,'J');
$pdf->Cell(5,5,'-',0,0,'L');
$pdf->MultiCell(0,5,'Bahwa Perjanjian ini berlaku sebagai jaminan atas pemenuhan kewajiban berdasarkan Akad '.$jenis_akad.', dan diberlakukan dalam hal terjadi wanprestasi.',0,'J');
$pdf->Ln(2);

$pdf->MultiCell(0,5,'Berdasarkan hal-hal tersebut di atas, PARA PIHAK dengan ini sepakat mengadakan akad ini dengan ketentuan-ketentuan sebagai berikut :',0,'J');

$pdf->Cell(5,5,'1.',0,0,'L');
$pdf->MultiCell(0,5,'PIHAK KEDUA dengan ini sepakat dan mengikatkan diri secara tanggung renteng untuk saling memberikan penjaminan (kafalah) atas seluruh kewajiban masing-masing anggota.',0,'J');

$pdf->Cell(5,5,'2.',0,0,'L');
$pdf->MultiCell(0,5,'Penjamin sebagaimana dimaksud pada ayat (1) tidak mengakibatkan pengalihan kewajiban (utang), sehingga kewajiban tersebut tetap melekat pada anggota yang menerima pembiayaan.',0,'J');

$pdf->Cell(5,5,'3.',0,0,'L');
$pdf->MultiCell(0,5,'Dalam hal salah satu atau lebih anggota kelompok tidak mampu memenuhi kewajibannya karena sebab apapun, maka anggota lainnya wajib secara bersama-sama tanpa syarat mengambil alih dan melunasi kewajiban tersebut.',0,'J');

$pdf->Cell(5,5,'4.',0,0,'L');
$pdf->MultiCell(0,5,'PIHAK PERTAMA selaku penerima jaminan terlebih dahulu melakukan penagihan kepada anggota yang wanprestasi. Dalam hal penagihan tersebut tidak berhasil atau anggota yang bersangkutan tetap tidak memenuhi kewajibannya, maka PIHAK PERTAMA berhak untuk menagih dan mamperoleh pelunasan dari anggota lainnya dalam PIHAK KEDUA, baik secara sebagian maupun seluruhnya.',0,'J');

$pdf->Cell(5,5,'5.',0,0,'L');
$pdf->MultiCell(0,5,'PIHAK PERTAMA berhak menerima pembayaran atas kewajiban sebagaimana dimaksud pada ayat (1) dari anggota yang terjamin maupun dari PIHAK KEDUA sebagai penjamin, dan pembayaran tersebut sah secara mengikat.',0,'J');

$pdf->Cell(5,5,'6.',0,0,'L');
$pdf->MultiCell(0,5,'Dalam hal PIHAK KEDUA sebagai penjamin melakukan pembayaran atas kewajiban anggota kepada PIHAK PERTAMA, maka kewajiban anggota tersebut kepada PIHAK PERTAMA dinyatakan lunas, sehingga PIHAK PERTAMA tidak lagi berhak melakukan penagihan maupun menerima pembayaran atas kewajiban tersebut dari anggota yang bersangkutan.',0,'J');

$pdf->Cell(5,5,'7.',0,0,'L');
$pdf->MultiCell(0,5,'Dalam hal pembayaran oleh PIHAK KEDUA sebagaimana dimaksud pada ayat (6) dilakukan atas dasar permintaan PIHAK KEDUA atau anggota yang bersangkutan, maka PIHAK KEDUA memperoleh hak subrogasi yaitu hak untuk menagih kembali kepada anggota yang dijamin sebesar jumlah yang telah dibayarkan kepada PIHAK PERTAMA.',0,'J');
$pdf->Ln(2);

$pdf->MultiCell(0,5,'Demikian Perjanjian ini dibuat dan ditandatangani oleh PIHAK PERTAMA dan PIHAK KEDUA dalam keadaan sadar, tanpa adanya paksaan dari pihak manapun, serta memiliki kekuatan hukum yang mengikat bagi PARA PIHAK sejak tanggal ditandatangani.',0,'J');
$pdf->Ln(5);

$pdf->SetFont('Arial','',8);
$pdf->Cell(50,5,'PIHAK PERTAMA',0,0,'C');
$pdf->Cell(135,5,'PIHAK KEDUA',0,1,'C');
$pdf->Ln(8);

$pihak_kedua_cols = 3;
$sig_rows = max(1, ceil(compat_num($total_members / $pihak_kedua_cols)));

for ($i = 0; $i < $sig_rows; $i++) {
    if ($i == 0) {
        $pdf->Cell(50,5,'(                                   )',0,0,'C');
    } else {
        $pdf->Cell(50,5,'',0,0,'C');
    }

    for ($c = 0; $c < $pihak_kedua_cols; $c++) {
        $idx = ($i * $pihak_kedua_cols) + $c;
        if ($idx < $total_members) {
            $nama = $members[$idx];
            $name_w = $pdf->GetStringWidth($nama);
            $w = max(35, $name_w + 12); // Lebar sel menyesuaikan teks, minimal 35mm
            
            $char_w = $pdf->GetStringWidth('.');
            $num_chars = max(15, ceil(compat_num($name_w / $char_w))); // Hitung jumlah titik (...) berdasarkan panjang nama
            $pdf->Cell($w, 5, ($idx + 1) . '. (' . str_repeat('.', $num_chars) . ')', 0, 0, 'L');
        }
    }
    $pdf->Ln();
    
    if ($i == 0) {
        $pdf->Cell(50,5,$nama_um,0,0,'C');
    } else {
        $pdf->Cell(50,5,'',0,0,'C');
    }

    for ($c = 0; $c < $pihak_kedua_cols; $c++) {
        $idx = ($i * $pihak_kedua_cols) + $c;
        if ($idx < $total_members) {
            $nama = $members[$idx];
            $name_w = $pdf->GetStringWidth($nama);
            $w = max(35, $name_w + 12);
            $pdf->Cell($w, 5, '    ' . $nama, 0, 0, 'L');
        }
    }
    $pdf->Ln();

    if ($i < $sig_rows - 1) {
        $pdf->Ln(10);
    }
}
$pdf->Ln(18);
$pdf->Cell(50,5,'',0,0,'C');
$pdf->Cell(50,5,'(........................)',0,0,'C');
$pdf->Ln(4);
$pdf->Cell(50,5,'',0,0,'C');
$pdf->Cell(50,5,'KETUA KELOMPOK',0,0,'C');


//$pdf->AutoPrint(true);
 
$pdf->Output();

?>
