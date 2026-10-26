<?php require_once __DIR__ . '/../../php8_compat.php';
header('Content-Type: application/json');

include dirname(dirname(__FILE__)).'/db/Db.class.php';

$db = new Db();

$limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 0;
$name = isset($_GET['name']) ? $_GET['name'] : '';
$bln = isset($_GET['bln']) ? $_GET['bln'] : '';


$sql_limit = '';
if (!empty($limit)) {
    $sql_limit = ' LIMIT 0,'.$limit;
}
$sql_name = '';
$tahun='2021';
$satu='1';
$unit='1010';
if (!empty($name)) {
    $sql_name = 'month(tanggal_transaksi) between '.$satu.' and '.$bln.' and kode_rekening LIKE \'%'.$name.'%\' ';
}

$cat_list = $db->query('select unit,kode_rekening,kode_transaksi,tanggal_transaksi,keterangan_transaksi,debet,kredit from tabel_transaksi_2021
 where unit='.$unit.' and year(tanggal_transaksi)='.$tahun.' and '.$sql_name.' '.$sql_limit);



echo json_encode($cat_list);
