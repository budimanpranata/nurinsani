
<?php require_once __DIR__ . '/koneksi_db.php';
session_start();
 include "conn2.php";
  $koneksi=open_connection2();
 
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
		0 =>'unit',
		1 =>'nama_kel', 
		2 =>'nama_ao',
		3 =>'cif',
		4 =>'nama',
		5 =>'ft',
		6 =>'nominal'

		
	);

	$where = $sqlTot = $sqlRec = "";

	// getting total number records without any search
	$sql = "select
tunggakan.unit,
nama_kel,
nama_ao,
tunggakan.cif,
Cust_Short_name,
ROUND(sum(KREDIT-DEBIT)/angsuran) as ft,
(sum(KREDIT-DEBIT)) as nominal
from tunggakan
inner join loan on loan.cif=tunggakan.cif
inner join ao on ao.cao=tunggakan.CAO
inner join kelompok on kelompok.code_kel = tunggakan.code_kel
where tunggakan.unit ='$unit' 
group by tunggakan.cif
HAVING sum(KREDIT-DEBIT) > 0
";
	$sqlTot .= $sql;
	$sqlRec .= $sql;


 	$sqlRec .=  " ORDER BY cif";

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
	
