<?php require_once __DIR__ . '/../../koneksi_db.php';
session_start();
$userid=$_SESSION['userid'];

   $db = db_ni();
	
	if(!$db) {
		echo 'Could not connect to the database.';
	} else {
		$dunit = $db->query("select kode_branch from user where userid='$userid'");
		while ($hasil = $dunit ->fetch_object()) {	
			$unit=$hasil->kode_branch;
			}
	
		if(isset($_POST['queryString'])) {
			$queryString = $db->real_escape_string($_POST['queryString']);
			
			if(strlen($queryString) >0) {

				$query = $db->query("SELECT deposan.UNIT,deposan.CIF,deposito.NOREK,deposan.CUST_SHORT_NAME FROM deposito 
				left join deposan on deposan.cif=deposito.cif
 WHERE deposito.CIF LIKE '$queryString%' and deposito.UNIT='$unit' or deposan.CUST_SHORT_NAME LIKE '$queryString%' and deposito.UNIT='$unit'");


				
				if($query) {
				echo '<ul>';
					while ($result = $query ->fetch_object()) {
	         			echo '<li onClick="fill(\''.addslashes($result->CUST_SHORT_NAME).'\'); fill2(\''.addslashes($result->NOREK).'\');">'.$result->NOREK.'&nbsp;&nbsp;'.$result->CUST_SHORT_NAME.'&nbsp;&nbsp;'.$result->UNIT.'&nbsp;&nbsp;'.$result->nama_kel.'</li>';
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
