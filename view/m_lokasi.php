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
				<div data-options="name:'kodelokasi'">Kode</div>
				<div data-options="name:'namalokasi'">Nama</div>
			</div>
		</div>
	</div>
	<div data-options="region:'center',">
		<table id="table_data" idField="KODELOKASI"></table>
	</div>
</div>

<div id="form_input" style="width:550px">
	<input type="hidden" name="act">
	<table style="padding:5px">
		<tr>
			<td align="right" id="label_form">Kode</td>
			<td><input name="KODELOKASI" id="KODELOKASI" style="width:100px" class="label_input" required="true" validType='length[4,4]'>
				<label id="label_form"><input type="checkbox" id="STATUS" name="STATUS" value="1"> Aktif</label>
			</td>
		</tr>
		<tr>
			<td align="right" id="label_form">Nama</td>
			<td><input name="NAMALOKASI" style="width:200px" class="label_input" required="true" validType='length[0,50]'>
				<label id="label_form"><input type="checkbox" id="PUSAT" name="PUSAT" value="1"> Lokasi Pusat</label>
			</td>
		</tr>
		<tr>
			<td align="right" id="label_form">Jenis</td>
			<td><label id="label_form"><input type="radio" name="JENIS" value="INDRA OPTIK" checked="checked" /> INDRA OPTIK</label>
			<label id="label_form"><input type="radio" name="JENIS" value="SUPER EYE"/> SUPER EYE</label></td>
		</tr>
		<tr>
			<td align="right" id="label_form">Nama Perusahaan</td>
			<td><input name="NAMAPERUSAHAAN" style="width:200px" class="label_input" required="true" validType='length[0,100]'></td>
		</tr>
		<tr>
			<td align="right" id="label_form">Alamat</td>
			<td><input name="ALAMAT" style="width:300px" class="label_input" validType='length[0,200]'></td>
		</tr>
		<tr>
			<td align="right" id="label_form">Kota</td>
			<td>
				<input name="KOTA" style="width:150px" class="label_input" validType='length[0,20]'>
				<!--&nbsp;&nbsp;Propinsi
				<input name="PROPINSI" style="width:150px" class="label_input" validType='length[0,100]'>
				&nbsp;&nbsp;Negara
				<input name="NEGARA" style="width:150px" class="label_input" validType='length[0,100]'>
				!-->
			</td>
		</tr>
		<tr>
			<td align="right" id="label_form">Telp</td>
			<td><input name="TELP" style="width:200px" class="label_input" validType='length[0,50]'></td>
		</tr>
		<tr>
			<td align="right" id="label_form">Alamat Perusahaan</td>
			<td><input name="ALAMATPERUSAHAAN" style="width:300px" class="label_input" validType='length[0,200]'></td>
		</tr>
		<tr>
			<td align="right" id="label_form">Kota Perusahaan</td>
			<td>
				<input name="KOTAPERUSAHAAN" style="width:150px" class="label_input" validType='length[0,20]'>
				<!--&nbsp;&nbsp;Propinsi
				<input name="PROPINSI" style="width:150px" class="label_input" validType='length[0,100]'>
				&nbsp;&nbsp;Negara
				<input name="NEGARA" style="width:150px" class="label_input" validType='length[0,100]'>
				!-->
			</td>
		</tr>
		<tr>
			<td align="right" id="label_form">Telp Perusahaan</td>
			<td><input name="TELPPERUSAHAAN" style="width:200px" class="label_input" validType='length[0,50]'></td>
		</tr>
		<tr>
			<td align="right" id="label_form">HP</td>
			<td><input name="HP" style="width:200px" class="label_input" validType='length[0,50]'></td>
		</tr>
		<tr>
			<td align="right" id="label_form">NPWP</td>
			<td><input name="NPWP" style="width:200px" class="label_input" validType='length[0,50]'></td>
		</tr>
		<tr>
			<td align="right" valign="top" id="label_form">Keterangan</td>
			<td><textarea name="CATATAN" style="width:350px; height:80px" class="label_input" multiline="true" validType='length[0,300]'></textarea></td>
		</tr>
	</table>
</div>
<input type="hidden" id="mode" name="mode">

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

function before_add () {
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
function before_edit () {
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
function before_delete () {
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

function tambah () {
	$('#form_input').dialog('open').dialog('setTitle', 'Tambah | <?=$menu?>');
	$('[name=act]').val('insert');
	$('#STATUS').prop('checked', true);
	$('#KODELOKASI').textbox('readonly', false);

	$('#lbl_kasir, #lbl_tanggal').html('');
}
function ubah () {
	var row = $('#table_data').datagrid('getSelected');
	if (row) {
		$('#form_input').dialog('open').dialog('setTitle', 'Ubah | <?=$menu?>');
		$('#form_input').form('load',row);

		$('[name=act]').val('edit');

		$('#lbl_kasir').html(row.USERENTRY);
		$('#lbl_tanggal').html(row.TGLENTRY);

		$('#KODELOKASI').textbox('readonly');
	}
}
function simpan () {
	var isValid = $('#form_input').form('validate');
	if (isValid) {
		act = $('[name=act]').val();
		$.ajax({
			type: 'POST',
			url: "data/process/proses_master.php",
			data: "table=lokasi&"+$('#form_input :input').serialize(),
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
				$.post('data/process/proses_master.php',{act:'delete', table:'lokasi', id:row.KODELOKASI},function(msg){
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
		url: 'config/datagrid.php?table=lokasi',
		rowStyler: function(index,row){
			if (row.STATUS == 0) return 'background-color:#a8aea6';
		},
		frozenColumns:[[
			{field:'KODELOKASI',title:'Kode',width:80, sortable:true,},
			{field:'NAMALOKASI',title:'Nama Lokasi',width:200, sortable:true,},
			{field:'NAMAPERUSAHAAN',title:'Nama Perusahaan',width:200, sortable:true,},
			{field:'PUSAT',title:'Lokasi Pusat', align:'center', sortable:true, formatter:format_checked,},
		]],
		columns:[[
			{field:'ALAMAT',title:'Alamat',width:250, sortable:true,},
			{field:'KOTA',title:'Kota',width:130, sortable:true,},
			{field:'TELP',title:'Telp',width:100, sortable:true, },
			{field:'ALAMATPERUSAHAAN',title:'Alamat Perusahaan',width:250, sortable:true,},
			{field:'KOTAPERUSAHAAN',title:'Kota Perusahaan',width:130, sortable:true,},
			{field:'TELPPERUSAHAAN',title:'Telp Perusahaan',width:100, sortable:true, },			
			{field:'HP',title:'HP',width:100, sortable:true, },
			{field:'NPWP',title:'NPWP',width:140, sortable:true, },
			{field:'REMARK',title:'Catatan',width:250, sortable:true,},
			{field:'USERENTRY',title:'User',width:70, sortable:true},
			{field:'TGLENTRY',title:'Tgl. Input',width:110, sortable:true, formatter:ubah_tgl_indo, align:'center',},
			{field:'STATUS',title:'Aktif', align:'center', sortable:true, formatter:format_checked,}
			//{field:'PROPINSI',title:'Propinsi',width:130, sortable:true,},
			//{field:'NEGARA',title:'Negara',width:130, sortable:true,},
		]],
		onDblClickRow:function(index,row) {
			before_edit();
		},
	}).datagrid('enableFilter');
}
</script>
