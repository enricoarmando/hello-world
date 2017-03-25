<?php
session_start();
include "koneksi.php";
include "function.php";

$act = $_REQUEST['act'];

switch ($act) {
	case 'get_status' :
		$kodetrans = $_POST['kodetrans'];
		$table = $_POST['table'];
		$field = $_POST['field'];
		
		$status = get_status($table, $field, $kodetrans);
		
		echo json_encode(array('status' => $status));
	break;
	
	case 'ubah_status' :
		$kodetrans = $_POST['kodetrans'];
		$table = $_POST['table'];
		$field = $_POST['field'];
		$status = $_POST['status'];
		
		//$db = new DB;
		
		$tr = $db->start_trans();
		$db->update(
			$table, 
			array('STATUS' => $status), 
			array($field => $kodetrans), 
			$tr
		);
		$db->commit($tr);
		
		echo json_encode(array('status' => 'sukses'));
	break;
}
?>