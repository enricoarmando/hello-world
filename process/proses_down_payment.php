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
		$kodetrans 		= $_POST['KODEDOWNPAYMENT'];
		$kodetrans_ref  = $_POST['KODETRANSREFERENSI'];
		$currency  		= $_POST['KODECURRENCY'];
		$Catatan	   	= $_POST['CATATAN'];
		$tgltrans  		= ubah_tgl_firebird($_POST['TGLTRANS']);
		
		if ($_POST['JENISTRANSAKSI']=='PO'){
			cek_valid_data('tpo', 'kodepo', $kodetrans_ref, 'Purchase Order ID');
			$Txt = "select grandtotal from tpo where kodepo='$kodetrans_ref'";
			$Q = $db->query($Txt);	
			$r = $db->fetch($Q);
			$grandtotal = $r->GRANDTOTAL;		
		}else if ($_POST['JENISTRANSAKSI']=='PO_AKTIVA'){
			cek_valid_data('tpoaktiva', 'kodepoaktiva', $kodetrans_ref, 'Purchase Order Assets ID');
			$Txt = "select grandtotal from tpoaktiva where kodepoaktiva='$kodetrans_ref'";
			$Q = $db->query($Txt);	
			$r = $db->fetch($Q);
			$grandtotal = $r->GRANDTOTAL;
		}else{
			cek_valid_data('tso', 'kodeso', $kodetrans_ref, 'Sales Order ID');
			$Txt = "select grandtotal from tso where kodeso='$kodetrans_ref'";
			$Q = $db->query($Txt);	
			$r = $db->fetch($Q);
			$grandtotal = $r->GRANDTOTAL;
		}
		
		$mode = $_POST['mode'];
		if ($mode=='tambah') {
			$temp_kodetrans = 'DP.'.substr($tgltrans, 2, 2).substr($tgltrans, 5, 2).'-';
			
			$kodetrans = $temp_kodetrans.get_max_urutan('tdownpayment', 'kodedownpayment', $temp_kodetrans, 4);
		} else {
			$Txt = "select amountkurs from tdownpayment where kodedownpayment='$kodetrans'";
			$Q = $db->query($Txt);	
			$r = $db->fetch($Q);
			$DP = $r->AMOUNTKURS;
		}
		
		
		//menghitung total DP yang sudah dilakukan apakah melebihi total keseluruhan DP
		$Txt = "select sum(amountkurs) as TotalDP from tdownpayment where kodetransreferensi='$kodetrans_ref'";
		$Q = $db->query($Txt);	
		$r = $db->fetch($Q);
		$TotalDP = $r->TOTALDP-$DP+$_POST['AMOUNT'];
		
		if ($grandtotal<$TotalDP) {			
			die(json_encode(array('errorMsg' => 'Downpayment Summary over The Grand Total Value of the Transaction '.$kodetrans_ref.' ('.number($grandtotal).')')));
		}
		
		// start transaction
		$tr = $db->start_trans();
		
		// query header
		$data_values = array (
			$kodetrans, $kodetrans_ref, '', $_POST['JENISTRANSAKSI'], $tgltrans, 
			date("Y.m.d"), date("H:i:s"), $_SESSION['user'], $currency, $_POST['AMOUNT'], 
			$_POST['NILAIKURS'], $_POST['AMOUNTKURS'], $Catatan, 'I'
		);
		$exe = $db->insert('tdownpayment', $data_values, $tr);
		if (!$exe) { $db->rollback($tr); die(json_encode(array('errorMsg' => 'Simpan Data Gagal <br>Kesalahan Pada Header Data Transaksi'))); }
		
		$db->commit($tr);
		
		echo json_encode(array('success' => true));
	break;

	case 'batal_trans' :
		$kodetrans = $_POST['kodetrans'];
		//$status    = get_status('pelunasanpiutang', 'kodedownpayment', $kodetrans);
		
		//if ($status!='P') die(json_encode(array('errorMsg' => 'Transaksi Tidak Dapat Dibatalkan')));
		
		//cek_pelunasan ('piutang', $kodetrans);
		
		$tr = $db->start_trans();
		$query = $db->update('tdownpayment', array('status' => 'D'), array('kodedownpayment' => $kodetrans), $tr);
		$db->commit($tr);
		
		echo json_encode(array('success' => true));
	break;
}
?>