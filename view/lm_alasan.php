<?php
if (empty($_SESSION['user'])) die(json_encode(array('errorMsg' => 'Expired Session <br> Please Relogin')));
?>
<form method='post' target='_blank' action='data/report/lap_m_alasan.php' id="form_input" style="width:520px">
    <div style="padding:5px;border:1px solid #ddd;">
        <a id="btn_print" href="#" class="easyui-linkbutton" data-options="iconCls:'icon-print', plain:true">Tampilkan Data</a>
		<a id="btn_export_excel" href="#" class="easyui-linkbutton" data-options="iconCls:'icon-excel', plain:true">Export</a>
    </div>

    <table style="border-bottom:1px #000" id="label_laporan">
		<tr>
			<td align="right" id="label_laporan">Jenis Transaksi :</td>
			<td>
				<select id="txt_jenistransaksi" name="txt_jenistransaksi" style="width:100px" class="easyui-combobox" required="true" panelHeight="auto">
					<option value="">--- Pilih ---</option>
					<option value="RETUR JUAL">RETUR JUAL</option>
					<option value="RETUR BELI">RETUR BELI</option>
					<option value="BARANG RUSAK">BARANG RUSAK</option>
				</select>				
			</td>
		</tr>
    </table>
	
	<input type="hidden" name="excel" id="excel">
	<input type="hidden" name="file_name" id="file_name" value="LocationReport">
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

function browse_data(id, table) {
	$(id).combogrid({
		panelWidth:360,
		url: 'config/combogrid.php?table='+table,
		idField:'NAMA',
		textField:'NAMA',
		mode:'remote',
		sortName:'NAMA',
		sortOrder:'asc',
		rowStyler: function(index,row) {
			if (row.STATUS == 0) return 'background-color:#A8AEA6';
		},
		columns:[[
			{field:'KODE',title:'Kode',width:80, sortable:false},
			{field:'NAMA',title:'Nama',width:250, sortable:false},
		]]
	});
}
</script>