<?php
session_start();
include "koneksi.php";
include "function.php";

$table = $_GET['table'];

//$db = new DB;

switch ($table) {
	case 'selisih_jatuh_tempo' :
		$tgl     = $_POST['tgl'];
		$selisih = $_POST['selisih'];
		echo selisih_jatuh_tempo($selisih, $tgl);
	break;
}
?>