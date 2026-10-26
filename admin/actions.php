<style type="text/css">
<!--
.header1 {color: #ffffff; background: #4682B4; font-weight: bold; font-family: Tahoma, Verdana; font-size: 13px; margin: 0px;}
.style13 {font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 14px; }
-->
<style type="text/css">
<!--
.header1 {color: #ffffff; background: #4682B4; font-weight: bold; font-family: Tahoma, Verdana; font-size: 13px; margin: 0px;}
-->
</style>
</style>
<?php require_once __DIR__ . '/../php8_compat.php';
include "conn.php";
$koneksi=open_connection();


$select = "select * from trnsk ";
$select_query = mysql_query($select);

while($select_result = mysql_fetch_array($select_query))
	{
		$kode_trn = $select_result['kode_trn'] ;
		$cif = $select_result['cif'] ;
		$kode_cabang = $select_result['kode_cabang'] ;
		$tgl_trn = $select_result['tgl_trn'] ;
		$kode_jenis = $select_result['kode_jenis'] ;
		$kode_mutasi = $select_result['kode_mutasi'] ;
		$kode_ket = $select_result['kode_ket'] ;
	
			}
?>			

<form action="update.php" method="POST">
  <table border='1' width='65%' cellpadding='2'  cellspacing='2' bgcolor="#FFFFFF">
  </table>
  <table width="100%" border="0" cellpadding="3" cellspacing="0" bgcolor="#4682B4">
  <tr>
    <td width="100%" class="header1">Maintenance Keterangan Data Jaminan </td>
  </tr>
</table>
<table border='0' width='65%' cellpadding='2'  cellspacing='2' bgcolor="#FFFFFF">
  <caption>&nbsp;
  </caption>
  </table>
<table width='100%' border='0' cellpadding='2'  cellspacing='2' bordercolor="#ECE9D8" bgcolor="#EAEAEA">
  <tr>
    <td><span class="style13"><strong>kode Logbook </strong></span></td>
    <td><input type="text" name="kode_trn" size="30" value="<?php echo $kode_trn;?>" maxlength="50"/></td>
  </tr>
  <tr>
    <td width="25%" bgcolor="#CCCCCC"><span class="style13"><strong>Cabang </strong></span></td>
    <td width="75%" bgcolor="#CCCCCC"><input name="kode_cabang" type="text" id="kode_cabang" value="<?php echo $kode_cabang ; ?>" size="30" maxlength="50"/></td>
  </tr>
  <tr>
    <td><span class="style13"><strong>Cif</strong></span></td>
    <td><input type="text" name="cif" size="30" value="<?php echo $cif ; ?>" maxlength="50"/></td>
  </tr>
   <tr>
     <td bgcolor="#CCCCCC"><span class="style13"><strong>Nama</strong></span></td>
     <td bgcolor="#CCCCCC">&nbsp;</td>
   </tr>
   <tr>
     <td><span class="style13"><strong>Nomor Rekening </strong></span></td>
     <td><script>DateInput('today', true)</script></td>
     <td>&nbsp;</td>
   </tr>
   
   <tr>
     <td bgcolor="#CCCCCC"><span class="style13"><strong>Nomor Pensiun </strong></span></td>
     <td bgcolor="#CCCCCC">&nbsp;</td>
     <td>&nbsp;</td>
   </tr>
   <tr>
    <td><span class="style13"><strong>Tanggal Skep </strong></span></td>
    <td><input name="tgl_trn" type="text" id="tgl_trn" value="<?php echo $tgl_trn; ?>" size="30" maxlength="50"/></td>
  </tr>
  <tr>
    <td bgcolor="#CCCCCC"><span class="style13"><strong>Jenis Mutas</strong></span></td>
    <td bgcolor="#CCCCCC"><select name="kode_mutasi" id="kode_mutasi">
      <option value="Pilih Jenis Mutasi ">Pilih Jenis Mutasi</option>
      <option value="Ma001">Masuk</option>
      <option value="Ke002">Keluar</option>
      <option value="Kem03">Kembali</option>
      <option value="Pin04">Pinjam</option>
    </select></td>
  </tr>
  
  <tr>
    <td><span class="style13"><strong>Jenis</strong></span></td>
    <td><input name="kode_jenis" type="text" id="kode_jenis" value="<?php echo $kode_jenis ; ?>" size="30" maxlength="50"/></td>
  </tr>
  <tr>
    <td bgcolor="#CCCCCC"><span class="style13"><strong>Ganti Keterangan </strong></span></td>
    <td bgcolor="#CCCCCC"><label>
    <select name="kode_ket" id="kode_ket">
      <option value="Pilih Keterangan Pinjaman">Pilih Keterangan Pinjaman</option>
      <option value="Ak01">Aktif</option>
      <option value="Lu02">Lunas</option>
      <option value="Cl03">Claim Asuransi</option>
      <option value="Ha04">Hapus Buku</option>
      <option value="LR05">Lunas Reguler</option>
        </select>
    </label></td>
  </tr>
  <tr>
    <td><span class="style13"><strong>No Skep</strong></span></td>
    <td>&nbsp;</td>
  <tr>
    <td bgcolor="#CCCCCC"><span class="style13"><strong>Penerbit</strong></span></td>
    <td bgcolor="#CCCCCC">&nbsp;</td>
  <tr><td></td><td>&nbsp;</td>
  </tr>
  <tr>
    <td bgcolor="#CCCCCC"></td>
    <td bgcolor="#CCCCCC"><input type="submit" name="kirim" value="Keluarkan Jaminan"/>
        <label></label></td>
  </tr>
</table>
</form>

