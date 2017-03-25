<?php
session_start();
include "koneksi.php";
include "function.php";

$table = $_GET['table'];
$q     = isset($_POST['q']) ? strtoupper($_POST['q']) : '';

//$db = new DB;

switch ($table) {
	case 'satuan_barang' :
		$kode_barang = $_GET['kode_barang'];
		$sql = "select satuan, konversi, jenis from (
					select satuan, 1 as konversi, 1 as jenis from mbarang where kodebarang='$kode_barang'
					union all
					select satuan2, konversi1 as konversi, 2 as jenis from mbarang where kodebarang='$kode_barang'
					union all
					select satuan3, konversi2 as konversi, 3 as jenis from mbarang where kodebarang='$kode_barang'
				)";
		$query = $db->query($sql);
		$rows  = array();
		while ($rs = $db->fetch($query)){
			if ($rs->SATUAN<>'')
				$rows[] = $rs;
		}
		echo json_encode($rows);
	break;
}
?>