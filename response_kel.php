
<?php require_once __DIR__ . '/koneksi_db.php';
session_start();
 include "conn2.php";
  $koneksi=open_connection2();
 $tgl_system=$_SESSION['stgl_system'];
//cek apakah user sudah login
if(!isset($_SESSION['stgl_system'])){
echo "<script>alert('Maaf,.tanggal system belum di setup');</script>";

   header("location:param_tgl.php");
}

  $tanggl=$_POST ['tanggal'];
$buss_date=date("y-m-d");
$userid=$_SESSION['userid'];
$data = mysql_query ("select kode_branch from user where userid ='$userid'");
$r = mysql_fetch_array($data);
$unit=$r['kode_branch'];
//cek apakah user sudah login
if(!isset($_SESSION['userid'])){
   header("location:index.html");//jika belum login jangan lanjut..
}

//cek level user
if($_SESSION['level']!="unit"){
     header("location:index.html");//jika bukan user jangan lanjut
}
	//include connection file 
	$db_host = "localhost";
$db_user = "root";
$db_pass = "anaskorti";
$db_name = "nur_insani";

$conn = db_ni();

if(mysqli_connect_errno()){
	echo 'Gagal melakukan koneksi ke Database : '.mysqli_connect_error();
}   
	 
	// initilize all variable
	$params = $columns = $totalRecords = $data = array();

	$params = $_REQUEST;

	//define index of column
	$columns = array( 
		0 =>'tgl_realisasi',
		1 =>'unit',
		2 =>'nama_kel', 
		3 =>'cif',
		4 =>'nama',
		5 =>'Pokok',
		6 =>'Margin',
		7 =>'TOTAL'

		
	);

	$where = $sqlTot = $sqlRec = "";

	// getting total number records without any search
	$sql = "Select tgl_realisasi,rek_loan.unit as unit,nama_kel,loan.cif,loan.cust_short_name as nama,
SUM(IF(rek_loan.status ='REALISASI POKOK', debet, 0)) AS Pokok,
SUM(IF(rek_loan.status ='REALISASI BAGHAS', debet, 0)) AS Margin,
SUM(debet) AS TOTAL
from rek_loan
inner JOIN loan on loan.cif=rek_loan.cif
INNER JOIN kelompok on kelompok.code_kel=loan.code_kel
where rek_loan.unit='$unit' and tgl_realisasi='$tgl_system'
group by rek_loan.cif ";
	$sqlTot .= $sql;
	$sqlRec .= $sql;


 	$sqlRec .=  " ORDER BY rek_loan.cif";

	$queryTot = db_query($conn, $sqlTot) or die("database error:". mysqli_error($conn));


	$totalRecords = db_num_rows($queryTot);

	$queryRecords = db_query($conn, $sqlRec) or die("error to fetch employees data");

	//iterate on results row and create new index array of data
	while( $row = db_fetch_row($queryRecords) ) { 
		$data[] = $row;
	}	

	$json_data = array(
			"draw"            => 1,   
			"recordsTotal"    => intval( $totalRecords ),  
			"recordsFiltered" => intval($totalRecords),
			"data"            => $data   // total data array
			);

	echo json_encode($json_data);  // send data as json format
?>
	
