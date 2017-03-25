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
		$kodetrans 	     = $_POST['KODETRANS'];
		$nofaktur        = $kodetrans;
		$kodejurubayar   = $_POST['KODEJURUBAYAR'];
		$tgltrans        = ubah_tgl_firebird($_POST['TGLTRANS']);
		$kodesyaratbayar = '';//$_POST['KODESYARATBAYAR'];
		$selisihhari     = 0;//$_POST['SELISIHHARI'];
		$tgljatuhtempo   = ubah_tgl_firebird($_POST['TGLJATUHTEMPO']);
		$total           = 0;
		$downpayment     = 0;
		$grandtotal      = $_POST['GRANDTOTAL'];
		$jtrans 		 = $grandtotal>0 ? 'BELI' : 'RETUR BELI';
		$terbayar		 = 0;
		$sisa			 = $grandtotal;
		$status 	     = 'I';	
	
		if($tgljatuhtempo<$tgltrans){
			die(json_encode(array('errorMsg' => 'Tanggal Jatuh Tempo Harus Lebih Besar Dari Tanggal Transaksi')));
		}
		
		cek_pelunasan ('hutangkomisi', $kodetrans);
		
		$mode = $_POST['mode'];
		// start transaction
		$tr  = $db->start_trans();
		
		// insert saldoperkiraan
		$data_values = array (
			$kodetrans, $kodejurubayar, $tgltrans, $tgljatuhtempo, date("Y-m-d"), 
			date("h:m:s"), $grandtotal, $terbayar, $sisa, 
			'Saldo Awal Hutang', $status
		);
		$exe = $db->insert('kartuhutangkomisi', $data_values, $tr);
		if (!$exe) { $db->rollback($tr); die(json_encode(array('errorMsg' => 'Simpan Data Gagal<br>Terdapat Kesalahan Data Header'))); }
		
		$db->commit($tr);
		
		// panggil fungsi untuk log history
		log_history(
			$kodetrans,
			'SALDO AWAL HUTANG KOMISI',
			$_POST['KODETRANS']=='' ? 'INSERT' : 'EDIT',
			array(
				array(
					'nama'  => 'header',
					'tabel' => 'kartuhutangkomisi',
					'kode'  => 'KODETRANS'
				),
			),
			$_SESSION['user']
		);
		
		echo json_encode(array('success' => true));
	break;
	
	case 'batal_trans' :
		$kodetrans = $_POST['kodetrans'];

		$query = $db->query("select * from pelunasanhutangkomisi a 
							 inner join pelunasanhutangkomisidtl b on a.kodepelunasan=b.kodepelunasan  
							 where b.kodetrans='$kodetrans'");
		$rs = $db->fetch($query);
		
		if ($rs->KODETRANS<>'' && $rs->STATUS<>'D') {
			die(json_encode(array('errorMsg' => 'Sudah Terdapat Pembayaran Komisi Pada Transaksi Ini<br>Data Tidak Dapat Diubah/Dibatalkan')));			
		}

		$tr = $db->start_trans();
		$query = $db->query("delete from kartuhutangkomisi where kodetrans='$kodetrans'", $tr);
		$db->commit($tr);

		// panggil fungsi untuk log history
		log_history(
			$kodetrans,
			'SALDO AWAL HUTANG KOMISI',
			'DELETE',
			array(
				array(
					'nama'  => 'header',
					'tabel' => 'kartuhutangkomisi',
					'kode'  => 'KODETRANS'
				),
			),
			$_SESSION['user']
		);
		
		echo json_encode(array('success' => true));
	break;
}
?>