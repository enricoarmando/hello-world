<?php
if (empty($_SESSION['user'])) header('Location: ../../../index.php');
?>

<div class="easyui-layout" style="width:100%;height:100%" fit="true">
	<div data-options="region:'north'" style="height:40px;padding:5px;">
		<a id="btn_tambah" href="#" class="easyui-linkbutton" data-options="iconCls:'icon-add', plain:true">Tambah</a>
		<a id="btn_ubah" href="#" class="easyui-linkbutton" data-options="iconCls:'icon-edit', plain:true">Ubah / F4</a>
		<a id="btn_hapus" href="#" class="easyui-linkbutton" data-options="iconCls:'icon-cancel', plain:true">Hapus</a>
		<a id="btn_refresh" href="#" class="easyui-linkbutton" data-options="iconCls:'icon-reload', plain:true">Refresh</a>
		<div style="float:right" hidden>
			<input class="easyui-searchbox" data-options="prompt:'Silahkan Ketikan Kata Kunci Pada Bagian Pencarian',menu:'#mm',searcher:do_search" style="width:300px"></input>
			<div id="mm">
				<div data-options="name:'alasan'">Promosi</div>				
			</div>
		</div>
	</div>
	<div data-options="region:'center',">
		<table id="table_data" ></table>
	</div>
</div>

<div id="form_input" style="width:750px">
	<input type="hidden" name="act">
	<table style="padding:5px">
		<tr>
			<td align="right" id="label_form">Kode Promosi</td>
			<td>
				<input id="KODEPROMO" name="KODEPROMO" style="width:100px" class="label_input" required="true" validType='length[0,10]'>
				<label id="label_form"><input type="checkbox" id="STATUS" name="STATUS" value="1"> Aktif</label> 
			</td>
		</tr>
		<tr>
			<td align="right" id="label_form">Nama Promosi</td>
			<td><input name="NAMAPROMO" style="width:300px" class="label_input" required="true" validType='length[0,30]'></td>
		</tr>
		<tr>
			<td align="right" id="label_form">Jenis</td>
			<td>
				<div id="label_form">
					<input type="radio" name="JENIS" value="0" id="RADIO1"> Persentase
					<input type="radio" name="JENIS" value="1" id="RADIO2"> Nominal &nbsp &nbsp
					<input id="AMOUNT" name="AMOUNT" class="easyui-numberbox" style="width:100px" class="label_input" required="true" validType='' data-options='min:0,max:100'>&nbsp
					<label id="labeljenis"></label>
				</div>
			</td>
		</tr>
		<tr>
			<td align="right" id="label_form">Periode Awal</td>
			<td><input class="easyui-datebox" name="PERIODEAWAL" data-options="required:true,showSeconds:false" value="3/4/2010 2:3" style="width:150px">
		</tr>
		<tr>
			<td align="right" id="label_form">Periode Akhir</td>
			<td><input class="easyui-datebox" name="PERIODEAKHIR" data-options="required:true,showSeconds:false" value="3/4/2010 2:3" style="width:150px">
		</tr>
		<tr>
			<td align="right" id="label_form">Hari Aktif</td>
			<td>
			<label id="label_form"><input type="checkbox" id="SENIN" name="SENIN" value="SENIN"> Senin</label>
			<label id="label_form"><input type="checkbox" id="SELASA" name="SELASA" value="SELASA"> Selasa</label>
			<label id="label_form"><input type="checkbox" id="RABU" name="RABU" value="RABU"> Rabu</label>
			<label id="label_form"><input type="checkbox" id="KAMIS" name="KAMIS" value="KAMIS"> Kamis</label>
			<label id="label_form"><input type="checkbox" id="JUMAT" name="JUMAT" value="JUMAT"> Jumat</label>
			<label id="label_form"><input type="checkbox" id="SABTU" name="SABTU" value="SABTU"> Sabtu</label>
			<label id="label_form"><input type="checkbox" id="MINGGU" name="MINGGU" value="MINGGU"> Minggu</label>
			</td>
		</tr>
		<tr>
			<td align="right" id="label_form">Limit Grand Total</td>
			<td><input id="LIMITGRANDTOTAL" name="LIMITGRANDTOTAL" class="easyui-numberbox" style="width:100px" data-options='min:0'></td>
		</tr>
		<tr>
			<td align="right" id="label_form">Keterangan</td>
			<td><textarea name="KETERANGAN" style="width:300px" validType='length[0,400]'></textarea></td>
		</tr>
	</table>
	<input type="hidden" id="data_detail" name="data_detail">

	<table id="table_data_detail" style="height:200px;width:100%;"></table>
</div>
<div id="dlg-buttons">
	<table cellpadding="0" cellspacing="0" style="width:100%">
		<tr>
			<td align="left" id="label_form"><label style="font-weight:normal" id="label_form">User :</label> <label id="lbl_kasir"></label> <label style="font-weight:normal" id="label_form">| Tgl Input :</label> <label id="lbl_tanggal"></label></td>
			<td style="text-align:right">
				<a href="#" class="easyui-linkbutton" iconCls="icon-save" id='btn_simpan' onclick="javascript:simpan()">Simpan</a>
				<a href="#" class="easyui-linkbutton" iconCls="icon-reload" onclick="javascript:tambah()">Reset</a>
			</td>
		</tr>
	</table>
</div>

<script type="text/javascript" src="script/jquery-easyui/plugins/datagrid-filter.min.js"></script>

<script>
$(document).ready(function(){
	create_form_login();
	
	buat_table();
	
	$('#form_login').dialog({
		onOpen:function(){
			$('#form_login').form('clear');
			$('#form_login [name=\'txt_user\']').focus();
		},
		buttons: [{
			text:'Otorisasi',
			iconCls:'icon-ok',
			handler:function(){
				mode = $('#mode').val();
				
				get_data_user('<?=$_GET['kode']?>', function(data){
					var msg = '';
					if (mode=='tambah') {
						if (data.TAMBAH==1)
							tambah();
						else
							msg = 'Tambah';
					} else if (mode=='ubah') {
						if (data.UBAH==1)
							ubah();
						else
							msg = 'Ubah';
					} else if (mode=='hapus') {
						if (data.HAPUS==1)
							hapus();
						else
							msg = 'Hapus';
					} 
					
					if (msg!='')
						$.messager.alert('Error', 'Anda Tidak Memiliki Hak Akses '+msg+' Data', 'error');
					else
						$('#form_login').dialog('close');
				});
			}
		}], 
		modal:true,
	}).dialog('close');
	
	$("#form_input").dialog({
		onOpen:function(){
			$('#form_input').form('clear');
		},
		buttons: '#dlg-buttons'
	}).dialog('close');
	
	buat_table_detail();
	
	$('#RADIO1').change(function(){
		$('#AMOUNT').attr("data-options",'min:0,max:100');
	});
	$('#RADIO2').change(function(){
		$('#AMOUNT').attr("data-options",'min:0');
	});
});

$("#btn_tambah").click(function(){
    before_add();
});
$("#btn_ubah").click(function(){
	before_edit();
});
$("#btn_batal").click(function(){
	batal();
});

$("#btn_hapus").click(function(){
	before_delete();
});
$("#btn_refresh").click(function(){
	$('#table_data').datagrid('reload');
});

shortcut.add('F2',function() {
	before_add();
});
shortcut.add('F4',function() {
	before_edit();
});
shortcut.add('F8',function() {
	simpan();
});

function before_add() {
	$('#mode').val('tambah');
	get_akses_user('<?=$_GET['kode']?>', function(data){
		if (data.TAMBAH==1) {
			tambah();
		} else {
			$.messager.confirm('Confirm', 'Anda Tidak Memiliki Hak Akses "Tambah", Anda Akan Melanjutkan Dengan Otorisasi ?', function(r){
				if (r) $('#form_login').dialog('open');
			});
		}
	});
}

function before_edit() {
	$('#mode').val('ubah');
	get_akses_user('<?=$_GET['kode']?>', function(data){
		if (data.UBAH==1) {
			ubah();
		} else {
			$.messager.confirm('Confirm', 'Anda Tidak Memiliki Hak Akses "Ubah", Anda Akan Melanjutkan Dengan Otorisasi ?', function(r){
				if (r) $('#form_login').dialog('open');
			});
		}
	});
}

function before_delete() {
	$('#mode').val('hapus');
	get_akses_user('<?=$_GET['kode']?>', function(data){
		if (data.HAPUS==1) {
			hapus();
		} else {
			$.messager.confirm('Confirm', 'Anda Tidak Memiliki Hak Akses "Hapus", Anda Akan Melanjutkan Dengan Otorisasi ?', function(r){
				if (r) $('#form_login').dialog('open');
			});
		}
	});
}

function tambah() {
	$('#form_input').dialog('open').dialog('setTitle', 'Tambah | <?=$menu?>');
	$('[name=act]').val('insert');
	$('#STATUS').prop('checked', true); $('#RADIO1').prop('checked', true); 
	$('#SENIN').prop('checked', true); $('#SELASA').prop('checked', true); 
	$('#RABU').prop('checked', true); $('#KAMIS').prop('checked', true); 
	$('#JUMAT').prop('checked', true);$('#SABTU').prop('checked', true); 
	$('#MINGGU').prop('checked', true); 
	$('#lbl_kasir, #lbl_tanggal').html('');
	
	reset_detail();
}

function ubah() {
	var row = $('#table_data').datagrid('getSelected');
	if (row) {
		$('#form_input').dialog('open').dialog('setTitle', 'Ubah | <?=$menu?>');
		$('#form_input').form('load',row);
		$('[name=act]').val('edit');
		
		//get_combogrid_data ($('#KODE'), row.kode, 'Sub Recipe');
		load_data_promo(row.KODEPROMO);
		
		$('#lbl_kasir').html(row.USERENTRY);
		$('#lbl_tanggal').html(row.TGLENTRY);
	}
}

function simpan() {
	var mode = $('[name=act]').val();

	$('#data_detail').val(JSON.stringify($('#table_data_detail').datagrid('getRows')));

	var datanya = $("#form_input :input").serialize();
	var isValid = $('#form_input').form('validate');
	
	if (isValid)
		isValid = cek_datagrid($('#table_data_detail'));
	if (isValid && (mode=='insert' || mode=='edit')) {
		$.ajax({
			type: 'POST',
			dataType: 'json',
			url: "data/process/proses_master.php",
			data: "table=simpan_promo&"+datanya,
			cache: false,
			beforeSend : function (){
				$.messager.progress();
			},
			success: function(msg){
				$.messager.progress('close');
				if (msg.success) {
					if (mode=='tambah') {
						tambah();
						$.messager.show({
							title:'Info',
							msg:'Simpan Sukses',
							showType:'show'
						});
					} else {
						$('#form_input').dialog('close');
						$.messager.alert('Info','Ubah Sukses','info');
					}
					$('#table_data').datagrid('reload');
				} else {
					$.messager.alert('Error', msg.errorMsg, 'error');
				}
			}
		});
	}
}

function hapus() {
	var row = $('#table_data').datagrid('getSelected');
	if (row){
		$.messager.confirm('Confirm','Anda Yakin Menghapus Data Ini ?',function(r){
			if (r){
				$.post('data/process/proses_master.php',{act:'delete', table:'promo', kode:row.KODEPROMO},function(msg){
					if (msg.success){						
						$('#table_data').datagrid('reload');    // reload the user data
					} else {
						$.messager.alert('Error', msg.errorMsg, 'error');
					}
				},'json');
			}
		});
	}
}

function buat_table() {
	$('#table_data').datagrid({
		remoteFilter:true,
		fit:true,
		singleSelect:true,
		striped:true,
		pagination:true,
		pageSize:20,
		url: 'config/datagrid.php?table=promosi',
		rowStyler: function(index,row){  
			if (row.STATUS == 0) return 'background-color:#a8aea6';
		},
		frozenColumns:[[
			{field:'KODEPROMO',title:'Kode Promo',width:80, sortable:true,},
			{field:'NAMAPROMO',title:'Nama Promo',width:200, sortable:true,},
		]],
		columns:[[
			{field:'JENIS',title:'Jenis',width:100, sortable:true, formatter:format_checked,align:'center'},
			{field:'AMOUNT',title:'Nominal',width:100, sortable:true,align:'right'},
			{field:'PERIODEAWAL',title:'Periode Awal',width:100, sortable:true,align:'center'},
			{field:'PERIODEAKHIR',title:'Periode Akhir',width:100, sortable:true,align:'center'},
			{field:'HARIAKTIF',title:'Hari Aktif',width:100, sortable:true,  align:'center'},
			{field:'LIMITGRANDTOTAL',title:'Limit Grand Total',width:100, sortable:true, align:'right'},
			{field:'KETERANGAN',title:'Keterangan',width:300, sortable:true,  align:'center'},
			{field:'SENIN',title:'Keterangan',width:300, sortable:true,  align:'center',hidden:true},
			{field:'SELASA',title:'Keterangan',width:300, sortable:true,  align:'center',hidden:true},
			{field:'RABU',title:'Keterangan',width:300, sortable:true,  align:'center',hidden:true},
			{field:'KAMIS',title:'Keterangan',width:300, sortable:true,  align:'center',hidden:true},
			{field:'JUMAT',title:'Keterangan',width:300, sortable:true,  align:'center',hidden:true},
			{field:'SABTU',title:'Keterangan',width:300, sortable:true,  align:'center',hidden:true},
			{field:'MINGGU',title:'Keterangan',width:300, sortable:true,  align:'center',hidden:true},
			{field:'USERENTRY',title:'User',width:70, sortable:true},
			{field:'TANGGALENTRY',title:'Tgl. Input',width:110, sortable:true, formatter:ubah_tgl_indo, align:'center',},
			{field:'STATUS',title:'Aktif', align:'center', sortable:true, formatter:format_checked,}

		]],
		onDblClickRow:function(index,row) {
			before_edit();
		},
	}).datagrid('enableFilter');
}

function browse_data_kategori(id, table, sort) {
	$(id).combogrid({
		panelWidth:380,
		url: 'config/combogrid.php?table='+table,
		idField:'KODE',
		textField:'KODE',
		mode:'local',
		columns:[[
			{field:'KODE',title:'Kode',width:80, sortable:true},
			{field:'NAMA',title:'Nama',width:240, sortable:true},
		]],
		onChange:function(){
			var row = $(this).combogrid('grid').datagrid('getSelected');
			if (row)
				$('#NAMAKATEGORI').textbox('setValue', row.NAMA)
			else
				$('#NAMAKATEGORI').textbox('clear')
		}
	});
}
function browse_data_satuan(id, table, sort) {
	$(id).combogrid({
		panelWidth:380,
		url: 'config/combogrid.php?table='+table,
		idField:'KODE',
		textField:'KODE',
		mode:'local',
		columns:[[
			{field:'KODE',title:'Kode',width:80, sortable:true},
			{field:'NAMA',title:'Nama',width:240, sortable:true},
		]]
	});
}

function buat_table_detail() {
	$("#table_data_detail").datagrid({
		showFooter:true,
		rownumbers:true,
		clickToEdit:false,
		data:[],
		frozenColumns:[[
			{field:'kode',title:'Kode Menu',width:100,editor:{
				type:'combogrid',
				options:{
					panelWidth:550,
					url: 'config/combogrid.php?table=promo_menu',
					mode: 'remote',
					idField:'KODE',
					textField:'KODE',
					view:bufferview,
					pageSize:10,
					columns:[[
						{field:'KODE',title:'Kode',width:100, sortable:true},
						{field:'NAMA',title:'Nama',width:200, sortable:true},
					]],
				}
			}},
		]],
		columns:[[
			{field:'nama',title:'Nama Menu',idField:'nama',width:250},
		]],
		onClickRow:function(){
		},
		onLoadSuccess : function (data){
		},
		onAfterDeleteRow:function(index, row){
		},
		onCellEdit:function(index,field,val){
			var row = $(this).datagrid('getRows')[index];
			var ed  = get_editor ('#table_data_detail', index, field);		
		},
		onEndEdit:function(index,row,changes){
			var cell = $(this).datagrid('cell');
			var ed = get_editor ('#table_data_detail', index, cell.field);
			var row_update = {};
			switch(cell.field) {
				case 'kode':
					var data = ed.combogrid('grid').datagrid('getSelected');
					var nama = data ? data.NAMA : '';
					row_update = {
						nama:nama
					};
					break;
			}
			if (jQuery.isEmptyObject(row_update) == false) {
				$(this).datagrid('updateRow',{
					index: index,
					row: row_update
				});
			}
		},
		onAfterEdit:function(index,row,changes){
		}
	}).datagrid('enableCellEditing');
}

function load_data_promo(kode) {
	$.ajax({
		type: 'POST',
		dataType: 'json',
		url: "data/process/proses_master.php",
		data: "table=load_data_promo&kode="+kode,
		cache: false,
		beforeSend : function (){
			$.messager.progress();
		},
		success: function(msg){
			$.messager.progress('close');
			if (msg.success) {
				$('#table_data_detail').datagrid('loadData', msg.detail).datagrid('unselectAll');
			} else {
				$.messager.alert('Error', msg.errorMsg, 'error');
			}
		}
	});
}

function reset_detail() {
	$('#table_data_detail').datagrid('loadData', []);
}
</script>
