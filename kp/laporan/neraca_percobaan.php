<?php require_once __DIR__ . '/../../php8_compat.php'; 
session_start();

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
<script type="text/javascript" src="../jquery.min.js"></script>
<link rel="stylesheet" href="../css/bootstrap.min.css"/>
	<link rel="stylesheet" href="../css/dataTables.bootstrap.min.css"/>
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
.style12 {font-size: 9px}
        </style>
		<table width="100%">
  <tr bgcolor="#669900">
  <td height="30" colspan="4" nowrap><div align="left" class="style9 style3 style1 style1"><strong> Neraca </strong></div></td>
  </tr>
</table>
<p>
	<div class="post">
	<div class="entry">
			<?php
					?>
			<div class="container">
				<table width="100%" border="1" bordercolor="669900" class="table table-striped table-bordered" width="100%" 
			
			<tr> 
				<div class="row" align="center">	
				<th align="center" rowspan="2">Account</th>
			 </div>
				<div class="row" align="center">
					<th align="center" rowspan="2">Nama Account</th>
					<th colspan="2">Awal</th>
					<th colspan="2">Mutasi</th>
					<th colspan="2">Sisa</th>
			  </tr>
			</div>
			<tr>
			
				<th>Debet</th>
				<th>Kredit</th><th>Debet</th><th> Kredit</th><th>Debet</th><th>Kredit</th>
			</tr>	
			<?php
			$query_mutasi=mysql_query("select * from tabel_master where unit ='$unit'  and  nama_rekening not like '%total%' order by kode_rekening asc");
			$total=mysql_fetch_array(mysql_query("select sum(awal_debet) as tot_awal_debet, sum(awal_kredit) as tot_awal_kredit, sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(sisa_debet) as tot_sisa_debet, sum(sisa_kredit) as tot_sisa_kredit from tabel_master where unit ='$unit' order by kode_rekening asc"));
			
			while($row_mut=mysql_fetch_array($query_mutasi)){
			
				$awal_debet=$row_mut['awal_debet'];
				$awal_kredit=$row_mut['awal_kredit'];
				$mutasi_debet=$row_mut['mut_debet'];
				$mutasi_kredit=$row_mut['mut_kredit'];
				$sisa_debet=$row_mut['sisa_debet'];
				$sisa_kredit=$row_mut['sisa_kredit'];
			
				?>
				<tr>
					<td><div align="center"><?php echo $row_mut['kode_rekening'];?></div></td>
					<td><?php echo $row_mut['nama_rekening'];?></td>
					
					<td align="right"><?php echo number_format(compat_num($awal_debet)); ?></td>
					<td align="right"><?php echo number_format(compat_num($awal_kredit)); ?></td>	
					
					<td align="right"><?php echo number_format(compat_num($mutasi_debet)); ?></td>
					<td align="right"><?php echo number_format(compat_num($mutasi_kredit)); ?></td>	
					
					<td align="right"><?php echo number_format(compat_num($sisa_debet)); ?></td>
					<td align="right"><?php echo number_format(compat_num($sisa_kredit)); ?></td>					
				</tr>
				<?php
			}
			?>
			<tr>
				<td colspan="2"><div align="center"><strong>TOTAL TRANSAKSI</strong></div></td>
				<td><div align="right"><strong><?php echo number_format(compat_num($total['tot_awal_debet'])); ?></strong></div></td><td><div align="right"><strong><?php echo number_format(compat_num($total['tot_awal_kredit'])); ?></strong></div></td>
				<td><div align="right"><strong><?php echo number_format(compat_num($total['tot_mut_debet'])); ?></strong></div></td><td><div align="right"><strong><?php echo number_format(compat_num($total['tot_mut_kredit'])); ?></strong></div></td>
				<td><div align="right"><strong><?php echo number_format(compat_num($total['tot_sisa_debet'])); ?></strong></div></td>
				<td><div align="right"><strong><?php echo number_format(compat_num($total['tot_sisa_kredit'])); ?></strong></div></td>
			</tr>
			</table>
			</div>
			</p>
	</div>
	</div>

<?php 

?>
