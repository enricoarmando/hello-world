<?php
if (empty($_SESSION['user'])) header('Location: ../../../index.php');
?>
<style>
.tree-icon{
	display:none;
}
</style>
<div style="padding:5px;border:1px solid #ddd; width:600px">
    <a id="btn_tambah" href="#" class="easyui-linkbutton" data-options="iconCls:'icon-add', plain:true">Tambah</a>
    <a id="btn_ubah" href="#" class="easyui-linkbutton" data-options="iconCls:'icon-edit', plain:true">Ubah / F4</a>
    <a id="btn_simpan" href="#" class="easyui-linkbutton" data-options="iconCls:'icon-save', plain:true">Simpan</a>
    <a id="btn_hapus" href="#" class="easyui-linkbutton" data-options="iconCls:'icon-cancel', plain:true">Hapus</a>
    <a id="btn_refresh" href="#" class="easyui-linkbutton" data-options="iconCls:'icon-reload', plain:true">Refresh</a>
</div>

<div style="padding:2px;width:600px" id="form_input">
    <table width="100%">
		<tr>
			<td style="width:420px">
				<fieldset id="menu_user">
					<legend id="label_laporan">General Info</legend>
					<table>
						<tr id="view_browse_user">
							<td width="120" id="label_form">ID User</td>
							<td><input name="USERID_CG" id="USERID_CG"/></td>
						</tr>
						<tr id="view_temp_user">
							<td width="120" id="label_form">ID User</td>
							<td><input name="USERID" class="label_input" data-options="required:true,fontTransform:'normal'" validType='length[0,50]'/></td>
						</tr>
						<tr>
							<td id="label_form">Nama User</td>
							<td><input name="USERNAME" class="label_input" data-options="required:true,fontTransform:'normal'" validType='length[0,50]' style="width:250px"/></td>
						</tr>
						<tr>
							<td id="label_form">Password</td>
							<td><input name="PASS" type="password" class="label_input" data-options="required:true,fontTransform:'normal'" validType='length[0,20]'/></td>
						</tr>
						<tr>
							<td id="label_form">Ulangi Password</td>
							<td><input name="RE_PASS" type="password" class="label_input" data-options="required:true,fontTransform:'normal'" validType="equals['[name=PASS]']"/></td>
						</tr>
					</table>
				</fieldset>
			</td>
			<td>
				<table style="width:170px; height:130px" title="Lokasi" id="table_data_lokasi"></table>
			</td>
		</tr>
		<tr hidden>
			<td colspan="2">
				<fieldset id="menu_tambahan">
					<legend id="label_laporan">Another</legend>
					<table width="100%">
						<tr>
							<td width="33%" hidden><label id="label_form"><input type="checkbox" name="OTORISASI" value="1" /> Authorization</label></td>
							<td width="40%"><label id="label_form"><input type="checkbox" name="TAMPILGRANDTOTAL" value="1" /> Show Price</label></td>
							<td><label id="label_form"><input type="checkbox" name="PRINTULANG" value="1" /> Re-Print</label></td>
						</tr>
					</table>
				</fieldset>
			</td>
		</tr>
		<tr>
			<td colspan="2">
				<div id="menu_tree" title="Daftar Hak Akses Menu"></div>
			</td>
		</tr>
	</table>

	<input type="hidden" name="act">
	<input type="hidden" id="data_detail" name="data_detail">
	<input type="hidden" id="data_lokasi" name="data_lokasi">
	<input type="hidden" name="mode" id="mode">
</div>
<script>
$(document).ready(function(){
	create_form_login();
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
							msg = 'Edit';
					} else if (mode=='hapus') {
						if (data.HAPUS==1)
							hapus();
						else
							msg = 'Delete';
					}

					if (msg!='')
						$.messager.alert('Error', 'Sorry You Don\'t Have Access to '+msg+' Data', 'error');
					else
						$('#form_login').dialog('close');
				});
			}
		}],
		modal:true,
	}).dialog('close');

	$("#table_data_lokasi").datagrid({
		rownumbers:true,
		singleSelect:true,
		checkOnSelect:false,
		selectOnCheck:false,
		url:'config/datagrid.php?table=semua_lokasi',
		columns:[[
			{field:'ck',title:'',width:30,checkbox:true},
			{field:'KODELOKASI',title:'Kode',width:80},
		]],
	});

	$('[name=USERID_CG]').combogrid({
		panelWidth: 170,
		mode: 'local',
		idField: 'USERID',
		textField: 'USERID',
		url: 'config/combogrid.php?table=user_id',
		columns: [[
			{field:'USERID',title:'ID User',width:150, sortable:true},
		]],
		onSelect: function(i, x) {
			select_user(x.USERID);
		},
	});

	$('#menu_tree').treegrid({
		height: 350,
		lines: true,
		url : 'config/treegrid.php?table=hak_akses',
		rownumbers: false,
		idField: 'id',
		treeField: 'menu',
		columns:[[
			{field:'menu',title:'',width:220},
			{field:'hakakses',title:'Hak Akses',align:'center', width:80, formatter:function(val, row){
				if (val==0 || val==1) {
					var checked = val==0 ? '' : 'checked';
					return '<input type="checkbox" '+checked+' name="cb_hakakses[]" onchange="cek_detail(\'hakakses\', \'cb_hakakses\', \''+row.id+'\')" id="cb_hakakses_'+row.id+'" value="'+row.tipe+'">';
				} else {
					return '<input type="checkbox" name="hakakses[]" onchange="cek_header(\'hakakses\', \'cb_hakakses\', \''+row.menu+'\')" value="'+row.menu+'">';
				}
			}},
			{field:'tambah',title:'Tambah',align:'center', width:80, formatter:function(val, row){
				var s = row.tipe!=null ? row.tipe : row.menu;
				if (s.search("aporan")>0 || s.search("aturan")>0) {
					return '';
				} else {
					if (val==0 || val==1) {
						var checked = val==0 ? '' : 'checked';
						return '<input type="checkbox" '+checked+' name="cb_tambah[]"  onchange="cek_detail(\'tambah\', \'cb_tambah\', \''+row.id+'\')" id="cb_tambah_'+row.id+'" value="'+row.tipe+'">';
					} else {
						return '<input type="checkbox" name="tambah[]" onchange="cek_header(\'tambah\', \'cb_tambah\', \''+row.menu+'\')" value="'+row.menu+'">';
					}
				}
			}},
			{field:'ubah',title:'Ubah',align:'center',width:80, formatter:function(val, row){
				var s = row.tipe!=null ? row.tipe : row.menu;
				if (s.search("aporan")>0 || s.search("aturan")>0) {
					return '';
				} else {
					if (val==0 || val==1) {
						var checked = val==0 ? '' : 'checked';
						return '<input type="checkbox" '+checked+' name="cb_ubah[]"  onchange="cek_detail(\'ubah\', \'cb_ubah\', \''+row.id+'\')" id="cb_ubah_'+row.id+'" value="'+row.tipe+'">';
					} else {
						return '<input type="checkbox" name="ubah[]" onchange="cek_header(\'ubah\', \'cb_ubah\', \''+row.menu+'\')" value="'+row.menu+'">';
					}
				}
			}},
			{field:'hapus',title:'Hapus',align:'center',width:80, formatter:function(val, row){
				var s = row.tipe!=null ? row.tipe : row.menu;
				if (s.search("aporan")>0 || s.search("aturan")>0) {
					return '';
				} else {
					if (val==0 || val==1) {
						var checked = val==0 ? '' : 'checked';
						return '<input type="checkbox" '+checked+' name="cb_hapus[]"  onchange="cek_detail(\'hapus\', \'cb_hapus\', \''+row.id+'\')" id="cb_hapus_'+row.id+'" value="'+row.tipe+'">';
					} else {
						return '<input type="checkbox" name="hapus[]" onchange="cek_header(\'hapus\', \'cb_hapus\', \''+row.menu+'\')" value="'+row.menu+'">';
					}
				}
			}},
		]],
		onLoadSuccess:function(row, data){
			$(':checkbox').prop('disabled', true);
		}
	});

	reset_data();
});

$("#btn_tambah").click(function(){
	before_add();
});
$("#btn_ubah").click(function(){
	before_edit();
});
$("#btn_simpan").click(function(){
	simpan();
});
$("#btn_hapus").click(function(){
	before_delete();
});
$("#btn_refresh").click(function(){
	reset_data();
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
			$.messager.confirm('Confirm', 'You don\'t Have "Add" Permissions, Do You Want To Continue With Authorization?', function(r){
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
			$.messager.confirm('Confirm', 'You don\'t Have "Edit" Permissions, Do You Want To Continue With Authorization?', function(r){
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
			$.messager.confirm('Confirm', 'You don\'t Have "Delete" Permissions, Do You Want To Continue With Authorization?', function(r){
				if (r) $('#form_login').dialog('open');
			});
		}
	});
}

function tambah(){
	if ($("#btn_simpan").val()=='') {
		$("#view_browse_user").hide();
		$("#view_temp_user").show();

		$('#form_input').form('clear');

		edit_mode('insert');
	}
}
function ubah() {
	var act = $('[name=act]').val();
	if (act=='') {
		var kode = $('#USERID_CG').combogrid('getValue');

		if (kode=='') {
			$.messager.alert('Warning','Please Select User Name!','warning');
		} else {
			$('#USERID_CG').combogrid('disable');
			edit_mode('edit');
		}
	}
}
function simpan() {
	var isValid = $('#form_input').form('validate');
	if (isValid){
		$('#data_detail').val(JSON.stringify($('#menu_tree').treegrid('getData')));
		$('#data_lokasi').val(JSON.stringify($('#table_data_lokasi').datagrid('getChecked')));

		$('#USERID_CG').combogrid('enable');
		var data_form = $("#form_input :input").serialize();
		var act = $('[name=act]').val();
		if (act=='insert' || act=='edit') {
			$('#USERID_CG').combogrid('disable');
			$.ajax({
				type: 'POST',
				dataType: 'json',
				url: "data/process/proses_master.php",
				data: 'table=user&'+data_form,
				cache: false,
				success: function(msg){
					if (msg.success) {
						$("#view_browse_user").show();
						$("#view_temp_user").hide();

						$('#USERID_CG').combogrid('enable').combogrid('grid').datagrid('reload');
						$('#USERID_CG').combogrid('setValue', msg.userid);

						view_mode();

						$.messager.alert('Info','Save Data Successfully!','info');
					} else {
						$.messager.alert('Error', msg.errorMsg, 'error');
					}
				}
			});
		}
	}
}
function hapus () {
	var user = $('#USERID_CG').combogrid('getValue');
	if (user != '') {
		$.messager.confirm('Warning', 'Are You Sure Want to Delete This Data?', function(r){
			if (r){
				$.ajax({
					type: 'POST',
					dataType: 'json',
					url: "data/process/proses_master.php",
					data: "act=delete&table=user&userid="+user,
					cache: false,
					success: function(msg){
						if (msg.success) {
							reset_data();

							$('#USERID_CG').combogrid('enable').combogrid('grid').datagrid('reload');
							$('#menu_tree').treegrid('load', {kode:''});
						} else {
							$.messager.alert('Error', msg.errorMsg, 'error');
						}
					}
				});
			}
		});
	}
}

function select_user(userid) {
	$.ajax({
		type: 'POST',
		dataType:'json',
		url: "data/process/proses_master.php",
		data: "act=view&table=user&userid="+userid,
		cache: false,
		success: function(msg){
			if (msg.success) {
				$('#form_input').form('load',msg.data);

				$('#menu_tree').treegrid('load', {kode:userid});

				var rows = $('#table_data_lokasi').datagrid('getRows');
				var ln = rows.length;

				for (var i = 0; i < ln; i++) {

					var data = msg.data_lokasi;
					var ln1 = data.length;

					for (var j = 0; j < ln1; j++) {
						if (rows[i].KODELOKASI == data[j].KODELOKASI) {
							$('#table_data_lokasi').datagrid('checkRow', i);
							break;
						}
					}
				}
				//$('#table_data_lokasi').datagrid('loadData', msg.data_lokasi);
			} else {
				$.messager.alert('Error', msg.errorMsg, 'error');
			}
		}
	});
}

function reset_data () {
	$("#view_browse_user").show();
	$("#view_temp_user").hide();

	$('#USERID_CG').combogrid('enable');
	$('#form_input').form('clear').form('disableValidation');

	view_mode();
}

function cek_header(a,b,tipe){
	var checked = false;
	$('[name="'+a+'[]"]').each(function(){
		if ($(this).val()==tipe) {
			checked = $(this).prop('checked');
		}
	});

	$('[name="'+b+'[]"]').each(function(){
		if ($(this).val()==tipe) {
			$(this).prop('checked', checked);

			var str = ($(this).prop('id')).split("_");
			if (a=='hakakses') {
				$('#menu_tree').treegrid('update',{
					id: str[2],
					row: {
						hakakses: checked==true ? 1 : 0,
					}
				});
			} else if (a=='tambah') {
				$('#menu_tree').treegrid('update',{
					id: str[2],
					row: {
						tambah: checked==true ? 1 : 0,
					}
				});
			} else if (a=='ubah') {
				$('#menu_tree').treegrid('update',{
					id: str[2],
					row: {
						ubah: checked==true ? 1 : 0,
					}
				});
			} else if (a=='hapus') {
				$('#menu_tree').treegrid('update',{
					id: str[2],
					row: {
						hapus: checked==true ? 1 : 0,
					}
				});
			}
 		}
	});
}

function cek_detail(a,b,id){
	var checked = false;
	var tipe = '';
	var h = 0;
	var j = 0;
	$('[name="'+b+'[]"]').each(function(){
		var str = ($(this).prop('id')).split("_");
		if (str[2]==id) {
			checked = $(this).prop('checked');
			tipe = $(this).val();
			if (a=='hakakses') {
				$('#menu_tree').treegrid('update',{
					id: str[2],
					row: {
						hakakses: checked==true ? 1 : 0,
					}
				});
			} else if (a=='tambah') {
				$('#menu_tree').treegrid('update',{
					id: str[2],
					row: {
						tambah: checked==true ? 1 : 0,
					}
				});
			} else if (a=='ubah') {
				$('#menu_tree').treegrid('update',{
					id: str[2],
					row: {
						ubah: checked==true ? 1 : 0,
					}
				});
			} else if (a=='hapus') {
				$('#menu_tree').treegrid('update',{
					id: str[2],
					row: {
						hapus: checked==true ? 1 : 0,
					}
				});
			}
		}
	});

	$('[name="'+b+'[]"]').each(function(){
		if ($(this).val()==tipe){
			j++;
			if ($(this).prop('checked')) {
				h++;
			}
		}
	});

	$('[name="'+a+'[]"]').each(function(){
		if ($(this).val()==tipe) {
			$(this).prop('checked', j==h ? true : false);
		}
	});
}

function edit_mode(mode){
	$("#btn_ubah, #btn_tambah, #btn_hapus").linkbutton('disable');

	$("#btn_simpan").linkbutton('enable');
	$('[name=act]').val(mode);
	$('#mode').val(mode=='insert' ? 'tambah' : 'ubah');

	$('#form_input .label_input').textbox('readonly', false);
	$('#USERID_CG').combogrid('disable');

	$('#form_input').form('enableValidation');

	$(':checkbox').prop('disabled', false);
}

function view_mode() {
	$("#btn_ubah, #btn_tambah, #btn_hapus").linkbutton('enable');

	$("#btn_simpan").linkbutton('disable');
	$('[name=act]').add($('#mode')).val('');
	$('#form_input .label_input').textbox('readonly');
	$('#USERID_CG').combogrid('textbox').prop('disabled', false);

	$(':checkbox').prop('disabled', true);
}
</script>