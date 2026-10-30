<?php require_once __DIR__ . '/../koneksi_db.php';
/**
 * Menu Realisasi > Hapus Buku (write off).
 *   Simpan  : catat jurnal hapus buku sesuai status pembiayaan dan jenis WO (meninggal, NPL, hapus tagih).
 *   Selesai : nolkan OS/margin, hapus tunggakan, catat jurnal_umum.
 *   Cancel  : hapus satu baris transaksi (link terenkripsi).
 * Logika dan urutan proses sama dengan versi lama; hanya tampilan dan query (prepared statement) yang berubah.
 */
require_once __DIR__ . '/../inc/tampilan.php';
session_start();
require_once __DIR__ . '/../inc/cek_login.php';
wajib_login();
$userid=$_SESSION['userid'];

    include "../encryption/function.php";

    //untuk waktu
    $tanggal=date("y-m-d");

    $id_user = db_satu("select kode_branch from user where userid=?", 's', (string) $userid);
    $unit = $id_user ? $id_user['kode_branch'] : null;

            //jurnal baru. cari nomor paling besar yaitu nomor jurnal terakhir
            $jurnal_umum=array_values((array) db_satu("SELECT max(nomor_jurnal) FROM jurnal_umum where unit =? ORDER BY tanggal_selesai DESC", 's', (string) $unit));
            $nomor_jurnal=$jurnal_umum[0]+1;
            $kode_transaksi="BU/".$unit .$nomor_jurnal;
// nomor bukti yang tampil di form dihitung sebelum proses simpan (sama seperti versi lama)
$kode_bukti_form = $kode_transaksi;
$tanggal_form = empty($_POST['tanggal_transaksi']) ? date('Y-m-d') : $_POST['tanggal_transaksi'];

// pesan/alert/script dari proses ditampilkan di bawah form, seperti versi lama
ob_start();

            //untuk menyimpan transaksi
            if(isset($_POST['simpan'])){
         
                $kode_transaksi=$_POST['kode_bukti'];
                $tanggal_transaksi=$_POST['tanggal_transaksi'];
                $keterangan_transaksi=isset($_POST['keterangan_transaksi']) ? $_POST['keterangan_transaksi'] : null; // field ini tidak ada di form (versi lama)
                $cif=$_POST['kode_rekening'];
                $ke =$_POST['ke'];
                $jenis=$_POST['jenis'];
                $margin=$_POST['margin'];
                $pokok=$_POST['pokok'];
                $nama=$_POST['nama']; // dikirim sebagai parameter, tidak perlu di-escape
                $tot_wo=$margin+$pokok;

                $data_cif=db_satu("select status_app from loan WHERE cif =?", 's', (string) $cif) or die(mysql_error());
                $status_app=$data_cif['status_app'];
                 
                     
        switch ($status_app) {
        case 'MURAB':
                if($jenis=='1'){
                    $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                                    values(?,?,'1423000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama ", (string) $margin, (string) $userid);
                    $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                                    values(?,?,'1413000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama", (string) $margin, (string) $userid);                 
                if ($ke >'13'){
                    $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                                    values(?,?,'1512000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama ", (string) $pokok, (string) $userid);
                    $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                                    values(?,?,'1413000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama", (string) $pokok, (string) $userid);                 
 
                }else{
                    $row_sal=db_satu("select(sum(KREDIT) - sum(DEBIT))as saldo from simpanan WHERE cif =? and TYPE not in ('04','05')", 's', (string) $cif) or die(mysql_error());
                    $sisa=$row_sal['saldo'];
                     
                    $kurang_stor_wo=$tot_wo-$sisa;
                    if($sisa>=$pokok){
                                     
                     
                    $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                                    values(?,?,'2101000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama ", (string) $pokok, (string) $userid);
                    $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                                    values(?,?,'1413000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama", (string) $pokok, (string) $userid);       
                    $query=db_jalankan("insert  into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,kode_transaksi)VALUES(?,?,?,?,?,?,'01','0',?,'Realisasi Hapus Buku','',?,?) ", 'sssssssss', (string) $tanggal_transaksi, (string) $no_anggota, (string) $unit, (string) $cif, (string) $code_kel, (string) $pokok, (string) $userid, (string) $cao, (string) $kode_transaksi) or die(mysql_error());             
         
                 
                 
                    }else{
                    $row_sal=db_satu("select(sum(KREDIT) - sum(DEBIT))as saldo from simpanan WHERE cif =? and TYPE not in ('04','05')", 's', (string) $cif) or die(mysql_error());
                    $sisa=$row_sal['saldo'];
                        $kurang=$pokok-$sisa;
                        $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                                    values(?,?,'1512000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama ", (string) $kurang, (string) $userid);
                        $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                                    values(?,?,'2101000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama ", (string) $sisa, (string) $userid);             
                                     
                    $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                                    values(?,?,'1413000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama", (string) $pokok, (string) $userid);
                     
                    $query=db_jalankan("insert  into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,kode_transaksi)VALUES(?,?,?,?,?,?,'01','0',?,'Realisasi Hapus Buku','',?,?) ", 'sssssssss', (string) $tanggal_transaksi, (string) $no_anggota, (string) $unit, (string) $cif, (string) $code_kel, (string) $sisa, (string) $userid, (string) $cao, (string) $kode_transaksi) or die(mysql_error());             
                   
                    }
                     
                     
                     
                }
 
                 
        }elseif($jenis=='3'){
                    $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                                    values(?,?,'1423000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama ", (string) $margin, (string) $userid);
                    $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                                    values(?,?,'1413000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama", (string) $margin, (string) $userid);                 
               
                    $row_sal=db_satu("select(sum(KREDIT) - sum(DEBIT))as saldo from simpanan WHERE cif =? and TYPE not in ('04','05')", 's', (string) $cif) or die(mysql_error());
                    $sisa=$row_sal['saldo'];
                     
                    $kurang_stor_wo=$tot_wo-$sisa;
                    if($sisa>=$pokok){
                                     
                     
                    $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                                    values(?,?,'2101000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama ", (string) $pokok, (string) $userid);
                    $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)

                                    values(?,?,'1413000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama", (string) $pokok, (string) $userid);       
                    $query=db_jalankan("insert  into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,kode_transaksi)VALUES(?,?,?,?,?,?,'01','0',?,'Realisasi Hapus Buku','',?,?) ", 'sssssssss', (string) $tanggal_transaksi, (string) $no_anggota, (string) $unit, (string) $cif, (string) $code_kel, (string) $pokok, (string) $userid, (string) $cao, (string) $kode_transaksi) or die(mysql_error());             
         
                 
                 
                    }else{
                    $row_sal=db_satu("select(sum(KREDIT) - sum(DEBIT))as saldo from simpanan WHERE cif =? and TYPE not in ('04','05')", 's', (string) $cif) or die(mysql_error());
                    $sisa=$row_sal['saldo'];
                        $kurang=$pokok-$sisa;
                        $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                                    values(?,?,'1512000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama ", (string) $kurang, (string) $userid);
                        $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                                    values(?,?,'2101000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama ", (string) $sisa, (string) $userid);             
                                     
                    $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                                    values(?,?,'1413000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama", (string) $pokok, (string) $userid);
                     
                    $query=db_jalankan("insert  into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,kode_transaksi)VALUES(?,?,?,?,?,?,'01','0',?,'Realisasi Hapus Buku','',?,?) ", 'sssssssss', (string) $tanggal_transaksi, (string) $no_anggota, (string) $unit, (string) $cif, (string) $code_kel, (string) $sisa, (string) $userid, (string) $cao, (string) $kode_transaksi) or die(mysql_error());             
                   
		}
                     
                     
                     
                

		
		}else{
                     
                    $data_rek=db_satu("select gol from loan WHERE cif =?", 's', (string) $cif) or die(mysql_error());
                    $gol=$data_rek['gol'];
                 
                if($gol<=0){
                    echo "<script> alert ('Maaf Anggota belum masuk dalam kategori Macet')</script>";         
 
                        echo "<script language='javascript'>document.location.href='hapus_buku.php'</script>";     
                     
                }else {
                    $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                                    values(?,?,'1423000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama ", (string) $margin, (string) $userid);
                    $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                                    values(?,?,'1413000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama", (string) $margin, (string) $userid);                 
                 
                 
                    $row_sal=db_satu("select(sum(KREDIT) - sum(DEBIT))as saldo from simpanan WHERE cif =? and TYPE not in ('04','05')", 's', (string) $cif) or die(mysql_error());
                    $sisa=$row_sal['saldo'];
                     
                    $kurang_stor_wo=$tot_wo-$sisa;
                    if($sisa>=$pokok){
                                     
                     
                    $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                                    values(?,?,'2101000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama ", (string) $pokok, (string) $userid);
                    $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                                    values(?,?,'1413000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama", (string) $pokok, (string) $userid);   
                    $query=db_jalankan("insert  into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,kode_transaksi)VALUES(?,?,?,?,?,?,'01','0',?,'Realisasi Hapus Buku','',?,?) ", 'sssssssss', (string) $tanggal_transaksi, (string) $no_anggota, (string) $unit, (string) $cif, (string) $code_kel, (string) $pokok, (string) $userid, (string) $cao, (string) $kode_transaksi) or die(mysql_error());             
                 
                 
                    }else{
                        $kurang=$pokok-$sisa;
                        $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                                    values(?,?,'1512000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama ", (string) $kurang, (string) $userid);
                        $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                                    values(?,?,'2101000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama ", (string) $sisa, (string) $userid);   
                        $query=db_jalankan("insert  into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,kode_transaksi)VALUES(?,?,?,?,?,?,'01','0',?,'Realisasi Hapus Buku','',?,?) ", 'sssssssss', (string) $tanggal_transaksi, (string) $no_anggota, (string) $unit, (string) $cif, (string) $code_kel, (string) $sisa, (string) $userid, (string) $cao, (string) $kode_transaksi) or die(mysql_error());
                                                             
                        $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                                    values(?,?,'1413000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama", (string) $pokok, (string) $userid);
                     
                        $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                                    values(?,?,'9141000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama ", (string) $kurang, (string) $userid);
                        $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                                    values(?,?,'9910000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama", (string) $kurang, (string) $userid);                         

		                $query=db_jalankan("update loan set os=os - ?,buss_date =? WHERE cif =?", 'sss', (string) $sisa, (string) $tanggal_transaksi, (string) $cif) or die(mysql_error());                  
		                $wo= db_jalankan("insert into loan_wo select * from loan WHERE cif =?", 's', (string) $cif) or die(mysql_error());
		  
                    }
                     
                 
                }   
                
                
                 

        }
                      
        break;
        case 'MUSYAROKAH':
               if($jenis=='1'){
                        
                if ($ke >'13'){
                    $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                                    values(?,?,'1517000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama ", (string) $pokok, (string) $userid);
                    $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                                    values(?,?,'1472000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama", (string) $pokok, (string) $userid);                 
 
                }else{
                    $row_sal=db_satu("select(sum(KREDIT) - sum(DEBIT))as saldo from simpanan WHERE cif =? and TYPE not in ('04','05')", 's', (string) $cif) or die(mysql_error());
                    $sisa=$row_sal['saldo'];
                     
                    $kurang_stor_wo=$tot_wo-$sisa;
                    if($sisa>=$pokok){
                                     
                     
                    $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                                    values(?,?,'2101000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama ", (string) $pokok, (string) $userid);
                    $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                                    values(?,?,'1472000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama", (string) $pokok, (string) $userid);       
                    $query=db_jalankan("insert  into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,kode_transaksi)VALUES(?,?,?,?,?,?,'01','0',?,'Realisasi Hapus Buku','',?,?) ", 'sssssssss', (string) $tanggal_transaksi, (string) $no_anggota, (string) $unit, (string) $cif, (string) $code_kel, (string) $pokok, (string) $userid, (string) $cao, (string) $kode_transaksi) or die(mysql_error());             
         
                 
                 
                    }else{
                    $row_sal=db_satu("select(sum(KREDIT) - sum(DEBIT))as saldo from simpanan WHERE cif =? and TYPE not in ('04','05')", 's', (string) $cif) or die(mysql_error());
                    $sisa=$row_sal['saldo'];
                        $kurang=$pokok-$sisa;
                        $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                                    values(?,?,'1517000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama ", (string) $kurang, (string) $userid);
                                                        
                    $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                                    values(?,?,'1472000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama", (string) $pokok, (string) $userid);
 $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                                    values(?,?,'2101000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama ", (string) $sisa, (string) $userid);         
                     
                    $query=db_jalankan("insert  into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,kode_transaksi)VALUES(?,?,?,?,?,?,'01','0',?,'Realisasi Hapus Buku','',?,?) ", 'sssssssss', (string) $tanggal_transaksi, (string) $no_anggota, (string) $unit, (string) $cif, (string) $code_kel, (string) $sisa, (string) $userid, (string) $cao, (string) $kode_transaksi) or die(mysql_error());             
                   
                    }
                     
                     
                     
                }
 
                 
        }elseif($jenis=='3'){
                    $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                                    values(?,?,'1423000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama ", (string) $margin, (string) $userid);
                    $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                                    values(?,?,'1413000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama", (string) $margin, (string) $userid);                 
               
                    $row_sal=db_satu("select(sum(KREDIT) - sum(DEBIT))as saldo from simpanan WHERE cif =? and TYPE not in ('04','05')", 's', (string) $cif) or die(mysql_error());
                    $sisa=$row_sal['saldo'];
                     
                    $kurang_stor_wo=$tot_wo-$sisa;
                    if($sisa>=$pokok){
                                     
                     
                    $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                                    values(?,?,'2101000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama ", (string) $pokok, (string) $userid);
                    $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)

                                    values(?,?,'1413000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama", (string) $pokok, (string) $userid);       
                    $query=db_jalankan("insert  into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,kode_transaksi)VALUES(?,?,?,?,?,?,'01','0',?,'Realisasi Hapus Buku','',?,?) ", 'sssssssss', (string) $tanggal_transaksi, (string) $no_anggota, (string) $unit, (string) $cif, (string) $code_kel, (string) $pokok, (string) $userid, (string) $cao, (string) $kode_transaksi) or die(mysql_error());             
         
                 
                 
                    }else{
                    $row_sal=db_satu("select(sum(KREDIT) - sum(DEBIT))as saldo from simpanan WHERE cif =? and TYPE not in ('04','05')", 's', (string) $cif) or die(mysql_error());
                    $sisa=$row_sal['saldo'];
                        $kurang=$pokok-$sisa;
                        $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                                    values(?,?,'1512000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama ", (string) $kurang, (string) $userid);
                        $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                                    values(?,?,'2101000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama ", (string) $sisa, (string) $userid);             
                                     
                    $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                                    values(?,?,'1413000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama", (string) $pokok, (string) $userid);
                     
                    $query=db_jalankan("insert  into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,kode_transaksi)VALUES(?,?,?,?,?,?,'01','0',?,'Realisasi Hapus Buku','',?,?) ", 'sssssssss', (string) $tanggal_transaksi, (string) $no_anggota, (string) $unit, (string) $cif, (string) $code_kel, (string) $sisa, (string) $userid, (string) $cao, (string) $kode_transaksi) or die(mysql_error());             
                   
		}
                     
                     
                     
                

		
		}else{
                     
                    $data_rek=db_satu("select gol from loan WHERE cif =?", 's', (string) $cif) or die(mysql_error());
                    $gol=$data_rek['gol'];
                 
                if($gol<=0){
                    echo "<script> alert ('Maaf Anggota belum masuk dalam kategori Macet')</script>";         
 
                        echo "<script language='javascript'>document.location.href='hapus_buku.php'</script>";     
                     
                }else {
                    $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                                    values(?,?,'1423000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama ", (string) $margin, (string) $userid);
                    $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                                    values(?,?,'1413000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama", (string) $margin, (string) $userid);                 
                 
                 
                    $row_sal=db_satu("select(sum(KREDIT) - sum(DEBIT))as saldo from simpanan WHERE cif =? and TYPE not in ('04','05')", 's', (string) $cif) or die(mysql_error());
                    $sisa=$row_sal['saldo'];
                     
                    $kurang_stor_wo=$tot_wo-$sisa;
                    if($sisa>=$pokok){
                                     
                     
                    $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                                    values(?,?,'2101000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama ", (string) $pokok, (string) $userid);
                    $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                                    values(?,?,'1413000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama", (string) $pokok, (string) $userid);   
                    $query=db_jalankan("insert  into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,kode_transaksi)VALUES(?,?,?,?,?,?,'01','0',?,'Realisasi Hapus Buku','',?,?) ", 'sssssssss', (string) $tanggal_transaksi, (string) $no_anggota, (string) $unit, (string) $cif, (string) $code_kel, (string) $pokok, (string) $userid, (string) $cao, (string) $kode_transaksi) or die(mysql_error());             
                 
                 
                    }else{
                        $kurang=$pokok-$sisa;
                        $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                                    values(?,?,'1512000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama ", (string) $kurang, (string) $userid);
                        $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                                    values(?,?,'2101000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama ", (string) $sisa, (string) $userid);   
                        $query=db_jalankan("insert  into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,kode_transaksi)VALUES(?,?,?,?,?,?,'01','0',?,'Realisasi Hapus Buku','',?,?) ", 'sssssssss', (string) $tanggal_transaksi, (string) $no_anggota, (string) $unit, (string) $cif, (string) $code_kel, (string) $sisa, (string) $userid, (string) $cao, (string) $kode_transaksi) or die(mysql_error());
                                                             
                        $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                                    values(?,?,'1413000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama", (string) $pokok, (string) $userid);
                     
                        $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                                    values(?,?,'9141000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama ", (string) $kurang, (string) $userid);
                        $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                                    values(?,?,'9910000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama", (string) $kurang, (string) $userid);                         

		                $query=db_jalankan("update loan set os=os - ?,buss_date =? WHERE cif =?", 'sss', (string) $sisa, (string) $tanggal_transaksi, (string) $cif) or die(mysql_error());                  
		                $wo= db_jalankan("insert into loan_wo select * from loan WHERE cif =?", 's', (string) $cif) or die(mysql_error());
		  
                    }
                     
                 
                }   
                
                
                 

        }
        
                      
        break;
        case 'COVID':
                 if($jenis=='1'){
                    $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                                    values(?,?,'1423000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama ", (string) $margin, (string) $userid);
                    $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                                    values(?,?,'1413000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama", (string) $margin, (string) $userid);                 
                if ($ke >'13'){
                    $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                                    values(?,?,'1512000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama ", (string) $pokok, (string) $userid);
                    $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                                    values(?,?,'1413000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama", (string) $pokok, (string) $userid);                 
 
                }else{
                    $row_sal=db_satu("select(sum(KREDIT) - sum(DEBIT))as saldo from simpanan WHERE cif =? and TYPE not in ('04','05')", 's', (string) $cif) or die(mysql_error());
                    $sisa=$row_sal['saldo'];
                     
                    $kurang_stor_wo=$tot_wo-$sisa;
                    if($sisa>=$pokok){
                                     
                     
                    $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                                    values(?,?,'2101000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama ", (string) $pokok, (string) $userid);
                    $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                                    values(?,?,'1413000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama", (string) $pokok, (string) $userid);       
                    $query=db_jalankan("insert  into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,kode_transaksi)VALUES(?,?,?,?,?,?,'01','0',?,'Realisasi Hapus Buku','',?,?) ", 'sssssssss', (string) $tanggal_transaksi, (string) $no_anggota, (string) $unit, (string) $cif, (string) $code_kel, (string) $pokok, (string) $userid, (string) $cao, (string) $kode_transaksi) or die(mysql_error());             
         
                 
                 
                    }else{
                    $row_sal=db_satu("select(sum(KREDIT) - sum(DEBIT))as saldo from simpanan WHERE cif =? and TYPE not in ('04','05')", 's', (string) $cif) or die(mysql_error());
                    $sisa=$row_sal['saldo'];
                        $kurang=$pokok-$sisa;
                        $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                                    values(?,?,'1512000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama ", (string) $kurang, (string) $userid);
                        $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                                    values(?,?,'2101000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama ", (string) $sisa, (string) $userid);             
                                     
                    $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                                    values(?,?,'1413000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama", (string) $pokok, (string) $userid);
                     
                    $query=db_jalankan("insert  into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,kode_transaksi)VALUES(?,?,?,?,?,?,'01','0',?,'Realisasi Hapus Buku','',?,?) ", 'sssssssss', (string) $tanggal_transaksi, (string) $no_anggota, (string) $unit, (string) $cif, (string) $code_kel, (string) $sisa, (string) $userid, (string) $cao, (string) $kode_transaksi) or die(mysql_error());             
                   
                    }
                     
                     
                     
                }
 
                 
        }elseif($jenis=='3'){
                    $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                                    values(?,?,'1423000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama ", (string) $margin, (string) $userid);
                    $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                                    values(?,?,'1413000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama", (string) $margin, (string) $userid);                 
               
                    $row_sal=db_satu("select(sum(KREDIT) - sum(DEBIT))as saldo from simpanan WHERE cif =? and TYPE not in ('04','05')", 's', (string) $cif) or die(mysql_error());
                    $sisa=$row_sal['saldo'];
                     
                    $kurang_stor_wo=$tot_wo-$sisa;
                    if($sisa>=$pokok){
                                     
                     
                    $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                                    values(?,?,'2101000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama ", (string) $pokok, (string) $userid);
                    $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)

                                    values(?,?,'1413000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama", (string) $pokok, (string) $userid);       
                    $query=db_jalankan("insert  into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,kode_transaksi)VALUES(?,?,?,?,?,?,'01','0',?,'Realisasi Hapus Buku','',?,?) ", 'sssssssss', (string) $tanggal_transaksi, (string) $no_anggota, (string) $unit, (string) $cif, (string) $code_kel, (string) $pokok, (string) $userid, (string) $cao, (string) $kode_transaksi) or die(mysql_error());             
         
                 
                 
                    }else{
                    $row_sal=db_satu("select(sum(KREDIT) - sum(DEBIT))as saldo from simpanan WHERE cif =? and TYPE not in ('04','05')", 's', (string) $cif) or die(mysql_error());
                    $sisa=$row_sal['saldo'];
                        $kurang=$pokok-$sisa;
                        $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                                    values(?,?,'1512000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama ", (string) $kurang, (string) $userid);
                        $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                                    values(?,?,'2101000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama ", (string) $sisa, (string) $userid);             
                                     
                    $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                                    values(?,?,'1413000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama", (string) $pokok, (string) $userid);
                     
                    $query=db_jalankan("insert  into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,kode_transaksi)VALUES(?,?,?,?,?,?,'01','0',?,'Realisasi Hapus Buku','',?,?) ", 'sssssssss', (string) $tanggal_transaksi, (string) $no_anggota, (string) $unit, (string) $cif, (string) $code_kel, (string) $sisa, (string) $userid, (string) $cao, (string) $kode_transaksi) or die(mysql_error());             
                   
		}
                     
                     
                     
                

		
		}else{
                     
                    $data_rek=db_satu("select gol from loan WHERE cif =?", 's', (string) $cif) or die(mysql_error());
                    $gol=$data_rek['gol'];
                 
                if($gol<=0){
                    echo "<script> alert ('Maaf Anggota belum masuk dalam kategori Macet')</script>";         
 
                        echo "<script language='javascript'>document.location.href='hapus_buku.php'</script>";     
                     
                }else {
                    $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                                    values(?,?,'1423000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama ", (string) $margin, (string) $userid);
                    $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                                    values(?,?,'1413000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama", (string) $margin, (string) $userid);                 
                 
                 
                    $row_sal=db_satu("select(sum(KREDIT) - sum(DEBIT))as saldo from simpanan WHERE cif =? and TYPE not in ('04','05')", 's', (string) $cif) or die(mysql_error());
                    $sisa=$row_sal['saldo'];
                     
                    $kurang_stor_wo=$tot_wo-$sisa;
                    if($sisa>=$pokok){
                                     
                     
                    $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                                    values(?,?,'2101000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama ", (string) $pokok, (string) $userid);
                    $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                                    values(?,?,'1413000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama", (string) $pokok, (string) $userid);   
                    $query=db_jalankan("insert  into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,kode_transaksi)VALUES(?,?,?,?,?,?,'01','0',?,'Realisasi Hapus Buku','',?,?) ", 'sssssssss', (string) $tanggal_transaksi, (string) $no_anggota, (string) $unit, (string) $cif, (string) $code_kel, (string) $pokok, (string) $userid, (string) $cao, (string) $kode_transaksi) or die(mysql_error());             
                 
                 
                    }else{
                        $kurang=$pokok-$sisa;
                        $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                                    values(?,?,'1512000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama ", (string) $kurang, (string) $userid);
                        $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                                    values(?,?,'2101000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama ", (string) $sisa, (string) $userid);   
                        $query=db_jalankan("insert  into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,kode_transaksi)VALUES(?,?,?,?,?,?,'01','0',?,'Realisasi Hapus Buku','',?,?) ", 'sssssssss', (string) $tanggal_transaksi, (string) $no_anggota, (string) $unit, (string) $cif, (string) $code_kel, (string) $sisa, (string) $userid, (string) $cao, (string) $kode_transaksi) or die(mysql_error());
                                                             
                        $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                                    values(?,?,'1413000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama", (string) $pokok, (string) $userid);
                     
                        $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                                    values(?,?,'9141000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama ", (string) $kurang, (string) $userid);
                        $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                                    values(?,?,'9910000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama", (string) $kurang, (string) $userid);                         

		                $query=db_jalankan("update loan set os=os - ?,buss_date =? WHERE cif =?", 'sss', (string) $sisa, (string) $tanggal_transaksi, (string) $cif) or die(mysql_error());                  
		                $wo= db_jalankan("insert into loan_wo select * from loan WHERE cif =?", 's', (string) $cif) or die(mysql_error());
		  
                    }
                     
                 
                }   
                
                
                 

        }           
        break;
        case 'REST':
                if($jenis=='1'){
                    $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                                    values(?,?,'1423000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama ", (string) $margin, (string) $userid);
                    $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                                    values(?,?,'1413000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama", (string) $margin, (string) $userid);                 
                if ($ke >'13'){
                    $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                                    values(?,?,'1512000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama ", (string) $pokok, (string) $userid);
                    $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                                    values(?,?,'1413000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama", (string) $pokok, (string) $userid);                 
 
                }else{
                    $row_sal=db_satu("select(sum(KREDIT) - sum(DEBIT))as saldo from simpanan WHERE cif =? and TYPE not in ('04','05')", 's', (string) $cif) or die(mysql_error());
                    $sisa=$row_sal['saldo'];
                     
                    $kurang_stor_wo=$tot_wo-$sisa;
                    if($sisa>=$pokok){
                                     
                     
                    $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                                    values(?,?,'2101000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama ", (string) $pokok, (string) $userid);
                    $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                                    values(?,?,'1413000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama", (string) $pokok, (string) $userid);       
                    $query=db_jalankan("insert  into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,kode_transaksi)VALUES(?,?,?,?,?,?,'01','0',?,'Realisasi Hapus Buku','',?,?) ", 'sssssssss', (string) $tanggal_transaksi, (string) $no_anggota, (string) $unit, (string) $cif, (string) $code_kel, (string) $pokok, (string) $userid, (string) $cao, (string) $kode_transaksi) or die(mysql_error());             
         
                 
                 
                    }else{
                    $row_sal=db_satu("select(sum(KREDIT) - sum(DEBIT))as saldo from simpanan WHERE cif =? and TYPE not in ('04','05')", 's', (string) $cif) or die(mysql_error());
                    $sisa=$row_sal['saldo'];
                        $kurang=$pokok-$sisa;
                        $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                                    values(?,?,'1512000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama ", (string) $kurang, (string) $userid);
                        $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                                    values(?,?,'2101000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama ", (string) $sisa, (string) $userid);             
                                     
                    $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                                    values(?,?,'1413000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama", (string) $pokok, (string) $userid);
                     
                    $query=db_jalankan("insert  into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,kode_transaksi)VALUES(?,?,?,?,?,?,'01','0',?,'Realisasi Hapus Buku','',?,?) ", 'sssssssss', (string) $tanggal_transaksi, (string) $no_anggota, (string) $unit, (string) $cif, (string) $code_kel, (string) $sisa, (string) $userid, (string) $cao, (string) $kode_transaksi) or die(mysql_error());             
                   
                    }
                     
                     
                     
                }
 
                 
        }elseif($jenis=='3'){
                    $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                                    values(?,?,'1423000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama ", (string) $margin, (string) $userid);
                    $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                                    values(?,?,'1413000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama", (string) $margin, (string) $userid);                 
               
                    $row_sal=db_satu("select(sum(KREDIT) - sum(DEBIT))as saldo from simpanan WHERE cif =? and TYPE not in ('04','05')", 's', (string) $cif) or die(mysql_error());
                    $sisa=$row_sal['saldo'];
                     
                    $kurang_stor_wo=$tot_wo-$sisa;
                    if($sisa>=$pokok){
                                     
                     
                    $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                                    values(?,?,'2101000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama ", (string) $pokok, (string) $userid);
                    $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)

                                    values(?,?,'1413000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama", (string) $pokok, (string) $userid);       
                    $query=db_jalankan("insert  into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,kode_transaksi)VALUES(?,?,?,?,?,?,'01','0',?,'Realisasi Hapus Buku','',?,?) ", 'sssssssss', (string) $tanggal_transaksi, (string) $no_anggota, (string) $unit, (string) $cif, (string) $code_kel, (string) $pokok, (string) $userid, (string) $cao, (string) $kode_transaksi) or die(mysql_error());             
         
                 
                 
                    }else{
                    $row_sal=db_satu("select(sum(KREDIT) - sum(DEBIT))as saldo from simpanan WHERE cif =? and TYPE not in ('04','05')", 's', (string) $cif) or die(mysql_error());
                    $sisa=$row_sal['saldo'];
                        $kurang=$pokok-$sisa;
                        $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                                    values(?,?,'1512000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama ", (string) $kurang, (string) $userid);
                        $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                                    values(?,?,'2101000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama ", (string) $sisa, (string) $userid);             
                                     
                    $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                                    values(?,?,'1413000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama", (string) $pokok, (string) $userid);
                     
                    $query=db_jalankan("insert  into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,kode_transaksi)VALUES(?,?,?,?,?,?,'01','0',?,'Realisasi Hapus Buku','',?,?) ", 'sssssssss', (string) $tanggal_transaksi, (string) $no_anggota, (string) $unit, (string) $cif, (string) $code_kel, (string) $sisa, (string) $userid, (string) $cao, (string) $kode_transaksi) or die(mysql_error());             
                   
		}
                     
                     
                     
                

		
		}else{
                     
                    $data_rek=db_satu("select gol from loan WHERE cif =?", 's', (string) $cif) or die(mysql_error());
                    $gol=$data_rek['gol'];
                 
                if($gol<=0){
                    echo "<script> alert ('Maaf Anggota belum masuk dalam kategori Macet')</script>";         
 
                        echo "<script language='javascript'>document.location.href='hapus_buku.php'</script>";     
                     
                }else {
                    $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                                    values(?,?,'1423000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama ", (string) $margin, (string) $userid);
                    $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                                    values(?,?,'1413000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama", (string) $margin, (string) $userid);                 
                 
                 
                    $row_sal=db_satu("select(sum(KREDIT) - sum(DEBIT))as saldo from simpanan WHERE cif =? and TYPE not in ('04','05')", 's', (string) $cif) or die(mysql_error());
                    $sisa=$row_sal['saldo'];
                     
                    $kurang_stor_wo=$tot_wo-$sisa;
                    if($sisa>=$pokok){
                                     
                     
                    $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                                    values(?,?,'2101000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama ", (string) $pokok, (string) $userid);
                    $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                                    values(?,?,'1413000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama", (string) $pokok, (string) $userid);   
                    $query=db_jalankan("insert  into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,kode_transaksi)VALUES(?,?,?,?,?,?,'01','0',?,'Realisasi Hapus Buku','',?,?) ", 'sssssssss', (string) $tanggal_transaksi, (string) $no_anggota, (string) $unit, (string) $cif, (string) $code_kel, (string) $pokok, (string) $userid, (string) $cao, (string) $kode_transaksi) or die(mysql_error());             
                 
                 
                    }else{
                        $kurang=$pokok-$sisa;
                        $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                                    values(?,?,'1512000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama ", (string) $kurang, (string) $userid);
                        $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                                    values(?,?,'2101000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama ", (string) $sisa, (string) $userid);   
                        $query=db_jalankan("insert  into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,kode_transaksi)VALUES(?,?,?,?,?,?,'01','0',?,'Realisasi Hapus Buku','',?,?) ", 'sssssssss', (string) $tanggal_transaksi, (string) $no_anggota, (string) $unit, (string) $cif, (string) $code_kel, (string) $sisa, (string) $userid, (string) $cao, (string) $kode_transaksi) or die(mysql_error());
                                                             
                        $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                                    values(?,?,'1413000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama", (string) $pokok, (string) $userid);
                     
                        $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                                    values(?,?,'9141000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama ", (string) $kurang, (string) $userid);
                        $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                                    values(?,?,'9910000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama", (string) $kurang, (string) $userid);                         

		                $query=db_jalankan("update loan set os=os - ?,buss_date =? WHERE cif =?", 'sss', (string) $sisa, (string) $tanggal_transaksi, (string) $cif) or die(mysql_error());                  
		                $wo= db_jalankan("insert into loan_wo select * from loan WHERE cif =?", 's', (string) $cif) or die(mysql_error());
		  
                    }
                     
                 
                }   
                
                
                 

        }
                  
        break;
        case 'REST_ANGSURAN':
                if($jenis=='1'){
                    $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                                    values(?,?,'1423000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama ", (string) $margin, (string) $userid);
                    $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                                    values(?,?,'1413000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama", (string) $margin, (string) $userid);                 
                if ($ke >'13'){
                    $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                                    values(?,?,'1512000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama ", (string) $pokok, (string) $userid);
                    $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                                    values(?,?,'1413000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama", (string) $pokok, (string) $userid);                 
 
                }else{
                    $row_sal=db_satu("select(sum(KREDIT) - sum(DEBIT))as saldo from simpanan WHERE cif =? and TYPE not in ('04','05')", 's', (string) $cif) or die(mysql_error());
                    $sisa=$row_sal['saldo'];
                     
                    $kurang_stor_wo=$tot_wo-$sisa;
                    if($sisa>=$pokok){
                                     
                     
                    $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                                    values(?,?,'2101000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama ", (string) $pokok, (string) $userid);
                    $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                                    values(?,?,'1413000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama", (string) $pokok, (string) $userid);       
                    $query=db_jalankan("insert  into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,kode_transaksi)VALUES(?,?,?,?,?,?,'01','0',?,'Realisasi Hapus Buku','',?,?) ", 'sssssssss', (string) $tanggal_transaksi, (string) $no_anggota, (string) $unit, (string) $cif, (string) $code_kel, (string) $pokok, (string) $userid, (string) $cao, (string) $kode_transaksi) or die(mysql_error());             
         
                 
                 
                    }else{
                    $row_sal=db_satu("select(sum(KREDIT) - sum(DEBIT))as saldo from simpanan WHERE cif =? and TYPE not in ('04','05')", 's', (string) $cif) or die(mysql_error());
                    $sisa=$row_sal['saldo'];
                        $kurang=$pokok-$sisa;
                        $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                                    values(?,?,'1512000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama ", (string) $kurang, (string) $userid);
                        $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                                    values(?,?,'2101000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama ", (string) $sisa, (string) $userid);             
                                     
                    $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                                    values(?,?,'1413000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama", (string) $pokok, (string) $userid);
                     
                    $query=db_jalankan("insert  into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,kode_transaksi)VALUES(?,?,?,?,?,?,'01','0',?,'Realisasi Hapus Buku','',?,?) ", 'sssssssss', (string) $tanggal_transaksi, (string) $no_anggota, (string) $unit, (string) $cif, (string) $code_kel, (string) $sisa, (string) $userid, (string) $cao, (string) $kode_transaksi) or die(mysql_error());             
                   
                    }
                     
                     
                     
                }
 
                 
        }elseif($jenis=='3'){
                    $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                                    values(?,?,'1423000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama ", (string) $margin, (string) $userid);
                    $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                                    values(?,?,'1413000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama", (string) $margin, (string) $userid);                 
               
                    $row_sal=db_satu("select(sum(KREDIT) - sum(DEBIT))as saldo from simpanan WHERE cif =? and TYPE not in ('04','05')", 's', (string) $cif) or die(mysql_error());
                    $sisa=$row_sal['saldo'];
                     
                    $kurang_stor_wo=$tot_wo-$sisa;
                    if($sisa>=$pokok){
                                     
                     
                    $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                                    values(?,?,'2101000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama ", (string) $pokok, (string) $userid);
                    $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)

                                    values(?,?,'1413000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama", (string) $pokok, (string) $userid);       
                    $query=db_jalankan("insert  into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,kode_transaksi)VALUES(?,?,?,?,?,?,'01','0',?,'Realisasi Hapus Buku','',?,?) ", 'sssssssss', (string) $tanggal_transaksi, (string) $no_anggota, (string) $unit, (string) $cif, (string) $code_kel, (string) $pokok, (string) $userid, (string) $cao, (string) $kode_transaksi) or die(mysql_error());             
         
                 
                 
                    }else{
                    $row_sal=db_satu("select(sum(KREDIT) - sum(DEBIT))as saldo from simpanan WHERE cif =? and TYPE not in ('04','05')", 's', (string) $cif) or die(mysql_error());
                    $sisa=$row_sal['saldo'];
                        $kurang=$pokok-$sisa;
                        $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                                    values(?,?,'1512000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama ", (string) $kurang, (string) $userid);
                        $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                                    values(?,?,'2101000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama ", (string) $sisa, (string) $userid);             
                                     
                    $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                                    values(?,?,'1413000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama", (string) $pokok, (string) $userid);
                     
                    $query=db_jalankan("insert  into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,kode_transaksi)VALUES(?,?,?,?,?,?,'01','0',?,'Realisasi Hapus Buku','',?,?) ", 'sssssssss', (string) $tanggal_transaksi, (string) $no_anggota, (string) $unit, (string) $cif, (string) $code_kel, (string) $sisa, (string) $userid, (string) $cao, (string) $kode_transaksi) or die(mysql_error());             
                   
		}
                     
                     
                     
                

		
		}else{
                     
                    $data_rek=db_satu("select gol from loan WHERE cif =?", 's', (string) $cif) or die(mysql_error());
                    $gol=$data_rek['gol'];
                 
                if($gol<=0){
                    echo "<script> alert ('Maaf Anggota belum masuk dalam kategori Macet')</script>";         
 
                        echo "<script language='javascript'>document.location.href='hapus_buku.php'</script>";     
                     
                }else {
                    $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                                    values(?,?,'1423000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama ", (string) $margin, (string) $userid);
                    $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                                    values(?,?,'1413000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama", (string) $margin, (string) $userid);                 
                 
                 
                    $row_sal=db_satu("select(sum(KREDIT) - sum(DEBIT))as saldo from simpanan WHERE cif =? and TYPE not in ('04','05')", 's', (string) $cif) or die(mysql_error());
                    $sisa=$row_sal['saldo'];
                     
                    $kurang_stor_wo=$tot_wo-$sisa;
                    if($sisa>=$pokok){
                                     
                     
                    $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                                    values(?,?,'2101000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama ", (string) $pokok, (string) $userid);
                    $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                                    values(?,?,'1413000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama", (string) $pokok, (string) $userid);   
                    $query=db_jalankan("insert  into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,kode_transaksi)VALUES(?,?,?,?,?,?,'01','0',?,'Realisasi Hapus Buku','',?,?) ", 'sssssssss', (string) $tanggal_transaksi, (string) $no_anggota, (string) $unit, (string) $cif, (string) $code_kel, (string) $pokok, (string) $userid, (string) $cao, (string) $kode_transaksi) or die(mysql_error());             
                 
                 
                    }else{
                        $kurang=$pokok-$sisa;
                        $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                                    values(?,?,'1512000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama ", (string) $kurang, (string) $userid);
                        $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                                    values(?,?,'2101000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama ", (string) $sisa, (string) $userid);   
                        $query=db_jalankan("insert  into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,kode_transaksi)VALUES(?,?,?,?,?,?,'01','0',?,'Realisasi Hapus Buku','',?,?) ", 'sssssssss', (string) $tanggal_transaksi, (string) $no_anggota, (string) $unit, (string) $cif, (string) $code_kel, (string) $sisa, (string) $userid, (string) $cao, (string) $kode_transaksi) or die(mysql_error());
                                                             
                        $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                                    values(?,?,'1413000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama", (string) $pokok, (string) $userid);
                     
                        $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                                    values(?,?,'9141000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama ", (string) $kurang, (string) $userid);
                        $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                                    values(?,?,'9910000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama", (string) $kurang, (string) $userid);                         

		                $query=db_jalankan("update loan set os=os - ?,buss_date =? WHERE cif =?", 'sss', (string) $sisa, (string) $tanggal_transaksi, (string) $cif) or die(mysql_error());                  
		                $wo= db_jalankan("insert into loan_wo select * from loan WHERE cif =?", 's', (string) $cif) or die(mysql_error());
		  
                    }
                     
                 
                }   
                
                
                 

        }
                   
        break;
        case 'REST_POKOK_MARGIN':
                if($jenis=='1'){
                    $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                                    values(?,?,'1423000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama ", (string) $margin, (string) $userid);
                    $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                                    values(?,?,'1413000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama", (string) $margin, (string) $userid);                 
                if ($ke >'13'){
                    $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                                    values(?,?,'1512000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama ", (string) $pokok, (string) $userid);
                    $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                                    values(?,?,'1413000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama", (string) $pokok, (string) $userid);                 
 
                }else{
                    $row_sal=db_satu("select(sum(KREDIT) - sum(DEBIT))as saldo from simpanan WHERE cif =? and TYPE not in ('04','05')", 's', (string) $cif) or die(mysql_error());
                    $sisa=$row_sal['saldo'];
                     
                    $kurang_stor_wo=$tot_wo-$sisa;
                    if($sisa>=$pokok){
                                     
                     
                    $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                                    values(?,?,'2101000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama ", (string) $pokok, (string) $userid);
                    $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                                    values(?,?,'1413000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama", (string) $pokok, (string) $userid);       
                    $query=db_jalankan("insert  into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,kode_transaksi)VALUES(?,?,?,?,?,?,'01','0',?,'Realisasi Hapus Buku','',?,?) ", 'sssssssss', (string) $tanggal_transaksi, (string) $no_anggota, (string) $unit, (string) $cif, (string) $code_kel, (string) $pokok, (string) $userid, (string) $cao, (string) $kode_transaksi) or die(mysql_error());             
         
                 
                 
                    }else{
                    $row_sal=db_satu("select(sum(KREDIT) - sum(DEBIT))as saldo from simpanan WHERE cif =? and TYPE not in ('04','05')", 's', (string) $cif) or die(mysql_error());
                    $sisa=$row_sal['saldo'];
                        $kurang=$pokok-$sisa;
                        $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                                    values(?,?,'1512000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama ", (string) $kurang, (string) $userid);
                        $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                                    values(?,?,'2101000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama ", (string) $sisa, (string) $userid);             
                                     
                    $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                                    values(?,?,'1413000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama", (string) $pokok, (string) $userid);
                     
                    $query=db_jalankan("insert  into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,kode_transaksi)VALUES(?,?,?,?,?,?,'01','0',?,'Realisasi Hapus Buku','',?,?) ", 'sssssssss', (string) $tanggal_transaksi, (string) $no_anggota, (string) $unit, (string) $cif, (string) $code_kel, (string) $sisa, (string) $userid, (string) $cao, (string) $kode_transaksi) or die(mysql_error());             
                   
                    }
                     
                     
                     
                }
 
                 
        }elseif($jenis=='3'){
                    $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                                    values(?,?,'1423000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama ", (string) $margin, (string) $userid);
                    $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                                    values(?,?,'1413000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama", (string) $margin, (string) $userid);                 
               
                    $row_sal=db_satu("select(sum(KREDIT) - sum(DEBIT))as saldo from simpanan WHERE cif =? and TYPE not in ('04','05')", 's', (string) $cif) or die(mysql_error());
                    $sisa=$row_sal['saldo'];
                     
                    $kurang_stor_wo=$tot_wo-$sisa;
                    if($sisa>=$pokok){
                                     
                     
                    $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                                    values(?,?,'2101000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama ", (string) $pokok, (string) $userid);
                    $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)

                                    values(?,?,'1413000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama", (string) $pokok, (string) $userid);       
                    $query=db_jalankan("insert  into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,kode_transaksi)VALUES(?,?,?,?,?,?,'01','0',?,'Realisasi Hapus Buku','',?,?) ", 'sssssssss', (string) $tanggal_transaksi, (string) $no_anggota, (string) $unit, (string) $cif, (string) $code_kel, (string) $pokok, (string) $userid, (string) $cao, (string) $kode_transaksi) or die(mysql_error());             
         
                 
                 
                    }else{
                    $row_sal=db_satu("select(sum(KREDIT) - sum(DEBIT))as saldo from simpanan WHERE cif =? and TYPE not in ('04','05')", 's', (string) $cif) or die(mysql_error());
                    $sisa=$row_sal['saldo'];
                        $kurang=$pokok-$sisa;
                        $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                                    values(?,?,'1512000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama ", (string) $kurang, (string) $userid);
                        $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                                    values(?,?,'2101000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama ", (string) $sisa, (string) $userid);             
                                     
                    $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                                    values(?,?,'1413000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama", (string) $pokok, (string) $userid);
                     
                    $query=db_jalankan("insert  into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,kode_transaksi)VALUES(?,?,?,?,?,?,'01','0',?,'Realisasi Hapus Buku','',?,?) ", 'sssssssss', (string) $tanggal_transaksi, (string) $no_anggota, (string) $unit, (string) $cif, (string) $code_kel, (string) $sisa, (string) $userid, (string) $cao, (string) $kode_transaksi) or die(mysql_error());             
                   
		}
                     
                     
                     
                

		
		}else{
                     
                    $data_rek=db_satu("select gol from loan WHERE cif =?", 's', (string) $cif) or die(mysql_error());
                    $gol=$data_rek['gol'];
                 
                if($gol<=0){
                    echo "<script> alert ('Maaf Anggota belum masuk dalam kategori Macet')</script>";         
 
                        echo "<script language='javascript'>document.location.href='hapus_buku.php'</script>";     
                     
                }else {
                    $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                                    values(?,?,'1423000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama ", (string) $margin, (string) $userid);
                    $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                                    values(?,?,'1413000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama", (string) $margin, (string) $userid);                 
                 
                 
                    $row_sal=db_satu("select(sum(KREDIT) - sum(DEBIT))as saldo from simpanan WHERE cif =? and TYPE not in ('04','05')", 's', (string) $cif) or die(mysql_error());
                    $sisa=$row_sal['saldo'];
                     
                    $kurang_stor_wo=$tot_wo-$sisa;
                    if($sisa>=$pokok){
                                     
                     
                    $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                                    values(?,?,'2101000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama ", (string) $pokok, (string) $userid);
                    $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                                    values(?,?,'1413000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama", (string) $pokok, (string) $userid);   
                    $query=db_jalankan("insert  into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,kode_transaksi)VALUES(?,?,?,?,?,?,'01','0',?,'Realisasi Hapus Buku','',?,?) ", 'sssssssss', (string) $tanggal_transaksi, (string) $no_anggota, (string) $unit, (string) $cif, (string) $code_kel, (string) $pokok, (string) $userid, (string) $cao, (string) $kode_transaksi) or die(mysql_error());             
                 
                 
                    }else{
                        $kurang=$pokok-$sisa;
                        $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                                    values(?,?,'1512000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama ", (string) $kurang, (string) $userid);
                        $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                                    values(?,?,'2101000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama ", (string) $sisa, (string) $userid);   
                        $query=db_jalankan("insert  into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,kode_transaksi)VALUES(?,?,?,?,?,?,'01','0',?,'Realisasi Hapus Buku','',?,?) ", 'sssssssss', (string) $tanggal_transaksi, (string) $no_anggota, (string) $unit, (string) $cif, (string) $code_kel, (string) $sisa, (string) $userid, (string) $cao, (string) $kode_transaksi) or die(mysql_error());
                                                             
                        $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                                    values(?,?,'1413000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama", (string) $pokok, (string) $userid);
                     
                        $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                                    values(?,?,'9141000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama ", (string) $kurang, (string) $userid);
                        $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                                    values(?,?,'9910000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama", (string) $kurang, (string) $userid);                         

		                $query=db_jalankan("update loan set os=os - ?,buss_date =? WHERE cif =?", 'sss', (string) $sisa, (string) $tanggal_transaksi, (string) $cif) or die(mysql_error());                  
		                $wo= db_jalankan("insert into loan_wo select * from loan WHERE cif =?", 's', (string) $cif) or die(mysql_error());
		  
                    }
                     
                 
                }   
                
                
                 

        }
        
        break;
        
        default:
               if($jenis=='1'){
                    $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                                    values(?,?,'1423000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama ", (string) $margin, (string) $userid);
                    $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                                    values(?,?,'1413000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama", (string) $margin, (string) $userid);                 
                if ($ke >'13'){
                    $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                                    values(?,?,'1512000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama ", (string) $pokok, (string) $userid);
                    $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                                    values(?,?,'1413000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama", (string) $pokok, (string) $userid);                 
 
                }else{
                    $row_sal=db_satu("select(sum(KREDIT) - sum(DEBIT))as saldo from simpanan WHERE cif =? and TYPE not in ('04','05')", 's', (string) $cif) or die(mysql_error());
                    $sisa=$row_sal['saldo'];
                     
                    $kurang_stor_wo=$tot_wo-$sisa;
                    if($sisa>=$pokok){
                                     
                     
                    $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                                    values(?,?,'2101000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama ", (string) $pokok, (string) $userid);
                    $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                                    values(?,?,'1413000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama", (string) $pokok, (string) $userid);       
                    $query=db_jalankan("insert  into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,kode_transaksi)VALUES(?,?,?,?,?,?,'01','0',?,'Realisasi Hapus Buku','',?,?) ", 'sssssssss', (string) $tanggal_transaksi, (string) $no_anggota, (string) $unit, (string) $cif, (string) $code_kel, (string) $pokok, (string) $userid, (string) $cao, (string) $kode_transaksi) or die(mysql_error());             
         
                 
                 
                    }else{
                    $row_sal=db_satu("select(sum(KREDIT) - sum(DEBIT))as saldo from simpanan WHERE cif =? and TYPE not in ('04','05')", 's', (string) $cif) or die(mysql_error());
                    $sisa=$row_sal['saldo'];
                        $kurang=$pokok-$sisa;
                        $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                                    values(?,?,'1512000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama ", (string) $kurang, (string) $userid);
                        $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                                    values(?,?,'2101000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama ", (string) $sisa, (string) $userid);             
                                     
                    $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                                    values(?,?,'1413000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama", (string) $pokok, (string) $userid);
                     
                    $query=db_jalankan("insert  into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,kode_transaksi)VALUES(?,?,?,?,?,?,'01','0',?,'Realisasi Hapus Buku','',?,?) ", 'sssssssss', (string) $tanggal_transaksi, (string) $no_anggota, (string) $unit, (string) $cif, (string) $code_kel, (string) $sisa, (string) $userid, (string) $cao, (string) $kode_transaksi) or die(mysql_error());             
                   
                    }
                     
                     
                     
                }
 
                 
        }elseif($jenis=='3'){
                    $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                                    values(?,?,'1423000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama ", (string) $margin, (string) $userid);
                    $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                                    values(?,?,'1413000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama", (string) $margin, (string) $userid);                 
               
                    $row_sal=db_satu("select(sum(KREDIT) - sum(DEBIT))as saldo from simpanan WHERE cif =? and TYPE not in ('04','05')", 's', (string) $cif) or die(mysql_error());
                    $sisa=$row_sal['saldo'];
                     
                    $kurang_stor_wo=$tot_wo-$sisa;
                    if($sisa>=$pokok){
                                     
                     
                    $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                                    values(?,?,'2101000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama ", (string) $pokok, (string) $userid);
                    $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)

                                    values(?,?,'1413000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama", (string) $pokok, (string) $userid);       
                    $query=db_jalankan("insert  into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,kode_transaksi)VALUES(?,?,?,?,?,?,'01','0',?,'Realisasi Hapus Buku','',?,?) ", 'sssssssss', (string) $tanggal_transaksi, (string) $no_anggota, (string) $unit, (string) $cif, (string) $code_kel, (string) $pokok, (string) $userid, (string) $cao, (string) $kode_transaksi) or die(mysql_error());             
         
                 
                 
                    }else{
                    $row_sal=db_satu("select(sum(KREDIT) - sum(DEBIT))as saldo from simpanan WHERE cif =? and TYPE not in ('04','05')", 's', (string) $cif) or die(mysql_error());
                    $sisa=$row_sal['saldo'];
                        $kurang=$pokok-$sisa;
                        $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                                    values(?,?,'1512000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama ", (string) $kurang, (string) $userid);
                        $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                                    values(?,?,'2101000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama ", (string) $sisa, (string) $userid);             
                                     
                    $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                                    values(?,?,'1413000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama", (string) $pokok, (string) $userid);
                     
                    $query=db_jalankan("insert  into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,kode_transaksi)VALUES(?,?,?,?,?,?,'01','0',?,'Realisasi Hapus Buku','',?,?) ", 'sssssssss', (string) $tanggal_transaksi, (string) $no_anggota, (string) $unit, (string) $cif, (string) $code_kel, (string) $sisa, (string) $userid, (string) $cao, (string) $kode_transaksi) or die(mysql_error());             
                   
		}
                     
                     
                     
                

		
		}else{
                     
                    $data_rek=db_satu("select gol from loan WHERE cif =?", 's', (string) $cif) or die(mysql_error());
                    $gol=$data_rek['gol'];
                 
                if($gol<=0){
                    echo "<script> alert ('Maaf Anggota belum masuk dalam kategori Macet')</script>";         
 
                        echo "<script language='javascript'>document.location.href='hapus_buku.php'</script>";     
                     
                }else {
                    $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                                    values(?,?,'1423000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama ", (string) $margin, (string) $userid);
                    $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                                    values(?,?,'1413000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama", (string) $margin, (string) $userid);                 
                 
                 
                    $row_sal=db_satu("select(sum(KREDIT) - sum(DEBIT))as saldo from simpanan WHERE cif =? and TYPE not in ('04','05')", 's', (string) $cif) or die(mysql_error());
                    $sisa=$row_sal['saldo'];
                     
                    $kurang_stor_wo=$tot_wo-$sisa;
                    if($sisa>=$pokok){
                                     
                     
                    $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                                    values(?,?,'2101000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama ", (string) $pokok, (string) $userid);
                    $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                                    values(?,?,'1413000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama", (string) $pokok, (string) $userid);   
                    $query=db_jalankan("insert  into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,kode_transaksi)VALUES(?,?,?,?,?,?,'01','0',?,'Realisasi Hapus Buku','',?,?) ", 'sssssssss', (string) $tanggal_transaksi, (string) $no_anggota, (string) $unit, (string) $cif, (string) $code_kel, (string) $pokok, (string) $userid, (string) $cao, (string) $kode_transaksi) or die(mysql_error());             
                 
                 
                    }else{
                        $kurang=$pokok-$sisa;
                        $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                                    values(?,?,'1512000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama ", (string) $kurang, (string) $userid);
                        $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                                    values(?,?,'2101000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama ", (string) $sisa, (string) $userid);   
                        $query=db_jalankan("insert  into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,kode_transaksi)VALUES(?,?,?,?,?,?,'01','0',?,'Realisasi Hapus Buku','',?,?) ", 'sssssssss', (string) $tanggal_transaksi, (string) $no_anggota, (string) $unit, (string) $cif, (string) $code_kel, (string) $sisa, (string) $userid, (string) $cao, (string) $kode_transaksi) or die(mysql_error());
                                                             
                        $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                                    values(?,?,'1413000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama", (string) $pokok, (string) $userid);
                     
                        $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                                    values(?,?,'9141000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama ", (string) $kurang, (string) $userid);
                        $query=db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                                    values(?,?,'9910000',?,'Bukti Umum',?,?,?)", 'ssssss', (string) $unit, (string) $kode_transaksi, (string) $tanggal_transaksi, "Realisasi Hapus Buku An $nama", (string) $kurang, (string) $userid);                         

		                $query=db_jalankan("update loan set os=os - ?,buss_date =? WHERE cif =?", 'sss', (string) $sisa, (string) $tanggal_transaksi, (string) $cif) or die(mysql_error());                  
		                $wo= db_jalankan("insert into loan_wo select * from loan WHERE cif =?", 's', (string) $cif) or die(mysql_error());
		  
                    }
                     
                 
                }   
                
                
                 

        }
        } 

              
                                     
                if($query){
                    //echo "berhasil";
                }else{
                    echo mysql_error();
                }
                 
             
            }else{
                unset($_POST['simpan']);
            }

            //untuk menyelesaikan transaksi
            if(isset($_POST['selesai'])){
             
                $kode_transaksi=$_POST['kode_bukti'];
                $nomor_jurnal=$_POST['nomor_jurnal'];
                $tanggal_selesai=$_POST['tanggal_selesai'];
                $cif=$_POST['cif'];
                $pokok=$_POST['pokok'];
                $row_sal=db_satu("select(sum(KREDIT) - sum(DEBIT))as saldo from simpanan WHERE cif =?", 's', (string) $cif) or die(mysql_error());
                $sisa=$row_sal['saldo'];
                 
                $data_rek=db_satu("select cao,code_kel,no_anggota,unit,pokok,ijaroh from loan WHERE cif =?", 's', (string) $cif) or die(mysql_error());
                $cao=$data_rek['cao'];
                $code_kel=$data_rek['code_kel'];
                $no_anggota=$data_rek['no_anggota'];
                $unit=$data_rek['unit'];
                $angsuran_pokok=$data_rek['pokok'];
                $margin=$data_rek['ijaroh'];
                $bayar=$angsuran_pokok + $margin;
                 
                 
                 
                $query=db_jalankan("update loan set os=0,saldo_margin=0 WHERE cif =?", 's', (string) $cif) or die(mysql_error());
$query=db_jalankan("delete from tunggakan WHERE cif =?", 's', (string) $cif) or die(mysql_error());
 
                $query=db_jalankan("update pinjaman_detail set jumlah_bayar=? WHERE id_pinjam =?", 'ss', (string) $bayar, (string) $no_anggota) or die(mysql_error());
                $query=db_jalankan("insert into jurnal_umum(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values('',?,?,?)", 'sss', (string) $kode_transaksi, (string) $tanggal_selesai, (string) $unit);
                                     
                if($query){
                    ?><script language="javascript">document.location.href="?<?php echo paramEncrypt('page=../transaksi/umum')?>"</script><?php
                }else{
                    echo mysql_error();
                }
                 
             
            }else{
                unset($_POST['selesai']);
            }

            //untuk mendecode url yang di enrypsi
            $var=decode($_SERVER['REQUEST_URI']);
     
            //pecahkan nilai array
            $mode=$var['mode'];
            $id_transaksi=$var['id_transaksi'];
             
            if($mode=='delete'){
                $query=db_jalankan("delete from tabel_transaksi where id_transaksi=?", 's', (string) $id_transaksi);
            }

$pesan_proses = ob_get_clean();

            //untuk menampilkan data
$baris_transaksi = array();
$tot_debet = null;
$tot_kredit = null;
foreach (db_semua("select * from tabel_transaksi where kode_transaksi=? and id_admin=?", 'ss', (string) $kode_transaksi, (string) $userid) as $row) {
    // versi lama membaca angka sebagai teks; perbandingan !== "0" di bawah bergantung pada itu
    $debet = $row['debet'] === null ? null : (string) $row['debet'];
    $kredit = $row['kredit'] === null ? null : (string) $row['kredit'];
    $tot_debet = $tot_debet + $debet;
    $tot_kredit = $tot_kredit + $kredit;
    $n = db_satu("select nama_rek from coa WHERE  kode_rek =?", 's', (string) $row['kode_rekening']) or die(mysql_error());
    $baris_transaksi[] = array(
        'kode_rekening' => $row['kode_rekening'],
        'nama_rek' => $n['nama_rek'],
        'keterangan_transaksi' => $row['keterangan_transaksi'],
        'debet' => $debet,
        'kredit' => $kredit,
        'id_transaksi' => $row['id_transaksi'],
    );
}

$aksi_form = '?' . paramEncrypt('page=../transaksi/umum');
halaman_awal('Hapus Buku');
?>
<div class="card card-ni">
  <div class="card-header">Kriteria Input</div>
  <div class="card-body">
    <form action="<?php echo h($aksi_form); ?>" method="post" name="postform" class="form-ni" autocomplete="off">
      <input type="hidden" name="kode_bukti" value="<?php echo h($kode_bukti_form); ?>">
      <div class="row g-2 mb-2">
        <label class="col-sm-4 col-form-label">Nomor Bukti</label>
        <div class="col-sm-8"><input type="text" class="form-control form-control-sm" disabled value="<?php echo h($kode_bukti_form); ?>"></div>
      </div>
      <div class="row g-2 mb-2">
        <label for="tanggal_transaksi" class="col-sm-4 col-form-label">Tanggal</label>
        <div class="col-sm-8"><input type="date" name="tanggal_transaksi" id="tanggal_transaksi" class="form-control form-control-sm" value="<?php echo h($tanggal_form); ?>"></div>
      </div>
      <div class="row g-2 mb-2">
        <label for="kode" class="col-sm-4 col-form-label">Nomor Rekening</label>
        <div class="col-sm-3 autocomplete">
          <input type="text" name="kode_rekening" id="kode" class="form-control form-control-sm" placeholder="CIF / nama"
                 data-autocomplete="../ajax/cari_hapus_buku.php" data-isi-nilai="cif" data-isi-label="cif,nama"
                 data-isi-target='{"#country":"nama","#pokok":"pokok","#margin":"margin","#ke":"ke","#saldo":"saldo"}'>
        </div>
        <div class="col-sm-5"><input type="text" name="nama" id="country" class="form-control form-control-sm" readonly></div>
      </div>
      <div class="row g-2 mb-2">
        <label for="pokok" class="col-sm-4 col-form-label">Pokok</label>
        <div class="col-sm-8"><input type="text" name="pokok" id="pokok" class="form-control form-control-sm" readonly></div>
      </div>
      <div class="row g-2 mb-2">
        <label for="margin" class="col-sm-4 col-form-label">Margin</label>
        <div class="col-sm-8"><input type="text" name="margin" id="margin" class="form-control form-control-sm" readonly></div>
      </div>
      <div class="row g-2 mb-2">
        <label for="ke" class="col-sm-4 col-form-label">Minggu Ke</label>
        <div class="col-sm-8"><input type="text" name="ke" id="ke" class="form-control form-control-sm"></div>
      </div>
      <div class="row g-2 mb-2">
        <label for="saldo" class="col-sm-4 col-form-label">Simpanan</label>
        <div class="col-sm-8"><input type="text" name="saldo" id="saldo" class="form-control form-control-sm"></div>
      </div>
      <div class="row g-2 mb-3">
        <label for="jenis" class="col-sm-4 col-form-label">Jenis WO</label>
        <div class="col-sm-8">
          <select name="jenis" id="jenis" class="form-select form-select-sm">
            <option value="1">Meninggal Dunia</option>
            <option value="2">NPL</option>
            <option value="3">Hapus Buku Hapus Tagih</option>
          </select>
        </div>
      </div>
      <div class="text-end">
        <button type="submit" name="simpan" value="Simpan" class="btn btn-ni btn-sm px-4">Simpan</button>
      </div>
    </form>
  </div>
</div>
<?php echo $pesan_proses; ?>

<div class="table-responsive">
  <table class="table table-sm table-bordered table-ni align-middle">
    <thead>
      <tr><th>Kode Rekening</th><th>Nama Rek</th><th>Keterangan</th><th class="text-end">Debet</th><th class="text-end">Kredit</th><th class="text-center">Action</th></tr>
    </thead>
    <tbody>
    <?php foreach ($baris_transaksi as $b): ?>
      <tr>
        <td><?php echo h($b['kode_rekening']); ?></td>
        <td><?php echo h($b['nama_rek']); ?></td>
        <td><?php echo h($b['keterangan_transaksi']); ?></td>
        <td class="text-end"><?php if ($b['debet'] !== "0") { echo number_format(compat_num($b['debet']), 2, '.', ','); } ?></td>
        <td class="text-end"><?php if ($b['kredit'] !== "0") { echo number_format(compat_num($b['kredit']), 2, '.', ','); } ?></td>
        <td class="text-center"><a href="?<?php echo h(paramEncrypt('page=../transaksi/umum&mode=delete&id_transaksi=' . $b['id_transaksi'])); ?>" class="btn btn-outline-danger btn-sm" data-konfirmasi="Apakah Anda yakin?">Cancel</a></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
    <tfoot>
      <tr class="fw-bold">
        <td colspan="3" class="text-center">TOTAL</td>
        <td class="text-end"><?php echo number_format(compat_num($tot_debet), 2, '.', ','); ?></td>
        <td class="text-end"><?php echo number_format(compat_num($tot_kredit), 2, '.', ','); ?></td>
        <td class="text-center">
          <?php
          //untuk menghitung balance
          if (!empty($tot_debet) || !empty($tot_kredit)) {
              if ($tot_debet == $tot_kredit) {
                  echo "<span class='text-primary'>Balance</span>";
              } else {
                  echo "<span class='text-danger'>Not Balance : " . abs(compat_num($tot_debet - $tot_kredit)) . "</span>";
              }
          }
          ?>
        </td>
      </tr>
    </tfoot>
  </table>
</div>

<form action="<?php echo h($aksi_form); ?>" method="post" name="form" class="text-end">
  <input type="hidden" name="tanggal_selesai" value="<?php if (empty($_POST['tanggal_transaksi'])) { echo h($tanggal); } else { echo h($_POST['tanggal_transaksi']); } ?>">
  <input type="hidden" name="kode_bukti" value="<?php echo h($kode_transaksi); ?>">
  <input type="hidden" name="nomor_jurnal" value="<?php echo h($nomor_jurnal); ?>">
  <input type="hidden" name="pokok" value="<?php echo h(isset($pokok) ? $pokok : ''); ?>">
  <input type="hidden" name="sisa" value="<?php echo h(isset($sisa) ? $sisa : ''); ?>">
  <input type="hidden" name="cif" value="<?php echo h(isset($cif) ? $cif : ''); ?>">
  <button type="submit" name="selesai" value="Selesai" class="btn btn-ni btn-sm px-4" data-konfirmasi="Apakah Anda Yakin?">Selesai</button>
</form>
<?php
halaman_akhir();
