<?php require_once __DIR__ . '/../php8_compat.php'; 
session_start();
 include "../conn2.php";
 include "../function.php";
  $koneksi=open_connection2();
$userid=$_SESSION['userid'];
$data = mysql_query ("select kode_branch from user where userid ='$userid'");
$r = mysql_fetch_array($data);
$unit=$r['kode_branch'];

$data_reg = mysql_query ("select code_region from branch where unit	='$unit'");
$re = mysql_fetch_array($data_reg);
$reg=$re['code_region'];

//cek apakah user sudah login
if(!isset($_SESSION['userid'])){
   header("location:../index.html");//jika belum login jangan lanjut..
}

//cek level user
if($_SESSION['level']!="dwh"){
     header("location:../index.html");//jika bukan user jangan lanjut
}



 ?>
<!DOCTYPE html>
<html lang="en">
 <?php include "head.php"; ?>

  <body>

  <section id="container" >
      <!-- **********************************************************************************************************************************************************
      TOP BAR CONTENT & NOTIFICATIONS
      *********************************************************************************************************************************************************** -->
      <!--header start-->
      <?php include "header.php"; ?>
      <!--header end-->
      
      <!-- **********************************************************************************************************************************************************
      MAIN SIDEBAR MENU
      *********************************************************************************************************************************************************** -->
      <!--sidebar start-->
      <aside>
          <div id="sidebar"  class="nav-collapse ">
              <!-- sidebar menu start-->
              <ul class="sidebar-menu" id="nav-accordion">
              
              	  <p class="centered"><a href="profile.html"><img src="assets/img/ui-sam.jpg" class="img-circle" width="60" style="border: 3px solid white;"/></a></p>
              	  <h5 class="centered">
                  Import Data Excel
              </h5>
              	  	
                  <?php include 'menu.php'; ?>

              </ul>
              <!-- sidebar menu end-->
          </div>
      </aside>
      <!--sidebar end-->
      
      <!-- **********************************************************************************************************************************************************
      MAIN CONTENT
      *********************************************************************************************************************************************************** -->
      <!--main content start-->
      <section id="main-content">
          <section class="wrapper">
          	<h3><i class="fa fa-angle-right"></i> DWH &raquo; Import Data</h3><br /><br />
          	
              <!-- BASIC FORM ELELEMNTS -->
          	<div class="row mt">
          		<div class="col-lg-12">
                    <div class="panel panel-success">
                        <div class="panel-heading">
                        <h3 class="panel-title"><i class="fa fa-user"></i> Import Data</h3> 
                        </div>
                        
                        <div class="panel-body">
                        <div class="alert alert-info alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</div>
                        <?php
//koneksi ke database, username,password  dan namadatabase menyesuaikan 
//mysql_connect('localhost', 'username', 'password');
//mysql_select_db('namadatabase');
 
//memanggil file excel_reader
require "excel_reader.php";

if (isset($_POST['submit'])) {
?>
<div id="progress" style="width:500px;border:1px solid #ccc;"></div>
<div id="info"></div>
<?php
}
?>
 

<?php
//jika tombol import ditekan
if(isset($_POST['submit'])){
 
    $target = basename($_FILES['filepegawaiall']['name']) ;
    move_uploaded_file($_FILES['filepegawaiall']['tmp_name'], $target);
    
    $data = new Spreadsheet_Excel_Reader($_FILES['filepegawaiall']['name'],false);
    
//    menghitung jumlah baris file xls
    $baris = $data->rowcount($sheet_index=0);
    
//    jika kosongkan data dicentang jalankan kode berikut
    $drop = isset( $_POST["drop"] ) ? $_POST["drop"] : 0 ;
    if($drop == 1){
//             kosongkan tabel pegawai
             $truncate ="delete from import_unit where unit ='$unit'";
             mysql_query($truncate);
    };
    
//    import data excel mulai baris ke-2 (karena tabel xls ada header pada baris 1)
    for ($i=13; $i<=$baris; $i++)
    {

 $barisreal = $baris-1;
        $k = $i-1;
        
// menghitung persentase progress
        $percent = intval($k/$barisreal * 100)."%";
 
// mengupdate progress
        echo '<script language="javascript">
        document.getElementById("progress").innerHTML="<div style=\"width:'.$percent.';  background-color:lightblue\">&nbsp;</div>";
        document.getElementById("info").innerHTML="'.$k.' data berhasil diinsert ('.$percent.' selesai).";
        </script>';
//       membaca data (kolom ke-1 sd terakhir)
   
	  $no            	= $data->val($i, 1);
      $norek      		= $data->val($i, 2);
	  $norek1      		= $data->val($i, 3);
	  $norek2     		= $data->val($i, 4);
	  $norek3      		= $data->val($i, 5);
	  $norek4      		= $data->val($i, 6);
      $alamat			= $data->val($i, 7);
	  $norek5      		= $data->val($i, 8);
      $pla    			= $data->val($i, 9);
	  $norek6      		= $data->val($i, 10);
      $margin    		= $data->val($i, 11);
      $tgl_pk  			= $data->val($i, 12);
      $jatpo  			= $data->val($i, 13);
      $jw  				= $data->val($i, 14);
      $m  				= $data->val($i, 15);
      $os  				= $data->val($i, 16);
      $saldo_margin  	= $data->val($i, 18);
	  $ft  				= $data->val($i, 20);
      $tunggak_pokok  	= $data->val($i, 21);
	  $ftb      		= $data->val($i, 22);
      $tunggak_margin  	= $data->val($i, 23);
      $gol  			= $data->val($i, 24);
     $ambilao=substr($norek,4,2);
	$tahun = substr($tgl_pk, 6, 2);
	$bulan = substr($tgl_pk, 3, 2);
	$tgl   = substr($tgl_pk, 0, 2);
	$tglpk="20$tahun-$bulan-$tgl";
	$ao="$unit$ambilao";

	$tahun_tempo = substr($jatpo, 6, 2);
	$bulan_tempo = substr($jatpo, 3, 2);
	$tgl_tempo   = substr($jatpo, 0, 2);
	$jatpoo="20$tahun_tempo-$bulan_tempo-$tgl_tempo";
	$buss_date=date('ymd');
	$tahun_tempo = substr($jatpo, 6, 2);
	$bulan_tempo = substr($jatpo, 3, 2);
	$year = substr($buss_date, 0, 2);
	$month_data = substr($buss_date, 2, 2);
	$year_data="20$year";
	 //$plaa= str_replace('.', '', $pla);
 
//      setelah data dibaca, masukkan ke tabel  sql
      $query = "REPLACE INTO import_unit (no,alamat,pla,margin,tgl_pk,jatuh_tempo,jw,saldo_pinjaman,saldo_margin,frek,tunggkan_pokok,tunggakan_margin,golongan,ft_brek,unit,tgl_cair,reg,buss_date,ao,month_data,year_data) VALUES 
                      ('$norek','$alamat','$pla','$margin','$tglpk','$jatpoo','$jw','$os','$saldo_margin','$ft','$tunggak_pokok','$tunggak_margin','$gol','$ftb','$unit','$tglpk','$reg','$buss_date','$ao','$month_data','$year_data')";
      $hasil = mysql_query($query);
	  
	 $query = "delete from import_unit where pla='0' and unit ='$unit' ";
	 
      $hasil = mysql_query($query);
    }
    
    if(!$hasil){
//          jika import gagal
          die(mysql_error());
      }else{
//          jika impor berhasil
          echo '<div class="alert alert-success alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>Data berhasil di import</div>';
    }
    
//    hapus file xls yang udah dibaca
    unlink($_FILES['filepegawaiall']['name']);
}
 
?>
 
<form name="myForm" id="myForm" onSubmit="return validateForm()" action="importxls.php" method="post" enctype="multipart/form-data">
    <input type="file" id="filepegawaiall" class="form-control" name="filepegawaiall" required /><br />
    <input type="submit" name="submit" class="brn btn-sm btn-success" value="Import" /><br/>
    <label><input type="checkbox" name="drop" value="1" /> <u>Kosongkan tabel data sebelumnya.</u> </label>
</form>
 
<script type="text/javascript">
//    validasi form (hanya file .xls yang diijinkan)
    function validateForm()
    {
        function hasExtension(inputID, exts) {
            var fileName = document.getElementById(inputID).value;
            return (new RegExp('(' + exts.join('|').replace(/\./g, '\\.') + ')$')).test(fileName);
        }
 
        if(!hasExtension('filepegawaiall', ['.xls'])){
            alert("Hanya file XLS (Excel 2003) yang diijinkan.");
            return false;
        }
    }
</script>
                        
              </div> 
              </div>
            </div><!-- col-lg-12-->      	
          	</div><!-- /row -->
          	
          	
		</section><! --/wrapper -->
      </section><!-- /MAIN CONTENT -->

      <!--main content end-->
      <!--footer start-->
      <?php include "footer.php"; ?>
      <!--footer end-->
  </section>

    <!-- js placed at the end of the document so the pages load faster -->
    <script src="assets/js/jquery.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
    <script class="include" type="text/javascript" src="assets/js/jquery.dcjqaccordion.2.7.js"></script>
    <script src="assets/js/jquery.scrollTo.min.js"></script>
    <script src="assets/js/jquery.nicescroll.js" type="text/javascript"></script>


    <!--common script for all pages-->
    <script src="assets/js/common-scripts.js"></script>

    <!--script for this page-->
    <script src="assets/js/jquery-ui-1.9.2.custom.min.js"></script>

	<!--custom switch-->
	<script src="assets/js/bootstrap-switch.js"></script>
	
	<!--custom tagsinput-->
	<script src="assets/js/jquery.tagsinput.js"></script>
	
	<!--custom checkbox & radio-->
	
	<script type="text/javascript" src="assets/js/bootstrap-datepicker/js/bootstrap-datepicker.js"></script>
	<script type="text/javascript" src="assets/js/bootstrap-daterangepicker/date.js"></script>
	<script type="text/javascript" src="assets/js/bootstrap-daterangepicker/daterangepicker.js"></script>
	
	<script type="text/javascript" src="assets/js/bootstrap-inputmask/bootstrap-inputmask.min.js"></script>
	
	
	<script src="assets/js/form-component.js"></script>    
    
    
  <script>
      //custom select box

      $(function(){
          $('select.styled').customSelect();
      });

  </script>

  </body>
</html>
