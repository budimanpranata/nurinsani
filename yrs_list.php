<?php require_once __DIR__ . '/php8_compat.php';
session_start();
ob_start();
$userid=$_SESSION['userid'];
$code_kel=$_GET['code_kel'];
//cek apakah user sudah login
if(!isset($_SESSION['userid'])){
   header("location:index.html");
}

//cek level user
if($_SESSION['level']!="unit"){
   header("location:index.html");
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
include "indo_tgl.php";
$koneksi=open_connection2(); 
//ambil data dari database
$result = mysql_query(" Select kode_branch from user where userid='$userid'");
while($row = mysql_fetch_array($result)){
$unit=$row['kode_branch'];
}
	
$query = "Select loan.no_anggota,loan.cif,loan.unit,Plafond,angsuran,ijaroh,bagi_hasil,bulat,pokok,loan.Cust_Short_name,kelompok.nama_kel,run_tenor from loan
left join anggota on loan.cif = anggota.cif 
left join kelompok on kelompok.code_kel = loan.code_kel
Where kelompok.code_kel='$code_kel' and loan.unit='$unit' and os > 0 group by loan.cif ";
$result = mysql_query($query);
?>
<form action="realisasi_tag.php" method="post">
    <table border="1" style="margin: 10px 0px" class="table table-striped" >
            <tr>
               <th><span class="badge"> Pilih</th>
<th><span class="badge"> No</th>
               <th><span class="badge"> No Anggota</th>
               <th><span class="badge">Nama</th>
               <th><span class="badge">Unit</th>
			   <th><span class="badge">Kelompok</th>
			   <th><span class="badge">Pembiayaan</th>
			   <th><span class="badge">Margin</th>
			   <th><span class="badge">Setoran</th>
<th><span class="badge">Angsuran</th>
<th><span class="badge">TWM</th>
			   <th><span class="badge">Saldo TWM</th>
				<th><span class="badge">Sisa TWM</th>
								
			            </tr>
    <?php 
$no=0;
    while ($data = mysql_fetch_array($result)) {
        $cif =$data['cif'];
        $bulat =$data['bulat'];
        $angsuran =$data['angsuran'];
        $minggon = $bulat-$angsuran;
        
        $run_tenor=$data['run_tenor'];
        $total_twm = $minggon * $run_tenor;
      
        $row_sal= mysql_fetch_array(mysql_query("select(sum(KREDIT) - sum(DEBIT))as twm from simpanan WHERE type not in ('05','04') and cif ='$cif'")) or die(mysql_error());
        $twm=$row_sal['twm'];
        $saldo_twm=$twm-$total_twm;

        

$no ++;
    ?>
            <tr>
                <td><input type="checkbox" id="cekbox" name="cekbox[]" value="<?php echo $data['no_anggota'] ?>"/></td>
 <td><?php echo $no ?></td>               
 <td><?php echo $data['cif'] ?></td>
				<td><?php echo $data['Cust_Short_name'] ?></td>
                <td><?php echo $data['unit'] ?></td>
				<td><?php echo $data['nama_kel']?></td>
				<td><?php echo number_format (compat_num($data['Plafond'])) ?></td>
				<td><?php echo number_format (compat_num($data['bagi_hasil'])) ?></td>
				<td><?php echo number_format (compat_num($data['bulat'])) ?></td>
				<td><?php echo number_format (compat_num($data['angsuran'])) ?></td>
             	<td><?php echo number_format (compat_num($total_twm)) ?></td>
                 <td><?php echo number_format (compat_num($twm)) ?></td>
				<td><?php echo number_format (compat_num($saldo_twm))?></td>
				
            </tr>
    
    <?php
    
	}

    ?>
<?php
       
        $total=mysql_query("select sum(Plafond) as tot_pla,sum(bagi_hasil) as margin,sum(angsuran) as angsuran,sum(pokok) as pokok,sum(bulat) as bulat,sum(ijaroh) as ijaroh from loan where code_kel ='$code_kel' and unit ='$unit' and status_app in ('MURAB','REST','COVID') and os  > 0 group by code_kel ");
        while($row_tran=mysql_fetch_array($total)){
            $tot_pla=$row_tran['tot_pla'];
            $margin=$row_tran['margin'];
            $angsur=$row_tran['angsuran'];
$bulat=$row_tran['bulat'];
$pokok=$row_tran['pokok'];
$ijaroh=$row_tran['ijaroh'];
$tabung=$angsuran=$row_tran['bulat']-$row_tran['angsuran'];

        }
            ?>
    <tr>
                <td colspan="6"><div align="center"><strong>TOTAL TRANSAKSI</strong></div></td>
                <td align="right"><strong><?php echo number_format(compat_num($tot_pla)); ?></strong></td>
                <td align="right"><strong><?php echo number_format(compat_num($margin)); ?></strong></td>
                <td align="right"><strong><?php echo number_format(compat_num($bulat)); ?></strong></td>
                <td align="right"><strong><?php echo number_format(compat_num($angsur)); ?></strong></td>
                <td align="right"><strong><?php echo number_format(compat_num($pokok)); ?></strong></td>
<td align="right"><strong><?php echo number_format(compat_num($ijaroh)); ?></strong></td>

            </tr>

    </table>
    <br />
    <input type="button" class="alert-success" onclick="cek(this.form.cekbox)" value="Select All" />
    <input type="button" class="btn-danger" onclick="uncek(this.form.cekbox)" value="Clear All" />
    <input name="submit" type="submit" class="btn-success" value="Realisasi Tagihan" />
		
	    <br />
		
</form>
