<?php require_once __DIR__ . '/koneksi_db.php';
require_once __DIR__ . '/inc/cek_login.php';
wajib_login();
/**
 * Menu Pemeliharaan Data > Pelunasan: cari pembiayaan aktif (OS > 0) berdasarkan NOREK, lalu lunasi (lunasi.php).
 * Perilaku sama dengan versi lama; pengaman login ditambahkan (versi lama tidak memeriksa login).
 */
require_once __DIR__ . '/inc/tampilan.php';
include "indo_tgl.php";

$nopen = isset($_POST['kode_rekening']) ? (string) $_POST['kode_rekening'] : '';
$hasil = array();
$pesan = array(); // script alert/pindah, dicetak seperti versi lama

if (!empty($_POST['search'])) {
    if (empty($nopen)) {
        $pesan[] = "<script>alert('Maaf,.isi Nomor Anggota yang benar  !!!');</script>";
        $pesan[] = "<script language='javascript'>document.location.href='pelunasan.php'</script>";
    } else {
        $kkel = db_satu("select CODE_KEL,CIF,UNIT,CUST_SHORT_NAME,CAO FROM anggota where  norek =?", 's', $nopen);
        $ciff = $kkel ? $kkel['CIF'] : null;
        $hasil = db_semua("select loan.no_anggota,loan.Cust_Short_name,loan.Plafond,os,maturity_date,branch.unit,angsuran,os,(sum(KREDIT)-sum(DEBIT))as saldo
            from loan
            left join simpanan on simpanan.cif=loan.cif
            left join branch on branch.kode_branch=loan.unit
            where loan.cif =? and os > 0 and simpanan.TYPE not in ('04','05') group by no_anggota", 's', (string) $ciff);
    }
    // versi lama: juga muncul saat NOREK kosong (alert kedua)
    if (!$hasil) {
        $pesan[] = "<script>alert('Maaf...,data tdak di temukan!!!');</script>";
        $pesan[] = "<script language='javascript'>document.location.href='pelunasan.php'</script>";
    }
}

halaman_awal('Pelunasan');
?>
<div class="card card-ni">
  <div class="card-header">Kriteria Pencarian</div>
  <div class="card-body">
    <form method="post" action="pelunasan.php" class="form-ni" autocomplete="off">
      <div class="row g-2 mb-3">
        <label for="kode" class="col-sm-3 col-form-label">NOREK</label>
        <div class="col-sm-4 autocomplete">
          <input type="text" name="kode_rekening" id="kode" class="form-control form-control-sm" value="<?php echo h($nopen); ?>"
                 placeholder="CIF / nama"
                 data-autocomplete="ajax/cari_rekening.php" data-isi-nilai="norek" data-isi-label="norek,nama,unit,nama_kel"
                 data-isi-target='{"#country":"nama"}'>
        </div>
        <div class="col-sm-5"><input type="text" id="country" class="form-control form-control-sm" disabled></div>
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
        <th>No Anggota</th>
        <th>Nama</th>
        <th class="text-end">Plafond</th>
        <th class="text-end">Angsuran</th>
        <th>Tgl Jatuh Tempo</th>
        <th class="text-end">Saldo OS</th>
        <th class="text-end">Saldo Rek</th>
        <th>Unit</th>
        <th class="text-center">Action</th>
      </tr>
    </thead>
    <tbody>
    <?php foreach ($hasil as $data): ?>
      <tr>
        <td><?php echo h($data['no_anggota']); ?></td>
        <td><?php echo h($data['Cust_Short_name']); ?></td>
        <td class="text-end"><?php echo number_format(compat_num($data['Plafond'])); ?></td>
        <td class="text-end"><?php echo number_format(compat_num($data['angsuran'])); ?></td>
        <td><?php echo h(tgl_indo($data['maturity_date'])); ?></td>
        <td class="text-end"><?php echo number_format(compat_num($data['os'])); ?></td>
        <td class="text-end"><?php echo number_format(compat_num($data['saldo'])); ?></td>
        <td><?php echo h($data['unit']); ?></td>
        <td class="text-center"><a href="lunasi.php?anggota=<?php echo urlencode($data['no_anggota']); ?>" class="btn btn-outline-danger btn-sm" data-konfirmasi="Apakah Anda yakin akan melakukan Pelunasan?">Disburse</a></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php
echo implode("\n", $pesan);
halaman_akhir();
