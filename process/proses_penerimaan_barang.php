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

		$kodetrans    	 = $_POST['KODEPENERIMAAN'];
		$lokasi       	 = $_SESSION['KODELOKASI'];
		$namalokasi   	 = $_SESSION['NAMALOKASI'];
		$supplier     	 = $_POST['KODESUPPLIER'];
		$namasupplier 	 = $_POST['NAMASUPPLIER'];
		$syaratbayar     = $_POST['KODESYARATBAYAR'];
		$namasyaratbayar = $_POST['NAMASYARATBAYAR'];
		$catatan         = $_POST['CATATAN'];
		$tgltrans        = $_POST['TGLTRANS'];
		$tgljatuhtempo   = $_POST['TGLJATUHTEMPO'];
		$kodepo          = $_POST['KODEPO'];
		if (count($a_detail)<1) die(json_encode(array('errorMsg' => 'Detail Transaksi Tidak Boleh Kosong')));

		cek_valid_data('MSUPPLIER', 'KODESUPPLIER', $supplier, 'Supplier');
		cek_valid_data('TPO', 'KODEPO', $kodepo, 'PO');

		//CEK APAKAH JUMLAH PENERIMAAN LEBIH KECIL SAMA DENGAN JUMLAH ORDER
		foreach ($a_detail as $item) {
			if ($item->jml>$item->jmlorder){
			  die(json_encode(array('errorMsg' => 'Terdapat Jumlah Penerimaan lebih Besar Dari Sisa PO, Transaksi Tidak Dapat Dilanjutkan')));
			}
		}
		$mode = $_POST[mode];
		if ($mode=='tambah') {
			/*$temp_kode = 'PB/'.$_SESSION['KODELOKASI'].'/';

			$urutan = get_new_urutan('tpenerimaan', 'kodepenerimaan', array($temp_kode, substr($tgltrans, 2, 2)));

			$kodetrans = $temp_kode.$urutan.'/'.substr($tgltrans, 5, 2).'/'.substr($tgltrans, 2, 2);*/

			$temp_kode = 'PB/'.$_SESSION['KODELOKASI'].'/'.substr($tgltrans, 2, 2).substr($tgltrans, 5, 2);

			$urutan = get_max_urutan('tpenerimaan', 'kodepenerimaan', $temp_kode, 4);

			$kodetrans = $temp_kode.$urutan;

			cek_periode($tgltrans,'tambahkan');
		} else {
			//cek tgl asal mungkin dilakukan perubahan tanggal
			cek_periode(get_tgl_trans('tpenerimaan', 'kodepenerimaan', $kodetrans), 'ubah');
			cek_periode($tgltrans,'ubah');
		}

		// start transaction
		$tr = $db->start_trans();

		if ($mode == 'ubah') {
			//KEMBALIKAN STOK PO TERLEBIH DAHULU
			//$exe = $db->query("execute procedure BUKA_PO('$kodetrans')", $tr);
		}

		// query header
		$data_values = array (
			$kodetrans, $lokasi, $namalokasi, $_POST['NOINVOICESUPPLIER'], $_POST['KODEPO'],
			$supplier,  $namasupplier, $tgltrans, date("Y.m.d"), date("H:i:s"),
			$syaratbayar, $namasyaratbayar, $tgljatuhtempo, $_SESSION['user'], $_POST['PAKAIPPN'],
			$_POST['TOTAL'], $_POST['DISKON'], $_POST['DISKONRP'], $_POST['PPNPERSEN'], $_POST['PPNRP'],
			$_POST['GRANDTOTAL'], $catatan, 'I', 0
		);

		$exe = $db->insert('tpenerimaan', $data_values, $tr);
		if (!$exe) {
			$db->rollback($tr);
			die(json_encode(array('errorMsg' => 'Simpan Data Gagal <br>Kesalahan Pada Header Data Transaksi')));
		}

		// query detail
		$i = 0;
		$sql = $db->insert('tpenerimaandtl', 15, $tr, true);
		$pr  = $db->prepare($sql, $tr);
		foreach ($a_detail as $item) {
			$data_values = array (
				$kodetrans, $item->kodebarang, $i, $item->namabarang, $item->jml,
				$item->satuan, '', 0, $_SESSION['KODECURRENCY'], $item->harga,
				$item->hargawarna, 1, $item->harga, $item->subtotal, $item->subtotal
			);

			if ($item->jml>0){
				$exe = $db->execute($pr, $data_values);
				if (!$exe) {
					$db->rollback($tr);
					die(json_encode(array('errorMsg' => 'Simpan Data Gagal <br>Kesalahan Pada Detail Data Transaksi')));
				}

				$i++;
			}
		}

		$db->commit($tr);

		$tr = $db->start_trans();
		$exe = $db->query("execute procedure UBAH_TERPENUHI_PO('$kodepo')", $tr);
		$db->commit($tr);

		$tr = $db->start_trans();
		$exe = $db->query("execute procedure TUTUP_PO('$kodetrans')", $tr);
		$db->commit($tr);

		//MELAKUKAN INPUT JURNAL PENERIMAAN
		/*$tr = $db->start_trans();
		$exe = $db->query("execute procedure inputjurnalbeli('PENERIMAAN','$kodetrans')", $tr);
		$db->commit($tr);
		*/
		// panggil fungsi untuk log history
		log_history(
			$kodetrans,
			'PENERIMAAN BARANG',
			$_POST['KODEPENERIMAAN']=='' ? 'INSERT' : 'EDIT',
			array(
				array(
					'nama' => 'header',
					'tabel' => 'tpenerimaan',
					'kode' => 'KODEPENERIMAAN'
				),
				array(
					'nama' => 'detail',
					'tabel' => 'tpenerimaandtl',
					'kode' => 'KODEPENERIMAAN'
				),
			),
			$_SESSION['user']
		);

		echo json_encode(array('success' => true));
	break;

	case 'load_data' :
		$kodetrans = $_POST['kodetrans'];
		$status = $_POST['status'];
		if ($status!='') $status = "and a.status='$status'";

		$sql   = "select b.kodepenerimaan, b.kodebarang, b.jml, b.satuan, b.konversi, b.kodecurrency, b.harga,
						 b.hargawarna, b.nilaikurs, b.hargakurs, b.subtotal, b.subtotalkurs, c.namabarang,
						 c.tipe, e.namabarang as namabarangsupplier, d.jenisframe,
						 sum(e.jml) as jmlorder, sum(e.sisa) as sisa, sum(e.terpenuhi) as terpenuhi
				  from tpenerimaan a
				  inner join tpenerimaandtl b on a.kodepenerimaan=b.kodepenerimaan
				  inner join mbarang c on b.kodebarang = c.kodebarang
				  left join mjenisframe d on b.kodebarang = d.kodebarang
				  left join tpodtl e on b.kodebarang = e.kodebarang and e.kodepo = a.kodepo
				  where a.kodepenerimaan = '$kodetrans' $status
				  group by b.kodepenerimaan, b.kodebarang, b.jml, b.satuan, b.konversi, b.kodecurrency, b.harga,
						   b.hargawarna, b.nilaikurs, b.hargakurs, b.subtotal, b.subtotalkurs, c.namabarang,
						   c.tipe, e.namabarang, d.jenisframe";
		$query = $db->query($sql);

		$items = array();
		while ($rs = $db->fetch($query)) {
			$items[] = array(
				'kodebarang'	 	 => $rs->KODEBARANG,
				'namabarang' 	 	 => $rs->NAMABARANG,
				'tipe'      	 	 => $rs->TIPE,
				'jenisframe'   	 	 => $rs->JENISFRAME,
				'namabarangsupplier' => $rs->NAMABARANGSUPPLIER,
				'jmlorder'			 => $rs->JMLORDER,
				'jml' 			     => $rs->JML,
				'terpenuhi' 	     => $rs->TERPENUHI - $rs->JML,
				'sisa' 		   	     => $rs->SISA + $rs->JML,
				'satuan' 		     => $rs->SATUAN,
				'konversi' 		     => $rs->KONVERSI,
				'kodecurrency' 	     => $rs->KODECURRENCY,
				'currency' 		     => $rs->SIMBOL,
				'harga'	 	       	 => $rs->HARGA,
				'hargawarna'         => $rs->HARGAWARNA,
				'nilaikurs' 	     => $rs->NILAIKURS,
				'subtotal' 		     => $rs->SUBTOTAL,
				'hargakurs'		     => $rs->HARGAKURS,
				'subtotalkurs' 	     => $rs->SUBTOTALKURS,
			);
		}
		echo json_encode(array(
			'success' => true,
			'detail' => $items,
		));
	break;

	case 'batal_trans' :
		$kodetrans = $_POST['kodetrans'];
		$status    = get_status('tpenerimaan', 'kodepenerimaan', $kodetrans);

		if ($status=='P') die(json_encode(array('errorMsg' => 'Transaksi Tidak Dapat Dibatalkan')));

		cek_periode(get_tgl_trans('tpenerimaan', 'kodepenerimaan', $kodetrans), 'hapus');

		/*
		//MELAKUKAN BUKA DATA PR
		$tr = $db->start_trans();
		$exe = $db->query("execute procedure BUKA_TRANSAKSI('PO','$kodetrans')", $tr);
		$db->commit($tr);
		*/

		$tr = $db->start_trans();
		$query = $db->update('tpenerimaan', array('status' => 'D'), array('kodepenerimaan' => $kodetrans), $tr);
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
			'PENERIMAAN BARANG',
			'DELETE',
			array(
				array(
					'nama' => 'header',
					'tabel' => 'tpenerimaan',
					'kode' => 'KODEPENERIMAAN'
				),
				array(
					'nama' => 'detail',
					'tabel' => 'tpenerimaandtl',
					'kode' => 'KODEPENERIMAAN'
				),
			),
			$_SESSION['user']
		);

		echo json_encode(array('success' => true));
	break;

	case 'load_data_po' :
		$kodetrans = $_POST['kodetrans'];

		/*$sql = "select b.kodebarang, c.namabarang, c.namabarangsupplier, c.tipe,
					   b.satuan, b.konversi, b.kodecurrency, b.nilaikurs,
					   b.harga, b.hargawarna, d.jenisframe,
					   sum(b.jml) as jml, sum(b.sisa) as sisa, sum(b.terpenuhi) as terpenuhi
				from tpo a
				inner join tpodtl b on a.kodepo=b.kodepo
				inner join mbarang c on b.kodebarang = c.kodebarang
				left join mjenisframe d on b.kodebarang = d.kodebarang
				where a.kodepo = '$kodetrans' and a.status='S'
				group by b.kodebarang, c.namabarang, c.namabarangsupplier, c.tipe,
						 b.satuan, b.konversi, b.kodecurrency, b.nilaikurs,
						 b.harga, b.hargawarna, d.jenisframe
				having sum(b.sisa) > 0";*/
		// karena bu nur laili minta urutannya harus sesuai dengan PO,
		// maka
		$sql = "select b.kodebarang, c.namabarang, b.namabarang as namabarangsupplier, c.tipe,
					   b.satuan, b.konversi, b.kodecurrency, b.nilaikurs,
					   b.harga, b.hargawarna, d.jenisframe,
					   b.jml, b.sisa, b.terpenuhi
				from tpo a
				inner join tpodtl b on a.kodepo=b.kodepo
				inner join mbarang c on b.kodebarang = c.kodebarang
				left join mjenisframe d on b.kodebarang = d.kodebarang
				where a.kodepo = '$kodetrans' and
					  a.status='S' and
					  b.sisa > 0
				order by b.urutan";
		$query = $db->query($sql);

		$items = array();
		$i = 0;
		while ($rs = $db->fetch($query)) {
			$insert = true;
			if (count($items) > 0) {
				for ($i = 0; $i < count($items); $i++) {
					if ($items[$i]['kodebarang'] == $rs->KODEBARANG) {
						$items[$i]['jmlorder'] += $rs->JML;
						$items[$i]['sisa'] += $rs->SISA;
						$items[$i]['terpenuhi'] += $rs->TERPENUHI;

						$insert = false;
					}
				}
			}

			if ($insert) {
				$items[] = array(
					'kodebarang'	=> $rs->KODEBARANG,
					'namabarang' 	=> $rs->NAMABARANG,
					'namabarangsupplier' => $rs->NAMABARANGSUPPLIER,
					'tipe' 			=> $rs->TIPE,
					'jenisframe'	=> $rs->JENISFRAME,
					'jmlorder' 		=> $rs->JML,
					'jml' 			=> 0,
					'terpenuhi' 	=> $rs->TERPENUHI,
					'sisa' 			=> $rs->SISA,
					'satuan' 		=> $rs->SATUAN,
					'konversi' 		=> $rs->KONVERSI,
					'kodecurrency' 	=> $rs->KODECURRENCY,
					'currency' 		=> $rs->SIMBOL,
					'harga'	 		=> $rs->HARGA,
					'hargawarna'	=> $rs->HARGAWARNA,
					'nilaikurs' 	=> $rs->NILAIKURS,
					'subtotal' 		=> ($rs->HARGA + $rs->HARGAWARNA) *  $rs->SISA,
					'hargakurs'		=> $rs->HARGA + $rs->HARGAWARNA,
					'subtotalkurs' 	=> ($rs->HARGA + $rs->HARGAWARNA) *  $rs->SISA,
				);
			}
		}
		echo json_encode(array(
			'success' => true,
			'detail' => $items,
		));
	break;
}
?>