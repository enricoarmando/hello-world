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
		<div style="float:right" hidden>
			<input class="easyui-searchbox" data-options="prompt:'Silahkan Ketikan Kata Kunci Pada Bagian Pencarian',menu:'#mm',searcher:do_search" style="width:300px"></input>
			<div id="mm">
				<div data-options="name:'a.username'">Nama</div>
				<div data-options="name:'a.userid'">Kode</div>
			</div>
		</div>
	</div>
	<div data-options="region:'center',">
		<table id="table_data" idField="USERID"></table>
	</div>
</div>

<form id="form_input" enctype="multipart/form-data" method="post">
	<input type="hidden" name="act">
	<input type="hidden" name="GAMBAR">
	<input type="hidden" id="data_detail" name="data_detail">
	<input type="hidden" id="data_lokasi" name="data_lokasi">

	<div class="easyui-tabs" style="width:700px;height:400px" plain='true'>
		<div title="Umum">
			<table tyle="padding:2px" border="0">
				<tr>
					<td align="right" id="label_form" style="width:100px">Kode</td>
					<td >
						<input name="USERID" id="USERID" style="width:150px" class="label_input" required="true" validType='length[0,20]'>
						<label id="label_form"><input type="checkbox" id="STATUS" name="STATUS" value="1"> Aktif</label>
					</td>
					<td rowspan="8">
						<div>
							<fieldset style="border:1px solid #d8d8d8; width:150px; height:180px; padding:0px">
								<legend>Preview Foto</legend>
								<img id="preview-image" style="width:100%; height:100%"/>
							</fieldset>
							<input id="FILEGAMBAR" name="FILEGAMBAR" class="easyui-filebox" data-options="required:false,buttonIcon:'icon-man',buttonText:'Foto'" style="width:155px">
						</div>
					</td>
				</tr>
				<tr>
					<td align="right" id="label_form">Nama</td>
					<td><input name="USERNAME" style="width:200px" class="label_input" required="true" validType='length[0,50]'></td>
				</tr>
				<tr>
					<td align="right" id="label_form">Password</td>
					<td><input name="PASS" type="password" class="label_input" data-options="required:true,fontTransform:'normal'" validType='length[0,20]' style="width:150px"/> <label id="label_laporan">Perhatikan huruf kecil dan besar</label></td>
				</tr>
				<tr>
					<td align="right" id="label_form">Ulangi Password</td>
					<td><input name="RE_PASS" type="password" class="label_input" data-options="required:true,fontTransform:'normal'" validType="equals['[name=PASS]']" style="width:150px"/> <label id="label_laporan">Perhatikan huruf kecil dan besar</label></td>
				</tr>
				<tr>
					<td align="right" id="label_form">Alamat</td>
					<td><input name="ALAMAT" style="width:200px; height:40px" multiline="true" class="label_input" required="true" validType='length[0,200]'></td>
				</tr>
				<tr>
					<td align="right" id="label_form">Kota</td>
					<td><input name="KOTA" style="width:150px" class="label_input" required="true" validType='length[0,20]'></td>
				</tr>
				<tr>
					<td align="right" id="label_form">Telp</td>
					<td><input name="TELP" style="width:150px" class="label_input" validType='length[0,50]'></td>
				</tr>
				<tr>
					<td align="right" id="label_form">Tempat Lahir</td>
					<td id="label_form"><input name="TEMPATLAHIR" style="width:150px" class="label_input" validType='length[0,50]'></td>
				</tr>
				<tr>
					<td align="right" id="label_form">Tgl. Lahir</td>
					<td id="label_form"><input name="TGLLAHIR" id="TGLLAHIR" class="date"/></td>
				</tr>
				<tr>
					<td align="right" id="label_form">Kelamin</td>
					<td colspan="2">
						<select name="JENISKELAMIN" style="width:100px" class="easyui-combobox label_input" required="true" panelHeight="auto">
							<option value="">--- Pilih ---</option>
							<option value="L">LAKI-LAKI</option>
							<option value="P">PEREMPUAN</option>
						</select>
					</td>
				</tr>
				<tr>
					<td align="right" id="label_form">Jabatan</td>
					<td colspan="2"><input id="JABATAN" name="JABATAN" style="width:150px"></td>
				</tr>
				<tr>
					<td align="right" id="label_form">Cabang</td>
					<td colspan="2"><input id="KODELOKASI" name="KODELOKASI" style="width:150px"></td>
				</tr>
				<tr>
					<td align="right" id="label_form"></td>
					<td colspan="2">
						<table>
							<tr>
								<td width="100"><label id="label_form"><input type="checkbox" id="SALES" name="SALES" value="1"> Sales</label></td>
								<td><label id="label_form"><input type="checkbox" id="FITTING" name="FITTING" value="1"> Fitting</label></td>
							<tr>
							<tr>
								<td><label id="label_form"><input type="checkbox" id="SALES" name="RO" value="1"> RO</label></td>
								<td><label id="label_form"><input type="checkbox" id="EDGER" name="EDGER" value="1"> Edger</label></td>
							<tr>
							<tr>
								<td><label id="label_form"><input type="checkbox" id="SETEL" name="SETEL" value="1"> Setel</label></td>
								<td align="right" id="label_form"></td>
							<tr>
						</table>
					</td>
				</tr>
				<tr>
					<td align="right" id="label_form">Catatan</td>
					<td colspan="2"><textarea name="REMARK" style="width:350px; height:50px" class="label_input" multiline="true" validType='length[0,300]'></textarea></td>
				</tr>
			</table>
		</div>
		<div title="Hak Akses">
			<div class="easyui-layout" style="width:100%;height:100%" fit="true">
				<div id="menu_tree"></div>
			</div>
		</div>
		<div title="Lokasi Login">
			<div class="easyui-layout" style="width:100%;height:100%" fit="true">
				<table id="table_data_lokasi"></table>
			</div>
		</div>
	</div>

	<fieldset id="menu_tambahan" hidden>
		<legend id="label_laporan">Another</legend>
		<table width="100%">
			<tr>
				<td width="33%" hidden><label id="label_form"><input type="checkbox" name="OTORISASI" value="1" /> Authorization</label></td>
				<td width="40%"><label id="label_form"><input type="checkbox" name="TAMPILGRANDTOTAL" value="1" /> Show Price</label></td>
				<td><label id="label_form"><input type="checkbox" name="PRINTULANG" value="1" /> Re-Print</label></td>
			</tr>
		</table>
	</fieldset>
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

<script type="text/javascript" src="script/jquery-easyui/plugins/datagrid-filter.min.js"></script>

<script>
$(document).ready(function(){
	create_form_login();

	$('#form_input').form({
        url:'data/process/proses_master.php?table=pegawai',
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

    $("#TGLLAHIR").datebox('setValue', date_format());
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

	$('[name=JABATAN]').combogrid({
		//required:true,
		panelWidth:140,
		mode:'local',
		idField:'JABATAN',
		textField:'JABATAN',
		sortName:'JABATAN',
		sortOrder:'asc',
		url:'config/combogrid.php?table=jabatan',
		columns:[[
			{field:'JABATAN',title:'Jabatan',width:120}
		]]
	});
	$('[name=KODELOKASI]').combogrid({
		//required:true,
		panelWidth:250,
		mode:'local',
		idField:'KODE',
		textField:'NAMA',
		sortName:'NAMA',
		sortOrder:'asc',
		url:'config/combogrid.php?table=lokasi',
		columns:[[
			{field:'NAMA',title:'Cabang',width:150},
			{field:'KODE',title:'Kode',width:50},
		]]
	});

	$("#table_data_lokasi").datagrid({
		height: '100%',
		rownumbers:true,
		singleSelect:true,
		checkOnSelect:false,
		selectOnCheck:false,
		url:'config/datagrid.php?table=semua_lokasi',
		columns:[[
			{field:'ck',title:'',width:30,checkbox:true},
			{field:'KODELOKASI',title:'Kode',width:80},
			{field:'NAMALOKASI',title:'Nama',width:200},
		]],
	});

	$('#menu_tree').treegrid({
		height: '100%',
		lines: true,
		url: 'config/treegrid.php?table=hak_akses',
		rownumbers: false,
		idField: 'id',
		treeField: 'menu',
		columns:[[
			{field:'menu',title:'',width:220},
			{field:'hakakses',title:'Hak Akses',align:'center', width:60, formatter:function(val, row){
				if (val==0 || val==1) {
					return '<input type="checkbox" '+(val==0 ? '' : 'checked')+' name="cb_hakakses[]" onchange="cek_detail(\'hakakses\', \'cb_hakakses\', \''+row.id+'\')" id="cb_hakakses_'+row.id+'" value="'+row.tipe+'">';
				} else {
					return '<input type="checkbox" name="hakakses[]" onchange="cek_header(\'hakakses\', \'cb_hakakses\', \''+row.menu+'\')" value="'+row.menu+'">';
				}
			}},
			{field:'tambah',title:'Tambah',align:'center', width:60, formatter:function(val, row){
				var str = row.tipe != null ? row.tipe : row.menu;
				var res = str.split(" ");

				if (res[0] == 'Laporan' || res[0] == 'Pengaturan') {
					return '';
				} else {
					if (val==0 || val==1) {
						return '<input type="checkbox" '+(val==0 ? '' : 'checked')+' name="cb_tambah[]"  onchange="cek_detail(\'tambah\', \'cb_tambah\', \''+row.id+'\')" id="cb_tambah_'+row.id+'" value="'+row.tipe+'">';
					} else {
						return '<input type="checkbox" name="tambah[]" onchange="cek_header(\'tambah\', \'cb_tambah\', \''+row.menu+'\')" value="'+row.menu+'">';
					}
				}
			}},
			{field:'ubah',title:'Ubah',align:'center',width:60, formatter:function(val, row){
				var str = row.tipe != null ? row.tipe : row.menu;
				var res = str.split(" ");

				if (res[0] == 'Laporan' || res[0] == 'Pengaturan') {
					return '';
				} else {
					if (val==0 || val==1) {
						return '<input type="checkbox" '+(val==0 ? '' : 'checked')+' name="cb_ubah[]"  onchange="cek_detail(\'ubah\', \'cb_ubah\', \''+row.id+'\')" id="cb_ubah_'+row.id+'" value="'+row.tipe+'">';
					} else {
						return '<input type="checkbox" name="ubah[]" onchange="cek_header(\'ubah\', \'cb_ubah\', \''+row.menu+'\')" value="'+row.menu+'">';
					}
				}
			}},
			{field:'hapus',title:'Hapus',align:'center',width:60, formatter:function(val, row){
				var str = row.tipe != null ? row.tipe : row.menu;
				var res = str.split(" ");

				if (res[0] == 'Laporan' || res[0] == 'Pengaturan') {
					return '';
				} else {
					if (val==0 || val==1) {
						return '<input type="checkbox" '+(val==0 ? '' : 'checked')+' name="cb_hapus[]"  onchange="cek_detail(\'hapus\', \'cb_hapus\', \''+row.id+'\')" id="cb_hapus_'+row.id+'" value="'+row.tipe+'">';
					} else {
						return '<input type="checkbox" name="hapus[]" onchange="cek_header(\'hapus\', \'cb_hapus\', \''+row.menu+'\')" value="'+row.menu+'">';
					}
				}
			}},
		]],
	});

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
	$('#TGLLAHIR').datebox('setValue', date_format());
	$('#USERID').textbox('readonly', false);
	$('#lbl_kasir, #lbl_tanggal').html('');

	$('#preview-image').removeAttr('src').replaceWith($('#preview-image').clone());
}

function ubah () {
	var row = $('#table_data').datagrid('getSelected');
	if (row) {
		$('#form_input').dialog('open').dialog('setTitle', 'Ubah | <?=$menu?>');
		$('#form_input').form('load',row);

		$('[name=act]').val('edit');
		
		$('#USERID').textbox('readonly');

		$('#menu_tree').treegrid('load', {kode:row.USERID});

		$.post('config/datagrid.php?table=lokasi_user',{user:row.USERID},function(msg){
			var rows = $('#table_data_lokasi').datagrid('getRows');
			var ln = rows.length;

			for (var i = 0; i < ln; i++) {

				var data = msg;
				var ln1 = data.length;

				for (var j = 0; j < ln1; j++) {
					if (rows[i].KODELOKASI == data[j].KODELOKASI) {
						$('#table_data_lokasi').datagrid('checkRow', i);
						break;
					}
				}
			}
		},'json');

		// remove image
		$('#preview-image').removeAttr('src').replaceWith($('#preview-image').clone());
		
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

			downloadingImage.src = "http://<?=$_SERVER['HTTP_HOST']?>"+url+"gambar-pegawai/"+gambar+'?'+ new Date().getTime();
			// akhir script load gambar
		} else {
			$('#preview-image').removeAttr('src').replaceWith($('#preview-image').clone());
		}
		
		$('#lbl_kasir').html(row.USERENTRY);
		$('#lbl_tanggal').html(row.TGLENTRY);
	}
}
function simpan () {
	$('#data_detail').val(JSON.stringify($('#menu_tree').treegrid('getData')));
	$('#data_lokasi').val(JSON.stringify($('#table_data_lokasi').datagrid('getChecked')));

	var isValid = $('#form_input').form('validate');
	if (isValid) {
		$('#form_input').submit();
		/*$.ajax({
			type: 'POST',
			url: "data/process/proses_master.php",
			data: "table=pegawai&"+$('#form_input :input').serialize(),
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
				$.post('data/process/proses_master.php',{act:'delete', table:'pegawai', id:row.USERID},function(msg){
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

function cek_header(a,b,tipe){
	var check = false;
	$('[name="'+a+'[]"]').each(function(){
		if ($(this).val()==tipe) {
			check = $(this).prop('checked');
		}
	});

	$('[name="'+b+'[]"]').each(function(){
		if ($(this).val()==tipe) {
			$(this).prop('checked', check);

			var str = ($(this).prop('id')).split("_");
			if (a=='hakakses') {
				$('#menu_tree').treegrid('update',{
					id: str[2],
					row: {
						hakakses: check==true ? 1 : 0,
					}
				});
			} else if (a=='tambah') {
				$('#menu_tree').treegrid('update',{
					id: str[2],
					row: {
						tambah: check==true ? 1 : 0,
					}
				});
			} else if (a=='ubah') {
				$('#menu_tree').treegrid('update',{
					id: str[2],
					row: {
						ubah: check==true ? 1 : 0,
					}
				});
			} else if (a=='hapus') {
				$('#menu_tree').treegrid('update',{
					id: str[2],
					row: {
						hapus: check==true ? 1 : 0,
					}
				});
			}
 		}
	});
}

function cek_detail(a,b,id){
	var check = false;
	var tipe = '';
	var h = 0;
	var j = 0;
	$('[name="'+b+'[]"]').each(function(){
		var str = ($(this).prop('id')).split("_");
		if (str[2]==id) {
			check = $(this).prop('checked');
			tipe = $(this).val();
			if (a=='hakakses') {
				$('#menu_tree').treegrid('update',{
					id: str[2],
					row: {
						hakakses: check==true ? 1 : 0,
					}
				});
			} else if (a=='tambah') {
				$('#menu_tree').treegrid('update',{
					id: str[2],
					row: {
						tambah: check==true ? 1 : 0,
					}
				});
			} else if (a=='ubah') {
				$('#menu_tree').treegrid('update',{
					id: str[2],
					row: {
						ubah: check==true ? 1 : 0,
					}
				});
			} else if (a=='hapus') {
				$('#menu_tree').treegrid('update',{
					id: str[2],
					row: {
						hapus: check==true ? 1 : 0,
					}
				});
			}
		}
	});

	$('[name="'+b+'[]"]').each(function(){
		if ($(this).val()==tipe){
			j++;
			if ($(this).prop('checked')) {
				h++;
			}
		}
	});

	$('[name="'+a+'[]"]').each(function(){
		if ($(this).val()==tipe) {
			$(this).prop('checked', j==h ? true : false);
		}
	});
}

function buat_table () {
	$('#table_data').datagrid({
		remoteFilter:true,
		fit:true,
		singleSelect:true,
		striped:true,
		pagination:true,
		pageSize:20,
		url: 'config/datagrid.php?table=pegawai',
		rowStyler: function(index,row){
			if (row.STATUS == 0) return 'background-color:#a8aea6';
		},
		frozenColumns:[[
			{field:'USERID',title:'NIK',width:130, sortable:true,},
			{field:'USERNAME',title:'Nama',width:200, sortable:true,},
		]],
		columns:[[
			{field:'SALES',title:'Sales', align:'center', sortable:true, formatter:format_checked,},
			{field:'RO',title:'RO', align:'center', sortable:true, formatter:format_checked,},
			{field:'SETEL',title:'Setel', align:'center', sortable:true, formatter:format_checked,},
			{field:'FITTING',title:'Fitting', align:'center', sortable:true, formatter:format_checked,},
			{field:'EDGER',title:'Edger', align:'center', sortable:true, formatter:format_checked,},
			{field:'ALAMAT',title:'Alamat',width:250, sortable:true,},
			{field:'KOTA',title:'Kota',width:130, sortable:true,},
			{field:'TELP',title:'Telp.',width:100, sortable:true, },
			{field:'TEMPATLAHIR',title:'Tempat Lahir',width:100, sortable:true, },
			{field:'TGLLAHIR',title:'Tgl. Lahir',width:80, sortable:true, },
			{field:'JENISKELAMIN',title:'Kelamin',width:70, sortable:true,formatter:function(val, row){
				return val == 'L' ? 'LAKI-LAKI' : 'PEREMPUAN';
			}},
			{field:'JABATAN',title:'Jabatan',width:120, sortable:true, },
			{field:'NAMALOKASI',title:'Cabang',width:150, sortable:true, },
			{field:'REMARK',title:'Catatan',width:250, sortable:true,},
			{field:'USERENTRY',title:'User',width:70, sortable:true},
			{field:'TGLENTRY',title:'Tgl Input',width:75, sortable:true, formatter:ubah_tgl_indo, align:'center',},
			{field:'STATUS',title:'Status', align:'center', sortable:true, formatter:format_checked,}
		]],
		onDblClickRow:function(index,row) {
			before_edit();
		},
	}).datagrid('enableFilter');
}
</script>