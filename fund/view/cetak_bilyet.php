<?php require_once __DIR__ . '/../../koneksi_db.php';
/**
 * Cetak Bilyet deposito (?kd=norek, dari view_cetak_bilyet.php): PDF FPDF 330x330 mm; tata letak dan isi sama dengan
 * versi lama. Sesudah data diambil, status_print diubah ke 'Print' (deposito hilang dari daftar Cetak Bilyet).
 * Perbaikan atas perintah user 2026-11-04: prepared statement; norek tidak ada atau status_print bukan 'OK' -> pesan
 * (dulu PDF kosong dan status tetap di-update); query ao/branch yang hasilnya tidak dipakai dibuang.
 * Sebelumnya (2026-11-04): nama_bank dari deposan (dulu query 'ambiguous' -> bilyet kosong), alamat didekripsi.
 */
require_once __DIR__ . '/../../inc/tampilan.php';
session_start();
ob_start();
require_once __DIR__ . '/../../inc/cek_login.php';
wajib_login();
//cek level user
if ((isset($_SESSION['level']) ? $_SESSION['level'] : '') != "fund") {
    header("location:../../index.html");
    exit;
}
require_once __DIR__ . '/../../inc/enkripsi.php';
include "../../indo_tgl.php";
$kode = isset($_GET['kd']) ? trim((string) $_GET['kd']) : '';

$cd = db_satu("Select contract_date,maturity_date,tenor,rasio,nominal,CUST_SHORT_NAME,HADDRESS1,norek_tujuan,deposan.nama_bank,aro,status_print
    from deposito inner join deposan on deposito.cif = deposan.cif where deposito.norek=?", 's', $kode);
if (!$cd || (string) $cd['status_print'] !== 'OK') {
    halaman_awal('Cetak Bilyet');
    echo '<div class="alert alert-warning">' . ($cd
        ? 'Bilyet deposito ' . h($kode) . ' tidak bisa dicetak: status ' . h($cd['status_print']) . ' (hanya status OK). Untuk cetak ulang gunakan menu Reprint.'
        : 'Deposito ' . h($kode) . ' tidak ditemukan.') . '</div>';
    echo '<a href="view_cetak_bilyet.php" class="btn btn-sm btn-secondary">Kembali</a>';
    halaman_akhir();
    exit;
}
$contract_date=tgl_indo ($cd['contract_date']);
$maturity_date=tgl_indo ($cd['maturity_date']);
$tenor=$cd['tenor'];
$rasio=$cd['rasio'];
$aro=$cd['aro'];
$angka=$cd['nominal'];
$norek_tujuan=$cd['norek_tujuan'];
$nama_bank=$cd['nama_bank'];
$nominal=number_format (compat_num($cd['nominal']));
$CUST_SHORT_NAME=$cd['CUST_SHORT_NAME'];
// alamat deposan tersimpan terenkripsi (inc/enkripsi.php, PEKERJAAN_TERTUNDA.md bagian 87); data lama terbuka tetap terbaca
$HADDRESS1=dekripsi_data($cd['HADDRESS1']);
$rasio_cor=100-$rasio;
$Terbilang=ucwords (Terbilang($angka));

db_jalankan("update deposito set status_print ='Print' where norek =?", 's', $kode);
ob_end_clean();

require ('../../fpdf17/fpdf.php');
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
$pdf=new PDF_AutoPrint('L','mm', array(330, 330));
$pdf->AddPage();
//$pdf=new FPDF('L','mm', array(216, 330));
$pdf->setX(11);
$pdf->setFillColor(255,255,255);


$pdf->SetFont('Arial','B',12);
$bulan =" Bulan";
$per=" %";
$rp=" Rupiah";
//membuat kop tabel
$pdf->SetFont('Arial','B',20);
$pdf->Ln(60);
$pdf->Cell(20,5,'',0,0,'L');
$pdf->Cell(70,5,''.$contract_date,0,0,'L');
$pdf->Cell(70,5,''.$tenor .$bulan,0,0,'L');
$pdf->Cell(70,5,''.$maturity_date,0,0,'L');
$pdf->Cell(55,5,''.$rasio .$per,0,0,'L');
$pdf->Cell(55,5,''.$rasio_cor .$per,0,0,'L');
$pdf->Ln(58);
$pdf->Cell(20,5,'',0,0,'L');
$pdf->Cell(200,5,''.$CUST_SHORT_NAME,0,0,'L');
$pdf->Cell(70,5,''.$aro,0,0,'L');
$pdf->Ln(10);
$pdf->Cell(20,5,'',0,0,'L');
$pdf->SetFont('Arial','B',13);
$pdf->Cell(130,5,''.$HADDRESS1,0,0,'L');
$pdf->Ln(30);
$pdf->Cell(40,5,'',0,0,'L');
$pdf->SetFont('Arial','B',20);
$pdf->Cell(120,5,''.$nominal,0,0,'L');
$pdf->SetFont('Arial','B',13);
$pdf->Cell(70,5,'Norek : '.$norek_tujuan,0,0,'L');
$pdf->Ln(5);
$pdf->Cell(160,5,'',0,0,'L');
$pdf->Cell(70,5,'Nama Bank : '.$nama_bank,0,0,'L');
$pdf->Ln(5);
$pdf->Cell(160,5,'',0,0,'L');
$pdf->Cell(70,5,'Atas Nama : '.$CUST_SHORT_NAME,0,0,'L');
$pdf->Ln(35);
$pdf->SetFont('Arial','B',20);
$pdf->Cell(20,5,'',0,0,'L');
$pdf->Cell(70,5,''.$Terbilang .$rp,0,0,'L');



//$pdf->AutoPrint(true);
 
$pdf->Output();

?>
<?php

function terbilang($angka) {
    // pastikan kita hanya berususan dengan tipe data numeric
    $angka = (float)$angka;
     
    // array bilangan 
    // sepuluh dan sebelas merupakan special karena awalan 'se'
    $bilangan = array(
            '',
            'satu',
            'dua',
            'tiga',
            'empat',
            'lima',
            'enam',
            'tujuh',
            'delapan',
            'sembilan',
            'sepuluh',
            'sebelas'
    );
     
    // pencocokan dimulai dari satuan angka terkecil
    if ($angka < 12) {
        // mapping angka ke index array $bilangan
        return $bilangan[$angka];
    } else if ($angka < 20) {
        // bilangan 'belasan'
        // misal 18 maka 18 - 10 = 8
        return $bilangan[$angka - 10] . ' belas';
    } else if ($angka < 100) {
        // bilangan 'puluhan'
        // misal 27 maka 27 / 10 = 2.7 (integer => 2) 'dua'
        // untuk mendapatkan sisa bagi gunakan modulus
        // 27 mod 10 = 7 'tujuh'
        $hasil_bagi = (int)($angka / 10);
        $hasil_mod = $angka % 10;
        return trim(sprintf('%s puluh %s', $bilangan[$hasil_bagi], $bilangan[$hasil_mod]));
    } else if ($angka < 200) {
        // bilangan 'seratusan' (itulah indonesia knp tidak satu ratus saja? :))
        // misal 151 maka 151 = 100 = 51 (hasil berupa 'puluhan')
        // daripada menulis ulang rutin kode puluhan maka gunakan
        // saja fungsi rekursif dengan memanggil fungsi terbilang(51)
        return sprintf('seratus %s', terbilang($angka - 100));
    } else if ($angka < 1000) {
        // bilangan 'ratusan'
        // misal 467 maka 467 / 100 = 4,67 (integer => 4) 'empat'
        // sisanya 467 mod 100 = 67 (berupa puluhan jadi gunakan rekursif terbilang(67))
        $hasil_bagi = (int)($angka / 100);
        $hasil_mod = $angka % 100;
        return trim(sprintf('%s ratus %s', $bilangan[$hasil_bagi], terbilang($hasil_mod)));
    } else if ($angka < 2000) {
        // bilangan 'seribuan'
        // misal 1250 maka 1250 - 1000 = 250 (ratusan)
        // gunakan rekursif terbilang(250)
        return trim(sprintf('seribu %s', terbilang($angka - 1000)));
    } else if ($angka < 1000000) {
        // bilangan 'ribuan' (sampai ratusan ribu
        $hasil_bagi = (int)($angka / 1000); // karena hasilnya bisa ratusan jadi langsung digunakan rekursif
        $hasil_mod = $angka % 1000;
        return sprintf('%s ribu %s', terbilang($hasil_bagi), terbilang($hasil_mod));
    } else if ($angka < 1000000000) {
        // bilangan 'jutaan' (sampai ratusan juta)
        // 'satu puluh' => SALAH
        // 'satu ratus' => SALAH
        // 'satu juta' => BENAR 
        // @#$%^ WT*
         
        // hasil bagi bisa satuan, belasan, ratusan jadi langsung kita gunakan rekursif
        $hasil_bagi = (int)($angka / 1000000);
        $hasil_mod = $angka % 1000000;
        return trim(sprintf('%s juta %s', terbilang($hasil_bagi), terbilang($hasil_mod)));
    } else if ($angka < 1000000000000) {
        // bilangan 'milyaran'
        $hasil_bagi = (int)($angka / 1000000000);
        // karena batas maksimum integer untuk 32bit sistem adalah 2147483647
        // maka kita gunakan fmod agar dapat menghandle angka yang lebih besar
        $hasil_mod = fmod($angka, 1000000000);
        return trim(sprintf('%s milyar %s', terbilang($hasil_bagi), terbilang($hasil_mod)));
    } else if ($angka < 1000000000000000) {
        // bilangan 'triliun'
        $hasil_bagi = $angka / 1000000000000;
        $hasil_mod = fmod($angka, 1000000000000);
        return trim(sprintf('%s triliun %s', terbilang($hasil_bagi), terbilang($hasil_mod)));
    } else {
        return 'Wow...';
    }
}
?>