<?php require __DIR__ . '/inc/hanya_cli.php'; require_once __DIR__ . '/koneksi_db.php';

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

$select = db_query ($con,"Select lebaran.cif as cif,calon_loan.angsuran,calon_loan.cao,Cust_Short_name,calon_loan.os as os,
no_anggota as norek ,code_kel,status_app,calon_loan.pokok,run_tenor,calon_loan.unit, calon_loan.bulat as bulat,calon_loan.ijaroh as ijaroh,nominal
from lebaran left join calon_loan on calon_loan.cif = lebaran.cif where nominal > 0 and tgl_tagih ='$tanggal'");
         
//$select = mysqli_query ($con,"Select lebaran.cif as cif,loan.angsuran,loan.cao,Cust_Short_name,loan.os as os,
//no_anggota as norek ,code_kel,status_app,loan.pokok,run_tenor,loan.unit, loan.bulat as bulat,loan.ijaroh as ijaroh,nominal
//from lebaran left join loan on loan.cif = lebaran.cif where nominal > 0");
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
        

        // 2026-11-22 (perintah user, P3-5): simpanan TYPE 10 = tabungan lebaran -> keterangan "Setoran Lebaran An nama CIF"
        // di simpanan & kedua baris jurnal (dulu "Setoran Angsuran An .." / kredit 2101000 "Simpanan Pokok")
        $ket1="Setoran Lebaran An $nama $ciff";
        $input ="Insert into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,blok,tgl_input,kode_transaksi)VALUES('$tanggal','$norek','$unit','$ciff','$code_kel',0,'10','$nominal','$unit','$ket1','','$cao','1','$tanggal','$kode_trans')";
        $inputtrnsk = db_query($con,$input);
        if($inputtrnsk){
        $ket="Setoran Lebaran An $nama $ciff";              
        
                // 2026-11-22 (P3-5): nomor jurnal_masuk auto increment (dulu max(jurnal_umum)+1 -> bisa bentrok, header hilang)
                db_query($con,"insert into jurnal_masuk(kode_transaksi,tanggal_selesai,unit) values('$kode_trans','$tanggal','$unit')");
                db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                values('$unit','$kode_trans','1120000','$tanggal','Bukti SYSTEM','$ket1','$nominal','$unit')");
                db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                values('$unit','$kode_trans','2101000','$tanggal','Bukti SYSTEM','$ket1','$nominal','$unit')");
              
                
            }
            else{
            echo "<script>alert('Maaf,.Porses Input Gagal');</script>";
          
            }
         
    
    
    
    }

 
    if (!mysqli_commit($con)) {
        echo "Transaksi Gagal commit Silahkan ulangi lagi";
        exit();
      }
echo "<script>alert('Setoran Lebaran sukses di simpan');</script>";

     
    
 
?>
