<?php
session_start();
include "koneksi.php";
include "function.php";

$table = $_GET['table'];

//$db = new DB;

switch ($table) {
	case 'harga_jual' :
		$kode_cust   = $_POST['kode_cust'];
		$kode_barang = $_POST['kode_barang'];
		$tgl_trans   = $_POST['tgl_trans'];

		$harga_jual = get_harga_jual_terakhir ($kode_cust, $kode_barang, $tgl_trans);
		$harga_beli = get_harga_beli_terakhir ($kode_barang, $tgl_trans);

		echo json_encode(array('harga_jual' => $harga_jual, 'harga_beli' => $harga_beli));
	break;

	case 'rate' :
		$tgl 		   = $_POST['tanggal']=='' ? date('Y.m.d') : $_POST['tanggal'];
		$kode_currency = $_POST['kode'];

		$sql	= "select first 1 skip 0 * from mcurrencykurs where tglaktif<='$tgl' and kodecurrency='$kode_currency' order by tglaktif desc";
		$query	= $db->query($sql);
		$rs		= $db->fetch($query);

		echo json_encode(array('success' => true, 'kurs' => $rs->KURS=='' ? 0 : $rs->KURS));
	break;

	case 'all_rate' :
		$tgl = $_POST['tanggal']=='' ? date('Y.m.d') : $_POST['tanggal'];

		$temp_array =  array();
		$query = $db->query('select * from mcurrency where tanda=0');
		while ($rs = $db->fetch($query)) {
			$query1	= $db->query("select first 1 skip 0 * from mcurrencykurs where tglaktif<='$tgl' and kodecurrency='$rs->KODECURRENCY' order by tglaktif desc");
			$rs1	= $db->fetch($query1);

			$temp_array[] = array('kodecurrency' => $rs->KODECURRENCY, 'kurs' => $rs1->KURS=='' ? 0 : $rs1->KURS);
		}

		echo json_encode(array('success' => true, 'data_detail' => $temp_array));
	break;

	case 'get_stok' :
		$tgl 	 = $_POST['tanggal'];
		$kodebrg = $_POST['kodebarang'];
		$kodelokasi = $_POST['kodelokasi'] == '' ? $_SESSION['KODELOKASI'] : $_POST['kodelokasi'];
		$kodegudang = $_POST['kodegudang'];

		// dapatkan gudang utama dr lokasi login
		if ($kodegudang == '') {
			$q = $db->select('mgudang', array('kodegudang'), array('kodelokasi'=>$kodelokasi, 'jenis'=>1));
			$r = $db->fetch($q);
			$kodegudang = $r->KODEGUDANG;
		}
		// panggil procedure
		$pr = $db->prepare("execute procedure GET_SALDOSTOK(?, ?, ?, ?)");
		$ex = $db->execute($pr, array($kodebrg, $kodelokasi, $kodegudang, $tgl));
		$rs = $db->fetch($ex);

		echo json_encode(array('success'=>true, 'jmlstok'=>$rs->SALDO));
	break;

	case 'get_barcode' :
		$jenisbarang = $_POST['kodejenis'];
		// jika jenis bahan [frame, sunglass, accesories]
		// maka tambahkan barcode
		
		$barcode = '';
		if (in_array($jenisbarang, array(1, 2, 4))) {
			$sql = "select max(barcode) as max_barcode
					from mbarang";
			$query = $db->query($sql);
			$rs = $db->fetch($query);

			$str = (string) substr($rs->MAX_BARCODE, 0, 1);
			$huruf = range('A', 'Z');
			$angka = substr($rs->MAX_BARCODE, 1, 5);

			if (in_array($str, $huruf)) {
				if ($angka == 9999) {
					$a = array_keys($huruf, $str);

					$barcode = $huruf[$a[0]+1].'0001';
				} else {
					$barcode = $str.substr(($angka + 10001), 1, 5);
				}
			} else {
				$barcode = 'A0001';
			}
		}
		
		echo json_encode(array('success'=>true, 'barcode'=>$barcode));
	break;
	
	case 'get_kodebarang' :
		$lensanonstok = $_POST['lensanonstok'];
		$jenisbarang  = $_POST['jenisbarang'];
		$kodekategori = $_POST['kodekategori'];
		$kodebahan    = $_POST['kodebahan'];
		$kodesupplier = explode("/",$_POST['kodesupplier']);
		
		if ($lensanonstok == 1) {
			$temp_kode = $jenisbarang . $kodebahan. '0' .$kodesupplier[2];

			$kodebarang = $temp_kode.get_max_urutan('mbarang', 'kodebarang', $temp_kode, 5);
		} else {
			if ($jenisbarang >= 3 and $jenisbarang <= 9) {
				// tanpa kategori

				$temp_kode = $jenisbarang;
				$kodebarang = $temp_kode.get_max_urutan('mbarang', 'kodebarang', $temp_kode, 12);
			} else {
				if (strlen($kodekategori) <> 3) {
					//die(json_encode(array('errorMsg' => 'Data Kategori Harus Lengkap')));
				}

				// dengan kategori
				$temp_kode = $jenisbarang . date('ym') . $kodekategori;
				$kodebarang = $temp_kode.get_max_urutan('mbarang', 'kodebarang', $temp_kode, 5);
			}
		}
		
		echo json_encode(array('success'=>true, 'kodebarang'=>$kodebarang));
	break;

	default :
		echo json_encode(array());
}
?>