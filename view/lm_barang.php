<?php
if (empty($_SESSION['user'])) die(json_encode(array('errorMsg' => 'Expired Session <br> Please Relogin')));
?>
<form method='post' target='_blank' action='data/report/lap_m_barang.php' id="form_input" style="width:500px">
    <div style="padding:5px;border:1px solid #ddd;">
        <a id="btn_print" href="#" class="easyui-linkbutton" data-options="iconCls:'icon-print', plain:true">Tampilkan Data</a>
		<a id="btn_export_excel" href="#" class="easyui-linkbutton" data-options="iconCls:'icon-excel', plain:true">Export</a>
    </div>

    <table style="border-bottom:1px #000">
		<tr>
			<td></td>
			<td align="left" id="label_laporan"><input type="checkbox" id="LENSANONSTOK" name="LENSANONSTOK" value="1"> Lensa Non Stok</td>
		</tr>
		<tr>
			<td align="right" id="label_laporan">Jenis</td>
			<td><input name="KODEJENIS" id="KODEJENIS" style="width:150px"></td>
		</tr>
		<tr hidden>
			<td align="right" id="label_laporan">Items Category :</td>
			<td><input id="txt_kode_kategori_awal" name="txt_kode_kategori_awal" style="width:200px"> - <input id="txt_kode_kategori_akhir" name="txt_kode_kategori_akhir" style="width:200px"></td>
		</tr>
    	<tr>
			<td align="right" id="label_laporan">Barang :</td>
			<td id="label_laporan"><input id="txt_namabarang" name="txt_namabarang" class="label_input" style="width:250px" prompt="nama barang">
			                       <input id="txt_kodebarang" name="txt_kodebarang" class="label_input" style="width:100px" prompt="kode barang">
			</td>
		</tr>
    </table>
	<fieldset id="field">
		<legend id="label_laporan">Jenis Laporan</legend>
        <table width="100%" border="0">
           	<tr>
				<td><label id="label_laporan"><input type="radio" name="rdTampil" value="HargaBarang" checked="checked"> Harga Barang</label></td>
               	<td><label id="label_laporan"><input type="radio" name="rdTampil" value="Detail"> Detail</label></td>
        	</tr>
		</table>
    </fieldset>
	
	<input type="hidden" name="excel" id="excel">
	<input type="hidden" name="file_name" id="file_name" value="ItemReport">
</form>
<script>
$(document).ready(function(){
	browse_data_barang('#txt_kode_awal', 'barang&status=all','NAMA');
	browse_data_barang('#txt_kode_akhir', 'barang&status=all','NAMA');
	browse_data('#txt_kode_kategori_awal', 'kategori_barang', 'kodekategoribarang');
	browse_data('#txt_kode_kategori_akhir', 'kategori_barang', 'kodekategoribarang');

	$('[name=KODEJENIS]').combogrid({
		required:false,
		panelWidth:170,
		mode:'local',
		idField:'NAMA',
		textField:'NAMA',
		sortName:'KODE',
		sortOrder:'asc',
		url:'config/combogrid.php?table=jenis_barang',
		columns:[[
			{field:'NAMA',title:'Jenis',width:150}
		]],
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

function browse_data(id, table, sort) {
	$(id).combogrid({
		panelWidth:360,
		url: 'config/combogrid.php?table='+table+'&jenis=all',
		idField:'NAMA',
		textField:'NAMA',
		mode:'remote',
		sortName:sort,
		sortOrder:'asc',
		view:bufferview,
		rowStyler: function(index,row){  
			if (row.STATUS == 0){  
				return 'background-color:#A8AEA6';  
			}  
		},
		columns:[[
			{field:'KODE',title:'ID',width:80, sortable:false},
			{field:'NAMA',title:'Description',width:250, sortable:false},
		]]
	});
}

function browse_data_barang(id, table, sort) {
	$(id).combogrid({
		panelWidth:820,
		url: 'config/combogrid.php?table='+table,
		idField:'KODE',
		textField:'KODE',
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
			{field:'KODE',title:'Item ID',width:80, sortable:false},
			{field:'NAMA',title:'Item Description',width:240, sortable:false},
			{field:'SATUAN',title:'Unit 1',width:100, sortable:false},
			//{field:'KONVERSI1',title:'Conv. 1',width:100, sortable:false},
			//{field:'SATUAN2',title:'Unit 2',width:100, sortable:false},
			//{field:'KONVERSI2',title:'Conv. 2',width:100, sortable:false},
			//{field:'SATUAN3',title:'Unit 3',width:100, sortable:false}
		]],
		onSelect:function(index, data){
			if (id=='#txt_barang_awal_list') {
				var dg = $('#txt_barang_akhir_list').combogrid('grid');
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
				$('#txt_barang_akhir_list').combogrid('showPanel').combogrid('grid').datagrid('selectAll');
				$('#txt_barang_akhir_list').combogrid('textbox').val('');
				$('#txt_barang_awal_list').combogrid('clear');
			}
		}
	});
}
</script>