<?php require_once __DIR__ . '/../../koneksi_db.php';
/**
 * Proses tombol Disburse di Monitoring > Daily Deposito level kp (kp/view/view_bayar_dep.php), dipanggil lewat AJAX.
 * ?p=update, POST id (norek), jatuh (tanggal), nomi (jumlah), code (rekening bank/GL), ket. Status dari dep_realisasi:
 *   PENEMPATAN : jurnal_umum (2x) + tabel_transaksi kredit 1010-2851000 / debet rekening; realisasi dipindah ke audit,
 *                deposito status_print OK
 *   BREAK      : tabel_transaksi debet 1010-2852000 / kredit rekening; nisbah deposito_detail dibayar; realisasi dipindah
 *                ke audit; deposito DIHAPUS
 *   lainnya    : bagi hasil. ARO-ARO -> escrow_deposito KREDIT dan nominal deposito bertambah; selain itu tabel_transaksi
 *                debet 1010-2837000 / kredit rekening. Nisbah deposito_detail dibayar, realisasi ke audit, status_print OK
 * Jawaban teks: "Sukses" atau daftar kesalahan (HTML) yang ditampilkan halaman.
 * Rekening dan urutan sama dengan versi lama; query diubah ke prepared statement. Wajib login (versi lama tanpa cek login).
 * Perbaikan 2026-11-03 (perintah user, PEKERJAAN_TERTUNDA.md bagian 51):
 *   - ditolak bila norek tidak punya data dep_realisasi (dulu tetap "Sukses" dan mencatat jurnal BU/ kosong); proses
 *     dikunci (GET_LOCK) dan realisasi dicek di dalam kunci, sehingga klik ganda tidak mencatat dua kali
 *   - PENEMPATAN dan BREAK mencatat jurnal_umum satu kali (dulu dua kali untuk kode yang sama)
 *   - jumlah harus angka bulat > 0, tanggal harus valid, rekening harus rekening bank unit 1010 (gr_sub 1310000/1330000,
 *     sama dengan daftar saran)
 * Perbaikan 2026-11-21 (perintah user, §51 D/E):
 *   - yang diproses baris antrean yang diklik (POST id_realisasi); dulu status dari baris terakhir norek dan semua baris
 *     norek dipindah ke audit. BREAK ditolak bila norek masih punya antrean lain (bagi hasil diproses dulu).
 *   - satu transaksi: gagal -> semua dibatalkan, antrean tetap di daftar, pesan GAGAL (dulu selalu "Sukses")
 */
require_once __DIR__ . '/../../inc/tampilan.php';
session_start();
require_once __DIR__ . '/../../inc/cek_login.php';
wajib_level('kp');
ob_start();
require_once __DIR__ . '/../../inc/cek_login.php';
wajib_login('json');
$userid = (string) $_SESSION['userid'];
include "../../function.php";

$unit = '';
foreach (db_semua("select kode_branch from user WHERE userid = ?", 's', $userid) as $r) {
    $unit = (string) $r['kode_branch'];
}
$p = isset($_GET['p']) ? (string) $_GET['p'] : '';
$pos = function ($k) {
    return isset($_POST[$k]) ? (string) $_POST[$k] : '';
};

switch ($p) {
    default:
        echo "Halaman tidak ditemukan";
        break;

    case "tambah":
        break;

    case "update":
        $norek = $pos('id');
        $buss_date = $pos('jatuh');
        $nomi = $pos('nomi');
        $rek = $pos('code');
        $ket = $pos('ket');
        $tgl = $pos('jatuh');
        // versi lama: unit jurnal selalu 1010
        $unit = '1010';

        $err = array();
        if (empty($tgl)) {
            $err[] = "<h5>* TANGGAL Masih Kosong</h5>";
        } else {
            $d = DateTime::createFromFormat('!Y-m-d', substr($tgl, 0, 10));
            if (!$d || $d->format('Y-m-d') !== substr($tgl, 0, 10)) { $err[] = "<h5>* TANGGAL tidak valid</h5>"; }
        }
        if (empty($rek)) {
            $err[] = "<h5>* REKENING GL Masih Kosong</h5>";
        } elseif (!db_satu("SELECT 1 as ada FROM tabel_master WHERE rekening_gl=? and unit ='1010' and gr_sub in ('1310000','1330000')", 's', $rek)) {
            $err[] = "<h5>* REKENING " . h($rek) . " tidak terdaftar sebagai rekening bank unit 1010</h5>";
        }
        if (empty($nomi)) {
            $err[] = "<h5>* NOMINAL Masih Kosong</h5>";
        } elseif (!preg_match('/^[0-9]+$/', $nomi) || (int) $nomi <= 0) {
            $err[] = "<h5>* NOMINAL harus angka lebih dari 0</h5>";
        }

        if ($err) {
            foreach ($err as $val) {
                echo $val;
            }
            break;
        }

        $kunci = db_satu("SELECT GET_LOCK('nurinsani_kp_disburse_dep', 15) as ok");
        if (!$kunci || (int) $kunci['ok'] !== 1) {
            echo "<h5>* Sistem sedang memproses transaksi lain, coba lagi</h5>";
            break;
        }
        $lepas = function () {
            db_satu("SELECT RELEASE_LOCK('nurinsani_kp_disburse_dep') as ok");
        };
        // 2026-11-21 (perintah user, §51 E): yang diproses baris yang DIKLIK (id_realisasi dari tombol baris). Dulu status
        // dan id diambil dari baris TERAKHIR norek itu dan SEMUA baris norek dipindah ke audit, sehingga dua antrean
        // satu norek (mis. BAGI HASIL + BREAK) diproses dengan status salah dan antrean lain hilang tanpa jurnal.
        $antrean = db_semua("select * from dep_realisasi WHERE norek = ?", 's', $norek);
        $id_klik = $pos('id_realisasi');
        if ($id_klik === '' && count($antrean) === 1) {
            // halaman lama (sebelum ada id_realisasi) dengan satu antrean: tidak ambigu
            $id_klik = (string) $antrean[0]['id_realisasi'];
        }
        $baris = null;
        foreach ($antrean as $a) {
            if ((string) $a['id_realisasi'] === $id_klik) {
                $baris = $a;
            }
        }
        if (!$antrean) {
            $lepas();
            echo "<h5>* Data realisasi rekening " . h($norek) . " tidak ditemukan atau sudah diproses</h5>";
            break;
        }
        if ($baris === null) {
            $lepas();
            echo "<h5>* Data realisasi yang dipilih sudah diproses atau halaman sudah lama. Muat ulang halaman lalu ulangi.</h5>";
            break;
        }
        $posisi = $baris['status'];
        $id_realisasi = (string) $baris['id_realisasi'];
        if ($posisi == 'BREAK' && count($antrean) > 1) {
            // break menghapus deposito: antrean lain norek ini (bagi hasil yang belum dibayar) diproses lebih dulu
            $lepas();
            echo "<h5>* Rekening " . h($norek) . " masih punya antrean lain (" . h(implode(', ', array_map(function ($a) {
                return $a['status'];
            }, array_filter($antrean, function ($a) use ($id_realisasi) {
                return (string) $a['id_realisasi'] !== $id_realisasi;
            })))) . ") yang belum di-Disburse. Proses antrean itu dulu, baru BREAK.</h5>";
            break;
        }

        $kode_transaksi = "BU/" . $id_realisasi;
        $jurnal_umum = function () use ($kode_transaksi, $buss_date, $unit) {
            // nomor_jurnal '' -> nomor otomatis (versi lama)
            db_jalankan("insert into jurnal_umum(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values('',?,?,?)", 'sss', $kode_transaksi, $buss_date, $unit);
        };
        $transaksi = function ($rekening, $kolom, $jenis) use ($unit, $kode_transaksi, $buss_date, $ket, $nomi) {
            db_jalankan("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,$kolom,id_admin)
                values(?,?,?,?,?,?,?,'userf')", 'sssssss', $unit, $kode_transaksi, $rekening, $buss_date, $jenis, $ket, $nomi);
        };
        $bayar_nisbah = function () use ($nomi, $tgl, $norek) {
            db_jalankan("update deposito_detail set jumlah_nisbah =?, ket ='BAYAR', tgl_bayar =? where tgl_jatuh_tempo =? and id_deposito=?", 'ssss', $nomi, $tgl, $tgl, $norek);
        };

        // 2026-11-21 (perintah user, §51 D): dulu setiap langkah tetap jalan walau langkah sebelumnya gagal dan jawabannya
        // selalu "Sukses". Kini: tabel InnoDB (dep_audit, jurnal_umum, tabel_transaksi, deposito_detail) dalam satu
        // transaksi; langkah MyISAM (audit_dep_realisasi, dep_realisasi, deposito, escrow) dijalankan sesudahnya, masih
        // sebelum commit. Gagal di mana pun -> rollback + langkah MyISAM yang sudah jalan dikembalikan, baris tetap di
        // daftar dan bisa diulang. Rekening, isi jurnal, dan urutan bisnis sama dengan sebelumnya.
        $db = db_ni();
        $gagal_awal = db_jumlah_gagal();
        $db->begin_transaction();
        $pesan_gagal = null;

        // --- langkah InnoDB ---
        $aro = null;
        $cif = null;
        if ($posisi == 'PENEMPATAN') {
            // jurnal_umum cukup satu kali (versi lama dua kali untuk kode yang sama)
            $jurnal_umum();
            $transaksi('1010-2851000', 'kredit', 'Bukti Umum');
            $transaksi($rek, 'debet', 'Bukti Umum');
        } elseif ($posisi == 'BREAK') {
            // 2026-11-20 (perintah user, §51): baris deposito diarsip ke dep_audit sebelum dihapus. Bila arsip gagal,
            // proses dihentikan tanpa perubahan (dulu deposito dihapus tanpa salinan).
            require_once __DIR__ . '/../../inc/arsip_deposito.php';
            if (!arsip_deposito($norek, 'BREAK', $tgl, $userid)) {
                $pesan_gagal = "Arsip data deposito " . h($norek) . " gagal disimpan (tabel dep_audit). Break tidak diproses, hubungi admin.";
            } else {
                // jurnal_umum cukup satu kali (versi lama dua kali untuk kode yang sama)
                $jurnal_umum();
                $transaksi('1010-2852000', 'debet', 'Bukti Umum');
                $transaksi($rek, 'kredit', 'Bukti Umum');
                $bayar_nisbah();
            }
        } else {
            foreach (db_semua("select * from deposito WHERE norek = ?", 's', $norek) as $st_aro) {
                $aro = $st_aro['aro'];
                $cif = $st_aro['cif'];
            }
            if ($aro == 'ARO-ARO') {
                // catatan: deposito tidak pernah ber-aro 'ARO-ARO' (penempatan menyimpan ARO_bulan/ARO_jatpo), cabang
                // ini dipertahankan apa adanya
                $bayar_nisbah();
            } else {
                $jurnal_umum();
                $transaksi('1010-2837000', 'debet', 'BS');
                $transaksi($rek, 'kredit', 'BS');
                $bayar_nisbah();
            }
        }
        if ($pesan_gagal === null && db_jumlah_gagal() > $gagal_awal) {
            $pesan_gagal = "Jurnal " . h($kode_transaksi) . " gagal disimpan: " . h(db_error_terakhir());
        }

        // --- langkah MyISAM (tanpa rollback; dicatat agar bisa dikembalikan) ---
        $sudah_audit = false;
        $sudah_hapus = false;
        if ($pesan_gagal === null) {
            // salinan ke audit hanya baris ini. Bila id yang sama sudah ada di audit (BREAK dan PENEMPATAN sama-sama ber-id
            // norek) salinan dilewati, sama hasilnya dengan versi lama yang gagal diam-diam di kunci utama.
            if (!db_satu("select 1 as ada from audit_dep_realisasi where id_realisasi=?", 's', $id_realisasi)) {
                if (db_jalankan("insert into audit_dep_realisasi select * from dep_realisasi where id_realisasi =?", 's', $id_realisasi)) {
                    $sudah_audit = true;
                } else {
                    $pesan_gagal = "Salinan audit realisasi gagal: " . h(db_error_terakhir());
                }
            }
        }
        if ($pesan_gagal === null) {
            if (db_jalankan("delete from dep_realisasi where id_realisasi =?", 's', $id_realisasi)) {
                $sudah_hapus = true;
            } else {
                $pesan_gagal = "Antrean realisasi gagal dihapus: " . h(db_error_terakhir());
            }
        }
        if ($pesan_gagal === null) {
            if ($posisi == 'PENEMPATAN') {
                $ok = db_jalankan("update deposito set status_print='OK' where norek=?", 's', $norek);
            } elseif ($posisi == 'BREAK') {
                $ok = db_jalankan("delete from deposito where norek =?", 's', $norek);
            } elseif ($aro == 'ARO-ARO') {
                $ok = db_jalankan("insert into escrow_deposito (BUSS_DATE,NOREK,UNIT,CIF, DEBIT, TYPE,KREDIT,USERID,KET)
                    values(?,?,?,?,'0','01',?,?,'ARO')", 'ssssss', $buss_date, $norek, $unit, (string) $cif, $nomi, $userid)
                    && db_jalankan("update deposito set nominal=nominal+?, status_print='OK' where norek=?", 'ss', $nomi, $norek);
            } else {
                $ok = db_jalankan("update deposito set status_print='OK' where norek=?", 's', $norek);
            }
            if (!$ok) {
                $pesan_gagal = "Data deposito " . h($norek) . " gagal diperbarui: " . h(db_error_terakhir());
            }
        }
        if ($pesan_gagal === null && !$db->commit()) {
            $pesan_gagal = "Simpan gagal: " . h($db->error);
        }

        if ($pesan_gagal !== null) {
            $db->rollback();
            if ($sudah_hapus) {
                db_jalankan("insert into dep_realisasi(id_realisasi,norek,cif,jumlah_bayar,tgl_bayar,tgl_jatuh_tempo,norek_tujuan,ket,status)
                    values(?,?,?,?,?,?,?,?,?)", 'sssssssss', $id_realisasi, (string) $baris['norek'], (string) $baris['cif'],
                    (string) $baris['jumlah_bayar'], (string) $baris['tgl_bayar'], (string) $baris['tgl_jatuh_tempo'],
                    (string) $baris['norek_tujuan'], (string) $baris['ket'], (string) $baris['status']);
            }
            if ($sudah_audit) {
                db_jalankan("delete from audit_dep_realisasi where id_realisasi =?", 's', $id_realisasi);
            }
            $lepas();
            echo "<h5>* Disburse " . h($norek) . " GAGAL, data tidak disimpan. Silakan ulangi.</h5><h5>" . $pesan_gagal . "</h5>";
            break;
        }

        $lepas();
        $_SESSION['info'] = "Mengubah";
        echo "Sukses";
        break;

    case "hapus":
        echo "Maaf ada tidak bisa mengkases menu ini silahkan hubungi atasan anda";
        $_SESSION['info'] = "GAGAL";
        header("location:view/view_jatuh.php");
        break;
}

ob_end_flush();
