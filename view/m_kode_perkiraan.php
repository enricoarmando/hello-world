<?php
if (empty($_SESSION['user'])) header('Location: ../../../index.php');
?>
<style>
.tree-icon{
	display:none;
}
</style>

<div class="easyui-tabs" plain='true' fit="true" data-options="
	onSelect:function(title) {
		if (title=='Tree View') {
			$('#tv_kode_perkiraan').tree('reload')
		}
	}">
	<div title="Grid View">
		<div class="easyui-layout" style="width:100%;height:100%" fit="true">
			<div data-options="region:'north'" style="height:40px;padding:5px;">
				<a id="btn_tambah" href="#" class="easyui-linkbutton" data-options="iconCls:'icon-add', plain:true">Tambah</a>
				<a id="btn_ubah" href="#" class="easyui-linkbutton" data-options="iconCls:'icon-edit', plain:true">Ubah / F4</a>
				<a id="btn_hapus" href="#" class="easyui-linkbutton" data-options="iconCls:'icon-cancel', plain:true">Hapus</a>
				<a id="btn_refresh" href="#" class="easyui-linkbutton" data-options="iconCls:'icon-reload', plain:true">Refresh</a>
				<div style="float:right" hidden>
					<input class="easyui-searchbox" data-options="prompt:'Silahkan Ketikan Kata Kunci Pada Bagian Pencarian',menu:'#mm',searcher:do_search" style="width:300px"></input>
					<div id="mm">
						<div data-options="name:'kodeperkiraan'">Kode Akun</div>
						<div data-options="name:'namaperkiraan'">Nama Akun</div>
					</div>
				</div>
			</div>
			<div data-options="region:'center',">
				<table id="table_data" idField="KODEPERKIRAAN"></table>
			</div>
		</div>					
	</div>
	<div title="Tree View">
		<div class="easyui-layout" style="width:100%;height:100%" fit="true">
			<div data-options="region:'center',">
				<div id="tv_kode_perkiraan" style="height:454px"></div>
			</div>
		</div>
	</div>
</div>

<div id="form_input" style="width:835px">
	<input type="hidden" name="act">
	<table style="border-collapse:collapse">
		<tr>
			<td>
				<table style="padding:5px">
					<tr>
						<td align="right" id="label_form">Kode</td>
						<td>
							<input id="KODEPERKIRAAN" name="KODEPERKIRAAN" style="width:100px" class="label_input" required="true" validType='length[0,20]'> 
							<label id="label_form"><input type="checkbox" id="STATUS" name="STATUS" value="1"> Aktif</label>
						</td>
					</tr>
					<tr>
						<td align="right" id="label_form">Nama</td>
						<td><input name="NAMAPERKIRAAN" style="width:400px" class="label_input" required="true" validType='length[0,100]'></td>
					</tr>
					<tr>
						<td align="right" id="label_form">Induk</td>
						<td><input id="INDUK" name="INDUK" style="width:80px"> <input name="NAMAINDUK" id="NAMAINDUK" style="width:250px" class="label_input" readonly></td>
					</tr>
					<tr>
						<td align="right" id="label_form">Kelompok</td>
						<td>
							<select name="KELOMPOK" style="width:200px" class="easyui-combobox" panelHeight="auto">
								<option value=""></option>
								<option value="NERACA-AKTIVA">Neraca-Aktiva</option>
								<option value="NERACA-PASIVA">Neraca-Pasiva</option>
								<option value="LABA/RUGI-PENAMBAH">Laba/Rugi-Penambah</option>
								<option value="LABA/RUGI-PENGURANG">Laba/Rugi-Pengurang</option>
							</select>
						</td>
					</tr>
					<tr>
						<td align="right" id="label_form">Tipe</td>
						<td>
							<select name="TIPE" style="width:80px" class="easyui-combobox" panelHeight="auto">
								<option value="HEADER">Header</option>
								<option value="DETAIL">Detail</option>
							</select>
						</td>
					</tr>
					<tr>
						<td align="right" id="label_form">Saldo</td>
						<td>
							<select name="SALDO" style="width:80px" class="easyui-combobox" panelHeight="auto">
								<option value=""> - </option>
								<option value="DEBET">Debet</option>
								<option value="KREDIT">Kredit</option>
							</select>
						</td>
					</tr>
					<tr>
						<td align="right" id="label_form">Jenis Kas-Bank</td>
						<td id="label_form">
							<select name="KASBANK" style="width:80px" class="easyui-combobox" panelHeight="auto">
								<option value="0"> - </option>
								<option value="1">Kas</option>
								<option value="2">Bank</option>
							</select>
							
							&nbsp;
							Kode Kas-Bank
							<input name="KODEKASBANK" style="width:50px" class="label_input" validType='length[0,20]'>
						</td>
					</tr>
					<tr>
						<td align="right" id="label_form">Currency</td>
						<td><input id="KODECURRENCY" name="KODECURRENCY" style="width:80px"></td>
					</tr>
				</table>
			</td>
			<td>
				<table style="width:330px; height:210px" title="Lokasi" id="table_data_detail"></table>
			</td>
		</tr>
	</table>
	
	<input type="hidden" id="data_detail" name="data_detail">
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
<input type="hidden" id="mode">

<script type="text/javascript" src="script/jquery-easyui/plugins/datagrid-filter.min.js"></script>

<script>
$(document).ready(function(){
	create_form_login();
	
	buat_table();
	buat_table_detail();
	
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
	
	$('[name=INDUK]').combogrid({
		panelWidth:360,
		mode:'remote',
		idField:'KODE',
		textField:'KODE',
		sortName:'KODE',
		sortOrder:'asc',
		url:'config/combogrid.php?table=kode_perkiraan_header',
		columns:[[
			{field:'KODE',title:'Kode Akun',width:80},
			{field:'NAMA',title:'Nama Akun',width:250}
		]],
		onChange:function(){
			var data = $(this).combogrid('grid').datagrid('getSelected');
			
			$('#NAMAINDUK').textbox('setValue', data ? data.NAMA : '');
		}
	});
	$('[name=KODECURRENCY]').combogrid({
		panelWidth:360,
		mode:'local',
		idField:'KODE',
		textField:'KODE',
		sortName:'KODE',
		sortOrder:'asc',
		url:'config/combogrid.php?table=currency',
		columns:[[
			{field:'KODE',title:'Kode',width:80},
			{field:'NAMA',title:'Nama',width:250}
		]],
	});
	$('#tv_kode_perkiraan').tree({
		lines:true,
		animate:false,
		url : 'config/treegrid.php?table=kode_perkiraan',
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
	$('#KODEPERKIRAAN').textbox('readonly', false);
	$('#STATUS').prop('checked', true);
	
	$('#lbl_kasir, #lbl_tanggal').html('');
}
function ubah () {
	var row = $('#table_data').datagrid('getSelected');
	if (row) {
		$('#form_input').dialog('open').dialog('setTitle', 'Ubah | <?=$menu?>');
		$('#form_input').form('load',row);
		
		$('[name=act]').val('edit');
		$('#KODEPERKIRAAN').textbox('readonly');
		
		$('#table_data_detail').datagrid('uncheckAll');
		
		var rows = $('#table_data_detail').datagrid('getRows');
		var ln = rows.length;
		
		for (var i = 0; i < ln; i++) {
			
			var data = row.detail_lokasi;
			var ln1 = data.length;
			
			for (var j = 0; j < ln1; j++) {
				if (rows[i].KODELOKASI == data[j].KODELOKASI) {
					$('#table_data_detail').datagrid('checkRow', i);
					break;
				}
			}
		}
		
		$('#lbl_kasir').html(row.USERENTRY);
		$('#lbl_tanggal').html(row.TGLENTRY);
	}
}
function simpan () {
	var mode = $("#mode").val();
	
	$('#data_detail').val(JSON.stringify($('#table_data_detail').datagrid('getChecked')));
	
	var isValid = $('#form_input').form('validate');
	if (isValid) {
		$.ajax({
			type: 'POST',
			url: "data/process/proses_master.php", 
			data: "table=kode_perkiraan&"+$('#form_input :input').serialize(),
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
		});
	}
}
function hapus () {
	var row = $('#table_data').datagrid('getSelected');
	if (row){
		$.messager.confirm('Confirm','Anda Yakin Menghapus Data Ini ?',function(r){
			if (r){
				$.post('data/process/proses_master.php',{act:'delete', table:'kode_perkiraan', id:row.KODEPERKIRAAN},function(msg){
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
		remoteFilter:true,
		fit:true,
		singleSelect:true,
		striped:true,
		pagination:true,
		pageSize:20,
		url: 'config/datagrid.php?table=kode_perkiraan',
		rowStyler: function(index,row){  
			if (row.STATUS == 0) return 'background-color:#a8aea6';
		},
		frozenColumns:[[
			{field:'KODEPERKIRAAN',title:'Kode Akun',width:80, sortable:true,},
			{field:'NAMAPERKIRAAN',title:'Nama Akun',width:200, sortable:true,},
		]],
		columns:[[
			{field:'INDUK',title:'Induk',width:100, sortable:true,},
			{field:'KELOMPOK',title:'Group',width:150, sortable:true,},
			{field:'SALDO',title:'Saldo',width:80, sortable:true,},
			{field:'TIPE',title:'Tipe',width:80, sortable:true,},
			{field:'KASBANK',title:'Kas/Bank',width:80, sortable:true,formatter:function(val, row){
				if (val==0) return '';
				else if (val==1) return 'Kas';
				else if (val==2) return 'Bank';
			}},
			{field:'KODEKASBANK',title:'Kode Kas/Bank',width:100, sortable:true,},
			{field:'KODECURRENCY',title:'Mata Uang',width:100, sortable:true,},
			{field:'USERENTRY',title:'User',width:70, sortable:true},
			{field:'TGLENTRY',title:'Tgl. Input',width:110, sortable:true, formatter:ubah_tgl_indo, align:'center',},
			{field:'STATUS',title:'Aktif', align:'center', sortable:true, formatter:format_checked,}
		]],
		onDblClickRow:function(index,row) {
			before_edit();
		},
	}).datagrid('enableFilter');
}

function buat_table_detail () {
	$("#table_data_detail").datagrid({
		rownumbers:true,
		singleSelect:true,
		checkOnSelect:false,
		selectOnCheck:false,
		url:'config/datagrid.php?table=semua_lokasi',
		columns:[[
			{field:'ck',title:'',width:30,checkbox:true},
			{field:'KODELOKASI',title:'Kode',width:80},
			{field:'NAMALOKASI',title:'Nama',width:160},
		]],
	});
}
</script>