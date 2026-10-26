<?php require_once __DIR__ . '/koneksi_db.php';
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

				$query = $db->query("SELECT nama_kel,loan.no_anggota,tgl_wakalah, anggota.CIF,anggota.CUST_SHORT_NAME,anggota.CODE_KEL,anggota.NOREK,anggota.UNIT,loan.DEAL_TYPE,HADDRESS1,HADDRESS2,HADDRESS3,HADDRESS4,HADDRESS5,HPOST_CODE,MOBILE_PHONE,HOME_PHONE,GENDER,BIRTH_DATE,
				ID_NUMBER,CITIZENSHIP,TEMPAT_LAHIR,MARITAL_STATUS,RELIGION,MOTHER_NAME,NPWP,SOURCE_INCOME,EDUCATION,ID_EXPIRED,WARIS,loan.CAO,anggota.USERID,tgl_wakalah,Plafond,usaha,sum(os)-sum(saldo_margin) as POKOK,saldo_margin as MARGIN,ke as KE
				FROM anggota
				left join loan on anggota.CIF=loan.cif
				inner join kelompok on loan.code_kel = kelompok.code_kel
				WHERE anggota.CIF LIKE '$queryString%' and anggota.UNIT='$unit' or anggota.CUST_SHORT_NAME  LIKE '$queryString%' and anggota.UNIT='$unit' group by cif");
				
				
				
				if($query) {
				echo '<ul>';
					while ($result = $query ->fetch_object()) {
	         			echo '<li onClick="fill(\''.addslashes($result->CUST_SHORT_NAME).'\'); 
						fill2(\''.addslashes($result->CIF).'\');
						fill3(\''.addslashes($result->POKOK).'\');
						fill4(\''.addslashes($result->MARGIN).'\');
						fill5(\''.addslashes($result->KE).'\');
						fill6(\''.addslashes($result->HADDRESS1).'\');
						
						
						
						
						
									
						">'.$result->nama_kel.'&nbsp;&nbsp;'.$result->CIF.'&nbsp;&nbsp;'.$result->CUST_SHORT_NAME.'</li>';
	         		}
				echo '</ul>';
					
				} else {
					echo 'DATA TIDAK DITEMUKAN :(';
				}
			} else {
				// do nothing
			}
		} else {
			echo 'MAAF..Request dalam prosess..!';
		}
	}
?>
