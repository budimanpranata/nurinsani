<?php require_once __DIR__ . '/../koneksi_db.php'; 

$con = db_ni();
if (mysqli_connect_errno()) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
    exit;
}
 
// Turn autocommit off
mysqli_autocommit($con,FALSE);

 
        $query=db_query($con,"delete from data_pokok");	



        $query=db_query($con,"insert into data_pokok

select 
CURDATE() as BUSS_DATE,
loan.unit as UNIT,
NOREK,
loan.CIF as CIF,
sum(KREDIT-DEBIT) as nominal
from simpanan_pokok
join loan on loan.cif = simpanan_pokok.cif
where  os > 0  
group by loan.cif


");	


 
    if (!mysqli_commit($con)) {
        echo "Transaksi Gagal commit Silahkan ulangi lagi";
        exit();
      }
echo "<script>alert('sukses');</script>";

     
    
 
?>
