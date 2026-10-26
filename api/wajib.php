<?php require_once __DIR__ . '/../koneksi_db.php'; 

$con = db_ni();
if (mysqli_connect_errno()) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
    exit;
}
 
// Turn autocommit off
mysqli_autocommit($con,FALSE);

 
        $query=db_query($con,"delete from data_wajib");	



        $query=db_query($con,"insert into data_wajib

select 
CURDATE() as BUSS_DATE,
loan.unit as UNIT,
NOREK,
loan.CIF as CIF,
sum(KREDIT-DEBIT) as nominal
from simpanan_wajib
join loan on loan.cif = simpanan_wajib.cif
where  os > 0  
group by loan.cif


");	


 
    if (!mysqli_commit($con)) {
        echo "Transaksi Gagal commit Silahkan ulangi lagi";
        exit();
      }
echo "<script>alert('sukses');</script>";

     
    
 
?>
