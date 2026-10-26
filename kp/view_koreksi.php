<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data Omzet</title>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/2.1.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
<style>
    .dataTables_wrapper {
  font-size: 16px;
}

.dataTables_filter input {
  height: 150px;
  font-size: 16px;
}

.dataTables_paginate a {
  font-size: 16px;
  padding: 8px 14px !important;
}
    </style>

</head>
<body class="bg-light">

<div class="container mt-5">
    <h4 class="mb-3">📊 Data Jurnal Kantor Pusat</h4>

    <div class="d-flex mb-3 gap-2">
       
      
    </div>

    <table id="omzetTable" class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Tanggal</th>
                <th>Unit</th>
                <th>Kode Transaksi</th>
                <th>Kode Rekening</th>
                <th>Jenis Transaksi</th>
		<th>Keterangan Transaksi</th>
                <th>Debet</th>
		 <th>Kredit</th>
                <th>Aksi</th>
            </tr>
        </thead>
    </table>
</div>

<!-- Modal Form Tambah/Edit -->
<div class="modal fade" id="modalForm" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="formData">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title" id="modalTitle">Tambah Data</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
            <input type="hidden" name="id" id="id">
            <div class="mb-3">
                <label>Tanggal</label>
                <input type="date" name="tanggal" id="tanggal" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>kode_transaksi</label>
                <input type="text" name="kode_transaksi" id="kode_transaksi" class="form-control" required>
            </div>
 	<div class="mb-3">
                <label>kode_rekening</label>
                <input type="text" name="kode_rekening" id="kode_rekening" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>debet</label>
                <input type="number" name="debet" id="debet" class="form-control" required>
            </div>
       
	<div class="mb-3">
                <label>kredit</label>
                <input type="number" name="kredit" id="kredit" class="form-control" required>
            </div>
     
        <div class="modal-footer">
          <button type="submit" class="btn btn-success">Simpan</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        </div>
      </form>
    </div>
  </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/2.1.7/js/dataTables.min.js"></script>
<script src="https://cdn.datatables.net/2.1.7/js/dataTables.bootstrap5.min.js"></script>

<script>
var table;
var aksi = '';

$(document).ready(function() {
    // --- TABEL OMZET UTAMA ---
    table = $('#omzetTable').DataTable({
        ajax: {
            url: "ajax_koreksi.php?type=koreksi",
            dataSrc: "data"
        },
        columns: [
            { data: "id" },
            { data: "tanggal" },
            { data: "unit" },
            { data: "kode_transaksi" },
            { data: "kode_rekening" },
            { data: "keterangan_transaksi" },
{ data: "jenis_transaksi" },
	    { data: "debet" },
            { data: "kredit" },
 	   
            {
                data: null,
                render: function(data) {
                    return `
                        <button class="btn btn-warning btn-sm" onclick="editData('${data.id}')">Edit</button>
                        <button class="btn btn-danger btn-sm" onclick="hapusData('${data.id}')">Hapus</button>
                    `;
                }
            }
        ],
        pageLength: 10,
        language: {
            search: "Cari:",
            lengthMenu: "Tampilkan _MENU_ data",
            zeroRecords: "Tidak ada data",
            info: "Menampilkan _START_ - _END_ dari _TOTAL_ data",
            paginate: { next: ">", previous: "<" }
        }
    });

    // --- SIMPAN DATA (Tambah/Edit) ---
    $('#formData').on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            url: 'crud_koreksi.php',
            type: 'POST',
            data: $(this).serialize() + '&aksi=' + aksi,
            success: function(res) {
                if (res == 'ok') {
                    $('#modalForm').modal('hide');
                    $('#formData')[0].reset();
                    table.ajax.reload();
                }
            }
        });
    });
});


// --- FUNGSI EDIT DATA ---
function editData(id) {
    aksi = 'edit';
    $('#modalTitle').text('Edit Data');
    $.post('crud_koreksi.php', { id: id, aksi: 'get' }, function(res) {
        var data = JSON.parse(res);
        $('#id').val(data.id);
        $('#tanggal').val(data.tanggal);
	$('#kode_rekening').val(data.kode_rekening);
        $('#kode_transaksi').val(data.kode_transaksi);
        $('#debet').val(data.debet);
	$('#kredit').val(data.kredit);
        $('#modalForm').modal('show');
    });
}

// --- FUNGSI HAPUS DATA ---
function hapusData(id) {
    if (confirm('Yakin ingin menghapus data ini?')) {
        $.post('crud_koreksi.php', { id: id, aksi: 'hapus' }, function(res) {
            if (res == 'ok') table.ajax.reload();
        });
    }
}


</script>
</body>
</html>
