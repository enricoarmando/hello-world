<?php
session_start();
if (empty($_SESSION['user'])) die('restart browser');
?>
<form method='post' target='_blank' action='data/report/lap_transaksi_purchase_order.php' id="form_input" style="width:720px">
    <div style="padding:5px;border:1px solid #ddd;">
        <a id="btn_print" href="#" class="easyui-linkbutton" data-options="iconCls:'icon-print', plain:true">Tampilkan Data</a>
		<a id="btn_export_excel" href="#" class="easyui-linkbutton" data-options="iconCls:'icon-excel', plain:true">Export</a>
    </div>

	<table style="border-bottom:1px #000">
		<tr>
            <td align="right" id="label_laporan">Lokasi :</td>
            <td><input id="txt_lokasi" name="txt_lokasi[]" style="width:250px"/></td>
        </tr>
        <tr>
            <td align="right" id="label_laporan">Tgl. Transaksi :</td>
            <td id="label_laporan"><input id="txt_tgl_aw" name="txt_tgl_aw" class="date"/> - <input id="txt_tgl_ak" name="txt_tgl_ak" class="date"/></td>
        </tr>
		<tr>
			<td align="right"><label id="label_laporan"><input type="radio" name="rd_supp" value="1" checked> Supplier :</label></td>
			<td id="label_laporan"><input class="label_input" id="txt_namasupplier" name="txt_namasupplier" style="width:250px"></td>
        </tr>
        <tr>
			<td align="right"><label id="label_laporan"><input type="radio" name="rd_supp" value="2"> Supplier :</label></td>
			<td id="label_laporan"><input id="txt_supplier_awal_list" name="txt_supplier_awal_list" style="width:250px"/> List Filter <input id="txt_supplier_akhir_list" name="txt_supplier_akhir_list[]" style="width:250px"/> <a id="" href="#" class="easyui-linkbutton" data-options="iconCls:'icon-remove', plain:true" onclick="clear_data('#txt_supplier_akhir_list')"></a></td>
        </tr>
		<tr hidden>
			<td align="right"><label id="label_laporan"><input type="radio" name="rd_supp" value="2"> Supplier :</label></td>
			<td id="label_laporan"><input id="txt_supplier_awal" name="txt_supplier_awal" style="width:250px"/> - <input id="txt_supplier_akhir" name="txt_supplier_akhir" style="width:250px"/></td>
        </tr>
        <tr hidden>
			<td align="right" id="label_laporan">Items Category :</td>
			<td id="label_laporan">
				<input id="txt_kategori_barang_awal" name="txt_kategori_barang_awal" style="width:250px"/>
				-
				<input id="txt_kategori_barang_akhir" name="txt_kategori_barang_akhir" style="width:250px"/>
			</td>
        </tr>
		<tr>
			<td align="right" id="label_laporan">Barang :</td>
			<td id="label_laporan"><input id="txt_namabarang" name="txt_namabarang" class="label_input" style="width:250px" prompt="nama barang">
			                       <input id="txt_kodebarang" name="txt_kodebarang" class="label_input" style="width:100px" prompt="kode barang">
			</td>
        </tr>
        <tr hidden>
			<td align="right"><label id="label_laporan"><input type="radio" name="rd_barang" value="1" checked> Barang :</label></td>
			<td id="label_laporan"><input id="txt_barang_awal_list" name="txt_barang_awal_list" style="width:250px"/> List Filter <input id="txt_barang_akhir_list" name="txt_barang_akhir_list[]" style="width:250px"/> <a id="" href="#" class="easyui-linkbutton" data-options="iconCls:'icon-remove', plain:true" onclick="clear_data('#txt_barang_akhir_list')"></a></td>
        </tr>
		<tr hidden>
			<td align="right"><label id="label_laporan"><input type="radio" name="rd_barang" value="2"> Barang :</label></td>
			<td id="label_laporan"><input id="txt_barang_awal" name="txt_barang_awal" style="width:250px"/> - <input id="txt_barang_akhir" name="txt_barang_akhir" style="width:250px"/></td>
        </tr>
    </table>
	<fieldset id="field">
		<legend id="label_laporan">Status</legend>
		<table width="100%" id="label_laporan">
			<tr>
				<td width="25%"><label id="label_laporan"><input type="checkbox" name="cbStatus[]" value="I" checked="checked"/> Input</label></td>
				<td width="25%"><label id="label_laporan"><input type="checkbox" name="cbStatus[]" value="S" checked="checked"/> Slip</label></td>
				<td width="25%"><label id="label_laporan"><input type="checkbox" name="cbStatus[]" value="P" checked="checked"/> Posting</label></td>
				<td width="25%"><label id="label_laporan"><input type="checkbox" name="cbStatus[]" value="D" /> Delete</label></td>
			</tr>
		</table>
	</fieldset>

	<fieldset id="field">
	<legend id="label_laporan">Jenis Laporan</legend>
        <table width="100%" border="0">
           	<tr>
               	<td><label id="label_laporan"><input type="radio" name="rdTampil" value="Register" checked="checked"> Register</label></td>
				<td><label id="label_laporan"><input type="radio" name="rdTampil" value="RekapByFaktur" /> Rekap Berdasarkan No. Faktur</label></td>
        	</tr>
           	<tr>
               	<td><label id="label_laporan"><input type="radio" name="rdTampil" value="RegisterBySupplier"> Register Berdasarkan Supplier</label></td>
				<td><label id="label_laporan"><input type="radio" name="rdTampil" value="RekapBySupplier" /> Rekap Berdasarkan Supplier</label></td>
			</tr>
           	<tr>
               	<td><label id="label_laporan"><input type="radio" name="rdTampil" value="RegisterByBarang"> Register Berdasarkan Barang</label></td>
				<td>&nbsp;</td>
			</tr>
		</table>
    </fieldset>

	<input type="hidden" name="excel" id="excel">
	<input type="hidden" name="file_name" id="file_name" value="LaporanPurchaseOrder">
</form>
<script>
$(document).ready(function(){
	browse_data_lokasi('#txt_lokasi', 'lokasi', 'KODE');
	browse_data_supplier('#txt_supplier_awal_list', 'supplier&status=all','NAMA');
	browse_data_supplier('#txt_supplier_awal', 'supplier&status=all','NAMA');
	browse_data_supplier('#txt_supplier_akhir', 'supplier&status=all','NAMA');

	browse_data_barang('#txt_barang_awal_list', 'barang','KODE');
	browse_data_barang('#txt_barang_awal', 'barang','KODE');
	browse_data_barang('#txt_barang_akhir', 'barang','KODE');

	browse_data_kategori_barang('#txt_kategori_barang_awal', 'kategori_barang&status=all','KODE');
	browse_data_kategori_barang('#txt_kategori_barang_akhir', 'kategori_barang&status=all','KODE');

	$('#txt_barang_akhir_list').combogrid({
		panelWidth:740,
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
		data:[],
		onSelect:function(){
			$(this).combogrid('textbox').val('');
		}
	});

	$('#txt_supplier_akhir_list').combogrid({
		panelWidth:730,
		idField:'KODE',
		mode:'local',
		multiple:true,
		columns:[[
			{field:'KODE',title:'Kode',width:150, sortable:true},
			{field:'NAMA',title:'Nama',width:240, sortable:true},
			{field:'ALAMAT',title:'Alamat',width:240, sortable:true},
			{field:'KOTA',title:'Kota',width:80, sortable:true},
		]],
		data:[],
		onSelect:function(){
			$(this).combogrid('textbox').val('');
		}
	});

	$('#txt_supplier_awal_list, #txt_supplier_akhir_list, #txt_barang_awal, #txt_barang_akhir').combogrid('disable');
});

$("[name=rdTampil]").change(function(){
	var sd = 'disable';
	var list = sd;
	if ($(this).val().substr(0, 5)!='Rekap') {
		var sd = $("[name=rd_barang]:checked").val()==1 ? 'disable' : 'enable';
		var list = $("[name=rd_barang]:checked").val()==1 ? 'enable' : 'disable';
	}

	$('#txt_barang_awal, #txt_barang_akhir').combogrid(sd).combogrid('clear');
	$('#txt_barang_awal_list, #txt_barang_akhir_list').combogrid(list).combogrid('clear');

	$('#txt_barang_akhir_list').combogrid('grid').datagrid('loadData', []);
});

$("[name=rd_supp]").change(function(){
	var cg = $(this).val()==1 ? 'enable' : 'disable';
	var list = $(this).val()==2 ? 'enable' : 'disable';

	//$('#txt_supplier_awal, #txt_supplier_akhir').combogrid(cg).combogrid('clear');
	$('#txt_namasupplier').textbox(cg).textbox('clear');
	$('#txt_supplier_awal_list, #txt_supplier_akhir_list').combogrid(list).combogrid('clear');

	$('#txt_supplier_akhir_list').combogrid('grid').datagrid('loadData', []);
});

$("[name=rd_barang]").change(function(){
	var sd = 'disable';
	var list = sd;
	if ($("[name=rdTampil]:checked").val().substr(0, 5)!='Rekap') {
		var sd = $(this).val()==1 ? 'disable' : 'enable';
		var list = $(this).val()==1 ? 'enable' : 'disable';
	}

	$('#txt_barang_awal, #txt_barang_akhir').combogrid(sd).combogrid('clear');
	$('#txt_barang_awal_list, #txt_barang_akhir_list').combogrid(list).combogrid('clear');

	$('#txt_barang_akhir_list').combogrid('grid').datagrid('loadData', []);
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
		mode:'local',
		sortName:sort,
		sortOrder:'asc',
		multiple:true,
		rowStyler: function(index,row){
			if (row.STATUS == 0){
				return 'background-color:#A8AEA6';
			}
		},
		columns:[[
			{field:'ck',checkbox:true},
			{field:'KODE',title:'Kode',width:80, sortable:true},
			{field:'NAMA',title:'Nama',width:240, sortable:true},
		]]
	});
}

function browse_data_supplier(id, table, sort) {
	$(id).combogrid({
		panelWidth:730,
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
			{field:'KODE',title:'Kode',width:150, sortable:true},
			{field:'NAMA',title:'Nama',width:240, sortable:true},
			{field:'ALAMAT',title:'Alamat',width:240, sortable:true},
			{field:'KOTA',title:'Kota',width:80, sortable:true},
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
					dg.datagrid('insertRow',{
						row:data
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
			{field:'KODE',title:'ID',width:80, sortable:true},
			{field:'NAMA',title:'Description',width:150, sortable:true},
		]]
	});
}
</script>