<?php require_once __DIR__ . '/../koneksi_db.php';
/**
 * Menu Approve > Status Pengajuan (level lead), per CIF calon_loan:
 *   01 Turun Plafond   : jurnal BU/ (balik wakalah sebesar plafond lama, wakalah baru sebesar harga baru, selisih ke
 *                        2500000) lalu calon_loan dihitung ulang dari param_biaya dan status_app jadi MURAB
 *   02 Ajukan Kembali  : status_app jadi MUSYAROKAH (bila jenis_akad unit MUSYAROKAH) atau PENDING
 *   03 Hapus Pengajuan : hapus calon_loan
 * Logika dan urutan sama dengan versi lama; hanya tampilan dan query (prepared statement) yang berubah. Saran CIF dari
 * ajax/cari_pengajuan_lead.php (query sama dengan lead/autosuggesst_rek_aju.php).
 * Penyesuaian PHP 8: hitungan memakai aturan PHP 5 (harga baru kosong = 0). Di PHP 8 versi lama berhenti dengan
 * TypeError untuk Ajukan Kembali / Hapus Pengajuan (harga baru kosong), sehingga keduanya tidak pernah jalan.
 * Perilaku lama yang dipertahankan dicatat di PEKERJAAN_TERTUNDA.md bagian 40 (termasuk jurnal tetap dibuat walau
 * harga baru lebih besar / parameter tidak ditemukan).
 */
require_once __DIR__ . '/../inc/tampilan.php';
session_start();
ob_start();
$userid = isset($_SESSION['userid']) ? $_SESSION['userid'] : null;
$tgl_system = isset($_SESSION['stgl_system']) ? $_SESSION['stgl_system'] : '';
//cek apakah user sudah login
if (!isset($_SESSION['userid'])) {
    header("location:index.html");
    exit;
}
if ($_SESSION['level'] != "lead") {
    header("location:../index.html");
    exit;
}
include "../function.php";

$cif_kel = isset($_POST['kode_rekening']) ? (string) $_POST['kode_rekening'] : '';
$pilih = isset($_POST['select']) ? (string) $_POST['select'] : '';
$status_pengajuan = $pilih == '01' ? "Turun Plafond" : "Pengajuan";
$harga_baru = isset($_POST['harga_baru']) ? (string) $_POST['harga_baru'] : '';

$skrip = array();
$select = array();
$plafond = '';
$done = '';
/** Nilai kolom sebagai teks seperti mysql_fetch_array versi lama ('' bila tidak ada). */
$teks = function ($baris, $k) {
    return $baris && isset($baris[$k]) && $baris[$k] !== null ? (string) $baris[$k] : '';
};

if (isset($_POST['search'])) {
    if (empty($cif_kel) || empty($pilih)) {
        $skrip[] = "alert('Maaf,.isi CIF dan Jenis Pengajuan');";
        // versi lama mengarah ke buka_bokir.php (halaman tidak ada) - dipertahankan
        $skrip[] = "document.location.href='buka_bokir.php';";
    } else {
        $cek_cif = db_satu("select Plafond,Cust_Short_name,Tenor,unit,cif from calon_loan WHERE cif=?", 's', $cif_kel);
        // versi lama: CIF tidak ada -> die(), proses berhenti tanpa pesan
        if ($cek_cif) {
            $plafond = $teks($cek_cif, 'Plafond');
            $selisih = compat_num($plafond) - compat_num($harga_baru);
            $tenor = $teks($cek_cif, 'Tenor');
            $unit = $teks($cek_cif, 'unit');
            $cif = $teks($cek_cif, 'cif');
            $nama = $teks($cek_cif, 'Cust_Short_name');
            $done = 'Done';
            $ket = "Turun Plafond $cif $nama";

            switch ($pilih) {
                case "01":
                    // nilai hitungan; tetap kosong bila harga baru lebih besar / parameter tidak ditemukan (versi lama)
                    $pokok = $margin = $ijaroh = $angsuran = $uang = $os = '';
                    if ($harga_baru > $plafond) {
                        $skrip[] = "alert ('Harga Baru lebih besar dari harga lama')";
                        $skrip[] = "document.location.href='turun_pangajuan.php';";
                    } else {
                        $pbiaya = db_satu("select margin,tab,pla,jw  FROM param_biaya where jw =? and pla=?", 'ss', $tenor, $harga_baru);
                        $param_margin = $teks($pbiaya, 'margin');
                        $param_tabung = $teks($pbiaya, 'tab');
                        $param_tenor = $teks($pbiaya, 'jw');
                        $param_pla = $teks($pbiaya, 'pla');
                        if ($param_tenor == $tenor and $harga_baru == $param_pla) {
                            $pokok = compat_bagi(compat_num($harga_baru), compat_num($tenor));
                            $margin = compat_num($harga_baru) * (compat_num($param_margin) / 100);
                            $ijaroh = compat_bagi($margin, compat_num($tenor));
                            $angsuran = compat_num($ijaroh) + compat_num($pokok);
                            $uang = compat_num($param_tabung) + $angsuran;
                            $os = compat_num($harga_baru) + $margin + (2500 * compat_num($tenor));
                            $margin = compat_num($harga_baru) * (compat_num($param_margin) / 100) + (2500 * compat_num($tenor));
                        } else {
                            $skrip[] = "alert ('Parameter Pembiayaan tidak ditemukan')";
                            $skrip[] = "document.location.href='turun_pangajuan.php';";
                        }
                    }
                    // versi lama: jurnal dan update di bawah tetap dijalankan di semua kondisi di atas
                    $kode_trans = "BU/" . $unit . acakangkahuruf(5);
                    //pencatatan jurnal umum (nomor_jurnal kosong di versi lama -> nomor otomatis)
                    db_jalankan("insert into jurnal_umum(nomor_jurnal,kode_transaksi,tanggal_selesai) values(?,?,?)", 'sss', '', $kode_trans, (string) $tgl_system);
                    $catat = function ($rekening, $kolom, $nilai) use ($unit, $kode_trans, $tgl_system, $ket, $userid) {
                        db_jalankan("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,$kolom,id_admin)
                            values(?,?,?,?,'Bukti SYSTEM',?,?,?)", 'sssssss',
                            $unit, $kode_trans, $rekening, (string) $tgl_system, $ket, (string) $nilai, (string) $userid);
                    };
                    //Reverse Transaksi Wakalah sebesar Plafond
                    $catat('1962000', 'debet', $plafond);
                    $catat('1431000', 'kredit', $plafond);
                    //Proses Wakalah turun Plafond sebesar harga baru
                    $catat('1431000', 'debet', $harga_baru);
                    $catat('1962000', 'kredit', $harga_baru);
                    //selisih
                    $catat('2500000', 'debet', $selisih);
                    $catat('1962000', 'kredit', $selisih);
                    $cek = db_jalankan("update calon_loan set plafond=?,os=?,angsuran=?,ijaroh=?,bulat=?,pokok=?,saldo_margin=?,bagi_hasil=?,status_app='MURAB' where cif =?",
                        'sssssssss', $harga_baru, (string) $os, (string) $angsuran, (string) $ijaroh, (string) $uang, (string) $pokok,
                        (string) $margin, (string) $margin, $cif_kel);
                    if ($cek) {
                        $skrip[] = "alert('  Turun Plafond Selelsai diproses');";
                        $select = db_semua("select * from calon_loan where cif=?", 's', $cif_kel);
                    } else {
                        $skrip[] = "alert('Turun Plafond Gagal di proses..');";
                    }
                    break;
                case "02":
                    $cek_jenis = db_satu("select nama_akad from jenis_akad WHERE unit=?", 's', $unit);
                    // versi lama: jenis_akad unit tidak ada -> die(), proses berhenti tanpa pesan
                    if ($cek_jenis) {
                        $status_app = $cek_jenis['nama_akad'] == 'MUSYAROKAH' ? 'MUSYAROKAH' : 'PENDING';
                        $cek = db_jalankan("update calon_loan set status_app=? where cif =?", 'ss', $status_app, $cif_kel);
                        if ($cek) {
                            $skrip[] = "alert('Proses Pengajuan Kembali Selesai');";
                            $select = db_semua("select * from calon_loan where cif=?", 's', $cif_kel);
                        } else {
                            $skrip[] = "alert('Pengajuan Kembali Gagal di proses..');";
                        }
                    }
                    break;
                case "03":
                    $cek = db_jalankan("delete from calon_loan where cif =?", 's', $cif_kel);
                    if ($cek) {
                        $skrip[] = "alert('Proses Reject Selesai');";
                        $select = db_semua("select * from calon_loan where cif=?", 's', $cif_kel);
                    } else {
                        $skrip[] = "alert('Pengajuan Kembali Gagal di proses..');";
                    }
                    break;
                default:
            }
        }
    }
}

halaman_awal('Status Pengajuan');
foreach ($skrip as $s) {
    echo "<script>$s</script>";
}
?>
<div class="card card-ni mb-3">
  <div class="card-header">Input Data</div>
  <div class="card-body">
    <form method="post" action="turun_pangajuan.php" name="postform" class="row g-3" autocomplete="off">
      <div class="col-sm-6">
        <label class="form-label" for="select">Status Pengajuan</label>
        <select name="select" id="select" class="form-select">
          <option>Pilih Salah satu</option>
          <option value="01">Turun Plafond</option>
          <option value="02">Ajukan Kembali </option>
          <option value="03">Hapus Pangajuan </option>
        </select>
      </div>
      <div class="col-sm-6">
        <label class="form-label" for="kode">CIF</label>
        <div class="autocomplete position-relative">
          <input type="text" name="kode_rekening" id="kode" class="form-control"
                 data-autocomplete="../ajax/cari_pengajuan_lead.php" data-isi-nilai="cif" data-isi-label="label"
                 data-isi-target='{"#country":"nama"}'>
        </div>
      </div>
      <div class="col-sm-6">
        <label class="form-label" for="country">Nama</label>
        <input type="text" disabled="disabled" name="nama_rekening" id="country" class="form-control">
      </div>
      <?php // versi lama: isian Harga Baru hanya tampil bila Turun Plafond dipilih ?>
      <div class="col-sm-6 d-none" id="grup-harga-baru">
        <label class="form-label" for="harga_baru">Harga Baru</label>
        <input type="text" name="harga_baru" id="harga_baru" class="form-control" inputmode="numeric">
        <div class="form-text" id="format"></div>
      </div>
      <div class="col-12">
        <button name="search" type="submit" id="submit2" value="Proses" class="btn btn-ni">Proses</button>
      </div>
    </form>
  </div>
</div>

<div class="card card-ni mw-100">
  <div class="table-responsive">
    <table class="table table-sm table-bordered table-ni align-middle mb-0">
      <thead>
        <tr>
          <th>Norek</th>
          <th>Nama</th>
          <th>Tgl Akad</th>
          <th>Status Pengajuan</th>
          <th class="text-end">Harga Baru</th>
          <th class="text-end">Harga Lama</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($select as $data): ?>
        <tr>
          <td><?php echo h($data['no_anggota']); ?></td>
          <td><?php echo h($data['Cust_Short_name']); ?></td>
          <td><?php echo h(tgl_indo($data['tgl_akad'])); ?></td>
          <td><?php echo h($status_pengajuan); ?></td>
          <td class="text-end"><?php echo number_format(compat_num($harga_baru)); ?></td>
          <td class="text-end"><?php echo number_format(compat_num($plafond)); ?></td>
          <td><?php echo h($done); ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<script>
(function () {
    var pilih = document.getElementById('select');
    var grup = document.getElementById('grup-harga-baru');
    var harga = document.getElementById('harga_baru');
    // versi lama: tampil saat 01, sembunyi saat 02 (pilihan lain tidak mengubah tampilan)
    pilih.addEventListener('change', function () {
        if (pilih.value === '01') {
            grup.classList.remove('d-none');
        } else if (pilih.value === '02') {
            grup.classList.add('d-none');
        }
    });
    // hanya angka, dan tampilan rupiah di bawah isian (versi lama: formatCurrency)
    harga.addEventListener('keypress', function (evt) {
        var kode = evt.which || evt.keyCode;
        if (kode > 31 && (kode < 48 || kode > 57)) {
            evt.preventDefault();
        }
    });
    harga.addEventListener('keyup', function () {
        var angka = harga.value.replace(/[^0-9]/g, '') || '0';
        document.getElementById('format').textContent = 'Rp. ' + angka.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    });
})();
</script>
<?php
halaman_akhir();
