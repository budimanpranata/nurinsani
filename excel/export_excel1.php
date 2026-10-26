<?php require_once __DIR__ . '/../php8_compat.php';
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

	$select = mssql_query ("select smsbsm.Area,smsbsm.SMS,smsbsm.NIK_SMS,Branch_name,Nama,CONVERT(VARCHAR,TGL_LAHIR,103) as tgl_lahir, FLOOR(DATEDIFF(MM, TGL_LAHIR, GETDATE())/12) AS Usia, NOPEN,Plafond,Tenor,GAJI,SISA_GAJI,MAX_POTONGAN,ASURANSI,ALAMAT,KELURAHAN,KOTA,JENIS,GROUP_BAYAR,KODE_MITRA FROM CIM_NEW
	Inner Join smsbsm  ON CIM_NEW.Branch_code=smsbsm.Br_code where smsbsm.NIK_SMS ='$nik'");
	while($res = mssql_fetch_array($select)){

	$data['Area'][] = $res['Area'];
	$data['SMS'][] = $res['SMS'];
	$data['Branch_name'][] = $res['Branch_name'];
	$data['Nama'][] = $res['Nama'];
	$data['tgl_lahir'][] = $res['tgl_lahir'];
	$data['Usia'][] = $res['Usia'];
	$data['NOPEN'][] = $res['NOPEN'];
	$data['Plafond'][] = $res['Plafond'];
	$data['Tenor'][] = $res['Tenor'];
	$data['GAJI'][] = $res['GAJI'];
	$data['SISA_GAJI'][] = $res['SISA_GAJI'];
	$data['MAX_POTONGAN'][] = $res['MAX_POTONGAN'];
	$data['ASURANSI'][] = $res['ASURANSI'];
	$data['ALAMAT'][] = $res['ALAMAT'];
	$data['KELURAHAN'][] = $res['KELURAHAN'];
	$data['KOTA'][] = $res['KOTA'];
	$data['JENIS'][] = $res['JENIS'];
	$data['GROUP_BAYAR'][] = $res['GROUP_BAYAR'];
	$data['KODE_MITRA'][] = $res['KODE_MITRA'];

} 

$jm = sizeof($data['SMS']);
header("Pragma: public" );
header("Expires: 0" );
header("Cache-Control: must-revalidate, post-check=0, pre-check=0" );
header("Content-Type: application/force-download" );
header("Content-Type: application/octet-stream" );
header("Content-Type: application/download" );;
header("Content-Disposition: attachment;filename=PotensiTaspenAsabri.xls " );
header("Content-Transfer-Encoding: binary " );
xlsBOF();
xlsWriteLabel(0,3,"DATA POTENSI NEW TASPEN DAN ASABRI" );
xlsWriteLabel(2,0,"No" );
xlsWriteLabel(2,1,"Area" );
xlsWriteLabel(2,2,"SMS" );
xlsWriteLabel(2,3,"Branch_Name" );
xlsWriteLabel(2,4,"Nama" );
xlsWriteLabel(2,5,"Tanggal_Lahir" );
xlsWriteLabel(2,6,"Usia" );
xlsWriteLabel(2,7,"Nopen" );
xlsWriteLabel(2,8,"Plafond" );
xlsWriteLabel(2,9,"Tenor" );
xlsWriteLabel(2,10,"Gaji" );
xlsWriteLabel(2,11,"SISA_GAJI" );
xlsWriteLabel(2,12,"MAX_POTONGAN" );
xlsWriteLabel(2,13,"ALAMAT" );
xlsWriteLabel(2,14,"KELURAHAN" );
xlsWriteLabel(2,15,"KOTA" );
xlsWriteLabel(2,16,"JENIS" );
xlsWriteLabel(2,17,"GROUP_BAYAR" );
xlsWriteLabel(2,18,"KODE_MITRA" );


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
	xlsWriteLabel($xlsRow,8,$data['Plafond'][$y]);
	xlsWriteLabel($xlsRow,9,$data['Tenor'][$y]);
	xlsWriteLabel($xlsRow,10,$data['GAJI'][$y]);
	xlsWriteLabel($xlsRow,11,$data['SISA_GAJI'][$y]);
	xlsWriteLabel($xlsRow,12,$data['MAX_POTONGAN'][$y]);
	xlsWriteLabel($xlsRow,13,$data['ALAMAT'][$y]);
	xlsWriteLabel($xlsRow,14,$data['KELURAHAN'][$y]);
	xlsWriteLabel($xlsRow,15,$data['KOTA'][$y]);
	xlsWriteLabel($xlsRow,16,$data['JENIS'][$y]);
	xlsWriteLabel($xlsRow,17,$data['GROUP_BAYAR'][$y]);
	xlsWriteLabel($xlsRow,18,$data['KODE_MITRA'][$y]);
	
	
	$xlsRow++;
}
xlsEOF();
exit();