<?php require_once __DIR__ . '/../../php8_compat.php'; 
session_start();
ob_start();
        $userid=$_SESSION['userid'];
        if(!isset($_SESSION['userid'])){
   header("location:../index.html");
 exit; }

include "../conn.php"; 

?>
<!DOCTYPE html>
<html lang="en">

    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Data Tansaksi Simpanan Nur Insani</title>
        <!-- css table datatables/dataTables -->
	<link rel="stylesheet" href="../datatables/dataTables.bootstrap.css"/>
    
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
                        <div class="content">
                            <?php
             if(isset($_GET['hal']) == 'hapus'){
				$kd_dept = $_GET['kd'];
				$cek = db_query($koneksi, "SELECT * FROM deposito WHERE norek='$kd_dept'");
				if(db_num_rows($cek) == 0){
					echo '<div class="alert alert-success alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button> Data tidak ditemukan.</div>';
				}else{
					$delete = db_query($koneksi, "insert into dep_break select * FROM deposito WHERE norek='$kd_dept'");
					if($delete){
						echo '<div class="alert alert-primary alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button> Data berhasil Proses, Proses berikutnya lakukan Break di menu Break.</div>';
					}else{
						echo '<div class="alert alert-danger alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button> Proses Break Gagal.</div>';
					}
				}
			}
			?>
                        <div class="panel panel-default">
                        <div class="panel-heading">
                        <h3 class="panel-title"><i class="icon-user"></i> Data Tansaksi Simpanan Nur Insani</h3> 
                        </div>
                        <div class="panel-body">
                                    <table id="lookup" class="table table-bordered table-hover">  
	                                   <thead bgcolor="#eeeeee" align="center">
                                        <tr>
	  
                                        <th>Unit</th>
	                                    <th>Nomor Rekening </th>
										<th>No. Bilyet </th>
                                        <th>Nama </th>
                                        <th>Simpanan</th>
                                        <th>Tenor </th>
                                        <th>Nisbah </th>
                                        <th>Rate </th>
                                        <th>Cara Hitung </th>
					                    <th>Start Date</th>
	                                <th>Maturity Date</th>
					<th>aksi</th>
	                                   
	  
                                       </tr>
                                      </thead>
                                        <tbody>
                                        </tbody>
                                    </table>
                                    <div class="pull-right">
                            <a href="../transaksi/input_trns.php" class="btn btn-sm btn-primary">+ Data transaksi</a>

                            </div>
                                </div>
                            </div>
                            
                        </div>
                        <!--/.content-->
                    </div>
                    <!--/.span9-->
                </div>
            </div>
            <!--/.container-->
        
        <!--/.wrapper--><br />
       
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
      
    </body>
