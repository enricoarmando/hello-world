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
		$kodetrans    = $_POST['KODEKUITANSI'];
		$tgltrans     = ubah_tgl_firebird($_POST['TGLTRANS']);
		$keterangan   = $_POST['KETERANGAN'];

		$mode = $_POST['mode'];
		if ($mode=='tambah') { // generate kodetrans
			/*$temp_kode = 'KW/'.$_SESSION['KODELOKASI'].'/';

			$urutan = get_new_urutan('tkuitansi', 'kodekuitansi', array($temp_kode, substr($tgltrans, 2, 2)));

			$kodetrans = $temp_kode.$urutan.'/'.substr($tgltrans, 5, 2).'/'.substr($tgltrans, 2, 2);*/

			$temp_kode = 'KW/'.$_SESSION['KODELOKASI'].'/'.substr($tgltrans, 2, 2).substr($tgltrans, 5, 2);

			$urutan = get_max_urutan('tkuitansi', 'kodekuitansi', $temp_kode, 4);

			$kodetrans = $temp_kode.$urutan;

			cek_periode($tgltrans,'tambahkan');
		} else {
			//cek tgl asal mungkin dilakukan perubahan tanggal
			cek_periode(get_tgl_trans('tkuitansi', 'kodekuitansi', $kodetrans), 'ubah');
			cek_periode($tgltrans,'ubah');
		}

		// start transaction
		$tr  = $db->start_trans();

		// insert tkuitansi
		$data_values = array(
			$kodetrans, $_SESSION['KODELOKASI'],$tgltrans, date("Y.m.d"), date("H:i:s"), $_POST['NAMA'],
			$_POST['ALAMAT'], $_POST['TELP'], $_POST['AMOUNT'], $keterangan, $_SESSION['user'],
			'I'
		);
		$exe = $db->insert('tkuitansi', $data_values, $tr);
		if (!$exe) { $db->rollback($tr); die(json_encode(array('errorMsg' => 'Simpan Data Gagal <br>Kesalahan Pada Header Data Transaksi'))); }

		$db->commit($tr);

		// panggil fungsi untuk log history
		log_history(
			$kodetrans,
			'KUITANSI MANUAL',
			$_POST['KODEPENYESUAIAN']=='' ? 'INSERT' : 'EDIT',
			array(
				array(
					'nama'  => 'header',
					'tabel' => 'tkuitansi',
					'kode'  => 'KODEKUITANSI'
				),
			),
			$_SESSION['user']
		);

		echo json_encode(array('success' => true));
	break;

	case 'load_data' :
		$kodetrans = $_POST['kodetrans'];
		echo json_encode(array(
			'success' => true,
			'detail' => $rows,
		));
	break;

	case 'batal_trans' :
		$kodetrans = $_POST['kodetrans'];
		$status    = get_status('tkuitansi', 'kodekuitansi', $kodetrans);

		if ($status=='P') die(json_encode(array('errorMsg' => 'Transaksi Tidak Dapat Dibatalkan')));

		cek_periode(get_tgl_trans('tkuitansi', 'kodekuitansi', $kodetrans), 'hapus');

		$tr = $db->start_trans();
		$query = $db->update('tkuitansi', array('status' => 'D'), array('kodekuitansi' => $kodetrans), $tr);
		$db->commit($tr);

		// panggil fungsi untuk log history
		log_history(
			$kodetrans,
			'KUITANSI MANUAL',
			'DELETE',
			array(
				array(
					'nama'  => 'header',
					'tabel' => 'tkuitansi',
					'kode'  => 'KODEKUITANSI'
				),
			),
			$_SESSION['user']
		);

		echo json_encode(array(
			'success' => true,
		));
	break;
}
?>