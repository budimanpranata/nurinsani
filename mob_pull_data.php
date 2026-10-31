<?php require_once __DIR__ . '/koneksi_db.php';
session_start();
ob_start();
//error_reporting(E_ALL);
//ini_set('display_errors',1);

$userid=$_SESSION['userid'];
$tgl_system=$_SESSION['stgl_system'];
//cek apakah user sudah login

if(!isset($_SESSION['userid'])){
   header("location:index.html");
 exit; }
if($_SESSION['level']!="unit"){
   header("location:../index.html");
 exit; }

?>
<?php
// Tampilan Bootstrap 5 (2026-10-31, permintaan user): hanya HTML/CSS yang diganti.
// Kode PHP proses Pull Data (query, mobcol, alert) sengaja dibiarkan sama dengan versi lama.
require_once __DIR__ . '/inc/tampilan.php';
halaman_awal('Pull Data');
?>
<script type="text/javascript" src="jquery.js"></script>
<script>
    // saran kelompok/CIF (versi lama, autosuggesst_rek_mob.php)
    function suggest(inputString){
        if(inputString.length == 0) {
            $('#suggestions').fadeOut();
        } else {
        $('#country').addClass('load');
            $.post("autosuggesst_rek_mob.php", {queryString: ""+inputString+""}, function(data){
                if(data.length >0) {
                    $('#suggestions').fadeIn();
                    $('#suggestionsList').html(data);
                    $('#country').removeClass('load');
                }
            });
        }
    }
    function fill(thisValue) {
        $('#country').val(thisValue);
        setTimeout("$('#suggestions').fadeOut();", 100);
    }
    function fill2(thisValue) {
        $('#kode').val(thisValue);
        setTimeout("$('#suggestions').fadeOut();", 100);
    }
    function fill3(thisValue) {
        $('#code_kel').val(thisValue);
        setTimeout("$('#suggestions').fadeOut();", 100);
    }
    function formatCurrency(num) {
    num = num.toString().replace(/\$|\,/g,'');
    num = num.toString().replace(/[^0-9]/g,'');
    if(isNaN(num))
    num = "0";
    sign = (num == (num = Math.abs(num)));
    num = Math.floor(num*100+0.50000000001);
    cents = num%100;
    num = Math.floor(num/100).toString();
    if(cents<10)
    cents = "0" + cents;
    for (var i = 0; i < Math.floor((num.length-(1+i))/3); i++)
    num = num.substring(0,num.length-(4*i+3))+'.'+
    num.substring(num.length-(4*i+3));
    return (((sign)?'':'-') + 'Rp. ' + num + '');
    }
    function wajibAngka(evt) {
    var charCode = (evt.which) ? evt.which : event.keyCode
    if (charCode > 31 && (charCode < 48 || charCode > 57))
    return false;
    }
</script>
<style>
  /* daftar saran versi lama (HTML <ul><li> dari autosuggesst_rek_mob.php) */
  .saran-lama { position: relative; }
  .saran-lama .suggestionsBox { position: absolute; left: 0; right: 0; top: 100%; z-index: 1050; margin-top: 2px;
    background: var(--bs-body-bg, #fff); border: 1px solid var(--bs-border-color, #dee2e6); border-radius: .375rem;
    box-shadow: 0 .5rem 1rem rgba(0,0,0,.15); max-height: 260px; overflow-y: auto; }
  .saran-lama .suggestionList ul { list-style: none; margin: 0; padding: 0; }
  .saran-lama .suggestionList li { padding: .4rem .75rem; cursor: pointer; font-size: .875rem; border-bottom: 1px solid var(--bs-border-color, #eee); }
  .saran-lama .suggestionList li:hover { background: #e8f2ec; }
</style>

<div class="card card-ni">
  <div class="card-header">Input Data</div>
  <div class="card-body">
    <form method="post" action="<?php echo h($_SERVER['PHP_SELF']); ?>" name="postform" class="form-ni" autocomplete="off">
      <div class="row g-2 mb-2">
        <label for="select" class="col-sm-4 col-form-label">Jenis Pull Data</label>
        <div class="col-sm-8">
          <select name="select" id="select" class="form-select form-select-sm">
            <option>Pilih Salah satu</option>
            <option value="01">Kelompok</option>
            <option value="02">Perorangan</option>
          </select>
        </div>
      </div>
      <div class="row g-2 mb-2">
        <label for="kode" class="col-sm-4 col-form-label">Kode Kelompok/CIF</label>
        <div class="col-sm-3 saran-lama">
          <input type="text" onKeyUp="suggest(this.value);" name="kode_rekening" onBlur="fill2();" id="kode" class="form-control form-control-sm" autocomplete="off">
          <div class="suggestionsBox" id="suggestions" style="display: none;">
            <div class="suggestionList" id="suggestionsList"></div>
          </div>
        </div>
        <div class="col-sm-5"><input type="text" disabled="disabled" name="nama_rekening" onBlur="fill();" id="country" class="form-control form-control-sm"></div>
      </div>
      <div class="row g-2 mb-2">
        <label for="transaksi" class="col-sm-4 col-form-label">Jenis Transaksi</label>
        <div class="col-sm-8">
          <select name="transaksi" id="transaksi" class="form-select form-select-sm">
            <option>Pilih Salah satu</option>
            <option value="lima">5%</option>
            <option value="lebaran">Lebaran</option>
            <option value="pelunasan">Pelunsan</option>
            <option value="pelunasan19">Pelunasan Ke 19</option>
            <option value="pelunasanDO">Pelunasan DO</option>
            <option value="pelunasanRestMargin">Pelunasan Rest Pokok Margin</option>
            <option value="penarikan">Penarikan</option>
            <option value="MeninggalDunia">Meninggal Dunia</option>
          </select>
        </div>
      </div>
      <div class="row g-2 mb-2 nilai">
        <label for="nominal" class="col-sm-4 col-form-label">Nominal</label>
        <div class="col-sm-4"><input name="nominal" type="text" id="nominal" value="" onKeyPress="return wajibAngka(event)" autocomplete="off" onKeyUp="document.getElementById('format').innerHTML = formatCurrency(this.value);" class="form-control form-control-sm text-end" inputmode="numeric"></div>
        <div class="col-sm-4 col-form-label text-muted" id="format"></div>
      </div>
      <div class="row g-2 mb-3">
        <label for="tanggal" class="col-sm-4 col-form-label">Tanggal Tagih</label>
        <div class="col-sm-8"><input name="tgl_tagih" type="date" id="tanggal" value="" class="form-control form-control-sm"></div>
      </div>
      <div class="text-end">
        <button name="search" type="submit" id="submit2" value="Proses" class="btn btn-ni btn-sm px-4">Proses</button>
      </div>
    </form>
  </div>
</div>

<div class="table-responsive mt-3">
<table class="table table-sm table-bordered table-ni align-middle">
  <thead>
  <tr>
    <th>Norek</th>
    <th>Nama</th>
    <th>TGL Transaksi</th>
    <th>Jenis Pull Data</th>
    <th class="text-end">Nominal</th>
    <th class="text-center">Aksi</th>
  </tr>
  </thead>
  <tbody>
  <?php
  include "conn2.php";
  include "function.php";

  //$host_dua   ="185.201.9.210";
  //$user_dua ="adminni";
  $host_dua   ="76.13.20.176";
$user_dua ="admin";
  $password_dua ="";
  $db_dua ="mobcol";
 
  $config_dua =  db_mobcol();
  if(mysqli_connect_errno())
  {
  echo'Gagal :'.mysqli_connect_error();
  }
  
  $koneksi=open_connection2();
  $cif_kel=$_POST['kode_rekening'];
  $pilih=$_POST['select'];
  $tanggal=date("Y-m-d");
  $transaksi=$_POST['transaksi'];
  $tgl_tagih= $_POST['tgl_tagih'];
  $nominal =$_POST['nominal'];
  

 
            
  if (isset($_POST ['search']))
{
if (empty($cif_kel)||empty($pilih)||empty($transaksi)){
    echo "<script>alert('Maaf,.isi Nomor kode kelompok dan Jenis Transaksi yang benar ya!');</script>";
         echo "<script language='javascript'>document.location.href='mob_pull_data.php'</script>";}
    
    else{
        
    switch($pilih)
    {
     case "01":
        $cek = mysql_query("SELECT unit, calon_loan.code_kel as code_kel, calon_loan.cif as cif, calon_loan.cao as cao,saldo_margin,Plafond,
        no_anggota as norek,pokok, ijaroh, angsuran, hari, bulat, os, Cust_Short_name, nama_kel
        FROM calon_loan left join kelompok on calon_loan.code_kel = kelompok.code_kel WHERE  calon_loan.code_kel ='$cif_kel' ");

     if($transaksi == 'lima'){  
                
        if(mysql_num_rows($cek)>=1){                
                
            while ($row=mysql_fetch_array($cek)){
                $unit=mysql_real_escape_string($row['unit']);
                $tgl_tagih=$tgl_tagih;
                $code_kel=mysql_real_escape_string($row['code_kel']);
                $cif=mysql_real_escape_string($row['cif']);
                $cao=mysql_real_escape_string($row['cao']);
                $norek=mysql_real_escape_string($row['norek']);
                $angsuran_pokok=mysql_real_escape_string($row['pokok']);
                $angsuran_margin=mysql_real_escape_string($row['ijaroh']);
                $angsuran=mysql_real_escape_string($row['angsuran']);
                $bayar=mysql_real_escape_string($row['Plafond'] * (5/100));
                $status_realisasi='';
                $pb='0';
                $ke = '0';
                $tunggakan='0';
                $hari=mysql_real_escape_string($row['hari']);
                $twm='0';
                $bulat=$bayar;
                $simpanan_wajib='0';
                $simpanan_pokok ='0';
                $os=mysql_real_escape_string($row['os']);
                $nama=mysql_real_escape_string($row['Cust_Short_name']);
                $nama_kel=mysql_real_escape_string($row['nama_kel']);
                $saldo_margin=mysql_real_escape_string($row['saldo_margin']);
                $plafond=mysql_real_escape_string($row['Plafond']);

             
            
            
            
                $Data_lima[] = "('$unit', '$tgl_tagih', '$code_kel','$cif','$cao','$norek', '$angsuran_pokok','$angsuran_margin','$angsuran','$bayar','$status_realisasi','$pb','$ke','$tunggakan','$hari','$twm','$bulat','$simpanan_wajib','$simpanan_pokok','$os','$nama','$nama_kel','$saldo_margin','$plafond')";
                $Data_pull[] = "('$unit', '$tgl_tagih', '$code_kel','$cif','$cao','$norek', '$angsuran_pokok','$angsuran_margin','$angsuran','$bayar','$status_realisasi','$pb','$ke','$tunggakan','$hari','$twm','$bulat','$simpanan_wajib','$simpanan_pokok','$os','$nama','$nama_kel','$saldo_margin','$plafond','lima')";
            
            }

            $sql = "insert INTO tagihan_lima_persen(unit, tgl_tagih,code_kel,cif,cao,norek,angsuran_pokok,angsuran_margin,angsuran,bayar,status_realisasi,pb,ke,tunggakan,hari,twm,bulat,simpanan_wajib,simpanan_pokok,os,nama,nama_kel,saldo_margin,plafond ) values ";
            $sql .= implode(',', $Data_lima);
            db_query($config_dua, $sql) or die(mysqli_error($config_dua));

            $sql = "insert INTO pull_data(unit, tgl_tagih,code_kel,cif,cao,norek,angsuran_pokok,angsuran_margin,angsuran,bayar,status_realisasi,pb,ke,tunggakan,hari,twm,bulat,simpanan_wajib,simpanan_pokok,os,nama,nama_kel,saldo_margin,plafond,jenis_pull ) values ";
            $sql .= implode(',', $Data_pull);
            db_query($config_dua, $sql) or die(mysqli_error($config_dua));
          


            echo "<script>alert('Pull Data 5% sukses');</script>";
            $select = db_query($config_dua,"SELECT * from pull_data where unit =$userid and tgl_tagih > '$tgl_system'");
            }else{
                echo "<script>alert('Gagal Pull Data !!!');</script>";
            }
        
        }elseif($transaksi =='lebaran'){

            if(mysql_num_rows($cek)>=1){                
                
                while ($row=mysql_fetch_array($cek)){
                    $unit=mysql_real_escape_string($row['unit']);
                    $tgl_tagih=$tgl_tagih;
                    $code_kel=mysql_real_escape_string($row['code_kel']);
                    $cif=mysql_real_escape_string($row['cif']);
                    $cao=mysql_real_escape_string($row['cao']);
                    $norek=mysql_real_escape_string($row['norek']);
                    $angsuran_pokok=mysql_real_escape_string($row['pokok']);
                    $angsuran_margin=mysql_real_escape_string($row['ijaroh']);
                    $angsuran=mysql_real_escape_string($row['angsuran']);
                    $bayar=mysql_real_escape_string($row['bulat']);
                    $status_realisasi='';
                    $pb='0';
                    $ke = '0';
                    $tunggakan='0';
                    $hari=mysql_real_escape_string($row['hari']);
                    $twm='0';
                    $bulat=mysql_real_escape_string($row['bulat']);
                    $simpanan_wajib='0';
                    $simpanan_pokok ='0';
                    $os=mysql_real_escape_string($row['os']);
                    $nama=mysql_real_escape_string($row['Cust_Short_name']);
                    $nama_kel=mysql_real_escape_string($row['nama_kel']);
                    $saldo_margin=mysql_real_escape_string($row['saldo_margin']);
                    $plafond=mysql_real_escape_string($row['Plafond']);
    
                 
                
                
                
                    $Data_lima[] = "('$unit', '$tgl_tagih', '$code_kel','$cif','$cao','$norek', '$angsuran_pokok','$angsuran_margin','$angsuran','$bayar','$status_realisasi','$pb','$ke','$tunggakan','$hari','$twm','$bulat','$simpanan_wajib','$simpanan_pokok','$os','$nama','$nama_kel','$saldo_margin','$plafond')";
                    $Data_pull[] = "('$unit', '$tgl_tagih', '$code_kel','$cif','$cao','$norek', '$angsuran_pokok','$angsuran_margin','$angsuran','$bayar','$status_realisasi','$pb','$ke','$tunggakan','$hari','$twm','$bulat','$simpanan_wajib','$simpanan_pokok','$os','$nama','$nama_kel','$saldo_margin','$plafond','lebaran')";
                
                }
    
                $sql = "insert INTO tagihan_lebaran(unit, tgl_tagih,code_kel,cif,cao,norek,angsuran_pokok,angsuran_margin,angsuran,bayar,status_realisasi,pb,ke,tunggakan,hari,twm,bulat,simpanan_wajib,simpanan_pokok,os,nama,nama_kel,saldo_margin,plafond ) values ";
                $sql .= implode(',', $Data_lima);
                db_query($config_dua, $sql) or die(mysqli_error($config_dua));
    
                $sql = "insert INTO pull_data(unit, tgl_tagih,code_kel,cif,cao,norek,angsuran_pokok,angsuran_margin,angsuran,bayar,status_realisasi,pb,ke,tunggakan,hari,twm,bulat,simpanan_wajib,simpanan_pokok,os,nama,nama_kel,saldo_margin,plafond,jenis_pull ) values ";
                $sql .= implode(',', $Data_pull);
                db_query($config_dua, $sql) or die(mysqli_error($config_dua));
              
    
    
                echo "<script>alert('Pull Data lebaran sukses');</script>";
                $select = db_query($config_dua,"SELECT * from pull_data where unit =$userid and tgl_tagih > '$tgl_system'");
                }else{
                    echo "<script>alert('Gagal Pull Data !!!');</script>";
                }
            
       
        }elseif($transaksi =='pelunasan'){
        $cek = mysql_query("SELECT unit, loan.code_kel as code_kel, loan.cif as cif, loan.cao as cao,saldo_margin,Plafond,
        no_anggota as norek,pokok, ijaroh, angsuran, hari, bulat, os, Cust_Short_name, nama_kel
        FROM loan left join kelompok on loan.code_kel = kelompok.code_kel WHERE  loan.code_kel ='$cif_kel' AND os> 0 ");


        if(mysql_num_rows($cek)>=1){                
            
            while ($row=mysql_fetch_array($cek)){
                $unit=mysql_real_escape_string($row['unit']);
                $tgl_tagih=$tgl_tagih;
                $code_kel=mysql_real_escape_string($row['code_kel']);
                $cif=mysql_real_escape_string($row['cif']);
                $row_sal= mysql_fetch_array(mysql_query("select(sum(KREDIT) - sum(DEBIT))as saldo from simpanan WHERE cif ='$cif' and type not in ('04','05')")) or die(mysql_error());
                $saldo=$row_sal['saldo'];
                if($saldo==null){
                    $saldo=0;
                }else{
                    $saldo = $saldo;
                }
                $cao=mysql_real_escape_string($row['cao']);
                $norek=mysql_real_escape_string($row['norek']);
                $angsuran_pokok=mysql_real_escape_string($row['pokok']);
                $angsuran_margin=mysql_real_escape_string($row['ijaroh']);
                $angsuran=mysql_real_escape_string($row['angsuran']);
                $os=mysql_real_escape_string($row['os']);
                $bayar=mysql_real_escape_string($row['bulat']);

                $sisa = $saldo - $os;
                if  ($saldo < $os ){
                    $bayar = 0;                    
                }else{
                    $bayar =$sisa;
                }

                $status_realisasi='';
                $pb='0';
                $ke = '0';
                $tunggakan='0';
                $hari=mysql_real_escape_string($row['hari']);
                $twm=$saldo;
                $bulat=mysql_real_escape_string($row['bulat']);
                $simpanan_wajib='0';
                $simpanan_pokok ='0';                
                $nama=mysql_real_escape_string($row['Cust_Short_name']);
                $nama_kel=mysql_real_escape_string($row['nama_kel']);
                $saldo_margin=mysql_real_escape_string($row['saldo_margin']);
                $plafond=mysql_real_escape_string($row['Plafond']);

             
            
            
            
                $Data_lima[] = "('$unit', '$tgl_tagih', '$code_kel','$cif','$cao','$norek', '$angsuran_pokok','$angsuran_margin','$angsuran','$bayar','$status_realisasi','$pb','$ke','$tunggakan','$hari','$twm','$bulat','$simpanan_wajib','$simpanan_pokok','$os','$nama','$nama_kel','$saldo_margin','$plafond')";
                $Data_pull[] = "('$unit', '$tgl_tagih', '$code_kel','$cif','$cao','$norek', '$angsuran_pokok','$angsuran_margin','$angsuran','$bayar','$status_realisasi','$pb','$ke','$tunggakan','$hari','$twm','$bulat','$simpanan_wajib','$simpanan_pokok','$os','$nama','$nama_kel','$saldo_margin','$plafond','pelunasan')";
            
            }
            

            $sql = "insert INTO tagihan_pelunasan(unit, tgl_tagih,code_kel,cif,cao,norek,angsuran_pokok,angsuran_margin,angsuran,bayar,status_realisasi,pb,ke,tunggakan,hari,twm,bulat,simpanan_wajib,simpanan_pokok,os,nama,nama_kel,saldo_margin,plafond ) values ";
            $sql .= implode(',', $Data_lima);
            db_query($config_dua, $sql) or die(mysqli_error($config_dua));

            $sql = "insert INTO pull_data(unit, tgl_tagih,code_kel,cif,cao,norek,angsuran_pokok,angsuran_margin,angsuran,bayar,status_realisasi,pb,ke,tunggakan,hari,twm,bulat,simpanan_wajib,simpanan_pokok,os,nama,nama_kel,saldo_margin,plafond,jenis_pull ) values ";
            $sql .= implode(',', $Data_pull);
            db_query($config_dua, $sql) or die(mysqli_error($config_dua));
          


            echo "<script>alert('Pull Data Pelunasan sukses');</script>";
            $select = db_query($config_dua,"SELECT * from pull_data where unit =$userid and tgl_tagih > '$tgl_system'" );
            }else{
                echo "<script>alert('Gagal Pull Data !!!');</script>";
            }

}elseif($transaksi =='pelunasanRestMargin'){
        $cek = mysql_query("SELECT unit, loan.code_kel as code_kel, loan.cif as cif, loan.cao as cao,saldo_margin,Plafond,
        no_anggota as norek,pokok, ijaroh, angsuran, hari, bulat, os, Cust_Short_name, nama_kel
        FROM loan left join kelompok on loan.code_kel = kelompok.code_kel WHERE  loan.code_kel ='$cif_kel' AND os> 0 ");


        if(mysql_num_rows($cek)>=1){                
            
            while ($row=mysql_fetch_array($cek)){
                $unit=mysql_real_escape_string($row['unit']);
                $tgl_tagih=$tgl_tagih;
                $code_kel=mysql_real_escape_string($row['code_kel']);
                $cif=mysql_real_escape_string($row['cif']);
                $row_sal= mysql_fetch_array(mysql_query("select(sum(KREDIT) - sum(DEBIT))as saldo from simpanan WHERE cif ='$cif' and type not in ('04','05')")) or die(mysql_error());
                $saldo=$row_sal['saldo'];
                if($saldo==null){
                    $saldo=0;
                }else{
                    $saldo = $saldo;
                }
                $cao=mysql_real_escape_string($row['cao']);
                $norek=mysql_real_escape_string($row['norek']);
                $angsuran_pokok=mysql_real_escape_string($row['pokok']);
                $angsuran_margin=mysql_real_escape_string($row['ijaroh']);
                $angsuran=mysql_real_escape_string($row['angsuran']);
                $os=mysql_real_escape_string($row['os']);
                $bayar=mysql_real_escape_string($row['bulat']);

                $sisa = $saldo - $os;
                if  ($saldo < $os ){
                    $bayar = 0;                    
                }else{
                    $bayar =$sisa;
                }

                $status_realisasi='';
                $pb='0';
                $ke = '0';
                $tunggakan='0';
                $hari=mysql_real_escape_string($row['hari']);
                $twm=$saldo;
                $bulat=mysql_real_escape_string($row['bulat']);
                $simpanan_wajib='0';
                $simpanan_pokok ='0';                
                $nama=mysql_real_escape_string($row['Cust_Short_name']);
                $nama_kel=mysql_real_escape_string($row['nama_kel']);
                $saldo_margin=mysql_real_escape_string($row['saldo_margin']);
                $plafond=mysql_real_escape_string($row['Plafond']);

             
            
            
            
                $Data_lima[] = "('$unit', '$tgl_tagih', '$code_kel','$cif','$cao','$norek', '$angsuran_pokok','$angsuran_margin','$angsuran','$bayar','$status_realisasi','$pb','$ke','$tunggakan','$hari','$twm','$bulat','$simpanan_wajib','$simpanan_pokok','$os','$nama','$nama_kel','$saldo_margin','$plafond')";
                $Data_pull[] = "('$unit', '$tgl_tagih', '$code_kel','$cif','$cao','$norek', '$angsuran_pokok','$angsuran_margin','$angsuran','$bayar','$status_realisasi','$pb','$ke','$tunggakan','$hari','$twm','$bulat','$simpanan_wajib','$simpanan_pokok','$os','$nama','$nama_kel','$saldo_margin','$plafond','pelunasanRestMargin')";
            
            }
            

            $sql = "insert INTO tagihan_pelunasan(unit, tgl_tagih,code_kel,cif,cao,norek,angsuran_pokok,angsuran_margin,angsuran,bayar,status_realisasi,pb,ke,tunggakan,hari,twm,bulat,simpanan_wajib,simpanan_pokok,os,nama,nama_kel,saldo_margin,plafond ) values ";
            $sql .= implode(',', $Data_lima);
            db_query($config_dua, $sql) or die(mysqli_error($config_dua));

            $sql = "insert INTO pull_data(unit, tgl_tagih,code_kel,cif,cao,norek,angsuran_pokok,angsuran_margin,angsuran,bayar,status_realisasi,pb,ke,tunggakan,hari,twm,bulat,simpanan_wajib,simpanan_pokok,os,nama,nama_kel,saldo_margin,plafond,jenis_pull ) values ";
            $sql .= implode(',', $Data_pull);
            db_query($config_dua, $sql) or die(mysqli_error($config_dua));
          


            echo "<script>alert('Pull Data Pelunasan Rest Pokok Margin sukses');</script>";
            $select = db_query($config_dua,"SELECT * from pull_data where unit =$userid and tgl_tagih > '$tgl_system'" );
            }else{
                echo "<script>alert('Gagal Pull Data !!!');</script>";
            }

        
   }elseif($transaksi =='pelunasan19'){
        $cek = mysql_query("SELECT unit, loan.code_kel as code_kel, loan.cif as cif, loan.cao as cao,saldo_margin,Plafond,
        no_anggota as norek,pokok, ijaroh, angsuran, hari, bulat, os, Cust_Short_name, nama_kel
        FROM loan left join kelompok on loan.code_kel = kelompok.code_kel WHERE  loan.code_kel ='$cif_kel' AND os> 0 ");


        if(mysql_num_rows($cek)>=1){                
            
            while ($row=mysql_fetch_array($cek)){
                $unit=mysql_real_escape_string($row['unit']);
                $tgl_tagih=$tgl_tagih;
                $code_kel=mysql_real_escape_string($row['code_kel']);
                $cif=mysql_real_escape_string($row['cif']);
                $row_sal= mysql_fetch_array(mysql_query("select(sum(KREDIT) - sum(DEBIT))as saldo from simpanan WHERE cif ='$cif' and type not in ('04','05')")) or die(mysql_error());
                $saldo=$row_sal['saldo'];
                if($saldo==null){
                    $saldo=0;
                }else{
                    $saldo = $saldo;
                }
                $cao=mysql_real_escape_string($row['cao']);
                $norek=mysql_real_escape_string($row['norek']);
                $angsuran_pokok=mysql_real_escape_string($row['pokok']);
                $angsuran_margin=mysql_real_escape_string($row['ijaroh']);
                $angsuran=mysql_real_escape_string($row['angsuran']);
                $os=mysql_real_escape_string($row['os']);
                $bayar=mysql_real_escape_string($row['bulat']);

                $sisa = $saldo - $os;
                if  ($saldo < $os ){
                    $bayar = 0;                    
                }else{
                    $bayar =$sisa;
                }

                $status_realisasi='';
                $pb='0';
                $ke = '0';
                $tunggakan='0';
                $hari=mysql_real_escape_string($row['hari']);
                $twm=$saldo;
                $bulat=mysql_real_escape_string($row['bulat']);
                $simpanan_wajib='0';
                $simpanan_pokok ='0';                
                $nama=mysql_real_escape_string($row['Cust_Short_name']);
                $nama_kel=mysql_real_escape_string($row['nama_kel']);
                $saldo_margin=mysql_real_escape_string($row['saldo_margin']);
                $plafond=mysql_real_escape_string($row['Plafond']);

             
            
            
            
                $Data_lima[] = "('$unit', '$tgl_tagih', '$code_kel','$cif','$cao','$norek', '$angsuran_pokok','$angsuran_margin','$angsuran','$bayar','$status_realisasi','$pb','$ke','$tunggakan','$hari','$twm','$bulat','$simpanan_wajib','$simpanan_pokok','$os','$nama','$nama_kel','$saldo_margin','$plafond')";
                $Data_pull[] = "('$unit', '$tgl_tagih', '$code_kel','$cif','$cao','$norek', '$angsuran_pokok','$angsuran_margin','$angsuran','$bayar','$status_realisasi','$pb','$ke','$tunggakan','$hari','$twm','$bulat','$simpanan_wajib','$simpanan_pokok','$os','$nama','$nama_kel','$saldo_margin','$plafond','pelunasan19')";
            
            }
            

            $sql = "insert INTO tagihan_pelunasan(unit, tgl_tagih,code_kel,cif,cao,norek,angsuran_pokok,angsuran_margin,angsuran,bayar,status_realisasi,pb,ke,tunggakan,hari,twm,bulat,simpanan_wajib,simpanan_pokok,os,nama,nama_kel,saldo_margin,plafond ) values ";
            $sql .= implode(',', $Data_lima);
            db_query($config_dua, $sql) or die(mysqli_error($config_dua));

            $sql = "insert INTO pull_data(unit, tgl_tagih,code_kel,cif,cao,norek,angsuran_pokok,angsuran_margin,angsuran,bayar,status_realisasi,pb,ke,tunggakan,hari,twm,bulat,simpanan_wajib,simpanan_pokok,os,nama,nama_kel,saldo_margin,plafond,jenis_pull ) values ";
            $sql .= implode(',', $Data_pull);
            db_query($config_dua, $sql) or die(mysqli_error($config_dua));
          


            echo "<script>alert('Pull Data Pelunasan sukses');</script>";
            $select = db_query($config_dua,"SELECT * from pull_data where unit =$userid and tgl_tagih > '$tgl_system'" );
            }else{
                echo "<script>alert('Gagal Pull Data !!!');</script>";
            }

    }elseif($transaksi =='pelunasanDO'){
        $cek = mysql_query("SELECT unit, loan.code_kel as code_kel, loan.cif as cif, loan.cao as cao,saldo_margin,Plafond,
        no_anggota as norek,pokok, ijaroh, angsuran, hari, bulat, os, Cust_Short_name, nama_kel
        FROM loan left join kelompok on loan.code_kel = kelompok.code_kel WHERE  loan.code_kel ='$cif_kel' AND os> 0 ");


        if(mysql_num_rows($cek)>=1){                
            
            while ($row=mysql_fetch_array($cek)){
                $unit=mysql_real_escape_string($row['unit']);
                $tgl_tagih=$tgl_tagih;
                $code_kel=mysql_real_escape_string($row['code_kel']);
                $cif=mysql_real_escape_string($row['cif']);
                $row_sal= mysql_fetch_array(mysql_query("select(sum(KREDIT) - sum(DEBIT))as saldo from simpanan WHERE cif ='$cif' and type not in ('04','05')")) or die(mysql_error());
                $saldo=$row_sal['saldo'];
                if($saldo==null){
                    $saldo=0;
                }else{
                    $saldo = $saldo;
                }
                $cao=mysql_real_escape_string($row['cao']);
                $norek=mysql_real_escape_string($row['norek']);
                $angsuran_pokok=mysql_real_escape_string($row['pokok']);
                $angsuran_margin=mysql_real_escape_string($row['ijaroh']);
                $angsuran=mysql_real_escape_string($row['angsuran']);
                $os=mysql_real_escape_string($row['os']);
                $bayar=mysql_real_escape_string($row['bulat']);

                $sisa = $saldo - $os;
                if  ($saldo < $os ){
                    $bayar = 0;                    
                }else{
                    $bayar =$sisa;
                }

                $status_realisasi='';
                $pb='0';
                $ke = '0';
                $tunggakan='0';
                $hari=mysql_real_escape_string($row['hari']);
                $twm=$saldo;
                $bulat=mysql_real_escape_string($row['bulat']);
                $simpanan_wajib='0';
                $simpanan_pokok ='0';                
                $nama=mysql_real_escape_string($row['Cust_Short_name']);
                $nama_kel=mysql_real_escape_string($row['nama_kel']);
                $saldo_margin=mysql_real_escape_string($row['saldo_margin']);
                $plafond=mysql_real_escape_string($row['Plafond']);

             
            
            
            
                $Data_lima[] = "('$unit', '$tgl_tagih', '$code_kel','$cif','$cao','$norek', '$angsuran_pokok','$angsuran_margin','$angsuran','$bayar','$status_realisasi','$pb','$ke','$tunggakan','$hari','$twm','$bulat','$simpanan_wajib','$simpanan_pokok','$os','$nama','$nama_kel','$saldo_margin','$plafond')";
                $Data_pull[] = "('$unit', '$tgl_tagih', '$code_kel','$cif','$cao','$norek', '$angsuran_pokok','$angsuran_margin','$angsuran','$bayar','$status_realisasi','$pb','$ke','$tunggakan','$hari','$twm','$bulat','$simpanan_wajib','$simpanan_pokok','$os','$nama','$nama_kel','$saldo_margin','$plafond','pelunasanDO')";
            
            }
            

            $sql = "insert INTO tagihan_pelunasan(unit, tgl_tagih,code_kel,cif,cao,norek,angsuran_pokok,angsuran_margin,angsuran,bayar,status_realisasi,pb,ke,tunggakan,hari,twm,bulat,simpanan_wajib,simpanan_pokok,os,nama,nama_kel,saldo_margin,plafond ) values ";
            $sql .= implode(',', $Data_lima);
            db_query($config_dua, $sql) or die(mysqli_error($config_dua));

            $sql = "insert INTO pull_data(unit, tgl_tagih,code_kel,cif,cao,norek,angsuran_pokok,angsuran_margin,angsuran,bayar,status_realisasi,pb,ke,tunggakan,hari,twm,bulat,simpanan_wajib,simpanan_pokok,os,nama,nama_kel,saldo_margin,plafond,jenis_pull ) values ";
            $sql .= implode(',', $Data_pull);
            db_query($config_dua, $sql) or die(mysqli_error($config_dua));
          


            echo "<script>alert('Pull Data Pelunasan DO sukses');</script>";
            $select = db_query($config_dua,"SELECT * from pull_data where unit =$userid and tgl_tagih > '$tgl_system'" );
            }else{
                echo "<script>alert('Gagal Pull Data !!!');</script>";
            }

    }elseif($transaksi =='penarikan'){
        $cek = mysql_query("SELECT unit, loan.code_kel as code_kel, loan.cif as cif, loan.cao as cao,saldo_margin,Plafond,deal_produk
        no_anggota as norek,pokok, ijaroh, angsuran, hari, bulat, os, Cust_Short_name, nama_kel
        FROM loan left join kelompok on loan.code_kel = kelompok.code_kel WHERE  loan.code_kel ='$cif_kel' ");


        if(mysql_num_rows($cek)>=1){                
            
            while ($row=mysql_fetch_array($cek)){
                $unit=mysql_real_escape_string($row['unit']);
                $tgl_tagih=$tgl_tagih;
                $code_kel=mysql_real_escape_string($row['code_kel']);
                $cif=mysql_real_escape_string($row['cif']);
$ijaroh=$row['ijaroh'];
		    $deal_produk=$row['deal_produk'];
		    if($deal_produk=='3'){
$pengurang = $ijaroh + 2500;
}else{
$pengurang =0;
}
                $row_sal= mysql_fetch_array(mysql_query("select(sum(KREDIT) - sum(DEBIT))as saldo from simpanan WHERE cif ='$cif' and type not in ('04','05')")) or die(mysql_error());
                $saldo=$row_sal['saldo'];
                if($saldo==null){
                    $saldo=0;
                }else{
                    $saldo = $saldo-$pengurang;
                }
                $cao=mysql_real_escape_string($row['cao']);
                $norek=mysql_real_escape_string($row['norek']);
                $angsuran_pokok=mysql_real_escape_string($row['pokok']);
                $angsuran_margin=mysql_real_escape_string($row['ijaroh']);
                $angsuran=mysql_real_escape_string($row['angsuran']);
                $os=mysql_real_escape_string($row['os']);
		
                if($nominal > $saldo){
                    $saldo = $saldo;
                }else{
                    $saldo = $nominal;
                }


if($os>0){
 $saldo = $saldo;

}else{
$saldo = $nominal;

}

                $bayar=$saldo;

                $status_realisasi='';
                $pb='0';
                $ke = '0';
                $tunggakan='0';
                $hari=mysql_real_escape_string($row['hari']);
                $twm=$saldo;
                $bulat=$saldo;
                $simpanan_wajib='0';
                $simpanan_pokok ='0';                
                $nama=mysql_real_escape_string($row['Cust_Short_name']);
                $nama_kel=mysql_real_escape_string($row['nama_kel']);
                $saldo_margin=mysql_real_escape_string($row['saldo_margin']);
                $plafond=mysql_real_escape_string($row['Plafond']);

             
            
            
            
                $Data_lima[] = "('$unit', '$tgl_tagih', '$code_kel','$cif','$cao','$norek', '$angsuran_pokok','$angsuran_margin','$angsuran','$bayar','$status_realisasi','$pb','$ke','$tunggakan','$hari','$twm','$bulat','$simpanan_wajib','$simpanan_pokok','$os','$nama','$nama_kel','$saldo_margin','$plafond')";
                $Data_pull[] = "('$unit', '$tgl_tagih', '$code_kel','$cif','$cao','$norek', '$angsuran_pokok','$angsuran_margin','$angsuran','$bayar','$status_realisasi','$pb','$ke','$tunggakan','$hari','$twm','$bulat','$simpanan_wajib','$simpanan_pokok','$os','$nama','$nama_kel','$saldo_margin','$plafond','penarikan')";
            
            }
            

            $sql = "insert INTO tagihan_penarikan(unit, tgl_tagih,code_kel,cif,cao,norek,angsuran_pokok,angsuran_margin,angsuran,bayar,status_realisasi,pb,ke,tunggakan,hari,twm,bulat,simpanan_wajib,simpanan_pokok,os,nama,nama_kel,saldo_margin,plafond ) values ";
            $sql .= implode(',', $Data_lima);
            db_query($config_dua, $sql) or die(mysqli_error($config_dua));

            $sql = "insert INTO pull_data(unit, tgl_tagih,code_kel,cif,cao,norek,angsuran_pokok,angsuran_margin,angsuran,bayar,status_realisasi,pb,ke,tunggakan,hari,twm,bulat,simpanan_wajib,simpanan_pokok,os,nama,nama_kel,saldo_margin,plafond,jenis_pull ) values ";
            $sql .= implode(',', $Data_pull);
            db_query($config_dua, $sql) or die(mysqli_error($config_dua));
          


            echo "<script>alert('Pull Data Pelunasan sukses');</script>";
            $select = db_query($config_dua,"SELECT * from pull_data where unit =$userid and tgl_tagih > '$tgl_system'");
            }else{
                echo "<script>alert('Gagal Pull Data !!!');</script>";
            }
        
   
    }

     break;
           case "02":
            $cek = mysql_query("SELECT unit, calon_loan.code_kel as code_kel, calon_loan.cif as cif, calon_loan.cao as cao,saldo_margin,Plafond,
            no_anggota as norek,pokok, ijaroh, angsuran, hari, bulat, os, Cust_Short_name, nama_kel
            FROM calon_loan left join kelompok on calon_loan.code_kel = kelompok.code_kel WHERE  calon_loan.cif ='$cif_kel' ");
		var_dump($cif_kel);
    
         if($transaksi == 'lima'){  
                    
            if(mysql_num_rows($cek)>=1){                
                    
                while ($row=mysql_fetch_array($cek)){
                    $unit=mysql_real_escape_string($row['unit']);
                    $tgl_tagih=$tgl_tagih;
                    $code_kel=mysql_real_escape_string($row['code_kel']);
                    $cif=mysql_real_escape_string($row['cif']);
                    $cao=mysql_real_escape_string($row['cao']);
                    $norek=mysql_real_escape_string($row['norek']);
                    $angsuran_pokok=mysql_real_escape_string($row['pokok']);
                    $angsuran_margin=mysql_real_escape_string($row['ijaroh']);
                    $angsuran=mysql_real_escape_string($row['angsuran']);
                    $bayar=mysql_real_escape_string($row['Plafond'] * (5/100));
                    $status_realisasi='';
                    $pb='0';
                    $ke = '0';
                    $tunggakan='0';
                    $hari=mysql_real_escape_string($row['hari']);
                    $twm='0';
                    $bulat=$bayar;
                    $simpanan_wajib='0';
                    $simpanan_pokok ='0';
                    $os=mysql_real_escape_string($row['os']);
                    $nama=mysql_real_escape_string($row['Cust_Short_name']);
                    $nama_kel=mysql_real_escape_string($row['nama_kel']);
                    $saldo_margin=mysql_real_escape_string($row['saldo_margin']);
                    $plafond=mysql_real_escape_string($row['Plafond']);
    
                 
                
                
                
                    $Data_lima[] = "('$unit', '$tgl_tagih', '$code_kel','$cif','$cao','$norek', '$angsuran_pokok','$angsuran_margin','$angsuran','$bayar','$status_realisasi','$pb','$ke','$tunggakan','$hari','$twm','$bulat','$simpanan_wajib','$simpanan_pokok','$os','$nama','$nama_kel','$saldo_margin','$plafond')";
                    $Data_pull[] = "('$unit', '$tgl_tagih', '$code_kel','$cif','$cao','$norek', '$angsuran_pokok','$angsuran_margin','$angsuran','$bayar','$status_realisasi','$pb','$ke','$tunggakan','$hari','$twm','$bulat','$simpanan_wajib','$simpanan_pokok','$os','$nama','$nama_kel','$saldo_margin','$plafond','lima')";
                
                }
    
                $sql = "insert INTO tagihan_lima_persen(unit, tgl_tagih,code_kel,cif,cao,norek,angsuran_pokok,angsuran_margin,angsuran,bayar,status_realisasi,pb,ke,tunggakan,hari,twm,bulat,simpanan_wajib,simpanan_pokok,os,nama,nama_kel,saldo_margin,plafond ) values ";
                $sql .= implode(',', $Data_lima);
                db_query($config_dua, $sql) or die(mysqli_error($config_dua));
    
                $sql = "insert INTO pull_data(unit, tgl_tagih,code_kel,cif,cao,norek,angsuran_pokok,angsuran_margin,angsuran,bayar,status_realisasi,pb,ke,tunggakan,hari,twm,bulat,simpanan_wajib,simpanan_pokok,os,nama,nama_kel,saldo_margin,plafond,jenis_pull ) values ";
                $sql .= implode(',', $Data_pull);
                db_query($config_dua, $sql) or die(mysqli_error($config_dua));
              
    
    
                echo "<script>alert('Pull Data 5% sukses');</script>";
                $select = db_query($config_dua,"SELECT * from pull_data where unit =$userid and tgl_tagih > '$tgl_system'");
                }else{
                    echo "<script>alert('Gagal Pull Data !!!');</script>";
                }
            
            }elseif($transaksi =='lebaran'){
    
                if(mysql_num_rows($cek)>=1){                
                    
                    while ($row=mysql_fetch_array($cek)){
                        $unit=mysql_real_escape_string($row['unit']);
                        $tgl_tagih=$tgl_tagih;
                        $code_kel=mysql_real_escape_string($row['code_kel']);
                        $cif=mysql_real_escape_string($row['cif']);
                        $cao=mysql_real_escape_string($row['cao']);
                        $norek=mysql_real_escape_string($row['norek']);
                        $angsuran_pokok=mysql_real_escape_string($row['pokok']);
                        $angsuran_margin=mysql_real_escape_string($row['ijaroh']);
                        $angsuran=mysql_real_escape_string($row['angsuran']);
                        $bayar=mysql_real_escape_string($row['bulat']);
                        $status_realisasi='';
                        $pb='0';
                        $ke = '0';
                        $tunggakan='0';
                        $hari=mysql_real_escape_string($row['hari']);
                        $twm='0';
                        $bulat=mysql_real_escape_string($row['bulat']);
                        $simpanan_wajib='0';
                        $simpanan_pokok ='0';
                        $os=mysql_real_escape_string($row['os']);
                        $nama=mysql_real_escape_string($row['Cust_Short_name']);
                        $nama_kel=mysql_real_escape_string($row['nama_kel']);
                        $saldo_margin=mysql_real_escape_string($row['saldo_margin']);
                        $plafond=mysql_real_escape_string($row['Plafond']);
        
                     
                    
                    
                    
                        $Data_lima[] = "('$unit', '$tgl_tagih', '$code_kel','$cif','$cao','$norek', '$angsuran_pokok','$angsuran_margin','$angsuran','$bayar','$status_realisasi','$pb','$ke','$tunggakan','$hari','$twm','$bulat','$simpanan_wajib','$simpanan_pokok','$os','$nama','$nama_kel','$saldo_margin','$plafond')";
                        $Data_pull[] = "('$unit', '$tgl_tagih', '$code_kel','$cif','$cao','$norek', '$angsuran_pokok','$angsuran_margin','$angsuran','$bayar','$status_realisasi','$pb','$ke','$tunggakan','$hari','$twm','$bulat','$simpanan_wajib','$simpanan_pokok','$os','$nama','$nama_kel','$saldo_margin','$plafond','lebaran')";
                    
                    }
        
                    $sql = "insert INTO tagihan_lebaran(unit, tgl_tagih,code_kel,cif,cao,norek,angsuran_pokok,angsuran_margin,angsuran,bayar,status_realisasi,pb,ke,tunggakan,hari,twm,bulat,simpanan_wajib,simpanan_pokok,os,nama,nama_kel,saldo_margin,plafond ) values ";
                    $sql .= implode(',', $Data_lima);
                    db_query($config_dua, $sql) or die(mysqli_error($config_dua));
        
                    $sql = "insert INTO pull_data(unit, tgl_tagih,code_kel,cif,cao,norek,angsuran_pokok,angsuran_margin,angsuran,bayar,status_realisasi,pb,ke,tunggakan,hari,twm,bulat,simpanan_wajib,simpanan_pokok,os,nama,nama_kel,saldo_margin,plafond,jenis_pull ) values ";
                    $sql .= implode(',', $Data_pull);
                    db_query($config_dua, $sql) or die(mysqli_error($config_dua));
                  
        
        
                    echo "<script>alert('Pull Data lebaran sukses');</script>";
                    $select = db_query($config_dua,"SELECT * from pull_data where unit =$userid and tgl_tagih > '$tgl_system'");
                    }else{
                        echo "<script>alert('Gagal Pull Data !!!');</script>";
                    }
                
           
            }elseif($transaksi =='pelunasan'){
            $cek = mysql_query("SELECT unit, loan.code_kel as code_kel, loan.cif as cif, loan.cao as cao,saldo_margin,Plafond,
            no_anggota as norek,pokok, ijaroh, angsuran, hari, bulat, os, Cust_Short_name, nama_kel
            FROM loan left join kelompok on loan.code_kel = kelompok.code_kel WHERE  loan.cif ='$cif_kel' AND os> 0 ");
    
    
            if(mysql_num_rows($cek)>=1){                
                
                while ($row=mysql_fetch_array($cek)){
                    $unit=mysql_real_escape_string($row['unit']);
                    $tgl_tagih=$tgl_tagih;
                    $code_kel=mysql_real_escape_string($row['code_kel']);
                    $cif=mysql_real_escape_string($row['cif']);
                    $row_sal= mysql_fetch_array(mysql_query("select(sum(KREDIT) - sum(DEBIT))as saldo from simpanan WHERE cif ='$cif' and type not in ('05','04')")) or die(mysql_error());
                    $saldo=$row_sal['saldo'];
                    if($saldo==null){
                        $saldo=0;
                    }else{
                        $saldo = $saldo;
                    }
                    $cao=mysql_real_escape_string($row['cao']);
                    $norek=mysql_real_escape_string($row['norek']);
                    $angsuran_pokok=mysql_real_escape_string($row['pokok']);
                    $angsuran_margin=mysql_real_escape_string($row['ijaroh']);
                    $angsuran=mysql_real_escape_string($row['angsuran']);
                    $os=mysql_real_escape_string($row['os']);
                    $bayar=mysql_real_escape_string($row['bulat']);
    
                    $sisa = $saldo - $os;
                    if  ($saldo < $os ){
                        $bayar = 0;                    
                    }else{
                        $bayar =$sisa;
                    }
    
                    $status_realisasi='';
                    $pb='0';
                    $ke = '0';
                    $tunggakan='0';
                    $hari=mysql_real_escape_string($row['hari']);
                    $twm=$saldo;
                    $bulat=mysql_real_escape_string($row['bulat']);
                    $simpanan_wajib='0';
                    $simpanan_pokok ='0';                
                    $nama=mysql_real_escape_string($row['Cust_Short_name']);
                    $nama_kel=mysql_real_escape_string($row['nama_kel']);
                    $saldo_margin=mysql_real_escape_string($row['saldo_margin']);
                    $plafond=mysql_real_escape_string($row['Plafond']);
    
                 
                
                
                
                    $Data_lima[] = "('$unit', '$tgl_tagih', '$code_kel','$cif','$cao','$norek', '$angsuran_pokok','$angsuran_margin','$angsuran','$bayar','$status_realisasi','$pb','$ke','$tunggakan','$hari','$twm','$bulat','$simpanan_wajib','$simpanan_pokok','$os','$nama','$nama_kel','$saldo_margin','$plafond')";
                    $Data_pull[] = "('$unit', '$tgl_tagih', '$code_kel','$cif','$cao','$norek', '$angsuran_pokok','$angsuran_margin','$angsuran','$bayar','$status_realisasi','$pb','$ke','$tunggakan','$hari','$twm','$bulat','$simpanan_wajib','$simpanan_pokok','$os','$nama','$nama_kel','$saldo_margin','$plafond','pelunasan')";
                
                }
                
    
                $sql = "insert INTO tagihan_pelunasan(unit, tgl_tagih,code_kel,cif,cao,norek,angsuran_pokok,angsuran_margin,angsuran,bayar,status_realisasi,pb,ke,tunggakan,hari,twm,bulat,simpanan_wajib,simpanan_pokok,os,nama,nama_kel,saldo_margin,plafond ) values ";
                $sql .= implode(',', $Data_lima);
                db_query($config_dua, $sql) or die(mysqli_error($config_dua));
    
                $sql = "insert INTO pull_data(unit, tgl_tagih,code_kel,cif,cao,norek,angsuran_pokok,angsuran_margin,angsuran,bayar,status_realisasi,pb,ke,tunggakan,hari,twm,bulat,simpanan_wajib,simpanan_pokok,os,nama,nama_kel,saldo_margin,plafond,jenis_pull ) values ";
                $sql .= implode(',', $Data_pull);
                db_query($config_dua, $sql) or die(mysqli_error($config_dua));
              
    
    
                echo "<script>alert('Pull Data Pelunasan sukses');</script>";
                $select = db_query($config_dua,"SELECT * from pull_data where unit =$userid and tgl_tagih > '$tgl_system'");
                }else{
                    echo "<script>alert('Gagal Pull Data !!!');</script>";
                }


  }elseif($transaksi =='pelunasanDO'){
            $cek = mysql_query("SELECT unit, loan.code_kel as code_kel, loan.cif as cif, loan.cao as cao,saldo_margin,Plafond,
            no_anggota as norek,pokok, ijaroh, angsuran, hari, bulat, os, Cust_Short_name, nama_kel
            FROM loan left join kelompok on loan.code_kel = kelompok.code_kel WHERE  loan.cif ='$cif_kel' AND os> 0 ");
    
    
            if(mysql_num_rows($cek)>=1){                
                
                while ($row=mysql_fetch_array($cek)){
                    $unit=mysql_real_escape_string($row['unit']);
                    $tgl_tagih=$tgl_tagih;
                    $code_kel=mysql_real_escape_string($row['code_kel']);
                    $cif=mysql_real_escape_string($row['cif']);
                    $row_sal= mysql_fetch_array(mysql_query("select(sum(KREDIT) - sum(DEBIT))as saldo from simpanan WHERE cif ='$cif' and type not in ('05','04')")) or die(mysql_error());
                    $saldo=$row_sal['saldo'];
                    if($saldo==null){
                        $saldo=0;
                    }else{
                        $saldo = $saldo;
                    }
                    $cao=mysql_real_escape_string($row['cao']);
                    $norek=mysql_real_escape_string($row['norek']);
                    $angsuran_pokok=mysql_real_escape_string($row['pokok']);
                    $angsuran_margin=mysql_real_escape_string($row['ijaroh']);
                    $angsuran=mysql_real_escape_string($row['angsuran']);
                    $os=mysql_real_escape_string($row['os']);
                    $bayar=mysql_real_escape_string($row['bulat']);
    
                    $sisa = $saldo - $os;
                    if  ($saldo < $os ){
                        $bayar = 0;                    
                    }else{
                        $bayar =$sisa;
                    }
    
                    $status_realisasi='';
                    $pb='0';
                    $ke = '0';
                    $tunggakan='0';
                    $hari=mysql_real_escape_string($row['hari']);
                    $twm=$saldo;
                    $bulat=mysql_real_escape_string($row['bulat']);
                    $simpanan_wajib='0';
                    $simpanan_pokok ='0';                
                    $nama=mysql_real_escape_string($row['Cust_Short_name']);
                    $nama_kel=mysql_real_escape_string($row['nama_kel']);
                    $saldo_margin=mysql_real_escape_string($row['saldo_margin']);
                    $plafond=mysql_real_escape_string($row['Plafond']);
    
                 
                
                
                
                    $Data_lima[] = "('$unit', '$tgl_tagih', '$code_kel','$cif','$cao','$norek', '$angsuran_pokok','$angsuran_margin','$angsuran','$bayar','$status_realisasi','$pb','$ke','$tunggakan','$hari','$twm','$bulat','$simpanan_wajib','$simpanan_pokok','$os','$nama','$nama_kel','$saldo_margin','$plafond')";
                    $Data_pull[] = "('$unit', '$tgl_tagih', '$code_kel','$cif','$cao','$norek', '$angsuran_pokok','$angsuran_margin','$angsuran','$bayar','$status_realisasi','$pb','$ke','$tunggakan','$hari','$twm','$bulat','$simpanan_wajib','$simpanan_pokok','$os','$nama','$nama_kel','$saldo_margin','$plafond','pelunasanDO')";
                
                }
                
    
                $sql = "insert INTO tagihan_pelunasan(unit, tgl_tagih,code_kel,cif,cao,norek,angsuran_pokok,angsuran_margin,angsuran,bayar,status_realisasi,pb,ke,tunggakan,hari,twm,bulat,simpanan_wajib,simpanan_pokok,os,nama,nama_kel,saldo_margin,plafond ) values ";
                $sql .= implode(',', $Data_lima);
                db_query($config_dua, $sql) or die(mysqli_error($config_dua));
    
                $sql = "insert INTO pull_data(unit, tgl_tagih,code_kel,cif,cao,norek,angsuran_pokok,angsuran_margin,angsuran,bayar,status_realisasi,pb,ke,tunggakan,hari,twm,bulat,simpanan_wajib,simpanan_pokok,os,nama,nama_kel,saldo_margin,plafond,jenis_pull ) values ";
                $sql .= implode(',', $Data_pull);
                db_query($config_dua, $sql) or die(mysqli_error($config_dua));
              
    
    
                echo "<script>alert('Pull Data Pelunasan sukses');</script>";
                $select = db_query($config_dua,"SELECT * from pull_data where unit =$userid and tgl_tagih > '$tgl_system'");
                }else{
                    echo "<script>alert('Gagal Pull Data !!!');</script>";
                }


  }elseif($transaksi =='pelunasanRestMargin'){
            $cek = mysql_query("SELECT unit, loan.code_kel as code_kel, loan.cif as cif, loan.cao as cao,saldo_margin,Plafond,
            no_anggota as norek,pokok, ijaroh, angsuran, hari, bulat, os, Cust_Short_name, nama_kel
            FROM loan left join kelompok on loan.code_kel = kelompok.code_kel WHERE  loan.cif ='$cif_kel' AND os> 0 ");
    
    
            if(mysql_num_rows($cek)>=1){                
                
                while ($row=mysql_fetch_array($cek)){
                    $unit=mysql_real_escape_string($row['unit']);
                    $tgl_tagih=$tgl_tagih;
                    $code_kel=mysql_real_escape_string($row['code_kel']);
                    $cif=mysql_real_escape_string($row['cif']);
                    $row_sal= mysql_fetch_array(mysql_query("select(sum(KREDIT) - sum(DEBIT))as saldo from simpanan WHERE cif ='$cif' and type not in ('05','04')")) or die(mysql_error());
                    $saldo=$row_sal['saldo'];
                    if($saldo==null){
                        $saldo=0;
                    }else{
                        $saldo = $saldo;
                    }
                    $cao=mysql_real_escape_string($row['cao']);
                    $norek=mysql_real_escape_string($row['norek']);
                    $angsuran_pokok=mysql_real_escape_string($row['pokok']);
                    $angsuran_margin=mysql_real_escape_string($row['ijaroh']);
                    $angsuran=mysql_real_escape_string($row['angsuran']);
                    $os=mysql_real_escape_string($row['os']);
                    $bayar=mysql_real_escape_string($row['bulat']);
    
                    $sisa = $saldo - $os;
                    if  ($saldo < $os ){
                        $bayar = 0;                    
                    }else{
                        $bayar =$sisa;
                    }
    
                    $status_realisasi='';
                    $pb='0';
                    $ke = '0';
                    $tunggakan='0';
                    $hari=mysql_real_escape_string($row['hari']);
                    $twm=$saldo;
                    $bulat=mysql_real_escape_string($row['bulat']);
                    $simpanan_wajib='0';
                    $simpanan_pokok ='0';                
                    $nama=mysql_real_escape_string($row['Cust_Short_name']);
                    $nama_kel=mysql_real_escape_string($row['nama_kel']);
                    $saldo_margin=mysql_real_escape_string($row['saldo_margin']);
                    $plafond=mysql_real_escape_string($row['Plafond']);
    
                 
                
                
                
                    $Data_lima[] = "('$unit', '$tgl_tagih', '$code_kel','$cif','$cao','$norek', '$angsuran_pokok','$angsuran_margin','$angsuran','$bayar','$status_realisasi','$pb','$ke','$tunggakan','$hari','$twm','$bulat','$simpanan_wajib','$simpanan_pokok','$os','$nama','$nama_kel','$saldo_margin','$plafond')";
                    $Data_pull[] = "('$unit', '$tgl_tagih', '$code_kel','$cif','$cao','$norek', '$angsuran_pokok','$angsuran_margin','$angsuran','$bayar','$status_realisasi','$pb','$ke','$tunggakan','$hari','$twm','$bulat','$simpanan_wajib','$simpanan_pokok','$os','$nama','$nama_kel','$saldo_margin','$plafond','pelunasanRestMargin')";
                
                }
                
    
                $sql = "insert INTO tagihan_pelunasan(unit, tgl_tagih,code_kel,cif,cao,norek,angsuran_pokok,angsuran_margin,angsuran,bayar,status_realisasi,pb,ke,tunggakan,hari,twm,bulat,simpanan_wajib,simpanan_pokok,os,nama,nama_kel,saldo_margin,plafond ) values ";
                $sql .= implode(',', $Data_lima);
                db_query($config_dua, $sql) or die(mysqli_error($config_dua));
    
                $sql = "insert INTO pull_data(unit, tgl_tagih,code_kel,cif,cao,norek,angsuran_pokok,angsuran_margin,angsuran,bayar,status_realisasi,pb,ke,tunggakan,hari,twm,bulat,simpanan_wajib,simpanan_pokok,os,nama,nama_kel,saldo_margin,plafond,jenis_pull ) values ";
                $sql .= implode(',', $Data_pull);
                db_query($config_dua, $sql) or die(mysqli_error($config_dua));
              
    
    
                echo "<script>alert('Pull Data Pelunasan Rest Pokok Margin sukses');</script>";
                $select = db_query($config_dua,"SELECT * from pull_data where unit =$userid and tgl_tagih > '$tgl_system'");
                }else{
                    echo "<script>alert('Gagal Pull Data !!!');</script>";
                }



}elseif($transaksi =='pelunasan19'){

 $cek_run_tenor= mysql_fetch_array(mysql_query("select run_tenor,angsuran from loan  WHERE cif ='$cif_kel' ")) or die(mysql_error());
                $run_tenor=$cek_run_tenor['run_tenor'];
$angsuran = $cek_run_tenor['angsuran'];
$cek_tunggak= mysql_fetch_array(mysql_query("select sum(KREDIT)- sum(DEBIT) as tunggak from tunggakan WHERE cif ='$cif_kel'"));
        $ada_tunggak=$cek_tunggak['tunggak']; 
        $ft=round(compat_num($ada_tunggak/$angsuran));  
  
            $cek = mysql_query("SELECT unit, loan.code_kel as code_kel, loan.cif as cif, loan.cao as cao,saldo_margin,Plafond,
            no_anggota as norek,pokok, ijaroh, angsuran, hari, bulat, os, Cust_Short_name, nama_kel
            FROM loan left join kelompok on loan.code_kel = kelompok.code_kel WHERE  loan.cif ='$cif_kel' AND os> 0 ");

	if($run_tenor<=18){
	
	if($ft<=0){
    
    
            if(mysql_num_rows($cek)>=1){                
                
                while ($row=mysql_fetch_array($cek)){
                    $unit=mysql_real_escape_string($row['unit']);
                    $tgl_tagih=$tgl_tagih;
                    $code_kel=mysql_real_escape_string($row['code_kel']);
                    $cif=mysql_real_escape_string($row['cif']);
                    $row_sal= mysql_fetch_array(mysql_query("select(sum(KREDIT) - sum(DEBIT))as saldo from simpanan WHERE cif ='$cif' and type not in ('04','05')")) or die(mysql_error());
                    $saldo=$row_sal['saldo'];
                    if($saldo==null){
                        $saldo=0;
                    }else{
                        $saldo = $saldo;
                    }
                    $cao=mysql_real_escape_string($row['cao']);
                    $norek=mysql_real_escape_string($row['norek']);
                    $angsuran_pokok=mysql_real_escape_string($row['pokok']);
                    $angsuran_margin=mysql_real_escape_string($row['ijaroh']);
                    $angsuran=mysql_real_escape_string($row['angsuran']);
                    $os=mysql_real_escape_string($row['os']);
                    $bayar=mysql_real_escape_string($row['bulat']);
    
                    $sisa = $saldo - $os;
                    if  ($saldo < $os ){
                        $bayar = 0;                    
                    }else{
                        $bayar =$sisa;
                    }
    
                    $status_realisasi='';
                    $pb='0';
                    $ke = '0';
                    $tunggakan='0';
                    $hari=mysql_real_escape_string($row['hari']);
                    $twm=$saldo;
                    $bulat=mysql_real_escape_string($row['bulat']);
                    $simpanan_wajib='0';
                    $simpanan_pokok ='0';                
                    $nama=mysql_real_escape_string($row['Cust_Short_name']);
                    $nama_kel=mysql_real_escape_string($row['nama_kel']);
                    $saldo_margin=mysql_real_escape_string($row['saldo_margin']);
                    $plafond=mysql_real_escape_string($row['Plafond']);
    
                 
                
                
                
                    $Data_lima[] = "('$unit', '$tgl_tagih', '$code_kel','$cif','$cao','$norek', '$angsuran_pokok','$angsuran_margin','$angsuran','$bayar','$status_realisasi','$pb','$ke','$tunggakan','$hari','$twm','$bulat','$simpanan_wajib','$simpanan_pokok','$os','$nama','$nama_kel','$saldo_margin','$plafond')";
                    $Data_pull[] = "('$unit', '$tgl_tagih', '$code_kel','$cif','$cao','$norek', '$angsuran_pokok','$angsuran_margin','$angsuran','$bayar','$status_realisasi','$pb','$ke','$tunggakan','$hari','$twm','$bulat','$simpanan_wajib','$simpanan_pokok','$os','$nama','$nama_kel','$saldo_margin','$plafond','pelunasan19')";
                
                }
                
    
                $sql = "insert INTO tagihan_pelunasan(unit, tgl_tagih,code_kel,cif,cao,norek,angsuran_pokok,angsuran_margin,angsuran,bayar,status_realisasi,pb,ke,tunggakan,hari,twm,bulat,simpanan_wajib,simpanan_pokok,os,nama,nama_kel,saldo_margin,plafond ) values ";
                $sql .= implode(',', $Data_lima);
                db_query($config_dua, $sql) or die(mysqli_error($config_dua));
    
                $sql = "insert INTO pull_data(unit, tgl_tagih,code_kel,cif,cao,norek,angsuran_pokok,angsuran_margin,angsuran,bayar,status_realisasi,pb,ke,tunggakan,hari,twm,bulat,simpanan_wajib,simpanan_pokok,os,nama,nama_kel,saldo_margin,plafond,jenis_pull ) values ";
                $sql .= implode(',', $Data_pull);
                db_query($config_dua, $sql) or die(mysqli_error($config_dua));
              
    
    
                echo "<script>alert('Pull Data Pelunasan sukses');</script>";
                $select = db_query($config_dua,"SELECT * from pull_data where unit =$userid and tgl_tagih > '$tgl_system'");
                }else{
                    echo "<script>alert('Gagal Pull Data !!!');</script>";
                }
	}else{
 echo "<script>alert('Maaf CIF tidak masuk kategori diskon margin');</script>";

}
}else{
 echo "<script>alert('Maaf CIF tidak masuk kategori diskon margin');</script>";
}


            
       
        }elseif($transaksi =='penarikan'){
            $cek = mysql_query("SELECT unit, loan.code_kel as code_kel, loan.cif as cif, loan.cao as cao,saldo_margin,Plafond,deal_produk,
            no_anggota as norek,pokok, ijaroh, angsuran, hari, bulat, os, Cust_Short_name, nama_kel
            FROM loan left join kelompok on loan.code_kel = kelompok.code_kel WHERE  loan.cif ='$cif_kel' ");
    
    
            if(mysql_num_rows($cek)>=1){                
                
                while ($row=mysql_fetch_array($cek)){
                    $unit=mysql_real_escape_string($row['unit']);
                    $tgl_tagih=$tgl_tagih;
                    $code_kel=mysql_real_escape_string($row['code_kel']);
		    $ijaroh=$row['ijaroh'];
		    $deal_produk=$row['deal_produk'];
		    if($deal_produk=='3'){
$pengurang = $ijaroh + 2500;
}else{
$pengurang =0;
}
                    $cif=mysql_real_escape_string($row['cif']);
                    $row_sal= mysql_fetch_array(mysql_query("select(sum(KREDIT) - sum(DEBIT))as saldo from simpanan WHERE cif ='$cif' and type not in ('04','05')")) or die(mysql_error());
                    $saldo=$row_sal['saldo'];
                    if($saldo==null){
                        $saldo=0;
                    }else{
                        $saldo = $saldo-$pengurang;
                    }
                    $cao=mysql_real_escape_string($row['cao']);
                    $norek=mysql_real_escape_string($row['norek']);
                    $angsuran_pokok=mysql_real_escape_string($row['pokok']);
                    $angsuran_margin=mysql_real_escape_string($row['ijaroh']);
                    $angsuran=mysql_real_escape_string($row['angsuran']);
                    $os=mysql_real_escape_string($row['os']);
                    if($nominal > $saldo){
                        $saldo = $saldo;
                    }else{
                        $saldo = $nominal;
                    }


if($os>0){
 $saldo = $saldo;

}else{
$saldo = $nominal;

}
                    $bayar=$saldo;
    
                    
                    $status_realisasi='';
                    $pb='0';
                    $ke = '0';
                    $tunggakan='0';
                    $hari=mysql_real_escape_string($row['hari']);
                    $twm=$saldo;
                    $bulat=$saldo;
                    $simpanan_wajib='0';
                    $simpanan_pokok ='0';                
                    $nama=mysql_real_escape_string($row['Cust_Short_name']);
                    $nama_kel=mysql_real_escape_string($row['nama_kel']);
                    $saldo_margin=mysql_real_escape_string($row['saldo_margin']);
                    $plafond=mysql_real_escape_string($row['Plafond']);
    
                 
                
                
                
                    $Data_lima[] = "('$unit', '$tgl_tagih', '$code_kel','$cif','$cao','$norek', '$angsuran_pokok','$angsuran_margin','$angsuran','$bayar','$status_realisasi','$pb','$ke','$tunggakan','$hari','$twm','$bulat','$simpanan_wajib','$simpanan_pokok','$os','$nama','$nama_kel','$saldo_margin','$plafond')";
                    $Data_pull[] = "('$unit', '$tgl_tagih', '$code_kel','$cif','$cao','$norek', '$angsuran_pokok','$angsuran_margin','$angsuran','$bayar','$status_realisasi','$pb','$ke','$tunggakan','$hari','$twm','$bulat','$simpanan_wajib','$simpanan_pokok','$os','$nama','$nama_kel','$saldo_margin','$plafond','penarikan')";
                
                }
                
    
                $sql = "insert INTO tagihan_penarikan(unit, tgl_tagih,code_kel,cif,cao,norek,angsuran_pokok,angsuran_margin,angsuran,bayar,status_realisasi,pb,ke,tunggakan,hari,twm,bulat,simpanan_wajib,simpanan_pokok,os,nama,nama_kel,saldo_margin,plafond ) values ";
                $sql .= implode(',', $Data_lima);
                db_query($config_dua, $sql) or die(mysqli_error($config_dua));
    
                $sql = "insert INTO pull_data(unit, tgl_tagih,code_kel,cif,cao,norek,angsuran_pokok,angsuran_margin,angsuran,bayar,status_realisasi,pb,ke,tunggakan,hari,twm,bulat,simpanan_wajib,simpanan_pokok,os,nama,nama_kel,saldo_margin,plafond,jenis_pull ) values ";
                $sql .= implode(',', $Data_pull);
                db_query($config_dua, $sql) or die(mysqli_error($config_dua));
              
    
    
                echo "<script>alert('Pull Data penarikan sukses');</script>";
                $select = db_query($config_dua,"SELECT * from pull_data where unit =$userid and tgl_tagih > '$tgl_system'");
                }else{
                    echo "<script>alert('Gagal Pull Data !!!');</script>";
                }
            
       
        }


     break;
      default:
    }
        }

    
  ?>
 
  <?php
    
    $select = db_query($config_dua,"SELECT * from pull_data where unit =$userid and tgl_tagih >= '$tgl_system' ");
    while($data = db_fetch_array($select)){


        ?>
  <tr>
    <td><?php echo $data['norek']; ?></td>
    <td><?php echo $data['nama'];  ?></td>
    <td><?php echo tgl_indo ($data['tgl_tagih']);?></td>
    <td><?php echo $data['jenis_pull']; ?></td>
    <td class="text-end"><?php echo number_format (compat_num($data['bayar'])); ?></td>
    <td class="text-center"><a href="delete.php?cif=<?php echo $data['cif'];?>&jenis=<?php echo $data['jenis_pull'];?>" class="btn btn-outline-danger btn-sm" onClick="return confirm('Apakah Anda yakin?')">Hapus</a></td>
  </tr>
    
  </tr>
  <?php
        
    }
    }else{
     
    $select = db_query($config_dua,"SELECT * from pull_data where unit =$userid and tgl_tagih >= '$tgl_system'");
    while($data = db_fetch_array($select)){


        ?>
  <tr>
    <td><?php echo $data['norek']; ?></td>
    <td><?php echo $data['nama'];  ?></td>
    <td><?php echo tgl_indo ($data['tgl_tagih']);?></td>
    <td><?php echo $data['jenis_pull']; ?></td>
    <td class="text-end"><?php echo number_format (compat_num($data['bayar'])); ?></td>
    <td class="text-center"><a href="delete.php?cif=<?php echo $data['cif'];?>&jenis=<?php echo $data['jenis_pull'];?>" class="btn btn-outline-danger btn-sm" onClick="return confirm('Apakah Anda yakin?')">Hapus</a></td>
  </tr>

  <?php
        
    }
}
    ?>
 
  </tbody>
</table>
</div>
<script>
// isian Nominal hanya untuk transaksi Penarikan (sama dengan versi lama)
$(document).ready(function(){
$('.nilai').hide();
$('#nominal').hide();

$('#transaksi').change(function(){
if($('#transaksi').val()=='penarikan'){
    $('.nilai').show();
$('#nominal').show();
}
});
});
</script>
<?php
halaman_akhir();
