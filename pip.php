<?php require_once __DIR__ . '/koneksi_db.php';

$tgl_eod="2021-12-31";
$con = mysqli_connect("192.168.88.71","root","webcenter","pipsync");
if (mysqli_connect_errno()) {
	echo "Failed to connect to MySQL: " . mysqli_connect_error();
	
    exit;
  }
 
  $con2 = db_ni();
if (mysqli_connect_errno()) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
    exit;
  }
 
// Turn autocommit off
mysqli_autocommit($con,FALSE);

 $select = db_query ($con2,"Select 
    CONCAT(no_anggota,suffix) as noakad,
    loan.Cust_Short_name as nama,
    ID_NUMBER as nik,
    MARITAL_STATUS as marriage,
    '3' as pendidikan,
    '8' as pekerjaan,
    '1' as skemakredit,
    '1' as islinkage,
    MOBILE_PHONE as nomorhp,
    '000000000000000' as npwp,
    BIRTH_DATE as birthdate,
    '2' as sex,
    'CASH COLLATERAL' as uraian_agunan,
    'TIDAK ADA' as noizin, 
    '526000' as sektor,
    loan.buss_date as mulaiusaha,
    51/100 * plafond as modal,
    111/100 * plafond as omset,
    '1' as jumlahpekerja,
    plafond as jumlahkredit,
    '0' as is_subsidized,
    'TIDAK ADA' as subsidi_sebelumnya,
    HADDRESS1 as alamatusaha,
    kode_wilayah as kodewilayah,
    HPOST_CODE as kodepos,
    '' as kondisirumah,
    CONCAT(HADDRESS1,HADDRESS2,HADDRESS3,HADDRESS5,HADDRESS4) as alamat,
    norek as norek,
    '1' as statusakad,
    '1' statusrekening,
    tgl_wakalah as tanggalakad,
    maturity_date as tanggaljatuhtempo,
    plafond as nilaiakad,
    '42' as skema,
    loan.code_kel as idkelompok,
    18 as sukubunga,
    '526000' as sektor,
    '0' as kodepenjamin,
    '00000' as nopenjaminan,
    '0' as nilaidijamin,
    loan.unit,
    loan.cif
    from loan 
    left join anggota on anggota.cif = loan.cif 
    left join wilayah on wilayah.kode_pos=anggota.HPOST_CODE
    where loan.unit in (
    '2004',
    '2006',
    '2010',
    '2013',
    '2017',
    '2021',
    '2022',
    '2023',
    '2026',
    '2027',
    '2028',
    '2030',
    '2031',
    '2029',
    '2032',
    '2002',
    '2003',
    '2005'
    
    ) and tgl_murab = '$tgl_eod' and os > 0");

    while($data = db_fetch_array($select)){
   
 
       
$nik= trim($data['nik']);
var_dump($nik);
$noakad=$data['noakad'];

$idkelompok=$data['idkelompok'];
$nilaiakad=$data['nilaiakad'];
$nopenjaminan=0000;
$kodepenjamin=0;
$nilaidijamin=0;
$statusrekening=1;
$tanggalakad=$data['tanggalakad'];
$sukubunga=18;
$skema=42;
$norekening=$data['norek'];
$statusakad=1;
$tanggaljatuhtempo=$data['tanggaljatuhtempo'];
$sektor=526000;
$status_sync_akad=1; //default 1

// debitur

$nama = $data['nama'];

$sex = '2';
$uraianagunan = 'CASH COLLATERAL';
$birthdate = $data['birthdate'];
$pendidikan = $data['pendidikan'];
$islinkage = '1';
$noizin = 'TIDAK ADA';
$alamat = $data['alamat'];
$skemakredit = '1';
$mulaiusaha = $data['mulaiusaha'];
$kondisirumah = '3';
$is_subsidized = '1';
$npwp = '0';
$subsidi_sebelumnya = 'TIDAK ADA';
$jumlahpekerja = '1';
$jumlahkredit = $data['jumlahkredit'];
$kodewilayah = $data['kodewilayah'];
$omset = $data['omset'];
$pekerjaan = '1';
$nomorhp = $data['nomorhp'];
$marriage = $data['marriage'];
if ($marriage=='kawin'){
    $marriage =1;
}else{
    $marriage =2;
}
$modal = $data['modal'];
$kodepos = trim($data['kodepos']);
$alamatusaha = $data['alamatusaha'];
$status_sync_debitur = '1'; //default 1

// droping


$nominal_dropping = $data['nilaiakad'];
$tanggaltransaksi = $data['tanggalakad'];
$tanggalpelaporan = $tgl_eod;
$status_sync_dropping = "1";//default 1


// transaksi
// tgl transaksi tidak boleh sama dengan tanggal akad

$outstanding =$data['nilaiakad'];
$angsurandenda ="0";
$angsuranpokok ="120000";
$kodekolektibilitas ="1";
$limits =$data['nilaiakad'];
$angsuranbunga ="21600";
$tanggaltransaksi =$tgl_eod;
$tanggalpelaporan =$tgl_eod;
$period ="1";
$status_sync_transaksi ="1"; //default 1
$channel ="E001";



$insert=db_query ($con,"INSERT INTO akad(nik, noakad, idkelompok, nilaiakad, nopenjaminan, kodepenjamin, nilaidijamin, statusrekening, tanggalakad, sukubunga, skema, norekening, statusakad, tanggaljatuhtempo, sektor,  status_sync_akad ) VALUES ('$nik', '$noakad', '$idkelompok', '$nilaiakad', '$nopenjaminan', '$kodepenjamin', '$nilaidijamin', '$statusrekening', '$tanggalakad', '$sukubunga', '$skema', '$norekening', '$statusakad', '$tanggaljatuhtempo', '$sektor',  '$status_sync_akad')");

$insert=db_query ($con,"INSERT INTO  debitur (nik, nama, sex, uraianagunan, birthdate, pendidikan, islinkage, noizin, alamat, skemakredit, mulaiusaha, kondisirumah, is_subsidized, npwp, subsidi_sebelumnya, jumlahpekerja, jumlahkredit, kodewilayah, omset, pekerjaan, nomorhp, marriage, modal, kodepos, alamatusaha, status_sync_debitur) VALUES ('$nik', '$nama' , '$sex', '$uraianagunan', '$birthdate', '$pendidikan', '$islinkage', '$noizin', '$alamat', '$skemakredit', '$mulaiusaha', '$kondisirumah', '$is_subsidized', '$npwp', '$subsidi_sebelumnya', '$jumlahpekerja', '$jumlahkredit', '$kodewilayah', '$omset', '$pekerjaan', '$nomorhp', '$marriage', '$modal', '$kodepos', '$alamatusaha', '$status_sync_debitur')");

$insert=db_query ($con,"INSERT INTO dropping(noakad, norekening, nominal_dropping, tanggaltransaksi, tanggalpelaporan, status_sync_dropping) VALUES ( '$noakad', '$norekening', '$nominal_dropping', '$tanggaltransaksi', '$tanggalpelaporan', '$status_sync_dropping')");

$insert=db_query ($con,"INSERT INTO transaksi(noakad, outstanding, angsurandenda, norekening, angsuranpokok, kodekolektibilitas, limits, angsuranbunga, tanggaltransaksi, tanggalpelaporan, period, status_sync_transaksi,channel) VALUES ('$noakad', '$outstanding', '$angsurandenda', '$norekening', '$angsuranpokok', '$kodekolektibilitas', '$limits', '$angsuranbunga', '$tanggaltransaksi', '$tanggalpelaporan', '$period', '$status_sync_transaksi','$channel')");



    }


    if (!mysqli_commit($con)) {
        echo "Transaksi Gagal commit Silahkan ulangi lagi";
        exit();
      }else{

       
        if($insert){

            echo "<script>alert('suksess!!!');</script>";
    
          }else{
            echo "<script>alert('Gagal!!!');</script>";
          }
    
    
    }

      









?> 
