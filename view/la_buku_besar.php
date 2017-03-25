<?php
if (empty($_SESSION['user'])) header('Location: ../../../index.php');
?>
<form method='post' target='_blank' action='data/report/lap_akun_buku_besar.php' id="form_input" style="width:400px">
    <div style="padding:5px;border:1px solid #ddd;">
        <a id="btn_print" href="#" class="easyui-linkbutton" data-options="iconCls:'icon-print', plain:true">Tampilkan Data</a>
		<a id="btn_export_excel" href="#" class="easyui-linkbutton" data-options="iconCls:'icon-excel', plain:true">Export</a>
    </div>
    <table>
		<tr>
            <td align="right" id="label_laporan">Lokasi :</td>
            <td><input id="txt_lokasi" name="txt_lokasi" style="width:100px"/></td>
        </tr>
        <tr>
            <td align="right" id="label_laporan">Akun :</td>
            <td><input id="txt_kodeperkiraan" name="txt_kodeperkiraan[]" style="width:250px"/></td>
        </tr>
        <tr>
            <td align="right" id="label_laporan">Periode :</td>
            <td id="label_laporan">
				<input id="txt_tgl_aw" name="txt_tgl_aw" class="date"/>
				&nbsp; s/d &nbsp;
				<input id="txt_tgl_ak" name="txt_tgl_ak" class="date"/>
			</td>
        </tr>
    </table>
	
	<input type="hidden" name="excel" id="excel">
	<input type="hidden" name="file_name" id="file_name" value="LaporanBukuBesar">
</form>
<script>
$(document).ready(function(){
	browse_data_lokasi('#txt_lokasi', 'lokasi&status=all','KODE');
    browse_data_perkiraan('#txt_kodeperkiraan', 'kode_perkiraan&status=all');
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
		multiple: false,
		mode:'local',
		sortName:sort,
		sortOrder:'asc',
		/*rowStyler: function(index,row){  
			if (row.STATUS == 0){  
				return 'background-color:#A8AEA6';  
			}  
		},*/
		columns:[[
			//{field:'ck',checkbox:true},
			{field:'KODE',title:'Kode',width:80, sortable:true},
			{field:'NAMA',title:'Nama',width:240, sortable:true},
		]]
	});
}

function browse_data_perkiraan(id, table) {
	$(id).combogrid({
		panelWidth:400,
		url: 'config/combogrid.php?table='+table+'&jenis=detail',
		idField:'KODE',
		textField:'KODE',
		multiple: true,
		mode:'remote',
		sortName:'KODE',
		sortOrder:'asc',
		rowStyler: function(index,row){  
			if (row.STATUS == 0){  
				return 'background-color:#A8AEA6';  
			}  
		},
		columns:[[
			{field:'ck',checkbox:true},
			{field:'KODE',title:'Akun',width:80, sortable:true},
			{field:'NAMA',title:'Nama Akun',width:240, sortable:true},
		]]
	});
}

</script>