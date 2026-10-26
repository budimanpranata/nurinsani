<?php require_once __DIR__ . '/../php8_compat.php'; 
session_start();
ob_start();
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
if(!isset($_SESSION['userid'])){
   header("location:index.html");
}

	
	   else{
?>
<div class="entry">
			<?php
				
			//untuk ID perusahaan
			
			$periode=mysql_fetch_array(mysql_query("select tanggal_awal from tabel_rugi_laba where kode_rekening='VII' and unit='$unit'"));
			?>
			<h2 align="center"><strong><img src="../Image/ni.png" width="202" height="46" /></strong></h2>
			<h3 align="center"><strong>PERHITUNGAN HASIL USAHA </strong></h3>
			<p align="center"><font color="#333333">Untuk Periode Yang Berakhir</font></p>
			<p align="center"><font color="#333333"> <?php echo "Periode : ".$periode['tanggal_awal'];?></font></p>
			<p align="center">&nbsp;</p>
			<p>
				<script type="text/javascript" src="../jquery.js"></script>
				<link rel="stylesheet" href="../css/bootstrap.min.css"/>
				</script>
		
			    <img src="../excel-icon.jpeg" width="16" height="16" /> excel <img src="../pdf-icon.jpg" width="16" height="16" /> pdf
                <button onclick="window.print();">Print</button>
                <div class="post">
<div class="container">
		  <table width="80%" border="1" bordercolor="669900" style="margin: 1px 1px" class="table table-striped" >
			<tr>
			  <th width="66">ID</th>
			  <th width="524">URAIAN PERKIRAAN</th>
			  <th width="92">MTD</th>
			  <th width="93">YTD</th>
			</tr>
			<tr><td align="center">I.</td><td><strong>PENDAPATAN OPERASIONAL</strong></td>
			<td></td><td></td></tr>
			<?php
			$pyd_murab=mysql_fetch_array(mysql_query("select  sum(sisa_kredit) - sum(sisa_debet)  as ytd, sum(rl_kredit) - sum(rl_debet) as mtd from tabel_rugi_laba where gr ='55' and unit ='$unit'"));
			$murab_ytd=$pyd_murab['ytd'];
			$murab_mtd=$pyd_murab['mtd'];
			?>
			
				<tr>
				  <td height="24" align="center">&nbsp;</td>
				  <td><strong>1. PENDAPATAN USAHA POKOK</strong></td>
				  <td align="right">&nbsp;</td>
				  <td align="right">&nbsp;</td>
		    </tr>
				<tr>
				  <td height="24" align="center">&nbsp;</td>
				  <td><table cellspacing="0" cellpadding="0">
                    <tr>
                      <td> &nbsp a. &nbspPENDAPATAN MARGIN    PYD MURABAHAH</td>
                    </tr>
			      </table></td>
				  <td align="right"><?php echo number_format(compat_num($murab_ytd),2,'.',','); ?></td>
				  <td align="right"><?php echo number_format(compat_num($murab_mtd),2,'.',','); ?></td>
			  </tr>
				<tr><td height="24" align="center">&nbsp;</td>
				  <td>&nbsp; b. &nbspPENDAPATAN BERSIH ISTISHNA</td>
				  <?php
			$pyd_is=mysql_fetch_array(mysql_query("select  sum(sisa_kredit) - sum(sisa_debet)  as ytd, sum(rl_kredit) - sum(rl_debet) as 			mtd from tabel_rugi_laba where gr ='56' and unit ='$unit'"));
			$mis_ytd=$pyd_is['ytd'];
			$is_mtd=$pyd_is['mtd'];
			?>
				<td align="right"><?php echo number_format(compat_num($mis_ytd),2,'.',','); ?></td>
				<td align="right"><?php echo number_format(compat_num($is_mtd),2,'.',','); ?></td>
				</tr>
			
			<tr><td colspan="4"></tr>
			<tr>
			  <td align="center">&nbsp;</td>
			  <td>&nbsp; c. &nbspPENDAPATAN SEWA PYD IJARAH</td>
			    <?php
			$pyd_ija=mysql_fetch_array(mysql_query("select  sum(sisa_kredit) - sum(sisa_debet)  as ytd, sum(rl_kredit) - sum(rl_debet) as 			mtd from tabel_rugi_laba where gr ='57' and unit ='$unit'"));
			$ija_ytd=$pyd_ija['ytd'];
			$ija_mtd=$pyd_ija['mtd'];
			?>
				<td align="right"><?php echo number_format(compat_num($ija_ytd),2,'.',','); ?></td>
				<td align="right"><?php echo number_format(compat_num($ija_mtd),2,'.',','); ?></td>
			</tr>
			
			<tr><td colspan="4"></tr>
			<tr>
			  <td align="center">&nbsp;</td>
			  <td>&nbsp; d.&nbsp PENDAPATAN BAGI HASIL PEMBIAYAAN</td>
			  <?php
			$pyd_bagi_hasil=mysql_fetch_array(mysql_query("select  sum(sisa_kredit) - sum(sisa_debet)  as ytd, sum(rl_kredit) - sum(rl_debet) as 			mtd from tabel_rugi_laba where gr ='58' and unit ='$unit'"));
			$bagi_ytd=$pyd_bagi_hasil['ytd'];
			$bagi_mtd=$pyd_bagi_hasil['mtd'];
			?>
			 <td align="right"><?php echo number_format(compat_num($bagi_ytd),2,'.',','); ?></td>
			<td align="right"><?php echo number_format(compat_num($bagi_mtd),2,'.',','); ?></td>
		    </tr>
			<tr>
			  <td align="center">&nbsp;</td>
			  <td>&nbsp; e.&nbsp PENDAPATAN DARI PENYERTAAN</td>
			    <?php
			$pyd_penyetaraan=mysql_fetch_array(mysql_query("select  sum(sisa_kredit) - sum(sisa_debet)  as ytd, sum(rl_debet) - 			sum(rl_kredit) as mtd from tabel_rugi_laba where gr ='59' and unit ='$unit'"));
			$penyetaraan_ytd=$pyd_penyetaraan['ytd'];
			$penyetaraan_mtd=$pyd_penyetaraan['mtd'];
			?>
			  <td align="right"><?php echo number_format(compat_num($penyetaraan_ytd),2,'.',','); ?></td>
			<td align="right"><?php echo number_format(compat_num($penyetaraan_mtd),2,'.',','); ?></td>
		    </tr>
			<tr>
			  <td align="center">&nbsp;</td>
			  <td>&nbsp; f.&nbsp PENDAPATAN PENEMPATAN PADA BANK LAIN</td>
			 <?php
			$pyd_pada_bank=mysql_fetch_array(mysql_query("select  sum(sisa_kredit) - sum(sisa_debet)  as ytd, sum(rl_kredit) - sum(rl_debet) as mtd from tabel_rugi_laba where gr ='60' and unit ='$unit'"));
			$pyd_bank_ytd=$pyd_pada_bank['ytd'];
			$pyd_bank_mtd=$pyd_pada_bank['mtd'];
			?>
			  <td align="right"><?php echo number_format(compat_num($pyd_bank_ytd),2,'.',','); ?></td>
			<td align="right"><?php echo number_format(compat_num($pyd_bank_mtd),2,'.',','); ?></td>
		    </tr>
			<tr>
			  <td align="center">&nbsp;</td>
			  <td>&nbsp; g.&nbspPDBH SURAT BERHARGA SYARIAH</td>
			  <?php
			$pyd_surat_harga=mysql_fetch_array(mysql_query("select  sum(sisa_kredit) - sum(sisa_debet)  as ytd, sum(rl_debet) - 	sum(rl_kredit) as mtd from tabel_rugi_laba where gr ='61' and unit ='$unit'"));
			$surat_harga_ytd=$pyd_surat_harga['ytd'];
			$surat_harga_mtd=$pyd_surat_harga['mtd'];
			?>
			  <td align="right"><?php echo number_format(compat_num($surat_harga_ytd),2,'.',','); ?></td>
			<td align="right"><?php echo number_format(compat_num($surat_harga_mtd),2,'.',','); ?></td>
		    </tr>
			<tr>
			  <td align="center">&nbsp;</td>
			  <td>&nbsp; h.&nbsp PENDAPATAN MARGIN PYD MURABAHAH AKRUAL</td>
			 <?php
			$pyd_murab_akrual=mysql_fetch_array(mysql_query("select  sum(sisa_kredit) - sum(sisa_debet)  as ytd, sum(rl_kredit) - sum(rl_debet) as mtd from tabel_rugi_laba where gr ='62' and unit ='$unit'"));
			$murab_akrual_ytd=$pyd_murab_akrual['ytd'];
			$murab_akrual_mtd=$pyd_surat_harga['mtd'];
			?>
			  <td align="right"><?php echo number_format(compat_num($murab_akrual_ytd),2,'.',','); ?></td>
			<td align="right"><?php echo number_format(compat_num($murab_akrual_mtd),2,'.',','); ?></td>
		    </tr>
			<tr>
			  <td align="center">&nbsp;</td>
			  <td>&nbsp; i. &nbsp PENDAPATAN BERSIH ISTISHNA AKRUAL</td>
			  <?php
			$pyd_is_akrual=mysql_fetch_array(mysql_query("select  sum(sisa_kredit) - sum(sisa_debet)  as ytd, sum(rl_kredit) - sum(rl_debet) as mtd from tabel_rugi_laba where gr ='63' and unit ='$unit'"));
			$is_akrual_ytd=$pyd_is_akrual['ytd'];
			$is_akrual_mtd=$pyd_is_akrual['mtd'];
			?>
			  <td align="right"><?php echo number_format(compat_num($is_akrual_ytd),2,'.',','); ?></td>
			<td align="right"><?php echo number_format(compat_num($is_akrual_mtd),2,'.',','); ?></td>
		    </tr>
			<tr>
			  <td align="center">&nbsp;</td>
			  <td>&nbsp; j. &nbsp PENDAPATAN SEWA PYD IJARAH AKRUAL</td>
			   <?php
			$pyd_ijaroh_akrual=mysql_fetch_array(mysql_query("select  sum(sisa_kredit) - sum(sisa_debet)  as ytd, sum(rl_kredit) - sum(rl_debet) as mtd from tabel_rugi_laba where gr ='64' and unit ='$unit'"));
			$ija_akrual_ytd=$pyd_ijaroh_akrual['ytd'];
			$ija_akrual_mtd=$pyd_ijaroh_akrual['mtd'];
			?>
			  <td align="right"><?php echo number_format(compat_num($ija_akrual_ytd),2,'.',','); ?></td>
			<td align="right"><?php echo number_format(compat_num($ija_akrual_mtd),2,'.',','); ?></td>
		    </tr>
			<tr>
			  <td align="center">&nbsp;</td>
			  <td>&nbsp; k.&nbsp PENDAPATAN BAGI HASIL PEMBIAYAAN AKRUAL</td>
			  <?php
			$pyd_bagihasil_akrual=mysql_fetch_array(mysql_query("select  sum(sisa_kredit) - sum(sisa_debet)  as ytd, sum(rl_kredit) - sum(rl_debet) as mtd from tabel_rugi_laba where gr ='65' and unit ='$unit'"));
			$bagihasil_akrual_ytd=$pyd_bagihasil_akrual['ytd'];
			$bagihasil_akrual_mtd=$pyd_bagihasil_akrual['mtd'];
			?>
			  <td align="right"><?php echo number_format(compat_num($bagihasil_akrual_ytd),2,'.',','); ?></td>
			<td align="right"><?php echo number_format(compat_num($bagihasil_akrual_mtd),2,'.',','); ?></td>
		    </tr>
			<tr>
			  <td align="center">&nbsp;</td>
			  <td>&nbsp; l. &nbsp PENDAPATAN PENEMPATAN PADA BANK LAIN AKRUAL</td>
			  <?php
			$pyd_banklain_akrual=mysql_fetch_array(mysql_query("select  sum(sisa_kredit) - sum(sisa_debet)  as ytd, sum(rl_kredit) - sum(rl_debet) as mtd from tabel_rugi_laba where gr ='66' and unit ='$unit'"));
			$banklain_akrual_ytd=$pyd_banklain_akrual['ytd'];
			$banklain_akrual_mtd=$pyd_banklain_akrual['mtd'];
			?>
			  <td align="right"><?php echo number_format(compat_num($banklain_akrual_ytd),2,'.',','); ?></td>
			<td align="right"><?php echo number_format(compat_num($banklain_akrual_mtd),2,'.',','); ?></td>
		    </tr>
			<tr>
			  <td align="center">&nbsp;</td>
			  <td>&nbsp; m.&nbsp; PDBH SURAT BERHARGA SYARIAH AKRUAL</td>
			  <?php
			$pyd_sy_akrual=mysql_fetch_array(mysql_query("select  sum(sisa_kredit) - sum(sisa_debet)  as ytd, sum(rl_kredit) - sum(rl_debet) as mtd from tabel_rugi_laba where gr ='67' and unit ='$unit'"));
			$sy_akrual_ytd=$pyd_sy_akrual['ytd'];
			$sy_akrual_mtd=$pyd_sy_akrual['mtd'];
			?>
			  <td align="right"><?php echo number_format(compat_num($sy_akrual_ytd),2,'.',','); ?></td>
			<td align="right"><?php echo number_format(compat_num($sy_akrual_mtd),2,'.',','); ?></td>
		    </tr>
			<tr>
			  <td align="center">&nbsp;</td>
			  <td><strong>JUMLAH PENDAPATAN USAHA POKOK</strong></td>
			   <?php
			$jumlah_usaha_ytd=$murab_ytd+$mis_ytd +$ija_ytd + $bagi_ytd +$penyetaraan_ytd + $pyd_bank_ytd +$surat_harga_ytd+$murab_akrual_ytd +$is_akrual_ytd +$ija_akrual_ytd +$bagihasil_akrual_ytd + $banklain_akrual_ytd+ $sy_akrual_ytd;
			$jumlah_usaha_mtd=$murab_mtd+ $is_mtd + $ija_mtd +$bagi_mtd +$penyetaraan_mtd +$pyd_bank_mtd+ $surat_harga_mtd + $murab_akrual_mtd +$is_akrual_mtd +$ija_akrual_mtd+$bagihasil_akrual_mtd + $banklain_akrual_mtd +$sy_akrual_mtd;
			?>
			  <td align="right"><?php echo number_format(compat_num($jumlah_usaha_ytd),2,'.',','); ?></td>
			<td align="right"><?php echo number_format(compat_num($jumlah_usaha_mtd),2,'.',','); ?></td>
		    </tr>
			<tr><td align="center">&nbsp;</td>
			  <td><strong>2. &nbsp; PENDAPATAN LAINNYA</strong></td>
			<td></td><td></td></tr>
			<tr>
			  <td height="24" align="center">&nbsp;</td>
			  <td>&nbsp; a. &nbsp; PENDAPATAN-KOREKSI PPAP &amp; NP</td>
			  <?php
			$pyd_kor_ppap=mysql_fetch_array(mysql_query("select  sum(sisa_kredit) - sum(sisa_debet)  as ytd, sum(rl_kredit) - sum(rl_debet) as mtd from tabel_rugi_laba where gr ='68' and unit ='$unit'"));
			$kor_ppap_ytd=$pyd_kor_ppap['ytd'];
			$kor_ppap_mtd=$pyd_kor_ppap['mtd'];
			?>
			  <td align="right"><?php echo number_format(compat_num($kor_ppap_ytd),2,'.',','); ?></td>
			<td align="right"><?php echo number_format(compat_num($kor_ppap_mtd),2,'.',','); ?></td>
		    </tr>
		    </tr>
			<tr>
			  <td height="24" align="center">&nbsp;</td>
			  <td>&nbsp; b. &nbsp; KENAIKAN NILAI SURAT BERHARGA</td>
			  <?php
			$pyd_naik_surat=mysql_fetch_array(mysql_query("select  sum(sisa_kredit) - sum(sisa_debet)  as ytd, sum(rl_kredit) - sum(rl_debet) as mtd from tabel_rugi_laba where gr ='69' and unit ='$unit'"));
			$naik_surat_ytd=$pyd_naik_surat['ytd'];
			$naik_surat_mtd=$pyd_naik_surat['mtd'];
			?>
			  <td align="right"><?php echo number_format(compat_num($naik_surat_ytd),2,'.',','); ?></td>
			<td align="right"><?php echo number_format(compat_num($naik_surat_mtd),2,'.',','); ?></td>
		    </tr>
			<tr>
			  <td height="24" align="center">&nbsp;</td>
			  <td>&nbsp; c. &nbsp; PENDAPATAN OPERASIONAL LAINNYA</td>
			  <?php
			$pyd_ops_lain=mysql_fetch_array(mysql_query("select  sum(sisa_kredit) - sum(sisa_debet)  as ytd, sum(rl_kredit) - sum(rl_debet) as mtd from tabel_rugi_laba where gr ='70' and unit ='$unit'"));
			$ops_lain_ytd=$pyd_ops_lain['ytd'];
			$ops_lain_mtd=$pyd_ops_lain['mtd'];
			?>
			  <td align="right"><?php echo number_format(compat_num($ops_lain_ytd),2,'.',','); ?></td>
			<td align="right"><?php echo number_format(compat_num($ops_lain_mtd),2,'.',','); ?></td>
		    </tr>
			  </tr>
			<tr>
			  <td></td>
			  <td><strong>JUMLAH PENDAPATAN LAINNYA</strong></td>
			 <?php
			$jumlah_pyd_lain_ytd=$kor_ppap_ytd+$naik_surat_ytd+$ops_lain_ytd;
			$jumlah_pyd_lain_mtd=$kor_ppap_mtd+$naik_surat_mtd+$ops_lain_mtd;
			?>
			  <td align="right"><?php echo number_format(compat_num($jumlah_pyd_lain_ytd),2,'.',','); ?></td>
			<td align="right"><?php echo number_format(compat_num($jumlah_pyd_lain_mtd),2,'.',','); ?></td>
		    </tr>
			<tr>
			  <td></td>
			  <td><strong>TOTAL PENDAPATAN OPERASIONAL (1+2)</strong></td>
			 <?php
			$total_pdp_ops_ytd=$jumlah_usaha_ytd+$jumlah_pyd_lain_ytd;
			$total_pdp_ops_mtd=$jumlah_usaha_mtd+$jumlah_pyd_lain_mtd;
			?>
			  <td align="right"><?php echo number_format(compat_num($total_pdp_ops_ytd),2,'.',','); ?></td>
			<td align="right"><?php echo number_format(compat_num($total_pdp_ops_mtd),2,'.',','); ?></td>
		    </tr>
			<tr>
			<tr><td align="center">II.</td><td><strong>BIAYA OPERASIONAL</strong></td>
			<td></td><td></td></tr>
		
				<tr>
				  <td align="center">&nbsp;</td>
				  <td><strong>1. &nbsp; BIAYA POKOK USAHA</strong></td>
			  </tr>
				<tr>
				  <td align="center">&nbsp;</td>
				  <td> &nbsp a. &nbsp; BAGI HSL SIMPANAN MUDHARABAH</td>
				 <?php
			$bagi_hasil_mudharobah=mysql_fetch_array(mysql_query("select  sum(sisa_debet) - sum(sisa_kredit)  as ytd, sum(rl_kredit) - sum(rl_debet) as mtd from tabel_rugi_laba where gr ='71' and unit ='$unit'"));
			$bagi_hasil_mudharobah_ytd=$bagi_hasil_mudharobah['ytd'];
			$bagi_hasil_mudharobah_mtd=$bagi_hasil_mudharobah['mtd'];
			?>
			 <td align="right"><?php echo number_format(compat_num($bagi_hasil_mudharobah_ytd),2,'.',','); ?></td>
			<td align="right"><?php echo number_format(compat_num($bagi_hasil_mudharobah_mtd),2,'.',','); ?></td>
		    </tr>
				<tr>
				  <td align="center">&nbsp;</td>
				  <td>&nbsp b. &nbsp; BAGI HSL DEPOSITO MUDHARABAH NB</td>
				 <?php
			$bagi_hasil_deposito=mysql_fetch_array(mysql_query("select  sum(sisa_debet) - sum(sisa_kredit)  as ytd, sum(rl_kredit) - sum(rl_debet) as mtd from tabel_rugi_laba where gr ='72' and unit ='$unit'"));
			$bagi_hasil_deposito_ytd=$bagi_hasil_deposito['ytd'];
			$bagi_hasil_deposito_mtd=$bagi_hasil_deposito['mtd'];
			?>
			 <td align="right"><?php echo number_format(compat_num($bagi_hasil_deposito_ytd),2,'.',','); ?></td>
			<td align="right"><?php echo number_format(compat_num($bagi_hasil_deposito_mtd),2,'.',','); ?></td>
		    </tr>
				<tr>
				  <td align="center">&nbsp;</td>
				  <td>&nbsp c.&nbsp; BEBAN BONUS WADIAH NB</td>
				  <?php
			$bonus_wadiah=mysql_fetch_array(mysql_query("select sum(sisa_debet) - sum(sisa_kredit)  as ytd, sum(rl_kredit) - sum(rl_debet) as mtd from tabel_rugi_laba where gr ='73' and unit ='$unit'"));
			$bonus_wadiah_ytd=$bonus_wadiah['ytd'];
			$bonus_wadiah_mtd=$bonus_wadiah['mtd'];
			?>
			 <td align="right"><?php echo number_format(compat_num($bonus_wadiah_ytd),2,'.',','); ?></td>
			<td align="right"><?php echo number_format(compat_num($bonus_wadiah_mtd),2,'.',','); ?></td>
		    </tr>
				<tr>
				  <td align="center">&nbsp;</td>
				  <td>&nbsp d. &nbsp; BIAYA BAGI HASIL PEMBIAYAAN/PINJAMN YG DITERIMA</td>
				  <?php
			$bagi_hasil_biaya=mysql_fetch_array(mysql_query("select sum(sisa_debet) - sum(sisa_kredit)  as ytd, sum(rl_kredit) - sum(rl_debet) as mtd from tabel_rugi_laba where gr ='74' and unit ='$unit'"));
			$bagi_hasil_biaya_ytd=$bagi_hasil_biaya['ytd'];
			$bagi_hasil_biaya_mtd=$bagi_hasil_biaya['mtd'];
			?>
			 <td align="right"><?php echo number_format(compat_num($bagi_hasil_biaya_ytd),2,'.',','); ?></td>
			<td align="right"><?php echo number_format(compat_num($bagi_hasil_biaya_mtd),2,'.',','); ?></td>
		    </tr>
				<tr><td align="center">&nbsp;</td><td><strong>JUMLAH BIAYA POKOK USAHA</strong></td>
				<?php
			$total_biaya_pokok_ytd=$bagi_hasil_mudharobah_ytd+$bagi_hasil_deposito_ytd+$bonus_wadiah_ytd+$bagi_hasil_biaya_ytd;
			$total_biaya_pokok_mtd=$bagi_hasil_mudharobah_mtd+$$bagi_hasil_deposito_mtd+$bonus_wadiah_mtd+$bagi_hasil_biaya_mtd;
			?>
			  <td align="right"><?php echo number_format(compat_num($total_biaya_pokok_ytd),2,'.',','); ?></td>
			<td align="right"><?php echo number_format(compat_num($total_biaya_pokok_mtd),2,'.',','); ?></td>
				</tr>
				<tr><td></td>
			  <td><font color="#333333"><strong>2. BIAYA OPERASIONAL USAHA</strong></font></td>
			<td></td><td></td></tr>
				<tr>
				  <td align="center">&nbsp;</td>
				  <td>&nbsp a. BIAYA ASURANSI PENJAMINAN DPK</td>
				  <?php
			$biaya_asuransi_dpk=mysql_fetch_array(mysql_query("select  sum(sisa_debet) - sum(sisa_kredit)  as ytd, sum(rl_debet) - sum(rl_kredit) as mtd from tabel_rugi_laba where gr ='75' and unit ='$unit'"));
			$biaya_asuransi_dpk_ytd=$biaya_asuransi_dpk['ytd'];
			$biaya_asuransi_dpk_mtd=$biaya_asuransi_dpk['mtd'];
			?>
			 <td align="right"><?php echo number_format(compat_num($biaya_asuransi_dpk_ytd),2,'.',','); ?></td>
			<td align="right"><?php echo number_format(compat_num($biaya_asuransi_dpk_mtd),2,'.',','); ?></td>
		    </tr>
				<tr>
				  <td align="center">&nbsp;</td>
				  <td>&nbsp b. BIAYA ASURANSI</td>
				   <?php
			$biaya_asuransi=mysql_fetch_array(mysql_query("select  sum(sisa_debet) - sum(sisa_kredit)  as ytd, sum(rl_debet) - sum(rl_kredit) as mtd from tabel_rugi_laba where gr ='76' and unit ='$unit'"));
			$biaya_asuransi_ytd=$biaya_asuransi['ytd'];
			$biaya_asuransi_mtd=$biaya_asuransi['mtd'];
			?>
			 <td align="right"><?php echo number_format(compat_num($biaya_asuransi_ytd),2,'.',','); ?></td>
			<td align="right"><?php echo number_format(compat_num($biaya_asuransi_mtd),2,'.',','); ?></td>
		    </tr>
				<tr>
				  <td align="center">&nbsp;</td>
				  <td>&nbsp c. BIAYA TENAGA KERJA-GAJI &amp; UPAH</td>
				   <?php
			$biaya_tk=mysql_fetch_array(mysql_query("select sum(sisa_debet) - sum(sisa_kredit)  as ytd, sum(rl_debet) - sum(rl_kredit) as mtd from tabel_rugi_laba where gr ='77' and unit ='$unit'"));
			$biaya_tk_ytd=$biaya_tk['ytd'];
			$biaya_tk_mtd=$biaya_tk['mtd'];
			?>
			 <td align="right"><?php echo number_format(compat_num($biaya_tk_ytd),2,'.',','); ?></td>
			<td align="right"><?php echo number_format(compat_num($biaya_tk_mtd),2,'.',','); ?></td>
		    </tr>
				<tr>
				  <td align="center">&nbsp;</td>
				  <td>&nbsp d. BIAYA TENAGA KERJA-GAJI &amp; UPAH PENGURUS/PENGAWAS/DPS</td>
				  	   <?php
			$biaya_tk_pengurus=mysql_fetch_array(mysql_query("select  sum(sisa_debet) - sum(sisa_kredit)  as ytd, sum(rl_debet) - sum(rl_kredit) as mtd  from tabel_rugi_laba where gr ='78' and unit ='$unit'"));
			$biaya_tk_pengurus_ytd=$biaya_tk_pengurus['ytd'];
			$biaya_tk_pengurus_mtd=$biaya_tk_pengurus['mtd'];
			?>
			 <td align="right"><?php echo number_format(compat_num($biaya_tk_pengurus_ytd),2,'.',','); ?></td>
			<td align="right"><?php echo number_format(compat_num($biaya_tk_pengurus_mtd),2,'.',','); ?></td>
		    </tr>
				<tr><td align="center">&nbsp;</td>
				  <td>&nbsp e. BIAYA TENAGA KERJA-LAINNYA</td>
				 <?php
			$biaya_tk_lainnya=mysql_fetch_array(mysql_query("select sum(sisa_debet) - sum(sisa_kredit)  as ytd, sum(rl_debet) - sum(rl_kredit) as mtd from tabel_rugi_laba where gr ='79' and unit ='$unit'"));
			$biaya_tk_lainnya_ytd=$biaya_tk_lainnya['ytd'];
			$biaya_tk_lainnya_mtd=$biaya_tk_lainnya['mtd'];
			?>
			 <td align="right"><?php echo number_format(compat_num($biaya_tk_lainnya_ytd),2,'.',','); ?></td>
			<td align="right"><?php echo number_format(compat_num($biaya_tk_lainnya_mtd),2,'.',','); ?></td>
				</tr>
			<tr>
				  <td align="center">&nbsp;</td>
				  <td>&nbsp f. BIAYA PENDIDIKAN DAN PELATIHAN</td>
				  <?php
			$biaya_pdk_plth=mysql_fetch_array(mysql_query("select  sum(sisa_debet) - sum(sisa_kredit)  as ytd, sum(rl_debet) - sum(rl_kredit) as mtd from tabel_rugi_laba where gr ='80' and unit ='$unit'"));
			$biaya_pdk_plth_ytd=$biaya_pdk_plth['ytd'];
			$biaya_pdk_plth_mtd=$biaya_pdk_plth['mtd'];
			?>
			 <td align="right"><?php echo number_format(compat_num($biaya_pdk_plth_ytd),2,'.',','); ?></td>
			<td align="right"><?php echo number_format(compat_num($biaya_pdk_plth_mtd),2,'.',','); ?></td>
		    </tr>
				<tr>
				  <td align="center">&nbsp;</td>
				  <td>&nbsp g. BIAYA SEWA</td>
				   <?php
			$biaya_sewa=mysql_fetch_array(mysql_query("select  sum(sisa_debet) - sum(sisa_kredit)  as ytd, sum(rl_debet) - sum(rl_kredit) as mtd from tabel_rugi_laba where gr ='81' and unit ='$unit'"));
			$biaya_sewa_ytd=$biaya_sewa['ytd'];
			$biaya_sewa_mtd=$biaya_sewa['mtd'];
			?>
			 <td align="right"><?php echo number_format(compat_num($biaya_sewa_ytd),2,'.',','); ?></td>
			<td align="right"><?php echo number_format(compat_num($biaya_sewa_mtd),2,'.',','); ?></td>
		    </tr>
				<tr>
				  <td align="center">&nbsp;</td>
				  <td>&nbsp h. BIAYA PROMOSI</td>
				 <?php
			$biaya_promosi=mysql_fetch_array(mysql_query("select  sum(sisa_debet) - sum(sisa_kredit)  as ytd, sum(rl_debet) - sum(rl_kredit) as mtd from tabel_rugi_laba where gr ='82' and unit ='$unit'"));
			$biaya_promosi_ytd=$biaya_promosi['ytd'];
			$biaya_promosi_mtd=$biaya_promosi['mtd'];
			?>
			 <td align="right"><?php echo number_format(compat_num($biaya_promosi_ytd),2,'.',','); ?></td>
			<td align="right"><?php echo number_format(compat_num($biaya_promosi_mtd),2,'.',','); ?></td>
		    </tr>
				<tr>
				  <td align="center">&nbsp;</td>
				  <td>&nbsp i. BIAYA PAJAK</td>
				<?php
			$biaya_pajak=mysql_fetch_array(mysql_query("select  sum(sisa_debet) - sum(sisa_kredit)  as ytd, sum(rl_debet) - sum(rl_kredit) as mtd from tabel_rugi_laba where gr ='83' and unit ='$unit'"));
			$biaya_pajak_ytd=$biaya_pajak['ytd'];
			$biaya_pajak_mtd=$biaya_pajak['mtd'];
			?>
			 <td align="right"><?php echo number_format(compat_num($biaya_pajak_ytd),2,'.',','); ?></td>
			<td align="right"><?php echo number_format(compat_num($biaya_pajak_mtd),2,'.',','); ?></td>
		    </tr>
				<tr>
				  <td align="center">&nbsp;</td>
				  <td>&nbsp j. BIAYA PEMELIHARAAN DAN PERBAIKAN</td>
				 <?php
			$biaya_pemeliharanaan=mysql_fetch_array(mysql_query("select sum(sisa_debet) - sum(sisa_kredit)  as ytd, sum(rl_debet) - sum(rl_kredit) as mtd from tabel_rugi_laba where gr ='84' and unit ='$unit'"));
			$biaya_pemeliharanaan_ytd=$biaya_pemeliharanaan['ytd'];
			$biaya_pemeliharanaan_mtd=$biaya_pemeliharanaan['mtd'];
			?>
			 <td align="right"><?php echo number_format(compat_num($biaya_pemeliharanaan_ytd),2,'.',','); ?></td>
			<td align="right"><?php echo number_format(compat_num($biaya_pemeliharanaan_mtd),2,'.',','); ?></td>
		    </tr>
				<tr>
				  <td align="center">&nbsp;</td>
				  <td>&nbsp k. BIAYA PENYUSUTAN AKTIVA TETAP DAN INVENTARIS </td>
				 <?php
			$biaya_nyusut_aktiva=mysql_fetch_array(mysql_query("select  sum(sisa_debet) - sum(sisa_kredit)  as ytd, sum(rl_debet) - sum(rl_kredit) as mtd from tabel_rugi_laba where gr ='85' and unit ='$unit'"));
			$biaya_nyusut_aktiva_ytd=$biaya_nyusut_aktiva['ytd'];
			$biaya_nyusut_aktiva_mtd=$biaya_nyusut_aktiva['mtd'];
			?>
			 <td align="right"><?php echo number_format(compat_num($biaya_nyusut_aktiva_ytd),2,'.',','); ?></td>
			<td align="right"><?php echo number_format(compat_num($biaya_nyusut_aktiva_mtd),2,'.',','); ?></td>
		    </tr>
				<tr>
				  <td align="center">&nbsp;</td>
				  <td>&nbsp l. BIAYA YANG DITANGGUHKAN</td>
				  <?php
			$biaya_tangguh=mysql_fetch_array(mysql_query("select sum(sisa_debet) - sum(sisa_kredit)  as ytd, sum(rl_debet) - sum(rl_kredit) as mtd from tabel_rugi_laba where gr ='86' and unit ='$unit'"));
			$biaya_tangguh_aktiva_ytd=$biaya_tangguh['ytd'];
			$biaya_tangguh_aktiva_mtd=$biaya_tangguh['mtd'];
			?>
			 <td align="right"><?php echo number_format(compat_num($biaya_tangguh_aktiva_ytd),2,'.',','); ?></td>
			<td align="right"><?php echo number_format(compat_num($biaya_tangguh_aktiva_mtd),2,'.',','); ?></td>
		    </tr>
				<tr>
				  <td align="center">&nbsp;</td>
				  <td>&nbsp m. BIAYA PPA-SURAT BERHARGA</td>
				    <?php
			$biaya_ppa_surat=mysql_fetch_array(mysql_query("select  sum(sisa_debet) - sum(sisa_kredit)  as ytd, sum(rl_debet) - sum(rl_kredit) as mtd from tabel_rugi_laba where gr ='87' and unit ='$unit'"));
			$biaya_ppa_surat_ytd=$biaya_ppa_surat['ytd'];
			$biaya_ppa_surat_mtd=$biaya_ppa_surat['mtd'];
			?>
			 <td align="right"><?php echo number_format(compat_num($biaya_ppa_surat_ytd),2,'.',','); ?></td>
			<td align="right"><?php echo number_format(compat_num($biaya_ppa_surat_mtd),2,'.',','); ?></td>
		    </tr>
				<tr>
				  <td align="center">&nbsp;</td>
				  <td>&nbsp n. BIAYA PPA-PENEMPATAN DANA</td>
			<?php
			$biaya_ppa_dana=mysql_fetch_array(mysql_query("select sum(sisa_debet) - sum(sisa_kredit)  as ytd, sum(rl_debet) - sum(rl_kredit) as mtd from tabel_rugi_laba where gr ='88' and unit ='$unit'"));
			$biaya_ppa_dana_ytd=$biaya_ppa_dana['ytd'];
			$biaya_ppa_dana_mtd=$biaya_ppa_dana['mtd'];
			?>
			 <td align="right"><?php echo number_format(compat_num($biaya_ppa_dana_ytd),2,'.',','); ?></td>
			<td align="right"><?php echo number_format(compat_num($biaya_ppa_dana_mtd),2,'.',','); ?></td>
		    </tr>
				<tr>
				  <td align="center">&nbsp;</td>
				  <td>&nbsp o. BIAYA PPA-Piutang MUDHARABAH</td>
				  <?php
			$biaya_ppa_piutang=mysql_fetch_array(mysql_query("select sum(sisa_debet) - sum(sisa_kredit)  as ytd, sum(rl_debet) - sum(rl_kredit) as mtd from tabel_rugi_laba where gr ='89' and unit ='$unit'"));
			$biaya_ppa_piutang_ytd=$biaya_ppa_piutang['ytd'];
			$biaya_ppa_piutang_mtd=$biaya_ppa_piutang['mtd'];
			?>
			 <td align="right"><?php echo number_format(compat_num($biaya_ppa_piutang_ytd),2,'.',','); ?></td>
			<td align="right"><?php echo number_format(compat_num($biaya_ppa_piutang_mtd),2,'.',','); ?></td>
		    </tr>
				<tr>
				  <td align="center">&nbsp;</td>
				  <td>&nbsp p. BIAYA PPA-PYD PIUTANG ISTISHNA</td>
				  <?php
			$biaya_ppa_pyd_is=mysql_fetch_array(mysql_query("select  sum(sisa_debet) - sum(sisa_kredit)  as ytd, sum(rl_debet) - sum(rl_kredit) as mtd from tabel_rugi_laba where gr ='90' and unit ='$unit'"));
			$biaya_ppa_pyd_is_ytd=$biaya_ppa_pyd_is['ytd'];
			$biaya_ppa_pyd_is_mtd=$biaya_ppa_pyd_is['mtd'];
			?>
			 <td align="right"><?php echo number_format(compat_num($biaya_ppa_pyd_is_ytd),2,'.',','); ?></td>
			<td align="right"><?php echo number_format(compat_num($biaya_ppa_pyd_is_mtd),2,'.',','); ?></td>
		    </tr>
				<tr>
				  <td align="center">&nbsp;</td>
				  <td>&nbsp q. BIAYA PPA-PYD PIUTANG QARDUL HASAN</td>
				  <?php
			$biaya_ppa_pyd_hasan=mysql_fetch_array(mysql_query("select sum(sisa_debet) - sum(sisa_kredit)  as ytd, sum(rl_debet) - sum(rl_kredit) as mtd from tabel_rugi_laba where gr ='91' and unit ='$unit'"));
			$biaya_ppa_pyd_hasan_ytd=$biaya_ppa_pyd_hasan['ytd'];
			$biaya_ppa_pyd_hasan_mtd=$biaya_ppa_pyd_hasan['mtd'];
			?>
			 <td align="right"><?php echo number_format(compat_num($biaya_ppa_pyd_hasan_ytd),2,'.',','); ?></td>
			<td align="right"><?php echo number_format(compat_num($biaya_ppa_pyd_hasan_mtd),2,'.',','); ?></td>
		    </tr>
				<tr>
				  <td align="center">&nbsp;</td>
				  <td>&nbsp r. BIAYA PENYUSUTAN/AMORTISASI AKTIVA IJARAH</td>
				   <?php
			$biaya_nyusut_amor=mysql_fetch_array(mysql_query("select  sum(sisa_debet) - sum(sisa_kredit)  as ytd, sum(rl_debet) - sum(rl_kredit) as mtd from tabel_rugi_laba where gr ='92' and unit ='$unit'"));
			$biaya_nyusut_amor_ytd=$biaya_nyusut_amor['ytd'];
			$biaya_nyusut_amor_mtd=$biaya_nyusut_amor['mtd'];
			?>
			 <td align="right"><?php echo number_format(compat_num($biaya_nyusut_amor_ytd),2,'.',','); ?></td>
			<td align="right"><?php echo number_format(compat_num($biaya_nyusut_amor_mtd),2,'.',','); ?></td>
		 	</tr>
				<tr>
				  <td align="center">&nbsp;</td>
				  <td>&nbsp s. BIAYA PPA-PYD MUDHARABAH</td>
				  <?php
			$biaya_pyd_mudharabah=mysql_fetch_array(mysql_query("select sum(sisa_debet) - sum(sisa_kredit)  as ytd, sum(rl_debet) - sum(rl_kredit) as mtd from tabel_rugi_laba where gr ='93' and unit ='$unit'"));
			$biaya_pyd_mudharabah_ytd=$biaya_pyd_mudharabah['ytd'];
			$biaya_pyd_mudharabah_mtd=$biaya_pyd_mudharabah['mtd'];
			?>
			 <td align="right"><?php echo number_format(compat_num($biaya_pyd_mudharabah_ytd),2,'.',','); ?></td>
			<td align="right"><?php echo number_format(compat_num($biaya_pyd_mudharabah_mtd),2,'.',','); ?></td>
		    </tr>
				<tr>
				  <td align="center">&nbsp;</td>
				  <td>&nbsp t. BIAYA PPA-PYD MUSYARAKAH</td>
				  <?php
			$biaya_pyd_mysa=mysql_fetch_array(mysql_query("select  sum(sisa_debet) - sum(sisa_kredit)  as ytd, sum(rl_debet) - sum(rl_kredit) as mtd from tabel_rugi_laba where gr ='94' and unit ='$unit'"));
			$biaya_pyd_mysa_ytd=$biaya_pyd_mysa['ytd'];
			$biaya_pyd_mysa_mtd=$biaya_pyd_mysa['mtd'];
			?>
			 <td align="right"><?php echo number_format(compat_num($biaya_pyd_mysa_ytd),2,'.',','); ?></td>
			<td align="right"><?php echo number_format(compat_num($biaya_pyd_mysa_mtd),2,'.',','); ?></td>
		    </tr>
				<tr>
				  <td align="center">&nbsp;</td>
				  <td>&nbsp u. BIAYA PPA-PENYERTAAN</td>
				  <?php
			$biaya_ppa_penyertaan=mysql_fetch_array(mysql_query("select  sum(sisa_debet) - sum(sisa_kredit)  as ytd, sum(rl_debet) - sum(rl_kredit) as mtd from tabel_rugi_laba where gr ='95' and unit ='$unit'"));
			$biaya_ppa_penyertaan_ytd=$biaya_ppa_penyertaan['ytd'];
			$biaya_ppa_penyertaan_mtd=$biaya_ppa_penyertaan['mtd'];
			?>
			 <td align="right"><?php echo number_format(compat_num($biaya_ppa_penyertaan_ytd),2,'.',','); ?></td>
			<td align="right"><?php echo number_format(compat_num($biaya_ppa_penyertaan_mtd),2,'.',','); ?></td>
		    </tr>
				<tr>
				  <td align="center">&nbsp;</td>
				  <td>&nbsp v. BIAYA BARANG JASA</td>
				  <?php
			$biaya_barang_jasa=mysql_fetch_array(mysql_query("select sum(sisa_debet) - sum(sisa_kredit)  as ytd, sum(rl_debet) - sum(rl_kredit) as mtd from tabel_rugi_laba where gr ='96' and unit ='$unit'"));
			$biaya_barang_jasa_ytd=$biaya_barang_jasa['ytd'];
			$biaya_barang_jasa_mtd=$biaya_barang_jasa['mtd'];
			?>
			 <td align="right"><?php echo number_format(compat_num($biaya_barang_jasa_ytd),2,'.',','); ?></td>
			<td align="right"><?php echo number_format(compat_num($biaya_barang_jasa_mtd),2,'.',','); ?></td>
		    </tr>
				<tr>
				  <td align="center">&nbsp;</td>
				  <td>&nbsp w. BIAYA OPERASIONAL LAINNYA</td>
				 <?php
			$biaya_ops_lain=mysql_fetch_array(mysql_query("select  sum(sisa_debet) - sum(sisa_kredit)  as ytd, sum(rl_debet) - sum(rl_kredit) as mtd from tabel_rugi_laba where gr ='97' and unit ='$unit'"));
			$biaya_ops_lain_ytd=$biaya_ops_lain['ytd'];
			$biaya_ops_lain_mtd=$biaya_ops_lain['mtd'];
			?>
			 <td align="right"><?php echo number_format(compat_num($biaya_ops_lain_ytd),2,'.',','); ?></td>
			<td align="right"><?php echo number_format(compat_num($biaya_ops_lain_mtd),2,'.',','); ?></td>
		    </tr>
				<tr>
				  <td align="center">&nbsp;</td>
				  <td><strong>JUMLAH BIAYA OPERASIONAL USAHA</strong></td>
				 <?php
				 $tot_biaya_usaha_ytd=$biaya_asuransi_dpk_ytd+$biaya_asuransi_ytd+$biaya_tk_ytd+$biaya_tk_pengurus_ytd+$biaya_tk_lainnya_ytd+$biaya_pdk_plth_ytd+$biaya_sewa_ytd+$biaya_promosi_ytd+$biaya_pajak_ytd+$biaya_pemeliharanaan_ytd+$biaya_nyusut_aktiva_ytd+$biaya_tangguh_aktiva_ytd+$biaya_ppa_surat_ytd+$biaya_ppa_dana_ytd+$biaya_ppa_piutang_ytd+$biaya_ppa_pyd_is_ytd+$biaya_ppa_pyd_hasan_ytd+$biaya_nyusut_amor_ytd+$biaya_pyd_mudharabah_ytd+$biaya_pyd_mysa_ytd+$biaya_ppa_penyertaan_ytd+$biaya_barang_jasa_ytd+$biaya_ops_lain_ytd;
				 $tot_biaya_usaha_mtd=$biaya_asuransi_dpk_mtd+$biaya_asuransi_mtd+$biaya_tk_mtd+$biaya_tk_pengurus_mtd+$biaya_tk_lainnya_mtd+$biaya_pdk_plth_mtd+$biaya_sewa_mtd+$biaya_promosi_mtd+$biaya_pajak_mtd+$biaya_pemeliharanaan_mtd+$biaya_nyusut_aktiva_mtd+$biaya_tangguh_aktiva_mtd+$biaya_ppa_surat_mtd+$biaya_ppa_dana_mtd+$biaya_ppa_piutang_mtd+$biaya_ppa_pyd_is_mtd+$biaya_ppa_pyd_hasan_mtd+$biaya_nyusut_amor_mtd+$biaya_pyd_mudharabah_mtd+$biaya_pyd_mysa_mtd+$biaya_ppa_penyertaan_mtd+$biaya_barang_jasa_mtd+$biaya_ops_lain_mtd;
				 ?>
				 <td align="right"><?php echo number_format(compat_num($tot_biaya_usaha_ytd),2,'.',','); ?></td>
			<td align="right"><?php echo number_format(compat_num($tot_biaya_usaha_mtd),2,'.',','); ?></td>
		    </tr>
				<tr>
				  <td align="center">&nbsp;</td>
				  <td><strong>TOTAL BIAYA OPERASIONAL USAHA (1+2)</strong></td>
				  <?php
				  $total_biaya_ops_usaha_ytd=$total_biaya_pokok_ytd + $tot_biaya_usaha_ytd;
					$total_biaya_ops_usaha_mtd=$total_biaya_pokok_mtd + $tot_biaya_usaha_mtd;
				  ?>
				   <td align="right"><?php echo number_format(compat_num($total_biaya_ops_usaha_ytd),2,'.',','); ?></td>
			<td align="right"><?php echo number_format(compat_num($total_biaya_ops_usaha_mtd),2,'.',','); ?></td>
			  </tr>
				<tr>
				  <td align="center">&nbsp;</td>
				  <td><strong><font color="#333333">LABA (RUGI) OPERASIONAL (I-II)</font></strong></td>
				  <?php
			$laba_rugi_ytd=$total_pdp_ops_ytd - $total_biaya_ops_usaha_ytd;
			$laba_rugi_mtd=$total_pdp_ops_mtd - $total_biaya_ops_usaha_mtd;
			?>
			 <td align="right"><?php echo number_format(compat_num($laba_rugi_ytd),2,'.',','); ?></td>
			<td align="right"><?php echo number_format(compat_num($laba_rugi_mtd),2,'.',','); ?></td>
			  </tr>
			
			
			<tr><td colspan="4"></tr>
			<tr>
			  <td><div align="center"><strong>III.</strong></div></td>
			  <td><strong>PENDAPATAN NON OPERASIONAL</strong></td>
			  <td align="right">&nbsp;</td>
			  <td align="right">&nbsp;</td>
		    </tr>
			<tr>
			  <td>&nbsp;</td>
			  <td>1. KEUNTUNGAN PENJUALAN AKTIVA TETAP</td>
			   <?php
			$untung_penjualan=mysql_fetch_array(mysql_query("select  sum(sisa_kredit) - sum(sisa_debet)  as ytd, sum(rl_kredit) - sum(rl_debet) as mtd from tabel_rugi_laba where gr ='98' and unit ='$unit'"));
			$untung_penjualann_ytd=$untung_penjualan['ytd'];
			$untung_penjualan_mtd=$untung_penjualan['mtd'];
			?>
			 <td align="right"><?php echo number_format(compat_num($untung_penjualann_ytd),2,'.',','); ?></td>
			<td align="right"><?php echo number_format(compat_num($untung_penjualan_mtd),2,'.',','); ?></td>
		    </tr>
			<tr>
			  <td>&nbsp;</td>
			  <td>2. PENDAPATAN BAGI HASIL/IMBALAN ANTAR KANTOR</td>
			  <?php
			$pendapatan_antar_kantor=mysql_fetch_array(mysql_query("select  sum(sisa_kredit) - sum(sisa_debet)  as ytd, sum(rl_kredit) - sum(rl_debet) as mtd from tabel_rugi_laba where gr ='99' and unit ='$unit'"));
			$pendapatan_antar_kantor_ytd=$pendapatan_antar_kantor['ytd'];
			$pendapatan_antar_kantor_mtd=$pendapatan_antar_kantor['mtd'];
			?>
			 <td align="right"><?php echo number_format(compat_num($pendapatan_antar_kantor_ytd),2,'.',','); ?></td>
			<td align="right"><?php echo number_format(compat_num($pendapatan_antar_kantor_mtd),2,'.',','); ?></td>
		    </tr>
			<tr>
			  <td>&nbsp;</td>
			  <td>3. PENDAPATAN NON OPERASIONAL SEWA</td>
			   <?php
			$pendapatan_non_ops=mysql_fetch_array(mysql_query("select  sum(sisa_kredit) - sum(sisa_debet)  as ytd, sum(rl_kredit) - sum(rl_debet) as mtd from tabel_rugi_laba where gr ='100' and unit ='$unit'"));
			$pendapatan_non_ops_ytd=$pendapatan_non_ops['ytd'];
			$pendapatan_non_ops_mtd=$pendapatan_non_ops['mtd'];
			?>
			 <td align="right"><?php echo number_format(compat_num($pendapatan_non_ops_ytd),2,'.',','); ?></td>
			<td align="right"><?php echo number_format(compat_num($pendapatan_non_ops_mtd),2,'.',','); ?></td>
		    </tr>
			<tr>
			  <td>&nbsp;</td>
			  <td>4. PENDAPATAN NON OPERASIONAL LAINNYA</td>
			   <?php
			$pendapatan_nonops_lain=mysql_fetch_array(mysql_query("select  sum(sisa_kredit) - sum(sisa_debet)  as ytd, sum(rl_kredit) - sum(rl_debet) as mtd from tabel_rugi_laba where gr ='101' and unit ='$unit'"));
			$pendapatan_nonops_lain_ytd=$pendapatan_nonops_lain['ytd'];
			$pendapatan_nonops_lain_mtd=$pendapatan_nonops_lain['mtd'];
			?>
			 <td align="right"><?php echo number_format(compat_num($pendapatan_nonops_lain_ytd),2,'.',','); ?></td>
			<td align="right"><?php echo number_format(compat_num($pendapatan_nonops_lain_mtd),2,'.',','); ?></td>
		    </tr>
			<tr>
			  <td>&nbsp;</td>
			  <td><strong>JUMLAH PENDAPATAN  NON OPERASIONAL</strong></td>
			   <?php
			   $jumlah_beban_non_ops_ytd=$untung_penjualann_ytd+$pendapatan_antar_kantor_ytd+$pendapatan_non_ops_ytd+$pendapatan_nonops_lain_ytd;
			   $jumlah_beban_non_ops_mtd=$untung_penjualann_mtd+$pendapatan_antar_kantor_mtd+$pendapatan_non_ops_mtd+$pendapatan_nonops_lain_mtd;
			   
			   ?>
			   
			   <td align="right"><?php echo number_format(compat_num($jumlah_beban_non_ops_ytd),2,'.',','); ?></td>
			<td align="right"><?php echo number_format(compat_num($jumlah_beban_non_ops_mtd),2,'.',','); ?></td>
		    </tr>
			<tr>
			  <td><div align="center"><strong>IV.</strong></div></td>
			  <td><strong>BEBAN NON OPERASIONAL</strong></td>
			  <td align="right">&nbsp;</td>
			  <td align="right">&nbsp;</td>
		    </tr>
			<tr>
			  <td></td>
			  <td>1. KERUGIAN KARENA PENJUALAN/KEHILANGAN ATI</td>
			 <?php
			$rugi_penjualan=mysql_fetch_array(mysql_query("select  sum(sisa_kredit) - sum(sisa_debet)  as ytd, sum(rl_kredit) - sum(rl_debet) as mtd from tabel_rugi_laba where gr ='102' and unit ='$unit'"));
			$rugi_penjualan_ytd=$rugi_penjualan['ytd'];
			$rugi_penjualan_mtd=$rugi_penjualan['mtd'];
			?>
			 <td align="right"><?php echo number_format(compat_num($rugi_penjualan_ytd),2,'.',','); ?></td>
			<td align="right"><?php echo number_format(compat_num($rugi_penjualan_mtd),2,'.',','); ?></td>
		    </tr>
			<tr>
			  <td></td>
			  <td>2. DENDA-DENDA/SANKSI-SANKSI</td>
			   <?php
			$denda_denda=mysql_fetch_array(mysql_query("select  sum(sisa_kredit) - sum(sisa_debet)  as ytd, sum(rl_kredit) - sum(rl_debet) as mtd from tabel_rugi_laba where gr ='103' and unit ='$unit'"));
			$denda_denda_ytd=$denda_denda['ytd'];
			$denda_denda_mtd=$denda_denda['mtd'];
			?>
			 <td align="right"><?php echo number_format(compat_num($denda_denda_ytd),2,'.',','); ?></td>
			<td align="right"><?php echo number_format(compat_num($denda_denda_mtd),2,'.',','); ?></td>
		    </tr>
			<tr>
			  <td></td>
			  <td>3. BEBAN BAGI HSL/IMBALAN ANTAR KANTOR</td>
			  <?php
			$beban_imbalan_kantor=mysql_fetch_array(mysql_query("select  sum(sisa_kredit) - sum(sisa_debet)  as ytd, sum(rl_kredit) - sum(rl_debet) as mtd from tabel_rugi_laba where gr ='104' and unit ='$unit'"));
			$rbeban_imbalan_kantor_ytd=$beban_imbalan_kantor['ytd'];
			$rbeban_imbalan_kantor_mtd=$beban_imbalan_kantor['mtd'];
			?>
			 <td align="right"><?php echo number_format(compat_num($rbeban_imbalan_kantor_ytd),2,'.',','); ?></td>
			<td align="right"><?php echo number_format(compat_num($rbeban_imbalan_kantor_mtd),2,'.',','); ?></td>
		    </tr>
			<tr>
			  <td></td>
			  <td>4. BEBAN NON OPERASIONAL LAINNYA</td>
			  <?php
			$beban_non_ops=mysql_fetch_array(mysql_query("select  sum(sisa_debet) - sum(sisa_kredit)  as ytd, sum(rl_debet) - sum(rl_kredit) as mtd from tabel_rugi_laba where gr ='105' and unit ='$unit'"));
			$beban_non_ops_ytd=$beban_non_ops['ytd'];
			$beban_non_ops_mtd=$beban_non_ops['mtd'];
			?>
			 <td align="right"><?php echo number_format(compat_num($beban_non_ops_ytd),2,'.',','); ?></td>
			<td align="right"><?php echo number_format(compat_num($beban_non_ops_mtd),2,'.',','); ?></td>
		    </tr>
			<tr>
			  <td></td>
			  <td><strong>JUMLAH  BEBAN NON OPERASIONAL</strong></td>
			   <?php
			$jumlah_beban_no_ops_ytd=$rugi_penjualan_ytd+$denda_denda_ytd+$rbeban_imbalan_kantor_ytd+$beban_non_ops_ytd;
			$jumlah_beban_no_ops_mtd=$rugi_penjualan_mtd+$denda_denda_mtd+$rbeban_imbalan_kantor_mtd+$beban_non_ops_mtd;
			?>
			 <td align="right"><?php echo number_format(compat_num($jumlah_beban_no_ops_ytd),2,'.',','); ?></td>
			<td align="right"><?php echo number_format(compat_num($jumlah_beban_no_ops_mtd),2,'.',','); ?></td>
		    </tr>
			<tr><td></td><td><strong>PENDAPATAN (BEBAN) NON OPERASIONAL (III+IV)</strong></td>
			<?php
			$Total_pendapatan_ytd=$jumlah_beban_non_ops_ytd+$jumlah_beban_no_ops_ytd;
			$Total_pendapatan_mtd=$jumlah_beban_non_ops_mtd+$jumlah_beban_no_ops_mtd;
			?>
			 <td align="right"><?php echo number_format(compat_num($Total_pendapatan_ytd),2,'.',','); ?></td>
			<td align="right"><?php echo number_format(compat_num($Total_pendapatan_mtd),2,'.',','); ?></td>
			<tr><td></td>
			<td><strong><font color="#333333">SHU SEBELUM PAJAK (LABA OPS + PENDAPANTAN (BEBAN)</font></strong></td>
			<?php
			$shu_sb_pajak_ytd=$laba_rugi_ytd+$Total_pendapatan_ytd;
			$shu_sb_pajak_mtd=$laba_rugi_mtd+$Total_pendapatan_mtd;
			?>
			 <td align="right"><?php echo number_format(compat_num($shu_sb_pajak_ytd),2,'.',','); ?></td>
			<td align="right"><?php echo number_format(compat_num($shu_sb_pajak_mtd),2,'.',','); ?></td>
			
			</tr>
			<tr><td><div align="center"><strong>V.</strong></div></td>
			  <td><strong><font color="#333333">PAJAK PENGHASILAN </font></strong></td>
			<?php
			$pajak_penghasilan=mysql_fetch_array(mysql_query("select  sum(sisa_kredit) - sum(sisa_debet)  as ytd, sum(rl_kredit) - sum(rl_debet) as mtd from tabel_rugi_laba where gr ='106' and unit ='$unit'"));
			$pajak_penghasilan_ytd=$pajak_penghasilan['ytd'];
			$bpajak_penghasilan_mtd=$pajak_penghasilan['mtd'];
			?>
			 <td align="right"><?php echo number_format(compat_num($pajak_penghasilan_ytd),2,'.',','); ?></td>
			 <td align="right"><?php echo number_format(compat_num($bpajak_penghasilan_mtd),2,'.',','); ?></td>
			</tr>
			<div
			</table>

		  </p>
  </div>
</div>

			<?php
			}
			?>

