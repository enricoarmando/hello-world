<?php
session_start();
if (empty($_SESSION['user'])) header('Location: ../../../index.php');
?>
<form method='post' target='_blank' action='data/report/lap_akun_piutang_cabang.php' id="form_input" style="width:600px">
    <div style="padding:5px;border:1px solid #ddd;">
        <a id="btn_print" href="#" class="easyui-linkbutton" data-options="iconCls:'icon-print', plain:true">Tampilkan Data</a>
		<a id="btn_export_excel" href="#" class="easyui-linkbutton" data-options="iconCls:'icon-excel', plain:true">Export</a>
    </div>

    <table style="border-bottom:1px #000">
        <tr>
            <td align="right" id="label_laporan">Lokasi :</td>
            <td><input id="txt_lokasi" name="txt_lokasi[]" style="width:250px"/></td>
        </tr>                    
        <tr>
            <td align="right" id="label_laporan"><label id="label_laporan">Tgl. Transaksi :</label></td>
            <td id="label_laporan"><input id="txt_tgl_aw" name="txt_tgl_aw" class="date"/> - <input id="txt_tgl_ak" name="txt_tgl_ak" class="date"/></td>
        </tr>        
    </tr>                    
    </table>
    
    <fieldset id="fieldStatus">
		<legend id="label_laporan">Status</legend>
        <table width="100%">
            <tr>
                <td width="33%"><label id="label_laporan"><input type="radio" name="rdStatus" value="semua" checked="checked">Semua</label></td>
                <td width="33%"><label id="label_laporan"><input type="radio" name="rdStatus" value="belumlunas">Belum Lunas</label></td>
                <td width="34%"><label id="label_laporan"><input type="radio" name="rdStatus" value="lunas">Lunas</label></td>
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

	$('[name=rdStatus]').add($("#txt_amount_aw, #txt_amount_ak")).prop('disabled', false);

	$('#txt_tglinput_aw, #txt_tgljatuhtempo, #txt_tglinput_ak, #txt_customer_awal, #txt_customer_akhir').datebox('disable');
	$('#txt_tgl_aw, #txt_tgl_ak').datebox('enable');
});

$('[name=rdTampil]').change(function(){
	var val = $(this).val();
	if (val=='DetailPiutang' || val=='DetailPiutangSalesman') {
		$('[name=rdStatus]').add($("#txt_amount_aw, #txt_amount_ak")).prop('disabled', false);
		
		$('#txt_tgl_aw, #txt_tgl_ak').datebox('enable');
	} else if (val=='RekapPiutang' || val=='KartuPiutang' || val=='UmurPiutangByCustomer' || val=='LaporanAktivitasPenagihanHarian' || val=='RekapUmurPiutangByCustomer' || val=='RekapUmurPiutangBySalesman') {
		$('[name=rdStatus]').add($("#txt_amount_aw, #txt_amount_ak")).prop('disabled', true);
		
		$('#txt_tgl_aw, #txt_tgl_ak').datebox('enable');
	} else if (val=='RincianPelunasanPiutang' || val=='RekapPelunasanPiutang') {
		$('[name=rdStatus]').add($("#txt_amount_aw, #txt_amount_ak")).prop('disabled', true);
		
		$('#txt_tgljatuhtempo').datebox('disable');
	} else if (val=='KasBelumDigunakan') {
		$('[name=rdStatus]').add($("#txt_amount_aw, #txt_amount_ak")).prop('disabled', true);		
		
		$('#txt_tgl_aw, #txt_tgl_ak').datebox('disable');
	}
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