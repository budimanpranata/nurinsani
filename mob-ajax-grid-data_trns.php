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
	0 => 'unit',
    1 => 'tgl_tagih',
    2 => 'nama_kel', 
	3 => 'cif',
	4 => 'nama',
	5 => 'bulat',
    6 => 'nominal',
    7 => 'status_trans',

	

);

// getting total number records without any search
$sql = "SELECT unit,tagihan_cs.tgl_tagih as tgl_tagih,cs.nama_kel as nama_kel,tagihan_cs.cif as cif,cs.nama as nama,tagihan_cs.bulat as bulat,nominal, status_trans";
$sql.=" FROM cs left join tagihan_cs on cs.cif = tagihan_cs.cif where unit = ?";
$query=db_hasil_di($conn, $sql, 's', (string) $userid) or die("ajax-grid-data_trns.php: get InventoryItems");
$totalData = db_num_rows($query);
$totalFiltered = $totalData;  // when there is no search parameter then total number rows = total number filtered rows.


// 2026-11-16 (perintah user): urutan/halaman dibatasi ke nilai sah (dulu ditempel langsung ke SQL)
$kol_urut = isset($requestData['order'][0]['column'], $columns[(int) $requestData['order'][0]['column']]) ? $columns[(int) $requestData['order'][0]['column']] : 'unit';
$arah_urut = (isset($requestData['order'][0]['dir']) && strtolower((string) $requestData['order'][0]['dir']) === 'desc') ? 'desc' : 'asc';
$batas = " ORDER BY " . $kol_urut . "   " . $arah_urut . "   LIMIT " . max(0, (int) (isset($requestData['start']) ? $requestData['start'] : 0)) . " ," . max(0, (int) (isset($requestData['length']) ? $requestData['length'] : 10)) . "   ";

if( !empty($requestData['search']['value']) ) {
	// 2026-11-16 (perintah user): pencarian dulu tanpa filter unit (data unit lain tampil) dan nilai cari ditempel
	// langsung ke SQL (SQL injection). Kini prepared statement + tetap hanya unit user login.
	$cari = (string) $requestData['search']['value'] . '%';
	$sql = "SELECT unit,tagihan_cs.tgl_tagih as tgl_tagih,cs.nama_kel as nama_kel,tagihan_cs.cif as cif,cs.nama as nama,tagihan_cs.bulat as bulat,nominal, status_trans";
    $sql.=" FROM cs left join tagihan_cs on cs.cif = tagihan_cs.cif ";
    $sql.=" WHERE unit = ? AND (unit LIKE ? OR cs.nama_kel LIKE ? OR cs.nama LIKE ? OR tagihan_cs.cif LIKE ? OR status_trans LIKE ?) ";

	$query=db_hasil_di($conn, $sql, 'ssssss', (string) $userid, $cari, $cari, $cari, $cari, $cari) or die("mob-ajax-grid-data_trns.php: get PO");
	$totalFiltered = db_num_rows($query); // when there is a search parameter then we have to modify total number filtered rows as per search result without limit in the query

	$query=db_hasil_di($conn, $sql . $batas, 'ssssss', (string) $userid, $cari, $cari, $cari, $cari, $cari) or die("mob-ajax-grid-data_trns.php: get PO"); // again run query with limit

} else {

	$sql = "SELECT unit,tagihan_cs.tgl_tagih as tgl_tagih,cs.nama_kel as nama_kel,tagihan_cs.cif as cif,cs.nama as nama,tagihan_cs.bulat as bulat,nominal, status_trans";
$sql.=" FROM cs left join tagihan_cs on cs.cif = tagihan_cs.cif where unit = ?" ;

	$query=db_hasil_di($conn, $sql . $batas, 's', (string) $userid) or die("mob-ajax-grid-data_trns.php: get PO");

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
