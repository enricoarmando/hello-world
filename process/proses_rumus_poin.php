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
		$kodetrans    = $_POST['KODERUMUSPOIN'];
		$tgltrans     = ubah_tgl_firebird($_POST['TGLTRANS']);
		$keterangan   = $_POST['KETERANGAN'];

		// DETIL BERISI FRAME, SUNGLASS, SOFTLENS
		$a_detail  = json_decode($_POST['data_detail']);

		// DETIL BERISI LENSA
		$a_detail2 = json_decode($_POST['data_detail2']);

		//cek_data($a_detail, 'kodebarang', 'mbarang');

		//if (count($a_detail)<1) die(json_encode(array('errorMsg' => 'Detail Transaksi Tidak Boleh Kosong')));

		//cek_valid_data('MGUDANG', 'kodegudang', $gudang, 'Gudang');

		$mode = $_POST['mode'];
		if ($mode=='tambah') { // generate kodetrans
			if ($_SESSION['JENISLOKASI'] == 'INDRA OPTIK')
				$jenislokasi = 'IO';
			else
				$jenislokasi = 'SE';

			$temp_kode = 'RMP/'.$jenislokasi.'/'.substr($tgltrans, 2, 2).substr($tgltrans, 5, 2).substr($tgltrans, -2);

			//$urutan = get_max_urutan('mrumuspoin', 'koderumuspoin', $temp_kode, 2);

			$kodetrans = $temp_kode;//.$urutan;

			//cek_periode($tgltrans,'tambahkan');
		} else {
			//cek tgl asal mungkin dilakukan perubahan tanggal
			//cek_periode(get_tgl_trans('mrumuspoin', 'koderumuspoin', $kodetrans), 'ubah');
			//cek_periode($tgltrans,'ubah');
		}

		// start transaction
		$tr  = $db->start_trans();

		// insert mrumuspoin
		$data_values = array(
			$kodetrans, $_SESSION['JENISLOKASI'], $tgltrans, date("Y.m.d"), date("H:i:s"), $_SESSION['user'], $keterangan, 'I'
		);
		$exe = $db->insert('mrumuspoin', $data_values, $tr);
		if (!$exe) { $db->rollback($tr); die(json_encode(array('errorMsg' => 'Simpan Data Gagal <br>Kesalahan Pada Header Data Transaksi'))); }

		//insert mrumuspoindtl
		$i = 0;
		$sql = $db->insert('mrumuspoindtl', 8, $tr, true);
		$pr  = $db->prepare($sql, $tr);

		if ($a_detail > 0) {
			foreach ($a_detail as $item) {
				$data_values = array (
					$kodetrans, $item->jenisbarang, '',  $item->rangeawal, $item->rangeakhir,
					$item->poin, $item->persentase, $i
				);
				$exe = $db->execute($pr, $data_values);
				if (!$exe) { $db->rollback($tr); die(json_encode(array('errorMsg' => 'Simpan Data Gagal<br>Kesalahan Pada Detail Data Transaksi 1'))); }
				$i++;
			}
		}

		if ($a_detail2 > 0) {
			foreach ($a_detail2 as $item) {
				$data_values = array (
					$kodetrans, '', $item->kodegroup, 0, 0,
					$item->poin, 0, $i
				);
				$exe = $db->execute($pr, $data_values);
				if (!$exe) { $db->rollback($tr); die(json_encode(array('errorMsg' => 'Simpan Data Gagal<br>Kesalahan Pada Detail Data Transaksi 2'))); }
				$i++;
			}
		}
		$db->commit($tr);

		// panggil fungsi untuk log history
		log_history(
			$kodetrans,
			'RUMUS POIN',
			$_POST['koderumuspoin']=='' ? 'INSERT' : 'EDIT',
			array(
				array(
					'nama'  => 'header',
					'tabel' => 'mrumuspoin',
					'kode'  => 'koderumuspoin'
				),
				array(
					'nama'  => 'detail',
					'tabel' => 'mrumuspoindtl',
					'kode'  => 'koderumuspoin'
				),
			),
			$_SESSION['user']
		);

		echo json_encode(array('success' => true));
	break;

	case 'load_data' :
		$kodetrans = $_POST['kodetrans'];

		$rows = array();
		$rows2 = array();
		$sql  = "select b.*
				 from mrumuspoin a
				 inner join mrumuspoindtl b on a.koderumuspoin=b.koderumuspoin
				 where a.koderumuspoin = '$kodetrans'
		         order by b.urutan";
		$query = $db->query($sql);
		while ($rs = $db->fetch($query)) {
			if ($rs->JENISBARANG <> '') {
				$rows[] = array(
					'jenisbarang'	=> $rs->JENISBARANG,
					'rangeawal'		=> $rs->RANGEAWAL,
					'rangeakhir'	=> $rs->RANGEAKHIR,
					'poin'			=> $rs->POIN,
					'persentase'	=> $rs->PERSENTASE,
				);
			} else {
				$rows2[] = array(
					'kodegroup'	=> $rs->KODEGROUP,
					'poin'		=> $rs->POIN,
				);
			}
		}

		echo json_encode(array(
			'success' => true,
			'detail' => $rows,
			'detail2' => $rows2,
		));
	break;

	case 'batal_trans' :
		$kodetrans = $_POST['kodetrans'];
		$status    = get_status('mrumuspoin', 'koderumuspoin', $kodetrans);

		if ($status=='P') die(json_encode(array('errorMsg' => 'Transaksi Tidak Dapat Dibatalkan')));

		//cek_periode(get_tgl_trans('mrumuspoin', 'koderumuspoin', $kodetrans), 'hapus');

		$tr = $db->start_trans();
		$query = $db->update('mrumuspoin', array('status' => 'D'), array('koderumuspoin' => $kodetrans), $tr);
		$db->commit($tr);

		// panggil fungsi untuk log history
		log_history(
			$kodetrans,
			'RUMUS POIN',
			'DELETE',
			array(
				array(
					'nama'  => 'header',
					'tabel' => 'mrumuspoin',
					'kode'  => 'koderumuspoin'
				),
				array(
					'nama'  => 'detail',
					'tabel' => 'mrumuspoindtl',
					'kode'  => 'koderumuspoin'
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