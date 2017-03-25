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
		$kodelokasi      = $_POST['KODELOKASI'];
		$kodetrans 	     = $_POST['KODETRANS'];
		$kodeinstansi    = $_POST['KODEINSTANSI'];
		$kodejurubayar   = '';
		$nofaktur        = $kodetrans;
		$kodecustomer    = $_POST['KODECUSTOMER'];
		$tgltrans        = ubah_tgl_firebird($_POST['TGLTRANS']);
		$kodesyaratbayar = '';//$_POST['KODESYARATBAYAR'];
		$selisihhari     = 0;//$_POST['SELISIHHARI'];
		$tgljatuhtempo   = ubah_tgl_firebird($_POST['TGLJATUHTEMPO']);
		$total           = 0;
		$downpayment     = 0;
		$grandtotal      = $_POST['GRANDTOTAL'];
		$jtrans 		 = $grandtotal>0 ? 'JUAL' : 'RETUR JUAL';
		$terbayar		 = 0;
		$sisa			 = $grandtotal;
		$status 	     = 'I';

		if($tgljatuhtempo<$tgltrans){
			die(json_encode(array('errorMsg' => 'Tanggal Jatuh Tempo Harus Lebih Besar Dari Tanggal Transaksi')));
		}

		cek_pelunasan ('piutang', $kodetrans);

		$mode = $_POST['mode'];
		// start transaction
		$tr  = $db->start_trans();
		
		// insert saldoperkiraan
		$data_values = array (
			$kodelokasi, $kodetrans, $nofaktur, $kodeinstansi, $kodejurubayar, 
			$kodecustomer, '', $tgltrans, $kodesyaratbayar, $selisihhari, 
			$tgljatuhtempo, date("Y-m-d"), date("h:m:s"), $jtrans, $grandtotal, 
			$downpayment, $grandtotal, $terbayar, $sisa, 'Saldo Awal Piutang', 
			$status
		);
		$exe = $db->insert('kartupiutang', $data_values, $tr);
		if (!$exe) { $db->rollback($tr); die(json_encode(array('errorMsg' => 'Simpan Data Gagal<br>Terdapat Kesalahan Data Header'))); }
		
		$db->commit($tr);
		
		// panggil fungsi untuk log history
		log_history(
			$kodetrans,
			'SALDO AWAL PIUTANG',
			$_POST['KODETRANS']=='' ? 'INSERT' : 'EDIT',
			array(
				array(
					'nama'  => 'header',
					'tabel' => 'kartupiutang',
					'kode'  => 'KODETRANS'
				),
			),
			$_SESSION['user']
		);
		
		echo json_encode(array('success' => true));
	break;
	
	case 'batal_trans' :
		$kodetrans = $_POST['kodetrans'];

		$query = $db->query("select * from pelunasanpiutang a 
							 inner join pelunasanpiutangdtl b on a.kodepelunasan=b.kodepelunasan  
							 where b.kodetrans='$kodetrans'");
		$rs = $db->fetch($query);
		
		if ($rs->KODETRANS<>'' && $rs->STATUS<>'D') {
			die(json_encode(array('isError' => true, 'msg' => 'Faktur Sudah Dilunasi Transaksi<br>Tidak Dapat Diubah/Dibatalkan')));
		}

		$tr = $db->start_trans();
		$query = $db->query("delete from kartupiutang where kodetrans='$kodetrans'", $tr);
		$db->commit($tr);

		// panggil fungsi untuk log history
		log_history(
			$kodetrans,
			'SALDO AWAL PIUTANG',
			'DELETE',
			array(
				array(
					'nama'  => 'header',
					'tabel' => 'kartupiutang',
					'kode'  => 'KODETRANS'
				),
			),
			$_SESSION['user']
		);
		
		echo json_encode(array('success' => true));
	break;
}
?>