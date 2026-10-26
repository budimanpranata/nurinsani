<?php require_once __DIR__ . '/koneksi_db.php'; 

$con = db_ni();
if (mysqli_connect_errno()) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
    exit;
}
 
// Turn autocommit off
$cek= db_fetch_array(db_query($con,"select param_tgl from param_tgl WHERE nama_param_tgl ='EOD_MOB'"));
$tgl = $cek['param_tgl']; 


$libur_array= array();
$sql_libur = "Select tgl_libur from param_libur";
$res_libur = db_query($con, $sql_libur);

if($res_libur){
while($i= db_fetch_assoc($res_libur)){
$libur_array[] = $i['tgl_libur'];
	}
}

$sql_loan = "select cif, tgl_murab, Tenor, maturity_date from loan where maturity_date <='$tgl' and os > 0";
$res_loan = db_query($con, $sql_loan);

$update_count = 0;

while ($row = db_fetch_assoc($res_loan)){
$cif = $row['cif'];
$tgl_akad = $row['tgl_murab'];
$tenor = (int)$row['Tenor'];

$maturity_db = date('Y-m-d',strtotime($row['maturity_date']));

$v_counter = 0;
$v_tgl_cek = $tgl_akad;

while ($v_counter < $tenor){
$v_tgl_cek = date('Y-m-d', strtotime($v_tgl_cek . '+7 days'));

if(!in_array($v_tgl_cek, $libur_array)){
$v_counter++;
}
}

if($v_tgl_cek !== $maturity_db){

$update_sql = "Update loan set maturity_date = '$v_tgl_cek 00:00:00' where cif = '$cif' ";

$exec_update = db_query($con, $update_sql);

if($exec_update){
$update_count++;
}

}

}

//mysqli_autocommit($con,FALSE);
 

$ambilunit=db_query($con,"select unit from loan where os > 0 group by unit");
while($row_hit_mut=db_fetch_array($ambilunit)){
    $unit=$row_hit_mut['unit'];	

    
db_query($con,"update loan
set gol=(SELECT
CASE
WHEN ROUND(sum(KREDIT-DEBIT)/(angsuran+2500)) between 1 and 3 then '1'
WHEN ROUND(sum(KREDIT-DEBIT)/(angsuran+2500)) between 4 and 6 then '2'
WHEN ROUND(sum(KREDIT-DEBIT)/(angsuran+2500)) between 7 and 12 then '3'
WHEN ROUND(sum(KREDIT-DEBIT)/(angsuran+2500)) > 12 then '4'
else '1' end
FROM
tunggakan
where loan.cif=tunggakan.CIF
) where loan.unit ='$unit' and deal_produk='2' and os > 0");
       
db_query($con,"update loan
set gol=(SELECT

CASE
WHEN sum(KREDIT-DEBIT)/(angsuran) between 1 and 3 then '1'
WHEN sum(KREDIT-DEBIT)/(angsuran) between 4 and 6 then '2'
WHEN sum(KREDIT-DEBIT)/(angsuran) between 7 and 12 then '3'
WHEN sum(KREDIT-DEBIT)/(angsuran) > 12 then '4'
else '1' end
FROM
tunggakan
where loan.cif=tunggakan.CIF
) where loan.unit ='$unit' and deal_produk='1' and os > 0");
       
db_query($con,"update loan
set gol=(SELECT

CASE
WHEN sum(KREDIT-DEBIT)/(pokok+ijaroh) between 1 and 3 then '1'
WHEN sum(KREDIT-DEBIT)/(pokok+ijaroh) between 4 and 6 then '2'
WHEN sum(KREDIT-DEBIT)/(pokok+ijaroh) between 7 and 12 then '3'
WHEN sum(KREDIT-DEBIT)/(pokok+ijaroh) > 12 then '4'
else '1' end
FROM
tunggakan
where loan.cif=tunggakan.CIF
) where loan.unit ='$unit' and deal_produk='3' and os > 0");
       
db_query($con,"update loan
set gol=(SELECT
CASE
WHEN ROUND(sum(KREDIT-DEBIT)/(angsuran+2500)) between 1 and 3 then '1'
WHEN ROUND(sum(KREDIT-DEBIT)/(angsuran+2500)) between 4 and 6 then '2'
WHEN ROUND(sum(KREDIT-DEBIT)/(angsuran+2500)) between 7 and 12 then '3'
WHEN ROUND(sum(KREDIT-DEBIT)/(angsuran+2500)) > 12 then '4'
else '1' end
FROM
tunggakan
where loan.cif=tunggakan.CIF
) where loan.unit ='$unit' and deal_produk='4' and os > 0");
       
    
       

db_query($con,"update loan
set gol = 
CASE
	WHEN datediff('$tgl',maturity_date) between 1 and 30 then '2'
	WHEN datediff('$tgl',maturity_date) between 31 and 60 then '3'
	WHEN datediff('$tgl',maturity_date) > 60 then '4'
	else '2' 
END
	where 
	unit ='$unit' 
	and maturity_date <= '$tgl' 
	and os > 0 AND gol in ('1','2')");  


db_query($con,"update loan
set gol = 
CASE
	WHEN datediff('$tgl',maturity_date) between 1 and 59 then '3'
	WHEN datediff('$tgl',maturity_date) between 31 and 60 then '3'
	WHEN datediff('$tgl',maturity_date) > 60 then '4'
	else '3' 
END
	where 
	unit ='$unit' 
	and maturity_date <= '$tgl' 
	and os > 0 AND gol in ('3')");     
    
                        

}

 
    //if (!mysqli_commit($con)) {
        //echo "Transaksi Gagal commit Silahkan ulangi lagi";
        //exit();
      //}
echo "<script>alert('Proses Selesai.');</script>";

     
    
 
?>
