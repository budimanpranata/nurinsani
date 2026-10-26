<?php require_once __DIR__ . '/../koneksi_db.php';
   $db = db_ni();
	
	if(!$db) {
		echo 'Could not connect to the database.';
	} else {
	
		if(isset($_POST['queryString'])) {
			$queryString = $db->real_escape_string($_POST['queryString']);
			
			if(strlen($queryString) >0) {

				$query = $db->query("SELECT nama_rekening,rekening_gl FROM tabel_master WHERE rekening_gl LIKE '$queryString%' and unit ='1010'");
				
				if($query) {
				echo '<ul>';
					while ($result = $query ->fetch_object()) {
	         			echo '<li onClick="fill(\''.addslashes($result->nama_rekening).'\'); fill2(\''.addslashes($result->rekening_gl).'\');">'.$result->rekening_gl.'&nbsp;&nbsp;'.$result->nama_rekening.'</li>';
	         		}
				echo '</ul>';
					
				} else {
					echo 'OOPS we had a problem :(';
				}
			} else {
				// do nothing
			}
		} else {
			echo 'There should be no direct access to this script!';
		}
	}
?>
