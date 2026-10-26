<?php require_once __DIR__ . '/../koneksi_db.php';
session_start();
$userid=$_SESSION['userid'];
$db = db_ni();
	
	if(!$db) {
		echo 'Could not connect to the database.';
	} else {
		$dunit = $db->query("select kode_branch from user where userid='$userid'");
		while ($hasil = $dunit->fetch_object()){	
			$unit=$hasil->kode_branch;
			}
	
		if(isset($_POST['queryString'])) {
			$queryString = $db->real_escape_string($_POST['queryString']);
			
			if(strlen($queryString) >0) {
				
			$query = $db->query("SELECT nama_kel,code_kel,cao,alamat FROM kelompok_toko WHERE code_kel LIKE '$queryString%' and code_unit='$unit' or  nama_kel LIKE '$queryString%' and code_unit='$unit'");
			
				if($query) {
				echo '<ul>';
					while ($result = $query ->fetch_object()) {
	         			echo '<li onClick="fill(\''.addslashes($result->nama_kel).'\'); 
					fill2(\''.addslashes($result->code_kel).'\');
					fill3(\''.addslashes($result->cao).'\');
					fill4(\''.addslashes($result->alamat).'\');



					">'.$result->code_kel.'&nbsp;&nbsp;'.$result->nama_kel.'</li>';
	         		}
				echo '</ul>';
					
				} else {
					echo 'OOPS we had a problem :(';
				}
			} else {
				// do nothing
			}
		}else {
			echo 'There should be no direct access to this script!';
		}
	
	}
?>
