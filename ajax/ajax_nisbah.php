<?php require_once __DIR__ . '/../koneksi_db.php';
/**
 * Data menu Monitoring Nisbah (view_nisbah.php). Juga dipakai lewat ajax_nisbah.php di root.
 *   type=omzet     : jadwal musyarakah (pinjaman_detail_mus) pembiayaan aktif (os > 0) unit user; bila ada parameter draw,
 *                    dijawab per halaman (DataTables server-side) seperti yang dipakai view_nisbah.php
 *   type=calonloan : pembiayaan MUSYAROKAH unit user beserta nisbah & omzet
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
    // Perbaikan 2026-10-31 (perintah user): mode server-side DataTables, hanya satu halaman yang dikirim.
    // Kolom, filter, dan format angka sama dengan mode lengkap di bawah.
    $hasil = dt_halaman(array(
        'pilih' => 'pinjaman_detail_mus.id, nama_kel, nama_ao, loan.Cust_Short_name as nama, pinjaman_detail_mus.cif as cif, cicilan,
            pinjaman_detail_mus.nisbah as nisbah, pinjaman_detail_mus.nisbah_anggota as nisbah_anggota, tgl_bayar as tanggal',
        'dari' => "from pinjaman_detail_mus
            inner join loan on loan.cif = pinjaman_detail_mus.cif
            left join kelompok on kelompok.code_kel = loan.code_kel
            left join ao on ao.cao = loan.cao
            where os > 0 and loan.unit = ?",
        'tipe' => 's',
        'nilai' => array((string) $userid),
        // nisbah dicari dalam bentuk tampilannya (1.234.567), sama seperti pencarian di browser dulu
        'cari' => array('pinjaman_detail_mus.id', 'tgl_bayar', 'kelompok.nama_kel', 'ao.nama_ao', 'pinjaman_detail_mus.cif', 'loan.Cust_Short_name',
            'pinjaman_detail_mus.cicilan', dt_format_angka('pinjaman_detail_mus.nisbah'), dt_format_angka('pinjaman_detail_mus.nisbah_anggota')),
        'urut' => array('pinjaman_detail_mus.id', 'tgl_bayar', 'kelompok.nama_kel', 'ao.nama_ao', 'pinjaman_detail_mus.cif', 'loan.Cust_Short_name',
            'pinjaman_detail_mus.cicilan', 'pinjaman_detail_mus.nisbah', 'pinjaman_detail_mus.nisbah_anggota'),
        'urut_akhir' => 'pinjaman_detail_mus.cif, pinjaman_detail_mus.id, pinjaman_detail_mus.cicilan',
    ));

    foreach ($hasil['rows'] as $row) {
        $data[] = array(
            'id'        => $row['id'],
            'tanggal'   => $row['tanggal'],
            'nama_kel'  => $row['nama_kel'],
            'nama_ao'   => $row['nama_ao'],
            'cif'       => $row['cif'],
            'nama'      => $row['nama'],
            'cicilan'   => $row['cicilan'] === null ? null : (string) $row['cicilan'],
            'nisbah'     => number_format(compat_num($row['nisbah']), 0, ',', '.'),
            'nisbah_anggota'     => number_format(compat_num($row['nisbah_anggota']), 0, ',', '.')
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
            id,nama_kel,nama_ao, loan.Cust_Short_name as nama,
            pinjaman_detail_mus.cif as cif, cicilan, pinjaman_detail_mus.nisbah as nisbah, pinjaman_detail_mus.nisbah_anggota as nisbah_anggota,
            tgl_bayar as tanggal from pinjaman_detail_mus
            left join loan on loan.cif = pinjaman_detail_mus.cif
            left join kelompok on kelompok.code_kel = loan.code_kel
            left join ao on ao.cao = loan.cao
            where os > 0 and loan.unit =? order by pinjaman_detail_mus.cif, id, cicilan ASC", 's', (string) $userid);

    foreach ($rows as $row) {
        $data[] = array(
            'id'        => $row['id'],
            'tanggal'   => $row['tanggal'],
            'nama_kel'  => $row['nama_kel'],
            'nama_ao'   => $row['nama_ao'],
            'cif'       => $row['cif'],
            'nama'      => $row['nama'],
            'cicilan'   => $row['cicilan'] === null ? null : (string) $row['cicilan'],
            'nisbah'     => number_format(compat_num($row['nisbah']), 0, ',', '.'),
            'nisbah_anggota'     => number_format(compat_num($row['nisbah_anggota']), 0, ',', '.')
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
            loan.cif,
            loan.Cust_Short_name,
            loan.Plafond,
            nisbah.nisbah,
            nisbah.omzet
        FROM loan
        LEFT JOIN kelompok ON kelompok.code_kel = loan.code_kel
        LEFT JOIN ao ON ao.cao = loan.cao
        LEFT JOIN nisbah ON nisbah.cif = loan.cif
        WHERE loan.status_app = 'MUSYAROKAH' and loan.unit =?", 's', (string) $userid);

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
