/* Monitoring > Daily Deposito level kp (kp/view/view_bayar_dep.php): modal Disburse dan pengiriman AJAX ke
   ../transaksi/engine_update_dep.php?p=update. Alur sama dengan versi lama (jquery.form): jawaban "Sukses" -> muat ulang
   sesudah 2 detik; jawaban lain ditampilkan di modal peringatan. */
(function () {
    var modalEdit = bootstrap.Modal.getOrCreateInstance(document.getElementById('edit-data'));
    var modalPeringatan = bootstrap.Modal.getOrCreateInstance(document.getElementById('modal-peringatan'));
    var form = document.getElementById('form-data');
    var tombol = form.querySelector('.btn-submit');
    var progres = document.getElementById('progres');

    // isi modal dari tombol baris: id = norek, tanggal = tgl bayar, jumlah = nominal, keterangan = keterangan baris itu
    document.querySelectorAll('.tombol-disburse').forEach(function (b) {
        b.addEventListener('click', function () {
            form.elements.id.value = b.getAttribute('data-id');
            form.elements.jatuh.value = b.getAttribute('data-jatuh');
            form.elements.nomi.value = b.getAttribute('data-nomi');
            form.elements.ket.value = b.getAttribute('data-ket');
            modalEdit.show();
        });
    });

    function peringatan(html) {
        progres.classList.add('d-none');
        document.getElementById('pesan-required-field').innerHTML = html;
        modalPeringatan.show();
        tombol.disabled = false;
    }

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        if (tombol.disabled) { return; }
        progres.classList.remove('d-none');
        tombol.disabled = true;
        fetch(form.action, { method: 'POST', body: new FormData(form), credentials: 'same-origin' })
            .then(function (r) { return r.text(); })
            .then(function (teks) {
                if (teks.trim() === 'Sukses') {
                    setTimeout(function () { location.reload(); }, 2000);
                } else {
                    // jawaban server berupa daftar kesalahan HTML buatan engine_update_dep.php
                    peringatan(teks);
                }
            })
            .catch(function (err) {
                peringatan('Gagal Memproses data<br/>' + String(err).replace(/[<>&]/g, ''));
            });
    });
})();
