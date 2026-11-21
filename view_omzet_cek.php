<?php require_once __DIR__ . '/koneksi_db.php';
/**
 * Menu Pemeliharaan Data > Cek Omzet & Porsi Nisbah (level unit), dibuat 2026-11-21 atas perintah user
 * (PEKERJAAN_TERTUNDA §11): jadwal musyarakah yang SUDAH disetor/ditagih (pinjaman_detail_mus cicilan <= loan.run_tenor,
 * pembiayaan MUSYAROKAH os > 0 unit user) yang omzetnya belum diinput atau porsi nisbahnya belum sesuai omzet.
 *   Porsi seharusnya = omzet tanggal setor x (loan.ijaroh / nisbah.omzet)  (rumus EOD proses_eod_mob.php)
 *                      omzet tanggal itu kosong -> nisbah tertagih minggu itu (pinjaman_detail_mus.nisbah = ijaroh)
 *   Status: Omzet belum diinput | Omzet awal kosong (tabel nisbah, porsi tidak bisa dihitung)
 *           Porsi belum sesuai omzet | Sesuai
 * Tombol "Hitung ulang CIF terpilih" (centang per CIF; POST, token sekali pakai, kunci per unit, satu transaksi): baris
 * CIF terpilih yang sudah ditagih dengan status Porsi belum sesuai -> porsi rumus EOD; Omzet belum diinput -> porsi =
 * nisbah tertagih. Omzet awal kosong, jadwal yang belum ditagih, dan CIF yang tidak dipilih tidak disentuh.
 * Perbaikan satu per satu juga bisa lewat Tambah / Edit omzet di menu Omzet dan Nisbah (crud_omzet.php).
 * Unit = userid, sama dengan menu Omzet dan Monitoring Nisbah.
 */
require_once __DIR__ . '/inc/tampilan.php';
require_once __DIR__ . '/inc/notif.php';
require_once __DIR__ . '/inc/csrf.php';
session_start();
require_once __DIR__ . '/inc/cek_login.php';
wajib_level('unit');
$unit = (string) $_SESSION['userid'];

/** Jadwal musyarakah yang sudah ditagih di unit ini beserta status dan porsi seharusnya. */
function omzet_cek_data($unit)
{
    $hasil = array();
    foreach (db_semua("SELECT d.id, d.cif, l.Cust_Short_name AS nama, k.nama_kel, a.nama_ao, d.cicilan, DATE(d.tgl_bayar) AS tgl, d.nisbah,
            d.nisbah_anggota, n.omzet AS omzet_awal, l.ijaroh,
            (SELECT o.nominal FROM omzet o WHERE o.cif = d.cif AND o.tanggal = DATE(d.tgl_bayar) ORDER BY o.id LIMIT 1) AS omzet
        FROM pinjaman_detail_mus d
        INNER JOIN loan l ON l.cif = d.cif
        LEFT JOIN nisbah n ON n.cif = d.cif
        LEFT JOIN kelompok k ON k.code_kel = l.code_kel
        LEFT JOIN ao a ON a.cao = l.cao
        WHERE l.unit = ? AND l.os > 0 AND l.status_app = 'MUSYAROKAH' AND d.cicilan <= CAST(l.run_tenor AS UNSIGNED)
        ORDER BY k.nama_kel, d.cif, d.cicilan", 's', (string) $unit) as $b) {
        $tercatat = (int) compat_num($b['nisbah_anggota']);
        if ($b['omzet'] === null) {
            // omzet kosong: porsi = nisbah tertagih minggu itu (perintah user 2026-11-21)
            $b['status'] = 'kosong';
            $b['seharusnya'] = (int) compat_num($b['nisbah']);
        } elseif (compat_num($b['omzet_awal']) <= 0) {
            $b['status'] = 'awal';
            $b['seharusnya'] = null;
        } else {
            // rumus EOD; kolom bigint menyimpan hasil yang dibulatkan
            $b['seharusnya'] = (int) round(compat_num($b['omzet']) * compat_num(compat_bagi($b['ijaroh'], $b['omzet_awal'])));
            $b['status'] = $b['seharusnya'] === $tercatat ? 'sesuai' : 'beda';
        }
        $b['perlu_diubah'] = in_array($b['status'], array('kosong', 'beda'), true) && $b['seharusnya'] !== $tercatat;
        $hasil[] = $b;
    }
    return $hasil;
}

// ---- Hitung ulang CIF terpilih (2026-11-21, perintah user: tidak semua, hanya CIF yang dipilih) ----
if (isset($_POST['hitung_ulang'])) {
    $cif_pilih = array();
    foreach ((isset($_POST['cif']) && is_array($_POST['cif']) ? $_POST['cif'] : array()) as $c) {
        $cif_pilih[(string) $c] = true;
    }
    if (!$cif_pilih) {
        notif_pergi('Belum ada CIF yang dipilih.', 'view_omzet_cek.php', 'warning');
        exit;
    }
    if (!token_sekali_pakai('omzet_cek_token', isset($_POST['token']) ? $_POST['token'] : '')) {
        notif_pergi('Proses ini sudah dijalankan atau halaman sudah kedaluwarsa. Silakan muat ulang halaman.', 'view_omzet_cek.php', 'warning');
        exit;
    }
    $kunci = 'nurinsani_omzet_cek_' . $unit;
    $k = db_satu("SELECT GET_LOCK(?, 15) AS ok", 's', $kunci);
    if (!$k || (int) $k['ok'] !== 1) {
        notif_pergi('Sistem sedang menghitung ulang porsi unit ini, coba lagi.', 'view_omzet_cek.php', 'warning');
        exit;
    }
    $db = db_ni();
    mysqli_begin_transaction($db);
    $gagal_awal = db_jumlah_gagal();
    $ubah = array('kosong' => 0, 'beda' => 0);
    // hanya baris unit ini (omzet_cek_data) milik CIF terpilih; CIF lain / unit lain diabaikan
    foreach (omzet_cek_data($unit) as $b) {
        if ($b['perlu_diubah'] && isset($cif_pilih[(string) $b['cif']])) {
            db_jalankan("UPDATE pinjaman_detail_mus SET nisbah_anggota=? WHERE id=?", 'ss', (string) $b['seharusnya'], (string) $b['id']);
            $ubah[$b['status']]++;
        }
    }
    if (db_jumlah_gagal() > $gagal_awal) {
        mysqli_rollback($db);
        db_satu("SELECT RELEASE_LOCK(?) AS ok", 's', $kunci);
        notif_pergi('Hitung ulang GAGAL, tidak ada data yang berubah. Silakan ulangi.', 'view_omzet_cek.php', 'error');
        exit;
    }
    mysqli_commit($db);
    db_satu("SELECT RELEASE_LOCK(?) AS ok", 's', $kunci);
    notif_pergi("Hitung ulang selesai (" . count($cif_pilih) . " CIF).\n\nPorsi disesuaikan dengan omzet: " . number_format($ubah['beda'], 0, ',', '.')
        . "\nOmzet kosong, porsi = nisbah: " . number_format($ubah['kosong'], 0, ',', '.'), 'view_omzet_cek.php', 'success');
    exit;
}

$saring = isset($_GET['status']) ? (string) $_GET['status'] : 'masalah';
$pilihan = array('masalah' => 'Belum sesuai (semua masalah)', 'kosong' => 'Omzet belum diinput', 'beda' => 'Porsi belum sesuai omzet',
    'awal' => 'Omzet awal kosong', 'semua' => 'Semua (termasuk yang sesuai)');
if (!isset($pilihan[$saring])) {
    $saring = 'masalah';
}

$jumlah = array('kosong' => 0, 'beda' => 0, 'awal' => 0, 'sesuai' => 0);
$tampil = array();
foreach (omzet_cek_data($unit) as $b) {
    $jumlah[$b['status']]++;
    if ($saring === 'semua' || $saring === $b['status'] || ($saring === 'masalah' && $b['status'] !== 'sesuai')) {
        $tampil[] = $b;
    }
}
$token = token_sekali_buat('omzet_cek_token');
$label = array('kosong' => array('Omzet belum diinput', 'text-bg-danger'), 'beda' => array('Porsi belum sesuai omzet', 'text-bg-warning'),
    'awal' => array('Omzet awal kosong', 'text-bg-secondary'), 'sesuai' => array('Sesuai', 'text-bg-success'));
$angka = function ($v) {
    return $v === null ? '-' : number_format(compat_num($v), 0, ',', '.');
};

halaman_awal('Cek Omzet & Porsi Nisbah', array('assets/app/datatables-ni.css'));
?>
<div class="card card-ni mb-3">
  <div class="card-body">
    <form method="get" class="row g-2 align-items-center">
      <div class="col-auto"><label for="status" class="col-form-label">Tampilkan</label></div>
      <div class="col-auto">
        <select name="status" id="status" class="form-select form-select-sm" onchange="this.form.submit()">
          <?php foreach ($pilihan as $k => $v): ?>
          <option value="<?php echo h($k); ?>"<?php echo $k === $saring ? ' selected' : ''; ?>><?php echo h($v); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-12 col-md d-flex flex-wrap gap-2 justify-content-md-end">
        <span class="badge text-bg-danger">Omzet belum diinput: <?php echo number_format($jumlah['kosong'], 0, ',', '.'); ?></span>
        <span class="badge text-bg-warning">Porsi belum sesuai: <?php echo number_format($jumlah['beda'], 0, ',', '.'); ?></span>
        <span class="badge text-bg-secondary">Omzet awal kosong: <?php echo number_format($jumlah['awal'], 0, ',', '.'); ?></span>
        <span class="badge text-bg-success">Sesuai: <?php echo number_format($jumlah['sesuai'], 0, ',', '.'); ?></span>
      </div>
    </form>
    <div class="small text-muted mt-2">
      Jadwal musyarakah yang sudah disetor. Porsi seharusnya = omzet tanggal setor &times; (nisbah / omzet awal), sama dengan proses EOD;
      bila omzet kosong, porsi = nisbah tertagih minggu itu. Perbaiki satu per satu lewat menu <a href="view_omzet.php">Omzet dan Nisbah</a>
      (tambah / edit omzet), atau centang CIF di tabel lalu tekan tombol di kanan.
    </div>
    <form method="post" class="text-end mt-2" id="form-hitung">
      <input type="hidden" name="token" value="<?php echo h($token); ?>">
      <input type="hidden" name="hitung_ulang" value="1">
      <?php // CIF terpilih ditambahkan oleh assets/app/omzet_cek.js (termasuk dari halaman tabel lain) ?>
      <button type="submit" class="btn btn-ni btn-sm" id="tombol-hitung" disabled
              data-konfirmasi="Hitung ulang porsi nisbah CIF yang dipilih? (Hanya jadwal yang sudah ditagih: porsi belum sesuai -> rumus omzet; omzet kosong -> porsi = nisbah. Jadwal yang belum ditagih dan omzet awal kosong tidak disentuh.)">
        Hitung ulang CIF terpilih (<span id="jumlah-cif">0</span>)
      </button>
    </form>
  </div>
</div>

<div class="card shadow-sm border-0">
  <div class="card-body">
    <div class="table-responsive">
      <table id="cekTable" class="table table-sm table-bordered table-ni table-hover align-middle w-100">
        <thead>
          <tr>
            <th class="text-center">Pilih</th>
            <th>Tgl Setor</th>
            <th>Kelompok</th>
            <th>AO</th>
            <th>CIF</th>
            <th>Nama Anggota</th>
            <th class="text-end">Ke</th>
            <th class="text-end">Nisbah</th>
            <th class="text-end">Omzet</th>
            <th class="text-end">Porsi Tercatat</th>
            <th class="text-end">Porsi Seharusnya</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($tampil as $b): ?>
          <tr>
            <td class="text-center"><?php if ($b['perlu_diubah']): ?><input type="checkbox" class="form-check-input pilih-cif" value="<?php echo h($b['cif']); ?>" aria-label="Pilih CIF <?php echo h($b['cif']); ?>"><?php endif; ?></td>
            <td><?php echo h($b['tgl']); ?></td>
            <td><?php echo h($b['nama_kel']); ?></td>
            <td><?php echo h($b['nama_ao']); ?></td>
            <td><?php echo h($b['cif']); ?></td>
            <td><?php echo h($b['nama']); ?></td>
            <td class="text-end"><?php echo h($b['cicilan']); ?></td>
            <td class="text-end"><?php echo $angka($b['nisbah']); ?></td>
            <td class="text-end"><?php echo $angka($b['omzet']); ?></td>
            <td class="text-end"><?php echo $angka($b['nisbah_anggota']); ?></td>
            <td class="text-end"><?php echo $angka($b['seharusnya']); ?></td>
            <td><span class="badge <?php echo $label[$b['status']][1]; ?>"><?php echo h($label[$b['status']][0]); ?></span></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php
halaman_akhir(array(
    'assets/media/js/jquery.js',
    'assets/media/js/jquery.dataTables.js',
    'assets/media/js/dataTables.bootstrap.js',
    'assets/app/omzet_cek.js',
));
