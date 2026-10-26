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
	$nama_file_baru = 'data.xlsx';
	
	
	
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
	
			$id_outlet = $row['A'];
			$id_trxsale = $row['B'];
			$id_kasir = $row['C'];
			$tgl = $row['D'];
			$jam = $row['E'];
			$id_cust = $row['F'];
			$nm_cust = mysqli_real_escape_string($koneksi,$row['G']);
			$nik = $row['H'];
			$id_pabrik = $row['I'];
			$cash_crdt = $row['J'];
			$total = $row['K'];
			$dompet = $row['L'];
		
		if ($id_outlet=='WH03'){
			$userid='3001';
			$rak_aktiva='1831000';
			$rak_pasiva='2761000';
			}elseif($id_outlet=='SRI002'){
			$userid='3002';
			$rak_aktiva='1832000';
			$rak_pasiva='2762000';
			}elseif($id_outlet=='SRI003'){
			$userid='3003';
			$rak_aktiva='1833000';
			$rak_pasiva='2763000';
			}
			

			$cek_unit= db_fetch_array(db_query($koneksi,"select unit from member WHERE id_cust='$id_cust'"));
			$unit =$cek_unit['unit'];
			
			if ($unit=='3001'){
			$rak_aktiva_unit='1831000';
			$rak_pasiva_unit='2761000';
			}elseif($unit=='3002'){
			$rak_aktiva_unit='1832000';
			$rak_pasiva_unit='2762000';
			}elseif($unit=='3003'){
			$rak_aktiva_unit='1833000';
			$rak_pasiva_unit='2763000';
			}
			
		// Cek jika semua data tidak diisi
		if(empty($id_outlet) && empty($id_trxsale) && empty($id_kasir) && empty($tgl) && empty($jam))
			continue; // Lewat data pada baris ini (masuk ke looping selanjutnya / baris selanjutnya)
		
	
		if($numrow > 1){
			// Proses simpan ke Database
			db_query($koneksi,"insert  into temp_toko(id_outlet,id_trxsale,id_kasir,tgl, jam, id_cust,nm_cust,nik,id_pabrik,cash_crdt,total,dompet,unit)
                    values('$id_outlet','$id_trxsale','$id_kasir','$tgl', '$jam', '$id_cust','$nm_cust','$nik','$id_pabrik','$cash_crdt','$total','$dompet','$userid')");
            
		switch($cash_crdt)
		{
     
     
			case "Credit":
				if ($unit==$userid){
				$jurnal=db_fetch_array(db_query($koneksi,"SELECT max(nomor_jurnal) FROM jurnal_umum where unit='$userid' ORDER BY tanggal_selesai DESC"));
                    $nomor=$jurnal[0]+1;
                    $trns="BU/".$userid .$nomor;
                $keterangan_transaksi="Musawamah an $nm_cust $id_cust";
                $query=db_query($koneksi,"insert into jurnal_umum(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values('$trns','$kode_transaksi','$tgl','$user')");
                $deb_dep=db_query($koneksi,"insert  into tabel_transaksi_toko(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                    values('$userid','$trns','1412000','$tgl','Bukti System','$keterangan_transaksi','$total','$userid')");
                $cr_dep=db_query($koneksi,"insert  into tabel_transaksi_toko(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                    values('$userid','$trns','1482000','$tgl','Bukti System','$keterangan_transaksi','$total' - 5/100*'$total','$userid')");
                 $cr_dep=db_query($koneksi,"insert  into tabel_transaksi_toko(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                    values('$userid','$trns','1422000','$tgl','Bukti System','$keterangan_transaksi','$total' * 5/100,'$userid')");
				}else{
	
				
					$jurnal=db_fetch_array(db_query($koneksi,"SELECT max(nomor_jurnal) FROM jurnal_umum where unit='$userid' ORDER BY tanggal_selesai DESC"));
                    $nomor=$jurnal[0]+1;
                    $trns="BU/".$unit .$nomor;
                $keterangan_transaksi="Musawamah an $nm_cust $id_cust";
                $query=db_query($koneksi,"insert into jurnal_umum(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values('$trns','$kode_transaksi','$tgl','$unit')");
                $deb_dep=db_query($koneksi,"insert  into tabel_transaksi_toko(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                    values('$userid','$trns','$rak_aktiva_unit','$tgl','Bukti System','$keterangan_transaksi','$total','$userid')");
                $cr_dep=db_query($koneksi,"insert  into tabel_transaksi_toko(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                    values('$userid','$trns','1482000','$tgl','Bukti System','$keterangan_transaksi','$total' - 5/100 *'$total','$userid')");
                 $cr_dep=db_query($koneksi,"insert  into tabel_transaksi_toko(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                    values('$userid','$trns','$rak_aktiva_unit','$tgl','Bukti System','$keterangan_transaksi','$total' * 5/100,'$userid')");
				
				$jurnal=db_fetch_array(db_query($koneksi,"SELECT max(nomor_jurnal) FROM jurnal_umum where unit='$userid' ORDER BY tanggal_selesai DESC"));
                    $nomor=$jurnal[0]+1;
                    $trns="BU/".$unit .$nomor;
                $keterangan_transaksi="Musawamah an $nm_cust $id_cust";
                $query=db_query($koneksi,"insert into jurnal_umum(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values('$trns','$kode_transaksi','$tgl','$unit')");
                $deb_dep=db_query($koneksi,"insert  into tabel_transaksi_toko(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                    values('$unit','$trns','1412000','$tgl','Bukti System','$keterangan_transaksi','$total','$unit')");
                $cr_dep=db_query($koneksi,"insert  into tabel_transaksi_toko(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                    values('$unit','$trns','$rak_pasiva','$tgl','Bukti System','$keterangan_transaksi','$total' - 5/100 *'$total','$unit')");
                 $cr_dep=db_query($koneksi,"insert  into tabel_transaksi_toko(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                    values('$unit','$trns','1422000','$tgl','Bukti System','$keterangan_transaksi','$total' * 5/100,'$unit')");
					
				}
			break;

			case "Cash":
				$jurnal=db_fetch_array(db_query($koneksi,"SELECT max(nomor_jurnal) FROM jurnal_umum where unit='$userid' ORDER BY tanggal_selesai DESC"));
                    $nomor=$jurnal[0]+1;
                    $trns="BU/".$userid .$nomor;
                $keterangan_transaksi="Pembelian Cash";
               $query=db_query($koneksi,"insert into jurnal_umum(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values('$trns','$kode_transaksi','$tgl','$userid')");
                $deb_dep=db_query($koneksi,"insert  into tabel_transaksi_toko(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                    values('$userid','$trns','1120000','$tgl','Bukti System','$keterangan_transaksi','$total','$userid')");
                $cr_dep=db_query($koneksi,"insert  into tabel_transaksi_toko(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                    values('$userid','$trns','1482000','$tgl','Bukti System','$keterangan_transaksi','$total' - 5/100 *'$total' ,'$userid')");
 			$cr_dep=db_query($koneksi,"insert  into tabel_transaksi_toko(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                    values('$userid','$trns','41001','$tgl','Bukti System','$keterangan_transaksi','$total' * 5/100,'$userid')");



			break;
	 

			default:
		}
		
		
		
		
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

						echo "<script language='javascript'>document.location.href='data_toko.php'</script>";

}
?>
