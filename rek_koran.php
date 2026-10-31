<?php require_once __DIR__ . '/koneksi_db.php';
/**
 * Menu Report > Mutasi (rekening koran anggota) dalam bentuk PDF (FPDF):
 *   01 Simpanan (mutasi simpanan + saldo berjalan), 03 Kartu Angsuran (rek_loan + saldo berjalan),
 *   04 Rencana Angsuran (pinjaman_detail + tunggakan). 02 Pembiayaan tidak punya isi (sama dengan versi lama).
 * Tata letak PDF disalin apa adanya dari versi lama; query diubah ke prepared statement dan saldo berjalan (@bal)
 * dihitung di PHP dengan urutan yang sama.
 * Perbaikan: versi lama mencetak HTML form sebelum PDF (±7 KB sebelum %PDF) dan sesudahnya, sehingga file PDF rusak;
 * kini keluaran dibersihkan sebelum PDF dikirim. Form dikirim ke tab baru agar halaman form tetap terbuka.
 */
require_once __DIR__ . '/inc/tampilan.php';
session_start();
ob_start();
unset($_SESSION['pilih']);
$userid = isset($_SESSION['userid']) ? $_SESSION['userid'] : null;
//cek apakah user sudah login
if (!isset($_SESSION['userid'])) {
    header("location:index.html");
    exit;
}
include "function.php";

/** Baris hasil query dengan nilai teks (seperti mysql_fetch_array versi lama). */
function baris_teks_rk($baris)
{
    if (!$baris) {
        return $baris;
    }
    foreach ($baris as $k => $v) {
        $baris[$k] = $v === null ? null : (string) $v;
    }
    return $baris;
}

$norek = isset($_POST['kode_rekening']) ? trim((string) $_POST['kode_rekening']) : '';
$pilih = isset($_POST['select']) ? (string) $_POST['select'] : '';

$pesan = array();
if (isset($_POST['search'])) {
    if (empty($norek) || empty($pilih)) {
        $pesan[] = "<script>alert('Maaf,.isi Nomor REKENING dan Jenis Transaksi yang benar  !!!');</script>";
        $pesan[] = "<script language='javascript'>document.location.href='rek_koran.php'</script>";
    } elseif (in_array($pilih, array('01', '03', '04'), true)) {
        // data kepala laporan (sama dengan versi lama: bila ada beberapa loan, yang terakhir dipakai)
        $r = baris_teks_rk(db_satu("select kode_branch from user where userid =?", 's', (string) $userid));
        $branch = $r ? $r['kode_branch'] : null;
        $r = baris_teks_rk(db_satu("select unit,alamat from branch where kode_branch =?", 's', (string) $branch));
        $nama_unit = $r ? $r['unit'] : null;
        $alamat = $r ? $r['alamat'] : null;
        $d = baris_teks_rk(db_satu("select cif from anggota where norek =?", 's', $norek));
        $cif = $d ? $d['cif'] : null;
        $os = $plafond = $tgl_akad = $tenor = $CUST_SHORT_NAME = $maturity_date = null;
        foreach (db_semua("select * from loan where cif =?", 's', (string) $cif) as $data_l) {
            $data_l = baris_teks_rk($data_l);
            $os = number_format(compat_num($data_l['os']));
            $plafond = number_format(compat_num($data_l['Plafond']));
            $tgl_akad = tgl_indo((string) $data_l['tgl_akad']);
            $tenor = $data_l['Tenor'];
            $CUST_SHORT_NAME = $data_l['Cust_Short_name'];
            $maturity_date = tgl_indo((string) $data_l['maturity_date']);
        }
        $sp = baris_teks_rk(db_satu("select SUM(KREDIT - DEBIT) as spokok from simpanan_pokok where cif=?", 's', (string) $cif));
        $spokok = number_format(compat_num($sp ? $sp['spokok'] : null));
        $sw = baris_teks_rk(db_satu("select SUM(KREDIT - DEBIT) as swajib from simpanan_wajib where cif=?", 's', (string) $cif));
        $swajib = number_format(compat_num($sw ? $sw['swajib'] : null));

        require('fpdf17/fpdf.php');
        // buang HTML yang mungkin sudah tertampung; yang dikirim hanya PDF
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        switch ($pilih) {
        case "01":
            // saldo berjalan = @bal + KREDIT - DEBIT, urut REFF (sama dengan versi lama)
            $sql = array();
            $bal = 0;
            foreach (db_semua("SELECT simpanan.KET,type,kode_transaksi, simpanan.NOREK,simpanan.BUSS_DATE,anggota.CUST_SHORT_NAME,DEBIT,KREDIT FROM simpanan
                join anggota on anggota.cif = simpanan.cif
                where simpanan.cif=? and TYPE not in ('04','05') order by simpanan.REFF", 's', (string) $cif) as $row) {
                $row = baris_teks_rk($row);
                $bal = $bal + compat_num($row['KREDIT']) - compat_num($row['DEBIT']);
                $row['balance'] = $bal;
                $sql[] = $row;
            }
            $pdf=new FPDF('P','mm', array(216, 275));
            $pdf->AddPage();
            //$pdf=new FPDF('L','mm', array(216, 330));
            $pdf->setX(11);
            $pdf->setFillColor(255,255,255);
            $pdf->cell(74,5,' ',0,'L',1);
            $pdf->Image('Image/nii.png');
            //$pdf-> Write (12,'User ID: ');
            $pdf->Ln(3);
            $pdf->SetFont('Arial','B',9);
            $pdf->SetFont('Arial','B',8);
            $pdf->multicell(0,5,'KOPERASI JASA KEUANGAN SYARIAH NUR INSANI UNIT '.$nama_unit.'',0,'C',1);
            $pdf->multicell(0,5,''.$alamat.'',0,'C',1);
            $pdf->SetFont('Arial','BU',8);
            $pdf->multicell(0,5,'DAFTAR MUTASI SIMPANAN',0,'C',1);
            $pdf->Ln();
            $pdf->SetFont('Arial','',8);
            $pdf->cell(70,5,'Nomor Rekenig',0,'R',1);
            $pdf->cell(1,5,':',0,'L',1);
            $pdf->cell(9,5,'',0,'L',1);
            $pdf->cell(15,5,''.$norek,0,'J',1);
            $pdf->Ln(5);
            $pdf->cell(70,5,'Nama ',0,'L',1);
            $pdf->cell(1,5,':',0,'L',1);
            $pdf->cell(9,5,'',0,'L',1);
            $pdf->cell(15,5,''.$CUST_SHORT_NAME,0,'L',1);
            $pdf->Ln(5);
            $pdf->cell(70,5,'Plafond Pembiayaan',0,'R',1);
            $pdf->cell(1,5,':',0,'R',1);
            $pdf->cell(9,5,'',0,'R',1);
            $pdf->cell(15,5,''.$plafond,0,'R',1);
            $pdf->Ln(5);
            $pdf->cell(70,5,'Tanggal Realisasi ',0,'R',1);
            $pdf->cell(1,5,':',0,'R',1);
            $pdf->cell(9,5,'',0,'R',1);
            $pdf->cell(15,5,''.$tgl_akad,0,'R',1);
            $pdf->Ln(5);
            $pdf->cell(70,5,'Janga Waktu ',0,'R',1);
            $pdf->cell(1,5,':',0,'R',1);
            $pdf->cell(9,5,'',0,'R',1);
            $minggu=' Minggu';
            $pdf->cell(15,5,''.$tenor .$minggu,0,'R',1);
            $pdf->Ln(5);
            $pdf->cell(70,5,'Tanggal Jatuh Tempo ',0,'R',1);
            $pdf->cell(1,5,':',0,'R',1);
            $pdf->cell(9,5,'',0,'R',1);
            $pdf->cell(15,5,''.$maturity_date,0,'R',1);
            $pdf->Ln(5);
            $pdf->cell(70,5,'Sisa Pembiayaan ',0,'R',1);
            $pdf->cell(1,5,':',0,'R',1);
            $pdf->cell(9,5,'',0,'R',1);
            $pdf->cell(15,5,''.$os,0,'R',1);
            $pdf->Ln(5);
            $pdf->cell(70,5,'Simpanan Pokok ',0,'R',1);
            $pdf->cell(1,5,':',0,'R',1);
            $pdf->cell(9,5,'',0,'R',1);
            $pdf->cell(15,5,''.$spokok,0,'R',1);
            $pdf->Ln(5);
            $pdf->cell(70,5,'Simpanan Wajib ',0,'R',1);
            $pdf->cell(1,5,':',0,'R',1);
            $pdf->cell(9,5,'',0,'R',1);
            $pdf->cell(15,5,''.$swajib,0,'R',1);
            $pdf->Ln(5);

            $pdf->Ln(5);
            $pdf->SetFont('ARIAL','B',7);
            $pdf->Cell(8,3,'No',1,0,'C');
            $pdf->Cell(25,3,'Tanggal',1,0,'C');
            $pdf->Cell(25,3,'kode transaksi',1,0,'C');
            $pdf->Cell(60,3,'Keterangan',1,0,'C');
            $pdf->Cell(9,3,'Tipe',1,0,'C');
            $pdf->Cell(15,3,'Debet',1,0,'C');
            $pdf->Cell(15,3,'Kredit',1,0,'C');
            $pdf->Cell(15,3,'Saldo',1,0,'C');

            $pdf->Ln();
            $no =0;
            foreach ($sql as $result) {
            $no ++;
            $norek = $result['NOREK'];
            $CUST_SHORT_NAME = $result['CUST_SHORT_NAME'];
            $BUSS_DATE = $result['BUSS_DATE'];
            $KET = $result['KET'];
            $tipe = $result['type'];
            $DEBIT = $result['DEBIT'];
            $KREDIT = $result['KREDIT'];
            $Balance = $result['balance'];
            $kode_transaksi = $result['kode_transaksi'];

            //query dan arraying
            $pdf->SetFont('Arial','',7);
            $pdf->Cell(8,3,''.$no,1,'L',1);
            $pdf->Cell(25,3,''.tgl_indo ($BUSS_DATE),1,'L',1);
            $pdf->Cell(25,3,''.$kode_transaksi,1,'L',1);
            $pdf->Cell(60,3,''.$KET,1,'L',1);
            $pdf->Cell(9,3,''.$tipe,1,'L',1);
            $pdf->Cell(15,3,''.number_format (compat_num($DEBIT)),1,'LR',0,'R');
            $pdf->Cell(15,3,''.number_format (compat_num($KREDIT)),1,'LR',0,'R');
            $pdf->Cell(15,3,''.number_format (compat_num($Balance)),1,'LR',0,'R');

            $pdf->Ln();
            }
            $pdf->Ln(9);
            $pdf->SetFont('ARIAL','B',8);
            $pdf->Cell(40,5,'Bisnis Manager',0,0,'L');

            $pdf->Ln(28,1);
            $pdf->Cell(60,5,'(_______________)',0,0,'L');


            $pdf->Output();
            exit;
        case "03":
            // saldo berjalan = @bal + saldo_kredit - debet, urut ref (sama dengan versi lama)
            $sql5 = array();
            $bal = 0;
            foreach (db_semua("SELECT rek_loan.KET,rek_loan.STATUS, rek_loan.no_anggota,rek_loan.tgl_realisasi,anggota.CUST_SHORT_NAME,debet,saldo_kredit FROM rek_loan
                join anggota on anggota.cif = rek_loan.cif
                where rek_loan.cif=? order by rek_loan.ref", 's', (string) $cif) as $row) {
                $row = baris_teks_rk($row);
                $bal = $bal + compat_num($row['saldo_kredit']) - compat_num($row['debet']);
                $row['balance'] = $bal;
                $sql5[] = $row;
            }
            $pdf=new FPDF('P','mm', array(216, 275));
            $pdf->AddPage();
            //$pdf=new FPDF('L','mm', array(216, 330));
            $pdf->setX(11);
            $pdf->setFillColor(255,255,255);
            $pdf->SetFont('Arial','B',9);
            $pdf->SetFont('Arial','B',8);
            $pdf->multicell(0,5,'KOPERASI JASA KEUANGAN SYARIAH NUR INSANI UNIT '.$nama_unit.'',0,'J',1);
            $pdf->multicell(0,5,'ALAMAT '.$alamat.'',0,'J',1);
            $pdf->SetFont('Arial','BU',8);
            $pdf->multicell(0,5,'DAFTAR MUTASI KARTU ANGSURAN',0,'J',1);
            $pdf->Ln();

            $pdf->SetFont('Arial','',8);
            $pdf->cell(70,5,'Nomor Rekenig',0,'R',1);
            $pdf->cell(1,5,':',0,'L',1);
            $pdf->cell(9,5,'',0,'L',1);
            $pdf->cell(15,5,''.$norek,0,'J',1);
            $pdf->Ln(5);
            $pdf->cell(70,5,'Nama ',0,'L',1);
            $pdf->cell(1,5,':',0,'L',1);
            $pdf->cell(9,5,'',0,'L',1);
            $pdf->cell(15,5,''.$CUST_SHORT_NAME,0,'L',1);
            $pdf->Ln(5);
            $pdf->cell(70,5,'Plafond Pembiayaan',0,'R',1);
            $pdf->cell(1,5,':',0,'R',1);
            $pdf->cell(9,5,'',0,'R',1);
            $pdf->cell(15,5,''.$plafond,0,'R',1);
            $pdf->Ln(5);
            $pdf->cell(70,5,'Tanggal Realisasi ',0,'R',1);
            $pdf->cell(1,5,':',0,'R',1);
            $pdf->cell(9,5,'',0,'R',1);
            $pdf->cell(15,5,''.$tgl_akad,0,'R',1);
            $pdf->Ln(5);
            $pdf->cell(70,5,'Janga Waktu ',0,'R',1);
            $pdf->cell(1,5,':',0,'R',1);
            $pdf->cell(9,5,'',0,'R',1);
            $minggu=' Minggu';
            $pdf->cell(15,5,''.$tenor .$minggu,0,'R',1);
            $pdf->Ln(5);
            $pdf->cell(70,5,'Tanggal Jatuh Tempo ',0,'R',1);
            $pdf->cell(1,5,':',0,'R',1);
            $pdf->cell(9,5,'',0,'R',1);
            $pdf->cell(15,5,''.$maturity_date,0,'R',1);
            $pdf->Ln(5);
            $pdf->cell(70,5,'Sisa Pembiayaan ',0,'R',1);
            $pdf->cell(1,5,':',0,'R',1);
            $pdf->cell(9,5,'',0,'R',1);
            $pdf->cell(15,5,''.$os,0,'R',1);
            $pdf->Ln(5);
            $pdf->Ln(5);
            $pdf->SetFont('ARIAL','B',7);
            $pdf->Cell(8,3,'No',1,0,'C');
            $pdf->Cell(25,3,'Tanggal',1,0,'C');
            $pdf->Cell(60,3,'Keterangan',1,0,'C');
            $pdf->Cell(40,3,'Jenis Transaksi',1,0,'C');
            $pdf->Cell(15,3,'Debet',1,0,'C');
            $pdf->Cell(15,3,'Kredit',1,0,'C');
            $pdf->Cell(15,3,'Saldo',1,0,'C');
            $pdf->Ln();
            $no =0;
            foreach ($sql5 as $result5) {
            $no ++;
            $norek = $result5['no_anggota'];
            $CUST_SHORT_NAME = $result5['CUST_SHORT_NAME'];
            $BUSS_DATE = $result5['tgl_realisasi'];
            $KET = $result5['KET'];
            $tipe = $result5['STATUS'];
            $DEBIT = $result5['debet'];
            $KREDIT = $result5['saldo_kredit'];
            $Balance = $result5['balance'];
            //query dan arraying
            $pdf->SetFont('Arial','',7);
            $pdf->Cell(8,3,''.$no,1,'L',1);
            $pdf->Cell(25,3,''.tgl_indo ($BUSS_DATE),1,'L',1);
            $pdf->Cell(60,3,''.$KET,1,'L',1);
            $pdf->Cell(40,3,''.$tipe,1,'L',1);
            $pdf->Cell(15,3,''.number_format (compat_num($DEBIT)),1,'LR',0,'R');
            $pdf->Cell(15,3,''.number_format (compat_num($KREDIT)),1,'LR',0,'R');
            $pdf->Cell(15,3,''.number_format (compat_num($Balance)),1,'LR',0,'R');
            $pdf->Ln();
            }	
            $pdf->Output();
            exit;
        case "04":
            // (versi lama: "tenor <= tenor" selalu benar, tanpa ORDER BY)
            $sql3 = array_map('baris_teks_rk', db_semua("select CUST_SHORT_NAME,id_pinjam,angsuran_pokok,bunga,tgl_jatuh_tempo,tgl_bayar,jumlah_bayar from pinjaman_detail
                inner join loan on loan.cif=pinjaman_detail.cif
                where loan.cif=? and tenor <=tenor", 's', (string) $cif));
            $pdf=new FPDF('P','mm', array(216, 275));
            $pdf->AddPage();
            //$pdf=new FPDF('L','mm', array(216, 330));
            $pdf->setX(11);
            $pdf->setFillColor(255,255,255);
            $pdf->SetFont('Arial','B',9);
            $pdf->SetFont('Arial','B',8);
            $pdf->multicell(0,5,'KOPERASI JASA KEUANGAN SYARIAH NUR INSANI UNIT '.$nama_unit.'',0,'J',1);
            $pdf->multicell(0,5,'ALAMAT '.$alamat.'',0,'J',1);
            $pdf->SetFont('Arial','BU',8);
            $pdf->multicell(0,5,'DAFTAR KARTU ANGSURAN PEMBIAYAAN',0,'J',1);
            $pdf->Ln();
            $pdf->SetFont('Arial','',8);
            $pdf->cell(70,5,'Nomor Rekenig',0,'R',1);
            $pdf->cell(1,5,':',0,'L',1);
            $pdf->cell(9,5,'',0,'L',1);
            $pdf->cell(15,5,''.$norek,0,'J',1);
            $pdf->Ln(5);
            $pdf->cell(70,5,'Nama ',0,'L',1);
            $pdf->cell(1,5,':',0,'L',1);
            $pdf->cell(9,5,'',0,'L',1);
            $pdf->cell(15,5,''.$CUST_SHORT_NAME,0,'L',1);
            $pdf->Ln(5);
            $pdf->cell(70,5,'Plafond Pembiayaan',0,'R',1);
            $pdf->cell(1,5,':',0,'R',1);
            $pdf->cell(9,5,'',0,'R',1);
            $pdf->cell(15,5,''.$plafond,0,'R',1);
            $pdf->Ln(5);
            $pdf->cell(70,5,'Tanggal Realisasi ',0,'R',1);
            $pdf->cell(1,5,':',0,'R',1);
            $pdf->cell(9,5,'',0,'R',1);
            $pdf->cell(15,5,''.$tgl_akad,0,'R',1);
            $pdf->Ln(5);
            $pdf->cell(70,5,'Janga Waktu ',0,'R',1);
            $pdf->cell(1,5,':',0,'R',1);
            $pdf->cell(9,5,'',0,'R',1);
            $minggu=' Minggu';
            $pdf->cell(15,5,''.$tenor .$minggu,0,'R',1);
            $pdf->Ln(5);
            $pdf->cell(70,5,'Tanggal Jatuh Tempo ',0,'R',1);
            $pdf->cell(1,5,':',0,'R',1);
            $pdf->cell(9,5,'',0,'R',1);
            $pdf->cell(15,5,''.$maturity_date,0,'R',1);
            $pdf->Ln(5);
            $pdf->cell(70,5,'Sisa Pembiayaan ',0,'R',1);
            $pdf->cell(1,5,':',0,'R',1);
            $pdf->cell(9,5,'',0,'R',1);
            $pdf->cell(15,5,''.$os,0,'R',1);
            $pdf->Ln(5);
            $pdf->Ln(5);
            $pdf->SetFont('ARIAL','B',7);


            $pdf->Cell(8,3,'No',1,0,'C');
            $pdf->Cell(25,3,'Tgl Jatuh Tempo',1,0,'C');
            $pdf->Cell(18,3,'Pokok',1,0,'C');
            $pdf->Cell(18,3,'Margin',1,0,'C');
            $pdf->Cell(15,3,'Jmlh Bayar',1,0,'C');
            $pdf->Cell(25,3,'Tgl Bayar',1,0,'C');
            $pdf->Cell(25,3,'Tunggakan Pokok',1,0,'C');
            $pdf->Cell(25,3,'Tunggakan Margin',1,0,'C');

            $pdf->Ln();
            $no3 =0;
            foreach ($sql3 as $result3) {
            $no3 ++;
            $id_pinjam = $result3['id_pinjam'];
            $CUST_SHORT_NAME = $result3['CUST_SHORT_NAME'];
            $tgl_jatuh_tempo = $result3['tgl_jatuh_tempo'];
            $angsuran_pokok = $result3['angsuran_pokok'];
            $bunga = $result3['bunga'];
            $jumlah_bayar=$result3['jumlah_bayar'];
            $tgl_bayar = $result3['tgl_bayar'];
            $ket = null; // versi lama: $result['jumlah_bayar'] (variabel tidak ada, tidak dipakai)
            $tg = baris_teks_rk(db_satu("select kredit as pokok from tunggakan where norek =? and TYPE='01' and tgl_tunggak=?", 'ss', (string) $norek, (string) $tgl_jatuh_tempo));
            $pokok=$tg['pokok'];
            $tg = baris_teks_rk(db_satu("select kredit as margin from tunggakan where norek =? and TYPE='02' and tgl_tunggak=?", 'ss', (string) $norek, (string) $tgl_jatuh_tempo));
            $margin=$tg['margin'];
            //query dan arraying
            $pdf->SetFont('Arial','',7);
            $pdf->Cell(8,3,''.$no3,1,'L',1);
            $pdf->Cell(25,3,''.tgl_indo ($tgl_jatuh_tempo),1,'L',1);
            $pdf->Cell(18,3,''.number_format (compat_num($angsuran_pokok)),1,'LR',0,'R');
            $pdf->Cell(18,3,''.number_format (compat_num($bunga)),1,'LR',0,'R');
            $pdf->Cell(15,3,''.number_format (compat_num($jumlah_bayar)),1,'R',0,'R');
            $pdf->Cell(25,3,''.tgl_indo ($tgl_bayar),1,'LR',0,'R');
            $pdf->Cell(25,3,''.number_format (compat_num($pokok)),1,'R',0,'R');
            $pdf->Cell(25,3,''.number_format (compat_num($margin)),1,'R',0,'R');
            $pdf->Ln();
            }   
            $pdf->Output();
            exit;
        }
    }
    // versi lama: jenis 02 (Pembiayaan) dan nilai lain tidak menghasilkan apa-apa selain halaman form
}

halaman_awal('Rekap Mutasi');
?>
<div class="card card-ni">
  <div class="card-header">Input Data</div>
  <div class="card-body">
    <form method="post" action="rek_koran.php" name="postform" class="form-ni" target="_blank" autocomplete="off">
      <div class="row g-2 mb-2">
        <label for="kode" class="col-sm-4 col-form-label">NOREK</label>
        <div class="col-sm-3 autocomplete">
          <input type="text" name="kode_rekening" id="kode" class="form-control form-control-sm" placeholder="CIF / nama" required
                 data-autocomplete="ajax/cari_rekening.php" data-isi-nilai="norek" data-isi-label="norek,nama,nama_kel"
                 data-isi-target='{"#country":"nama"}'>
        </div>
        <div class="col-sm-5"><input type="text" id="country" class="form-control form-control-sm" disabled></div>
      </div>
      <div class="row g-2 mb-3">
        <label for="select" class="col-sm-4 col-form-label">Jenis Transaksi</label>
        <div class="col-sm-8">
          <select name="select" id="select" class="form-select form-select-sm" required>
            <option value="">Pilih Salah satu</option>
            <option value="01">Simpanan</option>
            <option value="02">Pembiayaan</option>
            <option value="03">Kartu Angsuran</option>
            <option value="04">Rencana Angsuran</option>
          </select>
        </div>
      </div>
      <div class="d-flex justify-content-between align-items-center">
        <span class="small text-muted">PDF dibuka di tab baru.</span>
        <button name="search" type="submit" id="submit2" value="Cari" class="btn btn-ni btn-sm px-4">Cari</button>
      </div>
    </form>
  </div>
</div>
<?php echo implode("\n", $pesan); ?>
<?php
halaman_akhir();
