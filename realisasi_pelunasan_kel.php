<?php require_once __DIR__ . '/koneksi_db.php'; 
session_start();
ob_start();
$userid=$_SESSION['userid'];
$tgl_system=$_SESSION['stgl_system'];
$tgl_input=date("Y-m-d");
$buss_date=$tgl_system;
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

$tanggal=$tgl_system;
$simpan=$_SESSION['simpans'];	
$baki=$_SESSION['bakis'];	
$nom=isset($_POST['bulat']) ? $_POST['bulat'] : null;			
			
$checbox = isset($_POST['cekbox']) ? $_POST['cekbox'] : null;
if ($checbox) {
	for ($i=0; $i <sizeof($checbox);$i++){
		//$query="insert into data (name,alamat) values ('".$checbox[$i]."','".$nom[$i]."')";
		$kel = db_semua("select no_anggota,anggota.cif,loan.code_kel,loan.unit,angsuran,loan.Cust_Short_name,loan.cif,os,loan.cao,saldo_margin,status_app,deal_produk,Tenor,run_tenor FROM loan 
		left join anggota on anggota.cif=loan.cif
		where  loan.cif =?", 's', (string) $checbox[$i]);
		$data = $kel ? $kel[0] : false;

		// Perbaikan 2026-10-30 (perintah user): pengaman yang sama dengan lunasi.php, per anggota.
		// Anggota yang tidak lolos dilewati (alert) dan anggota berikutnya tetap diproses.
		$cek_loan = db_satu("select cif, os, status from loan where cif = ?", 's', (string) $checbox[$i]);
		if (!$cek_loan) {
			echo '<script>alert(' . json_encode('Maaf...,CIF ' . $checbox[$i] . ' tidak ditemukan') . ');</script>';
			continue;
		}
		if ($cek_loan['status'] == 'LUNAS' || compat_num($cek_loan['os']) <= 0) {
			echo '<script>alert(' . json_encode('Maaf...,Pembiayaan CIF ' . $checbox[$i] . ' sudah lunas') . ');</script>';
			continue;
		}
		if ((string) $cek_loan['cif'] === '') {
			echo '<script>alert(' . json_encode('Maaf...,CIF kosong, pelunasan dibatalkan') . ');</script>';
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

 $kodetr=7;
            $kodetr=acakangkahuruf($kodetr);
            $kode_trans="BU/".$unit.$kodetr;
		
	


if ($saldo < $os ){
			echo "<script> alert ('Maaf Saldo an $cif " . addslashes($nama) . " Pelunasan Lebih Kecil dari Sisa Pembiayaan')</script>"; // addslashes: dulu nama sudah di-escape
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
				$hapus = db_jalankan("delete from temp_eod where cif=?", 's', (string) $cif);

				$sc = db_jalankan("update simpanan set type ='01', blok=' ' where cif=? and type ='02'", 's', (string) $cif);


				db_jalankan("update pinjaman_detail set jumlah_bayar=?,tgl_bayar=? where id_pinjam=?", 'sss', (string) $angsuran, (string) $tanggal, (string) $anggota);
				
				$t = db_jalankan("insert into simpanan(BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,blok,tgl_input,kode_transaksi) 
				VALUES (?,?,?,?,?,?,'03','0',?,?,'',?,'0',?,?)", 'sssssssssss', (string) $tanggal, (string) $anggota, (string) $unit, (string) $cif, (string) $code_kel, (string) $os, (string) $userid, (string) $ket, (string) $cao, (string) $date, (string) $kode_trans);
db_jalankan("insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif)VALUES('',?,?,?,'0',?,'03','Pelunasan',?,'REALISASI POKOK',?)", 'ssssss', (string) $tanggal, (string) $unit, (string) $anggota, (string) $os, (string) $userid, (string) $cif) or die(mysql_error());
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
				$hapus = db_jalankan("delete from temp_eod where cif=?", 's', (string) $cif);

				$sc = db_jalankan("update simpanan set type ='01', blok=' ' where cif=? and type ='02'", 's', (string) $cif);


				db_jalankan("update pinjaman_detail set jumlah_bayar=?,tgl_bayar=? where id_pinjam=?", 'sss', (string) $angsuran, (string) $tanggal, (string) $anggota);
				db_jalankan("delete from tunggakan where cif=?", 's', (string) $cif);
				$t = db_jalankan("insert into simpanan(BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,blok,tgl_input,kode_transaksi) 
				VALUES (?,?,?,?,?,?,'03','0',?,?,'',?,'0',?,?)", 'sssssssssss', (string) $tanggal, (string) $anggota, (string) $unit, (string) $cif, (string) $code_kel, (string) $os, (string) $userid, (string) $ket, (string) $cao, (string) $date, (string) $kode_trans);
db_jalankan("insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif)VALUES('',?,?,?,'0',?,'03','Pelunasan',?,'REALISASI POKOK',?)", 'ssssss', (string) $tanggal, (string) $unit, (string) $anggota, (string) $pokok, (string) $userid, (string) $cif) or die(mysql_error());
				db_jalankan("insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif)VALUES('',?,?,?,'0',?,'03','Pelunasan',?,'REALISASI BAGHAS',?)", 'ssssss', (string) $tanggal, (string) $unit, (string) $anggota, (string) $saldo_margin, (string) $userid, (string) $cif) or die(mysql_error());

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
				$hapus = db_jalankan("delete from temp_eod where cif=?", 's', (string) $cif);

				$sc = db_jalankan("update simpanan set type ='01', blok=' ' where cif=? and type ='02'", 's', (string) $cif);


				db_jalankan("update pinjaman_detail set jumlah_bayar=?,tgl_bayar=? where id_pinjam=?", 'sss', (string) $angsuran, (string) $tanggal, (string) $anggota);
				db_jalankan("delete from tunggakan where cif=?", 's', (string) $cif);
				$t = db_jalankan("insert into simpanan(BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,blok,tgl_input,kode_transaksi) 
				VALUES (?,?,?,?,?,?,'03','0',?,?,'',?,'0',?,?)", 'sssssssssss', (string) $tanggal, (string) $anggota, (string) $unit, (string) $cif, (string) $code_kel, (string) $os, (string) $userid, (string) $ket, (string) $cao, (string) $date, (string) $kode_trans);
db_jalankan("insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif)VALUES('',?,?,?,'0',?,'03','Pelunasan',?,'REALISASI POKOK',?)", 'ssssss', (string) $tanggal, (string) $unit, (string) $anggota, (string) $pokok, (string) $userid, (string) $cif) or die(mysql_error());
				db_jalankan("insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif)VALUES('',?,?,?,'0',?,'03','Pelunasan',?,'REALISASI BAGHAS',?)", 'ssssss', (string) $tanggal, (string) $unit, (string) $anggota, (string) $saldo_margin, (string) $userid, (string) $cif) or die(mysql_error());

db_jalankan("Update  tagihan_cs set status_realisasi ='BAYAR'  where cif  =? and tgl_tagih=?", 'ss', (string) $checbox[$i], (string) $tanggal);

}



if (db_jumlah_gagal() > $gagal_awal) {
	mysqli_rollback($koneksi_transaksi);
	echo '<script>alert(' . json_encode("GAGAL Pelunasan an $cif, data tidak disimpan") . ');</script>';
	continue;
}
mysqli_commit($koneksi_transaksi);
echo "<script>alert('Pelunasa an $cif Suksess');</script>";
		
		
		
		
		
		
		
		
		
		
		}
		
		
	

            echo "<script language='javascript'>document.location.href='pelunasan_kel.php'</script>";
	}	
	
}else {
			echo '<script>alert("anda belum memilih data."); window.location="pelunasan_kel.php"</script>';
	}

?>
