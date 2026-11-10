<?php require_once __DIR__ . '/koneksi_db.php';
/**
 * Sumber data DataTables (protokol lama: sEcho/iDisplayStart/sSearch, POST) untuk Approval Pengajuan
 * (data_approval.php, level lead). Daftar calon_loan dengan gol='0'.
 * Logika sama dengan versi lama; query kini prepared statement dan isi sel di-escape HTML. Perilaku lama yang
 * dipertahankan (PEKERJAAN_TERTUNDA.md bagian 38): daftar tidak disaring per area lead, sedangkan iTotalRecords
 * dihitung dari calon_loan di area lead (tanpa syarat gol).
 * Perubahan: wajib login (versi lama tanpa cek login); tautan "delete" dihapus karena tidak pernah berfungsi
 * (memanggil deleteUser dengan id kosong ke crud.php milik menu Tagihan; menu Tagihan level unit beserta
 * m_tagih.php/crud.php/serverSide.php di root sudah dihapus 2026-11-04 karena tidak dipakai).
 */
require_once __DIR__ . '/inc/tampilan.php';
session_start();
require_once __DIR__ . '/inc/cek_login.php';
wajib_level('lead', 'json');
ob_start();
require_once __DIR__ . '/inc/cek_login.php';
wajib_login('json');
$userid = (string) $_SESSION['userid'];
mb_internal_encoding('UTF-8');

// kolom tabel (urutan sama dengan kolom tampilan, kecuali Action)
$aColumns = array('code_kel', 'no_anggota', 'cif', 'Cust_Short_name', 'Plafond', 'os', 'saldo_margin');
$iColumnCount = count($aColumns);
$input = $_POST;
$ambil = function ($k) use ($input) {
    return isset($input[$k]) ? (string) $input[$k] : '';
};

// Paging
$sLimit = "";
if (isset($input['iDisplayStart']) && $ambil('iDisplayLength') != '-1') {
    $sLimit = " LIMIT " . intval($input['iDisplayStart']) . ", " . intval($ambil('iDisplayLength'));
}

// Ordering (kolom di luar daftar, mis. Action, diabaikan)
$aOrderingRules = array();
if (isset($input['iSortCol_0'])) {
    $iSortingCols = intval($ambil('iSortingCols'));
    for ($i = 0; $i < $iSortingCols; $i++) {
        $kol = intval($ambil('iSortCol_' . $i));
        if ($ambil('bSortable_' . $kol) == 'true' && isset($aColumns[$kol])) {
            $aOrderingRules[] = "`" . $aColumns[$kol] . "` " . ($ambil('sSortDir_' . $i) === 'asc' ? 'asc' : 'desc');
        }
    }
}
$sOrder = $aOrderingRules ? " ORDER BY " . implode(", ", $aOrderingRules) : "";

// Filtering: pencarian umum (OR antar kolom) lalu pencarian per kolom (AND)
$aFilteringRules = array();
$tipe = '';
$nilai = array();
if ($ambil('sSearch') != "") {
    $atau = array();
    for ($i = 0; $i < $iColumnCount; $i++) {
        if ($ambil('bSearchable_' . $i) == 'true') {
            $atau[] = "`" . $aColumns[$i] . "` LIKE ?";
            $tipe .= 's';
            $nilai[] = '%' . $ambil('sSearch') . '%';
        }
    }
    if ($atau) {
        $aFilteringRules[] = '(' . implode(" OR ", $atau) . ')';
    }
}
for ($i = 0; $i < $iColumnCount; $i++) {
    if ($ambil('bSearchable_' . $i) == 'true' && $ambil('sSearch_' . $i) != '') {
        $aFilteringRules[] = "`" . $aColumns[$i] . "` LIKE ?";
        $tipe .= 's';
        $nilai[] = '%' . $ambil('sSearch_' . $i) . '%';
    }
}
$sWhere = $aFilteringRules ? " WHERE gol ='0' AND  " . implode(" AND ", $aFilteringRules) : " WHERE gol ='0'";

$rResult = db_semua("SELECT `" . implode("`, `", $aColumns) . "` FROM calon_loan" . $sWhere . $sOrder . $sLimit, $tipe, ...$nilai);
$hitung = db_satu("SELECT COUNT(*) as n FROM calon_loan" . $sWhere, $tipe, ...$nilai);
$iFilteredTotal = $hitung ? (int) $hitung['n'] : 0;
$total = db_satu("SELECT COUNT(`no_anggota`) as n FROM  calon_loan inner join branch on branch.kode_branch = calon_loan.unit where code_area =?", 's', $userid);
$iTotal = $total ? (int) $total['n'] : 0;

$output = array(
    "sEcho" => intval($ambil('sEcho')),
    // dikirim sebagai teks seperti versi lama (hasil fetch_row)
    "iTotalRecords" => (string) $iTotal,
    "iTotalDisplayRecords" => (string) $iFilteredTotal,
    "aaData" => array(),
);
foreach ($rResult as $aRow) {
    $btn = '<a href="new4.php?cif=' . h(rawurlencode((string) $aRow['cif'])) . '" class="btn btn-sm btn-ni">View Data</a>';
    $output['aaData'][] = array(
        h($aRow['code_kel']), h($aRow['no_anggota']), h($aRow['cif']), h($aRow['Cust_Short_name']),
        number_format(compat_num($aRow['Plafond'])), number_format(compat_num($aRow['os'])), number_format(compat_num($aRow['saldo_margin'])),
        $btn,
    );
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode($output);
