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
		<tr bgcolor="#2F6B4F">
		<td height="25" colspan="4" nowrap><div align="left" class="style3 style9">Porses Posting Parameter</div></td>
			</tr>
            </table>
			<p>
		<div class = "panel panel-default">
	  		<table width="100%" border="1" bordercolor="669900" style="margin: 7px 10px" class="table table-striped" >
		<tr>
			<th>Tanggal</th><th>Kode Proses</th><th>Jenis Dana</th><th>Nominal</th><th>Keterangan</th>
		</tr>
		<?php
		$query_transaksi=mysql_query("select * from param_deposito where  ket=''  order by buss_date asc");
		while($row_tran=mysql_fetch_array($query_transaksi)){
			$nominal=$row_tran['nominal'];
		
			
			?>
			<tr>
				<td><div align="center"><?php echo $row_tran['buss_date'];?></div></td>
				<td><div align="center"><?php echo $row_tran['code_bulan'];?></div></td>
				<td><?php echo $row_tran['code_jenis'];?></td>
				<td align="right"><?php echo number_format(compat_num($nominal),2,'.',','); ?></td>
				
				<td align="center"><?php echo $row_tran['ket'];?></td>
			</tr>
			<?php
		}
		?>
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
			$cek=mysql_query("select * from param_deposito where  ket=''");
			$cek_posting=mysql_num_rows($cek);
			if($cek_posting!==0){
				?>
				<form action="?<?php echo paramEncrypt('page=../transaksi/posting')?>" method="post" name="postform">
				<input type="submit" onclick="return confirm('Apakah Anda Yakin?')" name="posting" value="POSTING PAMRAMETER" />
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
		$perode_bulan=date("Ymd");
		 $tahun = substr($perode_bulan,0,4);
					$bulan=substr($perode_bulan,4,2);
					$code_bln_f="NI$tahun-$bulan";
	$query_jml_aset=mysql_fetch_array(mysql_query("select nomi_murab,marigin_murab,DATEDIFF(sd,dari) as hari from param_finan where code_param='NI2025-07'"));
	$tot_fin=$query_jml_aset['nomi_murab'];
	$margin=$query_jml_aset['marigin_murab'];
	$hari=$query_jml_aset['hari'];
	
	//$query_asset=mysql_fetch_array(mysql_query("select sum(nominal) as nominal from param_deposito where ket=''"));
	//$fund=$query_asset['nominal'];
	$query_wd=mysql_fetch_array(mysql_query("select nominal as wadiah from param_deposito where  code_jenis='WD' and ket =''"));
	$wadiah1=$query_wd['wadiah'];
	$query_mud=mysql_fetch_array(mysql_query("select sum(nominal) as mudarobah from param_deposito where  code_jenis='DP' and ket =''"));
	$mudarobah=$query_mud['mudarobah'];
	
	//cek posisi selisih financial 
	$kurang=$tot_fin-$mudarobah;
	$cek=$wadiah1-$kurang;
	$yang_diambil_dari_wadiah=$kurang-$wadiah1;
	//Jika Modarobah <= total FInancial makan total financial + selisih 
				if ($mudarobah<=$tot_fin){
				$kurang=$tot_fin-$mudarobah;
				$cek=$wadiah1-$kurang;
				$yang_diambil_dari_wadiah=$kurang-$wadiah1;
				$tot=$mudarobah + $kurang;
				$mudarobah=$mudarobah;
			// jika <>= makan ambil nilai mudarobah sebenarnya	
			}else {
				$mudarobah=$mudarobah;
				$tot_fin=$tot_fin;
			}
					
			if ($wadiah1 <= $yang_diambil_dari_wadiah){
				$kurang1=$tot_fin-$mudarobah;
				$yang_diambil_dari_modal=$kurang1-$wadiah1;
				$modd=$modal-$yang_diambil_dari_modal;
				$m=$modal-$modd;
				$mod=$m;
				$wadiah=$wadiah1;
				$tot=$mudarobah + $kurang;
				
					
			}elseif ($wadiah1 >= $yang_diambil_dari_wadiah)  {
				$kurang_wadiah=$tot_fin-$mudarobah;
				$dari_wadiah=$wadiah1-$kurang_wadiah;
				$n=$wadiah1-$dari_wadiah;
				$wadiah=$n;
				$mudarobah=$mudarobah;
				$tot=$mudarobah + $kurang_wadiah;
			}
			
	//$insert_dep_dp=mysql_query("insert into param_dep select * from param_deposito where code_jenis='DP' ");
	//$insert_dep_wd=mysql_query("replace into param_dep(code_bulan,code_jenis,nominal,margin_murab)VALUES ('2','WD','$wadiah','$marginnn')");
	//$insert_dep_modal=mysql_query("replace into param_dep(code_bulan,code_jenis,nominal,margin_murab)VALUES ('3','MC','$modal','$marginnn')");
	$update_mutasi=mysql_query("update param_deposito set nominal ='$wadiah' where code_jenis='WD' and ket=''");
	$update_mutasi=mysql_query("update param_rasio set nominal ='$wadiah' where code_jenis='WD' and ket=''");


		///////////////////////// HITUNG MUTASI /////////////////////
		$query_hitung_mutasi=mysql_query("select * from param_deposito where ket=''");
		while($row_hit_mut=mysql_fetch_array($query_hitung_mutasi)){
			$code_bulan=$row_hit_mut['code_bulan'];	
			$code_jenis=$row_hit_must['code_jenis'];
			$nominal=$row_hit_mut['nominal'];
			
		
			
				$mrg=$nominal/$tot_fin * $margin;
				$margi_bagi=$nominal/$tot * $margin;
				$update_mutasi=mysql_query("update param_deposito set margin_murab='$mrg', margin_bagi='$margi_bagi' where code_bulan='$code_bulan' and ket=''");
				$update_rasio=mysql_query("update param_rasio set margin_murab='$mrg', margin_bagi='$margi_bagi' where code_bulan='$code_bulan' and ket=''");
				
				//$insert_rasio=mysql_query("insert into param_rasio(code_bulan,rasio_invest,rasio_cor,code_jenis)VALUES");
				//$insert_dep=mysql_query("insert into param_dep(code_bulan,code_jenis,nominal,margin_murab)VALUES ('$code_bulan','$code_jenis','$marginnn')");


		}
		$query_hitung=mysql_query("select * from param_dep where ket=''");
		while($row_hit=mysql_fetch_array($query_hitung)){
			$code_bulan=$row_hit['code_bulan'];	
			$code_jenis=$row_hit['code_jenis'];
			$nominal=$row_hit['nominal'];
			
		
			
				$marginnn=$nominal/$tot * $margin;
				$update_mut=mysql_query("update param_dep set margin_murab='$marginnn' where code_bulan='$code_bulan' and ket=''");
				//$insert_rasio=mysql_query("insert into param_rasio(code_bulan,rasio_invest,rasio_cor,code_jenis)VALUES");
				//$insert_dep=mysql_query("insert into param_dep(code_bulan,code_jenis,nominal,margin_murab)VALUES ('$code_bulan','$code_jenis','$marginnn')");


		}
		$query_param_rasio=mysql_query("select * from param_rasio where ket=''");
		while($row_rasio=mysql_fetch_array($query_param_rasio)){
			$rasio_invest=$row_rasio['rasio_invest'];	
			$code_bulan=$row_rasio['code_bulan'];
			$margi_bagi=$row_rasio['margin_bagi'];
			$nominal_md=$row_rasio['nominal'];
			$rasio_cor=100-$rasio_invest;
			$rate=$row_rasio['rate'];
			
		
			
			$nomi_invest=($rasio_invest/100) * $margi_bagi;
			$eq=($nomi_invest/$nominal_md)*(365/$hari);
			$nomi_cor=($rasio_cor/100)*$margi_bagi;
			$ekspek=($rate/100)*$nominal_md*31/365;
			$rasio_eks=($ekspek/$margi_bagi)*100;
			$rasio_eks=round(compat_num($rasio_eks));
			
			
			$update_invest=mysql_query("update param_rasio set margin_murab='$marginnn',nomi_invest='$nomi_invest',rasio_cor='$rasio_cor', eq='$eq', nomi_cor='$nomi_cor',ekspek='$ekspek',rasio_eks='$rasio_eks' where code_bulan='$code_bulan' and ket=''");
				//$insert_rasio=mysql_query("insert into param_rasio(code_bulan,rasio_invest,rasio_cor,code_jenis)VALUES");
				//$insert_dep=mysql_query("insert into param_dep(code_bulan,code_jenis,nominal,margin_murab)VALUES ('$code_bulan','$code_jenis','$marginnn')");


		}
		$dep_dep_dp=mysql_query("delete from param_dep ");
		$insert_dep_dp=mysql_query("insert into param_dep select * from param_rasio where ket ='' ");
		////////////////////////// UBAH STATUS POSTING //////////////////////////////
	
		$selesai=mysql_query("update param_deposito set ket='Post' where ket='' ");
		$selesai=mysql_query("update param_dep set ket='Post' where ket='' ");
		$selesai=mysql_query("update param_rasio set ket='Post' where ket='' ");
		$selesai=mysql_query("update param_dep set code_bulan='1' where code_jenis='DP' ");
		$selesai=mysql_query("update param_dep set code_bulan='2' where code_jenis='WD' ");
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
