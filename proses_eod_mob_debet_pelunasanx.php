<?php require_once __DIR__ . '/koneksi_db.php'; 
 
$con = db_ni();
if (mysqli_connect_errno()) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
    exit;
  }
 
// Turn autocommit off
mysqli_autocommit($con,FALSE);
 
 
 
include "function.php";
 
$tanggal="2024-05-27";

 $cek= db_fetch_array(db_query($con,"select param_tgl from param_tgl WHERE nama_param_tgl ='EOD_MOB'"));
$tanggal = $cek['param_tgl']; 
//$tanggal="2024-01-13";
         
$select = db_query ($con,"Select pelunasan.cif as cif,loan.angsuran,loan.cao,Cust_Short_name,loan.os as os,deal_produk,loan.saldo_margin as saldo_margin,
no_anggota as norek ,code_kel,status_app,loan.pokok,run_tenor,loan.unit, loan.bulat as bulat,loan.ijaroh as ijaroh,nominal
from pelunasan left join loan on loan.cif = pelunasan.cif  where loan.os > 0 and pelunasan.ket ='pelunasan19' and tgl_tagih ='$tanggal' order by loan.unit ");
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
        $deal_produk=$data['deal_produk'];

        $os=$data['os'];
        $saldo_margin=$data['saldo_margin'];
        $status_app=$data['status_app'];
        $run_tenor=$data['run_tenor'];
        $pb=$run_tenor + 1;
        $next_tenor=$run_tenor + 2;
 
        $angsuran=$data['angsuran'];
        $bulat=$data['bulat'];      

 
 
        $cek_saldo= db_fetch_array(db_query($con,"select sum(KREDIT)- sum(DEBIT) as saldo from simpanan WHERE cif ='".$ciff."' and TYPE not in ('04','05','07')"));
        $saldo=$cek_saldo['saldo']; 
       
           
        
        if($deal_produk=='2'){
			$simpanan_minggon_asli=$bulat - $angsuran;
			$simpanan_minggon=$bulat - $angsuran - 2500;
			$simpan_duaribu = '2500';		
			$angsuran=$angsuran + 2500;
			$ijaroh = ($ijaroh + 2500) * 2;
	        $setoran=$nominal - $simpanan_minggon_asli;

		}else{			
		$simpanan_minggon=$bulat - $angsuran;
		$simpan_duaribu = '0';		
		$setoran=$nominal - $simpanan_minggon;
		$angsuran=$angsuran ;
	    $ijaroh = $ijaroh * 2 ;
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
        
         

     
            if($saldo>=$os){        
                    
 
 
                switch ($status_app) {
                    case "REST":
                        $jurnal_umum=db_fetch_array(db_query($con,"SELECT max(nomor_jurnal) FROM jurnal_umum where unit ='$unit' ORDER BY tanggal_selesai DESC"));
                        $nomor_jurnal=$jurnal_umum[0]+1;
 
                        db_query ($con,"insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif)VALUES('','$tanggal','$unit','$norek','0','$os','03','$ket2','$userid','REALISASI POKOK','".$ciff."')");
                        db_query ($con,"Update  loan set os =os-'$os',saldo_margin='0',run_tenor='0',ke='0' ,last_payment='$tanggal' where cif  ='".$ciff."'");
			db_query ($con,"delete from tunggakan where cif  ='".$ciff."'");
                        db_query ($con,"insert  into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,tgl_input,kode_transaksi)VALUES('$tanggal','$norek','$unit','".$ciff."','$code_kel','$angsuran','03','0','$userid','$ket2','$cao','$tgl_input','$kode_tran') ");
                     
                        db_query ($con,"Update  pinjaman_detail set jumlah_bayar ='$angsuran',tgl_bayar='$tanggal' where id_pinjam  ='$norek' and cicilan='$pb'");
                         
                         db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                        values('$unit','$kode_tran','2101000','$tanggal','Bukti SYSTEM','$ket2','$os','$userid')");
                        db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                        values('$unit','$kode_tran','1411000','$tanggal','Bukti SYSTEM','$ket2','$os','$userid')");                     
 
                         
 
                        
                 
                      break;
                    case "REST_POKOK_MARGIN":
 
                    $jurnal_umum=db_fetch_array(db_query($con,"SELECT max(nomor_jurnal) FROM jurnal_umum where unit ='$unit' ORDER BY tanggal_selesai DESC"));
                    $nomor_jurnal=$jurnal_umum[0]+1;                 
                     
 
                        db_query($con,"insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif)VALUES('','$tanggal','$unit','$norek','0','$os','03','$ket2','$userid','REALISASI POKOK','".$ciff."')");
                        db_query($con,"insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif)VALUES('','$tanggal','$unit','$norek','0','$saldo_margin','03','$ket2','$userid','REALISASI BAGHAS','".$ciff."')");
 
                        db_query ($con,"Update  loan set os =os-'$os',saldo_margin='0',run_tenor='0',ke='0' ,last_payment='$tanggal' where cif  ='".$ciff."'");
db_query ($con,"delete from tunggakan where cif  ='".$ciff."'");
                        db_query($con,"insert  into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,tgl_input,kode_transaksi)VALUES('$tanggal','$norek','$unit','".$ciff."','$code_kel','$angsuran','03','0','$userid','$ket2','$cao','$tgl_input','$kode_tran') ");
                         
                        db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                        values('$unit','$kode_tran','2101000','$tanggal','Bukti SYSTEM','$ket2','$os','$userid')");
 
                        db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                        values('$unit','$kode_tran','1411000','$tanggal','Bukti SYSTEM','$ket2','$os','$userid')");
                         
                        db_query($con,"insert into jurnal_umum(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values('$nomor_jurnal','$kode_tran','$tanggal','$unit')");
                        //Realisasi Piutang Margin     
                        db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                                    values('$unit','$kode_tran','1421000','$tanggal','Bukti SYSTEM','$ket2','$saldo_margin','$userid')");
                        //Realiasasi Murabahah Debit pada Piutang Murabahah total jumlah Pokok dan margin 
                        db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                        values('$unit','$kode_tran','41002','$tanggal','Bukti SYSTEM','$ket2','$saldo_margin','$userid')");                          
 
                         
                      
                      break;
                    case "MURAB":
                        $jurnal_umum=db_fetch_array(db_query($con,"SELECT max(nomor_jurnal) FROM jurnal_umum where unit ='$unit' ORDER BY tanggal_selesai DESC"));
                        $nomor_jurnal=$jurnal_umum[0]+1;                 
                         
                                                    
                            db_query($con,"insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif)VALUES('','$tanggal','$unit','$no_anggota','0','$os','03','$ket2','$userid','REALISASI POKOK','".$ciff."')");
                            db_query($con,"insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif)VALUES('','$tanggal','$unit','$no_anggota','0','$saldo_margin','03','$ket2','$userid','REALISASI BAGHAS','".$ciff."')");
                             
                            db_query($con,"Update  loan set os =os-'$os',saldo_margin='0',run_tenor='0',ke='0',last_payment='$tanggal' where cif  ='".$ciff."'");
db_query ($con,"delete from tunggakan where cif  ='".$ciff."'");
                            db_query($con,"insert  into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,tgl_input,kode_transaksi)VALUES('$tanggal','$norek','$unit','".$ciff."','$code_kel','$os','03','0','$userid','$ket','$cao','$tgl_input','$kode_tran') ") ;
                            db_query($con,"insert into jurnal_umum(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values('$nomor_jurnal','$kode_tran','$tanggal','$unit')");
             
                            db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                                values('$unit','$kode_tran','1413000','$tanggal','Bukti SYSTEM','$ket2','$os','$userid')");
                            //Realisasi Piutang Margin     
                            db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                                values('$unit','$kode_tran','1423000','$tanggal','Bukti SYSTEM','$ket2','$saldo_margin','$userid')");
                            //Realiasasi Murabahah Debit pada Piutang Murabahah total jumlah Pokok dan margin 
                            db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                                values('$unit','$kode_tran','41002','$tanggal','Bukti SYSTEM','$ket2','$saldo_margin','$userid')");
                            db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                                values('$unit','$kode_tran','2101000','$tanggal','Bukti SYSTEM','$ket2','$os','$userid')");

                            db_query($con,"insert  into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,tgl_input,kode_transaksi)VALUES('$tanggal','$norek','$unit','".$ciff."','$code_kel','0','06','$ijaroh','$userid','Diskon Margin Pelunasan','$ket','$cao','$tgl_input','$kode_tran') ") ;
                                
                            db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                                values('$unit','$kode_tran','41002','$tanggal','Bukti SYSTEM','$ket2','$ijaroh','$userid')");

                            db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                                values('$unit','$kode_tran','2101000','$tanggal','Bukti SYSTEM','$ket2','$ijaroh','$userid')");

                             
                             
                      
                      
                      break;
                    default:
                    $jurnal_umum=db_fetch_array(db_query($con,"SELECT max(nomor_jurnal) FROM jurnal_umum where unit ='$unit' ORDER BY tanggal_selesai DESC"));
                    $nomor_jurnal=$jurnal_umum[0]+1;                 
                     
                
                         
                        db_query($con,"insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif)VALUES('','$tanggal','$unit','$no_anggota','0','$os','03','$ket2','$userid','REALISASI POKOK','".$ciff."')");
                        db_query($con,"insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif)VALUES('','$tanggal','$unit','$no_anggota','0','$saldo_margin','03','$ket2','$userid','REALISASI BAGHAS','".$ciff."')");
                         
                        db_query($con,"Update  loan set os =os-'$os',saldo_margin='0',run_tenor='0',ke='0',last_payment='$tanggal' where cif  ='".$ciff."'");
db_query ($con,"delete from tunggakan where cif  ='".$ciff."'");
                        db_query($con,"insert  into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,tgl_input,kode_transaksi)VALUES('$tanggal','$norek','$unit','".$ciff."','$code_kel','$os','03','0','$userid','$ket2','$cao','$tgl_input','$kode_tran') ") ;
                        db_query($con,"insert into jurnal_umum(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values('$nomor_jurnal','$kode_tran','$tanggal','$unit')");
         
                        db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                            values('$unit','$kode_tran','1413000','$tanggal','Bukti SYSTEM','$ket2','$os','$userid')");
                        //Realisasi Piutang Margin     
                        db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                            values('$unit','$kode_tran','1423000','$tanggal','Bukti SYSTEM','$ket2','$saldo_margin','$userid')");
                        //Realiasasi Murabahah Debit pada Piutang Murabahah total jumlah Pokok dan margin 
                        db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                            values('$unit','$kode_tran','41002','$tanggal','Bukti SYSTEM','$ket2','$saldo_margin','$userid')");
                        db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                            values('$unit','$kode_tran','2101000','$tanggal','Bukti SYSTEM','$ket2','$os','$userid')");
                    
                         
                         
                      
                  }
               
 
                
            }else{
            echo "Saldo '".$nama."' tidak cukup";
         
            }       
         
        }  

 
    if (!mysqli_commit($con)) {
        echo "Transaksi Gagal commit Silahkan ulangi lagi";
        exit();
      }
echo "<script>alert('Pelunasan Kelompok sukses di simpan');</script>";

     
    
 
?>
