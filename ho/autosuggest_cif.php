<?php require_once __DIR__ . '/../koneksi_db.php';
session_start();
ob_start();
$userid=$_SESSION['userid'];
   $db = db_ni();
   $query = $db->query("select area from level where userid='$userid'");
   while ($result = $query ->fetch_object()) {
$area=$result->area;
   }
	
	if(!$db) {
		echo 'Could not connect to the database.';
	} else {
	
		if(isset($_POST['queryString'])) {
			$queryString = $db->real_escape_string($_POST['queryString']);
			
			if(strlen($queryString) >0) {

				$query = $db->query("SELECT CUST_SHORT_NAME,cif FROM anggota inner join branch on branch.kode_branch = anggota.unit 
				WHERE code_region='$area' and cif LIKE '$queryString%' ");
				
				if($query) {
				echo '<ul>';
					while ($result = $query ->fetch_object()) {
	         			echo '<li onClick="fill(\''.addslashes($result->CUST_SHORT_NAME).'\'); fill2(\''.addslashes($result->cif).'\');">'.$result->cif.'&nbsp;&nbsp;'.$result->CUST_SHORT_NAME.'</li>';
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
