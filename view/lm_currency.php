<?php
if (empty($_SESSION['user'])) header('Location: ../../../index.php');
?>
<form method='post' target='_blank' action='data/report/lap_m_.php' id="form_input" style="width:500px">
    <div style="padding:5px;border:1px solid #ddd;">
        <a id="btn_print" href="#" class="easyui-linkbutton" data-options="iconCls:'icon-print', plain:true">Tampilkan Data</a>
		<a id="btn_export_excel" href="#" class="easyui-linkbutton" data-options="iconCls:'icon-excel', plain:true">Export</a>
    </div>

    <table style="border-bottom:1px #000" id="label_laporan">
    	<tr>
			<td align="right" id="label_laporan">Currency :</td>
			<td><input id="txt_kode_awal" name="txt_kode_awal" style="width:100px"/> - <input id="txt_kode_akhir" name="txt_kode_akhir" style="width:100px"/></td>
		</tr>
    </table>
	
	<input type="hidden" name="excel" id="excel">
	<input type="hidden" name="file_name" id="file_name" value="CurrencyReport">
</form>
<script>
$(document).ready(function(){
	browse_data('#txt_kode_awal', 'currency');
	browse_data('#txt_kode_akhir', 'currency');
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

function browse_data(id, table) {
	$(id).combogrid({
		panelWidth:360,
		url: 'config/combogrid.php?table='+table,
		idField:'NAMA',
		textField:'NAMA',
		mode:'remote',
		sortName:'NAMA',
		sortOrder:'asc',
		rowStyler: function(index,row){  
			if (row.STATUS == 0){  
				return 'background-color:#A8AEA6';  
			}  
		},
		columns:[[
			{field:'KODE',title:'ID',width:80, sortable:false},
			{field:'NAMA',title:'Name',width:250, sortable:false},
		]]
	});
}
</script>