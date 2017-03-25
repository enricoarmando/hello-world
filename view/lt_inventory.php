<?php
session_start();
if (empty($_SESSION['user'])) die('restart browser');
?>

<form method='post' target='_blank' action='data/report/lap_transaksi_inventory.php' id="form_input" style="width:680px">
    <div style="padding:5px;border:1px solid #ddd;">
        <a id="btn_print" href="#" class="easyui-linkbutton" data-options="iconCls:'icon-print', plain:true">Print</a>
		<a id="btn_export_excel" href="#" class="easyui-linkbutton" data-options="iconCls:'icon-excel', plain:true">Export</a>
    </div>

	<table style="border-bottom:1px #000">
		<tr>
            <td align="right" id="label_laporan">Lokasi :</td>
            <td><input id="txt_lokasi" name="txt_lokasi" style="width:150px"/></td>
        </tr>
		<tr>
            <td align="right" id="label_laporan">Gudang :</td>
            <td><input id="txt_gudang" name="txt_gudang" style="width:150px"/></td>
        </tr>
        <tr>
            <td align="right" id="label_laporan">Tanggal :</td>
            <td id="label_laporan"><input id="txt_tgl_aw" name="txt_tgl_aw" class="date"/> s/d <input id="txt_tgl_ak" name="txt_tgl_ak" class="date"/></td>
        </tr>
		<tr>
			<td align="right" id="label_laporan">Barang :</td>
			<td id="label_laporan">
				<input id="txt_namabarang" name="txt_namabarang" class="label_input" style="width:250px" prompt="nama barang">
			    <input id="txt_kodebarang" name="txt_kodebarang" class="label_input" style="width:100px" prompt="kode barang">
			</td>
        </tr>
		<tr hidden>
			<td align="right"><label id="label_laporan"><input type="radio" name="rd_barang" value="1" checked> Barang :</label></td>
			<td id="label_laporan"><input id="txt_barang_awal_list" name="txt_barang_awal_list" style="width:250px"/> List Filter <input id="txt_barang_akhir_list" name="txt_barang_akhir_list[]" style="width:250px"/> <a id="" href="#" class="easyui-linkbutton" data-options="iconCls:'icon-remove', plain:true" onclick="clear_data('#txt_barang_akhir_list')"></a></td>
        </tr>
		<tr hidden>
			<td align="right"><label id="label_laporan"><input type="radio" name="rd_barang" value="2"> Nama Barang :</label></td>
			<td id="label_laporan"><input id="txt_barang_awal" name="txt_barang_awal" style="width:250px"/> s/d <input id="txt_barang_akhir" name="txt_barang_akhir" style="width:250px"/></td>
        </tr>
    </table>
	 
	<input type="hidden" name="excel" id="excel">
	<input type="hidden" name="file_name" id="file_name" value="LaporanInventory">
</form>
<script>
$(document).ready(function(){
	browse_data_lokasi('#txt_lokasi', '<?=$_SESSION['LOKASIPUSAT']==1 ? 'semua_lokasi' : 'lokasi'?>','KODE');
	browse_data_gudang('#txt_gudang', '');
	browse_data_barang('#txt_barang_awal_list', 'barang&status=all','NAMA');
	browse_data_barang('#txt_barang_awal', 'barang&status=all','NAMA');
	browse_data_barang('#txt_barang_akhir', 'barang&status=all','NAMA');
	
	$("#cbSupplier").prop("disabled",false).prop('checked',false);
	
	$('#txt_barang_akhir_list').combogrid({
		panelWidth:450,
		idField:'KODE',
		textField:'',
		mode:'local',
		multiple:true,
		rowStyler: function(index,row){  
			if (row.STATUS == 0){  
				return 'background-color:#A8AEA6';  
			}  
		},
		columns:[[
			{field:'KODE',title:'Kode',width:80, sortable:true},
			{field:'NAMA',title:'Nama',width:240, sortable:true},
			{field:'SATUAN',title:'Satuan',width:100, sortable:false},
		]],
		data:[],
		onSelect:function(){
			$(this).combogrid('textbox').val('');
		}
	});
	
	$('#txt_barang_awal').combogrid('disable');
	$('#txt_barang_akhir').combogrid('disable');
});

$("[name=rd_barang]").change(function(){
	if ($(this).val()==1) {
		$('#txt_barang_awal').combogrid('disable').combogrid('clear');
		$('#txt_barang_akhir').combogrid('disable').combogrid('clear');
		$('#txt_barang_awal_list').combogrid('enable').combogrid('clear');
		$('#txt_barang_akhir_list').combogrid('enable').combogrid('clear').combogrid('grid').datagrid('loadData', []);
	} else {
		$('#txt_barang_awal').combogrid('enable').combogrid('clear');
		$('#txt_barang_akhir').combogrid('enable').combogrid('clear');
		$('#txt_barang_awal_list').combogrid('disable').combogrid('clear');
		$('#txt_barang_akhir_list').combogrid('disable').combogrid('clear').combogrid('grid').datagrid('loadData', []);
	}
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

function browse_data_barang(id, table, sort) {
	$(id).combogrid({
		panelWidth:450,
		url: 'config/combogrid.php?table='+table,
		idField:'NAMA',
		textField:'NAMA',
		mode:'remote',
		sortName:sort,
		sortOrder:'asc',
		pageSize:20,
		view:bufferview,
		rowStyler: function(index,row){  
			if (row.STATUS == 0){  
				return 'background-color:#A8AEA6';  
			}  
		},
		columns:[[
			{field:'KODE',title:'Kode',width:80, sortable:true},
			{field:'NAMA',title:'Nama',width:250,hidden:true},
			{field:'NAMA2',title:'Nama',width:250,formatter:function(val, row){
				var nama = typeof row.NAMA !== 'undefined' ? row.NAMA : '';
				var tipe = typeof row.TIPE !== 'undefined' ? row.TIPE : '';
				var jenisframe = typeof row.JENISFRAME !== 'undefined' ? row.JENISFRAME : '';
				return nama + ' ' + tipe + jenisframe;
			}},
			{field:'TIPE',title:'Tipe',width:80,hidden:true},
			{field:'SATUAN',title:'Satuan',width:100, sortable:false},
		]],
		onSelect:function(index, data){
			if (id=='#txt_barang_awal_list') {
				var dg = $('#txt_barang_akhir_list').combogrid('grid');
				var rows = dg.datagrid('getRows');
				var insert = true;
				jQuery.each(rows, function() {
					if (this.KODE==data.KODE) {
						insert = false;
					}
				});
				if (insert) {
					dg.datagrid('insertRow',{
						row:data
					});
				}
				$('#txt_barang_akhir_list').combogrid('showPanel').combogrid('grid').datagrid('selectAll');
				$('#txt_barang_akhir_list').combogrid('textbox').val('');
				$('#txt_barang_awal_list').combogrid('clear');
			}
		}
	});
}
function browse_data_lokasi(id, table, sort) {
	$(id).combogrid({
		panelWidth:300,
		url: 'config/combogrid.php?table='+table,
		idField:'KODE',
		textField:'KODE',
		mode:'local',
		sortName:sort,
		sortOrder:'asc',
		multiple:false,
		rowStyler: function(index,row){
			if (row.STATUS == 0) {
				return 'background-color:#A8AEA6';
			}
		},
		columns:[[
			//{field:'ck',checkbox:true},
			{field:'KODE',title:'Kode',width:80, sortable:true},
			{field:'NAMA',title:'Nama',width:180, sortable:true},
		]],
		onChange:function(newVal, oldVal){
			var row = $(this).combogrid('grid').datagrid('getSelected');
			if (row) {
				if ($('#mode').val()!='') {
					ubah_url_combogrid ($('#txt_gudang'), 'config/combogrid.php?table=gudang&kodelokasi='+row.KODE, true);
				}
			} else {
				$('#txt_kodelokasi').textbox('clear');
				ubah_url_combogrid ($('#txt_gudang'), 'config/combogrid.php?table=gudang&kodelokasi=', true);
			}
		}
	});
}
function browse_data_gudang(id, table, sort) {
	$(id).combogrid({
		panelWidth:300,
		url: 'config/combogrid.php?table='+table,
		idField:'KODE',
		textField:'KODE',
		mode:'local',
		sortName:sort,
		sortOrder:'asc',
		multiple:false,
		rowStyler: function(index,row){
			if (row.STATUS == 0) {
				return 'background-color:#A8AEA6';
			}
		},
		columns:[[
			//{field:'ck',checkbox:true},
			{field:'KODE',title:'Kode',width:80, sortable:true},
			{field:'NAMA',title:'Nama',width:180, sortable:true},
		]],
	});
}

</script>