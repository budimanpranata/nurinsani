<?php require_once __DIR__ . '/../../php8_compat.php';
include "config.php";
include "header.php";
if(isset($_GET['u'])):
     if(isset($_POST['bts'])):
          $stmt = $mysqli->prepare("UPDATE tabel_transaksi SET debet=?, kredit=?,kode_rekening=? keterangan_transaksi=? WHERE id_transaksi=?");
          $stmt->bind_param('sssss', $debet, $kredit, $keterangan_transaksi, $id);
 
          $debet = $_POST['debet'];
          $kredit = $_POST['kredit'];
          $keterangan_transaksi = $_POST['keterangan_transaksi'];
          $id=$_POST['id'];
 	  $kode_rekening=$_POST['kode_rekening'];
          if($stmt->execute()):
              echo "<script>location.href='koreksi.php'</script>";
          else:
               echo "<script>alert('".$stmt->error."')</script>";
          endif;
     endif;
     $res = $mysqli->query("SELECT * FROM tabel_transaksi WHERE id_transaksi=".$_GET['u']);
     $row = $res->fetch_assoc();
?>
 
   <p>
</p>
     <div class="panel panel-default">
       <div class="panel-body">
        
  <form role="form" method="post">
    <input type="hidden" value="<?php echo $row['id_transaksi'] ?>" name="id"/>
    <div class="form-group">
      <label for="nm">Kode Tranksasi</label>
      <input type="text" class="form-control" name="kode_transaksi" id="kode_transaksi" value="<?php echo $row['kode_transaksi'] ?>">
    </div>
    <div class="form-group">
      <label for="gd">Coa</label>
         <input type="tel" class="form-control" name="kode_rekening" id="kode_rekening" value="<?php echo $row['kode_rekening'] ?>">
    </div>
    <div class="form-group">
      <label for="tl">Keterangan</label>
      <input type="tel" class="form-control" name="keterangan_transaksi" id="keterangan_transaksi" value="<?php echo $row['keterangan_transaksi'] ?>">
    </div>
    <div class="form-group">
      <label for="ar">Debet</label>
      <input type="text" class="form-control" name="debet" id="debet" value="<?php echo $row['debet'] ?>">
    </div>
	 <div class="form-group">
      <label for="ar">Kredit</label>
      <input type="text" class="form-control" name="kredit" id="kredit" value="<?php echo $row['kredit'] ?>">
    </div>
    <button type="submit" name="bts" class="btn btn-default">Submit</button>
  </form>
<?php
endif;
include "footer.php";
?>

