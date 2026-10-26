<?php require_once __DIR__ . '/koneksi_db.php';

$con = db_ni();
if (mysqli_connect_errno()) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
    exit;
}
 
// Turn autocommit of
mysqli_autocommit($con,FALSE);
 
 
include "function.php";
$tanggal="2024-05-27";

 $cek= db_fetch_array(db_query($con,"select param_tgl from param_tgl WHERE nama_param_tgl ='EOD_MOB'"));
$tanggal = $cek['param_tgl']; 
         
$select = db_query ($con,"Select penarikan.cif as cif,loan.angsuran,loan.cao,Cust_Short_name,loan.os as os,
no_anggota as norek ,code_kel,status_app,loan.pokok,run_tenor,loan.unit, loan.bulat as bulat,loan.ijaroh as ijaroh,nominal
from penarikan left join loan on loan.cif = penarikan.cif where nominal > 0  and tgl_tagih ='$tanggal'");
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
        $kodetr=acakangkahuruf($kodetr);
        $kode_trans="BU/".$unit.$kodetr; 
        $keterangan="Penarikan simpanan an $nama";
        
        $area = db_query ($con,"select code_area FROM branch where kode_branch ='$unit'");
$c_area = db_fetch_array($area);
        
$code_area=$c_area['code_area'];

$input ="Insert into temp_tarik (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,KREDIT,TYPE,DEBIT,USERID,KET,REFF,CAO,blok,tgl_input,kode_transaksi)VALUES('$tanggal','$norek','$unit','$ciff','$code_kel',0,'01','$nominal','$unit','$keterangan','','$ds','$code_area','$tanggal','$kode_trans')";

        $inputtrnsk = db_query($con,$input);
        if($inputtrnsk){
          
            @db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
        values('$unit','$kode_trans','1120000','$tanggal','Bukti Umum','$keterangan','$nominal','$unit')");
        @db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                values('$unit','$kode_trans','2101000','$tanggal','Bukti Umum','$keterangan','$nominal','$unit')");

        }
              
                
            
            else{
            echo "<script>alert('Maaf,.Porses Input Gagal');</script>";
          
            }
         
    
    
    
    }

 
    if (!mysqli_commit($con)) {
        echo "Transaksi Gagal commit Silahkan ulangi lagi";
        exit();
      }
echo "<script>alert('Setoran penarikan sukses di simpan');</script>";

     
    
 
?>
