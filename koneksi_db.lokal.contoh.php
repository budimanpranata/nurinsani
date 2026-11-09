<?php
/**
 * CONTOH kredensial database server. Salin menjadi koneksi_db.lokal.php (file itu tidak ikut git) lalu isi
 * user/password asli. Cukup isi kunci yang berbeda dari nilai bawaan di koneksi_db.php.
 * Gunakan user database khusus aplikasi (bukan root); lihat PEKERJAAN_TERTUNDA.md bagian 41.
 */
return array(
    'nur_insani' => array(
        'host' => 'localhost',
        'user' => 'nurinsani_app',
        'pass' => 'GANTI_DENGAN_PASSWORD_KUAT',
    ),
    'mobcol' => array(
        'host' => 'localhost',
        'user' => 'nurinsani_app',
        'pass' => 'GANTI_DENGAN_PASSWORD_KUAT',
    ),
    // Server PIP (pip.php, 192.168.88.71/pipsync)
    'pip' => array(
        'pass' => 'GANTI_DENGAN_PASSWORD_PIP',
    ),
    // Token endpoint api/categories/* (dipanggil antar-server tanpa login, inc/api_token.php).
    // HARUS SAMA di semua server yang saling memanggil. Buat: php -r "echo bin2hex(random_bytes(32)),\"\n\";"
    'api' => array(
        'token' => 'GANTI_DENGAN_TOKEN_ACAK_PANJANG',
    ),
    // Kunci enkripsi NIK/alamat (inc/enkripsi.php). Buat dengan: php inc/enkripsi.php buat-kunci
    // SIMPAN CADANGAN kunci ini terpisah dari backup database: tanpa kunci, data terenkripsi tidak dapat dibuka lagi.
    // Jangan pernah mengganti kunci yang sudah dipakai tanpa proses enkripsi ulang.
    'enkripsi' => array(
        'kunci' => 'GANTI_DENGAN_HASIL_buat-kunci',
        'kunci_indeks' => 'GANTI_DENGAN_HASIL_buat-kunci',
    ),
);
