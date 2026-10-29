<?php require_once __DIR__ . '/../../php8_compat.php';
ini_set('display_errors',1);
date_default_timezone_set('Asia/Jakarta');
session_start();
ob_start();
        $userid=$_SESSION['userid'];
        if(!isset($_SESSION['userid'])){
   header("location:../index.html");
 exit; }



include "../conn.php";

$cod = db_fetch_array(db_query($koneksi, "SELECT kode_branch FROM user where userid='$userid'"));
    $unit=$cod[0];

$today=date("ymd");
$buss_date=$today;
  $tahun = substr($today,0,2);
   $bulan=substr($today,2,2);
  $hari=substr($today,4,2);
  $t="$unit$tahun$bulan$hari";
  $tgl=date("ymd");
$dcount = db_query ($koneksi,"select max(norek) AS last FROM deposito where norek LIKE '%$t%'");
              $hit = db_fetch_array($dcount);
              $lastNoTransaksi = $hit['last'];
             $lastNoUrut = substr($lastNoTransaksi, 12, 4);
                $nextNoUrut = (int) $lastNoUrut + 1;
                $nextNoTransaksi = $today.sprintf('%04s', $nextNoUrut);
              $norek="$unit$nextNoTransaksi";
       


?>
<!DOCTYPE html>
<html>
<style>
    #result {
        height:20px;
        font-size:12px;
        font-family:Arial, Helvetica, sans-serif;
        color:#333;
        padding:5px;
        margin-bottom:10px;
        background-color:#FFFF99;
    }
    #country{
        padding:3px;
        border:1px #CCC solid;
        font-size:12px;
    }
    .suggestionsBox {
        position: absolute;
        left: 20px;
        top:60px;
        margin: 26px 0px 0px 0px;
        width: 200px;
        padding:0px;
        background-color:#2F6B4F;
        border-top: 3px solid #2F6B4F;
        color: #fff;
    }
    .suggestionList {
        margin: 0px;
        padding: 0px;
    }
    .suggestionList ul li {
        list-style:none;
        margin: 0px;
        padding: 6px;
        border-bottom:1px dotted #666;
        cursor: pointer;
    }
    .suggestionList ul li:hover {
        background-color: #b3b3ff;
        color:#000;
    }
    ul {
        font-family:Arial, Helvetica, sans-serif;
        font-size:11px;
        color:#FFF;
        padding:0;
        margin:0;
    }
   
    .load{
    background-image:url(loader.gif);
    background-position:right;
    background-repeat:no-repeat;
    }
   
    #suggest {
        position:relative;
    }
    </style>
<head>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <!-- css table datatables/dataTables -->
        <link rel="stylesheet" href="datatables/dataTables.bootstrap.css"/>
       
         <link type="text/css" href="../css/bootstrap.css" rel="stylesheet">
        <link type="text/css" href="../bootstrap/css/bootstrap.min.css" rel="stylesheet">
        <link type="text/css" href="../bootstrap/css/bootstrap-responsive.min.css" rel="stylesheet">
        <link type="text/css" href="../css/theme.css" rel="stylesheet">
        <link type="text/css" href="../images/icons/css/font-awesome.css" rel="stylesheet">
        <link type="text/css" href='http://fonts.googleapis.com/css?family=Open+Sans:400italic,600italic,400,600'
            rel='stylesheet'>
       
        <script src="../scripts/jquery-1.9.1.min.js" type="text/javascript"></script>
        <script src="../scripts/jquery-ui-1.10.1.custom.min.js" type="text/javascript"></script>
    </head>
    <body>
               

            <div class="container">
                <div class="row">
                    <div class="span12">
                        <div class="content"><div class="panel panel-default">
                        <div class="panel-heading">
                        <h3 class="panel-title"><i class="icon-user"></i> Data Deposan Nur Insani</h3>
                        </div>
                        <div class="panel-body">
                            <?php
            if(isset($_POST['input'])){
                $cif           = $_POST['code'];
$dtitip = db_query ($koneksi,"select sum(nominal) AS titip FROM dep_titip where cif = '$cif'");
              $titip = db_fetch_array($dtitip);
              $tot_titip = $titip['titip'];

                $bilyet       = $_POST['bilyet'];
                $tenor        = $_POST['tenor'];
                $tgl          = $_POST['tgl'];
                $eq_rate      = $_POST['eq_rate'];
                $nama_bank    = $_POST['nama_bank'];
                $norek_bank   = $_POST['norek_bank'];
                $cara_hitung  = $_POST['cara_hitung'];

                if ($tenor =='6'){
                    $sima="1010-2203000";
                }elseif ($tenor =='3') {
                    $sima="1010-2202000";
                }else {
                    $sima="1010-2204000";
                }
               
                //$notelp        = $_POST['notelp'];
                $nominal_as        = str_replace ('.','',$_POST['nominal']);
$nominal=$nominal_as + $tot_titip;
                $rasio        =$_POST['nisbah'];
                $aro        = $_POST['aro'];
$jenis_aro        = $_POST['jenis_aro'];
if ($aro=='ARO-ARO'){
$aro=$jenis_aro;
}else {
$aro=$aro;
}

                $bayar        = $_POST['bayar'];
                $sim='SIM';
                $jenis_simpanan="$sim-$tenor";
                $star_date=date("y-m-d");
               
                $jenis=$_POST['jenis'];
                if ($jenis =="WD"){
                    $tahun = substr($tgl,0,2);
                    $bulan=substr($tgl,2,5);
                    $code_bulan="WD$tahun$bulan";
                }elseif($jenis =="DP"){
                    $tahun = substr($tgl,0,2);
                    $bulan=substr($tgl,2,5);
                    $code_bulan="DP$tahun$bulan";
                   
                }elseif($jenis =="MC"){
                    $tahun = substr($tgl,0,2);
                    $bulan=substr($tgl,2,5);
                    $code_bulan="MC20$tahun$bulan";
                   
                }elseif($jenis =="EMC"){
                    $tahun = substr($tgl,0,2);
                    $bulan=substr($tgl,2,5);
                    $code_bulan="EMC20$tahun$bulan";
                   
                }
               
                $maturity_date =  date ("Y-m-d", strtotime("+$tenor month", strtotime($tgl)));
                $cek = db_query($koneksi, "SELECT * FROM deposito WHERE norek='$norek'");
                if(db_num_rows($cek) == 0){
                        
                $awal_hari = $tgl;
                $akhir_hari = $maturity_date;
                $x=0;
                while (strtotime($awal_hari) < strtotime($akhir_hari)) {
                $x ++;
                $awal_hari= date ("Y-m-d", strtotime("+1 month", strtotime($awal_hari)));
                $detail= db_query($koneksi,"INSERT INTO deposito_detail (id_deposito,cif,ke,tgl_jatuh_tempo,tgl_bayar,jumlah_nisbah,rek_tujuan,ket) values('$norek','$cif','$x','$awal_hari','$awal_hari','0','$norek','')") or die(mysqli_error($koneksi));
                   
                    }
               
               
if ($tot_titip > 0){
$insert = db_query($koneksi, "INSERT INTO deposito(norek,cif,bilyet, nominal,tenor,star_date,maturity_date,jenis_simpanan,aro,rasio,code_bulan,unit,suffix,contract_date,userid,jenis_bayar,eq_rate,nama_bank,norek_bank,cara_hitung)
                                                            VALUES('$norek','$cif', '$bilyet', '$nominal', '$tenor', '$tgl','$maturity_date','$jenis_simpanan','$aro','$rasio','$code_bulan','$unit','1','$tgl','$userid','$bayar','$eq_rate','$nama_bank','$norek_bank','$cara_hitung')") or die(mysqli_error());

$jurnal=db_fetch_array(db_query($koneksi,"SELECT max(nomor_jurnal) FROM jurnal_umum where unit='$branch' ORDER BY tanggal_selesai DESC"));
                    $nomor=$jurnal[0]+1;
                    $trns="BU/".$unit .$nomor;
                $keterangan_transaksi="Penempatan Dana Norek $norek";
                $query=db_query($koneksi,"insert into jurnal_umum(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values('$trns','$kode_transaksi','$tgl','$unit')");
                $deb_dep=db_query($koneksi,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                    values('$unit','$trns','1010-2851000','$tgl','Bukti System','$keterangan_transaksi','$nominal_as','$userid')");
                $cr_dep=db_query($koneksi,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                    values('$unit','$trns','$sima','$tgl','Bukti System','$keterangan_transaksi','$nominal_as','$userid')");
                
//$in_dep= mysqli_query($koneksi,"insert  into dep_realisasi(id_realisasi,norek,cif,jumlah_bayar,tgl_bayar,tgl_jatuh_tempo,norek_tujuan,ket,status)VALUES('$norek','$norek','$cif','$nominal_as','$tgl','$maturity_date','$norek_tujuan','EVVI','PENEMPATAN')");

//$deb_dep=mysqli_query($koneksi,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
//values('$unit','$trns','1010-2851000','$tgl','Bukti System','$keterangan_transaksi','$tot_titip','$userid')");

//$cr_dep=mysqli_query($koneksi,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
//values('$unit','$trns','$sima','$tgl','Bukti System','$keterangan_transaksi','$tot_titip','$userid')");

$input=db_query($koneksi,"delete from dep_titip where cif='$cif'");

}else {
$insert = db_query($koneksi, "INSERT INTO deposito(norek,cif,bilyet, nominal,tenor,star_date,maturity_date,jenis_simpanan,aro,rasio,code_bulan,unit,suffix,contract_date,userid,jenis_bayar,eq_rate,nama_bank,norek_bank,cara_hitung)
                                                            VALUES('$norek','$cif', '$bilyet', '$nominal_as', '$tenor', '$tgl','$maturity_date','$jenis_simpanan','$aro','$rasio','$code_bulan','$unit','1','$tgl','$userid','$bayar','$eq_rate','$nama_bank','$norek_bank','$cara_hitung')") or die(mysqli_error());
$jurnal=db_fetch_array(db_query($koneksi,"SELECT max(nomor_jurnal) FROM jurnal_umum where unit='$branch' ORDER BY tanggal_selesai DESC"));
                    $nomor=$jurnal[0]+1;
                    $trns="BU/".$unit .$nomor;
                $keterangan_transaksi="Penempatan Dana Norek $norek";
                $query=db_query($koneksi,"insert into jurnal_umum(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values('$trns','$kode_transaksi','$tgl','$unit')");
                $deb_dep=db_query($koneksi,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                    values('$unit','$trns','1010-2851000','$tgl','Bukti System','$keterangan_transaksi','$nominal_as','$userid')");
                $cr_dep=db_query($koneksi,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                    values('$unit','$trns','$sima','$tgl','Bukti System','$keterangan_transaksi','$nominal_as','$userid')");
                $in_dep= db_query($koneksi,"insert  into dep_realisasi(id_realisasi,norek,cif,jumlah_bayar,tgl_bayar,tgl_jatuh_tempo,norek_tujuan,ket,status)VALUES('$norek','$norek','$cif','$nominal_as','$tgl','$maturity_date','$norek_tujuan','EVVI','PENEMPATAN')");



}

                   
        if($insert){
         

                            echo '<div class="alert alert-success alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>Data Berhasil Di Simpan.</div>';
header("location:../view/master_trans.php");
                        }else{
                            echo '<div class="alert alert-danger alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>Ups, Data Gagal Di simpan !</div>';
                        }
                }else{
                    echo '<div class="alert alert-danger alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>Rekening Sudah Ada..!</div>';
                }
            }
            ?>
           
            <blockquote>
            Input Data Deposan Nur Insani
            </blockquote>
                      <form name="form1" id="form1" class="form-horizontal row-fluid" action="input_trns.php" method="POST" >
                                        <div class="control-group">
                                            <label class="control-label" for="basicinput">Norek</label>
                                            <div class="controls">
                                                <input type="text" name="norek" id="norek" placeholder="Tidak perlu di isi" value="<?php echo $norek; ?>" class="form-control span8 tip" readonly="readonly">
                                            </div>
                                        </div>
                                        <div class="control-group">
                                            <label class="control-label" for="basicinput">Tanggal Transaksi</label>
                                            <div class="controls">
                                                <input type="text" name="tgl" id="tgl" class="input-group date form-control span8 tip" type="text"  date="" placeholder="yyyy/mm/dd" required>
                                            </div>
                                        </div>
                                        <div id="suggest">
                                        <div class="control-group">
                                            <label class="control-label" for="basicinput">No Aggota</label>
                                        <div class="controls">
                                             <input type="text" onKeyUp="suggest(this.value);" name="code"  onBlur="fill2();" id="kode" size="15" placeholder="cif deposan" class="form-control span8 tip" required>
                                            <div class="suggestionsBox" id="suggestions" style="display: none;"> <img src="arrow.png" style="position: relative; top: -12px; left: 30px;" alt="upArrow">
                                            <div class="suggestionList" id="suggestionsList" >
                                            </div>
                                            </div>
                                            </div>
                                            </div>
                                            </div>
                                        <div class="control-group">
                                            <label class="control-label" for="basicinput">Bilyet</label>
                                            <div class="controls">
                                                <input type="text" name="bilyet" id="bilyet" placeholder="Bilyet" class="form-control span8 tip" required>
                                            </div>
                                            </div>
                                        <div class="control-group">
                                            <label class="control-label" for="basicinput">Nominal Simpanan</label>
                                            <div class="controls">
                                                <input type="text" name="nominal" id="nominal" placeholder="Nominal Simpanan" class="form-control span8 tip" required>
                                            </div>
                                        </div>
                                        <div class="control-group">
                                        <label class="control-label" for="basicinput">Tenor</label>
                                            <div class="controls">
                                            <select name="tenor" id ="tenor" class="form-control span8 tip" required>
                                            <option value="">Pilih Tenor</option>
                                            <option value="3">3 Bulan</option>
                                            <option value="6">6 Bulan</option>
                                            <option value="12">12 Bulan</option>
                               
                                            </select>
                                        </div>
                                        </div>
                                        <div class="control-group">
                                            <label class="control-label" for="basicinput">Nisbah</label>
                                            <div class="controls">
                                                <input type="text" name="nisbah" id="nisbah" placeholder="Nisbah" class="form-control span8 tip" required>
                                            </div>
                                        </div>

<div class="control-group">
                                            <label class="control-label" for="basicinput">Eq Rate</label>
                                            <div class="controls">
                                                <input type="text" name="eq_rate" id="eq_rate" placeholder="Eq Rate" class="form-control span8 tip" required>
                                            </div>
                                        </div>
                                        	
                                        <div class="control-group">
											<label class="control-label" for="basicinput">Cara Hitung</label>
											<div class="controls">
											<select name="cara_hitung" id ="cara_hitung" class="form-control span8 tip" required>
											<option value="">Cara Hitung</option>
											<option value="1">Nisbah</option>
											<option value="2">Rate</option>
																		
											</select> 
											</div>
										</div>
                                                                               
                                        <div class="control-group">
                                        <label class="control-label" for="basicinput">Perpanjangan</label>
                                            <div class="controls">
                                            <select name="aro" id ="aro" class="form-control span8 tip" required>
                                                <option value="">Pilih Perpanjangan</option>
                                                <option value="ARO">ARO</option>
                                                <option value="ARO-ARO">ARO ARO</option>
                                                <option value="BREAK">Tidak di Perpanjang</option>
                               
                                            </select>
                                        </div>
                                        </div>
<div class="control-group">
<div id="tombol" class="hasil">
<label class="control-label" for="basicinput">Jenis ARO</label>
<div class="controls">
<select name="jenis_aro" id ="jenis_aro" class="form-control span8 tip" >
<option value="">Pilih ARO</option>
<option value="ARO_bulan">Bulan</option>
<option value="ARO_jatpo">Jatuh Tempo</option>

 
</select>
</div>
</div>
</div>
                                        <div class="control-group">
                                            <label class="control-label" for="basicinput">Jenis Dana</label>
                                            <div class="controls">
                                            <select name="jenis" id ="jenis" class="form-control span8 tip" required>
                                            <option value="">Pilih Dana</option>
                                            <option value="DP">DEPOSITO MUDHARABAH</option>
                                            <option value="MC">MICRO CAPITAL</option>
                                            <option value="EMC">ESCROW MICRO CAPITAL</option>
                               
                                            </select>                                             </div>
                                        </div>  
<div class="control-group">
                                            <label class="control-label" for="basicinput">Pembayaran Bagi Hasil</label>
                                            <div class="controls">
                                            <select name="bayar" id ="bayar" class="form-control span8 tip" required>
                                            <option value="">Pilih Jenis</option>
                                            <option value="lain">Rekening Bank Lain</option>
                                            <option value="ni">Rekening Nurinsani</option>
                                           
                               
                                            </select>                                             </div>
                                        </div>                                      
                                   <div class="control-group">
                                            <label class="control-label" for="basicinput">Nama Bank</label>
                                            <div class="controls">
                                                <input type="text" name="nama_bank" id="nama_bank" placeholder="nama_bank" class="form-control span8 tip" required>
                                            </div>
                                        </div>
<div class="control-group">
                                            <label class="control-label" for="basicinput">Norek Bank</label>
                                            <div class="controls">
                                                <input type="text" name="norek_bank" id="norek_bank" placeholder="norek_bank" class="form-control span8 tip" required>
                                            </div>
                                        </div>


                                        <div class="control-group">
                                            <div class="controls">
                                                <button type="submit" name="input" id="input" class="btn btn-sm btn-primary">Simpan</button>
                                               <a href="../view/master_trans.php" class="btn btn-sm btn-danger">Kembali</a>
                                            </div>
                                        </div>
                                </form>
                        </div>
                        <!--/.content-->
                    </div>
                    <!--/.span9-->
                </div>
            </div>
          </div>
        </div>
       
        <!--/.wrapper-->
        <script src="../js/bootstrap-datepicker.js"></script>
        <script>
    //options method for call datepicker
    $(".input-group.date").datepicker({ autoclose: true, todayHighlight: true, format: 'yyyy-mm-dd', });
   
   
    </script>
        <script src="../bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
       
        <script src="../datatables/jquery.dataTables.js"></script>
        <script src="../datatables/dataTables.bootstrap.js"></script>
          <script>
        $(document).ready(function() {
                var dataTable = $('#lookup').DataTable( {
                    "processing": true,
                    "serverSide": true,
                    "ajax":{
                        url :"../view/ajax-grid-data_trns.php", // json datasource
                        type: "post",  // method  , by default get
                        error: function(){  // error handling
                            $(".lookup-error").html("");
                            $("#lookup").append('<tbody class="employee-grid-error"><tr><th colspan="3">No data found in the server</th></tr></tbody>');
                            $("#lookup_processing").css("display","none");
                           
                        }
                    }
                } );
            } );
        </script>
        <script type="text/javascript">
     
    function suggest(inputString){
        if(inputString.length == 0) {
            $('#suggestions').fadeOut();
        } else {
        $('#country').addClass('load');
            $.post("../autosuggest.php", {queryString: ""+inputString+""}, function(data){
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
   
    </script>
   
<script type="text/javascript">

    /* Tanpa Rupiah */
    var nominal = document.getElementById('nominal');
    nominal.addEventListener('keyup', function(e)
    {
        nominal.value = formatRupiah(this.value);
    });
   
    /* Dengan Rupiah */
    var dengan_rupiah = document.getElementById('dengan-rupiah');
    dengan_rupiah.addEventListener('keyup', function(e)
    {
        dengan_rupiah.value = formatRupiah(this.value, 'Rp. ');
    });
   
    /* Fungsi */
    function formatRupiah(angka, prefix)
    {
        var number_string = angka.replace(/[^,\d]/g, '').toString(),
            split    = number_string.split(','),
            sisa     = split[0].length % 3,
            rupiah     = split[0].substr(0, sisa),
            ribuan     = split[0].substr(sisa).match(/\d{3}/gi);
           
        if (ribuan) {
            separator = sisa ? '.' : '';
            rupiah += separator + ribuan.join('.');
        }
       
        rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
        return prefix == undefined ? rupiah : (rupiah ? 'Rp. ' + rupiah : '');
    }



</script>
<script>
$(document).ready(function(){
$('.hasil').hide();
$('#tombol').hide();

$('#aro').change(function(){
if($('#aro').val()=='ARO'){
$('.hasil').hide();
$('#tombol').hide();
$('#mitra').hide();

}else if($('#aro').val()=='BREAK'){
$('.hasil').hide();
$('#tombol').hide();
$('#mitra').hide();

}else if($('#aro').val()=='ARO-ARO'){
$('.hasil').show();
$('#tombol').show();

}
});
});
</script>
    </body>
</html>
