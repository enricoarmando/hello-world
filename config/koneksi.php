<?php
require_once("debug.php");
require_once("db_class.php");

//buka config.ini

$path_config = 'D:\xampp\htdocs\CONFIG\RESTO.INI';
$file  = @fopen($path_config, "r") or exit(json_encode(array('errorMsg' => 'Cek File Config di '.$path_config.' !')));

while (!feof($file)) {  
	$kalimat = explode("=", strtoupper(fgets($file)));
	
	if ($kalimat[0]=='DATABASEPATH') {
		$url = ':'.trim($kalimat[1]);
		break;
	}
}
//tutup file
fclose($file); 

//$url = '192.168.1.12:e:/xampp/htdocs/DATABASE/INDRAOPTIK.FDB';
// koneksi firebird
$db = new DB();
$db->connect($url, 'SYSDBA', 'masterkey');
?>