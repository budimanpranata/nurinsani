<?php require_once __DIR__ . '/../koneksi_db.php';
/**
 * Posting Ulang neraca kantor pusat (unit 1010), dibuka dari Detail di posting_neraca.php; rentang tanggal dari sesi
 * tgl_awal_pos / tgl_akhir_pos. Menampilkan ringkasan transaksi per rekening, lalu tombol POSTING JURNAL:
 *   1. mut_debet/mut_kredit tabel_master unit 1010 dinolkan
 *   2. mutasi = jumlah debet/kredit tabel_transaksi unit 1010 dalam rentang (semua, termasuk yang bertanda EOM),
 *      ditambahkan ke tabel_master per rekening_gl
 *   3. sisa_debet/sisa_kredit tiap rekening 1010 dihitung dari awal + mutasi menurut normal (negatif pindah sisi)
 *   4. tabel_transaksi unit 1010 dalam rentang (kecuali EOM) ditandai keterangan_posting='Post'
 *   lalu alert "posting Selesai." dan pindah ke laporan/hitung_shu.php.
 * Dimigrasi 2026-11-03: tampilan Bootstrap 5, prepared statement; perhitungan dan urutan sama dengan versi lama, termasuk:
 *   - tombol POSTING JURNAL selalu tampil: pemeriksaan versi lama memakai kolom salah ketik (keteterangan_posting) sehingga
 *     query selalu gagal dan syaratnya selalu benar
 *   - tanpa transaksi DB (tabel_master MyISAM)
 * Dibuang: teks status dari decode(REQUEST_URI) (tidak pernah berisi).
 */
require_once __DIR__ . '/../inc/tampilan.php';
session_start();
ob_start();
$userid = isset($_SESSION['userid']) ? $_SESSION['userid'] : null;
$tgl_awal = isset($_SESSION['tgl_awal_pos']) ? (string) $_SESSION['tgl_awal_pos'] : '';
$tgl_akhir = isset($_SESSION['tgl_akhir_pos']) ? (string) $_SESSION['tgl_akhir_pos'] : '';
if (!isset($_SESSION['userid'])) {
    header("location:index.html");
    exit;
}
if ($_SESSION['level'] != "kp") {
    header("location:index.html");
    exit;
}

if (isset($_POST['posting'])) {
    db_jalankan("update tabel_master set mut_kredit=0,mut_debet=0 where unit ='1010'");
    $mutasi = db_semua("select kode_rekening,sum(debet) as debet,sum(kredit) as kredit,unit from tabel_transaksi
        where unit ='1010' and tanggal_transaksi between ? and ? Group By kode_rekening", 'ss', $tgl_awal, $tgl_akhir);
    foreach ($mutasi as $m) {
        db_jalankan("update tabel_master set mut_debet=mut_debet+?, mut_kredit=mut_kredit+? where rekening_gl=? and unit ='1010'", 'sss',
            (string) $m['debet'], (string) $m['kredit'], (string) $m['kode_rekening']);
    }
    foreach (db_semua("select normal,kode_rekening,awal_debet,awal_kredit,mut_debet,mut_kredit from tabel_master where unit ='1010'") as $r) {
        $sisa_debet = $sisa_kredit = null;
        if ($r['normal'] == "debet") {
            $hitung = (compat_num($r['awal_debet']) + compat_num($r['mut_debet'])) - compat_num($r['mut_kredit']);
            list($sisa_debet, $sisa_kredit) = $hitung < 0 ? array(0, abs($hitung)) : array($hitung, 0);
        }
        if ($r['normal'] == "kredit") {
            $hitung = (compat_num($r['awal_kredit']) - compat_num($r['mut_debet'])) + compat_num($r['mut_kredit']);
            list($sisa_debet, $sisa_kredit) = $hitung < 0 ? array(abs($hitung), 0) : array(0, $hitung);
        }
        if ($sisa_debet !== null) {
            db_jalankan("update tabel_master set sisa_debet=?, sisa_kredit=? where kode_rekening=? and unit ='1010'", 'sss',
                (string) $sisa_debet, (string) $sisa_kredit, (string) $r['kode_rekening']);
        }
    }
    $selesai = db_jalankan("update tabel_transaksi set keterangan_posting='Post' where unit ='1010' and tanggal_transaksi between ? and ?
        and keterangan_posting not in ('EOM')", 'ss', $tgl_awal, $tgl_akhir);
    if ($selesai) {
        echo '<script>alert("posting Selesai."); window.location="laporan/hitung_shu.php"</script>';
    } else {
        echo h(db_error_terakhir());
    }
    exit;
}

$rekening = db_semua("select tanggal_transaksi,tabel_transaksi.kode_rekening,sum(debet) as debet,sum(kredit) as kredit,nama_rek from tabel_transaksi
    inner join coa on coa.kode_rek = SUBSTR(kode_rekening,6)
    where tabel_transaksi.unit ='1010' and tanggal_transaksi between ? and ? Group By kode_rekening", 'ss', $tgl_awal, $tgl_akhir);
$total = db_satu("select sum(debet) as tot_debet, sum(kredit) as tot_kredit from tabel_transaksi
    where tabel_transaksi.unit ='1010' and tanggal_transaksi between ? and ?", 'ss', $tgl_awal, $tgl_akhir);

halaman_awal('Proses Posting Jurnal');
?>
<p class="mb-2">Periode <strong><?php echo h($tgl_awal); ?></strong> s.d. <strong><?php echo h($tgl_akhir); ?></strong>, unit 1010</p>
<div class="table-responsive">
  <table class="table table-sm table-bordered table-ni align-middle">
    <thead>
      <tr><th>Kode Rekening</th><th>Nama Rekening</th><th class="text-end">Debet</th><th class="text-end">Kredit</th></tr>
    </thead>
    <tbody>
    <?php foreach ($rekening as $row): ?>
      <tr>
        <td class="text-center"><?php echo h($row['kode_rekening']); ?></td>
        <td><?php echo h($row['nama_rek']); ?></td>
        <td class="text-end"><?php echo number_format(compat_num($row['debet']), 2, '.', ','); ?></td>
        <td class="text-end"><?php echo number_format(compat_num($row['kredit']), 2, '.', ','); ?></td>
      </tr>
    <?php endforeach; ?>
      <tr class="fw-bold">
        <td colspan="2" class="text-center">TOTAL TRANSAKSI</td>
        <td class="text-end"><?php echo number_format(compat_num($total ? $total['tot_debet'] : null)); ?></td>
        <td class="text-end"><?php echo number_format(compat_num($total ? $total['tot_kredit'] : null)); ?></td>
      </tr>
    </tbody>
  </table>
</div>
<form action="posting_neraca_bulan.php" method="post" name="postform" class="text-center mt-3">
  <button type="submit" name="posting" value="POSTING JURNAL" class="btn btn-ni px-4" data-konfirmasi="Apakah Anda Yakin?">POSTING JURNAL</button>
</form>
<?php
halaman_akhir();
