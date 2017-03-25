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
		$bulan = $_POST['sb_bulan'];
		$tahun = $_POST['txt_tahun'];

		$tgl_aw = date('Y.m.d', mktime(0, 0, 0, $bulan, 1, $tahun));
		$tgl_ak = date('Y.m.t', mktime(0, 0, 0, $bulan, 1, $tahun));

		$kodetrans = 'SPL/'.date('ymt', mktime(0, 0, 0, $bulan, 1, $tahun));

		$exe = $db->select('tsplit', array('kodesplit'), array('kodesplit' => $kodetrans));
		$rs = $db->fetch($exe);

		if ($rs->KODESPLIT <> '') {
			die(json_encode(array('errorMsg' => 'Saving Data Failed...<br>Split Data on this Month has been executed')));
		}

		// start transaction
		$tr = $db->start_trans();

		$path = 'e:/xampp/htdocs/DATABASE/';

		// loop lokasi cabang
		$q = $db->select('mlokasi', array('kodelokasi'), array('pusat'=>0));
		while ($r = $db->fetch($q)) {
			$url_db = $path.$r->KODELOKASI.'.FDB';
			if (file_exists($url_db)) {
				$db2 = new DB();
				$db2->connect($url_db, 'SYSDBA', 'masterkey');
				$tr2 = $db2->start_trans();

				$pr = $db2->prepare('delete from tjual where tgltrans between ? and ?', $tr2);
				$db2->execute($pr, array($tgl_aw, $tgl_ak));

				$sql = 'select *
						from tjual
						where right(kodejual, 4) < ? and
							  tgltrans between ? and ? and
							  kodelokasi = ? and
							  status <> ?';
				$pr = $db->prepare($sql, $tr);
				$ex = $db->execute($pr, array('2001', $tgl_aw, $tgl_ak, $r->KODELOKASI, 'D'));
				while($rs = $db->fetch($ex)){
					$detil = array_values((array) $rs);

					$db2->insert('tjual', $detil, $tr2);
				}

				$sql = 'select b.*
						from tjual a
						inner join tjualdtl b on a.kodejual=b.kodejual
						where right(a.kodejual, 4) < ? and
							  a.tgltrans between ? and ? and
							  a.kodelokasi = ? and
							  a.status <> ?';
				$pr = $db->prepare($sql, $tr);
				$ex = $db->execute($pr, array('2001', $tgl_aw, $tgl_ak, $r->KODELOKASI, 'D'));
				while($rs = $db->fetch($ex)){
					$detil = array_values((array) $rs);

					$db2->insert('tjualdtl', $detil, $tr2);
				}

				$db2->commit($tr2);
				
				$db2->close();
			}
		}

		$data_values = array(
			$kodetrans, $tgl_aw, $tgl_ak, $_SESSION['user'], ''
		);
		$exe = $db->insert('tsplit', $data_values, $tr);
		if (!$exe) { $db->rollback($tr); die(json_encode(array('errorMsg' => 'Transaction Failed <br>Failed on step 1'))); }

		$db->commit($tr);

		echo json_encode(array('success' => true));

		// panggil fungsi untuk log history
		log_history(
			$kodetrans,
			'CLOSING',
			'INSERT',
			array(
				array(
					'nama' => 'header',
					'tabel' => 'tsplit',
					'kode' => 'kodesplit'
				),
			),
			$_SESSION['user']
		);
	break;

	case 'batal_trans' :
		$kodetrans = $_POST['kodetrans'];

		$q = $db->query("select tglawal, tglakhir FROM TSPLIT WHERE kodesplit='$kodetrans'");
		$r = $db->fetch($q);
		$tglawal = $r->TGLAWAL;
		$tglakhir = $r->TGLAKHIR;

		$db->query("DELETE FROM TSPLIT WHERE kodesplit='$kodetrans'");

		$path = 'e:/xampp/htdocs/DATABASE/';

		// loop lokasi cabang
		$q = $db->select('mlokasi', array('kodelokasi'), array('pusat'=>0));
		while ($r = $db->fetch($q)) {
			$url_db = $path.$r->KODELOKASI.'.FDB';

			if (file_exists($url_db)) {
				$db2 = new DB();
				$db2->connect($url_db, 'SYSDBA', 'masterkey');
				$tr2 = $db2->start_trans();

				$pr = $db2->prepare('delete from tjual where tgltrans between ? and ?', $tr2);
				$db2->execute($pr, array($tglawal, $tglakhir));

				$db2->commit($tr2);
			}
		}

		echo json_encode(array('success' => true));
	break;
}
?>