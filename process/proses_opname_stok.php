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
		$kodetrans  = $_POST['KODEOPNAME'];
		$lokasi     = $_POST['KODELOKASI'];
		$gudang     = $_POST['KODEGUDANG'];
		$tgltrans   = ubah_tgl_firebird($_POST['TGLTRANS']);
		$keterangan = $_POST['CATATAN'];

		$a_detail = json_decode($_POST['data_detail']);

		cek_data($a_detail, 'kodebarang', 'mbarang');

		if (count($a_detail)<1) die(json_encode(array('errorMsg' => 'Please Insert Detail Transaction')));

		//cek_valid_data('MLOKASI', 'KODELOKASI', $lokasi, 'Lokasi');
		cek_valid_data('mgudang', 'kodegudang', $gudang, 'Gudang');

		$mode = $_POST['mode'];
		if ($mode=='tambah') { // generate kodetrans
			/*$temp_kode = 'OPM/'.$_SESSION['KODELOKASI'].'/';

			$urutan = get_new_urutan('topnamestok', 'kodeopname', array($temp_kode, substr($tgltrans, 2, 2)), 3);

			$kodetrans = $temp_kode.$urutan.'/'.substr($tgltrans, 5, 2).'/'.substr($tgltrans, 2, 2);*/

			$temp_kode = 'OPM/'.$_SESSION['KODELOKASI'].'/'.substr($tgltrans, 2, 2).substr($tgltrans, 5, 2);

			$urutan = get_max_urutan('topnamestok', 'kodeopname', $temp_kode, 4);

			$kodetrans = $temp_kode.$urutan;

			cek_periode($tgltrans,'tambahkan');
		} else {
			//cek tgl asal mungkin dilakukan perubahan tanggal
			cek_periode(get_tgl_trans('topnamestok', 'kodeopname', $kodetrans), 'ubah');
			cek_periode($tgltrans,'ubah');
		}

		// start transaction
		$tr  = $db->start_trans();

		// insert header
		$data_values = array(
			$kodetrans, $_SESSION['KODELOKASI'], $_SESSION['NAMALOKASI'], $gudang, $tgltrans, date("Y.m.d"), date("H:i:s"), $_SESSION['user'],
			$keterangan, 'I', 0
		);
		$exe = $db->insert('topnamestok', $data_values, $tr);
		if (!$exe) { $db->rollback($tr); die(json_encode(array('errorMsg' => 'Transaksi Gagal <br>Harap Cek Data Header Transaksi'))); }

		//insert detail
		$i = 0;
		$sql = $db->insert('topnamestokdtl', 8, $tr, true);
		$pr  = $db->prepare($sql, $tr);
		foreach ($a_detail as $item) {
			$data_values = array(
				$kodetrans, $item->kodebarang, $i, $item->namabarang, $item->jml,
				$item->satuan, $item->satuankecil, 1
			);
			//if ($item->jml > 0) {
				$exe = $db->execute($pr, $data_values);
				if (!$exe) { $db->rollback($tr); die(json_encode(array('errorMsg' => 'Transaksi Gagal <br>Harap Cek Data Detail Transaksi'))); }
				$i++;
			//}
		}
		$db->commit($tr);

		// panggil fungsi untuk log history
		log_history(
			$kodetrans,
			'OPNAME STOK',
			$_POST['KODEOPNAME']=='' ? 'INSERT' : 'EDIT',
			array(
				array(
					'nama'  => 'header',
					'tabel' => 'topnamestok',
					'kode'  => 'kodeopname'
				),
				array(
					'nama'  => 'detail',
					'tabel' => 'topnamestokdtl',
					'kode'  => 'kodeopname'
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
				  from topnamestok a
				  inner join topnamestokdtl b on a.kodeopname=b.kodeopname
				  inner join mbarang c on b.kodebarang=c.kodebarang
				  left join mjenisframe d on b.kodebarang = d.kodebarang
				  where a.kodeopname = '$kodetrans'
		          order by kodebarang";
		$query = $db->query($sql);
		while ($rs = $db->fetch($query)) {
			$rows[] = array(
				'kodebarang'  => $rs->KODEBARANG,
				'namabarang'  => $rs->NAMABARANG,
				'tipe'        => $rs->TIPE,
				'jenisframe'  => $rs->JENISFRAME,
				'namabarangsupplier'  => $rs->NAMABARANGSUPPLIER,
				'jml'         => $rs->JML,
				'satuan'      => $rs->SATUAN,
				'satuanutama' => $rs->SATUANUTAMA,
				'konversi'    => $rs->KONVERSI,
			);
		}

		echo json_encode(array(
			'success' => true,
			'detail' => $rows,
		));
	break;

	case 'batal_trans' :
		$kodetrans = $_POST['kodetrans'];
		$status    = get_status('topnamestok', 'kodeopname', $kodetrans);

		if ($status=='P') die(json_encode(array('errorMsg' => 'Transaksi Tidak Dapat Dibatalkan')));

		cek_periode(get_tgl_trans('topnamestok', 'kodeopname', $kodetrans), 'hapus');

		$tr = $db->start_trans();
		$query = $db->update('topnamestok', array('status' => 'D'), array('kodeopname' => $kodetrans), $tr);
		$db->commit($tr);

		// panggil fungsi untuk log history
		log_history(
			$kodetrans,
			'OPNAME STOK',
			'DELETE',
			array(
				array(
					'nama'  => 'header',
					'tabel' => 'topnamestok',
					'kode'  => 'kodeopname'
				),
				array(
					'nama'  => 'detail',
					'tabel' => 'topnamestokdtl',
					'kode'  => 'kodeopname'
				),
			),
			$_SESSION['user']
		);

		echo json_encode(array('success' => true));
	break;

	case 'upload_excel' :
		$target_dir = "c:/";
		$target_file = $target_dir . basename($_FILES["fileExcel"]["name"]);
		$uploadOk = 1;
		$FileType = pathinfo($target_file,PATHINFO_EXTENSION);

		// Check file size
		if ($_FILES["fileExcel"]["size"] > 5000000) {
			die(json_encode(array('errorMsg' => 'Sorry, your file is too large')));
			$uploadOk = 0;
		}
		// Allow certain file formats
		if ($FileType != "xls") {
			die(json_encode(array('errorMsg' => 'Hanya file Excel yang berekstensi .xls yang diperbolehkan')));
			$uploadOk = 0;
		}
		// Check if $uploadOk is set to 0 by an error
		if ($uploadOk == 0) {
			die(json_encode(array('errorMsg' => 'Sorry, your file was not uploaded.')));
		// if everything is ok, try to upload file
		} else {
			$new_file_name = date("YMdHis");
			if (move_uploaded_file($_FILES["fileExcel"]["tmp_name"], $target_dir. '/' .$new_file_name.'.xls')) {
				//echo "The file ". basename( $_FILES["fileExcel"]["name"]). " has been uploaded.";
				//echo "<br>";

				$new_file_excel = 'c:/'.$new_file_name.'.xls';

				include "../../config/excel_reader2.php";

				// membaca file excel yang diupload
				$data = new Spreadsheet_Excel_Reader($new_file_excel);

				// membaca jumlah baris dari data excel
				$sheet_index = 1;
				$baris = $data->rowcount($sheet_index);

				// mendapatakan lokasi dan gudang
				$kodelokasi = $data->val(1, 2, $sheet_index);
				$kodegudang = $data->val(2, 2, $sheet_index);

				//query lokasi
				$q = $db->select('mlokasi', array('kodelokasi', 'namalokasi'), array('kodelokasi'=>$kodelokasi));
				$r = $db->fetch($q);

				if ($r->KODELOKASI == '') {
					die(json_encode(array('errorMsg' => 'Data Lokasi Salah')));
				}
				$namalokasi = $r->NAMALOKASI;

				//query gudang
				cek_valid_data('mgudang', 'kodegudang', $kodegudang, 'Gudang');

				// tgltrans
				$tgltrans = $_POST['tglopname'];

				// buat kodetrans
				$temp_kode = 'OPM/'.$kodelokasi.'/';

				$urutan = get_new_urutan('topnamestok', 'kodeopname', array($temp_kode, substr($tgltrans, 2, 2)), 3);

				$kodetrans = $temp_kode.$urutan.'/'.substr($tgltrans, 5, 2).'/'.substr($tgltrans, 2, 2);

				// start transaction
				$tr = $db->start_trans();

				// insert saldostok
				$data_values = array(
					$kodetrans, $kodelokasi, $namalokasi, $kodegudang, $tgltrans, date("Y.m.d"), date("H:i:s"), $_SESSION['user'],
					'', 'I', 0
				);
				$exe = $db->insert('topnamestok', $data_values, $tr);
				if (!$exe) { $db->rollback($tr); die(json_encode(array('errorMsg' => 'Transaksi Gagal <br>Harap Cek Data Header Transaksi'))); }

				// insert saldostokdtl
				$urutan = 0;
				$sql = $db->insert('topnamestokdtl', 8, $tr, true);
				$pr  = $db->prepare($sql, $tr);

				// prepare query barang
				$pr1 = $db->prepare('select namabarang, kodebarang, satuan from mbarang where kodebarang = ?');

				// variabel untuk menampung baris yang tidak dieksekusi atau datanya salah
				$data_salah = array();

				for ($i=4; $i<=$baris; $i++) {
					$barcode 	= $data->val($i, 1, $sheet_index);
					$kodebarang = $data->val($i, 3, $sheet_index);
					$jumlah 	= $data->val($i, 5, $sheet_index);

					// cek kodebarang
					$exe = $db->execute($pr1, $kodebarang);
					$rs  = $db->fetch($exe);

					if ($rs->KODEBARANG != '') {
						$data_values = array(
							$kodetrans, $kodebarang, $urutan, $rs->NAMABARANG, $jumlah,
							$rs->SATUAN, '', 0
						);
						$exe = $db->execute($pr, $data_values);
						if (!$exe) { $db->rollback($tr); die(json_encode(array('errorMsg' => 'Transaksi Gagal <br>Harap Cek Data Detail Transaksi'))); }
						$urutan++;
					} else {
						$data_salah[] = $i;
					}
				}

				unlink($new_file_excel);

				$db->commit($tr);

				echo json_encode(array('success' => true, 'warning'=>(count($data_salah) > 0 ? true : false), 'warningMsg'=>'Ada Kesalahan Data Barang Pada Baris ('.implode(', ', $data_salah).')'));

			} else {
				die(json_encode(array('errorMsg' => 'Sorry, there was an error uploading your file.')));
			}
		}
	break;
}
?>