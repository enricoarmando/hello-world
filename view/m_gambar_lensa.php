<?php
if (empty($_SESSION['user'])) header('Location: ../../../index.php');
?>
<style>
.tree-icon{
	display:none;
}
</style>
<div class="easyui-layout" style="width:100%;height:100%" fit="true">
	<div data-options="region:'north'" style="height:40px;padding:5px;">
		<a id="btn_tambah" href="#" class="easyui-linkbutton" data-options="iconCls:'icon-add', plain:true">Tambah</a>
		<a id="btn_ubah" href="#" class="easyui-linkbutton" data-options="iconCls:'icon-edit', plain:true">Ubah / F4</a>
		<a id="btn_hapus" href="#" class="easyui-linkbutton" data-options="iconCls:'icon-cancel', plain:true">Hapus</a>
		<a id="btn_refresh" href="#" class="easyui-linkbutton" data-options="iconCls:'icon-reload', plain:true">Refresh</a>
		<div style="float:right">
			<input class="easyui-searchbox" data-options="prompt:'Silahkan Ketikan Kata Kunci Pada Bagian Pencarian',menu:'#mm',searcher:do_search" style="width:300px"></input>
			<div id="mm">
				<div data-options="name:'a.namalensa'">Nama</div>
				<div data-options="name:'a.kodelensa'">Kode</div>
			</div>
		</div>
	</div>
	<div data-options="region:'center',">
		<table id="table_data" idField="KODELENSA"></table>
	</div>
</div>

<form id="form_input" enctype="multipart/form-data" method="post" style="width:500px">
	<input type="hidden" name="act">
	<input type="hidden" name="GAMBAR">

	<table tyle="padding:2px" border="0">
		<tr>
			<td align="right" id="label_form" style="width:100px">Kode</td>
			<td>
				<input name="KODELENSA" style="width:150px" class="label_input" prompt="Auto Generate" readonly>
				<label id="label_form"><input type="checkbox" id="STATUS" name="STATUS" value="1"> Aktif</label>
			</td>
		</tr>
		<tr>
			<td align="right" id="label_form">Nama</td>
			<td><input name="NAMALENSA" style="width:200px" class="label_input" required="true" validType='length[0,100]'></td>
		</tr>
		<tr>
			<td align="right" id="label_form">Urutan</td>
			<td><input name="URUTAN" style="width:50px" class="easyui-numberspinner"></td>
		</tr>
		<tr>
			<td></td>
			<td>
				<div>
					<fieldset style="border:1px solid #d8d8d8; width:250px; height:180px; padding:0px">
						<legend>Preview Foto</legend>
						<img id="preview-image" style="width:100%; height:100%"/>
					</fieldset>
					<input id="FILEGAMBAR" name="FILEGAMBAR" class="easyui-filebox" data-options="required:true,buttonIcon:'icon-search',buttonText:'Foto'" style="width:255px">
				</div>
			</td>
		</tr>
	</table>
</form>

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
<input type="hidden" id="mode">

<script>
$(document).ready(function(){
	create_form_login();

	$('#form_input').form({
        url:'data/process/proses_master.php?table=gambar_lensa',
        ajax:true,
        iframe:false,
        success: function(data){
			$.messager.progress('close');

			var msg = JSON.parse(data);

			if (msg.success){
				var act = $('[name=act]').val();

				if (act=='insert') tambah();
				else $('#form_input').dialog('close');

				$('#table_data').datagrid('reload');    // reload the user data

				$('#preview-image').removeAttr('src').replaceWith($('#preview-image').clone());
			} else {
				$.messager.alert('Error', msg.errorMsg, 'error');
			}
        },
    });

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

				get_data_user(<?="'".$_GET['kode']."'"?>, function(data){
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

	$('#FILEGAMBAR').filebox({
		accept: 'image/*',
		onChange:function(newVal, oldVal){
			var input = $(this).next().find('.textbox-value')[0];

			if (input.files && input.files[0]) {
				var reader = new FileReader();

				reader.onload = function (e) {
					$('#preview-image').attr('src', e.target.result);
				}

				reader.readAsDataURL(input.files[0]);
			}
		}
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

function before_add () {
	$('#mode').val('tambah');
	get_akses_user(<?="'".$_GET['kode']."'"?>, function(data){
		if (data.TAMBAH==1) {
			tambah();
		} else {
			$.messager.confirm('Confirm', 'Anda Tidak Memiliki Hak Akses "Tambah", Anda Akan Melanjutkan Dengan Otorisasi ?', function(r){
				if (r) $('#form_login').dialog('open');
			});
		}
	});
}
function before_edit () {
	$('#mode').val('ubah');
	get_akses_user(<?="'".$_GET['kode']."'"?>, function(data){
		if (data.UBAH==1) {
			ubah();
		} else {
			$.messager.confirm('Confirm', 'Anda Tidak Memiliki Hak Akses "Ubah", Anda Akan Melanjutkan Dengan Otorisasi ?', function(r){
				if (r) $('#form_login').dialog('open');
			});
		}
	});
}
function before_delete () {
	$('#mode').val('hapus');
	get_akses_user(<?="'".$_GET['kode']."'"?>, function(data){
		if (data.HAPUS==1) {
			hapus();
		} else {
			$.messager.confirm('Confirm', 'Anda Tidak Memiliki Hak Akses "Hapus", Anda Akan Melanjutkan Dengan Otorisasi ?', function(r){
				if (r) $('#form_login').dialog('open');
			});
		}
	});
}

function tambah () {
	$('#form_input').dialog('open').dialog('setTitle', 'Tambah | <?=$menu?>');
	$('[name=act]').val('insert');
	$('#STATUS').prop('checked', true);
	$('#lbl_kasir, #lbl_tanggal').html('');

	$('#preview-image').removeAttr('src').replaceWith($('#preview-image').clone());
}

function ubah () {
	var row = $('#table_data').datagrid('getSelected');
	if (row) {
		$('#form_input').dialog('open').dialog('setTitle', 'Ubah | <?=$menu?>');
		$('#form_input').form('load',row);

		$('[name=act]').val('edit');

		// remove image
		$('#preview-image').removeAttr('src').replaceWith($('#preview-image').clone());
		
		// load gambar
		if (row.GAMBAR != '') {
			var image = $('#preview-image')[0];
			var downloadingImage = new Image();
			downloadingImage.onload = function(){
				image.src = this.src;
			};

			var tempurl = '<?=$_SERVER['REQUEST_URI']?>';
			var res = tempurl.split("/");

			var url = '';
			for (var i = 0; i < (res.length - 1); i++) {
				url += res[i] + '/';
			}

			downloadingImage.src = "http://<?=$_SERVER['HTTP_HOST']?>"+url+"gambar-lensa/"+row.GAMBAR+'?'+ new Date().getTime();
			// akhir script load gambar
		}
		
		$('#lbl_kasir').html(row.USERENTRY);
		$('#lbl_tanggal').html(row.TGLENTRY);
	}
}
function simpan () {
	var isValid = $('#form_input').form('validate');
	if (isValid) {
		$('#form_input').submit();
		/*$.ajax({
			type: 'POST',
			url: "data/process/proses_master.php",
			data: "table=gambar_lensa&"+$('#form_input :input').serialize(),
			dataType: 'json',
			beforeSend : function (){
				$.messager.progress();
			},
			success: function(msg){
				$.messager.progress('close');
				if (msg.success){
					var act = $('[name=act]').val();

					if (act=='insert') tambah();
					else $('#form_input').dialog('close');

					$('#table_data').datagrid('reload');    // reload the user data
				} else {
					$.messager.alert('Error', msg.errorMsg, 'error');
				}
			}
		});*/
	}
}
function hapus () {
	var row = $('#table_data').datagrid('getSelected');
	if (row){
		$.messager.confirm('Confirm','Anda Yakin Menghapus Data Ini ?',function(r){
			if (r){
				$.post('data/process/proses_master.php',{act:'delete', table:'gambar_lensa', id:row.KODELENSA},function(msg){
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

function buat_table () {
	$('#table_data').datagrid({
		fit:true,
		singleSelect:true,
		striped:true,
		pagination:true,
		pageSize:20,
		url: 'config/datagrid.php?table=gambar_lensa',
		rowStyler: function(index,row){
			if (row.STATUS == 0) return 'background-color:#a8aea6';
		},
		columns:[[
			{field:'KODELENSA',title:'Kode',width:130, sortable:true,},
			{field:'NAMALENSA',title:'Nama',width:200, sortable:true,},
			{field:'Gambar',title:'Gambar',width:250,sortable:true,formatter:function(val, row, index){
				if (row.GAMBAR) {
					return '<img src="gambar-lensa/'+row.GAMBAR+'">';
				}
			}},
			{field:'URUTAN',title:'Urutan',width:70, align:'center', sortable:true,},
			{field:'USERENTRY',title:'User',width:70, sortable:true},
			{field:'TGLENTRY',title:'Tgl Input',width:75, sortable:true, formatter:ubah_tgl_indo, align:'center',},
			{field:'STATUS',title:'Status', align:'center', sortable:true, formatter:format_checked,}
		]],
		onDblClickRow:function(index,row) {
			before_edit();
		},
	});
}
</script>