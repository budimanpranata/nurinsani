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
}
//cek apakah user sudah login
if(!isset($_SESSION['userid'])){
   header("location:index.html");
}

//cek level user
if($_SESSION['level']!="unit"){
   header("location:index.html");
}
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
		$kel = mysql_query ("select anggota.cif,no_anggota as norek, loan.code_kel,loan.unit,angsuran,loan.Cust_Short_name,loan.cao,bulat FROM loan 
		left join anggota on anggota.cif=loan.cif
		where  loan.cif ='".$checbox[$i]."'");
		$data = mysql_fetch_array($kel);

		$norek=$data['norek'];
		$saldo_margin=$data['saldo_margin'];
		$pokok=$os-$saldo_margin;
		$unit=$data ['unit'];
		$code_kel=$data['code_kel'];
		$cao=$data['cao'];
		$ciff=$data['cif'];
		$angsuran=$data['angsuran'];
		$bulat=$data['bulat'];
		$simpanan_minggon=$bulat - $angsuran;
		$nama=mysql_real_escape_string($data['Cust_Short_name']);
		$ket ="Setoran an $nama $cif";
		$status_app=$data['status_app'];
		$setoran=$nom[$i] - $simpanan_minggon;
	
		

 $kodetr=7;
            $kodetr=acakangkahuruf($kodetr);
            $kode_trans="BU/".$unit.$kodetr;
		
		if ($nom[$i]==0){
		$data=0;
			
		}else{
			
		
		
     $input ="Insert into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,blok,tgl_input,kode_transaksi)
	 VALUES('$tgl_system','$norek','$unit','$ciff','$code_kel',0,'01','$setoran','$userid','$ket','','$cao','0','$tanggal','$kode_trans')";
	  $inputtrnsk = mysql_query($input);
	  $input ="Insert into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,blok,tgl_input,kode_transaksi)
	 VALUES('$tgl_system','$norek','$unit','$ciff','$code_kel',0,'01','$simpanan_minggon','$userid','$ket','','$cao','1','$tanggal','$kode_trans')";
            $inputtrnsk = mysql_query($input);
            if($inputtrnsk){
          
                    

                $jurnal_umum=mysql_fetch_array(mysql_query("SELECT max(nomor_jurnal) FROM jurnal_umum where unit ='$unit' ORDER BY tanggal_selesai DESC"));
                $nomor_jurnal=$jurnal_umum[0]+1;
                //pencatatan du jurnal realasisi Piutang Murabahaha total jumlah Pokok dan margin
                mysql_query("insert into jurnal_masuk(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values('$nomor_jurnal','$kode_trans','$tgl_system','$unit')");
                mysql_query("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                values('$unit','$kode_trans','1120000','$tgl_system','Bukti SYSTEM','$ket','$nom[$i]','$userid')");
                
                mysql_query("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                values('$unit','$kode_trans','2101000','$tgl_system','Bukti SYSTEM','$ket','$nom[$i]','$userid')");
              
                
                
            }
            else{
            echo "<script>alert('Maaf,.Proses Input Gagal');</script>";
            echo "<script language='javascript'>document.location.href='stor.php'</script>";
            }
		
		
		 

		
		
		
		
		
		
		
		
		}
		
		
	}
echo "<script>alert('Setoran Kelompok sukses di simpan');</script>";
            echo "<script language='javascript'>document.location.href='setor_kelompok.php'</script>";
	
}else {
			echo '<script>alert("anda belum memilih data."); window.location="form.php"</script>';
	}

?>
