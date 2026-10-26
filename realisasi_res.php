<?php require_once __DIR__ . '/koneksi_db.php';
date_default_timezone_set('Asia/Jakarta');
session_start();
ob_start();
    $userid=$_SESSION['userid'];
$tgl_system=$_SESSION['stgl_system'];
   

$pilih=$_SESSION['spilih'];
$wadiah=$_SESSION['swadiah'];
$muqosah=$_SESSION['snominal'];
$tenor=$_SESSION['stenor'];	
$cif=$_GET['cif'];
	
db_ni();
mysql_select_db('nur_insani');
include "function.php";

$row_saldo = mysql_fetch_array(mysql_query("select sum(kredit-DEBIT) as saldo_simpanan from simpanan where cif ='$cif' and type not in('04','05') ")) or die(mysql_error());
       $saldo_simpanan=$row_saldo['saldo_simpanan'];

$matur = mysql_fetch_array(mysql_query("select maturity_date, angsuran,hari from loan where cif ='$cif' ")) or die(mysql_error());
       $mat=$matur['maturity_date'];
$angsur=$matur['angsuran'];
$hari=$matur['hari'];

$row_tunggak = mysql_fetch_array(mysql_query("select sum(KREDIT-DEBIT) as saldo_tunggak from tunggakan where cif ='$cif' ")) or die(mysql_error());
       $saldo_tunggak=$row_tunggak['saldo_tunggak'];     
$tunggak=$saldo_tunggak;

$ft=$tunggak/$angsur;
         

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
            $bagi_hasil=$row['bagi_hasil'];
            $piutang=$Plafond + $bagi_hasil;
            $Cust_Short_name=mysql_real_escape_string($row['Cust_Short_name']);
            $os=$row['os'];
			$saldo_margin=$row['saldo_margin'];
            $cao=$row['cao'];
            $USERID=$row['USERID'];
			$unit=$row['unit'];
            if($wadiah =='ya'){
	$simpanan=$saldo_simpanan;
@mysql_query("insert into simpanan(BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,kode_transaksi)
            values('$tgl_system','$norek','$unit','$cif','$code_kel','$simpanan','01','0','$userid','$ket','','$cao','$kode_trans')");

} else{
	$simpanan=0;
}
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
 		
            $kodetr=5;
            $kodetr=acakangkahuruf($kodetr);
            $kode_trans="BU/".$unit .$kodetr;
            
            $kode_transaksi="BU/".$kode_tr;
            //pencatatan du jurnal realasisi Piutang Murabahaha total jumlah Pokok dan margin
            @mysql_query("insert into jurnal_umum(nomor_jurnal,kode_transaksi,tanggal_selesai) values('$nomor_jurnal','$kode_trans','$tgl_system')");
           
            //Realiasasi Murabahah Debit pada Aset Murabahaha total Plafond
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
            @mysql_query ("update loan  set os ='$os_res', saldo_margin='0',angsuran='$angsuran',pokok='$angsuran',bulat='$bulat',tenor='$tenor',run_tenor='0', ke ='1',ijaroh='0',gol='1',status_app='REST' , deal_produk ='1' WHERE cif ='$cif'")or die(mysql_error());
           @mysql_query ("delete from tunggakan WHERE cif ='$cif'")or die(mysql_error());
            mysql_query("insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif)VALUES('','$tgl_system','$unit','$no_anggota','0','$saldo_margin','03','$ket','$userid','REALISASI BAGHAS','$cif')") or die(mysql_error("GAGAL INPUT"));
           


           $maturity_date =  date("Y-m-d", strtotime("+$tenor WEEK", $tgl_system));
           
               
                $newdatee = date ("Y-m-d", strtotime("+10 WEEK" , strtotime($maturity_date))) ;
                $awal_hari = $tgl_system;
                $akhir_hari = $newdatee;
                $x=0;
                while (strtotime($awal_hari) < strtotime($akhir_hari)) {
                $x ++;
                $awal_hari= date ("Y-m-d", strtotime("+7 DAY", strtotime($awal_hari)));
                $detail= mysql_query("INSERT INTO pinjaman_detail_c (id,id_pinjam,cicilan,angsuran_pokok,bunga,tgl_jatuh_tempo,tgl_bayar,jumlah_bayar,ket)
                values('$d','$no_anggota','$x','$pokok','$ijaroh','$awal_hari','$awal','0','')") or die(mysql_error());
                   
                    }

                       
  @mysql_query ("delete from pinjaman_detail where cif='$cif'");   
        $cek_tanggal= mysql_query ("SELECT COUNT(tgl_libur) as libur from param_libur
        INNER join pinjaman_detail_c on pinjaman_detail_c.tgl_jatuh_tempo = param_libur.tgl_libur where id_pinjam='$no_anggota'");
        while($data_libur = mysql_fetch_array($cek_tanggal)){
            $libur =$data_libur['libur'];
           
           
        }
       
       
        if ($libur >0){
            @mysql_query ("delete from pinjaman_detail_c where id_pinjam='$no_anggota'");   
   
         
       
         $newdate3 = date ("Y-m-d", strtotime("+$libur WEEK" , strtotime ($maturity_date))) ;
         $cek = mysql_query("SELECT * FROM param_libur where tgl_libur='$newdate3'");
          if(mysql_num_rows($cek)==1){
       
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
                $detail_data= mysql_query("INSERT INTO pinjaman_detail (id,id_pinjam,cicilan,angsuran_pokok,bunga,tgl_jatuh_tempo,tgl_bayar,jumlah_bayar,ket,cif,unit,ao)
                values('$reff','$no_anggota','$xx','$pokok','$ijaroh','$awal_hari','$awal','0','','$cif','$unit','$cao')") or die(mysql_error());
                }   
                @mysql_query ("delete from pinjaman_detail where tgl_jatuh_tempo in (select tgl_libur from param_libur where tgl_libur=pinjaman_detail.tgl_jatuh_tempo)");
                $up=mysql_query("SET @count = 0;");
                $update=mysql_query ("UPDATE pinjaman_detail SET cicilan=@count:=@count+1 where id_pinjam='$no_anggota' ORDER BY cicilan");
                @mysql_query ("delete from pinjaman_detail where cicilan > '$Tenor' AND cif='$cif'");   
				@mysql_query ("update  from loan  set tgl_murab='$tgl_system',tgl_akad='$tgl_system',maturity_date='$newdate2' cif='$cif'"); 
                    }

           
            else{
                @mysql_query ("delete from pinjaman_detail_c where id_pinjam='$no_anggota'");   
                $awal_hari = $tgl_system;
                $akhir_hari = $maturity_date;
                $x1=0;
                while (strtotime($awal_hari) < strtotime($akhir_hari)){
                $x1 ++;
                $reff="$no_anggota$x1";
                $awal_hari= date ("Y-m-d", strtotime("+7 DAY", strtotime($awal_hari)));
                $update= mysql_query("INSERT INTO pinjaman_detail (id,id_pinjam,cicilan,angsuran_pokok,bunga,tgl_jatuh_tempo,tgl_bayar,jumlah_bayar,ket,cif,unit,ao)
                values('$reff','$no_anggota','$x1','$pokok','$ijaroh','$awal_hari','$awal','0','','$cif','$unit','$cao')") or die(mysql_error());
                           
                }
                @mysql_query ("delete from pinjaman_detail where tgl_jatuh_tempo in (select tgl_libur from param_libur where tgl_libur=pinjaman_detail.tgl_jatuh_tempo)");
                $up=mysql_query("SET @count = 0;");
                $update=mysql_query ("UPDATE pinjaman_detail SET cicilan=@count:=@count+1 where id_pinjam='$no_anggota' ORDER BY cicilan");
                @mysql_query ("delete from pinjaman_detail where cicilan > '$Tenor' AND cif='$cif'");   
				@mysql_query ("update loan set tgl_murab='$tgl_system',tgl_akad='$tgl_system',maturity_date='$newdate2' where cif='$cif'"); 

            }
   
           
    echo '<script>alert("Restruturisasi sudah direalisasi."); window.location="rest.php"</script>';     
    }

               
		
            elseif($pilih =='04') {

$tgl_wakal    = strtotime($mat);
        $murab1     = date("Y-m-d", strtotime("+7 day", $tgl_wakal));
        $tgl_m    = strtotime($murab1);
        $maturity_date_ress =  date("Y-m-d", strtotime("+$ft WEEK", $tgl_m));

$cek_tanggal= mysql_query ("SELECT COUNT(tgl_libur) as libur from param_libur where tgl_libur between '$mat' and '$maturity_date_ress' and Hari ='$hari'");
        while($data_libur = mysql_fetch_array($cek_tanggal)){
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

@mysql_query ("update loan set maturity_date ='$maturity_date_res', gol='1', deal_produk ='1' where cif ='$cif'");
@mysql_query ("delete from tunggakan where cif ='$cif'");
@mysql_query ("delete from temp_eod where cif ='$cif'")or die(mysql_error());

                    echo '<script>alert("Covid Updated ."); window.location="rest.php"</script>';
        }

   else {

                    echo '<script>alert("Maaf menu belum tersedia ."); window.location="rest.php"</script>';
        }

?>
