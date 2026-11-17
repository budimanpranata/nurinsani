<?php require_once __DIR__ . '/koneksi_db.php'; 
require_once __DIR__ . '/inc/notif.php';
session_start();
ob_start();
$userid=$_SESSION['userid'];
$tgl_system=$_SESSION['stgl_system'];
$tgl_input=date("Y-m-d");
$buss_date=$tgl_system;
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

$tanggal=$tgl_system;
$simpan=$_SESSION['simpans'];	
$baki=$_SESSION['bakis'];	
$nom=isset($_POST['bulat']) ? $_POST['bulat'] : null;			
			
$checbox = isset($_POST['cekbox']) ? $_POST['cekbox'] : null;

if (isset($_POST["realisasi"])){
  // "Save Changes" clicked

$checbox = is_array($checbox) ? array_values($checbox) : ($checbox ? array($checbox) : null);
if ($checbox) {
	// §2g (2026-11-17, perintah user): semua CIF wajib milik unit user (dulu CIF unit lain ikut diproses).
	// Dicek semua sebelum ada yang diproses; satu saja bukan milik unit -> HTTP 403 tanpa perubahan data.
	require_once __DIR__ . '/inc/cek_login.php';
	foreach ($checbox as $c) {
		wajib_cif_unit((string) $c);
	}
	$jml_proses=0;
	$jml_lewat=0;
	for ($i=0; $i <sizeof($checbox);$i++){
		$kunci='realisasi_rest_kel_' . (string) $checbox[$i];
		db_satu("SELECT GET_LOCK(?, 30) AS kunci", 's', $kunci);
		// §2g: loan.hari kini ikut di-SELECT (dulu tidak, sehingga cek hari libur selalu 0)
		$kel = db_semua("select maturity_date,no_anggota,anggota.cif,loan.code_kel,loan.unit,angsuran,loan.Cust_Short_name,loan.cif,os,loan.cao,saldo_margin,status_app,loan.hari FROM loan
		left join anggota on anggota.cif=loan.cif
		where  loan.cif =?", 's', (string) $checbox[$i]);
		$data = $kel ? $kel[0] : false;

		$mat=$data['maturity_date'];
		$cif=$data['cif'];
		$angsur=$data['angsuran'];
		$hari=$data['hari'];


		$row_tunggak=db_satu("select sum(KREDIT-DEBIT) as saldo_tunggak from tunggakan where cif =? ", 's', (string) $checbox[$i]) or die(mysql_error());
 $saldo_tunggak=$row_tunggak['saldo_tunggak'];
$tunggak=$saldo_tunggak;

		// §2g: anggota tanpa tunggakan (atau angsuran 0) dilewati tanpa perubahan. Dulu jatuh tempo diisi tanggal
		// sistem (pinjaman dipercepat), termasuk saat refresh/klik ganda karena tunggakan sudah dihapus di proses pertama.
		if (compat_num($tunggak) <= 0 || compat_num($angsur) <= 0) {
			db_satu("SELECT RELEASE_LOCK(?) AS lepas", 's', $kunci);
			$jml_lewat++;
			continue;
		}

// §2g: minggu dibulatkan ke atas (dulu "+1.5 WEEK" dsb. memberi tanggal kacau, mis. 1,9999999 -> tahun 193680),
// sama dengan realisasi_rest_kel_covid.php.
$ft =(int) ceil(round(compat_num($tunggak) / compat_num($angsur), 6));

		$tgl_m    = strtotime($tgl_system);
        $maturity_date_res =  date("Y-m-d", strtotime("+$ft WEEK", $tgl_m));
		
$cek_tanggal = db_semua("SELECT COUNT(tgl_libur) as libur from param_libur where tgl_libur between ? and ? and Hari =?", 'sss', (string) $mat, (string) $maturity_date_res, (string) $hari);
        foreach ($cek_tanggal as $data_libur){
            $libur =$data_libur['libur'];
           
           
        }

if($libur>0){
$ft =$ft+$libur;


        $tgl_m    = strtotime($tgl_system);
        $maturity_date_res =  date("Y-m-d", strtotime("+$ft WEEK", $tgl_m));



}else{
$maturity_date_res=$maturity_date_res;
}

db_jalankan("update loan set maturity_date =?, gol='1', deal_produk ='1' where cif =?", 'ss', (string) $maturity_date_res, (string) $cif);
db_jalankan("delete from tunggakan where cif =?", 's', (string) $cif);
db_satu("SELECT RELEASE_LOCK(?) AS lepas", 's', $kunci);
$jml_proses++;
	}

	// §2g: pesan sekali di akhir (dulu "Restrurisasi an <cif> Suksess" + pengalihan per anggota)
	if ($jml_proses > 0) {
		$pesan = "Restrukturisasi Jatpo $jml_proses anggota Suksess.";
		if ($jml_lewat > 0) {
			$pesan .= " $jml_lewat anggota dilewati karena tidak punya tunggakan.";
		}
		notif_pergi($pesan, 'rest_kel.php', 'success');
	} else {
		notif_pergi('Maaf, anggota yang dipilih tidak punya tunggakan. Tidak ada yang diproses.', 'rest_kel.php');
	}

}else {
			notif_pergi('anda belum memilih data.', 'rest_kel.php');
	}

  
  
} 
?>

