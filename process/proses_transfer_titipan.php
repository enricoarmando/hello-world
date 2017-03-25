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
		$kodetrans    = $_POST['KODETRANSFERTITIPAN'];
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
			/*$temp_kode = 'TFT/'.$lokasiasal.'/';

			$urutan = get_new_urutan('ttransfertitipan', 'kodetransfertitipan', array($temp_kode, substr($tgltrans, 2, 2)));

			$kodetrans = $temp_kode.$urutan.'/'.substr($tgltrans, 5, 2).'/'.substr($tgltrans, 2, 2);*/

			$temp_kode = 'TFT/'.$lokasiasal.'/'.substr($tgltrans, 2, 2).substr($tgltrans, 5, 2);

			$urutan = get_max_urutan('ttransfertitipan', 'kodetransfertitipan', $temp_kode, 4);

			$kodetrans = $temp_kode.$urutan;

			cek_periode($tgltrans,'tambahkan');
		} else {
			cek_periode(get_tgl_trans('ttransfertitipan', 'kodetransfertitipan', $kodetrans), 'ubah');
			cek_periode($tgltrans,'ubah');
		}

		// start transaction
		$tr  = $db->start_trans();

		// insert ttransfertitipan
		$data_values = array(
			$kodetrans, $_POST['KODESO'], $lokasiasal, $_POST['NAMALOKASIASAL'],
			$lokasitujuan, $_POST['NAMALOKASITUJUAN'], $tgltrans, date("Y.m.d"), date("H:i:s"),
			$_SESSION['user'], $catatan, 'I', 0
		);
		$exe = $db->insert('ttransfertitipan', $data_values, $tr);
		if (!$exe) { $db->rollback($tr); die(json_encode(array('errorMsg' => 'Simpan Data Gagal <br>Kesalahan Pada Header Data Transaksi'))); }

		//insert saldostokdtl
		$i = 0;
		$sql = $db->insert('ttransfertitipandtl', 10, false, true);
		$pr  = $db->prepare($sql, $tr);
		foreach ($a_detail as $item) {
			$data_values = array (
				$kodetrans, $item->kodebarang, $i, $item->namabarang, $item->jml,
				$item->harga, $item->subtotal, $item->satuan, '', 0
			);
		    if ($item->jml > 0) {
				// cek stok
				$stok_cukup = cek_stok ($item->kodebarang, $tgltrans, $item->jml, $item->satuan);
				if (!$stok_cukup) { $db->rollback($tr); die(json_encode(array('errorMsg' => 'stok barang '.$item->kodebarang.' ('.$item->namabarang.') tidak mencukupi'))); }

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
			$_POST['KODETRANSFERTITIPAN']=='' ? 'INSERT' : 'EDIT',
			array(
				array(
					'nama' => 'header',
					'tabel' => 'ttransfertitipan',
					'kode' => 'KODETRANSFERTITIPAN'
				),
				array(
					'nama' => 'detail',
					'tabel' => 'ttransfertitipandtl',
					'kode' => 'KODETRANSFERTITIPAN'
				),
			),
			$_SESSION['user']
		);

		echo json_encode(array('success' => true));
	break;

	case 'load_data' :
		$kodetrans = $_POST['kodetrans'];

		$rows = array();
		$sql   = "select b.*, c.tipe, d.jenisframe
				  from ttransfertitipan a
				  inner join ttransfertitipandtl b on a.kodetransfertitipan=b.kodetransfertitipan
				  inner join mbarang c on b.kodebarang = c.kodebarang
				  left join mjenisframe d on b.kodebarang = d.kodebarang
				  where a.kodetransfertitipan = '$kodetrans'
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
			);
		}

		echo json_encode(array(
			'success' => true,
			'detail' => $rows,
		));
	break;

	case 'batal_trans' :
		$kodetrans = $_POST['kodetrans'];
		$status    = get_status('ttransfertitipan', 'kodetransfertitipan', $kodetrans);

		if ($status=='P') die(json_encode(array('errorMsg' => 'Transaksi Tidak Dapat Dibatalkan')));

		cek_periode(get_tgl_trans('ttransfertitipan', 'kodetransfertitipan', $kodetrans), 'hapus');

		// cek so, jika sudah QC maka tidak bisa dibatalkan
		$sql = "select distinct a.status
				from tso a
				inner join ttransfertitipan b on a.kodeso = b.kodeso
				where b.kodetransfertitipan = '$kodetrans'";
		$q = $db->query($sql);
		$r = $db->fetch($q);
		if ($r->STATUS == 'P' or $r->STATUS == 'Q') die(json_encode(array('errorMsg' => 'Transaksi Tidak Dapat Dibatalkan<br>No SO Sudah Dipakai Di Quality Control/Penjualan')));

		cek_periode(get_tgl_trans('ttransfertitipan', 'kodetransfertitipan', $kodetrans), 'ubah');

		$tr = $db->start_trans();
		$query = $db->update('ttransfertitipan', array('status' => 'D'), array('kodetransfertitipan' => $kodetrans), $tr);
		$db->commit($tr);

		// panggil fungsi untuk log history
		log_history(
			$kodetrans,
			'TRANSFER TITIPAN',
			'DELETE',
			array(
				array(
					'nama' => 'header',
					'tabel' => 'ttransfertitipan',
					'kode' => 'KODETRANSFERTITIPAN'
				),
				array(
					'nama' => 'detail',
					'tabel' => 'ttransfertitipandtl',
					'kode' => 'KODETRANSFERTITIPAN'
				),
			),
			$_SESSION['user']
		);

		echo json_encode(array(
			'success' => true,
		));
	break;

	case 'load_data_so' :
		$kodetrans = $_POST['kodetrans'];

		$sql = "select distinct b.kodelokasi, a.kodeso, A.kodebarang, E.namabarang, A.satuan,
					   E.tipe, A.jml, (A.subtotal/A.jml) as harga, A.subtotal, F.jenisframe, E.hargajual
				from TSODTL A
				inner join TSO B on A.KODESO = B.KODESO and B.STATUS <> 'D'
				inner join MBARANG E on A.KODEBARANG = E.KODEBARANG
				left join MJENISFRAME F ON E.KODEBARANG = F.KODEBARANG
				left join TTRANSFERTITIPAN D on B.KODESO = D.KODESO and D.STATUS <> 'D'
				left join TTRANSFERTITIPANDTL C on D.KODETRANSFERTITIPAN = C.KODETRANSFERTITIPAN and A.KODEBARANG = C.KODEBARANG
				where C.KODEBARANG is null and a.kodeso = ?";
		$pr = $db->prepare($sql);
		$query = $db->execute($pr, $kodetrans);
		while ($rs = $db->fetch($query)) {
			
			// parameter tampil stok berasal dari retur pembelian
			// untuk mengetahui stok terbaru dari lokasi SO
			$stok = 0;
			//if ($_POST['tampil_stok']) {
				$pr = $db->prepare('execute procedure GET_SALDOSTOK(?, ?, ?, ?)');
				$ex = $db->execute($pr, array($rs->KODEBARANG, $rs->KODELOKASI, '', date('Y-m-d')));
				$r = $db->fetch($ex);
				$stok = $r->SALDO;
			//}
			
			$rows[] = array(
				'kodebarang'    => $rs->KODEBARANG,
				'namabarang'    => $rs->NAMABARANG,
				'tipe'		    => $rs->TIPE,
				'jenisframe'	=> $rs->JENISFRAME,
				'satuan'        => $rs->SATUAN,
				'jml'           => $rs->JML,
				'jmlstok'		=> $stok,
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
}
?>