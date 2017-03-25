<?php
if (empty($_SESSION['user'])) header('Location: ../../../index.php');
?>

<div class="easyui-layout" style="width:100%;height:100%" fit="true">
	<div data-options="region:'north'" style="height:40px;padding:5px;">
		<a id="btn_tambah" href="#" class="easyui-linkbutton" data-options="iconCls:'icon-add', plain:true">Tambah</a>
		<a id="btn_ubah" href="#" class="easyui-linkbutton" data-options="iconCls:'icon-edit', plain:true">Ubah / F4</a>
		<a id="btn_hapus" href="#" class="easyui-linkbutton" data-options="iconCls:'icon-cancel', plain:true">Hapus</a>
		<a id="btn_refresh" href="#" class="easyui-linkbutton" data-options="iconCls:'icon-reload', plain:true">Refresh</a>
		<div style="float:right">
			<input class="easyui-searchbox" data-options="prompt:'Silahkan Ketikan Kata Kunci Pada Bagian Pencarian',menu:'#mm',searcher:do_search" style="width:300px"></input>
			<div id="mm">
				<div data-options="name:'namacurrency'">Mata Uang</div>
				<div data-options="name:'kodecurrency'">Kode</div>            
			</div>
		</div>
	</div>
	<div data-options="region:'center',">
		<table id="table_data" idField="KODECURRENCY"></table>
	</div>
</div>

<div id="form_input" style="width:600px">
	<input type="hidden" name="act">
	<input type="hidden" name="mode" id="mode">
	<input type="hidden" name="data_detail" id="data_detail">
	
	<table style="padding:5px">
		<tr>
			<td align="right" id="label_form">Kode</td>
			<td>
				<input name="KODECURRENCY" id="KODECURRENCY" style="width:100px" class="label_input" required="true" validType='length[0,20]'> 
				<label id="label_form"><input type="checkbox" name="TANDA" value="1"> Currency Utama</label>&nbsp;&nbsp;
				<label id="label_form"><input type="checkbox" id="STATUS" name="STATUS" value="1"> Aktif</label>
			</td>
		</tr>
		<tr>
			<td align="right" id="label_form">Keterangan</td>
			<td><input name="NAMACURRENCY" style="width:200px" class="label_input" required="true" validType='length[0,100]'></td>
		</tr>
		<tr>
			<td align="right" id="label_form">Simbol</td>
			<td><input name="SIMBOL" style="width:40px" class="label_input" data-options="required:true,fontTransform:'normal'" validType='length[0,100]'></td>
		</tr>
	</table>
	
	<!-- <table id="table_data_detail" title="Rate" style="width:250; height:300px"></table> !-->
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
<script>
$(document).ready(function(){
	create_form_login();
	
	buat_table();
	//buat_table_detail();
	
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
/*
shortcut.add('Shift+a',function() {
	mode = $('#mode').val();
	if (mode=='ubah' || mode=='tambah') $("#table_data_detail").datagrid('cancelRow').datagrid('addRow', 0);
});
shortcut.add('Shift+s',function() {
	mode = $('#mode').val();
	if (mode=='ubah' || mode=='tambah') $('#table_data_detail').datagrid('saveRow');
});
shortcut.add('Shift+d',function() {
	mode = $('#mode').val();
	index = get_index('#table_data_detail');
	if (mode=='ubah' || mode=='tambah' && (index>=0)) $('#table_data_detail').datagrid('destroyRow', index);
});
shortcut.add('Shift+e',function() {
	index = get_index('#table_data_detail');
	if (index>=0) $('#table_data_detail').datagrid('cancelRow').datagrid('editRow', index);
});
shortcut.add('Shift+c',function() {
	mode = $('#mode').val();
	if (mode=='ubah' || mode=='tambah') $("#table_data_detail").datagrid('cancelRow');
});
*/
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
	$('#mode').val('tambah');
	$('#STATUS').prop('checked', true);
	//$('#table_data_detail').datagrid('loadData', []);
	$('#KODECURRENCY').textbox('readonly', false);

	$('#lbl_kasir, #lbl_tanggal').html('');
}

function ubah () {
	var row = $('#table_data').datagrid('getSelected');
	if (row) {
		$('#form_input').dialog('open').dialog('setTitle', 'Ubah | <?=$menu?>');
		$('#form_input').form('load',row);
		
		$('[name=act]').val('edit');
		$('#mode').val('ubah');
		$('#lbl_kasir').html(row.USERENTRY);
		$('#lbl_tanggal').html(row.TGLENTRY);
		$('#KODECURRENCY').textbox('readonly');
		/*
		$.ajax({
			type: 'POST',
			url: "data/process/proses_master.php", 
			data: "table=currency&act=view_detail&kode="+row.KODECURRENCY,
			dataType: 'json',
			beforeSend : function (){
				$.messager.progress();
			},
			success: function(msg){
				$.messager.progress('close');
				if (msg.success){
					$('#table_data_detail').datagrid('loadData', msg.detail);
				} else {
					$.messager.alert('Error', msg.errorMsg, 'error');
				}
			}
		});
		*/
	}
}

function simpan () {
	//$('#data_detail').val(JSON.stringify($('#table_data_detail').datagrid('getRows')));
	
	var isValid = $('#form_input').form('validate');
	if (isValid){
		act = $('[name=act]').val();
		$.ajax({
			type: 'POST',
			url: "data/process/proses_master.php", 
			data: "table=currency&"+$('#form_input :input').serialize(),
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
				$.post('data/process/proses_master.php',{act:'delete', table:'currency', id:row.KODECURRENCY},function(msg){
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
		url: 'config/datagrid.php?table=currency',
		rowStyler: function(index,row){  
			if (row.STATUS == 0) return 'background-color:#a8aea6';
		},
		frozenColumns:[[
			{field:'KODECURRENCY',title:'Kode',width:80, sortable:true,},
			{field:'NAMACURRENCY',title:'Keterangan',width:200, sortable:true,},
			{field:'SIMBOL',title:'Simbol',width:50, sortable:true,},
			{field:'TANDA',title:'Mata Uang Utama.', align:'center', sortable:true, formatter:format_checked,}
		]],
		columns:[[
			{field:'USERENTRY',title:'User',width:70, sortable:true},
			{field:'TGLENTRY',title:'Tgl. Input',width:110, sortable:true, formatter:ubah_tgl_indo, align:'center',},
			{field:'STATUS',title:'Aktif', align:'center', sortable:true, formatter:format_checked,}

		]],
		onDblClickRow:function(index,row) {
			before_edit();
		},
	});
}

/*
function buat_table_detail () {
	$("#table_data_detail").edatagrid({
	    singleSelect:true,
		rownumbers:true,
		toolbar: [{
			text:'Add / Shift+A',
			iconCls:'icon-add',
			handler:function(){
				$("#table_data_detail").edatagrid('cancelRow').edatagrid('addRow', 0);
			}
		},{
			text:'Edit / Shift+E',
			iconCls:'icon-edit',
			handler:function(){
				index = get_index('#table_data_detail');
				if (index>=0) {
					$('#table_data_detail').edatagrid('cancelRow').edatagrid('editRow', index);
				}
			}
		},{
			text:'Cancel / Shift+C',
			iconCls:'icon-undo',
			handler:function(){
				$("#table_data_detail").edatagrid('cancelRow');
			}
		},{
			text:'Save / Shift+S',
			iconCls:'icon-save',
			handler:function(){
				$("#table_data_detail").edatagrid('saveRow');
			}
		},{
			text:'Hapus / Shift+D',
			iconCls:'icon-remove',
			handler:function(){
				$("#table_data_detail").edatagrid('destroyRow');
			}
		}],
		saveUrl: 'data/process/proses_master.php',
		updateUrl: 'data/process/proses_master.php',
		data:[],
		columns:[[
			{field:'tglaktif',title:'Tgl. Aktif',width:90,align:'center',editor:{type:'datebox'}},
			{field:'kurs',title:'Rate (<?=$_SESSION['SIMBOLCURRENCY']?>)',width:100,align:'right',formatter:format_amount,editor:{type:'numberbox'}},
			{field:'act',title:'act',hidden:true,editor:{type:'validatebox'}},
			{field:'table',title:'table',hidden:true,editor:{type:'validatebox'}},
		]],
		onDblClickRow : function (index, data) {
			//$('#table_data_detail').edatagrid('editRow', index);
		},
		onBeforeSave:function(index){
			var save = true;
			
			var dg = '#table_data_detail';
			var e_tglaktif = get_editor(dg, index, 'tglaktif');
			
			var rows = $(dg).datagrid('getRows');
			if (rows.length>1) {
				for (var i=0; i<rows.length; i++) {
					if (i!=index && e_tglaktif.datebox('getValue')==rows[i].tglaktif) {
						save = false;
						break;
					}
				}
			}
			if (!save) {
				$.messager.alert('Error', 'No Duplicate Row', 'error');
			}
			return save;
		},
		onSave: function(index,row) {
			edit_row = false;
			$(this).datagrid('getPanel').panel('panel').focus();
		},
		onCancelEdit: function(index,row) {
			edit_row = false;
			$(this).datagrid('getPanel').panel('panel').focus();
		},
		onDestroy: function(index,row) {
			edit_row = false;
			$(this).datagrid('getPanel').panel('panel').focus();
		},
		onAdd: function(index,row) {
			edit_row = true;
			edit_grid('add', index, row);
		},
		onEdit: function(index,row) {
			edit_row = true;		
			edit_grid('edit', index, row);
		},
		onError: function(index,row){
			edit_row = true;
			$.messager.alert('Error', row.msg, 'error');
		}
	});
	
	datagrid_up_and_down('#table_data_detail');
}

function edit_grid (mode, index, row) {
	var dg = '#table_data_detail';
	
	var e_tglaktif = get_editor(dg, index, 'tglaktif');
	var e_kurs 	   = get_editor(dg, index, 'kurs');
	var e_act	   = get_editor(dg, index, 'act');
	var e_table	   = get_editor(dg, index, 'table');
	
	e_act.val('add_detail');
	e_table.val('currency');
	if (mode=='add') {
		e_kurs.numberbox('setValue', 0);
		e_tglaktif.datebox('setValue', date_format());
	}
	e_tglaktif.datebox('textbox').focus();
}	
*/
</script>