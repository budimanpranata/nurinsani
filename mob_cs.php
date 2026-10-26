<?php require_once __DIR__ . '/php8_compat.php'; 
session_start();
ob_start();
        $userid=$_SESSION['userid'];
        if(!isset($_SESSION['userid'])){
   header("location:../index.html");
}

include "conn.php"; 

?>
<!DOCTYPE html>
<html lang="en">

    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Data Tansaksi Simpanan Nur Insani</title>
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
                        <div class="content">
                          
                        <div class="panel panel-default">
                        <div class="panel-heading">
                        <h3 class="panel-title"><i class="icon-user"></i> Data Transaksi CS Mobile Collection</h3> 
                        </div>
                                   
                        <div class="panel-body">
                                    <table id="lookup" class="table table-bordered table-hover">  
	                                   <thead bgcolor="#eeeeee" align="center">
                                        <tr>
	  
                                        <th>Unit</th>
                                        <th>Tgl Tagih</th>
	                                    <th>Kelompok </th>
										<th>CIF</th>
                                        <th>Nama </th>
                                        <th>Tagihan</th>
                                        <th>Nyata </th>
                                        <th>Status Transaksi </th>
										<th>Aksi </th>
					
	                                   
	  
                                       </tr>
                                      </thead>
                                        <tbody>
                                        </tbody>
                                    </table>
                                    <div class="pull-right">
                            <a href="mob_export_cs.php" class="btn btn-sm btn-primary">Excel Cs</a>
			<a href="mob_export_penarikan.php" class="btn btn-sm btn-primary">Excel Penarikan</a>
<a href="mob_export_lebaran.php" class="btn btn-sm btn-primary">Excel Lebaran</a>
<a href="mob_export_lima.php" class="btn btn-sm btn-primary">Excel Lima%</a>
<a href="mob_export_pelunasan.php" class="btn btn-sm btn-primary">Excel Pelunasan</a>
<a href="mob_export_tunggakan.php" class="btn btn-sm btn-primary">Excel Tunggakan</a>
<a href="mob_export_wo.php" class="btn btn-sm btn-primary">Excel WO</a>
<a href="mob_export_omzet.php" class="btn btn-sm btn-primary">Excel Omzet</a>
			
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
      
    </body>
