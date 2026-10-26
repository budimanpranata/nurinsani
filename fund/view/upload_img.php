<?php require_once __DIR__ . '/../../php8_compat.php';
session_start();
include "../../conn2.php";
$koneksi=open_connection2();
$nopen='1212121';
$nama_photo=$_FILES['userImage']['name'];
if(is_array($_FILES)) {	
foreach ($_FILES['userImage']['name'] as $name => $value){
if(is_uploaded_file($_FILES['userImage']['tmp_name'][$name])) {
$sourcePath = $_FILES['userImage']['tmp_name'][$name];
$ext = explode('.', basename($_FILES['userImage']['name'][$name]));   // Explode file name from dot(.)
$file_extension = end($ext); // Store extensions in the variable.
$rnd=date('His');
$nama= $rnd."_".$nopen;				
$nama_file_upload= $nama . "." . $ext[count($ext) - 1][$name];
$targetPath = "../../load/uploads/".$nama_file_upload;
//$sourcePath = $_FILES['userImage']['tmp_name'];
$alamatfile=$target_path[$name];

if(move_uploaded_file($sourcePath, $targetPath)) {
$query=mysql_query("insert into image(name,NOPEN,image) values('$nama_photo','$nopen','$nama_file_upload')");

?>
<img src="<?php echo $targetPath; ?>" width="150px" height="180px" />
<?php
}else {
	echo "Gagal Upload";
}

}}}
?>