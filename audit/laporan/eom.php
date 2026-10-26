<?php require_once __DIR__ . '/../../php8_compat.php';
session_start();
$userid=$_SESSION['userid'];
$tgl_system=$_SESSION['stgl_system'];
$tgl_cari=$tgl_system;
include "../encryption/function.php";
     
    //untuk waktu
    $tanggal=date("y-m-d");
     
    //untuk koneksi database
    include "../conn2.php";
    $koneksi=open_connection2();
    $user=mysql_query("select kode_branch from user where userid='$userid'");
             
            while($id_user=mysql_fetch_array($user)){
             
                $unit=$id_user['kode_branch'];
            }
    ?>
    <?php
    if(isset($_POST['hitung_shu'])){
 
$total_pajak=mysql_fetch_array(mysql_query("select sum(saldo_awal) as saldo_awal,sum(saldo_awal) as saldo_akhir  from tabel_master_konsol
where kode_rekening='59999' and tanggal_awal='$tgl_cari' and unit ='0000'"));
$saldo_pajak=$total_pajak['saldo_awal'];
$saldo_akhir_pajak=$total_pajak['saldo_akhir'];
 
 
            $keterangan_transaksi="Reversal Pajak Bulan Lalu";
            $kode_transaksi="BS/BL-1010-$tgl_cari";
mysql_query("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,keterangan_posting,id_admin)
                                    values('1010','$kode_transaksi','1010-2881000','$tgl_cari','Bukti System','$keterangan_transaksi','$saldo_pajak','EOM','$userid')");
        mysql_query("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,keterangan_posting,id_admin)
                                    values('1010','$kode_transaksi','1010-59999','$tgl_cari','Bukti System','$keterangan_transaksi','$saldo_pajak','EOM','$userid')");                            
                 
   $update=mysql_query ("UPDATE tabel_master_konsol set saldo_akhir ='0',mut_debet='0',mut_kredit='0' where rekening_gl in ('1010-59999') and unit ='0000' and tanggal_awal='$tgl_cari'");    
    $update=mysql_query ("UPDATE tabel_master set saldo_akhir ='0',mut_debet='0',mut_kredit='0' where rekening_gl in ('1010-59999') and unit ='1010' ");    
       
         
$total_pendapatan=mysql_fetch_array(mysql_query("select sum(saldo_akhir) as saldo_akhir from tabel_master_konsol
where kode_rekening='40000' and tanggal_awal='$tgl_cari'"));
$pendapatan=$total_pendapatan['saldo_akhir'];
//total Biaya
$total_biaya=mysql_fetch_array(mysql_query("select sum(saldo_akhir) as saldo_akhir  from tabel_master_konsol
where kode_rekening='50000' and tanggal_awal='$tgl_cari'"));
$biaya=$total_biaya['saldo_akhir'];
//total pendapatan non operasional
$total_pendapan_n=mysql_fetch_array(mysql_query("select sum(saldo_akhir) as saldo_akhir  from tabel_master_konsol
where kode_rekening='57000' and tanggal_awal='$tgl_cari'"));
$pendapatan_non=$total_pendapan_n['saldo_akhir'];
 
//total Beban
$total_beban=mysql_fetch_array(mysql_query("select sum(saldo_akhir) as saldo_akhir  from tabel_master_konsol
where kode_rekening='58000' and tanggal_awal='$tgl_cari'"));
$beban=$total_beban['saldo_akhir'];

//$beban = $beban - $saldo_pajak;
 
//Hitung Pendapatan dan beban
$shu_ops=$pendapatan - $biaya;
$shu_non_ops=$pendapatan_non - $beban;
 
$shu_sb_pajak=$shu_ops + $shu_non_ops;

var_dump($shu_sb_pajak);
 
//hitung sebelum pajak pusat
//total Pendapatan
$total_pendapatan_p=mysql_fetch_array(mysql_query("select sum(saldo_akhir) as saldo_akhir from tabel_master_konsol
where kode_rekening='40000' and tanggal_awal='$tgl_cari' and unit ='0000'"));
$pendapatan_p=$total_pendapatan_p['saldo_akhir'];
//total Biaya
$total_biaya_p=mysql_fetch_array(mysql_query("select sum(saldo_akhir) as saldo_akhir  from tabel_master_konsol
where kode_rekening='50000' and tanggal_awal='$tgl_cari' and unit ='0000'"));
$biaya_p=$total_biaya_p['saldo_akhir'];
//total pendapatan non operasional
$total_pendapan_n_p=mysql_fetch_array(mysql_query("select sum(saldo_akhir) as saldo_akhir  from tabel_master_konsol
where kode_rekening='57000' and tanggal_awal='$tgl_cari'and unit ='0000'"));
$pendapatan_non_p=$total_pendapan_n_p['saldo_akhir'];
 
//total Beban
$total_beban_p=mysql_fetch_array(mysql_query("select sum(saldo_akhir) as saldo_akhir  from tabel_master_konsol
where kode_rekening='58000' and tanggal_awal='$tgl_cari'and unit ='0000'"));
$beban_p=$total_beban_p['saldo_akhir'];
//$beban_p=$beban_p - $saldo_pajak;
 
//Hitung Pendapatan dan beban
$shu_ops_p=$pendapatan_p - $biaya_p;
$shu_non_ops_p=$pendapatan_non_p - $beban_p;
 
$shu_sb_pajak_p=$shu_ops_p + $shu_non_ops_p;
 
 
 
//SHU_SEBELUMPAJAK
$sbg_jamu=mysql_fetch_array(mysql_query("select sum(saldo_akhir) as saldo_akhir from tabel_master_konsol where kode_rekening in('56211','56212','56311','56312') and tanggal_awal='$tgl_cari'"));
 
$smb_jamu=$sbg_jamu['saldo_akhir'];
var_dump($smb_jamu);

 
 
 
 
 
//SHU sebelum pajak tahun berjalan fiskal
$shu_sbp=$shu_sb_pajak+$smb_jamu;
var_dump($shu_sbp);
 
//Pendapatan
$totalp=$pendapatan;
//var_dump($totalp);
 
 
//parameter pajak
$param_pajak='4800000000';
//persentase
$persen =($param_pajak/$totalp);
 
 
 
//taksiran pajak mendapat psilitas
$taksiran_dapat=($shu_sbp * $persen);
//var_dump($taksiran_dapat);
 
//Taksiran tidak Mendapatan Pasilitas
$taksiran_tidak=$shu_sbp - $taksiran_dapat;
//var_dump($taksiran_tidak);
//Taksitan Pajaka
//25% X 50% X shu sebelum pajak
$tks_pajak=0.125* $taksiran_dapat;
//25 * b
$tsk_b=(25/100) * $taksiran_tidak;
 
$estimasi_taksiran=$tks_pajak + $tsk_b;
var_dump($estimasi_taksiran);

if($estimasi_taksiran < 1){
$estimasi_taksiran=0;
}else{
$estimasi_taksiran=$estimasi_taksiran;
}

var_dump($shu_sb_pajak_p);
$shu_sb_pajak_p=$shu_sb_pajak_p-$estimasi_taksiran;

 

 
$update=mysql_query ("UPDATE tabel_master_konsol set saldo_akhir ='$shu_sb_pajak_p' where rekening_gl='1010-3902000' and tanggal_awal='$tgl_cari'");
$update=mysql_query ("UPDATE tabel_master_konsol set saldo_akhir ='$estimasi_taksiran',mut_kredit='$estimasi_taksiran',mut_debet=saldo_awal where rekening_gl='1010-2881000' and tanggal_awal='$tgl_cari'");
$update_laba=mysql_query ("UPDATE tabel_master_konsol set saldo_akhir ='$estimasi_taksiran',mut_debet='$estimasi_taksiran',mut_kredit='$saldo_pajak' where rekening_gl='1010-59999' and tanggal_awal='$tgl_cari'");            
 
 
 
 
$update=mysql_query ("UPDATE tabel_master_konsol set saldo_akhir ='0',mut_debet='0',mut_kredit='0' where rekening_gl in ('1010-2800000') and unit ='0000' and tanggal_awal='$tgl_cari'");    
         
$saldo_280=mysql_fetch_array(mysql_query("select sum(saldo_akhir) as saldo_akhir,sum(mut_debet) as mut_debet, sum(mut_kredit) as mut_kredit from tabel_master_konsol where gr_sub='2800000' and unit ='0000' and tanggal_awal='$tgl_cari'"));
$s_280=$saldo_280['saldo_akhir'];
$s_280_mut_debet=$saldo_280['mut_debet'];
$s_280_mut_kredit=$saldo_280['mut_kredit'];
$update=mysql_query ("UPDATE tabel_master_konsol set saldo_akhir ='$s_280',mut_debet='$s_280_mut_debet', mut_kredit='$s_280_mut_kredit' where rekening_gl='1010-2800000' and tanggal_awal='$tgl_cari'");
 
 
 
$update=mysql_query ("UPDATE tabel_master_konsol set saldo_akhir ='0',mut_debet='0',mut_kredit='0' where rekening_gl in ('1010-58000','1010-58400') and unit ='0000' and tanggal_awal='$tgl_cari'");    
         
$saldo_580=mysql_fetch_array(mysql_query("select sum(saldo_akhir) as saldo_akhir,sum(mut_debet) as mut_debet, sum(mut_kredit) as mut_kredit from tabel_master_konsol where gr_head='58000' and unit ='0000' and tanggal_awal='$tgl_cari'"));
$s_580=$saldo_580['saldo_akhir'];
$s_580_mut_debet=$saldo_580['mut_debet'];
$s_580_mut_kredit=$saldo_580['mut_kredit'];
$update=mysql_query ("UPDATE tabel_master_konsol set saldo_akhir ='$s_580',mut_debet='$s_580_mut_debet', mut_kredit='$s_580_mut_kredit' where rekening_gl='1010-58000' and tanggal_awal='$tgl_cari'");
 
$saldo_584=mysql_fetch_array(mysql_query("select sum(saldo_akhir) as saldo_akhir,sum(mut_debet) as mut_debet, sum(mut_kredit) as mut_kredit from tabel_master_konsol where gr_sub='58400' and unit ='0000' and tanggal_awal='$tgl_cari'"));
$s_584=$saldo_584['saldo_akhir'];
$s_584_mut_debet=$saldo_584['mut_debet'];
$s_584_mut_kredit=$saldo_584['mut_kredit'];
$update=mysql_query ("UPDATE tabel_master_konsol set saldo_akhir ='$s_584',mut_debet='$s_584_mut_debet', mut_kredit='$s_584_mut_kredit' where rekening_gl='1010-58400' and tanggal_awal='$tgl_cari'");
 
 
//update tabel_master
 
$update=mysql_query ("UPDATE tabel_master set saldo_akhir ='$shu_sb_pajak_p' where rekening_gl='1010-3902000'");
$update=mysql_query ("UPDATE tabel_master set saldo_akhir ='$estimasi_taksiran',mut_kredit='$estimasi_taksiran',mut_debet=saldo_awal where rekening_gl='1010-2881000' and tanggal_awal='$tgl_cari'");
$update_laba=mysql_query ("UPDATE tabel_master set saldo_akhir ='$estimasi_taksiran',mut_debet='$estimasi_taksiran',mut_kredit='$saldo_pajak' where rekening_gl='1010-59999' and tanggal_awal='$tgl_cari'");            
 
$update=mysql_query ("UPDATE tabel_master set saldo_akhir ='0',mut_debet='0',mut_kredit='0' where rekening_gl in ('1010-2800000') and unit ='1010' and tanggal_awal='$tgl_cari'");    
 
$update=mysql_query ("UPDATE tabel_master set saldo_akhir ='$s_280',mut_debet='$s_280_mut_debet', mut_kredit='$s_280_mut_kredit' where rekening_gl='1010-2800000' and tanggal_awal='$tgl_cari'");
 
 
$update=mysql_query ("UPDATE tabel_master set saldo_akhir ='0',mut_debet='0',mut_kredit='0' where rekening_gl in ('1010-58000','1010-58400') and unit ='1010' and tanggal_awal='$tgl_cari'");    
         
 
$update=mysql_query ("UPDATE tabel_master set saldo_akhir ='$s_580',mut_debet='$s_580_mut_debet', mut_kredit='$s_580_mut_kredit' where rekening_gl='1010-58000' and tanggal_awal='$tgl_cari'");
 
 
$update=mysql_query ("UPDATE tabel_master set saldo_akhir ='$s_584',mut_debet='$s_584_mut_debet', mut_kredit='$s_584_mut_kredit' where rekening_gl='1010-58400' and tanggal_awal='$tgl_cari'");
 
 
//3902000

 
  echo "<script> alert ('Proses Update Selesai!')</script>";
 
        if($update){
            //1. Tahap Penginputan
            $keterangan_transaksi="Perhitungan Pajak $tgl_cari";
            $kode_transaksi="BS/-1010-$tgl_cari";
        mysql_query("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,keterangan_posting,id_admin)
                                    values('1010','$kode_transaksi','1010-2881000','$tgl_cari','Bukti System','$keterangan_transaksi','$estimasi_taksiran','EOM','$userid')");
        mysql_query("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,keterangan_posting,id_admin)
                                    values('1010','$kode_transaksi','1010-59999','$tgl_cari','Bukti System','$keterangan_transaksi','$estimasi_taksiran','EOM','$userid')");                            
                 
echo "<script>alert('Porses posting Jurnal Sukses ');</script>";
   
                }else{
            echo mysql_error();
        }
                             
    }else{
        unset($_POST['hitung_shu']);
    }
    ?>
     
    <style type="text/css">
    <!--
    .style1 {font-style: italic}
    -->
    </style>
 
      <link rel="stylesheet" type="text/css" href="../css/bootstrap.min.css">
 
 <script type="text/javascript" src="../js/jquery.min.js"></script>
 <script type="text/javascript" src="../js/bootstrap.min.js"></script>
 
     
    <div class="post">
      <div class="entry">
            <h2 align="center"><strong>Hitung Rugi-Laba</strong></h2>
            <p align="center">&nbsp;</p>
            <p align="center">
            <p align="center" class="style1"><font color="#666666">
<p align="center"><em><font color="#666666">Tanggal Periode EOM  <?php echo $tgl_system?>.
            Proses ini adalah proses untuk menghasilkan laporan keuangan yaitu menghitung Rugi Laba. </font></p>
            <p align="center"><em><font color="#666666">Proses bisa dilakukan setelah semua data diposting.
            <?php
            $cek=mysql_query("select * from tabel_transaksi where keterangan_posting='' and unit ='1010' and month(tanggal_transaksi)='4' and year(tanggal_transaksi)='2019' ");
            $cek_posting=mysql_num_rows($cek);
            if($cek_posting!==0){
                //echo "masih ada yang belum di posting";
            }else{
                ?>
                </font></em></p>
                <form action="?<?php echo paramEncrypt('page=../laporan/hitung_shu')?>" method="post" name="postform" id ="myform">
                <div align="center">
                <p>&nbsp;</p><p>
                <font color="#666666">
                <input type="submit" onclick="return confirm('Apakah Anda Yakin?')" name="hitung_shu" value="Proses" class="btn btn-primary" />
                 
                </font></p>
                </div>
                </form>
                 
                 
                 
                    </div>
                <?php
                             
            }
 
            ?>
            </div>
            </font>
            <div align="center"><font color="#0066FF">
             
            <?php
             
            if($berhasil){
            echo '<div class="alert alert-success alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><strong>Sukses </strong> SHU berhasil di Hitung </div>';
 
            }
            ?>          
            </font>
            </p>
            </div>
      </div>
    </div>
<?php
 
 
?>
