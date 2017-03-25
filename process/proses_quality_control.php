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
		$a_detail  = json_decode($_POST['data_detail']);

		cek_data($a_detail, 'kodebarang', 'mbarang');

		$kodetrans    = $_POST['KODEQC'];
		$kodetrans_so = $_POST['KODESO'];
		$catatan      = $_POST['CATATAN'];
		$tgltrans     = $_POST['TGLTRANS'];

		//cek_valid_data('MLOKASI', 'KODELOKASI', $lokasi, 'Location');
		//cek_valid_data('MSYARATBAYAR', 'KODESYARATBAYAR', $syaratbayar, 'Term Payment');
		//cek_valid_data('MCUSTOMER', 'KODECUSTOMER', $customer, 'Customer');
		cek_valid_data('tso', 'kodeso', $kodetrans_so, 'No SO');

		$mode = $_POST['mode'];
		if ($mode=='tambah') {
			/*$temp_kode = 'QC/'.$_SESSION['KODELOKASI'].'/';

			$urutan = get_new_urutan('tqualitycontrol', 'kodeqc', array($temp_kode, substr($tgltrans, 2, 2)), 6);

			$kodetrans = $temp_kode.$urutan.'/'.substr($tgltrans, 5, 2).'/'.substr($tgltrans, 2, 2);*/

			$temp_kode = 'QC/'.$_SESSION['KODELOKASI'].'/'.substr($tgltrans, 2, 2).substr($tgltrans, 5, 2);

			$urutan = get_max_urutan('tqualitycontrol', 'kodeqc', $temp_kode, 4);

			$kodetrans = $temp_kode.$urutan;

			cek_periode($tgltrans,'tambahkan');
		} else {
			//cek tgl asal mungkin dilakukan perubahan tanggal
			cek_periode(get_tgl_trans('tqualitycontrol', 'kodeqc', $kodetrans), 'ubah');
			cek_periode($tgltrans,'ubah');
		}

		// start transaction
		$tr = $db->start_trans();

		// query header
		$data_values = array (
			$kodetrans, $_SESSION['KODELOKASI'], $kodetrans_so, $_POST['KODEPEGAWAI_EDGER'], $tgltrans,
			date("Y.m.d"), date("H:i:s"), $_POST['JENISFRAME'], $_POST['MPD_R'], $_POST['MPD_L'],
			$_POST['STATUS_LENSA'], $_POST['KETERANGAN_LENSA'], $_POST['STATUS_FRAME'], $_POST['KETERANGAN_FRAME'], $_SESSION['user'],
			'I'
		);
		$exe = $db->insert('tqualitycontrol', $data_values, $tr);
		if (!$exe) { $db->rollback($tr); die(json_encode(array('errorMsg' => 'Simpan Data Gagal <br>Kesalahan Pada Header Data Transaksi '.$kodetrans))); }

		$q = $db->select('tso', array('kodelokasi'), array('kodeso'=>$kodetrans_so));
		$r = $db->fetch($q);
		foreach ($a_detail as $item) {
			// cek stok
			$stok_cukup = cek_stok ($item->kodebarang, $tgltrans, $item->jml, $item->satuan, $r->KODELOKASI);
			if (!$stok_cukup) { $db->rollback($tr); die(json_encode(array('errorMsg' => 'stok barang '.$item->kodebarang.' ('.$item->namabarang.') tidak mencukupi'))); }
		}

		$db->commit($tr);

		// panggil fungsi untuk log history
		log_history(
			$kodetrans,
			'QUALITY CONTROL',
			$_POST['KODEQC']=='' ? 'INSERT' : 'EDIT',
			array(
				array(
					'nama' => 'header',
					'tabel' => 'tqualitycontrol',
					'kode' => 'KODEQC'
				),
			),
			$_SESSION['user']
		);

		echo json_encode(array('success' => true));


		// script utk kirim sms
		$sql = 'select c.hp, b.namalokasi, b.hp as hpcabang
				from tso a
				inner join mlokasi b on a.kodelokasi = b.kodelokasi
				inner join mcustomer c on a.kodecustomer = c.kodecustomer
				where a.kodeso=?';
		$pr = $db->prepare($sql);
		$q = $db->execute($pr, $kodetrans_so);
		$r = $db->fetch($q);

		$phone = str_replace(' ', '', trim($r->HP));
		$hp_cabang = substr(str_replace(' ', '', trim($r->HPCABANG)), 0, 12);
		$namalokasi = ucwords(strtolower($r->NAMALOKASI));
		$namacustomer = substr(ucwords(strtolower($_POST['NAMACUSTOMER'])), 0, 15);		
		
		$sms = 'Yth. Bp/Ibu '.$namacustomer.' pesanan anda sdh bisa diambil setiap hari kerja pkl 08.00-20.30. Info, kritik, saran hub '.$hp_cabang.' ('.$namalokasi.')';

		// SAYA REMARK KARENA BELUM TAHU SETTINGAN GAMMU DI INDRA Optik 2016-12-09
		/*chdir('c:\gammu\bin');
		shell_exec ('gammu sendsms TEXT '.$phone.' -text "SO Saudara Sedang Diproses untuk Quality Control.\nIndra Optik"');*/

		/* pake gammu 2017-01-17*/
		$mysqli = new mysqli("localhost", "root", "", "sms");

		$stmt = $mysqli->prepare("insert into outbox (DestinationNumber, TextDecoded, CreatorID) values (?,?,?);");

		$stmt->bind_param("sss", $phone, $sms, $_SESSION['user']);

		$stmt->execute();

		$mysqli->close();
	break;

	case 'batal_trans' :
		$kodetrans = $_POST['kodetrans'];
		$status    = get_status('tqualitycontrol', 'kodeqc', $kodetrans);

		if ($status=='P') die(json_encode(array('errorMsg' => 'Transaksi Tidak Dapat Dibatalkan')));

		cek_periode(get_tgl_trans('tqualitycontrol', 'kodeqc', $kodetrans), 'hapus');

		$tr = $db->start_trans();
		$query = $db->update('tqualitycontrol', array('status' => 'D'), array('kodeqc' => $kodetrans), $tr);
		$db->commit($tr);

		// panggil fungsi untuk log history
		log_history(
			$kodetrans,
			'QUALITY CONTROL',
			'DELETE',
			array(
				array(
					'nama' => 'header',
					'tabel' => 'tqualitycontrol',
					'kode' => 'KODEQC'
				),
			),
			$_SESSION['user']
		);

		echo json_encode(array('success' => true));
	break;
}
?>