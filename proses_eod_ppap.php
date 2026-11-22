<?php require __DIR__ . '/inc/hanya_cli.php'; require_once __DIR__ . '/koneksi_db.php'; 

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
// 2026-11-22 (perintah user, P3-2 #2): tgl_murab kosong/0000 atau tenor <= 0 dilewati (dulu jatuh tempo dihitung dari
// hari ini / tanggal rusak -> pinjaman lewat tempo tampak belum jatuh tempo)
if (trim((string) $tgl_akad) === '' || strpos((string) $tgl_akad, '0000-00-00') === 0 || $tenor <= 0) {
    echo "Dilewati (tgl_murab/tenor tidak valid): $cif\n";
    continue;
}

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
 

// 2026-11-22 (perintah user, P3-2 #1): enam UPDATE golongan (rumus per deal_produk + aturan jatuh tempo) memakai helper
// bersama inc/kolektabilitas.php (SQL sama persis dengan versi ini dan dengan menu Report PPAP unit & Hitung PPAP KP), kini
// dalam satu transaksi per unit: bila gagal, gol unit itu tetap hasil kemarin (dulu tanpa transaksi -> bisa setengah).
require_once __DIR__ . '/inc/kolektabilitas.php';
$ambilunit=db_query($con,"select unit from loan where os > 0 group by unit order by unit");
while($row_hit_mut=db_fetch_array($ambilunit)){
    $unit=$row_hit_mut['unit'];
    if (!hitung_kolektabilitas($unit, $tgl)) {
        echo "GAGAL hitung kolektabilitas unit $unit (dibatalkan, gol tetap hasil sebelumnya)\n";
    }
}

 
    //if (!mysqli_commit($con)) {
        //echo "Transaksi Gagal commit Silahkan ulangi lagi";
        //exit();
      //}
echo "<script>alert('Proses Selesai.');</script>";

     
    
 
?>
