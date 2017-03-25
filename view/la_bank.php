<?php
if (empty($_SESSION['user'])) header('Location: ../../../index.php');
?>
<form method='post' target='_blank' action='data/report/lap_akun_kas.php' id="form_input" style="width:500px">
	<div style="padding:5px;border:1px solid #ddd;">
        <a id="btn_print" href="#" class="easyui-linkbutton" data-options="iconCls:'icon-print', plain:true">Tampilkan Data</a>
		<a id="btn_export_excel" href="#" class="easyui-linkbutton" data-options="iconCls:'icon-excel', plain:true">Export</a>
    </div>

	<table style="border-bottom:1px #000">
        <tr>
            <td align="right" id="label_laporan">Lokasi :</td>
            <td><input id="txt_lokasi" name="txt_lokasi" style="width:100px"/></td>
        </tr>                    
        <tr>
            <td align="right" id="label_laporan">Tgl. Transaksi :</td>
            <td><input id="txt_tgl_aw" name="txt_tgl_aw" class="date">&nbsp; s/d &nbsp;
				<input id="txt_tgl_ak" name="txt_tgl_ak" class="date"/>
			</td>
        </tr>                    
    </table>
    
    <fieldset id="field">
        <legend id="label_laporan">Transaksi</legend>
        <table width="410" id="label_laporan">
            <tr>
                <td width="50%"><label id="label_laporan"><input type="checkbox" class="jtrans" name="cbJenis[]" value="BANK MASUK" checked="checked">Bank Masuk</label></td>
                <td width=""><label id="label_laporan"><input type="checkbox" class="jtrans" name="cbJenis[]" value="BANK KELUAR" >Bank Keluar</label></td>
            </tr>
        </table>
    </fieldset>
    
    <fieldset id="field">
        <legend id="label_laporan">Tampilkan Berdasarkan</legend>
        <table width="466" id="label_laporan">
            <tr>
                <td width="43%"><label id="label_laporan"><input type="radio" name="rdTampil" value="Harian" checked="checked">Bank Harian</label></td>
                <td width="39%"><label id="label_laporan"><input type="radio" name="rdTampil" value="Detail">Detail</label></td>
                <td width="18%"><label id="label_laporan"><input type="radio" name="rdTampil" value="Register">Register</label></td>
            </tr>
        </table>
    </fieldset> 
    <fieldset id="field">
        <legend id="label_laporan">Akun Bank</legend>
        <div style='overflow:auto; height:150px;'>
			<table id="table_kode_perkiraan"></table>
        </div>
    </fieldset>
	
	<input type="hidden" name="KeteranganHeader" id="KeteranganHeader" value="Laporan Bank">
	<input type="hidden" name="excel" id="excel">
	<input type="hidden" name="file_name" id="file_name" value="LaporanKasBankGiro">
</form>
<script>
$(document).ready(function(){
	browse_data_lokasi('#txt_lokasi', 'lokasi&status=all','KODE');
    $("#txt_tgl_ak").datebox('disable');	
		
	$(".jtrans").each(function(){
		if ($(this).val()=="BANK MASUK" || $(this).val()=="BANK KELUAR") {
			$(this).prop("checked", true);
		} else {
			$(this).prop({"checked":false, "disabled":true});
		}
	});
	$.ajax({
		type: 'POST',
		dataType: 'json',
		url: "config/combogrid.php?table=kode_perkiraan&jenis=bank", 
		data: "", 
		cache: false, 
		success: function(msg){
			var temp_html = '';
			for (var i=0; i<msg.length; i++) {
				temp_html += '<tr><td><label id="label_laporan"><input type="checkbox" name="cbPerkiraan[]" value="'+msg[i].KODE+'"> '+msg[i].KODE+' - '+msg[i].NAMA+'</label></td></tr>';
			}
			$('#table_kode_perkiraan').html(temp_html);
		}
	});
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
		multiple: false,
		mode:'local',
		sortName:sort,
		sortOrder:'asc',
		rowStyler: function(index,row){  
			if (row.STATUS == 0){  
				return 'background-color:#A8AEA6';  
			}  
		},
		columns:[[
			//{field:'ck',checkbox:true},
			{field:'KODE',title:'Kode',width:80, sortable:true},
			{field:'NAMA',title:'Nama',width:240, sortable:true},
		]]
	});
}

function browse_data(id, table) {
	$(id).combogrid({
		panelWidth:500,
		url: 'config/combogrid.php?table='+table,
		idField:'KODE',
		textField:'KODE',
		mode:'local',
		sortName:'KODE',
		sortOrder:'asc',
		rowStyler: function(index,row){  
			if (row.STATUS == 0) {
				return 'background-color:#A8AEA6';  
			}  
		},
		columns:[[
			{field:'KODE',title:'ID',width:70, sortable:true},
			{field:'NAMA',title:'Description',width:250, sortable:true},
			{field:'KELOMPOK',title:'Group',width:100, sortable:true},
			{field:'SALDO',title:'Saldo',width:50, sortable:true}
		]]
	});
}

$("[name=rdTampil]").change(function(){
	if ($(this).val()=="Daily" || $(this).val()=="Detail") {
	    if ($(this).val()=="Daily") {
			$("#txt_tgl_ak").datebox('disable');
		} else {
			$("#txt_tgl_ak").datebox('enable');
		}
		
		$(".jtrans").each(function(){
			jtrans = $(this).val(); 
			if (jtrans=="BANK MASUK" || jtrans=="BANK KELUAR") {
				$(this).prop("checked", true);
			} else {
				$(this).prop({"checked":false, "disabled":true});
			}
		});
		
		$("[name='cbPerkiraan[]']").prop({"checked":false, "disabled":false});
	} else {
		$(".jtrans").each(function(){
			$(this).prop({"checked":false, "disabled":false});
		});
		$("[name='cbPerkiraan[]']").prop({"checked":false, "disabled":false});
	    $("#txt_tgl_ak").datebox('enable');	
	}
});
</script>