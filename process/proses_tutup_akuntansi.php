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

		$kodetrans = 'CLSA/'.$_SESSION['KODELOKASI'].'/'.date('ymt', mktime(0, 0, 0, $bulan, 1, $tahun));

		$exe = $db->select('mclosing', array('kodeclosing'), array('kodeclosing' => $kodetrans, 'jenisclosing'=>'AKUNTANSI'));
		$rs = $db->fetch($exe);

		if ($rs->KODECLOSING <> '') {
			die(json_encode(array('errorMsg' => 'Saving Data Failed...<br>Closing Statement on this Month has been Closed')));
		}

		// start transaction
		$tr = $db->start_trans();

		// hapus data saldoperkiraan
		$query = $db->delete('saldoperkiraan', array('kodesaldoperkiraan'=>$kodetrans), $tr);

		// hapus valueperkiraan
		$query = $db->delete('valueperkiraan', array('kodetrans'=>$kodetrans), $tr);

		// insert saldoperkiraan
		$data_values = array(
			$kodetrans, $_SESSION['KODELOKASI'], $tgl_ak, date("Y.m.d"), date("H:i:s"),
			$_SESSION['user'], '', 'T'
		);
		$exe = $db->insert('saldoperkiraan', $data_values, $tr);
		if (!$exe) { $db->rollback($tr); die(json_encode(array('errorMsg' => 'Transaction Failed <br>Failed on step 1'))); }

		// insert saldoperkiraandtl
		$i = 0;
		$sql = 'insert into saldoperkiraandtl values (?, ?, ?, ?, ?,
													  ?, ?, ?)';
		$pr  = $db->prepare($sql, $tr);

		// looping mperkiraan
		$sql = "select kodeperkiraan, namaperkiraan, saldo, kelompok
				from mperkiraan
				where status = 1 and
					  tipe = 'DETAIL' and
					  kodeperkiraan <> '".$_SESSION['KODELABARUGI']."'
				order by kodeperkiraan";
		$q = $db->query($sql);
		while ($r = $db->fetch($q)) {
			$kode_perkiraan = $r->KODEPERKIRAAN;

			if ($r->KELOMPOK=='LABA/RUGI-PENAMBAH' or $r->KELOMPOK=='LABA/RUGI-PENGURANG') {
				$saldo_akhir = 0;
			} else {
				$sql = "SELECT first 1 skip 0 b.*
						FROM SALDOPERKIRAAN A, SALDOPERKIRAANDTL B
						WHERE A.TGLTRANS<'$tgl_aw' AND
							  A.KODESALDOPERKIRAAN=B.KODESALDOPERKIRAAN AND
							  B.KODEPERKIRAAN='$kode_perkiraan' and
							  a.kodelokasi = '".$_SESSION['KODELOKASI']."'
						order by a.tgltrans desc";
				$query = $db->query($sql);
				$rs = $db->fetch($query);

				if ($rs->KODESALDOPERKIRAAN <> '') {
					if ($r->SALDO == $rs->SALDO) {
						$saldo_akhir = $rs->AMOUNT;
					} else {
						$saldo_akhir = -$rs->AMOUNT;
					}
					$s = "AND A.TGLTRANS>='$tgl_aw' AND A.TGLTRANS<='$tgl_ak'";
				} else {
					$saldo_akhir = 0;
					$s = "AND A.TGLTRANS<='$tgl_ak'";
				}
				if ($r->SALDO == 'DEBET') {
					$sql = "select sum(iif(a.saldo='DEBET',a.amount,-a.amount)) as amount
							from valueperkiraan a
							where a.kodeperkiraan='$kode_perkiraan' and
								  a.kodelokasi = '".$_SESSION['KODELOKASI']."' $s";
				} else if ($r->SALDO == 'KREDIT') {
					$sql = "select sum(iif(a.saldo='KREDIT',a.amount,-a.amount)) as amount
							from valueperkiraan a
							where a.kodeperkiraan='$kode_perkiraan' and 
								  a.kodelokasi = '".$_SESSION['KODELOKASI']."' $s";
				}

				$query = $db->query($sql);
				$rs = $db->fetch($query);

				$saldo_akhir += $rs->AMOUNT;
			}

			if ($r->SALDO=='DEBET') {
				if ($saldo_akhir<0) {
					$saldo  = 'KREDIT';
					$amount = -$saldo_akhir;
				} else {
					$saldo  = 'DEBET';
					$amount = $saldo_akhir;
				}
			} else {
				if ($saldo_akhir<0) {
					$saldo  = 'DEBET';
					$amount = -$saldo_akhir;
				} else {
					$saldo  = 'KREDIT';
					$amount = $saldo_akhir;
				}
			}

			$data_values = array (
				$kodetrans, $i, $kode_perkiraan, $saldo, $_SESSION['KODECURRENCY'],
				$amount, 1, $amount
			);
			$exe = $db->execute($pr, $data_values);
			if (!$exe) { $db->rollback($tr); die(json_encode(array('errorMsg' => 'Transaction Failed <br>Failed on step 2 '.print_r($data_values)))); }

			$i++;
		}

		hitung_labarugi ($db, $tr, $kodetrans, $tgl_aw, $tgl_ak);

		$data_values = array(
			$kodetrans, $_SESSION['KODELOKASI'], 'AKUNTANSI', $tgl_aw, $tgl_ak, 
			$_SESSION['user'], ''
		);
		$exe = $db->insert('mclosing', $data_values, $tr);
		if (!$exe) { $db->rollback($tr); die(json_encode(array('errorMsg' => 'Transaction Failed <br>Failed on step 4'))); }

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
					'tabel' => 'mclosing',
					'kode' => 'kodeclosing'
				),
			),
			$_SESSION['user']
		);
	break;

	case 'batal_trans' :
		$kodetrans = $_POST['kodetrans'];

		$query = $db->query("SELECT * FROM MCLOSING WHERE KODECLOSING='$kodetrans'");
		$rs = $db->fetch($query);

		$query = $db->query("SELECT * FROM MCLOSING WHERE TGLAKHIR>'$rs->TGLAKHIR'");
		$rs = $db->fetch($query);

		if ($rs->KODECLOSING!='') {
			die(json_encode(array('errorMsg' => 'Transaction Failed <br>Ada Closing Akuntansi Yang Belum Dibatalkan')));
		}

		$db->query("DELETE FROM SALDOPERKIRAAN WHERE KODESALDOPERKIRAAN='$kodetrans'");
		$db->query("DELETE FROM MCLOSING WHERE KODECLOSING='$kodetrans'");
		$db->query("DELETE FROM VALUEPERKIRAAN WHERE KODETRANS='$kodetrans'");

		echo json_encode(array('success' => true));
	break;
}

function hitung_labarugi ($db, $tr, $kodetrans, $tgl_aw, $tgl_ak) {
	$kode_perkiraan_labarugi = '3219'; $_SESSION['KODELABARUGI'];
	$laba_rugi = 0;

	$s = "and a.tgltrans>='$tgl_aw'
		  and a.tgltrans<='$tgl_ak' ";
	$sql = "select a.kodeperkiraan,b.saldo,b.kelompok,sum(iif(upper(a.saldo)=upper(b.saldo),a.amount,-a.amount)) as amount
            from valueperkiraan a 
			inner join mperkiraan b on a.kodeperkiraan=b.kodeperkiraan
            where b.kelompok like 'LABA/RUGI%' AND 
				  UPPER(b.TIPE)='DETAIL' AND 
				  A.KODELOKASI = '".$_SESSION['KODELOKASI']."' $s
            group by a.kodeperkiraan,b.saldo,b.kelompok order by kodeperkiraan";
	$query = $db->query($sql);
	while ($rs = $db->fetch($query)) {
		if ($rs->KELOMPOK=='LABA/RUGI-PENAMBAH') {
			if ($rs->SALDO=='KREDIT'){
				$laba_rugi += $rs->AMOUNT;
			} else {
				$laba_rugi += (-$rs->AMOUNT);
			}
		} else {
			if ($rs->SALDO=='DEBET') {
  			    $laba_rugi -= $rs->AMOUNT;
			} else {
  			    $laba_rugi -= (-$rs->AMOUNT);
			}
		}
	}
	$sql = "select * from saldoperkiraan a, saldoperkiraandtl b
			where a.kodesaldoperkiraan=b.kodesaldoperkiraan and 
				  b.kodeperkiraan='$kode_perkiraan_labarugi' and 
				  a.kodelokasi = '".$_SESSION['KODELOKASI']."' and
				  a.tgltrans=(
					select max(tgltrans) as tgltrans 
					from saldoperkiraan 
					where tgltrans<='$tgl_aw')";
	$query = $db->query($sql);
	$rs = $db->fetch($query);

	if ($rs->SALDO=='KREDIT') {
		$laba_rugi += $rs->AMOUNT;
	} else {
		$laba_rugi -= $rs->AMOUNT;
	}

	$sql = "select sum(iif(saldo='KREDIT',amount,-amount)) as amount 
			from valueperkiraan
			where kodeperkiraan='$kode_perkiraan_labarugi' and 
				  tgltrans>='$tgl_aw' and tgltrans<='$tgl_ak' and
				  kodelokasi = '".$_SESSION['KODELOKASI']."'";
	$query = $db->query($sql);
	$rs = $db->fetch($query);

	$laba_rugi += $rs->AMOUNT;

	if ($laba_rugi<0) {
		$saldo  = 'DEBET';
		$amount = -$laba_rugi;
	} else {
		$saldo  = 'KREDIT';
		$amount = $laba_rugi;
	}

	$data_values = array(
		$kodetrans, 0, $kode_perkiraan_labarugi, $saldo, $_SESSION['KODECURRENCY'],
		$amount, 1, $amount
	);
	$exe = $db->insert('saldoperkiraandtl', $data_values, $tr);
	if (!$exe) { $db->rollback($tr); die(json_encode(array('errorMsg' => 'Transaction Failed <br>Failed on step 3'))); }
}

?>