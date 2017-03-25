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

<div id="form_input" style="width:720px">
	<input type="hidden" name="act">
	<input type="hidden" name="data_detail" id="data_detail">
	<table border="0">
		<tr>
			<td style="width:480px">
				<table style="padding:5px">
					<tr>
						<td align="right"></td>
						<td><label id="label_form"><input type="checkbox" id="LENSANONSTOK" name="LENSANONSTOK" value="1"> Lensa Non Stok</label></td>
					</tr>
					<tr>
						<td align="right" id="label_form">Jenis</td>
						<td><input name="KODEJENIS" id="KODEJENIS" style="width:150px"></td>
					</tr>
					<tr id="info_kategori">
						<td align="right" id="label_form">Kategori</td>
						<td>
							<input name="KODEKATEGORI1" id="KODEKATEGORI1" style="width:80px">
						    <input name="KODEKATEGORI2" id="KODEKATEGORI2" style="width:80px">
						    <input name="KODEKATEGORI3" id="KODEKATEGORI3" style="width:80px">
						    <input name="KODEKATEGORI" id="KODEKATEGORI" style="width:80px" class="label_input" readonly>
						</td>
					</tr>
					<tr class="info_tambahan">
						<td align="right" id="label_form">Bahan</td>
						<td><input name="KODEBAHAN" id="KODEBAHAN" style="width:100px"></td>
					</tr>
					<tr>
						<td align="right" id="label_form">Supplier</td>
						<td><input name="KODESUPPLIER" id="KODESUPPLIER" style="width:250px"></td>
					</tr>
					<tr>
						<td align="right" id="label_form">Kode Barang</td>
						<td>
							<input name="KODEBARANG" id="KODEBARANG" style="width:250px" class="label_input" prompt="Auto Generate">
							<label id="label_form"><input type="checkbox" id="STATUS" name="STATUS" value="1"> Aktif</label>
						</td>
					</tr>
					<tr>
						<td align="right" id="label_form">Nama Barang</td>
						<td><input name="NAMABARANG" style="width:250px" class="label_input" required="true" validType='length[0,200]'></td>
					</tr>
					<tr>
						<td align="right" id="label_form">Nama Barang Supplier</td>
						<td><input name="NAMABARANGSUPPLIER" style="width:250px" class="label_input" validType='length[0,200]'></td>
					</tr>
					<tr>
						<td align="right" id="label_form">Tipe</td>
						<td><input name="TIPE" id="TIPE" style="width:200px" class="label_input" validType='length[0,50]'></td>
					</tr>
					<tr>
						<td align="right" id="label_form">Barcode</td>
						<td><input name="BARCODE" id="BARCODE" style="width:200px" class="label_input" validType='length[0,50]'></td>
					</tr>
					<tr>
						<td align="right" id="label_form">Satuan</td>
						<td align="left"><input name="SATUAN" id="SATUAN" style="width:100px" class="label_input" validType='length[0,10]'></td>
					</tr>
					<tr>
						<td align="right" id="label_form">HPP</td>
						<td><input name="HARGABELI" id="HARGABELI" style="width:100px;" class="number"></td>
					</tr>
					<tr>
						<td align="right" id="label_form">Harga Jual</td>
						<td>
							<input name="PERSENTASEMARGIN" id="PERSENTASEMARGIN" style="width:60px;" class="number" min="0" suffix=" %">
							<input name="HARGAJUAL" id="HARGAJUAL" style="width:100px;" class="number" min="0">
						</td>
					</tr>
					<tr>
						<td align="right" id="label_form">Harga Paket</td>
						<td><input name="HARGAPAKET" id="HARGAPAKET" style="width:100px;" class="number" min="0"></td>
					</tr>
					<tr>
						<td align="right" id="label_form">Limit Min</td>
						<td id="label_form">
							<input name="LIMITMIN" id="LIMITMIN" style="width:60px;" class="number" min="0">
							&nbsp;&nbsp;
							Limit Max <input name="LIMITMAX" id="LIMITMAX" style="width:60px;" class="number" min="0">
						</td>
					</tr>
					<tr>
						<td align="right" id="label_form">Akun Persediaan</td>
						<td><input id="KODEPERKIRAAN" name="KODEPERKIRAAN" style="width:255px" readonly></td>
					</tr>
					<!-- <tr>
						<td align="right">Purchase Price</td>
						<td><input name="HARGABELI" style="width:100px;" class="number" min="0" validType='length[0,14]' required="true"></td>
					</tr>
					!-->
					<!--<tr>
						<td align="right">Limit Min</td>
						<td><input name="LIMITMIN" style="width:100px;" class="number" min="0" validType='length[0,14]' required="true"></td>
					</tr>
					<tr>
						<td align="right">Limit Max</td>
						<td><input name="LIMITMAX" style="width:100px;" class="number" min="0" validType='length[0,14]' required="true"></td>
					</tr>
					!-->
					<tr>
						<td align="right" valign="top" id="label_form">Catatan</td>
						<td><textarea name="REMARK" style="width:310px; height:60px" class="label_input" multiline="true" validType='length[0,300]'></textarea></td>
					</tr>
				</table>
			</td>
			<td valign="top">
				<fieldset class="info_tambahan">
					<legend id="label_laporan">Bahan Tambahan</legend>
					<table>
						<tr>
							<td align="right" id="label_form">Index Bias</td>
							<td><input name="INDEXBIAS" class="label_input input-detail" validType='length[0,10]'></td>
						</tr>
						<tr>
							<td align="right" id="label_form">Sph Min</td>
							<td><input name="SPHERISMINIMUM" id="SPHERISMINIMUM" class="label_input input-detail" validType='length[0,10]'></td>
						</tr>
						<tr>
							<td align="right" id="label_form">Sph Max</td>
							<td><input name="SPHERISMAKSIMUM" id="SPHERISMAKSIMUM" class="label_input input-detail" validType='length[0,10]'></td>
						</tr>
						<tr>
							<td align="right" id="label_form">Cyl Min</td>
							<td><input name="CYLINDERMINIMUM" id="CYLINDERMINIMUM" class="label_input input-detail" validType='length[0,10]'></td>
						</tr>
						<tr>
							<td align="right" id="label_form">Cyl Max</td>
							<td><input name="CYLINDERMAKSIMUM" id="CYLINDERMAKSIMUM" class="label_input input-detail" validType='length[0,10]'></td>
						</tr>
						<tr>
							<td align="right" id="label_form">Lama Pesan</td>
							<td id="label_form"><input name="LAMAPESAN" class="number input-detail" min="0" precision="0">&nbsp;Hari</td>
						</tr>
						<tr>
							<td align="right" id="label_form">Kombinasi Max</td>
							<td><input name="KOMBINASIMAKSIMUM" id="KOMBINASIMAKSIMUM" class="label_input input-detail"></td>
						</tr>
						<tr>
							<td align="right" id="label_form">Poin</td>
							<td><input name="POIN" class="number input-detail" min="0"></td>
						</tr>
					</table>
				</fieldset>
			</td>
		</tr>
	</table>

	<input type="hidden" id="mode">
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
var a_jenisbarang;
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

	$('[name=KODEJENIS]').combogrid({
		required:false,
		cache:true,
		panelWidth:170,
		mode:'local',
		idField:'KODE',
		textField:'NAMA',
		sortName:'KODE',
		sortOrder:'asc',
		url:'config/combogrid.php?table=jenis_barang',
		columns:[[
			{field:'NAMA',title:'Jenis',width:150}
		]],
		onChange:function () {
			pilih_jenis();

			buat_kodebarang();

			var row = $(this).combogrid('grid').datagrid('getSelected');
			if (row)
				get_combogrid_data ($('#KODEPERKIRAAN'), row.KODEPERKIRAANPERSEDIAAN, 'kode_perkiraan');
			else
				$('#KODEPERKIRAAN').combogrid('clear');
		},
		onLoadSuccess:function(data){
			if (!Array.isArray(a_jenisbarang))
				a_jenisbarang = data.rows;
		}
	});

	$('#KODEKATEGORI1').combogrid({
		required:false,
		cache:true,
		panelWidth:100,
		panelHeight:130,
		mode:'local',
		idField:'KODE',
		textField:'NAMA',
		sortName:'KODE',
		sortOrder:'asc',
		url:'config/combogrid.php?table=kategori&tipe=1',
		columns:[[
			{field:'NAMA',title:'Kategori',width:80}
		]],
		onChange:function(newVal){
			buat_kategori();
		},
	});

	$('#KODEKATEGORI2').combogrid({
		required:false,
		cache:true,
		panelWidth:100,
		panelHeight:130,
		mode:'local',
		idField:'KODE',
		textField:'NAMA',
		sortName:'KODE',
		sortOrder:'asc',
		url:'config/combogrid.php?table=kategori&tipe=2',
		columns:[[
			{field:'NAMA',title:'Kategori',width:80}
		]],
		onChange:function(newVal){
			buat_kategori();
		},
	});

	$('#KODEKATEGORI3').combogrid({
		required:false,
		cache:true,
		panelWidth:100,
		panelHeight:130,
		mode:'local',
		idField:'KODE',
		textField:'NAMA',
		sortName:'KODE',
		sortOrder:'asc',
		url:'config/combogrid.php?table=kategori&tipe=3',
		columns:[[
			{field:'NAMA',title:'Kategori',width:80}
		]],
		onChange:function(newVal){
			buat_kategori();
		},
	});

	$('[name=KODEBAHAN]').combogrid({
		required:false,
		cache:true,
		panelWidth:170,
		mode:'local',
		idField:'KODE',
		textField:'NAMA',
		sortName:'KODEBAHAN',
		sortOrder:'asc',
		url:'config/combogrid.php?table=bahan_barang',
		columns:[[
			{field:'NAMA',title:'Bahan',width:150}
		]],
		onChange:function(){
			buat_kodebarang();
		}
	});

	$('#KODEKATEGORI').textbox({
		onChange:function(){
			buat_kodebarang();
		}
	});

	$('#SPHERISMINIMUM').add($('#SPHERISMAKSIMUM')).
	add($('#CYLINDERMINIMUM')).add($('#CYLINDERMAKSIMUM')).textbox({
		onChange:function(){
			if ($('#mode').val() != '')
				buat_tipe();
		}
	});

	$('#KOMBINASIMAKSIMUM').textbox({
		onChange:function(){
			if ($('#mode').val() != '')
				buat_tipe();
		}
	}).textbox('textbox').css('text-align','right');
/*
	$('[name=NAMABARANG]').combogrid({
		required:false,
		panelWidth:390,
		mode:'local',
		idField:'NAMA',
		textField:'NAMA',
		sortName:'NAMA',
		sortOrder:'asc',
		url:'config/combogrid.php?table=nama_barang',
		columns:[[
			{field:'NAMA',title:'Nama',width:250}
		]]
	});
*/
	$('[name=KODESUPPLIER]').combogrid({
		required:true,
		panelWidth:390,
		mode:'remote',
		idField:'KODE',
		textField:'NAMA',
		sortName:'KODE',
		sortOrder:'asc',
		url:'config/combogrid.php?table=supplier',
		columns:[[
			{field:'KODE',title:'Kode',width:120},
			{field:'NAMA',title:'Nama',width:250}
		]],
		onChange:function(val, row){
			if ($('#LENSANONSTOK').prop('checked'))
				buat_kodebarang();
		}
	});

	$('[name=KODEPERKIRAAN]').combogrid({
		required:true,
		panelWidth:360,
		mode:'remote',
		idField:'KODE',
		textField:'NAMA',
		sortName:'KODE',
		view:bufferview,
		pageSize:20,
		sortOrder:'asc',
		url:'config/combogrid.php?table=kode_perkiraan&jenis=detail',
		columns:[[
			{field:'KODE',title:'Kode Akun',width:80},
			{field:'NAMA',title:'Nama Akun',width:250}
		]]
	});

	$('#PERSENTASEMARGIN').numberbox({
		onChange:function (newVal, oldVal) {
			if (newVal != 0) {
				var harga = ((100 + parseFloat(newVal)) / 100) * $('#HARGABELI').numberbox('getValue');
				$('#HARGAJUAL').numberbox('setValue', harga);
			}
		},
	});

});

$('#LENSANONSTOK').change(function(){
	if ($('#mode').val() == 'tambah') {
		lensa_nonstok ($(this).prop('checked') ? 1 : 0);
		$('#KODEJENIS').combogrid('clear');
		$('#KODESUPPLIER').combogrid('enableValidation');
	} else {
		$(this).prop('checked', $(this).prop('checked') ? false : true);
		$('#KODESUPPLIER').combogrid('disableValidation');
	}

	buat_kodebarang();
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
	$('#KODESUPPLIER').combogrid('disableValidation');
	$('#KODEBARANG').textbox('readonly', false);
	$('#SATUAN').textbox('setValue', 'PCS');
	$('#KODEKATEGORI1, #KODEKATEGORI2, #KODEKATEGORI3, #KODEJENIS, #KODEBAHAN').combogrid('readonly', false);
	$('#STATUS').prop('checked', true);
	$('#lbl_kasir, #lbl_tanggal').html('');
	$('#mode').val('tambah');
	lensa_nonstok (0);
}

function ubah() {
	var row = $('#table_data').datagrid('getSelected');
	if (row) {
		$('#form_input').dialog('open').dialog('setTitle', 'Ubah | <?=$menu?>');
		$('#form_input').form('load',row);

		$('[name=act]').val('edit');
		$('#KODEBARANG').textbox('readonly', true);

		get_combogrid_data ($('#KODEPERKIRAAN'), row.KODEPERKIRAAN, 'kode_perkiraan');
		lensa_nonstok(row.LENSANONSTOK);

		$('#KODEKATEGORI1, #KODEKATEGORI2, #KODEKATEGORI3, #KODEJENIS, #KODEBAHAN').combogrid('readonly');

		$('#lbl_kasir').html(row.USERENTRY);
		$('#lbl_tanggal').html(row.TGLENTRY);
		
		$('#mode').val('ubah');
	}
}

function simpan() {
	var isValid = $('#form_input').form('validate');
	if (isValid){
		act = $('[name=act]').val();
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
		});
	}
}

function hapus() {
	var row = $('#table_data').datagrid('getSelected');
	if (row){
		$.messager.confirm('Confirm','Anda Yakin Menghapus Data Ini ?',function(r){
			if (r){
				$.post('data/process/proses_master.php',{act:'delete', table:'barang', id:row.KODEBARANG},function(msg){
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

function lensa_nonstok (val) {
	if (val == 1) {
		$('.info_tambahan').show();
		$('#info_kategori').hide();

		// clear all row
		$('#KODEJENIS').combogrid('grid').datagrid('loadData', []);

		// array jenis yg ditampilkan
		var arr_jenis = ['6', '7', '8'];
		for (var i = 0; i < a_jenisbarang.length; i++) {
			if (arr_jenis.indexOf(a_jenisbarang[i].KODE) >= 0) {
				$('#KODEJENIS').combogrid('grid').datagrid('appendRow', a_jenisbarang[i]);
			}
		}

	} else {
		$('.info_tambahan').hide();
		$('#info_kategori').show();

		// insert data
		$('#KODEJENIS').combogrid('grid').datagrid('loadData', a_jenisbarang);
	}

}

function buat_kategori () {
	var val = $('#KODEKATEGORI1').combogrid('getValue');
	val += $('#KODEKATEGORI2').combogrid('getValue');
	val += $('#KODEKATEGORI3').combogrid('getValue');

	$('#KODEKATEGORI').textbox('setValue', val);
}
function pilih_jenis () {
	// array jenis yg memakai kategori
	var arr_jenis = ['0', '1', '2'];

	// jika kodejenis sesuai dengan array
	var jenis = arr_jenis.indexOf($('#KODEJENIS').combogrid('getValue'));

	$('#KODEKATEGORI1, #KODEKATEGORI2, #KODEKATEGORI3').combogrid('clear');
	$('#KODEKATEGORI').textbox('clear');

	if (jenis < 0)
		$('#info_kategori').hide();
	else
		$('#info_kategori').show();

	// membuat barcode
	if ($('#mode').val() != '') {
		$.post('config/numberbox.php?table=get_barcode',{kodejenis:$('#KODEJENIS').combogrid('getValue')},function(msg){
			if (msg.success){
				$('#BARCODE').textbox('setValue', msg.barcode);
			} else {
				$.messager.alert('Error', msg.errorMsg, 'error');
			}
		},'json');
	}
}
function buat_kodebarang() {
	if ($('#mode').val()=='tambah') {
		$.post('config/numberbox.php?table=get_kodebarang',{
				lensanonstok:$('[name=LENSANONSTOK]:checked').val(),
				jenisbarang:$('#KODEJENIS').combogrid('getValue'),
				kodekategori:$('#KODEKATEGORI').textbox('getValue'),
				kodebahan:$('#KODEBAHAN').combogrid('getValue'),
				kodesupplier:$('#KODESUPPLIER').combogrid('getValue'),
			},function(msg){
				if (msg.success){
					$('#KODEBARANG').textbox('setValue', msg.kodebarang);
				} else {
					$.messager.alert('Error', msg.errorMsg, 'error');
					$('#KODEBARANG').textbox('clear');
				}
			},
		'json');
	}
}
function buat_tipe () {
	$('#TIPE').textbox('setValue', 'S '+$('#SPHERISMINIMUM').textbox('getValue')+' to '+$('#SPHERISMAKSIMUM').textbox('getValue')+' C '+$('#CYLINDERMINIMUM').textbox('getValue')+' to '+$('#CYLINDERMAKSIMUM').textbox('getValue')+ ' K '+$('#KOMBINASIMAKSIMUM').numberbox('getValue'));
}

function buat_table () {
	$('#table_data').datagrid({
		remoteFilter:true,
		fit:true,
		singleSelect:true,
		striped:true,
		pagination:true,
		pageSize:20,
		sortName:'NAMABARANG',
		sortOrder:'asc',
		url: 'config/datagrid.php?table=barang',
		rowStyler: function(index,row){
			if (row.STATUS == 0) return 'background-color:#a8aea6';
		},
		frozenColumns:[[
			{field:'LENSANONSTOK',title:'Non Stok', align:'center', sortable:true, formatter:format_checked,},
			{field:'NAMAJENIS',title:'Jenis',width:100, sortable:true,},
			{field:'KATEGORI',title:'Kategori',width:60, sortable:true,},
			{field:'NAMABAHAN',title:'Bahan',width:60, sortable:true,},
			{field:'KODEBARANG',title:'Kode Barang',width:120, sortable:true,},
			{field:'NAMABARANG',title:'Nama/Merk',width:150, sortable:true,},
			{field:'TIPE',title:'Tipe',width:200, sortable:true,},
		]],
		columns:[[
			{field:'SATUAN1',title:'Satuan',width:50, sortable:true,align:'center',},
			{field:'NAMASUPPLIER',title:'Supplier',width:200, sortable:true,align:'left',},
			{field:'NAMABARANGSUPPLIER',title:'Nama Barang Supplier',width:200, sortable:true,align:'left',},
			{field:'HARGABELI',title:'HPP',width:80,sortable:true,formatter:format_amount, align:'right'},
			{field:'PERSENTASEMARGIN',title:'Margin (%)',width:80,sortable:true,formatter:format_amount, align:'right'},
			{field:'HARGAJUAL',title:'Harga Jual',width:80,sortable:true,formatter:format_amount, align:'right'},
			{field:'HARGAPAKET',title:'Harga Paket',width:80,sortable:true,formatter:format_amount, align:'right'},
			{field:'INDEXBIAS',title:'Index Bias',width:70, sortable:true,align:'center',},
			{field:'SPHERISMINIMUM',title:'Sph Min',width:50, sortable:true,align:'center',},
			{field:'SPHERISMAKSIMUM',title:'Sph Max',width:50, sortable:true,align:'center',},
			{field:'CYLINDERMINIMUM',title:'Cyl Min',width:50, sortable:true,align:'center',},
			{field:'CYLINDERMAKSIMUM',title:'Cyl Max',width:50, sortable:true,align:'center',},
			{field:'LAMAPESAN',title:'Lama Pesan (Hari)',width:110,sortable:true,formatter:format_number, align:'right'},
			{field:'KOMBINASIMAX',title:'Kombinasi Max',width:90,sortable:true,formatter:format_amount, align:'right'},
			{field:'POIN',title:'Poin',width:80,sortable:true,formatter:format_amount, align:'right'},
			{field:'KODEPERKIRAAN',title:'Kode Akun',width:100, sortable:true,},
			{field:'NAMAPERKIRAAN',title:'Nama Akun',width:150, sortable:true,},
			{field:'REMARK',title:'Catatan',width:250, sortable:true,},
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
