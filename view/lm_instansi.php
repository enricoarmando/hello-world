<?php
if (empty($_SESSION['user'])) header('Location: ../../../index.php');
?>
<form method='post' target='_blank' action='data/report/lap_m_instansi.php' id="form_input" style="width:520px">
    <div style="padding:5px;border:1px solid #ddd;">
        <a id="btn_print" href="#" class="easyui-linkbutton" data-options="iconCls:'icon-print', plain:true">Tampilkan Data</a>
		<a id="btn_export_excel" href="#" class="easyui-linkbutton" data-options="iconCls:'icon-excel', plain:true">Export</a>
    </div>

    <table style="border-bottom:1px #000" id="label_laporan">
		<tr>
			<td align="right">Instansi :</td>
			<!--<td><input id="txt_kode_awal" name="txt_kode_awal" style="width:200px"> - <input id="txt_kode_akhir" name="txt_kode_akhir" style="width:200px"></td>!-->
			<td><input class="label_input" id="txt_namainstansi" name="txt_namainstansi" style="width:200px"></td>
		</tr>
    </table>
	
	<input type="hidden" name="excel" id="excel">
	<input type="hidden" name="file_name" id="file_name" value="LaporanInstansi">
</form>
<script>
$(document).ready(function(){
	browse_data('#txt_kode_awal', 'instansi');
	browse_data('#txt_kode_akhir', 'instansi');
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
		panelWidth:740,
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
			{field:'KODE',title:'Kode',width:80, sortable:true},
			{field:'NAMA',title:'Nama',width:200, sortable:true},
			{field:'ALAMAT',title:'Alamat',width:200, sortable:true},
			{field:'KOTA',title:'Kota',width:80, sortable:true},
			{field:'TELP',title:'Kota',width:80, sortable:true},
			{field:'FAX',title:'Kota',width:80, sortable:true},
		]]
	});
}
</script>