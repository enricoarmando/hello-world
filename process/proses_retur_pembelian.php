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

		$kodetrans    	 = $_POST['KODERETURBELI'];
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

		if (count($a_detail)<1) die(json_encode(array('errorMsg' => 'Detail Transaksi Tidak Boleh Kosong')));

		cek_valid_data('MLOKASI', 'KODELOKASI', $lokasi, 'Lokasi');
		cek_valid_data('MSUPPLIER', 'KODESUPPLIER', $supplier, 'Supplier');
		cek_valid_data('tbeli', 'kodebeli', $_POST['KODEBELI'], 'No Pembelian');

		$mode = $_POST[mode];
		if ($mode=='tambah') {
			/*$temp_kode = 'RBL/'.$_SESSION['KODELOKASI'].'/';

			$urutan = get_new_urutan('treturbeli', 'kodereturbeli', array($temp_kode, substr($tgltrans, 2, 2)), 6);

			$kodetrans = $temp_kode.$urutan.'/'.substr($tgltrans, 5, 2).'/'.substr($tgltrans, 2, 2);*/

			$temp_kode = 'RBL/'.$_SESSION['KODELOKASI'].'/'.substr($tgltrans, 2, 2).substr($tgltrans, 5, 2);

			$urutan = get_max_urutan('treturbeli', 'kodereturbeli', $temp_kode, 4);

			$kodetrans = $temp_kode.$urutan;

			cek_periode($tgltrans,'tambahkan');
		} else {
			//cek tgl asal mungkin dilakukan perubahan tanggal
			cek_periode(get_tgl_trans('treturbeli', 'kodereturbeli', $kodetrans), 'ubah');
			cek_periode($tgltrans,'ubah');
		}

		$q = $db->select('tbeli', array('tgltrans'), array('kodebeli'=>$_POST['KODEBELI']));
		$r = $db->fetch($q);

		$selisih = strtotime($tgltrans) -  strtotime($r->TGLTRANS);

		if ($selisih < 0) {
			die(json_encode(array('errorMsg' => 'Tanggal Retur Tidak Boleh Kurang Tanggal Beli')));
		}

		// start transaction
		$tr = $db->start_trans();

		// query header
		$data_values = array (
			$kodetrans, $lokasi, $namalokasi, $_POST['NOINVOICESUPPLIER'], $_POST['KODEBELI'],
			$supplier, $namasupplier, $tgltrans, date("Y.m.d"), date("H:i:s"),
			'', '', $tgltrans, $_SESSION['user'], $_POST['PAKAIPPN'],
			$_POST['TOTAL'], $_POST['DISKON'], $_POST['DISKONRP'], $_POST['PPNPERSEN'], $_POST['PPNRP'],
			$_POST['PEMBULATAN'], $_POST['GRANDTOTAL'], $_POST['ALASAN'], $Catatan, 'I',
			0
		);
		$exe = $db->insert('treturbeli', $data_values, $tr);
		if (!$exe) { $db->rollback($tr); die(json_encode(array('errorMsg' => 'Simpan Data Gagal <br>Kesalahan Pada Header Data Transaksi'))); }

		// query detail
		$i = 0;
		$sql = $db->insert('treturbelidtl', 24, $tr, true);
		$pr  = $db->prepare($sql, $tr);
		foreach ($a_detail as $item) {
			$data_values = array(
				$kodetrans, $item->kodebarang, $i, $item->namabarang, $item->jml,
				$item->satuan, '', 0, $item->kodecurrency, $item->harga,
				$item->nilaikurs, $item->hargakurs, $item->disc1, $item->discrp1, $item->disc2,
				$item->discrp2,	$item->disc3, $item->discrp3, $item->disc4, $item->discrp4,
				$item->disc5, $item->discrp5, $item->subtotal, $item->subtotalkurs
			);

			if ($item->jml > 0) {
				$exe = $db->execute($pr, $data_values);
				if (!$exe) { $db->rollback($tr); die(json_encode(array('errorMsg' => 'Simpan Data Gagal <br>Kesalahan Pada Detail Data Transaksi'))); }
				$i++;

				// cek stok
				$stok_cukup = cek_stok ($item->kodebarang, $tgltrans, $item->jml, $item->satuan, $lokasi);
				if (!$stok_cukup) { $db->rollback($tr); die(json_encode(array('errorMsg' => 'stok barang '.$item->kodebarang.' ('.$item->namabarang.') tidak mencukupi'))); }
			}
		}

		$db->commit($tr);

		//INPUT JURNAL LINK
		$tr = $db->start_trans();
		$exe = $db->query("execute procedure INPUTJURNALBELI('RBELI','$kodetrans')", $tr);
		if (!$exe) { $db->rollback($tr); die(json_encode(array('errorMsg' => 'Simpan Data Gagal <br>Please Check Jurnal Link Program'))); }

		/*$tr = $db->start_trans();
		$exe = $db->query("execute procedure TUTUP_DETAIL('$kodetrans')", $tr);
		if (!$exe) { $db->rollback($tr); die(json_encode(array('errorMsg' => 'Simpan Data Gagal <br>Cek Procedure Tutup_Detail'))); }
		$db->commit($tr);

		$tr = $db->start_trans();
		$exe = $db->query("execute procedure TUTUP_HEADER('$kodetrans')", $tr);
		if (!$exe) { $db->rollback($tr); die(json_encode(array('errorMsg' => 'Simpan Data Gagal <br>Cek Procedure Tutup_Detail'))); }
		$db->commit($tr);*/

		// panggil fungsi untuk log history
		log_history(
			$kodetrans,
			'RETUR PEMBELIAN',
			$_POST['KODERETURBELI']=='' ? 'INSERT' : 'EDIT',
			array(
				array(
					'nama'  => 'header',
					'tabel' => 'treturbeli',
					'kode'  => 'kodereturbeli'
				),
				array(
					'nama'  => 'detail',
					'tabel' => 'treturbelidtl',
					'kode'  => 'kodereturbeli'
				),
			),
			$_SESSION['user']
		);

		echo json_encode(array('success' => true));
	break;

	case 'load_data' :
		$kodetrans = $_POST['kodetrans'];

		$sql   = "select b.*, c.simbol, d.tipe, d.namabarangsupplier, e.jenisframe
				  from treturbeli a
				  inner join treturbelidtl b on a.kodereturbeli=b.kodereturbeli
				  left join mcurrency c on c.kodecurrency=b.kodecurrency
				  inner join mbarang d on b.kodebarang=d.kodebarang
				  left join mjenisframe e on b.kodebarang = e.kodebarang
				  where a.kodereturbeli = '$kodetrans'
		          order by b.urutan";
		$query = $db->query($sql);

		$items = array();
		while ($rs = $db->fetch($query)) {
			$items[] = array(
				'kodebarang' 	=> $rs->KODEBARANG,
				'namabarang'	=> $rs->NAMABARANG,
				'tipe'	        => $rs->TIPE,
				'jenisframe'    => $rs->JENISFRAME,
				'namabarangsupplier' => $rs->NAMABARANGSUPPLIER,
				'jmlstok'		=> 0,
				'jmlbeli'		=> 0,
				'jml'			=> $rs->JML,
				'satuan'		=> $rs->SATUAN,
				'konversi'		=> $rs->KONVERSI,
				'jmlkecil'		=> $rs->SISA,
				'satuankecil'	=> $rs->KONVERSI,
				'kodecurrency'	=> $rs->KODECURRENCY,
				'currency' 		=> $rs->SIMBOL,
				'harga' 		=> $rs->HARGA,
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

	case 'load_data_beli' :
		$kodetrans = $_POST['kodetrans'];

		$sql   = "select B.KODEBARANG, B.NAMABARANG, B.JML, B.HARGA, B.HARGAKURS, B.HARGAWARNA, C.SIMBOL, D.TIPE, D.NAMABARANGSUPPLIER,
					     E.JENISFRAME, A.KODELOKASI, B.SATUAN, B.KONVERSI, sum(g.jml) as jmlretur
				  from TBELI A
				  inner join TBELIDTL B on A.KODEBELI = B.KODEBELI
				  inner join MBARANG D on B.KODEBARANG = D.KODEBARANG
				  left join MCURRENCY C on C.KODECURRENCY = B.KODECURRENCY
				  left join MJENISFRAME E on B.KODEBARANG = E.KODEBARANG
				  left join TRETURBELI F on A.KODEBELI = F.KODEBELI and F.STATUS <> 'D'
				  left join TRETURBELIDTL G on F.KODERETURBELI = G.KODERETURBELI and G.KODEBARANG = B.KODEBARANG
				  where A.KODEBELI = '$kodetrans'
				  group by B.KODEBARANG, B.NAMABARANG, B.JML, B.HARGA, B.HARGAKURS, B.HARGAWARNA, C.SIMBOL, D.TIPE, D.NAMABARANGSUPPLIER, E.JENISFRAME, A.KODELOKASI, B.SATUAN, B.KONVERSI";
		$query = $db->query($sql);

		$items = array();
		while ($rs = $db->fetch($query)) {
			// parameter tampil stok berasal dari retur pembelian
			// untuk mengetahui stok terbaru dari lokasi SO
			$stok = 0;
			if ($_POST['tampil_stok']) {
				$pr = $db->prepare('execute procedure GET_SALDOSTOK(?, ?, ?, ?)');
				$ex = $db->execute($pr, array($rs->KODEBARANG, $rs->KODELOKASI, '', $_POST['tgltrans']));
				$r = $db->fetch($ex);
				$stok = $r->SALDO;
			}

			$items[] = array(
				'kodebarang' 	=> $rs->KODEBARANG,
				'namabarang'	=> $rs->NAMABARANG,
				'tipe'	        => $rs->TIPE,
				'jenisframe'    => $rs->JENISFRAME,
				'namabarangsupplier' => $rs->NAMABARANGSUPPLIER,
				'jmlstok'		=> $stok,
				'jmlbeli'		=> $rs->JML,
				'jmlretur'		=> $rs->JMLRETUR,
				'jml'			=> 0,
				'satuan'		=> $rs->SATUAN,
				'konversi'		=> $rs->KONVERSI,
				'jmlkecil'		=> $rs->SISA,
				'satuankecil'	=> $rs->KONVERSI,
				'kodecurrency'	=> $rs->KODECURRENCY,
				'currency' 		=> $rs->SIMBOL,
				'harga' 		=> $rs->HARGA,
				'hargawarna'	=> $rs->HARGAWARNA,
				'nilaikurs' 	=> $rs->NILAIKURS,
				'subtotal' 		=> 0,
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
				'subtotalkurs' 	=> 0,
			);
		}
		echo json_encode(array(
			'success' => true,
			'detail' => $items,
		));
	break;

	case 'batal_trans' :
		$kodetrans = $_POST['kodetrans'];
		$status    = get_status('treturbeli', 'kodereturbeli', $kodetrans);

		if ($status=='P') die(json_encode(array('errorMsg' => 'Transaksi Tidak Dapat Dibatalkan')));

		cek_periode(get_tgl_trans('treturbeli', 'kodereturbeli', $kodetrans), 'hapus');

		$tr = $db->start_trans();
		$query = $db->update('treturbeli', array('status' => 'D'), array('kodereturbeli' => $kodetrans), $tr);
		$db->commit($tr);

		// panggil fungsi untuk log history
		log_history(
			$kodetrans,
			'RETUR PEMBELIAN',
			'DELETE',
			array(
				array(
					'nama'  => 'header',
					'tabel' => 'treturbeli',
					'kode'  => 'kodereturbeli'
				),
				array(
					'nama'  => 'detail',
					'tabel' => 'treturbelidtl',
					'kode'  => 'kodereturbeli'
				),
			),
			$_SESSION['user']
		);

		echo json_encode(array('success' => true));
	break;
}
?>