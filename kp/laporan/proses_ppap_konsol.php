<?php require_once __DIR__ . '/../../koneksi_db.php'; 
session_start();
$userid=$_SESSION['userid'];
$tanggal_transaksi=$_SESSION['stgl_system'];

	//untuk waktu
	//$tanggal=date("y-m-d");
	include "../encryption/function.php";	
	$con = db_ni();
	if (mysqli_connect_errno()) {
		echo "Failed to connect to MySQL: " . mysqli_connect_error();
		exit;
	
	}

	function acakangkahuruf($panjang)
{
    $karakter= '1234567890ABCDEFGHIJKL';
    $string = '';
    for ($i = 0; $i < $panjang; $i++) {
  $pos = rand(0, strlen($karakter)-1);
  $string = $karakter($pos);
    }
    return $string;
}
	
if(!isset($_SESSION['stgl_system'])){
echo "<script>alert('Maaf,.tanggal system belum di setup');</script>";

   header("location:../../param_tgl.php");
}
//cek apakah user sudah login
if(!isset($_SESSION['userid'])){
   header("location:../../index.html");
}
	
?>

<?php
	if(isset($_POST['proses_ppap'])){
		$_SESSION['progress'] =0;
		
	
		$ambilunit=db_query($con,"select * from report_ppap");
		while($row_hit_mut=db_fetch_array($ambilunit)){
			$unit=$row_hit_mut['unit'];	
			$nominal=$row_hit_mut['jumlah_selisih'];
			$status=$row_hit_mut['status'];
			$kodetrs=8;
		$kodetrs=acakangkahuruf($kodetrs);
		$kode_transaksi="BU/".$unit.$kodetrs;
	
		if($status=='1'){
			$query=db_query($con,"insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
			values('$unit','$kode_transaksi','54131','$tanggal_transaksi','Bukti System','Biaya PPA Umum-Piutang-Murabahah','$nominal','$userid')");
	
	$query=db_query($con,"insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
			values('$unit','$kode_transaksi','1512000','$tanggal_transaksi','Bukti System','PPA Umum-PYD Piutang Murabahah','$nominal','$userid')");
	
	
		} else{
			$nominal = -1*($nominal);
	
			$query=db_query($con,"insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
			values('$unit','$kode_transaksi','1512000','$tanggal_transaksi','Bukti System','PPA Umum-PYD Piutang Murabahah','$nominal','$userid')");
	
	$query=db_query($con,"insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
			values('$unit','$kode_transaksi','42131','$tanggal_transaksi','Bukti System','Pdptn-Koreksi PPA-Piutang-Murabahah','$nominal','$userid')");
	
		}
			
	
	
	
		$berhasil= db_query ($con,"update report_ppap set status_posting ='post' where unit ='$unit'");					
		
		}
		echo "<script>alert('update Total SHU Sukses ');</script>";

	
	
							
	}else{
		unset($_POST['hitung_ppap']);
	}
	?>
 
	<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Progress Bar Example</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" rel="stylesheet">

	<style>
        .loading-indicator {
            display: none;
            width: 100px;
            height: 100px;
        }
    </style>
</head>
<body>
		


			<h2 align="center"><strong>Proses PPAP</strong></h2>
			<p align="center">&nbsp;</p>
			<p align="center">
			<p align="center" class="style1"><font color="#666666">
			<p align="center"><em><font color="#666666">Tanggal Periode PPAP <?php echo $tgl_system?>.
		    <p align="center"><em><font color="#666666">Proses bisa Setelah Neraca Balance.
			<p align="center"><em><font color="#666666">Proses ini akan melakukan blokir userid
			
				
				<form action="progres_ppap.php" method="post" id ="myform">

				</font>
		    <div align="center"><font color="#0066FF">
			<button type="submit" class="btn btn-primary">Proses PPAP</button>
				
           
	        </font>
				
</div>	
				
			
				
				</form>
				
		    <div align="center"><font color="#0066FF">
			
			<div class="mt-3" id="loadingContainer">
            <img src="https://c.tenor.com/5o2p0tH5LFQAAAAi/hug.gif" class="loading-indicator" id="loadingIndicator" alt="Loading...">
        </div>
	      
	      
            </div>
				
				
				<form action="?<?php echo paramEncrypt('page=../laporan/hitung_shu')?>" method="post" name="postform" >
				<div align="center">
				<table border="1" cellpadding="10">
				<tr>
				<td bgcolor="#00ff80"> No</td>
				<td bgcolor="#00ff80"> Kode AP</td>
				<td bgcolor="#00ff80"> AP</td>
				<td bgcolor="#00ff80"> Akan dibentuk</td>
				<td bgcolor="#00ff80"> Sudah dibentuk</td>
				<td bgcolor="#00ff80"> Selisih</td>
				<td bgcolor="#00ff80"> Status Jurnal</td>
				</tr>
				<?php
				$report=db_query($con,"select no,akan_dibentuk, sdh_dibentuk, jumlah_selisih,status, report_ppap.unit, branch.unit as ap from report_ppap
				left join branch on branch.kode_branch = report_ppap.unit
				where status_posting is null");
				while($row=db_fetch_array($report))

{
	if($row['status']=='1'	){
		$status= 'Selisih lebih besar';
	}else{
		$status= 'Selisih lebih Kecil';
	}

	echo '<tr>
		<td>' . $row['no'] . '</td>
		<td>' . $row['unit'] . '</td>
		<td>' . $row['ap'] . '</td>
		<td>' . number_format(compat_num($row['akan_dibentuk'])) . '</td>
		<td>' . number_format(compat_num($row['sdh_dibentuk'])) . '</td>
		<td>' . number_format(compat_num($row['jumlah_selisih'])) . '</td>
		<td>' .$status. '</td> 
	
		</tr>';

}

?>
				<table>
				<p>&nbsp;</p><p>
				<font color="#666666">
				<input type="submit" onclick="return confirm('Apakah Anda Yakin akan proses PPAP?')" name="proses_ppap" value="Posting Jurnal PPAP" class="btn btn-primary" />
				
				</font></p>
				
				</form>			
				
				
			
		
			</font>
		    <div align="center"><font color="#0066FF">
			
           
	        </font>
	        </p>
            </div>
	

			<script>
        document.getElementById('myform').addEventListener('submit', function(event) {
            event.preventDefault();

            var formData = new FormData(this);
            var loadingIndicator = document.getElementById('loadingIndicator');
            loadingIndicator.style.display = 'block';

            fetch('progres_ppap.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                loadingIndicator.style.display = 'none';
                if (data.success) {
                    setTimeout(() => {
                        window.location.href = 'proses_ppap_konsol.php';
                    }, 1000);
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                loadingIndicator.style.display = 'none';
                console.error('Error:', error);
                alert('Error: Server tidak merespon.');
            });
        });
    </script>

</body>
</html>