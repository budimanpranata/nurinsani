<?php require_once __DIR__ . '/koneksi_db.php';
session_start();
ob_start();
error_reporting(0);
$userid=$_SESSION['userid'];
$tgl_system=$_SESSION['stgl_system'];
//cek apakah user sudah login

if(!isset($_SESSION['userid'])){
   header("location:index.html");
 exit; }
/* Database connection start */
$servername = "76.13.20.176";
$username = "admin";
$password = "";
$dbname = "mobcol";
include "indo_tgl.php";

$conn = db_mobcol() or die("Connection failed: " . mysqli_connect_error());

/* Database connection end */


// storing  request (ie, get/post) global array to a variable  
$requestData= $_REQUEST;
$bussdate=$_REQUEST;


$columns = array( 
// datatable column index  => database column name
	0 => 'tgl_setor',
    1 => 'unit',
    2 => 'nama_ao', 
	3 => 'bank',
	4 => 'name',
	5 => 'image',
    
	

);

// getting total number records without any search
$sql = "SELECT tgl_setor,kode_unit,nama_ao,bank,name,image";
$sql.=" FROM bukti_setor left join ao on ao.cao = bukti_setor.cao where kode_unit ='$userid'";
$query=db_query($conn, $sql) or die("ajax-grid-data_trns.php: get InventoryItems");
$totalData = db_num_rows($query);
$totalFiltered = $totalData;  // when there is no search parameter then total number rows = total number filtered rows.


if( !empty($requestData['search']['value']) ) {
	// if there is a search parameter
	$sql = "SELECT tgl_setor,kode_unit,nama_ao,bank,name,image";
$sql.=" FROM bukti_setor left join ao on ao.cao = bukti_setor.cao";
    $sql.=" WHERE kode_unit  LIKE '".$requestData['search']['value']."%'  ";  
    $sql.=" OR nama_ao LIKE '".$requestData['search']['value']."%'  ";
	$sql.=" OR bank LIKE '".$requestData['search']['value']."%'  ";    // $requestData['search']['value'] contains search parameter
	$sql.=" OR tgl_setor  LIKE '".$requestData['search']['value']."%'  ";
 
	
	$query=db_query($conn, $sql) or die("mob-ajax-grid-data_bank.php: get PO");
	$totalFiltered = db_num_rows($query); // when there is a search parameter then we have to modify total number filtered rows as per search result without limit in the query 

	$sql.=" ORDER BY ". $columns[$requestData['order'][0]['column']]."   ".$requestData['order'][0]['dir']."   LIMIT ".$requestData['start']." ,".$requestData['length']."   "; // $requestData['order'][0]['column'] contains colmun index, $requestData['order'][0]['dir'] contains order such as asc/desc , $requestData['start'] contains start row number ,$requestData['length'] contains limit length.
	$query=db_query($conn, $sql) or die("mob-ajax-grid-data_bank.php: get PO"); // again run query with limit
	
} else {	

    $sql = "SELECT tgl_setor,kode_unit,nama_ao,bank,name,image";
    $sql.=" FROM bukti_setor left join ao on ao.cao = bukti_setor.cao where kode_unit ='$userid'";

	$sql.=" ORDER BY ". $columns[$requestData['order'][0]['column']]."   ".$requestData['order'][0]['dir']."   LIMIT ".$requestData['start']." ,".$requestData['length']."   ";
	$query=db_query($conn, $sql) or die("mob-ajax-grid-data_bank.php: get PO");
	
}

$data = array();
while( $row=db_fetch_array($query) ) {  // preparing an array
	$nestedData=array(); 

	
    $nestedData[] = tgl_indo($row["tgl_setor"]);
    $nestedData[] = $row["kode_unit"];
    $nestedData[] = $row["nama_ao"];
	$nestedData[] = $row["bank"];	
	$nestedData[] = number_format (compat_num($row["name"]));
    $nestedData[] =  '<td><a href="download.php?file='.$row['image'].'" class="btn btn-primary"><span class="glyphicon glyphicon-download"></span> Download</a></td>';

    	
	
	$data[] = $nestedData;
    
}



$json_data = array(
			"draw"            => intval( $requestData['draw'] ),   // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw. 
			"recordsTotal"    => intval( $totalData ),  // total number of records
			"recordsFiltered" => intval( $totalFiltered ), // total number of records after searching, if there is no searching then totalFiltered = totalData
			"data"            => $data   // total data array
			);

echo json_encode($json_data);  // send data as json format

?>
