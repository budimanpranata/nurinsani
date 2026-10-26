<?php require_once __DIR__ . '/koneksi_db.php';
session_start();
ob_start();
$userid=$_SESSION['userid'];
	//Connection Database
	$con = db_ni();
	if (mysqli_connect_errno())
	{
		echo "Failed to connect to MySQL: " . mysqli_connect_error();
	}
	
	switch ($_POST['type']) {
		
		//Tampilkan Data 
		case "get":
			
			$SQL = db_query($con, "SELECT * from loan WHERE unit ='$userid' and id='".$_POST['id']."'");
			$return = db_fetch_array($SQL,MYSQLI_ASSOC);
			echo json_encode($return);
			break;
		
		//Tambah Data	
		case "new":
			
			
			$SQL = db_query($con, 
									"INSERT INTO user SET 
										userid='".$_POST['userid']."', 
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
			
			$SQL = db_query($con, "DELETE FROM user WHERE userid='".$_POST['userid']."'");
			if($SQL){
				echo json_encode("OK");
			}			
			break;
	} 
	
?>
