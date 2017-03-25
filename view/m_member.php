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
				<div data-options="name:'b.namacustomer'">Nama</div>
				<div data-options="name:'a.kodemember'">Kode Member</div>
				<div data-options="name:'a.kodecustomer'">Kode Customer</div>
				<div data-options="name:'a.nokartu'">No. Kartu</div>
			</div>
		</div>
	</div>
	<div data-options="region:'center',">
		<table id="table_data"></table>
	</div>
</div>

<div id="form_input">
	<input type="hidden" name="act">
	<table tyle="padding:5px" border="0">
		<tr>
			<td align="right" id="label_form">Kode Member</td>
			<td><input name="KODEMEMBER" style="width:200px" class="label_input" prompt="Auto Generate" readonly>
				<label id="label_form"><input type="checkbox" id="STATUS" name="STATUS" value="1"> Aktif</label>
			</td>
		</tr>
		<tr>
			<td align="right" id="label_form">Kode Customer</td>
			<td><input name="KODECUSTOMER" id="KODECUSTOMER" style="width:200px"></td>
		</tr>
		<tr>
			<td align="right" id="label_form">Nama</td>
			<td><input name="NAMACUSTOMER" id="NAMACUSTOMER" style="width:300px" class="label_input" readonly></td>
		</tr>
		<tr>
			<td align="right" id="label_form">Alamat</td>
			<td><input name="ALAMAT" id="ALAMAT" style="width:400px" class="label_input" readonly></td>
		</tr>
		<tr>
			<td align="right" id="label_form">Jenis</td>
			<td>
				<select name="JENIS" id="JENIS" style="width:100px">
					<option value="UTAMA">UTAMA</option>
					<option value="REFERENSI">REFERENSI</option>
				</select>
				<span id="label_form" class="label_referensi"> <input name="UPLINE" id="UPLINE" style="width:200px"></span>
			</td>
		</tr>
		<tr>
			<td align="right" id="label_form">No Kartu</td>
			<td><input name="NOKARTU" id="NOKARTU" style="width:150px" class="label_input" validType='length[0,20]'></td>
		</tr>
		<tr>
			<td align="right" id="label_form">Diskon</td>
			<td><input name="DISKON" id="DISKON" style="width:60px" class="number" required="true" min="0" max="100" suffix="%"></td>
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
<input type="hidden" id="mode">

<script type="text/javascript" src="script/jquery-easyui/plugins/datagrid-filter.min.js"></script>

<script>
$(document).ready(function(){
	create_form_login();

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

	$('#KODECUSTOMER').combogrid({
		required:true,
		panelWidth:690,
		remoteFilter:true,
		mode:'remote',		
		idField:'KODE',
		textField:'KODE',
		sortName:'KODE',
		sortOrder:'asc',
		view:bufferview,
		pageSize:50,
		url:'config/combogrid.php?table=customer&member=no',
		columns:[[
			{field:'KODE',title:'Kd. Customer',width:130, sortable:true},
			{field:'NAMA',title:'Nama Customer',width:240, sortable:true},
			{field:'ALAMAT',title:'Alamat',width:240, sortable:true},
			{field:'KOTA',title:'Kota',width:60, sortable:true},
		]],
		onChange:function(){
			var data = $(this).combogrid('grid').datagrid('getSelected');
			$('#NAMACUSTOMER').textbox('setValue', data ? data.NAMA : '');
			$('#ALAMAT').textbox('setValue', data ? data.ALAMAT : '');
		}
	}).combogrid('grid').datagrid('enableFilter');

	$('#UPLINE').combogrid({
		panelWidth:690,
		remoteFilter:true,
		mode:'remote',
		idField:'KODE',
		textField:'NAMA',
		sortName:'KODE',
		sortOrder:'asc',
		view:bufferview,
		pageSize:50,
		url:'config/combogrid.php?table=customer&member=yes',
		columns:[[
			{field:'KODE',title:'Kode',width:140},
			{field:'NAMA',title:'Nama',width:200},
			{field:'ALAMAT',title:'Alamat',width:300},
			{field:'NOKARTU',title:'No. Kartu',width:150},
		]],
		onChange:function(newVal, oldVal){
			var row = $(this).combogrid('grid').datagrid('getSelected');
			if (row) {
				$('#NOKARTU').textbox('setValue', row.NOKARTU);
			} else {
				$('#NOKARTU').textbox('clear');
			}
		}
	}).combogrid('grid').datagrid('enableFilter');

	<?php
	if (isset($_POST['kodecustomer']) and $_POST['kodecustomer'] <> '') {
		echo 'tambah();';
		echo 'get_combogrid_data ($(\'#KODECUSTOMER\'), \''.$_POST['kodecustomer'].'\', \'customer\');';
		unset($_POST['kodecustomer']);
	}
	?>

	$('#JENIS').combobox({
		panelHeight:'auto',
		required:true,
		onChange:function(newVal){
			$('.label_referensi').hide();

			if (newVal == 'REFERENSI')
				$('.label_referensi').show();

			$('#UPLINE').combogrid('clear');
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
	$('#DISKON').numberbox('setValue', 10);

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

		//$('[name=KODECUSTOMER]').prop('readonly', true);
	}
}
function simpan () {
	var isValid = $('#form_input').form('validate');
	if (isValid) {
		act = $('[name=act]').val();
		$.ajax({
			type: 'POST',
			url: "data/process/proses_master.php",
			data: "table=member&"+$('#form_input :input').serialize(),
			dataType: 'json',
			beforeSend : function (){
				$.messager.progress();
			},
			success: function(msg){
				$.messager.progress('close');
				if (msg.success){
					if (act=='insert') tambah();
					else $('#form_input').dialog('close');

					if (act == 'insert') {
						$.messager.confirm('Confirm','Anda Ingin Melanjutkan ke Rekam Medis ?',function(r){
							if (r){
								<?php
								$url_kode = '';
								foreach (json_decode($_SESSION['array_menu']) as $menu) {
									if ($menu->tipe == 'Transaksi' && $menu->menu == 'Rekam Medis') {
										$url_kode = $menu->kode;
										break;
									}
								}
								?>
								var str = 	"<form method='post' action='index.php?kode=<?=$url_kode?>' style='display:none' id='fm_GoMember'>";
								str += 			"<input type='text' name='kodecustomer' value='"+msg.kodecustomer+"'>";
								str += 		"</form>";
								$('body').prepend(str);
								$('#fm_GoMember').submit().remove();
							}
						});
					}
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
				$.post('data/process/proses_master.php',{act:'delete', table:'member', id:row.KODECUSTOMER},function(msg){
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
		url: 'config/datagrid.php?table=member',
		rowStyler: function(index,row){
			if (row.STATUS == 0) return 'background-color:#a8aea6';
		},
		frozenColumns:[[
			{field:'KODEMEMBER',title:'Kode Member',width:130, sortable:true,},
			{field:'KODECUSTOMER',title:'Kode Customer',width:130, sortable:true,},
			{field:'NAMACUSTOMER',title:'Nama',width:200, sortable:true,},
		]],
		columns:[[
			{field:'ALAMAT',title:'Alamat',width:250, sortable:true,},
			{field:'NOKARTU',title:'No Kartu',width:130, sortable:true,},
			{field:'JENIS',title:'Jenis',width:80, sortable:true,},
			{field:'UPLINE',title:'Kode Upline',width:130, sortable:true,},
			{field:'NAMAUPLINE',title:'Nama Upline',width:200, sortable:true,},
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