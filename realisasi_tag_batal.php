<?php require_once __DIR__ . '/koneksi_db.php'; 
session_start();
ob_start();
$userid=$_SESSION['userid'];
//cek apakah user sudah login
if(!isset($_SESSION['userid'])){
   header("location:index.html");
}

//cek level user
if($_SESSION['level']!="unit"){
   header("location:index.html");
}
db_ni();
mysql_select_db('nur_insani');
include "function.php";

$tanggal=date("Y-m-d");
			
			
$cekbox = $_POST['cekbox'];
if ($cekbox) {
       foreach ($cekbox as $value) {
		   		$row_sal= mysql_fetch_array(mysql_query("select(sum(KREDIT) - sum(DEBIT))as saldo from simpanan WHERE TYPE='01' AND NOREK ='" . $value . "'")) or die(mysql_error());
				$saldo=$row_sal['saldo'];
		$select = mysql_query("select DATE_ADD(next_schedule,INTERVAL 7 DAY) as next_schedule,unit,code_kel,no_anggota,cif,ijaroh,Plafond,pokok,os,angsuran,bulat,TIMESTAMPDIFF(WEEK,tgl_murab,next_schedule) AS RUN_TENOR1,run_tenor,TIMESTAMPDIFF(WEEK,tgl_murab,CURDATE()) AS ke1,cao,ke as next  from loan  WHERE no_anggota ='" . $value . "'");
			while($row = mysql_fetch_array($select)){
				$next_schedule1=$row['next_schedule'];
				$no_anggota=$row['no_anggota'];
				$pokok=$row['pokok'];
				$os=$row['os'];
				$bulat=$row['bulat'];
				$run_tenor=$row['run_tenor'];
				$pb=$run_tenor - 1;
				$next_tenor=$run_tenor + 2;
				$unit=$row['unit'];
				$angsuran=$row['angsuran'];
				$ijaroh=$row['ijaroh'];
				$code_kel=$row['code_kel'];
				$ke=$row['ke1'];
				$cif=$row['cif'];
				$cao=$row['cao'];
				$ke_tenor=$row['next'];
				$ket="Batal Realisasi Tagihan $no_anggota $tanggal";
				$ket2="Setoran Tagihan $no_anggota $tanggal";
				$ket3="Tunggakan Tagihan $no_anggota $next_schedule";
				$kode_tr=5;
				$kode_tr=acakangkahuruf($kode_tr);	  
				$kode_transaksi="BU/".$unit .$kode_tr;
				$wajib_kh=$bulat - $angsuran;
				$cek_next= mysql_fetch_array(mysql_query("select cicilan,tgl_jatuh_tempo from pinjaman_detail WHERE cicilan='$next_tenor' AND id_pinjam ='" . $value . "'"));
				$next_schedule=$cek_next['tgl_jatuh_tempo'];
				$next_cicilan=$cek_next['cicilan'];
				$batas=25;
				
			
		
				mysql_query("insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif)VALUES('','$tanggal','$unit','$no_anggota','$pokok','0','03','$ket','$userid','BATAL REALISASI POKOK','$cif')") or die(mysql_error("GAGAL INPUT"));
				mysql_query("insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif)VALUES('','$tanggal','$unit','$no_anggota','$ijaroh','0','03','$ket','$userid','BATAL REALISASI BAGHAS','$cif')") or die(mysql_error("GAGAL INPUT"));

				mysql_query("Update  loan set next_schedule ='$next_schedule',os =os+'$angsuran',saldo_margin=saldo_margin+'$ijaroh',run_tenor=run_tenor - 1,last_payment='$tanggal' where no_anggota  ='" . $value . "'") or die(mysql_error("GAGAL INPUT"));
				mysql_query("insert  into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO)VALUES('$tanggal','$no_anggota','$unit','$cif','$code_kel','0','03','$ijaroh','$userid','BATAL REALISASI TAGIHAN','$cao') ") or die(mysql_error("GAGAL INPUT"));
				mysql_query("Update  tabungan_detail set saldo =(select(sum(KREDIT)-SUM(DEBIT)) as saldo from simpanan where NOREK='" . $value . "') where no_anggota  ='" . $value . "'") or die(mysql_error("GAGAL INPUT"));
				mysql_query("Update  pinjaman_detail set jumlah_bayar ='0',tgl_bayar='$tanggal' where id_pinjam  ='" . $value . "' and cicilan='$pb'") or die(mysql_error("GAGAL INPUT"));
				mysql_query("insert  into tagihan (id_reff,code_kel,no_anggota,bagi_hasil,pokok,tagihan,bulat,tagihan_ke,sisa_os,tgl_realisasi,status)VALUES('','$code_kel','$no_anggota','$ijaroh','$pokok','$angsuran','$bulat','$ke','$os','$next_schedule','RENCANA') ") or die(mysql_error("GAGAL INPUT"));
				$jurnal_umum=mysql_fetch_array(mysql_query("SELECT max(nomor_jurnal) FROM jurnal_umum where unit ='$unit' ORDER BY tanggal_selesai DESC"));
				$nomor_jurnal=$jurnal_umum[0]+1;
				//pencatatan du jurnal realasisi Piutang Murabahaha total jumlah Pokok dan margin
				//mysql_query("insert into jurnal_masuk(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values('$nomor_jurnal','$kode_transaksi','$tanggal','$unit')");
				//mysql_query("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
				//values('$unit','$kode_transaksi','1120000','$tanggal','KAS MASUK','$ket','$bulat','$userid')");
				mysql_query("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
				values('$unit','$kode_transaksi','1413000','$tanggal','Bukti Umum','Koreksi Pendapatan Margin Yang di Tangguhkan','$angsuran','$userid')");
				$kodet=5;
				$kodet=acakangkahuruf($kodet);
				$kode_tran="BU/".$unit .$kodet;
				mysql_query("insert into jurnal_umum(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values('$nomor_jurnal','$kode_trans','$tanggal','$unit')");
				//Realisasi Piutang Margin	
				mysql_query("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
							values('$unit','$kode_tran','1423000','$tanggal','Bukti Umum','$ket','$ijaroh','$userid')");
				//Realiasasi Murabahah Debit pada Piutang Murabahaha total jumlah Pokok dan margin 
				mysql_query("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
				values('$unit','$kode_tran','41002','$tanggal','Bukti Umum','Koreksi Pendapatan Margin','$ijaroh','$userid')");
				//mysql_query("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
				//values('$unit','$kode_transaksi','3102000','$tanggal','Bukti Umum','Simpanan Pokok','$wajib_kh','$userid')");


				}
			}
			echo '<script>alert("Data sudah direalisasikan."); window.location="yrs_list.php"</script>';
			
		
	}else {
			echo '<script>alert("anda belum memilih data."); window.location="yrs_list.php"</script>';
	}

?>
