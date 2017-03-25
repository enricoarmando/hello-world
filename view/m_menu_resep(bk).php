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
				<div data-options="name:'alasan'">Sub Resep</div>				
			</div>
		</div>
	</div>
	<div data-options="region:'center',">
		<table id="table_data" ></table>
	</div>
</div>

<div id="form_input" style="width:900px">
	<input type="hidden" name="act">
	<div class="easyui-tabs" style="width:850px;height:205px;" plain='true'>
		<div title="Informasi Utama">
			<table style="padding:5px">
				<tr>
					<td align="right" id="label_form">Kode Menu Resep</td>
					<td><input id="KODE" name="KODE" style="width:100px" class="label_input" required="true">
					<input name="NAMA" id="NAMA" style="width:150px" class="label_input"  required="true">
					<label id="label_form"><input type="checkbox" id="PAKET" name="PAKET" value="1"> Menu Paket</label></td>
				</tr>
				<tr>
					<td align="right" id="label_form">Kode Brand</td>
					<td><input id="KODEBRAND" name="KODEBRAND" style="width:100px">
					<input name="NAMABRAND" id="NAMABRAND" style="width:150px" class="label_input" readonly>
					<label id="label_form"><input type="checkbox" id="PPN" name="PPN" value="1"> PPN</label></td>
				</tr>
				<tr>
					<td align="right" id="label_form">Departemen Menu</td>
					<td><input id="KODEDEPARTEMEN" name="KODEDEPARTEMEN" style="width:100px">
					<input name="NAMADEPARTEMENMENU" id="NAMADEPARTEMENMENU" style="width:150px" class="label_input" readonly>
					<label id="label_form"><input type="checkbox" id="PRINT" name="PRINT" value="1"> Print</label></td>
				</tr>
				<tr>
					<td align="right" id="label_form">Kode Kategori Menu</td>
					<td><input id="KODEKATEGORI" name="KODEKATEGORI" style="width:100px">
					<input name="NAMAKATEGORI" id="NAMAKATEGORI" style="width:150px" class="label_input" readonly>
					<label id="label_form"><input type="checkbox" id="GELATO" name="GELATO" value="1"> Gelato</label></td>
				</tr>
				
				<tr>
					<td align="right" id="label_form">Urutan Tampil</td>
					<td><input id="URUTAN" name="URUTAN" class="easyui-numberspinner" style="width:80px;"
							required="required" data-options="min:1,editable:true">
					<label id="label_form">Warna</label><input name="NAMAWARNA" type ="color" value="#ffffff" style="width:40px" class="label_input" required="true" validType='length[0,100]'>
					</td>
				</tr>
				<tr>
					<td align="right" id="label_form">Akun Standard Cost</td>
					<td><input id="KODEPERKIRAAN" name="KODEPERKIRAAN" style="width:100px">
					<input name="NAMAPERKIRAAN" id="NAMAPERKIRAAN" style="width:150px" class="label_input" readonly></td>
				</tr>
			</table>
		</div>
		<div title="Informasi Tambahan">
			<div class="easyui-tabs" style="width:850px;height:150px;" plain='true'>
				<div title="Cara Memasak">
				<textarea name="CARAMASAK" style="width:100%; height:100%" class="label_input" multiline="true" validType='length[0,300]'></textarea>
				</div>
				<div title="Penanganan Produk">
				<textarea name="PENANGANAN" style="width:100%; height:100%" class="label_input" multiline="true" validType='length[0,300]'></textarea>
				</div>
				<div title="Cara Penyajian">
				<textarea name="CARASAJI" style="width:100%; height:100%" class="label_input" multiline="true" validType='length[0,300]'></textarea>
				</div>
				<div title="Peralatan yang dibutuhkan">
				<textarea name="PERALATAN" style="width:100%; height:100%" class="label_input" multiline="true" validType='length[0,300]'></textarea>
				</div>				
			</div>
		</div>
	</div>
	<div class="easyui-tabs" style="width:850px;height:225px;" plain='true'>
		<div title="Menu Utama">
			<input type="hidden" id="data_detail" name="data_detail">
			<table id="table_data_detail" style="height:200px;width:100%;"></table>
		</div>
		<div title="Menu Makanan Pilihan">
			<input type="hidden" id="data_detail_makanan" name="data_detail_makanan">
			<table id="table_data_detail_makanan" style="height:200px;width:100%;"></table>
		</div>
		<div title="Menu Minuman Pilihan">
			<input type="hidden" id="data_detail_minuman" name="data_detail_minuman">
			<table id="table_data_detail_minuman" style="height:200px;width:100%;"></table>
		</div>
	</div>
	
	<table style="width:100%;table-layout: fixed;">
		<tr align="center">
			<td>
				<label align="right" id="label_form" style="width:15%">Max Porsi Makanan </label>
			</td>
			<td>			
				<label align="right" id="label_form" style="width:15%">Harga Jual + Pajak Resto </label>
			</td>
			<td>			
				<label align="right" id="label_form" style="width:15%">Harga Jual </label>
			</td>
			<td>			
				<label align="right" id="label_form" style="width:15%">%HPP</label>
			</td>
			<td>			
				<label align="right" id="label_form" style="width:15%">Selisih(Harga - Ttl HPP)</label>
			</td>
			<td>			
				<label align="right" id="label_form" style="width:15%">Amount Standard Cost(Rp)</label>
			</td>
		</tr >
		<tr align="center">
			<td>
				<input name="MAXSELECTEDMAKANAN" id="MAXSELECTEDMAKANAN" class="number noDecimal" style="width:100px;" required="true"/>
			</td>
			<td>
				<label align="right" id="label_form">1 </label>
				<input name="HARGAJUAL1" id="HARGAJUAL1" class="number noDecimal" style="width:100px;" required="true"/>
			</td>
			<td>
				<label align="right" id="label_form" >1 </label>
				<input name="BIAYA1" id="BIAYA1" class="number noDecimal" style="width:100px;" required="true"/>
			</td>
			<td>
				<input name="MAXSELECTEDMAKANAN" id="MAXSELECTEDMAKANAN" class="number noDecimal" style="width:100px;" required="true"/>
			</td>
			<td>
				<label align="right" id="label_form">1 </label>
				<input name="MAXSELECTEDMAKANAN" id="MAXSELECTEDMAKANAN" class="number noDecimal" style="width:100px;" required="true"/>
			</td>
			<td>
				<input name="MAXSELECTEDMAKANAN" id="MAXSELECTEDMAKANAN" class="number noDecimal" style="width:100px;" required="true"/>
			</td>
		
		</tr>
		<tr align="center">
			<td>
				<label align="right" id="label_form">Max Porsi Minuman </label>
			</td>
			<td>
				<label align="right" id="label_form">2 </label>
				<input name="MAXSELECTEDMAKANAN" id="MAXSELECTEDMAKANAN" class="number noDecimal" style="width:100px;" required="true"/>
			</td>	
			<td>
				<label align="right" id="label_form">2 </label>
				<input name="MAXSELECTEDMAKANAN" id="MAXSELECTEDMAKANAN" class="number noDecimal" style="width:100px;" required="true"/>
			</td>
			<td>
				<input name="MAXSELECTEDMAKANAN" id="MAXSELECTEDMAKANAN" class="number noDecimal" style="width:100px;" required="true"/>
			</td>
			<td>
				<label align="right" id="label_form">2 </label>
				<input name="MAXSELECTEDMAKANAN" id="MAXSELECTEDMAKANAN" class="number noDecimal" style="width:100px;" required="true"/>
			</td>
			<td>
				<label align="right" id="label_form">Total HPP(Rp) </label>
			</td>
		</tr>
		<tr align="center">
			<td>			
				<input name="MAXSELECTEDMINUMAN" id="MAXSELECTEDMINUMAN" class="number noDecimal" style="width:100px;" required="true"/>
			</td>
			<td>
				<label align="right" id="label_form">3 </label>
				<input name="MAXSELECTEDMAKANAN" id="MAXSELECTEDMAKANAN" class="number noDecimal" style="width:100px;" required="true"/>
			</td>
			<td>
				<label align="right" id="label_form">3 </label>
				<input name="MAXSELECTEDMAKANAN" id="MAXSELECTEDMAKANAN" class="number noDecimal" style="width:100px;" required="true"/>
			</td>
			<td>
				<input name="MAXSELECTEDMAKANAN" id="MAXSELECTEDMAKANAN" class="number noDecimal" style="width:100px;" required="true"/>
			</td>
			<td>
				<label align="right" id="label_form">3 </label>
				<input name="MAXSELECTEDMAKANAN" id="MAXSELECTEDMAKANAN" class="number noDecimal" style="width:100px;" required="true"/>
			</td>
			<td>
				<input name="MAXSELECTEDMAKANAN" id="MAXSELECTEDMAKANAN" class="number noDecimal" style="width:100px;" required="true"/>
			</td>
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
	
	browse_data_kategori('#KODEKATEGORI','resep_kategori','KODE');
	browse_data_satuan('#SATUAN','resep_satuan','KODE');
	browse_data_departemenmenu('#KODEDEPARTEMEN','departemen_menu','KODEDEPARTEMENMENU');
	browse_data_brand('#KODEBRAND','brand_menu','KODE');
	browse_data_perkiraan('#KODEPERKIRAAN','perkiraan_menu','KODE');
	buat_table_detail();
	buat_table_detail_makanan();
	buat_table_detail_minuman();
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
	$('#CK').prop('checked', true); 
	$('#URUTAN').numberspinner('setValue', 1);
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
		load_data_menu_resep(row.KODE);
		
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
			data: "table=simpan_sub_resep&"+datanya,
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
	/*
	var isValid = $('#form_input').form('validate');
	if (isValid) {
		act = $('[name=act]').val();
		$.ajax({
			type: 'POST',
			url: "data/process/proses_master.php", 
			data: "table=discount&"+$('#form_input :input').serialize(),
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
	}*/
}

function hapus() {
	var row = $('#table_data').datagrid('getSelected');
	if (row){
		$.messager.confirm('Confirm','Anda Yakin Menghapus Data Ini ?',function(r){
			if (r){
				$.post('data/process/proses_master.php',{act:'delete', table:'sub_resep', kode:row.KODE},function(msg){
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
		autoRowHeight:true,
		pageSize:20,
		url: 'config/datagrid.php?table=menu_resep',
		rowStyler: function(index,row){  
			if (row.STATUS == 0) return 'background-color:#a8aea6';
		},
		frozenColumns:[[
			{field:'KODE',title:'Kode Menu Resep',width:80, sortable:true,},
			{field:'NAMA',title:'Nama Menu Resep',width:200, sortable:true,},
		]],
		columns:[[
			{field:'NAMAFRONT',title:'Nama Front',width:200, sortable:true,},
			{field:'NAMAPRINTER',title:'Nama Printer',width:200, sortable:true,},
			{field:'PAKET',title:'Paket',width:50, sortable:true, formatter:format_checked,align:'right'},
			{field:'PPN',title:'PPN',width:50, sortable:true,align:'right'},
			{field:'KODEBRAND',title:'Kode Brand',width:100, sortable:true,align:'center'},
			{field:'KODEDEPARTEMEN',title:'Kode Departemen',width:100, sortable:true,align:'center'},
			{field:'KODEKATEGORI',title:'Kode Kategori',width:100, sortable:true,align:'center'},
			{field:'KODEPERKIRAAN',title:'Kode Perkiraan',width:100, sortable:true,align:'center'},
			{field:'SATUAN',title:'Satuan',width:100, sortable:true,align:'center'},
			{field:'TOTAL',title:'Total',width:100, sortable:true,  align:'right'},
			{field:'STANDARDCOST',title:'Standard Cost',width:100, sortable:true,  align:'right'},
			{field:'GRANDTOTAL',title:'Grand Total',width:100, sortable:true,  align:'right'},
			{field:'HARGAJUAL1',title:'Harga Jual 1',width:100, sortable:true,  align:'right'},
			{field:'HARGAJUAL2',title:'Harga Jual 2',width:100, sortable:true,  align:'right'},
			{field:'HARGAJUAL3',title:'Harga Jual 3',width:100, sortable:true,  align:'right'},
			{field:'BIAYA1',title:'Biaya 1',width:100, sortable:true,  align:'right'},
			{field:'BIAYA2',title:'Biaya 2',width:100, sortable:true,  align:'right'},
			{field:'BIAYA3',title:'Biaya 3',width:100, sortable:true,  align:'right'},
			{field:'PERALATAN',title:'Peralatan',width:100, sortable:true, align:'center'},
			{field:'PENANGANAN',title:'Penanganan',width:100, sortable:true, align:'center'},
			{field:'CARAMASAK',title:'Cara Masak',width:100, sortable:true,resizeable:true, align:'center'},
			{field:'CARASAJI',title:'Cara Saji',width:100, sortable:true, align:'center'},
			{field:'GAMBAR',title:'Gambar',width:100, sortable:true, align:'center'},
			{field:'MAXSELECTEDMAKANAN',title:'Max Selected Makanan',width:100, sortable:true,  align:'right'},
			{field:'MINSELECTEDMINUMAN',title:'Max Selected Minuman',width:100, sortable:true,  align:'right'},
			{field:'KODEPRINTER',title:'Kode Printer',width:100, sortable:true,  align:'center'},
			{field:'PRINT',title:'Print',width:100, sortable:true,  align:'right'},
			{field:'PRINTCHECKER',title:'Print Checker',width:100, sortable:true,  align:'right'},
			{field:'GELATO',title:'Gelato',width:100, sortable:true,  align:'right'},
			{field:'URUTAN',title:'Urutan',width:100, sortable:true,  align:'right'},
			{field:'NAMAWARNA',title:'Nama Warna',width:100, sortable:true,  align:'center'},
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
function browse_data_departemenmenu(id, table, sort) {
	$(id).combogrid({
		panelWidth:380,
		url: 'config/combogrid.php?table='+table,
		idField:'KODEDEPARTEMENMENU',
		textField:'KODEDEPARTEMENMENU',
		mode:'local',
		columns:[[
			{field:'KODEDEPARTEMENMENU',title:'Kode',width:80, sortable:true},
			{field:'NAMADEPARTEMENMENU',title:'Nama',width:240, sortable:true},
		]],
		onChange:function(){
			var row = $(this).combogrid('grid').datagrid('getSelected');
			if (row)
				$('#NAMADEPARTEMENMENU').textbox('setValue', row.NAMADEPARTEMENMENU)
			else
				$('#NAMADEPARTEMENMENU').textbox('clear')
		}
	});
}
function browse_data_brand(id, table, sort) {
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
				$('#NAMABRAND').textbox('setValue', row.NAMA)
			else
				$('#NAMABRAND').textbox('clear')
		}
	});
}
function browse_data_perkiraan(id, table, sort) {
	$(id).combogrid({
		panelWidth:380,
		url: 'config/combogrid.php?table='+table,
		idField:'KODE',
		textField:'KODE',
		mode:'local',
		columns:[[
			{field:'KODE',title:'Kode',width:80, sortable:true},
			{field:'NAMAPERKIRAAN',title:'Nama',width:240, sortable:true},
		]],
		onChange:function(){
			var row = $(this).combogrid('grid').datagrid('getSelected');
			if (row)
				$('#NAMAPERKIRAAN').textbox('setValue', row.NAMAPERKIRAAN)
			else
				$('#NAMAPERKIRAAN').textbox('clear')
		}
	});
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
			}},
			{field:'koderecipe',title:'Sub Rsp.',width:75,editor:{
				type:'combogrid',
				options:{
					panelWidth:550,
					url: 'config/combogrid.php?table=resep_sub_resep',
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
			{field:'kodemenurecipe',title:'Menu Rsp.',width:75,editor:{
				type:'combogrid',
				options:{
					panelWidth:550,
					url: 'config/combogrid.php?table=resep_menu_resep',
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
			{field:'keterangan',title:'Keterangan',idField:'KET',width:185},
		]],
		columns: [[
			{field:'konversi',title:'Konversi',align:'right', width:40, formatter:format_number,hidden:true},
			{field:'jml',title:'Jml',align:'right', width:40, formatter:format_amount,editor:{type:'numberbox', options:{precision:2,required:true,}}},
			{field:'satuan',title:'Satuan',width:60, align:'center'},
			{field:'jml2',title:'Jml2',align:'right', width:40, formatter:format_amount},
			{field:'satuan2',title:'Satuan2',width:60, align:'center'},
			{field:'bhnbaku',title:'Bahan Baku', align:'right', width:70, formatter:format_amount},
			{field:'subtotal',title:'Subtotal',align:'right', width:95, formatter:format_amount},
		]],
		onClickRow:function(){
		},
		onLoadSuccess : function (data){
			hitung_grandtotal();
		},
		onAfterDeleteRow:function(index, row){
			hitung_grandtotal();
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
					var satuan = data ? data.SATUAN : '';
					var satuan2 = data ? data.SATUAN2 : '';
					var harga = data ? data.HARGA : '';
					var konversi = data ? data.KONVERSI : '';
					row_update = {
						koderecipe:'',
						kodemenurecipe:'',
						keterangan:nama,
						satuan:satuan,
						satuan2:satuan2,
						konversi:konversi,
					};
					break;
				case 'koderecipe':
					var data = ed.combogrid('grid').datagrid('getSelected');
					var nama = data ? data.NAMA : '';
					var satuan = data ? data.SATUAN : '';
					var harga = data ? data.GRANDTOTAL : '';
					var satuan2 = data ? data.SATUAN2 : '';
					var konversi = data ? data.KONVERSI : '';
					row_update = {
						kodebrg:'',
						kodemenurecipe:'',
						keterangan:nama,
						satuan:satuan,
						satuan2:satuan2,
						konversi:konversi,
					};
					break;
				case 'kodemenurecipe':
					var data = ed.combogrid('grid').datagrid('getSelected');
					var nama = data ? data.NAMA : '';
					var satuan = data ? data.SATUAN : '';
					var satuan2 = data ? data.SATUAN2 : '';
					var harga = data ? data.HARGA : '';
					var konversi = data ? data.KONVERSI : '';
					row_update = {
						kodebrg:'',
						koderecipe:'',
						keterangan:nama,
						satuan:satuan,
						satuan2:satuan2,
						konversi:konversi,
					};
					break;
				case 'jml':
					if(changes.jml != null){
						row_update = {
							jml2:changes.jml*row.konversi,
						};
					}
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
			hitung_subtotal_detail(index,row);
			hitung_grandtotal();
		}
	}).datagrid('enableCellEditing');
}
function buat_table_detail_makanan() {
	$("#table_data_detail_makanan").datagrid({
		showFooter:true,
		rownumbers:true,
		clickToEdit:false,
		data:[],
		frozenColumns:[[
			{field:'kodemenurecipe',title:'Menu Rsp.',width:75,editor:{
				type:'combogrid',
				options:{
					panelWidth:550,
					url: 'config/combogrid.php?table=resep_menu_resep',
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
			{field:'keterangan',title:'Keterangan',idField:'KET',width:185},
		]],
		columns: [[
			{field:'konversi',title:'Konversi',align:'right', width:40, formatter:format_number,hidden:true},
			{field:'jml',title:'Jml',align:'right', width:40, formatter:format_amount,editor:{type:'numberbox', options:{precision:2,required:true,}}},
			{field:'satuan',title:'Satuan',width:60, align:'center'},
			{field:'jml2',title:'Jml2',align:'right', width:40, formatter:format_amount},
			{field:'satuan2',title:'Satuan2',width:60, align:'center'},
			{field:'bhnbaku',title:'Bahan Baku', align:'right', width:70, formatter:format_amount},
			{field:'subtotal',title:'Subtotal',align:'right', width:95, formatter:format_amount},
		]],
		onClickRow:function(){
		},
		onLoadSuccess : function (data){
			hitung_grandtotal();
		},
		onAfterDeleteRow:function(index, row){
			hitung_grandtotal();
		},
		onCellEdit:function(index,field,val){
			var row = $(this).datagrid('getRows')[index];
			var ed  = get_editor ('#table_data_detail_makanan', index, field);

		
		},
		onEndEdit:function(index,row,changes){
			var cell = $(this).datagrid('cell');
			var ed = get_editor ('#table_data_detail_makanan', index, cell.field);
			var row_update = {};
			switch(cell.field) {
				case 'kodemenurecipe':
					var data = ed.combogrid('grid').datagrid('getSelected');
					var nama = data ? data.NAMA : '';
					var satuan = data ? data.SATUAN : '';
					var satuan2 = data ? data.SATUAN2 : '';
					var harga = data ? data.HARGA : '';
					var konversi = data ? data.KONVERSI : '';
					row_update = {
						keterangan:nama,
						satuan:satuan,
						satuan2:satuan2,
						konversi:konversi,
					};
					break;
				case 'jml':
					if(changes.jml != null){
						row_update = {
							jml2:changes.jml*row.konversi,
						};
					}
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
			hitung_subtotal_detail_makanan(index,row);
			hitung_grandtotal();
		}
	}).datagrid('enableCellEditing');
}
function buat_table_detail_minuman() {
	$("#table_data_detail_minuman").datagrid({
		showFooter:true,
		rownumbers:true,
		clickToEdit:false,
		data:[],
		frozenColumns:[[
			{field:'kodemenurecipe',title:'Menu Rsp.',width:75,editor:{
				type:'combogrid',
				options:{
					panelWidth:550,
					url: 'config/combogrid.php?table=resep_menu_resep',
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
			{field:'keterangan',title:'Keterangan',idField:'KET',width:185},
		]],
		columns: [[
			{field:'konversi',title:'Konversi',align:'right', width:40, formatter:format_number,hidden:true},
			{field:'jml',title:'Jml',align:'right', width:40, formatter:format_amount,editor:{type:'numberbox', options:{precision:2,required:true,}}},
			{field:'satuan',title:'Satuan',width:60, align:'center'},
			{field:'jml2',title:'Jml2',align:'right', width:40, formatter:format_amount},
			{field:'satuan2',title:'Satuan2',width:60, align:'center'},
			{field:'bhnbaku',title:'Bahan Baku', align:'right', width:70, formatter:format_amount},
			{field:'subtotal',title:'Subtotal',align:'right', width:95, formatter:format_amount},
		]],
		onClickRow:function(){
		},
		onLoadSuccess : function (data){
			hitung_grandtotal();
		},
		onAfterDeleteRow:function(index, row){
			hitung_grandtotal();
		},
		onCellEdit:function(index,field,val){
			var row = $(this).datagrid('getRows')[index];
			var ed  = get_editor ('#table_data_detail_minuman', index, field);

		
		},
		onEndEdit:function(index,row,changes){
			var cell = $(this).datagrid('cell');
			var ed = get_editor ('#table_data_detail_minuman', index, cell.field);
			var row_update = {};
			switch(cell.field) {
				case 'kodemenurecipe':
					var data = ed.combogrid('grid').datagrid('getSelected');
					var nama = data ? data.NAMA : '';
					var satuan = data ? data.SATUAN : '';
					var satuan2 = data ? data.SATUAN2 : '';
					var harga = data ? data.HARGA : '';
					var konversi = data ? data.KONVERSI : '';
					row_update = {
						keterangan:nama,
						satuan:satuan,
						satuan2:satuan2,
						konversi:konversi,
					};
					break;
				case 'jml':
					if(changes.jml != null){
						row_update = {
							jml2:changes.jml*row.konversi,
						};
					}
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
			hitung_subtotal_detail_minuman(index,row);
			hitung_grandtotal();
		}
	}).datagrid('enableCellEditing');
}
function hitung_subtotal_detail(index, row) {
	// hitung diskon lebih dahulu
	var data = {};
	var dg = $('#table_data_detail');

	data.subtotal = row.bhnbaku * row.jml;
	dg.datagrid('updateRow',{
		index: index,
		row: data
	});
	
	// cek jika ada barang yang sama
	var rows = dg.datagrid('getRows');
	for (var i = 0; i < rows.length; i++) {
		if (index != i && ((rows[i].kodebrg !="" && row.kodebrg == rows[i].kodebrg)||
			(rows[i].koderecipe !="" && row.koderecipe == rows[i].koderecipe) || 
			(rows[i].kodemenurecipe != null && row.kodemenurecipe == rows[i].kodemenurecipe))) {
			$.messager.show({
				title:'Warning',
				msg:'Bahan yang diinput tidak boleh sama dalam satu detil Resep',
				timeout:1000,
            });

			dg.datagrid('deleteRow', index);

			break;
		}
	}
}
function hitung_subtotal_detail_makanan(index, row) {
	// hitung diskon lebih dahulu
	var data = {};
	var dg = $('#table_data_detail_makanan');
	
	data.subtotal = row.bhnbaku * row.jml;
	dg.datagrid('updateRow',{
		index: index,
		row: data
	});
	
	// cek jika ada barang yang sama
	var rows = dg.datagrid('getRows');
	for (var i = 0; i < rows.length; i++) {
		if (index != i && (rows[i].kodemenurecipe != null && row.kodemenurecipe == rows[i].kodemenurecipe)) {
			$.messager.show({
				title:'Warning',
				msg:'Bahan yang diinput tidak boleh sama dalam satu detil Resep',
				timeout:1000,
            });

			dg.datagrid('deleteRow', index);

			break;
		}
	}
}
function hitung_subtotal_detail_minuman(index, row) {
	// hitung diskon lebih dahulu
	var data = {};
	var dg = $('#table_data_detail_minuman');

	data.subtotal = row.bhnbaku * row.jml;
	dg.datagrid('updateRow',{
		index: index,
		row: data
	});
	
	// cek jika ada barang yang sama
	var rows = dg.datagrid('getRows');
	for (var i = 0; i < rows.length; i++) {
		if (index != i && (rows[i].kodemenurecipe != null && row.kodemenurecipe == rows[i].kodemenurecipe)) {
			$.messager.show({
				title:'Warning',
				msg:'Bahan yang diinput tidak boleh sama dalam satu detil Resep',
				timeout:1000,
            });

			dg.datagrid('deleteRow', index);

			break;
		}
	}
}
function hitung_grandtotal(){
	var data       = $("#table_data_detail").datagrid('getRows');
	var grandtotal = 0;
	for (var i=0; i<data.length; i++) {
		grandtotal += parseFloat(data[i].subtotal);
	}
	grandtotal = Math.round(grandtotal);
	$("#GRANDTOTAL").numberbox('setValue', grandtotal);
}

function load_data_menu_resep(kode) {
	$.ajax({
		type: 'POST',
		dataType: 'json',
		url: "data/process/proses_master.php",
		data: "table=load_data_menu_resep&kode="+kode,
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
