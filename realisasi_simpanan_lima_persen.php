<?php require_once __DIR__ . '/koneksi_db.php';
/**
 * Setoran 5 Persen langkah 3: catat simpanan 5% dari plafond untuk anggota yang dipilih di yra_list_lima_persen.php
 * (pokok 50.000 lebih dulu bila belum ada, sisanya ke simpanan wajib) beserta jurnalnya, dalam satu transaksi DB.
 * Logika dan urutan sama dengan versi lama; hanya query (prepared statement) yang berubah.
 * Kekeliruan versi lama yang sengaja dipertahankan dicatat di PEKERJAAN_TERTUNDA.md (bagian Setoran 5 Persen).
 */
date_default_timezone_set('Asia/Jakarta');
session_start();
ob_start();

$userid=isset($_SESSION['userid']) ? $_SESSION['userid'] : null;
$tgl_system=isset($_SESSION['stgl_system']) ? $_SESSION['stgl_system'] : null;
//cek apakah user sudah login
if(!isset($_SESSION['stgl_system'])){
echo "<script>alert('Maaf,.tanggal system belum di setup');</script>";

   header("location:param_tgl.php");
 exit; }
if(!isset($_SESSION['userid'])){
   header("location:index.html");
 exit; }
if($_SESSION['level']!="unit"){
   header("location:index.html");
 exit; }

$con = db_ni();

// Turn autocommit off
mysqli_autocommit($con,FALSE);
include "function.php";

/** Catat satu baris tabel_transaksi; $kolom = 'debet' atau 'kredit' (seperti query versi lama). */
function catat_transaksi_5($unit, $kode_trans, $rekening, $tgl, $ket, $kolom, $nilai, $userid)
{
    $kolom = $kolom === 'debet' ? 'debet' : 'kredit';
    db_jalankan("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,$kolom,id_admin)
        values(?,?,?,?,'Bukti SYSTEM',?,?,?)", 'sssssss',
        (string) $unit, (string) $kode_trans, (string) $rekening, (string) $tgl, (string) $ket, (string) $nilai, (string) $userid);
}

/** Insert simpanan_pokok (TYPE 04, blok 4) atau simpanan_wajib (TYPE 05, blok 1) seperti versi lama. */
function catat_simpanan_5($tabel, $tgl, $norek, $unit, $cif, $code_kel, $kredit, $userid, $kode_trans)
{
    $pokok = $tabel === 'simpanan_pokok';
    db_jalankan("Insert into " . ($pokok ? 'simpanan_pokok' : 'simpanan_wajib') . " (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,blok,kode_transaksi)
        VALUES(?,?,?,?,?,0,?,?,?,?,'','',?,?)", 'sssssssssss',
        (string) $tgl, (string) $norek, (string) $unit, (string) $cif, (string) $code_kel, $pokok ? '04' : '05', (string) $kredit,
        (string) $userid, $pokok ? 'Simpanan Pokok' : 'PB Simpanan Wajib', $pokok ? '4' : '1', (string) $kode_trans);
}

$cekbox = isset($_POST['cekbox']) ? $_POST['cekbox'] : null;
if ($cekbox) {
       foreach ($cekbox as $value) {
			$value = (string) $value;
			$kode_tr=5;
			$kode_tr=acakangkahuruf($kode_tr);
		     $row = db_satu("select DATE_ADD(tgl_wakalah,INTERVAL 7 DAY) as tgl_murab,maturity_date,calon_loan.buss_date,calon_loan.code_kel,calon_loan.no_anggota,calon_loan.Cust_Short_name,	calon_loan.Deal_type,suffix,bagi_hasil,Tenor,Plafond,os,angsuran,pokok,ijaroh,bulat,ke,usaha,calon_loan.unit,tgl_wakalah,hari,calon_loan.cao,calon_loan.USERID,calon_loan.status,calon_loan.cif
			 from calon_loan
			 left join anggota on anggota.cif = calon_loan.cif
			 WHERE calon_loan.cif =? ", 's', $value);
			$no_anggota=$row ? $row['no_anggota'] : null;
            $nama=$row ? $row['Cust_Short_name'] : null;
            $unit=$row ? $row['unit'] : null;
            $code_kel=$row ? $row['code_kel'] : null;
			$Plafond=$row ? $row['Plafond'] : null;
			$nominal = compat_num($Plafond) *5/100;
			$norek="$no_anggota";

$kodetr=8;
$kodetr=acakangkahuruf($kodetr);
$kode_trans="BU/".$unit.$kodetr;
			$ket="Realisasi Simpanan Wajib dan Pokok Unit $unit";

            $cwajib = db_satu("select SUM(KREDIT-DEBIT) as wajib from simpanan_wajib where cif =?", 's', $value);
            $wajib = $cwajib ? $cwajib['wajib'] : null;
            $cpokok = db_satu("select SUM(KREDIT-DEBIT) as pokok from simpanan_pokok where cif =?", 's', $value);
            $pokok = $cpokok ? $cpokok['pokok'] : null;

            // catatan: CAO tidak pernah diisi di versi lama, sehingga tersimpan kosong
            if ($pokok == 0 && $wajib >= 0)  {
                $in_pokok=50000;
                $ket="Setoran simpanan Pokok an $nama";
                catat_simpanan_5('simpanan_pokok', $tgl_system, $norek, $unit, $value, $code_kel, $in_pokok, $userid, $kode_trans);
                catat_transaksi_5($unit, $kode_trans, '1120000', $tgl_system, $ket, 'debet', $in_pokok, $userid);
                catat_transaksi_5($unit, $kode_trans, '3102000', $tgl_system, $ket, 'kredit', $in_pokok, $userid);

                //HITUNG SIA SIMPANAN JIKA SISA > 0 MAKA SIMPAN DI WADIAH
                $sisa = $nominal - $in_pokok;
                if($sisa>0){
                    $ket="Setoran simpanan 5% an $nama";
                    catat_simpanan_5('simpanan_wajib', $tgl_system, $norek, $unit, $value, $code_kel, $sisa, $userid, $kode_trans);
                    catat_transaksi_5($unit, $kode_trans, '1120000', $tgl_system, $ket, 'debet', $sisa, $userid);
                    catat_transaksi_5($unit, $kode_trans, '3202000', $tgl_system, $ket, 'kredit', $sisa, $userid);
                }
            }
            //JIKA PUNYA POKOK DA BELUM MEMPUNYA WAJIB
            elseif($pokok == 50000 && $wajib >= 0){
                $ket="Setoran simpanan 5% an $nama";
                catat_simpanan_5('simpanan_wajib', $tgl_system, $norek, $unit, $value, $code_kel, $nominal, $userid, $kode_trans);
                catat_transaksi_5($unit, $kode_trans, '1120000', $tgl_system, $ket, 'debet', $nominal, $userid);
                catat_transaksi_5($unit, $kode_trans, '3202000', $tgl_system, $ket, 'kredit', $nominal, $userid);
            }
            // (cabang "pokok == 0 && wajib == 0" versi lama tidak pernah tercapai karena sudah tertangkap cabang pertama)
            else {
                // versi lama memakai $sisa_pokok yang belum diisi: KREDIT simpanan_wajib tersimpan kosong,
                // sedangkan jurnal sebesar nominal dengan keterangan "Realisasi Simpanan Wajib dan Pokok Unit ..."
                catat_simpanan_5('simpanan_wajib', $tgl_system, $norek, $unit, $value, $code_kel, '', $userid, $kode_trans);
                catat_transaksi_5($unit, $kode_trans, '1120000', $tgl_system, $ket, 'debet', $nominal, $userid);
                catat_transaksi_5($unit, $kode_trans, '3202000', $tgl_system, $ket, 'kredit', $nominal, $userid);
            }
		}

        if (!mysqli_commit($con)) {
            echo "Transaksi Gagal commit Silahkan ulangi lagi";
            exit();
          }else{
				echo '<script>alert("Simpanan wajib dan Pokok sudah direalisasikan."); window.location="simpanan_lima_persen.php"</script>';
          }
            }
			else {
					echo '<script>alert("anda belum memilih data."); window.location="simpanan_lima_persen.php"</script>';
		}

?>
