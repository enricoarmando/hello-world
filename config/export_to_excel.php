<?php
$name_file = $_POST['file_name'];

header("Pragma: public");
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Content-Type: application/force-download");
header("Content-Type: application/octet-stream");
header("Content-Type: application/download");
header("Content-Disposition: attachment;filename=$name_file.xls");
header("Content-Transfer-Encoding: binary ");

if (isset($_POST['tableData']) and strlen($_POST['tableData']) > 10) {
	echo $_POST['tableData'];
}
?>
