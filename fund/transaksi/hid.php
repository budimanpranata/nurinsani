<html>
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
		left: 20px;
		top:60px;
		margin: 26px 0px 0px 0px;
		width: 200px;
		padding:0px;
		background-color:#669900;
		border-top: 3px solid #669900;
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
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <!-- css table datatables/dataTables -->
	    <link rel="stylesheet" href="datatables/dataTables.bootstrap.css"/>
        
         <link type="text/css" href="../css/bootstrap.css" rel="stylesheet">
        <link type="text/css" href="../bootstrap/css/bootstrap.min.css" rel="stylesheet"> 
        <link type="text/css" href="../bootstrap/css/bootstrap-responsive.min.css" rel="stylesheet">
        <link type="text/css" href="../css/theme.css" rel="stylesheet">
        <link type="text/css" href="../images/icons/css/font-awesome.css" rel="stylesheet">
        <link type="text/css" href='http://fonts.googleapis.com/css?family=Open+Sans:400italic,600italic,400,600'
            rel='stylesheet'>
        
        <script src="../scripts/jquery-1.9.1.min.js" type="text/javascript"></script>
        <script src="../scripts/jquery-ui-1.10.1.custom.min.js" type="text/javascript"></script>
</head>
<body>
  <div class="container">
                <div class="row">
                    <div class="span12">
                        <div class="content"><div class="panel panel-default">
                        <div class="panel-heading">
                        <h3 class="panel-title"><i class="icon-user"></i> Data Deposito Nur Insani</h3> 
                        </div>
                        <div class="panel-body">
                            <?php require_once __DIR__ . '/../../php8_compat.php';
			if(isset($_POST['input'])){
				$cif	       = $_POST['code'];
				$bilyet		   = $_POST['bilyet'];
				$tenor        = $_POST['tenor'];
				$tgl 			= $_POST['tgl'];
				if ($tenor ='6'){
					$sima='2203000';
				}else {
					$sima='2204000';
				}
                $notelp        = $_POST['notelp'];
				$nominal        = $_POST['nominal'];
				$rasio		=$_POST['nisbah'];
				$aro        = $_POST['aro'];
				$sim='SIM';
				$jenis_simpanan="$sim-$tenor";
				$star_date=date("y-m-d");
				
				$jenis=$_POST['jenis'];
				if ($jenis =="WD"){
					$tahun = substr($tgl,0,2);
					$bulan=substr($tgl,2,5);
					$code_bulan="WD$tahun$bulan";
				}elseif($jenis =="DP"){
					$tahun = substr($tgl,0,2);
					$bulan=substr($tgl,2,5);
					$code_bulan="DP$tahun$bulan";
					
				}elseif($jenis =="MC"){
					$tahun = substr($tgl,0,2);
					$bulan=substr($tgl,2,5);
					$code_bulan="MC20$tahun$bulan";
					
				}elseif($jenis =="EMC"){
					$tahun = substr($tgl,0,2);
					$bulan=substr($tgl,2,5);
					$code_bulan="EMC20$tahun$bulan";
					
				}
				
				$maturity_date =  date ("Y-m-d", strtotime("+$tenor month", strtotime($tgl)));
				$cek = db_query($koneksi, "SELECT * FROM deposito WHERE norek='$norek'");
				if(db_num_rows($cek) == 0){
						$insert = db_query($koneksi, "INSERT INTO deposito(norek,cif,bilyet, nominal,tenor,star_date,maturity_date,jenis_simpanan,aro,rasio,code_bulan,unit,suffix,contract_date,userid)
															VALUES('$norek','$cif', '$bilyet', '$nominal', '$tenor', '$tgl','$maturity_date','$jenis_simpanan','$aro','$rasio','$code_bulan','$unit','1','$tgl','$userid')") or die(mysqli_error());
				$awal_hari = $tgl;
				$akhir_hari = $maturity_date;
				$x=0;
				while (strtotime($awal_hari) < strtotime($akhir_hari)) {
				$x ++;
				$awal_hari= date ("Y-m-d", strtotime("+1 month", strtotime($awal_hari)));
				$detail= db_query($koneksi,"INSERT DELAYED INTO deposito_detail (id,id_deposito,cif,ke,tgl_jatuh_tempo,tgl_bayar,jumlah_nisbah,rek_tujuan,ket) values('$d','$norek','$cif','$x','$awal_hari','$awal_hari','0','$norek','$next_schedule')") or die(mysqli_error());
					
					}
				
				$jurnal=db_fetch_array(db_query($koneksi,"SELECT max(nomor_jurnal) FROM jurnal_umum where unit='$branch' ORDER BY tanggal_selesai DESC"));
					$nomor=$jurnal[0]+1;
					$trns="BU/".$unit .$nomor;
				$keterangan_transaksi="Penempatan Dana Norek $norek";
				$query=db_query($koneksi,"insert into jurnal_umum(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values('$trns','$kode_transaksi','$buss_date','$unit')");
				$deb_dep=db_query($koneksi,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
					values('$unit','$trns','2651000','$buss_date','Bukti Umum','$keterangan_transaksi','$nominal','$userid')");
				$cr_dep=db_query($koneksi,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
					values('$unit','$trns','$sima','$buss_date','Bukti Umum','$keterangan_transaksi','$nominal','$userid')");
				$in_dep= db_query($koneksi,"insert  into dep_realisasi(id_realisasi,norek,cif,jumlah_bayar,tgl_bayar,tgl_jatuh_tempo,norek_tujuan,ket,status)VALUES('$norek','$norek','$cif','$nominal','$tgl','$maturity_date','$norek_tujuan','EVVI','PENEMPATAN')");

					
		if($insert){
			$input=db_query($koneksi,"select 
					norek ,
					nominal,
					tenor,
					case 
					when tenor =6 and nominal <= 50000000 then '0.1'
					when tenor =12 and nominal <= 50000000 then '0.11'
					when tenor =6 and nominal <=99000000 then '0.11'
					when tenor =12 and nominal <=99000000 then '0.12'
					when tenor =6 and nominal  <=499000000 then '0.13'
					when tenor =12 and nominal <=500000000 then '0.13'
					else '0.14' end as rate
					from deposito where norek='$norek'");


							echo '<div class="alert alert-success alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>Data Berhasil Di Simpan.</div>';
						}else{
							echo '<div class="alert alert-danger alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>Ups, Data Gagal Di simpan !</div>';
						}
				}else{
					echo '<div class="alert alert-danger alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>Rekening Sudah Ada..!</div>';
				}
			}
			?>
            
            <blockquote>
            Input Data Deposan Nur Insani
            </blockquote>
			<script src="jquery-1.10.2.js"></script>
<div id="body">
<form name="form1" id="form1" class="form-horizontal row-fluid" action="input_trns.php" method="POST" >
<div class="control-group">
<label class="control-label" for="basicinput">Pilih </label>
<div>

<select id="form" name="pencarian">
<option value="1"> Perorangan
<option value="2">  Perusahaan
</select>
</h1>
</div>
</div>
<div class="control-group">
<div id="select2" class="hasil">
<label class="control-label" for="basicinput">Pilih </label>
<div class="controls">
<input name="cari" type="text" id="form" placeholder="NIP atau Nama Pegawai" size="40">
</div>
</div>
</div>

<div class="control-group">
											<label class="control-label" for="basicinput">No</label>
											<div class="controls">
												<input type="text" name="nim" id="nim" placeholder="Tidak perlu di isi" value="<?php echo $nim; ?>" class="form-control span8 tip" readonly="readonly">
											</div>
										</div>
										<div class="control-group">
											<label class="control-label" for="basicinput">Nomor KTP</label>
											<div class="controls">
												<input type="text" name="ktp" id="ktp" placeholder="Nomor KTP" class="form-control span8 tip" required>
											</div>
										</div>

										<div class="control-group">
											<label class="control-label" for="basicinput">Nama</label>
											<div class="controls">
												<input type="text" name="nama" id="nama" placeholder="Nama Deposan" class="form-control span8 tip" required>
											</div>
										</div>

										<div class="control-group">
											<label class="control-label" for="basicinput">Tempat Lahir</label>
											<div class="controls">
												<input type="text" name="tempat_lahir" id="tempat_lahir" placeholder="Tempat Lahir" class="form-control span8 tip" required>
											</div>
										</div>

										<div class="control-group">
											<label class="control-label" for="basicinput">Tanggal Lahir</label>
											<div class="controls">
												<input name="tanggal_lahir" id="tanggal_lahir" class="input-group date form-control span8 tip" type="text"  date="" placeholder="yyyy/mm/dd" required />
											</div>
										</div>

										<div class="control-group">
											<label class="control-label" for="basicinput">Alamat</label>
											<div class="controls">
												<input name="alamat" id="alamat" class=" form-control span8 tip" type="text" placeholder="Alamat" required />
											</div>
										</div>
                                        
                                        <div class="control-group">
											<label class="control-label" for="basicinput">No Telepon</label>
											<div class="controls">
												<input name="notelp" id="notelp" class=" form-control span8 tip" type="text" placeholder="No Telepon" required />
											</div>
										</div>

                                        <div class="control-group">
											<label class="control-label" for="basicinput">No Rekening Bank Lain</label>
											<div class="controls">
												<input name="norek" id="norek" class=" form-control span8 tip" type="text" placeholder="No Rekening Pencairan Baghas" required />
											</div>
										</div>
										 <div class="control-group">
											<label class="control-label" for="basicinput">Nama Bank</label>
											<div class="controls">
												<input name="bank" id="bank" class=" form-control span8 tip" type="text" placeholder="Nama Bank" required />
											</div>
										</div>
									
										<div class="control-group">
											<div class="controls">
												<button type="submit" name="input" id="input" class="btn btn-sm btn-primary">Simpan</button>
                                               <a href="../view/master_fund.php" class="btn btn-sm btn-danger">Kembali</a>
											</div>
										</div>
									</form>
                        </div>
                        <!--/.content-->
                    </div>
                    <!--/.span9-->
                </div>
            </div>
<div id="tombol" >

</div>
</div>
</form>
<script>
$(document).ready(function(){
$('.hasil').hide();
$('#tombol').hide();
$('#form').change(function(){
if($('#form').val()=='1'){
$('#tombol').show();
$('#select2').hide();
}else if($('#form').val()=='2'){
$('#tombol').show();
$('#select2').show();

}
});
});
</script>

</div>
</body>
</head>