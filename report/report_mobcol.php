<?php require_once __DIR__ . '/../koneksi_db.php';
session_start();
ob_start();
$userid=$_SESSION['userid'];
//cek apakah user sudah login
if(!isset($_SESSION['userid'])){
   header("location:index.html");
 exit; }

?>
<?php
// Tampilan Bootstrap 5 (2026-10-31, permintaan user): hanya HTML/CSS yang diganti.
// Kode PHP report (query ke database mobcol, sesi stgl_npl/jenis_npl) sengaja dibiarkan sama dengan versi lama.
require_once __DIR__ . '/../inc/tampilan.php';
halaman_awal('Report Mobcol');
?>
<div class="card card-ni">
  <div class="card-header">Kriteria Pencarian</div>
  <div class="card-body">
    <form method="post" action="<?php echo h($_SERVER['PHP_SELF']); ?>" name="postform" class="form-ni" autocomplete="off">
      <div class="row g-2 mb-2">
        <label for="tgl" class="col-sm-4 col-form-label">Tanggal Pencarian</label>
        <div class="col-sm-8"><input name="tgl" type="date" id="tgl" value="" class="form-control form-control-sm"></div>
      </div>
      <div class="row g-2 mb-3">
        <label for="select" class="col-sm-4 col-form-label">Jenis Report</label>
        <div class="col-sm-8">
          <select name="select" id="select" class="form-select form-select-sm">
            <option>Pilih Salah satu</option>
            <option value="01">CS</option>
            <option value="02">Non Cs Lima Persen</option>
            <option value="03">Non Cs Lebaran</option>
            <option value="04">Non Cs Penarikan</option>
            <option value="05">Non Cs Tunggakan</option>
            <option value="06">Non Cs Pelunasan</option>
            <option value="07">Non Cs WO</option>
            <option value="08">Omzet</option>
          </select>
        </div>
      </div>
      <div class="text-end">
        <button name="search" type="submit" id="submit2" value="Cari" class="btn btn-ni btn-sm px-4">Cari</button>
      </div>
    </form>
  </div>
</div>

<div class="table-responsive mt-3">
<table class="table table-sm table-bordered table-ni align-middle">
  <thead>
  <tr>
    <th>Unit</th>
    <th>Jenis Report</th>
    <th class="text-end">NoA</th>
    <th class="text-end">Tagihan</th>
    <th class="text-end">Tertagih</th>
    <th class="text-center">Excel</th>
  </tr>
  </thead>
  <tbody>
  <?php
  db_mobcol();
  mysql_select_db('mobcol');
  include "../function.php";

  $tgl=$_POST['tgl'];
  $select=$_POST['select'];
  $buss_date=date("Ymd");
               
  If (isset($_POST ['search']))
{
if (empty($tgl)||empty($select)){
    echo "<script>alert('Maaf,.isi Tanggal  !!!');</script>";
         echo "<script language='javascript'>document.location.href='ppap.php'</script>";}
   
    else{



        $_SESSION['stgl_npl'] = $tgl;
        $_SESSION['jenis_npl'] = $select;

    switch($select)
    {
     case "01":
      $jenis ='CS';
    //$cek_saldo= mysql_fetch_array(mysql_query("SELECT sum(saldo_kredit) - sum(debet) as saldo from rek_loan where unit ='$unit' "));
    $data_noa= mysql_fetch_array(mysql_query("SELECT count(cif) as noa, sum(bulat) as tagihan,sum(bayar) as tertagih from eod_tagihan_cs where unit ='$userid' and tgl_tagih ='$tgl' 
    "));
    $noa=$data_noa['noa'];
    $tagihan=$data_noa['tagihan'];
    $tertagih=$data_noa['tertagih'];
    break;
     
    case "02":
   $jenis='Non Cs Lima Persen';
    $data_noa= mysql_fetch_array(mysql_query("SELECT count(cif) as noa, sum(bulat) as tagihan,sum(bayar) as tertagih from eod_tagihan_lima_persen where unit ='$userid' and tgl_tagih ='$tgl'"));
    $noa=$data_noa['noa'];
    $tagihan=$data_noa['tagihan'];
    $tertagih=$data_noa['tertagih'];
    break;
   
    case "03":
      $jenis='Non Cs Lebaran';
    //$cek_saldo= mysql_fetch_array(mysql_query("SELECT sum(nominal) as saldo, datediff('$tgl', tgl_tunggak) as selisih from tunggakan where datediff('$tgl', tgl_tunggak) between 121 AND 365 AND unit  ='$unit' group by cif "));
    $data_noa= mysql_fetch_array(mysql_query("SELECT count(cif) as noa, sum(bulat) as tagihan,sum(bayar) as tertagih from eod_tagihan_lebaran where unit ='$userid' and tgl_tagih ='$tgl'"));
    $noa=$data_noa['noa'];
    $tagihan=$data_noa['tagihan'];
    $tertagih=$data_noa['tertagih'];
    break;
   
    case "04":
      $jenis='Non Cs Penarikan';
    //$cek_saldo= mysql_fetch_array(mysql_query("SELECT sum(nominal) as saldo, datediff('$tgl', tgl_tunggak) as selisih from tunggakan where datediff('$tgl', tgl_tunggak) >365 AND unit  ='$unit' group by cif "));
    $data_noa= mysql_fetch_array(mysql_query("SELECT count(cif) as noa, sum(bulat) as tagihan,sum(bayar) as tertagih from eod_tagihan_penarikan where unit ='$userid' and tgl_tagih ='$tgl'"));
    $noa=$data_noa['noa'];
    $tagihan=$data_noa['tagihan'];
    $tertagih=$data_noa['tertagih'];
     break;

     case "05":
      $jenis='Non Cs Tunggakan';
      //$cek_saldo= mysql_fetch_array(mysql_query("SELECT sum(nominal) as saldo, datediff('$tgl', tgl_tunggak) as selisih from tunggakan where datediff('$tgl', tgl_tunggak) >365 AND unit  ='$unit' group by cif "));
      $data_noa= mysql_fetch_array(mysql_query("SELECT count(cif) as noa, sum(bulat) as tagihan,sum(bayar) as tertagih from eod_tagihan_tunggakan where unit ='$userid' and tgl_tagih ='$tgl'"));
      $noa=$data_noa['noa'];
      $tagihan=$data_noa['tagihan'];
      $tertagih=$data_noa['tertagih'];
       break;
    case "06":
      $jenis='Non Cs Pelunasan';
        //$cek_saldo= mysql_fetch_array(mysql_query("SELECT sum(nominal) as saldo, datediff('$tgl', tgl_tunggak) as selisih from tunggakan where datediff('$tgl', tgl_tunggak) >365 AND unit  ='$unit' group by cif "));
        $data_noa= mysql_fetch_array(mysql_query("SELECT count(cif) as noa, sum(bulat) as tagihan,sum(bayar) as tertagih from eod_tagihan_pelunasan where unit ='$userid' and tgl_tagih ='$tgl'"));
        $noa=$data_noa['noa'];
        $tagihan=$data_noa['tagihan'];
        $tertagih=$data_noa['tertagih'];
         break;
 case "07":
      $jenis='Non Cs Wo';
        //$cek_saldo= mysql_fetch_array(mysql_query("SELECT sum(nominal) as saldo, datediff('$tgl', tgl_tunggak) as selisih from tunggakan where datediff('$tgl', tgl_tunggak) >365 AND unit  ='$unit' group by cif "));
        $data_noa= mysql_fetch_array(mysql_query("SELECT count(cif) as noa, sum(os) as os,sum(nominal) as tertagih from eod_wo left join ao on ao.cao = eod_wo.cao where kode_unit ='$userid' and tgl_tagih ='$tgl'"));
        $noa=$data_noa['noa'];
        $tagihan=$data_noa['os'];
        $tertagih=$data_noa['tertagih'];
         break;
 case "08":
      $jenis='Non Cs Wo';
        //$cek_saldo= mysql_fetch_array(mysql_query("SELECT sum(nominal) as saldo, datediff('$tgl', tgl_tunggak) as selisih from tunggakan where datediff('$tgl', tgl_tunggak) >365 AND unit  ='$unit' group by cif "));
        $data_noa= mysql_fetch_array(mysql_query("SELECT count(cif) as noa, '0' as os, sum(omzet) as tertagih from eod_omzet left join ao on ao.cao = eod_omzet.cao where kode_unit ='$userid' and tgl_tagih ='$tgl'"));
        $noa=$data_noa['noa'];
        $tagihan=$data_noa['os'];
        $tertagih=$data_noa['tertagih'];
         break;
      default:
    }
        }

   
  ?>
 
  <?php
   
    //menampilkan data


//menghitung hitung
//$birthdate = $data['Birth_date'];
//$age = ((time() - strtotime($birthdate))/(60*60*24*365.2425));

        ?>
  <tr>
    <td><?php echo $userid; ?></td>
    <td><?php echo $jenis; ?></td>
    <td class="text-end"><?php echo $noa;  ?></td>
    <td class="text-end"><?php echo number_format (compat_num($tagihan));?></td>
    <td class="text-end"><?php echo number_format (compat_num($tertagih)); ?></td>
    <td class="text-center"><a href="export_mobcol.php?unit=<?php echo $userid;?>&tgl=<?php echo $tgl;?>&jenis=<?php echo $select;?>" class="btn btn-outline-success btn-sm" onClick="return confirm('Apakah Anda yakin ?')">Download</a></td>
 
   
  </tr>
  <?php
       
   
    }
    ?>
 
  </tbody>
</table>
</div>
<?php
halaman_akhir();
