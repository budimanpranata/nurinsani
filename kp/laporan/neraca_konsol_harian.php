<?php require_once __DIR__ . '/../../php8_compat.php';

session_start();
ob_start();
$_SESSION['units']=$unit;
$tgl_cari=$_SESSION['tgl_cari_konsols'];

header("Content-type: application/vnd-ms-excel");
header("Content-Disposition: attachment; filename=consol.xls");
 include"../../indo_tgl.php";

$jenis=$_SESSION['jenis'];
$tgl=tgl_indo($tgl_cari);
if($jenis=='currnet'){
$sumber="tabel_master_harian";
}else{
$sumber = "tabel_master_eoy";
}

var_dump($jenis);
//membuat header HTML
echo "<html>
		<head>
			<title>NeracaPercobaan</title>
			<title>NeracaPercobaan</title>
			<style>
				body {font-family:tahoma, arial}
				table {border-collapse: collapse}
				th, td {font-size: 17px; border: 1px solid #DEDEDE; padding: 5px 7px; color: #303030}
				th {background: #EAEAEA; font-size: 17px; border-color:#B0B0B0}
				.subtotal td{background: #F5F5F5}
				.total td{background: #ECECEC}
				.right{text-align: rijprght}
			</style>
		</head>
		<body>
	<B>KOPERASI SIMPAN PINJAM PEMBIAYAAN SYARIAH</b><br/>
	<B>LAPORAN PERHITUNGAN HASIL USAHA</b><br/>
	<B>KONSOLIDASI</b><br/>
	<B> ".$tgl."</B>  <br/>
	
	<strong></strong><br/>";


//melakukan koneksi ke database, parameter disesuaikan dengan lingkungan anda
include "../conn2.php";
	$koneksi=open_connection2();
	
$total_ak_001=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='001' and tanggal_awal='$tgl_cari' and gr_neraca='AKTIVA' and LINE_BALANCE='HEADING' and kode_rekening not in('1310000','1330000','1340000','1350000')  "));
$total_ak_002=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='002' and tanggal_awal='$tgl_cari' and gr_neraca='AKTIVA' and LINE_BALANCE='HEADING' and kode_rekening not in('1310000','1330000','1340000','1350000')  "));
$total_ak_003=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='003' and tanggal_awal='$tgl_cari' and gr_neraca='AKTIVA' and LINE_BALANCE='HEADING' and kode_rekening not in('1310000','1330000','1340000','1350000') "));
$total_ak_004=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='004' and tanggal_awal='$tgl_cari' and gr_neraca='AKTIVA' and LINE_BALANCE='HEADING' and kode_rekening not in('1310000','1330000','1340000','1350000') "));
$total_ak_005=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='005' and tanggal_awal='$tgl_cari' and gr_neraca='AKTIVA' and LINE_BALANCE='HEADING' and kode_rekening not in('1310000','1330000','1340000','1350000') "));
$total_ak_006=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='006' and tanggal_awal='$tgl_cari' and gr_neraca='AKTIVA' and LINE_BALANCE='HEADING' and kode_rekening not in('1310000','1330000','1340000','1350000') "));
$total_ak_007=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='007' and tanggal_awal='$tgl_cari' and gr_neraca='AKTIVA' and LINE_BALANCE='HEADING' and kode_rekening not in('1310000','1330000','1340000','1350000')  "));
$total_ak_008=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='008' and tanggal_awal='$tgl_cari' and gr_neraca='AKTIVA' and LINE_BALANCE='HEADING' and kode_rekening not in('1310000','1330000','1340000','1350000') "));
$total_ak_009=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='009' and tanggal_awal='$tgl_cari' and gr_neraca='AKTIVA' and LINE_BALANCE='HEADING' and kode_rekening not in('1310000','1330000','1340000','1350000') "));

$total_ak_010=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='010' and tanggal_awal='$tgl_cari' and gr_neraca='AKTIVA' and LINE_BALANCE='HEADING' and kode_rekening not in('1310000','1330000','1340000','1350000') "));
$total_ak_011=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='011' and tanggal_awal='$tgl_cari' and gr_neraca='AKTIVA' and LINE_BALANCE='HEADING' and kode_rekening not in('1310000','1330000','1340000','1350000') "));

$total_ak_012=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='012' and tanggal_awal='$tgl_cari' and gr_neraca='AKTIVA' and LINE_BALANCE='HEADING' and kode_rekening not in('1310000','1330000','1340000','1350000') "));
$total_ak_013=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='013' and tanggal_awal='$tgl_cari' and gr_neraca='AKTIVA' and LINE_BALANCE='HEADING' and kode_rekening not in('1310000','1330000','1340000','1350000') "));
$total_ak_014=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='014' and tanggal_awal='$tgl_cari' and gr_neraca='AKTIVA' and LINE_BALANCE='HEADING' and kode_rekening not in('1310000','1330000','1340000','1350000') "));
$total_ak_015=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='015' and tanggal_awal='$tgl_cari' and gr_neraca='AKTIVA' and LINE_BALANCE='HEADING' and kode_rekening not in('1310000','1330000','1340000','1350000') "));
$total_ak_016=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='016' and tanggal_awal='$tgl_cari' and gr_neraca='AKTIVA' and LINE_BALANCE='HEADING' and kode_rekening not in('1310000','1330000','1340000','1350000') "));


$total_ak_2001=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='2001' and tanggal_awal='$tgl_cari' and gr_neraca='AKTIVA' and LINE_BALANCE='HEADING' and kode_rekening not in('1310000','1330000','1340000','1350000') "));
$total_ak_2002=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='2002' and tanggal_awal='$tgl_cari' and gr_neraca='AKTIVA' and LINE_BALANCE='HEADING' and kode_rekening not in('1310000','1330000','1340000','1350000') "));
$total_ak_2003=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='2003' and tanggal_awal='$tgl_cari' and gr_neraca='AKTIVA' and LINE_BALANCE='HEADING' and kode_rekening not in('1310000','1330000','1340000','1350000') "));
$total_ak_2004=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='2004' and tanggal_awal='$tgl_cari' and gr_neraca='AKTIVA' and LINE_BALANCE='HEADING' and kode_rekening not in('1310000','1330000','1340000','1350000') "));
$total_ak_2005=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='2005' and tanggal_awal='$tgl_cari' and gr_neraca='AKTIVA' and LINE_BALANCE='HEADING' and kode_rekening not in('1310000','1330000','1340000','1350000') "));
$total_ak_2006=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='2006' and tanggal_awal='$tgl_cari' and gr_neraca='AKTIVA' and LINE_BALANCE='HEADING' and kode_rekening not in('1310000','1330000','1340000','1350000') "));
$total_ak_2007=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='2007' and tanggal_awal='$tgl_cari' and gr_neraca='AKTIVA' and LINE_BALANCE='HEADING' and kode_rekening not in('1310000','1330000','1340000','1350000') "));
$total_ak_2008=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='2008' and tanggal_awal='$tgl_cari' and gr_neraca='AKTIVA' and LINE_BALANCE='HEADING' and kode_rekening not in('1310000','1330000','1340000','1350000') "));
$total_ak_2009=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='2009' and tanggal_awal='$tgl_cari' and gr_neraca='AKTIVA' and LINE_BALANCE='HEADING' and kode_rekening not in('1310000','1330000','1340000','1350000') "));
$total_ak_2010=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='2010' and tanggal_awal='$tgl_cari' and gr_neraca='AKTIVA' and LINE_BALANCE='HEADING' and kode_rekening not in('1310000','1330000','1340000','1350000') "));
$total_ak_2011=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='2011' and tanggal_awal='$tgl_cari' and gr_neraca='AKTIVA' and LINE_BALANCE='HEADING' and kode_rekening not in('1310000','1330000','1340000','1350000') "));
$total_ak_2012=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='2012' and tanggal_awal='$tgl_cari' and gr_neraca='AKTIVA' and LINE_BALANCE='HEADING' and kode_rekening not in('1310000','1330000','1340000','1350000') "));
$total_ak_2013=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='2013' and tanggal_awal='$tgl_cari' and gr_neraca='AKTIVA' and LINE_BALANCE='HEADING' and kode_rekening not in('1310000','1330000','1340000','1350000') "));
$total_ak_2014=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='2014' and tanggal_awal='$tgl_cari' and gr_neraca='AKTIVA' and LINE_BALANCE='HEADING' and kode_rekening not in('1310000','1330000','1340000','1350000') "));
$total_ak_2015=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='2015' and tanggal_awal='$tgl_cari' and gr_neraca='AKTIVA' and LINE_BALANCE='HEADING' and kode_rekening not in('1310000','1330000','1340000','1350000') "));
$total_ak_2016=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='2016' and tanggal_awal='$tgl_cari' and gr_neraca='AKTIVA' and LINE_BALANCE='HEADING' and kode_rekening not in('1310000','1330000','1340000','1350000') "));

$total_ak_2017=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='2017' and tanggal_awal='$tgl_cari' and gr_neraca='AKTIVA' and LINE_BALANCE='HEADING' and kode_rekening not in('1310000','1330000','1340000','1350000') "));
$total_ak_2018=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='2018' and tanggal_awal='$tgl_cari' and gr_neraca='AKTIVA' and LINE_BALANCE='HEADING' and kode_rekening not in('1310000','1330000','1340000','1350000') "));
$total_ak_2019=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='2019' and tanggal_awal='$tgl_cari' and gr_neraca='AKTIVA' and LINE_BALANCE='HEADING' and kode_rekening not in('1310000','1330000','1340000','1350000') "));
$total_ak_2020=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='2020' and tanggal_awal='$tgl_cari' and gr_neraca='AKTIVA' and LINE_BALANCE='HEADING' and kode_rekening not in('1310000','1330000','1340000','1350000') "));
$total_ak_2021=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='2021' and tanggal_awal='$tgl_cari' and gr_neraca='AKTIVA' and LINE_BALANCE='HEADING' and kode_rekening not in('1310000','1330000','1340000','1350000') "));
$total_ak_2022=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='2022' and tanggal_awal='$tgl_cari' and gr_neraca='AKTIVA' and LINE_BALANCE='HEADING' and kode_rekening not in('1310000','1330000','1340000','1350000') "));
$total_ak_2023=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='2023' and tanggal_awal='$tgl_cari' and gr_neraca='AKTIVA' and LINE_BALANCE='HEADING' and kode_rekening not in('1310000','1330000','1340000','1350000') "));
$total_ak_2024=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='2024' and tanggal_awal='$tgl_cari' and gr_neraca='AKTIVA' and LINE_BALANCE='HEADING' and kode_rekening not in('1310000','1330000','1340000','1350000') "));
$total_ak_2025=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='2025' and tanggal_awal='$tgl_cari' and gr_neraca='AKTIVA' and LINE_BALANCE='HEADING' and kode_rekening not in('1310000','1330000','1340000','1350000') "));

$total_ak_2026=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='2026' and tanggal_awal='$tgl_cari' and gr_neraca='AKTIVA' and LINE_BALANCE='HEADING' and kode_rekening not in('1310000','1330000','1340000','1350000') "));
$total_ak_2027=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='2027' and tanggal_awal='$tgl_cari' and gr_neraca='AKTIVA' and LINE_BALANCE='HEADING' and kode_rekening not in('1310000','1330000','1340000','1350000') "));

$total_ak_2028=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='2028' and tanggal_awal='$tgl_cari' and gr_neraca='AKTIVA' and LINE_BALANCE='HEADING' and kode_rekening not in('1310000','1330000','1340000','1350000') "));
$total_ak_2029=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='2029' and tanggal_awal='$tgl_cari' and gr_neraca='AKTIVA' and LINE_BALANCE='HEADING' and kode_rekening not in('1310000','1330000','1340000','1350000') "));
$total_ak_2030=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='2030' and tanggal_awal='$tgl_cari' and gr_neraca='AKTIVA' and LINE_BALANCE='HEADING' and kode_rekening not in('1310000','1330000','1340000','1350000') "));
$total_ak_2031=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='2031' and tanggal_awal='$tgl_cari' and gr_neraca='AKTIVA' and LINE_BALANCE='HEADING' and kode_rekening not in('1310000','1330000','1340000','1350000') "));
$total_ak_2032=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='2032' and tanggal_awal='$tgl_cari' and gr_neraca='AKTIVA' and LINE_BALANCE='HEADING' and kode_rekening not in('1310000','1330000','1340000','1350000') "));
$total_ak_2033=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='2033' and tanggal_awal='$tgl_cari' and gr_neraca='AKTIVA' and LINE_BALANCE='HEADING' and kode_rekening not in('1310000','1330000','1340000','1350000') "));

$total_ak_2034=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,

								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='2034' and tanggal_awal='$tgl_cari' and gr_neraca='AKTIVA' and LINE_BALANCE='HEADING' and kode_rekening not in('1310000','1330000','1340000','1350000') "));
$total_ak_2035=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,

								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='2035' and tanggal_awal='$tgl_cari' and gr_neraca='AKTIVA' and LINE_BALANCE='HEADING' and kode_rekening not in('1310000','1330000','1340000','1350000') "));
$total_ak_2036=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,

								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='2036' and tanggal_awal='$tgl_cari' and gr_neraca='AKTIVA' and LINE_BALANCE='HEADING' and kode_rekening not in('1310000','1330000','1340000','1350000') "));
$total_ak_2037=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,

								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='2037' and tanggal_awal='$tgl_cari' and gr_neraca='AKTIVA' and LINE_BALANCE='HEADING' and kode_rekening not in('1310000','1330000','1340000','1350000') "));
$total_ak_2038=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='2038' and tanggal_awal='$tgl_cari' and gr_neraca='AKTIVA' and LINE_BALANCE='HEADING' and kode_rekening not in('1310000','1330000','1340000','1350000') "));
$total_ak_2039=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='2039' and tanggal_awal='$tgl_cari' and gr_neraca='AKTIVA' and LINE_BALANCE='HEADING' and kode_rekening not in('1310000','1330000','1340000','1350000') "));
$total_ak_2040=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='2040' and tanggal_awal='$tgl_cari' and gr_neraca='AKTIVA' and LINE_BALANCE='HEADING' and kode_rekening not in('1310000','1330000','1340000','1350000') "));
$total_ak_2041=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='2041' and tanggal_awal='$tgl_cari' and gr_neraca='AKTIVA' and LINE_BALANCE='HEADING' and kode_rekening not in('1310000','1330000','1340000','1350000') "));
$total_ak_2042=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='2042' and tanggal_awal='$tgl_cari' and gr_neraca='AKTIVA' and LINE_BALANCE='HEADING' and kode_rekening not in('1310000','1330000','1340000','1350000') "));
$total_ak_2043=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='2043' and tanggal_awal='$tgl_cari' and gr_neraca='AKTIVA' and LINE_BALANCE='HEADING' and kode_rekening not in('1310000','1330000','1340000','1350000') "));
$total_ak_2044=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='2044' and tanggal_awal='$tgl_cari' and gr_neraca='AKTIVA' and LINE_BALANCE='HEADING' and kode_rekening not in('1310000','1330000','1340000','1350000') "));




$total_ak_3000=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='3000' and tanggal_awal='$tgl_cari' and gr_neraca='AKTIVA' and LINE_BALANCE='HEADING' and kode_rekening not in('1310000','1330000','1340000','1350000') "));
															

$total_ak_3001=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='3001' and tanggal_awal='$tgl_cari' and gr_neraca='AKTIVA' and LINE_BALANCE='HEADING' and kode_rekening not in('1310000','1330000','1340000','1350000') "));

$total_ak_3002=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,

								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='3002' and tanggal_awal='$tgl_cari' and gr_neraca='AKTIVA' and LINE_BALANCE='HEADING' and kode_rekening not in('1310000','1330000','1340000','1350000') "));

$total_ak_3003=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,

								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='3003' and tanggal_awal='$tgl_cari' and gr_neraca='AKTIVA' and LINE_BALANCE='HEADING' and kode_rekening not in('1310000','1330000','1340000','1350000') "));
$total_ak_3004=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,

								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='3004' and tanggal_awal='$tgl_cari' and gr_neraca='AKTIVA' and LINE_BALANCE='HEADING' and kode_rekening not in('1310000','1330000','1340000','1350000') "));
$total_ak_3005=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,

								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='3005' and tanggal_awal='$tgl_cari' and gr_neraca='AKTIVA' and LINE_BALANCE='HEADING' and kode_rekening not in('1310000','1330000','1340000','1350000') "));



$total_ak_4000=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='4000' and tanggal_awal='$tgl_cari' and gr_neraca='AKTIVA' and LINE_BALANCE='HEADING' and kode_rekening not in('1310000','1330000','1340000','1350000') "));
															

$total_ak_4001=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='4001' and tanggal_awal='$tgl_cari' and gr_neraca='AKTIVA' and LINE_BALANCE='HEADING' and kode_rekening not in('1310000','1330000','1340000','1350000') "));

$total_ak_4002=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,

								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='4002' and tanggal_awal='$tgl_cari' and gr_neraca='AKTIVA' and LINE_BALANCE='HEADING' and kode_rekening not in('1310000','1330000','1340000','1350000') "));

$total_ak_4003=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,

								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='4003' and tanggal_awal='$tgl_cari' and gr_neraca='AKTIVA' and LINE_BALANCE='HEADING' and kode_rekening not in('1310000','1330000','1340000','1350000') "));

								
$total_ak_1010=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='0000' and tanggal_awal='$tgl_cari' and gr_neraca='AKTIVA' and LINE_BALANCE='HEADING' and kode_rekening not in('1310000','1330000','1340000','1350000') "));
$total_ak_tot=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where gr_neraca='AKTIVA' and tanggal_awal='$tgl_cari' and LINE_BALANCE='HEADING' and kode_rekening not in('1310000','1330000','1340000','1350000') "));

//total pasiva
$total_pa_001=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='001' and kode_rekening in ('3901000','3902000','2500000') and tanggal_awal='$tgl_cari' and gr_neraca='PASIVA'"));
$total_pa_002=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='002' and kode_rekening in ('3901000','3902000','2500000') and tanggal_awal='$tgl_cari' and gr_neraca='PASIVA'"));
$total_pa_003=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='003' and kode_rekening in ('3901000','3902000','2500000') and tanggal_awal='$tgl_cari' and gr_neraca='PASIVA'"));
$total_pa_004=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='004' and kode_rekening in ('3901000','3902000','2500000') and tanggal_awal='$tgl_cari' and gr_neraca='PASIVA'"));
$total_pa_005=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='005' and kode_rekening in ('3901000','3902000','2500000') and tanggal_awal='$tgl_cari' and gr_neraca='PASIVA'"));
$total_pa_006=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='006' and kode_rekening in ('3901000','3902000','2500000') and tanggal_awal='$tgl_cari' and gr_neraca='PASIVA'"));
$total_pa_007=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='007' and kode_rekening in ('3901000','3902000','2500000') and tanggal_awal='$tgl_cari' and gr_neraca='PASIVA'"));
$total_pa_008=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='008' and kode_rekening in ('3901000','3902000','2500000') and tanggal_awal='$tgl_cari' and gr_neraca='PASIVA'"));
$total_pa_009=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='009' and kode_rekening in ('3901000','3902000','2500000') and tanggal_awal='$tgl_cari' and gr_neraca='PASIVA'"));
$total_pa_010=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='010' and kode_rekening in ('3901000','3902000','2500000') and tanggal_awal='$tgl_cari' and gr_neraca='PASIVA'"));
$total_pa_011=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='011' and kode_rekening in ('3901000','3902000','2500000') and tanggal_awal='$tgl_cari' and gr_neraca='PASIVA'"));
$total_pa_012=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='012' and kode_rekening in ('3901000','3902000','2500000') and tanggal_awal='$tgl_cari' and gr_neraca='PASIVA'"));
$total_pa_013=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='013' and kode_rekening in ('3901000','3902000','2500000') and tanggal_awal='$tgl_cari' and gr_neraca='PASIVA'"));
$total_pa_014=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='014' and kode_rekening in ('3901000','3902000','2500000') and tanggal_awal='$tgl_cari' and gr_neraca='PASIVA'"));
$total_pa_015=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='015' and kode_rekening in ('3901000','3902000','2500000') and tanggal_awal='$tgl_cari' and gr_neraca='PASIVA'"));
$total_pa_016=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='016' and kode_rekening in ('3901000','3902000','2500000') and tanggal_awal='$tgl_cari' and gr_neraca='PASIVA'"));


$total_pa_2001=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='2001' and kode_rekening in ('3901000','3902000','2500000') and tanggal_awal='$tgl_cari' and gr_neraca='PASIVA'"));
$total_pa_2002=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='2002' and kode_rekening in ('3901000','3902000','2500000') and tanggal_awal='$tgl_cari' and gr_neraca='PASIVA'"));
$total_pa_2003=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='2003' and kode_rekening in ('3901000','3902000','2500000') and tanggal_awal='$tgl_cari' and gr_neraca='PASIVA'"));
$total_pa_2004=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='2004' and kode_rekening in ('3901000','3902000','2500000') and tanggal_awal='$tgl_cari' and gr_neraca='PASIVA'"));
$total_pa_2005=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='2005' and kode_rekening in ('3901000','3902000','2500000') and tanggal_awal='$tgl_cari' and gr_neraca='PASIVA'"));
$total_pa_2006=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='2006' and kode_rekening in ('3901000','3902000','2500000') and tanggal_awal='$tgl_cari' and gr_neraca='PASIVA'"));
$total_pa_2007=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='2007' and kode_rekening in ('3901000','3902000','2500000') and tanggal_awal='$tgl_cari' and gr_neraca='PASIVA'"));
$total_pa_2008=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='2008' and kode_rekening in ('3901000','3902000','2500000','2611000') and tanggal_awal='$tgl_cari' and gr_neraca='PASIVA'"));
$total_pa_2009=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='2009' and kode_rekening in ('3901000','3902000','2500000') and tanggal_awal='$tgl_cari' and gr_neraca='PASIVA'"));
$total_pa_2010=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='2010' and kode_rekening in ('3901000','3902000','2500000') and tanggal_awal='$tgl_cari' and gr_neraca='PASIVA'"));
$total_pa_2011=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='2011' and kode_rekening in ('3901000','3902000','2500000') and tanggal_awal='$tgl_cari' and gr_neraca='PASIVA'"));
$total_pa_2012=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='2012' and kode_rekening in ('3901000','3902000','2500000') and tanggal_awal='$tgl_cari' and gr_neraca='PASIVA'"));
$total_pa_2013=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='2013' and kode_rekening in ('3901000','3902000','2500000') and tanggal_awal='$tgl_cari' and gr_neraca='PASIVA'"));
$total_pa_2014=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='2014' and kode_rekening in ('3901000','3902000','2500000') and tanggal_awal='$tgl_cari' and gr_neraca='PASIVA'"));
$total_pa_2015=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='2015' and kode_rekening in ('3901000','3902000','2500000') and tanggal_awal='$tgl_cari' and gr_neraca='PASIVA'"));
$total_pa_2016=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='2016' and kode_rekening in ('3901000','3902000','2500000') and tanggal_awal='$tgl_cari' and gr_neraca='PASIVA'"));
$total_pa_2017=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='2017' and kode_rekening in ('3901000','3902000','2500000') and tanggal_awal='$tgl_cari' and gr_neraca='PASIVA'"));
$total_pa_2018=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='2018' and kode_rekening in ('3901000','3902000','2500000') and tanggal_awal='$tgl_cari' and gr_neraca='PASIVA'"));
$total_pa_2019=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='2019' and kode_rekening in ('3901000','3902000','2500000') and tanggal_awal='$tgl_cari' and gr_neraca='PASIVA'"));
$total_pa_2020=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='2020' and kode_rekening in ('3901000','3902000','2500000') and tanggal_awal='$tgl_cari' and gr_neraca='PASIVA'"));
$total_pa_2021=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='2021' and kode_rekening in ('3901000','3902000','2500000') and tanggal_awal='$tgl_cari' and gr_neraca='PASIVA'"));
$total_pa_2022=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='2022' and kode_rekening in ('3901000','3902000','2500000') and tanggal_awal='$tgl_cari' and gr_neraca='PASIVA'"));
$total_pa_2023=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='2023' and kode_rekening in ('3901000','3902000','2500000') and tanggal_awal='$tgl_cari' and gr_neraca='PASIVA'"));
$total_pa_2024=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='2024' and kode_rekening in ('3901000','3902000','2500000') and tanggal_awal='$tgl_cari' and gr_neraca='PASIVA'"));

$total_pa_2025=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
					sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='2025' and kode_rekening in ('3901000','3902000','2500000') and tanggal_awal='$tgl_cari' and gr_neraca='PASIVA'"));

$total_pa_2026=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
					sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='2026' and kode_rekening in ('3901000','3902000','2500000') and tanggal_awal='$tgl_cari' and gr_neraca='PASIVA'"));

$total_pa_2027=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
					sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='2027' and kode_rekening in ('3901000','3902000','2500000') and tanggal_awal='$tgl_cari' and gr_neraca='PASIVA'"));

$total_pa_2028=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
					sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='2028' and kode_rekening in ('3901000','3902000','2500000') and tanggal_awal='$tgl_cari' and gr_neraca='PASIVA'"));
$total_pa_2029=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
					sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='2029' and kode_rekening in ('3901000','3902000','2500000') and tanggal_awal='$tgl_cari' and gr_neraca='PASIVA'"));
$total_pa_2030=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
					sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='2030' and kode_rekening in ('3901000','3902000','2500000') and tanggal_awal='$tgl_cari' and gr_neraca='PASIVA'"));
$total_pa_2031=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
					sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='2031' and kode_rekening in ('3901000','3902000','2500000') and tanggal_awal='$tgl_cari' and gr_neraca='PASIVA'"));
$total_pa_2032=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
					sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='2032' and kode_rekening in ('3901000','3902000','2500000') and tanggal_awal='$tgl_cari' and gr_neraca='PASIVA'"));
$total_pa_2033=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
					sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='2033' and kode_rekening in ('3901000','3902000','2500000') and tanggal_awal='$tgl_cari' and gr_neraca='PASIVA'"));
$total_pa_2034=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
					sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='2034' and kode_rekening in ('3901000','3902000','2500000') and tanggal_awal='$tgl_cari' and gr_neraca='PASIVA'"));
$total_pa_2035=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
					sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='2035' and kode_rekening in ('3901000','3902000','2500000') and tanggal_awal='$tgl_cari' and gr_neraca='PASIVA'"));
$total_pa_2036=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
					sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='2036' and kode_rekening in ('3901000','3902000','2500000') and tanggal_awal='$tgl_cari' and gr_neraca='PASIVA'"));
$total_pa_2037=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
					sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='2037' and kode_rekening in ('3901000','3902000','2500000') and tanggal_awal='$tgl_cari' and gr_neraca='PASIVA'"));
$total_pa_2038=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
					sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='2038' and kode_rekening in ('3901000','3902000','2500000') and tanggal_awal='$tgl_cari' and gr_neraca='PASIVA'"));
$total_pa_2039=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
					sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='2039' and kode_rekening in ('3901000','3902000','2500000') and tanggal_awal='$tgl_cari' and gr_neraca='PASIVA'"));
$total_pa_2040=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
					sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='2040' and kode_rekening in ('3901000','3902000','2500000') and tanggal_awal='$tgl_cari' and gr_neraca='PASIVA'"));
$total_pa_2041=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
					sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='2041' and kode_rekening in ('3901000','3902000','2500000') and tanggal_awal='$tgl_cari' and gr_neraca='PASIVA'"));
$total_pa_2042=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
					sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='2042' and kode_rekening in ('3901000','3902000','2500000') and tanggal_awal='$tgl_cari' and gr_neraca='PASIVA'"));
$total_pa_2043=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
					sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='2043' and kode_rekening in ('3901000','3902000','2500000') and tanggal_awal='$tgl_cari' and gr_neraca='PASIVA'"));
$total_pa_2044=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
					sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='2044' and kode_rekening in ('3901000','3902000','2500000') and tanggal_awal='$tgl_cari' and gr_neraca='PASIVA'"));


$total_pa_3000=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
					sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='3000' and kode_rekening in ('3901000','3902000','2500000','2761000','2762000','2763000') and tanggal_awal='$tgl_cari' and gr_neraca='PASIVA'"));

$total_pa_3001=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
					sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='3001' and kode_rekening in ('3901000','3902000','2500000','2761000','2762000','2763000') and tanggal_awal='$tgl_cari' and gr_neraca='PASIVA'"));

$total_pa_3002=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
					sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='3002' and kode_rekening in ('3901000','3902000','2500000','2761000','2762000','2763000') and tanggal_awal='$tgl_cari' and gr_neraca='PASIVA'"));

$total_pa_3003=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
					sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='3003' and kode_rekening in ('3901000','3902000','2500000','2761000','2762000','2763000') and tanggal_awal='$tgl_cari' and gr_neraca='PASIVA'"));
$total_pa_3004=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
					sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='3004' and kode_rekening in ('3901000','3902000','2500000','2761000','2762000','2763000') and tanggal_awal='$tgl_cari' and gr_neraca='PASIVA'"));
$total_pa_3005=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
					sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='3005' and kode_rekening in ('3901000','3902000','2500000','2761000','2762000','2763000') and tanggal_awal='$tgl_cari' and gr_neraca='PASIVA'"));



$total_pa_4000=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
					sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='4000' and kode_rekening in ('3901000','3902000','2500000','2761000','2762000','2763000') and tanggal_awal='$tgl_cari' and gr_neraca='PASIVA'"));

$total_pa_4001=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
					sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='4001' and kode_rekening in ('3901000','3902000','2500000','2761000','2762000','2763000') and tanggal_awal='$tgl_cari' and gr_neraca='PASIVA'"));

$total_pa_4002=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
					sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='4002' and kode_rekening in ('3901000','3902000','2500000','2761000','2762000','2763000') and tanggal_awal='$tgl_cari' and gr_neraca='PASIVA'"));

$total_pa_4003=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
					sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='4003' and kode_rekening in ('3901000','3902000','2500000','2761000','2762000','2763000') and tanggal_awal='$tgl_cari' and gr_neraca='PASIVA'"));

							
$total_pa_1010=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='0000' and kode_rekening in ('3901000','3902000','2500000') and tanggal_awal='$tgl_cari' and gr_neraca='PASIVA'"));
$total_pa_tot=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where gr_neraca='PASIVA' and tanggal_awal='$tgl_cari' and kode_rekening in ('3901000','3902000','2500000','2761000','2762000','2763000','2611000')"));
//total pa x 25000
$total_pax_001=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='001' and tanggal_awal='$tgl_cari' and gr_neraca='PASIVA' and LINE_BALANCE='HEADING'  "));
$total_pax_002=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='002' and tanggal_awal='$tgl_cari' and gr_neraca='PASIVA' and LINE_BALANCE='HEADING'  "));
$total_pax_003=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='003' and tanggal_awal='$tgl_cari' and gr_neraca='PASIVA' and LINE_BALANCE='HEADING'  "));
$total_pax_004=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='004' and tanggal_awal='$tgl_cari' and gr_neraca='PASIVA' and LINE_BALANCE='HEADING'  "));
$total_pax_005=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='005' and tanggal_awal='$tgl_cari' and gr_neraca='PASIVA' and LINE_BALANCE='HEADING'  "));
$total_pax_006=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='006' and tanggal_awal='$tgl_cari' and gr_neraca='PASIVA' and LINE_BALANCE='HEADING'  "));
$total_pax_007=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='007' and tanggal_awal='$tgl_cari' and gr_neraca='PASIVA' and LINE_BALANCE='HEADING'  "));
$total_pax_008=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='008' and tanggal_awal='$tgl_cari' and gr_neraca='PASIVA' and LINE_BALANCE='HEADING'  "));
$total_pax_009=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='009' and tanggal_awal='$tgl_cari' and gr_neraca='PASIVA' and LINE_BALANCE='HEADING'  "));
$total_pax_010=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='010' and tanggal_awal='$tgl_cari' and gr_neraca='PASIVA' and LINE_BALANCE='HEADING'  "));
$total_pax_011=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='011' and tanggal_awal='$tgl_cari' and gr_neraca='PASIVA' and LINE_BALANCE='HEADING'  "));
$total_pax_012=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='012' and tanggal_awal='$tgl_cari' and gr_neraca='PASIVA' and LINE_BALANCE='HEADING'  "));
$total_pax_013=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='013' and tanggal_awal='$tgl_cari' and gr_neraca='PASIVA' and LINE_BALANCE='HEADING'  "));

$total_pax_014=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='014' and tanggal_awal='$tgl_cari' and gr_neraca='PASIVA' and LINE_BALANCE='HEADING'  "));
$total_pax_015=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='015' and tanggal_awal='$tgl_cari' and gr_neraca='PASIVA' and LINE_BALANCE='HEADING'  "));
$total_pax_016=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='016' and tanggal_awal='$tgl_cari' and gr_neraca='PASIVA' and LINE_BALANCE='HEADING'  "));


$total_pax_2001=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='2001' and tanggal_awal='$tgl_cari' and gr_neraca='PASIVA' and LINE_BALANCE='HEADING'  "));
$total_pax_2002=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='2002' and tanggal_awal='$tgl_cari' and gr_neraca='PASIVA' and LINE_BALANCE='HEADING'  "));
$total_pax_2003=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='2003' and tanggal_awal='$tgl_cari' and gr_neraca='PASIVA' and LINE_BALANCE='HEADING'  "));
$total_pax_2004=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='2004' and tanggal_awal='$tgl_cari' and gr_neraca='PASIVA' and LINE_BALANCE='HEADING'  "));
$total_pax_2005=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='2005' and tanggal_awal='$tgl_cari' and gr_neraca='PASIVA' and LINE_BALANCE='HEADING'  "));
$total_pax_2006=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='2006' and tanggal_awal='$tgl_cari' and gr_neraca='PASIVA' and LINE_BALANCE='HEADING'  "));
$total_pax_2007=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='2007' and tanggal_awal='$tgl_cari' and gr_neraca='PASIVA' and LINE_BALANCE='HEADING'  "));
$total_pax_2008=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='2008' and tanggal_awal='$tgl_cari' and gr_neraca='PASIVA' and LINE_BALANCE='HEADING'  "));
$total_pax_2009=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='2009' and tanggal_awal='$tgl_cari' and gr_neraca='PASIVA' and LINE_BALANCE='HEADING'  "));
$total_pax_2010=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='2010' and tanggal_awal='$tgl_cari' and gr_neraca='PASIVA' and LINE_BALANCE='HEADING'  "));
$total_pax_2011=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='2011' and tanggal_awal='$tgl_cari' and gr_neraca='PASIVA' and LINE_BALANCE='HEADING'  "));
$total_pax_2012=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='2012' and tanggal_awal='$tgl_cari' and gr_neraca='PASIVA' and LINE_BALANCE='HEADING'  "));
$total_pax_2013=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='2013' and tanggal_awal='$tgl_cari' and gr_neraca='PASIVA' and LINE_BALANCE='HEADING'  "));
$total_pax_2014=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='2014' and tanggal_awal='$tgl_cari' and gr_neraca='PASIVA' and LINE_BALANCE='HEADING'  "));
$total_pax_2015=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='2015' and tanggal_awal='$tgl_cari' and gr_neraca='PASIVA' and LINE_BALANCE='HEADING'  "));
$total_pax_2016=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='2016' and tanggal_awal='$tgl_cari' and gr_neraca='PASIVA' and LINE_BALANCE='HEADING'  "));
$total_pax_2017=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='2017' and tanggal_awal='$tgl_cari' and gr_neraca='PASIVA' and LINE_BALANCE='HEADING'  "));
$total_pax_2018=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='2018' and tanggal_awal='$tgl_cari' and gr_neraca='PASIVA' and LINE_BALANCE='HEADING'  "));
$total_pax_2019=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='2019' and tanggal_awal='$tgl_cari' and gr_neraca='PASIVA' and LINE_BALANCE='HEADING'  "));
$total_pax_2020=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='2020' and tanggal_awal='$tgl_cari' and gr_neraca='PASIVA' and LINE_BALANCE='HEADING'  "));
$total_pax_2021=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='2021' and tanggal_awal='$tgl_cari' and gr_neraca='PASIVA' and LINE_BALANCE='HEADING'  "));
$total_pax_2022=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='2022' and tanggal_awal='$tgl_cari' and gr_neraca='PASIVA' and LINE_BALANCE='HEADING'  "));
$total_pax_2023=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='2023' and tanggal_awal='$tgl_cari' and gr_neraca='PASIVA' and LINE_BALANCE='HEADING'  "));
$total_pax_2024=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='2024' and tanggal_awal='$tgl_cari' and gr_neraca='PASIVA' and LINE_BALANCE='HEADING'  "));
$total_pax_2025=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
					sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='2025' and tanggal_awal='$tgl_cari' and gr_neraca='PASIVA' and LINE_BALANCE='HEADING'  "));

$total_pax_2026=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
					sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='2026' and tanggal_awal='$tgl_cari' and gr_neraca='PASIVA' and LINE_BALANCE='HEADING'  "));
$total_pax_2027=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
					sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='2027' and tanggal_awal='$tgl_cari' and gr_neraca='PASIVA' and LINE_BALANCE='HEADING'  "));
$total_pax_2028=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
					sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='2028' and tanggal_awal='$tgl_cari' and gr_neraca='PASIVA' and LINE_BALANCE='HEADING'  "));
$total_pax_2029=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
					sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='2029' and tanggal_awal='$tgl_cari' and gr_neraca='PASIVA' and LINE_BALANCE='HEADING'  "));
$total_pax_2030=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
					sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='2030' and tanggal_awal='$tgl_cari' and gr_neraca='PASIVA' and LINE_BALANCE='HEADING'  "));
$total_pax_2031=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
					sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='2031' and tanggal_awal='$tgl_cari' and gr_neraca='PASIVA' and LINE_BALANCE='HEADING'  "));
$total_pax_2032=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
					sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='2032' and tanggal_awal='$tgl_cari' and gr_neraca='PASIVA' and LINE_BALANCE='HEADING'  "));
$total_pax_2033=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
					sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='2033' and tanggal_awal='$tgl_cari' and gr_neraca='PASIVA' and LINE_BALANCE='HEADING'  "));
$total_pax_2034=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
					sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='2034' and tanggal_awal='$tgl_cari' and gr_neraca='PASIVA' and LINE_BALANCE='HEADING'  "));
$total_pax_2035=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
					sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='2035' and tanggal_awal='$tgl_cari' and gr_neraca='PASIVA' and LINE_BALANCE='HEADING'  "));
$total_pax_2036=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
					sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='2036' and tanggal_awal='$tgl_cari' and gr_neraca='PASIVA' and LINE_BALANCE='HEADING'  "));
$total_pax_2037=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
					sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='2037' and tanggal_awal='$tgl_cari' and gr_neraca='PASIVA' and LINE_BALANCE='HEADING'  "));
$total_pax_2038=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
					sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='2038' and tanggal_awal='$tgl_cari' and gr_neraca='PASIVA' and LINE_BALANCE='HEADING'  "));
$total_pax_2039=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
					sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='2039' and tanggal_awal='$tgl_cari' and gr_neraca='PASIVA' and LINE_BALANCE='HEADING'  "));
$total_pax_2040=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
					sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='2040' and tanggal_awal='$tgl_cari' and gr_neraca='PASIVA' and LINE_BALANCE='HEADING'  "));
$total_pax_2041=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
					sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='2041' and tanggal_awal='$tgl_cari' and gr_neraca='PASIVA' and LINE_BALANCE='HEADING'  "));
$total_pax_2042=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
					sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='2042' and tanggal_awal='$tgl_cari' and gr_neraca='PASIVA' and LINE_BALANCE='HEADING'  "));
$total_pax_2043=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
					sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='2043' and tanggal_awal='$tgl_cari' and gr_neraca='PASIVA' and LINE_BALANCE='HEADING'  "));
$total_pax_2044=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
					sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='2044' and tanggal_awal='$tgl_cari' and gr_neraca='PASIVA' and LINE_BALANCE='HEADING'  "));



							
$total_pax_1010=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='0000' and tanggal_awal='$tgl_cari' and gr_neraca='PASIVA' and LINE_BALANCE='HEADING'"));

$total_pax_3000=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='3000' and tanggal_awal='$tgl_cari' and gr_neraca='PASIVA' and LINE_BALANCE='HEADING'"));

$total_pax_3001=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='3001' and tanggal_awal='$tgl_cari' and gr_neraca='PASIVA' and LINE_BALANCE='HEADING'"));
$total_pax_3002=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='3002' and tanggal_awal='$tgl_cari' and gr_neraca='PASIVA' and LINE_BALANCE='HEADING'"));
$total_pax_3003=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='3003' and tanggal_awal='$tgl_cari' and gr_neraca='PASIVA' and LINE_BALANCE='HEADING'"));
$total_pax_3004=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='3004' and tanggal_awal='$tgl_cari' and gr_neraca='PASIVA' and LINE_BALANCE='HEADING'"));
$total_pax_3005=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='3005' and tanggal_awal='$tgl_cari' and gr_neraca='PASIVA' and LINE_BALANCE='HEADING'"));


$total_pax_4000=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='4000' and tanggal_awal='$tgl_cari' and gr_neraca='PASIVA' and LINE_BALANCE='HEADING'"));
$total_pax_4001=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='4001' and tanggal_awal='$tgl_cari' and gr_neraca='PASIVA' and LINE_BALANCE='HEADING'"));
$total_pax_4002=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='4002' and tanggal_awal='$tgl_cari' and gr_neraca='PASIVA' and LINE_BALANCE='HEADING'"));
$total_pax_4003=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where unit ='4003' and tanggal_awal='$tgl_cari' and gr_neraca='PASIVA' and LINE_BALANCE='HEADING'"));


$total_pax_tot=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where gr_neraca='PASIVA' and tanggal_awal='$tgl_cari' and LINE_BALANCE='HEADING'  "));

								
								
//query yang telah dibuat
$sql = "SELECT
kode_rekening,
nama_rekening,
LINE_BALANCE,
SUM( IF( unit = '001', saldo_akhir, 0) ) AS '001',
SUM( IF( unit = '002', saldo_akhir, 0) ) AS '002',
SUM( IF( unit = '003', saldo_akhir, 0) ) AS '003',
SUM( IF( unit = '004', saldo_akhir, 0) ) AS '004',
SUM( IF( unit = '005', saldo_akhir, 0) ) AS '005',
SUM( IF( unit = '006', saldo_akhir, 0) ) AS '006',
SUM( IF( unit = '007', saldo_akhir, 0) ) AS '007',
SUM( IF( unit = '008', saldo_akhir, 0) ) AS '008',
SUM( IF( unit = '009', saldo_akhir, 0) ) AS '009',
SUM( IF( unit = '010', saldo_akhir, 0) ) AS '010',
SUM( IF( unit = '011', saldo_akhir, 0) ) AS '011',
SUM( IF( unit = '012', saldo_akhir, 0) ) AS '012',
SUM( IF( unit = '013', saldo_akhir, 0) ) AS '013',
SUM( IF( unit = '014', saldo_akhir, 0) ) AS '014',
SUM( IF( unit = '015', saldo_akhir, 0) ) AS '015',
SUM( IF( unit = '016', saldo_akhir, 0) ) AS '016',
SUM( IF( unit = '2001', saldo_akhir, 0) ) AS '2001',
SUM( IF( unit = '2002', saldo_akhir, 0) ) AS '2002',
SUM( IF( unit = '2003', saldo_akhir, 0) ) AS '2003',
SUM( IF( unit = '2004', saldo_akhir, 0) ) AS '2004',
SUM( IF( unit = '2005', saldo_akhir, 0) ) AS '2005',
SUM( IF( unit = '2006', saldo_akhir, 0) ) AS '2006',
SUM( IF( unit = '2007', saldo_akhir, 0) ) AS '2007',
SUM( IF( unit = '2008', saldo_akhir, 0) ) AS '2008',
SUM( IF( unit = '2009', saldo_akhir, 0) ) AS '2009',
SUM( IF( unit = '2010', saldo_akhir, 0) ) AS '2010',
SUM( IF( unit = '2011', saldo_akhir, 0) ) AS '2011',
SUM( IF( unit = '2012', saldo_akhir, 0) ) AS '2012',
SUM( IF( unit = '2013', saldo_akhir, 0) ) AS '2013',
SUM( IF( unit = '2014', saldo_akhir, 0) ) AS '2014',
SUM( IF( unit = '2015', saldo_akhir, 0) ) AS '2015',
SUM( IF( unit = '2016', saldo_akhir, 0) ) AS '2016',
SUM( IF( unit = '2017', saldo_akhir, 0) ) AS '2017',
SUM( IF( unit = '2018', saldo_akhir, 0) ) AS '2018',
SUM( IF( unit = '2019', saldo_akhir, 0) ) AS '2019',
SUM( IF( unit = '2020', saldo_akhir, 0) ) AS '2020',
SUM( IF( unit = '2021', saldo_akhir, 0) ) AS '2021',
SUM( IF( unit = '2022', saldo_akhir, 0) ) AS '2022',
SUM( IF( unit = '2023', saldo_akhir, 0) ) AS '2023',
SUM( IF( unit = '2024', saldo_akhir, 0) ) AS '2024',
SUM( IF( unit = '2025', saldo_akhir, 0) ) AS '2025',
SUM( IF( unit = '2026', saldo_akhir, 0) ) AS '2026',
SUM( IF( unit = '2027', saldo_akhir, 0) ) AS '2027',
SUM( IF( unit = '2028', saldo_akhir, 0) ) AS '2028',
SUM( IF( unit = '2029', saldo_akhir, 0) ) AS '2029',
SUM( IF( unit = '2030', saldo_akhir, 0) ) AS '2030',
SUM( IF( unit = '2031', saldo_akhir, 0) ) AS '2031',
SUM( IF( unit = '2032', saldo_akhir, 0) ) AS '2032',
SUM( IF( unit = '2033', saldo_akhir, 0) ) AS '2033',
SUM( IF( unit = '2034', saldo_akhir, 0) ) AS '2034',
SUM( IF( unit = '2035', saldo_akhir, 0) ) AS '2035',
SUM( IF( unit = '2036', saldo_akhir, 0) ) AS '2036',
SUM( IF( unit = '2037', saldo_akhir, 0) ) AS '2037',
SUM( IF( unit = '2038', saldo_akhir, 0) ) AS '2038',
SUM( IF( unit = '2039', saldo_akhir, 0) ) AS '2039',
SUM( IF( unit = '2040', saldo_akhir, 0) ) AS '2040',
SUM( IF( unit = '2041', saldo_akhir, 0) ) AS '2041',
SUM( IF( unit = '2042', saldo_akhir, 0) ) AS '2042',
SUM( IF( unit = '2043', saldo_akhir, 0) ) AS '2043',
SUM( IF( unit = '2044', saldo_akhir, 0) ) AS '2044',

SUM( IF( unit = '0000', saldo_akhir, 0) ) AS '1010',
SUM( IF( unit = '3000', saldo_akhir, 0) ) AS '3000',
SUM( IF( unit = '3001', saldo_akhir, 0) ) AS '3001',
SUM( IF( unit = '3002', saldo_akhir, 0) ) AS '3002',
SUM( IF( unit = '3003', saldo_akhir, 0) ) AS '3003',
SUM( IF( unit = '3004', saldo_akhir, 0) ) AS '3004',
SUM( IF( unit = '3005', saldo_akhir, 0) ) AS '3005',

SUM( IF( unit = '4000', saldo_akhir, 0) ) AS '4000',
SUM( IF( unit = '4001', saldo_akhir, 0) ) AS '4001',
SUM( IF( unit = '4002', saldo_akhir, 0) ) AS '4002',
SUM( IF( unit = '4003', saldo_akhir, 0) ) AS '4003',

SUM(saldo_akhir) AS total_trx
FROM
".$sumber." where gr_neraca='AKTIVA' and tanggal_awal='$tgl_cari' 
group by kode_rekening";

$sql_pa = "SELECT
kode_rekening,
nama_rekening,
LINE_BALANCE,
SUM( IF( unit = '001', saldo_akhir, 0) ) AS '001',
SUM( IF( unit = '002', saldo_akhir, 0) ) AS '002',
SUM( IF( unit = '003', saldo_akhir, 0) ) AS '003',
SUM( IF( unit = '004', saldo_akhir, 0) ) AS '004',
SUM( IF( unit = '005', saldo_akhir, 0) ) AS '005',
SUM( IF( unit = '006', saldo_akhir, 0) ) AS '006',
SUM( IF( unit = '007', saldo_akhir, 0) ) AS '007',
SUM( IF( unit = '008', saldo_akhir, 0) ) AS '008',
SUM( IF( unit = '009', saldo_akhir, 0) ) AS '009',
SUM( IF( unit = '010', saldo_akhir, 0) ) AS '010',
SUM( IF( unit = '011', saldo_akhir, 0) ) AS '011',
SUM( IF( unit = '012', saldo_akhir, 0) ) AS '012',
SUM( IF( unit = '013', saldo_akhir, 0) ) AS '013',
SUM( IF( unit = '014', saldo_akhir, 0) ) AS '014',
SUM( IF( unit = '015', saldo_akhir, 0) ) AS '015',
SUM( IF( unit = '016', saldo_akhir, 0) ) AS '016',

SUM( IF( unit = '2001', saldo_akhir, 0) ) AS '2001',
SUM( IF( unit = '2002', saldo_akhir, 0) ) AS '2002',
SUM( IF( unit = '2003', saldo_akhir, 0) ) AS '2003',
SUM( IF( unit = '2004', saldo_akhir, 0) ) AS '2004',
SUM( IF( unit = '2005', saldo_akhir, 0) ) AS '2005',
SUM( IF( unit = '2006', saldo_akhir, 0) ) AS '2006',
SUM( IF( unit = '2007', saldo_akhir, 0) ) AS '2007',
SUM( IF( unit = '2008', saldo_akhir, 0) ) AS '2008',
SUM( IF( unit = '2009', saldo_akhir, 0) ) AS '2009',
SUM( IF( unit = '2010', saldo_akhir, 0) ) AS '2010',
SUM( IF( unit = '2011', saldo_akhir, 0) ) AS '2011',
SUM( IF( unit = '2012', saldo_akhir, 0) ) AS '2012',
SUM( IF( unit = '2013', saldo_akhir, 0) ) AS '2013',
SUM( IF( unit = '2014', saldo_akhir, 0) ) AS '2014',
SUM( IF( unit = '2015', saldo_akhir, 0) ) AS '2015',
SUM( IF( unit = '2016', saldo_akhir, 0) ) AS '2016',
SUM( IF( unit = '2017', saldo_akhir, 0) ) AS '2017',
SUM( IF( unit = '2018', saldo_akhir, 0) ) AS '2018',
SUM( IF( unit = '2019', saldo_akhir, 0) ) AS '2019',
SUM( IF( unit = '2020', saldo_akhir, 0) ) AS '2020',
SUM( IF( unit = '2021', saldo_akhir, 0) ) AS '2021',
SUM( IF( unit = '2022', saldo_akhir, 0) ) AS '2022',
SUM( IF( unit = '2023', saldo_akhir, 0) ) AS '2023',
SUM( IF( unit = '2024', saldo_akhir, 0) ) AS '2024',
SUM( IF( unit = '2025', saldo_akhir, 0) ) AS '2025',
SUM( IF( unit = '2026', saldo_akhir, 0) ) AS '2026',
SUM( IF( unit = '2027', saldo_akhir, 0) ) AS '2027',
SUM( IF( unit = '2028', saldo_akhir, 0) ) AS '2028',
SUM( IF( unit = '2029', saldo_akhir, 0) ) AS '2029',
SUM( IF( unit = '2030', saldo_akhir, 0) ) AS '2030',
SUM( IF( unit = '2031', saldo_akhir, 0) ) AS '2031',
SUM( IF( unit = '2032', saldo_akhir, 0) ) AS '2032',
SUM( IF( unit = '2033', saldo_akhir, 0) ) AS '2033',
SUM( IF( unit = '2034', saldo_akhir, 0) ) AS '2034',
SUM( IF( unit = '2035', saldo_akhir, 0) ) AS '2035',
SUM( IF( unit = '2036', saldo_akhir, 0) ) AS '2036',
SUM( IF( unit = '2037', saldo_akhir, 0) ) AS '2037',
SUM( IF( unit = '2038', saldo_akhir, 0) ) AS '2038',
SUM( IF( unit = '2039', saldo_akhir, 0) ) AS '2039',
SUM( IF( unit = '2040', saldo_akhir, 0) ) AS '2040',
SUM( IF( unit = '2041', saldo_akhir, 0) ) AS '2041',
SUM( IF( unit = '2042', saldo_akhir, 0) ) AS '2042',
SUM( IF( unit = '2043', saldo_akhir, 0) ) AS '2043',
SUM( IF( unit = '2044', saldo_akhir, 0) ) AS '2044',

SUM( IF( unit = '0000', saldo_akhir, 0) ) AS '1010',
SUM( IF( unit = '3000', saldo_akhir, 0) ) AS '3000',
SUM( IF( unit = '3001', saldo_akhir, 0) ) AS '3001',
SUM( IF( unit = '3002', saldo_akhir, 0) ) AS '3002',
SUM( IF( unit = '3003', saldo_akhir, 0) ) AS '3003',
SUM( IF( unit = '3004', saldo_akhir, 0) ) AS '3004',
SUM( IF( unit = '3005', saldo_akhir, 0) ) AS '3005',

SUM( IF( unit = '4000', saldo_akhir, 0) ) AS '4000',
SUM( IF( unit = '4001', saldo_akhir, 0) ) AS '4001',
SUM( IF( unit = '4002', saldo_akhir, 0) ) AS '4002',
SUM( IF( unit = '4003', saldo_akhir, 0) ) AS '4003',

SUM(saldo_akhir) AS total_trx
FROM
".$sumber." where gr_neraca='PASIVA' and tanggal_awal='$tgl_cari'
group by kode_rekening";

$sql_laba = "SELECT
kode_rekening,
nama_rekening,
LINE_BALANCE,
SUM( IF( unit = '001', saldo_akhir, 0) ) AS '001',
SUM( IF( unit = '002', saldo_akhir, 0) ) AS '002',
SUM( IF( unit = '003', saldo_akhir, 0) ) AS '003',
SUM( IF( unit = '004', saldo_akhir, 0) ) AS '004',
SUM( IF( unit = '005', saldo_akhir, 0) ) AS '005',
SUM( IF( unit = '006', saldo_akhir, 0) ) AS '006',
SUM( IF( unit = '007', saldo_akhir, 0) ) AS '007',
SUM( IF( unit = '008', saldo_akhir, 0) ) AS '008',
SUM( IF( unit = '009', saldo_akhir, 0) ) AS '009',
SUM( IF( unit = '010', saldo_akhir, 0) ) AS '010',
SUM( IF( unit = '011', saldo_akhir, 0) ) AS '011',
SUM( IF( unit = '012', saldo_akhir, 0) ) AS '012',
SUM( IF( unit = '013', saldo_akhir, 0) ) AS '013',
SUM( IF( unit = '014', saldo_akhir, 0) ) AS '014',
SUM( IF( unit = '015', saldo_akhir, 0) ) AS '015',
SUM( IF( unit = '016', saldo_akhir, 0) ) AS '016',

SUM( IF( unit = '2001', saldo_akhir, 0) ) AS '2001',
SUM( IF( unit = '2002', saldo_akhir, 0) ) AS '2002',
SUM( IF( unit = '2003', saldo_akhir, 0) ) AS '2003',
SUM( IF( unit = '2004', saldo_akhir, 0) ) AS '2004',
SUM( IF( unit = '2005', saldo_akhir, 0) ) AS '2005',
SUM( IF( unit = '2006', saldo_akhir, 0) ) AS '2006',
SUM( IF( unit = '2007', saldo_akhir, 0) ) AS '2007',
SUM( IF( unit = '2008', saldo_akhir, 0) ) AS '2008',
SUM( IF( unit = '2009', saldo_akhir, 0) ) AS '2009',
SUM( IF( unit = '2010', saldo_akhir, 0) ) AS '2010',
SUM( IF( unit = '2011', saldo_akhir, 0) ) AS '2011',
SUM( IF( unit = '2012', saldo_akhir, 0) ) AS '2012',
SUM( IF( unit = '2013', saldo_akhir, 0) ) AS '2013',
SUM( IF( unit = '2014', saldo_akhir, 0) ) AS '2014',
SUM( IF( unit = '2015', saldo_akhir, 0) ) AS '2015',
SUM( IF( unit = '2016', saldo_akhir, 0) ) AS '2016',
SUM( IF( unit = '2017', saldo_akhir, 0) ) AS '2017',
SUM( IF( unit = '2018', saldo_akhir, 0) ) AS '2018',
SUM( IF( unit = '2019', saldo_akhir, 0) ) AS '2019',
SUM( IF( unit = '2020', saldo_akhir, 0) ) AS '2020',
SUM( IF( unit = '2021', saldo_akhir, 0) ) AS '2021',
SUM( IF( unit = '2022', saldo_akhir, 0) ) AS '2022',
SUM( IF( unit = '2023', saldo_akhir, 0) ) AS '2023',
SUM( IF( unit = '2024', saldo_akhir, 0) ) AS '2024',
SUM( IF( unit = '2025', saldo_akhir, 0) ) AS '2025',
SUM( IF( unit = '2026', saldo_akhir, 0) ) AS '2026',
SUM( IF( unit = '2027', saldo_akhir, 0) ) AS '2027',
SUM( IF( unit = '2028', saldo_akhir, 0) ) AS '2028',
SUM( IF( unit = '2029', saldo_akhir, 0) ) AS '2029',
SUM( IF( unit = '2030', saldo_akhir, 0) ) AS '2030',
SUM( IF( unit = '2031', saldo_akhir, 0) ) AS '2031',
SUM( IF( unit = '2032', saldo_akhir, 0) ) AS '2032',
SUM( IF( unit = '2033', saldo_akhir, 0) ) AS '2033',
SUM( IF( unit = '2034', saldo_akhir, 0) ) AS '2034',
SUM( IF( unit = '2035', saldo_akhir, 0) ) AS '2035',
SUM( IF( unit = '2036', saldo_akhir, 0) ) AS '2036',
SUM( IF( unit = '2037', saldo_akhir, 0) ) AS '2037',
SUM( IF( unit = '2038', saldo_akhir, 0) ) AS '2038',
SUM( IF( unit = '2039', saldo_akhir, 0) ) AS '2039',
SUM( IF( unit = '2040', saldo_akhir, 0) ) AS '2040',
SUM( IF( unit = '2041', saldo_akhir, 0) ) AS '2041',
SUM( IF( unit = '2042', saldo_akhir, 0) ) AS '2042',
SUM( IF( unit = '2043', saldo_akhir, 0) ) AS '2043',
SUM( IF( unit = '2044', saldo_akhir, 0) ) AS '2044',

SUM( IF( unit = '0000', saldo_akhir, 0) ) AS '1010',
SUM( IF( unit = '3000', saldo_akhir, 0) ) AS '3000',
SUM( IF( unit = '3001', saldo_akhir, 0) ) AS '3001',
SUM( IF( unit = '3002', saldo_akhir, 0) ) AS '3002',
SUM( IF( unit = '3003', saldo_akhir, 0) ) AS '3003',
SUM( IF( unit = '3004', saldo_akhir, 0) ) AS '3004',
SUM( IF( unit = '3005', saldo_akhir, 0) ) AS '3005',

SUM( IF( unit = '4000', saldo_akhir, 0) ) AS '4000',
SUM( IF( unit = '4001', saldo_akhir, 0) ) AS '4001',
SUM( IF( unit = '4002', saldo_akhir, 0) ) AS '4002',
SUM( IF( unit = '4003', saldo_akhir, 0) ) AS '4003',

SUM(saldo_akhir) AS total_trx
FROM
".$sumber." where gr_neraca='rugi-laba' and tanggal_awal='$tgl_cari' and kode_rekening not in ('59999')
group by kode_rekening";


 $query_pa = mysql_query($sql_pa);
 
//jalankan query dan akan menghasilkan mysqli result object
$query = mysql_query($sql);

$query_laba = mysql_query($sql_laba);

$sql1 = "SELECT
branch.unit as unit from ".$sumber."
left join branch 
on branch.kode_branch = ".$sumber.".unit
 where tanggal_awal='$tgl_cari' and status_aktif='aktif' group by ".$sumber.".unit order by kode_branch";
$query1 = mysql_query($sql1);

$total_pendapan=mysql_query("select unit,
SUM( IF( unit = '001', saldo_akhir, 0) ) AS '001',
SUM( IF( unit = '002', saldo_akhir, 0) ) AS '002',
SUM( IF( unit = '003', saldo_akhir, 0) ) AS '003',
SUM( IF( unit = '004', saldo_akhir, 0) ) AS '004',
SUM( IF( unit = '005', saldo_akhir, 0) ) AS '005',
SUM( IF( unit = '006', saldo_akhir, 0) ) AS '006',
SUM( IF( unit = '007', saldo_akhir, 0) ) AS '007',
SUM( IF( unit = '008', saldo_akhir, 0) ) AS '008',
SUM( IF( unit = '009', saldo_akhir, 0) ) AS '009',
SUM( IF( unit = '010', saldo_akhir, 0) ) AS '010',
SUM( IF( unit = '011', saldo_akhir, 0) ) AS '011',
SUM( IF( unit = '012', saldo_akhir, 0) ) AS '012',
SUM( IF( unit = '013', saldo_akhir, 0) ) AS '013',
SUM( IF( unit = '014', saldo_akhir, 0) ) AS '014',
SUM( IF( unit = '015', saldo_akhir, 0) ) AS '015',
SUM( IF( unit = '016', saldo_akhir, 0) ) AS '016',

SUM( IF( unit = '2001', saldo_akhir, 0) ) AS '2001',
SUM( IF( unit = '2002', saldo_akhir, 0) ) AS '2002',
SUM( IF( unit = '2003', saldo_akhir, 0) ) AS '2003',
SUM( IF( unit = '2004', saldo_akhir, 0) ) AS '2004',
SUM( IF( unit = '2005', saldo_akhir, 0) ) AS '2005',
SUM( IF( unit = '2006', saldo_akhir, 0) ) AS '2006',
SUM( IF( unit = '2007', saldo_akhir, 0) ) AS '2007',
SUM( IF( unit = '2008', saldo_akhir, 0) ) AS '2008',
SUM( IF( unit = '2009', saldo_akhir, 0) ) AS '2009',
SUM( IF( unit = '2010', saldo_akhir, 0) ) AS '2010',
SUM( IF( unit = '2011', saldo_akhir, 0) ) AS '2011',
SUM( IF( unit = '2012', saldo_akhir, 0) ) AS '2012',
SUM( IF( unit = '2013', saldo_akhir, 0) ) AS '2013',
SUM( IF( unit = '2014', saldo_akhir, 0) ) AS '2014',
SUM( IF( unit = '2015', saldo_akhir, 0) ) AS '2015',
SUM( IF( unit = '2016', saldo_akhir, 0) ) AS '2016',
SUM( IF( unit = '2017', saldo_akhir, 0) ) AS '2017',
SUM( IF( unit = '2018', saldo_akhir, 0) ) AS '2018',
SUM( IF( unit = '2019', saldo_akhir, 0) ) AS '2019',
SUM( IF( unit = '2020', saldo_akhir, 0) ) AS '2020',
SUM( IF( unit = '2021', saldo_akhir, 0) ) AS '2021',
SUM( IF( unit = '2022', saldo_akhir, 0) ) AS '2022',
SUM( IF( unit = '2023', saldo_akhir, 0) ) AS '2023',
SUM( IF( unit = '2024', saldo_akhir, 0) ) AS '2024',
SUM( IF( unit = '2025', saldo_akhir, 0) ) AS '2025',
SUM( IF( unit = '2026', saldo_akhir, 0) ) AS '2026',
SUM( IF( unit = '2027', saldo_akhir, 0) ) AS '2027',
SUM( IF( unit = '2028', saldo_akhir, 0) ) AS '2028',
SUM( IF( unit = '2029', saldo_akhir, 0) ) AS '2029',
SUM( IF( unit = '2030', saldo_akhir, 0) ) AS '2030',
SUM( IF( unit = '2031', saldo_akhir, 0) ) AS '2031',
SUM( IF( unit = '2032', saldo_akhir, 0) ) AS '2032',
SUM( IF( unit = '2033', saldo_akhir, 0) ) AS '2033',
SUM( IF( unit = '2034', saldo_akhir, 0) ) AS '2034',
SUM( IF( unit = '2035', saldo_akhir, 0) ) AS '2035',
SUM( IF( unit = '2036', saldo_akhir, 0) ) AS '2036',
SUM( IF( unit = '2037', saldo_akhir, 0) ) AS '2037',
SUM( IF( unit = '2038', saldo_akhir, 0) ) AS '2038',
SUM( IF( unit = '2039', saldo_akhir, 0) ) AS '2039',
SUM( IF( unit = '2040', saldo_akhir, 0) ) AS '2040',
SUM( IF( unit = '2041', saldo_akhir, 0) ) AS '2041',
SUM( IF( unit = '2042', saldo_akhir, 0) ) AS '2042',
SUM( IF( unit = '2043', saldo_akhir, 0) ) AS '2043',
SUM( IF( unit = '2044', saldo_akhir, 0) ) AS '2044',

SUM( IF( unit = '0000', saldo_akhir, 0) ) AS '1010',
SUM( IF( unit = '3000', saldo_akhir, 0) ) AS '3000',
SUM( IF( unit = '3001', saldo_akhir, 0) ) AS '3001',
SUM( IF( unit = '3002', saldo_akhir, 0) ) AS '3002',
SUM( IF( unit = '3003', saldo_akhir, 0) ) AS '3003',
SUM( IF( unit = '3004', saldo_akhir, 0) ) AS '3004',
SUM( IF( unit = '3005', saldo_akhir, 0) ) AS '3005',

SUM( IF( unit = '4000', saldo_akhir, 0) ) AS '4000',
SUM( IF( unit = '4001', saldo_akhir, 0) ) AS '4001',
SUM( IF( unit = '4002', saldo_akhir, 0) ) AS '4002',
SUM( IF( unit = '4003', saldo_akhir, 0) ) AS '4003',


SUM(saldo_akhir) AS total_trx
from ".$sumber." 
where kode_rekening='40000' group by gr_head and tanggal_awal='$tgl_cari'");

$total_biaya=mysql_query("select 
SUM( IF( unit = '001', saldo_akhir, 0) ) AS '001',
SUM( IF( unit = '002', saldo_akhir, 0) ) AS '002',
SUM( IF( unit = '003', saldo_akhir, 0) ) AS '003',
SUM( IF( unit = '004', saldo_akhir, 0) ) AS '004',
SUM( IF( unit = '005', saldo_akhir, 0) ) AS '005',
SUM( IF( unit = '006', saldo_akhir, 0) ) AS '006',
SUM( IF( unit = '007', saldo_akhir, 0) ) AS '007',
SUM( IF( unit = '008', saldo_akhir, 0) ) AS '008',
SUM( IF( unit = '009', saldo_akhir, 0) ) AS '009',
SUM( IF( unit = '010', saldo_akhir, 0) ) AS '010',
SUM( IF( unit = '011', saldo_akhir, 0) ) AS '011',
SUM( IF( unit = '012', saldo_akhir, 0) ) AS '012',
SUM( IF( unit = '013', saldo_akhir, 0) ) AS '013',
SUM( IF( unit = '014', saldo_akhir, 0) ) AS '014',
SUM( IF( unit = '015', saldo_akhir, 0) ) AS '015',
SUM( IF( unit = '016', saldo_akhir, 0) ) AS '016',

SUM( IF( unit = '2001', saldo_akhir, 0) ) AS '2001',
SUM( IF( unit = '2002', saldo_akhir, 0) ) AS '2002',
SUM( IF( unit = '2003', saldo_akhir, 0) ) AS '2003',
SUM( IF( unit = '2004', saldo_akhir, 0) ) AS '2004',
SUM( IF( unit = '2005', saldo_akhir, 0) ) AS '2005',
SUM( IF( unit = '2006', saldo_akhir, 0) ) AS '2006',
SUM( IF( unit = '2007', saldo_akhir, 0) ) AS '2007',
SUM( IF( unit = '2008', saldo_akhir, 0) ) AS '2008',
SUM( IF( unit = '2009', saldo_akhir, 0) ) AS '2009',
SUM( IF( unit = '2010', saldo_akhir, 0) ) AS '2010',
SUM( IF( unit = '2011', saldo_akhir, 0) ) AS '2011',
SUM( IF( unit = '2012', saldo_akhir, 0) ) AS '2012',
SUM( IF( unit = '2013', saldo_akhir, 0) ) AS '2013',
SUM( IF( unit = '2014', saldo_akhir, 0) ) AS '2014',
SUM( IF( unit = '2015', saldo_akhir, 0) ) AS '2015',
SUM( IF( unit = '2016', saldo_akhir, 0) ) AS '2016',
SUM( IF( unit = '2017', saldo_akhir, 0) ) AS '2017',
SUM( IF( unit = '2018', saldo_akhir, 0) ) AS '2018',
SUM( IF( unit = '2019', saldo_akhir, 0) ) AS '2019',
SUM( IF( unit = '2020', saldo_akhir, 0) ) AS '2020',
SUM( IF( unit = '2021', saldo_akhir, 0) ) AS '2021',
SUM( IF( unit = '2022', saldo_akhir, 0) ) AS '2022',
SUM( IF( unit = '2023', saldo_akhir, 0) ) AS '2023',
SUM( IF( unit = '2024', saldo_akhir, 0) ) AS '2024',
SUM( IF( unit = '2025', saldo_akhir, 0) ) AS '2025',
SUM( IF( unit = '2026', saldo_akhir, 0) ) AS '2026',
SUM( IF( unit = '2027', saldo_akhir, 0) ) AS '2027',
SUM( IF( unit = '2028', saldo_akhir, 0) ) AS '2028',
SUM( IF( unit = '2029', saldo_akhir, 0) ) AS '2029',
SUM( IF( unit = '2030', saldo_akhir, 0) ) AS '2030',
SUM( IF( unit = '2031', saldo_akhir, 0) ) AS '2031',
SUM( IF( unit = '2032', saldo_akhir, 0) ) AS '2032',
SUM( IF( unit = '2033', saldo_akhir, 0) ) AS '2033',
SUM( IF( unit = '2034', saldo_akhir, 0) ) AS '2034',
SUM( IF( unit = '2035', saldo_akhir, 0) ) AS '2035',
SUM( IF( unit = '2036', saldo_akhir, 0) ) AS '2036',
SUM( IF( unit = '2037', saldo_akhir, 0) ) AS '2037',
SUM( IF( unit = '2038', saldo_akhir, 0) ) AS '2038',
SUM( IF( unit = '2039', saldo_akhir, 0) ) AS '2039',
SUM( IF( unit = '2040', saldo_akhir, 0) ) AS '2040',
SUM( IF( unit = '2041', saldo_akhir, 0) ) AS '2041',
SUM( IF( unit = '2042', saldo_akhir, 0) ) AS '2042',
SUM( IF( unit = '2043', saldo_akhir, 0) ) AS '2043',
SUM( IF( unit = '2044', saldo_akhir, 0) ) AS '2044',

SUM( IF( unit = '0000', saldo_akhir, 0) ) AS '1010',
SUM( IF( unit = '3001', saldo_akhir, 0) ) AS '3001',
SUM( IF( unit = '3000', saldo_akhir, 0) ) AS '3000',
SUM( IF( unit = '3002', saldo_akhir, 0) ) AS '3002',
SUM( IF( unit = '3003', saldo_akhir, 0) ) AS '3003',
SUM( IF( unit = '3004', saldo_akhir, 0) ) AS '3004',
SUM( IF( unit = '3005', saldo_akhir, 0) ) AS '3005',

SUM( IF( unit = '4001', saldo_akhir, 0) ) AS '4001',
SUM( IF( unit = '4000', saldo_akhir, 0) ) AS '4000',
SUM( IF( unit = '4002', saldo_akhir, 0) ) AS '4002',
SUM( IF( unit = '4003', saldo_akhir, 0) ) AS '4003',

SUM(saldo_akhir) AS total_trx
from ".$sumber." 
where kode_rekening='50000' group by gr_head and tanggal_awal='$tgl_cari'");

$total_pendapan_n=mysql_query("select unit,
SUM( IF( unit = '001', saldo_akhir, 0) ) AS '001',
SUM( IF( unit = '002', saldo_akhir, 0) ) AS '002',
SUM( IF( unit = '003', saldo_akhir, 0) ) AS '003',
SUM( IF( unit = '004', saldo_akhir, 0) ) AS '004',
SUM( IF( unit = '005', saldo_akhir, 0) ) AS '005',
SUM( IF( unit = '006', saldo_akhir, 0) ) AS '006',
SUM( IF( unit = '007', saldo_akhir, 0) ) AS '007',
SUM( IF( unit = '008', saldo_akhir, 0) ) AS '008',
SUM( IF( unit = '009', saldo_akhir, 0) ) AS '009',
SUM( IF( unit = '010', saldo_akhir, 0) ) AS '010',
SUM( IF( unit = '011', saldo_akhir, 0) ) AS '011',
SUM( IF( unit = '012', saldo_akhir, 0) ) AS '012',
SUM( IF( unit = '013', saldo_akhir, 0) ) AS '013',
SUM( IF( unit = '014', saldo_akhir, 0) ) AS '014',
SUM( IF( unit = '015', saldo_akhir, 0) ) AS '015',
SUM( IF( unit = '016', saldo_akhir, 0) ) AS '016',

SUM( IF( unit = '2001', saldo_akhir, 0) ) AS '2001',
SUM( IF( unit = '2002', saldo_akhir, 0) ) AS '2002',
SUM( IF( unit = '2003', saldo_akhir, 0) ) AS '2003',
SUM( IF( unit = '2004', saldo_akhir, 0) ) AS '2004',
SUM( IF( unit = '2005', saldo_akhir, 0) ) AS '2005',
SUM( IF( unit = '2006', saldo_akhir, 0) ) AS '2006',
SUM( IF( unit = '2007', saldo_akhir, 0) ) AS '2007',
SUM( IF( unit = '2008', saldo_akhir, 0) ) AS '2008',
SUM( IF( unit = '2009', saldo_akhir, 0) ) AS '2009',
SUM( IF( unit = '2010', saldo_akhir, 0) ) AS '2010',
SUM( IF( unit = '2011', saldo_akhir, 0) ) AS '2011',
SUM( IF( unit = '2012', saldo_akhir, 0) ) AS '2012',
SUM( IF( unit = '2013', saldo_akhir, 0) ) AS '2013',
SUM( IF( unit = '2014', saldo_akhir, 0) ) AS '2014',
SUM( IF( unit = '2015', saldo_akhir, 0) ) AS '2015',
SUM( IF( unit = '2016', saldo_akhir, 0) ) AS '2016',
SUM( IF( unit = '2017', saldo_akhir, 0) ) AS '2017',
SUM( IF( unit = '2018', saldo_akhir, 0) ) AS '2018',
SUM( IF( unit = '2019', saldo_akhir, 0) ) AS '2019',
SUM( IF( unit = '2020', saldo_akhir, 0) ) AS '2020',
SUM( IF( unit = '2021', saldo_akhir, 0) ) AS '2021',
SUM( IF( unit = '2022', saldo_akhir, 0) ) AS '2022',
SUM( IF( unit = '2023', saldo_akhir, 0) ) AS '2023',
SUM( IF( unit = '2024', saldo_akhir, 0) ) AS '2024',
SUM( IF( unit = '2025', saldo_akhir, 0) ) AS '2025',
SUM( IF( unit = '2026', saldo_akhir, 0) ) AS '2026',
SUM( IF( unit = '2027', saldo_akhir, 0) ) AS '2027',
SUM( IF( unit = '2028', saldo_akhir, 0) ) AS '2028',
SUM( IF( unit = '2029', saldo_akhir, 0) ) AS '2029',
SUM( IF( unit = '2030', saldo_akhir, 0) ) AS '2030',
SUM( IF( unit = '2031', saldo_akhir, 0) ) AS '2031',
SUM( IF( unit = '2032', saldo_akhir, 0) ) AS '2032',
SUM( IF( unit = '2033', saldo_akhir, 0) ) AS '2033',
SUM( IF( unit = '2034', saldo_akhir, 0) ) AS '2034',
SUM( IF( unit = '2035', saldo_akhir, 0) ) AS '2035',
SUM( IF( unit = '2036', saldo_akhir, 0) ) AS '2036',
SUM( IF( unit = '2037', saldo_akhir, 0) ) AS '2037',
SUM( IF( unit = '2038', saldo_akhir, 0) ) AS '2038',
SUM( IF( unit = '2039', saldo_akhir, 0) ) AS '2039',
SUM( IF( unit = '2040', saldo_akhir, 0) ) AS '2040',
SUM( IF( unit = '2041', saldo_akhir, 0) ) AS '2041',
SUM( IF( unit = '2042', saldo_akhir, 0) ) AS '2042',
SUM( IF( unit = '2043', saldo_akhir, 0) ) AS '2043',
SUM( IF( unit = '2044', saldo_akhir, 0) ) AS '2044',

SUM( IF( unit = '0000', saldo_akhir, 0) ) AS '1010',
SUM( IF( unit = '3000', saldo_akhir, 0) ) AS '3000',
SUM( IF( unit = '3001', saldo_akhir, 0) ) AS '3001',
SUM( IF( unit = '3002', saldo_akhir, 0) ) AS '3002',
SUM( IF( unit = '3003', saldo_akhir, 0) ) AS '3003',
SUM( IF( unit = '3004', saldo_akhir, 0) ) AS '3004',
SUM( IF( unit = '3005', saldo_akhir, 0) ) AS '3005',

SUM( IF( unit = '4000', saldo_akhir, 0) ) AS '4000',
SUM( IF( unit = '4001', saldo_akhir, 0) ) AS '4001',
SUM( IF( unit = '4002', saldo_akhir, 0) ) AS '4002',
SUM( IF( unit = '4003', saldo_akhir, 0) ) AS '4003',

SUM(saldo_akhir) AS total_trx
from ".$sumber." 
where kode_rekening='57000' group by gr_head and tanggal_awal='$tgl_cari'");

$total_beban=mysql_query("select 
SUM( IF( unit = '001', saldo_akhir, 0) ) AS '001',
SUM( IF( unit = '002', saldo_akhir, 0) ) AS '002',
SUM( IF( unit = '003', saldo_akhir, 0) ) AS '003',
SUM( IF( unit = '004', saldo_akhir, 0) ) AS '004',
SUM( IF( unit = '005', saldo_akhir, 0) ) AS '005',
SUM( IF( unit = '006', saldo_akhir, 0) ) AS '006',
SUM( IF( unit = '007', saldo_akhir, 0) ) AS '007',
SUM( IF( unit = '008', saldo_akhir, 0) ) AS '008',
SUM( IF( unit = '009', saldo_akhir, 0) ) AS '009',
SUM( IF( unit = '010', saldo_akhir, 0) ) AS '010',
SUM( IF( unit = '011', saldo_akhir, 0) ) AS '011',
SUM( IF( unit = '012', saldo_akhir, 0) ) AS '012',
SUM( IF( unit = '013', saldo_akhir, 0) ) AS '013',
SUM( IF( unit = '014', saldo_akhir, 0) ) AS '014',
SUM( IF( unit = '015', saldo_akhir, 0) ) AS '015',
SUM( IF( unit = '016', saldo_akhir, 0) ) AS '016',

SUM( IF( unit = '2001', saldo_akhir, 0) ) AS '2001',
SUM( IF( unit = '2002', saldo_akhir, 0) ) AS '2002',
SUM( IF( unit = '2003', saldo_akhir, 0) ) AS '2003',
SUM( IF( unit = '2004', saldo_akhir, 0) ) AS '2004',
SUM( IF( unit = '2005', saldo_akhir, 0) ) AS '2005',
SUM( IF( unit = '2006', saldo_akhir, 0) ) AS '2006',
SUM( IF( unit = '2007', saldo_akhir, 0) ) AS '2007',
SUM( IF( unit = '2008', saldo_akhir, 0) ) AS '2008',
SUM( IF( unit = '2009', saldo_akhir, 0) ) AS '2009',
SUM( IF( unit = '2010', saldo_akhir, 0) ) AS '2010',
SUM( IF( unit = '2011', saldo_akhir, 0) ) AS '2011',
SUM( IF( unit = '2012', saldo_akhir, 0) ) AS '2012',
SUM( IF( unit = '2013', saldo_akhir, 0) ) AS '2013',
SUM( IF( unit = '2014', saldo_akhir, 0) ) AS '2014',
SUM( IF( unit = '2015', saldo_akhir, 0) ) AS '2015',
SUM( IF( unit = '2016', saldo_akhir, 0) ) AS '2016',
SUM( IF( unit = '2017', saldo_akhir, 0) ) AS '2017',
SUM( IF( unit = '2018', saldo_akhir, 0) ) AS '2018',
SUM( IF( unit = '2019', saldo_akhir, 0) ) AS '2019',
SUM( IF( unit = '2020', saldo_akhir, 0) ) AS '2020',
SUM( IF( unit = '2021', saldo_akhir, 0) ) AS '2021',
SUM( IF( unit = '2022', saldo_akhir, 0) ) AS '2022',
SUM( IF( unit = '2023', saldo_akhir, 0) ) AS '2023',
SUM( IF( unit = '2024', saldo_akhir, 0) ) AS '2024',
SUM( IF( unit = '2025', saldo_akhir, 0) ) AS '2025',
SUM( IF( unit = '2026', saldo_akhir, 0) ) AS '2026',
SUM( IF( unit = '2027', saldo_akhir, 0) ) AS '2027',
SUM( IF( unit = '2028', saldo_akhir, 0) ) AS '2028',
SUM( IF( unit = '2029', saldo_akhir, 0) ) AS '2029',
SUM( IF( unit = '2030', saldo_akhir, 0) ) AS '2030',
SUM( IF( unit = '2031', saldo_akhir, 0) ) AS '2031',
SUM( IF( unit = '2032', saldo_akhir, 0) ) AS '2032',
SUM( IF( unit = '2033', saldo_akhir, 0) ) AS '2033',
SUM( IF( unit = '2034', saldo_akhir, 0) ) AS '2034',
SUM( IF( unit = '2035', saldo_akhir, 0) ) AS '2035',
SUM( IF( unit = '2036', saldo_akhir, 0) ) AS '2036',
SUM( IF( unit = '2037', saldo_akhir, 0) ) AS '2037',
SUM( IF( unit = '2038', saldo_akhir, 0) ) AS '2038',
SUM( IF( unit = '2039', saldo_akhir, 0) ) AS '2039',
SUM( IF( unit = '2040', saldo_akhir, 0) ) AS '2040',
SUM( IF( unit = '2041', saldo_akhir, 0) ) AS '2041',
SUM( IF( unit = '2042', saldo_akhir, 0) ) AS '2042',
SUM( IF( unit = '2043', saldo_akhir, 0) ) AS '2043',
SUM( IF( unit = '2044', saldo_akhir, 0) ) AS '2044',

SUM( IF( unit = '0000', saldo_akhir, 0) ) AS '1010',
SUM( IF( unit = '3000', saldo_akhir, 0) ) AS '3000',
SUM( IF( unit = '3001', saldo_akhir, 0) ) AS '3001',
SUM( IF( unit = '3002', saldo_akhir, 0) ) AS '3002',
SUM( IF( unit = '3003', saldo_akhir, 0) ) AS '3003',
SUM( IF( unit = '3004', saldo_akhir, 0) ) AS '3004',
SUM( IF( unit = '3005', saldo_akhir, 0) ) AS '3005',

SUM( IF( unit = '4000', saldo_akhir, 0) ) AS '4000',
SUM( IF( unit = '4001', saldo_akhir, 0) ) AS '4001',
SUM( IF( unit = '4002', saldo_akhir, 0) ) AS '4002',
SUM( IF( unit = '4003', saldo_akhir, 0) ) AS '4003',

SUM(saldo_akhir) AS total_trx
from ".$sumber." 
where kode_rekening='58000' group by gr_head and tanggal_awal='$tgl_cari'");





 //check apakah object query tercipta
if($query)
{
    //buat table
    echo '<table class="grey">';
	
    echo '<thead>';
    echo '<tr>';
	echo '<th>TYPE COA</th>';
    echo '<th>COA</th>';
    echo '<th>DESKRIPSI</th>';
	 while($row2=mysql_fetch_array($query1)){
    echo '<th>'.$row2['unit'].'</th>';
	 }
    echo '<th>KONSOLIDASI</th>';
    echo '</tr>';
    echo '</thead>';
 
    echo '<tbody>';
    //ambil data per row
		// jika ada total atau sub total

   while($row=mysql_fetch_array($query))
    
	{
			if ($row['LINE_BALANCE'] == 'HEADING') {
		$class = ' class="subtotal"';
	} else{
		$class = ' class="subtotall"';
	}	
        echo '<tr '.$class.' >';
		echo '<td>'.$row['LINE_BALANCE'].'</td>';
        echo '<td>'.$row['kode_rekening'].'</td>';
        echo '<td align="Left">'.$row['nama_rekening'].'</td>';
        echo '<td align="right">'.number_format(compat_num($row['1010']),0, ',', '.').'</td>';
		echo '<td align="right">'.number_format(compat_num($row['001']),0, ',', '.').'</td>';
		echo '<td align="right">'.number_format(compat_num($row['002']),0, ',', '.').'</td>';
		echo '<td align="right">'.number_format(compat_num($row['003']),0, ',', '.').'</td>';
		echo '<td align="right">'.number_format(compat_num($row['004']),0, ',', '.').'</td>';
		echo '<td align="right">'.number_format(compat_num($row['005']),0, ',', '.').'</td>';
		echo '<td align="right">'.number_format(compat_num($row['006']),0, ',', '.').'</td>';
		echo '<td align="right">'.number_format(compat_num($row['007']),0, ',', '.').'</td>';
		echo '<td align="right">'.number_format(compat_num($row['008']),0, ',', '.').'</td>';
		echo '<td align="right">'.number_format(compat_num($row['009']),0, ',', '.').'</td>';
		echo '<td align="right">'.number_format(compat_num($row['010']),0, ',', '.').'</td>';
		echo '<td align="right">'.number_format(compat_num($row['011']),0, ',', '.').'</td>';
echo '<td align="right">'.number_format(compat_num($row['012']),0, ',', '.').'</td>';
echo '<td align="right">'.number_format(compat_num($row['013']),0, ',', '.').'</td>';
echo '<td align="right">'.number_format(compat_num($row['014']),0, ',', '.').'</td>';
echo '<td align="right">'.number_format(compat_num($row['015']),0, ',', '.').'</td>';
echo '<td align="right">'.number_format(compat_num($row['016']),0, ',', '.').'</td>';
		echo '<td align="right">'.number_format(compat_num($row['2001']),0, ',', '.').'</td>';
		echo '<td align="right">'.number_format(compat_num($row['2002']),0, ',', '.').'</td>';
		echo '<td align="right">'.number_format(compat_num($row['2003']),0, ',', '.').'</td>';
		echo '<td align="right">'.number_format(compat_num($row['2004']),0, ',', '.').'</td>';
		echo '<td align="right">'.number_format(compat_num($row['2005']),0, ',', '.').'</td>';
		echo '<td align="right">'.number_format(compat_num($row['2006']),0, ',', '.').'</td>';
		echo '<td align="right">'.number_format(compat_num($row['2007']),0, ',', '.').'</td>';
		echo '<td align="right">'.number_format(compat_num($row['2008']),0, ',', '.').'</td>';
		echo '<td align="right">'.number_format(compat_num($row['2009']),0, ',', '.').'</td>';
		echo '<td align="right">'.number_format(compat_num($row['2010']),0, ',', '.').'</td>';
		echo '<td align="right">'.number_format(compat_num($row['2011']),0, ',', '.').'</td>';
		echo '<td align="right">'.number_format(compat_num($row['2012']),0, ',', '.').'</td>';
		echo '<td align="right">'.number_format(compat_num($row['2013']),0, ',', '.').'</td>';
		echo '<td align="right">'.number_format(compat_num($row['2014']),0, ',', '.').'</td>';
		echo '<td align="right">'.number_format(compat_num($row['2015']),0, ',', '.').'</td>';
		echo '<td align="right">'.number_format(compat_num($row['2016']),0, ',', '.').'</td>';
		echo '<td align="right">'.number_format(compat_num($row['2017']),0, ',', '.').'</td>';
		echo '<td align="right">'.number_format(compat_num($row['2018']),0, ',', '.').'</td>';
		echo '<td align="right">'.number_format(compat_num($row['2019']),0, ',', '.').'</td>';
		echo '<td align="right">'.number_format(compat_num($row['2020']),0, ',', '.').'</td>';
		echo '<td align="right">'.number_format(compat_num($row['2021']),0, ',', '.').'</td>';
		echo '<td align="right">'.number_format(compat_num($row['2022']),0, ',', '.').'</td>';
		echo '<td align="right">'.number_format(compat_num($row['2023']),0, ',', '.').'</td>';
		echo '<td align="right">'.number_format(compat_num($row['2024']),0, ',', '.').'</td>';
		echo '<td align="right">'.number_format(compat_num($row['2025']),0, ',', '.').'</td>';
echo '<td align="right">'.number_format(compat_num($row['2026']),0, ',', '.').'</td>';
echo '<td align="right">'.number_format(compat_num($row['2027']),0, ',', '.').'</td>';
echo '<td align="right">'.number_format(compat_num($row['2028']),0, ',', '.').'</td>';
echo '<td align="right">'.number_format(compat_num($row['2029']),0, ',', '.').'</td>';
echo '<td align="right">'.number_format(compat_num($row['2030']),0, ',', '.').'</td>';
echo '<td align="right">'.number_format(compat_num($row['2031']),0, ',', '.').'</td>';
echo '<td align="right">'.number_format(compat_num($row['2032']),0, ',', '.').'</td>';
echo '<td align="right">'.number_format(compat_num($row['2033']),0, ',', '.').'</td>';
echo '<td align="right">'.number_format(compat_num($row['2034']),0, ',', '.').'</td>';
echo '<td align="right">'.number_format(compat_num($row['2035']),0, ',', '.').'</td>';
echo '<td align="right">'.number_format(compat_num($row['2036']),0, ',', '.').'</td>';
echo '<td align="right">'.number_format(compat_num($row['2037']),0, ',', '.').'</td>';
echo '<td align="right">'.number_format(compat_num($row['2038']),0, ',', '.').'</td>';
echo '<td align="right">'.number_format(compat_num($row['2039']),0, ',', '.').'</td>';
echo '<td align="right">'.number_format(compat_num($row['2040']),0, ',', '.').'</td>';
echo '<td align="right">'.number_format(compat_num($row['2041']),0, ',', '.').'</td>';
echo '<td align="right">'.number_format(compat_num($row['2042']),0, ',', '.').'</td>';
echo '<td align="right">'.number_format(compat_num($row['2043']),0, ',', '.').'</td>';
echo '<td align="right">'.number_format(compat_num($row['2044']),0, ',', '.').'</td>';

echo '<td align="right">'.number_format(compat_num($row['3000']),0, ',', '.').'</td>';
		echo '<td align="right">'.number_format(compat_num($row['3001']),0, ',', '.').'</td>';
		echo '<td align="right">'.number_format(compat_num($row['3002']),0, ',', '.').'</td>';
		echo '<td align="right">'.number_format(compat_num($row['3003']),0, ',', '.').'</td>';
echo '<td align="right">'.number_format(compat_num($row['3004']),0, ',', '.').'</td>';
echo '<td align="right">'.number_format(compat_num($row['3005']),0, ',', '.').'</td>';

echo '<td align="right">'.number_format(compat_num($row['4000']),0, ',', '.').'</td>';
		echo '<td align="right">'.number_format(compat_num($row['4001']),0, ',', '.').'</td>';
		echo '<td align="right">'.number_format(compat_num($row['4002']),0, ',', '.').'</td>';
		echo '<td align="right">'.number_format(compat_num($row['4003']),0, ',', '.').'</td>';

		echo '<td align="right">'.number_format(compat_num($row['total_trx']),0, ',', '.').'</td>';
        echo '</tr>';
    }
	echo '<tr class="grey">
		<td colspan="3"><b>TOTAL AKTIVA</td>';
		
		echo '<td align="right"><B>'.number_format(compat_num($total_ak_1010['tot_saldo_akhir']),0, ',', '.').'</td></B>';
        echo '<td align="right"><B>'.number_format(compat_num($total_ak_001['tot_saldo_akhir']),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($total_ak_002['tot_saldo_akhir']),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($total_ak_003['tot_saldo_akhir']),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($total_ak_004['tot_saldo_akhir']),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($total_ak_005['tot_saldo_akhir']),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($total_ak_006['tot_saldo_akhir']),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($total_ak_007['tot_saldo_akhir']),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($total_ak_008['tot_saldo_akhir']),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($total_ak_009['tot_saldo_akhir']),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($total_ak_010['tot_saldo_akhir']),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($total_ak_011['tot_saldo_akhir']),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($total_ak_012['tot_saldo_akhir']),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($total_ak_013['tot_saldo_akhir']),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($total_ak_014['tot_saldo_akhir']),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($total_ak_015['tot_saldo_akhir']),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($total_ak_016['tot_saldo_akhir']),0, ',', '.').'</td></B>';

		echo '<td align="right"><B>'.number_format(compat_num($total_ak_2001['tot_saldo_akhir']),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($total_ak_2002['tot_saldo_akhir']),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($total_ak_2003['tot_saldo_akhir']),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($total_ak_2004['tot_saldo_akhir']),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($total_ak_2005['tot_saldo_akhir']),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($total_ak_2006['tot_saldo_akhir']),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($total_ak_2007['tot_saldo_akhir']),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($total_ak_2008['tot_saldo_akhir']),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($total_ak_2009['tot_saldo_akhir']),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($total_ak_2010['tot_saldo_akhir']),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($total_ak_2011['tot_saldo_akhir']),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($total_ak_2012['tot_saldo_akhir']),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($total_ak_2013['tot_saldo_akhir']),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($total_ak_2014['tot_saldo_akhir']),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($total_ak_2015['tot_saldo_akhir']),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($total_ak_2016['tot_saldo_akhir']),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($total_ak_2017['tot_saldo_akhir']),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($total_ak_2018['tot_saldo_akhir']),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($total_ak_2019['tot_saldo_akhir']),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($total_ak_2020['tot_saldo_akhir']),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($total_ak_2021['tot_saldo_akhir']),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($total_ak_2022['tot_saldo_akhir']),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($total_ak_2023['tot_saldo_akhir']),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($total_ak_2024['tot_saldo_akhir']),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($total_ak_2025['tot_saldo_akhir']),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($total_ak_2026['tot_saldo_akhir']),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($total_ak_2027['tot_saldo_akhir']),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($total_ak_2028['tot_saldo_akhir']),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($total_ak_2029['tot_saldo_akhir']),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($total_ak_2030['tot_saldo_akhir']),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($total_ak_2031['tot_saldo_akhir']),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($total_ak_2032['tot_saldo_akhir']),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($total_ak_2033['tot_saldo_akhir']),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($total_ak_2034['tot_saldo_akhir']),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($total_ak_2035['tot_saldo_akhir']),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($total_ak_2036['tot_saldo_akhir']),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($total_ak_2037['tot_saldo_akhir']),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($total_ak_2038['tot_saldo_akhir']),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($total_ak_2039['tot_saldo_akhir']),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($total_ak_2040['tot_saldo_akhir']),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($total_ak_2041['tot_saldo_akhir']),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($total_ak_2042['tot_saldo_akhir']),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($total_ak_2043['tot_saldo_akhir']),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($total_ak_2044['tot_saldo_akhir']),0, ',', '.').'</td></B>';

echo '<td align="right"><B>'.number_format(compat_num($total_ak_3000['tot_saldo_akhir']),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($total_ak_3001['tot_saldo_akhir']),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($total_ak_3002['tot_saldo_akhir']),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($total_ak_3003['tot_saldo_akhir']),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($total_ak_3004['tot_saldo_akhir']),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($total_ak_3005['tot_saldo_akhir']),0, ',', '.').'</td></B>';

echo '<td align="right"><B>'.number_format(compat_num($total_ak_4000['tot_saldo_akhir']),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($total_ak_4001['tot_saldo_akhir']),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($total_ak_4002['tot_saldo_akhir']),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($total_ak_4003['tot_saldo_akhir']),0, ',', '.').'</td></B>';

		echo '<td align="right"><B>'.number_format(compat_num($total_ak_tot['tot_saldo_akhir']),0, ',', '.').'</td></B>';
        echo '</tr>';
	
	echo '<tr class="grey">
		<td colspan="3"><b></td>';
		
		
        echo '<td align="right">'.$t.'</td>';
		echo '<td align="right">'.$t.'</td>';
		echo '<td align="right">'.$t.'</td>';
		echo '<td align="right">'.$t.'</td>';
		echo '<td align="right">'.$t.'</td>';
		echo '<td align="right">'.$t.'</td>';
		echo '<td align="right">'.$t.'</td>';
		echo '<td align="right">'.$t.'</td>';
		echo '<td align="right">'.$t.'</td>';
		echo '<td align="right">'.$t.'</td>';
		
		echo '<td align="right">'.$t.'</td>';
		echo '<td align="right">'.$t.'</td>';
		echo '<td align="right">'.$t.'</td>';
		echo '<td align="right">'.$t.'</td>';
		echo '<td align="right">'.$t.'</td>';
		echo '<td align="right">'.$t.'</td>';
		echo '<td align="right">'.$t.'</td>';
		echo '<td align="right">'.$t.'</td>';
		echo '<td align="right">'.$t.'</td>';
		echo '<td align="right">'.$t.'</td>';
		
		
		echo '<td align="right">'.$t.'</td>';
		echo '<td align="right">'.$t.'</td>';
		echo '<td align="right">'.$t.'</td>';
		echo '<td align="right">'.$t.'</td>';
		echo '<td align="right">'.$t.'</td>';
		echo '<td align="right">'.$t.'</td>';
		echo '<td align="right">'.$t.'</td>';
		echo '<td align="right">'.$t.'</td>';
		echo '<td align="right">'.$t.'</td>';
		echo '<td align="right">'.$t.'</td>';
		
		echo '<td align="right">'.$t.'</td>';
		echo '<td align="right">'.$t.'</td>';
		echo '<td align="right">'.$t.'</td>';
		echo '<td align="right">'.$t.'</td>';
		echo '<td align="right">'.$t.'</td>';
		
		echo '<td align="right">'.$t.'</td>';
		echo '<td align="right">'.$t.'</td>';
		echo '<td align="right">'.$t.'</td>';
		echo '<td align="right">'.$t.'</td>';
		echo '<td align="right">'.$t.'</td>';

		echo '<td align="right">'.$t.'</td>';
		echo '<td align="right">'.$t.'</td>';
		echo '<td align="right">'.$t.'</td>';

		echo '<td align="right">'.$t.'</td>';
		echo '<td align="right">'.$t.'</td>';
		echo '<td align="right">'.$t.'</td>';
echo '<td align="right">'.$t.'</td>';
		echo '<td align="right">'.$t.'</td>';
echo '<td align="right">'.$t.'</td>';
        echo '<td align="right">'.$t.'</td>';
echo '<td align="right">'.$t.'</td>';
echo '<td align="right">'.$t.'</td>';
echo '<td align="right">'.$t.'</td>';
echo '<td align="right">'.$t.'</td>';
echo '<td align="right">'.$t.'</td>';
echo '<td align="right">'.$t.'</td>';
echo '<td align="right">'.$t.'</td>';
echo '<td align="right">'.$t.'</td>';

echo '<td align="right">'.$t.'</td>';
echo '<td align="right">'.$t.'</td>';
echo '<td align="right">'.$t.'</td>';
echo '<td align="right">'.$t.'</td>';
echo '<td align="right">'.$t.'</td>';		

echo '<td align="right">'.$t.'</td>';
echo '<td align="right">'.$t.'</td>';
echo '<td align="right">'.$t.'</td>';
echo '<td align="right">'.$t.'</td>';
echo '<td align="right">'.$t.'</td>';		
echo '<td align="right">'.$t.'</td>';
echo '<td align="right">'.$t.'</td>';
echo '<td align="right">'.$t.'</td>';
echo '<td align="right">'.$t.'</td>';		

		
        echo '</tr>';

	
while($row=mysql_fetch_array($query_pa))
    
	{
			if ($row['LINE_BALANCE'] == 'HEADING') {
		$class = ' class="subtotal"';
	} else{
		$class = ' class="subtotall"';
	}	
        echo '<tr '.$class.' >';
		echo '<td>'.$row['LINE_BALANCE'].'</td>';
        echo '<td>'.$row['kode_rekening'].'</td>';
        echo '<td align="Left">'.$row['nama_rekening'].'</td>';
        echo '<td align="right">'.number_format(compat_num($row['1010']),0, ',', '.').'</td></B>';
		echo '<td align="right">'.number_format(compat_num($row['001']),0, ',', '.').'</td></B>';
		echo '<td align="right">'.number_format(compat_num($row['002']),0, ',', '.').'</td></B>';
		echo '<td align="right">'.number_format(compat_num($row['003']),0, ',', '.').'</td></B>';
		echo '<td align="right">'.number_format(compat_num($row['004']),0, ',', '.').'</td></B>';
		echo '<td align="right">'.number_format(compat_num($row['005']),0, ',', '.').'</td></B>';
		echo '<td align="right">'.number_format(compat_num($row['006']),0, ',', '.').'</td></B>';
		echo '<td align="right">'.number_format(compat_num($row['007']),0, ',', '.').'</td></B>';
		echo '<td align="right">'.number_format(compat_num($row['008']),0, ',', '.').'</td></B>';
		echo '<td align="right">'.number_format(compat_num($row['009']),0, ',', '.').'</td></B>';
		echo '<td align="right">'.number_format(compat_num($row['010']),0, ',', '.').'</td></B>';
echo '<td align="right">'.number_format(compat_num($row['011']),0, ',', '.').'</td></B>';
echo '<td align="right">'.number_format(compat_num($row['012']),0, ',', '.').'</td></B>';
echo '<td align="right">'.number_format(compat_num($row['013']),0, ',', '.').'</td></B>';
echo '<td align="right">'.number_format(compat_num($row['014']),0, ',', '.').'</td></B>';
echo '<td align="right">'.number_format(compat_num($row['015']),0, ',', '.').'</td></B>';
echo '<td align="right">'.number_format(compat_num($row['016']),0, ',', '.').'</td></B>';
		echo '<td align="right">'.number_format(compat_num($row['2001']),0, ',', '.').'</td></B>';
		echo '<td align="right">'.number_format(compat_num($row['2002']),0, ',', '.').'</td></B>';
		echo '<td align="right">'.number_format(compat_num($row['2003']),0, ',', '.').'</td></B>';
		echo '<td align="right">'.number_format(compat_num($row['2004']),0, ',', '.').'</td></B>';
		echo '<td align="right">'.number_format(compat_num($row['2005']),0, ',', '.').'</td></B>';
		echo '<td align="right">'.number_format(compat_num($row['2006']),0, ',', '.').'</td></B>';
		echo '<td align="right">'.number_format(compat_num($row['2007']),0, ',', '.').'</td></B>';
		echo '<td align="right">'.number_format(compat_num($row['2008']),0, ',', '.').'</td></B>';
		echo '<td align="right">'.number_format(compat_num($row['2009']),0, ',', '.').'</td></B>';
		echo '<td align="right">'.number_format(compat_num($row['2010']),0, ',', '.').'</td></B>';
		echo '<td align="right">'.number_format(compat_num($row['2011']),0, ',', '.').'</td></B>';
		echo '<td align="right">'.number_format(compat_num($row['2012']),0, ',', '.').'</td></B>';
		echo '<td align="right">'.number_format(compat_num($row['2013']),0, ',', '.').'</td></B>';
		echo '<td align="right">'.number_format(compat_num($row['2014']),0, ',', '.').'</td></B>';
		echo '<td align="right">'.number_format(compat_num($row['2015']),0, ',', '.').'</td></B>';
		echo '<td align="right">'.number_format(compat_num($row['2016']),0, ',', '.').'</td></B>';
		echo '<td align="right">'.number_format(compat_num($row['2017']),0, ',', '.').'</td></B>';
		echo '<td align="right">'.number_format(compat_num($row['2018']),0, ',', '.').'</td></B>';
		echo '<td align="right">'.number_format(compat_num($row['2019']),0, ',', '.').'</td></B>';
		echo '<td align="right">'.number_format(compat_num($row['2020']),0, ',', '.').'</td></B>';
		echo '<td align="right">'.number_format(compat_num($row['2021']),0, ',', '.').'</td></B>';
		echo '<td align="right">'.number_format(compat_num($row['2022']),0, ',', '.').'</td></B>';
		echo '<td align="right">'.number_format(compat_num($row['2023']),0, ',', '.').'</td></B>';
		echo '<td align="right">'.number_format(compat_num($row['2024']),0, ',', '.').'</td></B>';
		echo '<td align="right">'.number_format(compat_num($row['2025']),0, ',', '.').'</td></B>';
echo '<td align="right">'.number_format(compat_num($row['2026']),0, ',', '.').'</td></B>';
echo '<td align="right">'.number_format(compat_num($row['2027']),0, ',', '.').'</td></B>';
echo '<td align="right">'.number_format(compat_num($row['2028']),0, ',', '.').'</td></B>';
echo '<td align="right">'.number_format(compat_num($row['2029']),0, ',', '.').'</td></B>';
echo '<td align="right">'.number_format(compat_num($row['2030']),0, ',', '.').'</td></B>';
echo '<td align="right">'.number_format(compat_num($row['2031']),0, ',', '.').'</td></B>';
echo '<td align="right">'.number_format(compat_num($row['2032']),0, ',', '.').'</td></B>';
echo '<td align="right">'.number_format(compat_num($row['2033']),0, ',', '.').'</td></B>';
echo '<td align="right">'.number_format(compat_num($row['2034']),0, ',', '.').'</td></B>';
echo '<td align="right">'.number_format(compat_num($row['2035']),0, ',', '.').'</td></B>';
echo '<td align="right">'.number_format(compat_num($row['2036']),0, ',', '.').'</td></B>';
echo '<td align="right">'.number_format(compat_num($row['2037']),0, ',', '.').'</td></B>';
echo '<td align="right">'.number_format(compat_num($row['2038']),0, ',', '.').'</td></B>';
echo '<td align="right">'.number_format(compat_num($row['2039']),0, ',', '.').'</td></B>';
echo '<td align="right">'.number_format(compat_num($row['2040']),0, ',', '.').'</td></B>';
echo '<td align="right">'.number_format(compat_num($row['2041']),0, ',', '.').'</td></B>';
echo '<td align="right">'.number_format(compat_num($row['2042']),0, ',', '.').'</td></B>';
echo '<td align="right">'.number_format(compat_num($row['2043']),0, ',', '.').'</td></B>';
echo '<td align="right">'.number_format(compat_num($row['2044']),0, ',', '.').'</td></B>';

echo '<td align="right">'.number_format(compat_num($row['3000']),0, ',', '.').'</td></B>';
echo '<td align="right">'.number_format(compat_num($row['3001']),0, ',', '.').'</td></B>';
echo '<td align="right">'.number_format(compat_num($row['3002']),0, ',', '.').'</td></B>';
echo '<td align="right">'.number_format(compat_num($row['3003']),0, ',', '.').'</td></B>';
echo '<td align="right">'.number_format(compat_num($row['3004']),0, ',', '.').'</td></B>';
echo '<td align="right">'.number_format(compat_num($row['3005']),0, ',', '.').'</td></B>';

echo '<td align="right">'.number_format(compat_num($row['4000']),0, ',', '.').'</td></B>';
echo '<td align="right">'.number_format(compat_num($row['4001']),0, ',', '.').'</td></B>';
echo '<td align="right">'.number_format(compat_num($row['4002']),0, ',', '.').'</td></B>';
echo '<td align="right">'.number_format(compat_num($row['4003']),0, ',', '.').'</td></B>';

		echo '<td align="right">'.number_format(compat_num($row['total_trx']),0, ',', '.').'</td></B>';
        echo '</tr>';
    }
	echo '<tr class="grey">
		<td colspan="3"><b>TOTAL PASIVA</td>';
		
		echo '<td align="right"><B>'.number_format(compat_num($total_pa_1010['tot_saldo_akhir'] + $total_pax_1010['tot_saldo_akhir']),0, ',', '.').'</td></B>';
        echo '<td align="right"><B>'.number_format(compat_num($total_pa_001['tot_saldo_akhir'] + $total_pax_001['tot_saldo_akhir']),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($total_pa_002['tot_saldo_akhir'] + $total_pax_002['tot_saldo_akhir']),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($total_pa_003['tot_saldo_akhir'] + $total_pax_003['tot_saldo_akhir']),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($total_pa_004['tot_saldo_akhir'] + $total_pax_004['tot_saldo_akhir']),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($total_pa_005['tot_saldo_akhir'] + $total_pax_005['tot_saldo_akhir']),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($total_pa_006['tot_saldo_akhir'] + $total_pax_006['tot_saldo_akhir']),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($total_pa_007['tot_saldo_akhir'] + $total_pax_007['tot_saldo_akhir']),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($total_pa_008['tot_saldo_akhir'] + $total_pax_008['tot_saldo_akhir']),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($total_pa_009['tot_saldo_akhir'] + $total_pax_009['tot_saldo_akhir']),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($total_pa_010['tot_saldo_akhir'] + $total_pax_010['tot_saldo_akhir']),0, ',', '.').'</td></B>';

echo '<td align="right"><B>'.number_format(compat_num($total_pa_011['tot_saldo_akhir'] + $total_pax_011['tot_saldo_akhir']),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($total_pa_012['tot_saldo_akhir'] + $total_pax_012['tot_saldo_akhir']),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($total_pa_013['tot_saldo_akhir'] + $total_pax_013['tot_saldo_akhir']),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($total_pa_014['tot_saldo_akhir'] + $total_pax_014['tot_saldo_akhir']),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($total_pa_015['tot_saldo_akhir'] + $total_pax_015['tot_saldo_akhir']),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($total_pa_016['tot_saldo_akhir'] + $total_pax_016['tot_saldo_akhir']),0, ',', '.').'</td></B>';

		echo '<td align="right"><B>'.number_format(compat_num($total_pa_2001['tot_saldo_akhir'] + $total_pax_2001['tot_saldo_akhir']),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($total_pa_2002['tot_saldo_akhir'] + $total_pax_2002['tot_saldo_akhir']),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($total_pa_2003['tot_saldo_akhir'] + $total_pax_2003['tot_saldo_akhir']),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($total_pa_2004['tot_saldo_akhir'] + $total_pax_2004['tot_saldo_akhir']),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($total_pa_2005['tot_saldo_akhir'] + $total_pax_2005['tot_saldo_akhir']),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($total_pa_2006['tot_saldo_akhir'] + $total_pax_2006['tot_saldo_akhir']),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($total_pa_2007['tot_saldo_akhir'] + $total_pax_2007['tot_saldo_akhir']),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($total_pa_2008['tot_saldo_akhir'] + $total_pax_2008['tot_saldo_akhir']),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($total_pa_2009['tot_saldo_akhir'] + $total_pax_2009['tot_saldo_akhir']),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($total_pa_2010['tot_saldo_akhir'] + $total_pax_2010['tot_saldo_akhir']),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($total_pa_2011['tot_saldo_akhir'] + $total_pax_2011['tot_saldo_akhir']),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($total_pa_2012['tot_saldo_akhir'] + $total_pax_2012['tot_saldo_akhir']),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($total_pa_2013['tot_saldo_akhir'] + $total_pax_2013['tot_saldo_akhir']),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($total_pa_2014['tot_saldo_akhir'] + $total_pax_2014['tot_saldo_akhir']),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($total_pa_2015['tot_saldo_akhir'] + $total_pax_2015['tot_saldo_akhir']),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($total_pa_2016['tot_saldo_akhir'] + $total_pax_2016['tot_saldo_akhir']),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($total_pa_2017['tot_saldo_akhir'] + $total_pax_2017['tot_saldo_akhir']),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($total_pa_2018['tot_saldo_akhir'] + $total_pax_2018['tot_saldo_akhir']),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($total_pa_2019['tot_saldo_akhir'] + $total_pax_2019['tot_saldo_akhir']),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($total_pa_2020['tot_saldo_akhir'] + $total_pax_2020['tot_saldo_akhir']),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($total_pa_2021['tot_saldo_akhir'] + $total_pax_2021['tot_saldo_akhir']),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($total_pa_2022['tot_saldo_akhir'] + $total_pax_2022['tot_saldo_akhir']),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($total_pa_2023['tot_saldo_akhir'] + $total_pax_2023['tot_saldo_akhir']),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($total_pa_2024['tot_saldo_akhir'] + $total_pax_2024['tot_saldo_akhir']),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($total_pa_2025['tot_saldo_akhir'] + $total_pax_2025['tot_saldo_akhir']),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($total_pa_2026['tot_saldo_akhir'] + $total_pax_2026['tot_saldo_akhir']),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($total_pa_2027['tot_saldo_akhir'] + $total_pax_2027['tot_saldo_akhir']),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($total_pa_2028['tot_saldo_akhir'] + $total_pax_2028['tot_saldo_akhir']),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($total_pa_2029['tot_saldo_akhir'] + $total_pax_2029['tot_saldo_akhir']),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($total_pa_2030['tot_saldo_akhir'] + $total_pax_2030['tot_saldo_akhir']),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($total_pa_2031['tot_saldo_akhir'] + $total_pax_2031['tot_saldo_akhir']),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($total_pa_2032['tot_saldo_akhir'] + $total_pax_2032['tot_saldo_akhir']),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($total_pa_2033['tot_saldo_akhir'] + $total_pax_2033['tot_saldo_akhir']),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($total_pa_2034['tot_saldo_akhir'] + $total_pax_2034['tot_saldo_akhir']),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($total_pa_2035['tot_saldo_akhir'] + $total_pax_2035['tot_saldo_akhir']),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($total_pa_2036['tot_saldo_akhir'] + $total_pax_2036['tot_saldo_akhir']),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($total_pa_2037['tot_saldo_akhir'] + $total_pax_2037['tot_saldo_akhir']),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($total_pa_2038['tot_saldo_akhir'] + $total_pax_2038['tot_saldo_akhir']),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($total_pa_2039['tot_saldo_akhir'] + $total_pax_2039['tot_saldo_akhir']),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($total_pa_2040['tot_saldo_akhir'] + $total_pax_2040['tot_saldo_akhir']),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($total_pa_2041['tot_saldo_akhir'] + $total_pax_2041['tot_saldo_akhir']),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($total_pa_2042['tot_saldo_akhir'] + $total_pax_2042['tot_saldo_akhir']),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($total_pa_2043['tot_saldo_akhir'] + $total_pax_2043['tot_saldo_akhir']),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($total_pa_2044['tot_saldo_akhir'] + $total_pax_2044['tot_saldo_akhir']),0, ',', '.').'</td></B>';


echo '<td align="right"><B>'.number_format(compat_num($total_pa_3000['tot_saldo_akhir'] + $total_pax_3000['tot_saldo_akhir']),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($total_pa_3001['tot_saldo_akhir'] + $total_pax_3001['tot_saldo_akhir']),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($total_pa_3002['tot_saldo_akhir'] + $total_pax_3002['tot_saldo_akhir']),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($total_pa_3003['tot_saldo_akhir'] + $total_pax_3003['tot_saldo_akhir']),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($total_pa_3004['tot_saldo_akhir'] + $total_pax_3004['tot_saldo_akhir']),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($total_pa_3005['tot_saldo_akhir'] + $total_pax_3005['tot_saldo_akhir']),0, ',', '.').'</td></B>';

echo '<td align="right"><B>'.number_format(compat_num($total_pa_4000['tot_saldo_akhir'] + $total_pax_4000['tot_saldo_akhir']),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($total_pa_4001['tot_saldo_akhir'] + $total_pax_4001['tot_saldo_akhir']),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($total_pa_4002['tot_saldo_akhir'] + $total_pax_4002['tot_saldo_akhir']),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($total_pa_4003['tot_saldo_akhir'] + $total_pax_4003['tot_saldo_akhir']),0, ',', '.').'</td></B>';

		
        echo '<td align="right"><B>'.number_format(compat_num($total_pa_tot['tot_saldo_akhir'] + $total_pax_tot['tot_saldo_akhir']),0, ',', '.').'</td></B>';
        echo '</tr>';
	
	echo '<tr class="grey">
		<td colspan="3"><b></td>';
		
		
       echo '<td align="right">'.$t.'</td>';
		echo '<td align="right">'.$t.'</td>';
		echo '<td align="right">'.$t.'</td>';
		echo '<td align="right">'.$t.'</td>';
		echo '<td align="right">'.$t.'</td>';
		echo '<td align="right">'.$t.'</td>';
		echo '<td align="right">'.$t.'</td>';
		echo '<td align="right">'.$t.'</td>';
		echo '<td align="right">'.$t.'</td>';
		echo '<td align="right">'.$t.'</td>';
		
		echo '<td align="right">'.$t.'</td>';
		echo '<td align="right">'.$t.'</td>';
		echo '<td align="right">'.$t.'</td>';
		echo '<td align="right">'.$t.'</td>';
		echo '<td align="right">'.$t.'</td>';
		echo '<td align="right">'.$t.'</td>';
		echo '<td align="right">'.$t.'</td>';
		echo '<td align="right">'.$t.'</td>';
		echo '<td align="right">'.$t.'</td>';
		echo '<td align="right">'.$t.'</td>';
		
		
		echo '<td align="right">'.$t.'</td>';
		echo '<td align="right">'.$t.'</td>';
		echo '<td align="right">'.$t.'</td>';
		echo '<td align="right">'.$t.'</td>';
		echo '<td align="right">'.$t.'</td>';
		echo '<td align="right">'.$t.'</td>';
		echo '<td align="right">'.$t.'</td>';
		echo '<td align="right">'.$t.'</td>';
		echo '<td align="right">'.$t.'</td>';
		echo '<td align="right">'.$t.'</td>';
		
		echo '<td align="right">'.$t.'</td>';
		echo '<td align="right">'.$t.'</td>';
		echo '<td align="right">'.$t.'</td>';
		echo '<td align="right">'.$t.'</td>';
		echo '<td align="right">'.$t.'</td>';
echo '<td align="right">'.$t.'</td>';
		echo '<td align="right">'.$t.'</td>';
		echo '<td align="right">'.$t.'</td>';
		echo '<td align="right">'.$t.'</td>';
		echo '<td align="right">'.$t.'</td>';


echo '<td align="right">'.$t.'</td>';
echo '<td align="right">'.$t.'</td>';
echo '<td align="right">'.$t.'</td>';
echo '<td align="right">'.$t.'</td>';
echo '<td align="right">'.$t.'</td>';
echo '<td align="right">'.$t.'</td>';
echo '<td align="right">'.$t.'</td>';
echo '<td align="right">'.$t.'</td>';
echo '<td align="right">'.$t.'</td>';
echo '<td align="right">'.$t.'</td>';
echo '<td align="right">'.$t.'</td>';
echo '<td align="right">'.$t.'</td>';
echo '<td align="right">'.$t.'</td>';
echo '<td align="right">'.$t.'</td>';
echo '<td align="right">'.$t.'</td>';
echo '<td align="right">'.$t.'</td>';

echo '<td align="right">'.$t.'</td>';
echo '<td align="right">'.$t.'</td>';
echo '<td align="right">'.$t.'</td>';
echo '<td align="right">'.$t.'</td>';
echo '<td align="right">'.$t.'</td>';

echo '<td align="right">'.$t.'</td>';
echo '<td align="right">'.$t.'</td>';
echo '<td align="right">'.$t.'</td>';
echo '<td align="right">'.$t.'</td>';
echo '<td align="right">'.$t.'</td>';
echo '<td align="right">'.$t.'</td>';
echo '<td align="right">'.$t.'</td>';
echo '<td align="right">'.$t.'</td>';
echo '<td align="right">'.$t.'</td>';

		
		echo '<td align="right">'.$t.'</td>';
        echo '<td align="right">'.$t.'</td>';
		
        echo '</tr>';


        echo '</tr>';
	
while($row=mysql_fetch_array($query_laba))
    
	{
			if ($row['LINE_BALANCE'] == 'HEADING') {
		$class = ' class="subtotal"';
	} else{
		$class = ' class="subtotall"';
	}	
        echo '<tr '.$class.' >';
		echo '<td>'.$row['LINE_BALANCE'].'</td>';
        echo '<td>'.$row['kode_rekening'].'</td>';
        echo '<td align="Left">'.$row['nama_rekening'].'</td>';
        echo '<td align="right">'.number_format(compat_num($row['1010']),0, ',', '.').'</td>';
		echo '<td align="right">'.number_format(compat_num($row['001']),0, ',', '.').'</td>';
		echo '<td align="right">'.number_format(compat_num($row['002']),0, ',', '.').'</td>';
		echo '<td align="right">'.number_format(compat_num($row['003']),0, ',', '.').'</td>';
		echo '<td align="right">'.number_format(compat_num($row['004']),0, ',', '.').'</td>';
		echo '<td align="right">'.number_format(compat_num($row['005']),0, ',', '.').'</td>';
		echo '<td align="right">'.number_format(compat_num($row['006']),0, ',', '.').'</td>';
		echo '<td align="right">'.number_format(compat_num($row['007']),0, ',', '.').'</td>';
		echo '<td align="right">'.number_format(compat_num($row['008']),0, ',', '.').'</td>';
		echo '<td align="right">'.number_format(compat_num($row['009']),0, ',', '.').'</td>';
		echo '<td align="right">'.number_format(compat_num($row['010']),0, ',', '.').'</td>';
echo '<td align="right">'.number_format(compat_num($row['011']),0, ',', '.').'</td>';
echo '<td align="right">'.number_format(compat_num($row['012']),0, ',', '.').'</td>';
echo '<td align="right">'.number_format(compat_num($row['013']),0, ',', '.').'</td>';
echo '<td align="right">'.number_format(compat_num($row['014']),0, ',', '.').'</td>';
echo '<td align="right">'.number_format(compat_num($row['015']),0, ',', '.').'</td>';
echo '<td align="right">'.number_format(compat_num($row['016']),0, ',', '.').'</td>';
		echo '<td align="right">'.number_format(compat_num($row['2001']),0, ',', '.').'</td>';
		echo '<td align="right">'.number_format(compat_num($row['2002']),0, ',', '.').'</td>';
		echo '<td align="right">'.number_format(compat_num($row['2003']),0, ',', '.').'</td>';
		echo '<td align="right">'.number_format(compat_num($row['2004']),0, ',', '.').'</td>';
		echo '<td align="right">'.number_format(compat_num($row['2005']),0, ',', '.').'</td>';
		echo '<td align="right">'.number_format(compat_num($row['2006']),0, ',', '.').'</td>';
		echo '<td align="right">'.number_format(compat_num($row['2007']),0, ',', '.').'</td>';
		echo '<td align="right">'.number_format(compat_num($row['2008']),0, ',', '.').'</td>';
		echo '<td align="right">'.number_format(compat_num($row['2009']),0, ',', '.').'</td>';
		echo '<td align="right">'.number_format(compat_num($row['2010']),0, ',', '.').'</td>';
		echo '<td align="right">'.number_format(compat_num($row['2011']),0, ',', '.').'</td>';
		echo '<td align="right">'.number_format(compat_num($row['2012']),0, ',', '.').'</td>';
		echo '<td align="right">'.number_format(compat_num($row['2013']),0, ',', '.').'</td>';
		echo '<td align="right">'.number_format(compat_num($row['2014']),0, ',', '.').'</td>';
		echo '<td align="right">'.number_format(compat_num($row['2015']),0, ',', '.').'</td>';
		echo '<td align="right">'.number_format(compat_num($row['2016']),0, ',', '.').'</td>';
		echo '<td align="right">'.number_format(compat_num($row['2017']),0, ',', '.').'</td>';
		echo '<td align="right">'.number_format(compat_num($row['2018']),0, ',', '.').'</td>';
		echo '<td align="right">'.number_format(compat_num($row['2019']),0, ',', '.').'</td>';
		echo '<td align="right">'.number_format(compat_num($row['2020']),0, ',', '.').'</td>';
		echo '<td align="right">'.number_format(compat_num($row['2021']),0, ',', '.').'</td>';
		echo '<td align="right">'.number_format(compat_num($row['2022']),0, ',', '.').'</td>';
		echo '<td align="right">'.number_format(compat_num($row['2023']),0, ',', '.').'</td>';
		echo '<td align="right">'.number_format(compat_num($row['2024']),0, ',', '.').'</td>';
		echo '<td align="right">'.number_format(compat_num($row['2025']),0, ',', '.').'</td>';

echo '<td align="right">'.number_format(compat_num($row['2026']),0, ',', '.').'</td>';
echo '<td align="right">'.number_format(compat_num($row['2027']),0, ',', '.').'</td>';
echo '<td align="right">'.number_format(compat_num($row['2028']),0, ',', '.').'</td>';
echo '<td align="right">'.number_format(compat_num($row['2029']),0, ',', '.').'</td>';
echo '<td align="right">'.number_format(compat_num($row['2030']),0, ',', '.').'</td>';
echo '<td align="right">'.number_format(compat_num($row['2031']),0, ',', '.').'</td>';
echo '<td align="right">'.number_format(compat_num($row['2032']),0, ',', '.').'</td>';
echo '<td align="right">'.number_format(compat_num($row['2033']),0, ',', '.').'</td>';
echo '<td align="right">'.number_format(compat_num($row['2034']),0, ',', '.').'</td>';
echo '<td align="right">'.number_format(compat_num($row['2035']),0, ',', '.').'</td>';
echo '<td align="right">'.number_format(compat_num($row['2036']),0, ',', '.').'</td>';
echo '<td align="right">'.number_format(compat_num($row['2037']),0, ',', '.').'</td>';
echo '<td align="right">'.number_format(compat_num($row['2038']),0, ',', '.').'</td>';
echo '<td align="right">'.number_format(compat_num($row['2039']),0, ',', '.').'</td>';
echo '<td align="right">'.number_format(compat_num($row['2040']),0, ',', '.').'</td>';
echo '<td align="right">'.number_format(compat_num($row['2041']),0, ',', '.').'</td>';
echo '<td align="right">'.number_format(compat_num($row['2042']),0, ',', '.').'</td>';
echo '<td align="right">'.number_format(compat_num($row['2043']),0, ',', '.').'</td>';
echo '<td align="right">'.number_format(compat_num($row['2044']),0, ',', '.').'</td>';

echo '<td align="right">'.number_format(compat_num($row['3000']),0, ',', '.').'</td>';
echo '<td align="right">'.number_format(compat_num($row['3001']),0, ',', '.').'</td>';
echo '<td align="right">'.number_format(compat_num($row['3002']),0, ',', '.').'</td>';
echo '<td align="right">'.number_format(compat_num($row['3003']),0, ',', '.').'</td>';
echo '<td align="right">'.number_format(compat_num($row['3004']),0, ',', '.').'</td>';
echo '<td align="right">'.number_format(compat_num($row['3005']),0, ',', '.').'</td>';

echo '<td align="right">'.number_format(compat_num($row['4000']),0, ',', '.').'</td>';
echo '<td align="right">'.number_format(compat_num($row['4001']),0, ',', '.').'</td>';
echo '<td align="right">'.number_format(compat_num($row['4002']),0, ',', '.').'</td>';
echo '<td align="right">'.number_format(compat_num($row['4003']),0, ',', '.').'</td>';
		
        echo '<td align="right">'.number_format(compat_num($row['total_trx']),0, ',', '.').'</td>';
        echo '</tr>';
    }
		
	echo '<tr class="grey">
		<td colspan="3"><b></td>';
		
		
        echo '<td align="right">'.$t.'</td>';
		echo '<td align="right">'.$t.'</td>';
		echo '<td align="right">'.$t.'</td>';
		echo '<td align="right">'.$t.'</td>';
		echo '<td align="right">'.$t.'</td>';
		echo '<td align="right">'.$t.'</td>';
		echo '<td align="right">'.$t.'</td>';
		echo '<td align="right">'.$t.'</td>';
		echo '<td align="right">'.$t.'</td>';
		echo '<td align="right">'.$t.'</td>';
		
		echo '<td align="right">'.$t.'</td>';
		echo '<td align="right">'.$t.'</td>';
		echo '<td align="right">'.$t.'</td>';
		echo '<td align="right">'.$t.'</td>';
		echo '<td align="right">'.$t.'</td>';
		echo '<td align="right">'.$t.'</td>';
		echo '<td align="right">'.$t.'</td>';
		echo '<td align="right">'.$t.'</td>';
		echo '<td align="right">'.$t.'</td>';
		echo '<td align="right">'.$t.'</td>';
		
		
		echo '<td align="right">'.$t.'</td>';
		echo '<td align="right">'.$t.'</td>';
		echo '<td align="right">'.$t.'</td>';
		echo '<td align="right">'.$t.'</td>';
		echo '<td align="right">'.$t.'</td>';
		echo '<td align="right">'.$t.'</td>';
		echo '<td align="right">'.$t.'</td>';
		echo '<td align="right">'.$t.'</td>';
		echo '<td align="right">'.$t.'</td>';
		echo '<td align="right">'.$t.'</td>';
		
		echo '<td align="right">'.$t.'</td>';
		echo '<td align="right">'.$t.'</td>';
		echo '<td align="right">'.$t.'</td>';
		echo '<td align="right">'.$t.'</td>';
		echo '<td align="right">'.$t.'</td>';

		echo '<td align="right">'.$t.'</td>';
		echo '<td align="right">'.$t.'</td>';
		echo '<td align="right">'.$t.'</td>';
		echo '<td align="right">'.$t.'</td>';
		echo '<td align="right">'.$t.'</td>';
echo '<td align="right">'.$t.'</td>';
echo '<td align="right">'.$t.'</td>';
echo '<td align="right">'.$t.'</td>';
echo '<td align="right">'.$t.'</td>';
echo '<td align="right">'.$t.'</td>';
echo '<td align="right">'.$t.'</td>';
echo '<td align="right">'.$t.'</td>';
echo '<td align="right">'.$t.'</td>';
echo '<td align="right">'.$t.'</td>';
echo '<td align="right">'.$t.'</td>';
echo '<td align="right">'.$t.'</td>';
echo '<td align="right">'.$t.'</td>';
echo '<td align="right">'.$t.'</td>';
echo '<td align="right">'.$t.'</td>';
echo '<td align="right">'.$t.'</td>';
echo '<td align="right">'.$t.'</td>';

echo '<td align="right">'.$t.'</td>';	
echo '<td align="right">'.$t.'</td>';
echo '<td align="right">'.$t.'</td>';
echo '<td align="right">'.$t.'</td>';
echo '<td align="right">'.$t.'</td>';		
echo '<td align="right">'.$t.'</td>';	
echo '<td align="right">'.$t.'</td>';
echo '<td align="right">'.$t.'</td>';
echo '<td align="right">'.$t.'</td>';
echo '<td align="right">'.$t.'</td>';		
echo '<td align="right">'.$t.'</td>';
echo '<td align="right">'.$t.'</td>';
echo '<td align="right">'.$t.'</td>';
echo '<td align="right">'.$t.'</td>';		

	
		echo '<td align="right">'.$t.'</td>';
        echo '<td align="right">'.$t.'</td>';
		
        echo '</tr>';
	
	
	
	
	while($row2=mysql_fetch_array($total_biaya)){
	$unit1b=$row2['001'];
	$unit2b=$row2['002'];
	$unit3b=$row2['003'];
	$unit4b=$row2['004'];
	$unit5b=$row2['005'];
	$unit6b=$row2['006'];
	$unit7b=$row2['007'];
	$unit8b=$row2['008'];
	$unit9b=$row2['009'];
	$unit10b=$row2['010'];
$unit11b=$row2['011'];
$unit12b=$row2['012'];
$unit13b=$row2['013'];
$unit14b=$row2['014'];
$unit15b=$row2['015'];
$unit16b=$row2['016'];
	$unit21b=$row2['2001'];
	$unit22b=$row2['2002'];
	$unit23b=$row2['2003'];
	$unit24b=$row2['2004'];
	$unit25b=$row2['2005'];
	$unit26b=$row2['2006'];
	$unit27b=$row2['2007'];
	$unit28b=$row2['2008'];
	$unit29b=$row2['2009'];
	$unit210b=$row2['2010'];
	$unit211b=$row2['2011'];
	$unit212b=$row2['2012'];
	$unit213b=$row2['2013'];
	$unit214b=$row2['2014'];
	$unit215b=$row2['2015'];
	$unit216b=$row2['2016'];
	$unit217b=$row2['2017'];
	$unit218b=$row2['2018'];
	$unit219b=$row2['2019'];
	$unit220b=$row2['2020'];
	$unit221b=$row2['2021'];
	$unit222b=$row2['2022'];
	$unit223b=$row2['2023'];
	$unit224b=$row2['2024'];
	$unit225b=$row2['2025'];
$unit226b=$row2['2026'];
$unit227b=$row2['2027'];
$unit228b=$row2['2028'];
$unit229b=$row2['2029'];
$unit230b=$row2['2030'];
$unit231b=$row2['2031'];
$unit232b=$row2['2032'];
$unit233b=$row2['2033'];
$unit234b=$row2['2034'];
$unit235b=$row2['2035'];
$unit236b=$row2['2036'];
$unit237b=$row2['2037'];
$unit238b=$row2['2038'];
$unit239b=$row2['2039'];
$unit240b=$row2['2040'];
$unit241b=$row2['2041'];
$unit242b=$row2['2042'];
$unit243b=$row2['2043'];
$unit244b=$row2['2044'];


$unit3000b=$row2['3000'];
$unit3001b=$row2['3001'];
$unit3002b=$row2['3002'];
$unit3003b=$row2['3003'];
$unit3004b=$row2['3004'];
$unit3005b=$row2['3005'];

$unit4000b=$row2['4000'];
$unit4001b=$row2['4001'];
$unit4002b=$row2['4002'];
$unit4003b=$row2['4003'];

	$unit1010b=$row2['1010'];
	$total_b=$row2['total_trx'];
		}
	echo '<tr class="grey">
		<td colspan="3"><b>SHU OPERASIONAL</td>';
		
		while($row=mysql_fetch_array($total_pendapan))
    {

	$unit1p=$row['001']-$unit1b;
	$unit2p=$row['002']-$unit2b;
	$unit3p=$row['003']-$unit3b;
	$unit4p=$row['004']-$unit4b;
	$unit5p=$row['005']-$unit5b;
	$unit6p=$row['006']-$unit6b;
	$unit7p=$row['007']-$unit7b;
	$unit8p=$row['008']-$unit8b;
	$unit9p=$row['009']-$unit9b;
	$unit10p=$row['010']-$unit10b;
	$unit11p=$row['011']-$unit11b;
	$unit12p=$row['012']-$unit12b;
	$unit13p=$row['013']-$unit13b;
	$unit14p=$row['014']-$unit14b;
	$unit15p=$row['015']-$unit15b;
	$unit16p=$row['016']-$unit16b;
	
	$unit21p=$row['2001']-$unit21b;
	$unit22p=$row['2002']-$unit22b;
	$unit23p=$row['2003']-$unit23b;
	$unit24p=$row['2004']-$unit24b;
	$unit25p=$row['2005']-$unit25b;
	$unit26p=$row['2006']-$unit26b;
	$unit27p=$row['2007']-$unit27b;
	$unit28p=$row['2008']-$unit28b;
	$unit29p=$row['2009']-$unit29b;
	$unit210p=$row['2010']-$unit210b;
	$unit211p=$row['2011']-$unit211b;
	$unit212p=$row['2012']-$unit212b;
	$unit213p=$row['2013']-$unit213b;
	$unit214p=$row['2014']-$unit214b;
	$unit215p=$row['2015']-$unit215b;
	$unit216p=$row['2016']-$unit216b;
	$unit217p=$row['2017']-$unit217b;
	$unit218p=$row['2018']-$unit218b;
	$unit219p=$row['2019']-$unit219b;
	$unit220p=$row['2020']-$unit220b;
	$unit221p=$row['2021']-$unit221b;
	$unit222p=$row['2022']-$unit222b;
	$unit223p=$row['2023']-$unit223b;
	$unit224p=$row['2024']-$unit224b;
	$unit225p=$row['2025']-$unit225b;
$unit226p=$row['2026']-$unit226b;
$unit227p=$row['2027']-$unit227b;
$unit228p=$row['2028']-$unit228b;
$unit229p=$row['2029']-$unit229b;
$unit230p=$row['2030']-$unit230b;
$unit231p=$row['2031']-$unit231b;
$unit232p=$row['2032']-$unit232b;
$unit233p=$row['2033']-$unit233b;
$unit234p=$row['2034']-$unit234b;
$unit235p=$row['2035']-$unit235b;
$unit236p=$row['2036']-$unit236b;
$unit237p=$row['2037']-$unit237b;
$unit238p=$row['2038']-$unit238b;
$unit239p=$row['2039']-$unit239b;
$unit240p=$row['2040']-$unit240b;
$unit241p=$row['2041']-$unit241b;
$unit242p=$row['2042']-$unit242b;
$unit243p=$row['2043']-$unit243b;
$unit244p=$row['2044']-$unit244b;

$unit3000p=$row['3000']-$unit3000b;
$unit3001p=$row['3001']-$unit3001b;
$unit3002p=$row['3002']-$unit3002b;
$unit3003p=$row['3003']-$unit3003b;
$unit3004p=$row['3004']-$unit3004b;
$unit3005p=$row['3005']-$unit3005b;

$unit4000p=$row['4000']-$unit4000b;
$unit4001p=$row['4001']-$unit4001b;
$unit4002p=$row['4002']-$unit4002b;
$unit4003p=$row['4003']-$unit4003b;

	$unit1010p=$row['1010']-$unit1010b;
	$totalp=$unit1p+$unit2p+$unit3p+$unit4p+$unit5p+$unit6p+$unit7p+$unit8p+$unit9p+$unit10p+$unit11p+$unit12p+$unit13p+$unit14p+$unit15p+$unit16p+
	$unit21p+$unit22p+$unit23p+$unit24p+$unit25p+$unit26p+$unit27p+$unit28p+$unit29p+$unit210p+
	$unit211p+$unit212p+$unit213p+$unit214p+$unit215p+$unit216p+$unit217p+$unit218p+$unit219p+$unit220p+
	$unit221p+$unit222p+$unit223p+$unit224p+$unit225p+$unit226p+$unit227p+$unit228p+$unit229p+$unit230p+$unit231p+$unit232p+$unit233p+$unit234p+$unit235p+$unit236p+$unit237p+$unit238p+
	$unit239p+$unit240p+$unit241p+$unit242p+$unit243p+$unit244p+
	$unit1010p+$unit3000p+$unit3001p+$unit3002p+$unit3003p+$unit3004p+$unit3005p+
	$unit4000p+$unit4001p+$unit4002p+$unit4003p;
	}
        echo '<td align="right"><B>'.number_format(compat_num($unit1010p),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($unit1p),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($unit2p),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($unit3p),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($unit4p),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($unit5p),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($unit6p),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($unit7p),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($unit8p),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($unit9p),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($unit10p),0, ',', '.').'</td></B>';

echo '<td align="right"><B>'.number_format(compat_num($unit11p),0, ',', '.').'</td></B>';

echo '<td align="right"><B>'.number_format(compat_num($unit12p),0, ',', '.').'</td></B>';

echo '<td align="right"><B>'.number_format(compat_num($unit13p),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($unit14p),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($unit15p),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($unit16p),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($unit21p),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($unit22p),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($unit23p),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($unit24p),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($unit25p),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($unit26p),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($unit27p),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($unit28p),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($unit29p),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($unit210p),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($unit211p),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($unit212p),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($unit213p),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($unit214p),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($unit215p),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($unit216p),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($unit217p),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($unit218p),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($unit219p),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($unit220p),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($unit221p),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($unit222p),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($unit223p),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($unit224p),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($unit225p),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($unit226p),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($unit227p),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($unit228p),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($unit229p),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($unit230p),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($unit231p),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($unit232p),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($unit233p),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($unit234p),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($unit235p),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($unit236p),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($unit237p),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($unit238p),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($unit239p),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($unit240p),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($unit241p),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($unit242p),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($unit243p),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($unit244p),0, ',', '.').'</td></B>';

echo '<td align="right"><B>'.number_format(compat_num($unit3000p),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($unit3001p),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($unit3002p),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($unit3003p),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($unit3004p),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($unit3005p),0, ',', '.').'</td></B>';

echo '<td align="right"><B>'.number_format(compat_num($unit4000p),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($unit4001p),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($unit4002p),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($unit4003p),0, ',', '.').'</td></B>';
		
        echo '<td align="right"><B>'.number_format(compat_num($totalp),0, ',', '.').'</td>';
        echo '</tr>';
	
$total_pajak=mysql_fetch_array(mysql_query("select sum(saldo_akhir) as saldo_akhir  from ".$sumber." 
where kode_rekening='59999' and tanggal_awal='$tgl_cari' and unit ='0000'"));
$saldo_pajak=$total_pajak['saldo_akhir'];

	while($row2=mysql_fetch_array($total_beban)){
	$unit1=$row2['001'];
	$unit2=$row2['002'];
	$unit3=$row2['003'];
	$unit4=$row2['004'];
	$unit5=$row2['005'];
	$unit6=$row2['006'];
	$unit7=$row2['007'];
	$unit8=$row2['008'];
	$unit9=$row2['009'];
	$unit10=$row2['010'];
	$unit11=$row2['011'];
	$unit12=$row2['012'];
	$unit13=$row2['013'];
	$unit14=$row2['014'];
$unit15=$row2['015'];
$unit16=$row2['016'];
	$unit21=$row2['2001'];
	$unit22=$row2['2002'];
	$unit23=$row2['2003'];
	$unit24=$row2['2004'];
	$unit25=$row2['2005'];
	$unit26=$row2['2006'];
	$unit27=$row2['2007'];
	$unit28=$row2['2008'];
	$unit29=$row2['2009'];
	$unit210=$row2['2010'];
	$unit211=$row2['2011'];
	$unit212=$row2['2012'];
	$unit213=$row2['2013'];
	$unit214=$row2['2014'];
	$unit215=$row2['2015'];
	$unit216=$row2['2016'];
	$unit217=$row2['2017'];
	$unit218=$row2['2018'];
	$unit219=$row2['2019'];
	$unit220=$row2['2020'];
	$unit221=$row2['2021'];
	$unit222=$row2['2022'];
	$unit223=$row2['2023'];
	$unit224=$row2['2024'];
	$unit225=$row2['2025'];
$unit226=$row2['2026'];
$unit227=$row2['2027'];
$unit228=$row2['2028'];
$unit229=$row2['2029'];
$unit230=$row2['2030'];
$unit231=$row2['2031'];
$unit232=$row2['2032'];
$unit233=$row2['2033'];
$unit234=$row2['2034'];
$unit235=$row2['2035'];
$unit236=$row2['2036'];
$unit237=$row2['2037'];
$unit238=$row2['2038'];
$unit239=$row2['2039'];
$unit240=$row2['2040'];
$unit241=$row2['2041'];
$unit242=$row2['2042'];
$unit243=$row2['2043'];
$unit244=$row2['2044'];

$unit3000=$row2['3000'];
$unit3001=$row2['3001'];
$unit3002=$row2['3002'];
$unit3003=$row2['3003'];
$unit3004=$row2['3004'];
$unit3005=$row2['3005'];

$unit4000=$row2['4000'];
$unit4001=$row2['4001'];
$unit4002=$row2['4002'];
$unit4003=$row2['4003'];

	$unit1010=$row2['1010'];
	$total_b=$row2['total_trx'];
		}
	echo '<tr class="grey">

		<td colspan="3"><b>SHU NON OPERASIONAL</td>';
			while($row=mysql_fetch_array($total_pendapan_n))
    {

	$unit1n=$row['001']-$unit1;
	$unit2n=$row['002']-$unit2;
	$unit3n=$row['003']-$unit3;
	$unit4n=$row['004']-$unit4;
	$unit5n=$row['005']-$unit5;
	$unit6n=$row['006']-$unit6;
	$unit7n=$row['007']-$unit7;
	$unit8n=$row['008']-$unit8;
	$unit9n=$row['009']-$unit9;
	$unit10n=$row['010']-$unit10;
$unit11n=$row['011']-$unit11;
$unit12n=$row['012']-$unit12;
$unit13n=$row['013']-$unit13;
$unit14n=$row['014']-$unit14;
$unit15n=$row['015']-$unit15;
$unit16n=$row['016']-$unit16;
	$unit21n=$row['2001']-$unit21;
	$unit22n=$row['2002']-$unit22;
	$unit23n=$row['2003']-$unit23;
	$unit24n=$row['2004']-$unit24;
	$unit25n=$row['2005']-$unit25;
	$unit26n=$row['2006']-$unit26;
	$unit27n=$row['2007']-$unit27;
	$unit28n=$row['2008']-$unit28;
	$unit29n=$row['2009']-$unit29;
	$unit210n=$row['2010']-$unit210;
	$unit211n=$row['2011']-$unit211;
	$unit212n=$row['2012']-$unit212;
	$unit213n=$row['2013']-$unit213;
	$unit214n=$row['2014']-$unit214;
	$unit215n=$row['2015']-$unit215;
	$unit216n=$row['2016']-$unit216;
	$unit217n=$row['2017']-$unit217;
	$unit218n=$row['2018']-$unit218;
	$unit219n=$row['2019']-$unit219;
	$unit220n=$row['2020']-$unit220;
	$unit221n=$row['2021']-$unit221;
	$unit222n=$row['2022']-$unit222;
	$unit223n=$row['2023']-$unit223;
	$unit224n=$row['2024']-$unit224;
	$unit225n=$row['2025']-$unit225;
$unit226n=$row['2026']-$unit226;
$unit227n=$row['2027']-$unit227;
$unit228n=$row['2028']-$unit228;
$unit229n=$row['2029']-$unit229;
$unit230n=$row['2030']-$unit230;
$unit231n=$row['2031']-$unit231;
$unit232n=$row['2032']-$unit232;
$unit233n=$row['2033']-$unit233;
$unit234n=$row['2034']-$unit234;
$unit235n=$row['2035']-$unit235;
$unit236n=$row['2036']-$unit236;
$unit237n=$row['2037']-$unit237;
$unit238n=$row['2038']-$unit238;
$unit239n=$row['2039']-$unit239;
$unit240n=$row['2040']-$unit240;
$unit241n=$row['2041']-$unit241;
$unit242n=$row['2042']-$unit242;
$unit243n=$row['2043']-$unit243;
$unit244n=$row['2044']-$unit244;

$unit3000n=$row['3000']-$unit3000;
$unit3001n=$row['3001']-$unit3001;
$unit3002n=$row['3002']-$unit3002;
$unit3003n=$row['3003']-$unit3003;
$unit3004n=$row['3004']-$unit3004;
$unit3005n=$row['3005']-$unit3005;

$unit4000n=$row['4000']-$unit4000;
$unit4001n=$row['4001']-$unit4001;
$unit4002n=$row['4002']-$unit4002;
$unit4003n=$row['4003']-$unit4003;

	$unit1010n=$row['1010']-$unit1010;
	$totaln=$unit1n+$unit2n+$unit3n+$unit4n+$unit5n+$unit6n+$unit7n+$unit8n+$unit9n+$unit10n+$unit11n+$unit12n+$unit13n+$unit14n+$unit15n+$unit16n+
	$unit21n+$unit22n+$unit23n+$unit24n+$unit25n+$unit26n+$unit27n+$unit28n+$unit29n+$unit210n+
	$unit211n+$unit212n+$unit213n+$unit214n+$unit215n+$unit216n+$unit217n+$unit218n+$unit219n+$unit220n+
	$unit221n+$unit222n+$unit223n+$unit224n+$unit225n+$unit226n+$unit227n+$unit228n+$unit229n+$unit230n+$unit231n+$unit232n+$unit233n+$unit234n+$unit235n+$unit236n+$unit237n+$unit238n+
	$unit239n+$unit240n+$unit241n+$unit242n+$unit243n+$unit244n+
	$unit3000n+$unit3001n+$unit3002n+$unit3003n+$unit3004n+$unit3005n+
	$unit4000n+$unit4001n+$unit4002n+$unit4003n+$unit1010n;
	}
	echo '<td align="right"><B>'.number_format(compat_num($unit1010n),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($unit1n),0, ',', '.').'</td></b>';
		echo '<td align="right"><B>'.number_format(compat_num($unit2n),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($unit3n),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($unit4n),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($unit5n),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($unit6n),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($unit7n),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($unit8n),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($unit9n),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($unit10n),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($unit11n),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($unit12n),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($unit13n),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($unit14n),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($unit15n),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($unit16n),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($unit21n),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($unit22n),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($unit23n),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($unit24n),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($unit25n),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($unit26n),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($unit27n),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($unit28n),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($unit29n),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($unit210n),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($unit211n),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($unit212n),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($unit213n),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($unit214n),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($unit215n),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($unit216n),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($unit217n),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($unit218n),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($unit219n),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($unit220n),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($unit221n),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($unit222n),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($unit223n),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($unit224n),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($unit225n),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($unit226n),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($unit227n),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($unit228n),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($unit229n),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($unit230n),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($unit231n),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($unit232n),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($unit233n),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($unit234n),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($unit235n),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($unit236n),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($unit237n),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($unit238n),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($unit239n),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($unit240n),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($unit241n),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($unit242n),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($unit243n),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($unit244n),0, ',', '.').'</td></B>';

echo '<td align="right"><B>'.number_format(compat_num($unit3000n),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($unit3001n),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($unit3002n),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($unit3003n),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($unit3004n),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($unit3005n),0, ',', '.').'</td></B>';

echo '<td align="right"><B>'.number_format(compat_num($unit4000n),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($unit4001n),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($unit4002n),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($unit4003n),0, ',', '.').'</td></B>';
		
        echo '<td align="right"><B>'.number_format(compat_num($totaln),0, ',', '.').'</td></B>';
        echo '</tr>';
	$shu1_sb=$unit1n + $unit1p;
	$shu2_sb=$unit2n + $unit2p;
	$shu3_sb=$unit3n + $unit3p;
	$shu4_sb=$unit4n + $unit4p;
	$shu5_sb=$unit5n + $unit5p;
	$shu6_sb=$unit6n + $unit6p;
	$shu7_sb=$unit7n + $unit7p;
	$shu8_sb=$unit8n + $unit8p;
	$shu9_sb=$unit9n + $unit9p;
	$shu10_sb=$unit10n+ $unit10p;
$shu11_sb=$unit11n+ $unit11p;
$shu12_sb=$unit12n+ $unit12p;
$shu13_sb=$unit13n+ $unit13p;
$shu14_sb=$unit14n+ $unit14p;
$shu15_sb=$unit15n+ $unit15p;
$shu16_sb=$unit16n+ $unit16p;
	
	$shu21_sb=$unit21n + $unit21p;
	$shu22_sb=$unit22n + $unit22p;
	$shu23_sb=$unit23n + $unit23p;
	$shu24_sb=$unit24n + $unit24p;
	$shu25_sb=$unit25n + $unit25p;
	$shu26_sb=$unit26n + $unit26p;
	$shu27_sb=$unit27n + $unit27p;
	$shu28_sb=$unit28n + $unit28p;
	$shu29_sb=$unit29n + $unit29p;
	$shu210_sb=$unit210n+ $unit210p;
	
	$shu211_sb=$unit211n + $unit211p;
	$shu212_sb=$unit212n + $unit212p;
	$shu213_sb=$unit213n + $unit213p;
	$shu214_sb=$unit214n + $unit214p;
	$shu215_sb=$unit215n + $unit215p;
	$shu216_sb=$unit216n + $unit216p;
	$shu217_sb=$unit217n + $unit217p;
	$shu218_sb=$unit218n + $unit218p;
	$shu219_sb=$unit219n + $unit219p;
	
	$shu220_sb=$unit220n+ $unit220p;
	$shu221_sb=$unit221n+ $unit221p;
	$shu222_sb=$unit222n+ $unit222p;
	$shu223_sb=$unit223n+ $unit223p;
	$shu224_sb=$unit224n+ $unit224p;
	$shu225_sb=$unit225n+ $unit225p;
	$shu226_sb=$unit226n+ $unit226p;
$shu227_sb=$unit227n+ $unit227p;
$shu228_sb=$unit228n+ $unit228p;
$shu229_sb=$unit229n+ $unit229p;
$shu230_sb=$unit230n+ $unit230p;
$shu231_sb=$unit231n+ $unit231p;
$shu232_sb=$unit232n+ $unit232p;
$shu233_sb=$unit233n+ $unit233p;
$shu234_sb=$unit234n+ $unit234p;
$shu235_sb=$unit235n+ $unit235p;
$shu236_sb=$unit236n+ $unit236p;
$shu237_sb=$unit237n+ $unit237p;
$shu238_sb=$unit238n+ $unit238p;
$shu239_sb=$unit239n+ $unit239p;
$shu240_sb=$unit240n+ $unit240p;
$shu241_sb=$unit241n+ $unit241p;
$shu242_sb=$unit242n+ $unit242p;
$shu243_sb=$unit243n+ $unit243p;
$shu244_sb=$unit244n+ $unit244p;

$shu3000_sb=$unit3000n+ $unit3000p;
$shu3001_sb=$unit3001n+ $unit3001p;
$shu3002_sb=$unit3002n+ $unit3002p;
$shu3003_sb=$unit3003n+ $unit3003p;
$shu3004_sb=$unit3004n+ $unit3004p;
$shu3005_sb=$unit3005n+ $unit3005p;

$shu4000_sb=$unit4000n+ $unit4000p;
$shu4001_sb=$unit4001n+ $unit4001p;
$shu4002_sb=$unit4002n+ $unit4002p;
$shu4003_sb=$unit4003n+ $unit4003p;

	$shu1010_sb=$unit1010n + $unit1010p;
	$shutot_sb=$shu1_sb+$shu2_sb+$shu3_sb+$shu4_sb+$shu5_sb+$shu6_sb+$shu7_sb+$shu8_sb+$shu9_sb+$shu10_sb+$shu11_sb+$shu12_sb+$shu13_sb+$shu14_sb+$shu15_sb+$shu16_sb+
	$shu21_sb+$shu22_sb+$shu23_sb+$shu24_sb+$shu25_sb+$shu26_sb+$shu27_sb+$shu28_sb+$shu29_sb+$shu210_sb+
	$shu211_sb+$shu212_sb+$shu213_sb+$shu214_sb+$shu215_sb+$shu216_sb+$shu217_sb+$shu218_sb+$shu219_sb+$shu220_sb+
	$shu221_sb+$shu222_sb+$shu223_sb+$shu224_sb+$shu225_sb+$shu226_sb+$shu227_sb+$shu228_sb+$shu229_sb+$shu230_sb+$shu231_sb+$shu232_sb+$shu233_sb+$shu234_sb+$shu235_sb+$shu236_sb+$shu237_sb+
	$shu238_sb+$shu239_sb+$shu240_sb+$shu241_sb+$shu242_sb+$shu243_sb+$shu244_sb+
	$shu1010_sb+$shu3000_sb+$shu3001_sb+$shu3002_sb+$shu3003_sb+$shu3004_sb+$shu3005_sb+
	$shu4000_sb+$shu4001_sb+$shu4002_sb+$shu4003_sb;
echo '<tr class="grey">

		<td colspan="3"><b>SHU TAHUN BERJALAN SEBELUM PAJAK</td>';
		echo '<td align="right"><B>'.number_format(compat_num($shu1010_sb),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($shu1_sb),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($shu2_sb),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($shu3_sb),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($shu4_sb),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($shu5_sb),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($shu6_sb),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($shu7_sb),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($shu8_sb),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($shu9_sb),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($shu10_sb),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($shu11_sb),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($shu12_sb),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($shu13_sb),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($shu14_sb),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($shu15_sb),0, ',', '.').'</td></B>';	
echo '<td align="right"><B>'.number_format(compat_num($shu16_sb),0, ',', '.').'</td></B>';		
		echo '<td align="right"><B>'.number_format(compat_num($shu21_sb),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($shu22_sb),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($shu23_sb),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($shu24_sb),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($shu25_sb),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($shu26_sb),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($shu27_sb),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($shu28_sb),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($shu29_sb),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($shu210_sb),0, ',', '.').'</td></B>';
		
		
		echo '<td align="right"><B>'.number_format(compat_num($shu211_sb),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($shu212_sb),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($shu213_sb),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($shu214_sb),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($shu215_sb),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($shu216_sb),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($shu217_sb),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($shu218_sb),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($shu219_sb),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($shu220_sb),0, ',', '.').'</td></B>';
		
		echo '<td align="right"><B>'.number_format(compat_num($shu221_sb),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($shu222_sb),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($shu223_sb),0, ',', '.').'</td></B>';
		echo '<td align="right"><B>'.number_format(compat_num($shu224_sb),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($shu225_sb),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($shu226_sb),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($shu227_sb),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($shu228_sb),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($shu229_sb),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($shu230_sb),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($shu231_sb),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($shu232_sb),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($shu233_sb),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($shu234_sb),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($shu235_sb),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($shu236_sb),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($shu237_sb),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($shu238_sb),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($shu239_sb),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($shu240_sb),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($shu241_sb),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($shu242_sb),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($shu243_sb),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($shu244_sb),0, ',', '.').'</td></B>';

echo '<td align="right"><B>'.number_format(compat_num($shu3000_sb),0, ',', '.').'</td></B>';		
echo '<td align="right"><B>'.number_format(compat_num($shu3001_sb),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($shu3002_sb),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($shu3003_sb),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($shu3004_sb),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($shu3005_sb),0, ',', '.').'</td></B>';

echo '<td align="right"><B>'.number_format(compat_num($shu4000_sb),0, ',', '.').'</td></B>';		
echo '<td align="right"><B>'.number_format(compat_num($shu4001_sb),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($shu4002_sb),0, ',', '.').'</td></B>';
echo '<td align="right"><B>'.number_format(compat_num($shu4003_sb),0, ',', '.').'</td></B>';

		
        echo '<td align="right"><B>'.number_format(compat_num($shutot_sb),0, ',', '.').'</td></B>';
        echo '</tr>';
//Estimasi Perhitungan Pajak
//sumbangan dan Jamuan 
//SHU_SEBELUMPAJAK
$sbg_jamu=mysql_fetch_array(mysql_query("select sum(saldo_akhir) as saldo_akhir from ".$sumber." where kode_rekening in('56211','56212','56311','56312') and tanggal_awal='$tgl_cari'"));

$smb_jamu=$sbg_jamu['saldo_akhir'];

//var_dump($smb_jamu);
$pendapatan=mysql_fetch_array(mysql_query("select sum(saldo_akhir) as saldo_akhir from ".$sumber." where kode_rekening in('40000') and tanggal_awal='$tgl_cari'"));
$pendapatan_p=$pendapatan['saldo_akhir'];
//var_dump($shutot_sb);

//SHU sebelum pajak tahun berjalan fiskal
$shu_sbp=$shutot_sb+$smb_jamu;
//var_dump($shu_sbp);
//Pendapatan
$totalp=$pendapatan_p;
//var_dump($totalp);


//parameter pajak 
$param_pajak=4800000000;
//persentase
$persen =($param_pajak/$totalp);
//var_dump($persen);



//taksiran pajak mendapat psilitas 
$taksiran_dapat=($shu_sbp * $persen);
//var_dump($taksiran_dapat);

//Taksiran tidak Mendapatan Pasilitas
$taksiran_tidak=$shu_sbp - $taksiran_dapat;
//var_dump($taksiran_tidak);
//Taksitan Pajaka
//25% X 50% X shu sebelum pajak
$tks_pajak=0.125* $taksiran_dapat;
//25 * b`
$tsk_b=(25/100) * $taksiran_tidak;

$estimasi_taksiran=$tks_pajak + $tsk_b;

$shu_bersih=$shutot_sb-$estimasi_taksiran;

	 echo '<tr class="grey">
		<td colspan="3"><b>ESTIMASI TAKSIRAN PAJAK PENGHASILAN</td>
		<td><b>' . number_format(compat_num($estimasi_taksiran), 0, ',', '.') . '</td></B>
		<td><b>' . number_format(compat_num($shu_ops_jalan1), 0, ',', '.') . '</td></B>
		<td><b>' . number_format(compat_num($shu_ops_akhir1), 0, ',', '.') . '</td></B>
		<td><b>' . number_format(compat_num($shu_ops_awal1), 0, ',', '.') . '</td></B>
		<td><b>' . number_format(compat_num($shu_ops_jalan1), 0, ',', '.') . '</td></B>
		<td><b>' . number_format(compat_num($shu_ops_akhir1), 0, ',', '.') . '</td></B>
		<td><b>' . number_format(compat_num($shu_ops_awal1), 0, ',', '.') . '</td></B>
		<td><b>' . number_format(compat_num($shu_ops_jalan1), 0, ',', '.') . '</td></B>
		<td><b>' . number_format(compat_num($shu_ops_akhir1), 0, ',', '.') . '</td></B>
		<td><b>' . number_format(compat_num($shu_ops_awal1), 0, ',', '.') . '</td></B>
		
				<td><b>' . number_format(compat_num($shu_ops_awal1), 0, ',', '.') . '</td></B>
		<td><b>' . number_format(compat_num($shu_ops_jalan1), 0, ',', '.') . '</td></B>
		<td><b>' . number_format(compat_num($shu_ops_akhir1), 0, ',', '.') . '</td></B>
		<td><b>' . number_format(compat_num($shu_ops_awal1), 0, ',', '.') . '</td></B>
		<td><b>' . number_format(compat_num($shu_ops_jalan1), 0, ',', '.') . '</td></B>
		<td><b>' . number_format(compat_num($shu_ops_akhir1), 0, ',', '.') . '</td></B>
		<td><b>' . number_format(compat_num($shu_ops_awal1), 0, ',', '.') . '</td></B>
		<td><b>' . number_format(compat_num($shu_ops_jalan1), 0, ',', '.') . '</td></B>
		<td><b>' . number_format(compat_num($shu_ops_akhir1), 0, ',', '.') . '</td></B>
		<td><b>' . number_format(compat_num($shu_ops_awal1), 0, ',', '.') . '</td></B>
		
				<td><b>' . number_format(compat_num($shu_ops_awal1), 0, ',', '.') . '</td></B>
		<td><b>' . number_format(compat_num($shu_ops_jalan1), 0, ',', '.') . '</td></B>
		<td><b>' . number_format(compat_num($shu_ops_akhir1), 0, ',', '.') . '</td></B>
		<td><b>' . number_format(compat_num($shu_ops_awal1), 0, ',', '.') . '</td></B>
		<td><b>' . number_format(compat_num($shu_ops_jalan1), 0, ',', '.') . '</td></B>
		<td><b>' . number_format(compat_num($shu_ops_akhir1), 0, ',', '.') . '</td></B>
		<td><b>' . number_format(compat_num($shu_ops_awal1), 0, ',', '.') . '</td></B>
		<td><b>' . number_format(compat_num($shu_ops_jalan1), 0, ',', '.') . '</td></B>
		<td><b>' . number_format(compat_num($shu_ops_akhir1), 0, ',', '.') . '</td></B>
		<td><b>' . number_format(compat_num($shu_ops_awal1), 0, ',', '.') . '</td></B>
		<td><b>' . number_format(compat_num($shu_ops_jalan1), 0, ',', '.') . '</td></B>
		<td><b>' . number_format(compat_num($shu_ops_akhir1), 0, ',', '.') . '</td></B>
		<td><b>' . number_format(compat_num($shu_ops_awal1), 0, ',', '.') . '</td></B>
		<td><b>' . number_format(compat_num($shu_ops_jalan1), 0, ',', '.') . '</td></B>
		<td><b>' . number_format(compat_num($shu_ops_jalan1), 0, ',', '.') . '</td></B>
		
		<td><b>' . number_format(compat_num($shu_ops_awal1), 0, ',', '.') . '</td></B>
		<td><b>' . number_format(compat_num($shu_ops_jalan1), 0, ',', '.') . '</td></B>
		<td><b>' . number_format(compat_num($shu_ops_jalan1), 0, ',', '.') . '</td></B>
<td><b>' . number_format(compat_num($shu_ops_jalan1), 0, ',', '.') . '</td></B>
<td><b>' . number_format(compat_num($shu_ops_jalan1), 0, ',', '.') . '</td></B>
<td><b>' . number_format(compat_num($shu_ops_awal1), 0, ',', '.') . '</td></B>
<td><b>' . number_format(compat_num($shu_ops_awal1), 0, ',', '.') . '</td></B>
		<td><b>' . number_format(compat_num($shu_ops_jalan1), 0, ',', '.') . '</td></B>
		<td><b>' . number_format(compat_num($shu_ops_jalan1), 0, ',', '.') . '</td></B>
<td><b>' . number_format(compat_num($shu_ops_jalan1), 0, ',', '.') . '</td></B>
<td><b>' . number_format(compat_num($shu_ops_jalan1), 0, ',', '.') . '</td></B>

<td><b>' . number_format(compat_num($shu_ops_jalan1), 0, ',', '.') . '</td></B>
<td><b>' . number_format(compat_num($shu_ops_jalan1), 0, ',', '.') . '</td></B>
<td><b>' . number_format(compat_num($shu_ops_jalan1), 0, ',', '.') . '</td></B>
<td><b>' . number_format(compat_num($shu_ops_jalan1), 0, ',', '.') . '</td></B>
<td><b>' . number_format(compat_num($shu_ops_jalan1), 0, ',', '.') . '</td></B>
<td><b>' . number_format(compat_num($shu_ops_jalan1), 0, ',', '.') . '</td></B>
<td><b>' . number_format(compat_num($shu_ops_jalan1), 0, ',', '.') . '</td></B>

<td><b>' . number_format(compat_num($shu_ops_jalan1), 0, ',', '.') . '</td></B>
<td><b>' . number_format(compat_num($shu_ops_jalan1), 0, ',', '.') . '</td></B>
<td><b>' . number_format(compat_num($shu_ops_jalan1), 0, ',', '.') . '</td></B>
<td><b>' . number_format(compat_num($shu_ops_jalan1), 0, ',', '.') . '</td></B>
<td><b>' . number_format(compat_num($shu_ops_jalan1), 0, ',', '.') . '</td></B>
<td><b>' . number_format(compat_num($shu_ops_jalan1), 0, ',', '.') . '</td></B>
<td><b>' . number_format(compat_num($shu_ops_jalan1), 0, ',', '.') . '</td></B>
<td><b>' . number_format(compat_num($shu_ops_jalan1), 0, ',', '.') . '</td></B>
<td><b>' . number_format(compat_num($shu_ops_jalan1), 0, ',', '.') . '</td></B>
<td><b>' . number_format(compat_num($shu_ops_jalan1), 0, ',', '.') . '</td></B>
<td><b>' . number_format(compat_num($shu_ops_jalan1), 0, ',', '.') . '</td></B>
<td><b>' . number_format(compat_num($shu_ops_jalan1), 0, ',', '.') . '</td></B>
<td><b>' . number_format(compat_num($shu_ops_jalan1), 0, ',', '.') . '</td></B>
<td><b>' . number_format(compat_num($shu_ops_jalan1), 0, ',', '.') . '</td></B>
<td><b>' . number_format(compat_num($shu_ops_jalan1), 0, ',', '.') . '</td></B>
<td><b>' . number_format(compat_num($shu_ops_jalan1), 0, ',', '.') . '</td></B>
<td><b>' . number_format(compat_num($shu_ops_jalan1), 0, ',', '.') . '</td></B>
<td><b>' . number_format(compat_num($shu_ops_jalan1), 0, ',', '.') . '</td></B>
		
		<td><b>' . number_format(compat_num($estimasi_taksiran), 0, ',', '.') . '</td></B>

	 </tr>';
echo '<tr class="grey">
		<td colspan="3"><b>SHU TAHUN BERJALAN SETELAH PAJAK</td>
		<td><b>' . number_format(compat_num($shu1010_sb - $estimasi_taksiran ), 0, ',', '.') . '</td></B>
		<td><b>' . number_format(compat_num($shu1_sb), 0, ',', '.') . '</td></B>
		<td><b>' . number_format(compat_num($shu2_sb), 0, ',', '.') . '</td></B>
		<td><b>' . number_format(compat_num($shu3_sb), 0, ',', '.') . '</td></B>
		<td><b>' . number_format(compat_num($shu4_sb), 0, ',', '.') . '</td></B>
		<td><b>' . number_format(compat_num($shu5_sb), 0, ',', '.') . '</td></B>
		<td><b>' . number_format(compat_num($shu6_sb), 0, ',', '.') . '</td></B>
		<td><b>' . number_format(compat_num($shu7_sb), 0, ',', '.') . '</td></B>
		<td><b>' . number_format(compat_num($shu8_sb), 0, ',', '.') . '</td></B>
		<td><b>' . number_format(compat_num($shu9_sb), 0, ',', '.') . '</td></B>
		
		<td><b>' . number_format(compat_num($shu10_sb), 0, ',', '.') . '</td></B>
		<td><b>' . number_format(compat_num($shu11_sb), 0, ',', '.') . '</td></B>
		<td><b>' . number_format(compat_num($shu12_sb), 0, ',', '.') . '</td></B>
		<td><b>' . number_format(compat_num($shu13_sb), 0, ',', '.') . '</td></B>
<td><b>' . number_format(compat_num($shu14_sb), 0, ',', '.') . '</td></B>
<td><b>' . number_format(compat_num($shu15_sb), 0, ',', '.') . '</td></B>
<td><b>' . number_format(compat_num($shu16_sb), 0, ',', '.') . '</td></B>
		<td><b>' . number_format(compat_num($shu21_sb), 0, ',', '.') . '</td></B>
		<td><b>' . number_format(compat_num($shu22_sb), 0, ',', '.') . '</td></B>
		<td><b>' . number_format(compat_num($shu23_sb), 0, ',', '.') . '</td></B>
		<td><b>' . number_format(compat_num($shu24_sb), 0, ',', '.') . '</td></B>
		<td><b>' . number_format(compat_num($shu25_sb), 0, ',', '.') . '</td></B>
		<td><b>' . number_format(compat_num($shu26_sb), 0, ',', '.') . '</td></B>
		<td><b>' . number_format(compat_num($shu27_sb), 0, ',', '.') . '</td></B>
		<td><b>' . number_format(compat_num($shu28_sb), 0, ',', '.') . '</td></B>
		<td><b>' . number_format(compat_num($shu29_sb), 0, ',', '.') . '</td></B>
		<td><b>' . number_format(compat_num($shu210_sb), 0, ',', '.') . '</td></B>
		
		<td><b>' . number_format(compat_num($shu211_sb), 0, ',', '.') . '</td></B>
		<td><b>' . number_format(compat_num($shu212_sb), 0, ',', '.') . '</td></B>
		<td><b>' . number_format(compat_num($shu213_sb), 0, ',', '.') . '</td></B>
		<td><b>' . number_format(compat_num($shu214_sb), 0, ',', '.') . '</td></B>
		<td><b>' . number_format(compat_num($shu215_sb), 0, ',', '.') . '</td></B>
		<td><b>' . number_format(compat_num($shu216_sb), 0, ',', '.') . '</td></B>
		<td><b>' . number_format(compat_num($shu217_sb), 0, ',', '.') . '</td></B>
		<td><b>' . number_format(compat_num($shu218_sb), 0, ',', '.') . '</td></B>
		<td><b>' . number_format(compat_num($shu219_sb), 0, ',', '.') . '</td></B>
		<td><b>' . number_format(compat_num($shu220_sb), 0, ',', '.') . '</td></B>
		
		<td><b>' . number_format(compat_num($shu221_sb), 0, ',', '.') . '</td></B>
		<td><b>' . number_format(compat_num($shu222_sb), 0, ',', '.') . '</td></B>
		<td><b>' . number_format(compat_num($shu223_sb), 0, ',', '.') . '</td></B>
		<td><b>' . number_format(compat_num($shu224_sb), 0, ',', '.') . '</td></B>
		<td><b>' . number_format(compat_num($shu225_sb), 0, ',', '.') . '</td></B>
<td><b>' . number_format(compat_num($shu226_sb), 0, ',', '.') . '</td></B>
<td><b>' . number_format(compat_num($shu227_sb), 0, ',', '.') . '</td></B>
<td><b>' . number_format(compat_num($shu228_sb), 0, ',', '.') . '</td></B>
<td><b>' . number_format(compat_num($shu229_sb), 0, ',', '.') . '</td></B>
<td><b>' . number_format(compat_num($shu230_sb), 0, ',', '.') . '</td></B>
<td><b>' . number_format(compat_num($shu231_sb), 0, ',', '.') . '</td></B>
<td><b>' . number_format(compat_num($shu232_sb), 0, ',', '.') . '</td></B>
<td><b>' . number_format(compat_num($shu233_sb), 0, ',', '.') . '</td></B>
<td><b>' . number_format(compat_num($shu234_sb), 0, ',', '.') . '</td></B>
<td><b>' . number_format(compat_num($shu235_sb), 0, ',', '.') . '</td></B>
<td><b>' . number_format(compat_num($shu236_sb), 0, ',', '.') . '</td></B>
<td><b>' . number_format(compat_num($shu237_sb), 0, ',', '.') . '</td></B>
<td><b>' . number_format(compat_num($shu238_sb), 0, ',', '.') . '</td></B>
<td><b>' . number_format(compat_num($shu239_sb), 0, ',', '.') . '</td></B>
<td><b>' . number_format(compat_num($shu240_sb), 0, ',', '.') . '</td></B>
<td><b>' . number_format(compat_num($shu241_sb), 0, ',', '.') . '</td></B>
<td><b>' . number_format(compat_num($shu242_sb), 0, ',', '.') . '</td></B>
<td><b>' . number_format(compat_num($shu243_sb), 0, ',', '.') . '</td></B>
<td><b>' . number_format(compat_num($shu244_sb), 0, ',', '.') . '</td></B>

<td><b>' . number_format(compat_num($shu3000_sb), 0, ',', '.') . '</td></B>

<td><b>' . number_format(compat_num($shu3001_sb), 0, ',', '.') . '</td></B>
<td><b>' . number_format(compat_num($shu3002_sb), 0, ',', '.') . '</td></B>
<td><b>' . number_format(compat_num($shu3003_sb), 0, ',', '.') . '</td></B>
<td><b>' . number_format(compat_num($shu3004_sb), 0, ',', '.') . '</td></B>
<td><b>' . number_format(compat_num($shu3005_sb), 0, ',', '.') . '</td></B>

<td><b>' . number_format(compat_num($shu4000_sb), 0, ',', '.') . '</td></B>

<td><b>' . number_format(compat_num($shu4001_sb), 0, ',', '.') . '</td></B>
<td><b>' . number_format(compat_num($shu4002_sb), 0, ',', '.') . '</td></B>
<td><b>' . number_format(compat_num($shu4003_sb), 0, ',', '.') . '</td></B>

		
			<<td align="right"><b>' . number_format(compat_num($shu_bersih), 0, ',', '.') . '</td></B>
	 </tr>';	 	 
    echo '</tbody>';
    echo '</table>';
	

}
else
{
    echo '<p>Gagal menjalankan query. Aplikasi dihentikan</p>';
}
 
//tutup koneksi
 
echo '</body>';
echo '</html>';
 
?>
