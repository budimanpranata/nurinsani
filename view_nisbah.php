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
    <h4 class="mb-3">📊 Data Nisbah Anggota</h4>

    <div class="d-flex mb-3 gap-2">
       
        <button class="btn btn-success" onclick="lihatData()">🔍 View Nisbah</button>
    </div>

    <table id="omzetTable" class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Tanggal</th>
                <th>Nama Kelompok</th>
                <th>Nama AO</th>
                <th>CIF</th>
                <th>Nama Anggota</th>
		<th>Ke</th>
                <th>nisbah</th>
		 <th>nisbah Anggota</th>
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
                <label>CIF</label>
                <input type="text" name="cif" id="cif" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Nominal</label>
                <input type="number" name="nominal" id="nominal" class="form-control" required>
            </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success">Simpan</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal View Data -->
<div class="modal fade" id="modalView" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title">📋 Data Pembiayaan (MUSYAROKAH)</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <table id="viewTable" class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>Kelompok</th>
                    <th>AO</th>
                    <th>CIF</th>
                    <th>Anggota</th>
                    <th>Plafond</th>
                    <th>Nisbah</th>
                    <th>Omzet</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
      </div>
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
            url: "ajax_nisbah.php?type=omzet",
            dataSrc: "data"
        },
        columns: [
            { data: "id" },
            { data: "tanggal" },
            { data: "nama_kel" },
            { data: "nama_ao" },
            { data: "cif" },
            { data: "nama" },
{ data: "cicilan" },
            { data: "nisbah" },
 	    { data: "nisbah_anggota" },
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
            url: 'crud_nisbah.php',
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

// --- FUNGSI TAMBAH DATA ---
function tambahData() {
    aksi = 'tambah';
    $('#modalTitle').text('Tambah Data');
    $('#formData')[0].reset();
    $('#modalForm').modal('show');
}

// --- FUNGSI EDIT DATA ---
function editData(id) {
    aksi = 'edit';
    $('#modalTitle').text('Edit Data');
    $.post('crud_nisbah.php', { id: id, aksi: 'get' }, function(res) {
        var data = JSON.parse(res);
        $('#id').val(data.id);
        $('#tanggal').val(data.tanggal);
        $('#cif').val(data.cif);
        $('#nominal').val(data.nominal);
        $('#modalForm').modal('show');
    });
}

// --- FUNGSI HAPUS DATA ---
function hapusData(id) {
    if (confirm('Yakin ingin menghapus data ini?')) {
        $.post('crud_nisbah.php', { id: id, aksi: 'hapus' }, function(res) {
            if (res == 'ok') table.ajax.reload();
        });
    }
}

// --- FUNGSI LIHAT DATA (VIEW) ---
function lihatData() {
    $('#modalView').modal('show');
    $('#viewTable').DataTable({
        destroy: true,
        ajax: {
            url: "ajax_nisbah.php?type=calonloan", // file PHP baru untuk query MUSYAROKAH
            dataSrc: "data"
        },
        columns: [
            { data: "nama_kel" },
            { data: "nama_ao" },
            { data: "cif" },
            { data: "Cust_Short_name" },
            { data: "Plafond" },
            { data: "nisbah" },
            { data: "omzet" }
        ]
    });
}
</script>
</body>
</html>
