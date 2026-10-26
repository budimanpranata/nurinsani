<?php require_once __DIR__ . '/../../php8_compat.php';
include "../conn.php";
if(isset($_POST['update'])){
				$nim	       = $_POST['nim'];
				$ktp		   = $_POST['ktp'];
				$nama		   = $_POST['nama'];
				$tanggal_lahir = $_POST['tanggal_lahir'];
				$alamat        = $_POST['alamat'];
                $notelp        = $_POST['notelp'];
				$norek_tujuan	=$_POST['norek_tujuan'];
				$bank 			= $_POST['bank'];

		
				$update = db_query($koneksi, "UPDATE deposan SET CUST_SHORT_NAME='$nama',ID_NUMBER='$ktp',norek_tujuan='$norek_tujuan',nama_bank='$bank',BIRTH_DATE='$tanggal_lahir', HADDRESS1='$alamat', MOBILE_PHONE='$notelp' WHERE no	='$nim'") or die(mysqli_error());
				if($update){
					echo "<script>alert('Data Berhasil di Update!'); window.location = '../view/master_fund.php'</script>";
				}else{
					echo "<script>alert('Data Gagal di Update!'); window.location = '../view/master_fund.php'</script>";
				}
			}
            ?>
