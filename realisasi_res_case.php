<?php require_once __DIR__ . '/koneksi_db.php';
date_default_timezone_set('Asia/Jakarta');
session_start();
ob_start();
    $userid=$_SESSION['userid'];
$tgl_system=$_SESSION['stgl_system'];
   

$pilih=$_SESSION['spilih'];
$wadiah=$_SESSION['swadiah'];
$pla=$_SESSION['snominal'];
$tenor=$_SESSION['stenor'];	
$cif=$_GET['cif'];

	
db_ni();
mysql_select_db('nur_insani');
include "function.php";

$param_biaya = mysql_query ("select margin,tab,pla,jw  FROM param_biaya where jw ='$tenor' and pla='$pla'");
						$pbiaya = mysql_fetch_array($param_biaya);
						$param_margin=$pbiaya['margin'];
						$param_tabung=$pbiaya['tab'];
						$param_tenor=$pbiaya['jw'];
						$param_pla=$pbiaya['pla'];
						if ($param_tenor==$tenor and $pla==$param_pla)
						{
						$pokok_baru=$pla/$tenor;
						$margin_baru=$pla * ($param_margin/100);
						$ijaroh=$margin_baru/$tenor;
						$angsuran_baru=$ijaroh+$pokok_baru;
						$uang =$param_tabung+$angsuran_baru;
						$os_baru =$pla + $margin_baru;	

				}


$row_saldo = mysql_fetch_array(mysql_query("select sum(kredit-DEBIT) as saldo_simpanan from simpanan where cif ='$cif' and type not in('04','05') ")) or die(mysql_error());
       $saldo_simpanan=$row_saldo['saldo_simpanan'];
if($wadiah =='ya'){
	$simpanan=$saldo_simpanan;
} else{
	$simpanan=0;
}
         

if ($pilih=='01') {
	$kode_tr=5;
            $kode_tr=acakangkahuruf($kode_tr);      
            
            $row = mysql_fetch_array(mysql_query("select DATE_ADD(tgl_wakalah,INTERVAL 7 DAY) as tgl_murab1,DATE_ADD(tgl_wakalah,INTERVAL 26 WEEK) as matur,tgl_murab,loan.buss_date,loan.code_kel,loan.no_anggota,loan.Cust_Short_name,maturity_date,
            loan.Deal_type,suffix,bagi_hasil,Tenor,Plafond,os,angsuran,pokok,ijaroh,bulat,ke,usaha,loan.unit,tgl_wakalah,hari,loan.cao,loan.USERID,loan.status,loan.cif,saldo_margin
            from loan 
             inner join anggota on anggota.cif = loan.cif
             WHERE loan.cif ='$cif'")) or die(mysql_error());
        //$row = mysql_fetch_array(mysql_query("select * from calon_loan  WHERE no_anggota ='" . $value . "'")) or die(mysql_error());
            $no_anggota=$row['no_anggota'];
            $cif=$row['cif'];
            $Cust_Short_name=mysql_real_escape_string($row['Cust_Short_name']);
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
			
			$pokok=$os_res/$tenor;
			$angsuran=$pokok;
			
            $totalharga = $angsuran;
			
 		
            $kodetr=5;
            $kodetr=acakangkahuruf($kodetr);
            $kode_trans="BU/".$unit .$kodetr;
            
            $kode_transaksi="BU/".$kode_tr;
			
			 //pencatatan du jurnal realasisi Piutang Murabahaha total jumlah Pokok dan margin
            @mysql_query("insert into jurnal_umum(nomor_jurnal,kode_transaksi,tanggal_selesai) values('$nomor_jurnal','$kode_trans','$tgl_system')");
			
			@mysql_query("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                    values('$unit','$kode_trans','1413000','$tgl_system','Bukti SYSTEM','$ket','$wadiah','$userid')");
			@mysql_query("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                    values('$unit','$kode_trans','2101000','$tgl_system','Bukti SYSTEM','$ket','$wadiah','$userid')");		
			//pendapatan
			@mysql_query("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                    values('$unit','$kode_trans','41002','$tgl_system','Bukti SYSTEM','$ket','$wadiah_margin','$userid')");
			//pymd
			@mysql_query("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
            values('$unit','$kode_trans','1423000','$tgl_system','Bukti SYSTEM','$ket','$wadiah_margin','$userid')");
			mysql_query("insert  into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,blok,tgl_input,kode_transaksi)VALUES('$tgl_system','$no_anggota','$unit','$cif','$code_kel','0','03','$wadiah','$userid','$ket1','','$cao','0','$tgl_system','$kode_trans') ") or die(mysql_error("GAGAL INPUT"));
			
           
           //Close Pembiyaan sebelumnya
		   //Realisasi PMYD   
            @mysql_query("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
            values('$unit','$kode_trans','1423000','$tgl_system','Bukti SYSTEM','$ket','$bagi_hasil','$userid')");

		   
            //Realiasasi Murabahah Pos Pelantara
            @mysql_query("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
            values('$unit','$kode_trans','1962000','$tgl_system','Bukti SYSTEM','$ket','$Plafond','$userid')");
           
            //Realiasasi Murabahah kredit pada Piutang Murabahaha total jumlah Pokok dan margin
            @mysql_query("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                    values('$unit','$kode_trans','1413000','$tgl_system','Bukti SYSTEM','$ket','$piutang','$userid')");
                    

					
            //open pembiyaan Baru			
			//Piutang murabahah
			@mysql_query("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                    values('$unit','$kode_trans','1413000','$tgl_system','Bukti SYSTEM','$ket','$os_baru','$userid')");
			
			
			//Realisasi PMYD   
            @mysql_query("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
            values('$unit','$kode_trans','1423000','$tgl_system','Bukti SYSTEM','$ket','$margin_baru','$userid')");

			 //Realiasasi Murabahah Pos Pelantara
            @mysql_query("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
            values('$unit','$kode_trans','1962000','$tgl_system','Bukti SYSTEM','$ket','$pla','$userid')");		
                    
           
            //@mysql_query("Update calon_loan set next_schedule = (select DATE_ADD(tgl_murab,INTERVAL 7 DAY) from calon_loan where no_anggota  ='" . $value . "') where no_anggota  ='" . $value . "'") or die(mysql_error("GAGAL INPUT"));
            @mysql_query ("update loan  set Plafond='$pla',os ='$os_baru', bagi_hasil='$margin_baru',saldo_margin='$margin_baru',angsuran='$angsuran_baru',pokok='$pokok_baru',bulat='$uang',tenor='$tenor',run_tenor='0', ke ='1',ijaroh='$ijaroh',gol='1',status_app='MURAB', deal_produk ='1' WHERE cif ='$cif'")or die(mysql_error());
           @mysql_query ("delete from tunggakan WHERE cif ='$cif'")or die(mysql_error());
            mysql_query("insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif)VALUES('','$tgl_system','$unit','$no_anggota','0','$saldo_margin','03','$ket','$userid','REALISASI BAGHAS','$cif')") or die(mysql_error("GAGAL INPUT"));
           mysql_query("insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif)VALUES('','$tgl_system','$unit','$no_anggota','0','$pokok_os','03','$ket','$userid','REALISASI POKOK','$cif')") or die(mysql_error("GAGAL INPUT"));



           

                       
 
           
    echo '<script>alert("Restruturisasi sudah direalisasi."); window.location="rest_case.php"</script>';     
    }elseif ($pilih=='05'){
$row = mysql_fetch_array(mysql_query("select DATE_ADD(tgl_wakalah,INTERVAL 7 DAY) as tgl_murab1,DATE_ADD('$tgl_system',INTERVAL '$tenor' WEEK) as matur,tgl_murab,loan.buss_date,loan.code_kel,loan.no_anggota,loan.Cust_Short_name,maturity_date,
            loan.Deal_type,suffix,bagi_hasil,Tenor,Plafond,os,angsuran,pokok,ijaroh,bulat,ke,usaha,loan.unit,tgl_wakalah,hari,loan.cao,loan.USERID,loan.status,loan.cif,saldo_margin
            from loan 
             inner join anggota on anggota.cif = loan.cif
             WHERE loan.cif ='$cif'")) or die(mysql_error());
        //$row = mysql_fetch_array(mysql_query("select * from calon_loan  WHERE no_anggota ='" . $value . "'")) or die(mysql_error());
            $no_anggota=$row['no_anggota'];
            $cif=$row['cif'];
	    $matur=$row['matur'];
            $Cust_Short_name=mysql_real_escape_string($row['Cust_Short_name']);
            $os=$row['os'];
		$saldo_margin=$row['saldo_margin'];
            $cao=$row['cao'];
            $USERID=$row['USERID'];
		$unit=$row['unit'];
            $angsuran_baru= $os/$tenor;

	$totalharga = $angsuran_baru;
$totalharga=ceil(compat_num($totalharga));
							if (substr($totalharga,-3)>499){
								$total_harga=round(compat_num($totalharga),-3);
							} else {
								$total_harga=round(compat_num($totalharga),-3)+1000;
							} 

 @mysql_query ("update loan  set os ='$os', angsuran='$angsuran_baru',pokok='$angsuran_baru',bulat='$total_harga',tenor='$tenor',run_tenor='0',maturity_date ='$matur',ke ='1',gol='1', tgl_akad='$tgl_system', tgl_murab ='$tgl_system', deal_produk ='1' WHERE cif ='$cif'")or die(mysql_error());
  @mysql_query ("delete from tunggakan WHERE cif ='$cif'")or die(mysql_error());

}

               
		
            else {
                    echo '<script>alert("Maaf Menu Belum tersedia."); window.location="rest_case.php"</script>';
        }

?>
