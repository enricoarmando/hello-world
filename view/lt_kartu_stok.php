<?php
session_start();
if (empty($_SESSION['user'])) die(json_encode(array('errorMsg' => 'Expired Session <br> Please Relogin')));
?>

<form method='post' target='_blank' action='data/report/lap_kartu_stok.php' id="form_input" style="width:750px">
    <div style="padding:5px;border:1px solid #ddd;">
        <a id="btn_print" href="#" class="easyui-linkbutton" data-options="iconCls:'icon-print', plain:true">Tampilkan Data</a>
		<a id="btn_export_excel" href="#" class="easyui-linkbutton" data-options="iconCls:'icon-excel', plain:true">Export</a>
    </div>

	<table id="form_input" style="border-bottom:1px #000">
        <tr>
            <td align="right" id="label_laporan">Tgl. Transaksi :</td>
            <td id="label_laporan">
				<input id="txt_tgl_aw" name="txt_tgl_aw" class="date"/> - <input id="txt_tgl_ak" name="txt_tgl_ak" class="date"/>
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
			<td align="right" id="label_laporan">Item Category :</td>
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
			<td align="right"><label  id="label_laporan"><input type="radio" name="rd_barang" value="2"> Barang :</label></td>
			<td id="label_laporan"><input id="txt_barang_awal" name="txt_barang_awal" style="width:250px"/> - <input id="txt_barang_akhir" name="txt_barang_akhir" style="width:250px"/></td>
        </tr>
        <tr hidden>
			<td align="right"><label id="label_laporan"><input type="radio" name="rd_barang" value="1" checked> Barang :</label></td>
			<td id="label_laporan"><input id="txt_barang_awal_list" name="txt_barang_awal_list" style="width:250px"/> Daftar Filter <input id="txt_barang_akhir_list" name="txt_barang_akhir_list[]" style="width:250px"/> <a id="" href="#" class="easyui-linkbutton" data-options="iconCls:'icon-remove', plain:true" onclick="clear_data('#txt_barang_akhir_list')"></a></td>
        </tr>
		<tr <?=$_SESSION['LOKASIPUSAT']==0 ? 'hidden' : ''?>>
			<td></td>
			<td align="left">
				<label id="label_laporan"><input type="checkbox" name="cbBarangAktif" id="cbBarangAktif" value="1"> Tampilkan Barang Yang Aktif</label>
				<label id="label_laporan"><input type="checkbox" name="cb_filter_barang" id="cb_filter_barang" value="1" /> Tampilkan Barang Yang Bertransaksi Pada Transaksi &nbsp; <input id="txt_tgl_transaksi" name="txt_tgl_transaksi" class="date"/></label>
			</td>
        </tr>
    </table>

<!--    <fieldset id="field">
		<legend id="label_laporan">Jenis Laporan</legend>
        <table width="100%">
			<tr>
				<td><label id="label_laporan"><input type="radio" name="rdJenis" value="RekapByBarang" checked="checked" /> Group Berdasarkan Barang</label></td>
				<td><label id="label_laporan"><input type="radio" name="rdJenis" value="RekapByGudang"/> Group Berdasarkan Gudang</label></td>
			</tr>
        </table>
    </fieldset>
!-->
	<input type="hidden" name="excel" id="excel">
	<input type="hidden" name="file_name" id="file_name" value="LaporanKartuStok">
</form>
<script>
$(document).ready(function(){
	browse_data_lokasi('#txt_lokasi', '<?=$_SESSION['LOKASIPUSAT']==1 ? 'semua_lokasi' : 'lokasi'?>','KODE');
	browse_data_gudang('#txt_gudang', 'gudang','KODE');
	browse_data_barang('#txt_barang_awal_list', 'barang','NAMA');
	browse_data_barang('#txt_barang_awal', 'barang','NAMA');
	browse_data_barang('#txt_barang_akhir', 'barang','NAMA');
	
	if ('<?=$_SESSION['LOKASIPUSAT']?>' == '0') {
		browse_data_barang('#txt_namabarang', 'barang','KODE');
	}
	//browse_data_barang('#txt_barang_akhir_list', 'barang&status=all','NAMA');
	//browse_data_kategori_barang('#txt_kategori_barang_awal', 'kategori_barang&status=all','NAMA');
	//browse_data_kategori_barang('#txt_kategori_barang_akhir', 'kategori_barang&status=all','NAMA');
	$('#txt_tgl_transaksi').datebox('disable');
	$('#cb_filter_barang').change(function(){
		$('#txt_tgl_aw, #txt_tgl_ak').datebox('setValue', date_format());

		if ($(this).prop('checked')) {
			$('#txt_tgl_transaksi').datebox('enable');
		} else {
			$('#txt_tgl_transaksi').datebox('disable');
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

	$('#txt_barang_awal, #txt_barang_akhir').combogrid('disable');
});

$("[name=rd_barang]").change(function(){
	var cg = $(this).val()==1 ? 'disable' : 'enable';
	var list = $(this).val()==2 ? 'disable' : 'enable';

	$('#txt_barang_awal, #txt_barang_akhir').combogrid(cg).combogrid('clear');
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
			{field:'KODE',title:'ID',width:80, sortable:true},
			{field:'NAMA',title:'Description',width:150, sortable:true},
		]]
	});
}
</script>