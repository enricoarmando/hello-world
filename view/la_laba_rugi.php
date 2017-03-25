<?php
session_start();
if (empty($_SESSION['user'])) header('Location: ../../../index.php');
?>
<form method='post' target='_blank' action='data/report/lap_akun_laba_rugi.php' id="form_input" style="width:300px">
	<input type="hidden" name="txt_namalokasi" id="txt_namalokasi">
	
    <div style="padding:5px;border:1px solid #ddd;">
        <a id="btn_print" href="#" class="easyui-linkbutton" data-options="iconCls:'icon-print', plain:true">Tampilkan Data</a>
		<a id="btn_export_excel" href="#" class="easyui-linkbutton" data-options="iconCls:'icon-excel', plain:true">Export</a>
    </div>
    <table>
		<tr>
            <td align="left" id="label_form" width="50px">Lokasi</td>
            <td colspan="3"><input id="txt_lokasi" name="txt_lokasi[]" style="width:250px"/></td>
        </tr>        
        <tr>
			<td id="label_form">Bulan</td>
			<td>
				<select id="sb_bulan" class="easyui-combobox" name="sb_bulan">
					<option value="01">JANUARI</option>
					<option value="02">FEBRUARI</option>
					<option value="03">MARET</option>
					<option value="04">APRIL</option>
					<option value="05">MEI</option>
					<option value="06">JUNI</option>
					<option value="07">JULI</option>
					<option value="08">AGUSTUS</option>
					<option value="09">SEPTEMBER</option>
					<option value="10">OKTOBER</option>
					<option value="11">NOVEMBER</option>
					<option value="12">DESEMBER</option>
				</select>
			</td>
			<td id="label_form">Tahun</td>
			<td><input name="txt_tahun" type="text" class="easyui-numberspinner" id="txt_tahun" style="width:60px" maxlength="4" data-options="min:1990" value="<?=date("Y")?>"></td>
		</tr>
    </table>
	
	<input type="hidden" name="cetak_akun" id="cetak_akun" value="0">
	<input type="hidden" name="excel" id="excel">
	<input type="hidden" name="file_name" id="file_name" value="LaporanLabaRugi">
</form>
<script>
$(document).ready(function(){
	browse_data_lokasi('#txt_lokasi', 'lokasi&status=all','KODE');	
	$('#sb_bulan').combobox('setValue', <?php echo date("m"); ?>);
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

function browse_data_lokasi(id, table, sort) {
	$(id).combogrid({
		panelWidth:360,
		url: 'config/combogrid.php?table='+table,
		idField:'KODE',
		textField:'KODE',
		multiple: true,
		mode:'local',
		sortName:sort,
		sortOrder:'asc',
		rowStyler: function(index,row){  
			if (row.STATUS == 0){  
				return 'background-color:#A8AEA6';  
			}  
		},
		columns:[[
			{field:'ck',checkbox:true},
			{field:'KODE',title:'Kode',width:80, sortable:true},
			{field:'NAMA',title:'Nama',width:240, sortable:true},
		]],
		onChange:function () {
			$('#txt_namalokasi').val($(this).combogrid('getText'));
		}
	});
}
</script>