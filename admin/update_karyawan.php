<?php require_once __DIR__ . '/../php8_compat.php'; require_once __DIR__ . '/cek_admin.php';
include "connect.php";
$koneksi=open_connection();
if(isset($_POST['submit'])){
	$dep=addslashes(htmlentities(ucwords($_POST['dep'])));
	$nama=addslashes(htmlentities(ucwords($_POST['nama'])));
	$nik=addslashes(htmlentities(ucwords($_POST['nik'])));
	$tgl_lahir=addslashes(htmlentities(ucwords($_POST['tgl_lahir'])));
	$alamat=addslashes(htmlentities(ucwords($_POST['alamat'])));
	$tmt_sk=addslashes(htmlentities(ucwords($_POST['tmt_sk'])));
	$jabatan=addslashes(htmlentities(ucwords($_POST['jabatan'])));
	$status_kar=addslashes(htmlentities(ucwords($_POST['status_kar'])));
	$vendor=addslashes(htmlentities(ucwords($_POST['vendor'])));
	$atasan1=addslashes(htmlentities(ucwords($_POST['atasan1'])));
	$atasan2=addslashes(htmlentities(ucwords($_POST['atasan2'])));
	$branch=addslashes(htmlentities(ucwords($_POST['branch'])));
	$status=addslashes(htmlentities(ucwords($_POST['status'])));
	$tgl_status=addslashes(htmlentities(ucwords($_POST['tgl_status'])));

	$nama_photo=$_FILES['photo']['name'];
	$type = $_FILES['photo']['type'];
	$ukuran=$_FILES['photo']['size'];
	
	$photo_lama=$_POST['photo_lama'];
	$nama_photo=$_FILES['photo']['name'];
	
	
	if(empty($nama) || empty($nik) || empty($jabatan)){
		echo "Maaf, Form belum lengkap!!";
		?><a href=simulasi/lead/edit_karyawan.php?nik=<?php echo $nik;?>> Silahkan ulangi</a><?php
	
		}
		else{
		
			if(empty($_FILES['photo']['name'])){
				$nama_file_upload=$photo_lama;
				$type=$_POST['type'];
				$ukuran=$_POST['ukuran'];				
				$query=mysql_query("update karyawan set dep='$dep',nama='$nama',nik='$nik',tgl_lahir='$tgl_lahir',alamat='$alamat',tmt_sk='$tmt_sk',jabatan='$jabatan',status_kar='$status_kar',vendor='$vendor',atasan1='$atasan1',atasan2='$atasan2',branch='$branch',status='$status',tgl_status='$tgl_status',photo='$nama_file_upload',ukuran='$ukuran',type='$type' where nik='$nik'");
			}else{				
				$type=$_FILES['photo']['type'];
				$ukuran=$_FILES['photo']['size'];		
				$uploaddir='./photo/';
				$rnd=date('His');				
				$nama_file_upload=$rnd.'-'.$nama_photo;
				$alamatfile=$uploaddir.$nama_file_upload;
				
				if($type != "image/gif"  &&  $type != "image/jpg"  && $type != "image/jpeg" && $type != "image/png") {
						echo "File Yang Di izinkan Hanya jpg,jpeg,png,gif!!</a>";
						?><a href=../kanwiljabar/edit_karyawan.php?nik=<?php echo $nik;?>> Silahkan ulangi</a><?php
				}else{
					if($ukuran>1000000){
						echo "File Yang Di izinkan Hanya berukuran kurang dari 1MB!!</a>";
						?><a href=../kanwiljabar/edit_karyawan.php?nik=<?php echo $nik;?>> Silahkan ulangi</a><?php
					}else{				
						unlink("./photo/".$photo_lama);
						$upload=move_uploaded_file($_FILES['photo']['tmp_name'],$alamatfile);
						$query=mysql_query("update karyawan set dep='$dep',nama='$nama',nik='$nik',tgl_lahir='$tgl_lahir',alamat='$alamat',tmt_sk='$tmt_sk',jabatan='$jabatan',status_kar='$status_kar',vendor='$vendor',atasan1='$atasan1',$atasan2='atasan2',branch='$branch',status='$status',tgl_status='$tgl_status',photo='$nama_file_upload',ukuran='$ukuran',type='$type' where nik='$nik'");
					}
				}
			}
			
			if($query){
				echo "Data Anda berhasil disimpan <a href=input_karyawan.php> Input kembali</a>";
			}else{
				echo mysql_query();
				}
			}
				
}else{
	unset($_POST['submit']);
}
?>