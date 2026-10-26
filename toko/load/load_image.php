<!DOCTYPE html>
<html>
<head>
<title>Upload Multiple Images Using jquery and PHP</title>
<!-------Including jQuery from Google ------>
<script src="jquery.min.js"></script>
<script src="script.js"></script>
<!------- Including CSS File ------>
<link rel="stylesheet" type="text/css" href="stylee.css">

<style type="text/css">
<!--
.style1 {font-weight: bold}
.style2 {
	color: #FFFFFF;
	font-weight: bold;
}
-->
</style>
<body>
<div id="maindiv">
  <table width="979" border="1" bgcolor="#669900">
    <tr>
      <td><span class="style2">Uplod Dokumen</span></td>
    </tr>
  </table>
<div id="formdiv">
<tr>
    <td width="100%" bgcolor="#776a56" class="style1">&nbsp;</td>
  </tr>

<form enctype="multipart/form-data" action="" method="post">
<div id="filediv"><input name="file[]" type="file" id="file"/>
</div>
<input type="button" id="add_more" class="upload" value="Add More Files"/>
<input type="submit" value="Upload File" name="submit" id="upload" class="upload"/>
</form>
<!------- Including PHP Script here ------>
<?php require_once __DIR__ . '/../../php8_compat.php'; include "upload.php"; ?>
</div>
</div>
</body>
</html>