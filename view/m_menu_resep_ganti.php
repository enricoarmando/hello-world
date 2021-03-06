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

<div>
	<form id="form_input" enctype="multipart/form-data" method="post" style="width:720px">
		<input type="hidden" name="act">
		<div class="easyui-tabs" style="width:850px;" plain='true'>
			<div title="Informasi Utama">
				<table style="padding:5px">
					<tr>
						<td align="right" id="label_form">Kode Menu Resep</td>
						<td><input id="KODE" name="KODE" style="width:100px" class="label_input" required="true">
							<input name="NAMA" id="NAMA" style="width:220px" class="label_input"  required="true"></td>
						<td>
							<label id="label_form"><input type="checkbox" id="PAKET" name="PAKET" value="1"> Menu Paket</label>
						</td>
						<td rowspan=6>
							<fieldset style="border:1px solid #d8d8d8; width:250px; height:150px; padding:1px">
								<legend>Preview Gambar</legend>
								<img id="preview-image" style="width:250px; height:250px;"/>
							</fieldset>
						
						</td>
					</tr>
					<tr>
						<td align="right" id="label_form">Kode Brand</td>
						<td><input id="KODEBRAND" name="KODEBRAND" style="width:100px">
							<input name="NAMABRAND" id="NAMABRAND" style="width:220px" class="label_input" readonly>
						</td>
						<td>
							<label id="label_form"><input type="checkbox" id="PPN" name="PPN" value="1"> PPN</label>
						</td>
					</tr>
					<tr>
						<td align="right" id="label_form">Departemen Menu</td>
						<td><input id="KODEDEPARTEMEN" name="KODEDEPARTEMEN" style="width:100px">
							<input name="NAMADEPARTEMENMENU" id="NAMADEPARTEMENMENU" style="width:220px" class="label_input" readonly></td>
						<td>
							<label id="label_form"><input type="checkbox" id="PRINT" name="PRINT" value="1"> Print</label>
						</td>
					</tr>
					<tr>
						<td align="right" id="label_form">Kode Kategori Menu</td>
						<td><input id="KODEKATEGORI" name="KODEKATEGORI" style="width:100px">
							<input name="NAMAKATEGORI" id="NAMAKATEGORI" style="width:220px" class="label_input" readonly></td>
						<td>
							<label id="label_form"><input type="checkbox" id="GELATO" name="GELATO" value="1"> Gelato</label>
						</td>
					</tr>
					<tr>
						<td align="right" id="label_form">Urutan Tampil</td>
						<td><input id="URUTAN" name="URUTAN" class="easyui-numberspinner" style="width:100px;"
								required="required" data-options="min:1,editable:true">
							<label id="label_form">Warna</label><input name="NAMAWARNA" type ="color" value="#ffffff" style="width:100px" class="label_input" required="true" validType='length[0,100]'>
						</td>
						<td>
							<label id="label_form"><input type="checkbox" id="PRINTCHECKER" name="PRINTCHECKER" value="1"> Print Checker</label>
						</td>
					</tr>
					<tr>
						<td align="right" id="label_form">Akun Standard Cost</td>
						<td><input id="KODEPERKIRAAN" name="KODEPERKIRAAN" style="width:100px">
							<input name="NAMAPERKIRAAN" id="NAMAPERKIRAAN" style="width:220px" class="label_input" readonly>
						</td>
						<td>
							<label id="label_form"><input type="checkbox" id="STATUS" name="STATUS" value="1">Tampil</label>
						</td>
					</tr>
					<tr>
						<td align="right" id="label_form">Kode Printer</td>
						<td>
						<input id="KODEPRINTER" name="KODEPRINTER" style="width:100px" required="true">
						<label id="label_form">Nama Front</label>
						<input name="NAMAFRONT" class="label_input" style="width:150px" required="true" validType='length[0,100]'>
						</td>
						<td></td>
						<td id="label_form">
							<input id="GAMBAR" name="GAMBAR" class="easyui-filebox" data-options="required:false,prompt:'Choose image file...'" style="width:200px">
						</td>
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
		<div class="easyui-tabs" style="width:850px;height:220px;" plain='true'>
			<div title="Menu Utama">
				<input type="hidden" id="data_detail" name="data_detail">
				<table id="table_data_detail" style="height:100%;width:100%;"></table>
			</div>
			<div title="Menu Makanan Pilihan">
				<input type="hidden" id="data_detail_makanan" name="data_detail_makanan">
				<table id="table_data_detail_makanan" style="height:100%;width:100%;"></table>
			</div>
			<div title="Menu Minuman Pilihan">
				<input type="hidden" id="data_detail_minuman" name="data_detail_minuman">
				<table id="table_data_detail_minuman" style="height:100%;width:100%;"></table>
			</div>
		</div>

		<table style="width:100%;" border="0">
			<tr>
				<td>
					<label align="right" id="label_form">Max Porsi Makanan </label>
				</td>
				<td></td>
				<td>
					<label align="right" id="label_form" style="width:15%">Hrg Jual + Pjk Resto </label>
				</td>
				<td></td>
				<td>
					<label align="right" id="label_form" style="width:15%">Hrg Jual </label>
				</td>
				<td>
					<label align="right" id="label_form" style="width:15%">%HPP</label>
				</td>
				<td></td>
				<td>
					<label align="right" id="label_form" style="width:15%">Selisih</label>
				</td>
				<td align="right">
					<label align="right" id="label_form" style="width:15%">Amount Standard Cost (Rp)</label>
					<input name="STANDARDCOST" id="STANDARDCOST" class="number noDecimal" style="width:100px;"/>
				</td>
			</tr>
			<tr>
				<td>
					<input name="MAXSELECTEDMAKANAN" id="MAXSELECTEDMAKANAN" class="number noDecimal" style="width:40px;"/>
				</td>
				<td><label align="right" id="label_form">1 </label></td>
				<td>
					<input name="HARGAJUAL1" id="HARGAJUAL1" class="number noDecimal" style="width:100px;"/>
				</td>
				<td><label align="right" id="label_form">1 </label></td>
				<td>
					<input name="BIAYA1" id="BIAYA1" class="number noDecimal" style="width:100px;" />
				</td>     
				<td>
					<input name="HPP1" id="HPP1" class="number" style="width:60px;" data-options="precision:2" readonly />
				</td>
				<td><label align="right" id="label_form">1 </label></td>
				<td>
					<input name="SELISIH1" id="SELISIH1" class="number noDecimal" style="width:100px;" readonly />
				</td>
				<td align="right">
					<label align="right" id="label_form">Total HPP (Rp)</label>
					<input name="GRANDTOTAL" id="GRANDTOTAL" class="number noDecimal" style="width:100px;" required="true"/>
				</td>
			</tr>
			<tr>
				<td>
					<label align="right" id="label_form">Max Porsi Minuman </label>
				</td>
				<td><label align="right" id="label_form">2 </label></td>
				<td>
					<input name="HARGAJUAL2" id="HARGAJUAL2" class="number noDecimal" style="width:100px;" />
				</td>
				<td><label align="right" id="label_form">2 </label></td>
				<td>
					<input name="BIAYA2" id="BIAYA2" class="number noDecimal" style="width:100px;" />
				</td>
				<td>
					<input name="HPP2" id="HPP2" class="number" style="width:60px;"data-options="precision:2" readonly />
				</td>
				<td><label align="right" id="label_form">2 </label></td>
				<td>
					<input name="SELISIH2" id="SELISIH2" class="number noDecimal" style="width:100px;" readonly />
				</td>
				<td></td>
			</tr>
			<tr>
				<td>
					<input name="MAXSELECTEDMINUMAN" id="MAXSELECTEDMINUMAN" class="number noDecimal" style="width:40px;"/>
				</td>
				<td><label align="right" id="label_form">3 </label></td>
				<td>
					<input name="HARGAJUAL3" id="HARGAJUAL3" class="number noDecimal" style="width:100px;"/>
				</td>
				<td><label align="right" id="label_form">3 </label></td>
				<td>
					<input name="BIAYA3" id="BIAYA3" class="number noDecimal" style="width:100px;"/>
				</td>
				<td>
					<input name="HPP3" id="HPP3" class="number" style="width:60px;"data-options="precision:2" readonly />
				</td>
				<td><label align="right" id="label_form">3 </label></td>
				<td>
					<input name="SELISIH3" id="SELISIH3" class="number noDecimal" style="width:100px;" readonly />
				</td>
				<td>
					<label id="label_form"><input type="checkbox" id="PAKAIHPP" name="PAKAIHPP" value="1"> Hitung Berdasarkan %HPP</label>
				</td>
			</tr>
		</table>
		<input type="hidden" id="GAMBAR" name="GAMBAR">
	</form>
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

	$('#form_input').form({
        url:'data/process/proses_master.php?table=menu_resep&act=insert',
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

	$("#form_input").dialog({
		onOpen:function(){
			$('#form_input').form('clear');
		},
		buttons: '#dlg-buttons'
	}).dialog('close');

	browse_data_kategori('#KODEKATEGORI','kategori_menu','KODE');
	browse_data_satuan('#SATUAN','satuan','KODE');
	browse_data_departemenmenu('#KODEDEPARTEMEN','departemen_menu','KODEDEPARTEMENMENU');
	browse_data_brand('#KODEBRAND','brand_menu','KODE');
	browse_data_printer('#KODEPRINTER','printer_menu','KODE');
	browse_data_perkiraan('#KODEPERKIRAAN','perkiraan_menu','KODE');
	buat_table_detail_makanan();
	buat_table_detail_minuman();
	buat_table_detail();
	
	//buat onchange text
	$('#BIAYA1').textbox({
	  onChange: function(value){hitung_hpp(1);}
	});
	$('#BIAYA2').textbox({
	  onChange: function(value){hitung_hpp(2);}
	});
	$('#BIAYA3').textbox({
	  onChange: function(value){hitung_hpp(3);}
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
		load_data_menu_resep_makanan(row.KODE);
		load_data_menu_resep_minuman(row.KODE);

		$('#lbl_kasir').html(row.USERENTRY);
		$('#lbl_tanggal').html(row.TGLENTRY);
	}
}

function simpan() {
	var mode = $('[name=act]').val();

	$('#data_detail').val(JSON.stringify($('#table_data_detail').datagrid('getRows')));
	$('#data_detail_makanan').val(JSON.stringify($('#table_data_detail_makanan').datagrid('getRows')));
	$('#data_detail_minuman').val(JSON.stringify($('#table_data_detail_minuman').datagrid('getRows')));

	var datanya = $("#form_input :input").serialize();
	var isValid = $('#form_input').form('validate');

	if (isValid)
		isValid = cek_datagrid($('#table_data_detail'));
	if (isValid)
		isValid = cek_datagrid($('#table_data_detail_makanan'));
	if (isValid)
		isValid = cek_datagrid($('#table_data_detail_minuman'));
	if (isValid && (mode=='insert' || mode=='edit')) {
		$("#form_input").submit();
		
		/*$.ajax({
			type: 'POST',
			dataType: 'json',
			url: "data/process/proses_master.php",
			data: "table=simpan_menu_resep&"+datanya,
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
		});*/
	}
}

function hapus() {
	var row = $('#table_data').datagrid('getSelected');
	if (row){
		$.messager.confirm('Confirm','Anda Yakin Menghapus Data Ini ?',function(r){
			if (r){
				$.post('data/process/proses_master.php',{act:'delete', table:'menu_resep', kode:row.KODE},function(msg){
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
function browse_data_printer(id, table, sort) {
	$(id).combogrid({
		panelWidth:380,
		url: 'config/combogrid.php?table='+table,
		idField:'KODE',
		textField:'NAMA',
		mode:'local',
		columns:[[
			{field:'KODE',title:'Kode',width:80, sortable:true},
			{field:'NAMAPRINTER',title:'Nama',width:240, sortable:true},
		]]
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
			{field:'jml',title:'Jml',align:'right', width:65, formatter:format_amount_4,editor:{type:'numberbox', options:{precision:4,required:true,}}},
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
		},
		onAfterDeleteRow:function(index, row){
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
		},
		onAfterDeleteRow:function(index, row){
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
			(rows[i].kodemenurecipe !="" && row.kodemenurecipe == rows[i].kodemenurecipe))) {
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
		if (index != i && (rows[i].kodemenurecipe != "" && row.kodemenurecipe == rows[i].kodemenurecipe)) {
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
		if (index != i && (rows[i].kodemenurecipe != "" && row.kodemenurecipe == rows[i].kodemenurecipe)) {
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
	var total      = 0; var grandtotal = 0;
	var pembulatan = 0;//$('#PEMBULATAN').numberbox('getValue');
	var footer = {
		jml:0,
		subtotal:0
	}
	
	for (var i=0; i<data.length; i++) {
		total += parseFloat(data[i].subtotal);

		footer.jml += parseFloat(data[i].jml);
		footer.subtotal += parseFloat(data[i].subtotal);
	}

	total = Math.round(total);
	//diskonrp = diskon>0 ? Math.round(total * (diskon/100)) : diskonrp;

	//var DPP = Math.round(total-diskonrp);
	/*
	if (jenis_ppn==0) {
		ppnrp = 0;
		grandtotal = DPP;
	} else if (jenis_ppn==1) {
		ppnrp = Math.round((DPP*ppn)/100);
		grandtotal = Math.round(DPP + ppnrp);
	} else if (jenis_ppn==2) {
		grandtotal = DPP;
		ppnrp = Math.round(DPP*(ppn/110));
		DPP = grandtotal - ppnrp;
	}*/

	//$("#TOTAL").numberbox('setValue', total);
	//$('#DISKONRP').numberbox('setValue', diskonrp);
	//$('#txt_DPP').numberbox('setValue', DPP);
	//$("#PPNRP").numberbox('setValue', ppnrp);
	//$("#GRANDTOTAL").numberbox('setValue', grandtotal-pembulatan);
	$("#GRANDTOTAL").numberbox('setValue', total);

	$('#table_data_detail').datagrid('reloadFooter', [footer]);
}
function hitung_hpp(x){
	//hitung hpp yang ke-x
	$("#HPP"+x).textbox('setValue',($("#GRANDTOTAL").val() / $("#BIAYA"+x).val()) * 100 + "%");
	$("#SELISIH"+x).textbox('setValue',$("#BIAYA"+x).val() - $("#GRANDTOTAL").val() );
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
function load_data_menu_resep_makanan(kode) {
	$.ajax({
		type: 'POST',
		dataType: 'json',
		url: "data/process/proses_master.php",
		data: "table=load_data_menu_resep_makanan&kode="+kode,
		cache: false,
		beforeSend : function (){
			$.messager.progress();
		},
		success: function(msg){
			$.messager.progress('close');
			if (msg.success) {
				$('#table_data_detail_makanan').datagrid('loadData', msg.detail).datagrid('unselectAll');
			} else {
				$.messager.alert('Error', msg.errorMsg, 'error');
			}
		}
	});
}
function load_data_menu_resep_minuman(kode) {
	$.ajax({
		type: 'POST',
		dataType: 'json',
		url: "data/process/proses_master.php",
		data: "table=load_data_menu_resep_minuman&kode="+kode,
		cache: false,
		beforeSend : function (){
			$.messager.progress();
		},
		success: function(msg){
			$.messager.progress('close');
			if (msg.success) {
				$('#table_data_detail_minuman').datagrid('loadData', msg.detail).datagrid('unselectAll');
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
