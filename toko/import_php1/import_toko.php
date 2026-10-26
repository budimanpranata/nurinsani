<?php require_once __DIR__ . '/../../php8_compat.php';
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
include "koneksi.php";

if(isset($_POST['import'])){ // Jika user mengklik tombol Import
	
 
	
	// Buat query Insert
	$sql = $pdo->prepare("INSERT INTO temp_toko VALUES(:id_outlet,:id_trxsale,:id_kasir,:tgl,:jam,:id_cust,:nm_cust,:nik,:id_pabrik,:cash_crdt,:total,:dompet)");
	
	$sql = $pdo->prepare("SELECT count(id_trxsale) as noa FROM ");
	while($data = $sql->fetch()){ // Ambil semua data dari hasil eksekusi $sql
						echo "<tr>";
						$numrow=$data['noa'];
	}
	
	$sql1 = $pdo->prepare("select * from siswa");
					$sql->execute(); // Eksekusi querynya
					
					$no = 1; // Untuk penomoran tabel, di awal set dengan 1
					while($data = $sql1->fetch()){ // Ambil semua data dari hasil eksekusi $sql
						echo "<tr>";
			$id_outlet = $data['id_outlet'];
			$id_trxsale = $data['id_trxsale'];
			$id_kasir = $data['id_kasir'];
			$tgl = $data['tgl']; 
			$jam = $data['jam'];
			$id_cust = $data['id_cust'];
			$nm_cust = $data['nm_cust'];
			$nik = $data['nik'];
			$id_pabrik = $data['id_pabrik'];
			$cash_crdt = $data['cash_crdt'];
			$total = $data['total'];
			$dompet = $data['dompet'];
						
		
		
	}
if($numrow > 1){
			// Proses simpan ke Database
			$sql->bindParam(':id_outlet',$id_outlet);
			$sql->bindParam(':id_trxsale',$id_trxsale);
			$sql->bindParam(':id_kasir',$id_kasir);
			$sql->bindParam(':tgl',$tgl);
			$sql->bindParam(':jam',$jam);
			$sql->bindParam(':id_cust',$id_cust);
			$sql->bindParam(':nm_cust',$nm_cust);
			$sql->bindParam(':nik',$nik);
			$sql->bindParam(':id_pabrik',$id_pabrik);
			$sql->bindParam(':cash_crdt',$cash_crdt);
			$sql->bindParam(':total',$total);
			$sql->bindParam(':dompet',$dompet);
			$sql->execute(); // Eksekusi query insert
		}
		
		$numrow++; // Tambah 1 setiap kali looping

header('location: index.php'); // Redirect ke halaman awal
}else {
	echo "Gagal Posting";
}
?>
