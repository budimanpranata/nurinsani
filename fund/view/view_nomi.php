<?php require_once __DIR__ . '/../../koneksi_db.php'; error_reporting(0) // tambahkan untuk menghilangkan notice... hehe ?>
<!doctype html>
<html>
    <head>
        <title> </title>
        <link rel="stylesheet" href="bootstrap.min.css"/>
        <style>
            /*fix margin pagination*/
            .pagination{
                margin-top: 0px;
            }
        </style>
    </head>
    <body>
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
            $reload = "view_nomi.php?pagination=true&keyword=$keyword";
            $sql =  "SELECT deposito.norek,bilyet,CUST_SHORT_NAME,deposito.cif,nominal,tenor,maturity_date,rasio,star_date, sum(KREDIT)-sum(DEBIT) as saldo FROM deposito join deposan on deposan.CIF = deposito.cif 
			left join escrow_deposito on escrow_deposito.cif = deposito.cif
			where norek = '$keyword' group by norek";
            $result = mysql_query($sql);
        }else{
//            jika tidak ada pencarian pakai ini
            $reload = "view_nomi.php?pagination=true";
            $sql =  "SELECT deposito.norek,bilyet,CUST_SHORT_NAME,deposito.cif,nominal,tenor,maturity_date,rasio,star_date,sum(KREDIT)-sum(DEBIT) as saldo  FROM deposito join deposan on deposan.CIF = deposito.cif left join escrow_deposito on escrow_deposito.cif = deposito.cif group by norek";
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
        <div class="container" style="margin-top: 50px">
            <div class="row">
                <div class="col-lg-8">
                    <!--muncul jika ada pencarian (tombol reset pencarian)-->
                    <?php
                    if($_REQUEST['keyword']<>""){
                    ?>
                        <a class="btn btn-default btn-outline" href="view_nomi.php"> Reset Pencarian</a>
                    <?php
                    }
                    ?>
                </div>
                <div class="col-lg-4 text-right">
                    <form method="post" action="view_nomi.php">
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
                        <th>Norek</th>
<th>Bilyet</th>

						<th>Nama</th>
						  <th>Nominal</th>
						   <th>Tenor</th>
						    <th>Jatuh Tempo	</th>
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
                            <?php echo $data ['bilyet']; ?> 
                        </td>

						 <td>
                            <?php echo $data ['CUST_SHORT_NAME']; ?> 
                        </td>
						<td>
                            <?php echo number_format (compat_num($data ['nominal'])); ?> 
                        </td>
							<td>
                            <?php echo $data ['tenor']; ?> 
                        </td>
							<td>	
                            <?php echo $data ['maturity_date']; ?> 
                        </td>
                        <td width="120px" class="text-center">
                            <a href="#"> Edit</a> |
                            <a href="#">Delete</a>
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
    </body>
</html>

