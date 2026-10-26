<?php require_once __DIR__ . '/../../koneksi_db.php';
/* Database connection start */
$servername = "localhost";
$username = "root";
$password = "anaskorti";
$dbname = "nur_insani";
include "../../indo_tgl.php";

$conn = db_ni() or die("Connection failed: " . mysqli_connect_error());

/* Database connection end */


// storing  request (ie, get/post) global array to a variable  
$requestData= $_REQUEST;
$bussdate=$_REQUEST;


$columns = array( 
// datatable column index  => database column name
	0 => 'unit',
    1 => 'norek', 
	2 => 'bilyet',
	3 => 'CUST_SHORT_NAME',
	4 => 'nominal',
	5 => 'tenor',
	6 => 'rasio',
	7 => 'eq_rate',
	8 => 'cara_hitung',
	9 => 'star_date',
	7 => 'maturity_date',
	8 => 'norek'

);

// getting total number records without any search
$sql = "SELECT norek,bilyet,deposito.unit, deposan.no, deposan.CUST_SHORT_NAME,nominal,tenor,star_date,maturity_date, rasio, eq_rate,cara_hitung";
$sql.=" FROM deposito left join deposan on deposito.cif = deposan.CIF";
$query=db_query($conn, $sql) or die("ajax-grid-data_trns.php: get InventoryItems");
$totalData = db_num_rows($query);
$totalFiltered = $totalData;  // when there is no search parameter then total number rows = total number filtered rows.


if( !empty($requestData['search']['value']) ) {
	// if there is a search parameter
	$sql = "SELECT deposito.unit,bilyet,deposan.CIF,norek,CUST_SHORT_NAME,nominal,tenor,star_date,maturity_date, rasio, eq_rate,cara_hitung ";
	$sql.=" FROM deposito left join deposan on deposito.cif = deposan.CIF";
	$sql.=" WHERE norek LIKE '".$requestData['search']['value']."%' ";    // $requestData['search']['value'] contains search parameter
	$sql.=" OR bilyet LIKE '".$requestData['search']['value']."%' ";
	$sql.=" OR CUST_SHORT_NAME LIKE '".$requestData['search']['value']."%' ";
	$sql.=" OR nominal LIKE '".$requestData['search']['value']."%' ";
	$sql.=" OR tenor LIKE '".$requestData['search']['value']."%' ";
	$sql.=" OR rasio LIKE '".$requestData['search']['value']."%' ";
	$sql.=" OR eq_rate LIKE '".$requestData['search']['value']."%' ";
	$sql.=" OR cara_hitung LIKE '".$requestData['search']['value']."%' ";

$sql.=" OR star_date LIKE '".$requestData['search']['value']."%' ";
$sql.=" OR maturity_date LIKE '".$requestData['search']['value']."%' ";
  	$query=db_query($conn, $sql) or die("../view/ajax-grid-data_trns.php: get PO");
	$totalFiltered = db_num_rows($query); // when there is a search parameter then we have to modify total number filtered rows as per search result without limit in the query 

	$sql.=" ORDER BY ". $columns[$requestData['order'][0]['column']]."   ".$requestData['order'][0]['dir']."   LIMIT ".$requestData['start']." ,".$requestData['length']."   "; // $requestData['order'][0]['column'] contains colmun index, $requestData['order'][0]['dir'] contains order such as asc/desc , $requestData['start'] contains start row number ,$requestData['length'] contains limit length.
	$query=db_query($conn, $sql) or die("../view/ajax-grid-data_trns.php: get PO"); // again run query with limit
	
} else {	

	$sql = "SELECT norek,bilyet,deposito.unit, deposan.no, deposan.CUST_SHORT_NAME,nominal,tenor,star_date,maturity_date, rasio, eq_rate,cara_hitung ";
	$sql.=" FROM deposito left join deposan on deposito.cif = deposan.CIF";
	$sql.=" ORDER BY ". $columns[$requestData['order'][0]['column']]."   ".$requestData['order'][0]['dir']."   LIMIT ".$requestData['start']." ,".$requestData['length']."   ";
	$query=db_query($conn, $sql) or die("ajax-grid-data_trns.php: get PO");
	
}

$data = array();
while( $row=db_fetch_array($query) ) {  // preparing an array
	$nestedData=array(); 

	$nestedData[] = $row["unit"];
    $nestedData[] = $row["norek"];
	$nestedData[] = $row["bilyet"];
	$nestedData[] = $row["CUST_SHORT_NAME"];
	$nestedData[] = number_format (compat_num($row["nominal"]));
	$nestedData[] = $row["tenor"];
	$nestedData[] = $row["rasio"];
	$nestedData[] = $row["eq_rate"];
	$nestedData[] = $row["cara_hitung"];
	$nestedData[] = tgl_indo ($row["star_date"]);
$nestedData[] = tgl_indo ($row["maturity_date"]);
	    $nestedData[] = '<td><center>
					 <a href="../transaksi/edit_nisbah.php?kd='.$row['norek'].'"  data-toggle="tooltip" title="Edit" class="btn btn-sm btn-warning"> <i class="menu-icon icon-pencil"></i> </a>
                     <a href="../view/master_trans.php?hal=hapus&kd='.$row['norek'].'"  data-toggle="tooltip" title="Break" class="btn btn-sm btn-danger"> <i class="menu-icon icon-trash"></i> </a>
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
