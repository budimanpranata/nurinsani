<?php require_once __DIR__ . '/koneksi_db.php';
require_once __DIR__ . '/inc/notif.php';
session_start();
ob_start();
$userid=$_SESSION['userid'];
//cek apakah user sudah login
$tgl_system=$_SESSION['stgl_system'];
//cek apakah user sudah login
if(!isset($_SESSION['stgl_system'])){
notif_pergi('Maaf, tanggal system belum di setup', 'param_tgl.php', 'warning');
 
   /* redirect ditangani notif_pergi */
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
 
             
             
$cekbox = isset($_POST['cekbox']) ? $_POST['cekbox'] : null;
if ($cekbox) {
       // §2b (2026-11-19, perintah user): hasil per anggota dikumpulkan lalu SATU ringkasan di akhir
       require_once __DIR__ . '/inc/cek_login.php';
       $unit_saya = unit_user();
       $cekbox = array_values((array) $cekbox);
       $jml_bayar = 0;
       $jml_tunggak = 0;
       $dilewati = array();
       foreach ($cekbox as $value) {
        // §2b (2026-11-19, perintah user): pengaman per anggota sebelum proses (anggota yang tidak lolos dilewati,
        // masuk ringkasan, anggota berikutnya tetap diproses).
        $value = (string) $value;
        $cek_ang = db_satu("select cif, unit, Cust_Short_name from loan where no_anggota = ?", 's', $value);
        if (!$cek_ang) { $dilewati[] = "No Anggota $value (tidak ditemukan)"; continue; }
        $label_ang = 'CIF ' . $cek_ang['cif'] . ' AN ' . $cek_ang['Cust_Short_name'];
        if ($unit_saya === '' || (string) $cek_ang['unit'] !== $unit_saya) { $dilewati[] = "CIF {$cek_ang['cif']} (bukan anggota unit Anda)"; continue; }
        // kunci per CIF lalu cek ulang: keputusan user 7a, satu anggota hanya sekali sehari (tanggal bayar terakhir = tanggal sistem -> dilewati)
        $kunci_cif = 'realisasi_tag_' . $cek_ang['cif'];
        db_satu("SELECT GET_LOCK(?, 30) AS kunci", 's', $kunci_cif);
        $cek_bayar = db_satu("select last_payment from loan where no_anggota = ?", 's', $value);
        if ($cek_bayar && substr((string) $cek_bayar['last_payment'], 0, 10) === substr((string) $tgl_system, 0, 10)) {
            db_satu("SELECT RELEASE_LOCK(?) AS lepas", 's', $kunci_cif);
            $dilewati[] = "$label_ang (sudah direalisasi hari ini)";
            continue;
        }
        // kunci nomor jurnal per unit (max(nomor_jurnal)+1) sampai commit, lalu semua penulisan anggota ini satu transaksi
        // (header jurnal_umum memakai INSERT IGNORE: nomor_jurnal adalah PK global, max+1 per unit bisa bentrok dengan nomor unit lain;
        //  versi lama insert itu gagal diam-diam dan data lain tetap tersimpan -> dipertahankan, tidak membatalkan transaksi)
        $kunci_jurnal = 'nomor_jurnal_' . $unit_saya;
        db_satu("SELECT GET_LOCK(?, 30) AS kunci", 's', $kunci_jurnal);
        $koneksi_transaksi = db_ni();
        mysqli_begin_transaction($koneksi_transaksi);
        $gagal_awal = db_jumlah_gagal();
        $kategori = 'bayar';
                   
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
                $row_sal=db_satu("select(sum(KREDIT) - sum(DEBIT))as saldo from simpanan WHERE cif =? and type not in ('05','02','04','07','10')", 's', (string) $cif);
                $sisa=$row_sal['saldo'];
                $row_sal=db_satu("select(sum(KREDIT) - sum(DEBIT))as blok from simpanan WHERE type in ('05','02','04','07','10') and cif =?", 's', (string) $cif);
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
 
 
if ($saldo < $angsuran){ $kategori = 'tunggak'; // §2b ringkasan    
                // §2b (2026-11-16, perintah user): dulu tanggal ditulis '<tanggal>-1' yang tersimpan sebagai jam 01:00:00
                // (cetak CS yang mencocokkan tanggal tidak menemukan baris itu); kini tanggal murni tanpa '-1'.
                db_jalankan("Update loan set next_schedule=?,ke=?+1 where no_anggota  =?", 'sss', (string) $next_schedule1, (string) $ke_tenor, (string) $value);
                                 
                //mysql_query("insert  into tunggakan (tgl_tunggak,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,blok)VALUES('$tanggal','$no_anggota','$unit','$cif','$code_kel','0','01','$pokok','$userid','$ket','$cao','') ") or die(mysql_error("GAGAL INPUT"));
               
            /* §2b: masuk ringkasan (menunggak) */
 
}else{

$cek_tunggak=db_satu("select sum(KREDIT)- sum(DEBIT) as tunggak from tunggakan WHERE cif=?", 's', (string) $cif);
            $ada_tunggak=$cek_tunggak['tunggak'];    
            if($ada_tunggak>0){
db_jalankan("insert  into tunggakan (tgl_tunggak,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,blok)VALUES(?,?,?,?,?,?,'01','0',?,?,?,'') ", 'sssssssss', (string) $tanggal, (string) $no_anggota, (string) $unit, (string) $cif, (string) $code_kel, (string) $pokok, (string) $userid, (string) $ket, (string) $cao);

db_jalankan("insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif,AO)VALUES('',?,?,?,'0',?,'03',?,?,'REALISASI POKOK',?,(select cao from loan where cif=? limit 1))", 'ssssssss', (string) $tanggal, (string) $unit, (string) $no_anggota, (string) $pokok, (string) $ket, (string) $userid, (string) $cif, (string) $cif);
               
                db_jalankan("Update  loan set next_schedule =?,os =os-?,saldo_margin=saldo_margin-?,run_tenor=?,ke=?+1 ,last_payment=? where no_anggota  =?", 'sssssss', (string) $next_schedule, (string) $angsuran, (string) $ijaroh, (string) $pb, (string) $ke_tenor, (string) $tanggal, (string) $value);
                db_jalankan("insert  into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,tgl_input,kode_transaksi)VALUES(?,?,?,?,?,?,'03','0',?,?,?,?,?) ", 'sssssssssss', (string) $tanggal, (string) $no_anggota, (string) $unit, (string) $cif, (string) $code_kel, (string) $angsuran, (string) $userid, (string) $ket, (string) $cao, (string) $tgl_input, (string) $kode_tran);
                db_jalankan("Update  pinjaman_detail set jumlah_bayar =?,tgl_bayar=? where id_pinjam  =? and cicilan=?", 'ssss', (string) $angsuran, (string) $tanggal, (string) $value, (string) $pb);
                db_jalankan("Update  pinjaman_detail_jatpo set jumlah_bayar =?,tgl_bayar=? where id_pinjam  =? and cicilan=?", 'ssss', (string) $angsuran, (string) $tanggal, (string) $value, (string) $pb);
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
                // §2b (2026-11-19, perintah user): header jurnal_umum (dulu REST hanya mencatat tabel_transaksi)
                db_jalankan("insert ignore into jurnal_umum(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values(?,?,?,?)", 'ssss', (string) $nomor_jurnal, (string) $kode_tran, (string) $tanggal, (string) $unit);
 		db_jalankan("Update  tagihan_cs set status_realisasi ='BAYAR'  where cif  =? and tgl_tagih=?", 'ss', (string) $cif, (string) $tanggal);

               
           
            /* §2b: masuk ringkasan */


}else{
   
                db_jalankan("insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif,AO)VALUES('',?,?,?,'0',?,'03',?,?,'REALISASI POKOK',?,(select cao from loan where cif=? limit 1))", 'ssssssss', (string) $tanggal, (string) $unit, (string) $no_anggota, (string) $pokok, (string) $ket, (string) $userid, (string) $cif, (string) $cif);
               
                db_jalankan("Update  loan set next_schedule =?,os =os-?,saldo_margin=saldo_margin-?,run_tenor=?,ke=?+1 ,last_payment=? where no_anggota  =?", 'sssssss', (string) $next_schedule, (string) $angsuran, (string) $ijaroh, (string) $pb, (string) $ke_tenor, (string) $tanggal, (string) $value);
                db_jalankan("insert  into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,tgl_input,kode_transaksi)VALUES(?,?,?,?,?,?,'03','0',?,?,?,?,?) ", 'sssssssssss', (string) $tanggal, (string) $no_anggota, (string) $unit, (string) $cif, (string) $code_kel, (string) $angsuran, (string) $userid, (string) $ket, (string) $cao, (string) $tgl_input, (string) $kode_tran);
                db_jalankan("Update  pinjaman_detail set jumlah_bayar =?,tgl_bayar=? where id_pinjam  =? and cicilan=?", 'ssss', (string) $angsuran, (string) $tanggal, (string) $value, (string) $pb);
                db_jalankan("Update  pinjaman_detail_jatpo set jumlah_bayar =?,tgl_bayar=? where id_pinjam  =? and cicilan=?", 'ssss', (string) $angsuran, (string) $tanggal, (string) $value, (string) $pb);
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
                // §2b (2026-11-19, perintah user): header jurnal_umum (dulu REST hanya mencatat tabel_transaksi)
                db_jalankan("insert ignore into jurnal_umum(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values(?,?,?,?)", 'ssss', (string) $nomor_jurnal, (string) $kode_tran, (string) $tanggal, (string) $unit);
 		db_jalankan("Update  tagihan_cs set status_realisasi ='BAYAR'  where cif  =? and tgl_tagih=?", 'ss', (string) $cif, (string) $tanggal);

               
           
            /* §2b: masuk ringkasan */
           }
}
       
}elseif($status_app=="REST_POKOK_MARGIN"){
 
db_jalankan("INSERT INTO pinjaman_detail_rencana(id,id_pinjam,cicilan,angsuran_pokok,bunga,tgl_jatuh_tempo,tgl_bayar,jumlah_bayar,ket,cif,unit,ao)
                    VALUES(?,?,?,?,?,?,'','','',?,?,?)", 'sssssssss', (string) $new_id, (string) $no_anggota, (string) $urut, (string) $pokok, (string) $ijaroh, (string) $next_schedule1, (string) $cif, (string) $unit, (string) $cao);
                 
 
if ($saldo < $angsuran){ $kategori = 'tunggak'; // §2b ringkasan    
                db_jalankan("Update loan set next_schedule=?,ke=?+1 where no_anggota  =?", 'sss', (string) $next_schedule1, (string) $ke_tenor, (string) $value);
                $ke_n=db_satu("select ke from loan WHERE cif =?", 's', (string) $cif);
                $ke_lagi=$ke_n['ke'];
                 
                //mysql_query("insert  into tunggakan (tgl_tunggak,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,blok)VALUES('$tanggal','$no_anggota','$unit','$cif','$code_kel','0','01','$pokok','$userid','$ket','$cao','') ") or die(mysql_error("GAGAL INPUT"));
                //mysql_query("insert  into tunggakan (tgl_tunggak,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,blok)VALUES('$tanggal','$no_anggota','$unit','$cif','$code_kel','0','02','$ijaroh','$userid','$ket','$cao','') ") or die(mysql_error("GAGAL INPUT"));
                 
                if ($maturity_date < $tgl_system){
                             
                 
        db_jalankan("INSERT INTO pinjaman_detail (id,id_pinjam,cicilan,angsuran_pokok,bunga,tgl_jatuh_tempo,tgl_bayar,jumlah_bayar,ket,cif,unit,ao)
                values(?,?,?,?,?,?,'','0','JATPO',?,?,?)", 'sssssssss', (string) $new_id, (string) $no_anggota, (string) $urut, (string) $pokok, (string) $ijaroh, (string) $next_schedule1, (string) $cif, (string) $unit, (string) $cao);
     
                    db_jalankan("INSERT INTO pinjaman_detail_jatpo(id,id_pinjam,cicilan,angsuran_pokok,bunga,tgl_jatuh_tempo,tgl_bayar,jumlah_bayar,ket)
                    VALUES(?,?,?,?,?,?,'','','')", 'ssssss', (string) $new_id, (string) $no_anggota, (string) $urut, (string) $pokok, (string) $ijaroh, (string) $next_schedule1);
                    //mysql_query("Update  loan set next_schedule ='$next_schedule1-1',ke=ke+1 where no_anggota  ='" . $value . "'") or die(mysql_error("GAGAL INPUT"));
                    //mysql_query("delete from tunggakan where cif ='$cif' and tgl_tunggak ='$tanggal' ");
                     
                     
                }else {
                    /* §2b: masuk ringkasan (menunggak) */
                     
                }            
         
        }    
        else{
$cek_tunggak=db_satu("select sum(KREDIT)- sum(DEBIT) as tunggak from tunggakan WHERE cif=?", 's', (string) $cif);
            $ada_tunggak=$cek_tunggak['tunggak'];    
            if($ada_tunggak>0){
db_jalankan("insert  into tunggakan (tgl_tunggak,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,blok)VALUES(?,?,?,?,?,?,'01','0',?,?,?,'') ", 'sssssssss', (string) $tanggal, (string) $no_anggota, (string) $unit, (string) $cif, (string) $code_kel, (string) $pokok, (string) $userid, (string) $ket, (string) $cao);
                db_jalankan("insert  into tunggakan (tgl_tunggak,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,blok)VALUES(?,?,?,?,?,?,'02','0',?,?,?,'') ", 'sssssssss', (string) $tanggal, (string) $no_anggota, (string) $unit, (string) $cif, (string) $code_kel, (string) $ijaroh, (string) $userid, (string) $ket, (string) $cao);
db_jalankan("insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif,AO)VALUES('',?,?,?,'0',?,'03',?,?,'REALISASI POKOK',?,(select cao from loan where cif=? limit 1))", 'ssssssss', (string) $tanggal, (string) $unit, (string) $no_anggota, (string) $pokok, (string) $ket, (string) $userid, (string) $cif, (string) $cif);
                db_jalankan("insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif,AO)VALUES('',?,?,?,'0',?,'03',?,?,'REALISASI BAGHAS',?,(select cao from loan where cif=? limit 1))", 'ssssssss', (string) $tanggal, (string) $unit, (string) $no_anggota, (string) $ijaroh, (string) $ket, (string) $userid, (string) $cif, (string) $cif);
 
                db_jalankan("Update  loan set next_schedule =?,os =os-?,saldo_margin=saldo_margin-?,run_tenor=?,ke=?+1,last_payment=? where no_anggota  =?", 'sssssss', (string) $next_schedule, (string) $angsuran, (string) $ijaroh, (string) $pb, (string) $ke_tenor, (string) $tanggal, (string) $value);
                 
 
                 
db_jalankan("insert  into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,blok,tgl_input,kode_transaksi)VALUES(?,?,?,?,?,?,'03','0',?,?,'',?,'2',?,?) ", 'sssssssssss', (string) $tanggal, (string) $no_anggota, (string) $unit, (string) $cif, (string) $code_kel, (string) $angsuran, (string) $userid, (string) $ket, (string) $cao, (string) $tgl_input, (string) $kode_tran);
                $jurnal_umum=array_values((array) db_satu("SELECT max(nomor_jurnal) FROM jurnal_umum where unit =? ORDER BY tanggal_selesai DESC", 's', (string) $unit));
                $nomor_jurnal=$jurnal_umum[0]+1;

db_jalankan("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                values(?,?,'2101000',?,'Bukti SYSTEM',?,?,?)", 'ssssss', (string) $unit, (string) $kode_tran, (string) $tanggal, (string) $ket, (string) $angsuran, (string) $userid);
 
                 db_jalankan("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                values(?,?,'1411000',?,'Bukti SYSTEM',?,?,?)", 'ssssss', (string) $unit, (string) $kode_tran, (string) $tanggal, (string) $ket, (string) $angsuran, (string) $userid);
                 
                db_jalankan("insert ignore into jurnal_umum(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values(?,?,?,?)", 'ssss', (string) $nomor_jurnal, (string) $kode_tran, (string) $tanggal, (string) $unit);
                //Realisasi Piutang Margin    
                db_jalankan("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                            values(?,?,'1421000',?,'Bukti SYSTEM',?,?,?)", 'ssssss', (string) $unit, (string) $kode_tran, (string) $tanggal, (string) $ket, (string) $ijaroh, (string) $userid);
                //Realiasasi Murabahah Debit pada Piutang Murabahah total jumlah Pokok dan margin
                db_jalankan("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                values(?,?,'41002',?,'Bukti SYSTEM',?,?,?)", 'ssssss', (string) $unit, (string) $kode_tran, (string) $tanggal, (string) $ket, (string) $ijaroh, (string) $userid);
		db_jalankan("Update  tagihan_cs set status_realisasi ='BAYAR'  where cif  =? and tgl_tagih=?", 'ss', (string) $cif, (string) $tanggal);

 
}else{
 
                db_jalankan("insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif,AO)VALUES('',?,?,?,'0',?,'03',?,?,'REALISASI POKOK',?,(select cao from loan where cif=? limit 1))", 'ssssssss', (string) $tanggal, (string) $unit, (string) $no_anggota, (string) $pokok, (string) $ket, (string) $userid, (string) $cif, (string) $cif);
                db_jalankan("insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif,AO)VALUES('',?,?,?,'0',?,'03',?,?,'REALISASI BAGHAS',?,(select cao from loan where cif=? limit 1))", 'ssssssss', (string) $tanggal, (string) $unit, (string) $no_anggota, (string) $ijaroh, (string) $ket, (string) $userid, (string) $cif, (string) $cif);
 
                db_jalankan("Update  loan set next_schedule =?,os =os-?,saldo_margin=saldo_margin-?,run_tenor=?,ke=?+1,last_payment=? where no_anggota  =?", 'sssssss', (string) $next_schedule, (string) $angsuran, (string) $ijaroh, (string) $pb, (string) $ke_tenor, (string) $tanggal, (string) $value);
                db_jalankan("insert  into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,tgl_input,kode_transaksi)VALUES(?,?,?,?,?,?,'03','0',?,?,?,?,?) ", 'sssssssssss', (string) $tanggal, (string) $no_anggota, (string) $unit, (string) $cif, (string) $code_kel, (string) $angsuran, (string) $userid, (string) $ket, (string) $cao, (string) $tgl_input, (string) $kode_tran);
                $jurnal_umum=array_values((array) db_satu("SELECT max(nomor_jurnal) FROM jurnal_umum where unit =? ORDER BY tanggal_selesai DESC", 's', (string) $unit));
                $nomor_jurnal=$jurnal_umum[0]+1;
db_jalankan("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                values(?,?,'2101000',?,'Bukti SYSTEM',?,?,?)", 'ssssss', (string) $unit, (string) $kode_tran, (string) $tanggal, (string) $ket, (string) $angsuran, (string) $userid);
 
                 db_jalankan("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                values(?,?,'1411000',?,'Bukti SYSTEM',?,?,?)", 'ssssss', (string) $unit, (string) $kode_tran, (string) $tanggal, (string) $ket, (string) $angsuran, (string) $userid);
                 
                db_jalankan("insert ignore into jurnal_umum(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values(?,?,?,?)", 'ssss', (string) $nomor_jurnal, (string) $kode_tran, (string) $tanggal, (string) $unit);
                //Realisasi Piutang Margin    
                db_jalankan("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                            values(?,?,'1421000',?,'Bukti SYSTEM',?,?,?)", 'ssssss', (string) $unit, (string) $kode_tran, (string) $tanggal, (string) $ket, (string) $ijaroh, (string) $userid);
                //Realiasasi Murabahah Debit pada Piutang Murabahah total jumlah Pokok dan margin
                db_jalankan("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                values(?,?,'41002',?,'Bukti SYSTEM',?,?,?)", 'ssssss', (string) $unit, (string) $kode_tran, (string) $tanggal, (string) $ket, (string) $ijaroh, (string) $userid);
		db_jalankan("Update  tagihan_cs set status_realisasi ='BAYAR'  where cif  =? and tgl_tagih=?", 'ss', (string) $cif, (string) $tanggal);

 
 
                }
            }
            /* §2b: masuk ringkasan */
             

        }elseif($status_app=="MUSYAROKAH"){

           			$cek_omzet=db_satu("SELECT nominal from omzet WHERE tanggal =? and cif =?", 'ss', (string) $tanggal, (string) $cif);
        			$omzet=$cek_omzet['nominal']; 

					$cek_nisbah=db_satu("SELECT nisbah from nisbah WHERE cif =?", 's', (string) $cif);
        			$nisbah=$cek_nisbah['nisbah']; 

					$porsi= $omzet * $nisbah;
                    $angsuran_pokok = $angsuran - $nisbah_mus - 2500;
                

            db_jalankan("INSERT INTO pinjaman_detail_rencana(id,id_pinjam,cicilan,angsuran_pokok,bunga,tgl_jatuh_tempo,tgl_bayar,jumlah_bayar,ket,cif,unit,ao)
                    VALUES(?,?,?,?,?,?,'','','',?,?,?)", 'sssssssss', (string) $new_id, (string) $no_anggota, (string) $urut, (string) $pokok, (string) $ijaroh, (string) $next_schedule1, (string) $cif, (string) $unit, (string) $cao);
                 
 
if ($saldo < $angsuran){ $kategori = 'tunggak'; // §2b ringkasan    
                db_jalankan("Update loan set next_schedule=?,ke=?+1 where no_anggota  =?", 'sss', (string) $next_schedule1, (string) $ke_tenor, (string) $value);
                $ke_n=db_satu("select ke from loan WHERE cif =?", 's', (string) $cif);
                $ke_lagi=$ke_n['ke'];
                 
                        
                if ($maturity_date < $tgl_system){
                             
                 
        db_jalankan("INSERT INTO pinjaman_detail (id,id_pinjam,cicilan,angsuran_pokok,bunga,tgl_jatuh_tempo,tgl_bayar,jumlah_bayar,ket,cif,unit,ao)
                values(?,?,?,?,?,?,'','0','JATPO',?,?,?)", 'sssssssss', (string) $new_id, (string) $no_anggota, (string) $urut, (string) $pokok, (string) $ijaroh, (string) $next_schedule1, (string) $cif, (string) $unit, (string) $cao);
     
                    db_jalankan("INSERT INTO pinjaman_detail_jatpo(id,id_pinjam,cicilan,angsuran_pokok,bunga,tgl_jatuh_tempo,tgl_bayar,jumlah_bayar,ket)
                    VALUES(?,?,?,?,?,?,'','','')", 'ssssss', (string) $new_id, (string) $no_anggota, (string) $urut, (string) $pokok, (string) $ijaroh, (string) $next_schedule1);
                     
                     
                }else {
                    /* §2b: masuk ringkasan (menunggak) */
                     
                }            
         
        }    
        else{
$cek_tunggak=db_satu("select sum(KREDIT)- sum(DEBIT) as tunggak from tunggakan WHERE cif=?", 's', (string) $cif);
            $ada_tunggak=$cek_tunggak['tunggak'];    
            if($ada_tunggak>0){
db_jalankan("insert  into tunggakan (tgl_tunggak,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,blok)VALUES(?,?,?,?,?,?,'01','0',?,?,?,'') ", 'sssssssss', (string) $tanggal, (string) $no_anggota, (string) $unit, (string) $cif, (string) $code_kel, (string) $angsuran_pokok, (string) $userid, (string) $ket, (string) $cao);
//mysql_query("insert  into tunggakan (tgl_tunggak,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,blok)VALUES('$tanggal','$no_anggota','$unit','$cif','$code_kel','$nisbah_mus','01','0','$userid','$ket','$cao','') ") or die(mysql_error("GAGAL INPUT"));

db_jalankan("insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif,AO)VALUES('',?,?,?,'0',?,'03',?,?,'REALISASI POKOK',?,(select cao from loan where cif=? limit 1))", 'ssssssss', (string) $tanggal, (string) $unit, (string) $no_anggota, (string) $angsuran, (string) $ket, (string) $userid, (string) $cif, (string) $cif);
db_jalankan("insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif,AO)VALUES('',?,?,?,'0',?,'03',?,?,'REALISASI BAGHAS',?,(select cao from loan where cif=? limit 1))", 'ssssssss', (string) $tanggal, (string) $unit, (string) $no_anggota, (string) $nisbah_mus, (string) $ket, (string) $userid, (string) $cif, (string) $cif);
db_jalankan("Update  loan set next_schedule =?,os =os-?,run_tenor=?,ke=?+1,last_payment=? where cif  =?", 'ssssss', (string) $next_schedule, (string) $angsuran_pokok, (string) $pb, (string) $ke_tenor, (string) $tanggal, (string) $cif);
db_jalankan("update pinjaman_detail_mus set nisbah_anggota =?,angsuran_pokok = ?,nisbah=?,tgl_bayar=? where cif  =? and cicilan=?", 'ssssss', (string) $nisbah_mus, (string) $angsuran_pokok, (string) $nisbah_mus, (string) $tanggal, (string) $cif, (string) $pb);                 
 
                 
db_jalankan("insert  into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,blok,tgl_input,kode_transaksi)VALUES(?,?,?,?,?,?,'03','0',?,?,'',?,'2',?,?) ", 'sssssssssss', (string) $tanggal, (string) $no_anggota, (string) $unit, (string) $cif, (string) $code_kel, (string) $angsuran, (string) $userid, (string) $ket, (string) $cao, (string) $tgl_input, (string) $kode_tran);
              $jurnal_umum=array_values((array) db_satu("SELECT max(nomor_jurnal) FROM jurnal_umum where unit =? ORDER BY tanggal_selesai DESC", 's', (string) $unit));
                $nomor_jurnal=$jurnal_umum[0]+1;
                  db_jalankan("insert ignore into jurnal_umum(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values(?,?,?,?)", 'ssss', (string) $nomor_jurnal, (string) $kode_tran, (string) $tanggal, (string) $unit);
              
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
 
               db_jalankan("insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif,AO)VALUES('',?,?,?,'0',?,'03',?,?,'REALISASI POKOK',?,(select cao from loan where cif=? limit 1))", 'ssssssss', (string) $tanggal, (string) $unit, (string) $no_anggota, (string) $pokok, (string) $ket, (string) $userid, (string) $cif, (string) $cif);
              db_jalankan("update pinjaman_detail_mus set nisbah_anggota =?,angsuran_pokok = ?,nisbah=?,tgl_bayar=? where cif  =? and cicilan=?", 'ssssss', (string) $porsi, (string) $angsuran_pokok, (string) $nisbah_mus, (string) $tanggal, (string) $cif, (string) $pb);             
                db_jalankan("Update  loan set next_schedule =?,os =os-?,run_tenor=?,ke=?+1,last_payment=? where cif  =?", 'ssssss', (string) $next_schedule, (string) $angsuran_pokok, (string) $pb, (string) $ke_tenor, (string) $tanggal, (string) $cif);
                db_jalankan("insert  into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,tgl_input,kode_transaksi)VALUES(?,?,?,?,?,?,'03','0',?,?,?,?,?) ", 'sssssssssss', (string) $tanggal, (string) $no_anggota, (string) $unit, (string) $cif, (string) $code_kel, (string) $angsuran, (string) $userid, (string) $ket, (string) $cao, (string) $tgl_input, (string) $kode_tran);
                $jurnal_umum=array_values((array) db_satu("SELECT max(nomor_jurnal) FROM jurnal_umum where unit =? ORDER BY tanggal_selesai DESC", 's', (string) $unit));
                $nomor_jurnal=$jurnal_umum[0]+1;
                 
                db_jalankan("insert ignore into jurnal_umum(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values(?,?,?,?)", 'ssss', (string) $nomor_jurnal, (string) $kode_tran, (string) $tanggal, (string) $unit);
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
            /* §2b: masuk ringkasan */



        }elseif($status_app=="MURAB"){
// §2b (2026-11-19, keputusan user 8a): MURAB dulu tidak cek saldo (simpanan bisa minus). Kini sama dengan cabang lain:
// saldo kurang -> jadwal dimundurkan (next_schedule, ke), JATPO bila sudah lewat jatuh tempo; simpanan tidak dipotong.
if ($saldo < $angsuran){ $kategori = 'tunggak';
                db_jalankan("Update loan set next_schedule=?,ke=?+1 where no_anggota  =?", 'sss', (string) $next_schedule1, (string) $ke_tenor, (string) $value);
                if ($maturity_date < $tgl_system){
        db_jalankan("INSERT INTO pinjaman_detail (id,id_pinjam,cicilan,angsuran_pokok,bunga,tgl_jatuh_tempo,tgl_bayar,jumlah_bayar,ket,cif,unit,ao)
                values(?,?,?,?,?,?,'','0','JATPO',?,?,?)", 'sssssssss', (string) $new_id, (string) $no_anggota, (string) $urut, (string) $pokok, (string) $ijaroh, (string) $next_schedule1, (string) $cif, (string) $unit, (string) $cao);
                    db_jalankan("INSERT INTO pinjaman_detail_jatpo(id,id_pinjam,cicilan,angsuran_pokok,bunga,tgl_jatuh_tempo,tgl_bayar,jumlah_bayar,ket)
                    VALUES(?,?,?,?,?,?,'','','')", 'ssssss', (string) $new_id, (string) $no_anggota, (string) $urut, (string) $pokok, (string) $ijaroh, (string) $next_schedule1);
                }
}else{ // §2b saldo cukup: proses MURAB lama tanpa perubahan
            
$cek_tunggak=db_satu("select sum(KREDIT)- sum(DEBIT) as tunggak from tunggakan WHERE cif=?", 's', (string) $cif);
            $ada_tunggak=$cek_tunggak['tunggak'];    

            if($ada_tunggak>0){
db_jalankan("insert  into tunggakan (tgl_tunggak,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,blok)VALUES(?,?,?,?,?,?,'01','0',?,?,?,'') ", 'sssssssss', (string) $tanggal, (string) $no_anggota, (string) $unit, (string) $cif, (string) $code_kel, (string) $pokok, (string) $userid, (string) $ket, (string) $cao);
                db_jalankan("insert  into tunggakan (tgl_tunggak,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,blok)VALUES(?,?,?,?,?,?,'02','0',?,?,?,'') ", 'sssssssss', (string) $tanggal, (string) $no_anggota, (string) $unit, (string) $cif, (string) $code_kel, (string) $ijaroh, (string) $userid, (string) $ket, (string) $cao);
db_jalankan("insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif,AO)VALUES('',?,?,?,'0',?,'03',?,?,'REALISASI POKOK',?,(select cao from loan where cif=? limit 1))", 'ssssssss', (string) $tanggal, (string) $unit, (string) $no_anggota, (string) $pokok, (string) $ket, (string) $userid, (string) $cif, (string) $cif);
                db_jalankan("insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif,AO)VALUES('',?,?,?,'0',?,'03',?,?,'REALISASI BAGHAS',?,(select cao from loan where cif=? limit 1))", 'ssssssss', (string) $tanggal, (string) $unit, (string) $no_anggota, (string) $ijaroh, (string) $ket, (string) $userid, (string) $cif, (string) $cif);
 
                db_jalankan("Update  loan set next_schedule =?,os =os-?,saldo_margin=saldo_margin-?,run_tenor=?,ke=?+1,last_payment=? where no_anggota  =?", 'sssssss', (string) $next_schedule, (string) $angsuran, (string) $ijaroh, (string) $pb, (string) $ke_tenor, (string) $tanggal, (string) $value);
                 
 
                 
db_jalankan("insert  into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,blok,tgl_input,kode_transaksi)VALUES(?,?,?,?,?,?,'03','0',?,?,'',?,'2',?,?) ", 'sssssssssss', (string) $tanggal, (string) $no_anggota, (string) $unit, (string) $cif, (string) $code_kel, (string) $angsuran, (string) $userid, (string) $ket, (string) $cao, (string) $tgl_input, (string) $kode_tran);
                             $jurnal_umum=array_values((array) db_satu("SELECT max(nomor_jurnal) FROM jurnal_umum where unit =? ORDER BY tanggal_selesai DESC", 's', (string) $unit));
                $nomor_jurnal=$jurnal_umum[0]+1;
		if($deal_produk =='2'){
$margin = $ijaroh - 2500;
db_jalankan("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                values(?,?,'1413000',?,'Bukti SYSTEM',?,?,?)", 'ssssss', (string) $unit, (string) $kode_tran, (string) $tanggal, (string) $ket, (string) $angsuran, (string) $userid);
                 
                db_jalankan("insert ignore into jurnal_umum(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values(?,?,?,?)", 'ssss', (string) $nomor_jurnal, (string) $kode_tran, (string) $tanggal, (string) $unit);
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
                 
                db_jalankan("insert ignore into jurnal_umum(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values(?,?,?,?)", 'ssss', (string) $nomor_jurnal, (string) $kode_tran, (string) $tanggal, (string) $unit);
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
                 
                db_jalankan("insert ignore into jurnal_umum(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values(?,?,?,?)", 'ssss', (string) $nomor_jurnal, (string) $kode_tran, (string) $tanggal, (string) $unit);
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
 
                db_jalankan("insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif,AO)VALUES('',?,?,?,'0',?,'03',?,?,'REALISASI POKOK',?,(select cao from loan where cif=? limit 1))", 'ssssssss', (string) $tanggal, (string) $unit, (string) $no_anggota, (string) $pokok, (string) $ket, (string) $userid, (string) $cif, (string) $cif);
                db_jalankan("insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif,AO)VALUES('',?,?,?,'0',?,'03',?,?,'REALISASI BAGHAS',?,(select cao from loan where cif=? limit 1))", 'ssssssss', (string) $tanggal, (string) $unit, (string) $no_anggota, (string) $ijaroh, (string) $ket, (string) $userid, (string) $cif, (string) $cif);
 
                db_jalankan("Update  loan set next_schedule =?,os =os-?,saldo_margin=saldo_margin-?,run_tenor=?,ke=?+1,last_payment=? where no_anggota  =?", 'sssssss', (string) $next_schedule, (string) $angsuran, (string) $ijaroh, (string) $pb, (string) $ke_tenor, (string) $tanggal, (string) $value);
                db_jalankan("insert  into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,tgl_input,kode_transaksi)VALUES(?,?,?,?,?,?,'03','0',?,?,?,?,?) ", 'sssssssssss', (string) $tanggal, (string) $no_anggota, (string) $unit, (string) $cif, (string) $code_kel, (string) $angsuran, (string) $userid, (string) $ket, (string) $cao, (string) $tgl_input, (string) $kode_tran);
                  $jurnal_umum=array_values((array) db_satu("SELECT max(nomor_jurnal) FROM jurnal_umum where unit =? ORDER BY tanggal_selesai DESC", 's', (string) $unit));
                $nomor_jurnal=$jurnal_umum[0]+1;

if($deal_produk =='2'){
$margin = $ijaroh - 2500;
 db_jalankan("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                values(?,?,'1413000',?,'Bukti SYSTEM',?,?,?)", 'ssssss', (string) $unit, (string) $kode_tran, (string) $tanggal, (string) $ket, (string) $angsuran, (string) $userid);
                 
                db_jalankan("insert ignore into jurnal_umum(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values(?,?,?,?)", 'ssss', (string) $nomor_jurnal, (string) $kode_tran, (string) $tanggal, (string) $unit);
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
                 
                db_jalankan("insert ignore into jurnal_umum(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values(?,?,?,?)", 'ssss', (string) $nomor_jurnal, (string) $kode_tran, (string) $tanggal, (string) $unit);
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
                 
                db_jalankan("insert ignore into jurnal_umum(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values(?,?,?,?)", 'ssss', (string) $nomor_jurnal, (string) $kode_tran, (string) $tanggal, (string) $unit);
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
            
            } // §2b akhir cek saldo MURAB
            /* §2b: masuk ringkasan */

        }
        
        else{
db_jalankan("INSERT INTO pinjaman_detail_rencana(id,id_pinjam,cicilan,angsuran_pokok,bunga,tgl_jatuh_tempo,tgl_bayar,jumlah_bayar,ket,cif,unit,ao)
                    VALUES(?,?,?,?,?,?,'','','',?,?,?)", 'sssssssss', (string) $new_id, (string) $no_anggota, (string) $urut, (string) $pokok, (string) $ijaroh, (string) $next_schedule1, (string) $cif, (string) $unit, (string) $cao);
                 
 
if ($saldo < $angsuran){ $kategori = 'tunggak'; // §2b ringkasan    
                db_jalankan("Update loan set next_schedule=?,ke=?+1 where no_anggota  =?", 'sss', (string) $next_schedule1, (string) $ke_tenor, (string) $value);
                $ke_n=db_satu("select ke from loan WHERE cif =?", 's', (string) $cif);
                $ke_lagi=$ke_n['ke'];
                 
                //mysql_query("insert  into tunggakan (tgl_tunggak,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,blok)VALUES('$tanggal','$no_anggota','$unit','$cif','$code_kel','0','01','$pokok','$userid','$ket','$cao','') ") or die(mysql_error("GAGAL INPUT"));
                //mysql_query("insert  into tunggakan (tgl_tunggak,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,blok)VALUES('$tanggal','$no_anggota','$unit','$cif','$code_kel','0','02','$ijaroh','$userid','$ket','$cao','') ") or die(mysql_error("GAGAL INPUT"));
                 
                if ($maturity_date < $tgl_system){
                             
                 
        db_jalankan("INSERT INTO pinjaman_detail (id,id_pinjam,cicilan,angsuran_pokok,bunga,tgl_jatuh_tempo,tgl_bayar,jumlah_bayar,ket,cif,unit,ao)
                values(?,?,?,?,?,?,'','0','JATPO',?,?,?)", 'sssssssss', (string) $new_id, (string) $no_anggota, (string) $urut, (string) $pokok, (string) $ijaroh, (string) $next_schedule1, (string) $cif, (string) $unit, (string) $cao);
     
                    db_jalankan("INSERT INTO pinjaman_detail_jatpo(id,id_pinjam,cicilan,angsuran_pokok,bunga,tgl_jatuh_tempo,tgl_bayar,jumlah_bayar,ket)
                    VALUES(?,?,?,?,?,?,'','','')", 'ssssss', (string) $new_id, (string) $no_anggota, (string) $urut, (string) $pokok, (string) $ijaroh, (string) $next_schedule1);
                    //mysql_query("Update  loan set next_schedule ='$next_schedule1-1',ke=ke+1 where no_anggota  ='" . $value . "'") or die(mysql_error("GAGAL INPUT"));
                    //mysql_query("delete from tunggakan where cif ='$cif' and tgl_tunggak ='$tanggal' ");
                     
                     
                }else {
                    /* §2b: masuk ringkasan (menunggak) */
                     
                }            
         
        }    
        else{
$cek_tunggak=db_satu("select sum(KREDIT)- sum(DEBIT) as tunggak from tunggakan WHERE cif=?", 's', (string) $cif);
            $ada_tunggak=$cek_tunggak['tunggak'];    
            if($ada_tunggak>0){
db_jalankan("insert  into tunggakan (tgl_tunggak,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,blok)VALUES(?,?,?,?,?,?,'01','0',?,?,?,'') ", 'sssssssss', (string) $tanggal, (string) $no_anggota, (string) $unit, (string) $cif, (string) $code_kel, (string) $pokok, (string) $userid, (string) $ket, (string) $cao);
                db_jalankan("insert  into tunggakan (tgl_tunggak,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,blok)VALUES(?,?,?,?,?,?,'02','0',?,?,?,'') ", 'sssssssss', (string) $tanggal, (string) $no_anggota, (string) $unit, (string) $cif, (string) $code_kel, (string) $ijaroh, (string) $userid, (string) $ket, (string) $cao);
db_jalankan("insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif,AO)VALUES('',?,?,?,'0',?,'03',?,?,'REALISASI POKOK',?,(select cao from loan where cif=? limit 1))", 'ssssssss', (string) $tanggal, (string) $unit, (string) $no_anggota, (string) $pokok, (string) $ket, (string) $userid, (string) $cif, (string) $cif);
                db_jalankan("insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif,AO)VALUES('',?,?,?,'0',?,'03',?,?,'REALISASI BAGHAS',?,(select cao from loan where cif=? limit 1))", 'ssssssss', (string) $tanggal, (string) $unit, (string) $no_anggota, (string) $ijaroh, (string) $ket, (string) $userid, (string) $cif, (string) $cif);
 
                db_jalankan("Update  loan set next_schedule =?,os =os-?,saldo_margin=saldo_margin-?,run_tenor=?,ke=?+1,last_payment=? where no_anggota  =?", 'sssssss', (string) $next_schedule, (string) $angsuran, (string) $ijaroh, (string) $pb, (string) $ke_tenor, (string) $tanggal, (string) $value);
                 
 
                 
db_jalankan("insert  into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,blok,tgl_input,kode_transaksi)VALUES(?,?,?,?,?,?,'03','0',?,?,'',?,'2',?,?) ", 'sssssssssss', (string) $tanggal, (string) $no_anggota, (string) $unit, (string) $cif, (string) $code_kel, (string) $angsuran, (string) $userid, (string) $ket, (string) $cao, (string) $tgl_input, (string) $kode_tran);
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
                 
                db_jalankan("insert ignore into jurnal_umum(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values(?,?,?,?)", 'ssss', (string) $nomor_jurnal, (string) $kode_tran, (string) $tanggal, (string) $unit);
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
 
                db_jalankan("insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif,AO)VALUES('',?,?,?,'0',?,'03',?,?,'REALISASI POKOK',?,(select cao from loan where cif=? limit 1))", 'ssssssss', (string) $tanggal, (string) $unit, (string) $no_anggota, (string) $pokok, (string) $ket, (string) $userid, (string) $cif, (string) $cif);
                db_jalankan("insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif,AO)VALUES('',?,?,?,'0',?,'03',?,?,'REALISASI BAGHAS',?,(select cao from loan where cif=? limit 1))", 'ssssssss', (string) $tanggal, (string) $unit, (string) $no_anggota, (string) $ijaroh, (string) $ket, (string) $userid, (string) $cif, (string) $cif);
 
                db_jalankan("Update  loan set next_schedule =?,os =os-?,saldo_margin=saldo_margin-?,run_tenor=?,ke=?+1,last_payment=? where no_anggota  =?", 'sssssss', (string) $next_schedule, (string) $angsuran, (string) $ijaroh, (string) $pb, (string) $ke_tenor, (string) $tanggal, (string) $value);
                db_jalankan("insert  into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,tgl_input,kode_transaksi)VALUES(?,?,?,?,?,?,'03','0',?,?,?,?,?) ", 'sssssssssss', (string) $tanggal, (string) $no_anggota, (string) $unit, (string) $cif, (string) $code_kel, (string) $angsuran, (string) $userid, (string) $ket, (string) $cao, (string) $tgl_input, (string) $kode_tran);
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
                 
                db_jalankan("insert ignore into jurnal_umum(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values(?,?,?,?)", 'ssss', (string) $nomor_jurnal, (string) $kode_tran, (string) $tanggal, (string) $unit);
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
            /* §2b: masuk ringkasan */
}    
    }    
        // §2b: akhir anggota -> commit, atau batalkan bila ada query gagal
        if (db_jumlah_gagal() > $gagal_awal) {
            mysqli_rollback($koneksi_transaksi);
            $dilewati[] = "$label_ang (GAGAL, data tidak disimpan: " . db_error_terakhir() . ')';
        } else {
            mysqli_commit($koneksi_transaksi);
            if ($kategori === 'tunggak') { $jml_tunggak++; } else { $jml_bayar++; }
        }
        db_satu("SELECT RELEASE_LOCK(?) AS lepas", 's', $kunci_jurnal);
        db_satu("SELECT RELEASE_LOCK(?) AS lepas", 's', $kunci_cif);
}    
    // §2b: satu ringkasan lalu kembali ke yrs_list.php (dulu notif per anggota)
    $ringkas = "Realisasi selesai: $jml_bayar anggota bayar, $jml_tunggak anggota menunggak (jadwal dimundurkan).";
    if ($dilewati) {
        $ringkas .= "\nDilewati " . count($dilewati) . " anggota:\n- " . implode("\n- ", $dilewati);
    }
    notif_pergi($ringkas, 'yrs_list.php', $dilewati ? (($jml_bayar + $jml_tunggak) > 0 ? 'warning' : 'error') : 'success');
}else {
            notif_pergi('anda belum memilih data.', 'yrs_list.php');
    }
 
?>


