<?php require_once __DIR__ . '/../../koneksi_db.php'; 
session_start();
ob_start();
$userid=$_SESSION['userid'];
$tgl_system=$_SESSION['stgl_system'];
//cek apakah user sudah login
if(!isset($_SESSION['userid'])){
   header("location:index.html");
}
	
db_ni();
mysql_select_db('nur_insani');


//kosongkan temporari
@mysql_query("DELETE tabel_master_hari_ah 
FROM tabel_master_hari_ah 
INNER JOIN branch ON branch.kode_branch = tabel_master_hari_ah.unit
WHERE branch.code_area = '$userid';");
				

echo "<script>alert('Temporary selesai di hapus');</script>";

@mysql_query("insert into tabel_master_hari_ah(rekening_gl,kode_rekening,unit,nama_rekening,tanggal_awal,awal_debet,
awal_kredit,mut_debet,mut_kredit,sisa_debet,sisa_kredit,posisi,normal,gr_head,gr_sub,gr_neraca,saldo_awal,saldo_akhir,LINE_BALANCE) select rekening_gl,kode_rekening,tabel_master.unit as unit,nama_rekening,tanggal_awal,awal_debet,
awal_kredit,mut_debet,mut_kredit,sisa_debet,sisa_kredit,posisi,normal,gr_head,gr_sub,gr_neraca,saldo_awal,saldo_akhir,LINE_BALANCE from tabel_master LEFT JOIN branch on branch.kode_branch = tabel_master.unit
WHERE branch.code_area ='$userid'");
//$update=mysql_query ("UPDATE tabel_master_hari_ah set unit ='0000'where unit ='1010' ");

echo "<script>alert('Posting Koperasi Sukses');</script>";



@mysql_query("UPDATE tabel_master_hari_ah
INNER JOIN branch ON branch.kode_branch = tabel_master_hari_ah.unit
SET tabel_master_hari_ah.tanggal_awal = '$tgl_system'
WHERE branch.code_area = '$userid'");

echo "<script>alert('Konsolidasi selesai');</script>";
		echo "<script>location.href='konsolidasi.php';</script>";

		

?>

