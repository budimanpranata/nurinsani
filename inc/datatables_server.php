<?php
/**
 * Bantuan DataTables server-side (parameter draw/start/length/search/order dari DataTables 1.10, lewat GET).
 * Pencarian meniru pencarian DataTables di browser: setiap kata (dipisah spasi) harus cocok di salah satu kolom.
 *
 *   $hasil = dt_halaman(array(
 *       'pilih' => 'omzet.id, omzet.tanggal, ...',          // daftar kolom SELECT
 *       'dari'  => 'FROM omzet ... WHERE anggota.UNIT = ?', // FROM + WHERE dasar
 *       'tipe'  => 's', 'nilai' => array($unit),            // parameter untuk 'dari'
 *       'cari'  => array(ekspresi SQL per kolom tabel, dalam bentuk teks yang tampil),
 *       'urut'  => array(ekspresi SQL per kolom tabel untuk ORDER BY),
 *       'urut_akhir' => 'omzet.id ASC',                     // penentu urutan bila nilai sama
 *   ));
 *   // $hasil = array('draw' => ..., 'recordsTotal' => ..., 'recordsFiltered' => ..., 'rows' => array(...))
 */
require_once __DIR__ . '/../koneksi_db.php';

function dt_halaman($cfg)
{
    $dari = $cfg['dari'];
    $tipe = $cfg['tipe'];
    $nilai = $cfg['nilai'];

    $cari = isset($_GET['search']['value']) ? trim((string) $_GET['search']['value']) : '';
    $syarat = '';
    $tipe_cari = $tipe;
    $nilai_cari = $nilai;
    foreach (preg_split('/\s+/', $cari, -1, PREG_SPLIT_NO_EMPTY) as $kata) {
        $atau = array();
        foreach ($cfg['cari'] as $ekspresi) {
            $atau[] = "$ekspresi LIKE ?";
            $tipe_cari .= 's';
            $nilai_cari[] = '%' . addcslashes($kata, '%_\\') . '%';
        }
        $syarat .= ' AND (' . implode(' OR ', $atau) . ')';
    }

    $urut = array();
    if (isset($_GET['order']) && is_array($_GET['order'])) {
        foreach ($_GET['order'] as $o) {
            $i = isset($o['column']) ? (int) $o['column'] : -1;
            if (isset($cfg['urut'][$i])) {
                $urut[] = $cfg['urut'][$i] . (isset($o['dir']) && $o['dir'] === 'desc' ? ' DESC' : ' ASC');
            }
        }
    }
    $urut[] = $cfg['urut_akhir'];
    $mulai = max(0, isset($_GET['start']) ? (int) $_GET['start'] : 0);
    $jumlah = isset($_GET['length']) ? (int) $_GET['length'] : 10;
    $jumlah = $jumlah < 1 ? 10 : min($jumlah, 1000);

    $panggil = function ($fungsi, $sql, $t, $v) {
        return call_user_func_array($fungsi, array_merge(array($sql, $t), $v));
    };
    $total = $panggil('db_satu', "SELECT COUNT(*) AS n $dari", $tipe, $nilai);
    $tersaring = $syarat === '' ? $total : $panggil('db_satu', "SELECT COUNT(*) AS n $dari$syarat", $tipe_cari, $nilai_cari);
    $rows = $panggil('db_semua', 'SELECT ' . $cfg['pilih'] . " $dari$syarat ORDER BY " . implode(', ', $urut) . " LIMIT $mulai, $jumlah", $tipe_cari, $nilai_cari);

    return array(
        'draw' => isset($_GET['draw']) ? (int) $_GET['draw'] : 0,
        'recordsTotal' => $total ? (int) $total['n'] : 0,
        'recordsFiltered' => $tersaring ? (int) $tersaring['n'] : 0,
        'rows' => $rows,
    );
}

/** Ekspresi SQL angka dalam format tampilan number_format($x, 0, ',', '.'), mis. 1.500.000 (untuk pencarian). */
function dt_format_angka($kolom)
{
    // NULL tampil sebagai 0 (number_format(compat_num(null)))
    return "REPLACE(FORMAT(IFNULL($kolom, 0), 0), ',', '.')";
}
