<form name="myForm" id="myForm" onSubmit="return validateForm()" action="upload_jateng.php" method="post" enctype="multipart/form-data">
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
//koneksi ke database, username,password  dan namadatabase menyesuaikan 
db_ni();
mysql_select_db('nur_insani');

//memanggil file excel_reader
require "excel_reader.php";
$tgl_cari=$_SESSION['stgl_system'];
//$tgl_cari='2018-05-31';
//jika tombol import ditekan
if(isset($_POST['submit'])){

    $target = basename($_FILES['filepegawaiall']['name']) ;
    move_uploaded_file($_FILES['filepegawaiall']['tmp_name'], $target);
    
    $data = new Spreadsheet_Excel_Reader($_FILES['filepegawaiall']['name'],false);
    
//    menghitung jumlah baris file xls
    //$baris = $data->rowcount($sheet_index=0);
    
//    jika kosongkan data dicentang jalankan kode berikut
    if($_POST['drop']==1){
//             kosongkan tabel pegawai
             $truncate ="TRUNCATE TABLE upload_jateng";
             mysql_query($truncate);
			 $query = "	delete from tabel_master_konsol where tanggal_awal='$tgl_cari' and unit in (
	'2001',
	'2002',
	'2003',
	'2004',
	'2005',
	'2006',
	'2007',
	'2008',
	'2009',
	'2010',
	'2011',
	'2012',
	'2013',
	'2014',
	'2015',
	'2016',
	'2017',
	'2018',
	'2019',
	'2020',
	'0000')";
	 $delete = mysql_query($query);
$query = "update tabel_master_jateng set
awal_debet=0,
awal_kredit=0,
mut_debet=0,
mut_kredit=0,
sisa_debet=0,
sisa_kredit=0,
saldo_awal=0,
saldo_akhir=0";
$update_master = mysql_query($query);
	
	};
    
	
	
	$baris = $data->rowcount($sheet_index=0);

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
	// baca data pada baris ke-i, kolom ke-1, pada sheet 1
	$coa = $data->val($i, 2, 0);
	// baca data pada baris ke-i, kolom ke-2, pada sheet 1
	$nama = $data->val($i, 3, 0);
	$saldo_awal = $data->val($i, 5, 0);
	$debet= $data->val($i, 6, 0);
	$kredit = $data->val($i, 7, 0);
	$saldo_akhir = $data->val($i, 8, 0);	
    $gl="2001-$coa";   
	$unit="2001";	

$query = "INSERT INTO upload_jateng(rekening_gl,coa,nama,debet,kredit,saldo_awal,saldo_akhir,unit) VALUES ('$gl','$coa','$nama','$debet','$kredit','$saldo_awal','$saldo_akhir','$unit')";
	 $hasil = mysql_query($query);

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
	$coa = $data->val($i, 2, 1);
	// baca data pada baris ke-i, kolom ke-2, pada sheet 1
	$nama = $data->val($i, 3, 1);
	$saldo_awal = $data->val($i, 5, 1);
	$debet= $data->val($i, 6, 1);
	$kredit = $data->val($i, 7, 1);
	$saldo_akhir = $data->val($i, 8, 1);	
   $gl="2002-$coa";    

	$unit="2002";	

$query = "INSERT INTO upload_jateng(rekening_gl,coa,nama,debet,kredit,saldo_awal,saldo_akhir,unit) VALUES ('$gl','$coa','$nama','$debet','$kredit','$saldo_awal','$saldo_akhir','$unit')";
	 $hasil = mysql_query($query);
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
$coa = $data->val($i, 2, 2);
	// baca data pada baris ke-i, kolom ke-2, pada sheet 1
	$nama = $data->val($i, 3, 2);
	$saldo_awal = $data->val($i, 5, 2);
	$debet= $data->val($i, 6, 2);
	$kredit = $data->val($i, 7, 2);
	$saldo_akhir = $data->val($i, 8, 2);	
    $gl="2003-$coa";    

$unit="2003";	

$query = "INSERT INTO upload_jateng(rekening_gl,coa,nama,debet,kredit,saldo_awal,saldo_akhir,unit) VALUES ('$gl','$coa','$nama','$debet','$kredit','$saldo_awal','$saldo_akhir','$unit')";
	 $hasil = mysql_query($query);
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
$coa = $data->val($i, 2, 3);
	// baca data pada baris ke-i, kolom ke-2, pada sheet 1
	$nama = $data->val($i, 3, 3);
	$saldo_awal = $data->val($i, 5, 3);
	$debet= $data->val($i, 6, 0);
	$kredit = $data->val($i, 7, 0);
	$saldo_akhir = $data->val($i, 8, 3);	
    $gl="2004-$coa";    

$unit="2004";	

$query = "INSERT INTO upload_jateng(rekening_gl,coa,nama,debet,kredit,saldo_awal,saldo_akhir,unit) VALUES ('$gl','$coa','$nama','$debet','$kredit','$saldo_awal','$saldo_akhir','$unit')";
	 $hasil = mysql_query($query);
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
$coa = $data->val($i, 2, 4);
	// baca data pada baris ke-i, kolom ke-2, pada sheet 1
	$nama = $data->val($i, 3, 4);
	$saldo_awal = $data->val($i, 5, 4);
	$debet= $data->val($i, 6, 4);
	$kredit = $data->val($i, 7, 4);
	$saldo_akhir = $data->val($i, 8, 4);	
    $gl="2005-$coa";    

$unit="2005";	

$query = "INSERT INTO upload_jateng(rekening_gl,coa,nama,debet,kredit,saldo_awal,saldo_akhir,unit) VALUES ('$gl','$coa','$nama','$debet','$kredit','$saldo_awal','$saldo_akhir','$unit')";
	 $hasil = mysql_query($query);
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
$coa = $data->val($i, 2, 5);
	// baca data pada baris ke-i, kolom ke-2, pada sheet 1
	$nama = $data->val($i, 3, 5);
	$saldo_awal = $data->val($i, 5, 5);
	$debet= $data->val($i, 6, 5);
	$kredit = $data->val($i, 7, 5);
	$saldo_akhir = $data->val($i, 8, 5);	
    $gl="2006-$coa";    

$unit="2006";	

$query = "INSERT INTO upload_jateng(rekening_gl,coa,nama,debet,kredit,saldo_awal,saldo_akhir,unit) VALUES ('$gl','$coa','$nama','$debet','$kredit','$saldo_awal','$saldo_akhir','$unit')";
	 $hasil = mysql_query($query);
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
$coa = $data->val($i, 2, 6);
	// baca data pada baris ke-i, kolom ke-2, pada sheet 1
	$nama = $data->val($i, 3, 6);
	$saldo_awal = $data->val($i, 5, 6);
	$debet= $data->val($i, 6, 6);
	$kredit = $data->val($i, 7, 6);
	$saldo_akhir = $data->val($i, 8, 6);	
    $gl="2007-$coa";    

$unit="2007";	

$query = "INSERT INTO upload_jateng(rekening_gl,coa,nama,debet,kredit,saldo_awal,saldo_akhir,unit) VALUES ('$gl','$coa','$nama','$debet','$kredit','$saldo_awal','$saldo_akhir','$unit')";
	 $hasil = mysql_query($query);
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
$coa = $data->val($i, 2, 7);
	// baca data pada baris ke-i, kolom ke-2, pada sheet 1
	$nama = $data->val($i, 3, 7);
	$saldo_awal = $data->val($i, 5, 7);
	$debet= $data->val($i, 6, 7);
	$kredit = $data->val($i, 7, 7);
	$saldo_akhir = $data->val($i, 8, 7);	
    $gl="2008-$coa";    

$unit="2008";	

$query = "INSERT INTO upload_jateng(rekening_gl,coa,nama,debet,kredit,saldo_awal,saldo_akhir,unit) VALUES ('$gl','$coa','$nama','$debet','$kredit','$saldo_awal','$saldo_akhir','$unit')";
	 $hasil = mysql_query($query);
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
$coa = $data->val($i, 2, 8);
	// baca data pada baris ke-i, kolom ke-2, pada sheet 1
	$nama = $data->val($i, 3, 8);
	$saldo_awal = $data->val($i, 5, 8);
	$debet= $data->val($i, 6, 8);
	$kredit = $data->val($i, 7, 8);
	$saldo_akhir = $data->val($i, 8, 8);	
    $gl="2009-$coa";    

$unit="2009";	

$query = "INSERT INTO upload_jateng(rekening_gl,coa,nama,debet,kredit,saldo_awal,saldo_akhir,unit) VALUES ('$gl','$coa','$nama','$debet','$kredit','$saldo_awal','$saldo_akhir','$unit')";
	 $hasil = mysql_query($query);
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
$coa = $data->val($i, 2, 9);
	// baca data pada baris ke-i, kolom ke-2, pada sheet 1
	$nama = $data->val($i, 3, 9);
	$saldo_awal = $data->val($i, 5, 9);
	$debet= $data->val($i, 6, 9);
	$kredit = $data->val($i, 7, 9);
	$saldo_akhir = $data->val($i, 8, 9);	
    $gl="2010-$coa";    

$unit="2010";	

$query = "INSERT INTO upload_jateng(rekening_gl,coa,nama,debet,kredit,saldo_awal,saldo_akhir,unit) VALUES ('$gl','$coa','$nama','$debet','$kredit','$saldo_awal','$saldo_akhir','$unit')";
	 $hasil = mysql_query($query);
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
$coa = $data->val($i, 2, 10);
	// baca data pada baris ke-i, kolom ke-2, pada sheet 1
	$nama = $data->val($i, 3, 10);
	$saldo_awal = $data->val($i, 5, 10);
	$debet= $data->val($i, 6, 10);
	$kredit = $data->val($i, 7, 10);
	$saldo_akhir = $data->val($i, 8, 10);	
    $gl="2011-$coa";    

$unit="2011";	

$query = "INSERT INTO upload_jateng(rekening_gl,coa,nama,debet,kredit,saldo_awal,saldo_akhir,unit) VALUES ('$gl','$coa','$nama','$debet','$kredit','$saldo_awal','$saldo_akhir','$unit')";
	 $hasil = mysql_query($query);
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
$coa = $data->val($i, 2, 11);
	// baca data pada baris ke-i, kolom ke-2, pada sheet 1
	$nama = $data->val($i, 3, 11);
	$saldo_awal = $data->val($i, 5, 11);
	$debet= $data->val($i, 6, 11);
	$kredit = $data->val($i, 7, 11);
	$saldo_akhir = $data->val($i, 8, 11);	
    $gl="2012-$coa";    

$unit="2012";	

$query = "INSERT INTO upload_jateng(rekening_gl,coa,nama,debet,kredit,saldo_awal,saldo_akhir,unit) VALUES ('$gl','$coa','$nama','$debet','$kredit','$saldo_awal','$saldo_akhir','$unit')";
	 $hasil = mysql_query($query);
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
$coa = $data->val($i, 2, 12);
	// baca data pada baris ke-i, kolom ke-2, pada sheet 1
	$nama = $data->val($i, 3, 12);
	$saldo_awal = $data->val($i, 5, 12);
	$debet= $data->val($i, 6, 12);
	$kredit = $data->val($i, 7, 12);
	$saldo_akhir = $data->val($i, 8, 12);	
    $gl="2013-$coa";    

$unit="2013";	

$query = "INSERT INTO upload_jateng(rekening_gl,coa,nama,debet,kredit,saldo_awal,saldo_akhir,unit) VALUES ('$gl','$coa','$nama','$debet','$kredit','$saldo_awal','$saldo_akhir','$unit')";
	 $hasil = mysql_query($query);
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
$coa = $data->val($i, 2, 13);
	// baca data pada baris ke-i, kolom ke-2, pada sheet 1
	$nama = $data->val($i, 3, 13);
	$saldo_awal = $data->val($i, 5, 13);
	$debet= $data->val($i, 6, 13);
	$kredit = $data->val($i, 7, 13);
	$saldo_akhir = $data->val($i, 8, 13);	
    $gl="2014-$coa";    

$unit="2014";	

$query = "INSERT INTO upload_jateng(rekening_gl,coa,nama,debet,kredit,saldo_awal,saldo_akhir,unit) VALUES ('$gl','$coa','$nama','$debet','$kredit','$saldo_awal','$saldo_akhir','$unit')";
	 $hasil = mysql_query($query);
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
$coa = $data->val($i, 2, 14);
	// baca data pada baris ke-i, kolom ke-2, pada sheet 1
	$nama = $data->val($i, 3, 14);
	$saldo_awal = $data->val($i, 5, 14);
	$debet= $data->val($i, 6, 14);
	$kredit = $data->val($i, 7, 14);
	$saldo_akhir = $data->val($i, 8, 14);	
    $gl="2015-$coa";    

$unit="2015";	

$query = "INSERT INTO upload_jateng(rekening_gl,coa,nama,debet,kredit,saldo_awal,saldo_akhir,unit) VALUES ('$gl','$coa','$nama','$debet','$kredit','$saldo_awal','$saldo_akhir','$unit')";
	 $hasil = mysql_query($query);
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
$coa = $data->val($i, 2, 15);
	// baca data pada baris ke-i, kolom ke-2, pada sheet 1
	$nama = $data->val($i, 3, 15);
	$saldo_awal = $data->val($i, 5, 15);
	$debet= $data->val($i, 6, 15);
	$kredit = $data->val($i, 7, 15);
	$saldo_akhir = $data->val($i, 8, 15);	
    $gl="2016-$coa";    

$unit="2016";	

$query = "INSERT INTO upload_jateng(rekening_gl,coa,nama,debet,kredit,saldo_awal,saldo_akhir,unit) VALUES ('$gl','$coa','$nama','$debet','$kredit','$saldo_awal','$saldo_akhir','$unit')";
	 $hasil = mysql_query($query);
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
$coa = $data->val($i, 2, 16);
	// baca data pada baris ke-i, kolom ke-2, pada sheet 1
	$nama = $data->val($i, 3, 16);
	$saldo_awal = $data->val($i, 5, 16);
	$debet= $data->val($i, 6, 16);
	$kredit = $data->val($i, 7, 16);
	$saldo_akhir = $data->val($i, 8, 16);	
    $gl="2017-$coa";    

$unit="2017";	

$query = "INSERT INTO upload_jateng(rekening_gl,coa,nama,debet,kredit,saldo_awal,saldo_akhir,unit) VALUES ('$gl','$coa','$nama','$debet','$kredit','$saldo_awal','$saldo_akhir','$unit')";
	 $hasil = mysql_query($query);
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
$coa = $data->val($i, 2, 17);
	// baca data pada baris ke-i, kolom ke-2, pada sheet 1
	$nama = $data->val($i, 3, 17);
	$saldo_awal = $data->val($i, 5, 17);
	$debet= $data->val($i, 6, 17);
	$kredit = $data->val($i, 7, 17);
	$saldo_akhir = $data->val($i, 8, 17);	
    $gl="2018-$coa";    

$unit="2018";	

$query = "INSERT INTO upload_jateng(rekening_gl,coa,nama,debet,kredit,saldo_awal,saldo_akhir,unit) VALUES ('$gl','$coa','$nama','$debet','$kredit','$saldo_awal','$saldo_akhir','$unit')";
	 $hasil = mysql_query($query);
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
$coa = $data->val($i, 2, 18);
	// baca data pada baris ke-i, kolom ke-2, pada sheet 1
	$nama = $data->val($i, 3, 18);
	$saldo_awal = $data->val($i, 5, 18);
	$debet= $data->val($i, 6, 18);
	$kredit = $data->val($i, 7, 18);
	$saldo_akhir = $data->val($i, 8, 18);	
    $gl="2019-$coa";    

$unit="2019";	

$query = "INSERT INTO upload_jateng(rekening_gl,coa,nama,debet,kredit,saldo_awal,saldo_akhir,unit) VALUES ('$gl','$coa','$nama','$debet','$kredit','$saldo_awal','$saldo_akhir','$unit')";
	 $hasil = mysql_query($query);
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
$coa = $data->val($i, 2, 19);
	// baca data pada baris ke-i, kolom ke-2, pada sheet 1
	$nama = $data->val($i, 3, 19);
	$saldo_awal = $data->val($i, 5, 19);
	$debet= $data->val($i, 6, 19);
	$kredit = $data->val($i, 7, 19);
	$saldo_akhir = $data->val($i, 8, 19);	
    $gl="2020-$coa";    

$unit="2020";	

$query = "INSERT INTO upload_jateng(rekening_gl,coa,nama,debet,kredit,saldo_awal,saldo_akhir,unit) VALUES ('$gl','$coa','$nama','$debet','$kredit','$saldo_awal','$saldo_akhir','$unit')";
	 $hasil = mysql_query($query);
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
$coa = $data->val($i, 2, 20);
	// baca data pada baris ke-i, kolom ke-2, pada sheet 1
	$nama = $data->val($i, 3, 20);
	$saldo_awal = $data->val($i, 5, 20);
	$debet= $data->val($i, 6, 20);
	$kredit = $data->val($i, 7, 20);
	$saldo_akhir = $data->val($i, 8, 20);	
    $gl="0000-$coa";    

$unit="0000";	

$query = "INSERT INTO upload_jateng(rekening_gl,coa,nama,debet,kredit,saldo_awal,saldo_akhir,unit) VALUES ('$gl','$coa','$nama','$debet','$kredit','$saldo_awal','$saldo_akhir','$unit')";
	 $hasil = mysql_query($query);
	 flush();

//      kita tambahkan sleep agar ada penundaan, sehingga progress terbaca bila file yg diimport sedikit
//      pada prakteknya sleep dihapus aja karena bikin lama hehe
    //sleep(1);


}
if ($hasil){

echo "<p>Proses import selesai</p>";
$query = "delete from upload_jateng where coa in('0',' ')";
	 $delete = mysql_query($query);     
}
	
if($delete){
	
	$select_gr_sub = mysql_query ("Select rekening_gl,sum(debet) as mut_debet, sum(kredit) as mut_kredit, 
sum(saldo_awal) as saldo_awal, sum(saldo_akhir) as saldo_akhir from upload_jateng group by rekening_gl ");
while($data = mysql_fetch_array($select_gr_sub)){

$saldo_awal=$data['saldo_awal'];
$saldo_akhir=$data['saldo_akhir'];
$mut_debet=$data['mut_debet'];
$mut_kredit=$data['mut_kredit'];
$rekening_gl=$data['rekening_gl'];

$update=mysql_query ("UPDATE tabel_master_jateng set saldo_awal ='$saldo_awal',saldo_akhir='$saldo_akhir',mut_debet='$mut_debet',mut_kredit='$mut_kredit' where rekening_gl ='$rekening_gl'");
			}
echo "<script>alert('Porses udate saldo Sukses ');</script>";
			}	
			
			$select_gr_head = mysql_query ("Select unit, rekening_gl,sum(mut_debet) as mut_debet, sum(mut_kredit) as mut_kredit, 
sum(saldo_awal) as saldo_awal, sum(saldo_akhir) as saldo_akhir from tabel_master_jateng where gr_head > 0 group by gr_head,unit ");
while($data = mysql_fetch_array($select_gr_head)){

$saldo_awal=$data['saldo_awal'];
$saldo_akhir=$data['saldo_akhir'];
$mut_debet=$data['mut_debet'];
$mut_kredit=$data['mut_kredit'];
$rekening_gl=$data['rekening_gl'];
$unit=$data['unit'];

$update=mysql_query ("UPDATE tabel_master_jateng set saldo_awal ='$saldo_awal',saldo_akhir='$saldo_akhir',mut_debet='$mut_debet',mut_kredit='$mut_kredit' where rekening_gl ='$rekening_gl'");
			}
			echo "<script>alert('update Saldo Header Sukses ');</script>";

$select_gr_sub = mysql_query ("Select unit,rekening_gl,sum(mut_debet) as mut_debet, sum(mut_kredit) as mut_kredit, 
sum(saldo_awal) as saldo_awal, sum(saldo_akhir) as saldo_akhir from tabel_master_jateng where gr_sub > 0  group by gr_sub,unit ");
while($data = mysql_fetch_array($select_gr_sub)){

$saldo_awal=$data['saldo_awal'];
$saldo_akhir=$data['saldo_akhir'];
$mut_debet=$data['mut_debet'];
$mut_kredit=$data['mut_kredit'];
$rekening_gl=$data['rekening_gl'];
$unit=$data['unit'];
$update=mysql_query ("UPDATE tabel_master_jateng set saldo_awal ='$saldo_awal',saldo_akhir='$saldo_akhir',mut_debet='$mut_debet',mut_kredit='$mut_kredit' where rekening_gl ='$rekening_gl'");
			}	
			$update=mysql_query ("UPDATE tabel_master_jateng set tanggal_awal='$tgl_cari'");
			$update=mysql_query ("insert into tabel_master_konsol select * from tabel_master_jateng");
			
			
//total Pendapatan
$total_pendapatan=mysql_fetch_array(mysql_query("select sum(saldo_akhir) as saldo_akhir from tabel_master_konsol 
where kode_rekening='40000' and tanggal_awal='$tgl_cari'"));
$pendapatan=$total_pendapatan['saldo_akhir'];
//total Biaya
$total_biaya=mysql_fetch_array(mysql_query("select sum(saldo_akhir) as saldo_akhir  from tabel_master_konsol 
where kode_rekening='50000' and tanggal_awal='$tgl_cari'"));
$biaya=$total_biaya['saldo_akhir'];
//total pendapatan non operasional
$total_pendapan_n=mysql_fetch_array(mysql_query("select sum(saldo_akhir) as saldo_akhir  from tabel_master_konsol 
where kode_rekening='57000' and tanggal_awal='$tgl_cari'"));
$pendapatan_non=$total_pendapan_n['saldo_akhir'];

//total Beban
$total_beban=mysql_fetch_array(mysql_query("select sum(saldo_akhir) as saldo_akhir  from tabel_master_konsol 
where kode_rekening='58000' and tanggal_awal='$tgl_cari'"));
$beban=$total_beban['saldo_akhir'];

//Hitung Pendapatan dan beban
$shu_ops=$pendapatan - $biaya;
$shu_non_ops=$pendapatan_non - $beban;

$shu_sb_pajak=$shu_ops + $shu_non_ops;

//hitung sebelum pajak pusat
//total Pendapatan
$total_pendapatan_p=mysql_fetch_array(mysql_query("select sum(saldo_akhir) as saldo_akhir from tabel_master_konsol 
where kode_rekening='40000' and tanggal_awal='$tgl_cari' and unit ='0000'"));
$pendapatan_p=$total_pendapatan_p['saldo_akhir'];
//total Biaya
$total_biaya_p=mysql_fetch_array(mysql_query("select sum(saldo_akhir) as saldo_akhir  from tabel_master_konsol 
where kode_rekening='50000' and tanggal_awal='$tgl_cari' and unit ='0000'"));
$biaya_p=$total_biaya_p['saldo_akhir'];
//total pendapatan non operasional
$total_pendapan_n_p=mysql_fetch_array(mysql_query("select sum(saldo_akhir) as saldo_akhir  from tabel_master_konsol 
where kode_rekening='57000' and tanggal_awal='$tgl_cari'and unit ='0000'"));
$pendapatan_non_p=$total_pendapan_n_p['saldo_akhir'];

//total Beban
$total_beban_p=mysql_fetch_array(mysql_query("select sum(saldo_akhir) as saldo_akhir  from tabel_master_konsol 
where kode_rekening='58000' and tanggal_awal='$tgl_cari'and unit ='0000'"));
$beban_p=$total_beban_p['saldo_akhir'];

//Hitung Pendapatan dan beban
$shu_ops_p=$pendapatan_p - $biaya_p;
$shu_non_ops_p=$pendapatan_non_p - $beban_p;

$shu_sb_pajak_p=$shu_ops_p + $shu_non_ops_p;



//SHU_SEBELUMPAJAK
$sbg_jamu=mysql_fetch_array(mysql_query("select sum(saldo_akhir) as saldo_akhir from tabel_master_konsol where kode_rekening in('56211','56212','56311','56312') and tanggal_awal='$tgl_cari'"));

$smb_jamu=$sbg_jamu['saldo_akhir'];





//SHU sebelum pajak tahun berjalan fiskal
$shu_sbp=$shu_sb_pajak+$smb_jamu;

//Pendapatan
$totalp=$pendapatan;
//var_dump($totalp);


//parameter pajak 
$param_pajak='4800000000';
//persentase
$persen =($param_pajak/$totalp);



//taksiran pajak mendapat psilitas 
$taksiran_dapat=($shu_sbp * $persen);
//var_dump($taksiran_dapat);

//Taksiran tidak Mendapatan Pasilitas
$taksiran_tidak=$shu_sbp - $taksiran_dapat;
//var_dump($taksiran_tidak);
//Taksitan Pajaka
//25% X 50% X shu sebelum pajak
$tks_pajak=0.125* $taksiran_dapat;
//25 * b
$tsk_b=(25/100) * $taksiran_tidak;

$estimasi_taksiran=$tks_pajak + $tsk_b;
$shu_sb_pajak_p=$shu_sb_pajak_p-$estimasi_taksiran;
$update=mysql_query ("UPDATE tabel_master_konsol set saldo_akhir ='$shu_sb_pajak_p' where rekening_gl='0000-3902000' and tanggal_awal='$tgl_cari'");
$update=mysql_query ("UPDATE tabel_master_konsol set saldo_akhir ='$estimasi_taksiran',mut_kredit='$estimasi_taksiran',mut_debet=saldo_awal where rekening_gl='0000-2881000' and tanggal_awal='$tgl_cari'");
$update_laba=mysql_query ("UPDATE tabel_master_konsol set saldo_akhir ='$estimasi_taksiran',mut_debet='$estimasi_taksiran',mut_kredit=saldo_awal where rekening_gl='0000-59999' and tanggal_awal='$tgl_cari'");			

$update=mysql_query ("UPDATE tabel_master_konsol set saldo_akhir ='0' where rekening_gl in ('0000-2800000') and unit ='0000' and tanggal_awal='$tgl_cari'");			

//$saldo_584=mysql_fetch_array(mysql_query("select sum(saldo_akhir) as saldo_akhir from tabel_master_konsol where gr_sub='58400' and unit ='0000' and tanggal_awal='$tgl_cari'"));
//$s_584=$saldo_584['saldo_akhir'];
//$update=mysql_query ("UPDATE tabel_master_konsol set saldo_akhir ='$s_584' where rekening_gl='0000-58400' and tanggal_awal='$tgl_cari'");

//$saldo_580=mysql_fetch_array(mysql_query("select sum(saldo_akhir) as saldo_akhir from tabel_master_konsol where gr_head='58000' and unit ='0000' and tanggal_awal='$tgl_cari'"));
//$s_580=$saldo_580['saldo_akhir'];
//$update=mysql_query ("UPDATE tabel_master_konsol set saldo_akhir ='$s_580' where rekening_gl='0000-58000' and tanggal_awal='$tgl_cari'");

$saldo_280=mysql_fetch_array(mysql_query("select sum(saldo_akhir) as saldo_akhir from tabel_master_konsol where gr_sub='2800000' and unit ='0000' and tanggal_awal='$tgl_cari'"));
$s_280=$saldo_280['saldo_akhir'];
$update=mysql_query ("UPDATE tabel_master_konsol set saldo_akhir ='$s_280' where rekening_gl='0000-2800000' and tanggal_awal='$tgl_cari'");


//3902000

	echo "<p>Proses selesai</p>";
	
	

    
        
//    hapus file xls yang udah dibaca
    unlink($_FILES['filepegawaiall']['name']);
}

?>