<?php require_once __DIR__ . '/../koneksi_db.php'; 
		
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

$curl = curl("http://192.168.88.44/ni/api/categories/list.php");

// mengubah JSON menjadi array
$data = json_decode($curl, TRUE);
//var_dump($curl);

//kosongkan temporari
@mysql_query("TRUNCATE tabel_master_hari");
				$jurnal_umum=mysql_fetch_array(mysql_query("SELECT tanggal_awal from tabel_master where rekening_gl ='001-1410000' "));
				$tanggal=$jurnal_umum['tanggal_awal'];

echo "<script>alert('Temporary selesai di hapus');</script>";

@mysql_query("insert into tabel_master_hari select * from tabel_master where unit not in ('3000','3001','3002','3003','4000','4001','4002','4003')");
$update=mysql_query ("UPDATE tabel_master_hari set unit ='0000'where unit ='1010' ");

echo "<script>alert('Posting Koperasi Sukses');</script>";

foreach($data as $row){
	
	$rekening_gl=$row["rekening_gl"];
	$kode_rekening=$row["kode_rekening"];
	$unit=$row["unit"];
	$nama_rekening=$row["nama_rekening"];
	$tanggal_awal=$row["tanggal_awal"];
	$awal_debet=$row["awal_debet"];
	$awal_kredit=$row["awal_kredit"];
	$mut_debet=$row["mut_debet"];
	$mut_kredit=$row["mut_kredit"];
	$sisa_debet=$row["sisa_debet"];
	$sisa_kredit=$row["sisa_kredit"];
	$posisi=$row["posisi"];
	$normal=$row["normal"];
	$gr_head=$row["gr_head"];
	$gr_sub=$row["gr_sub"];
	$gr_neraca=$row["gr_neraca"];
	$saldo_awal=$row["saldo_awal"];
	$saldo_akhir=$row["saldo_akhir"];
	$LINE_BALANCE=$row["LINE_BALANCE"];
	@mysql_query("insert into tabel_master_hari(rekening_gl,kode_rekening,unit,nama_rekening,tanggal_awal,awal_debet,
awal_kredit,mut_debet,mut_kredit,sisa_debet,sisa_kredit,posisi,normal,gr_head,gr_sub,gr_neraca,saldo_awal,saldo_akhir,LINE_BALANCE) 
values('$rekening_gl','$kode_rekening','$unit','$nama_rekening','$tanggal_awal','$awal_debet',
'$awal_kredit','$mut_debet','$mut_kredit','$sisa_debet','$sisa_kredit','$posisi','$normal','$gr_head','$gr_sub','$gr_neraca',
'$saldo_awal','$saldo_akhir','$LINE_BALANCE')");
}

@mysql_query("update tabel_master_hari set tanggal_awal='$tanggal'");
echo "<script>alert('Posting Toko selesai');</script>";
		echo "Proses Selesai</script>";
		

?>

