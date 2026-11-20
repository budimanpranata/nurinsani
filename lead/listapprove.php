<?php require_once __DIR__ . '/../koneksi_db.php';
/**
 * Menu Approve > View Data Deviasi (level lead), hasil cari dari viewapprove.php.
 *   tanpa ?a / Cancel : daftar pembiayaan deviasi (calon_loan status_usia YA) area lead per tanggal wakalah atau CIF;
 *                       bila kosong, daftar penarikan deviasi (temp_tarik) per tanggal atau CIF.
 *   ?a=process        : konfirmasi CIF yang dicentang (Lanjut = approve, Reject = reject).
 *   ?a=approve        : status_usia calon_loan jadi DEVIASI, lalu temp_tarik CIF disalin ke simpanan dan dihapus.
 *   ?a=reject         : hapus calon_loan CIF, lalu hapus temp_tarik dengan norek = CIF.
 * Logika sama dengan versi lama; hanya tampilan dan query (prepared statement) yang berubah. Perilaku lama yang
 * dipertahankan (PEKERJAAN_TERTUNDA.md bagian 37): cabang penarikan selalu dijalankan saat approve, reject temp_tarik
 * memakai norek = CIF. Perubahan atas perintah user 2026-11-02: update tabel_transaksi keterangan_posting=<CIF>
 * dihapus (selalu 0 baris; memicu error MySQL 8 untuk CIF pembiayaan).
 * Perbaikan 2026-11-20 (perintah user, §37): jenis data yang tampil di daftar (pembiayaan / penarikan) dibawa sampai
 * approve/reject (hidden input "jenis"); approve/reject hanya memproses jenis itu:
 *   pembiayaan : approve = status_usia YA -> DEVIASI; reject = hapus calon_loan CIF yang status_usia YA
 *   penarikan  : approve = temp_tarik -> simpanan lalu hapus temp_tarik; reject = hapus temp_tarik WHERE cif
 * Dulu approve selalu menjalankan keduanya, dan reject menghapus calon_loan CIF apa pun statusnya (pengajuan
 * pembiayaan anggota ikut hilang saat penarikannya ditolak) sementara temp_tarik tidak terhapus (norek = CIF).
 * CIF harus dari unit di area lead; proses dalam satu transaksi (temp_tarik MyISAM tidak ikut rollback).
 * Jurnal penarikan yang di-reject tetap tidak dibalik (sama dengan lama, keputusan: jurnal baru tidak ditambahkan).
 */
require_once __DIR__ . '/../inc/tampilan.php';
session_start();
ob_start();
$iduser = isset($_SESSION['userid']) ? $_SESSION['userid'] : null;
if (!isset($_SESSION['userid'])) {
    header("location:../index.html");
    exit;
}
//cek level user
if ($_SESSION['level'] != "lead") {
    header("location:../index.html");
    exit;
}
include "../indo_tgl.php";

$c_area = db_satu("select kode_branch FROM user where userid =?", 's', (string) $iduser);
$code_area = $c_area ? (string) $c_area['kode_branch'] : '';

$tgl_trn = isset($_POST['tgl_trn']) ? (string) $_POST['tgl_trn'] : '';
$nopen = isset($_POST['nopen']) ? (string) $_POST['nopen'] : '';
$a = isset($_GET['a']) ? $_GET['a'] : '';
$idlist = isset($_POST['approve']) && is_array($_POST['approve']) ? $_POST['approve'] : array();
$cancel = false;
// 2026-11-20: jenis data yang tampil di daftar ('pembiayaan' / 'penarikan'); kosong = tentukan seperti halaman konfirmasi
$jenis = isset($_POST['jenis']) && in_array($_POST['jenis'], array('pembiayaan', 'penarikan'), true) ? $_POST['jenis'] : '';

/** Jalankan query tulis; bila gagal halaman berhenti (versi lama: or die(mysql_error())). Transaksi berjalan dibatalkan. */
function jalankan_deviasi($sql, $tipe, ...$param)
{
    if (!db_jalankan($sql, $tipe, ...$param)) {
        @mysqli_rollback(db_ni());
        echo '<div class="alert alert-danger">Gagal memproses data. Proses dihentikan, data tidak disimpan.</div>';
        halaman_akhir();
        exit;
    }
}

/** Jenis data CIF di area lead: 'pembiayaan' (calon_loan status_usia YA), 'penarikan' (temp_tarik), atau '' (tidak ada). */
function jenis_deviasi($cif, $code_area, $jenis)
{
    $pemb = db_satu("SELECT 1 AS ada FROM calon_loan INNER JOIN branch ON branch.kode_branch = calon_loan.unit
        WHERE calon_loan.cif = ? AND status_usia = 'YA' AND branch.code_area = ? LIMIT 1", 'ss', $cif, $code_area);
    $tarik = db_satu("SELECT 1 AS ada FROM temp_tarik INNER JOIN branch ON branch.kode_branch = temp_tarik.unit
        WHERE temp_tarik.cif = ? AND branch.code_area = ? LIMIT 1", 'ss', $cif, $code_area);
    if ($jenis === 'pembiayaan') return $pemb ? 'pembiayaan' : '';
    if ($jenis === 'penarikan') return $tarik ? 'penarikan' : '';
    return $pemb ? 'pembiayaan' : ($tarik ? 'penarikan' : '');
}

/** Satu baris tabel data deviasi (sel pertama berbeda antara daftar dan konfirmasi). */
function baris_deviasi($row, $sel_pertama)
{
    ?>
      <tr>
        <td class="text-center"><?php echo $sel_pertama; ?></td>
        <td class="text-center"><?php echo h($row['unit']); ?></td>
        <td class="text-center"><?php echo h($row['cif']); ?></td>
        <td><?php echo h($row['nama']); ?></td>
        <td class="text-center"><?php echo h(tgl_indo($row['buss_date'])); ?></td>
        <td class="text-center"><?php echo h($row['no_anggota']); ?></td>
        <td class="text-end"><?php echo number_format(compat_num($row['plafond'])); ?></td>
        <td class="text-center"><?php echo h(isset($row['Usia']) ? $row['Usia'] : ''); ?></td>
      </tr>
    <?php
}

halaman_awal('Approve Dokumen');

if ($a == 'process' && $idlist) {
    $action = '';
    if (!empty($_POST['approve_sub'])) {
        $action = 'approve';
    } elseif (!empty($_POST['reject'])) {
        $action = 'reject';
    }
    ?>
<form action="listapprove.php?a=<?php echo h($action); ?>" method="post">
  <input type="hidden" name="jenis" value="<?php echo h($jenis); ?>">
  <div class="card card-ni mw-100">
    <div class="table-responsive">
      <table class="table table-sm table-bordered table-ni align-middle mb-0">
        <thead>
          <tr>
            <th>No Transaksi</th><th>Unit</th><th>Cif</th><th>Nama</th><th>Tanggal Transaksi</th>
            <th>No Anggota</th><th class="text-end">Nominal</th><th>Usia</th>
          </tr>
        </thead>
        <tbody>
    <?php
    foreach ($idlist as $value) {
        $value = (string) $value;
        // 2026-11-20: bila jenis dari daftar = penarikan, data pembiayaan CIF yang sama tidak ditampilkan/diproses
        $data = $jenis === 'penarikan' ? array() : db_semua("SELECT calon_loan.unit as unit,cif,buss_date,no_anggota,plafond,Cust_Short_name as nama FROM calon_loan inner join branch on branch.kode_branch = calon_loan.unit WHERE  status_usia ='YA' and branch.code_area =? AND calon_loan.cif=?",
            'ss', $code_area, $value);
        if (count($data) == 0 && $jenis !== 'pembiayaan') {
            $data = db_semua("SELECT temp_tarik.unit as unit ,temp_tarik.cif,temp_tarik.buss_date,temp_tarik.norek as no_anggota, sum(debit) as plafond, type as Usia,CUST_SHORT_NAME as nama FROM temp_tarik inner join anggota on anggota.cif=temp_tarik.cif inner join branch on branch.kode_branch = temp_tarik.unit WHERE branch.code_area =? and temp_tarik.cif =? group by temp_tarik.norek order by temp_tarik.norek",
                'ss', $code_area, $value);
        }
        foreach ($data as $row) {
            baris_deviasi($row, '<input type="hidden" value="' . h($row['cif']) . '" name="approve[]">' . h($row['cif']));
        }
    }
    ?>
        </tbody>
      </table>
    </div>
    <div class="card-body d-flex gap-2 justify-content-center">
      <button type="submit" value="Approve" name="confirm" class="btn btn-ni">Approve</button>
      <button type="submit" value="Cancel" name="cancel" class="btn btn-outline-secondary">Cancel</button>
    </div>
  </div>
</form>
    <?php
} elseif ($a == 'approve' && $idlist) {
    if (!empty($_POST['cancel'])) {
        $cancel = true;
    } elseif (!empty($_POST['confirm'])) {
        // 2026-11-20 (perintah user): dulu kedua cabang (pembiayaan + penarikan) selalu dijalankan untuk setiap CIF
        // (mysql_num_rows() pada hasil UPDATE selalu false). Kini hanya jenis yang tampil, CIF harus di area lead.
        $dilewati = array();
        mysqli_begin_transaction(db_ni());
        foreach ($idlist as $value) {
            $value = (string) $value;
            $jenis_cif = jenis_deviasi($value, $code_area, $jenis);
            if ($jenis_cif === 'pembiayaan') {
                jalankan_deviasi("Update calon_loan SET status_usia ='DEVIASI' WHERE cif=? AND status_usia='YA'", 's', $value);
            } elseif ($jenis_cif === 'penarikan') {
                jalankan_deviasi("insert into simpanan select * from temp_tarik WHERE cif=?", 's', $value);
                jalankan_deviasi("delete from temp_tarik WHERE cif=?", 's', $value);
            } else {
                $dilewati[] = $value;
            }
        }
        mysqli_commit(db_ni());
        echo '<div class="alert alert-success">Aplikasi Sudah di Setujui.</div>';
        if ($dilewati) {
            echo '<div class="alert alert-warning">Tidak diproses (tidak ditemukan di unit area Anda): ' . h(implode(', ', $dilewati)) . '</div>';
        }
    }
} elseif ($a == 'reject' && $idlist) {
    if (!empty($_POST['cancel'])) {
        $cancel = true;
    } elseif (!empty($_POST['confirm'])) {
        // 2026-11-20 (perintah user): dulu calon_loan CIF dihapus apa pun statusnya (pengajuan pembiayaan ikut hilang
        // saat penarikan di-reject) dan temp_tarik dihapus dengan norek = CIF (tidak pernah cocok). Kini per jenis.
        // Jurnal penarikan yang di-reject tetap tidak dibalik (sama dengan versi lama).
        $dilewati = array();
        mysqli_begin_transaction(db_ni());
        foreach ($idlist as $value) {
            $value = (string) $value;
            $jenis_cif = jenis_deviasi($value, $code_area, $jenis);
            if ($jenis_cif === 'pembiayaan') {
                jalankan_deviasi("DELETE FROM calon_loan WHERE cif=? AND status_usia='YA'", 's', $value);
            } elseif ($jenis_cif === 'penarikan') {
                jalankan_deviasi("delete from temp_tarik WHERE cif=?", 's', $value);
            } else {
                $dilewati[] = $value;
            }
        }
        mysqli_commit(db_ni());
        echo '<div class="alert alert-success">Applications successfully rejected.</div>';
        if ($dilewati) {
            echo '<div class="alert alert-warning">Tidak diproses (tidak ditemukan di unit area Anda): ' . h(implode(', ', $dilewati)) . '</div>';
        }
    }
}

if (!$a || $cancel || !$idlist) {
    $result = db_semua("SELECT calon_loan.unit as unit ,cif,buss_date,no_anggota,plafond,Cust_Short_name as nama FROM calon_loan inner join branch on branch.kode_branch = calon_loan.unit WHERE tgl_wakalah=? AND status_usia='YA' and code_area =? OR cif=? AND status_usia='YA' and code_area=?",
        'ssss', $tgl_trn, $code_area, $nopen, $code_area);
    $jenis_daftar = 'pembiayaan';
    if (count($result) == 0) {
        $jenis_daftar = 'penarikan';
        $result = db_semua("SELECT temp_tarik.unit,temp_tarik.cif,temp_tarik.buss_date,temp_tarik.norek as no_anggota, sum(debit) as plafond, type as Usia,CUST_SHORT_NAME as nama FROM temp_tarik left join anggota on anggota.cif=temp_tarik.cif WHERE temp_tarik.buss_date=? and blok=? OR temp_tarik.cif=? and blok=? group by temp_tarik.cif order by temp_tarik.cif",
            'ssss', $tgl_trn, $code_area, $nopen, $code_area);
    }
    ?>
<form action="listapprove.php?a=process" method="post">
  <input type="hidden" name="jenis" value="<?php echo h($jenis_daftar); ?>">
  <div class="card card-ni mw-100">
    <div class="table-responsive">
      <table class="table table-sm table-bordered table-ni align-middle mb-0">
        <thead>
          <tr>
            <th>Approve / Reject</th><th>Kode Cabang</th><th>Cif</th><th>Nama</th><th>Tanggal Transaksi</th>
            <th>Norek</th><th class="text-end">Nominal</th><th>Status</th>
          </tr>
        </thead>
        <tbody>
    <?php foreach ($result as $row) {
        baris_deviasi($row, '<input type="checkbox" class="form-check-input" value="' . h($row['cif']) . '" name="approve[]">');
    } ?>
        </tbody>
      </table>
    </div>
    <div class="card-body d-flex gap-2 justify-content-center">
      <button type="submit" value="Lanjut" name="approve_sub" class="btn btn-ni">Lanjut</button>
      <button type="submit" value="Reject" name="reject" class="btn btn-outline-danger">Reject</button>
    </div>
  </div>
</form>
    <?php
}
halaman_akhir();
