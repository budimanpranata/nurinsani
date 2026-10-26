<?php require_once __DIR__ . '/koneksi_db.php';
ini_set ('display_errors',1);
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
             $row = mysql_fetch_array(mysql_query("select DATE_ADD(tgl_wakalah,INTERVAL 7 DAY) as tgl_murab1,DATE_ADD(tgl_wakalah,INTERVAL 26 WEEK) as matur,tgl_murab as tgl_murab,calon_loan.buss_date,calon_loan.code_kel,calon_loan.no_anggota,calon_loan.Cust_Short_name,maturity_date,
            calon_loan.Deal_type,suffix,bagi_hasil,Tenor,Plafond,os,angsuran,pokok,ijaroh,bulat,ke,usaha,calon_loan.unit,tgl_wakalah,hari,calon_loan.cao,calon_loan.USERID,calon_loan.status,calon_loan.cif
             from calon_loan 
             inner join anggota on anggota.cif = calon_loan.cif
             WHERE no_anggota ='" . $value . "'")) or die(mysql_error());
        //$row = mysql_fetch_array(mysql_query("select * from calon_loan  WHERE no_anggota ='" . $value . "'")) or die(mysql_error());
            $no_anggota=$row['no_anggota'];
            $tgl_wakalah= $row['tgl_wakalah'];
            $cif=$row['cif'];
            $Plafond=$row['Plafond'];
            $bagi_hasil=$row['bagi_hasil'];
            $piutang=$Plafond + $bagi_hasil;
            $murab=$row['tgl_murab'];
            $buss_date=$row['buss_date'];
            $code_kel=$row['code_kel'];
            $Cust_Short_name=mysql_real_escape_string($row['Cust_Short_name']);
            $Deal_type=$row['Deal_type'];
            $suffix=$row['suffix'];
            $Tenor=$row['Tenor'];
            $os=$row['os'];
            $angsuran=$row['angsuran'];
            $pokok=$row['pokok'];
            $ijaroh=$row['ijaroh'];
            $bulat=$row['bulat'];
            $maturity_date=$row['maturity_date'];

            if ($suffix=1){
                $ke=1;
            }else{
                $ke=((int)$suffix)+1;
            }
          

            $usaha=$row['usaha'];
            $unit=$row['unit'];
            $hari=$row['hari'];
            $cao=$row['cao'];
            $USERID=$row['USERID'];
            $status='ANGGOTA';
            $norek="$no_anggota";
            $ket="Realisasi Murabahah $Cust_Short_name";
 		$query = mysql_query("SELECT os FROM loan WHERE cif= '$cif' ");
		 $check = mysql_fetch_array($query);
		$os_lama=$check['os'];
		if ($os_lama >0) {

			// jika data ditemukan
		echo "<script> alert ('Maaf  $cif mempunyai saldo OS sebesar $os_lama ')</script>";

		echo "<script language='javascript'>document.location.href='new2.php'</script>";
				}else{


            $kodetr=5;
            $kodetr=acakangkahuruf($kodetr);
            $kode_trans="BU/".$unit .$kodetr;
            
            $kode_transaksi="BU/".$kode_tr;
            //pencatatan du jurnal realasisi Piutang Murabahaha total jumlah Pokok dan margin
            @mysql_query("insert into jurnal_umum(nomor_jurnal,kode_transaksi,tanggal_selesai) values('$nomor_jurnal','$kode_trans','$tgl_system')");
           
            //Realiasasi Murabahah Debit pada Aset Murabahaha total Plafond
            @mysql_query("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
            values('$unit','$kode_trans','1481000','$tgl_system','Bukti SYSTEM','$ket','$Plafond','$userid')");
           
            //Realiasasi Murabahah Debit pada Piutang Murabahaha total jumlah Pokok dan margin
            @mysql_query("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                    values('$unit','$kode_trans','1431000','$tgl_system','Bukti SYSTEM','$ket','$Plafond','$userid')");
           
            @mysql_query("insert into jurnal_umum(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values('$nomor_jurnal','$kode_trans','$$tgl_system','$unit')");
            //Realisasi Piutang Margin   
            @mysql_query("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                values('$unit','$kode_trans','1413000','$tgl_system','Bukti SYSTEM','$ket','$piutang','$userid')");

            @mysql_query("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
            values('$unit','$kode_trans','1481000','$tgl_system','Bukti SYSTEM','$ket','$Plafond','$userid')");
           
            @mysql_query("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                    values('$unit','$kode_trans','1423000','$tgl_system','Bukti SYSTEM','$ket','$bagi_hasil','$userid')");
                   
            //@mysql_query("insert into jurnal_umum(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values('$nomor_jurnal','$kode_trans','$$tgl_system','$unit')");
           
            //@mysql_query("Update calon_loan set next_schedule = (select DATE_ADD(tgl_murab,INTERVAL 7 DAY) from calon_loan where no_anggota  ='" . $value . "') where no_anggota  ='" . $value . "'") or die(mysql_error("GAGAL INPUT"));
            @mysql_query ("delete from loan  WHERE cif ='$cif'")or die(mysql_error());
	@mysql_query ("delete from tunggakan  WHERE cif ='$cif'")or die(mysql_error());	
            @mysql_query("insert  INTO loan select * from calon_loan WHERE cif ='$cif'") or die(mysql_error("GAGAL INPUT"));
            @mysql_query("INSERT INTO `loan_detail` (code_kel,buss_date,no_anggota,cif,suffic,Cust_Short_name,Deal_type,bagi_hasil,Tenor,Plafond,unit,Contract_Date,hari_tagih,angsuran,storan,cao,USERID)
                        VALUES ('$code_kel','$buss_date','$no_anggota','$cif','$ke','$Cust_Short_name','$Deal_type','$bagi_hasil','$Tenor','$Plafond','$unit','$murab','$hari','$angsuran','$bulat','$cao','$USERID')") or die(mysql_error("GAGAL INPUT"));
           
@mysql_query("insert into simpanan(BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,KREDIT,TYPE,USERID,KET,REFF,CAO,kode_transaksi)
            values('$murab','$norek','$unit','$cif','$code_kel','$Plafond','01','$userid','$ket','','$cao','$kode_trans')");           
@mysql_query("insert into simpanan(BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,kode_transaksi)
            values('$murab','$norek','$unit','$cif','$code_kel','$Plafond','01','0','$userid','$ket','','$cao','$kode_trans')");
$update=mysql_query ("UPDATE simpanan SET blok='', TYPE='01' where cif='$cif' and type ='02' ");
$update=mysql_query ("UPDATE loan SET status='CETAK',gol ='1' where cif='$cif'");

           
           
            @mysql_query("INSERT INTO `rek_loan` (ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif,AO)
                    VALUES ('','$buss_date','$unit','$no_anggota','$os','0','L001','$ket','$USERID','REALISASI MURABAHAH','$cif','$cao')") or die(mysql_error("GAGAL INPUT"));
            @mysql_query ("delete from `calon_loan`  WHERE no_anggota ='" . $value . "'")or die(mysql_error());
           @mysql_query ("delete from pinjaman_detail where cif='$cif'"); 
            //@mysql_query("insert  into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF)VALUES('$buss_date','$no_anggota','$unit','$cif','$code_kel','0','01','0','$userid','') ") or die(mysql_error("GAGAL INPUT"));
               
                $newdatee = date ("Y-m-d", strtotime("+10 WEEK" , strtotime($maturity_date))) ;
                $awal_hari = $murab;
                $akhir_hari = $newdatee;
                $x=0;
                while (strtotime($awal_hari) < strtotime($akhir_hari)) {
                $x ++;
                $awal_hari= date ("Y-m-d", strtotime("+7 DAY", strtotime($awal_hari)));
                $detail= mysql_query("INSERT INTO pinjaman_detail_c (id,id_pinjam,cicilan,angsuran_pokok,bunga,tgl_jatuh_tempo,tgl_bayar,jumlah_bayar,ket)
                values('$d','$no_anggota','$x','$pokok','$ijaroh','$awal_hari','$awal','0','')") or die(mysql_error());
                   
                    }

                       

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
               
                $awal_hari = $murab;
                $new_akhir_hari = $newdate2;
                $xx=0;
                while (strtotime($awal_hari) < strtotime($new_akhir_hari)) {
                $xx ++;
                $reff="$no_anggota$xx";
                $awal_hari= date ("Y-m-d", strtotime("+7 DAY", strtotime($awal_hari)));
                $detail_data= mysql_query("INSERT INTO pinjaman_detail_temp (id,id_pinjam,cicilan,angsuran_pokok,bunga,tgl_jatuh_tempo,tgl_bayar,jumlah_bayar,ket,cif,unit,ao,code_kel)
                values('$reff','$no_anggota','$xx','$pokok','$ijaroh','$awal_hari','$awal','0','','$cif','$unit','$cao','$code_kel')") or die(mysql_error());
                }   
                @mysql_query ("delete from pinjaman_detail_temp where tgl_jatuh_tempo in (select tgl_libur from param_libur where tgl_libur=pinjaman_detail_temp.tgl_jatuh_tempo) and id_pinjam='$no_anggota'");
                $up=mysql_query("SET @count = 0;");
                $update=mysql_query ("UPDATE pinjaman_detail_temp SET cicilan=@count:=@count+1 where id_pinjam='$no_anggota' ORDER BY cicilan");
                @mysql_query ("delete from pinjaman_detail_temp where cicilan > '$Tenor' AND cif='$cif'");   

                    }

           
            else{
                @mysql_query ("delete from pinjaman_detail_c where id_pinjam='$no_anggota'");   
                $awal_hari = $murab;
                $akhir_hari = $maturity_date;
                $x1=0;
                while (strtotime($awal_hari) < strtotime($akhir_hari)){
                $x1 ++;
                $reff="$no_anggota$x1";
                $awal_hari= date ("Y-m-d", strtotime("+7 DAY", strtotime($awal_hari)));
                $update= mysql_query("INSERT INTO pinjaman_detail_temp (id,id_pinjam,cicilan,angsuran_pokok,bunga,tgl_jatuh_tempo,tgl_bayar,jumlah_bayar,ket,cif,unit,ao,code_kel)
                values('$reff','$no_anggota','$x1','$pokok','$ijaroh','$awal_hari','$awal','0','','$cif','$unit','$cao','$code_kel')") or die(mysql_error());
                           
                }
                @mysql_query ("delete from pinjaman_detail_temp where tgl_jatuh_tempo in (select tgl_libur from param_libur where tgl_libur=pinjaman_detail_temp.tgl_jatuh_tempo)");
                $up=mysql_query("SET @count = 0;");
                $update=mysql_query ("UPDATE pinjaman_detail_temp SET cicilan=@count:=@count+1 where id_pinjam='$no_anggota' ORDER BY cicilan");
                @mysql_query ("delete from pinjaman_detail_temp where cicilan > '$Tenor' AND cif='$cif'");   

            }
   
           
        //$cek_matur = mysql_fetch_array(mysql_query("select tgl_jatuh_tempo from pinjaman_detail  WHERE cicilan ='$tenor' and cif='$cif'")) or die(mysql_error());
        //$maturity=$cek_matur['tgl_jatuh_tempo'];
         //@mysql_query ("UPDATE loan SET maturity_date='$maturity' where cif='$cif' ");       
        //$cek_matur= mysql_query ("select tgl_jatuh_tempo from pinjaman_detail  WHERE cicilan ='$tenor' and cif='$cif'");
        //while($data_m = mysql_fetch_array($cek_matur)){
            //$maturity =$data_m['tgl_jatuh_tempo'];       
           
        //}
        //@mysql_query ("UPDATE loan SET maturity_date='$maturity' where cif='$cif' ");
    }

   
    echo '<script>alert("Pembiayaan sudah direalisasikan."); window.location="realisasi.php"</script>';
   
            } 
		}
            else {
                    echo '<script>alert("anda belum memilih data."); window.location="realisasi.php"</script>';
        }

?>
