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

		$kodetrans    	 = $_POST['KODEPO'];
		$lokasi       	 = $_SESSION['KODELOKASI'];
		$namalokasi   	 = $_SESSION['NAMALOKASI'];
		$supplier     	 = $_POST['KODESUPPLIER'];
		$namasupplier 	 = $_POST['NAMASUPPLIER'];
		$syaratbayar     = $_POST['KODESYARATBAYAR'];
		$namasyaratbayar = $_POST['NAMASYARATBAYAR'];
		$catatan         = $_POST['CATATAN'];
		$tgltrans        = $_POST['TGLTRANS'];
		$tgljatuhtempo   = $_POST['TGLJATUHTEMPO'];

		if (count($a_detail)<1) die(json_encode(array('errorMsg' => 'Detail Transaksi Tidak Boleh Kosong')));

		cek_valid_data('MSUPPLIER', 'KODESUPPLIER', $supplier, 'Supplier');
		cek_valid_data('MLOKASI', 'KODELOKASI', $_POST['KODELOKASIPESAN'], 'Lokasi Pemesan');

		$a_msg = array();

		$mode = $_POST[mode];
		if ($mode=='tambah') {
			/*$temp_kode = 'PO/'.$_SESSION['KODELOKASI'].'/';

			$urutan = get_new_urutan('tpo', 'kodepo', array($temp_kode, substr($tgltrans, 2, 2)));

			$kodetrans = $temp_kode.$urutan.'/'.substr($tgltrans, 5, 2).'/'.substr($tgltrans, 2, 2);*/

			$temp_kode = 'PO/'.$_SESSION['KODELOKASI'].'/'.substr($tgltrans, 2, 2).substr($tgltrans, 5, 2);

			$urutan = get_max_urutan('tpo', 'kodepo', $temp_kode, 4);

			$kodetrans = $temp_kode.$urutan;

			cek_periode($tgltrans,'tambahkan');
		} else {
			//cek tgl asal mungkin dilakukan perubahan tanggal
			cek_periode(get_tgl_trans('tpo', 'kodepo', $kodetrans), 'ubah');
			cek_periode($tgltrans,'ubah');
		}
		// start transaction
		$tr = $db->start_trans();

		// query header
		$data_values = array (
			$kodetrans, $lokasi, $namalokasi, $_POST['KODELOKASIPESAN'], $_POST['NAMALOKASIPESAN'], $supplier, $namasupplier,
			$tgltrans, date("Y.m.d"), date("Y.m.d"), date("H:i:s"),
			$syaratbayar, $namasyaratbayar, $tgljatuhtempo, $_SESSION['user'], $_POST['PAKAIPPN'],
			$_POST['TOTAL'], $_POST['DISKON'], $_POST['DISKONRP'], $_POST['PPNPERSEN'], $_POST['PPNRP'],
			$_POST['GRANDTOTAL'], $catatan, 'S', 0
		);
		$exe = $db->insert('tpo', $data_values, $tr);
		if (!$exe) {
			$db->rollback($tr);
			die(json_encode(array('errorMsg' => 'Simpan Data Gagal <br>Kesalahan Pada Header Data Transaksi')));
		}

		$jmlSP = 0;

		// query detail
		$i = 0;
		$sql = $db->insert('tpodtl', 30, $tr, true);
		$pr  = $db->prepare($sql, $tr);
		foreach ($a_detail as $item) {
			$data_values = array (
				$kodetrans, $item->kodepr, $item->kodebarang, $i, $item->namabarangsupplier,
				$item->jml, 0, $item->jml, $item->satuan, '',
				0, $_SESSION['KODECURRENCY'], $item->harga, $item->hargawarna, 1,
				$item->harga, 0, 0, 0, 0,
				0, 0, 0, 0, 0,
				0, $item->subtotal, $item->subtotal, 0, $item->keterangan
			);

			if ($item->jml > 0) {
				$exe = $db->execute($pr, $data_values);
				if (!$exe) { $db->rollback($tr); die(json_encode(array('errorMsg' => 'Simpan Data Gagal <br>Kesalahan Pada Detail Data Transaksi'))); }
				$i++;

				// FIELD NAMABARANG DI TPODTL DI ISI PAKE NAMABARANGSUPPLIER 18.11.2016
				// namabarangsuppliertemp diambil dr master barang
				// namabarangsupplier dari ketika user di detil transaksi
				// jika 2 variabel tsb tidak sama, maka update namabarangsupplier ke master barang
				/*if ($item->namabarangsupplier != $item->namabarangsuppliertemp)
					$db->update('mbarang', array('namabarangsupplier'=>$item->namabarangsupplier), array('kodebarang'=>$item->kodebarang));*/

				if ($item->kodepr <> '')
					$jmlSP++;

				// DI PO, NO SP TIDAK BOLEH DIPAKAI DUA KALI DI PO YANG LAIN
				if ($item->kodepr <> '') {
					$q = $db->query("select a.kodepo
									 from tpo a
									 inner join tpodtl b on a.kodepo = b.kodepo
									 where a.status<>'D' and
										   b.kodepr='$item->kodepr' and
										   b.kodebarang='$item->kodebarang' and
										   b.kodepo<>'$kodetrans'");
					$r = $db->fetch($q);
					if ($r->KODEPO) {
						$db->rollback($tr);
						die(json_encode(array('errorMsg' => 'Simpan Data Gagal <br>No SP '.$item->kodepr.' dengan Barang '.$item->kodebarang.' sudah Diinput PO '.$r->KODEPO)));
					}
				}
			}
		}
		$db->commit($tr);

		$tr = $db->start_trans();
		$exe = $db->query("execute procedure UBAH_STATUS_PR('$kodetrans', 'S')", $tr);
		if (!$exe) { $db->rollback($tr); die(json_encode(array('errorMsg' => 'Ubah Status Surat Pesanan Gagal<br>'))); }
		$db->commit($tr);

/*
		//MELAKUKAN TUTUP DATA PR
		$tr = $db->start_trans();
		$exe = $db->query("execute procedure TUTUP_DETAIL('$kodetrans')", $tr);
		$db->commit($tr);

		//MELAKUKAN TUTUP DATA PR
		$tr = $db->start_trans();
		$exe = $db->query("execute procedure TUTUP_HEADER('$kodetrans')", $tr);
		$db->commit($tr);
*/
		// panggil fungsi untuk log history
		log_history(
			$kodetrans,
			'PURCHASE ORDER',
			$_POST['KODEPO']=='' ? 'INSERT' : 'EDIT',
			array(
				array(
					'nama' => 'header',
					'tabel' => 'tpo',
					'kode' => 'KODEPO'
				),
				array(
					'nama' => 'detail',
					'tabel' => 'tpodtl',
					'kode' => 'KODEPO'
				),
			),
			$_SESSION['user']
		);

		echo json_encode(array('success' => true, 'kodetrans' => $kodetrans, 'SP' => $jmlSP));
	break;

	case 'load_data' :
		$kodetrans = $_POST['kodetrans'];

		$sql   = "select b.*, c.namabarang as namabarangasli, c.tipe
				  from tpo a
				  inner join tpodtl b on a.kodepo=b.kodepo
				  inner join mbarang c on b.kodebarang = c.kodebarang
				  where a.kodepo = '$kodetrans'
		          order by b.urutan";
		$query = $db->query($sql);

		$items = array();
		while ($rs = $db->fetch($query)) {
			$items[] = array(
				'kodepo'		=> $rs->KODEPO,
				'kodepr'		=> $rs->KODEPR,
				'kodebarang'	=> $rs->KODEBARANG,
				'namabarang' 	=> $rs->NAMABARANGASLI,
				'tipe'      	=> $rs->TIPE,
				'namabarangsupplier' => $rs->NAMABARANG, // PAKE NAMABARANG KRN DI TPODTL YG DISIMPAN NAMABARANG
				'jml' 			=> $rs->JML,
				'terpenuhi' 	=> $rs->TERPENUHI,
				'sisa' 			=> $rs->SISA,
				'satuan' 		=> $rs->SATUAN,
				'konversi' 		=> $rs->KONVERSI,
				'kodecurrency' 	=> $rs->KODECURRENCY,
				'currency' 		=> $rs->SIMBOL,
				'harga'	 		=> $rs->HARGA,
				'hargawarna'    => $rs->HARGAWARNA,
				'nilaikurs' 	=> $rs->NILAIKURS,
				'subtotal' 		=> $rs->SUBTOTAL,
				'hargakurs'		=> $rs->HARGAKURS,
				'disc1'			=> $rs->DISC1,
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
				'tutup'			=> $rs->TUTUP,
				'keterangan'	=> $rs->KETERANGAN,
			);
		}
		echo json_encode(array(
			'success' => true,
			'detail' => $items,
		));
	break;

	case 'batal_trans' :
		$kodetrans = $_POST['kodetrans'];
		$status    = get_status('tpo', 'kodepo', $kodetrans);

		if ($status=='P') die(json_encode(array('errorMsg' => 'Transaksi Tidak Dapat Dibatalkan')));

		cek_periode(get_tgl_trans('tpo', 'kodepo', $kodetrans), 'hapus');

		/*
		//MELAKUKAN BUKA DATA PR
		$tr = $db->start_trans();
		$exe = $db->query("execute procedure BUKA_TRANSAKSI('PO','$kodetrans')", $tr);
		$db->commit($tr);
		*/

		$tr = $db->start_trans();
		$query = $db->update('tpo', array('status' => 'D'), array('kodepo' => $kodetrans), $tr);
		$db->commit($tr);

		/*
		//MELAKUKAN TUTUP DATA PR
		$tr = $db->start_trans();
		$exe = $db->query("execute procedure TUTUP_DETAIL('$kodetrans')", $tr);
		$db->commit($tr);

		//MELAKUKAN TUTUP DATA PR
		$tr = $db->start_trans();
		$exe = $db->query("execute procedure TUTUP_HEADER('$kodetrans')", $tr);
		$db->commit($tr);
		*/

		// panggil fungsi untuk log history
		log_history(
			$kodetrans,
			'PURCHASE ORDER',
			'DELETE',
			array(
				array(
					'nama' => 'header',
					'tabel' => 'tpo',
					'kode' => 'KODEPO'
				),
				array(
					'nama' => 'detail',
					'tabel' => 'tpodtl',
					'kode' => 'KODEPO'
				),
			),
			$_SESSION['user']
		);

		echo json_encode(array('success' => true));
	break;

	case 'search_data_SP' :
		$kodesupplier = $_POST['kodesupplier'];
		$kodelokasi = $_POST['kodelokasi'];

		if ($kodelokasi == '') {
			die(json_encode(array('errorMsg' => 'Lokasi Belum Diisi')));
		}

		/*$sql = "select b.*, c.hargabeli, c.tipe, c.namabarangsupplier, d.jenisframe
				from tpr a
				inner join tprdtl b on a.kodepr = b.kodepr
				inner join mbarang c on b.kodebarang = c.kodebarang
				left join mjenisframe d on b.kodebarang = d.kodebarang
				where c.kodesupplier = '$kodesupplier' and
					  a.kodelokasi = '$kodelokasi' and
					  a.status = 'I'";
		*/
		$sql = "select b.*, d.hargabeli, d.tipe, d.namabarangsupplier, e.jenisframe
				from tpr a
				inner join tprdtl b on a.kodepr=b.kodepr
				left join tpodtl c on b.kodepr=c.kodepr and b.kodebarang=c.kodebarang
				inner join mbarang d on b.kodebarang=d.kodebarang
				left join mjenisframe e on b.kodebarang = e.kodebarang
				where d.kodesupplier='$kodesupplier' and
					  a.kodelokasi = '$kodelokasi' and
					  c.kodepo is null and
					  (a.status <> 'D' and a.status <> 'P')";
		$query = $db->query($sql);
		$items = array();
		while ($rs = $db->fetch($query)) {
			// ambil harga terakhir
			$pr = $db->prepare('
				select first 1 skip 0 b.harga
				from tbeli a
				inner join tbelidtl b on a.kodebeli = b.kodebeli
				where a.status <> ? and
					  b.kodebarang = ?
				order by a.tgltrans desc, a.kodebeli asc
			');
			$ex = $db->execute($pr, array('D', $rs->KODEBARANG));
			$r  = $db->fetch($ex);

			$harga_beli = 0;
			if ($r->HARGA == 0 or $r->HARGA == '') {
				$harga_beli = $rs->HARGABELI;
			} else {
				$harga_beli = $r->HARGA;
			}

			$items[] = array(
				'kodepr'		=> $rs->KODEPR,
				'kodebarang'	=> $rs->KODEBARANG,
				'namabarang' 	=> $rs->NAMABARANG,
				'tipe'      	=> $rs->TIPE,
				'jenisframe'   	=> $rs->JENISFRAME,
				'namabarangsupplier' => $rs->NAMABARANGSUPPLIER,
				'jml' 			=> $rs->JML,
				'sisa' 			=> $rs->JML,
				'satuan' 		=> $rs->SATUAN,
				'satuan_lama'	=> $rs->SATUAN,
				'konversi' 		=> $rs->KONVERSI,
				'kodecurrency' 	=> $_SESSION['KODECURRENCY'],
				'currency' 		=> $rs->SIMBOL,
				'harga'	 		=> $harga_beli,
				'hargawarna'	=> 0,
				'nilaikurs' 	=> 1,
				'subtotal' 		=> $harga_beli * $rs->JML,
				'hargakurs'		=> $harga_beli,
				'subtotalkurs' 	=> $harga_beli * $rs->JML,
				'tutup'			=> $rs->TUTUP,
				'keterangan'	=> $rs->KETERANGAN,
			);
		}

		echo json_encode(array('success' => true, 'detail'=>$items));
	break;
}
?>