<?php require_once __DIR__ . '/../koneksi_db.php'; 
session_start();
ob_start();
$userid=$_SESSION['userid'];
$tanggal=$_SESSION['jatuh'];
//cek apakah user sudah login
if(!isset($_SESSION['userid'])){
   header("location:../../index.html");
}

//cek level user
if($_SESSION['level']!="fund"){
   header("location:../../index.html");
}
db_ni();
mysql_select_db('nur_insani');
include "../function.php";
$jatuh=$_POST['jatuh_tempo'];
$tanggal=date("Y-m-d");
$tanggal=$_SESSION['jatuh'];
$data = mysql_query ("select kode_branch from user where userid ='$userid'");
$r = mysql_fetch_array($data);
$unit=$r['kode_branch'];
$jurnal_keluar=mysql_fetch_array(mysql_query("SELECT max(nomor_jurnal) FROM jurnal_umum where unit ='$unit' ORDER BY tanggal_selesai DESC"));
			$nomor_jurnal=$jurnal_keluar[0]+1;
			$kode_transaksi="BU/".$unit .$nomor_jurnal;		
		
		$perode_bulan=date("Ymd");
		 $tahun = substr($perode_bulan,0,4);
		$bulan=substr($perode_bulan,4,2);
		$bulan1=(int) $bulan-1;
		$code_bln_f="NI$tahun-$bulan1";
	$query_jml_aset=mysql_fetch_array(mysql_query("select nomi_murab,marigin_murab,DATEDIFF(sd,dari) as hari from param_finan where code_param='NI2018-08'"));
	$tot_fin=$query_jml_aset['nomi_murab'];
	$margin=$query_jml_aset['marigin_murab'];
	$hari=$query_jml_aset['hari'];
			
$kalender =CAL_GREGORIAN;
$bulan =date('m');
$tahun =date('y');
$hari=cal_days_in_month($kalender,$bulan,$tahun);
$hari =30;	
			
$cekbox = $_POST['cekbox'];
if ($cekbox) {
       foreach ($cekbox as $value) {
		   
		$select = mysql_query("select CUST_SHORT_NAME,bilyet,id_deposito,norek,ke,deposito.cif,tgl_jatuh_tempo,aro,tenor,deposito.nominal as nomi,maturity_date,rasio,deposito.code_bulan,norek_tujuan,param_dep.nominal,margin_murab,eq_rate,cara_hitung from deposito 
inner join deposito_detail on deposito.norek=deposito_detail.id_deposito 
inner join deposan on deposito.cif=deposan.cif 
inner join param_dep on param_dep.code_bulan= deposito.code_bulan 
where deposito_detail.id_deposito  ='" . $value . "' and tgl_jatuh_tempo='$jatuh' group By deposito_detail.id_deposito" );
			while($row = mysql_fetch_array($select)){
		$aro=$row['aro'];
		$cif=$row['cif'];
		$norek=$row['id_deposito'];
		$tgl_jatuh_tempo=$row['tgl_jatuh_tempo'];
		$code_bulan=$row['code_bulan'];
		$jenis_simpanan=$row['jenis_simpanan'];
		$norek_tujuan=$row['norek_tujuan'];
		$nominal=$row['nominal'];
		$nama=$row['CUST_SHORT_NAME'];
		$nomi=$row['nomi'];
		$margin_murab=$row['margin_murab'];
		$cara_hitung=$row['cara_hitung'];

		$maturity_date=$row['maturity_date'];
		$tenor=$row['tenor'];
		if ($tenor =='6'){
                    $sima="1010-2203000";
                }elseif ($tenor =='3') {
                    $sima="1010-2202000";
                }else {
                    $sima="1010-2204000";
                }
		$ke=$row['ke'];
		$rasio=$row['rasio'];
		$eq_rate=$row['eq_rate'];
		$kali_eq=$eq_rate/100;

		$param_rasio = mysql_query ("select rasio_invest,eq from param_dep where rasio_invest = '$rasio'");
		$param = mysql_fetch_array($param_rasio);
		$eq=$param['eq'];
		$rasio_invest=$param['rasio_invest'];
		
		//Perhitungan Nisbah
		$bayar=($nomi*$eq)*$hari/365;
		$gross=$bayar/0.9;
		$pph4=$gross-$bayar;
		$jlbayar=$gross-$pph4;
		
		//perhitungan Rate 
		$bayar_rate=($nomi*$kali_eq)*$hari/365;
		$gross_rate=$bayar_rate/0.9;
		$pph4_rate=$gross_rate-$bayar_rate;
		$jlbayar_rate=$gross_rate-$pph4_rate;
		

		
		//if($bayar_rate > $bayar){
		if($cara_hitung =='2'){
			
			$baghas =$bayar;
			//$bonus=($bayar_rate-$bayar)+$pph4_rate;
			
							
			$pajak=$pph4_rate;
			$bayar=$jlbayar_rate;
			$bonus =($bayar+$pajak)-$baghas;
			
		}else{
			$baghas =$bayar;
			$bonus=$pph4_rate;
			$pajak=$pph4_rate;
			$bayar=$bayar;
			
		}
		
		
		$id_realisasi="$norek$ke";
		$ket ="Realisasi Pembayaran Bagi Hasil an $nama";
		
		if ($aro=='ARO_Bulan'){
		//@mysql_query("insert  into dep_realisasi(id_realisasi,norek,cif,jumlah_bayar,tgl_bayar,tgl_jatuh_tempo,norek_tujuan,ket,status)VALUES('$id_realisasi','$norek','$cif','$bayar','$tanggal','$tgl_jatuh_tempo','$norek_tujuan','EVVI','BAGI_HASIL') ") or die(mysql_error("GAGAL INPUT"));
		
		@mysql_query("insert into jurnal_umum(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values('','$kode_transaksi','$tanggal','$unit')");
		
		//Realiasasi Pembayaran margin Deposito Debet Pendapatan Sebesar Baghas
		@mysql_query("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
					values('$unit','$kode_transaksi','1010-41001','$tanggal','Bukti System','$ket','$baghas','$userid')");
		//Realiasasi Pembayaran margin Deposito Debet Biaya Baghas Sebesar Bonus
		@mysql_query("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
					values('$unit','$kode_transaksi','1010-51101','$tanggal','Bukti System','$ket','$bonus','$userid')");
		//Realiasasi Pembayaran margin Deposito Kredit  Simpanan Berjangka Sebesar Bayar
		@mysql_query("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
					values('$unit','$kode_transaksi','$sima','$tanggal','Bukti System','$ket','$bayar','$userid')");
		//Realiasasi Pembayaran margin Deposito kredit Pajak
		@mysql_query("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
					values('$unit','$kode_transaksi','1010-2423000','$tanggal','Bukti System','$ket','$pajak','$userid')");
		@db_query("update deposito set nominal=nominal + '$bayar', status_print='ADVIS' where norek='$norek'");  
		
		}elseif($aro=='ARO_jatpo') {
		//@mysql_query("insert  into dep_realisasi(id_realisasi,norek,cif,jumlah_bayar,tgl_bayar,tgl_jatuh_tempo,norek_tujuan,ket,status)VALUES('$id_realisasi','$norek','$cif','$bayar','$tanggal','$tgl_jatuh_tempo','$norek_tujuan','EVVI','BAGI_HASIL') ") or die(mysql_error("GAGAL INPUT"));
		
		@mysql_query("insert into jurnal_umum(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values('','$kode_transaksi','$tanggal','$unit')");
		
		//Realiasasi Pembayaran margin Deposito Debet Pendapatan Sebesar Baghas
		@mysql_query("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
					values('$unit','$kode_transaksi','1010-41001','$tanggal','Bukti System','$ket','$baghas','$userid')");
		//Realiasasi Pembayaran margin Deposito Debet Biaya Baghas Sebesar Bonus
		@mysql_query("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
					values('$unit','$kode_transaksi','1010-51101','$tanggal','Bukti System','$ket','$bonus','$userid')");
		//Realiasasi Pembayaran margin Deposito Kredit  SImpaman Wadiah Sebesar Bayar
		@mysql_query("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
					values('$unit','$kode_transaksi','1010-2102000','$tanggal','Bukti System','$ket','$bayar','$userid')");
		//Realiasasi Pembayaran margin Deposito kredit Pajak
		@mysql_query("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
					values('$unit','$kode_transaksi','1010-2423000','$tanggal','Bukti System','$ket','$pajak','$userid')");
		@mysql_query("insert  into escrow_deposito (BUSS_DATE,NOREK,UNIT,CIF,DEBIT,TYPE,KREDIT,USERID,KET,REFF,kode_transaksi)VALUES('$tanggal','$norek','$unit','$cif','0','03','$bayar','$userid','$ket','','$kode_transaksi') ") or die(mysql_error("GAGAL INPUT"));
			
			
					
		}else {
		
		@mysql_query("insert  into dep_realisasi(id_realisasi,norek,cif,jumlah_bayar,tgl_bayar,tgl_jatuh_tempo,norek_tujuan,ket,status)VALUES('$id_realisasi','$norek','$cif','$bayar','$tanggal','$tgl_jatuh_tempo','$norek_tujuan','EVVI','BAGI_HASIL') ") or die(mysql_error("GAGAL INPUT"));
		
		@mysql_query("insert into jurnal_umum(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values('','$kode_transaksi','$tanggal','$unit')");
		
		//Realiasasi Pembayaran margin Deposito Debet Pendapatan Sebesar Baghas
		@mysql_query("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
					values('$unit','$kode_transaksi','1010-41004','$tanggal','Bukti System','$ket','$baghas','$userid')");
		//Realiasasi Pembayaran margin Deposito Debet Biaya Baghas Sebesar Bonus
		@mysql_query("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
					values('$unit','$kode_transaksi','1010-51102','$tanggal','Bukti System','$ket','$bonus','$userid')");
		//Realiasasi Pembayaran margin Deposito Kredit  RRP Sebesar Bayar
		@mysql_query("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
					values('$unit','$kode_transaksi','1010-2837000','$tanggal','Bukti System','$ket','$bayar','$userid')");
		//Realiasasi Pembayaran margin Deposito kredit Pajak
		@mysql_query("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
					values('$unit','$kode_transaksi','1010-2423000','$tanggal','Bukti System','$ket','$pajak','$userid')");
		



		
			
				}
			}
	   }
			echo '<script>alert("Data sudah direalisasikan."); window.location="view/view_jatuh.php"</script>';
		
		
	}else {
			echo '<script>alert("anda belum memilih data."); window.location="view/view_jatuh.php"</script>';
	}

?>
