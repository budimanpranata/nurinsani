<?php require_once __DIR__ . '/../../php8_compat.php';
date_default_timezone_set('Asia/Jakarta');
session_start();
ob_start();
        $userid=$_SESSION['userid'];
        if(!isset($_SESSION['userid'])){
   header("location:../index.html");
 exit; }

$cif =$_GET['cif'];

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
        left: 190px;
        top:15px;
        margin: 26px 0px 0px 0px;
        width: 200px;
        padding:0px;
        background-color:#669900;
        border-top: 3px solid #669900;
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
                
                $jenis        = $_POST['jenis'];
                $tgl             = $_POST['tgl'];
				$pb= $_POST['pb'];
				$coa= $_POST['code'];
				$scif=$_POST['cif'];
				//var_dump($scif);
				//var_dump($cif);
                if ($jenis =='04'){
                    $sima="1010-3102000";
					$ket="SImpanan Pokok Anggota";
					$norek ="$scif$jenis";
                }elseif ($jenis =='05') {
                    $sima="1010-3202000";
					$ket="SImpanan Wajib Anggota";
					$norek ="$scif$jenis";
                }elseif ($jenis =='06') {
                    $sima="1010-3302000";
					$ket="SImpanan Khusus Anggota";
					$norek ="$scif$jenis";
				}else {
                    $sima="1010-2102000";
					$ket="SImpanan UMUM Anggota";
					$norek ="$scif$jenis";
                }
               
                  $nominal_as        = str_replace ('.','',$_POST['nominal']);
				
               
                
               
                
				if ($pb =='debet'){
					
				$jurnal=db_fetch_array(db_query($koneksi,"SELECT max(nomor_jurnal) FROM jurnal_umum where unit='1010' ORDER BY tanggal_selesai DESC"));
                    $nomor=$jurnal[0]+1;
                    $trns="BU/".$unit .$nomor;
                $keterangan_transaksi="$ket $norek";
                $query=db_query($koneksi,"insert into jurnal_umum(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values('$trns','$kode_transaksi','$tgl','$unit')");
                $deb_dep=db_query($koneksi,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                    values('$unit','$trns','$sima','$tgl','Bukti System','$keterangan_transaksi','$nominal_as','$userid')");
                $cr_dep=db_query($koneksi,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                    values('$unit','$trns','$coa','$tgl','Bukti System','$keterangan_transaksi','$nominal_as','$userid')");
                $insert= db_query($koneksi,"Insert into simpanan_kp(BUSS_DATE,SNOREK,SUNIT,SCIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,blok,tgl_input,kode_transaksi)VALUES('$tgl','$norek','$unit','$scif','$code_kel','$nominal_as','$jenis',0,'$userid','$keterangan_transaksi','','$cao','1','$tgl','$trns')");
				 
				

				}else {
					
					$jurnal=db_fetch_array(db_query($koneksi,"SELECT max(nomor_jurnal) FROM jurnal_umum where unit='$branch' ORDER BY tanggal_selesai DESC"));
                    $nomor=$jurnal[0]+1;
                    $trns="BU/".$unit .$nomor;
                $keterangan_transaksi="$ket $norek";
                $query=db_query($koneksi,"insert into jurnal_umum(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values('$trns','$kode_transaksi','$tgl','$unit')");
                
				$cr_dep=db_query($koneksi,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                    values('$unit','$trns','$coa','$tgl','Bukti System','$keterangan_transaksi','$nominal_as','$userid')");
					
				$deb_dep=db_query($koneksi,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                    values('$unit','$trns','$sima','$tgl','Bukti System','$keterangan_transaksi','$nominal_as','$userid')");

                $insert= db_query($koneksi,"Insert into simpanan_kp(BUSS_DATE,SNOREK,SUNIT,SCIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,blok,tgl_input,kode_transaksi)
				VALUES('$tgl','$norek','$unit','$scif','$code_kel',0,'$jenis','$nominal_as','$userid','$keterangan_transaksi','','$cao','1','$tgl','$trns')");

				
				
				}
				
                   
        if($insert){
          

                            echo '<div class="alert alert-success alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>Data Berhasil Di Simpan.</div>';
//header("location:../view/master_trans.php");
                        }else{
                            echo '<div class="alert alert-danger alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>Ups, Data Gagal Di simpan !</div>';
                        }
                }
            
            ?>
           
            <blockquote>
            Input Data Simpanan Nur Insani
            </blockquote>
                      <form name="form1" id="form1" class="form-horizontal row-fluid" action="input_trns.php" method="POST" >
                                        <div class="control-group">
                                            <label class="control-label" for="basicinput">Norek</label>
                                            <div class="controls">
                                                <input type="text" name="cif" id="cif" placeholder="Tidak perlu di isi" value="<?php echo $cif; ?>" class="form-control span8 tip" readonly="readonly">
                                            </div>
                                        </div>
                                        <div class="control-group">
                                            <label class="control-label" for="basicinput">Tanggal Transaksi</label>
                                            <div class="controls">
                                                <input type="text" name="tgl" id="tgl" class="input-group date form-control span8 tip" type="text"  date="" placeholder="yyyy/mm/dd" required>
                                            </div>
                                        </div>
                                        
                                        
                                       
                                        <div class="control-group">
                                        <label class="control-label" for="basicinput">Jenis PB</label>
                                            <div class="controls">
                                            <select name="pb" id ="pb" class="form-control span8 tip" required>
                                            <option value="">Pilih PB</option>
                                            <option value="debet">DEBET</option>
                                            <option value="kredit">KREDIT</option>
                                                                            
                                            </select>
                                        </div>
                                        </div>
                                         
										 <div id="suggest">
                                        <div class="control-group">
                                            <label class="control-label" for="basicinput">No GL</label>
                                        <div class="controls">
                                             <input type="text" onKeyUp="suggest(this.value);" name="code"  onBlur="fill2();" id="kode" size="15" placeholder="Rekning GL" class="form-control span8 tip" required> 
                                            <div class="suggestionsBox" id="suggestions" style="display: none;"> <img src="arrow.png" style="position: relative; top: -12px; left: 30px;" alt="upArrow">
                                            <div class="suggestionList" id="suggestionsList" >
                                            </div>
                                            </div>
                                            </div>
                                            </div>
                                            </div>
											
										<div class="control-group">
											<label class="control-label" for="basicinput">Nominal Simpanan</label>
                                            <div class="controls">
                                                <input type="text" name="nominal" id="nominal" placeholder="Nominal Simpanan" class="form-control span8 tip" required>
                                            </div>
										</div>
										
										
											
                                        <div class="control-group">
                                            <label class="control-label" for="basicinput">Jenis Simpanan</label>
                                            <div class="controls">
                                            <select name="jenis" id ="jenis" class="form-control span8 tip" required>
                                            <option value="">Pilih Simpanan</option>
                                            <option value="04">Pokok</option>
                                            <option value="05">Wajib</option>
                                            <option value="06">Khusus</option>
					    <option value="07">Umum</option>
											
                                
                                            </select>                                            
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
            $.post("../autosuggest_kp_kp.php", {queryString: ""+inputString+""}, function(data){
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

