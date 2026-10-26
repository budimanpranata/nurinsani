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
}
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

$tanggal=$tgl_system;
$simpan=$_SESSION['simpans'];	
$baki=$_SESSION['bakis'];	
$nom=$_POST['bulat'];			
			
$checbox = $_POST['cekbox'];
if ($checbox) {
	for ($i=0; $i <sizeof($checbox);$i++){
		//$query="insert into data (name,alamat) values ('".$checbox[$i]."','".$nom[$i]."')";
		$kel = mysql_query ("select no_anggota,anggota.cif,loan.code_kel,loan.unit,angsuran,loan.Cust_Short_name,loan.cif,os,loan.cao,saldo_margin,status_app,deal_produk,Tenor,run_tenor FROM loan 
		left join anggota on anggota.cif=loan.cif
		where  loan.cif ='".$checbox[$i]."'");
		$data = mysql_fetch_array($kel);

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
		$nama=mysql_real_escape_string($data['Cust_Short_name']);
		$ket ="Pelunasan $nama $cif";
		$status_app=$data['status_app'];
	
		$cek_saldo = mysql_query ("SELECT SUM(KREDIT - DEBIT) AS saldo from simpanan where cif ='$cif' and TYPE not in ('04','05','07')");
		$data_saldo= mysql_fetch_array($cek_saldo);
		$saldo=$data_saldo['saldo'];

 $kodetr=7;
            $kodetr=acakangkahuruf($kodetr);
            $kode_trans="BU/".$unit.$kodetr;
		
	


if ($saldo < $os ){
			echo "<script> alert ('Maaf Saldo an $cif $nama Pelunasan Lebih Kecil dari Sisa Pembiayaan')</script>";
			//echo "<script language='javascript'>document.location.href='pelunasan_kel.php'</script>";
			}else {
							
					$kodetr=5;
					$kodetr=acakangkahuruf($kodetr);
					$kode_trans="BU/".$unit .$kodetr;
				if($status_app=='REST'){
				@mysql_query("insert into jurnal_umum(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values('','$kode_trans','$tanggal','$unit')");
					//Realisasi Piutang Margin	

				mysql_query("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                values('$unit','$kode_trans','2101000','$tanggal','Bukti SYSTEM','$ket','$os','$userid')");
               
                mysql_query("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                values('$unit','$kode_trans','1411000','$tanggal','Bukti SYSTEM','$ket','$os','$userid')");

				$upos="Update loan set os =os - '$os',saldo_margin='0',ke='1',status='LUNAS', Tenor ='25',status_app ='MURAB' where no_anggota='$anggota'";
				$uos = mysql_query($upos);
				$hapus_tg="delete from tunggakan where cif='$cif'";
				$hapus = mysql_query($hapus_tg);

				$hapus_sc="update anggota set SOURCE_INCOME ='0' where cif='$cif'";

				$sc = mysql_query($hapus_sc);
				$hapus_tg="delete from temp_eod where cif='$cif'";
				$hapus = mysql_query($hapus_tg);

				$hapus_sc="update simpanan set type ='01', blok=' ' where cif='$cif' and type ='02'";

				$sc = mysql_query($hapus_sc);


				@mysql_query("update pinjaman_detail set jumlah_bayar='$angsuran',tgl_bayar='$tanggal' where id_pinjam='$anggota'");
				
				$qtabung="insert into simpanan(BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,blok,tgl_input,kode_transaksi) 
				VALUES ('$tanggal','$anggota','$unit','$cif','$code_kel','$os','03','0','$userid','$ket','','$cao','0','$date','$kode_trans')";
				$t = mysql_query($qtabung);
mysql_query("insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif)VALUES('','$tanggal','$unit','$anggota','0','$os','03','Pelunasan','$userid','REALISASI POKOK','$cif')") or die(mysql_error("GAGAL INPUT"));
				 		mysql_query("Update  tagihan_cs set status_realisasi ='BAYAR'  where cif  ='".$checbox[$i]."' and tgl_tagih='$tanggal'");


				}elseif($status_app=='REST_POKOK_MARGIN'){
				
@mysql_query("insert into jurnal_umum(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values('','$kode_trans','$tanggal','$unit')");
					//Realisasi Piutang Margin	
					
				mysql_query("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
				values('$unit','$kode_trans','2101000','$tanggal','Bukti System','$ket','$os','$userid')");
	
				mysql_query("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
				values('$unit','$kode_trans','1411000','$tanggal','Bukti System','$ket','$os','$userid')");
								//Realisasi Piutang Margin	
				mysql_query("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
							values('$unit','$kode_trans','1421000','$tanggal','Bukti Umum','$ket','$saldo_margin','$userid')");
				//Realiasasi Murabahah Debit pada Piutang Murabahah total jumlah Pokok dan margin 
				mysql_query("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
				values('$unit','$kode_trans','41002','$tanggal','Bukti System','$ket','$saldo_margin','$userid')");
				
				$upos="Update loan set os =os - '$os',saldo_margin='0',run_tenor='0',ke='1',status='LUNAS' where cif='$cif'";
				$uos = mysql_query($upos);
				$hapus_tg="delete from tunggakan where cif='$cif'";
				$hapus = mysql_query($hapus_tg);

				$hapus_sc="update anggota set SOURCE_INCOME ='0' where cif='$cif'";
				$sc = mysql_query($hapus_sc);
				$hapus_tg="delete from temp_eod where cif='$cif'";
				$hapus = mysql_query($hapus_tg);

				$hapus_sc="update simpanan set type ='01', blok=' ' where cif='$cif' and type ='02'";

				$sc = mysql_query($hapus_sc);


				@mysql_query("update pinjaman_detail set jumlah_bayar='$angsuran',tgl_bayar='$tanggal' where id_pinjam='$anggota'");
				@mysql_query("delete from tunggakan where cif='$cif'");
				$qtabung="insert into simpanan(BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,blok,tgl_input,kode_transaksi) 
				VALUES ('$tanggal','$anggota','$unit','$cif','$code_kel','$os','03','0','$userid','$ket','','$cao','0','$date','$kode_trans')";
				$t = mysql_query($qtabung);
mysql_query("insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif)VALUES('','$tanggal','$unit','$anggota','0','$pokok','03','Pelunasan','$userid','REALISASI POKOK','$cif')") or die(mysql_error("GAGAL INPUT"));
				mysql_query("insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif)VALUES('','$tanggal','$unit','$anggota','0','$saldo_margin','03','Pelunasan','$userid','REALISASI BAGHAS','$cif')") or die(mysql_error("GAGAL INPUT"));

mysql_query("Update  tagihan_cs set status_realisasi ='BAYAR'  where cif  ='".$checbox[$i]."' and tgl_tagih='$tanggal'");

				}else{
					
					@mysql_query("insert into jurnal_umum(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values('','$kode_trans','$tanggal','$unit')");
					//Realisasi Piutang Margin	
					
				mysql_query("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
				values('$unit','$kode_trans','2101000','$tanggal','Bukti System','$ket','$os','$userid')");
	
				mysql_query("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
				values('$unit','$kode_trans','1413000','$tanggal','Bukti System','$ket','$os','$userid')");
								//Realisasi Piutang Margin	
				mysql_query("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
							values('$unit','$kode_trans','1423000','$tanggal','Bukti Umum','$ket','$saldo_margin','$userid')");
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
				mysql_query("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
				values('$unit','$kode_trans','41002','$tanggal','Bukti System','$ket','$margin','$userid')");
				mysql_query("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
				values('$unit','$kode_trans','2867000','$tanggal','Bukti System','$ket','$sisa_margin','$userid')");

 
				}
				else{
				mysql_query("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
				values('$unit','$kode_trans','41002','$tanggal','Bukti System','$ket','$saldo_margin','$userid')");
				}
				
				
				$upos="Update loan set os =os - '$os',saldo_margin='0',run_tenor='0',ke='1',status='LUNAS' where cif='$cif'";
				$uos = mysql_query($upos);
				$hapus_tg="delete from tunggakan where cif='$cif'";
				$hapus = mysql_query($hapus_tg);

				$hapus_sc="update anggota set SOURCE_INCOME ='0' where cif='$cif'";
				$sc = mysql_query($hapus_sc);
				$hapus_tg="delete from temp_eod where cif='$cif'";
				$hapus = mysql_query($hapus_tg);

				$hapus_sc="update simpanan set type ='01', blok=' ' where cif='$cif' and type ='02'";

				$sc = mysql_query($hapus_sc);


				@mysql_query("update pinjaman_detail set jumlah_bayar='$angsuran',tgl_bayar='$tanggal' where id_pinjam='$anggota'");
				@mysql_query("delete from tunggakan where cif='$cif'");
				$qtabung="insert into simpanan(BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,blok,tgl_input,kode_transaksi) 
				VALUES ('$tanggal','$anggota','$unit','$cif','$code_kel','$os','03','0','$userid','$ket','','$cao','0','$date','$kode_trans')";
				$t = mysql_query($qtabung);
mysql_query("insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif)VALUES('','$tanggal','$unit','$anggota','0','$pokok','03','Pelunasan','$userid','REALISASI POKOK','$cif')") or die(mysql_error("GAGAL INPUT"));
				mysql_query("insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif)VALUES('','$tanggal','$unit','$anggota','0','$saldo_margin','03','Pelunasan','$userid','REALISASI BAGHAS','$cif')") or die(mysql_error("GAGAL INPUT"));

mysql_query("Update  tagihan_cs set status_realisasi ='BAYAR'  where cif  ='".$checbox[$i]."' and tgl_tagih='$tanggal'");

}



echo "<script>alert('Pelunasa an $cif Suksess');</script>";
		
		
		
		
		
		
		
		
		
		
		}
		
		
	

            echo "<script language='javascript'>document.location.href='pelunasan_kel.php'</script>";
	}	
	
}else {
			echo '<script>alert("anda belum memilih data."); window.location="pelunasan_kel.php"</script>';
	}

?>
