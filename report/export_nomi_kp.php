<?php require_once __DIR__ . '/../koneksi_db.php';
/**
 * Rekap Nominative Acrual (Excel), dibuka dari kp > Report > Nominative Pembiayaan (Kr_kp.php).
 * Isian dari sesi Kr_kp.php (status, buss_dates). Mengosongkan accrual dan loan_accrual, mengisi loan_accrual dari
 * loan (CURRENT) atau data_loan (EOM), menambah data musawamah dari API 192.168.88.44, menghitung day/accrual,
 * lalu menampilkan seluruh loan_accrual. loan_accrual dipakai accrual.php untuk posting jurnal.
 * Dimigrasi 2026-11-03 (prepared statement, pengaman login); perhitungan sama dengan versi lama, termasuk:
 *   - query select $query versi lama tidak pernah dijalankan, jadi dibuang
 *   - update day untuk baris API ("where acrual ='1'", nama kolom salah ketik) selalu gagal tanpa pesan di versi
 *     lama, jadi tidak dijalankan; akibatnya baris API tetap day=0 sehingga accrual-nya 0 (sama dengan versi lama)
 *   - kolom buss_date di tabel menampilkan buss_date baris API terakhir (bila ada), selain itu buss_dates sesi
 *   - API tidak terjangkau / bukan JSON: dianggap tanpa data (versi lama: warning foreach), batas koneksi 10 detik
 */
require_once __DIR__ . '/../inc/cek_login.php';
wajib_login();
$userid = $_SESSION['userid'];
$status = isset($_SESSION['status']) ? $_SESSION['status'] : '';
$buss_date = isset($_SESSION['buss_dates']) ? (string) $_SESSION['buss_dates'] : '';
$day = date('D', strtotime($buss_date));
$dayList = array(
    'Sun' => 'Minggu',
    'Mon' => 'Senin',
    'Tue' => 'Selasa',
    'Wed' => 'Rabu',
    'Thu' => 'Kamis',
    'Fri' => 'Jumat',
    'Sat' => 'Sabtu'
);
$kolom = 'buss_date,code_kel,no_anggota,cif,Cust_Short_name,Deal_type,suffix,bagi_hasil,Tenor,Plafond,os,saldo_margin,
angsuran,pokok,ijaroh,bulat,run_tenor,ke,usaha,nama_usaha,unit,tgl_wakalah,tgl_akad,tgl_murab,next_schedule,
maturity_date,last_payment,hari,cao,USERID,status,status_usia,status_app,gol';

db_jalankan("TRUNCATE TABLE accrual");
db_jalankan("TRUNCATE TABLE loan_accrual");

switch ($status) {
    case "CURRENT":
        db_jalankan("insert into loan_accrual select $kolom, '' as day, '' as accrual from loan where os > 0 group by loan.cif");
        break;
    case "EOM":
        db_jalankan("insert into loan_accrual select $kolom, '' as day, '' as accrual from data_loan where os > 0 and buss_date = ?", 's', $buss_date);
        break;
    default:
}

db_jalankan("update loan_accrual set day = (select total_hari from param_hari where hari_eom = ? and hari_tagih = hari)", 's',
    isset($dayList[$day]) ? $dayList[$day] : '');
db_jalankan("update loan_accrual set accrual = Plafond * 18/100/tenor/7*day");

function curl($url)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    $output = curl_exec($ch);
    curl_close($ch);
    return $output;
}

$curl = curl("http://192.168.88.44/ni/api/categories/musawamah.php");
// mengubah JSON menjadi array
$data = is_string($curl) ? json_decode($curl, true) : null;
if (!is_array($data)) {
    $data = array();
}

$kolom_api = array(
    'buss_date', 'code_kel', 'no_anggota', 'id_member', 'Cust_Short_name', 'Deal_type', 'suffix', 'bagi_hasil', 'Tenor',
    'Plafond', 'os', 'saldo_margin', 'angsuran', 'pokok', 'ijaroh', 'bulat', 'run_tenor', 'ke', 'usaha', 'nama_usaha',
    'unit', 'tgl_wakalah', 'tgl_akad', 'tgl_murab', 'next_schedule', 'maturity_date', 'last_payment', 'hari', 'cao',
    'USERID', 'status', 'status_usia', 'status_app', 'gol',
);
$tanda = implode(',', array_fill(0, count($kolom_api) + 2, '?'));
foreach ($data as $row) {
    $nilai = array();
    foreach ($kolom_api as $k) {
        $nilai[] = isset($row[$k]) ? (string) $row[$k] : '';
    }
    $buss_date = $nilai[0];
    $nilai[] = '0'; // day
    $nilai[] = '1'; // accrual
    db_jalankan("insert into loan_accrual($kolom,day,accrual) values($tanda)", str_repeat('s', count($nilai)), ...$nilai);
}

db_jalankan("update loan_accrual set accrual = day/7*ijaroh where accrual='1'");

$baris = db_semua("Select * from loan_accrual");

header("Content-type: application/vnd-ms-excel");
header("Content-Disposition: attachment; filename=accrual.xls");

$e = function ($s) {
    return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
};
?>
<html>
<head>
<title>NeracaPercobaan</title>
<style>
  body {font-family:tahoma, arial}
  table {border-collapse: collapse}
  th, td {font-size: 17px; border: 1px solid #DEDEDE; padding: 5px 7px; color: #303030}
  th {background: #EAEAEA; font-size: 17px; border-color:#B0B0B0}
</style>
</head>
<body>
<b>KOPERASI SIMPAN PINJAM PEMBIAYAAN SYARIAH</b><br/>
<b>LAPORAN ACRUAL</b><br/>
<b>UNIT KANTOR PUSAT</b><br/>
<b>Periode - <?php echo $e(isset($_SESSION['buss_dates']) ? $_SESSION['buss_dates'] : ''); ?></b><br/>
<br/>
<table width="100%" border="1">
<tr>
<?php foreach (array('buss_date', 'code_kel', 'no_anggota', 'cif', 'Cust_Short_name', 'Deal_type', 'suffix', 'bagi_hasil',
    'Tenor', 'Plafond', 'Pembiayaan', 'os', 'saldo_margin', 'angsuran', 'pokok', 'Margin_minggon', 'bulat', 'run_tenor', 'ke',
    'usaha', 'nama_usaha', 'unit', 'tgl_wakalah', 'tgl_akad', 'tgl_murab', 'next_schedule', 'maturity_date', 'last_payment',
    'hari', 'cao', 'USERID', 'status', 'status_usia', 'status_app', 'gol', 'Day', 'Acrual') as $judul): ?>
<th><?php echo $judul; ?></th>
<?php endforeach; ?>
</tr>
<?php foreach ($baris as $r): ?>
<tr>
<td align="center"><?php echo $e($buss_date); ?></td>
<?php foreach (array('code_kel', 'no_anggota', 'cif', 'Cust_Short_name', 'Deal_type', 'suffix', 'bagi_hasil', 'Tenor', 'Plafond', 'os') as $k): ?>
<td align="center"><?php echo $e($r[$k]); ?></td>
<?php endforeach; ?>
<td align="center"><?php echo $e(compat_num($r['os']) - compat_num($r['saldo_margin'])); ?></td>
<?php foreach (array('saldo_margin', 'angsuran', 'pokok', 'ijaroh', 'bulat', 'run_tenor', 'ke', 'usaha', 'nama_usaha', 'unit',
    'tgl_wakalah', 'tgl_akad', 'tgl_murab', 'next_schedule', 'maturity_date', 'last_payment', 'hari', 'cao', 'USERID', 'status',
    'status_usia', 'status_app', 'gol', 'day', 'accrual') as $k): ?>
<td align="center"><?php echo $e($r[$k]); ?></td>
<?php endforeach; ?>
</tr>
<?php endforeach; ?>
</table>
</body>
</html>
