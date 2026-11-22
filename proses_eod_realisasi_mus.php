<?php require __DIR__ . '/inc/hanya_cli.php'; require_once __DIR__ . '/koneksi_db.php';
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
// 2026-11-22 (perintah user, P2-10): query proses per anggota dihitung gagalnya. Bila ada yang gagal, proses anggota itu
// dibatalkan (ROLLBACK TO SAVEPOINT; loan_detail MyISAM dihapus manual) dan anggota dicatat ke gagal_loan supaya tampil di
// menu Realisasi -> Monitoring Gagal EOD. Dulu tetap di-commit walau ada query gagal (data setengah).
function eod_q($con, $sql)
{
    $r = db_query($con, $sql);
    if ($r === false) {
        $GLOBALS['__eod_gagal'] = (isset($GLOBALS['__eod_gagal']) ? $GLOBALS['__eod_gagal'] : 0) + 1;
        $GLOBALS['__eod_err'] = mysqli_error($con) . ' | ' . substr(preg_replace('/\s+/', ' ', $sql), 0, 150);
    }
    return $r;
}



//$tgl_system ="2024-05-27"; 
 $cek= db_fetch_array(db_query($con,"select param_tgl from param_tgl WHERE nama_param_tgl ='EOD_MOB'"));
$tgl_system = $cek['param_tgl']; 
//$tgl_system ="2026-09-15";
		
           



            $kode_tr=5;
            $kode_tr=acakangkahuruf($kode_tr);      
             $data = db_query($con,"select DATE_ADD(tgl_wakalah,INTERVAL 7 DAY) as tgl_murab1,DATE_ADD(tgl_akad,INTERVAL 26 WEEK) as matur,tgl_murab,tgl_akad,calon_loan.buss_date,calon_loan.code_kel,calon_loan.no_anggota,calon_loan.Cust_Short_name,maturity_date,
            calon_loan.Deal_type,suffix,bagi_hasil,Tenor,Plafond,os,angsuran,pokok,ijaroh,bulat,ke,usaha,calon_loan.unit,tgl_wakalah,hari,calon_loan.cao,calon_loan.USERID,calon_loan.status,calon_loan.cif,nisbah,omzet
             from calon_loan 
             inner join anggota on anggota.cif = calon_loan.cif
left join nisbah on nisbah.cif = calon_loan.cif
             WHERE tgl_akad ='$tgl_system' and status_app ='MUSYAROKAH' ");
             while($row = db_fetch_array($data)){
                $no_anggota=$row['no_anggota'];
                $tgl_wakalah= $row['tgl_wakalah'];
                $cif=$row['cif'];
                $Plafond=$row['Plafond'];
                $bagi_hasil=$row['bagi_hasil'];
                $piutang=$Plafond + $bagi_hasil;
                $murab=$row['tgl_murab'];
 $tgl_akad=$row['tgl_akad'];
                $buss_date=$row['buss_date'];
                $code_kel=$row['code_kel'];
                $Cust_Short_name=mysqli_real_escape_string($con,$row['Cust_Short_name']);
                $Deal_type=$row['Deal_type'];
                $suffix=$row['suffix'];
                $Tenor=$row['Tenor'];
                $os=$row['os'];
 $nisbah=$row['nisbah'];
 $omzet=$row['omzet'];
                $angsuran=$row['angsuran'];
                $pokok=$row['pokok'];
                $ijaroh=$row['ijaroh'];
                $bulat=$row['bulat'];
                $maturity_date=$row['maturity_date'];
               
    
                // §2 (2026-11-17, perintah user): dulu `if ($suffix=1)` (penugasan, bukan perbandingan) sehingga suffic
                // loan_detail selalu 1. Kini = pembiayaan ke- (calon_loan.suffix, sama dengan 2 digit akhir no_anggota); kosong -> 1.
                $ke = ((int) $suffix > 0) ? (int) $suffix : 1;
                //var_dump($cif);
                
    
                $usaha=$row['usaha'];
                $unit=$row['unit'];
                $hari=$row['hari'];
                $cao=$row['cao'];
                $userid=$row['USERID'];
                $status='ANGGOTA';
                $norek="$no_anggota";
                $ket="Realisasi MUSYARAKAH $Cust_Short_name $cif"; // 2026-11-22: CIF ditambahkan
             $query = db_query ($con,"SELECT os FROM loan WHERE cif= '$cif' ");
             $check = db_fetch_array($query);
            $os_lama=$check['os'];
            if ($os_lama > 0) {
    
            db_query($con,"REPLACE INTO gagal_loan select * from calon_loan WHERE cif ='$cif'") ;
                    }else{
                // 2026-11-22 (P2-10): titik batal per anggota
                $GLOBALS['__eod_gagal'] = 0; $GLOBALS['__eod_err'] = ''; $ld_ok = false;
                mysqli_query($con, "SAVEPOINT sp_anggota");
    
    
                $kodetr=5;
                $kodetr=acakangkahuruf($kodetr);
                $kode_trans="BU/".$unit .$kodetr;
                
                $kode_transaksi="BU/".$kode_tr;
                //pencatatan du jurnal realasisi Piutang Murabahaha total jumlah Pokok dan margin
                eod_q($con,"insert into jurnal_umum(kode_transaksi,tanggal_selesai) values('$kode_trans','$tgl_system')");
               
                //Realiasasi Murabahah Debit pada Aset Murabahaha total Plafond
                eod_q($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                values('$unit','$kode_trans','1472000','$tgl_system','Bukti SYSTEM','$ket','$Plafond','$userid')");
               
                //Realiasasi Murabahah Debit pada Piutang Murabahaha total jumlah Pokok dan margin
                eod_q($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                        values('$unit','$kode_trans','1120000','$tgl_system','Bukti SYSTEM','$ket','$Plafond','$userid')");
               
               // §1 (2026-11-16, perintah user): dulu ke tabel salah ketik rekap_nsibah (tidak ada) sehingga nisbah tidak pernah
               // tercatat; kini rekap_nisbah, sama dengan realisasi manual (realisasi_musyarakah.php).
               eod_q($con,"insert into rekap_nisbah(cif,nisbah,omzet,tgl_realisasi) values('$cif','$nisbah','$omzet','$tgl_system')");
         
        eod_q($con,"delete from loan  WHERE cif ='$cif'");
        eod_q($con,"delete from tunggakan  WHERE cif ='$cif'");
        eod_q($con,"delete from omzet  WHERE cif ='$cif'");	
        eod_q($con,"insert  INTO loan select * from calon_loan WHERE cif ='$cif'") ;
        $ld_ok = eod_q($con,"INSERT INTO `loan_detail` (code_kel,buss_date,no_anggota,cif,suffic,Cust_Short_name,Deal_type,bagi_hasil,Tenor,Plafond,unit,Contract_Date,hari_tagih,angsuran,storan,cao,USERID)
                            VALUES ('$code_kel','$buss_date','$no_anggota','$cif','$ke','$Cust_Short_name','$Deal_type','$bagi_hasil','$Tenor','$Plafond','$unit','$murab','$hari','$angsuran','$bulat','$cao','$userid')") ;
               
    eod_q($con,"insert into simpanan(BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,KREDIT,TYPE,USERID,KET,REFF,CAO,kode_transaksi)
                values('$tgl_system','$norek','$unit','$cif','$code_kel','$Plafond','01','$userid','$ket','','$cao','$kode_trans')");           
    eod_q($con,"insert into simpanan(BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,kode_transaksi)
                values('$tgl_system','$norek','$unit','$cif','$code_kel','$Plafond','01','0','$userid','$ket','','$cao','$kode_trans')");
    $update=eod_q($con,"UPDATE simpanan SET blok='', TYPE='01' where cif='$cif' and type ='02' ");
    $update=eod_q($con,"UPDATE loan SET status='CETAK',gol ='1', deal_produk ='3' where cif='$cif'");
    
               
               
                eod_q($con,"INSERT INTO `rek_loan` (tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif,AO)
                        VALUES ('$buss_date','$unit','$no_anggota','$os','0','L001','$ket','$userid','REALISASI MURABAHAH','$cif','$cao')") ;
                eod_q($con,"delete from `calon_loan`  WHERE cif ='$cif'");
               eod_q($con,"delete from pinjaman_detail_mus where cif='$cif'"); 
               eod_q($con,"delete from pinjaman_detail where cif='$cif'"); 
//mysqli_query($con,"delete from pinjaman_detail_temp_mus where cif='$cif'"); 
                //mysqli_query ($con,"insert  into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF)VALUES('$buss_date','$no_anggota','$unit','$cif','$code_kel','0','01','0','$userid','') ") ;
                   
                    $awal_hari = $tgl_akad;
                    $akhir_hari = $maturity_date;
                    $x1=0;
                    while (strtotime($awal_hari) < strtotime($akhir_hari)){
                    $x1 ++;
                    $reff="$no_anggota$x1";
                    $awal_hari= date ("Y-m-d", strtotime("+7 DAY", strtotime($awal_hari)));
                    $update= eod_q($con,"INSERT INTO pinjaman_detail_temp_mus (id,id_pinjam,cicilan,angsuran_pokok,bunga,tgl_jatuh_tempo,tgl_bayar,jumlah_bayar,ket,cif,unit,ao,code_kel)
                    values('$reff','$no_anggota','$x1','$pokok','$ijaroh','$awal_hari','$awal_hari','0','','$cif','$unit','$cao','$code_kel')") ;
                               
                    }
eod_q($con,"delete from pinjaman_detail_temp_mus where cicilan > '$Tenor' AND cif='$cif'");  
    
                           
    
            
       
               
                // 2026-11-22 (P2-10): ada query gagal -> batalkan proses anggota ini saja, catat ke gagal_loan & log cron
                if (!empty($GLOBALS['__eod_gagal'])) {
                    mysqli_query($con, "ROLLBACK TO SAVEPOINT sp_anggota");
                    if ($ld_ok) db_query($con, "delete from loan_detail where cif='$cif' and Contract_Date='$murab' and suffic='$ke' limit 1");
                    db_query($con, "REPLACE INTO gagal_loan select * from calon_loan WHERE cif ='$cif'");
                    echo "GAGAL realisasi $cif $Cust_Short_name: " . $GLOBALS['__eod_err'] . " (dibatalkan, dicatat di gagal_loan)\n";
                }
                mysqli_query($con, "RELEASE SAVEPOINT sp_anggota");
        }
    
       

       
                } 


                if (!mysqli_commit($con)) {
                    echo "Transaksi Gagal commit Silahkan ulangi lagi";
                    exit();
                  }

                echo '<script>alert("Sukses"); </script>';
           
    ?>
    
