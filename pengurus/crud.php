<?php require_once __DIR__ . '/../koneksi_db.php';
session_start();
$userid=$_SESSION['userid'];
$tgl_system=$_SESSION['stgl_system'];
if(!isset($_SESSION['userid'])){
   header("location:index.html");
 exit; }


	//Connection Database
	$con = db_ni();
	if (mysqli_connect_errno())
	{
		echo "Failed to connect to MySQL: " . mysqli_connect_error();
	}
	
	switch ($_POST['type']) {
		
		//Tampilkan Data 
		case "get":
			
			$SQL = db_query($con, "SELECT * from tabel_transaksi WHERE kode_transaksi='".$_POST['id']."'");
			$return = db_fetch_array($SQL,MYSQLI_ASSOC);
			echo json_encode($return);
			break;
		
		//Tambah Data	
		case "new":
			
			
			$SQL = db_query($con, 
									"INSERT INTO pinjaman_detail SET 
										id_pinjam='".$_POST['id_pinjam']."', 
										kode_branch='".$_POST['kode_cabang']."',
										pass='".md5 ($_POST['password'])."',
										level='".$_POST['level']."',
										nama='".$_POST['nama']."', 
										no_hp='".$_POST['no_hp']."'
																		
								");
			if($SQL){
				echo json_encode("OK");
			}
			break;
			
		//Edit Data	
		case "edit":
			
			$SQL = db_query($con, 
									"UPDATE pinjaman_detail SET 
										jumlah_bayar='".$_POST['jumlah_bayar']."', 
										tgl_bayar='".$_POST['tgl_jatuh_tempo']."', 
										status='".$_POST['status']."'
										WHERE id='".$_POST['id']."'
								");
			if($SQL){
				echo json_encode("OK");
			}			
			break;
			
		//Hapus Data	
		case "delete":
			$result = db_query($con,"select cif,type,buss_date FROM simpanan_kp WHERE kode_transaksi='".$_POST['id']."'");
			while($row = db_fetch_array($result)){
			$cif=$row['cif'];
			$type=$row['type'];
			$buss_date=$row['buss_date'];
			}
			
			if ($type =='03'){

				$q=db_query($con,"DELETE FROM rek_loan WHERE cif='$cif' and tgl_realisasi='$buss_date'");

			}else{	
			$q=db_query($con,"DELETE FROM simpanan_kp WHERE kode_transaksi='".$_POST['id']."'");

				 }
						
			$SQL = db_query($con, "DELETE FROM tabel_transaksi WHERE kode_transaksi='".$_POST['id']."'");
			
			if($SQL){
				echo json_encode("OK");
			}			
			break;
	} 
	
?>
