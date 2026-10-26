<?php require_once __DIR__ . '/koneksi_db.php'; 
db_ni();
mysql_select_db('nur_insani');


$tanggal=date("d/m/Y");
			
			
$cekbox = $_POST['cekbox'];
if ($cekbox) {
       foreach ($cekbox as $value) {
		   
			$row_sal= mysql_fetch_array(mysql_query("select(sum(DEBIT)-sum(KREDIT))as saldo from simpanan WHERE NOREK ='" . $value . "'")) or die(mysql_error());
				$saldo=$row_sal['saldo'];
			
			$select = mysql_query("select DATE_ADD(next_schedule,INTERVAL 7 DAY) as next_schedule,next_schedule as tgl_jatuh, unit,code_kel,no_anggota,ijaroh,Plafond,pokok,os,angsuran,bulat,TIMESTAMPDIFF(WEEK,tgl_wakalah,next_schedule) AS RUN_TENOR,TIMESTAMPDIFF(WEEK,tgl_wakalah,CURDATE()) AS ke  from loan  WHERE no_anggota ='" . $value . "'");
			while($row = mysql_fetch_array($select)){
				$next_schedule=$row['next_schedule'];
				$no_anggota=$row['no_anggota'];
				$pokok=$row['pokok'];
				$os=$row['os'];
				$bulat=$row['bulat'];
				$RUN_TENOR=$row['RUN_TENOR'];
				$unit=$row['unit'];
				$angsuran=$row['angsuran'];
				$ijaroh=$row['ijaroh'];
				$code_kel=$row['code_kel'];
				$ke=$row['ke'];
				$ket="Realisasi tagihan $no_anggota $tanggal";

	if ($saldo < $angsuran){
		@mysql_query("INSERT INTO `tunggakan`(code_tunggak,no_anggota,tgl_tunggak,nominal,unit,ket)VALUES('','$no_anggota','$tgl_jatuh','$angsuran','$unit','$ket')");
		}	
		else{
			@mysql_query("Update  `loan` set next_schedule ='$next_schedule',os =os-'$pokok',run_tenor='$RUN_TENOR',ke='$ke' where no_anggota  ='" . $value . "'") or die(mysql_error("GAGAL INPUT"));
			@mysql_query("Update  `rek_loan` set ket ='$ket',STATUS='REALISASI' where no_anggota  ='" . $value . "'") or die(mysql_error("GAGAL INPUT"));
			@mysql_query("insert into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF)VALUES('$tanggal','$no_anggota','$unit','$cif','$code_kel','0','02','$pokok','$userid','') ") or die(mysql_error("GAGAL INPUT"));
			@mysql_query("Update  `tabungan_detail` set saldo =(select(sum(DEBIT)-SUM(KREDIT)) as saldo from simpanan where NOREK='" . $value . "') where no_anggota  ='" . $value . "'") or die(mysql_error("GAGAL INPUT"));
			@mysql_query("insert into tagihan (id_reff,code_kel,no_anggota,bagi_hasil,pokok,tagihan,bulat,tagihan_ke,sisa_os,tgl_realisasi,status)VALUES('','$code_kel','$no_anggota','$ijaroh','$pokok','$angsuran','$bulat','$ke','$os','$next_schedule','RENCANA') ") or die(mysql_error("GAGAL INPUT"));
			}
				}
					echo '<script>alert("Pinjaman sudah direalisasikan."); window.location="auto_deb.php"</script>';
    
				} 
				
	   }
?>