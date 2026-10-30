<?php require_once __DIR__ . '/koneksi_db.php';
error_reporting(E_ALL);
date_default_timezone_set('Asia/Jakarta');
session_start();
require_once __DIR__ . '/inc/cek_login.php';
wajib_login();
ob_start();
    $userid=$_SESSION['userid'];
$tgl_system=$_SESSION['stgl_system'];


$pilih=$_SESSION['spilih'];
$wadiah=$_SESSION['swadiah'];
$muqosah=$_SESSION['snominal'];
$tenor=$_SESSION['stenor'];	
	
include "function.php";
$checbox = isset($_POST['cekbox']) ? $_POST['cekbox'] : null;



if (isset($_POST["realisasi"])){        
    
    if ($checbox) {
        for ($i=0; $i <sizeof($checbox);$i++){

    $row = db_satu("select DATE_ADD(tgl_wakalah,INTERVAL 7 DAY) as tgl_murab1,DATE_ADD(?,INTERVAL ? WEEK) as matur,tgl_murab,loan.buss_date,loan.code_kel,loan.no_anggota,loan.Cust_Short_name,maturity_date,Tenor,
    loan.Deal_type,suffix,bagi_hasil,Tenor,Plafond,os,angsuran,pokok,ijaroh,bulat,ke,usaha,loan.unit,tgl_wakalah,hari,loan.cao,loan.USERID,loan.status,loan.cif,saldo_margin
    from loan 
     left join anggota on anggota.cif = loan.cif
     WHERE loan.cif =?", 'sss', (string) $tgl_system, (string) $tenor, (string) $checbox[$i]);


            $no_anggota=$row['no_anggota'];
            $cif=$row['cif'];
            $bagi_hasil=$row['bagi_hasil'];
            $os=$row['os'];
            $piutang=$os;
            $Cust_Short_name=$row['Cust_Short_name']; // dikirim sebagai parameter, tidak perlu di-escape
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
            
            $ket="Realisasi Restrukturisasi $Cust_Short_name";
			$pokok_os=$os - $saldo_margin;
			
			//Hitung Restruturisasai
			$os_res=$pokok_os-$simpanan;
			
			$pokok=compat_bagi($os_res, $tenor); // PHP 5: pembagi 0/kosong -> false, bukan error

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
            db_jalankan("insert into jurnal_umum(nomor_jurnal,kode_transaksi,tanggal_selesai) values(?,?,?)", 'sss', (string) $nomor_jurnal, (string) $kode_trans, (string) $tgl_system);
           
            //Realiasasi Murabahah Debit pada Aset Murabahaha total Plafond
            db_jalankan("insert into simpanan(BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,kode_transaksi)
            values(?,?,?,?,?,?,'01','0',?,'Realisasi Restrukturisasi','',?,?)", 'sssssssss', (string) $tgl_system, (string) $norek, (string) $unit, (string) $cif, (string) $code_kel, (string) $simpanan, (string) $userid, (string) $cao, (string) $kode_trans);
            
            db_jalankan("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                        values(?,?,'2101000',?,'Bukti SYSTEM',?,?,?)", 'ssssss', (string) $unit, (string) $kode_trans, (string) $tgl_system, (string) $ket, (string) $simpanan, (string) $userid);
                       
            //Realiasasi Murabahah Debit pada Piutang Murabahaha total jumlah Pokok dan margin
            db_jalankan("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                    values(?,?,'1413000',?,'Bukti SYSTEM',?,?,?)", 'ssssss', (string) $unit, (string) $kode_trans, (string) $tgl_system, (string) $ket, (string) $simpanan, (string) $userid);
                      
            //Realisasi PMYD   
            db_jalankan("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
            values(?,?,'1423000',?,'Bukti SYSTEM',?,?,?)", 'ssssss', (string) $unit, (string) $kode_trans, (string) $tgl_system, (string) $ket, (string) $saldo_margin, (string) $userid);
			
			//Piutang murabahah
			db_jalankan("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                    values(?,?,'1413000',?,'Bukti SYSTEM',?,?,?)", 'ssssss', (string) $unit, (string) $kode_trans, (string) $tgl_system, (string) $ket, (string) $saldo_margin, (string) $userid);
			
			
			//Restrukrisasi
			db_jalankan("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                    values(?,?,'1411000',?,'Bukti SYSTEM',?,?,?)", 'ssssss', (string) $unit, (string) $kode_trans, (string) $tgl_system, (string) $ket, (string) $os_res, (string) $userid);
			
			db_jalankan("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                    values(?,?,'1413000',?,'Bukti SYSTEM',?,?,?)", 'ssssss', (string) $unit, (string) $kode_trans, (string) $tgl_system, (string) $ket, (string) $os_res, (string) $userid);
					
                    
           
            //@mysql_query("Update calon_loan set next_schedule = (select DATE_ADD(tgl_murab,INTERVAL 7 DAY) from calon_loan where no_anggota  ='" . $value . "') where no_anggota  ='" . $value . "'") or die(mysql_error("GAGAL INPUT"));
            db_jalankan("update loan  set os =?, saldo_margin='0',angsuran=?,pokok=?,bulat=?,tenor=?,maturity_date =?,run_tenor='0', ke ='1',ijaroh='0',gol='1',status_app='REST',tgl_murab=?, deal_produk ='1' WHERE cif =?", 'ssssssss', (string) $os_res, (string) $angsuran, (string) $angsuran, (string) $bulat, (string) $tenor, (string) $maturity_date, (string) $tgl_system, (string) $cif) or die(mysql_error());
           db_jalankan("delete from tunggakan WHERE cif =?", 's', (string) $cif) or die(mysql_error());
db_jalankan("delete from temp_eod where cif =?", 's', (string) $cif) or die(mysql_error());
db_jalankan("delete from  rest_covid where cif =?", 's', (string) $cif) or die(mysql_error());	
db_jalankan("insert into rest_covid values('',?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)", 'sssssssssssssss', (string) $tgl_system, (string) $cif, (string) $angsur, (string) $pokok, (string) $ijaroh, (string) $bulat_lama, (string) $tenor_lama, (string) $matur, (string) $angsuran, (string) $angsuran_pokok, (string) $angsuran_margin, (string) $bulat, (string) $tenor, (string) $maturity_date, (string) $kelompok) or die(mysql_error());


            db_jalankan("insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif)VALUES('',?,?,?,'0',?,'03',?,?,'REALISASI BAGHAS',?)", 'sssssss', (string) $tgl_system, (string) $unit, (string) $no_anggota, (string) $saldo_margin, (string) $ket, (string) $userid, (string) $cif) or die(mysql_error());
           


                   
               

				db_jalankan("update  loan  set tgl_murab=?,tgl_akad=?,maturity_date=? cif=?", 'ssss', (string) $tgl_system, (string) $tgl_system, (string) $maturity_date, (string) $cif); 

            
   
           
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
			
			$pokok=compat_bagi($os_res, $tenor); // PHP 5: pembagi 0/kosong -> false, bukan error
			$angsuran=$pokok;
            $os_pokok=$os_res - $saldo_margin;
            $angsuran_pokok = compat_bagi($os_pokok, $tenor); // PHP 5: pembagi 0/kosong -> false, bukan error
            
            $angsuran_margin =compat_bagi($saldo_margin, $tenor); // PHP 5: pembagi 0/kosong -> false, bukan error

			
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
            db_jalankan("insert into jurnal_umum(nomor_jurnal,kode_transaksi,tanggal_selesai) values(?,?,?)", 'sss', (string) $nomor_jurnal, (string) $kode_trans, (string) $tgl_system);
           
            //Realiasasi Murabahah Debit pada Aset Murabahaha total Plafond
            db_jalankan("insert into simpanan(BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,kode_transaksi)
values(?,?,?,?,?,?,'01','0',?,'Realisasi Restrukturisasi','',?,?)", 'sssssssss', (string) $tgl_system, (string) $norek, (string) $unit, (string) $cif, (string) $code_kel, (string) $simpanan, (string) $userid, (string) $cao, (string) $kode_trans);

db_jalankan("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
            values(?,?,'2101000',?,'Bukti SYSTEM',?,?,?)", 'ssssss', (string) $unit, (string) $kode_trans, (string) $tgl_system, (string) $ket, (string) $simpanan, (string) $userid);
           
            //Realiasasi Murabahah Debit pada Piutang Murabahaha total jumlah Pokok dan margin
            db_jalankan("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                    values(?,?,'1413000',?,'Bukti SYSTEM',?,?,?)", 'ssssss', (string) $unit, (string) $kode_trans, (string) $tgl_system, (string) $ket, (string) $simpanan, (string) $userid);
                      
             
			db_jalankan("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
            values(?,?,'1411000',?,'Bukti SYSTEM',?,?,?)", 'ssssss', (string) $unit, (string) $kode_trans, (string) $tgl_system, (string) $ket, (string) $os_res, (string) $userid);

            db_jalankan("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
            values(?,?,'1423000',?,'Bukti SYSTEM',?,?,?)", 'ssssss', (string) $unit, (string) $kode_trans, (string) $tgl_system, (string) $ket, (string) $saldo_margin, (string) $userid);
			
			
            db_jalankan("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
            values(?,?,'1413000',?,'Bukti SYSTEM',?,?,?)", 'ssssss', (string) $unit, (string) $kode_trans, (string) $tgl_system, (string) $ket, (string) $os_res, (string) $userid);
					
			db_jalankan("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                    values(?,?,'1421000',?,'Bukti SYSTEM',?,?,?)", 'ssssss', (string) $unit, (string) $kode_trans, (string) $tgl_system, (string) $ket, (string) $saldo_margin, (string) $userid);
					
                    
           
            //@mysql_query("Update calon_loan set next_schedule = (select DATE_ADD(tgl_murab,INTERVAL 7 DAY) from calon_loan where no_anggota  ='" . $value . "') where no_anggota  ='" . $value . "'") or die(mysql_error("GAGAL INPUT"));
db_jalankan("update loan  set os =?, saldo_margin=?,angsuran=?,pokok=?,ijaroh =?,maturity_date =?,bulat=?,tenor=?,run_tenor='0',tgl_murab=? ,ke ='1',gol='1',status_app='REST_POKOK_MARGIN', deal_produk ='1' WHERE cif =?", 'ssssssssss', (string) $os_res, (string) $saldo_margin, (string) $angsuran, (string) $angsuran_pokok, (string) $angsuran_margin, (string) $maturity_date, (string) $bulat, (string) $tenor, (string) $tgl_system, (string) $cif) or die(mysql_error());


db_jalankan("delete from  rest_covid where cif =?", 's', (string) $cif) or die(mysql_error());	
db_jalankan("insert into rest_covid values('',?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)", 'sssssssssssssss', (string) $tgl_system, (string) $cif, (string) $angsur, (string) $pokok, (string) $ijaroh, (string) $bulat_lama, (string) $tenor_lama, (string) $matur, (string) $angsuran, (string) $angsuran_pokok, (string) $angsuran_margin, (string) $bulat, (string) $tenor, (string) $maturity_date, (string) $kelompok) or die(mysql_error());


           db_jalankan("delete from tunggakan WHERE cif =?", 's', (string) $cif) or die(mysql_error());
db_jalankan("delete from temp_eod where cif =?", 's', (string) $cif) or die(mysql_error());
            //mysql_query("insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif)VALUES('','$tgl_system','$unit','$no_anggota','0','$saldo_margin','03','$ket','$userid','REALISASI BAGHAS','$cif')") or die(mysql_error("GAGAL INPUT"));
           


                   
               

				db_jalankan("update  loan  set tgl_murab=?,tgl_akad=?,maturity_date=? cif=?", 'ssss', (string) $tgl_system, (string) $tgl_system, (string) $maturity_date, (string) $cif); 
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
