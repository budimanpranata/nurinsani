<?php require_once __DIR__ . '/koneksi_db.php'; 

$con = db_ni();
if (mysqli_connect_errno()) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
    exit;
}
 
// Turn autocommit off
mysqli_autocommit($con,FALSE);
 
//$tgl_awal ='2024-10-01';
//$tgl_akhir='2024-10-18';
$cek_awal= db_fetch_array(db_query($con,"select param_tgl from param_tgl WHERE nama_param_tgl ='MOB_AWAL'"));
$tgl_awal = $cek_awal['param_tgl']; 
//var_dump($tgl_awal);

$cek_akhir= db_fetch_array(db_query($con,"select param_tgl from param_tgl WHERE nama_param_tgl ='EOD_MOB'"));
$tgl_akhir = $cek_akhir['param_tgl']; 
var_dump($tgl_akhir);
$ambilunit=db_query($con,"select unit from tabel_transaksi where  tanggal_transaksi between '$tgl_awal' and '$tgl_akhir' Group By unit");
while($row_hit_mut=db_fetch_array($ambilunit)){
    $unitt=$row_hit_mut['unit'];	

   


    ///////////////////////// HITUNG MUTASI /////////////////////
    $update_mutasi=db_query($con,"update tabel_master set mut_kredit=0,mut_debet=0  where unit ='$unitt'");
    $query_hitung_mutasi=db_query($con,"select kode_rekening,sum(debet) as debet,sum(kredit) as kredit,unit from tabel_transaksi where unit ='$unitt' and tanggal_transaksi between '$tgl_awal' and '$tgl_akhir' Group By kode_rekening");
    while($row_hit_mut=db_fetch_array($query_hitung_mutasi)){
        $kode_rekening=$row_hit_mut['kode_rekening'];	
        $debet=$row_hit_mut['debet'];
        $kredit=$row_hit_mut['kredit'];
        $unit =$row_hit_mut['unit'];
        $update_mutasi=db_query($con,"update tabel_master set mut_debet=mut_debet+'$debet', mut_kredit=mut_kredit+'$kredit' where kode_rekening='$kode_rekening' and unit ='$unit'");
            
    }
            
    if($query_hitung_mutasi){
        $query_hitung_sisa=db_query($con,"select * from tabel_master where unit ='$unitt'");
        
        while($row_hit_sisa=db_fetch_array($query_hitung_sisa)){
        
            $saldo_awal=$row_hit_sisa['saldo_awal'];
            $normal=$row_hit_sisa['normal'];
            $kode_rekening=$row_hit_sisa['kode_rekening'];
                
            $awal_debet=$row_hit_sisa['awal_debet'];
            $awal_kredit=$row_hit_sisa['awal_kredit'];
            
            $mutasi_debet=$row_hit_sisa['mut_debet'];
            $mutasi_kredit=$row_hit_sisa['mut_kredit'];
                
        
            if($normal=="debet"){
                $hitung_sisa_debet=($saldo_awal+$mutasi_debet)-$mutasi_kredit;
                //if($hitung_sisa_debet<0){
                    //$positif_sisa_kredit=abs($hitung_sisa_debet);
                    //$update_mutasi=mysqli_query($con,"update tabel_master set sisa_debet=0, sisa_kredit='$positif_sisa_kredit' where kode_rekening='$kode_rekening' and unit ='$unitt'");
                //}else{
                    $update_mutasi=db_query($con,"update tabel_master set sisa_debet='$hitung_sisa_debet', sisa_kredit='0' where kode_rekening='$kode_rekening' and unit ='$unitt'");
                	
                            
            }
            
            if($normal=="kredit"){
                $hitung_sisa_kredit=($saldo_awal-$mutasi_debet)+$mutasi_kredit;
                
                //if($hitung_sisa_kredit<0){
                    //$positif_sisa_debet=abs($hitung_sisa_kredit);
                    //$update_mutasi=mysqli_query($con,"update tabel_master set sisa_debet='$positif_sisa_debet', sisa_kredit='0' where kode_rekening='$kode_rekening' and unit ='$unitt'");
                //}else{
                    $update_mutasi=db_query($con,"update tabel_master set sisa_debet=0, sisa_kredit='$hitung_sisa_kredit' where kode_rekening='$kode_rekening' and unit ='$unitt'");
                //}		
            }
        }


    }
    
    
    ////////////////////////// UBAH STATUS POSTING //////////////////////////////
    $selesai=db_query($con,"update tabel_transaksi set  keterangan_posting='Post' where unit ='$unitt' and tanggal_transaksi between '$tgl_awal' and '$tgl_akhir'");
    
    if($selesai){
        echo "posting Jurnal Selesai";

    }else{
        echo mysql_error();
    } 


// Proses Laba Rugi
$update=db_query ($con,"UPDATE tabel_master set saldo_awal ='0',saldo_akhir='0',mut_debet='0',mut_kredit='0',sisa_debet='0',sisa_kredit='0' where LINE_BALANCE ='HEADING' and unit ='$unitt' and kode_rekening not in ('2500000')");
$update=db_query ($con,"UPDATE tabel_master set saldo_akhir='0' where LINE_BALANCE ='DETAIL' and unit ='$unitt'");

//$update=mysql_query ("update tabel_master set saldo_awal = awal_debet - awal_kredit where gr_neraca ='AKTIVA' and unit ='$unit'");
//$update=mysql_query ("update tabel_master set saldo_akhir = sisa_debet - sisa_kredit where gr_neraca ='AKTIVA' and unit ='$unit'");
$update=db_query ($con,"update tabel_master set saldo_akhir = saldo_awal + mut_debet - mut_kredit where gr_neraca ='AKTIVA' and unit ='$unitt'");

//ADMIN
$update=db_query ($con,"update tabel_master set saldo_akhir = saldo_awal + mut_debet - mut_kredit where gr_neraca ='admin' and unit ='$unitt' and normal ='debet'");
$update=db_query ($con,"update tabel_master set saldo_akhir = saldo_awal + mut_kredit - mut_debet where gr_neraca ='admin' and unit ='$unitt'and normal ='kredit'");

//PASIVA
//$update=mysql_query ("update tabel_master set saldo_awal =awal_kredit - awal_debet where gr_neraca ='PASIVA' and unit ='$unit'");
//$update=mysql_query ("update tabel_master set saldo_akhir = sisa_kredit - sisa_debet where gr_neraca ='PASIVA'and unit ='$unit'");
$update=db_query ($con,"update tabel_master set saldo_akhir = saldo_awal + mut_kredit - mut_debet  where gr_neraca ='PASIVA'and unit ='$unitt'");


//Pedndapatan
//$update=mysql_query ("update tabel_master set saldo_awal = awal_kredit - awal_debet where kode_rekening between '40000'and '42399' and unit ='$unit'");
$update=db_query ($con,"update tabel_master set saldo_akhir = sisa_kredit - sisa_debet where kode_rekening between '40000'and '42399' and unit ='$unitt'");

//Biaya
//$update=mysql_query ("update tabel_master set saldo_awal = awal_debet - awal_kredit where kode_rekening between '50000'and '56999' and unit ='$unit'");
$update=db_query ($con,"update tabel_master set saldo_akhir = sisa_debet - sisa_kredit where kode_rekening between '50000'and '56999' and unit ='$unitt'");

//Pendapatan Lainyya
//$update=mysql_query ("update tabel_master set saldo_awal = awal_kredit - awal_debet where kode_rekening between '57000'and '57501' and unit ='$unit'");
$update=db_query ($con,"update tabel_master set saldo_akhir = sisa_kredit - sisa_debet where kode_rekening between '57000'and '57501' and unit ='$unitt'");

//BEBAN 
//$update=mysql_query ("update tabel_master set saldo_awal = awal_debet - awal_kredit where kode_rekening between '58000'and '59999' and unit ='$unit'");
$update=db_query ($con,"update tabel_master set saldo_akhir = sisa_debet - sisa_kredit where kode_rekening between '58000'and '59999' and unit ='$unitt'");


if($update){
    echo "update Saldo awal saldo akhir Sukses";
    }
            
    $select = db_query ($con,"Select gr_head as kode_rekening,sum(mut_debet) as mut_debet, sum(mut_kredit) as mut_kredit, 
    sum(saldo_awal) as saldo_awal, sum(saldo_akhir) as saldo_akhir from tabel_master where unit ='$unitt' and gr_head > 0 group by gr_head ");
    while($data = db_fetch_array($select)){
    
    $saldo_awal=$data['saldo_awal'];
    $saldo_akhir=$data['saldo_akhir'];
    $mut_debet=$data['mut_debet'];
    $mut_kredit=$data['mut_kredit'];
    $kode_rekening=$data['kode_rekening'];
    
    $update=db_query ($con,"UPDATE tabel_master set saldo_awal ='$saldo_awal',saldo_akhir='$saldo_akhir',mut_debet='$mut_debet',mut_kredit='$mut_kredit' where kode_rekening ='$kode_rekening' and unit ='$unitt'");
                }
    
    $select = db_query ($con,"Select gr_sub as kode_rekening,sum(mut_debet) as mut_debet, sum(mut_kredit) as mut_kredit, 
    sum(saldo_awal) as saldo_awal, sum(saldo_akhir) as saldo_akhir from tabel_master where unit ='$unitt' and gr_sub > 0  group by gr_sub ");
    while($data = db_fetch_array($select)){
    
    $saldo_awal=$data['saldo_awal'];
    $saldo_akhir=$data['saldo_akhir'];
    $mut_debet=$data['mut_debet'];
    $mut_kredit=$data['mut_kredit'];
    $kode_rekening=$data['kode_rekening'];
    
    $update=db_query ($con,"UPDATE tabel_master set saldo_awal ='$saldo_awal',saldo_akhir='$saldo_akhir',mut_debet='$mut_debet',mut_kredit='$mut_kredit' where kode_rekening ='$kode_rekening' and unit ='$unitt'");
                }	
            ///////////////////////// HITUNG SHU /////////////////////
            $master=db_query ($con,"select * from tabel_master where unit ='$unitt'");
            while($row=db_fetch_array($master)){
                $posisi=$row['posisi'];
                $sisa_debet=$row['sisa_debet'];
                $sisa_kredit=$row['sisa_kredit'];
                $kode_rekening=$row['kode_rekening'];
                
                if($posisi=='rugi-laba'){
                    //update rugi laba
                    $update=db_query ($con,"update tabel_master set rl_debet='$sisa_debet', rl_kredit='$sisa_kredit' where kode_rekening='$kode_rekening' and unit ='$unitt'");
    //Pedndapatan
    $update=db_query ($con,"update tabel_master set saldo_jalan = mut_kredit - mut_debet where kode_rekening between '40000'and '42399' and unit ='$unitt'");
    
    //Biaya
    $update=db_query ($con,"update tabel_master set saldo_jalan = mut_debet - mut_kredit where kode_rekening between '50000'and '56999' and unit ='$unitt'");
    
    //Pendapatan Lainyya
    $update=db_query ($con,"update tabel_master set saldo_jalan = mut_kredit - mut_debet where kode_rekening between '57000'and '57501' and unit ='$unitt'");
    
    //BEBAN 
    $update=db_query ($con,"update tabel_master set saldo_jalan = mut_debet - mut_kredit where kode_rekening between '58000'and '58999' and unit ='$unitt'");
    
                
                
                
                }else{
                    //update neraca
                    $update=db_query ($con,"update tabel_master set nrc_debet='$sisa_debet', nrc_kredit='$sisa_kredit' where kode_rekening='$kode_rekening'  and unit ='$unitt'");
                }
            }
            
            //jika sudah selesai update
            if($update){
                //$biaya=mysql_fetch_array(mysql_query("select sum(rl_debet) as biaya from tabel_master where normal='debet' and posisi='rugi-laba' and gr_head='50000' and unit ='$unit'"));
                //$pendapatan=mysql_fetch_array(mysql_query("select sum(rl_kredit) as pendapatan from tabel_master where normal='kredit' and posisi='rugi-laba' and gr_head='40000' and unit ='$unit'"));
                //hitung SHU
                //$shu=$pendapatan['pendapatan']-$biaya['biaya'];
            }
            //update rugi laba debet dan neraca kredit dengan SHU
            $total_pendapan=db_fetch_array(db_query ($con,"select saldo_jalan,saldo_awal,saldo_akhir from tabel_master where unit ='$unitt' and kode_rekening='40000'"));
    $total_biaya=db_fetch_array(db_query ($con,"select saldo_jalan,saldo_awal,saldo_akhir from tabel_master where unit ='$unitt' and kode_rekening='50000'"));
    
    
    $shu_ops_awal=$total_pendapan['saldo_awal'] - $total_biaya['saldo_awal'];
    $shu_ops_jalan=$total_pendapan['saldo_jalan'] - $total_biaya['saldo_jalan'];
    $shu_ops_akhir=$total_pendapan['saldo_akhir'] - $total_biaya['saldo_akhir'];
    
    $total_pendapan_n=db_fetch_array(db_query ($con,"select saldo_jalan,saldo_awal,saldo_akhir from tabel_master where unit ='$unitt' and kode_rekening='57000'"));
    $total_beban=db_fetch_array(db_query ($con,"select saldo_jalan,saldo_awal,saldo_akhir from tabel_master where unit ='$unitt' and kode_rekening='58000'"));
    
    //$shu_debet=mysql_fetch_array(mysql_query("select sum(debet) from tabel_transaksi where unit ='$unit' and kode_rekening='3902000' and yeanr(tanggal_transaksi) ='2020'"));
    //$shu_kredit=mysql_fetch_array(mysql_query("select sum(kredit) from tabel_transaksi where unit ='$unit' and kode_rekening='3902000' and yeanr(tanggal_transaksi) ='2020'"));
    
    
    $shu_non_ops_awal=$total_pendapan_n['saldo_awal'] - $total_beban['saldo_awal'];
    $shu_non_ops_jalan=$total_pendapan_n['saldo_jalan'] - $total_beban['saldo_jalan'];
    $shu_ops_non_akhir=$total_pendapan_n['saldo_akhir'] - $total_beban['saldo_akhir'];
    $total_biaya_beba=$total_biaya['saldo_akhir'] + $total_beban['saldo_akhir'];
    $total_pendapantan_dan_n=$total_pendapan_n['saldo_akhir']+$total_pendapan['saldo_akhir'];
    
    $shu_sb_pajak_awal=$shu_ops_awal + $shu_non_ops_awal;
    $shu_sb_pajak_jalan=$shu_ops_jalan + $shu_non_ops_jalan;
    $shu_sb_pajak_akhir=$shu_ops_akhir + $shu_ops_non_akhir;
            
            
    $update_shu=db_query ($con,"update tabel_master set  mut_debet='$total_biaya_beba',mut_kredit='$total_pendapantan_dan_n',saldo_akhir='$shu_sb_pajak_akhir', saldo_jalan='$shu_sb_pajak_jalan' where kode_rekening='3902000' and unit ='$unitt'");
            
    if($update_shu){
    echo "Create SHU Sukses";
    }
            
            if($update_shu){
                $berhasil="Neraca dan Rugi Laba Berhasil dihitung";		
            }else{
                echo mysqli_error();
            }
            ///////////////////HITUNG LABA-RUGI////////////////////
            //hapus table semporial yang lama
            $hapus_tabel_temporial=db_query ($con,"delete from tabel_rugi_laba where unit ='$unitt'");
            if($hapus_tabel_temporial){
                //1. Tahap Penginputan
                db_query ($con,"INSERT INTO tabel_rugi_laba SELECT * FROM tabel_master where gr_neraca='rugi-laba' and unit ='$unitt'");
                        }else{
                echo mysqli_error();
            }
                                
       


    
    
                        

}
db_query($con,"update param_tgl set param_tgl =current_date() where nama_param_tgl not in ('EOM','EOD_MOB','MOB_AWAL')");

 
    if (!mysqli_commit($con)) {
        echo "Transaksi Gagal commit Silahkan ulangi lagi";
        exit();
      }
echo "<script>alert('Proses Selesai.');</script>";

     
    
 
?>
