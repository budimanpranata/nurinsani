<?php require_once __DIR__ . '/koneksi_db.php';
/**
 * Setoran PerKelompok langkah 3: proses setoran anggota yang dipilih di setoran_list.php
 * (nunggak / setoran kurang / setoran penuh + pembayaran angsuran sesuai status pembiayaan), dalam satu transaksi DB.
 * Logika dan urutan query sama dengan versi lama; hanya query (prepared statement) yang berubah.
 * Perbaikan 2026-10-31 (perintah user), lihat PEKERJAAN_TERTUNDA.md bagian 15:
 *   - nominal diambil per CIF (bulat[CIF]); versi lama memakai isian ke-i untuk anggota tercentang ke-i
 *   - loan.ke = ke + 1 (versi lama selalu 1 karena $ke_tenor tidak pernah diisi)
 *   - $cif dan $no_anggota diisi dari data loan (versi lama kosong di keterangan dan rek_loan)
 *   - pesan gagal / belum memilih data kembali ke setor_kelompok.php (versi lama ke stor.php dan form.php yang tidak ada)
 */
session_start();
ob_start();
$userid=isset($_SESSION['userid']) ? $_SESSION['userid'] : null;
$tgl_system=isset($_SESSION['stgl_system']) ? $_SESSION['stgl_system'] : null;
$tgl_input=date("Y-m-d");
$buss_date=$tgl_system;
//cek apakah user sudah login
if(!isset($_SESSION['stgl_system'])){
echo "<script>alert('Maaf,.tanggal system belum di setup');</script>";

   header("location:param_tgl.php");
 exit; }
//cek apakah user sudah login
if(!isset($_SESSION['userid'])){
   header("location:index.html");
 exit; }

//cek level user
if($_SESSION['level']!="unit"){
   header("location:index.html");
 exit; }

$con = db_ni();
// Turn autocommit off
mysqli_autocommit($con,FALSE);

include "function.php";

/** Jalankan query tulis; semua parameter dikirim sebagai teks (seperti nilai '$var' di query versi lama). */
function qs($sql, ...$nilai)
{
    return db_jalankan($sql, str_repeat('s', count($nilai)), ...array_map(function ($v) {
        return $v === null ? '' : (string) $v;
    }, $nilai));
}

/** Baris hasil query dengan semua nilai sebagai teks (seperti mysql_fetch_array versi lama). */
function baris_teks($baris)
{
    if (!$baris) {
        return $baris;
    }
    foreach ($baris as $k => $v) {
        $baris[$k] = $v === null ? null : (string) $v;
    }
    return $baris;
}

/** Nomor jurnal berikutnya per unit (query sama dengan versi lama). */
function nomor_jurnal_kel($unit)
{
    $j = db_satu("SELECT max(nomor_jurnal) AS n FROM jurnal_umum where unit =? ORDER BY tanggal_selesai DESC", 's', (string) $unit);
    return compat_num($j ? $j['n'] : null) + 1;
}

$tanggal=$tgl_system;
$nom=isset($_POST['bulat']) ? $_POST['bulat'] : null;
$checbox = isset($_POST['cekbox']) ? $_POST['cekbox'] : null;

// versi lama mencetak var_dump($checbox) (sisa debug) di sini; tidak ditampilkan lagi
if ($checbox) {
	for ($i=0; $i <sizeof($checbox);$i++){
		$c = (string) $checbox[$i];
		// nominal milik anggota ini (form mengirim bulat[CIF])
		$nom_i = isset($nom[$c]) ? (string) $nom[$c] : null;

		$data = baris_teks(db_satu("select anggota.cif,no_anggota as norek,run_tenor,last_payment,loan.code_kel,loan.unit,angsuran,status_app,loan.Cust_Short_name,loan.cao,bulat,tgl_murab,pokok,os,ijaroh,deal_produk FROM loan
		left join anggota on anggota.cif=loan.cif
		where  loan.cif =?", 's', $c));
		$kol = function ($k) use ($data) {
			return $data && isset($data[$k]) ? $data[$k] : null;
		};

		$norek=$kol('norek');
		$unit=$kol('unit');
		$angsuran=$kol('angsuran');
		$angsuran_pokok=$kol('pokok');
		$ijaroh=$kol('ijaroh');
		$code_kel=$kol('code_kel');
		$cao=$kol('cao');
		$deal_produk=$kol('deal_produk');
		$tgl_murab=$kol('tgl_murab');
		$os=$kol('os');
		$status_app=$kol('status_app');
		$run_tenor=$kol('run_tenor');
		$pb=compat_num($run_tenor) + 1;
		$next_tenor=compat_num($run_tenor) + 2;
		$bulat=$kol('bulat');

		$cek_next= baris_teks(db_satu("select cicilan,tgl_jatuh_tempo from pinjaman_detail WHERE cicilan=? AND cif =?", 'ss', (string) $next_tenor, $c));
		$next_schedule=$cek_next ? $cek_next['tgl_jatuh_tempo'] : null;

		$cek_tunggak= baris_teks(db_satu("select sum(KREDIT)- sum(DEBIT) as tunggak from tunggakan WHERE cif =?", 's', $c));
        $ada_tunggak=$cek_tunggak ? $cek_tunggak['tunggak'] : null;
		// semantik PHP 5: angsuran 0/kosong -> hasil bagi false -> 0
		$ft=round(compat_num(compat_bagi($ada_tunggak, $angsuran)));

		if($deal_produk=='2'){
			$simpanan_minggon_asli=compat_num($bulat) - compat_num($angsuran);
			$simpanan_minggon=compat_num($bulat) - compat_num($angsuran) - 2500;
			$simpan_duaribu = '2500';
			$angsuran=compat_num($angsuran) + 2500;
			$ijaroh = compat_num($ijaroh) + 2500;
			$setoran=compat_num($nom_i) - $simpanan_minggon_asli;
		}else{
			$simpanan_minggon=compat_num($bulat) - compat_num($angsuran);
			$simpan_duaribu = '0';
			$setoran=compat_num($nom_i) - $simpanan_minggon;
		}

		$tgl1=new DateTime((string) $tgl_murab);
		$tgl2=new DateTime((string) $tanggal);
		$jarak = $tgl2->diff($tgl1);
		// versi lama: hanya komponen hari dari selisih tanggal (bukan total hari)
		$jarak =$jarak->d;

		$nama=$kol('Cust_Short_name');
		// versi lama: $cif dan $no_anggota tidak pernah diisi (kosong di keterangan & rek_loan)
		$cif = $c;
		$no_anggota = $norek;
		$ket ="Setoran an $nama $cif";
		$ket2 ="Angsuran  an $nama $cif";

		$kodetr=8;
		$kodetr=acakangkahuruf($kodetr);
		$kode_trans="BU/".$unit.$kodetr;

		$kodetrs=8;
		$kodetrs=acakangkahuruf($kodetrs);
		$kode_tran="BU/".$unit.$kodetrs;


		$tunggak_kredit = function ($type, $kredit, $dengan_ket) use ($tanggal, $norek, $unit, $c, $code_kel, $userid, $ket, $cao) {
			// versi lama: cabang nunggak memakai kolom KET (REFF kosong), cabang lain menaruh $ket di REFF
			if ($dengan_ket) {
				qs("insert into tunggakan (tgl_tunggak,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,blok)VALUES(?,?,?,?,?,'0',?,?,?,?,'',?,'') ",
					$tanggal, $norek, $unit, $c, $code_kel, $type, $kredit, $userid, $ket, $cao);
			} else {
				qs("insert  into tunggakan (tgl_tunggak,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,blok)VALUES(?,?,?,?,?,'0',?,?,?,?,?,'') ",
					$tanggal, $norek, $unit, $c, $code_kel, $type, $kredit, $userid, $ket, $cao);
			}
		};
		$tunggak_debet = function ($type, $debet, $reff) use ($tanggal, $norek, $unit, $c, $code_kel, $userid, $cao) {
			qs("insert  into tunggakan (tgl_tunggak,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,blok)VALUES(?,?,?,?,?,?,?,'0',?,?,?,'') ",
				$tanggal, $norek, $unit, $c, $code_kel, $debet, $type, $userid, $reff, $cao);
		};
		$temp_eod = function () use ($c, $unit, &$setoran, &$angsuran, $angsuran_pokok, &$ijaroh, $ft, $tanggal) {
			qs("insert  into temp_eod (cif,unit,setoran,angsuran,pokok,margin,ft,pb,tgl_setor,tgl_eod,status,status_eod)VALUES
				(?,?,?,?,?,?,?,'',?,?,'NUNGGAK','PENDING') ", $c, $unit, $setoran, $angsuran, $angsuran_pokok, $ijaroh, $ft, $tanggal, $tanggal);
		};
		$tagihan = function ($status) use ($c, $tanggal) {
			qs("Update  tagihan_cs set status_realisasi =?  where cif  =? and tgl_tagih=?", $status, $c, $tanggal);
		};
		$hapus_tunggak_hari_ini = function () use ($c, $tanggal) {
			qs("delete from tunggakan where cif =? and tgl_tunggak =? ", $c, $tanggal);
		};
		$simpanan_setor = function ($type, $kredit, $blok) use ($tgl_system, $norek, $unit, $c, $code_kel, $userid, $ket, $cao, $tanggal, $kode_trans) {
			return qs("Insert into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,blok,tgl_input,kode_transaksi)
				VALUES(?,?,?,?,?,0,?,?,?,?,'',?,?,?,?)", $tgl_system, $norek, $unit, $c, $code_kel, $type, $kredit, $userid, $ket, $cao, $blok, $tanggal, $kode_trans);
		};
		$jurnal_setoran = function () use ($unit, $kode_trans, $tgl_system, $ket, &$nom_i, $userid) {
			$nomor_jurnal = nomor_jurnal_kel($unit);
			qs("insert into jurnal_masuk(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values(?,?,?,?)", $nomor_jurnal, $kode_trans, $tgl_system, $unit);
			qs("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
				values(?,?,'1120000',?,'Bukti SYSTEM',?,?,?)", $unit, $kode_trans, $tgl_system, $ket, $nom_i, $userid);
			qs("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
				values(?,?,'2101000',?,'Bukti SYSTEM',?,?,?)", $unit, $kode_trans, $tgl_system, $ket, $nom_i, $userid);
		};
		$tt = function ($rekening, $kolom, $nilai) use ($unit, $kode_tran, $tanggal, $ket2, $userid) {
			$kolom = $kolom === 'debet' ? 'debet' : 'kredit';
			qs("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,$kolom,id_admin)
				values(?,?,?,?,'Bukti SYSTEM',?,?,?)", $unit, $kode_tran, $rekening, $tanggal, $ket2, $nilai, $userid);
		};
		$rek_loan = function ($nilai, $ket_rek, $status, $no_ang, $cif_rek) use ($tanggal, $unit, $userid) {
			qs("insert into rek_loan(ref,tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif)VALUES('',?,?,?,'0',?,'03',?,?,?,?)",
				$tanggal, $unit, $no_ang, $nilai, $ket_rek, $userid, $status, $cif_rek);
		};
		// ke = ke + 1 seperti Tagihan Kelompok (versi lama: '$ke_tenor'+1 dengan $ke_tenor kosong, selalu 1)
		$update_loan = function () use (&$next_schedule, &$angsuran, &$ijaroh, $pb, $tanggal, $c) {
			qs("Update  loan set next_schedule =?,os =os-?,saldo_margin=saldo_margin-?,run_tenor=?,ke=ke+1,last_payment=? where cif  =?",
				$next_schedule, $angsuran, $ijaroh, $pb, $tanggal, $c);
		};
		// simpanan angsuran (TYPE 03): dengan KET+blok 2, atau tanpa KET dengan keterangan di REFF (seperti versi lama)
		$simpanan_angsuran = function ($teks, $dengan_ket) use ($tanggal, $norek, $unit, $c, $code_kel, &$angsuran, $userid, $cao, $tgl_input, $kode_tran) {
			if ($dengan_ket) {
				qs("insert  into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,blok,tgl_input,kode_transaksi)VALUES(?,?,?,?,?,?,'03','0',?,?,'',?,'2',?,?) ",
					$tanggal, $norek, $unit, $c, $code_kel, $angsuran, $userid, $teks, $cao, $tgl_input, $kode_tran);
			} else {
				qs("insert  into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,REFF,CAO,tgl_input,kode_transaksi)VALUES(?,?,?,?,?,?,'03','0',?,?,?,?,?) ",
					$tanggal, $norek, $unit, $c, $code_kel, $angsuran, $userid, $teks, $cao, $tgl_input, $kode_tran);
			}
		};
		$jurnal_umum = function ($nomor_jurnal) use ($kode_tran, $tanggal, $unit) {
			qs("insert into jurnal_umum(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values(?,?,?,?)", $nomor_jurnal, $kode_tran, $tanggal, $unit);
		};

		if ($nom_i =='0'){
			$tunggak_kredit('01', $angsuran_pokok, true);
			$tunggak_kredit('02', $ijaroh, true);
			$temp_eod();
			$tagihan('NUNGGAK');
			if ($ada_tunggak>=$os){
				$hapus_tunggak_hari_ini();
			}
			if ($jarak<2){
				$hapus_tunggak_hari_ini();
				qs("delete from temp_eod where cif =? and tgl_eod =? ", $c, $tanggal);
			}

		}elseif($nom_i < $bulat){
			// (cabang "nominal == '0'" di dalam versi lama tidak pernah tercapai karena sudah tertangkap di atas)
			$tunggak_kredit('01', $angsuran_pokok, false);
			$tunggak_kredit('02', $ijaroh, false);
			$temp_eod();
			$simpanan_setor('01', $setoran, '0');
			$simpanan_setor('02', $simpanan_minggon, '1');
			$simpanan_setor('01', $simpan_duaribu, '1');
			$jurnal_setoran();
			$tagihan('NUNGGAK');
			if ($ada_tunggak>=$os){
				$hapus_tunggak_hari_ini();
			}

		}else{
			$simpanan_setor('01', $setoran, '0');
			$tagihan('NUNGGAK');
			$simpanan_setor('02', $simpanan_minggon, '1');
			if($ada_tunggak >0 ){
				$tunggak_kredit('01', $angsuran_pokok, false);
				$tunggak_kredit('02', $ijaroh, false);
				$tagihan('NUNGGAK');
			}
			$inputtrnsk = $simpanan_setor('01', $simpan_duaribu, '1');
            if($inputtrnsk){
				$jurnal_setoran();

				switch ($status_app) {
					case "REST":
						$nomor_jurnal = nomor_jurnal_kel($unit);
						if($ada_tunggak>0){
							$tunggak_debet('01', $angsuran_pokok, $ket);
						}
						$rek_loan($angsuran_pokok, $ket2, 'REALISASI POKOK', $norek, $c);
						$update_loan();
						$simpanan_angsuran($ket2, false);
						qs("Update  pinjaman_detail set jumlah_bayar =?,tgl_bayar=? where id_pinjam  =? and cicilan=?", $angsuran, $tanggal, $norek, $pb);
						$tt('2101000', 'debet', $angsuran);
						$tt('1411000', 'kredit', $angsuran);
						$tagihan('BAYAR');
					  break;

					case "REST_POKOK_MARGIN":
						$nomor_jurnal = nomor_jurnal_kel($unit);
						if($ada_tunggak > 0){
							$tunggak_debet('01', $angsuran_pokok, $ket);
							$tunggak_debet('02', $ijaroh, $ket);
							$rek_loan($angsuran_pokok, $ket, 'REALISASI POKOK', $norek, $c);
							$rek_loan($ijaroh, $ket, 'REALISASI BAGHAS', $norek, $c);
							$update_loan();
							$simpanan_angsuran($ket2, true);
						}else{
							$rek_loan($angsuran_pokok, $ket2, 'REALISASI POKOK', $norek, $c);
							$rek_loan($ijaroh, $ket2, 'REALISASI BAGHAS', $norek, $c);
							$update_loan();
							$simpanan_angsuran($ket2, false);
						}
						$tt('2101000', 'debet', $angsuran);
						$tt('1411000', 'kredit', $angsuran);
						$jurnal_umum($nomor_jurnal);
						//Realisasi Piutang Margin
						$tt('1421000', 'debet', $ijaroh);
						$tt('41002', 'kredit', $ijaroh);
						$tagihan('BAYAR');
					  break;

					case "MURAB":
					default:
						// MURAB dan status lain memakai jurnal yang sama; perbedaan versi lama ada di REFF/KET dan no_anggota/cif rek_loan
						$murab = $status_app === "MURAB";
						$nomor_jurnal = nomor_jurnal_kel($unit);
						if($ada_tunggak>0){
							$tunggak_debet('01', $angsuran_pokok, $murab ? $ket : $ket2);
							$tunggak_debet('02', $ijaroh, $murab ? $ket : $ket2);
							$rek_loan($angsuran_pokok, $ket2, 'REALISASI POKOK', $norek, $cif);
							$rek_loan($ijaroh, $ket2, 'REALISASI BAGHAS', $norek, $cif);
							$update_loan();
							$simpanan_angsuran($murab ? $ket : $ket2, true);
						}else{
							$rek_loan($angsuran_pokok, $ket2, 'REALISASI POKOK', $no_anggota, $c);
							$rek_loan($ijaroh, $ket2, 'REALISASI BAGHAS', $no_anggota, $c);
							$update_loan();
							$simpanan_angsuran($murab ? $ket : $ket2, false);
						}
						$jurnal_umum($nomor_jurnal);
						$tt('1413000', 'kredit', $angsuran);
						//Realisasi Piutang Margin
						$tt('1423000', 'debet', $ijaroh);
						//Realiasasi Murabahah Debit pada Piutang Murabahah total jumlah Pokok dan margin
						$tt('41002', 'kredit', $ijaroh);
						$tt('2101000', 'debet', $angsuran);
						$tagihan('BAYAR');
				  }
            }else{
            echo "<script>alert('Maaf,.Proses Input Gagal');</script>";
            echo "<script language='javascript'>document.location.href='setor_kelompok.php'</script>";
            }
		}
	}

	if (!mysqli_commit($con)) {
		echo "Transaksi Gagal commit Silahkan ulangi lagi";
		exit();
	  }
echo "<script>alert('Setoran Kelompok sukses di simpan');</script>";
echo "<script language='javascript'>document.location.href='setor_kelompok.php'</script>";

}else {
			echo '<script>alert("anda belum memilih data."); window.location="setor_kelompok.php"</script>';
	}

?>
