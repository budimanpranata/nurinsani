<?php require_once __DIR__ . '/../php8_compat.php';

session_start();
ob_start();
$buss_date=date("y-d-m");
$userid=$_SESSION['userid'];
include "config.php";
if(!isset($_SESSION['userid'])){
   header("location:../index.html");//jika belum login jangan lanjut..
}
include "../conn2.php";
$koneksi=open_connection2();

switch ( $_GET['p'] ) {

	default: echo "Halaman tidak ditemukan"; break;

	case "tambah":

		$nik 	= $_POST['nik'];
		$nama = $_POST['nama'];
		$alamat = $_POST['alamat'];
		$gender = $_POST['gender'];
		$status= $_POST['status'];
		$lahir = $_POST['lahir'];
		$tlp = $_POST['tlp'];
		$kota=$_POST['kota'];
		empty( $nik ) 	 ? $err[] = "<h5>* NIK Masih Kosong</h5>" : "";
		empty( $nama ) 	 ? $err[] = "<h5>* NAMA Masih Kosong</h5>" : "";
		empty( $alamat ) ? $err[] = "<h5>* ALAMAT masih kosong</h5>" : "";
		empty( $gender ) ? $err[] = "<h5>* gender Masih Kosong</h5>" : "";
		empty( $status ) ? $err[] = "<h5>* STATUS Kosong</h5>" : "";
		empty( $lahir ) ? $err[] = "<h5>* TGL LAHIR Masih Kosong</h5>" : "";
		empty( $tlp) ? $err[] = "<h5>* TLP Masih Kosong</h5>" : "";

		// Cek apakah nopenn belum terdaftar
		$sql = $conn->query("SELECT NIK from person WHERE NIK = '$nik' ");
		
		if ( $sql->num_rows > 0 ) { $err[] = "<h5>* NIK telah terdaftar</h5>"; }

		if ( isset( $err ) ) {

			foreach ( $err as $val ) {
				echo $val;
			}

		} else {
		$pass = $nik;
		$password = md5($pass);

			$conn->query("INSERT INTO person VALUES ('$nik','$nama','$alamat','$lahir','Sales Marketing','$userid','$tlp','$buss_date','$gender','$status')");
			$conn->query("INSERT INTO user VALUES ('$nik','0','$password','smo','$nama','0')");
			$conn->query("INSERT INTO smo VALUES ('$nik','$nama','$tlp','1','$kota','0')");
			$_SESSION['info'] = "Menyimpan";
			echo "Sukses";

		}

	break;

	case "update":
		$nik 	= $_POST['nik'];
		$nama = $_POST['nama'];
		$alamat = $_POST['alamat'];
		$gender = $_POST['gender'];
		$status= $_POST['status'];
		$lahir = $_POST['lahir'];
		$tlp = $_POST['tlp'];
		empty( $nama ) 	 ? $err[] = "<h5>* NAMA Masih Kosong</h5>" : "";
		empty( $alamat ) ? $err[] = "<h5>* ALAMAT masih kosong</h5>" : "";
		empty( $gender ) ? $err[] = "<h5>* gender Masih Kosong</h5>" : "";
		empty( $status ) ? $err[] = "<h5>* STATUS Kosong</h5>" : "";
		empty( $lahir ) ? $err[] = "<h5>* TGL LAHIR Masih Kosong</h5>" : "";
		empty( $tlp) ? $err[] = "<h5>* TLP Masih Kosong</h5>" : "";		


		// Cek apakah NIM belum terdaftar
		//$sql = $conn->query("SELECT NOPEN from feed_back WHERE NOPEN = '$nopen' AND id !='$id'");
		
		//if ( $sql->num_rows > 0 ) { $err[] = "<h5>* NOPEN telah terdaftar</h5>"; }

		if ( isset( $err ) ) {

			foreach ( $err as $val ) {
				echo $val;
			}

		} else {
			$update=("UPDATE person SET Nama='$nama',alamat='$alamat',No_tlp='$tlp',status='$status',gender='$gender' WHERE NIK='$nik'");
			$person = mysql_query($update);
if ($person){ 
			$_SESSION['info'] = "ubah	";
			echo "Sukses";

		}else
{
  echo "Maaf terjadi kegalan input data, silahkan input <a href=\"javascript:history.back()\">kembali</a> ";
}
}
	break;

	case "hapus" :

		$id = $_GET['nik'];
		$conn->query("DELETE FROM person WHERE NIK= '$nik'");
		$conn->query("DELETE FROM user WHERE userid= '$nik'");
		$conn->query("DELETE FROM user WHERE kode_ds= '$nik'");
		$_SESSION['info'] = "hapus";
		header("lonikcation:feedback_edit.php");

	break;
}

ob_end_flush();

?>