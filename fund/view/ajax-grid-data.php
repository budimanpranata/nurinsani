<?php require_once __DIR__ . '/../../koneksi_db.php';
/* Database connection start */
$servername = "localhost";
$username = "root";
$password = "anaskorti";
$dbname = "nur_insani";

$conn = db_ni() or die("Connection failed: " . mysqli_connect_error());
include "../../indo_tgl.php";
/* Database connection end */


// storing  request (ie, get/post) global array to a variable  
$requestData= $_REQUEST;


$columns = array( 
// datatable column index  => database column name
	0 => 'UNIT',
    1 => 'CIF', 
	2 => 'CUST_SHORT_NAME',
	3 => 'BIRTH_DATE',
    4 => 'HADDRESS1',
    5 => 'MOBILE_PHONE'  
);

// getting total number records without any search
$sql = "SELECT UNIT, no, CUST_SHORT_NAME, BIRTH_DATE, HADDRESS1, MOBILE_PHONE ";
$sql.=" FROM deposan";
$query=db_query($conn, $sql) or die("ajax-grid-data.php: get InventoryItems");
$totalData = db_num_rows($query);
$totalFiltered = $totalData;  // when there is no search parameter then total number rows = total number filtered rows.


if( !empty($requestData['search']['value']) ) {
	// if there is a search parameter
	$sql = "SELECT UNIT, CIF, CUST_SHORT_NAME, BIRTH_DATE, HADDRESS1, MOBILE_PHONE ";
	$sql.=" FROM deposan";
	$sql.=" WHERE CIF LIKE '".$requestData['search']['value']."%' ";    // $requestData['search']['value'] contains search parameter
	$sql.=" OR CUST_SHORT_NAME LIKE '".$requestData['search']['value']."%' ";
	$sql.=" OR BIRTH_DATE LIKE '".$requestData['search']['value']."%' ";
    $sql.=" OR HADDRESS1 LIKE '".$requestData['search']['value']."%' ";
    $sql.=" OR MOBILE_PHONE LIKE '".$requestData['search']['value']."%' ";
	$query=db_query($conn, $sql) or die("../view/ajax-grid-data.php: get PO");
	$totalFiltered = db_num_rows($query); // when there is a search parameter then we have to modify total number filtered rows as per search result without limit in the query 

	$sql.=" ORDER BY ". $columns[$requestData['order'][0]['column']]."   ".$requestData['order'][0]['dir']."   LIMIT ".$requestData['start']." ,".$requestData['length']."   "; // $requestData['order'][0]['column'] contains colmun index, $requestData['order'][0]['dir'] contains order such as asc/desc , $requestData['start'] contains start row number ,$requestData['length'] contains limit length.
	$query=db_query($conn, $sql) or die("../view/ajax-grid-data.php: get PO"); // again run query with limit
	
} else {	

	$sql = "SELECT  UNIT, CIF, CUST_SHORT_NAME, BIRTH_DATE, HADDRESS1, MOBILE_PHONE ";
	$sql.=" FROM deposan";
	$sql.=" ORDER BY ". $columns[$requestData['order'][0]['column']]."   ".$requestData['order'][0]['dir']."   LIMIT ".$requestData['start']." ,".$requestData['length']."   ";
	$query=db_query($conn, $sql) or die("ajax-grid-data.php: get PO");
	
}

$data = array();
while( $row=db_fetch_array($query) ) {  // preparing an array
	$nestedData=array(); 

	$nestedData[] = $row["UNIT"];
    $nestedData[] = $row["CIF"];
	$nestedData[] = $row["CUST_SHORT_NAME"];
	$nestedData[] = tgl_indo ($row["BIRTH_DATE"]);
    $nestedData[] = $row["HADDRESS1"];
    $nestedData[] = $row["MOBILE_PHONE"];
    $nestedData[] = '<td><center>
                     <a href="../transaksi/edit.php?kd='.$row['CIF'].'"  data-toggle="tooltip" title="Edit" class="btn btn-sm btn-warning"> <i class="menu-icon icon-pencil"></i> </a>
                     <a href="../view/master_fund.php?hal=hapus&kd='.$row['CIF'].'"  data-toggle="tooltip" title="Hapus" class="btn btn-sm btn-danger"> <i class="menu-icon icon-trash"></i> </a>
				     </center></td>';		
	
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
