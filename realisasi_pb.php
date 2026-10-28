<?php require_once __DIR__ . '/koneksi_db.php'; 
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
db_ni();
mysql_select_db('nur_insani');
include "function.php";

$tanggal=$tgl_system;
$simpan=$_SESSION['simpans'];	
$baki=$_SESSION['bakis'];	
$nom=$_POST['bulat'];			
			
$checbox = $_POST['cekbox'];
if ($checbox) {
	for ($i=0; $i <sizeof($checbox);$i++){
		//$query="insert into data (name,alamat) values ('".$checbox[$i]."','".$nom[$i]."')";
		$kel = mysql_query ("select no_anggota,code_kel,cif,unit,Cust_Short_name,cao FROM loan where  cif ='".$checbox[$i]."'");
$kkel = mysql_fetch_array($kel);
$code_kel = $kkel['code_kel'];
$cif = $kkel['cif'];
$ciff = $kkel['cif'];
$unit=$kkel['unit'];

 $kodetr=7;
            $kodetr=acakangkahuruf($kodetr);
            $kode_trans="BU/".$unit.$kodetr;
		
		if ($nom[$i]==0){
		$data=0;
			
		}else{
		 

$nama=mysql_real_escape_string ($kkel['Cust_Short_name']);
$cao=$kkel['cao'];
$norek=$kkel['no_anggota'];

      if ($baki=="Debet"){
                $jurnal_umum=mysql_fetch_array(mysql_query("SELECT max(nomor_jurnal) FROM jurnal_umum where unit ='$unit' ORDER BY tanggal_selesai DESC"));
                $nomor_jurnal=$jurnal_umum[0]+1;
                if($simpan=="pokok"){
                    $gl='3102000';
					$keterangan="PB Debet Pokok $cif";
					
                      $input ="Insert into simpanan_pokok(BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,kode_transaksi)VALUES('$buss_date','$norek','$unit','$ciff','$code_kel','$nom[$i]','04','0','$userid','$keterangan','','$cao','$kode_trans')";
          $inputtrnsk = mysql_query($input);
          //mysql_query("Update  simpanan set type ='01' where cif  ='$ciff' and type ='04'") or die(mysql_error("GAGAL INPUT"));
           //mysql_query("Update  simpanan set type ='01' where cif  ='$ciff' and type ='02'") or die(mysql_error("GAGAL INPUT"));
                   $input ="Insert into simpanan(BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,kode_transaksi)VALUES('$buss_date','$norek','$unit','$ciff','$code_kel','0','01','$nom[$i]','$userid','$keterangan','','$cao','$kode_trans')";
          $inputtrnsk = mysql_query($input);

                }else{
                    $gl='3202000';
					$keterangan="PB Wajib  $cif";
                    $input ="Insert into simpanan_wajib(BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,kode_transaksi)VALUES('$buss_date','$norek','$unit','$ciff','$code_kel','$nom[$i]','05','0','$userid','$keterangan','','$cao','$kode_trans')";
                    $inputtrnsk = mysql_query($input);
                     //mysql_query("Update  simpanan set type ='01' where cif  ='$ciff' and type ='05'") or die(mysql_error("GAGAL INPUT"));
   $input ="Insert into simpanan(BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,kode_transaksi)VALUES('$buss_date','$norek','$unit','$ciff','$code_kel',0,'01','$nom[$i]','$userid','$keterangan','','$cao','$kode_trans')";
          $inputtrnsk = mysql_query($input);

                }
                //pencatatan d jurnal realasisi Piutang Murabahaha total jumlah Pokok dan margin
                mysql_query("insert into jurnal_masuk(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values('$nomor_jurnal','$kode_transaksi','$tgl_system','$unit')");
                mysql_query("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                values('$unit','$kode_trans','$gl','$tgl_system','Bukti SYSTEM','$keterangan','$nom[$i]','$userid')");
                mysql_query("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                values('$unit','$kode_trans','2101000','$tgl_system','Bukti SYSTEM','$keterangan','$nom[$i]','$userid')");
                //mysql_query("Update  tunggakan set margin =margin - '$ijaroh', pokok =pokok-'$pokok'where no_anggota  ='$norek'") or die(mysql_error("GAGAL INPUT"));
                      
        
    }else{
        $jurnal_umum=mysql_fetch_array(mysql_query("SELECT max(nomor_jurnal) FROM jurnal_umum where unit ='$unit' ORDER BY tanggal_selesai DESC"));
                $nomor_jurnal=$jurnal_umum[0]+1;
                if($simpan=="pokok"){
                    $gl='3102000';
					$keterangan="PB  Pokok $cif";
                    $input ="Insert into simpanan_pokok(BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,kode_transaksi)VALUES('$buss_date','$norek','$unit','$ciff','$code_kel',0,'04','$nom[$i]','$userid','$keterangan','','$cao','$kode_trans')";
          $inputtrnsk = mysql_query($input);

$input ="Insert into simpanan(BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,kode_transaksi)VALUES('$buss_date','$norek','$unit','$ciff','$code_kel','$nom[$i]','01',0,'$userid','$keterangan','','$cao','$kode_trans')";
          $inputtrnsk = mysql_query($input);

        
        
                }else{
                    $gl='3202000';
					$keterangan="PB Wajib  $cif";
                    $input ="Insert into simpanan_wajib(BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,kode_transaksi)VALUES('$buss_date','$norek','$unit','$ciff','$code_kel',0,'04','$nom[$i]','$userid','$keterangan','','$cao','$kode_trans')";
          $inputtrnsk = mysql_query($input);
$input ="Insert into simpanan(BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,kode_transaksi)VALUES('$buss_date','$norek','$unit','$ciff','$code_kel','$nom[$i]','01',0,'$userid','$keterangan','','$cao','$kode_trans')";
          $inputtrnsk = mysql_query($input);


                }
                //pencatatan d jurnal realasisi Piutang Murabahaha total jumlah Pokok dan margin
                mysql_query("insert into jurnal_masuk(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values('$nomor_jurnal','$kode_transaksi','$tgl_system','$unit')");
                mysql_query("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                values('$unit','$kode_trans','2101000','$tgl_system','Bukti SYSTEM','$keterangan','$nom[$i]','$userid')");
                mysql_query("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                values('$unit','$kode_trans','$gl','$tgl_system','Bukti SYSTEM','$keterangan','$nom[$i]','$userid')");
                 
      
    }
echo "<script>alert('Pemindah Bukuan an $cif Suksess');</script>";
		
		
		
		
		
		
		
		
		
		
		}
		
		
	}

            echo "<script language='javascript'>document.location.href='pb_kel.php'</script>";
	
}else {
			echo '<script>alert("anda belum memilih data."); window.location="form.php"</script>';
	}

?>
