<?php require_once __DIR__ . '/koneksi_db.php'; 
date_default_timezone_set('Asia/Jakarta');
session_start();
ob_start();
	$userid=$_SESSION['userid'];
	$tanggal=date("y-m-d");

db_ni();
mysql_select_db('nur_insani');
include "function.php";

 
			
$cekbox = $_POST['cekbox'];
if ($cekbox) {
       foreach ($cekbox as $value) {
	
		
			$kode_tr=5;
			$kode_tr=acakangkahuruf($kode_tr);	   
		     $row = mysql_fetch_array(mysql_query("select DATE_ADD(tgl_wakalah,INTERVAL 7 DAY) as tgl_murab,maturity_date,calon_loan.buss_date,calon_loan.code_kel,calon_loan.no_anggota,calon_loan.Cust_Short_name,
			calon_loan.Deal_type,suffix,bagi_hasil,Tenor,Plafond,os,angsuran,pokok,ijaroh,bulat,ke,usaha,calon_loan.unit,tgl_wakalah,hari,calon_loan.cao,calon_loan.USERID,calon_loan.status,calon_loan.cif
			 from calon_loan  
			 left join anggota on anggota.cif = calon_loan.cif
			 WHERE calon_loan.cif ='" . $value . "' ")) or die(mysql_error());
        //$row = mysql_fetch_array(mysql_query("select * from calon_loan  WHERE no_anggota ='" . $value . "'")) or die(mysql_error());
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
			$bulat=$row['bulat'];
		
			if ($suffix=1){
				$ke=1;
			}else{
				$ke=((int)$suffix)+1;
			}
			
			$usaha=$row['usaha'];
			$unit=$row['unit'];
			$hari=$row['hari'];
			$cao=$row['cao'];
			$USERID=$row['USERID'];
			$status='ANGGOTA';
			$norek="$no_anggota";
			//$awal_hari = $tgl_wakalah;
			//$akhir_hari = $maturity_date;
			//$x=0;
			//while (strtotime($awal_hari) < strtotime($akhir_hari)) {
			//$x ++;
			//$awal_hari= date ("Y-m-d", strtotime("+7 day", strtotime($awal_hari)));
			//$detail="INSERT DELAYED INTO pinjaman_detail (id,id_pinjam,cicilan,angsuran_pokok,bunga,tgl_jatuh_tempo,tgl_bayar,jumlah_bayar,ket) values('$d','$no_anggota','$x','$pokok','$ijaroh','$awal_hari','$next_schedule','0','$next_schedule')";
			//$r=mysql_query($detail);
			//}
			$kode_transaksi="BU/".$unit .$cif;					
			$keterangan_transaksi="Realisasi Wakalah Unit $unit $cif";					
			@mysql_query("insert into jurnal_umum(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values('$nomor_jurnal','$kode_transaksi','$tgl_wakalah','$unit')");

			//Dr aseet Murabahah		
			@mysql_query("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
			values('$unit','$kode_transaksi','1431000','$tgl_wakalah','Bukti SYSTEM','$keterangan_transaksi','$Plafond','$userid')");
					
			//cr aseet kas
			@mysql_query("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
			values('$unit','$kode_transaksi','1120000','$tgl_wakalah','Bukti SYSTEM','$keterangan_transaksi','$Plafond','$userid')");
			@mysql_query("update calon_loan set status_app='MURAB' where no_anggota ='$no_anggota'");

		}
				echo '<script>alert("Wakalah sudah direalisasikan."); window.location="realisasi.php"</script>';
    
			}  
			else {
					echo '<script>alert("anda belum memilih data."); window.location="realisasi.php"</script>';
		}

?>
