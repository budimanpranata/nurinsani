<?php require_once __DIR__ . '/koneksi_db.php';
date_default_timezone_set('Asia/Jakarta');
session_start();
ob_start();
        $userid=$_SESSION['userid'];
        if(!isset($_SESSION['userid'])){
   header("location:../index.html");
 exit; }

$cif =$_GET['cif'];
$type =$_GET['type'];



$host_dua   ="185.201.9.210";
//$host_dua   ="192.168.88.144";
$user_dua ="adminni";
$password_dua ="";
$db_dua ="mobcol";

$koneksi =  db_mobcol();
if(mysqli_connect_errno())
{
echo'Gagal :'.mysqli_connect_error();
}


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
        <link rel="stylesheet" href="assets/datatables/dataTables.bootstrap.css"/>
       
         <link type="text/css" href="assets/css/bootstrap.css" rel="stylesheet">
        <link type="text/css" href="assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
        <link type="text/css" href="assets/bootstrap/css/bootstrap-responsive.min.css" rel="stylesheet">
        <link type="text/css" href="assets/css/theme.css" rel="stylesheet">
        <link type="text/css" href="assets/css/font-awesome.css" rel="stylesheet">
        <link type="text/css" href='http://fonts.googleapis.com/css?family=Open+Sans:400italic,600italic,400,600'
            rel='stylesheet'>
       
        <script src="assets/scripts/jquery-1.9.1.min.js" type="text/javascript"></script>
        <script src="assets/scripts/jquery-ui-1.10.1.custom.min.js" type="text/javascript"></script>
    </head>
    <body>
               

            <div class="container">
                <div class="row">
                    <div class="span12">
                        <div class="content"><div class="panel panel-default">
                        <div class="panel-heading">
                        <h3 class="panel-title"><i class="icon-user"></i> Data Mobile Collectio Nur Insani</h3>
                        </div>
                        <div class="panel-body">
                            <?php
            if(isset($_POST['input'])){
                
                $jenis        = $_POST['jenis'];
                $cif        = $_POST['cif'];
                $nominal       = str_replace ('.','',$_POST['nominal']);	
                var_dump($cif);		
                 
                $insert= db_query($koneksi,"update cs set nominal ='$nominal', status_trans='$jenis' where cif ='$cif'");
				
		
               
                
				
				
                   
        if($insert){
          

                            echo '<div class="alert alert-success alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>Data Berhasil Di Update.</div>';
//header("location:../view/master_trans.php");
                        }else{
                            echo '<div class="alert alert-danger alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>Ups, Data Gagal Di Update !</div>';
                        }
                }
            
            ?>
           
            <blockquote>
            Input Data Simpanan Nur Insani
            </blockquote>
                      <form name="form1" id="form1" class="form-horizontal row-fluid" action="mob_edit.php" method="POST" >
                                        <div class="control-group">
                                            <label class="control-label" for="basicinput">Nama</label>
                                            <div class="controls">
											<input type="text" name="tipe" id="tipe" placeholder="Tidak perlu di isi" value="<?php echo $type; ?>" class="form-control span8 tip" readonly="readonly">
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
											<label class="control-label" for="basicinput">Nominal</label>
                                            <div class="controls">
                                                <input type="text" name="nominal" id="nominal" placeholder="Nominal" class="form-control span8 tip" required>
                                            </div>
										</div>
										
										
											
                                        <div class="control-group">
                                            <label class="control-label" for="basicinput">Jenis Transaksi</label>
                                            <div class="controls">
                                            <select name="jenis" id ="jenis" class="form-control span8 tip" required>
                                            <option value="">Pilih Jenis</option>
                                            <option value="Bayar">Bayar</option>
                                            <option value="Titipan">Titipan</option>
                                           
											
                                
                                            </select>                                            
											</div>
                                        </div>   
                                   

                                        <div class="control-group">
                                            <div class="controls">
                                                <button type="submit" name="input" id="input" class="btn btn-sm btn-primary">Simpan</button>
                                               <a href="mob_edit.php" class="btn btn-sm btn-danger">Kembali</a>
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
        <script src="assets/js/bootstrap-datepicker.js"></script>
        <script>
    //options method for call datepicker
    $(".input-group.date").datepicker({ autoclose: true, todayHighlight: true, format: 'yyyy-mm-dd', });
   
   
    </script>
        <script src="assets/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
       
        <script src="assets/datatables/jquery.dataTables.js"></script>
        <script src="assets/datatables/dataTables.bootstrap.js"></script>
          <script>
        $(document).ready(function() {
                var dataTable = $('#lookup').DataTable( {
                    "processing": true,
                    "serverSide": true,
                    "ajax":{
                        url :"mob-ajax-grid-data_trns.php", // json datasource
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

    </body>
</html>

