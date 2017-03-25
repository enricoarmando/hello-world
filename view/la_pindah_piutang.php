<?php
session_start();
if (empty($_SESSION['user'])) header('Location: ../../../index.php');
?>
<form method='post' target='_blank' action='data/report/lap_akun_pindah_piutang.php' id="form_input" style="width:600px">
    <div style="padding:5px;border:1px solid #ddd;">
        <a id="btn_print" href="#" class="easyui-linkbutton" data-options="iconCls:'icon-print', plain:true">Tampilkan Data</a>
		<a id="btn_export_excel" href="#" class="easyui-linkbutton" data-options="iconCls:'icon-excel', plain:true">Export</a>
    </div>

    <table style="border-bottom:1px #000">
        <tr>
            <td align="right" id="label_laporan">Lokasi Asal :</td>
            <td><input id="txt_lokasi_asal" name="txt_lokasi_asal[]" style="width:250px"/></td>
        </tr>                    
        <tr>
            <td align="right" id="label_laporan">Lokasi Baru :</td>
            <td><input id="txt_lokasi_baru" name="txt_lokasi_baru[]" style="width:250px"/></td>
        </tr>                    
        <tr>
            <td align="right" id="label_laporan"><label id="label_laporan">Tgl. Transaksi :</label></td>
            <td id="label_laporan"><input id="txt_tgl_aw" name="txt_tgl_aw" class="date"/> - <input id="txt_tgl_ak" name="txt_tgl_ak" class="date"/></td>
        </tr>        
    </tr>                    
    </table>
    <fieldset id="field">
		<legend id="label_laporan">Status</legend>
        <table width="100%">
			<tr>
				<td width="25%"><label id="label_laporan"> <input type="checkbox" name="cbStatus[]" value="I" checked="checked" /> Input</label></td>
				<td width="25%"><label id="label_laporan"> <input type="checkbox" name="cbStatus[]" value="D" /> Delete</label></td>
			</tr>
		</table>
	</fieldset>
    
	<fieldset id="field">
		<legend id="label_laporan">Jenis Laporan</legend>
    	<table width="100%" id="label_laporan">
            <tr>
                <td width="34%"><label id="label_laporan"><input type="radio" name="rdTampil" value="Register" checked="checked"> Register</label></td>
            </tr>
        </table>
    </fieldset>
	
	<input type="hidden" name="excel" id="excel">
	<input type="hidden" name="file_name" id="file_name" value="LaporanPiutangKartuKredit">
</form>
<script>
$(document).ready(function(){
	browse_data_lokasi('#txt_lokasi_asal', 'lokasi&status=all','KODE');
	browse_data_lokasi('#txt_lokasi_baru', 'lokasi&status=all','KODE');

	$('#txt_tgl_aw, #txt_tgl_ak').datebox('enable');
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

function browse_data_lokasi(id, table, sort) {
	$(id).combogrid({
		panelWidth:380,
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
			{field:'KODE',title:'Kode',width:80, sortable:true},
			{field:'NAMA',title:'Nama',width:240, sortable:true},
		]]
	});
}


</script>