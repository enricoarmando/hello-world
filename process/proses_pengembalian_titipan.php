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
		$kodetrans    = $_POST['KODEKEMBALITITIPAN'];
		$lokasiasal   = $_POST['KODELOKASIASAL'];
		$lokasitujuan = $_POST['KODELOKASITUJUAN'];
		$tgltrans     = ubah_tgl_firebird($_POST['TGLTRANS']);
		$catatan      = $_POST['CATATAN'];

		$a_detail = json_decode($_POST['data_detail']);

		cek_data($a_detail, 'kodebarang', 'mbarang');

		if (count($a_detail)<1) die(json_encode(array('errorMsg' => 'Detail Transaksi Tidak Boleh Kosong')));
		if ($lokasiasal == $lokasitujuan) die(json_encode(array('errorMsg' => 'Lokasi Asal Dan Tujuan Tidak Boleh Sama')));

		cek_valid_data('MLOKASI', 'KODELOKASI', $lokasiasal, 'Lokasi Asal');
		cek_valid_data('MLOKASI', 'KODELOKASI', $lokasitujuan, 'Lokasi Tujuan');

		$mode = $_POST['mode'];
		if ($mode=='tambah') { // generate kodetrans
			/*$temp_kode = 'TKT/'.$lokasiasal.'/';

			$urutan = get_new_urutan('tkembalititipan', 'kodekembalititipan', array($temp_kode, substr($tgltrans, 2, 2)));

			$kodetrans = $temp_kode.$urutan.'/'.substr($tgltrans, 5, 2).'/'.substr($tgltrans, 2, 2);*/

			$temp_kode = 'TKT/'.$lokasiasal.'/'.substr($tgltrans, 2, 2).substr($tgltrans, 5, 2);

			$urutan = get_max_urutan('tkembalititipan', 'kodekembalititipan', $temp_kode, 4);

			$kodetrans = $temp_kode.$urutan;

			cek_periode($tgltrans,'tambahkan');
		} else {
			//cek tgl asal mungkin dilakukan perubahan tanggal
			cek_periode(get_tgl_trans('tkembalititipan', 'kodekembalititipan', $kodetrans), 'ubah');
			cek_periode($tgltrans,'ubah');
		}

		// start transaction
		$tr  = $db->start_trans();

		// insert tkembalititipan
		$data_values = array(
			$kodetrans, $_POST['KODETERIMATITIPAN'], $lokasiasal, $_POST['NAMALOKASIASAL'],
			$lokasitujuan, $_POST['NAMALOKASITUJUAN'], $tgltrans, date("Y.m.d"), date("H:i:s"),
			$_SESSION['user'], $catatan, 'S', 0
		);
		$exe = $db->insert('tkembalititipan', $data_values, $tr);
		if (!$exe) { $db->rollback($tr); die(json_encode(array('errorMsg' => 'Simpan Data Gagal <br>Kesalahan Pada Header Data Transaksi'))); }

		//insert saldostokdtl
		$i = 0;
		$sql = $db->insert('tkembalititipandtl', 10, false, true);
		$pr  = $db->prepare($sql, $tr);
		foreach ($a_detail as $item) {
			$data_values = array (
				$kodetrans, $item->kodebarang, $i, $item->namabarang, $item->jml,
				$item->harga, $item->subtotal, $item->satuan, '', 0
			);
			if ($item->jml > 0) {
				$exe = $db->execute($pr, $data_values);
				if (!$exe) { $db->rollback($tr); die(json_encode(array('errorMsg' => 'Simpan Data Gagal <br>Kesalahan Pada Detail Data Transaksi'))); }
				$i++;
			}
		}

		$db->commit($tr);

		// panggil fungsi untuk log history
		log_history(
			$kodetrans,
			'TRANSFER TITIPAN',
			$_POST['KODEKEMBALITITIPAN']=='' ? 'INSERT' : 'EDIT',
			array(
				array(
					'nama' => 'header',
					'tabel' => 'tkembalititipan',
					'kode' => 'KODEKEMBALITITIPAN'
				),
				array(
					'nama' => 'detail',
					'tabel' => 'tkembalititipandtl',
					'kode' => 'KODEKEMBALITITIPAN'
				),
			),
			$_SESSION['user']
		);

		echo json_encode(array('success' => true));
	break;

	case 'load_data' :
		$kodetrans = $_POST['kodetrans'];

		$rows = array();
		$sql   = "select b.*, c.tipe, d.jenisframe, c.hargajual
				  from tkembalititipan a
				  inner join tkembalititipandtl b on a.kodekembalititipan=b.kodekembalititipan
				  inner join mbarang c on b.kodebarang = c.kodebarang
				  left join mjenisframe d on c.kodebarang = d.kodebarang
				  where a.kodekembalititipan = '$kodetrans'
		          order by kodebarang";
		$query = $db->query($sql);
		while ($rs = $db->fetch($query)) {
			$rows[] = array(
				'kodebarang'    => $rs->KODEBARANG,
				'namabarang'    => $rs->NAMABARANG,
				'tipe'    		=> $rs->TIPE,
				'jenisframe'	=> $rs->JENISFRAME,
				'satuan'        => $rs->SATUAN,
				'jml'           => $rs->JML,
				'harga'         => $rs->HARGA,
				'subtotal'      => $rs->SUBTOTAL,
				'hargajual'     => $rs->HARGAJUAL,
			);
		}

		echo json_encode(array(
			'success' => true,
			'detail' => $rows,
		));
	break;

	case 'batal_trans' :
		$kodetrans = $_POST['kodetrans'];
		$status    = get_status('tkembalititipan', 'kodekembalititipan', $kodetrans);

		if ($status=='P') die(json_encode(array('errorMsg' => 'Transaksi Tidak Dapat Dibatalkan')));

		cek_periode(get_tgl_trans('tkembalititipan', 'kodekembalititipan', $kodetrans), 'hapus');

		$tr = $db->start_trans();
		$query = $db->update('tkembalititipan', array('status' => 'D'), array('kodekembalititipan' => $kodetrans), $tr);
		$db->commit($tr);

		// panggil fungsi untuk log history
		log_history(
			$kodetrans,
			'TRANSFER TITIPAN',
			'DELETE',
			array(
				array(
					'nama' => 'header',
					'tabel' => 'tkembalititipan',
					'kode' => 'KODEKEMBALITITIPAN'
				),
				array(
					'nama' => 'detail',
					'tabel' => 'tkembalititipandtl',
					'kode' => 'KODEKEMBALITITIPAN'
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