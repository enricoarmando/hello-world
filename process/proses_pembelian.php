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

		$kodetrans    	 = $_POST['KODEBELI'];
		$lokasi       	 = $_SESSION['KODELOKASI'];
		$namalokasi   	 = $_SESSION['NAMALOKASI'];
		$supplier     	 = $_POST['KODESUPPLIER'];
		$namasupplier 	 = $_POST['NAMASUPPLIER'];
		$syaratbayar     = $_POST['KODESYARATBAYAR'];
		$namasyaratbayar = $_POST['NAMASYARATBAYAR'];
		$Catatan         = $_POST['CATATAN'];
		$tgltrans        = $_POST['TGLTRANS'];
		$tglkirim        = date('Y.m.d'); //$_POST['TGLKIRIM'];
		$tgljatuhtempo   = date('Y.m.d'); //$_POST['TGLJATUHTEMPO'];

		$kode_ekspedisi  = $_POST['KODEEKSPEDISI'];
		$noresi  		 = $_POST['NORESI'];
		$tglkirim   	 = $_POST['TGLKIRIM'];

		if (count($a_detail)<1) die(json_encode(array('errorMsg' => 'Detail Transaksi Tidak Boleh Kosong')));

		cek_valid_data('MLOKASI', 'KODELOKASI', $lokasi, 'Lokasi');
		cek_valid_data('MSYARATBAYAR', 'KODESYARATBAYAR', $syaratbayar, 'Syarat Bayar');
		cek_valid_data('MSUPPLIER', 'KODESUPPLIER', $supplier, 'Supplier');
		cek_valid_data('tpenerimaan', 'kodepenerimaan', $_POST['KODEPENERIMAAN'], 'No Penerimaan');

		// cek jangan sampai kodepenerimaan dipakai 2x
		$sql = 'select kodebeli from tbeli where kodepenerimaan=? and status<>? AND KODEBELI<>?';
		$pr = $db->prepare($sql);
		$ex = $db->execute($pr, array($_POST['KODEPENERIMAAN'], 'D', $kodetrans));
		$rs = $db->fetch($ex);

		if ($rs->KODEBELI <> '') {
			die(json_encode(array('errorMsg' => 'No Penerimaan Sudah Dipakai di '.$rs->KODEBELI)));
		}

		$mode = $_POST['mode'];
		if ($mode=='tambah') {
			/*$temp_kode = 'BL/'.$_SESSION['KODELOKASI'].'/';

			$urutan = get_new_urutan('tbeli', 'kodebeli', array($temp_kode, substr($tgltrans, 2, 2)), 6);

			$kodetrans = $temp_kode.$urutan.'/'.substr($tgltrans, 5, 2).'/'.substr($tgltrans, 2, 2);*/

			$temp_kode = 'BL/'.$_SESSION['KODELOKASI'].'/'.substr($tgltrans, 2, 2).substr($tgltrans, 5, 2);

			$urutan = get_max_urutan('tbeli', 'kodebeli', $temp_kode, 4);

			$kodetrans = $temp_kode.$urutan;

			cek_periode($tgltrans,'tambahkan');
		} else {
			//cek tgl asal mungkin dilakukan perubahan tanggal
			cek_periode(get_tgl_trans('tbeli', 'kodebeli', $kodetrans), 'ubah');
			cek_periode($tgltrans,'ubah');

			cek_pemakaian_stok($kodetrans);
		}

		// start transaction
		$tr = $db->start_trans();

		// query header
		$data_values = array (
			$kodetrans, $lokasi, $namalokasi, $_POST['NOINVOICESUPPLIER'], $_POST['KODEPENERIMAAN'],
			$supplier, $namasupplier, '', $tgltrans, date("Y.m.d"),
			date("H:i:s"), $syaratbayar, $namasyaratbayar, $tgljatuhtempo, $_SESSION['user'],
			$_POST['PAKAIPPN'], $_POST['TOTAL'], $_POST['DISKON'], $_POST['DISKONRP'], $_POST['PPNPERSEN'],
			$_POST['PPNRP'], $_POST['KODEPERKIRAANBIAYA'], $_POST['BIAYA'], $_POST['GRANDTOTAL'], $Catatan, 'S', 0
		);
		$exe = $db->insert('tbeli', $data_values, $tr);
		if (!$exe) { $db->rollback($tr); die(json_encode(array('errorMsg' => 'Simpan Data Gagal <br>Kesalahan Pada Header Data Transaksi'))); }

		// query detail
		$i = 0;
		$sql = $db->insert('tbelidtl', 29, $tr, true);
		$pr  = $db->prepare($sql, $tr);
		foreach ($a_detail as $item) {
			$data_values = array(
				$kodetrans, $item->kodebarang, $i, $item->namabarang, $item->jml,
				$item->satuan, '', 0, 0, $item->satuan,
				'', 0, $item->kodecurrency, $item->harga, $item->hargawarna,
				$item->nilaikurs, $item->harga, 0, 0, 0,
				0, 0, 0, 0, 0,
				0, 0, $item->subtotal, $item->subtotal
			);

			if ($item->jml > 0) {
				$exe = $db->execute($pr, $data_values);
				if (!$exe) { $db->rollback($tr); die(json_encode(array('errorMsg' => 'Simpan Data Gagal <br>Kesalahan Pada Detail Data Transaksi'))); }
				$i++;
			}
		}

		$db->commit($tr);

		$tr = $db->start_trans();
		$exe = $db->query("execute procedure TUTUP_PENERIMAAN('$kodetrans','P')", $tr);
		$db->commit($tr);

		//INPUT JURNAL LINK
		$tr = $db->start_trans();
		$exe = $db->query("execute procedure INPUTJURNALBELI('BELI', '$kodetrans')", $tr);
		if (!$exe) { $db->rollback($tr); die(json_encode(array('errorMsg' => 'Simpan Data Gagal <br>Please Check Jurnal Link Program'))); }
		$db->commit($tr);

		// panggil fungsi untuk log history
		log_history(
			$kodetrans,
			'PEMBELIAN',
			$_POST['KODEBELI']=='' ? 'INSERT' : 'EDIT',
			array(
				array(
					'nama'  => 'header',
					'tabel' => 'tbeli',
					'kode'  => 'kodebeli'
				),
				array(
					'nama'  => 'detail',
					'tabel' => 'tbelidtl',
					'kode'  => 'kodebeli'
				),
			),
			$_SESSION['user']
		);

		echo json_encode(array('success' => true));
	break;

	case 'load_data' :
		$kodetrans = $_POST['kodetrans'];

		$sql   = "select b.*, c.simbol, d.tipe, d.namabarangsupplier, e.jenisframe, a.kodelokasi
				  from tbeli a
				  inner join tbelidtl b on a.kodebeli=b.kodebeli
				  left join mcurrency c on c.kodecurrency=b.kodecurrency
				  inner join mbarang d on b.kodebarang=d.kodebarang
				  left join mjenisframe e on b.kodebarang = e.kodebarang
				  where a.kodebeli = '$kodetrans'
		          order by b.urutan";
		$query = $db->query($sql);

		$items = array();
		while ($rs = $db->fetch($query)) {
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
				'kodebarang' 	=> $rs->KODEBARANG,
				'namabarang'	=> $rs->NAMABARANG,
				'tipe'	        => $rs->TIPE,
				'jenisframe'    => $rs->JENISFRAME,
				'namabarangsupplier' => $rs->NAMABARANGSUPPLIER,
				'jmlasal'		=> $rs->JMLPO,
				'jmlbb'			=> $rs->JMLBBM,
				'jmlstok'		=> $stok,
				'jml'			=> $rs->JML,
				'jmlbonus'		=> $rs->JMLBONUS,
				'satuan'		=> $rs->SATUAN,
				'konversi'		=> $rs->KONVERSI,
				'jmlkecil'		=> $rs->SISA,
				'satuankecil'	=> $rs->KONVERSI,
				'kodecurrency'	=> $rs->KODECURRENCY,
				'currency' 		=> $rs->SIMBOL,
				'harga' 		=> $rs->HARGA,
				'hargawarna'	=> $rs->HARGAWARNA,
				'nilaikurs' 	=> $rs->NILAIKURS,
				'subtotal' 		=> $rs->SUBTOTAL,
				'hargakurs' 	=> $rs->HARGAKURS,
				'disc1' 		=> $rs->DISC1,
				'disc2' 		=> $rs->DISC2,
				'disc3' 		=> $rs->DISC3,
				'disc4' 		=> $rs->DISC4,
				'disc5' 		=> $rs->DISC5,
				'discrp1' 		=> $rs->DISCRP1,
				'discrp2' 		=> $rs->DISCRP2,
				'discrp3' 		=> $rs->DISCRP3,
				'discrp4' 		=> $rs->DISCRP4,
				'discrp5' 		=> $rs->DISCRP5,
				'subtotalkurs' 	=> $rs->SUBTOTALKURS,
			);
		}
		echo json_encode(array(
			'success' => true,
			'detail' => $items,
		));
	break;

	case 'batal_trans' :
		$kodetrans = $_POST['kodetrans'];
		$status    = get_status('tbeli', 'kodebeli', $kodetrans);

		if ($status=='P') die(json_encode(array('errorMsg' => 'Transaksi Tidak Dapat Dibatalkan')));

		cek_periode(get_tgl_trans('tbeli', 'kodebeli', $kodetrans), 'hapus');

		cek_pemakaian_stok($kodetrans);

		$tr = $db->start_trans();
		$query = $db->update('tbeli', array('status' => 'D', 'kodepenerimaan'=>''), array('kodebeli' => $kodetrans), $tr);
		$db->commit($tr);

		// panggil fungsi untuk log history
		log_history(
			$kodetrans,
			'PEMBELIAN',
			'DELETE',
			array(
				array(
					'nama'  => 'header',
					'tabel' => 'tbeli',
					'kode'  => 'kodebeli'
				),
				array(
					'nama'  => 'detail',
					'tabel' => 'tbelidtl',
					'kode'  => 'kodebeli'
				),
			),
			$_SESSION['user']
		);

		echo json_encode(array('success' => true));
	break;

}
?>