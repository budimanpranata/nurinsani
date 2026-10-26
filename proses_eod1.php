<?php require_once __DIR__ . '/koneksi_db.php';
include "function.php";

$con = db_ni();
if (mysqli_connect_errno()) {
	echo "Failed to connect to MySQL: " . mysqli_connect_error();
	exit;
  }


// Turn autocommit off
mysqli_autocommit($con,FALSE);

$select = db_query ($con,"Select DATE_ADD(tgl_eod,INTERVAL 1 DAY) as tanggal, temp_eod.cif,loan.angsuran,loan.cao,no_anggota,code_kel,status_app,loan.pokok,run_tenor,loan.unit, bulat,loan.ijaroh as ijaroh,tgl_eod,temp_eod.status,status_eod from temp_eod left join loan on loan.cif = temp_eod.cif where status_eod ='PENDING' ");
while($data = db_fetch_array($select)){
   

$angsuran=$data['angsuran'];
$code_kel=$data['code_kel'];
$norek=$data['no_anggota'];

$cif=$data['cif'];
$angsuran_pokok=$data['pokok'];
$ijaroh=$data['ijaroh'];
$ft=$data['ft'];
$status_app=$data['status_app'];
$bulat=$data['bulat'];
$tanggal=$data['tanggal'];
//$tanggal="2022-03-21";
$run_tenor=$data['run_tenor'];
$unit=$data['unit'];
$userid=$unit;
$pb=$run_tenor + 1;
$next_tenor=$run_tenor + 2;
$ket2 ="Realisasi Angsuran   $cif";
$cao=$data['cao'];


$kodetrs=8;
$kodetrs=acakangkahuruf($kodetrs);
$kode_tran="EOD".$unit.$kodetrs;

$cek_tunggak= db_fetch_array(db_query($con,"select sum(KREDIT)- sum(DEBIT) as tunggak from tunggakan WHERE cif ='$cif"));
        $ada_tunggak=$cek_tunggak['tunggak']; 
		$ft=round(compat_num($ada_tunggak/$angsuran));  



$row_sal= db_fetch_array(db_query($con,"select(sum(KREDIT) - sum(DEBIT))as saldo from simpanan WHERE cif ='$cif'"));
                $sisa=$row_sal['saldo'];
                $row_sal= db_fetch_array(db_query($con,"select(sum(KREDIT) - sum(DEBIT))as blok from simpanan WHERE type in ('05','02','04') and cif ='$cif'")) ;
                $blok=$row_sal['blok'];         
$saldo=$sisa-$blok;



if($saldo>=$angsuran){


    switch ($status_app) {
        case "REST":

if($ft>0){
            $jurnal_umum=db_fetch_array(db_query($con,"SELECT max(nomor_jurnal) FROM jurnal_umum where unit ='$unit' ORDER BY tanggal_selesai DESC"));
            $nomor_jurnal=$jurnal_umum[0]+1;
            db_query($con,"insert  into tunggakan (tgl_tunggak,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,blok)VALUES('$tanggal','$norek','$unit','$cif','$code_kel','$angsuran_pokok','01','0','$userid','$ket2','$cao','') ") ;

            db_query ($con,"insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif)VALUES('','$tanggal','$unit','$norek','0','$angsuran_pokok','03','$ket2','$userid','REALISASI POKOK','$cif')");
            db_query ($con,"Update  loan set next_schedule ='$next_schedule',os =os-'$angsuran',saldo_margin=saldo_margin-'$ijaroh',run_tenor='$pb',ke='$ke_tenor'+1 ,last_payment='$tanggal' where cif  ='$cif'");
            db_query ($con,"insert  into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,tgl_input,kode_transaksi)VALUES('$tanggal','$norek','$unit','$cif','$code_kel','$angsuran','03','0','$userid','$ket2','$cao','$tgl_input','$kode_tran') ");
        
            db_query ($con,"Update  pinjaman_detail set jumlah_bayar ='$angsuran',tgl_bayar='$tanggal' where id_pinjam  ='$norek' and cicilan='$pb'");
            
            db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
            values('$unit','$kode_tran','2101000','$tanggal','Bukti SYSTEM','$ket2','$angsuran','$userid')");
            db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
            values('$unit','$kode_tran','1411000','$tanggal','Bukti SYSTEM','$ket2','$angsuran','$userid')");
            $update =db_query ($con,"Update  temp_eod set status_eod ='DONE'  where cif  ='$cif'");
    }else{

$update =db_query ($con,"Update  temp_eod set status_eod ='TIDAK NUNGGAK'  where cif  ='$cif'");
}

          break;
        case "REST_POKOK_MARGIN":

if($ft>0){

        $jurnal_umum=db_fetch_array(db_query($con,"SELECT max(nomor_jurnal) FROM jurnal_umum where unit ='$unit' ORDER BY tanggal_selesai DESC"));
        $nomor_jurnal=$jurnal_umum[0]+1;                 
        
        db_query($con,"insert  into tunggakan (tgl_tunggak,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,blok)VALUES('$tanggal','$norek','$unit','$cif','$code_kel','$angsuran_pokok','01','0','$userid','$ket2','$cao','') ") ;
        db_query($con,"insert  into tunggakan (tgl_tunggak,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,blok)VALUES('$tanggal','$norek','$unit','$cif','$code_kel','$ijaroh','02','0','$userid','$ket2','$cao','') ");

            db_query($con,"insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif)VALUES('','$tanggal','$unit','$norek','0','$angsuran_pokok','03','$ket2','$userid','REALISASI POKOK','$cif')");
            db_query($con,"insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif)VALUES('','$tanggal','$unit','$norek','0','$ijaroh','03','$ket2','$userid','REALISASI BAGHAS','$cif')");

            db_query($con,"Update  loan set next_schedule ='$next_schedule',os =os-'$angsuran',saldo_margin=saldo_margin-'$ijaroh',run_tenor='$pb',ke='$ke_tenor'+1,last_payment='$tanggal' where cif  ='$cif'");
            db_query($con,"insert  into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,tgl_input,kode_transaksi)VALUES('$tanggal','$norek','$unit','$cif','$code_kel','$angsuran','03','0','$userid','$ket2','$cao','$tgl_input','$kode_tran') ");
            
            db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
            values('$unit','$kode_tran','2101000','$tanggal','Bukti SYSTEM','$ket2','$angsuran','$userid')");

            db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
            values('$unit','$kode_tran','1411000','$tanggal','Bukti SYSTEM','$ket2','$angsuran','$userid')");
            
            db_query($con,"insert into jurnal_umum(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values('$nomor_jurnal','$kode_tran','$tanggal','$unit')");
            //Realisasi Piutang Margin     
            db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                        values('$unit','$kode_tran','1421000','$tanggal','Bukti SYSTEM','$ket2','$ijaroh','$userid')");
            //Realiasasi Murabahah Debit pada Piutang Murabahah total jumlah Pokok dan margin 
            db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
            values('$unit','$kode_tran','41002','$tanggal','Bukti SYSTEM','$ket2','$ijaroh','$userid')");

            $update =db_query ($con,"Update  temp_eod set status_eod ='DONE'  where cif  ='$cif'");

}else{

$update =db_query ($con,"Update  temp_eod set status_eod ='TIDAK NUNGGAK'  where cif  ='$cif'");
}

            
          
          break;
        case "MURAB":
            $jurnal_umum=db_fetch_array(db_query($con,"SELECT max(nomor_jurnal) FROM jurnal_umum where unit ='$unit' ORDER BY tanggal_selesai DESC"));
            $nomor_jurnal=$jurnal_umum[0]+1;                 
            
if($ft>0){

            db_query($con,"insert  into tunggakan (tgl_tunggak,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,blok)VALUES('$tanggal','$norek','$unit','$cif','$code_kel','$angsuran_pokok','01','0','$userid','$ket','$cao','') ") ;
            db_query($con,"insert  into tunggakan (tgl_tunggak,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,blok)VALUES('$tanggal','$norek','$unit','$cif','$code_kel','$ijaroh','02','0','$userid','$ket','$cao','') ");           
              
            db_query($con,"insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif)VALUES('','$tanggal','$unit','$no_anggota','0','$angsuran_pokok','03','$ket2','$userid','REALISASI POKOK','$cif')");
                db_query($con,"insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif)VALUES('','$tanggal','$unit','$no_anggota','0','$ijaroh','03','$ket2','$userid','REALISASI BAGHAS','$cif')");
                
                db_query($con,"Update  loan set next_schedule ='$next_schedule',os =os-'$angsuran',saldo_margin=saldo_margin-'$ijaroh',run_tenor='$pb',ke='$ke_tenor'+1,last_payment='$tanggal' where cif  ='$cif'");
                db_query($con,"insert  into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,tgl_input,kode_transaksi)VALUES('$tanggal','$norek','$unit','$cif','$code_kel','$angsuran','03','0','$userid','$ket2','$cao','$tgl_input','$kode_tran') ") ;
                db_query($con,"insert into jurnal_umum(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values('$nomor_jurnal','$kode_tran','$tanggal','$unit')");

                db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                    values('$unit','$kode_tran','1413000','$tanggal','Bukti SYSTEM','$ket2','$angsuran','$userid')");
                //Realisasi Piutang Margin     
                db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                    values('$unit','$kode_tran','1423000','$tanggal','Bukti SYSTEM','$ket2','$ijaroh','$userid')");
                //Realiasasi Murabahah Debit pada Piutang Murabahah total jumlah Pokok dan margin 
                db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                    values('$unit','$kode_tran','41002','$tanggal','Bukti SYSTEM','$ket2','$ijaroh','$userid')");
                db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                    values('$unit','$kode_tran','2101000','$tanggal','Bukti SYSTEM','$ket2','$angsuran','$userid')");

                    $update =db_query ($con,"Update  temp_eod set status_eod ='DONE'  where cif  ='$cif'");

          }else{

$update =db_query ($con,"Update  temp_eod set status_eod ='TIDAK NUNGGAK'  where cif  ='$cif'");
}       
                 
                      
          break;
        default:
        $jurnal_umum=db_fetch_array(db_query($con,"SELECT max(nomor_jurnal) FROM jurnal_umum where unit ='$unit' ORDER BY tanggal_selesai DESC"));
        $nomor_jurnal=$jurnal_umum[0]+1; 
if($ft>0){
                
        db_query($con,"insert  into tunggakan (tgl_tunggak,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,blok)VALUES('$tanggal','$norek','$unit','$cif','$code_kel','$angsuran_pokok','01','0','$userid','$ket','$cao','') ") ;
		db_query($con,"insert  into tunggakan (tgl_tunggak,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,blok)VALUES('$tanggal','$norek','$unit','$cif','$code_kel','$ijaroh','02','0','$userid','$ket','$cao','') ");
                     
            db_query($con,"insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif)VALUES('','$tanggal','$unit','$norek','0','$angsuran_pokok','03','$ket2','$userid','REALISASI POKOK','$cif')");
            db_query($con,"insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif)VALUES('','$tanggal','$unit','$norek','0','$ijaroh','03','$ket2','$userid','REALISASI BAGHAS','$cif')");
            
            db_query($con,"Update  loan set next_schedule ='$next_schedule',os =os-'$angsuran',saldo_margin=saldo_margin-'$ijaroh',run_tenor='$pb',ke='$ke_tenor'+1,last_payment='$tanggal' where cif  ='$cif'");
            db_query($con,"insert  into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,tgl_input,kode_transaksi)VALUES('$tanggal','$norek','$unit','$cif','$code_kel','$angsuran','03','0','$userid','$ket2','$cao','$tgl_input','$kode_tran') ") ;
            db_query($con,"insert into jurnal_umum(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values('$nomor_jurnal','$kode_tran','$tanggal','$unit')");

            db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                values('$unit','$kode_tran','1413000','$tanggal','Bukti SYSTEM','$ket2','$angsuran','$userid')");
            //Realisasi Piutang Margin     
            db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                values('$unit','$kode_tran','1423000','$tanggal','Bukti SYSTEM','$ket2','$ijaroh','$userid')");
            //Realiasasi Murabahah Debit pada Piutang Murabahah total jumlah Pokok dan margin 
            db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                values('$unit','$kode_tran','41002','$tanggal','Bukti SYSTEM','$ket2','$ijaroh','$userid')");
            db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                values('$unit','$kode_tran','2101000','$tanggal','Bukti SYSTEM','$ket2','$angsuran','$userid')");

                $update =db_query ($con,"Update  temp_eod set status_eod ='DONE'  where cif  ='$cif'");

             
               }else{

$update =db_query ($con,"Update  temp_eod set status_eod ='TIDAK NUNGGAK'  where cif  ='$cif'");
}       
            
      }




}else{
    $update =db_query ($con,"Update  temp_eod set status_eod ='SALDO TIDAK CUKUP'  where cif  ='$cif'");
}


}

$update =db_query ($con,"Update  param_tgl  set param_tgl ='$tanggal'  where nama_param_tgl  ='EOD'");


if (!mysqli_commit($con)) {
    echo "Transaksi Gagal commit Silahkan ulangi lagi";
    exit();
  }

if ($update){
echo "<script>alert('suksess!!!');</script>";



}else {
	echo "<script>alert('gagal!!!');</script>";
}
?>
