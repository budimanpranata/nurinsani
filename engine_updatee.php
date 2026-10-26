<?php require_once __DIR__ . '/php8_compat.php';
session_start();
ob_start();
$userid=$_SESSION['userid'];

include "config.php";
include "function.php";
	
switch ( $_GET['p'] ) {

	default: echo "Halaman tidak ditemukan"; break;

	case "tambah":

		
	break;

	case "update":
		$id = $_POST['id'];
		$tgl_wakalah = $_POST['inn'];
		$pla= $_POST['pla'];
		$cif=$_POST['cif'];
		$akad=$_POST['akad'];
		$tenor=$_POST['tenor'];
		$tgl_wakal=strtotime($tgl_wakalah);
		$murab1 =  date("Y-m-d", strtotime("+7 day", $tgl_wakal));
		$Newmurab = date ("Y-m-d", strtotime("+7 day" , strtotime ($tgl_wakal))) ;
		$tgl_m	= strtotime($murab1);
		$maturity_date1 =  date("Y-m-d", strtotime("+$tenor WEEK", $tgl_m));
		
		$tanggal = $tgl_wakalah;
    $day = date('D', strtotime($tanggal));
    $dayList = array(
    	'Sun' => 'Minggu',
    	'Mon' => 'Senin',
    	'Tue' => 'Selasa',
    	'Wed' => 'Rabu',
    	'Thu' => 'Kamis',
    	'Fri' => 'Jumat',
    	'Sat' => 'Sabtu'
    );
		
		
		
		
		$sql = $conn->query("select unit from loan WHERE cif = '$cif'");
		while ( $r = $sql->fetch_assoc() ) {
		$unit=$r['unit'];
		}
		
		
		$sql1 = $conn->query("select margin,tab,pla,jw FROM param_biaya where jw ='$tenor' and pla='$pla'");
		while ( $pbiaya = $sql1->fetch_assoc() ) {				
				$param_margin=$pbiaya['margin'];
				$param_tabung=$pbiaya['tab'];
				$param_tenor=$pbiaya['jw'];
				$param_pla=$pbiaya['pla'];
				}
				
				if ($param_tenor==$tenor and $pla==$param_pla)
					{
						
					$pokok=$pla/$tenor;
					$margin=$pla * ($param_margin/100);
					$ijaroh=$margin/$tenor;
					$angsuran=$ijaroh+$pokok;
					$uang =$param_tabung+$angsuran;
					$os =$pla + $margin;	

				} else {$err[] = "<h5>* parameter pembiyaan tidak ditemukan</h5>";}
		




		empty( $pla ) 	 ? $err[] = "<h5>* PLAFOND YANG DISETUJUI Masih Kosong</h5>" : "";
		//empty( $tgl_kunjung) ? $err[] = "<h5>* TGL Kunjungan Masih Kosong</h5>" : "";
		empty( $tgl_wakalah ) ? $err[] = "<h5>* TANGGAL WAKALAH  masih kosong</h5>" : "";
		empty( $akad ) ? $err[] = "<h5>* TANGGAL AKAD  masih kosong</h5>" : "";


		// Cek apakah NOPEN belum terdaftar
		//$sql = $conn->query("SELECT NOPEN from feed_back WHERE NOPEN = '$nopen' AND id !='$id'");
		
		//if ( $sql->num_rows > 0 ) { $err[] = "<h5>* NOPEN telah terdaftar</h5>"; }

		if ( isset( $err ) ) {

			foreach ( $err as $val ) {
				echo $val;
			}

		} else {
					$_SESSION['sakadd']=$akad;
					$_SESSION['swakall']=$tgl_wakalah;
					
			
					
					$ang=6;
					$acak=acakangkahuruf($ang);
					$kode_transaksi="BU/".$acak;
					$buss_date=date("ymd");
					
					$conn->query("replace into calon_loan select * from loan WHERE cif = '$cif'");
					$conn->query("UPDATE calon_loan set Plafond = '$pla',tgl_wakalah='$tgl_wakalah',maturity_date='$maturity_date1',tgl_murab='$Newmurab' ,status='CALON_ANGGOTA',saldo_margin='$margin',angsuran='$angsuran',pokok='$pokok',ijaroh='$ijaroh',ke ='1',suffix=suffix +1,os='$os',hari='$dayList[$day]', tgl_akad='$akad',bulat='$uang', status_app='PENDING',run_tenor='0' WHERE cif = '$cif'");
					$conn->query("UPDATE calon_loan set status='CALON_ANGGOTA' WHERE cif = '$cif'");
					$sqll = $conn->query("select suffix,Cust_Short_name,tgl_murab,maturity_date  from calon_loan WHERE cif = '$cif'");
					while ( $d = $sqll->fetch_assoc() ) {
						$suffix		=$d['suffix'];
						$nama		=mysql_real_escape_strig($d['Cust_Short_name ']);
						$murab		=$d['tgl_murab'];
						$maturity_date=$d['maturity_date'];
						$next_schedule=$d['next_schedule']; 	
					}

					$nol='0';
					$anggota="$unit$cif$nol$suffix";
					$keterangan_transaksi="Realisasi Wakalah An $nama";
					$conn->query("UPDATE calon_loan set no_anggota= '$anggota' where cif='$cif'");
					$conn->query("UPDATE anggota set status='CALON_ANGGOTA' WHERE cif = '$cif'");
					
					//$conn->query("insert into jurnal_umum(nomor_jurnal,kode_transaksi,tanggal_selesai) values('$nomor_jurnal','$kode_transaksi','$buss_date')");
					//$conn->query("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
					//values('$unit','$kode_transaksi','1431000','$buss_date','Bukti Umum','$keterangan_transaksi','$pla','$userid')");

					//$conn->query ("insert DELAYED into tabungan_detail (no_anggota,cif,saldo,unit,account_open) 
						//VALUES ('$anggota','$cif','$saldo','$unit','$buss_date')");
				
					//$conn->query("insert into jurnal_umum(nomor_jurnal,kode_transaksi,tanggal_selesai) values('$nomor','$kode_transaksi','$buss_date')");

					//$conn->query("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
					//values('$unit','$kode_transaksi','1120000','$buss_date','Bukti Umum','$keterangan_transaksi','$pla','$userid')");
					
					$awal_hari = $murab;
					$akhir_hari = $maturity_date;
					
					$x=0;
					while (strtotime($awal_hari) < strtotime($akhir_hari)) {
					$x ++;
					$awal_hari= date ("Y-m-d", strtotime("+7 day", strtotime($awal_hari)));

					$conn->query("INSERT DELAYED INTO pinjaman_detail_c (id,id_pinjam,cicilan,angsuran_pokok,bunga,tgl_jatuh_tempo,tgl_bayar,jumlah_bayar,ket) values('','$anggota','$x','$pokok','$ijaroh','$awal_hari','$next_schedule','0','$next_schedule')");
					
					}
					$sql_tgl=$conn->query ("SELECT COUNT(tgl_libur) as libur from param_libur 
					INNER join pinjaman_detail_c on pinjaman_detail_c.tgl_jatuh_tempo = param_libur.tgl_libur");
					while ( $c = $sql_tgl->fetch_assoc() ) {
					$libur =$c['libur'];
					}
					$conn->query("delete from pinjaman_detail_c where id_pinjam='$anggota'");
					//@mysql_query ("delete from pinjaman_detail where id_pinjam='$no_anggota'");
		 
		 
					$akhir_hari = $maturity_date;
					$newdate2 = date ("Y-m-d", strtotime("+$libur WEEK" , strtotime ($akhir_hari))) ;
				
				//$awal_hari = $murab;
				$new_akhir_hari = $newdate2;
						
				$conn->query("UPDATE calon_loan set maturity_date='$new_akhir_hari' WHERE cif = '$cif'");
				//$conn->query("delete from loan  WHERE no = '$id'");
			$_SESSION['info'] = "Mengubah";
			echo "Sukses";

		}

	break;

	case "hapus" :

		$id = $_GET['id'];
		echo "Maaf ada tidak bisa mengkases menu ini silahkan hubungi atasan anda";
		$_SESSION['info'] = "GAGAL";
		header("location:feedback_edit.php");

	break;
}

ob_end_flush();

?>
