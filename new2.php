<?php require_once __DIR__ . '/php8_compat.php';
session_start();
ob_start();
$userid=$_SESSION['userid'];
$cao=$_SESSION['cao'];
$code_kel=$_SESSION['code_kel'];
$tgl_waka=$_SESSION['tgl_wakalah'];
$tgl_akad=$_SESSION['stgl_akad'];
//cek apakah user sudah login
if(!isset($_SESSION['userid'])){
   header("location:index.html");
 exit; }

//$length = '6';
//generate a random id. Encrypt it and set it as $random
//$random = crypt(uniqid(rand(),1)); 
//if you want to remove any slashes
//$random_no_slashes = strip_tags(stripslashes($random));
//remove any . and or reversing the string
//$random_id = str_replace(".","",$random_no_slashes); 
//$random_final = strrev(str_replace("/","",$random_id));
//take the first 10 characters from $random_final
//$cif = substr($random_final,0,$length); 


include "conn2.php";
include "function.php";
$koneksi=open_connection2();
$data = mysql_query ("select kode_branch from user where userid ='$userid'");
$r = mysql_fetch_array($data);
$branch=$r['kode_branch'];

$today=date("Ymd");
$count = mysql_query ("select count(no) AS last FROM anggota");
$hit = mysql_fetch_array($count);
$lastNoTransaksi = $hit['last'];
$lastNoUrut = $lastNoTransaksi;
$nextNoUrut = $lastNoUrut + 1;
$nextNoTransaksi = sprintf('%04s', $nextNoUrut);
$cif=6;
$cif=acakangkahuruf($cif);

$anggota=$branch.$cif."01"; 
?>
    <html>

    <head>
        <title>Form Input Calon Anggota</title>
        <link rel="stylesheet" type="text/css" href="./load/stylee.css">
        <style type="text/css">
		
            <!-- .header1 {
                color: #ffffff;
                background: #4682B4;
                font-weight: bold;
                font-family: Tahoma, Verdana;
                font-size: 13px;
                margin: 0px;
            }
            
            .style1 {
                color: #CCCCCC
            }
            
            .style5 {
                color: #FF0000
            }
            
            .style9 {
                color: #FF0000;
                font-size: 14px;
            }
            
            .style13 {
                font-family: Verdana, Arial, Helvetica, sans-serif;
                font-size: 14px;
            }
            
            .style14 {
                color: #FFFFFF;
                font-weight: bold;
            }
            
            .style20 {
                color: #000000
            }
.style21 {font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 14px; color: #000000; }
            
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
		left: 375px;
		top:100px;
		margin: 26px 0px 0px 0px;
		width: 600px;
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
   <script type="text/javascript" src="jquery.js"></script>
<script type="text/javascript" charset="utf-8">
    
        function validateForm() {
            var okSoFar = true
            with(document.postform) {
                if (branch.value == "" && okSoFar) {
                    okSoFar = false
                    alert("KODE Branch Masih kosong")
                    branch.focus()
                }
                if (id.value == "" && okSoFar) {
                    okSoFar = false
                    alert("NOMOR KTP Masih kosong")
                    id.focus()
                }
                if (code_kel.value == "" && okSoFar) {
                    okSoFar = false
                    alert("KODE Kelopok Masih kosong")
                    code_kel.focus()
                }
                if (cif.value == "" && okSoFar) {
                    okSoFar = false
                    alert("CIF Masih kosong")
                    cif.focus()
                }
                if (nama.value == "" && okSoFar) {
                    okSoFar = false
                    alert("Nama Masih kosong")
                    nama.focus()
                }
                if (tempat_lahir.value == "" && okSoFar) {
                    okSoFar = false
                    alert("TEMPAT LAHIR Masih kosong")
                    tempat_lahir.focus()
                }

                if (wali.value == "" && okSoFar) {
                    okSoFar = false
                    alert("NAMA PENJAMIN masih kosong")
                    wali.focus()
                }
                if (tgl_lahir.value == "" && okSoFar) {
                    okSoFar = false
                    alert("TANGGAL LAHIR masih kosong")
                    tgl_lahir.focus()
                }

                if (no_tlp.value == "" && okSoFar) {
                    okSoFar = false
                    alert("NOMOR TLP masih kosong")
                    no_tlp.focus()
                }

                if (tlp_penjamin.value == "" && okSoFar) {
                    okSoFar = false
                    alert("NOMOR TLP Penjamin masih kosong")
                    tlp_penjamin.focus()
                }
                if (alamat.value == "" && okSoFar) {
                    okSoFar = false
                    alert("ALAMAT masih kosong")
                    alamat.focus()
                }
                if (npwp.value == "" && okSoFar) {
                    okSoFar = false
                    alert("NPWP masih kosong")
                    npwp.focus()
                }
                if (rt.value == "" && okSoFar) {
                    okSoFar = false
                    alert("RT/RW masih kosong")
                    rt.focus()
                }
                if (ibu.value == "" && okSoFar) {
                    okSoFar = false
                    alert("IBU KANDUNG masih kosong")
                    ibu.focus()
                }
                if (kd_pos.value == "" && okSoFar) {
                    okSoFar = false
                    alert("KODE POS masih kosong")
                    kd_pos.focus()
                }
                if (tgl_skep.value == "" && okSoFar) {
                    okSoFar = false
                    alert("TANGGAL WAKALAH masih kosong")
                    tgl_skep.focus()
                }
				if (tgl_akad.value == "" && okSoFar) {
                    okSoFar = false
                    alert("TANGGAL AKAD MURABAHAH masih kosong")
                    tgl_akad.focus()
                }
                if (lurah.value == "" && okSoFar) {
                    okSoFar = false
                    alert("LURAH masih kosong")
                    lurah.focus()
                }
                if (kec.value == "" && okSoFar) {
                    okSoFar = false
                    alert("KECAMATAN masih kosong")
                    kec.focus()
                }
                if (kota.value == "" && okSoFar) {
                    okSoFar = false
                    alert("KOTA masih kosong")
                    kota.focus()
                }
                if (pekerjaan.value == "" && okSoFar) {
                    okSoFar = false
                    alert("Pekerjaan masih kosong")
                    pekerjaan.focus()
                }
                if (id_expire.value == "" && okSoFar) {
                    okSoFar = false
                    alert(" id_expire masih kosong")
                    id_expire.focus()
                }
                if (pla.value == "" && okSoFar) {
                    okSoFar = false
                    alert(" PEMBIAYAAN masih kosong")
                    pla.focus()
                }
               
               
                if (pendidikan.selectedIndex == "" && okSoFar) {
                    okSoFar = false
                    alert("Pendidikan Belum di pilih")
                    pendidikan.focus()
                }
				
                if (usaha.selectedIndex == "" && okSoFar) {
                    okSoFar = false
                    alert("Usaha Belum di pilih")
                    usaha.focus()
                }
                if (okSoFar == true) submit();
            }
        }
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
    </script>
<script>
	function suggest(inputString){
		if(inputString.length == 0) {
			$('#suggestions').fadeOut();
		} else {
		$('#country').addClass('load');
			$.post("autosuggesst_sim.php", {queryString: ""+inputString+""}, function(data){
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
		$('#cif').val(thisValue);
		setTimeout("$('#suggestions').fadeOut();", 100);
	}
	function fill3(thisValue) {
		$('#no_anggota').val(thisValue);
		setTimeout("$('#suggestions').fadeOut();", 100);
	}function fill4(thisValue) {
		$('#branch').val(thisValue);
		setTimeout("$('#suggestions').fadeOut();", 100);
	}function fill5(thisValue) {
		$('#ibu').val(thisValue);
		setTimeout("$('#suggestions').fadeOut();", 100);
	}function fill6(thisValue) {
		$('#alamat').val(thisValue);
		setTimeout("$('#suggestions').fadeOut();", 100);
	}
	function fill7(thisValue) {
		$('#lurah').val(thisValue);
		setTimeout("$('#suggestions').fadeOut();", 100);
	}
	function fill8(thisValue) {
		$('#kec').val(thisValue);
		setTimeout("$('#suggestions').fadeOut();", 100);
	}
	function fill9(thisValue) {
		$('#kota').val(thisValue);
		setTimeout("$('#suggestions').fadeOut();", 100);
	}
	function fill10(thisValue) {
		$('#kode_kel').val(thisValue);
		setTimeout("$('#suggestions').fadeOut();", 100);
	}
	function fill11(thisValue) {
		$('#wali').val(thisValue);
		setTimeout("$('#suggestions').fadeOut();", 100);
	}
		function fill12(thisValue) {
		$('#notlp').val(thisValue);
		setTimeout("$('#suggestions').fadeOut();", 100);
	}
		function fill13(thisValue) {
		$('#tgl_lahir').val(thisValue);
		setTimeout("$('#suggestions').fadeOut();", 100);
	}
	function fill14(thisValue) {
		$('#tempat_lahir').val(thisValue);
		setTimeout("$('#suggestions').fadeOut();", 100);
	}
	function fill15(thisValue) {
		$('#code_kel').val(thisValue);
		setTimeout("$('#suggestions').fadeOut();", 100);
	}function fill16(thisValue) {
		$('#pendidikan').val(thisValue);
		setTimeout("$('#suggestions').fadeOut();", 100);
	}
	function fill17(thisValue) {
		$('#npwp').val(thisValue);
		setTimeout("$('#suggestions').fadeOut();", 100);
	}
	function fill18(thisValue) {
		$('#kd_pos').val(thisValue);
		setTimeout("$('#suggestions').fadeOut();", 100);
	}
	function fill19(thisValue) {
		$('#status_kawin').val(thisValue);
		setTimeout("$('#suggestions').fadeOut();", 100);
	}
	function fill20(thisValue) {
		$('#id_expire').val(thisValue);
		setTimeout("$('#suggestions').fadeOut();", 100);
	}
	function fill21(thisValue) {
		$('#id').val(thisValue);
		setTimeout("$('#suggestions').fadeOut();", 100);
	}
	function fill22(thisValue) {
		$('#sumber').val(thisValue);
		setTimeout("$('#suggestions').fadeOut();", 100);
	}
	function fill23(thisValue) {
		$('#jenis_kel').val(thisValue);
		setTimeout("$('#suggestions').fadeOut();", 100);
	}
	function fill24(thisValue) {
		$('#code_ao').val(thisValue);
		setTimeout("$('#suggestions').fadeOut();", 100);
	}
	function fill25(thisValue) {
		$('#tgl_skep').val(thisValue);
		setTimeout("$('#suggestions').fadeOut();", 100);
	}
	function fill26(thisValue) {
		$('#rt').val(thisValue);
		setTimeout("$('#suggestions').fadeOut();", 100);
	}
	function fill28(thisValue) {
		$('#barang_satu').val(thisValue);
		setTimeout("$('#suggestions').fadeOut();", 100);
	}
	function fill29(thisValue) {
		$('#barang_dua').val(thisValue);
		setTimeout("$('#suggestions').fadeOut();", 100);
	}
	function fill30(thisValue) {
		$('#barang_tiga').val(thisValue);
		setTimeout("$('#suggestions').fadeOut();", 100);
	}
	function fill31(thisValue) {
		$('#barang_empat').val(thisValue);
		setTimeout("$('#suggestions').fadeOut();", 100);
	}
	function fill32(thisValue) {
		$('#barang_lima').val(thisValue);
		setTimeout("$('#suggestions').fadeOut();", 100);
	}
	function fill33(thisValue) {
		$('#barang_enam').val(thisValue);
		setTimeout("$('#suggestions').fadeOut();", 100);
	}
	function fill34(thisValue) {
		$('#barang_tujuh').val(thisValue);
		setTimeout("$('#suggestions').fadeOut();", 100);
	}
	function fill35(thisValue) {
		$('#barang_delapan').val(thisValue);
		setTimeout("$('#suggestions').fadeOut();", 100);
	}
	function fill36(thisValue) {
		$('#barang_sembilan').val(thisValue);
		setTimeout("$('#suggestions').fadeOut();", 100);
	}
	function fill37(thisValue) {
		$('#barang_sepuluh').val(thisValue);
		setTimeout("$('#suggestions').fadeOut();", 100);
	}
	function fill38(thisValue) {
		$('#harga_satu').val(thisValue);
		setTimeout("$('#suggestions').fadeOut();", 100);
	}
	function fill39(thisValue) {
		$('#harga_dua').val(thisValue);
		setTimeout("$('#suggestions').fadeOut();", 100);
	}
	function fill40(thisValue) {
		$('#harga_tiga').val(thisValue);
		setTimeout("$('#suggestions').fadeOut();", 100);
	}
	function fill41(thisValue) {
		$('#harga_empat').val(thisValue);
		setTimeout("$('#suggestions').fadeOut();", 100);
	}
	function fill42(thisValue) {
		$('#harga_lima').val(thisValue);
		setTimeout("$('#suggestions').fadeOut();", 100);
	}
	function fill43(thisValue) {
		$('#harga_enam').val(thisValue);
		setTimeout("$('#suggestions').fadeOut();", 100);
	}
	function fill44(thisValue) {
		$('#harga_tujuh').val(thisValue);
		setTimeout("$('#suggestions').fadeOut();", 100);
	}
	function fill45(thisValue) {
		$('#harga_delapan').val(thisValue);
		setTimeout("$('#suggestions').fadeOut();", 100);
	}
	function fill46(thisValue) {
		$('#harga_sembilan').val(thisValue);
		setTimeout("$('#suggestions').fadeOut();", 100);
	}
	function fill47(thisValue) {
		$('#harga_sepuluh').val(thisValue);
		setTimeout("$('#suggestions').fadeOut();", 100);
	}
	function fill48(thisValue) {
		$('#pla').val(thisValue);
		setTimeout("$('#suggestions').fadeOut();", 100);
	}
		function fill49(thisValue) {
		$('#usaha').val(thisValue);
		setTimeout("$('#suggestions').fadeOut();", 100);
		
		}
	</script>

</script>
<script type="text/javascript">
 function wajibAngka(evt) {
 var charCode = (evt.which) ? evt.which : event.keyCode
 if (charCode > 31 && (charCode < 48 || charCode > 57))
 return false;
 }
</script>


    <body >
        <form action="inputlog.php" method="post" name="postform">
            <table border='1' width='65%' cellpadding='2' cellspacing='2' bgcolor="#FFFFFF">
            </table>
            <table width="100%" border="0" cellpadding="3" cellspacing="0" bgcolor="#4682B4">
                <tr>
                    <td width="100%" bgcolor="#669900" class="style1"><span class="style14">Entry Calon Anggota </span></td>
                </tr>
            </table>
            <table width='100%' border='0' cellpadding='2' cellspacing='2' bordercolor="#ECE9D8" bgcolor="#EAEAEA">
              <tr>
                <td width="23%" bgcolor="#CCCCCC"><span class="style13">Kode Unit </span><span class="style9">* </span></td>
                <td width="21%" bgcolor="#CCCCCC"><span class="style1">
                  <input name="branch" type="text" id="branch" value="<?php echo $branch; ?>" style="font-weight:bold;" size="30" maxlength="6"/>
                </span></td>
                <td width="22%" bgcolor="#CCCCCC"><span class="style13">Kode Kelompok </span><span class="style9">* </span></td>
                <td width="34%" bgcolor="#CCCCCC"><input name="code_kel" tabindex="17" type="text" id="code_kel" value="<?php echo $code_kel; ?>"style="font-weight:bold;" onKeyUp="this.value = this.value.toUpperCase()" maxlength="20"></td>
              </tr>
              <tr>
                 <td><span class="style13">Cif <span class="style5">* </span></span> </td>
                <td nowrap><input type="text" onKeyUp="suggest(this.value);" name="cif" value="<?php echo $cif; ?>" onBlur="fill2();" id="cif" size="15"/>
                    <div class="suggestionsBox" id="suggestions" style="display: none;"> <img src="arrow.png" style="position: relative; top: -12px; left: 30px;" alt="upArrow" />
                        <div class="suggestionList" id="suggestionsList"> </div>
                    </div>
                    
                                        cif simpanan
                    <input type="text" onKeyUp="suggest(this.value);" name="ciff" value="<?php echo $cif; ?>" onBlur="fill2();" id="ciff" size="15"/></td>
                <td><span class="style9"><span class="style21">Kode AO </span>* </span></td>
                <td width="34%" bgcolor="#CCCCCC"><input name="code_ao" type="text" tabindex="18" id="code_ao" value="<?php echo $cao; ?>"style="font-weight:bold;"/>                </td>
              </tr>
              <tr>
                <td bgcolor="#CCCCCC"><span class="style13">Nomor KTP <span class="style5">* </span></span> </td>
                <td bgcolor="#CCCCCC"><input name="id" id="id" tabindex="1" type="text" onKeyPress="return wajibAngka(event)"  onKeyPress="nextfield('nama')" size="30" maxlength="16" /></td>
                <td bgcolor="#CCCCCC"><span class="style13">Kewarganegaraan </span><span class="style9">* </span></td>
                <td bgcolor="#CCCCCC"><select name="negara" tabindex="19" id="negara">
                    <option value="ID">INDONESIA</option>
                </select></td>
              </tr>
              <tr>
                <td><span class="style13">Nama <span class="style5">* </span></span> </td>
                <td><input name="nama" type="text" tabindex="2" id="nama" onKeyUp="this.value = this.value.toUpperCase()" size="45" maxlength="45" /></td>
                <td><span class="style13">Pendidikan </span><span class="style9">* </span></td>
                <td><select name="pendidikan" tabindex="20" id="pendidikan">
                    <option>Pilih Salah satu</option>
                    <option value="SMA" selected>SMA</option>
                    <option value="SMP">SMP</option>
                    <option value="SD">SD</option>
                    <option value="D1">D1</option>
                    <option value="D3">D3</option>
                    <option value="S1">S1</option>
                                </select></td>
              </tr>
              <tr>
                <td bgcolor="#CCCCCC"><span class="style13">Tempat Lahir <span class="style5">* </span></span> </td>
                <td bgcolor="#CCCCCC"><input name="tempat_lahir" onKeyUp="this.value = this.value.toUpperCase()" tabindex="3" type="text" id="tempat_lahir" size="30" maxlength="14" /></td>
                <td bgcolor="#CCCCCC"><span class="style13">Nama Pasangan/Penjamin </span><span class="style9">* </span></td>
                <td bgcolor="#CCCCCC"><input name="wali" onKeyUp="this.value = this.value.toUpperCase()" tabindex="21" type="text" id="wali"></td>
              </tr>
              <tr>
                <td><span class="style13">Tgl Lahir Sesuai KTP <span class="style5">* </span></span> </td>
                <td><input name="tgl_lahir" tabindex="4" type="text" id="tgl_lahir" placeholder="YYYY-MM-DD" value="<?php echo $tgl_lahir; ?>" size="15" />
                  <a href="javascript:void(0)" onClick="if(self.gfPop)gfPop.fPopCalendar(document.postform.tgl_lahir);return false;"><img src="images/calendarIcon.jpg" alt="" name="popcal" width="16" height="15" border="0" align="absmiddle" id="popcal" /></a> </td>
                <td><span class="style13">No Tlp </span><span class="style9">* </span></td>
                <td><input name="no_tlp" type="text" tabindex="22" id="no_tlp" value="0"></td>
              </tr>
              <tr>
                <td bgcolor="#CCCCCC"><span class="style13">Jenis Kelamin <span class="style9">* </span></span> </td>
                <td bgcolor="#CCCCCC"><select name="jenis_kel" tabindex="5" id="jenis_kel">
                    <option value="Perempuan">Perempuan</option>
                    <option value="Laki-laki">Laki-laki</option>
                </select></td>
                <td bgcolor="#CCCCCC"><span class="style13">No Tlp Penjamin</span><span class="style9">* </span></td>
                <td bgcolor="#CCCCCC"><input name="tlp_penjamin" tabindex="23" type="text" id="tlp_penjamin" value="0"></td>
              </tr>
              <tr>
                <td><span class="style13">ALamat Sesuai KTP <span class="style5">* </span></span> </td>
                <td><input name="alamat" type="text" tabindex="6" onKeyUp="this.value = this.value.toUpperCase()" id="alamat" size="50" maxlength="50"></td>
                <td><span class="style13">NPWP </span><span class="style9">* </span></td>
                <td><input name="npwp" type="text" id="npwp" value="0" tabindex="24" size="20" maxlength="20"></td>
              </tr>
              <tr>
                <td bgcolor="#CCCCCC"><span class="style13">RT/RW <span class="style5">* </span></span> </td>
                <td bgcolor="#CCCCCC"><input name="rt" type="text" tabindex="7" id="rt"></td>
                <td bgcolor="#CCCCCC"><span class="style13">Nama Ibu Kandung</span><span class="style9">* </span></td>
                <td bgcolor="#CCCCCC"><input name="ibu" type="text" tabindex="25" onKeyUp="this.value = this.value.toUpperCase()" id="ibu"></td>
              </tr>
              <tr>
                <td><span class="style13">Kode Pos </span><span class="style9">* </span></td>
                <td><input name="kd_pos" type="text" tabindex="8" id="kd_pos"></td>
                <td><span class="style13">Tanggal Wakalah <span class="style5">* </span></span> </td>
                <td><input name="tgl_skep" type="text" tabindex="26" value="<?php echo $tgl_waka; ?>" id="tgl_skep" size="15" />
                  <a href="javascript:void(0)" onClick="if(self.gfPop)gfPop.fPopCalendar(document.postform.tgl_skep);return false;"><img src="images/calendarIcon.jpg" alt="" name="popcal" width="16" height="15" border="0" align="absmiddle" id="popcal" /></a> </td>
              </tr>
              <tr>
                <td bgcolor="#CCCCCC"><span class="style13">Kelurahan </span><span class="style9">* </span></td>
                <td bgcolor="#CCCCCC"><input name="lurah" tabindex="9" onKeyUp="this.value = this.value.toUpperCase()" type="text" id="lurah"></td>
                <td bgcolor="#CCCCCC"><span class="style13">Tgl Akad Murabahah <span class="style5">* </span></span></td>
                <td bgcolor="#CCCCCC"><input name="tgl_akad" type="text" tabindex="26" value="<?php echo $tgl_akad; ?>" id="tgl_akad" size="15" />
                <a href="javascript:void(0)" onClick="if(self.gfPop)gfPop.fPopCalendar(document.postform.tgl_akad);return false;"><img src="images/calendarIcon.jpg" alt="" name="popcal" width="16" height="15" border="0" align="absmiddle" id="popcal" /></a></td>
              </tr>
              <tr>
                <td><span class="style13">Kecamatan </span><span class="style9">* </span></td>
                <td><input name="kec" type="text" tabindex="10" onKeyUp="this.value = this.value.toUpperCase()" id="kec"></td>
                <td><span class="style13">Jenis Produk Pembiayaan <span class="style5">* </span></span></td>
                <td><select name="deal_type" tabindex="27" id="deal_type">
                  <option value="1">Minggu</option>
                  <option value="2">Hari</option>
                  <option value="3">Bulan</option>
                </select></td>
              </tr>
              <tr>
                <td bgcolor="#CCCCCC"><span class="style13">Kabupaten/Kota</span><span class="style9">* </span></td>
                <td bgcolor="#CCCCCC"><input name="kota" tabindex="11" type="text" onKeyUp="this.value = this.value.toUpperCase()" id="kota"></td>
                <td bgcolor="#CCCCCC"><span class="style13">Bidang Usaha<span class="style5">* </span></span></td>
                <td bgcolor="#CCCCCC"><select name="usaha" id="usaha"  tabindex="28">
                  <option selected>Pilih Salah satu</option>
                  <?php
              		$query = "select * from bidang_usaha ";
					$result = mysql_query($query);
					while ($kanwil=mysql_fetch_array($result))
					{
			  		  	echo "<option value=$kanwil[0]>$kanwil[1]</option>";
					}
              		echo"</select>";?>
                  <input name="nama_usaha" type="text" tabindex="8" size="15" id="nama_usaha">
                </select></td>
              </tr>
              <tr>
                <td><span class="style13">Status Perkawinan <span class="style5">* </span></span> </td>
                <td><select name="status_kawin" tabindex="12" id="status_kawin">
                    <option value="kawin">KAWIN</option>
                    <option value="belum_kawin">BELUM KAWIN</option>
                    <option value="janda">JANDA</option>
                </select></td>
                <td><span class="style13">Sumber Dana Pembiayaan<span class="style5">* </span></span></td>
                <td><select name="sumber" tabindex="29" id="sumber">
<option value="1">NI</option>                  
<option value="2">Pihak Ketiga</option>
<option value="3">Bank Victoria</option>
<option value="4">BSM</option>
<option value="5">BAV</option>
<option value="6">bjb</option>
<option value="7">BRI</option>
<option value="8">LPDB</option>
                </select></td>
              <tr>
                <td bgcolor="#CCCCCC"><span class="style13">Pekerjaan Pasangan<span class="style5">* </span></span></td>
                <td bgcolor="#CCCCCC"><input name="pekerjaan" id="pekerjaan" tabindex="13" onKeyUp="this.value = this.value.toUpperCase()" type="text" value="0" size="30" maxlength="75"/></td>
                <td bgcolor="#CCCCCC"><span class="style13">Golongan Pemilik <span class="style5">*</span></span></td>
                <td bgcolor="#CCCCCC"><input name="gol_pe" type="text" id="gol_pe" tabindex="30" value="875" size="30" maxlength="20"/>
              <tr>
                            <td><span class="style13">Agama </span><span class="style9">* </span></td>
                            <td><select name="agama" tabindex="14" id="agama">
                                <option value="ISLAM">ISLAM</option>
				<option value="BUDHA">BUDHA</option>
				<option value="KRISTEN">KRISTEN</option>

                            </select></td>
                            <td><span class="style13">Golongan Debitur </span><span class="style9">* </span></td>
                            <td><input name="gol_deb" type="text" tabindex="31" id="gol_deb" value="874" size="15"/></td>
                          </tr>
                          <tr>
                            <td bgcolor="#CCCCCC"><span class="style13">ID Expire </span><span class="style9">* </span></td>
                            <td bgcolor="#CCCCCC"><input name="id_expire" tabindex="15" type="text" id="id_expire" value="1900:00:00" size="15" />
                              <a href="javascript:void(0)" onClick="if(self.gfPop)gfPop.fPopCalendar(document.postform.id_expire);return false;"><img src="images/calendarIcon.jpg" alt="" name="popcal" width="16" height="15" border="0" align="absmiddle" id="popcal" /></a> </td>
                            <td bgcolor="#CCCCCC"><span class="style13">Keterkaitan </span><span class="style9">* </span></td>
                            <td bgcolor="#CCCCCC"><input name="ket" type="text" value="2" tabindex="32" id="ket"></td>
                            <td bgcolor="">&nbsp;</td>
                          </tr>
            </table>
            <br>
            <iframe width=174 height=189 name="gToday:normal:calender/normal.js" id="gToday:normal:calender/normal.js" src="calender/ipopeng.htm" scrolling="no" frameborder="0" style="visibility:visible; z-index:999; position:absolute; top:-500px; left:-500px;">
</iframe>

<table width="100%" border="0" cellpadding="3" cellspacing="0" bgcolor="#4682B4">
                <tr>
                    <td width="100%" bgcolor="#669900" class="style14">Dokumen Pembiayaan </td>
                </tr>
         </table>
<br>
  <table width='100%' border='0' bordercolor="#ECE9D8" bgcolor="#EAEAEA">

<tr>
<td id ="imgktp">ktp</td>
<td id ="imgkk">kk</td>
<td id ="imgpenjamin">Penjamin</td>
<td id ="imgusaha">usaha</td>

</tr>
<div>
  <button type="button" onclick="loadAllFiles()">Cari Dokumen</button>
</div>
</table>
<br>
<br>
<br>
            <table width="100%" border="0" cellpadding="3" cellspacing="0" bgcolor="#4682B4">
                <tr>
                    <td width="100%" bgcolor="#669900" class="style14">Informasi Pengajuan Pembiayaan </td>
                </tr>
         </table>
            <table width='100%' border='0' cellpadding='2' cellspacing='2' bordercolor="#ECE9D8" bgcolor="#EAEAEA">

                <tr>
                    <td width="192" bgcolor="#CCCCCC">Barang 1</td>
                    <td width="284" bgcolor="#CCCCCC"><span class="style1">
        <input name="barang_satu" type="text" tabindex="33" onKeyUp="this.value = this.value.toUpperCase()" id="barang_satu" size="30" maxlength="20"/>
      </span></td>
                    <td width="142" bgcolor="#CCCCCC">Harga</td>
                    <td width="470" bgcolor="#CCCCCC"><input name="harga_satu" onKeyPress="return wajibAngka(event)"  tabindex="34" type="text" id="harga_satu" value="0" /></td>
                </tr>
                <tr>
                    <td bgcolor="#E8E8E8">Barang 2 </td>
                    <td bgcolor="#E8E8E8"> <span id="ubah"><span class="style1">
        <input name="barang_dua" type="text" tabindex="35" onKeyUp="this.value = this.value.toUpperCase()"id="barang_dua" size="30" maxlength="20"/>
      </span></span>
                    </td>
                    <td bgcolor="#E8E8E8">Harga</td>
                    <td bgcolor="#E8E8E8"><input name="harga_dua" tabindex="36" onKeyPress="return wajibAngka(event)"  type="text" id="harga_dua" value="0"></td>
                </tr>
                <tr>
                    <td bgcolor="#CCCCCC">Barang 3 </td>
                    <td bgcolor="#CCCCCC"><span class="style1">
        <input name="barang_tiga" type="text" tabindex="37" onKeyUp="this.value = this.value.toUpperCase()"id="barang_tiga" size="30" maxlength="20"/>
        </span></td>
                    <td bgcolor="#CCCCCC">Harga</td>
                    <td bgcolor="#CCCCCC"><input name="harga_tiga" tabindex="38" type="text" onKeyPress="return wajibAngka(event)"  id="harga_tiga" value="0" /></td>
                </tr>
                <tr>
                    <td bgcolor="#E8E8E8">Barang 4 </td>
                    <td bgcolor="#E8E8E8">
                        <input name="barang_empat" type="text" tabindex="39" onKeyUp="this.value = this.value.toUpperCase()" id="barang_empat" size="30" maxlength="40" />
                    </td>
                    <td bgcolor="#E8E8E8">Harga</td>
                    <td bgcolor="#E8E8E8"><input name="harga_empat" tabindex="40" type="text" onKeyPress="return wajibAngka(event)"  id="harga_empat" value="0" /></td>
                </tr>
                <tr>
                    <td bgcolor="#CCCCCC">Barang 5 </td>
                    <td bgcolor="#CCCCCC"><span class="style1">
        <input name="barang_lima" type="text" tabindex="41" onKeyUp="this.value = this.value.toUpperCase()"id="barang_lima" size="30"/>
      </span></td>
                    <td bgcolor="#CCCCCC">Harga</td>
                    <td bgcolor="#CCCCCC"><input name="harga_lima" tabindex="42" type="text" onKeyPress="return wajibAngka(event)"  id="harga_lima" value="0" /></td>
                </tr>
                <tr>
                    <td bgcolor="#E8E8E8">Barang 6 </td>
                    <td bgcolor="#E8E8E8"><span class="style1">
        <input name="barang_enam" type="text"onkeyup="this.value = this.value.toUpperCase()" tabindex="43" id="barang_enam" size="30"/>
      </span></td>
                    <td bgcolor="#E8E8E8">Harga</td>
                    <td bgcolor="#E8E8E8"><input name="harga_enam" tabindex="44" type="text" onKeyPress="return wajibAngka(event)"  id="harga_enam" value="0" /></td>
                </tr>
                <tr>
                    <td bgcolor="#CCCCCC">Barang 7 </td>
                    <td bgcolor="#CCCCCC"><span class="style1">
        <input name="barang_tujuh" type="text"onkeyup="this.value = this.value.toUpperCase()" tabindex="45" id="barang_tujuh" size="30"/>
      </span></td>
                    <td bgcolor="#CCCCCC">Harga</td>
                    <td bgcolor="#CCCCCC"><input name="harga_tujuh" type="text" tabindex="46" onKeyPress="return wajibAngka(event)"  id="harga_tujuh" value="0" /></td>
                </tr>
                <tr>
                    <td bgcolor="#E8E8E8">Barang 8 </td>
                    <td bgcolor="#E8E8E8"><span class="style1">
        <input name="barang_delapan" type="text" onKeyUp="this.value = this.value.toUpperCase()" tabindex="47" id="barang_delapan" size="30"/>
      </span></td>
                    <td bgcolor="#E8E8E8">Harga</td>
                    <td bgcolor="#E8E8E8"><input name="harga_delapan" type="text" id="harga_delapan" onKeyPress="return wajibAngka(event)" tabindex="48" value="0" /></td>
                </tr>
                <tr>
                    <td bgcolor="#CCCCCC">Barang 9 </td>
                    <td bgcolor="#CCCCCC"><span class="style1">
        <input name="barang_sembilan" type="text"onkeyup="this.value = this.value.toUpperCase()" tabindex="49" id="barang_sembilan" size="30"/>
      </span></td>
                    <td bgcolor="#CCCCCC">Harga</td>
                    <td bgcolor="#CCCCCC"><input name="harga_sembilan" type="text" tabindex="50" onKeyPress="return wajibAngka(event)" id="harga_sembilan" value="0" /></td>
                </tr>
                <tr>
                    <td bgcolor="#E8E8E8">Barang 10 </td>
                    <td bgcolor="#E8E8E8"><span class="style1">
        <input name="barang_sepuluh" type="text"onkeyup="this.value = this.value.toUpperCase()" tabindex="51" id="barang_sepuluh" size="30"/>
      </span></td>
                    <td bgcolor="#E8E8E8">Harga</td>
                    <td bgcolor="#E8E8E8"><input name="harga_sepuluh" type="text" onKeyPress="return wajibAngka(event)" id="harga_sepuluh" tabindex="52" value="0" /></td>
					<td bgcolor="">&nbsp;</td>
                </tr>
            </table>
            <br>
            <table width="100%" border="0" cellpadding="3" cellspacing="0" bgcolor="#4682B4">
                <tr>
                    <td width="100%" bgcolor="#669900" class="style14">Informasi Pembiayaan dan Simpanan Anggota </td>
                </tr>
            </table>
            <table width="100%" border="0">
                <tr>
                    <td width="192" bgcolor="#CCCCCC">No Rekening</td>
                    <td width="284" bgcolor="#CCCCCC"><span class="style1">
        <input name="no_anggota" type="text" id="no_anggota" value="<?php echo $anggota;?>"/>
      </span></td>
                    <td width="331" bgcolor="#CCCCCC">Simpanan Khusus 5% </td>
                  <td width="349" bgcolor="#CCCCCC"><input name="simpanan" type="text" id="simpanan" onFocus="startCalc();" onBlur="stopCalc();" value="0" /> </td>
                </tr>
                <tr>
                    <td bgcolor="#E8E8E8">Pengajuan</td>
                    <td bgcolor="#E8E8E8"><input name="num" type="text" onKeyPress="return wajibAngka(event)" tabindex="53" onKeyUp="document.getElementById('format').innerHTML = formatCurrency(this.value);" id="num" size="30" maxlength="40" />
                        <span id="ubah"></span> </td>
                    <td bgcolor="#E8E8E8">Angsuran</td>
                    <td bgcolor="#E8E8E8"><input name="angsuran" type="text" id="angsuran" onFocus="startCalc();" onBlur="stopCalc();"value="0" /></td>
                </tr>
                <tr>
                    <td bgcolor="#CCCCCC">Tenor</td>
                    <td bgcolor="#CCCCCC"><span class="style1">
        <select name="tenor" tabindex="29" id="tenor">
                  <option value="25">25</option>
<option value="35">35</option>
<option value="50">50</option>


                </select>
        <span class="style20">Minggu</span></span>
                    </td>
                    <td bgcolor="#CCCCCC">Total Margin </td>
                    <td bgcolor="#CCCCCC"><input name="tot_margin" type="text" id="tot_margin"onFocus="startCalc();" onBlur="stopCalc();" value="0" /></td>
                </tr>
                <tr>
                    <td bgcolor="#E8E8E8">Disetujui UM/GFH </td>
                    <td bgcolor="#E8E8E8">
                        <input name="pla" type="text" id="pla" tabindex="54" onKeyPress="return wajibAngka(event)" onKeyUp="document.getElementById('format').innerHTML = formatCurrency(this.value);" style="font-weight:bold;" onFocus="startCalc();" onBlur="stopCalc();" size="15" maxlength="8" />
                        <span id="format"></span> </td>
                    <td bgcolor="#E8E8E8">Harga Jual</td>
					<td bgcolor="#E8E8E8"><input name="harga_jual" type="text" id="harga_jual"onFocus="startCalc();" onBlur="stopCalc();" value="0" /></td>
                </tr>
                <tr>
                    <td bgcolor="#CCCCCC">Margin </td>
                    <td bgcolor="#CCCCCC"><span class="style1">
        <input name="rate" type="text" id="rate" onFocus="startCalc();" onBlur="stopCalc();" size="15"/>
      </span></td>
                    <td bgcolor="#CCCCCC">Setoran</td>
                    <td bgcolor="#CCCCCC"><input name="setoran" type="text" id="setoran" onFocus="startCalc();" onBlur="stopCalc();" value="0" /></td>
                </tr>
                <tr>
                    <td bgcolor="#E8E8E8"></td>
                    <td bgcolor="#E8E8E8"><span class="style1">
      </td>
      <td bgcolor="#E8E8E8">&nbsp;</td>
      <td bgcolor="#E8E8E8"><input name="simpan" type="button"  tabindex="55" class="upload"onClick="javascript:validateForm()" value="Simpan"></td>
    </tr>
</table>
</form>
<iframe width=174 height=189 name="gToday:normal:calender/normal.js" id="gToday:normal:calender/normal.js" src="calender/ipopeng.htm" scrolling="no" frameborder="0" style="visibility:visible; z-index:999; position:absolute; top:-500px; left:-500px;">
</iframe>
	<script>
function sum() {
      var txtFirstNumberValue = document.getElementById('harga_satu').value;
      var txtSecondNumberValue = document.getElementById('harga_dua').value;
	    var txtThreeNumberValue = document.getElementById('harga_tiga').value;
		  var txtFourNumberValue = document.getElementById('harga_empat').value;
		var txtFiveNumberValue= document.getElementById('harga_lima').value;
		var txtSixNumberValue= document.getElementById('harga_enam').value;
		var txtSevenNumberValue= document.getElementById('harga_tujuh').value;
		var txtEightNumberValue= document.getElementById('harga_delapan').value;
		var txtNineNumberValue= document.getElementById('harga_sembilan').value;
		var txtTenNumberValue= document.getElementById('harga_sepuluh').value;
	      var result = parseInt(txtFirstNumberValue) + parseInt(txtSecondNumberValue) + parseInt(txtThreeNumberValue) + parseInt(txtFourNumberValue) + parseInt(txtFiveNumberValue) + parseInt(txtSixNumberValue) + parseInt(txtSevenNumberValue) + parseInt(txtEightNumberValue) + parseInt(txtNineNumberValue) + parseInt(txtTenNumberValue);
      if (!isNaN(result)) {
         document.getElementById('pla').value = result;
      }
}
</script>
<script language="javascript">
  document.postform.id.select();
  document.postform.id.focus();
function toRp(a,b,c,d,e){e=function(f){return f.split('').reverse().join('')};b=e(parseInt(a,10).toString());for(c=0,d='';c<b.length;c++){d+=b[c];if((c+1)%3===0&&c!==(b.length-1)){d+='.';}}return'Rp.\t'+e(d)+',00'}
function toAngka(rp){return parseInt(rp.replace(/,.*|\D/g,''),10)}
function startCalc(){
interval = setInterval("calc()",1);}
function calc(){
ten = document.postform.tenor.value;
var tenor=ten;
var mar=0;
if (tenor==35){
mar=24;
}else if(tenor==50){
mar=36;
}else{
mar=18;
}
	
pla = document.postform.pla.value;
rate = document.postform.rate.value;
var plaf=pla/1;
var plaff=pla/1;
var pokok=pla/tenor;
var margin=pla*(rate/100);
var baghas=margin/tenor;
var simpanan=pla*(5/100);
var angsuran=pokok+baghas;
var uang=1-1;
		if (plaf==1000000 && tenor==25){
		uang=baghas+pokok+5800;
	}else if(plaf==1500000  && tenor==25){
		uang=baghas+pokok+5200;
	}else if(plaf==2000000 && tenor==25){
		uang=baghas+pokok+5600;
	}else if(plaf==2500000 && tenor==25){
		uang=baghas+pokok+5000;
	}else if(plaf==3000000 && tenor==25){
		uang=angsuran+5400;
	}else if(plaf==3500000 && tenor==25){
		uang=baghas+pokok+5800;
	}else if(plaf==4000000 && tenor==25){
		uang=baghas+pokok+5200;
	}else if(plaf==4500000 && tenor==25){
		uang=baghas+pokok+5600;
	}else if(plaf==5000000 && tenor==25){
		uang=baghas+pokok+5000;
	}else if(plaf==5500000 && tenor==25){
		uang=baghas+pokok+5400;
	}else if(plaf==6000000 && tenor==25){
	uang=baghas+pokok+5800;
	}else if(plaf==6500000 && tenor==25){
	uang=baghas+pokok+5200;
	}else if(plaf==7000000 && tenor==25){
		uang=baghas+pokok+5600;
	}else if(plaf==1000000  && tenor==35){
		uang=baghas+pokok+5571;
	}else if(plaf==1500000  && tenor==35){
		uang=baghas+pokok+5857;
	}else if(plaf==2000000 && tenor==35){
		uang=baghas+pokok+5143;
	}else if(plaf==2500000 && tenor==35){
		uang=baghas+pokok+5429;
	}else if(plaf==3000000 && tenor==35){
		uang=angsuran+5714;
	}else if(plaf==3500000 && tenor==35){
		uang=baghas+pokok+5000;
	}else if(plaf==4000000 && tenor==35){
		uang=baghas+pokok+5286;
	}else if(plaf==4500000 && tenor==35){
		uang=baghas+pokok+5571;
	}else if(plaf==5000000 && tenor==35){
		uang=baghas+pokok+5857;
	}else if(plaf==5500000 && tenor==35){
		uang=baghas+pokok+5143;
	}else if(plaf==6000000 && tenor==35){
	uang=baghas+pokok+5429;
	}else if(plaf==6500000 && tenor==35){
	uang=baghas+pokok+5714;
	}else if(plaf==7000000 && tenor==35){
		uang=baghas+pokok+5000;

}else if(plaf==1000000  && tenor==50){
		uang=baghas+pokok+5800;
	}else if(plaf==1500000  && tenor==50){
		uang=baghas+pokok+5200;
	}else if(plaf==2000000 && tenor==50){
		uang=baghas+pokok+5600;
	}else if(plaf==2500000 && tenor==50){
		uang=baghas+pokok+5000;
	}else if(plaf==3000000 && tenor==50){
		uang=angsuran+5400;
	}else if(plaf==3500000 && tenor==50){
		uang=baghas+pokok+5800;
	}else if(plaf==4000000 && tenor==50){
		uang=baghas+pokok+5200;
	}else if(plaf==4500000 && tenor==50){
		uang=baghas+pokok+5400;
	}else if(plaf==5000000 && tenor==50){
		uang=baghas+pokok+5000;
	}else if(plaf==5500000 && tenor==50){
		uang=baghas+pokok+5400;
	}else if(plaf==6000000 && tenor==50){
	uang=baghas+pokok+5200;
	}else if(plaf==6500000 && tenor==50){
	uang=baghas+pokok+5200;
	}else if(plaf==7000000 && tenor==50){
		uang=baghas+pokok+5600;



	}else{
		uang=baghas+pokok;
	}
document.postform.rate.value = (mar);
document.postform.simpanan.value = toRp(simpanan);
document.postform.tot_margin.value = toRp(margin);
document.postform.angsuran.value = toRp (Math.round(baghas+pokok));
document.postform.harga_jual.value = toRp(plaff+margin);
document.postform.setoran.value = toRp (Math.round(uang));

}

function stopCalc(){
clearInterval(interval);}
</script>
<script>
function loadAllFiles() {
    let nik = document.getElementById("id").value.trim();
    if (nik === "") {
        alert("Isi NIK dulu!");
        return;
    }

    // list type dokumen
    let types = ["ktp", "kk", "penjamin", "usaha"];

    types.forEach(type => {
        fetch(`http://rmc.nurinsani.co.id:8474/apimobcol/cek_berkas_pembiayaan.php?nik=${nik}&type=${type}`)
            .then(res => res.json())
            .then(data => {
                if (data.valid) {
                    if (data.url) {
                        document.getElementById("img" + type).innerHTML =
                            `<a href="${data.url}" target="_blank">
                                <img src="${data.url}" width="120" style="border:1px solid #ccc;">
                             </a>`;
                    } else {
                        document.getElementById("img" + type).innerHTML = "<em>Tidak ada file</em>";
                    }
                } else {
                    document.getElementById("img" + type).innerHTML = "<em>NIK tidak terdaftar</em>";
                }
            })
            .catch(err => {
                document.getElementById("img" + type).innerHTML = "<em>Gagal load</em>";
                console.error(err);
            });
    });
}
</script>

	</body>
</html>
