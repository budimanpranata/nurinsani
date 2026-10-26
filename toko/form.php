<?php require_once __DIR__ . '/../koneksi_db.php';
$host="localhost";
	$user="root";
	$pass="anaskorti";
	$db="nur_insani";
	
	$kelompok=$_GET['code_kel'];
	//koneksi database
	$koneksi2=db_ni();
	mysql_select_db($db,$koneksi2);
	
$date=date('Y-m-d');	
$query = "Select musawamah.id_member,unit,Plafond,ijaroh,bagi_hasil,bulat,pokok,Cust_Short_name,kelompok_toko.nama_kel,(bulat-angsuran) as sk, angsuran,os from musawamah
left join kelompok_toko on kelompok_toko.code_kel = musawamah.code_kel
Where kelompok_toko.code_kel='$kelompok' and os > 0";
$result = mysql_query($query);
?>
<html>
<head>
<link rel="stylesheet" href="../css/bootstrap.min.css"/>
<title> Form Setoran Kelompok </title>

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




function formatCurrency(num){
 num = num.toString().replace(/\$|\,/g,'');
 if(isNaN(num)) num = "0";
  cents = Math.floor((num*100+0.5)%100);
  num = Math.floor((num*100+0.5)/100).toString();
 if(cents < 10) cents = "0" + cents;
  for (var i = 0; i < Math.floor((num.length-(1+i))/3); i++)
  num = num.substring(0,num.length-(4*i+3))+'.'+num.substring(num.length-(4*i+3));
  return ("Rp. " + num + ",");
}
</script>

<script type="text/javascript">
    $(function() {
        //event handler to the checkbox selection change event
        $("input[type=checkbox]").change(function() {
            //variables to store the total price of selected rows
            //and to hold the reference to the current checkbox control
            var totalPrice = 0, ctlPrice;
            //iterate through all the rows of the gridview
            $('#data_list').each(function() {
                //if the checkbox in that rows is checked, add price to our total proce
                if ($(this).find('input:checkbox').attr("checked")) {
                    ctlPrice = $(this).find('[name= bulat[]]');
                    //since it is a currency column, we need to remove the $ sign and then convert it
                    //to a number before adding it to the total
                    totalPrice += parseFloat(ctlPrice.text().replace(/[^\d\.]/g, ''));
                }
            });
            //finally set the total price (rounded to 2 decimals) to the total paragraph control.
            $('#lblTotal').text("" + totalPrice.toFixed(2));               
        });
    });
</script>
</head>

<body>

<form action="checkbox.php" method="post" name="form">
    <table border="1" style="margin: 10px 0px" id ="data_list" class="table table-striped" >
           <thead>
		   <tr>
               <th><span class="badge"> Pilih</th>
               <th><span class="badge"> ID Member</th>
               <th><span class="badge">Nama</th>
               <th><span class="badge">Unit</th>
			   <th><span class="badge">Kelompok</th>
			   <th><span class="badge">OS</th>
			   <th><span class="badge">Margin</th>
			   <th><span class="badge">Storan</th>
			   <th><span class="badge">Angsruan</th>
				
				
			            </tr>
			</thead>
    <?php 
    while ($data = mysql_fetch_array($result)) {
    ?>
	<tbody>
            <tr>
                <td><input type="checkbox" id="cekbox" name="cekbox[]" value="<?php echo $data['id_member'] ?>"  onclick="return calculateSum();"/></td>
                <td><?php echo $data['id_member'] ?></td>
				<td><?php echo $data['Cust_Short_name'] ?></td>
                <td><?php echo $data['unit'] ?></td>
				<td><?php echo $data['nama_kel']?></td>
				<td><?php echo number_format (compat_num($data['os'])) ?></td>
				<td><?php echo number_format (compat_num($data['bagi_hasil'])) ?></td>
				<td><input type="text" id="bulat" name="bulat[]" value="<?php echo $data['bulat'] ?>"/></td>
             	<td><?php echo number_format (compat_num($data['angsuran'])) ?></td>
				
            </tr>
   </tbody>
    <?php
    
	}

    ?>
  
	</table>
    	
    <input type="button" class="alert-success" onclick="cek(this.form.cekbox)" value="Select All" />
    <input type="button" class="btn-danger" onclick="uncek(this.form.cekbox)" value="Clear All" />
    <input name="submit" type="submit" class="btn-success" value="submit" />
		
	    <br />
		
</form>
</body>



</html>