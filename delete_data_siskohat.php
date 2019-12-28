

<script type="text/javascript">
	$(document).ready(function() {
		$('#ModalHapus').on('click', function(e){
			e.preventDefault();
			var KD_PPIU=$('#id_cus').val();
			$.ajax({
				type : 'POST',
				url  : "<?php echo base_url()?>index.php/c_main/delete_siskohat",
				data : {KD_PPIU:KD_PPIU},
				success : function(data){
					alert 'success';
				}
			});
		}
	}

	/*function reload_table()
	{
		table.ajax.reload(null, false); //untuk reload datatable ajax
	}*/

	/*function delete_data(KD_PPIU)
	{
		if(confirm('Delete Data PPIU'))
		{
			//Ajax Delete Data to Database
			$.ajax({
				url : "<?php echo site_url('c_main/haiDelete')?>/"+KD_PPIU,
				type : "POST", 
				dataType : "JSON", 
				success  : function(data)
				{
					//if success reload ajax table
					$('#ModalHapus').modal('hide');
					reload_table();
				},

				error : function (jqXHR, textStatus, errorThrown)
				{
					alert('Error deleting data');
				}
			});
		}*/
	}
</script>

