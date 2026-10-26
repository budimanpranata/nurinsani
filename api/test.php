<?php require_once __DIR__ . '/../koneksi_db.php'; 

$con = db_ni();
if (mysqli_connect_errno()) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
    exit;
}
 
// Turn autocommit off
mysqli_autocommit($con,FALSE);
 

        
        $query=db_query($con,"insert into cif_nol(cif,ap,sumber_dana)
        values('111111','1010','1')");	


         
    
    
   

 
    if (!mysqli_commit($con)) {
        echo "Transaksi Gagal commit Silahkan ulangi lagi";
        exit();
      }
echo "<script>alert('Setoran penarikan sukses di simpan');</script>";

     
    
 
?>
