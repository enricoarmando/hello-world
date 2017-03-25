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
	case 'simpan_trans' :
		$kodetrans = $_POST['KODEFOLLOWUP'];
		$tgltrans = $_POST['TGLTRANS'];
		
		$mode = $_POST['mode'];
		
		if ($mode=='tambah') {
			$temp_kode = 'FP/'.$_SESSION['KODELOKASI'].'/'.substr($tgltrans, 2, 2).substr($tgltrans, 5, 2);

			$urutan = get_max_urutan('tfollowup', 'kodefollowup', $temp_kode, 4);

			$kodetrans = $temp_kode.$urutan;

			cek_periode($tgltrans,'tambahkan');
		} else {
			//cek tgl asal mungkin dilakukan perubahan tanggal
			cek_periode(get_tgl_trans('tfollowup', 'kodefollowup', $kodetrans), 'ubah');
			cek_periode($tgltrans,'ubah');
		}
		
		// start transaction
		$tr = $db->start_trans();

		// query header
		$data_values = array(
			$kodetrans, $_SESSION['KODELOKASI'], $_POST['KODEJUAL'], $tgltrans, date("Y.m.d"), 
			date("H:i:s"), $_SESSION['user'], '', $_POST['KETERANGAN'], 'I'
		);
		$exe = $db->insert('tfollowup', $data_values, $tr);
		if (!$exe) { $db->rollback($tr); die(json_encode(array('errorMsg' => 'Simpan Data Gagal <br>Kesalahan Pada Header Data Transaksi'))); }

		$db->commit($tr);

		// panggil fungsi untuk log history
		log_history(
			$kodetrans,
			'FOLLOW UP',
			'INSERT',
			array(
				array(
					'nama'  => 'header',
					'tabel' => 'tfollowup',
					'kode'  => 'kodefollowup'
				),
			),
			$_SESSION['user']
		);

		echo json_encode(array('success' => true));
	break;
}
?>