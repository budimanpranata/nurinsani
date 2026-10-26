<?php require_once __DIR__ . '/../../koneksi_db.php'; error_reporting(0); // tambahkan untuk menghilangkan notice... hehe 
 include "../../function.php";
?>
<!doctype html>
<html>
    <head>
         <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Data Deposan Nur Insani</title>
        <!-- css table datatables/dataTables -->
	<link rel="stylesheet" href="../datatables/dataTables.bootstrap.css"/>
    
         <link rel="stylesheet" href="bootstrap.min.css"/>
        <link type="text/css" href="../bootstrap/css/bootstrap-responsive.min.css" rel="stylesheet">
        <link type="text/css" href="../css/theme.css" rel="stylesheet">
        <link type="text/css" href="../images/icons/css/font-awesome.css" rel="stylesheet">
        <link type="text/css" href='http://fonts.googleapis.com/css?family=Open+Sans:400italic,600italic,400,600'
            rel='stylesheet'>
            
            
            
        <script src="../scripts/jquery-1.9.1.min.js" type="text/javascript"></script>
        <script src="../scripts/jquery-ui-1.10.1.custom.min.js" type="text/javascript"></script>
        <style>
            /*fix margin pagination*/
            .pagination{
                margin-top: 0px;
            }
        </style>
    </head>
    <body>
	 <div class="container" >
                <div class="row">
                    <div class="span12">
                        <div class="content">
        <?php 
//        includekan fungsi paginasi
        include 'pagination1.php';
//        koneksi ke database
        $koneksi = db_ni();
        $db = mysql_select_db('nur_insani');
        
//        mengatur variabel reload dan sql
        if(isset($_REQUEST['keyword']) && $_REQUEST['keyword']<>""){
//        jika ada kata kunci pencarian (artinya form pencarian disubmit dan tidak kosong)
//        pakai ini
            $keyword=$_REQUEST['keyword'];
            $reload = "view_cetak_bilyet.php?pagination=true&keyword=$keyword";
            $sql =  "SELECT norek,CUST_SHORT_NAME,nominal,tenor,maturity_date,deposito.cif,aro FROM deposito join deposan on deposan.cif = deposito.cif where status_print ='OK' and deposito.cif LIKE '%$keyword% or status_print ='OK' and deposito.CUST_SHORT_NAME LIKE '%$keyword%''";
            $result = mysql_query($sql);
        }else{
//            jika tidak ada pencarian pakai ini
            $reload = "view_cetak_bilyet.php?pagination=true";
            $sql =  "SELECT norek,CUST_SHORT_NAME,nominal,tenor,maturity_date,deposito.cif,aro FROM deposito join deposan on deposan.CIF = deposito.cif where status_print ='OK'";
            $result = mysql_query($sql);
        }
        
        //pagination config start
        $rpp = 10; // jumlah record per halaman
        $page = intval($_GET["page"]);
        if($page<=0) $page = 1;  
        $tcount = mysql_num_rows($result);
        $tpages = ($tcount) ? ceil(compat_num($tcount/$rpp)) : 1; // total pages, last page number
        $count = 0;
        $i = ($page-1)*$rpp;
        $no_urut = ($page-1)*$rpp;
        //pagination config end
        ?>
	<div class="panel panel-default">
		<div class="panel-heading">
                        <h3 class="panel-title"><i class="icon-user"></i> Data Bilyet Nur Insani</h3> 
                        </div>
						<div class="panel-body">
       
            
			<div class="row" >
			
                <div class="col-lg-8">
                    <!--muncul jika ada pencarian (tombol reset pencarian)-->
                    <?php
                    if($_REQUEST['keyword']<>""){
                    ?>
                        <a class="btn btn-default btn-outline" href="view_break.php"> Reset Pencarian</a>
                    <?php
                    }
                    ?>
                </div>
                <div class="col-lg-4 text-right">
                    <form method="post" action="view_break.php">
                        <div class="form-group input-group">
                            <input type="text" name="keyword" class="form-control" placeholder="Search..." value="<?php echo $_REQUEST['keyword']; ?>">
                            <span class="input-group-btn">
                                <button class="btn btn-primary" type="submit">Cari
                                </button>
                            </span>
                        </div>
                    </form>
                </div>
            </div>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>#</th>
						<th>NO TRANSAKSI</th>
                        <th>Cif</th>
						<th>Nama</th>
						  <th>Nominal</th>
						   <th>Tenor</th>
						    <th>Jatuh Tempo	</th>
							<th>Status Aro</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    while(($count<$rpp) && ($i<$tcount)) {
                        mysql_data_seek($result,$i);
                        $data = mysql_fetch_array($result);
                    ?>
                    <tr>
                        <td width="80px">
                            <?php echo ++$no_urut;?> 
                        </td>
						 <td>
                            <?php echo $data ['norek']; ?> 
                        </td>

                        <td>
                            <?php echo $data ['cif']; ?> 
                        </td>
						 <td>
                            <?php echo $data ['CUST_SHORT_NAME']; ?> 
                        </td>
						<td>
                            <?php echo $data ['nominal']; ?> 
                        </td>
							<td>
                            <?php echo $data ['tenor']; ?> 
                        </td>
							<td>	
                            <?php echo tgl_indo ($data ['maturity_date']); ?> 
                        </td>
						<td>	
                            <?php echo $data ['aro']; ?> 
                        </td>
                        <td width="120px" class="text-center">
												  
                            <a  href="cetak_bilyet.php?kd=<?php echo $data ['norek'] ?> " class="btn btn-success btn-lg"> 
							<span class="glyphicon glyphicon-print"></span> Print
							</a>
						
						</div>	
                        </td>
                    </tr>
                    <?php
                        $i++; 
                        $count++;
                    }
                    ?>
                </tbody>
            </table>
            <div class="row">
                <div class="col-md-8">
                    <?php echo paginate_one($reload, $page, $tpages); ?>
                </div>
                <div class="col-md-4 text-right">
                    <!--form ini akan brfungsi untuk mengirimkan query sql yang sekarang aktif-->
                    <!--sql diambil dari baris 28 atau 33, tergantung ada atau tidaknya kata kunci pencarian-->
                    <form action="export.php" method="post">
                        <input type="hidden" name="sql" value="<?php echo $sql ?>" >
                        <input type="submit" value="Export Excel" class="btn btn-default" >
                    </form>
                </div>
				</div>
              </div>
			    </div>
				  </div>
				    </div>
    </body>
</html>

