<?php
session_start();
if (empty($_SESSION[user])) header('Location: ../../../index.php');
?>
<style>
.kode{
	width:80px;
}
.nama{
	width:300px;
}
.biru{
	color:#06F;
}
.merah{
	color:#F03;
}
</style>
<div style="padding:5px;border:1px solid #ddd; height:25px">
    <a id="btn_simpan" href="#" class="easyui-linkbutton" data-options="iconCls:'icon-save', plain:true">Simpan</a>
    <a id="btn_refresh" href="#" class="easyui-linkbutton" data-options="iconCls:'icon-reload', plain:true">Refresh</a>
</div>

<div title="Use Of Material" hidden>
	<fieldset style="width:650px">
		<legend id="label_form">Use Of Material</legend>
		<table>
			<tr>
				<td id="label_form" class="biru">Cost Of Goods Available for Penjualan (Debet)</td>
				<td><input id="txt_PEMAKAIAN" name="txt_PEMAKAIAN" class="kode" /> <input id="txt_nama_PEMAKAIAN" name="txt_nama_PEMAKAIAN" class="nama label_input" readonly="readonly" /></td>
			</tr>
			<tr>
				<td id="label_form" class="merah" colspan="2">Stock Account Entry on Data Master Items (Kredit)</td>
			</tr>
		</table>
	</fieldset>
</div>

<div style="padding:5px" class="easyui-tabs" id="menu">
	<div title="Pembelian">
        <fieldset style="width:650px">
            <legend id="label_form">Pembelian</legend>
            <table>
				<tr>
                    <td id="label_form" class="biru" colspan="2">Akun Persediaan Barang Mengikuti Master Barang</td>
                </tr>
				<tr>
                    <td id="label_form" class="biru">Selisih</td>
                    <td><input id="txt_BELI-SELISIH" name="txt_BELI-SELISIH" class="kode" /> <input id="txt_nama_BELI-SELISIH" name="txt_nama_BELI-SELISIH" class="nama label_input" readonly="readonly" /></td>
                </tr>
				<tr>
                    <td id="label_form" class="biru">Faktur</td>
                    <td><input id="txt_BELI-FAKTUR" name="txt_BELI-FAKTUR" class="kode" /> <input id="txt_nama_BELI-FAKTUR" name="txt_nama_BELI-FAKTUR" class="nama label_input" readonly="readonly" /></td>
                </tr>
                <tr>
                    <td id="label_form" class="merah">Hutang Dagang</td>
                    <td><input id="txt_BELI-HUTANG" name="txt_BELI-HUTANG" class="kode" /> <input id="txt_nama_BELI-HUTANG" name="txt_nama_BELI-HUTANG" class="nama label_input" readonly="readonly" /></td>
                </tr>				
            </table>
        </fieldset>

        <fieldset style="width:650px">
            <legend id="label_form">Retur Pembelian</legend>
            <table>
                <tr>
                    <td id="label_form" class="biru">Hutang Dagang</td>
                    <td><input id="txt_RBELI-HUTANG" name="txt_RBELI-HUTANG" class="kode" /> <input id="txt_nama_RBELI-HUTANG" name="txt_nama_RBELI-HUTANG" class="nama label_input" readonly="readonly" /></td>
                </tr>
				<tr>
                    <td id="label_form" class="merah" colspan="2">Akun Persediaan Barang Mengikuti Master Barang</td>
                </tr>
            </table>
        </fieldset>
    </div>

    <div title="Penjualan">
        <fieldset style="width:650px">
            <legend id="label_form">Penjualan</legend>
            <table>
				<tr>
                    <td id="label_form" class="biru">Piutang Dagang</td>
                    <td><input id="txt_JUAL-PIUTANG" name="txt_JUAL-PIUTANG" class="kode" /> <input id="txt_nama_JUAL-PIUTANG" name="txt_nama_JUAL-PIUTANG" class="nama label_input" readonly="readonly" /></td>
                </tr>
				<tr>
                    <td id="label_form" class="biru">Diskon</td>
                    <td><input id="txt_JUAL-DISKON" name="txt_JUAL-DISKON" class="kode" /> <input id="txt_nama_JUAL-DISKON" name="txt_nama_JUAL-DISKON" class="nama label_input" readonly="readonly" /></td>
                </tr>
				<tr>
                    <td id="label_form" class="biru">HPP</td>
                    <td><input id="txt_JUAL-HPP" name="txt_JUAL-HPP" class="kode" /> <input id="txt_nama_JUAL-HPP" name="txt_nama_JUAL-HPP" class="nama label_input" readonly="readonly" /></td>
                </tr>
                <tr>
                    <td id="label_form" class="merah">Penjualan</td>
                    <td><input id="txt_JUAL" name="txt_JUAL" class="kode" /> <input id="txt_nama_JUAL" name="txt_nama_JUAL" class="nama label_input" readonly="readonly" /></td>
                </tr>
                <tr>
                    <td id="label_form" class="merah">Kartu Kredit</td>
                    <td><input id="txt_JUAL-KARTU KREDIT" name="txt_JUAL-KARTU KREDIT" class="kode" /> <input id="txt_nama_JUAL-KARTU KREDIT" name="txt_nama_JUAL-KARTU KREDIT" class="nama label_input" readonly="readonly" /></td>
                </tr>
				<tr>
                    <td id="label_form" class="merah" colspan="2">Akun Persediaan Barang Mengikuti Master Barang</td>
                </tr>
            </table>
        </fieldset>

        <fieldset style="width:650px">
            <legend id="label_form">Retur Penjualan</legend>
            <table>
            	<tr>
                    <td id="label_form" class="biru">Retur Penjualan (Debet)</td>
                    <td><input id="txt_RJUAL" name="txt_RJUAL" class="kode" /> <input id="txt_nama_RJUAL" name="txt_nama_RJUAL" class="nama label_input" readonly="readonly" /></td>
                </tr>
				<tr>
                    <td id="label_form" class="biru">PPN (Debet)</td>
                    <td><input id="txt_RJUAL-PPN" name="txt_RJUAL-PPN" class="kode" /> <input id="txt_nama_RJUAL-PPN" name="txt_nama_RJUAL-PPN" class="nama label_input" readonly="readonly" /></td>
                </tr>
				<tr>
                    <td id="label_form" class="biru" colspan="2">Akun Persediaan Barang Mengikuti Master Barang (Debet)</td>
                </tr>
                <tr>
                    <td style="display:none" id="label_form" class="merah">Diskon (Kredit)</td>
                    <td style="display:none"><input id="txt_RJUAL-DISKON" name="txt_RJUAL-DISKON" class="kode" /> <input id="txt_nama_RJUAL-DISKON" name="txt_nama_RJUAL-DISKON" class="nama label_input" readonly="readonly" /></td>
                </tr>
                <tr>
                    <td id="label_form" class="merah">Piutang Dagang (Kredit)</td>
                    <td><input id="txt_RJUAL-PIUTANG" name="txt_RJUAL-PIUTANG" class="kode" /> <input id="txt_nama_RJUAL-PIUTANG" name="txt_nama_RJUAL-PIUTANG" class="nama label_input" readonly="readonly" /></td>
                </tr>
                <tr>
                    <td id="label_form" class="merah">HPP (Kredit)</td>
                    <td><input id="txt_RJUAL-HPP" name="txt_RJUAL-HPP" class="kode" /> <input id="txt_nama_RJUAL-HPP" name="txt_nama_RJUAL-HPP" class="nama label_input" readonly="readonly" /></td>
                </tr>

            </table>
        </fieldset>
    </div>

	<div title="Penyesuaian">
        <fieldset style="width:650px">
            <legend id="label_form">Penyesuaian</legend>
            <table>
                <tr>
                    <td id="label_form" class="biru">HPP (Debet)</td>
                    <td><input id="txt_PENYESUAIAN" name="txt_PENYESUAIAN" class="kode" /> <input id="txt_nama_PENYESUAIAN" name="txt_nama_PENYESUAIAN" class="nama label_input" readonly="readonly" /></td>
                </tr>
				<tr>
                    <td id="label_form" class="merah" colspan="2">Akun Persediaan Barang Mengikuti Master Barang (Kredit)</td>
                </tr>
            </table>
        </fieldset>
	</div>

    <div title="Piutang Dagang">
        <fieldset style="width:650px">
            <legend id="label_form">Ayat Silang Piutang</legend>
            <table>
				<tr>
                    <td id="label_form">Ayat Silang Piutang</td>
                    <td><input id="txt_PELUNASAN-PIUTANG" name="txt_PELUNASAN-PIUTANG" class="kode" /> <input id="txt_nama_PELUNASAN-PIUTANG" name="txt_nama_PELUNASAN-PIUTANG" class="nama label_input" readonly="readonly" /></td>
                </tr>
            </table>
        </fieldset>
    </div>

    <div title="Hutang Dagang">
        <fieldset style="width:650px">
            <legend id="label_form">Ayat Silang Hutang</legend>
            <table>
	            <tr>
                    <td id="label_form">Ayat Silang Hutang</td>
                    <td><input id="txt_PELUNASAN-HUTANG" name="txt_PELUNASAN-HUTANG" class="kode" /> <input id="txt_nama_PELUNASAN-HUTANG" name="txt_nama_PELUNASAN-HUTANG" class="nama label_input" readonly="readonly" /></td>
                </tr>
            </table>
        </fieldset>
	</div>

	<div title="Giro">
    	<fieldset style="width:650px">
            <legend id="label_form">Receivable Cheque</legend>
            <table>
                <tr>
                    <td id="label_form" class="biru">Piutang Giro Mundur</td>
                    <td><input id="txt_GIRO-MASUK" name="txt_GIRO-MASUK" class="kode" /> <input id="txt_nama_GIRO-MASUK" name="txt_nama_GIRO-MASUK" class="nama label_input" readonly="readonly" /></td>
                </tr>
                <tr>
                    <td colspan="2" class="merah" id="label_form">Akun Kredit Lawan Menggunakan Akun Ayat Silang Piutang</td>
                </tr>
            </table>
        </fieldset>
        <fieldset style="width:650px">
            <legend id="label_form">Pencairan Giro Masuk</legend>
            <table>
                <tr>
	                <td colspan="2" class="biru" id="label_form">Akun Debet Lawan Menggunakan Kode Perkiraan Kas/Bank</td>
                </tr>
				<tr>
                    <td id="label_form" class="merah">Piutang Giro Mundur</td>
                    <td><input id="txt_CAIR-GIRO-MASUK" name="txt_CAIR-GIRO-MASUK" class="kode" /> <input id="txt_nama_CAIR-GIRO-MASUK" name="txt_nama_CAIR-GIRO-MASUK" class="nama label_input" readonly="readonly" /></td>
                </tr>
            </table>
        </fieldset>
        <fieldset style="width:650px">
            <legend id="label_form">Payable Cheque</legend>
            <table>
                <tr>
                    <td id="label_form" class="biru">Hutang Dagang</td>
                    <td><input id="txt_GIRO-KELUAR-DEBET" name="txt_GIRO-KELUAR-DEBET" class="kode" /> <input id="txt_nama_GIRO-KELUAR-DEBET" name="txt_nama_GIRO-KELUAR-DEBET" class="nama label_input" readonly="readonly" /></td>
                </tr>
                <tr>
                    <td id="label_form" class="merah">Hutang Giro Mundur</td>
                    <td><input id="txt_GIRO-KELUAR-KREDIT" name="txt_GIRO-KELUAR-KREDIT" class="kode" /> <input id="txt_nama_GIRO-KELUAR-KREDIT" name="txt_nama_GIRO-KELUAR-KREDIT" class="nama label_input" readonly="readonly" /></td>
                </tr>
            </table>
        </fieldset>

        <fieldset style="width:650px">
            <legend id="label_form">Pencairan Giro Keluar</legend>
            <table>
                <tr>
                    <td id="label_form" class="biru">Hutang Giro Mundur</td>
                    <td><input id="txt_CAIR-GIRO-KELUAR" name="txt_CAIR-GIRO-KELUAR" class="kode" /> <input id="txt_nama_CAIR-GIRO-KELUAR" name="txt_nama_CAIR-GIRO-KELUAR" class="nama label_input" readonly="readonly" /></td>
                </tr>
                <tr>
                    <td colspan="2" class="merah" id="label_form">Kredit Account Using Bank Account</td>
                </tr>
            </table>
        </fieldset>
        <fieldset style="width:650px">
            <legend id="label_form">Entry Giro Reject</legend>
            <table>
                <tr>
                    <td id="label_form" class="biru">Piutang Dagang</td>
                    <td><input id="txt_TOLAKAN-GIRO-MASUK-DEBET" name="txt_TOLAKAN-GIRO-MASUK-DEBET" class="kode" /> <input id="txt_nama_TOLAKAN-GIRO-MASUK-DEBET" name="txt_nama_TOLAKAN-GIRO-MASUK-DEBET" class="nama label_input" readonly="readonly" /></td>
                </tr>
                <tr>
                    <td id="label_form" class="merah">Piutang Giro Mundur</td>
                    <td><input id="txt_TOLAKAN-GIRO-MASUK-KREDIT" name="txt_TOLAKAN-GIRO-MASUK-KREDIT" class="kode" /> <input id="txt_nama_TOLAKAN-GIRO-MASUK-KREDIT" name="txt_nama_TOLAKAN-GIRO-MASUK-KREDIT" class="nama label_input" readonly="readonly" /></td>
                </tr>
            </table>
        </fieldset>
        <fieldset style="width:650px">
            <legend id="label_form">Tolakan Giro Keluar</legend>
            <table>
                <tr>
                    <td id="label_form" class="biru">Hutang Giro Mundur</td>
                    <td><input id="txt_TOLAKAN-GIRO-KELUAR-DEBET" name="txt_TOLAKAN-GIRO-KELUAR-DEBET" class="kode" /> <input id="txt_nama_TOLAKAN-GIRO-KELUAR-DEBET" name="txt_nama_TOLAKAN-GIRO-KELUAR-DEBET" class="nama label_input" readonly="readonly" /></td>
                </tr>
                <tr>
                    <td id="label_form" class="merah">Hutang Dagang</td>
                    <td><input id="txt_TOLAKAN-GIRO-KELUAR-KREDIT" name="txt_TOLAKAN-GIRO-KELUAR-KREDIT" class="kode" /> <input id="txt_nama_TOLAKAN-GIRO-KELUAR-KREDIT" name="txt_nama_TOLAKAN-GIRO-KELUAR-KREDIT" class="nama label_input" readonly="readonly" /></td>
                </tr>
            </table>
        </fieldset>
    </div>
	<div title="Laba Rugi">
    	<fieldset style="width:650px">
            <legend id="label_form">Laba Rugi</legend>
            <table>
                <tr>
                    <td id="label_form" class="biru">Laba Rugi</td>
                    <td><input id="txt_LABA-RUGI" name="txt_LABA-RUGI" class="kode" /> <input id="txt_nama_LABA-RUGI" name="txt_nama_LABA-RUGI" class="nama label_input" readonly="readonly" /></td>
                </tr>
            </table>
        </fieldset>
    </div>
</div>

<script>
$(document).ready(function(){
	tampilkan_data();
});

$("#btn_simpan").click(function(){
	simpan();
});
$("#btn_refresh").click(function(){
	tampilkan_data();
});

function simpan (){
	$.ajax({
		type: 'POST',
		url: "data/process/proses_master.php",
		data: "&act=insert&table=jurnal_link&"+$('#menu :input').serialize(),
		cache: false,
		success: function(msg){
			var json = JSON.parse(msg);
			if (json.status == 'sukses') {
				$.messager.alert('Info','Simpan Data Sukses!','info');
			} else {
				$.messager.alert('Error', json.status, 'error');
			}
		}
	});
}

function tampilkan_data () {
	$.ajax({
		type: 'POST',
		dataType:'json',
		url: "data/process/proses_master.php",
		data: "act=view&table=jurnal_link",
		cache: false,
		beforeSend : function (){
			$.messager.progress();
		},
		success: function(msg){
			$.messager.progress('close');

			var json = msg;
			if (json.status == 'sukses') {
				var data = json.data;
				for (var i=0; i<data.length; i++) {
					browse_kode_perkiraan(data[i].JENIS);

					$('#txt_'+data[i].JENIS).combogrid('setValue', data[i].KODEPERKIRAAN);
					$('#txt_nama_'+data[i].JENIS).textbox('setValue', data[i].NAMAPERKIRAAN);
				}
			} else {
				$.messager.alert('Error', json.status, 'error');
			}
		}
	});
}
function browse_kode_perkiraan (id) {
	$('#txt_'+id).combogrid({
		panelWidth: 380,
		mode: 'remote',
		idField: 'KODE',
		textField: 'KODE',
		url: 'config/combogrid.php?table=kode_perkiraan&jenis=detail',
		columns: [[
			{field:'KODE',title:'Account',width:80, sortable:true},
			{field:'NAMA',title:'Description',width:270, sortable:true},
		]],
		onSelect: function(index, data){
			$('#txt_nama_'+id).textbox('setValue', data.NAMA);
		},
		onChange:function(index, data){
			if (data.KODE=='') $('#txt_nama_'+id).textbox('clear');
		}
	});
}

</script>