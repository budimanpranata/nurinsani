<?php require_once __DIR__ . '/koneksi_db.php';
session_start();
require_once __DIR__ . '/inc/cek_login.php';
wajib_level('unit', 'json');
ob_start();
error_reporting(0);
$userid=$_SESSION['userid'];
$tgl_system=$_SESSION['stgl_system'];
//cek apakah user sudah login

if(!isset($_SESSION['userid'])){
   header("location:index.html");
 exit; }
/* Database connection start */
// host/user/password mobcol hanya di koneksi_db.php (variabel lama yang tidak terpakai dibuang 2026-11-05)
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
$sql.=" FROM bukti_setor left join ao on ao.cao = bukti_setor.cao where kode_unit = ?";
$query=db_hasil_di($conn, $sql, 's', (string) $userid) or die("ajax-grid-data_trns.php: get InventoryItems");
$totalData = db_num_rows($query);
$totalFiltered = $totalData;  // when there is no search parameter then total number rows = total number filtered rows.

// 2026-11-19 (perintah user): urutan/halaman dibatasi ke nilai sah (dulu ditempel langsung ke SQL)
$kol_urut = isset($requestData['order'][0]['column'], $columns[(int) $requestData['order'][0]['column']]) ? $columns[(int) $requestData['order'][0]['column']] : 'tgl_setor';
$arah_urut = (isset($requestData['order'][0]['dir']) && strtolower((string) $requestData['order'][0]['dir']) === 'desc') ? 'desc' : 'asc';
$batas = " ORDER BY " . $kol_urut . "   " . $arah_urut . "   LIMIT " . max(0, (int) (isset($requestData['start']) ? $requestData['start'] : 0)) . " ," . max(0, (int) (isset($requestData['length']) ? $requestData['length'] : 10)) . "   ";

if( !empty($requestData['search']['value']) ) {
	// 2026-11-19 (perintah user, guard unit mobcol): pencarian dulu tanpa filter unit (bukti setor unit lain tampil
	// & bisa diunduh) dan nilai cari ditempel langsung ke SQL (SQL injection). Kini prepared statement + hanya unit user.
	$cari = (string) $requestData['search']['value'] . '%';
	$sql = "SELECT tgl_setor,kode_unit,nama_ao,bank,name,image";
$sql.=" FROM bukti_setor left join ao on ao.cao = bukti_setor.cao";
    $sql.=" WHERE kode_unit = ? AND (kode_unit LIKE ? OR nama_ao LIKE ? OR bank LIKE ? OR tgl_setor LIKE ?) ";

	$query=db_hasil_di($conn, $sql, 'sssss', (string) $userid, $cari, $cari, $cari, $cari) or die("mob-ajax-grid-data_bank.php: get PO");
	$totalFiltered = db_num_rows($query); // when there is a search parameter then we have to modify total number filtered rows as per search result without limit in the query

	$query=db_hasil_di($conn, $sql . $batas, 'sssss', (string) $userid, $cari, $cari, $cari, $cari) or die("mob-ajax-grid-data_bank.php: get PO"); // again run query with limit

} else {

    $sql = "SELECT tgl_setor,kode_unit,nama_ao,bank,name,image";
    $sql.=" FROM bukti_setor left join ao on ao.cao = bukti_setor.cao where kode_unit = ?";

	$query=db_hasil_di($conn, $sql . $batas, 's', (string) $userid) or die("mob-ajax-grid-data_bank.php: get PO");

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
