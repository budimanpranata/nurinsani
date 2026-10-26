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

				$query = $db->query("SELECT nama_kel,calon_musa.no_anggota,tgl_wakalah, member.id_cust,member.CUST_SHORT_NAME,member.CODE_KEL,member.NOREK,member.UNIT,calon_musa.DEAL_TYPE,HADDRESS1,HADDRESS2,HADDRESS3,HADDRESS4,HADDRESS5,HPOST_CODE,MOBILE_PHONE,HOME_PHONE,GENDER,BIRTH_DATE,
				ID_NUMBER,CITIZENSHIP,TEMPAT_LAHIR,MARITAL_STATUS,RELIGION,MOTHER_NAME,NPWP,SOURCE_INCOME,EDUCATION,ID_EXPIRED,WARIS,calon_musa.CAO,member.USERID,tgl_wakalah,Plafond,usaha, 
				barang_satu,barang_dua,barang_tiga,barang_empat,barang_lima, barang_enam, barang_tujuh, barang_sembilan, barang_sepuluh,Plafond,
				harga_satu,harga_dua,harga_tiga,harga_empat,harga_lima,harga_enam,harga_tujuh, harga_sembilan,harga_sepuluh,nama_usaha,tgl_akad,tenor,pekerjaan_pasangan,suffix
				FROM member
				left join calon_musa on member.id_cust=calon_musa.id_member
				left join barang on barang.id_barang=calon_musa.no_anggota
				left join kelompok_toko on calon_musa.code_kel = kelompok_toko.code_kel
				WHERE member.id_cust LIKE '$queryString%' and member.UNIT='$unit' or member.CUST_SHORT_NAME  LIKE '$queryString%' and member.UNIT='$unit'");
				
				
				if($query) {
				echo '<ul>';
					while ($result = $query ->fetch_object()) {
	         			echo '<li onClick="fill(\''.addslashes($result->CUST_SHORT_NAME).'\'); 
						fill2(\''.addslashes($result->id_cust).'\');
						fill3(\''.addslashes($result->NOREK).'\');
						fill4(\''.addslashes($result->UNIT).'\');
						fill5(\''.addslashes($result->MOTHER_NAME).'\');
						fill6(\''.addslashes($result->HADDRESS1).'\');
						fill7(\''.addslashes($result->HADDRESS2).'\');
						fill8(\''.addslashes($result->HADDRESS3).'\');
						fill9(\''.addslashes($result->HADDRESS4).'\');
						fill10(\''.addslashes($result->CODE_KEL).'\');
						fill11(\''.addslashes($result->WARIS).'\');
						fill12(\''.addslashes($result->MOBILE_PHONE).'\');
						fill13(\''.addslashes($result->BIRTH_DATE).'\');
						fill14(\''.addslashes($result->TEMPAT_LAHIR).'\');
						fill15(\''.addslashes($result->CODE_KEL).'\');
						fill16(\''.addslashes($result->EDUCATION).'\');
						fill17(\''.addslashes($result->NPWP).'\');
						fill18(\''.addslashes($result->HPOST_CODE).'\');
						fill19(\''.addslashes($result->MARITAL_STATUS).'\');
						fill20(\''.addslashes($result->ID_EXPIRED).'\');
						fill21(\''.addslashes($result->ID_NUMBER).'\');
						fill22(\''.addslashes($result->SOURCE_INCOME).'\');
						fill23(\''.addslashes($result->GENDER).'\');
						fill24(\''.addslashes($result->CAO).'\');
						fill25(\''.addslashes($result->tgl_wakalah).'\');
						fill26(\''.addslashes($result->HADDRESS5).'\');
						fill28(\''.addslashes($result->barang_satu).'\');
						fill29(\''.addslashes($result->barang_dua).'\');
						fill30(\''.addslashes($result->barang_tiga).'\');
						fill31(\''.addslashes($result->barang_empat).'\');
						fill32(\''.addslashes($result->barang_lima).'\');
						fill33(\''.addslashes($result->barang_enam).'\');
						fill34(\''.addslashes($result->barang_tujuh).'\');
						fill35(\''.addslashes($result->barang_delapan).'\');
						fill36(\''.addslashes($result->barang_sembilan).'\');
						fill37(\''.addslashes($result->barang_sepuluh).'\');
						fill38(\''.addslashes($result->harga_satu).'\');
						fill39(\''.addslashes($result->harga_dua).'\');
						fill40(\''.addslashes($result->harga_tiga).'\');
						fill41(\''.addslashes($result->harga_empat).'\');
						fill42(\''.addslashes($result->harga_lima).'\');
						fill43(\''.addslashes($result->harga_enam).'\');
						fill44(\''.addslashes($result->harga_tujuh).'\');
						fill45(\''.addslashes($result->harga_delapan).'\');
						fill46(\''.addslashes($result->harga_sembilan).'\');
						fill47(\''.addslashes($result->harga_sepuluh).'\');
						fill48(\''.addslashes($result->Plafond).'\');
						fill49(\''.addslashes($result->usaha).'\');
						fill50(\''.addslashes($result->no_anggota).'\');
						fill51(\''.addslashes($result->tgl_wakalah).'\');
						fill52(\''.addslashes($result->nama_usaha).'\');
						fill53(\''.addslashes($result->tgl_akad).'\');
						fill54(\''.addslashes($result->tenor).'\');
						fill55(\''.addslashes($result->pekerjaan_pasangan).'\');
						fill56(\''.addslashes($result->suffix).'\');		
						
						
									
						">'.$result->nama_kel.'&nbsp;&nbsp;'.$result->id_cust.'&nbsp;&nbsp;'.$result->CUST_SHORT_NAME.'</li>';
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
