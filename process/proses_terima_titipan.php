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
		$kodetrans    = $_POST['KODETERIMATITIPAN'];
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
			/*$temp_kode = 'TMT/'.$lokasiasal.'/';

			$urutan = get_new_urutan('tterimatitipan', 'kodeterimatitipan', array($temp_kode, substr($tgltrans, 2, 2)));

			$kodetrans = $temp_kode.$urutan.'/'.substr($tgltrans, 5, 2).'/'.substr($tgltrans, 2, 2);*/

			$temp_kode = 'TMT/'.$lokasitujuan.'/'.substr($tgltrans, 2, 2).substr($tgltrans, 5, 2);

			$urutan = get_max_urutan('tterimatitipan', 'kodeterimatitipan', $temp_kode, 4);

			$kodetrans = $temp_kode.$urutan;

			cek_periode($tgltrans,'tambahkan');
		} else {
			cek_periode(get_tgl_trans('tterimatitipan', 'kodeterimatitipan', $kodetrans), 'ubah');
			cek_periode($tgltrans,'ubah');
		}

		// start transaction
		$tr  = $db->start_trans();

		// insert tterimatitipan
		$data_values = array(
			$kodetrans, $_POST['KODETRANSFERTITIPAN'], $lokasiasal, $_POST['NAMALOKASIASAL'],
			$lokasitujuan, $_POST['NAMALOKASITUJUAN'], $tgltrans, date("Y.m.d"), date("H:i:s"),
			$_SESSION['user'], $catatan, 'S', 0
		);
		$exe = $db->insert('tterimatitipan', $data_values, $tr);
		if (!$exe) { $db->rollback($tr); die(json_encode(array('errorMsg' => 'Simpan Data Gagal <br>Kesalahan Pada Header Data Transaksi'))); }

		//insert saldostokdtl
		$i = 0;
		$sql = $db->insert('tterimatitipandtl', 10, false, true);
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

			// cek stok
			//$stok_cukup = cek_stok ($item->kodebarang, $tgltrans, $item->jml, $item->satuan, $lokasiasal);
			//if (!$stok_cukup) { $db->rollback($tr); die(json_encode(array('errorMsg' => 'stok barang '.$item->kodebarang.' ('.$item->namabarang.') tidak mencukupi'))); }
		}

		$db->commit($tr);

		// panggil fungsi untuk log history
		log_history(
			$kodetrans,
			'TRANSFER TITIPAN',
			$_POST['KODETERIMATITIPAN']=='' ? 'INSERT' : 'EDIT',
			array(
				array(
					'nama' => 'header',
					'tabel' => 'tterimatitipan',
					'kode' => 'KODETERIMATITIPAN'
				),
				array(
					'nama' => 'detail',
					'tabel' => 'tterimatitipandtl',
					'kode' => 'KODETERIMATITIPAN'
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
				  from tterimatitipan a
				  inner join tterimatitipandtl b on a.kodeterimatitipan=b.kodeterimatitipan
				  inner join mbarang c on b.kodebarang = c.kodebarang
				  inner join mjenisframe d on b.kodebarang = d.kodebarang
				  where a.kodeterimatitipan = '$kodetrans'
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
				'hargajual'		=> $rs->HARGAJUAL,
			);
		}

		echo json_encode(array(
			'success' => true,
			'detail' => $rows,
		));
	break;

	case 'batal_trans' :
		$kodetrans = $_POST['kodetrans'];
		$status    = get_status('tterimatitipan', 'kodeterimatitipan', $kodetrans);

		if ($status=='P') die(json_encode(array('errorMsg' => 'Transaksi Tidak Dapat Dibatalkan')));

		cek_periode(get_tgl_trans('tterimatitipan', 'kodeterimatitipan', $kodetrans), 'hapus');

		$tr = $db->start_trans();
		$query = $db->update('tterimatitipan', array('status' => 'D'), array('kodeterimatitipan' => $kodetrans), $tr);
		$db->commit($tr);

		// panggil fungsi untuk log history
		log_history(
			$kodetrans,
			'TRANSFER TITIPAN',
			'DELETE',
			array(
				array(
					'nama' => 'header',
					'tabel' => 'tterimatitipan',
					'kode' => 'KODETERIMATITIPAN'
				),
				array(
					'nama' => 'detail',
					'tabel' => 'tterimatitipandtl',
					'kode' => 'KODETERIMATITIPAN'
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