<?php require_once __DIR__ . '/../koneksi_db.php';
/**
 * Menu Report > PPAP (kolektabilitas pembiayaan).
 *   Cari     : hitung ulang kolektabilitas (loan.gol) seluruh pembiayaan unit berdasarkan tunggakan dan jatuh tempo
 *              terhadap tanggal pencarian, lalu tampilkan ringkasan golongan yang dipilih.
 *   Rekap    : PDF rekap kolektabilitas (rekap_ppap.php, tidak diubah).
 *   Download : export .xlsx bertahap 4 sheet (kolek1..kolek4) dengan indikator progres
 *              (report/ppap_export.php, assets/app/export_xlsx.js), pengganti export_npl.php (.xls).
 * Rumus kolektabilitas, isian sesi, dan ringkasan sama dengan versi lama; hanya tampilan, query (prepared statement),
 * dan cara export yang berubah.
 * Perubahan 2026-10-31 (keputusan user, lihat PEKERJAAN_TERTUNDA.md bagian 27):
 *   - tanggal perhitungan dikunci ke tanggal sistem (versi lama: tanggal bebas diketik user), agar gol hari berjalan
 *     berkesinambungan dengan proses EOD esok hari
 *   - keenam UPDATE gol dijalankan dalam satu transaksi DB (berhasil semua atau dibatalkan semua)
 */
require_once __DIR__ . '/../inc/tampilan.php';
session_start();
$userid = isset($_SESSION['userid']) ? $_SESSION['userid'] : null;
//cek apakah user sudah login
if (!isset($_SESSION['userid'])) {
    header("location:index.html");
    exit;
}
// tanggal perhitungan = tanggal sistem
if (empty($_SESSION['stgl_system'])) {
    echo "<script>alert('Maaf,.tanggal system belum di setup');</script>";
    header("location:../param_tgl.php");
    exit;
}

$tgl = (string) $_SESSION['stgl_system'];
$select = isset($_POST['select']) ? (string) $_POST['select'] : '';
$unit = null;
foreach (db_semua("select kode_branch from user where userid=?", 's', (string) $userid) as $id_user) {
    $unit = $id_user['kode_branch'];
}

/**
 * Hitung ulang loan.gol unit (urutan dan rumus sama dengan versi lama) dalam satu transaksi DB.
 * Hasil true bila keenam UPDATE berhasil (commit); false bila ada yang gagal (rollback, gol tidak berubah).
 */
function hitung_kolektabilitas($unit, $tgl)
{
    $db = db_ni();
    $gagal_awal = db_jumlah_gagal();
    $db->begin_transaction();
    try {
        hitung_kolektabilitas_isi($unit, $tgl);
    } catch (Throwable $e) {
        $GLOBALS['__db_jumlah_gagal'] = db_jumlah_gagal() + 1;
    }
    if (db_jumlah_gagal() > $gagal_awal) {
        $db->rollback();
        return false;
    }
    return $db->commit();
}

/** Enam UPDATE kolektabilitas (dipanggil di dalam transaksi). */
function hitung_kolektabilitas_isi($unit, $tgl)
{
    $unit = (string) $unit;
    // golongan dari jumlah angsuran tertunggak, per jenis produk (deal_produk)
    $per_produk = array(
        '2' => 'ROUND(sum(KREDIT-DEBIT)/(angsuran+2500))',
        '1' => 'sum(KREDIT-DEBIT)/(angsuran)',
        '3' => 'sum(KREDIT-DEBIT)/(pokok+ijaroh)',
        '4' => 'ROUND(sum(KREDIT-DEBIT)/(angsuran+2500))',
    );
    foreach ($per_produk as $deal => $ft) {
        db_jalankan("update loan
            set gol=(SELECT
            CASE
            WHEN $ft between 1 and 3 then '1'
            WHEN $ft between 4 and 6 then '2'
            WHEN $ft between 7 and 12 then '3'
            WHEN $ft > 12 then '4'
            else '1' end
            FROM
            tunggakan
            where loan.cif=tunggakan.CIF
            ) where loan.unit =? and deal_produk=? and os > 0", 'ss', $unit, (string) $deal);
    }
    // sudah lewat jatuh tempo: minimal golongan 2/3 menurut lama keterlambatan
    db_jalankan("update loan
        set gol =
        CASE
            WHEN datediff(?,maturity_date) between 1 and 30 then '2'
            WHEN datediff(?,maturity_date) between 31 and 60 then '3'
            WHEN datediff(?,maturity_date) > 60 then '4'
            else '2'
        END
            where
            unit =?
            and maturity_date <= ?
            and os > 0 AND gol in ('1','2')", 'sssss', $tgl, $tgl, $tgl, $unit, $tgl);
    db_jalankan("update loan
        set gol =
        CASE
            WHEN datediff(?,maturity_date) between 1 and 59 then '3'
            WHEN datediff(?,maturity_date) between 31 and 60 then '3'
            WHEN datediff(?,maturity_date) > 60 then '4'
            else '3'
        END
            where
            unit =?
            and maturity_date <= ?
            and os > 0 AND gol in ('3')", 'sssss', $tgl, $tgl, $tgl, $unit, $tgl);
}

$pesan = array();
$cari = isset($_POST['search']);
$noa = $saldo = $pla = null;
if ($cari) {
    if (empty($tgl) || empty($select)) {
        $pesan[] = "<script>alert('Maaf,.isi Tanggal  !!!');</script>";
        $pesan[] = "<script language='javascript'>document.location.href='ppap.php'</script>";
    } elseif (!hitung_kolektabilitas($unit, $tgl)) {
        $pesan[] = "<script>alert('Perhitungan kolektabilitas gagal dan dibatalkan; data golongan tidak berubah. Silakan ulangi.');</script>";
    } else {
        $_SESSION['stgl_npl'] = $tgl;
        $_SESSION['jenis_npl'] = $select;
        if (in_array($select, array('01', '02', '03', '04'), true)) {
            $data_noa = db_satu("SELECT count(cif) as noa, sum(Plafond) as pla,sum(os-saldo_margin) as saldo from loan where loan.unit =? and OS >0 and gol=?",
                'ss', (string) $unit, (string) (int) $select);
            $noa = $data_noa ? $data_noa['noa'] : null;
            $saldo = $data_noa ? $data_noa['saldo'] : null;
            $pla = $data_noa ? $data_noa['pla'] : null;
        }
    }
}

halaman_awal('Report PPAP');
?>
<div class="card card-ni">
  <div class="card-header">Kriteria Pencarian</div>
  <div class="card-body">
    <form method="post" action="ppap.php" name="postform" class="form-ni" autocomplete="off">
      <div class="row g-2 mb-2">
        <label for="tgl" class="col-sm-4 col-form-label">Tanggal Perhitungan</label>
        <div class="col-sm-8">
          <input type="date" id="tgl" value="<?php echo h($tgl); ?>" class="form-control form-control-sm" readonly aria-describedby="ket-tgl">
          <div id="ket-tgl" class="form-text">Tanggal sistem. Kolektabilitas dihitung ulang per tanggal ini dan dilanjutkan oleh proses EOD esok hari.</div>
        </div>
      </div>
      <div class="row g-2 mb-3">
        <label for="select" class="col-sm-4 col-form-label">Jenis Kolektabilitas</label>
        <div class="col-sm-8">
          <select name="select" id="select" class="form-select form-select-sm">
            <option>Pilih Salah satu</option>
            <?php foreach (array('01' => 'Lancar', '02' => 'Kurang Lancar', '03' => 'Diragukan', '04' => 'Macet') as $k => $v): ?>
            <option value="<?php echo $k; ?>"<?php echo $select === $k ? ' selected' : ''; ?>><?php echo $v; ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
      <div class="text-end">
        <button name="search" type="submit" id="submit2" value="Cari" class="btn btn-ni btn-sm px-4">Cari</button>
      </div>
    </form>
  </div>
</div>

<?php echo implode("\n", $pesan); ?>

<div class="table-responsive mt-3">
  <table class="table table-sm table-bordered table-ni align-middle">
    <thead>
      <tr>
        <th>Unit</th>
        <th class="text-end">NoA</th>
        <th class="text-end">Plafond</th>
        <th class="text-end">Saldo Pinjaman</th>
        <th class="text-center">Rekap</th>
        <th class="text-center">Excel</th>
      </tr>
    </thead>
    <tbody>
    <?php if ($cari): ?>
      <tr>
        <td><?php echo h($unit); ?></td>
        <td class="text-end"><?php echo h($noa); ?></td>
        <td class="text-end"><?php echo number_format(compat_num($pla)); ?></td>
        <td class="text-end"><?php echo number_format(compat_num($saldo)); ?></td>
        <td class="text-center"><a href="rekap_ppap.php?nopen=<?php echo urlencode((string) $unit); ?>" class="btn btn-outline-secondary btn-sm" data-konfirmasi="Apakah Anda yakin ?">Rekap</a></td>
        <td class="text-center">
          <button type="button" class="btn btn-outline-success btn-sm" data-export-xlsx data-export-url="ppap_export.php" data-export-satuan="baris">Download (.xlsx)</button>
        </td>
      </tr>
    <?php endif; ?>
    </tbody>
  </table>
</div>

<div class="modal fade" id="modal-export" tabindex="-1" aria-labelledby="judul-export" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h2 class="modal-title fs-6" id="judul-export">Export Kolektabilitas Pembiayaan</h2>
      </div>
      <div class="modal-body">
        <div class="progress" role="progressbar" aria-label="Progres export" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0" style="height: 1.25rem;">
          <div class="progress-bar progress-bar-striped progress-bar-animated" style="width: 0%">0%</div>
        </div>
        <div class="small text-muted mt-2" data-export-teks aria-live="polite">Menyiapkan data…</div>
        <div data-export-ringkasan></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary btn-sm d-none" data-bs-dismiss="modal" data-export-tutup>Tutup</button>
      </div>
    </div>
  </div>
</div>
<?php
halaman_akhir(array('assets/app/export_xlsx.js'));
