<?php
session_start();
if (empty($_SESSION['user'])) die('restart browser');
?>
<form method='post' target='_blank' action='data/report/lap_transaksi_penjualan.php' id="form_input" style="width:550px">
    <div style="padding:5px;border:1px solid #ddd;">
        <a id="btn_print" href="#" class="easyui-linkbutton" data-options="iconCls:'icon-print', plain:true">Tampilkan Data</a>
		<a id="btn_export_excel" href="#" class="easyui-linkbutton" data-options="iconCls:'icon-excel', plain:true">Export</a>
    </div>

    <fieldset id="field">
        <legend id="label_laporan">Transaksi</legend>
        <table width="100%">
            <tr>
                <td width="50%"><label id="label_laporan"><input type="checkbox" class="jtrans" name="cbJenis[]" value="JUAL" checked="checked"> Jual</label></td>
                <td><label id="label_laporan"><input type="checkbox" class="jtrans" name="cbJenis[]" value="RETUR JUAL" checked="checked"> Retur Jual</label></td>
            </tr>
        </table>
    </fieldset>

	<fieldset id="field">
        <legend id="label_laporan">Jenis Penjualan</legend>
        <table width="100%">
            <tr>
                <td width="50%"><label id="label_laporan"><input type="checkbox" class="jtrans" name="cbJenisPenjualan[]" value="LANGSUNG" checked="checked"> Langsung</label></td>
                <td><label id="label_laporan"><input type="checkbox" class="jtrans" name="cbJenisPenjualan[]" value="PESANAN" checked="checked"> Pesanan</label></td>
            </tr>
        </table>
    </fieldset>

    <fieldset id="field">
        <legend id="label_laporan">Tunai/Kredit</legend>
        <table width="100%">
            <tr>
                <td width="50%"><label id="label_laporan"><input type="checkbox" class="jtrans" name="cbTunaiKredit[]" value="TUNAI" checked="checked"> Tunai</label></td>
                <td><label id="label_laporan"><input type="checkbox" class="jtrans" name="cbTunaiKredit[]" value="KREDIT" checked="checked"> Kredit</label></td>
            </tr>
        </table>
    </fieldset>

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
			<td align="right" id="label_laporan">Customer :</td>
			<td id="label_laporan"><input id="txt_namacustomer" name="txt_namacustomer" class="label_input" style="width:250px" prompt="Nama"/></td>
        </tr>
		<tr>
			<td align="right" id="label_laporan">Salesman :</td>
			<td id="label_laporan"><input id="txt_namasalesman" name="txt_namasalesman" class="label_input" style="width:250px" prompt="Nama"/></td>
        </tr>
		<tr>
			<td align="right" id="label_laporan">Barang :</td>
			<td id="label_laporan">
				<input id="txt_namabarang" name="txt_namabarang" class="label_input" style="width:250px" prompt="Nama"/> 
			    <input id="txt_kodebarang" name="txt_kodebarang" class="label_input" style="width:100px" prompt="Kode"/>
			</td>
        </tr>
    </table>

    <fieldset id="field">
		<legend id="label_laporan">Status</legend>
        <table width="100%">
			<tr>
				<td width="25%" hidden><label id="label_laporan"> <input type="checkbox" name="cbStatus[]" value="I" checked="checked" /> Input</label></td>
				<td width="25%"><label id="label_laporan"> <input type="checkbox" name="cbStatus[]" value="S" checked="checked" /> Slip</label></td>
				<td width="25%" hidden><label id="label_laporan"> <input type="checkbox" name="cbStatus[]" value="P" checked="checked" /> Posting</label></td>
				<td width="25%"><label id="label_laporan"> <input type="checkbox" name="cbStatus[]" value="D" /> Delete</label></td>
			</tr>
		</table>
	</fieldset>

	<fieldset id="field">
		<legend id="label_laporan">Jenis Laporan</legend>
        <table width="100%" border="0">
           	<tr>
               	<td width="33%"><label id="label_laporan"><input type="radio" name="rdTampil" value="Rekap" checked="checked"/> Rekap</label></td>
               	<td width="33%"><label id="label_laporan"><input type="radio" name="rdTampil" value="Register"> Register</label></td>
				<td width="33%"><label id="label_laporan"><input type="radio" name="rdTampil" value="RekapKaryawan"> Rekap per Karyawan</label></td>
        	</tr>
			<tr>
               	<td><label id="label_laporan"><input type="radio" name="rdTampil" value="HitungPoin"/> Hitung Poin</label></td>
               	<td><label id="label_laporan"><input type="radio" name="rdTampil" value="HitungPoinDetail"/> Hitung Poin (Detail)</label></td>
				<td></td>
        	</tr>
		</table>
    </fieldset>

	<input type="hidden" name="excel" id="excel">
	<input type="hidden" name="file_name" id="file_name" value="LaporanPenjualan">
</form>
<script>
$(document).ready(function(){
	browse_data_lokasi('#txt_lokasi', 'lokasi', 'KODE');
	
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
			{field:'NAMA',title:'Nama',width:250,hidden:true},
			{field:'NAMA2',title:'Nama',width:250,formatter:function(val, row){
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

	$('#txt_customer_akhir_list').combogrid({
		panelWidth:690,
		idField:'KODE',
		mode:'local',
		multiple:true,
		columns:[[
			{field:'KODE',title:'Kd. Customer',width:130, sortable:true},
			{field:'NAMA',title:'Nama Customer',width:240, sortable:true},
			{field:'ALAMAT',title:'Alamat',width:240, sortable:true},
			{field:'KOTA',title:'Kota',width:60, sortable:true},
		]],
		data:[],
		onSelect:function(){
			$(this).combogrid('textbox').val('');
		}
	});

	$('#txt_marketing_akhir_list').combogrid({
		panelWidth:300,
		idField:'KODE',
		mode:'local',
		multiple:true,
		columns:[[
			{field:'KODE',title:'ID',width:60, sortable:false},
			{field:'NAMA',title:'Name',width:220, sortable:false},
		]],
		data:[],
		onSelect:function(){
			$(this).combogrid('textbox').val('');
		}
	});

	$('#txt_customer_awal, #txt_customer_akhir, #txt_marketing_awal, #txt_marketing_akhir, #txt_barang_awal, #txt_barang_akhir').combogrid('disable');

	$('#txt_namasalesman').combogrid('disable');

	$('[name="cbJenis[]"]').change(function(){
		var jen = $(this);
		if (jen.val()=='JUAL') {
			var check = jen.prop('checked');
			$('[name="cbJenisPenjualan[]"]').each(function(){
				$(this).prop('disabled', check ? false : true).prop('checked', check);
			})
		}
	});
});

$("[name=rdTampil]").change(function(){
	var cg = 'disable'
	if ($(this).val().substr(0, 5)!='Rekap') {
		cg = 'enable';
	}
	
	$('#txt_namasalesman').textbox(cg).textbox('clear');
	
	if ($(this).val()=='RekapKaryawan' || $(this).val()=='HitungPoin' || $(this).val()=='HitungPoinDetail') {
		$('#txt_namacustomer').textbox('disable').textbox('clear');
		$('#txt_namasalesman').textbox('disable').textbox('clear');
		$('#txt_namabarang').textbox('disable').textbox('clear');
		$('#txt_kodebarang').textbox('disable').textbox('clear');
	}else{
		$('#txt_namacustomer').textbox('enable').textbox('clear');
		$('#txt_namabarang').textbox('enable').textbox('clear');
		$('#txt_kodebarang').textbox('enable').textbox('clear');		
	}
});

$("[name=rd_cust]").change(function(){
	var cg = $(this).val()==1 ? 'disable' : 'enable';
	var list = $(this).val()==2 ? 'disable' : 'enable';

	$('#txt_customer_awal, #txt_customer_akhir').combogrid(cg).combogrid('clear');
	$('#txt_customer_awal_list, #txt_customer_akhir_list').combogrid(list).combogrid('clear');

	$('#txt_customer_akhir_list').combogrid('grid').datagrid('loadData', []);
});

$("[name=rd_marketing]").change(function(){
	var cg = $(this).val()==1 ? 'disable' : 'enable';
	var list = $(this).val()==2 ? 'disable' : 'enable';

	$('#txt_marketing_awal, #txt_marketing_akhir').combogrid(cg).combogrid('clear');
	$('#txt_marketing_awal_list, #txt_marketing_akhir_list').combogrid(list).combogrid('clear');

	$('#txt_marketing_akhir_list').combogrid('grid').datagrid('loadData', []);
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

function browse_data_customer(id, table, sort) {
	$(id).combogrid({
		panelWidth:690,
		url: 'config/combogrid.php?table='+table+'&member=all',
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
			{field:'KODE',title:'Kd. Customer',width:130, sortable:true},
			{field:'NAMA',title:'Nama Customer',width:240, sortable:true},
			{field:'ALAMAT',title:'Alamat',width:240, sortable:true},
			{field:'KOTA',title:'Kota',width:60, sortable:true},
		]],
		onSelect:function(index, data){
			if (id=='#txt_customer_awal_list') {
				var dg = $('#txt_customer_akhir_list').combogrid('grid');
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
				$('#txt_customer_akhir_list').combogrid('showPanel').combogrid('grid').datagrid('selectAll');
				$('#txt_customer_akhir_list').combogrid('textbox').val('');
				$('#txt_customer_awal_list').combogrid('clear');
			}
		}
	});
}

function browse_data_marketing(id, table, sort) {
	$(id).combogrid({
		panelWidth:640,
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
			{field:'ALAMAT',title:'Alamat',width:240, sortable:true},
			{field:'KOTA',title:'Kota',width:60, sortable:true},
		]],
		onSelect:function(index, data){
			if (id=='#txt_marketing_awal_list') {
				var dg = $('#txt_marketing_akhir_list').combogrid('grid');
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
				$('#txt_marketing_akhir_list').combogrid('showPanel').combogrid('grid').datagrid('selectAll');
				$('#txt_marketing_akhir_list').combogrid('textbox').val('');
				$('#txt_marketing_awal_list').combogrid('clear');
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
		mode:'local',
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
			{field:'NAMA',title:'Nama',width:250,hidden:true},
			{field:'NAMA2',title:'Nama',width:250,formatter:function(val, row){
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
		idField:'NAMA',
		textField:'NAMA',
		mode:'local',
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
			{field:'NAMA',title:'Nama',width:150, sortable:true},
		]]
	});
}

function browse_data_kategori_customer(id, table, sort) {
	$(id).combogrid({
		panelWidth:260,
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
			{field:'NAMA',title:'Nama',width:150, sortable:true},
		]]
	});
}
</script>