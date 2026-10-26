<?php require_once __DIR__ . '/../../php8_compat.php';
include "../conn.php";
if(isset($_POST['update'])){
				$norek	       = $_POST['norek'];
				$rasio		   = $_POST['rasio'];
				$eq_rate		   = $_POST['eq_rate'];
				$cara_hitung = $_POST['cara_hitung'];
				$nama_bank        = $_POST['nama_bank'];
                $norek_bank        = $_POST['norek_bank'];
			

		
				$update = db_query($koneksi, "UPDATE deposito SET eq_rate='$eq_rate',rasio='$rasio',norek_bank='$norek_bank',nama_bank='$nama_bank',cara_hitung='$cara_hitung', status_print ='ADVIS'  WHERE norek ='$norek'") or die(mysqli_error());
				if($update){
					echo "<script>alert('Data Berhasil di Update!'); window.location = '../view/master_trans.php'</script>";
				}else{
					echo "<script>alert('Data Gagal di Update!'); window.location = '../view/master_fund.trans'</script>";
				}
			}
            ?>
