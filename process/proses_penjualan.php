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

		$kodetrans    	 = $_POST['KODEJUAL'];
		$nofakturpajak   = '';//$_POST['NOFAKTURPAJAK'];
		$kode_so    	 = ''; //$_POST[txt_nofaktursupplier];
		$lokasi       	 = $_SESSION['KODELOKASI'];//$_POST['KODELOKASI'];
		$namalokasi   	 = $_SESSION['NAMALOKASI'];//$_POST['NAMALOKASI'];
		$customer     	 = $_POST['KODECUSTOMER'];
		$namacustomer 	 = $_POST['NAMACUSTOMER'];
		//$syaratbayar     = $_POST['KODESYARATBAYAR'];
		//$namasyaratbayar = $_POST['NAMASYARATBAYAR'];
		//$Catatan         = $_POST['CATATAN'];
		$tgltrans        = $_POST['TGLTRANS'];
		$tgljatuhtempo   = $_POST['TGLTRANS'];//$_POST['TGLJATUHTEMPO'];
		$selisihjual     = 0;
		if ($_POST['MULAIANGSUR'] == '')
			$_POST['MULAIANGSUR'] = date('Y.m.d');

		if (count($a_detail)<1) die(json_encode(array('errorMsg' => 'Simpan Data Gagal <br>Please Insert Detail Transaction')));

		if ($namacustomer=='') die(json_encode(array('errorMsg' => 'Informasi Nama Customer Tidak Boleh Kosong')));

		cek_valid_data('MLOKASI', 'KODELOKASI', $lokasi, 'Lokasi');
		//cek_valid_data('MSYARATBAYAR', 'KODESYARATBAYAR', $syaratbayar, 'Syarat Bayar');
		cek_valid_data('MCUSTOMER', 'KODECUSTOMER', $customer, 'Customer');

		if ($_POST['KODEPEGAWAI_SALES'] <> '') cek_valid_data('muser', 'userid', $_POST['KODEPEGAWAI_SALES'], 'Sales');
		if ($_POST['KODEPEGAWAI_RO'] <> '') cek_valid_data('muser', 'userid', $_POST['KODEPEGAWAI_RO'], 'RO');
		if ($_POST['KODEPEGAWAI_EDGER'] <> '') cek_valid_data('muser', 'userid', $_POST['KODEPEGAWAI_EDGER'], 'Edger');
		if ($_POST['KODEPEGAWAI_SETEL'] <> '') cek_valid_data('muser', 'userid', $_POST['KODEPEGAWAI_SETEL'], 'Setel');
		if ($_POST['KODEPEGAWAI_FITTING'] <> '') cek_valid_data('muser', 'userid', $_POST['KODEPEGAWAI_FITTING'], 'Fitting');
		if ($_POST['SISABAYAR'] > 0) {
			cek_valid_data('minstansi', 'kodeinstansi', $_POST['KODEINSTANSI'], 'Instansi');
			cek_valid_data('mjurubayar', 'kodejurubayar', $_POST['KODEJURUBAYAR'], 'Juru Bayar');
		}

		// satu penjualan hanya boleh satu so
		if ($_POST['KODESO'] <> '') {
			$q = $db->query("select kodeso, kodejual from tjual where kodeso='".$_POST['KODESO']."' and status<>'D'");
			$r = $db->fetch($q);
			if ($r->KODEJUAL <> '') {
				die(json_encode(array('errorMsg' => 'No SO telah digunakan di Penjualan '.$r->KODEJUAL)));
			}

			$sql = 'select a.kodetransfertitipan, b.kodeterimatitipan, c.kodekembalititipan, d.kodeterimakembalititipan
					from ttransfertitipan a
					left join tterimatitipan b on a.kodetransfertitipan = b.kodetransfertitipan and b.status <> \'D\'
					left join tkembalititipan c on b.kodeterimatitipan = c.kodeterimatitipan and c.status <> \'D\'
					left join tterimakembalititipan d on c.kodekembalititipan = d.kodekembalititipan and d.status <> \'D\'
					where a.kodeso=? and a.status <> \'D\'';
			$p = $db->prepare($sql);
			$q = $db->execute($p, $_POST['KODESO']);
			$r = $db->fetch($q);

			if ($r->KODETRANSFERTITIPAN <> '' and $r->KODETERIMAKEMBALITITIPAN == '') {
				die(json_encode(array('errorMsg' => 'SO Belum Dilakukan Terima Kembali Titipan')));
			}
		}

		// CEK APAKAH SISA MINUS ?
		// JIKA YA MAKA KEMBALIKAN SISA YANG MINUS
		$ada_pengembalian = false;
		if ($_POST['DOWNPAYMENT'] > $_POST['GRANDTOTAL']) {
			$_POST['CASH'] = 0;
			$_POST['KARTUKREDIT'] = 0;
			$_POST['KARTUDEBIT'] = 0;
			$_POST['ASKES'] = 0;

			$_POST['DOWNPAYMENT'] = $_POST['GRANDTOTAL']; //$_POST['DOWNPAYMENT'] - ($_POST['DOWNPAYMENT'] - $_POST['GRANDTOTAL']);
			//$_POST['GRANDTOTAL'] = 0;

			$ada_pengembalian = true;
		}

		$mode = $_POST['mode'];
		if ($mode=='tambah') {
			/*$temp_kode = 'BP/'.$_SESSION['KODELOKASI'].'/';

			$urutan = get_new_urutan('tjual', 'kodejual', array($temp_kode, substr($tgltrans, 2, 2)));

			$kodetrans = $temp_kode.$urutan.'/'.substr($tgltrans, 5, 2).'/'.substr($tgltrans, 2, 2);*/

			$temp_kode = $_SESSION['KODELOKASI'].'/'.substr($tgltrans, 2, 2).substr($tgltrans, 5, 2).substr($tgltrans, -2);

			// mendapatkan urutan
			$q = $db->query("select max(substring(kodejual from 4)) as kode from tjual where kodejual like '%$temp_kode%'");
			$r = $db->fetch($q);

			$urutan = substr(substr($r->KODE, -4)+10001, 1);

			if ($urutan < 1000) {
				$urutan += 1000;
			}

			// ambil max urutan dr tabel history tanggal
			$q = $db->select('historytanggal', array('URUTANMAXJUAL'), array('tanggal'=>$tgltrans, 'kodelokasi'=>$_SESSION['KODELOKASI']));
			$r = $db->fetch($q);
			$max_urutan = $r->URUTANMAXJUAL;

			$last_urutan = (int) substr($urutan, -2);
			if ($last_urutan > $max_urutan && $urutan < 2000) {
				$urutan = 2001;
			}

			if ($_POST['KODESO'] <> '') {
				$temp_kode = 'BP/'.$temp_kode;
			} else {
				$temp_kode = 'DS/'.$temp_kode;
			}
			$kodetrans = $temp_kode.$urutan;

			cek_periode($tgltrans,'tambahkan');
		} else {
			//$kodetrans = $_POST['KODEPELUNASAN'];
			//cek tgl asal mungkin dilakukan perubahan tanggal
			cek_periode(get_tgl_trans('tjual', 'kodejual', $kodetrans), 'ubah');
			cek_periode($tgltrans,'ubah');
		}

		// start transaction
		$tr = $db->start_trans();

		// query header
		$data_values = array(
			$kodetrans, $_POST['NOINVOICEMANUAL'], $nofakturpajak, $lokasi, $namalokasi,
			$_POST['KODESO'], $_POST['KODEINSTANSI'], $_POST['KODEJURUBAYAR'], $customer, $namacustomer,
			$_POST['KODEPROMO'], $_POST['KODEPEGAWAI_SALES'], $_POST['KODEPEGAWAI_RO'], $_POST['KODEPEGAWAI_EDGER'], $_POST['KODEPEGAWAI_SETEL'],
			$_POST['KODEPEGAWAI_FITTING'], $tgltrans, date("Y.m.d"), date("H:i:s"), $_POST['LAMAANGSURAN'],
			$_POST['MULAIANGSUR'], $_SESSION['user'], $_POST['PAKAIPPN'], $_POST['TOTAL'], $_POST['DISKON'],
			$_POST['DISKONRP'], $_POST['PPNPERSEN'], $_POST['PPNRP'], $_POST['GRANDTOTAL'], $_POST['DOWNPAYMENT'],
			$selisihjual, $_POST['CASH'], $_POST['KARTUKREDIT'], $_POST['BANKKARTUKREDIT'], $_POST['NOKARTUKREDIT'],
			$_POST['KARTUDEBIT'], $_POST['BANKKARTUDEBIT'], $_POST['NOKARTUDEBIT'], $_POST['ASKES'], $_POST['CATATAN'],
			'I', 0
		);
		$exe = $db->insert('tjual', $data_values, $tr);
		if (!$exe) { $db->rollback($tr); die(json_encode(array('errorMsg' => 'Simpan Data Gagal <br>Kesalahan Pada Header Data Transaksi'))); }

		// query detail
		$i = 0;
		$sql = $db->insert('tjualdtl', 26, $tr, true);
		$pr  = $db->prepare($sql, $tr);

		/*
		$sql = $db->insert('mhargajual', 7, $tr, true);
		$pr2 = $db->prepare($sql);
		*/
		foreach ($a_detail as $item) {
			$data_values = array (
				$kodetrans, $item->kodeso, $item->kodebarang, $i, $item->namabarang,
				$item->jml, $item->satuan, '', 0, 0,
				$item->kodecurrency, $item->harga, 1, $item->harga, $item->disc1,
				$item->discrp1, $item->disc2, $item->discrp2, $item->disc3, $item->discrp3,
				$item->disc4, $item->discrp4, $item->disc5, $item->discrp5, $item->subtotal,
				$item->subtotal
			);

			if ($item->jml > 0) {
				$exe = $db->execute($pr, $data_values);
				if (!$exe) { $db->rollback($tr); die(json_encode(array('errorMsg' => 'Simpan Data Gagal <br>Kesalahan Pada Detail Data Transaksi'))); }
				$i++;
			}

			// cek stok
			$stok_cukup = cek_stok ($item->kodebarang, $tgltrans, $item->jml, $item->satuan);
			if (!$stok_cukup) { $db->rollback($tr); die(json_encode(array('errorMsg' => 'stok barang '.$item->kodebarang.' ('.$item->namabarang.') tidak mencukupi'))); }
		}
		//MELAKUKAN TARIK DATA KE VALUE PERKIRAAN
		//$exe = $db->query("execute procedure INPUTJURNALJUAL('JUAL','$kodetrans')", $tr);
		//if (!$exe) { $db->rollback($tr); die(json_encode(array('errorMsg' => 'Simpan Data Gagal <br>Please Check Jurnal Link Program'))); }

		$db->commit($tr);

		$tr = $db->start_trans();
		$exe = $db->query("execute procedure INPUTJURNALJUAL('JUAL','$kodetrans')", $tr);
		$db->commit($tr);

		// jika lebih bayar dp, maka lakukan pengembalian
		if ($ada_pengembalian) {
			$tr = $db->start_trans();
			$exe = $db->query("execute procedure INPUTJURNALPENGEMBALIAN('JUAL','$kodetrans')", $tr);
			$db->commit($tr);
		} //else {
		// jika tidak lebih bayar maka lakukan jurnal pembayaran
			$tr = $db->start_trans();
			$exe = $db->query("execute procedure INPUTJURNALPEMBAYARAN('JUAL','$kodetrans')", $tr);
			$db->commit($tr);
		//}

		// panggil fungsi untuk log history
		log_history(
			$kodetrans,
			'PENJUALAN',
			$_POST['KODEJUAL']=='' ? 'INSERT' : 'EDIT',
			array(
				array(
					'nama'  => 'header',
					'tabel' => 'tjual',
					'kode'  => 'kodejual'
				),
				array(
					'nama'  => 'detail',
					'tabel' => 'tjualdtl',
					'kode'  => 'kodejual'
				),
			),
			$_SESSION['user']
		);

		echo json_encode(array('success' => true, 'kodetrans' => $kodetrans, 'kodeso' => $_POST['KODESO']));
	break;

	case 'load_data' :
		$kodetrans = $_POST['kodetrans'];

		$sql   = "select b.*, c.simbol as currency, d.tipe, d.namabarangsupplier, e.jenisframe
				  from tjual a
				  inner join tjualdtl b on a.kodejual=b.kodejual
				  left join mcurrency c on c.kodecurrency=b.kodecurrency
				  inner join mbarang d on b.kodebarang = d.kodebarang
				  left join mjenisframe e on b.kodebarang = e.kodebarang
				  where a.kodejual = '$kodetrans'
		          order by urutan";
		$query = $db->query($sql);

		$items = array();
		while ($rs = $db->fetch($query)) {
			$items[] = array(
				'kodebarang' => $rs->KODEBARANG,
				'namabarang' => $rs->NAMABARANG,
				'namabarangsupplier' => $rs->NAMABARANGSUPPLIER,
				'tipe' => $rs->TIPE,
				'jenisframe' => $rs->JENISFRAME,
				'jml' => $rs->JML,
				'satuan' => $rs->SATUAN,
				'kodecurrency' => $rs->KODECURRENCY,
				'currency' => $rs->CURRENCY,
				'hpokok' => $rs->HPOKOK,
				'harga' => $rs->HARGA,
				'subtotal' => $rs->SUBTOTAL,
				'nilaikurs' => $rs->NILAIKURS,
				'disc1' => $rs->DISC1,
				'disc2' => $rs->DISC2,
				'disc3' => $rs->DISC3,
				'disc4' => $rs->DISC4,
				'disc5' => $rs->DISC5,
				'discrp1' => $rs->DISCRP1,
				'discrp2' => $rs->DISCRP2,
				'discrp3' => $rs->DISCRP3,
				'discrp4' => $rs->DISCRP4,
				'discrp5' => $rs->DISCRP5,
				'hargakurs' => $rs->HARGAKURS,
				'subtotalkurs' => $rs->SUBTOTALKURS,
			);
		}

		echo json_encode(array('success' => true, 'detail' => $items));
	break;

	case 'batal_trans' :
		$kodetrans = $_POST[kodetrans];
		$status    = get_status('tjual', 'kodejual', $kodetrans);

		if ($status=='P') die(json_encode(array('errorMsg' => 'Transaksi Tidak Dapat Dibatalkan')));

		$q = $db->query('select kodejual from treturjual where status <> \'D\' and kodejual = \''.$kodetrans.'\'');
		$r = $db->fetch($q);
		if ($r->KODEJUAL <> '') {
			die(json_encode(array('errorMsg' => 'Transaksi Tidak Dapat Dibatalkan<br>No Penjualan Telah Digunakan di Retur Penjualan')));
		}

		cek_periode(get_tgl_trans('tjual', 'kodejual', $kodetrans), 'hapus');

		cek_pelunasan ('piutang', $kodetrans);

		$tr = $db->start_trans();
		$query = $db->update('tjual', array('status' => 'D'), array('kodejual' => $kodetrans), $tr);
		$db->commit($tr);

		$tr = $db->start_trans();
		$exe = $db->query("execute procedure INPUTJURNALPENGEMBALIAN('BATAL JUAL','$kodetrans')", $tr);
		$db->commit($tr);

		// panggil fungsi untuk log history
		log_history(
			$kodetrans,
			'PENJUALAN',
			'DELETE',
			array(
				array(
					'nama'  => 'header',
					'tabel' => 'tjual',
					'kode'  => 'kodejual'
				),
				array(
					'nama'  => 'detail',
					'tabel' => 'tjualdtl',
					'kode'  => 'kodejual'
				),
			),
			$_SESSION['user']
		);

		echo json_encode(array('success' => true));
	break;

	case 'load_data_so' :
		$kodetrans = $_POST['kodetrans'];

		$items = array();

	    $sql   = "select b.*, c.simbol, a.kodecustomer, a.tgltrans,
						d.tipe, d.namabarangsupplier, e.kodemember,
						f.jenisframe, e.diskon, a.kodelokasi
				  from tso a
				  inner join tsodtl b on a.kodeso=b.kodeso
				  left join mcurrency c on c.kodecurrency=b.kodecurrency
				  inner join mbarang d on b.kodebarang=d.kodebarang
				  left join mmember e on a.kodecustomer = e.kodecustomer
				  left join mjenisframe f on b.kodebarang = f.kodebarang
				  where a.kodeso = '$kodetrans'
		          order by b.urutan";
		$query = $db->query($sql);

		$subtotal = 0;
		while ($rs = $db->fetch($query)) {
			if (in_array(substr($rs->KODEBARANG, 0, 1), array(1, 2))) {
				if ($rs->KODEMEMBER <> '' and $rs->DISCRP1 == 0) {
					$rs->DISC1 = $rs->DISKON;
					$rs->DISCRP1 = $rs->HARGA * $rs->DISC1 / 100;
					$rs->SUBTOTAL = ($rs->HARGA - $rs->DISCRP1) * $rs->JML;
					$rs->SUBTOTALKURS = $rs->SUBTOTAL;
				}
			}

			// parameter tampil stok berasal dari retur pembelian
			// untuk mengetahui stok terbaru dari lokasi SO
			$stok = 0;
			if ($_POST['tampil_stok']) {
				$pr = $db->prepare('execute procedure GET_SALDOSTOK(?, ?, ?, ?)');
				$ex = $db->execute($pr, array($rs->KODEBARANG, $rs->KODELOKASI, $rs->KODEGUDANG, $_POST['tgltrans']));
				$r = $db->fetch($ex);
				$stok = $r->SALDO;
			}

			$items[] = array(
				'kodeso' => $rs->KODESO,
				'kodebarang' => $rs->KODEBARANG,
				'namabarang' => $rs->NAMABARANG,
				'tipe' => $rs->TIPE,
				'jenisframe' => $rs->JENISFRAME,
				'namabarangsupplier' => $rs->NAMABARANGSUPPLIER,
				'jmlstok' => $stok,
				'jml' => $rs->JML,
				'satuan' => $rs->SATUAN,
				'konversi' => $rs->KONVERSI,
				'kodecurrency' => $rs->KODECURRENCY,
				'currency' => $rs->SIMBOL,
				'harga' => $rs->HARGA,
				'subtotal' => $rs->SUBTOTAL,
				'nilaikurs' => $rs->NILAIKURS,
				'disc1' => $rs->DISC1,
				'disc2' => $rs->DISC2,
				'disc3' => $rs->DISC3,
				'disc4' => $rs->DISC4,
				'disc5' => $rs->DISC5,
				'discrp1' => $rs->DISCRP1,
				'discrp2' => $rs->DISCRP2,
				'discrp3' => $rs->DISCRP3,
				'discrp4' => $rs->DISCRP4,
				'discrp5' => $rs->DISCRP5,
				'hargakurs' => $rs->HARGAKURS,
				'subtotalkurs' => $rs->SUBTOTALKURS,
				'tutup' => $rs->TUTUP,
				'temp_harga_jual' => get_harga_jual_terakhir ($rs->KODECUSTOMER, $rs->KODEBARANG, $rs->TGLTRANS),
				'temp_harga_beli' => get_harga_beli_terakhir ($rs->KODEBARANG, $rs->TGLTRANS),
			);

			$subtotal += $rs->SUBTOTALKURS;
		}

		$temp_sql = 'and b.jenis = \'JUAL\'';
		if ($subtotal > 500000) {
			$temp_sql .= ' or b.jenis = \'LEBIH DARI 500RB\'';
		}
		$sql   = "select a.*, c.jenisframe
				  from mbarang a
				  inner join mbarangjual b on a.kodebarang = b.kodebarang
				  left join mjenisframe c on a.kodebarang = c.kodebarang
				  where 1=1 $temp_sql
				  order by b.urutan";
		$query = $db->query($sql);
		while ($rs = $db->fetch($query)) {
			$items[] = array(
				'kodeso' => '',
				'kodebarang' => $rs->KODEBARANG,
				'namabarang' => $rs->NAMABARANG,
				'tipe' => $rs->TIPE,
				'jenisframe' => $rs->JENISFRAME,
				'namabarangsupplier' => $rs->NAMABARANGSUPPLIER,
				'jml' => 1,
				'satuan' => $rs->SATUAN,
				'konversi' => 1,
				'kodecurrency' => $_SESSION['KODECURRENCY'],
				'currency' => 'Rp',
				'harga' => 0,
				'subtotal' => 0,
				'nilaikurs' => 1,
				'disc1' => 0,
				'disc2' => 0,
				'disc3' => 0,
				'disc4' => 0,
				'disc5' => 0,
				'discrp1' => 0,
				'discrp2' => 0,
				'discrp3' => 0,
				'discrp4' => 0,
				'discrp5' => 0,
				'hargakurs' => 0,
				'subtotalkurs' => 0,
				'temp_harga_jual' => 0,
				'temp_harga_beli' => 0
			);
		}

		echo json_encode(array(
			'success' => true,
			'detail' => $items,
		));
	break;

	case 'load_data_barang_jual' :
		$sql = "select a.*, c.jenisframe
				from mbarang a
				inner join mbarangjual b on a.kodebarang = b.kodebarang
				left join mjenisframe c on a.kodebarang = c.kodebarang
				where b.jenis = 'JUAL'
				order by b.urutan";
		$query = $db->query($sql);
		while ($rs = $db->fetch($query)) {
			$items[] = array(
				'kodeso' => '',
				'kodebarang' => $rs->KODEBARANG,
				'namabarang' => $rs->NAMABARANG,
				'tipe' => $rs->TIPE,
				'jenisframe' =>  $rs->JENISFRAME,
				'namabarangsupplier' => $rs->NAMABARANGSUPPLIER,
				'jml' => 1,
				'satuan' => $rs->SATUAN,
				'konversi' => 1,
				'kodecurrency' => $_SESSION['KODECURRENCY'],
				'currency' => 'Rp',
				'harga' => 0,
				'subtotal' => 0,
				'nilaikurs' => 1,
				'disc1' => 0,
				'disc2' => 0,
				'disc3' => 0,
				'disc4' => 0,
				'disc5' => 0,
				'discrp1' => 0,
				'discrp2' => 0,
				'discrp3' => 0,
				'discrp4' => 0,
				'discrp5' => 0,
				'hargakurs' => 0,
				'subtotalkurs' => 0,
				'temp_harga_jual' => 0,
				'temp_harga_beli' => 0
			);
		}

		echo json_encode(array(
			'success' => true,
			'detail' => $items,
		));
	break;
}
?>