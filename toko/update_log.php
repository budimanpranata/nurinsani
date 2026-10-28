<?php require_once __DIR__ . '/../php8_compat.php';
date_default_timezone_set('Asia/Jakarta');
session_start();
ob_start();
		$kel=$_SESSION['code_kel'];
		$userid=$_SESSION['userid'];
		//cek apakah user sudah login
if(!isset($_SESSION['userid'])){
   header("location:index.html");
	 exit; }
	
//cek level user
if($_SESSION['level']!="unit"){
   header("location:index.html");
 exit; }
include "function.php";
include "conn2.php";
		$koneksi	=open_connection2();
		$code_kel	= $_POST['code_kel'];
		$cao		= $_POST['code_ao'];
		$branch 	= $_POST['branch'];
		$cif 		= $_POST ['cif'];
		$nama 		= mysql_real_escape_string($_POST['nama']);
		$nama1 		= mysql_real_escape_string($_POST['wali']);
		$alamat 	= $_POST['alamat'];
		$rt 		= $_POST['rt'];
		$notlp 		= $_POST['no_tlp'];
		$home_tlp 	= $_POST['tlp_penjamin'];
		$tgl_lahir	= $_POST['tgl_lahir'];
		$tgl_wa= $_POST['tgl_skep'];
		$tgl_akad	= $_POST['tgl_akad'];
		$ibu 		= $_POST['ibu'];
		$buss_date	= date("Y-m-d H:m:s");
		$usaha		= $_POST['usaha'];
		$agama 		= $_POST['agama'];
		$negara 	= $_POST['negara'];
		$pendidikan = $_POST['pendidikan'];
		$kerja = $_POST['pekerjaan'];
		$npwp 		= $_POST['npwp'];
		$jenis_kel	= $_POST['jenis_kel'];
		$kode_pos 	= $_POST['kd_pos'];
		$lurah 		= $_POST['lurah'];
		$kec 		= $_POST['kec'];
		$rekomen 	= $_POST['num'];
		$kota 		= $_POST['kota'];
		$status_kawin=$_POST['status_kawin'];
		$id_expire	= $_POST['id_expire'];
		$ktp		=$_POST['id'];
		$pendapatan	= $_POST['sumber'];
		$pendidikan	= $_POST['pendidikan'];
		$tempat_lahir=$_POST['tempat_lahir'];
		$norek		= $_POST['norek'];
		$no_anggota	= $_POST['no_anggota'];
		$nama_usaha = $_POST['nama_usaha'];
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
		$suffix= $_POST['suffix'];
		
		$tenor=$_POST['tenor'];
		$pla=$_POST['pla'];
		$tgl_wakal	= strtotime($tgl_wa);
		$murab1 	= date("Y-m-d", strtotime("+7 day", $tgl_wakal));
		$Newmurab = date ("Y-m-d", strtotime("+7 day" , strtotime ($tgl_wa))) ;
		$tgl_m	= strtotime($murab1);
		$maturity_date =  date("Y-m-d", strtotime("+$tenor WEEK", $tgl_m));
		//$suffix=substr($no_anggota, -1);
		$param_libur = mysql_query ("select tgl_libur AS param FROM param_libur where tgl_libur ='$murab1'");
						$l = mysql_fetch_array($param_libur);
						$param=$l['param'];
$param_app = mysql_query ("select suffix,status_app FROM calon_loan where cif ='$cif'");
						$app = mysql_fetch_array($param_app);
						$status_app=$app['status_app'];
		
		 $tanggal = $tgl_wa;
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

/* Ini untuk mengecek Bila Tanggal Libur */
	if ($Newmurab = $param){
			$murab =  date("Y-m-d", strtotime("+2 week", $tgl_wakal));
	}else {
			$murab =  date("Y-m-d", strtotime("+7 day", $tgl_wakal));
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
						$angsuran=$ijaroh+$pokok;
						$uang =$param_tabung+$angsuran;
						$os =$pla + $margin;	

				} else {
							
						echo "<script> alert ('Parameter Pembiayaan tidak ditemukan')</script>";		

						echo "<script language='javascript'>document.location.href='new2.php'</script>";									
	
				}

		$today		=date("Ymd");
$count = mysql_query ("select max(no) AS last FROM member where  no LIKE '%$today%'and unit='$branch'");
		$hit = mysql_fetch_array($count);
				$lastNoTransaksi = $hit['last'];
				$lastNoUrut = substr($lastNoTransaksi, 12, 4);
				$nextNoUrut = (int) $lastNoUrut + 1;
				$nextNoTransaksi = $today.sprintf('%04s', $nextNoUrut);
				$no="$branch$nextNoTransaksi";

						
					
				$awal_hari = $murab1;
				$akhir_hari = $maturity_date;
				$x=0;
				while (strtotime($awal_hari) < strtotime($akhir_hari)) {
				$x ++;
				$awal_hari= date ("Y-m-d", strtotime("+7 DAY", strtotime($awal_hari)));
				$detail= mysql_query("INSERT INTO pinjaman_detail_c (id,id_pinjam,cicilan,angsuran_pokok,bunga,tgl_jatuh_tempo,tgl_bayar,jumlah_bayar,ket) 
				values('$d','$no_anggota','$x','$pokok','$ijaroh','$awal_hari','$awal','0','')") or die(mysql_error());
				}

					$cek_tanggal= mysql_query ("SELECT COUNT(tgl_libur) as libur from param_libur 
					INNER join pinjaman_detail_c on pinjaman_detail_c.tgl_jatuh_tempo = param_libur.tgl_libur");
					while($data_libur = mysql_fetch_array($cek_tanggal)){
					$libur =$data_libur['libur'];
					}
					@mysql_query ("delete from pinjaman_detail_c where id_pinjam='$no_anggota'");
					//@mysql_query ("delete from pinjaman_detail where id_pinjam='$no_anggota'");
		 
		 
					$akhir_hari = $maturity_date;
					$newdate2 = date ("Y-m-d", strtotime("+$libur WEEK" , strtotime ($akhir_hari))) ;
				
				//$awal_hari = $murab;
				$new_akhir_hari = $newdate2;
				//$xx=0;
				//while (strtotime($awal_hari) < strtotime($new_akhir_hari)) {
				//$xx ++;
				//$awal_hari= date ("Y-m-d", strtotime("+7 DAY", strtotime($awal_hari)));
				//$detail_data= mysql_query("INSERT INTO pinjaman_detail (id,id_pinjam,cicilan,angsuran_pokok,bunga,tgl_jatuh_tempo,tgl_bayar,jumlah_bayar,ket) 
				//values('$d','$no_anggota','$xx','$pokok','$ijaroh','$awal_hari','$awal','0','')") or die(mysql_error());
				
					$calon_loan= "REPLACE into calon_musa(buss_date,code_kel,no_anggota,id_member,Cust_Short_name,Deal_type,suffix,bagi_hasil,Tenor,Plafond,os,saldo_margin,angsuran,pokok,ijaroh,bulat,ke,usaha,nama_usaha,unit,tgl_wakalah,tgl_akad,tgl_murab,next_schedule,maturity_date,hari,cao,USERID,status,status_usia,status_app)
					values('$buss_date','$code_kel','$no_anggota','$cif','$nama','$deal_type','$suffix','$margin','$tenor','$pla','$os','$margin','$angsuran','$pokok','$ijaroh','$uang','0','$usaha','$nama_usaha','$branch','$tgl_wa','$tgl_akad','$murab','$next_schedule','$new_akhir_hari','$dayList[$day]','$cao','$userid','ANGGOTA','$status_usia','$status_app')";
					$input_loan = mysql_query($calon_loan);	

					//}
				//@mysql_query ("delete from pinjaman_detail where tgl_jatuh_tempo in (select tgl_libur from param_libur where tgl_libur=pinjaman_detail.tgl_jatuh_tempo ) ");
				//@mysql_query("SET @count = 0;");
				//@mysql_query ("UPDATE pinjaman_detail SET cicilan=@count:=@count+1 where id_pinjam='$no_anggota' ORDER BY cicilan");
										
					$calon_anggota= "replace into member
					(no,CODE_KEL,NOREK,BUSS_DATE,id_cust,CUST_SHORT_NAME,UNIT,DEAL_TYPE,HADDRESS1,HADDRESS2,HADDRESS3,HPOST_CODE,HADDRESS4,HADDRESS5,MOBILE_PHONE,HOME_PHONE,GENDER,BIRTH_DATE,ID_NUMBER,CITIZENSHIP,MARITAL_STATUS,RELIGION,MOTHER_NAME,NPWP,SOURCE_INCOME,EDUCATION,TEMPAT_LAHIR,ID_EXPIRED,WARIS,CAO,USERID,status,pekerjaan_pasangan)
					values
					('$no','$code_kel','$no_anggota','$buss_date','$cif','$nama','$branch','$deal_type','$alamat','$lurah','$kec','$kode_pos','$kota','$rt','$notlp','$home_tlp','$jenis_kel','$tgl_lahir','$ktp','$negara','$status_kawin','$agama','$ibu','$npwp','$usaha','$pendidikan','$tempat_lahir','$id_expire','$nama1','$cao','$userid','ANGGOTA','$kerja')";
					$i = mysql_query($calon_anggota);

										
					$rek="replace into rekomendasi (norek,cif,nominal)values('$no_anggota','$cif','$rekomen')";
					$rekomendasi = mysql_query($rek);
										
								if(($i)||($input_loan)||($b)||($r)||($rekomendasi)){ 
	
							echo "<script> alert ('data Anggota $nama sudah di koreksi pada kelompok $code_kel ')</script>";

						echo "<script language='javascript'>document.location.href='dim.php'</script>";

				} else {
					echo "gagal input <a href=\"javascript:history.back()\">kembali</a>";
			} 
	
		
	
?>
