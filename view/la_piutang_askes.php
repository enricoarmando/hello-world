<?php
session_start();
if (empty($_SESSION['user'])) header('Location: ../../../index.php');
?>
<form method='post' target='_blank' action='data/report/lap_akun_piutang_askes.php' id="form_input" style="width:600px">
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
                <td width="34%"><label id="label_laporan"><input type="radio" name="rdTampil" value="DetailPiutang" checked="checked"> Detail Piutang Askes</label></td>
				<td width="33%"><label id="label_laporan"><input type="radio" name="rdTampil" value="RincianPelunasanPiutang"/> Rincian Pelunasan Piutang</label></td>
            </tr>
            <tr>
				<td><label id="label_laporan"><input type="radio" name="rdTampil" value="KartuPiutang"> Kartu Piutang Askes</label></td>            	
				<td><label id="label_laporan"><input type="radio" name="rdTampil" value="RekapPelunasanPiutang"/> Rekapitulasi Pelunasan Piutang Askes</label></td>				
            </tr>            
            <tr>
                <td><label id="label_laporan"><input type="radio" name="rdTampil" value="RekapPiutang"/> Rekapitulasi Piutang Askes</label></td>
                <td><label id="label_laporan"></label></td>
            </tr>
        </table>
    </fieldset>
	
	<input type="hidden" name="excel" id="excel">
	<input type="hidden" name="file_name" id="file_name" value="LaporanPiutangKartuKredit">
</form>
<script>
$(document).ready(function(){
	browse_data_lokasi('#txt_lokasi', 'lokasi&status=all','KODE');

	$('#txt_tgl_aw, #txt_tgl_ak').datebox('enable');
});

$('[name=rdTampil]').change(function(){
	var val = $(this).val();
	if (val=='DetailPiutang') {		
		$('#txt_tgl_aw, #txt_tgl_ak').datebox('enable');
	} else if (val=='RekapPiutang' || val=='KartuPiutang') {		
		$('#txt_tgl_aw, #txt_tgl_ak').datebox('enable');
	} else if (val=='RincianPelunasanPiutang' || val=='RekapPelunasanPiutang') {
		$('#txt_tgl_aw, #txt_tgl_ak').datebox('enable');
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