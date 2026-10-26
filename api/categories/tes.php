<?php require_once __DIR__ . '/../../koneksi_db.php'; 
db_ni();
mysql_select_db('nur_insani');
function curl($url){
    $ch = curl_init(); 
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
    $output = curl_exec($ch);  
    curl_close($ch);      
    return $output;
}

$curl = curl("http://localhost/nurinsani/api/categories/list.php");

// mengubah JSON menjadi array
$data = json_decode($curl, TRUE);
//var_dump($data);

//kosongkan temporari
@mysql_query("TRUNCATE tabel_master_hari");

echo "<script>alert('Temporary selesai di hapus');</script>";

@mysql_query("insert into tabel_master_hari select * from tabel_master where unit not in ('3000','3001','3002','3003','4000','4001','4002','4003')");


echo "<script>alert('Posting Koperasi Sukses');</script>";

	
?>

