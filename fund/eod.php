<?php require_once __DIR__ . '/../php8_compat.php'; 
session_start();
$userid=$_SESSION['userid'];

include "../encryption/function.php";
     
    //untuk waktu
    $tanggal=date("y-m-d");
     
    //untuk koneksi database
    include "../conn2.php";
    $koneksi=open_connection2();
    $user=mysql_query("select kode_branch from user where userid='$userid'");
             
            while($id_user=mysql_fetch_array($user)){
             
                $unit=$id_user['kode_branch'];
            }
    ?>
    <?php
    if(isset($_POST['hitung_shu'])){
        $tgl=$_POST['tgl'];

        
        $day = date('D', strtotime($tgl));
        $dayList = array(
            'Sun' => 'Minggu',
            'Mon' => 'Senin',
            'Tue' => 'Selasa',
            'Wed' => 'Rabu',
            'Thu' => 'Kamis',
            'Fri' => 'Jumat',
            'Sat' => 'Sabtu'
        );

        $tgl_akhir_bulan=date('t');
        $tgl_hari_ini=date('d');

       
        $sabtu = date('Y-m-d', strtotime('+1 days', strtotime($tgl)));
       
        $minggu = date('Y-m-d', strtotime('+2 days', strtotime($tgl)));
        $senin = date('Y-m-d', strtotime('+3 days', strtotime($tgl)));


       $sekarang=substr($tgl,5,2);
   

        if($dayList[$day]=='Jumat' AND $tgl_akhir_bulan != $sekarang ){
          
        $update=mysql_query ("UPDATE deposito set code_bulan ='1'");
        $update=mysql_query ("UPDATE deposito_detail set tgl_jatuh_tempo='$senin', tgl_bayar='$senin' where tgl_jatuh_tempo in ('$sabtu','$minggu')");

         
        }else{
            
        $update=mysql_query ("UPDATE deposito set code_bulan ='1'");

        
        }


        $akhir =date('Y-m-d',strtotime('last day of this month'));
        $hari = date('D', strtotime($akhir));
        $listhari = array(
            'Sun' => 'Minggu',
            'Mon' => 'Senin',
            'Tue' => 'Selasa',
            'Wed' => 'Rabu',
            'Thu' => 'Kamis',
            'Fri' => 'Jumat',
            'Sat' => 'Sabtu'
        );
        

        if($listhari[$hari]=='Sabtu'){
            $jumat_sabtu = date('Y-m-d', strtotime('-1 days', strtotime($akhir)));

        $update=mysql_query ("UPDATE deposito_detail set tgl_jatuh_tempo='$jumat_sabtu', tgl_bayar='$jumat_sabtu' where tgl_jatuh_tempo ='$akhir'");
        }elseif($listhari[$hari]=='Minggu'){
            $jumat_minggu = date('Y-m-d', strtotime('-2 days', strtotime($akhir)));
            $update=mysql_query ("UPDATE deposito_detail set tgl_jatuh_tempo='$jumat_minggu', tgl_bayar='$jumat_minggu' where tgl_jatuh_tempo ='$akhir'");

        }
    

 
 


 
    echo "('Porses update Tgl Bayar Sabtu Minggu dan Akhir bulan selesai ')";
 
        if($update){
            //1. Tahap Penginputan

        
            echo "<script> alert ('Proses EOD Deposito Selesai ')</script>";
            echo "<script language='javascript'>document.location.href='eod.php'</script>";
	
                }else{
            echo mysql_error();
        }
                             
    }else{
        unset($_POST['hitung_shu']);
    }
    ?>
     
 
 
      <link rel="stylesheet" type="text/css" href="../css/bootstrap.min.css">
 
 <script type="text/javascript" src="../js/jquery.min.js"></script>
 <script type="text/javascript" src="../js/bootstrap.min.js"></script>
 
     
    <div class="post">
      <div class="entry">
            <h2 align="center"><strong>Proses EOD</strong></h2>
            <p align="center">&nbsp;</p>
            <p align="center">
            <p align="center" class="style1"><font color="#666666">
            
            <p align="center"><em><font color="#666666">Proses bisa dilakukan setelah semua data diposting.
            <?php 
            $cek=mysql_query("select * from tabel_transaksi where keterangan_posting='' and unit ='$unit'");
            $cek_posting=mysql_num_rows($cek);
            if($cek_posting!==0){
                //echo "masih ada yang belum di posting";
            }else{
                ?>
</font></em></p>
            
          
           
            </font></span></p>
            <form action="?<?php echo paramEncrypt('page=../laporan/hitung_shu')?>" method="post" name="postform" id ="myform">
                <div align="center">
                <p>&nbsp;</p><p>
                <font color="#666666">
				 <p align="center"><span class="style1"><font color="#666666">
              
			 <div class="control-group">
			  <div class="input-group-addon">
           <span class="glyphicon glyphicon-th"></span>
       
                                            <label class="control-label" for="basicinput">Tanggal Transaksi</label>
                                            <div class="controls">
                                                <input type="text" name="tgl" id="tgl" class="input-group date form-control span8 tip" type="text"  date="" placeholder="yyyy/mm/dd" required>
                                            </div>
                                        </div>
			  </div>
			  <div class="control-group">
                <input type="submit" onclick="return confirm('Apakah Anda Yakin?')" name="hitung_shu" value="Proses" class="btn btn-primary" />
                 </div>
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
            echo '<div class="alert alert-success alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><strong>Sukses </strong> SHU berhasil di Hitung </div>';
 
            }
            ?>          
            </font>
            </p>
            </div>
      </div>
    </div>
     <script src="../js/bootstrap-datepicker.js"></script>
        <script>
    //options method for call datepicker
    $(".input-group.date").datepicker({ autoclose: true, todayHighlight: true, format: 'yyyy-mm-dd', });
   
   
    </script>