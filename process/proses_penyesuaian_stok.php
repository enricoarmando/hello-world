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
		$kodetrans    = $_POST['KODEPENYESUAIAN'];
		$kodeopname   = $_POST['KODEOPNAME'];
		$lokasi       = $_SESSION['KODELOKASI'];//$_POST['KODELOKASI'];
		$gudang       = $_POST['KODEGUDANG'];//$_POST['KODELOKASI'];
		$tgltrans     = ubah_tgl_firebird($_POST['TGLTRANS']);
		$keterangan   = $_POST['CATATAN'];

		$a_detail = json_decode($_POST['data_detail']);

		cek_data($a_detail, 'kodebarang', 'mbarang');

		if (count($a_detail)<1) die(json_encode(array('errorMsg' => 'Detail Transaksi Tidak Boleh Kosong')));

		cek_valid_data('MGUDANG', 'kodegudang', $gudang, 'Gudang');

		$mode = $_POST['mode'];
		if ($mode=='tambah') { // generate kodetrans
			/*$temp_kode = 'ADJ/'.$_SESSION['KODELOKASI'].'/';

			$urutan = get_new_urutan('tpenyesuaian', 'kodepenyesuaian', array($temp_kode, substr($tgltrans, 2, 2)), 3);

			$kodetrans = $temp_kode.$urutan.'/'.substr($tgltrans, 5, 2).'/'.substr($tgltrans, 2, 2);*/

			$temp_kode = 'ADJ/'.$_SESSION['KODELOKASI'].'/'.substr($tgltrans, 2, 2).substr($tgltrans, 5, 2);

			$urutan = get_max_urutan('tpenyesuaian', 'kodepenyesuaian', $temp_kode, 4);

			$kodetrans = $temp_kode.$urutan;

			cek_periode($tgltrans,'tambahkan');
		} else {
			//cek tgl asal mungkin dilakukan perubahan tanggal
			cek_periode(get_tgl_trans('tpenyesuaian', 'kodepenyesuaian', $kodetrans), 'ubah');
			cek_periode($tgltrans,'ubah');

			cek_pemakaian_stok($kodetrans);
		}

		// start transaction
		$tr  = $db->start_trans();

		// insert tpenyesuaian
		$data_values = array(
			$kodetrans, $kodeopname, $lokasi, $_SESSION['NAMALOKASI'], $gudang,
			$tgltrans, date("Y.m.d"), date("H:i:s"), $_SESSION['user'], $keterangan, 'I', 0
		);
		$exe = $db->insert('tpenyesuaian', $data_values, $tr);
		if (!$exe) { $db->rollback($tr); die(json_encode(array('errorMsg' => 'Simpan Data Gagal <br>Kesalahan Pada Header Data Transaksi'))); }

		//insert tpenyesuaiandtl
		$i = 0;
		$sql = $db->insert('tpenyesuaiandtl', 11, $tr, true);
		$pr  = $db->prepare($sql, $tr);
		foreach ($a_detail as $item) {
			$data_values = array (
				$kodetrans, $item->kodebarang, $i, $item->namabarang, $item->jml,
				$item->selisih, $item->satuan, '', 0, $item->harga, $item->subtotal
			);
			if ($item->selisih <> 0) {
				$exe = $db->execute($pr, $data_values);
				if (!$exe) { $db->rollback($tr); die(json_encode(array('errorMsg' => 'Simpan Data Gagal<br>Kesalahan Pada Detail Data Transaksi'))); }
				$i++;
				
				if ($item->selisih < 0) {
					// cek stok
					$stok_cukup = cek_stok ($item->kodebarang, $tgltrans, $item->selisih, $item->satuan);
					if (!$stok_cukup) { $db->rollback($tr); die(json_encode(array('errorMsg' => 'stok barang '.$item->kodebarang.' ('.$item->namabarang.') tidak mencukupi'))); }
				}
			}
		}
		$db->commit($tr);

		// panggil fungsi untuk log history
		log_history(
			$kodetrans,
			'PENYESUAIAN STOK',
			$_POST['KODEPENYESUAIAN']=='' ? 'INSERT' : 'EDIT',
			array(
				array(
					'nama'  => 'header',
					'tabel' => 'tpenyesuaian',
					'kode'  => 'KODEPENYESUAIAN'
				),
				array(
					'nama'  => 'detail',
					'tabel' => 'tpenyesuaiandtl',
					'kode'  => 'KODEPENYESUAIAN'
				),
			),
			$_SESSION['user']
		);

		echo json_encode(array('success' => true));
	break;

	case 'load_data' :
		$kodetrans = $_POST['kodetrans'];

		$rows = array();
		$sql  = "select b.*, c.tipe, c.namabarangsupplier, d.jenisframe
				 from tpenyesuaian a
				 inner join tpenyesuaiandtl b on a.kodepenyesuaian=b.kodepenyesuaian
				 inner join mbarang c on b.kodebarang=c.kodebarang
				 left join mjenisframe d on b.kodebarang = d.kodebarang
				 where a.kodepenyesuaian = '$kodetrans'
		         order by b.urutan";
		$query = $db->query($sql);
		while ($rs = $db->fetch($query)) {
			$rows[] = array(
				'kodebarang'         => $rs->KODEBARANG,
				'namabarang'         => $rs->NAMABARANG,
				'tipe'               => $rs->TIPE,
				'jenisframe'         => $rs->JENISFRAME,
				'namabarangsupplier' => $rs->NAMABARANGSUPPLIER,
				'satuan'             => $rs->SATUAN,
				'jml'                => $rs->JML,
				'selisih'            => $rs->SELISIH,
				'harga'              => $rs->HARGA,
				'subtotal'           => $rs->SELISIH * $rs->HARGA
			);
		}

		echo json_encode(array(
			'success' => true,
			'detail' => $rows,
		));
	break;

	case 'batal_trans' :
		$kodetrans = $_POST['kodetrans'];
		$status    = get_status('tpenyesuaian', 'kodepenyesuaian', $kodetrans);

		if ($status=='P') die(json_encode(array('errorMsg' => 'Transaksi Tidak Dapat Dibatalkan')));

		cek_periode(get_tgl_trans('tpenyesuaian', 'kodepenyesuaian', $kodetrans), 'hapus');

		cek_pemakaian_stok($kodetrans);

		$tr = $db->start_trans();
		$query = $db->update('tpenyesuaian', array('status' => 'D'), array('kodepenyesuaian' => $kodetrans), $tr);
		$db->commit($tr);

		// panggil fungsi untuk log history
		log_history(
			$kodetrans,
			'PENYESUAIAN STOK',
			'DELETE',
			array(
				array(
					'nama'  => 'header',
					'tabel' => 'tpenyesuaian',
					'kode'  => 'KODEPENYESUAIAN'
				),
				array(
					'nama'  => 'detail',
					'tabel' => 'tpenyesuaiandtl',
					'kode'  => 'KODEPENYESUAIAN'
				),
			),
			$_SESSION['user']
		);

		echo json_encode(array(
			'success' => true,
		));
	break;

	case 'load_data_opname' :
		$kodetrans = $_POST[kodetrans]=='' ? $_GET[kodetrans] : $_POST[kodetrans];

		$sql = "select a.kodelokasi, a.kodegudang, a.TglTrans, b.*, c.tipe,
					   c.namabarangsupplier, d.jenisframe
				from topnamestok a
				inner join topnamestokdtl b on a.kodeopname=b.kodeopname
				inner join mbarang c on b.kodebarang=c.kodebarang
				left join mjenisframe d on b.kodebarang = d.kodebarang
				where a.kodeopname='$kodetrans'";

		$items = array();
		$query = $db->query($sql);
		while ($rs = $db->fetch($query)) {
			$pr   = "select saldo from get_saldostok('$rs->KODEBARANG','$rs->KODELOKASI','$rs->KODEGUDANG','$rs->TGLTRANS')";
			$query2 = $db->query($pr);
			$r = $db->fetch($query2);

			$pr2  = "select konversi, satuanutama from get_konversi_satuanutama('$rs->KODEBARANG','$rs->SATUAN')";
			$exe2 = $db->query($pr2);
			$r2   = $db->fetch($exe2);

			$konversi = $r2->KONVERSI==0 ? 1 : $r2->KONVERSI;

			$saldo = $r->SALDO/$konversi;
			$jml = $rs->JML;
			$selisih = $saldo!=$jml ? ($jml-$saldo) : 0;

			$items[] = array(
				'kodebarang'         => $rs->KODEBARANG,
				'namabarang'         => $rs->NAMABARANG,
				'tipe'               => $rs->TIPE,
				'jenisframe'         => $rs->JENISFRAME,
				'namabarangsupplier' => $rs->NAMABARANGSUPPLIER,
				'satuan'             => $rs->SATUAN,
				'jml'                => $rs->JML,
				'selisih'            => $selisih,
				'harga'              => 0,
				'subtotal'           => 0,
			);
		}
		echo json_encode($items);
	break;
}
?>