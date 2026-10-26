<?php require_once __DIR__ . '/../koneksi_db.php';
$hostname = "localhost";
$database = "nur_insani";
$username = "root";
$password = "anaskorti";
$connect = db_ni();
// script cek koneksi   
if (!$connect) {
    die("Koneksi Tidak Berhasil: " . mysqli_connect_error());
}


   if(function_exists($_GET['function'])) {
         $_GET['function']();
      }   
   
   function insert_transaksi()
      {
         global $connect;   
         $check = array( ' unit' => '',' id_transaksi' => '', 'kode_transaksi' => '', 'kode_rekening' => '', 'tanggal_transaksi' => '', 'jenis_transaksi' => '', 'keterangan_transaksi' => '', 'debet' => '', 'kredit' => '', 'tanggal_posting' => '', 'keterangan_posting' => '', 'id_admin' => '');
         $check_match = count(array_intersect_key($_POST, $check));

         
       $ket =mysqli_real_escape_string($connect,$_POST['keterangan_transaksi']);
         
               $result = db_query($connect, "INSERT INTO tabel_transaksi_cms SET
               unit = '$_POST[unit]',
               id_transaksi = '$_POST[id_transaksi]',
               kode_transaksi = '$_POST[kode_transaksi]',
               kode_rekening = '$_POST[kode_rekening]',
               tanggal_transaksi = '$_POST[tanggal_transaksi]',
               jenis_transaksi = '$_POST[jenis_transaksi]',
               keterangan_transaksi = '$ket',
               debet = '$_POST[debet]',
               kredit = '$_POST[kredit]',
               tanggal_posting = '$_POST[tanggal_posting]',
               keterangan_posting = '$_POST[keterangan_posting]',
               id_admin = '$_POST[id_admin]'");
               
               if($result)
               {
                  $response=array(
                     'status' => 1,
                     'message' =>'Insert Success'
                  );
               }
               else
               {
                  $response=array(
                     'status' => 0,
                     'message' =>'Insert Failed.'
                  );
               }
        
         header('Content-Type: application/json');
         echo json_encode($response);
      }
   
 ?>
