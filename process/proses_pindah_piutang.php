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
		$data  	   	 = json_decode($_POST['data_detail']);

		$mode		= $_POST['mode'];

		$kodetrans 	= $_POST['KODEPINDAHPIUTANG'];
		$tgltrans  	= ubah_tgl_firebird($_POST['TGLTRANS']);
		$lokasilama	= $_POST['KODELOKASILAMA'];
		$lokasibaru = $_POST['KODELOKASIBARU'];
		$ket		= $_POST['KETERANGAN'];

		$kasbank	 	= $_POST['KODEPERKIRAANKAS'];
		$amount_kasbank = $_POST['AMOUNTPERKIRAANKAS'];

		if ($lokasilama == $lokasibaru) die(json_encode(array('errorMsg' => 'Lokasi Baru Tidak Boleh Sama')));

		cek_valid_data('mlokasi', 'kodelokasi', $lokasilama, 'Lokasi Penjualan');
		cek_valid_data('mlokasi', 'kodelokasi', $lokasibaru, 'Lokasi Baru');
		cek_valid_data('mperkiraan', 'kodeperkiraan', $kasbank, 'Akun Kas/Bank');

		if ($mode=='tambah') {
			/*$temp_kode = 'PK/'.$_SESSION['KODELOKASI'].'/';

			$urutan = get_new_urutan('tpindahpiutang', 'kodepindahpiutang', array($temp_kode, substr($tgltrans, 2, 2)));

			$kodetrans = $temp_kode.$urutan.'/'.substr($tgltrans, 5, 2).'/'.substr($tgltrans, 2, 2);*/

			$temp_kode = 'TPP/'.$lokasibaru.'/'.substr($tgltrans, 2, 2).substr($tgltrans, 5, 2);

			$urutan = get_max_urutan('tpindahpiutang', 'kodepindahpiutang', $temp_kode, 4);

			$kodetrans = $temp_kode.$urutan;

			cek_periode($tgltrans,'tambahkan');
		} else {
			cek_periode(get_tgl_trans('tpindahpiutang', 'kodepindahpiutang', $kodetrans), 'ubah');
			cek_periode($tgltrans,'ubah');
		}

		// start transaction
		$tr = $db->start_trans();

		$data_values = array(
			$kodetrans, $lokasilama, $lokasibaru, $kasbank, $ket,
			$amount_kasbank, $tgltrans, date("Y.m.d"), date("H:i:s"), $_SESSION['user'],
			'S'
		);
		$exe = $db->insert('tpindahpiutang', $data_values, $tr);
		if (!$exe) { $db->rollback($tr); die(json_encode(array('errorMsg' => 'Simpan Data Gagal<br>Terdapat Kesalahan Data Header'))); }

		// LOOPING ARRAY
		$sql = $db->insert('tpindahpiutangdtl', 2, $tr, true);
		$pr  = $db->prepare($sql, $tr);
		foreach ($data as $item) {
			$data_values = array(
				$kodetrans, $item->kodetrans
			);
			$exe = $db->execute($pr, $data_values);
			if (!$exe) { $db->rollback($tr); die(json_encode(array('errorMsg' => 'Simpan Data Gagal<br>Terdapat Kesalahan Data Detail'))); }
		}

		// commit transaction
		$db->commit($tr);

		$tr = $db->start_trans();
		$exe = $db->query("execute procedure inputjurnalpindahpiutang('$kodetrans', 'SIMPAN');", $tr);
		$db->commit($tr);

		echo json_encode(array('success' => true));
	break;

	case 'load_data' :
		$kodetrans = $_POST['kodetrans'];

		$sql = "select b.kodetrans, d.tgltrans, c.kodecustomer, c.namacustomer, d.grandtotal,
					   f.namajurubayar, g.namainstansi, a.kodelokasilama
				from tpindahpiutang a
				inner join tpindahpiutangdtl b on a.kodepindahpiutang = b.kodepindahpiutang
				inner join kartupiutang d on d.kodetrans=b.kodetrans
				left outer join mcustomer c on d.kodecustomer = c.kodecustomer
				left join mjurubayar f on f.kodejurubayar = d.kodejurubayar
				left join minstansi g on g.kodeinstansi = d.kodeinstansi
				where a.kodepindahpiutang = '$kodetrans'
				order by d.tgltrans, b.kodetrans";
		$query = $db->query($sql);

		$json['success'] = true;

		$items = array();
		while ($rs = $db->fetch($query)) {
			$items[] = array(
				'kodetrans'     => $rs->KODETRANS,
				'tgltrans'      => $rs->TGLTRANS,
				'kodecustomer'  => $rs->KODECUSTOMER,
				'namacustomer'	=> $rs->NAMACUSTOMER,
				'namajurubayar'	=> $rs->NAMAJURUBAYAR,
				'namainstansi'	=> $rs->NAMAINSTANSI,
				'total' 		=> $rs->GRANDTOTAL,
			);
			$kodelokasi = $rs->KODELOKASILAMA;
		}

		$json['detail'] = $items;

		$sql = "select a.kodeperkiraan, a.saldo, b.namaperkiraan, a.amountkurs,
				a.keterangan, b.kasbank, a.kodecurrency, c.simbol, a.nilaikurs
				from valueperkiraan a
				inner join mperkiraan b on a.kodeperkiraan = b.kodeperkiraan
				left join mcurrency c on a.kodecurrency=c.kodecurrency
				where a.kodetrans = '$kodetrans' and a.kodelokasi='$kodelokasi'";
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
			}
		}

		$json['detail_perkiraan'] = $items;

		echo json_encode($json);
	break;

	case 'batal_trans' :
		$kodetrans = $_POST['kodetrans'];
		$status    = get_status('tpindahpiutang', 'kodepindahpiutang', $kodetrans);

		if ($status=='P') die(json_encode(array('errorMsg' => 'Transaksi Tidak Dapat Dibatalkan')));

		cek_periode(get_tgl_trans('tpindahpiutang', 'kodepindahpiutang', $kodetrans), 'hapus');

		$tr  = $db->start_trans();
		$query = $db->update('tpindahpiutang', array('status' => 'D'), array('kodepindahpiutang' => $kodetrans), $tr);
		$db->commit($tr);

		echo json_encode(array('success' => true));
	break;

	case 'tampil_data' :
		$temp_sql = '';
		$temp_sql .= $_POST['txt_tgl_aw']!='' ? "and a.tgltrans>='".ubah_tgl_firebird($_POST['txt_tgl_aw'])."'" : '';
		$temp_sql .= $_POST['txt_tgl_ak']!='' ? "and a.tgltrans<='".ubah_tgl_firebird($_POST['txt_tgl_ak'])."'" : '';

		$kodelokasi = $_POST['KODELOKASILAMA'];

		$sql = "select a.kodetrans, a.tgltrans, a.kodecustomer, c.namacustomer,
					   a.grandtotal, d.namajurubayar, e.namainstansi
				from kartupiutang a
				inner join mcustomer c on a.kodecustomer = c.kodecustomer
				left join mjurubayar d on a.kodejurubayar = d.kodejurubayar
				left join minstansi e on a.kodeinstansi = e.kodeinstansi
				where a.kodelokasi = '$kodelokasi' and
					  a.sisa > 0 $temp_sql
				order by a.tgltrans, a.kodetrans";
		$query = $db->query($sql);
		$rows = array();
		while ($rs = $db->fetch($query)) {
			$rows[] = array(
				'kodetrans'     => $rs->KODETRANS,
				'tgltrans'      => $rs->TGLTRANS,
				'kodecustomer'  => $rs->KODECUSTOMER,
				'namacustomer'	=> $rs->NAMACUSTOMER,
				'namajurubayar'	=> $rs->NAMAJURUBAYAR,
				'namainstansi'	=> $rs->NAMAINSTANSI,
				'total' 		=> $rs->GRANDTOTAL,
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
				$amount += $item->total;
			}

			$a_detail[] = insert_detail_kodeperkiraan('PIUTANG', $amount, 'PINDAH PIUTANG DARI '.$_POST['lokasilama'].' KE '.$_POST['lokasibaru']);
		}

		echo json_encode(array('success' => true, 'data_detail' => $a_detail));
	break;
}


function insert_detail_kodeperkiraan($jtrans, $amount, $ket = '') {
	global $db;
	//$db = new DB;

	if ($jtrans=='PIUTANG') {
		$jenis = 'JUAL-PIUTANG';
		$keterangan = $ket;
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
		'saldo' => $amount>0 ? $rs->SALDO : (($rs->SALDO=='DEBET') ? 'KREDIT' : 'DEBET'),
		'nilaikurs' => 1,
		'amount' => $amount>0 ? $amount : (-1 * $amount),
		'amountkurs' => $amount>0 ? $amount : (-1 * $amount),
		'kodecurrency' => $_SESSION['KODECURRENCY'],
		'currency' => $_SESSION['SIMBOLCURRENCY'],
	);
	return $item;
}

?>