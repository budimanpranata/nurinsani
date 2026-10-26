<html>
<head>
<title>Cari</title>
	<link rel="stylesheet" href="../layar.css" media="screen"/>  
    <link rel="stylesheet" href="../print.css" media="print"/>  
    <link rel="stylesheet" href="../tabel.css" />
       <style type="text/css">
	<!--
.header1 {color: #ffffff; background: #4682B4; font-weight: bold; font-family: Tahoma, Verdana; font-size: 13px; margin: 0px;}
.style1 {
	color: #FFFFFF;
	font-weight: bold;
}
-->
       </style>
</head>
<body onLoad="document.postform.elements['kode_cabang'].focus();">
<table width="100%" border="0" cellpadding="3" cellspacing="0" bgcolor="#669900">
  <tr>
    <td width="100%" bgcolor="#669900"><span class="style1">Cari Data Approve </span></td>
  </tr>
</table>
<form action="listapprove.php" method="post" name="postform">
  <table width="795" border="0">

<tr>
    <td width="203" height="23" bgcolor="#CCCCCC"><span class="style13"> <div align="left">Masukan Tanggal Pencarian </div></td>
    <td width="143" bgcolor="#CCCCCC"><div align="left">
      <input name="tgl_trn" type="text" id="tgl_trn" value="" size="15"/>
      <a href="javascript:void(0)" onClick="if(self.gfPop)gfPop.fPopCalendar(document.postform.tgl_trn);return false;" ><img src="../images/calendarIcon.jpg" alt="" name="popcal" width="16" height="15" border="0" align="absmiddle" id="popcal" /></a></div></td>
    <td width="65" bgcolor="#CCCCCC"><div align="left">Norek</div>
    <td width="118" bgcolor="#CCCCCC"><input name="nopen" type="text" id="nopen" value="" size="15"/>
      <td width="200" colspan="2"bgcolor="#CCCCCC"><input type="submit" value="Cari Data verifikasi" name="cari"></td> 
</tr>
</table>
</form>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp; </p>
<iframe width=174 height=189 name="gToday:normal:../calender/normal.js" id="gToday:normal:../calender/normal.js" src="../calender/ipopeng.htm" scrolling="no" frameborder="0" style="visibility:visible; z-index:999; position:absolute; top:-500px; left:-500px;">
</iframe>
</body>
</html>