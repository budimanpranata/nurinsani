<?php require_once __DIR__ . '/../../php8_compat.php';
session_start();
ob_start();
$userid=$_SESSION['userid'];

include "../../config.php";
include "../../function.php";
	$sql = $conn->query("select kode_branch from user WHERE userid = '$userid'");
	while ( $r = $sql->fetch_assoc() ) {
		$unit=$r['kode_branch'];
	}
switch ( $_GET['p'] ) {

	default: echo "Halaman tidak ditemukan"; break;

	case "tambah":

		
	break;

	case "update":
	
		$norek = $_POST['id'];
		$buss_date = $_POST['jatuh'];
		$nomi = $_POST['nomi'];
		$rek=$_POST['code'];
		$ket =$_POST['ket'];
		$tgl=$_POST['jatuh'];

		$unit='1010';
		
		empty( $tgl ) 	 ? $err[] = "<h5>* TANGGAL Masih Kosong</h5>" : "";
		empty( $rek) ? $err[] = "<h5>* REKENING GL Masih Kosong</h5>" : "";
		empty( $nomi ) ? $err[] = "<h5>* NOMINAL Masih Kosong</h5>" : "";
		


		// Cek apakah NOPEN belum terdaftar
		//$sql = $conn->query("SELECT NOPEN from feed_back WHERE NOPEN = '$nopen' AND id !='$id'");
		
		//if ( $sql->num_rows > 0 ) { $err[] = "<h5>* NOPEN telah terdaftar</h5>"; }

		if ( isset( $err ) ) {

			foreach ( $err as $val ) {
				echo $val;
			}

		} else {
			
				$sql_st = $conn->query("select status,id_realisasi from dep_realisasi WHERE norek = '$norek'");
				while ( $st = $sql_st->fetch_assoc() ) {
				$posisi=$st['status'];	
				$id_realisasi=$st['id_realisasi'];
				$nor=$st['norek'];
				}
				if($posisi=='PENEMPATAN'){
					
					$kode_transaksi="BU/".$id_realisasi;
					$conn->query("insert into jurnal_umum(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values('','$kode_transaksi','$buss_date','$unit')");
					$conn->query("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
					values('$unit','$kode_transaksi','1010-2851000','$buss_date','Bukti Umum','$ket','$nomi','userf')");
					$conn->query("insert into jurnal_umum(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values('','$kode_transaksi','$buss_date','$unit')");
					$conn->query("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
					values('$unit','$kode_transaksi','$rek','$buss_date','Bukti Umum','$ket','$nomi','userf')");
					
					$conn->query("insert into audit_dep_realisasi select * from dep_realisasi where norek ='$norek'");
					$conn->query("delete from dep_realisasi where norek ='$norek'");
					$conn->query("update deposito set status_print='OK' where norek='$norek'");
					
					
					}elseif($posisi=='BREAK'){
						
					$kode_transaksi="BU/".$id_realisasi;
					$conn->query("insert into jurnal_umum(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values('','$kode_transaksi','$buss_date','$unit')");
					$conn->query("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
					values('$unit','$kode_transaksi','1010-2852000','$buss_date','Bukti Umum','$ket','$nomi','userf')");
					$conn->query("insert into jurnal_umum(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values('','$kode_transaksi','$buss_date','$unit')");
					$conn->query("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
					values('$unit','$kode_transaksi','$rek','$buss_date','Bukti Umum','$ket','$nomi','userf')");
					$conn->query("update deposito_detail set jumlah_nisbah ='$nomi', ket ='BAYAR', tgl_bayar ='$tgl' where tgl_jatuh_tempo ='$tgl' and id_deposito='$norek'");
					$conn->query("insert into audit_dep_realisasi select * from dep_realisasi where norek ='$norek'");
					$conn->query("delete from dep_realisasi where norek ='$norek'");
					$conn->query("delete from deposito where norek ='$norek'");
	
					}else{
					$sql_aro = $conn->query("select * from deposito WHERE norek = '$norek'");
					while ( $st_aro = $sql_aro->fetch_assoc() ) {
					$aro=$st_aro['aro'];
					$cif=$st_aro['cif'];					
					}
					if ($aro=='ARO-ARO'){
							$conn->query("insert into escrow_deposito (BUSS_DATE,NOREK,UNIT,CIF, DEBIT, TYPE,KREDIT,USERID,KET)
							values('$buss_date','$norek','$unit','$cif','0','01','$nomi','$userid','ARO')");
							$conn->query("update deposito_detail set jumlah_nisbah ='$nomi', ket ='BAYAR', tgl_bayar ='$tgl' where tgl_jatuh_tempo ='$tgl' and id_deposito='$norek'");
$conn->query("update deposito set nominal=nominal+'$nomi' where norek='$norek'");
							$conn->query("insert into audit_dep_realisasi select * from dep_realisasi where norek ='$norek'");
							$conn->query("delete from dep_realisasi where norek ='$norek'");
							$conn->query("update deposito set status_print='OK' where norek='$norek'");
						
					}else {
							$kode_transaksi="BU/".$id_realisasi;
					
							$conn->query("insert into jurnal_umum(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values('','$kode_transaksi','$buss_date','$unit')");
							$conn->query("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
							values('$unit','$kode_transaksi','1010-2837000','$buss_date','BS','$ket','$nomi','userf')");
							$conn->query("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
							values('$unit','$kode_transaksi','$rek','$buss_date','BS','$ket','$nomi','userf')");
							$conn->query("update deposito_detail set jumlah_nisbah ='$nomi', ket ='BAYAR', tgl_bayar ='$tgl' where tgl_jatuh_tempo ='$tgl' and id_deposito='$norek'");
							$conn->query("insert into audit_dep_realisasi select * from dep_realisasi where norek ='$norek'");
							$conn->query("delete from dep_realisasi where norek ='$norek'");
							$conn->query("update deposito set status_print='OK' where norek='$norek'");
							}	
					}
										

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
