<html>
<head>
<title>Cari</title>
	<link rel="stylesheet" href="layar.css" media="screen"/>  
    <link rel="stylesheet" href="print.css" media="print"/>  
    <link rel="stylesheet" href="tabel.css" />
       <style type="text/css">
	<!--
.header1 {color: #ffffff; background: #4682B4; font-weight: bold; font-family: Tahoma, Verdana; font-size: 13px; margin: 0px;}
body {
	background-image: url();
}
.style1 {
	color: #CC9933;
	font-weight: bold;
}
.style2 {color: #FFFFFF}
-->
       </style>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"></head>
<body>
<?php require_once __DIR__ . '/../php8_compat.php';
include "conn.php";
$koneksi=open_connection();
$nik= $_POST ['nik'];
$nama= $_POST ['nama'];

if ($_POST['cari'])
{

if (empty($nik)){
	echo "<script>alert('Maaf,.salah satu harus di isi !!!');</script>";
         echo "<script language='javascript'>document.location.href='cari_karyawan.php'</script>";
		 }
	
	else{
$karyawan="SELECT * 
from person
INNER JOIN branch ON person.Branch=branch.branch WHERE NIK='$nik' or Nama='$nama'";
$select_query=mssql_query($karyawan); 
		}
?>
<div align="left">
  <table style="font-size:11px;font-family:'arial';text-align:center;" border='1' width="80%" align='center' >
  </table>
  <table width="104%" border="0" cellpadding="3" cellspacing="0" bgcolor="#776a56">
    <tr>
      <td width="100%" class="style1 style2">Data Karyawan BTPN</td>
    </tr>
  </table>
  <table width="1141" class="datatable">

    <tr class="header1">
      <th width="114">Photo</th>
      <th width="138">Nama</th>
      <th width="68">Nik</th>
      <th width="115">Jabatan</th>
      <th width="85">Tanggal TMT </th>
      <th width="74">Atasan 1 </th>
      <th width="91">Atasan 2 </th>
      <th width="123">Status Karawan </th>
	   <th width="123">Penempatan Kerja</th>
	   <th width="87">Status</th>
	  <th width="75">Edit </th> 
    </tr>
    <?php
	
	//menampilkan data
		while($data=mssql_fetch_array($select_query)){
	?>
    <tr>
	<td><?php echo $data['photo'];?></td>   
  	 <td><?php echo $data['Nama']; ?></td>
      <td><?php echo $data['NIK'];  ?></td>
      <td><?php echo $data['Jabatan'];?></td>
      <td><?php echo $data['tmt_sk']; ?></td>
      <td><?php echo $data['atasan1']; ?></td>
      <td><?php echo $data['atasan2']; ?></td>
      <td><?php echo $data['Vendor'];?></td>
	  <td><?php echo $data['Branch'];?></td>
	 <td><?php echo $data['status'];?></td>
	 <td>
	   <div align="center"><a href="edit_karyawan.php?nik=<?php echo $data['nik'];?>" class="altrow" onClick="return confirm('Apakah Anda yakin?')">Edit</a>
       </div></td>
	</tr>
    <?php
		
	}
	
	?>
	      <tr>
      <td colspan="4" align="center"><?php
		//jika data tidak ditemukan
		if(mssql_num_rows($select_query)==0){
			echo "<font color=red><blink>Data tidak Ditemukan!</blink></font>";
		}
	}
		?>
		</td>
    	</tr>
  </table>
</div>
</body>
</html>