<?php
session_start();
if (empty($_SESSION['user'])) die('restart browser');
?>
<form method='post' target='_blank' action='data/report/lap_transaksi_ubah_juru_bayar.php' id="form_input" style="width:720px">
    <div style="padding:5px;border:1px solid #ddd;">
        <a id="btn_print" href="#" class="easyui-linkbutton" data-options="iconCls:'icon-print', plain:true">Tampilkan Data</a>
		<a id="btn_export_excel" href="#" class="easyui-linkbutton" data-options="iconCls:'icon-excel', plain:true">Export</a>
    </div>
    
	<table style="border-bottom:1px #000">
        <tr>
            <td align="right" id="label_laporan">Tgl. Transaksi :</td>
            <td id="label_laporan"><input id="txt_tgl_aw" name="txt_tgl_aw" class="date"/> - <input id="txt_tgl_ak" name="txt_tgl_ak" class="date"/></td>
        </tr>
    </table>
	<fieldset id="field">
	<legend id="label_laporan">Jenis Laporan</legend>
        <table width="100%" border="0">
           	<tr>
               	<td><label id="label_laporan"><input type="radio" name="rdTampil" value="Register" checked="checked"> Register</label></td>
        	</tr>
		</table>
    </fieldset>
	
	<input type="hidden" name="excel" id="excel">
	<input type="hidden" name="file_name" id="file_name" value="LaporanUbahJuruBayar">
</form>
<script>
$(document).ready(function(){
	
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


</script>