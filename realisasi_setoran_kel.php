<?php require_once __DIR__ . '/koneksi_db.php'; 
ini_set('display_errors',1);

session_start();
ob_start();
$userid=$_SESSION['userid'];
$tgl_system=$_SESSION['stgl_system'];
$tgl_input=date("Y-m-d");
$buss_date=$tgl_system;
//cek apakah user sudah login
if(!isset($_SESSION['stgl_system'])){
echo "<script>alert('Maaf,.tanggal system belum di setup');</script>";

   header("location:param_tgl.php");
 exit; }
//cek apakah user sudah login
if(!isset($_SESSION['userid'])){
   header("location:index.html");
 exit; }

//cek level user
if($_SESSION['level']!="unit"){
   header("location:index.html");
 exit; }



$con = db_ni();
if (mysqli_connect_errno()) {
	echo "Failed to connect to MySQL: " . mysqli_connect_error();
	exit;
  }

// Turn autocommit off
mysqli_autocommit($con,FALSE);



include "function.php";

$tanggal=$tgl_system;
$simpan=$_SESSION['simpans'];	
$baki=$_SESSION['bakis'];	
$nom=$_POST['bulat'];			
			
$checbox = $_POST['cekbox'];

var_dump($checbox);
if ($checbox) {
	for ($i=0; $i <sizeof($checbox);$i++){
		
		$kel =db_query ($con,"select anggota.cif,no_anggota as norek,run_tenor,last_payment,loan.code_kel,loan.unit,angsuran,status_app,loan.Cust_Short_name,loan.cao,bulat,tgl_murab,pokok,os,ijaroh,deal_produk FROM loan 
		left join anggota on anggota.cif=loan.cif
		where  loan.cif ='".$checbox[$i]."'");
		$data = db_fetch_array($kel);

		$norek=$data['norek'];
		$saldo_margin=$data['saldo_margin'];
		$pokok=$os-$saldo_margin;
		$unit=$data ['unit'];
		$angsuran=$data ['angsuran'];
		$angsuran_pokok=$data ['pokok'];
		$ijaroh=$data ['ijaroh'];
		$code_kel=$data['code_kel'];
		$ke = $data['run_tenor'];
		$cao=$data['cao'];
		$ciff=$data['cif'];
$last_payment=$data['last_payment'];
		$deal_produk=$data['deal_produk'];
$tgl_murab=$data['tgl_murab'];
		$os=$data['os'];
		$status_app=$data['status_app'];
		$run_tenor=$data['run_tenor'];
		$pb=$run_tenor + 1;
		$next_tenor=$run_tenor + 2;

		$angsuran=$data['angsuran'];
		$bulat=$data['bulat'];
		$cek_next= db_fetch_array(db_query($con,"select cicilan,tgl_jatuh_tempo from pinjaman_detail WHERE cicilan='$next_tenor' AND cif ='".$checbox[$i]."'"));
		$next_schedule=$cek_next['tgl_jatuh_tempo'];
		$next_cicilan=$cek_next['cicilan'];


		$cek_tunggak= db_fetch_array(db_query($con,"select sum(KREDIT)- sum(DEBIT) as tunggak from tunggakan WHERE cif ='".$checbox[$i]."'"));
        $ada_tunggak=$cek_tunggak['tunggak']; 
		$ft=round(compat_num($ada_tunggak/$angsuran));  
		
		if($deal_produk=='2'){
			$simpanan_minggon_asli=$bulat - $angsuran;
			$simpanan_minggon=$bulat - $angsuran - 2500;
			$simpan_duaribu = '2500';		
			$angsuran=$angsuran + 2500;
			$ijaroh = $ijaroh + 2500;


	$setoran=$nom[$i] - $simpanan_minggon_asli;

		}else{
			
				$simpanan_minggon=$bulat - $angsuran;
				$simpan_duaribu = '0';		

		$setoran=$nom[$i] - $simpanan_minggon;
		$angsuran=$angsuran ;
			$ijaroh = $ijaroh;


		}
		

$tgl1=new DateTime($tgl_murab);
$tgl2=new DateTime($tanggal);
$jarak = $tgl2->diff($tgl1); 
$jarak =$jarak->d;




		





		$nama=mysqli_real_escape_string($con,($data['Cust_Short_name']));
		$ket ="Setoran an $nama $cif";
		$ket2 ="Angsuran  an $nama $cif";
		$status_app=$data['status_app'];
		
	
		

 			$kodetr=8;
            $kodetr=acakangkahuruf($kodetr);
            $kode_trans="BU/".$unit.$kodetr;

			$kodetrs=8;
            $kodetrs=acakangkahuruf($kodetrs);
            $kode_tran="BU/".$unit.$kodetrs;
		
		if ($nom[$i] =='0'){
			db_query ($con,"insert into tunggakan (tgl_tunggak,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,blok)VALUES('$tanggal','$norek','$unit','".$checbox[$i]."','$code_kel','0','01','$angsuran_pokok','$userid','$ket','','$cao','') ");
			db_query ($con,"insert into tunggakan (tgl_tunggak,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,blok)VALUES('$tanggal','$norek','$unit','".$checbox[$i]."','$code_kel','0','02','$ijaroh','$userid','$ket','','$cao','') ");
			
			db_query ($con,"insert  into temp_eod (cif,unit,setoran,angsuran,pokok,margin,ft,pb,tgl_setor,tgl_eod,status,status_eod)VALUES
													('".$checbox[$i]."','$unit','$setoran','$angsuran','$angsuran_pokok','$ijaroh','$ft','','$tanggal','$tanggal','NUNGGAK','PENDING') ");

							db_query ($con,"Update  tagihan_cs set status_realisasi ='NUNGGAK'  where cif  ='".$checbox[$i]."' and tgl_tagih='$tanggal'");

		if ($ada_tunggak>=$os){

			db_query ($con,"delete from tunggakan where cif ='".$checbox[$i]."' and tgl_tunggak ='$tanggal' ");

		}
if ($jarak<2){

			db_query ($con,"delete from tunggakan where cif ='".$checbox[$i]."' and tgl_tunggak ='$tanggal' ");
db_query ($con,"delete from temp_eod where cif ='".$checbox[$i]."' and tgl_eod ='$tanggal' ");


		}
	


		}elseif($nom[$i] < $bulat){

			
			if ($nom[$i] =='0'){
				$data =0;
							db_query ($con,"Update  tagihan_cs set status_realisasi ='BAYAR'  where cif  ='".$checbox[$i]."' and tgl_tagih='$tanggal'");

if ($jarak < 2){

			db_query ($con,"delete from tunggakan where cif ='".$checbox[$i]."' and tgl_tunggak ='$tanggal' ");
db_query ($con,"delete from temp_eod where cif ='".$checbox[$i]."' and tgl_eod ='$tanggal' ");


		}


			}else{
		
				db_query ($con,"insert  into tunggakan (tgl_tunggak,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,blok)VALUES('$tanggal','$norek','$unit','".$checbox[$i]."','$code_kel','0','01','$angsuran_pokok','$userid','$ket','$cao','') ");
				db_query ($con,"insert  into tunggakan (tgl_tunggak,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,blok)VALUES('$tanggal','$norek','$unit','".$checbox[$i]."','$code_kel','0','02','$ijaroh','$userid','$ket','$cao','') ");
				db_query ($con,"insert  into temp_eod (cif,unit,setoran,angsuran,pokok,margin,ft,pb,tgl_setor,tgl_eod,status,status_eod)VALUES
				('".$checbox[$i]."','$unit','$setoran','$angsuran','$angsuran_pokok','$ijaroh','$ft','','$tanggal','$tanggal','NUNGGAK','PENDING') ");

		$input ="Insert into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,blok,tgl_input,kode_transaksi)
	 VALUES('$tgl_system','$norek','$unit','".$checbox[$i]."','$code_kel',0,'01','$setoran','$userid','$ket','','$cao','0','$tanggal','$kode_trans')";
	  $inputtrnsk = db_query ($con,$input);
	  $input ="Insert into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,blok,tgl_input,kode_transaksi)
	 VALUES('$tgl_system','$norek','$unit','".$checbox[$i]."','$code_kel',0,'02','$simpanan_minggon','$userid','$ket','','$cao','1','$tanggal','$kode_trans')";
	 $inputtrnsk = db_query ($con,$input);
 $input ="Insert into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,blok,tgl_input,kode_transaksi)
	 VALUES('$tgl_system','$norek','$unit','".$checbox[$i]."','$code_kel',0,'01','$simpan_duaribu','$userid','$ket','','$cao','1','$tanggal','$kode_trans')";
	 $inputtrnsk = db_query ($con,$input);


	 $jurnal_umum=db_fetch_array(db_query ($con,"SELECT max(nomor_jurnal) FROM jurnal_umum where unit ='$unit' ORDER BY tanggal_selesai DESC"));
	 $nomor_jurnal=$jurnal_umum[0]+1;
	 //pencatatan du jurnal realasisi Piutang Murabahaha total jumlah Pokok dan margin
	 db_query ($con,"insert into jurnal_masuk(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values('$nomor_jurnal','$kode_trans','$tgl_system','$unit')");
	 db_query ($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
	 values('$unit','$kode_trans','1120000','$tgl_system','Bukti SYSTEM','$ket','$nom[$i]','$userid')");
	 
	 db_query ($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
	 values('$unit','$kode_trans','2101000','$tgl_system','Bukti SYSTEM','$ket','$nom[$i]','$userid')");
db_query ($con,"Update  tagihan_cs set status_realisasi ='NUNGGAK'  where cif  ='".$checbox[$i]."' and tgl_tagih='$tanggal'");
	 if ($ada_tunggak>=$os){

		db_query ($con,"delete from tunggakan where cif ='".$checbox[$i]."' and tgl_tunggak ='$tanggal' ");
//mysqli_query ($con,"Update  tagihan_cs set status_realisasi ='NUNGGAK'  where cif  ='".$checbox[$i]."' and tgl_tagih='$tanggal'");

	}	


	 
			}
	
		}else{
			

		
		
     $input ="Insert into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,blok,tgl_input,kode_transaksi)
	 VALUES('$tgl_system','$norek','$unit','".$checbox[$i]."','$code_kel',0,'01','$setoran','$userid','$ket','','$cao','0','$tanggal','$kode_trans')";
	  $inputtrnsk = db_query ($con,$input);
	  $input ="Insert into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,blok,tgl_input,kode_transaksi)
	 VALUES('$tgl_system','$norek','$unit','".$checbox[$i]."','$code_kel',0,'02','$simpanan_minggon','$userid','$ket','','$cao','1','$tanggal','$kode_trans')";
db_query ($con,"Update  tagihan_cs set status_realisasi ='NUNGGAK'  where cif  ='".$checbox[$i]."' and tgl_tagih='$tanggal'");

 $inputtrnsk = db_query ($con,$input);
	 
	 $input ="Insert into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,blok,tgl_input,kode_transaksi)
	 VALUES('$tgl_system','$norek','$unit','".$checbox[$i]."','$code_kel',0,'01','$simpan_duaribu','$userid','$ket','','$cao','1','$tanggal','$kode_trans')";

     if($ada_tunggak >0 ){

		db_query ($con,"insert  into tunggakan (tgl_tunggak,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,blok)VALUES('$tanggal','$norek','$unit','".$checbox[$i]."','$code_kel','0','01','$angsuran_pokok','$userid','$ket','$cao','') ");
		db_query ($con,"insert  into tunggakan (tgl_tunggak,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,blok)VALUES('$tanggal','$norek','$unit','".$checbox[$i]."','$code_kel','0','02','$ijaroh','$userid','$ket','$cao','') ");

db_query ($con,"Update  tagihan_cs set status_realisasi ='NUNGGAK'  where cif  ='".$checbox[$i]."' and tgl_tagih='$tanggal'");
	

	 }       
	 
	 
	 
	 $inputtrnsk = db_query ($con,$input);
            if($inputtrnsk){
          
                    

                $jurnal_umum=db_fetch_array(db_query ($con,"SELECT max(nomor_jurnal) FROM jurnal_umum where unit ='$unit' ORDER BY tanggal_selesai DESC"));
                $nomor_jurnal=$jurnal_umum[0]+1;
                //pencatatan du jurnal realasisi Piutang Murabahaha total jumlah Pokok dan margin
                db_query ($con,"insert into jurnal_masuk(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values('$nomor_jurnal','$kode_trans','$tgl_system','$unit')");
                db_query ($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                values('$unit','$kode_trans','1120000','$tgl_system','Bukti SYSTEM','$ket','$nom[$i]','$userid')");
                
                db_query ($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                values('$unit','$kode_trans','2101000','$tgl_system','Bukti SYSTEM','$ket','$nom[$i]','$userid')");




				switch ($status_app) {
					case "REST":
						$jurnal_umum=db_fetch_array(db_query($con,"SELECT max(nomor_jurnal) FROM jurnal_umum where unit ='$unit' ORDER BY tanggal_selesai DESC"));
						$nomor_jurnal=$jurnal_umum[0]+1;

						if($ada_tunggak>0){
							db_query($con,"insert  into tunggakan (tgl_tunggak,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,blok)VALUES('$tanggal','$norek','$unit','".$checbox[$i]."','$code_kel','$angsuran_pokok','01','0','$userid','$ket','$cao','') ") ;
							db_query ($con,"insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif)VALUES('','$tanggal','$unit','$norek','0','$angsuran_pokok','03','$ket2','$userid','REALISASI POKOK','".$checbox[$i]."')");
							db_query ($con,"Update  loan set next_schedule ='$next_schedule',os =os-'$angsuran',saldo_margin=saldo_margin-'$ijaroh',run_tenor='$pb',ke='$ke_tenor'+1 ,last_payment='$tanggal' where cif  ='".$checbox[$i]."'");
							db_query ($con,"insert  into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,tgl_input,kode_transaksi)VALUES('$tanggal','$norek','$unit','".$checbox[$i]."','$code_kel','$angsuran','03','0','$userid','$ket2','$cao','$tgl_input','$kode_tran') ");
						
							db_query ($con,"Update  pinjaman_detail set jumlah_bayar ='$angsuran',tgl_bayar='$tanggal' where id_pinjam  ='$norek' and cicilan='$pb'");
							
							db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
							values('$unit','$kode_tran','2101000','$tanggal','Bukti SYSTEM','$ket2','$angsuran','$userid')");
							db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
							values('$unit','$kode_tran','1411000','$tanggal','Bukti SYSTEM','$ket2','$angsuran','$userid')");
							db_query ($con,"Update  tagihan_cs set status_realisasi ='BAYAR'  where cif  ='".$checbox[$i]."' and tgl_tagih='$tanggal'");

						}else{
						db_query ($con,"insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif)VALUES('','$tanggal','$unit','$norek','0','$angsuran_pokok','03','$ket2','$userid','REALISASI POKOK','".$checbox[$i]."')");
						db_query ($con,"Update  loan set next_schedule ='$next_schedule',os =os-'$angsuran',saldo_margin=saldo_margin-'$ijaroh',run_tenor='$pb',ke='$ke_tenor'+1 ,last_payment='$tanggal' where cif  ='".$checbox[$i]."'");
						db_query ($con,"insert  into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,tgl_input,kode_transaksi)VALUES('$tanggal','$norek','$unit','".$checbox[$i]."','$code_kel','$angsuran','03','0','$userid','$ket2','$cao','$tgl_input','$kode_tran') ");
					
						db_query ($con,"Update  pinjaman_detail set jumlah_bayar ='$angsuran',tgl_bayar='$tanggal' where id_pinjam  ='$norek' and cicilan='$pb'");
						
												db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
						values('$unit','$kode_tran','2101000','$tanggal','Bukti SYSTEM','$ket2','$angsuran','$userid')");
						db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
						values('$unit','$kode_tran','1411000','$tanggal','Bukti SYSTEM','$ket2','$angsuran','$userid')");
							db_query ($con,"Update  tagihan_cs set status_realisasi ='BAYAR'  where cif  ='".$checbox[$i]."' and tgl_tagih='$tanggal'");

						

						}
				
					  break;
					case "REST_POKOK_MARGIN":

					$jurnal_umum=db_fetch_array(db_query($con,"SELECT max(nomor_jurnal) FROM jurnal_umum where unit ='$unit' ORDER BY tanggal_selesai DESC"));
					$nomor_jurnal=$jurnal_umum[0]+1;                 
					
					if($ada_tunggak > 0){
					db_query($con,"insert  into tunggakan (tgl_tunggak,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,blok)VALUES('$tanggal','$norek','$unit','".$checbox[$i]."','$code_kel','$angsuran_pokok','01','0','$userid','$ket','$cao','') ") ;
					db_query($con,"insert  into tunggakan (tgl_tunggak,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,blok)VALUES('$tanggal','$norek','$unit','".$checbox[$i]."','$code_kel','$ijaroh','02','0','$userid','$ket','$cao','') ");
					db_query($con,"insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif)VALUES('','$tanggal','$unit','$norek','0','$angsuran_pokok','03','$ket','$userid','REALISASI POKOK','".$checbox[$i]."')") ;
					db_query($con,"insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif)VALUES('','$tanggal','$unit','$norek','0','$ijaroh','03','$ket','$userid','REALISASI BAGHAS','".$checbox[$i]."')");
					db_query($con,"Update  loan set next_schedule ='$next_schedule',os =os-'$angsuran',saldo_margin=saldo_margin-'$ijaroh',run_tenor='$pb',ke='$ke_tenor'+1,last_payment='$tanggal' where cif  ='".$checbox[$i]."'") ;
											
					db_query($con,"insert  into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,blok,tgl_input,kode_transaksi)VALUES('$tanggal','$norek','$unit','".$checbox[$i]."','$code_kel','$angsuran','03','0','$userid','$ket2','','$cao','2','$tgl_input','$kode_tran') ") ;
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

							db_query ($con,"Update  tagihan_cs set status_realisasi ='BAYAR'  where cif  ='".$checbox[$i]."' and tgl_tagih='$tanggal'");


					}else{

						db_query($con,"insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif)VALUES('','$tanggal','$unit','$norek','0','$angsuran_pokok','03','$ket2','$userid','REALISASI POKOK','".$checbox[$i]."')");
						db_query($con,"insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif)VALUES('','$tanggal','$unit','$norek','0','$ijaroh','03','$ket2','$userid','REALISASI BAGHAS','".$checbox[$i]."')");

						db_query($con,"Update  loan set next_schedule ='$next_schedule',os =os-'$angsuran',saldo_margin=saldo_margin-'$ijaroh',run_tenor='$pb',ke='$ke_tenor'+1,last_payment='$tanggal' where cif  ='".$checbox[$i]."'");
						db_query($con,"insert  into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,tgl_input,kode_transaksi)VALUES('$tanggal','$norek','$unit','".$checbox[$i]."','$code_kel','$angsuran','03','0','$userid','$ket2','$cao','$tgl_input','$kode_tran') ");
						
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

							db_query ($con,"Update  tagihan_cs set status_realisasi ='BAYAR'  where cif  ='".$checbox[$i]."' and tgl_tagih='$tanggal'");



						}

					  
					  break;
					case "MURAB":
						$jurnal_umum=db_fetch_array(db_query($con,"SELECT max(nomor_jurnal) FROM jurnal_umum where unit ='$unit' ORDER BY tanggal_selesai DESC"));
						$nomor_jurnal=$jurnal_umum[0]+1;                 
						
						if($ada_tunggak>0){
							db_query($con,"insert  into tunggakan (tgl_tunggak,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,blok)VALUES('$tanggal','$norek','$unit','".$checbox[$i]."','$code_kel','$angsuran_pokok','01','0','$userid','$ket','$cao','') ") ;
							db_query($con,"insert  into tunggakan (tgl_tunggak,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,blok)VALUES('$tanggal','$norek','$unit','".$checbox[$i]."','$code_kel','$ijaroh','02','0','$userid','$ket','$cao','') ");
							db_query($con,"insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif)VALUES('','$tanggal','$unit','$norek','0','$angsuran_pokok','03','$ket2','$userid','REALISASI POKOK','$cif')") ;
							db_query($con,"insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif)VALUES('','$tanggal','$unit','$norek','0','$ijaroh','03','$ket2','$userid','REALISASI BAGHAS','$cif')");
							db_query($con,"Update  loan set next_schedule ='$next_schedule',os =os-'$angsuran',saldo_margin=saldo_margin-'$ijaroh',run_tenor='$pb',ke='$ke_tenor'+1,last_payment='$tanggal' where cif  ='".$checbox[$i]."'");
											 
					
							db_query($con,"insert  into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,blok,tgl_input,kode_transaksi)VALUES('$tanggal','$norek','$unit','".$checbox[$i]."','$code_kel','$angsuran','03','0','$userid','$ket','','$cao','2','$tgl_input','$kode_tran') ");
							
											 
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
							db_query ($con,"Update  tagihan_cs set status_realisasi ='BAYAR'  where cif  ='".$checbox[$i]."' and tgl_tagih='$tanggal'");

							 
							}else{
							 
							db_query($con,"insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif)VALUES('','$tanggal','$unit','$no_anggota','0','$angsuran_pokok','03','$ket2','$userid','REALISASI POKOK','".$checbox[$i]."')");
							db_query($con,"insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif)VALUES('','$tanggal','$unit','$no_anggota','0','$ijaroh','03','$ket2','$userid','REALISASI BAGHAS','".$checbox[$i]."')");
							
							db_query($con,"Update  loan set next_schedule ='$next_schedule',os =os-'$angsuran',saldo_margin=saldo_margin-'$ijaroh',run_tenor='$pb',ke='$ke_tenor'+1,last_payment='$tanggal' where cif  ='".$checbox[$i]."'");
							db_query($con,"insert  into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,tgl_input,kode_transaksi)VALUES('$tanggal','$norek','$unit','".$checbox[$i]."','$code_kel','$angsuran','03','0','$userid','$ket','$cao','$tgl_input','$kode_tran') ") ;
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

							db_query ($con,"Update  tagihan_cs set status_realisasi ='BAYAR'  where cif  ='".$checbox[$i]."' and tgl_tagih='$tanggal'");

							 
							 
							}
					  
					  break;
					default:
					$jurnal_umum=db_fetch_array(db_query($con,"SELECT max(nomor_jurnal) FROM jurnal_umum where unit ='$unit' ORDER BY tanggal_selesai DESC"));
					$nomor_jurnal=$jurnal_umum[0]+1;                 
					
					if($ada_tunggak>0){
						db_query($con,"insert  into tunggakan (tgl_tunggak,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,blok)VALUES('$tanggal','$norek','$unit','".$checbox[$i]."','$code_kel','$angsuran_pokok','01','0','$userid','$ket2','$cao','') ") ;
						db_query($con,"insert  into tunggakan (tgl_tunggak,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,blok)VALUES('$tanggal','$norek','$unit','".$checbox[$i]."','$code_kel','$ijaroh','02','0','$userid','$ket2','$cao','') ");
						db_query($con,"insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif)VALUES('','$tanggal','$unit','$norek','0','$angsuran_pokok','03','$ket2','$userid','REALISASI POKOK','$cif')") ;
						db_query($con,"insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif)VALUES('','$tanggal','$unit','$norek','0','$ijaroh','03','$ket2','$userid','REALISASI BAGHAS','$cif')");
						db_query($con,"Update  loan set next_schedule ='$next_schedule',os =os-'$angsuran',saldo_margin=saldo_margin-'$ijaroh',run_tenor='$pb',ke='$ke_tenor'+1,last_payment='$tanggal' where cif  ='".$checbox[$i]."'");
										 
				
						db_query($con,"insert  into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,blok,tgl_input,kode_transaksi)VALUES('$tanggal','$norek','$unit','".$checbox[$i]."','$code_kel','$angsuran','03','0','$userid','$ket2','','$cao','2','$tgl_input','$kode_tran') ");
						
										 
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

							db_query ($con,"Update  tagihan_cs set status_realisasi ='BAYAR'  where cif  ='".$checbox[$i]."' and tgl_tagih='$tanggal'");

						 
						}else{
						 
						db_query($con,"insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif)VALUES('','$tanggal','$unit','$no_anggota','0','$angsuran_pokok','03','$ket2','$userid','REALISASI POKOK','".$checbox[$i]."')");
						db_query($con,"insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif)VALUES('','$tanggal','$unit','$no_anggota','0','$ijaroh','03','$ket2','$userid','REALISASI BAGHAS','".$checbox[$i]."')");
						
						db_query($con,"Update  loan set next_schedule ='$next_schedule',os =os-'$angsuran',saldo_margin=saldo_margin-'$ijaroh',run_tenor='$pb',ke='$ke_tenor'+1,last_payment='$tanggal' where cif  ='".$checbox[$i]."'");
						db_query($con,"insert  into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,tgl_input,kode_transaksi)VALUES('$tanggal','$norek','$unit','".$checbox[$i]."','$code_kel','$angsuran','03','0','$userid','$ket2','$cao','$tgl_input','$kode_tran') ") ;
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
							db_query ($con,"Update  tagihan_cs set status_realisasi ='BAYAR'  where cif  ='".$checbox[$i]."' and tgl_tagih='$tanggal'");

						 
						 
						}
				  }
              
                











                
            }else{
            echo "<script>alert('Maaf,.Proses Input Gagal');</script>";
            echo "<script language='javascript'>document.location.href='stor.php'</script>";
            }
			
		
		
		
	}
		
		
	}

	if (!mysqli_commit($con)) {
		echo "Transaksi Gagal commit Silahkan ulangi lagi";
		exit();
	  }
echo "<script>alert('Setoran Kelompok sukses di simpan');</script>";
echo "<script language='javascript'>document.location.href='setor_kelompok.php'</script>";
	
}else {
			echo '<script>alert("anda belum memilih data."); window.location="form.php"</script>';
	}

?>
