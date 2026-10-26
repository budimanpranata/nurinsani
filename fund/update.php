<?php require_once __DIR__ . '/../php8_compat.php';
include "config.php";
include "head.php";
if(isset($_GET['u'])):
     if(isset($_POST['bts'])):
          $stmt = $mysqli->prepare("UPDATE deposan SET CUST_SHORT_NAME=?, gender=?, MOBILE_PHONE=?, HADDRESS1=? WHERE no=?");
          $stmt->bind_param('sssss', $nm, $gd, $tl, $ar, $id);
 
          $nm = $_POST['nm'];
          $gd = $_POST['gd'];
          $tl = $_POST['tl'];
          $ar = $_POST['ar'];
          $id = $_POST['id'];
 
          if($stmt->execute()):
               echo "<script> location.href='master_fund.php'</script>";
          else:
               echo "<script>alert('".$stmt->error."')</script>";
          endif;
     endif;
     $res = $mysqli->query("SELECT * FROM deposan WHERE no=".$_GET['u']);
     $row = $res->fetch_assoc();
?>
 
   <p>
</p>
     <div class="panel panel-default">
       <div class="panel-body">
        
  <form role="form" method="post">
    <input type="hidden" value="<?php echo $row['no'] ?>" name="id"/>
    <div class="form-group">
      <label for="nm">Name</label>
      <input type="text" class="form-control" name="nm" id="nm" value="<?php echo $row['name'] ?>">
    </div>
    <div class="form-group">
      <label for="gd">Gender</label>
      <select class="form-control" id="gd" name="gd">
        <option><?php echo $row['gender'] ?></option>
        <option>Male</option>
        <option>Female</option>
      </select>
    </div>
    <div class="form-group">
      <label for="tl">Phone</label>
      <input type="tel" class="form-control" name="tl" id="tl" value="<?php echo $row['telp'] ?>">
    </div>
    <div class="form-group">
      <label for="ar">Address</label>
      <textarea class="form-control" name="ar" id="ar" rows="3"><?php echo $row['address'] ?></textarea>
    </div>
    <button type="submit" name="bts" class="btn btn-default">Submit</button>
  </form>
<?php
endif;
include "footer.php";
?>