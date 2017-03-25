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
		$kodetrans    = $_POST['KODETRANSFER'];
		$lokasiasal   = $_POST['KODELOKASIASAL'];
		$gudangasal   = $_POST['KODEGUDANGASAL'];
		$lokasitujuan = $_POST['KODELOKASITUJUAN'];
		$tgltrans     = ubah_tgl_firebird($_POST['TGLTRANS']);
		$Catatan      = $_POST['CATATAN'];

		$a_detail = json_decode($_POST['data_detail']);

		cek_data($a_detail, 'kodebarang', 'mbarang');

		if (count($a_detail)<1) die(json_encode(array('errorMsg' => 'Detail Transaksi Tidak Boleh Kosong')));
		if ($lokasiasal == $lokasitujuan) die(json_encode(array('errorMsg' => 'Lokasi Asal Dan Tujuan Tidak Boleh Sama')));

		cek_valid_data('MLOKASI', 'KODELOKASI', $lokasiasal, 'Lokasi Asal');
		cek_valid_data('MGUDANG', 'KODEGUDANG', $gudangasal, 'Gudang Asal');
		cek_valid_data('MLOKASI', 'KODELOKASI', $lokasitujuan, 'Lokasi Tujuan');

		// pengecekan jika transfer barang rusak, maka lokasi tujuan harus pusat
		$q = $db->query("select jenis from mgudang where kodegudang='$gudangasal'");
		$r = $db->fetch($q);
		if ($r->JENIS == 2) {
			$q = $db->query("select pusat from mlokasi where kodelokasi='$lokasitujuan'");
			$r = $db->fetch($q);
			if ($r->PUSAT == 0) {
				die(json_encode(array('errorMsg' => 'Jika Transfer Barang Rusak, Maka Lokasi Tujuan Harus Pusat/Office')));
			}
		}
		
		// cek SP, apakah sudah pernah ditransfer apa belum.
		if ($_POST['KODETRANSREFERENSI'] <> '') {
			$p = $db->prepare('select kodetransfer from ttransfer where kodetransreferensi=? and status<>?');
			$e = $db->execute($p, array($_POST['KODETRANSREFERENSI'], 'D'));
			$r = $db->fetch($e);
			
			if ($r->KODETRANSFER <> '')
				die(json_encode(array('errorMsg' => 'No SP sudah pernah dilakukan transfer dengan No Transfer \''.$r->KODETRANSFER.'\'')));
		}
		
		$mode = $_POST['mode'];
		if ($mode=='tambah') { // generate kodetrans
			/*$temp_kode = 'TF/'.$lokasiasal.'/';

			$urutan = get_new_urutan('ttransfer', 'kodetransfer', array($temp_kode, substr($tgltrans, 2, 2)));

			$kodetrans = $temp_kode.$urutan.'/'.substr($tgltrans, 5, 2).'/'.substr($tgltrans, 2, 2);*/

			$temp_kode = 'TF/'.$lokasiasal.'/'.substr($tgltrans, 2, 2).substr($tgltrans, 5, 2);

			$urutan = get_max_urutan('ttransfer', 'kodetransfer', $temp_kode, 4);

			$kodetrans = $temp_kode.$urutan;

			cek_periode($tgltrans,'tambahkan');
		} else {
			cek_periode(get_tgl_trans('ttransfer', 'kodetransfer', $kodetrans), 'ubah');
			cek_periode($tgltrans,'ubah');

			cek_pemakaian_stok($kodetrans);
		}

		// start transaction
		$tr  = $db->start_trans();

		// insert ttransfer
		$data_values = array(
			$kodetrans, '', $_POST['KODETRANSREFERENSI'], $lokasiasal, $_POST['NAMALOKASIASAL'],
			$gudangasal, $lokasitujuan, $_POST['NAMALOKASITUJUAN'], $tgltrans, date("Y.m.d"),
			date("H:i:s"), $_SESSION['user'], $Catatan, 'I', 0
		);
		$exe = $db->insert('ttransfer', $data_values, $tr);
		if (!$exe) { $db->rollback($tr); die(json_encode(array('errorMsg' => 'Simpan Data Gagal <br>Kesalahan Pada Header Data Transaksi'))); }

		//insert saldostokdtl
		$i = 0;
		$sql = $db->insert('ttransferdtl', 10, false, true);
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

				// cek stok
				// saya remark karena pengecekan stok ada di waktu sebelum cetak 16.12.05
				//$stok_cukup = cek_stok ($item->kodebarang, $tgltrans, $item->jml, $item->satuan, $lokasiasal, $gudangasal);
				//if (!$stok_cukup) { $db->rollback($tr); die(json_encode(array('errorMsg' => 'stok barang '.$item->kodebarang.' ('.$item->namabarang.') tidak mencukupi'))); }
			}
		}

		$db->commit($tr);

		// panggil fungsi untuk log history
		log_history(
			$kodetrans,
			'TRANSFER',
			$_POST['KODETRANSFER']=='' ? 'INSERT' : 'EDIT',
			array(
				array(
					'nama' => 'header',
					'tabel' => 'ttransfer',
					'kode' => 'KODETRANSFER'
				),
				array(
					'nama' => 'detail',
					'tabel' => 'ttransferdtl',
					'kode' => 'KODETRANSFER'
				),
			),
			$_SESSION['user']
		);

		echo json_encode(array('success' => true, 'kodetrans'=>$kodetrans));
	break;

	case 'load_data' :
		$kodetrans = $_POST['kodetrans'];

		$rows = array();
		$sql   = "select b.*, c.namabarang as namabarang2, c.namabarangsupplier, c.tipe, c.hargajual, d.jenisframe
				  from ttransfer a
				  inner join ttransferdtl b on a.kodetransfer=b.kodetransfer
				  inner join mbarang c on b.kodebarang = c.kodebarang
				  left join mjenisframe d on b.kodebarang = d.kodebarang
				  where a.kodetransfer = '$kodetrans'
		          order by kodebarang";
		$query = $db->query($sql);
		while ($rs = $db->fetch($query)) {
			$rows[] = array(
				'kodebarang'	=> $rs->KODEBARANG,
				'namabarang'    => $rs->NAMABARANG2,
				'namabarangsupplier' => $rs->NAMABARANGSUPPLIER,
				'tipe'    		=> $rs->TIPE,
				'jenisframe'	=> $rs->JENISFRAME,
				'satuan'        => $rs->SATUAN,
				'jml'           => $rs->JML,
				'harga'       	=> $rs->HARGA,
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
		$status    = get_status('ttransfer', 'kodetransfer', $kodetrans);

		if ($status=='P') die(json_encode(array('errorMsg' => 'Transaksi Tidak Dapat Dibatalkan')));

		cek_periode(get_tgl_trans('ttransfer', 'kodetransfer', $kodetrans), 'hapus');

		//cek_pemakaian_stok($kodetrans);

		$tr = $db->start_trans();
		$query = $db->update('ttransfer', array('status' => 'D'), array('kodetransfer' => $kodetrans), $tr);
		$db->commit($tr);

		// panggil fungsi untuk log history
		log_history(
			$kodetrans,
			'TRANSFER',
			'DELETE',
			array(
				array(
					'nama' => 'header',
					'tabel' => 'ttransfer',
					'kode' => 'KODETRANSFER'
				),
				array(
					'nama' => 'detail',
					'tabel' => 'ttransferdtl',
					'kode' => 'KODETRANSFER'
				),
			),
			$_SESSION['user']
		);

		echo json_encode(array(
			'success' => true,
		));
	break;

	case 'load_data_pr' :
		$kodetrans = $_POST['kodetrans'];

		$rows = array();
		$sql   = "select b.*, c.namabarang, c.namabarangsupplier, c.hargajual, c.tipe, d.jenisframe
				  from tpr a
				  inner join tprdtl b on a.kodepr=b.kodepr
				  inner join mbarang c on b.kodebarang = c.kodebarang
				  left join mjenisframe d on b.kodebarang = d.kodebarang
				  where a.kodepr = '$kodetrans'
		          order by kodebarang";
		$query = $db->query($sql);
		while ($rs = $db->fetch($query)) {
			$stok = 0;
			if ($_POST['tampil_stok']) {
				$pr = $db->prepare('execute procedure GET_SALDOSTOK(?, ?, ?, ?)');
				$ex = $db->execute($pr, array($rs->KODEBARANG, $_SESSION['KODELOKASI'], '', $_POST['tgltrans']));
				$r = $db->fetch($ex);
				$stok = $r->SALDO;
			}
			
			$rows[] = array(
				'kodebarang'	=> $rs->KODEBARANG,
				'namabarang'    => $rs->NAMABARANG,
				'namabarangsupplier' => $rs->NAMABARANGSUPPLIER,
				'tipe'		    => $rs->TIPE,
				'jenisframe'    => $rs->JENISFRAME,
				'satuan'        => $rs->SATUAN,
				'jmlstok'		=> $stok,
				'jml'           => $rs->JML,
				'harga'         => 0,
				'subtotal'      => 0,
				'hargajual'     => $rs->HARGAJUAL,
			);
		}

		echo json_encode(array(
			'success' => true,
			'detail' => $rows,
		));
	break;

	// case ini berfungsi untuk mengecek stok sebelum tombol print di tekan
	case 'cek_stok' :
		$kodetrans = $_POST['kodetrans'];

		$sql   = "select a.tgltrans, b.kodebarang, c.namabarang, b.jml, b.satuan, a.kodelokasiasal, a.kodegudangasal
				  from ttransfer a
				  inner join ttransferdtl b on a.kodetransfer=b.kodetransfer
				  inner join mbarang c on b.kodebarang = c.kodebarang
				  where a.kodetransfer = '$kodetrans'
		          order by b.kodebarang";
		$query = $db->query($sql);
		while ($rs = $db->fetch($query)) {
			// cek stok
			$stok_cukup = cek_stok ($rs->KODEBARANG, $rs->TGLTRANS, $rs->JML, $rs->SATUAN, $rs->KODELOKASIASAL, $rs->KODEGUDANGASAL);
			if (!$stok_cukup) { die(json_encode(array('errorMsg' => 'stok barang '.$rs->KODEBARANG.' ('.$rs->NAMABARANG.') tidak mencukupi'))); }
		}

		echo json_encode(array('success' => true));
	break;
}
?>