<?php
if (empty($_SESSION['user'])) header('Location: ../../../index.php');
?>
<style>
	.input-detail {
		width: 50px;
	}
</style>
<div class="easyui-layout" style="width:100%;height:100%" fit="true">
	<div data-options="region:'north'" style="height:40px;padding:5px;">
		<a id="btn_tambah" href="#" class="easyui-linkbutton" data-options="iconCls:'icon-add', plain:true">Tambah</a>
		<a id="btn_ubah" href="#" class="easyui-linkbutton" data-options="iconCls:'icon-edit', plain:true">Ubah / F4</a>
		<a id="btn_hapus" href="#" class="easyui-linkbutton" data-options="iconCls:'icon-cancel', plain:true">Hapus</a>
		<a id="btn_refresh" href="#" class="easyui-linkbutton" data-options="iconCls:'icon-reload', plain:true">Refresh</a>
		<div style="float:right" hidden>
			<input class="easyui-searchbox" data-options="prompt:'Silahkan Ketikan Kata Kunci Pada Bagian Pencarian',menu:'#mm',searcher:do_search" style="width:300px"></input>
			<div id="mm">
				<div data-options="name:'a.namabarang'">Nama</div>
				<div data-options="name:'a.kodebarang'">Kode</div>
			</div>
		</div>
	</div>
	<div data-options="region:'center',">
		<table id="table_data" idField="KODEBARANG"></table>
	</div>
</div>

<form id="form_input" enctype="multipart/form-data" method="post" style="width:720px">
	<input type="hidden" name="act">
	<input type="hidden" name="data_detail" id="data_detail">
	<table style="padding:5px">
		<tr>
			<td align="right" id="label_form" style="width:150px">Kode Barang</td>
			<td ><input name="KODE" id="KODE" style="width:100px" class="label_input"></td>
			<td rowspan="6">
				<fieldset style="border:1px solid #d8d8d8; width:250px; height:150px; padding:1px">
					<legend>Preview Gambar</legend>
					<img id="preview-image" style="width:250px; height:250px;"/>
				</fieldset>
			</td>
		</tr>
		<tr>
			<td align="right" id="label_form">Nama Barang</td>
			<td><input name="NAMA" id="NAMA" style="width:200px" class="label_input"></td>
		</tr>
		<tr>
			<td align="right" id="label_form">Barcode</td>
			<td><input name="BARCODE" id="BARCODE" style="width:200px" class="label_input" validType='length[0,50]'></td>
		</tr>
		<tr>
			<td align="right" id="label_form">Departemen</td>
			<td><input id="KODEDEPARTEMEN" name="KODEDEPARTEMEN" style="width:100px">
			<input id="NAMADEPARTEMEN" name="NAMADEPARTEMEN" style="width:200px" class="label_input" readonly></td>
		</tr>
		<tr>
			<td align="right" id="label_form">Kategori</td>
			<td><input id="KODEKATEGORI" name="KODEKATEGORI" style="width:100px">
			<input id="NAMAKATEGORI" name="NAMAKATEGORI" style="width:200px" class="label_input" readonly></td>
		</tr>
		<tr>
			<td align="right" id="label_form">Jenis Barang</td>
			<td><input id="KODEJENISBARANG" name="KODEJENISBARANG" style="width:100px">
			<input id="NAMAJENISBARANG" name="NAMAJENISBARANG" style="width:200px" class="label_input" readonly></td>
		</tr>
		<tr>
			<td align="right" id="label_form">Akun Persediaan</td>
			<td><input id="KODEPERKIRAAN" name="KODEPERKIRAAN" style="width:100px">
			<input id="NAMAPERKIRAAN" name="NAMAPERKIRAAN" style="width:200px" class="label_input" readonly></td>
			<td id="label_form">
				<input id="FILEGAMBAR" name="FILEGAMBAR" class="easyui-filebox" data-options="required:false,prompt:'Choose image file...'" style="width:200px">
			</td>
		</tr>
		<tr>
			<td align="right" id="label_form">Satuan Awal</td>
			<td colspan="2"><input id="SATUAN" name="SATUAN" style="width:100px">
			<input id="NAMASATUAN1" name="NAMASATUAN1" style="width:200px" class="label_input" readonly></td>
		</tr>
		<tr>
			<td align="right" id="label_form">Satuan Akhir</td>
			<td colspan="2"><input id="SATUAN2" name="SATUAN2" style="width:100px">
			<input id="NAMASATUAN2" name="NAMASATUAN2" style="width:200px" class="label_input" readonly></td>
		</tr>
		<tr>
			<td align="right" id="label_form">Konv. Min 1</td>
			<td colspan="2"><input name="QTYMIN" style="width:70px;" class="number label_input" min="0" validType='length[0,14]' required="true">
			<label id="label_form"><input type="checkbox" id="VALIDASIKONVERSI" name="VALIDASIKONVERSI" value="1"> Cek Validasi Konversi</label></td>
		</tr>
		<tr>
			<td align="right" id="label_form">Konv. Max 1</td>
			<td colspan="2"><input name="QTYMAX" style="width:70px;" class="number label_input" min="0" validType='length[0,14]' required="true"></td>
		</tr>
		<!--
		<tr>
			<td align="right" id="label_form"></td>
			<td align="left" id="label_form">
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				Unit &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				Konversi 1 &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				Unit 2 &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				Konversi 2 &nbsp;&nbsp;&nbsp;&nbsp;
				Unit 3
			</td>
		</tr>
		<tr>
			<td align="right" id="label_form">1</td>
			<td>
				<input name="SATUAN" style="width:60px" class="easyui-validatebox label_input" validType='length[0,100]'>
				<input name="KONVERSI1" style="width:70px;" class="number label_input" min="0" validType='length[0,14]' required="true">
				<input name="SATUAN2" style="width:60px" class="easyui-validatebox label_input" validType='length[0,100]'>
				<input name="KONVERSI2" style="width:70px;" class="number label_input" min="0" validType='length[0,14]' required="true">	
				<input name="SATUAN3" style="width:60px" class="easyui-validatebox label_input" validType='length[0,100]'>
			</td>
		</tr>
		<tr>
			<td align="right" id="label_form">Harga Beli</td>
			<td><input name="HARGABELI" id="HARGABELI" style="width:100px;" class="number"></td>
		</tr>
		<tr>
			<td align="right" id="label_form">Harga Jual</td>
			<td>							
				<input name="HARGAJUAL" id="HARGAJUAL" style="width:100px;" class="number" min="0">
			</td>
		</tr>
		-->
		<tr>
			<td align="right" id="label_form">Stok Ideal</td>
			<td id="label_form" colspan="2">
				<input name="STOKIDEAL" id="STOKIDEAL" style="width:100px;" class="number" min="0">
			</td>
		</tr>
		<tr>
			<td align="right" id="label_form">Limit Min</td>
			<td id="label_form" colspan="2">
				<input name="LIMITMIN" id="LIMITMIN" style="width:100px;" class="number" min="0"> &nbsp
				<label id="label_form"><b>Barang Digunakan Oleh Divisi</b></label>
			</td>
		</tr>
		<tr>
			<td align="right" id="label_form">Limit Max</td>
			<td id="label_form" colspan="2">
				<input name="LIMITMAX" id="LIMITMAX" style="width:100px;" class="number" min="0"> &nbsp
				<input type="radio" name="DIVISI" value="KITCHEN" id="RADIO1"> Kitchen &nbsp
				<input type="radio" name="DIVISI" value="BAR"> Bar &nbsp
				<input type="radio" name="DIVISI" value="Front"> Front &nbsp
			</td>
		</tr>
		<tr>
			<td align="right" valign="top" id="label_form">Spesifikasi</td>
			<td><textarea name="SPESIFIKASI" style="width:310px; height:60px" class="label_input" multiline="true" validType='length[0,300]'></textarea></td>
			<td rowspan="2">
				 <fieldset id="field">
					<legend id="label_laporan">Lokasi</legend>
					<div style='overflow:auto; height:100px; width:200px'>
						<table id="table_kode_perkiraan"></table>
					</div>
				</fieldset>
			</td>
		</tr>
		<tr>
			<td align="right" valign="top" id="label_form">Catatan</td>
			<td><textarea name="NOTE" style="width:310px; height:60px" class="label_input" multiline="true" validType='length[0,300]'></textarea></td>
		</tr>
	</table>

	<input type="hidden" id="mode">
	<input type="hidden" id="GAMBAR" name="GAMBAR">
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

<script type="text/javascript" src="script/jquery-easyui/plugins/datagrid-filter.min.js"></script>
<script>
var a_jenisbarang;
$(document).ready(function(){
	create_form_login();
	buat_table();

	$('#form_input').form({
        url:'data/process/proses_master.php?table=barang&act=insert',
        ajax:true,
        iframe:false,
        success: function(data){
			var msg = JSON.parse(data);
			if (msg.success) {
				var mode = $('#mode').val();
				if (mode=='tambah') {
					tambah();
					$.messager.show({
						title:'Info',
						msg:'Simpan Data Sukses',
						showType:'show'
					});
				} else {
					$('#form_input').dialog('close');
					$.messager.alert('Info','Simpan Data Sukses','info');
				}
				$('#table_data').datagrid('reload');

				$('#preview-image').removeAttr('src').replaceWith($('#preview-image').clone());
			} else {
				$.messager.alert('Error', msg.errorMsg, 'error');
			}
        },
    });
	
	
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

	/*$("#form_input").dialog({
		onOpen:function(){
			$('#form_input').form('clear');
		},
		buttons: '#dlg-buttons'
	}).dialog('close');
	*/
	$('#KODEDEPARTEMEN').combogrid({
		url: 'config/combogrid.php?table=departemen',
		required:true,
		panelWidth:320,
		mode:'local',
		idField:'KODE',
		textField:'KODE',
		sortName:'KODE',
		sortOrder:'asc',
		//pageSize:20,
		columns:[[
			{field:'KODE',title:'Kode',width:100, sortable:true},
			{field:'NAMA',title:'Nama',width:200, sortable:true},
		]],
		onChange:function(){
			var row = $(this).combogrid('grid').datagrid('getSelected');
			if (row) {
				$('#NAMADEPARTEMEN').textbox('setValue', row.NAMA);
			} else {
				$('#NAMADEPARTEMEN').textbox('clear');
			}
		}
	});
	$('#KODEKATEGORI').combogrid({
		url: 'config/combogrid.php?table=kategori',
		required:true,
		panelWidth:320,
		mode:'local',
		idField:'KODE',
		textField:'KODE',
		sortName:'KODE',
		sortOrder:'asc',
		//pageSize:20,
		columns:[[
			{field:'KODE',title:'Kode',width:100, sortable:true},
			{field:'NAMA',title:'Nama',width:200, sortable:true},
		]],
		onChange:function(){
			var row = $(this).combogrid('grid').datagrid('getSelected');
			if (row) {
				$('#NAMAKATEGORI').textbox('setValue', row.NAMA);
			} else {
				$('#NAMAKATEGORI').textbox('clear');
			}
		}
	});
	$('#KODEJENISBARANG').combogrid({
		url: 'config/combogrid.php?table=jenis_barang',
		required:true,
		panelWidth:320,
		mode:'local',
		idField:'KODE',
		textField:'KODE',
		sortName:'KODE',
		sortOrder:'asc',
		//pageSize:20,
		columns:[[
			{field:'KODE',title:'Kode',width:100, sortable:true},
			{field:'NAMA',title:'Nama',width:200, sortable:true},
		]],
		onChange:function(){
			var row = $(this).combogrid('grid').datagrid('getSelected');
			if (row) {
				$('#NAMAJENISBARANG').textbox('setValue', row.NAMA);
			} else {
				$('#NAMAJENISBARANG').textbox('clear');
			}
		}
	});
	$('#KODEPERKIRAAN').combogrid({
		url: 'config/combogrid.php?table=perkiraan',
		required:true,
		panelWidth:320,
		mode:'local',
		idField:'KODE',
		textField:'KODE',
		sortName:'KODE',
		sortOrder:'asc',
		//pageSize:20,
		columns:[[
			{field:'KODE',title:'Kode',width:100, sortable:true},
			{field:'NAMAPERKIRAAN',title:'Nama',width:200, sortable:true},
		]],
		onChange:function(){
			var row = $(this).combogrid('grid').datagrid('getSelected');
			if (row) {
				$('#NAMAPERKIRAAN').textbox('setValue', row.NAMAPERKIRAAN);
			} else {
				$('#NAMAPERKIRAAN').textbox('clear');
			}
		}
	});
	$('#SATUAN').combogrid({
		url: 'config/combogrid.php?table=satuan',
		required:true,
		panelWidth:320,
		mode:'local',
		idField:'KODE',
		textField:'KODE',
		sortName:'KODE',
		sortOrder:'asc',
		//pageSize:20,
		columns:[[
			{field:'KODE',title:'Kode',width:100, sortable:true},
			{field:'NAMA',title:'Nama',width:200, sortable:true},
		]],
		onChange:function(){
			var row = $(this).combogrid('grid').datagrid('getSelected');
			if (row) {
				$('#NAMASATUAN1').textbox('setValue', row.NAMA);
			} else {
				$('#NAMASATUAN1').textbox('clear');
			}
		}
	});
	$('#SATUAN2').combogrid({
		url: 'config/combogrid.php?table=satuan',
		required:false,
		panelWidth:320,
		mode:'local',
		idField:'KODE',
		textField:'KODE',
		sortName:'KODE',
		sortOrder:'asc',
		//pageSize:20,
		columns:[[
			{field:'KODE',title:'Kode',width:100, sortable:true},
			{field:'NAMA',title:'Nama',width:200, sortable:true},
		]],
		onChange:function(){
			var row = $(this).combogrid('grid').datagrid('getSelected');
			if (row) {
				$('#NAMASATUAN2').textbox('setValue', row.NAMA);
			} else {
				$('#NAMASATUAN2').textbox('clear');
			}
		}
	});
	$.ajax({
		type: 'POST',
		dataType: 'json',
		url: "config/combogrid.php?table=lokasi",
		data: "", 
		cache: false, 
		success: function(msg){
			var temp_html = '';
			//alert(msg);
			for (var i=0; i<msg.length; i++) {
				temp_html += '<tr><td><label id="label_laporan"><input type="checkbox" name="cbLokasi[]" value="'+msg[i].KODE+'"> '+msg[i].KODE+' - '+msg[i].NAMA+'</label></td></tr>';
			}
			$('#table_kode_perkiraan').html(temp_html);
		}
	});
	
	$("#form_input").dialog({
		onOpen:function(){
			$('#form_input').form('clear');
			//$('#preview-image').removeAttr('src').replaceWith($('#preview-image').clone());
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
			$.messager.confirm('Confirm', 'Anda Tidak Memiliki Hak Akses "Hapus", Anda Akan Melanjutkan Dengan Otorisasi  ?', function(r){
				if (r) $('#form_login').dialog('open');
			});
		}
	});
}

function tambah() {
	$('#form_input').dialog('open').dialog('setTitle', 'Tambah | <?=$menu?>');
	$('[name=act]').val('insert');
	$('.number').numberbox('setValue', 0);
	$('#KODEBARANG').textbox('readonly', false);
	$('#RADIO1').prop('checked', true);;
	$('#STATUS').prop('checked', true);
	$('#lbl_kasir, #lbl_tanggal').html('');
	$('.combogrid-f').each(function(){
		$(this).combogrid('grid').datagrid('load', {q:''});
	});
	$('#mode').val('tambah');
}

function ubah() {
	var row = $('#table_data').datagrid('getSelected');
	if (row) {
		$('#form_input').dialog('open').dialog('setTitle', 'Ubah | <?=$menu?>');
		$('#form_input').form('load',row);
		
		//load_data(row.KODEPR);

		// load gambar
		var gambar = row.GAMBAR;
		
		if (gambar && gambar != "") {
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

			downloadingImage.src = "http://<?=$_SERVER['HTTP_HOST']?>"+url+"gambar-barang/"+gambar+'?'+ new Date().getTime();
			// akhir script load gambar
		} else {
			$('#preview-image').removeAttr('src').replaceWith($('#preview-image').clone());
		}
		
		$('[name=act]').val('edit');
		$('#KODEBARANG').textbox('readonly', true);

		//get_combogrid_data ($('#KODEPERKIRAAN'), row.KODEPERKIRAAN, 'kode_perkiraan');

		$('#lbl_kasir').html(row.USERENTRY);
		$('#lbl_tanggal').html(row.TGLENTRY);
		
		$('#mode').val('ubah');
	}
}

function simpan() {
	var isValid = $('#form_input').form('validate');
	if (isValid){
		$('#form_input').submit();
		/*act = $('[name=act]').val();
		$.ajax({
			type: 'POST',
			url: "data/process/proses_master.php",
			data: "table=barang&act="+act+"&"+$('#form_input :input').serialize(),
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
		});*/
	}
}

function hapus() {
	var row = $('#table_data').datagrid('getSelected');
	if (row){
		$.messager.confirm('Confirm','Anda Yakin Menghapus Data Ini ?',function(r){
			if (r){
				$.post('data/process/proses_master.php',{act:'delete', table:'barang', id:row.KODE},function(msg){
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
		sortName:'NAMA',
		sortOrder:'asc',
		url: 'config/datagrid.php?table=barang',
		rowStyler: function(index,row){
			if (row.STATUS == 0) return 'background-color:#a8aea6';
		},
		frozenColumns:[[
			{field:'KODE',title:'Kode Brg',width:80, sortable:true,},
			{field:'NAMA',title:'Nama Barang',width:200, sortable:true,},
			{field:'KODEDEPARTEMEN',title:'Departemen',width:90, sortable:true,},
			{field:'KODEKATEGORI',title:'Kategori',width:75, sortable:true,},
		]],
		columns:[[
			{field:'KODEJENISBARANG',title:'Jenis Barang',width:90, sortable:true,},
			{field:'KODEPERKIRAAN',title:'Perkiraan',width:85, sortable:true,},
			{field:'DIVISI',title:'Divisi',width:60, sortable:true,align:'center',},
			{field:'SATUAN',title:'Satuan Awal',width:90, sortable:true,align:'left',},
			{field:'SATUAN2',title:'Satuan Akhir',width:90, sortable:true,align:'left',},
			{field:'QTYMIN',title:'Konversi Min',width:90,sortable:true,formatter:format_amount, align:'right'},
			{field:'QTYMAX',title:'Konversi Max',width:90,sortable:true,formatter:format_amount, align:'right'},
			{field:'VALIDASIKONVERSI',title:'Validasi',width:60,sortable:true,formatter:format_amount, align:'right'},
			{field:'KIRIMDATA',title:'Kirim Data',width:80,sortable:true,formatter:format_amount, align:'right'},
			{field:'BARCODE',title:'Barcode',width:70, sortable:true,align:'center',},
			{field:'LIMITMIN',title:'Limit Min',width:75, sortable:true,align:'center',},
			{field:'STOKIDEAL',title:'Stok Ideal',width:75, sortable:true,align:'center',},
			{field:'LIMITMAX',title:'Limit Max',width:75, sortable:true,align:'center',},
			{field:'KODEOLAHDATA',title:'Kode Olah Data',width:100, sortable:true,align:'center',},
			{field:'NAMAFILESERTIFIKATHALAL',title:'File Sertifikat',width:90, sortable:true,align:'center',},
			{field:'TGLBERLAKUSERTIFIKATHALAL',title:'Tgl. Berlaku Sertifikat',width:135, sortable:true,align:'center',},
			{field:'TGLEXPIREDSERTIFIKATHALAL',title:'Tgl. Expired Sertifikat',width:135, sortable:true,align:'center',},
			{field:'USERENTRY',title:'User',width:70, sortable:true},
			{field:'TGLENTRY',title:'Tgl. Input',width:90, sortable:true, formatter:ubah_tgl_indo, align:'center',},
			{field:'STATUS',title:'Aktif', align:'center', sortable:true, formatter:format_checked,}
		]],
		onDblClickRow:function(index,row) {
			before_edit();
		},
	}).datagrid('enableFilter');
}

</script>
