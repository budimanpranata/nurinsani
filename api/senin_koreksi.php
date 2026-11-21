<?php require __DIR__ . '/../inc/hanya_cli.php'; require_once __DIR__ . '/../koneksi_db.php';
// 2026-11-21 (perintah user, PEKERJAAN_TERTUNDA §103 P1-4): hanya CLI (jalankan manual lewat SSH bila perlu). Dulu bisa
// dibuka lewat web -> data_wadiah Senin bertambah ganda. Isi proses tidak diubah.

$con = db_ni();
if (mysqli_connect_errno()) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
    exit;
}
 
// Turn autocommit off
mysqli_autocommit($con,FALSE);

 
        //$query=mysqli_query($con,"delete from data_wadiah");	



        $query=db_query($con,"insert into data_wadiah

select 
CURDATE() as BUSS_DATE,
loan.unit as UNIT,
NOREK,
loan.CIF as CIF,
sum(KREDIT-DEBIT) as nominal,
loan.angsuran as angsuran
from simpanan
join loan on loan.cif = simpanan.cif
where TYPE not in ('04','05') and os > 0 and hari ='Senin' 
group by loan.cif order by loan.cif


");	


 
    if (!mysqli_commit($con)) {
        echo "Transaksi Gagal commit Silahkan ulangi lagi";
        exit();
      }
echo "<script>alert('sukses');</script>";

     
    
 
?>
