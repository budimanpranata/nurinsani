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
wajib_login('json');
$userid=$_SESSION['userid'];
header('Content-Type: application/json; charset=utf-8');

$type =isset($_GET['type'])? $_GET['type'] : 'omzet';
$data = array();

if ($type == 'omzet' && isset($_GET['draw'])) {
    // Perbaikan 2026-10-30 (perintah user): mode server-side DataTables, hanya satu halaman yang dikirim.
    // Kolom, filter unit, dan format angka sama dengan mode lengkap di bawah.
    $dari = "FROM omzet
        INNER JOIN anggota ON anggota.cif = omzet.cif
        LEFT JOIN kelompok ON kelompok.code_kel = anggota.CODE_KEL
        LEFT JOIN ao ON ao.cao = anggota.CAO
        WHERE anggota.UNIT = ?";
    $kolom = array('omzet.id', 'omzet.tanggal', 'kelompok.nama_kel', 'ao.nama_ao', 'omzet.cif', 'anggota.CUST_SHORT_NAME', 'omzet.nominal');

    // pencarian: setiap kata harus cocok di salah satu kolom (seperti pencarian DataTables di sisi browser)
    $cari = isset($_GET['search']['value']) ? trim((string) $_GET['search']['value']) : '';
    $syarat = '';
    $tipe = 's';
    $nilai = array((string) $userid);
    foreach (preg_split('/\s+/', $cari, -1, PREG_SPLIT_NO_EMPTY) as $kata) {
        $atau = array();
        foreach ($kolom as $k) {
            // omzet dicari dalam bentuk tampilannya (1.500.000), sama seperti pencarian di browser dulu
            $atau[] = ($k === 'omzet.nominal' ? "REPLACE(FORMAT(omzet.nominal, 0), ',', '.')" : $k) . ' LIKE ?';
            $tipe .= 's';
            $nilai[] = '%' . addcslashes($kata, '%_\\') . '%';
        }
        $syarat .= ' AND (' . implode(' OR ', $atau) . ')';
    }

    $urut = array();
    if (isset($_GET['order']) && is_array($_GET['order'])) {
        foreach ($_GET['order'] as $o) {
            $i = isset($o['column']) ? (int) $o['column'] : -1;
            if (isset($kolom[$i])) {
                $urut[] = $kolom[$i] . (isset($o['dir']) && $o['dir'] === 'desc' ? ' DESC' : ' ASC');
            }
        }
    }
    $urut[] = 'omzet.id ASC';
    $mulai = max(0, isset($_GET['start']) ? (int) $_GET['start'] : 0);
    $jumlah = isset($_GET['length']) ? (int) $_GET['length'] : 10;
    $jumlah = $jumlah < 1 ? 10 : min($jumlah, 1000);

    $total = db_satu("SELECT COUNT(*) AS n $dari", 's', (string) $userid);
    $tersaring = $syarat === '' ? $total : call_user_func_array('db_satu', array_merge(array("SELECT COUNT(*) AS n $dari$syarat", $tipe), $nilai));
    $rows = call_user_func_array('db_semua', array_merge(array(
        "SELECT omzet.id, omzet.tanggal, kelompok.nama_kel, ao.nama_ao, omzet.cif, anggota.CUST_SHORT_NAME AS nama, omzet.nominal AS omzet
        $dari$syarat ORDER BY " . implode(', ', $urut) . " LIMIT $mulai, $jumlah", $tipe), $nilai));

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
        'draw' => (int) $_GET['draw'],
        'recordsTotal' => $total ? (int) $total['n'] : 0,
        'recordsFiltered' => $tersaring ? (int) $tersaring['n'] : 0,
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
