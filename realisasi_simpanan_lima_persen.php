<?php require_once __DIR__ . '/koneksi_db.php'; 
date_default_timezone_set('Asia/Jakarta');
session_start();
ob_start();

$userid=$_SESSION['userid'];
$tgl_system=$_SESSION['stgl_system'];
//cek apakah user sudah login
if(!isset($_SESSION['stgl_system'])){
echo "<script>alert('Maaf,.tanggal system belum di setup');</script>";

   header("location:param_tgl.php");
 exit; }
if(!isset($_SESSION['userid'])){
   header("location:index.html");
 exit; }
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

 
			
$cekbox = $_POST['cekbox'];
if ($cekbox) {
       foreach ($cekbox as $value) {
	
		
			$kode_tr=5;
			$kode_tr=acakangkahuruf($kode_tr);	   
		     $row = db_fetch_array(db_query($con,"select DATE_ADD(tgl_wakalah,INTERVAL 7 DAY) as tgl_murab,maturity_date,calon_loan.buss_date,calon_loan.code_kel,calon_loan.no_anggota,calon_loan.Cust_Short_name,	calon_loan.Deal_type,suffix,bagi_hasil,Tenor,Plafond,os,angsuran,pokok,ijaroh,bulat,ke,usaha,calon_loan.unit,tgl_wakalah,hari,calon_loan.cao,calon_loan.USERID,calon_loan.status,calon_loan.cif
			 from calon_loan  
			 left join anggota on anggota.cif = calon_loan.cif
			 WHERE calon_loan.cif ='" . $value . "' "));
        //$row = mysql_fetch_array(mysql_query("select * from calon_loan  WHERE no_anggota ='" . $value . "'")) or die(mysql_error());
			$no_anggota=$row['no_anggota'];
			$tgl_wakalah= $row['tgl_wakalah'];
            $nama=mysqli_real_escape_string ($con,$row['Cust_Short_name']);
			$cif=$row['cif'];
            $unit=$row['unit'];
            $code_kel=$row['code_kel'];
			$Plafond=$row['Plafond'];
			$nominal =$Plafond *5/100; 
		
			$norek="$no_anggota";

			
$kodetr=8;
$kodetr=acakangkahuruf($kodetr);
$kode_trans="BU/".$unit.$kodetr;				
			$ket="Realisasi Simpanan Wajib dan Pokok Unit $unit";		


            $cek_wajib = db_query($con,"select SUM(KREDIT-DEBIT) as wajib from simpanan_wajib where cif ='" . $value . "'");
$cwajib = db_fetch_array($cek_wajib);
$wajib = $cwajib['wajib'];
$cek_pokok = db_query($con,"select SUM(KREDIT-DEBIT) as pokok from simpanan_pokok where cif ='" . $value . "'");
$cpokok = db_fetch_array($cek_pokok);
$pokok = $cpokok['pokok'];

if ($pokok == 0 && $wajib >= 0)  {
                $in_pokok=50000;
               $ket="Setoran simpanan Pokok an $nama";
                    $input ="Insert into simpanan_pokok (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,blok,kode_transaksi)VALUES('$tgl_system','$norek','$unit','" . $value . "','$code_kel',0,'04','$in_pokok','$userid','Simpanan Pokok','','$cao','4','$kode_trans')";
                    $inputtrnsk = db_query($con,$input);

                    @db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                    values('$unit','$kode_trans','1120000','$tgl_system','Bukti SYSTEM','$ket','$in_pokok','$userid')");
                    @db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                    values('$unit','$kode_trans','3102000','$tgl_system','Bukti SYSTEM','$ket','$in_pokok','$userid')");
              
            //HITUNG SIA SIMPANAN JIKA SISA > 0 MAKA SIMPAN DI WADIAH
                $sisa = $nominal - $in_pokok;
                if($sisa>0){
                   $ket="Setoran simpanan 5% an $nama";
                        $input ="Insert into simpanan_wajib (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,blok,kode_transaksi)VALUES('$tgl_system','$norek','$unit','" . $value . "','$code_kel',0,'05','$sisa','$userid','PB Simpanan Wajib','','$cao','1','$kode_trans')";
                        $inputtrnsk = db_query($con,$input);
                        db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                        values('$unit','$kode_trans','1120000','$tgl_system','Bukti SYSTEM','$ket','$sisa','$userid')");

                        db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                        values('$unit','$kode_trans','3202000','$tgl_system','Bukti SYSTEM','$ket','$sisa','$userid')");
                  
                }
              
            }
          
            //JIKA PUNYA POKOK DA BELUM MEMPUNYA WAJIB
            elseif($pokok == 50000 && $wajib >= 0){
                $ket="Setoran simpanan 5% an $nama";

                    $input ="Insert into simpanan_wajib (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,blok,kode_transaksi)VALUES('$tgl_system','$norek','$unit','" . $value . "','$code_kel',0,'05','$nominal','$userid','PB Simpanan Wajib','','$cao','1','$kode_trans')";
                        $inputtrnsk = db_query($con,$input);


                    @db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                    values('$unit','$kode_trans','1120000','$tgl_system','Bukti SYSTEM','$ket','$nominal','$userid')");
                    @db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                    values('$unit','$kode_trans','3202000','$tgl_system','Bukti SYSTEM','$ket','$nominal','$userid')");
              

              
            }
           
           
            //JIKA BELUMPUNYA WAJIB DAN POKOK SPLIT SIMPANANT KE POKOK DAN WAJIB
            elseif($pokok == 0 && $wajib == 0){
                $in_pokok=50000;
               $ket="Setoran simpanan 5% an $nama";
                   
                    $input ="Insert into simpanan_pokok (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,blok,kode_transaksi)VALUES('$tgl_system','$norek','$unit','" . $value . "','$code_kel',0,'04','$in_pokok','$userid','Simpanan Pokok','','$cao','4','$kode_trans')";
                    $inputtrnsk = db_query($con,$input);
                    @db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                    values('$unit','$kode_trans','1120000','$tgl_system','Bukti SYSTEM','$ket','$in_pokok','$userid')");
                    @db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                    values('$unit','$kode_trans','3102000','$tgl_system','Bukti SYSTEM','$ket','$in_pokok','$userid')");
              

              
                //HITUNG JIKA SALDO CUKU UNTUK POKOK KRDIT KE POKOK
                    $sisa_pokok = $nominal - $in_pokok;
                    if ($sisa_pokok>0){
                        $ket="Setoran simpanan 5% an $nama";
                   

                        $input ="Insert into simpanan_wajib (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,blok,kode_transaksi)VALUES('$tgl_system','$norek','$unit','" . $value . "','$code_kel',0,'05','$sisa_pokok','$userid','PB Simpanan Wajib','','$cao','1','$kode_trans')";
                        $inputtrnsk = mysql_query($input);
                    @db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                    values('$unit','$kode_trans','1120000','$tgl_system','Bukti SYSTEM','$ket','$wajib_in','$userid')");
                    @db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                    values('$unit','$kode_trans','3202000','$tgl_system','Bukti SYSTEM','$ket','$wajib_in','$userid')");
                    } 
                   
            }else {
                $ket1="Wadiah $nama";
              
                   $input ="Insert into simpanan_wajib (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,blok,kode_transaksi)VALUES('$tgl_system','$norek','$unit','" . $value . "','$code_kel',0,'05','$sisa_pokok','$userid','PB Simpanan Wajib','','$cao','1','$kode_trans')";
                        $inputtrnsk = db_query($con,$input);
                        db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                    values('$unit','$kode_trans','1120000','$tgl_system','Bukti SYSTEM','$ket','$nominal','$userid')");

                    db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                    values('$unit','$kode_trans','3202000','$tgl_system','Bukti SYSTEM','$ket','$nominal','$userid')");
           
              
            } 
            
            



			




		}



        if (!mysqli_commit($con)) {
            echo "Transaksi Gagal commit Silahkan ulangi lagi";
            exit();
          }else{
				echo '<script>alert("Simpanan wajib dan Pokok sudah direalisasikan."); window.location="simpanan_lima_persen.php"</script>';
    
          }
            }  
			else {
					echo '<script>alert("anda belum memilih data."); window.location="simpanan_lima_persen.php"</script>';
		}

?>
