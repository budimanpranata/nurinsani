<?php require_once __DIR__ . '/koneksi_db.php';
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
		$code_kel	= trim($_POST['code_kel']);
		$cao		= trim($_POST['code_ao']);
		$branch 	= $_POST['branch'];
		$cif 		= $_POST ['cif'];
		$nama 		= $_POST['nama'];
		$nama1 		= $_POST['wali'];
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
		$deal_type=$_POST['sumber'];
		$tenor=$_POST['tenor'];
		$pla=$_POST['pla'];
		$tgl_wakal	= strtotime($tgl_wa);
		$murab1 	= date("Y-m-d", strtotime("+7 day", $tgl_wakal));
		$Newmurab = date ("Y-m-d", strtotime("+7 day" , strtotime ($tgl_wa))) ;
		$tgl_m	= strtotime($murab1);
		$maturity_date =  date("Y-m-d", strtotime("+$tenor WEEK", $tgl_m));
		//$suffix=substr($no_anggota, -1);
		$param_libur = db_semua("select tgl_libur AS param FROM param_libur where tgl_libur =?", 's', (string) $murab1);
						$l = $param_libur ? $param_libur[0] : false;
						$param=$l['param'];
$param_app = db_semua("select suffix,status_app FROM calon_loan where cif =?", 's', (string) $cif);
						$app = $param_app ? $param_app[0] : false;
						$status_app1=$app['status_app'];

$param_app1 = db_semua("select status_approve  FROM branch where kode_branch =? and status_approve='YA'", 's', (string) $branch);
                        $app = $param_app1 ? $param_app1[0] : false;
                       
                        $param_apprve=$app['status_approve'];
                        if ($param_apprve=='YA')
                        {
                        $gol='0'; 
$status_app=$status_app1;   

                } else {
 $gol='0';
                           
                       $status_app=$status_app1;    
                }
		
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
	
	
	$tgl_lahir_dt = date_create($tgl_lahir);
	$rubah = $tgl_lahir_dt ? date_format($tgl_lahir_dt, 'Y') : false; // PHP 5: tanggal tidak valid -> false (PHP 8: TypeError)
	$thn_skrg = date('Y');
	$usia = $thn_skrg - $rubah;
/* Ini untuk mengecek APabila Usia Memebihi 55 */
	if ($usia >55){
		$status_usia='YA';
		echo "<script> alert ('Usai Lebih Besar 55 Proses selanjutnya menunggu approval Lead Admin ')</script>";
		}else {
				$status_usia='NO';
		}
		$param_biaya = db_semua("select margin,tab,pla,jw  FROM param_biaya where jw =? and pla=?", 'ss', (string) $tenor, (string) $pla);
						$pbiaya = $param_biaya ? $param_biaya[0] : false;
						$param_margin=$pbiaya['margin'];
						$param_tabung=$pbiaya['tab'];
						$param_tenor=$pbiaya['jw'];
						$param_pla=$pbiaya['pla'];
						if ($param_tenor==$tenor and $pla==$param_pla)
						{
						$pokok=compat_bagi($pla, $tenor); // PHP 5: pembagi 0/kosong -> false, bukan error
						$margin=$pla * ($param_margin/100);
						$ijaroh=compat_bagi($margin, $tenor); // PHP 5: pembagi 0/kosong -> false, bukan error
						$angsuran=$ijaroh+$pokok;
						$uang =$param_tabung+$angsuran;
						$os =$pla + $margin + (2500 * $tenor);	
$margin=$pla * ($param_margin/100) + (2500 * $tenor);

$param_omzet = db_semua("select min(nominal) as omzet from omzet WHERE cif = ? and nominal > 0", 's', (string) $cif);
$pomzet = $param_omzet ? $param_omzet[0] : false;
$omzet=$pomzet['omzet'];

if($suffix=='1'){
$omzet = $omzet/2;
}else{
$omzet = $omzet;
}

$param_akad = db_semua("select jenis_akad  FROM jenis_akad where unit=?", 's', (string) $branch);
$jakad = $param_akad ? $param_akad[0] : false;
$jenis_akad=$jakad['jenis_akad'];
if($jenis_akad =='MURAB'){
$ijaroh =$ijaroh;
$angsuran = $angsuran;
$pokok = $pokok;
$margin =$margin;
$os = $os;
}else{
$ijaroh = (($param_margin/100)*$pla)/$tenor;
$pokok = compat_bagi($pla, $tenor); // PHP 5: pembagi 0/kosong -> false, bukan error
$angsuran = $pokok + $ijaroh + 2500;
$nisbah = compat_bagi($ijaroh, $omzet); // PHP 5: pembagi 0/kosong -> false, bukan error
$os = $pla;
$margin =0;
$nisbah = compat_bagi($ijaroh, $omzet); // PHP 5: pembagi 0/kosong -> false, bukan error
db_jalankan("replace into nisbah (cif,nisbah,omzet)values(?,?,?)", 'sss', (string) $cif, (string) $nisbah, (string) $omzet);
}


				} else {
							
						echo "<script> alert ('Parameter Pembiayaan tidak ditemukan')</script>";		

						echo "<script language='javascript'>document.location.href='new2.php'</script>";									
	
				}





		$today		=date("Ymd");
$count = db_semua("select max(no) AS last FROM anggota where  no LIKE ? and unit=?", 'ss', "%$today%", (string) $branch);
		$hit = $count ? $count[0] : false;
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
				$detail= db_jalankan("INSERT INTO pinjaman_detail_c (id,id_pinjam,cicilan,angsuran_pokok,bunga,tgl_jatuh_tempo,tgl_bayar,jumlah_bayar,ket) 
				values(?,?,?,?,?,?,?,'0','')", 'sssssss', (string) $d, (string) $no_anggota, (string) $x, (string) $pokok, (string) $ijaroh, (string) $awal_hari, (string) $awal) or die(mysql_error());
				}

					$cek_tanggal = db_semua("SELECT COUNT(tgl_libur) as libur from param_libur 
					INNER join pinjaman_detail_c on pinjaman_detail_c.tgl_jatuh_tempo = param_libur.tgl_libur");
					foreach ($cek_tanggal as $data_libur){
					$libur =$data_libur['libur'];
					}
					db_jalankan("delete from pinjaman_detail_c where id_pinjam=?", 's', (string) $no_anggota);
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
				if($jenis_akad =='MURAB'){
					$input_loan = db_jalankan("REPLACE into calon_loan(buss_date,code_kel,no_anggota,cif,Cust_Short_name,Deal_type,suffix,bagi_hasil,Tenor,Plafond,os,saldo_margin,angsuran,pokok,ijaroh,bulat,run_tenor,ke,usaha,nama_usaha,unit,tgl_wakalah,tgl_akad,tgl_murab,next_schedule,maturity_date,hari,cao,USERID,status,status_usia,status_app,gol,deal_produk)
					values(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,'0','1',?,?,?,?,?,?,?,?,?,?,?,'ANGGOTA',?,?,'0','2')", 'sssssssssssssssssssssssssssss', (string) $buss_date, (string) $code_kel, (string) $no_anggota, (string) $cif, (string) $nama, (string) $deal_type, (string) $suffix, (string) $margin, (string) $tenor, (string) $pla, (string) $os, (string) $margin, (string) $angsuran, (string) $pokok, (string) $ijaroh, (string) $uang, (string) $usaha, (string) $nama_usaha, (string) $branch, (string) $tgl_wa, (string) $tgl_akad, (string) $murab, (string) $next_schedule, (string) $new_akhir_hari, "$dayList[$day]", (string) $cao, (string) $userid, (string) $status_usia, (string) $status_app);	

}else{
$input_loan = db_jalankan("REPLACE into calon_loan(buss_date,code_kel,no_anggota,cif,Cust_Short_name,Deal_type,suffix,bagi_hasil,Tenor,Plafond,os,saldo_margin,angsuran,pokok,ijaroh,bulat,run_tenor,ke,usaha,nama_usaha,unit,tgl_wakalah,tgl_akad,tgl_murab,next_schedule,maturity_date,hari,cao,USERID,status,status_usia,status_app,gol,deal_produk)
					values(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,'0','1',?,?,?,?,?,?,?,?,?,?,?,'ANGGOTA',?,?,'0','3')", 'sssssssssssssssssssssssssssss', (string) $buss_date, (string) $code_kel, (string) $no_anggota, (string) $cif, (string) $nama, (string) $deal_type, (string) $suffix, (string) $margin, (string) $tenor, (string) $pla, (string) $os, (string) $margin, (string) $angsuran, (string) $pokok, (string) $ijaroh, (string) $uang, (string) $usaha, (string) $nama_usaha, (string) $branch, (string) $tgl_wa, (string) $tgl_akad, (string) $murab, (string) $next_schedule, (string) $new_akhir_hari, "$dayList[$day]", (string) $cao, (string) $userid, (string) $status_usia, (string) $status_app);	

}

					//}
				//@mysql_query ("delete from pinjaman_detail where tgl_jatuh_tempo in (select tgl_libur from param_libur where tgl_libur=pinjaman_detail.tgl_jatuh_tempo ) ");
				//@mysql_query("SET @count = 0;");
				//@mysql_query ("UPDATE pinjaman_detail SET cicilan=@count:=@count+1 where id_pinjam='$no_anggota' ORDER BY cicilan");
										
					$i = db_jalankan("replace into anggota
					(no,CODE_KEL,NOREK,BUSS_DATE,CIF,CUST_SHORT_NAME,UNIT,DEAL_TYPE,HADDRESS1,HADDRESS2,HADDRESS3,HPOST_CODE,HADDRESS4,HADDRESS5,MOBILE_PHONE,HOME_PHONE,GENDER,BIRTH_DATE,ID_NUMBER,CITIZENSHIP,MARITAL_STATUS,RELIGION,MOTHER_NAME,NPWP,SOURCE_INCOME,EDUCATION,TEMPAT_LAHIR,ID_EXPIRED,WARIS,CAO,USERID,status,pekerjaan_pasangan)
					values
					(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,'ANGGOTA',?)", 'ssssssssssssssssssssssssssssssss', (string) $no, (string) $code_kel, (string) $no_anggota, (string) $buss_date, (string) $cif, (string) $nama, (string) $branch, (string) $deal_type, (string) $alamat, (string) $lurah, (string) $kec, (string) $kode_pos, (string) $kota, (string) $rt, (string) $notlp, (string) $home_tlp, (string) $jenis_kel, (string) $tgl_lahir, (string) $ktp, (string) $negara, (string) $status_kawin, (string) $agama, (string) $ibu, (string) $npwp, (string) $usaha, (string) $pendidikan, (string) $tempat_lahir, (string) $id_expire, (string) $nama1, (string) $cao, (string) $userid, (string) $kerja);
db_jalankan("delete from barang where cif=?", 's', (string) $cif);
					$b = db_jalankan("replace into barang(id_barang,unit,cif,code_kel,barang_satu,harga_satu,barang_dua,harga_dua,barang_tiga,harga_tiga,barang_empat,harga_empat,barang_lima,harga_lima,barang_enam,harga_enam,barang_tujuh,harga_tujuh,barang_delapan,harga_delapan,barang_sembilan,harga_sembilan,barang_sepuluh,harga_sepuluh,userid) 
					VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?) where id_barang =?", 'ssssssssssssssssssssssssss', (string) $no_anggota, (string) $branch, (string) $cif, (string) $code_kel, (string) $barang_satu, (string) $harga_satu, (string) $barang_dua, (string) $harga_dua, (string) $barang_tiga, (string) $harga_tiga, (string) $barang_empat, (string) $harga_empat, (string) $barang_lima, (string) $harga_lima, (string) $barang_enam, (string) $harga_enam, (string) $barang_tujuh, (string) $harga_tujuh, (string) $barang_delapan, (string) $harga_delapan, (string) $barang_sembilan, (string) $harga_sembilan, (string) $barang_sepuluh, (string) $harga_sepuluh, (string) $userid, (string) $no_anggota);
					
					$rekomendasi = db_jalankan("replace into rekomendasi (norek,cif,nominal)values(?,?,?)", 'sss', (string) $no_anggota, (string) $cif, (string) $rekomen);
										
								if(($i)||($input_loan)||($b)||($r)||($rekomendasi)){ 
	
							// versi lama memakai nama yang sudah di-escape untuk SQL; addslashes menjaga alert tetap benar bila nama berisi tanda kutip
							echo "<script> alert ('data Anggota " . addslashes($nama) . " sudah di koreksi pada kelompok $code_kel ')</script>";

						echo "<script language='javascript'>document.location.href='dim.php'</script>";

				} else {
					echo "gagal input <a href=\"javascript:history.back()\">kembali</a>";
			} 
	
		
	
?>
