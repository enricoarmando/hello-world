<?php
session_start();
ob_start("ob_gzhandler");
date_default_timezone_set("Asia/Jakarta");

if (empty($_SESSION['user'])) die(json_encode(array('errorMsg' => 'Expired Session <br> Please Relogin')));

include "../../config/koneksi.php";
include "../../config/function.php";

$act = isset($_REQUEST['act']) ? $_REQUEST['act'] : '';

//$db = new DB;

switch ($act) {
	case 'buka_tanggal' :
		$a_detail  = json_decode($_POST['data_detail']);

		// start transaction
		$tr = $db->start_trans();

		// query detail
		$i = 0;
		$sql = $db->insert('historytanggal', 5, $tr, true);
		$pr  = $db->prepare($sql, $tr);
		foreach ($a_detail as $item) {
			$data_values = array (
				$_POST['KODELOKASI'], $item->tanggal, $item->urutanmaxjual, $_SESSION['user'], 1
			);
			$exe = $db->execute($pr, $data_values);
			if (!$exe) { $db->rollback($tr); die(json_encode(array('errorMsg' => 'Simpan Data Gagal <br>Kesalahan Pada Detail Data Transaksi'))); }
			$i++;
		}

		$db->commit($tr);

		// panggil fungsi untuk log history
		log_history(
			'',
			'BUKA TANGGAL',
			'BUKA TANGGAL',
			array(),
			$_SESSION['user']
		);

		echo json_encode(array('success' => true));
	break;
	
	case 'tutup_tanggal' :
		$a_detail  = json_decode($_POST['data_detail']);

		// start transaction
		$tr = $db->start_trans();

		// query detail
		$i = 0;
		$sql = $db->insert('historytanggal', 5, $tr, true);
		$pr  = $db->prepare($sql, $tr);
		foreach ($a_detail as $item) {
			$data_values = array (
				$_POST['KODELOKASI'], $item->tanggal, $item->urutanmaxjual, $_SESSION['user'], 0
			);
			$exe = $db->execute($pr, $data_values);
			if (!$exe) { $db->rollback($tr); die(json_encode(array('errorMsg' => 'Simpan Data Gagal <br>Kesalahan Pada Detail Data Transaksi'))); }
			$i++;
		}

		$db->commit($tr);

		// panggil fungsi untuk log history
		log_history(
			'',
			'BUKA TANGGAL',
			'BUKA TANGGAL',
			array(),
			$_SESSION['user']
		);

		echo json_encode(array('success' => true));
	break;

	case 'tampil_tanggal' :
		// Start date
		$date = $_POST['tglawal'];
		// End date
		$end_date = $_POST['tglakhir'];

		$items = array();
		while (strtotime($date) <= strtotime($end_date)) {
			$items[] = array(
				'tanggal' => $date,
				'urutanmaxjual' => rand(25,30),
				'status' => '',
			);
			
			$date = date ("Y-m-d", strtotime("+1 day", strtotime($date)));
		}
		
		$items2 = array();		
		$p = $db->prepare('select * from historytanggal where kodelokasi = ? and tanggal between ? and ?');
		$e = $db->execute($p, array($_POST['kodelokasi'], $_POST['tglawal'], $_POST['tglakhir']));
		while ($r = $db->fetch($e)) {
			$kembar = false;
			for ($i = 0; $i < count($items); $i++) {
				if ($items[$i]['tanggal'] == $r->TANGGAL) {
					$kembar = true;
					$items[$i]['status'] = $r->STATUS;
					$items[$i]['urutanmaxjual'] = $r->URUTANMAXJUAL;					
					break;
				}
			}
			if ( !$kembar ) {
				$items2[] = array(
					'tanggal' => $r->TANGGAL,
					'urutanmaxjual' => $r->URUTANMAXJUAL,
					'status' => $r->STATUS,
				);
			}
		}
		
		$detil = array_merge($items, $items2);

		echo json_encode(array(
			'success' => true,
			'detail' => $detil,
			'a'=>$items,
		));
	break;
}
?>