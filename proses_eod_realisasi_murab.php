<?php require_once __DIR__ . '/koneksi_db.php';
error_reporting(E_ALL);
date_default_timezone_set('Asia/Jakarta');
$tanggal=date("y-m-d");

$con = db_ni();
if (mysqli_connect_errno()) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
    exit;
  }
 
// Turn autocommit off
mysqli_autocommit($con,FALSE);
 

include "function.php";



$tgl_system ="2024-05-27"; 
 $cek= db_fetch_array(db_query($con,"select param_tgl from param_tgl WHERE nama_param_tgl ='EOD_MOB'"));
$tgl_system = $cek['param_tgl']; 
		
           



            $kode_tr=5;
            $kode_tr=acakangkahuruf($kode_tr);      
             $data = db_query($con,"select DATE_ADD(tgl_wakalah,INTERVAL 7 DAY) as tgl_murab1,DATE_ADD(tgl_wakalah,INTERVAL 26 WEEK) as matur,tgl_murab,calon_loan.buss_date,calon_loan.code_kel,calon_loan.no_anggota,calon_loan.Cust_Short_name,maturity_date,
            calon_loan.Deal_type,suffix,bagi_hasil,Tenor,Plafond,os,angsuran,pokok,ijaroh,bulat,ke,usaha,calon_loan.unit,tgl_wakalah,hari,calon_loan.cao,calon_loan.USERID,calon_loan.status,calon_loan.cif
             from calon_loan 
             inner join anggota on anggota.cif = calon_loan.cif
             WHERE tgl_akad ='$tgl_system' and status_app ='MURAB' ");
             while($row = db_fetch_array($data)){
                $no_anggota=$row['no_anggota'];
                $tgl_wakalah= $row['tgl_wakalah'];
                $cif=$row['cif'];
                $Plafond=$row['Plafond'];
                $bagi_hasil=$row['bagi_hasil'];
                $piutang=$Plafond + $bagi_hasil;
                $murab=$row['tgl_murab'];
                $buss_date=$row['buss_date'];
                $code_kel=$row['code_kel'];
                $Cust_Short_name=mysqli_real_escape_string($con,$row['Cust_Short_name']);
                $Deal_type=$row['Deal_type'];
                $suffix=$row['suffix'];
                $Tenor=$row['Tenor'];
                $os=$row['os'];
                $angsuran=$row['angsuran'];
                $pokok=$row['pokok'];
                $ijaroh=$row['ijaroh'];
                $bulat=$row['bulat'];
                $maturity_date=$row['maturity_date'];
               
    
                if ($suffix=1){
                    $ke=1;
                }else{
                    $ke=((int)$suffix)+1;
                }
                //var_dump($cif);
                
    
                $usaha=$row['usaha'];
                $unit=$row['unit'];
                $hari=$row['hari'];
                $cao=$row['cao'];
                $userid=$row['USERID'];
                $status='ANGGOTA';
                $norek="$no_anggota";
                $ket="Realisasi Murabahah $Cust_Short_name";
             $query = db_query ($con,"SELECT os FROM loan WHERE cif= '$cif' ");
             $check = db_fetch_array($query);
            $os_lama=$check['os'];
            if ($os_lama > 0) {
    
            db_query($con,"insert INTO gagal_loan select * from calon_loan WHERE cif ='$cif'") ;
                    }else{
    
    
                $kodetr=5;
                $kodetr=acakangkahuruf($kodetr);
                $kode_trans="BU/".$unit .$kodetr;
                
                $kode_transaksi="BU/".$kode_tr;
                //pencatatan du jurnal realasisi Piutang Murabahaha total jumlah Pokok dan margin
                db_query($con,"insert into jurnal_umum(kode_transaksi,tanggal_selesai) values('$kode_trans','$tgl_system')");
               
                //Realiasasi Murabahah Debit pada Aset Murabahaha total Plafond
                db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                values('$unit','$kode_trans','1481000','$tgl_system','Bukti SYSTEM','$ket','$Plafond','$userid')");
               
                //Realiasasi Murabahah Debit pada Piutang Murabahaha total jumlah Pokok dan margin
                db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                        values('$unit','$kode_trans','1431000','$tgl_system','Bukti SYSTEM','$ket','$Plafond','$userid')");
               
                db_query($con,"insert into jurnal_umum(kode_transaksi,tanggal_selesai,unit) values('$kode_trans','$tgl_system','$unit')");
                //Realisasi Piutang Margin   
                db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                    values('$unit','$kode_trans','1413000','$tgl_system','Bukti SYSTEM','$ket','$piutang','$userid')");
    
                db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                values('$unit','$kode_trans','1481000','$tgl_system','Bukti SYSTEM','$ket','$Plafond','$userid')");
               
                db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                        values('$unit','$kode_trans','1423000','$tgl_system','Bukti SYSTEM','$ket','$bagi_hasil','$userid')");
         
                        db_query($con,"delete from loan  WHERE cif ='$cif' and unit ='$unit'");
        db_query($con,"delete from tunggakan  WHERE cif ='$cif' and unit ='$unit'");	
        db_query($con,"insert  INTO loan select * from calon_loan WHERE cif ='$cif'") ;
        db_query($con,"INSERT INTO `loan_detail` (code_kel,buss_date,no_anggota,cif,suffic,Cust_Short_name,Deal_type,bagi_hasil,Tenor,Plafond,unit,Contract_Date,hari_tagih,angsuran,storan,cao,USERID)
                            VALUES ('$code_kel','$buss_date','$no_anggota','$cif','$ke','$Cust_Short_name','$Deal_type','$bagi_hasil','$Tenor','$Plafond','$unit','$murab','$hari','$angsuran','$bulat','$cao','$userid')") ;
               
    db_query($con,"insert into simpanan(BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,KREDIT,TYPE,USERID,KET,REFF,CAO,kode_transaksi)
                values('$murab','$norek','$unit','$cif','$code_kel','$Plafond','01','$userid','$ket','','$cao','$kode_trans')");           
    db_query($con,"insert into simpanan(BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,kode_transaksi)
                values('$murab','$norek','$unit','$cif','$code_kel','$Plafond','01','0','$userid','$ket','','$cao','$kode_trans')");
    $update=db_query($con,"UPDATE simpanan SET blok='', TYPE='01' where cif='$cif' and type ='02' ");
    $update=db_query($con,"UPDATE loan SET status='CETAK',gol ='1', deal_produk ='2' where cif='$cif'");
    
               
               
                db_query($con,"INSERT INTO `rek_loan` (tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif,AO)
                        VALUES ('$buss_date','$unit','$no_anggota','$os','0','L001','$ket','$userid','REALISASI MURABAHAH','$cif','$cao')") ;
                db_query($con,"delete from `calon_loan`  WHERE cif ='$cif'");
               db_query($con,"delete from pinjaman_detail where cif='$cif'");
//mysqli_query($con,"delete from pinjaman_detail_temp where cif='$cif'");
 
                //mysqli_query ($con,"insert  into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF)VALUES('$buss_date','$no_anggota','$unit','$cif','$code_kel','0','01','0','$userid','') ") ;
                   
                    $newdatee = date ("Y-m-d", strtotime("+10 WEEK" , strtotime($maturity_date))) ;
                    $awal_hari = $murab;
                    $akhir_hari = $newdatee;
                    $x=0;
                    while (strtotime($awal_hari) < strtotime($akhir_hari)) {
                    $x ++;
                    $awal_hari= date ("Y-m-d", strtotime("+7 DAY", strtotime($awal_hari)));
                    $detail= db_query($con,"INSERT INTO pinjaman_detail_c (id_pinjam,cicilan,angsuran_pokok,bunga,tgl_jatuh_tempo,tgl_bayar,jumlah_bayar,ket)
                    values('$no_anggota','$x','$pokok','$ijaroh','$awal_hari','$awal_hari','0','')");
                       
                        }
    
                           
    
            $cek_tanggal= db_query($con,"SELECT COUNT(tgl_libur) as libur from param_libur
            INNER join pinjaman_detail_c on pinjaman_detail_c.tgl_jatuh_tempo = param_libur.tgl_libur where id_pinjam='$no_anggota'");
            while($data_libur = db_fetch_array($cek_tanggal)){
                $libur =$data_libur['libur'];
               
               
            }
           
           
            if ($libur >0){
                db_query($con,"delete from pinjaman_detail_c where id_pinjam='$no_anggota'");   
       
             
           
             $newdate3 = date ("Y-m-d", strtotime("+$libur WEEK" , strtotime ($maturity_date))) ;
             $cek = db_query($con,"SELECT * FROM param_libur where tgl_libur='$newdate3'");
              if(db_num_rows($cek)==1){
           
                $liburr=$libur+1;
                 $newdate2 = date ("Y-m-d", strtotime("+$liburr WEEK" , strtotime ($maturity_date))) ;
                }else {
                $liburr=$libur;
                 $newdate2 = date ("Y-m-d", strtotime("+$liburr WEEK" , strtotime ($maturity_date))) ;
                }
                   
                    $awal_hari = $murab;
                    $new_akhir_hari = $newdate2;
                    $xx=0;
                    while (strtotime($awal_hari) < strtotime($new_akhir_hari)) {
                    $xx ++;
                    $reff="$no_anggota$xx";
                    $awal_hari= date ("Y-m-d", strtotime("+7 DAY", strtotime($awal_hari)));
                    $detail_data= db_query($con,"INSERT INTO pinjaman_detail_temp (id,id_pinjam,cicilan,angsuran_pokok,bunga,tgl_jatuh_tempo,tgl_bayar,jumlah_bayar,ket,cif,unit,ao,code_kel)
                    values('$reff','$no_anggota','$xx','$pokok','$ijaroh','$awal_hari','$awal_hari','0','','$cif','$unit','$cao','$code_kel')") ;
                    }   
                    db_query($con,"delete from pinjaman_detail_temp where tgl_jatuh_tempo in (select tgl_libur from param_libur where tgl_libur=pinjaman_detail_temp.tgl_jatuh_tempo) and id_pinjam='$no_anggota' ");
                    $up=db_query($con,"SET @count = 0;");
                    $update=db_query($con,"UPDATE pinjaman_detail_temp SET cicilan=@count:=@count+1 where id_pinjam='$no_anggota' ORDER BY cicilan");
                    db_query($con,"delete from pinjaman_detail_temp where cicilan > '$Tenor' AND cif='$cif'");   
    
                        }
    
               
                else{
                    db_query($con,"delete from pinjaman_detail_c where id_pinjam='$no_anggota'");   
                    $awal_hari = $murab;
                    $akhir_hari = $maturity_date;
                    $x1=0;
                    while (strtotime($awal_hari) < strtotime($akhir_hari)){
                    $x1 ++;
                    $reff="$no_anggota$x1";
                    $awal_hari= date ("Y-m-d", strtotime("+7 DAY", strtotime($awal_hari)));
                    $update= db_query($con,"INSERT INTO pinjaman_detail_temp (id,id_pinjam,cicilan,angsuran_pokok,bunga,tgl_jatuh_tempo,tgl_bayar,jumlah_bayar,ket,cif,unit,ao,code_kel)
                    values('$reff','$no_anggota','$x1','$pokok','$ijaroh','$awal_hari','$awal_hari','0','','$cif','$unit','$cao','$code_kel')") ;
                               
                    }
                    db_query($con,"delete from pinjaman_detail_temp where tgl_jatuh_tempo in (select tgl_libur from param_libur where tgl_libur=pinjaman_detail_temp.tgl_jatuh_tempo)");
                    $up=db_query($con,"SET @count = 0;");
                    $update=db_query($con,"UPDATE pinjaman_detail_temp SET cicilan=@count:=@count+1 where id_pinjam='$no_anggota' ORDER BY cicilan");
                    db_query($con,"delete from pinjaman_detail_temp where cicilan > '$Tenor' AND cif='$cif'");   
    
                }
       
               
        }
    
       

       
                } 


                if (!mysqli_commit($con)) {
                    echo "Transaksi Gagal commit Silahkan ulangi lagi";
                    exit();
                  }

                echo '<script>alert("Sukses"); </script>';
           
    ?>
    
