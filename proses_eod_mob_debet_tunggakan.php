<?php require_once __DIR__ . '/koneksi_db.php';

$con = db_ni();
if (mysqli_connect_errno()) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
    exit;
}
 
// Turn autocommit off
mysqli_autocommit($con,FALSE);
 
 
include "function.php";
$tanggal="2024-05-27";

 $cek= db_fetch_array(db_query($con,"select param_tgl from param_tgl WHERE nama_param_tgl ='EOD_MOB'"));
$tanggal = $cek['param_tgl']; 
//$tanggal="2026-04-14";         
$select = db_query ($con,"Select tunggakan_mob.cif as cif,loan.angsuran,loan.cao,Cust_Short_name,loan.os as os,deal_produk,loan.saldo_margin as saldo_margin,
no_anggota as norek ,code_kel,status_app,loan.pokok,run_tenor,loan.unit, loan.bulat as bulat,loan.ijaroh as ijaroh,nominal
from tunggakan_mob left join loan on loan.cif = tunggakan_mob.cif where nominal > 0 and tgl_tagih ='$tanggal' ");
while($data = db_fetch_array($select)){
 
        $norek=$data['norek'];
        $no_anggota=$data['norek'];
        $unit=$data ['unit'];
        $userid=$data['userid'];
        $angsuran=$data ['angsuran'];
        $saldo_margin=$data ['saldo_margin'];
        $nominal=$data ['nominal'];
        $pokok=$data ['pokok'];
        $ijaroh=$data ['ijaroh'];
        $nisbah_mus=$data['ijaroh'];
        $code_kel=$data['code_kel'];
        $ke = $data['run_tenor'];
        $cao=$data['cao'];
        $ciff=$data['cif'];
        $cif=$data['cif'];
        $deal_produk=$data['deal_produk'];

        $os=$data['os'];
        $status_app=$data['status_app'];
        $run_tenor=$data['run_tenor'];
        $pb=$run_tenor + 1;
        $next_tenor=$run_tenor + 2;
 
        $angsuran=$data['angsuran'];
        $bulat=$data['bulat'];
        $nama=mysqli_real_escape_string($con,($data['Cust_Short_name']));
        $kodetr=8;
        $kodetr=acakangkahuruf($kodetr);
        $kode_transaksi="BU/".$unit.$kodetr;      
        
   
        $ket="Setor Tagihan $nama $cif";
        $ket2="Setoran Tagihan $nama";
        $ket3="Tunggakan Tagihan $nama";
         
       
        $wajib_kh=$bulat - $angsuran;
        $cek_next= db_fetch_array(db_query($con,"select cicilan,tgl_jatuh_tempo from pinjaman_detail WHERE cicilan='$next_tenor' AND id_pinjam ='$norek'"));
        $next_schedule=$cek_next['tgl_jatuh_tempo'];
        $next_cicilan=$cek_next['cicilan'];
        $row_sal= db_fetch_array(db_query($con,"select(sum(KREDIT) - sum(DEBIT))as saldo from simpanan WHERE cif ='$cif'")) ;
        $sisa=$row_sal['saldo'];
        $row_sal= db_fetch_array(db_query($con,"select(sum(KREDIT) - sum(DEBIT))as blok from simpanan WHERE type in ('05','02','04','10') and cif ='$cif'"));
        $blok=$row_sal['blok'];
        

        $minggon = $bulat-$angsuran;

        //$total_twm = $minggon * $run_tenor;
        $total_twm=0;


        $saldo=$sisa-$total_twm-$blok;
if($deal_produk=='2'){
         
    $angsuran=$angsuran + 2500;
    $ijaroh = $ijaroh + 2500;



}elseif($deal_produk=='4'){

    $angsuran=$angsuran + 2500;
    $ijaroh = $ijaroh + 2500;

}else{
     
         
$angsuran=$angsuran ;
    $ijaroh = $ijaroh;


}
        
$saldo=$sisa-$blok;
$urut = $ke_tenor + 1;
if ($os>=$angsuran){
$angsuran=$angsuran;
}else{
$angsuran=$os;
}        

if ($saldo_margin>=$ijaroh){
$ijaroh=$ijaroh;
}else{
$ijaroh=$saldo_margin;
}        
$new_id="$no_anggota$urut";

$kodet=7;
$kodet=acakangkahuruf($kodet);
$kode_tran="BU/".$unit .$kodet;
if($status_app == 'REST'){


if ($saldo < $angsuran){    
        db_query($con,"Update loan set next_schedule='$next_schedule1-1',ke='$ke_tenor'+1 where cif  ='$cif'");
                         
        //mysql_query("insert  into tunggakan (tgl_tunggak,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,blok)VALUES('$tanggal','$no_anggota','$unit','$cif','$code_kel','0','01','$pokok','$userid','$ket','$cao','') ") or die(mysql_error("GAGAL INPUT"));
       


}else{

$cek_tunggak= db_fetch_array(db_query($con,"select sum(KREDIT)- sum(DEBIT) as tunggak from tunggakan WHERE cif='$cif'"));
    $ada_tunggak=$cek_tunggak['tunggak'];    
    if($ada_tunggak>0){
db_query($con,"insert  into tunggakan (tgl_tunggak,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,blok)VALUES('$tanggal','$no_anggota','$unit','$cif','$code_kel','$pokok','01','0','$userid','$ket','$cao','') ");

db_query($con,"insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif)VALUES('','$tanggal','$unit','$no_anggota','0','$pokok','03','$ket','$userid','REALISASI POKOK','$cif')");
       
        db_query($con,"Update  loan set next_schedule ='$next_schedule',os =os-'$angsuran',saldo_margin=saldo_margin-'$ijaroh',run_tenor='$pb',ke='$ke_tenor'+1 ,last_payment='$tanggal' where cif  ='$cif'");
        db_query($con,"insert  into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,tgl_input,kode_transaksi)VALUES('$tanggal','$no_anggota','$unit','$cif','$code_kel','$angsuran','03','0','$userid','$ket','$cao','$tgl_input','$kode_tran') ");
        //mysql_query("Update  tabungan_detail set saldo =(select(sum(KREDIT)-SUM(DEBIT)) as saldo from simpanan where NOREK='" $norek "') where no_anggota  ='" $norek "'") or die(mysql_error("GAGAL INPUT"));
        db_query($con,"Update  pinjaman_detail set jumlah_bayar ='$angsuran',tgl_bayar='$tanggal' where id_pinjam  ='$norek' and cicilan='$pb'");
        db_query($con,"Update  pinjaman_detail_jatpo set jumlah_bayar ='$angsuran',tgl_bayar='$tanggal' where id_pinjam  ='$norek' and cicilan='$pb'");
        //mysql_query("Update  tunggakan set pokok ='0',margin='0' where cif  ='$cif' and ke='$ke_tenor'") or die(mysql_error("GAGAL INPUT"));
        //mysql_query("insert  into tagihan (id_reff,code_kel,no_anggota,bagi_hasil,pokok,tagihan,bulat,tagihan_ke,sisa_os,tgl_realisasi,status)VALUES('','$code_kel','$no_anggota','$ijaroh','$pokok','$angsuran','$bulat','$ke','$os','$next_schedule','RENCANA') ") or die(mysql_error("GAGAL INPUT"));
        $jurnal_umum=db_fetch_array(db_query($con,"SELECT max(nomor_jurnal) FROM jurnal_umum where unit ='$unit' ORDER BY tanggal_selesai DESC"));
        $nomor_jurnal=$jurnal_umum[0]+1;
        //pencatatan di jurnal realasisi Piutang Murabahaha total jumlah Pokok dan margin
        //mysql_query("insert into jurnal_masuk(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values('$nomor_jurnal','$kode_transaksi','$tanggal','$unit')");
        //mysql_query("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
        //values('$unit','$kode_transaksi','1120000','$tanggal','KAS MASUK','$ket','$bulat','$userid')");
        db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
        values('$unit','$kode_tran','2101000','$tanggal','Bukti SYSTEM','$ket','$angsuran','$userid')");
       
        db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
        values('$unit','$kode_tran','1411000','$tanggal','Bukti SYSTEM','$ket','$angsuran','$userid')");
 
       
   



}else{

        db_query($con,"insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif)VALUES('','$tanggal','$unit','$no_anggota','0','$pokok','03','$ket','$userid','REALISASI POKOK','$cif')");
       
        db_query($con,"Update  loan set next_schedule ='$next_schedule',os =os-'$angsuran',saldo_margin=saldo_margin-'$ijaroh',run_tenor='$pb',ke='$ke_tenor'+1 ,last_payment='$tanggal' where cif  ='$cif'");
        db_query($con,"insert  into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,tgl_input,kode_transaksi)VALUES('$tanggal','$no_anggota','$unit','$cif','$code_kel','$angsuran','03','0','$userid','$ket','$cao','$tgl_input','$kode_tran') ");
        //mysql_query("Update  tabungan_detail set saldo =(select(sum(KREDIT)-SUM(DEBIT)) as saldo from simpanan where NOREK='" $norek "') where no_anggota  ='" $norek "'") or die(mysql_error("GAGAL INPUT"));
        db_query($con,"Update  pinjaman_detail set jumlah_bayar ='$angsuran',tgl_bayar='$tanggal' where id_pinjam  ='$norek' and cicilan='$pb'");
        db_query($con,"Update  pinjaman_detail_jatpo set jumlah_bayar ='$angsuran',tgl_bayar='$tanggal' where id_pinjam  ='$norek' and cicilan='$pb'");
        //mysql_query("Update  tunggakan set pokok ='0',margin='0' where cif  ='$cif' and ke='$ke_tenor'") or die(mysql_error("GAGAL INPUT"));
        //mysql_query("insert  into tagihan (id_reff,code_kel,no_anggota,bagi_hasil,pokok,tagihan,bulat,tagihan_ke,sisa_os,tgl_realisasi,status)VALUES('','$code_kel','$no_anggota','$ijaroh','$pokok','$angsuran','$bulat','$ke','$os','$next_schedule','RENCANA') ") or die(mysql_error("GAGAL INPUT"));
        $jurnal_umum=db_fetch_array(db_query($con,"SELECT max(nomor_jurnal) FROM jurnal_umum where unit ='$unit' ORDER BY tanggal_selesai DESC"));
        $nomor_jurnal=$jurnal_umum[0]+1;
        //pencatatan di jurnal realasisi Piutang Murabahaha total jumlah Pokok dan margin
        //mysql_query("insert into jurnal_masuk(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values('$nomor_jurnal','$kode_transaksi','$tanggal','$unit')");
        //mysql_query("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
        //values('$unit','$kode_transaksi','1120000','$tanggal','KAS MASUK','$ket','$bulat','$userid')");
        db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
        values('$unit','$kode_tran','2101000','$tanggal','Bukti SYSTEM','$ket','$angsuran','$userid')");
       
        db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
        values('$unit','$kode_tran','1411000','$tanggal','Bukti SYSTEM','$ket','$angsuran','$userid')");
 
       
   
 
   }
}
}elseif($status_app=="REST_POKOK_MARGIN"){

db_query($con,"INSERT INTO pinjaman_detail_rencana(id,id_pinjam,cicilan,angsuran_pokok,bunga,tgl_jatuh_tempo,tgl_bayar,jumlah_bayar,ket,cif,unit,ao)
            VALUES('$new_id','$no_anggota','$urut','$pokok','$ijaroh','$next_schedule1','','','','$cif','$unit','$cao')");
         

if ($saldo < $angsuran){    
        db_query($con,"Update loan set next_schedule='$next_schedule1-1',ke='$ke_tenor'+1 where cif  ='$cif'");
        $ke_n= mysql_fetch_array(mysql_query("select ke from loan WHERE cif ='$cif'"));
        $ke_lagi=$ke_n['ke'];
         
        //mysql_query("insert  into tunggakan (tgl_tunggak,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,blok)VALUES('$tanggal','$no_anggota','$unit','$cif','$code_kel','0','01','$pokok','$userid','$ket','$cao','') ") or die(mysql_error("GAGAL INPUT"));
        //mysql_query("insert  into tunggakan (tgl_tunggak,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,blok)VALUES('$tanggal','$no_anggota','$unit','$cif','$code_kel','0','02','$ijaroh','$userid','$ket','$cao','') ") or die(mysql_error("GAGAL INPUT"));
         
       
 
}    
else{
$cek_tunggak= db_fetch_array(db_query($con,"select sum(KREDIT)- sum(DEBIT) as tunggak from tunggakan WHERE cif='$cif'"));
    $ada_tunggak=$cek_tunggak['tunggak'];    
    if($ada_tunggak>0){
db_query($con,"insert  into tunggakan (tgl_tunggak,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,blok)VALUES('$tanggal','$no_anggota','$unit','$cif','$code_kel','$pokok','01','0','$userid','$ket','$cao','') ");
        mysql_query("insert  into tunggakan (tgl_tunggak,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,blok)VALUES('$tanggal','$no_anggota','$unit','$cif','$code_kel','$ijaroh','02','0','$userid','$ket','$cao','') ");
db_query($con,"insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif)VALUES('','$tanggal','$unit','$no_anggota','0','$pokok','03','$ket','$userid','REALISASI POKOK','$cif')");
        db_query($con,"insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif)VALUES('','$tanggal','$unit','$no_anggota','0','$ijaroh','03','$ket','$userid','REALISASI BAGHAS','$cif')");

        db_query($con,"Update  loan set next_schedule ='$next_schedule',os =os-'$angsuran',saldo_margin=saldo_margin-'$ijaroh',run_tenor='$pb',ke='$ke_tenor'+1,last_payment='$tanggal' where cif  ='$cif'");
         

         
db_query($con,"insert  into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,blok,tgl_input,kode_transaksi)VALUES('$tanggal','$no_anggota','$unit','$cif','$code_kel','$angsuran','03','0','$userid','$ket','','$cao','2','$tgl_input','$kode_tran') ");
        $jurnal_umum=mysql_fetch_array(mysql_query("SELECT max(nomor_jurnal) FROM jurnal_umum where unit ='$unit' ORDER BY tanggal_selesai DESC"));
        $nomor_jurnal=$jurnal_umum[0]+1;

db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
        values('$unit','$kode_tran','2101000','$tanggal','Bukti SYSTEM','$ket','$angsuran','$userid')");

         db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
        values('$unit','$kode_tran','1411000','$tanggal','Bukti SYSTEM','$ket','$angsuran','$userid')");
         
        db_query($con,"insert into jurnal_umum(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values('$nomor_jurnal','$kode_tran','$tanggal','$unit')");
        //Realisasi Piutang Margin    
        db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                    values('$unit','$kode_tran','1421000','$tanggal','Bukti SYSTEM','$ket','$ijaroh','$userid')");
        //Realiasasi Murabahah Debit pada Piutang Murabahah total jumlah Pokok dan margin
        db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
        values('$unit','$kode_tran','41002','$tanggal','Bukti SYSTEM','$ket','$ijaroh','$userid')");


}else{

        db_query($con,"insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif)VALUES('','$tanggal','$unit','$no_anggota','0','$pokok','03','$ket','$userid','REALISASI POKOK','$cif')");
        db_query($con,"insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif)VALUES('','$tanggal','$unit','$no_anggota','0','$ijaroh','03','$ket','$userid','REALISASI BAGHAS','$cif')");

        db_query($con,"Update  loan set next_schedule ='$next_schedule',os =os-'$angsuran',saldo_margin=saldo_margin-'$ijaroh',run_tenor='$pb',ke='$ke_tenor'+1,last_payment='$tanggal' where cif  ='$cif'");
        db_query($con,"insert  into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,tgl_input,kode_transaksi)VALUES('$tanggal','$no_anggota','$unit','$cif','$code_kel','$angsuran','03','0','$userid','$ket','$cao','$tgl_input','$kode_tran') ");
        $jurnal_umum=db_fetch_array(db_query($con,"SELECT max(nomor_jurnal) FROM jurnal_umum where unit ='$unit' ORDER BY tanggal_selesai DESC"));
        $nomor_jurnal=$jurnal_umum[0]+1;
db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
        values('$unit','$kode_tran','2101000','$tanggal','Bukti SYSTEM','$ket','$angsuran','$userid')");

         db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
        values('$unit','$kode_tran','1411000','$tanggal','Bukti SYSTEM','$ket','$angsuran','$userid')");
         
        db_query($con,"insert into jurnal_umum(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values('$nomor_jurnal','$kode_tran','$tanggal','$unit')");
        //Realisasi Piutang Margin    
        db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                    values('$unit','$kode_tran','1421000','$tanggal','Bukti SYSTEM','$ket','$ijaroh','$userid')");
        //Realiasasi Murabahah Debit pada Piutang Murabahah total jumlah Pokok dan margin
        db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
        values('$unit','$kode_tran','41002','$tanggal','Bukti SYSTEM','$ket','$ijaroh','$userid')");
       


        }
    }
  
     
}elseif($status_app=="MUSYAROKAH"){
$cek_omzet= db_fetch_array(db_query($con,"SELECT nominal from omzet WHERE tanggal ='$tanggal' and cif ='$cif'"));
        			$omzet=$cek_omzet['nominal']; 

					$cek_nisbah= db_fetch_array(db_query($con,"SELECT nisbah from nisbah WHERE cif ='$cif'"));
        			$nisbah=$cek_nisbah['nisbah']; 

					$porsi= $omzet * $nisbah;
                    $angsuran_pokok = $angsuran - $nisbah_mus - 2500;
                     db_query ($con,"Update  pinjaman_detail_mus set nisbah_anggota ='$nisbah_mus',tgl_bayar='$tanggal' where id_pinjam  ='$norek' and cicilan='$pb'");
                       
if ($saldo < $angsuran){    
        db_query($con,"Update loan set next_schedule='$next_schedule1-1',ke='$ke_tenor'+1 where cif  ='$cif'");
        $ke_n= mysql_fetch_array(mysql_query("select ke from loan WHERE cif ='$cif'"));
        $ke_lagi=$ke_n['ke'];
         
        //mysql_query("insert  into tunggakan (tgl_tunggak,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,blok)VALUES('$tanggal','$no_anggota','$unit','$cif','$code_kel','0','01','$pokok','$userid','$ket','$cao','') ") or die(mysql_error("GAGAL INPUT"));
        //mysql_query("insert  into tunggakan (tgl_tunggak,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,blok)VALUES('$tanggal','$no_anggota','$unit','$cif','$code_kel','0','02','$ijaroh','$userid','$ket','$cao','') ") or die(mysql_error("GAGAL INPUT"));
         
       
 
}else{

                          $jurnal_umum=db_fetch_array(db_query($con,"SELECT max(nomor_jurnal) FROM jurnal_umum where unit ='$unit' ORDER BY tanggal_selesai DESC"));
                        $nomor_jurnal=$jurnal_umum[0]+1;                 
                         
                        if($ada_tunggak>0){
                            db_query($con,"insert  into tunggakan (tgl_tunggak,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,blok)VALUES('$tanggal','$norek','$unit','$cif','$code_kel','$angsuran_pokok','01','0','$userid','$ket','$cao','') ") ;
                            //mysqli_query($con,"insert  into tunggakan (tgl_tunggak,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,blok)VALUES('$tanggal','$norek','$unit','$cif','$code_kel','$ijaroh','02','0','$userid','$ket','$cao','') ");
                            db_query($con,"insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif)VALUES('','$tanggal','$unit','$norek','0','$angsuran_pokok','03','$ket2','$userid','REALISASI POKOK','$cif')") ;
                            db_query($con,"insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif)VALUES('','$tanggal','$unit','$norek','0','$ijaroh','03','$ket2','$userid','REALISASI BAGHAS','$cif')");
                            db_query($con,"Update  loan set next_schedule ='$next_schedule',os =os-'$angsuran_pokok',run_tenor='$pb',ke='$ke_tenor'+1,last_payment='$tanggal' where cif  ='$cif'");
                                             
                     
                            db_query($con,"insert  into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,blok,tgl_input,kode_transaksi)VALUES('$tanggal','$norek','$unit','".$cif."','$code_kel','$angsuran','03','0','$userid','$ket2','','$cao','2','$tgl_input','$kode_tran') ");
                                                                     
                            db_query($con,"insert into jurnal_umum(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values('$nomor_jurnal','$kode_tran','$tanggal','$unit')");
                             
                            db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                                values('$unit','$kode_tran','2101000','$tanggal','Bukti SYSTEM','$ket2','$angsuran','$userid')");                         
                            //Realisasi Piutang Margin     
                            db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                                values('$unit','$kode_tran','1472000','$tanggal','Bukti SYSTEM','$ket2','$angsuran_pokok','$userid')");
                            //Realiasasi Murabahah Debit pada Piutang Murabahah total jumlah Pokok dan margin 
                            db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                                values('$unit','$kode_tran','41062','$tanggal','Bukti SYSTEM','$ket','$nisbah_mus','$userid')");
                            db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                                values('$unit','$kode_tran','2867000','$tanggal','Bukti SYSTEM','$ket','2500','$userid')");
               

                                  
 
                             
                            }else{
                             
                            db_query($con,"insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif)VALUES('','$tanggal','$unit','$no_anggota','0','$angsuran_pokok','03','$ket2','$userid','REALISASI POKOK','$cif')");
                            db_query($con,"insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif)VALUES('','$tanggal','$unit','$no_anggota','0','$ijaroh','03','$ket2','$userid','REALISASI BAGHAS','".$cif."')");
                             
                            db_query($con,"Update  loan set next_schedule ='$next_schedule',os =os-'$angsuran_pokok',run_tenor='$pb',ke='$ke_tenor'+1,last_payment='$tanggal' where cif  ='$cif'");
                            db_query($con,"insert  into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,tgl_input,kode_transaksi)VALUES('$tanggal','$norek','$unit','".$ciff."','$code_kel','$angsuran','03','0','$userid','$ket','$cao','$tgl_input','$kode_tran') ") ;
                            db_query($con,"insert into jurnal_umum(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values('$nomor_jurnal','$kode_tran','$tanggal','$unit')");
             

                            
                            db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                                values('$unit','$kode_tran','2101000','$tanggal','Bukti SYSTEM','$ket2','$angsuran','$userid')");                         
                            //Realisasi Piutang Margin     
                            db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                                values('$unit','$kode_tran','1472000','$tanggal','Bukti SYSTEM','$ket2','$angsuran_pokok','$userid')");
                            //Realiasasi Murabahah Debit pada Piutang Murabahah total jumlah Pokok dan margin 
                            db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                                values('$unit','$kode_tran','41062','$tanggal','Bukti SYSTEM','$ket2','$nisbah_mus','$userid')");
                            db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                                values('$unit','$kode_tran','2867000','$tanggal','Bukti SYSTEM','$ket2','2500','$userid')");
               

                            
                             
                             
                            }
}


}else{
    db_query($con,"INSERT INTO pinjaman_detail_rencana(id,id_pinjam,cicilan,angsuran_pokok,bunga,tgl_jatuh_tempo,tgl_bayar,jumlah_bayar,ket,cif,unit,ao)
            VALUES('$new_id','$no_anggota','$urut','$pokok','$ijaroh','$next_schedule1','','','','$cif','$unit','$cao')");
         

if ($saldo < $angsuran){    
    db_query($con,"Update loan set next_schedule='$next_schedule1-1',ke='$ke_tenor'+1 where cif  ='$cif'");
        $ke_n= db_fetch_array(db_query($con,"select ke from loan WHERE cif ='$cif'"));
        $ke_lagi=$ke_n['ke'];
         
        //mysql_query("insert  into tunggakan (tgl_tunggak,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,blok)VALUES('$tanggal','$no_anggota','$unit','$cif','$code_kel','0','01','$pokok','$userid','$ket','$cao','') ") or die(mysql_error("GAGAL INPUT"));
        //mysql_query("insert  into tunggakan (tgl_tunggak,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,blok)VALUES('$tanggal','$no_anggota','$unit','$cif','$code_kel','0','02','$ijaroh','$userid','$ket','$cao','') ") or die(mysql_error("GAGAL INPUT"));
         
 
}    
else{
$cek_tunggak= db_fetch_array(db_query($con,"select sum(KREDIT)- sum(DEBIT) as tunggak from tunggakan WHERE cif='$cif'"));
    $ada_tunggak=$cek_tunggak['tunggak'];    
    if($ada_tunggak>0){
db_query($con,"insert  into tunggakan (tgl_tunggak,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,blok)VALUES('$tanggal','$no_anggota','$unit','$cif','$code_kel','$pokok','01','0','$userid','$ket','$cao','') ");
        db_query($con,"insert  into tunggakan (tgl_tunggak,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,blok)VALUES('$tanggal','$no_anggota','$unit','$cif','$code_kel','$ijaroh','02','0','$userid','$ket','$cao','') ");
        db_query($con,"insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif)VALUES('','$tanggal','$unit','$no_anggota','0','$pokok','03','$ket','$userid','REALISASI POKOK','$cif')");
        db_query($con,"insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif)VALUES('','$tanggal','$unit','$no_anggota','0','$ijaroh','03','$ket','$userid','REALISASI BAGHAS','$cif')");

        db_query($con,"Update  loan set next_schedule ='$next_schedule',os =os-'$angsuran',saldo_margin=saldo_margin-'$ijaroh',run_tenor='$pb',ke='$ke_tenor'+1,last_payment='$tanggal' where cif  ='$cif'");
         
if($deal_produk =='2'){
$margin = $ijaroh - 2500;
   db_query($con,"insert  into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,blok,tgl_input,kode_transaksi)VALUES('$tanggal','$no_anggota','$unit','$cif','$code_kel','$angsuran','03','0','$userid','$ket','','$cao','2','$tgl_input','$kode_tran') ");
       
        $jurnal_umum=db_fetch_array(db_query($con,"SELECT max(nomor_jurnal) FROM jurnal_umum where unit ='$unit' ORDER BY tanggal_selesai DESC"));
        $nomor_jurnal=$jurnal_umum[0]+1;
      
        db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
        values('$unit','$kode_tran','1413000','$tanggal','Bukti SYSTEM','$ket','$angsuran','$userid')");
         
        db_query($con,"insert into jurnal_umum(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values('$nomor_jurnal','$kode_tran','$tanggal','$unit')");
        //Realisasi Piutang Margin    
        db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                    values('$unit','$kode_tran','1423000','$tanggal','Bukti SYSTEM','$ket','$ijaroh','$userid')");
        //Realiasasi Murabahah Debit pada Piutang Murabahah total jumlah Pokok dan margin
        db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
        values('$unit','$kode_tran','41002','$tanggal','Bukti SYSTEM','$ket','$margin','$userid')");
        db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
        values('$unit','$kode_tran','2101000','$tanggal','Bukti SYSTEM','$ket','$angsuran','$userid')");
db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                                values('$unit','$kode_tran','2867000','$tanggal','Bukti SYSTEM','$ket2','2500','$userid')");

}elseif($deal_produk =='4'){

$margin = $ijaroh - 2500;
   db_query($con,"insert  into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,blok,tgl_input,kode_transaksi)VALUES('$tanggal','$no_anggota','$unit','$cif','$code_kel','$angsuran','03','0','$userid','$ket','','$cao','2','$tgl_input','$kode_tran') ");
       
        $jurnal_umum=db_fetch_array(db_query($con,"SELECT max(nomor_jurnal) FROM jurnal_umum where unit ='$unit' ORDER BY tanggal_selesai DESC"));
        $nomor_jurnal=$jurnal_umum[0]+1;
      
        db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
        values('$unit','$kode_tran','1413000','$tanggal','Bukti SYSTEM','$ket','$angsuran','$userid')");
         
        db_query($con,"insert into jurnal_umum(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values('$nomor_jurnal','$kode_tran','$tanggal','$unit')");
        //Realisasi Piutang Margin    
        db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                    values('$unit','$kode_tran','1423000','$tanggal','Bukti SYSTEM','$ket','$ijaroh','$userid')");
        //Realiasasi Murabahah Debit pada Piutang Murabahah total jumlah Pokok dan margin
        db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
        values('$unit','$kode_tran','41002','$tanggal','Bukti SYSTEM','$ket','$margin','$userid')");
        db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
        values('$unit','$kode_tran','2101000','$tanggal','Bukti SYSTEM','$ket','$angsuran','$userid')");
db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                                values('$unit','$kode_tran','41002','$tanggal','Bukti SYSTEM','$ket2','2500','$userid')");

}else{

   db_query($con,"insert  into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,blok,tgl_input,kode_transaksi)VALUES('$tanggal','$no_anggota','$unit','$cif','$code_kel','$angsuran','03','0','$userid','$ket','','$cao','2','$tgl_input','$kode_tran') ");
       
        $jurnal_umum=db_fetch_array(db_query($con,"SELECT max(nomor_jurnal) FROM jurnal_umum where unit ='$unit' ORDER BY tanggal_selesai DESC"));
        $nomor_jurnal=$jurnal_umum[0]+1;
      
        db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
        values('$unit','$kode_tran','1413000','$tanggal','Bukti SYSTEM','$ket','$angsuran','$userid')");
         
        db_query($con,"insert into jurnal_umum(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values('$nomor_jurnal','$kode_tran','$tanggal','$unit')");
        //Realisasi Piutang Margin    
        db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                    values('$unit','$kode_tran','1423000','$tanggal','Bukti SYSTEM','$ket','$ijaroh','$userid')");
        //Realiasasi Murabahah Debit pada Piutang Murabahah total jumlah Pokok dan margin
        db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
        values('$unit','$kode_tran','41002','$tanggal','Bukti SYSTEM','$ket','$ijaroh','$userid')");
        db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
        values('$unit','$kode_tran','2101000','$tanggal','Bukti SYSTEM','$ket','$angsuran','$userid')");




}
         
     
}else{

    db_query($con,"insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif)VALUES('','$tanggal','$unit','$no_anggota','0','$pokok','03','$ket','$userid','REALISASI POKOK','$cif')");
    db_query($con,"insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif)VALUES('','$tanggal','$unit','$no_anggota','0','$ijaroh','03','$ket','$userid','REALISASI BAGHAS','$cif')");

    db_query($con,"Update  loan set next_schedule ='$next_schedule',os =os-'$angsuran',saldo_margin=saldo_margin-'$ijaroh',run_tenor='$pb',ke='$ke_tenor'+1,last_payment='$tanggal' where cif  ='$cif'");
    db_query($con,"insert  into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,tgl_input,kode_transaksi)VALUES('$tanggal','$no_anggota','$unit','$cif','$code_kel','$angsuran','03','0','$userid','$ket','$cao','$tgl_input','$kode_tran') ");
       
        $jurnal_umum=db_fetch_array(db_query($con,"SELECT max(nomor_jurnal) FROM jurnal_umum where unit ='$unit' ORDER BY tanggal_selesai DESC"));
        $nomor_jurnal=$jurnal_umum[0]+1;
       
        db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
        values('$unit','$kode_tran','1413000','$tanggal','Bukti SYSTEM','$ket','$angsuran','$userid')");
         
        db_query($con,"insert into jurnal_umum(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values('$nomor_jurnal','$kode_tran','$tanggal','$unit')");
        //Realisasi Piutang Margin    
        db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                    values('$unit','$kode_tran','1423000','$tanggal','Bukti SYSTEM','$ket','$ijaroh','$userid')");
        //Realiasasi Murabahah Debit pada Piutang Murabahah total jumlah Pokok dan margin
        db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
        values('$unit','$kode_tran','41002','$tanggal','Bukti SYSTEM','$ket','$ijaroh','$userid')");
        db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
        values('$unit','$kode_tran','2101000','$tanggal','Bukti SYSTEM','$ket','$angsuran','$userid')");
  

        }
    }
  
}
    
    
    }

 
    if (!mysqli_commit($con)) {
        echo "Transaksi Gagal commit Silahkan ulangi lagi";
        exit();
      }
echo "<script>alert('Setoran tunggakan sukses di simpan');</script>";

     
    
 
?>
