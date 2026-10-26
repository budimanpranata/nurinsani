<?php require_once __DIR__ . '/../php8_compat.php';
session_start();
ob_start();
$userid=$_SESSION['userid'];
$tanggal=$_GET['jatuh'];
//cek apakah user sudah login
if(!isset($_SESSION['userid'])){
   header("location:index.html");
}

//cek level user
if($_SESSION['level']!="fund"){
   header("location:../../index.html");
}  
?>
<link rel="stylesheet" href="css/bootstrap.min.css"/>
<script>
function cek(cekbox){
    for(i=0; i < cekbox.length; i++){
        cekbox[i].checked = true;
    }
}
function uncek(cekbox){
    for(i=0; i < cekbox.length; i++){
        cekbox[i].checked = false;
    }
}
</script>
<?php  

  
include "conn2.php";
include "../indo_tgl.php";
$koneksi=open_connection2(); 
//ambil data dari database
$result = mysql_query(" Select kode_branch from user where userid='$userid'");
while($row = mysql_fetch_array($result)){
$unit=$row['kode_branch'];
}
//ambil Jumlah hari dari param Financing
$perode_bulan=date("Ymd");
		 $tahun = substr($perode_bulan,0,4);
					$bulan=substr($perode_bulan,4,2);
					$code_bln_f="NI$tahun-$bulan";
	$query_jml_aset=mysql_fetch_array(mysql_query("select nomi_murab,marigin_murab,DATEDIFF(sd,dari) as hari from param_finan where code_param='NI2025-07'"));
	$tot_fin=$query_jml_aset['nomi_murab'];
	$margin=$query_jml_aset['marigin_murab'];
	$hari=$query_jml_aset['hari'];
$kalender =CAL_GREGORIAN;
$bulan =date('m');
$tahun =date('y');
$hari=cal_days_in_month($kalender,$bulan,$tahun);
$hari =30;

$date=date('Y-m-d');	
$query = " select CUST_SHORT_NAME,bilyet,id_deposito,norek,tgl_jatuh_tempo,aro,tenor,deposito.nominal as nomi,maturity_date,rasio,deposito.code_bulan,norek_tujuan,param_dep.nominal,margin_murab,last_payment from deposito 
inner join deposito_detail on deposito.norek=deposito_detail.id_deposito 
inner join deposan on deposito.cif=deposan.cif 
inner join param_dep on param_dep.code_bulan= deposito.code_bulan 
where deposito_detail.tgl_jatuh_tempo ='$tanggal' and  deposito_detail.id_deposito not in (select norek from dep_realisasi) group by norek";
$result = mysql_query($query);
?>
<form action="realisasi_dep.php" method="post">
    <table border="1" style="margin: 10px 0px" class="table table-striped" >
            <tr>
               <th><span class="badge"> Pilih</th>
               <th><span class="badge"> Norek</th>
               <th><span class="badge">Nama</th>
               <th><span class="badge">Bilyet</th>
			   <th><span class="badge">Status Aro</th>
			   <th><span class="badge">Deposito</th>
			   <th><span class="badge">Tgl Jatuh Tempo</th>
			   <th><span class="badge">Nisbah</th>
			
				
				
			            </tr>
    <?php 
    while ($data = mysql_fetch_array($result)) {
$last_payment=$data['last_payment'];
		$dt1 = new DateTime($last_payment);
		$dt2 = new DateTime($tanggal);
		$hari_stay =$dt2->diff($dt1)->days + 0;

	//if($hari_stay>31){
		//$hari=0;
	//}else{
		//$hari=$hari_stay;
	//}
$hari =$hari;
		$aro=$data['aro'];
		$norek=$data['id_deposito'];
		$tgl_jatuh_tempo=$data['tgl_jatuh_tempo'];
		$code_bulan=$data['code_bulan'];
		$jenis_simpanan=$data['jenis_simpanan'];
		$norek_tujuan=$data['norek_tujuan'];
		$nominal=$data['nominal'];
		$nomi=$data['nomi'];
		$margin_murab=$data['margin_murab'];
		$maturity_date=$data['maturity_date'];
		$tenor=$data['tenor'];
		$rasio=$data['rasio'];
		$param_rasio = mysql_query ("select rasio_invest,eq from param_dep where rasio_invest = '$rasio'");
		$param = mysql_fetch_array($param_rasio);
		$eq=$param['eq'];
		$rasio_invest=$param['rasio_invest'];

		$bayar=($nomi*$eq)*$hari/365;
    ?>
            <tr>
                <td><input type="checkbox" id="cekbox" name="cekbox[]" value="<?php echo $data['norek'] ?>"/></td>
                <td><?php echo $data['norek'] ?></td>
				<td><?php echo $data['CUST_SHORT_NAME'] ?></td>
				<td><?php echo $data['bilyet']?></td>
				<td><?php echo $data['aro'] ?></td>
				<td><?php echo number_format (compat_num($data['nomi'])) ?></td>
				<td><?php echo tgl_indo($data['tgl_jatuh_tempo']) ?></td>
             	<td><?php echo number_format (compat_num($bayar)) ?></td>
							
            </tr>
    <input type="hidden" name="jatuh_tempo" id="jatuh_tempo" value="<?php echo $tanggal; ?>">
    <?php
    
	}

    ?>
    </table>
    <br />
	
    <input type="button" class="alert-success" onclick="cek(this.form.cekbox)" value="Select All" />
    <input type="button" class="btn-danger" onclick="uncek(this.form.cekbox)" value="Clear All" />
    <input name="submit" type="submit" class="btn-success" value="Realisasi Deposito" />
		
	    <br />
		
</form>
