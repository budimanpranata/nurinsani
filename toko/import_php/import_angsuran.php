<?php require_once __DIR__ . '/../../koneksi_db.php';


/*
-- Source Code from My Notes Code (www.mynotescode.com)
-- 
-- Follow Us on Social Media
-- Facebook : http://facebook.com/mynotescode/
-- Twitter  : http://twitter.com/code_notes
-- Google+  : http://plus.google.com/118319575543333993544
--
-- Terimakasih telah mengunjungi blog kami.
-- Jangan lupa untuk Like dan Share catatan-catatan yang ada di blog kami.
*/

// Load file koneksi.php
$db_host = "localhost";
$db_user = "root";
$db_pass = "anaskorti";
$db_name = "nur_insani";


$koneksi = db_ni();

if(mysqli_connect_errno()){
	echo 'Gagal melakukan koneksi ke Database : '.mysqli_connect_error();
}

if(isset($_POST['import'])){ // Jika user mengklik tombol Import
	$nama_file_baru = 'data2.xlsx';
	
	
	
	?>
<div id="progress"  style="align:center; width:500px;border:1px solid #ccc;"></div>
<div id="info"></div>
<?php
	// Load librari PHPExcel nya
	require_once 'PHPExcel/PHPExcel.php';
	
	$excelreader = new PHPExcel_Reader_Excel2007();
	$loadexcel = $excelreader->load('tmp/'.$nama_file_baru); // Load file excel yang tadi diupload ke folder tmp
	$sheet = $loadexcel->getActiveSheet()->toArray(null, true, true ,true);
	
	// Buat query Insert
	
	$numrow = 1;
	$nol=0;
	foreach($sheet as $row){
	
			$unit = $row['A'];
			$tgl = $row['B'];
			$id_member = $row['C'];
			$nik = $row['D'];
			$nama = mysqli_real_escape_string($koneksi,$row['E']);
			$angsuran = $row['F'];
			$setoran = $row['G'];
			$selisih = mysqli_real_escape_string($koneksi,$row['H']);
			$kode_trans="BU/$unit-$tgl";
			$ket ="Setoran Musawamah $nama";
			$keterangan_transaksi="Setoran Musawamah $nama";
			
	
		//var_dump($selisih[8]);
			
			
		// Cek jika semua data tidak diisi
		if(empty($unit) && empty($tgl) && empty($id_member) && empty($nik) && empty($nama))
			continue; // Lewat data pada baris ini (masuk ke looping selanjutnya / baris selanjutnya)
		
	
		if($numrow > 1){
			// Proses simpan ke Database
			
			db_query($koneksi,"insert into temp_angsuran(unit,tanggal,id_member,nik, nama,angsuran,setoran,selisih)
                    values('$unit','$tgl','$id_member','$nik', '$nama','$angsuran','$setoran','$selisih')");
            
	
				
		
		}
		$numrow++; // Tambah 1 setiap kali looping'
		for ($i=11; $i<=$numrow; $i++)
{	
		$barisreal = $numrow-1;
        $k = $i-1;
        
// menghitung persentase progress
        $percent = intval($k/$barisreal * 100)."%";

// mengupdate progress
        echo '<script language="javascript">
        document.getElementById("progress").innerHTML="<div style=\"width:'.$percent.'; background-color:lightblue\">&nbsp;</div>";
        document.getElementById("info").innerHTML="'.$k.' data berhasil diimport ('.$percent.' selesai).";
        </script>';
		
		
}

	}
echo "<script> alert ('Proses Posting Selesai!')</script>";

						echo "<script language='javascript'>document.location.href='data_toko_angsuran.php'</script>";

}
?>
