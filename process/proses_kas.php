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
		$kodetrans  	= $_POST['KODEKAS'];
		$kodelokasi  	= $_SESSION['KODELOKASI']; //$_POST['KODELOKASI'];
		$namalokasi  	= '';//$_POST['NAMALOKASI'];
		$tgltrans   	= ubah_tgl_firebird($_POST['TGLTRANS']);
		$jtrans		 	= $_POST['JENISKAS'];
		$nofakturmanual = $_POST['NOBUKTIMANUAL'];
		$akunkasbank	= $_POST['KODEPERKIRAANKAS']=='' ? '' : $_POST['KODEPERKIRAANKAS'];
		$amount 		= $_POST['AMOUNTKURS']=='' ? 0 : $_POST['AMOUNTKURS'];//$_POST['AMOUNT']=='' ? 0 : $_POST['AMOUNT'];
		$amountkurs 	= $_POST['AMOUNTKURS']=='' ? 0 : $_POST['AMOUNTKURS'];
		$nilaikurs 		= 1;//$_POST['NILAIKURS']=='' ? 0 : $_POST['NILAIKURS'];
		$currency	 	= 'IDR';//$_POST['KODECURRENCY']=='' ? '' : $_POST['KODECURRENCY'];
		$referensi	 	= $_POST['REFERENSI'];
		$keterangan 	= $_POST['KETERANGAN'];
		$totaldebet 	= $_POST['TOTALDEBET']=='' ? 0 : $_POST['TOTALDEBET'];
		$totalkredit 	= $_POST['TOTALKREDIT']=='' ? 0 : $_POST['TOTALKREDIT'];

		$kodetrans_dp 	= $_POST['txt_kodetrans_DP']=='' ? '' : $_POST['txt_kodetrans_DP'];
		$no_giro 		= $_POST['txt_nogiro']=='' ? '' : $_POST['txt_nogiro'];

		// variabel khusus giro masuk dan keluar
		$noBG 	   = $_POST['NOGIRO'];
		$amountBG  = $_POST['AMOUNTGIRO']=='' ? 0 : $_POST['AMOUNTGIRO'];
		$bankBG    = $_POST['NAMABANKGIRO'];
		$tglcairBG = $_POST['TGLCAIRGIRO']=='' ? $tgltrans : ubah_tgl_firebird($_POST['TGLCAIRGIRO']);

		$a_detail = json_decode($_POST['data_detail']);
		$a_detail_giro = json_decode($_POST['data_detail_giro']);
		$a_detail_girotolak = json_decode($_POST['data_detail_girotolak']);

		cek_data($a_detail, 'kodeperkiraan', 'mperkiraan');

		$arr_jtrans = explode(' ', $jtrans);
		if ($jtrans!='MEMORIAL') {
			$singkatan_jtrans = substr($arr_jtrans[0], 0, 1).substr($arr_jtrans[1], 0, 1);
		} else {
			$singkatan_jtrans = 'MM';
		}

		if ($arr_jtrans[0]=='KAS' or $arr_jtrans[0]=='BANK') {
			$q = $db->query("select kodekasbank, kodeperkiraan from mperkiraan where kodeperkiraan = '$akunkasbank'");
			$r = $db->fetch($q);
			$kodekasbank = $r->KODEKASBANK;
			if ($r->KODEPERKIRAAN=='') die(json_encode(array('errorMsg' => 'Anda Belum Memilih Kode Perkiraan Kas/Bank')));
		} else {
			$kodekasbank = '';
		}
		if (count($a_detail)<1) die(json_encode(array('errorMsg' => 'Anda Belum Menambahkan Detail Transaksi')));
		if ($totaldebet<>$totalkredit) die(json_encode(array('errorMsg' => 'Total Debet Harus Sama Dengan Total Kredit')));
		if ($keterangan=='') die(json_encode(array('errorMsg' => 'Anda Belum Mengisi Keterangan')));

		cek_valid_data('mlokasi', 'kodelokasi', $kodelokasi, 'Lokasi');

		$mode = $_POST['mode'];
		if ($mode=='tambah') { // generate kodetrans
			$temp_kodetrans = $singkatan_jtrans.$kodekasbank.'/'.$kodelokasi.'/'.substr($tgltrans, 2, 2).substr($tgltrans, 5, 2).substr($tgltrans, -2);

			$kodetrans = $temp_kodetrans.get_max_urutan('tkas', 'kodekas', $temp_kodetrans, 4);

			/*$temp_kode = $singkatan_jtrans.$kodekasbank.'/'.$kodelokasi.'/';

			$urutan = get_new_urutan('tkas', 'kodekas', array($temp_kode, substr($tgltrans, 2, 2)));

			$kodetrans = $temp_kode.$urutan.'/'.substr($tgltrans, 5, 2).'/'.substr($tgltrans, 2, 2);*/

			cek_periode($tgltrans,'tambahkan');
		} else {
			//cek tgl asal mungkin dilakukan perubahan tanggal
			cek_periode(get_tgl_trans('tkas', 'kodekas', $kodetrans), 'ubah');
			cek_periode($tgltrans,'ubah');
		}

		if ($singkatan_jtrans=='GM' or $singkatan_jtrans=='GK') {
			if ($noBG=='') die(json_encode(array('errorMsg' => 'Anda Belum Mengisi Giro')));
		}

		// start transaction
		$tr  = $db->start_trans();

		// insert tkas
		$data_values = array(
			$kodetrans, $kodelokasi, $nofakturmanual, $jtrans, $noBG, $tgltrans,
			date("Y.m.d"), date("H:i:s"), $referensi, $keterangan, $akunkasbank,
			$currency, $amount, $nilaikurs, $amountkurs, $totaldebet,
			$totalkredit, $_SESSION['user'], 'I', 0
		);
		$exe = $db->insert('tkas', $data_values, $tr);
		if (!$exe) { $db->rollback($tr); die(json_encode(array('errorMsg' => 'Simpan Data Gagal<br>Terdapat Kesalahan Data Header '.ucwords(strtolower($jtrans)).''))); }

		//insert tkasdtl
		$i = 0;
		$sql = $db->insert('tkasdtl', 9, false, true);
		$pr  = $db->prepare($sql, $tr);
		foreach ($a_detail as $item) {
			$data_values = array(
				$kodetrans, $i, $item->kodeperkiraan, $item->keterangan, $item->saldo,
				$item->kodecurrency, $item->amount, $item->nilaikurs, $item->amountkurs
			);
			$exe = $db->execute($pr, $data_values, $tr);
			if (!$exe) { $db->rollback($tr); die(json_encode(array('errorMsg' => 'Simpan Data Gagal<br>Terdapat Kesalahan Data Detail '.ucwords(strtolower($jtrans)).''))); }
			$i++;
		}

		// JIKA ADA PENCAIRAN GIRO
		if (($singkatan_jtrans=='BM' or $singkatan_jtrans=='BK') and strlen($no_giro)>0) {
			$table = $jtrans=='BANK MASUK' ? 'pelunasanpiutang' : 'pelunasanhutang';

			$exe = $db->update('mgiro', array('status' => 'C', 'tglcair' => $tgltrans, 'kodekas' => $kodetrans), array('nogiro' => $no_giro, 'status' => 'G'), $tr);
			if (!$exe) { $db->rollback($tr); die(json_encode(array('errorMsg' => 'Simpan Data Gagal<br>Terdapat Kesalahan Pada Pencairan Giro'))); }

			$exe = $db->update($table, array('kodekas' => $kodetrans, 'statusBG' => 'C', 'tglcairBG' => $tgltrans), array('nobg' => $no_giro, 'statusBG' => 'G'), $tr);
			if (!$exe) { $db->rollback($tr); die(json_encode(array('errorMsg' => 'Simpan Data Gagal<br>Terdapat Kesalahan Pada Update Pelunasan Piutang/Hutang'))); }
		}

		// JIKA ADA TOLAKAN GIRO
		if ($singkatan_jtrans=='GT' and count($a_detail_girotolak)>0) {
			foreach ($a_detail_girotolak as $item) {
				$nogiro = $item->nogiro;

				if (substr($item->nogiro, -1)=='*') {
					$sql = "select count(nogiro) as jml from mgiro where nogiro like '$nogiro%'";
				} else {
					$sql = "select count(nogiro) as jml from mgiro where nogiro = '$nogiro'";
				}
				$query = $db->query($sql);
				$rs	   = $db->fetch($query);

				for ($i=0; $i<$rs->JML; $i++) {
					$nogiro .= '*';
				}

				$exe = $db->update('mgiro', array('nogiro' => $nogiro, 'tglcair' => $tgltrans, 'status' => 'T', 'kodekas' => $kodetrans), array('nogiro' => $item->nogiro, 'status' => 'G'), $tr);
				if (!$exe) { $db->rollback($tr); die(json_encode(array('errorMsg' => 'Simpan Data Gagal<br>Terdapat Kesalahan Pada Update Giro'))); }

				if ($item->jenis=='GIRO MASUK') {
					$table_1 = 'pelunasanpiutang';
					$table_2 = 'pelunasanpiutangdtl';
					$table_3 = 'kartupiutang';
				} else {
					$table_1 = 'pelunasanhutang';
					$table_2 = 'pelunasanhutangdtl';
					$table_3 = 'kartuhutang';
				}

				$query = $db->query("select kodepelunasan from $table_1 where nobg = '$item->nogiro'");
				$rs	   = $db->fetch($query);
				$kodepelunasan = $rs->KODEPELUNASAN;

				$exe = $db->update($table_1, array('nobg' => $nogiro, 'kodekas' => $kodetrans, 'tglcairBG' => $tgltrans, 'statusBG' => 'T'), array('nobg' => $item->nogiro, 'statusBG' => 'G'), $tr);
				if (!$exe) { $db->rollback($tr); die(json_encode(array('errorMsg' => 'Simpan Data Gagal<br>Terdapat Kesalahan Pada Update Giro'))); }

				$sql = "select b.amount as amountpelunasan, c.* from $table_1 a, $table_2 b, $table_3 c
						where a.kodepelunasan='$kodepelunasan'
						and a.kodepelunasan=b.kodepelunasan and b.kodetrans=c.kodetrans order by b.kodetrans asc";
				$query = $db->query($sql);

				while ($rs = $db->fetch($query)) {
					if ($item->jenis=='GIRO MASUK') {
						$data_values = 	array(
							$rs->KODETRANS.'*', $rs->NOFAKTUR, $rs->KODECUSTOMER, '', $tgltrans,
							'01', 0, $tgltrans, date("Y.m.d"), date("H:i:s"),
							$rs->JTRANS, $rs->AMOUNTPELUNASAN, 0, $rs->AMOUNTPELUNASAN, 0,
							$rs->AMOUNTPELUNASAN, $rs->KETERANGAN, 'I'
						);

						$exe = $db->insert($table_3, $data_values, $tr);
					} else {
						$data_values = array(
							$rs->KODETRANS.'*', $rs->NOINVOICESUPPLIER, $rs->KODESUPPLIER, $tgltrans, '01',
							0, $tgltrans, date("Y.m.d"), date("H:i:s"), $rs->JTRANS,
							$rs->AMOUNTPELUNASAN, $rs->DOWNPAYMENT, $rs->AMOUNTPELUNASAN, 0, $rs->AMOUNTPELUNASAN,
							$rs->KETERANGAN, 'I'
						);
						$exe = $db->insert($table_3, $data_values, $tr);
					}
					if (!$exe) { $db->rollback($tr); die(json_encode(array('errorMsg' => 'Simpan Data Gagal<br>Terdapat Kesalahan Pada Pembuatan Hutang/Piutang'))); }
				}
			}
		}

		// JIKA ADA GIRO MASUK/KELUAR
		if ($singkatan_jtrans=='GM' or $singkatan_jtrans=='GK') {
			$data_values = array(
				$noBG, '', $kodetrans, $referensi, $bankBG,
				'', '', $tgltrans, $tglcairBG, $amountBG,
				$jtrans, $_SESSION['user'], date("Y.m.d"), 'G'
			);

			$exe = $db->insert('mgiro', $data_values, $tr);
			if (!$exe) { $db->rollback($tr); die(json_encode(array('errorMsg' => 'Simpan Data Gagal<br>Terdapat Kesalahan Data Giro'))); }
		}

		if ($kodetrans_dp!='') {
			$exe = $db->update('tdownpayment', array('kodekas' => $kodetrans, 'status' => 'S'), array('kodedownpayment' => $kodetrans_dp), $tr);
		}
		$db->commit($tr);

		$jtrans = str_replace('KAS', 'CASH', $jtrans);
		$jtrans = str_replace('MASUK', 'IN', $jtrans);
		$jtrans = str_replace('KELUAR', 'OUT', $jtrans);
		$jtrans = str_replace('TOLAK', 'REJECT', $jtrans);

		// panggil fungsi untuk log history
		log_history(
			$kodetrans,
			$jtrans,
			$_POST['KODEKAS']=='' ? 'INSERT' : 'EDIT',
			array(
				array(
					'nama' => 'header',
					'tabel' => 'tkas',
					'kode' => 'kodekas'
				),
				array(
					'nama' => 'detail',
					'tabel' => 'tkasdtl',
					'kode' => 'kodekas'
				),
				array(
					'nama' => 'giro',
					'tabel' => 'mgiro',
					'kode' => 'kodememo'
				),
			),
			$_SESSION['user']
		);

		echo json_encode(array('success' => true));
	break;

	case 'load_data' :
		$kodetrans = $_POST['kodetrans'];
		$jtrans = substr($kodetrans, 0, 2);

		$sql = "select a.*, b.namaperkiraan as namaperkiraankas
				from tkas a
				left join mperkiraan b on a.kodeperkiraankas = b.kodeperkiraan
				where a.kodekas = '$kodetrans'";
		$query = $db->query($sql);
		$data_header = $db->fetch($query);

		$rows = array();
		$sql = "select a.*, b.namaperkiraan, c.simbol, c.tanda
				from tkasdtl a inner join mperkiraan b on a.kodeperkiraan=b.kodeperkiraan
				left join mcurrency c on a.kodecurrency=c.kodecurrency
				where a.kodekas='$kodetrans' order by a.urutan";
		$query = $db->query($sql);
		while ($rs = $db->fetch($query)) {
			$rows[] = array(
				'kodeperkiraan'	=> $rs->KODEPERKIRAAN,
				'namaperkiraan' => $rs->NAMAPERKIRAAN,
				'saldo'	     	=> $rs->SALDO,
				'amount'	    => $rs->AMOUNT,
				'kodecurrency'	=> $rs->KODECURRENCY,
				'currency'	    => $rs->SIMBOL,
				'tandakurs'	    => $rs->TANDA,
				'nilaikurs'		=> $rs->NILAIKURS,
				'amountkurs'	=> $rs->AMOUNTKURS,
				'keterangan'    => $rs->KETERANGAN,
				'kodebarang'	=> $rs->KODEBARANG,
				'namabarang'	=> $rs->NAMABARANG,
			);
		}

		// JIKA ADA GIRO YG DI CAIRKAN
		$rows_giro = array();
		if ($jtrans=='BM' or $jtrans=='BK') {
			$sql = "select * from mgiro where kodekas = '$kodetrans' and status = 'C'";
			$query = $db->query($sql);
			while ($rs = $db->fetch($query)) {
				$rows_giro[] = array(
					'jenis'		=> $rs->JENIS,
					'nogiro' 	=> $rs->NOGIRO,
					'tglterima'	=> $rs->TGLTERIMA,
					'tglcair'	=> $rs->TGLCAIR,
					'namabank'	=> $rs->NAMABANKGIRO,
					'referensi'	=> $rs->REFERENSI,
					'amount'	=> $rs->AMOUNT,
				);
			}
		}

		// JIKA ADA GIRO YG DITOLAK
		$rows_giro_tolak = array();
		if ($jtrans=='GT') {
			$sql = "select * from mgiro where kodekas = '$kodetrans' and status = 'T'";
			$query = $db->query($sql);
			while ($rs = $db->fetch($query)) {
				$rows_giro_tolak[] = array(
					'jenis'		=> $rs->JENIS,
					'nogiro' 	=> $rs->NOGIRO,
					'tglterima'	=> $rs->TGLTERIMA,
					'tglcair'	=> $rs->TGLCAIR,
					'namabank'	=> $rs->NAMABANKGIRO,
					'referensi'	=> $rs->REFERENSI,
					'amount'	=> $rs->AMOUNT,
				);
			}
		}

		// JIKA ADA GIRO MASUK/KELUAR
		$data_giro = array();
		if ($jtrans=='GM' or $jtrans=='GK') {
			$sql   = "select nogiro,amount as amountgiro,namabankgiro,tglcair as tglcairgiro from mgiro where kodememo = '$kodetrans'";
			$query = $db->query($sql);
			$data_giro  = $db->fetch($query);
		}

		// JIKA ADA DOWN PAYMENT
		$data_dp = '';
		if ($jtrans=='BM' or $jtrans=='BK') {
			$sql   = "select * from tdownpayment where kodekas = '$kodetrans'";
			$query = $db->query($sql);
			$data_dp  = $db->fetch($query);
		}

		echo json_encode(array(
			'success' => true,
			'header' => $data_header,
			'header_giro' => $data_giro,
			'detail' => $rows,
			'detail_giro' => $rows_giro,
			'detail_giro_tolak' => $rows_giro_tolak,
			'data_dp' => $data_dp,
		));
	break;

	case 'batal_trans' :
		$kodetrans = $_POST['kodetrans'];
		$status    = get_status('tkas', 'kodekas', $kodetrans);

		if ($status=='P') die(json_encode(array('errorMsg' => 'Transaksi Tidak Bisa Dibatalkan')));

		cek_periode(get_tgl_trans('tkas', 'kodekas', $kodetrans), 'hapus');

		$tr = $db->start_trans();
		$query = $db->update('tkas', array('status' => 'D'), array('kodekas' => $kodetrans), $tr);
		$db->commit($tr);

		$singkatan1 = substr($kodetrans, 0, 1);
		$singkatan2 = substr($kodetrans, 1, 2);
		if ($singkatan1=='M') {
			$jtrans = 'MEMORIAL';
		} else {
			$jtrans = $singkatan1=='K' ? 'KAS' : ($singkatan1=='B' ? 'BANK' : 'GIRO');
			$jtrans .= ' '.$singkatan2=='M' ? 'MASUK' : 'KELUAR';
		}

		$jtrans = str_replace('KAS', 'CASH', $jtrans);
		$jtrans = str_replace('MASUK', 'IN', $jtrans);
		$jtrans = str_replace('KELUAR', 'OUT', $jtrans);
		$jtrans = str_replace('TOLAK', 'REJECT', $jtrans);

		// panggil fungsi untuk log history
		log_history(
			$kodetrans,
			$jtrans,
			'DELETE',
			array(
				array(
					'nama' => 'header',
					'tabel' => 'tkas',
					'kode' => 'kodekas'
				),
				array(
					'nama' => 'detail',
					'tabel' => 'tkasdtl',
					'kode' => 'kodekas'
				),
				array(
					'nama' => 'giro',
					'tabel' => 'mgiro',
					'kode' => 'kodekas'
				),
			),
			$_SESSION['user']
		);

		echo json_encode(array('success' => true));
	break;

	case 'tampil_giro' :
		$jenis_giro = $_POST['jenis_giro'];

		$sql = "select * from mgiro where status = 'G' and jenis='$jenis_giro'";
		$query = $db->query($sql);
		$rows = array();
		while ($rs = $db->fetch($query)) {
			$rows[] = array(
				'jenis'		=> $rs->JENIS,
				'nogiro' 	=> $rs->NOGIRO,
				'tglterima'	=> $rs->TGLTERIMA,
				'tglcair'	=> $rs->TGLCAIR,
				'namabank'	=> $rs->NAMABANKGIRO,
				'referensi'	=> $rs->REFERENSI,
				'amount'	=> $rs->AMOUNT,
			);
		}

		echo json_encode(array('success' => true, 'data_giro' => $rows));
	break;

	case 'buat_jurnallink' :
		$jenis  = $_POST['jenis'];
		$a_data = json_decode($_POST['data']);
		$rows   = array();

		if (substr($jenis, 0, 9)=='CAIR-GIRO') {
			$sql = "select a.kodeperkiraan, b.namaperkiraan, a.saldo
					from settingjurnallink a inner join mperkiraan b on a.kodeperkiraan = b.kodeperkiraan
					where a.jenis = '$jenis'";
			$query = $db->query($sql);
			$rs = $db->fetch($query);

			$sql = "select distinct c.namasupplier as referensi from
					pelunasanhutang a inner join pelunasanhutangdtl b on a.kodepelunasan=b.kodepelunasan
					inner join msupplier c on a.kodesupplier=c.kodesupplier
					where a.nobg='".$a_data->nogiro."'
					order by c.namasupplier";
			$referensi = '';
			$query = $db->query($sql);
			while ($rs2 = $db->fetch($query)) {
				$referensi .= $rs2->REFERENSI.', ';
			}

			$rows[] = array(
				'kodeperkiraan'	=> $rs->KODEPERKIRAAN,
				'namaperkiraan' => $rs->NAMAPERKIRAAN,
				'saldo'	     	=> $jenis=='CAIR-GIRO-MASUK' ? 'KREDIT' : 'DEBET',
				'amount'	    => $a_data->amount,
				'kodecurrency'	=> $_SESSION['KODECURRENCY'],
				'currency'	    => $_SESSION['SIMBOLCURRENCY'],
				'nilaikurs'		=> 1,
				'amountkurs'	=> $a_data->amount,
				'keterangan'    => 'CAIR GIRO '.$a_data->nogiro.' '.$a_data->namabank.' ('.$referensi.')',
			);
		} else if (substr($jenis, 0, 12)=='TOLAKAN-GIRO') {
			$sql = "select a.kodeperkiraan, b.namaperkiraan, a.saldo
					from settingjurnallink a inner join mperkiraan b on a.kodeperkiraan = b.kodeperkiraan
					where a.jenis like '$jenis%'";
			$query = $db->query($sql);
			while($rs = $db->fetch($query)){
				$rows[] = array(
					'kodeperkiraan'	=> $rs->KODEPERKIRAAN,
					'namaperkiraan' => $rs->NAMAPERKIRAAN,
					'saldo'	     	=> $rs->SALDO,
					'amount'	    => $a_data->amount,
					'kodecurrency'	=> $_SESSION['KODECURRENCY'],
					'currency'	    => $_SESSION['SIMBOLCURRENCY'],
					'nilaikurs'		=> 1,
					'amountkurs'	=> $a_data->amount,
					'keterangan'    => 'GIRO TOLAK '.$a_data->nogiro,
				);
			}
		}
		echo json_encode(array('success' => true, 'data_detail' => $rows));
	break;
}
?>