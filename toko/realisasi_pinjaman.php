<?php require_once __DIR__ . '/../koneksi_db.php';
date_default_timezone_set('Asia/Jakarta');
session_start();
ob_start();
    $userid=$_SESSION['userid'];
$tgl_system=$_SESSION['stgl_system'];
    $tanggal=date("y-m-d");

db_ni();
mysql_select_db('nur_insani');
include "function.php";

 
           
$cekbox = $_POST['cekbox'];
if ($cekbox) {
       foreach ($cekbox as $value) {
       
            $kode_tr=5;
            $kode_tr=acakangkahuruf($kode_tr);      
             $row = mysql_fetch_array(mysql_query("select DATE_ADD(tgl_wakalah,INTERVAL 7 DAY) as tgl_murab1,DATE_ADD(tgl_wakalah,INTERVAL 26 WEEK) as matur,tgl_murab,calon_musa.buss_date,calon_musa.code_kel,calon_musa.no_anggota,calon_musa.Cust_Short_name,maturity_date,
            calon_musa.Deal_type,suffix,bagi_hasil,Tenor,Plafond,os,angsuran,pokok,ijaroh,bulat,ke,usaha,calon_musa.unit,tgl_wakalah,hari,calon_musa.cao,calon_musa.USERID,calon_musa.status,calon_musa.id_member
             from calon_musa 
             inner join member on member.id_cust = calon_musa.id_member
             WHERE id_member ='" . $value . "'")) or die(mysql_error());
        //$row = mysql_fetch_array(mysql_query("select * from calon_musa  WHERE no_anggota ='" . $value . "'")) or die(mysql_error());
            $id_member=$row['id_member'];
            var_dump($id_member);
            $suffix=$row['suffix'];
            $Tenor=$row['Tenor'];
            

            if ($suffix=1){
                $ke=1;
            }else{
                $ke=((int)$suffix)+1;
            }
           
		   @mysql_query("update calon_musa set angsuran=0, saldo_margin=0, pokok=0,ijaroh=0 WHERE id_member ='$id_member'")or die(mysql_error());

		   @mysql_query ("delete from musawamah  WHERE id_member ='$id_member'")or die(mysql_error());
			
		   @mysql_query("replace  INTO musawamah select * from calon_musa WHERE id_member ='$id_member'") or die(mysql_error("GAGAL INPUT"));
		@mysql_query ("delete from calon_musa  WHERE id_member ='$id_member'")or die(mysql_error());

           
   
    echo '<script>alert("Pembiayaan sudah direalisasikan."); window.location="realisasi_wakalah.php"</script>';
   
            } 
		}
            else {
                    echo '<script>alert("anda belum memilih data."); window.location="realisasi_wakalah.php"</script>';
        }

?>
