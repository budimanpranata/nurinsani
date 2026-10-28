<?php require_once __DIR__ . '/php8_compat.php'; session_start();

ob_start();
$userid=$_SESSION['userid'];

//cek apakah user sudah login
if(!isset($_SESSION['userid'])){
   header("location:index.html");
 exit; }else{
	include "conn2.php";
	include "indo_tgl.php";
	$koneksi=open_connection2();
	$norek=$_GET['rekening'];
	//untuk core PDF
	require('fpdf17/fpdf.php');
	
	class PDF extends FPDF
	{
		function Header()
		{
			//Select Arial bold 15
			$this->SetFont('Arial','B',8);
			//Move to the right
			$this->Cell(80);
			//Framed title
			
			$judul='Rekening Koran';
			
			$this->Cell(50,10,$judul,0,0,'C');
			//Line break
			$this->Ln(20);
			
		}


		//Colored table
		function tabel_ri32_color()
		{
			$norek=$_GET['rekening'];
			//$trns=$_SESSION['pilih'];
			
			//Queri untuk Menampilkan data
			if ($trns='01'){
			$query1 = mysql_query("SET @bal = 0;");
				$query =mysql_query ("SELECT simpanan.KET, simpanan.NOREK,simpanan.BUSS_DATE,anggota.CUST_SHORT_NAME,DEBIT,KREDIT,@bal:=@bal+KREDIT-DEBIT as balance FROM simpanan 
				join anggota on anggota.NOREK = simpanan.NOREK
				where simpanan.NOREK='$norek' order by simpanan.BUSS_DATE");
			}
			elseif($trns='02'){
				$query1 = mysql_query("select * from pinjaman_detail where id_pinjam=''$norek");
				
			}
			else{
				
				
			}
			

			//Column titles
			if($trns='01'){
			$header=array('anana','Rekening','Nama','Keterangan','Tanggal','Debet','Kredit','Saldo');
			}elseif($trns='02'){
				$header=array('No','Rekening','Nama','Keterangan','Tanggal','Pokok','Margin','Saldo');
			}else{
				$header=array('No','Rekening','Nama','Keterangan','Tanggal','Debet','Kredit','Saldo');
			}
			//Colors, line width and bold font
			$this->SetFillColor(0,255,0);
			$this->SetTextColor(255);
			$this->SetDrawColor(128,0,0);
			$this->SetLineWidth(.1);
			$this->SetFont('','B');
			
			//Title table
			//$this->Cell(20,30,'Title',1,0,'C');
			
			//Header
			$w=array(8,25,38,42,30,18,18,18);
			for($i=0;$i<count($header);$i++)
				$this->Cell($w[$i],3,$header[$i],1,0,'C',true);
			$this->Ln();
			
			//Color and font restoration
			$this->SetFillColor(210,235,255);
			$this->SetTextColor(0);
			$this->SetFont('');
			
			//Data
			$fill=false;
			
			//$this->Cell(-10,-20,'Enjoy new fonts with FPDF!');
			
			if($trns='01'){
			$no=0;
			while($data = mysql_fetch_array($query))
			{
				$no ++;
				$this->Cell($w[0],3,$no,'LR',0,'C',$fill);
				$this->Cell($w[1],3,$data['NOREK'],'LR',0,'C',$fill);
				$this->Cell($w[2],3,$data['CUST_SHORT_NAME'],'LR',0,'L',$fill);
				$this->Cell($w[3],3,$data['KET'],'LR',0,'L',$fill);
				$this->Cell($w[4],3,tgl_indo ($data['BUSS_DATE']),'LR',0,'L',$fill);
				$this->Cell($w[5],3,number_format (compat_num($data['DEBIT'])),'LR',0,'R',$fill);
				$this->Cell($w[6],3,number_format (compat_num($data['KREDIT'])),'LR',0,'R',$fill);
				$this->Cell($w[7],3,number_format (compat_num($data['balance'])),'LR',0,'R',$fill);
				$this->Ln();
				$fill=!$fill;
				}
			}
			elseif($trns='02'){
			$no=0;
			while($data = mysql_fetch_array($query))
			{
				$no ++;
				$this->Cell($w[0],3,$no,'LR',0,'C',$fill);
				$this->Cell($w[1],3,$data['id_pinjam'],'LR',0,'C',$fill);
				$this->Cell($w[2],3,$data['CUST_SHORT_NAME'],'LR',0,'L',$fill);
				$this->Cell($w[3],3,$data['KET'],'LR',0,'L',$fill);
				$this->Cell($w[4],3,tgl_indo ($data['tgl_jatuh_tempo']),'LR',0,'L',$fill);
				$this->Cell($w[5],3,number_format (compat_num($data['pokok'])),'LR',0,'R',$fill);
				$this->Cell($w[6],3,number_format (compat_num($data['bunga'])),'LR',0,'R',$fill);
				$this->Cell($w[7],3,number_format (compat_num($data['balance'])),'LR',0,'R',$fill);
				$this->Ln();
				$fill=!$fill;
				}
			}else{
			$no=0;
			while($data = mysql_fetch_array($query))
			{
				$no ++;
				$this->Cell($w[0],3,$no,'LR',0,'C',$fill);
				$this->Cell($w[1],3,$data['NOREK'],'LR',0,'C',$fill);
				$this->Cell($w[2],3,$data['CUST_SHORT_NAME'],'LR',0,'L',$fill);
				$this->Cell($w[3],3,$data['KET'],'LR',0,'L',$fill);
				$this->Cell($w[4],3,tgl_indo ($data['BUSS_DATE']),'LR',0,'L',$fill);
				$this->Cell($w[5],3,number_format (compat_num($data['DEBIT'])),'LR',0,'R',$fill);
				$this->Cell($w[6],3,number_format (compat_num($data['KREDIT'])),'LR',0,'R',$fill);
				$this->Cell($w[7],3,number_format (compat_num($data['balance'])),'LR',0,'R',$fill);
				$this->Ln();
				$fill=!$fill;
			}
			}
			
			
			$this->Cell(array_sum($w),3,'Copyright (c) 2017 Nurinsani','T');
		}
	}
	
	$pdf=new PDF();
	$title='Nomor Perkiraan';
	$pdf->SetTitle($title);
	$pdf->SetAuthor('Budiman Pranaya');
	
	$pdf->SetFont('Arial','',7);
	$pdf->AddPage();
	//memanggil fungsi table
	$pdf->tabel_ri32_color();
	$pdf->Output();
	

}
unset($_SESSION['pilih']);
?>
