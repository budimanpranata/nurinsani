<?php require_once __DIR__ . '/../php8_compat.php';
session_start();
ob_start();  
include "../conn2.php";
$koneksi=open_connection2();
$userid=$_SESSION['userid'];
$awal=$_SESSION['sbuss1'];
$akhir=$_SESSION['sbuss2'];

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

$select = mysql_query (" select 
unit,
no_anggota,
Cust_Short_name,
Plafond,
nominal,
nama_kel,
nama_ao,
suffic
from loan_detail
left join kelompok on kelompok.code_kel=loan_detail.code_kel
left join ao on ao.cao = loan_detail.cao
left join rekomendasi on rekomendasi.norek=loan_detail.no_anggota 
WHERE unit='$userid' AND buss_date BETWEEN '$awal' AND '$akhir'");
while($res = mysql_fetch_array($select)){

	$data['unit'][] = $res['unit'];
	$data['no_anggota'][] = $res['no_anggota'];
	$data['Cust_Short_name'][] = $res['Cust_Short_name'];
	$data['Plafond'][] = $res['Plafond'];
	$data['nominal'][] = $res['nominal'];
	$data['nama_kel'][] = $res['nama_kel'];
	$data['nama_ao'][] = $res['nama_ao'];
	$data['suffic'][] = $res['suffic'];
	} 

$jm = sizeof($data['no_anggota']);
header("Pragma: public" );
header("Expires: 0" );
header("Cache-Control: must-revalidate, post-check=0, pre-check=0" );
header("Content-Type: application/force-download" );
header("Content-Type: application/octet-stream" );
header("Content-Type: application/download" );;
header("Content-Disposition: attachment;filename=wakalah.xls " );
header("Content-Transfer-Encoding: binary " );
xlsBOF();
xlsWriteLabel(0,1,"KSPPS NURINSANI" );
xlsWriteLabel(1,1,"TANGGAL" );
xlsWriteLabel(2,1,"NAMA UNIT" );
xlsWriteLabel(2,2,"NOMOR ANGGOTA" );
xlsWriteLabel(2,3,"NAMA" );
xlsWriteLabel(2,4,"NAMA KELOMPOK" );
xlsWriteLabel(2,5,"NAMA AO" );
xlsWriteLabel(2,6,"PEMBIAYAAN" );
xlsWriteLabel(2,7,"REKOMENDASI" );
xlsWriteLabel(2,8,"PINJAMAN KE" );



$xlsRow = 3;

for ($y=0; $y<$jm; $y++) {
	++$i;
	xlsWriteNumber($xlsRow,0,"$i" );
	xlsWriteLabel($xlsRow,1,$data['unit'][$y]);
	xlsWriteLabel($xlsRow,2,$data['no_anggota'][$y]);
	xlsWriteLabel($xlsRow,3,$data['Cust_Short_name'][$y]);
	xlsWriteLabel($xlsRow,4,$data['nama_kel'][$y]);
	xlsWriteLabel($xlsRow,5,$data['nama_ao'][$y]);
	xlsWriteLabel($xlsRow,6,$data['Plafond'][$y]);
	xlsWriteLabel($xlsRow,7,$data['nominal'][$y]);
	xlsWriteLabel($xlsRow,8,$data['suffic'][$y]);
	
		
	
	$xlsRow++;
}
xlsEOF();
exit();