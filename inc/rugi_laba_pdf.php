<?php
/**
 * PDF Rugi Laba (2026-11-21, perintah user, PEKERJAAN_TERTUNDA §32 A2/D1), dipakai:
 *   laporan/laba_pdf.php     (level unit;  sama dengan layar laporan/new_laba.php)
 *   kp/laporan/laba_pdf.php  (level kp;    sama dengan layar kp/laporan/new_laba.php: daftar rekening ber-gr_neraca
 *                             'rugi-laba')
 * Isi: rekening rugi-laba tabel_rugi_laba unit (kecuali 59999), SHU operasional, SHU non operasional, SHU sebelum / setelah
 * pajak; angka bertanda (rugi = minus). Hanya membaca. Mengirim PDF lalu berhenti.
 * Estimasi pajak: unit 0 (seperti lama); kp = estimasi_pajak_rl() (2026-11-22, §32, perintah user).
 */

/**
 * Estimasi taksiran pajak untuk laporan Rugi Laba kp (2026-11-22, §32, perintah user): saldo_akhir rekening 59999
 * (diisi menu kp > EOM, kp/laporan/eom.php) unit itu. Di kp (1010) 59999 ber-gr_head 59999, jadi tidak termasuk beban
 * 58000 dan dulu hilang dari laporan (estimasi selalu 0). Bila 59999 ber-gr_head 58000 (struktur unit biasa) pajak sudah
 * termasuk beban non operasional, jadi 0 supaya tidak terhitung dua kali.
 * Dibaca dari tabel_master, bukan tabel_rugi_laba: urutan kp = Hitung SHU (mengisi tabel_rugi_laba) lalu EOM (menulis
 * taksiran baru ke tabel_master saja), sehingga 59999 di tabel_rugi_laba masih taksiran sebelum EOM.
 */
function estimasi_pajak_rl($unit)
{
    $r = db_satu("select saldo_akhir, gr_head from tabel_master where unit =? and kode_rekening='59999'", 's', (string) $unit);
    if (!$r || (string) $r['gr_head'] === '58000') {
        return 0;
    }
    return compat_num($r['saldo_akhir']);
}

function rugi_laba_pdf($unit, $saring_gr_neraca)
{
    $unit = (string) $unit;
    $cabang = db_satu("select unit, alamat from branch where kode_branch=?", 's', $unit);
    $rekening = db_semua("SELECT kode_rekening,nama_rekening,saldo_awal,saldo_akhir,saldo_jalan,LINE_BALANCE
        FROM `tabel_rugi_laba`
        where unit =? and posisi='rugi-laba'" . ($saring_gr_neraca ? " and gr_neraca='rugi-laba'" : '') . " and kode_rekening not in ('59999')
        GROUP BY gr_neraca,kode_rekening ORDER BY gr_neraca,kode_rekening ", 's', $unit);
    $saldo_rl = function ($kode) use ($unit) {
        $r = db_satu("select saldo_akhir from tabel_rugi_laba where unit =? and kode_rekening=?", 'ss', $unit, $kode);
        return compat_num($r ? $r['saldo_akhir'] : null);
    };
    $shu_ops_akhir = $saldo_rl('40000') - $saldo_rl('50000');
    $shu_ops_non_akhir = $saldo_rl('57000') - $saldo_rl('58000');
    $shu_sb_pajak_akhir = $shu_ops_akhir + $shu_ops_non_akhir;
    $estimasi_pajak = $saring_gr_neraca ? estimasi_pajak_rl($unit) : 0; // kp (saring gr_neraca) saja
    $shu_after_pajak_akhir = $shu_sb_pajak_akhir - $estimasi_pajak;

    // font inti FPDF memakai Latin-1
    $teks = function ($s) {
        $t = @iconv('UTF-8', 'ISO-8859-1//TRANSLIT', (string) $s);
        return $t === false ? (string) $s : $t;
    };
    $angka = function ($v) {
        return number_format(compat_num($v), 0, ',', '.');
    };

    require_once __DIR__ . '/../fpdf17/fpdf.php';
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    $pdf = new FPDF('P', 'mm', 'A4');
    $pdf->SetMargins(12, 12, 12);
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->Cell(0, 5, $teks('KOPERASI JASA KEUANGAN SYARIAH NUR INSANI UNIT ' . ($cabang ? $cabang['unit'] : $unit)), 0, 1);
    $pdf->SetFont('Arial', '', 8);
    $pdf->Cell(0, 5, $teks('ALAMAT ' . ($cabang ? $cabang['alamat'] : '')), 0, 1);
    $pdf->SetFont('Arial', 'BU', 10);
    $pdf->Cell(0, 8, 'LAPORAN RUGI LABA', 0, 1, 'C');
    $pdf->SetFont('Arial', '', 7);
    $pdf->Cell(0, 4, $teks('Dicetak ' . date('d-m-Y H:i')), 0, 1, 'R');

    $lebar = array(22, 22, 102, 40);
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->SetFillColor(230, 230, 230);
    foreach (array('TYPE COA', 'COA', 'Deskripsi', 'Saldo Akhir') as $i => $judul) {
        $pdf->Cell($lebar[$i], 6, $judul, 1, 0, $i === 3 ? 'R' : 'C', true);
    }
    $pdf->Ln();
    foreach ($rekening as $row) {
        $pdf->SetFont('Arial', $row['LINE_BALANCE'] == 'HEADING' ? 'B' : '', 8);
        $pdf->Cell($lebar[0], 5, $teks($row['LINE_BALANCE']), 1);
        $pdf->Cell($lebar[1], 5, $teks($row['kode_rekening']), 1);
        $pdf->Cell($lebar[2], 5, $teks(substr((string) $row['nama_rekening'], 0, 70)), 1);
        $pdf->Cell($lebar[3], 5, $angka($row['saldo_akhir']), 1, 1, 'R');
    }
    $pdf->SetFont('Arial', 'B', 8);
    foreach (array(
        array('SHU OPERASIONAL', $shu_ops_akhir),
        array('SHU NON OPERASIONAL', $shu_ops_non_akhir),
        array('SHU TAHUN BERJALAN SEBELUM PAJAK', $shu_sb_pajak_akhir),
        array('ESTIMASI TAKSIRAN PAJAK PENGHASILAN', $estimasi_pajak),
        array('SHU TAHUN BERJALAN SETELAH PAJAK', $shu_after_pajak_akhir),
    ) as $shu) {
        $pdf->Cell($lebar[0] + $lebar[1] + $lebar[2], 6, $shu[0], 1);
        $pdf->Cell($lebar[3], 6, $angka($shu[1]), 1, 1, 'R');
    }
    // FPDF 1.7: Output(nama, tujuan)
    $pdf->Output('rugi_laba_' . preg_replace('/[^A-Za-z0-9_-]/', '', $unit) . '.pdf', 'I');
    exit;
}
