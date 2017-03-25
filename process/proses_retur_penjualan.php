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

		$kodetrans    	 = $_POST['KODERETURJUAL'];
		//$kode_bbm    	 = $_POST['KODEBBM'];
		$lokasi       	 = $_SESSION['KODELOKASI']; $_POST['KODELOKASI'];
		$namalokasi   	 = $_SESSION['NAMALOKASI'];
		$customer     	 = $_POST['KODECUSTOMER'];
		$namacustomer 	 = $_POST['NAMACUSTOMER'];
		$salesman     	 = '';
		$namasalesman 	 = '';
		$syaratbayar     = $_POST['KODESYARATBAYAR'];
		$namasyaratbayar = $_POST['NAMASYARATBAYAR'];
		$tgltrans        = $_POST['TGLTRANS'];
		$tgljatuhtempo   = $_POST['TGLJATUHTEMPO'];
		$penanggungjawab = $_POST['KODEPENANGGUNGJAWAB'];

		if (count($a_detail)<1) die(json_encode(array('errorMsg' => 'Detail Transaksi Tidak Boleh Kosong')));

		//cek_valid_data('MLOKASI', 'KODELOKASI', $lokasi, 'Lokasi');
		//cek_valid_data('MSYARATBAYAR', 'KODESYARATBAYAR', $syaratbayar, 'Syarat Bayar');
		if ($penanggungjawab <> '')
			cek_valid_data('MUSER', 'USERID', $penanggungjawab, 'Penanggung Jawab');

		cek_valid_data('MCUSTOMER', 'KODECUSTOMER', $customer, 'Customer');

		$mode = $_POST[mode];
		if ($mode=='tambah') {
			/*$temp_kode = 'RJL/'.$_SESSION['KODELOKASI'].'/';

			$urutan = get_new_urutan('treturjual', 'kodereturjual', array($temp_kode, substr($tgltrans, 2, 2)), 6);

			$kodetrans = $temp_kode.$urutan.'/'.substr($tgltrans, 5, 2).'/'.substr($tgltrans, 2, 2);*/

			$temp_kode = 'RJL/'.$_SESSION['KODELOKASI'].'/'.substr($tgltrans, 2, 2).substr($tgltrans, 5, 2);

			$urutan = get_max_urutan('treturjual', 'kodereturjual', $temp_kode, 4);

			$kodetrans = $temp_kode.$urutan;

			cek_periode($tgltrans,'tambahkan');
		} else {
			//cek tgl asal mungkin dilakukan perubahan tanggal
			cek_periode(get_tgl_trans('treturjual', 'kodereturjual', $kodetrans), 'ubah');
			cek_periode($tgltrans,'ubah');

			cek_pemakaian_stok($kodetrans);
		}

		$q = $db->select('tjual', array('tgltrans'), array('kodejual'=>$_POST['KODEJUAL']));
		$r = $db->fetch($q);

		$selisih = strtotime($tgltrans) -  strtotime($r->TGLTRANS);

		if ($selisih < 0) {
			die(json_encode(array('errorMsg' => 'Tanggal Retur Tidak Boleh Kurang Tanggal Jual')));
		}

		// start transaction
		$tr = $db->start_trans();

		// query header
		$data_values = array (
			$kodetrans, $_POST['KODEJUAL'], $lokasi, $namalokasi, $customer,
			$namacustomer, $penanggungjawab, $tgltrans, date("Y.m.d"), date("H:i:s"),
			$syaratbayar, $namasyaratbayar, $tgljatuhtempo, $_SESSION['user'], $_POST['PAKAIPPN'],
			$_POST['TOTAL'], $_POST['DISKON'], $_POST['DISKONRP'], $_POST['PPNPERSEN'], $_POST['PPNRP'],
			$_POST['GRANDTOTAL'], $_POST['CASH'], $_POST['ALASAN'], $_POST['CATATAN'], 'I',
			0
		);
		$exe = $db->insert('treturjual', $data_values, $tr);
		if (!$exe) { $db->rollback($tr); die(json_encode(array('errorMsg' => 'Simpan Data Gagal <br>Kesalahan Pada Header Data Transaksi'))); }

		// query detail
		$i = 0;
		$sql = $db->insert('treturjualdtl', 25, $tr, true);
		$pr  = $db->prepare($sql, $tr);
		foreach ($a_detail as $item) {
			$data_values = array (
				$kodetrans, $item->kodebarang, $i, $item->namabarang, $item->jml,
				$item->satuan, '', 0, $item->hpokok, $item->kodecurrency,
				$item->harga, 1, $item->harga, 0, 0,
				0, 0, 0, 0, 0,
				0, 0, 0, $item->subtotal, $item->subtotal
			);

			if ($item->jml > 0) {
				$exe = $db->execute($pr, $data_values);
				if (!$exe) { $db->rollback($tr); die(json_encode(array('errorMsg' => 'Simpan Data Gagal <br>Kesalahan Pada Detail Data Transaksi'))); }
				$i++;
			}
		}

		//MELAKUKAN TARIK DATA KE VALUE PERKIRAAN
		//$exe = $db->query("execute procedure INPUTJURNALJUAL('RJUAL','$kodetrans')", $tr);
		//if (!$exe) { $db->rollback($tr); die(json_encode(array('errorMsg' => 'Simpan Data Gagal <br>Please Check Jurnal Link Program'))); }

		$db->commit($tr);

		$tr = $db->start_trans();
		$exe = $db->query("execute procedure INPUTJURNALJUAL('RJUAL', '$kodetrans')", $tr);
		$db->commit($tr);

		$tr = $db->start_trans();
		$exe = $db->query("execute procedure INPUTJURNALPEMBAYARAN('RETUR JUAL','$kodetrans')", $tr);
		$db->commit($tr);

		// panggil fungsi untuk log history
		log_history(
			$kodetrans,
			'SALES RETURN',
			$_POST['KODERETURJUAL']=='' ? 'INSERT' : 'EDIT',
			array(
				array(
					'nama' => 'header',
					'tabel' => 'treturjual',
					'kode' => 'KODERETURJUAL'
				),
				array(
					'nama' => 'detail',
					'tabel' => 'treturjualdtl',
					'kode' => 'KODERETURJUAL'
				),
			),
			$_SESSION['user']
		);

		echo json_encode(array('success' => true, 'kodetrans' => $kodetrans));
	break;

	case 'load_data' :
		$kodetrans = $_POST['kodetrans'];

		$sql = "select b.*, c.simbol, b.jml as jmlasal, d.namabarangsupplier, d.tipe, e.jenisframe
				from treturjual a
				inner join treturjualdtl b on a.kodereturjual=b.kodereturjual
				left outer join mcurrency c on c.kodecurrency=b.kodecurrency
				inner join mbarang d on b.kodebarang = d.kodebarang
				left join mjenisframe e on b.kodebarang = e.kodebarang
				where a.kodereturjual = '$kodetrans'
				order by b.urutan";
		$query = $db->query($sql);

		$items = array();
		while ($rs = $db->fetch($query)) {
			$stok = 0;
			if ($_POST['tampil_stok']) {
				$pr = $db->prepare('execute procedure GET_SALDOSTOK(?, ?, ?, ?)');
				$ex = $db->execute($pr, array($rs->KODEBARANG, $_SESSION['KODELOKASI'], '', $_POST['tgltrans']));
				$r = $db->fetch($ex);
				$stok = $r->SALDO;
			}

			$items[] = array(
				'kodebarang' => $rs->KODEBARANG,
				'namabarang' => $rs->NAMABARANG,
				'tipe' => $rs->TIPE,
				'jenisframe' => $rs->JENISFRAME,
				'jmlstok' => $stok,
				'jml' => $rs->JML,
				'satuan' => $rs->SATUAN,
				'konversi' => $rs->KONVERSI,
				'jmlkecil' => $rs->SISA,
				'satuankecil' => $rs->KONVERSI,
				'kodecurrency' => $rs->KODECURRENCY,
				'currency' => $rs->SIMBOL,
				'harga' => $rs->HARGA,
				'hpokok' => $rs->HPOKOK,
				'nilaikurs' => $rs->NILAIKURS,
				'subtotal' => $rs->SUBTOTAL,
				'hargakurs' => $rs->HARGAKURS,
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
				'subtotalkurs' => $rs->SUBTOTALKURS,
			);
		}
		echo json_encode(array(
			'success' => true,
			'detail' => $items,
		));
	break;

	case 'batal_trans' :
		$kodetrans = $_POST[kodetrans];
		$status    = get_status('treturjual', 'kodereturjual', $kodetrans);

		if ($status=='P') die(json_encode(array('errorMsg' => 'Transaksi Tidak Dapat Dibatalkan')));

		cek_periode(get_tgl_trans('treturjual', 'kodereturjual', $kodetrans), 'hapus');

		cek_pemakaian_stok($kodetrans);

		cek_pelunasan('piutang', $kodetrans);

		$tr = $db->start_trans();
		$query = $db->update('treturjual', array('status' => 'D'), array('kodereturjual' => $kodetrans), $tr);
		$db->commit($tr);

		// panggil fungsi untuk log history
		log_history(
			$kodetrans,
			'SALES RETURN',
			'DELETE',
			array(
				array(
					'nama' => 'header',
					'tabel' => 'treturjual',
					'kode' => 'KODERETURJUAL'
				),
				array(
					'nama' => 'detail',
					'tabel' => 'treturjualdtl',
					'kode' => 'KODERETURJUAL'
				),
			),
			$_SESSION['user']
		);

		echo json_encode(array('success' => true));
	break;
}
?>