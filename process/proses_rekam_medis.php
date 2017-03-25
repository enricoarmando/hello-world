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
		$kodetrans    = $_POST['KODEREKAMMEDIS'];
		$customer     = $_POST['KODECUSTOMER'];
		$tgltrans     = ubah_tgl_firebird($_POST['TGLTRANS']);
		$keterangan   = $_POST['CATATAN'];

		cek_valid_data('MCUSTOMER', 'KODECUSTOMER', $customer, 'customer');
		cek_valid_data('MUSER', 'USERID', $_POST['RO'], 'Pegawai RO');

		$mode = $_POST['mode'];
		if ($mode=='tambah') { // generate kodetrans
			/*$temp_kode = 'RM/'.$_SESSION['KODELOKASI'].'/';

			$urutan = get_new_urutan('trekammedis', 'koderekammedis', array($temp_kode, substr($tgltrans, 2, 2)));

			$kodetrans = $temp_kode.$urutan.'/'.substr($tgltrans, 5, 2).'/'.substr($tgltrans, 2, 2);*/
			
			$temp_kode = 'RM/'.$_SESSION['KODELOKASI'].'/'.substr($tgltrans, 2, 2).substr($tgltrans, 5, 2);

			$urutan = get_max_urutan('trekammedis', 'koderekammedis', $temp_kode, 4);

			$kodetrans = $temp_kode.$urutan;
			
			cek_periode($tgltrans,'tambahkan');
		} else {
			//cek tgl asal mungkin dilakukan perubahan tanggal
			cek_periode(get_tgl_trans('trekammedis', 'koderekammedis', $kodetrans), 'ubah');
			cek_periode($tgltrans,'ubah');
		}

		// start transaction
		$tr  = $db->start_trans();

		// insert trekammedis
		$data_values = array(
			$kodetrans, $customer, $_POST['MOTIVASI'], $_POST['KELUHAN'], $tgltrans,
			date("Y.m.d"), date("H:i:s"), $_SESSION['user'], $_POST['TANPAKACA_R_VA'], $_POST['TANPAKACA_L_VA'],

			$_POST['RESEPDOKTER_R_SPH'], $_POST['RESEPDOKTER_R_CYL'], $_POST['RESEPDOKTER_R_AXIS'], $_POST['RESEPDOKTER_R_PRISM'], $_POST['RESEPDOKTER_R_VA'],
			$_POST['RESEPDOKTER_R_ADD'], $_POST['RESEPDOKTER_L_SPH'], $_POST['RESEPDOKTER_L_CYL'], $_POST['RESEPDOKTER_L_AXIS'], $_POST['RESEPDOKTER_L_PRISM'],

			$_POST['RESEPDOKTER_L_VA'], $_POST['RESEPDOKTER_L_ADD'], $_POST['RX_OLD_LAMAPAKAI'], $_POST['RX_OLD_JENIS'], $_POST['RX_OLD_JENIS_NAMA'],
			$_POST['REFRAKSI_R_SPH'], $_POST['REFRAKSI_R_CYL'], $_POST['REFRAKSI_R_AXIS'], $_POST['REFRAKSI_R_PRISM'], $_POST['REFRAKSI_R_VA'],

			$_POST['REFRAKSI_L_SPH'], $_POST['REFRAKSI_L_CYL'], $_POST['REFRAKSI_L_AXIS'], $_POST['REFRAKSI_L_PRISM'], $_POST['REFRAKSI_L_VA'],
			$_POST['SUBJECTIVE_RX_R_SPH'], $_POST['SUBJECTIVE_RX_R_CYL'], $_POST['SUBJECTIVE_RX_R_AXIS'], $_POST['SUBJECTIVE_RX_R_PRISM'], $_POST['SUBJECTIVE_RX_R_VA'],

			$_POST['SUBJECTIVE_RX_R_ADD'], $_POST['REFRAKSI_L_SPH'], $_POST['REFRAKSI_L_CYL'], $_POST['REFRAKSI_L_AXIS'], $_POST['REFRAKSI_L_PRISM'],
			$_POST['SUBJECTIVE_RX_L_VA'], $_POST['SUBJECTIVE_RX_L_ADD'], $_POST['BALANCE_R_SPH'], $_POST['BALANCE_R_CYL'], $_POST['BALANCE_R_AXIS'],

			$_POST['BALANCE_R_PRISM'], $_POST['BALANCE_R_VA'], $_POST['BALANCE_R_ADD'], $_POST['BALANCE_L_SPH'], $_POST['BALANCE_L_CYL'],
			$_POST['BALANCE_L_AXIS'], $_POST['BALANCE_L_PRISM'], $_POST['BALANCE_L_VA'], $_POST['BALANCE_L_ADD'], $_POST['BALANCE_KETERANGAN'],

			$_POST['TINDAKAN_R_SPH'], $_POST['TINDAKAN_R_CYL'], $_POST['TINDAKAN_R_AXIS'], $_POST['TINDAKAN_R_PRISM'], $_POST['TINDAKAN_R_VA'],
			$_POST['TINDAKAN_R_ADD'], $_POST['TINDAKAN_R_PD'], $_POST['TINDAKAN_L_SPH'], $_POST['TINDAKAN_L_CYL'], $_POST['TINDAKAN_L_AXIS'],

			$_POST['TINDAKAN_L_PRISM'], $_POST['TINDAKAN_L_VA'], $_POST['TINDAKAN_L_ADD'], $_POST['TINDAKAN_L_PD'], $_POST['TINDAKAN_PV'],
			$_POST['TINDAKAN_SH'], $_POST['TINDAKAN_KETERANGAN'], $_POST['SL_R_SPH'], $_POST['SL_R_CYL'], $_POST['SL_R_AXIS'],

			$_POST['SL_R_PRISM'], $_POST['SL_R_VA'], $_POST['SL_R_ADD'], $_POST['SL_L_SPH'], $_POST['SL_L_CYL'],
			$_POST['SL_L_AXIS'], $_POST['SL_L_PRISM'], $_POST['SL_L_VA'], $_POST['SL_L_ADD'], $_POST['SL_JENIS'],

			$_POST['RO'], '', '', '', '',
			'', 'I'
		);
		$exe = $db->insert('trekammedis', $data_values, $tr);
		if (!$exe) {
			$db->rollback($tr);
			die(json_encode(array('errorMsg' => 'Simpan Data Gagal <br>Kesalahan Pada Data Rekam Medis')));
		}
		
		// update riwayat kesehatan di mcustomer
		$exe = $db->update('mcustomer', array('riwayatkesehatan'=>$_POST['RIWAYATKESEHATAN']), array('kodecustomer'=>$customer), $tr);

		$db->commit($tr);

		// panggil fungsi untuk log history
		log_history(
			$kodetrans,
			'REKAM MEDIS',
			$_POST['KODEREKAMMEDIS']=='' ? 'INSERT' : 'EDIT',
			array(
				array(
					'nama' => 'header',
					'tabel' => 'trekammedis',
					'kode' => 'KODEREKAMMEDIS'
				),
			),
			$_SESSION['user']
		);

		echo json_encode(array('success' => true));
	break;

	case 'batal_trans' :
		$kodetrans = $_POST['kodetrans'];
		$status    = get_status('trekammedis', 'koderekammedis', $kodetrans);

		if ($status=='S') die(json_encode(array('errorMsg' => 'Data Rekam Medis Tidak Dapat Dibatalkan')));

		cek_periode(get_tgl_trans('trekammedis', 'koderekammedis', $kodetrans), 'hapus');

		$tr = $db->start_trans();
		$query = $db->update('trekammedis', array('status' => 'D'), array('koderekammedis' => $kodetrans), $tr);
		$db->commit($tr);

		// panggil fungsi untuk log history
		log_history(
			$kodetrans,
			'REKAM MEDIS',
			'DELETE',
			array(
				array(
					'nama' => 'header',
					'tabel' => 'trekammedis',
					'kode' => 'KODEREKAMMEDIS'
				),
			),
			$_SESSION['user']
		);

		echo json_encode(array(
			'success' => true,
		));
	break;

	case 'load_data_lama' :
		$kodecustomer = $_POST['kodecustomer'];
		
		$sql   = "select * from trekammedis where koderekammedis = (select max(koderekammedis) from trekammedis where kodecustomer='$kodecustomer')";		
		$query = $db->query($sql);
		$rs    = $db->fetch($query);

		echo json_encode($rs);
	break;
}

?>