<?php
if (empty($_SESSION['user'])) die(json_encode(array('errorMsg' => 'Expired Session <br> Please Relogin')));
?>
<form method='post' target='_blank' action='data/report/lap_m_satuan.php' id="form_input" style="width:500px">
    <div style="padding:5px;border:1px solid #ddd;">
        <a id="btn_print" href="#" class="easyui-linkbutton" data-options="iconCls:'icon-print', plain:true">Tampilkan Data</a>
		<a id="btn_export_excel" href="#" class="easyui-linkbutton" data-options="iconCls:'icon-excel', plain:true">Export</a>
    </div>

    <table style="border-bottom:1px #000">
		
    	<tr>
			<td align="right" id="label_laporan">Satuan :</td>
			<td id="label_laporan"><input id="txt_namasatuan" name="txt_namasatuan" class="label_input" style="width:250px" prompt="nama satuan">
			                       <input id="txt_kodesatuan" name="txt_kodesatuan" class="label_input" style="width:100px" prompt="kode satuan">
			</td>
		</tr>
    </table>
	
	
	<input type="hidden" name="excel" id="excel">
	<input type="hidden" name="file_name" id="file_name" value="Laporan Satuan">
</form>
<script>
$(document).ready(function(){
	
});

$("#btn_export_excel").click(function(){
	$('#excel').val('ya');
	$('#form_input').submit();
	return false;
});
$("#btn_print").click(function(){
	$('#excel').val('tidak');
	$('#form_input').submit();
	return false;
});


</script>