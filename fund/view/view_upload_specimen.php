<?php require_once __DIR__ . '/../../php8_compat.php';
session_start();
$nopen = $_GET['nopen'];
$_SESSION['nopen'] = $nopen;
 
?>

<html>
<head>
<title>Upload Specimen</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<style type="text/css">
.gallery-bg {width: 900px;background-color: #E8E8E8;border-radius:4px;}
#gallery{padding: 10px;text-align:center;font-weight: bold;color: #E8E8E8;background-color: #E8E8E8;overflow:auto;border-top-left-radius:4px;border-top-right-radius:4px;}
#gallery img{padding: 20px;}
#uploadFormLayer{padding: 10px;}
.btnUpload {background-color: #3FA849;padding:5px 20px;border: #3FA849 1px solid;color: #FFFFFF;border-radius:4px;}
.inputFile {padding: 4px;background-color: #FFFFFF;border-radius:4px;}
.txt-subtitle {font-size:1.2em;}

<!--
.style1 {color: #CCCCCC}
.style14 {color: #FFFFFF;
	font-weight: bold;
}
.style19 {color: #CCCCCC; font-size: 12px; }
-->
</style>
<script src="jquery-2.1.1.min.js" type="text/javascript"></script>
<script type="text/javascript">
$(document).ready(function (e) {
	$("#uploadForm").on('submit',(function(e) {
		e.preventDefault();
		$.ajax({
        	url: "upload_img.php",
			type: "POST",
				data:  new FormData(this),
					contentType: false,
				cache: false,
			processData:false,
			success: function(data){
			$("#gallery").html(data);
		    },
		  	error: function(){} 	        
	   });
	}));
});
</script>
</head>

<body>
<div class="gallery-bg">
<form id="uploadForm" action="upload_img.php" method="post">
<table width="100%" border="0" cellpadding="3" cellspacing="0" bgcolor="#4682B4">
  <tr>
    <td width="100%" bgcolor="#44222" class="style14">Upload Dokumen </td>
  </tr>
</table>
<div id="gallery">No Images in Gallery</div>
<div id="uploadFormLayer">

<form action="upload.php" method="post" name="postform">
  <table width="547" border="0">
        <tr>
      <td width="285" bgcolor="#CCCCCC">Specimen 1</td>
      <td width="252" bgcolor="#CCCCCC"><span class="style1">
	  <input name="userImage[]" type="file" class="inputFile" />
      </span></td>
    </tr>
    <tr>
      <td bgcolor="#E8E8E8">Specimen 2</td>
      <td bgcolor="#E8E8E8"><span class="style1">
	  <input name="userImage[]" type="file" class="inputFile" />
      </span></td>
   
    <tr>
      <td bgcolor="#E8E8E8">&nbsp;</td>
      <td bgcolor="#E8E8E8"><input name="submit" type="submit" class="btnUpload" value="Submit" /></td>
    <input type="hidden" name="nopen" id="nopen">
	</tr>
  </table>
</form>
</body>
</html>
