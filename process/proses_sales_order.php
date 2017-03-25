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

		$kodetrans    	 = $_POST['KODESO'];
		$lokasi       	 = $_SESSION['KODELOKASI'];
		$namalokasi   	 = $_SESSION['NAMALOKASI'];
		$customer     	 = $_POST['KODECUSTOMER'];
		$namacustomer 	 = $_POST['NAMACUSTOMER'];
		$syaratbayar     = 'SB001'; //$_POST['KODESYARATBAYAR'];
		$namasyaratbayar = $_POST['NAMASYARATBAYAR'];
		$catatan         = $_POST['CATATAN'];
		$tgltrans        = $_POST['TGLTRANS'];
		$tgljatuhtempo   = $_POST['TGLJATUHTEMPO'];
		$kodejurubayar   = $_POST['KODEJURUBAYAR'];
		$kodeinstansi    = $_POST['KODEINSTANSI'];

		if ($namacustomer=='') die(json_encode(array('errorMsg' => 'Informasi Nama Customer Tidak Boleh Kosong')));
		
		if (is_null($kodejurubayar)) $kodejurubayar ='';
		if (is_null($kodeinstansi)) $kodeinstansi ='';
		if ($kodeinstansi!='' && $kodejurubayar=='') die(json_encode(array('errorMsg' => 'Informasi Juru Bayar Tidak Boleh Kosong')));
		if ($kodeinstansi=='' && $kodejurubayar!='') die(json_encode(array('errorMsg' => 'Informasi Instansi Tidak Boleh Kosong')));
		if ($_POST['JENIS']!='REVISI' && $_POST['JENIS']!='BARU'){
			die(json_encode(array('errorMsg' => 'Informasi Jenis SO Tidak Valid, Harus Berisi Informasi BARU/REVISI')));
		}
		if ($_POST['TIPEORDER']!='TUNAI' && $_POST['TIPEORDER']!='KREDIT'){
			die(json_encode(array('errorMsg' => 'Tipe Order Tidak Valid, Harus Berisi Informasi TUNAI/KREDIT')));
		}
		if ($_POST['TIPEORDER'] == 'KREDIT') {
			cek_valid_data('MINSTANSI', 'KODEINSTANSI', $kodeinstansi, 'Instansi');
			cek_valid_data('MJURUBAYAR', 'KODEJURUBAYAR', $kodejurubayar, 'Juru Bayar');
		}
		if ($_POST['PAKET']!='' && $_POST['PAKET']!='PAKET 1' && $_POST['PAKET']!='PAKET 2' && $_POST['PAKET']!='PAKET 3' && $_POST['PAKET']!='PAKET 4'){
			die(json_encode(array('errorMsg' => 'Informasi Paket Tidak Valid, Harus Berisi Informasi Paket 1-4')));
		}

		if (count($a_detail)<1) die(json_encode(array('errorMsg' => 'Detail Transaksi Tidak Boleh Kosong')));

		//cek_valid_data('MLOKASI', 'KODELOKASI', $lokasi, 'Location');
		//cek_valid_data('MSYARATBAYAR', 'KODESYARATBAYAR', $syaratbayar, 'Term Payment');
		cek_valid_data('MCUSTOMER', 'KODECUSTOMER', $customer, 'Customer');
		if ($kodejurubayar!='') cek_valid_data('MJURUBAYAR', 'KODEJURUBAYAR', $kodejurubayar, 'Juru Bayar');
		if ($kodeinstansi!='') cek_valid_data('MINSTANSI', 'KODEINSTANSI', $kodeinstansi, 'Instansi');
		if ($_POST['KODEPROMO']!='') cek_valid_data('MPROMO', 'KODEPROMO', $_POST['KODEPROMO'], 'Promo');
		cek_valid_data('MUSER', 'USERID', $_POST['KODEPEGAWAI_SALES'], 'Salesman');

		$mode = $_POST['mode'];
		if ($mode=='tambah') {
			/*$temp_kode = 'SO/'.$_SESSION['KODELOKASI'].'/';

			$urutan = get_new_urutan('tso', 'kodeso', array($temp_kode, substr($tgltrans, 2, 2)));

			$kodetrans = $temp_kode.$urutan.'/'.substr($tgltrans, 5, 2).'/'.substr($tgltrans, 2, 2);*/

			$temp_kode = 'SO/'.$_SESSION['KODELOKASI'].'/'.substr($tgltrans, 2, 2).substr($tgltrans, 5, 2).substr($tgltrans, -2);

			$urutan = get_max_urutan('tso', 'kodeso', $temp_kode, 4);

			$kodetrans = $temp_kode.$urutan;

			cek_periode($tgltrans,'tambahkan');
		} else {
			cek_periode(get_tgl_trans('tso', 'kodeso', $kodetrans), 'ubah');
			cek_periode($tgltrans,'ubah');
		}

		// start transaction
		$tr = $db->start_trans();

		// query header
		$data_values = array (
			$kodetrans, $lokasi, $namalokasi, $customer, $namacustomer,
			$_POST['KODEPEGAWAI_SALES'], $_POST['KODEREKAMMEDIS'], $_POST['JENIS'], $_POST['KODEJUAL'], $_POST['TIPEORDER'], $_POST['PAKET'],
			$_POST['NOSPMANUAL'], $_POST['KODEPROMO'], $kodeinstansi, $kodejurubayar,
			$tgltrans, $_POST['TGLJANJI'], date("Y.m.d"), date("H:i:s"), $syaratbayar,
			$namasyaratbayar, $tgljatuhtempo, $_SESSION['user'], $_POST['PAKAIPPN'], $_POST['GRANDTOTAL'],
			$_POST['DISKON'], $_POST['DISKONRP'], $_POST['PPNPERSEN'], $_POST['PPNRP'], $_POST['PEMBULATAN'],
			$_POST['GRANDTOTAL'], date("Y.m.d"), $_POST['CASH'], $_POST['KARTUKREDIT'], $_POST['BANKKARTUKREDIT'], $_POST['NOKARTUKREDIT'],
			$_POST['KARTUDEBIT'], $_POST['BANKKARTUDEBIT'], $_POST['NOKARTUDEBIT'], $_POST['ASKES'], $_POST['KODEPENANGGUNGJAWAB'],
			$catatan, 'I', 0
		);
		$exe = $db->insert('tso', $data_values, $tr);
		if (!$exe) { $db->rollback($tr); die(json_encode(array('errorMsg' => 'Simpan Data Gagal <br>Kesalahan Pada Header Data Transaksi'))); }

		// query detail
		$i = 0;
		$sql = $db->insert('tsodtl', 27, $tr, true);
		$pr  = $db->prepare($sql, $tr);
		foreach ($a_detail as $item) {
			$data_values = array (
				$kodetrans, $item->kodebarang, $i, $item->namabarang, $item->jml,
				0, $item->jml, $item->satuan, '', 0,
				$_SESSION['KODECURRENCY'], $item->harga, $item->nilaikurs, $item->harga, $item->disc1,
				$item->discrp1, $item->disc2, $item->discrp2, $item->disc3, $item->discrp3,
				$item->disc4, $item->discrp4, $item->disc5, $item->discrp5, $item->subtotal,
				$item->subtotal, 0
			);

			if ($item->jml > 0) {
				$exe = $db->execute($pr, $data_values);
				if (!$exe) { $db->rollback($tr); die(json_encode(array('errorMsg' => 'Simpan Data Gagal <br>Kesalahan Pada Detail Data Transaksi'))); }
				$i++;
			}

			// CEK PAKET
			// JIKA ADA PAKET DAN BARANG ADALAH FRAME DAN HARGA 0, MAKA MUNCUL ERROR
			if (substr($item->kodebarang, 0, 1) == '1' && $item->harga <= 0 && $_POST['PAKET'] != '') {
				$db->rollback($tr);
				die(json_encode(array('errorMsg' => 'Simpan Data Gagal <br>Jika Paket Diisi, Maka Harga Frame Tidak Boleh 0')));
			}
		}

		$db->commit($tr);

		// panggil fungsi untuk log history
		log_history(
			$kodetrans,
			'SALES ORDER',
			$_POST['KODESO']=='' ? 'INSERT' : 'EDIT',
			array(
				array(
					'nama' => 'header',
					'tabel' => 'tso',
					'kode' => 'KODESO'
				),
				array(
					'nama' => 'detail',
					'tabel' => 'tsodtl',
					'kode' => 'KODESO'
				),
			),
			$_SESSION['user']
		);

		$msg_warning = '';

		echo json_encode(array('success' => true));
	break;

	case 'update_bayar_so' :
		$kodetrans = $_POST['KODESO'];

		// cek jika SO sudah dibayar maka tidak boleh terinput 2x
		$status  = get_status('tso', 'kodeso', $kodetrans);

		if ($status != 'I') {
			die(json_encode(array('errorMsg' => 'No SO sudah Berlanjut')));
		}

		// JIKA ADA DP MINIMAL, LALU PEMBAYARAN DP KURANG DARI DP MINIMAL, MAKA PENANGGUNG JAWAB HARUS DIISI
		if ($_POST['DPMINIMAL'] > 0 ) {
			$total_dp = $_POST['CASH'] + $_POST['KARTUKREDIT'] + $_POST['KARTUDEBIT'] + $_POST['ASKES'];

			if ($total_dp < $_POST['DPMINIMAL'] && $_POST['KODEPENANGGUNGJAWAB']=='') {
				die(json_encode(array('errorMsg' => 'Karena Total DP < DP Minimal, Data Penanggung Jawab Harus Diisi')));
			}
		}

		cek_valid_data('TSO', 'KODESO', $kodetrans, 'Sales Order');

		// start transaction
		$tr = $db->start_trans();

		// query header
		$data_set = array(
			'TGLBAYARDP' => $_POST['TGLBAYARDP'],
			'KODEPENANGGUNGJAWAB' => $_POST['KODEPENANGGUNGJAWAB'],
			'CASH' => $_POST['CASH'],
			'KARTUKREDIT' => $_POST['KARTUKREDIT'],
			'BANKKARTUKREDIT' => $_POST['BANKKARTUKREDIT'],
			'NOKARTUKREDIT' => $_POST['NOKARTUKREDIT'],
			'KARTUDEBIT' => $_POST['KARTUDEBIT'],
			'BANKKARTUDEBIT' => $_POST['BANKKARTUDEBIT'],
			'NOKARTUDEBIT' => $_POST['NOKARTUDEBIT'],
			'ASKES' => $_POST['ASKES'],
			'CATATAN' => $_POST['CATATAN'],
			'STATUS' => 'S',
		);
		$exe = $db->update('tso', $data_set, array('kodeso' => $kodetrans), $tr);
		if (!$exe) { $db->rollback($tr); die(json_encode(array('errorMsg' => 'Simpan Data Gagal <br>Kesalahan Pada Header Data Transaksi'))); }

		$db->commit($tr);

		$tr = $db->start_trans();
		//$exe = $db->delete('valueperkiraan', array('kodetrans'=>$kodetrans));

		$pr = $db->prepare("execute procedure INPUTJURNALPEMBAYARAN(?, ?)", $tr);
		$exe = $db->execute($pr, array('SO', $kodetrans));
		$db->commit($tr);

		// panggil fungsi untuk log history
		log_history(
			$kodetrans,
			'BAYAR SALES ORDER',
			'UPDATE',
			array(
				array(
					'nama' => 'header',
					'tabel' => 'tso',
					'kode' => 'KODESO'
				),
			),
			$_SESSION['user']
		);

		echo json_encode(array('success' => true));
	break;

	case 'batal_bayar_so' :
		$kodetrans = $_POST['kodetrans'];

		cek_valid_data('TSO', 'KODESO', $kodetrans, 'Sales Order');

		// cek SPL, apakah ada SO
		$query = $db->query('select kodepr from tpr where kodeso = \''.$kodetrans.'\' and status <> \'D\'');
		$rs = $db->fetch($query);
		if ($rs->KODEPR != '') {
			die(json_encode(array('errorMsg' => 'Pembatalan Tidak Bisa Dilakukan, No SO ini sudah dipakai di Surat Pesanan Lensa dengan no '.$rs->KODEPR)));
		}

		// cek QC, apakah ada SO
		$query = $db->query('select kodeqc from tqualitycontrol where kodeso = \''.$kodetrans.'\' and status <> \'D\'');
		$rs = $db->fetch($query);
		if ($rs->KODEQC != '') {
			die(json_encode(array('errorMsg' => 'Pembatalan Tidak Bisa Dilakukan, No SO ini sudah dipakai di Quality Control dengan no '.$rs->KODEQC)));
		}

		$tr = $db->start_trans();
		$exe = $db->query("execute procedure INPUTJURNALPENGEMBALIAN('SO','$kodetrans')", $tr);
		$db->commit($tr);

		// start transaction
		$tr = $db->start_trans();

		// query header
		$data_set = array(
			'KODEPENANGGUNGJAWAB' => '',
			'CASH' => 0,
			'KARTUKREDIT' => 0,
			'BANKKARTUKREDIT' => '',
			'NOKARTUKREDIT' => '',
			'KARTUDEBIT' => 0,
			'BANKKARTUDEBIT' => '',
			'NOKARTUDEBIT' => '',
			'ASKES' => 0,
			'CATATAN' => '',
			'STATUS' => 'I',
		);
		$exe = $db->update('tso', $data_set, array('kodeso' => $kodetrans), $tr);
		if (!$exe) { $db->rollback($tr); die(json_encode(array('errorMsg' => 'Simpan Data Gagal <br>Kesalahan Pada Header Data Transaksi'))); }

		//$exe = $db->delete('valueperkiraan', array('kodetrans'=>$kodetrans));

		$db->commit($tr);

		// panggil fungsi untuk log history
		log_history(
			$kodetrans,
			'BAYAR SALES ORDER',
			'DELETE',
			array(
				array(
					'nama' => 'header',
					'tabel' => 'tso',
					'kode' => 'KODESO'
				),
			),
			$_SESSION['user']
		);

		echo json_encode(array('success' => true));
	break;

	case 'load_data' :
		$kodetrans = $_POST['kodetrans'];

		$sql   = "select a.kodelokasi, f.kodegudang, b.*, c.simbol, a.kodecustomer, a.tgltrans,
						 d.tipe, d.namabarangsupplier, g.jenisframe
				  from tso a
				  inner join tsodtl b on a.kodeso=b.kodeso
				  left join mcurrency c on c.kodecurrency=b.kodecurrency
				  inner join mbarang d on b.kodebarang=d.kodebarang
				  inner join mlokasi e on a.kodelokasi = e.kodelokasi
				  inner join mgudang f on e.kodelokasi = f.kodelokasi and f.jenis = 1
				  left join mjenisframe g on d.kodebarang = g.kodebarang
				  where a.kodeso = '$kodetrans'
		          order by b.urutan";
		$query = $db->query($sql);

		$items = array();
		while ($rs = $db->fetch($query)) {
			// parameter tampil stok berasal dari quality control
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
				'jml' => $rs->JML,
				'jmlstok' => $stok,
				'terpenuhi' => $rs->TERPENUHI,
				'sisa' => $rs->SISA,
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
		}

		echo json_encode(array(
			'success' => true,
			'detail' => $items,
		));
	break;

	case 'batal_trans' :
		$kodetrans = $_POST['kodetrans'];
		$status    = get_status('tso', 'kodeso', $kodetrans);

		if ($status=='P') die(json_encode(array('errorMsg' => 'Transaksi Tidak Dapat Dibatalkan')));

		cek_periode(get_tgl_trans('tso', 'kodeso', $kodetrans), 'ubah');

		$tr = $db->start_trans();
		$query = $db->update('tso', array('status' => 'D'), array('kodeso' => $kodetrans), $tr);
		$db->commit($tr);

		// panggil fungsi untuk log history
		log_history(
			$kodetrans,
			'SALES ORDER',
			'DELETE',
			array(
				array(
					'nama' => 'header',
					'tabel' => 'tso',
					'kode' => 'KODESO'
				),
				array(
					'nama' => 'detail',
					'tabel' => 'tsodtl',
					'kode' => 'KODESO'
				),
			),
			$_SESSION['user']
		);

		echo json_encode(array('success' => true));
	break;

}
?>