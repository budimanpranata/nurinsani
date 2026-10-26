<?php require_once __DIR__ . '/php8_compat.php';
session_start();
ob_start();
$userid=$_SESSION['userid'];
date_default_timezone_set('Asia/Jakarta');

include "config.php";
include "function.php";
	
switch ( $_GET['p'] ) {

	default: echo "Halaman tidak ditemukan"; break;

	case "tambah":

		
	break;

	case "update":
		$id = $_POST['id'];
		$tgl_wakalah = $_POST['inn'];
		$pla=$_POST['pla'];
		$cif=$_POST['cif'];
		$akad=$_POST['akad'];
		$tenor=$_POST['tenor'];
		$jenis_akad=$_POST['jenisAkad'];
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

		$sql = $conn->query("select nominal from omzet WHERE cif = '$cif'");
		while ( $r = $sql->fetch_assoc() ) {
		$omzet=$r['nominal']; 
		}
		if ($jenis_akad=="MUSYAROKAH"){
			$omzet =$omzet;
			if($omzet==0){
				{$err[] = "<h5>* Omzet tidakdi Temukan</h5>";}

			}else{
				$omzet * (50/100);
			}
		}else{
			$omzet =$omzet;

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
					$os =$pla + $margin + (2500 * $tenor);
					$margin=$pla * ($param_margin/100) + (2500 * $tenor);	

				} else {$err[] = "<h5>* parameter pembiyaan tidak ditemukan</h5>";}


$sql2 = $conn->query("select status_approve FROM branch where kode_branch ='$unit' and status_approve='YA'");
		while ( $pbiaya2 = $sql2->fetch_assoc() ) {				
				$param_status=$pbiaya2['status_approve'];
				
				}
				
				if ($param_status=='YA')
					{
						
					
					$gol ='0';	

				} else {
				$gol ='0';
				}
		


		
		empty( $pla ) 	 ? $err[] = "<h5>* PLAFOND YANG DISETUJUI Masih Kosong</h5>" : "";
		//empty( $tgl_kunjung) ? $err[] = "<h5>* TGL Kunjungan Masih Kosong</h5>" : "";
		empty( $tgl_wakalah ) ? $err[] = "<h5>* TANGGAL WAKALAH  masih kosong</h5>" : "";
		empty( $akad ) ? $err[] = "<h5>* TANGGAL AKAD  masih kosong</h5>" : "";



		if ( isset( $err ) ) {

			foreach ( $err as $val ) {
				echo $val;
			}

		} else {
					$_SESSION['sakadd']=$akad;
					$_SESSION['swakall']=$tgl_wakalah;

					//var_dump($jenis_akad);

					
					if ($jenis_akad=='MURAB'){
			
					
					$ang=6;
					$acak=acakangkahuruf($ang);
					$kode_transaksi="BU/".$acak;
					$buss_date=date("ymd");
					
					$conn->query("replace into calon_loan select * from loan WHERE cif = '$cif'");
					$conn->query("UPDATE calon_loan set Plafond = 
'$pla',tgl_wakalah='$tgl_wakalah',maturity_date='$maturity_date1',tgl_murab='$Newmurab' 
,status='CALON_ANGGOTA',saldo_margin='$margin',angsuran='$angsuran',pokok='$pokok',ijaroh='$ijaroh',ke ='1',suffix=suffix+1,deal_produk='2',os=$os,hari='$hari', tgl_akad='$akad',bulat='$uang',hari='$dayList[$day]', tenor ='$tenor',  status_app='PENDING',run_tenor='0',gol ='$gol' WHERE cif = '$cif'");
					$conn->query("UPDATE calon_loan set status='CALON_ANGGOTA' WHERE cif = '$cif'");
					$sqll = $conn->query("select suffix,Cust_Short_name,tgl_murab,maturity_date  from calon_loan WHERE cif = '$cif'");
					while ( $d = $sqll->fetch_assoc() ) {
						$suffix		=$d['suffix'];
						$nama		=$d['Cust_Short_name '];
						$murab		=$d['tgl_murab'];
						$maturity_date=$d['maturity_date'];
						$next_schedule=$d['next_schedule']; 	
					}

					$nol='0';
					$anggota="$unit$cif$nol$suffix";
					$keterangan_transaksi="Realisasi Wakalah An $nama";
					$conn->query("UPDATE calon_loan set no_anggota= '$anggota' where cif='$cif'");
					$conn->query("UPDATE anggota set status='CALON_ANGGOTA' WHERE cif = '$cif'");
					
									
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
			$_SESSION['info'] = "Proses Pengajuan Berhasil";
			echo "Sukses";
				}else{
					$_SESSION['sakadd']=$akad;
					$_SESSION['swakall']=$tgl_wakalah;
					$angsuran=$pla/$tenor;
					$pokok=$pla/$tenor;
					
					
					$conn->query("UPDATE anggota set status='CALON_ANGGOTA' WHERE cif = '$cif'");
					$conn->query("replace into calon_loan select * from loan WHERE cif = '$cif'");
					

						$sqll = $conn->query("select min(nominal) as omzet from omzet WHERE cif = '$cif' and nominal > 0");
						while ( $d = $sqll->fetch_assoc() ) {
							$omzet = $d['omzet'];
							//$omzet =1;						
						}	
						//$omzet=250000;	
						//var_dump($data);				
							if($omzet==0 || $omzet ==null || $omzet ==''){
								$err[] = "<h5>* Belum memiliki omzet</h5>";

							}else{

								$omzet = $omzet/2;
							}
							//var_dump($omzet);
						
						
						
						
						
						//var_dump($nisbah);
						$ijaroh = (($param_margin/100)*$pla)/$tenor;
						//var_dump($ijaroh);
						
						$angsuran_pokok = $pla/$tenor;
						$angsuran = $angsuran_pokok + $ijaroh + 2500;
						$nisbah = $ijaroh/$omzet;

						
					$conn->query("UPDATE calon_loan set Plafond = 
					'$pla',tgl_wakalah='$akad',maturity_date='$maturity_date1',tgl_murab='$Newmurab' 
					,status='CALON_ANGGOTA',saldo_margin='0',angsuran='$angsuran',pokok='$angsuran_pokok',ijaroh='$ijaroh',ke ='1',suffix=suffix+1, deal_produk='3', gol='0', os=$pla,hari='$hari', tgl_akad='$akad',tgl_murab ='$akad',bulat='$uang',hari='$dayList[$day]', tenor ='$tenor',status_app='MUSYAROKAH',run_tenor='0' WHERE cif = '$cif'");
					
						$conn->query("UPDATE calon_loan set status='CALON_ANGGOTA', status_app ='MUSYAROKAH' WHERE cif = '$cif'");
						$conn->query("UPDATE nisbah set nisbah = '$nisbah', omzet='$omzet' WHERE cif = '$cif'");

						
						$_SESSION['info'] = "Musarokah sukses di input";
						echo "Sukses";


					
				}
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
