<?php require_once __DIR__ . '/../koneksi_db.php'; 

$con = db_ni();
if (mysqli_connect_errno()) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
    exit;
}
 
// Turn autocommit off
mysqli_autocommit($con,FALSE);

 
        $query=db_query($con,"delete from tunggakan_eom");	



        $query=db_query($con,"insert into tunggakan_eom

select 
CURDATE() as BUSS_DATE,
tunggakan.unit,
tunggakan.cif,
Cust_Short_name,
os,
saldo_margin,
ROUND(sum(KREDIT-DEBIT)/angsuran) as ft,
ROUND(sum(KREDIT-DEBIT)) as nominal,
bulat as angsuran,
loan.code_kel as code_kel,
loan.cao as cao,
'0' as twm,
bulat,
plafond
from tunggakan
inner join loan on loan.cif = tunggakan.cif
group by tunggakan.cif
HAVING sum(KREDIT-DEBIT) > 0


");	


 
    if (!mysqli_commit($con)) {
        echo "Transaksi Gagal commit Silahkan ulangi lagi";
        exit();
      }
echo "<script>alert('sukses');</script>";

     
    
 
?>
