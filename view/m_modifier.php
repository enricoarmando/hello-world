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
				<div data-options="name:'alasan'">Modifier</div>				
			</div>
		</div>
	</div>
	<div data-options="region:'center',">
		<table id="table_data"></table>
	</div>
</div>

<div id="form_input" style="width:550px">
	<input type="hidden" name="act">
	<table style="padding:5px">
		<tr>
			<td align="right" id="label_form">Kode Brand</td>
			<td>
				<input name="KODEBRAND" style="width:100px" class="label_input" required="true" validType='length[0,10]'>
				<label id="label_form"><input type="checkbox" id="STATUS" name="STATUS" value="1"> Aktif</label>
			</td>
		</tr>
		<tr>
			<td align="right" id="label_form">Kode Modifier</td>
			<td>
				<input name="KODE" style="width:100px" class="label_input" required="true" validType='length[0,10]'>
			</td>
		</tr>
		<tr>
			<td align="right" id="label_form">Nama Modifier</td>
			<td><input name="NAMA" style="width:200px" class="label_input" required="true" validType='length[0,50]'></td>
		</tr>
		<tr>
			<td align="right" id="label_form">Nama Front</td>
			<td><input name="NAMAFRONT" style="width:200px" class="label_input" required="true" validType='length[0,50]'></td>
		</tr>
		<tr>
			<td align="right" id="label_form">Nama Printer</td>
			<td><input name="NAMAPRINTER" style="width:200px" class="label_input" required="true" validType='length[0,50]'></td>
		</tr>
		<tr>
			<td align="right" id="label_form">Nama Warna</td>
			<td><input name="NAMAWARNA" type ="color" value="#ffffff" style="width:50px" class="label_input" required="true" validType='length[0,100]'></td>
		</tr>
		<tr>
			<td align="right" id="label_form">Printer yang dipakai</td>
			<td>
				<input id="KODEPRINTER" name="KODEPRINTER" style="width:100px" required="true">
			</td>
		</tr>
		<tr>
			<td align="right" id="label_form">Jenis</td>
			<td>
				<div id="label_form">
					<input type="radio" id="JENISDEFAULT" name="JENIS" value="0">Modifier Item</input>
					<input type="radio" id="JENIS2"name="JENIS" value="1" >Modifier Non Item</input>
				</div>
			</td>
		</tr>
	</table>
	<input type="hidden" id="data_detail" name="data_detail">
	<div id="show_table">
		<table id="table_data_detail" style="height:220px;width:100%;"></table>
	</div>
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
	browse_data_printer('#KODEPRINTER','printer_menu','KODE');
	$('[name=JENIS]').change(function(){
		if(this.id=="JENISDEFAULT"){//jika modifier item diklik
			$("#show_table").show();
			reset_detail();buat_table_detail();
		}
		else $("#show_table").hide();
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
	$('#STATUS').prop('checked', true);
	$('#JENISDEFAULT').prop('checked', true); //agar salah satu radio button terpilih
	$('#lbl_kasir, #lbl_tanggal').html('');
}

function ubah() {
	var row = $('#table_data').datagrid('getSelected');
	if (row) {
		$('#form_input').dialog('open').dialog('setTitle', 'Ubah | <?=$menu?>');
		$('#form_input').form('load',row);
		
		if(row.JENIS=="0"){//jika modifier item diklik
			$("#show_table").show();
			reset_detail();buat_table_detail();
		}
		else $("#show_table").hide();
		
		load_data_modifier(row.KODE,row.KODEBRAND);
		
		$('[name=act]').val('edit');		
		$('#lbl_kasir').html(row.USERENTRY);
		$('#lbl_tanggal').html(row.TANGGALENTRY);
	}
}

function simpan() {
	var isValid = $('#form_input').form('validate');
	var act = $('[name=act]').val();
	$('#data_detail').val(JSON.stringify($('#table_data_detail').datagrid('getRows')));
	var datanya = $("#form_input :input").serialize();
	
	if (isValid && $('#JENISDEFAULT').is(':checked')) //kalo modifier item dicentang
		isValid = cek_datagrid($('#table_data_detail'));
	if (isValid && (act=='insert' || act=='edit')) {
		$.ajax({
			type: 'POST',
			dataType: 'json',
			url: "data/process/proses_master.php",
			data: "table=simpan_modifier&"+datanya,
			cache: false,
			beforeSend : function (){
				$.messager.progress();
			},
			success: function(msg){
				$.messager.progress('close');
				if (msg.success) {
					if (act=='insert') {
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
				$.post('data/process/proses_master.php',
					{act:'delete', table:'modifier', kode:row.KODE},function(msg){
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
		url: 'config/datagrid.php?table=modifier',
		rowStyler: function(index,row){  
			if (row.STATUS == 0) return 'background-color:#a8aea6';
		},
		frozenColumns:[[
			{field:'KODEBRAND',title:'Kode Brand',width:120, sortable:true,},
			{field:'KODE',title:'Kode Modifier',width:120, sortable:true,},
			{field:'NAMA',title:'Nama Modifier',width:200, sortable:true,},
		]],
		columns:[[
			{field:'NAMAFRONT',title:'Nama Front',width:100, sortable:true},
			{field:'NAMAPRINTER',title:'Nama Printer',width:100, sortable:true},
			{field:'NAMAWARNA',title:'Nama Warna',width:100, sortable:true},
			{field:'KODEPRINTER',title:'Kode Printer',width:100, sortable:true},
			{field:'JENIS',title:'Jenis',width:100, sortable:true, formatter:function(val, row){
				if (row.JENIS==0)
					return 'Modifier Item';
				else 
					return 'Modifier Non Item';
			}},
			{field:'USERENTRY',title:'Penginput',width:120, sortable:true},
			{field:'TANGGALENTRY',title:'Tgl. Input',width:110, sortable:true, formatter:ubah_tgl_indo, align:'center',},
			{field:'STATUS',title:'Aktif', align:'center', sortable:true, formatter:format_checked,}

		]],
		onDblClickRow:function(index,row) {
			before_edit();
		},
	}).datagrid('enableFilter');
}

function buat_table_detail() {
	$("#table_data_detail").datagrid({
		showFooter:true,
		rownumbers:true,
		clickToEdit:false,
		data:[],
		frozenColumns:[[
			{field:'kodebrg',title:'Kode Barang',width:75,editor:{
				type:'combogrid',
				options:{
					panelWidth:550,
					url: 'config/combogrid.php?table=resep_barang',
					mode: 'remote',
					idField:'KODE',
					textField:'KODE',
					view:bufferview,
					pageSize:10,
					columns:[[
						{field:'KODE',title:'Kode',width:100, sortable:true},
						{field:'NAMA',title:'Nama',width:200, sortable:true},
					]]
				}
			}}
		]],
		columns: [[
			{field:'keterangan',title:'Keterangan',align:'center', width:300},
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
				case 'kodebrg':
					var data = ed.combogrid('grid').datagrid('getSelected');
					var nama = data ? data.NAMA : '';
					row_update = {
						keterangan:nama
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
			//hitung_subtotal_detail(index,row);
		}
	}).datagrid('enableCellEditing');
}
function browse_data_printer(id, table, sort) {
	$(id).combogrid({
		panelWidth:380,
		url: 'config/combogrid.php?table='+table,
		idField:'KODE',
		textField:'NAMAPRINTER',
		mode:'local',
		columns:[[
			{field:'KODE',title:'Kode',width:80, sortable:true},
			{field:'NAMAPRINTER',title:'Nama Printer',width:240, sortable:true},
		]]
	});
}

function load_data_modifier(kode,brand) {
	$.ajax({
		type: 'POST',
		dataType: 'json',
		url: "data/process/proses_master.php",
		data: "table=load_data_modifier&kode="+kode+"&brand="+brand,
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
