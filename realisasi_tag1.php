<?php require_once __DIR__ . '/koneksi_db.php'; 
session_start();
ob_start();
$userid=$_SESSION['userid'];
//cek apakah user sudah login
$tgl_system=$_SESSION['stgl_system'];
//cek apakah user sudah login
if(!isset($_SESSION['stgl_system'])){
echo "<script>alert('Maaf,.tanggal system belum di setup');</script>";

   header("location:param_tgl.php");
 exit; }
if(!isset($_SESSION['userid'])){
   header("location:index.html");
 exit; }

//cek level user
if($_SESSION['level']!="unit"){
   header("location:index.html");
 exit; }
db_ni();
mysql_select_db('nur_insani');
include "function.php";

$tanggal=$tgl_system;

$tgl_input=date("Y-m-d");

			
			
$cekbox = $_POST['cekbox'];
if ($cekbox) {
       foreach ($cekbox as $value) {
		   		
		$select = mysql_query("select DATE_ADD(next_schedule,INTERVAL 7 DAY) as next_schedule,unit,code_kel,no_anggota,CUST_SHORT_NAME,cif,ijaroh,Plafond,pokok,os,angsuran,bulat,TIMESTAMPDIFF(WEEK,tgl_murab,next_schedule) AS RUN_TENOR1,run_tenor,TIMESTAMPDIFF(WEEK,tgl_murab,CURDATE()) AS ke1,cao,ke as next,Tenor  from loan  WHERE no_anggota ='" . $value . "'");
			while($row = mysql_fetch_array($select)){
				$next_schedule1=$row['next_schedule'];
				$no_anggota=$row['no_anggota'];
				$pokok=$row['pokok'];
				$os=$row['os'];
				$bulat=$row['bulat'];
				$run_tenor=$row['run_tenor'];
				$pb=$run_tenor + 1;
				$next_tenor=$run_tenor + 2;
				$unit=$row['unit'];
				$angsuran=$row['angsuran'];
				$ijaroh=$row['ijaroh'];
				$code_kel=$row['code_kel'];
				$ke=$row['ke1'];
				$cif=$row['cif'];
				$cao=$row['cao'];
				$tenor=$row['Tenor'];
				$ke_tenor=$row['next'];
$nama=mysql_real_escape_string ($row['CUST_SHORT_NAME']);
				$ket="Setor Tagihan $nama";
				$ket2="Setoran Tagihan $nama";
				$ket3="Tunggakan Tagihan $nama";
				
				$kode_tr=5;
				$kode_tr=acakangkahuruf($kode_tr);	  
				$kode_transaksi="BU/".$unit .$kode_tr;
				$wajib_kh=$bulat - $angsuran;
				$cek_next= mysql_fetch_array(mysql_query("select cicilan,tgl_jatuh_tempo from pinjaman_detail WHERE cicilan='$next_tenor' AND id_pinjam ='" . $value . "'"));
				$next_schedule=$cek_next['tgl_jatuh_tempo'];
				$next_cicilan=$cek_next['cicilan'];
				$row_sal= mysql_fetch_array(mysql_query("select(sum(KREDIT) - sum(DEBIT))as saldo from simpanan WHERE cif ='$cif'")) or die(mysql_error());
				$sisa=$row_sal['saldo'];
				$row_sal= mysql_fetch_array(mysql_query("select(sum(KREDIT) - sum(DEBIT))as blok from simpanan WHERE type in ('05','02','04')and cif ='$cif'")) or die(mysql_error());
				$blok=$row_sal['blok'];		
$saldo=$sisa-$blok;
$urut = $ke_tenor + 1;		
				
if ($saldo < $angsuran){	
				mysql_query("Update loan set next_schedule='$next_schedule1',ke='$urut' where no_anggota  ='" . $value . "'") or die(mysql_error("GAGAL INPUT"));
				$ke_n= mysql_fetch_array(mysql_query("select ke from loan WHERE cif ='$cif'")) or die(mysql_error());
				$ke_lagi=$ke_n['ke'];
				
				mysql_query("insert  into tunggakan (tgl_tunggak,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,blok)VALUES('$tanggal','$no_anggota','$unit','$cif','$code_kel','0','01','$pokok','$userid','$ket3','$cao','') ") or die(mysql_error("GAGAL INPUT"));
				mysql_query("insert  into tunggakan (tgl_tunggak,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,blok)VALUES('$tanggal','$no_anggota','$unit','$cif','$code_kel','0','02','$ijaroh','$userid','$ket3','$cao','') ") or die(mysql_error("GAGAL INPUT"));
				
				if ($ke_tenor > $tenor){
							
				$new_id="$no_anggota$urut";
					
					mysql_query("INSERT INTO pinjaman_detail_jatpo(id,id_pinjam,cicilan,angsuran_pokok,bunga,tgl_jatuh_tempo,tgl_bayar,jumlah_bayar,ket)
					VALUES('$new_id','$no_anggota','$ke_lagi','$pokok','$ijaroh','$next_schedule1-1','','','')");
					mysql_query("INSERT INTO pinjaman_detail_jatpo(id,id_pinjam,cicilan,angsuran_pokok,bunga,tgl_jatuh_tempo,tgl_bayar,jumlah_bayar,ket)
					VALUES('$new_id','$no_anggota','$ke_lagi','$pokok','$ijaroh','$next_schedule1-1','','','')");
					
					
					
				}else {
					echo '<script>alert("Next angsuran  belum di tambahkan karean dalam Rencana Taghan."); window.location="yrs_list.php"</script>';
					
				}			
		
		}	
		else{
$cek_tunggak= mysql_fetch_array(mysql_query("select sum(KREDIT)- sum(DEBIT) as tunggak from tunggakan WHERE cif='$cif'"));
			$ada_tunggak=$cek_tunggak['tunggak'];	
			if($ada_tunggak>0){
mysql_query("insert  into tunggakan (tgl_tunggak,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,blok)VALUES('$tanggal','$no_anggota','$unit','$cif','$code_kel','$pokok','01','0','$userid','$ket','$cao','') ") or die(mysql_error("GAGAL INPUT"));
				mysql_query("insert  into tunggakan (tgl_tunggak,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,blok)VALUES('$tanggal','$no_anggota','$unit','$cif','$code_kel','$ijaroh','02','0','$userid','$ket','$cao','') ") or die(mysql_error("GAGAL INPUT"));
mysql_query("insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif,AO)VALUES('','$tanggal','$unit','$no_anggota','0','$pokok','03','$ket','$userid','REALISASI POKOK','$cif','$cao')") or die(mysql_error("GAGAL INPUT"));
				mysql_query("insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif,AO)VALUES('','$tanggal','$unit','$no_anggota','0','$ijaroh','04','$ket','$userid','REALISASI BAGHAS','$cif','$cao')") or die(mysql_error("GAGAL INPUT"));

				mysql_query("Update  loan set next_schedule ='$next_schedule',os =os-'$angsuran',saldo_margin=saldo_margin-'$ijaroh',run_tenor='$pb',ke='$next_cicilan',last_payment='$tanggal' where no_anggota  ='" . $value . "'") or die(mysql_error("GAGAL INPUT"));
				mysql_query("insert  into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,blok,tgl_input)VALUES('$tanggal','$no_anggota','$unit','$cif','$code_kel','$angsuran','03','0','$userid','$ket5','','$cao','2','$tgl_input') ") or die(mysql_error("GAGAL INPUT"));
				mysql_query("Update  tabungan_detail set saldo =(select(sum(KREDIT)-SUM(DEBIT)) as saldo from simpanan where NOREK='" . $value . "') where no_anggota  ='" . $value . "'") or die(mysql_error("GAGAL INPUT"));
				mysql_query("Update  pinjaman_detail set jumlah_bayar ='$angsuran',tgl_bayar='$tanggal' where id_pinjam  ='" . $value . "' and cicilan='$pb'") or die(mysql_error("GAGAL INPUT"));
				mysql_query("Update  pinjaman_detail_jatpo set jumlah_bayar ='$angsuran',tgl_bayar='$tanggal' where id_pinjam  ='" . $value . "' and cicilan='$pb'") or die(mysql_error("GAGAL INPUT"));
				//mysql_query("Update  tunggakan set pokok ='0',margin='0' where cif  ='$cif' and ke='$ke_tenor'") or die(mysql_error("GAGAL INPUT"));
				mysql_query("insert  into tagihan (id_reff,code_kel,no_anggota,bagi_hasil,pokok,tagihan,bulat,tagihan_ke,sisa_os,tgl_realisasi,status)VALUES('','$code_kel','$no_anggota','$ijaroh','$pokok','$angsuran','$bulat','$ke','$os','$next_schedule','RENCANA') ") or die(mysql_error("GAGAL INPUT"));
				$jurnal_umum=mysql_fetch_array(mysql_query("SELECT max(nomor_jurnal) FROM jurnal_umum where unit ='$unit' ORDER BY tanggal_selesai DESC"));
				$nomor_jurnal=$jurnal_umum[0]+1;
				//pencatatan di jurnal realasisi Piutang Murabahaha total jumlah Pokok dan margin
				//mysql_query("insert into jurnal_masuk(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values('$nomor_jurnal','$kode_transaksi','$tanggal','$unit')");
				//mysql_query("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
				//values('$unit','$kode_transaksi','1120000','$tanggal','KAS MASUK','$ket','$bulat','$userid')");
				mysql_query("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
				values('$unit','$kode_transaksi','1413000','$tanggal','Bukti Umum','Pendapatan Margin Yang di Tangguhkan','$angsuran','$userid')");
				$kodet=5;
				$kodet=acakangkahuruf($kodet);
				$kode_tran="BU/".$unit .$kodet;
				mysql_query("insert into jurnal_umum(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values('$nomor_jurnal','$kode_trans','$tanggal','$unit')");
				//Realisasi Piutang Margin	
				mysql_query("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
							values('$unit','$kode_tran','1423000','$tanggal','Bukti Umum','$ket','$ijaroh','$userid')");
				//Realiasasi Murabahah Debit pada Piutang Murabahah total jumlah Pokok dan margin 
				mysql_query("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
				values('$unit','$kode_tran','41002','$tanggal','Bukti Umum','Pendapatan Margin','$ijaroh','$userid')");
				mysql_query("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
				values('$unit','$kode_transaksi','2101000','$tanggal','Bukti Umum','ANGSURAN','$angsuran','$userid')");

}else{

				mysql_query("insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif,AO)VALUES('','$tanggal','$unit','$no_anggota','0','$pokok','03','$ket','$userid','REALISASI POKOK','$cif','$cao')") or die(mysql_error("GAGAL INPUT"));
				mysql_query("insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif,AO)VALUES('','$tanggal','$unit','$no_anggota','0','$ijaroh','03','$ket','$userid','REALISASI BAGHAS','$cif','$cao')") or die(mysql_error("GAGAL INPUT"));

				mysql_query("Update  loan set next_schedule ='$next_schedule',os =os-'$angsuran',saldo_margin=saldo_margin-'$ijaroh',run_tenor='$pb',ke='$next_cicilan',last_payment='$tanggal' where no_anggota  ='" . $value . "'") or die(mysql_error("GAGAL INPUT"));
				mysql_query("insert  into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,tgl_input)VALUES('$tanggal','$no_anggota','$unit','$cif','$code_kel','$angsuran','03','0','$userid','REALISASI ANGSURAN','$cao','$tgl_input') ") or die(mysql_error("GAGAL INPUT"));
				mysql_query("Update  tabungan_detail set saldo =(select(sum(KREDIT)-SUM(DEBIT)) as saldo from simpanan where NOREK='" . $value . "') where no_anggota  ='" . $value . "'") or die(mysql_error("GAGAL INPUT"));
				mysql_query("Update  pinjaman_detail set jumlah_bayar ='$angsuran',tgl_bayar='$tanggal' where id_pinjam  ='" . $value . "' and cicilan='$pb'") or die(mysql_error("GAGAL INPUT"));
				mysql_query("Update  pinjaman_detail_jatpo set jumlah_bayar ='$angsuran',tgl_bayar='$tanggal' where id_pinjam  ='" . $value . "' and cicilan='$pb'") or die(mysql_error("GAGAL INPUT"));
				//mysql_query("Update  tunggakan set pokok ='0',margin='0' where cif  ='$cif' and ke='$ke_tenor'") or die(mysql_error("GAGAL INPUT"));
				mysql_query("insert  into tagihan (id_reff,code_kel,no_anggota,bagi_hasil,pokok,tagihan,bulat,tagihan_ke,sisa_os,tgl_realisasi,status)VALUES('','$code_kel','$no_anggota','$ijaroh','$pokok','$angsuran','$bulat','$ke','$os','$next_schedule','RENCANA') ") or die(mysql_error("GAGAL INPUT"));
				$jurnal_umum=mysql_fetch_array(mysql_query("SELECT max(nomor_jurnal) FROM jurnal_umum where unit ='$unit' ORDER BY tanggal_selesai DESC"));
				$nomor_jurnal=$jurnal_umum[0]+1;
				//pencatatan di jurnal realasisi Piutang Murabahaha total jumlah Pokok dan margin
				//mysql_query("insert into jurnal_masuk(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values('$nomor_jurnal','$kode_transaksi','$tanggal','$unit')");
				//mysql_query("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
				//values('$unit','$kode_transaksi','1120000','$tanggal','KAS MASUK','$ket','$bulat','$userid')");
				mysql_query("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
				values('$unit','$kode_transaksi','1413000','$tanggal','Bukti Umum','Pendapatan Margin Yang di Tangguhkan','$angsuran','$userid')");
				$kodet=5;
				$kodet=acakangkahuruf($kodet);
				$kode_tran="BU/".$unit .$kodet;
				mysql_query("insert into jurnal_umum(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values('$nomor_jurnal','$kode_trans','$tanggal','$unit')");
				//Realisasi Piutang Margin	
				mysql_query("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
							values('$unit','$kode_tran','1423000','$tanggal','Bukti Umum','$ket','$ijaroh','$userid')");
				//Realiasasi Murabahah Debit pada Piutang Murabahah total jumlah Pokok dan margin 
				mysql_query("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
				values('$unit','$kode_tran','41002','$tanggal','Bukti Umum','Pendapatan Margin','$ijaroh','$userid')");
				mysql_query("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
				values('$unit','$kode_transaksi','2101000','$tanggal','Bukti Umum','ANGSURAN','$angsuran','$userid')");


				}
			}
			echo '<script>alert("Data sudah direalisasikan."); window.location="yrs_list.php"</script>';
			}  
		}	
		
	}else {
			echo '<script>alert("anda belum memilih data."); window.location="yrs_list.php"</script>';
	}

?>
