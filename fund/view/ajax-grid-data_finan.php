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
	0 => 'code_param',
    1 => 'nomi_murab', 
	2 => 'marigin_murab',
	3 => 'buss_date'
   
);

// getting total number records without any search
$sql = "SELECT code_param, nomi_murab, marigin_murab, buss_date ";
$sql.=" FROM param_finan";
$query=db_query($conn, $sql) or die("ajax-grid-data_finan.php: get InventoryItems");
$totalData = db_num_rows($query);
$totalFiltered = $totalData;  // when there is no search parameter then total number rows = total number filtered rows.


if( !empty($requestData['search']['value']) ) {
	// if there is a search parameter
	$sql = "SELECT code_param, nomi_murab, marigin_murab, buss_date";
	$sql.=" FROM param_finan";
	$sql.=" WHERE code_param LIKE '".$requestData['search']['value']."%' ";    // $requestData['search']['value'] contains search parameter
	$sql.=" OR nomi_murab LIKE '".$requestData['search']['value']."%' ";
	$sql.=" OR marigin_murab LIKE '".$requestData['search']['value']."%' ";
    $sql.=" OR buss_date LIKE '".$requestData['search']['value']."%' ";
   
	$query=db_query($conn, $sql) or die("../view/ajax-grid-data_finan.php: get PO");
	$totalFiltered = db_num_rows($query); // when there is a search parameter then we have to modify total number filtered rows as per search result without limit in the query 

	$sql.=" ORDER BY ". $columns[$requestData['order'][0]['column']]."   ".$requestData['order'][0]['dir']."   LIMIT ".$requestData['start']." ,".$requestData['length']."   "; // $requestData['order'][0]['column'] contains colmun index, $requestData['order'][0]['dir'] contains order such as asc/desc , $requestData['start'] contains start row number ,$requestData['length'] contains limit length.
	$query=db_query($conn, $sql) or die("../view/ajax-grid-data_finan.php: get PO"); // again run query with limit
	
} else {	

	$sql = "SELECT code_param, nomi_murab, marigin_murab, buss_date";
	$sql.=" FROM param_finan";
	$sql.=" ORDER BY ". $columns[$requestData['order'][0]['column']]."   ".$requestData['order'][0]['dir']."   LIMIT ".$requestData['start']." ,".$requestData['length']."   ";
	$query=db_query($conn, $sql) or die("ajax-grid-data_finan.php: get PO");
	
}

$data = array();
while( $row=db_fetch_array($query) ) {  // preparing an array
	$nestedData=array(); 

	$nestedData[] = $row["code_param"];
    $nestedData[] = number_format (compat_num($row["nomi_murab"]));
	$nestedData[] = number_format (compat_num($row["marigin_murab"]));
	$nestedData[] = tgl_indo ($row["buss_date"]);
   
    $nestedData[] = '<td><center>
                     <a href="../transaksi/edit.php?kd='.$row['no'].'"  data-toggle="tooltip" title="Edit" class="btn btn-sm btn-warning"> <i class="menu-icon icon-pencil"></i> </a>
                     <a href="../view/view_finan.php?hal=hapus&kd='.$row['no'].'"  data-toggle="tooltip" title="Hapus" class="btn btn-sm btn-danger"> <i class="menu-icon icon-trash"></i> </a>
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
