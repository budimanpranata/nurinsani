<?php require_once __DIR__ . '/koneksi_db.php';
session_start();
ob_start();

$con = db_ni();
if (mysqli_connect_errno()) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
    exit;
  }
 
  // Turn autocommit off
mysqli_autocommit($con,false);

include "function.php";
 
$tanggal='2024-05-27'; 
$tgl_input=date("Y-m-d");

 $cek= db_fetch_array(db_query($con,"select param_tgl from param_tgl WHERE nama_param_tgl ='EOD_MOB'"));
$tanggal = $cek['param_tgl']; 

    

                   
        $select = db_query ($con,"Select cs.cif as cif,loan.angsuran,loan.cao,Cust_Short_name,loan.os as os,deal_produk,loan.saldo_margin as saldo_margin,
        no_anggota as norek ,code_kel,status_app,loan.pokok,run_tenor,loan.unit, loan.bulat as bulat,loan.ijaroh as ijaroh,nominal,status_trans
        from cs left join loan on loan.cif = cs.cif  where loan.os > 0 
 and status_app  in ('REST','REST_POKOK_MARGIN','COVID') and nominal ='0' and status_trans ='BAYAR' and tgl_tagih ='$tanggal'
order by loan.unit  ");
            while($row = db_fetch_array($select)){
                $next_schedule1=$row['next_schedule'];
                $no_anggota=$row['norek'];
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
                $code_kel=$row['code_kel'];
                $deal_produk=$row['deal_produk'];
                $saldo_margin=$row['saldo_margin'];
                $ke=$row['ke1'];
                $cif=$row['cif'];
                $cao=$row['cao'];
                $status_app=$row['status_app'];
                $tenor=$row['Tenor'];
                $ke_tenor=$row['next'];
                $nama=mysqli_real_escape_string ($con,$row['CUST_SHORT_NAME']);
                $ket="Setor Tagihan $nama $cif";
                $ket2="Setoran Tagihan $nama";
                $ket3="Tunggakan Tagihan $nama";
                 
                $kode_tr=7;
                $kode_tr=acakangkahuruf($kode_tr);      
                $kode_transaksi="BU/".$unit .$kode_tr;
                $wajib_kh=$bulat - $angsuran;
                $cek_next= db_fetch_array(db_query ($con,"select cicilan,tgl_jatuh_tempo from pinjaman_detail WHERE cicilan='$next_tenor' AND id_pinjam ='$no_anggota'"));
                $next_schedule=$cek_next['tgl_jatuh_tempo'];
                $next_cicilan=$cek_next['cicilan'];
                $row_sal= db_fetch_array(db_query ($con,"select(sum(KREDIT) - sum(DEBIT))as saldo from simpanan WHERE cif ='$cif'")) ;
                $sisa=$row_sal['saldo'];
                $row_sal= db_fetch_array(db_query ($con,"select(sum(KREDIT) - sum(DEBIT))as blok from simpanan WHERE type in ('05','02','04','07') and cif ='$cif'"));
                $blok=$row_sal['blok'];
                $next_schedule    = date('Y-m-d', strtotime('+7 days', strtotime($tanggal)));    
 
                

                $minggon = $bulat-$angsuran;
       
                //$total_twm = $minggon * $run_tenor;
		$total_twm=0;
      
             
                $saldo=$sisa-$total_twm;
if($deal_produk=='2'){
				
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
                                 
                //mysqli_query ($con,"insert  into tunggakan (tgl_tunggak,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,blok)VALUES('$tanggal','$no_anggota','$unit','$cif','$code_kel','0','01','$pokok','$userid','$ket','$cao','') ");
               
            //echo '<script>alert("Next angsuran  belum di tambahkan karean Menuggak."); window.location="yrs_list.php"</script>';
 
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
          
             
        }else{
db_query ($con,"INSERT INTO pinjaman_detail_rencana(id,id_pinjam,cicilan,angsuran_pokok,bunga,tgl_jatuh_tempo,tgl_bayar,jumlah_bayar,ket,cif,unit,ao)
                    VALUES('$new_id','$no_anggota','$urut','$pokok','$ijaroh','$next_schedule1','','','','$cif','$unit','$cao')");
                 
 
if ($saldo < $angsuran){    
                db_query ($con,"Update loan set next_schedule='$next_schedule1-1',ke='$ke_tenor'+1 where cif  ='$cif'");
                $ke_n= db_fetch_array(db_query ($con,"select ke from loan WHERE cif ='$cif'"));
                $ke_lagi=$ke_n['ke'];
                 
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
                db_query ($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                values('$unit','$kode_tran','1413000','$tanggal','Bukti SYSTEM','$ket','$angsuran','$userid')");
                 
                db_query ($con,"insert into jurnal_umum(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values('$nomor_jurnal','$kode_tran','$tanggal','$unit')");
                //Realisasi Piutang Margin    
                db_query ($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                            values('$unit','$kode_tran','1423000','$tanggal','Bukti SYSTEM','$ket','$ijaroh','$userid')");
                //Realiasasi Murabahah Debit pada Piutang Murabahah total jumlah Pokok dan margin
                db_query ($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                values('$unit','$kode_tran','41002','$tanggal','Bukti SYSTEM','$ket','$ijaroh','$userid')");
                db_query ($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                values('$unit','$kode_tran','2101000','$tanggal','Bukti SYSTEM','$ket','$angsuran','$userid')");
 
}else{
 
                db_query ($con,"insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif)VALUES('','$tanggal','$unit','$no_anggota','0','$pokok','03','$ket','$userid','REALISASI POKOK','$cif')");
                db_query ($con,"insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif)VALUES('','$tanggal','$unit','$no_anggota','0','$ijaroh','03','$ket','$userid','REALISASI BAGHAS','$cif')");
 
                db_query ($con,"Update  loan set next_schedule ='$next_schedule',os =os-'$angsuran',saldo_margin=saldo_margin-'$ijaroh',run_tenor='$pb',ke='$ke_tenor'+1,last_payment='$tanggal' where cif  ='$cif'");
                db_query ($con,"insert  into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,tgl_input,kode_transaksi)VALUES('$tanggal','$no_anggota','$unit','$cif','$code_kel','$angsuran','03','0','$userid','$ket','$cao','$tgl_input','$kode_tran') ");
                         $jurnal_umum=db_fetch_array(db_query ($con,"SELECT max(nomor_jurnal) FROM jurnal_umum where unit ='$unit' ORDER BY tanggal_selesai DESC"));
                $nomor_jurnal=$jurnal_umum[0]+1;
                             db_query ($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                values('$unit','$kode_tran','1413000','$tanggal','Bukti SYSTEM','$ket','$angsuran','$userid')");
                 
                db_query ($con,"insert into jurnal_umum(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values('$nomor_jurnal','$kode_tran','$tanggal','$unit')");
                //Realisasi Piutang Margin    
                db_query ($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                            values('$unit','$kode_tran','1423000','$tanggal','Bukti SYSTEM','$ket','$ijaroh','$userid')");
                //Realiasasi Murabahah Debit pada Piutang Murabahah total jumlah Pokok dan margin
                db_query ($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                values('$unit','$kode_tran','41002','$tanggal','Bukti SYSTEM','$ket','$ijaroh','$userid')");
                db_query ($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                values('$unit','$kode_tran','2101000','$tanggal','Bukti SYSTEM','$ket','$angsuran','$userid')");
                db_query ($con,"Update  tagihan_cs set status_realisasi ='BAYAR'  where cif  ='$cif' and tgl_tagih='$tanggal'");
 
                }
            }
          
}    
    }
   
    if (!mysqli_commit($con)) {
        echo "Transaksi Gagal commit Silahkan ulangi lagi";
        exit();
      }
echo "<script>alert('Setoran Kelompok REST sukses di simpan');</script>";


 
?>


