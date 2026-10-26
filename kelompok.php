<?php require_once __DIR__ . '/php8_compat.php';
session_start();
//cek apakah user sudah login
if(!isset($_SESSION['userid'])){
     header("location:index.html");//jika belum login jangan lanjut..
}
require_once "conn2.php";
 $koneksi=open_connection2();
$userid=$_SESSION['userid'];
$data = mysql_query ("select kode_branch from user where userid ='$userid'");
$r = mysql_fetch_array($data);
$branch=$r['kode_branch'];


?>
<html>
<head>
<script type="text/javascript" charset="utf-8">
function validateForm() 
{
 var okSoFar=true 
 with (document.postform)
 {
  if (code_kel.value=="" && okSoFar)
  {
    okSoFar=false
    alert("Masukkan KODE KELOMPOK")
    code_kel.focus()
  }
  if (nama.value=="" && okSoFar)
  {
    okSoFar=false
	alert("Masukkan NAMA KELOMPOK")
    nama_kel.focus()
  }
      if (okSoFar==true)  submit();
 }
}

		</script>
<script type="text/javascript" src="jquery.js"></script>
	<script>
	function suggest(inputString){
		if(inputString.length == 0) {
			$('#suggestions').fadeOut();
		} else {
		$('#country').addClass('load');
			$.post("autosuggest.php", {queryString: ""+inputString+""}, function(data){
				if(data.length >0) {
					$('#suggestions').fadeIn();
					$('#suggestionsList').html(data);
					$('#country').removeClass('load');
				}
			});
		}
	}
	
	function fill(thisValue) {
		$('#nama').val(thisValue);	
		setTimeout("$('#suggestions').fadeOut();", 100);
	}
	
	function fill2(thisValue) {
		$('#code_kel').val(thisValue);
		setTimeout("$('#suggestions').fadeOut();", 100);
	}
	function fill3(thisValue) {
		$('#cao').val(thisValue);
		setTimeout("$('#suggestions').fadeOut();", 100);
	}
	function fill4(thisValue) {
		$('#alamat').val(thisValue);
		setTimeout("$('#suggestions').fadeOut();", 100);
	}
function fill5(thisValue) {
		$('#hari').val(thisValue);
		setTimeout("$('#suggestions').fadeOut();", 100);
	}
	</script>
<title>Cari</title>
	
	       <style type="text/css">
	<!--
.header1 {color: #ffffff; background: #4682B4; font-weight: bold; font-family: Tahoma, Verdana; font-size: 13px; margin: 0px;}
.style14 {	color: #FFFFFF;
	font-weight: bold;
}
.style1 {color: #CCCCCC}
.style15 {
	color: #000000;
	font-style: italic;
}
.style16 {	color: #FF0000;
	font-weight: bold;
}
.style19 {color: #CCCCCC; font-size: 12px; }
-->
       </style>
	   <style>
	#result {
		height:20px;
		font-size:12px;
		font-family:Arial, Helvetica, sans-serif;
		color:#333;
		padding:5px;
		margin-bottom:10px;
		background-color:#FFFF99;
	}
	#country{
		padding:3px;
		border:1px #CCC solid;
		font-size:12px;
	}
	.suggestionsBox {
		position: absolute;
		left: 350px;
		top:50px;
		margin: 26px 0px 0px 0px;
		width: 200px;
		padding:0px;
		background-color:#999999;
		border-top: 3px solid #999999;
		color: #fff;
	}
	.suggestionList {
		margin: 0px;
		padding: 0px;
	}
	.suggestionList ul li {
		list-style:none;
		margin: 0px;
		padding: 6px;
		border-bottom:1px dotted #666;
		cursor: pointer;
	}
	.suggestionList ul li:hover {
		background-color: #b3b3ff;
		color:#000;
	}
	ul {
		font-family:Arial, Helvetica, sans-serif;
		font-size:11px;
		color:#FFF;
		padding:0;
		margin:0;
	}
	
	.load{
	background-image:url(loader.gif);
	background-position:right;
	background-repeat:no-repeat;
	}
	
	#suggest {
		position:relative;
	}
	</style>
</head>

<body onLoad="document.postform.elements['nopen'].focus();">
<table width="100%" border="0" cellpadding="3" cellspacing="0" bgcolor="#4682B4">
  <tr>
    <td width="100%" bgcolor="#669900" class="style14">Update Kelompok </td>
  </tr>
</table>
<form action="update_kel.php" method="post" name="postform">
  <table width="1043" border="0">
<tr>
<div id="suggest">
    <td width="263" bgcolor="#CCCCCC">Kode Kelompok <span class="style16">* </span></td>
    <td width="349" bgcolor="#CCCCCC"><span class="style1">
      <input type="text" onKeyUp="suggest(this.value);" name="code_kel" autocomplete="off" onBlur="fill2();" id="code_kel" size="15"/> 
	 <div class="suggestionsBox" id="suggestions" style="display: none;"> <img src="arrow.png" style="position: relative; top: -12px; left: 30px;" alt="upArrow" />
	 <div class="suggestionList" id="suggestionsList"> </div>
    </span></td>
	 </div>
	
	 </div>
    <td width="417" colspan="2" bgcolor="#CCCCCC"><input name="cari" type="button" class="upload"onClick="javascript:validateForm()" value="Simpan"></td>
</tr>
<tr>
    <td bgcolor="#E8E8E8">Kode AO <span class="style16">* </span></td>
    <td bgcolor="#E8E8E8"><span class="style1">
     <select name="cao" id ="cao" type="text">
		  <option value="">- Select Kode AO -</option>
	  <?php
              		$query = "select * from ao where kode_unit='$branch' ";
					$result = mysql_query($query);
					while ($kanwil=mysql_fetch_array($result))
					{
			  		  	echo "<option value=$kanwil[0]>$kanwil[1]</option>";
					}
              		echo"</select>";?>
      </span></td>
<td colspan="2" bgcolor="#E8E8E8">&nbsp;</td>
</tr>
<tr>
    <td bgcolor="#CCCCCC">Nama Kelompok <span class="style16">* </span></td>
    <td bgcolor="#CCCCCC"><span class="style1">
      <input name="nama" type="text" id="nama" size="30" maxlength="30"/>
    </span></td>
    <td colspan="2" bgcolor="#CCCCCC">&nbsp;</td>
</tr>
<tr>
    <td bgcolor="#E8E8E8">Alamat Kelompok <span class="style16">* </span></td>
    <td bgcolor="#E8E8E8"><span class="style1">
     <textarea name="alamat" type="text" id="alamat" size="55"/></textarea>
	 <td colspan="2" bgcolor="#E8E8E8">&nbsp;</td>
</td>

<tr>
    <td bgcolor="#CCCCCC">Hari Minggon <span class="style16">* </span></td>
    <td bgcolor="#CCCCCC"><span class="style1">
      <input name="hari" type="text" id="hari" size="30" maxlength="30"/>
    </span></td>
    <td colspan="2" bgcolor="#CCCCCC">&nbsp;</td>
</tr>

</table>

</form>
<iframe width=174 height=189 name="gToday:normal:calender/normal.js" id="gToday:normal:calender/normal.js" src="calender/ipopeng.htm" scrolling="no" frameborder="0" style="visibility:visible; z-index:999; position:absolute; top:-500px; left:-500px;">
</iframe>
</body>
</html>
