<?php require_once __DIR__ . '/../../koneksi_db.php';
/**
 * Export Excel (.xls BIFF, fungsi.php) NOMINATIVE DEPOSITO untuk menu fund lama: POST laporan = nomi | reprint,
 * keyword = kata kunci pencarian halaman itu. Query sama dengan halaman pemanggilnya (view_nomi.php, view_reprint.php).
 * Perbaikan keamanan 2026-11-04 (perintah user): dulu halaman mengirim teks SQL di field tersembunyi "sql" dan file ini
 * menjalankannya apa adanya tanpa cek login, sehingga siapa pun bisa menjalankan SQL apa pun (membaca/mengubah/menghapus
 * data). Kini wajib login level fund dan query disusun di sini dengan prepared statement. Data Jatuh Tempo memakai
 * tempo_export.php, Data Break / Cetak Bilyet / Cetak Advis tempo_export.php?laporan=break|bilyet|advis.
 */
session_start();
require_once __DIR__ . '/../../inc/cek_login.php';
wajib_login();
if ((isset($_SESSION['level']) ? $_SESSION['level'] : '') != "fund") {
    header("location:../../index.html");
    exit;
}
include 'fungsi.php';

$laporan = isset($_POST['laporan']) ? (string) $_POST['laporan'] : '';
$keyword = isset($_POST['keyword']) ? trim((string) $_POST['keyword']) : '';
$cari = '%' . $keyword . '%';
$ada = $keyword !== '';
$kolom = "SELECT norek,CUST_SHORT_NAME,nominal,tenor,maturity_date,deposito.cif,aro FROM deposito join deposan on deposan.cif = deposito.cif";
switch ($laporan) {
    case 'nomi':
        $k = "SELECT deposito.norek,bilyet,CUST_SHORT_NAME,deposito.cif,nominal,tenor,maturity_date,rasio,star_date, sum(KREDIT)-sum(DEBIT) as saldo
            FROM deposito join deposan on deposan.CIF = deposito.cif left join escrow_deposito on escrow_deposito.cif = deposito.cif";
        $result = $ada ? db_semua("$k where norek = ? group by norek", 's', $keyword) : db_semua("$k group by norek");
        break;
    case 'reprint':
        $result = $ada ? db_semua("$kolom where deposito.cif LIKE ?", 's', $cari) : db_semua($kolom);
        break;
    default:
        http_response_code(400);
        exit('Jenis laporan tidak dikenal.');
}
//pengaturan nama file
$namaFile = "fund.xls";
//pengaturan judul data
$judul = "NOMINATIVE DEPOSITO KSPPS NUR INSANI";
//baris berapa header tabel di tulis
$tablehead = 2;
//baris berapa data mulai di tulis
$tablebody = 3;
//no urut data
$nourut = 1;
//penulisan header
header("Pragma: public");
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0,pre-check=0");
header("Content-Type: application/force-download");
header("Content-Type: application/octet-stream");
header("Content-Type: application/download");
header("Content-Disposition: attachment;filename=".$namaFile."");
header("Content-Transfer-Encoding: binary ");
xlsBOF();
xlsWriteLabel(0,0,$judul);
$kolomhead = 0;
xlsWriteLabel($tablehead,$kolomhead++,"NOMOR");
xlsWriteLabel($tablehead,$kolomhead++,"NOREK");
xlsWriteLabel($tablehead,$kolomhead++,"BILYET");
xlsWriteLabel($tablehead,$kolomhead++,"CIF");
xlsWriteLabel($tablehead,$kolomhead++,"NAMA");
xlsWriteLabel($tablehead,$kolomhead++,"ALAMAT");
xlsWriteLabel($tablehead,$kolomhead++,"NOMINAL");
xlsWriteLabel($tablehead,$kolomhead++,"START DATE");
xlsWriteLabel($tablehead,$kolomhead++,"JATUH TEMPO");
xlsWriteLabel($tablehead,$kolomhead++,"JANGKA WAKTU");
xlsWriteLabel($tablehead,$kolomhead++,"RASIO");
xlsWriteLabel($tablehead,$kolomhead++,"TABUNGAN");
foreach ($result as $data)
{
$v = function ($k) use ($data) {
    return isset($data[$k]) ? (string) $data[$k] : '';
};
$kolombody = 0;
//gunakan xlsWriteNumber untuk penulisan nomor dan xlsWriteLabel untuk penulisan string
xlsWriteNumber($tablebody,$kolombody++,$nourut);
xlsWriteLabel($tablebody,$kolombody++,$v('norek'));
xlsWriteLabel($tablebody,$kolombody++,$v('bilyet'));
xlsWriteLabel($tablebody,$kolombody++,$v('cif'));
xlsWriteLabel($tablebody,$kolombody++,$v('CUST_SHORT_NAME'));
xlsWriteLabel($tablebody,$kolombody++,$v('HADDRESS1'));
xlsWriteNumber($tablebody,$kolombody++,compat_num($v('nominal')));
xlsWriteLabel($tablebody,$kolombody++,$v('star_date'));
xlsWriteLabel($tablebody,$kolombody++,$v('maturity_date'));
xlsWriteLabel($tablebody,$kolombody++,$v('tenor'));
xlsWriteLabel($tablebody,$kolombody++,$v('rasio'));
xlsWriteNumber($tablebody,$kolombody++,compat_num($v('saldo')));
$tablebody++;
$nourut++;
}
xlsEOF();
exit();
