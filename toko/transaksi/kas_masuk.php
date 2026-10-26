<?php require_once __DIR__ . '/../../php8_compat.php'; 
session_start();
$userid=$_SESSION['userid'];
if(!isset($_SESSION['userid'])){
   header("location:../index.html");
}

//cek level user
if($_SESSION['level']!="unit"){
   header("location:../../index.html");
}

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
	<script type="text/javascript" src="../jquery.js"></script>
		<link href="../style.css" rel="stylesheet" type="text/css" media="screen" />
	<link rel="stylesheet" href="../css/bootstrap.min.css"/>
	<script>
	function suggest(inputString){
		if(inputString.length == 0) {
			$('#suggestions').fadeOut();
		} else {
		$('#country').addClass('load');
			$.post("../autosuggesst.php", {queryString: ""+inputString+""}, function(data){
				if(data.length >0) {
					$('#suggestions').fadeIn();
					$('#suggestionsList').html(data);
					$('#country').removeClass('load');
				}
			});
		}
	}
	
	function fill(thisValue) {
		$('#country').val(thisValue);
		setTimeout("$('#suggestions').fadeOut();", 100);
	}
	
	function fill2(thisValue) {
		$('#kode').val(thisValue);
		setTimeout("$('#suggestions').fadeOut();", 100);
	}
	function formatCurrency(num) {
    num = num.toString().replace(/\$|\,/g,'');
    if(isNaN(num))
    num = "0";
    sign = (num == (num = Math.abs(num)));
    num = Math.floor(num*100+0.50000000001);
    cents = num%100;
    num = Math.floor(num/100).toString();
    if(cents<10)
    cents = "0" + cents;
    for (var i = 0; i < Math.floor((num.length-(1+i))/3); i++)
    num = num.substring(0,num.length-(4*i+3))+'.'+
    num.substring(num.length-(4*i+3));
    return (((sign)?'':'-') + 'Rp. ' + num + '');
    }
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
		left:140px;
		top:0px;
		margin: 26px 0px 0px 0px;
		width: 200px;
		padding:0px;
		background-color:#669900;
		border-top: 3px solid #669900;
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
	

<div class="col-md-12 col">
	<body onLoad="document.postform.elements['keterangan_jurnal'].focus();">
	<table width="100%">
  <tr bgcolor="#669900">
  <td height="25" colspan="4" nowrap><div align="left" class="style3 style9">Jurnal Masuk</div></td>
  </tr>
</table>
	<div class="post">
				<div class="entry">
				<?php 
			
			//jurnal baru. cari nomor paling besar yaitu nomor jurnal terakhir 
			$jurnal_keluar=mysql_fetch_array(mysql_query("SELECT max(nomor_jurnal) FROM jurnal_masuk where unit='$unit' ORDER BY tanggal_selesai DESC"));
			$nomor_jurnal=$jurnal_keluar[0]+1;
			$kode_transaksi="KM/".$unit .$nomor_jurnal;
			
			?>
			
			 <table width="502" border="0" bordercolor="#FFFFFF" align="center">
			 </table>
			<form action="?<?php echo paramEncrypt('page=../transaksi/kas_masuk')?>" method="post" name="postform"> <table width="502" border="1" border bordercolor="#FFFFFF" align="center">
			   <tr bgcolor="#669900">
  				<td height="25" colspan="4" nowrap><div align="center" class="style3 style9">Kriteria Input</div></td>
  				</tr>
				  <tr>
                  <td width="100" height="15"  nowrap bgcolor="#B3B3FF"><div align="left" class="style10">Nomor Transaksi</div></td>
				  <td width="10" nowrap><div align="center"><strong>:</strong></div></td>
                  <td colspan="2" nowrap>
				  <input type="hidden" name="kode_bukti" value="<?php echo $kode_transaksi;?>">
				  <input type="text" disabled="disabled" value="<?php echo $kode_transaksi;?>" size="15"/>
				  </td>
                </tr>
                <tr>
                  <td  nowrap bgcolor="#B3B3FF"><span class="style10">Tanggal</span></td>
				  <td width="10" nowrap>   <div align="center"><strong>:</strong></div></td>
                  <td colspan="2" nowrap><input type="text" name="tanggal_transaksi" size="15" value="<?php if(empty($_POST['tanggal_transaksi'])){ echo $tanggal;}else{ echo $_POST['tanggal_transaksi']; }?>"/>
                    <a href="javascript:void(0)" onClick="if(self.gfPop)gfPop.fPopCalendar(document.postform.tanggal_transaksi);return false;" ><img src="../images/calendarIcon.jpg" alt="" name="popcal" width="20" height="20" border="0" align="absmiddle" id="popcal" /></a></td>
                </tr>
                <tr>
                  <td height="31" nowrap bgcolor="#B3B3FF"><span class="style10">Keterangan Jurnal </span></td>
				  <td width="10" nowrap>   <div align="center"><strong>:</strong></div></td>
                  <td colspan="2" nowrap><input type="text" value="<?php echo $_POST['keterangan_jurnal'];?>" name="keterangan_jurnal" size="45"/></td>
                </tr>
				
				<tr><td colspan="2" nowrap></td></tr>
				
                <tr>
                  <td class="style10"  nowrap bgcolor="#B3B3FF">Rekening</td>
				  <td width="10" nowrap><div align="center"><strong>:</strong></div></td>
                  <td width="73">
				  <div id="suggest">
				  
					   <input type="text" onKeyUp="suggest(this.value);" name="kode_rekening"  onBlur="fill2();" id="kode" size="15"/> 
					   <div class="suggestionsBox" id="suggestions" style="display: none;"> <img src="arrow.png" style="position: relative; top: -12px; left: 30px;" alt="List coa" />
					   <div class="suggestionList" id="suggestionsList"> &nbsp; </div>
					   </div>
				  </div>
				  </td>
                  <td width="292" align="left"><input type="text" disabled="disabled" name="nama_rekening" onBlur="fill();" id="country"  size="30"/></td>
                  <td width="292" align="left">&nbsp;</td>
                </tr>
				
                <tr>
                  <td height="25" class="style10"  nowrap bgcolor="#B3B3FF">Keterangn Transaksi </td>
				  <td width="10" nowrap>   <div align="center"><strong>:</strong></div></td>
                  <td colspan="2">
				  <input type="keterangan_transaksi" value="<?php echo $_POST['keterangan_jurnal'];?>" name="keterangan_transaksi" size="45">
				  </td>
                </tr>
				  <td></td> </td>
				<tr>
                  <td height="21" class="style10"  nowrap bgcolor="#B3B3FF">Jumlah (Rp)</td>
				   <td width="10" nowrap><div align="center"><strong>:</strong></div></td>
                  <td colspan="2"><input type="text" name="jumlah_dk" size="15" autocomplete="off" onKeyUp="document.getElementById('format').innerHTML = formatCurrency(this.value);"/>
   				 <span id="format"></span></td>
                </tr>
<tr>
  <td height="10" colspan="4">&nbsp;</td>
  </tr>
<tr bgcolor="#B3B3FF">
  <td height="10">&nbsp; </td>
  <td><div align="center"></div></td>

                  <td><input type="submit" value="Simpan" name="simpan"></td>
                  <td colspan="2">&nbsp;</td>
                </tr>
              </table>
			</form>
			<br />
			
			
			<?php
			//untuk menyimpan transaksi
			if(isset($_POST['simpan'])){
		
				$kode_transaksi=$_POST['kode_bukti'];
				$tanggal_transaksi=$_POST['tanggal_transaksi'];
				$keterangan_transaksi=mysql_real_escape_string($_POST['keterangan_transaksi']);
				$kode_rekening=$_POST['kode_rekening'];

				$keterangan_jurnal=mysql_real_escape_string($_POST['keterangan_jurnal']);
				
				//untuk jurnal kas keluar, semua transaksi ada sisi DEBET
				$jumlah_dk=ucwords($_POST['jumlah_dk']);
				
				
				$query=mysql_query("insert into tabel_transaksi_toko(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
									values('$unit','$kode_transaksi','$kode_rekening','$tanggal_transaksi','Kas Masuk','$keterangan_transaksi','$jumlah_dk','$userid')");
									
				if($query){
					//echo "berhasil";
				}else{
					echo mysql_error();
				}
				
			
			}else{
				unset($_POST['simpan']);
			}
			
			//untuk menyelesaikan transaksi
			if(isset($_POST['selesai'])){
			
				$kode_transaksi=$_POST['kode_bukti'];
				$nomor_jurnal=$_POST['nomor_jurnal'];
				$tgl_transaksi=$_POST['tanggal_selesai'];
				$tot_kredit=$_POST['tot_kredit'];
				$keterangan_jurnal=$_POST['keterangan_jurnal'];
				
				//karena kas keluar lawan transaksinya adalah kas, maka kas adlah 111.01 yaitu kas unit umum yang ada di sisi KREDIT.
				$kode_rekening="1120000";
				
				$query_transaksi=mysql_query("insert into tabel_transaksi_toko(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
									values('$unit','$kode_transaksi','$kode_rekening','$tgl_transaksi','Kas Masuk','$keterangan_jurnal','$tot_kredit','$userid')");
									
				$query_jurnal=mysql_query("insert into jurnal_masuk(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values(' ','$kode_transaksi','$tgl_transaksi','$unit')");
									
				if($query_transaksi){
					?><script language="javascript">document.location.href="?<?php echo paramEncrypt('page=../transaksi/kas_masuk')?>"</script><?php
				}else{
					echo mysql_error();
				}
				
				
			}else{
				unset($_POST['selesai']);
			}
			
			//untuk mendecode url yang di enrypsi
			$var=decode($_SERVER['REQUEST_URI']);
	
			//pecahkan nilai array
			$mode=$var['mode'];
			$id_transaksi=$var['id_transaksi'];
			
			if($mode=='delete'){
				$query=mysql_query("delete from tabel_transaksi_toko where id_transaksi='$id_transaksi'");
			}else{
				echo mysql_error();
			}
			
			
			//untuk menampilkan transaksi
			?>
			<div class = "panel panel-default">
	  		<table width="100%" border="1" bordercolor="669900" style="margin: 7px 10px" class="table table-striped" >
			<tr>
				<th>Kode Rekening</th><th>Keterangan</th><th>Kredit</th><th>Action</th>
			</tr>
			<?php
			$query=mysql_query("select * from tabel_transaksi_toko where kode_transaksi='$kode_transaksi' and id_admin='$userid'");
			while($row=mysql_fetch_array($query)){
				$kredit=$row['kredit'];
				$id_transaksi=$row['id_transaksi'];
				$tgl_transaksi=$row['tanggal_transaksi'];
				$tot_kredit=$tot_debet+$kredit;
				
				?>
				<tr>
					<td><?php echo $row['kode_rekening'];?></td>
					<td><?php echo $row['keterangan_transaksi'];?></td>
					<td align="right"><?php if($kredit!=="0"){echo number_format(compat_num($kredit),2,'.',',');}; ?></td>
					<td align="center"><a href="?<?php echo paramEncrypt('page=../transaksi/kas_masuk&mode=delete&id_transaksi='.$id_transaksi)?>" onClick="return confirm('Apakah Anda yakin?')">Batal</a></td>
				</tr>
				<?php
			}
			?>
			<tr>
				<td colspan="2" align="center"><b>TOTAL</b></td><td align="right"><b><?php echo number_format(compat_num($tot_debet),2,'.',','); ?></b></td><td align="center"></td>
			</tr>
			</table>
			<br />			
			<form action="?<?php echo paramEncrypt('page=../transaksi/kas_masuk')?>" method="post" name="form">
				<input type="hidden" name="tanggal_selesai" size="15" value="<?php if(empty($_POST['tanggal_transaksi'])){ echo $tanggal;}else{ echo $_POST['tanggal_transaksi']; }?>"/>
				<input type="hidden" name="kode_bukti" value="<?php echo $kode_transaksi;?>">
				<input type="hidden" name="nomor_jurnal" value="<?php echo $nomor_jurnal;?>">
				<input type="hidden" name="tot_kredit" value="<?php echo $tot_kredit;?>">
				<input type="hidden" name="keterangan_jurnal" value="<?php echo $keterangan_jurnal;?>">
				<input type="hidden" name="tgl_transaksi" value="<?php echo $tgl_transaksi;?>">

				<input type="submit" onClick="return confirm('Apakah Anda Yakin?')" value="Selesai" name="selesai"/>
			</form>
			


			</p>
		</div>
	</div>
	</div>
	</body>
	
	<iframe width=174 height=189 name="gToday:normal:../calender/agenda.js" id="gToday:normal:../calender/agenda.js" src="../calender/ipopeng.htm" scrolling="no" frameborder="0" style="visibility:visible; z-index:999; position:absolute; top:-500px; left:-500px;"></iframe>

