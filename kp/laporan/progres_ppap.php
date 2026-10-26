<?php require_once __DIR__ . '/../../koneksi_db.php';
session_start();

$con = db_ni();
if (mysqli_connect_errno()) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
    exit;

}

function acakangkahuruf($panjang)
{
$karakter= '1234567890ABCDEFGHIJKL';
$string = '';
for ($i = 0; $i < $panjang; $i++) {
$pos = rand(0, strlen($karakter)-1);
$string = $karakter($pos);
}
return $string;
}

if (!isset($_SESSION['progress'])) {
    $_SESSION['progress'] = 0;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	$berhasil= db_query ($con,"truncate from report_ppap");	
		

		$ambilunit=db_query($con,"select unit from loan where os > 0  group by unit");
		while($row_hit_mut=db_fetch_array($ambilunit)){
			$unit=$row_hit_mut['unit'];	
		
			db_query($con,"update loan
			set gol=(SELECT
			CASE
			WHEN sum(KREDIT-DEBIT)/angsuran between 1 and 3 then '1'
			WHEN sum(KREDIT-DEBIT)/angsuran between 4 and 6 then '2'
			WHEN sum(KREDIT-DEBIT)/angsuran between 7 and 12 then '3'
			WHEN sum(KREDIT-DEBIT)/angsuran > 12 then '4'
			else '1' end as gol
			FROM
			tunggakan
			where loan.cif=tunggakan.CIF
			) where loan.unit ='$unit'");
				   
				 
			$cek_matur=db_query($con,"Select loan.cif,angsuran,ROUND(sum(KREDIT-DEBIT)/angsuran) as ft, Cust_Short_name,datediff('$tgl',maturity_date) as lama_jatpo from loan
			inner JOIN tunggakan
			on loan.cif=tunggakan.CIF
			WHERE loan.unit ='$unit' and maturity_date < '$tgl' and os >0
			GROUP BY loan.CIF ");
			while($data_matur=db_fetch_array($cek_matur)){
			$cif=$data_matur['cif'];
			$a=$data_matur['ft'];
			$b=$data_matur['lama_jatpo'];
			//cek lama Jatpo
			$c=30;
			
			if ($a<=6){
			$gol='2';   
			
				if($b>60){
				$gol='4';
				}else{
					$gol='2';
			}
			}
			elseif($a<=12){
			$gol='3';
			
			if($b>60){
				$gol='4';
			}else{
					$gol='3';
			}
			}
			elseif($a>12){
			$gol='4';
			
			if($b>60){
				$gol='4';
			}else{
					$gol='4';
			}   
			}else {
				$gol='10';
			}
			
			if ($a<4){
				$gol='10';
			}
			   
			db_query($con,"update loan set gol='$gol' where cif ='$cif'");
			
		}
					   
			db_query($con,"update loan
			set gol=(SELECT
			CASE
			WHEN sum(KREDIT-DEBIT)/angsuran > 0 and datediff('$tgl',maturity_date) between 1 and 30 then '2'
			WHEN sum(KREDIT-DEBIT)/angsuran > 0 and datediff('$tgl',maturity_date) between 31 and 60 then '3'
			WHEN sum(KREDIT-DEBIT)/angsuran > 0 and datediff('$tgl',maturity_date) > 60 then '4'
			else '4' end as gol
			FROM
			tunggakan
			where loan.cif=tunggakan.CIF
			) where loan.unit ='$unit' and maturity_date < '$tgl' and gol='10' ");  


			
			$coa_sdh_dibentuk= db_fetch_array(db_query($con,"select saldo_awal from tabel_master WHERE unit ='".$unit."' and kode_rekening ='1512000' "));
			$sdh_dibentuk=$coa_sdh_dibentuk['saldo_awal']; 
			
			$berhasil= db_query ($con,"insert into report_ppap(sdh_dibentuk,akan_dibentuk,jumlah_selisih,unit,status) values('$sdh_dibentuk','0','0','$unit','0')");

			//query dan arraying coll 1 
	//$cek_saldo= mysql_fetch_array(mysql_query("SELECT sum(saldo_kredit) - sum(debet) as saldo from rek_loan where unit ='$kode_branch' "));
	$data_noa= db_fetch_array(db_query($con,"SELECT count(cif) as noa, sum(Plafond) as pla,sum(os-saldo_margin) as saldo from loan where gol='1' AND loan.unit ='$unit' and os>0"));
	$tot_ppap1= (0.5/100 * $data_noa['saldo']);	
//query dan arraying coll 2 
	//$cek_saldo2= mysql_fetch_array(mysql_query("SELECT sum(pokok)+sum(margin) as saldo, datediff('$tgl', tgl_tunggak) as selisih from tunggakan where datediff('$tgl', tgl_tunggak) between 91 and 120 AND unit  ='$kode_branch' group by cif "));
	$data_noa2= db_fetch_array(db_query($con,"SELECT count(cif) as noa, sum(Plafond) as pla,sum(os-saldo_margin) as saldo from loan where gol='2' AND loan.unit ='$unit' and os>0"));
	$tot_ppap2= (10/100 * $data_noa2['saldo']);
//query dan arraying coll 3 
	//$cek_saldo3= mysql_fetch_array(mysql_query("SELECT sum(pokok)+sum(margin) as saldo, datediff('$tgl', tgl_tunggak) as selisih from tunggakan where datediff('$tgl', tgl_tunggak) between 121 AND 365 AND unit  ='$kode_branch' group by cif "));
	$data_noa3= db_fetch_array(db_query($con,"SELECT count(cif) as noa, sum(Plafond) as pla ,sum(os-saldo_margin) as saldo from loan where gol='3' AND loan.unit ='$unit'and os>0"));
	$tot_ppap3= (50/100 * $data_noa3['saldo']);
//query dan arraying coll 4
	//$cek_saldo4= mysql_fetch_array(mysql_query("SELECT sum(pokok)+sum(margin) as saldo, datediff('$tgl', tgl_tunggak) as selisih from tunggakan where datediff('$tgl', tgl_tunggak) >365 AND unit  ='$kode_branch' group by cif "));
	$data_noa4= db_fetch_array(db_query($con,"SELECT count(cif) as noa, sum(Plafond) as pla ,sum(os-saldo_margin) as saldo from loan where gol='4' AND loan.unit ='$unit' and os>0"));
	$tot_ppap4= (100/100 * $data_noa4['saldo']);
	$total_ppap_unit = $tot_ppap1 + $tot_ppap12 + $tot_ppap3 + $tot_ppap4;
	$berhasil= db_query ($con,"update report_ppap set akan_dibentuk ='$total_ppap_unit' where unit ='$unit'");	
	$berhasil= db_query ($con,"update report_ppap set jumlah_selisih = akan_dibentuk + sdh_dibentuk  where unit ='$unit'");	
	$berhasil= db_query ($con,"update report_ppap  
			set status=(SELECT 
			CASE WHEN jumlah_selisih > 0 then '1'
			else '2' end as status
			) where unit ='$unit'");	
		
         
		
		}
        if ($_SESSION['progress'] > 100) {
			$_SESSION['progress'] = 100;
		}
	
		echo json_encode([
			'success' => true,
			'message' => 'Data berhasil disimpan',
			'progress' => $_SESSION['progress']
		]);
	} else {
        echo json_encode([
            'success' => false,
            'message' => 'Gagal menyimpan data'
        ]);
    }

	

		
							
	

	

    ?>