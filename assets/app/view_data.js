/* View Data (view_data_n.php): DataTables server-side, kolom dan permintaan sama dengan versi lama. */
$(function () {
    var angka = function (v) {
        // tampilan angka dengan pemisah ribuan; nilai kosong tetap kosong
        return v === null || v === '' ? '' : Number(v).toLocaleString('en-US');
    };
    $('#example').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: 'server_side.php',
            type: 'POST'
        },
        columns: [
            { data: 'ID_NUMBER' },
            { data: 'unit' },
            { data: 'code_kel' },
            { data: 'no_anggota' },
            { data: 'cif' },
            { data: 'Cust_Short_name' },
            { data: 'os', className: 'text-end', render: angka },
            { data: 'Plafond', className: 'text-end', render: angka },
            { data: 'saldo_margin', className: 'text-end', render: angka },
            { data: 'action', orderable: false, searchable: false, className: 'text-center' }
        ],
        language: {
            processing: 'Memuat data…',
            search: 'Cari:',
            lengthMenu: 'Tampilkan _MENU_ baris',
            info: 'Baris _START_–_END_ dari _TOTAL_',
            infoEmpty: 'Tidak ada data',
            infoFiltered: '(disaring dari _MAX_ data)',
            zeroRecords: 'Data tidak ditemukan',
            paginate: { first: 'Awal', last: 'Akhir', next: '›', previous: '‹' }
        }
    });
});
