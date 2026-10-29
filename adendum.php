<?php require_once __DIR__ . '/koneksi_db.php';
/**
 * Menu Cetak Dokumen > Cetak Adendum: cari kelompok + jenis adendum, lalu cetak (cetak_adendum.php).
 *
 * PERHATIAN (perilaku lama, dipertahankan): Search juga memproses ulang jadwal anggota kelompok
 * yang ada di rest_covid: update maturity_date, hapus pinjaman_detail & pinjaman_detail_covid,
 * lalu buat jadwal baru di pinjaman_detail_covid.
 */
require_once __DIR__ . '/inc/tampilan.php';
session_start();
$userid = $_SESSION['userid'];
$tgl_system = $_SESSION['stgl_system'];

//cek apakah user sudah login
if (!isset($_SESSION['userid'])) {
    header('location:index.html');
    exit;
}

set_time_limit(0);
$kelompok = isset($_POST['kelompok']) ? (string) $_POST['kelompok'] : '';
$nama = isset($_POST['nama']) ? (string) $_POST['nama'] : '';
$jenis = isset($_POST['select']) ? (string) $_POST['select'] : '';

$r = db_satu('SELECT kode_branch FROM user WHERE userid = ?', 's', $userid);
$branch = $r ? $r['kode_branch'] : '';

$h = db_satu('SELECT hari, no_anggota, cao FROM loan WHERE code_kel = ?', 's', $kelompok);
// versi lama menyisipkan nilai langsung ke SQL: data yang tidak ada menjadi string kosong
$hari = $h ? (string) $h['hari'] : '';
$no_anggota = $h ? (string) $h['no_anggota'] : '';
$cao = $h ? (string) $h['cao'] : '';

/** Tampilkan alert lalu kembali ke adendum.php (sama seperti versi lama). */
function alert_kembali($pesan)
{
    echo '<script>alert(' . json_encode($pesan) . ');</script>';
    echo "<script language='javascript'>document.location.href='adendum.php'</script>";
    exit;
}

$hasil = array();
if (!empty($_POST['search'])) {
    if (empty($kelompok) || empty($jenis)) {
        alert_kembali('Maaf,isi kode kelompok dan Jenis cetak');
    }
    $db = db_ni();
    $st = $db->prepare("SELECT COUNT(no_anggota) AS noa, unit, kelompok.nama_kel, loan.cao, tgl_wakalah FROM loan
        INNER JOIN kelompok ON kelompok.code_kel = loan.code_kel
        LEFT JOIN rest_covid ON loan.cif = rest_covid.cif
        WHERE loan.code_kel = ? AND loan.unit = ? AND status_app = ?
        GROUP BY unit");
    $select = $st && $st->bind_param('sss', $kelompok, $branch, $jenis) && $st->execute();

    // versi lama: diproses setiap kali query di atas berhasil (walaupun hasilnya kosong)
    if ($select) {
        $hasil = $st->get_result()->fetch_all(MYSQLI_ASSOC);
        $unit = ''; // versi lama: tidak pernah diisi
        $awal = ''; // versi lama: tidak pernah diisi
        $rest = db_semua('SELECT cif, tenor_baru, pokok_baru, ijaroh_baru FROM rest_covid WHERE code_kel = ?', 's', $kelompok);
        foreach ($rest as $data_matur) {
            $cif = $data_matur['cif'];
            $ft = $data_matur['tenor_baru'];
            $pokok = (string) $data_matur['pokok_baru'];
            $ijaroh = (string) $data_matur['ijaroh_baru'];

            $tgl_m = strtotime($tgl_system);
            $maturity_date_ress = date('Y-m-d', strtotime("+$ft WEEK", $tgl_m));

            $cek_tanggal = db_satu('SELECT COUNT(tgl_libur) AS libur FROM param_libur WHERE tgl_libur BETWEEN ? AND ? AND Hari = ?',
                'sss', (string) $tgl_system, $maturity_date_ress, $hari);
            $libur = $cek_tanggal['libur'];

            if ($libur > 0) {
                $ft = $ft + $libur;
                $tgl_m = strtotime($tgl_system);
                $maturity_date_res = date('Y-m-d', strtotime("+$ft WEEK", $tgl_m));
            } else {
                $maturity_date_res = $maturity_date_ress;
            }

            db_jalankan('UPDATE loan SET maturity_date = ? WHERE cif = ?', 'ss', $maturity_date_res, $cif);
            db_jalankan('UPDATE rest_covid SET jatpo_baru = ? WHERE cif = ?', 'ss', $maturity_date_res, $cif);
            db_jalankan('DELETE FROM pinjaman_detail WHERE cif = ?', 's', $cif);
            db_jalankan('DELETE FROM pinjaman_detail_covid WHERE cif = ?', 's', $cif);

            $awal_hari = $tgl_system;
            $akhir_hari = $maturity_date_res;
            $x1 = 0;
            while (strtotime($awal_hari) < strtotime($akhir_hari)) {
                $x1++;
                $reff = "$no_anggota$x1";
                $awal_hari = date('Y-m-d', strtotime('+7 DAY', strtotime($awal_hari)));
                db_jalankan("INSERT INTO pinjaman_detail_covid (id, id_pinjam, cicilan, angsuran_pokok, bunga, tgl_jatuh_tempo,
                        tgl_bayar, jumlah_bayar, ket, cif, unit, ao)
                    VALUES (?, ?, ?, ?, ?, ?, ?, '0', '', ?, ?, ?)",
                    'ssssssssss', $reff, $no_anggota, $x1, $pokok, $ijaroh, $awal_hari, $awal, $cif, $unit, $cao);
            }
        }
    }
    if (!$hasil) {
        // versi lama menampilkan variabel $nopen yang tidak pernah diisi, sehingga nama kelompok kosong
        alert_kembali('Maaf...,Kelompok  tidak ditemukan!!!');
    }
}

halaman_awal('Cetak Adendum Murabahah');
?>
<div class="card card-ni">
  <div class="card-header">Kriteria Pencarian</div>
  <div class="card-body">
    <form method="post" action="adendum.php" class="form-ni" autocomplete="off">
      <div class="row g-2 mb-2">
        <label for="kode" class="col-sm-4 col-form-label">Nomor Kelompok</label>
        <div class="col-sm-8 autocomplete">
          <input type="text" name="kelompok" id="kode" class="form-control form-control-sm" value="<?php echo h($kelompok); ?>"
                 placeholder="ketik kode atau nama kelompok"
                 data-autocomplete="ajax/cari_kelompok.php" data-isi-nilai="code_kel"
                 data-isi-label="code_kel,nama_kel" data-isi-target='{"#nama":"nama_kel"}'>
        </div>
      </div>
      <div class="row g-2 mb-2">
        <label for="nama" class="col-sm-4 col-form-label">Nama Kelompok</label>
        <div class="col-sm-8">
          <input type="text" name="nama" id="nama" class="form-control form-control-sm" value="<?php echo h($nama); ?>">
        </div>
      </div>
      <div class="row g-2 mb-3">
        <label for="select" class="col-sm-4 col-form-label">Jenis Adendum</label>
        <div class="col-sm-8">
          <select name="select" id="select" class="form-select form-select-sm">
            <?php foreach (array('' => ' ', 'REST' => 'Restrukturisasi', 'JATPO' => 'Restrukturisasi Angsuran',
                'REST_POKOK_MARGIN' => 'Restrukturisasi POKOK MARGIN', 'COVID' => 'Covid') as $v => $t): ?>
              <option value="<?php echo h($v); ?>" <?php echo $jenis === $v ? 'selected' : ''; ?>><?php echo h($t); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
      <div class="text-end">
        <button type="submit" name="search" value="Search" class="btn btn-ni btn-sm px-4">Search</button>
      </div>
    </form>
  </div>
</div>

<div class="table-responsive">
  <table class="table table-sm table-bordered table-ni align-middle">
    <thead>
      <tr>
        <th>Unit</th>
        <th>Kelompok</th>
        <th class="text-end">Moa</th>
        <th>Nama Ao</th>
        <th>Jatpo</th>
        <th class="text-center">Proses</th>
      </tr>
    </thead>
    <tbody>
    <?php foreach ($hasil as $data): ?>
      <tr>
        <td><?php echo h($data['unit']); ?></td>
        <td><?php echo h($data['nama_kel']); ?></td>
        <td class="text-end"><?php echo h($data['noa']); ?></td>
        <td><?php echo h($data['cao']); ?></td>
        <td><?php echo h($data['tgl_wakalah']); ?></td>
        <td class="text-center">
          <a href="cetak_adendum.php?jenis=<?php echo urlencode($jenis); ?>&amp;code_kel=<?php echo urlencode($kelompok); ?>"
             class="btn btn-ni btn-sm" data-konfirmasi="anda yakin akan Cetak Murabahah ?">Detail</a>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php
halaman_akhir();
