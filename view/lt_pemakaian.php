<?php
session_start();
if (empty($_SESSION[user])) header('Location: ../../../index.php');
?>
<form method='post' target='_blank' action='data/report/lap_transaksi_pemakaian.php' id="form_input" style="width:720px">
    <div style="padding:5px;border:1px solid #ddd;">
        <a id="btn_print" href="#" class="easyui-linkbutton" data-options="iconCls:'icon-print', plain:true">Tampilkan Data</a>
		<a id="btn_export_excel" href="#" class="easyui-linkbutton" data-options="iconCls:'icon-excel', plain:true">Export</a>
    </div>

	<table style="border-bottom:1px #000">
        <tr>
            <td align="right" id="label_laporan">Trans. Tgl. Transaksi :</td>
            <td id="label_laporan"><input id="txt_tgl_aw" name="txt_tgl_aw" class="date"/> - <input id="txt_tgl_ak" name="txt_tgl_ak" class="date"/></td>
        </tr>
		<tr>
            <td align="right" id="label_laporan">Lokasi :</td>
            <td id="label_laporan"><input id="txt_lokasi" name="txt_lokasi[]" style="width:250px"/></td>
        </tr>
        <tr hidden>
			<td align="right" id="label_laporan">Kategori Barang :</td>
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
			<td align="right" id="label_laporan"><label><input type="radio" name="rd_barang" value="1" checked> Barang :</label></td>
			<td id="label_laporan"><input id="txt_barang_awal_list" name="txt_barang_awal_list" style="width:250px"/> Daftar Filter <input id="txt_barang_akhir_list" name="txt_barang_akhir_list[]" style="width:250px"/> <a id="" href="#" class="easyui-linkbutton" data-options="iconCls:'icon-remove', plain:true" onclick="clear_data('#txt_barang_akhir_list')"></a></td>
        </tr>
		<tr hidden>
			<td align="right" id="label_laporan"><label><input type="radio" name="rd_barang" value="2"> Barang :</label></td>
			<td id="label_laporan"><input id="txt_barang_awal" name="txt_barang_awal" style="width:250px"/> - <input id="txt_barang_akhir" name="txt_barang_akhir" style="width:250px"/></td>
        </tr>
    </table>

	<fieldset id="field">
		<legend id="label_laporan">Laporan</legend>
        <table width="100%" id="label_laporan" border="0">
           	<tr>
               	<td width="50%"><label><input type="radio" name="rdTampil" value="Register" checked="checked"> Register</label></td>
				<td><label><input type="radio" name="rdTampil" value="RegisterByBarang"> Register Berdasarkan Barang</label></td>
        	</tr>
		</table>
    </fieldset>

	<input type="hidden" name="excel" id="excel">
	<input type="hidden" name="file_name" id="file_name" value="LaporanPemakaian">
</form>
<script>
$(document).ready(function(){
	browse_data('#txt_lokasi', 'lokasi&status=all','KODE');

	browse_data_barang('#txt_barang_awal_list', 'barang&status=all&jenis[]=RAW MATERIAL&jenis[]=PACKAGING&jenis[]=OTHER ITEM/GOOD&jenis[]=SPARE PART','KODE');
	browse_data_barang('#txt_barang_awal', 'barang&status=all&jenis[]=RAW MATERIAL&jenis[]=PACKAGING&jenis[]=OTHER ITEM/GOOD&jenis[]=SPARE PART','KODE');
	browse_data_barang('#txt_barang_akhir', 'barang&status=all&jenis[]=RAW MATERIAL&jenis[]=PACKAGING&jenis[]=OTHER ITEM/GOOD&jenis[]=SPARE PART','KODE');

	browse_data_kategori_barang('#txt_kategori_barang_awal', 'kategori_barang&status=all','KODE');
	browse_data_kategori_barang('#txt_kategori_barang_akhir', 'kategori_barang&status=all','KODE');

	$('#txt_barang_akhir_list').combogrid({
		panelWidth:425,
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
			{field:'KODE',title:'Kode',width:100, sortable:false},
			{field:'NAMA',title:'Nama',width:250, sortable:false},
			{field:'SATUAN',title:'Satuan',width:50, sortable:false, align:'center'},
			/*{field:'KONVERSI1',title:'Conv. 1',width:60, sortable:false,formatter:format_amount, align:'right'},
			{field:'SATUAN2',title:'Unit 2',width:60, sortable:false, align:'center'},
			{field:'KONVERSI2',title:'Conv. 2',width:60, sortable:false,formatter:format_amount, align:'right'},
			{field:'SATUAN3',title:'Unit 3',width:60, sortable:false, align:'center'},*/
		]],
		data:[],
		onSelect:function(){
			$(this).combogrid('textbox').val('');
		}
	});

	$('#txt_barang_awal, #txt_barang_akhir').combogrid('disable');
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

function browse_data_barang(id, table, sort) {
	$(id).combogrid({
		panelWidth:425,
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
			{field:'KODE',title:'Kode',width:100, sortable:false},
			{field:'NAMA',title:'Nama',width:250,hidden:true},
			{field:'NAMA2',title:'Nama',width:250,formatter:function(val, row){
				var nama = typeof row.NAMA !== 'undefined' ? row.NAMA : '';
				var tipe = typeof row.TIPE !== 'undefined' ? row.TIPE : '';
				var jenisframe = typeof row.JENISFRAME !== 'undefined' ? row.JENISFRAME : '';
				return nama + ' ' + tipe + jenisframe;
			}},
			{field:'TIPE',title:'Tipe',width:80,hidden:true},
			{field:'SATUAN',title:'Satuan',width:50, sortable:false, align:'center'},
			/*{field:'KONVERSI1',title:'Conv. 1',width:60, sortable:false,formatter:format_amount, align:'right'},
			{field:'SATUAN2',title:'Unit 2',width:60, sortable:false, align:'center'},
			{field:'KONVERSI2',title:'Conv. 2',width:60, sortable:false,formatter:format_amount, align:'right'},
			{field:'SATUAN3',title:'Unit 3',width:60, sortable:false, align:'center'},*/
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
			{field:'KODE',title:'ID',width:80, sortable:false},
			{field:'NAMA',title:'Description',width:150, sortable:false},
		]]
	});
}

function browse_data(id, table, sort) {
	$(id).combogrid({
		panelWidth:300,
		url: 'config/combogrid.php?table='+table,
		idField:'NAMA',
		textField:'NAMA',
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
			{field:'KODE',title:'ID',width:80, sortable:false},
			{field:'NAMA',title:'Description',width:150, sortable:false},
		]]
	});
}
</script>