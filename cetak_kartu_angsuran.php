<?php require_once __DIR__ . '/php8_compat.php';
session_start();
ob_start();
    $userid=$_SESSION['userid'];
include "conn2.php";
include "function.php";


$koneksi=open_connection2();
$norek=$_GET['code_kel'];

$pilih=$_GET['jenis'];



$data = mysql_query ("select kode_branch from user where userid ='$userid'");
$r = mysql_fetch_array($data);
$branch=$r['kode_branch'];
$nama_brnach = mysql_query ("select * from branch where kode_branch ='$branch'");
$r = mysql_fetch_array($nama_brnach);
$nama_unit=$r['unit'];
$alamat=$r['alamat'];

$sql1=mysql_query("Select nama,kode_branch FROM user WHERE userid='$userid'");
$dsmo= mysql_fetch_array($sql1);
$kode_branch=$dsmo['kode_branch'];



$datacif = mysql_query ("select cif from anggota where cif ='$norek'");
$d = mysql_fetch_array($datacif);
$cif=$d['cif'];
$data_loan = mysql_query ("select * from loan where cif ='$cif'");
while($data_l= mysql_fetch_array($data_loan)){
$os=number_format(compat_num($data_l['os']));

$plafond=number_format(compat_num($data_l['Plafond']));
$angsuran=$data_l['angsuran'];
$pokok=number_format(compat_num($data_l['pokok']));
$margin=number_format(compat_num($data_l['ijaroh']));
$tgl_akad=tgl_indo($data_l['tgl_akad']);
$tenor=$data_l['Tenor'];
$no_anggota=$data_l['no_anggota'];
$CUST_SHORT_NAME=$data_l['Cust_Short_name'];
$maturity_date=tgl_indo($data_l['maturity_date']);
}




require ('fpdf17/fpdf.php');

  switch($pilih)
    {
     case "01":

$sql3 =mysql_query ("select CUST_SHORT_NAME,id_pinjam,bulat,angsuran_pokok,bunga,tgl_jatuh_tempo,tgl_bayar,cicilan from pinjaman_detail
inner join loan on loan.no_anggota=pinjaman_detail.id_pinjam
where loan.cif='$norek' and tenor <=tenor order by cicilan");

$datapokok = mysql_query ("select SUM(KREDIT - DEBIT) as spokok from simpanan_pokok where cif='$cif'");
$sp = mysql_fetch_array($datapokok);
$spokok=number_format(compat_num($sp['spokok']));

$datawajib = mysql_query ("select SUM(KREDIT - DEBIT) as swajib from simpanan_wajib where cif='$cif'");
$sw = mysql_fetch_array($datawajib);
$swajib=number_format(compat_num($sw['swajib']));

$datas = mysql_query ("select SUM(KREDIT - DEBIT) as saldo from simpanan where cif='$cif' and type not in('04','05')");
$s = mysql_fetch_array($datas);
$simpanan=number_format(compat_num($s['saldo']));

$pdf=new FPDF('P','mm', array(216, 275));
$pdf->AddPage();
//$pdf=new FPDF('L','mm', array(216, 330));
//$pdf->setX(11);
$pdf->setFillColor(255,255,255);
$pdf->SetFont('Arial','B',9);
$pdf->SetFont('Arial','B',8);
$pdf->multicell(0,5,'KOPERASI SIMPAN PINJAM DAN PEMBIAYAAN SYARIAH NUR INSANI UNIT '.$nama_unit.'',0,'J',1);
$pdf->multicell(0,5,'ALAMAT '.$alamat.'',0,'J',1);
$pdf->SetFont('Arial','BU',8);
$pdf->multicell(0,5,'DAFTAR KARTU ANGSURAN DAN TABUNGAN',0,'J',1);
$pdf->Ln(1);
$pdf->SetFont('Arial','',8);
$pdf->cell(70,5,'Nomor Rekening',0,'R',1);
$pdf->cell(1,5,':',0,'L',1);
$pdf->cell(9,5,'',0,'L',1);
$pdf->cell(15,5,''.$norek,0,'J',1);
$pdf->Ln(5);
$pdf->cell(70,5,'Nama ',0,'L',1);
$pdf->cell(1,5,':',0,'L',1);
$pdf->cell(9,5,'',0,'L',1);
$pdf->cell(15,5,''.$CUST_SHORT_NAME,0,'L',1);
$pdf->Ln(5);
$pdf->cell(70,5,'Plafond Pembiayaan',0,'R',1);
$pdf->cell(1,5,':',0,'R',1);
$pdf->cell(9,5,'',0,'R',1);
$pdf->cell(15,5,''.$plafond,0,'R',1);
$pdf->Ln(5);
$pdf->cell(70,5,'Tanggal Realisasi ',0,'R',1);
$pdf->cell(1,5,':',0,'R',1);
$pdf->cell(9,5,'',0,'R',1);
$pdf->cell(15,5,''.$tgl_akad,0,'R',1);
$pdf->Ln(5);
$pdf->cell(70,5,'Janga Waktu ',0,'R',1);
$pdf->cell(1,5,':',0,'R',1);
$pdf->cell(9,5,'',0,'R',1);
$minggu=' Minggu';
$pdf->cell(15,5,''.$tenor .$minggu,0,'R',1);
$pdf->Ln(5);
$pdf->cell(70,5,'Tanggal Jatuh Tempo ',0,'R',1);
$pdf->cell(1,5,':',0,'R',1);
$pdf->cell(9,5,'',0,'R',1);
$pdf->cell(15,5,''.$maturity_date,0,'R',1);
$pdf->Ln(5);
$pdf->cell(70,5,'Simpanan Pokok ',0,'R',1);
$pdf->cell(1,5,':',0,'R',1);
$pdf->cell(9,5,'',0,'R',1);
$pdf->cell(15,5,''.$spokok,0,'R',1);
$pdf->Ln(5);
$pdf->cell(70,5,'Simpanan Wajib ',0,'R',1);
$pdf->cell(1,5,':',0,'R',1);
$pdf->cell(9,5,'',0,'R',1);
$pdf->cell(15,5,''.$swajib,0,'R',1);
$pdf->Ln(5);
//$pdf->cell(70,5,'Simpanan Wadiah ',0,'R',1);
//$pdf->cell(1,5,':',0,'R',1);
//$pdf->cell(9,5,'',0,'R',1);
//$pdf->cell(15,5,''.$simpanan,0,'R',1);


$pdf->SetFont('ARIAL','B',8);


$pdf->Cell(12,10,'KE',1,0,'C');
$pdf->Cell(26,10,'Tgl Transaksi',1,0,'C');
$pdf->Cell(22,10,'setoran',1,0,'C');
$pdf->Cell(20,10,'Angsuran',1,0,'C');
$pdf->Cell(60,5,'TABUNGAN',1,0,'C');
$pdf->Cell(20,5,'Paraf',1,0,'C');
$pdf->Ln();
$pdf->Cell(30,5,'',0,0,'C');
$pdf->Cell(50,5,'',0,0,'C');
$pdf->Cell(20,5,'Pencairan',1,0,'C');
$pdf->Cell(20,5,'wajib',1,0,'C');
$pdf->Cell(20,5,'Saldo',1,0,'C');
$pdf->Cell(20,5,'Anggota',1,0,'C');
$pdf->Ln();
$no3 =0;
while($result3= mysql_fetch_array($sql3)){
$no3 ++;
$id_pinjam = $result3['id_pinjam'];
$CUST_SHORT_NAME = $result3['CUST_SHORT_NAME'];
$tgl_jatuh_tempo = $result3['tgl_jatuh_tempo'];
$angsuran_pokok = $result3['angsuran_pokok'];
$bunga = $result3['bunga'];
$jumlah_bayar=$result3['jumlah_bayar'];
$tgl_bayar = $result3['tgl_bayar'];
$cicilan = $result3['cicilan'];
$bulat= $result3['bulat'];
$angsuran=$angsuran_pokok  + $bunga +2500;
$angsurann=$angsuran_pokok  + $bunga;
$mingguan=$bulat-$angsurann-2500;

//query dan arraying
$pdf->SetFont('Arial','',8);
$pdf->Cell(12,5,''.$cicilan,1,'L',1);
$pdf->Cell(26,5,'',1,'L',1);
$pdf->Cell(22,5,''.number_format (compat_num($bulat)),1,'LR',0,'C');
$pdf->Cell(20,5,''.number_format (compat_num($angsuran)),1,'LR',0,'C');
$pdf->Cell(20,5,'',1,'LR',0,'R');
$pdf->Cell(20,5,''.number_format (compat_num($mingguan)),1,'LR',0,'R');
$pdf->Cell(20,5,'',1,'LR',0,'R');
$pdf->Cell(20,5,'',1,'LR',0,'R');
$pdf->Ln();


}  
$pdf->Ln(1); 
$pdf->SetFont('Arial','BU',7);
$pdf->multicell(0,5,'INFORMASI',0,'J',1);
$pdf->SetFont('Arial','B',6);
$w=95;
$startX=$pdf->GetX();
$startY=$pdf->GetY();

$pdf->multicell($w,5,'1. Pada Angsuran Ke 17',0,'J',1);
$pdf->SetFont('Arial','',6);
$pdf->multicell($w,5,'  Sosialisasi aturan kembali dan dokumen persyaratan yang harus di lengkapi/dibawa oleh anggota kemudian di serahkan kepada petugas KSPPS NURINSANI',0,'J',1);
$pdf->multicell($w,5,'  1. Formulir pengajuan pembiayaan yang di sediakan oleh petugas',0,'J',1);
$pdf->multicell($w,5,'  2. KTP pemohon dan penjamin Anggota',0,'J',1);
$pdf->multicell($w,5,'  3. Kartu keluarga pemohon dan penjamin',0,'J',1);
$pdf->multicell($w,5,'  4. Dokumen tambahan (jika dipersyaratkan)',0,'J',1);
$endY_col1=$pdf->GetY();

$pdf->SetLeftMargin($startX + $w);
$pdf->SetY($startY);
$pdf->SetFont('Arial','B',6);
$pdf->multicell(0,5,'2. Pada Angsuran Ke 18',0,'J',1);
$pdf->SetFont('Arial','',6);
$pdf->multicell($w,5,'  Melakukan Survey ke rumah anggota memperoleh informasi akurat mengenai',0,'J',1);
$pdf->multicell($w,5,'  1. Kondisi kesehatan, ekonomi(usaha) anggota dan persetujuan penjamin ',0,'J',1);
$pdf->multicell($w,5,'  2. Memastikan pembiayaan yang diberikan sesuai kebutuhan dan kemampuan bayar',0,'J',1);
$pdf->multicell($w,5,'  3. Memastikan anggota berkomitmen pembiayaan dipakai sendiri ',0,'J',1);

$endY_col2 = $pdf->GetY();
$pdf->SetLeftMargin($startX);

$pdf->SetY(max($end_col1, $endY_col2));
$pdf->ln(9);

$pdf->SetFont('Arial','BUI',7);
$pdf->multicell(0,5,'Catatan : Untuk Anggota dan atau Kelompok yang pembayaran angsuran dan tabungannya lancar serta disiplin  ',0,'J',1);
$pdf->Ln();

$pdf->Output();
  break;
	case "02":

$pdf=new FPDF ('P','mm','A4');

$pdf->addPage();

$pot = mysql_query("select * from loan where code_kel='$norek'  ");

$page=0;
while($p = mysql_fetch_array($pot)){
$page++;
$nama=$p['Cust_Short_name'];
$norekk=$p['no_anggota'];
$bulat=$p['bulat'];
$tgl_jatuh_tempo=$p['tgl_jatuh_tempo'];
$pla=number_format (compat_num($p['Plafond']));
$cif=$p['cif'];
$tgl_akad=tgl_indo($p['tgl_akad']);
$maturity_date=tgl_indo($p['maturity_date']);
$tenor =$p['Tenor'];
$angsuran =number_format (compat_num($p['angsuran']));

$untung =$p['bagi_hasil'];
$margin = number_format (compat_num($p['bagi_hasil']));


$pot2 =mysql_query ("select id_pinjam,angsuran_pokok,bunga,tgl_jatuh_tempo,tgl_bayar,cicilan from pinjaman_detail
where id_pinjam='$norekk' order by cicilan");


//$pot2 =mysql_query ("select CUST_SHORT_NAME,id_pinjam,bulat,angsuran_pokok,bunga,tgl_jatuh_tempo,tgl_bayar,cicilan from pinjaman_detail
//inner join loan on loan.cif=pinjaman_detail.cif
//where loan.cif='$cif' order by cicilan");

$datapokok = mysql_query ("select SUM(KREDIT - DEBIT) as spokok from simpanan_pokok where cif='$cif'");
$sp = mysql_fetch_array($datapokok);
$spokok=number_format(compat_num($sp['spokok']));

$datawajib = mysql_query ("select SUM(KREDIT - DEBIT) as swajib from simpanan_wajib where cif='$cif'");
$sw = mysql_fetch_array($datawajib);
$swajib=number_format(compat_num($sw['swajib']));

$datas = mysql_query ("select SUM(KREDIT - DEBIT) as saldo from simpanan where cif='$cif' and type not in('04','05')");
$s = mysql_fetch_array($datas);
$simpanan=number_format(compat_num($s['saldo']));

 do {

$pdf->setX(11);
$pdf->setFillColor(255,255,255);
$pdf->SetFont('Arial','B',9);
$pdf->SetFont('Arial','B',8);
$pdf->multicell(0,5,'KOPERASI SIMPAN PINJAM DAN PEMBIAYAAN  SYARIAH NUR INSANI UNIT '.$nama_unit.'',0,'J',1);
$pdf->multicell(0,5,'ALAMAT '.$alamat.'',0,'J',1);
$pdf->SetFont('Arial','BU',8);
$pdf->multicell(0,5,'DAFTAR KARTU ANGSURAN DAN TABUNGAN',0,'J',1);
$pdf->Ln(2);
$pdf->SetFont('Arial','',8);
$pdf->cell(70,5,'Nomor Rekening',0,'R',1);
$pdf->cell(1,5,':',0,'L',1);
$pdf->cell(9,5,'',0,'L',1);
$pdf->cell(15,5,''.$norekk,0,'J',1);
$pdf->Ln(5);
$pdf->cell(70,5,'Nama ',0,'L',1);
$pdf->cell(1,5,':',0,'L',1);
$pdf->cell(9,5,'',0,'L',1);
$pdf->cell(15,5,''.$nama,0,'L',1);
$pdf->Ln(5);
$pdf->cell(70,5,'Plafond Pembiayaan',0,'R',1);
$pdf->cell(1,5,':',0,'R',1);
$pdf->cell(9,5,'',0,'R',1);
$pdf->cell(15,5,''.$pla,0,'R',1);
$pdf->Ln(5);
$pdf->cell(70,5,'Tanggal Realisasi ',0,'R',1);
$pdf->cell(1,5,':',0,'R',1);
$pdf->cell(9,5,'',0,'R',1);
$pdf->cell(15,5,''.$tgl_akad,0,'R',1);
$pdf->Ln(5);
$pdf->cell(70,5,'Janga Waktu ',0,'R',1);
$pdf->cell(1,5,':',0,'R',1);
$pdf->cell(9,5,'',0,'R',1);
$minggu=' Minggu';
$pdf->cell(15,5,''.$tenor .$minggu,0,'R',1);
$pdf->Ln(5);
$pdf->cell(70,5,'Tanggal Jatuh Tempo ',0,'R',1);
$pdf->cell(1,5,':',0,'R',1);
$pdf->cell(9,5,'',0,'R',1);
$pdf->cell(15,5,''.$maturity_date,0,'R',1);
$pdf->Ln(5);
$pdf->cell(70,5,'Simpanan Pokok ',0,'R',1);
$pdf->cell(1,5,':',0,'R',1);
$pdf->cell(9,5,'',0,'R',1);
$pdf->cell(15,5,''.$spokok,0,'R',1);
$pdf->Ln(5);
$pdf->cell(70,5,'Simpanan Wajib ',0,'R',1);
$pdf->cell(1,5,':',0,'R',1);
$pdf->cell(9,5,'',0,'R',1);
$pdf->cell(15,5,''.$swajib,0,'R',1);
$pdf->Ln(5);
$pdf->cell(70,5,'Simpanan Wadiah ',0,'R',1);
$pdf->cell(1,5,':',0,'R',1);
$pdf->cell(9,5,'',0,'R',1);
$pdf->cell(15,5,''.$simpanan,0,'R',1);
$pdf->Ln(4);
$pdf->SetFont('ARIAL','B',8);


$pdf->Cell(12,10,'KE',1,0,'C');
$pdf->Cell(26,10,'Tgl Transaksi',1,0,'C');
$pdf->Cell(22,10,'setoran',1,0,'C');
$pdf->Cell(20,10,'Angsuran',1,0,'C');
$pdf->Cell(60,5,'TABUNGAN',1,0,'C');
$pdf->Cell(20,5,'Paraf',1,0,'C');
$pdf->Ln();
$pdf->Cell(30,5,'',0,0,'C');
$pdf->Cell(50,5,'',0,0,'C');
$pdf->Cell(20,5,'Pencairan',1,0,'C');
$pdf->Cell(20,5,'wajib',1,0,'C');
$pdf->Cell(20,5,'Saldo',1,0,'C');
$pdf->Cell(20,5,'Anggota',1,0,'C');


$pdf->Ln();
$no3 =0;
while($result3= mysql_fetch_array($pot2)){
$no3 ++;
$id_pinjam = $result3['id_pinjam'];
$CUST_SHORT_NAME = $result3['CUST_SHORT_NAME'];
$tgl_jatuh_tempo = $result3['tgl_jatuh_tempo'];
$angsuran_pokok = $result3['angsuran_pokok'];
$bunga = $result3['bunga'] ;
$jumlah_bayar=$result3['jumlah_bayar'];
$tgl_bayar = $result3['tgl_bayar'];
$cicilan = $result3['cicilan'];
//$bulat= $result3['bulat'];
$angsuran=$angsuran_pokok  + $bunga +2500;
$angsurann=$angsuran_pokok  + $bunga;
$mingguan=$bulat-$angsurann-2500;


//query dan arraying
$pdf->SetFont('Arial','',8);
$pdf->Cell(12,5,''.$cicilan,1,'L',1);
$pdf->Cell(26,5,'',1,'L',1);
$pdf->Cell(22,5,''.number_format (compat_num($bulat)),1,'LR',0,'C');
$pdf->Cell(20,5,''.number_format (compat_num($angsuran)),1,'LR',0,'C');
$pdf->Cell(20,5,'',1,'LR',0,'R');
$pdf->Cell(20,5,''.number_format (compat_num($mingguan)),1,'LR',0,'R');
$pdf->Cell(20,5,'',1,'LR',0,'R');
$pdf->Cell(20,5,'',1,'LR',0,'R');
$pdf->Ln();

}
if($tenor==25){
$pdf->Ln(5); 
$pdf->SetFont('Arial','BU',7);
$pdf->multicell(0,5,'INFORMASI',0,'J',1);
$pdf->SetFont('Arial','B',6);
$w=90;
$startX=$pdf->GetX();
$startY=$pdf->GetY();

$pdf->multicell($w,5,'1. Pada Angsuran Ke 17',0,'J',1);
$pdf->SetFont('Arial','',6);
$pdf->multicell($w,5,'  Sosialisasi aturan kembali dan dokumen persyaratan yang harus di lengkapi/dibawa oleh anggota kemudian di serahkan kepada petugas KSPPS NURINSANI',0,'J',1);
$pdf->multicell($w,5,'  1. Formulir pengajuan pembiayaan yang di sediakan oleh petugas',0,'J',1);
$pdf->multicell($w,5,'  2. KTP pemohon dan penjamin Anggota',0,'J',1);
$pdf->multicell($w,5,'  3. Kartu keluarga pemohon dan penjamin',0,'J',1);
$pdf->multicell($w,5,'  4. Dokumen tambahan (jika dipersyaratkan)',0,'J',1);
$endY_col1=$pdf->GetY();

$pdf->SetLeftMargin($startX + $w);
$pdf->SetY($startY);
$pdf->SetFont('Arial','B',6);
$pdf->multicell(0,5,'2. Pada Angsuran Ke 18',0,'J',1);
$pdf->SetFont('Arial','',6);
$pdf->multicell($w,5,'  Melakukan Survey ke rumah anggota memperoleh informasi akurat mengenai',0,'J',1);
$pdf->multicell($w,5,'  1. Kondisi kesehatan, ekonomi(usaha) anggota dan persetujuan penjamin ',0,'J',1);
$pdf->multicell($w,5,'  2. Memastikan pembiayaan yang diberikan sesuai kebutuhan dan kemampuan bayar',0,'J',1);
$pdf->multicell($w,5,'  3. Memastikan anggota berkomitmen pembiayaan dipakai sendiri ',0,'J',1);

$endY_col2 = $pdf->GetY();
$pdf->SetLeftMargin($startX);

$pdf->SetY(max($end_col1, $endY_col2));
$pdf->ln(9);

$pdf->SetFont('Arial','BUI',7);
$pdf->multicell(0,5,'Catatan : Untuk Anggota dan atau Kelompok yang pembayaran angsuran dan tabungannya lancar serta disiplin  ',0,'J',1);
$pdf->Ln();
$pdf->Ln(20);
}else{
$pdf->Ln(5); 
$pdf->SetFont('Arial','BU',7);
$pdf->multicell(0,5,'INFORMASI',0,'J',1);
$pdf->SetFont('Arial','B',6);

$w=0;
$pdf->multicell($w,5,'1. Pada Angsuran Ke 17',0,'J',1);
$pdf->SetFont('Arial','',6);
$pdf->multicell($w,5,'  Sosialisasi aturan kembali dan dokumen persyaratan yang harus di lengkapi/dibawa oleh anggota kemudian di serahkan kepada petugas KSPPS NURINSANI',0,'J',1);
$pdf->multicell($w,5,'  1. Formulir pengajuan pembiayaan yang di sediakan oleh petugas',0,'J',1);
$pdf->multicell($w,5,'  2. KTP pemohon dan penjamin Anggota',0,'J',1);
$pdf->multicell($w,5,'  3. Kartu keluarga pemohon dan penjamin',0,'J',1);
$pdf->multicell($w,5,'  4. Dokumen tambahan (jika dipersyaratkan)',0,'J',1);

$pdf->SetFont('Arial','B',6);
$pdf->multicell(0,5,'2. Pada Angsuran Ke 18',0,'J',1);
$pdf->SetFont('Arial','',6);
$pdf->multicell($w,5,'  Melakukan Survey ke rumah anggota memperoleh informasi akurat mengenai',0,'J',1);
$pdf->multicell($w,5,'  1. Kondisi kesehatan, ekonomi(usaha) anggota dan persetujuan penjamin ',0,'J',1);
$pdf->multicell($w,5,'  2. Memastikan pembiayaan yang diberikan sesuai kebutuhan dan kemampuan bayar',0,'J',1);
$pdf->multicell($w,5,'  3. Memastikan anggota berkomitmen pembiayaan dipakai sendiri ',0,'J',1);



$pdf->ln(9);

$pdf->SetFont('Arial','BUI',7);
$pdf->multicell(0,5,'Catatan : Untuk Anggota dan atau Kelompok yang pembayaran angsuran dan tabungannya lancar serta disiplin  ',0,'J',1);
$pdf->Ln();
$pdf->Ln(202);
}
}
 
while ($page<$feed);
}

$pdf->Output();
	
	
	 break;
     case "03":

        $pdf=new FPDF ('P','mm','A4');
        
        $pdf->addPage();
        
        $pot = mysql_query("select * from loan where code_kel='$norek' and status='CETAK' ");
        
        $page=0;
        while($p = mysql_fetch_array($pot)){
        $page++;
        $nama=$p['Cust_Short_name'];
        $norekk=$p['no_anggota'];
        $bulat=$p['bulat'];
        $tgl_jatuh_tempo=$p['tgl_jatuh_tempo'];
        $pla=number_format (compat_num($p['Plafond']));
        $cif=$p['cif'];
	$status_app=$p['status_app'];
        $tgl_akad=tgl_indo($p['tgl_akad']);
        $maturity_date=tgl_indo($p['maturity_date']);
        $tenor =$p['Tenor'];
        $angsuran =number_format (compat_num($p['angsuran']));
        
        $untung =$p['bagi_hasil'];
        $margin = number_format (compat_num($p['bagi_hasil']));
        
        
        $pot2 =mysql_query ("select id_pinjam,angsuran_pokok,bunga,tgl_jatuh_tempo,tgl_bayar,cicilan from pinjaman_detail_temp
        where id_pinjam='$norekk' order by cicilan");

	if($status_app=='MURAB'){
        $pot3 =mysql_query (" insert into pinjaman_detail select * from  pinjaman_detail_temp
        where id_pinjam='$norekk' order by cicilan");
	}else{
	        $pot3 =mysql_query (" insert into pinjaman_detail_mus 
	select id, id_pinjam, cicilan, angsuran_pokok,bunga as nisbah,tgl_jatuh_tempo,tgl_bayar,jumlah_bayar as nisbah_anggota,ket,cif,unit,ao,code_kel from  pinjaman_detail_temp_mus
        where id_pinjam='$norekk' order by cicilan");
	 $pot3 =mysql_query (" insert into pinjaman_detail select * from  pinjaman_detail_temp_mus
        where id_pinjam='$norekk' order by cicilan");
     $pot2 =mysql_query ("select id_pinjam,angsuran_pokok,bunga,tgl_jatuh_tempo,tgl_bayar,cicilan from pinjaman_detail_temp_mus
        where id_pinjam='$norekk' order by cicilan");
	}

$pot5 = mysql_query("delete from pinjaman_detail_temp_mus where id_pinjam='$norekk' ");

$pot5 = mysql_query("delete from pinjaman_detail_temp where id_pinjam='$norekk' ");






    

        
        
        //$pot2 =mysql_query ("select CUST_SHORT_NAME,id_pinjam,bulat,angsuran_pokok,bunga,tgl_jatuh_tempo,tgl_bayar,cicilan from pinjaman_detail
        //inner join loan on loan.cif=pinjaman_detail.cif
        //where loan.cif='$cif' order by cicilan");
        
        $datapokok = mysql_query ("select SUM(KREDIT - DEBIT) as spokok from simpanan_pokok where cif='$cif'");
        $sp = mysql_fetch_array($datapokok);
        $spokok=number_format(compat_num($sp['spokok']));
        
        $datawajib = mysql_query ("select SUM(KREDIT - DEBIT) as swajib from simpanan_wajib where cif='$cif'");
        $sw = mysql_fetch_array($datawajib);
        $swajib=number_format(compat_num($sw['swajib']));
        
        $datas = mysql_query ("select SUM(KREDIT - DEBIT) as saldo from simpanan where cif='$cif' and type not in('04','05')");
        $s = mysql_fetch_array($datas);
        $simpanan=number_format(compat_num($s['saldo']));
        
         do {
        
        $pdf->setX(11);
        $pdf->setFillColor(255,255,255);
        $pdf->SetFont('Arial','B',9);
        $pdf->SetFont('Arial','B',8);
        $pdf->multicell(0,5,'KOPERASI SIMPAN PINJAM DAN PEMBIAYAAN  SYARIAH NUR INSANI UNIT '.$nama_unit.'',0,'J',1);
        $pdf->multicell(0,5,'ALAMAT '.$alamat.'',0,'J',1);
        $pdf->SetFont('Arial','BU',8);
        $pdf->multicell(0,5,'DAFTAR KARTU ANGSURAN DAN TABUNGAN',0,'J',1);
        $pdf->Ln();
        $pdf->SetFont('Arial','',8);
        $pdf->cell(70,5,'Nomor Rekening',0,'R',1);
        $pdf->cell(1,5,':',0,'L',1);
        $pdf->cell(9,5,'',0,'L',1);
        $pdf->cell(15,5,''.$norekk,0,'J',1);
        $pdf->Ln(5);
        $pdf->cell(70,5,'Nama ',0,'L',1);
        $pdf->cell(1,5,':',0,'L',1);
        $pdf->cell(9,5,'',0,'L',1);
        $pdf->cell(15,5,''.$nama,0,'L',1);
        $pdf->Ln(5);
        $pdf->cell(70,5,'Plafond Pembiayaan',0,'R',1);
        $pdf->cell(1,5,':',0,'R',1);
        $pdf->cell(9,5,'',0,'R',1);
        $pdf->cell(15,5,''.$pla,0,'R',1);
        $pdf->Ln(5);
        $pdf->cell(70,5,'Tanggal Realisasi ',0,'R',1);
        $pdf->cell(1,5,':',0,'R',1);
        $pdf->cell(9,5,'',0,'R',1);
        $pdf->cell(15,5,''.$tgl_akad,0,'R',1);
        $pdf->Ln(5);
        $pdf->cell(70,5,'Janga Waktu ',0,'R',1);
        $pdf->cell(1,5,':',0,'R',1);
        $pdf->cell(9,5,'',0,'R',1);
        $minggu=' Minggu';
        $pdf->cell(15,5,''.$tenor .$minggu,0,'R',1);
        $pdf->Ln(5);
        $pdf->cell(70,5,'Tanggal Jatuh Tempo ',0,'R',1);
        $pdf->cell(1,5,':',0,'R',1);
        $pdf->cell(9,5,'',0,'R',1);
        $pdf->cell(15,5,''.$maturity_date,0,'R',1);
        $pdf->Ln(5);
        $pdf->cell(70,5,'Simpanan Pokok ',0,'R',1);
        $pdf->cell(1,5,':',0,'R',1);
        $pdf->cell(9,5,'',0,'R',1);
        $pdf->cell(15,5,''.$spokok,0,'R',1);
        $pdf->Ln(5);
        $pdf->cell(70,5,'Simpanan Wajib ',0,'R',1);
        $pdf->cell(1,5,':',0,'R',1);
        $pdf->cell(9,5,'',0,'R',1);
        $pdf->cell(15,5,''.$swajib,0,'R',1);
        $pdf->Ln(5);
        $pdf->cell(70,5,'Simpanan Wadiah ',0,'R',1);
        $pdf->cell(1,5,':',0,'R',1);
        $pdf->cell(9,5,'',0,'R',1);
        $pdf->cell(15,5,''.$simpanan,0,'R',1);
	 $pdf->Ln(4);
               
        
        $pdf->SetFont('ARIAL','B',8);
        
        
        $pdf->Cell(12,10,'KE',1,0,'C');
        $pdf->Cell(26,10,'Tgl Transaksi',1,0,'C');
        $pdf->Cell(22,10,'setoran',1,0,'C');
        $pdf->Cell(20,10,'Angsuran',1,0,'C');
        $pdf->Cell(60,5,'TABUNGAN',1,0,'C');
        $pdf->Cell(20,5,'Paraf',1,0,'C');
        $pdf->Ln();
        $pdf->Cell(30,5,'',0,0,'C');
        $pdf->Cell(50,5,'',0,0,'C');
        $pdf->Cell(20,5,'Pencairan',1,0,'C');
        $pdf->Cell(20,5,'wajib',1,0,'C');
        $pdf->Cell(20,5,'Saldo',1,0,'C');
        $pdf->Cell(20,5,'Anggota',1,0,'C');
        
        
        $pdf->Ln();
        $no3 =0;
        while($result3= mysql_fetch_array($pot2)){
        $no3 ++;
        $id_pinjam = $result3['id_pinjam'];
        $CUST_SHORT_NAME = $result3['CUST_SHORT_NAME'];
        $tgl_jatuh_tempo = $result3['tgl_jatuh_tempo'];
        $angsuran_pokok = $result3['angsuran_pokok'];
        $bunga = $result3['bunga'];
        $jumlah_bayar=$result3['jumlah_bayar'];
        $tgl_bayar = $result3['tgl_bayar'];
        $cicilan = $result3['cicilan'];
        //$bulat= $result3['bulat'];
        $angsuran=$angsuran_pokok  + $bunga + 2500;
	$angsurann=$angsuran_pokok  + $bunga;
        $mingguan=$bulat-$angsurann - 2500;
        
        
        //query dan arraying
        $pdf->SetFont('Arial','',8);
        $pdf->Cell(12,5,''.$cicilan,1,'L',1);
        $pdf->Cell(26,5,'',1,'L',1);
        $pdf->Cell(22,5,''.number_format (compat_num($bulat)),1,'LR',0,'C');
        $pdf->Cell(20,5,''.number_format (compat_num($angsuran)),1,'LR',0,'C');
        $pdf->Cell(20,5,'',1,'LR',0,'R');
        $pdf->Cell(20,5,''.number_format (compat_num($mingguan)),1,'LR',0,'R');
        $pdf->Cell(20,5,'',1,'LR',0,'R');
        $pdf->Cell(20,5,'',1,'LR',0,'R');
        $pdf->Ln();
        
        }
if($tenor==25){
$pdf->Ln(5); 
$pdf->SetFont('Arial','BU',7);
$pdf->multicell(0,5,'INFORMASI',0,'J',1);
$pdf->SetFont('Arial','B',6);
$w=90;
$startX=$pdf->GetX();
$startY=$pdf->GetY();

$pdf->multicell($w,5,'1. Pada Angsuran Ke 17',0,'J',1);
$pdf->SetFont('Arial','',6);
$pdf->multicell($w,5,'  Sosialisasi aturan kembali dan dokumen persyaratan yang harus di lengkapi/dibawa oleh anggota kemudian di serahkan kepada petugas KSPPS NURINSANI',0,'J',1);
$pdf->multicell($w,5,'  1. Formulir pengajuan pembiayaan yang di sediakan oleh petugas',0,'J',1);
$pdf->multicell($w,5,'  2. KTP pemohon dan penjamin Anggota',0,'J',1);
$pdf->multicell($w,5,'  3. Kartu keluarga pemohon dan penjamin',0,'J',1);
$pdf->multicell($w,5,'  4. Dokumen tambahan (jika dipersyaratkan)',0,'J',1);
$endY_col1=$pdf->GetY();

$pdf->SetLeftMargin($startX + $w);
$pdf->SetY($startY);
$pdf->SetFont('Arial','B',6);
$pdf->multicell(0,5,'2. Pada Angsuran Ke 18',0,'J',1);
$pdf->SetFont('Arial','',6);
$pdf->multicell($w,5,'  Melakukan Survey ke rumah anggota memperoleh informasi akurat mengenai',0,'J',1);
$pdf->multicell($w,5,'  1. Kondisi kesehatan, ekonomi(usaha) anggota dan persetujuan penjamin ',0,'J',1);
$pdf->multicell($w,5,'  2. Memastikan pembiayaan yang diberikan sesuai kebutuhan dan kemampuan bayar',0,'J',1);
$pdf->multicell($w,5,'  3. Memastikan anggota berkomitmen pembiayaan dipakai sendiri ',0,'J',1);

$endY_col2 = $pdf->GetY();
$pdf->SetLeftMargin($startX);

$pdf->SetY(max($end_col1, $endY_col2));
$pdf->ln(9);

$pdf->SetFont('Arial','BUI',7);
$pdf->multicell(0,5,'Catatan : Untuk Anggota dan atau Kelompok yang pembayaran angsuran dan tabungannya lancar serta disiplin  ',0,'J',1);
$pdf->Ln();
$pdf->Ln(20);
}else{
$pdf->Ln(5); 
$pdf->SetFont('Arial','BU',7);
$pdf->multicell(0,5,'INFORMASI',0,'J',1);
$pdf->SetFont('Arial','B',6);

$w=0;
$pdf->multicell($w,5,'1. Pada Angsuran Ke 17',0,'J',1);
$pdf->SetFont('Arial','',6);
$pdf->multicell($w,5,'  Sosialisasi aturan kembali dan dokumen persyaratan yang harus di lengkapi/dibawa oleh anggota kemudian di serahkan kepada petugas KSPPS NURINSANI',0,'J',1);
$pdf->multicell($w,5,'  1. Formulir pengajuan pembiayaan yang di sediakan oleh petugas',0,'J',1);
$pdf->multicell($w,5,'  2. KTP pemohon dan penjamin Anggota',0,'J',1);
$pdf->multicell($w,5,'  3. Kartu keluarga pemohon dan penjamin',0,'J',1);
$pdf->multicell($w,5,'  4. Dokumen tambahan (jika dipersyaratkan)',0,'J',1);

$pdf->SetFont('Arial','B',6);
$pdf->multicell(0,5,'2. Pada Angsuran Ke 18',0,'J',1);
$pdf->SetFont('Arial','',6);
$pdf->multicell($w,5,'  Melakukan Survey ke rumah anggota memperoleh informasi akurat mengenai',0,'J',1);
$pdf->multicell($w,5,'  1. Kondisi kesehatan, ekonomi(usaha) anggota dan persetujuan penjamin ',0,'J',1);
$pdf->multicell($w,5,'  2. Memastikan pembiayaan yang diberikan sesuai kebutuhan dan kemampuan bayar',0,'J',1);
$pdf->multicell($w,5,'  3. Memastikan anggota berkomitmen pembiayaan dipakai sendiri ',0,'J',1);



$pdf->ln(9);

$pdf->SetFont('Arial','BUI',7);
$pdf->multicell(0,5,'Catatan : Untuk Anggota dan atau Kelompok yang pembayaran angsuran dan tabungannya lancar serta disiplin  ',0,'J',1);
$pdf->Ln();
$pdf->Ln(202);
}
}
 
        while ($page<$feed);
        }


        $pot = mysql_query("update loan set status='ANGGOTA' where code_kel='$norek' and status='CETAK' ");
    
        $pdf->Output();


            
            
             break;


      default:

}




?>
