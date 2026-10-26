<?php require_once __DIR__ . '/php8_compat.php';
include "conn2.php";
$koneksi=open_connection2();
$select = mysql_query ("Select tgl_murab,cif,pokok,no_anggota,ijaroh,unit,cao,tenor,
DATE_ADD(tgl_murab,INTERVAL tenor WEEK) as maturity_datee,maturity_date from loan where cif ='0DE1F0'");
while($data = mysql_fetch_array($select)){

$murab=$data['tgl_murab'];
$maturity_date=$data['maturity_date'];
$cif=$data['cif'];
$no_anggota=$data['no_anggota'];
$pokok=$data['pokok'];
$margin_minggu=$data['ijaroh'];
$tenor=$data['tenor'];
$unit=$data['unit'];
$cao=$data['cao'];

var_dump($maturity_date);

				$newdatee = date ("Y-m-d", strtotime("+ 10 WEEK" , strtotime($maturity_date))) ;
				$awal_hari = $murab;
				$akhir_hari = $newdatee;
				$x=0;
				 
				while (strtotime($awal_hari) < strtotime($akhir_hari)) {
				$x ++;
				$awal_hari= date ("Y-m-d", strtotime("+7 DAY", strtotime($awal_hari)));
				$detail= mysql_query("INSERT INTO pinjaman_detail_c (id,id_pinjam,cicilan,angsuran_pokok,bunga,tgl_jatuh_tempo,tgl_bayar,jumlah_bayar,ket) 
				values('$d','$no_anggota','$x','$pokok','$margin_minggu','$awal_hari','$awal','0','')") or die(mysql_error());
					
					}

						

		$cek_tanggal= mysql_query ("SELECT COUNT(tgl_libur) as libur from param_libur 
		inner join pinjaman_detail_c on pinjaman_detail_c.tgl_jatuh_tempo = param_libur.tgl_libur where id_pinjam='$no_anggota'");
		while($data_libur = mysql_fetch_array($cek_tanggal)){
			$libur =$data_libur['libur'];
			
			
		}
		
		
		if($libur > 0){
		@mysql_query ("delete from pinjaman_detail_c where id_pinjam='$no_anggota'");	
	
		 
		
		 $newdate3 = date ("Y-m-d", strtotime("+$libur WEEK" , strtotime($maturity_date))) ;
		 
		 $cek = mysql_query("SELECT * FROM param_libur where tgl_libur='$newdate3'");
		  if(mysql_num_rows($cek)==1){
		
			$liburr=$libur + 1;
			 $newdate2 = date ("Y-m-d", strtotime("+$liburr WEEK" , strtotime($maturity_date))) ;
			}else {
			$liburr=$libur;
			 $newdate2 = date ("Y-m-d", strtotime("+$liburr WEEK" , strtotime($maturity_date))) ;
			}
		
				$awal_hari = $murab;
				$new_akhir_hari = $newdate2;
				$xx=0;
				while (strtotime($awal_hari) < strtotime($new_akhir_hari)) {
				$xx ++;
				$reff="$no_anggota$xx";
				$awal_hari= date ("Y-m-d", strtotime("+7 DAY", strtotime($awal_hari)));
				$detail_data= mysql_query("INSERT INTO pinjaman_detail (id,id_pinjam,cicilan,angsuran_pokok,bunga,tgl_jatuh_tempo,tgl_bayar,jumlah_bayar,ket,cif,unit,ao) 
				values('$reff','$no_anggota','$xx','$pokok','$margin_minggu','$awal_hari','$awal','0','','$cif','$unit','$cao')") or die(mysql_error());
				}	
@mysql_query ("delete from pinjaman_detail where tgl_jatuh_tempo in (select tgl_libur from param_libur where tgl_libur=pinjaman_detail.tgl_jatuh_tempo) AND id_pinjam='$no_anggota'");

$up=mysql_query("SET @count = 0;");
$update=mysql_query ("UPDATE pinjaman_detail SET cicilan=@count:=@count+1 where id_pinjam='$no_anggota' ORDER BY cicilan");
@mysql_query ("delete from pinjaman_detail where cicilan > '$tenor' AND id_pinjam='$no_anggota'");

}

			
			else{
				$awal_hari = $murab;
				$akhir_hari = $maturity_date;
				$x1=0;
				while (strtotime($awal_hari) < strtotime($akhir_hari)){
				$x1 ++;
				$reff="$no_anggota$x1";
				$awal_hari= date ("Y-m-d", strtotime("+7 DAY", strtotime($awal_hari)));
				$update= mysql_query("INSERT INTO pinjaman_detail (id,id_pinjam,cicilan,angsuran_pokok,bunga,tgl_jatuh_tempo,tgl_bayar,jumlah_bayar,ket,cif,unit,ao) 
				values('$reff','$no_anggota','$x1','$pokok','$margin_minggu','$awal_hari','$awal','0','','$cif','$unit','$cao')") or die(mysql_error());
				@mysql_query ("delete from pinjaman_detail_c where id_pinjam='$no_anggota'");	
				
				}
				@mysql_query ("delete from pinjaman_detail where tgl_jatuh_tempo in (select tgl_libur from param_libur where tgl_libur=pinjaman_detail.tgl_jatuh_tempo) AND id_pinjam='$no_anggota'");

$up=mysql_query("SET @count = 0;");
$update=mysql_query ("UPDATE pinjaman_detail SET cicilan=@count:=@count+1 where id_pinjam='$no_anggota' ORDER BY cicilan");
@mysql_query ("delete from pinjaman_detail where cicilan > '$tenor' AND id_pinjam='$no_anggota'");
			}	
}			
if ($update){
echo "<script>alert('suksess!!!');</script>";
echo "Jumlah Libur : .$libur";
echo "<br>";
echo "Jatuh tempo: .$newdate2";
var_dump ($awal_hari);

}else {
	echo "<script>alert('gagal!!!');</script>";
}
?>
