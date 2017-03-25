<?php
session_start();
ob_start("ob_gzhandler");
date_default_timezone_set("Asia/Jakarta");

if (empty($_SESSION['user'])) die(json_encode(array('errorMsg' => 'Expired Session <br> Please Relogin')));

include "../../config/koneksi.php";
include "../../config/function.php";

// UNTUK BROWSE DATA
$act = isset($_REQUEST['act']) ? $_REQUEST['act'] : '';

//$db = new DB;

switch ($act) {
	case 'simpan_trans' :
		$kodetrans  = $_POST['KODEUBAHJURUBAYAR'];
		$tgltrans   = ubah_tgl_firebird($_POST['TGLTRANS']);
		$keterangan = $_POST['CATATAN'];

		cek_valid_data('MJURUBAYAR', 'KODEJURUBAYAR', $_POST['KODEJURUBAYARLAMA'], 'Juru Bayar Lama');
		cek_valid_data('MJURUBAYAR', 'KODEJURUBAYAR', $_POST['KODEJURUBAYARBARU'], 'Juru Bayar Baru');

		$mode = $_POST['mode'];

		if ($mode=='tambah') { // generate kodetrans
			/*$temp_kode = 'UJB/'.$_SESSION['KODELOKASI'].'/';

			$urutan = get_new_urutan('TUBAHJURUBAYAR', 'KODEUBAHJURUBAYAR', array($temp_kode, substr($tgltrans, 2, 2)), 2);

			$kodetrans = $temp_kode.$urutan.'/'.substr($tgltrans, 5, 2).'/'.substr($tgltrans, 2, 2);*/

			$temp_kode = 'UJB/'.$_SESSION['KODELOKASI'].'/'.substr($tgltrans, 2, 2).substr($tgltrans, 5, 2);

			$urutan = get_max_urutan('tubahjurubayar', 'kodeubahjurubayar', $temp_kode, 4);

			$kodetrans = $temp_kode.$urutan;

			cek_periode($tgltrans,'tambahkan');
		} else {
			cek_periode(get_tgl_trans('tubahjurubayar', 'kodeubahjurubayar', $kodetrans), 'ubah');
			cek_periode($tgltrans,'ubah');
		}

		// start transaction
		$tr  = $db->start_trans();

		// insert saldostok
		$data_values = array (
			$kodetrans, $_SESSION['KODELOKASI'], $_POST['KODEJUAL'], $_POST['KODEJURUBAYARLAMA'], $_POST['KODEJURUBAYARBARU'],
			$tgltrans, date("Y.m.d"), date("H:i:s"), $keterangan, $_SESSION['user'],
			'I', 0
		);
		$exe = $db->insert('tubahjurubayar', $data_values, $tr);
		if (!$exe) { $db->rollback($tr); die(json_encode(array('errorMsg' => 'Simpan Data Gagal <br>Kesalahan Pada Header Data Transaksi'))); }

		$db->commit($tr);

		// panggil fungsi untuk log history
		log_history(
			$kodetrans,
			'UBAH JURU BAYAR',
			$_POST['KODEUBAHJURUBAYAR']=='' ? 'INSERT' : 'EDIT',
			array(
				array(
					'nama'  => 'header',
					'tabel' => 'tubahjurubayar',
					'kode'  => 'KODEUBAHJURUBAYAR'
				),
			),
			$_SESSION['user']
		);

		echo json_encode(array('success' => true));
	break;

	case 'batal_trans' :
		$kodetrans = $_POST['kodetrans'];
		$status    = get_status('tubahjurubayar', 'kodeubahjurubayar', $kodetrans);

		if ($status=='P') die(json_encode(array('errorMsg' => 'Transaksi Tidak Dapat Dibatalkan')));

		cek_periode(get_tgl_trans('tubahjurubayar', 'kodeubahjurubayar', $kodetrans), 'hapus');

		$tr = $db->start_trans();
		$query = $db->update('tubahjurubayar', array('status' => 'D'), array('kodeubahjurubayar' => $kodetrans), $tr);
		$db->commit($tr);

		// panggil fungsi untuk log history
		log_history(
			$kodetrans,
			'SALDO AWAL STOK',
			'DELETE',
			array(
				array(
					'nama'  => 'header',
					'tabel' => 'tubahjurubayar',
					'kode'  => 'KODEUBAHJURUBAYAR'
				)
			),
			$_SESSION['user']
		);

		echo json_encode(array('success' => true));
	break;
}
?>