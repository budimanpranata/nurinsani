<?php require_once __DIR__ . '/koneksi_db.php'; 

$con = db_ni();
if (mysqli_connect_errno()) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
    exit;
}
 
// Turn autocommit off
mysqli_autocommit($con,FALSE);
 
 
include "function.php";
$tanggal_transaksi="2026-02-26";

$cek= db_fetch_array(db_query($con,"select param_tgl from param_tgl WHERE nama_param_tgl ='EOD_MOB'"));
$tanggal_transaksi = $cek['param_tgl']; 
         
$select = db_query ($con,"select bukti_setor.cao as cao, kode_unit, name as nominal, gl, unit, nama_ao,bank
from bukti_setor 
left join ao on ao.cao = bukti_setor.cao 
left JOIN branch on branch.kode_branch = ao.kode_unit
where tgl_setor ='$tanggal_transaksi'
order by ao.kode_unit");
while($data = db_fetch_array($select)){
 
      
        $unit=$data ['kode_unit'];
        $gl=$data ['gl'];
        $nominal=$data ['nominal'];
        $bank=$data['bank'];
        $ap =$data ['unit'];
        $nama_ao=mysqli_real_escape_string($con,($data ['nama_ao']));

        $keterangan_jurnal="Setoran $bank AREA PEMASARAN $ap AN $nama_ao";
        
        if($bank=="BSI"){
            $kode_rekening="1311000";
        }else if($bank=="BRI"){
            $kode_rekening="1333000";
        }else if($bank=="MANDIRI"){
            $kode_rekening="1335000";
        }else{
            $kode_rekening="1332000";
        }
       
        $kodetr=8;
        $kodetr=acakangkahuruf($kodetr);
        $kode_transaksi="BU/".$unit.$kodetr; 
        $keterangan="Penarikan simpanan an $nama";
        
        $query=db_query($con,"insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
        values('1010','$kode_transaksi','1010-$kode_rekening','$tanggal_transaksi','Kas Keluar','$keterangan_jurnal','$nominal','$unit')");	

$query=db_query($con,"insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
        values('1010','$kode_transaksi','1010-$gl','$tanggal_transaksi','Kas Keluar','$keterangan_jurnal','$nominal','$unit')");	

$query=db_query($con,"insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
        values('$unit','$kode_transaksi','2500000','$tanggal_transaksi','Kas Keluar','$keterangan_jurnal','$nominal','$unit')");	

      
$query=db_query($con,"insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
        values('$unit','$kode_transaksi','1120000','$tanggal_transaksi','Kas Keluar','$keterangan_jurnal','$nominal','$unit')");	

         
    
    
    
    }

 
    if (!mysqli_commit($con)) {
        echo "Transaksi Gagal commit Silahkan ulangi lagi";
        exit();
      }
echo "<script>alert('Setoran penarikan sukses di simpan');</script>";

     
    
 
?>
