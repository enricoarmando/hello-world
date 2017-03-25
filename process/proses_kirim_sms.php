<?php
session_start();
ob_start("ob_gzhandler");
date_default_timezone_set("Asia/Jakarta");

if (empty($_SESSION['user'])) die(json_encode(array('errorMsg' => 'Expired Session <br> Please Relogin')));

include "../../config/koneksi.php";
include "../../config/function.php";

$detail = json_decode($_POST['datacustomer']);
$format_sms = $_POST['sms'];

if (count($detail)<1) die(json_encode(array('errorMsg' => 'Customer Tidak Boleh Kosong')));
if (strchr($format_sms,"[nama]")=='') die(json_encode(array('errorMsg' => 'Format SMS harus ada kata \'[nama]\'')));
if ($format_sms=='[nama]') die(json_encode(array('errorMsg' => 'Invalid format SMS, isi text SMS')));

$mysqli = new mysqli("localhost", "root", "", "sms");

$stmt = $mysqli->prepare("insert into outbox (DestinationNumber, TextDecoded, CreatorID) values (?,?,?);");

foreach ($detail as $item) {
	$sms = str_replace('[nama]', substr($item->NAMACUSTOMER, 0, 20), $format_sms);
	$hp = str_replace(' ', '', trim($item->HP));

	// bind parameters for markers
	$stmt->bind_param("sss", $hp, $sms, $_SESSION['user']);

	// execute query
	if (strlen($hp) > 5)
		$stmt->execute();
}

$mysqli->close();

echo json_encode(array('success' => true));
?>