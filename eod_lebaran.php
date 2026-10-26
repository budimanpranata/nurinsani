<?php require_once __DIR__ . '/koneksi_db.php'; 
 
$con = db_ni();
if (mysqli_connect_errno()) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
    exit;
  }
 
// Turn autocommit off
mysqli_autocommit($con,FALSE);
 
 
 
include "function.php";
 
$tanggal="2023-04-20";
$tgl_input= "2023-04-20";

         
$select = db_query ($con,"Select tagihan_cs.cif as cif,loan.angsuran,loan.cao,Cust_Short_name,loan.os as os,
no_anggota as norek ,loan.code_kel,status_app,loan.pokok,run_tenor,loan.unit, loan.bulat as bulat,loan.ijaroh as ijaroh,nominal
from tagihan_cs left join loan on loan.cif = tagihan_cs.cif  where loan.os > 0 order by loan.unit ");
while($data = db_fetch_array($select)){
 
        $norek=$data['norek'];
        $unit=$data ['unit'];
        $angsuran=$data ['angsuran'];
        $nominal=$data ['nominal'];
        $angsuran_pokok=$data ['pokok'];
        $ijaroh=$data ['ijaroh'];
        $code_kel=$data['code_kel'];
        $ke = $data['run_tenor'];
        $cao=$data['cao'];
        $ciff=$data['cif'];

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
             
            // if ($ada_tunggak>=$os){ 
            //     mysqli_query ($con,"delete from tunggakan where cif ='".$ciff."' and tgl_tunggak ='$tanggal' ");
            // }
 
           
     
            
     
        }else{  
$test='0';
if($ada_tunggak >0 ){
 
        db_query ($con,"insert  into tunggakan (tgl_tunggak,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,blok)VALUES('$tanggal','$norek','$unit','".$ciff."','$code_kel','0','01','$angsuran_pokok','$userid','$ket','$cao','') ");
        db_query ($con,"insert  into tunggakan (tgl_tunggak,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,blok)VALUES('$tanggal','$norek','$unit','".$ciff."','$code_kel','0','02','$ijaroh','$userid','$ket','$cao','') ");
 
  
 
     }   
     
   
     
 
 
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
                         
                        if($ada_tunggak>0){
                            db_query($con,"insert  into tunggakan (tgl_tunggak,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,blok)VALUES('$tanggal','$norek','$unit','".$ciff."','$code_kel','$angsuran_pokok','01','0','$userid','$ket','$cao','') ") ;
                            db_query($con,"insert  into tunggakan (tgl_tunggak,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,blok)VALUES('$tanggal','$norek','$unit','".$ciff."','$code_kel','$ijaroh','02','0','$userid','$ket','$cao','') ");
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
                            db_query($con,"insert  into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,tgl_input,kode_transaksi)VALUES('$tanggal','$norek','$unit','".$ciff."','$code_kel','$angsuran','03','0','$userid','$ket','$cao','$tgl_input','$kode_tran') ") ;
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
              
                
 
 
 
 
 
 
 
 
 
 
 
                
          
         
         
         
        }
         
         
    }

 
    if (!mysqli_commit($con)) {
        echo "Transaksi Gagal commit Silahkan ulangi lagi";
        exit();
      }
echo "<script>alert('Setoran Kelompok sukses di simpan');</script>";

     
    
 
?>
