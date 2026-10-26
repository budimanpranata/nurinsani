<?php require_once __DIR__ . '/php8_compat.php';
function antiinjection($data){
  $filter_sql = mysql_real_escape_string(stripslashes(strip_tags(htmlspecialchars($data,ENT_QUOTES))));
  return $filter_sql;
  }
function namahari($tanggal){
    
    //fungsi mencari namahari
    //format $tgl YYYY-MM-DD
    //harviacode.com
    
    $tgl=substr($tanggal,8,2);
    $bln=substr($tanggal,5,2);
    $thn=substr($tanggal,0,4);
 
    $info=date('w', mktime(0,0,0,$bln,$tgl,$thn));
    
    switch($info){
        case '0': return "Minggu"; break;
        case '1': return "Senin"; break;
        case '2': return "Selasa"; break;
        case '3': return "Rabu"; break;
        case '4': return "Kamis"; break;
        case '5': return "Jumat"; break;
        case '6': return "Sabtu"; break;
    }
    
	}
function pembulatan($uang)
{
 $ratusan = substr($uang, -3);
 if($ratusan<500)
 $akhir = $uang - $ratusan;
 else
 $akhir = $uang + (1000-(int) $ratusan);
}
function tgl_indo($tgl){
$tanggal = substr($tgl,8,2);
$bulan   = getBulan(substr($tgl,5,2));
$tahun   = substr($tgl,0,4);
return $tanggal.' '.$bulan.' '.$tahun;
}
function getBulan($bln){
switch ($bln){
case 1:
return "Januari";
break;
case 2:
return "Februari";
break;
case 3:
return "Maret";
break;
case 4:
return "April";
break;
case 5:
return "Mei";
break;
case 6:
return "Juni";
break;
case 7:
return "Juli";
break;
case 8:
return "Agustus";
break;
case 9:
return "September";
break;
case 10:
return "Oktober";
break;
case 11:
return "November";
break;
case 12:
return "Desember";
break;
}
}
function acakangkahuruf($panjang)
{
    $karakter= '1234567890ABCDEFGHIJKL';
    $string = '';
    for ($i = 0; $i < $panjang; $i++) {
  $pos = rand(0, strlen($karakter)-1);
  $string .= $karakter[$pos];
    }
    return $string;
}





?>