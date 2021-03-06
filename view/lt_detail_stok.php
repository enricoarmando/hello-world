<?php
session_start();
if (empty($_SESSION[user])) die('restart browser');
?>

<form method='post' target='_blank' action='data/report/lap_detail_stok.php' id="form_input" style="width:720px">
    <div style="padding:5px;border:1px solid #ddd;">
        <a id="btn_print" href="#" class="easyui-linkbutton" data-options="iconCls:'icon-print', plain:true">Cetak</a>
		<a id="btn_export_excel" href="#" class="easyui-linkbutton" data-options="iconCls:'icon-excel', plain:true">Export</a>
		<!--<a id="btn_help" href="#" class="easyui-linkbutton" data-options="iconCls:'icon-help', plain:true">Panduan</a>!-->
    </div>

	<table id="form_input" style="border-bottom:1px #000">
        <tr>
            <td align="right" id="label_laporan">Tanggal :</td>
            <td id="label_laporan">
				<input id="txt_tgl_aw" name="txt_tgl_aw" class="date"/> s/d <input id="txt_tgl_ak" name="txt_tgl_ak" class="date"/>				
			</td>
        </tr>
		<tr>
            <td align="right" id="label_laporan">Lokasi :</td>
            <td><input id="txt_lokasi" name="txt_lokasi" style="width:250px"/></td>
        </tr>  
		<tr>
            <td align="right" id="label_laporan">Gudang :</td>
            <td><input id="txt_gudang" name="txt_gudang" style="width:250px"/></td>
        </tr>  
        <tr>
			<td id="label_laporan" align="right"><input type="checkbox" name="cbSupplier" id="cbSupplier" value="1">Supplier :</td>
			<td id="label_laporan"><input id="txt_supplier_awal_list" name="txt_supplier_awal_list" style="width:250px"/> List Filter <input id="txt_supplier_akhir_list" name="txt_supplier_akhir_list[]" style="width:250px"/> <a id="" href="#" class="easyui-linkbutton" data-options="iconCls:'icon-remove', plain:true" onclick="clear_data('#txt_supplier_akhir_list')"></a></td>
        </tr>
		<tr>
			<td align="right" id="label_laporan">Barang :</td>
			<td id="label_laporan"><input id="txt_namabarang" name="txt_namabarang" class="label_input" style="width:250px" prompt="nama barang">
			                       <input id="txt_kodebarang" name="txt_kodebarang" class="label_input" style="width:100px" prompt="kode barang">
			</td>
        </tr>
        <tr hidden>
			<td align="right" id="label_laporan"><input type="radio" name="rd_barang" value="1" checked> Barang :</td>
			<td id="label_laporan"><input id="txt_barang_awal_list" name="txt_barang_awal_list" style="width:250px"/> List Filter <input id="txt_barang_akhir_list" name="txt_barang_akhir_list[]" style="width:250px"/> <a id="" href="#" class="easyui-linkbutton" data-options="iconCls:'icon-remove', plain:true" onclick="clear_data('#txt_barang_akhir_list')"></a></td>
        </tr>
		<tr hidden>
			<td align="right" id="label_laporan"><input type="radio" name="rd_barang" value="2"> Barang :</td>
			<td id="label_laporan"><input id="txt_barang_awal" name="txt_barang_awal" style="width:250px"/> s/d <input id="txt_barang_akhir" name="txt_barang_akhir" style="width:250px"/></td>
        </tr>
    </table>
    
	<input type="hidden" name="excel" id="excel">
	<input type="hidden" name="file_name" id="file_name" value="LaporanDetailStok">
</form>
<script>
$(document).ready(function(){
	browse_data_lokasi('#txt_lokasi', '<?=$_SESSION['LOKASIPUSAT']==1 ? 'semua_lokasi' : 'lokasi'?>','KODE');
	browse_data_supplier('#txt_supplier_awal_list', 'supplier','NAMA');
	browse_data_gudang('#txt_gudang', 'gudang','KODE');
	browse_data_barang('#txt_barang_awal_list', 'barang','NAMA');
	browse_data_barang('#txt_barang_awal', 'barang','NAMA');
	browse_data_barang('#txt_barang_akhir', 'barang','NAMA');
	
	$('#cb_filter_barang').change(function(){
		var prop = $(this).prop('checked') ? 'disable' : 'enable';
		
		$('#txt_tgl_aw').datebox('setValue', '<?=date("d/m/Y")?>');
		$('#txt_tgl_ak').datebox('setValue', '<?=date("d/m/Y")?>').datebox(prop);
		$('#txt_kategori_barang_awal').combogrid('clear').combogrid(prop);
		$('#txt_kategori_barang_akhir').combogrid('clear').combogrid(prop);
		$('#txt_barang_awal').combogrid('clear').combogrid(prop);
		$('#txt_barang_akhir').combogrid('clear').combogrid(prop);
	});
	
	$("#cbSupplier").prop("disabled",false).prop('checked',false);
	$('#txt_supplier_akhir_list').combogrid({
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
			{field:'KODE',title:'Kode',width:80, sortable:false},
			{field:'NAMA',title:'Nama',width:240, sortable:false},
			{field:'ALAMAT',title:'Alamat',width:200, sortable:false},
			{field:'KOTA',title:'Kota',width:100, sortable:false}
		]],
		data:[],
		onSelect:function(){
			$(this).combogrid('textbox').val('');
		}
	});

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
			{field:'KODE',title:'Kode',width:80, sortable:false},
			{field:'NAMA',title:'Nama',width:240, sortable:false},
			{field:'SATUAN',title:'Satuan',width:100, sortable:false}
		]],
		data:[],
		onSelect:function(){
			$(this).combogrid('textbox').val('');
		}
	});
	
	$('#txt_supplier_awal_list').combogrid('disable');
	$('#txt_supplier_akhir_list').combogrid('disable');
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

$("#cbSupplier").change(function(){
	if ($(this).prop('checked')==true) {
		$('#txt_supplier_awal_list').combogrid('enable').combogrid('clear');
		$('#txt_supplier_akhir_list').combogrid('enable').combogrid('clear').combogrid('grid').datagrid('loadData', []);
	} else {
		$('#txt_supplier_awal_list').combogrid('disable').combogrid('clear');
		$('#txt_supplier_akhir_list').combogrid('disable').combogrid('clear').combogrid('grid').datagrid('loadData', []);
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
			}
		}
	});
}

function browse_data_gudang(id, table, sort) {
	$(id).combogrid({
		panelWidth:310,
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
			{field:'KODE',title:'Kode',width:60, sortable:true},
			{field:'NAMA',title:'Nama',width:200, sortable:true},
		]],
	});
}

function browse_data_supplier(id, table, sort) {
	$(id).combogrid({
		panelWidth:700,
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
			{field:'KODE',title:'Kode',width:120, sortable:false},
			{field:'NAMA',title:'Nama',width:300, sortable:false},
			{field:'ALAMAT',title:'Alamat',width:200, sortable:false},
			{field:'KOTA',title:'Kota',width:100, sortable:false}
		]],
		onSelect:function(index, data){
			if (id=='#txt_supplier_awal_list') {
				var dg = $('#txt_supplier_akhir_list').combogrid('grid');
				var rows = dg.datagrid('getRows');
				var insert = true;
				jQuery.each(rows, function() {
					if (this.KODE==data.KODE) {
						insert = false;
					}
				});
				if (insert) {
					dg.datagrid('appendRow',{
						KODE: data.KODE,
						NAMA: data.NAMA,
						ALAMAT: data.ALAMAT,
						KOTA: data.KOTA,
					});
				}
				$('#txt_supplier_akhir_list').combogrid('showPanel').combogrid('grid').datagrid('selectAll');
				$('#txt_supplier_akhir_list').combogrid('textbox').val('');
				$('#txt_supplier_awal_list').combogrid('clear');
			}
		}
	});
}

function browse_data_barang(id, table, sort) {
	$(id).combogrid({
		panelWidth:740,
		url: 'config/combogrid.php?table='+table,
		idField:'KODE',
		textField:'KODE',
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
			{field:'KODE',title:'Kd. Barang',width:120, sortable:true},
			{field:'NAMA',title:'Nama',width:200,hidden:true},
			{field:'NAMA2',title:'Nama Barang',width:200,formatter:function(val, row){
				var nama = typeof row.NAMA !== 'undefined' ? row.NAMA : '';
				var tipe = typeof row.TIPE !== 'undefined' ? row.TIPE : '';
				var jenisframe = typeof row.JENISFRAME !== 'undefined' ? row.JENISFRAME : '';
				return nama + ' ' + tipe + jenisframe;
			}},
			{field:'TIPE',title:'Tipe',width:80,hidden:true},
			{field:'NAMABARANGSUPPLIER',title:'Nama Barang Supplier',width:240, sortable:true},
			{field:'SATUAN',title:'Satuan',width:60, sortable:false, align:'center'},
			//{field:'KONVERSI1',title:'Conv. 1',width:60, sortable:false,formatter:format_amount, align:'right'},
			//{field:'SATUAN2',title:'Unit 2',width:60, sortable:false, align:'center'},
			//{field:'KONVERSI2',title:'Conv. 2',width:60, sortable:false,formatter:format_amount, align:'right'},
			//{field:'SATUAN3',title:'Unit 3',width:60, sortable:false, align:'center'},
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

function browse_data_kategori_barang(id, table, sort) {
	$(id).combogrid({
		panelWidth:260,
		url: 'data_browse.php?act=combogrid&table='+table,
		idField:'KODE',
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
			{field:'KODE',title:'Kode',width:80, sortable:false},
			{field:'NAMA',title:'Nama',width:150, sortable:false},
		]]
	});
}


</script>