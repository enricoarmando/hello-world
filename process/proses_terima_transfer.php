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
		$kodetrans    = $_POST['KODETERIMATRANSFER'];
		$lokasiasal   = $_POST['KODELOKASIASAL'];
		$lokasitujuan = $_POST['KODELOKASITUJUAN'];
		$gudangtujuan = $_POST['KODEGUDANGTUJUAN'];
		$notransfer   = $_POST['KODETRANSREFERENSI'];
		$tgltrans     = ubah_tgl_firebird($_POST['TGLTRANS']);
		$Catatan      = $_POST['CATATAN'];

		$a_detail = json_decode($_POST['data_detail']);

		cek_data($a_detail, 'kodebarang', 'mbarang');

		if (count($a_detail)<1) die(json_encode(array('errorMsg' => 'Detail Transaksi Tidak Boleh Kosong')));
		if ($lokasiasal == $lokasitujuan) die(json_encode(array('errorMsg' => 'Lokasi Asal Dan Tujuan Tidak Boleh Sama')));

		cek_valid_data('MLOKASI', 'KODELOKASI', $lokasiasal, 'Lokasi Asal');
		cek_valid_data('MLOKASI', 'KODELOKASI', $lokasitujuan, 'Lokasi Tujuan');
		cek_valid_data('MGUDANG', 'KODEGUDANG', $gudangtujuan, 'Gudang Tujuan');

		// pengecekan jika transfer barang rusak, maka lokasi tujuan harus pusat
		$q = $db->query("select b.jenis from ttransfer a inner join mgudang b on a.kodegudangasal = b.kodegudang where a.kodetransfer='$notransfer'");
		$r = $db->fetch($q);
		if ($r->JENIS == 2) {
			$q = $db->query("select jenis from mgudang where kodegudang='$gudangtujuan'");
			$r = $db->fetch($q);
			if ($r->JENIS == 1) {
				die(json_encode(array('errorMsg' => 'No Transfer merupakan transfer barang rusak, Gudang Tujuan harus Gudang Barang Rusak')));
			}
		}

		$mode = $_POST['mode'];
		if ($mode=='tambah') { // generate kodetrans
			/*$temp_kode = 'TT/'.$lokasiasal.'/';

			$urutan = get_new_urutan('tterimatransfer', 'kodeterimatransfer', array($temp_kode, substr($tgltrans, 2, 2)));

			$kodetrans = $temp_kode.$urutan.'/'.substr($tgltrans, 5, 2).'/'.substr($tgltrans, 2, 2);*/

			$temp_kode = 'TT/'.$lokasitujuan.'/'.substr($tgltrans, 2, 2).substr($tgltrans, 5, 2);

			$urutan = get_max_urutan('tterimatransfer', 'kodeterimatransfer', $temp_kode, 4);

			$kodetrans = $temp_kode.$urutan;

			cek_periode($tgltrans,'tambahkan');
		} else {
			cek_periode(get_tgl_trans('tterimatransfer', 'kodeterimatransfer', $kodetrans), 'ubah');
			cek_periode($tgltrans,'ubah');
		}

		// start transaction
		$tr  = $db->start_trans();

		// insert tterimatransfer
		$data_values = array(
			$kodetrans, '', $notransfer, $lokasiasal, $_POST['NAMALOKASIASAL'],
			$lokasitujuan, $_POST['NAMALOKASITUJUAN'], $gudangtujuan, $tgltrans, date("Y.m.d"), date("H:i:s"),
			$_SESSION['user'], $Catatan, 'I', 0
		);
		$exe = $db->insert('tterimatransfer', $data_values, $tr);
		if (!$exe) { $db->rollback($tr); die(json_encode(array('errorMsg' => 'Simpan Data Gagal <br>Kesalahan Pada Header Data Transaksi'))); }

		//insert saldostokdtl
		$i = 0;
		$sql = $db->insert('tterimatransferdtl', 10, false, true);
		$pr  = $db->prepare($sql, $tr);
		foreach ($a_detail as $item) {
			$data_values = array (
				$kodetrans, $item->kodebarang, $i, $item->namabarang, $item->jml,
				$item->harga, $item->subtotal, $item->satuan, '', 0,
			);
			if ($item->jml > 0) {
				$exe = $db->execute($pr, $data_values);
				if (!$exe) { $db->rollback($tr); die(json_encode(array('errorMsg' => 'Simpan Data Gagal <br>Kesalahan Pada Detail Data Transaksi'))); }
				$i++;
			}
		}
		$db->commit($tr);

		$tr = $db->start_trans();
		$db->update('tterimatransfer', array('status' => 'S'), array('kodeterimatransfer' => $kodetrans), $tr);
		$db->commit($tr);

		// panggil fungsi untuk log history
		log_history(
			$kodetrans,
			'TERIMA TRANSFER',
			$_POST['KODETERIMATRANSFER']=='' ? 'INSERT' : 'EDIT',
			array(
				array(
					'nama'  => 'header',
					'tabel' => 'tterimatransfer',
					'kode'  => 'KODETERIMATRANSFER'
				),
				array(
					'nama'  => 'detail',
					'tabel' => 'tterimatransferdtl',
					'kode'  => 'KODETERIMATRANSFER'
				),
			),
			$_SESSION['user']
		);

		echo json_encode(array('success' => true));
	break;

	case 'load_data' :
		$kodetrans = $_POST['kodetrans'];

		$rows = array();
		$sql   = "select b.*, c.tipe, c.hargajual, d.jenisframe
				  from tterimatransfer a
				  inner join tterimatransferdtl b on a.kodeterimatransfer=b.kodeterimatransfer
				  inner join mbarang c on b.kodebarang = c.kodebarang
				  left join mjenisframe d on b.kodebarang = d.kodebarang
				  where a.kodeterimatransfer = '$kodetrans'
		          order by kodebarang";
		$query = $db->query($sql);
		while ($rs = $db->fetch($query)) {
			$rows[] = array(
				'kodebarang' => $rs->KODEBARANG,
				'namabarang' => $rs->NAMABARANG,
				'tipe'    	 => $rs->TIPE,
				'jenisframe' => $rs->JENISFRAME,
				'satuan'     => $rs->SATUAN,
				'jml'        => $rs->JML,
				'harga'      => $rs->HARGA,
				'subtotal'   => $rs->SUBTOTAL,
				'hargajual'  => $rs->HARGAJUAL
			);
		}

		echo json_encode(array(
			'success' => true,
			'detail' => $rows,
		));
	break;

	case 'batal_trans' :
		$kodetrans = $_POST['kodetrans'];
		$status    = get_status('tterimatransfer', 'kodeterimatransfer', $kodetrans);

		if ($status=='P') die(json_encode(array('errorMsg' => 'Transaksi Tidak Dapat Dibatalkan')));

		cek_periode(get_tgl_trans('tterimatransfer', 'kodeterimatransfer', $kodetrans), 'hapus');

		// mendapatkan no transfer
		$q = $db->select('tterimatransfer', array('KODETRANSREFERENSI'), array('kodeterimatransfer'=>$kodetrans));
		$r = $db->fetch($q);

		cek_pemakaian_stok($kodetrans);

		$tr = $db->start_trans();
		$query = $db->update('tterimatransfer', array('status' => 'D'), array('kodeterimatransfer' => $kodetrans), $tr);
		$db->commit($tr);

		// panggil fungsi untuk log history
		log_history(
			$kodetrans,
			'TERIMA TRANSFER',
			'DELETE',
			array(
				array(
					'nama'  => 'header',
					'tabel' => 'tterimatransfer',
					'kode'  => 'KODETERIMATRANSFER'
				),
				array(
					'nama'  => 'detail',
					'tabel' => 'tterimatransferdtl',
					'kode'  => 'KODETERIMATRANSFER'
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