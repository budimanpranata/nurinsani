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
include "function.php";
 
$tanggal=$tgl_system;
 
$tgl_input=date("Y-m-d");
 
             
             
$cekbox = $_POST['cekbox'];
if ($cekbox) {
       foreach ($cekbox as $value) {
                   
        $select = db_semua("select DATE_ADD(next_schedule,INTERVAL 7 DAY) as next_schedule,unit,code_kel,no_anggota,CUST_SHORT_NAME,cif,ijaroh,Plafond,pokok,os,angsuran,bulat,TIMESTAMPDIFF(WEEK,tgl_murab,next_schedule) AS RUN_TENOR1,run_tenor,TIMESTAMPDIFF(WEEK,tgl_murab,CURDATE()) AS ke1,cao,ke as next,Tenor,saldo_margin,maturity_date,status_app,deal_produk  from loan  WHERE no_anggota =?", 's', (string) $value);
            foreach ($select as $row){
                $next_schedule1=$row['next_schedule'];
                $no_anggota=$row['no_anggota'];
                $pokok=$row['pokok'];
                $os=$row['os'];
                $bulat=$row['bulat'];
                $run_tenor=$row['run_tenor'];
                $pb=$run_tenor + 1;
                $next_tenor=$run_tenor + 2;
                $unit=$row['unit'];
$maturity_date=$row['maturity_date'];
                $angsuran=$row['angsuran'];
                $ijaroh=$row['ijaroh'];
 $nisbah_mus=$row['ijaroh'];
                $code_kel=$row['code_kel'];
$deal_produk=$row['deal_produk'];
                $saldo_margin=$row['saldo_margin'];
                $ke=$row['ke1'];
                $cif=$row['cif'];
                $cao=$row['cao'];
$status_app=$row['status_app'];
                $tenor=$row['Tenor'];
                $ke_tenor=$row['next'];
// versi lama di-escape untuk disisipkan ke SQL; dengan prepared statement nilai asli yang dikirim (hasil tersimpan sama)
$nama=$row['CUST_SHORT_NAME'];
                $ket="Setor Tagihan $nama $cif";
                $ket2="Setoran Tagihan $nama";
                $ket3="Tunggakan Tagihan $nama";
                 
                $kode_tr=7;
                $kode_tr=acakangkahuruf($kode_tr);      
                $kode_transaksi="BU/".$unit .$kode_tr;
                $wajib_kh=$bulat - $angsuran;
                $cek_next=db_satu("select cicilan,tgl_jatuh_tempo from pinjaman_detail WHERE cicilan=? AND id_pinjam =?", 'ss', (string) $next_tenor, (string) $value);
                $next_schedule=$cek_next['tgl_jatuh_tempo'];
                $next_cicilan=$cek_next['cicilan'];
                $row_sal=db_satu("select(sum(KREDIT) - sum(DEBIT))as saldo from simpanan WHERE cif =? and type not in ('05','02','04','07','10')", 's', (string) $cif) or die(mysql_error());
                $sisa=$row_sal['saldo'];
                $row_sal=db_satu("select(sum(KREDIT) - sum(DEBIT))as blok from simpanan WHERE type in ('05','02','04','07','10') and cif =?", 's', (string) $cif) or die(mysql_error());
                $blok=$row_sal['blok'];
                

                $minggon = $bulat-$angsuran;
       
                //$total_twm = $minggon * $run_tenor;
		$total_twm=0;
      
     
                $saldo=$sisa-$total_twm;
if($deal_produk=='2'){
				
			$angsuran=$angsuran + 2500;
			$ijaroh = $ijaroh + 2500;


	
		}elseif($deal_produk=='4'){

$angsuran=$angsuran + 2500;
			$ijaroh = $ijaroh + 2500;




}else{
			
				
		$angsuran=$angsuran ;
			$ijaroh = $ijaroh;


		}
                
//$saldo=$sisa-$blok;
$urut = $ke_tenor + 1;
if ($os>=$angsuran){
$angsuran=$angsuran;
}else{
$angsuran=$os;
}        
 
if ($saldo_margin>=$ijaroh){
$ijaroh=$ijaroh;
}else{
$ijaroh=$saldo_margin;
}        
$new_id="$no_anggota$urut";
 
$kodet=7;
$kodet=acakangkahuruf($kodet);
$kode_tran="BU/".$unit .$kodet;
if($status_app == 'REST'){
 
 
if ($saldo < $angsuran){    
                db_jalankan("Update loan set next_schedule=?,ke=?+1 where no_anggota  =?", 'sss', $next_schedule1 . '-1', (string) $ke_tenor, (string) $value) or die(mysql_error());
                                 
                //mysql_query("insert  into tunggakan (tgl_tunggak,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,blok)VALUES('$tanggal','$no_anggota','$unit','$cif','$code_kel','0','01','$pokok','$userid','$ket','$cao','') ") or die(mysql_error("GAGAL INPUT"));
               
            echo '<script>alert("Next angsuran  belum di tambahkan karean Menuggak."); window.location="yrs_list.php"</script>';
 
}else{

$cek_tunggak=db_satu("select sum(KREDIT)- sum(DEBIT) as tunggak from tunggakan WHERE cif=?", 's', (string) $cif);
            $ada_tunggak=$cek_tunggak['tunggak'];    
            if($ada_tunggak>0){
db_jalankan("insert  into tunggakan (tgl_tunggak,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,blok)VALUES(?,?,?,?,?,?,'01','0',?,?,?,'') ", 'sssssssss', (string) $tanggal, (string) $no_anggota, (string) $unit, (string) $cif, (string) $code_kel, (string) $pokok, (string) $userid, (string) $ket, (string) $cao) or die(mysql_error());

db_jalankan("insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif)VALUES('',?,?,?,'0',?,'03',?,?,'REALISASI POKOK',?)", 'sssssss', (string) $tanggal, (string) $unit, (string) $no_anggota, (string) $pokok, (string) $ket, (string) $userid, (string) $cif) or die(mysql_error());
               
                db_jalankan("Update  loan set next_schedule =?,os =os-?,saldo_margin=saldo_margin-?,run_tenor=?,ke=?+1 ,last_payment=? where no_anggota  =?", 'sssssss', (string) $next_schedule, (string) $angsuran, (string) $ijaroh, (string) $pb, (string) $ke_tenor, (string) $tanggal, (string) $value) or die(mysql_error());
                db_jalankan("insert  into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,tgl_input,kode_transaksi)VALUES(?,?,?,?,?,?,'03','0',?,?,?,?,?) ", 'sssssssssss', (string) $tanggal, (string) $no_anggota, (string) $unit, (string) $cif, (string) $code_kel, (string) $angsuran, (string) $userid, (string) $ket, (string) $cao, (string) $tgl_input, (string) $kode_tran) or die(mysql_error());
                //mysql_query("Update  tabungan_detail set saldo =(select(sum(KREDIT)-SUM(DEBIT)) as saldo from simpanan where NOREK='" . $value . "') where no_anggota  ='" . $value . "'") or die(mysql_error("GAGAL INPUT"));
                db_jalankan("Update  pinjaman_detail set jumlah_bayar =?,tgl_bayar=? where id_pinjam  =? and cicilan=?", 'ssss', (string) $angsuran, (string) $tanggal, (string) $value, (string) $pb) or die(mysql_error());
                db_jalankan("Update  pinjaman_detail_jatpo set jumlah_bayar =?,tgl_bayar=? where id_pinjam  =? and cicilan=?", 'ssss', (string) $angsuran, (string) $tanggal, (string) $value, (string) $pb) or die(mysql_error());
                //mysql_query("Update  tunggakan set pokok ='0',margin='0' where cif  ='$cif' and ke='$ke_tenor'") or die(mysql_error("GAGAL INPUT"));
                //mysql_query("insert  into tagihan (id_reff,code_kel,no_anggota,bagi_hasil,pokok,tagihan,bulat,tagihan_ke,sisa_os,tgl_realisasi,status)VALUES('','$code_kel','$no_anggota','$ijaroh','$pokok','$angsuran','$bulat','$ke','$os','$next_schedule','RENCANA') ") or die(mysql_error("GAGAL INPUT"));
                $jurnal_umum=array_values((array) db_satu("SELECT max(nomor_jurnal) FROM jurnal_umum where unit =? ORDER BY tanggal_selesai DESC", 's', (string) $unit));
                $nomor_jurnal=$jurnal_umum[0]+1;
                //pencatatan di jurnal realasisi Piutang Murabahaha total jumlah Pokok dan margin
                //mysql_query("insert into jurnal_masuk(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values('$nomor_jurnal','$kode_transaksi','$tanggal','$unit')");
                //mysql_query("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                //values('$unit','$kode_transaksi','1120000','$tanggal','KAS MASUK','$ket','$bulat','$userid')");
                db_jalankan("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                values(?,?,'2101000',?,'Bukti SYSTEM',?,?,?)", 'ssssss', (string) $unit, (string) $kode_tran, (string) $tanggal, (string) $ket, (string) $angsuran, (string) $userid);
               
                db_jalankan("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                values(?,?,'1411000',?,'Bukti SYSTEM',?,?,?)", 'ssssss', (string) $unit, (string) $kode_tran, (string) $tanggal, (string) $ket, (string) $angsuran, (string) $userid);
 		db_jalankan("Update  tagihan_cs set status_realisasi ='BAYAR'  where cif  =? and tgl_tagih=?", 'ss', (string) $cif, (string) $tanggal);

               
           
            echo '<script>alert("Data sudah direalisasikan."); window.location="yrs_list.php"</script>';


}else{
   
                db_jalankan("insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif)VALUES('',?,?,?,'0',?,'03',?,?,'REALISASI POKOK',?)", 'sssssss', (string) $tanggal, (string) $unit, (string) $no_anggota, (string) $pokok, (string) $ket, (string) $userid, (string) $cif) or die(mysql_error());
               
                db_jalankan("Update  loan set next_schedule =?,os =os-?,saldo_margin=saldo_margin-?,run_tenor=?,ke=?+1 ,last_payment=? where no_anggota  =?", 'sssssss', (string) $next_schedule, (string) $angsuran, (string) $ijaroh, (string) $pb, (string) $ke_tenor, (string) $tanggal, (string) $value) or die(mysql_error());
                db_jalankan("insert  into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,tgl_input,kode_transaksi)VALUES(?,?,?,?,?,?,'03','0',?,?,?,?,?) ", 'sssssssssss', (string) $tanggal, (string) $no_anggota, (string) $unit, (string) $cif, (string) $code_kel, (string) $angsuran, (string) $userid, (string) $ket, (string) $cao, (string) $tgl_input, (string) $kode_tran) or die(mysql_error());
                //mysql_query("Update  tabungan_detail set saldo =(select(sum(KREDIT)-SUM(DEBIT)) as saldo from simpanan where NOREK='" . $value . "') where no_anggota  ='" . $value . "'") or die(mysql_error("GAGAL INPUT"));
                db_jalankan("Update  pinjaman_detail set jumlah_bayar =?,tgl_bayar=? where id_pinjam  =? and cicilan=?", 'ssss', (string) $angsuran, (string) $tanggal, (string) $value, (string) $pb) or die(mysql_error());
                db_jalankan("Update  pinjaman_detail_jatpo set jumlah_bayar =?,tgl_bayar=? where id_pinjam  =? and cicilan=?", 'ssss', (string) $angsuran, (string) $tanggal, (string) $value, (string) $pb) or die(mysql_error());
                //mysql_query("Update  tunggakan set pokok ='0',margin='0' where cif  ='$cif' and ke='$ke_tenor'") or die(mysql_error("GAGAL INPUT"));
                //mysql_query("insert  into tagihan (id_reff,code_kel,no_anggota,bagi_hasil,pokok,tagihan,bulat,tagihan_ke,sisa_os,tgl_realisasi,status)VALUES('','$code_kel','$no_anggota','$ijaroh','$pokok','$angsuran','$bulat','$ke','$os','$next_schedule','RENCANA') ") or die(mysql_error("GAGAL INPUT"));
                $jurnal_umum=array_values((array) db_satu("SELECT max(nomor_jurnal) FROM jurnal_umum where unit =? ORDER BY tanggal_selesai DESC", 's', (string) $unit));
                $nomor_jurnal=$jurnal_umum[0]+1;
                //pencatatan di jurnal realasisi Piutang Murabahaha total jumlah Pokok dan margin
                //mysql_query("insert into jurnal_masuk(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values('$nomor_jurnal','$kode_transaksi','$tanggal','$unit')");
                //mysql_query("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                //values('$unit','$kode_transaksi','1120000','$tanggal','KAS MASUK','$ket','$bulat','$userid')");
                db_jalankan("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                values(?,?,'2101000',?,'Bukti SYSTEM',?,?,?)", 'ssssss', (string) $unit, (string) $kode_tran, (string) $tanggal, (string) $ket, (string) $angsuran, (string) $userid);
               
                db_jalankan("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                values(?,?,'1411000',?,'Bukti SYSTEM',?,?,?)", 'ssssss', (string) $unit, (string) $kode_tran, (string) $tanggal, (string) $ket, (string) $angsuran, (string) $userid);
 		db_jalankan("Update  tagihan_cs set status_realisasi ='BAYAR'  where cif  =? and tgl_tagih=?", 'ss', (string) $cif, (string) $tanggal);

               
           
            echo '<script>alert("Data sudah direalisasikan."); window.location="yrs_list.php"</script>';
           }
}
       
}elseif($status_app=="REST_POKOK_MARGIN"){
 
db_jalankan("INSERT INTO pinjaman_detail_rencana(id,id_pinjam,cicilan,angsuran_pokok,bunga,tgl_jatuh_tempo,tgl_bayar,jumlah_bayar,ket,cif,unit,ao)
                    VALUES(?,?,?,?,?,?,'','','',?,?,?)", 'sssssssss', (string) $new_id, (string) $no_anggota, (string) $urut, (string) $pokok, (string) $ijaroh, (string) $next_schedule1, (string) $cif, (string) $unit, (string) $cao);
                 
 
if ($saldo < $angsuran){    
                db_jalankan("Update loan set next_schedule=?,ke=?+1 where no_anggota  =?", 'sss', $next_schedule1 . '-1', (string) $ke_tenor, (string) $value) or die(mysql_error());
                $ke_n=db_satu("select ke from loan WHERE cif =?", 's', (string) $cif) or die(mysql_error());
                $ke_lagi=$ke_n['ke'];
                 
                //mysql_query("insert  into tunggakan (tgl_tunggak,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,blok)VALUES('$tanggal','$no_anggota','$unit','$cif','$code_kel','0','01','$pokok','$userid','$ket','$cao','') ") or die(mysql_error("GAGAL INPUT"));
                //mysql_query("insert  into tunggakan (tgl_tunggak,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,blok)VALUES('$tanggal','$no_anggota','$unit','$cif','$code_kel','0','02','$ijaroh','$userid','$ket','$cao','') ") or die(mysql_error("GAGAL INPUT"));
                 
                if ($maturity_date < $tgl_system){
                             
                 
        db_jalankan("INSERT INTO pinjaman_detail (id,id_pinjam,cicilan,angsuran_pokok,bunga,tgl_jatuh_tempo,tgl_bayar,jumlah_bayar,ket,cif,unit,ao)
                values(?,?,?,?,?,?,'','0','JATPO',?,?,?)", 'sssssssss', (string) $new_id, (string) $no_anggota, (string) $urut, (string) $pokok, (string) $ijaroh, $next_schedule1 . '-1', (string) $cif, (string) $unit, (string) $cao) or die(mysql_error());
     
                    db_jalankan("INSERT INTO pinjaman_detail_jatpo(id,id_pinjam,cicilan,angsuran_pokok,bunga,tgl_jatuh_tempo,tgl_bayar,jumlah_bayar,ket)
                    VALUES(?,?,?,?,?,?,'','','')", 'ssssss', (string) $new_id, (string) $no_anggota, (string) $urut, (string) $pokok, (string) $ijaroh, $next_schedule1 . '-1');
                    //mysql_query("Update  loan set next_schedule ='$next_schedule1-1',ke=ke+1 where no_anggota  ='" . $value . "'") or die(mysql_error("GAGAL INPUT"));
                    //mysql_query("delete from tunggakan where cif ='$cif' and tgl_tunggak ='$tanggal' ");
                     
                     
                }else {
                    echo '<script>alert("Next angsuran  belum di tambahkan karean dalam Rencana Tagihan."); window.location="yrs_list.php"</script>';
                     
                }            
         
        }    
        else{
$cek_tunggak=db_satu("select sum(KREDIT)- sum(DEBIT) as tunggak from tunggakan WHERE cif=?", 's', (string) $cif);
            $ada_tunggak=$cek_tunggak['tunggak'];    
            if($ada_tunggak>0){
db_jalankan("insert  into tunggakan (tgl_tunggak,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,blok)VALUES(?,?,?,?,?,?,'01','0',?,?,?,'') ", 'sssssssss', (string) $tanggal, (string) $no_anggota, (string) $unit, (string) $cif, (string) $code_kel, (string) $pokok, (string) $userid, (string) $ket, (string) $cao) or die(mysql_error());
                db_jalankan("insert  into tunggakan (tgl_tunggak,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,blok)VALUES(?,?,?,?,?,?,'02','0',?,?,?,'') ", 'sssssssss', (string) $tanggal, (string) $no_anggota, (string) $unit, (string) $cif, (string) $code_kel, (string) $ijaroh, (string) $userid, (string) $ket, (string) $cao) or die(mysql_error());
db_jalankan("insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif)VALUES('',?,?,?,'0',?,'03',?,?,'REALISASI POKOK',?)", 'sssssss', (string) $tanggal, (string) $unit, (string) $no_anggota, (string) $pokok, (string) $ket, (string) $userid, (string) $cif) or die(mysql_error());
                db_jalankan("insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif)VALUES('',?,?,?,'0',?,'03',?,?,'REALISASI BAGHAS',?)", 'sssssss', (string) $tanggal, (string) $unit, (string) $no_anggota, (string) $ijaroh, (string) $ket, (string) $userid, (string) $cif) or die(mysql_error());
 
                db_jalankan("Update  loan set next_schedule =?,os =os-?,saldo_margin=saldo_margin-?,run_tenor=?,ke=?+1,last_payment=? where no_anggota  =?", 'sssssss', (string) $next_schedule, (string) $angsuran, (string) $ijaroh, (string) $pb, (string) $ke_tenor, (string) $tanggal, (string) $value) or die(mysql_error());
                 
 
                 
db_jalankan("insert  into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,blok,tgl_input,kode_transaksi)VALUES(?,?,?,?,?,?,'03','0',?,?,'',?,'2',?,?) ", 'sssssssssss', (string) $tanggal, (string) $no_anggota, (string) $unit, (string) $cif, (string) $code_kel, (string) $angsuran, (string) $userid, (string) $ket, (string) $cao, (string) $tgl_input, (string) $kode_tran) or die(mysql_error());
                $jurnal_umum=array_values((array) db_satu("SELECT max(nomor_jurnal) FROM jurnal_umum where unit =? ORDER BY tanggal_selesai DESC", 's', (string) $unit));
                $nomor_jurnal=$jurnal_umum[0]+1;

db_jalankan("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                values(?,?,'2101000',?,'Bukti SYSTEM',?,?,?)", 'ssssss', (string) $unit, (string) $kode_tran, (string) $tanggal, (string) $ket, (string) $angsuran, (string) $userid);
 
                 db_jalankan("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                values(?,?,'1411000',?,'Bukti SYSTEM',?,?,?)", 'ssssss', (string) $unit, (string) $kode_tran, (string) $tanggal, (string) $ket, (string) $angsuran, (string) $userid);
                 
                db_jalankan("insert into jurnal_umum(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values(?,?,?,?)", 'ssss', (string) $nomor_jurnal, (string) $kode_tran, (string) $tanggal, (string) $unit);
                //Realisasi Piutang Margin    
                db_jalankan("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                            values(?,?,'1421000',?,'Bukti SYSTEM',?,?,?)", 'ssssss', (string) $unit, (string) $kode_tran, (string) $tanggal, (string) $ket, (string) $ijaroh, (string) $userid);
                //Realiasasi Murabahah Debit pada Piutang Murabahah total jumlah Pokok dan margin
                db_jalankan("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                values(?,?,'41002',?,'Bukti SYSTEM',?,?,?)", 'ssssss', (string) $unit, (string) $kode_tran, (string) $tanggal, (string) $ket, (string) $ijaroh, (string) $userid);
		db_jalankan("Update  tagihan_cs set status_realisasi ='BAYAR'  where cif  =? and tgl_tagih=?", 'ss', (string) $cif, (string) $tanggal);

 
}else{
 
                db_jalankan("insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif)VALUES('',?,?,?,'0',?,'03',?,?,'REALISASI POKOK',?)", 'sssssss', (string) $tanggal, (string) $unit, (string) $no_anggota, (string) $pokok, (string) $ket, (string) $userid, (string) $cif) or die(mysql_error());
                db_jalankan("insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif)VALUES('',?,?,?,'0',?,'03',?,?,'REALISASI BAGHAS',?)", 'sssssss', (string) $tanggal, (string) $unit, (string) $no_anggota, (string) $ijaroh, (string) $ket, (string) $userid, (string) $cif) or die(mysql_error());
 
                db_jalankan("Update  loan set next_schedule =?,os =os-?,saldo_margin=saldo_margin-?,run_tenor=?,ke=?+1,last_payment=? where no_anggota  =?", 'sssssss', (string) $next_schedule, (string) $angsuran, (string) $ijaroh, (string) $pb, (string) $ke_tenor, (string) $tanggal, (string) $value) or die(mysql_error());
                db_jalankan("insert  into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,tgl_input,kode_transaksi)VALUES(?,?,?,?,?,?,'03','0',?,?,?,?,?) ", 'sssssssssss', (string) $tanggal, (string) $no_anggota, (string) $unit, (string) $cif, (string) $code_kel, (string) $angsuran, (string) $userid, (string) $ket, (string) $cao, (string) $tgl_input, (string) $kode_tran) or die(mysql_error());
                $jurnal_umum=array_values((array) db_satu("SELECT max(nomor_jurnal) FROM jurnal_umum where unit =? ORDER BY tanggal_selesai DESC", 's', (string) $unit));
                $nomor_jurnal=$jurnal_umum[0]+1;
db_jalankan("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                values(?,?,'2101000',?,'Bukti SYSTEM',?,?,?)", 'ssssss', (string) $unit, (string) $kode_tran, (string) $tanggal, (string) $ket, (string) $angsuran, (string) $userid);
 
                 db_jalankan("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                values(?,?,'1411000',?,'Bukti SYSTEM',?,?,?)", 'ssssss', (string) $unit, (string) $kode_tran, (string) $tanggal, (string) $ket, (string) $angsuran, (string) $userid);
                 
                db_jalankan("insert into jurnal_umum(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values(?,?,?,?)", 'ssss', (string) $nomor_jurnal, (string) $kode_tran, (string) $tanggal, (string) $unit);
                //Realisasi Piutang Margin    
                db_jalankan("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                            values(?,?,'1421000',?,'Bukti SYSTEM',?,?,?)", 'ssssss', (string) $unit, (string) $kode_tran, (string) $tanggal, (string) $ket, (string) $ijaroh, (string) $userid);
                //Realiasasi Murabahah Debit pada Piutang Murabahah total jumlah Pokok dan margin
                db_jalankan("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                values(?,?,'41002',?,'Bukti SYSTEM',?,?,?)", 'ssssss', (string) $unit, (string) $kode_tran, (string) $tanggal, (string) $ket, (string) $ijaroh, (string) $userid);
		db_jalankan("Update  tagihan_cs set status_realisasi ='BAYAR'  where cif  =? and tgl_tagih=?", 'ss', (string) $cif, (string) $tanggal);

 
 
                }
            }
            echo '<script>alert("Data sudah direalisasikan."); window.location="yrs_list.php"</script>';
             

        }elseif($status_app=="MUSYAROKAH"){

           			$cek_omzet=db_satu("SELECT nominal from omzet WHERE tanggal =? and cif =?", 'ss', (string) $tanggal, (string) $cif);
        			$omzet=$cek_omzet['nominal']; 

					$cek_nisbah=db_satu("SELECT nisbah from nisbah WHERE cif =?", 's', (string) $cif);
        			$nisbah=$cek_nisbah['nisbah']; 

					$porsi= $omzet * $nisbah;
                    $angsuran_pokok = $angsuran - $nisbah_mus - 2500;
                

            db_jalankan("INSERT INTO pinjaman_detail_rencana(id,id_pinjam,cicilan,angsuran_pokok,bunga,tgl_jatuh_tempo,tgl_bayar,jumlah_bayar,ket,cif,unit,ao)
                    VALUES(?,?,?,?,?,?,'','','',?,?,?)", 'sssssssss', (string) $new_id, (string) $no_anggota, (string) $urut, (string) $pokok, (string) $ijaroh, (string) $next_schedule1, (string) $cif, (string) $unit, (string) $cao);
                 
 
if ($saldo < $angsuran){    
                db_jalankan("Update loan set next_schedule=?,ke=?+1 where no_anggota  =?", 'sss', $next_schedule1 . '-1', (string) $ke_tenor, (string) $value) or die(mysql_error());
                $ke_n=db_satu("select ke from loan WHERE cif =?", 's', (string) $cif) or die(mysql_error());
                $ke_lagi=$ke_n['ke'];
                 
                        
                if ($maturity_date < $tgl_system){
                             
                 
        db_jalankan("INSERT INTO pinjaman_detail (id,id_pinjam,cicilan,angsuran_pokok,bunga,tgl_jatuh_tempo,tgl_bayar,jumlah_bayar,ket,cif,unit,ao)
                values(?,?,?,?,?,?,'','0','JATPO',?,?,?)", 'sssssssss', (string) $new_id, (string) $no_anggota, (string) $urut, (string) $pokok, (string) $ijaroh, $next_schedule1 . '-1', (string) $cif, (string) $unit, (string) $cao) or die(mysql_error());
     
                    db_jalankan("INSERT INTO pinjaman_detail_jatpo(id,id_pinjam,cicilan,angsuran_pokok,bunga,tgl_jatuh_tempo,tgl_bayar,jumlah_bayar,ket)
                    VALUES(?,?,?,?,?,?,'','','')", 'ssssss', (string) $new_id, (string) $no_anggota, (string) $urut, (string) $pokok, (string) $ijaroh, $next_schedule1 . '-1');
                     
                     
                }else {
                    echo '<script>alert("Next angsuran  belum di tambahkan karean dalam Rencana Tagihan."); window.location="yrs_list.php"</script>';
                     
                }            
         
        }    
        else{
$cek_tunggak=db_satu("select sum(KREDIT)- sum(DEBIT) as tunggak from tunggakan WHERE cif=?", 's', (string) $cif);
            $ada_tunggak=$cek_tunggak['tunggak'];    
            if($ada_tunggak>0){
db_jalankan("insert  into tunggakan (tgl_tunggak,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,blok)VALUES(?,?,?,?,?,?,'01','0',?,?,?,'') ", 'sssssssss', (string) $tanggal, (string) $no_anggota, (string) $unit, (string) $cif, (string) $code_kel, (string) $angsuran_pokok, (string) $userid, (string) $ket, (string) $cao) or die(mysql_error());
//mysql_query("insert  into tunggakan (tgl_tunggak,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,blok)VALUES('$tanggal','$no_anggota','$unit','$cif','$code_kel','$nisbah_mus','01','0','$userid','$ket','$cao','') ") or die(mysql_error("GAGAL INPUT"));

db_jalankan("insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif)VALUES('',?,?,?,'0',?,'03',?,?,'REALISASI POKOK',?)", 'sssssss', (string) $tanggal, (string) $unit, (string) $no_anggota, (string) $angsuran, (string) $ket, (string) $userid, (string) $cif) or die(mysql_error());
db_jalankan("insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif)VALUES('',?,?,?,'0',?,'03',?,?,'REALISASI BAGHAS',?)", 'sssssss', (string) $tanggal, (string) $unit, (string) $no_anggota, (string) $nisbah_mus, (string) $ket, (string) $userid, (string) $cif) or die(mysql_error());
db_jalankan("Update  loan set next_schedule =?,os =os-?,run_tenor=?,ke=?+1,last_payment=? where cif  =?", 'ssssss', (string) $next_schedule, (string) $angsuran_pokok, (string) $pb, (string) $ke_tenor, (string) $tanggal, (string) $cif) or die(mysql_error());
db_jalankan("update pinjaman_detail_mus set nisbah_anggota =?,angsuran_pokok = ?,nisbah=?,tgl_bayar=? where cif  =? and cicilan=?", 'ssssss', (string) $nisbah_mus, (string) $angsuran_pokok, (string) $nisbah_mus, (string) $tanggal, (string) $cif, (string) $pb);                 
 
                 
db_jalankan("insert  into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,blok,tgl_input,kode_transaksi)VALUES(?,?,?,?,?,?,'03','0',?,?,'',?,'2',?,?) ", 'sssssssssss', (string) $tanggal, (string) $no_anggota, (string) $unit, (string) $cif, (string) $code_kel, (string) $angsuran, (string) $userid, (string) $ket, (string) $cao, (string) $tgl_input, (string) $kode_tran) or die(mysql_error());
              $jurnal_umum=array_values((array) db_satu("SELECT max(nomor_jurnal) FROM jurnal_umum where unit =? ORDER BY tanggal_selesai DESC", 's', (string) $unit));
                $nomor_jurnal=$jurnal_umum[0]+1;
                  db_jalankan("insert into jurnal_umum(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values(?,?,?,?)", 'ssss', (string) $nomor_jurnal, (string) $kode_tran, (string) $tanggal, (string) $unit);
              
                  db_jalankan("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                values(?,?,'2101000',?,'Bukti SYSTEM',?,?,?)", 'ssssss', (string) $unit, (string) $kode_tran, (string) $tanggal, (string) $ket, (string) $angsuran, (string) $userid);
                db_jalankan("Update  tagihan_cs set status_realisasi ='BAYAR'  where cif  =? and tgl_tagih=?", 'ss', (string) $cif, (string) $tanggal);

                db_jalankan("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                values(?,?,'1472000',?,'Bukti SYSTEM',?,?,?)", 'ssssss', (string) $unit, (string) $kode_tran, (string) $tanggal, (string) $ket, (string) $angsuran_pokok, (string) $userid);

                  db_jalankan("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                values(?,?,'41062',?,'Bukti SYSTEM',?,?,?)", 'ssssss', (string) $unit, (string) $kode_tran, (string) $tanggal, (string) $ket, (string) $nisbah_mus, (string) $userid);
            
                db_jalankan("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                            values(?,?,'2867000',?,'Bukti SYSTEM',?,'2500',?)", 'sssss', (string) $unit, (string) $kode_tran, (string) $tanggal, (string) $ket, (string) $userid);
 
}else{
 
               db_jalankan("insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif)VALUES('',?,?,?,'0',?,'03',?,?,'REALISASI POKOK',?)", 'sssssss', (string) $tanggal, (string) $unit, (string) $no_anggota, (string) $pokok, (string) $ket, (string) $userid, (string) $cif) or die(mysql_error());
              db_jalankan("update pinjaman_detail_mus set nisbah_anggota =?,angsuran_pokok = ?,nisbah=?,tgl_bayar=? where cif  =? and cicilan=?", 'ssssss', (string) $porsi, (string) $angsuran_pokok, (string) $nisbah_mus, (string) $tanggal, (string) $cif, (string) $pb);             
                db_jalankan("Update  loan set next_schedule =?,os =os-?,run_tenor=?,ke=?+1,last_payment=? where cif  =?", 'ssssss', (string) $next_schedule, (string) $angsuran_pokok, (string) $pb, (string) $ke_tenor, (string) $tanggal, (string) $cif) or die(mysql_error());
                db_jalankan("insert  into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,tgl_input,kode_transaksi)VALUES(?,?,?,?,?,?,'03','0',?,?,?,?,?) ", 'sssssssssss', (string) $tanggal, (string) $no_anggota, (string) $unit, (string) $cif, (string) $code_kel, (string) $angsuran, (string) $userid, (string) $ket, (string) $cao, (string) $tgl_input, (string) $kode_tran) or die(mysql_error());
                $jurnal_umum=array_values((array) db_satu("SELECT max(nomor_jurnal) FROM jurnal_umum where unit =? ORDER BY tanggal_selesai DESC", 's', (string) $unit));
                $nomor_jurnal=$jurnal_umum[0]+1;
                 
                db_jalankan("insert into jurnal_umum(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values(?,?,?,?)", 'ssss', (string) $nomor_jurnal, (string) $kode_tran, (string) $tanggal, (string) $unit);
                //Realisasi Piutang Margin 
                db_jalankan("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                values(?,?,'2101000',?,'Bukti SYSTEM',?,?,?)", 'ssssss', (string) $unit, (string) $kode_tran, (string) $tanggal, (string) $ket, (string) $angsuran, (string) $userid); 
                   
                db_jalankan("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                            values(?,?,'1472000',?,'Bukti SYSTEM',?,?,?)", 'ssssss', (string) $unit, (string) $kode_tran, (string) $tanggal, (string) $ket, (string) $angsuran_pokok, (string) $userid);
                //Realiasasi Murabahah Debit pada Piutang Murabahah total jumlah Pokok dan margin
                db_jalankan("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                values(?,?,'41062',?,'Bukti SYSTEM',?,?,?)", 'ssssss', (string) $unit, (string) $kode_tran, (string) $tanggal, (string) $ket, (string) $nisbah_mus, (string) $userid);
                  db_jalankan("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                            values(?,?,'2867000',?,'Bukti SYSTEM',?,'2500',?)", 'sssss', (string) $unit, (string) $kode_tran, (string) $tanggal, (string) $ket, (string) $userid);
                
                }
            }
            echo '<script>alert("Data sudah direalisasikan."); window.location="yrs_list.php"</script>';



        }elseif($status_app=="MURAB"){
            
$cek_tunggak=db_satu("select sum(KREDIT)- sum(DEBIT) as tunggak from tunggakan WHERE cif=?", 's', (string) $cif);
            $ada_tunggak=$cek_tunggak['tunggak'];    

            if($ada_tunggak>0){
db_jalankan("insert  into tunggakan (tgl_tunggak,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,blok)VALUES(?,?,?,?,?,?,'01','0',?,?,?,'') ", 'sssssssss', (string) $tanggal, (string) $no_anggota, (string) $unit, (string) $cif, (string) $code_kel, (string) $pokok, (string) $userid, (string) $ket, (string) $cao) or die(mysql_error());
                db_jalankan("insert  into tunggakan (tgl_tunggak,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,blok)VALUES(?,?,?,?,?,?,'02','0',?,?,?,'') ", 'sssssssss', (string) $tanggal, (string) $no_anggota, (string) $unit, (string) $cif, (string) $code_kel, (string) $ijaroh, (string) $userid, (string) $ket, (string) $cao) or die(mysql_error());
db_jalankan("insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif)VALUES('',?,?,?,'0',?,'03',?,?,'REALISASI POKOK',?)", 'sssssss', (string) $tanggal, (string) $unit, (string) $no_anggota, (string) $pokok, (string) $ket, (string) $userid, (string) $cif) or die(mysql_error());
                db_jalankan("insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif)VALUES('',?,?,?,'0',?,'03',?,?,'REALISASI BAGHAS',?)", 'sssssss', (string) $tanggal, (string) $unit, (string) $no_anggota, (string) $ijaroh, (string) $ket, (string) $userid, (string) $cif) or die(mysql_error());
 
                db_jalankan("Update  loan set next_schedule =?,os =os-?,saldo_margin=saldo_margin-?,run_tenor=?,ke=?+1,last_payment=? where no_anggota  =?", 'sssssss', (string) $next_schedule, (string) $angsuran, (string) $ijaroh, (string) $pb, (string) $ke_tenor, (string) $tanggal, (string) $value) or die(mysql_error());
                 
 
                 
db_jalankan("insert  into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,blok,tgl_input,kode_transaksi)VALUES(?,?,?,?,?,?,'03','0',?,?,'',?,'2',?,?) ", 'sssssssssss', (string) $tanggal, (string) $no_anggota, (string) $unit, (string) $cif, (string) $code_kel, (string) $angsuran, (string) $userid, (string) $ket, (string) $cao, (string) $tgl_input, (string) $kode_tran) or die(mysql_error());
                             $jurnal_umum=array_values((array) db_satu("SELECT max(nomor_jurnal) FROM jurnal_umum where unit =? ORDER BY tanggal_selesai DESC", 's', (string) $unit));
                $nomor_jurnal=$jurnal_umum[0]+1;
		if($deal_produk =='2'){
$margin = $ijaroh - 2500;
db_jalankan("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                values(?,?,'1413000',?,'Bukti SYSTEM',?,?,?)", 'ssssss', (string) $unit, (string) $kode_tran, (string) $tanggal, (string) $ket, (string) $angsuran, (string) $userid);
                 
                db_jalankan("insert into jurnal_umum(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values(?,?,?,?)", 'ssss', (string) $nomor_jurnal, (string) $kode_tran, (string) $tanggal, (string) $unit);
                //Realisasi Piutang Margin    
                db_jalankan("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                            values(?,?,'1423000',?,'Bukti SYSTEM',?,?,?)", 'ssssss', (string) $unit, (string) $kode_tran, (string) $tanggal, (string) $ket, (string) $ijaroh, (string) $userid);
                //Realiasasi Murabahah Debit pada Piutang Murabahah total jumlah Pokok dan margin
                db_jalankan("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                values(?,?,'41002',?,'Bukti SYSTEM',?,?,?)", 'ssssss', (string) $unit, (string) $kode_tran, (string) $tanggal, (string) $ket, (string) $margin, (string) $userid);
 db_jalankan("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                values(?,?,'2867000',?,'Bukti SYSTEM',?,'2500',?)", 'sssss', (string) $unit, (string) $kode_tran, (string) $tanggal, (string) $ket, (string) $userid);
                db_jalankan("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                values(?,?,'2101000',?,'Bukti SYSTEM',?,?,?)", 'ssssss', (string) $unit, (string) $kode_tran, (string) $tanggal, (string) $ket, (string) $angsuran, (string) $userid);
db_jalankan("Update  tagihan_cs set status_realisasi ='BAYAR'  where cif  =? and tgl_tagih=?", 'ss', (string) $cif, (string) $tanggal);

}elseif($deal_produk =='4'){

$margin = $ijaroh - 2500;
 db_jalankan("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                values(?,?,'1413000',?,'Bukti SYSTEM',?,?,?)", 'ssssss', (string) $unit, (string) $kode_tran, (string) $tanggal, (string) $ket, (string) $angsuran, (string) $userid);
                 
                db_jalankan("insert into jurnal_umum(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values(?,?,?,?)", 'ssss', (string) $nomor_jurnal, (string) $kode_tran, (string) $tanggal, (string) $unit);
                //Realisasi Piutang Margin    
                db_jalankan("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                            values(?,?,'1423000',?,'Bukti SYSTEM',?,?,?)", 'ssssss', (string) $unit, (string) $kode_tran, (string) $tanggal, (string) $ket, (string) $ijaroh, (string) $userid);
                //Realiasasi Murabahah Debit pada Piutang Murabahah total jumlah Pokok dan margin
                db_jalankan("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                values(?,?,'41002',?,'Bukti SYSTEM',?,?,?)", 'ssssss', (string) $unit, (string) $kode_tran, (string) $tanggal, (string) $ket, (string) $margin, (string) $userid);
 db_jalankan("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                values(?,?,'41002',?,'Bukti SYSTEM',?,'2500',?)", 'sssss', (string) $unit, (string) $kode_tran, (string) $tanggal, (string) $ket, (string) $userid);
                db_jalankan("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                values(?,?,'2101000',?,'Bukti SYSTEM',?,?,?)", 'ssssss', (string) $unit, (string) $kode_tran, (string) $tanggal, (string) $ket, (string) $angsuran, (string) $userid);
 db_jalankan("Update  tagihan_cs set status_realisasi ='BAYAR'  where cif  =? and tgl_tagih=?", 'ss', (string) $cif, (string) $tanggal);

}else{
db_jalankan("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                values(?,?,'1413000',?,'Bukti SYSTEM',?,?,?)", 'ssssss', (string) $unit, (string) $kode_tran, (string) $tanggal, (string) $ket, (string) $angsuran, (string) $userid);
                 
                db_jalankan("insert into jurnal_umum(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values(?,?,?,?)", 'ssss', (string) $nomor_jurnal, (string) $kode_tran, (string) $tanggal, (string) $unit);
                //Realisasi Piutang Margin    
                db_jalankan("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                            values(?,?,'1423000',?,'Bukti SYSTEM',?,?,?)", 'ssssss', (string) $unit, (string) $kode_tran, (string) $tanggal, (string) $ket, (string) $ijaroh, (string) $userid);
                //Realiasasi Murabahah Debit pada Piutang Murabahah total jumlah Pokok dan margin
                db_jalankan("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                values(?,?,'41002',?,'Bukti SYSTEM',?,?,?)", 'ssssss', (string) $unit, (string) $kode_tran, (string) $tanggal, (string) $ket, (string) $ijaroh, (string) $userid);
                db_jalankan("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                values(?,?,'2101000',?,'Bukti SYSTEM',?,?,?)", 'ssssss', (string) $unit, (string) $kode_tran, (string) $tanggal, (string) $ket, (string) $angsuran, (string) $userid);
db_jalankan("Update  tagihan_cs set status_realisasi ='BAYAR'  where cif  =? and tgl_tagih=?", 'ss', (string) $cif, (string) $tanggal);

}

               
 
}else{
 
                db_jalankan("insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif)VALUES('',?,?,?,'0',?,'03',?,?,'REALISASI POKOK',?)", 'sssssss', (string) $tanggal, (string) $unit, (string) $no_anggota, (string) $pokok, (string) $ket, (string) $userid, (string) $cif) or die(mysql_error());
                db_jalankan("insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif)VALUES('',?,?,?,'0',?,'03',?,?,'REALISASI BAGHAS',?)", 'sssssss', (string) $tanggal, (string) $unit, (string) $no_anggota, (string) $ijaroh, (string) $ket, (string) $userid, (string) $cif) or die(mysql_error());
 
                db_jalankan("Update  loan set next_schedule =?,os =os-?,saldo_margin=saldo_margin-?,run_tenor=?,ke=?+1,last_payment=? where no_anggota  =?", 'sssssss', (string) $next_schedule, (string) $angsuran, (string) $ijaroh, (string) $pb, (string) $ke_tenor, (string) $tanggal, (string) $value) or die(mysql_error());
                db_jalankan("insert  into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,tgl_input,kode_transaksi)VALUES(?,?,?,?,?,?,'03','0',?,?,?,?,?) ", 'sssssssssss', (string) $tanggal, (string) $no_anggota, (string) $unit, (string) $cif, (string) $code_kel, (string) $angsuran, (string) $userid, (string) $ket, (string) $cao, (string) $tgl_input, (string) $kode_tran) or die(mysql_error());
                  $jurnal_umum=array_values((array) db_satu("SELECT max(nomor_jurnal) FROM jurnal_umum where unit =? ORDER BY tanggal_selesai DESC", 's', (string) $unit));
                $nomor_jurnal=$jurnal_umum[0]+1;

if($deal_produk =='2'){
$margin = $ijaroh - 2500;
 db_jalankan("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                values(?,?,'1413000',?,'Bukti SYSTEM',?,?,?)", 'ssssss', (string) $unit, (string) $kode_tran, (string) $tanggal, (string) $ket, (string) $angsuran, (string) $userid);
                 
                db_jalankan("insert into jurnal_umum(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values(?,?,?,?)", 'ssss', (string) $nomor_jurnal, (string) $kode_tran, (string) $tanggal, (string) $unit);
                //Realisasi Piutang Margin    
                db_jalankan("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                            values(?,?,'1423000',?,'Bukti SYSTEM',?,?,?)", 'ssssss', (string) $unit, (string) $kode_tran, (string) $tanggal, (string) $ket, (string) $ijaroh, (string) $userid);
                //Realiasasi Murabahah Debit pada Piutang Murabahah total jumlah Pokok dan margin
                db_jalankan("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                values(?,?,'41002',?,'Bukti SYSTEM',?,?,?)", 'ssssss', (string) $unit, (string) $kode_tran, (string) $tanggal, (string) $ket, (string) $margin, (string) $userid);
 db_jalankan("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                values(?,?,'2867000',?,'Bukti SYSTEM',?,'2500',?)", 'sssss', (string) $unit, (string) $kode_tran, (string) $tanggal, (string) $ket, (string) $userid);
                db_jalankan("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                values(?,?,'2101000',?,'Bukti SYSTEM',?,?,?)", 'ssssss', (string) $unit, (string) $kode_tran, (string) $tanggal, (string) $ket, (string) $angsuran, (string) $userid);
 db_jalankan("Update  tagihan_cs set status_realisasi ='BAYAR'  where cif  =? and tgl_tagih=?", 'ss', (string) $cif, (string) $tanggal);
}elseif($deal_produk =='4'){

$margin = $ijaroh - 2500;
 db_jalankan("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                values(?,?,'1413000',?,'Bukti SYSTEM',?,?,?)", 'ssssss', (string) $unit, (string) $kode_tran, (string) $tanggal, (string) $ket, (string) $angsuran, (string) $userid);
                 
                db_jalankan("insert into jurnal_umum(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values(?,?,?,?)", 'ssss', (string) $nomor_jurnal, (string) $kode_tran, (string) $tanggal, (string) $unit);
                //Realisasi Piutang Margin    
                db_jalankan("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                            values(?,?,'1423000',?,'Bukti SYSTEM',?,?,?)", 'ssssss', (string) $unit, (string) $kode_tran, (string) $tanggal, (string) $ket, (string) $ijaroh, (string) $userid);
                //Realiasasi Murabahah Debit pada Piutang Murabahah total jumlah Pokok dan margin
                db_jalankan("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                values(?,?,'41002',?,'Bukti SYSTEM',?,?,?)", 'ssssss', (string) $unit, (string) $kode_tran, (string) $tanggal, (string) $ket, (string) $margin, (string) $userid);
 db_jalankan("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                values(?,?,'41002',?,'Bukti SYSTEM',?,'2500',?)", 'sssss', (string) $unit, (string) $kode_tran, (string) $tanggal, (string) $ket, (string) $userid);
                db_jalankan("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                values(?,?,'2101000',?,'Bukti SYSTEM',?,?,?)", 'ssssss', (string) $unit, (string) $kode_tran, (string) $tanggal, (string) $ket, (string) $angsuran, (string) $userid);
 db_jalankan("Update  tagihan_cs set status_realisasi ='BAYAR'  where cif  =? and tgl_tagih=?", 'ss', (string) $cif, (string) $tanggal);

}else{
 db_jalankan("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                values(?,?,'1413000',?,'Bukti SYSTEM',?,?,?)", 'ssssss', (string) $unit, (string) $kode_tran, (string) $tanggal, (string) $ket, (string) $angsuran, (string) $userid);
                 
                db_jalankan("insert into jurnal_umum(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values(?,?,?,?)", 'ssss', (string) $nomor_jurnal, (string) $kode_tran, (string) $tanggal, (string) $unit);
                //Realisasi Piutang Margin    
                db_jalankan("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                            values(?,?,'1423000',?,'Bukti SYSTEM',?,?,?)", 'ssssss', (string) $unit, (string) $kode_tran, (string) $tanggal, (string) $ket, (string) $ijaroh, (string) $userid);
                //Realiasasi Murabahah Debit pada Piutang Murabahah total jumlah Pokok dan margin
                db_jalankan("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                values(?,?,'41002',?,'Bukti SYSTEM',?,?,?)", 'ssssss', (string) $unit, (string) $kode_tran, (string) $tanggal, (string) $ket, (string) $ijaroh, (string) $userid);
                db_jalankan("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                values(?,?,'2101000',?,'Bukti SYSTEM',?,?,?)", 'ssssss', (string) $unit, (string) $kode_tran, (string) $tanggal, (string) $ket, (string) $angsuran, (string) $userid);
 db_jalankan("Update  tagihan_cs set status_realisasi ='BAYAR'  where cif  =? and tgl_tagih=?", 'ss', (string) $cif, (string) $tanggal);
}
               
 
                }
            
            echo '<script>alert("Data sudah direalisasikan."); window.location="yrs_list.php"</script>';

        }
        
        else{
db_jalankan("INSERT INTO pinjaman_detail_rencana(id,id_pinjam,cicilan,angsuran_pokok,bunga,tgl_jatuh_tempo,tgl_bayar,jumlah_bayar,ket,cif,unit,ao)
                    VALUES(?,?,?,?,?,?,'','','',?,?,?)", 'sssssssss', (string) $new_id, (string) $no_anggota, (string) $urut, (string) $pokok, (string) $ijaroh, (string) $next_schedule1, (string) $cif, (string) $unit, (string) $cao);
                 
 
if ($saldo < $angsuran){    
                db_jalankan("Update loan set next_schedule=?,ke=?+1 where no_anggota  =?", 'sss', $next_schedule1 . '-1', (string) $ke_tenor, (string) $value) or die(mysql_error());
                $ke_n=db_satu("select ke from loan WHERE cif =?", 's', (string) $cif) or die(mysql_error());
                $ke_lagi=$ke_n['ke'];
                 
                //mysql_query("insert  into tunggakan (tgl_tunggak,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,blok)VALUES('$tanggal','$no_anggota','$unit','$cif','$code_kel','0','01','$pokok','$userid','$ket','$cao','') ") or die(mysql_error("GAGAL INPUT"));
                //mysql_query("insert  into tunggakan (tgl_tunggak,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,blok)VALUES('$tanggal','$no_anggota','$unit','$cif','$code_kel','0','02','$ijaroh','$userid','$ket','$cao','') ") or die(mysql_error("GAGAL INPUT"));
                 
                if ($maturity_date < $tgl_system){
                             
                 
        db_jalankan("INSERT INTO pinjaman_detail (id,id_pinjam,cicilan,angsuran_pokok,bunga,tgl_jatuh_tempo,tgl_bayar,jumlah_bayar,ket,cif,unit,ao)
                values(?,?,?,?,?,?,'','0','JATPO',?,?,?)", 'sssssssss', (string) $new_id, (string) $no_anggota, (string) $urut, (string) $pokok, (string) $ijaroh, $next_schedule1 . '-1', (string) $cif, (string) $unit, (string) $cao) or die(mysql_error());
     
                    db_jalankan("INSERT INTO pinjaman_detail_jatpo(id,id_pinjam,cicilan,angsuran_pokok,bunga,tgl_jatuh_tempo,tgl_bayar,jumlah_bayar,ket)
                    VALUES(?,?,?,?,?,?,'','','')", 'ssssss', (string) $new_id, (string) $no_anggota, (string) $urut, (string) $pokok, (string) $ijaroh, $next_schedule1 . '-1');
                    //mysql_query("Update  loan set next_schedule ='$next_schedule1-1',ke=ke+1 where no_anggota  ='" . $value . "'") or die(mysql_error("GAGAL INPUT"));
                    //mysql_query("delete from tunggakan where cif ='$cif' and tgl_tunggak ='$tanggal' ");
                     
                     
                }else {
                    echo '<script>alert("Next angsuran  belum di tambahkan karean dalam Rencana Tagihan."); window.location="yrs_list.php"</script>';
                     
                }            
         
        }    
        else{
$cek_tunggak=db_satu("select sum(KREDIT)- sum(DEBIT) as tunggak from tunggakan WHERE cif=?", 's', (string) $cif);
            $ada_tunggak=$cek_tunggak['tunggak'];    
            if($ada_tunggak>0){
db_jalankan("insert  into tunggakan (tgl_tunggak,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,blok)VALUES(?,?,?,?,?,?,'01','0',?,?,?,'') ", 'sssssssss', (string) $tanggal, (string) $no_anggota, (string) $unit, (string) $cif, (string) $code_kel, (string) $pokok, (string) $userid, (string) $ket, (string) $cao) or die(mysql_error());
                db_jalankan("insert  into tunggakan (tgl_tunggak,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,blok)VALUES(?,?,?,?,?,?,'02','0',?,?,?,'') ", 'sssssssss', (string) $tanggal, (string) $no_anggota, (string) $unit, (string) $cif, (string) $code_kel, (string) $ijaroh, (string) $userid, (string) $ket, (string) $cao) or die(mysql_error());
db_jalankan("insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif)VALUES('',?,?,?,'0',?,'03',?,?,'REALISASI POKOK',?)", 'sssssss', (string) $tanggal, (string) $unit, (string) $no_anggota, (string) $pokok, (string) $ket, (string) $userid, (string) $cif) or die(mysql_error());
                db_jalankan("insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif)VALUES('',?,?,?,'0',?,'03',?,?,'REALISASI BAGHAS',?)", 'sssssss', (string) $tanggal, (string) $unit, (string) $no_anggota, (string) $ijaroh, (string) $ket, (string) $userid, (string) $cif) or die(mysql_error());
 
                db_jalankan("Update  loan set next_schedule =?,os =os-?,saldo_margin=saldo_margin-?,run_tenor=?,ke=?+1,last_payment=? where no_anggota  =?", 'sssssss', (string) $next_schedule, (string) $angsuran, (string) $ijaroh, (string) $pb, (string) $ke_tenor, (string) $tanggal, (string) $value) or die(mysql_error());
                 
 
                 
db_jalankan("insert  into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,blok,tgl_input,kode_transaksi)VALUES(?,?,?,?,?,?,'03','0',?,?,'',?,'2',?,?) ", 'sssssssssss', (string) $tanggal, (string) $no_anggota, (string) $unit, (string) $cif, (string) $code_kel, (string) $angsuran, (string) $userid, (string) $ket, (string) $cao, (string) $tgl_input, (string) $kode_tran) or die(mysql_error());
                //mysql_query("Update  tabungan_detail set saldo =(select(sum(KREDIT)-SUM(DEBIT)) as saldo from simpanan where NOREK='" . $value . "') where no_anggota  ='" . $value . "'") or die(mysql_error("GAGAL INPUT"));
                //mysql_query("Update  pinjaman_detail set jumlah_bayar ='$angsuran',tgl_bayar='$tanggal' where id_pinjam  ='" . $value . "' and cicilan='$pb'") or die(mysql_error("GAGAL INPUT"));
                //mysql_query("Update  pinjaman_detail_jatpo set jumlah_bayar ='$angsuran',tgl_bayar='$tanggal' where id_pinjam  ='" . $value . "' and cicilan='$pb'") or die(mysql_error("GAGAL INPUT"));
                //mysql_query("Update  tunggakan set pokok ='0',margin='0' where cif  ='$cif' and ke='$ke_tenor'") or die(mysql_error("GAGAL INPUT"));
                //mysql_query("insert  into tagihan (id_reff,code_kel,no_anggota,bagi_hasil,pokok,tagihan,bulat,tagihan_ke,sisa_os,tgl_realisasi,status)VALUES('','$code_kel','$no_anggota','$ijaroh','$pokok','$angsuran','$bulat','$ke','$os','$next_schedule','RENCANA') ") or die(mysql_error("GAGAL INPUT"));
                $jurnal_umum=array_values((array) db_satu("SELECT max(nomor_jurnal) FROM jurnal_umum where unit =? ORDER BY tanggal_selesai DESC", 's', (string) $unit));
                $nomor_jurnal=$jurnal_umum[0]+1;
                //pencatatan di jurnal realasisi Piutang Murabahaha total jumlah Pokok dan margin
                //mysql_query("insert into jurnal_masuk(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values('$nomor_jurnal','$kode_transaksi','$tanggal','$unit')");
                //mysql_query("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                //values('$unit','$kode_transaksi','1120000','$tanggal','KAS MASUK','$ket','$bulat','$userid')");
                db_jalankan("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                values(?,?,'1413000',?,'Bukti SYSTEM',?,?,?)", 'ssssss', (string) $unit, (string) $kode_tran, (string) $tanggal, (string) $ket, (string) $angsuran, (string) $userid);
                 
                db_jalankan("insert into jurnal_umum(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values(?,?,?,?)", 'ssss', (string) $nomor_jurnal, (string) $kode_tran, (string) $tanggal, (string) $unit);
                //Realisasi Piutang Margin    
                db_jalankan("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                            values(?,?,'1423000',?,'Bukti SYSTEM',?,?,?)", 'ssssss', (string) $unit, (string) $kode_tran, (string) $tanggal, (string) $ket, (string) $ijaroh, (string) $userid);
                //Realiasasi Murabahah Debit pada Piutang Murabahah total jumlah Pokok dan margin
                db_jalankan("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                values(?,?,'41002',?,'Bukti SYSTEM',?,?,?)", 'ssssss', (string) $unit, (string) $kode_tran, (string) $tanggal, (string) $ket, (string) $ijaroh, (string) $userid);
                db_jalankan("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                values(?,?,'2101000',?,'Bukti SYSTEM',?,?,?)", 'ssssss', (string) $unit, (string) $kode_tran, (string) $tanggal, (string) $ket, (string) $angsuran, (string) $userid);
db_jalankan("Update  tagihan_cs set status_realisasi ='BAYAR'  where cif  =? and tgl_tagih=?", 'ss', (string) $cif, (string) $tanggal);
 
}else{
 
                db_jalankan("insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif)VALUES('',?,?,?,'0',?,'03',?,?,'REALISASI POKOK',?)", 'sssssss', (string) $tanggal, (string) $unit, (string) $no_anggota, (string) $pokok, (string) $ket, (string) $userid, (string) $cif) or die(mysql_error());
                db_jalankan("insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif)VALUES('',?,?,?,'0',?,'03',?,?,'REALISASI BAGHAS',?)", 'sssssss', (string) $tanggal, (string) $unit, (string) $no_anggota, (string) $ijaroh, (string) $ket, (string) $userid, (string) $cif) or die(mysql_error());
 
                db_jalankan("Update  loan set next_schedule =?,os =os-?,saldo_margin=saldo_margin-?,run_tenor=?,ke=?+1,last_payment=? where no_anggota  =?", 'sssssss', (string) $next_schedule, (string) $angsuran, (string) $ijaroh, (string) $pb, (string) $ke_tenor, (string) $tanggal, (string) $value) or die(mysql_error());
                db_jalankan("insert  into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,tgl_input,kode_transaksi)VALUES(?,?,?,?,?,?,'03','0',?,?,?,?,?) ", 'sssssssssss', (string) $tanggal, (string) $no_anggota, (string) $unit, (string) $cif, (string) $code_kel, (string) $angsuran, (string) $userid, (string) $ket, (string) $cao, (string) $tgl_input, (string) $kode_tran) or die(mysql_error());
                //mysql_query("Update  tabungan_detail set saldo =(select(sum(KREDIT)-SUM(DEBIT)) as saldo from simpanan where NOREK='" . $value . "') where no_anggota  ='" . $value . "'") or die(mysql_error("GAGAL INPUT"));
                //mysql_query("Update  pinjaman_detail set jumlah_bayar ='$angsuran',tgl_bayar='$tanggal' where id_pinjam  ='" . $value . "' and cicilan='$pb'") or die(mysql_error("GAGAL INPUT"));
                //mysql_query("Update  pinjaman_detail_jatpo set jumlah_bayar ='$angsuran',tgl_bayar='$tanggal' where id_pinjam  ='" . $value . "' and cicilan='$pb'") or die(mysql_error("GAGAL INPUT"));
                //mysql_query("Update  tunggakan set pokok ='0',margin='0' where cif  ='$cif' and ke='$ke_tenor'") or die(mysql_error("GAGAL INPUT"));
                //mysql_query("insert  into tagihan (id_reff,code_kel,no_anggota,bagi_hasil,pokok,tagihan,bulat,tagihan_ke,sisa_os,tgl_realisasi,status)VALUES('','$code_kel','$no_anggota','$ijaroh','$pokok','$angsuran','$bulat','$ke','$os','$next_schedule','RENCANA') ") or die(mysql_error("GAGAL INPUT"));
                $jurnal_umum=array_values((array) db_satu("SELECT max(nomor_jurnal) FROM jurnal_umum where unit =? ORDER BY tanggal_selesai DESC", 's', (string) $unit));
                $nomor_jurnal=$jurnal_umum[0]+1;
                //pencatatan di jurnal realasisi Piutang Murabahaha total jumlah Pokok dan margin
                //mysql_query("insert into jurnal_masuk(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values('$nomor_jurnal','$kode_transaksi','$tanggal','$unit')");
                //mysql_query("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                //values('$unit','$kode_transaksi','1120000','$tanggal','KAS MASUK','$ket','$bulat','$userid')");
                db_jalankan("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                values(?,?,'1413000',?,'Bukti SYSTEM',?,?,?)", 'ssssss', (string) $unit, (string) $kode_tran, (string) $tanggal, (string) $ket, (string) $angsuran, (string) $userid);
                 
                db_jalankan("insert into jurnal_umum(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values(?,?,?,?)", 'ssss', (string) $nomor_jurnal, (string) $kode_tran, (string) $tanggal, (string) $unit);
                //Realisasi Piutang Margin    
                db_jalankan("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                            values(?,?,'1423000',?,'Bukti SYSTEM',?,?,?)", 'ssssss', (string) $unit, (string) $kode_tran, (string) $tanggal, (string) $ket, (string) $ijaroh, (string) $userid);
                //Realiasasi Murabahah Debit pada Piutang Murabahah total jumlah Pokok dan margin
                db_jalankan("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                values(?,?,'41002',?,'Bukti SYSTEM',?,?,?)", 'ssssss', (string) $unit, (string) $kode_tran, (string) $tanggal, (string) $ket, (string) $ijaroh, (string) $userid);
                db_jalankan("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                values(?,?,'2101000',?,'Bukti SYSTEM',?,?,?)", 'ssssss', (string) $unit, (string) $kode_tran, (string) $tanggal, (string) $ket, (string) $angsuran, (string) $userid);
 db_jalankan("Update  tagihan_cs set status_realisasi ='BAYAR'  where cif  =? and tgl_tagih=?", 'ss', (string) $cif, (string) $tanggal);
 
                }
            }
            echo '<script>alert("Data sudah direalisasikan."); window.location="yrs_list.php"</script>';
}    
    }    
}    
}else {
            echo '<script>alert("anda belum memilih data."); window.location="yrs_list.php"</script>';
    }
 
?>


