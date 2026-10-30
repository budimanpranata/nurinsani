<?php require_once __DIR__ . '/koneksi_db.php';
/**
 * Realisasi Musyarakah langkah 3: proses anggota yang dipilih di yra_list_mus.php.
 *
 * Metode mengikuti proses EOD (proses_eod_realisasi_mus.php), tanggal mengikuti proses manual lama:
 *   - jurnal_umum.tanggal_selesai = tgl_wakalah; tabel_transaksi, simpanan, rekap_nisbah = tgl_akad
 *   - user pencatat transaksi & simpanan = user yang login
 *   - nisbah ditulis ke rekap_nisbah (EOD menulis ke rekap_nsibah yang tidak ada)
 * Semua anggota diproses dalam satu transaksi database seperti EOD.
 */
date_default_timezone_set('Asia/Jakarta');
session_start();
require_once __DIR__ . '/inc/cek_login.php';
wajib_login();
ob_start();
$userid = $_SESSION['userid'];

include "function.php";

$cekbox = isset($_POST['cekbox']) ? $_POST['cekbox'] : null;
if (!$cekbox) {
    echo '<script>alert("anda belum memilih data."); window.location="realisasi_musyarokah.php"</script>';
    exit;
}

$con = db_ni();
mysqli_autocommit($con, false);

foreach ($cekbox as $value) {
    $kode_tr = 5;
    $kode_tr = acakangkahuruf($kode_tr);
    $row = db_satu("select DATE_ADD(tgl_wakalah,INTERVAL 7 DAY) as tgl_murab1,DATE_ADD(tgl_akad,INTERVAL 26 WEEK) as matur,tgl_murab,tgl_akad,calon_loan.buss_date,calon_loan.code_kel,calon_loan.no_anggota,calon_loan.Cust_Short_name,maturity_date,
        calon_loan.Deal_type,suffix,bagi_hasil,Tenor,Plafond,os,angsuran,pokok,ijaroh,bulat,ke,usaha,calon_loan.unit,tgl_wakalah,hari,calon_loan.cao,calon_loan.USERID,calon_loan.status,calon_loan.cif,nisbah
        from calon_loan
        inner join anggota on anggota.cif = calon_loan.cif
        left join nisbah on nisbah.cif = calon_loan.cif
        WHERE calon_loan.cif = ? and status_app = 'MUSYAROKAH'", 's', (string) $value);
    if (!$row) {
        // seperti EOD: data yang tidak memenuhi (bukan MUSYAROKAH / tanpa anggota) dilewati
        continue;
    }
    $no_anggota = $row['no_anggota'];
    $tgl_wakalah = $row['tgl_wakalah'];
    $cif = $row['cif'];
    $Plafond = $row['Plafond'];
    $bagi_hasil = $row['bagi_hasil'];
    $murab = $row['tgl_murab'];
    $tgl_akad = $row['tgl_akad'];
    $buss_date = $row['buss_date'];
    $code_kel = $row['code_kel'];
    $Cust_Short_name = $row['Cust_Short_name'];
    $Deal_type = $row['Deal_type'];
    $suffix = $row['suffix'];
    $Tenor = $row['Tenor'];
    $os = $row['os'];
    $nisbah = $row['nisbah'];
    $angsuran = $row['angsuran'];
    $pokok = $row['pokok'];
    $ijaroh = $row['ijaroh'];
    $bulat = $row['bulat'];
    $maturity_date = $row['maturity_date'];

    // sama dengan EOD (dan versi lama): kondisi berupa penugasan, sehingga $ke selalu 1
    if ($suffix = 1) {
        $ke = 1;
    } else {
        $ke = ((int) $suffix) + 1;
    }

    $unit = $row['unit'];
    $hari = $row['hari'];
    $cao = $row['cao'];
    $USERID = $row['USERID'];
    $norek = "$no_anggota";
    $ket = "Realisasi MUSYARAKAH $Cust_Short_name";

    $check = db_satu("SELECT os FROM loan WHERE cif = ?", 's', (string) $cif);
    $os_lama = $check ? $check['os'] : null;
    if ($os_lama > 0) {
        // seperti EOD: anggota yang masih punya OS dicatat ke gagal_loan dan tidak direalisasi
        db_jalankan("insert INTO gagal_loan select * from calon_loan WHERE cif = ?", 's', (string) $cif);
        continue;
    }

    $kodetr = 5;
    $kodetr = acakangkahuruf($kodetr);
    $kode_trans = "BU/" . $unit . $kodetr;

    db_jalankan("insert into jurnal_umum(kode_transaksi,tanggal_selesai) values(?,?)", 'ss',
        (string) $kode_trans, (string) $tgl_wakalah);

    // Dr Aset Musyarakah, Cr Kas sebesar Plafond
    db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
        values(?,?,'1472000',?,'Bukti SYSTEM',?,?,?)", 'ssssss',
        (string) $unit, (string) $kode_trans, (string) $tgl_akad, (string) $ket, (string) $Plafond, (string) $userid);
    db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
        values(?,?,'1120000',?,'Bukti SYSTEM',?,?,?)", 'ssssss',
        (string) $unit, (string) $kode_trans, (string) $tgl_akad, (string) $ket, (string) $Plafond, (string) $userid);

    // EOD mengisi omzet dari variabel yang tidak pernah diisi (kosong); dipertahankan
    db_jalankan("insert into rekap_nisbah(cif,nisbah,omzet,tgl_realisasi) values(?,?,?,?)", 'ssss',
        (string) $cif, (string) $nisbah, '', (string) $tgl_akad);

    db_jalankan("delete from loan WHERE cif = ?", 's', (string) $cif);
    db_jalankan("delete from tunggakan WHERE cif = ?", 's', (string) $cif);
    db_jalankan("delete from omzet WHERE cif = ?", 's', (string) $cif);
    db_jalankan("insert INTO loan select * from calon_loan WHERE cif = ?", 's', (string) $cif);
    db_jalankan("INSERT INTO `loan_detail` (code_kel,buss_date,no_anggota,cif,suffic,Cust_Short_name,Deal_type,bagi_hasil,Tenor,Plafond,unit,Contract_Date,hari_tagih,angsuran,storan,cao,USERID)
        VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)", 'sssssssssssssssss',
        (string) $code_kel, (string) $buss_date, (string) $no_anggota, (string) $cif, (string) $ke, (string) $Cust_Short_name,
        (string) $Deal_type, (string) $bagi_hasil, (string) $Tenor, (string) $Plafond, (string) $unit, (string) $murab,
        (string) $hari, (string) $angsuran, (string) $bulat, (string) $cao, (string) $USERID);

    db_jalankan("insert into simpanan(BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,KREDIT,TYPE,USERID,KET,REFF,CAO,kode_transaksi)
        values(?,?,?,?,?,?,'01',?,?,'',?,?)", 'ssssssssss',
        (string) $tgl_akad, (string) $norek, (string) $unit, (string) $cif, (string) $code_kel, (string) $Plafond,
        (string) $userid, (string) $ket, (string) $cao, (string) $kode_trans);
    db_jalankan("insert into simpanan(BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,kode_transaksi)
        values(?,?,?,?,?,?,'01','0',?,?,'',?,?)", 'ssssssssss',
        (string) $tgl_akad, (string) $norek, (string) $unit, (string) $cif, (string) $code_kel, (string) $Plafond,
        (string) $userid, (string) $ket, (string) $cao, (string) $kode_trans);
    db_jalankan("UPDATE simpanan SET blok='', TYPE='01' where cif = ? and type ='02'", 's', (string) $cif);
    db_jalankan("UPDATE loan SET status='CETAK',gol ='1', deal_produk ='3' where cif = ?", 's', (string) $cif);

    db_jalankan("INSERT INTO `rek_loan` (tgl_realisasi,unit,no_anggota,saldo_kredit,debet,tipe,ket,USERID,STATUS,cif,AO)
        VALUES (?,?,?,?,'0','L001',?,?,'REALISASI MURABAHAH',?,?)", 'ssssssss',
        (string) $buss_date, (string) $unit, (string) $no_anggota, (string) $os, (string) $ket, (string) $USERID,
        (string) $cif, (string) $cao);
    db_jalankan("delete from `calon_loan` WHERE cif = ?", 's', (string) $cif);
    db_jalankan("delete from pinjaman_detail_mus where cif = ?", 's', (string) $cif);
    db_jalankan("delete from pinjaman_detail where cif = ?", 's', (string) $cif);

    // jadwal angsuran mingguan dari tanggal akad sampai jatuh tempo (seperti EOD)
    $awal_hari = $tgl_akad;
    $akhir_hari = $maturity_date;
    $x1 = 0;
    while (strtotime($awal_hari) < strtotime($akhir_hari)) {
        $x1++;
        $reff = "$no_anggota$x1";
        $awal_hari = date("Y-m-d", strtotime("+7 DAY", strtotime($awal_hari)));
        db_jalankan("INSERT INTO pinjaman_detail_temp_mus (id,id_pinjam,cicilan,angsuran_pokok,bunga,tgl_jatuh_tempo,tgl_bayar,jumlah_bayar,ket,cif,unit,ao,code_kel)
            values(?,?,?,?,?,?,?,'0','',?,?,?,?)", 'sssssssssss',
            (string) $reff, (string) $no_anggota, (string) $x1, (string) $pokok, (string) $ijaroh, (string) $awal_hari,
            (string) $awal_hari, (string) $cif, (string) $unit, (string) $cao, (string) $code_kel);
    }
    db_jalankan("delete from pinjaman_detail_temp_mus where cicilan > ? AND cif = ?", 'ss', (string) $Tenor, (string) $cif);
}

if (!mysqli_commit($con)) {
    echo "Transaksi Gagal commit Silahkan ulangi lagi";
    exit();
}
mysqli_autocommit($con, true);

echo '<script>alert("Musyarakah sudah direalisasikan."); window.location="realisasi_musyarokah.php"</script>';
