<?php require_once __DIR__ . '/koneksi_db.php';
require_once __DIR__ . '/inc/cek_login.php';
wajib_login('json');
	//Connection Database
	$con = db_ni();
	if (mysqli_connect_errno())
	{
		echo "Failed to connect to MySQL: " . mysqli_connect_error();
	}
	// salinan $_POST yang sudah di-escape untuk dipakai di dalam query
	$p = array_map(function ($v) use ($con) { return is_string($v) ? mysqli_real_escape_string($con, $v) : ''; }, $_POST);

	switch ($_POST['type']) {
		
		//Tampilkan Data 
		case "get":
			
			$SQL = db_query($con, "SELECT * from pinjaman_detail WHERE id='".$p['id']."'");
			$return = db_fetch_array($SQL,MYSQLI_ASSOC);
			echo json_encode($return);
			break;
		
		//Tambah Data	
		case "new":
			
			
			$SQL = db_query($con, 
									"INSERT INTO user SET 
										userid='".$p['userid']."', 
										kode_branch='".$p['kode_cabang']."',
										pass='".md5 ($_POST['password'])."',
										level='".$p['level']."',
										nama='".$p['nama']."', 
										no_hp='".$p['no_hp']."'
																		
								");
			if($SQL){
				echo json_encode("OK");
			}
			break;
			
		//Edit Data	
		case "edit":
			
			$SQL = db_query($con, 
									"UPDATE pinjaman_detail SET 
										jumlah_bayar='".$p['jumlah_bayar']."', 
										tgl_bayar='".$p['tgl_jatuh_tempo']."', 
										status='".$p['status']."'
										WHERE id='".$p['id']."'
								");
			if($SQL){
				echo json_encode("OK");
			}			
			break;
			
		//Hapus Data	
		case "delete":
			
			$SQL = db_query($con, "DELETE FROM user WHERE userid='".$p['userid']."'");
			if($SQL){
				echo json_encode("OK");
			}			
			break;
	} 
	
?>