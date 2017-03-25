<?php
session_start();
if (empty($_SESSION['user'])) header('Location: ../../../index.php');
?>
<form method='post' target='_blank' action='data/report/lap_posisi_stok.php' id="form_input" style="width:720px">
    <div style="padding:5px;border:1px solid #ddd;">
        <a id="btn_print" href="#" class="easyui-linkbutton" data-options="iconCls:'icon-print', plain:true">Tampilkan Data</a>
		<a id="btn_export_excel" href="#" class="easyui-linkbutton" data-options="iconCls:'icon-excel', plain:true">Export</a>
    </div>

	<table id="form_input" style="border-bottom:1px #000">
        <tr>
            <td align="right" id="label_laporan">Tgl. Transaksi :</td>
            <td id="label_laporan">
				<input id="txt_tgl_aw" name="txt_tgl_aw" class="date"/>
				<!--<label><input type="checkbox" name="cb_filter_barang" id="cb_filter_barang" value="1" /> Show All Item with Transaction</label>!-->
			</td>
        </tr>
		<tr>
            <td align="right" id="label_laporan">Lokasi :</td>
            <td><input id="txt_lokasi" name="txt_lokasi" style="width:250px"/></td>
        </tr>  
		<tr>
            <td align="right" id="label_laporan">Gudang :</td>
            <td><input id="txt_gudang" name="txt_gudang[]" style="width:250px"/></td>
        </tr>  
        <tr hidden>
			<td id="label_laporan" align="right"><label><input type="checkbox" name="cbSupplier" id="cbSupplier" value="1">Supplier :</label></td>
			<td id="label_laporan"><input id="txt_supplier_awal_list" name="txt_supplier_awal_list" style="width:250px"/> Daftar Filter <input id="txt_supplier_akhir_list" name="txt_supplier_akhir_list[]" style="width:250px"/> <a id="" href="#" class="easyui-linkbutton" data-options="iconCls:'icon-remove', plain:true" onclick="clear_data('#txt_supplier_akhir_list')"></a></td>
        </tr>
        <tr hidden>
			<td align="right" id="label_laporan">Kategori Buku :</td>
			<td id="label_laporan">
				<input id="txt_kategori_barang_awal" name="txt_kategori_barang_awal" style="width:250px"/>
				-
				<input id="txt_kategori_barang_akhir" name="txt_kategori_barang_akhir" style="width:250px"/>
			</td>
        </tr>
		<tr>
			<td align="right" id="label_laporan">Barang :</td>
			<td id="label_laporan">
				<input id="txt_namabarang" name="txt_namabarang" class="label_input" style="width:250px" prompt="nama barang">
			    <span <?=$_SESSION['LOKASIPUSAT']==0 ? 'hidden' : ''?>><input id="txt_kodebarang" name="txt_kodebarang" class="label_input" style="width:100px" prompt="kode barang"></span>
			</td>
        </tr>
        <tr hidden>
			<td align="right" id="label_laporan"><input type="radio" name="rd_barang" value="1" checked> Barang :</td>
			<td id="label_laporan"><input id="txt_barang_awal_list" name="txt_barang_awal_list" style="width:250px"/> Daftar Filter <input id="txt_barang_akhir_list" name="txt_barang_akhir_list[]" style="width:250px"/> <a id="" href="#" class="easyui-linkbutton" data-options="iconCls:'icon-remove', plain:true" onclick="clear_data('#txt_barang_akhir_list')"></a></td>
        </tr>
		<tr hidden>
			<td align="right" id="label_laporan"><input type="radio" name="rd_barang" value="2"> Barang :</td>
			<td id="label_laporan"><input id="txt_barang_awal" name="txt_barang_awal" style="width:250px"/> - <input id="txt_barang_akhir" name="txt_barang_akhir" style="width:250px"/></td>
        </tr>
		<tr <?=$_SESSION['LOKASIPUSAT']==0 ? 'hidden' : ''?>>
			<td id="label_laporan"></td>
			<td id="label_laporan" align="left">
				<input type="checkbox" name="cbBarangAktif" id="cbBarangAktif" value="1"> Tampilkan Barang Yang Aktif Saja
			</td>
        </tr>
		<tr <?=$_SESSION['LOKASIPUSAT']==0 ? 'hidden' : ''?>>
			<td id="label_laporan"></td>
			<td id="label_laporan" align="left">
				<label id="label_laporan"><input type="checkbox" name="cb_filter_barang" id="cb_filter_barang" value="1" /> Tampilkan Barang Yang Bertransaksi Pada Transaksi &nbsp; <input id="txt_tgl_transaksi" name="txt_tgl_transaksi" class="date"/></label>
			</td>
        </tr>
    </table>
    
    <fieldset id="field">
	<legend id="label_laporan">Jenis Laporan</legend>
        <table width="100%">
			<tr>
				<td id="label_laporan"><input type="radio" name="rdTampil" value="PosisiStok" checked="checked" /> Posisi Stok Rekap Gudang</td>
				<td id="label_laporan"><input type="radio" name="rdTampil" value="DetailPosisiStok" /> Posisi Stok Per Gudang</td>
				<td id="label_laporan"><input type="radio" name="rdTampil" value="DetailPosisiStokLokasi" /> Posisi Stok Online</td>
			</tr>
        </table>
    </fieldset>
	
	<input type="hidden" name="excel" id="excel">
	<input type="hidden" name="file_name" id="file_name" value="LaporanPosisiStok">
</form>
<script>
$(document).ready(function(){
	browse_data_lokasi('#txt_lokasi', '<?=$_SESSION['LOKASIPUSAT']==1 ? 'semua_lokasi' : 'lokasi'?>','KODE');
	browse_data_gudang('#txt_gudang', 'gudang','KODE');
	browse_data_supplier('#txt_supplier_awal_list', 'supplier&status=all','NAMA');
	
	// jika browse barang ingin difilter berdasarkan jenis barang maka tambahkan sintak jenis[] di tablenya
	// akan tetapi jika barang yang ingin tampil ialah semua jenis, maka sintak jenis[] tidak usah ditambah
	browse_data_barang('#txt_barang_awal_list', 'barang','KODE');
	browse_data_barang('#txt_barang_awal', 'barang','KODE');
	browse_data_barang('#txt_barang_akhir', 'barang','KODE');
	//browse_data_barang('#txt_barang_akhir_list', 'barang&status=all','NAMA');
	browse_data_kategori_barang('#txt_kategori_barang_awal', 'kategori_barang&status=all','NAMA');
	browse_data_kategori_barang('#txt_kategori_barang_akhir', 'kategori_barang&status=all','NAMA');
	
	if ('<?=$_SESSION['LOKASIPUSAT']?>' == '0') {
		browse_data_barang('#txt_namabarang', 'barang','KODE');
	}
	
	$('#txt_tgl_transaksi').datebox('disable');
	$('#cb_filter_barang').change(function(){
		$('#txt_tgl_aw').datebox('setValue', date_format());

		if ($(this).prop('checked')) {
			$('#txt_tgl_transaksi').datebox('enable');
		} else {
			$('#txt_tgl_transaksi').datebox('disable');
		}
	});
	
	$("#cbSupplier").prop("disabled",false).prop('checked',false);
	$('#txt_supplier_akhir_list').combogrid({
		panelWidth:730,
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
			{field:'NAMA',title:'Nama Barang',width:240, sortable:true},
			{field:'TIPE',title:'TIPE',width:60, sortable:true},
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
	
	$('#txt_supplier_awal_list, #txt_supplier_akhir_list, #txt_barang_awal, #txt_barang_akhir').combogrid('disable');
});

$("[name=rdTampil]").change(function(){	
	if ($(this).val()=='DetailPosisiStokLokasi') {
		$('#txt_lokasi').combogrid('disable').combogrid('clear');
		$('#txt_gudang').combogrid('disable').combogrid('clear');
	}else{
		$('#txt_lokasi').combogrid('enable').combogrid('clear');
		$('#txt_gudang').combogrid('enable').combogrid('clear');
	}
});

$("[name=rd_barang]").change(function(){
	var cg = $(this).val()==1 ? 'disable' : 'enable';
	var list = $(this).val()==2 ? 'disable' : 'enable';
	
	$('#txt_barang_awal, #txt_barang_akhir').combogrid(cg).combogrid('clear');
	$('#txt_barang_awal_list, #txt_barang_akhir_list').combogrid(list).combogrid('clear');
	
	$('#txt_barang_akhir_list').combogrid('grid').datagrid('loadData', []);
});

$("#cbSupplier").change(function(){
	var cg = $(this).val()==true ? 'enable' : 'disable';
	
	$('#txt_supplier_awal_list, #txt_supplier_akhir_list').combogrid(cg).combogrid('clear');
	
	$('#txt_supplier_akhir_list').combogrid('grid').datagrid('loadData', []);
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
		multiple: false,
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
			//{field:'ck',checkbox:true},
			{field:'KODE',title:'Kode',width:80, sortable:false},
			{field:'NAMA',title:'Nama',width:180, sortable:false},
		]],
		onChange:function(newVal, oldVal){
			var row = $(this).combogrid('grid').datagrid('getSelected');
			if (row) {
				ubah_url_combogrid ($('#txt_gudang'), 'config/combogrid.php?table=gudang&kodelokasi='+row.KODE, true);
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
		multiple:true,
		rowStyler: function(index,row){  
			if (row.STATUS == 0) {
				return 'background-color:#A8AEA6';  
			}  
		},
		columns:[[
			{field:'ck',checkbox:true},
			{field:'KODE',title:'Kode',width:60, sortable:true},
			{field:'NAMA',title:'Nama',width:200, sortable:true},
		]],
	});
}

function browse_data_supplier(id, table, sort) {
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
			{field:'KODE',title:'Kode',width:80, sortable:false},
			{field:'NAMA',title:'Nama',width:240, sortable:false},
			{field:'ALAMAT',title:'Alamat',width:240, sortable:false},
			{field:'KOTA',title:'Kota',width:240, sortable:false}
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