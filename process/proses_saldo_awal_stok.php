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
		$kodetrans  = $_POST['KODESALDOSTOK'];
		$lokasi     = $_SESSION['KODELOKASI'];//$_POST['KODELOKASI'];
		$gudang     = $_POST['KODEGUDANG'];
		$tgltrans   = ubah_tgl_firebird($_POST['TGLTRANS']);
		$keterangan = $_POST['CATATAN'];

		$a_detail = json_decode($_POST['data_detail']);

		cek_data($a_detail, 'kodebarang', 'mbarang');

		if (count($a_detail)<1) die(json_encode(array('errorMsg' => 'Detail Transaksi Tidak Boleh Kosong')));

		cek_valid_data('MLOKASI', 'KODELOKASI', $lokasi, 'Lokasi');

		$mode = $_POST['mode'];
		// cek apakah tanggal yang diinput berbeda dengan tanggal yang pernah diinput sebelumnya
		// transaksi saldo awal hanya ada 1 tanggal saka
		$sql   = "select * from saldostok where status <> 'D'";
		$query = $db->query($sql);
		$rs = $db->fetch($query);

		if($rs->TGLTRANS!='' and $rs->TGLTRANS!=$tgltrans){
			die(json_encode(array('errorMsg' => 'Tanggal Transaksi Salah, Gunakan Tanggal '.$rs->TGLTRANS.' Untuk Transaksi Ini')));
		}

		if ($mode=='tambah') { // generate kodetrans
			/*$temp_kode = 'ASA/'.$_SESSION['KODELOKASI'].'/';

			$urutan = get_new_urutan('saldostok', 'kodesaldostok', array($temp_kode, substr($tgltrans, 2, 2)), 2);

			$kodetrans = $temp_kode.$urutan.'/'.substr($tgltrans, 5, 2).'/'.substr($tgltrans, 2, 2);*/

			$temp_kode = 'ASA/'.$_SESSION['KODELOKASI'].'/'.substr($tgltrans, 2, 2).substr($tgltrans, 5, 2);

			$urutan = get_max_urutan('saldostok', 'kodesaldostok', $temp_kode, 2);

			$kodetrans = $temp_kode.$urutan;

			//cek_periode($tgltrans,'tambahkan');
		} else {
			//cek_periode(get_tgl_trans('saldoperkiraan', 'kodesaldoperkiraan', $kodetrans), 'ubah');
			//cek_periode($tgltrans,'ubah');

			cek_pemakaian_stok($kodetrans);
		}

		// start transaction
		$tr  = $db->start_trans();

		// insert saldostok
		$data_values = array (
			$kodetrans, $_SESSION['KODELOKASI'], $_SESSION['NAMALOKASI'], $gudang, $tgltrans, date("Y.m.d"),
			date("H:i:s"), $keterangan, $_SESSION['user'], 'I', 0
		);
		$exe = $db->insert('saldostok', $data_values, $tr);
		if (!$exe) { $db->rollback($tr); die(json_encode(array('errorMsg' => 'Simpan Data Gagal <br>Kesalahan Pada Header Data Transaksi'))); }

		//insert saldostokdtl
		$sql = $db->insert('saldostokdtl', 9, $tr, true);
		$pr  = $db->prepare($sql, $tr);
		foreach ($a_detail as $item) {
			$data_values = array (
				$kodetrans, $item->kodebarang, $item->namabarang, $item->jml, $item->satuan,
				'', 0, $item->harga, $item->subtotal
			);
			if ($item->jml > 0) {
				$exe = $db->execute($pr, $data_values);
				if (!$exe) { $db->rollback($tr); die(json_encode(array('errorMsg' => 'Simpan Data Gagal<br>Kesalahan Pada Detail Data Transaksi'))); }
			}
		}
		$db->commit($tr);

		// panggil fungsi untuk log history
		log_history(
			$kodetrans,
			'SALDO AWAL STOK',
			$_POST['KODESALDOSTOK']=='' ? 'INSERT' : 'EDIT',
			array(
				array(
					'nama'  => 'header',
					'tabel' => 'saldostok',
					'kode'  => 'KODESALDOSTOK'
				),
				array(
					'nama'  => 'detail',
					'tabel' => 'saldostokdtl',
					'kode'  => 'KODESALDOSTOK'
				),
			),
			$_SESSION['user']
		);

		echo json_encode(array('success' => true));
	break;

	case 'load_data' :
		$kodetrans = $_POST['kodetrans'];

		$rows = array();
		$sql   = "select b.*, c.tipe, c.namabarangsupplier, d.jenisframe
				  from saldostok a
				  inner join saldostokdtl b on a.kodesaldostok=b.kodesaldostok
				  inner join mbarang c on b.kodebarang = c.kodebarang
				  left join mjenisframe d on b.kodebarang = d.kodebarang
				  where a.kodesaldostok = '$kodetrans'
		          order by b.kodebarang";
		$query = $db->query($sql);
		while ($rs = $db->fetch($query)) {
			$rows[] = array(
				'kodebarang' 		 => $rs->KODEBARANG,
				'namabarang' 		 => $rs->NAMABARANG,
				'tipe' 				 => $rs->TIPE,
				'jenisframe'		 => $rs->JENISFRAME,
				'namabarangsupplier' => $rs->NAMABARANGSUPPLIER,
				'satuan'     		 => $rs->SATUAN,
				'jml'        		 => $rs->JML,
				'harga'      		 => $rs->HARGA,
				'subtotal'   		 => $rs->SUBTOTAL,
			);
		}

		echo json_encode(array(
			'success' => true,
			'detail'  => $rows,
		));
	break;

	case 'batal_trans' :
		$kodetrans = $_POST['kodetrans'];
		$status    = get_status('saldostok', 'kodesaldostok', $kodetrans);

		if ($status=='P') die(json_encode(array('errorMsg' => 'Transaksi Tidak Dapat Dibatalkan')));

		//cek_periode(get_tgl_trans('saldostok', 'kodesaldostok', $kodetrans), 'hapus');

		cek_pemakaian_stok($kodetrans);

		$tr = $db->start_trans();
		$query = $db->update('saldostok', array('status' => 'D'), array('kodesaldostok' => $kodetrans), $tr);
		$db->commit($tr);

		// panggil fungsi untuk log history
		log_history(
			$kodetrans,
			'SALDO AWAL STOK',
			'DELETE',
			array(
				array(
					'nama'  => 'header',
					'tabel' => 'saldostok',
					'kode'  => 'KODESALDOSTOK'
				),
				array(
					'nama'  => 'detail',
					'tabel' => 'saldostokdtl',
					'kode'  => 'KODESALDOSTOK'
				),
			),
			$_SESSION['user']
		);

		echo json_encode(array('success' => true));
	break;
}
?>