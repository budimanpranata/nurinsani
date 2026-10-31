<?php require_once __DIR__ . '/koneksi_db.php';
/**
 * Menu Transaksi > Setor: setoran & penarikan per rekening anggota.
 *   01 Setoran tunai simpanan wajib/pokok   02 Tarik tunai (ke temp_tarik)   03 Setoran angsuran
 *   04 Pemindah bukuan (debet/kredit pokok/wajib)   05 Setoran angsuran WO
 * Logika, urutan query, dan pesan sama dengan versi lama; hanya tampilan dan query (prepared statement) yang berubah.
 * Kekeliruan versi lama yang sengaja dipertahankan dicatat di PEKERJAAN_TERTUNDA.md (bagian Setor).
 */
require_once __DIR__ . '/inc/tampilan.php';
session_start();
ob_start();
$userid = isset($_SESSION['userid']) ? $_SESSION['userid'] : null;
$tgl_system = isset($_SESSION['stgl_system']) ? $_SESSION['stgl_system'] : null;
//cek apakah user sudah login
if (!isset($_SESSION['stgl_system'])) {
    echo "<script>alert('Maaf,.tanggal system belum di setup');</script>";
    header("location:param_tgl.php");
    exit;
}
if (!isset($_SESSION['userid'])) {
    header("location:index.html");
    exit;
}
if ($_SESSION['level'] != "unit") {
    header("location:index.html");
    exit;
}
include "function.php";

$pos = function ($nama) {
    return isset($_POST[$nama]) ? (string) $_POST[$nama] : '';
};
$norek = $pos('kode_rekening');
$nominal = $pos('nama');
$baki = $pos('baki');
$simpan = $pos('simpan');
$tanggal = date("Y-m-d");
$pilih = $pos('select');
$buss_date = $tgl_system;
$keterangan = $pos('ket');

/** Catat satu baris tabel_transaksi; $kolom = 'debet' atau 'kredit' (seperti query versi lama). */
function catat_transaksi($unit, $kode_trans, $rekening, $tgl, $jenis, $ket, $kolom, $nilai, $userid)
{
    $kolom = $kolom === 'debet' ? 'debet' : 'kredit';
    db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,$kolom,id_admin)
        values(?,?,?,?,?,?,?,?)", 'ssssssss',
        (string) $unit, (string) $kode_trans, (string) $rekening, (string) $tgl, (string) $jenis, (string) $ket, (string) $nilai, (string) $userid);
}

/** Insert ke simpanan / simpanan_pokok / simpanan_wajib dengan kolom yang sama seperti versi lama. */
function catat_simpanan($tabel, $isi)
{
    $kolom = array_keys($isi);
    db_jalankan("Insert into $tabel (" . implode(',', $kolom) . ") VALUES (" . implode(',', array_fill(0, count($kolom), '?')) . ")",
        str_repeat('s', count($kolom)), ...array_map(function ($v) {
            return $v === null ? '' : (string) $v;
        }, array_values($isi)));
}

/** Nomor jurnal berikutnya per unit (query sama dengan versi lama). */
function nomor_jurnal_berikut($unit)
{
    $j = db_satu("SELECT max(nomor_jurnal) AS n FROM jurnal_umum where unit =? ORDER BY tanggal_selesai DESC", 's', (string) $unit);
    return compat_num($j ? $j['n'] : null) + 1;
}

$kkel = db_satu("select CODE_KEL,CIF,UNIT,CUST_SHORT_NAME,CAO FROM anggota where  norek =?", 's', $norek);
$code_kel = $kkel ? $kkel['CODE_KEL'] : null;
$ciff = $kkel ? $kkel['CIF'] : null;
$unit = $kkel ? $kkel['UNIT'] : null;
$nama = $kkel ? $kkel['CUST_SHORT_NAME'] : null;
$ds = $kkel ? $kkel['CAO'] : null;

$kodetr = 7;
$kodetr = acakangkahuruf($kodetr);
$kode_trans = "BU/" . $unit . $kodetr;

// versi lama: variabel di bawah hanya terisi bila anggota punya loan (unit & cao ikut diambil dari loan)
$cao = null;
$cif = null;
foreach (db_semua("select DATE_ADD(next_schedule,INTERVAL 7 DAY) as next_schedule,unit,code_kel,no_anggota,cif,ijaroh,Plafond,pokok,os,angsuran,bulat,TIMESTAMPDIFF(WEEK,tgl_murab,next_schedule) AS RUN_TENOR1,run_tenor,TIMESTAMPDIFF(WEEK,tgl_murab,CURDATE()) AS ke,cao  from loan  WHERE cif =?", 's', (string) $ciff) as $row) {
    $no_anggota = $row['no_anggota'];
    $pokok = $row['pokok'];
    $os = $row['os'];
    $bulat = $row['bulat'];
    $run_tenor = $row['run_tenor'];
    $unit = $row['unit'];
    $angsuran = $row['angsuran'];
    $ijaroh = $row['ijaroh'];
    $ke = $row['ke'];
    $cif = $row['cif'];
    $cao = $row['cao'];
}

$pesan = array();      // script alert/pindah halaman, dicetak di tempat yang sama dengan versi lama
$mutasi = array();     // baris tabel mutasi di bawah form
$alert = function ($teks) use (&$pesan) {
    $pesan[] = "<script>alert('$teks');</script>";
};
$pindah = function () use (&$pesan) {
    $pesan[] = "<script language='javascript'>document.location.href='stor.php'</script>";
};
/** Daftar mutasi dengan saldo berjalan (@bal), query sama dengan versi lama. */
$ambil_mutasi = function ($sql, $kunci) {
    db_jalankan("SET @bal = 0;");
    return db_semua($sql, 's', (string) $kunci);
};

if (isset($_POST['search'])) {
    if ($kkel) {
        if (empty($norek) || empty($pilih) || empty($nominal)) {
            $alert('Maaf,.isi Nomor REKENING dan Jenis Transaksi yang benar');
            $pindah();
        } else {
            switch ($pilih) {
                case "01":
                    $cwajib = db_satu("select SUM(KREDIT-DEBIT) as wajib from simpanan_wajib where cif =?", 's', (string) $ciff);
                    $wajib = $cwajib ? $cwajib['wajib'] : null;
                    $cpokok = db_satu("select SUM(KREDIT-DEBIT) as pokok from simpanan_pokok where cif =?", 's', (string) $ciff);
                    $pokok = $cpokok ? $cpokok['pokok'] : null;
                    $dasar = array('BUSS_DATE' => $tgl_system, 'NOREK' => $norek, 'UNIT' => $unit, 'CIF' => $ciff, 'CODE_KEL' => $code_kel, 'DEBIT' => 0);

                    // Perbaikan 2026-10-31 (perintah user): anggota yang belum punya simpanan pokok wajib setor minimal 50.000.
                    // Versi lama tetap mencatat pokok & jurnal 50.000 walau uang yang disetor kurang.
                    if ($pokok == 0 && $wajib >= 0 && compat_num($nominal) < 50000) {
                        $alert('Setoran pertama minimal Rp 50.000 untuk simpanan pokok. Transaksi tidak disimpan.');
                        $pindah();
                        break;
                    }

                    if ($pokok == 0 && $wajib >= 0) {
                        $in_pokok = 50000;
                        $ket = "Setoran simpanan Pokok an $nama";
                        catat_simpanan('simpanan_pokok', $dasar + array('TYPE' => '04', 'KREDIT' => $in_pokok, 'USERID' => $userid, 'KET' => 'Simpanan Pokok', 'REFF' => '', 'CAO' => $cao, 'blok' => '4', 'kode_transaksi' => $kode_trans));
                        catat_transaksi($unit, $kode_trans, '1120000', $tgl_system, 'Bukti SYSTEM', $ket, 'debet', $in_pokok, $userid);
                        catat_transaksi($unit, $kode_trans, '3102000', $tgl_system, 'Bukti SYSTEM', $ket, 'kredit', $in_pokok, $userid);

                        //HITUNG SIA SIMPANAN JIKA SISA > 0 MAKA SIMPAN DI WADIAH
                        $sisa = compat_num($nominal) - $in_pokok;
                        if ($sisa > 0) {
                            $ket = "Setoran simpanan 5% an $nama";
                            catat_simpanan('simpanan_wajib', $dasar + array('TYPE' => '05', 'KREDIT' => $sisa, 'USERID' => $userid, 'KET' => 'PB Simpanan Wajib', 'REFF' => '', 'CAO' => $cao, 'blok' => '1', 'kode_transaksi' => $kode_trans));
                            // versi lama memakai $ket1 yang belum diisi: keterangan jurnal kosong
                            catat_transaksi($unit, $kode_trans, '1120000', $tgl_system, 'Bukti SYSTEM', '', 'debet', $sisa, $userid);
                            catat_transaksi($unit, $kode_trans, '3202000', $tgl_system, 'Bukti SYSTEM', '', 'kredit', $sisa, $userid);
                        }
                    } elseif ($pokok == 50000 && $wajib >= 0) {
                        //JIKA PUNYA POKOK DA BELUM MEMPUNYA WAJIB
                        $ket = "Setoran simpanan 5% an $nama";
                        catat_simpanan('simpanan_wajib', $dasar + array('TYPE' => '05', 'KREDIT' => $nominal, 'USERID' => $userid, 'KET' => 'PB Simpanan Wajib', 'REFF' => '', 'CAO' => $cao, 'blok' => '1', 'kode_transaksi' => $kode_trans));
                        catat_transaksi($unit, $kode_trans, '1120000', $tgl_system, 'Bukti SYSTEM', $ket, 'debet', $nominal, $userid);
                        catat_transaksi($unit, $kode_trans, '3202000', $tgl_system, 'Bukti SYSTEM', $ket, 'kredit', $nominal, $userid);
                    } else {
                        // (cabang "pokok == 0 && wajib == 0" versi lama tidak pernah tercapai karena sudah tertangkap cabang pertama)
                        // Perbaikan 2026-10-31 (perintah user): versi lama mencatat KREDIT simpanan_wajib kosong ($sisa_pokok tidak
                        // pernah diisi) padahal jurnal sebesar nominal. Kini: bila pokok < 50.000 (dan wajib tidak negatif) pokok
                        // dilengkapi dulu sampai 50.000, sisanya ke wajib; selain itu seluruh nominal ke wajib. Jurnal mengikuti.
                        $ket1 = "Wadiah $nama";
                        $jumlah = compat_num($nominal);
                        $ke_pokok = 0;
                        if (compat_num($pokok) < 50000 && $wajib >= 0) {
                            $ke_pokok = min($jumlah, 50000 - compat_num($pokok));
                        }
                        $ke_wajib = $jumlah - $ke_pokok;
                        if ($ke_pokok > 0) {
                            $ket = "Setoran simpanan Pokok an $nama";
                            catat_simpanan('simpanan_pokok', $dasar + array('TYPE' => '04', 'KREDIT' => $ke_pokok, 'USERID' => $userid, 'KET' => 'Simpanan Pokok', 'REFF' => '', 'CAO' => $cao, 'blok' => '4', 'kode_transaksi' => $kode_trans));
                            catat_transaksi($unit, $kode_trans, '1120000', $tgl_system, 'Bukti SYSTEM', $ket, 'debet', $ke_pokok, $userid);
                            catat_transaksi($unit, $kode_trans, '3102000', $tgl_system, 'Bukti SYSTEM', $ket, 'kredit', $ke_pokok, $userid);
                        }
                        if ($ke_wajib > 0) {
                            catat_simpanan('simpanan_wajib', $dasar + array('TYPE' => '05', 'KREDIT' => $ke_wajib, 'USERID' => $userid, 'KET' => 'PB Simpanan Wajib', 'REFF' => '', 'CAO' => $cao, 'blok' => '1', 'kode_transaksi' => $kode_trans));
                            catat_transaksi($unit, $kode_trans, '1120000', $tgl_system, 'Bukti SYSTEM', $ket1, 'debet', $ke_wajib, $userid);
                            catat_transaksi($unit, $kode_trans, '3202000', $tgl_system, 'Bukti SYSTEM', $ket1, 'kredit', $ke_wajib, $userid);
                        }
                    }
                    $alert("Nominal sudah ditambahkan pada rekening $norek !!!");
                    $mutasi = $ambil_mutasi("SELECT simpanan_wajib.NOREK,simpanan_wajib.BUSS_DATE,anggota.CUST_SHORT_NAME,DEBIT,KREDIT,@bal:=(@bal+KREDIT)-DEBIT as balance FROM simpanan_wajib
                        left join anggota on anggota.cif = simpanan_wajib.cif
                        where simpanan_wajib.cif=? order by simpanan_wajib.REFF", $ciff);
                    break;

                case "02":
                    $ket = $keterangan;
                    $c_area = db_satu("select code_area FROM branch where kode_branch =?", 's', (string) $unit);
                    $code_area = $c_area ? $c_area['code_area'] : null;
                    $SAL = db_satu("select sum(KREDIT)- sum(DEBIT) as SALDO  FROM simpanan where  cif =? and type not in ('04','05')", 's', (string) $ciff);
                    $SALDO = $SAL ? $SAL['SALDO'] : null;
                    // semantik PHP 5: saldo kosong = 0
                    if (compat_num($SALDO) < compat_num($nominal)) {
                        $alert("Maaf,.Saldo Anda Tidak Mencukupi saldo anda adalah $SALDO");
                        $pindah();
                    } else {
                        $inputtrnsk = db_jalankan("Insert into temp_tarik (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,KREDIT,TYPE,DEBIT,USERID,KET,REFF,CAO,blok,tgl_input,kode_transaksi)
                            VALUES(?,?,?,?,?,0,'01',?,?,?,'',?,?,?,?)", 'ssssssssssss',
                            (string) $tgl_system, $norek, (string) $unit, (string) $ciff, (string) $code_kel, $nominal, (string) $userid, $ket,
                            (string) $ds, (string) $code_area, $tanggal, $kode_trans);
                        if ($inputtrnsk) {
                            $mutasi = $ambil_mutasi("SELECT simpanan.NOREK,simpanan.BUSS_DATE,anggota.CUST_SHORT_NAME,DEBIT,KREDIT,@bal:=(@bal+KREDIT)-DEBIT as balance FROM simpanan
                                join anggota on anggota.CIF = simpanan.cif
                                where simpanan.cif=? order by simpanan.REFF", $ciff);
                            catat_transaksi($unit, $kode_trans, '1120000', $tgl_system, 'Bukti Umum', $ket, 'kredit', $nominal, $userid);
                            catat_transaksi($unit, $kode_trans, '2101000', $tgl_system, 'Bukti Umum', $ket, 'debet', $nominal, $userid);
                        } else {
                            // pesan versi lama memang tidak berpenutup kutip (alert tidak tampil)
                            $pesan[] = "<script>alert('Maaf,.Gagal Input );</script>";
                            $pindah();
                        }
                    }
                    break;

                case "03":
                    $ket1 = "Setoran Angsuran An $nama";
                    $inputtrnsk = db_jalankan("Insert into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,blok,tgl_input,kode_transaksi)
                        VALUES(?,?,?,?,?,0,'01',?,?,?,'',?,'1',?,?)", 'sssssssssss',
                        (string) $tgl_system, $norek, (string) $unit, (string) $ciff, (string) $code_kel, $nominal, (string) $userid, $ket1, (string) $cao, $tanggal, $kode_trans);
                    if ($inputtrnsk) {
                        $ket = "Setoran Angsuran An $nama";
                        db_jalankan("Update  tabungan_detail set saldo =(select(sum(KREDIT)-SUM(DEBIT)) as saldo from simpanan where NOREK=?) where no_anggota  =?", 'ss', $norek, $norek)
                            or die(mysqli_error(db_ni()));
                        $nomor_jurnal = nomor_jurnal_berikut($unit);
                        db_jalankan("insert into jurnal_masuk(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values(?,?,?,?)", 'ssss',
                            (string) $nomor_jurnal, $kode_trans, (string) $tgl_system, (string) $unit);
                        catat_transaksi($unit, $kode_trans, '1120000', $tgl_system, 'Bukti SYSTEM', $ket, 'debet', $nominal, $userid);
                        catat_transaksi($unit, $kode_trans, '2101000', $tgl_system, 'Bukti SYSTEM', 'Simpanan Pokok', 'kredit', $nominal, $userid);
                        $alert("Nominal sudah ditambahkan pada rekening $norek");
                        $mutasi = $ambil_mutasi("SELECT simpanan.NOREK,simpanan.BUSS_DATE,anggota.CUST_SHORT_NAME,DEBIT,KREDIT,@bal:=(@bal+KREDIT)-DEBIT as balance FROM simpanan
                            join anggota on anggota.cif = simpanan.cif
                            where simpanan.cif=? order by simpanan.REFF", $ciff);
                    } else {
                        $alert('Maaf,.Porses Input Gagal');
                        $pindah();
                    }
                    break;

                case "04":
                    $nomor_jurnal = nomor_jurnal_berikut($unit);
                    $dasar = array('BUSS_DATE' => $buss_date, 'NOREK' => $norek, 'UNIT' => $unit, 'CIF' => $ciff, 'CODE_KEL' => $code_kel);
                    $isi = function ($debit, $type, $kredit) use ($dasar, $userid, $keterangan, $cao, $kode_trans) {
                        return $dasar + array('DEBIT' => $debit, 'TYPE' => $type, 'KREDIT' => $kredit, 'USERID' => $userid, 'KET' => $keterangan, 'REFF' => '', 'CAO' => $cao, 'kode_transaksi' => $kode_trans);
                    };
                    if ($baki == "Debet") {
                        if ($simpan == "pokok") {
                            $gl = '3102000';
                            catat_simpanan('simpanan_pokok', $isi($nominal, '04', 0));
                        } else {
                            $gl = '3202000';
                            catat_simpanan('simpanan_wajib', $isi($nominal, '05', 0));
                        }
                        catat_simpanan('simpanan', $isi(0, '01', $nominal));
                        // versi lama memakai $kode_transaksi yang belum diisi: kode transaksi jurnal_masuk kosong
                        db_jalankan("insert into jurnal_masuk(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values(?,'',?,?)", 'sss',
                            (string) $nomor_jurnal, (string) $tgl_system, (string) $unit);
                        catat_transaksi($unit, $kode_trans, $gl, $tgl_system, 'Bukti SYSTEM', $keterangan, 'debet', $nominal, $userid);
                        catat_transaksi($unit, $kode_trans, '2101000', $tgl_system, 'Bukti SYSTEM', $keterangan, 'kredit', $nominal, $userid);
                    } else {
                        if ($simpan == "pokok") {
                            $gl = '3102000';
                            catat_simpanan('simpanan_pokok', $isi(0, '04', $nominal));
                        } else {
                            $gl = '3202000';
                            // versi lama: TYPE '04' juga untuk simpanan_wajib
                            catat_simpanan('simpanan_wajib', $isi(0, '04', $nominal));
                        }
                        catat_simpanan('simpanan', $isi($nominal, '01', 0));
                        db_jalankan("insert into jurnal_masuk(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values(?,'',?,?)", 'sss',
                            (string) $nomor_jurnal, (string) $tgl_system, (string) $unit);
                        // versi lama memakai $ket yang belum diisi: keterangan jurnal debet kosong
                        catat_transaksi($unit, $kode_trans, '2101000', $tgl_system, 'Bukti SYSTEM', '', 'debet', $nominal, $userid);
                        catat_transaksi($unit, $kode_trans, $gl, $tgl_system, 'Bukti SYSTEM', 'Simpanan Pokok', 'kredit', $nominal, $userid);
                    }
                    $alert("Pemindah Bukuan an $cif Suksess");
                    $mutasi = $ambil_mutasi("SELECT simpanan.NOREK,simpanan.BUSS_DATE,anggota.CUST_SHORT_NAME,DEBIT,KREDIT,@bal:=(@bal+KREDIT)-DEBIT as balance FROM simpanan
                        join anggota on anggota.NOREK = simpanan.NOREK
                        where simpanan.NOREK=? order by simpanan.REFF", $norek);
                    break;

                case "05":
                    $ket = "Setoran Angsuran WO An $nama";
                    db_jalankan("Update  tabungan_detail set saldo =(select(sum(KREDIT)-SUM(DEBIT)) as saldo from simpanan where NOREK=?) where no_anggota  =?", 'ss', $norek, $norek)
                        or die(mysqli_error(db_ni()));
                    $nomor_jurnal = nomor_jurnal_berikut($unit);
                    db_jalankan("insert into jurnal_masuk(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values(?,?,?,?)", 'ssss',
                        (string) $nomor_jurnal, $kode_trans, (string) $tgl_system, (string) $unit);
                    catat_transaksi($unit, $kode_trans, '1120000', $tgl_system, 'Bukti SYSTEM', $ket, 'debet', $nominal, $userid);
                    catat_transaksi($unit, $kode_trans, '42356', $tgl_system, 'Bukti SYSTEM', $ket, 'kredit', $nominal, $userid);
                    catat_transaksi($unit, $kode_trans, '9910000', $tgl_system, 'Bukti SYSTEM', $ket, 'debet', $nominal, $userid);
                    catat_transaksi($unit, $kode_trans, '9141000', $tgl_system, 'Bukti SYSTEM', $ket, 'kredit', $nominal, $userid);
                    // versi lama: CIF diambil dari data loan (kosong bila anggota tidak punya loan)
                    db_jalankan("update loan_wo set os=os - ?  WHERE cif =?", 'ss', $nominal, (string) $cif) or die(mysqli_error(db_ni()));
                    $alert("Nominal sudah ditambahkan pada rekening $norek");
                    $mutasi = $ambil_mutasi("SELECT simpanan.NOREK,simpanan.BUSS_DATE,anggota.CUST_SHORT_NAME,DEBIT,KREDIT,@bal:=(@bal+KREDIT)-DEBIT as balance FROM simpanan
                        join anggota on anggota.cif = simpanan.cif
                        where simpanan.cif=? order by simpanan.REFF", $ciff);
                    break;

                default:
            }
        }
    } else {
        $alert('Maaf,Nomor Rekening tidak ditemukan');
        $pindah();
    }
}

$jenis_transaksi = array(
    '01' => 'Setoran Tunai Simpanan Wajib/Pokok',
    '02' => 'Tarik Tunai',
    '03' => 'Setoran Angsuran',
    '04' => 'Pemindah Bukuan',
    '05' => 'Setoran Angsuran Wo',
);

halaman_awal('Transaksi Setoran dan Penarikan');
?>
<div class="card card-ni">
  <div class="card-header">Input Data</div>
  <div class="card-body">
    <form method="post" action="stor.php" name="postform" class="form-ni" autocomplete="off">
      <div class="row g-2 mb-2">
        <label for="kode" class="col-sm-4 col-form-label">No Rekening</label>
        <div class="col-sm-4 autocomplete">
          <input type="text" name="kode_rekening" id="kode" class="form-control form-control-sm" placeholder="CIF / nama"
                 data-autocomplete="ajax/cari_rekening.php" data-isi-nilai="norek" data-isi-label="norek,nama,unit,nama_kel"
                 data-isi-target='{"#country":"nama"}'>
        </div>
        <div class="col-sm-4"><input type="text" id="country" class="form-control form-control-sm" disabled aria-label="Nama anggota"></div>
      </div>
      <div class="row g-2 mb-2">
        <label for="nominal" class="col-sm-4 col-form-label">Nominal</label>
        <div class="col-sm-4">
          <input type="text" name="nama" id="nominal" class="form-control form-control-sm" inputmode="numeric" data-hanya-angka>
        </div>
        <div class="col-sm-4 col-form-label text-muted" id="format" aria-live="polite"></div>
      </div>
      <div class="row g-2 mb-2">
        <label for="ket" class="col-sm-4 col-form-label">Keterangan</label>
        <div class="col-sm-8"><input type="text" name="ket" id="ket" class="form-control form-control-sm"></div>
      </div>
      <div class="row g-2 mb-2">
        <label for="select" class="col-sm-4 col-form-label">Jenis Transaksi</label>
        <div class="col-sm-8">
          <select name="select" id="select" class="form-select form-select-sm">
            <option>Pilih Salah satu</option>
            <?php foreach ($jenis_transaksi as $nilai => $label): ?>
            <option value="<?php echo h($nilai); ?>"><?php echo h($label); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
      <div class="row g-2 mb-2 khusus-pb" hidden>
        <label for="baki" class="col-sm-4 col-form-label">Jenis Pemindah Bukuan</label>
        <div class="col-sm-8">
          <select name="baki" id="baki" class="form-select form-select-sm">
            <option value="Debet">Debet</option>
            <option value="Kredit">Kredit</option>
          </select>
        </div>
      </div>
      <div class="row g-2 mb-3 khusus-pb" hidden>
        <label for="simpan" class="col-sm-4 col-form-label">Jenis Simpanan</label>
        <div class="col-sm-8">
          <select name="simpan" id="simpan" class="form-select form-select-sm">
            <option value="pokok">Pokok</option>
            <option value="wajib">wajib</option>
          </select>
        </div>
      </div>
      <div class="text-end">
        <button type="submit" name="search" value="Input" class="btn btn-ni btn-sm px-4">Input</button>
      </div>
    </form>
  </div>
</div>

<?php echo implode("\n", $pesan); ?>

<div class="table-responsive">
  <table class="table table-sm table-bordered table-ni align-middle">
    <thead>
      <tr>
        <th>NOREK</th>
        <th>NAMA</th>
        <th>TGL TRANSAKSI</th>
        <th class="text-end">DEBIT</th>
        <th class="text-end">KREDIT</th>
        <th class="text-end">SALDO</th>
      </tr>
    </thead>
    <tbody>
    <?php foreach ($mutasi as $data): ?>
      <tr>
        <td><?php echo h($data['NOREK']); ?></td>
        <td><?php echo h($data['CUST_SHORT_NAME']); ?></td>
        <td><?php echo h(tgl_indo($data['BUSS_DATE'])); ?></td>
        <td class="text-end"><?php echo number_format(compat_num($data['DEBIT'])); ?></td>
        <td class="text-end"><?php echo number_format(compat_num($data['KREDIT'])); ?></td>
        <td class="text-end"><?php echo number_format(compat_num($data['balance'])); ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
<script>
// tampilan nominal (Rp. 1.500.000) dan isian khusus Pemindah Bukuan, sama dengan versi lama
document.getElementById('nominal').addEventListener('input', function () {
    var angka = this.value.replace(/[^0-9]/g, '');
    document.getElementById('format').textContent = angka === '' ? '' : 'Rp. ' + Number(angka).toLocaleString('id-ID');
});
document.getElementById('select').addEventListener('change', function () {
    var pb = this.value === '04';
    document.querySelectorAll('.khusus-pb').forEach(function (el) { el.hidden = !pb; });
});
</script>
<?php
halaman_akhir();
