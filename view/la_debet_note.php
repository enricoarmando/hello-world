<?php
session_start();
if (empty($_SESSION['user'])) header('Location: ../../../index.php');
?>
<form method='post' target='_blank' action='data/report/lap_akun_debet.php' id="form_input" style="width:710px">
    <div style="padding:5px;border:1px solid #ddd;">
        <a id="btn_print" href="#" class="easyui-linkbutton" data-options="iconCls:'icon-print', plain:true">Tampilkan Data</a>
		<a id="btn_export_excel" href="#" class="easyui-linkbutton" data-options="iconCls:'icon-excel', plain:true">Export</a>
    </div>

    <table style="border-bottom:1px #000">
        <tr>
			<td align="right" id="label_laporan">Supplier :</td>
            <td id="label_laporan"><input id="txt_supplier_awal" name="txt_supplier_awal" style="width:250px"/> Daftar Filter  <input id="txt_supplier_akhir" name="txt_supplier_akhir[]" style="width:250px"/> <a id="" href="#" class="easyui-linkbutton" data-options="iconCls:'icon-remove', plain:true" onclick="clear_data('#txt_supplier_akhir')"></a></td>
        </tr>
        <tr>
			<td align="right" id="label_laporan">Tgl. Transaksi :</td>
			<td id="label_laporan">
				<input id="txt_tgl_aw" name="txt_tgl_aw" class="date"/>
				-
				<input id="txt_tgl_ak" name="txt_tgl_ak" class="date"/>
			</td>
        </tr>                  
    </table>
    
	<!--<fieldset id="field">
	<legend id="label_laporan">Tampilkan Secara</legend>
    	<table width="702" id="label_laporan">
            <tr>
                <td width="30%" height="25"><label><input type="radio" name="rdTampil" value="DetailDebet" checked="checked"> Register </label></td>
				<td width="36%">&nbsp;</td>
            </tr>
        </table> 
    </fieldset>
	!-->
	<input type="hidden" name="excel" id="excel">
	<input type="hidden" name="file_name" id="file_name" value="LaporanDebetNote">
</form>
<script>
$(document).ready(function(){
	browse_data_supplier('#txt_supplier_awal', 'supplier&status=all', 'KODE');
	//browse_data_supplier('#txt_supplier_akhir', 'supplier&status=all', 'KODE');
	
	$('#txt_supplier_akhir').combogrid({
		panelWidth:300,
		idField:'KODE',
		mode:'local',
		multiple: true,
		columns:[[
			{field:'KODE',title:'Kode',width:60, sortable:true},
			{field:'NAMA',title:'Nama',width:220, sortable:true},
		]],
		data:[],
		onSelect:function(){
			$(this).combogrid('textbox').val('');
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

function browse_data_supplier(id, table, sort) {
	$(id).combogrid({
		panelWidth:380,
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
			{field:'KODE',title:'Kode',width:80, sortable:true},
			{field:'NAMA',title:'Nama',width:240, sortable:true},
		]],
		onSelect:function(index, data){
			var dg = $('#txt_supplier_akhir').combogrid('grid');
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
			$('#txt_supplier_akhir').combogrid('showPanel').combogrid('grid').datagrid('selectAll');
			$('#txt_supplier_akhir').combogrid('textbox').val('');
			$('#txt_supplier_awal').combogrid('clear');
		}
	});
}
</script>