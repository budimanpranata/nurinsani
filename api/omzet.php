<?php require_once __DIR__ . '/../koneksi_db.php'; 

$con = db_ni();
if (mysqli_connect_errno()) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
    exit;
}
 
// Turn autocommit off
mysqli_autocommit($con,FALSE);

 

        $query=db_query($con,"insert into omzet
select 
'' as id,
tgl_tagih as tanggal,
cif as cif,
omzet as nominal
from omzet_mob
where omzet > 0

");	



     $query=db_query($con,"insert into tabel_transaksi
select
unit, 
'' as id_transaksi,
kode_transaksi,
kode_rekening,
tanggal_transaksi,
jenis_transaksi,
keterangan_transaksi,
debet,
kredit,
tanggal_posting,
keterangan_posting,
id_admin
from tabel_transaksi_hris
where tanggal_transaksi = CURDATE() ");

 $query=db_query($con,"insert into tabel_transaksi
select
unit, 
'' as id_transaksi,
kode_transaksi,
kode_rekening,
tanggal_transaksi,
jenis_transaksi,
keterangan_transaksi,
debet,
kredit,
tanggal_posting,
keterangan_posting,
id_admin
from tabel_transaksi_cms
where tanggal_transaksi = CURDATE() ");	

$query=db_query($con,"update param_tgl set param_tgl =current_date() where nama_param_tgl ='EOD_MOB' ");
	


 
    if (!mysqli_commit($con)) {
        echo "Transaksi Gagal commit Silahkan ulangi lagi";
        exit();
      }
echo "<script>alert('sukses');</script>";

     
    
 
?>
