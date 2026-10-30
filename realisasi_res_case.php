<?php require_once __DIR__ . '/koneksi_db.php';
date_default_timezone_set('Asia/Jakarta');
session_start();
require_once __DIR__ . '/inc/cek_login.php';
wajib_login();
ob_start();
    $userid=$_SESSION['userid'];
$tgl_system=$_SESSION['stgl_system'];
   

$pilih=$_SESSION['spilih'];
$wadiah=$_SESSION['swadiah'];
$pla=$_SESSION['snominal'];
$tenor=$_SESSION['stenor'];	
$cif=$_GET['cif'];

	
include "function.php";

$param_biaya = db_semua("select margin,tab,pla,jw  FROM param_biaya where jw =? and pla=?", 'ss', (string) $tenor, (string) $pla);
						$pbiaya = $param_biaya ? $param_biaya[0] : false;
						$param_margin=$pbiaya['margin'];
						$param_tabung=$pbiaya['tab'];
						$param_tenor=$pbiaya['jw'];
						$param_pla=$pbiaya['pla'];
						if ($param_tenor==$tenor and $pla==$param_pla)
						{
						$pokok_baru=compat_bagi($pla, $tenor); // PHP 5: pembagi 0/kosong -> false, bukan error
						$margin_baru=$pla * ($param_margin/100);
						$ijaroh=compat_bagi($margin_baru, $tenor); // PHP 5: pembagi 0/kosong -> false, bukan error
						$angsuran_baru=$ijaroh+$pokok_baru;
						$uang =$param_tabung+$angsuran_baru;
						$os_baru =$pla + $margin_baru;	

				}


$row_saldo=db_satu("select sum(kredit-DEBIT) as saldo_simpanan from simpanan where cif =? and type not in('04','05') ", 's', (string) $cif) or die(mysql_error());
       $saldo_simpanan=$row_saldo['saldo_simpanan'];
if($wadiah =='ya'){
	$simpanan=$saldo_simpanan;
} else{
	$simpanan=0;
}
         

if ($pilih=='01') {
	$kode_tr=5;
            $kode_tr=acakangkahuruf($kode_tr);      
            
            $row=db_satu("select DATE_ADD(tgl_wakalah,INTERVAL 7 DAY) as tgl_murab1,DATE_ADD(tgl_wakalah,INTERVAL 26 WEEK) as matur,tgl_murab,loan.buss_date,loan.code_kel,loan.no_anggota,loan.Cust_Short_name,maturity_date,
            loan.Deal_type,suffix,bagi_hasil,Tenor,Plafond,os,angsuran,pokok,ijaroh,bulat,ke,usaha,loan.unit,tgl_wakalah,hari,loan.cao,loan.USERID,loan.status,loan.cif,saldo_margin
            from loan 
             inner join anggota on anggota.cif = loan.cif
             WHERE loan.cif =?", 's', (string) $cif) or die(mysql_error());
        //$row = mysql_fetch_array(mysql_query("select * from calon_loan  WHERE no_anggota ='" . $value . "'")) or die(mysql_error());
            $no_anggota=$row['no_anggota'];
            $cif=$row['cif'];
            $Cust_Short_name=$row['Cust_Short_name']; // dikirim sebagai parameter, tidak perlu di-escape
            $os=$row['os'];
			$saldo_margin=$row['saldo_margin'];
            $cao=$row['cao'];
            $USERID=$row['USERID'];
			$unit=$row['unit'];
            $Plafond=$row['Plafond'];
			$bagi_hasil=$row['bagi_hasil'];
			$piutang=$Plafond + $bagi_hasil;
            $ket="Realisasi Restrukturisasi $Cust_Short_name";
			$ket1="Realisasi Reversal Restrukturisasi $Cust_Short_name";
			$pokok_os=$os - $saldo_margin;
			$wadiah = ($Plafond + $bagi_hasil)-$os;
			$wadiah_margin=$bagi_hasil - $saldo_margin;
			$code_kel=$row['code_kel'];
			//Hitung Restruturisasai
			$os_res=$pokok_os-$simpanan;
			
			$pokok=compat_bagi($os_res, $tenor); // PHP 5: pembagi 0/kosong -> false, bukan error
			$angsuran=$pokok;
			
            $totalharga = $angsuran;
			
 		
            $kodetr=5;
            $kodetr=acakangkahuruf($kodetr);
            $kode_trans="BU/".$unit .$kodetr;
            
            $kode_transaksi="BU/".$kode_tr;
			
			 //pencatatan du jurnal realasisi Piutang Murabahaha total jumlah Pokok dan margin
            db_jalankan("insert into jurnal_umum(nomor_jurnal,kode_transaksi,tanggal_selesai) values(?,?,?)", 'sss', (string) $nomor_jurnal, (string) $kode_trans, (string) $tgl_system);
			
			db_jalankan("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                    values(?,?,'1413000',?,'Bukti SYSTEM',?,?,?)", 'ssssss', (string) $unit, (string) $kode_trans, (string) $tgl_system, (string) $ket, (string) $wadiah, (string) $userid);
			db_jalankan("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                    values(?,?,'2101000',?,'Bukti SYSTEM',?,?,?)", 'ssssss', (string) $unit, (string) $kode_trans, (string) $tgl_system, (string) $ket, (string) $wadiah, (string) $userid);		
			//pendapatan
			db_jalankan("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                    values(?,?,'41002',?,'Bukti SYSTEM',?,?,?)", 'ssssss', (string) $unit, (string) $kode_trans, (string) $tgl_system, (string) $ket, (string) $wadiah_margin, (string) $userid);
			//pymd
			db_jalankan("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
            values(?,?,'1423000',?,'Bukti SYSTEM',?,?,?)", 'ssssss', (string) $unit, (string) $kode_trans, (string) $tgl_system, (string) $ket, (string) $wadiah_margin, (string) $userid);
			db_jalankan("insert  into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,blok,tgl_input,kode_transaksi)VALUES(?,?,?,?,?,'0','03',?,?,?,'',?,'0',?,?) ", 'sssssssssss', (string) $tgl_system, (string) $no_anggota, (string) $unit, (string) $cif, (string) $code_kel, (string) $wadiah, (string) $userid, (string) $ket1, (string) $cao, (string) $tgl_system, (string) $kode_trans) or die(mysql_error());
			
           
           //Close Pembiyaan sebelumnya
		   //Realisasi PMYD   
            db_jalankan("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
            values(?,?,'1423000',?,'Bukti SYSTEM',?,?,?)", 'ssssss', (string) $unit, (string) $kode_trans, (string) $tgl_system, (string) $ket, (string) $bagi_hasil, (string) $userid);

		   
            //Realiasasi Murabahah Pos Pelantara
            db_jalankan("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
            values(?,?,'1962000',?,'Bukti SYSTEM',?,?,?)", 'ssssss', (string) $unit, (string) $kode_trans, (string) $tgl_system, (string) $ket, (string) $Plafond, (string) $userid);
           
            //Realiasasi Murabahah kredit pada Piutang Murabahaha total jumlah Pokok dan margin
            db_jalankan("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                    values(?,?,'1413000',?,'Bukti SYSTEM',?,?,?)", 'ssssss', (string) $unit, (string) $kode_trans, (string) $tgl_system, (string) $ket, (string) $piutang, (string) $userid);
                    

					
            //open pembiyaan Baru			
			//Piutang murabahah
			db_jalankan("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                    values(?,?,'1413000',?,'Bukti SYSTEM',?,?,?)", 'ssssss', (string) $unit, (string) $kode_trans, (string) $tgl_system, (string) $ket, (string) $os_baru, (string) $userid);
			
			
			//Realisasi PMYD   
            db_jalankan("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
            values(?,?,'1423000',?,'Bukti SYSTEM',?,?,?)", 'ssssss', (string) $unit, (string) $kode_trans, (string) $tgl_system, (string) $ket, (string) $margin_baru, (string) $userid);

			 //Realiasasi Murabahah Pos Pelantara
            db_jalankan("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
            values(?,?,'1962000',?,'Bukti SYSTEM',?,?,?)", 'ssssss', (string) $unit, (string) $kode_trans, (string) $tgl_system, (string) $ket, (string) $pla, (string) $userid);		
                    
           
            //@mysql_query("Update calon_loan set next_schedule = (select DATE_ADD(tgl_murab,INTERVAL 7 DAY) from calon_loan where no_anggota  ='" . $value . "') where no_anggota  ='" . $value . "'") or die(mysql_error("GAGAL INPUT"));
            db_jalankan("update loan  set Plafond=?,os =?, bagi_hasil=?,saldo_margin=?,angsuran=?,pokok=?,bulat=?,tenor=?,run_tenor='0', ke ='1',ijaroh=?,gol='1',status_app='MURAB', deal_produk ='1' WHERE cif =?", 'ssssssssss', (string) $pla, (string) $os_baru, (string) $margin_baru, (string) $margin_baru, (string) $angsuran_baru, (string) $pokok_baru, (string) $uang, (string) $tenor, (string) $ijaroh, (string) $cif) or die(mysql_error());
           db_jalankan("delete from tunggakan WHERE cif =?", 's', (string) $cif) or die(mysql_error());
            db_jalankan("insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif)VALUES('',?,?,?,'0',?,'03',?,?,'REALISASI BAGHAS',?)", 'sssssss', (string) $tgl_system, (string) $unit, (string) $no_anggota, (string) $saldo_margin, (string) $ket, (string) $userid, (string) $cif) or die(mysql_error());
           db_jalankan("insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif)VALUES('',?,?,?,'0',?,'03',?,?,'REALISASI POKOK',?)", 'sssssss', (string) $tgl_system, (string) $unit, (string) $no_anggota, (string) $pokok_os, (string) $ket, (string) $userid, (string) $cif) or die(mysql_error());



           

                       
 
           
    echo '<script>alert("Restruturisasi sudah direalisasi."); window.location="rest_case.php"</script>';     
    }elseif ($pilih=='05'){
$row=db_satu("select DATE_ADD(tgl_wakalah,INTERVAL 7 DAY) as tgl_murab1,DATE_ADD(?,INTERVAL ? WEEK) as matur,tgl_murab,loan.buss_date,loan.code_kel,loan.no_anggota,loan.Cust_Short_name,maturity_date,
            loan.Deal_type,suffix,bagi_hasil,Tenor,Plafond,os,angsuran,pokok,ijaroh,bulat,ke,usaha,loan.unit,tgl_wakalah,hari,loan.cao,loan.USERID,loan.status,loan.cif,saldo_margin
            from loan 
             inner join anggota on anggota.cif = loan.cif
             WHERE loan.cif =?", 'sss', (string) $tgl_system, (string) $tenor, (string) $cif) or die(mysql_error());
        //$row = mysql_fetch_array(mysql_query("select * from calon_loan  WHERE no_anggota ='" . $value . "'")) or die(mysql_error());
            $no_anggota=$row['no_anggota'];
            $cif=$row['cif'];
	    $matur=$row['matur'];
            $Cust_Short_name=$row['Cust_Short_name']; // dikirim sebagai parameter, tidak perlu di-escape
            $os=$row['os'];
		$saldo_margin=$row['saldo_margin'];
            $cao=$row['cao'];
            $USERID=$row['USERID'];
		$unit=$row['unit'];
            $angsuran_baru= compat_bagi($os, $tenor); // PHP 5: pembagi 0/kosong -> false, bukan error

	$totalharga = $angsuran_baru;
$totalharga=ceil(compat_num($totalharga));
							if (substr($totalharga,-3)>499){
								$total_harga=round(compat_num($totalharga),-3);
							} else {
								$total_harga=round(compat_num($totalharga),-3)+1000;
							} 

 db_jalankan("update loan  set os =?, angsuran=?,pokok=?,bulat=?,tenor=?,run_tenor='0',maturity_date =?,ke ='1',gol='1', tgl_akad=?, tgl_murab =?, deal_produk ='1' WHERE cif =?", 'sssssssss', (string) $os, (string) $angsuran_baru, (string) $angsuran_baru, (string) $total_harga, (string) $tenor, (string) $matur, (string) $tgl_system, (string) $tgl_system, (string) $cif) or die(mysql_error());
  db_jalankan("delete from tunggakan WHERE cif =?", 's', (string) $cif) or die(mysql_error());

}

               
		
            else {
                    echo '<script>alert("Maaf Menu Belum tersedia."); window.location="rest_case.php"</script>';
        }

?>
