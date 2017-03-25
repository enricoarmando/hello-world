<?php
if (empty($_SESSION['user'])) header('Location: ../../../index.php');
?>
<form method='post' target='_blank' action='data/report/lap_m_customer.php' id="form_input" style="width:600px">
    <div style="padding:5px;border:1px solid #ddd;">
        <a id="btn_print" href="#" class="easyui-linkbutton" data-options="iconCls:'icon-print', plain:true">Tampilkan Data</a>
		<a id="btn_export_excel" href="#" class="easyui-linkbutton" data-options="iconCls:'icon-excel', plain:true">Export</a>
    </div>

    <table style="border-bottom:1px #000" id="label_laporan">
		<tr>
            <td align="right" id="label_laporan">Lokasi :</td>
            <td id="label_laporan"><input id="txt_lokasi" name="txt_lokasi[]" style="width:250px"/></td>
        </tr>
		<tr>
			<td align="right" id="label_laporan">Nama Customer :</td>
			<!--<td><input id="txt_kode_awal" name="txt_kode_awal" style="width:200px"> s/d <input id="txt_kode_akhir" name="txt_kode_akhir" style="width:200px"></td>!-->
			<td><input class="label_input" id="txt_namacustomer" name="txt_namacustomer" style="width:200px"></td>
		</tr>
		<tr>
			<td></td>
			<td align="left" id="label_laporan"><input type="checkbox" id="MEMBER" name="MEMBER" value="1"> Tampilkan Data Customer Yang Menjadi Member</td>
		</tr>
		<tr hidden>
			<td align="right" id="label_laporan">Kode Rekam Medis :</td>
			<td><input class="label_input" id="txt_koderekammedis" name="txt_koderekammedis" style="width:200px"/></td>
		</tr>
	</table>
	
	<fieldset id="field" hidden>
		<legend id="label_laporan">Jenis Laporan</legend>
        <table width="100%" border="0">
           	<tr>
               	<td><label id="label_laporan"><input type="radio" name="rdTampil" value="Customer" checked="checked"> Data Customer</label></td>
				<td hidden><label id="label_laporan"><input type="radio" name="rdTampil" value="RekamMedis" /> Rekam Medis</label></td>
        	</tr>
		</table>
    </fieldset>

	<input type="hidden" name="excel" id="excel">
	<input type="hidden" name="file_name" id="file_name" value="LaporanCustomer">
</form>
<script>
$(document).ready(function(){
    browse_data_lokasi('#txt_lokasi', 'lokasi&status=all','KODE');

	browse_data_customer('#txt_kode_awal', 'customer');
	browse_data_customer('#txt_kode_akhir', 'customer');

	browse_data_rekam_medis('#txt_koderekammedis', 'rekam_medis');
	$('#txt_koderekammedis').combogrid('disabled');
});

$("#btn_export_excel").click(function(){
	$('#excel').val('ya');
	$('#form_input').submit();
	return false;
});
$("#btn_print").click(function(){
	$('#excel').val('tidak');
	$('#form_input').submit();
	return false;
});

$('[name=rdTampil]').change(function(){
	var val = $(this).val();
	if (val=='Customer') {		
		$('#txt_kode_awal, #txt_kode_akhir').combogrid('enable');
		$('#txt_koderekammedis').combogrid('disabled');
	} else if (val=='RekamMedis') {
		$('#txt_kode_akhir').combogrid('disabled');		
		$('#txt_koderekammedis').combogrid('enable');		
	}
});

function browse_data_lokasi(id, table, sort) {
	$(id).combogrid({
		panelWidth:300,
		url: 'config/combogrid.php?table='+table,
		idField:'KODE',
		textField:'KODE',
		multiple: true,
		mode:'local',
		sortName:sort,
		sortOrder:'asc',
		rowStyler: function(index,row){  
			if (row.STATUS == 0){  
				return 'background-color:#A8AEA6';  
			}  
		},
		columns:[[
			{field:'ck',checkbox:true},
			{field:'KODE',title:'ID',width:80, sortable:false},
			{field:'NAMA',title:'Description',width:150, sortable:false},
		]]
	});
}

function browse_data_customer(id, table) {
	$(id).combogrid({
		panelWidth:690,
		url: 'config/combogrid.php?table='+table,
		idField:'KODE',
		textField:'KODE',
		mode:'remote',
		sortName:'KODE',
		sortOrder:'asc',
		view:bufferview,
		rowStyler: function(index,row){  
			if (row.STATUS == 0){  
				return 'background-color:#A8AEA6';  
			}  
		},
		columns:[[
			{field:'KODE',title:'Kd. Customer',width:130, sortable:true},
			{field:'NAMA',title:'Nama Customer',width:240, sortable:true},
			{field:'ALAMAT',title:'Alamat',width:240, sortable:true},
			{field:'KOTA',title:'Kota',width:60, sortable:true},
		]]
	});
}

function browse_data_rekam_medis(id, table) {
	$(id).combogrid({
		panelWidth:690,
		url: 'config/combogrid.php?table='+table+'&customer='+$('#txt_kode_awal').textbox('getValue'),
		idField:'KODEREKAMMEDIS',
		textField:'KODEREKAMMEDIS',
		mode:'remote',
		sortName:'KODEREKAMMEDIS',
		sortOrder:'asc',
		view:bufferview,
		rowStyler: function(index,row){  
			if (row.STATUS == 0){  
				return 'background-color:#A8AEA6';  
			}  
		},
		columns:[[
			{field:'KODEREKAMMEDIS',title:'Kd. Rekam Medis',width:130, sortable:true},
			{field:'TGLTRANS',title:'Tgl. Rekam Medis',width:80,sortable:true,formatter:ubah_tgl_indo,align:'center'},
		]]
	});
}

</script>