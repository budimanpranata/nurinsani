<?php require_once __DIR__ . '/../php8_compat.php';
date_default_timezone_set('Asia/Jakarta');
session_start();
ob_start();
		
		$userid=$_SESSION['userid'];
		//cek apakah user sudah login
if(!isset($_SESSION['userid'])){
   header("location:../index.html");
	 exit; }
	
//cek level user
if($_SESSION['level']!="toko"){
   header("location:../index.html");
 exit; }
include "function.php";
include "conn2.php";
		$koneksi	=open_connection2();
		//$code_kel	= $_POST['code_kel'];
		$cao		= $_POST['code_ao'];
		$branch 	= $_POST['branch'];
		$cif 		= $_POST ['cif'];
		$nama 		= mysql_real_escape_string($_POST['nama']);
		$nama1 		= mysql_real_escape_string($_POST['wali']);
		$alamat 	= mysql_real_escape_string($_POST['alamat']);
		$rt 		= $_POST['rt'];
		$notlp 		= $_POST['no_tlp'];
		$home_tlp 	= $_POST['tlp_penjamin'];
		$tgl_lahir	= $_POST['tgl_lahir'];
		$tgl_wakalah= $_POST['tgl_skep'];
		$tgl_akad	= $_POST['tgl_akad'];
		$ibu 		= mysql_real_escape_string($_POST['ibu']);
		$buss_date	= date("Y-m-d h:m:s");
		$usaha		= $_POST['usaha'];
		$agama 		= $_POST['agama'];
		$negara 	= $_POST['negara'];
		$pendidikan = $_POST['pendidikan'];
		$npwp 		= $_POST['npwp'];
		$jenis_kel	= $_POST['jenis_kel'];
		$kode_pos 	= $_POST['kd_pos'];
		$lurah 		= mysql_real_escape_string($_POST['lurah']);
		$kec 		= mysql_real_escape_string($_POST['kec']);
		$rekomen 		= $_POST['num'];
		$kota 		= mysql_real_escape_string($_POST['kota']);
		$status_kawin=$_POST['status_kawin'];
		$id_expire	= $_POST['id_expire'];
		$ktp		=mysql_real_escape_string($_POST['id']);
		$pendapatan	= $_POST['sumber'];
		$kerja	= $_POST['pekerjaan'];
		$pendidikan	= $_POST['pendidikan'];
		$tempat_lahir=$_POST['tempat_lahir'];
		$norek		= $_POST['norek'];
		$no_anggota	= $_POST['no_anggota'];
		$nama_usaha = $_POST['nama_usaha'];
		$tenor=$_POST['tenor'];
		$pla=$_POST['pla'];
		$barang_satu = $_POST['barang_satu'];
		$harga_satu = $_POST['harga_satu'];
		$barang_dua = $_POST['barang_dua'];
		$harga_dua = $_POST['harga_dua'];
		$barang_tiga = $_POST['barang_tiga'];
		$harga_tiga = $_POST['harga_tiga'];
		$barang_empat = $_POST['barang_empat'];
		$harga_empat = $_POST['harga_empat'];
		$barang_lima = $_POST['barang_lima'];
		$harga_lima = $_POST['harga_lima'];
		$barang_enam = $_POST['barang_enam'];
		$harga_enam = $_POST['harga_enam'];
		$barang_tujuh = $_POST['barang_tujuh'];
		$harga_tujuh = $_POST['harga_tujuh'];
		$barang_delapan = $_POST['barang_delapan'];
		$harga_delapan = $_POST['harga_delapan'];
		$barang_sembilan = $_POST['barang_sembilan'];
		$harga_sembilan = $_POST['harga_sembilan'];
		$barang_sepuluh = $_POST['barang_sepuluh'];
		$harga_sepuluh = $_POST['harga_sepuluh'];
		$deal_type=$_POST['deal_type'];
		$tgl_wakal	= strtotime($tgl_wakalah);
		$murab1 	= date("Y-m-d", strtotime("+7 day", $tgl_wakal));
		$Newmurab = date ("Y-m-d", strtotime("+7 day" , strtotime ($tgl_wakalah))) ;
		$tgl_m	= strtotime($murab1);
		$maturity_date =  date("Y-m-d", strtotime("+$tenor WEEK", $tgl_m));
		
				
		$_SESSION['tgl_wakalah']=$tgl_wakalah;
		$_SESSION['stgl_akad']=$tgl_akad;
		$param_libur = mysql_query ("select tgl_libur AS param FROM param_libur where tgl_libur ='$murab1'");
						$l = mysql_fetch_array($param_libur);
						$param=$l['param'];

/* Ini untuk mengecek Bila Tanggal Libur */
	if ($murab1 = $param){
			$murab =  date('Y-m-d', strtotime('+8 day', $tgl_wakalah));
	}else {
			$murab =  date('Y-m-d', strtotime('+7 day', $tgl_wakalah));
	}
	
	
	$rubah = date_format(date_create($tgl_lahir), 'Y');
	$thn_skrg = date('Y');
	$usia = $thn_skrg - $rubah;
/* Ini untuk mengecek APabila Usia Memebihi 55 */
	if ($usia >55){
		$status_usia='YA';
		echo "<script> alert ('Usai Lebih Besar 55 Proses selanjutnya menunggu approval Lead Admin ')</script>";
		}else {
				$status_usia='NO';
		}
		
		
		
	
	$param_biaya = mysql_query ("select margin,tab,pla,jw  FROM param_biaya where jw ='$tenor' and pla='$pla'");
						$pbiaya = mysql_fetch_array($param_biaya);
						$param_margin=$pbiaya['margin'];
						$param_tabung=$pbiaya['tab'];
						$param_tenor=$pbiaya['jw'];
						$param_pla=$pbiaya['pla'];
						if ($param_tenor==$tenor and $pla==$param_pla)
						{
						$pokok=$pla/$tenor;
						$margin=$pla * ($param_margin/100);
						$ijaroh=$margin/$tenor;
						$angsuran=$pla/$tenor;
						$os =$pla;	

				} else {
							
						echo "<script> alert ('Parameter Pembiayaan tidak ditemukan')</script>";		

						echo "<script language='javascript'>document.location.href='new2.php'</script>";									
	
				}
			
	
		$today		=date("Ymd");
$count = mysql_query ("select max(no) AS last FROM member where  no LIKE '$today%'and unit='$branch'");
		$hit = mysql_fetch_array($count);
				$lastNoTransaksi = $hit['last'];
				$lastNoUrut = substr($lastNoTransaksi, 12, 4);
				$nextNoUrut = (int) $lastNoUrut + 1;
				$nextNoTransaksi = $today.sprintf('%04s', $nextNoUrut);
				$no="$branch$nextNoTransaksi";

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

	
	
		$row_sal= mysql_fetch_array(mysql_query("SELECT count(tgl_libur) as libur from param_libur where tgl_libur BETWEEN '$Newmurab' and '$maturity_date' and Hari ='$dayList[$day]'")) or die(mysql_error());
				$libur=$row_sal['libur'];
				
				if ($libur > 0){
					$new_tenor = $tenor + $libur;
				}else{
					$new_tenor=$tenor;
				}
				$maturity_date =  date("Y-m-d", strtotime("+$new_tenor WEEK", $tgl_m));
	
		
$query = mysql_query("SELECT 1 FROM member WHERE ID_NUMBER= '1212' or  ID_NUMBER= '1212' AND MOTHER_NAME='1212' or ID_NUMBER= '1212' AND BIRTH_DATE='q1212'");
		 $check = mysql_fetch_array($query);
		if ($check[0]) {

			// jika data ditemukan
		echo "<script> alert ('data $ktp and $nama sudah ada dalam System ')</script>";

		echo "<script language='javascript'>document.location.href='new2.php'</script>";
				}

			else {
			
				$query = mysql_query("SELECT 1 FROM member WHERE no = '$cif' ");
				$check = mysql_fetch_array($query);
				if ($check[0]) {

		// jika data ditemukan
				echo "<script> alert ('data sudah ada dalam database, isi form dengan No anggota yang berbeda ')</script>";
				echo "<script language='javascript'>document.location.href='new2.php'</script>";
				}

				else {
					
				if($dayList[$day]=='Senin'){
		$code_kel="sn-$cao";
	}elseif ($dayList[$day]=='Selasa'){
		$code_kel="sl-$cao";
	}elseif ($dayList[$day]=='Rabu'){
	$code_kel="rb-$code_ao";
	}elseif ($dayList[$day]=='Kamis'){
		$code_kel="km-$cao";
	}elseif ($dayList[$day]=='Jumat'){
		$code_kel="jm-$cao";
	}
								
				$calon_loan= "insert into calon_musa(buss_date,code_kel,no_anggota,id_member,Cust_Short_name,Deal_type,suffix,bagi_hasil,Tenor,Plafond,os,saldo_margin,angsuran,pokok,ijaroh,bulat,ke,usaha,nama_usaha,unit,tgl_wakalah,tgl_akad,tgl_murab,next_schedule,maturity_date,hari,cao,USERID,status,status_usia,status_app,gol,code_musa)
				values('$buss_date','$code_kel','$no_anggota','$cif','$nama','$deal_type','1','$margin','$tenor','$pla','0','$margin','$angsuran','$pokok','$ijaroh','$uang','0','$usaha','$nama_usaha','$branch','$tgl_wakalah','$tgl_akad','$Newmurab','$next_schedule','$maturity_date',
'$dayList[$day]','$cao','$userid','ANGGOTA','$status_usia','PENDING','1','$cif')";
				$input_loan = mysql_query($calon_loan);

					
										
					$calon_anggota= "replace into member values
					('$no','$code_kel','$no_anggota','$buss_date','$cif','$nama','$branch','$deal_type','$alamat','$lurah','$kec','$kode_pos','$kota','$rt','$notlp','$home_tlp','$jenis_kel','$tgl_lahir','$ktp','$negara','$status_kawin','$agama','$ibu','$npwp','$usaha','$pendidikan','$tempat_lahir','$id_expire','$nama1','$cao','$userid','Active','$kerja','$dayList[$day]')";
					$i = mysql_query($calon_anggota);

					$qbarang="insert into barang(id_barang,unit,cif,code_kel,barang_satu,harga_satu,barang_dua,harga_dua,barang_tiga,harga_tiga,barang_empat,harga_empat,barang_lima,harga_lima,barang_enam,harga_enam,barang_tujuh,harga_tujuh,barang_delapan,harga_delapan,barang_sembilan,harga_sembilan,barang_sepuluh,harga_sepuluh,userid) 
					VALUES ('$no_anggota','$branch','$cif','$code_kel','$barang_satu','$harga_satu','$barang_dua','$harga_dua','$barang_tiga','$harga_tiga','$barang_empat','$harga_empat','$barang_lima','$harga_lima','$barang_enam','$harga_enam','$barang_tujuh','$harga_tujuh','$barang_delapan','$harga_delapan','$barang_sembilan','$harga_sembilan','$barang_sepuluh','$harga_sepuluh','$userid')";
					$b = mysql_query($qbarang);
					
					$rek="insert into rekomendasi (norek,cif,nominal)values('$no_anggota','$cif','$rekomen')";
					$rekomendasi = mysql_query($rek);
					
					}
					if(($i)||($input_loan)||($rekomendasi)){ 
	
							echo "<script> alert ('data Anggota $nama sudah ditambahkan di kelompok $code_kel ')</script>";

						echo "<script language='javascript'>document.location.href='new2.php'</script>";

				} else {
					echo "gagal input <a href=\"javascript:history.back()\">kembali</a>";
			} 
	
		}
	
?>
