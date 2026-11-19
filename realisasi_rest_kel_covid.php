<?php require_once __DIR__ . '/koneksi_db.php'; 
require_once __DIR__ . '/inc/notif.php';
session_start();
ob_start();
$userid=$_SESSION['userid'];
$tanggal=$_SESSION['stgl_system'];
$tgl_input=date("Y-m-d");
$buss_date=$tgl_system;
// §2d (2026-11-17, perintah user): $tgl_system tidak pernah diisi di file ini -> rest_covid.tgl_perubahan COVID/MURAB
// tersimpan 0000-00-00. Kini memakai $tanggal (tanggal sistem), sama dengan cabang REST_POKOK_MARGIN.
$kel =$_GET['code_kel'];
$kode_kel_url = $kel;
$code_kel=$_POST['kel'];
//cek apakah user sudah login
if(!isset($_SESSION['stgl_system'])){
notif_pergi('Maaf, tanggal system belum di setup', 'param_tgl.php', 'warning');

   /* redirect ditangani notif_pergi */
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
	// §2d (2026-11-19, perintah user): hasil per anggota dikumpulkan lalu SATU ringkasan di akhir (dulu "Suksess" per anggota
	// walau tidak diproses); CIF unit lain dilewati; penulisan per anggota satu transaksi. Hitungan & jurnal tidak diubah.
	require_once __DIR__ . '/inc/cek_login.php';
	$unit_saya = unit_user();
	$checbox = array_values((array) $checbox);
	$jml_proses = 0;
	$dilewati = array();

	for ($i=0; $i <sizeof($checbox);$i++){
		$checbox[$i] = (string) $checbox[$i];
		$data = db_satu("select hari,tenor,maturity_date,bulat,ijaroh,pokok,no_anggota,os,saldo_margin,loan.code_kel,loan.unit,angsuran,loan.Cust_Short_name,loan.cif,loan.cao,status_app FROM loan 
		left join anggota on anggota.cif=loan.cif
		where  loan.cif =?", 's', (string) $checbox[$i]);

		if (!$data) { $dilewati[] = 'CIF ' . $checbox[$i] . ' (tidak ditemukan)'; continue; }
		$label_ang = 'CIF ' . $data['cif'] . ' AN ' . $data['Cust_Short_name'];
		if ($unit_saya === '' || (string) $data['unit'] !== $unit_saya) { $dilewati[] = 'CIF ' . $data['cif'] . ' (bukan anggota unit Anda)'; continue; }
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
		$dilewati[] = "$label_ang (Kemampuan Bayar kosong)"; // §2d: ringkasan
		continue;
			
		}else{
			
			
			
			
			$tenor_baru=$pembiayaan/$nominal;
			$ijaroh_baru=$saldo_margin/$tenor_baru;
			// §2d (2026-11-17, perintah user): tenor baru (OS / kemampuan bayar) bisa pecahan; dulu dipakai langsung di
			// strtotime("+5.76 WEEK") -> jatuh tempo 0000-00-00 / tahun 2028. Kini dibulatkan KE ATAS (5,76 -> 6 kali setor sampai
			// lunas) untuk Tenor & jatuh tempo. Nominal angsuran/pokok/margin tetap dihitung seperti dulu.
			$tenor_bulat=(int) ceil(round($tenor_baru, 6));
			$pokok_baru = $nominal - $ijaroh_baru;
			$angsuran_baru =$nominal; 
$kodetr=8;
            $kodetr=acakangkahuruf($kodetr);
            $kode_trans="BH/".$unit.$kodetr;
	
		$tgl_m    = strtotime($tanggal);
        $maturity_date_res =  date("Y-m-d", strtotime("+$tenor_bulat WEEK", $tgl_m));
			
			
				// §2d: semua penulisan anggota ini satu transaksi
				$koneksi_transaksi = db_ni();
				mysqli_begin_transaction($koneksi_transaksi);
				$gagal_awal = db_jumlah_gagal();
				$alasan_lewat = '';
				switch ($status_app) {
					case "REST_POKOK_MARGIN":
db_jalankan("delete from  rest_covid where cif =?", 's', (string) $cif);	
db_jalankan("insert into rest_covid values('',?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)", 'sssssssssssssss', (string) $tanggal, (string) $cif, (string) $angsur, (string) $pokok, (string) $ijaroh, (string) $bulat, (string) $tenor, (string) $mat, (string) $angsuran_baru, (string) $pokok_baru, (string) $ijaroh_baru, (string) $angsuran_baru, (string) $tenor_bulat, (string) $maturity_date_res, (string) $kelompok);
db_jalankan("update loan set angsuran=?, pokok=?,ijaroh=?,bulat=?,gol='1',run_tenor ='0', ke='1',maturity_date =?,tgl_akad =?,status_app='REST_ANGSURAN',Tenor =?, deal_produk ='1' where cif =?", 'ssssssss', (string) $angsuran_baru, (string) $pokok_baru, (string) $ijaroh_baru, (string) $angsuran_baru, (string) $maturity_date_res, (string) $tanggal, (string) $tenor_bulat, (string) $cif);
db_jalankan("delete from tunggakan where cif =?", 's', (string) $cif);
$update =db_jalankan("delete from pinjaman_detail where cif =?", 's', (string) $cif);
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

$alasan_lewat = 'status REST tidak bisa direstrukturisasi lagi'; // §2d: dulu notif lalu tetap "Suksess"
break;
case "COVID":
db_jalankan("delete from  rest_covid where cif =?", 's', (string) $cif);	
db_jalankan("insert into rest_covid values('',?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)", 'sssssssssssssss', (string) $tanggal, (string) $cif, (string) $angsur, (string) $pokok, (string) $ijaroh, (string) $bulat, (string) $tenor, (string) $mat, (string) $angsuran_baru, (string) $pokok_baru, (string) $ijaroh_baru, (string) $angsuran_baru, (string) $tenor_bulat, (string) $maturity_date_res, (string) $kelompok);
db_jalankan("update loan set angsuran=?, pokok=?,ijaroh=?,bulat=?,gol='1',run_tenor ='0', ke='1',maturity_date =?,tgl_akad =?,status_app='REST_ANGSURAN',Tenor =?, deal_produk ='1' where cif =?", 'ssssssss', (string) $angsuran_baru, (string) $pokok_baru, (string) $ijaroh_baru, (string) $angsuran_baru, (string) $maturity_date_res, (string) $tanggal, (string) $tenor_bulat, (string) $cif);
db_jalankan("delete from tunggakan where cif =?", 's', (string) $cif);
$update =db_jalankan("delete from pinjaman_detail where cif =?", 's', (string) $cif);
$ket ="Restrukturisasi Rest pokok margin ke REST_ANGSURAN ";
break;
case "MURAB":
db_jalankan("delete from  rest_covid where cif =?", 's', (string) $cif);	
db_jalankan("insert into rest_covid values('',?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)", 'sssssssssssssss', (string) $tanggal, (string) $cif, (string) $angsur, (string) $pokok, (string) $ijaroh, (string) $bulat, (string) $tenor, (string) $mat, (string) $angsuran_baru, (string) $pokok_baru, (string) $ijaroh_baru, (string) $angsuran_baru, (string) $tenor_bulat, (string) $maturity_date_res, (string) $kelompok);
db_jalankan("update loan set angsuran=?, pokok=?,ijaroh=?,bulat=?,gol='1',run_tenor ='0', ke='1',maturity_date =?,tgl_akad =?,status_app='REST_ANGSURAN',Tenor =?,deal_produk ='1' where cif =?", 'ssssssss', (string) $angsuran_baru, (string) $pokok_baru, (string) $ijaroh_baru, (string) $angsuran_baru, (string) $maturity_date_res, (string) $tanggal, (string) $tenor_bulat, (string) $cif);
db_jalankan("delete from tunggakan where cif =?", 's', (string) $cif);
$update =db_jalankan("delete from pinjaman_detail where cif =?", 's', (string) $cif);
$ket ="Restrukturisasi Rest pokok margin ke REST_ANGSURAN ";
break;
default:
$alasan_lewat = 'status ' . $status_app . ' tidak bisa direstrukturisasi di menu ini'; // §2d: dulu tetap "Suksess"
}
// §2d: akhir anggota -> commit / batalkan bila ada query gagal / dilewati (tidak ada yang ditulis)
if ($alasan_lewat !== '') {
	mysqli_rollback($koneksi_transaksi);
	$dilewati[] = "$label_ang ($alasan_lewat)";
} elseif (db_jumlah_gagal() > $gagal_awal) {
	mysqli_rollback($koneksi_transaksi);
	$dilewati[] = "$label_ang (GAGAL, data tidak disimpan: " . db_error_terakhir() . ')';
} else {
	mysqli_commit($koneksi_transaksi);
	$jml_proses++;
}
		
		}	
		

		
// versi lama menampilkan $kel yang sudah tertimpa hasil query (PHP 8: fatal error); kini menampilkan kode kelompok dari URL
// §2d: dulu notif_pergi("Restrurisasi an $kode_kel_url Suksess") per anggota; kini satu ringkasan setelah loop
	
		
}	
	$ringkas = "Restrukturisasi selesai: $jml_proses anggota diproses.";
	if ($dilewati) {
		$ringkas .= "\nDilewati " . count($dilewati) . " anggota:\n- " . implode("\n- ", $dilewati);
	}
	notif_pergi($ringkas, 'rest_covid.php', $dilewati ? ($jml_proses > 0 ? 'warning' : 'error') : 'success');
}else {
			notif_pergi('anda belum memilih data.', 'HISTORY_BACK', 'error');
	}

  
 
			
			
                     
        
				


?>

