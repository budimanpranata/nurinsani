<?php require_once __DIR__ . '/php8_compat.php';


$con = mysqli_connect("192.168.88.44","api","","insan");
if (mysqli_connect_errno()) {
	echo "Failed to connect to MySQL: " . mysqli_connect_error();
	exit;

}else{
echo "sukses";
}

