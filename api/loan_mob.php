<?php require_once __DIR__ . '/../koneksi_db.php'; 

$con = db_ni();
if (mysqli_connect_errno()) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
    exit;
}
 
// Turn autocommit off
mysqli_autocommit($con,FALSE);

 
        $query=db_query($con,"delete from data_loan_mob");	



        $query=db_query($con,"insert into data_loan_mob select * from loan where os > 0");
        $query=db_query($con,"update data_loan_mob set code_kel =trim(code_kel)");
        $query=db_query($con,"update data_loan_mob set run_tenor ='0' where run_tenor =''");
        $query=db_query($con,"update data_loan_mob set buss_date = CURDATE() ");


 
    if (!mysqli_commit($con)) {
        echo "Transaksi Gagal commit Silahkan ulangi lagi";
        exit();
      }
echo "<script>alert('sukses');</script>";

     
    
 
?>
