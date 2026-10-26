<?php require_once __DIR__ . '/../php8_compat.php';
session_start();
$userid=$_SESSION['userid'];
$tgl_system=$_SESSION['stgl_system'];
if(!isset($_SESSION['userid'])){
   header("location:index.html");
}
if($_SESSION['level']!="lead"){
   header("location:index.html");
}
if(!isset($_SESSION['stgl_system'])){
echo "<script>alert('Maaf,.tanggal system belum di setup');</script>";
   }
?>
<HTML>
	<HEAD>
		
		<!-- JQUERY -->
		<script type="text/javascript" language="javascript" src="../assets/media/js/jquery.js"></script>
		
		<!-- BOOTSTRAP -->
		<!-- Latest compiled and minified CSS -->
		<link rel="stylesheet" href="../assets/bootstrap/css/bootstrap.min.css">
		
		<!-- Optional theme -->
		<link rel="stylesheet" href="../assets/bootstrap/css/bootstrap-theme.min.css">
		<link rel="stylesheet" href="../assets/bootstrap/css/bootstrap.min.css">
		<!-- Latest compiled and minified JavaScript -->
		<script src="../assets/bootstrap/js/bootstrap.min.js"></script>
		
		<!-- DataTables -->
		<link rel="stylesheet" type="text/css" href="../assets/media/css/dataTables.bootstrap.css">
		<link rel="stylesheet" type="text/css" href="../assets/media/css/dataTables.responsive.css">
		<script type="text/javascript" language="javascript" src="../assets/media/js/jquery.dataTables.js"></script>
		<script type="text/javascript" language="javascript" src="../assets/media/js/dataTables.responsive.js"></script>
		<script type="text/javascript" language="javascript" src="../assets/media/js/dataTables.bootstrap.js"></script>
		<script type="text/javascript" language="javascript" src="../assets/media/js/common.js"></script>
		<script type="text/javascript" language="javascript" >
			
			var dTable;
			// #Example adalah id pada table
			$(document).ready(function() {
				dTable = $('#example').DataTable( {
					"bProcessing": true,
					"bServerSide": true,
					"bJQueryUI": false,
					
					"sAjaxSource": "serverSide.php", // Load Data
					"sServerMethod": "POST",
					"columnDefs": [
					{ "orderable": false, "targets": 0, "searchable": false },
					{ "orderable": false, "targets": 1, "searchable": true },
					{ "orderable": false, "targets": 2, "searchable": true },
					{ "orderable": false, "targets": 3, "searchable": true },
					{ "orderable": false, "targets": 4, "searchable": true },
					{ "orderable": false, "targets": 5, "searchable": true },
					{ "orderable": false, "targets": 6, "searchable": true },
					{ "orderable": false, "targets": 7, "searchable": true },
					
					
				
					]
				} );
				
				$('#example').removeClass( 'display' ).addClass('table table-striped table-bordered');
				$('#example tfoot th').each( function () {
					
					//Agar kolom Action Tidak Ada Tombol Pencarian
					if( $(this).text() != "Action" ){
						var title = $('#example thead th').eq( $(this).index() ).text();
						$(this).html( '<input type="text" placeholder="Search '+title+'" class="form-control" />' );
					}
				} );
				
				// Untuk Pencarian, di kolom paling bawah
				dTable.columns().every( function () {
					var that = this;
					
					$( 'input', this.footer() ).on( 'keyup change', function () {
						that
						.search( this.value )
						.draw();
					} );
				} );
			} );
			
			
		</script>
	</HEAD>
	<BODY>
		<div class="container-fluid">
			<div class="row">
				<div class="col-md-12">
						
					<br>
					<hr>
					<br>
					<table id="example"  cellpadding="0" cellspacing="0" border="0" class="display" width="100%">
						<thead>
							<tr>
								
								
								 <th>unit</th>
          <th>tanggal</th>
          <th>kode_transaksi</th>
          <th>kode_rekening</th>
          <th>keterangan</th>
		  <th>Debet</th>
		  <th>Kredit</th>
          <th>Action</th>
							</tr>
						</thead>
						<tbody>
						</tbody>
						<tfoot>
							
							
							 <th>unit</th>
          <th>tanggal</th>
          <th>kode_transaksi</th>
          <th>kode_rekening</th>
          <th>keterangan</th>
		  <th>Debet</th>
		  <th>Kredit</th>
          <th>Action</th>
						</tfoot>
					</table>
				</div>
			</div>
		</div>
		
		<!-- Modal Popup -->
						
		<div class="modal fade" id="myModals" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title" id="myModalLabel">Add User</h4>
					</div>
					<div class="modal-body">
						
						<div class="alert alert-danger" role="alert" id="removeWarning">
							<span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>
							<span class="sr-only">Error:</span>
							Anda yakin ingin menghapus Data transaksi ini
						</div>
						<br>
						<form class="form-horizontal" id="formUser">
							
								<input type="hidden" class="form-control" id="id" name="id">
								<input type="hidden" class="form-control" id="type" name="type">
									
							<div class="form-group">
								<label for="kode_transaksi" class="col-sm-2 control-label">kode_transaksi</label>
								<div class="col-sm-10">
									<input type="text" class="form-control" id="kode_transaksi" name="kode_transaksi">
								</div>
							</div>
							<div class="form-group">
								<label for="kode_branch" class="col-sm-2 control-label">kode_branch</label>
								<div class="col-sm-10">
									<input type="text" class="form-control" id="kode_branch" name="kode_branch" >
								</div>
							</div>
							<div class="form-group">
								<label for="debet" class="col-sm-2 control-label">debet </label>
								<div class="col-sm-10">
									<input type="text" class="form-control" id="debet" name="debet">
								</div>
							</div>
							<div class="form-group">
								<label for="kredit" class="col-sm-2 control-label">kredit</label>
								<div class="col-sm-10">
									<input type="text" class="form-control" id="kredit" name="kredit" >
								</div>
							</div>
							
								
						</form>
						
					</div>
					<div class="modal-footer">
						<button type="button" onClick="submitUser()" class="btn btn-default" data-dismiss="modal">Submit</button>
						<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
					</div>
				</div>
			</div>
		</div>
		
		<script>
		
		    //Tampilkan Modal 
			function showModals( id )
			{
				waitingDialog.show();
				clearModals();
				
				// Untuk Eksekusi Data Yang Ingin di Edit atau Di Hapus 
				if( id )
				{
					$.ajax({
						type: "POST",
						url: "crud.php",
						dataType: 'json',
						data: {id:id,type:"get"},
						success: function(res) {
							waitingDialog.hide();
							setModalData( res );
						}
					});
				}
				// Untuk Tambahkan Data
				else
				{
					$("#myModals").modal("show");
					$("#myModalLabel").html("New User");
					$("#type").val("new"); 
					waitingDialog.hide();
				}
			}
			
			//Data Yang Ingin Di Tampilkan Pada Modal statusika Di Edit 
			function setModalData( data )
			{
				$("#myModalLabel").html(data.Cust_Short_name);
				$("#id_transaksi").val(data.id_transaksi);
				$("#id_pinjam").val(data.id_pinjam);
				$("#type").val("edit");
				$("#Cust_Short_name").val(data.Cust_Short_name);
				$("#jumlah_bayar").val(data.jumlah_bayar);
				$("#status").val(data.status);
				$("#tgl_jatuh_tempo").val(data.tgl_jatuh_tempo);
				$("#myModals").modal("show");
			}
			
			//Submit Untuk Eksekusi Tambah/Edit/Hapus Data 
			function submitUser()
			{
				var formData = $("#formUser").serialize();
				waitingDialog.show();
				$.ajax({
					type: "POST",
					url: "crud.php",
					dataType: 'json',
					data: formData,
					success: function(data) {
						dTable.ajax.reload(); // Untuk Reload Tables secara otomatis
						waitingDialog.hide();	
					}
				});
			}
			
			//Hapus Data
			function deleteUser(id)
			{
				clearModals();
				$.ajax({
					type: "POST",
					url: "crud.php",
					dataType: 'json',
					data: {id:id,type:"get"},
					success: function(data) {
						$("#removeWarning").show();
						$("#myModalLabel").html("Delete Data Transaksi");
						$("#id").val(data.kode_transaksi);						
						$("#type").val("delete");
						$("#kode_transaksi").val(data.kode_transaksi);	
						$("#kode_branch").val(data.unit);
						$("#debet").val(data.debet);
						$("#kredit").val(data.kredit);
						$("#myModals").modal("show");
						waitingDialog.hide();			
					}
				});
			}
			
			//Clear Modal atau menutup modal supaya tidak terjadi duplikat modal
			function clearModals()
			{
				$("#removeWarning").hide();
				$("#id_pinjam").val("").removeAttr( "disabled" );
				$("#id").val("").removeAttr( "disabled" );
				$("#Cust_Short_name").val("").removeAttr( "disabled" );
				$("#tgl_jatuh_tempo").val("").removeAttr( "disabled" );
				$("#jumlah_bayar").val("").removeAttr( "disabled" );
				$("#type").val("");
				$("#status").val("").removeAttr( "disabled" );
				
			}
			
		</script>
		
	</BODY>
</HTML>