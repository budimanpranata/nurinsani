<?php require_once __DIR__ . '/../../koneksi_db.php';
/**
 * Menu Transaksi > Posting jurnal (level kp).
 *   Daftar  : semua transaksi unit user yang belum diposting (kecuali id_admin 'userf'), per rekening, nama dari COA
 *             (kode tanpa awalan unit: SUBSTR(kode_rekening,6)).
 *   Posting : per transaksi tambah mutasi ke tabel_master (rekening_gl) lalu hitung ulang sisa rekening itu; sesudah
 *             itu semua transaksi ditandai 'Post'.
 * Rumus mutasi/sisa sama dengan versi lama; tampilan dan query (prepared statement) diperbarui. Versi lama tidak punya
 * cek login; sekarang wajib login.
 * Perbaikan 2026-11-03 (perintah user, PEKERJAAN_TERTUNDA.md bagian 48, sama dengan Posting unit):
 *   - posting ditolak seluruhnya bila ada rekening yang belum ada di COA atau di tabel_master (rekening_gl), dengan daftarnya
 *   - transaksi ditandai Post satu per satu; mutasi hanya ditambahkan untuk yang berhasil ditandai di proses itu
 *     (klik ganda / proses bersamaan tidak menggandakan mutasi); tombol POSTING dikunci sesudah diklik
 *   - daftar menampilkan semua rekening (dulu hanya yang ada di COA), sehingga cocok dengan TOTAL
 */
require_once __DIR__ . '/../../inc/tampilan.php';
session_start();
require_once __DIR__ . '/../../inc/cek_login.php';
wajib_login();
$userid = $_SESSION['userid'];
include "../encryption/function.php";
include "../../indo_tgl.php";
//untuk waktu (tanggal_posting berupa teks; format yy-mm-dd seperti versi lama)
$tanggal = date("y-m-d");

$unitt = null;
foreach (db_semua("select kode_branch from user where userid=?", 's', (string) $userid) as $id_user) {
    $unitt = $id_user['kode_branch'];
}
$unitt = (string) $unitt;

// daftar dan total dibaca sebelum proses posting (versi lama menampilkan daftar dulu, baru memproses)
// left join: rekening yang belum ada di COA tetap tampil (nama kosong) supaya daftar cocok dengan TOTAL
$baris_transaksi = db_semua("select tanggal_transaksi,tabel_transaksi.kode_rekening,sum(debet) as debet,sum(kredit) as kredit,nama_rek,keterangan_transaksi from tabel_transaksi left join coa on coa.kode_rek = SUBSTR(kode_rekening,6)  where  keterangan_posting='' and tabel_transaksi.unit =? and id_admin not in('userf')  Group By kode_rekening",
    's', $unitt);
$total = db_satu("select sum(debet) as tot_debet, sum(kredit) as tot_kredit from tabel_transaksi where  keterangan_posting='' and unit =? and id_admin not in('userf') order by kode_rekening  asc", 's', $unitt);
$cek = db_satu("select count(*) as n from tabel_transaksi where keterangan_posting='' and unit =? and id_admin not in ('userf')", 's', $unitt);
$cek_posting = $cek ? (int) $cek['n'] : 0;

//untuk mendecode url yang di enrypsi (pesan status sesudah posting)
$var = strpos($_SERVER['REQUEST_URI'], '?') !== false ? decode($_SERVER['REQUEST_URI']) : array();
$status = isset($var['status']) ? $var['status'] : null;

ob_start();
if (isset($_POST['posting'])) {
    $antrian = db_semua("select id_transaksi,kode_rekening,debet,kredit,unit from tabel_transaksi where keterangan_posting='' and unit =? and id_admin not in('userf') order by id_transaksi", 's', $unitt);

    // cek rekening dulu: kode yang tidak ada di COA atau di tabel_master membuat posting ditolak seluruhnya
    // (versi lama tetap menandai Post, sehingga mutasinya hilang)
    $kurang = array();
    $sudah_dicek = array();
    foreach ($antrian as $a) {
        $kode = (string) $a['kode_rekening'];
        if (isset($sudah_dicek[$kode])) {
            continue;
        }
        $sudah_dicek[$kode] = true;
        if (!db_satu("select kode_rek from coa where kode_rek=SUBSTR(?,6)", 's', $kode)) {
            $kurang[] = "Rekening " . $kode . " belum ada di COA";
        }
        if (!db_satu("select rekening_gl from tabel_master where rekening_gl=?", 's', $kode)) {
            $kurang[] = "Rekening " . $kode . " belum ada di tabel_master";
        }
    }
    if ($kurang) {
        echo "<script>alert(" . json_encode("Posting dibatalkan, tidak ada transaksi yang diposting.\n\n" . implode("\n", $kurang)
            . "\n\nLengkapi rekening tersebut lalu ulangi posting.") . ");</script>";
        $antrian = array();
    }

    ///////////////////////// HITUNG MUTASI /////////////////////
    // tiap transaksi ditandai Post dulu; mutasi hanya ditambahkan bila penandaan itu berhasil di proses ini
    $tandai = db_ni()->prepare("update tabel_transaksi set tanggal_posting=?, keterangan_posting='Post' where id_transaksi=? and keterangan_posting=''");
    foreach ($antrian as $row_hit_mut) {
        $id = (string) $row_hit_mut['id_transaksi'];
        $tgl_post = (string) $tanggal;
        $tandai->bind_param('ss', $tgl_post, $id);
        if (!$tandai->execute() || $tandai->affected_rows !== 1) {
            continue;
        }
        $kode_rekening = (string) $row_hit_mut['kode_rekening'];
        db_jalankan("update tabel_master set mut_debet=mut_debet+?, mut_kredit=mut_kredit+? where rekening_gl=?", 'sss',
            (string) $row_hit_mut['debet'], (string) $row_hit_mut['kredit'], $kode_rekening);
        // sisa rekening dihitung ulang sesudah tiap transaksi (versi lama)
        $row_hit_sisa = db_satu("select * from tabel_master where rekening_gl =?", 's', $kode_rekening);
        $normal = $row_hit_sisa ? $row_hit_sisa['normal'] : null;
        $awal_debet = compat_num($row_hit_sisa ? $row_hit_sisa['awal_debet'] : null);
        $awal_kredit = compat_num($row_hit_sisa ? $row_hit_sisa['awal_kredit'] : null);
        $mutasi_debet = compat_num($row_hit_sisa ? $row_hit_sisa['mut_debet'] : null);
        $mutasi_kredit = compat_num($row_hit_sisa ? $row_hit_sisa['mut_kredit'] : null);
        if ($normal == "debet") {
            $hitung_sisa_debet = ($awal_debet + $mutasi_debet) - $mutasi_kredit;
            if ($hitung_sisa_debet < 0) {
                db_jalankan("update tabel_master set sisa_debet=0, sisa_kredit=? where rekening_gl=?", 'ss', (string) abs($hitung_sisa_debet), $kode_rekening);
            } else {
                db_jalankan("update tabel_master set sisa_debet=?, sisa_kredit='0' where rekening_gl=?", 'ss', (string) $hitung_sisa_debet, $kode_rekening);
            }
        }
        if ($normal == "kredit") {
            $hitung_sisa_kredit = ($awal_kredit - $mutasi_debet) + $mutasi_kredit;
            if ($hitung_sisa_kredit < 0) {
                db_jalankan("update tabel_master set sisa_debet=?, sisa_kredit='0' where rekening_gl=?", 'ss', (string) abs($hitung_sisa_kredit), $kode_rekening);
            } else {
                db_jalankan("update tabel_master set sisa_debet=0, sisa_kredit=? where rekening_gl=?", 'ss', (string) $hitung_sisa_kredit, $kode_rekening);
            }
        }
    }

    ////////////////////////// STATUS POSTING //////////////////////////////
    // (status Post sudah diubah per transaksi di atas)
    if (!$kurang) {
        ?><script language="javascript">document.location.href="?<?php echo paramEncrypt('page=./transaksi/posting&status=Proses Posting Selesai')?>"</script><?php
    }
}
$pesan_proses = ob_get_clean();

halaman_awal('Proses Posting Jurnal');
?>
<div class="table-responsive">
  <table class="table table-sm table-bordered table-ni align-middle">
    <thead>
      <tr><th>Tanggal</th><th class="text-center">Kode Rekening</th><th>Nama Rekening</th><th>Keterangan</th><th class="text-end">Debet</th><th class="text-end">Kredit</th></tr>
    </thead>
    <tbody>
    <?php foreach ($baris_transaksi as $row_tran): ?>
      <tr>
        <td><?php echo h(tgl_indo($row_tran['tanggal_transaksi'])); ?></td>
        <td class="text-center"><?php echo h($row_tran['kode_rekening']); ?></td>
        <td><?php echo h($row_tran['nama_rek']); ?></td>
        <td><?php echo h($row_tran['keterangan_transaksi']); ?></td>
        <td class="text-end"><?php echo number_format(compat_num($row_tran['debet']), 2, '.', ','); ?></td>
        <td class="text-end"><?php echo number_format(compat_num($row_tran['kredit']), 2, '.', ','); ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
    <tfoot>
      <tr class="fw-bold">
        <td colspan="4" class="text-center">TOTAL TRANSAKSI</td>
        <td class="text-end"><?php echo number_format(compat_num($total ? $total['tot_debet'] : null)); ?></td>
        <td class="text-end"><?php echo number_format(compat_num($total ? $total['tot_kredit'] : null)); ?></td>
      </tr>
    </tfoot>
  </table>
</div>

<div class="text-center">
  <?php //untuk mengakhiri posting dan memberi tanda posting ?>
  <?php if ($cek_posting !== 0): ?>
  <form action="?<?php echo h(paramEncrypt('page=../transaksi/posting')); ?>" method="post" name="postform" class="mb-2" id="form-posting">
    <button type="submit" name="posting" value="POSTING JURNAL" class="btn btn-ni px-4" data-konfirmasi="Apakah Anda Yakin?">POSTING JURNAL</button>
  </form>
  <?php endif; ?>
  <?php if ($status !== null && $status !== ''): ?>
  <div class="text-muted"><?php echo h($status); ?></div>
  <?php endif; ?>
</div>
<?php echo $pesan_proses; ?>
<script>
// cegah klik ganda: form hanya terkirim sekali, tombol dikunci sesudah terkirim
(function () {
    var form = document.getElementById('form-posting');
    if (!form) { return; }
    var terkirim = false;
    form.addEventListener('submit', function (e) {
        if (terkirim) { e.preventDefault(); return; }
        terkirim = true;
        var tombol = form.querySelector('button[name="posting"]');
        // dikunci sesudah submit berjalan agar nilai tombol tetap ikut terkirim
        setTimeout(function () { tombol.disabled = true; tombol.textContent = 'Memproses…'; }, 0);
    });
})();
</script>
<?php
halaman_akhir();
