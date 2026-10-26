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
        

        $ket1="Setoran Angsuran An $nama";
        $input ="Insert into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,blok,tgl_input,kode_transaksi)VALUES('$tanggal','$norek','$unit','$ciff','$code_kel',0,'10','$nominal','$unit','$ket1','','$cao','1','$tanggal','$kode_trans')";
        $inputtrnsk = db_query($con,$input);
        if($inputtrnsk){
        $ket="Setoran Angsuran An $nama";              
        
                $jurnal_umum=db_fetch_array(db_query($con,"SELECT max(nomor_jurnal) FROM jurnal_umum where unit ='$unit' ORDER BY tanggal_selesai DESC"));
                $nomor_jurnal=$jurnal_umum[0]+1;
                db_query($con,"insert into jurnal_masuk(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values('$nomor_jurnal','$kode_trans','$tanggal','$unit')");
                db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                values('$unit','$kode_trans','1120000','$tanggal','Bukti SYSTEM','$ket1','$nominal','$unit')");
                db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                values('$unit','$kode_trans','2101000','$tanggal','Bukti SYSTEM','Simpanan Pokok','$nominal','$unit')");
              
                
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
