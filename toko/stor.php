<?php require_once __DIR__ . '/../php8_compat.php';
session_start();
ob_start();
$userid=$_SESSION['userid'];
$tgl_system=$_SESSION['stgl_system'];
//cek apakah user sudah login
if(!isset($_SESSION['stgl_system'])){
echo "<script>alert('Maaf,.tanggal system belum di setup');</script>";

   header("location:param_tgl.php");
 exit; }
if(!isset($_SESSION['userid'])){
   header("location:index.html");
 exit; }
if($_SESSION['level']!="toko"){
   header("location:../index.html");
 exit; }

?>
<script type="text/javascript">
 function wajibAngka(evt) {
 var charCode = (evt.which) ? evt.which : event.keyCode
 if (charCode > 31 && (charCode < 48 || charCode > 57))
 return false;
 }
</script>
    <script type="text/javascript" src="jquery.js"></script>
    <script>
    function suggest(inputString){
        if(inputString.length == 0) {
            $('#suggestions').fadeOut();
        } else {
        $('#country').addClass('load');
            $.post("autosuggesst_rek.php", {queryString: ""+inputString+""}, function(data){
                if(data.length >0) {
                    $('#suggestions').fadeIn();
                    $('#suggestionsList').html(data);
                    $('#country').removeClass('load');
                }
            });
        }
    }
   
    function fill(thisValue) {
        $('#country').val(thisValue);
        setTimeout("$('#suggestions').fadeOut();", 100);
    }
   
    function fill2(thisValue) {
        $('#kode').val(thisValue);
        setTimeout("$('#suggestions').fadeOut();", 100);
    }
   
    </script>
<script>
    function formatCurrency(num) {
    num = num.toString().replace(/\$|\,/g,'');
    num = num.toString().replace(/[^0-9]/g,'');
    if(isNaN(num))
    num = "0";
    sign = (num == (num = Math.abs(num)));
    num = Math.floor(num*100+0.50000000001);
    cents = num%100;
    num = Math.floor(num/100).toString();
    if(cents<10)
    cents = "0" + cents;
    for (var i = 0; i < Math.floor((num.length-(1+i))/3); i++)
    num = num.substring(0,num.length-(4*i+3))+'.'+
    num.substring(num.length-(4*i+3));
    return (((sign)?'':'-') + 'Rp. ' + num + '');
    }
    </script>
   
</script>


<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Transaksi NI</title>
<link rel="stylesheet" href="tabel.css" />
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<style type="text/css">
<script type="text/javascript" src="jquery.js"></script>
        <link href="../style.css" rel="stylesheet" type="text/css" media="screen" />
        <link rel="stylesheet" href="../css/bootstrap.min.css"/>
<script>

<!--
.style3 {    color: #FFFFFF;
    font-size: 13px;
    font-weight: bold;
}
.header1 {color: #ffffff; background: #4682B4; font-weight: bold; font-family: Tahoma, Verdana; font-size: 13px; margin: 0px;}
body {
    background-image: url();
}
a:link {
    text-decoration: none;
}
a:visited {
    text-decoration: none;
}
a:hover {
    text-decoration: none;
}
a:active {
    text-decoration: none;
}
.style5 {font-family: Verdana, Arial, Helvetica, sans-serif}
.style6 {font-size: 12px}
.style7 {font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 12px; }
.style8 {font-size: 14px}
a {
    font-size: 12px;
}
.style9 {
    font-size: 16px;
    font-family: Verdana, Arial, Helvetica, sans-serif;
}
-->

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
        left: 0px;
        top:40px;
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
        background-color: #FC3;
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
    .style9 {
    font-size: 16px;
    font-family: Verdana, Arial, Helvetica, sans-serif;
}
.style3 {    color: #FFFFFF;
    font-size: 13px;
    font-weight: bold;
}
.header1 {color: #ffffff; background: #4682B4; font-weight: bold; font-family: Tahoma, Verdana; font-size: 13px; margin: 0px;}
body {
    background-image: url();
}
a:link {
    text-decoration: none;
}
a:visited {
    text-decoration: none;
}
a:hover {
    text-decoration: none;
}
a:active {
    text-decoration: none;
}
    .style10 {
    font-family: Verdana, Arial, Helvetica, sans-serif;
    font-size: 12px;
}

    .style11 {font-size: small}
    </style>
</head>

<table width="100%" cellpadding="1" bordercolor="#CCCCCC" bgcolor="#2F6B4F">
  <tr>
    <td height="20" bordercolor="#FFFFFF" bgcolor="#2F6B4F" class="style3 style9">Transaksi Setoran dan Penarikan
    <div align="right"></div></td>
  </tr>
</table>

<table width="428" border="1" align="center">
</table>
  <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" name="postform">  <table width="543" border="0" align="center">
<tr bgcolor="#2F6B4F">
  <td height="10" colspan="4" nowrap><div align="center" class="style3 style9">Input Data </div></td>
  </tr>
<tr>
    <td width="203" height="10" nowrap bgcolor="#B3B3FF"><div align="left" class="style5 style6">No Rerek</div></td>
    <td width="16" nowrap>        <div align="center"><strong>:</strong></div></td>
    <td width="310" colspan="2" nowrap>
     <div id="suggest">
                       <input type="text" onKeyUp="suggest(this.value);" name="kode_rekening" autocomplete="off" onBlur="fill2();" id="kode" size="40"/>
                       <div class="suggestionsBox" id="suggestions" style="display: none;"> <img src="arrow.png" style="position: relative; top: -12px; left: 30px;" alt="upArrow" />
                       <div class="suggestionList" id="suggestionsList"> &nbsp; </div>
                       </div>
       
      <input type="text" disabled="disabled" name="nama_rekening" onBlur="fill();" id="country" />
</div>
</tr>
<tr>
  <td height="10" nowrap bgcolor="#B3B3FF"><div align="left" class="style7">Nominal</div></td>
  <td nowrap>
   <div align="center"><strong>:      </strong></div></td>
  <td colspan="2" nowrap><input name="nama" type="text" id="nominal" value="" onKeyPress="return wajibAngka(event)" autocomplete="off" onKeyUp="document.getElementById('format').innerHTML = formatCurrency(this.value);" size="20"/>                     <span id="format"></span></td>
</tr>
<tr>
  <td height="10" nowrap bgcolor="#B3B3FF"><div align="left" class="style7">Keterangan</div></td>
  <td nowrap>
   <div align="center"><strong>:      </strong></div></td>
  <td colspan="2" nowrap><input name="ket" type="text" id="ket"  size="30"/>  </tr>
<tr>
  <td height="10" nowrap bgcolor="#B3B3FF"><span class="style7">Jenis Transaksi </span></td>
  <td nowrap><div align="center"><strong>:</strong></div></td>
  <td colspan="2" nowrap><select name="select" id="select" size="1">
    <option>Pilih Salah satu</option>
       <option value="03">Setoran Angsuran</option>
	   <option value="04">Setoran Kurang Angsuran Ke titipan </option>
	   <option value="05">Setoran Angsuran dari Titipan</option>
	   <option value="06">Pelunasan</option>

  </select></td>
</tr>

<tr>
  <td height="10" colspan="4">&nbsp;</td>
  </tr>
<tr bgcolor="#B3B3FF">
  <td height="10">&nbsp; </td>
  <td><div align="center"></div></td>
  <td colspan="2"><input name="search" type="submit" id="submit2" value="Input" size="40"></td>
</tr>
</table>
</form>
<iframe width=174 height=189 name="gToday:normal:calender/normal.js" id="gToday:normal:calender/normal.js" src="calender/ipopeng.htm" scrolling="no" frameborder="0" style="visibility:visible; z-index:999; position:absolute; top:-500px; left:-500px;">
</iframe>

<table border="2" bgcolor="" width="100%" class="table1">
  <tr class="table1">
    <th>NOREK</th>
    <th>NAMA</th>
    <th>TGL TRANSAKSI </th>
    <th>DEBIT</th>
    <th>KREDIT</th>
    <th>SALDO</th>
  </tr>
  <?php
  include "conn2.php";
  include "function.php";
  $koneksi=open_connection2();
  $norek=$_POST ['kode_rekening'];
  $nominal=$_POST['nama'];
  $baki=$_POST['baki'];
  $simpan=$_POST['simpan'];
  $tanggal=date("Y-m-d");
  $pilih=$_POST['select'];
  
 $user=mysql_query("select kode_branch from user where userid='$userid'");
			
			while($id_user=mysql_fetch_array($user)){
			
				$unit=$id_user['kode_branch'];
			} 
  
  
  //$buss_date=date("Y-m-d H:i:s");
$buss_date=$tgl_system;
  $keterangan=mysql_real_escape_string($_POST['ket']);

  $kel = mysql_query ("select code_kel,id_member,unit,os,Cust_Short_name,cao,angsuran,code_musa  FROM musawamah where code_musa ='$norek'");
$kkel = mysql_fetch_array($kel);
$code_kel = $kkel['code_kel'];
$id_cust = $kkel['id_member'];
$os = $kkel['os'];
$code_musa = $kkel['code_musa'];


$nama=mysql_real_escape_string ($kkel['Cust_Short_name']);
$ds=$kkel['kode_ao'];
$angsuran=$kkel['angsuran'];

$margin=5/100 * $nominal;
$sisa=$nominal - $angsuran;
$margin_titip=5/100 * $angsuran;

$margin_lunas=5/100 * $os;

$kodetr=7;
            $kodetr=acakangkahuruf($kodetr);
            $kode_trans="BU/".$unit.$kodetr;


     
           
  If (isset($_POST ['search']))
{
	if(mysql_num_rows($kel)){
if (empty($norek)||empty($pilih)||empty($nominal)){
    echo "<script>alert('Maaf,.isi Nomor REKENING dan Jenis Transaksi yang benar');</script>";
         echo "<script language='javascript'>document.location.href='stor.php'</script>";}
   
    else{
    switch($pilih)
    {
     
     
     case "03":
	  $ket="Setoran Angsuran An $nama $norek";
	  $jurnal_umum=mysql_fetch_array(mysql_query("SELECT max(nomor_jurnal) FROM jurnal_umum where unit ='$unit' ORDER BY tanggal_selesai DESC"));
                $nomor_jurnal=$jurnal_umum[0]+1;
	    
		$input ="Insert into list_toko (BUSS_DATE,NOREK,UNIT,id_member,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,blok,tgl_input,kode_transaksi)VALUES					   ('$tgl_system','$norek','$unit','$id_cust','$code_kel',0,'03','$nominal','$userid','$ket','','$cao','1','$tanggal','$kode_trans')";
            $inputtrnsk = mysql_query($input);
    
	$input ="Insert into list_toko (BUSS_DATE,NOREK,UNIT,id_member,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,blok,tgl_input,kode_transaksi)VALUES('$tgl_system','$norek','$unit','$id_cust','$code_kel','$angsuran','03',0,'$userid','$ket','','$cao','1','$tanggal','$kode_trans')";
            $inputtrnsk = mysql_query($input);
			
			
	 mysql_query("update musawamah set os = os - angsuran,saldo_margin=saldo_margin - ijaroh where code_musa='$code_musa'");
     		
			
            if($inputtrnsk){
           
                      

               
                //pencatatan du jurnal realasisi Piutang Murabahaha total jumlah Pokok dan margin
                mysql_query("insert into jurnal_masuk(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values('$nomor_jurnal','$kode_trans','$tgl_system','$unit')");
                mysql_query("insert  into tabel_transaksi_toko(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                values('$unit','$kode_trans','1120000','$tgl_system','Bukti SYSTEM','$ket','$nominal','$userid')");
				 mysql_query("insert  into tabel_transaksi_toko(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                values('$unit','$kode_trans','1422000','$tgl_system','Bukti SYSTEM','$ket','$margin','$userid')");
               
                
                mysql_query("insert  into tabel_transaksi_toko(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                values('$unit','$kode_trans','1412000','$tgl_system','Bukti SYSTEM','$ket','$angsuran','$userid')");
                //mysql_query("Update  tunggakan set margin =margin - '$ijaroh', pokok =pokok-'$pokok'where no_anggota  ='$norek'") or die(mysql_error("GAGAL INPUT"));
              mysql_query("insert  into tabel_transaksi_toko(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                values('$unit','$kode_trans','41001','$tgl_system','Bukti SYSTEM','$ket','$margin','$userid')");
				
		
		
			  echo "<script>alert('Nominal sudah ditambahkan pada angsuran member $norek');</script>";
               
                
            }
            else{
            echo "<script>alert('Maaf,.Porses Input Gagal');</script>";
            echo "<script language='javascript'>document.location.href='stor.php'</script>";
            }
			if($sisa>0){
				
				
                
                mysql_query("insert  into tabel_transaksi_toko(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                values('$unit','$kode_trans','2891000','$tgl_system','Bukti SYSTEM','Titipan Angusran AN $nama $norek','$sisa','$userid')");
				 $select1 = mysql_query("SET @bal = 0;");
    $select = mysql_query("SELECT list_toko.NOREK,list_toko.BUSS_DATE,musawamah.CUST_SHORT_NAME,DEBIT,KREDIT,@bal:=(@bal+KREDIT)-DEBIT as balance FROM list_toko
   LEFT JOIN musawamah on musawamah.id_member = list_toko.id_member
    where list_toko.id_member='$id_cust' order by list_toko.REFF");
				
			}else{
				  echo "<script>alert('Proses setoran selesai');</script>";
            echo "<script language='javascript'>document.location.href='stor.php'</script>";
			 
			}
			
			
     break;

	     case "04":
     $ket1="Setoran Titipan Angsuran An $nama $norek";
     $input ="Insert into list_toko (BUSS_DATE,NOREK,UNIT,id_member,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,blok,tgl_input,kode_transaksi)VALUES('$tgl_system','$norek','$unit','$id_cust','$code_kel',0,'01','$nominal','$userid','$ket1','','$cao','1','$tanggal','$kode_trans')";
            $inputtrnsk = mysql_query($input);
            if($inputtrnsk){
            $ket="Setoran Titipan Angsuran An $nama $norek";
                      
//escropw 2891000
                $jurnal_umum=mysql_fetch_array(mysql_query("SELECT max(nomor_jurnal) FROM jurnal_umum where unit ='$unit' ORDER BY tanggal_selesai DESC"));
                $nomor_jurnal=$jurnal_umum[0]+1;
                //pencatatan du jurnal realasisi Piutang Murabahaha total jumlah Pokok dan margin
                mysql_query("insert into jurnal_masuk(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values('$nomor_jurnal','$kode_trans','$tgl_system','$unit')");
                mysql_query("insert  into tabel_transaksi_toko(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                values('$unit','$kode_trans','1120000','$tgl_system','Bukti SYSTEM','$ket','$nominal','$userid')");
				
                
                mysql_query("insert  into tabel_transaksi_toko(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                values('$unit','$kode_trans','2891000','$tgl_system','Bukti SYSTEM','Titipan Angusiran AN $nama $norek','$nominal','$userid')");
                
				
		
			  echo "<script>alert('Nominal sudah ditambahkan pada angsuran member $norek');</script>";
                
				$select1 = mysql_query("SET @bal = 0;");
   $select = mysql_query("SELECT list_toko.NOREK,list_toko.BUSS_DATE,musawamah.CUST_SHORT_NAME,DEBIT,KREDIT,@bal:=(@bal+KREDIT)-DEBIT as balance FROM list_toko
   LEFT JOIN musawamah on musawamah.id_member = list_toko.id_member
    where list_toko.id_member='$id_cust' order by list_toko.REFF");
                
            }
            else{
            echo "<script>alert('Maaf,.Porses Input Gagal');</script>";
            echo "<script language='javascript'>document.location.href='stor.php'</script>";
            }
     break;
    
	case "05":
     $ket1="Setoran dari Titipan Angsuran An $nama $norek";
      $SA = mysql_query ("select sum(KREDIT)- sum(DEBIT) as SALDO  FROM list_toko where  id_member ='$id_cust'");
     $SAL = mysql_fetch_array($SA);
	 
     $SALDO = $SAL['SALDO'];
     

	 if ($SALDO < $angsuran){
            echo "<script>alert('Maaf,.Saldo Tidak Mencukupi saldo titipan adalah $SALDO');</script>";
            echo "<script language='javascript'>document.location.href='stor.php'</script>";
        }else{
	 
	 $input ="Insert into list_toko (BUSS_DATE,NOREK,UNIT,id_member,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,blok,tgl_input,kode_transaksi)VALUES('$tgl_system','$norek','$unit','$ciff','$code_kel','$angsuran','03',0,'$userid','$ket1','','$cao','1','$tanggal','$kode_trans')";
            $inputtrnsk = mysql_query($input);
            if($inputtrnsk){
            $ket="Setoran Angsuran dari titipan An $nama $norek";
                      

                $jurnal_umum=mysql_fetch_array(mysql_query("SELECT max(nomor_jurnal) FROM jurnal_umum where unit ='$unit' ORDER BY tanggal_selesai DESC"));
                $nomor_jurnal=$jurnal_umum[0]+1;
                //pencatatan du jurnal realasisi Piutang Murabahaha total jumlah Pokok dan margin
                mysql_query("insert into jurnal_masuk(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values('$nomor_jurnal','$kode_trans','$tgl_system','$unit')");
                mysql_query("insert  into tabel_transaksi_toko(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                values('$unit','$kode_trans','2891000','$tgl_system','Bukti SYSTEM','$ket','$angsuran','$userid')");
				 mysql_query("insert  into tabel_transaksi_toko(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                values('$unit','$kode_trans','1422000','$tgl_system','Bukti SYSTEM','$ket','$margin_titip','$userid')");
               
                
                mysql_query("insert  into tabel_transaksi_toko(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                values('$unit','$kode_trans','1412000','$tgl_system','Bukti SYSTEM','$ket','$angsuran','$userid')");
                //mysql_query("Update  tunggakan set margin =margin - '$ijaroh', pokok =pokok-'$pokok'where no_anggota  ='$norek'") or die(mysql_error("GAGAL INPUT"));
              mysql_query("insert  into tabel_transaksi_toko(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                values('$unit','$kode_trans','41001','$tgl_system','Bukti SYSTEM','$ket','$margin_titip','$userid')");
				 mysql_query("update musawamah set os = os - angsuran where code_musa='$code_musa'");
		
			  echo "<script>alert('Nominal sudah didebet untuk angsuran member $norek');</script>";
                $select1 = mysql_query("SET @bal = 0;");
				$select = mysql_query("SELECT list_toko.NOREK,list_toko.BUSS_DATE,musawamah.CUST_SHORT_NAME,DEBIT,KREDIT,@bal:=(@bal+KREDIT)-DEBIT as balance FROM list_toko
   LEFT JOIN musawamah on musawamah.id_member = list_toko.id_member
    where list_toko.id_member='$id_cust' order by list_toko.REFF");
                
            }
            else{
            echo "<script>alert('Maaf,.Porses Input Gagal');</script>";
            echo "<script language='javascript'>document.location.href='stor.php'</script>";
            }
		}
     break;
	 
	case "06":
     $ket1="Pelunasan AN $nama $norek";
      $SA = mysql_query ("select sum(KREDIT-DEBIT) as SALDO  FROM list_toko where id_member ='$id_cust'");
     $SAL = mysql_fetch_array($SA);
	   $SALDO = $SAL['SALDO'];

	 $total_lunas = $nominal + $SALDO;
      $total_es = $os - $nominal;

if ($total_lunas < $os){

            echo "<script>alert('Maaf, Saldo tidak mencukupi untuk pelunasan saldo $nama adalah $os');</script>";
            echo "<script language='javascript'>document.location.href='stor.php'</script>";
        
		}else{
	 
	$input ="Insert into list_toko (BUSS_DATE,NOREK,UNIT,id_member,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,blok,tgl_input,kode_transaksi)VALUES('$tgl_system','$norek','$unit','$id_cust','$code_kel',0,'03','$nominal','$userid','$ket1','','$cao','1','$tanggal','$kode_trans')";
            $inputtrnsk = mysql_query($input);
    
	$input ="Insert into list_toko (BUSS_DATE,NOREK,UNIT,id_member,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,blok,tgl_input,kode_transaksi)VALUES('$tgl_system','$norek','$unit','$id_cust','$code_kel','$os','03',0,'$userid','$ket1','','$cao','1','$tanggal','$kode_trans')";
            $inputtrnsk = mysql_query($input);
                      
			if($inputtrnsk){
                $jurnal_umum=mysql_fetch_array(mysql_query("SELECT max(nomor_jurnal) FROM jurnal_umum where unit ='$unit' ORDER BY tanggal_selesai DESC"));
                $nomor_jurnal=$jurnal_umum[0]+1;
                //pencatatan du jurnal realasisi Piutang Murabahaha total jumlah Pokok dan margin
                mysql_query("insert into jurnal_masuk(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values('$nomor_jurnal','$kode_trans','$tgl_system','$unit')");
                
				   mysql_query("insert  into tabel_transaksi_toko(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                values('$unit','$kode_trans','1120000','$tgl_system','Bukti SYSTEM','$ket1','$nominal','$userid')");
 					

		mysql_query("insert  into tabel_transaksi_toko(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                values('$unit','$kode_trans','2891000','$tgl_system','Bukti SYSTEM','$ket1','$total_es','$userid')");

		
				 mysql_query("insert  into tabel_transaksi_toko(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                values('$unit','$kode_trans','1422000','$tgl_system','Bukti SYSTEM','$ket1','$margin_lunas','$userid')");
               
                
                mysql_query("insert  into tabel_transaksi_toko(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                values('$unit','$kode_trans','1412000','$tgl_system','Bukti SYSTEM','$ket1','$os','$userid')");
                //mysql_query("Update  tunggakan set margin =margin - '$ijaroh', pokok =pokok-'$pokok'where no_anggota  ='$norek'") or die(mysql_error("GAGAL INPUT"));
              mysql_query("insert  into tabel_transaksi_toko(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                values('$unit','$kode_trans','41001','$tgl_system','Bukti SYSTEM','$ket1','$margin_lunas','$userid')");
				 mysql_query("update musawamah set os = os - '$os',saldo_margin=0 where code_musa='$code_musa'");
		
			  echo "<script>alert('Nominal sudah didebet untuk angsuran member $norek');</script>";
				
				$select1 = mysql_query("SET @bal = 0;");
    $select = mysql_query("SELECT list_toko.NOREK,list_toko.BUSS_DATE,musawamah.CUST_SHORT_NAME,DEBIT,KREDIT,@bal:=(@bal+KREDIT)-DEBIT as balance FROM list_toko
   LEFT JOIN musawamah on musawamah.id_member = list_toko.id_member
    where list_toko.id_member='$id_cust' order by list_toko.REFF");
            }
			
            else{
            echo "<script>alert('Maaf,.Porses Input Gagal');</script>";
            echo "<script language='javascript'>document.location.href='stor.php'</script>";
            }
		}
	
     break;
      default:
    }
        }
 }
            else{
            echo "<script>alert('Maaf,Nomor Rekening tidak ditemukan');</script>";
            echo "<script language='javascript'>document.location.href='stor.php'</script>";
            }

   
  ?>
 
  <?php
   
    //menampilkan data
    while($data = mysql_fetch_array($select)){

//menghitung hitung
//$birthdate = $data['Birth_date'];
//$age = ((time() - strtotime($birthdate))/(60*60*24*365.2425));

        ?>
  <tr>
    <td class="style6"><?php echo $data['NOREK']; ?></td>
    <td class="style6"><?php echo $data['CUST_SHORT_NAME'];  ?></td>
    <td class="style6"><?php echo tgl_indo ($data['BUSS_DATE']);?></td>
    <td class="style6"><?php echo number_format (compat_num($data['DEBIT'])); ?></td>
    <td class="style6"><?php echo number_format (compat_num($data['KREDIT'])); ?></td>
    <td class="style6"><?php echo number_format (compat_num($data['balance'])); ?></td>
   
  </tr>
  <?php
       
    }
    }
    ?>
 
</table>
<p>&nbsp;</p>

 <iframe width=174 height=189 name="gToday:normal:calender/normal.js" id="gToday:normal:calender/normal.js" src="calender/ipopeng.htm" scrolling="no" frameborder="0" style="visibility:visible; z-index:999; position:absolute; top:-500px; left:-500px;">
</iframe>
<script>
$(document).ready(function(){
$('.hasil').hide();
$('#tombol').hide();
$('#baki').hide();
$('#simpan').hide();
$('#select').change(function(){
if($('#select').val()=='01'){
$('.hasil').hide();   
$('#tombol').hide();
$('#baki').hide();
$('#simpan').hide();
}else if($('#select').val()=='02'){
$('.hasil').hide();
$('#tombol').hide();
$('#baki').hide();
$('#simpan').hide();
}else if($('#select').val()=='03'){
$('.hasil').hide();
$('#tombol').hide();
$('#baki').hide();
$('#simpan').hide();
}else if($('#select').val()=='04'){
$('.hasil').show();
$('#tombol').show();
$('#baki').show();
$('#simpan').show();
}
});
});
</script>

</body>

</html>
