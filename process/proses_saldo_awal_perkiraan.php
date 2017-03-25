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
		$kodetrans  = $_POST['KODESALDOPERKIRAAN'];
		$kodelokasi = $_SESSION['KODELOKASI'];
		$tgltrans   = ubah_tgl_firebird($_POST['TGLTRANS']);

		$a_detail = json_decode($_POST['data_detail']);

		if (count($a_detail)<1) die(json_encode(array('errorMsg' => 'Detail Transaksi Tidak Boleh Kosong')));

		$mode = $_POST['mode'];
		if ($mode=='tambah') { // generate kodetrans
			/*$temp_kode = 'ASA/'.$_SESSION['KODELOKASI'].'/';

			$urutan = get_new_urutan('saldoperkiraan', 'kodesaldoperkiraan', array($temp_kode, substr($tgltrans, 2, 2)), 2);

			$kodetrans = $temp_kode.$urutan.'/'.substr($tgltrans, 5, 2).'/'.substr($tgltrans, 2, 2);*/
			
			$temp_kode = 'ASA/'.$_SESSION['KODELOKASI'].'/'.substr($tgltrans, 2, 2).substr($tgltrans, 5, 2);

			$urutan = get_max_urutan('saldoperkiraan', 'kodesaldoperkiraan', $temp_kode, 4);

			$kodetrans = $temp_kode.$urutan;

			cek_periode($tgltrans,'tambahkan');
		} else {
			//cek tgl asal mungkin dilakukan perubahan tanggal
			cek_periode(get_tgl_trans('saldoperkiraan', 'kodesaldoperkiraan', $kodetrans), 'ubah');
			cek_periode($tgltrans,'ubah');
		}

		// start transaction
		$tr  = $db->start_trans();

		// insert saldoperkiraan
		$data_values = array (
			$kodetrans, $kodelokasi, $tgltrans, date("Y.m.d"), date("H:i:s"), $_SESSION['user'],
			$_POST['CATATAN'], 'I'
		);
		$exe = $db->insert('saldoperkiraan', $data_values, $tr);
		if (!$exe) { $db->rollback($tr); die(json_encode(array('errorMsg' => 'Simpan Data Gagal<br>Terdapat Kesalahan Data Header'))); }

		//insert saldoperkiraandtl
		$i = 0;
		$sql = $db->insert('saldoperkiraandtl', 8, $tr, true);
		$pr  = $db->prepare($sql, $tr);
		foreach ($a_detail as $item) {
			$data_values = array (
				$kodetrans, $i, $item->kodeperkiraan, $item->saldo, $item->kodecurrency,
				$item->amount, $item->nilaikurs, $item->amountkurs
			);
			$exe = $db->execute($pr, $data_values);
			if (!$exe) { $db->rollback($tr); die(json_encode(array('errorMsg' => 'Simpan Data Gagal<br>Terdapat Kesalahan Data Detail'))); }
			$i++;
		}

		$db->commit($tr);

		// panggil fungsi untuk log history
		log_history(
			$kodetrans,
			'SALDO AWAL PERKIRAAN',
			$_POST['KODESALDOPERKIRAAN']=='' ? 'INSERT' : 'EDIT',
			array(
				array(
					'nama' => 'header',
					'tabel' => 'saldoperkiraan',
					'kode' => 'KODESALDOPERKIRAAN'
				),
				array(
					'nama' => 'detail',
					'tabel' => 'saldoperkiraandtl',
					'kode' => 'KODESALDOPERKIRAAN'
				),
			),
			$_SESSION['user']
		);

		echo json_encode(array('success' => true));
	break;

	case 'load_data' :
		$kodetrans = $_POST['kodetrans'];

		$rows = array();
		$sql = "select a.*, b.namaperkiraan, c.simbol
				from saldoperkiraandtl a left outer join mperkiraan b on a.kodeperkiraan=b.kodeperkiraan
				left outer join mcurrency c on a.kodecurrency=c.kodecurrency
				where a.kodesaldoperkiraan='$kodetrans' order by a.kodeperkiraan";
		$query = $db->query($sql);
		while ($rs = $db->fetch($query)) {
			$rows[] = array(
				'kodeperkiraan'	=> $rs->KODEPERKIRAAN,
				'namaperkiraan' => $rs->NAMAPERKIRAAN,
				'saldo'	     	=> $rs->SALDO,
				'amount'	    => $rs->AMOUNT,
				'kodecurrency'	=> $rs->KODECURRENCY,
				'currency'	    => $rs->SIMBOL,
				'nilaikurs'		=> $rs->NILAIKURS,
				'amountkurs'	=> $rs->AMOUNTKURS,
				'keterangan'    => $rs->KETERANGAN,
			);
		}

		echo json_encode(array(
			'success' => true,
			'detail' => $rows,
		));
	break;

	case 'batal_trans' :
		$kodetrans = $_POST['kodetrans'];
		$status    = get_status('saldoperkiraan', 'kodesaldoperkiraan', $kodetrans);

		if ($status=='P') die(json_encode(array('errorMsg' => 'Transaksi Tidak Bisa Dibatalkan')));

		cek_periode(get_tgl_trans('saldoperkiraan', 'kodesaldoperkiraan', $kodetrans), 'hapus');

		$tr = $db->start_trans();
		$query = $db->update('saldoperkiraan', array('status' => 'D'), array('kodesaldoperkiraan' => $kodetrans), $tr);
		$db->commit($tr);

		// panggil fungsi untuk log history
		log_history(
			$kodetrans,
			'SALDO AWAL PERKIRAAN',
			'DELETE',
			array(
				array(
					'nama'  => 'header',
					'tabel' => 'saldoperkiraan',
					'kode'  => 'KODESALDOPERKIRAAN'
				),
				array(
					'nama'  => 'detail',
					'tabel' => 'saldoperkiraandtl',
					'kode'  => 'KODESALDOPERKIRAAN'
				),
			),
			$_SESSION['user']
		);

		echo json_encode(array('success' => true));
	break;
}
?>