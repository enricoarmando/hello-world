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
				<div data-options="name:'alasan'">Alasan</div>				
			</div>
		</div>
	</div>
	<div data-options="region:'center',">
		<table id="table_data" ></table>
	</div>
</div>

<div id="form_input" style="width:550px">
	<input type="hidden" name="act">
	<table style="padding:5px">
		<tr>
			<td align="right" id="label_form">Kode Departemen Menu</td>
			<td><input name="KODEDEPARTEMENMENU" style="width:100px" class="label_input" required="true" validType='length[0,10]'><label id="label_form"><input type="checkbox" id="STATUS" name="STATUS" value="1"> Aktif</label></td>
		</tr>
		<tr>
			<td align="right" id="label_form">Nama</td>
			<td><input name="NAMADEPARTEMENMENU" style="width:300px" class="label_input" required="true" validType='length[0,50]'></td>
		</tr>
		<tr>
			<td align="right" id="label_form">Nama Front</td>
			<td><input name="NAMAFRONT" style="width:300px" class="label_input" required="true" validType='length[0,50]'></td>
		</tr>
		<tr>
			<td align="right" id="label_form">Nama Printer</td>
			<td><input name="NAMAPRINTER" style="width:300px" class="label_input" required="true" validType='length[0,50]'></td>
		</tr>
		<tr>
			<td align="right" id="label_form">Nama Warna</td>
			<td><input name="NAMAWARNA" type="color" value="#ff0000" style="width:100px" class="label_input" required="true" validType='length[0,50]'></td>
		</tr>
		<tr>
			<td align="right" id="label_form">Urutan</td>
			<td><input id="URUTAN" name="URUTAN" class="easyui-numberspinner" style="width:100px" class="label_input" required="true" validType='length[0,100]' data-options='min:1'></td>
		</tr>
	</table>
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
	$('#lbl_kasir, #lbl_tanggal').html('');
	$('#URUTAN').numberspinner('setValue', 1);
}

function ubah() {
	var row = $('#table_data').datagrid('getSelected');
	if (row) {
		$('#form_input').dialog('open').dialog('setTitle', 'Ubah | <?=$menu?>');
		
		$('#form_input').form('load',row);
		$('[name=act]').val('edit');
		
		$('#lbl_kasir').html(row.USERENTRY);
		$('#lbl_tanggal').html(row.TGLENTRY);
	}
}

function simpan() {
	var isValid = $('#form_input').form('validate');
	if (isValid) {
		act = $('[name=act]').val();
		$.ajax({
			type: 'POST',
			url: "data/process/proses_master.php", 
			data: "table=departemen_menu&"+$('#form_input :input').serialize(),
			dataType: 'json',
			beforeSend : function (){
				$.messager.progress();
			},
			success: function(msg){
				$.messager.progress('close');
				if (msg.success){
					if (act=='insert') tambah();
					else $('#form_input').dialog('close');
					
					$('#table_data').datagrid('reload');    // reload the user data
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
				$.post('data/process/proses_master.php',{act:'delete', table:'departemen_menu', kodedepartemenmenu:row.KODEDEPARTEMENMENU},function(msg){
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
		url: 'config/datagrid.php?table=departemen_menu',
		rowStyler: function(index,row){  
			if (row.STATUS == 0) return 'background-color:#a8aea6';
		},
		frozenColumns:[[
			{field:'KODEDEPARTEMENMENU',title:'Kode Departemen Menu',width:80, sortable:true,},
			{field:'NAMADEPARTEMENMENU',title:'Nama Departemen Menu',width:200, sortable:true,},
		]],
		columns:[[
			{field:'NAMAFRONT',title:'Nama Front',width:100, sortable:true},
			{field:'NAMAPRINTER',title:'Nama Printer',width:100, sortable:true},
			{field:'NAMAWARNA',title:'Warna',width:70, sortable:true},
			{field:'URUTAN',title:'Urutan',width:70, sortable:true},
			{field:'USERENTRY',title:'User',width:70, sortable:true},
			{field:'TANGGALENTRY',title:'Tgl. Input',width:110, sortable:true, formatter:ubah_tgl_indo, align:'center',},
			{field:'STATUS',title:'Aktif', align:'center', sortable:true, formatter:format_checked,}

		]],
		onDblClickRow:function(index,row) {
			before_edit();
		},
	}).datagrid('enableFilter');
}
</script>
