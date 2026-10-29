<?php require_once __DIR__ . '/php8_compat.php';
session_start();
$userid=$_SESSION['userid'];
//cek apakah user sudah login
if(!isset($_SESSION['userid'])){
   header("location:index.html");
 exit; }

//cek level user
if($_SESSION['level']!="user"){
   header("location:index.html");
 exit; }
$length = '6';

//generate a random id. Encrypt it and set it as $random
$random = crypt(uniqid(rand(),1)); 

//if you want to remove any slashes
$random_no_slashes = strip_tags(stripslashes($random));

//remove any . and or reversing the string
$random_id = str_replace(".","",$random_no_slashes); 
$random_final = strrev(str_replace("/","",$random_id));

//take the first 10 characters from $random_final
$cif = substr($random_final,0,$length); 


include "conn2.php";
$koneksi=open_connection2();

$nopen =$_GET['nopen'];
$query= mysql_query("select No_Pegawai,Branch,Birth_date,Gaji_Bersih,Deal_type,Cust_Short_name,materai,btl,asuransi,Plafond,tenor,Suku_Bunga_flat,Deal_type,USERID
FROM loan where No_Pegawai ='$nopen' ");  
$data   = mysql_fetch_array($query); 
  $nama=$data['Cust_Short_name'];
  $branch=$data['Branch'];
$pla=$data['Plafond'];
$tenor=$data['tenor'];
$gaji=$data['Gaji_Bersih'];
$tgl_lahir=$data['Birth_date'];
$rate=$data['Suku_Bunga_flat'];
$deal_type=$data['Deal_type'];
$userid=$data['USERID'];
$bersih=$data['bersih'];
$asuransi=$data['asuransi'];
$btl=$data['btl'];
$angsuran=$data['angsuran'];
$materai=$data['materai	'];
 
?>
<html>
<head><title>Form Insert</title>
<link rel="stylesheet" type="text/css" href="./load/stylee.css">
<style type="text/css">
<!--
.header1 {color: #ffffff; background: #4682B4; font-weight: bold; font-family: Tahoma, Verdana; font-size: 13px; margin: 0px;}
.style1 {color: #CCCCCC}
.style5 {color: #FF0000}
.style9 {color: #FF0000; font-size: 14px; }
.style13 {font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 14px; }
.style14 {
	color: #FFFFFF;
	font-weight: bold;
}
.style16 {color: #FF0000;
	font-weight: bold;
}
.style19 {color: #CCCCCC; font-size: 12px; }
-->
</style>
</head>
<script type="text/javascript" charset="utf-8">
function validateForm() 
{
 var okSoFar=true 
 with (document.postform)
 {
  if (notlp.value=="" && okSoFar)
  {
    okSoFar=false
    alert("Input data no tlp")
    notlp.focus()
  }
  if (tlp_rumah.value=="" && okSoFar)
  {
    okSoFar=false
	alert("Input Nomor Telephone Rumah ")
    tlp_rumah.focus()
  }
  if (tempat_lahir.value=="" && okSoFar)
  {
    okSoFar=false
    alert("Masukan tempat lahir debitur")
    tempat_lahir.focus()
  }
if (tgl_skep.value=="" && okSoFar)
  {
    okSoFar=false
    alert("Masukan tanggal skep debitur")
    tgl_skep.focus()
  }
if (ibu.value=="" && okSoFar)
  {
    okSoFar=false
    alert("Masukan nama gadis Ibu kandung debitur")
    ibu.focus()
  }
if (npwp.value=="" && okSoFar)
  {
    okSoFar=false
    alert("Masukkan NPWP debitur")
    npwp.focus()
  }
if (jenis_pen.value=="" && okSoFar)
  {
    okSoFar=false
    alert("Masukkan Jenis Pensiun debitur")
    jenis_pen.focus()
  }
if (kd_jiwa.value=="" && okSoFar)
  {
    okSoFar=false
    alert("Masukkan Kode Jiwa debitur")
    kd_jiwa.focus()
  }
if (alamat.value=="" && okSoFar)
  {
    okSoFar=false
    alert("Masukkan Alamat debitur")
    alamat.focus()
  }
if (rt.value=="" && okSoFar)
  {
    okSoFar=false
    alert("Masukkan RT dan RW debitur")
    rt.focus()
  }
if (kd_pos.value=="" && okSoFar)
  {
    okSoFar=false
    alert("Masukkan Kode POS debitur")
    kd_pos.focus()
  }
if (lurah.value=="" && okSoFar)
  {
    okSoFar=false
    alert("Masukkan NPWP debitur")
    lurah.focus()
  }
if (kec.value=="" && okSoFar)
  {
    okSoFar=false
    alert("Masukkan Kecamatan debitur")
    kec.focus()
  }
if (status_kawin.value=="" && okSoFar)
  {
    okSoFar=false
    alert("Masukkan Status Kawin debitur")
    status_kawin.focus()
  }
if (id_expire.value=="" && okSoFar)
  {
    okSoFar=false
    alert("Masukkan Tanggal Kadaluarsa KTP debitur")
    id_expire.focus()
  }
if (ktp.value=="" && okSoFar)
  {
    okSoFar=false
    alert("Masukkan ktp debitur")
    ktp.focus()
  }
if (kode_jenis.selectedIndex=="" && okSoFar)
  {
    okSoFar=false
    alert("Masukkan ktp debitur")
    kode_jenis.focus()
  }
if (norek.value=="" && okSoFar)
  {
    okSoFar=false
    alert("Masukkan nomor rekening cair debitur")
    norek.focus()
  }

if (okSoFar==true)  submit();
 }
}
	</script>

<body onLoad="document.postform.elements['nopen'].focus();">
<body>
<form action="inputlog.php" method="post" name="postform">
<table border='1' width='65%' cellpadding='2'  cellspacing='2' bgcolor="#FFFFFF">
</table>
<table width="100%" border="0" cellpadding="3" cellspacing="0" bgcolor="#4682B4">
  <tr>
    <td width="100%" bgcolor="#2F6B4F" class="style1"><span class="style14">Entry Data Debitur </span></td>
  </tr>
</table>
<table width='100%' border='0' cellpadding='2'  cellspacing='2' bordercolor="#ECE9D8" bgcolor="#EAEAEA">
  <tr>
    <td width="23%" bgcolor="#CCCCCC"><span class="style13">Cabang </span><span class="style9">* </span></td>
    <td width="21%" bgcolor="#CCCCCC"><span class="style1">
      <input name="branch" type="text" id="branch" value="<?php echo $branch; ?>" size="30" maxlength="6"/>
    </span></td>
    <td width="22%" bgcolor="#CCCCCC"><span class="style13">Nama Gadis Ibu Kandung </span><span class="style9">* </span></td>
    <td width="34%" bgcolor="#CCCCCC"><input name="ibu" type="text" id="ibu" maxlength="20"></td>
  </tr>
  <tr>
    <td><span class="style13">Cif <span class="style5">* </span></span></td>
    <td><input name="cif" type="text" value="<?php echo $cif;?>" size="16" maxlength="6"/></td>
    <td><span class="style13">Agama </span><span class="style9">* </span></td>
    <td><select name="agama" id="agama">
      <option selected>Pilih Salah satu</option>
      <option value="Islam">Islam</option>
      <option value="Keristen">Keristen</option>
      <option value="Budha">Budha</option>
      <option value="Hindu">Hindu</option>
      <option value="Konghuci">Konghucu</option>
    </select></td>
  </tr>
   <tr>
     <td bgcolor="#CCCCCC"><span class="style13">Nama Sesuai KTP <span class="style5">* </span></span></td>
     <td bgcolor="#CCCCCC"><input name="nama" type="text" value="<?php echo $nama; ?>" size="30" maxlength="20"/></td>
     <td bgcolor="#CCCCCC"><span class="style13">Kewarganegaraan </span><span class="style9">* </span></td>
     <td bgcolor="#CCCCCC"><select name="negara" id="negara">
       <option selected>Pilih Salah satu</option>
       <option value="ID">Indonesia</option>
          </select></td>
   </tr>
   <tr>
     <td><span class="style13">Nama Pelapor <span class="style5">* </span></span></td>
     <td><input name="nama_pela" type="text" id="nama_pela" value="<?php echo $nama; ?>" size="30" maxlength="20"/></td>
     <td><span class="style13">Pendidikan </span><span class="style9">* </span></td>
     <td><select name="pendidikan" id="pendidikan">
       <option selected>Pilih Salah satu</option>
       <option value="S3">S3</option>
       <option value="S2">S2</option>
       <option value="S1">S1</option>
       <option value="D3">D3</option>
       <option value="D1">D1</option>
       <option value="SMA">SMA</option>
       <option value="SMP">SMP</option>
       <option value="SD">SD</option>
          </select></td>
   </tr>
   <tr>
     <td bgcolor="#CCCCCC"><span class="style13">No telepon  <span class="style5">* </span></span></td>
     <td bgcolor="#CCCCCC"><input name="notlp" type="text" id="notlp" size="30" maxlength="14"/></td>
     <td bgcolor="#CCCCCC"><span class="style13">NPWP </span><span class="style9">* </span></td>
     <td bgcolor="#CCCCCC"><input name="npwp" type="text" id="npwp"></td>
   </tr>
   <tr>
     <td><span class="style13">No Tlp Rumah <span class="style5">* </span></span></td>
     <td><input name="tlp_rumah" type="text" id="tlp_rumah" size="30" maxlength="15"/></td>
     <td><span class="style13">Jenis Pensiun </span><span class="style9">* </span></td>
     <td><input name="jenis_pen" type="text" id="jenis_pen"></td>
   </tr>
   <tr>
     <td bgcolor="#CCCCCC"><span class="style13">Nomor Pensiun  <span class="style5">* </span></span></td>
     <td bgcolor="#CCCCCC"><input name="nopen" type="text" id="nopen" value="<?php echo $nopen; ?>" size="30" maxlength="15"/></td>
     <td bgcolor="#CCCCCC"><span class="style13">Kode Jiwa </span><span class="style9">* </span></td>
     <td bgcolor="#CCCCCC"><input name="kd_jiwa" type="text" id="kd_jiwa"></td>
   </tr>
   <tr>
     <td><span class="style13">Tanggal Lahir Sesuai KTP <span class="style5">* </span></span></td>
     <td><input name="tgl_lahir" type="text" id="tgl_lahir" value="<?php echo $tgl_lahir; ?>" size="15"/>
      <a href="javascript:void(0)" onClick="if(self.gfPop)gfPop.fPopCalendar(document.postform.tgl_lahir);return false;" ><img src="images/calendarIcon.jpg" alt="" name="popcal" width="16" height="15" border="0" align="absmiddle" id="popcal" /></a></td>
     <td><span class="style13">Alamat Sesuai KTP </span><span class="style9">* </span></td>
     <td><input name="alamat" type="text" id="alamat" size="50" maxlength="50"></td>
   </tr>

   <tr>
     <td bgcolor="#CCCCCC"><span class="style13">Tempat Lahir Sesuai KTP <span class="style5">* </span></span></td>
     <td bgcolor="#CCCCCC"><input name="tempat_lahir" type="text" id="tempat_lahir" size="30" maxlength="15"/></td>
     <td bgcolor="#CCCCCC"><span class="style13">RT/RW</span><span class="style9">* </span></td>
     <td bgcolor="#CCCCCC"><input name="rt" type="text" id="rt"></td>
   </tr>
   <tr>
    <td><span class="style13">Tanggal SKEP <span class="style5">* </span></span></td>
    <td><input name="tgl_skep" type="text" id="tgl_skep" size="15"/>
      <a href="javascript:void(0)" onClick="if(self.gfPop)gfPop.fPopCalendar(document.postform.tgl_skep);return false;" ><img src="images/calendarIcon.jpg" alt="" name="popcal" width="16" height="15" border="0" align="absmiddle" id="popcal" /></a></td>
    <td><span class="style13">Kode Pos </span><span class="style9">* </span></td>
    <td><input name="kd_pos" type="text" id="kd_pos"></td>
   </tr>
  <tr>
    <td bgcolor="#CCCCCC"><span class="style13">Jenis Produk Pinjaman <span class="style5">* </span></span></td>
    <td bgcolor="#CCCCCC"><select name="kode_jenis" id="kode_jenis">
      <option value="pilih" selected>--Pilih--</option>
      <option value="KPN1">KPN</option>
      <option value="KPP2">KPP</option>
      <option value="N2504">N25</option>
      <option value="B305">B30</option>
      <option value="KMP03">KMP</option>
    </select></td>
    <td bgcolor="#CCCCCC"><span class="style13">Kelurahan </span><span class="style9">* </span></td>
    <td bgcolor="#CCCCCC"><input name="lurah" type="text" id="lurah"></td>
  </tr>
  
  <tr>
    <td><span class="style13">Nomor SKEP  <span class="style5">* </span></span></td>
    <td><input name="noskep" type="text" id="noskep" size="30" maxlength="20"/></td>
    <td><span class="style13">Kecamatan </span><span class="style9">* </span></td>
    <td><input name="kec" type="text" id="kec"></td>
  </tr>
  <tr>
    <td bgcolor="#CCCCCC"><span class="style13">Penerbit <span class="style5">* </span></span></td>
    <td bgcolor="#CCCCCC"><input type="text" name="penerbit" size="30" maxlength="20"/></td>
    <td bgcolor="#CCCCCC"><span class="style13">Kabupaten/Kota </span><span class="style9">* </span></td>
    <td bgcolor="#CCCCCC"><input name="kota" type="text" id="kota"></td>
  </tr>
  <tr>
    <td><span class="style13">Keberadaan SKEP <span class="style5">* </span></span></td>
    <td><select name="cek" id="cek">
      <option value="Pilih Keberadaan">Pilih Keberadaan</option>
      <option value="Ada1">Ada</option>
      <option value="FC">Foto Copy</option>
      <option value="Inf">Infasing</option>
    </select></td>
    <td><span class="style13">Status Perkawinan </span><span class="style9">* </span></td>
    <td><input name="status_kawin" type="text" id="status_kawin"></td>
  <tr>
    <td bgcolor="#CCCCCC"><span class="style13">Sumber Pendapatan <span class="style5">* </span></span></td>
    <td bgcolor="#CCCCCC"><select name="sumber" id="sumber">
      <option>Pilih Salah satu</option>
      <option value="1">Gaji</option>
                </select></td>
    <td bgcolor="#CCCCCC"><span class="style13">ID Expire </span><span class="style9">* </span></td>
    <td bgcolor="#CCCCCC"><input name="id_expire" type="text" id="id_expire" value="" size="15"/>
      <a href="javascript:void(0)" onClick="if(self.gfPop)gfPop.fPopCalendar(document.postform.id_expire);return false;" ><img src="images/calendarIcon.jpg" alt="" name="popcal" width="16" height="15" border="0" align="absmiddle" id="popcal" /></a></td>
  <tr>
  <td><span class="style13">Jenis Kelamin <span class="style5">* </span></span></td>
  <td><select name="jenis_kel" id="jenis_kel">
    <option>Pilih Salah satu</option>
    <option value="Laki-laki">Laki-laki</option>
    <option value="Perempuan">Perempan</option>
    </select></td>
  <td><span class="style13">ID </span><span class="style9">* </span></td>
  <td><input name="ktp" type="text" id="ktp"></td>
  </tr>
  <tr>
    <td bgcolor="#CCCCCC">&nbsp;</td>
    <td bgcolor="#CCCCCC">&nbsp;</td>
    <td bgcolor="#CCCCCC"><span class="style13">Nomor Rekening Cair </span><span class="style9">* </span></td>
    <td bgcolor="#CCCCCC"><input name="norek" type="text" id="norek"></td>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td><input name="simpan" type="button" class="upload"onClick="javascript:validateForm()" value="Simpan"></td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
  </tr>
</table>
</form>
<iframe width=174 height=189 name="gToday:normal:calender/normal.js" id="gToday:normal:calender/normal.js" src="calender/ipopeng.htm" scrolling="no" frameborder="0" style="visibility:visible; z-index:999; position:absolute; top:-500px; left:-500px;">
</iframe>
<table width="100%" border="0" cellpadding="3" cellspacing="0" bgcolor="#2F6B4F">
  <tr>
    <td width="100%" bgcolor="#2F6B4F" class="style14">Informasi Kredit Debitur </td>
  </tr>
</table>
<table width="1104" border="0">
    <tr>
      <td width="192" bgcolor="#CCCCCC">Nopen </td>
      <td width="284" bgcolor="#CCCCCC"><span class="style1">
        <input name="nopen2" type="text" id="nopen2" value="<?php echo $branch; ?>" size="30" maxlength="18"disabled="true"/>
      </span></td>
      <td colspan="2" bgcolor="#CCCCCC">Asuransi</td>
      <td width="374" bgcolor="#CCCCCC"><input name="status_kawin2" type="text" id="status_kawin2" value="<?php echo $asuransi; ?>" disabled="true"></td>
    </tr>
    <tr>
      <td bgcolor="#E8E8E8">Nama</td>
      <td bgcolor="#E8E8E8"><span class="style1">
        <input name="nama2" type="text" id="nama" value="<?php echo $nama; ?>" size="30" maxlength="6"disabled="true"/>
      </span></td>
      <td colspan="2" bgcolor="#E8E8E8">Biaya Tata Laksana </td>
      <td bgcolor="#E8E8E8"><input name="status_kawin3" type="text" id="status_kawin3" value="<?php echo $btl; ?>"disabled="true"></td>
    </tr>
    <tr>
      <td width="192" height="23" bgcolor="#CCCCCC" class="altrow">Tanggal Lahir </td>
      <td bgcolor="#CCCCCC"><input name="tgl_lahir" type="text" id="tgl_lahir3" value="<?php echo $tgl_lahir; ?>" size="15"disabled="true"/>
      </span></td>      
	<td colspan="2"bgcolor="#CCCCCC">Materai</td>
	<td bgcolor="#CCCCCC"><input name="tgl_lahir" type="text" id="tgl_lahir3" value="<?php echo $materai; ?>" size="15"disabled="true"/>
  </tr>
    <tr>
      <td bgcolor="#E8E8E8">Gaji</td>
      <td bgcolor="#E8E8E8"><input name="num"  type="text" id="num"onkeyup="document.getElementById('ubah').innerHTML = ubahCurrency(this.value);" value="<?php echo $gaji; ?>"  size="30" maxlength="40" disabled="true"/>
      <span id="ubah"></span> </td>
      <td colspan="2" bgcolor="#E8E8E8">IIR</td>
      <td bgcolor="#E8E8E8"><input name="iir" type="text" id="iir" disabled="true"></td>
    </tr>
    <tr>
      <td bgcolor="#CCCCCC">Tenor</td>
      <td bgcolor="#CCCCCC"><span class="style1">
        <input name="tenor" type="text" id="tenor" value="<?php echo $tenor; ?>" size="30" maxlength="6"/>
      </span></td>
      <td colspan="2" bgcolor="#CCCCCC">Terima Bersih </td>
      <td bgcolor="#CCCCCC"><input name="bersih" type="text"disabled="true" id="bersih" value="<?php echo $bersih;?>"></td>
    </tr>
    <tr>
      <td bgcolor="#E8E8E8">Pinjaman Yang Diajukan</td>
      <td bgcolor="#E8E8E8">
        <input name="pinjam" type="text"  id="pinjam3"onKeyUp="document.getElementById('format').innerHTML = formatCurrency(this.value);" value="<?php echo $pla; ?>" size="30" maxlength="40"disabled="true"/>
        <span id="format"></span> </td>
      <td colspan="2" bgcolor="#E8E8E8">Angsuran</td>
      <td bgcolor="#E8E8E8"><input name="ang" type="text" disabled="true" id="ang" value="<?php echo $angsuran; ?>"></td>
    </tr>
    <tr>
      <td bgcolor="#CCCCCC">Rate </td>
      <td bgcolor="#CCCCCC"><span class="style1">
        <input name="rate" type="text" id="rate" value="<?php echo $rate; ?>" size="15"disabled="true"/>
      </span></td>
      <td colspan="2" bgcolor="#CCCCCC">&nbsp;</td>
      <td bgcolor="#CCCCCC">&nbsp;</td>
    </tr>
    <tr>
      <td bgcolor="#E8E8E8">Jenis Produk Pinjaman </td>
      <td bgcolor="#E8E8E8"><span class="style1">
        <input name="rate2" type="text" id="rate2" value="<?php echo $deal_type; ?>" size="15"disabled="true"/>
      </span></td>
      <td colspan="2" bgcolor="#E8E8E8">&nbsp;</td>
      <td bgcolor="#E8E8E8">&nbsp;</td>
    </tr>
</table>
</form>
<iframe width=174 height=189 name="gToday:normal:calender/normal.js" id="gToday:normal:calender/normal.js" src="calender/ipopeng.htm" scrolling="no" frameborder="0" style="visibility:visible; z-index:999; position:absolute; top:-500px; left:-500px;">
</iframe>
</body>
</html>


