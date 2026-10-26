<?php require_once __DIR__ . '/koneksi_db.php'; 
error_reporting(1);
date_default_timezone_set('Asia/Jakarta');

$con = db_ni();
if (mysqli_connect_errno()) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
    exit;
  }
 
// Turn autocommit off
mysqli_autocommit($con,FALSE);
 
 
include "function.php";
$tgl_wakalah ="2024-05-27";
 
var_dump($tgl_wakalah);

 $cek= db_fetch_array(db_query($con,"select param_tgl from param_tgl WHERE nama_param_tgl ='EOD_MOB'"));
$tgl_wakalah = $cek['param_tgl']; 
		
			$kode_tr=5;
			$kode_tr=acakangkahuruf($kode_tr);	   
		     $data = db_query($con,"select DATE_ADD(tgl_wakalah,INTERVAL 7 DAY) as tgl_murab,maturity_date,calon_loan.buss_date,calon_loan.code_kel,calon_loan.no_anggota,calon_loan.Cust_Short_name,status_app,
			calon_loan.Deal_type,suffix,bagi_hasil,Tenor,Plafond,os,angsuran,pokok,ijaroh,bulat,ke,usaha,calon_loan.unit,tgl_wakalah,hari,calon_loan.cao,calon_loan.USERID,calon_loan.status,calon_loan.cif
			 from calon_loan  
			 left join anggota on anggota.cif = calon_loan.cif
			 WHERE calon_loan.status_app  in ('APPROVE','BATAL') and tgl_wakalah ='$tgl_wakalah'");
             while($row = db_fetch_array($data)){

  
			$no_anggota=$row['no_anggota'];
			$tgl_wakalah= $row['tgl_wakalah'];
			$cif=$row['cif'];
			$Plafond=$row['Plafond'];
			$bagi_hasil=$row['bagi_hasil'];
			$piutang=$Plafond + $bagi_hasil;
			$tgl_murab=$row['tgl_murab'];
			$buss_date=$row['buss_date'];
			$code_kel=$row['code_kel'];
			$Cust_Short_name=$row['Cust_Short_name'];
			$Deal_type=$row['Deal_type'];
			$suffix=$row['suffix'];
			$Tenor=$row['Tenor'];
			$maturity_date=$row['maturity_date'];
			$os=$row['os'];
			$angsuran=$row['angsuran'];
			$pokok=$row['pokok'];
			$ijaroh=$row['ijaroh'];
			$status_app=$row['status_app'];
			$unit=$row['unit'];
			
		
			if ($suffix=1){
				$ke=1;
			}else{
				$ke=((int)$suffix)+1;
			}

			if($status_app=="APPROVE"){
				$status_app = "MURAB";
			}else{
				$status_app="BATAL";
			}
						$USERID=$row['USERID'];
			$status='ANGGOTA';
			$norek="$no_anggota";
		
			$kode_transaksi="BU/".$unit .$cif;					
			$keterangan_transaksi="Realisasi Wakalah Unit $unit $cif";					
			@db_query ($con,"insert into jurnal_umum(kode_transaksi,tanggal_selesai,unit) values('$kode_transaksi','$tgl_wakalah','$unit')");

			//Dr aseet Murabahah		
			@db_query ($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
			values('$unit','$kode_transaksi','1431000','$tgl_wakalah','Bukti SYSTEM','$keterangan_transaksi','$Plafond','$USERID')");
					
			//cr aseet kas
			@db_query ($con,"insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
			values('$unit','$kode_transaksi','1120000','$tgl_wakalah','Bukti SYSTEM','$keterangan_transaksi','$Plafond','$USERID')");
			@db_query ($con,"update calon_loan set status_app='$status_app' where cif ='$cif'");

		}


		if (!mysqli_commit($con)) {
			echo "Transaksi Gagal commit Silahkan ulangi lagi";
			exit();
		  }
				echo '<script>alert("Wakalah sudah direalisasikan.");</script>';
    
			
		

?>
