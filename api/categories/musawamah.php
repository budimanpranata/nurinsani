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
    $sql_name = ' where os > 0 or id_member LIKE \'%'.$name.'%\' ';
}

$cat_list = $db->query('select * from musawamah where os > 0'.$sql_name.' '.$sql_limit);



echo json_encode($cat_list);
