<?php
session_start();
if (empty($_SESSION['user'])) header('Location: ../../../index.php');
?>
<form method='post' target='_blank' action='data/report/lap_akun_hutang.php' id="form_input" style="width:700px">
    <div style="padding:5px;border:1px solid #ddd;">
        <a id="btn_print" href="#" class="easyui-linkbutton" data-options="iconCls:'icon-print', plain:true">Tampilkan Data</a>
		<a id="btn_export_excel" href="#" class="easyui-linkbutton" data-options="iconCls:'icon-excel', plain:true">Export</a>
    </div>

    <table width="642" style="border-bottom:1px #000">
        <tr>
            <td width="109" align="right" id="label_laporan">Lokasi :</td>
            <td width="599"><input id="txt_lokasi" name="txt_lokasi[]" style="width:250px"/></td>
        </tr>                    
        <tr>
			<td align="right"><label id="label_laporan"><input type="radio" name="rd_supp" value="1" checked> Supplier :</label></td>
			<td id="label_laporan"><input id="txt_supplier_awal_list" name="txt_supplier_awal_list" style="width:150px"/> Daftar Filter <input id="txt_supplier_akhir_list" name="txt_supplier_akhir_list[]" style="width:250px"/> <a id="" href="#" class="easyui-linkbutton" data-options="iconCls:'icon-remove', plain:true" onclick="clear_data('#txt_supplier_akhir_list')"></a></td>
        </tr>
		<tr>
			<td align="right"><label id="label_laporan"><input type="radio" name="rd_supp" value="2"> Supplier :</label></td>
			<td id="label_laporan"><input id="txt_supplier_awal" name="txt_supplier_awal" style="width:150px"/> - <input id="txt_supplier_akhir" name="txt_supplier_akhir" style="width:150px"/></td>
        </tr>
        <tr>
            <td align="right"><label id="label_laporan">
			Tgl. Transaksi :</label></td>
            <td id="label_laporan"><input id="txt_tgl_aw" name="txt_tgl_aw" class="date"/> - <input id="txt_tgl_ak" name="txt_tgl_ak" class="date"/></td>
        </tr>        
        <tr>
            <td align="right" id="label_laporan">Saldo :</td>
            <td id="label_laporan"><input id="txt_amount_aw" name="txt_amount_aw" style="width:100px;" class="label_input number"/> - <input id="txt_amount_ak" name="txt_amount_ak" style="width:100px;" class="label_input  number"/></td>
        </tr>                    
    </table>
    
    <fieldset id="field">
    <legend id="label_laporan">Status</legend>
        <table width="92%">
            <tr>
                <td width="33%"><label id="label_laporan"><input type="radio" name="rdStatus" value="semua" checked="checked"> Semua</label></td>
                <td width="33%"><label id="label_laporan"><input type="radio" name="rdStatus" value="belumlunas"> Belum Lunas</label></td>
                <td width="33%"><label id="label_laporan"><input type="radio" name="rdStatus" value="lunas"> Lunas</label></td>
            </tr>
        </table> 
    </fieldset>  

	<fieldset id="field">
	    <legend id="label_laporan">Tampilkan Secara</legend>
    	<table width="92%">
            <tr>
                <td width="25%"><label id="label_laporan"><input type="radio" name="rdTampil" value="DetailHutang" checked="checked"> Detail Hutang</label></td>
                <td width="35%"><label id="label_laporan"><input type="radio" name="rdTampil" value="RincianPelunasanHutang"> Rincian Pelunasan Hutang</label></td>
            </tr>
            <tr>
                <td><label id="label_laporan"><input type="radio" name="rdTampil" value="KartuHutang"> Kartu Hutang</label></td>
                <td><label id="label_laporan"><input type="radio" name="rdTampil" value="RekapPelunasanHutang"> Rekapitulasi Pelunasan Hutang</label></td>
            </tr>
            <tr>
                <td width="40%"><label id="label_laporan"><input type="radio" name="rdTampil" value="RekapHutang"> Rekapitulasi Hutang</label></td>
				<td hidden><label id="label_laporan"><input type="radio" name="rdTampil" value="KasBelumDigunakan" /> Kas/Bank/Giro Belum Digunakan Pelunasan</label></td> 
            </tr>
        </table> 
    </fieldset>
	
	<input type="hidden" name="excel" id="excel">
	<input type="hidden" name="file_name" id="file_name" value="LaporanHutangDanPelunasanHutang">
</form>
<script>
$(document).ready(function(){
	browse_data_lokasi('#txt_lokasi', 'lokasi&status=all','KODE');
	browse_data_supplier('#txt_supplier_awal_list', 'supplier&status=all','NAMA');
	browse_data_supplier('#txt_supplier_awal', 'supplier&status=all','NAMA');
	browse_data_supplier('#txt_supplier_akhir', 'supplier&status=all','NAMA');
	//browse_data_supplier('#txt_supplier_akhir_list', 'supplier&status=all','NAMA');
	
	
	$('#txt_supplier_akhir_list').combogrid({
		panelWidth:730,
		idField:'KODE',
		mode:'local',
		multiple: true,
		columns:[[
			{field:'KODE',title:'Kode',width:150, sortable:true},
			{field:'NAMA',title:'Nama',width:240, sortable:true},
			{field:'ALAMAT',title:'Alamat',width:240, sortable:true},
			{field:'KOTA',title:'Kota',width:80, sortable:true},
		]],
		data:[],
		onSelect:function(){
			$(this).combogrid('textbox').val('');
		}
	});

	$('[name=rdStatus]').add($("#txt_amount_aw, #txt_amount_ak")).prop('disabled', false);
	$("#cbTgl").prop("disabled",false).prop('checked',true);
	$("#cbTempo").prop("disabled",false).prop('checked',false);
	$("#cbTglInput").prop("disabled",true).prop('checked',false);

	$('#txt_tglinput_aw, #txt_tgljatuhtempo, #txt_tglinput_ak, #txt_customer_awal, #txt_customer_akhir').datebox('disable');
	$('#txt_tgl_aw, #txt_tgl_ak').datebox('enable');
});
	
$('[name=rdTampil]').change(function(){
	var val = $(this).val();
	if (val=='DetailHutang') {
		$('[name=rdStatus]').add($("#txt_amount_aw, #txt_amount_ak")).prop('disabled', false);
		
		$("#cbTgl").prop("disabled",false).prop('checked',true);
		$("#cbTempo").prop("disabled",false).prop('checked',false);
		$("#cbTglInput").prop("disabled",true).prop('checked',false);
		
		$('#txt_tgljatuhtempo, #txt_tglinput_aw, #txt_tglinput_ak').datebox('disable');
		$('#txt_tgl_aw, #txt_tgl_ak').datebox('enable');
	} else if (val=='RekapHutang' || val=='KartuHutang') {
		$('[name=rdStatus]').add($("#txt_amount_aw, #txt_amount_ak")).prop('disabled', true);
		
		$("#cbTempo, #cbTglInput").prop("disabled",true).prop('checked',false);
		$("#cbTgl").prop("disabled",false).prop('checked',true);
		
		$('#txt_tglinput_aw, #txt_tglinput_ak, #txt_tgljatuhtempo').datebox('disable');
		$('#txt_tgl_aw, #txt_tgl_ak').datebox('enable');
	} else if (val=='RincianPelunasanHutang' || val=='RekapPelunasanHutang') {
		$('[name=rdStatus]').add($("#txt_amount_aw, #txt_amount_ak")).prop('disabled', true);
		
		$("#cbTempo").prop("disabled",true).prop('checked',false);
		$("#cbTgl, #cbTglInput").prop("disabled",false).prop('checked',true);
		
		$('#txt_tglinput_aw, #txt_tglinput_ak, #txt_tgl_aw, #txt_tgl_ak').datebox('enable');
		$('#txt_tgljatuhtempo').datebox('disable');
	} else if (val=='KasBelumDigunakan') {
		$('[name=rdStatus]').add($("#txt_amount_aw, #txt_amount_ak")).prop('disabled', true);
	
		$("#cbTempo, #cbTgl, #cbTglInput").prop("disabled",true).prop('checked',false);
	
		$('#txt_tglinput_aw, #txt_tglinput_ak, #txt_tgl_aw, #txt_tgl_ak, #txt_tgljatuhtempo').datebox('disable');
	}
});

$("[name=rd_supp]").change(function(){
	var cg = $(this).val()==1 ? 'disable' : 'enable';
	var list = $(this).val()==2 ? 'disable' : 'enable';
	
	$('#txt_supplier_awal, #txt_supplier_akhir').combogrid(cg).combogrid('clear');
	$('#txt_supplier_awal_list, #txt_supplier_akhir_list').combogrid(list).combogrid('clear');
	
	$('#txt_supplier_akhir_list').combogrid('grid').datagrid('loadData', []);
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

function browse_data_supplier(id, table, sort) {
	$(id).combogrid({
		panelWidth:730,
		url: 'config/combogrid.php?table='+table,
		idField:'NAMA',
		textField:'NAMA',
		mode:'remote',
		sortName:sort,
		sortOrder:'asc',
		pageSize:20,
		view:bufferview,
		rowStyler: function(index,row){  
			if (row.STATUS == 0){  
				return 'background-color:#A8AEA6';  
			}  
		},
		columns:[[
			{field:'KODE',title:'Kode',width:150, sortable:true},
			{field:'NAMA',title:'Nama',width:240, sortable:true},
			{field:'ALAMAT',title:'Alamat',width:240, sortable:true},
			{field:'KOTA',title:'Kota',width:80, sortable:true},
		]],
		onSelect:function(index, data){
			if (id=='#txt_supplier_awal_list') {
				var dg = $('#txt_supplier_akhir_list').combogrid('grid');
				var rows = dg.datagrid('getRows');
				var insert = true;
				jQuery.each(rows, function() {
					if (this.KODE==data.KODE) {
						insert = false;
					}
				});
				if (insert) {
					dg.datagrid('insertRow',{
						row:data
					});
				}
				$('#txt_supplier_akhir_list').combogrid('showPanel').combogrid('grid').datagrid('selectAll');
				$('#txt_supplier_akhir_list').combogrid('textbox').val('');
				$('#txt_supplier_awal_list').combogrid('clear');
			}
		}
	});
}
</script>