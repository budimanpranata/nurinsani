<?php require_once __DIR__ . '/../koneksi_db.php';
/**
 * Data menu Omzet dan Nisbah (view_omzet.php). Juga dipakai lewat ajax_omzet.php di root.
 *   type=omzet     : daftar omzet anggota unit user (urut tanggal terbaru); bila ada parameter draw,
 *                    dijawab per halaman (DataTables server-side) seperti yang dipakai view_omzet.php
 *   type=calonloan : pengajuan MUSYAROKAH unit user beserta nisbah & omzet
 * Query dan format angka sama dengan versi lama (unit dibandingkan dengan userid, seperti versi lama).
 */
session_start();
ob_start();
error_reporting(0);
require_once __DIR__ . '/../inc/cek_login.php';
require_once __DIR__ . '/../inc/datatables_server.php';
wajib_login('json');
$userid=$_SESSION['userid'];
header('Content-Type: application/json; charset=utf-8');

$type =isset($_GET['type'])? $_GET['type'] : 'omzet';
$data = array();

if ($type == 'omzet' && isset($_GET['draw'])) {
    // Perbaikan 2026-10-30 (perintah user): mode server-side DataTables, hanya satu halaman yang dikirim.
    // Kolom, filter unit, dan format angka sama dengan mode lengkap di bawah.
    $hasil = dt_halaman(array(
        'pilih' => 'omzet.id, omzet.tanggal, kelompok.nama_kel, ao.nama_ao, omzet.cif, anggota.CUST_SHORT_NAME AS nama, omzet.nominal AS omzet',
        'dari' => "FROM omzet
            INNER JOIN anggota ON anggota.cif = omzet.cif
            LEFT JOIN kelompok ON kelompok.code_kel = anggota.CODE_KEL
            LEFT JOIN ao ON ao.cao = anggota.CAO
            WHERE anggota.UNIT = ?",
        'tipe' => 's',
        'nilai' => array((string) $userid),
        // omzet dicari dalam bentuk tampilannya (1.500.000), sama seperti pencarian di browser dulu
        'cari' => array('omzet.id', 'omzet.tanggal', 'kelompok.nama_kel', 'ao.nama_ao', 'omzet.cif', 'anggota.CUST_SHORT_NAME', dt_format_angka('omzet.nominal')),
        'urut' => array('omzet.id', 'omzet.tanggal', 'kelompok.nama_kel', 'ao.nama_ao', 'omzet.cif', 'anggota.CUST_SHORT_NAME', 'omzet.nominal'),
        'urut_akhir' => 'omzet.id ASC',
    ));
    $rows = $hasil['rows'];

    foreach ($rows as $row) {
        $data[] = array(
            'id'        => (string) $row['id'],
            'tanggal'   => $row['tanggal'],
            'nama_kel'  => $row['nama_kel'],
            'nama_ao'   => $row['nama_ao'],
            'cif'       => $row['cif'],
            'nama'      => $row['nama'],
            'omzet'     => number_format(compat_num($row['omzet']), 0, ',', '.')
        );
    }

    echo json_encode(array(
        'draw' => $hasil['draw'],
        'recordsTotal' => $hasil['recordsTotal'],
        'recordsFiltered' => $hasil['recordsFiltered'],
        'data' => $data,
    ));
    exit;
}

elseif ($type == 'omzet') {

    $rows = db_semua("
        SELECT
            omzet.id,
            omzet.tanggal,
            kelompok.nama_kel,
            ao.nama_ao,
            omzet.cif,
            anggota.CUST_SHORT_NAME AS nama,
            omzet.nominal AS omzet
        FROM omzet
        LEFT JOIN anggota ON anggota.cif = omzet.cif
        LEFT JOIN kelompok ON kelompok.code_kel = anggota.CODE_KEL
        LEFT JOIN ao ON ao.cao = anggota.CAO
        where anggota.UNIT =?
        ORDER BY omzet.tanggal DESC", 's', (string) $userid);

    foreach ($rows as $row) {
        $data[] = array(
            'id'        => (string) $row['id'],
            'tanggal'   => $row['tanggal'],
            'nama_kel'  => $row['nama_kel'],
            'nama_ao'   => $row['nama_ao'],
            'cif'       => $row['cif'],
            'nama'      => $row['nama'],
            'omzet'     => number_format(compat_num($row['omzet']), 0, ',', '.')
        );
    }

    echo json_encode(array('data' => $data));
    exit;
}

elseif ($type == 'calonloan') {

    $rows = db_semua("
        SELECT
            kelompok.nama_kel,
            ao.nama_ao,
            calon_loan.cif,
            calon_loan.Cust_Short_name,
            calon_loan.Plafond,
            nisbah.nisbah,
            nisbah.omzet
        FROM calon_loan
        LEFT JOIN kelompok ON kelompok.code_kel = calon_loan.code_kel
        LEFT JOIN ao ON ao.cao = calon_loan.cao
        LEFT JOIN nisbah ON nisbah.cif = calon_loan.cif
        WHERE calon_loan.status_app = 'MUSYAROKAH' and calon_loan.unit =?", 's', (string) $userid);

    foreach ($rows as $row) {
        $data[] = array(
            'nama_kel'        => $row['nama_kel'],
            'nama_ao'         => $row['nama_ao'],
            'cif'             => $row['cif'],
            'Cust_Short_name' => $row['Cust_Short_name'],
            'Plafond'         => number_format(compat_num($row['Plafond']), 0, ',', '.'),
            'nisbah'          => $row['nisbah'] === null ? null : (string) $row['nisbah'],
            'omzet'           => number_format(compat_num($row['omzet']), 0, ',', '.')
        );
    }

    echo json_encode(array('data' => $data));
    exit;
}

else {
    echo json_encode(array('data' => array(), 'error' => 'Invalid type parameter'));
    exit;
}
