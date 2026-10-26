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

         
$select = db_query ($con,"Select lima.cif as cif,calon_loan.angsuran,calon_loan.cao,Cust_Short_name,calon_loan.os as os,
no_anggota as norek ,code_kel,status_app,calon_loan.pokok,run_tenor,calon_loan.unit, calon_loan.bulat as bulat,calon_loan.ijaroh as ijaroh,nominal
from lima left join calon_loan on calon_loan.cif = lima.cif where nominal > 0  and tgl_tagih ='$tanggal'");

//$select = mysqli_query ($con,"Select lima.cif as cif,loan.angsuran,loan.cao,Cust_Short_name,loan.os as os,
//no_anggota as norek ,code_kel,status_app,loan.pokok,run_tenor,loan.unit, loan.bulat as bulat,loan.ijaroh as ijaroh,nominal
//from lima left join loan on loan.cif = lima.cif where nominal > 0");
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
        $nama=mysqli_real_escape_string($con,($data['Cust_Short_name']));
        $kodetr=8;
        $kodetr=8;
        $kodetr=acakangkahuruf($kodetr);
        $kode_trans="BU/".$unit.$kodetr;				
        $ket="Realisasi Simpanan Wajib dan Pokok Unit $unit";		
        
        
        $cek_wajib = db_query($con,"select SUM(KREDIT-DEBIT) as wajib from simpanan_wajib where cif ='" . $ciff . "'");
        $cwajib = db_fetch_array($cek_wajib);
        $wajib = $cwajib['wajib'];
        $cek_pokok = db_query($con,"select SUM(KREDIT-DEBIT) as pokok from simpanan_pokok where cif ='" . $ciff . "'");
        $cpokok = db_fetch_array($cek_pokok);
        $pokok = $cpokok['pokok'];
       
                
        if ($pokok == 0 && $wajib >= 0)  {
            $in_pokok=50000;
           $ket="Setoran simpanan Pokok an $nama";
                $input ="Insert into simpanan_pokok (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,blok,kode_transaksi)VALUES('$tanggal','$norek','$unit','" . $ciff . "','$code_kel',0,'04','$in_pokok','$userid','Simpanan Pokok','','$cao','4','$kode_trans')";
                $inputtrnsk = db_query($con,$input);

                @db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                values('$unit','$kode_trans','1120000','$tanggal','Bukti SYSTEM','$ket','$in_pokok','$userid')");
                @db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                values('$unit','$kode_trans','3102000','$tanggal','Bukti SYSTEM','$ket','$in_pokok','$userid')");
          
        //HITUNG SIA SIMPANAN JIKA SISA > 0 MAKA SIMPAN DI WADIAH
            $sisa = $nominal - $in_pokok;
            if($sisa>0){
               $ket="Setoran simpanan 5% an $nama";
                    $input ="Insert into simpanan_wajib (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,blok,kode_transaksi)VALUES('$tanggal','$norek','$unit','" . $ciff . "','$code_kel',0,'05','$sisa','$userid','PB Simpanan Wajib','','$cao','1','$kode_trans')";
                    $inputtrnsk = db_query($con,$input);
                    db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                    values('$unit','$kode_trans','1120000','$tanggal','Bukti SYSTEM','$ket','$sisa','$userid')");

                    db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                    values('$unit','$kode_trans','3202000','$tanggal','Bukti SYSTEM','$ket','$sisa','$userid')");
              
            }
          
        }
      
        //JIKA PUNYA POKOK DA BELUM MEMPUNYA WAJIB
        elseif($pokok == 50000 && $wajib >= 0){
            $ket="Setoran simpanan 5% an $nama";

                $input ="Insert into simpanan_wajib (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,blok,kode_transaksi)VALUES('$tanggal','$norek','$unit','" . $ciff . "','$code_kel',0,'05','$nominal','$userid','PB Simpanan Wajib','','$cao','1','$kode_trans')";
                    $inputtrnsk = db_query($con,$input);


                @db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                values('$unit','$kode_trans','1120000','$tanggal','Bukti SYSTEM','$ket','$nominal','$userid')");
                @db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                values('$unit','$kode_trans','3202000','$tanggal','Bukti SYSTEM','$ket','$nominal','$userid')");
          

          
        }
       
       
        //JIKA BELUMPUNYA WAJIB DAN POKOK SPLIT SIMPANANT KE POKOK DAN WAJIB
        elseif($pokok == 0 && $wajib == 0){
            $in_pokok=50000;
           $ket="Setoran simpanan 5% an $nama";
               
                $input ="Insert into simpanan_pokok (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,blok,kode_transaksi)VALUES('$tanggal','$norek','$unit','" . $ciff . "','$code_kel',0,'04','$in_pokok','$userid','Simpanan Pokok','','$cao','4','$kode_trans')";
                $inputtrnsk = db_query($con,$input);
                @db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                values('$unit','$kode_trans','1120000','$tanggal','Bukti SYSTEM','$ket','$in_pokok','$userid')");
                @db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                values('$unit','$kode_trans','3102000','$tanggal','Bukti SYSTEM','$ket','$in_pokok','$userid')");
          

          
            //HITUNG JIKA SALDO CUKU UNTUK POKOK KRDIT KE POKOK
                $sisa_pokok = $nominal - $in_pokok;
                if ($sisa_pokok>0){
                    $ket="Setoran simpanan 5% an $nama";
               

                    $input ="Insert into simpanan_wajib (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,blok,kode_transaksi)VALUES('$tanggal','$norek','$unit','" . $ciff . "','$code_kel',0,'05','$sisa_pokok','$userid','PB Simpanan Wajib','','$cao','1','$kode_trans')";
                    $inputtrnsk = mysql_query($input);
                @db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                values('$unit','$kode_trans','1120000','$tgl_system','Bukti SYSTEM','$ket','$wajib_in','$userid')");
                @db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                values('$unit','$kode_trans','3202000','$tgl_system','Bukti SYSTEM','$ket','$wajib_in','$userid')");
                } 
               
        }else {
            $ket1="Wadiah $nama";
          
               $input ="Insert into simpanan_wajib (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,blok,kode_transaksi)VALUES('$tanggal','$norek','$unit','" . $ciff . "','$code_kel',0,'05','$sisa_pokok','$userid','PB Simpanan Wajib','','$cao','1','$kode_trans')";
                    $inputtrnsk = db_query($con,$input);
                    db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                values('$unit','$kode_trans','1120000','$tgl_system','Bukti SYSTEM','$ket','$nominal','$userid')");

                db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                values('$unit','$kode_trans','3202000','$tgl_system','Bukti SYSTEM','$ket','$nominal','$userid')");
       
          
        } 
        
        



    
    
    }

 
    if (!mysqli_commit($con)) {
        echo "Transaksi Gagal commit Silahkan ulangi lagi";
        exit();
      }
echo "<script>alert('Setoran Lebaran sukses di simpan');</script>";

     
    
 
?>
