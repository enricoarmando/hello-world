<?php
session_start();
if (empty($_SESSION['user'])) header('Location: ../../../index.php');
?>
<form method='post' target='_blank' action='data/report/lap_akun_saldo_awal_perkiraan.php' id="form_input">
    <div style="padding:5px;border:1px solid #ddd;">
        <a id="btn_print" href="#" class="easyui-linkbutton" data-options="iconCls:'icon-print', plain:true">Tampilkan Data</a>
		<a id="btn_export_excel" href="#" class="easyui-linkbutton" data-options="iconCls:'icon-excel', plain:true">Export</a>
    </div>
    <table style="border-bottom:1px #000">
        <tr>
            <td align="right" id="label_laporan">Lokasi :</td>
            <td><input id="txt_lokasi" name="txt_lokasi" style="width:100px"/></td>
        </tr>                    
        <tr>
            <td><div align="right" id="label_laporan">No. Transaksi :</div></td>
            <td><input id="txt_kodetrans" name="txt_kodetrans[]" style="width:300px"/></td>
        </tr>
        <tr>
            <td><div align="right" id="label_laporan">Akun :</div></td>
            <td><input id="txt_kode" name="txt_kode[]" style="width:300px"/></td>
        </tr>                    
    </table>
	
	<input type="hidden" name="excel" id="excel">
	<input type="hidden" name="file_name" id="file_name" value="LaporanSaldoAwalPerkiraan">
</form>
<script>
$(document).ready(function(){
	browse_data_lokasi('#txt_lokasi', 'lokasi','KODE');
	browse_data_kodetrans('#txt_kodetrans', 'kodetrans_saldo_perkiraan');
	browse_data('#txt_kode', 'kode_perkiraan');
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
		panelWidth:380,
		url: 'config/combogrid.php?table='+table,
		idField:'KODE',
		textField:'KODE',
		multiple: false,
		mode:'local',
		sortName:sort,
		sortOrder:'asc',
		rowStyler: function(index,row){  
			if (row.STATUS == 0){  
				return 'background-color:#A8AEA6';  
			}  
		},
		columns:[[
			//{field:'ck',checkbox:true},
			{field:'KODE',title:'Kode',width:80, sortable:true},
			{field:'NAMA',title:'Nama',width:240, sortable:true},
		]]
	});
}

function browse_data_kodetrans(id, table) {
	$(id).combogrid({
		panelWidth:330,
		url: 'config/combogrid.php?table='+table,
		idField:'KODESALDOPERKIRAAN',
		textField:'KODESALDOPERKIRAAN',
		mode:'local',
		multiple: true,
		columns:[[
			{field:'ck',checkbox:true},
			{field:'KODESALDOPERKIRAAN',title:'Trans. ID',width:110},
			{field:'TGLTRANS',title:'Tgl. Transaksi',width:80, align:'center', formatter:ubah_tgl_indo},
			{field:'USERENTRY',title:'User',width:80},
		]]
	});
}

function browse_data(id, table) {
	$(id).combogrid({
		panelWidth:430,
		url: 'config/combogrid.php?table='+table,
		idField:'KODE',
		textField:'KODE',
		mode:'local',
		sortName:'KODE',
		sortOrder:'asc',
		multiple: true,
		columns:[[
			{field:'ck',checkbox:true},
			{field:'KODE',title:'ID',width:70, sortable:true},
			{field:'NAMA',title:'Keterangan',width:300, sortable:true},
		]]
	});
}
</script>