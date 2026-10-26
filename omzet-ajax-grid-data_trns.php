<?php require_once __DIR__ . '/koneksi_db.php';
session_start();
ob_start();
error_reporting(0);
$userid=$_SESSION['userid'];
$tgl_system=$_SESSION['stgl_system'];
//cek apakah user sudah login

if(!isset($_SESSION['userid'])){
   header("location:index.html");
}
/* Database connection start */
//$servername = "192.168.88.144";
$servername   ="185.201.9.210";
$username = "adminni";
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
	0 => 'unit',
    	1 => 'tgl_tagih',
    	2 => 'nama_kel', 
	3 => 'cif',
	4 => 'nama',
	5 => 'omzet',

	

);

// getting total number records without any search
$sql = "SELECT branch.unit,tanggal as tgl_tagih,kode_kel.nama_kel as nama_kel,omzet.cif as cif,loan.Cist_Short_name as nama,nominal as omzet";
$sql.=" FROM cs left join loan on loan.cif = omzet.cif ";
$sql.=" left join branch on loan.kode_branch = loan.unit ";
$sql.=" left join kelompoak on kelompok.code_kel = loan.code_kel where loan.unit ='$userid'";

$query=db_query($conn, $sql) or die("ajax-grid-data_trns.php: get InventoryItems");
$totalData = db_num_rows($query);
$totalFiltered = $totalData;  // when there is no search parameter then total number rows = total number filtered rows.


if( !empty($requestData['search']['value']) ) {
	// if there is a search parameter
	$sql = "SELECT unit,tagihan_cs.tgl_tagih as tgl_tagih,cs.nama_kel as nama_kel,tagihan_cs.cif as cif,cs.nama as nama,tagihan_cs.bulat as bulat,nominal, status_trans";
    $sql.=" FROM cs left join tagihan_cs on cs.cif = tagihan_cs.cif ";
    $sql.=" WHERE unit  LIKE '".$requestData['search']['value']."%'  ";  
    $sql.=" OR cs.nama_kel LIKE '".$requestData['search']['value']."%'  ";
	$sql.=" OR cs.nama LIKE '".$requestData['search']['value']."%'  ";    // $requestData['search']['value'] contains search parameter
	$sql.=" OR tagihan_cs.cif  LIKE '".$requestData['search']['value']."%'  ";
    $sql.=" OR status_trans  LIKE '".$requestData['search']['value']."%'  ";
	
	$query=db_query($conn, $sql) or die("mob-ajax-grid-data_trns.php: get PO");
	$totalFiltered = db_num_rows($query); // when there is a search parameter then we have to modify total number filtered rows as per search result without limit in the query 

	$sql.=" ORDER BY ". $columns[$requestData['order'][0]['column']]."   ".$requestData['order'][0]['dir']."   LIMIT ".$requestData['start']." ,".$requestData['length']."   "; // $requestData['order'][0]['column'] contains colmun index, $requestData['order'][0]['dir'] contains order such as asc/desc , $requestData['start'] contains start row number ,$requestData['length'] contains limit length.
	$query=db_query($conn, $sql) or die("mob-ajax-grid-data_trns.php: get PO"); // again run query with limit
	
} else {	

	$sql = "SELECT unit,tagihan_cs.tgl_tagih as tgl_tagih,cs.nama_kel as nama_kel,tagihan_cs.cif as cif,cs.nama as nama,tagihan_cs.bulat as bulat,nominal, status_trans";
$sql.=" FROM cs left join tagihan_cs on cs.cif = tagihan_cs.cif where unit ='$userid'" ;

	$sql.=" ORDER BY ". $columns[$requestData['order'][0]['column']]."   ".$requestData['order'][0]['dir']."   LIMIT ".$requestData['start']." ,".$requestData['length']."   ";
	$query=db_query($conn, $sql) or die("mob-ajax-grid-data_trns.php: get PO");
	
}

$data = array();
while( $row=db_fetch_array($query) ) {  // preparing an array
	$nestedData=array(); 

	$nestedData[] = $row["unit"];
    $nestedData[] = tgl_indo($row["tgl_tagih"]);
    $nestedData[] = $row["nama_kel"];
    $nestedData[] = $row["cif"];
	$nestedData[] = $row["nama"];	
	$nestedData[] = number_format (compat_num($row["bulat"]));
    $nestedData[] = number_format (compat_num($row["nominal"]));
    $nestedData[] = $row["status_trans"];	
    
	    $nestedData[] = '<td><center>
                     <a href="mob_edit.php?cif='.$row['cif'].'& type='.$row['nama'].'"  data-toggle="tooltip" title="Edit" class="btn btn-sm btn-warning"> <i class="menu-icon icon-pencil"></i> </a>

                     
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
