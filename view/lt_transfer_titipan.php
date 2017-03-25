<?php
session_start();
if (empty($_SESSION['user'])) die(json_encode(array('errorMsg' => 'Expired Session <br> Please Relogin')));
?>
<form method='post' target='_blank' action='data/report/lap_transaksi_transfer_titipan.php' id="form_input" style="width:680px">
    <div style="padding:5px;border:1px solid #ddd;">
        <a id="btn_print" href="#" class="easyui-linkbutton" data-options="iconCls:'icon-print', plain:true">Tampilkan Data</a>
		<a id="btn_export_excel" href="#" class="easyui-linkbutton" data-options="iconCls:'icon-excel', plain:true">Export</a>
    </div>

	<table style="border-bottom:1px #000">
        <tr>
            <td width="130" align="right" id="label_laporan">Date :</td>
            <td width="539" id="label_laporan"><input id="txt_tgl_aw" name="txt_tgl_aw" class="date"/> - <input id="txt_tgl_ak" name="txt_tgl_ak" class="date"/></td>
        </tr>
        <tr>
            <td align="right" id="label_laporan">Lokasi Asal :</td>
            <td><input id="txt_lokasiasal" name="txt_lokasiasal[]" style="width:250px"/></td>
        </tr>
        <tr>
            <td align="right" id="label_laporan">Lokasi Tujuan :</td>
            <td><input id="txt_lokasitujuan" name="txt_lokasitujuan[]" style="width:250px"/></td>
        </tr>
        <!--<tr>
            <td align="right" id="label_laporan">Lokasi :</td>
            <td><input id="txt_lokasi" name="txt_lokasi[]" style="width:250px"/></td>
        </tr>-->
        <tr hidden>
			<td align="right" id="label_laporan">Items Category :</td>
			<td id="label_laporan"><input id="txt_kategori_barang_awal" name="txt_kategori_barang_awal" style="width:250px"/> - <input id="txt_kategori_barang_akhir" name="txt_kategori_barang_akhir" style="width:250px"/></td>
        </tr>
		<tr>
			<td align="right" id="label_laporan">Barang :</td>
			<td id="label_laporan">
				<input id="txt_namabarang" name="txt_namabarang" class="label_input" style="width:250px" prompt="nama barang">
			    <input id="txt_kodebarang" name="txt_kodebarang" class="label_input" style="width:100px" prompt="kode barang">
			</td>
        </tr>
        <tr hidden>
            <td align="right" id="label_laporan">Barang :</td>
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
		<legend id="label_laporan">Laporan</legend>
        <table width="520" border="0">
           	<tr>
               	<td width="33%"><label id="label_laporan"><input type="radio" name="rdTampil" value="Register" checked="checked"> Register</label></td>
               	<td width="36%"><label id="label_laporan"><input type="radio" name="rdTampil" value="RegisterByLokasiAsal"> Register Berdasarkan Lokasi Asal</label></td>
        	</tr>
           	<tr>
				<td><label id="label_laporan"><input type="radio" name="rdTampil" value="RegisterByBarang" /> Register Berdasarkan Barang</label></td>
              	<td><label id="label_laporan"><input type="radio" name="rdTampil" value="RegisterByLokasiTujuan"> Register Berdasarkan Lokasi Tujuan</label></td>
			</tr>
		</table>
    </fieldset>

	<input type="hidden" name="excel" id="excel">
	<input type="hidden" name="file_name" id="file_name" value="TransferTitipanReport">
</form>
<script>
$(document).ready(function(){
	browse_data('#txt_lokasiasal', 'semua_lokasi&status=all','KODE');
	browse_data('#txt_lokasitujuan', 'semua_lokasi&status=all','KODE');

	browse_data_barang('#txt_barang_awal_list', 'barang&status=all&jenis[]=RAW MATERIAL&jenis[]=FINISH GOOD&jenis[]=SEMI FINISH GOOD&jenis[]=PACKAGING&jenis[]=OTHER ITEM/GOOD&jenis[]=SPARE PART','KODE');
	browse_data_barang('#txt_barang_awal', 'barang&status=all&jenis[]=RAW MATERIAL&jenis[]=FINISH GOOD&jenis[]=SEMI FINISH GOOD&jenis[]=PACKAGING&jenis[]=OTHER ITEM/GOOD&jenis[]=SPARE PART','KODE');
	browse_data_barang('#txt_barang_akhir', 'barang&status=all&jenis[]=RAW MATERIAL&jenis[]=FINISH GOOD&jenis[]=SEMI FINISH GOOD&jenis[]=PACKAGING&jenis[]=OTHER ITEM/GOOD&jenis[]=SPARE PART','KODE');

	browse_data_kategori_barang('#txt_kategori_barang_awal', 'kategori_barang&status=all','NAMA');
	browse_data_kategori_barang('#txt_kategori_barang_akhir', 'kategori_barang&status=all','NAMA');

	$('#txt_barang_akhir_list').combogrid({
		panelWidth:650,
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
			{field:'KODE',title:'ID',width:80, sortable:true},
			{field:'NAMA',title:'Description',width:240, sortable:true},
			{field:'SATUAN',title:'Unit 1',width:60, sortable:false, align:'center'},
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

function browse_data(id, table, sort) {
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

function browse_data_barang(id, table, sort) {
	$(id).combogrid({
		panelWidth:650,
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
			{field:'KODE',title:'Kode',width:80, sortable:true},
			{field:'NAMA',title:'Nama',width:240, sortable:true},
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