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
				<div data-options="name:'namapromo'">Nama Promo</div>
				<div data-options="name:'kodepromo'">Kode Promo</div>
			</div>
		</div>
	</div>
	<div data-options="region:'center',">
		<table id="table_data" idField="KODEPROMO"></table>
	</div>
</div>

<div id="form_input" style="width:auto">
	<input type="hidden" name="act">
	<table style="border-collapse:collapse">
		<tr>
			<td>
				<table style="padding:5px">
					<tr>
						<td align="right" id="label_form">Kode Promo</td>
						<td>
							<input name="KODEPROMO" style="width:100px" class="label_input" prompt="Auto Generate" readonly validType='length[0,20]'>
							<label id="label_form"><input type="checkbox" id="STATUS" name="STATUS" value="1"> Aktif</label>
						</td>
					</tr>
					<tr>
						<td align="right" id="label_form">Nama Promo</td>
						<td><input name="NAMAPROMO" style="width:300px" class="label_input" required="true" validType='length[0,100]'></td>
					</tr>
					<tr>
						<td align="right" id="label_form"><input type="radio" name="rdJenis" value="lblpersentase" checked="checked"> Persentase</td>
						<td><input name="PERSENTASE" id="PERSENTASE" style="width:50px;" class="number" min="0" max="100">%</td>
					</tr>
					<tr>
						<td align="right" id="label_form"><input type="radio" name="rdJenis" value="lblamount"> Amount (Rp)</td>
						<td><input name="AMOUNT" id="AMOUNT" style="width:100px;" class="number" min="0"></td>
					</tr>
					<tr>
						<td align="right" id="label_form">Tgl Berlaku</td>
						<td id="label_form"> <input name="TGLBERLAKUAWAL" id="TGLBERLAKUAWAL" class="date"/>&nbsp;s/d&nbsp;<input name="TGLBERLAKUAKHIR" id="TGLBERLAKUAKHIR" class="date"/>
						</td>
					</tr>
					<tr>
						<td align="right" valign="top" id="label_form">Catatan</td>
						<td><textarea name="REMARK" style="width:310px; height:60px" class="label_input" multiline="true" validType='length[0,300]'></textarea></td>
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

function tambah() {
	$('#form_input').dialog('open').dialog('setTitle', 'Tambah | <?=$menu?>');
	$('[name=act]').val('insert');
	$('[name=KODEPROMO]').prop('readonly', false);
	$('.number').numberbox('setValue', 0);
	$('.date').datebox('setValue', date_format());
	$('#STATUS').prop('checked', true);

	$('[name=rdJenis]').filter(function(){
	  return $(this).val()=='lblpersentase';
	}).prop('checked', true);

	$('#PERSENTASE').numberbox('readonly',false);
	$('#AMOUNT').numberbox('readonly');

	$('#lbl_kasir, #lbl_tanggal').html('');
}
function ubah() {
	var row = $('#table_data').datagrid('getSelected');
	if (row) {
		$('#form_input').dialog('open').dialog('setTitle', 'Ubah | <?=$menu?>');
		$('#form_input').form('load',row);

		$('[name=act]').val('edit');

		$('[name=KODEPPROMO]').prop('readonly', true);
		$('#table_data_detail').datagrid('uncheckAll');

		if (row.PERSENTASE>0) {
			$('[name=rdJenis]').filter(function(){
				return $(this).val()=='lblpersentase';
			}).prop('checked', true);
			$('#PERSENTASE').numberbox('readonly',false);
			$('#AMOUNT').numberbox('readonly');
		} else {
			$('[name=rdJenis]').filter(function(){
				return $(this).val()=='lblamount';
			}).prop('checked', true);
			$('#PERSENTASE').numberbox('readonly');
			$('#AMOUNT').numberbox('readonly',false);
		}

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
		act = $('[name=act]').val();
		$.ajax({
			type: 'POST',
			url: "data/process/proses_master.php",
			data: "table=promo&act="+act+"&"+$('#form_input :input').serialize(),
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
function hapus () {
	var row = $('#table_data').datagrid('getSelected');
	if (row){
		$.messager.confirm('Confirm','Anda Yakin Menghapus Data Ini ?',function(r){
			if (r){
				$.post('data/process/proses_master.php',{act:'delete', table:'promo', id:row.KODEPROMO},function(msg){
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
		url: 'config/datagrid.php?table=promo',
		rowStyler: function(index,row){
			if (row.STATUS == 0) return 'background-color:#a8aea6';
		},
		frozenColumns:[[
			{field:'KODEPROMO',title:'Kode',width:120, sortable:true,},
			{field:'NAMAPROMO',title:'Nama',width:200, sortable:true,},
		]],
		columns:[[
			{field:'PERSENTASE',title:'Persentase (%)',width:90, formatter:format_amount, sortable:true, align:'right',},
			{field:'AMOUNT',title:'Amount (Rp)',width:100, formatter:format_amount, sortable:true, align:'right',},
			{field:'TGLBERLAKUAWAL',title:'Tgl. Berlaku Awal',width:110, sortable:true, formatter:ubah_tgl_indo, align:'center',},
			{field:'TGLBERLAKUAKHIR',title:'Tgl. Berlaku Akhir',width:110, sortable:true, formatter:ubah_tgl_indo, align:'center',},
			{field:'REMARK',title:'Catatan',width:200, sortable:true},
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

$('[name=rdJenis]').change(function(){
	var val = $(this).val();
	if (val=='lblpersentase') {
		$('#PERSENTASE').numberbox('readonly',false);
		$('#AMOUNT').numberbox('readonly');
		$('#AMOUNT').numberbox('setValue', 0);

	} else if (val=='lblamount') {
		$('#PERSENTASE').numberbox('readonly');
		$('#AMOUNT').numberbox('readonly',false);
		$('#PERSENTASE').numberbox('setValue', 0);
	}
});
</script>