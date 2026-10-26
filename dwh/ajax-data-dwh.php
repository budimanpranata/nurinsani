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
$bussdate= $_POST['buss_date'];
	$k = db_query ($conn,"select kode_branch AS unit FROM user where userid='$userid'");
			  $kbranch = db_fetch_array($k);
			  $unit = $kbranch['unit'];

$columns = array( 
// datatable column index  => database column name
	0 => 'ket',
	1 => 'os',
    2 => 'noa', 
	3 => 'ytd_os',
	4 => 'mtd_os',
    5 => 'dd_os',
    6 => 'ytd_noa',
    7 => 'mtd_noa',
	8 => 'dd_noa',
    9 => 'ytd_vol',
   10 => 'mtd_vol',
   11 => 'dd_vol'
   
);

// getting total number records without any search
$sql = "SELECT ket, os, noa, ytd_os, mtd_os, dd_os, ytd_noa, mtd_noa,dd_noa, ytd_vol,mtd_vol,dd_vol";
$sql.=" FROM dwh";
$query=db_query($conn, $sql) or die("ajax-data-dwh.php: get Nilai");
$totalData = db_num_rows($query);
$totalFiltered = $totalData;  // when there is no search parameter then total number rows = total number filtered rows.


if( !empty($requestData['search']['value']) ) {
	// if there is a search parameter
$sql = "SELECT ket, os, noa, ytd_os, mtd_os, dd_os, ytd_noa, mtd_noa,dd_noa, ytd_vol,mtd_vol,dd_vol";
$sql.=" FROM dwh";
	$sql.=" WHERE ket LIKE '".$requestData['search']['value']."%' ";    // $requestData['search']['value'] contains search parameter
	$sql.=" OR code_unit LIKE '".$requestData['search']['value']."%' ";
	
    
	$query=db_query($conn, $sql) or die("ajax-data-dwh.php: get Siswa");
	$totalFiltered = db_num_rows($query); // when there is a search parameter then we have to modify total number filtered rows as per search result without limit in the query 

	$sql.=" ORDER BY ". $columns[$requestData['order'][0]['column']]."   ".$requestData['order'][0]['dir']."   LIMIT ".$requestData['start']." ,".$requestData['length']."   "; // $requestData['order'][0]['column'] contains colmun index, $requestData['order'][0]['dir'] contains order such as asc/desc , $requestData['start'] contains start row number ,$requestData['length'] contains limit length.
	$query=db_query($conn, $sql) or die("ajax-data.php: get Nilai"); // again run query with limit
	
} 

elseif($bussdate) {
	// if there is a buss_date parameter
$sql = "SELECT ket, os, noa, ytd_os, mtd_os, dd_os, ytd_noa, mtd_noa,dd_noa, ytd_vol,mtd_vol,dd_vol";
$sql.=" FROM dwh where buss_date='$bussdate'";
	
    
	$query=db_query($conn, $sql) or die("ajax-data-dwh.php: get Siswa");
	$totalFiltered = db_num_rows($query); // when there is a search parameter then we have to modify total number filtered rows as per search result without limit in the query 

	$sql.=" ORDER BY ". $columns[$requestData['order'][0]['column']]."   ".$requestData['order'][0]['dir']."   LIMIT ".$requestData['start']." ,".$requestData['length']."   "; // $requestData['order'][0]['column'] contains colmun index, $requestData['order'][0]['dir'] contains order such as asc/desc , $requestData['start'] contains start row number ,$requestData['length'] contains limit length.
	$query=db_query($conn, $sql) or die("ajax-data.php: get Nilai"); // again run query with limit
	
}else {	

$sql = "SELECT ket, os, noa, ytd_os, mtd_os, dd_os, ytd_noa, mtd_noa,dd_noa, ytd_vol,mtd_vol,dd_vol";
$sql.=" FROM dwh";
	$sql.=" ORDER BY ". $columns[$requestData['order'][0]['column']]."   ".$requestData['order'][0]['dir']."   LIMIT ".$requestData['start']." ,".$requestData['length']."   ";
	$query=db_query($conn, $sql) or die("ajax-data-dwh.php: get Nilai");   
	
}

$data = array();
while( $row=db_fetch_array($query) ) {  // preparing an array
	$nestedData=array(); 
	$nestedData[] = $row["ket"];
	$nestedData[] = number_format (compat_num($row["os"]/1000000));
    $nestedData[] = $row["noa"];
	$nestedData[] = number_format (compat_num($row["ytd_os"]/1000000));
	$nestedData[] = number_format (compat_num($row["mtd_os"]/1000000));
    $nestedData[] = number_format (compat_num($row["dd_os"]/1000000));
    $nestedData[] = $row["ytd_noa"];
    $nestedData[] = $row["mtd_noa"];
	$nestedData[] = $row["dd_noa"];
	$nestedData[] = number_format (compat_num($row["ytd_vol"]/1000000));
	$nestedData[] = number_format (compat_num($row["mtd_vol"]/1000000));
	$nestedData[] = number_format (compat_num($row["dd_vol"]/1000000));
   
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
