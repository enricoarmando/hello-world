<?php
session_start();
if (empty($_SESSION[user])) header('Location: ../../../index.php');
?>
<form method='post' target='_blank' action='data/report/lap_akun_giro.php' id="form_input" style="width:500px">
    <div style="padding:5px;border:1px solid #ddd;">
        <a id="btn_print" href="#" class="easyui-linkbutton" data-options="iconCls:'icon-print', plain:true">Tampilkan Data</a>
		<a id="btn_export_excel" href="#" class="easyui-linkbutton" data-options="iconCls:'icon-excel', plain:true">Export</a>
    </div>
    <!--<table style="border-bottom:1px #000">
        <tr>
            <td align="right" id="label_laporan">No Giro :</td>
            <td><input id="txt_nogiro" name="txt_nogiro[]" style="width:200px"/></td>
        </tr>
    </table>-->
    
    <fieldset id="field">
        <legend id="label_laporan">Jenis Giro</legend>
        <table width="100%" id="label_laporan">
            <tr>
                <td width="30%"><label><input type="radio" name="rdJenis" value="semua" checked="checked"> Semua</label></td>
                <td width="30%"><label><input type="radio" name="rdJenis" value="giromasuk"> Giro Masuk</label></td>
                <td width="30%"><label><input type="radio" name="rdJenis" value="girokeluar"> Giro Keluar</label></td>
            </tr>
        </table> 
    </fieldset>  
    
    <fieldset id="field">
    	<legend id="label_laporan">Status Giro</legend>
        <table width="100%" id="label_laporan">
            <tr>
                <td width="25%"><label><input type="radio" name="rdStatus" value="semua" checked="checked"> Semua</label></td>
                <td width="25%"><label><input type="radio" name="rdStatus" value="belumcair"> Belum Cair</label></td>
                <td width="25%"><label><input type="radio" name="rdStatus" value="cair"> Cair</label></td>
                <td width="25%"><label><input type="radio" name="rdStatus" value="tolakan"> Tolakan</label></td>
            </tr>
        </table> 
    </fieldset>
	
	<input type="hidden" name="excel" id="excel">
	<input type="hidden" name="file_name" id="file_name" value="LaporanGiro">
</div>
<script>
$(document).ready(function(){
	//browse_data('#txt_nogiro', 'nogiro');
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

function browse_data(id, table) {
	$(id).combogrid({
		panelWidth:480,
		url: 'data_browse.php?act=combogrid&table='+table,
		idField:'NOGIRO',
		textField:'NOGIRO',
		mode:'local',
		sortName:'NOGIRO',
		sortOrder:'asc',
		multiple: true,
		columns:[[
			{field:'ck',checkbox:true},
			{field:'NOGIRO',title:'No Giro',width:150, sortable:true},
			{field:'NAMAREFERENSI',title:'Referensi',width:280, sortable:true}
		]]
	});
}
</script>