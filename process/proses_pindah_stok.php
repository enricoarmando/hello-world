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
		$kodetrans  = $_POST['KODETRANSFERGUDANG'];
		$lokasi  	= $_SESSION['KODELOKASI'];
		$gudang     = $_POST['KODEGUDANG'];
		$tgltrans   = ubah_tgl_firebird($_POST['TGLTRANS']);
		$keterangan = $_POST['CATATAN'];

		$a_detail = json_decode($_POST['data_detail']);

		if (count($a_detail)<1) die(json_encode(array('errorMsg' => 'Please Insert Detail Transaction')));

		cek_valid_data('MGUDANG', 'KODEGUDANG', $gudang, 'Gudang');

		$mode = $_POST['mode'];
		if ($mode=='tambah') { // generate kodetrans
			/*$temp_kode = 'TPB/'.$lokasiasal.'/';

			$urutan = get_new_urutan('TPINDAHBARANG', 'KODEPINDAH', array($temp_kode, substr($tgltrans, 2, 2)), 4);

			$kodetrans = $temp_kode.$urutan.'/'.substr($tgltrans, 5, 2).'/'.substr($tgltrans, 2, 2);*/

			$temp_kode = 'TPB/'.$lokasi.'/'.substr($tgltrans, 2, 2).substr($tgltrans, 5, 2);

			$urutan = get_max_urutan('tpindahbarang', 'kodepindah', $temp_kode, 4);

			$kodetrans = $temp_kode.$urutan;

			cek_periode($tgltrans,'tambahkan');
		} else {
			//cek tgl asal mungkin dilakukan perubahan tanggal
			cek_periode(get_tgl_trans('tpindahbarang', 'kodepindah', $kodetrans), 'ubah');
			cek_periode($tgltrans,'ubah');
		}
		// start transaction
		$tr  = $db->start_trans();

		// insert saldostok
		$data_values = array(
			$kodetrans, $lokasi, $gudang, $tgltrans, date("Y.m.d"), date("H:i:s"), $_SESSION['user'],
			$keterangan, 'I', 0
		);
		$exe = $db->insert('tpindahbarang', $data_values, $tr);
		if (!$exe) { $db->rollback($tr); die(json_encode(array('errorMsg' => 'Transaksi Gagal <br>Harap Cek Data Header Transaksi'))); }

		//insert saldostokdtl
		$i = 0;
		$sql = $db->insert('tpindahbarangdtl', 8, $tr, true);
		$pr  = $db->prepare($sql, $tr);
		foreach ($a_detail as $item) {
			$data_values = array(
				$kodetrans, $item->kodebaranglama, $item->kodebarangbaru, $i, $item->jml,
				$item->satuan, 0, 0
			);

			if ($item->jml > 0) {
				$exe = $db->execute($pr, $data_values);
				if (!$exe) { $db->rollback($tr); die(json_encode(array('errorMsg' => 'Transaksi Gagal <br>Harap Cek Data Detail Transaksi'))); }
				$i++;

				// cek stok
				$stok_cukup = cek_stok ($item->kodebaranglama, $tgltrans, $item->jml, $item->satuan, $lokasi, $gudang);
				if (!$stok_cukup) { $db->rollback($tr); die(json_encode(array('errorMsg' => 'stok barang '.$item->kodebaranglama.' ('.$item->namabaranglama.') tidak mencukupi'))); }
			}
		}
		$db->commit($tr);

		// panggil fungsi untuk log history
		log_history(
			$kodetrans,
			'PINDAH BARANG',
			$_POST['KODEPINDAH']=='' ? 'INSERT' : 'EDIT',
			array(
				array(
					'nama'  => 'header',
					'tabel' => 'tpindahbarang',
					'kode'  => 'kodepindah'
				),
				array(
					'nama'  => 'detail',
					'tabel' => 'tpindahbarangdtl',
					'kode'  => 'kodepindah'
				),
			),
			$_SESSION['user']
		);

		echo json_encode(array('success' => true));
	break;

	case 'load_data' :
		$kodetrans = $_POST['kodetrans'];

		$rows = array();
		$sql   = "select b.*, c.namabarang as namabaranglama, c.tipe as tipelama,
					     d.namabarang as namabarangbaru, d.tipe as tipebaru,
						 e.jenisframe as jenisframelama, f.jenisframe as jenisframebaru
				  from tpindahbarang a
				  inner join tpindahbarangdtl b on a.kodepindah = b.kodepindah
				  inner join mbarang c on b.kodebaranglama = c.kodebarang
				  inner join mbarang d on b.kodebarangbaru = d.kodebarang
				  left join mjenisframe e on c.kodebarang = e.kodebarang
				  left join mjenisframe f on d.kodebarang = f.kodebarang
				  where a.kodepindah = '$kodetrans'
		          order by kodebaranglama";

		$query = $db->query($sql);
		while ($rs = $db->fetch($query)) {
			$rows[] = array(
				'kodebaranglama' => $rs->KODEBARANGLAMA,
				'namabaranglama' => $rs->NAMABARANGLAMA,
				'tipelama'       => $rs->TIPELAMA,
				'jenisframelama' => $rs->JENISFRAMELAMA,
				'kodebarangbaru' => $rs->KODEBARANGBARU,
				'namabarangbaru' => $rs->NAMABARANGBARU,
				'tipebaru'       => $rs->TIPEBARU,
				'jenisframebaru' => $rs->JENISFRAMEBARU,
				'jml'            => $rs->JML,
				'satuan'         => $rs->SATUAN,
			);
		}

		echo json_encode(array(
			'success' => true,
			'detail' => $rows,
		));
	break;

	case 'batal_trans' :
		$kodetrans = $_POST['kodetrans'];
		$status    = get_status('tpindahbarang', 'kodepindah', $kodetrans);

		if ($status=='P') die(json_encode(array('errorMsg' => 'Transaksi Tidak Dapat Dibatalkan')));

		cek_periode(get_tgl_trans('tpindahbarang', 'kodepindah', $kodetrans), 'hapus');
		
		cek_pemakaian_stok($kodetrans);

		$tr = $db->start_trans();
		$query = $db->update('tpindahbarang', array('status' => 'D'), array('kodepindah' => $kodetrans), $tr);
		$db->commit($tr);

		// panggil fungsi untuk log history
		log_history(
			$kodetrans,
			'PINDAH BARANG',
			'DELETE',
			array(
				array(
					'nama'  => 'header',
					'tabel' => 'tpindahbarang',
					'kode'  => 'kodepindah'
				),
				array(
					'nama'  => 'detail',
					'tabel' => 'tpindahbarangdtl',
					'kode'  => 'kodepindah'
				),
			),
			$_SESSION['user']
		);

		echo json_encode(array('success' => true));
	break;
}
?>