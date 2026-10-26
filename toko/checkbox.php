<?php require_once __DIR__ . '/../php8_compat.php';
session_start();
ob_start();
$unit=$_SESSION['userid'];
include "conn2.php";
$koneksi=open_connection2();
$tgl_system=$_SESSION['stgl_system'];
include "function.php";
 $kodetr=7;
            $kodetr=acakangkahuruf($kodetr);
            $kode_trans="BU/".$unit.$kodetr;
           
$checbox=$_POST ['cekbox'];
$nom=$_POST ['bulat'];
if ($checbox) {
    for ($i=0; $i <sizeof($checbox);$i++){
        //$query="insert into data (name,alamat) values ('".$checbox[$i]."','".$nom[$i]."')";
        $kel = mysql_query ("select code_kel,id_member,unit,os,Cust_Short_name ,cao,angsuran,code_musa  FROM musawamah where  id_member ='".$checbox[$i]."'");
$kkel = mysql_fetch_array($kel);
$code_kel = $kkel['code_kel'];
$id_cust = $kkel['id_member'];
$os = $kkel['os'];
$code_musa = $kkel['code_musa'];


       
        if ($nom[$i]==0){
        mysql_query("insert  into tunggakan_toko (tgl_tunggak,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,blok)VALUES('$tgl_system','$norek','$unit','$id_cust','$code_kel','".$nom[$i]."','01','0','$userid','$ket','$cao','') ") or die(mysql_error("GAGAL INPUT"));
   
           
        }else{
         

$nama=mysql_real_escape_string ($kkel['Cust_Short_name']);
$ds=$kkel['kode_ao'];
$angsuran=$kkel['angsuran'];

$margin=5/100 * $angsuran;
$sisa=$nom[$i] - $angsuran;
$margin_titip=5/100 * $angsuran;

$margin_lunas=5/100 * $os;
       
        $ket="Setoran Angsuran An $nama $id_cust";
      $jurnal_umum=mysql_fetch_array(mysql_query("SELECT max(nomor_jurnal) FROM jurnal_umum where unit ='$unit' ORDER BY tanggal_selesai DESC"));
                $nomor_jurnal=$jurnal_umum[0]+1;
       
        $input ="Insert into list_toko (BUSS_DATE,NOREK,UNIT,id_member,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,blok,tgl_input,kode_transaksi)VALUES('$tgl_system','$code_musa','$unit','$id_cust','$code_kel',0,'03','".$nom[$i]."','$unit','$ket','','$cao','1','$tanggal','$kode_trans')";
            $inputtrnsk = mysql_query($input);
   
    $input ="Insert into list_toko (BUSS_DATE,NOREK,UNIT,id_member,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,blok,tgl_input,kode_transaksi)VALUES('$tgl_system','$code_musa','$unit','$id_cust','$code_kel','$angsuran','03',0,'$unit','$ket','','$cao','1','$tanggal','$kode_trans')";
            $inputtrnsk = mysql_query($input);
           
           
     mysql_query("update musawamah set os = os - angsuran,saldo_margin=saldo_margin - ijaroh where id_member='".$checbox[$i]."'");
            
           
            if($inputtrnsk){
          
                     

              
                //pencatatan du jurnal realasisi Piutang Murabahaha total jumlah Pokok dan margin
                mysql_query("insert into jurnal_masuk(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values('$nomor_jurnal','$kode_trans','$tgl_system','$unit')");
                mysql_query("insert  into tabel_transaksi_toko(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                values('$unit','$kode_trans','1120000','$tgl_system','Bukti SYSTEM','$ket','".$nom[$i]."','$userid')");
                 mysql_query("insert  into tabel_transaksi_toko(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                values('$unit','$kode_trans','1422000','$tgl_system','Bukti SYSTEM','$ket','$margin','$userid')");
              
               
                mysql_query("insert  into tabel_transaksi_toko(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                values('$unit','$kode_trans','1412000','$tgl_system','Bukti SYSTEM','$ket','$angsuran','$userid')");
                //mysql_query("Update  tunggakan set margin =margin - '$ijaroh', pokok =pokok-'$pokok'where no_anggota  ='$norek'") or die(mysql_error("GAGAL INPUT"));
              mysql_query("insert  into tabel_transaksi_toko(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                values('$unit','$kode_trans','41001','$tgl_system','Bukti SYSTEM','$ket','$margin','$userid')");
               
       
       
              echo "<script>alert('Nominal sudah ditambahkan pada angsuran member $id_cust');</script>";
              echo "<script language='javascript'>document.location.href='stor_kel.php'</script>";
               
            }
            else{
            echo "<script>alert('Maaf,.Porses Input Gagal');</script>";
            echo "<script language='javascript'>document.location.href='stor_kel.php'</script>";
            }
	if($sisa>0){
				
				
                
                mysql_query("insert  into tabel_transaksi_toko(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                values('$unit','$kode_trans','2891000','$tgl_system','Bukti SYSTEM','Titipan Angusran AN $nama $id_cust','$sisa','$userid')");
				 
				
			}else{
				  echo "<script>alert('Proses setoran selesai');</script>";
            echo "<script language='javascript'>document.location.href='stor.php'</script>";
			 
			}

       
        }
       
       
    }
}else {
            echo '<script>alert("anda belum memilih data."); window.location="form.php"</script>';
    }
