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
		$kodetrans       = $_POST['KODETRANSFERGUDANG'];
		$lokasiasal	     = $_SESSION['KODELOKASI'];
		$gudangasal      = $_POST['KODEGUDANGASAL'];
		$gudangtujuan    = $_POST['KODEGUDANGTUJUAN'];
		$penanggungjawab = $_POST['KODEPENANGGUNGJAWAB'];
		$tgltrans        = ubah_tgl_firebird($_POST['TGLTRANS']);
		$keterangan      = $_POST['CATATAN'];

		$a_detail = json_decode($_POST['data_detail']);

		cek_data($a_detail, 'kodebarang', 'mbarang');

		if ($gudangasal==$gudangtujuan) die(json_encode(array('errorMsg' => 'Gudang Asal dan Gudang Tujuan Tidak Boleh Sama')));
		if (count($a_detail)<1) die(json_encode(array('errorMsg' => 'Please Insert Detail Transaction')));

		cek_valid_data('MGUDANG', 'KODEGUDANG', $gudangasal, 'Gudang Asal');
		cek_valid_data('MGUDANG', 'KODEGUDANG', $gudangtujuan, 'Gudang Tujuan');
		cek_valid_data('MUSER', 'USERID', $penanggungjawab, 'Penanggung Jawab');
		cek_valid_data('MALASAN', 'ALASAN', $_POST['ALASAN'], 'Alasan');

		$mode = $_POST['mode'];
		if ($mode=='tambah') { // generate kodetrans
			/*$temp_kode = 'TAG/'.$lokasiasal.'/';

			$urutan = get_new_urutan('TTRANSFERGUDANG', 'KODETRANSFERGUDANG', array($temp_kode, substr($tgltrans, 2, 2)), 4);

			$kodetrans = $temp_kode.$urutan.'/'.substr($tgltrans, 5, 2).'/'.substr($tgltrans, 2, 2);*/

			$temp_kode = 'BRK/'.$lokasiasal.'/'.substr($tgltrans, 2, 2).substr($tgltrans, 5, 2);

			$urutan = get_max_urutan('ttransfergudang', 'kodetransfergudang', $temp_kode, 4);

			$kodetrans = $temp_kode.$urutan;

			cek_periode($tgltrans,'tambahkan');
		} else {
			cek_periode(get_tgl_trans('ttransfergudang', 'kodetransfergudang', $kodetrans), 'ubah');
			cek_periode($tgltrans,'ubah');
		}

		// start transaction
		$tr  = $db->start_trans();

		// insert saldostok
		$data_values = array(
			$kodetrans, $lokasiasal, $gudangasal, $gudangtujuan, $penanggungjawab, $_POST['ALASAN'], $tgltrans, date("Y.m.d"), date("H:i:s"), $_SESSION['user'],
			$keterangan, 'I', 0
		);
		$exe = $db->insert('ttransfergudang', $data_values, $tr);
		if (!$exe) { $db->rollback($tr); die(json_encode(array('errorMsg' => 'Transaksi Gagal <br>Harap Cek Data Header Transaksi'))); }

		//insert saldostokdtl
		$i = 0;
		$sql = $db->insert('ttransfergudangdtl', 10, $tr, true);
		$pr  = $db->prepare($sql, $tr);
		foreach ($a_detail as $item) {
			$data_values = array(
				$kodetrans, $item->kodebarang, $i, $item->namabarang, $item->jml,
				0, 0, $item->satuan, $item->satuankecil, 1
			);

			if ($item->jml > 0) {
				$exe = $db->execute($pr, $data_values);
				if (!$exe) { $db->rollback($tr); die(json_encode(array('errorMsg' => 'Transaksi Gagal <br>Harap Cek Data Detail Transaksi'))); }
				$i++;

				// cek stok
				$stok_cukup = cek_stok ($item->kodebarang, $tgltrans, $item->jml, $item->satuan, $lokasiasal, $gudangasal);
				if (!$stok_cukup) { $db->rollback($tr); die(json_encode(array('errorMsg' => 'stok barang '.$item->kodebarang.' ('.$item->namabarang.') tidak mencukupi'))); }
			}
		}
		$db->commit($tr);

		$tr = $db->start_trans();
		$query = $db->update('ttransfergudang', array('status' => 'S'), array('kodetransfergudang' => $kodetrans), $tr);
		$db->commit($tr);

		// panggil fungsi untuk log history
		log_history(
			$kodetrans,
			'TRANSFER ANTAR GUDANG',
			$_POST['KODETRANSFERGUDANG']=='' ? 'INSERT' : 'EDIT',
			array(
				array(
					'nama'  => 'header',
					'tabel' => 'ttransfergudang',
					'kode'  => 'kodetransfergudang'
				),
				array(
					'nama'  => 'detail',
					'tabel' => 'ttransfergudangdtl',
					'kode'  => 'kodetransfergudang'
				),
			),
			$_SESSION['user']
		);

		echo json_encode(array('success' => true, 'kodetrans'=>$kodetrans));
	break;

	case 'load_data' :
		$kodetrans = $_POST['kodetrans'];

		$rows = array();
		$sql   = "select b.*, c.tipe, c.namabarangsupplier, d.jenisframe, a.kodegudangtujuan, c.hargajual
				  from ttransfergudang a
				  inner join ttransfergudangdtl b on a.kodetransfergudang=b.kodetransfergudang
				  inner join mbarang c on b.kodebarang=c.kodebarang
				  left join mjenisframe d on b.kodebarang = d.kodebarang
				  where a.kodetransfergudang = '$kodetrans'
		          order by kodebarang";

		$query = $db->query($sql);
		while ($rs = $db->fetch($query)) {
			$stok = 0;
			if ($_POST['tampil_stok']) {
				$pr = $db->prepare('execute procedure GET_SALDOSTOK(?, ?, ?, ?)');
				$ex = $db->execute($pr, array($rs->KODEBARANG, $_SESSION['KODELOKASI'], $rs->KODEGUDANGTUJUAN, $_POST['tgltrans']));
				$r = $db->fetch($ex);
				$stok = $r->SALDO;
			}
			
			$rows[] = array(
				'kodebarang'  => $rs->KODEBARANG,
				'namabarang'  => $rs->NAMABARANG,
				'tipe'        => $rs->TIPE,
				'jenisframe'  => $rs->JENISFRAME,
				'namabarangsupplier'  => $rs->NAMABARANGSUPPLIER,
				'jmlstok'	  => $stok,
				'jml'         => $rs->JML,
				'satuan'      => $rs->SATUAN,
				'satuanutama' => $rs->SATUANUTAMA,
				'konversi'    => $rs->KONVERSI,
				'hargajual'   => $rs->HARGAJUAL,
			);
		}

		echo json_encode(array(
			'success' => true,
			'detail' => $rows,
		));
	break;

	case 'batal_trans' :
		$kodetrans = $_POST['kodetrans'];
		$status    = get_status('ttransfergudang', 'kodetransfergudang', $kodetrans);

		if ($status=='P') die(json_encode(array('errorMsg' => 'Transaksi Tidak Dapat Dibatalkan')));

		cek_periode(get_tgl_trans('ttransfergudang', 'kodetransfergudang', $kodetrans), 'hapus');

		$tr = $db->start_trans();
		$query = $db->update('ttransfergudang', array('status' => 'D'), array('kodetransfergudang' => $kodetrans), $tr);
		$db->commit($tr);

		// panggil fungsi untuk log history
		log_history(
			$kodetrans,
			'TRANSFER ANTAR GUDANG',
			'DELETE',
			array(
				array(
					'nama'  => 'header',
					'tabel' => 'ttransfergudang',
					'kode'  => 'kodetransfergudang'
				),
				array(
					'nama'  => 'detail',
					'tabel' => 'ttransfergudangdtl',
					'kode'  => 'kodetransfergudang'
				),
			),
			$_SESSION['user']
		);

		echo json_encode(array('success' => true));
	break;
}
?>