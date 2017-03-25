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
				<div data-options="name:'a.namacustomer'">Nama</div>
				<div data-options="name:'a.kodecustomer'">Kode</div>
			</div>
		</div>
	</div>
	<div data-options="region:'center',">
		<table id="table_data" idField="KODECUSTOMER"></table>
	</div>
</div>

<div id="form_input">
	<input type="hidden" name="act">
	<table tyle="padding:2px" border="0">
		<tr>
			<td align="right" id="label_form">Kode Customer</td>
			<td><input name="KODECUSTOMER" style="width:150px" class="label_input" prompt="Auto Generate" readonly> 
				<label id="label_form"><input type="checkbox" id="STATUS" name="STATUS" value="1"> Aktif</label>
			</td>
		</tr>
		<tr>
			<td align="right" id="label_form">No Member</td>
			<td><input name="KODEMEMBER" style="width:150px" class="label_input" readonly></td>
		</tr>
		<tr>
			<td align="right" id="label_form">Nama</td>
			<td><input name="NAMACUSTOMER" style="width:300px" class="label_input" required="true" validType='length[0,100]'></td>
		</tr>
		<tr>
			<td align="right" id="label_form">Alamat</td>
			<td><input name="ALAMAT" style="width:400px" class="label_input" required="true" validType='length[0,200]'></td>
		</tr>
		<tr>
			<td align="right" id="label_form">Kota</td>
			<td>
				<input name="KOTA" style="width:150px" class="label_input" required="true" validType='length[0,20]'>
				<!--&nbsp;&nbsp; Propinsi
				<input name="PROPINSI" style="width:150px" class="label_input" validType='length[0,100]'>
				&nbsp;&nbsp;Negara
				<input name="NEGARA" style="width:150px" class="label_input" validType='length[0,100]'>
				!-->
			</td>
		</tr>				
		<tr>
			<td align="right" id="label_form">Telp</td>
			<td><input name="TELP" style="width:250px" class="label_input" validType='length[0,50]'></td>
		</tr>
		<tr>
			<td align="right" id="label_form">HP</td>
			<td><input name="HP" style="width:250px" class="label_input" validType='length[0,50]'></td>
		</tr>
		<tr>
			<td align="right" id="label_form">Tempat/Tgl. Lahir</td>
			<td>
				<input name="TEMPATLAHIR" style="width:150px" class="label_input" validType='length[0,50]'> /
				<input name="TGLLAHIR" id="TGLLAHIR" class="date"/>
			</td>	
		</tr>
		<tr>
			<td align="right" id="label_form">Kelamin</td>
			<td>
				<select name="JENISKELAMIN" style="width:100px" class="easyui-combobox" required="true" panelHeight="auto">
					<option value="">--- Pilih ---</option>
					<option value="L">LAKI-LAKI</option>
					<option value="P">PEREMPUAN</option>
				</select>
			</td>
		</tr>
		<tr>
			<td align="right" id="label_form">Riwayat Kesehatan</td>
			<td><input name="RIWAYATKESEHATAN" style="width:350px" class="label_input" validType='length[0,200]'></td>
		</tr>
		<tr>
			<td align="right" id="label_form">Pekerjaan</td>
			<td><input name="PEKERJAAN" style="width:350px" class="label_input" validType='length[0,200]'></td>
		</tr>
		<tr>
			<td align="right" id="label_form">Hobi</td>
			<td><input name="HOBI" style="width:350px" class="label_input" validType='length[0,100]'></td>
		</tr>
		<tr>
			<td align="right" valign="top" id="label_form">Catatan</td>
			<td><textarea name="CATATAN" style="width:350px; height:70px" class="label_input" multiline="true" validType='length[0,300]'></textarea></td>
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

<div id="dlg-member-rekam-medis" class="easyui-dialog" modal="true" title="Confirm">
	<div class="messager-body" title="" style="width: 330px; height: auto; padding:10px">
		<div class="messager-icon messager-question"></div>
		<div>Apakah Anda Ingin Melanjutkan Input Data Member ?</div>
		<div style="clear:both;"></div>
		<div class="messager-button">
			<a href="javascript:void(0)" class="easyui-linkbutton" onclick="tambah_master_member()">Ya</a>
			<a href="javascript:void(0)" class="easyui-linkbutton" onclick="$('#dlg-member-rekam-medis').dialog('close')">Tidak</a>
			<a href="javascript:void(0)" class="easyui-linkbutton" onclick="tambah_rekam_medis()" style="width:40%">Lanjut Rekam Medis</a>
		</div>
	</div>
</div>

<script type="text/javascript" src="script/jquery-easyui/plugins/datagrid-filter.min.js"></script>

<script>

// deklarasi variabel kodecustomer untuk tambah member/rekam medis
var kodecustomer;
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
	
	$('#dlg-member-rekam-medis').dialog('close');
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
	//$('[name=KODECUSTOMER]').prop('readonly', false);
	
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
			data: "table=customer&"+$('#form_input :input').serialize(),
			dataType: 'json',
			beforeSend : function (){
				$.messager.progress();
			},
			success: function(msg){
				$.messager.progress('close');
				if (msg.success){
					if (act=='insert') tambah();
					else $('#form_input').dialog('close');
					
					kodecustomer = msg.kode;
					
					if (act == 'insert') {
						$('#dlg-member-rekam-medis').dialog('open');
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
				$.post('data/process/proses_master.php',{act:'delete', table:'customer', id:row.KODECUSTOMER},function(msg){
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

// fungsi tambahan
function tambah_master_member () {
	<?php
	$url_kode = '';
	foreach (json_decode($_SESSION['array_menu']) as $menu) {
		if ($menu->tipe == 'Master' && $menu->menu == 'Member') {
			$url_kode = $menu->kode;
			break;
		}
	}
	?>
	var str = 	"<form method='post' action='index.php?kode=<?=$url_kode?>' style='display:none' id='fm_GoMember'>";
	str += 			"<input type='text' name='kodecustomer' value='"+kodecustomer+"'>";
	str += 		"</form>";
	$('body').prepend(str);
	$('#fm_GoMember').submit().remove();
	
	kodecustomer = '';
}

function tambah_rekam_medis () {
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
	str += 			"<input type='text' name='kodecustomer' value='"+kodecustomer+"'>";
	str += 		"</form>";
	$('body').prepend(str);
	$('#fm_GoMember').submit().remove();
	
	kodecustomer = '';
}

function buat_table () {
	$('#table_data').datagrid({
		remoteFilter:true,
		fit:true,
		singleSelect:true,
		striped:true,
		pagination:true,
		pageSize:20,
		url: 'config/datagrid.php?table=customer',
		rowStyler: function(index,row){  
			if (row.STATUS == 0) return 'background-color:#a8aea6';
		},
		frozenColumns:[[
			{field:'KODECUSTOMER',title:'Kode',width:130, sortable:true,},
			{field:'KODEMEMBER',title:'Kode Member',width:130, sortable:true,},
			{field:'NAMACUSTOMER',title:'Nama',width:200, sortable:true,},
		]],
		columns:[[
			{field:'ALAMAT',title:'Alamat',width:250, sortable:true,},
			{field:'KOTA',title:'Kota',width:130, sortable:true,},
			//{field:'PROPINSI',title:'Propinsi',width:130, sortable:true,},
			//{field:'NEGARA',title:'Negara',width:130, sortable:true,},
			//{field:'KODEPOS',title:'Kode Pos',width:80, sortable:true,},
			{field:'TELP',title:'Telp.',width:100, sortable:true, },
			{field:'HP',title:'HP',width:100, sortable:true, },
			{field:'TEMPATLAHIR',title:'Tempat Lahir',width:100, sortable:true, },
			{field:'TGLLAHIR',title:'Tgl. Lahir',width:80, sortable:true, },
			{field:'JENISKELAMIN',title:'Kelamin',width:70, sortable:true,formatter:function(val, row){
				return val == 'L' ? 'LAKI-LAKI' : 'PEREMPUAN';
			}},
			//{field:'AGAMA',title:'Agama',width:100, sortable:true, },
			{field:'RIWAYATKESEHATAN',title:'Riwayat Kesehatan',width:150, sortable:true, },
			{field:'PEKERJAAN',title:'Pekerjaan',width:150, sortable:true, },
			{field:'HOBI',title:'Hobi',width:150, sortable:true, },
			//{field:'FAX',title:'Fax',width:100, sortable:true, },
			//{field:'EMAIL',title:'E-Mail',width:100, sortable:true, },
			//{field:'WEBSITE',title:'Website',width:100, sortable:true, },
			//{field:'ALAMATKIRIM',title:'Alamat Kirim',width:250, sortable:true,},
			//{field:'KOTAKIRIM',title:'Kota Kirim',width:130, sortable:true, },
			//{field:'PROPINSIKIRIM',title:'Propinsi Kirim',width:130, sortable:true, },
			//{field:'NEGARAKIRIM',title:'Negara Kirim',width:130, sortable:true,},
			//{field:'TELPKIRIM',title:'Telp. Kirim',width:100, sortable:true, },
			//{field:'KODEPOSKIRIM',title:'Kode Pos Kirim',width:80, sortable:true,},
			//{field:'CONTACTPERSON',title:'Contact Person',width:100, sortable:true, },
			//{field:'TELPCP',title:'Telp. CP',width:100, sortable:true, },
			//{field:'EMAILCP',title:'E-Mail CP',width:100, sortable:true, },
			//{field:'NAMASYARATBAYAR',title:'Syarat Bayar',width:100, sortable:true,},
			//{field:'MAXCREDIT', title:'Maks. Piutang',width:100, sortable:true, align:'right', formatter:format_amount,},
			//{field:'DISC', title:'Diskon', width:100, sortable:true, align:'right', formatter:format_amount,},
			{field:'REMARK',title:'Catatan',width:250, sortable:true,},
			//{field:'NAMAFAKTURPAJAK',title:'Nama di Faktur Pajak',width:200, sortable:true,},
			//{field:'ALAMATFAKTURPAJAK',title:'Alamat di Faktur Pajak',width:450,},
			//{field:'NPWP',title:'NPWP',width:100, sortable:true, },
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
