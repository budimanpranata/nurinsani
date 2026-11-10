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
if ($checbox) {
	for ($i=0; $i <sizeof($checbox);$i++){
		//$query="insert into data (name,alamat) values ('".$checbox[$i]."','".$nom[$i]."')";
		$kel = db_semua("select no_anggota,anggota.cif,loan.code_kel,loan.unit,angsuran,loan.Cust_Short_name,loan.cif,os,loan.cao,saldo_margin,status_app,deal_produk,Tenor,run_tenor,loan.ijaroh FROM loan
		left join anggota on anggota.cif=loan.cif
		where  loan.cif =?", 's', (string) $checbox[$i]);
		$data = $kel ? $kel[0] : false;

		// Perbaikan 2026-10-30 (perintah user): pengaman yang sama dengan lunasi.php, per anggota.
		// Anggota yang tidak lolos dilewati (alert) dan anggota berikutnya tetap diproses.
		$cek_loan = db_satu("select cif, os, status from loan where cif = ?", 's', (string) $checbox[$i]);
		if (!$cek_loan) {
			notif_pesan('Maaf...,CIF ' . $checbox[$i] . ' tidak ditemukan');
			continue;
		}
		if ($cek_loan['status'] == 'LUNAS' || compat_num($cek_loan['os']) <= 0) {
			notif_pesan('Maaf...,Pembiayaan CIF ' . $checbox[$i] . ' sudah lunas');
			continue;
		}
		if ((string) $cek_loan['cif'] === '') {
			notif_pesan('Maaf...,CIF kosong, pelunasan dibatalkan');
			continue;
		}
		// semua penulisan anggota ini dalam satu transaksi (jurnal_umum & rek_loan masih MyISAM, tidak ikut dibatalkan)
		$koneksi_transaksi = db_ni();
		mysqli_begin_transaction($koneksi_transaksi);
		$gagal_awal = db_jumlah_gagal();

		$os=$data['os'];
		$anggota=$data['no_anggota'];
		$saldo_margin=$data['saldo_margin'];
		$pokok=$os-$saldo_margin;
		$unit=$data ['unit'];
		$code_kel=$data['code_kel'];
		$cao=$data['cao'];
		$cif=$data['cif'];
		$tenor=$data['Tenor'];
		$run_tenor=$data['run_tenor'];
		$deal_produk=$data['deal_produk'];
		$angsuran=$data['angsuran'];
		$nama=$data['Cust_Short_name']; // dikirim sebagai parameter, tidak perlu di-escape
		$ket ="Pelunasan $nama $cif";
		$status_app=$data['status_app'];
	
		$cek_saldo = db_semua("SELECT SUM(KREDIT - DEBIT) AS saldo from simpanan where cif =? and TYPE not in ('04','05','07')", 's', (string) $cif);
		$data_saldo = $cek_saldo ? $cek_saldo[0] : false;
		$saldo=$data_saldo['saldo'];

		// Perbaikan 2026-11-03 (perintah user): musyarokah dilunasi sebesar OS + nisbah (ijaroh) + 2.500,
		// sama dengan proses_eod_mob_debet_pelunasan.php (kasus "pelunasan")
		$ijaroh_mus=compat_num($data['ijaroh']);
		$saldo_os=compat_num($os);
		if($status_app=='MUSYAROKAH'){
			$os = $saldo_os + $ijaroh_mus + 2500;
		}

 $kodetr=7;
            $kodetr=acakangkahuruf($kodetr);
            $kode_trans="BU/".$unit.$kodetr;
		
	


if ($saldo < $os ){
			notif_pesan("Maaf Saldo an $cif " . addslashes($nama) . " Pelunasan Lebih Kecil dari Sisa Pembiayaan"); // addslashes: dulu nama sudah di-escape
			//echo "<script language='javascript'>document.location.href='pelunasan_kel.php'</script>";
			}else {
							
					$kodetr=5;
					$kodetr=acakangkahuruf($kodetr);
					$kode_trans="BU/".$unit .$kodetr;
				if($status_app=='REST'){
				db_jalankan("insert into jurnal_umum(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values('',?,?,?)", 'sss', (string) $kode_trans, (string) $tanggal, (string) $unit);
					//Realisasi Piutang Margin	

				db_jalankan("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                values(?,?,'2101000',?,'Bukti SYSTEM',?,?,?)", 'ssssss', (string) $unit, (string) $kode_trans, (string) $tanggal, (string) $ket, (string) $os, (string) $userid);
               
                db_jalankan("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                values(?,?,'1411000',?,'Bukti SYSTEM',?,?,?)", 'ssssss', (string) $unit, (string) $kode_trans, (string) $tanggal, (string) $ket, (string) $os, (string) $userid);

				$uos = db_jalankan("Update loan set os =os - ?,saldo_margin='0',ke='1',status='LUNAS', Tenor ='25',status_app ='MURAB' where no_anggota=?", 'ss', (string) $os, (string) $anggota);
				$hapus = db_jalankan("delete from tunggakan where cif=?", 's', (string) $cif);

				$sc = db_jalankan("update anggota set SOURCE_INCOME ='0' where cif=?", 's', (string) $cif);

				$sc = db_jalankan("update simpanan set type ='01', blok=' ' where cif=? and type ='02'", 's', (string) $cif);


				db_jalankan("update pinjaman_detail set jumlah_bayar=?,tgl_bayar=? where id_pinjam=?", 'sss', (string) $angsuran, (string) $tanggal, (string) $anggota);
				
				$t = db_jalankan("insert into simpanan(BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,blok,tgl_input,kode_transaksi) 
				VALUES (?,?,?,?,?,?,'03','0',?,?,'',?,'0',?,?)", 'sssssssssss', (string) $tanggal, (string) $anggota, (string) $unit, (string) $cif, (string) $code_kel, (string) $os, (string) $userid, (string) $ket, (string) $cao, (string) $date, (string) $kode_trans);
db_jalankan("insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif,AO)VALUES('',?,?,?,'0',?,'03','Pelunasan',?,'REALISASI POKOK',?,(select cao from loan where cif=? limit 1))", 'sssssss', (string) $tanggal, (string) $unit, (string) $anggota, (string) $os, (string) $userid, (string) $cif, (string) $cif) or die(mysql_error());
				 		db_jalankan("Update  tagihan_cs set status_realisasi ='BAYAR'  where cif  =? and tgl_tagih=?", 'ss', (string) $checbox[$i], (string) $tanggal);


				}elseif($status_app=='REST_POKOK_MARGIN'){
				
db_jalankan("insert into jurnal_umum(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values('',?,?,?)", 'sss', (string) $kode_trans, (string) $tanggal, (string) $unit);
					//Realisasi Piutang Margin	
					
				db_jalankan("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
				values(?,?,'2101000',?,'Bukti System',?,?,?)", 'ssssss', (string) $unit, (string) $kode_trans, (string) $tanggal, (string) $ket, (string) $os, (string) $userid);
	
				db_jalankan("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
				values(?,?,'1411000',?,'Bukti System',?,?,?)", 'ssssss', (string) $unit, (string) $kode_trans, (string) $tanggal, (string) $ket, (string) $os, (string) $userid);
								//Realisasi Piutang Margin	
				db_jalankan("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
							values(?,?,'1421000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_trans, (string) $tanggal, (string) $ket, (string) $saldo_margin, (string) $userid);
				//Realiasasi Murabahah Debit pada Piutang Murabahah total jumlah Pokok dan margin 
				db_jalankan("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
				values(?,?,'41002',?,'Bukti System',?,?,?)", 'ssssss', (string) $unit, (string) $kode_trans, (string) $tanggal, (string) $ket, (string) $saldo_margin, (string) $userid);
				
				$uos = db_jalankan("Update loan set os =os - ?,saldo_margin='0',run_tenor='0',ke='1',status='LUNAS' where cif=?", 'ss', (string) $os, (string) $cif);
				$hapus = db_jalankan("delete from tunggakan where cif=?", 's', (string) $cif);

				$sc = db_jalankan("update anggota set SOURCE_INCOME ='0' where cif=?", 's', (string) $cif);

				$sc = db_jalankan("update simpanan set type ='01', blok=' ' where cif=? and type ='02'", 's', (string) $cif);


				db_jalankan("update pinjaman_detail set jumlah_bayar=?,tgl_bayar=? where id_pinjam=?", 'sss', (string) $angsuran, (string) $tanggal, (string) $anggota);
				db_jalankan("delete from tunggakan where cif=?", 's', (string) $cif);
				$t = db_jalankan("insert into simpanan(BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,blok,tgl_input,kode_transaksi) 
				VALUES (?,?,?,?,?,?,'03','0',?,?,'',?,'0',?,?)", 'sssssssssss', (string) $tanggal, (string) $anggota, (string) $unit, (string) $cif, (string) $code_kel, (string) $os, (string) $userid, (string) $ket, (string) $cao, (string) $date, (string) $kode_trans);
db_jalankan("insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif,AO)VALUES('',?,?,?,'0',?,'03','Pelunasan',?,'REALISASI POKOK',?,(select cao from loan where cif=? limit 1))", 'sssssss', (string) $tanggal, (string) $unit, (string) $anggota, (string) $pokok, (string) $userid, (string) $cif, (string) $cif) or die(mysql_error());
				db_jalankan("insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif,AO)VALUES('',?,?,?,'0',?,'03','Pelunasan',?,'REALISASI BAGHAS',?,(select cao from loan where cif=? limit 1))", 'sssssss', (string) $tanggal, (string) $unit, (string) $anggota, (string) $saldo_margin, (string) $userid, (string) $cif, (string) $cif) or die(mysql_error());

db_jalankan("Update  tagihan_cs set status_realisasi ='BAYAR'  where cif  =? and tgl_tagih=?", 'ss', (string) $checbox[$i], (string) $tanggal);

				}elseif($status_app=='MUSYAROKAH'){
				// Perbaikan 2026-11-03 (perintah user): jurnal & update loan mengikuti proses_eod_mob_debet_pelunasan.php
				// (kasus "pelunasan", MUSYAROKAH). Bedanya: no_anggota rek_loan diisi (EOD memakai variabel kosong),
				// nomor jurnal_umum otomatis seperti cabang lain di file ini, dan pembersihan pelunasan kelompok
				// (SOURCE_INCOME, temp_eod, blokir simpanan, tagihan_cs) sama dengan cabang lain.
				$ket2 = "Angsuran  an $nama $cif";
				// Perbaikan 2026-11-03 (perintah user): minggu yang sudah disetor (cicilan 1..run_tenor) tanpa omzet (porsi 0) dianggap porsi = nisbah, jadi tidak ikut dikembalikan
				$debetan = db_satu("SELECT sum(nisbah) as debet_ni, sum(CASE WHEN nisbah_anggota = 0 THEN nisbah ELSE nisbah_anggota END) as debet_omzet from pinjaman_detail_mus WHERE cif =? and cicilan between '1' and ?", 'ss', (string) $cif, (string) $run_tenor);
				$debet_ni = compat_num($debetan ? $debetan['debet_ni'] : null);
				$debet_omzet = compat_num($debetan ? $debetan['debet_omzet'] : null);
				if($debet_omzet==0){
					$debet_omzet = $debet_ni;
				}
				$kembali_nisbah = $debet_ni - $debet_omzet;
				$os_margin = $os; // = OS + nisbah + 2.500

				db_jalankan("insert into jurnal_umum(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values('',?,?,?)", 'sss', (string) $kode_trans, (string) $tanggal, (string) $unit);
				if($kembali_nisbah > 0){
					// nisbah yang terdebet melebihi bagian anggota dikembalikan ke simpanan
					// (EOD menaruh teks keterangan di REFF yang berupa nomor otomatis, sehingga teksnya hilang; kini di KET)
					db_jalankan("insert  into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,tgl_input,kode_transaksi)VALUES(?,?,?,?,?,'0','03',?,?,'Pengembalian Nisbah Pelunasan','',?,?,?)", 'ssssssssss',
						(string) $tanggal, (string) $anggota, (string) $unit, (string) $cif, (string) $code_kel, (string) $kembali_nisbah, (string) $userid, (string) $cao, (string) $tgl_input, (string) $kode_trans);
					db_jalankan("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
					values(?,?,'41063',?,'Bukti SYSTEM','Pengembalian Nisbah Pelunasan',?,?)", 'sssss', (string) $unit, (string) $kode_trans, (string) $tanggal, (string) $kembali_nisbah, (string) $userid);
					db_jalankan("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
					values(?,?,'2101000',?,'Bukti SYSTEM','Pengembalian Nisbah Pelunasan',?,?)", 'sssss', (string) $unit, (string) $kode_trans, (string) $tanggal, (string) $kembali_nisbah, (string) $userid);
					$baghas_rek = 0;
				}else{
					$baghas_rek = $saldo_margin;
				}
				db_jalankan("insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif,AO)VALUES('',?,?,?,'0',?,'03',?,?,'REALISASI POKOK',?,(select cao from loan where cif=? limit 1))", 'ssssssss',
					(string) $tanggal, (string) $unit, (string) $anggota, (string) $os, (string) $ket2, (string) $userid, (string) $cif, (string) $cif);
				db_jalankan("insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif,AO)VALUES('',?,?,?,'0',?,'03',?,?,'REALISASI BAGHAS',?,(select cao from loan where cif=? limit 1))", 'ssssssss',
					(string) $tanggal, (string) $unit, (string) $anggota, (string) $baghas_rek, (string) $ket2, (string) $userid, (string) $cif, (string) $cif);

				$uos = db_jalankan("Update  loan set os ='0',saldo_margin='0',run_tenor='0',ke='0',last_payment=? where cif  =?", 'ss', (string) $tanggal, (string) $cif);
				db_jalankan("delete from tunggakan where cif  =?", 's', (string) $cif);
				$t = db_jalankan("insert  into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,tgl_input,kode_transaksi)VALUES(?,?,?,?,?,?,'03','0',?,?,'',?,?,?)", 'sssssssssss',
					(string) $tanggal, (string) $anggota, (string) $unit, (string) $cif, (string) $code_kel, (string) $os_margin, (string) $userid, (string) $ket, (string) $cao, (string) $tgl_input, (string) $kode_trans);

				// debet simpanan OS + nisbah + 2.500 = kredit piutang musyarokah (OS) + pendapatan nisbah + titipan 2.500
				db_jalankan("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
				values(?,?,'2101000',?,'Bukti SYSTEM',?,?,?)", 'ssssss', (string) $unit, (string) $kode_trans, (string) $tanggal, (string) $ket2, (string) $os_margin, (string) $userid);
				db_jalankan("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
				values(?,?,'1472000',?,'Bukti SYSTEM',?,?,?)", 'ssssss', (string) $unit, (string) $kode_trans, (string) $tanggal, (string) $ket2, (string) $saldo_os, (string) $userid);
				db_jalankan("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
				values(?,?,'41062',?,'Bukti SYSTEM',?,?,?)", 'ssssss', (string) $unit, (string) $kode_trans, (string) $tanggal, (string) $ket2, (string) $ijaroh_mus, (string) $userid);
				db_jalankan("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
				values(?,?,'2867000',?,'Bukti SYSTEM',?,'2500',?)", 'sssss', (string) $unit, (string) $kode_trans, (string) $tanggal, (string) $ket2, (string) $userid);

				db_jalankan("update anggota set SOURCE_INCOME ='0' where cif=?", 's', (string) $cif);
				db_jalankan("update simpanan set type ='01', blok=' ' where cif=? and type ='02'", 's', (string) $cif);
				db_jalankan("Update  tagihan_cs set status_realisasi ='BAYAR'  where cif  =? and tgl_tagih=?", 'ss', (string) $checbox[$i], (string) $tanggal);

				}else{
					
					db_jalankan("insert into jurnal_umum(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values('',?,?,?)", 'sss', (string) $kode_trans, (string) $tanggal, (string) $unit);
					//Realisasi Piutang Margin	
					
				db_jalankan("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
				values(?,?,'2101000',?,'Bukti System',?,?,?)", 'ssssss', (string) $unit, (string) $kode_trans, (string) $tanggal, (string) $ket, (string) $os, (string) $userid);
	
				db_jalankan("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
				values(?,?,'1413000',?,'Bukti System',?,?,?)", 'ssssss', (string) $unit, (string) $kode_trans, (string) $tanggal, (string) $ket, (string) $os, (string) $userid);
								//Realisasi Piutang Margin	
				db_jalankan("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
							values(?,?,'1423000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_trans, (string) $tanggal, (string) $ket, (string) $saldo_margin, (string) $userid);
				//Realiasasi Murabahah Debit pada Piutang Murabahah total jumlah Pokok dan margin 
				if($deal_produk=='2'){
				$sisa_tenor = $tenor - $run_tenor;
				$sisa_margin = $sisa_tenor * 2500;
				$margin= $saldo_margin - $sisa_margin;
				if($margin <=0){
				$margin =$saldo_margin;
				$sisa_margin =0;

				}else{
				$margin = $margin;
				$sisa_margin=$sisa_margin;
				
				}
				db_jalankan("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
				values(?,?,'41002',?,'Bukti System',?,?,?)", 'ssssss', (string) $unit, (string) $kode_trans, (string) $tanggal, (string) $ket, (string) $margin, (string) $userid);
				db_jalankan("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
				values(?,?,'2867000',?,'Bukti System',?,?,?)", 'ssssss', (string) $unit, (string) $kode_trans, (string) $tanggal, (string) $ket, (string) $sisa_margin, (string) $userid);

 
				}
				else{
				db_jalankan("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
				values(?,?,'41002',?,'Bukti System',?,?,?)", 'ssssss', (string) $unit, (string) $kode_trans, (string) $tanggal, (string) $ket, (string) $saldo_margin, (string) $userid);
				}
				
				
				$uos = db_jalankan("Update loan set os =os - ?,saldo_margin='0',run_tenor='0',ke='1',status='LUNAS' where cif=?", 'ss', (string) $os, (string) $cif);
				$hapus = db_jalankan("delete from tunggakan where cif=?", 's', (string) $cif);

				$sc = db_jalankan("update anggota set SOURCE_INCOME ='0' where cif=?", 's', (string) $cif);

				$sc = db_jalankan("update simpanan set type ='01', blok=' ' where cif=? and type ='02'", 's', (string) $cif);


				db_jalankan("update pinjaman_detail set jumlah_bayar=?,tgl_bayar=? where id_pinjam=?", 'sss', (string) $angsuran, (string) $tanggal, (string) $anggota);
				db_jalankan("delete from tunggakan where cif=?", 's', (string) $cif);
				$t = db_jalankan("insert into simpanan(BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,blok,tgl_input,kode_transaksi) 
				VALUES (?,?,?,?,?,?,'03','0',?,?,'',?,'0',?,?)", 'sssssssssss', (string) $tanggal, (string) $anggota, (string) $unit, (string) $cif, (string) $code_kel, (string) $os, (string) $userid, (string) $ket, (string) $cao, (string) $date, (string) $kode_trans);
db_jalankan("insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif,AO)VALUES('',?,?,?,'0',?,'03','Pelunasan',?,'REALISASI POKOK',?,(select cao from loan where cif=? limit 1))", 'sssssss', (string) $tanggal, (string) $unit, (string) $anggota, (string) $pokok, (string) $userid, (string) $cif, (string) $cif) or die(mysql_error());
				db_jalankan("insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif,AO)VALUES('',?,?,?,'0',?,'03','Pelunasan',?,'REALISASI BAGHAS',?,(select cao from loan where cif=? limit 1))", 'sssssss', (string) $tanggal, (string) $unit, (string) $anggota, (string) $saldo_margin, (string) $userid, (string) $cif, (string) $cif) or die(mysql_error());

db_jalankan("Update  tagihan_cs set status_realisasi ='BAYAR'  where cif  =? and tgl_tagih=?", 'ss', (string) $checbox[$i], (string) $tanggal);

}



if (db_jumlah_gagal() > $gagal_awal) {
	mysqli_rollback($koneksi_transaksi);
	notif_pesan("GAGAL Pelunasan an $cif, data tidak disimpan");
	continue;
}
mysqli_commit($koneksi_transaksi);
notif_pesan("Pelunasa an $cif Suksess");
		
		
		
		
		
		
		
		
		
		
		}
		
		
	

            echo "<script language='javascript'>document.location.href='pelunasan_kel.php'</script>";
	}	
	
}else {
			notif_pergi('anda belum memilih data.', 'pelunasan_kel.php');
	}

?>
