<?php require_once __DIR__ . '/koneksi_db.php';
error_reporting(E_ALL);
date_default_timezone_set('Asia/Jakarta');
require_once __DIR__ . '/inc/notif.php';
session_start();
require_once __DIR__ . '/inc/cek_login.php';
wajib_level('unit');
require_once __DIR__ . '/inc/cek_login.php';
wajib_login();
ob_start();
    $userid=$_SESSION['userid'];
$tgl_system=$_SESSION['stgl_system'];


$pilih=isset($_SESSION['spilih']) ? $_SESSION['spilih'] : null;
$wadiah=isset($_SESSION['swadiah']) ? $_SESSION['swadiah'] : null;
$muqosah=isset($_SESSION['snominal']) ? $_SESSION['snominal'] : null;
$tenor=isset($_SESSION['stenor']) ? $_SESSION['stenor'] : null;

include "function.php";
$checbox = isset($_POST['cekbox']) ? $_POST['cekbox'] : null;
$checbox = is_array($checbox) ? array_values($checbox) : ($checbox ? array($checbox) : null);
$nomor_jurnal=''; // tidak pernah diisi di versi lama (jurnal_umum.nomor_jurnal kosong); kini eksplisit agar tanpa warning



if (isset($_POST["realisasi"])){

    if (!$checbox) {
        notif_pergi('anda belum memilih data.', 'rest_kel_pokok.php');
    } elseif ($pilih!='01' && $pilih!='02') {
        // Muqosah (04) belum ada logika bisnisnya (keputusan user 2026-11-17); dulu pesan ini muncul sekali per anggota
        notif_pergi('Maaf Menu Belum tersedia.', 'rest_kel_pokok.php');
    } else {
        // §2f A (2026-11-17, perintah user): semua CIF yang dicentang wajib milik unit user (dulu CIF unit lain ikut diproses).
        // Dicek semua sebelum ada yang diproses; satu saja bukan milik unit -> HTTP 403, tidak ada data berubah.
        foreach ($checbox as $c) {
            wajib_cif_unit((string) $c);
        }
        $jml_proses=0;
        $jml_lewat=0;
        for ($i=0; $i <sizeof($checbox);$i++){
        // §2f B (2026-11-17, perintah user): refresh/klik ganda dulu memproses ulang (simpanan, jurnal, rek_loan ganda).
        // Kunci per CIF + lewati anggota yang sudah direstrukturisasi hari ini (sama dengan realisasi_res.php).
        $kunci='realisasi_res_' . (string) $checbox[$i];
        db_satu("SELECT GET_LOCK(?, 30) AS kunci", 's', $kunci);
        $sudah=db_satu("select 1 as ada from loan where cif=? and status_app in ('REST','REST_POKOK_MARGIN') and tgl_akad=?", 'ss', (string) $checbox[$i], (string) $tgl_system);
        if ($sudah) {
            db_satu("SELECT RELEASE_LOCK(?) AS lepas", 's', $kunci);
            $jml_lewat++;
            continue;
        }

    $row = db_satu("select DATE_ADD(tgl_wakalah,INTERVAL 7 DAY) as tgl_murab1,DATE_ADD(?,INTERVAL ? WEEK) as matur,tgl_murab,loan.buss_date,loan.code_kel,loan.no_anggota,loan.Cust_Short_name,maturity_date,Tenor,
    loan.Deal_type,suffix,bagi_hasil,Tenor,Plafond,os,angsuran,pokok,ijaroh,bulat,ke,usaha,loan.unit,tgl_wakalah,hari,loan.cao,loan.USERID,loan.status,loan.cif,saldo_margin
    from loan 
     left join anggota on anggota.cif = loan.cif
     WHERE loan.cif =?", 'sss', (string) $tgl_system, (string) $tenor, (string) $checbox[$i]);


            $no_anggota=$row['no_anggota'];
            // §2f (2026-11-17, perintah user): dulu $norek/$code_kel tidak pernah diisi -> baris simpanan
            // "Realisasi Restrukturisasi" tersimpan dengan NOREK/CODE_KEL kosong. Kini dari loan (no_anggota, code_kel).
            $norek=$row['no_anggota'];
            $code_kel=$row['code_kel'];
            $kelompok=$row['code_kel']; // dulu tidak diisi -> rest_covid.code_kel kosong
            $cif=$row['cif'];
            $bagi_hasil=$row['bagi_hasil'];
            $os=$row['os'];
            $piutang=$os;
            $Cust_Short_name=$row['Cust_Short_name']; // dikirim sebagai parameter, tidak perlu di-escape
            $maturity_date=$row['matur'];
$pokok=$row['pokok'];
$tenor_lama=$row['Tenor'];
$angsur=$row['angsuran'];
$ijaroh=$row['ijaroh'];
$matur=$row['maturity_date'];
$bulat_lama=$row['bulat'];
         
            
			$saldo_margin=$row['saldo_margin'];
            $cao=$row['cao'];
            $USERID=$row['USERID'];
			$unit=$row['unit'];

            $kodetr=5;
            $kodetr=acakangkahuruf($kodetr);
            $kode_trans="BU/".$unit .$kodetr;


    $row_saldo=db_satu("select sum(kredit-DEBIT) as saldo_simpanan from simpanan where cif =? and type not in('04','05') ", 's', (string) $cif) or die(mysql_error());
       $saldo_simpanan=$row_saldo['saldo_simpanan'];
if($wadiah =='ya'){
	$simpanan=$saldo_simpanan;
    


} else{
	$simpanan=0;
}




                 
            

if ($pilih=='01') {
	$angsuran_pokok=''; $angsuran_margin=''; // tidak diisi di jenis 01 versi lama (rest_covid kosong); eksplisit agar tanpa warning
	$kode_tr=5;
            $kode_tr=acakangkahuruf($kode_tr);      
            
            $ket="Realisasi Restrukturisasi $Cust_Short_name";
			$pokok_os=$os - $saldo_margin;
			// §2f (2026-11-19, perintah user): simpanan yang diambil maksimal sebesar sisa pokok (dulu simpanan > sisa pokok
			// -> OS, angsuran & jurnal minus, kelebihan simpanan ikut terdebet). Debit simpanan & jurnal memakai nilai ini.
			if (compat_num($simpanan) > compat_num($pokok_os)) {
				$simpanan = $pokok_os;
			}

			//Hitung Restruturisasai
			$os_res=$pokok_os-$simpanan;
			
			$pokok=compat_bagi($os_res, $tenor); // PHP 5: pembagi 0/kosong -> false, bukan error

			$angsuran=$pokok;
			
            $totalharga = $angsuran;
			$totalharga=ceil(compat_num($totalharga));
                            if (substr($totalharga,-4)>9999){
                                $bulat=round(compat_num($totalharga+10000),-4);
                            } else {
                                $bulat=round(compat_num($totalharga),-4)+5000;
                            } 

             		

            
            $kode_transaksi="BU/".$kode_tr;
            //pencatatan du jurnal realasisi Piutang Murabahaha total jumlah Pokok dan margin
            db_jalankan("insert into jurnal_umum(nomor_jurnal,kode_transaksi,tanggal_selesai) values(?,?,?)", 'sss', (string) $nomor_jurnal, (string) $kode_trans, (string) $tgl_system);
           
            //Realiasasi Murabahah Debit pada Aset Murabahaha total Plafond
            db_jalankan("insert into simpanan(BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,kode_transaksi)
            values(?,?,?,?,?,?,'01','0',?,?,'',?,?)", 'ssssssssss', (string) $tgl_system, (string) $norek, (string) $unit, (string) $cif, (string) $code_kel, (string) $simpanan, (string) $userid, (string) "Realisasi Restrukturisasi $Cust_Short_name $cif", (string) $cao, (string) $kode_trans);
            
            db_jalankan("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                        values(?,?,'2101000',?,'Bukti SYSTEM',?,?,?)", 'ssssss', (string) $unit, (string) $kode_trans, (string) $tgl_system, (string) $ket, (string) $simpanan, (string) $userid);
                       
            //Realiasasi Murabahah Debit pada Piutang Murabahaha total jumlah Pokok dan margin
            db_jalankan("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                    values(?,?,'1413000',?,'Bukti SYSTEM',?,?,?)", 'ssssss', (string) $unit, (string) $kode_trans, (string) $tgl_system, (string) $ket, (string) $simpanan, (string) $userid);
                      
            //Realisasi PMYD   
            db_jalankan("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
            values(?,?,'1423000',?,'Bukti SYSTEM',?,?,?)", 'ssssss', (string) $unit, (string) $kode_trans, (string) $tgl_system, (string) $ket, (string) $saldo_margin, (string) $userid);
			
			//Piutang murabahah
			db_jalankan("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                    values(?,?,'1413000',?,'Bukti SYSTEM',?,?,?)", 'ssssss', (string) $unit, (string) $kode_trans, (string) $tgl_system, (string) $ket, (string) $saldo_margin, (string) $userid);
			
			
			//Restrukrisasi
			db_jalankan("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                    values(?,?,'1411000',?,'Bukti SYSTEM',?,?,?)", 'ssssss', (string) $unit, (string) $kode_trans, (string) $tgl_system, (string) $ket, (string) $os_res, (string) $userid);
			
			db_jalankan("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                    values(?,?,'1413000',?,'Bukti SYSTEM',?,?,?)", 'ssssss', (string) $unit, (string) $kode_trans, (string) $tgl_system, (string) $ket, (string) $os_res, (string) $userid);
					
                    
           
            //@mysql_query("Update calon_loan set next_schedule = (select DATE_ADD(tgl_murab,INTERVAL 7 DAY) from calon_loan where no_anggota  ='" . $value . "') where no_anggota  ='" . $value . "'") or die(mysql_error("GAGAL INPUT"));
            db_jalankan("update loan  set os =?, saldo_margin='0',angsuran=?,pokok=?,bulat=?,tenor=?,maturity_date =?,run_tenor='0', ke ='1',ijaroh='0',gol='1',status_app='REST',tgl_murab=?, deal_produk ='1' WHERE cif =?", 'ssssssss', (string) $os_res, (string) $angsuran, (string) $angsuran, (string) $bulat, (string) $tenor, (string) $maturity_date, (string) $tgl_system, (string) $cif) or die(mysql_error());
           db_jalankan("delete from tunggakan WHERE cif =?", 's', (string) $cif) or die(mysql_error());
db_jalankan("delete from  rest_covid where cif =?", 's', (string) $cif) or die(mysql_error());	
db_jalankan("insert into rest_covid values('',?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)", 'sssssssssssssss', (string) $tgl_system, (string) $cif, (string) $angsur, (string) $pokok, (string) $ijaroh, (string) $bulat_lama, (string) $tenor_lama, (string) $matur, (string) $angsuran, (string) $angsuran_pokok, (string) $angsuran_margin, (string) $bulat, (string) $tenor, (string) $maturity_date, (string) $kelompok) or die(mysql_error());


            db_jalankan("insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif,AO)VALUES('',?,?,?,'0',?,'03',?,?,'REALISASI BAGHAS',?,(select cao from loan where cif=? limit 1))", 'ssssssss', (string) $tgl_system, (string) $unit, (string) $no_anggota, (string) $saldo_margin, (string) $ket, (string) $userid, (string) $cif, (string) $cif) or die(mysql_error());
           


                   
               

				// §2f E (2026-11-17, perintah user): dulu "maturity_date=? cif=?" tanpa WHERE -> SQL gagal, tgl_akad tidak terisi.
				db_jalankan("update  loan  set tgl_murab=?,tgl_akad=?,maturity_date=? WHERE cif=?", 'ssss', (string) $tgl_system, (string) $tgl_system, (string) $maturity_date, (string) $cif); 

            
   

    $jml_proses++; // pesan sukses kini sekali di akhir (dulu sekali per anggota)
    }

               
		
    elseif($pilih=='02') {


        $kode_tr=5;
            $kode_tr=acakangkahuruf($kode_tr);      
            
            $ket="Realisasi Restrukturisasi";
			$pokok_os=$os - $saldo_margin;
            $pembiayaan =$os;
			
			//Hitung Restruturisasai
			$os_res=$pembiayaan-$simpanan;
			
			$pokok=compat_bagi($os_res, $tenor); // PHP 5: pembagi 0/kosong -> false, bukan error
			$angsuran=$pokok;
            $os_pokok=$os_res - $saldo_margin;
            $angsuran_pokok = compat_bagi($os_pokok, $tenor); // PHP 5: pembagi 0/kosong -> false, bukan error
            
            $angsuran_margin =compat_bagi($saldo_margin, $tenor); // PHP 5: pembagi 0/kosong -> false, bukan error

			
           $totalharga = $angsuran;
			$totalharga=ceil(compat_num($totalharga));
                            if (substr($totalharga,-4)>9999){
                                $bulat=round(compat_num($totalharga+10000),-4);
                            } else {
                                $bulat=round(compat_num($totalharga),-4)+5000;
                            } 

             		
            $kodetr=5;
            $kodetr=acakangkahuruf($kodetr);
            $kode_trans="BU/".$unit .$kodetr;
            
            $kode_transaksi="BU/".$kode_tr;
            //pencatatan du jurnal realasisi Piutang Murabahaha total jumlah Pokok dan margin
            db_jalankan("insert into jurnal_umum(nomor_jurnal,kode_transaksi,tanggal_selesai) values(?,?,?)", 'sss', (string) $nomor_jurnal, (string) $kode_trans, (string) $tgl_system);
           
            //Realiasasi Murabahah Debit pada Aset Murabahaha total Plafond
            db_jalankan("insert into simpanan(BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,kode_transaksi)
values(?,?,?,?,?,?,'01','0',?,?,'',?,?)", 'ssssssssss', (string) $tgl_system, (string) $norek, (string) $unit, (string) $cif, (string) $code_kel, (string) $simpanan, (string) $userid, (string) "Realisasi Restrukturisasi $Cust_Short_name $cif", (string) $cao, (string) $kode_trans);

db_jalankan("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
            values(?,?,'2101000',?,'Bukti SYSTEM',?,?,?)", 'ssssss', (string) $unit, (string) $kode_trans, (string) $tgl_system, (string) $ket, (string) $simpanan, (string) $userid);
           
            //Realiasasi Murabahah Debit pada Piutang Murabahaha total jumlah Pokok dan margin
            db_jalankan("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                    values(?,?,'1413000',?,'Bukti SYSTEM',?,?,?)", 'ssssss', (string) $unit, (string) $kode_trans, (string) $tgl_system, (string) $ket, (string) $simpanan, (string) $userid);
                      
             
			db_jalankan("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
            values(?,?,'1411000',?,'Bukti SYSTEM',?,?,?)", 'ssssss', (string) $unit, (string) $kode_trans, (string) $tgl_system, (string) $ket, (string) $os_res, (string) $userid);

            db_jalankan("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
            values(?,?,'1423000',?,'Bukti SYSTEM',?,?,?)", 'ssssss', (string) $unit, (string) $kode_trans, (string) $tgl_system, (string) $ket, (string) $saldo_margin, (string) $userid);
			
			
            db_jalankan("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
            values(?,?,'1413000',?,'Bukti SYSTEM',?,?,?)", 'ssssss', (string) $unit, (string) $kode_trans, (string) $tgl_system, (string) $ket, (string) $os_res, (string) $userid);
					
			db_jalankan("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                    values(?,?,'1421000',?,'Bukti SYSTEM',?,?,?)", 'ssssss', (string) $unit, (string) $kode_trans, (string) $tgl_system, (string) $ket, (string) $saldo_margin, (string) $userid);
					
                    
           
            //@mysql_query("Update calon_loan set next_schedule = (select DATE_ADD(tgl_murab,INTERVAL 7 DAY) from calon_loan where no_anggota  ='" . $value . "') where no_anggota  ='" . $value . "'") or die(mysql_error("GAGAL INPUT"));
db_jalankan("update loan  set os =?, saldo_margin=?,angsuran=?,pokok=?,ijaroh =?,maturity_date =?,bulat=?,tenor=?,run_tenor='0',tgl_murab=? ,ke ='1',gol='1',status_app='REST_POKOK_MARGIN', deal_produk ='1' WHERE cif =?", 'ssssssssss', (string) $os_res, (string) $saldo_margin, (string) $angsuran, (string) $angsuran_pokok, (string) $angsuran_margin, (string) $maturity_date, (string) $bulat, (string) $tenor, (string) $tgl_system, (string) $cif) or die(mysql_error());


db_jalankan("delete from  rest_covid where cif =?", 's', (string) $cif) or die(mysql_error());	
db_jalankan("insert into rest_covid values('',?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)", 'sssssssssssssss', (string) $tgl_system, (string) $cif, (string) $angsur, (string) $pokok, (string) $ijaroh, (string) $bulat_lama, (string) $tenor_lama, (string) $matur, (string) $angsuran, (string) $angsuran_pokok, (string) $angsuran_margin, (string) $bulat, (string) $tenor, (string) $maturity_date, (string) $kelompok) or die(mysql_error());


           db_jalankan("delete from tunggakan WHERE cif =?", 's', (string) $cif) or die(mysql_error());
            //mysql_query("insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif)VALUES('','$tgl_system','$unit','$no_anggota','0','$saldo_margin','03','$ket','$userid','REALISASI BAGHAS','$cif')") or die(mysql_error("GAGAL INPUT"));
           


                   
               

				// §2f E (2026-11-17, perintah user): dulu "maturity_date=? cif=?" tanpa WHERE -> SQL gagal, tgl_akad tidak terisi.
				db_jalankan("update  loan  set tgl_murab=?,tgl_akad=?,maturity_date=? WHERE cif=?", 'ssss', (string) $tgl_system, (string) $tgl_system, (string) $maturity_date, (string) $cif); 
        $jml_proses++;




    }

        db_satu("SELECT RELEASE_LOCK(?) AS lepas", 's', $kunci);
         }

        $pesan = $pilih=='01' ? 'Restruturisasi Pokok sudah direalisasi' : 'Restruturisasi Pokok Margin sudah direalisasi';
        if ($jml_proses > 0) {
            $pesan .= " ($jml_proses anggota).";
            if ($jml_lewat > 0) {
                $pesan .= " $jml_lewat anggota dilewati karena sudah direstrukturisasi hari ini.";
            }
            notif_pergi($pesan, 'rest_kel_pokok.php', 'success');
        } else {
            notif_pergi('Maaf, anggota yang dipilih sudah direstrukturisasi hari ini.', 'rest_kel_pokok.php');
        }
    }
}

?>
