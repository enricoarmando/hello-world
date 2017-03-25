<?php
if (empty($_SESSION['user'])) header('Location: ../../../index.php');
?>

<div class="easyui-layout" style="width:100%;height:100%" fit="true">
	<div data-options="region:'north'" style="height:40px;padding:5px;">
		<a href="#" class="easyui-linkbutton" data-options="iconCls:'icon-save', plain:true" id='btn_simpan' onclick="javascript:simpan()">Simpan</a>
		<a id="btn_refresh" href="#" class="easyui-linkbutton" data-options="iconCls:'icon-reload', plain:true">Refresh</a>
		<div style="float:right" hidden>
			<input class="easyui-searchbox" data-options="prompt:'Silahkan Ketikan Kata Kunci Pada Bagian Pencarian',menu:'#mm',searcher:do_search" style="width:300px"></input>
			<div id="mm">
				<div data-options="name:'alasan'">Printer</div>				
			</div>
		</div>
	</div>
	<div data-options="region:'center'," >
		<!--<table id="table_data"></table>-->
		<div id="form_input">
		<input type="hidden" id="data_detail" name="data_detail">
		</div>
		<table id="table_data_detail" style="height:100%;width:100%;"></table>
	</div>
</div>

<script type="text/javascript" src="script/jquery-easyui/plugins/datagrid-filter.min.js"></script>

<script>
$(document).ready(function(){
	create_form_login();
	//buat_table();
	
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
	load_data_printer();
	
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
	$('#table_data_detail').datagrid('reload');
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
		
		
		
		$('[name=act]').val('edit');		
		$('#lbl_kasir').html(row.USERENTRY);
		$('#lbl_tanggal').html(row.TANGGALENTRY);
	}
}

function simpan() {
	$('#data_detail').val(JSON.stringify($('#table_data_detail').datagrid('getRows')));
	var datanya = $("#form_input :input").serialize();
	isValid = cek_datagrid($('#table_data_detail'));
	
	if (isValid) {
		$.ajax({
			type: 'POST',
			dataType: 'json',
			url: "data/process/proses_master.php",
			data: "table=simpan_printer_function&"+datanya,
			cache: false,
			beforeSend : function (){
				$.messager.progress();
			},
			success: function(msg){
				$.messager.progress('close');
				if (msg.success) {
					$('#form_input').dialog('close');
					$.messager.alert('Info','Simpan Sukses','info');
					$('#table_data_detail').datagrid('reload');
				} else {
					$.messager.alert('Error', msg.errorMsg, 'error');
				}
			}
		});
	}
}

function hapus() {
	var row = $('#table_data_detail').datagrid('getSelected');
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

function buat_table_detail() {
	
	var fungsi_printer = [
	{id:"CASHIER"},{id:"CHANGE TABLE NUMBER"},
	{id:"CHECKER"},{id:"CHECKER GELATO"},
	{id:"MERGE BILL"},{id:"SPLIT BILL"},
	{id:"TRANSFER BILL"},{id:"VOID ITEM"}
	];

	$("#table_data_detail").datagrid({
		showFooter:true,
		singleSelect:true,
		rownumbers:true,
		clickToEdit:false,
		data:[],
		frozenColumns:[[
			{field:'URUTAN',title:'Urutan',width:75,hidden:true},
			{field:'KODE',title:'Kode Printer',width:75,hidden:true},
			{field:'NAMAPRINTER',title:'Alias Printer',width:200, sortable:true,editor:{
				type:'combogrid',
				options:{
					panelWidth:550,
					url: 'config/combogrid.php?table=data_printer',
					mode: 'remote',
					idField:'NAMAPRINTER',
					textField:'NAMAPRINTER',
					view:bufferview,
					pageSize:10,
					columns:[[
						{field:'KODE',title:'Kode Printer',width:200, sortable:true},
						{field:'NAMAPRINTER',title:'Alias Printer',width:200, sortable:true},
						{field:'NAMA',title:'Nama',width:200, sortable:true,hidden:true},
						{field:'NAMAKOMPUTER',title:'Nama Komputer',width:200, sortable:true,hidden:true},
					]]
				}
			}},
			{field:'NAMA',title:'Nama Printer',width:200, sortable:true},
			{field:'NAMAKOMPUTER',title:'Nama Komputer',width:200, sortable:true},
		]],
		columns:[[
			{field:'FUNGSI',title:'Fungsi Printer',width:200, sortable:true,
				editor:{
                    type:'combobox',
                    options:{
                        valueField:'id',
                        textField:'id',
                        data:fungsi_printer,
                        required:true
                    }
                }},
			{field:'NAMAKOMPUTERPEMANGGIL',title:'Nama Komputer Pemanggil',width:200, sortable:true,editor:{
				type:'combogrid',
				options:{
					panelWidth:550,
					url: 'config/combogrid.php?table=data_printer',
					mode: 'remote',
					idField:'NAMAPRINTER',
					textField:'NAMAPRINTER',
					view:bufferview,
					pageSize:10,
					columns:[[
						{field:'KODE',title:'Kode Printer',width:200, sortable:true},
						{field:'NAMAPRINTER',title:'Alias Printer',width:200, sortable:true},
						{field:'NAMA',title:'Nama',width:200, sortable:true,hidden:true},
						{field:'NAMAKOMPUTER',title:'Nama Komputer',width:200, sortable:true,hidden:true},
					]]
				}
			}},
			{field:'JUMLAHPRINT',title:'Jumlah Print',width:100, sortable:true,align:'right',editor:{type:'numberbox',options:{precision:0,required:true,}}},
			{field:'USERENTRY',title:'Penginput',width:120, sortable:true},
			{field:'TANGGALENTRY',title:'Tgl. Input',width:110, sortable:true, formatter:ubah_tgl_indo, align:'center',}
		]],
		onClickRow:function(index,row){
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
				case 'NAMAPRINTER':
					var data = ed.combogrid('grid').datagrid('getSelected');
					var kode = data ? data.KODE : '';
					var namakomp = data ? data.NAMAKOMPUTER : '';
					var nama = data ? data.NAMA : '';
					row_update = {
						NAMAKOMPUTER:namakomp,
						NAMA:nama,
						KODE:kode,
						USERENTRY:"<?php echo $_SESSION['user'];?>",
						TANGGALENTRY:"<?php echo date("Y-m-d");?>",
						STATUS:1
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

function load_data_printer() {
	$.ajax({
		type: 'POST',
		dataType: 'json',
		url: "data/process/proses_master.php",
		data: "table=load_data_printer_function",
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
