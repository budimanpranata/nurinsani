<?php require_once __DIR__ . '/../koneksi_db.php';
session_start();

$userid=$_SESSION['userid'];

if(!isset($_SESSION['userid'])){
   header("location:index.html");
 exit; }
if($_SESSION['level']!="unit"){
   header("location:index.html");
 exit; }

$connect = db_ni();
$columns = array('unit','tanggal_transaksi','kode_transaksi','kode_rekening','nama_rek', 'keterangan_transaksi', 'debet','kredit');

$user=db_query($connect,"select kode_branch from user where userid='$userid'");
			
			while($id_user=db_fetch_array($user)){
			
				$unit=$id_user['kode_branch'];
			}

$query = "SELECT unit,tanggal_transaksi,kode_transaksi,kode_rekening,nama_rek,keterangan_transaksi,debet,kredit FROM tabel_transaksi inner join coa on coa.kode_rek = tabel_transaksi.kode_rekening WHERE unit='$unit' AND ";

if($_POST["is_date_search"] == "yes")
{
 $query .= 'tanggal_transaksi BETWEEN "'.$_POST["start_date"].'" AND "'.$_POST["end_date"].'" AND ';
}

if(isset($_POST["search"]["value"]))
{
 $query .= '
  (unit LIKE "%'.$_POST["search"]["value"].'%" 
  OR tanggal_transaksi LIKE "%'.$_POST["search"]["value"].'%"
  OR kode_transaksi LIKE "%'.$_POST["search"]["value"].'%" 
  OR kode_rekening LIKE "%'.$_POST["search"]["value"].'%" 
  OR nama_rek LIKE "%'.$_POST["search"]["value"].'%" 
  OR keterangan_transaksi LIKE "%'.$_POST["search"]["value"].'%"
  OR debet LIKE "%'.$_POST["search"]["value"].'%"
  OR kredit LIKE "%'.$_POST["search"]["value"].'%")
 ';
}

if(isset($_POST["order"]))
{
 $query .= 'ORDER BY '.$columns[$_POST['order']['0']['column']].' '.$_POST['order']['0']['dir'].' 
 ';
}
else
{
 $query .= 'ORDER BY id_transaksi DESC ';
}

$query1 = '';

if($_POST["length"] != -1)
{
 $query1 = 'LIMIT ' . $_POST['start'] . ', ' . $_POST['length'];
}

$number_filter_row = db_num_rows(db_query($connect, $query));

$result = db_query($connect, $query . $query1);

$data = array();

while($row = db_fetch_array($result))
{
 $sub_array = array();
 $sub_array[] = $row["unit"];
 $sub_array[] = $row["tanggal_transaksi"];
 $sub_array[] = $row["kode_transaksi"];
 $sub_array[] = $row["kode_rekening"];
 $sub_array[] = $row["nama_rek"];
 $sub_array[] = $row["keterangan_transaksi"];
 $sub_array[] = number_format (compat_num($row["debet"]));
 $sub_array[] = number_format (compat_num($row["kredit"]));
 $data[] = $sub_array;
}

function get_all_data($connect)
{
 $query = "SELECT unit,tanggal_transaksi,kode_transaksi,kode_rekening,nama_rek,keterangan_transaksi,debet,kredit FROM tabel_transaksi inner join coa on coa.kode_rek = tabel_transaksi.kode_rekening WHERE unit='$unit'";
 $result = db_query($connect, $query);
 return db_num_rows($result);
}

$output = array(
 "draw"    => intval($_POST["draw"]),
 "recordsTotal"  =>  get_all_data($connect),
 "recordsFiltered" => $number_filter_row,
 "data"    => $data
);

echo json_encode($output);

?>
