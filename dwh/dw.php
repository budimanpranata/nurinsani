<?php require_once __DIR__ . '/../php8_compat.php'; include "koneksi.php"; ?>
<html>
<head>
 <title>Menampilkan Data kedalam Tabel dengan PHP dan AJAX</title>
 <script>
function showHint(str) {
  var xhttp;
  if (str.length == 0) { 
    document.getElementById("tampildisini").innerHTML = "";
    return;
  }
  xhttp = new XMLHttpRequest();
  xhttp.onreadystatechange = function() {
    if (this.readyState == 4 && this.status == 200) {
      document.getElementById("tampildisini").innerHTML = this.responseText;
    }
  };
  xhttp.open("GET", "ambildata.php?kirim="+str, true);
  xhttp.send();   
}
</script>

</head>

<body>

<form>
<select name="data" id="data" onchange="showHint(this.value)">
<option value="">-- Pilih Data --</option>
<option value="semua">Semua Data</option>
<?php
$id=1;
$getdata=mysql_query("select * from dwh group by buss_date");
  if($getdata){
    while($result=mysql_fetch_array($getdata)){
      ?>
      <option value="<?php echo $result['buss_date'];?>"><?php echo $result['buss_date'];?></option>
      <?php 
    }
  }
?>
</select>
</form>


<div id="tampildisini"></div>
</body>
</html>
