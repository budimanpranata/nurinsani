<?php require_once __DIR__ . '/php8_compat.php';
session_start();
ob_start();

// Cek apakah user sudah login
if (!isset($_SESSION['userid'])) {
    header("location:index.html");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DataTables dengan Pencarian & Filter</title>
    
    <!-- Bootstrap -->
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <script src="assets/bootstrap/js/bootstrap.min.js"></script>
    
    <!-- jQuery -->
    <script src="assets/media/js/jquery.js"></script>
    
    <!-- DataTables -->
    <link rel="stylesheet" type="text/css" href="assets/media/css/dataTables.bootstrap.css">
    <script src="assets/media/js/jquery.dataTables.js"></script>
    <script src="assets/media/js/dataTables.bootstrap.js"></script>
</head>
<body>

<div class="container">
    <h2 class="text-center">Data Pembiayaan</h2>
    <hr>
    
    <table id="example" class="table table-striped table-bordered" width="100%">
        <thead>
            <tr>
                <th>KTP</th>
                <th>Unit</th>
                <th>Kode Kel</th>
                <th>Norek</th>
                <th>CIF</th>
                <th>Nama</th>
                <th>Pembiayaan</th>
                <th>Plafond</th>
                <th>Saldo Margin</th>
                <th>Action</th>
            </tr>
        </thead>
    </table>
</div>

<!-- Modal Edit Data -->
<div class="modal fade" id="editModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Data</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="editForm">
                    <input type="hidden" id="edit_id" name="id">
                    
                    <div class="form-group">
                        <label>Nama</label>
                        <input type="text" class="form-control" id="edit_nama" name="nama" required>
                    </div>
                    <div class="form-group">
                        <label>Unit</label>
                        <input type="text" class="form-control" id="edit_unit" name="unit" required>
                    </div>
                    <div class="form-group">
                        <label>Pembiayaan</label>
                        <input type="text" class="form-control" id="edit_pembiayaan" name="pembiayaan" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    var dTable = $('#example').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "server_side.php",
            "type": "POST"
        },
        "columns": [
            { "data": "ID_NUMBER" },
            { "data": "unit" },
            { "data": "code_kel" },
            { "data": "no_anggota" },
            { "data": "cif" },
            { "data": "Cust_Short_name" },
            { "data": "os" },
            { "data": "Plafond" },
            { "data": "saldo_margin" },
            { "data": "action", "orderable": false, "searchable": false }
        ]
    });

    // Tampilkan modal saat tombol Edit diklik
    $('#example tbody').on('click', '.editBtn', function() {
        var data = dTable.row($(this).parents('tr')).data();
        $('#edit_id').val(data.id);
        $('#edit_nama').val(data.nama);
        $('#edit_unit').val(data.unit);
        $('#edit_pembiayaan').val(data.pembiayaan);
        $('#editModal').modal('show');
    });

    // Proses update data
    $('#editForm').submit(function(e) {
        e.preventDefault();
        $.ajax({
            url: "update_data.php",
            type: "POST",
            data: $(this).serialize(),
            success: function(response) {
                alert(response);
                $('#editModal').modal('hide');
                dTable.ajax.reload();
            }
        });
    });
});
</script>

</body>
</html>
