<?php require_once __DIR__ . '/../koneksi_db.php';
   $db = db_ni();
	
	if(!$db) {
		echo 'Could not connect to the database.';
	} else {
	
		if(isset($_POST['queryString'])) {
			$queryString = $db->real_escape_string($_POST['queryString']);
			
			if(strlen($queryString) >0) {

				$query = $db->query("SELECT MAX(buss_date)  FROM param_rasio WHERE buss_date LIKE '$queryString%'");
				
				if($query) {
				echo '<ul>';
					while ($result = $query ->fetch_object()) {
	         			echo '<li onClick="fill(\''.addslashes($result->code_jenis).'\'); fill2(\''.addslashes($result->buss_date).'\');">'.$result->buss_date.'&nbsp;&nbsp;'.$result->code_jenis.'</li>';
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
