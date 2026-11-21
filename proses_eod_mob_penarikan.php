<?php require __DIR__ . '/inc/hanya_cli.php'; require_once __DIR__ . '/koneksi_db.php';

$con = db_ni();
if (mysqli_connect_errno()) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
    exit;
}
 
// Turn autocommit of
mysqli_autocommit($con,FALSE);
 
 
include "function.php";
$tanggal="2024-05-27";

 $cek= db_fetch_array(db_query($con,"select param_tgl from param_tgl WHERE nama_param_tgl ='EOD_MOB'"));
$tanggal = $cek['param_tgl']; 
         
$select = db_query ($con,"Select penarikan.cif as cif,loan.angsuran,loan.cao,Cust_Short_name,loan.os as os,
no_anggota as norek ,code_kel,status_app,loan.pokok,run_tenor,loan.unit, loan.bulat as bulat,loan.ijaroh as ijaroh,nominal
from penarikan left join loan on loan.cif = penarikan.cif where nominal > 0  and tgl_tagih ='$tanggal'");
while($data = db_fetch_array($select)){
 
        $norek=$data['norek'];
        $unit=$data ['unit'];
        $angsuran=$data ['angsuran'];
        $nominal=$data ['nominal'];
        $angsuran_pokok=$data ['pokok'];
        $ijaroh=$data ['ijaroh'];
        $code_kel=$data['code_kel'];
        $ke = $data['run_tenor'];
        $cao=$data['cao'];
        $ciff=$data['cif'];

        $os=$data['os'];
        $status_app=$data['status_app'];
        $run_tenor=$data['run_tenor'];
        $pb=$run_tenor + 1;
        $next_tenor=$run_tenor + 2;
 
        $angsuran=$data['angsuran'];
        $bulat=$data['bulat'];
        $nama=mysqli_real_escape_string($con,($data['Cust_Short_name']));
        $kodetr=8;
        $kodetr=acakangkahuruf($kodetr);
        $kode_trans="BU/".$unit.$kodetr; 
        $keterangan="Penarikan simpanan an $nama";
        
        // 2026-11-21 (perintah user, PEKERJAAN_TERTUNDA §103 P2-1):
        //  - CAO dari anggota.CAO seperti penarikan manual (stor.php 02); dulu $ds tidak terdefinisi -> CAO kosong.
        //  - anggota tanpa baris loan (left join): unit/kelompok/norek dulu kosong (jurnal ber-unit kosong); kini dari
        //    anggota. Anggota yang punya loan tetap memakai data loan seperti dulu.
        $agt = db_satu("select UNIT, CODE_KEL, NOREK, CAO, CUST_SHORT_NAME from anggota where CIF = ? limit 1", 's', (string) $ciff);
        $ds = $agt ? $agt['CAO'] : null;
        if ((string) $unit === '' && $agt) {
            $unit = $agt['UNIT'];
            $code_kel = $agt['CODE_KEL'];
            $norek = $agt['NOREK'];
            $nama = mysqli_real_escape_string($con, (string) $agt['CUST_SHORT_NAME']);
            $kode_trans = "BU/" . $unit . $kodetr;
            $keterangan = "Penarikan simpanan an $nama";
        }
        //  - uang sudah diserahkan AO di lapangan, jadi penarikan TETAP dicatat & dijurnal; bila saldo (selain 04/05)
        //    dikurangi penarikan yang masih antre di temp_tarik tidak cukup, hanya peringatan di log cron supaya lead
        //    mengecek sebelum approve (sama dengan cek saldo menu manual, tanpa menolak).
        $sal = db_satu("select sum(KREDIT) - sum(DEBIT) as saldo from simpanan where cif = ? and type not in ('04','05')", 's', (string) $ciff);
        $antre = db_satu("select sum(DEBIT) - sum(KREDIT) as antre from temp_tarik where cif = ?", 's', (string) $ciff);
        $tersedia = compat_num($sal ? $sal['saldo'] : null) - compat_num($antre ? $antre['antre'] : null);
        if ($tersedia < compat_num($nominal)) {
            echo "PERINGATAN saldo tidak cukup: tgl $tanggal unit $unit CIF $ciff tarik $nominal, saldo tersedia $tersedia"
                . " (saldo " . compat_num($sal ? $sal['saldo'] : null) . " - antre " . compat_num($antre ? $antre['antre'] : null) . ")\n";
        }

        $area = db_query ($con,"select code_area FROM branch where kode_branch ='$unit'");
$c_area = db_fetch_array($area);
        
$code_area=$c_area['code_area'];

$input ="Insert into temp_tarik (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,KREDIT,TYPE,DEBIT,USERID,KET,REFF,CAO,blok,tgl_input,kode_transaksi)VALUES('$tanggal','$norek','$unit','$ciff','$code_kel',0,'01','$nominal','$unit','$keterangan','','$ds','$code_area','$tanggal','$kode_trans')";

        $inputtrnsk = db_query($con,$input);
        if($inputtrnsk){
          
            @db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
        values('$unit','$kode_trans','1120000','$tanggal','Bukti Umum','$keterangan','$nominal','$unit')");
        @db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                values('$unit','$kode_trans','2101000','$tanggal','Bukti Umum','$keterangan','$nominal','$unit')");

        }
              
                
            
            else{
            echo "<script>alert('Maaf,.Porses Input Gagal');</script>";
          
            }
         
    
    
    
    }

 
    if (!mysqli_commit($con)) {
        echo "Transaksi Gagal commit Silahkan ulangi lagi";
        exit();
      }
echo "<script>alert('Setoran penarikan sukses di simpan');</script>";

     
    
 
?>
