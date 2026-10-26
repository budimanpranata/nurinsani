<?php require_once __DIR__ . '/koneksi_db.php';
session_start();
ob_start();
date_default_timezone_set('Asia/Jakarta');


$con = db_ni();
if (mysqli_connect_errno()) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
    exit;
  }
 
  // Turn autocommit off
mysqli_autocommit($con,false);

include "function.php";
 
$tanggal='2026-03-26';
$tgl_system='2026-03-26'; 
$tgl_input=date("Y-m-d");

    

                   
        $select = db_query ($con,"select DATE_ADD(next_schedule,INTERVAL 7 DAY) as next_schedule,unit,code_kel,no_anggota,CUST_SHORT_NAME,cif,ijaroh,Plafond,pokok,os,angsuran,bulat,TIMESTAMPDIFF(WEEK,tgl_murab,next_schedule) AS RUN_TENOR1,run_tenor,TIMESTAMPDIFF(WEEK,tgl_murab,CURDATE()) AS ke1,cao,ke as next,Tenor,saldo_margin,maturity_date,status_app,deal_produk from loan  WHERE cif not in (select cif from cif_skeep) and os > 0 and hari ='Kamis' order by loan.unit  ");
            while($row = db_fetch_array($select)){
                $next_schedule1=$row['next_schedule'];
                $no_anggota=$row['no_anggota'];
                $norek=$row['no_anggota'];
                $pokok=$row['pokok'];
                $angsuran_pokok=$row['pokok'];                
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
                $ciff=$row['cif'];
                $tgl_system=date("Y-m-d");
                $cao=$row['cao'];
                $status_app=$row['status_app'];
                $tenor=$row['Tenor'];
                $ke_tenor=$row['next'];
                $nama=mysqli_real_escape_string ($con,$row['CUST_SHORT_NAME']);
                $ket="Setor Tagihan Lebaran $nama $cif";
                $ket2="Setoran Tagihan Lebaran $nama";
                $ket3="Tunggakan Tagihan Lebaran $nama";
                $userid=$unit;                 
                $kode_tr=7;
                $kode_tr=acakangkahuruf($kode_tr);      
                $kode_transaksi="BU/".$unit .$kode_tr;
                $wajib_kh=$bulat - $angsuran;
                $cek_next= db_fetch_array(db_query ($con,"select cicilan,tgl_jatuh_tempo from pinjaman_detail WHERE cicilan='$next_tenor' AND id_pinjam ='$no_anggota'"));
                $next_schedule=$cek_next['tgl_jatuh_tempo'];
                $next_cicilan=$cek_next['cicilan'];
                $row_sal= db_fetch_array(db_query ($con,"select(sum(KREDIT) - sum(DEBIT))as saldo from simpanan WHERE cif ='$cif' and type not in ('05','05')")); ;
                $sisa=$row_sal['saldo'];
                //$row_sal= mysqli_fetch_array(mysqli_query ($con,"select(sum(KREDIT) - sum(DEBIT))as blok from simpanan WHERE type in ('05','02','04','07') and cif ='$cif'"));
                //$blok=$row_sal['blok'];
                $next_schedule    = date('Y-m-d', strtotime('+7 days', strtotime($tanggal)));    
 
                

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
                db_query ($con,"Update loan set next_schedule='$next_schedule1-1',ke='$ke_tenor'+1 where cif  ='$cif'");
                                 
                db_query ($con,"insert  into tunggakan (tgl_tunggak,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,blok)VALUES('$tanggal','$no_anggota','$unit','$cif','$code_kel','0','01','$pokok','$userid','$ket','$cao','') ");
               

 
}else{

$cek_tunggak= db_fetch_array(db_query ($con,"select sum(KREDIT)- sum(DEBIT) as tunggak from tunggakan WHERE cif='$cif'"));
            $ada_tunggak=$cek_tunggak['tunggak'];    
            if($ada_tunggak>0){
db_query ($con,"insert  into tunggakan (tgl_tunggak,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,blok)VALUES('$tanggal','$no_anggota','$unit','$cif','$code_kel','$pokok','01','0','$userid','$ket','$cao','') ");

db_query ($con,"insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif)VALUES('','$tanggal','$unit','$no_anggota','0','$pokok','03','$ket','$userid','REALISASI POKOK','$cif')");
               
                db_query ($con,"Update  loan set next_schedule ='$next_schedule',os =os-'$angsuran',saldo_margin=saldo_margin-'$ijaroh',run_tenor='$pb',ke='$ke_tenor'+1 ,last_payment='$tanggal' where cif  ='$cif'");
                db_query ($con,"insert  into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,tgl_input,kode_transaksi)VALUES('$tanggal','$no_anggota','$unit','$cif','$code_kel','$angsuran','03','0','$userid','$ket','$cao','$tgl_input','$kode_tran') ");
                //mysqli_query ($con,"Update  tabungan_detail set saldo =(select(sum(KREDIT)-SUM(DEBIT)) as saldo from simpanan where NOREK='" . $value . "') where no_anggota  ='" . $value . "'");
                db_query ($con,"Update  pinjaman_detail set jumlah_bayar ='$angsuran',tgl_bayar='$tanggal' where id_pinjam  ='$no_anggota' and cicilan='$pb'");
                db_query ($con,"Update  pinjaman_detail_jatpo set jumlah_bayar ='$angsuran',tgl_bayar='$tanggal' where id_pinjam  ='$no_anggota' and cicilan='$pb'");
                //mysqli_query ($con,"Update  tunggakan set pokok ='0',margin='0' where cif  ='$cif' and ke='$ke_tenor'");
                //mysqli_query ($con,"insert  into tagihan (id_reff,code_kel,no_anggota,bagi_hasil,pokok,tagihan,bulat,tagihan_ke,sisa_os,tgl_realisasi,status)VALUES('','$code_kel','$no_anggota','$ijaroh','$pokok','$angsuran','$bulat','$ke','$os','$next_schedule','RENCANA') ");
                $jurnal_umum=db_fetch_array(db_query ($con,"SELECT max(nomor_jurnal) FROM jurnal_umum where unit ='$unit' ORDER BY tanggal_selesai DESC"));
                $nomor_jurnal=$jurnal_umum[0]+1;

                db_query ($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                values('$unit','$kode_tran','2101000','$tanggal','Bukti SYSTEM','$ket','$angsuran','$userid')");
               
                db_query ($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                values('$unit','$kode_tran','1411000','$tanggal','Bukti SYSTEM','$ket','$angsuran','$userid')");
 		//mysqli_query ($con,"Update  tagihan_cs set status_realisasi ='BAYAR'  where cif  ='$cif' and tgl_tagih='$tanggal'");

               
           
            


}else{
   
                db_query ($con,"insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif)VALUES('','$tanggal','$unit','$no_anggota','0','$pokok','03','$ket','$userid','REALISASI POKOK','$cif')");
               
                db_query ($con,"Update  loan set next_schedule ='$next_schedule',os =os-'$angsuran',saldo_margin=saldo_margin-'$ijaroh',run_tenor='$pb',ke='$ke_tenor'+1 ,last_payment='$tanggal' where cif  ='$cif'");
                db_query ($con,"insert  into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,tgl_input,kode_transaksi)VALUES('$tanggal','$no_anggota','$unit','$cif','$code_kel','$angsuran','03','0','$userid','$ket','$cao','$tgl_input','$kode_tran') ");
                db_query ($con,"Update  pinjaman_detail set jumlah_bayar ='$angsuran',tgl_bayar='$tanggal' where id_pinjam  ='$no_anggota' and cicilan='$pb'");
                db_query ($con,"Update  pinjaman_detail_jatpo set jumlah_bayar ='$angsuran',tgl_bayar='$tanggal' where id_pinjam  ='$no_anggota' and cicilan='$pb'");
                $jurnal_umum=db_fetch_array(db_query ($con,"SELECT max(nomor_jurnal) FROM jurnal_umum where unit ='$unit' ORDER BY tanggal_selesai DESC"));
                $nomor_jurnal=$jurnal_umum[0]+1;
              
                db_query ($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                values('$unit','$kode_tran','2101000','$tanggal','Bukti SYSTEM','$ket','$angsuran','$userid')");
               
                db_query ($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                values('$unit','$kode_tran','1411000','$tanggal','Bukti SYSTEM','$ket','$angsuran','$userid')");

               
           

           }
}
       
}elseif($status_app=="REST_POKOK_MARGIN"){
 
db_query ($con,"INSERT INTO pinjaman_detail_rencana(id,id_pinjam,cicilan,angsuran_pokok,bunga,tgl_jatuh_tempo,tgl_bayar,jumlah_bayar,ket,cif,unit,ao)
                    VALUES('$new_id','$no_anggota','$urut','$pokok','$ijaroh','$next_schedule1','','','','$cif','$unit','$cao')");
                 
 
if ($saldo < $angsuran){    
                db_query ($con,"Update loan set next_schedule='$next_schedule1-1',ke='$ke_tenor'+1 where cif  ='$cif'");
                $ke_n= db_fetch_array(db_query ($con,"select ke from loan WHERE cif ='$cif'"));
                $ke_lagi=$ke_n['ke'];
                 
            db_query($con,"insert  into tunggakan (tgl_tunggak,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,blok)VALUES('$tanggal','$no_anggota','$unit','$cif','$code_kel','0','01','$pokok','$userid','$ket','$cao','') ") ;
            db_query($con,"insert  into tunggakan (tgl_tunggak,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,blok)VALUES('$tanggal','$no_anggota','$unit','$cif','$code_kel','0','02','$ijaroh','$userid','$ket','$cao','') ");
                     
                if ($maturity_date < $tgl_system){
                             
                 
        db_query ($con,"INSERT INTO pinjaman_detail (id,id_pinjam,cicilan,angsuran_pokok,bunga,tgl_jatuh_tempo,tgl_bayar,jumlah_bayar,ket,cif,unit,ao)
                values('$new_id','$no_anggota','$urut','$pokok','$ijaroh','$next_schedule1-1','','0','JATPO','$cif','$unit','$cao')");
     
                    db_query ($con,"INSERT INTO pinjaman_detail_jatpo(id,id_pinjam,cicilan,angsuran_pokok,bunga,tgl_jatuh_tempo,tgl_bayar,jumlah_bayar,ket)
                    VALUES('$new_id','$no_anggota','$urut','$pokok','$ijaroh','$next_schedule1-1','','','')");
                          
                     
                }else {
                  
                     
                }            
         
        }    
        else{
            $cek_tunggak= db_fetch_array(db_query ($con,"select sum(KREDIT)- sum(DEBIT) as tunggak from tunggakan WHERE cif='$cif'"));
            $ada_tunggak=$cek_tunggak['tunggak'];    
            if($ada_tunggak>0){
            db_query ($con,"insert  into tunggakan (tgl_tunggak,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,blok)VALUES('$tanggal','$no_anggota','$unit','$cif','$code_kel','$pokok','01','0','$userid','$ket','$cao','') ");
            db_query ($con,"insert  into tunggakan (tgl_tunggak,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,blok)VALUES('$tanggal','$no_anggota','$unit','$cif','$code_kel','$ijaroh','02','0','$userid','$ket','$cao','') ");
            db_query ($con,"insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif)VALUES('','$tanggal','$unit','$no_anggota','0','$pokok','03','$ket','$userid','REALISASI POKOK','$cif')");
            db_query ($con,"insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif)VALUES('','$tanggal','$unit','$no_anggota','0','$ijaroh','03','$ket','$userid','REALISASI BAGHAS','$cif')");
            db_query ($con,"Update  loan set next_schedule ='$next_schedule',os =os-'$angsuran',saldo_margin=saldo_margin-'$ijaroh',run_tenor='$pb',ke='$ke_tenor'+1,last_payment='$tanggal' where cif  ='$cif'");
                 
 
                 
            db_query ($con,"insert  into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,blok,tgl_input,kode_transaksi)VALUES('$tanggal','$no_anggota','$unit','$cif','$code_kel','$angsuran','03','0','$userid','$ket','','$cao','2','$tgl_input','$kode_tran') ");
            $jurnal_umum=db_fetch_array(db_query ($con,"SELECT max(nomor_jurnal) FROM jurnal_umum where unit ='$unit' ORDER BY tanggal_selesai DESC"));
            $nomor_jurnal=$jurnal_umum[0]+1;

            db_query ($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                values('$unit','$kode_tran','2101000','$tanggal','Bukti SYSTEM','$ket','$angsuran','$userid')");
 
                 db_query ($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                values('$unit','$kode_tran','1411000','$tanggal','Bukti SYSTEM','$ket','$angsuran','$userid')");
                 
                db_query ($con,"insert into jurnal_umum(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values('$nomor_jurnal','$kode_tran','$tanggal','$unit')");
                //Realisasi Piutang Margin    
                db_query ($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                            values('$unit','$kode_tran','1421000','$tanggal','Bukti SYSTEM','$ket','$ijaroh','$userid')");
                //Realiasasi Murabahah Debit pada Piutang Murabahah total jumlah Pokok dan margin
                db_query ($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                values('$unit','$kode_tran','41002','$tanggal','Bukti SYSTEM','$ket','$ijaroh','$userid')");

 
}else{
 
                db_query ($con,"insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif)VALUES('','$tanggal','$unit','$no_anggota','0','$pokok','03','$ket','$userid','REALISASI POKOK','$cif')");
                db_query ($con,"insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif)VALUES('','$tanggal','$unit','$no_anggota','0','$ijaroh','03','$ket','$userid','REALISASI BAGHAS','$cif')");
 
                db_query ($con,"Update  loan set next_schedule ='$next_schedule',os =os-'$angsuran',saldo_margin=saldo_margin-'$ijaroh',run_tenor='$pb',ke='$ke_tenor'+1,last_payment='$tanggal' where cif  ='$cif'");
                db_query ($con,"insert  into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,tgl_input,kode_transaksi)VALUES('$tanggal','$no_anggota','$unit','$cif','$code_kel','$angsuran','03','0','$userid','$ket','$cao','$tgl_input','$kode_tran') ");
                $jurnal_umum=db_fetch_array(db_query ($con,"SELECT max(nomor_jurnal) FROM jurnal_umum where unit ='$unit' ORDER BY tanggal_selesai DESC"));
                $nomor_jurnal=$jurnal_umum[0]+1;
db_query ($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                values('$unit','$kode_tran','2101000','$tanggal','Bukti SYSTEM','$ket','$angsuran','$userid')");
 
                 db_query ($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                values('$unit','$kode_tran','1411000','$tanggal','Bukti SYSTEM','$ket','$angsuran','$userid')");
                 
                db_query ($con,"insert into jurnal_umum(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values('$nomor_jurnal','$kode_tran','$tanggal','$unit')");
                //Realisasi Piutang Margin    
                db_query ($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                            values('$unit','$kode_tran','1421000','$tanggal','Bukti SYSTEM','$ket','$ijaroh','$userid')");
                //Realiasasi Murabahah Debit pada Piutang Murabahah total jumlah Pokok dan margin
                db_query ($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                values('$unit','$kode_tran','41002','$tanggal','Bukti SYSTEM','$ket','$ijaroh','$userid')");

 
 
                }
            }
          
             
        }elseif($status_app=="MUSYAROKAH"){
       
				
   

        if ($saldo < $angsuran){    
                db_query ($con,"Update loan set next_schedule='$next_schedule1-1',ke='$ke_tenor'+1 where cif  ='$cif'");
                $ke_n= db_fetch_array(db_query ($con,"select ke from loan WHERE cif ='$cif'"));
                $ke_lagi=$ke_n['ke'];
         db_query($con,"insert  into tunggakan (tgl_tunggak,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,blok)VALUES('$tanggal','$no_anggota','$unit','$cif','$code_kel','0','01','$pokok','$userid','$ket','$cao','') ") ;
         db_query($con,"insert  into tunggakan (tgl_tunggak,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,blok)VALUES('$tanggal','$no_anggota','$unit','$cif','$code_kel','0','02','$ijaroh','$userid','$ket','$cao','') ");
                     
         
        }else{
             $angsuran_pokok = $angsuran - $nisbah_mus - 2500;
          db_query ($con,"Update  pinjaman_detail_mus set nisbah_anggota ='$nisbah_mus',tgl_bayar='$tanggal' where id_pinjam  ='$norek' and cicilan='$pb'");
                    


        if($ada_tunggak>0){
                            db_query($con,"insert  into tunggakan (tgl_tunggak,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,blok)VALUES('$tanggal','$norek','$unit','".$ciff."','$code_kel','$angsuran_pokok','01','0','$userid','$ket','$cao','') ") ;
                            db_query($con,"insert  into tunggakan (tgl_tunggak,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,blok)VALUES('$tanggal','$norek','$unit','".$ciff."','$code_kel','$ijaroh','02','0','$userid','$ket','$cao','') ");
                            db_query($con,"insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif)VALUES('','$tanggal','$unit','$norek','0','$angsuran_pokok','03','$ket2','$userid','REALISASI POKOK','$cif')") ;
                            db_query($con,"insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif)VALUES('','$tanggal','$unit','$norek','0','$ijaroh','03','$ket2','$userid','REALISASI BAGHAS','$cif')");
                            db_query($con,"Update  loan set next_schedule ='$next_schedule',os =os-'$angsuran_pokok',run_tenor='$pb',ke='$ke_tenor'+1,last_payment='$tanggal' where cif  ='".$ciff."'");
                                             
                     
                            db_query($con,"insert  into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,blok,tgl_input,kode_transaksi)VALUES('$tanggal','$norek','$unit','".$ciff."','$code_kel','$angsuran','03','0','$userid','$ket2','','$cao','2','$tgl_input','$kode_tran') ");
                                                                     
                            db_query($con,"insert into jurnal_umum(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values('$nomor_jurnal','$kode_tran','$tanggal','$unit')");
                             
                            db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                                values('$unit','$kode_tran','2101000','$tanggal','Bukti SYSTEM','$ket2','$angsuran','$userid')");                         
                            //Realisasi Piutang Margin     
                            db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                                values('$unit','$kode_tran','1472000','$tanggal','Bukti SYSTEM','$ket2','$angsuran_pokok','$userid')");
                            //Realiasasi Murabahah Debit pada Piutang Murabahah total jumlah Pokok dan margin 
                            db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                                values('$unit','$kode_tran','41062','$tanggal','Bukti SYSTEM','$ket2','$nisbah_mus','$userid')");
                            db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                                values('$unit','$kode_tran','2867000','$tanggal','Bukti SYSTEM','$ket2','2500','$userid')");
               

                                  
 
                             
                            }else{
                             
                            db_query($con,"insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif)VALUES('','$tanggal','$unit','$no_anggota','0','$angsuran_pokok','03','$ket2','$userid','REALISASI POKOK','".$ciff."')");
                            db_query($con,"insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif)VALUES('','$tanggal','$unit','$no_anggota','0','$ijaroh','03','$ket2','$userid','REALISASI BAGHAS','".$ciff."')");
                             
                            db_query($con,"Update  loan set next_schedule ='$next_schedule',os =os-'$angsuran_pokok',run_tenor='$pb',ke='$ke_tenor'+1,last_payment='$tanggal' where cif  ='".$ciff."'");
                            db_query($con,"insert  into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,tgl_input,kode_transaksi)VALUES('$tanggal','$norek','$unit','".$ciff."','$code_kel','$angsuran','03','0','$userid','$ket','','$cao','$tgl_input','$kode_tran') ") ;
                            db_query($con,"insert into jurnal_umum(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values('$nomor_jurnal','$kode_tran','$tanggal','$unit')");
             

                            
                            db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                                values('$unit','$kode_tran','2101000','$tanggal','Bukti SYSTEM','$ket2','$angsuran','$userid')");                         
                            //Realisasi Piutang Margin     
                            db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                                values('$unit','$kode_tran','1472000','$tanggal','Bukti SYSTEM','$ket2','$angsuran_pokok','$userid')");
                            //Realiasasi Murabahah Debit pada Piutang Murabahah total jumlah Pokok dan margin 
                            db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                                values('$unit','$kode_tran','41062','$tanggal','Bukti SYSTEM','$ket2','$nisbah_mus','$userid')");
                            db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                                values('$unit','$kode_tran','2867000','$tanggal','Bukti SYSTEM','$ket2','2500','$userid')");
               

                            
                             
                             
                            }


        
        
        }

        }else{
db_query ($con,"INSERT INTO pinjaman_detail_rencana(id,id_pinjam,cicilan,angsuran_pokok,bunga,tgl_jatuh_tempo,tgl_bayar,jumlah_bayar,ket,cif,unit,ao)
                    VALUES('$new_id','$no_anggota','$urut','$pokok','$ijaroh','$next_schedule1','','','','$cif','$unit','$cao')");
                 
 
if ($saldo < $angsuran){    
                db_query ($con,"Update loan set next_schedule='$next_schedule1-1',ke='$ke_tenor'+1 where cif  ='$cif'");
                $ke_n= db_fetch_array(db_query ($con,"select ke from loan WHERE cif ='$cif'"));
                $ke_lagi=$ke_n['ke'];
         db_query($con,"insert  into tunggakan (tgl_tunggak,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,blok)VALUES('$tanggal','$no_anggota','$unit','$cif','$code_kel','0','01','$pokok','$userid','$ket','$cao','') ") ;
         db_query($con,"insert  into tunggakan (tgl_tunggak,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,blok)VALUES('$tanggal','$no_anggota','$unit','$cif','$code_kel','0','02','$ijaroh','$userid','$ket','$cao','') ");
           
                if ($maturity_date < $tgl_system){
                             
                 
        db_query ($con,"INSERT INTO pinjaman_detail (id,id_pinjam,cicilan,angsuran_pokok,bunga,tgl_jatuh_tempo,tgl_bayar,jumlah_bayar,ket,cif,unit,ao)
                values('$new_id','$no_anggota','$urut','$pokok','$ijaroh','$next_schedule1-1','','0','JATPO','$cif','$unit','$cao')");
     
                    db_query ($con,"INSERT INTO pinjaman_detail_jatpo(id,id_pinjam,cicilan,angsuran_pokok,bunga,tgl_jatuh_tempo,tgl_bayar,jumlah_bayar,ket)
                    VALUES('$new_id','$no_anggota','$urut','$pokok','$ijaroh','$next_schedule1-1','','','')");
                     
                     
                }else {
                  
                     
                }            
         
        }    
        else{
$cek_tunggak= db_fetch_array(db_query ($con,"select sum(KREDIT)- sum(DEBIT) as tunggak from tunggakan WHERE cif='$cif'"));
            $ada_tunggak=$cek_tunggak['tunggak'];    
             if($ada_tunggak>0){
                            db_query($con,"insert  into tunggakan (tgl_tunggak,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,blok)VALUES('$tanggal','$norek','$unit','".$ciff."','$code_kel','$angsuran_pokok','01','0','$userid','$ket','$cao','') ") ;
                            db_query($con,"insert  into tunggakan (tgl_tunggak,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,blok)VALUES('$tanggal','$norek','$unit','".$ciff."','$code_kel','$ijaroh','02','0','$userid','$ket','$cao','') ");
                            db_query($con,"insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif)VALUES('','$tanggal','$unit','$norek','0','$angsuran_pokok','03','$ket2','$userid','REALISASI POKOK','$cif')") ;
                            db_query($con,"insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif)VALUES('','$tanggal','$unit','$norek','0','$ijaroh','03','$ket2','$userid','REALISASI BAGHAS','$cif')");
                            db_query($con,"Update  loan set next_schedule ='$next_schedule',os =os-'$angsuran',saldo_margin=saldo_margin-'$ijaroh',run_tenor='$pb',ke='$ke_tenor'+1,last_payment='$tanggal' where cif  ='".$ciff."'");
                                        
                     
                            db_query($con,"insert  into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,blok,tgl_input,kode_transaksi)VALUES('$tanggal','$norek','$unit','".$ciff."','$code_kel','$angsuran','03','0','$userid','$ket2','','$cao','2','$tgl_input','$kode_tran') ");
                             
                                             
                            db_query($con,"insert into jurnal_umum(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values('$nomor_jurnal','$kode_tran','$tanggal','$unit')");
                             
if($deal_produk =='2'){
$margin = $ijaroh - 2500;
 db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                                values('$unit','$kode_tran','1413000','$tanggal','Bukti SYSTEM','$ket2','$angsuran','$userid')");                         
                            //Realisasi Piutang Margin     
                            db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                                values('$unit','$kode_tran','1423000','$tanggal','Bukti SYSTEM','$ket2','$ijaroh','$userid')");
                            //Realiasasi Murabahah Debit pada Piutang Murabahah total jumlah Pokok dan margin 
                            db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                                values('$unit','$kode_tran','41002','$tanggal','Bukti SYSTEM','$ket2','$margin','$userid')");
 db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                                values('$unit','$kode_tran','2867000','$tanggal','Bukti SYSTEM','$ket2','2500','$userid')");
                            db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                                values('$unit','$kode_tran','2101000','$tanggal','Bukti SYSTEM','$ket2','$angsuran','$userid')");
               

}elseif($deal_produk =='4'){
$margin = $ijaroh -2500;
db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                                values('$unit','$kode_tran','1413000','$tanggal','Bukti SYSTEM','$ket2','$angsuran','$userid')");
                            //Realisasi Piutang Margin     
                            db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                                values('$unit','$kode_tran','1423000','$tanggal','Bukti SYSTEM','$ket2','$ijaroh','$userid')");
                            //Realiasasi Murabahah Debit pada Piutang Murabahah total jumlah Pokok dan margin 
                            db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                                values('$unit','$kode_tran','41002','$tanggal','Bukti SYSTEM','$ket2','$margin','$userid')");
 db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                                values('$unit','$kode_tran','41002','$tanggal','Bukti SYSTEM','$ket2','2500','$userid')");
                            db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                                values('$unit','$kode_tran','2101000','$tanggal','Bukti SYSTEM','$ket2','$angsuran','$userid')");
 
}else{
 db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                                values('$unit','$kode_tran','1413000','$tanggal','Bukti SYSTEM','$ket2','$angsuran','$userid')");                         
                            //Realisasi Piutang Margin     
                            db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                                values('$unit','$kode_tran','1423000','$tanggal','Bukti SYSTEM','$ket2','$ijaroh','$userid')");
                            //Realiasasi Murabahah Debit pada Piutang Murabahah total jumlah Pokok dan margin 
                            db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                                values('$unit','$kode_tran','41002','$tanggal','Bukti SYSTEM','$ket2','$ijaroh','$userid')");
                            db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                                values('$unit','$kode_tran','2101000','$tanggal','Bukti SYSTEM','$ket2','$angsuran','$userid')");
               
}
                                  
 
                             
                            }else{
                             
                            db_query($con,"insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif)VALUES('','$tanggal','$unit','$no_anggota','0','$angsuran_pokok','03','$ket2','$userid','REALISASI POKOK','".$ciff."')");
                            db_query($con,"insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif)VALUES('','$tanggal','$unit','$no_anggota','0','$ijaroh','03','$ket2','$userid','REALISASI BAGHAS','".$ciff."')");
                       
                            db_query($con,"Update  loan set next_schedule ='$next_schedule',os =os-'$angsuran',saldo_margin=saldo_margin-'$ijaroh',run_tenor='$pb',ke='$ke_tenor'+1,last_payment='$tanggal' where cif  ='".$ciff."'");
                            db_query($con,"insert  into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,tgl_input,kode_transaksi)VALUES('$tanggal','$norek','$unit','".$ciff."','$code_kel','$angsuran','03','0','$userid','$ket','','$cao','$tgl_input','$kode_tran') ") ;
                            db_query($con,"insert into jurnal_umum(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values('$nomor_jurnal','$kode_tran','$tanggal','$unit')");
             
if($deal_produk =='2'){
$margin = $ijaroh - 2500;
db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                                values('$unit','$kode_tran','1413000','$tanggal','Bukti SYSTEM','$ket2','$angsuran','$userid')");
                            //Realisasi Piutang Margin     
                            db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                                values('$unit','$kode_tran','1423000','$tanggal','Bukti SYSTEM','$ket2','$ijaroh','$userid')");
                            //Realiasasi Murabahah Debit pada Piutang Murabahah total jumlah Pokok dan margin 
                            db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                                values('$unit','$kode_tran','41002','$tanggal','Bukti SYSTEM','$ket2','$margin','$userid')");
                            
db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                                values('$unit','$kode_tran','2867000','$tanggal','Bukti SYSTEM','$ket2','2500','$userid')");
db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                                values('$unit','$kode_tran','2101000','$tanggal','Bukti SYSTEM','$ket2','$angsuran','$userid')");
 
}elseif($deal_produk =='4'){
$margin = $ijaroh - 2500;
db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                                values('$unit','$kode_tran','1413000','$tanggal','Bukti SYSTEM','$ket2','$angsuran','$userid')");
                            //Realisasi Piutang Margin     
                            db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                                values('$unit','$kode_tran','1423000','$tanggal','Bukti SYSTEM','$ket2','$ijaroh','$userid')");
                            //Realiasasi Murabahah Debit pada Piutang Murabahah total jumlah Pokok dan margin 
                            db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                                values('$unit','$kode_tran','41002','$tanggal','Bukti SYSTEM','$ket2','$margin','$userid')");
db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                                values('$unit','$kode_tran','41002','$tanggal','Bukti SYSTEM','$ket2','2500','$userid')");
                            db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                                values('$unit','$kode_tran','2101000','$tanggal','Bukti SYSTEM','$ket2','$angsuran','$userid')");
 
}else{
db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                                values('$unit','$kode_tran','1413000','$tanggal','Bukti SYSTEM','$ket2','$angsuran','$userid')");
                            //Realisasi Piutang Margin     
                            db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                                values('$unit','$kode_tran','1423000','$tanggal','Bukti SYSTEM','$ket2','$ijaroh','$userid')");
                            //Realiasasi Murabahah Debit pada Piutang Murabahah total jumlah Pokok dan margin 
                            db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                                values('$unit','$kode_tran','41002','$tanggal','Bukti SYSTEM','$ket2','$ijaroh','$userid')");
                            db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                                values('$unit','$kode_tran','2101000','$tanggal','Bukti SYSTEM','$ket2','$angsuran','$userid')");
 
}

                            
                             
                             
                            }
            }
          
}    
    }
   
    if (!mysqli_commit($con)) {
        echo "Transaksi Gagal commit Silahkan ulangi lagi";
        exit();
      }
echo "<script>alert('Debet setoran Lebaran sukses di simpan');</script>";


 
?>


