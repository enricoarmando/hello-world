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
		$a_detail = json_decode($_POST['data_detail']);
		
		if (count($a_detail)<1) die(json_encode(array('errorMsg' => 'Detail Transaksi Tidak Boleh Kosong')));
		
		// start transaction
		$tr = $db->start_trans();
		
		// query detail
		$sql = $db->update('tpodtl', array('tutup' => 0), array('kodepo' => '', 'kodebarang' => '', 'kodebarangdtl' => ''), $tr, true);
		$pr  = $db->prepare($sql, $tr);
		foreach ($a_detail as $item) {
			$data_values = array (
				$item->tutup, $item->kodepo, $item->kodebarang, $item->kodebarangdtl
			);
			$exe = $db->execute($pr, $data_values);
			
			if (!$exe) {
				$db->rollback($tr); 
				die(json_encode(array('errorMsg' => 'Simpan Data Gagal <br>Kesalahan Pada Detail Data Transaksi')));
			}
			
			$kodetrans = $item->kodepo;
		}
		
		$db->commit($tr);

		// panggil fungsi untuk log history
		log_history(
			$kodetrans,
			'TUTUP PURCHASE ORDER',
			'UPDATE',
			array(
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
	
	case 'tampil_data' :
		$tgl_awal  = $_POST['txt_tgl_awal'];
		$tgl_akhir = $_POST['txt_tgl_akhir'];
		
		$kodetrans_awal  = $_POST['txt_kodetrans_awal'];
		$kodetrans_akhir = $_POST['txt_kodetrans_akhir'];
		
		$status = is_array($_POST['cb_status']) ? $_POST['cb_status'] : array();
		$temp_status = '';
		if (in_array(1, $status)) {
			$temp_status = 'count(B.KODEBARANG) = sum(B.TUTUP)';
		}		
		if (in_array(2, $status)) {
			if ($temp_status <> '')
				$temp_status .= ' or ';
			$temp_status .= 'count(B.KODEBARANG) <> sum(B.TUTUP)';
		}		
		if ($temp_status <> '')
			$temp_status = 'having '.$temp_status;
		
		$sql = "select A.TGLTRANS, A.KODEPO, A.KODESUPPLIER, A.NAMASUPPLIER, A.TOTAL, A.PPNRP, A.GRANDTOTAL, A.CATATAN, A.USERENTRY,
					   A.STATUS, A.TGLINPUT, count(B.KODEBARANG) as JMLBRG, sum(B.TUTUP) as JMLTUTUP
				from TPO A
				inner join TPODTL B on A.KODEPO = B.KODEPO
				where A.STATUS <> 'D'
				and A.TGLTRANS >= '$tgl_awal' and A.TGLTRANS <= '$tgl_akhir'
				group by A.TGLTRANS, A.KODEPO, A.KODESUPPLIER, A.NAMASUPPLIER, A.TOTAL, A.PPNRP, A.GRANDTOTAL, A.CATATAN, A.USERENTRY, A.STATUS, A.TGLINPUT
				$temp_status";
		$query = $db->query($sql);
		$rows = array();
		while ($rs = $db->fetch($query)) {
			$rows[] = $rs;
		}
		
		echo json_encode(array('success'=>true, 'data'=>$rows));
	break;
}
?>