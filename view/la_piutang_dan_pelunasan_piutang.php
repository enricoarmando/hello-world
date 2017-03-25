<?php
session_start();
if (empty($_SESSION['user'])) header('Location: ../../../index.php');
?>
<form method='post' target='_blank' action='data/report/lap_akun_piutang.php' id="form_input" style="width:600px">
    <div style="padding:5px;border:1px solid #ddd;">
        <a id="btn_print" href="#" class="easyui-linkbutton" data-options="iconCls:'icon-print', plain:true">Tampilkan Data</a>
		<a id="btn_export_excel" href="#" class="easyui-linkbutton" data-options="iconCls:'icon-excel', plain:true">Export</a>
    </div>

    <table style="border-bottom:1px #000">
        <tr>
            <td align="right" id="label_laporan">Lokasi :</td>
            <td><input id="txt_lokasi" name="txt_lokasi[]" style="width:250px"/></td>
        </tr>                    
    	<!--<tr>
			<td align="right" id="label_laporan">Marketing :</td>
			<td id="label_laporan"><input id="txt_salesman_awal" name="txt_salesman_awal" style="width:250px"/> - <input id="txt_salesman_akhir" name="txt_salesman_akhir" style="width:250px"/></td>
        </tr> -->
        <tr>
			<td align="right"><label id="label_laporan">Instansi :</label></td>
			<td id="label_laporan"><input id="txt_instansi" name="txt_instansi" style="width:150px"/> </td>
        </tr>
        <tr>
			<td align="right"><label id="label_laporan"><input type="radio" name="rd_cust" value="1" checked> Customer :</label></td>
			<td id="label_laporan"><input id="txt_customer_awal_list" name="txt_customer_awal_list" style="width:150px"/> Daftar Filter <input id="txt_customer_akhir_list" name="txt_customer_akhir_list[]" style="width:150px"/> <a id="" href="#" class="easyui-linkbutton" data-options="iconCls:'icon-remove', plain:true" onclick="clear_data('#txt_customer_akhir_list')"></a></td>
        </tr>
		<tr>
			<td align="right"><label id="label_laporan"><input type="radio" name="rd_cust" value="2"> Customer :</label></td>
			<td id="label_laporan"><input id="txt_customer_awal" name="txt_customer_awal" style="width:150px"/> - <input id="txt_customer_akhir" name="txt_customer_akhir" style="width:250px"/></td>
        </tr>
		<tr hidden>
            <td align="right"><label id="label_laporan"><input type="checkbox" name="cbTglInput" id="cbTglInput" value="1"/> Tgl. Input :</label></td>
			<td id="label_laporan"><input id="txt_tglinput_aw" name="txt_tglinput_aw" class="date"/> - <input id="txt_tglinput_ak" name="txt_tglinput_ak" class="date"/></td>
        </tr>
        <tr>
            <td align="right" id="label_laporan"><label id="label_laporan"><input type="checkbox" name="cbTgl" id="cbTgl" value="1"/> Tgl. Transaksi :</label></td>
            <td id="label_laporan"><input id="txt_tgl_aw" name="txt_tgl_aw" class="date"/> - <input id="txt_tgl_ak" name="txt_tgl_ak" class="date"/></td>
        </tr>        
		<tr>
            <td align="right" ><label id="label_laporan"><input type="checkbox" name="cbTempo" id="cbTempo" value="1"/> Tgl. Jatuh Tempo :</label></td>
            <td><input id="txt_tgljatuhtempo" name="txt_tgljatuhtempo" class="date"/></td>
        </tr>
<!--        <tr>
            <td align="right" id="label_laporan">Saldo :</td>
            <td id="label_laporan"><input id="txt_amount_aw" name="txt_amount_aw" style="width:100px;" class="label_input number"/> - <input id="txt_amount_ak" name="txt_amount_ak" style="width:100px;" class="label_input number"/></td>
!-->    
    </tr>                    
    </table>
    
    <fieldset id="fieldStatus">
		<legend id="label_laporan">Status</legend>
        <table width="100%">
            <tr>
                <td width="33%"><label id="label_laporan"><input type="radio" name="rdStatus" value="belumlunas" checked="checked">Belum Lunas</label></td>
                <td width="34%"><label id="label_laporan"><input type="radio" name="rdStatus" value="lunas">Lunas</label></td>
                <td width="33%"><label id="label_laporan"><input type="radio" name="rdStatus" value="semua">Semua</label></td>
            </tr>
        </table> 
	</fieldset>  

	<fieldset id="field">
		<legend id="label_laporan">Jenis Laporan</legend>
    	<table width="100%" id="label_laporan">
            <tr>
                <td width="34%"><label id="label_laporan"><input type="radio" name="rdTampil" value="DetailPiutang" checked="checked"> Detail Piutang</label></td>
				<td width="33%"><label id="label_laporan"><input type="radio" name="rdTampil" value="RincianPelunasanPiutang"/> Rincian Pelunasan Piutang</label></td>
            </tr>
            <!--<tr>
                <td><label id="label_laporan"><input type="radio" name="rdTampil" value="DetailPiutangSalesman"> Detail of Receivable Berdasarkan Marketing</label></td>
                <td><label id="label_laporan"><input type="radio" name="rdTampil" value="UmurPiutangBySalesman" /> Detail of Aging of Receivables Berdasarkan Marketing</label></td>                                
                <td><label id="label_laporan"><input type="radio" name="rdTampil" value="RekapPelunasanPiutang" /> Rekapitulasi of Settlement Account Receivable</label></td>
            </tr>-->
            <tr>
				<td><label id="label_laporan"><input type="radio" name="rdTampil" value="KartuPiutang"> Kartu Piutang</label></td>            	
				<td><label id="label_laporan"><input type="radio" name="rdTampil" value="RekapPelunasanPiutang" /> Rekapitulasi Pelunasan Piutang</label></td>				
            </tr>            
            <tr>
                <td><label id="label_laporan"><input type="radio" name="rdTampil" value="RekapPiutang" /> Rekapitulasi Piutang</label></td>
                <td><label id="label_laporan"><input type="radio" name="rdTampil" value="Tagihan" /> Tagihan</label></td>
				<!--<td><label id="label_laporan"><input type="radio" name="rdTampil" value="KasBelumDigunakan" /> Kas/Bank/Giro Belum Digunakan Pelunasan</label></td> 				
				<td><label id="label_laporan"><input type="radio" name="rdTampil" value="RekapUmurPiutangByCustomer" /> Rekapitulasi Umur Piutang</label></td>              
				<td><label id="label_laporan"><input type="radio" name="rdTampil" value="RekapUmurPiutangBySalesman" /> Rekapitulasi - Aging of Receivables By Marketing</label></td>-->
            </tr>
        </table>
    </fieldset>
	
	<input type="hidden" name="excel" id="excel">
	<input type="hidden" name="file_name" id="file_name" value="LaporanPiutangDanPelunasanPiutang">
</form>
<script>
$(document).ready(function(){
	browse_data_lokasi('#txt_lokasi', 'lokasi&status=all','KODE');
	//browse_data_salesman('#txt_salesman_awal', 'salesman&status=all','NAMA');
	//browse_data_salesman('#txt_salesman_akhir', 'salesman&status=all','NAMA');
	//browse_data_rute('#txt_rute', 'rute&status=all','NAMA');
	//browse_data_subrute('#txt_subrute', 'subrute&status=all','NAMA');
	
	browse_data_instansi('#txt_instansi', 'instansi','KODE');
	browse_data_customer('#txt_customer_awal_list', 'customer&status=all','KODE');
	browse_data_customer('#txt_customer_awal', 'customer&status=all','KODE');
	browse_data_customer('#txt_customer_akhir', 'customer&status=all','KODE');
	//browse_data_kategori_customer('#txt_kategori_customer_awal', 'kategori_customer&status=all','NAMA');
	//browse_data_kategori_customer('#txt_kategori_customer_akhir', 'kategori_customer&status=all','NAMA');
		
	$('#txt_customer_akhir_list').combogrid({
		panelWidth:690,
		idField:'KODE',
		mode:'local',
		multiple: true,
		columns:[[
			{field:'KODE',title:'Kd. Customer',width:130, sortable:true},
			{field:'NAMA',title:'Nama Customer',width:240, sortable:true},
			{field:'ALAMAT',title:'Alamat',width:240, sortable:true},
			{field:'KOTA',title:'Kota',width:60, sortable:true},
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
	if (val=='DetailPiutang' || val=='DetailPiutangSalesman') {
		$('[name=rdStatus]').add($("#txt_amount_aw, #txt_amount_ak")).prop('disabled', false);
		
		$("#cbTgl").prop("disabled",false).prop('checked',true);
		$("#cbTempo").prop("disabled",false).prop('checked',false);
		$("#cbTglInput").prop("disabled",true).prop('checked',false);
		
		$('#txt_tgljatuhtempo, #txt_tglinput_aw, #txt_tglinput_ak').datebox('disable');
		$('#txt_tgl_aw, #txt_tgl_ak').datebox('enable');
	} else if (val=='RekapPiutang' || val=='KartuPiutang' || val=='UmurPiutangByCustomer' || val=='LaporanAktivitasPenagihanHarian' || val=='RekapUmurPiutangByCustomer' || val=='RekapUmurPiutangBySalesman') {
		$("#txt_amount_aw, #txt_amount_ak").prop('disabled', true);
		
		$("#cbTempo, #cbTglInput").prop("disabled",true).prop('checked',false);
		$("#cbTgl").prop("disabled",false).prop('checked',true);
		
		$('#txt_tglinput_aw, #txt_tglinput_ak, #txt_tgljatuhtempo').datebox('disable');
		$('#txt_tgl_aw, #txt_tgl_ak').datebox('enable');
	} else if (val=='RincianPelunasanPiutang' || val=='RekapPelunasanPiutang') {
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
	
$("#cbTgl").change(function(){
	var db = $(this).prop('checked')==true ? 'enable' : 'disable';

	$("#txt_tgl_aw, #txt_tgl_ak").datebox(db);
});

$("#cbTempo").change(function(){
	var db = $(this).prop('checked')==true ? 'enable' : 'disable';
	
	$("#txt_tgljatuhtempo").datebox(db);
});

$("#cbTglInput").change(function(){
	var db = $(this).prop('checked')==true ? 'enable' : 'disable';
	s
	$("#txt_tglinput_aw, #txt_tglinput_ak").datebox(db);
});

$("[name=rd_cust]").change(function(){
	var cg = $(this).val()==1 ? 'disable' : 'enable';
	var list = $(this).val()==2 ? 'disable' : 'enable';
	
	$('#txt_customer_awal, #txt_customer_akhir').combogrid(cg).combogrid('clear');
	$('#txt_customer_awal_list, #txt_customer_akhir_list').combogrid(list).combogrid('clear');
	
	$('#txt_customer_akhir_list').combogrid('grid').datagrid('loadData', []);
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

function browse_data_customer(id, table, sort) {
	$(id).combogrid({
		panelWidth:690,
		url: 'config/combogrid.php?table='+table+'&member=all',
		idField:'KODE',
		textField:'KODE',
		mode:'remote',
		sortName:'KODE',
		sortOrder:'asc',
		pageSize:20,
		view:bufferview,
		columns:[[
			{field:'KODE',title:'Kd. Customer',width:130, sortable:true},
			{field:'NAMA',title:'Nama Customer',width:240, sortable:true},
			{field:'ALAMAT',title:'Alamat',width:240, sortable:true},
			{field:'KOTA',title:'Kota',width:60, sortable:true},
		]],
		onSelect:function(index, data){
			if (id=='#txt_customer_awal_list') {
				var dg = $('#txt_customer_akhir_list').combogrid('grid');
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
				$('#txt_customer_akhir_list').combogrid('showPanel').combogrid('grid').datagrid('selectAll');
				$('#txt_customer_akhir_list').combogrid('textbox').val('');
				$('#txt_customer_awal_list').combogrid('clear');
			}
		}
	});
}

function browse_data_salesman(id, table, sort) {
	$(id).combogrid({
		panelWidth:380,
		url: 'config/combogrid.php?table='+table,
		idField:'NAMA',
		textField:'NAMA',
		mode:'remote',
		sortName:'NAMA',
		sortOrder:'asc',
		pageSize:20,
		view:bufferview,
		columns:[[
			{field:'KODE',title:'Kode',width:80, sortable:false},
			{field:'NAMA',title:'Nama',width:240, sortable:false},
		]]
	});
}
function browse_data_rute(id, table, sort) {
	$(id).combogrid({
		panelWidth:380,
		url: 'config/combogrid.php?table='+table,
		idField:'NAMA',
		textField:'NAMA',		
		multiple: true,
		mode:'remote',
		sortName:'NAMA',
		sortOrder:'asc',
		pageSize:20,
		view:bufferview,
		columns:[[
			{field:'ck',checkbox:true},
			{field:'KODE',title:'Kode',width:80, sortable:false},
			{field:'NAMA',title:'Nama',width:240, sortable:false},
		]]
	});
}
function browse_data_subrute(id, table, sort) {
	$(id).combogrid({
		panelWidth:320,
		url: 'config/combogrid.php?table='+table,
		idField:'NAMA',
		textField:'NAMA',		
		multiple: true,
		mode:'remote',
		sortName:'NAMA',
		sortOrder:'asc',
		pageSize:20,
		view:bufferview,
		columns:[[
			{field:'ck',checkbox:true},
			{field:'KODE',title:'Kode',width:80, sortable:false},
			{field:'NAMA',title:'Nama',width:220, sortable:false},
		]]
	});
}
function browse_data_kategori_customer(id, table, sort) {
	$(id).combogrid({
		panelWidth:260,
		url: 'config/combogrid.php?table='+table,
		idField:'KODE',
		textField:'NAMA',
		mode:'remote',
		sortName:'NAMA',
		sortOrder:'asc',
		pageSize:20,
		view:bufferview,
		columns:[[
			{field:'KODE',title:'Kode',width:80, sortable:false},
			{field:'NAMA',title:'Nama',width:150, sortable:false},
		]]
	});
}
</script>