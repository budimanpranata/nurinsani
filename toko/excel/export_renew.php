<?php require_once __DIR__ . '/../../php8_compat.php';
session_start();
include "../conn.php";
$koneksi=open_connection();
$userid=$_SESSION['userid'];
$result = mssql_query("select 
smsbsm.Area,
nama,
UserId,
smsbsm.Jabatan1
from tabeluser
Inner Join smsbsm on tabeluser.UserId = smsbsm.NIK_SMS  WHERE UserId='$userid'");
while($row = mssql_fetch_array($result)){
$nama=$row['nama'];
$nik=$row['UserId'];
$area=$row['Area'];
$Jabatan=$row['Jabatan1'];
}
?>
<?php 
function xlsBOF() {
echo pack("ssssss", 0x809, 0x8, 0x0, 0x10, 0x0, 0x0);
return;
}

function xlsEOF() {
echo pack("ss", 0x0A, 0x00);
return;
}

function xlsWriteNumber($Row, $Col, $Value) {
echo pack("sssss", 0x203, 14, $Row, $Col, 0x0);
echo pack("d", $Value);
return;
}

function xlsWriteLabel($Row, $Col, $Value ) {
$L = strlen($Value);
echo pack("ssssss", 0x204, 8 + $L, $Row, $Col, 0x0, $L);
echo $Value;
return;
}

	$select = mssql_query ("select smsbsm.Area,smsbsm.SMS,smsbsm.NIK_SMS,Branch_name,Nama,CONVERT(VARCHAR,TGL_LAHIR,103) as tgl_lahir, FLOOR(DATEDIFF(MM, TGL_LAHIR, GETDATE())/12) AS Usia, NOPEN,CONVERT(VARCHAR,TGL_JTH_TEMPO,103) AS TGL_JTH_TEMPO,NEW_PLAFOND,NEW_TENOR,GAJI,SISA_GAJI,MAX_POTONGAN,ASURANSI,
	BERSIH,ALAMAT,KELURAHAN,KOTA,HOME_PHONE,HAND_PHONE,JENIS,GROUP_BAYAR,KODE_MITRA,M_Bayar.MBayar as MITRA_BAYAR FROM CIM_REN1
	Inner Join smsbsm  ON CIM_REN1.Branch_code = smsbsm.Br_code 
	Inner join M_Bayar ON CIM_REN1.KODE_MITRA = M_Bayar.Kode
	where smsbsm.NIK_SMS ='$nik' AND (PLAFOND BETWEEN 2000000 AND 300000000) AND BERSIH >= 2000000 ");
	while($res = mssql_fetch_array($select)){

	$data['Area'][] = $res['Area'];
	$data['SMS'][] = $res['SMS'];
	$data['Branch_name'][] = $res['Branch_name'];
	$data['Nama'][] = $res['Nama'];
	$data['tgl_lahir'][] = $res['tgl_lahir'];
	$data['Usia'][] = $res['Usia'];
	$data['NOPEN'][] = $res['NOPEN'];
	$data['TGL_JTH_TEMPO'][] = $res['TGL_JTH_TEMPO'];
	$data['NEW_PLAFOND'][] = $res['NEW_PLAFOND'];
	$data['NEW_TENOR'][] = $res['NEW_TENOR'];
	$data['GAJI'][] = $res['GAJI'];
	$data['SISA_GAJI'][] = $res['SISA_GAJI'];
	$data['MAX_POTONGAN'][] = $res['MAX_POTONGAN'];
	$data['BERSIH'][] = $res['BERSIH'];
	$data['ASURANSI'][] = $res['ASURANSI'];
	$data['ALAMAT'][] = $res['ALAMAT'];
	$data['KELURAHAN'][] = $res['KELURAHAN'];
	$data['KOTA'][] = $res['KOTA'];
	$data['HOME_PHONE'][] = $res['HOME_PHONE'];
	$data['HAND_PHONE'][] = $res['HAND_PHONE'];
	$data['JENIS'][] = $res['JENIS'];
	$data['MITRA_BAYAR '][] = $res['MITRA_BAYAR '];
	$data['KODE_MITRA'][] = $res['KODE_MITRA'];

} 

$jm = sizeof($data['SMS']);
header("Pragma: public" );
header("Expires: 0" );
header("Cache-Control: must-revalidate, post-check=0, pre-check=0" );
header("Content-Type: application/force-download" );
header("Content-Type: application/octet-stream" );
header("Content-Type: application/download" );;
header("Content-Disposition: attachment;filename=PotensiRenew.xls " );
header("Content-Transfer-Encoding: binary " );
xlsBOF();
xlsWriteLabel(0,3,"DATA POTENSI RENEW" );
xlsWriteLabel(2,0,"No" );
xlsWriteLabel(2,1,"Area" );
xlsWriteLabel(2,2,"SMS" );
xlsWriteLabel(2,3,"Branch_Name" );
xlsWriteLabel(2,4,"Nama" );
xlsWriteLabel(2,5,"Tanggal_Lahir" );
xlsWriteLabel(2,6,"Usia" );
xlsWriteLabel(2,7,"Nopen" );
xlsWriteLabel(2,8,"TGL_JTH_TEMPO" );
xlsWriteLabel(2,9,"NEW_PLAFOND" );
xlsWriteLabel(2,10,"NEW_TENOR" );
xlsWriteLabel(2,11,"Gaji" );
xlsWriteLabel(2,12,"SISA_GAJI" );
xlsWriteLabel(2,13,"MAX_POTONGAN" );
xlsWriteLabel(2,14,"ASURANSI" );
xlsWriteLabel(2,15,"BERSIH" );
xlsWriteLabel(2,16,"ALAMAT" );
xlsWriteLabel(2,17,"KELURAHAN" );
xlsWriteLabel(2,18,"KOTA" );
xlsWriteLabel(2,19,"HOME_PHONE" );
xlsWriteLabel(2,20,"HAND_PHONE" );
xlsWriteLabel(2,21,"JENIS" );
xlsWriteLabel(2,22,"MITRA_BAYAR " );
xlsWriteLabel(2,23,"KODE_MITRA" );


$xlsRow = 3;

for ($y=0; $y<$jm; $y++) {
	++$i;
	xlsWriteNumber($xlsRow,0,"$i" );
	xlsWriteLabel($xlsRow,1,$data['Area'][$y]);
	xlsWriteLabel($xlsRow,2,$data['SMS'][$y]);
	xlsWriteLabel($xlsRow,3,$data['Branch_name'][$y]);
	xlsWriteLabel($xlsRow,4,$data['Nama'][$y]);
	xlsWriteLabel($xlsRow,5,$data['tgl_lahir'][$y]);
	xlsWriteLabel($xlsRow,6,$data['Usia'][$y]);
	xlsWriteLabel($xlsRow,7,$data['NOPEN'][$y]);
	xlsWriteLabel($xlsRow,8,$data['TGL_JTH_TEMPO'][$y]);
	xlsWriteLabel($xlsRow,9,$data['NEW_PLAFOND'][$y]);
	xlsWriteLabel($xlsRow,10,$data['NEW_TENOR'][$y]);
	xlsWriteLabel($xlsRow,11,$data['GAJI'][$y]);
	xlsWriteLabel($xlsRow,12,$data['SISA_GAJI'][$y]);
	xlsWriteLabel($xlsRow,13,$data['MAX_POTONGAN'][$y]);
	xlsWriteLabel($xlsRow,14,$data['ASURANSI'][$y]);
	xlsWriteLabel($xlsRow,15,$data['BERSIH'][$y]);
	xlsWriteLabel($xlsRow,16,$data['ALAMAT'][$y]);
	xlsWriteLabel($xlsRow,17,$data['KELURAHAN'][$y]);
	xlsWriteLabel($xlsRow,18,$data['KOTA'][$y]);
	xlsWriteLabel($xlsRow,19,$data['HOME_PHONE'][$y]);
	xlsWriteLabel($xlsRow,20,$data['HAND_PHONE'][$y]);
	xlsWriteLabel($xlsRow,21,$data['JENIS'][$y]);
	xlsWriteLabel($xlsRow,22,$data['MITRA_BAYAR'][$y]);
	xlsWriteLabel($xlsRow,23,$data['KODE_MITRA'][$y]);
	
	
	$xlsRow++;
}
xlsEOF();
exit();