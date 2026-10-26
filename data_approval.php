<?php require_once __DIR__ . '/php8_compat.php';
include "conn2.php";
 $koneksi=open_connection2();
?>
<HTML>
	<HEAD>
		
		<!-- JQUERY -->
		<script type="text/javascript" language="javascript" src="assets/media/js/jquery.js"></script>
		
		<!-- BOOTSTRAP -->
		<!-- Latest compiled and minified CSS -->
		<link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
		
		<!-- Optional theme -->
		<link rel="stylesheet" href="assets/bootstrap/css/bootstrap-theme.min.css">
		
		<!-- Latest compiled and minified JavaScript -->
		<script src="assets/bootstrap/js/bootstrap.min.js"></script>
		
		<!-- DataTables -->
		<link rel="stylesheet" type="text/css" href="assets/media/css/dataTables.bootstrap.css">
		<link rel="stylesheet" type="text/css" href="assets/media/css/dataTables.responsive.css">
		<script type="text/javascript" language="javascript" src="assets/media/js/jquery.dataTables.js"></script>
		<script type="text/javascript" language="javascript" src="assets/media/js/dataTables.responsive.js"></script>
		<script type="text/javascript" language="javascript" src="assets/media/js/dataTables.bootstrap.js"></script>
		<script type="text/javascript" language="javascript" src="assets/media/js/common.js"></script>
		<script type="text/javascript" language="javascript" >
			
			var dTable;
			// #Example adalah id pada table
			$(document).ready(function() {
				dTable = $('#example').DataTable( {
					"bProcessing": true,
					"bServerSide": true,
					"bJQueryUI": false,
					"responsive": true,
					"sAjaxSource": "server_side_approval.php", // Load Data
					"sServerMethod": "POST",
					"columnDefs": [
					{ "orderable": false, "targets": 0, "searchable": false },
					{ "orderable": true, "targets": 1, "searchable": true },
					{ "orderable": true, "targets": 2, "searchable": true },
					{ "orderable": true, "targets": 3, "searchable": true },
					{ "orderable": true, "targets": 4, "searchable": true },
					{ "orderable": true, "targets": 5, "searchable": true },
					{ "orderable": true, "targets": 6, "searchable": true },
				
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
				<div class="col-sm-12">
					
					<br>
					<hr>
					<br>
					<table id="example"  cellpadding="0" cellspacing="0" border="0" class="display" width="100%">
						<thead>
							<tr>
								
<th>kode Kel</th>								
<th>Norek</th>
								<th>cif</th>
								<th>nama</th>
								<th>Pembiayaan </th>
								<th>Saldo Pembiayaan</th>
								<th>Saldo Margin</th>
								<th>Action</th>
							</tr>
						</thead>
						<tbody>
						</tbody>
						<tfoot>
							
<th>Kode Kel</th>
							<th>Norek</th>
								<th>cif</th>
								<th>nama</th>
								<th>Pembiayaan </th>
								<th>Saldo Pembiayaan</th>
								<th>Saldo Margin</th>
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
						<h4 class="modal-title" id="myModalLabel">Pemeliharaan Tagihan</h4>
					</div>
					<div class="modal-body">
						
						<div class="alert alert-danger" role="alert" id="removeWarning">
							<span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>
							<span class="sr-only">Error:</span>
							Anda yakin ingin menghapus user ini
						</div>
						<br>
						<form class="form-horizontal" id="formUser">
							
								<input type="hidden" class="form-control" id="id" name="id">
								<input type="hidden" class="form-control" id="type" name="type">
									
							<div class="form-group">
								<label for="id_pinjam" class="col-sm-2 control-label">id_pinjaman</label>
								<div class="col-sm-10">
									<input type="text" class="form-control" id="id_pinjam" name="id_pinjam">
								</div>
							</div>
							<div class="form-group">
								<label for="Cust_Short_name" class="col-sm-2 control-label">Cust_Short_name</label>
								<div class="col-sm-10">
									<input type="text" class="form-control" id="Cust_Short_name" name="Cust_Short_name" >
								</div>
							</div>
							<div class="form-group">
								<label for="tgl_jatuh_tempo" class="col-sm-2 control-label">Tgl Jatuh </label>
								<div class="col-sm-10">
									<input type="text" class="form-control" id="tgl_jatuh_tempo" name="tgl_jatuh_tempo">
								</div>
							</div>
							<div class="form-group">
								<label for="jumlah_bayar" class="col-sm-2 control-label">Jumlah Bayar</label>
								<div class="col-sm-10">
									<input type="jumlah_bayar" class="form-control" id="jumlah_bayar" name="jumlah_bayar" >
								</div>
							</div>
							
								<div class="form-group">
								<label for="status" class="col-sm-2 control-label">STATUS</label>
								<div class="col-sm-10">
								<select name="status" id ="status" class="form-control">
						        <option value="">STATUS</option>
						        <option value="RETURN">RETURN</option>
						        <option value="BAYAR">BAYAR</option>
 								
						  </select> 
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
						url: "view_data_n_curd.php",
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
				$("#id").val(data.id);
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
						$("#myModalLabel").html("Delete User");
						$("#id").val(data.id);
						$("#id_pinjam").val(data.id_pinjam);
						$("#type").val("delete");
						$("#Cust_Short_name").val(data.Cust_Short_name).attr("disabled","true");
						$("#status").val(data.status).attr("disabled","true");
						$("#jumlah_bayar").val(data.jumlah_bayar).attr("disabled","true");
						$("#tgl_jatuh_tempo").val(data.tgl_jatuh_tempo).attr("disabled","true");
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
