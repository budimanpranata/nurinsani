<?php require_once __DIR__ . '/koneksi_db.php'; 
session_start();
ob_start();
$userid=$_SESSION['userid'];
$tanggal=$_SESSION['stgl_system'];
$tgl_input=date("Y-m-d");
$buss_date=$tgl_system;
$kel =$_GET['code_kel'];
$kode_kel_url = $kel;
$code_kel=$_POST['kel'];
//cek apakah user sudah login
if(!isset($_SESSION['stgl_system'])){
echo "<script>alert('Maaf,.tanggal system belum di setup');</script>";

   header("location:param_tgl.php");
 exit; }
//cek apakah user sudah login
if(!isset($_SESSION['userid'])){
   header("location:index.html");
 exit; }

//cek level user
if($_SESSION['level']!="unit"){
   header("location:index.html");
 exit; }
include "function.php";


$nom=isset($_POST['bulat']) ? $_POST['bulat'] : null;			
			
$checbox = isset($_POST['cekbox']) ? $_POST['cekbox'] : null;


//if (isset($_POST['cekbox'])){
if($checbox){

	for ($i=0; $i <sizeof($checbox);$i++){
		$data = db_satu("select hari,tenor,maturity_date,bulat,ijaroh,pokok,no_anggota,os,saldo_margin,loan.code_kel,loan.unit,angsuran,loan.Cust_Short_name,loan.cif,loan.cao,status_app FROM loan 
		left join anggota on anggota.cif=loan.cif
		where  loan.cif =?", 's', (string) $checbox[$i]);

		$mat=$data['maturity_date'];
		$cif=$data['cif'];
		$angsur=$data['angsuran'];
		$bulat=$data['bulat'];
		$pokok=$data['pokok'];
		$ijaroh=$data['ijaroh'];
		$kelompok=$data['code_kel'];
		$tenor=$data['tenor'];
		$no_anggota=$data['no_anggota'];
		
		$unit=$data['unit'];		
		$hari=$data['hari'];
		$pembiayaan=$data['os'];
		$saldo_margin=$data['saldo_margin'];
$status_app=$data['status_app'];
// Kemampuan Bayar diambil berdasarkan CIF anggota (form mengirim bulat[CIF]).
// Dulu dipasangkan menurut urutan centang, sehingga anggota bisa memakai isian baris lain.
$nominal = isset($nom[$checbox[$i]]) ? $nom[$checbox[$i]] : null;
		
		
		
		
		
		




// PHP 5/7: Kemampuan Bayar kosong/bukan angka dianggap 0 sehingga anggota dilewati (PHP 8: '' == 0 bernilai false lalu error saat dibagi)
if (compat_num($nominal) == 0){
		$data=0;
			
		}else{
			
			
			
			
			$tenor_baru=$pembiayaan/$nominal;
			$ijaroh_baru=$saldo_margin/$tenor_baru;
			$pokok_baru = $nominal - $ijaroh_baru;
			$angsuran_baru =$nominal; 
$kodetr=8;
            $kodetr=acakangkahuruf($kodetr);
            $kode_trans="BH/".$unit.$kodetr;
	
		$tgl_m    = strtotime($tanggal);
        $maturity_date_res =  date("Y-m-d", strtotime("+$tenor_baru WEEK", $tgl_m));
			
			
				switch ($status_app) {
					case "REST_POKOK_MARGIN":
db_jalankan("delete from  rest_covid where cif =?", 's', (string) $cif) or die(mysql_error());	
db_jalankan("insert into rest_covid values('',?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)", 'sssssssssssssss', (string) $tanggal, (string) $cif, (string) $angsur, (string) $pokok, (string) $ijaroh, (string) $bulat, (string) $tenor, (string) $mat, (string) $angsuran_baru, (string) $pokok_baru, (string) $ijaroh_baru, (string) $angsuran_baru, (string) $tenor_baru, (string) $maturity_date_res, (string) $kelompok) or die(mysql_error());
db_jalankan("update loan set angsuran=?, pokok=?,ijaroh=?,bulat=?,gol='1',run_tenor ='0', ke='1',maturity_date =?,tgl_akad =?,status_app='REST_ANGSURAN',Tenor =?, deal_produk ='1' where cif =?", 'ssssssss', (string) $angsuran_baru, (string) $pokok_baru, (string) $ijaroh_baru, (string) $angsuran_baru, (string) $maturity_date_res, (string) $tanggal, (string) $tenor_baru, (string) $cif) or die(mysql_error());
db_jalankan("delete from tunggakan where cif =?", 's', (string) $cif) or die(mysql_error());
$update =db_jalankan("delete from pinjaman_detail where cif =?", 's', (string) $cif) or die(mysql_error());
$update =db_jalankan("delete from temp_eod where cif =?", 's', (string) $cif) or die(mysql_error());
$ket ="Restrukturisasi Rest pokok margin ke REST_ANGSURAN ";

db_jalankan("insert into jurnal_umum(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values('',?,?,?)", 'sss', (string) $kode_trans, (string) $tanggal, (string) $unit);
//Realisasi Piutang Margin	
db_jalankan("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
				values(?,?,'1413000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_trans, (string) $tanggal, (string) $ket, (string) $pembiayaan, (string) $userid);
db_jalankan("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
				values(?,?,'1421000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_trans, (string) $tanggal, (string) $ket, (string) $saldo_margin, (string) $userid);
//Realisasi Piutang Margin	
db_jalankan("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
	values(?,?,'1423000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_trans, (string) $tanggal, (string) $ket, (string) $saldo_margin, (string) $userid);
//Realiasasi Murabahah Debit pada Piutang Murabahah total jumlah Pokok dan margin 
db_jalankan("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
				values(?,?,'1411000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_trans, (string) $tanggal, (string) $ket, (string) $pembiayaan, (string) $userid);

break;
case "REST":

echo "<script>alert('Maaf Status REST tidak bisa di Restrturisasi lagi');</script>";
        echo "<script language='javascript'>document.location.href='rest_covid.php'</script>"; 
break;
case "COVID":
db_jalankan("delete from  rest_covid where cif =?", 's', (string) $cif) or die(mysql_error());	
db_jalankan("insert into rest_covid values('',?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)", 'sssssssssssssss', (string) $tgl_system, (string) $cif, (string) $angsur, (string) $pokok, (string) $ijaroh, (string) $bulat, (string) $tenor, (string) $mat, (string) $angsuran_baru, (string) $pokok_baru, (string) $ijaroh_baru, (string) $angsuran_baru, (string) $tenor_baru, (string) $maturity_date_res, (string) $kelompok) or die(mysql_error());
db_jalankan("update loan set angsuran=?, pokok=?,ijaroh=?,bulat=?,gol='1',run_tenor ='0', ke='1',maturity_date =?,tgl_akad =?,status_app='REST_ANGSURAN',Tenor =?, deal_produk ='1' where cif =?", 'ssssssss', (string) $angsuran_baru, (string) $pokok_baru, (string) $ijaroh_baru, (string) $angsuran_baru, (string) $maturity_date_res, (string) $tanggal, (string) $tenor_baru, (string) $cif) or die(mysql_error());
db_jalankan("delete from tunggakan where cif =?", 's', (string) $cif) or die(mysql_error());
$update =db_jalankan("delete from pinjaman_detail where cif =?", 's', (string) $cif) or die(mysql_error());
$update =db_jalankan("delete from temp_eod where cif =?", 's', (string) $cif) or die(mysql_error());
$ket ="Restrukturisasi Rest pokok margin ke REST_ANGSURAN ";
break;
case "MURAB":
db_jalankan("delete from  rest_covid where cif =?", 's', (string) $cif) or die(mysql_error());	
db_jalankan("insert into rest_covid values('',?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)", 'sssssssssssssss', (string) $tgl_system, (string) $cif, (string) $angsur, (string) $pokok, (string) $ijaroh, (string) $bulat, (string) $tenor, (string) $mat, (string) $angsuran_baru, (string) $pokok_baru, (string) $ijaroh_baru, (string) $angsuran_baru, (string) $tenor_baru, (string) $maturity_date_res, (string) $kelompok) or die(mysql_error());
db_jalankan("update loan set angsuran=?, pokok=?,ijaroh=?,bulat=?,gol='1',run_tenor ='0', ke='1',maturity_date =?,tgl_akad =?,status_app='REST_ANGSURAN',Tenor =?,deal_produk ='1' where cif =?", 'ssssssss', (string) $angsuran_baru, (string) $pokok_baru, (string) $ijaroh_baru, (string) $angsuran_baru, (string) $maturity_date_res, (string) $tanggal, (string) $tenor_baru, (string) $cif) or die(mysql_error());
db_jalankan("delete from tunggakan where cif =?", 's', (string) $cif) or die(mysql_error());
$update =db_jalankan("delete from pinjaman_detail where cif =?", 's', (string) $cif) or die(mysql_error());
$update =db_jalankan("delete from temp_eod where cif =?", 's', (string) $cif) or die(mysql_error());
$ket ="Restrukturisasi Rest pokok margin ke REST_ANGSURAN ";
break;
default:
		
}
		
		}	
		

		
// versi lama menampilkan $kel yang sudah tertimpa hasil query (PHP 8: fatal error); kini menampilkan kode kelompok dari URL
echo '<script>alert(' . json_encode("Restrurisasi an $kode_kel_url Suksess") . ');</script>';
        echo "<script language='javascript'>document.location.href='rest_covid.php'</script>";
	
		
}	
}else {
			echo '<script>alert("anda belum memilih data."); window.history.back();</script>';
	}

  
 
			
			
                     
        
				


?>

