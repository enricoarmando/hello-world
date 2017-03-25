<?php
if (empty($_SESSION['user'])) header('Location: ../../../index.php');
?>
<div style="padding:5px;border:1px solid #ddd; height:25px;">
    <a id="btn_simpan" href="#" class="easyui-linkbutton" data-options="iconCls:'icon-save', plain:true">Simpan</a>
</div>

<div id="form_input" style="width:auto;height:auto;padding:5px;">
    <table>
        <tr>
            <td id="label_form" width="150">Nama Perusahaan</td>
            <td><input type="text" id="NAMA" name="NAMA" class="label_input font-normal" style="width:200px"></td>
        </tr>
        <tr>
            <td id="label_form">Alamat</td>
            <td><input type="text" id="ALAMAT" name="ALAMAT" class="label_input font-normal" style="width:500px"></td>
        </tr>
        <tr>
            <td id="label_form">Kota</td>
            <td><input type="text" id="KOTA" name="KOTA" class="label_input font-normal" style="width:200px"></td>
        </tr>
        <tr>
            <td id="label_form">Kode Pos</td>
            <td><input type="text" id="KODEPOS" name="KODEPOS" class="label_input font-normal" style="width:200px"></td>
        </tr>
        <tr>
            <td id="label_form">Telp</td>
            <td><input type="text" id="TELP" name="TELP" class="label_input font-normal" style="width:200px"></td>
        </tr>
        <tr>
            <td id="label_form">Fax</td>
            <td><input type="text" id="FAX" name="FAX" class="label_input font-normal" style="width:200px"></td>
        </tr>
        <tr hidden>
            <td id="label_form">PPN</td>
            <td><input type="text" id="PPN" name="PPN" class="number" min="0" max="100" suffix="%" style="width:60px"></td>
        </tr>
        <tr hidden>
            <td id="label_form">NPWP</td>
            <td><input type="text" id="NPWP" name="NPWP" class="number" min="0" max="100" suffix="%" style="width:60px"></td>
        </tr>
		<tr hidden>
            <td id="label_form">Nama Tanda Tangan 1</td>
            <td><input type="text" id="NAMATANDATANGAN" name="NAMATANDATANGAN" class="label_input" style="width:200px"></td>
        </tr>		
        <tr>
            <td id="label_form">Diskon Member</td>
            <td id="label_form"><input type="text" id="DISKONMEMBER" name="DISKONMEMBER" class="number" style="width:50px" suffix="%"></td>
        </tr>
        <tr>
            <td id="label_form">Diskon Ultah</td>
            <td id="label_form"><input type="text" id="DISKONULTAH" name="DISKONULTAH" class="number" style="width:50px" suffix="%"></td>
        </tr>
        <tr>
            <td id="label_form">Lokasi Backup Data</td>
            <td><input type="text" id="LOKASIBACKUP" name="LOKASIBACKUP" class="label_input" style="width:600px"></td>
        </tr>
        <tr>
            <td id="label_form">Backup Otomatis</td>
            <td>
				<label id="label_form"><input type="radio" value="1" name="BACKUPOTOMATIS" checked="checked"> Ya</label> 
				<label id="label_form"><input type="radio" value="0" name="BACKUPOTOMATIS"> Tidak</label>
			</td>
        </tr>
        <tr>
            <td id="label_form">Interval Backup</td>
            <td id="label_form"><input type="text" id="INTERVALBACKUP" name="INTERVALBACKUP" class="number" style="width:50px"> Menit</td>
        </tr>
		<tr>
            <td id="label_form">Rate Indra Optik</td>
            <td id="label_form"><input type="text" id="RATEIO" name="RATEIO" class="number" style="width:50px"></td>
        </tr>
		<tr>
            <td id="label_form">Rate Super Eye</td>
            <td id="label_form"><input type="text" id="RATESE" name="RATESE" class="number" style="width:50px"></td>
        </tr>
    </table>
</div>
<script>
$(document).ready(function(){
	$.ajax({
		type: 'POST',
		url: "data/process/proses_master.php",
		data: "act=view&table=settinglain", 
		cache: false,
		dataType: 'json',
		success: function(msg){
			$('#form_input').form('load', msg.data);
		}
	});
});

$("#btn_simpan").click(function(){
	simpan();
});

shortcut.add('F8',function() {
	simpan();
});

function simpan () {
	var isValid = $('#form_input').form('validate');
	if (isValid) {		
		$.ajax({
			type: 'POST',
			url: "data/process/proses_master.php", 
			data: "table=settinglain&act=simpan&"+$('#form_input :input').serialize(),
			dataType: 'json',
			beforeSend : function (){
				$.messager.progress();
			},
			success: function(msg){
				$.messager.progress('close');
				if (msg.success) {
					$.messager.show({
						title:'Info',
						msg:'Transaksi Sukses',
						showType:'show'
					});
				} else {
					$.messager.alert('Error', msg.errorMsg, 'error');
				}
			},
		});
	}
}
</script>