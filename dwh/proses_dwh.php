<?php require_once __DIR__ . '/../php8_compat.php';
session_start();
include "../conn2.php";
include "../function.php";
$koneksi=open_connection2();
$userid=$_SESSION['userid'];
$data = mysql_query ("select kode_branch from user where userid ='$userid'");
$r = mysql_fetch_array($data);
$branch=$r['kode_branch'];
//cek apakah user sudah login
if(!isset($_SESSION['userid'])){
   header("location:index.html");//jika belum login jangan lanjut..
 exit; }

//cek level user
if($_SESSION['level']!="dwh"){
     header("location:../index.html");//jika bukan user jangan lanjut
 exit; }


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
                  Proses ETL
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
          	<h3><i class="fa fa-angle-right"></i> DWH &raquo; ETL</h3><br /><br />
          	
              <!-- BASIC FORM ELELEMNTS -->
          	<div class="row mt">
          		<div class="col-lg-12">
                    <div class="panel panel-success">
                        <div class="panel-heading">
                        <h3 class="panel-title"><i class="fa fa-user"></i> ETL</h3> 
                        </div>
                        
                        <div class="panel-body">
                        <div class="alert alert-info alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</div>
                 
	<?php
	 
	if(isset($_POST['proses_dwh'])){
	
		///////////////////////// HITUNG SHU /////////////////////
		//Setiap OND OF YEAR
		mysql_query("INSERT INTO import select * from import_unit");
		mysql_query("delete from import_unit");

		$qos=mysql_query("SELECT  
import.unit,
nama_unit,
buss_date,
SUM(IF (month_data= '12' AND year_data='2015', saldo_pinjaman,0)) AS OS_2015,
SUM(IF (month_data= '2' AND year_data='2016', saldo_pinjaman,0)) AS OS,
SUM(IF (month_data= '2' AND year_data='2016', saldo_pinjaman,0))  - SUM(IF (month_data= '12' AND year_data='2015', saldo_pinjaman,0))  as GROWTH_OS_YTD,
SUM(IF (month_data= '12' AND year_data='2015', saldo_pinjaman,0)) AS OS_2015,
SUM(IF (month_data= '2' AND year_data='2016', saldo_pinjaman,0)) AS OS_2016,
SUM(IF (month_data= '2' AND year_data='2016', saldo_pinjaman,0))  - SUM(IF (month_data= '12' AND year_data='2015', saldo_pinjaman,0))  as GROWTH_OS_MTD,
count(IF (month_data= '12' AND year_data='2015', saldo_pinjaman,NULL)) AS NOA_2015,
count(IF (month_data= '2' AND year_data='2016', saldo_pinjaman,NULL)) AS NOA_2016,
count(IF (month_data= '2' AND year_data='2016', saldo_pinjaman,NULL))  - COUNT(IF (month_data= '12' AND year_data='2015', saldo_pinjaman,NULL))  as GROWTH_NOA_YTD,
count(IF (month_data= '12' AND year_data='2015', saldo_pinjaman,NULL)) AS NOA_2015,
count(IF (month_data= '2' AND year_data='2016', saldo_pinjaman,NULL)) AS NOA,
count(IF (month_data= '2' AND year_data='2016', saldo_pinjaman,NULL))  - COUNT(IF (month_data= '12' AND year_data='2015', saldo_pinjaman,NULL))  as GROWTH_NOA_MTD,
SUM(IF (MONTH(tgl_pk) BETWEEN '1'AND '2' AND YEAR(tgl_pk)='2016', pla,0)) AS Ytd_Disburse,
SUM(IF (MONTH(tgl_pk) = '2' AND YEAR(tgl_pk)='2016', pla,0)) AS Mtd_Disburse_2016,
SUM(IF (DAY(tgl_pk) = '29' AND MONTH(tgl_pk)='2' AND YEAR(tgl_pk)='2016', pla,0)) AS Dd_Disburse,
count(IF (MONTH(tgl_pk) BETWEEN '1'AND '1' AND YEAR(tgl_pk)='2016', pla,NULL)) AS Ytd_Disburse_Noa,
count(IF (MONTH(tgl_pk) = '2' AND YEAR(tgl_pk)='2016', pla,NULL)) AS Mtd_Disburse_Noa,
count(IF (DAY(tgl_pk) = '29' AND MONTH(tgl_pk)='2' AND YEAR(tgl_pk)='2016', pla,NULL)) AS Dd_Disburse_Noa,
SUM(IF (MONTH(tgl_pk) BETWEEN '1'AND '2' AND YEAR(tgl_pk)='2016', tunggkan_pokok,0)) AS Ytd_tunggak_pokok,
SUM(IF (MONTH(tgl_pk) BETWEEN '1'AND '2' AND YEAR(tgl_pk)='2016', tunggakan_margin,0)) AS Ytd_tunggak_margi,
SUM(IF (MONTH(tgl_pk) = '2' AND YEAR(tgl_pk)='2016', tunggkan_pokok,0)) AS Mtd_pokok,
SUM(IF (MONTH(tgl_pk) = '2' AND YEAR(tgl_pk)='2016', tunggakan_margin,0)) AS Mtd_margin
FROM import  
LEFT JOIN branch_d USING(unit)
where saldo_pinjaman >0
group By Unit");
while($data_dwh = mysql_fetch_array($qos)){
		$os_ytd=$data_dwh['GROWTH_OS_YTD'];
		$os_mtd=$data_dwh['GROWTH_OS_MTD'];
		$os_dd=$data_dwh['GROWTH_OS_DD'];
		$noa_ytd=$data_dwh['GROWTH_NOA_YTD'];
		$noa_mtd=$data_dwh['GROWTH_NOA_MTD'];
		$noa_dd=$data_dwh['GROWTH_NOA_DD'];
		$dis_ytd=$data_dwh['Ytd_Disburse_2016'];
		$dis_mtd=$data_dwh['Mtd_Disburse_2016'];
		$dis_dd=$data_dwh['Dd_Disburse_2016'];
		$noa_dis_ytd=$data_dwh['Ytd_Disburse_Noa'];
		$noa_dis_mtd=$data_dwh['Mtd_Disburse_Noa'];
		$noa_dis_dd=$data_dwh['Dd_Disburse_Noa'];
		$tunggkan_pokok_ytd=$data_dwh['Ytd_tunggak_pokok'];
		$tunggkan_pokok_mtd=$data_dwh['Mtd_pokok'];
		$tunggkan_pokok_dd=$data_dwh['Mtd_pokok'];
		$tunggkan_margin_ytd=$data_dwh['Ytd_tunggak_margi'];
		$tunggkan_margin_mtd=$data_dwh['Mtd_margin'];
		$tunggkan_margin_dd=$data_dwh['Mtd_margin'];
		$unit=$data_dwh['unit'];
		$OS=$data_dwh['OS'];
		$NOA=$data_dwh['NOA'];
		$ket=$data_dwh['nama_unit'];
		$buss_date=$data_dwh['buss_date'];
		
		//1. dissburse
		$berhasil=mysql_query("INSERT INTO dwh(ket,os,noa,ytd_os,mtd_os,dd_os,ytd_noa,mtd_noa,dd_noa,ytd_vol,mtd_vol,dd_vol,noa_ytd,noa_mtd,noa_dd,code_unit,buss_date)
		values('$ket','$OS','$NOA','$os_ytd','$os_mtd','$os_dd','$noa_ytd','$noa_mtd','$noa_dd','$dis_ytd','$dis_mtd','$dis_dd','$noa_dis_ytd','$noa_dis_mtd','$noa_dis_dd','$unit','$buss_date')");
};


			

							
	}else{
		unset($_POST['proses_dwh']);
	}
	?>
	
	<style type="text/css">
	<!--
	.style1 {font-style: italic}
	-->
	</style>

	  <link rel="stylesheet" type="text/css" href="assets/dari/bootstrap.min.css">

 <script type="text/javascript" src="assets/dari/jquery.min.js"></script>
 <script type="text/javascript" src="assets/dari/bootstrap.min.js"></script>

	
	<div class="post">
	  <div class="entry">
			<h2 align="center"><strong>Proses ETL</strong></h2>
			<p align="center">&nbsp;</p>
			<p align="center">
		
			<p align="center"><em><font color="#666666">Proses bisa dilakukan setelah semua nominative sudah di import.
			</br>
		    <?php 
			$cek=mysql_query("select import.unit from import left join branch on import.unit = branch.unit   group by unit");
			$cek_posting=mysql_num_rows($cek);
			if($cek_posting==0){
				echo "masih ada yang unit belum Import";
			}else{
				?>
				</font></em></p>
				<form action="proses_dwh.php" method="post" name="postform" id ="myform">
				<div align="center">
				<p>&nbsp;</p><p>
				<font color="#666666">
				<input type="submit" onclick="return confirm('Apakah Anda Yakin?')" name="proses_dwh" value="Proses" class="btn btn-primary" />
				
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
			echo '<div class="alert alert-success alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><strong>Sukses </strong> ETL Sukses </div>';

			}
			?>		  
	        </font>
	        </p>
            </div>
	  </div>
	</div>
	  
	</section><! --/wrapper -->
      </section><!-- /MAIN CONTENT -->

      <!--main content end-->
      <!--footer start-->
      <?php include "footer.php"; ?>
      <!--footer end-->
  </section>
</body>
</html>
