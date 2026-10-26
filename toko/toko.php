<?php require_once __DIR__ . '/../php8_compat.php';
session_start();  
ob_start(); 
//cek apakah user sudah login
if(!isset($_SESSION['userid'])){
   header("location:../../index.html");
}

//cek level user
if($_SESSION['level']!="toko"){
   header("location:../../index.html");
}  


?>	
<html>
<head>

<LINK REL="SHORTCUT ICON" href="http://localhost/btpnsk/favicon.ico">
	<title>KSPPS Nur Insani</title>
	<meta name="description" content="Free Cross Browser Javascript DHTML Tree Menu Navigation">
	<meta name="keywords" content="JavaScript tree menu, DHTML tree menu, client side tree menu, table of contents, site map, web authoring, scripting, freeware, download, shareware, free software, DHTML, Free Tree Menu, site, navigation, html, web, netscape, explorer, IE, opera, DOM, control, cross browser, support, frames, target, download">
	<meta name="robots" content="index,follow">

</head>

<frameset rows="122,*,20" border="0">
	<frame name="header" src="header.html" marginwidth="0" marginheight="0" scrolling="no" frameborder="0" noresize>
	<frameset cols="5,210,*" border="0">
		<frame src="kanan.html" name="blank" frameborder="0" scrolling="yes" marginwidth="15" marginheight="15" id="blank"><?php echo "<h3>Welcome ".$_SESSION['userid']."</h3>";?>
		<frame src="tree.html" name="blank" id="blank" frameborder="0" marginwidth="0" marginheight="0">
		<frame src="semua.html" name="frameset" frameborder="1" scrolling="1" frameborder="1" id="frameset">
		<frame src="welcome.php"><frame src="UntitledFrame-8.html"><frame src="UntitledFrame-9.html"></frameset><?php echo "<h3>Welcome ".$_SESSION['userid']."</h3>";?>
	<frame name="footer" src="footer.html" marginwidth="15" marginheight="15" scrolling="no" frameborder="0" noresize>
</frameset><noframes></noframes>

<body>

</body>
</html>
