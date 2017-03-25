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
		$kodetrans 	 = $_POST['KODEPELUNASAN'];
		$data  	   	 = json_decode($_POST['data_detail']);
		$tgltrans  	 = ubah_tgl_firebird($_POST['TGLTRANS']);
		$nokas	   	 = $_POST['KODEKAS']=='' ? '' : $_POST['KODEKAS'];
		$amount	   	 = $_POST['AMOUNTKAS'];
		$nobg	   	 = $_POST['NOBG'];

		$kode_akun_kas	 = $_POST['KODEPERKIRAANKAS']=='' ? '' : $_POST['KODEPERKIRAANKAS'];
		$amount_akun_kas = $_POST['AMOUNTPERKIRAANKAS'];

		$ket 	   	 = $_POST['KETERANGAN'];
		$mode		 = $_POST['mode'];
		$jenis_pelunasan = 0; $_POST['rd_jenis_trans'];

		$a_perkiraan = json_decode($_POST['data_detail_perkiraan']);

		cek_data($a_perkiraan, 'kodeperkiraan', 'mperkiraan');

		cek_valid_data('mlokasi', 'kodelokasi', $_POST['KODELOKASI'], 'Lokasi');
		//cek_valid_data('minstansi', 'kodeinstansi', $_POST['KODEINSTANSI'], 'Instansi');

		if (count($data)<1) die(json_encode(array('errorMsg' => 'Anda Belum Memilih Data Pelunasan')));
		if ($jenis_pelunasan==1) {
			if ($nokas=='') die(json_encode(array('errorMsg' => 'Anda Belum Memilih No Kas/Bank/Giro')));
		} else {
			if ($kode_akun_kas=='') die(json_encode(array('errorMsg' => 'Anda Belum Memilih Kode Akun Kas/Bank')));
			if ($amount_akun_kas=='') die(json_encode(array('errorMsg' => 'Anda Belum Mengisi Amount Kurs Kas/Bank')));

			$a_perkiraan[] = (object) array(
				'kodeperkiraan' => $kode_akun_kas,
				'namaperkiraan' => $_POST['NAMAPERKIRAANKAS'],
				'keterangan' => $ket,
				'saldo' => 'DEBET',
				'amount' => $amount_akun_kas,
				'nilaikurs' => 1,
				'amountkurs' => $amount_akun_kas,
				'kodecurrency' => $_SESSION['KODECURRENCY'],
				'currency' => $_SESSION['SIMBOLCURRENCY'],
			);
		}

		// HITUNG TOTAL DEBET/KREDIT
		$total_debet  = 0;
		$total_kredit = 0;
		foreach ($a_perkiraan as $item) {
			if ($item->saldo=='DEBET') {
				$total_debet += $item->amount;
			} else if ($item->saldo=='KREDIT') {
				$total_kredit += $item->amount;
			}

			if ($item->keterangan=='') die(json_encode(array('errorMsg' => 'Anda Belum Mengisi Detail Data Keterangan')));
			if ($item->kodeperkiraan=='') die(json_encode(array('errorMsg' => 'Kode Perkiraan Ada Yang Belum Diisi')));
		}

		// CEK TOTAL DEBET DG TOTAL KREDIT
		if ($total_debet<>$total_kredit) die(json_encode(array('errorMsg' => 'Total Debet Masih Belum Sama Dengan Total Kredit')));

		if (substr($nokas, 0, 2)=='GM') {
			$query    = $db->query("select * from mgiro where kodememo = '$nokas'");
			$rs 	  = $db->fetch($query);

			$kodekas   = $rs->KODEKAS;
			$kodememo  = $rs->KODEMEMO;
			$tglcairbg = ubah_tgl_firebird(ubah_tgl_indo($rs->TGLCAIR));
			$namabank  = $rs->NAMABANKGIRO;
			$statusbg  = $rs->STATUS;
		} else {
			$kodekas   = $nokas;
			$kodememo  = '';
			$tglcairbg = $tgltrans;
			$namabank  = '';
			$statusbg  = '';
		}

		if ($mode=='tambah') {
			/*$temp_kode = 'PP/'.$_POST['KODELOKASI'].'/';

			$urutan = get_new_urutan('pelunasanpiutangkredit', 'kodepelunasan', array($temp_kode, substr($tgltrans, 2, 2)));

			$kodetrans = $temp_kode.$urutan.'/'.substr($tgltrans, 5, 2).'/'.substr($tgltrans, 2, 2);*/

			$temp_kode = 'PKK/'.$_SESSION['KODELOKASI'].'/';

			$urutan = get_max_urutan('pelunasanpiutangkredit', 'kodepelunasan', $temp_kode, 4);

			$kodetrans = $temp_kode.$urutan;
		} else {
			//$kodetrans = $_POST['KODEPELUNASAN'];
		}

		// start transaction
		$tr = $db->start_trans();

		$data_values = array(
			$kodetrans, $_POST['KODELOKASI'], $tgltrans, date("Y.m.d"), date("H:i:s"),
			$_SESSION['user'], $total_debet, $ket, 'S', 0
		);
		$exe = $db->insert('pelunasanpiutangkredit', $data_values, $tr);
		if (!$exe) { $db->rollback($tr); die(json_encode(array('errorMsg' => 'Simpan Data Gagal<br>Terdapat Kesalahan Data Pelunasan Piutang'))); }

		// LOOPING ARRAY PELUNASAN
		$sql = $db->insert('pelunasanpiutangkreditdtl', 4, $tr, true);
		$pr  = $db->prepare($sql, $tr);
		foreach ($data as $item) {
			$data_values = array(
				$kodetrans, $item->kodecustomer, $item->kodetrans, $item->pelunasan,
			);
			$exe = $db->execute($pr, $data_values);
			if (!$exe) { $db->rollback($tr); die(json_encode(array('errorMsg' => 'Simpan Data Gagal<br>Terdapat Kesalahan Data Detail Pelunasan Piutang'))); }
		}

		// BUAT JURNAL LINK
		$sql = $db->insert('valueperkiraan', 11, $tr, true);
		$pr  = $db->prepare($sql, $tr);
		foreach ($a_perkiraan as $item) {
			$data_values = array(
				$kodetrans, $_POST['KODELOKASI'], $item->kodeperkiraan, $tgltrans, $item->saldo,
				$item->keterangan, $item->kodecurrency, $item->amount, $item->nilaikurs, $item->amount,
				1
			);
			$exe = $db->execute($pr, $data_values);

			if (!$exe) { $db->rollback($tr); die(json_encode(array('errorMsg' => 'Simpan Data Gagal<br>Terdapat Kesalahan Data Jurnal Transaksi Pelunasan Piutang'))); }
		}
		// commit transaction
		$db->commit($tr);

		$tr  = $db->start_trans();
		$query = $db->update('pelunasanpiutangkredit', array('status' => 'S'), array('kodepelunasan' => $kodetrans), $tr);
		$db->commit($tr);

		$tr  = $db->start_trans();
		$pr  = $db->prepare('execute procedure tutup_kartu_piutang_kredit(?)', $tr);
		$exe = $db->execute($pr, $kodetrans);
		$db->commit($tr);

		echo json_encode(array('success' => true));
	break;

	case 'load_data' :
		$kodetrans = $_POST['kodetrans'];

		$sql = "select b.kodetrans, d.tgltrans, b.amount, d.grandtotal, d.sisa, c.kodecustomer, c.namacustomer
				from pelunasanpiutangkredit a
				inner join pelunasanpiutangkreditdtl b on a.kodepelunasan = b.kodepelunasan
				inner join kartupiutangkredit d on d.kodetrans=b.kodetrans
				left join mcustomer c on b.kodecustomer = c.kodecustomer
				where a.kodepelunasan = '$kodetrans'
				order by d.tgltrans,b.kodetrans";

		$query = $db->query($sql);

		$json['success'] = true;

		$items = array();
		while ($rs = $db->fetch($query)) {
			$items[] = array(
				'kodetrans' => $rs->KODETRANS,
				'tgltrans' => $rs->TGLTRANS,
				'kodecustomer' => $rs->KODECUSTOMER,
				'namacustomer' => $rs->NAMACUSTOMER,
				'total' => $rs->GRANDTOTAL,
				'sisa' => $rs->SISA,
				'pelunasan' => $rs->AMOUNT,
			);
		}

		$json['detail'] = $items;

		$sql = "select a.kodeperkiraan, a.saldo, b.namaperkiraan, a.amountkurs,
				a.keterangan, b.kasbank, a.kodecurrency, c.simbol, a.nilaikurs
				from valueperkiraan a
				inner join mperkiraan b on a.kodeperkiraan = b.kodeperkiraan
				left join mcurrency c on a.kodecurrency=c.kodecurrency
				where a.kodetrans = '$kodetrans'";
		$query = $db->query($sql);

		$items = array();
		while ($rs = $db->fetch($query)) {
			if ($rs->KASBANK==0) {
				$items[] = array(
					'kodeperkiraan' => $rs->KODEPERKIRAAN,
					'namaperkiraan' => $rs->NAMAPERKIRAAN,
					'keterangan' => $rs->KETERANGAN,
					'saldo' => $rs->SALDO,
					'nilaikurs' => $rs->NILAIKURS,
					'amount' => $rs->AMOUNTKURS,
					'amountkurs' => $rs->AMOUNTKURS,
					'kodecurrency' => $rs->KODECURRENCY,
					'currency' => $rs->SIMBOL,
				);
			} else {
				$json['data_kasbank'] = $rs;
			}
		}

		$json['detail_perkiraan'] = $items;

		echo json_encode($json);
	break;

	case 'batal_trans' :
		$kodetrans = $_POST['kodetrans'];
		$status    = get_status('pelunasanpiutangkredit', 'kodepelunasan', $kodetrans);

		if ($status=='P') die(json_encode(array('errorMsg' => 'Transaksi Tidak Dapat Dibatalkan')));

		$tr  = $db->start_trans();
		$query = $db->update('pelunasanpiutangkredit', array('status' => 'D'), array('kodepelunasan' => $kodetrans), $tr);
		$db->commit($tr);

		$tr  = $db->start_trans();
		$pr  = $db->prepare('execute procedure tutup_kartu_piutang_kredit(?)', $tr);
		$exe = $db->execute($pr, $kodetrans);
		$db->commit($tr);

		echo json_encode(array('success' => true));
	break;

	case 'tampil_data' :
		$temp_sql = '';
		$temp_sql .= $_POST['txt_tgl_aw']!='' ? "and a.tgltrans>='".ubah_tgl_firebird($_POST['txt_tgl_aw'])."'" : '';
		$temp_sql .= $_POST['txt_tgl_ak']!='' ? "and a.tgltrans<='".ubah_tgl_firebird($_POST['txt_tgl_ak'])."'" : '';

		$sql = "select * from (
					select 2 as urutan, a.kodetrans, a.tgltrans, a.grandtotal, a.sisa, c.kodecustomer, c.namacustomer
					from kartupiutangkredit a
					inner join tjual b on a.kodetrans = b.kodejual
					inner join mcustomer c on b.kodecustomer = c.kodecustomer
					where a.sisa!=0 $temp_sql and
						  a.kodelokasi='".$_POST['KODELOKASI']."'

					union all

					select 1 as urutan, a.kodetrans, a.tgltrans, a.grandtotal, a.sisa, c.kodecustomer, c.namacustomer
					from kartupiutangkredit a
					inner join tso b on a.kodetrans = b.kodeso
					inner join mcustomer c on b.kodecustomer = c.kodecustomer
					where a.sisa!=0 $temp_sql and
						a.kodelokasi='".$_POST['KODELOKASI']."'
				) order by tgltrans, urutan, kodetrans";
		$query = $db->query($sql);
		$rows = array();
		while ($rs = $db->fetch($query)) {
			$rows[] = array(
				'kodetrans' => $rs->KODETRANS,
				'tgltrans' => $rs->TGLTRANS,
				'kodecustomer' => $rs->KODECUSTOMER,
				'namacustomer' => $rs->NAMACUSTOMER,
				'total' => $rs->GRANDTOTAL,
				'sisa' => $rs->SISA,
				'pelunasan' => 0,
			);
		}
		echo json_encode(array('success' => true, 'data_detail' => $rows));
	break;

	case 'buat_jurnallink' :
		$a_detail = array();
		$a_data   = json_decode($_POST['data']);
		$amount   = 0;
		$amount_kasbank = $_POST['total_kasbank'];
		if (count($a_data)>0) {
			foreach($a_data as $item){
				$amount += $item->pelunasan;
			}

			$a_detail[] = insert_detail_kodeperkiraan('PIUTANG', $amount, $_POST['instansi']);
		}

		if ($amount_kasbank<>0) {
			$a_detail[] = insert_detail_kodeperkiraan('AYAT SILANG', $amount<0 ? (-1 * $amount_kasbank) : $amount_kasbank);
		}

		echo json_encode(array('success' => true, 'data_detail' => $a_detail));
	break;
}

function insert_detail_kodeperkiraan($jtrans, $amount, $ket = '') {
	global $db;
	//$db = new DB;

	if ($jtrans=='PIUTANG') {
		$jenis = 'JUAL-KARTU_KREDIT';
		$keterangan = 'ANGSURAN KARTU KREDIT';
	} else if ($jtrans=='AYAT SILANG') {
		$jenis = 'PELUNASAN-PIUTANG';
		$keterangan = 'PELUNASAN PIUTANG';
	}

	$sql = "select a.kodeperkiraan, b.namaperkiraan, a.jenis, a.saldo
			from settingjurnallink a
			inner join mperkiraan b on a.kodeperkiraan = b.kodeperkiraan
			where a.jenis = '$jenis' and a.kodelokasi = '".$_SESSION['KODELOKASI']."'";
	$query = $db->query($sql);
	$rs    = $db->fetch($query);

	$item = array(
		'tanda' => 1,
		'kodeperkiraan' => $rs->KODEPERKIRAAN,
		'namaperkiraan' => $rs->NAMAPERKIRAAN,
		'keterangan' => $keterangan,
		'saldo' => $amount<0 ? $rs->SALDO : (($rs->SALDO=='DEBET') ? 'KREDIT' : 'DEBET'),
		'nilaikurs' => 1,
		'amount' => $amount>0 ? $amount : (-1 * $amount),
		'amountkurs' => $amount>0 ? $amount : (-1 * $amount),
		'kodecurrency' => $_SESSION['KODECURRENCY'],
		'currency' => $_SESSION['SIMBOLCURRENCY'],
	);
	return $item;
}

?>