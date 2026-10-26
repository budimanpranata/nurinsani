<?php require_once __DIR__ . '/koneksi_db.php'; 
date_default_timezone_set('Asia/Jakarta');
session_start();
ob_start();
	$userid=$_SESSION['userid'];
	$tanggal=date("y-m-d");

db_ni();
mysql_select_db('nur_insani');
include "function.php";

 
			
$cekbox = $_POST['cekbox'];
if ($cekbox) {
       foreach ($cekbox as $value) {
	
		
			$kode_tr=5;
			$kode_tr=acakangkahuruf($kode_tr);	   
		     $row = mysql_fetch_array(mysql_query("select DATE_ADD(tgl_wakalah,INTERVAL 7 DAY) as tgl_murab,tgl_akad,maturity_date,calon_loan.buss_date,calon_loan.code_kel,calon_loan.no_anggota,calon_loan.Cust_Short_name,calon_loan.status_app,
			calon_loan.Deal_type,suffix,bagi_hasil,Tenor,Plafond,os,angsuran,pokok,ijaroh,bulat,ke,usaha,calon_loan.unit,tgl_wakalah,hari,calon_loan.cao,calon_loan.USERID,calon_loan.status,calon_loan.cif
			 from calon_loan  
			 left join anggota on anggota.cif = calon_loan.cif
			 WHERE calon_loan.cif ='" . $value . "' ")) or die(mysql_error());
        //$row = mysql_fetch_array(mysql_query("select * from calon_loan  WHERE no_anggota ='" . $value . "'")) or die(mysql_error());
			$no_anggota=$row['no_anggota'];
			$tgl_wakalah= $row['tgl_wakalah'];
			$cif=$row['cif'];
			$Plafond=$row['Plafond'];
			$bagi_hasil=$row['bagi_hasil'];
			$piutang=$Plafond + $bagi_hasil;
			$tgl_murab=$row['tgl_akad'];
			$tgl_akad=$row['tgl_akad'];

			$buss_date=$row['buss_date'];
			$code_kel=$row['code_kel'];
			$Cust_Short_name=$row['Cust_Short_name'];
			$Deal_type=$row['Deal_type'];
			$suffix=$row['suffix'];
			$Tenor=$row['Tenor'];
			$maturity_date=$row['maturity_date'];
			$os=$row['os'];
			$angsuran=$row['angsuran'];
			$pokok=$row['pokok'];
			$ijaroh=$row['ijaroh'];
			$bulat=$row['bulat'];
			$status_app=$row['status_app'];
		
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


			$kodetr=5;
            $kodetr=acakangkahuruf($kodetr);

		
			$kode_trans="BU/".$unit .$kodetr;				
			$keterangan_transaksi="Realisasi MUSYAROKAH Unit $unit $cif";	
			$query = mysql_query("SELECT os FROM loan WHERE cif= '$cif' ");
		 $check = mysql_fetch_array($query);
		$os_lama=$check['os'];
		if ($os_lama > 0) {

			// jika data ditemukan
		echo "<script> alert ('Maaf  $cif mempunyai saldo OS sebesar $os_lama ')</script>";
		echo "<script language='javascript'>document.location.href='realisasi_wakalah.php'</script>";
		}else{				
			@mysql_query("insert into jurnal_umum(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values('$nomor_jurnal','$kode_trans','$tgl_wakalah','$unit')");

			//Dr aseet Murabahah		
			@mysql_query("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
			values('$unit','$kode_trans','1472000','$tgl_akad','Bukti SYSTEM','$keterangan_transaksi','$Plafond','$userid')");
					
			//cr aseet kas
			@mysql_query("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
			values('$unit','$kode_trans','1120000','$tgl_akad','Bukti SYSTEM','$keterangan_transaksi','$Plafond','$userid')");
			

			@mysql_query ("delete from loan  WHERE cif ='$cif' and unit ='$unit'")or die(mysql_error());
			@mysql_query ("delete from tunggakan  WHERE cif ='$cif' and unit ='$unit'")or die(mysql_error());	
            @mysql_query("insert  INTO loan select * from calon_loan WHERE cif ='$cif'") or die(mysql_error("GAGAL INPUT"));
			  @mysql_query("update loan set saldo_margin ='0' WHERE cif ='$cif'") or die(mysql_error("GAGAL INPUT"));
			
			@mysql_query("delete from calon_loan where cif ='$cif'");

			@mysql_query("insert into simpanan(BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,KREDIT,TYPE,USERID,KET,REFF,CAO,kode_transaksi)
            values('$tgl_akad','$norek','$unit','$cif','$code_kel','$Plafond','01','$userid','$keterangan_transaksi','','$cao','$kode_trans')");           
			@mysql_query("insert into simpanan(BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,kode_transaksi)
            values('$tgl_akad','$norek','$unit','$cif','$code_kel','$Plafond','01','0','$userid','$keterangan_transaksi','','$cao','$kode_trans')");
			$update=mysql_query ("UPDATE simpanan SET blok='', TYPE='01' where cif='$cif' and type ='02' ");
			$update=mysql_query ("UPDATE loan SET status='CETAK', gol ='1', deal_produk ='3' where cif='$cif'");

           
           
            @mysql_query("INSERT INTO `rek_loan` (ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif,AO)
                    VALUES ('','$buss_date','$unit','$no_anggota','$os','0','L001','$keterangan_transaksi','$USERID','REALISASI MURABAHAH','$cif','$cao')") or die(mysql_error("GAGAL INPUT"));
            //@mysql_query ("delete from `calon_loan`  WHERE no_anggota ='" . $value . "'")or die(mysql_error());
           @mysql_query ("delete from pinjaman_detail where cif='$cif'"); 
            //@mysql_query("insert  into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF)VALUES('$buss_date','$no_anggota','$unit','$cif','$code_kel','0','01','0','$userid','') ") or die(mysql_error("GAGAL INPUT"));
               
                $newdatee = date ("Y-m-d", strtotime("+10 WEEK" , strtotime($maturity_date))) ;
                $awal_hari = $tgl_akad;
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
                $detail_data= mysql_query("INSERT INTO pinjaman_detail_temp_mus (id,id_pinjam,cicilan,angsuran_pokok,bunga,tgl_jatuh_tempo,tgl_bayar,jumlah_bayar,ket,cif,unit,ao,code_kel)
                values('$reff','$no_anggota','$xx','$pokok','$ijaroh','$awal_hari','$awal','0','','$cif','$unit','$cao','$code_kel')") or die(mysql_error());
                }   
                @mysql_query ("delete from pinjaman_detail_temp_mus where tgl_jatuh_tempo in (select tgl_libur from param_libur where tgl_libur=pinjaman_detail_temp_mus.tgl_jatuh_tempo) and id_pinjam='$no_anggota'");
                $up=mysql_query("SET @count = 0;");
                $update=mysql_query ("UPDATE pinjaman_detail_temp_mus SET cicilan=@count:=@count+1 where id_pinjam='$no_anggota' ORDER BY cicilan");
                @mysql_query ("delete from pinjaman_detail_temp_mus where cicilan > '$Tenor' AND cif='$cif'");   

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
                $update= mysql_query("INSERT INTO pinjaman_detail_temp_mus (id,id_pinjam,cicilan,angsuran_pokok,bunga,tgl_jatuh_tempo,tgl_bayar,jumlah_bayar,ket,cif,unit,ao,code_kel)
                values('$reff','$no_anggota','$x1','0','0','$awal_hari','$awal','0','','$cif','$unit','$cao','$code_kel')") or die(mysql_error());
                           
                }
                @mysql_query ("delete from pinjaman_detail_temp_mus where tgl_jatuh_tempo in (select tgl_libur from param_libur where tgl_libur=pinjaman_detail_temp_mus.tgl_jatuh_tempo)");
                $up=mysql_query("SET @count = 0;");
                $update=mysql_query ("UPDATE pinjaman_detail_temp_mus SET cicilan=@count:=@count+1 where id_pinjam='$no_anggota' ORDER BY cicilan");
                @mysql_query ("delete from pinjaman_detail_temp_mus where cicilan > '$Tenor' AND cif='$cif'");   

            }
   



			}
			echo '<script>alert("Musyarakah sudah direalisasikan."); window.location="realisasi_musyarokah.php"</script>';
		
			

		}
				
    
			}  
			else {
					echo '<script>alert("anda belum memilih data."); window.location="realisasi_musyarokah.php"</script>';
		}

?>
