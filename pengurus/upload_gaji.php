<form name="myForm" id="myForm" onSubmit="return validateForm()" action="upload_gaji.php" method="post" enctype="multipart/form-data">
    <input type="file" id="filepegawaiall" name="filepegawaiall" />
    <input type="submit" name="submit" value="Import" /><br/>
    <label><input type="checkbox" name="drop" value="1" /> <u>Kosongkan Temporary terlebih dahulu.</u> </label>
</form>
<?php require_once __DIR__ . '/../koneksi_db.php'; 
if (isset($_POST['submit'])) {
?>
<div id="progress" style="width:500px;border:1px solid #ccc;"></div>
<div id="info"></div>
<?php
}
?>

<script type="text/javascript">
//    validasi form (hanya file .xls yang diijinkan)
    function validateForm()
    {
        function hasExtension(inputID, exts) {
            var fileName = document.getElementById(inputID).value;
            return (new RegExp('(' + exts.join('|').replace(/\./g, '\\.') + ')$')).test(fileName);
        }

        if(!hasExtension('filepegawaiall', ['.xls'])){
            alert("Hanya file XLS (Excel 2003) yang diijinkan.");
            return false;
        }
    }
</script>

<?php
mb_internal_encoding("8bit");
//koneksi ke database, username,password  dan namadatabase menyesuaikan 
db_ni();
mysql_select_db('nur_insani');

//memanggil file excel_reader
require "excel_reader2.php";
$tgl_cari=$_SESSION['stgl_system'];
$tgl_cari=date('Y-m-d');
//jika tombol import ditekan
if(isset($_POST['submit'])){

    $target = basename($_FILES['filepegawaiall']['name']) ;
    move_uploaded_file($_FILES['filepegawaiall']['tmp_name'], $target);
    chmod($target,0755);
    $data = new Spreadsheet_Excel_Reader($_FILES['filepegawaiall']['name'],false);
    
//    menghitung jumlah baris file xls
    //$baris = $data->rowcount($sheet_index=0);
    
//    jika kosongkan data dicentang jalankan kode berikut
    if($_POST['drop']==1){
//             kosongkan tabel pegawai
             $truncate ="TRUNCATE TABLE upload_jateng";
             mysql_query($truncate);
			 
	 	
	};
    
	
	
	$baris = $data->rowcount($sheet_index=0);

for ($i=6; $i<=$baris; $i++)
{
	
	$barisreal = $baris-1;
        $k = $i-1;
        
// menghitung persentase progress
        $percent = intval($k/$barisreal * 100)."%";

// mengupdate progress
        echo '<script language="javascript">
        document.getElementById("progress").innerHTML="<div style=\"width:'.$percent.'; background-color:lightblue\">&nbsp;</div>";
        document.getElementById("info").innerHTML="'.$k.' data berhasil diinsert ('.$percent.' selesai).";
        </script>';
	// baca data pada baris ke-i, kolom ke-1, pada sheet 1
	$coa = $data->val($i, 1, 0);
	// baca data pada baris ke-i, kolom ke-2, pada sheet 1
	$nama = $data->val($i, 5, 0);
	$saldo_awal = $data->val($i, 8, 0);
	$debet= $data->val($i, 3, 0);
	$debet = str_replace(",","", $debet);
	$kredit = $data->val($i, 4, 0);
	$kredit = str_replace(",","", $kredit);
	$saldo_akhir = $data->val($i, 8, 0);	
    $gl="001-$coa";   
	$unit="001";	

$query = "INSERT INTO upload_jateng(rekening_gl,coa,nama,debet,kredit,saldo_awal,saldo_akhir,unit) VALUES ('$gl','$coa','$nama','$debet','$kredit','$saldo_awal','$saldo_akhir','$unit')";
	 $hasil = mysql_query($query);
if($hasil){
@mysql_query("delete from upload_jateng where debet=0 and kredit =0 and unit ='$unit'");
}
      flush();

//      kita tambahkan sleep agar ada penundaan, sehingga progress terbaca bila file yg diimport sedikit
//      pada prakteknya sleep dihapus aja karena bikin lama hehe
      //sleep(1);

	}

//-------- import dari sheet 2 ----------

// baca jumlah baris dalam sheet 2
$baris = $data->rowcount($sheet_index=1);

for ($i=11; $i<=$baris; $i++)
{
	
	    $barisreal = $baris-1;
        $k = $i-1;
        
// menghitung persentase progress
        $percent = intval($k/$barisreal * 100)."%";

// mengupdate progress
        echo '<script language="javascript">
        document.getElementById("progress").innerHTML="<div style=\"width:'.$percent.'; background-color:lightblue\">&nbsp;</div>";
        document.getElementById("info").innerHTML="'.$k.' data berhasil diinsert ('.$percent.' selesai).";
        </script>';
	// baca data pada baris ke-i, kolom ke-1, pada sheet 2
	$coa = $data->val($i, 1, 1);
	// baca data pada baris ke-i, kolom ke-2, pada sheet 1
	$nama = $data->val($i, 5, 1);
	$saldo_awal = $data->val($i, 8, 1);
	$debet= $data->val($i, 3, 1);
	$debet = str_replace(",","", $debet);
	$kredit = $data->val($i, 4, 1);
	$kredit = str_replace(",","", $kredit);
	$saldo_akhir = $data->val($i, 8, 1);	
    $gl="002-$coa";   
	$unit="002";	

$query = "INSERT INTO upload_jateng(rekening_gl,coa,nama,debet,kredit,saldo_awal,saldo_akhir,unit) VALUES ('$gl','$coa','$nama','$debet','$kredit','$saldo_awal','$saldo_akhir','$unit')";
	 $hasil = mysql_query($query);
if($hasil){
@mysql_query("delete from upload_jateng where debet=0 and kredit =0 and unit ='$unit'");
}
      flush();

//      kita tambahkan sleep agar ada penundaan, sehingga progress terbaca bila file yg diimport sedikit
//      pada prakteknya sleep dihapus aja karena bikin lama hehe
      //sleep(1);

	// insert data ke tabel dosen
	

}

//-------- import dari sheet 3 ----------

// baca jumlah baris dalam sheet 3
$baris = $data->rowcount($sheet_index=2);
for ($i=11; $i<=$baris; $i++)
{
	$barisreal = $baris-1;
        $k = $i-1;
        
// menghitung persentase progress
        $percent = intval($k/$barisreal * 100)."%";

// mengupdate progress
        echo '<script language="javascript">
        document.getElementById("progress").innerHTML="<div style=\"width:'.$percent.'; background-color:lightblue\">&nbsp;</div>";
        document.getElementById("info").innerHTML="'.$k.' data berhasil diinsert ('.$percent.' selesai).";
        </script>';
	// baca data pada baris ke-i, kolom ke-1, pada sheet 3
$coa = $data->val($i, 1, 2);
	// baca data pada baris ke-i, kolom ke-2, pada sheet 1
	$nama = $data->val($i, 5, 2);
	$saldo_awal = $data->val($i, 8, 2);
	$debet= $data->val($i, 3, 2);
	$debet = str_replace(",","", $debet);
	$kredit = $data->val($i, 4, 2);
	$kredit = str_replace(",","", $kredit);
	$saldo_akhir = $data->val($i, 8, 2);	
    $gl="003-$coa";   
	$unit="003";	

$query = "INSERT INTO upload_jateng(rekening_gl,coa,nama,debet,kredit,saldo_awal,saldo_akhir,unit) VALUES ('$gl','$coa','$nama','$debet','$kredit','$saldo_awal','$saldo_akhir','$unit')";
	 $hasil = mysql_query($query);
if($hasil){
@mysql_query("delete from upload_jateng where debet=0 and kredit =0 and unit ='$unit'");
}
      flush();
//      kita tambahkan sleep agar ada penundaan, sehingga progress terbaca bila file yg diimport sedikit
//      pada prakteknya sleep dihapus aja karena bikin lama hehe
      //sleep(1);


}
$baris = $data->rowcount($sheet_index=3);
for ($i=11; $i<=$baris; $i++)
{
	$barisreal = $baris-1;
        $k = $i-1;
        
// menghitung persentase progress
        $percent = intval($k/$barisreal * 100)."%";

// mengupdate progress
        echo '<script language="javascript">
        document.getElementById("progress").innerHTML="<div style=\"width:'.$percent.'; background-color:lightblue\">&nbsp;</div>";
        document.getElementById("info").innerHTML="'.$k.' data berhasil diinsert ('.$percent.' selesai).";
        </script>';
	// baca data pada baris ke-i, kolom ke-1, pada sheet 3
$coa = $data->val($i, 1, 3);
	// baca data pada baris ke-i, kolom ke-2, pada sheet 1
	$nama = $data->val($i, 5, 3);
	$saldo_awal = $data->val($i, 8, 3);
	$debet= $data->val($i, 3, 3);
	$debet = str_replace(",","", $debet);
	$kredit = $data->val($i, 4, 3);
	$kredit = str_replace(",","", $kredit);
	$saldo_akhir = $data->val($i, 8, 3);	
    $gl="004-$coa";   
	$unit="004";	

$query = "INSERT INTO upload_jateng(rekening_gl,coa,nama,debet,kredit,saldo_awal,saldo_akhir,unit) VALUES ('$gl','$coa','$nama','$debet','$kredit','$saldo_awal','$saldo_akhir','$unit')";
	 $hasil = mysql_query($query);
if($hasil){
@mysql_query("delete from upload_jateng where debet=0 and kredit =0 and unit ='$unit'");
}
      flush();

//      kita tambahkan sleep agar ada penundaan, sehingga progress terbaca bila file yg diimport sedikit
//      pada prakteknya sleep dihapus aja karena bikin lama hehe
      //sleep(1);


}
$baris = $data->rowcount($sheet_index=4);
for ($i=11; $i<=$baris; $i++)
{
	$barisreal = $baris-1;
        $k = $i-1;
        
// menghitung persentase progress
        $percent = intval($k/$barisreal * 100)."%";

// mengupdate progress
        echo '<script language="javascript">
        document.getElementById("progress").innerHTML="<div style=\"width:'.$percent.'; background-color:lightblue\">&nbsp;</div>";
        document.getElementById("info").innerHTML="'.$k.' data berhasil diinsert ('.$percent.' selesai).";
        </script>';
	// baca data pada baris ke-i, kolom ke-1, pada sheet 3
$coa = $data->val($i, 1, 4);
	// baca data pada baris ke-i, kolom ke-2, pada sheet 1
	$nama = $data->val($i, 5, 4);
	$saldo_awal = $data->val($i, 8, 4);
	$debet= $data->val($i, 3, 4);
	$debet = str_replace(",","", $debet);
	$kredit = $data->val($i, 4, 4);
	$kredit = str_replace(",","", $kredit);
	$saldo_akhir = $data->val($i, 8, 4);	
    $gl="005-$coa";   
	$unit="005";	

$query = "INSERT INTO upload_jateng(rekening_gl,coa,nama,debet,kredit,saldo_awal,saldo_akhir,unit) VALUES ('$gl','$coa','$nama','$debet','$kredit','$saldo_awal','$saldo_akhir','$unit')";
	 $hasil = mysql_query($query);
if($hasil){
@mysql_query("delete from upload_jateng where debet=0 and kredit =0 and unit ='$unit'");
}
      flush();
//      kita tambahkan sleep agar ada penundaan, sehingga progress terbaca bila file yg diimport sedikit
//      pada prakteknya sleep dihapus aja karena bikin lama hehe
      //sleep(1);


}
$baris = $data->rowcount($sheet_index=5);
for ($i=11; $i<=$baris; $i++)
{
	$barisreal = $baris-1;
        $k = $i-1;
        
// menghitung persentase progress
        $percent = intval($k/$barisreal * 100)."%";

// mengupdate progress
        echo '<script language="javascript">
        document.getElementById("progress").innerHTML="<div style=\"width:'.$percent.'; background-color:lightblue\">&nbsp;</div>";
        document.getElementById("info").innerHTML="'.$k.' data berhasil diinsert ('.$percent.' selesai).";
        </script>';
	// baca data pada baris ke-i, kolom ke-1, pada sheet 3
$coa = $data->val($i, 1, 5);
	// baca data pada baris ke-i, kolom ke-2, pada sheet 1
	$nama = $data->val($i, 5, 5);
	$saldo_awal = $data->val($i, 8, 5);
	$debet= $data->val($i, 3, 5);
	$debet = str_replace(",","", $debet);
	$kredit = $data->val($i, 4, 5);
	$kredit = str_replace(",","", $kredit);
	$saldo_akhir = $data->val($i, 8, 5);	
    $gl="006-$coa";   
	$unit="006";	

$query = "INSERT INTO upload_jateng(rekening_gl,coa,nama,debet,kredit,saldo_awal,saldo_akhir,unit) VALUES ('$gl','$coa','$nama','$debet','$kredit','$saldo_awal','$saldo_akhir','$unit')";
	 $hasil = mysql_query($query);
if($hasil){
@mysql_query("delete from upload_jateng where debet=0 and kredit =0 and unit ='$unit'");
}
      flush();
//      kita tambahkan sleep agar ada penundaan, sehingga progress terbaca bila file yg diimport sedikit
//      pada prakteknya sleep dihapus aja karena bikin lama hehe
      //sleep(1);


}
$baris = $data->rowcount($sheet_index=6);
for ($i=11; $i<=$baris; $i++)
{
	$barisreal = $baris-1;
        $k = $i-1;
        
// menghitung persentase progress
        $percent = intval($k/$barisreal * 100)."%";

// mengupdate progress
        echo '<script language="javascript">
        document.getElementById("progress").innerHTML="<div style=\"width:'.$percent.'; background-color:lightblue\">&nbsp;</div>";
        document.getElementById("info").innerHTML="'.$k.' data berhasil diinsert ('.$percent.' selesai).";
        </script>';
	// baca data pada baris ke-i, kolom ke-1, pada sheet 3
$coa = $data->val($i, 1, 6);
	// baca data pada baris ke-i, kolom ke-2, pada sheet 1
	$nama = $data->val($i, 5, 6);
	$saldo_awal = $data->val($i, 8, 6);
	$debet= $data->val($i, 3, 6);
	$debet = str_replace(",","", $debet);
	$kredit = $data->val($i, 4, 6);
	$kredit = str_replace(",","", $kredit);
	$saldo_akhir = $data->val($i, 8, 6);	
    $gl="007-$coa";   
	$unit="007";	

$query = "INSERT INTO upload_jateng(rekening_gl,coa,nama,debet,kredit,saldo_awal,saldo_akhir,unit) VALUES ('$gl','$coa','$nama','$debet','$kredit','$saldo_awal','$saldo_akhir','$unit')";
	 $hasil = mysql_query($query);
if($hasil){
@mysql_query("delete from upload_jateng where debet=0 and kredit =0 and unit ='$unit'");
}
      flush();
//      kita tambahkan sleep agar ada penundaan, sehingga progress terbaca bila file yg diimport sedikit
//      pada prakteknya sleep dihapus aja karena bikin lama hehe
      //sleep(1);


}
$baris = $data->rowcount($sheet_index=7);
for ($i=11; $i<=$baris; $i++)
{
	$barisreal = $baris-1;
        $k = $i-1;
        
// menghitung persentase progress
        $percent = intval($k/$barisreal * 100)."%";

// mengupdate progress
        echo '<script language="javascript">
        document.getElementById("progress").innerHTML="<div style=\"width:'.$percent.'; background-color:lightblue\">&nbsp;</div>";
        document.getElementById("info").innerHTML="'.$k.' data berhasil diinsert ('.$percent.' selesai).";
        </script>';
	// baca data pada baris ke-i, kolom ke-1, pada sheet 3
$coa = $data->val($i, 1, 7);
	// baca data pada baris ke-i, kolom ke-2, pada sheet 1
	$nama = $data->val($i, 5, 7);
	$saldo_awal = $data->val($i, 8, 7);
	$debet= $data->val($i, 3, 7);
	$debet = str_replace(",","", $debet);
	$kredit = $data->val($i, 4, 7);
	$kredit = str_replace(",","", $kredit);
	$saldo_akhir = $data->val($i, 8, 7);	
    $gl="008-$coa";   
	$unit="008";	

$query = "INSERT INTO upload_jateng(rekening_gl,coa,nama,debet,kredit,saldo_awal,saldo_akhir,unit) VALUES ('$gl','$coa','$nama','$debet','$kredit','$saldo_awal','$saldo_akhir','$unit')";
	 $hasil = mysql_query($query);
if($hasil){
@mysql_query("delete from upload_jateng where debet=0 and kredit =0 and unit ='$unit'");
}
      flush();
//      kita tambahkan sleep agar ada penundaan, sehingga progress terbaca bila file yg diimport sedikit
//      pada prakteknya sleep dihapus aja karena bikin lama hehe
      //sleep(1);


}
$baris = $data->rowcount($sheet_index=8);
for ($i=11; $i<=$baris; $i++)
{
	$barisreal = $baris-1;
        $k = $i-1;
        
// menghitung persentase progress
        $percent = intval($k/$barisreal * 100)."%";

// mengupdate progress
        echo '<script language="javascript">
        document.getElementById("progress").innerHTML="<div style=\"width:'.$percent.'; background-color:lightblue\">&nbsp;</div>";
        document.getElementById("info").innerHTML="'.$k.' data berhasil diinsert ('.$percent.' selesai).";
        </script>';
	// baca data pada baris ke-i, kolom ke-1, pada sheet 3
$coa = $data->val($i, 1, 8);
	// baca data pada baris ke-i, kolom ke-2, pada sheet 1
	$nama = $data->val($i, 5, 8);
	$saldo_awal = $data->val($i, 8, 8);
	$debet= $data->val($i, 3, 8);
	$debet = str_replace(",","", $debet);
	$kredit = $data->val($i, 4, 8);
	$kredit = str_replace(",","", $kredit);
	$saldo_akhir = $data->val($i, 8, 8);	
    $gl="009-$coa";   
	$unit="009";	

$query = "INSERT INTO upload_jateng(rekening_gl,coa,nama,debet,kredit,saldo_awal,saldo_akhir,unit) VALUES ('$gl','$coa','$nama','$debet','$kredit','$saldo_awal','$saldo_akhir','$unit')";
	 $hasil = mysql_query($query);
if($hasil){
@mysql_query("delete from upload_jateng where debet=0 and kredit =0 and unit ='$unit'");
}
      flush();
//      kita tambahkan sleep agar ada penundaan, sehingga progress terbaca bila file yg diimport sedikit
//      pada prakteknya sleep dihapus aja karena bikin lama hehe
      //sleep(1);


}
$baris = $data->rowcount($sheet_index=9);
for ($i=11; $i<=$baris; $i++)
{
	$barisreal = $baris-1;
        $k = $i-1;
        
// menghitung persentase progress
        $percent = intval($k/$barisreal * 100)."%";

// mengupdate progress
        echo '<script language="javascript">
        document.getElementById("progress").innerHTML="<div style=\"width:'.$percent.'; background-color:lightblue\">&nbsp;</div>";
        document.getElementById("info").innerHTML="'.$k.' data berhasil diinsert ('.$percent.' selesai).";
        </script>';
	// baca data pada baris ke-i, kolom ke-1, pada sheet 3
$coa = $data->val($i, 1, 9);
	// baca data pada baris ke-i, kolom ke-2, pada sheet 1
	$nama = $data->val($i, 5, 9);
	$saldo_awal = $data->val($i, 8, 9);
	$debet= $data->val($i, 3, 9);
	$debet = str_replace(",","", $debet);
	$kredit = $data->val($i, 4, 9);
	$kredit = str_replace(",","", $kredit);
	$saldo_akhir = $data->val($i, 8, 9);	
    $gl="010-$coa";   
	$unit="010";	

$query = "INSERT INTO upload_jateng(rekening_gl,coa,nama,debet,kredit,saldo_awal,saldo_akhir,unit) VALUES ('$gl','$coa','$nama','$debet','$kredit','$saldo_awal','$saldo_akhir','$unit')";
	 $hasil = mysql_query($query);
if($hasil){
@mysql_query("delete from upload_jateng where debet=0 and kredit =0 and unit ='$unit'");
}
      flush();
//      kita tambahkan sleep agar ada penundaan, sehingga progress terbaca bila file yg diimport sedikit
//      pada prakteknya sleep dihapus aja karena bikin lama hehe
      //sleep(1);


}
$baris = $data->rowcount($sheet_index=10);
for ($i=11; $i<=$baris; $i++)
{
	$barisreal = $baris-1;
        $k = $i-1;
        
// menghitung persentase progress
        $percent = intval($k/$barisreal * 100)."%";

// mengupdate progress
        echo '<script language="javascript">
        document.getElementById("progress").innerHTML="<div style=\"width:'.$percent.'; background-color:lightblue\">&nbsp;</div>";
        document.getElementById("info").innerHTML="'.$k.' data berhasil diinsert ('.$percent.' selesai).";
        </script>';
	// baca data pada baris ke-i, kolom ke-1, pada sheet 3
$coa = $data->val($i, 1, 10);
	// baca data pada baris ke-i, kolom ke-2, pada sheet 1
	$nama = $data->val($i, 5, 10);
	$saldo_awal = $data->val($i, 8, 10);
	$debet= $data->val($i, 3, 10);
	$debet = str_replace(",","", $debet);
	$kredit = $data->val($i, 4, 10);
	$kredit = str_replace(",","", $kredit);
	$saldo_akhir = $data->val($i, 8, 10);	
    $gl="011-$coa";   
	$unit="011";	

$query = "INSERT INTO upload_jateng(rekening_gl,coa,nama,debet,kredit,saldo_awal,saldo_akhir,unit) VALUES ('$gl','$coa','$nama','$debet','$kredit','$saldo_awal','$saldo_akhir','$unit')";
	 $hasil = mysql_query($query);
if($hasil){
@mysql_query("delete from upload_jateng where debet=0 and kredit =0 and unit ='$unit'");
}
      flush();
//      kita tambahkan sleep agar ada penundaan, sehingga progress terbaca bila file yg diimport sedikit
//      pada prakteknya sleep dihapus aja karena bikin lama hehe
      //sleep(1);


}
$baris = $data->rowcount($sheet_index=11);
for ($i=11; $i<=$baris; $i++)
{
	$barisreal = $baris-1;
        $k = $i-1;
        
// menghitung persentase progress
        $percent = intval($k/$barisreal * 100)."%";

// mengupdate progress
        echo '<script language="javascript">
        document.getElementById("progress").innerHTML="<div style=\"width:'.$percent.'; background-color:lightblue\">&nbsp;</div>";
        document.getElementById("info").innerHTML="'.$k.' data berhasil diinsert ('.$percent.' selesai).";
        </script>';
	// baca data pada baris ke-i, kolom ke-1, pada sheet 3
$coa = $data->val($i, 1, 11);
	// baca data pada baris ke-i, kolom ke-2, pada sheet 1
	$nama = $data->val($i, 5, 11);
	$saldo_awal = $data->val($i, 8, 11);
	$debet= $data->val($i, 3, 11);
	$debet = str_replace(",","", $debet);
	$kredit = $data->val($i, 4, 11);
	$kredit = str_replace(",","", $kredit);
	$saldo_akhir = $data->val($i, 8, 11);	
    $gl="012-$coa";   
	$unit="012";	

$query = "INSERT INTO upload_jateng(rekening_gl,coa,nama,debet,kredit,saldo_awal,saldo_akhir,unit) VALUES ('$gl','$coa','$nama','$debet','$kredit','$saldo_awal','$saldo_akhir','$unit')";
	 $hasil = mysql_query($query);
if($hasil){
@mysql_query("delete from upload_jateng where debet=0 and kredit =0 and unit ='$unit'");
}
      flush();
//      kita tambahkan sleep agar ada penundaan, sehingga progress terbaca bila file yg diimport sedikit
//      pada prakteknya sleep dihapus aja karena bikin lama hehe
      //sleep(1);


}
$baris = $data->rowcount($sheet_index=12);
for ($i=11; $i<=$baris; $i++)
{
	$barisreal = $baris-1;
        $k = $i-1;
        
// menghitung persentase progress
        $percent = intval($k/$barisreal * 100)."%";

// mengupdate progress
        echo '<script language="javascript">
        document.getElementById("progress").innerHTML="<div style=\"width:'.$percent.'; background-color:lightblue\">&nbsp;</div>";
        document.getElementById("info").innerHTML="'.$k.' data berhasil diinsert ('.$percent.' selesai).";
        </script>';
	// baca data pada baris ke-i, kolom ke-1, pada sheet 3
$coa = $data->val($i, 1, 12);
	// baca data pada baris ke-i, kolom ke-2, pada sheet 1
	$nama = $data->val($i, 5, 12);
	$saldo_awal = $data->val($i, 8, 12);
	$debet= $data->val($i, 3, 12);
	$debet = str_replace(",","", $debet);
	$kredit = $data->val($i, 4, 12);
	$kredit = str_replace(",","", $kredit);
	$saldo_akhir = $data->val($i, 8, 12);	
    $gl="013-$coa";   
	$unit="013";	

$query = "INSERT INTO upload_jateng(rekening_gl,coa,nama,debet,kredit,saldo_awal,saldo_akhir,unit) VALUES ('$gl','$coa','$nama','$debet','$kredit','$saldo_awal','$saldo_akhir','$unit')";
	 $hasil = mysql_query($query);
if($hasil){
@mysql_query("delete from upload_jateng where debet=0 and kredit =0 and unit ='$unit'");
}
      flush();
//      kita tambahkan sleep agar ada penundaan, sehingga progress terbaca bila file yg diimport sedikit
//      pada prakteknya sleep dihapus aja karena bikin lama hehe
      //sleep(1);


}
$baris = $data->rowcount($sheet_index=13);
for ($i=11; $i<=$baris; $i++)
{
	$barisreal = $baris-1;
        $k = $i-1;
        
// menghitung persentase progress
        $percent = intval($k/$barisreal * 100)."%";

// mengupdate progress
        echo '<script language="javascript">
        document.getElementById("progress").innerHTML="<div style=\"width:'.$percent.'; background-color:lightblue\">&nbsp;</div>";
        document.getElementById("info").innerHTML="'.$k.' data berhasil diinsert ('.$percent.' selesai).";
        </script>';
	// baca data pada baris ke-i, kolom ke-1, pada sheet 3
$coa = $data->val($i, 1, 13);
	// baca data pada baris ke-i, kolom ke-2, pada sheet 1
	$nama = $data->val($i, 5, 13);
	$saldo_awal = $data->val($i, 8, 13);
	$debet= $data->val($i, 3, 13);
	$debet = str_replace(",","", $debet);
	$kredit = $data->val($i, 4, 13);
	$kredit = str_replace(",","", $kredit);
	$saldo_akhir = $data->val($i, 8, 13);	
    $gl="014-$coa";   
	$unit="014";	

$query = "INSERT INTO upload_jateng(rekening_gl,coa,nama,debet,kredit,saldo_awal,saldo_akhir,unit) VALUES ('$gl','$coa','$nama','$debet','$kredit','$saldo_awal','$saldo_akhir','$unit')";
	 $hasil = mysql_query($query);
if($hasil){
@mysql_query("delete from upload_jateng where debet=0 and kredit =0 and unit ='$unit'");
}
      flush();
//      kita tambahkan sleep agar ada penundaan, sehingga progress terbaca bila file yg diimport sedikit
//      pada prakteknya sleep dihapus aja karena bikin lama hehe
      //sleep(1);


}
$baris = $data->rowcount($sheet_index=14);
for ($i=11; $i<=$baris; $i++)
{
	$barisreal = $baris-1;
        $k = $i-1;
        
// menghitung persentase progress
        $percent = intval($k/$barisreal * 100)."%";

// mengupdate progress
        echo '<script language="javascript">
        document.getElementById("progress").innerHTML="<div style=\"width:'.$percent.'; background-color:lightblue\">&nbsp;</div>";
        document.getElementById("info").innerHTML="'.$k.' data berhasil diinsert ('.$percent.' selesai).";
        </script>';
	// baca data pada baris ke-i, kolom ke-1, pada sheet 3
$coa = $data->val($i, 1, 14);
	// baca data pada baris ke-i, kolom ke-2, pada sheet 1
	$nama = $data->val($i, 5, 14);
	$saldo_awal = $data->val($i, 8, 14);
	$debet= $data->val($i, 3, 14);
	$debet = str_replace(",","", $debet);
	$kredit = $data->val($i, 4, 14);
	$kredit = str_replace(",","", $kredit);
	$saldo_akhir = $data->val($i, 8, 14);	
    $gl="015-$coa";   
	$unit="015";	

$query = "INSERT INTO upload_jateng(rekening_gl,coa,nama,debet,kredit,saldo_awal,saldo_akhir,unit) VALUES ('$gl','$coa','$nama','$debet','$kredit','$saldo_awal','$saldo_akhir','$unit')";
	 $hasil = mysql_query($query);
if($hasil){
@mysql_query("delete from upload_jateng where debet=0 and kredit =0 and unit ='$unit'");
}
      flush();
//      kita tambahkan sleep agar ada penundaan, sehingga progress terbaca bila file yg diimport sedikit
//      pada prakteknya sleep dihapus aja karena bikin lama hehe
      //sleep(1);


}
$baris = $data->rowcount($sheet_index=15);
for ($i=11; $i<=$baris; $i++)
{
	$barisreal = $baris-1;
        $k = $i-1;
        
// menghitung persentase progress
        $percent = intval($k/$barisreal * 100)."%";

// mengupdate progress
        echo '<script language="javascript">
        document.getElementById("progress").innerHTML="<div style=\"width:'.$percent.'; background-color:lightblue\">&nbsp;</div>";
        document.getElementById("info").innerHTML="'.$k.' data berhasil diinsert ('.$percent.' selesai).";
        </script>';
	// baca data pada baris ke-i, kolom ke-1, pada sheet 3
$coa = $data->val($i, 1, 15);
	// baca data pada baris ke-i, kolom ke-2, pada sheet 1
	$nama = $data->val($i, 5, 15);
	$saldo_awal = $data->val($i, 8, 15);
	$debet= $data->val($i, 3, 15);
	$debet = str_replace(",","", $debet);
	$kredit = $data->val($i, 4, 15);
	$kredit = str_replace(",","", $kredit);
	$saldo_akhir = $data->val($i, 8, 15);	
    $gl="3001-$coa";   
	$unit="3001";	

$query = "INSERT INTO upload_jateng(rekening_gl,coa,nama,debet,kredit,saldo_awal,saldo_akhir,unit) VALUES ('$gl','$coa','$nama','$debet','$kredit','$saldo_awal','$saldo_akhir','$unit')";
	 $hasil = mysql_query($query);
if($hasil){
@mysql_query("delete from upload_jateng where debet=0 and kredit =0 and unit ='$unit'");
}
      flush();
//      kita tambahkan sleep agar ada penundaan, sehingga progress terbaca bila file yg diimport sedikit
//      pada prakteknya sleep dihapus aja karena bikin lama hehe
      //sleep(1);


}
$baris = $data->rowcount($sheet_index=16);
for ($i=11; $i<=$baris; $i++)
{
	$barisreal = $baris-1;
        $k = $i-1;
        
// menghitung persentase progress
        $percent = intval($k/$barisreal * 100)."%";

// mengupdate progress
        echo '<script language="javascript">
        document.getElementById("progress").innerHTML="<div style=\"width:'.$percent.'; background-color:lightblue\">&nbsp;</div>";
        document.getElementById("info").innerHTML="'.$k.' data berhasil diinsert ('.$percent.' selesai).";
        </script>';
	// baca data pada baris ke-i, kolom ke-1, pada sheet 3
$coa = $data->val($i, 1, 16);
	// baca data pada baris ke-i, kolom ke-2, pada sheet 1
	$nama = $data->val($i, 5, 16);
	$saldo_awal = $data->val($i, 8, 16);
	$debet= $data->val($i, 3, 16);
	$debet = str_replace(",","", $debet);
	$kredit = $data->val($i, 4, 16);
	$kredit = str_replace(",","", $kredit);
	$saldo_akhir = $data->val($i, 8, 16);	
    $gl="3002-$coa";   
	$unit="3002";	

$query = "INSERT INTO upload_jateng(rekening_gl,coa,nama,debet,kredit,saldo_awal,saldo_akhir,unit) VALUES ('$gl','$coa','$nama','$debet','$kredit','$saldo_awal','$saldo_akhir','$unit')";
	 $hasil = mysql_query($query);
if($hasil){
@mysql_query("delete from upload_jateng where debet=0 and kredit =0 and unit ='$unit'");
}
      flush();
//      kita tambahkan sleep agar ada penundaan, sehingga progress terbaca bila file yg diimport sedikit
//      pada prakteknya sleep dihapus aja karena bikin lama hehe
      //sleep(1);


}
$baris = $data->rowcount($sheet_index=17);
for ($i=11; $i<=$baris; $i++)
{
	$barisreal = $baris-1;
        $k = $i-1;
        
// menghitung persentase progress
        $percent = intval($k/$barisreal * 100)."%";

// mengupdate progress
        echo '<script language="javascript">
        document.getElementById("progress").innerHTML="<div style=\"width:'.$percent.'; background-color:lightblue\">&nbsp;</div>";
        document.getElementById("info").innerHTML="'.$k.' data berhasil diinsert ('.$percent.' selesai).";
        </script>';
	// baca data pada baris ke-i, kolom ke-1, pada sheet 3
$coa = $data->val($i, 1, 17);
	// baca data pada baris ke-i, kolom ke-2, pada sheet 1
	$nama = $data->val($i, 5, 17);
	$saldo_awal = $data->val($i, 8, 17);
	$debet= $data->val($i, 3, 17);
	$debet = str_replace(",","", $debet);
	$kredit = $data->val($i, 4, 17);
	$kredit = str_replace(",","", $kredit);
	$saldo_akhir = $data->val($i, 8, 17);	
    $gl="3003-$coa";   
	$unit="3003";	

$query = "INSERT INTO upload_jateng(rekening_gl,coa,nama,debet,kredit,saldo_awal,saldo_akhir,unit) VALUES ('$gl','$coa','$nama','$debet','$kredit','$saldo_awal','$saldo_akhir','$unit')";
	 $hasil = mysql_query($query);
if($hasil){
@mysql_query("delete from upload_jateng where debet=0 and kredit =0 and unit ='$unit'");
}
      flush();

//      kita tambahkan sleep agar ada penundaan, sehingga progress terbaca bila file yg diimport sedikit
//      pada prakteknya sleep dihapus aja karena bikin lama hehe
      //sleep(1);


}
$baris = $data->rowcount($sheet_index=18);
for ($i=11; $i<=$baris; $i++)
{
	$barisreal = $baris-1;
        $k = $i-1;
        
// menghitung persentase progress
        $percent = intval($k/$barisreal * 100)."%";

// mengupdate progress
        echo '<script language="javascript">
        document.getElementById("progress").innerHTML="<div style=\"width:'.$percent.'; background-color:lightblue\">&nbsp;</div>";
        document.getElementById("info").innerHTML="'.$k.' data berhasil diinsert ('.$percent.' selesai).";
        </script>';
	// baca data pada baris ke-i, kolom ke-1, pada sheet 3
$coa = $data->val($i, 1, 18);
	// baca data pada baris ke-i, kolom ke-2, pada sheet 1
	$nama = $data->val($i, 5, 18);
	$saldo_awal = $data->val($i, 8, 18);
	$debet= $data->val($i, 3, 18);
	$debet = str_replace(",","", $debet);
	$kredit = $data->val($i, 4, 18);
	$kredit = str_replace(",","", $kredit);
	$saldo_akhir = $data->val($i, 8, 18);	
    $gl="2001-$coa";   
	$unit="2001";	

$query = "INSERT INTO upload_jateng(rekening_gl,coa,nama,debet,kredit,saldo_awal,saldo_akhir,unit) VALUES ('$gl','$coa','$nama','$debet','$kredit','$saldo_awal','$saldo_akhir','$unit')";
	 $hasil = mysql_query($query);
if($hasil){
@mysql_query("delete from upload_jateng where debet=0 and kredit =0 and unit ='$unit'");
}
      flush();

//      kita tambahkan sleep agar ada penundaan, sehingga progress terbaca bila file yg diimport sedikit
//      pada prakteknya sleep dihapus aja karena bikin lama hehe
      //sleep(1);


}
$baris = $data->rowcount($sheet_index=19);
for ($i=11; $i<=$baris; $i++)
{
	$barisreal = $baris-1;
        $k = $i-1;
        
// menghitung persentase progress
        $percent = intval($k/$barisreal * 100)."%";

// mengupdate progress
        echo '<script language="javascript">
        document.getElementById("progress").innerHTML="<div style=\"width:'.$percent.'; background-color:lightblue\">&nbsp;</div>";
        document.getElementById("info").innerHTML="'.$k.' data berhasil diinsert ('.$percent.' selesai).";
        </script>';
	// baca data pada baris ke-i, kolom ke-1, pada sheet 3
$coa = $data->val($i, 1, 19);
	// baca data pada baris ke-i, kolom ke-2, pada sheet 1
	$nama = $data->val($i, 5, 19);
	$saldo_awal = $data->val($i, 8, 19);
	$debet= $data->val($i, 3, 19);
	$debet = str_replace(",","", $debet);
	$kredit = $data->val($i, 4, 19);
	$kredit = str_replace(",","", $kredit);
	$saldo_akhir = $data->val($i, 8, 19);	
    $gl="2002-$coa";   
	$unit="2002";	

$query = "INSERT INTO upload_jateng(rekening_gl,coa,nama,debet,kredit,saldo_awal,saldo_akhir,unit) VALUES ('$gl','$coa','$nama','$debet','$kredit','$saldo_awal','$saldo_akhir','$unit')";
	 $hasil = mysql_query($query);
if($hasil){
@mysql_query("delete from upload_jateng where debet=0 and kredit =0 and unit ='$unit'");
}
      flush();

//      kita tambahkan sleep agar ada penundaan, sehingga progress terbaca bila file yg diimport sedikit
//      pada prakteknya sleep dihapus aja karena bikin lama hehe
    //sleep(1);


}
$baris = $data->rowcount($sheet_index=20);
for ($i=11; $i<=$baris; $i++)
{
	$barisreal = $baris-1;
        $k = $i-1;
        
// menghitung persentase progress
        $percent = intval($k/$barisreal * 100)."%";

// mengupdate progress
        echo '<script language="javascript">
        document.getElementById("progress").innerHTML="<div style=\"width:'.$percent.'; background-color:lightblue\">&nbsp;</div>";
        document.getElementById("info").innerHTML="'.$k.' Proses Upload.. ('.$percent.' selesai).";
        </script>';
	// baca data pada baris ke-i, kolom ke-1, pada sheet 3
$coa = $data->val($i, 1, 20);
	// baca data pada baris ke-i, kolom ke-2, pada sheet 1
	$nama = $data->val($i, 5, 20);
	$saldo_awal = $data->val($i, 8, 20);
	$debet= $data->val($i, 3, 20);
	$debet = str_replace(",","", $debet);
	$kredit = $data->val($i, 4, 20);
	$kredit = str_replace(",","", $kredit);
	$saldo_akhir = $data->val($i, 8, 20);	
    $gl="2003-$coa";   
	$unit="2003";	

$query = "INSERT INTO upload_jateng(rekening_gl,coa,nama,debet,kredit,saldo_awal,saldo_akhir,unit) VALUES ('$gl','$coa','$nama','$debet','$kredit','$saldo_awal','$saldo_akhir','$unit')";
	 $hasil = mysql_query($query);
if($hasil){
@mysql_query("delete from upload_jateng where debet=0 and kredit =0 and unit ='$unit'");
}
      flush();

//      kita tambahkan sleep agar ada penundaan, sehingga progress terbaca bila file yg diimport sedikit
//      pada prakteknya sleep dihapus aja karena bikin lama hehe
    //sleep(1);


}

$baris = $data->rowcount($sheet_index=21);
for ($i=11; $i<=$baris; $i++)
{
	$barisreal = $baris-1;
        $k = $i-1;
        
// menghitung persentase progress
        $percent = intval($k/$barisreal * 100)."%";

// mengupdate progress
        echo '<script language="javascript">
        document.getElementById("progress").innerHTML="<div style=\"width:'.$percent.'; background-color:lightblue\">&nbsp;</div>";
        document.getElementById("info").innerHTML="'.$k.' Proses Upload.. ('.$percent.' selesai).";
        </script>';
	// baca data pada baris ke-i, kolom ke-1, pada sheet 3
$coa = $data->val($i, 1, 0);
	// baca data pada baris ke-i, kolom ke-2, pada sheet 1
	$nama = $data->val($i, 5, 21);
	$saldo_awal = $data->val($i, 8, 21);
	$debet= $data->val($i, 3, 21);
	$debet = str_replace(",","", $debet);
	$kredit = $data->val($i, 4, 21);
	$kredit = str_replace(",","", $kredit);
	$saldo_akhir = $data->val($i, 8, 21);	
    $gl="2004-$coa";   
	$unit="2004";	

$query = "INSERT INTO upload_jateng(rekening_gl,coa,nama,debet,kredit,saldo_awal,saldo_akhir,unit) VALUES ('$gl','$coa','$nama','$debet','$kredit','$saldo_awal','$saldo_akhir','$unit')";
	 $hasil = mysql_query($query);
if($hasil){
@mysql_query("delete from upload_jateng where debet=0 and kredit =0 and unit ='$unit'");
}
      flush();

//      kita tambahkan sleep agar ada penundaan, sehingga progress terbaca bila file yg diimport sedikit
//      pada prakteknya sleep dihapus aja karena bikin lama hehe
    //sleep(1);


}
$baris = $data->rowcount($sheet_index=22);
for ($i=11; $i<=$baris; $i++)
{
	$barisreal = $baris-1;
        $k = $i-1;
        
// menghitung persentase progress
        $percent = intval($k/$barisreal * 100)."%";

// mengupdate progress
        echo '<script language="javascript">
        document.getElementById("progress").innerHTML="<div style=\"width:'.$percent.'; background-color:lightblue\">&nbsp;</div>";
        document.getElementById("info").innerHTML="'.$k.' Proses Upload.. ('.$percent.' selesai).";
        </script>';
	// baca data pada baris ke-i, kolom ke-1, pada sheet 3
$coa = $data->val($i, 1, 22);
	// baca data pada baris ke-i, kolom ke-2, pada sheet 1
	$nama = $data->val($i, 5, 22);
	$saldo_awal = $data->val($i, 8, 22);
	$debet= $data->val($i, 3, 22);
	$debet = str_replace(",","", $debet);
	$kredit = $data->val($i, 4, 22);
	$kredit = str_replace(",","", $kredit);
	$saldo_akhir = $data->val($i, 8, 22);	
    $gl="2005-$coa";   
	$unit="2005";	

$query = "INSERT INTO upload_jateng(rekening_gl,coa,nama,debet,kredit,saldo_awal,saldo_akhir,unit) VALUES ('$gl','$coa','$nama','$debet','$kredit','$saldo_awal','$saldo_akhir','$unit')";
	 $hasil = mysql_query($query);
if($hasil){
@mysql_query("delete from upload_jateng where debet=0 and kredit =0 and unit ='$unit'");
}
      flush();

//      kita tambahkan sleep agar ada penundaan, sehingga progress terbaca bila file yg diimport sedikit
//      pada prakteknya sleep dihapus aja karena bikin lama hehe
    //sleep(1);


}

$baris = $data->rowcount($sheet_index=23);
for ($i=11; $i<=$baris; $i++)
{
	$barisreal = $baris-1;
        $k = $i-1;
        
// menghitung persentase progress
        $percent = intval($k/$barisreal * 100)."%";

// mengupdate progress
        echo '<script language="javascript">
        document.getElementById("progress").innerHTML="<div style=\"width:'.$percent.'; background-color:lightblue\">&nbsp;</div>";
        document.getElementById("info").innerHTML="'.$k.' Proses Upload.. ('.$percent.' selesai).";
        </script>';
	// baca data pada baris ke-i, kolom ke-1, pada sheet 3
$coa = $data->val($i, 1, 23);
	// baca data pada baris ke-i, kolom ke-2, pada sheet 1
	$nama = $data->val($i, 5, 23);
	$saldo_awal = $data->val($i, 8, 23);
	$debet= $data->val($i, 3, 23);
	$debet = str_replace(",","", $debet);
	$kredit = $data->val($i, 4, 23);
	$kredit = str_replace(",","", $kredit);
	$saldo_akhir = $data->val($i, 8, 23);	
    $gl="2006-$coa";   
	$unit="2006";	

$query = "INSERT INTO upload_jateng(rekening_gl,coa,nama,debet,kredit,saldo_awal,saldo_akhir,unit) VALUES ('$gl','$coa','$nama','$debet','$kredit','$saldo_awal','$saldo_akhir','$unit')";
	 $hasil = mysql_query($query);
if($hasil){
@mysql_query("delete from upload_jateng where debet=0 and kredit =0 and unit ='$unit'");
}
      flush();

//      kita tambahkan sleep agar ada penundaan, sehingga progress terbaca bila file yg diimport sedikit
//      pada prakteknya sleep dihapus aja karena bikin lama hehe
    //sleep(1);


}

$baris = $data->rowcount($sheet_index=24);
for ($i=11; $i<=$baris; $i++)
{
	$barisreal = $baris-1;
        $k = $i-1;
        
// menghitung persentase progress
        $percent = intval($k/$barisreal * 100)."%";

// mengupdate progress
        echo '<script language="javascript">
        document.getElementById("progress").innerHTML="<div style=\"width:'.$percent.'; background-color:lightblue\">&nbsp;</div>";
        document.getElementById("info").innerHTML="'.$k.' Proses Upload.. ('.$percent.' selesai).";
        </script>';
	// baca data pada baris ke-i, kolom ke-1, pada sheet 3
$coa = $data->val($i, 1, 24);
	// baca data pada baris ke-i, kolom ke-2, pada sheet 1
	$nama = $data->val($i, 5, 24);
	$saldo_awal = $data->val($i, 8, 24);
	$debet= $data->val($i, 3, 24);
	$debet = str_replace(",","", $debet);
	$kredit = $data->val($i, 4, 24);
	$kredit = str_replace(",","", $kredit);
	$saldo_akhir = $data->val($i, 8, 24);	
    $gl="2007-$coa";   
	$unit="2007";	

$query = "INSERT INTO upload_jateng(rekening_gl,coa,nama,debet,kredit,saldo_awal,saldo_akhir,unit) VALUES ('$gl','$coa','$nama','$debet','$kredit','$saldo_awal','$saldo_akhir','$unit')";
	 $hasil = mysql_query($query);
if($hasil){
@mysql_query("delete from upload_jateng where debet=0 and kredit =0 and unit ='$unit'");
}
      flush();

//      kita tambahkan sleep agar ada penundaan, sehingga progress terbaca bila file yg diimport sedikit
//      pada prakteknya sleep dihapus aja karena bikin lama hehe
    //sleep(1);


}

$baris = $data->rowcount($sheet_index=25);
for ($i=11; $i<=$baris; $i++)
{
	$barisreal = $baris-1;
        $k = $i-1;
        
// menghitung persentase progress
        $percent = intval($k/$barisreal * 100)."%";

// mengupdate progress
        echo '<script language="javascript">
        document.getElementById("progress").innerHTML="<div style=\"width:'.$percent.'; background-color:lightblue\">&nbsp;</div>";
        document.getElementById("info").innerHTML="'.$k.' Proses Upload.. ('.$percent.' selesai).";
        </script>';
	// baca data pada baris ke-i, kolom ke-1, pada sheet 3
$coa = $data->val($i, 1, 25);
	// baca data pada baris ke-i, kolom ke-2, pada sheet 1
	$nama = $data->val($i, 5, 25);
	$saldo_awal = $data->val($i, 8, 25);
	$debet= $data->val($i, 3, 25);
	$debet = str_replace(",","", $debet);
	$kredit = $data->val($i, 4, 25);
	$kredit = str_replace(",","", $kredit);
	$saldo_akhir = $data->val($i, 8, 25);	
    $gl="2008-$coa";   
	$unit="2008";	

$query = "INSERT INTO upload_jateng(rekening_gl,coa,nama,debet,kredit,saldo_awal,saldo_akhir,unit) VALUES ('$gl','$coa','$nama','$debet','$kredit','$saldo_awal','$saldo_akhir','$unit')";
	 $hasil = mysql_query($query);
if($hasil){
@mysql_query("delete from upload_jateng where debet=0 and kredit =0 and unit ='$unit'");
}
      flush();

//      kita tambahkan sleep agar ada penundaan, sehingga progress terbaca bila file yg diimport sedikit
//      pada prakteknya sleep dihapus aja karena bikin lama hehe
    //sleep(1);


}

$baris = $data->rowcount($sheet_index=26);
for ($i=11; $i<=$baris; $i++)
{
	$barisreal = $baris-1;
        $k = $i-1;
        
// menghitung persentase progress
        $percent = intval($k/$barisreal * 100)."%";

// mengupdate progress
        echo '<script language="javascript">
        document.getElementById("progress").innerHTML="<div style=\"width:'.$percent.'; background-color:lightblue\">&nbsp;</div>";
        document.getElementById("info").innerHTML="'.$k.' Proses Upload.. ('.$percent.' selesai).";
        </script>';
	// baca data pada baris ke-i, kolom ke-1, pada sheet 3
$coa = $data->val($i, 1, 26);
	// baca data pada baris ke-i, kolom ke-2, pada sheet 1
	$nama = $data->val($i, 5, 26);
	$saldo_awal = $data->val($i, 8, 26);
	$debet= $data->val($i, 3, 26);
	$debet = str_replace(",","", $debet);
	$kredit = $data->val($i, 4, 26);
	$kredit = str_replace(",","", $kredit);
	$saldo_akhir = $data->val($i, 8, 26);	
    $gl="2010-$coa";   
	$unit="2010";	

$query = "INSERT INTO upload_jateng(rekening_gl,coa,nama,debet,kredit,saldo_awal,saldo_akhir,unit) VALUES ('$gl','$coa','$nama','$debet','$kredit','$saldo_awal','$saldo_akhir','$unit')";
	 $hasil = mysql_query($query);
if($hasil){
@mysql_query("delete from upload_jateng where debet=0 and kredit =0 and unit ='$unit'");
}
      flush();

//      kita tambahkan sleep agar ada penundaan, sehingga progress terbaca bila file yg diimport sedikit
//      pada prakteknya sleep dihapus aja karena bikin lama hehe
    //sleep(1);


}

$baris = $data->rowcount($sheet_index=27);
for ($i=11; $i<=$baris; $i++)
{
	$barisreal = $baris-1;
        $k = $i-1;
        
// menghitung persentase progress
        $percent = intval($k/$barisreal * 100)."%";

// mengupdate progress
        echo '<script language="javascript">
        document.getElementById("progress").innerHTML="<div style=\"width:'.$percent.'; background-color:lightblue\">&nbsp;</div>";
        document.getElementById("info").innerHTML="'.$k.' Proses Upload.. ('.$percent.' selesai).";
        </script>';
	// baca data pada baris ke-i, kolom ke-1, pada sheet 3
$coa = $data->val($i, 1, 27);
	// baca data pada baris ke-i, kolom ke-2, pada sheet 1
	$nama = $data->val($i, 5, 27);
	$saldo_awal = $data->val($i, 8, 27);
	$debet= $data->val($i, 3, 27);
	$debet = str_replace(",","", $debet);
	$kredit = $data->val($i, 4, 27);
	$kredit = str_replace(",","", $kredit);
	$saldo_akhir = $data->val($i, 8, 27);	
    $gl="2027-$coa";   
	$unit="2027";	

$query = "INSERT INTO upload_jateng(rekening_gl,coa,nama,debet,kredit,saldo_awal,saldo_akhir,unit) VALUES ('$gl','$coa','$nama','$debet','$kredit','$saldo_awal','$saldo_akhir','$unit')";
	 $hasil = mysql_query($query);
if($hasil){
@mysql_query("delete from upload_jateng where debet=0 and kredit =0 and unit ='$unit'");
}
      flush();

//      kita tambahkan sleep agar ada penundaan, sehingga progress terbaca bila file yg diimport sedikit
//      pada prakteknya sleep dihapus aja karena bikin lama hehe
    //sleep(1);


}

$baris = $data->rowcount($sheet_index=28);
for ($i=11; $i<=$baris; $i++)
{
	$barisreal = $baris-1;
        $k = $i-1;
        
// menghitung persentase progress
        $percent = intval($k/$barisreal * 100)."%";

// mengupdate progress
        echo '<script language="javascript">
        document.getElementById("progress").innerHTML="<div style=\"width:'.$percent.'; background-color:lightblue\">&nbsp;</div>";
        document.getElementById("info").innerHTML="'.$k.' Proses Upload.. ('.$percent.' selesai).";
        </script>';
	// baca data pada baris ke-i, kolom ke-1, pada sheet 3
$coa = $data->val($i, 1, 28);
	// baca data pada baris ke-i, kolom ke-2, pada sheet 1
	$nama = $data->val($i, 5, 28);
	$saldo_awal = $data->val($i, 8, 28);
	$debet= $data->val($i, 3, 28);
	$debet = str_replace(",","", $debet);
	$kredit = $data->val($i, 4, 28);
	$kredit = str_replace(",","", $kredit);
	$saldo_akhir = $data->val($i, 8, 28);	
    $gl="2011-$coa";   
	$unit="2011";	

$query = "INSERT INTO upload_jateng(rekening_gl,coa,nama,debet,kredit,saldo_awal,saldo_akhir,unit) VALUES ('$gl','$coa','$nama','$debet','$kredit','$saldo_awal','$saldo_akhir','$unit')";
	 $hasil = mysql_query($query);
if($hasil){
@mysql_query("delete from upload_jateng where debet=0 and kredit =0 and unit ='$unit'");
}
      flush();

//      kita tambahkan sleep agar ada penundaan, sehingga progress terbaca bila file yg diimport sedikit
//      pada prakteknya sleep dihapus aja karena bikin lama hehe
    //sleep(1);


}

$baris = $data->rowcount($sheet_index=29);
for ($i=11; $i<=$baris; $i++)
{
	$barisreal = $baris-1;
        $k = $i-1;
        
// menghitung persentase progress
        $percent = intval($k/$barisreal * 100)."%";

// mengupdate progress
        echo '<script language="javascript">
        document.getElementById("progress").innerHTML="<div style=\"width:'.$percent.'; background-color:lightblue\">&nbsp;</div>";
        document.getElementById("info").innerHTML="'.$k.' Proses Upload.. ('.$percent.' selesai).";
        </script>';
	// baca data pada baris ke-i, kolom ke-1, pada sheet 3
$coa = $data->val($i, 1, 29);
	// baca data pada baris ke-i, kolom ke-2, pada sheet 1
	$nama = $data->val($i, 5, 29);
	$saldo_awal = $data->val($i, 8, 29);
	$debet= $data->val($i, 3, 29);
	$debet = str_replace(",","", $debet);
	$kredit = $data->val($i, 4, 29);
	$kredit = str_replace(",","", $kredit);
	$saldo_akhir = $data->val($i, 8, 0);	
    $gl="2012-$coa";   
	$unit="2012";	

$query = "INSERT INTO upload_jateng(rekening_gl,coa,nama,debet,kredit,saldo_awal,saldo_akhir,unit) VALUES ('$gl','$coa','$nama','$debet','$kredit','$saldo_awal','$saldo_akhir','$unit')";
	 $hasil = mysql_query($query);
if($hasil){
@mysql_query("delete from upload_jateng where debet=0 and kredit =0 and unit ='$unit'");
}
      flush();

//      kita tambahkan sleep agar ada penundaan, sehingga progress terbaca bila file yg diimport sedikit
//      pada prakteknya sleep dihapus aja karena bikin lama hehe
    //sleep(1);


}
$baris = $data->rowcount($sheet_index=30);
for ($i=11; $i<=$baris; $i++)
{
	$barisreal = $baris-1;
        $k = $i-1;
        
// menghitung persentase progress
        $percent = intval($k/$barisreal * 100)."%";

// mengupdate progress
        echo '<script language="javascript">
        document.getElementById("progress").innerHTML="<div style=\"width:'.$percent.'; background-color:lightblue\">&nbsp;</div>";
        document.getElementById("info").innerHTML="'.$k.' Proses Upload.. ('.$percent.' selesai).";
        </script>';
	// baca data pada baris ke-i, kolom ke-1, pada sheet 3
$coa = $data->val($i, 1, 30);
	// baca data pada baris ke-i, kolom ke-2, pada sheet 1
	$nama = $data->val($i, 5, 30);
	$saldo_awal = $data->val($i, 8, 30);
	$debet= $data->val($i, 3, 30);
	$debet = str_replace(",","", $debet);
	$kredit = $data->val($i, 4, 30);
	$kredit = str_replace(",","", $kredit);
	$saldo_akhir = $data->val($i, 8, 30);	
    $gl="2013-$coa";   
	$unit="2013";	

$query = "INSERT INTO upload_jateng(rekening_gl,coa,nama,debet,kredit,saldo_awal,saldo_akhir,unit) VALUES ('$gl','$coa','$nama','$debet','$kredit','$saldo_awal','$saldo_akhir','$unit')";
	 $hasil = mysql_query($query);
if($hasil){
@mysql_query("delete from upload_jateng where debet=0 and kredit =0 and unit ='$unit'");
}
      flush();

//      kita tambahkan sleep agar ada penundaan, sehingga progress terbaca bila file yg diimport sedikit
//      pada prakteknya sleep dihapus aja karena bikin lama hehe
    //sleep(1);


}

$baris = $data->rowcount($sheet_index=31);
for ($i=11; $i<=$baris; $i++)
{
	$barisreal = $baris-1;
        $k = $i-1;
        
// menghitung persentase progress
        $percent = intval($k/$barisreal * 100)."%";

// mengupdate progress
        echo '<script language="javascript">
        document.getElementById("progress").innerHTML="<div style=\"width:'.$percent.'; background-color:lightblue\">&nbsp;</div>";
        document.getElementById("info").innerHTML="'.$k.' Proses Upload.. ('.$percent.' selesai).";
        </script>';
	// baca data pada baris ke-i, kolom ke-1, pada sheet 3
$coa = $data->val($i, 1, 31);
	// baca data pada baris ke-i, kolom ke-2, pada sheet 1
	$nama = $data->val($i, 5, 31);
	$saldo_awal = $data->val($i, 8, 31);
	$debet= $data->val($i, 3, 31);
	$debet = str_replace(",","", $debet);
	$kredit = $data->val($i, 4, 31);
	$kredit = str_replace(",","", $kredit);
	$saldo_akhir = $data->val($i, 8, 31);	
    $gl="2018-$coa";   
	$unit="2018";	

$query = "INSERT INTO upload_jateng(rekening_gl,coa,nama,debet,kredit,saldo_awal,saldo_akhir,unit) VALUES ('$gl','$coa','$nama','$debet','$kredit','$saldo_awal','$saldo_akhir','$unit')";
	 $hasil = mysql_query($query);
if($hasil){
@mysql_query("delete from upload_jateng where debet=0 and kredit =0 and unit ='$unit'");
}
      flush();

//      kita tambahkan sleep agar ada penundaan, sehingga progress terbaca bila file yg diimport sedikit
//      pada prakteknya sleep dihapus aja karena bikin lama hehe
    //sleep(1);


}

$baris = $data->rowcount($sheet_index=32);
for ($i=11; $i<=$baris; $i++)
{
	$barisreal = $baris-1;
        $k = $i-1;
        
// menghitung persentase progress
        $percent = intval($k/$barisreal * 100)."%";

// mengupdate progress
        echo '<script language="javascript">
        document.getElementById("progress").innerHTML="<div style=\"width:'.$percent.'; background-color:lightblue\">&nbsp;</div>";
        document.getElementById("info").innerHTML="'.$k.' Proses Upload.. ('.$percent.' selesai).";
        </script>';
	// baca data pada baris ke-i, kolom ke-1, pada sheet 3
$coa = $data->val($i, 1, 32);
	// baca data pada baris ke-i, kolom ke-2, pada sheet 1
	$nama = $data->val($i, 5, 32);
	$saldo_awal = $data->val($i, 8, 32);
	$debet= $data->val($i, 3, 32);
	$debet = str_replace(",","", $debet);
	$kredit = $data->val($i, 4, 32);
	$kredit = str_replace(",","", $kredit);
	$saldo_akhir = $data->val($i, 8, 32);	
    $gl="2029-$coa";   
	$unit="2029";	

$query = "INSERT INTO upload_jateng(rekening_gl,coa,nama,debet,kredit,saldo_awal,saldo_akhir,unit) VALUES ('$gl','$coa','$nama','$debet','$kredit','$saldo_awal','$saldo_akhir','$unit')";
	 $hasil = mysql_query($query);
if($hasil){
@mysql_query("delete from upload_jateng where debet=0 and kredit =0 and unit ='$unit'");
}
      flush();

//      kita tambahkan sleep agar ada penundaan, sehingga progress terbaca bila file yg diimport sedikit
//      pada prakteknya sleep dihapus aja karena bikin lama hehe
    //sleep(1);


}
$baris = $data->rowcount($sheet_index=33);
for ($i=11; $i<=$baris; $i++)
{
	$barisreal = $baris-1;
        $k = $i-1;
        
// menghitung persentase progress
        $percent = intval($k/$barisreal * 100)."%";

// mengupdate progress
        echo '<script language="javascript">
        document.getElementById("progress").innerHTML="<div style=\"width:'.$percent.'; background-color:lightblue\">&nbsp;</div>";
        document.getElementById("info").innerHTML="'.$k.' Proses Upload.. ('.$percent.' selesai).";
        </script>';
	// baca data pada baris ke-i, kolom ke-1, pada sheet 3
$coa = $data->val($i, 1, 33);
	// baca data pada baris ke-i, kolom ke-2, pada sheet 1
	$nama = $data->val($i, 5, 33);
	$saldo_awal = $data->val($i, 8, 33);
	$debet= $data->val($i, 3, 33);
	$debet = str_replace(",","", $debet);
	$kredit = $data->val($i, 4, 33);
	$kredit = str_replace(",","", $kredit);
	$saldo_akhir = $data->val($i, 8, 33);	
    $gl="2014-$coa";   
	$unit="2014";	

$query = "INSERT INTO upload_jateng(rekening_gl,coa,nama,debet,kredit,saldo_awal,saldo_akhir,unit) VALUES ('$gl','$coa','$nama','$debet','$kredit','$saldo_awal','$saldo_akhir','$unit')";
	 $hasil = mysql_query($query);
if($hasil){
@mysql_query("delete from upload_jateng where debet=0 and kredit =0 and unit ='$unit'");
}
      flush();

//      kita tambahkan sleep agar ada penundaan, sehingga progress terbaca bila file yg diimport sedikit
//      pada prakteknya sleep dihapus aja karena bikin lama hehe
    //sleep(1);


}

$baris = $data->rowcount($sheet_index=34);
for ($i=11; $i<=$baris; $i++)
{
	$barisreal = $baris-1;
        $k = $i-1;
        
// menghitung persentase progress
        $percent = intval($k/$barisreal * 100)."%";

// mengupdate progress
        echo '<script language="javascript">
        document.getElementById("progress").innerHTML="<div style=\"width:'.$percent.'; background-color:lightblue\">&nbsp;</div>";
        document.getElementById("info").innerHTML="'.$k.' Proses Upload.. ('.$percent.' selesai).";
        </script>';
	// baca data pada baris ke-i, kolom ke-1, pada sheet 3
$coa = $data->val($i, 1, 34);
	// baca data pada baris ke-i, kolom ke-2, pada sheet 1
	$nama = $data->val($i, 5, 34);
	$saldo_awal = $data->val($i, 8, 34);
	$debet= $data->val($i, 3, 34);
	$debet = str_replace(",","", $debet);
	$kredit = $data->val($i, 4, 34);
	$kredit = str_replace(",","", $kredit);
	$saldo_akhir = $data->val($i, 8, 34);	
    $gl="2015-$coa";   
	$unit="2015";	

$query = "INSERT INTO upload_jateng(rekening_gl,coa,nama,debet,kredit,saldo_awal,saldo_akhir,unit) VALUES ('$gl','$coa','$nama','$debet','$kredit','$saldo_awal','$saldo_akhir','$unit')";
	 $hasil = mysql_query($query);
if($hasil){
@mysql_query("delete from upload_jateng where debet=0 and kredit =0 and unit ='$unit'");
}
      flush();

//      kita tambahkan sleep agar ada penundaan, sehingga progress terbaca bila file yg diimport sedikit
//      pada prakteknya sleep dihapus aja karena bikin lama hehe
    //sleep(1);


}
$baris = $data->rowcount($sheet_index=35);
for ($i=11; $i<=$baris; $i++)
{
	$barisreal = $baris-1;
        $k = $i-1;
        
// menghitung persentase progress
        $percent = intval($k/$barisreal * 100)."%";

// mengupdate progress
        echo '<script language="javascript">
        document.getElementById("progress").innerHTML="<div style=\"width:'.$percent.'; background-color:lightblue\">&nbsp;</div>";
        document.getElementById("info").innerHTML="'.$k.' Proses Upload.. ('.$percent.' selesai).";
        </script>';
	// baca data pada baris ke-i, kolom ke-1, pada sheet 3
$coa = $data->val($i, 1, 35);
	// baca data pada baris ke-i, kolom ke-2, pada sheet 1
	$nama = $data->val($i, 5, 35);
	$saldo_awal = $data->val($i, 8, 35);
	$debet= $data->val($i, 3, 35);
	$debet = str_replace(",","", $debet);
	$kredit = $data->val($i, 4, 35);
	$kredit = str_replace(",","", $kredit);
	$saldo_akhir = $data->val($i, 8, 35);	
    $gl="2016-$coa";   
	$unit="2016";	

$query = "INSERT INTO upload_jateng(rekening_gl,coa,nama,debet,kredit,saldo_awal,saldo_akhir,unit) VALUES ('$gl','$coa','$nama','$debet','$kredit','$saldo_awal','$saldo_akhir','$unit')";
	 $hasil = mysql_query($query);
if($hasil){
@mysql_query("delete from upload_jateng where debet=0 and kredit =0 and unit ='$unit'");
}
      flush();

//      kita tambahkan sleep agar ada penundaan, sehingga progress terbaca bila file yg diimport sedikit
//      pada prakteknya sleep dihapus aja karena bikin lama hehe
    //sleep(1);


}

$baris = $data->rowcount($sheet_index=36);
for ($i=11; $i<=$baris; $i++)
{
	$barisreal = $baris-1;
        $k = $i-1;
        
// menghitung persentase progress
        $percent = intval($k/$barisreal * 100)."%";

// mengupdate progress
        echo '<script language="javascript">
        document.getElementById("progress").innerHTML="<div style=\"width:'.$percent.'; background-color:lightblue\">&nbsp;</div>";
        document.getElementById("info").innerHTML="'.$k.' Proses Upload.. ('.$percent.' selesai).";
        </script>';
	// baca data pada baris ke-i, kolom ke-1, pada sheet 3
$coa = $data->val($i, 1, 36);
	// baca data pada baris ke-i, kolom ke-2, pada sheet 1
	$nama = $data->val($i, 5, 36);
	$saldo_awal = $data->val($i, 8, 36);
	$debet= $data->val($i, 3, 36);
	$debet = str_replace(",","", $debet);
	$kredit = $data->val($i, 4, 36);
	$kredit = str_replace(",","", $kredit);
	$saldo_akhir = $data->val($i, 8, 36);	
    $gl="2017-$coa";   
	$unit="2017";	

$query = "INSERT INTO upload_jateng(rekening_gl,coa,nama,debet,kredit,saldo_awal,saldo_akhir,unit) VALUES ('$gl','$coa','$nama','$debet','$kredit','$saldo_awal','$saldo_akhir','$unit')";
	 $hasil = mysql_query($query);
if($hasil){
@mysql_query("delete from upload_jateng where debet=0 and kredit =0 and unit ='$unit'");
}
      flush();

//      kita tambahkan sleep agar ada penundaan, sehingga progress terbaca bila file yg diimport sedikit
//      pada prakteknya sleep dihapus aja karena bikin lama hehe
    //sleep(1);


}

$baris = $data->rowcount($sheet_index=37);
for ($i=11; $i<=$baris; $i++)
{
	$barisreal = $baris-1;
        $k = $i-1;
        
// menghitung persentase progress
        $percent = intval($k/$barisreal * 100)."%";

// mengupdate progress
        echo '<script language="javascript">
        document.getElementById("progress").innerHTML="<div style=\"width:'.$percent.'; background-color:lightblue\">&nbsp;</div>";
        document.getElementById("info").innerHTML="'.$k.' Proses Upload.. ('.$percent.' selesai).";
        </script>';
	// baca data pada baris ke-i, kolom ke-1, pada sheet 3
$coa = $data->val($i, 1, 37);
	// baca data pada baris ke-i, kolom ke-2, pada sheet 1
	$nama = $data->val($i, 5, 37);
	$saldo_awal = $data->val($i, 8, 37);
	$debet= $data->val($i, 3, 37);
	$debet = str_replace(",","", $debet);
	$kredit = $data->val($i, 4, 37);
	$kredit = str_replace(",","", $kredit);
	$saldo_akhir = $data->val($i, 8, 37);	
    $gl="2020-$coa";   
	$unit="2020";	

$query = "INSERT INTO upload_jateng(rekening_gl,coa,nama,debet,kredit,saldo_awal,saldo_akhir,unit) VALUES ('$gl','$coa','$nama','$debet','$kredit','$saldo_awal','$saldo_akhir','$unit')";
	 $hasil = mysql_query($query);
if($hasil){
@mysql_query("delete from upload_jateng where debet=0 and kredit =0 and unit ='$unit'");
}
      flush();

//      kita tambahkan sleep agar ada penundaan, sehingga progress terbaca bila file yg diimport sedikit
//      pada prakteknya sleep dihapus aja karena bikin lama hehe
    //sleep(1);


}
$baris = $data->rowcount($sheet_index=38);
for ($i=11; $i<=$baris; $i++)
{
	$barisreal = $baris-1;
        $k = $i-1;
        
// menghitung persentase progress
        $percent = intval($k/$barisreal * 100)."%";

// mengupdate progress
        echo '<script language="javascript">
        document.getElementById("progress").innerHTML="<div style=\"width:'.$percent.'; background-color:lightblue\">&nbsp;</div>";
        document.getElementById("info").innerHTML="'.$k.' Proses Upload.. ('.$percent.' selesai).";
        </script>';
	// baca data pada baris ke-i, kolom ke-1, pada sheet 3
$coa = $data->val($i, 1, 38);
	// baca data pada baris ke-i, kolom ke-2, pada sheet 1
	$nama = $data->val($i, 5, 38);
	$saldo_awal = $data->val($i, 8, 38);
	$debet= $data->val($i, 3, 38);
	$debet = str_replace(",","", $debet);
	$kredit = $data->val($i, 4, 38);
	$kredit = str_replace(",","", $kredit);
	$saldo_akhir = $data->val($i, 8, 38);	
    $gl="2021-$coa";   
	$unit="2021";	

$query = "INSERT INTO upload_jateng(rekening_gl,coa,nama,debet,kredit,saldo_awal,saldo_akhir,unit) VALUES ('$gl','$coa','$nama','$debet','$kredit','$saldo_awal','$saldo_akhir','$unit')";
	 $hasil = mysql_query($query);
if($hasil){
@mysql_query("delete from upload_jateng where debet=0 and kredit =0 and unit ='$unit'");
}
      flush();

//      kita tambahkan sleep agar ada penundaan, sehingga progress terbaca bila file yg diimport sedikit
//      pada prakteknya sleep dihapus aja karena bikin lama hehe
    //sleep(1);


}

$baris = $data->rowcount($sheet_index=39);
for ($i=11; $i<=$baris; $i++)
{
	$barisreal = $baris-1;
        $k = $i-1;
        
// menghitung persentase progress
        $percent = intval($k/$barisreal * 100)."%";

// mengupdate progress
        echo '<script language="javascript">
        document.getElementById("progress").innerHTML="<div style=\"width:'.$percent.'; background-color:lightblue\">&nbsp;</div>";
        document.getElementById("info").innerHTML="'.$k.' Proses Upload.. ('.$percent.' selesai).";
        </script>';
	// baca data pada baris ke-i, kolom ke-1, pada sheet 3
$coa = $data->val($i, 1, 39);
	// baca data pada baris ke-i, kolom ke-2, pada sheet 1
	$nama = $data->val($i, 5, 39);
	$saldo_awal = $data->val($i, 8, 39);
	$debet= $data->val($i, 3, 39);
	$debet = str_replace(",","", $debet);
	$kredit = $data->val($i, 4, 39);
	$kredit = str_replace(",","", $kredit);
	$saldo_akhir = $data->val($i, 8, 39);	
    $gl="2022-$coa";   
	$unit="2022";	

$query = "INSERT INTO upload_jateng(rekening_gl,coa,nama,debet,kredit,saldo_awal,saldo_akhir,unit) VALUES ('$gl','$coa','$nama','$debet','$kredit','$saldo_awal','$saldo_akhir','$unit')";
	 $hasil = mysql_query($query);
if($hasil){
@mysql_query("delete from upload_jateng where debet=0 and kredit =0 and unit ='$unit'");
}
      flush();

//      kita tambahkan sleep agar ada penundaan, sehingga progress terbaca bila file yg diimport sedikit
//      pada prakteknya sleep dihapus aja karena bikin lama hehe
    //sleep(1);


}

$baris = $data->rowcount($sheet_index=40);
for ($i=11; $i<=$baris; $i++)
{
	$barisreal = $baris-1;
        $k = $i-1;
        
// menghitung persentase progress
        $percent = intval($k/$barisreal * 100)."%";

// mengupdate progress
        echo '<script language="javascript">
        document.getElementById("progress").innerHTML="<div style=\"width:'.$percent.'; background-color:lightblue\">&nbsp;</div>";
        document.getElementById("info").innerHTML="'.$k.' Proses Upload.. ('.$percent.' selesai).";
        </script>';
	// baca data pada baris ke-i, kolom ke-1, pada sheet 3
$coa = $data->val($i, 1, 40);
	// baca data pada baris ke-i, kolom ke-2, pada sheet 1
	$nama = $data->val($i, 5, 40);
	$saldo_awal = $data->val($i, 8, 40);
	$debet= $data->val($i, 3, 40);
	$debet = str_replace(",","", $debet);
	$kredit = $data->val($i, 4, 40);
	$kredit = str_replace(",","", $kredit);
	$saldo_akhir = $data->val($i, 8, 40);	
    $gl="2023-$coa";   
	$unit="2023";	

$query = "INSERT INTO upload_jateng(rekening_gl,coa,nama,debet,kredit,saldo_awal,saldo_akhir,unit) VALUES ('$gl','$coa','$nama','$debet','$kredit','$saldo_awal','$saldo_akhir','$unit')";
	 $hasil = mysql_query($query);
if($hasil){
@mysql_query("delete from upload_jateng where debet=0 and kredit =0 and unit ='$unit'");
}
      flush();

//      kita tambahkan sleep agar ada penundaan, sehingga progress terbaca bila file yg diimport sedikit
//      pada prakteknya sleep dihapus aja karena bikin lama hehe
    //sleep(1);


}

$baris = $data->rowcount($sheet_index=41);
for ($i=11; $i<=$baris; $i++)
{
	$barisreal = $baris-1;
        $k = $i-1;
        
// menghitung persentase progress
        $percent = intval($k/$barisreal * 100)."%";

// mengupdate progress
        echo '<script language="javascript">
        document.getElementById("progress").innerHTML="<div style=\"width:'.$percent.'; background-color:lightblue\">&nbsp;</div>";
        document.getElementById("info").innerHTML="'.$k.' Proses Upload.. ('.$percent.' selesai).";
        </script>';
	// baca data pada baris ke-i, kolom ke-1, pada sheet 3
$coa = $data->val($i, 1, 41);
	// baca data pada baris ke-i, kolom ke-2, pada sheet 1
	$nama = $data->val($i, 5, 41);
	$saldo_awal = $data->val($i, 8, 41);
	$debet= $data->val($i, 3, 41);
	$debet = str_replace(",","", $debet);
	$kredit = $data->val($i, 4, 41);
	$kredit = str_replace(",","", $kredit);
	$saldo_akhir = $data->val($i, 8, 41);	
    $gl="2024-$coa";   
	$unit="2024";	

$query = "INSERT INTO upload_jateng(rekening_gl,coa,nama,debet,kredit,saldo_awal,saldo_akhir,unit) VALUES ('$gl','$coa','$nama','$debet','$kredit','$saldo_awal','$saldo_akhir','$unit')";
	 $hasil = mysql_query($query);
if($hasil){
@mysql_query("delete from upload_jateng where debet=0 and kredit =0 and unit ='$unit'");
}
      flush();

//      kita tambahkan sleep agar ada penundaan, sehingga progress terbaca bila file yg diimport sedikit
//      pada prakteknya sleep dihapus aja karena bikin lama hehe
    //sleep(1);


}
$baris = $data->rowcount($sheet_index=42);
for ($i=11; $i<=$baris; $i++)
{
	$barisreal = $baris-1;
        $k = $i-1;
        
// menghitung persentase progress
        $percent = intval($k/$barisreal * 100)."%";

// mengupdate progress
        echo '<script language="javascript">
        document.getElementById("progress").innerHTML="<div style=\"width:'.$percent.'; background-color:lightblue\">&nbsp;</div>";
        document.getElementById("info").innerHTML="'.$k.' Proses Upload.. ('.$percent.' selesai).";
        </script>';
	// baca data pada baris ke-i, kolom ke-1, pada sheet 3
$coa = $data->val($i, 1, 42);
	// baca data pada baris ke-i, kolom ke-2, pada sheet 1
	$nama = $data->val($i, 5, 42);
	$saldo_awal = $data->val($i, 8, 42);
	$debet= $data->val($i, 3, 42);
	$debet = str_replace(",","", $debet);
	$kredit = $data->val($i, 4, 42);
	$kredit = str_replace(",","", $kredit);
	$saldo_akhir = $data->val($i, 8, 42);	
    $gl="2026-$coa";   
	$unit="2026";	

$query = "INSERT INTO upload_jateng(rekening_gl,coa,nama,debet,kredit,saldo_awal,saldo_akhir,unit) VALUES ('$gl','$coa','$nama','$debet','$kredit','$saldo_awal','$saldo_akhir','$unit')";
	 $hasil = mysql_query($query);
if($hasil){
@mysql_query("delete from upload_jateng where debet=0 and kredit =0 and unit ='$unit'");
}
      flush();

//      kita tambahkan sleep agar ada penundaan, sehingga progress terbaca bila file yg diimport sedikit
//      pada prakteknya sleep dihapus aja karena bikin lama hehe
    //sleep(1);


}
$baris = $data->rowcount($sheet_index=43);
for ($i=11; $i<=$baris; $i++)
{
	$barisreal = $baris-1;
        $k = $i-1;
        
// menghitung persentase progress
        $percent = intval($k/$barisreal * 100)."%";

// mengupdate progress
        echo '<script language="javascript">
        document.getElementById("progress").innerHTML="<div style=\"width:'.$percent.'; background-color:lightblue\">&nbsp;</div>";
        document.getElementById("info").innerHTML="'.$k.' Proses Upload.. ('.$percent.' selesai).";
        </script>';
	// baca data pada baris ke-i, kolom ke-1, pada sheet 3
$coa = $data->val($i, 1, 43);
	// baca data pada baris ke-i, kolom ke-2, pada sheet 1
	$nama = $data->val($i, 5, 43);
	$saldo_awal = $data->val($i, 8, 43);
	$debet= $data->val($i, 3, 43);
	$debet = str_replace(",","", $debet);
	$kredit = $data->val($i, 4, 43);
	$kredit = str_replace(",","", $kredit);
	$saldo_akhir = $data->val($i, 8, 43);	
    $gl="2028-$coa";   
	$unit="2028";	

$query = "INSERT INTO upload_jateng(rekening_gl,coa,nama,debet,kredit,saldo_awal,saldo_akhir,unit) VALUES ('$gl','$coa','$nama','$debet','$kredit','$saldo_awal','$saldo_akhir','$unit')";
	 $hasil = mysql_query($query);
if($hasil){
@mysql_query("delete from upload_jateng where debet=0 and kredit =0 and unit ='$unit'");
}
      flush();

//      kita tambahkan sleep agar ada penundaan, sehingga progress terbaca bila file yg diimport sedikit
//      pada prakteknya sleep dihapus aja karena bikin lama hehe
    //sleep(1);


}
$baris = $data->rowcount($sheet_index=44);
for ($i=11; $i<=$baris; $i++)
{
	$barisreal = $baris-1;
        $k = $i-1;
        
// menghitung persentase progress
        $percent = intval($k/$barisreal * 100)."%";

// mengupdate progress
        echo '<script language="javascript">
        document.getElementById("progress").innerHTML="<div style=\"width:'.$percent.'; background-color:lightblue\">&nbsp;</div>";
        document.getElementById("info").innerHTML="'.$k.' Proses Upload.. ('.$percent.' selesai).";
        </script>';
	// baca data pada baris ke-i, kolom ke-1, pada sheet 3
$coa = $data->val($i, 1, 44);
	// baca data pada baris ke-i, kolom ke-2, pada sheet 1
	$nama = $data->val($i, 5, 44);
	$saldo_awal = $data->val($i, 8, 44);
	$debet= $data->val($i, 3, 44);
	$debet = str_replace(",","", $debet);
	$kredit = $data->val($i, 4, 44);
	$kredit = str_replace(",","", $kredit);
	$saldo_akhir = $data->val($i, 8, 44);	
    $gl="2030-$coa";   
	$unit="2030";	

$query = "INSERT INTO upload_jateng(rekening_gl,coa,nama,debet,kredit,saldo_awal,saldo_akhir,unit) VALUES ('$gl','$coa','$nama','$debet','$kredit','$saldo_awal','$saldo_akhir','$unit')";
	 $hasil = mysql_query($query);
if($hasil){
@mysql_query("delete from upload_jateng where debet=0 and kredit =0 and unit ='$unit'");
}
      flush();

//      kita tambahkan sleep agar ada penundaan, sehingga progress terbaca bila file yg diimport sedikit
//      pada prakteknya sleep dihapus aja karena bikin lama hehe
    //sleep(1);


}

$baris = $data->rowcount($sheet_index=45);
for ($i=11; $i<=$baris; $i++)
{
	$barisreal = $baris-1;
        $k = $i-1;
        
// menghitung persentase progress
        $percent = intval($k/$barisreal * 100)."%";

// mengupdate progress
        echo '<script language="javascript">
        document.getElementById("progress").innerHTML="<div style=\"width:'.$percent.'; background-color:lightblue\">&nbsp;</div>";
        document.getElementById("info").innerHTML="'.$k.' Proses Upload.. ('.$percent.' selesai).";
        </script>';
	// baca data pada baris ke-i, kolom ke-1, pada sheet 3
$coa = $data->val($i, 1, 45);
	// baca data pada baris ke-i, kolom ke-2, pada sheet 1
	$nama = $data->val($i, 5, 45);
	$saldo_awal = $data->val($i, 8, 45);
	$debet= $data->val($i, 3, 45);
	$debet = str_replace(",","", $debet);
	$kredit = $data->val($i, 4, 45);
	$kredit = str_replace(",","", $kredit);
	$saldo_akhir = $data->val($i, 8, 45);	
    $gl="2031-$coa";   
	$unit="2031";	

$query = "INSERT INTO upload_jateng(rekening_gl,coa,nama,debet,kredit,saldo_awal,saldo_akhir,unit) VALUES ('$gl','$coa','$nama','$debet','$kredit','$saldo_awal','$saldo_akhir','$unit')";
	 $hasil = mysql_query($query);


if($hasil){
@mysql_query("delete from upload_jateng where debet=0 and kredit =0 and unit ='$unit'");
}
      flush();

//      kita tambahkan sleep agar ada penundaan, sehingga progress terbaca bila file yg diimport sedikit
//      pada prakteknya sleep dihapus aja karena bikin lama hehe
    //sleep(1);

}
if ($hasil){

echo "<p>Proses import selesai</p>";
$query = "delete from upload_jateng where coa in('0',' ')";
	 $delete = mysql_query($query);  

$query = "insert into tabel_transaksi select 
unit as unit,
'' as id_transaksi,
CONCAT('BS/',unit,'-Gaji-','$tgl_cari') as kode_transaksi,
coa as kode_rekening,
'$tgl_cari' as tabel_transaksi,
'Bukti System' as jenis_transaksi,
nama as keterangan_transaksi,
debet as debet,
kredit as kredit,
'' as tanggal_posting,
'' as keterangan_posting,
unit as id_admin
from upload_jateng
";
	 $delete = mysql_query($query);  
	 
}
	


//3902000

	echo "<p>Proses selesai</p>";
	
	

    
        
//    hapus file xls yang udah dibaca
    unlink($_FILES['filepegawaiall']['name']);
}

?>
