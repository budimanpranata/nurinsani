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
$muqosah=$_SESSION['snominal'];
$tenor=$_SESSION['stenor'];	
$cif=$_GET['cif'];
	
include "function.php";

$row_saldo=db_satu("select sum(kredit-DEBIT) as saldo_simpanan from simpanan where cif =? and type not in('04','05') ", 's', (string) $cif) or die(mysql_error());
       $saldo_simpanan=$row_saldo['saldo_simpanan'];

$matur=db_satu("select maturity_date, angsuran,hari from loan where cif =? ", 's', (string) $cif) or die(mysql_error());
       $mat=$matur['maturity_date'];
$angsur=$matur['angsuran'];
$hari=$matur['hari'];

$row_tunggak=db_satu("select sum(KREDIT-DEBIT) as saldo_tunggak from tunggakan where cif =? ", 's', (string) $cif) or die(mysql_error());
       $saldo_tunggak=$row_tunggak['saldo_tunggak'];     
$tunggak=$saldo_tunggak;

$ft=compat_bagi($tunggak, $angsur); // PHP 5: pembagi 0/kosong -> false, bukan error
         

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
            $bagi_hasil=$row['bagi_hasil'];
            $piutang=$Plafond + $bagi_hasil;
            $Cust_Short_name=$row['Cust_Short_name']; // dikirim sebagai parameter, tidak perlu di-escape
            $os=$row['os'];
			$saldo_margin=$row['saldo_margin'];
            $cao=$row['cao'];
            $USERID=$row['USERID'];
			$unit=$row['unit'];
            if($wadiah =='ya'){
	$simpanan=$saldo_simpanan;
db_jalankan("insert into simpanan(BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,kode_transaksi)
            values(?,?,?,?,?,?,'01','0',?,?,'',?,?)", 'ssssssssss', (string) $tgl_system, (string) $norek, (string) $unit, (string) $cif, (string) $code_kel, (string) $simpanan, (string) $userid, (string) $ket, (string) $cao, (string) $kode_trans);

} else{
	$simpanan=0;
}
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
 		
            $kodetr=5;
            $kodetr=acakangkahuruf($kodetr);
            $kode_trans="BU/".$unit .$kodetr;
            
            $kode_transaksi="BU/".$kode_tr;
            //pencatatan du jurnal realasisi Piutang Murabahaha total jumlah Pokok dan margin
            db_jalankan("insert into jurnal_umum(nomor_jurnal,kode_transaksi,tanggal_selesai) values(?,?,?)", 'sss', (string) $nomor_jurnal, (string) $kode_trans, (string) $tgl_system);
           
            //Realiasasi Murabahah Debit pada Aset Murabahaha total Plafond
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
            db_jalankan("update loan  set os =?, saldo_margin='0',angsuran=?,pokok=?,bulat=?,tenor=?,run_tenor='0', ke ='1',ijaroh='0',gol='1',status_app='REST' , deal_produk ='1' WHERE cif =?", 'ssssss', (string) $os_res, (string) $angsuran, (string) $angsuran, (string) $bulat, (string) $tenor, (string) $cif) or die(mysql_error());
           db_jalankan("delete from tunggakan WHERE cif =?", 's', (string) $cif) or die(mysql_error());
            db_jalankan("insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif)VALUES('',?,?,?,'0',?,'03',?,?,'REALISASI BAGHAS',?)", 'sssssss', (string) $tgl_system, (string) $unit, (string) $no_anggota, (string) $saldo_margin, (string) $ket, (string) $userid, (string) $cif) or die(mysql_error());
           


           // Diperbaiki 2026-10-30 (perintah user): jatuh tempo = tanggal sistem + tenor minggu.
           // Versi lama memakai teks tanggal sebagai timestamp sehingga jatuh tempo menjadi sekitar 1970.
           $maturity_date =  date("Y-m-d", strtotime("+$tenor WEEK", strtotime($tgl_system)));
           
               
                $newdatee = date ("Y-m-d", strtotime("+10 WEEK" , strtotime($maturity_date))) ;
                $awal_hari = $tgl_system;
                $akhir_hari = $newdatee;
                $x=0;
                while (strtotime($awal_hari) < strtotime($akhir_hari)) {
                $x ++;
                $awal_hari= date ("Y-m-d", strtotime("+7 DAY", strtotime($awal_hari)));
                $detail= db_jalankan("INSERT INTO pinjaman_detail_c (id,id_pinjam,cicilan,angsuran_pokok,bunga,tgl_jatuh_tempo,tgl_bayar,jumlah_bayar,ket)
                values(?,?,?,?,?,?,?,'0','')", 'sssssss', (string) $d, (string) $no_anggota, (string) $x, (string) $pokok, (string) $ijaroh, (string) $awal_hari, (string) $awal) or die(mysql_error());
                   
                    }

                       
  db_jalankan("delete from pinjaman_detail where cif=?", 's', (string) $cif);   
        $cek_tanggal = db_semua("SELECT COUNT(tgl_libur) as libur from param_libur
        INNER join pinjaman_detail_c on pinjaman_detail_c.tgl_jatuh_tempo = param_libur.tgl_libur where id_pinjam=?", 's', (string) $no_anggota);
        foreach ($cek_tanggal as $data_libur){
            $libur =$data_libur['libur'];
           
           
        }
       
       
        if ($libur >0){
            db_jalankan("delete from pinjaman_detail_c where id_pinjam=?", 's', (string) $no_anggota);   
   
         
       
         $newdate3 = date ("Y-m-d", strtotime("+$libur WEEK" , strtotime ($maturity_date))) ;
         $cek = db_semua("SELECT * FROM param_libur where tgl_libur=?", 's', (string) $newdate3);
          if(count($cek)==1){
       
            $liburr=$libur+1;
             $newdate2 = date ("Y-m-d", strtotime("+$liburr WEEK" , strtotime ($maturity_date))) ;
            }else {
            $liburr=$libur;
             $newdate2 = date ("Y-m-d", strtotime("+$liburr WEEK" , strtotime ($maturity_date))) ;
            }
               
                $awal_hari = $tgl_system;
                $new_akhir_hari = $newdate2;
                $xx=0;
                while (strtotime($awal_hari) < strtotime($new_akhir_hari)) {
                $xx ++;
                $reff="$no_anggota$xx";
                $awal_hari= date ("Y-m-d", strtotime("+7 DAY", strtotime($awal_hari)));
                $detail_data= db_jalankan("INSERT INTO pinjaman_detail (id,id_pinjam,cicilan,angsuran_pokok,bunga,tgl_jatuh_tempo,tgl_bayar,jumlah_bayar,ket,cif,unit,ao)
                values(?,?,?,?,?,?,?,'0','',?,?,?)", 'ssssssssss', (string) $reff, (string) $no_anggota, (string) $xx, (string) $pokok, (string) $ijaroh, (string) $awal_hari, (string) $awal, (string) $cif, (string) $unit, (string) $cao) or die(mysql_error());
                }   
                db_jalankan("delete from pinjaman_detail where tgl_jatuh_tempo in (select tgl_libur from param_libur where tgl_libur=pinjaman_detail.tgl_jatuh_tempo)");
                $up=db_jalankan("SET @count = 0;");
                $update=db_jalankan("UPDATE pinjaman_detail SET cicilan=@count:=@count+1 where id_pinjam=? ORDER BY cicilan", 's', (string) $no_anggota);
                db_jalankan("delete from pinjaman_detail where cicilan > ? AND cif=?", 'ss', (string) $tenor, (string) $cif); // dulu $Tenor (tidak pernah diisi) sehingga semua cicilan terhapus   
				db_jalankan("update loan set tgl_murab=?,tgl_akad=?,maturity_date=? where cif=?", 'ssss', (string) $tgl_system, (string) $tgl_system, (string) $newdate2, (string) $cif); // dulu SQL tidak valid (update from ...) sehingga tidak pernah tersimpan 
                    }

           
            else{
                db_jalankan("delete from pinjaman_detail_c where id_pinjam=?", 's', (string) $no_anggota);   
                $awal_hari = $tgl_system;
                $akhir_hari = $maturity_date;
                $x1=0;
                while (strtotime($awal_hari) < strtotime($akhir_hari)){
                $x1 ++;
                $reff="$no_anggota$x1";
                $awal_hari= date ("Y-m-d", strtotime("+7 DAY", strtotime($awal_hari)));
                $update= db_jalankan("INSERT INTO pinjaman_detail (id,id_pinjam,cicilan,angsuran_pokok,bunga,tgl_jatuh_tempo,tgl_bayar,jumlah_bayar,ket,cif,unit,ao)
                values(?,?,?,?,?,?,?,'0','',?,?,?)", 'ssssssssss', (string) $reff, (string) $no_anggota, (string) $x1, (string) $pokok, (string) $ijaroh, (string) $awal_hari, (string) $awal, (string) $cif, (string) $unit, (string) $cao) or die(mysql_error());
                           
                }
                db_jalankan("delete from pinjaman_detail where tgl_jatuh_tempo in (select tgl_libur from param_libur where tgl_libur=pinjaman_detail.tgl_jatuh_tempo)");
                $up=db_jalankan("SET @count = 0;");
                $update=db_jalankan("UPDATE pinjaman_detail SET cicilan=@count:=@count+1 where id_pinjam=? ORDER BY cicilan", 's', (string) $no_anggota);
                db_jalankan("delete from pinjaman_detail where cicilan > ? AND cif=?", 'ss', (string) $tenor, (string) $cif); // dulu $Tenor (tidak pernah diisi) sehingga semua cicilan terhapus   
				db_jalankan("update loan set tgl_murab=?,tgl_akad=?,maturity_date=? where cif=?", 'ssss', (string) $tgl_system, (string) $tgl_system, (string) $maturity_date, (string) $cif); // dulu $newdate2 (hanya ada di cabang libur) sehingga tersimpan 0000-00-00 

            }
   
           
    echo '<script>alert("Restruturisasi sudah direalisasi."); window.location="rest.php"</script>';     
    }

               
		
            elseif($pilih =='04') {

$tgl_wakal    = strtotime($mat);
        $murab1     = date("Y-m-d", strtotime("+7 day", $tgl_wakal));
        $tgl_m    = strtotime($murab1);
        $maturity_date_ress =  date("Y-m-d", strtotime("+$ft WEEK", $tgl_m));

$cek_tanggal = db_semua("SELECT COUNT(tgl_libur) as libur from param_libur where tgl_libur between ? and ? and Hari =?", 'sss', (string) $mat, (string) $maturity_date_ress, (string) $hari);
        foreach ($cek_tanggal as $data_libur){
            $libur =$data_libur['libur'];
           
           
        }


if ($libur>0){

$ft =$ft+$libur;

$tgl_wakal    = strtotime($mat);
        $murab1     = date("Y-m-d", strtotime("+7 day", $tgl_wakal));
        $tgl_m    = strtotime($murab1);
        $maturity_date_res =  date("Y-m-d", strtotime("+$ft WEEK", $tgl_m));

}else{
$maturity_date_res =$maturity_date_ress;
}

db_jalankan("update loan set maturity_date =?, gol='1', deal_produk ='1' where cif =?", 'ss', (string) $maturity_date_res, (string) $cif);
db_jalankan("delete from tunggakan where cif =?", 's', (string) $cif);
db_jalankan("delete from temp_eod where cif =?", 's', (string) $cif) or die(mysql_error());

                    echo '<script>alert("Covid Updated ."); window.location="rest.php"</script>';
        }

   else {

                    echo '<script>alert("Maaf menu belum tersedia ."); window.location="rest.php"</script>';
        }

?>
