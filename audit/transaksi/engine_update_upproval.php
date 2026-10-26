<?php require_once __DIR__ . '/../../php8_compat.php';
session_start();
ob_start();
$userid=$_SESSION['userid'];

include "../../config.php";
include "../../function.php";
	$sql = $conn->query("select kode_branch,GL from branch WHERE kode_branch = '$userid'");
	while ( $r = $sql->fetch_assoc() ) {
		$unit=$r['kode_branch'];
		$gl=$r['GL'];
		$rak_unit="1010-$gl";
$unit=$r['kode_branch'];
	}
switch ( $_GET['p'] ) {

	default: echo "Halaman tidak ditemukan"; break;

	case "tambah":

		
	break;

	case "update":
	
		$unitt = $_POST['unit'];
		$buss_date = $_POST['jatuh'];
		$nomi = $_POST['nomi'];
		$ket =$_POST['ket'];
		$tgl=$_POST['jatuh'];
		$kode_transaksi=$_POST['kode_trn'];
		$rek =$_POST['code'];
		$checkbox=$_POS['checkbox'];
if (isset($_POST['checkbox'])){
$status_app='PENDING';
}else{
$status_app='APPROVE';
}
		empty( $tgl ) 	 ? $err[] = "<h5>* TANGGAL Masih Kosong</h5>" : "";
		empty( $rek) ? $err[] = "<h5>* REKENING GL Masih Kosong</h5>" : "";
		empty( $nomi ) ? $err[] = "<h5>* NOMINAL Masih Kosong</h5>" : "";
		
	$sql = $conn->query("select kode_branch,GL from branch WHERE kode_branch = '$unitt'");
	while ( $r = $sql->fetch_assoc() ) {
		$unit=$r['kode_branch'];
		$gl=$r['GL'];
		$rak_unit="1010-$gl";
		}	


		// Cek apakah NOPEN belum terdaftar
		//$sql = $conn->query("SELECT NOPEN from feed_back WHERE NOPEN = '$nopen' AND id !='$id'");
		
		//if ( $sql->num_rows > 0 ) { $err[] = "<h5>* NOPEN telah terdaftar</h5>"; }

		if ( isset( $err ) ) {

			foreach ( $err as $val ) {
				echo $val;
			}

		} else {
			
								
					$conn->query("insert into jurnal_umum(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values('','$kode_transaksi','$buss_date','1010')");
					$conn->query("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
					values('1010','$kode_transaksi','$rak_unit','$buss_date','Bukti Umum','$ket','$nomi','$userid')");
					

					
					$conn->query("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
					values('1010','$kode_transaksi','$rek','$buss_date','Bukti Umum','$ket','$nomi','$userid')");
					
					
					$conn->query("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
					values('$unitt','$kode_transaksi','1120000','$buss_date','Bukti Umum','$ket','$nomi','$userid')");
					

					
					$conn->query("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
					values('$unitt','$kode_transaksi','2500000','$buss_date','Bukti Umum','$ket','$nomi','$userid')");					
					
					//$conn->query("update deposito_detail set jumlah_nisbah ='$nomi', ket ='BAYAR', tgl_bayar ='$tgl' where tgl_jatuh_tempo ='$tgl' and id_deposito='$norek'");
					$conn->query("Update calon_loan set status_app='$status_app' where unit ='$unitt' and tgl_wakalah='$tgl'");
					
					
					
					
					

							//$conn->query("delete from loan  WHERE no = '$id'");
			$_SESSION['info'] = "Mengubah";
			echo "Sukses";

		}

	break;

	case "hapus" :

		$id = $_GET['id'];
		echo "Maaf ada tidak bisa mengkases menu ini silahkan hubungi atasan anda";
		$_SESSION['info'] = "GAGAL";
		header("location:view/view_jatuh.php");

	break;
}

ob_end_flush();

?>
