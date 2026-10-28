<?php require_once __DIR__ . '/php8_compat.php';
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

$select = mysql_query ("select count(no_anggota)as noa from loan");
if(mysql_num_rows($select)==0){
	echo "<script>alert ('Maaf...,No Anggota $nopen tidak ditemukan, Input no anggota yang benar !!!'); </script>";
    echo "<script language='javascript'>document.location.href='pelunasan.php' </script>";
	}
	else{

		$param = mysql_query ("select anggota.cif,loan.code_kel,loan.unit,angsuran,loan.Cust_Short_name,loan.cif,os,loan.cao,saldo_margin,status_app,ijaroh from loan 
		left join anggota on anggota.cif=loan.cif
		where loan.no_anggota ='$anggota' group by no_anggota");
		$data= mysql_fetch_array($param);
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
		$nama=mysql_real_escape_string($data['Cust_Short_name']);
		$ket ="Pelunasan $nama $cif";
		$status_app=$data['status_app'];
		$ket2="Pelunasan MUsyarokah $nama $cif";
$norek =$anggota;
	
		$cek_saldo = mysql_query ("SELECT SUM(KREDIT - DEBIT) AS saldo from simpanan where cif ='$cif' and TYPE not in ('04','05','07')");
		$data_saldo= mysql_fetch_array($cek_saldo);
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

				

				}elseif($status_app=='MUSYAROKAH'){

				$cek_total_debetan= mysql_fetch_array(mysql_query("SELECT sum(nisbah) as debet_ni, sum(nisbah_anggota) as debet_omzet  from pinjaman_detail_mus WHERE cif ='$ciff' and cicilan ='$run_tenor'"));
        			$debet_ni=$cek_total_debetan['debet_ni'];
                    $debet_omzet = $cek_total_debetan['debet_omzet'];

					$debet_ni = $iharoh_mus * $run_tenor;
                    $kembali_nisbah = $debet_ni - $debet_omzet;
					$os_margin = $os + $ijaroh_mus + 2500;

                    if($kembali_nisbah > 0 ){
                         $jurnal_umum=mysql_fetch_array(mysql_query("SELECT max(nomor_jurnal) FROM jurnal_umum where unit ='$unit' ORDER BY tanggal_selesai DESC"));
                    $nomor_jurnal=$jurnal_umum[0]+1;

                        mysql_query("insert  into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,tgl_input,kode_transaksi)VALUES('$tanggal','$norek','$unit','".$ciff."','$code_kel','0','03','$kembali_nisbah','$userid','Pengembalian Nisbah Pelunasan','$ket2','$cao','$tgl_input','$kode_trans') ") ;
                        
                        mysql_query("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                        values('$unit','$kode_trans','2101000','$tanggal','Bukti SYSTEM','Pengembalian Nisbah Pelunasan','$kembali_nisbah','$userid')");
                        mysql_query("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                                values('$unit','$kode_trans','41063','$tanggal','Bukti SYSTEM','Pengembalian Nisbah Pelunasan','$kembali_nisbah','$userid')");
                          mysql_query("insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif)VALUES('','$tanggal','$unit','$no_anggota','0','$os','03','$ket2','$userid','REALISASI POKOK','".$ciff."')");
                            mysql_query("insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif)VALUES('','$tanggal','$unit','$no_anggota','0','$saldo_margin','03','$ket2','$userid','REALISASI BAGHAS','".$ciff."')");
                             
                            $uos = mysql_query("Update  loan set os ='0',saldo_margin='0',run_tenor='0',ke='0',last_payment='$tanggal' where cif  ='".$ciff."'");
                            $t= mysql_query ("delete from tunggakan where cif  ='".$ciff."'");
							mysql_query ("delete from pinjaman_detail_mus where cif  ='".$ciff."'");

                            mysql_query("insert  into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,tgl_input,kode_transaksi)VALUES('$tanggal','$norek','$unit','".$ciff."','$code_kel','$os_margin','03','0','$userid','$ket2','$cao','$tgl_input','$kode_trans') ") ;
                            mysql_query("insert into jurnal_umum(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values('$nomor_jurnal','$kode_trans','$tanggal','$unit')");
							 
							mysql_query("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                                values('$unit','$kode_trans','2101000','$tanggal','Bukti SYSTEM','$ket2','$os_margin','$userid')");

                            mysql_query("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                                values('$unit','$kode_trans','1472000','$tanggal','Bukti SYSTEM','$ket2','$os','$userid')");

							mysql_query("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                                values('$unit','$kode_trans','41062','$tanggal','Bukti SYSTEM','$ket2','$ijaroh_mus','$userid')");
							mysql_query("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                                values('$unit','$kode_trans','2867000','$tanggal','Bukti SYSTEM','$ket2','2500','$userid')");
                          
                           


                    }        
                    else{

                          $jurnal_umum=mysql_fetch_array(mysql_query("SELECT max(nomor_jurnal) FROM jurnal_umum where unit ='$unit' ORDER BY tanggal_selesai DESC"));
                    $nomor_jurnal=$jurnal_umum[0]+1;
                      mysql_query("insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif)VALUES('','$tanggal','$unit','$no_anggota','0','$os','03','$ket2','$userid','REALISASI POKOK','".$ciff."')");
                            mysql_query("insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif)VALUES('','$tanggal','$unit','$no_anggota','0','$saldo_margin','03','$ket2','$userid','REALISASI BAGHAS','".$ciff."')");
                             
                            $uos=mysql_query("Update  loan set os ='0',saldo_margin='0',run_tenor='0',ke='0',last_payment='$tanggal' where cif  ='".$ciff."'");
                            $t=mysql_query ("delete from tunggakan where cif  ='".$ciff."'");
							mysql_query ("delete from pinjaman_detail_mus where cif  ='".$ciff."'");
                            mysql_query("insert  into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,tgl_input,kode_transaksi)VALUES('$tanggal','$norek','$unit','".$ciff."','$code_kel','$os_margin','03','0','$userid','$ket2','$cao','$tgl_input','$kode_trans') ") ;
                            mysql_query("insert into jurnal_umum(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values('$nomor_jurnal','$kode_tran','$tanggal','$unit')");
             
                          	mysql_query("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                                values('$unit','$kode_trans','2101000','$tanggal','Bukti SYSTEM','$ket2','$os_margin','$userid')");

                            mysql_query("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                                values('$unit','$kode_trans','1472000','$tanggal','Bukti SYSTEM','$ket2','$os','$userid')");

							mysql_query("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                                values('$unit','$kode_trans','41062','$tanggal','Bukti SYSTEM','$ket2','$ijaroh_mus','$userid')");
							mysql_query("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                                values('$unit','$kode_trans','2867000','$tanggal','Bukti SYSTEM','$ket2','2500','$userid')");
                          


                    }        
                     


					
				
				}else{
					
					@mysql_query("insert into jurnal_umum(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values('','$kode_trans','$tanggal','$unit')");
					//Realisasi Piutang Margin	
					
				mysql_query("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
				values('$unit','$kode_trans','2101000','$tanggal','Bukti Umum','$ket','$os','$userid')");
	
				mysql_query("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
				values('$unit','$kode_trans','1413000','$tanggal','Bukti Umum','$ket','$os','$userid')");
								//Realisasi Piutang Margin	
				mysql_query("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
							values('$unit','$kode_trans','1423000','$tanggal','Bukti Umum','$ket','$saldo_margin','$userid')");
				//Realiasasi Murabahah Debit pada Piutang Murabahah total jumlah Pokok dan margin 
				mysql_query("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
				values('$unit','$kode_trans','41002','$tanggal','Bukti Umum','$ket','$saldo_margin','$userid')");
				
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
				VALUES ('$tanggal','$anggota','$unit','$cif','$code_kel','$os','03','0','$userid','$ket','','$cao','0','$date','$kode_trans')";
				$t = mysql_query($qtabung);
mysql_query("insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif)VALUES('','$tanggal','$unit','$anggota','0','$pokok','03','Pelunasan','$userid','REALISASI POKOK','$cif')") or die(mysql_error("GAGAL INPUT"));
				mysql_query("insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif)VALUES('','$tanggal','$unit','$anggota','0','$saldo_margin','03','Pelunasan','$userid','REALISASI BAGHAS','$cif')") or die(mysql_error("GAGAL INPUT"));

}


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
