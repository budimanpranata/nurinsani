<?php require_once __DIR__ . '/koneksi_db.php';
session_start();
ob_start();
$userid=$_SESSION['userid'];
$tanggal=$_SESSION['stgl_system'];
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
include "conn2.php";
include "function.php";
$koneksi=open_connection2();
$anggota=$_GET['anggota'];
$date= date("Y-m-d");

// Perbaikan 2026-10-30 (perintah user): versi lama memakai "select count(no_anggota) from loan" tanpa filter,
// yang selalu menghasilkan satu baris, sehingga nomor anggota yang tidak ada / sudah lunas tetap diproses.
$cek_loan = db_satu("select cif, os, status from loan where no_anggota = ?", 's', (string) $anggota);
$tujuan_kembali = 'pelunasan.php';
if (!$cek_loan) {
	echo '<script>alert(' . json_encode("Maaf...,No Anggota $anggota tidak ditemukan, Input no anggota yang benar !!!") . ');</script>';
	echo "<script language='javascript'>document.location.href='$tujuan_kembali'</script>";
	exit;
}
if ($cek_loan['status'] == 'LUNAS' || compat_num($cek_loan['os']) <= 0) {
	echo '<script>alert(' . json_encode("Maaf...,Pembiayaan No Anggota $anggota sudah lunas") . ');</script>';
	echo "<script language='javascript'>document.location.href='$tujuan_kembali'</script>";
	exit;
}
if ((string) $cek_loan['cif'] === '') {
	echo '<script>alert(' . json_encode("Maaf...,CIF untuk No Anggota $anggota kosong, pelunasan dibatalkan") . ');</script>';
	echo "<script language='javascript'>document.location.href='$tujuan_kembali'</script>";
	exit;
}
// semua penulisan dalam satu transaksi: dibatalkan bila ada query yang gagal
// (catatan: jurnal_umum dan rek_loan masih MyISAM sehingga tidak ikut dibatalkan)
$koneksi_transaksi = db_ni();
mysqli_begin_transaction($koneksi_transaksi);
$gagal_awal = db_jumlah_gagal();
{ // isi proses pelunasan (dulu cabang else dari cek awal)

		$param = db_semua("select anggota.cif,loan.code_kel,loan.unit,angsuran,loan.Cust_Short_name,loan.cif,os,loan.cao,saldo_margin,status_app,ijaroh from loan 
		left join anggota on anggota.cif=loan.cif
		where loan.no_anggota =? group by no_anggota", 's', (string) $anggota);
		$data = $param ? $param[0] : false;
		$os=$data['os'];
		$saldo_margin=$data['saldo_margin'];
		$pokok=$os-$saldo_margin;
		$unit=$data ['unit'];
		$ijaroh_mus=$data ['ijaroh'];
		$code_kel=$data['code_kel'];
		$cao=$data['cao'];
		$cif=$data['cif'];
		$ciff=$data['cif'];
		$angsuran=$data['angsuran'];
		$nama=$data['Cust_Short_name']; // dikirim sebagai parameter, tidak perlu di-escape
		$ket ="Pelunasan $nama $cif";
		$status_app=$data['status_app'];
		$ket2="Pelunasan MUsyarokah $nama $cif";
$norek =$anggota;
	
		$cek_saldo = db_semua("SELECT SUM(KREDIT - DEBIT) AS saldo from simpanan where cif =? and TYPE not in ('04','05','07')", 's', (string) $cif);
		$data_saldo = $cek_saldo ? $cek_saldo[0] : false;
		$saldo=$data_saldo['saldo'];
		if($status_app=='REST'){
		$os = $os + $ijaroh_mus + 2500;

		}else{
		$os = $os ;

		}
			

		if ($saldo < $os ){
			echo "<script> alert ('Maaf Saldo Pelunasan Lebih Kecil dari Sisa Pembiayaan')</script>";
			echo "<script language='javascript'>document.location.href='pelunasan.php'</script>";
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

				$uos = db_jalankan("Update loan set os =os - ?,saldo_margin='0',ke='1',status='LUNAS',Tenor ='25',status_app ='MURAB' where no_anggota=?", 'ss', (string) $os, (string) $anggota);
				$hapus = db_jalankan("delete from tunggakan where cif=?", 's', (string) $cif);
				$hapus = db_jalankan("delete from temp_eod where cif=?", 's', (string) $cif);

				$sc = db_jalankan("update simpanan set type ='01', blok=' ' where cif=? and type ='02'", 's', (string) $cif);


				db_jalankan("update pinjaman_detail set jumlah_bayar=?,tgl_bayar=? where id_pinjam=?", 'sss', (string) $angsuran, (string) $tanggal, (string) $anggota);
				
				$t = db_jalankan("insert into simpanan(BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,blok,tgl_input,kode_transaksi) 
				VALUES (?,?,?,?,?,?,'03','0',?,?,'',?,'0',?,?)", 'sssssssssss', (string) $tanggal, (string) $anggota, (string) $unit, (string) $cif, (string) $code_kel, (string) $os, (string) $userid, (string) $ket, (string) $cao, (string) $date, (string) $kode_trans);
				db_jalankan("insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif)VALUES('',?,?,?,'0',?,'03','Pelunasan',?,'REALISASI POKOK',?)", 'ssssss', (string) $tanggal, (string) $unit, (string) $anggota, (string) $os, (string) $userid, (string) $cif) or die(mysql_error());

				

				}elseif($status_app=='MUSYAROKAH'){

				$cek_total_debetan=db_satu("SELECT sum(nisbah) as debet_ni, sum(nisbah_anggota) as debet_omzet  from pinjaman_detail_mus WHERE cif =? and cicilan =?", 'ss', (string) $ciff, (string) $run_tenor);
        			$debet_ni=$cek_total_debetan['debet_ni'];
                    $debet_omzet = $cek_total_debetan['debet_omzet'];

					$debet_ni = $iharoh_mus * $run_tenor;
                    $kembali_nisbah = $debet_ni - $debet_omzet;
					$os_margin = $os + $ijaroh_mus + 2500;

                    if($kembali_nisbah > 0 ){
                         $jurnal_umum=array_values((array) db_satu("SELECT max(nomor_jurnal) FROM jurnal_umum where unit =? ORDER BY tanggal_selesai DESC", 's', (string) $unit));
                    $nomor_jurnal=$jurnal_umum[0]+1;

                        db_jalankan("insert  into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,tgl_input,kode_transaksi)VALUES(?,?,?,?,?,'0','03',?,?,'Pengembalian Nisbah Pelunasan',?,?,?,?) ", 'sssssssssss', (string) $tanggal, (string) $norek, (string) $unit, (string) $ciff, (string) $code_kel, (string) $kembali_nisbah, (string) $userid, (string) $ket2, (string) $cao, (string) $tgl_input, (string) $kode_trans);
                        
                        db_jalankan("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                        values(?,?,'2101000',?,'Bukti SYSTEM','Pengembalian Nisbah Pelunasan',?,?)", 'sssss', (string) $unit, (string) $kode_trans, (string) $tanggal, (string) $kembali_nisbah, (string) $userid);
                        db_jalankan("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                                values(?,?,'41063',?,'Bukti SYSTEM','Pengembalian Nisbah Pelunasan',?,?)", 'sssss', (string) $unit, (string) $kode_trans, (string) $tanggal, (string) $kembali_nisbah, (string) $userid);
                          db_jalankan("insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif)VALUES('',?,?,?,'0',?,'03',?,?,'REALISASI POKOK',?)", 'sssssss', (string) $tanggal, (string) $unit, (string) $no_anggota, (string) $os, (string) $ket2, (string) $userid, (string) $ciff);
                            db_jalankan("insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif)VALUES('',?,?,?,'0',?,'03',?,?,'REALISASI BAGHAS',?)", 'sssssss', (string) $tanggal, (string) $unit, (string) $no_anggota, (string) $saldo_margin, (string) $ket2, (string) $userid, (string) $ciff);
                             
                            $uos = db_jalankan("Update  loan set os ='0',saldo_margin='0',run_tenor='0',ke='0',last_payment=? where cif  =?", 'ss', (string) $tanggal, (string) $ciff);
                            $t= db_jalankan("delete from tunggakan where cif  =?", 's', (string) $ciff);
							db_jalankan("delete from pinjaman_detail_mus where cif  =?", 's', (string) $ciff);

                            db_jalankan("insert  into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,tgl_input,kode_transaksi)VALUES(?,?,?,?,?,?,'03','0',?,?,?,?,?) ", 'sssssssssss', (string) $tanggal, (string) $norek, (string) $unit, (string) $ciff, (string) $code_kel, (string) $os_margin, (string) $userid, (string) $ket2, (string) $cao, (string) $tgl_input, (string) $kode_trans);
                            db_jalankan("insert into jurnal_umum(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values(?,?,?,?)", 'ssss', (string) $nomor_jurnal, (string) $kode_trans, (string) $tanggal, (string) $unit);
							 
							db_jalankan("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                                values(?,?,'2101000',?,'Bukti SYSTEM',?,?,?)", 'ssssss', (string) $unit, (string) $kode_trans, (string) $tanggal, (string) $ket2, (string) $os_margin, (string) $userid);

                            db_jalankan("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                                values(?,?,'1472000',?,'Bukti SYSTEM',?,?,?)", 'ssssss', (string) $unit, (string) $kode_trans, (string) $tanggal, (string) $ket2, (string) $os, (string) $userid);

							db_jalankan("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                                values(?,?,'41062',?,'Bukti SYSTEM',?,?,?)", 'ssssss', (string) $unit, (string) $kode_trans, (string) $tanggal, (string) $ket2, (string) $ijaroh_mus, (string) $userid);
							db_jalankan("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                                values(?,?,'2867000',?,'Bukti SYSTEM',?,'2500',?)", 'sssss', (string) $unit, (string) $kode_trans, (string) $tanggal, (string) $ket2, (string) $userid);
                          
                           


                    }        
                    else{

                          $jurnal_umum=array_values((array) db_satu("SELECT max(nomor_jurnal) FROM jurnal_umum where unit =? ORDER BY tanggal_selesai DESC", 's', (string) $unit));
                    $nomor_jurnal=$jurnal_umum[0]+1;
                      db_jalankan("insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif)VALUES('',?,?,?,'0',?,'03',?,?,'REALISASI POKOK',?)", 'sssssss', (string) $tanggal, (string) $unit, (string) $no_anggota, (string) $os, (string) $ket2, (string) $userid, (string) $ciff);
                            db_jalankan("insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif)VALUES('',?,?,?,'0',?,'03',?,?,'REALISASI BAGHAS',?)", 'sssssss', (string) $tanggal, (string) $unit, (string) $no_anggota, (string) $saldo_margin, (string) $ket2, (string) $userid, (string) $ciff);
                             
                            $uos=db_jalankan("Update  loan set os ='0',saldo_margin='0',run_tenor='0',ke='0',last_payment=? where cif  =?", 'ss', (string) $tanggal, (string) $ciff);
                            $t=db_jalankan("delete from tunggakan where cif  =?", 's', (string) $ciff);
							db_jalankan("delete from pinjaman_detail_mus where cif  =?", 's', (string) $ciff);
                            db_jalankan("insert  into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,tgl_input,kode_transaksi)VALUES(?,?,?,?,?,?,'03','0',?,?,?,?,?) ", 'sssssssssss', (string) $tanggal, (string) $norek, (string) $unit, (string) $ciff, (string) $code_kel, (string) $os_margin, (string) $userid, (string) $ket2, (string) $cao, (string) $tgl_input, (string) $kode_trans);
                            db_jalankan("insert into jurnal_umum(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values(?,?,?,?)", 'ssss', (string) $nomor_jurnal, (string) $kode_tran, (string) $tanggal, (string) $unit);
             
                          	db_jalankan("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                                values(?,?,'2101000',?,'Bukti SYSTEM',?,?,?)", 'ssssss', (string) $unit, (string) $kode_trans, (string) $tanggal, (string) $ket2, (string) $os_margin, (string) $userid);

                            db_jalankan("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                                values(?,?,'1472000',?,'Bukti SYSTEM',?,?,?)", 'ssssss', (string) $unit, (string) $kode_trans, (string) $tanggal, (string) $ket2, (string) $os, (string) $userid);

							db_jalankan("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                                values(?,?,'41062',?,'Bukti SYSTEM',?,?,?)", 'ssssss', (string) $unit, (string) $kode_trans, (string) $tanggal, (string) $ket2, (string) $ijaroh_mus, (string) $userid);
							db_jalankan("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                                values(?,?,'2867000',?,'Bukti SYSTEM',?,'2500',?)", 'sssss', (string) $unit, (string) $kode_trans, (string) $tanggal, (string) $ket2, (string) $userid);
                          


                    }        
                     


					
				
				}else{
					
					db_jalankan("insert into jurnal_umum(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values('',?,?,?)", 'sss', (string) $kode_trans, (string) $tanggal, (string) $unit);
					//Realisasi Piutang Margin	
					
				db_jalankan("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
				values(?,?,'2101000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_trans, (string) $tanggal, (string) $ket, (string) $os, (string) $userid);
	
				db_jalankan("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
				values(?,?,'1413000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_trans, (string) $tanggal, (string) $ket, (string) $os, (string) $userid);
								//Realisasi Piutang Margin	
				db_jalankan("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
							values(?,?,'1423000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_trans, (string) $tanggal, (string) $ket, (string) $saldo_margin, (string) $userid);
				//Realiasasi Murabahah Debit pada Piutang Murabahah total jumlah Pokok dan margin 
				db_jalankan("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
				values(?,?,'41002',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_trans, (string) $tanggal, (string) $ket, (string) $saldo_margin, (string) $userid);
				
				$uos = db_jalankan("Update loan set os =os - ?,saldo_margin='0',run_tenor='0',ke='1',status='LUNAS' where no_anggota=?", 'ss', (string) $os, (string) $anggota);
				$hapus = db_jalankan("delete from tunggakan where cif=?", 's', (string) $cif);

				$hapus = db_jalankan("delete from temp_eod where cif=?", 's', (string) $cif);
				$sc = db_jalankan("update simpanan set type ='01', blok=' ' where cif=? and type ='02'", 's', (string) $cif);


				db_jalankan("update pinjaman_detail set jumlah_bayar=?,tgl_bayar=? where id_pinjam=?", 'sss', (string) $angsuran, (string) $tanggal, (string) $anggota);
				db_jalankan("delete from tunggakan where cif=?", 's', (string) $cif);
				$t = db_jalankan("insert into simpanan(BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,blok,tgl_input,kode_transaksi) 
				VALUES (?,?,?,?,?,?,'03','0',?,?,'',?,'0',?,?)", 'sssssssssss', (string) $tanggal, (string) $anggota, (string) $unit, (string) $cif, (string) $code_kel, (string) $os, (string) $userid, (string) $ket, (string) $cao, (string) $date, (string) $kode_trans);
db_jalankan("insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif)VALUES('',?,?,?,'0',?,'03','Pelunasan',?,'REALISASI POKOK',?)", 'ssssss', (string) $tanggal, (string) $unit, (string) $anggota, (string) $pokok, (string) $userid, (string) $cif) or die(mysql_error());
				db_jalankan("insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif)VALUES('',?,?,?,'0',?,'03','Pelunasan',?,'REALISASI BAGHAS',?)", 'ssssss', (string) $tanggal, (string) $unit, (string) $anggota, (string) $saldo_margin, (string) $userid, (string) $cif) or die(mysql_error());

}


					if (db_jumlah_gagal() > $gagal_awal) {
						mysqli_rollback($koneksi_transaksi);
						echo '<script>alert(' . json_encode("GAGAL Pelunasan Nomor Anggota $anggota, data tidak disimpan") . ');</script>';
						echo "<script language='javascript'>document.location.href='$tujuan_kembali'</script>";
						exit;
					}
					mysqli_commit($koneksi_transaksi);
					if(($t)||($uos)){ 
	
						echo "<script> alert ('Pelunasan Nomor Anggota $anggota sukses ')</script>";

						echo "<script language='javascript'>document.location.href='pelunasan.php'</script>";
						}else {
		
							echo "<script> alert ('GAGAL Pelunasan Nomor Anggota $anggota')</script>";

							echo "<script language='javascript'>document.location.href='pelunasan.php'</script>";
							}
			}
}
?>
