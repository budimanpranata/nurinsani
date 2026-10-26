<?php require_once __DIR__ . '/../koneksi_db.php';
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

				$query = $db->query("SELECT id_member ,Cust_Short_name,unit,code_musa,nama_kel FROM musawamah 
left join kelompok_toko on musawamah.code_kel = kelompok_toko.code_kel				
WHERE id_member LIKE '$queryString%' and UNIT='$unit' or  Cust_Short_name LIKE '$queryString%' and UNIT='$unit'");


				
				if($query) {
				echo '<ul>';
					while ($result = $query ->fetch_object()) {
	         			echo '<li onClick="fill(\''.addslashes($result->Cust_Short_name).'\'); fill2(\''.addslashes($result->code_musa).'\');">'.$result->code_musa.'&nbsp;&nbsp;'.$result->Cust_Short_name.'&nbsp;&nbsp;'.$result->unit.'&nbsp;&nbsp;'.$result->nama_kel.'</li>';
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
