<?php require_once __DIR__ . '/koneksi_db.php';
ini_set ('display_errors',1);
date_default_timezone_set('Asia/Jakarta');
session_start();
require_once __DIR__ . '/inc/cek_login.php';
wajib_login();
ob_start();
    $userid=$_SESSION['userid'];
$tgl_system=$_SESSION['stgl_system'];
    $tanggal=date("y-m-d");

include "function.php";

 
           
$cekbox = isset($_POST['cekbox']) ? $_POST['cekbox'] : null;
if ($cekbox) {
       foreach ($cekbox as $value) {
       
            $kode_tr=5;
            $kode_tr=acakangkahuruf($kode_tr);      
             $row = db_satu("select DATE_ADD(tgl_wakalah,INTERVAL 7 DAY) as tgl_murab1,DATE_ADD(tgl_wakalah,INTERVAL 26 WEEK) as matur,tgl_murab as tgl_murab,calon_loan.buss_date,calon_loan.code_kel,calon_loan.no_anggota,calon_loan.Cust_Short_name,maturity_date,
            calon_loan.Deal_type,suffix,bagi_hasil,Tenor,Plafond,os,angsuran,pokok,ijaroh,bulat,ke,usaha,calon_loan.unit,tgl_wakalah,hari,calon_loan.cao,calon_loan.USERID,calon_loan.status,calon_loan.cif
             from calon_loan 
             inner join anggota on anggota.cif = calon_loan.cif
             WHERE no_anggota =?", 's', (string) $value) or die(mysql_error());
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
            // versi lama di-escape untuk disisipkan ke SQL; dengan prepared statement nilai asli yang dikirim (hasil tersimpan sama)
            $Cust_Short_name=$row['Cust_Short_name'];
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
 		$check = db_satu("SELECT os FROM loan WHERE cif= ? ", 's', (string) $cif);
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
            db_jalankan("insert into jurnal_umum(nomor_jurnal,kode_transaksi,tanggal_selesai) values(?,?,?)", 'sss', (string) (isset($nomor_jurnal) ? $nomor_jurnal : ''), (string) $kode_trans, (string) $tgl_system);
           
            //Realiasasi Murabahah Debit pada Aset Murabahaha total Plafond
            db_jalankan("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
            values(?,?,'1481000',?,'Bukti SYSTEM',?,?,?)", 'ssssss', (string) $unit, (string) $kode_trans, (string) $tgl_system, (string) $ket, (string) $Plafond, (string) $userid);
           
            //Realiasasi Murabahah Debit pada Piutang Murabahaha total jumlah Pokok dan margin
            db_jalankan("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                    values(?,?,'1431000',?,'Bukti SYSTEM',?,?,?)", 'ssssss', (string) $unit, (string) $kode_trans, (string) $tgl_system, (string) $ket, (string) $Plafond, (string) $userid);
           
            // versi lama menulis '$$tgl_system' (tanda $ ikut terkirim di depan tanggal); dipertahankan
            db_jalankan("insert into jurnal_umum(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values(?,?,?,?)", 'ssss', (string) (isset($nomor_jurnal) ? $nomor_jurnal : ''), (string) $kode_trans, '$' . $tgl_system, (string) $unit);
            //Realisasi Piutang Margin   
            db_jalankan("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                values(?,?,'1413000',?,'Bukti SYSTEM',?,?,?)", 'ssssss', (string) $unit, (string) $kode_trans, (string) $tgl_system, (string) $ket, (string) $piutang, (string) $userid);

            db_jalankan("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
            values(?,?,'1481000',?,'Bukti SYSTEM',?,?,?)", 'ssssss', (string) $unit, (string) $kode_trans, (string) $tgl_system, (string) $ket, (string) $Plafond, (string) $userid);
           
            db_jalankan("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                    values(?,?,'1423000',?,'Bukti SYSTEM',?,?,?)", 'ssssss', (string) $unit, (string) $kode_trans, (string) $tgl_system, (string) $ket, (string) $bagi_hasil, (string) $userid);
                   
            //@mysql_query("insert into jurnal_umum(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values('$nomor_jurnal','$kode_trans','$$tgl_system','$unit')");
           
            //@mysql_query("Update calon_loan set next_schedule = (select DATE_ADD(tgl_murab,INTERVAL 7 DAY) from calon_loan where no_anggota  ='" . $value . "') where no_anggota  ='" . $value . "'") or die(mysql_error("GAGAL INPUT"));
            db_jalankan("delete from loan  WHERE cif =?", 's', (string) $cif) or die(mysql_error());
	db_jalankan("delete from tunggakan  WHERE cif =?", 's', (string) $cif) or die(mysql_error());	
            db_jalankan("insert  INTO loan select * from calon_loan WHERE cif =?", 's', (string) $cif) or die(mysql_error());
            db_jalankan("INSERT INTO `loan_detail` (code_kel,buss_date,no_anggota,cif,suffic,Cust_Short_name,Deal_type,bagi_hasil,Tenor,Plafond,unit,Contract_Date,hari_tagih,angsuran,storan,cao,USERID)
                        VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)", 'sssssssssssssssss', (string) $code_kel, (string) $buss_date, (string) $no_anggota, (string) $cif, (string) $ke, (string) $Cust_Short_name, (string) $Deal_type, (string) $bagi_hasil, (string) $Tenor, (string) $Plafond, (string) $unit, (string) $murab, (string) $hari, (string) $angsuran, (string) $bulat, (string) $cao, (string) $USERID) or die(mysql_error());
           
db_jalankan("insert into simpanan(BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,KREDIT,TYPE,USERID,KET,REFF,CAO,kode_transaksi)
            values(?,?,?,?,?,?,'01',?,?,'',?,?)", 'ssssssssss', (string) $murab, (string) $norek, (string) $unit, (string) $cif, (string) $code_kel, (string) $Plafond, (string) $userid, (string) $ket, (string) $cao, (string) $kode_trans);           
db_jalankan("insert into simpanan(BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,kode_transaksi)
            values(?,?,?,?,?,?,'01','0',?,?,'',?,?)", 'ssssssssss', (string) $murab, (string) $norek, (string) $unit, (string) $cif, (string) $code_kel, (string) $Plafond, (string) $userid, (string) $ket, (string) $cao, (string) $kode_trans);
$update=db_jalankan("UPDATE simpanan SET blok='', TYPE='01' where cif=? and type ='02' ", 's', (string) $cif);
$update=db_jalankan("UPDATE loan SET status='CETAK',gol ='1' where cif=?", 's', (string) $cif);

           
           
            db_jalankan("INSERT INTO `rek_loan` (ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif,AO)
                    VALUES ('',?,?,?,?,'0','L001',?,?,'REALISASI MURABAHAH',?,?)", 'ssssssss', (string) $buss_date, (string) $unit, (string) $no_anggota, (string) $os, (string) $ket, (string) $USERID, (string) $cif, (string) $cao) or die(mysql_error());
            db_jalankan("delete from `calon_loan`  WHERE no_anggota =?", 's', (string) $value) or die(mysql_error());
           db_jalankan("delete from pinjaman_detail where cif=?", 's', (string) $cif);  
            //@mysql_query("insert  into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF)VALUES('$buss_date','$no_anggota','$unit','$cif','$code_kel','0','01','0','$userid','') ") or die(mysql_error("GAGAL INPUT"));
               
                $newdatee = date ("Y-m-d", strtotime("+10 WEEK" , strtotime($maturity_date))) ;
                $awal_hari = $murab;
                $akhir_hari = $newdatee;
                $x=0;
                while (strtotime($awal_hari) < strtotime($akhir_hari)) {
                $x ++;
                $awal_hari= date ("Y-m-d", strtotime("+7 DAY", strtotime($awal_hari)));
                $detail= db_jalankan("INSERT INTO pinjaman_detail_c (id,id_pinjam,cicilan,angsuran_pokok,bunga,tgl_jatuh_tempo,tgl_bayar,jumlah_bayar,ket)
                values(?,?,?,?,?,?,?,'0','')", 'sssssss', (string) (isset($d) ? $d : ''), (string) $no_anggota, (string) $x, (string) $pokok, (string) $ijaroh, (string) $awal_hari, (string) (isset($awal) ? $awal : '')) or die(mysql_error());
                   
                    }

                       

        $cek_tanggal= db_semua("SELECT COUNT(tgl_libur) as libur from param_libur
        INNER join pinjaman_detail_c on pinjaman_detail_c.tgl_jatuh_tempo = param_libur.tgl_libur where id_pinjam=?", 's', (string) $no_anggota);
        foreach ($cek_tanggal as $data_libur){
            $libur =$data_libur['libur'];
           
           
        }
       
       
        if ($libur >0){
            db_jalankan("delete from pinjaman_detail_c where id_pinjam=?", 's', (string) $no_anggota);
      
         
       
         $newdate3 = date ("Y-m-d", strtotime("+$libur WEEK" , strtotime ($maturity_date))) ;
         $cek = db_semua("SELECT * FROM param_libur where tgl_libur=?", 's', (string) $newdate3);
          if(count($cek)==1){
       
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
                $detail_data= db_jalankan("INSERT INTO pinjaman_detail_temp (id,id_pinjam,cicilan,angsuran_pokok,bunga,tgl_jatuh_tempo,tgl_bayar,jumlah_bayar,ket,cif,unit,ao,code_kel)
                values(?,?,?,?,?,?,?,'0','',?,?,?,?)", 'sssssssssss', (string) $reff, (string) $no_anggota, (string) $xx, (string) $pokok, (string) $ijaroh, (string) $awal_hari, (string) (isset($awal) ? $awal : ''), (string) $cif, (string) $unit, (string) $cao, (string) $code_kel) or die(mysql_error());
                }   
                db_jalankan("delete from pinjaman_detail_temp where tgl_jatuh_tempo in (select tgl_libur from param_libur where tgl_libur=pinjaman_detail_temp.tgl_jatuh_tempo) and id_pinjam=?", 's', (string) $no_anggota);
                // @count harus di koneksi yang sama dengan UPDATE berikutnya (db_ni() selalu koneksi yang sama)
                $up=db_jalankan("SET @count = 0");
                $update=db_jalankan("UPDATE pinjaman_detail_temp SET cicilan=@count:=@count+1 where id_pinjam=? ORDER BY cicilan", 's', (string) $no_anggota);
                db_jalankan("delete from pinjaman_detail_temp where cicilan > ? AND cif=?", 'ss', (string) $Tenor, (string) $cif);      

                    }

           
            else{
                db_jalankan("delete from pinjaman_detail_c where id_pinjam=?", 's', (string) $no_anggota);
                $awal_hari = $murab;
                $akhir_hari = $maturity_date;
                $x1=0;
                while (strtotime($awal_hari) < strtotime($akhir_hari)){
                $x1 ++;
                $reff="$no_anggota$x1";
                $awal_hari= date ("Y-m-d", strtotime("+7 DAY", strtotime($awal_hari)));
                $update= db_jalankan("INSERT INTO pinjaman_detail_temp (id,id_pinjam,cicilan,angsuran_pokok,bunga,tgl_jatuh_tempo,tgl_bayar,jumlah_bayar,ket,cif,unit,ao,code_kel)
                values(?,?,?,?,?,?,?,'0','',?,?,?,?)", 'sssssssssss', (string) $reff, (string) $no_anggota, (string) $x1, (string) $pokok, (string) $ijaroh, (string) $awal_hari, (string) (isset($awal) ? $awal : ''), (string) $cif, (string) $unit, (string) $cao, (string) $code_kel) or die(mysql_error());
                           
                }
                db_jalankan("delete from pinjaman_detail_temp where tgl_jatuh_tempo in (select tgl_libur from param_libur where tgl_libur=pinjaman_detail_temp.tgl_jatuh_tempo)");
                // @count harus di koneksi yang sama dengan UPDATE berikutnya (db_ni() selalu koneksi yang sama)
                $up=db_jalankan("SET @count = 0");
                $update=db_jalankan("UPDATE pinjaman_detail_temp SET cicilan=@count:=@count+1 where id_pinjam=? ORDER BY cicilan", 's', (string) $no_anggota);
                db_jalankan("delete from pinjaman_detail_temp where cicilan > ? AND cif=?", 'ss', (string) $Tenor, (string) $cif);      

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
