-- Jumlah hari bagi hasil deposito (perintah user 2026-11-04).
-- Satu baris (id = 1): diisi otomatis oleh Posting Parameter (fund/transaksi/posting.php) dari DATEDIFF(sd, dari)
-- periode Param Financial terbaru, dan bisa diubah lewat menu Seting Parameter > Jumlah Hari Bagi Hasil
-- (fund/view/view_hari_dep.php). Dipakai Posting Parameter (eq) dan Realisasi Bagi Hasil (inc/bagi_hasil_deposito.php).
-- Selama tabel kosong, Realisasi memakai 30 hari dan menampilkan peringatan.
CREATE TABLE IF NOT EXISTS hari_dep (
  id TINYINT UNSIGNED NOT NULL DEFAULT 1,
  jumlah_hari INT NOT NULL,
  sumber VARCHAR(20) NOT NULL DEFAULT '',
  tgl_update DATETIME NULL,
  userid VARCHAR(20) NOT NULL DEFAULT '',
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
