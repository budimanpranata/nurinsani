<?php require_once __DIR__ . '/php8_compat.php';
session_start();
ob_start();
$userid=$_SESSION['userid'];
$tanggal=$_SESSION['stgl_system'];
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
include "conn2.php";
include "function.php";
$koneksi=open_connection2();
$anggota=$_GET['anggota'];
$date= date("Y-m-d");

$select = mysql_query ("select count(no_anggota)as noa from loan");
if(mysql_num_rows($select)==0){
	echo "<script>alert ('Maaf...,No Anggota $nopen tidak ditemukan, Input no anggota yang benar !!!'); </script>";
    echo "<script language='javascript'>document.location.href='pelunasan.php' </script>";
	}
	else{

		$param = mysql_query ("select anggota.cif,loan.code_kel,loan.unit,angsuran,loan.Cust_Short_name,loan.cif,os,loan.cao,saldo_margin,status_app from loan 
		left join anggota on anggota.cif=loan.cif
		where loan.no_anggota ='$anggota' group by no_anggota");
		$data= mysql_fetch_array($param);
		$os=$data['os'];
		$saldo_margin=$data['saldo_margin'];
		$pokok=$os-$saldo_margin;
		$unit=$data ['unit'];
		$code_kel=$data['code_kel'];
		$cao=$data['cao'];
		$cif=$data['cif'];
		$angsuran=$data['angsuran'];
		$nama=mysql_real_escape_string($data['Cust_Short_name']);
		$ket ="Pelunasan Hapus Margin $nama $cif";
		$status_app=$data['status_app'];
	
		$cek_saldo = mysql_query ("SELECT SUM(KREDIT - DEBIT) AS saldo from simpanan where cif ='$cif' and TYPE not in ('04','05')");
		$data_saldo= mysql_fetch_array($cek_saldo);
		$saldo=$data_saldo['saldo'];

			

		if ($saldo < $pokok ){
			echo "<script> alert ('Maaf Saldo Pelunasan Lebih Kecil dari Sisa Pokok')</script>";
			echo "<script language='javascript'>document.location.href='pelunasan_hapus_margin.php'</script>";
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

				$upos="Update loan set os =os - '$os',saldo_margin='0',ke='1',status='LUNAS',Tenor ='25',status_app ='MURAB' where no_anggota='$anggota'";
				$uos = mysql_query($upos);
				$hapus_tg="delete from tunggakan where cif='$cif'";
				$hapus = mysql_query($hapus_tg);
				$hapus_tg="delete from temp_eod where cif='$cif'";
				$hapus = mysql_query($hapus_tg);

				$hapus_sc="update simpanan set type ='01', blok=' ' where cif='$cif' and type ='02'";

				$sc = mysql_query($hapus_sc);


				@mysql_query("update pinjaman_detail set jumlah_bayar='$angsuran',tgl_bayar='$tanggal' where id_pinjam='$anggota'");
				
				$qtabung="insert into simpanan(BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,blok,tgl_input,kode_transaksi) 
				VALUES ('$tanggal','$anggota','$unit','$cif','$code_kel','$os','03','0','$userid','$ket','','$cao','0','$date','$kode_trans')";
				$t = mysql_query($qtabung);
mysql_query("insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif)VALUES('','$tanggal','$unit','$anggota','0','$os','03','Pelunasan','$userid','REALISASI POKOK','$cif')") or die(mysql_error("GAGAL INPUT"));
mysql_query("Update  tagihan_cs set status_realisasi ='BAYAR'  where cif  ='$cif' and tgl_tagih='$tanggal'");
				

				}else{
					
					@mysql_query("insert into jurnal_umum(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values('','$kode_trans','$tanggal','$unit')");
					//Realisasi Piutang Margin	
					
				mysql_query("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
				values('$unit','$kode_trans','2101000','$tanggal','Bukti Umum','$ket','$pokok','$userid')");
	
				mysql_query("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
				values('$unit','$kode_trans','1413000','$tanggal','Bukti Umum','$ket','$os','$userid')");
								//Realisasi Piutang Margin	
				mysql_query("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
							values('$unit','$kode_trans','1423000','$tanggal','Bukti Umum','$ket','$saldo_margin','$userid')");
				//Realiasasi Murabahah Debit pada Piutang Murabahah total jumlah Pokok dan margin 
		
				
				$upos="Update loan set os =os - '$os',saldo_margin='0',run_tenor='0',ke='1',status='LUNAS' where no_anggota='$anggota'";
				$uos = mysql_query($upos);
				$hapus_tg="delete from tunggakan where cif='$cif'";
				$hapus = mysql_query($hapus_tg);

				$hapus_tg="delete from temp_eod where cif='$cif'";
				$hapus = mysql_query($hapus_tg);
				$hapus_sc="update simpanan set type ='01', blok=' ' where cif='$cif' and type ='02'";

				$sc = mysql_query($hapus_sc);


				@mysql_query("update pinjaman_detail set jumlah_bayar='$angsuran',tgl_bayar='$tanggal' where id_pinjam='$anggota'");
				@mysql_query("delete from tunggakan where cif='$cif'");
				$qtabung="insert into simpanan(BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,blok,tgl_input,kode_transaksi) 
				VALUES ('$tanggal','$anggota','$unit','$cif','$code_kel','$pokok','03','0','$userid','$ket','','$cao','0','$date','$kode_trans')";
				$t = mysql_query($qtabung);
mysql_query("insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif)VALUES('','$tanggal','$unit','$anggota','0','$pokok','03','Pelunasan','$userid','REALISASI POKOK','$cif')") or die(mysql_error("GAGAL INPUT"));
				mysql_query("insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif)VALUES('','$tanggal','$unit','$anggota','0','$saldo_margin','03','Pelunasan','$userid','REALISASI BAGHAS','$cif')") or die(mysql_error("GAGAL INPUT"));
mysql_query("Update  tagihan_cs set status_realisasi ='BAYAR'  where cif  ='$cif' and tgl_tagih='$tanggal'");

}


					if(($t)||($uos)){ 
	
						echo "<script> alert ('Pelunasan Hapus margin Nomor Anggota $anggota sukses ')</script>";

						echo "<script language='javascript'>document.location.href='pelunasan_hapus_margin.php'</script>";
						}else {
		
							echo "<script> alert ('GAGAL Pelunasan Nomor Anggota $anggota')</script>";

							echo "<script language='javascript'>document.location.href='pelunasan_hapus_margin.php'</script>";
							}
			}
}
?>
