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
	0 => 'sunit',
    1 => 'snorek', 
	2 => 'scif',
	3 => 'CUST_SHORT_NAME',
	4 => 'type',
    5 => 'DEBIT',
6 => 'KREDIT'
	

);

// getting total number records without any search
$sql = "SELECT snorek,scif,sunit, CUST_SHORT_NAME,type, sum(KREDIT - DEBIT) as SALDO";
$sql.=" FROM simpanan_kp left join anggota on simpanan_kp.scif = anggota.CIF GROUP BY SNOREK,TYPE";
$query=db_query($conn, $sql) or die("ajax-grid-data_trns.php: get InventoryItems");
$totalData = db_num_rows($query);
$totalFiltered = $totalData;  // when there is no search parameter then total number rows = total number filtered rows.


if( !empty($requestData['search']['value']) ) {
	// if there is a search parameter
	$sql = "SELECT snorek,scif,sunit, CUST_SHORT_NAME,type, sum(KREDIT - DEBIT) as SALDO";
$sql.=" FROM simpanan_kp left join anggota on simpanan_kp.scif = anggota.CIF ";

	$sql.=" WHERE snorek LIKE '".$requestData['search']['value']."%'  ";    // $requestData['search']['value'] contains search parameter
	$sql.=" OR scif LIKE '".$requestData['search']['value']."%'  ";
	$sql.=" OR CUST_SHORT_NAME LIKE '".$requestData['search']['value']."%' GROUP BY SNOREK,TYPE ";
	$sql.=" OR sunit LIKE '".$requestData['search']['value']."%' ";
	$sql.=" OR type LIKE '".$requestData['search']['value']."%' ";
  	$query=db_query($conn, $sql) or die("../view/ajax-grid-data_trns.php: get PO");
	$totalFiltered = db_num_rows($query); // when there is a search parameter then we have to modify total number filtered rows as per search result without limit in the query 

	$sql.=" ORDER BY ". $columns[$requestData['order'][0]['column']]."   ".$requestData['order'][0]['dir']."   LIMIT ".$requestData['start']." ,".$requestData['length']."   "; // $requestData['order'][0]['column'] contains colmun index, $requestData['order'][0]['dir'] contains order such as asc/desc , $requestData['start'] contains start row number ,$requestData['length'] contains limit length.
	$query=db_query($conn, $sql) or die("../view/ajax-grid-data_trns.php: get PO"); // again run query with limit
	
} else {	

	$sql = "SELECT snorek,scif,sunit, CUST_SHORT_NAME,type, sum(KREDIT - DEBIT) as SALDO";
$sql.=" FROM simpanan_kp left join anggota on simpanan_kp.scif = anggota.CIF GROUP BY SNOREK,TYPE";

	$sql.=" ORDER BY ". $columns[$requestData['order'][0]['column']]."   ".$requestData['order'][0]['dir']."   LIMIT ".$requestData['start']." ,".$requestData['length']."   ";
	$query=db_query($conn, $sql) or die("ajax-grid-data_trns.php: get PO");
	
}

$data = array();
while( $row=db_fetch_array($query) ) {  // preparing an array
	$nestedData=array(); 

	$nestedData[] = $row["sunit"];
    $nestedData[] = $row["snorek"];
	$nestedData[] = $row["scif"];
	$nestedData[] = $row["CUST_SHORT_NAME"];
if ($row["type"] =='04'){
                   $nestedData[] = "POKOK";
                }elseif ($row["type"] =='05') {
                   $nestedData[] = "WAJIB";
                }elseif ($row["type"]=='06') {
                   $nestedData[] = "KHUSUS";
				}else {
                 $nestedData[] = "UMUM";
                }
	
	$nestedData[] = number_format (compat_num($row["SALDO"]));
    
	    $nestedData[] = '<td><center>
                     <a href="../transaksi/input_trns.php?cif='.$row['scif'].'"  data-toggle="tooltip" title="Edit" class="btn btn-sm btn-warning"> <i class="menu-icon icon-pencil"></i> </a>
<a href="../transaksi/rek_koran.php?norek='.$row['snorek'].'"  data-toggle="tooltip" title="Cetak" class="btn btn-sm btn-success"> <i class="menu-icon icon-print"></i> </a>
                     
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
