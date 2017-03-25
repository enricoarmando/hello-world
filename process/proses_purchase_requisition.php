<?php
session_start();
ob_start("ob_gzhandler");
date_default_timezone_set("Asia/Jakarta");

if (empty($_SESSION['user'])) die(json_encode(array('errorMsg' => 'Expired Session <br> Please Relogin')));

include "../../config/koneksi.php";
include "../../config/function.php";

$act = isset($_REQUEST['act']) ? $_REQUEST['act'] : '';

//$db = new DB;

switch ($act) {
	case 'simpan_trans' :
		$a_detail  = json_decode($_POST['data_detail']);

		cek_data($a_detail, 'kodebarang', 'mbarang');

		$kodetrans    	 = $_POST['KODEPR'];
		$kode_so    	 = $_POST['KODESO'];
		$lokasi       	 = $_SESSION['KODELOKASI'];
		$namalokasi   	 = $_SESSION['NAMALOKASI'];
		$customer     	 = $_POST['KODECUSTOMER'];
		$namacustomer 	 = $_POST['NAMACUSTOMER'];
		$syaratbayar     = 'SB001'; //$_POST['KODESYARATBAYAR'];
		$namasyaratbayar = $_POST['NAMASYARATBAYAR'];
		$catatan         = $_POST['CATATAN'];
		$tgltrans        = $_POST['TGLTRANS'];

		if (count($a_detail)<1) die(json_encode(array('errorMsg' => 'Detail Transaksi Tidak Boleh Kosong')));

		//cek_valid_data('MLOKASI', 'KODELOKASI', $lokasi, 'Location');
		//cek_valid_data('MSYARATBAYAR', 'KODESYARATBAYAR', $syaratbayar, 'Term Payment');
		//cek_valid_data('MCUSTOMER', 'KODECUSTOMER', $customer, 'Customer');
		//cek_valid_data('MMARKETING', 'KODEMARKETING', $_POST['KODEMARKETING'], 'Marketing');

		// cek jangan sampai kodeso dipakai 2x
		if ($kode_so <> ''){
			$sql = 'select kodeso, kodepr from tpr where kodeso=? and status<>? and kodepr<>?';
			$pr = $db->prepare($sql);
			$ex = $db->execute($pr, array($kode_so, 'D', $kodetrans));
			$rs = $db->fetch($ex);

			if ($rs->KODEPR <> '')
				die(json_encode(array('errorMsg' => 'No Sales Order Sudah Dipakai di Surat Pesanan dg no '.$rs->KODEPR)));
		}


		$mode = $_POST['mode'];
		if ($mode=='tambah') {
			/*$temp_kode = 'SP/'.$_SESSION['KODELOKASI'].'/';

			$urutan = get_new_urutan('tpr', 'kodepr', array($temp_kode, substr($tgltrans, 2, 2)));

			$kodetrans = $temp_kode.$urutan.'/'.substr($tgltrans, 5, 2).'/'.substr($tgltrans, 2, 2);*/

			$temp_kode = 'SP/'.$_SESSION['KODELOKASI'].'/'.substr($tgltrans, 2, 2).substr($tgltrans, 5, 2);

			$urutan = get_max_urutan('tpr', 'kodepr', $temp_kode, 4);

			$kodetrans = $temp_kode.$urutan;

			// cek gambar
			if ($_FILES["FILEGAMBAR"]['name'] == '')
				$gambar = '';
			//die(json_encode(array('errorMsg' => 'Gambar belum dipilih')));

			cek_periode($tgltrans,'tambahkan');
		} else {
			$gambar = $_POST['GAMBAR'];
			cek_periode(get_tgl_trans('tpr', 'kodepr', $kodetrans), 'ubah');
			cek_periode($tgltrans,'ubah');
		}

		if ($_FILES["FILEGAMBAR"]['name'] != '') {
			// upload gambar
			$target_dir = "../../gambar-lensa/";
			$uploadOk = 1;
			$imageFileType = pathinfo($_FILES['FILEGAMBAR']['name'], PATHINFO_EXTENSION);
			$target_file = $target_dir . str_replace('/', '.', $kodetrans) . '.' . $imageFileType;
			$gambar = str_replace('/', '.', $kodetrans) . '.' . $imageFileType;
			// Check if image file is a actual image or fake image

			$check = getimagesize($_FILES["FILEGAMBAR"]["tmp_name"]);
			if ($check !== false) {
				$uploadOk = 1;
			} else {
				die(json_encode(array('errorMsg' => 'File yang Diupload bukan gambar')));
				$uploadOk = 0;
			}

			if ($_POST['GAMBAR'] != '') {
				unlink($target_dir.$_POST['GAMBAR']);
			}
			// Check if file already exists
			if (file_exists($target_file)) {
				//$uploadOk = 0;
				unlink($target_file);
			}
			// Check file size
			if ($_FILES["FILEGAMBAR"]["size"] > 500000) {
				die(json_encode(array('errorMsg' => 'Sorry, your file is too large.')));

				$uploadOk = 0;
			}
			// Allow certain file formats
			if ( ! in_array(strtolower($imageFileType), array('jpg', 'png', 'jpeg', 'gif')) ) {
				die(json_encode(array('errorMsg' => 'Sorry, only JPG, JPEG, PNG & GIF files are allowed.')));

				$uploadOk = 0;
			}
			// Check if $uploadOk is set to 0 by an error
			//if ($uploadOk == 0) {
				//die(json_encode(array('errorMsg' => 'Sorry, your file was not uploaded.')));
			// if everything is ok, try to upload file
			//} else {
				if (move_uploaded_file($_FILES["FILEGAMBAR"]["tmp_name"], $target_file)) {
					//echo "The file ". basename($_FILES["GAMBAR"]["name"]). " has been uploaded.";
				} else {
					die(json_encode(array('errorMsg' => 'Sorry, there was an error uploading your file.')));
				}
			//}
		}

		// start transaction
		$tr = $db->start_trans();

		// query header
		$data_values = array (
			$kodetrans, $lokasi, $namalokasi, $_POST['KODELOKASITERIMA'], $_POST['NAMALOKASITERIMA'],
			$kode_so, $customer, $tgltrans, date("Y.m.d"), date("H:i:s"),
			$_SESSION['user'], $_POST['JENISFRAME'], $_POST['LEBARFRAME'], $_POST['TINGGIFRAME'], $_POST['DIAGONALFRAME'],
			$_POST['BRIDGEFRAME'], $_POST['BAGIANORDER'], $_POST['JENISWARNA'], $_POST['KETERANGANWARNA'], $_POST['KODELENSA'],
			$gambar, $catatan, 'I', 0
		);
		$exe = $db->insert('tpr', $data_values, $tr);
		if (!$exe) { $db->rollback($tr); die(json_encode(array('errorMsg' => 'Simpan Data Gagal <br>Kesalahan Pada Header Data Transaksi'))); }

		// query detail
		$i = 0;
		$sql = $db->insert('tprdtl', 7, $tr, true);
		$pr  = $db->prepare($sql, $tr);
		foreach ($a_detail as $item) {
			$data_values = array (
				$kodetrans, $item->kodebarang, $i, $item->namabarang, $item->jml,
				$item->satuan, $item->keterangan
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
			'SURAT PESANAN',
			$_POST['KODEPR']=='' ? 'INSERT' : 'EDIT',
			array(
				array(
					'nama'  => 'header',
					'tabel' => 'tpr',
					'kode'  => 'KODEPR'
				),
				array(
					'nama'  => 'detail',
					'tabel' => 'tprdtl',
					'kode'  => 'KODEPR'
				),
			),
			$_SESSION['user']
		);

		echo json_encode(array('success' => true));
	break;

	case 'load_data' :
		$kodetrans = $_POST['kodetrans'];

		$sql   = "select b.*, c.namabarangsupplier, d.namasupplier, e.jml as jmlso, c.tipe, f.jenisframe
				  from tpr a
				  inner join tprdtl b on a.kodepr=b.kodepr
				  inner join mbarang c on b.kodebarang = c.kodebarang
				  left join msupplier d on c.kodesupplier = d.kodesupplier
				  left join tsodtl e on a.kodeso = e.kodeso and b.kodebarang = e.kodebarang
				  left join mjenisframe f on b.kodebarang = f.kodebarang
				  where a.kodepr = '$kodetrans'
		          order by b.urutan";
		$query = $db->query($sql);

		$items = array();
		while ($rs = $db->fetch($query)) {
			$items[] = array(
				'kodepr' 			 => $rs->KODEPR,
				'kodebarang' 		 => $rs->KODEBARANG,
				'namabarang' 		 => $rs->NAMABARANG,
				'tipe' 				 => $rs->TIPE,
				'jenisframe'		 => $rs->JENISFRAME,
				'namabarangsupplier' => $rs->NAMABARANGSUPPLIER,
				'namasupplier' 		 => $rs->NAMASUPPLIER,
				'jmlso' 			 => $rs->JMLSO,
				'jml' 				 => $rs->JML,
				'satuan' 			 => $rs->SATUAN,
				'keterangan'		 => $rs->KETERANGAN,
			);
		}

		echo json_encode(array(
			'success' => true,
			'detail' => $items,
		));
	break;

	case 'load_data_so' :
		$kodetrans = $_POST['kodetrans'];

		$sql = "select a.kodeso, a.kodebarang, b.namabarang, b.tipe, b.namabarangsupplier, d.namasupplier,
					   a.jml, a.satuan, a.sisa, a.terpenuhi, e.jenisframe
				from tsodtl a
				inner join mbarang b on a.kodebarang = b.kodebarang
				left join mcurrency c on c.kodecurrency = a.kodecurrency
				left join msupplier d on b.kodesupplier = d.kodesupplier
				left join mjenisframe e on a.kodebarang = e.kodebarang
				where a.kodeso = '$kodetrans' and
					  (b.kodejenis >= '3' and b.kodejenis <= '8')
		        order by a.urutan";

		$query = $db->query($sql);

		$items = array();
		while ($rs = $db->fetch($query)) {
			$items[] = array(
				'kodebarang' => $rs->KODEBARANG,
				'namabarang' => $rs->NAMABARANG,
				'tipe' => $rs->TIPE,
				'jenisframe' => $rs->JENISFRAME,
				'namabarangsupplier' => $rs->NAMABARANGSUPPLIER,
				'namasupplier' => $rs->NAMASUPPLIER,
				'jmlso' => $rs->JML,
				'jml' => $rs->JML,
				'terpenuhi' => $rs->TERPENUHI,
				'sisa' => $rs->SISA,
				'satuan' => $rs->SATUAN,
				'keterangan' => '',
			);
		}

		echo json_encode(array(
			'success' => true,
			'detail' => $items,
		));
	break;

	case 'batal_trans' :
		$kodetrans = $_POST['kodetrans'];
		$status    = get_status('tpr', 'kodepr', $kodetrans);

		if ($status=='P' or $status=='S') die(json_encode(array('errorMsg' => 'Transaksi Tidak Dapat Dibatalkan')));

		$tr = $db->start_trans();
		//$query = $db->update('tpr', array('status' => 'D'), array('kodepr' => $kodetrans), $tr);
		$query = $db->delete('tpr', array('kodepr' => $kodetrans), $tr);
		$db->commit($tr);

		// panggil fungsi untuk log history
		log_history(
			$kodetrans,
			'SURAT PESANAN',
			'DELETE',
			array(
				array(
					'nama'  => 'header',
					'tabel' => 'tpr',
					'kode'  => 'KODEPR'
				),
				array(
					'nama'  => 'detail',
					'tabel' => 'tprdtl',
					'kode'  => 'KODEPR'
				),
			),
			$_SESSION['user']
		);

		echo json_encode(array('success' => true));
	break;
}
?>