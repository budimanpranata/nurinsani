<?php require_once __DIR__ . '/koneksi_db.php';
include "function.php";

$con = db_ni();
if (mysqli_connect_errno()) {
	echo "Failed to connect to MySQL: " . mysqli_connect_error();
	exit;
  }
// //jtg 1
// '2008',
// '2005',
// '2002',
// '2003',
// '2001',
// '2049',

// //jtg 2
// '2010',
// '2009',
// '2006',
// '2007',
// '2004',
// '2027',

// //jtg 3
// '2012',
// '2013',
// '2011',
// '2018',
// '2019',
// '2029',

// //jtg 4
// '2014',
// '2015',
// '2016',
// '2017',
// '2020'

// //jtg 5
// '2021',
// '2022',
// '2023',
// '2025',
// '2024',
// '2026',

// //jtg 6
// '2028',
// '2030',
// '2031',
// '2032',
// '2033',

// //jtg 7
// '2034',
// '2035',
// '2036',
// '2037',
// '2038',

// //jtg 8
// '2039',
// '2040',
// '2041',
// '2042',
// '2043',

// //jtg 9 
// '2044',
// '2045',
// '2046',
// '2047',
// '2048',


// Turn autocommit off
mysqli_autocommit($con,FALSE);

$select = db_query ($con,"Select DATE_ADD(tgl_tagih,INTERVAL 1 DAY) as tanggal_esok,
 tagihan_cs.cif,loan.angsuran,loan.cao,no_anggota,loan.code_kel,status_app,loan.pokok,
 run_tenor,loan.unit, bulat,loan.ijaroh as ijaroh,tgl_tagih,status_realisasi 
 from tagihan_cs left join loan on loan.cif = tagihan_cs.cif where status_realisasi ='PENDING' and tagihan_cs.unit in (

'2008',
'2005',
'2002',
'2003',
'2001',
'2049',
'2010',
'2009',
'2006',
'2007',
'2004',
'2027',
'2012',
'2013',
'2011',
'2018',
'2019',
'2029',
'2014',
'2015',
'2016',
'2017',
'2020'

) ");
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
$tanggal_eod=$data['tanggal_esok'];
$tanggal=$data['tgl_tagih'];
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

$cek_tunggak= db_fetch_array(db_query($con,"select (sum(KREDIT) - sum(DEBIT))as tunggak from tunggakan WHERE CIF ='$cif'")) or die(mysqli_error($con)) ;
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
            db_query ($con,"Update  tagihan_cs set status_realisasi ='BAYAR'  where cif ='$cif' and tgl_tagih ='$tanggal'");
    }else{

        db_query ($con,"insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif)VALUES('','$tanggal','$unit','$norek','0','$angsuran_pokok','03','$ket2','$userid','REALISASI POKOK','$cif')");
        db_query ($con,"Update  loan set next_schedule ='$next_schedule',os =os-'$angsuran',saldo_margin=saldo_margin-'$ijaroh',run_tenor='$pb',ke='$ke_tenor'+1 ,last_payment='$tanggal' where cif  ='$cif'");
        db_query ($con,"insert  into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,tgl_input,kode_transaksi)VALUES('$tanggal','$norek','$unit','$cif','$code_kel','$angsuran','03','0','$userid','$ket2','$cao','$tgl_input','$kode_tran') ");
    
        db_query ($con,"Update  pinjaman_detail set jumlah_bayar ='$angsuran',tgl_bayar='$tanggal' where id_pinjam  ='$norek' and cicilan='$pb'");
        
        db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
        values('$unit','$kode_tran','2101000','$tanggal','Bukti SYSTEM','$ket2','$angsuran','$userid')");
        db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
        values('$unit','$kode_tran','1411000','$tanggal','Bukti SYSTEM','$ket2','$angsuran','$userid')");

        $update =db_query ($con,"Update  tagihan_cs set status_realisasi ='BAYAR'  where cif  ='$cif' and tgl_tagih ='$tanggal'");

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

            db_query($con,"Update  tagihan_cs set status_realisasi ='BAYAR'  where cif  ='$cif' and tgl_tagih ='$tanggal'");

}else{
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

    db_query ($con,"Update  tagihan_cs set status_realisasi ='BAYAR'  where cif  ='$cif' and tgl_tagih ='$tanggal'");
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

                db_query ($con,"Update  tagihan_cs set status_realisasi ='BAYAR'  where cif  ='$cif' and tgl_tagih ='$tanggal'");

          }else{

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

            db_query ($con,"Update  tagihan_cs set status_realisasi ='BAYAR'  where cif  ='$cif' and tgl_tagih ='$tanggal'");

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

            db_query ($con,"Update  tagihan_cs set status_realisasi ='BAYAR'  where cif  ='$cif' and tgl_tagih ='$tanggal'");

             
               }else{

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
    
                db_query ($con,"Update  tagihan_cs set status_realisasi ='BAYAR'  where cif  ='$cif' and tgl_tagih ='$tanggal'");
    
}       
            
      }




}else{
    db_query($con,"insert  into tunggakan (tgl_tunggak,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,blok)VALUES('$tanggal','$norek','$unit','$cif','$code_kel','0','01','$angsuran_pokok','$userid','$ket2','$cao','') ") ;
    db_query($con,"insert  into tunggakan (tgl_tunggak,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,blok)VALUES('$tanggal','$norek','$unit','$cif','$code_kel','0','02','$ijaroh','$userid','$ket2','$cao','') ");

    db_query ($con,"Update  tagihan_cs set status_realisasi ='NUNGGAK'  where cif  ='$cif' and tgl_tagih ='$tanggal'");
}


}

$update =db_query ($con,"Update  param_tgl  set param_tgl ='$tanggal_eod'  where nama_param_tgl  ='EOD'");

// $tanggal = $tanggal_eod;
//     $day = date('D', strtotime($tanggal));
//     $dayList = array(
//     	'Sun' => 'Minggu',
//     	'Mon' => 'Senin',
//     	'Tue' => 'Selasa',
//     	'Wed' => 'Rabu',
//     	'Thu' => 'Kamis',
//     	'Fri' => 'Jumat',
//     	'Sat' => 'Sabtu'
//     );

// $update =mysqli_query ($con,"insert into  `tagihan_cs`
// select 
// '' as id,
// unit as unit,
// '$tanggal_eod' as tgl_tagih,
// code_kel as code_kel,
// cif as cif,
// cao as cao,
// no_anggota as norek,
// pokok as angsuran_pokok, 
// ijaroh as angsuran_margin,
// angsuran as angsuran,
// '0' as bayar,
// 'PENDING' as status_realisasi
// from loan 
// where  hari ='$dayList[$day]' and os > 0 and tgl_murab not in ('$tanggal_eod')");


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
