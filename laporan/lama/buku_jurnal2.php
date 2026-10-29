<html>
 <head>
 
<title>LIST JURNAL</title>

<title>KSPPS NURINSANI</title>

<link rel="stylesheet" href="../tabel.css" />

<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">

<style type="text/css">

<!--

.style3 {	color: #FFFFFF;

	font-size: 13px;

	font-weight: bold;

}

.header1 {color: #ffffff; background: #4682B4; font-weight: bold; font-family: Tahoma, Verdana; font-size: 13px; margin: 0px;}

body {

	background-image: url();

}

a:link {

	text-decoration: none;

}

a:visited {

	text-decoration: none;

}

a:hover {

	text-decoration: none;

}

a:active {

	text-decoration: none;

}

.style5 {font-family: Verdana, Arial, Helvetica, sans-serif}

.style6 {font-size: 12px}

.style7 {font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 12px; }

.style8 {font-size: 14px}

a {

	font-size: 12px;

}

.style9 {

	font-size: 16px;

	font-family: Verdana, Arial, Helvetica, sans-serif;

}

-->

</style>


<table width="100%">

  <tr bgcolor="#2F6B4F">

  <td height="100%" colspan="4" nowrap><div align="left" class="style3 style9">Buku Jurnal</div></td>

  </tr>

</table>

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" />
    <script src="https://cdn.datatables.net/1.10.15/js/dataTables.bootstrap.min.js"></script>
    <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
    <script src="https://cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.5.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.5.1/js/buttons.flash.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.32/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.32/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.5.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.5.1/js/buttons.print.min.js"></script>

  
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/css/bootstrap-datepicker.css" />
  <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/js/bootstrap-datepicker.js"></script>
  <style>
   body
   {
    margin:0;
    padding:0;
    background-color:#f1f1f1;
   }
   .box
   {
    width:1270px;
    padding:20px;
    background-color:#fff;
    border:1px solid #ccc;
    border-radius:5px;
    margin-top:25px;
   }
  </style>
  

 </head>
 <body>
  <div class="container box">
   <div class="table-responsive">
      <div class="row">
     <div class="input-daterange">
      <div class="col-md-4">
       <input type="text" name="start_date" id="start_date" class="form-control" />
      </div>
      <div class="col-md-4">
       <input type="text" name="end_date" id="end_date" class="form-control" />
      </div>      
     </div>
     <div class="col-md-4">
      <input type="button" name="search" id="search" value="Search" class="btn btn-info" />
     </div>
    </div>
       <table id="order_data" class="table table-bordered table-striped">
     <thead>
      <tr>
       <th> Unit</th>
       <th>Tanggal Transaksi</th>
       <th>kode transaksi</th>
       <th>Norek</th>
	   <th>Nama Rekening</th>
	   <th>Keterangan</th>
       <th>debet</th>
	   <th>Kredit</th>
      </tr>
     </thead>
	  <tfoot>
            <tr>
                <th></th>
                <th></th><th></th><th></th><th></th><th></th><th></th>
            </tr>
        </tfoot>
    </table>
    
   </div>
  </div>
 </body>
</html>

<script type="text/javascript" language="javascript" >
$(document).ready(function(){
 $('.input-daterange').datepicker({
  todayBtn:'linked',
  format: "yyyy-mm-dd",
  autoclose: true
 });
 fetch_data('no');

 function fetch_data(is_date_search, start_date='', end_date='')
 {
  var dataTable = $('#order_data').DataTable({
   "footerCallback": function ( row, data, start, end, display ) {
            var api = this.api(), data;
 
            // converting to interger to find total
            var intVal = function ( i ) {
                return typeof i === 'string' ?
                    i.replace(/[\$,]/g, '')*1 :
                    typeof i === 'number' ?
                        i : 0;
            };
 
            // computing column Total the complete result 
            var monTotal = api
                .column( 1 )
                .data()
                .reduce( function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0 );
				
			 var tueTotal = api
                .column( 2 )
                .data()
                .reduce( function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0 );
				
			 var wedTotal = api
                .column( 3 )
                .data()
                .reduce( function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0 );
				
			 var thuTotal = api
                .column( 4 )
                .data()
                .reduce( function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0 );
				
			 var friTotal = api
                .column( 5 )
                .data()
                .reduce( function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0 );
			
			var friTotal = api
                .column( 6 )
                .data()
                .reduce( function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0 );
				
            // Update footer by showing the total with the reference of the column index 
			$( api.column( 0 ).footer() ).html('Total');
            $( api.column( 1 ).footer() ).html(monTotal);
            $( api.column( 2 ).footer() ).html(tueTotal);
            $( api.column( 3 ).footer() ).html(wedTotal);
            $( api.column( 4 ).footer() ).html(thuTotal);
            $( api.column( 5 ).footer() ).html(friTotal);
            $( api.column( 6 ).footer() ).html(friTotal);
},

   "processing" : true,
   "serverSide" : true,
   "order" : [],
    "dom": 'lBfrtip', 
	"buttons": [
            {
                extend: 'collection',
                text: 'Export',
                buttons: [
                    'copy',
                    'excel',
                    'csv',
                    'pdf',
                    'print'
                ],
            }
        ],
   "ajax" : {
    url:"fetch.php",
    type:"POST",
   data:{
     is_date_search:is_date_search, start_date:start_date, end_date:end_date
    
         
	}
	  
   }
  });
 }

 $('#search').click(function(){
  var start_date = $('#start_date').val();
  var end_date = $('#end_date').val();
  if(start_date != '' && end_date !='')
  {
   $('#order_data').DataTable().destroy();
   fetch_data('yes', start_date, end_date);
  }
  else
  {
   alert("Both Date is Required");
  }
 }); 
 
 
 

});
</script>

