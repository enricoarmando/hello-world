<?php
session_start();
ob_start("ob_gzhandler");
date_default_timezone_set("Asia/Jakarta"); 

if (empty($_SESSION['user'])) die(json_encode(array('errorMsg' => 'Expired Session <br> Please Relogin')));

include "../../config/koneksi.php";
include "../../config/function.php";

$bulan = $_POST['sb_bulan'];
$tahun = $_POST['txt_tahun'];

$tgl_aw = date('Y.m.d', mktime(0, 0, 0, $bulan, 1, $tahun));
$tgl_ak = date('Y.m.t', mktime(0, 0, 0, $bulan, 1, $tahun));

// UNTUK BROWSE DATA
$act = isset($_REQUEST['act']) ? $_REQUEST['act'] : '';

//$db = new DB;

switch ($act) {
	case 'hitung_tahap1' :
		$tr = $db->start_trans();
		
		$kodetrans = 'CLS.'.substr($tgl_ak, 2, 2).substr($tgl_ak, 5, 2).substr($tgl_ak, -2);

		$exe = $db->select('mclosing', array('keterangan'), array('kodeclosing' => $kodetrans));
		$rs = $db->fetch($exe);
		if ($rs->KETERANGAN=='TAHAP 1') {
			die(json_encode(array('errorMsg' => 'Simpan Data Gagal...<br>First Step Cost of Good Calculation Has Already Saving<br>Please Doing Second Step'))); 
		} else if ($rs->KETERANGAN=='TAHAP 2') {
			die(json_encode(array('errorMsg' => 'Simpan Data Gagal...<br>Cost of Good Calculation Has been Saving'))); 
		}

		$pr = $db->prepare('execute procedure hitung_hpp(?, ?)', $tr);
		$exe = $db->execute($pr, array($tgl_aw, $tgl_ak));
		if (!$exe) { $db->rollback($tr); die(json_encode(array('errorMsg' => 'Simpan Data Gagal...<br>There\'s Something Problem with Query'))); }

		$data_values = array(
			$kodetrans, 'HPP', $tgl_aw, $tgl_ak, $_SESSION['user'],
			'TAHAP 1'
		);
		$exe = $db->insert('mclosing', $data_values, $tr);
		if (!$exe) { 
			$db->rollback($tr);
			die(json_encode(array('errorMsg' => 'Simpan Data Gagal...<br>There\'s Something Problem with Query Insert'))); 
		}

		$db->commit($tr);

		echo json_encode(array('success' => true));
	break;

	case 'hitung_tahap2' :
		$kodetrans = 'CLS/'.substr($tgl_ak, 2, 2).substr($tgl_ak, 5, 2).substr($tgl_ak, -2);

		$exe = $db->select('mclosing', array('keterangan'), array('kodeclosing' => $kodetrans));
		$rs = $db->fetch($exe);
		if ($rs->KETERANGAN=='TAHAP 2') {
			die(json_encode(array('errorMsg' => 'Cost of Good Calculation Has Been Saving'))); 
		}

		// PERHITUNGAN BIAYA
		$sql = 'select 
					kodeperkiraan, namaperkiraan, sum(amountkurs) as amountkurs
				from (
					select
						a.kodeperkiraan, b.namaperkiraan, iif(b.saldo=a.saldo, a.amountkurs, -a.amountkurs) as amountkurs
					from
						valueperkiraan a inner join mperkiraan b on a.kodeperkiraan=b.kodeperkiraan
					where 
						a.kodeperkiraan>=? and a.kodeperkiraan<=? and b.tipe=?
						and a.tgltrans>=? and a.tgltrans<=?
					order by a.kodeperkiraan
				) group by kodeperkiraan, namaperkiraan';
		$pr = $db->prepare($sql);
		$exe = $db->execute($pr, array($_POST['KODEPERKIRAAN_AWAL'], $_POST['KODEPERKIRAAN_AKHIR'], 'DETAIL',  $tgl_aw, $tgl_ak));
		$a_kodeperkiraan = array();
		$total_amount = 0;
		while ($rs = $db->fetch($exe)) {
			$a_kodeperkiraan[] = array(
				'kodeperkiraan' => $rs->KODEPERKIRAAN,
				'namaperkiraan' => $rs->NAMAPERKIRAAN,
				'amountkurs' => $rs->AMOUNTKURS,
			);
			$total_amount += $rs->AMOUNTKURS;
		}
		// PERHITUNGAN SALDO STOK
		$sql = "select kodebarang, namabarang, satuan from mbarang where jenisbarang='FINISH GOOD'";
		$q = $db->query($sql);

		$pr = $db->prepare('select * from get_saldostok_global(?, ?)');

		$sql = 'select 
					sum(subtotal) as subtotal
				from (
					select
						(c.jml * c.harga) as subtotal
					from
						tproduksi a inner join tproduksidtl b on a.kodeproduksi=b.kodeproduksi
						inner join kartustok c on a.kodeproduksi=c.kodetrans
					where
						a.status<>? and a.kodebarang=?
						and c.tgltrans>=? and c.tgltrans<=?
				)';
		$pr2 = $db->prepare($sql);

		$a_barang = array();
		$total_jml = 0;
		while ($r = $db->fetch($q)) {
			
			$exe = $db->execute($pr, array($r->KODEBARANG, $tgl_ak));
			$rs = $db->fetch($exe);

			$jml_saldo = $rs->SALDO;
			$jml_awal  = $rs->SALDOAWAL;
			$jml_hasil = $jml_saldo - $jml_awal;

			if ($jml_hasil<0) {
				$jml_hasil *= -1;
			}

			$total_saldo = $rs->TOTAL;
			$total_awal  = $rs->TOTALAWAL;
			$total_hasil = $total_saldo - $total_awal;

			$exe = $db->execute($pr2, array('D', $r->KODEBARANG, $tgl_aw, $tgl_ak));
			$rs = $db->fetch($exe);

			if (($rs->SUBTOTAL=='' or $rs->SUBTOTAL==0) or ($jml_hasil=='' or $jml_hasil==0)) {
				$hpp_bb = 0;
			} else {
				$hpp_bb = $rs->SUBTOTAL / $jml_hasil;
			}

			if ($jml_hasil<>0) {
				$a_barang[] = array(
					'kodebarang' => $r->KODEBARANG,
					'namabarang' => $r->NAMABARANG,
					'jmlhasil' => $jml_hasil,
					'persentase' => 0,
					'hppbahanbaku' => $hpp_bb,
					'biaya' => 0,
					'total' => 0,
					'jmlsaldo' => $jml_awal,
					'totalsaldo' => $total_awal,
					'hppproduksi' => 0,
				);
			}

			$total_jml += $jml_hasil;
		}

		$j = count($a_barang);
		for ($i = 0; $i < $j; $i++) {
			$persentase = ($total_jml * 100) / $a_barang[$i]['jmlhasil'];
			$biaya = (100 * $total_amount) / $persentase;
			$total = $a_barang[$i]['hppbahanbaku'] + $biaya;
			
			$hppproduksi = (($a_barang[$i]['totalsaldo'] * $a_barang[$i]['jmlsaldo']) + ($total * $a_barang[$i]['jmlhasil'])) / ($a_barang[$i]['jmlsaldo'] + $a_barang[$i]['jmlhasil']);

			$a_barang[$i]['persentase'] = $persentase;
			$a_barang[$i]['biaya'] = $biaya;
			$a_barang[$i]['total'] = $total;
			$a_barang[$i]['hppproduksi'] = $hppproduksi;
		}

		echo json_encode(array('success'=>true, 'data_barang'=>$a_barang, 'data_perkiraan'=>$a_kodeperkiraan));
	break;

	case 'update_tahap2' :
		$a_detail = json_decode($_POST['data_detail']);

		if (count($a_detail)<1) {
			die(json_encode(array('errorMsg' => 'Anda Belum Menambahkan Detail Transaksi')));
		}
		$kodetrans = 'CLS/'.substr($tgl_ak, 2, 2).substr($tgl_ak, 5, 2).substr($tgl_ak, -2);

		$exe = $db->select('mclosing', array('keterangan'), array('kodeclosing' => $kodetrans));
		$rs = $db->fetch($exe);
		if ($rs->KETERANGAN=='TAHAP 2') {
			die(json_encode(array('errorMsg' => 'Cost of Good Calculation Has Been Saving'))); 
		} else if ($rs->KETERANGAN=='') {
			die(json_encode(array('errorMsg' => 'You Must Doing First Step Calculation'))); 
		}

		$tr = $db->start_trans();

		$sql = 'update kartustok set harga=? where kodebarang=? and a.tgltrans>=? and a.tgltrans<=? and jtrans like \'JUAL%\'';
		$pr = $db->prepare($sql, $tr);
		foreach ($a_detail as $item) {
			$exe = $db->execute($pr, array($item->hppproduksi, $item->kodebarang, $tgl_aw, $tgl_ak));
			if (!$exe) {
				$db->rollback($tr);
				die(json_encode(array('errorMsg' => 'Simpan Data Gagal...<br>There\'s Something Problem with Query'))); 
			}
		}

		$exe = $db->update('mclosing', array('keterangan'=>'TAHAP 2'), array('kodeclosing'=>$kodetrans), $tr);
		if (!$exe) {
			$db->rollback($tr);
			die(json_encode(array('errorMsg' => 'Simpan Data Gagal...<br>There\'s Something Problem with Query Update'))); 
		}

		$db->commit($tr);

		echo json_encode(array('success'=>true));
	break;

	case 'batal_trans_hpp' :
		$kodetrans = $_POST['kodetrans'];

		if ($kodetrans=='') {
			die(json_encode(array('errorMsg' => 'Please Select Trans ID Which Want to Delete')));
		}

		$query = $db->query('select * from mclosing where kodeclosing=\''.$kodetrans.'\'');
		$rs = $db->fetch($query);

		$tgl_aw = str_replace('-', '.', $rs->TGLAWAL);
		$tgl_ak = str_replace('-', '.', $rs->TGLAKHIR);

		if ($tgl_ak<>'' and $tgl_aw<>'') {

			$tr = $db->start_trans();

			$sql = 'update kartustok 
					set harga=? 
					where tgltrans>=? and tgltrans<=? and
					(jtrans like \'JUAL%\' or jtrans like \'KIRIM TRANSFER%\' or 
					jtrans like \'TERIMA TRANSFER%\' or jtrans like \'PAKAI BAHAN%\' or 
					jtrans like \'ADJUSTMENT%\' or jtrans like \'PAKAI%\' or jtrans like \'PAKAI%\')';
			$pr = $db->prepare($sql, $tr);

			$exe = $db->execute($pr, array(0, $tgl_aw, $tgl_ak));
			if (!$exe) {
				$db->rollback($tr);
				die(json_encode(array('errorMsg' => 'Simpan Data Gagal...<br>There\'s Something Problem with Query Update'))); 
			}

			$exe = $db->delete('mclosing', array('kodeclosing'=>$kodetrans), $tr);
			if (!$exe) {
				$db->rollback($tr);
				die(json_encode(array('errorMsg' => 'Simpan Data Gagal...<br>There\'s Something Problem with Delete Query'))); 
			}			

			$db->commit($tr);
		}
		echo json_encode(array('success'=>true));
	break;

	default :
		die(json_encode(array('errorMsg' => 'Sorry, Are You Lost in Program ?'))); 
}