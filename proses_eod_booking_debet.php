<?php require __DIR__ . '/inc/hanya_cli.php'; require_once __DIR__ . '/koneksi_db.php'; 
date_default_timezone_set('Asia/Jakarta');

 
$con = db_ni();
if (mysqli_connect_errno()) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
    exit;
  }
 
// Turn autocommit off
mysqli_autocommit($con,FALSE);
 
 
 
include "function.php";

// 2026-11-22 (perintah user, P3-7: "ikuti pattern proses_eod_mob untuk semua produk"): kredit pendapatan margin Murabahah per
// deal sama dengan proses_eod_mob.php (jalur MURAB): deal 2 -> margin tanpa 2.500 ke 41002 + 2.500 per angsuran ke 2867000;
// deal 4 -> 41002 dua baris (margin tanpa 2.500 + 2.500); deal lain -> satu baris 41002. $margin sudah termasuk 2.500 (deal 2/4).
function booking_kredit_margin($con, $unit, $kode_tran, $tanggal, $ket2, $userid, $deal_produk, $margin, $jumlah_angsuran) {
    $duaribu = 2500 * $jumlah_angsuran;
    if ($deal_produk == '2' || $deal_produk == '4') {
        $sisa = $margin - $duaribu;
        $rek_duaribu = $deal_produk == '2' ? '2867000' : '41002';
        db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
            values('$unit','$kode_tran','41002','$tanggal','Bukti SYSTEM','$ket2','$sisa','$userid')");
        db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
            values('$unit','$kode_tran','$rek_duaribu','$tanggal','Bukti SYSTEM','$ket2','$duaribu','$userid')");
    } else {
        db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
            values('$unit','$kode_tran','41002','$tanggal','Bukti SYSTEM','$ket2','$margin','$userid')");
    }
}
 


 $cek= db_fetch_array(db_query($con,"select param_tgl from param_tgl WHERE nama_param_tgl ='EOD_MOB'"));
$tanggal = $cek['param_tgl']; 

// 2026-11-22 (perintah user, P3-7): dulu `$tanggal = '2025-07-31';` menimpa EOD_MOB -> booking baru tak pernah diproses
         
$select = db_query ($con,"Select booking_debet.cif as cif,loan.angsuran,loan.cao,Cust_Short_name,loan.os as os,deal_produk,loan.saldo_margin as saldo_margin,
no_anggota as norek ,code_kel,status_app,loan.pokok,run_tenor,loan.unit, loan.bulat as bulat,loan.ijaroh as ijaroh,loan.ke as ke_loan
from booking_debet left join loan on loan.cif = booking_debet.cif  where loan.os > 0 and status_booking='Booking Debet'  order by loan.unit");
// 2026-11-22 (P3-7): semua booking yang masih 'Booking Debet' diproses (dulu hanya tgl_booking = tanggal; booking hari Sabtu
// tak pernah sama dengan EOD_MOB). Pengaman jalan ulang: status berubah Done / Saldo Tidak Cukup / Tidak Ada Tunggakan.
while($data = db_fetch_array($select)){
 
        $norek=$data['norek'];
        $unit=$data ['unit'];
        // 2026-11-22 (P3-7): dulu tak terdefinisi (USERID kosong, tgl_input 0000, ke jadi 1); kini seperti file EOD lain
        $userid=$unit;
        $tgl_input=date("Y-m-d");
        $ke_tenor=$data['ke_loan'];
        $nisbah_mus=$data['ijaroh'];
        $angsuran=$data ['angsuran'];
        $angsuran_pokok=$data ['pokok'];
	$angsuran_pokok_asli =$data['pokok'];
        $ijaroh=$data ['ijaroh'];
        $code_kel=$data['code_kel'];
        $ke = $data['run_tenor'];
        $cao=$data['cao'];
        $ciff=$data['cif'];
        $saldo_margin=$data['saldo_margin'];
        $deal_produk=$data['deal_produk'];

        $os=$data['os'];
        $status_app=$data['status_app'];
        $run_tenor=$data['run_tenor'];
        $pb=$run_tenor + 1;
        $next_tenor=$run_tenor + 2;


        $bulat=$data['bulat'];      
        $next_schedule    = date('Y-m-d', strtotime('+7 days', strtotime($tanggal)));    
        
        $cek_saldo= db_fetch_array(db_query($con,"select sum(KREDIT)- sum(DEBIT) as saldo from simpanan WHERE cif ='".$ciff."' and TYPE not in ('04','05')"));
        $saldo_simpanan=$cek_saldo['saldo']; 
  
        $cek_tunggak= db_fetch_array(db_query($con,"select sum(KREDIT)- sum(DEBIT) as tunggak from tunggakan WHERE cif ='".$ciff."'"));
        $ada_tunggak=$cek_tunggak['tunggak']; 
        // 2026-11-22 (P3-7): deal 2/4 tunggakan per minggu = angsuran + 2.500 (proses_eod_mob), ft dibagi angsuran + 2.500
        $angsuran_ft = ($deal_produk == '2' || $deal_produk == '4') ? $angsuran + 2500 : $angsuran;
        $ft=round(compat_num(compat_bagi($ada_tunggak, $angsuran_ft))); // PHP 5: pembagi 0/kosong -> false, bukan error
        $ke_twm = $bulat - $angsuran;
       
        if($status_app=='MURAB'){
		$minggoisn =2500;
if($deal_produk =='2'){
$minggon =2500;

}else{

$minggon = $bulat - $angsuran;
}
           
		$twm = $run_tenor * $minggon;  


        }else{
            $twm = 0;

        }

        
        $simpanan = $saldo_simpanan - $twm;

if ($saldo_margin>$ijaroh){
$ijaroh=$ijaroh;
}else{
$ijaroh=$saldo_margin;
}    

      
        if($deal_produk=='2' || $deal_produk=='4'){ // 2026-11-22 (P3-7): deal 4 seperti deal 2 (proses_eod_mob)
            $total_tunggak = $ada_tunggak;
            $total_angsuran_margin = ($ijaroh + 2500) * $ft;	          
            $total_angsuran_pokok= $total_tunggak - $total_angsuran_margin;	  
            $angsuran=$angsuran + 2500;
            $angsuran_pokok = $angsuran_pokok * $ft;
	    $angsuran_margin = ($ijaroh + 2500) * $ft;
            
	    // 2026-11-22 (P3-7): dulu $angsuran_pokok * $ft (sudah dikali ft -> pokok x ft x ft) dan margin $ijaroh * $ft (tanpa 2.500,
	    // padahal tunggakan 02 deal 2 = ijaroh + 2.500 dan jurnal margin = (ijaroh + 2.500) x ft)
	    $angsuran_pokok_tunggak = $angsuran_pokok_asli * $ft;
            $angsuran_margin_tunggak = ($ijaroh + 2500) * $ft;

	    $pokok_tunggak = $angsuran_pokok_asli;
            $ijaroh_tunggak= $ijaroh+2500;

		}else{		
		
           $total_tunggak = $ada_tunggak ;
            $total_angsuran_margin = $ijaroh  * $ft;	          
            $total_angsuran_pokok= $total_tunggak - $total_angsuran_margin;	  
            $angsuran=$angsuran;
 	    $angsuran_pokok_tunggak = $angsuran_pokok * $ft;
	    $angsuran_margin_tunggak = $ijaroh * $ft;
            $angsuran_pokok = $angsuran_pokok * $ft;
	    $angsuran_margin = $ijaroh * $ft;

	    $pokok_tunggak= $angsuran_pokok_asli;
            $ijaroh_tunggak= $ijaroh;
		}		
				
        $nama=mysqli_real_escape_string($con,($data['Cust_Short_name']));
        $ket ="Setoran an $nama $ciff"; // 2026-11-22 (P3-7): dulu $cif (tak terdefinisi)
        $ket2 ="Angsuran  an $nama $ciff";
 
        $kodetr=8;
        $kodetr=acakangkahuruf($kodetr);
        $kode_trans="BU/".$unit.$kodetr;
 
        $kodetrs=8;
        $kodetrs=acakangkahuruf($kodetrs);
        $kode_tran="BU/".$unit.$kodetrs;
     
     // 2026-11-22 (perintah user, P3-7): MUSYAROKAH mengikuti pola proses_eod_mob_debet_tunggakan.php (jalur ada tunggakan).
     // Tunggakan MUSYAROKAH per minggu = pokok (TYPE 01); satu angsuran = pokok + nisbah + 2.500 didebet dari simpanan.
     // Jumlah angsuran = tunggakan / pokok, dibatasi saldo bebas. Dulu memakai variabel centang dari menu manual (tak ada di file ini)
     // (simpanan & tunggakan ke CIF kosong, loan tak berubah, jurnal tak seimbang, status tetap Booking Debet).
     if ($status_app == 'MUSYAROKAH') {
        $angsuran_pokok_mus = $angsuran - $nisbah_mus - 2500;
        $ft_mus = $angsuran_pokok_mus > 0 ? (int) round(compat_num($ada_tunggak) / $angsuran_pokok_mus) : 0;
        $n_mus = $angsuran > 0 ? (int) min($ft_mus, floor(compat_num($simpanan) / $angsuran)) : 0;
        if ($ada_tunggak <= 0) {
            echo "<script>alert('Saldo $nama tidak mempunyai tunggakan');</script>";
            db_query($con,"update booking_debet set status_booking ='Tidak Ada Tunggakan' where cif ='$ciff'");
        } elseif ($n_mus < 1) {
            db_query($con,"update booking_debet set status_booking ='Saldo Tidak Cukup' where cif ='$ciff'");
        } else {
            $bayar_mus = $n_mus * $angsuran;
            $pokok_mus = $n_mus * $angsuran_pokok_mus;
            $nisbah_bayar_mus = $n_mus * $nisbah_mus;
            $titipan_mus = $n_mus * 2500;
            $jurnal_umum=db_fetch_array(db_query($con,"SELECT max(nomor_jurnal) FROM jurnal_umum where unit ='$unit' ORDER BY tanggal_selesai DESC"));
            $nomor_jurnal=$jurnal_umum[0]+1;
            db_query ($con,"Update  pinjaman_detail_mus set nisbah_anggota ='$nisbah_mus',tgl_bayar='$tanggal' where id_pinjam  ='$norek' and cicilan='$pb'");
            db_query($con,"insert  into tunggakan (tgl_tunggak,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,blok)VALUES('$tanggal','$norek','$unit','$ciff','$code_kel','$pokok_mus','01','0','$userid','$ket','','$cao','') ");
            db_query($con,"insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif,AO)VALUES('','$tanggal','$unit','$norek','0','$pokok_mus','03','$ket2','EOD','REALISASI POKOK','$ciff',(select cao from loan where cif='$ciff' limit 1))");
            db_query($con,"insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif,AO)VALUES('','$tanggal','$unit','$norek','0','$nisbah_bayar_mus','03','$ket2','EOD','REALISASI BAGHAS','$ciff',(select cao from loan where cif='$ciff' limit 1))");
            db_query($con,"Update  loan set next_schedule ='$next_schedule',os =os-'$pokok_mus',run_tenor='$pb',ke='$ke_tenor'+1,last_payment='$tanggal' where cif  ='$ciff'");
            db_query($con,"insert  into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,blok,tgl_input,kode_transaksi)VALUES('$tanggal','$norek','$unit','$ciff','$code_kel','$bayar_mus','03','0','$userid','$ket2','','$cao','2','$tgl_input','$kode_tran') ");
            db_query($con,"insert into jurnal_umum(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values('$nomor_jurnal','$kode_tran','$tanggal','$unit')");
            db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                values('$unit','$kode_tran','2101000','$tanggal','Bukti SYSTEM','$ket2','$bayar_mus','$userid')");
            db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                values('$unit','$kode_tran','1472000','$tanggal','Bukti SYSTEM','$ket2','$pokok_mus','$userid')");
            db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                values('$unit','$kode_tran','41062','$tanggal','Bukti SYSTEM','$ket2','$nisbah_bayar_mus','$userid')");
            db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                values('$unit','$kode_tran','2867000','$tanggal','Bukti SYSTEM','$ket2','$titipan_mus','$userid')");
            db_query ($con,"Update  tagihan_cs set status_realisasi ='BAYAR'  where cif  ='$ciff' and tgl_tagih='$tanggal'");
            db_query($con,"update booking_debet set status_booking ='Done' where cif ='$ciff'");
        }
        continue;
     }

     if ($simpanan >= $ada_tunggak) { 
 
                switch ($status_app) {
                    case "REST":
                        $jurnal_umum=db_fetch_array(db_query($con,"SELECT max(nomor_jurnal) FROM jurnal_umum where unit ='$unit' ORDER BY tanggal_selesai DESC"));
                        $nomor_jurnal=$jurnal_umum[0]+1;
 
                        if($ada_tunggak>0){
                            db_query($con,"insert  into tunggakan (tgl_tunggak,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,blok)VALUES('$tanggal','$norek','$unit','".$ciff."','$code_kel','$angsuran_pokok_tunggak','01','0','$userid','$ket','','$cao','') ") ;
                            db_query ($con,"insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif,AO)VALUES('','$tanggal','$unit','$norek','0','$angsuran_pokok_tunggak','03','$ket2','EOD','REALISASI POKOK','".$ciff."',(select cao from loan where cif='".$ciff."' limit 1))");
                            db_query ($con,"Update  loan set next_schedule ='$next_schedule',os =os-'$total_tunggak',saldo_margin=saldo_margin-'$total_angsuran_margin',run_tenor='$pb',ke='$ke_tenor'+1 ,last_payment='$tanggal' where cif  ='".$ciff."'");
db_query($con,"update booking_debet set status_booking ='Done' where cif ='$ciff'");
                            db_query ($con,"insert  into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,tgl_input,kode_transaksi)VALUES('$tanggal','$norek','$unit','".$ciff."','$code_kel','$total_tunggak','03','0','$userid','$ket2','','$cao','$tgl_input','$kode_tran') ");
                            db_query ($con,"Update  pinjaman_detail set jumlah_bayar ='$total_tunggak',tgl_bayar='$tanggal' where id_pinjam  ='$norek' and cicilan='$pb'");
                            db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                            values('$unit','$kode_tran','2101000','$tanggal','Bukti SYSTEM','$ket2','$total_tunggak','$userid')");
                            db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                            values('$unit','$kode_tran','1411000','$tanggal','Bukti SYSTEM','$ket2','$total_tunggak','$userid')");
                    
                        }else{
                    
                         echo "<script>alert('Saldo $nama tidak mempunyai tunggakan');</script>"; db_query($con,"update booking_debet set status_booking ='Tidak Ada Tunggakan' where cif ='$ciff'"); // 2026-11-22 (P3-7)
 
                        }
                 
                      break;
                    case "REST_POKOK_MARGIN":
 
                    $jurnal_umum=db_fetch_array(db_query($con,"SELECT max(nomor_jurnal) FROM jurnal_umum where unit ='$unit' ORDER BY tanggal_selesai DESC"));
                    $nomor_jurnal=$jurnal_umum[0]+1;                 
                     
                    if($ada_tunggak > 0){
                    db_query($con,"insert  into tunggakan (tgl_tunggak,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,blok)VALUES('$tanggal','$norek','$unit','".$ciff."','$code_kel','$angsuran_pokok_tunggak','01','0','$userid','$ket','','$cao','') ") ;
                    db_query($con,"insert  into tunggakan (tgl_tunggak,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,blok)VALUES('$tanggal','$norek','$unit','".$ciff."','$code_kel','$angsuran_margin_tunggak','02','0','$userid','$ket','','$cao','') ");
                    db_query($con,"insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif,AO)VALUES('','$tanggal','$unit','$norek','0','$angsuran_pokok','03','$ket','EOD','REALISASI POKOK','".$ciff."',(select cao from loan where cif='".$ciff."' limit 1))") ;
                    db_query($con,"insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif,AO)VALUES('','$tanggal','$unit','$norek','0','$angsuran_margin','03','$ket','EOD','REALISASI BAGHAS','".$ciff."',(select cao from loan where cif='".$ciff."' limit 1))");
                    db_query($con,"Update  loan set next_schedule ='$next_schedule',os =os-'$total_tunggak',saldo_margin=saldo_margin-'$total_angsuran_margin',run_tenor='$pb',ke='$ke_tenor'+1,last_payment='$tanggal' where cif  ='".$ciff."'") ;
                                             
                    db_query($con,"insert  into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,blok,tgl_input,kode_transaksi)VALUES('$tanggal','$norek','$unit','".$ciff."','$code_kel','$total_tunggak','03','0','$userid','$ket2','','$cao','2','$tgl_input','$kode_tran') ") ;
                    db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                        values('$unit','$kode_tran','2101000','$tanggal','Bukti SYSTEM','$ket2','$total_tunggak','$userid')");
 
                    db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                        values('$unit','$kode_tran','1411000','$tanggal','Bukti SYSTEM','$ket2','$total_tunggak','$userid')");
                    db_query($con,"insert into jurnal_umum(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values('$nomor_jurnal','$kode_tran','$tanggal','$unit')");
                        //Realisasi Piutang Margin     
                    db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                        values('$unit','$kode_tran','1421000','$tanggal','Bukti SYSTEM','$ket2','$total_angsuran_margin','$userid')");
                        //Realiasasi Murabahah Debit pada Piutang Murabahah total jumlah Pokok dan margin 
                    db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                        values('$unit','$kode_tran','41002','$tanggal','Bukti SYSTEM','$ket2','$total_angsuran_margin','$userid')");

 db_query($con,"Update booking_debet set status_booking ='Done' where cif ='$ciff'");
                       
 
                    }else{
 
                    
                           echo "<script>alert('Saldo $nama tidak mempunyai tunggakan');</script>"; db_query($con,"update booking_debet set status_booking ='Tidak Ada Tunggakan' where cif ='$ciff'"); // 2026-11-22 (P3-7)
 
                        }
 
                      
                      break;
                    case "MURAB":
                        $jurnal_umum=db_fetch_array(db_query($con,"SELECT max(nomor_jurnal) FROM jurnal_umum where unit ='$unit' ORDER BY tanggal_selesai DESC"));
                        $nomor_jurnal=$jurnal_umum[0]+1;                 
                         
                        if($ada_tunggak>0){
                            db_query($con,"insert  into tunggakan (tgl_tunggak,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,blok)VALUES('$tanggal','$norek','$unit','".$ciff."','$code_kel','$angsuran_pokok_tunggak','01','0','$userid','$ket','','$cao','') ") ;
                            db_query($con,"insert  into tunggakan (tgl_tunggak,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,blok)VALUES('$tanggal','$norek','$unit','".$ciff."','$code_kel','$angsuran_margin_tunggak','02','0','$userid','$ket','','$cao','') ");
                            db_query($con,"insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif,AO)VALUES('','$tanggal','$unit','$norek','0','$angsuran_pokok_tunggak','03','$ket2','EOD','REALISASI POKOK','$ciff',(select cao from loan where cif='$ciff' limit 1))") ;
                            db_query($con,"insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif,AO)VALUES('','$tanggal','$unit','$norek','0','$angsuran_margin_tunggak','03','$ket2','EOD','REALISASI BAGHAS','$ciff',(select cao from loan where cif='$ciff' limit 1))");
                            db_query($con,"Update  loan set next_schedule ='$next_schedule',os =os-'$total_tunggak',saldo_margin=saldo_margin-'$total_angsuran_margin',run_tenor='$pb',ke='$ke_tenor'+1,last_payment='$tanggal' where cif  ='".$ciff."'");
                                             
                     
                            db_query($con,"insert  into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,blok,tgl_input,kode_transaksi)VALUES('$tanggal','$norek','$unit','".$ciff."','$code_kel','$total_tunggak','03','0','$userid','$ket2','','$cao','2','$tgl_input','$kode_tran') ");
                             
                                             
                            db_query($con,"insert into jurnal_umum(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values('$nomor_jurnal','$kode_tran','$tanggal','$unit')");
                             
                            db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                                values('$unit','$kode_tran','1413000','$tanggal','Bukti SYSTEM','$ket2','$total_tunggak','$userid')");                         
                            //Realisasi Piutang Margin     
                            db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                                values('$unit','$kode_tran','1423000','$tanggal','Bukti SYSTEM','$ket2','$total_angsuran_margin','$userid')");
                            //Realiasasi Murabahah Debit pada Piutang Murabahah total jumlah Pokok dan margin
                            booking_kredit_margin($con, $unit, $kode_tran, $tanggal, $ket2, $userid, $deal_produk, $total_angsuran_margin, $ft); // 2026-11-22 (P3-7): dulu satu baris 41002
                            db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                                values('$unit','$kode_tran','2101000','$tanggal','Bukti SYSTEM','$ket2','$total_tunggak','$userid')");
                            db_query ($con,"Update  pinjaman_detail set jumlah_bayar ='$total_tunggak',tgl_bayar='$tanggal' where id_pinjam  ='$norek' and cicilan='$pb'"); // 2026-11-22 (P3-7): seperti proses_eod_mob.php
                      db_query($con,"update booking_debet set status_booking ='Done' where cif ='$ciff'");
 
                             
                            }else{
                             echo "<script>alert('Saldo $nama tidak mempunyai tunggakan');</script>"; db_query($con,"update booking_debet set status_booking ='Tidak Ada Tunggakan' where cif ='$ciff'"); // 2026-11-22 (P3-7)
                            
                             
                            }
                      
                      break;
                    case "COVID":
                        $jurnal_umum=db_fetch_array(db_query($con,"SELECT max(nomor_jurnal) FROM jurnal_umum where unit ='$unit' ORDER BY tanggal_selesai DESC"));
                        $nomor_jurnal=$jurnal_umum[0]+1;                 
                         
                        if($ada_tunggak>0){
                            db_query($con,"insert  into tunggakan (tgl_tunggak,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,blok)VALUES('$tanggal','$norek','$unit','".$ciff."','$code_kel','$angsuran_pokok_tunggak','01','0','$userid','$ket','','$cao','') ") ;
                            db_query($con,"insert  into tunggakan (tgl_tunggak,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,blok)VALUES('$tanggal','$norek','$unit','".$ciff."','$code_kel','$angsuran_margin_tunggak','02','0','$userid','$ket','','$cao','') ");
                            db_query($con,"insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif,AO)VALUES('','$tanggal','$unit','$norek','0','$angsuran_pokok','03','$ket2','EOD','REALISASI POKOK','$ciff',(select cao from loan where cif='$ciff' limit 1))") ;
                            db_query($con,"insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif,AO)VALUES('','$tanggal','$unit','$norek','0','$angsuran_margin','03','$ket2','EOD','REALISASI BAGHAS','$ciff',(select cao from loan where cif='$ciff' limit 1))");
                            db_query($con,"Update  loan set next_schedule ='$next_schedule',os =os-'$total_tunggak',saldo_margin=saldo_margin-'$total_angsuran_margin',run_tenor='$pb',ke='$ke_tenor'+1,last_payment='$tanggal' where cif  ='".$ciff."'");
                                             
                     
                            db_query($con,"insert  into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,blok,tgl_input,kode_transaksi)VALUES('$tanggal','$norek','$unit','".$ciff."','$code_kel','$total_tunggak','03','0','$userid','$ket2','','$cao','2','$tgl_input','$kode_tran') ");
                             
                                             
                            db_query($con,"insert into jurnal_umum(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values('$nomor_jurnal','$kode_tran','$tanggal','$unit')");
                             
                            db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                                values('$unit','$kode_tran','1413000','$tanggal','Bukti SYSTEM','$ket2','$total_tunggak','$userid')");                         
                            //Realisasi Piutang Margin     
                            db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                                values('$unit','$kode_tran','1423000','$tanggal','Bukti SYSTEM','$ket2','$angsuran_margin','$userid')");
                            //Realiasasi Murabahah Debit pada Piutang Murabahah total jumlah Pokok dan margin 
                            db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                                values('$unit','$kode_tran','41002','$tanggal','Bukti SYSTEM','$ket2','$angsuran_margin','$userid')");
                            db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                                values('$unit','$kode_tran','2101000','$tanggal','Bukti SYSTEM','$ket2','$total_tunggak','$userid')");
db_query($con,"update booking_debet set status_booking ='Done' where cif ='$ciff'");
                      
 
                             
                            }else{
                             echo "<script>alert('Saldo $nama tidak mempunyai tunggakan');</script>"; db_query($con,"update booking_debet set status_booking ='Tidak Ada Tunggakan' where cif ='$ciff'"); // 2026-11-22 (P3-7)
                            
                             
                            }
                    
			 
                    default:
                    $jurnal_umum=db_fetch_array(db_query($con,"SELECT max(nomor_jurnal) FROM jurnal_umum where unit ='$unit' ORDER BY tanggal_selesai DESC"));
                    $nomor_jurnal=$jurnal_umum[0]+1;                 
                     

                  }



     }else{

        $jumlah_angsur = floor(compat_num(compat_bagi($simpanan, $angsuran))); // PHP 5: pembagi 0/kosong -> false, bukan error
       
      
        $dibayar = $jumlah_angsur * $angsuran;

        // 2026-11-22 (P3-7): dulu $angsuran_pokok / $angsuran_margin (sudah dikali ft) dikali lagi, dan $dibayar_margin_tunggak
        // = jumlah x jumlah x margin. Kini per angsuran x jumlah angsuran yang dibayar.
        $ijaroh_tunggak = $jumlah_angsur * $ijaroh_tunggak;
$dibayar_pokok_tunggak = $jumlah_angsur * $pokok_tunggak;
        $dibayar_pokok = $dibayar_pokok_tunggak;
        $dibayar_margin = $ijaroh_tunggak;
$dibayar_margin_tunggak = $ijaroh_tunggak;

        if($jumlah_angsur >=1){

            switch ($status_app) {
                    case "REST":
                        $jurnal_umum=db_fetch_array(db_query($con,"SELECT max(nomor_jurnal) FROM jurnal_umum where unit ='$unit' ORDER BY tanggal_selesai DESC"));
                        $nomor_jurnal=$jurnal_umum[0]+1;
 
                        if($ada_tunggak>0){
                            db_query($con,"insert  into tunggakan (tgl_tunggak,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,blok)VALUES('$tanggal','$norek','$unit','".$ciff."','$code_kel','$dibayar_pokok_tunggak','01','0','$userid','$ket','','$cao','') ") ;
                            db_query ($con,"insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif,AO)VALUES('','$tanggal','$unit','$norek','0','$dibayar_pokok_tunggak','03','$ket2','EOD','REALISASI POKOK','".$ciff."',(select cao from loan where cif='".$ciff."' limit 1))");
                            db_query ($con,"Update  loan set next_schedule ='$next_schedule',os =os-'$dibayar',saldo_margin=saldo_margin-'$dibayar_margin',run_tenor='$pb',ke='$ke_tenor'+1 ,last_payment='$tanggal' where cif  ='".$ciff."'");
                            db_query ($con,"insert  into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,tgl_input,kode_transaksi)VALUES('$tanggal','$norek','$unit','".$ciff."','$code_kel','$dibayar','03','0','$userid','$ket2','','$cao','$tgl_input','$kode_tran') ");
                            db_query ($con,"Update  pinjaman_detail set jumlah_bayar ='$dibayar',tgl_bayar='$tanggal' where id_pinjam  ='$norek' and cicilan='$pb'");
                            db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                            values('$unit','$kode_tran','2101000','$tanggal','Bukti SYSTEM','$ket2','$dibayar','$userid')");
                            db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                            values('$unit','$kode_tran','1411000','$tanggal','Bukti SYSTEM','$ket2','$dibayar','$userid')");
                    db_query($con,"update booking_debet set status_booking ='Done' where cif ='$ciff'");
                        }else{
                    
                         echo "<script>alert('Saldo $nama tidak mempunyai tunggakan');</script>"; db_query($con,"update booking_debet set status_booking ='Tidak Ada Tunggakan' where cif ='$ciff'"); // 2026-11-22 (P3-7)
 
                        }
                 
                      break;
                    case "REST_POKOK_MARGIN": 
                    $jurnal_umum=db_fetch_array(db_query($con,"SELECT max(nomor_jurnal) FROM jurnal_umum where unit ='$unit' ORDER BY tanggal_selesai DESC"));
                    $nomor_jurnal=$jurnal_umum[0]+1;                 
                     
                    if($ada_tunggak > 0){
                    db_query($con,"insert  into tunggakan (tgl_tunggak,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,blok)VALUES('$tanggal','$norek','$unit','".$ciff."','$code_kel','$dibayar_pokok_tunggak','01','0','$userid','$ket','','$cao','') ") ;
                    db_query($con,"insert  into tunggakan (tgl_tunggak,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,blok)VALUES('$tanggal','$norek','$unit','".$ciff."','$code_kel','$ijaroh_tunggak','02','0','$userid','$ket','','$cao','') ");
                    db_query($con,"insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif,AO)VALUES('','$tanggal','$unit','$norek','0','$dibayar_pokok_tunggak','03','$ket','EOD','REALISASI POKOK','".$ciff."',(select cao from loan where cif='".$ciff."' limit 1))") ;
                    db_query($con,"insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif,AO)VALUES('','$tanggal','$unit','$norek','0','$dibayar_margin_tunggak','03','$ket','EOD','REALISASI BAGHAS','".$ciff."',(select cao from loan where cif='".$ciff."' limit 1))");
                    db_query($con,"Update  loan set next_schedule ='$next_schedule',os =os-'$dibayar',saldo_margin=saldo_margin-'$ijaroh_tunggak',run_tenor='$pb',ke='$ke_tenor'+1,last_payment='$tanggal' where cif  ='".$ciff."'") ;
                                             
                    db_query($con,"insert  into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,blok,tgl_input,kode_transaksi)VALUES('$tanggal','$norek','$unit','".$ciff."','$code_kel','$dibayar','03','0','$userid','$ket2','','$cao','2','$tgl_input','$kode_tran') ") ;
                    db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                        values('$unit','$kode_tran','2101000','$tanggal','Bukti SYSTEM','$ket2','$dibayar','$userid')");
 
                    db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                        values('$unit','$kode_tran','1411000','$tanggal','Bukti SYSTEM','$ket2','$dibayar','$userid')");
                    db_query($con,"insert into jurnal_umum(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values('$nomor_jurnal','$kode_tran','$tanggal','$unit')");
                        //Realisasi Piutang Margin     
                    db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                        values('$unit','$kode_tran','1421000','$tanggal','Bukti SYSTEM','$ket2','$ijaroh_tunggak','$userid')");
                        //Realiasasi Murabahah Debit pada Piutang Murabahah total jumlah Pokok dan margin 
                    db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                        values('$unit','$kode_tran','41002','$tanggal','Bukti SYSTEM','$ket2','$ijaroh_tunggak','$userid')");
db_query($con,"update booking_debet set status_booking ='Done' where cif ='$ciff'");
 
                       
 
                    }else{
 
                    
                           echo "<script>alert('Saldo $nama tidak mempunyai tunggakan');</script>"; db_query($con,"update booking_debet set status_booking ='Tidak Ada Tunggakan' where cif ='$ciff'"); // 2026-11-22 (P3-7)
 
                        }
 
                      
                      break;
                    case "MURAB":
                        $jurnal_umum=db_fetch_array(db_query($con,"SELECT max(nomor_jurnal) FROM jurnal_umum where unit ='$unit' ORDER BY tanggal_selesai DESC"));
                        $nomor_jurnal=$jurnal_umum[0]+1;                 
                         
                        if($ada_tunggak>0){
                            db_query($con,"insert  into tunggakan (tgl_tunggak,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,blok)VALUES('$tanggal','$norek','$unit','".$ciff."','$code_kel','$dibayar_pokok_tunggak','01','0','$userid','$ket','','$cao','') ") ;
                            db_query($con,"insert  into tunggakan (tgl_tunggak,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,blok)VALUES('$tanggal','$norek','$unit','".$ciff."','$code_kel','$ijaroh_tunggak','02','0','$userid','$ket','','$cao','') ");
                            db_query($con,"insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif,AO)VALUES('','$tanggal','$unit','$norek','0','$dibayar_pokok_tunggak','03','$ket2','EOD','REALISASI POKOK','$ciff',(select cao from loan where cif='$ciff' limit 1))") ; // 2026-11-22 (P3-7): dulu $angsuran_pokok (pokok x ft)
                            db_query($con,"insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif,AO)VALUES('','$tanggal','$unit','$norek','0','$dibayar_margin','03','$ket2','EOD','REALISASI BAGHAS','$ciff',(select cao from loan where cif='$ciff' limit 1))");
                            db_query($con,"Update  loan set next_schedule ='$next_schedule',os =os-'$dibayar',saldo_margin=saldo_margin-'$ijaroh_tunggak',run_tenor='$pb',ke='$ke_tenor'+1,last_payment='$tanggal' where cif  ='".$ciff."'");
                                             
                     
                            db_query($con,"insert  into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,blok,tgl_input,kode_transaksi)VALUES('$tanggal','$norek','$unit','".$ciff."','$code_kel','$dibayar','03','0','$userid','$ket2','','$cao','2','$tgl_input','$kode_tran') ");
                             
                                             
                            db_query($con,"insert into jurnal_umum(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values('$nomor_jurnal','$kode_tran','$tanggal','$unit')");
                             
                            db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                                values('$unit','$kode_tran','1413000','$tanggal','Bukti SYSTEM','$ket2','$dibayar','$userid')");                         
                            //Realisasi Piutang Margin     
                            db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                                values('$unit','$kode_tran','1423000','$tanggal','Bukti SYSTEM','$ket2','$ijaroh_tunggak','$userid')");
                            //Realiasasi Murabahah Debit pada Piutang Murabahah total jumlah Pokok dan margin
                            booking_kredit_margin($con, $unit, $kode_tran, $tanggal, $ket2, $userid, $deal_produk, $ijaroh_tunggak, $jumlah_angsur); // 2026-11-22 (P3-7): dulu satu baris 41002
                            db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                                values('$unit','$kode_tran','2101000','$tanggal','Bukti SYSTEM','$ket2','$dibayar','$userid')");
                            db_query ($con,"Update  pinjaman_detail set jumlah_bayar ='$dibayar',tgl_bayar='$tanggal' where id_pinjam  ='$norek' and cicilan='$pb'"); // 2026-11-22 (P3-7): seperti proses_eod_mob.php

 db_query($con,"update booking_debet set status_booking ='Done' where cif ='$ciff'");
                             
                            }else{
                             echo "<script>alert('Saldo $nama tidak mempunyai tunggakan');</script>"; db_query($con,"update booking_debet set status_booking ='Tidak Ada Tunggakan' where cif ='$ciff'"); // 2026-11-22 (P3-7)
                            
                             
                            }
                      
                      break;
                      case "COVID":
                        $jurnal_umum=db_fetch_array(db_query($con,"SELECT max(nomor_jurnal) FROM jurnal_umum where unit ='$unit' ORDER BY tanggal_selesai DESC"));
                        $nomor_jurnal=$jurnal_umum[0]+1;                 
                         
                        if($ada_tunggak>0){
                            db_query($con,"insert  into tunggakan (tgl_tunggak,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,blok)VALUES('$tanggal','$norek','$unit','".$ciff."','$code_kel','$dibayar_pokok_tunggak','01','0','$userid','$ket','','$cao','') ") ;
                            db_query($con,"insert  into tunggakan (tgl_tunggak,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,blok)VALUES('$tanggal','$norek','$unit','".$ciff."','$code_kel','$dibayar_margin_tunggak','02','0','$userid','$ket','','$cao','') ");
                            db_query($con,"insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif,AO)VALUES('','$tanggal','$unit','$norek','0','$dibayar_pokok_tunggak','03','$ket2','EOD','REALISASI POKOK','$ciff',(select cao from loan where cif='$ciff' limit 1))") ; // 2026-11-22 (P3-7): dulu $angsuran_pokok (pokok x ft)
                            db_query($con,"insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif,AO)VALUES('','$tanggal','$unit','$norek','0','$dibayar_margin','03','$ket2','EOD','REALISASI BAGHAS','$ciff',(select cao from loan where cif='$ciff' limit 1))");
                            db_query($con,"Update  loan set next_schedule ='$next_schedule',os =os-'$dibayar',saldo_margin=saldo_margin-'$dibayar_margin',run_tenor='$pb',ke='$ke_tenor'+1,last_payment='$tanggal' where cif  ='".$ciff."'");
                                             
                     
                            db_query($con,"insert  into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,blok,tgl_input,kode_transaksi)VALUES('$tanggal','$norek','$unit','".$ciff."','$code_kel','$dibayar','03','0','$userid','$ket2','','$cao','2','$tgl_input','$kode_tran') ");
                             
                                             
                            db_query($con,"insert into jurnal_umum(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values('$nomor_jurnal','$kode_tran','$tanggal','$unit')");
                             
                            db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                                values('$unit','$kode_tran','1413000','$tanggal','Bukti SYSTEM','$ket2','$dibayar','$userid')");                         
                            //Realisasi Piutang Margin     
                            db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                                values('$unit','$kode_tran','1423000','$tanggal','Bukti SYSTEM','$ket2','$dibayar_margin','$userid')");
                            //Realiasasi Murabahah Debit pada Piutang Murabahah total jumlah Pokok dan margin 
                            db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                                values('$unit','$kode_tran','41002','$tanggal','Bukti SYSTEM','$ket2','$dibayar_margin','$userid')");
                            db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                                values('$unit','$kode_tran','2101000','$tanggal','Bukti SYSTEM','$ket2','$dibayar','$userid')");
                      
 db_query($con,"update booking_debet set status_booking ='Done' where cif ='$ciff'");
                             
                            }else{
                             echo "<script>alert('Saldo $nama tidak mempunyai tunggakan');</script>"; db_query($con,"update booking_debet set status_booking ='Tidak Ada Tunggakan' where cif ='$ciff'"); // 2026-11-22 (P3-7)
                            
                             
                            }
                      
                      break;
			 
                    default:
                    $jurnal_umum=db_fetch_array(db_query($con,"SELECT max(nomor_jurnal) FROM jurnal_umum where unit ='$unit' ORDER BY tanggal_selesai DESC"));
                    $nomor_jurnal=$jurnal_umum[0]+1;                 
                     
                  
                  }


            

        }else{
           db_query($con,"update booking_debet set status_booking ='Saldo Tidak Cukup' where cif ='$ciff'");
          
                  
        }
         
         
         
        }
         

    } 

 
    if (!mysqli_commit($con)) {
        echo "Transaksi Gagal commit Silahkan ulangi lagi";
        exit();
      }
echo "<script>alert('Setoran booking Debet sukses di simpan');</script>";
    

     
    
 
?>
