<?php require_once __DIR__ . '/koneksi_db.php';
error_reporting(E_ALL);
date_default_timezone_set('Asia/Jakarta');
session_start();
ob_start();
    $userid=$_SESSION['userid'];
$tgl_system=$_SESSION['stgl_system'];


$pilih=$_SESSION['spilih'];
$wadiah=$_SESSION['swadiah'];
$muqosah=$_SESSION['snominal'];
$tenor=$_SESSION['stenor'];	
	
db_ni();
mysql_select_db('nur_insani');
include "function.php";
$checbox = $_POST['cekbox'];



if (isset($_POST["realisasi"])){        
    
    if ($checbox) {
        for ($i=0; $i <sizeof($checbox);$i++){

    $row1 = mysql_query("select DATE_ADD(tgl_wakalah,INTERVAL 7 DAY) as tgl_murab1,DATE_ADD('$tgl_system',INTERVAL '$tenor' WEEK) as matur,tgl_murab,loan.buss_date,loan.code_kel,loan.no_anggota,loan.Cust_Short_name,maturity_date,Tenor,
    loan.Deal_type,suffix,bagi_hasil,Tenor,Plafond,os,angsuran,pokok,ijaroh,bulat,ke,usaha,loan.unit,tgl_wakalah,hari,loan.cao,loan.USERID,loan.status,loan.cif,saldo_margin
    from loan 
     left join anggota on anggota.cif = loan.cif
     WHERE loan.cif ='".$checbox[$i]."'");

     $row = mysql_fetch_array($row1);


            $no_anggota=$row['no_anggota'];
            $cif=$row['cif'];
            $bagi_hasil=$row['bagi_hasil'];
            $os=$row['os'];
            $piutang=$os;
            $Cust_Short_name=mysql_real_escape_string($row['Cust_Short_name']);
            $maturity_date=$row['matur'];
$pokok=$row['pokok'];
$tenor_lama=$row['Tenor'];
$angsur=$row['angsuran'];
$ijaroh=$row['ijaroh'];
$matur=$row['maturity_date'];
$bulat_lama=$row['bulat'];
         
            
			$saldo_margin=$row['saldo_margin'];
            $cao=$row['cao'];
            $USERID=$row['USERID'];
			$unit=$row['unit'];

            $kodetr=5;
            $kodetr=acakangkahuruf($kodetr);
            $kode_trans="BU/".$unit .$kodetr;


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
            
            $ket="Realisasi Restrukturisasi $Cust_Short_name";
			$pokok_os=$os - $saldo_margin;
			
			//Hitung Restruturisasai
			$os_res=$pokok_os-$simpanan;
			
			$pokok=$os_res/$tenor;

			$angsuran=$pokok;
			
            $totalharga = $angsuran;
			$totalharga=ceil(compat_num($totalharga));
                            if (substr($totalharga,-4)>9999){
                                $bulat=round(compat_num($totalharga+10000),-4);
                            } else {
                                $bulat=round(compat_num($totalharga),-4)+5000;
                            } 

             		

            
            $kode_transaksi="BU/".$kode_tr;
            //pencatatan du jurnal realasisi Piutang Murabahaha total jumlah Pokok dan margin
            @mysql_query("insert into jurnal_umum(nomor_jurnal,kode_transaksi,tanggal_selesai) values('$nomor_jurnal','$kode_trans','$tgl_system')");
           
            //Realiasasi Murabahah Debit pada Aset Murabahaha total Plafond
            @mysql_query("insert into simpanan(BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,kode_transaksi)
            values('$tgl_system','$norek','$unit','$cif','$code_kel','$simpanan','01','0','$userid','Realisasi Restrukturisasi','','$cao','$kode_trans')");
            
            @mysql_query("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                        values('$unit','$kode_trans','2101000','$tgl_system','Bukti SYSTEM','$ket','$simpanan','$userid')");
                       
            //Realiasasi Murabahah Debit pada Piutang Murabahaha total jumlah Pokok dan margin
            @mysql_query("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                    values('$unit','$kode_trans','1413000','$tgl_system','Bukti SYSTEM','$ket','$simpanan','$userid')");
                      
            //Realisasi PMYD   
            @mysql_query("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
            values('$unit','$kode_trans','1423000','$tgl_system','Bukti SYSTEM','$ket','$saldo_margin','$userid')");
			
			//Piutang murabahah
			@mysql_query("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                    values('$unit','$kode_trans','1413000','$tgl_system','Bukti SYSTEM','$ket','$saldo_margin','$userid')");
			
			
			//Restrukrisasi
			@mysql_query("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                    values('$unit','$kode_trans','1411000','$tgl_system','Bukti SYSTEM','$ket','$os_res','$userid')");
			
			@mysql_query("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                    values('$unit','$kode_trans','1413000','$tgl_system','Bukti SYSTEM','$ket','$os_res','$userid')");
					
                    
           
            //@mysql_query("Update calon_loan set next_schedule = (select DATE_ADD(tgl_murab,INTERVAL 7 DAY) from calon_loan where no_anggota  ='" . $value . "') where no_anggota  ='" . $value . "'") or die(mysql_error("GAGAL INPUT"));
            @mysql_query ("update loan  set os ='$os_res', saldo_margin='0',angsuran='$angsuran',pokok='$angsuran',bulat='$bulat',tenor='$tenor',maturity_date ='$maturity_date',run_tenor='0', ke ='1',ijaroh='0',gol='1',status_app='REST',tgl_murab='$tgl_system', deal_produk ='1' WHERE cif ='$cif'")or die(mysql_error());
           @mysql_query ("delete from tunggakan WHERE cif ='$cif'")or die(mysql_error());
@mysql_query ("delete from temp_eod where cif ='$cif'")or die(mysql_error());
@mysql_query ("delete from  rest_covid where cif ='$cif'")or die(mysql_error());	
@mysql_query ("insert into rest_covid values('','$tgl_system','$cif','$angsur','$pokok','$ijaroh','$bulat_lama','$tenor_lama','$matur','$angsuran','$angsuran_pokok','$angsuran_margin','$bulat','$tenor','$maturity_date','$kelompok')")or die(mysql_error());


            mysql_query("insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif)VALUES('','$tgl_system','$unit','$no_anggota','0','$saldo_margin','03','$ket','$userid','REALISASI BAGHAS','$cif')") or die(mysql_error("GAGAL INPUT"));
           


                   
               

				@mysql_query ("update  loan  set tgl_murab='$tgl_system',tgl_akad='$tgl_system',maturity_date='$maturity_date' cif='$cif'"); 

            
   
           
    echo '<script>alert("Restruturisasi Pokok sudah direalisasi."); window.location="rest_kel_pokok.php"</script>';     
    }

               
		
    elseif($pilih=='02') {


        $kode_tr=5;
            $kode_tr=acakangkahuruf($kode_tr);      
            
            $ket="Realisasi Restrukturisasi";
			$pokok_os=$os - $saldo_margin;
            $pembiayaan =$os;
			
			//Hitung Restruturisasai
			$os_res=$pembiayaan-$simpanan;
			
			$pokok=$os_res/$tenor;
			$angsuran=$pokok;
            $os_pokok=$os_res - $saldo_margin;
            $angsuran_pokok = $os_pokok/$tenor;
            
            $angsuran_margin =$saldo_margin/$tenor;

			
           $totalharga = $angsuran;
			$totalharga=ceil(compat_num($totalharga));
                            if (substr($totalharga,-4)>9999){
                                $bulat=round(compat_num($totalharga+10000),-4);
                            } else {
                                $bulat=round(compat_num($totalharga),-4)+5000;
                            } 

             		
            $kodetr=5;
            $kodetr=acakangkahuruf($kodetr);
            $kode_trans="BU/".$unit .$kodetr;
            
            $kode_transaksi="BU/".$kode_tr;
            //pencatatan du jurnal realasisi Piutang Murabahaha total jumlah Pokok dan margin
            @mysql_query("insert into jurnal_umum(nomor_jurnal,kode_transaksi,tanggal_selesai) values('$nomor_jurnal','$kode_trans','$tgl_system')");
           
            //Realiasasi Murabahah Debit pada Aset Murabahaha total Plafond
            @mysql_query("insert into simpanan(BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,kode_transaksi)
values('$tgl_system','$norek','$unit','$cif','$code_kel','$simpanan','01','0','$userid','Realisasi Restrukturisasi','','$cao','$kode_trans')");

@mysql_query("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
            values('$unit','$kode_trans','2101000','$tgl_system','Bukti SYSTEM','$ket','$simpanan','$userid')");
           
            //Realiasasi Murabahah Debit pada Piutang Murabahaha total jumlah Pokok dan margin
            @mysql_query("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                    values('$unit','$kode_trans','1413000','$tgl_system','Bukti SYSTEM','$ket','$simpanan','$userid')");
                      
             
			@mysql_query("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
            values('$unit','$kode_trans','1411000','$tgl_system','Bukti SYSTEM','$ket','$os_res','$userid')");

            @mysql_query("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
            values('$unit','$kode_trans','1423000','$tgl_system','Bukti SYSTEM','$ket','$saldo_margin','$userid')");
			
			
            @mysql_query("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
            values('$unit','$kode_trans','1413000','$tgl_system','Bukti SYSTEM','$ket','$os_res','$userid')");
					
			@mysql_query("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                    values('$unit','$kode_trans','1421000','$tgl_system','Bukti SYSTEM','$ket','$saldo_margin','$userid')");
					
                    
           
            //@mysql_query("Update calon_loan set next_schedule = (select DATE_ADD(tgl_murab,INTERVAL 7 DAY) from calon_loan where no_anggota  ='" . $value . "') where no_anggota  ='" . $value . "'") or die(mysql_error("GAGAL INPUT"));
@mysql_query ("update loan  set os ='$os_res', saldo_margin='$saldo_margin',angsuran='$angsuran',pokok='$angsuran_pokok',ijaroh ='$angsuran_margin',maturity_date ='$maturity_date',bulat='$bulat',tenor='$tenor',run_tenor='0',tgl_murab='$tgl_system' ,ke ='1',gol='1',status_app='REST_POKOK_MARGIN', deal_produk ='1' WHERE cif ='$cif'")or die(mysql_error());


@mysql_query ("delete from  rest_covid where cif ='$cif'")or die(mysql_error());	
@mysql_query ("insert into rest_covid values('','$tgl_system','$cif','$angsur','$pokok','$ijaroh','$bulat_lama','$tenor_lama','$matur','$angsuran','$angsuran_pokok','$angsuran_margin','$bulat','$tenor','$maturity_date','$kelompok')")or die(mysql_error());


           @mysql_query ("delete from tunggakan WHERE cif ='$cif'")or die(mysql_error());
@mysql_query ("delete from temp_eod where cif ='$cif'")or die(mysql_error());
            //mysql_query("insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif)VALUES('','$tgl_system','$unit','$no_anggota','0','$saldo_margin','03','$ket','$userid','REALISASI BAGHAS','$cif')") or die(mysql_error("GAGAL INPUT"));
           


                   
               

				@mysql_query ("update  loan  set tgl_murab='$tgl_system',tgl_akad='$tgl_system',maturity_date='$maturity_date' cif='$cif'"); 
        echo '<script>alert("Restruturisasi Pokok Margin sudah direalisasi."); window.location="rest_kel_pokok.php"</script>';




    }

            else {
                    echo '<script>alert("Maaf Menu Belum tersedia."); window.location="rest_kel_pokok.php"</script>';
        }


        

   
   
         }





}else {
			echo '<script>alert("anda belum memilih data."); window.location="rest_kel_pokok.php"</script>';
	}
}

?>
