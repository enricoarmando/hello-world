<?php
session_start();
if (empty($_SESSION['user'])) header('Location: ../../../index.php');
?>
<form method='post' target='_blank' action='data/report/lap_akun_hutang_komisi.php' id="form_input" style="width:600px">
    <div style="padding:5px;border:1px solid #ddd;">
        <a id="btn_print" href="#" class="easyui-linkbutton" data-options="iconCls:'icon-print', plain:true">Tampilkan Data</a>
		<a id="btn_export_excel" href="#" class="easyui-linkbutton" data-options="iconCls:'icon-excel', plain:true">Export</a>
    </div>

    <table width="662" style="border-bottom:1px #000">
        <tr>
			<td align="right"><label id="label_laporan">Instansi :</label></td>
			<td id="label_laporan"><input id="txt_instansi" name="txt_instansi" style="width:150px"/> </td>
        </tr>
        <tr>
			<td width="123" align="right"><label id="label_laporan"><input type="radio" name="rd_supp" value="1" checked> Juru Bayar :</label></td>
			<td width="485" id="label_laporan"><input id="txt_jurubayar_awal_list" name="txt_jurubayar_awal_list" style="width:150px"/> Daftar Filter <input id="txt_jurubayar_akhir_list" name="txt_supplier_akhir_list[]" style="width:250px"/> <a id="" href="#" class="easyui-linkbutton" data-options="iconCls:'icon-remove', plain:true" onclick="clear_data('#txt_jurubayar_akhir_list')"></a></td>
        </tr>
		<tr>
			<td align="right"><label id="label_laporan"><input type="radio" name="rd_supp" value="2"> Juru Bayar :</label></td>
			<td id="label_laporan"><input id="txt_jurubayar_awal" name="txt_jurubayar_awal" style="width:150px"/> - <input id="txt_jurubayar_akhir" name="txt_jurubayar_akhir" style="width:150px"/></td>
        </tr>
        <tr hidden>
            <td align="right"><label id="label_laporan"><input type="checkbox" name="cbTglInput" id="cbTglInput" value="1"/> Tgl. Input :</label></td>
			<td id="label_laporan"><input id="txt_tglinput_aw" name="txt_tglinput_aw" class="date"/> - <input id="txt_tglinput_ak" name="txt_tglinput_ak" class="date"/></td>
        </tr>
        <tr>
            <td align="right">Trans. Tgl. Transaksi :</label></td>
            <td id="label_laporan"><input id="txt_tgl_aw" name="txt_tgl_aw" class="date"/> - <input id="txt_tgl_ak" name="txt_tgl_ak" class="date"/></td>
        </tr>        
    </table>
    
    <fieldset id="field">
    <legend id="label_laporan">Status</legend>
        <table width="111%">
            <tr>
                <td width="33%"><label id="label_laporan"><input type="radio" name="rdStatus" value="semua" checked="checked"> Semua</label></td>
                <td width="33%"><label id="label_laporan"><input type="radio" name="rdStatus" value="belumlunas"> Belum Lunas</label></td>
                <td width="33%"><label id="label_laporan"><input type="radio" name="rdStatus" value="lunas"> Lunas</label></td>
            </tr>
        </table> 
    </fieldset>  

	<fieldset id="field">
	    <legend id="label_laporan">Tampilkan Secara</legend>
    	<table width="111%">
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
				<td></td> 
            </tr>
        </table> 
    </fieldset>
	
	<input type="hidden" name="excel" id="excel">
	<input type="hidden" name="file_name" id="file_name" value="LaporanHutangDanPelunasanHutang">
</form>
<script>
$(document).ready(function(){
	browse_data_instansi('#txt_instansi', 'instansi','KODE');
	browse_data_jurubayar('#txt_jurubayar_awal_list', 'juru_bayar','NAMA');
	browse_data_jurubayar('#txt_jurubayar_awal', 'juru_bayar','NAMA');
	browse_data_jurubayar('#txt_jurubayar_akhir', 'juru_bayar','NAMA');
	//browse_data_supplier('#txt_supplier_akhir_list', 'supplier&status=all','NAMA');
	
	
	$('#txt_jurubayar_akhir_list').combogrid({
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

	$('#txt_jurubayar_awal, #txt_jurubayar_akhir').datebox('disable');
	$('#txt_tgl_aw, #txt_tgl_ak').datebox('enable');
});
	
$('[name=rdTampil]').change(function(){
	var val = $(this).val();
	if (val=='DetailHutang') {						
		$('#txt_tgl_aw, #txt_tgl_ak').datebox('enable');
	} else if (val=='RekapHutang' || val=='KartuHutang') {				
		$('#txt_tgl_aw, #txt_tgl_ak').datebox('enable');
	} else if (val=='RincianPelunasanHutang' || val=='RekapPelunasanHutang') {		
		$('#txt_tgl_aw, #txt_tgl_ak').datebox('enable');		
	} else if (val=='KasBelumDigunakan') {	
		$('#txt_tgl_aw, #txt_tgl_ak').datebox('disable');
	}
});

$("[name=rd_supp]").change(function(){
	var cg = $(this).val()==1 ? 'disable' : 'enable';
	var list = $(this).val()==2 ? 'disable' : 'enable';
	
	$('#txt_jurubayar_awal, #txt_jurubayar_akhir').combogrid(cg).combogrid('clear');
	$('#txt_jurubayar_awal_list, #txt_jurubayar_akhir_list').combogrid(list).combogrid('clear');
	
	$('#txt_jurubayar_akhir_list').combogrid('grid').datagrid('loadData', []);
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

function browse_data_instansi(id, table, sort) {
	$(id).combogrid({
		panelWidth:690,
		url: 'config/combogrid.php?table='+table,
		idField:'KODE',
		textField:'NAMA',
		mode:'remote',
		sortName:'KODE',
		sortOrder:'asc',
		pageSize:20,
		view:bufferview,
		columns:[[
			{field:'KODE',title:'Kd. Instansi',width:130, sortable:true},
			{field:'NAMA',title:'Nama Instansi',width:240, sortable:true},
			{field:'ALAMAT',title:'Alamat',width:240, sortable:true},
			{field:'KOTA',title:'Kota',width:60, sortable:true},
		]],
		onSelect:function(index, data){
			/*if (id=='#txt_intansi_awal_list') {
				var dg = $('#txt_instansi_akhir_list').combogrid('grid');
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
				$('#txt_instansi_akhir_list').combogrid('showPanel').combogrid('grid').datagrid('selectAll');
				$('#txt_instansi_akhir_list').combogrid('textbox').val('');
				$('#txt_instansi_awal_list').combogrid('clear');
			}*/
		}
	});
}

function browse_data_jurubayar(id, table, sort) {
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
			if (id=='#txt_jurubayar_awal_list') {
				var dg = $('#txt_jurubayar_akhir_list').combogrid('grid');
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
				$('#txt_jurubayar_akhir_list').combogrid('showPanel').combogrid('grid').datagrid('selectAll');
				$('#txt_jurubayar_akhir_list').combogrid('textbox').val('');
				$('#txt_jurubayar_awal_list').combogrid('clear');
			}
		}
	});
}
</script>