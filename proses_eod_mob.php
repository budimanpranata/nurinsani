<?php require_once __DIR__ . '/koneksi_db.php'; 
 
$con = db_ni();
if (mysqli_connect_errno()) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
    exit;
  }
 
// Turn autocommit off
mysqli_autocommit($con,FALSE);
 
 
 
include "function.php";
 
//$tanggal="2024-05-27";

 $cek= db_fetch_array(db_query($con,"select param_tgl from param_tgl WHERE nama_param_tgl ='EOD_MOB'"));
$tanggal = $cek['param_tgl']; 
//$tanggal="2026-01-05";
         
$select = db_query ($con,"Select cs.cif as cif,loan.angsuran,loan.cao,Cust_Short_name,loan.os as os,deal_produk,loan.saldo_margin as saldo_margin,
no_anggota as norek ,code_kel,status_app,loan.pokok,run_tenor,loan.unit, loan.bulat as bulat,loan.ijaroh as ijaroh,nominal
from cs left join loan on loan.cif = cs.cif  where loan.os > 0 and tgl_tagih ='$tanggal' order by loan.unit");
while($data = db_fetch_array($select)){
 
        $norek=$data['norek'];
        $unit=$data ['unit'];
        $userid=$unit;
        $angsuran=$data ['angsuran'];
        $nominal=$data ['nominal'];
        $angsuran_pokok=$data ['pokok'];
        $ijaroh=$data ['ijaroh'];
        $nisbah_mus=$data['ijaroh'];
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
 
        $angsuran=$data['angsuran'];
        $bulat=$data['bulat'];      
        $next_schedule    = date('Y-m-d', strtotime('+7 days', strtotime($tanggal)));    
 
 
        $cek_tunggak= db_fetch_array(db_query($con,"select sum(KREDIT)- sum(DEBIT) as tunggak from tunggakan WHERE cif ='".$ciff."'"));
        $ada_tunggak=$cek_tunggak['tunggak']; 
        $ft=round(compat_num($ada_tunggak/$angsuran));  
        $simpanan_minggon=$bulat - $angsuran;         
        $setoran=$nominal - $simpanan_minggon;      
        
        if($deal_produk=='2'){
			$simpanan_minggon_asli=$bulat - $angsuran;
			$simpanan_minggon=$bulat - $angsuran - 2500;
			$simpan_duaribu = '2500';		
			$angsuran=$angsuran + 2500;
			$ijaroh = $ijaroh + 2500;


	$setoran=$nominal - $simpanan_minggon_asli;

}elseif($deal_produk=='4'){
$simpanan_minggon_asli=$bulat - $angsuran;
			$simpanan_minggon=$bulat - $angsuran - 2500;
			$simpan_duaribu = '2500';		
			$angsuran=$angsuran + 2500;
			$ijaroh = $ijaroh + 2500;


	$setoran=$nominal - $simpanan_minggon_asli;




}else{
			
				$simpanan_minggon=$bulat - $angsuran;
				$simpan_duaribu = '0';		

		$setoran=$nominal - $simpanan_minggon;
		$angsuran=$angsuran ;
			$ijaroh = $ijaroh;


		}

if ($saldo_margin>$ijaroh){
$ijaroh=$ijaroh;
}else{
$ijaroh=$saldo_margin;
}    
		
				
        $nama=mysqli_real_escape_string($con,($data['Cust_Short_name']));
        $ket ="Setoran an $nama $cif";
        $ket2 ="Angsuran  an $nama $cif";
        $status_app=$data['status_app'];
 
        $kodetr=8;
        $kodetr=acakangkahuruf($kodetr);
        $kode_trans="BU/".$unit.$kodetr;
 
        $kodetrs=8;
        $kodetrs=acakangkahuruf($kodetrs);
        $kode_tran="BU/".$unit.$kodetrs;
        
         
        if ($nominal =='0'){
		
            db_query ($con,"insert  into tunggakan (tgl_tunggak,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,blok)VALUES('$tanggal','$norek','$unit','".$ciff."','$code_kel','0','01','$angsuran_pokok','$userid','$ket','$cao','') ");
            db_query ($con,"insert  into tunggakan (tgl_tunggak,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,blok)VALUES('$tanggal','$norek','$unit','".$ciff."','$code_kel','0','02','$ijaroh','$userid','$ket','$cao','') ");
             
            if ($ada_tunggak>=$os){ 
                 db_query ($con,"delete from tunggakan where cif ='".$ciff."' and tgl_tunggak ='$tanggal' ");
            }else{
		$s=0;

	     }
 
            }elseif($nominal < $bulat){         
         
            db_query ($con,"insert  into tunggakan (tgl_tunggak,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,blok)VALUES('$tanggal','$norek','$unit','".$ciff."','$code_kel','0','01','$angsuran_pokok','$userid','$ket','$cao','') ");
            db_query ($con,"insert  into tunggakan (tgl_tunggak,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,blok)VALUES('$tanggal','$norek','$unit','".$ciff."','$code_kel','0','02','$ijaroh','$userid','$ket','$cao','') ");
         
 
                $input ="Insert into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,blok,tgl_input,kode_transaksi)
            VALUES('$tanggal','$norek','$unit','".$ciff."','$code_kel',0,'01','$setoran','$userid','$ket','','$cao','0','$tanggal','$kode_trans')";
            $inputtrnsk = db_query ($con,$input);
            $input ="Insert into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,blok,tgl_input,kode_transaksi)
            VALUES('$tanggal','$norek','$unit','".$ciff."','$code_kel',0,'02','$simpanan_minggon','$userid','$ket','','$cao','1','$tanggal','$kode_trans')";
            $inputtrnsk = db_query ($con,$input);

            $input ="Insert into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,blok,tgl_input,kode_transaksi)
            VALUES('$tanggal','$norek','$unit','".$ciff."','$code_kel',0,'01','$simpan_duaribu','$userid','$ket','','$cao','1','$tanggal','$kode_trans')";
            $inputtrnsk = db_query ($con,$input);
        
            $jurnal_umum=db_fetch_array(db_query ($con,"SELECT max(nomor_jurnal) FROM jurnal_umum where unit ='$unit' ORDER BY tanggal_selesai DESC"));
            $nomor_jurnal=$jurnal_umum[0]+1;
            //pencatatan du jurnal realasisi Piutang Murabahaha total jumlah Pokok dan margin
            db_query ($con,"insert into jurnal_masuk(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values('$nomor_jurnal','$kode_trans','$tanggal','$unit')");
            db_query ($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
            values('$unit','$kode_trans','1120000','$tanggal','Bukti SYSTEM','$ket','$nominal','$userid')");
            
            db_query ($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
            values('$unit','$kode_trans','2101000','$tanggal','Bukti SYSTEM','$ket','$nominal','$userid')");
           
 
            db_query ($con,"delete from simpanan where DEBIT ='0' and KREDIT ='0' and cif ='$ciff' and BUSS_DATE ='$tanggal'");
         
            
     
        }else{           
 
         
         
     $input ="Insert into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,blok,tgl_input,kode_transaksi)
     VALUES('$tanggal','$norek','$unit','".$ciff."','$code_kel',0,'01','$setoran','$userid','$ket','','$cao','0','$tanggal','$kode_trans')";
      $inputtrnsk = db_query ($con,$input);
      $input ="Insert into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,blok,tgl_input,kode_transaksi)
     VALUES('$tanggal','$norek','$unit','".$ciff."','$code_kel',0,'02','$simpanan_minggon','$userid','$ket','','$cao','1','$tanggal','$kode_trans')";
     $inputtrnsk = db_query ($con,$input);
     $input ="Insert into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,blok,tgl_input,kode_transaksi)
     VALUES('$tanggal','$norek','$unit','".$ciff."','$code_kel',0,'01','$simpan_duaribu','$userid','$ket','','$cao','1','$tanggal','$kode_trans')";
    
     if($ada_tunggak >0 ){
 
        db_query ($con,"insert  into tunggakan (tgl_tunggak,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,blok)VALUES('$tanggal','$norek','$unit','".$ciff."','$code_kel','0','01','$angsuran_pokok','$userid','$ket','$cao','') ");
        db_query ($con,"insert  into tunggakan (tgl_tunggak,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,blok)VALUES('$tanggal','$norek','$unit','".$ciff."','$code_kel','0','02','$ijaroh','$userid','$ket','$cao','') ");
 
  
 
     }   
     

     
     
     $inputtrnsk = db_query ($con,$input);
     db_query ($con,"delete from simpanan where DEBIT ='0' and KREDIT ='0' and cif ='$ciff' and BUSS_DATE ='$tanggal'");
     
            if($inputtrnsk){
          
                    
 
                $jurnal_umum=db_fetch_array(db_query ($con,"SELECT max(nomor_jurnal) FROM jurnal_umum where unit ='$unit' ORDER BY tanggal_selesai DESC"));
                $nomor_jurnal=$jurnal_umum[0]+1;
                //pencatatan du jurnal realasisi Piutang Murabahaha total jumlah Pokok dan margin
                db_query ($con,"insert into jurnal_masuk(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values('$nomor_jurnal','$kode_trans','$tanggal','$unit')");
                db_query ($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                values('$unit','$kode_trans','1120000','$tanggal','Bukti SYSTEM','$ket','$nominal','$userid')");
                
                db_query ($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                values('$unit','$kode_trans','2101000','$tanggal','Bukti SYSTEM','$ket','$nominal','$userid')");
 
 
 
 
                switch ($status_app) {
                    case "REST":
                        $jurnal_umum=db_fetch_array(db_query($con,"SELECT max(nomor_jurnal) FROM jurnal_umum where unit ='$unit' ORDER BY tanggal_selesai DESC"));
                        $nomor_jurnal=$jurnal_umum[0]+1;
 
                        if($ada_tunggak>0){
                            db_query($con,"insert  into tunggakan (tgl_tunggak,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,blok)VALUES('$tanggal','$norek','$unit','".$ciff."','$code_kel','$angsuran_pokok','01','0','$userid','$ket','$cao','') ") ;
                            db_query ($con,"insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif)VALUES('','$tanggal','$unit','$norek','0','$angsuran_pokok','03','$ket2','$userid','REALISASI POKOK','".$ciff."')");
                            db_query ($con,"Update  loan set next_schedule ='$next_schedule',os =os-'$angsuran',saldo_margin=saldo_margin-'$ijaroh',run_tenor='$pb',ke='$ke_tenor'+1 ,last_payment='$tanggal' where cif  ='".$ciff."'");
                            db_query ($con,"insert  into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,tgl_input,kode_transaksi)VALUES('$tanggal','$norek','$unit','".$ciff."','$code_kel','$angsuran','03','0','$userid','$ket2','$cao','$tgl_input','$kode_tran') ");
                         
                            db_query ($con,"Update  pinjaman_detail set jumlah_bayar ='$angsuran',tgl_bayar='$tanggal' where id_pinjam  ='$norek' and cicilan='$pb'");
                             
                            db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                            values('$unit','$kode_tran','2101000','$tanggal','Bukti SYSTEM','$ket2','$angsuran','$userid')");
                            db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                            values('$unit','$kode_tran','1411000','$tanggal','Bukti SYSTEM','$ket2','$angsuran','$userid')");
                       
 
                        }else{
                        db_query ($con,"insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif)VALUES('','$tanggal','$unit','$norek','0','$angsuran_pokok','03','$ket2','$userid','REALISASI POKOK','".$ciff."')");
                        db_query ($con,"Update  loan set next_schedule ='$next_schedule',os =os-'$angsuran',saldo_margin=saldo_margin-'$ijaroh',run_tenor='$pb',ke='$ke_tenor'+1 ,last_payment='$tanggal' where cif  ='".$ciff."'");
                        db_query ($con,"insert  into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,tgl_input,kode_transaksi)VALUES('$tanggal','$norek','$unit','".$ciff."','$code_kel','$angsuran','03','0','$userid','$ket2','$cao','$tgl_input','$kode_tran') ");
                     
                        db_query ($con,"Update  pinjaman_detail set jumlah_bayar ='$angsuran',tgl_bayar='$tanggal' where id_pinjam  ='$norek' and cicilan='$pb'");
                         
                                                db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                        values('$unit','$kode_tran','2101000','$tanggal','Bukti SYSTEM','$ket2','$angsuran','$userid')");
                        db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                        values('$unit','$kode_tran','1411000','$tanggal','Bukti SYSTEM','$ket2','$angsuran','$userid')");
                          
 
                         
 
                        }
                 
                      break;
                    case "REST_POKOK_MARGIN":
 
                    $jurnal_umum=db_fetch_array(db_query($con,"SELECT max(nomor_jurnal) FROM jurnal_umum where unit ='$unit' ORDER BY tanggal_selesai DESC"));
                    $nomor_jurnal=$jurnal_umum[0]+1;                 
                     
                    if($ada_tunggak > 0){
                    db_query($con,"insert  into tunggakan (tgl_tunggak,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,blok)VALUES('$tanggal','$norek','$unit','".$ciff."','$code_kel','$angsuran_pokok','01','0','$userid','$ket','$cao','') ") ;
                    db_query($con,"insert  into tunggakan (tgl_tunggak,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,blok)VALUES('$tanggal','$norek','$unit','".$ciff."','$code_kel','$ijaroh','02','0','$userid','$ket','$cao','') ");
                    db_query($con,"insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif)VALUES('','$tanggal','$unit','$norek','0','$angsuran_pokok','03','$ket','$userid','REALISASI POKOK','".$ciff."')") ;
                    db_query($con,"insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif)VALUES('','$tanggal','$unit','$norek','0','$ijaroh','03','$ket','$userid','REALISASI BAGHAS','".$ciff."')");
                    db_query($con,"Update  loan set next_schedule ='$next_schedule',os =os-'$angsuran',saldo_margin=saldo_margin-'$ijaroh',run_tenor='$pb',ke='$ke_tenor'+1,last_payment='$tanggal' where cif  ='".$ciff."'") ;
                                             
                    db_query($con,"insert  into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,blok,tgl_input,kode_transaksi)VALUES('$tanggal','$norek','$unit','".$ciff."','$code_kel','$angsuran','03','0','$userid','$ket2','','$cao','2','$tgl_input','$kode_tran') ") ;
                    db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                        values('$unit','$kode_tran','2101000','$tanggal','Bukti SYSTEM','$ket2','$angsuran','$userid')");
 
                    db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                        values('$unit','$kode_tran','1411000','$tanggal','Bukti SYSTEM','$ket2','$angsuran','$userid')");
                    db_query($con,"insert into jurnal_umum(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values('$nomor_jurnal','$kode_tran','$tanggal','$unit')");
                        //Realisasi Piutang Margin     
                    db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                        values('$unit','$kode_tran','1421000','$tanggal','Bukti SYSTEM','$ket2','$ijaroh','$userid')");
                        //Realiasasi Murabahah Debit pada Piutang Murabahah total jumlah Pokok dan margin 
                    db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                        values('$unit','$kode_tran','41002','$tanggal','Bukti SYSTEM','$ket2','$ijaroh','$userid')");
 
                       
 
                    }else{
 
                        db_query($con,"insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif)VALUES('','$tanggal','$unit','$norek','0','$angsuran_pokok','03','$ket2','$userid','REALISASI POKOK','".$ciff."')");
                        db_query($con,"insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif)VALUES('','$tanggal','$unit','$norek','0','$ijaroh','03','$ket2','$userid','REALISASI BAGHAS','".$ciff."')");
 
                        db_query($con,"Update  loan set next_schedule ='$next_schedule',os =os-'$angsuran',saldo_margin=saldo_margin-'$ijaroh',run_tenor='$pb',ke='$ke_tenor'+1,last_payment='$tanggal' where cif  ='".$ciff."'");
                        db_query($con,"insert  into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,tgl_input,kode_transaksi)VALUES('$tanggal','$norek','$unit','".$ciff."','$code_kel','$angsuran','03','0','$userid','$ket2','$cao','$tgl_input','$kode_tran') ");
                         
                        db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                        values('$unit','$kode_tran','2101000','$tanggal','Bukti SYSTEM','$ket2','$angsuran','$userid')");
 
                        db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                        values('$unit','$kode_tran','1411000','$tanggal','Bukti SYSTEM','$ket2','$angsuran','$userid')");
                         
                        db_query($con,"insert into jurnal_umum(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values('$nomor_jurnal','$kode_tran','$tanggal','$unit')");
                        //Realisasi Piutang Margin     
                        db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                                    values('$unit','$kode_tran','1421000','$tanggal','Bukti SYSTEM','$ket2','$ijaroh','$userid')");
                        //Realiasasi Murabahah Debit pada Piutang Murabahah total jumlah Pokok dan margin 
                        db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                        values('$unit','$kode_tran','41002','$tanggal','Bukti SYSTEM','$ket2','$ijaroh','$userid')");
 
                           
 
                        }
 
                      
                      break;
                    case "MURAB":
                        $jurnal_umum=db_fetch_array(db_query($con,"SELECT max(nomor_jurnal) FROM jurnal_umum where unit ='$unit' ORDER BY tanggal_selesai DESC"));
                        $nomor_jurnal=$jurnal_umum[0]+1;  
                        db_query ($con,"Update  pinjaman_detail set jumlah_bayar ='$angsuran',tgl_bayar='$tanggal' where id_pinjam  ='$norek' and cicilan='$pb'");
                                    
                         
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
                            db_query($con,"insert  into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,tgl_input,kode_transaksi)VALUES('$tanggal','$norek','$unit','".$ciff."','$code_kel','$angsuran','03','0','$userid','$ket','$cao','$tgl_input','$kode_tran') ") ;
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
                      
                      break;

                    case "MUSYAROKAH":
                    $cek_omzet= db_fetch_array(db_query($con,"SELECT nominal from omzet WHERE tanggal ='$tanggal' and cif ='$ciff'"));
        			$omzet=$cek_omzet['nominal']; 

					$cek_nisbah= db_fetch_array(db_query($con,"SELECT nisbah, omzet from nisbah WHERE cif ='$ciff'"));
        			$nisbah=$cek_nisbah['nisbah'];
				$omzet_awal=$cek_nisbah['omzet']; 

					$porsi= $omzet * ($nisbah_mus/$omzet_awal);
                    $angsuran_pokok = $angsuran - $nisbah_mus - 2500;
                     db_query ($con,"Update  pinjaman_detail_mus set nisbah_anggota ='$porsi',nisbah ='$nisbah_mus',tgl_bayar='$tanggal' where id_pinjam  ='$norek' and cicilan='$pb'");
                       


                          $jurnal_umum=db_fetch_array(db_query($con,"SELECT max(nomor_jurnal) FROM jurnal_umum where unit ='$unit' ORDER BY tanggal_selesai DESC"));
                        $nomor_jurnal=$jurnal_umum[0]+1;                 
                         
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
                            db_query($con,"insert  into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,tgl_input,kode_transaksi)VALUES('$tanggal','$norek','$unit','".$ciff."','$code_kel','$angsuran','03','0','$userid','$ket','$cao','$tgl_input','$kode_tran') ") ;
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


                

                    break;

                    default:
                    $jurnal_umum=db_fetch_array(db_query($con,"SELECT max(nomor_jurnal) FROM jurnal_umum where unit ='$unit' ORDER BY tanggal_selesai DESC"));
                    $nomor_jurnal=$jurnal_umum[0]+1;                 
                     
                    if($ada_tunggak>0){
                        db_query($con,"insert  into tunggakan (tgl_tunggak,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,blok)VALUES('$tanggal','$norek','$unit','".$ciff."','$code_kel','$angsuran_pokok','01','0','$userid','$ket2','$cao','') ") ;
                        db_query($con,"insert  into tunggakan (tgl_tunggak,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,blok)VALUES('$tanggal','$norek','$unit','".$ciff."','$code_kel','$ijaroh','02','0','$userid','$ket2','$cao','') ");
                        db_query($con,"insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif)VALUES('','$tanggal','$unit','$norek','0','$angsuran_pokok','03','$ket2','$userid','REALISASI POKOK','$cif')") ;
                        db_query($con,"insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif)VALUES('','$tanggal','$unit','$norek','0','$ijaroh','03','$ket2','$userid','REALISASI BAGHAS','$cif')");
                        db_query($con,"Update  loan set next_schedule ='$next_schedule',os =os-'$angsuran',saldo_margin=saldo_margin-'$ijaroh',run_tenor='$pb',ke='$ke_tenor'+1,last_payment='$tanggal' where cif  ='".$ciff."'");
                                         
                 
                        db_query($con,"insert  into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,blok,tgl_input,kode_transaksi)VALUES('$tanggal','$norek','$unit','".$ciff."','$code_kel','$angsuran','03','0','$userid','$ket2','','$cao','2','$tgl_input','$kode_tran') ");
                         
                                         
                        db_query($con,"insert into jurnal_umum(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values('$nomor_jurnal','$kode_tran','$tanggal','$unit')");
                         
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
             
                        }else{
                         
                        db_query($con,"insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif)VALUES('','$tanggal','$unit','$no_anggota','0','$angsuran_pokok','03','$ket2','$userid','REALISASI POKOK','".$ciff."')");
                        db_query($con,"insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif)VALUES('','$tanggal','$unit','$no_anggota','0','$ijaroh','03','$ket2','$userid','REALISASI BAGHAS','".$ciff."')");
                         
                        db_query($con,"Update  loan set next_schedule ='$next_schedule',os =os-'$angsuran',saldo_margin=saldo_margin-'$ijaroh',run_tenor='$pb',ke='$ke_tenor'+1,last_payment='$tanggal' where cif  ='".$ciff."'");
                        db_query($con,"insert  into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,tgl_input,kode_transaksi)VALUES('$tanggal','$norek','$unit','".$ciff."','$code_kel','$angsuran','03','0','$userid','$ket2','$cao','$tgl_input','$kode_tran') ") ;
                        db_query($con,"insert into jurnal_umum(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values('$nomor_jurnal','$kode_tran','$tanggal','$unit')");
         
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
              
                
 
 
 
 
 
 
 
 
 
 
 
                
            }else{
            echo "<script>alert('Maaf,.Proses Input Gagal');</script>";
         
            }
             
         
         
         
        }
         
         
    }

 
    if (!mysqli_commit($con)) {
        echo "Transaksi Gagal commit Silahkan ulangi lagi";
        exit();
      }
echo "<script>alert('Setoran Kelompok sukses di simpan');</script>";

     
    
 
?>
