<?php 
	$message = $this->session->flashdata('message');
	echo isset($message) ? '<h4 align="center" class="err">'.$message.'</h4>' : '';
?>

<?php echo form_open('c_main/management_ppiu');?>
<html>
<head>
	<title><?php echo $title ?></title>
	<!--Load Bootstrap-->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <link href="https://cdn.datatables.net/1.10.13/css/jquery.dataTables.min.css" rel="stylesheet">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
</head>

<body>
		<div class="container" style="margin-top: 50px">
		<?php echo $this->session->flashdata('message_success') ?>
		<a href="<?php echo base_url('index.php/c_main/add_data_siskopatuh/'); ?>"
			class="btn btn-md btn-success">*add PPIU</a>
        <!-- <a href="<?php echo site_url() ?>/c_main/add_data_siskopatuh/" class="btn btn-md btn-success">*add PPIU</a>
 -->
        <hr>
        <!--Table-->
        <div class="table-responsive">
			<table id="table" class="table table-striped table-bordered table-hover">
				<thead>
					<tr>
						<th>Kode PPIU</th>
						<th>Nama PPIU</th>
						<th>Rek.Penampung PPIU</th>
						<th>Rek.Operasional PPIU</th>
						<th style="text-align: right;">Actions</th>
					</tr>
				</thead>
				<tbody id="show_data"></tbody>
			</table>
		</div>
	</div>

	<!--Modal Delete-->
	<div class="modal fade" id="ModalHapus" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h4 class="modal-title" id="myModalLabel">Confirm Delete</h4>
				</div>
			<form class="form-horizontal">
				<div class="modal-body">
					<input type="hidden" name="kode" id="id_cus" value="">
					<div class="alert alert-danger"><p>Delete Data PPIU</p></div>
				</div>
			<div class="modal-footer">
				<button class="btn_hapus btn btn-danger" id="btn_hapus" data-toggle="modal" data-target="#ModalHapus">Delete</button>
				<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>	
			</div>
			</form>
			</div>
		</div>
	</div>

		<!-- Load JavaScript and JQuery -->

        <script src="https://cdn.datatables.net/1.10.13/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.10.13/js/dataTables.bootstrap.min.js"></script>

 		<script type="text/javascript" language="javascript"> 

 			//Dikomen Sementara
 			$(document).ready(function() {
 				var oTable = $('#table').dataTable({
 					"ajax": "<?php echo site_url("c_main/dtManagementPPPIU") ?>"
    			});  
			}); 

 			function showDeleteModal()
 			{
 				$.ajax({
 					type : "POST",
 					url  : "<?php echo base_url();?>c_main/delete",
 					success : function(html){
 						$('#ModalHapus').modal('show');
 					},
 					error: function(){
 						alert('ajax gue gak bisa coooooooy');
 					}
 				});
 			}

 			function clickListener()
 			{
 				$('.openModal').unbind();
 				$('.openModal').click(function (e)){
 					e.preventDefault();
 					showDeleteModal();
 				});
 			}


			/*function showDeleteModal(val)
			{	

				var  a = confirm("Anda yakin akan menghapus data ini?");

				console.log(val);

				if(a){
					console.log("yes");

				}else{
					console.log("no");
				}
/*
				console.log("modalllll ngg kelainlanbedjgahb == " + val);
				$('#ModalHapus').modal();
			}*/
		</script>
</body>
</html>

<?php echo form_close();?>

