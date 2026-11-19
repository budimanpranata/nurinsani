<?php require_once __DIR__ . '/../koneksi_db.php'; require_once __DIR__ . '/../inc/api_token.php'; 
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../inc/cek_login.php';
wajib_level('kp');
db_ni();
mysql_select_db('nur_insani');
// 2026-11-19 (perintah user): API toko ("Posting Toko", ti.nurinsani.co.id:81) dihapus, toko tidak dipakai lagi.

//kosongkan temporari
@mysql_query("TRUNCATE tabel_master_hari");
				$jurnal_umum=mysql_fetch_array(mysql_query("SELECT tanggal_awal from tabel_master where rekening_gl ='001-1410000' "));
				$tanggal=$jurnal_umum['tanggal_awal'];

echo "<script>alert('Temporary selesai di hapus');</script>";

@mysql_query("insert into tabel_master_hari select * from tabel_master where unit not in ('3000','3001','3002','3003','4000','4001','4002','4003')");
$update=mysql_query ("UPDATE tabel_master_hari set unit ='0000'where unit ='1010' ");

echo "<script>alert('Posting Koperasi Sukses');</script>";

@mysql_query("update tabel_master_hari set tanggal_awal='$tanggal'");
		
	
?>

