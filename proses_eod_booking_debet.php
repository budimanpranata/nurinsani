<?php require_once __DIR__ . '/koneksi_db.php'; 
date_default_timezone_set('Asia/Jakarta');

 
$con = db_ni();
if (mysqli_connect_errno()) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
    exit;
  }
 
// Turn autocommit off
mysqli_autocommit($con,FALSE);
 
 
 
include "function.php";
 


 $cek= db_fetch_array(db_query($con,"select param_tgl from param_tgl WHERE nama_param_tgl ='EOD_MOB'"));
$tanggal = $cek['param_tgl']; 

$tanggal = '2025-07-31';
         
$select = db_query ($con,"Select booking_debet.cif as cif,loan.angsuran,loan.cao,Cust_Short_name,loan.os as os,deal_produk,loan.saldo_margin as saldo_margin,
no_anggota as norek ,code_kel,status_app,loan.pokok,run_tenor,loan.unit, loan.bulat as bulat,loan.ijaroh as ijaroh
from booking_debet left join loan on loan.cif = booking_debet.cif  where loan.os > 0 and tgl_booking ='$tanggal' and status_booking='Booking Debet'  order by loan.unit");
while($data = db_fetch_array($select)){
 
        $norek=$data['norek'];
        $unit=$data ['unit'];
        $angsuran=$data ['angsuran'];
        $nominal=$data ['nominal'];
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
        $ft=round(compat_num($ada_tunggak/$angsuran));
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

        var_dump($twm);
        
        $simpanan = $saldo_simpanan - $twm;

if ($saldo_margin>$ijaroh){
$ijaroh=$ijaroh;
}else{
$ijaroh=$saldo_margin;
}    

      
        if($deal_produk=='2'){
            $total_tunggak = $ada_tunggak;
            $total_angsuran_margin = ($ijaroh + 2500) * $ft;	          
            $total_angsuran_pokok= $total_tunggak - $total_angsuran_margin;	  
            $angsuran=$angsuran + 2500;
            $angsuran_pokok = $angsuran_pokok * $ft;
	    $angsuran_margin = ($ijaroh + 2500) * $ft;
            
	    $angsuran_pokok_tunggak = $angsuran_pokok * $ft;
            $angsuran_margin_tunggak = $ijaroh * $ft;

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
        $ket ="Setoran an $nama $cif";
        $ket2 ="Angsuran  an $nama $cif";
 
        $kodetr=8;
        $kodetr=acakangkahuruf($kodetr);
        $kode_trans="BU/".$unit.$kodetr;
 
        $kodetrs=8;
        $kodetrs=acakangkahuruf($kodetrs);
        $kode_tran="BU/".$unit.$kodetrs;
     
     if ($simpanan >= $ada_tunggak) { 
 
                switch ($status_app) {
                    case "REST":
                        $jurnal_umum=db_fetch_array(db_query($con,"SELECT max(nomor_jurnal) FROM jurnal_umum where unit ='$unit' ORDER BY tanggal_selesai DESC"));
                        $nomor_jurnal=$jurnal_umum[0]+1;
 
                        if($ada_tunggak>0){
                            db_query($con,"insert  into tunggakan (tgl_tunggak,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,blok)VALUES('$tanggal','$norek','$unit','".$ciff."','$code_kel','$angsuran_pokok_tunggak','01','0','$userid','$ket','$cao','') ") ;
                            db_query ($con,"insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif)VALUES('','$tanggal','$unit','$norek','0','$angsuran_pokok_tunggak','03','$ket2','$userid','REALISASI POKOK','".$ciff."')");
                            db_query ($con,"Update  loan set next_schedule ='$next_schedule',os =os-'$total_tunggak',saldo_margin=saldo_margin-'$total_angsuran_margin',run_tenor='$pb',ke='$ke_tenor'+1 ,last_payment='$tanggal' where cif  ='".$ciff."'");
db_query($con,"update booking_debet set status_booking ='Done' where cif ='$ciff'");
                            db_query ($con,"insert  into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,tgl_input,kode_transaksi)VALUES('$tanggal','$norek','$unit','".$ciff."','$code_kel','$total_tunggak','03','0','$userid','$ket2','$cao','$tgl_input','$kode_tran') ");
                            db_query ($con,"Update  pinjaman_detail set jumlah_bayar ='$total_tunggak',tgl_bayar='$tanggal' where id_pinjam  ='$norek' and cicilan='$pb'");
                            db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                            values('$unit','$kode_tran','2101000','$tanggal','Bukti SYSTEM','$ket2','$total_tunggak','$userid')");
                            db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                            values('$unit','$kode_tran','1411000','$tanggal','Bukti SYSTEM','$ket2','$total_tunggak','$userid')");
                    
                        }else{
                    
                         echo "<script>alert('Saldo $ama tidak mempunyai tunggakan');</script>";
 
                        }
                 
                      break;
                    case "REST_POKOK_MARGIN":
 
                    $jurnal_umum=db_fetch_array(db_query($con,"SELECT max(nomor_jurnal) FROM jurnal_umum where unit ='$unit' ORDER BY tanggal_selesai DESC"));
                    $nomor_jurnal=$jurnal_umum[0]+1;                 
                     
                    if($ada_tunggak > 0){
                    db_query($con,"insert  into tunggakan (tgl_tunggak,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,blok)VALUES('$tanggal','$norek','$unit','".$ciff."','$code_kel','$angsuran_pokok_tunggak','01','0','$userid','$ket','$cao','') ") ;
                    db_query($con,"insert  into tunggakan (tgl_tunggak,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,blok)VALUES('$tanggal','$norek','$unit','".$ciff."','$code_kel','$angsuran_margin_tunggak','02','0','$userid','$ket','$cao','') ");
                    db_query($con,"insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif)VALUES('','$tanggal','$unit','$norek','0','$angsuran_pokok','03','$ket','$userid','REALISASI POKOK','".$ciff."')") ;
                    db_query($con,"insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif)VALUES('','$tanggal','$unit','$norek','0','$angsuran_margin','03','$ket','$userid','REALISASI BAGHAS','".$ciff."')");
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
 
                    
                           echo "<script>alert('Saldo $ama tidak mempunyai tunggakan');</script>";
 
                        }
 
                      
                      break;
                    case "MURAB":
                        $jurnal_umum=db_fetch_array(db_query($con,"SELECT max(nomor_jurnal) FROM jurnal_umum where unit ='$unit' ORDER BY tanggal_selesai DESC"));
                        $nomor_jurnal=$jurnal_umum[0]+1;                 
                         
                        if($ada_tunggak>0){
                            db_query($con,"insert  into tunggakan (tgl_tunggak,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,blok)VALUES('$tanggal','$norek','$unit','".$ciff."','$code_kel','$angsuran_pokok_tunggak','01','0','$userid','$ket','$cao','') ") ;
                            db_query($con,"insert  into tunggakan (tgl_tunggak,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,blok)VALUES('$tanggal','$norek','$unit','".$ciff."','$code_kel','$angsuran_margin_tunggak','02','0','$userid','$ket','$cao','') ");
                            db_query($con,"insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif)VALUES('','$tanggal','$unit','$norek','0','$angsuran_pokok_tunggak','03','$ket2','$userid','REALISASI POKOK','$cif')") ;
                            db_query($con,"insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif)VALUES('','$tanggal','$unit','$norek','0','$angsuran_margin_tunggak','03','$ket2','$userid','REALISASI BAGHAS','$cif')");
                            db_query($con,"Update  loan set next_schedule ='$next_schedule',os =os-'$total_tunggak',saldo_margin=saldo_margin-'$total_angsuran_margin',run_tenor='$pb',ke='$ke_tenor'+1,last_payment='$tanggal' where cif  ='".$ciff."'");
                                             
                     
                            db_query($con,"insert  into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,blok,tgl_input,kode_transaksi)VALUES('$tanggal','$norek','$unit','".$ciff."','$code_kel','$total_tunggak','03','0','$userid','$ket2','','$cao','2','$tgl_input','$kode_tran') ");
                             
                                             
                            db_query($con,"insert into jurnal_umum(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values('$nomor_jurnal','$kode_tran','$tanggal','$unit')");
                             
                            db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                                values('$unit','$kode_tran','1413000','$tanggal','Bukti SYSTEM','$ket2','$total_tunggak','$userid')");                         
                            //Realisasi Piutang Margin     
                            db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                                values('$unit','$kode_tran','1423000','$tanggal','Bukti SYSTEM','$ket2','$total_angsuran_margin','$userid')");
                            //Realiasasi Murabahah Debit pada Piutang Murabahah total jumlah Pokok dan margin 
                            db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                                values('$unit','$kode_tran','41002','$tanggal','Bukti SYSTEM','$ket2','$total_angsuran_margin','$userid')");
                            db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                                values('$unit','$kode_tran','2101000','$tanggal','Bukti SYSTEM','$ket2','$total_tunggak','$userid')");
                      db_query($con,"update booking_debet set status_booking ='Done' where cif ='$ciff'");
 
                             
                            }else{
                             echo "<script>alert('Saldo $ama tidak mempunyai tunggakan');</script>";
                            
                             
                            }
                      
                      break;
                      case "MUSYAROKAH":
						$jurnal_umum=db_fetch_array(db_query($con,"SELECT max(nomor_jurnal) FROM jurnal_umum where unit ='$unit' ORDER BY tanggal_selesai DESC"));
						$nomor_jurnal=$jurnal_umum[0]+1;                 
						
						if($ada_tunggak>0){
							db_query($con,"insert  into tunggakan (tgl_tunggak,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,blok)VALUES('$tanggal','$norek','$unit','".$checbox[$i]."','$code_kel','$angsuran_pokok','01','0','$userid','BH','$ket','$cao','') ") ;
							db_query($con,"insert  into tunggakan (tgl_tunggak,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,blok)VALUES('$tanggal','$norek','$unit','".$checbox[$i]."','$code_kel','$ijaroh','02','0','$userid','BH','$ket','$cao','') ");
							db_query($con,"insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif)VALUES('','$tanggal','$unit','$norek','0','$angsuran_pokok','03','$ket2','$userid','REALISASI POKOK','$cif')") ;
							db_query($con,"insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif)VALUES('','$tanggal','$unit','$norek','0','$ijaroh','03','$ket2','$userid','REALISASI BAGHAS','$cif')");
							db_query($con,"Update  loan set next_schedule ='$next_schedule',os =os-'$total_tunggak',saldo_margin=saldo_margin-'$itotal_angsuran_marginjaroh',run_tenor='$pb',ke='$ke_tenor'+1,last_payment='$tanggal' where cif  ='".$checbox[$i]."'");
											 
					
							db_query($con,"insert  into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,blok,tgl_input,kode_transaksi)VALUES('$tanggal','$norek','$unit','".$checbox[$i]."','$code_kel','$angsuran','03','0','$userid','$ket','','$cao','2','$tgl_input','$kode_tran') ");
							
											 
							db_query($con,"insert into jurnal_umum(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values('$nomor_jurnal','$kode_tran','$tanggal','$unit')");
							
													//Realisasi Piutang Margin     
							db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
								values('$unit','$kode_tran','1472000','$tanggal','Bukti SYSTEM','$ket2','$total_angsuran_margin','$userid')");
							//Realiasasi Murabahah Debit pada Piutang Murabahah total jumlah Pokok dan margin 
							db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
								values('$unit','$kode_tran','2101000','$tanggal','Bukti SYSTEM','$ket2','$total_tunggak','$userid')");
							db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
								values('$unit','$kode_tran','41062','$tanggal','Bukti SYSTEM','$ket2','$total_angsuran_margin','$userid')");
							
							db_query ($con,"Update  tagihan_cs set status_realisasi ='BAYAR'  where cif  ='".$checbox[$i]."' and tgl_tagih='$tanggal'");

							 
							}else{
							 
                                echo "<script>alert('Saldo $ama tidak mempunyai tunggakan');</script>";
							 
							 
							}
					  
					  break;
                    case "COVID":
                        $jurnal_umum=db_fetch_array(db_query($con,"SELECT max(nomor_jurnal) FROM jurnal_umum where unit ='$unit' ORDER BY tanggal_selesai DESC"));
                        $nomor_jurnal=$jurnal_umum[0]+1;                 
                         
                        if($ada_tunggak>0){
                            db_query($con,"insert  into tunggakan (tgl_tunggak,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,blok)VALUES('$tanggal','$norek','$unit','".$ciff."','$code_kel','$angsuran_pokok_tunggak','01','0','$userid','$ket','$cao','') ") ;
                            db_query($con,"insert  into tunggakan (tgl_tunggak,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,blok)VALUES('$tanggal','$norek','$unit','".$ciff."','$code_kel','$angsuran_margin_tunggak','02','0','$userid','$ket','$cao','') ");
                            db_query($con,"insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif)VALUES('','$tanggal','$unit','$norek','0','$angsuran_pokok','03','$ket2','$userid','REALISASI POKOK','$cif')") ;
                            db_query($con,"insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif)VALUES('','$tanggal','$unit','$norek','0','$angsuran_margin','03','$ket2','$userid','REALISASI BAGHAS','$cif')");
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
                             echo "<script>alert('Saldo $ama tidak mempunyai tunggakan');</script>";
                            
                             
                            }
                    
			 
                    default:
                    $jurnal_umum=db_fetch_array(db_query($con,"SELECT max(nomor_jurnal) FROM jurnal_umum where unit ='$unit' ORDER BY tanggal_selesai DESC"));
                    $nomor_jurnal=$jurnal_umum[0]+1;                 
                     

                  }



     }else{

        $jumlah_angsur = floor(compat_num($simpanan / $angsuran));
        var_dump($jumlah_angsur);
       
      
        $dibayar = $jumlah_angsur * $angsuran;

        $dibayar_pokok = $jumlah_angsur * $angsuran_pokok;
        $dibayar_margin = $jumlah_angsur * $angsuran_margin;
        $ijaroh_tunggak = $jumlah_angsur * $ijaroh_tunggak;
$dibayar_pokok_tunggak = $jumlah_angsur * $pokok_tunggak;
$dibayar_margin_tunggak = $jumlah_angsur * $ijaroh_tunggak;

        if($jumlah_angsur >=1){

            switch ($status_app) {
                    case "REST":
                        $jurnal_umum=db_fetch_array(db_query($con,"SELECT max(nomor_jurnal) FROM jurnal_umum where unit ='$unit' ORDER BY tanggal_selesai DESC"));
                        $nomor_jurnal=$jurnal_umum[0]+1;
 
                        if($ada_tunggak>0){
                            db_query($con,"insert  into tunggakan (tgl_tunggak,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,blok)VALUES('$tanggal','$norek','$unit','".$ciff."','$code_kel','$dibayar_pokok_tunggak','01','0','$userid','$ket','$cao','') ") ;
                            db_query ($con,"insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif)VALUES('','$tanggal','$unit','$norek','0','$dibayar_pokok_tunggak','03','$ket2','$userid','REALISASI POKOK','".$ciff."')");
                            db_query ($con,"Update  loan set next_schedule ='$next_schedule',os =os-'$dibayar',saldo_margin=saldo_margin-'$dibayar_margin',run_tenor='$pb',ke='$ke_tenor'+1 ,last_payment='$tanggal' where cif  ='".$ciff."'");
                            db_query ($con,"insert  into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,tgl_input,kode_transaksi)VALUES('$tanggal','$norek','$unit','".$ciff."','$code_kel','$dibayar','03','0','$userid','$ket2','$cao','$tgl_input','$kode_tran') ");
                            db_query ($con,"Update  pinjaman_detail set jumlah_bayar ='$dibayar',tgl_bayar='$tanggal' where id_pinjam  ='$norek' and cicilan='$pb'");
                            db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                            values('$unit','$kode_tran','2101000','$tanggal','Bukti SYSTEM','$ket2','$dibayar','$userid')");
                            db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                            values('$unit','$kode_tran','1411000','$tanggal','Bukti SYSTEM','$ket2','$dibayar','$userid')");
                    db_query($con,"update booking_debet set status_booking ='Done' where cif ='$ciff'");
                        }else{
                    
                         echo "<script>alert('Saldo $ama tidak mempunyai tunggakan');</script>";
 
                        }
                 
                      break;
                    case "REST_POKOK_MARGIN": 
                    $jurnal_umum=db_fetch_array(db_query($con,"SELECT max(nomor_jurnal) FROM jurnal_umum where unit ='$unit' ORDER BY tanggal_selesai DESC"));
                    $nomor_jurnal=$jurnal_umum[0]+1;                 
                     
                    if($ada_tunggak > 0){
                    db_query($con,"insert  into tunggakan (tgl_tunggak,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,blok)VALUES('$tanggal','$norek','$unit','".$ciff."','$code_kel','$dibayar_pokok_tunggak','01','0','$userid','$ket','$cao','') ") ;
                    db_query($con,"insert  into tunggakan (tgl_tunggak,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,blok)VALUES('$tanggal','$norek','$unit','".$ciff."','$code_kel','$ijaroh_tunggak','02','0','$userid','$ket','$cao','') ");
                    db_query($con,"insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif)VALUES('','$tanggal','$unit','$norek','0','$dibayar_pokok_tunggak','03','$ket','$userid','REALISASI POKOK','".$ciff."')") ;
                    db_query($con,"insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif)VALUES('','$tanggal','$unit','$norek','0','$dibayar_margin_tunggak','03','$ket','$userid','REALISASI BAGHAS','".$ciff."')");
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
 
                    
                           echo "<script>alert('Saldo $ama tidak mempunyai tunggakan');</script>";
 
                        }
 
                      
                      break;
                    case "MURAB":
                        $jurnal_umum=db_fetch_array(db_query($con,"SELECT max(nomor_jurnal) FROM jurnal_umum where unit ='$unit' ORDER BY tanggal_selesai DESC"));
                        $nomor_jurnal=$jurnal_umum[0]+1;                 
                         
                        if($ada_tunggak>0){
                            db_query($con,"insert  into tunggakan (tgl_tunggak,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,blok)VALUES('$tanggal','$norek','$unit','".$ciff."','$code_kel','$dibayar_pokok_tunggak','01','0','$userid','$ket','$cao','') ") ;
                            db_query($con,"insert  into tunggakan (tgl_tunggak,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,blok)VALUES('$tanggal','$norek','$unit','".$ciff."','$code_kel','$ijaroh_tunggak','02','0','$userid','$ket','$cao','') ");
                            db_query($con,"insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif)VALUES('','$tanggal','$unit','$norek','0','$angsuran_pokok','03','$ket2','$userid','REALISASI POKOK','$cif')") ;
                            db_query($con,"insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif)VALUES('','$tanggal','$unit','$norek','0','$dibayar_margin','03','$ket2','$userid','REALISASI BAGHAS','$cif')");
                            db_query($con,"Update  loan set next_schedule ='$next_schedule',os =os-'$dibayar',saldo_margin=saldo_margin-'$ijaroh_tunggak',run_tenor='$pb',ke='$ke_tenor'+1,last_payment='$tanggal' where cif  ='".$ciff."'");
                                             
                     
                            db_query($con,"insert  into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,blok,tgl_input,kode_transaksi)VALUES('$tanggal','$norek','$unit','".$ciff."','$code_kel','$dibayar','03','0','$userid','$ket2','','$cao','2','$tgl_input','$kode_tran') ");
                             
                                             
                            db_query($con,"insert into jurnal_umum(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values('$nomor_jurnal','$kode_tran','$tanggal','$unit')");
                             
                            db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                                values('$unit','$kode_tran','1413000','$tanggal','Bukti SYSTEM','$ket2','$dibayar','$userid')");                         
                            //Realisasi Piutang Margin     
                            db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                                values('$unit','$kode_tran','1423000','$tanggal','Bukti SYSTEM','$ket2','$ijaroh_tunggak','$userid')");
                            //Realiasasi Murabahah Debit pada Piutang Murabahah total jumlah Pokok dan margin 
                            db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                                values('$unit','$kode_tran','41002','$tanggal','Bukti SYSTEM','$ket2','$ijaroh_tunggak','$userid')");
                            db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                                values('$unit','$kode_tran','2101000','$tanggal','Bukti SYSTEM','$ket2','$dibayar','$userid')");
                      
 db_query($con,"update booking_debet set status_booking ='Done' where cif ='$ciff'");
                             
                            }else{
                             echo "<script>alert('Saldo $ama tidak mempunyai tunggakan');</script>";
                            
                             
                            }
                      
                      break;
                      case "MUSYAROKAH":
						$jurnal_umum=db_fetch_array(db_query($con,"SELECT max(nomor_jurnal) FROM jurnal_umum where unit ='$unit' ORDER BY tanggal_selesai DESC"));
						$nomor_jurnal=$jurnal_umum[0]+1;                 
						
						if($ada_tunggak>0){
							db_query($con,"insert  into tunggakan (tgl_tunggak,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,blok)VALUES('$tanggal','$norek','$unit','".$checbox[$i]."','$code_kel','$angsuran_pokok_tunggak','01','0','$userid','BH','$ket','$cao','') ") ;
							db_query($con,"insert  into tunggakan (tgl_tunggak,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,blok)VALUES('$tanggal','$norek','$unit','".$checbox[$i]."','$code_kel','$ijaroh','02','0','$userid','BH','$ket','$cao','') ");
							db_query($con,"insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif)VALUES('','$tanggal','$unit','$norek','0','$angsuran_pokok','03','$ket2','$userid','REALISASI POKOK','$cif')") ;
							db_query($con,"insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif)VALUES('','$tanggal','$unit','$norek','0','$ijaroh','03','$ket2','$userid','REALISASI BAGHAS','$cif')");
							db_query($con,"Update  loan set next_schedule ='$next_schedule',os =os-'$total_tunggak',saldo_margin=saldo_margin-'$itotal_angsuran_marginjaroh',run_tenor='$pb',ke='$ke_tenor'+1,last_payment='$tanggal' where cif  ='".$checbox[$i]."'");
											 
					
							db_query($con,"insert  into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,blok,tgl_input,kode_transaksi)VALUES('$tanggal','$norek','$unit','".$checbox[$i]."','$code_kel','$angsuran','03','0','$userid','$ket','','$cao','2','$tgl_input','$kode_tran') ");
							
											 
							db_query($con,"insert into jurnal_umum(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values('$nomor_jurnal','$kode_tran','$tanggal','$unit')");
							
													//Realisasi Piutang Margin     
							db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
								values('$unit','$kode_tran','1472000','$tanggal','Bukti SYSTEM','$ket2','$total_angsuran_margin','$userid')");
							//Realiasasi Murabahah Debit pada Piutang Murabahah total jumlah Pokok dan margin 
							db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
								values('$unit','$kode_tran','2101000','$tanggal','Bukti SYSTEM','$ket2','$total_tunggak','$userid')");
							db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
								values('$unit','$kode_tran','41062','$tanggal','Bukti SYSTEM','$ket2','$total_angsuran_margin','$userid')");
							
							db_query ($con,"Update  tagihan_cs set status_realisasi ='BAYAR'  where cif  ='".$checbox[$i]."' and tgl_tagih='$tanggal'");

							 
							}else{
							 
                                echo "<script>alert('Saldo $ama tidak mempunyai tunggakan');</script>";
							 
							 
							}
					  
					  break;
                      case "COVID":
                        $jurnal_umum=db_fetch_array(db_query($con,"SELECT max(nomor_jurnal) FROM jurnal_umum where unit ='$unit' ORDER BY tanggal_selesai DESC"));
                        $nomor_jurnal=$jurnal_umum[0]+1;                 
                         
                        if($ada_tunggak>0){
                            db_query($con,"insert  into tunggakan (tgl_tunggak,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,blok)VALUES('$tanggal','$norek','$unit','".$ciff."','$code_kel','$dibayar_pokok_tunggak','01','0','$userid','$ket','$cao','') ") ;
                            db_query($con,"insert  into tunggakan (tgl_tunggak,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,blok)VALUES('$tanggal','$norek','$unit','".$ciff."','$code_kel','$dibayar_margin_tunggak','02','0','$userid','$ket','$cao','') ");
                            db_query($con,"insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif)VALUES('','$tanggal','$unit','$norek','0','$angsuran_pokok','03','$ket2','$userid','REALISASI POKOK','$cif')") ;
                            db_query($con,"insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif)VALUES('','$tanggal','$unit','$norek','0','$dibayar_margin','03','$ket2','$userid','REALISASI BAGHAS','$cif')");
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
                             echo "<script>alert('Saldo $ama tidak mempunyai tunggakan');</script>";
                            
                             
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
