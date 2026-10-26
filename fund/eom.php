<?php require_once __DIR__ . '/../php8_compat.php'; 
session_start();
date_default_timezone_set('Asia/Jakarta');
$userid=$_SESSION['userid'];
	include "../encryption/function.php";
	//untuk waktu
	$tanggal=date("y-m-d");
	//untuk koneksi database
	include "../conn2.php";
	$koneksi=open_connection2();
	$user=mysql_query("select kode_branch from user where userid='$userid'");
	while($id_user=mysql_fetch_array($user)){
			$unit=$id_user['kode_branch'];
			}
	?>
	<?php
	if(isset($_POST['hitung_shu'])){
			///////////////////////// HITUNG SHU /////////////////////
		$master=mysql_query("select id_deposito,norek,tgl_jatuh_tempo,aro,tenor,deposito.nominal as nomi,maturity_date,rasio,deposito.code_bulan,norek_tujuan,param_deposito.nominal,margin_murab 	 from deposito
		inner join deposito_detail on deposito.norek= deposito_detail.id_deposito 
		inner join param_deposito on param_deposito.code_bulan= deposito.code_bulan 
		left join deposan on deposan.cif= deposito.cif where tgl_jatuh_tempo ='$tanggal' group by deposito.cif");
		while($row=mysql_fetch_array($master)){
		$aro=$row['aro'];
		$norek=$row['id_deposito'];
		$tgl_jatuh_tempo=$row['tgl_jatuh_tempo'];
		$code_bulan=$row['code_bulan'];
		$jenis_simpanan=$row['jenis_simpanan'];
		$norek_tujuan=$row['norek_tujuan'];
		$nominal=$row['nominal'];
		$nomi=$row['nomi'];
		$margin_murab=$row['margin_murab'];
		$maturity_date=$row['maturity_date'];
		$tenor=$row['tenor'];
		$rasio=$row['rasio']/100;
		$bayar=$nomi/$nominal*$margin_murab*$rasio;
		if($aro=='n'){
		//update Tabel Deposito_detail
		$update=mysql_query("update deposito_detail set jumlah_nisbah='$bayar',tgl_bayar='$tanggal' where tgl_jatuh_tempo ='$tgl_jatuh_tempo' and id_deposito ='$norek'");
		}else{
		//update neraca
		$update=mysql_query("update deposito_detail set jumlah_nisbah='$bayar',tgl_bayar='$tanggal' where tgl_jatuh_tempo ='$tgl_jatuh_tempo' and id_deposito ='$norek'");
		}
		}
		if($update){
		$input_master=mysql_query("select * from deposito where maturity_date ='$tanggal' and aro='y' ");
		while($row=mysql_fetch_array($input_master)){
		$aro=$row['aro'];
		$norek=$row['id_deposito'];
		$tgl_jatuh_tempo=$row['tgl_jatuh_tempo'];
		$code_bulan=$row['code_bulan'];
		$jenis_simpanan=$row['jenis_simpanan'];
		$bilyet =$row['bilyet'];
		$tenor =$row['tenor'];
		$jenis=substr($code_bulan, 0, 2);
		if ($jenis =="WD"){
			$tahun = substr($tanggal,0,2);
			$bulan=substr($tanggal,2,3);
			$code_bulan2="WD$tahun$bulan";
			}elseif($jenis =="DP"){
			$tahun = substr($tanggal,0,2);
			$bulan=substr($tanggal,2,3);
			$code_bulan2="DP20$tahun$bulan";
			}elseif($jenis =="MC"){
					$tahun = substr($tanggal,0,2);
					$bulan=substr($tanggal,2,3);
					$code_bulan2="MC20$tahun$bulan";
					}elseif($jenis =="EMC"){
					$tahun = substr($tanggal,0,2);
					$bulan=substr($tanggal,2,3);
					$code_bulan2="EMC20$tahun$bulan";
					
				}
				$maturity_date2 =  date ("Y-m-d", strtotime("+$tenor month", strtotime($tanggal)));
				$cek = mysql_query("SELECT * FROM deposito WHERE norek='$norek'");
				if(mysql_num_rows($cek) == 0){
				$insert = mysql_query( "INSERT INTO deposito(norek,cif,bilyet, nominal,tenor,star_date,maturity_date,jenis_simpanan,aro,rasio,code_bulan,unit,userid)
				VALUES('$norek','$cif', '$bilyet', '$nominal', '$tenor', '$tanggal','$maturity_date2','$jenis_simpanan','$aro','$rasio','$code_bulan2','$unit','$userid')") or die(mysqli_error());
				$awal_hari = $tanggal;
				$akhir_hari = $maturity_date2;
				$x=0;
				while (strtotime($awal_hari) < strtotime($akhir_hari)) {
				$x ++;
				$awal_hari= date ("Y-m-d", strtotime("+1 month", strtotime($awal_hari)));
				$detail= mysql_query("INSERT DELAYED INTO deposito_detail (id,id_deposito,ke,tgl_jatuh_tempo,tgl_bayar,jumlah_nisbah,rek_tujuan,ket) values('$d','$norek','$x','$awal_hari','$awal_hari','0','$norek','$next_schedule')") or die(mysqli_error());
				}
				$jurnal=mysql_fetch_array(mysql_query("SELECT max(nomor_jurnal) FROM jurnal_umum where unit='$branch' ORDER BY tanggal_selesai DESC"));
				$nomor=$jurnal[0]+1;
				$trns="BU/".$branch .$nomor;
				$keterangan_transaksi="Penempatan Dana Norek $norek";
				$query=mysql_query("insert into jurnal_umum(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values('$trns','$kode_transaksi','$buss_date','$unit')");
				$deb_dep=mysql_query("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
				values('$branch','$trns','2651000','$buss_date','Bukti Umum','$keterangan_transaksi','$nominal','$userid')");
				$cr_dep=mysql_query("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
				values('$branch','$trns','$sima','$buss_date','Bukti Umum','$keterangan_transaksi','$nominal','$userid')");
			
				}
		}
		}else{
			echo "<script> alert ('data Anggota $buss_date sudah ditambahkan di kelompok $maturity_date ')</script>";

						echo "<script language='javascript'>document.location.href='new2.php'</script>";
		}
		//jika sudah selesai update
		
	}else{
		unset($_POST['hitung_shu']);
	}
	
	?>
	<style type="text/css">
	<!--
	.style1 {font-style: italic}
	-->
	</style>

	  <link rel="stylesheet" type="text/css" href="../css/bootstrap.min.css">

 <script type="text/javascript" src="../js/jquery.min.js"></script>
 <script type="text/javascript" src="../js/bootstrap.min.js"></script>

	
	<div class="post">
	  <div class="entry">
			<h1 align="center"><strong>Disburse</strong></h1>
			<p align="center">&nbsp;</p>
			<p align="center">
			<p align="center" class="style1"><font color="#666666">
		    Proses ini adalah proses untuk menghasilkan laporan DPK nur insani. </font></p>
			<p align="center"><em><font color="#666666">Proses  dilakukan setiap hari.
		    <?php 
			$cek=mysql_query("select * from tabel_transaksi where keterangan_posting='' and unit ='$unit'");
			$cek_posting=mysql_num_rows($cek);
			if($cek_posting!==0){
				//echo "masih ada yang belum di posting";
			}else{
				?>
				</font></em></p>
				<form action="?<?php echo paramEncrypt('page=../laporan/hitung_shu')?>" method="post" name="postform" id ="myform">
				<div align="center">
				<p>&nbsp;</p><p>
				<font color="#666666">
				<input type="submit" onclick="return confirm('Apakah Anda Yakin?')" name="hitung_shu" value="Proses" class="btn btn-primary" />
				</font></p>
				</div>
				</form>
				</div>
				<?php
			}
			?>
		    </div>
			</font>
		    <div align="center"><font color="#0066FF">
			<?php
			if($update){
			echo '<div class="alert alert-success alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><strong>Sukses </strong> DPK berhasil di Hitung </div>';
			}
			?>		  
	        </font>
	        </p>
            </div>
	  </div>
	</div>
<?php 
?>

