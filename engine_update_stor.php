<?php require_once __DIR__ . '/php8_compat.php';
session_start();
ob_start();
$userid=$_SESSION['userid'];
include "conn2.php";
$koneksi=open_connection2();
include "config.php";
include "function.php";

switch ( $_GET['p'] ) {

	default: echo "Halaman tidak ditemukan"; break;

	case "tambah":

		
	break;

	case "update":
		$id = $_POST['id'];
		$setor=$_POST['setor'];
		$buss_date=date("y-m-d");
		$ket="Realisasi Tagihan $id $buss_date";
		$sql1= $conn->query("SELECT angsuran_pokok,margin FROM rek_loan where no_anggota ='$id' and STATUS =''"); 
		while($data1 = $sql1->fetch_array()){ 
		$pokok =$data1['angsuran_pokok'];
		$margin =$data1['margin'];
		$angsuran=number_format(compat_num($pokok + $margin));}
		
		$query = mysql_query("SELECT 1 FROM rek_loan WHERE no_anggota = '$id' and STATUS ='' ");
		$check = mysql_fetch_array($query);
		if ($check[0]) {
		$err[] = "<h5>* MAFF ada angsuran $id sebesar $angsuran yang belum di realasisasi </h5>";
		}
		$sql= $conn->query("SELECT loan.unit,CIF,ijaroh,pokok,anggota.CODE_KEL FROM loan 
		inner join anggota on anggota.no=loan.no_anggota where no_anggota ='$id'"); 
		while($data = $sql->fetch_array()){ 
		$unit =$data['unit'];
		$cif=$data['CIF'];
		$pokok=$data['pokok'];
		$ijaroh=$data['ijaroh'];
		$code_kel=$data['CODE_KEL'];
		$angsuran=$pokok + $ijaroh;
		}
		if ($setor < $angsuran){ $err[] = "<h5>* NOMINAL SETOR lebihkecil dari angsuran</h5>";	}
		
		empty( $setor ) 	 ? $err[] = "<h5>* NOMINAL SETOR Masih Kosong</h5>" : "";
		//empty( $tgl_kunjung) ? $err[] = "<h5>* TGL Kunjungan Masih Kosong</h5>" : "";
			


		// Cek apakah NOPEN belum terdaftar
		//$sql = $conn->query("SELECT NOPEN from feed_back WHERE NOPEN = '$nopen' AND id !='$id'");
		
		//if ( $sql->num_rows > 0 ) { $err[] = "<h5>* NOPEN telah terdaftar</h5>"; }

		if ( isset( $err ) ) {

			foreach ( $err as $val ) {
				echo $val;
			}

		} else {

							$conn->query("insert into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF)VALUES('$buss_date','$id','$unit','$cif','$code_kel','$setor','01','0','$userid','')");
							$conn->query("insert into rek_loan (ref,tgl_realisasi,unit,no_anggota,margin,saldo_kredit,angsuran_pokok,tipe,ket,USERID,STATUS)VALUES(' ','$buss_date','$unit','$id','$ijaroh','0','$pokok','L002','$ket','$userid',' ')");
												
							
			$_SESSION['info'] = "Dana sudah di Setor ";
			echo "Sukses";

		}

	break;

	case "hapus" :

		$id = $_GET['id'];
		echo "Maaf ada tidak bisa mengkases menu ini silahkan hubungi atasan anda";
		$_SESSION['info'] = "GAGAL";
		header("location:engine_update_stor.php");

	break;
}

ob_end_flush();

?>