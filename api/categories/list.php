<?php require_once __DIR__ . '/../../php8_compat.php';
header('Content-Type: application/json');

include dirname(dirname(__FILE__)).'/db/Db.class.php';

$db = new Db();

$limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 0;
$name = isset($_GET['name']) ? $_GET['name'] : '';

$sql_limit = '';
if (!empty($limit)) {
    $sql_limit = ' LIMIT 0,'.$limit;
}
$sql_name = '';
if (!empty($name)) {
    $sql_name = ' where rekening_gl LIKE \'%'.$name.'%\' ';
}

$cat_list = $db->query('select rekening_gl,kode_rekening,unit,nama_rekening,tanggal_awal,awal_debet,
awal_kredit,mut_debet,mut_kredit,sisa_debet,sisa_kredit,posisi,normal,gr_head,gr_sub,gr_neraca,saldo_awal,saldo_akhir,LINE_BALANCE 

 from tabel_master '.$sql_name.' '.$sql_limit);



echo json_encode($cat_list);