<?php require_once __DIR__ . '/../../php8_compat.php'; 
session_start();
$userid=$_SESSION['userid'];
if(!isset($_SESSION['userid'])){
   header("location:../../index.html");
 exit; }
$tgl_system=$_SESSION['stgl_system'];
//cek apakah user sudah login
if(!isset($_SESSION['stgl_system'])){
echo "<script>alert('Maaf,.tanggal system belum di setup');</script>";

   header("location:../param_tgl.php");
 exit; }

//cek level user
if($_SESSION['level']!="toko"){
   header("location:../../index.html");
 exit; }

	include "../encryption/function.php";
	include"../../indo_tgl.php";
	
	//untuk waktu
	$tanggal=date("y-m-d");
	
	//untuk koneksi database
	include "../conn2.php";
	$koneksi=open_connection2();
	$user=mysql_query("select kode_branch from user where userid='$userid'");
			
			while($id_user=mysql_fetch_array($user)){
			
				$unitt=$id_user['kode_branch'];
			}
	
?>	
<script type="text/javascript" src="../jquery.js"></script>
		<link href="../style.css" rel="stylesheet" type="text/css" media="screen" />
		<link rel="stylesheet" href="../css/bootstrap.min.css"/>
	</script>
	
		<style>
	#result {
		height:20px;
		font-size:12px;
		font-family:Arial, Helvetica, sans-serif;
		color:#333;
		padding:5px;
		margin-bottom:10px;
		background-color:#FFFF99;
	}
	#country{
		padding:3px;
		border:1px #CCC solid;
		font-size:12px;
	}
	.suggestionsBox {
		position: absolute;
		left: 0px;
		top:40px;
		margin: 26px 0px 0px 0px;
		width: 200px;
		padding:0px;
		background-color:#999999;
		border-top: 3px solid #999999;
		color: #fff;
	}
	.suggestionList {
		margin: 0px;
		padding: 0px;
	}
	.suggestionList ul li {
		list-style:none;
		margin: 0px;
		padding: 6px;
		border-bottom:1px dotted #666;
		cursor: pointer;
	}
	.suggestionList ul li:hover {
		background-color: #FC3;
		color:#000;
	}
	ul {
		font-family:Arial, Helvetica, sans-serif;
		font-size:11px;
		color:#FFF;
		padding:0;
		margin:0;
	}
	
	.load{
	background-image:url(loader.gif);
	background-position:right;
	background-repeat:no-repeat;
	}
	
	#suggest {
		position:relative;
	}
	.style9 {
	font-size: 16px;
	font-family: Verdana, Arial, Helvetica, sans-serif;
}
.style3 {	color: #FFFFFF;
	font-size: 13px;
	font-weight: bold;
}
.header1 {color: #ffffff; background: #4682B4; font-weight: bold; font-family: Tahoma, Verdana; font-size: 13px; margin: 0px;}
body {
	background-image: url();
}
a:link {
	text-decoration: none;
}
a:visited {
	text-decoration: none;
}
a:hover {
	text-decoration: none;
}
a:active {
	text-decoration: none;
}
	.style10 {
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: 12px;
}

    .style11 {font-size: small}
    </style>

	<link href="../style.css" rel="stylesheet" type="text/css" media="screen" />
	<div class="post">
	<div class="entry">
	
			<table width="100%">
		<tr bgcolor="#669900">
		<td height="25" colspan="4" nowrap><div align="left" class="style3 style9">Porses Posting Jurnal</div></td>
			</tr>
            </table>
			<p>
		<div class = "panel panel-default">
	  		<table width="100%" border="1" bordercolor="669900" style="margin: 7px 10px" class="table table-striped" >
		<tr>
			<th>Tanggal</th><th>Kode Rekening</th><th>Nama Rekening</th><th>Keterangan</th><th>Debet</th><th>Kredit</th>
		</tr>
		<?php
		$query_transaksi=mysql_query("select tanggal_transaksi,tabel_transaksi_toko.kode_rekening,sum(debet) as debet,sum(kredit) as kredit,nama_rek,keterangan_transaksi from tabel_transaksi_toko inner join coa on coa.kode_rek = tabel_transaksi_toko.kode_rekening  where  keterangan_posting='' and tabel_transaksi_toko.unit ='$unitt' and tanggal_transaksi='$tgl_system'  Group By kode_rekening");
		$total=mysql_fetch_array(mysql_query("select sum(debet) as tot_debet, sum(kredit) as tot_kredit from tabel_transaksi_toko where  keterangan_posting='' and unit ='$unitt' and tanggal_transaksi='$tgl_system' order by kode_rekening  asc"));
		while($row_tran=mysql_fetch_array($query_transaksi)){
			$debet=$row_tran['debet'];
			$kredit=$row_tran['kredit'];
			
			?>
			<tr>
				<td><div align="left"><?php echo tgl_indo ($row_tran['tanggal_transaksi']);?></div></td>
				<td><div align="center"><?php echo $row_tran['kode_rekening'];?></div></td>
				<td><div align="left"><?php echo $row_tran['nama_rek'];?></div></td>
				<td><?php echo $row_tran['keterangan_transaksi'];?></td>
				<td align="right"><?php echo number_format(compat_num($debet),2,'.',','); ?></td>
				<td align="right"><?php echo number_format(compat_num($kredit),2,'.',','); ?></td>
				
			</tr>
			<?php
		}
		?>
		<tr>
				<td colspan="4"><div align="center"><strong>TOTAL TRANSAKSI</strong></div></td>
				<td align="right"><strong><?php echo number_format(compat_num($total['tot_debet'])); ?></strong></td>
				<td align="right"><strong><?php echo number_format(compat_num($total['tot_kredit'])); ?></strong></td>
			</tr>
		</table>
		</p>
	</div>
	</div>



	<div class="post">
	<div class="entry">
		<p>
		<table border="0" align="center">
		<tr>
			<td width="72" align="center">
			<!---untuk mengakhiri posting dan memberi tanda posting-->
			<?php 
			$cek=mysql_query("select keterangan_posting from tabel_transaksi_toko where keterangan_posting='' and unit ='$unitt' and tanggal_transaksi='$tgl_system'");
			$cek_posting=mysql_num_rows($cek);
			if($cek_posting!==0){
				?>
				<form action="?<?php echo paramEncrypt('page=../transaksi/posting')?>" method="post" name="postform">
				<input type="submit" onclick="return confirm('Apakah Anda Yakin?')" name="posting" value="POSTING JURNAL" />
				</form>
			<?php
			}
			?>
		  </td>
		</tr>
		<tr>
			<td width="601" align="center">
			<font face="verdana" color="#666666">
			<?php
			//untuk mendecode url yang di enrypsi
			$var=decode($_SERVER['REQUEST_URI']);
			//pecahkan nilai array
			echo $page=$var['status'];
			?>
			</font>			
			</td>
		</tr>
		</table>
		</p>
	</div>
	</div>

	
	<?php
	if(isset($_POST['posting'])){
	
		///////////////////////// HITUNG MUTASI /////////////////////
		$query_hitung_mutasi=mysql_query("select kode_rekening,debet,kredit,unit from tabel_transaksi_toko where keterangan_posting=''and unit ='$unitt'and tanggal_transaksi='$tgl_system'");
		while($row_hit_mut=mysql_fetch_array($query_hitung_mutasi)){
			$kode_rekening=$row_hit_mut['kode_rekening'];	
			$debet=$row_hit_mut['debet'];
			$kredit=$row_hit_mut['kredit'];
			$unit =$row_hit_mut['unit'];
			$update_mutasi=mysql_query("update tabel_master set mut_debet=mut_debet+'$debet', mut_kredit=mut_kredit+'$kredit' where kode_rekening='$kode_rekening' and unit ='$unit'");

			//$update_mutasi=mysql_query("update tabel_master set mut_debet=mut_debet+(SELECT debet FROM tabel_transaksi_toko WHERE kode_rekening ='$kode_rekening' and keterangan_posting=''), mut_kredit=mut_kredit+(SELECT kredit FROM tabel_transaksi_toko WHERE kode_rekening ='$kode_rekening' and keterangan_posting='') where kode_rekening='$kode_rekening'");

		}
				
		if($query_hitung_mutasi){
			$query_hitung_sisa=mysql_query("select kode_rekening,normal,awal_debet,awal_kredit,mut_debet,mut_kredit from tabel_master where unit ='$unitt'");
			
			while($row_hit_sisa=mysql_fetch_array($query_hitung_sisa)){
			
				$normal=$row_hit_sisa['normal'];
				$kode_rekening=$row_hit_sisa['kode_rekening'];
					
				$awal_debet=$row_hit_sisa['awal_debet'];
				$awal_kredit=$row_hit_sisa['awal_kredit'];
				
				$mutasi_debet=$row_hit_sisa['mut_debet'];
				$mutasi_kredit=$row_hit_sisa['mut_kredit'];
					
			
				if($normal=="debet"){
					$hitung_sisa_debet=($awal_debet+$mutasi_debet)-$mutasi_kredit;
					
					if($hitung_sisa_debet<0){
						$positif_sisa_kredit=abs(compat_num($hitung_sisa_debet));
						$update_mutasi=mysql_query("update tabel_master set sisa_debet=0, sisa_kredit='$positif_sisa_kredit' where kode_rekening='$kode_rekening' and unit ='$unitt'");
					}else{
						$update_mutasi=mysql_query("update tabel_master set sisa_debet='$hitung_sisa_debet', sisa_kredit='0' where kode_rekening='$kode_rekening' and unit ='$unitt'");
					}	
								
				}
				
				if($normal=="kredit"){
					$hitung_sisa_kredit=($awal_kredit-$mutasi_debet)+$mutasi_kredit;
					
					if($hitung_sisa_kredit<0){
						$positif_sisa_debet=abs(compat_num($hitung_sisa_kredit));
						$update_mutasi=mysql_query("update tabel_master set sisa_debet='$positif_sisa_debet', sisa_kredit='0' where kode_rekening='$kode_rekening' and unit ='$unitt'");
					}else{
						$update_mutasi=mysql_query("update tabel_master set sisa_debet=0, sisa_kredit='$hitung_sisa_kredit' where kode_rekening='$kode_rekening' and unit ='$unitt'");
					}		
				}
			}
		}
		
		
		////////////////////////// UBAH STATUS POSTING //////////////////////////////
		$selesai=mysql_query("update tabel_transaksi_toko set tanggal_posting='$tanggal', keterangan_posting='Post' where keterangan_posting='' and unit ='$unitt' and tanggal_transaksi='$tgl_system'");
		
		if($selesai){
			?><script language="javascript">document.location.href="?<?php echo paramEncrypt('page=./transaksi/posting&status=Proses Posting Selesai')?>"</script><?php
		}else{
			echo mysql_error();
		} 
		
							
	}else{
		unset($_POST['posting']);
	}
	?>

<?php 

?>
