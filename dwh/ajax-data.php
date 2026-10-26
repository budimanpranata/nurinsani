<?php require_once __DIR__ . '/../koneksi_db.php';
session_start();
$userid=$_SESSION['userid'];
/* Database connection start */
$servername = "localhost";
$username = "root";
$password = "anaskorti";
$dbname = "nur_insani";

$conn = db_ni() or die("Connection failed: " . mysqli_connect_error());
 include "../indo_tgl.php";
/* Database connection end */


// storing  request (ie, get/post) global array to a variable  
$requestData= $_REQUEST;
	$k = db_query ($conn,"select kode_branch AS unit FROM user where userid='$userid'");
			  $kbranch = db_fetch_array($k);
			  $unit = $kbranch['unit'];

$columns = array( 
// datatable column index  => database column name
	0 => 'unit',
	1 => 'no',
    2 => 'alamat', 
	3 => 'pla',
	4 => 'saldo_pinjaman',
    5 => 'saldo_margin',
    6 => 'tgl_pk',
    7 => 'jatuh_tempo'
   
   
);

// getting total number records without any search
$sql = "SELECT unit, no, alamat, pla, saldo_pinjaman, saldo_margin, tgl_pk, jatuh_tempo";
$sql.=" FROM import_unit where unit ='$unit'";
$query=db_query($conn, $sql) or die("ajax-data.php: get Nilai");
$totalData = db_num_rows($query);
$totalFiltered = $totalData;  // when there is no search parameter then total number rows = total number filtered rows.


if( !empty($requestData['search']['value']) ) {
	// if there is a search parameter
	$sql = "SELECT no, norek, alamat, pla, saldo_pinjaman, saldo_margin, tgl_pk, jatuh_tempo";
	$sql.=" FROM import_unit";
	$sql.=" WHERE no LIKE '".$requestData['search']['value']."%' ";    // $requestData['search']['value'] contains search parameter
	$sql.=" OR alamat LIKE '".$requestData['search']['value']."%' ";
	$sql.=" OR pla LIKE '".$requestData['search']['value']."%' ";
    $sql.=" OR tgl_pk LIKE '".$requestData['search']['value']."%' ";
    $sql.=" OR saldo_margin LIKE '".$requestData['search']['value']."%' ";
    $sql.=" OR jatuh_tempo LIKE '".$requestData['search']['value']."%' ";
    
	$query=db_query($conn, $sql) or die("ajax-data.php: get Siswa");
	$totalFiltered = db_num_rows($query); // when there is a search parameter then we have to modify total number filtered rows as per search result without limit in the query 

	$sql.=" ORDER BY ". $columns[$requestData['order'][0]['column']]."   ".$requestData['order'][0]['dir']."   LIMIT ".$requestData['start']." ,".$requestData['length']."   "; // $requestData['order'][0]['column'] contains colmun index, $requestData['order'][0]['dir'] contains order such as asc/desc , $requestData['start'] contains start row number ,$requestData['length'] contains limit length.
	$query=db_query($conn, $sql) or die("ajax-data.php: get Nilai"); // again run query with limit
	
} else {	

$sql = "SELECT unit, no, alamat, pla, saldo_pinjaman, saldo_margin, tgl_pk, jatuh_tempo";
	$sql.=" FROM import_unit";
	$sql.=" ORDER BY ". $columns[$requestData['order'][0]['column']]."   ".$requestData['order'][0]['dir']."   LIMIT ".$requestData['start']." ,".$requestData['length']."   ";
	$query=db_query($conn, $sql) or die("ajax-data.php: get Nilai");   
	
}

$data = array();
while( $row=db_fetch_array($query) ) {  // preparing an array
	$nestedData=array(); 
	$nestedData[] = $row["unit"];
	$nestedData[] = $row["no"];
    $nestedData[] = $row["alamat"];
	$nestedData[] = $row["pla"];
	$nestedData[] = $row["saldo_pinjaman"];
    $nestedData[] = $row["saldo_margin"];
    $nestedData[] = tgl_indo ($row["tgl_pk"]);
    $nestedData[] = tgl_indo ($row["jatuh_tempo"]);
   
    /** $nestedData[] = '<td><center>
                     <a href="edit-siswa.php?kd='.$row['kode_siswa'].'"  data-toggle="tooltip" title="Edit" class="btn btn-sm btn-primary"> <i class="glyphicon glyphicon-edit"></i> </a>
				     <a href="hapus-siswa.php?kd='.$row['kode_siswa'].'"  data-toggle="tooltip" title="Delete" onclick="return confirm(\'Anda yakin akan menghapus data '.$row['nama_siswa'].'?\')" class="btn btn-sm btn-danger"> <i class="glyphicon glyphicon-trash"> </i> </a>
	                 </center></td>';		**/
	
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
