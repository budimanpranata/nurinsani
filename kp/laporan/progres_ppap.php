<?php require_once __DIR__ . '/../../koneksi_db.php';
/**
 * Hitung PPAP kp (dipanggil tombol "Proses PPAP" di proses_ppap_konsol.php lewat fetch, POST), untuk tanggal sistem.
 * Per unit yang punya pembiayaan aktif:
 *   - hitung ulang loan.gol dengan rumus menu Report > PPAP unit (inc/kolektabilitas.php: per deal_produk, aturan jatuh
 *     tempo terhadap tanggal sistem, satu transaksi DB per unit)
 *   - report_ppap: sudah dibentuk = saldo_awal 1512000 unit (tersimpan negatif), akan dibentuk = PPAP seperti rekap PPAP
 *     unit (report/rekap_ppap.php): 0,5% gol 1 + 10% gol 2 + 50% gol 3 + 100% gol 4 dari os - saldo_margin, tanpa
 *     MUSYAROKAH; selisih = akan + sudah dibentuk; status 1 bila selisih > 0, selain itu 2
 * Jawaban JSON {success, message, progress} sama dengan versi lama.
 * Dimigrasi 2026-11-03; perbaikan atas perintah user 2026-11-03 (versi lama):
 *   - report_ppap tidak pernah dikosongkan ("truncate from report_ppap" salah sintaks) -> kini dikosongkan dulu
 *   - tanggal acuan $tgl kosong sehingga aturan jatuh tempo tidak pernah berlaku -> kini tanggal sistem
 *   - PPAP golongan 2 tidak ikut ($tot_ppap12 salah ketik) dan MUSYAROKAH ikut dihitung -> kini sama dengan rekap unit
 *   - rumus golongan berbeda dengan menu unit -> kini fungsi yang sama
 *   - tanpa cek login/level -> kini wajib login level kp
 */
session_start();
require_once __DIR__ . '/../../inc/cek_login.php';
require_once __DIR__ . '/../../inc/kolektabilitas.php';
wajib_login('json');
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['progress'])) {
    $_SESSION['progress'] = 0;
}
if ($_SESSION['level'] != 'kp') {
    echo json_encode(array('success' => false, 'message' => 'Anda bukan user'));
    exit;
}
$tgl = isset($_SESSION['stgl_system']) ? (string) $_SESSION['stgl_system'] : '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $tgl !== '') {
    db_jalankan("TRUNCATE TABLE report_ppap");
    $gagal = array();
    foreach (db_semua("select unit from loan where os > 0 group by unit order by unit") as $row_hit_mut) {
        $unit = (string) $row_hit_mut['unit'];
        if (!hitung_kolektabilitas($unit, $tgl)) {
            $gagal[] = $unit;
            continue;
        }
        $coa = db_satu("select saldo_awal from tabel_master WHERE unit =? and kode_rekening ='1512000'", 's', $unit);
        $sdh_dibentuk = compat_num($coa ? $coa['saldo_awal'] : null);
        $saldo = array();
        foreach (db_semua("SELECT gol, sum(os-saldo_margin) as saldo from loan where loan.unit =? and os>0 and status_app not in ('MUSYAROKAH')
            and gol in ('1','2','3','4') group by gol order by gol", 's', $unit) as $g) {
            $saldo[(string) $g['gol']] = compat_num($g['saldo']);
        }
        $akan_dibentuk = 0.5 / 100 * (isset($saldo['1']) ? $saldo['1'] : 0)
            + 10 / 100 * (isset($saldo['2']) ? $saldo['2'] : 0)
            + 50 / 100 * (isset($saldo['3']) ? $saldo['3'] : 0)
            + 100 / 100 * (isset($saldo['4']) ? $saldo['4'] : 0);
        $akan_dibentuk = (int) round($akan_dibentuk);
        $selisih = $akan_dibentuk + $sdh_dibentuk;
        db_jalankan("insert into report_ppap(sdh_dibentuk,akan_dibentuk,jumlah_selisih,unit,status) values(?,?,?,?,?)", 'sssss',
            (string) $sdh_dibentuk, (string) $akan_dibentuk, (string) $selisih, $unit, $selisih > 0 ? '1' : '2');
    }
    if ($_SESSION['progress'] > 100) {
        $_SESSION['progress'] = 100;
    }
    echo json_encode(array(
        'success' => !$gagal,
        'message' => $gagal ? 'Perhitungan kolektabilitas gagal untuk unit ' . implode(', ', $gagal) . '; silakan ulangi.' : 'Data berhasil disimpan',
        'progress' => $_SESSION['progress'],
    ));
} else {
    echo json_encode(array(
        'success' => false,
        'message' => $tgl === '' ? 'Tanggal sistem belum di setup' : 'Gagal menyimpan data',
    ));
}
