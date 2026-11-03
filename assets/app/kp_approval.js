/* Monitoring > Approval level kp (kp/view/view_approval.php): modal Disburse dan pengiriman AJAX ke
   ../transaksi/engine_update_upproval.php?p=update. Alur sama dengan versi lama (jquery.form): jawaban "Sukses" -> muat
   ulang sesudah 2 detik; jawaban lain ditampilkan di modal peringatan. */
(function () {
    var modalEdit = bootstrap.Modal.getOrCreateInstance(document.getElementById('edit-data'));
    var modalPeringatan = bootstrap.Modal.getOrCreateInstance(document.getElementById('modal-peringatan'));
    var form = document.getElementById('form-data');
    var tombol = form.querySelector('.btn-submit');
    var progres = document.getElementById('progres');

    // isi modal dari tombol baris: unit, tanggal = tgl wakalah, tgl murabahah, jumlah = total plafond
    document.querySelectorAll('.tombol-disburse').forEach(function (b) {
        b.addEventListener('click', function () {
            form.elements.unit.value = b.getAttribute('data-unit');
            form.elements.jatuh.value = b.getAttribute('data-jatuh');
            form.elements.murab.value = b.getAttribute('data-murab');
            form.elements.nomi.value = b.getAttribute('data-nomi');
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
                    // jawaban server berupa daftar kesalahan HTML buatan engine_update_upproval.php
                    peringatan(teks);
                }
            })
            .catch(function (err) {
                peringatan('Gagal Memproses data<br/>' + String(err).replace(/[<>&]/g, ''));
            });
    });
})();
