<?php require_once __DIR__ . '/../php8_compat.php';
session_start();
ob_start();
error_reporting(0);
$userid=$_SESSION['userid'];
$tgl_system=$_SESSION['stgl_system'];
//cek apakah user sudah login

if(!isset($_SESSION['userid'])){
   header("location:index.html");
 exit; }
if($_SESSION['level']!="lead"){
   header("location:../index.html");
 exit; }

?>
    <script type="text/javascript" src="../jquery.js"></script>
    <script>
    function suggest(inputString){
        if(inputString.length == 0) {
            $('#suggestions').fadeOut();
        } else {
        $('#country').addClass('load');
            $.post("autosuggesst_rek_aju.php", {queryString: ""+inputString+""}, function(data){
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
    function fill3(thisValue) {
        $('#code_kel').val(thisValue);
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
<link rel="stylesheet" href="../tabel.css" />
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<style type="text/css">
<script type="text/javascript" src="../jquery.js"></script>
        <link href="../../style.css" rel="stylesheet" type="text/css" media="screen" />
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

<table width="100%" cellpadding="1" bordercolor="#CCCCCC" bgcolor="#669900">
  <tr>
    <td height="20" bordercolor="#FFFFFF" bgcolor="#669900" class="style3 style9">Status Pengajuan
    <div align="right"></div></td>
  </tr>
</table>

<table width="428" border="1" align="center">
</table>
  <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" name="postform">  <table width="543" border="0" align="center">
<tr bgcolor="#669900">
  <td height="10" colspan="4" nowrap><div align="center" class="style3 style9">Input Data </div></td>
  </tr>
<tr>
  <td height="10" nowrap bgcolor="#B3B3FF"><span class="style7">Status Pengajuan </span></td>
  <td nowrap><div align="center"><strong>:</strong></div></td>
  <td colspan="2" nowrap><select name="select" id="select" size="1">
    <option>Pilih Salah satu</option>
    <option value="01">Turun Plafond</option>
    <option value="02">Ajukan Kembali </option>
	<option value="03">Hapus Pangajuan </option>	
    </select></td>
</tr>
<tr>
    <td width="203" height="10" nowrap bgcolor="#B3B3FF"><div align="left" class="style5 style6">CIF </div></td>
    <td width="16" nowrap>        <div align="center"><strong>:</strong></div></td>
    <td width="310" colspan="2" nowrap>
     <div id="suggest">
                       <input type="text" onKeyUp="suggest(this.value);" name="kode_rekening"  onBlur="fill2();" id="kode" size="15"/>
                       <div class="suggestionsBox" id="suggestions" style="display: none;"> <img src="arrow.png" style="position: relative; top: -12px; left: 30px;" alt="upArrow" />
                       <div class="suggestionList" id="suggestionsList"> &nbsp; </div>
                       </div>
        
      <input type="text" disabled="disabled" name="nama_rekening" onBlur="fill();" id="country" />
</div></tr>
<tr>
<div class="tombol">
    <td class ="hasil" width="203" height="10" nowrap bgcolor="#B3B3FF"><div align="left" class="style5 style6">Harga Baru </div></td>
    <td class ="titik" width="16" nowrap>        <div align="center"><strong>:</strong></div></td>
    <td width="310" colspan="2" nowrap>
    
                       <input type="text" name="harga_baru"  onKeyPress="return wajibAngka(event)" onKeyUp="document.getElementById('format').innerHTML = formatCurrency(this.value);" id="harga_baru"  size="15"/>
                       
   <span id="format"></span></td>    
 </div> </tr>


<tr>
  <td height="10" colspan="4"><label></label></td>
  </tr>
<tr bgcolor="#B3B3FF">
  <td height="10">&nbsp; </td>
  <td><div align="center"></div></td>
  <td colspan="2"><input name="search" type="submit" id="submit2" value="Proses" size="40"></td>
</tr>
</table>
</form>

<table border="4" bgcolor="" width="100%" class="table1">
  <tr class="table1">
    <th>Norek</th>
    <th>Nama</th>
    <th>Tgl Akad</th>
    <th>Status Pengajuan</th>
    <th>Harga Baru</th>
    <th>Harga Lama</th>
    <th>Status</th>
  </tr>
  <?php
  include "../conn2.php";
  include "../function.php";
  $koneksi=open_connection2();
  $cif_kel=$_POST['kode_rekening'];
  $pilih=$_POST['select'];
  if($pilih=='01')
  {
	$status_pengajuan="Turun Plafond";
  }else{
	$status_pengajuan="Pengajuan";  
  }
  $tanggal=date("Y-m-d");
  $harga_baru=$_POST['harga_baru'];
 
            
  If (isset($_POST ['search']))
{
if (empty($cif_kel)||empty($pilih)){
    echo "<script>alert('Maaf,.isi CIF dan Jenis Pengajuan');</script>";
         echo "<script language='javascript'>document.location.href='buka_bokir.php'</script>";}
    
    else{
       $cek_cif = mysql_fetch_array(mysql_query("select Plafond,Cust_Short_name,Tenor,unit,cif from calon_loan WHERE cif='$cif_kel'")) or die(mysql_error());
        $plafond=$cek_cif['Plafond'];
		$selisih=$plafond - $harga_baru;
		$tenor=$cek_cif['Tenor'];
		$unit=$cek_cif['unit'];
		$cif=$cek_cif['cif'];
		$nama=mysql_real_escape_string ($cek_cif['Cust_Short_name']);
		$done='Done';
$ket ="Turun Plafond $cif $nama";
		
	
		
    switch($pilih)
    {
     case "01":
	 if($harga_baru > $plafond){
					echo "<script> alert ('Harga Baru lebih besar dari harga lama')</script>";		

						echo "<script language='javascript'>document.location.href='turun_pangajuan.php'</script>";									
				}else{
		$param_biaya = mysql_query ("select margin,tab,pla,jw  FROM param_biaya where jw ='$tenor' and pla='$harga_baru'");
						$pbiaya = mysql_fetch_array($param_biaya);
						$param_margin=$pbiaya['margin'];
						$param_tabung=$pbiaya['tab'];
						$param_tenor=$pbiaya['jw'];
						$param_pla=$pbiaya['pla'];
						if ($param_tenor==$tenor and $harga_baru==$param_pla)
						{
						$pokok=$harga_baru/$tenor;
						$margin=$harga_baru * ($param_margin/100);
						$ijaroh=$margin/$tenor;
						$angsuran=$ijaroh+$pokok;
						$uang =$param_tabung+$angsuran;
						$os =$harga_baru + $margin + (2500 * $tenor);	
						$margin=$harga_baru * ($param_margin/100) + (2500 * $tenor);

				} else {
							
						echo "<script> alert ('Parameter Pembiayaan tidak ditemukan')</script>";		

						echo "<script language='javascript'>document.location.href='turun_pangajuan.php'</script>";									
	
				}
			}	
    $kodetr=5;
            $kodetr=acakangkahuruf($kodetr);
            $kode_trans="BU/".$unit .$kodetr;
            
            $kode_transaksi="BU/".$kode_tr;
            //pencatatan du jurnal realasisi Piutang Murabahaha total jumlah Pokok dan margin
            @mysql_query("insert into jurnal_umum(nomor_jurnal,kode_transaksi,tanggal_selesai) values('$nomor_jurnal','$kode_trans','$tgl_system')");
           
            //Reverse Transaksi Wakalah  sebesar Plafond
			//Realiasasi Murabahah Debit pada PP Operasional sebesar Plafond
            @mysql_query("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
            values('$unit','$kode_trans','1962000','$tgl_system','Bukti SYSTEM','$ket','$plafond','$userid')");
           
            //Realiasasi Murabahah kredit pada Piutang Wakalah
            @mysql_query("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                    values('$unit','$kode_trans','1431000','$tgl_system','Bukti SYSTEM','$ket','$plafond','$userid')");
					
			//Proses Wakalah turun Plafon 
			//Realiasasi Murabahah Debit pada Piutang Wakalah  sebesar Turun Plafond
            @mysql_query("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
            values('$unit','$kode_trans','1431000','$tgl_system','Bukti SYSTEM','$ket','$harga_baru','$userid')");
           
            //Realiasasi Murabahah kredit pada PP Ops
            @mysql_query("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                    values('$unit','$kode_trans','1962000','$tgl_system','Bukti SYSTEM','$ket','$harga_baru','$userid')");
			
			@mysql_query("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
            values('$unit','$kode_trans','2500000','$tgl_system','Bukti SYSTEM','$ket','$selisih','$userid')");
           
            //Realiasasi Murabahah kredit pada PP Ops
            @mysql_query("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                    values('$unit','$kode_trans','1962000','$tgl_system','Bukti SYSTEM','$ket','$selisih','$userid')");
			 $cek=mysql_query("update calon_loan set plafond='$harga_baru',os='$os',angsuran='$angsuran',ijaroh='$ijaroh',bulat='$uang',pokok='$pokok',saldo_margin='$margin',bagi_hasil='$margin',status_app='MURAB' where cif ='$cif_kel'");
					
					
					
					
        if($cek){                
                
        echo "<script>alert('  Turun Plafond Selelsai diproses');</script>";
         $select = mysql_query("select * from calon_loan where cif='$cif_kel'");
            }else{
                echo "<script>alert('Turun Plafond Gagal di proses..');</script>";
            }
        
		break;
        case "02":
		       $cek_jenis = mysql_fetch_array(mysql_query("select nama_akad from jenis_akad WHERE unit='$unit'")) or die(mysql_error());
        $akad=$cek_jenis['nama_akad'];
	if($akad=='MUSYAROKAH'){
	$cek=mysql_query("update calon_loan set status_app='MUSYAROKAH' where cif ='$cif_kel'");


	}else{
	$cek=mysql_query("update calon_loan set status_app='PENDING' where cif ='$cif_kel'");

	}

		 
					
					
					
					
        if($cek){                
                
        echo "<script>alert('Proses Pengajuan Kembali Selesai');</script>";
         $select = mysql_query("select * from calon_loan where cif='$cif_kel'");
            }else{
                echo "<script>alert('Pengajuan Kembali Gagal di proses..');</script>";
            }
            
		break;
	case "03":
		 $cek=mysql_query("delete from calon_loan where cif ='$cif_kel'");
					
					
					
					
        if($cek){                
                
        echo "<script>alert('Proses Reject Selesai');</script>";
         $select = mysql_query("select * from calon_loan where cif='$cif_kel'");
            }else{
                echo "<script>alert('Pengajuan Kembali Gagal di proses..');</script>";
            }
            
		break;
	default:
    }
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
    <td class="style6"><?php echo $data['no_anggota']; ?></td>
    <td class="style6"><?php echo $data['Cust_Short_name'];  ?></td>
    <td class="style6"><?php echo tgl_indo ($data['tgl_akad']);?></td>
    <td class="style6"><?php echo $status_pengajuan; ?></td>
    <td class="style6"><?php echo number_format (compat_num($harga_baru)); ?></td>
    <td class="style6"><?php echo number_format (compat_num($plafond)); ?></td>
    <td class="style6"><?php echo $done; ?></td>
    
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
$('.titik').hide();
$('#tombol').hide();
$('#tabel').hide();
$('#harga_baru').hide();
$('#select').change(function(){
if($('#select').val()=='01'){
$('.hasil').show();  
$('.titik').show();  
$('#tombol').hide();
$('#baki').hide();
$('#harga_baru').show();
}else if($('#select').val()=='02'){
$('.hasil').hide();
$('.titik').hide();
$('#tombol').hide();
$('#baki').hide();
$('#harga_baru').hide();

}
});
});


</script>
 <script>
        function formatCurrency(num) {
            num = num.toString().replace(/\$|\,/g, '');
            num = num.toString().replace(/[^0-9]/g, '');
            if (isNaN(num))
                num = "0";
            sign = (num == (num = Math.abs(num)));
            num = Math.floor(num * 100 + 0.50000000001);
            cents = num % 100;
            num = Math.floor(num / 100).toString();
            if (cents < 10)
                cents = "0" + cents;
            for (var i = 0; i < Math.floor((num.length - (1 + i)) / 3); i++)
                num = num.substring(0, num.length - (4 * i + 3)) + '.' +
                num.substring(num.length - (4 * i + 3));
            return (((sign) ? '' : '-') + 'Rp. ' + num + '');
        }
		function wajibAngka(evt) {
 var charCode = (evt.which) ? evt.which : event.keyCode
 if (charCode > 31 && (charCode < 48 || charCode > 57))
 return false;
 }
    </script>
</body>

</html>


