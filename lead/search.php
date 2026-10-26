<html>
<head>
<title>Cari</title>
	<link rel="stylesheet" href="layar.css" media="screen"/>  
    <link rel="stylesheet" href="print.css" media="print"/>  
    <link rel="stylesheet" href="tabel.css" />
       <style type="text/css">
	<!--
.header1 {color: #ffffff; background: #4682B4; font-weight: bold; font-family: Tahoma, Verdana; font-size: 13px; margin: 0px;}
.style1 {color: #FF0000}
.style2 {
	color: #FFFFFF;
	font-weight: bold;
}
-->
       </style>
</head>
<body>
<table width="100%" border="0" cellpadding="3" cellspacing="0" bgcolor="#776a56">
  <tr>
    <td width="100%" bgcolor="#776a56"><span class="style2">Cari Data Karyawan </span></td>
  </tr>
</table>
<form action="view_data.php" method="post" name="postform">
  <table width="800" border="0">
<tr>
    <td width="238">&nbsp;</td>
    <td width="227">&nbsp;</td>
    <td width="321" colspan="2">&nbsp;</td>
</tr>
<tr>
    <td width="238" height="23" bgcolor="#CCCCCC">NIK<span class="style13"><span class="style1">*</span></td>
    <td bgcolor="#CCCCCC"><input name="nik" type="text" id="nik" value="" size="30" maxlength="8"/>      </td>
    <td colspan="2"bgcolor="#CCCCCC"><input type="submit" value="Tampilkan Data" name="cari"></td>
</tr>
<tr>
    <td width="238" height="23" bgcolor="#CCCCCC"><span class="style13">Nama</span></td>
    <td width="238" height="23" bordercolor="#CCCCCC" bgcolor="#CCCCCC"><input name="nama" type="text" id="nama" size="30" maxlength="40"></td>
    <td colspan="2" bgcolor="#CCCCCC">&nbsp;</td>
</tr>
<tr>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td colspan="2">&nbsp;</td>
</tr>
</table>
</form>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp; </p>
</body>
</html>