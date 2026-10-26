<?php require_once __DIR__ . '/../php8_compat.php';
session_start();
ob_start();
$userid=$_SESSION['userid'];
if(!isset($_SESSION['userid'])){
   header("location:index.html");//jika belum login jangan lanjut..
}


	include "../encryption/function.php";
	
	//untuk waktu
	$tanggal=date("y-m-d");
	
	//untuk koneksi database
	include "../conn2.php";
	$koneksi=open_connection2();
	$userrr=mysql_query("select kode_branch from user where userid='$userid'");
			
			while($id_user=mysql_fetch_array($userrr)){
			
				$unit=$id_user['kode_branch'];
			}
		
	$query_tanggal=mysql_fetch_array(mysql_query("select min(tanggal_posting) as tanggal_pertama from tabel_transaksi"));
	$tanggal_pertama=$query_tanggal['tanggal_pertama'];
?>
		<script type="text/javascript" src="../jquery.js"></script>
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
.style12 {font-size: 9px}
        </style>

<table width="100%">
  <tr bgcolor="#669900">
  <td height="30" colspan="4" nowrap><div align="left" class="style9 style3 style1 style1"><strong>Laporan Neraca </strong></div></td>
  </tr>
</table>
<p>
<div class="post">
	<div class="entry">

		<form action="?<?php echo paramEncrypt('page=../laporan/neraca')?>" method="post" name="postform">
		
		  <table width="100%" border="0">
			<tr>
			  <td width="91"><strong>Periode</strong></td>
			  <td colspan="2"><input type="text" name="tanggal1" size="15" value="<?php if(empty($_POST['tanggal1'])){ echo $tanggal_pertama;}else{ echo $_POST['tanggal1']; }?>"/>
			  <a href="javascript:void(0)" onClick="if(self.gfPop)gfPop.fPopCalendar(document.postform.tanggal1);return false;" ><img src="../images/calendarIcon.jpg" alt="" name="popcal" width="20" height="20" border="0" align="absmiddle" id="popcal" /></a></td>
			  <td width="40"><strong>S/D</strong></td>
			  <td colspan="2"><input type="text" name="tanggal2" size="15" value="<?php if(empty($_POST['tanggal2'])){ echo $tanggal;}else{ echo $_POST['tanggal2']; }?>"/>
			  <a href="javascript:void(0)" onClick="if(self.gfPop)gfPop.fPopCalendar(document.postform.tanggal2);return false;" ><img src="../images/calendarIcon.jpg" alt="" name="popcal" width="20" height="20" border="0" align="absmiddle" id="popcal" /></a></td>
			  <td width="102">
			  <select name="neraca">
					<option value="aktiva">Neraca Aktiva</option>
					<option value="pasiva">Neraca Pasiva</option>
					<option value="neraca">Neraca </option>
			  </select>
			  </td>
			  <td width="154"><input type="submit" name="report" value="Tampilkan" /></td>
			  <input type="hidden" name="unit" value="<?php echo $unit;?>">
			</tr>
		  </table>
		</form>
	</div>
</div>
</div>

	<img src="../excel-icon.jpeg" width="16" height="16" />
	excel <img src="../pdf-icon.jpg" width="16" height="16" /> pdf
	  <button onClick="window.print();">Print</button> 
    <div class="post">
		<div class="entry">
		<p>
		<?php
		//untuk menyelesaikan transaksi
		if(isset($_POST['report'])){
			
			//tanggal periode laporan
			$tanggal1=$_POST['tanggal1'];
			$tanggal2=$_POST['tanggal2'];
			$neraca=$_POST['neraca'];
		
			function aktiva(){
			global $unit;
			

				///////////////////HITUNG AKTIVA////////////////////
				//hapus table semporial yang lama
				$hapus_neraca_temporial=mysql_query("delete from tabel_neraca where unit='$unit'");
				
				if($hapus_neraca_temporial){
					//Query aktiva LANCAR
					$query_kas=mysql_fetch_array(mysql_query("SELECT sum(sisa_debet)- sum(sisa_kredit) AS kas FROM tabel_master WHERE gr = '1' and unit ='$unit'"));
					$query_giroy=mysql_fetch_array(mysql_query("SELECT sum(sisa_debet)- sum(sisa_kredit) AS giro_syariah FROM tabel_master WHERE gr ='2' and unit ='$unit'"));
					$query_girok=mysql_fetch_array(mysql_query("SELECT sum(sisa_debet)- sum(sisa_kredit) AS girok from tabel_master WHERE gr='3' and unit ='$unit'"));
					$query_depo_ib=mysql_fetch_array(mysql_query("SELECT sum(sisa_debet) - sum(sisa_kredit) AS depo_ib FROM tabel_master WHERE gr='5' and unit ='$unit'"));
					$query_simpanan_kspp=mysql_fetch_array(mysql_query("SELECT sum(sisa_debet) - sum(sisa_kredit) AS simpanan_kspps FROM tabel_master WHERE gr='4' and unit ='$unit'"));
					$query_surat_harga=mysql_fetch_array(mysql_query("SELECT sum(sisa_debet) - sum(sisa_kredit) AS surat_harga FROM tabel_master WHERE gr='6' and unit ='$unit'"));
					$query_piutang_murab=mysql_fetch_array(mysql_query("SELECT sum(sisa_debet) - sum(sisa_kredit) AS piutang_murab FROM tabel_master WHERE gr='7' and unit ='$unit'"));
					$query_pmyd=mysql_fetch_array(mysql_query("SELECT sum(sisa_debet) - sum(sisa_kredit) AS pmyd FROM tabel_master WHERE gr='8' and unit ='$unit'"));
					$query_Piutang_Wakalah=mysql_fetch_array(mysql_query("SELECT sum(sisa_debet) - sum(sisa_kredit) AS Piutang_Wakalah FROM tabel_master WHERE gr='9' and unit ='$unit'"));
					$query_Piutang_istishna=mysql_fetch_array(mysql_query("SELECT sum(sisa_debet) - sum(sisa_kredit) AS Piutang_istishna FROM tabel_master WHERE gr='10' and unit ='$unit'"));
					$query_PMIYD_Piutang_istishna=mysql_fetch_array(mysql_query("SELECT sum(sisa_debet) - sum(sisa_kredit) AS PMIYD_Piutang_istishna FROM tabel_master WHERE gr='11' and unit ='$unit'"));
					$query_Piutang_Qardul_Hasan=mysql_fetch_array(mysql_query("SELECT sum(sisa_debet) - sum(sisa_kredit) AS Piutang_Qardul_Hasan FROM tabel_master WHERE gr='12' and unit ='$unit'"));
					$query_Pembiayaan_Musyarakah=mysql_fetch_array(mysql_query("SELECT sum(sisa_debet) - sum(sisa_kredit) AS Pembiayaan_Musyarakah FROM tabel_master WHERE gr='13' and unit ='$unit'"));
					$query_persediaan=mysql_fetch_array(mysql_query("SELECT sum(sisa_debet) - sum(sisa_kredit) AS persediaan FROM tabel_master WHERE gr='14' and unit ='$unit'"));
					$query_pembiayaan_ijaroh=mysql_fetch_array(mysql_query("SELECT sum(sisa_debet) - sum(sisa_kredit) AS pembiayaan_ijaroh FROM tabel_master WHERE gr='15' and unit ='$unit'"));
					$query_penyusutan_ijaroh=mysql_fetch_array(mysql_query("SELECT sum(sisa_debet) - sum(sisa_kredit) AS penyusutan_ijaroh FROM tabel_master WHERE gr='16' and unit ='$unit'"));
					$query_pernyataan=mysql_fetch_array(mysql_query("SELECT sum(sisa_debet) - sum(sisa_kredit) AS pernyataan FROM tabel_master WHERE gr='17' and unit ='$unit'"));
					$query_ppap_cadangan_umum=mysql_fetch_array(mysql_query("SELECT sum(sisa_debet) - sum(sisa_kredit) AS ppap_cadangan_umum FROM tabel_master WHERE gr='18' and unit ='$unit'"));
					$query_ppap_cadangan_khusus=mysql_fetch_array(mysql_query("SELECT sum(sisa_debet) - sum(sisa_kredit) AS ppap_cadangan_khusus FROM tabel_master WHERE gr='19' and unit ='$unit'"));
					$query_inventasi_tanah=mysql_fetch_array(mysql_query("SELECT sum(sisa_debet) - sum(sisa_kredit) AS inventasi_tanah FROM tabel_master WHERE gr='20' and unit ='$unit'"));
					$query_nyusut_ati=mysql_fetch_array(mysql_query("SELECT sum(sisa_debet) - sum(sisa_kredit) AS inventasi_ati FROM tabel_master WHERE gr='21' and unit ='$unit'"));
					$query_ATI=mysql_fetch_array(mysql_query("SELECT sum(sisa_debet) - sum(sisa_kredit) AS ati FROM tabel_master WHERE gr='22' and unit ='$unit'"));
					$query_ati_api=mysql_fetch_array(mysql_query("SELECT sum(sisa_debet) - sum(sisa_kredit) AS ati_api FROM tabel_master WHERE gr='23' and unit ='$unit'"));
					$query_rak=mysql_fetch_array(mysql_query("SELECT sum(sisa_debet) - sum(sisa_kredit) AS rak FROM tabel_master WHERE gr='24' and unit ='$unit'"));
					$query_rupa_rupa_aktiva=mysql_fetch_array(mysql_query("SELECT sum(sisa_debet) - sum(sisa_kredit) AS rupa_rupa_aktiva FROM tabel_master WHERE gr='25' and unit ='$unit'"));
					$query_um=mysql_fetch_array(mysql_query("SELECT sum(sisa_debet) - sum(sisa_kredit) AS um FROM tabel_master WHERE gr='26' and unit ='$unit'"));
					$query_bdb=mysql_fetch_array(mysql_query("SELECT sum(sisa_debet) - sum(sisa_kredit) AS bdb FROM tabel_master WHERE gr='27' and unit ='$unit'"));
					$query_persediaan_pos_tag=mysql_fetch_array(mysql_query("SELECT sum(sisa_debet) - sum(sisa_kredit) AS persediaan_pos_tag FROM tabel_master WHERE gr='28' and unit ='$unit'"));
					$query_praor_amor=mysql_fetch_array(mysql_query("SELECT sum(sisa_debet) - sum(sisa_kredit) AS praor_amor FROM tabel_master WHERE kode_rekening='1991000' and unit ='$unit'"));
					$query_amor=mysql_fetch_array(mysql_query("SELECT sum(sisa_debet) - sum(sisa_kredit) AS amor FROM tabel_master WHERE kode_rekening='1992000' and unit ='$unit'"));
					$query_deferred=mysql_fetch_array(mysql_query("SELECT sum(sisa_debet) - sum(sisa_kredit) AS deferred FROM tabel_master WHERE kode_rekening='1999000' and unit ='$unit'"));

					

					//Array Query
					$kas=$query_kas['kas'];
					$bank1=$query_giroy['giro_syariah'];
					$bank2=$query_girok['girok'];
					$depo_ib=$query_depo_ib['depo_ib'];
					$simpanan_kspp=$query_simpanan_kspp['simpanan_kspps'];
					$surat_harga=$query_surat_harga['surat_harga'];
					$piutang_murab=$query_piutang_murab['piutang_murab'];
					$pmyd=$query_pmyd['pmyd'];
					$Piutang_Wakalah=$query_Piutang_Wakalah['Piutang_Wakalah'];
					$Piutang_istishna=$query_Piutang_istishna['Piutang_istishna'];
					$PMIYD_Piutang_istishna=$query_PMIYD_Piutang_istishna['PMIYD_Piutang_istishna'];
					$Piutang_Qardul_Hasan=$query_Piutang_Qardul_Hasan['Piutang_Qardul_Hasan'];
					$Pembiayaan_Musyarakah=$query_Pembiayaan_Musyarakah['Pembiayaan_Musyarakah'];
					$persediaan=$query_persediaan['persediaan'];
					$pembiayaan_ijaroh=$query_pembiayaan_ijaroh['pembiayaan_ijaroh'];
					$penyusutan_ijaroh=$query_penyusutan_ijaroh['penyusutan_ijaroh'];
					$pernyataan=$query_pernyataan['pernyataan'];
					$ppap_cadangan_umum=$query_ppap_cadangan_umum['ppap_cadangan_umum'];
					$cadangan_khusus=$query_ppap_cadangan_khusus['ppap_cadangan_khusus'];
					$inventasi_tanah=$$query_inventasi_tanah['inventasi_tanah'];
					$nyusut_ati=$query_nyusut_ati['nyusut_ati'];
					$ati_api=$query_ati_api['ati_api'];
					$ATI=$query_ATI['ATI'];
					$rak=$query_rak['rak'];
					$rupa_rupa_aktiva=$query_rupa_rupa_aktiva['rupa_rupa_aktiva'];
					$um=$query_um['um'];
					$bdb=$query_bdb['bdb'];
					$persediaan_pos_tag=$query_persediaan_pos_tag['persediaan_pos_tag'];
					$praor_amor=$query_praor_amor['praor_amor'];
					$amor=$query_amor['amor'];
					$deferred=$query_deferred['deferred'];
					
						//porses penjumlahan Aktiva Lancar 
					$total_aktiva_lancar=$kas+$bank1+$bank2+$depo_ib+$simpanan_kspp+$surat_harga+$piutang_murab+$pmyd+$Piutang_Wakalah+$Piutang_istishna+$PMIYD_Piutang_istishna+$Piutang_Qardul_Hasan+$Pembiayaan_Musyarakah+$persediaan+$pembiayaan_ijaroh+$penyusutan_ijaroh+$pernyataan+$ppap_cadangan_umum+$cadangan_khusus+$rupa_rupa_aktiva+$um+$bdb+$persediaan_pos_tag;
					//Porses penjumlahan Aktiva Tetap 
					$tot_aktv_tetap=$inventasi_tanah+$nyusut_ati+$ati_api;
					//Porses penjumlahan Aktiva LAINNYA
					$tot_aktv_lain=$praor_amor+$amor+$deferred;				
					//Proses Penjumlahan Total AKTIVA
					$total_aktiva=$total_aktiva_lancar+$tot_aktv_tetap+$tot_aktv_lain;
					
					//Tahap penginputan Data
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening,unit) values('$unit 01','<b>I.&nbsp AKTIVA LANCAR</b>','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening,awal_debet,unit) values('$unit 02','&nbsp 1. KAS','$kas','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening) values('$unit 03','&nbsp 2. PENEMATAN PADA BANK LAIN (RPBL)','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening, awal_debet,unit) values('$unit 04','&nbsp &nbsp a. &nbsp PPBL-Giro iB Pada Bank Syariah', '$bank1','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening, awal_debet,unit) values('$unit 05','&nbsp &nbsp b. &nbsp PPBL-Giro iB Pada Bank Konvensional', '$bank2','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening, awal_debet,unit) values('$unit 06','&nbsp &nbsp c. &nbsp PPBL-Deposito iB', '$depo_ib','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening, awal_debet,unit) values('$unit 07','&nbsp 3. SIMPANAN PADA KSPPS LAIN', '$simpanan_kspp','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening, awal_debet,unit) values('$unit 08','&nbsp 4. SURAT BERHARGA YANG DIMILIKI (SBYD)', '$surat_harga','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening, awal_debet,unit) values('$unit 09','&nbsp 5. PEMBIAYAAN PIUTANG MURABAHAH', '$piutang_murab','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening, awal_debet,unit) values('$unit 10',&nbsp 6. PENDAPATAN MARGIN YG DITANGGUHKAN (PMYD)', '$pmyd','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening, awal_debet,unit) values('$unit 11','&nbsp 7. PEMBIAYAAN PIUTANG WAKALAH', '$Piutang_Wakalah','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening, awal_debet,unit) values('$unit 12','&nbsp 8. PEMBIAYAAN PIUTANG ISTISHNA', '$Piutang_istishna','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening, awal_debet,unit) values('$unit 13','&nbsp 9. PDPTN MARGIN ISTISHNA YG DITANGGUHKAN (PMIYD)', '$PMIYD_Piutang_istishna','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening, awal_debet,unit) values('$unit 14','&nbsp 10. PEMBIAYAAN PIUTANG QARDUL HASAN','$Piutang_Qardul_Hasan','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening, awal_debet,unit) values('$unit 15','&nbsp 11. PEMBIAYAAN YANG DIBERIKAN', '$Pembiayaan_Musyarakah','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening, awal_debet,unit) values('$unit 16','&nbsp 12. PERSEDIAAN', '$persediaan','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening, awal_debet,unit) values('$unit 17','&nbsp 13. PEMBIAYAAN IJARAH', '$pembiayaan_ijaroh','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening, awal_debet,unit) values('$unit 18','&nbsp 14. AKUMULASI PENYUSUTAN AKTIVA IJARAH -/-', '$penyusutan_ijaroh','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening, awal_debet,unit) values('$unit 19','&nbsp 15. PENYERTAAN', '$pernyataan','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening, awal_debet,unit) values('$unit 20','&nbsp 16. PPA-CADANGAN UMUM', '$ppap_cadangan_umum','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening, awal_debet,unit) values('$unit 21','&nbsp 17. PPA-CADANGAN KHUSUS', '$cadangan_khusus','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening, awal_debet,unit) values('$unit 22','&nbsp 18. PENDAPATAN YANG AKAN DITERIMA', '$rupa_rupa_aktiva','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening, awal_debet,unit) values('$unit 23','&nbsp 19. UANG MUKA', '$um','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening, awal_debet,unit) values('$unit 24','&nbsp 20. BIAYA DIBAYAR DIMUKA', '$bdb','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening, awal_debet,unit) values('$unit 25','&nbsp 21. RUPA-RUPA AKTIVA LAINNYA', '$persediaan_pos_tag','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening,unit,unit) values('$unit 26','break','$unit')");
				
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening,awal_debet,unit) values('$unit 27','<b> &nbsp JUMLAH AKTIVA LANCAR</b>', '$total_aktiva_lancar','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening,unit) values('$unit 28','break','$unit')");
					
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening,unit) values('$unit 29','<b>II. &nbsp INVENTARIS JANGKA PANJANG</b>','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening,unit, awal_debet) values('$unit 30','&nbsp 1. INVESTASI PADA KSPPS LAINNYA', '$persediaan_toko','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening,unit) values('$unit 31','&nbsp 2. INVESTASI PADA SAHAM', '$persediaan_toko','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening,unit, awal_debet) values('$unit 32','&nbsp 3. INVESTASI LAINNYA', '$persediaan_toko','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening,unit) values('$unit 33','break','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening,awal_debet,unit) values('$unit 34','<b>&nbsp JUMLAH JANGKA PANJANG</b>', '$persediaan_toko','$unit')");
								
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening,unit) values('$unit 35','<b>III. &nbsp AKTIVA TETAP</b>','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening, awal_debet,unit) values('$unit 36','&nbsp 1. AKTIVA TETAP DAN INVENTARIS-TANAH/GEDUNG', '$inventasi_tanah','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening,unit) values('$unit 38','2. AKUMULASI PENYUSUTAN ATI-GEDUNG', '$nyusut_ati','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening,unit, awal_debet) values('$unit 39','&nbsp 3. AKTIVA TETAP DAN INVENTARIS-INVENTARIS', '$ATI','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening, awal_debet,unit) values('$unit 40','&nbsp 4. AKUMULASI PENYUSUTAN ATI-INVENTARIS', '$ati_api','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening,unit) values('$unit 41','break','$unit')");
					
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening,awal_debet,unit) values('$unit 42','<b>&nbsp JUMLAH AKTIVA TETAP</b>', '$tot_aktv_tetap','$unit')");
				
					
					
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening,unit) values('$unit 43','<b>IV. &nbsp AKTIVA LAIN-LAIN</b>','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening, awal_debet,unit) values('$unit 44','&nbsp 1. BIAYA PRA OPERASIONAL', '$praor_amor','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening,unit) values('$unit 45','&nbsp 2. AMORTISASI BIAYA PRA OPRASIONAL', '$amor','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening, awal_debet,unit) values('$unit 46','&nbsp 3. DEFFERED ACCOUNT', '$deferred','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening,unit) values('$unit 47','break','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening,awal_debet,unit) values('$unit 48','<b>&nbsp JUMLAH AKTIVA LAIN-LAIN</b>', '$tot_aktv_lain','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening,unit) values('$unit 49','break','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening,awal_debet,unit) values('$unit 50','<b>TOTAL AKTIVA</b>', '$total_aktiva','$unit')");
					

					
					
					
					
					
					
					
										
					$query_report=mysql_query("select * from tabel_neraca where unit ='$unit' order by kode_rekening ");
					
					?>
		  <div class="container">
					<h3 align="left"><b>Neraca (Balance Sheet)</b></h3><br />
					<font color="#333333">Periode : <?php echo $tanggal2=$_POST['tanggal2']; ?></font>
					<h3 align="left"><b>AKTIVA</b></h3><br />
				
					<table width="100%" border="1" bordercolor="669900" class="table table-striped table-bordered"			
					<tr>
					<th>URAIAN</th>
					<th>NILAI</th>

					</tr>
		  </div>
					<?php
					while($row=mysql_fetch_array($query_report)){
						$nama_rekening=$row['nama_rekening'];
						$awal_debet=number_format(compat_num($row['awal_debet']),2,'.',',');
						
						if($nama_rekening=='break'){
							?><tr><td colspan="2"></td></tr><?php
						}else{
							?><tr><td><?php echo $nama_rekening ?></td><td align="right"><?php echo $awal_debet;?></span></td>
					</tr><?php
						}
					}
					?>
		  </table>
					<?php
					
				}else{
					echo mysql_error();
				}
			}
				 
			function pasiva(){
				global $unit;
				///////////////////HITUNG PASIVA////////////////////
				//hapus table semporial yang lama
				$hapus_neraca_temporial=mysql_query("delete from tabel_neraca where unit ='$unit'");
				
				if($hapus_neraca_temporial){
					 //kewajiban
					$query_simpanan_wadiah=mysql_fetch_array(mysql_query("SELECT sum(sisa_kredit) - sum(sisa_debet) AS simpanan_wadiah FROM tabel_master WHERE gr='30' and unit ='$unit'"));
					$query_simpanan_mantap=mysql_fetch_array(mysql_query("SELECT sum(sisa_kredit) - sum(sisa_debet) AS simpanan_mantap FROM tabel_master WHERE gr='31' and unit ='$unit'"));
					$query_Simpanan_berjangka =mysql_fetch_array(mysql_query("SELECT sum(sisa_kredit)- sum(sisa_debet) AS Simpanan_berjangka  FROM tabel_master WHERE gr='32' and unit ='$unit'"));
					$query_sgu=mysql_fetch_array(mysql_query("SELECT sum(sisa_kredit)- sum(sisa_debet) AS sgu FROM tabel_master WHERE gr='33' and unit ='$unit'"));
					$query_pembiayaan_bank=mysql_fetch_array(mysql_query("SELECT sum(sisa_kredit)- sum(sisa_debet) AS pembiayaan_bank FROM tabel_master WHERE gr='34' and unit ='$unit'"));
					$query_pembiayaan_orang=mysql_fetch_array(mysql_query("SELECT sum(sisa_kredit)- sum(sisa_debet) AS pembiayaan_orang FROM tabel_master WHERE gr='35' and unit ='$unit'"));
					$query_lpdb=mysql_fetch_array(mysql_query("SELECT sum(sisa_kredit)- sum(sisa_debet) AS lpdb FROM tabel_master WHERE gr='36' and unit ='$unit'"));
					$query_sewa_guna_usaha=mysql_fetch_array(mysql_query("SELECT sum(sisa_kredit)- sum(sisa_debet) AS sewa_guna_usaha FROM tabel_master WHERE gr='37' and unit ='$unit'"));
					$query_pydt_bank=mysql_fetch_array(mysql_query("SELECT sum(sisa_kredit)- sum(sisa_debet) AS pydt_bank FROM tabel_master WHERE gr='38' and unit ='$unit'"));
					$query_pydt_orang=mysql_fetch_array(mysql_query("SELECT sum(sisa_kredit)- sum(sisa_debet) AS pydt_orang FROM tabel_master WHERE gr='39' and unit ='$unit'"));
					$query_lpdb_lain=mysql_fetch_array(mysql_query("SELECT sum(sisa_kredit)- sum(sisa_debet) AS lpdb_lain FROM tabel_master WHERE gr='40' and unit ='$unit'"));
					$query_ksd=mysql_fetch_array(mysql_query("SELECT sum(sisa_kredit)- sum(sisa_debet) AS ksd FROM tabel_master WHERE gr in ('41','42') and unit ='$unit'"));
					$query_wajib_bayar=mysql_fetch_array(mysql_query("SELECT sum(sisa_kredit)- sum(sisa_debet) AS wajib_bayar FROM tabel_master WHERE gr ='43' and unit ='$unit'"));
					$query_wajib_lainnya=mysql_fetch_array(mysql_query("SELECT sum(sisa_kredit)- sum(sisa_debet) AS wajib_lainnya FROM tabel_master WHERE gr ='44' and unit ='$unit'"));
									
					//menghitung modal
					$query_s_pokok=mysql_fetch_array(mysql_query("SELECT sum(sisa_kredit)- sum(sisa_debet) AS simpanan_poko FROM tabel_master WHERE gr='45' and unit ='$unit'"));
					$query_s_wajib=mysql_fetch_array(mysql_query("SELECT sum(sisa_kredit)- sum(sisa_debet) AS sw FROM tabel_master WHERE gr='46' and unit ='$unit'"));
					$query_s_wajib_khusus=mysql_fetch_array(mysql_query("SELECT sum(sisa_kredit)- sum(sisa_debet) AS swk FROM tabel_master WHERE gr='47' and unit ='$unit'"));
					$query_modal_penyetaraan=mysql_fetch_array(mysql_query("SELECT sum(sisa_kredit)- sum(sisa_debet) AS modal_penyetaraan FROM tabel_master WHERE gr='48' and unit ='$unit'"));
					$query_modal_penyertaan=mysql_fetch_array(mysql_query("SELECT sum(sisa_kredit)- sum(sisa_debet) AS modal_penyertaan FROM tabel_master WHERE gr='49' and unit ='$unit'"));
					$query_modal_sumbang=mysql_fetch_array(mysql_query("SELECT sum(sisa_kredit)- sum(sisa_debet) AS modal_sumbang FROM tabel_master WHERE gr='50' and unit ='$unit'"));
					$query_selisih_penilaian=mysql_fetch_array(mysql_query("SELECT sum(sisa_kredit)- sum(sisa_debet) AS selisih_penilaian FROM tabel_master WHERE gr='51' and unit ='$unit'"));
					$query_cadangan=mysql_fetch_array(mysql_query("SELECT sum(sisa_kredit)- sum(sisa_debet) AS cadangan FROM tabel_master WHERE gr='52' and unit ='$unit'"));
					$query_shu_lalu=mysql_fetch_array(mysql_query("SELECT sum(sisa_kredit)- sum(sisa_debet) AS shu_lalu FROM tabel_master WHERE gr='53' and unit ='$unit'"));
					$query_shu_jalan=mysql_fetch_array(mysql_query("SELECT sum(sisa_kredit)- sum(sisa_debet) AS shu_jalan FROM tabel_master WHERE gr='55' and unit ='$unit'"));
										
					//Array Query
					$impanan_wadiah=$query_simpanan_wadiah['impanan_wadiah'];
					$simpanan_mantap=$query_simpanan_mantap['simpanan_mantap'];
					$Simpanan_berjangka=$query_Simpanan_berjangka['Simpanan_berjangka'];
					$pembiayaan_bank=$query_pembiayaan_bank['pembiayaan_bank'];
					$pembiayaan_orang=$query_pembiayaan_orang['pembiayaan_orang'];
					$sgu=$query_sgu['sgu'];
					$lpdb=$query_lpdb['lpdb'];
					$sewa_guna_usaha=$query_sewa_guna_usaha['sewa_guna_usaha'];
					$pydt_bank=$query_pydt_bank['pydt_bank'];
					$pydt_orang=$query_pydt_orang['pydt_orang'];
					$wajib_bayar=$query_wajib_bayar['wajib_bayar'];
					$lpdb_lain=$query_lpdb_lain['lpdb_lain'];
					
					//total aktiva lancar
					$total_lancar=$impanan_wadiah+$simpanan_mantap+$Simpanan_berjangka+$sgu+$lpdb+$pydt_orang+$lpdb_lain+$ksd+$wajib_bayar+$wajib_lainnya;
					
					$ksd=$query_ksd['ksd'];
					$wajib_lainnya=$query_wajib_lainnya['wajib_lainnya'];
				
					//Array Query
					$s_pokok=$query_s_pokok['s_pokok'];
					$s_wajib=$query_s_wajib['sw'];
					$s_wajib_khusus=$query_s_wajib_khusus['s_wajib_khusus'];
					$modal_penyetaraan=$query_modal_penyetaraan['modal_penyetaraan'];
					$modal_penyertaan=$query_modal_penyertaan['modal_penyertaan'];
					
					//Array Query
					$modal_sumbang=$query_modal_sumbang['modal_sumbang'];
					$selisih_penilaian=$query_selisih_penilaian['selisih_penilaian'];	
					$cadangan=$query_cadangan['cadangan'];	
					$shu_lalu=$query_shu_lalu['shu_lalu'];	
					$shu_jalan=$query_shu_jalan['shu_jalan'];	
					//Proses Perhitungan
					$tot_ekuit=$swkp+$s_pokok+$s_wajib+$modal_penyetaraan+$modal_penyertaan+$modal_sumbang+$selisih_penilaian+$selisih_penilaian+$selisih_penilaian+$shu_bp;
					
					
					
					
					//Tahap penginputan Data
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening,unit) values('0','<b>I. &nbsp KEWAJIBAN LANCAR</b>','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening, awal_debet,unit) values('1',' &nbsp 1. &nbsp SIMPANAN WADIAH', '$impanan_wadiah','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening, awal_debet,unit) values('2',' &nbsp 2. &nbsp  SIMPANAN MUDHARABAH MUTHLAQAH', '$simpanan_mantap','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening, awal_debet,unit) values('3','&nbsp 3. &nbsp SIMA MUDHARABAH MUTHLAQAH', '$Simpanan_berjangka','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening, awal_debet,unit) values('4','&nbsp 4. &nbsp PEMBIAYAAN YANG DITERIMA - SGU (Bagian jatuh tempo < 1 tahun)', '$sgu','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening, awal_debet,unit) values('5','&nbsp 5. &nbsp PEMBIAYAAN YANG DITERIMA - BANK (Bagian jatuh tempo < 1 tahun)', '$pembiayaan_bank','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening, awal_debet,unit) values('6','&nbsp 6. &nbsp PINJAMAN YANG DITERIMA - PERORANGAN', '$pembiayaan_orang','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening, awal_debet,unit) values('7','&nbsp 7. &nbsp PEMBIAYAAN YANG DITERIMA - LPDB (Bagian jatuh tempo < 1 tahun)', '$lpdb','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening, awal_debet,unit) values('8','&nbsp 8. &nbsp KEWAJIBAN SEGERA DIBAYAR', '$ksd','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening, awal_debet,unit) values('9','&nbsp 9. &nbsp BEBAN YANG MASIH HARUS DIBAYAR', '$wajib_bayar','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening, awal_debet,unit) values('7','&nbsp 10. &nbsp KEWAJIBAN LAIN-LAIN', '$wajib_lainnya','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening,unit) values('49','break','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening, awal_debet,unit) values('26','<b>&nbsp JUMLAH KEWAJIBAN LANCAR<b>', '$total_lancar','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening,unit) values('59','break','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening,unit) values('10','<b>II. &nbsp KEWAJIBAN JANGKA PANJANG</b>','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening, awal_debet,unit) values('11','&nbsp 1. &nbsp SIMA MUDHARABAH MUTHLAQAH', '$Simpanan_berjangka','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening, awal_debet,unit) values('12','&nbsp 2. &nbsp PEMBIAYAAN YANG DITERIMA - SGU', '$sewa_guna_usaha','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening, awal_debet,unit) values('13','&nbsp 3. &nbsp PEMBIAYAAN YANG DITERIMA - BANK', '$pydt_bank','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening, awal_debet,unit) values('14','&nbsp 4. &nbsp PINJAMAN YANG DITERIMA - PERORANGAN', '$pydt_orang','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening, awal_debet,unit) values('15','&nbsp 5. &nbsp PEMBIAYAAN YANG DITERIMA - LPDB', '$lpdb_lain','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening,unit) values('13','break','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening, awal_debet,unit) values('27','<b>&nbsp JUMLAH KEWAJIBAN JANGKA PANJANG<b>', '$total_pasiva','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening,unit) values('33','break','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening) values('16','<b>III. &nbsp EKUITAS</b>','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening, awal_debet,unit) values('17','&nbsp 1. &nbsp SIMPANAN POKOK', '$swkp','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening, awal_debet,unit) values('18','&nbsp 2. &nbsp SIMPANAN WAJIB', '$s_pokok','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening, awal_debet,unit) values('19','&nbsp 3. &nbsp SIMPANAN WAJIB KHUSUS', '$s_wajib','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening, awal_debet,unit) values('20','&nbsp 4. &nbsp MODAL PENYETARAAN', '$modal_penyetaraan','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening, awal_debet,unit) values('21','&nbsp 5. &nbsp MODAL PENYERTAAN', '$modal_penyertaan','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening, awal_debet,unit) values('22','&nbsp 6. &nbsp MODAL SUMBANGAN/HIBAH', '$modal_sumbang','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening, awal_debet,unit) values('23','&nbsp 7. &nbsp SELISIH PENILAIAN KEMBALI AKTIVA TETAP', '$selisih_penilaian','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening, awal_debet,unit) values('24','&nbsp 8. &nbsp CADANGAN', '$selisih_penilaian','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening, awal_debet,unit) values('25','&nbsp 9. &nbsp SHU - TAHUN-TAHUN LALU BLM DIBAGI', '$selisih_penilaian','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening, awal_debet,unit) values('26','&nbsp 10. &nbsp SHU - TAHUN BERJALAN', '$shu_bp','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening,unit) values('66','break','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening, awal_debet,unit) values('3433','<b> JUMLAH EKUITAS</b>', '$shu_bp','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening,unit) values('45','break','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening, awal_debet,unit) values('87','<b>JUMLAH KEWAJIBA DAN EKUITAS</b>', '$shu_bp','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening,unit) values('77','break','$unit')");
					
					$query_report=mysql_query("select * from tabel_neraca where unit ='$unit'");
					
					?>
					<div class="container">
					<h3 align="left"><b>Neraca (Balance Sheet)</b></h3><br />
					<font color="#333333">Periode : <?php echo $tanggal2=$_POST['tanggal2']; ?></font>
					<h3 align="right"><b>PASIVA</b></h3><br />
					
			
					<table width="80%" border="1" bordercolor="669900" style="margin: 1px 1px" class="table table-striped" >
					<tr>
					<Th>URAIAN</th>
					<th>NILAI</th>
					</TR>
					<?php
					while($row=mysql_fetch_array($query_report)){
						$nama_rekening=$row['nama_rekening'];
						$awal_debet=number_format(compat_num($row['awal_debet']),2,'.',',');
						
						if($nama_rekening=='break'){
							?><tr><td colspan="2"></td></tr><?php
						}else{
							?><tr><td><?php echo $nama_rekening ?></td><td align="right"><?php echo $awal_debet;?></td></tr><?php
						}
					}
					?>
					</div>
				</table>
					
					
				<?php
				}else{
					echo mysql_error();
				}
			}
			function neraca(){
				global $unit;

				///////////////////HITUNG AKTIVA////////////////////
				//hapus table semporial yang lama
				$hapus_neraca_temporial=mysql_query("delete from tabel_neraca where unit ='$unit'");
				
				if($hapus_neraca_temporial){
					//Query aktiva LANCAR
					$query_kas=mysql_fetch_array(mysql_query("SELECT sum(sisa_debet) - sum(sisa_kredit) AS kas FROM tabel_master WHERE gr= '1' and unit ='$unit'"));
					$query_giroy=mysql_fetch_array(mysql_query("SELECT sum(sisa_debet)  - sum(sisa_kredit)AS giro_syariah FROM tabel_master WHERE gr='2' and unit ='$unit'"));
					$query_girok=mysql_fetch_array(mysql_query("SELECT sum(sisa_debet)  - sum(sisa_kredit) AS girok from tabel_master WHERE gr='3' and unit ='$unit'"));
					$query_depo_ib=mysql_fetch_array(mysql_query("SELECT sum(sisa_debet) - sum(sisa_kredit) AS depo_ib FROM tabel_master WHERE gr='5' and unit ='$unit'"));
					$query_simpanan_kspp=mysql_fetch_array(mysql_query("SELECT sum(sisa_debet) - sum(sisa_kredit) AS simpanan_kspps FROM tabel_master WHERE gr='4' and unit ='$unit'"));
					$query_surat_harga=mysql_fetch_array(mysql_query("SELECT sum(sisa_debet) - sum(sisa_kredit) AS surat_harga FROM tabel_master WHERE gr='6' and unit ='$unit'"));
					$query_piutang_murab=mysql_fetch_array(mysql_query("SELECT sum(sisa_debet) - sum(sisa_kredit) AS piutang_murab FROM tabel_master WHERE gr='7' and unit ='$unit'"));
					$query_pmyd=mysql_fetch_array(mysql_query("SELECT sum(sisa_debet) - sum(sisa_kredit) AS pmyd FROM tabel_master WHERE gr='8' and unit ='$unit'"));
					$query_Piutang_Wakalah=mysql_fetch_array(mysql_query("SELECT sum(sisa_debet) - sum(sisa_kredit) AS Piutang_Wakalah FROM tabel_master WHERE gr='9' and unit ='$unit'"));
					$query_Piutang_istishna=mysql_fetch_array(mysql_query("SELECT sum(sisa_debet)  - sum(sisa_kredit) AS Piutang_istishna FROM tabel_master WHERE gr='10' and unit ='$unit'"));
					$query_PMIYD_Piutang_istishna=mysql_fetch_array(mysql_query("SELECT sum(sisa_debet)  - sum(sisa_kredit) AS PMIYD_Piutang_istishna FROM tabel_master WHERE gr='11' and unit ='$unit'"));
					$query_Piutang_Qardul_Hasan=mysql_fetch_array(mysql_query("SELECT sum(sisa_debet)  - sum(sisa_kredit) AS Piutang_Qardul_Hasan FROM tabel_master WHERE gr='12' and unit ='$unit'"));
					$query_Pembiayaan_Musyarakah=mysql_fetch_array(mysql_query("SELECT sum(sisa_debet)  - sum(sisa_kredit) AS Pembiayaan_Musyarakah FROM tabel_master WHERE gr='13' and unit ='$unit'"));
					$query_persediaan=mysql_fetch_array(mysql_query("SELECT sum(sisa_debet)  - sum(sisa_kredit) AS persediaan FROM tabel_master WHERE gr='14' and unit ='$unit'"));
					$query_pembiayaan_ijaroh=mysql_fetch_array(mysql_query("SELECT sum(sisa_debet)  - sum(sisa_kredit) AS pembiayaan_ijaroh FROM tabel_master WHERE gr='15' and unit ='$unit'"));
					$query_penyusutan_ijaroh=mysql_fetch_array(mysql_query("SELECT sum(sisa_debet)  - sum(sisa_kredit) AS penyusutan_ijaroh FROM tabel_master WHERE gr='16' and unit ='$unit'"));
					$query_pernyataan=mysql_fetch_array(mysql_query("SELECT sum(sisa_debet)  - sum(sisa_kredit) AS pernyataan FROM tabel_master WHERE gr='17' and unit ='$unit'"));
					$query_ppap_cadangan_umum=mysql_fetch_array(mysql_query("SELECT sum(sisa_debet)  - sum(sisa_kredit) AS ppap_cadangan_umum FROM tabel_master WHERE gr='18' and unit ='$unit'"));
					$query_ppap_cadangan_khusus=mysql_fetch_array(mysql_query("SELECT sum(sisa_debet)  - sum(sisa_kredit) AS ppap_cadangan_khusus FROM tabel_master WHERE gr='19' and unit ='$unit'"));
					$query_inventasi_tanah=mysql_fetch_array(mysql_query("SELECT sum(sisa_debet)  - sum(sisa_kredit) AS inventasi_tanah FROM tabel_master WHERE gr='20' and unit ='$unit'"));
					$query_nyusut_ati=mysql_fetch_array(mysql_query("SELECT sum(sisa_debet)  - sum(sisa_kredit) AS inventasi_ati FROM tabel_master WHERE gr='21' and unit ='$unit'"));
					$query_ATI=mysql_fetch_array(mysql_query("SELECT sum(sisa_debet)  - sum(sisa_kredit) AS ati FROM tabel_master WHERE gr='22' and unit ='$unit'"));
					$query_ati_api=mysql_fetch_array(mysql_query("SELECT sum(sisa_debet)  - sum(sisa_kredit) AS ati_api FROM tabel_master WHERE gr='23' and unit ='$unit'"));
					$query_rak=mysql_fetch_array(mysql_query("SELECT sum(sisa_debet)  - sum(sisa_kredit) AS rak FROM tabel_master WHERE gr='24' and unit ='$unit'"));
					$query_rupa_rupa_aktiva=mysql_fetch_array(mysql_query("SELECT sum(sisa_debet)  - sum(sisa_kredit) AS rupa_rupa_aktiva FROM tabel_master WHERE gr='25' and unit ='$unit'"));
					$query_um=mysql_fetch_array(mysql_query("SELECT sum(sisa_debet)  - sum(sisa_kredit) AS um FROM tabel_master WHERE gr='26' and unit ='$unit'"));
					$query_bdb=mysql_fetch_array(mysql_query("SELECT sum(sisa_debet)  - sum(sisa_kredit) AS bdb FROM tabel_master WHERE gr='27' and unit ='$unit'"));
					$query_persediaan_pos_tag=mysql_fetch_array(mysql_query("SELECT sum(sisa_debet)  - sum(sisa_kredit) AS persediaan_pos_tag FROM tabel_master WHERE gr='28' and unit ='$unit'"));
					$query_praor_amor=mysql_fetch_array(mysql_query("SELECT sum(sisa_debet)  - sum(sisa_kredit) AS praor_amor FROM tabel_master WHERE kode_rekening='1991000' and unit ='$unit'"));
					$query_amor=mysql_fetch_array(mysql_query("SELECT sum(sisa_debet)  - sum(sisa_kredit) AS amor FROM tabel_master WHERE kode_rekening='1992000' and unit ='$unit'"));
					$query_deferred=mysql_fetch_array(mysql_query("SELECT sum(sisa_debet)  - sum(sisa_kredit) AS deferred FROM tabel_master WHERE kode_rekening='1999000' and unit ='$unit'"));

					//Array Query
					$kas=$query_kas['kas'];
					$bank1=$query_giroy['giro_syariah'];
					$bank2=$query_girok['giro_konven'];
					$depo_ib=$query_depo_ib['depo_ib'];
					$simpanan_kspp=$query_simpanan_kspp['simpanan_kspps'];
					$surat_harga=$query_surat_harga['surat_harga'];
					$piutang_murab=$query_piutang_murab['piutang_murab'];
					$pmyd=$query_pmyd['pmyd'];
					$Piutang_Wakalah=$query_Piutang_Wakalah['Piutang_Wakalah'];
					$Piutang_istishna=$query_Piutang_istishna['Piutang_istishna'];
					$PMIYD_Piutang_istishna=$query_PMIYD_Piutang_istishna['PMIYD_Piutang_istishna'];
					$Piutang_Qardul_Hasan=$query_Piutang_Qardul_Hasan['Piutang_Qardul_Hasan'];
					$Pembiayaan_Musyarakah=$query_Pembiayaan_Musyarakah['Pembiayaan_Musyarakah'];
					$persediaan=$query_persediaan['persediaan'];
					$pembiayaan_ijaroh=$query_pembiayaan_ijaroh['pembiayaan_ijaroh'];
					$penyusutan_ijaroh=$query_penyusutan_ijaroh['penyusutan_ijaroh'];
					$pernyataan=$query_pernyataan['pernyataan'];
					$ppap_cadangan_umum=$query_ppap_cadangan_umum['ppap_cadangan_umum'];
					$cadangan_khusus=$query_ppap_cadangan_khusus['ppap_cadangan_khusus'];
					$inventasi_tanah=$$query_inventasi_tanah['inventasi_tanah'];
					$nyusut_ati=$query_nyusut_ati['nyusut_ati'];
					$ati_api=$query_ati_api['ati_api'];
					$ATI=$query_ATI['ATI'];
					$rak=$query_rak['rak'];
					$rupa_rupa_aktiva=$query_rupa_rupa_aktiva['rupa_rupa_aktiva'];
					$um=$query_um['um'];
					$bdb=$query_bdb['bdb'];
					$persediaan_pos_tag=$query_persediaan_pos_tag['persediaan_pos_tag'];
					$praor_amor=$query_praor_amor['praor_amor'];
					$amor=$query_amor['amor'];
					$deferred=$query_deferred['deferred'];
					
					//porses penjumlahan Aktiva Lancar 
					$total_aktiva_lancar=$kas+$bank1+$bank2+$depo_ib+$simpanan_kspp+$surat_harga+$piutang_murab+$pmyd+$Piutang_Wakalah+$Piutang_istishna+$PMIYD_Piutang_istishna+$Piutang_Qardul_Hasan+$Pembiayaan_Musyarakah+$persediaan+$pembiayaan_ijaroh+$penyusutan_ijaroh+$pernyataan+$ppap_cadangan_umum+$cadangan_khusus+$rupa_rupa_aktiva+$um+$bdb+$persediaan_pos_tag;
					//Porses penjumlahan Aktiva Tetap 
					$tot_aktv_tetap=$inventasi_tanah+$nyusut_ati+$ati_api;
					//Porses penjumlahan Aktiva LAINNYA
					$tot_aktv_lain=$praor_amor+$amor+$deferred;				
					//Proses Penjumlahan Total AKTIVA
					$total_aktiva=$total_aktiva_lancar+$tot_aktv_tetap+$tot_aktv_lain;
					
					//Tahap penginputan Data
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening,unit) values('$unit 01','<b>I.&nbsp AKTIVA LANCAR</b>','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening,awal_debet,unit) values('$unit 02','&nbsp 1. KAS','$kas','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening,unit) values('$unit 03','&nbsp 2. PENEMATAN PADA BANK LAIN (RPBL)','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening, awal_debet,unit) values('$unit 04','&nbsp &nbsp a. &nbsp PPBL-Giro iB Pada Bank Syariah', '$bank1','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening, awal_debet,unit) values('$unit 05','&nbsp &nbsp b. &nbsp PPBL-Giro iB Pada Bank Konvensional', '$bank2','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening, awal_debet,unit) values('$unit 06','&nbsp &nbsp c. &nbsp PPBL-Deposito iB', '$depo_ib','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening, awal_debet,unit) values('$unit 07','&nbsp 3. SIMPANAN PADA KSPPS LAIN', '$simpanan_kspp','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening, awal_debet,unit) values('$unit 08','&nbsp 4. SURAT BERHARGA YANG DIMILIKI (SBYD)', '$surat_harga','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening, awal_debet,unit) values('$unit 09','&nbsp 5. PEMBIAYAAN PIUTANG MURABAHAH', '$piutang_murab','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening, awal_debet,unit) values('$unit 10)',&nbsp 6. PENDAPATAN MARGIN YG DITANGGUHKAN (PMYD)', '$pmyd','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening, awal_debet,unit) values('$unit 11)','&nbsp 7. PEMBIAYAAN PIUTANG WAKALAH', '$Piutang_Wakalah','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening, awal_debet,unit) values('$unit 12','&nbsp 8. PEMBIAYAAN PIUTANG ISTISHNA', '$Piutang_istishna','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening, awal_debet,unit) values('$unit 13)','&nbsp 9. PDPTN MARGIN ISTISHNA YG DITANGGUHKAN (PMIYD)', '$PMIYD_Piutang_istishna','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening, awal_debet,unit) values('$unit 14','&nbsp 10. PEMBIAYAAN PIUTANG QARDUL HASAN','$Piutang_Qardul_Hasan','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening, awal_debet,unit) values('$unit 15','&nbsp 11. PEMBIAYAAN YANG DIBERIKAN', '$Pembiayaan_Musyarakah','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening, awal_debet,unit) values('$unit 16','&nbsp 12. PERSEDIAAN', '$persediaan','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening, awal_debet,unit) values('$unit 17','&nbsp 13. PEMBIAYAAN IJARAH', '$pembiayaan_ijaroh','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening, awal_debet,unit) values('$unit 18','&nbsp 14. AKUMULASI PENYUSUTAN AKTIVA IJARAH -/-', '$penyusutan_ijaroh','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening, awal_debet,unit) values('$unit 19','&nbsp 15. PENYERTAAN', '$pernyataan','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening, awal_debet,unit) values('$unit 20','&nbsp 16. PPA-CADANGAN UMUM', '$ppap_cadangan_umum','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening, awal_debet,unit) values('$unit 21','&nbsp 17. PPA-CADANGAN KHUSUS', '$cadangan_khusus','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening, awal_debet,unit) values('$unit 22','&nbsp 18. PENDAPATAN YANG AKAN DITERIMA', '$rupa_rupa_aktiva','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening, awal_debet,unit) values('$unit 23','&nbsp 19. UANG MUKA', '$um','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening, awal_debet,unit) values('$unit 24','&nbsp 20. BIAYA DIBAYAR DIMUKA', '$bdb','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening, awal_debet,unit) values('$unit 25','&nbsp 21. RUPA-RUPA AKTIVA LAINNYA', '$persediaan_pos_tag','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening,unit) values('$unit 26','break','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening,awal_debet,unit) values('$unit 27','<b> &nbsp JUMLAH AKTIVA LANCAR</b>', '$total_aktiva_lancar','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening,unit) values('$unit 28','break','$unit')");
					
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening,unit) values('$unit 29','<b>II. &nbsp INVENTARIS JANGKA PANJANG</b>')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening, awal_debet,unit) values('$unit 30','&nbsp 1. INVESTASI PADA KSPPS LAINNYA', '$persediaan_toko','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening,unit) values('$unit 31','&nbsp 2. INVESTASI PADA SAHAM', '$persediaan_toko','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening, awal_debet,unit) values('$unit 32 ','&nbsp 3. INVESTASI LAINNYA', '$persediaan_toko','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening,unit) values('$unit 33','break','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening,awal_debet,unit) values('$unit 34','<b>&nbsp JUMLAH JANGKA PANJANG</b>', '$persediaan_toko','$unit')");
								
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening,unit) values('$unit 35','<b>III. &nbsp AKTIVA TETAP</b>','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening, awal_debet,unit) values('$unit 36','&nbsp 1. AKTIVA TETAP DAN INVENTARIS-TANAH/GEDUNG', '$inventasi_tanah','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening,unit) values('$unit 37','2. AKUMULASI PENYUSUTAN ATI-GEDUNG', '$nyusut_ati','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening, awal_debet,unit) values('$unit 38','&nbsp 3. AKTIVA TETAP DAN INVENTARIS-INVENTARIS', '$ATI','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening, awal_debet,unit) values('$unit 39','&nbsp 4. AKUMULASI PENYUSUTAN ATI-INVENTARIS', '$ati_api','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening,unit) values('$unit 40','break','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening,awal_debet,unit) values('$unit 41','<b>&nbsp JUMLAH AKTIVA TETAP</b>', '$tot_aktv_tetap','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening,unit) values('$unit 42','<b>IV. &nbsp AKTIVA LAIN-LAIN</b>','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening, awal_debet,unit) values('$unit 43','&nbsp 1. BIAYA PRA OPERASIONAL', '$praor_amor','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening,unit) values('$unit 44','&nbsp 39. AMORTISASI BIAYA PRA OPRASIONAL', '$amor','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening, awal_debet,unit) values('$unit 45','&nbsp 3. DEFFERED ACCOUNT', '$deferred','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening,unit) values('$unit 46','break')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening,awal_debet,unit) values('$unit 47','<b>&nbsp JUMLAH AKTIVA LAIN-LAIN</b>', '$tot_aktv_lain','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening,unit) values('$unit 48','break','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening,awal_debet,unit) values('$unit 49','<b>TOTAL AKTIVA</b>', '$total_aktiva','$unit')");
					 
					//kewajiban
					$query_simpanan_wadiah=mysql_fetch_array(mysql_query("SELECT sum(sisa_kredit)- sum(sisa_debet) AS simpanan_wadiah FROM tabel_master WHERE gr='30' and unit ='$unit'"));
					$query_simpanan_mantap=mysql_fetch_array(mysql_query("SELECT sum(sisa_kredit)- sum(sisa_debet) AS simpanan_mantap FROM tabel_master WHERE gr='31' and unit ='$unit'"));
					$query_Simpanan_berjangka =mysql_fetch_array(mysql_query("SELECT sum(sisa_kredit)- sum(sisa_debet) AS Simpanan_berjangka  FROM tabel_master WHERE gr='32' and unit ='$unit'"));
					$query_sgu=mysql_fetch_array(mysql_query("SELECT sum(sisa_kredit)- sum(sisa_debet) AS sgu FROM tabel_master WHERE gr='33' and unit ='$unit'"));
					$query_pembiayaan_bank=mysql_fetch_array(mysql_query("SELECT sum(sisa_kredit)- sum(sisa_debet) AS pembiayaan_bank FROM tabel_master WHERE gr='34' and unit ='$unit'"));
					$query_pembiayaan_orang=mysql_fetch_array(mysql_query("SELECT sum(sisa_kredit)- sum(sisa_debet) AS pembiayaan_orang FROM tabel_master WHERE gr='35' and unit ='$unit'"));
					$query_lpdb=mysql_fetch_array(mysql_query("SELECT sum(sisa_kredit)- sum(sisa_debet) AS lpdb FROM tabel_master WHERE gr='36' and unit ='$unit'"));
					$query_sewa_guna_usaha=mysql_fetch_array(mysql_query("SELECT sum(sisa_kredit)- sum(sisa_debet) AS sewa_guna_usaha FROM tabel_master WHERE gr='37' and unit ='$unit'"));
					$query_pydt_bank=mysql_fetch_array(mysql_query("SELECT sum(sisa_kredit)- sum(sisa_debet) AS pydt_bank FROM tabel_master WHERE gr='38' and unit ='$unit'"));
					$query_pydt_orang=mysql_fetch_array(mysql_query("SELECT sum(sisa_kredit)- sum(sisa_debet) AS pydt_orang FROM tabel_master WHERE gr='39' and unit ='$unit'"));
					$query_lpdb_lain=mysql_fetch_array(mysql_query("SELECT sum(sisa_kredit)- sum(sisa_debet) AS lpdb_lain FROM tabel_master WHERE gr='40' and unit ='$unit'"));
					$query_ksd=mysql_fetch_array(mysql_query("SELECT sum(sisa_kredit)- sum(sisa_debet) AS ksd FROM tabel_master WHERE gr in ('41','42') and unit ='$unit'"));
					$query_wajib_bayar=mysql_fetch_array(mysql_query("SELECT sum(sisa_kredit)- sum(sisa_debet) AS wajib_bayar FROM tabel_master WHERE gr ='43' and unit ='$unit'"));
					$query_wajib_lainnya=mysql_fetch_array(mysql_query("SELECT sum(sisa_kredit)- sum(sisa_debet) AS wajib_lainnya FROM tabel_master WHERE gr ='44' and unit ='$unit'"));
									
					//menghitung modal
					$query_s_pokok=mysql_fetch_array(mysql_query("SELECT sum(sisa_kredit)- sum(sisa_debet) AS simpanan_poko FROM tabel_master WHERE gr='45' and unit ='$unit'"));
					$query_s_wajib=mysql_fetch_array(mysql_query("SELECT sum(sisa_kredit)- sum(sisa_debet) AS sw FROM tabel_master WHERE gr='46' and unit ='$unit'"));
					$query_s_wajib_khusus=mysql_fetch_array(mysql_query("SELECT sum(sisa_kredit)- sum(sisa_debet) AS swk FROM tabel_master WHERE gr='47' and unit ='$unit'"));
					$query_modal_penyetaraan=mysql_fetch_array(mysql_query("SELECT sum(sisa_kredit)- sum(sisa_debet) AS modal_penyetaraan FROM tabel_master WHERE gr='48' and unit ='$unit'"));
					$query_modal_penyertaan=mysql_fetch_array(mysql_query("SELECT sum(sisa_kredit)- sum(sisa_debet) AS modal_penyertaan FROM tabel_master WHERE gr='49' and unit ='$unit'"));
					$query_modal_sumbang=mysql_fetch_array(mysql_query("SELECT sum(sisa_kredit)- sum(sisa_debet) AS modal_sumbang FROM tabel_master WHERE gr='50' and unit ='$unit'"));
					$query_selisih_penilaian=mysql_fetch_array(mysql_query("SELECT sum(sisa_kredit)- sum(sisa_debet) AS selisih_penilaian FROM tabel_master WHERE gr='51' and unit ='$unit'"));
					$query_cadangan=mysql_fetch_array(mysql_query("SELECT sum(sisa_kredit)- sum(sisa_debet) AS cadangan FROM tabel_master WHERE gr='52' and unit ='$unit'"));
					$query_shu_lalu=mysql_fetch_array(mysql_query("SELECT sum(sisa_kredit)- sum(sisa_debet) AS shu_lalu FROM tabel_master WHERE gr='53' and unit ='$unit'"));
					$query_shu_jalan=mysql_fetch_array(mysql_query("SELECT sum(sisa_kredit)- sum(sisa_debet) AS shu_jalan FROM tabel_master WHERE gr='55' and unit ='$unit'"));
										
					//Array Query
					$impanan_wadiah=$query_simpanan_wadiah['impanan_wadiah'];
					$simpanan_mantap=$query_simpanan_mantap['simpanan_mantap'];
					$Simpanan_berjangka=$query_Simpanan_berjangka['Simpanan_berjangka'];
					$pembiayaan_bank=$query_pembiayaan_bank['pembiayaan_bank'];
					$pembiayaan_orang=$query_pembiayaan_orang['pembiayaan_orang'];
					$sgu=$query_sgu['sgu'];
					$lpdb=$query_lpdb['lpdb'];
					$sewa_guna_usaha=$query_sewa_guna_usaha['sewa_guna_usaha'];
					$pydt_bank=$query_pydt_bank['pydt_bank'];
					$pydt_orang=$query_pydt_orang['pydt_orang'];
					$wajib_bayar=$query_wajib_bayar['wajib_bayar'];
					$lpdb_lain=$query_lpdb_lain['lpdb_lain'];
					
					//total aktiva lancar
					$total_lancar=$impanan_wadiah+$simpanan_mantap+$Simpanan_berjangka+$sgu+$lpdb+$pydt_orang+$lpdb_lain+$ksd+$wajib_bayar+$wajib_lainnya;
					
					$ksd=$query_ksd['ksd'];
					$wajib_lainnya=$query_wajib_lainnya['wajib_lainnya'];
				
					//Array Query
					$s_pokok=$query_s_pokok['s_pokok'];
					$s_wajib=$query_s_wajib['sw'];
					$s_wajib_khusus=$query_s_wajib_khusus['s_wajib_khusus'];
					$modal_penyetaraan=$query_modal_penyetaraan['modal_penyetaraan'];
					$modal_penyertaan=$query_modal_penyertaan['modal_penyertaan'];
					
					//Array Query
					$modal_sumbang=$query_modal_sumbang['modal_sumbang'];
					$selisih_penilaian=$query_selisih_penilaian['selisih_penilaian'];	
					$cadangan=$query_cadangan['cadangan'];	
					$shu_lalu=$query_shu_lalu['shu_lalu'];	
					$shu_jalan=$query_shu_jalan['shu_jalan'];	
					//Proses Perhitungan
					$tot_ekuit=$swkp+$s_pokok+$s_wajib+$modal_penyetaraan+$modal_penyertaan+$modal_sumbang+$selisih_penilaian+$selisih_penilaian+$selisih_penilaian+$shu_bp;
					
					
					
					
					//Tahap penginputan Data
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening,unit) values('$unit 50','<b>PASIVA</b>','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening,unit) values('$unit 51','<b>I. &nbsp KEWAJIBAN LANCAR</b>','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening, awal_debet,unit) values('$unit 52',' &nbsp 1. &nbsp SIMPANAN WADIAH', '$impanan_wadiah','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening, awal_debet,unit) values('$unit 53',' &nbsp 2. &nbsp  SIMPANAN MUDHARABAH MUTHLAQAH', '$simpanan_mantap','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening, awal_debet,unit) values('$unit 54','&nbsp 3. &nbsp SIMA MUDHARABAH MUTHLAQAH', '$Simpanan_berjangka','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening, awal_debet,unit) values('$unit 55','&nbsp 4. &nbsp PEMBIAYAAN YANG DITERIMA - SGU (Bagian jatuh tempo < 1 tahun)', '$sgu','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening, awal_debet,unit) values('$unit 56','&nbsp 5. &nbsp PEMBIAYAAN YANG DITERIMA - BANK (Bagian jatuh tempo < 1 tahun)', '$pembiayaan_bank','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening, awal_debet,unit) values('$unit 57','&nbsp 6. &nbsp PINJAMAN YANG DITERIMA - PERORANGAN', '$pembiayaan_orang','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening, awal_debet,unit) values('$unit 58','&nbsp 7. &nbsp PEMBIAYAAN YANG DITERIMA - LPDB (Bagian jatuh tempo < 1 tahun)', '$lpdb','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening, awal_debet,unit) values('$unit 59','&nbsp 8. &nbsp KEWAJIBAN SEGERA DIBAYAR', '$ksd','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening, awal_debet,unit) values('$unit 60','&nbsp 9. &nbsp BEBAN YANG MASIH HARUS DIBAYAR', '$wajib_bayar','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening, awal_debet,unit) values('$unit 61','&nbsp 10. &nbsp KEWAJIBAN LAIN-LAIN', '$wajib_lainnya','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening,unit) values('$unit 62','break','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening, awal_debet,unit) values('$unit 63','<b>&nbsp JUMLAH KEWAJIBAN LANCAR<b>', '$total_lancar','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening,unit) values('$unit 64','break','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening,unit) values('$unit 65','<b>II. &nbsp KEWAJIBAN JANGKA PANJANG</b>','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening, awal_debet,unit) values('$unit 66','&nbsp 1. &nbsp SIMA MUDHARABAH MUTHLAQAH', '$Simpanan_berjangka','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening, awal_debet,unit) values('$unit 67','&nbsp 2. &nbsp PEMBIAYAAN YANG DITERIMA - SGU', '$sewa_guna_usaha','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening, awal_debet,unit) values('$unit 68','&nbsp 3. &nbsp PEMBIAYAAN YANG DITERIMA - BANK', '$pydt_bank','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening, awal_debet,unit) values('$unit 69','&nbsp 4. &nbsp PINJAMAN YANG DITERIMA - PERORANGAN', '$pydt_orang','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening, awal_debet,unit) values('$unit 70','&nbsp 5. &nbsp PEMBIAYAAN YANG DITERIMA - LPDB', '$lpdb_lain','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening,unit) values('$unit 71','break','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening, awal_debet,unit) values('$unit 72','<b>&nbsp JUMLAH KEWAJIBAN JANGKA PANJANG<b>', '$total_pasiva','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening,unit) values('$unit 73','break','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening,unit) values('$unit 74','<b>III. &nbsp EKUITAS</b>','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening, awal_debet,unit) values('$unit 75','&nbsp 1. &nbsp SIMPANAN POKOK', '$swkp','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening, awal_debet,unit) values('$unit 76','&nbsp 2. &nbsp SIMPANAN WAJIB', '$s_pokok','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening, awal_debet,unit) values('$unit 77','&nbsp 3. &nbsp SIMPANAN WAJIB KHUSUS', '$s_wajib','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening, awal_debet,unit) values('$unit 78','&nbsp 4. &nbsp MODAL PENYETARAAN', '$modal_penyetaraan','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening, awal_debet,unit values('$unit 79','&nbsp 5. &nbsp MODAL PENYERTAAN', '$modal_penyertaan','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening, awal_debet,unit) values('$unit 80','&nbsp 6. &nbsp MODAL SUMBANGAN/HIBAH', '$modal_sumbang','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening, awal_debet,unit) values('$unit 81','&nbsp 7. &nbsp SELISIH PENILAIAN KEMBALI AKTIVA TETAP', '$selisih_penilaian','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening, awal_debet,unit) values('$unit 82','&nbsp 8. &nbsp CADANGAN', '$selisih_penilaian','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening, awal_debet,unit) values('$unit 83','&nbsp 9. &nbsp SHU - TAHUN-TAHUN LALU BLM DIBAGI', '$selisih_penilaian','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening, awal_debet,unit) values('$unit 84','&nbsp 10. &nbsp SHU - TAHUN BERJALAN', '$shu_bp','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening,unit) values('$unit 85','break','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening, awal_debet,unit) values('$unit 86','<b> JUMLAH EKUITAS</b>', '$shu_bp','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening,unit) values('$unit 87','break','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening, awal_debet,unit) values('$unit 88','<b>JUMLAH KEWAJIBA DAN EKUITAS</b>', '$shu_bp','$unit')");
					mysql_query("INSERT INTO tabel_neraca(kode_rekening, nama_rekening,unit) values('$unit 89','break','$unit')");
					
					$query_report=mysql_query("select * from tabel_neraca where unit ='$unit' order by kode_rekening");
					
					?>
					<div class="container">
					<h3 align="left"><b>Neraca (Balance Sheet)</b></h3><br />
					<font color="#333333">Periode : <?php echo $tanggal2=$_POST['tanggal2']; ?></font>
					
					
			
					<table width="80%" border="1" bordercolor="669900" style="margin: 1px 1px" class="table table-striped" >
					<tr>
					<Th>URAIAN</th>
					<th>NILAI</th>
					</TR>
					<?php
					while($row=mysql_fetch_array($query_report)){
						$nama_rekening=$row['nama_rekening'];
						$awal_debet=number_format(compat_num($row['awal_debet']),2,'.',',');
						
						if($nama_rekening=='break'){
							?><tr><td colspan="2"></td></tr><?php
						}else{
							?><tr><td><?php echo $nama_rekening ?></td><td align="right"><?php echo $awal_debet;?></td></tr><?php
						}
					}
					?>
					</div>
				</table>
					
					
				<?php
				}else{
					echo mysql_error();
				}
			}
					 
			
			if($neraca=='aktiva'){
				 aktiva();
			}else if($neraca=='pasiva'){
				pasiva();
			}else if($neraca=='neraca'){
				neraca();
			}
			
			
		}else{	
			unset($_POST['report']);
		}
		?>
		
		</p>
		</div>
</div>
	
	
	<iframe width=174 height=189 name="gToday:normal:../calender/agenda.js" id="gToday:normal:../calender/agenda.js" src="../calender/ipopeng.htm" scrolling="no" frameborder="0" style="visibility:visible; z-index:999; position:absolute; top:-500px; left:-500px;"></iframe>
<?php 




?>
