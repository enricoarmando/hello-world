<?php
session_start();
ob_start("ob_gzhandler");
date_default_timezone_set("Asia/Jakarta");

if (empty($_SESSION['user'])) die(json_encode(array('errorMsg' => 'Expired Session <br> Please Relogin')));

include "../../config/koneksi.php";
include "../../config/function.php";

$table = isset($_POST['table']) ? $_POST['table'] : $_GET['table'];
$act   = isset($_POST['act']) ? $_POST['act'] :  $_GET['act'];

//$db = new DB;

switch ($table) {
	//========================= MASTER DATA INSTANSI ===========================
	case 'instansi' :
		if ($act=='insert' || $act=='edit') {
			$kode	= $_POST['KODEINSTANSI'];
			$nama	= $_POST['NAMAINSTANSI'];
			$status	= isset($_POST['STATUS']) ? $_POST['STATUS'] : 0;

			if ($_POST['NAMAINSTANSI'] == '') die(json_encode(array('errorMsg' => 'Nama Instansi Tidak Boleh Kosong')));

			if ($kode=='') {
				$temp_kode = substr($_SESSION['KODELOKASI'],2,2).'/';
				$kode = $temp_kode.get_max_urutan('minstansi', 'kodeinstansi', $temp_kode, 4);
			}

			//CEK APAKAH KODE SUDAH DIGUNAKAN
			if ($act=='insert') {
				$p = $db->prepare('select kodeinstansi, namainstansi from minstansi where kodeinstansi=?');
				$q = $db->execute($p, $kode);
				$r = $db->fetch($q);
				if ($r->KODEINSTANSI!='') {
					die(json_encode(array('errorMsg' => 'Kode Instansi Sudah Digunakan Oleh Instansi ('.$r->KODEINSTANSI.') '.$r->NAMAINSTANSI.', Kode Tidak Dapat Digunakan')));
				}

				$p = $db->prepare('select kodeinstansi, namainstansi from minstansi where namainstansi=?');
				$q = $db->execute($p, $nama);
				$r = $db->fetch($q);
				if ($r->NAMAINSTANSI!='') {
					die(json_encode(array('errorMsg' => 'Nama Instansi Sudah Digunakan Oleh Instansi ('.$r->KODEINSTANSI.') '.$r->NAMAINSTANSI.', Nama Tidak Dapat Digunakan')));
				}
				$status = 1;
			} else if ($act=='edit') {
				$p = $db->prepare('select kodeinstansi, namainstansi from minstansi where kodeinstansi<>? and namainstansi=?');
				$q = $db->execute($p, array($kode, $nama));
				$r = $db->fetch($q);
				if ($r->NAMAINSTANSI!='') {
					die(json_encode(array('errorMsg' => 'Nama Instansi Sudah Digunakan Oleh Instansi ('.$r->KODEINSTANSI.') '.$r->NAMAINSTANSI.', Nama Tidak Dapat Digunakan')));
				}
			}
			$tr = $db->start_trans();

			$data_values = array (
				$kode, $_POST['NAMAINSTANSI'], $_POST['ALAMAT'], $_POST['KECAMATAN'], $_POST['KOTA'],
				$_POST['TELP'], $_POST['FAX'], $_SESSION['user'], date("Y-m-d"), $status
			);
			$query = $db->insert('minstansi', $data_values, $tr);
			if (!$query) {
				$db->rollback($tr);
				die(json_encode(array('errorMsg' => 'Simpan Data Gagal')));
			}

			$db->commit($tr);

			// panggil fungsi untuk log history
			log_history(
				$kode,
				'MASTER INSTANSI',
				$act,
				array(
					array(
						'nama' => 'header',
						'tabel' => 'minstansi',
						'kode' => 'kodeinstansi'
					),
				),
				$_SESSION['user']
			);

			echo json_encode(array('success' => true));
		} else if ($act=='delete') {
			$kode = $_POST['id'];

			$p = $db->prepare('select first 1 skip 0 a.kodejurubayar as kode from tso a, mjurubayar b where b.kodeinstansi=? and a.kodejurubayar=b.kodejurubayar');
			$q = $db->execute($p, $kode);
			$r = $db->fetch($q);

			if ($r->KODE<>'') { die(json_encode(array('errorMsg'=>'Data Instansi Tidak Dihapus, Data Sudah Digunakan Pada Transaksi'))); }

			$q = $db->delete('minstansi', array('kodeinstansi' => $kode));

			if ($q) {
				// panggil fungsi untuk log history
				log_history(
					$kode,
					'MASTER INSTANSI',
					$act,
					array(
						array(
							'nama' => 'header',
							'tabel' => 'minstansi',
							'kode' => 'kodeinstansi'
						),
					),
					$_SESSION['user']
				);

				echo json_encode(array('success'=>true));
			} else {
				echo json_encode(array('errorMsg'=>'Terdapat Data Error, Data Tidak Dapat Dihapus'));
			}
		}
	break;
	//========================= END MASTER DATA INSTANSI ===========================

	//========================= MASTER DATA JURU BAYAR ===========================
	case 'juru_bayar' :
		if ($act=='insert' || $act=='edit') {
			$kode	= $_POST['KODEJURUBAYAR'];
			$nama	= $_POST['NAMAJURUBAYAR'];
			$status	= isset($_POST['STATUS']) ? $_POST['STATUS'] : 0;

			if ($_POST['NAMAJURUBAYAR'] == '') die(json_encode(array('errorMsg' => 'Nama Juru Bayar Tidak Boleh Kosong')));

			if ($kode=='') {
				$temp_kode = 'JB/'.substr($_SESSION['KODELOKASI'],2,2).'/';
				$kode = $temp_kode.get_max_urutan('mjurubayar', 'kodejurubayar', $temp_kode, 4);
			}

			//CEK APAKAH KODE SUDAH DIGUNAKAN
			if ($act=='insert') {
				$p = $db->prepare('select kodejurubayar, namajurubayar from mjurubayar where kodejurubayar=?');
				$q = $db->execute($p, $kode);
				$r = $db->fetch($q);
				if ($r->KODEJURUBAYAR!='') {
					die(json_encode(array('errorMsg' => 'Kode Juru Bayar Sudah Digunakan Oleh Juru Bayar ('.$r->KODEJURUBAYAR.') '.$r->NAMAJURUBAYAR.', Kode Tidak Dapat Digunakan')));
				}

				$p = $db->prepare('select kodejurubayar, namajurubayar from mjurubayar where namajurubayar=? and kodeinstansi=?');
				$q = $db->execute($p, array($nama, $_POST['KODEINSTANSI']));
				$r = $db->fetch($q);
				if ($r->NAMAJURUBAYAR!='') {
					die(json_encode(array('errorMsg' => 'Nama Juru Bayar Sudah Digunakan Oleh Juru Bayar ('.$r->KODEJURUBAYAR.') '.$r->NAMAJURUBAYAR.', Nama Tidak Dapat Digunakan')));
				}
				$status = 1;
			} else if ($act=='edit') {
				$p = $db->prepare('select kodejurubayar, namajurubayar from mjurubayar where kodejurubayar<>? and namajurubayar=?');
				$q = $db->execute($p, array($kode, $nama));
				$r = $db->fetch($q);
				if ($r->NAMAJURUBAYAR!='') {
					die(json_encode(array('errorMsg' => 'Nama Juru Bayar Sudah Digunakan Oleh Juru Bayar ('.$r->KODEJURUBAYAR.') '.$r->NAMAJURUBAYAR.', Nama Tidak Dapat Digunakan')));
				}
			}
			$tr = $db->start_trans();

			$data_values = array (
				$kode, $_POST['KODEINSTANSI'], $_POST['NAMAJURUBAYAR'], $_POST['ALAMAT'], $_POST['KOTA'],
				$_POST['TELP'], $_POST['KOMISI'], $_SESSION['user'], date("Y-m-d"), $status
			);
			$query = $db->insert('mjurubayar', $data_values, $tr);
			if (!$query) {
				$db->rollback($tr);
				die(json_encode(array('errorMsg' => 'Simpan Data Gagal')));
			}

			$db->commit($tr);

			// panggil fungsi untuk log history
			log_history(
				$kode,
				'MASTER JURU BAYAR',
				$act,
				array(
					array(
						'nama'  => 'header',
						'tabel' => 'mjurubayar',
						'kode'  => 'kodejurubayar'
					),
				),
				$_SESSION['user']
			);

			echo json_encode(array('success' => true));
		} else if ($act=='delete') {
			$kode = $_POST['id'];

			$p = $db->prepare('select first 1 skip 0 kodejurubayar as kode from tso where kodejurubayar=?');
			$q = $db->execute($p, $kode);
			$r = $db->fetch($q);

			if ($r->KODE<>'') { die(json_encode(array('errorMsg'=>'Data Juru Bayar Tidak Dihapus, Data Sudah Digunakan Pada Transaksi'))); }

			$q = $db->delete('mjurubayar', array('kodejurubayar' => $kode));

			if ($q) {

				// panggil fungsi untuk log history
				log_history(
					$kode,
					'MASTER JURU BAYAR',
					$act,
					array(
						array(
							'nama' =>  'header',
							'tabel' => 'mjurubayar',
							'kode' =>  'kodejurubayar'
						),
					),
					$_SESSION['user']
				);

				echo json_encode(array('success'=>true));
			} else {
				echo json_encode(array('errorMsg'=>'Terdapat Data Error, Data Tidak Dapat Dihapus'));
			}
		}
	break;
	//========================= END MASTER DATA JURU BAYAR ===========================

	//========================= MASTER DATA EKSPEDISI ===========================
	case 'ekspedisi' :
		if ($act=='insert' || $act=='edit') {
			$kode	= $_POST['KODEEKSPEDISI'];
			$nama	= $_POST['NAMAEKSPEDISI'];
			$status	= isset($_POST['STATUS']) ? $_POST['STATUS'] : 0;

			if ($_POST['NAMAEKSPEDISI'] == '') die(json_encode(array('errorMsg' => 'Company Name Tidak Boleh Kosong')));

			if ($kode=='') {
				$temp_kode = '';//'C'.substr($_POST['NAMAEKSPEDISI'], 0, 1);
				$kode = $temp_kode.get_max_urutan('mekspedisi', 'kodeekspedisi', $temp_kode, 2);
			}

			//CEK APAKAH KODE SUDAH DIGUNAKAN
			if ($act=='insert') {
				$p = $db->prepare('select kodeekspedisi, namaekspedisi from mekspedisi where kodeekspedisi=?');
				$q = $db->execute($p, $kode);
				$r = $db->fetch($q);
				if ($r->KODEEKSPEDISI!='') {
					die(json_encode(array('errorMsg' => 'Shipping/Ekspedition ID Sudah Digunakan Oleh Shipping/Ekspedition ('.$r->KODEEKSPEDISI.') '.$r->NAMAEKSPEDISI.', ID Tidak Dapat Digunakan')));
				}

				$p = $db->prepare('select kodeekspedisi, namaekspedisi from mekspedisi where namaekspedisi=?');
				$q = $db->execute($p, $nama);
				$r = $db->fetch($q);
				if ($r->NAMAEKSPEDISI!='') {
					die(json_encode(array('errorMsg' => 'Shipping/Ekspedition Name Sudah Digunakan Oleh Shipping/Ekspedition ('.$r->KODEEKSPEDISI.') '.$r->NAMAEKSPEDISI.', Nama Tidak Dapat Digunakan')));
				}
				$status = 1;
			} else if ($act=='edit') {
				$p = $db->prepare('select kodeekspedisi, namaekspedisi from mekspedisi where kodeekspedisi<>? and namaekspedisi=?');
				$q = $db->execute($p, array($kode, $nama));
				$r = $db->fetch($q);
				if ($r->NAMAEKSPEDISI!='') {
					die(json_encode(array('errorMsg' => 'Shipping/Ekspedition Name Sudah Digunakan Oleh Shipping/Ekspedition ('.$r->KODEEKSPEDISI.') '.$r->NAMAEKSPEDISI.', Nama Tidak Dapat Digunakan')));
				}
			}
			$tr = $db->start_trans();

			$data_values = array (
				$kode, $_POST['NAMAEKSPEDISI'], $_POST['ALAMAT'], $_POST['KOTA'], $_POST['PROPINSI'],
				$_POST['NEGARA'], $_POST['KODEPOS'], $_POST['TELP'], $_POST['FAX'], $_POST['EMAIL'],
				$_POST['CONTACTPERSON'], $_POST['TELPCP'], $_POST['EMAILCP'], $_POST['REMARK'], $_SESSION['user'],
				date("Y-m-d"), $status
			);
			$query = $db->insert('mekspedisi', $data_values, $tr);
			if (!$query) {
				$db->rollback($tr);
				die(json_encode(array('errorMsg' => 'Simpan Data Gagal')));
			}

			$db->commit($tr);

			// panggil fungsi untuk log history
			log_history(
				$kode,
				'MASTER EXPEDITION',
				$act,
				array(
					array(
						'nama' => 'header',
						'tabel' => 'mekspedisi',
						'kode' => 'kodeekspedisi'
					),
				),
				$_SESSION['user']
			);

			echo json_encode(array('success' => true));
		} else if ($act=='delete') {
			$kode = $_POST['id'];

			/*
			$p = $db->prepare('select first 1 skip 0 kodeekspedisi as kode from kartuhutang where kodesupplier='$kode');
			$r = $db->fetch($q);

			if ($r->KODE<>'') { die(json_encode(array('errorMsg'=>'Expedition Data Tidak Dihapus, Data Sudah Digunakan Pada Transaksi'))); }
			*/

			$q = $db->delete('mekspedisi', array('kodeekspedisi' => $kode));

			if ($q) {

				// panggil fungsi untuk log history
				log_history(
					$kode,
					'MASTER EXPEDITION',
					$act,
					array(
						array(
							'nama' => 'header',
							'tabel' => 'mekspedisi',
							'kode' => 'kodeekspedisi'
						),
					),
					$_SESSION['user']
				);

				echo json_encode(array('success'=>true));
			} else {
				echo json_encode(array('errorMsg'=>'Terdapat Data Error, Data Tidak Dapat Dihapus'));
			}
		}
	break;
	//========================= END MASTER DATA EKSPEDISI ===========================

	//========================= MASTER DATA MARKETING ===========================
	case 'marketing' :
		if ($act=='insert' || $act=='edit') {
			$kode	= $_POST['KODEMARKETING'];
			$nama	= $_POST['NAMAMARKETING'];
			$status	= isset($_POST['STATUS']) ? $_POST['STATUS'] : 0;

			if ($_POST['NAMAMARKETING'] == '') die(json_encode(array('errorMsg' => 'Nama Marketing Tidak Boleh Kosong')));

			if ($kode=='') {
				$temp_kode = 'M';
				$kode = $temp_kode.get_max_urutan('mmarketing', 'kodemarketing', $temp_kode, 3);
			}

			//CEK APAKAH KODE SUDAH DIGUNAKAN
			if ($act=='insert'){
				$p = $db->prepare('select kodemarketing, namamarketing from mmarketing where kodemarketing=?');
				$q = $db->execute($p, $kode);
				$r = $db->fetch($q);
				if ($r->KODEMARKETING!='') {
					die(json_encode(array('errorMsg' => 'ID Marketing Sudah Digunakan Oleh Marketing ('.$r->KODEMARKETING.') '.$r->NAMAMARKETING.', ID Tidak Dapat Digunakan')));
				}
				$p = $db->prepare('select kodemarketing, namamarketing from mmarketing where namamarketing=?');
				$q = $db->execute($p, $nama);
				$r = $db->fetch($q);
				if ($r->NAMAMARKETING!='') {
					die(json_encode(array('errorMsg' => 'Nama Marketing Sudah Digunakan Oleh Marketing ('.$r->KODEMARKETING.') '.$r->NAMAMARKETING.', Nama Tidak Dapat Digunakan')));
				}
				$status = 1;
			} else if ($act=='edit') {
				$p = $db->prepare('select kodemarketing, namamarketing from mmarketing where kodemarketing<>? and namamarketing=?');
				$q = $db->execute($p, array($kode, $nama));
				$r = $db->fetch($q);
				if ($r->NAMAMARKETING!='') {
					die(json_encode(array('errorMsg' => 'Nama Marketing Sudah Digunakan Oleh Marketing ('.$r->KODEMARKETING.') '.$r->NAMAMARKETING.', Nama Tidak Dapat Digunakan')));
				}
			}
			$tr = $db->start_trans();

			$data_values = array (
				$kode, $_POST['NAMAMARKETING'], $_POST['ALAMAT'], $_POST['KOTA'], $_POST['PROPINSI'],
				$_POST['NEGARA'], $_POST['KODEPOS'], $_POST['TELP'], $_POST['HP'], $_POST['EMAIL'],
				$_POST['REMARK'], $_SESSION['user'], date("Y-m-d"), $status
			);
			$query = $db->insert('mmarketing', $data_values, $tr);
			if (!$query) {
				$db->rollback($tr);
				die(json_encode(array('errorMsg' => 'Simpan Data Gagal')));
			}

			$db->commit($tr);

			// panggil fungsi untuk log history
			log_history(
				$kode,
				'MASTER MARKETING',
				$act,
				array(
					array(
						'nama' => 'header',
						'tabel' => 'mmarketing',
						'kode' => 'kodemarketing'
					),
				),
				$_SESSION['user']
			);

			echo json_encode(array('success' => true));
		} else if ($act=='delete') {
			$kode = $_POST['id'];

			$p = $db->prepare('select first 1 skip 0 kodemarketing as kode from kartupiutang where kodemarketing=?');
			$q = $db->execute($p, $kode);
			$r = $db->fetch($q);

			if ($r->KODE<>'') { die(json_encode(array('errorMsg'=>'Data Marketing Tidak Dapat Dihapus, Data Sudah Digunakan Pada Transaksi'))); }

			$q = $db->delete('mmarketing', array('kodemarketing' => $kode));

			if ($q) {
				// panggil fungsi untuk log history
				log_history(
					$kode,
					'MASTER MARKETING',
					$act,
					array(
						array(
							'nama' => 'header',
							'tabel' => 'mmarketing',
							'kode' => 'kodemarketing'
						),
					),
					$_SESSION['user']
				);
				echo json_encode(array('success'=>true));
			} else {
				echo json_encode(array('errorMsg'=>'Terdapat Data Error, Data Tidak Dapat Dihapus'));
			}
		}
	break;
	//========================= END MASTER DATA MARKETING ===========================

	//========================= MASTER DATA KATEGORI CUSTOMER ===========================
	case 'kategori_customer' :
		if ($act=='insert' || $act=='edit'){
			$kode   = $_POST['KODEKATEGORICUSTOMER'];
			$nama   = $_POST['NAMAKATEGORICUSTOMER'];
			$status = $_POST['STATUS'];

			if ($kode=='') {
				//$temp_kode = 'K'.substr($nama, 0, 1);
				//$kode = $temp_kode.get_max_urutan('mkategoribarang', 'kodekategoribarang', $temp_kode, 3);
			}

			if ($kode == '') die(json_encode(array('errorMsg' => 'ID Kategori Customer Tidak Boleh Kosong')));
			if ($nama == '') die(json_encode(array('errorMsg' => 'Deskripsi Kategori Customer Tidak Boleh Kosong')));

			// buka koneksi

			//CEK APAKAH KODE DAN NAMA SUDAH DIGUNAKAN
			if ($act=='insert') {
				$p = $db->prepare('select kodekategoricustomer, namakategoricustomer from mkategoricustomer where kodekategoricustomer=?');
				$q = $db->execute($p, $kode);
				$r = $db->fetch($q);
				if ($r->KODEKATEGORICUSTOMER!='') {
					die(json_encode(array('errorMsg' => 'ID Kategori Customer Sudah Digunakan Oleh Kategori Customer ('.$r->KODEKATEGORICUSTOMER.') '.$r->NAMAKATEGORICUSTOMER.', ID Tidak Dapat Digunakan')));
				}
				$p = $db->prepare('select kodekategoricustomer, namakategoricustomer from mkategoricustomer where namakategoricustomer=?');
				$q = $db->execute($p, $kode);
				$r = $db->fetch($q);
				if ($r->NAMAKATEGORICUSTOMER!='') {
					die(json_encode(array('errorMsg' => 'Deskripsi Kategori Customer Sudah Digunakan Oleh Kategori Customer ('.$r->KODEKATEGORICUSTOMER.') '.$r->NAMAKATEGORICUSTOMER.', Deskripsi Tidak Dapat Digunakan')));
				}
				$status = 1;
			} else if ($act=='edit') {
				$p = $db->prepare('select kodekategoricustomer, namakategoricustomer from mkategoricustomer where kodekategoricustomer<>? and namakategoricustomer=?');
				$q = $db->execute($p, array($kode, $nama));
				$r = $db->fetch($q);
				if ($r->NAMAKATEGORICUSTOMER!='') {
					die(json_encode(array('errorMsg' => 'Deskripsi Kategori Customer Sudah Digunakan Oleh Kategori Customer ('.$r->KODEKATEGORICUSTOMER.') '.$r->NAMAKATEGORICUSTOMER.', Deskripsi Tidak Dapat Digunakan')));
				}
			}

			// start transaction
			$tr = $db->start_trans();

			$data_values = array (
				$kode, $nama, $_POST['REMARK'],$_SESSION['user'], date("Y-m-d"),
				$status
			);
			$query = $db->insert('mkategoricustomer', $data_values, $tr);
			if (!$query) {
				// rollback transaction
				$db->rollback($tr);
				die(json_encode(array('errorMsg' => 'Simpan Data Gagal')));
			}

			// commit transaction
			$db->commit($tr);

			// panggil fungsi untuk log history
			log_history(
				$kode,
				'MASTER KATEGORI CUSTOMER',
				$act,
				array(
					array(
						'nama' => 'header',
						'tabel' => 'mkategoricustomer',
						'kode' => 'kodekategoricustomer'
					),
				),
				$_SESSION['user']
			);

			echo json_encode(array('success' => true));
		} else if ($act=='delete'){
			$kode = $_POST['id'];

			$p = $db->prepare('select first 1 skip 0 kodekategoricustomer as kode from mbarang where kodekategoricustomer=?');
			$q = $db->execute($p, $kode);
			$r = $db->fetch($q);

			if ($r->KODE<>'') { die(json_encode(array('errorMsg'=>'Data Kategori Customer Tidak Dapat Dihapus, Data Sudah Digunakan Pada Transaksi'))); }

			$q = $db->delete('mkategoribarang', array('kodekategoricustomer' => $kode));

			if ($q) {

				// panggil fungsi untuk log history
				log_history(
					$kode,
					'MASTER KATEGORI CUSTOMER',
					$act,
					array(
						array(
							'nama' => 'header',
							'tabel' => 'mkategoricustomer',
							'kode' => 'kodekategoricustomer'
						),
					),
					$_SESSION['user']
				);

				echo json_encode(array('success'=>true));
			} else {
				echo json_encode(array('errorMsg'=>'Terdapat Data Error, Data Tidak Dapat Dihapus'));
			}
		}
	break;
	//========================= END MASTER DATA KATEGORI CUSTOMER ===========================

	//========================= MASTER DATA CUSTOMER ===========================
	case 'customer' :
		if ($act=='insert' || $act=='edit') {
			$kode	= $_POST['KODECUSTOMER'];
			$nama	= $_POST['NAMACUSTOMER'];
			$alamat = $_POST['ALAMAT'];
			$status	= isset($_POST['STATUS']) ? $_POST['STATUS'] : 0;

			if ($_POST['NAMACUSTOMER'] == '') die(json_encode(array('errorMsg' => 'Nama Customer Tidak Boleh Kosong')));

			if ($kode=='') {
				$tgl = date("Y.m.d");

				/*$temp_kode = 'CST/'.$_SESSION['KODELOKASI'].'/';

				$urutan = get_new_urutan('mcustomer', 'kodecustomer', array($temp_kode, substr($tgl, 2, 2)));

				$kode = $temp_kode.$urutan.'/'.substr($tgl, 5, 2).'/'.substr($tgl, 2, 2);
				*/
				$temp_kode = 'CST/'.$_SESSION['KODELOKASI'].'/'.substr($tgl, 2, 2).substr($tgl, 5, 2).substr($tgl, -2);

				$urutan = get_max_urutan('mcustomer', 'kodecustomer', $temp_kode, 4);

				$kode = $temp_kode.$urutan;
			}

			//CEK APAKAH KODE SUDAH DIGUNAKAN
			if ($act=='insert') {
				$p = $db->prepare('select kodecustomer, namacustomer from mcustomer where kodecustomer=?');
				$q = $db->execute($p, $kode);
				$r = $db->fetch($q);
				if ($r->KODECUSTOMER!='') {
					die(json_encode(array('errorMsg' => 'Kode Customer Sudah Digunakan Oleh Customer ('.$r->KODECUSTOMER.') '.$r->NAMACUSTOMER.', Kode Tidak Dapat Digunakan')));
				}
				$p = $db->prepare('select kodecustomer, namacustomer from mcustomer where namacustomer=? and alamat=?');
				$q = $db->execute($p, array($kode, $alamat));
				$r = $db->fetch($q);
				if ($r->NAMACUSTOMER!='') {
					die(json_encode(array('errorMsg' => 'Nama Customer dan Alamat Sudah Digunakan Oleh Customer ('.$r->KODECUSTOMER.') '.$r->NAMACUSTOMER.', Nama Tidak Dapat Digunakan')));
				}
				$status = 1;
			} else if ($act=='edit') {
				$p = $db->prepare('select kodecustomer, namacustomer from mcustomer where kodecustomer<>? and namacustomer=? and alamat=?');
				$q = $db->execute($p, array($kode, $nama, $alamat));
				$r = $db->fetch($q);
				if ($r->NAMACUSTOMER!='') {
					die(json_encode(array('errorMsg' => 'Nama Customer dan Alamat Sudah Digunakan Oleh Customer ('.$r->KODECUSTOMER.') '.$r->NAMACUSTOMER.', Nama Tidak Dapat Digunakan')));
				}
			}

			$tr = $db->start_trans();

			/*$max_credit = $_POST['MAXCREDIT']=='' ? 0 : $_POST['MAXCREDIT'];
			$disc = $_POST['DISC']=='' ? 0 : $_POST['DISC'];
			*/
			$data_values = array (
				$kode, $_POST['NAMACUSTOMER'], $alamat, $_POST['KOTA'], '',
				$_POST['TELP'], $_POST['HP'], $_POST['TEMPATLAHIR'], $_POST['TGLLAHIR'], $_POST['JENISKELAMIN'],
				$_POST['AGAMA'], $_POST['RIWAYATKESEHATAN'], $_POST['PEKERJAAN'], $_POST['HOBI'], $_POST['REMARK'],
				$_SESSION['user'], date("Y-m-d"), $status
			);
			$query = $db->insert('mcustomer', $data_values, $tr);
			if (!$query) {
				$db->rollback($tr);
				die(json_encode(array('errorMsg' => 'Simpan Data Gagal')));
			}

			$db->commit($tr);

			// panggil fungsi untuk log history
			log_history(
				$kode,
				'MASTER CUSTOMER',
				$act,
				array(
					array(
						'nama' => 'header',
						'tabel' => 'mcustomer',
						'kode' => 'kodecustomer'
					),
				),
				$_SESSION['user']
			);

			echo json_encode(array('success' => true, 'kode' => $kode));
		} else if ($act=='delete') {
			$kode = $_POST['id'];

			$p = $db->prepare('select first 1 skip 0 kodecustomer as kode from kartupiutang where kodecustomer=?');
			$q = $db->execute($p, $kode);
			$r = $db->fetch($q);

			if ($r->KODE<>'') { die(json_encode(array('errorMsg'=>'Data Customer Tidak Dapat Dihapus, Data Sudah Digunakan Pada Transaksi'))); }

			$q = $db->delete('mcustomer', array('kodecustomer' => $kode));

			if ($q) {
				// panggil fungsi untuk log history
				log_history(
					$kode,
					'MASTER CUSTOMER',
					$act,
					array(
						array(
							'nama' => 'header',
							'tabel' => 'mcustomer',
							'kode' => 'kodecustomer'
						),
					),
					$_SESSION['user']
				);

				echo json_encode(array('success'=>true));
			} else {
				echo json_encode(array('errorMsg'=>'Terdapat Data Error, Data Tidak Dapat Dihapus'));
			}
		}
	break;
	//========================= END MASTER DATA CUSTOMER ===========================

	//========================= MASTER DATA MEMBER ===========================
	case 'member' :
		if ($act=='insert' || $act=='edit') {
			$kode		  = $_POST['KODEMEMBER'];
			$kodecustomer = $_POST['KODECUSTOMER'];
			$jenis        = $_POST['JENIS'];
			$upline        = $_POST['UPLINE'];
			$status		  = isset($_POST['STATUS']) ? $_POST['STATUS'] : 0;

			if ($jenis==''){
				die(json_encode(array('errorMsg' => 'Jenis Member Tidak Boleh Kosong')));
			} else if ($jenis=='REFERENSI' && $upline==''){
				die(json_encode(array('errorMsg' => 'Informasi Upline Tidak Boleh Kosong')));
			}
			if ($kode=='') {
				$tgl = date("Y.m.d");

				/*$temp_kode = 'MBR/'.$_SESSION['KODELOKASI'].'/';

				$urutan = get_new_urutan('mmember', 'kodemember', array($temp_kode, substr($tgl, 2, 2)));

				$kode = $temp_kode.$urutan.'/'.substr($tgl, 5, 2).'/'.substr($tgl, 2, 2);*/

				$temp_kode = 'MBR/'.$_SESSION['KODELOKASI'].'/'.substr($tgl, 2, 2).substr($tgl, 5, 2);

				$urutan = get_max_urutan('mmember', 'kodemember', $temp_kode, 4);

				$kode = $temp_kode.$urutan;
			}

			//CEK APAKAH KODE SUDAH DIGUNAKAN
			if ($act=='insert') {
				$pr = $db->prepare('select kodecustomer, kodemember from mmember where kodecustomer=?');
				$ex = $db->execute($pr, $kodecustomer);
				$rs = $db->fetch($ex);
				if ($rs->KODECUSTOMER <> '') {
					die(json_encode(array('errorMsg' => 'Customer Sudah Menjadi Member dengan Kode Member ('.$rs->KODEMEMBER.')')));
				}

				$status = 1;
			} else if ($act=='edit') {
				$pr = $db->prepare("select kodecustomer, kodemember from mmember where kodemember=?");
				$ex = $db->execute($pr, $kode);
				$rs = $db->fetch($ex);
				$tgl = $rs->TGLDAFTAR;
				/*$p = $db->prepare('select kodemember, namacustomer from mcustomer where kodemember<>? and namacustomer=?');
				$q = $db->execute($p, array($kode, $nama));
				$r = $db->fetch($q);
				if ($r->NAMACUSTOMER!='') {
					die(json_encode(array('errorMsg' => 'Nama Customer Sudah Digunakan Oleh Customer ('.$r->KODECUSTOMER.') '.$r->NAMACUSTOMER.', Nama Tidak Dapat Digunakan')));
				}*/
			}

			$tr = $db->start_trans();

			/*$max_credit = $_POST['MAXCREDIT']=='' ? 0 : $_POST['MAXCREDIT'];
			$disc = $_POST['DISC']=='' ? 0 : $_POST['DISC'];
			*/
			$data_values = array (
				$kode, $kodecustomer, $_POST['NOKARTU'], $_POST['JENIS'],
				$_POST['UPLINE'], $tgl, $_POST['DISKON'], $_POST['REMARK'], $_SESSION['user'], date("Y-m-d"),
				$status
			);
			$query = $db->insert('mmember', $data_values, $tr);
			if (!$query) {
				$db->rollback($tr);
				die(json_encode(array('errorMsg' => 'Simpan Data Gagal')));
			}

			$db->commit($tr);

			// panggil fungsi untuk log history
			log_history(
				$kode,
				'MASTER MEMBER',
				$act,
				array(
					array(
						'nama' => 'header',
						'tabel' => 'mmember',
						'kode' => 'kodemember'
					),
				),
				$_SESSION['user']
			);

			echo json_encode(array('success' => true, 'kode' => $kode, 'kodecustomer' => $kodecustomer));
		} else if ($act=='delete') {
			$kode = $_POST['id'];

			/*$p = $db->prepare('select first 1 skip 0 kodemember as kode from kartupiutang where kodemember='$kode');
			$q = $db->execute($p, $kode);
			$r = $db->fetch($q);

			if ($r->KODE<>'') { die(json_encode(array('errorMsg'=>'Data Customer Tidak Dapat Dihapus, Data Sudah Digunakan Pada Transaksi'))); }*/

			$q = $db->delete('mmember', array('kodecustomer' => $kode));

			if ($q) {
				// panggil fungsi untuk log history
				log_history(
					$kode,
					'MASTER MEMBER',
					$act,
					array(
						array(
							'nama' => 'header',
							'tabel' => 'mmember',
							'kode' => 'kodecustomer'
						),
					),
					$_SESSION['user']
				);

				echo json_encode(array('success'=>true));
			} else {
				echo json_encode(array('errorMsg'=>'Terdapat Data Error, Data Tidak Dapat Dihapus'));
			}
		}
	break;
	//========================= END MASTER DATA MEMBER ===========================

	//========================= MASTER DATA LOKASI ===========================
	case 'lokasi' :
		if ($act=='insert' || $act=='edit') {
			$kode	= $_POST['KODELOKASI'];
			$nama	= $_POST['NAMALOKASI'];
			$jenis	= $_POST['JENIS'];
			$status	= isset($_POST['STATUS']) ? $_POST['STATUS'] : 0;
			$pusat	= isset($_POST['PUSAT']) ? $_POST['PUSAT'] : 0;
			if ($kode=='') die(json_encode(array('errorMsg' => 'Kode Lokasi Tidak Boleh Kosong')));
			if ($nama=='') die(json_encode(array('errorMsg' => 'Nama Lokasi Tidak Boleh Kosong')));
			if ($jenis=='') die(json_encode(array('errorMsg' => 'Jenis Lokasi Tidak Boleh Kosong')));

			if ($kode=='') {
				//$temp_kode = 'B'.substr($nama, 0, 1);
				//$kode = $temp_kode.get_max_urutan('mbarang', 'kodebarang', $temp_kode, 3);
			}

			//CEK APAKAH KODE DAN NAMA SUDAH DIGUNAKAN
			if ($act=='insert'){
				$p = $db->prepare('select kodelokasi, namalokasi from mlokasi where kodelokasi=?');
				$q = $db->execute($p, $kode);
				$r = $db->fetch($q);
				if ($r->KODELOKASI!='') {
					die(json_encode(array('errorMsg' => 'Kode Lokasi Sudah Digunakan Oleh Lokasi ('.$r->KODELOKASI.') '.$r->NAMALOKASI.', Kode Tidak Dapat Digunakan')));
				}
				$p = $db->prepare('select kodelokasi, namalokasi from mlokasi where namalokasi=?');
				$q = $db->execute($p, $nama);
				$r = $db->fetch($q);
				if ($r->NAMALOKASI!='') {
					die(json_encode(array('errorMsg' => 'Nama Lokasi Sudah Digunakan Oleh Lokasi ('.$r->KODELOKASI.') '.$r->NAMALOKASI.', Nama Lokasi Tidak Dapat Digunakan')));
				}
				$status = 1;
			} else if ($act=='edit'){
				$p = $db->prepare('select kodelokasi, namalokasi from mlokasi where kodelokasi<>? and namalokasi=?');
				$q = $db->execute($p, array($kode, $nama));
				$r = $db->fetch($q);
				if ($r->NAMALOKASI!='') {
					die(json_encode(array('errorMsg' => 'Nama Lokasi Sudah Digunakan Oleh Lokasi ('.$r->KODELOKASI.') '.$r->NAMALOKASI.', Nama Lokasi Tidak Dapat Digunakan')));
				}
				$status = 1;
			}
			$tr = $db->start_trans();

			$data_values = array (
				$kode, $nama, $_POST['NAMAPERUSAHAAN'], $_POST['ALAMAT'], $_POST['ALAMATPERUSAHAAN'], 
				$_POST['KOTA'], $_POST['KOTAPERUSAHAAN'], $_POST['PROPINSI'], $_POST['NEGARA'], $_POST['TELP'], 
				$_POST['TELPPERUSAHAAN'], $_POST['HP'], $pusat, $jenis, $_POST['NPWP'], 
				$_POST['REMARK'], $_SESSION['user'], date("Y-m-d"), $status
			);
			$query = $db->insert('mlokasi', $data_values, $tr);
			if (!$query) {
				$db->rollback($tr);
				die(json_encode(array('errorMsg' => 'Simpan Data Gagal')));
			}

			// commit transaction
			$db->commit($tr);

			// panggil fungsi untuk log history
			log_history(
				$kode,
				'MASTER LOKASI',
				$act,
				array(
					array(
						'nama' => 'header',
						'tabel' => 'mlokasi',
						'kode' => 'kodelokasi'
					),
				),
				$_SESSION['user']
			);

			echo json_encode(array('success' => true));
		} else if ($act=='delete') {
			$kode = $_POST['id'];

			$p = $db->prepare('select first 1 skip 0 kodelokasi as kode from kartustok where kodelokasi=?');
			$q = $db->execute($p, $kode);
			$r = $db->fetch($q);

			if ($r->KODE<>'') { die(json_encode(array('errorMsg'=>'Data Lokasi Tidak Dapat Dihapus, Sudah Digunakan Pada Transaksi'))); }

			$q = $db->delete('mlokasi', array('kodelokasi' => $kode));

			if ($q) {
				// panggil fungsi untuk log history
				log_history(
					$kode,
					'MASTER LOKASI',
					$act,
					array(
						array(
							'nama' => 'header',
							'tabel' => 'mlokasi',
							'kode' => 'kodelokasi'
						),
					),
					$_SESSION['user']
				);

				echo json_encode(array('success'=>true));
			} else {
				echo json_encode(array('errorMsg'=>'Terdapat Data Error, Data Tidak Dapat Dihapus'));
			}
		}
	break;
	//========================= END MASTER DATA LOKASI ===========================

	//========================= MASTER DATA GUDANG ===========================
	case 'gudang' :
		if ($act=='insert' || $act=='edit') {
			$kodelokasi	= $_POST['KODELOKASI'];
			$kode	    = $_POST['KODEGUDANG'];
			$nama	    = $_POST['NAMAGUDANG'];
			$status	    = isset($_POST['STATUS']) ? $_POST['STATUS'] : 0;

			if ($kodelokasi=='') die(json_encode(array('errorMsg' => 'Data Lokasi Tidak Boleh Kosong')));
			if ($kode=='') die(json_encode(array('errorMsg' => 'Kode Gudang Tidak Boleh Kosong')));
			if ($nama=='') die(json_encode(array('errorMsg' => 'Nama Gudang Tidak Boleh Kosong')));

			if ($kode=='') {
				//$temp_kode = 'B'.substr($nama, 0, 1);
				//$kode = $temp_kode.get_max_urutan('mbarang', 'kodebarang', $temp_kode, 3);
			}

			//CEK APAKAH KODE DAN NAMA SUDAH DIGUNAKAN
			if ($act=='insert'){
				$p = $db->prepare('select kodegudang, namagudang from mgudang where kodegudang=?');
				$q = $db->execute($p, $kode);
				$r = $db->fetch($q);
				if ($r->KODEGUDANG!='') {
					die(json_encode(array('errorMsg' => 'Kode Gudang Sudah Digunakan Oleh Gudang ('.$r->KODEGUDANG.') '.$r->NAMAGUDANG.', Kode Tidak Dapat Digunakan')));
				}

				$p = $db->prepare('select kodegudang, namagudang from mgudang where namagudang=?');
				$q = $db->execute($p, $nama);
				$r = $db->fetch($q);
				if ($r->NAMAGUDANG!='') {
					die(json_encode(array('errorMsg' => 'Nama Gudang Sudah Digunakan Oleh Gudang ('.$r->KODEGUDANG.') '.$r->NAMAGUDANG.', Nama Gudang Tidak Dapat Digunakan')));
				}

				$p = $db->prepare('select kodegudang, namagudang from mgudang where kodelokasi=? and jenis=?');
				$q = $db->execute($p, array($kodelokasi, $_POST['JENIS']));
				$r = $db->fetch($q);
				if ($r->NAMAGUDANG!='') {
					die(json_encode(array('errorMsg' => 'Jenis Gudang Telah Digunakan oleh '.$r->NAMAGUDANG)));
				}

				$status = 1;
			} else if ($act=='edit'){
				$p = $db->prepare('select kodegudang, namagudang from mgudang where kodegudang<>? and namagudang=?');
				$q = $db->execute($p, array($kode, $nama));
				$r = $db->fetch($q);
				if ($r->NAMALOKASI!='') {
					die(json_encode(array('errorMsg' => 'Nama Gudang Sudah Digunakan Oleh Gudang ('.$r->KODEGUDANG.') '.$r->NAMAGUDANG.', Nama Gudang Tidak Dapat Digunakan')));
				}
				//$status = 1;
				
				$p = $db->prepare('select kodegudang, namagudang from mgudang where kodegudang<>? and kodelokasi=? and jenis=?');
				$q = $db->execute($p, array($kode, $kodelokasi, $_POST['JENIS']));
				$r = $db->fetch($q);
				if ($r->NAMAGUDANG!='') {
					die(json_encode(array('errorMsg' => 'Jenis Gudang Telah Digunakan oleh '.$r->NAMAGUDANG)));
				}
			}
			$tr = $db->start_trans();

			$data_values = array (
				$kode, $kodelokasi, $nama, $_POST['JENIS'], $_SESSION['user'],
				date("Y-m-d"), $status
			);
			$query = $db->insert('mgudang', $data_values, $tr);
			if (!$query) {
				$db->rollback($tr);
				die(json_encode(array('errorMsg' => 'Simpan Data Gagal')));
			}

			// commit transaction
			$db->commit($tr);

			// panggil fungsi untuk log history
			log_history(
				$kode,
				'MASTER GUDANG',
				$act,
				array(
					array(
						'nama'  => 'header',
						'tabel' => 'mgudang',
						'kode'  => 'kodegudang'
					),
				),
				$_SESSION['user']
			);

			echo json_encode(array('success' => true));
		} else if ($act=='delete') {
			$kode = $_POST['id'];

			$p = $db->prepare('select first 1 skip 0 kodegudang as kode from kartustok where kodegudang=?');
			$q = $db->execute($p, $kode);
			$r = $db->fetch($q);

			if ($r->KODE<>'') { die(json_encode(array('errorMsg'=>'Data Gudang Tidak Dapat Dihapus, Sudah Digunakan Pada Transaksi'))); }

			$q = $db->delete('mgudang', array('kodegudang' => $kode));

			if ($q) {
				// panggil fungsi untuk log history
				log_history(
					$kode,
					'MASTER GUDANG',
					$act,
					array(
						array(
							'nama'  => 'header',
							'tabel' => 'mgudang',
							'kode'  => 'kodegudang'
						),
					),
					$_SESSION['user']
				);

				echo json_encode(array('success'=>true));
			} else {
				echo json_encode(array('errorMsg'=>'Terdapat Data Error, Data Tidak Dapat Dihapus'));
			}
		}
	break;
	//========================= END MASTER DATA GUDANG ===========================

	//========================= MASTER DATA PEGAWAI ===========================
	case 'pegawai' :
		if ($act=='insert' || $act=='edit') {
			$kode	 = $_POST['USERID'];
			$nama	 = $_POST['USERNAME'];
			$status	 = isset($_POST['STATUS']) ? $_POST['STATUS'] : 0;
			$sales	 = isset($_POST['SALES']) ? $_POST['SALES'] : 0;
			$ro   	 = isset($_POST['RO']) ? $_POST['RO'] : 0;
			$setel 	 = isset($_POST['SETEL']) ? $_POST['SETEL'] : 0;
			$fitting = isset($_POST['FITTING']) ? $_POST['FITTING'] : 0;
			$edger   = isset($_POST['EDGER']) ? $_POST['EDGER'] : 0;

			$tg = isset($_POST['TAMPILGRANDTOTAL']) ? $_POST['TAMPILGRANDTOTAL'] : 0;
			$pu = isset($_POST['PRINTULANG']) ? $_POST['PRINTULANG'] : 0;
			$otorisasi = isset($_POST['OTORISASI']) ? $_POST['OTORISASI'] : 0;

			if ($nama == '') die(json_encode(array('errorMsg' => 'Nama Pegawai Tidak Boleh Kosong')));

			if ($kode=='') {
				$tgl = date("Y.m.d");
				/*$temp_kode = 'P';
				$kode = $temp_kode.get_max_urutan('muser', 'userid', $temp_kode, 4);*/
			}

			$a_kode_cek = json_decode($_POST['data_detail']);
			$a_lokasi   = json_decode($_POST['data_lokasi']);

			// pengecekan password
			$q = $db->select('muser', array('pass'), array('userid'=>$kode));
			$r = $db->fetch($q);

			if ($r->PASS === $_POST['PASS']) {
				$pass	 = $_POST['PASS'];
				$re_pass = $_POST['RE_PASS'];
			} else {
				$pass	 = encrypt_data($_POST['PASS']);
				$re_pass = encrypt_data($_POST['RE_PASS']);
			}

			$a_temp = array();
			foreach ($a_kode_cek as $item) {
				foreach ($item->children as $row) {
					$a_temp[] = $row;
				}
			}

			$q = $db->query('select kodemenu, namamenu from mmenu order by kodemenu');
			$a_menu = array();
			while($r = $db->fetch($q)) {
				$a_menu[] = array(
					'kode' => $r->KODEMENU,
					'menu' => $r->NAMAMENU,
				);
			}
			$a_menu = json_decode(json_encode($a_menu));

			//CEK APAKAH KODE SUDAH DIGUNAKAN
			if ($act=='insert') {
				$p = $db->prepare('select userid, username from muser where userid=?');
				$q = $db->execute($p, $kode);
				$r = $db->fetch($q);
				if ($r->USERID!='') {
					die(json_encode(array('errorMsg' => 'Kode Pegawai Sudah Digunakan Oleh Pegawai ('.$r->USERID.') '.$r->USERNAME.', Kode Tidak Dapat Digunakan')));
				}
				$p = $db->prepare('select userid, username from muser where username=?');
				$q = $db->execute($p, $nama);
				$r = $db->fetch($q);
				if ($r->USERNAME!='') {
					die(json_encode(array('errorMsg' => 'Nama Pegawai Sudah Digunakan Oleh Pegawai ('.$r->USERID.') '.$r->USERNAME.', Nama Tidak Dapat Digunakan')));
				}
				$status = 1;

				// cek gambar
				if ($_FILES["FILEGAMBAR"]['name'] == '')
					$gambar = '';

			} else if ($act=='edit') {
				$p = $db->prepare('select userid, username from muser where userid<>? and username=?');
				$q = $db->execute($p, array($kode, $nama));
				$r = $db->fetch($q);
				if ($r->USERNAME!='') {
					die(json_encode(array('errorMsg' => 'Nama Pegawai Sudah Digunakan Oleh Pegawai ('.$r->USERID.') '.$r->USERNAME.', Nama Tidak Dapat Digunakan')));
				}

				$gambar = $_POST['GAMBAR'];
			}


			if ($_FILES["FILEGAMBAR"]['name'] != '') {
				// upload gambar
				$target_dir = "../../gambar-pegawai/";
				$uploadOk = 1;
				$imageFileType = pathinfo($_FILES['FILEGAMBAR']['name'], PATHINFO_EXTENSION);
				$target_file = $target_dir . str_replace('/', '.', $kode) . '.' . $imageFileType;
				$gambar = str_replace('/', '.', $kode) . '.' . $imageFileType;
				// Check if image file is a actual image or fake image

				$check = getimagesize($_FILES["FILEGAMBAR"]["tmp_name"]);
				if ($check !== false) {
					$uploadOk = 1;
				} else {
					die(json_encode(array('errorMsg' => 'File yang Diupload bukan gambar')));
					$uploadOk = 0;
				}

				if ($_POST['GAMBAR'] != '') {
					unlink($target_dir.$_POST['GAMBAR']);
				}
				// Check if file already exists
				if (file_exists($target_file)) {
					$uploadOk = 0;
				}
				// Check file size
				if ($_FILES["FILEGAMBAR"]["size"] > 500000) {
					die(json_encode(array('errorMsg' => 'Sorry, your file is too large.')));

					$uploadOk = 0;
				}
				// Allow certain file formats
				if ( ! in_array(strtolower($imageFileType), array('jpg', 'png', 'jpeg', 'gif')) ) {
					die(json_encode(array('errorMsg' => 'Sorry, only JPG, JPEG, PNG & GIF files are allowed.')));

					$uploadOk = 0;
				}
				// Check if $uploadOk is set to 0 by an error
				if ($uploadOk == 0) {
					die(json_encode(array('errorMsg' => 'Sorry, your file was not uploaded.')));
				// if everything is ok, try to upload file
				} else {
					if (move_uploaded_file($_FILES["FILEGAMBAR"]["tmp_name"], $target_file)) {
						//echo "The file ". basename($_FILES["GAMBAR"]["name"]). " has been uploaded.";
					} else {
						die(json_encode(array('errorMsg' => 'Sorry, there was an error uploading your file.')));
					}
				}
			}

			$tr = $db->start_trans();

			// insert informasi umum
			$data_values = array (
				$kode, $nama, $pass, $otorisasi, $tg,
				$pu, 0, $_POST['ALAMAT'], $_POST['KOTA'], $_POST['TELP'],
				$_POST['TEMPATLAHIR'], $_POST['TGLLAHIR'], $_POST['JENISKELAMIN'], $_POST['JABATAN'], $_POST['KODELOKASI'],
				$sales, $ro, $setel, $fitting, $edger,
				$_POST['REMARK'], $gambar, $_SESSION['user'], date("Y-m-d"), $status
			);
			$query = $db->insert('muser', $data_values, $tr);
			if (!$query) {
				$db->rollback($tr);
				die(json_encode(array('errorMsg' => 'Simpan Data Gagal')));
			}

			// insert hak akses
			$sql = $db->insert('muserakses', 6, $tr, true);
			$pr = $db->prepare($sql, $tr);
			foreach ($a_menu as $i) {
				$hakakses = 0;
				$tambah = 0;
				$ubah = 0;
				$hapus = 0;

				foreach ($a_temp as $row) {
					if ($i->kode==$row->id) {
						$hakakses = $row->hakakses;
						if ($hakakses==1) {
							$tambah = $row->tambah;
							$ubah = $row->ubah;
							$hapus = $row->hapus;
						}
						break;
					}
				}

				$hakakses = $i->menu=='-' ? 1 : $hakakses;
				$data_values = array (
					$kode, $i->kode, $hakakses, $tambah, $ubah,
					$hapus
				);
				$exe = $db->execute($pr, $data_values);
				if (!$exe) { $db->rollback($tr); die(json_encode(array('errorMsg'=>'Simpan Data Gagal 2 '.print_r($data_values)))); }
			}

			// insert lokasi login
			if (count($a_lokasi) > 0) {
				$pr = $db->prepare('insert into muserlokasi values (?, ?)', $tr);

				foreach ($a_lokasi as $item) {
					$data_values = array (
						$kode, $item->KODELOKASI
					);
					$exe = $db->execute($pr, $data_values);
					if (!$exe) { $db->rollback($tr); die(json_encode(array('errorMsg'=>'Simpan Data Gagal 3'))); }
				}
			}

			$db->commit($tr);

			// panggil fungsi untuk log history
			log_history(
				$kode,
				'MASTER USER',
				$act,
				array(
					array(
						'nama' => 'header',
						'tabel' => 'muser',
						'kode' => 'userid'
					),
				),
				$_SESSION['user']
			);

			echo json_encode(array('success' => true));
		} else if ($act=='delete') {
			$kode = $_POST['id'];

			$p = $db->prepare('select first 1 skip 0 kodejual as kode
							   from tjual where (KODEPEGAWAI_SALES=? or KODEPEGAWAI_RO=? or KODEPEGAWAI_EDGER=? or
												 KODEPEGAWAI_SETEL=? or KODEPEGAWAI_FITTING=?)');
			$q = $db->execute($p, array($kode, $kode, $kode, $kode, $kode));
			$r = $db->fetch($q);

			if ($r->KODE<>'') { die(json_encode(array('errorMsg'=>'Data Pegawai Tidak Dapat Dihapus, Data Sudah Digunakan Pada Transaksi'))); }

			$q = $db->delete('muser', array('userid' => $kode));

			if ($q) {
				// panggil fungsi untuk log history
				log_history(
					$kode,
					'MASTER PEGAWAI',
					$act,
					array(
						array(
							'nama' => 'header',
							'tabel' => 'muser',
							'kode' => 'userid'
						),
					),
					$_SESSION['user']
				);

				echo json_encode(array('success'=>true));
			} else {
				echo json_encode(array('errorMsg'=>'Terdapat Data Error, Data Tidak Dapat Dihapus'));
			}
		} else if ($act=='view') {
			// view hak akses menu dan lokasi login
			$kode = $_POST['id'];

			// select lokasi login
			$pr  = $db->prepare("select * from muserlokasi where userid = ?");
			$exe = $db->execute($pr, $kode);

			$data_lokasi = array();
			while($r = $db->fetch($exe)) {
				$data_lokasi[] = $r;
			}

			echo json_encode(array('success' => true, 'data_hakakses'=>$data_hakakses, 'data_lokasi'=>$data_lokasi));
		}
	break;
	//========================= END MASTER DATA PEGAWAI ===========================

	//========================= MASTER DATA GAMBAR LENSA ===========================
	case 'gambar_lensa' :
		if ($act=='insert' || $act=='edit') {
			$kode	 = $_POST['KODELENSA'];
			$nama	 = $_POST['NAMALENSA'];

			if ($nama == '') die(json_encode(array('errorMsg' => 'Nama Lensa Tidak Boleh Kosong')));

			if ($kode=='') {
				$tgl = date("Y.m.d");
				$temp_kode = 'LS';
				$kode = $temp_kode.get_max_urutan('mgambarlensa', 'kodelensa', $temp_kode, 4);
			}
			//CEK APAKAH KODE SUDAH DIGUNAKAN
			if ($act=='insert') {
				$p = $db->prepare('select kodelensa, namalensa from mgambarlensa where kodelensa=?');
				$q = $db->execute($p, $nama);
				$r = $db->fetch($q);
				if ($r->NAMALENSA!='') {
					die(json_encode(array('errorMsg' => 'Nama Lensa Sudah Digunakan Oleh Lensa ('.$r->KODELENSA.') '.$r->NAMALENSA.', Nama Tidak Dapat Digunakan')));
				}
				$status = 1;

				// cek gambar
				if ($_FILES["FILEGAMBAR"]['name'] == '')
					$gambar = '';

			} else if ($act=='edit') {
				$p = $db->prepare('select kodelensa, namalensa from mgambarlensa where kodelensa<>? and namalensa=?');
				$q = $db->execute($p, array($kode, $nama));
				$r = $db->fetch($q);
				if ($r->NAMALENSA!='') {
					die(json_encode(array('errorMsg' => 'Nama Lensa Sudah Digunakan Oleh Lensa ('.$r->KODELENSA.') '.$r->NAMALENSA.', Nama Tidak Dapat Digunakan')));
				}

				$gambar = $_POST['GAMBAR'];
			}


			if ($_FILES["FILEGAMBAR"]['name'] != '') {
				// upload gambar
				$target_dir = "../../gambar-lensa/";
				$uploadOk = 1;
				$imageFileType = pathinfo($_FILES['FILEGAMBAR']['name'], PATHINFO_EXTENSION);
				$target_file = $target_dir . str_replace('/', '.', $kode) . '.' . $imageFileType;
				$gambar = str_replace('/', '.', $kode) . '.' . $imageFileType;
				// Check if image file is a actual image or fake image

				$check = getimagesize($_FILES["FILEGAMBAR"]["tmp_name"]);
				if ($check !== false) {
					$uploadOk = 1;
				} else {
					die(json_encode(array('errorMsg' => 'File yang Diupload bukan gambar')));
					$uploadOk = 0;
				}

				if ($_POST['GAMBAR'] != '') {
					unlink($target_dir.$_POST['GAMBAR']);
				}
				// Check if file already exists
				if (file_exists($target_file)) {
					$uploadOk = 0;
				}
				// Check file size
				if ($_FILES["FILEGAMBAR"]["size"] > 500000) {
					die(json_encode(array('errorMsg' => 'Sorry, your file is too large.')));

					$uploadOk = 0;
				}
				// Allow certain file formats
				if ( ! in_array(strtolower($imageFileType), array('jpg', 'png', 'jpeg', 'gif')) ) {
					die(json_encode(array('errorMsg' => 'Sorry, only JPG, JPEG, PNG & GIF files are allowed.')));

					$uploadOk = 0;
				}
				// Check if $uploadOk is set to 0 by an error
				if ($uploadOk == 0) {
					die(json_encode(array('errorMsg' => 'Sorry, your file was not uploaded.')));
				// if everything is ok, try to upload file
				} else {
					if (move_uploaded_file($_FILES["FILEGAMBAR"]["tmp_name"], $target_file)) {
						//echo "The file ". basename($_FILES["GAMBAR"]["name"]). " has been uploaded.";
					} else {
						die(json_encode(array('errorMsg' => 'Sorry, there was an error uploading your file.')));
					}
				}
			}

			$tr = $db->start_trans();

			// insert informasi umum
			$data_values = array (
				$kode, $nama, $gambar, $_POST['URUTAN'], $_SESSION['user'],
				date("Y-m-d"), $status
			);
			$query = $db->insert('mgambarlensa', $data_values, $tr);
			if (!$query) {
				$db->rollback($tr);
				die(json_encode(array('errorMsg' => 'Simpan Data Gagal')));
			}

			$db->commit($tr);

			// panggil fungsi untuk log history
			log_history(
				$kode,
				'MASTER USER',
				$act,
				array(
					array(
						'nama' => 'header',
						'tabel' => 'mgambarlensa',
						'kode' => 'kodelensa'
					),
				),
				$_SESSION['user']
			);

			echo json_encode(array('success' => true));
		} else if ($act=='delete') {
			$kode = $_POST['id'];

			$p = $db->prepare('select first 1 skip 0 kodelensa as kode
							   from tpr where kodelensa=?');
			$q = $db->execute($p, $kode);
			$r = $db->fetch($q);

			if ($r->KODE<>'') { die(json_encode(array('errorMsg'=>'Data Lensa Tidak Dapat Dihapus, Data Sudah Digunakan Pada Transaksi'))); }

			$q = $db->delete('mgambarlensa', array('kodelensa' => $kode));

			if ($q) {
				// panggil fungsi untuk log history
				log_history(
					$kode,
					'MASTER GAMBAR LENSA',
					$act,
					array(
						array(
							'nama' => 'header',
							'tabel' => 'mgambarlensa',
							'kode' => 'kodelensa'
						),
					),
					$_SESSION['user']
				);

				echo json_encode(array('success'=>true));
			} else {
				echo json_encode(array('errorMsg'=>'Terdapat Data Error, Data Tidak Dapat Dihapus'));
			}
		}
	break;
	//========================= END MASTER DATA GAMBAR LENSA ===========================

	//========================= MASTER DATA ALASAN ===========================
	case 'alasan' :
		if ($act=='insert' || $act=='edit') {
			$jenistransaksi = $_POST['JENISTRANSAKSI'];
			$alasan	        = $_POST['ALASAN'];
			$status	        = isset($_POST['STATUS']) ? $_POST['STATUS'] : 0;

			$tr = $db->start_trans();

			$data_values = array (
				$jenistransaksi, $alasan, $_SESSION['user'], date("Y-m-d"), $status
			);
			$query = $db->insert('malasan', $data_values, $tr);
			if (!$query) {
				$db->rollback($tr);
				die(json_encode(array('errorMsg' => 'Simpan Data Gagal')));
			}

			// commit transaction
			$db->commit($tr);

			echo json_encode(array('success' => true));
		} else if ($act=='delete') {
			$jenistransaksi = $_POST['jenistransaksi'];
			$alasan         = $_POST['alasan'];

			$p = $db->prepare('select first 1 skip 0 alasan from treturjual where alasan=?');
			$q = $db->execute($p, $alasan);
			$r = $db->fetch($q);

			if ($r->KODE<>'') { die(json_encode(array('errorMsg'=>'Data Alasan Tidak Dapat Dihapus, Sudah Digunakan Pada Transaksi'))); }

			$q = $db->delete('malasan', array('jenistransaksi' => $jenistransaksi,'alasan' => $alasan));

			if ($q) {
				echo json_encode(array('success'=>true));
			} else {
				echo json_encode(array('errorMsg'=>'Terdapat Data Error, Data Tidak Dapat Dihapus'));
			}
		}
	break;
	//========================= END MASTER DATA ALASAN ===========================

	//========================= MASTER DATA CURRENCY ===========================
	case 'currency' :
		if ($act=='insert' || $act=='edit') {
			$kode	= $_POST['KODECURRENCY'];
			$nama	= $_POST['NAMACURRENCY'];
			$tanda	= isset($_POST['TANDA']) ? 1 : 0;
			$status	= isset($_POST['STATUS']) ? $_POST['STATUS'] : 0;

			$a_detail = json_decode($_POST['data_detail']);

			if ($kode=='') die(json_encode(array('errorMsg' => 'Kode Mata Uang Tidak Boleh Kosong')));
			if ($nama=='') die(json_encode(array('errorMsg' => 'Keterangan Currency Tidak Boleh Kosong')));

			//CEK APAKAH KODE DAN NAMA SUDAH DIGUNAKAN
			if ($act=='insert'){
				$p = $db->prepare('select kodecurrency, namacurrency from mcurrency where kodecurrency=?');
				$q = $db->execute($p, $kode);
				$r = $db->fetch($q);
				if ($r->KODECURRENCY!='') {
					die(json_encode(array('errorMsg' => 'ID Mata Uang Sudah Digunakan Oleh Mata Uang ('.$r->KODECURRENCY.') '.$r->NAMACURRENCY.', Kode Tidak Dapat Digunakan')));
				}
				$p = $db->prepare('select kodecurrency, namacurrency from mcurrency where namacurrency=?');
				$q = $db->execute($p, $nama);
				$r = $db->fetch($q);
				if ($r->NAMACURRENCY!='') {
					die(json_encode(array('errorMsg' => 'Deskripsi Mata Uang Sudah Digunakan Oleh Mata Uang ('.$r->KODECURRENCY.') '.$r->NAMACURRENCY.', Keterangan Tidak Dapat Digunakan')));
				}
				$status = 1;// $tanda=1;
			} else if ($act=='edit') {
				$p = $db->prepare('select kodecurrency, namacurrency from mcurrency where kodecurrency<>? and namacurrency=?');
				$q = $db->execute($p, array($kode, $nama));
				$r = $db->fetch($q);
				if ($r->NAMACURRENCY!='') {
					die(json_encode(array('errorMsg' => 'Deskripsi Mata Uang Sudah Digunakan Oleh Mata Uang ('.$r->KODECURRENCY.') '.$r->NAMACURRENCY.', Keterangan Tidak Dapat Digunakan')));
				}
			}
			if ($tanda==1){
				$p = $db->prepare('select kodecurrency, namacurrency,tanda from mcurrency where kodecurrency<>? and tanda=1');
				$q = $db->execute($p, $kode);
				$r = $db->fetch($q);
				if ($r->KODECURRENCY!='') {
					die(json_encode(array('errorMsg' => 'Mata Uang Utama Sudah Digunakan Oleh Mata Uang ('.$r->KODECURRENCY.') '.$r->NAMACURRENCY.', Mata Uang Utama Tidak Dapat Digunakan')));
				}
			}

			$tr = $db->start_trans();

			$data_values = array (
				$kode, $_POST['NAMACURRENCY'], $_POST['SIMBOL'], $tanda, $_SESSION['user'],
				date("Y-m-d"), $status
			);
			$query = $db->insert('mcurrency', $data_values, $tr);
			if (!$query) {
				$db->rollback($tr);
				die(json_encode(array('errorMsg' => 'Simpan Data Gagal')));
			}

			$sql = $db->insert('mcurrencykurs', 3, $tr, true);
			$pr = $db->prepare($sql, $tr);
			if (count($a_detail)>0) {
				foreach ($a_detail as $item) {
					$data_values = array($kode, $item->kurs, $item->tglaktif);

					$exe = $db->execute($pr, $data_values);

					if (!$exe) { $db->rollback($tr); die(json_encode(array('errorMsg' => 'Simpan Data Gagal<br>Terdapat Data Error'))); }
				}
			}
			// commit transaction
			$db->commit($tr);

			// panggil fungsi untuk log history
			log_history(
				$kode,
				'MASTER CURRENCY',
				$act,
				array(
					array(
						'nama' => 'header',
						'tabel' => 'mcurrency',
						'kode' => 'kodecurrency'
					),
					array(
						'nama' => 'detail',
						'tabel' => 'mcurrencykurs',
						'kode' => 'kodecurrency'
					),
				),
				$_SESSION['user']
			);

			echo json_encode(array('success' => true));
		} else if ($act=='delete') {
			$kode = $_POST['id'];

			$sql = 'select * from (
						select first 1 skip 0 kodecurrency as kode from tpodtl where kodecurrency=?
						union all
						select first 1 skip 0 kodecurrency as kode from tbelidtl where kodecurrency=?
						union all
						select first 1 skip 0 kodecurrency as kode from torderreturbelidtl where kodecurrency=?
						union all
						select first 1 skip 0 kodecurrency as kode from treturbelidtl where kodecurrency=?
						union all
						select first 1 skip 0 kodecurrency as kode from tsodtl where kodecurrency=?
						union all
						select first 1 skip 0 kodecurrency as kode from tjualdtl where kodecurrency=?
						union all
						select first 1 skip 0 kodecurrency as kode from treturjualdtl where kodecurrency=?
						union all
						select first 1 skip 0 kodecurrency as kode from torderreturjualdtl where kodecurrency=?
					)';
			$p = $db->prepare($sql);
			$q = $db->execute($p, array($kode, $kode, $kode, $kode, $kode, $kode, $kode, $kode));
			$r = $db->fetch($q);

			if ($r->KODE<>'') { die(json_encode(array('errorMsg'=>'Data Mata Uang Tidak Dapat Dihapus, Sudah Digunakan Pada Transaksi'))); }

			$q = $db->delete('mcurrency', array('kodecurrency' => $kode));

			if ($q) {
				// panggil fungsi untuk log history
				log_history(
					$kode,
					'MASTER CURRENCY',
					$act,
					array(
						array(
							'nama' => 'header',
							'tabel' => 'mcurrency',
							'kode' => 'kodecurrency'
						),
					),
					$_SESSION['user']
				);

				echo json_encode(array('success'=>true));
			} else {
				echo json_encode(array('errorMsg'=>'Terdapat Data Error, Data Tidak Dapat Dihapus'));
			}
		} else if ($act=='add_detail') {
			echo json_encode($_POST);
		} else if ($act=='view_detail') {
			$kode = $_POST['kode'];

			$p = $db->prepare('select * from mcurrencykurs where kodecurrency=? order by tglaktif desc');
			$q = $db->execute($p, $kode);

			$temp_detail = array();
			while ($rs = $db->fetch($q)) {
				$temp_detail[] = array(
					'tglaktif' => $rs->TGLAKTIF,
					'kurs' => $rs->KURS,
				);
			}

			echo json_encode(array('success' => true, 'detail' => $temp_detail));
		}
	break;
	//========================= END MASTER DATA CURRENCY ===========================

	//========================= MASTER DATA LOGIN USER ========================
	case 'user' :
		if ($act=='view') {
			$userid  = $_POST['userid'];

			$pr  = $db->prepare('select * from muser where userid = ?');
			$exe = $db->execute($pr, $userid);
			$rs  = $db->fetch($exe);

			//$rs->PASS = encrypt_data($rs->PASS);
			$rs->RE_PASS = $rs->PASS;

			$pr  = $db->prepare('select * from muserlokasi where userid = ?');
			$exe = $db->execute($pr, $userid);

			$data_lokasi = array();
			while($r = $db->fetch($exe)) {
				$data_lokasi[] = $r;
			}

			echo json_encode(array('success' => true, 'data' => $rs, 'data_lokasi' => $data_lokasi));
		} else if ($act=='insert' or $act=='edit') {
			$userid 	= $act=='insert' ? $_POST['USERID'] : $_POST['USERID_CG'];
			$user 		= $_POST['USERNAME'];
			$email 		= $_POST['EMAIL'];

			$a_kode_cek = json_decode($_POST['data_detail']);
			$a_lokasi   = json_decode($_POST['data_lokasi']);

			$q = $db->select('muser', array('pass'), array('userid'=>$userid));
			$r = $db->fetch($q);

			if ($r->PASS === $_POST['PASS']) {
				$pass  		= $_POST['PASS'];
				$re_pass  	= $_POST['RE_PASS'];
			} else {
				$pass  		= encrypt_data($_POST['PASS']);
				$re_pass  	= encrypt_data($_POST['RE_PASS']);
			}

			if ($userid=='') die(json_encode(array('errorMsg' => 'User Tidak Boleh Kosong')));
			/*if ($email=='') die(json_encode(array('errorMsg' => 'E-Mail Tidak Boleh Kosong')));*/
			if ($pass=='') die(json_encode(array('errorMsg' => 'Password Tidak Boleh Kosong')));

			$a_temp = array();
			foreach ($a_kode_cek as $item) {
				foreach ($item->children as $row) {
					$a_temp[] = $row;
				}
			}

			$q = $db->query('select kodemenu, namamenu from mmenu order by kodemenu');
			$a_menu = array();
			while($r = $db->fetch($q)) {
				$a_menu[] = array(
					'kode' => $r->KODEMENU,
					'menu' => $r->NAMAMENU,
				);
			}
			$a_menu = json_decode(json_encode($a_menu));

			$tg = isset($_POST['TAMPILGRANDTOTAL']) ? $_POST['TAMPILGRANDTOTAL'] : 0;
			$pu = isset($_POST['PRINTULANG']) ? $_POST['PRINTULANG'] : 0;
			$otorisasi = isset($_POST['OTORISASI']) ? $_POST['OTORISASI'] : 0;

			if ($act=='insert') {
				$pr  = $db->prepare("select * from muser where userid = ?");
				$exe = $db->execute($pr, $userid);
				$rs  = $db->fetch($exe);

				if ($rs->USERID!='') {
					die(json_encode(array('errorMsg'=>'Please Use Another User ID')));
				}
			}

			if ($userid=='') die(json_encode(array('errorMsg'=>'ID Pengguna Tidak Boleh Kosong')));
			if ($user=='') die(json_encode(array('errorMsg'=>'Nama Pengguna Tidak Boleh Kosong')));
			if ($pass=='' or $re_pass=='') die(json_encode(array('errorMsg'=>'Kata Kunci Tidak Boleh Kosong')));
			if ($pass!=$re_pass) die(json_encode(array('errorMsg'=>'Tolong Cek Kembali Kata Kunci Anda')));

			$tr = $db->start_trans();

			$data_values = array (
				$userid, $user, $pass, $otorisasi, $tg,
				$pu, 1, 1
			);
			$exe = $db->insert('muser', $data_values, $tr);
			if (!$exe) { $db->rollback($tr); die(json_encode(array('errorMsg'=>'Simpan Data Gagal 1'))); }

			$sql = $db->insert('muserakses', 6, $tr, true);
			$pr = $db->prepare($sql, $tr);
			foreach ($a_menu as $i) {
				$hakakses = 0;
				$tambah = 0;
				$ubah = 0;
				$hapus = 0;

				foreach ($a_temp as $row) {
					if ($i->kode==$row->id) {
						$hakakses = $row->hakakses;
						if ($hakakses==1) {
							$tambah = $row->tambah;
							$ubah = $row->ubah;
							$hapus = $row->hapus;
						}
						break;
					}
				}

				$hakakses = $i->menu=='-' ? 1 : $hakakses;
				$data_values = array (
					$userid, $i->kode, $hakakses, $tambah, $ubah,
					$hapus
				);
				$exe = $db->execute($pr, $data_values);
				if (!$exe) { $db->rollback($tr); die(json_encode(array('errorMsg'=>'Simpan Data Gagal 2'))); }
			}

			if (count($a_lokasi) > 0) {
				$pr = $db->prepare('insert into muserlokasi values (?, ?)', $tr);

				foreach ($a_lokasi as $item) {
					$data_values = array (
						$userid, $item->KODELOKASI
					);
					$exe = $db->execute($pr, $data_values);
					if (!$exe) { $db->rollback($tr); die(json_encode(array('errorMsg'=>'Simpan Data Gagal 3'))); }
				}
			}

			$db->commit($tr);

			// panggil fungsi untuk log history
			log_history(
				$userid,
				'MASTER USER',
				$act,
				array(
					array(
						'nama' => 'header',
						'tabel' => 'muser',
						'kode' => 'userid'
					),
					array(
						'nama' => 'userakses',
						'tabel' => 'muserakses',
						'kode' => 'userid'
					),
				),
				$_SESSION['user']
			);

			echo json_encode(array('success'=>true, 'userid' => $userid));
		} else if ($act=='delete') {
			$db->delete('muser', array('userid' => $_POST['userid']));

			// panggil fungsi untuk log history
			log_history(
				$_POST['userid'],
				'MASTER USER',
				$act,
				array(
					array(
						'nama' => 'header',
						'tabel' => 'muser',
						'kode' => 'userid'
					),
					array(
						'nama' => 'userakses',
						'tabel' => 'muserakses',
						'kode' => 'userid'
					),
				),
				$_SESSION['user']
			);

			echo json_encode(array('success'=>true, 'userid' => $_POST['userid']));
		}
	break;
	//========================= END MASTER DATA USER ===========================

	//========================= MASTER DATA KODE PERKIRAAN ===========================
	case 'kode_perkiraan' :
		if ($act=='insert' || $act=='edit'){
			$kode  		 = $_POST['KODEPERKIRAAN'];
			$nama     	 = $_POST['NAMAPERKIRAAN'];
			$kelompok 	 = $_POST['KELOMPOK'];
			$saldo   	 = $_POST['SALDO'];
			$induk   	 = $_POST['INDUK'];
			$tipe   	 = $_POST['TIPE'];
			$kasbank   	 = $_POST['KASBANK'];
			$kodekasbank = $kasbank!=0 ? $_POST['KODEKASBANK'] : '';
			$status		 = isset($_POST['STATUS']) ? $_POST['STATUS'] : 0;
			$a_detail = json_decode($_POST['data_detail']);

			if ($kode=='') die(json_encode(array('errorMsg' => 'Informasi Mata Uang Tidak Boleh Kosong')));
			if ($nama=='') die(json_encode(array('errorMsg' => 'Nama Akun Tidak Boleh Kosong')));

			//CEK APAKAH KODE SUDAH DIGUNAKAN
			if ($act=='insert'){
				$p = $db->prepare('select kodeperkiraan as kode, namaperkiraan as nama from mperkiraan where kodeperkiraan=?');
				$q = $db->execute($p, $kode);
				$r = $db->fetch($q);
				if ($r->KODE!='') {
					die(json_encode(array('errorMsg' => 'Akun Sudah Digunakan Oleh CoA ('.$r->KODECURRENCY.') '.$r->NAMA.', Akun Tidak Dapat Digunakan')));
				}

				$status = 1;
			}

			$tr = $db->start_trans();

			$data_values = array (
				$kode, $nama, $kelompok, $tipe, $induk,
				$saldo, $kasbank, $kodekasbank, $_POST['KODECURRENCY'], $_SESSION['user'],
				date("Y-m-d"), $status
			);
			$exe = $db->insert('mperkiraan', $data_values, $tr);
			if (!$exe) { $db->rollback($tr); die(json_encode(array('errorMsg'=>'Simpan Data Gagal'))); }

			// insert lokasi
			if (count($a_detail) > 0) {
				$pr = $db->prepare('insert into mperkiraanlokasi values(?, ?)', $tr);

				foreach ($a_detail as $item) {
					$exe = $db->execute($pr, array($kode, $item->KODELOKASI));
					if (!$exe) { $db->rollback($tr); die(json_encode(array('errorMsg'=>'Simpan Detail Lokasi Gagal'))); }
				}
			}

			$db->commit($tr);

			// panggil fungsi untuk log history
			log_history(
				$kode,
				'MASTER CHART OF ACCOUNT',
				$act,
				array(
					array(
						'nama' => 'header',
						'tabel' => 'mperkiraan',
						'kode' => 'kodeperkiraan'
					),
					array(
						'nama' => 'detail lokasi',
						'tabel' => 'mperkiraanlokasi',
						'kode' => 'kodeperkiraan'
					),
				),
				$_SESSION['user']
			);

			echo json_encode(array('success'=>true));
		} else if ($act=='delete') {
			$kode = $_POST['id'];

			$p = $db->prepare('select first 1 skip 0 kodeperkiraan as kode from valueperkiraan where kodeperkiraan=?');
			$q = $db->execute($p, $kode);
			$r = $db->fetch($q);
			if ($r->KODE!='') {
				die(json_encode(array('errorMsg' => 'Data Perkiraan Tidak Bisa Dihapus, Sudah Digunakan Pada Transaksi')));
			}

			$db->delete('mperkiraan', array('kodeperkiraan' => $kode));

			// panggil fungsi untuk log history
			log_history(
				$kode,
				'MASTER CHART OF ACCOUNT',
				$act,
				array(
					array(
						'nama' => 'header',
						'tabel' => 'mperkiraan',
						'kode' => 'kodeperkiraan'
					),
				),
				$_SESSION['user']
			);

			echo json_encode(array('success'=>true));
		}
	break;
	//========================= END MASTER DATA KODE PERKIRAAN ===========================


	//========================= MASTER DATA PRODUKSI ===========================
	case 'produksi' :
		if ($act=='insert' || $act=='edit') {
			$kode 	  = $_POST['KODEMPRODUKSI'];
			$kode_brg = $_POST['KODEBARANGPRODUKSI'];
			$status   = isset($_POST['STATUS']) ? $_POST['STATUS'] : 0;

			$a_detail = json_decode($_POST['data_detail_barang']);

			if (count($a_detail)<1) die(json_encode(array('errorMsg' => 'Anda Belum Menambahkan Detail Transaksi')));

			cek_valid_data('mbarang', 'kodebarang', $kode_brg, 'Item');

			//CEK APAKAH KODE SUDAH DIGUNAKAN
			if ($act=='insert'){
				$p = $db->prepare('select kodemproduksi as kode, keterangan as nama from mproduksi where kodemproduksi=?');
				$q = $db->execute($p, $kode);
				$r = $db->fetch($q);
				if ($r->KODE!='') {
					die(json_encode(array('errorMsg' => 'Bill of Material Has Sudah Digunakan Oleh ('.$r->KODE.') '.$r->NAMA.', ID Tidak Dapat Digunakan')));
				}

				$status = 1;
			}
			$status = 1;

			$tr = $db->start_trans();

			$data_values = array (
				$kode, $kode_brg, $_POST['NAMABARANGPRODUKSI'], $_POST['KETERANGAN'], $_POST['SATUAN'], $_POST['JML'], $_POST['HPP'],
				($_POST['CEKTOLERANSI']=='' ? 0 : 1), $_POST['TOLERANSIPENERIMAAN'], $_POST['REMARK'], $_SESSION['user'], date("Y-m-d"),
				$status
			);
			$exe = $db->insert('mproduksi', $data_values, $tr);
			if (!$exe) { $db->rollback($tr); die(json_encode(array('errorMsg'=>'Simpan Data Gagal'))); }

			$i = 0;
			$sql = $db->insert('mproduksidtl', 10, $tr, true);
			$pr = $db->prepare($sql, $tr);
			foreach ($a_detail as $item) {
				$data_values = array (
					$kode, $item->kodebarang, $i, $item->namabarang, $item->jml,
					$item->satuan, '', 0, $item->harga, $item->subtotal
				);
				$exe = $db->execute($pr, $data_values);
				if (!$exe) { $db->rollback($tr); die(json_encode(array('errorMsg' => 'Simpan Data Gagal <br>Please Check Detail Data Material'))); }
				$i++;
			}

			$a_detail = json_decode($_POST['data_detail_biaya']);

			$i = 0;
			$sql = $db->insert('mproduksidtlakun', 5, $tr, true);
			$pr = $db->prepare($sql, $tr);
			foreach ($a_detail as $item) {
				$data_values = array(
					$kode, $item->kodeperkiraan, $i, $item->keterangan, $item->amount
				);
				$exe = $db->execute($pr, $data_values);
				if (!$exe) { $db->rollback($tr); die(json_encode(array('errorMsg' => 'Simpan Data Gagal <br>Please Check Detail Data Cost'))); }
				$i++;
			}

			$db->commit($tr);

			// panggil fungsi untuk log history
			log_history(
				$kode,
				'MASTER PRODUCTION',
				$act,
				array(
					array(
						'nama' => 'header',
						'tabel' => 'mproduksi',
						'kode' => 'kodemproduksi'
					),
					array(
						'nama' => 'detail_barang',
						'tabel' => 'mproduksidtl',
						'kode' => 'kodemproduksi'
					),
					array(
						'nama' => 'detail_akun',
						'tabel' => 'mproduksidtlakun',
						'kode' => 'kodemproduksi'
					),
				),
				$_SESSION['user']
			);

			echo json_encode(array('success'=>true));
		} else if ($act=='delete') {
			$kode = $_POST['id'];

			/*$p = $db->prepare('select first 1 skip 0 kodeperkiraan as kode from valueperkiraan where kodeperkiraan='$kode');
			$r = $db->fetch($q);
			if ($r->KODE!='') {
				die(json_encode(array('errorMsg' => 'Data Kode Perkiraan Tidak Bisa Dihapus, Karena Sudah Digunakan Dalam Transaksi')));
			}*/

			$db->delete('mproduksi', array('kodemproduksi' => $kode));

			// panggil fungsi untuk log history
			log_history(
				$kode,
				'MASTER PRODUCTION',
				$act,
				array(
					array(
						'nama' => 'header',
						'tabel' => 'mproduksi',
						'kode' => 'kodemproduksi'
					),
					array(
						'nama' => 'detail_barang',
						'tabel' => 'mproduksidtl',
						'kode' => 'kodemproduksi'
					),
					array(
						'nama' => 'detail_akun',
						'tabel' => 'mproduksidtlakun',
						'kode' => 'kodemproduksi'
					),
				),
				$_SESSION['user']
			);
			echo json_encode(array('success'=>true));
		} else if ($act=='load_data') {
			$kodetrans = $_POST['kodetrans'];

			$rows_barang = array();
			$sql   = "select b.*, c.namabarang
					  from mproduksi a inner join mproduksidtl b on a.kodemproduksi=b.kodemproduksi
					  inner join mbarang c on b.kodebarang=c.kodebarang
					  where a.kodemproduksi = '$kodetrans'
					  order by urutan";
			$query = $db->query($sql);
			while ($rs = $db->fetch($query)) {
				$rows_barang[] = array(
					'kodebarang' => $rs->KODEBARANG,
					'namabarang' => $rs->NAMABARANG,
					'satuan' => $rs->SATUAN,
					'jml' => $rs->JML,
					'harga' => $rs->HARGA,
					'subtotal' => $rs->SUBTOTAL,
				);
			}

			$rows_biaya = array();
			$sql   = "select b.*, c.namaperkiraan
					  from mproduksi a inner join mproduksidtlakun b on a.kodemproduksi=b.kodemproduksi
					  inner join mperkiraan c on b.kodeperkiraan=c.kodeperkiraan
					  where a.kodemproduksi = '$kodetrans'
					  order by urutan";
			$query = $db->query($sql);
			while ($rs = $db->fetch($query)) {
				$rows_biaya[] = array(
					'kodeperkiraan' => $rs->KODEPERKIRAAN,
					'namaperkiraan' => $rs->NAMAPERKIRAAN,
					'keterangan' => $rs->KETERANGAN,
					'amount' => $rs->AMOUNT,
				);
			}

			echo json_encode(array(
				'success' => true,
				'detail_barang' => $rows_barang,
				'detail_biaya' => $rows_biaya,
			));
		} else if ($act=='get_hpp') {
			$a_detail = json_decode($_POST['data_detail']);

			if (count($a_detail)<1) die(json_encode(array('errorMsg' => 'Anda Belum Menambahkan Detail Transaksi')));

			foreach ($a_detail as $item) {
				$item->harga = get_harga_beli_terakhir ($item->kodebarang, date('Y-m-d'));
				$item->subtotal = $item->harga * $item->jml;
			}

			echo json_encode(array('success' => true, 'detail' => $a_detail));
		}
	break;
	//========================= END MASTER PRODUKSI ===========================

	//========================= MASTER PENGATURAN JURNAL LINK ===========================
	case 'jurnal_link' :
		if ($act=='view'){
			$sql = "select a.kodeperkiraan, a.jenis, a.urutan, b.namaperkiraan
					from settingjurnallink a
					left join mperkiraan b on a.kodeperkiraan=b.kodeperkiraan
					where a.kodelokasi = '".$_SESSION['KODELOKASI']."'
					order by a.kodeperkiraan, a.urutan, a.saldo";
			$query = $db->query($sql);
			while ($rs = $db->fetch($query)) {
				$data[] = $rs;
			}

			echo json_encode(array(
				'status' => 'sukses',
				'data' => $data,
			));
		} else if ($act=='insert' || $act=='edit') {
			$q = $db->select('settingjurnallink', array('jenis', 'urutan'), array('kodelokasi'=>$_SESSION['KODELOKASI']));
			$pr = $db->prepare("UPDATE SETTINGJURNALLINK SET KODEPERKIRAAN = ? WHERE JENIS = ? AND URUTAN = ? AND KODELOKASI = ?");

			while ($rs = $db->fetch($q)) {
				$data_values = array(
					$_POST['txt_'.$rs->JENIS],
					$rs->JENIS,
					$rs->URUTAN,
					$_SESSION['KODELOKASI']
				);
				$db->execute($pr, $data_values);
			}


			echo json_encode(array(
				'status' => 'sukses',
			));
		}
	break;
	//========================= MASTER PENGATURAN JURNAL LINK ===========================

	//========================= PENGATURAN LAIN-LAIN ===========================
	case 'settinglain' :
		if ($act=='view'){
			$query = $db->query("select * from settinglain");
			$rs    = $db->fetch($query);
			echo json_encode(array('success' => true, 'data' => $rs));
		} else if ($act=='simpan') {
			$nama			 = $_POST['NAMA'];
			$alamat			 = $_POST['ALAMAT'];
			$kota			 = $_POST['KOTA'];
			$kodepos		 = $_POST['KODEPOS'];
			$telp			 = $_POST['TELP'];
			$fax			 = $_POST['FAX'];
			$ppn			 = $_POST['PPN']=='' ? 0 : $_POST['PPN'];
			$npwp			 = $_POST['NPWP'];
			$ttd			 = $_POST['NAMATANDATANGAN'];
			$lokasi_backup   = $_POST['LOKASIBACKUP'];
			$backup_otomatis = $_POST['BACKUPOTOMATIS'];
			$interval_backup = $_POST['INTERVALBACKUP'];
			$diskonmember    = $_POST['DISKONMEMBER'];
			$diskonultah     = $_POST['DISKONULTAH'];

			$tr = $db->start_trans();

			$data_values = array (
				$nama, $alamat, $kota, $kodepos, $telp,
				$fax, $ppn, $npwp, $ttd, $lokasi_backup, 
				$backup_otomatis, $interval_backup, 1, $diskonmember, $diskonultah, 
				$_POST['RATEIO'], $_POST['RATESE']
			);
			$exe = $db->insert('settinglain', $data_values, $tr);

			if (!$exe) { $db->rollback($tr); die(json_encode(array('errorMsg' => 'Simpan Data Gagal'))); }

		    $db->commit($tr);

			echo json_encode(array('success' => true));
		}
	break;
	//========================= END PENGATURAN LAIN-LAIN ===========================

	//========================= MASTER DATA TARIF AKTIVA ===========================
	case 'tarifaktiva' :
		if ($act=='insert' || $act=='edit') {
			$kode	= $_POST['KODETARIF'];
			$nama	= $_POST['NAMATARIF'];
			$status	= isset($_POST['STATUS']) ? $_POST['STATUS'] : 0;

			if ($_POST['KODETARIF'] == '') die(json_encode(array('errorMsg' => 'Syarat Bayar Tidak Boleh Kosong')));
			if ($_POST['NAMATARIF'] == '') die(json_encode(array('errorMsg' => 'Syarat Bayar Description Tidak Boleh Kosong')));

			if ($kode=='') {
				//$temp_kode = 'B'.substr($nama, 0, 1);
				//$kode = $temp_kode.get_max_urutan('mbarang', 'kodebarang', $temp_kode, 3);
			}

			//CEK APAKAH KODE DAN NAMA SUDAH DIGUNAKAN
			if ($act=='insert'){
				$p = $db->prepare('select kodetarif, namatarif from mtarifaktiva where kodetarif=?');
				$q = $db->execute($p, $kode);
				$r = $db->fetch($q);
				if ($r->KODETARIF!='') {
					die(json_encode(array('errorMsg' => 'Rates Assets ID Sudah Digunakan Oleh Rates Assets ('.$r->KODETARIF.') '.$r->NAMATARIF.', ID Tidak Dapat Digunakan')));
				}
				$p = $db->prepare('select kodetarif, namatarif from mtarifaktiva where namatarif=?');
				$q = $db->execute($p, $nama);
				$r = $db->fetch($q);
				if ($r->NAMATARIF!='') {
					die(json_encode(array('errorMsg' => 'Rates Assets Description Sudah Digunakan Oleh Rates Assets ('.$r->KODETARIF.') '.$r->NAMATARIF.', Description Tidak Dapat Digunakan')));
				}
				$status = 1;
			} else if ($act=='edit') {
				$p = $db->prepare('select kodetarif, namatarif from mtarifaktiva where kodetarif<>? and namatarif=?');
				$q = $db->execute($p, $kode, $nama);
				$r = $db->fetch($q);
				if ($r->NAMATARIF!='') {
					die(json_encode(array('errorMsg' => 'Rates Assets Description Sudah Digunakan Oleh Rates Assets ('.$r->KODETARIF.') '.$r->NAMATARIF.', Description Tidak Dapat Digunakan')));
				}
			}

			$tr = $db->start_trans();

			$data_values = array (
				$kode, $_POST['NAMATARIF'], $_POST['PERSENTASE'], $_POST['REMARK'], $_SESSION['user'],
				date("Y-m-d"), $status
			);
			$query = $db->insert('mtarifaktiva', $data_values, $tr);
			if (!$query) {
				$db->rollback($tr);
				die(json_encode(array('errorMsg' => 'Simpan Data Gagal')));
			}

			// commit transaction
			$db->commit($tr);

			// panggil fungsi untuk log history
			log_history(
				$kode,
				'MASTER RATES ASSETS',
				$act,
				array(
					array(
						'nama'  => 'header',
						'tabel' => 'mtarifaktiva',
						'kode'  => 'kodetarif'
					),
				),
				$_SESSION['user']
			);

			echo json_encode(array('success' => true));
		} else if ($act=='delete') {
			$kode = $_POST['id'];

			$p = $db->prepare('select first 1 skip 0 kodetarif as kode from tbeliaktivadtl where kodetarif=?');
			$q = $db->execute($p, $kode);
			$r = $db->fetch($q);

			if ($r->KODE<>'') { die(json_encode(array('errorMsg'=>'Data Aset Rates Assets Tidak Dapat Dihapus, Sudah Digunakan Pada Transaksi'))); }

			$q = $db->delete('mtarifaktiva', array('kodetarif' => $kode), false);

			if ($q) {
				// panggil fungsi untuk log history
				log_history(
					$kode,
					'MASTER RATES ASSETS',
					$act,
					array(
						array(
							'nama' => 'header',
							'tabel' => 'mtarifaktiva',
							'kode' => 'kodetarif'
						),
					),
					$_SESSION['user']
				);

				echo json_encode(array('success'=>true));
			} else {
				echo json_encode(array('errorMsg'=>'Terdapat Data Error, Data Tidak Dapat Dihapus'));
			}
		}
	break;
	//========================= END MASTER DATA TARIF AKTIVA ===========================
	//========================= MASTER DATA DEPARTEMEN MENU ===========================
	case 'departemen_menu' :
		if ($act=='insert' || $act=='edit'){
			$kode   = $_POST['KODEDEPARTEMENMENU'];
			$nama   = $_POST['NAMADEPARTEMENMENU'];
			$status = $_POST['STATUS'];

			if ($kode=='') {
				$temp_kode = 'K';
				$kode = $temp_kode.get_max_urutan('mdepartemenmenu', 'kodedepartemenmenu', $temp_kode, 3);
			}

			if ($kode == '') die(json_encode(array('errorMsg' => 'ID Kategori Buku Tidak Boleh Kosong')));
			if ($nama == '') die(json_encode(array('errorMsg' => 'Deskripsi Kategori Buku Tidak Boleh Kosong')));

			//CEK APAKAH KODE DAN NAMA SUDAH DIGUNAKAN
			if ($act=='insert') {
				$p = $db->prepare('select kodedepartemenmenu, namadepartemenmenu from mdepartemenmenu where kodedepartemenmenu=?');
				$q = $db->execute($p, $kode);
				$r = $db->fetch($q);
				if ($r->KODEDEPARTEMENMENU!='') {
					die(json_encode(array('errorMsg' => 'Kode Departemen Menu Sudah Digunakan Oleh Kategori Buku ('.$r->KODEDEPARTEMENMENU.') '.$r->NAMADEPARTEMENMENU.', Kode Tidak Dapat Digunakan')));
				}

				$p = $db->prepare('select kodedepartemenmenu, namadepartemenmenu from mdepartemenmenu where namadepartemenmenu=?');
				$q = $db->execute($p, $nama);
				$r = $db->fetch($q);
				if ($r->NAMADEPARTEMENMENU!='') {
					die(json_encode(array('errorMsg' => 'Nama Departemen Menu Sudah Digunakan Oleh Kategori Buku ('.$r->KODEDEPARTEMENMENU.') '.$r->NAMADEPARTEMENMENU.', Nama Tidak Dapat Digunakan')));
				}
				$status = 1;
			} else if ($act=='edit') {
				$p = $db->prepare('select kodedepartemenmenu, namadepartemenmenu from mdepartemenmenu where kodedepartemenmenu<>? and namadepartemenmenu=?');
				$q = $db->execute($p, array($kode, $nama));
				$r = $db->fetch($q);
				if ($r->NAMAKATEGORIBARANG!='') {
					die(json_encode(array('errorMsg' => 'Deskripsi Kategori Buku Sudah Digunakan Oleh Kategori Buku ('.$r->KODEDEPARTEMENMENU.') '.$r->NAMADEPARTEMENMENU.', Deskripsi Tidak Dapat Digunakan')));
				}
			}

			// start transaction
			$tr = $db->start_trans();

			$data_values = array (
				$kode, $nama, $_POST['NAMAFRONT'], $_POST['NAMAPRINTER'],  $_POST['NAMAWARNA'],  $_POST['URUTAN'], $_SESSION['user'], date('Y-m-d'),
				$status
			);
			$query = $db->insert('mdepartemenmenu', $data_values, $tr);
			if (!$query) {
				// rollback transaction
				$db->rollback($tr);
				die(json_encode(array('errorMsg' => 'Simpan Data Gagal')));
			}

			// commit transaction
			$db->commit($tr);

			// panggil fungsi untuk log history
			log_history(
				$kode,
				'MASTER DEPARTEMEN MENU',
				$act,
				array(
					array(
						'nama'  => 'header',
						'tabel' => 'mdepartemenmenu',
						'kode'  => 'kodedepartemenmenu'
					),
				),
				$_SESSION['user']
			);

			echo json_encode(array('success' => true));
		} else if ($act=='delete'){
			$kode = $_POST['kodedepartemenmenu'];

			/*$p = $db->prepare('select first 1 skip 0 kodedepartemenmenu as kode from mdepartemenmenu where kodedepartemenmenu=?');
			$q = $db->execute($p, $kode);
			$r = $db->fetch($q);

			if ($r->KODE<>'') { die(json_encode(array('errorMsg'=>'Data Kategori Barang Tidak Dapat Dihapus, Data Sudah Digunakan Pada Transaksi'))); }
			*/
			$q = $db->delete('mdepartemenmenu', array('kodedepartemenmenu' => $kode));

			if ($q) {

				// panggil fungsi untuk log history
				log_history(
					$kode,
					'MASTER DEPARTEMEN MENU',
					$act,
					array(
						array(
							'nama'  => 'header',
							'tabel' => 'mdepartemenmenu',
							'kode'  => 'kodedepartemenmenu'
						),
					),
					$_SESSION['user']
				);

				echo json_encode(array('success'=>true));
			} else {
				echo json_encode(array('errorMsg'=>'Terdapat Data Error, Data Tidak Dapat Dihapus'));
			}
		}
	break;
	//========================= END MASTER DATA DEPARTEMEN MENU ===========================

	//========================= MASTER DATA KATEGORI MENU ===========================
	case 'kategori_menu' :
		if ($act=='insert' || $act=='edit'){
			$kode   = $_POST['KODEKATEGORIMENU'];
			$nama   = $_POST['NAMAKATEGORIMENU'];
			$status = $_POST['STATUS'];

			if ($kode=='') {
				$temp_kode = 'K';
				$kode = $temp_kode.get_max_urutan('mkategorimenu', 'kodekategorimenu', $temp_kode, 3);
			}

			if ($kode == '') die(json_encode(array('errorMsg' => 'Kode Kategori Menu Tidak Boleh Kosong')));
			if ($nama == '') die(json_encode(array('errorMsg' => 'Nama Kategori Menu Tidak Boleh Kosong')));

			//CEK APAKAH KODE DAN NAMA SUDAH DIGUNAKAN
			if ($act=='insert') {
				$p = $db->prepare('select kodekategorimenu, namakategorimenu from mkategorimenu where kodekategorimenu=?');
				$q = $db->execute($p, $kode);
				$r = $db->fetch($q);
				if ($r->KODEKATEGORIMENU!='') {
					die(json_encode(array('errorMsg' => 'Kode Kategori Menu Sudah Digunakan Oleh Kategori Menu ('.$r->KODEKATEGORIMENU.') '.$r->NAMAKATEGORIMENU.', ID Tidak Dapat Digunakan')));
				}

				$p = $db->prepare('select kodekategorimenu, namakategorimenu from mkategorimenu where namakategorimenu=?');
				$q = $db->execute($p, $nama);
				$r = $db->fetch($q);
				if ($r->NAMAKATEGORIMENU!='') {
					die(json_encode(array('errorMsg' => 'Nama Kategori Menu Sudah Digunakan Oleh Kategori Menu ('.$r->KODEKATEGORIMENU.') '.$r->NAMAKATEGORIMENU.', Deskripsi Tidak Dapat Digunakan')));
				}
				$status = 1;
			} else if ($act=='edit') {
				$p = $db->prepare('select kodekategorimenu, namakategorimenu from mkategorimenu where kodekategorimenu<>? and namakategorimenu=?');
				$q = $db->execute($p, array($kode, $nama));
				$r = $db->fetch($q);
				if ($r->NAMAKATEGORIBARANG!='') {
					die(json_encode(array('errorMsg' => 'Nama Kategori Menu Sudah Digunakan Oleh Kategori Menu ('.$r->KODEKATEGORIMENU.') '.$r->NAMAKATEGORIMENU.', Deskripsi Tidak Dapat Digunakan')));
				}
			}

			// start transaction
			$tr = $db->start_trans();

			$data_values = array (
				$kode, $nama, $_POST['NAMAFRONT'], $_POST['NAMAPRINTER'], $_POST['NAMAWARNA'], 
				$_POST['URUTAN'], $_SESSION['user'], date("Y-m-d"), $status
			);
			$query = $db->insert('mkategorimenu', $data_values, $tr);
			if (!$query) {
				// rollback transaction
				$db->rollback($tr);
				die(json_encode(array('errorMsg' => 'Simpan Data Gagal')));
			}

			// commit transaction
			$db->commit($tr);

			// panggil fungsi untuk log history
			log_history(
				$kode,
				'MASTER KATEGORI MENU',
				$act,
				array(
					array(
						'nama'  => 'header',
						'tabel' => 'mkategorimenu',
						'kode'  => 'kodekategorimenu'
					),
				),
				$_SESSION['user']
			);

			echo json_encode(array('success' => true));
		} else if ($act=='delete'){
			$kode = $_POST['kodekategorimenu'];

			//$p = $db->prepare('select first 1 skip 0 kodekategorim as kode from mbarang where kodekategoribarang=?');
			//$q = $db->execute($p, $kode);
			//$r = $db->fetch($q);

			//if ($r->KODE<>'') { die(json_encode(array('errorMsg'=>'Data Kategori Barang Tidak Dapat Dihapus, Data Sudah Digunakan Pada Transaksi'))); }

			$q = $db->delete('mkategorimenu', array('kodekategorimenu' => $kode));

			if ($q) {

				// panggil fungsi untuk log history
				log_history(
					$kode,
					'MASTER KATEGORI MENU',
					$act,
					array(
						array(
							'nama'  => 'header',
							'tabel' => 'mkategorimenu',
							'kode'  => 'kodekategorimenu'
						),
					),
					$_SESSION['user']
				);

				echo json_encode(array('success'=>true));
			} else {
				echo json_encode(array('errorMsg'=>$kode));
			}
		}
	break;
	//========================= END MASTER DATA KATEGORI MENU ===========================

	//========================= MASTER DATA MEJA ===========================
	case 'meja' :
		if ($act=='insert' || $act=='edit'){
			$kode   = $_POST['NOMORMEJA'];
			//$nama   = $_POST['NAMADEPARTEMENMENU'];
			$status = $_POST['STATUS'];

			if ($kode=='') {
				$temp_kode = 'K';
				$kode = $temp_kode.get_max_urutan('mmeja', 'nomormeja', $temp_kode, 3);
			}

			if ($kode == '') die(json_encode(array('errorMsg' => 'ID Kategori Buku Tidak Boleh Kosong')));
			//if ($nama == '') die(json_encode(array('errorMsg' => 'Deskripsi Kategori Buku Tidak Boleh Kosong')));

			//CEK APAKAH KODE DAN NAMA SUDAH DIGUNAKAN
			if ($act=='insert') {
				$p = $db->prepare('select nomormeja from mmeja where nomormeja=?');
				$q = $db->execute($p, $kode);
				$r = $db->fetch($q);
				if ($r->NOMORMEJA!='') {
					die(json_encode(array('errorMsg' => 'Kode Departemen Menu Sudah Digunakan Oleh Kategori Buku ('.$r->NOMORMEJA.') './*$r->NAMADEPARTEMENMENU.*/', Kode Tidak Dapat Digunakan')));
				}

				/*$p = $db->prepare('select nomormeja from mmeja where namadepartemenmenu=?');
				$q = $db->execute($p, $nama);
				$r = $db->fetch($q);
				if ($r->NAMADEPARTEMENMENU!='') {
					die(json_encode(array('errorMsg' => 'Nama Departemen Menu Sudah Digunakan Oleh Kategori Buku ('.$r->KODEDEPARTEMENMENU.') '.$r->NAMADEPARTEMENMENU.', Nama Tidak Dapat Digunakan')));
				}*/
				$status = 1;
			} else if ($act=='edit') {
				$p = $db->prepare('select nomormeja from mmeja where nomormeja<>?');
				$q = $db->execute($p, array($kode, $nama));
				$r = $db->fetch($q);
				/*if ($r->NAMADEPARTEMENMENU!='') {
					die(json_encode(array('errorMsg' => 'Deskripsi Kategori Buku Sudah Digunakan Oleh Kategori Buku ('.$r->KODEDEPARTEMENMENU.') '.$r->NAMADEPARTEMENMENU.', Deskripsi Tidak Dapat Digunakan')));
				}*/
			}

			// start transaction
			$tr = $db->start_trans();
			$time = new DateTime($_POST['ORDERTIME']);
			//die(json_encode(array('errorMsg' =>  date_format($time,'Y-m-d'))));	
			$data_values = array (
				$kode, $_POST['JUMLAHMAXCOVER'], $_POST['TANDA'], $_POST['KODETRANS'], date_format($time,'Y-m-d'), date_format($time,'H:i:s'), $_POST['PAYMENTTYPE'], $_POST['URUTAN'], $_SESSION['user'], date('Y-m-d'), $status
			);
			$query = $db->insert('mmeja', $data_values, $tr);
			if (!$query) {
				// rollback transaction
				$db->rollback($tr);
				die(json_encode(array('errorMsg' => 'Simpan Data Gagal')));
			}

			// commit transaction
			$db->commit($tr);

			// panggil fungsi untuk log history
			log_history(
				$kode,
				'MASTER MEJA',
				$act,
				array(
					array(
						'nama'  => 'header',
						'tabel' => 'mmeja',
						'kode'  => 'nomormeja'
					),
				),
				$_SESSION['user']
			);

			echo json_encode(array('success' => true));
		} else if ($act=='delete'){
			$kode = $_POST['nomormeja'];

			/*$p = $db->prepare('select first 1 skip 0 kodedepartemenmenu as kode from mdepartemenmenu where kodedepartemenmenu=?');
			$q = $db->execute($p, $kode);
			$r = $db->fetch($q);

			if ($r->KODE<>'') { die(json_encode(array('errorMsg'=>'Data Kategori Barang Tidak Dapat Dihapus, Data Sudah Digunakan Pada Transaksi'))); }
			*/
			$q = $db->delete('mmeja', array('nomormeja' => $kode));

			if ($q) {

				// panggil fungsi untuk log history
				log_history(
					$kode,
					'MASTER MEJA',
					$act,
					array(
						array(
							'nama'  => 'header',
							'tabel' => 'mmeja',
							'kode'  => 'nomormeja'
						),
					),
					$_SESSION['user']
				);

				echo json_encode(array('success'=>true));
			} else {
				echo json_encode(array('errorMsg'=>'Terdapat Data Error, Data Tidak Dapat Dihapus'));
			}
		}
	break;
	//========================= END MASTER DATA MEJA ===========================

	//========================= MASTER DATA VOUCHER ===========================
	case 'voucher' :
		if ($act=='insert' || $act=='edit'){
			$kode   = $_POST['KODEVOUCHER'];
			$nama   = $_POST['NAMAVOUCHER'];
			$status = $_POST['STATUS'];

			if ($kode=='') {
				$temp_kode = 'K';
				$kode = $temp_kode.get_max_urutan('mvoucher', 'kodevoucher', $temp_kode, 3);
			}

			if ($kode == '') die(json_encode(array('errorMsg' => 'Kode Voucher Menu Tidak Boleh Kosong')));
			if ($nama == '') die(json_encode(array('errorMsg' => 'Deskripsi Voucher Menu Tidak Boleh Kosong')));

			//CEK APAKAH KODE DAN NAMA SUDAH DIGUNAKAN
			if ($act=='insert') {
				$p = $db->prepare('select kodevoucher, namavoucher from mvoucher where kodevoucher=?');
				$q = $db->execute($p, $kode);
				$r = $db->fetch($q);
				if ($r->KODEVOUCHER!='') {
					die(json_encode(array('errorMsg' => 'Kode Voucher Sudah Digunakan Oleh Voucher ('.$r->KODEVOUCHER.') '.$r->NAMAVOUCHER.', ID Tidak Dapat Digunakan')));
				}

				$p = $db->prepare('select kodevoucher, namavoucher from mvoucher where namavoucher=?');
				$q = $db->execute($p, $nama);
				$r = $db->fetch($q);
				if ($r->NAMAVOUCHER!='') {
					die(json_encode(array('errorMsg' => 'Deskripsi Voucher Sudah Digunakan Oleh Voucher ('.$r->KODEVOUCHER.') '.$r->NAMAVOUCHER.', Deskripsi Tidak Dapat Digunakan')));
				}
				$status = 1;
			} else if ($act=='edit') {
				$p = $db->prepare('select kodevoucher, namavoucher from mvoucher where kodevoucher<>? and namavoucher=?');
				$q = $db->execute($p, array($kode, $nama));
				$r = $db->fetch($q);
				if ($r->NAMAVOUCHER!='') {
					die(json_encode(array('errorMsg' => 'Deskripsi Voucher Sudah Digunakan Oleh Voucher ('.$r->KODEVOUCHER.') '.$r->NAMAVOUCHER.', Deskripsi Tidak Dapat Digunakan')));
				}
			}

			// start transaction
			$tr = $db->start_trans();

			$data_values = array (
				$kode, $nama, $_POST['AMOUNT'], $_POST['HARGA'], $_SESSION['user'],
				date("Y-m-d"), $status
			);
			$query = $db->insert('mvoucher', $data_values, $tr);
			if (!$query) {
				// rollback transaction
				$db->rollback($tr);
				die(json_encode(array('errorMsg' => 'Simpan Data Gagal')));
			}

			// commit transaction
			$db->commit($tr);

			// panggil fungsi untuk log history
			log_history(
				$kode,
				'MASTER VOUCHER',
				$act,
				array(
					array(
						'nama'  => 'header',
						'tabel' => 'mvoucher',
						'kode'  => 'kodevoucher'
					),
				),
				$_SESSION['user']
			);

			echo json_encode(array('success' => true));
		} else if ($act=='delete'){
			$kode   = $_POST['id'];
			//$p = $db->prepare('select first 1 skip 0 kodekategorim as kode from mbarang where kodekategoribarang=?');
			//$q = $db->execute($p, $kode);
			//$r = $db->fetch($q);

			//if ($r->KODE<>'') { die(json_encode(array('errorMsg'=>'Data Kategori Barang Tidak Dapat Dihapus, Data Sudah Digunakan Pada Transaksi'))); }

			$q = $db->delete('mvoucher', array('kodevoucher' => $kode));

			if ($q) {

				// panggil fungsi untuk log history
				log_history(
					$kode,
					'MASTER VOUCHER',
					$act,
					array(
						array(
							'nama'  => 'header',
							'tabel' => 'mvoucher',
							'kode'  => 'kodevoucher'
						),
					),
					$_SESSION['user']
				);

				echo json_encode(array('success'=>true));
			} else {
				echo json_encode(array('errorMsg'=>$kode));
			}
		}
	break;
	//========================= END MASTER DATA VOUCHER===========================
	//========================= MASTER DATA KARTU KREDIT ===========================
	case 'kartu_kredit' :
		if ($act=='insert' || $act=='edit'){
			$kode   = $_POST['KODEKARTUKREDIT'];
			$nama   = $_POST['NAMAKARTUKREDIT'];
			$status = $_POST['STATUS'];

			if ($kode=='') {
				$temp_kode = 'K';
				$kode = $temp_kode.get_max_urutan('mkartukredit', 'kodekartukredit', $temp_kode, 3);
			}

			if ($kode == '') die(json_encode(array('errorMsg' => 'Kode Kartu Kredit Tidak Boleh Kosong')));
			if ($nama == '') die(json_encode(array('errorMsg' => 'Nama Kartu Kredit Tidak Boleh Kosong')));

			//CEK APAKAH KODE DAN NAMA SUDAH DIGUNAKAN
			if ($act=='insert') {
				$p = $db->prepare('select kodekartukredit, namakartukredit from mkartukredit where kodekartukredit=?');
				$q = $db->execute($p, $kode);
				$r = $db->fetch($q);
				if ($r->KODEKARTUKREDIT!='') {
					die(json_encode(array('errorMsg' => 'Kode Kartu Kredit Sudah Digunakan Oleh Kartu Kredit ('.$r->KODEKARTUKREDIT.') '.$r->NAMAKARTUKREDIT.', ID Tidak Dapat Digunakan')));
				}

				$p = $db->prepare('select kodekartukredit, namakartukredit from mkartukredit where namakartukredit=?');
				$q = $db->execute($p, $nama);
				$r = $db->fetch($q);
				if ($r->NAMAKARTUKREDIT!='') {
					die(json_encode(array('errorMsg' => 'Nama Kartu Kredit Sudah Digunakan Oleh Kartu Kredit ('.$r->KODEKARTUKREDIT.') '.$r->NAMAKARTUKREDIT.', Nama Tidak Dapat Digunakan')));
				}
				$status = 1;
			} else if ($act=='edit') {
				$p = $db->prepare('select kodekartukredit, namakartukredit from mkartukredit where kodekartukredit<>? and namakartukredit=?');
				$q = $db->execute($p, array($kode, $nama));
				$r = $db->fetch($q);
				if ($r->NAMAKARTUKREDIT!='') {
					die(json_encode(array('errorMsg' => 'Nama Kartu Kredit Sudah Digunakan Oleh Kartu Kredit ('.$r->KODEKARTUKREDIT.') '.$r->NAMAKARTUKREDIT.', Nama Tidak Dapat Digunakan')));
				}
			}

			// start transaction
			$tr = $db->start_trans();

			$data_values = array (
				$kode, $nama, $_POST['NOMORKARTUKREDIT'], $_POST['PANJANGNOMOR'], $_POST['JENISKARTU'],
				$_POST['AMOUNT'], $_POST['URUTAN'], $_SESSION['user'],date("Y-m-d"), $status
			);
			$query = $db->insert('mkartukredit', $data_values, $tr);
			if (!$query) {
				// rollback transaction
				$db->rollback($tr);
				die(json_encode(array('errorMsg' => 'Simpan Data Gagal')));
			}

			// commit transaction
			$db->commit($tr);

			// panggil fungsi untuk log history
			log_history(
				$kode,
				'MASTER KARTU KREDIT',
				$act,
				array(
					array(
						'nama'  => 'header',
						'tabel' => 'mkartukredit',
						'kode'  => 'kodekartukredit'
					),
				),
				$_SESSION['user']
			);

			echo json_encode(array('success' => true));
		} else if ($act=='delete'){
			$kode   = $_POST['id'];
			//$p = $db->prepare('select first 1 skip 0 kodekategorim as kode from mbarang where kodekategoribarang=?');
			//$q = $db->execute($p, $kode);
			//$r = $db->fetch($q);

			//if ($r->KODE<>'') { die(json_encode(array('errorMsg'=>'Data Kategori Barang Tidak Dapat Dihapus, Data Sudah Digunakan Pada Transaksi'))); }

			$q = $db->delete('mkartukredit', array('kodekartukredit' => $kode));

			if ($q) {

				// panggil fungsi untuk log history
				log_history(
					$kode,
					'MASTER KARTU KREDIT',
					$act,
					array(
						array(
							'nama'  => 'header',
							'tabel' => 'mkartukredit',
							'kode'  => 'kodekartukredit'
						),
					),
					$_SESSION['user']
				);

				echo json_encode(array('success'=>true));
			} else {
				echo json_encode(array('errorMsg'=>$kode));
			}
		}
	break;
	//========================= END MASTER DATA KARTU KREDIT ===========================

	//========================= MASTER DATA DISCOUNT ===========================
	case 'discount' :
		if ($act=='insert' || $act=='edit'){
			$kode   = $_POST['KODE'];
			$nama   = $_POST['NAMADISCOUNT'];
			$status = $_POST['STATUS'];

			if ($kode=='') {
				$temp_kode = 'K';
				$kode = $temp_kode.get_max_urutan('mdiscount', 'kode', $temp_kode, 3);
			}

			if ($kode == '') die(json_encode(array('errorMsg' => 'ID Kategori Buku Tidak Boleh Kosong')));
			if ($nama == '') die(json_encode(array('errorMsg' => 'Deskripsi Kategori Buku Tidak Boleh Kosong')));

			//CEK APAKAH KODE DAN NAMA SUDAH DIGUNAKAN
			if ($act=='insert') {
				$p = $db->prepare('select kode, namadiscount from mdiscount where kode=?');
				$q = $db->execute($p, $kode);
				$r = $db->fetch($q);
				if ($r->kode!='') {
					die(json_encode(array('errorMsg' => 'Kode Diskon Menu Sudah Digunakan Oleh Kategori Buku ('.$r->KODE.') '.$r->NAMADISCOUNT.', Kode Tidak Dapat Digunakan')));
				}

				$p = $db->prepare('select kode, namadiscount from mdiscount where namadiscount=?');
				$q = $db->execute($p, $nama);
				$r = $db->fetch($q);
				if ($r->NAMADISCOUNT!='') {
					die(json_encode(array('errorMsg' => 'Nama Diskon Menu Sudah Digunakan Oleh Kategori Buku ('.$r->KODE.') '.$r->NAMADISCOUNT.', Nama Tidak Dapat Digunakan')));
				}
				$status = 1;
			} else if ($act=='edit') {
				$p = $db->prepare('select kode, namadiscount from mdiscount where kode<>? and namadiscount=?');
				$q = $db->execute($p, array($kode, $nama));
				$r = $db->fetch($q);
				if ($r->NAMADISCOUNT!='') {
					die(json_encode(array('errorMsg' => 'Deskripsi Kategori Buku Sudah Digunakan Oleh Kategori Buku ('.$r->KODE.') '.$r->NAMADISCOUNT.', Deskripsi Tidak Dapat Digunakan')));
				}
			}

			// start transaction
			$tr = $db->start_trans();

			$data_values = array (
				$kode, $nama, $_POST['JENIS'], $_POST['DISCMEMBER'],  $_POST['AMOUNT'],  /*$_POST['URUTAN'],*/ $_SESSION['user'], date('Y-m-d'),
				$status
			);
			$query = $db->insert('mdiscount', $data_values, $tr);
			if (!$query) {
				// rollback transaction
				$db->rollback($tr);
				die(json_encode(array('errorMsg' => 'Simpan Data Gagal')));
			}

			// commit transaction
			$db->commit($tr);

			// panggil fungsi untuk log history
			log_history(
				$kode,
				'MASTER DISCOUNT',
				$act,
				array(
					array(
						'nama'  => 'header',
						'tabel' => 'mdiscount',
						'kode'  => 'kode'
					),
				),
				$_SESSION['user']
			);

			echo json_encode(array('success' => true));
		} else if ($act=='delete'){
			$kode = $_POST['kode'];

			/*$p = $db->prepare('select first 1 skip 0 kodedepartemenmenu as kode from mdepartemenmenu where kodedepartemenmenu=?');
			$q = $db->execute($p, $kode);
			$r = $db->fetch($q);

			if ($r->KODE<>'') { die(json_encode(array('errorMsg'=>'Data Kategori Barang Tidak Dapat Dihapus, Data Sudah Digunakan Pada Transaksi'))); }
			*/
			$q = $db->delete('mdiscount', array('kode' => $kode));

			if ($q) {

				// panggil fungsi untuk log history
				log_history(
					$kode,
					'MASTER DISCOUNT',
					$act,
					array(
						array(
							'nama'  => 'header',
							'tabel' => 'mdiscount',
							'kode'  => 'kode'
						),
					),
					$_SESSION['user']
				);

				echo json_encode(array('success'=>true));
			} else {
				echo json_encode(array('errorMsg'=>'Terdapat Data Error, Data Tidak Dapat Dihapus'));
			}
		}
	break;
	//========================= END MASTER DATA DISCOUNT ===========================
	//========================= MASTER DATA SATUAN ===========================
	case 'satuan' :
		if ($act=='insert' || $act=='edit'){
			$kode   = $_POST['KODESATUAN'];
			$nama   = $_POST['NAMASATUAN'];
			$status = $_POST['STATUS'];

			if ($kode=='') {
				$temp_kode = 'K';
				$kode = $temp_kode.get_max_urutan('msatuan', 'kodesatuan', $temp_kode, 3);
			}

			if ($kode == '') die(json_encode(array('errorMsg' => 'Kode Satuan Tidak Boleh Kosong')));
			if ($nama == '') die(json_encode(array('errorMsg' => 'Nama Satuan Tidak Boleh Kosong')));

			//CEK APAKAH KODE DAN NAMA SUDAH DIGUNAKAN
			if ($act=='insert') {
				$p = $db->prepare('select kodesatuan, namasatuan from msatuan where kodesatuan=?');
				$q = $db->execute($p, $kode);
				$r = $db->fetch($q);
				if ($r->KODESATUAN!='') {
					die(json_encode(array('errorMsg' => 'Kode Satuan Sudah Digunakan Oleh Satuan ('.$r->KODESATUAN.') '.$r->NAMASATUAN.', ID Tidak Dapat Digunakan')));
				}

				$p = $db->prepare('select kodesatuan, namasatuan from msatuan where namasatuan=?');
				$q = $db->execute($p, $nama);
				$r = $db->fetch($q);
				if ($r->NAMASATUAN!='') {
					die(json_encode(array('errorMsg' => 'Nama Satuan Sudah Digunakan Oleh Satuan ('.$r->KODESATUAN.') '.$r->NAMASATUAN.', Nama Tidak Dapat Digunakan')));
				}
				$status = 1;
			} else if ($act=='edit') {
				$p = $db->prepare('select kodesatuan, namasatuan from msatuan where kodesatuan<>? and namasatuan=?');
				$q = $db->execute($p, array($kode, $nama));
				$r = $db->fetch($q);
				if ($r->NAMASATUAN!='') {
					die(json_encode(array('errorMsg' => 'Nama Satuan Sudah Digunakan Oleh Satuan ('.$r->KODESATUAN.') '.$r->NAMASATUAN.', Nama Tidak Dapat Digunakan')));
				}
			}

			// start transaction
			$tr = $db->start_trans();

			$data_values = array (
				$kode, $nama, $_POST['KONVERSI'], $_POST['SATUAN2'], $_SESSION['user'],
				date("Y-m-d"), $status
			);
			$query = $db->insert('msatuan', $data_values, $tr);
			if (!$query) {
				// rollback transaction
				$db->rollback($tr);
				die(json_encode(array('errorMsg' => 'Simpan Data Gagal')));
			}

			// commit transaction
			$db->commit($tr);

			// panggil fungsi untuk log history
			log_history(
				$kode,
				'MASTER SATUAN',
				$act,
				array(
					array(
						'nama'  => 'header',
						'tabel' => 'msatuan',
						'kode'  => 'kodesatuan'
					),
				),
				$_SESSION['user']
			);

			echo json_encode(array('success' => true));
		} else if ($act=='delete'){
			$kode   = $_POST['id'];
			//$p = $db->prepare('select first 1 skip 0 kodekategorim as kode from mbarang where kodekategoribarang=?');
			//$q = $db->execute($p, $kode);
			//$r = $db->fetch($q);

			//if ($r->KODE<>'') { die(json_encode(array('errorMsg'=>'Data Kategori Barang Tidak Dapat Dihapus, Data Sudah Digunakan Pada Transaksi'))); }

			$q = $db->delete('msatuan', array('kodesatuan' => $kode));

			if ($q) {

				// panggil fungsi untuk log history
				log_history(
					$kode,
					'MASTER SATUAN',
					$act,
					array(
						array(
							'nama'  => 'header',
							'tabel' => 'msatuan',
							'kode'  => 'kodesatuan'
						),
					),
					$_SESSION['user']
				);

				echo json_encode(array('success'=>true));
			} else {
				echo json_encode(array('errorMsg'=>$kode));
			}
		}
	break;
	//========================= END MASTER DATA SATUAN===========================
	
	//========================= MASTER DATA SUB RESEP ===========================
	case 'sub_resep' :
		if ($act=='delete'){
			$kode   = $_POST['kode'];
			
			//cek di subresep dtl
			$p = $db->prepare('select first 1 skip 0 koderecipe as kode from mrecipedtl where koderecipe=?');
			$q = $db->execute($p, $kode);
			$r = $db->fetch($q);
			if ($r->KODE<>'') { die(json_encode(array('errorMsg'=>'Data Sub Resep Tidak Dapat Dihapus, Data Sudah Digunakan Pada Sub Resep '.$r->KODE))); }

			//cek di menu resep
			$p = $db->prepare('select first 1 skip 0 kode as kode from mmenurecipedtl where koderecipe=?');
			$q = $db->execute($p, $kode);
			$r = $db->fetch($q);
			if ($r->KODE<>'') { die(json_encode(array('errorMsg'=>'Data Menu Resep Tidak Dapat Dihapus, Data Sudah Digunakan Pada Menu Resep '.$r->KODE))); }

			$q = $db->delete('mrecipe', array('kode' => $kode));
			if ($q) {
				$q = $db->delete('mrecipedtl', array('koderecipe' => $kode));
			} else {
				echo json_encode(array('errorMsg'=>$kode));
			}
			if ($q) {

				// panggil fungsi untuk log history
				log_history(
					$kode,
					'MASTER SUB RESEP',
					$act,
					array(
						array(
							'nama'  => 'header',
							'tabel' => 'mrecipe',
							'kode'  => 'kode'
						),
					),
					$_SESSION['user']
				);

				echo json_encode(array('success'=>true));
			} else {
				echo json_encode(array('errorMsg'=>$kode));
			}
		}
	break;
	
	case 'load_data_sub_resep' :
		$kode = $_POST['kode'];
		$sql   = "select b.*,d.konversi as konversi
				  from mrecipe a
				  inner join mrecipedtl b on a.kode=b.kode
				  inner join mbarang c on b.kodebrg = c.kode
				  inner join msatuan d on c.satuan = d.kodesatuan
				  where a.kode = '$kode'
		          order by b.urutan";
		$query = $db->query($sql);
		$items = array();
		while ($rs = $db->fetch($query)) {
			$items[] = array(
				'kodebrg'		=> $rs->KODEBRG,
				'koderecipe'	=> $rs->KODERECIPE,
				'keterangan'	=> $rs->KETERANGAN,
				'jml'			=> $rs->JML,
				'satuan'		=> $rs->SATUAN,
				'jml2'			=> $rs->JML2,
				'satuan2'		=> $rs->SATUAN2,
				'konversi'		=> $rs->KONVERSI,
				'bhnbaku'		=> $rs->HARGA,
				'subtotal'		=> $rs->SUBTOTAL,
			);
		}
		echo json_encode(array(
			'success' => true,
			'detail' => $items,
		));
	break;
	
	case 'simpan_sub_resep' :
		$a_detail  = json_decode($_POST['data_detail']);

		$kode    	 = $_POST['KODE'];
		$kodekategori= $_POST['KODEKATEGORI'];
		$nama 		 = $_POST['NAMA'];
		$status 	 = $_POST['STATUS'];

		if (count($a_detail)<1) die(json_encode(array('errorMsg' => 'Detail Transaksi Tidak Boleh Kosong')));

		$a_msg = array();

		$mode = $_POST[mode];
		// start transaction
		$tr = $db->start_trans();

		// query header
		$data_values = array (
			$kode,$_POST['CK'],$_POST['JML'],$kodekategori,
			$nama, $_POST['SATUAN'],1,'',$_POST['GRANDTOTAL'],
			'',$_POST['GRANDTOTAL'],$_POST['PERALATAN'],$_POST['PENANGANAN'],$_POST['CARAMASAK'],
			$_POST['CARASAJI'],'',$_SESSION['user'],date("Y-m-d"), $status
		);
		$exe = $db->insert('mrecipe', $data_values, $tr);
		if (!$exe) {
			$db->rollback($tr);
			die(json_encode(array('errorMsg' => 'Simpan Data Gagal <br>Kesalahan Pada Header Data Transaksi')));
		}

		$jmlSP = 0;

		// query detail
		$i = 0;
		$sql = $db->insert('mrecipedtl', 11, $tr, true);
		$pr  = $db->prepare($sql, $tr);
		foreach ($a_detail as $item) {
			if($item->kodebrg!="")cek_valid_data('mbarang', 'kode',$item->kodebrg, 'Barang');
			if($item->koderecipe!="")cek_valid_data('mrecipe', 'kode',$item->koderecipe, 'Sub Resep');
			$data_values = array (
				$kode,$item->kodebrg,$item->koderecipe,$i,$item->keterangan,
				$item->jml,$item->satuan,$item->jml2,$item->satuan2,$item->bhnbaku,
				$item->subtotal
			);
			$exe = $db->execute($pr, $data_values);
			if (!$exe) { $db->rollback($tr); die(json_encode(array('errorMsg' => 'Simpan Data Gagal <br>Kesalahan Pada Detail Data Transaksi'))); }
			$i++;
		}
		$db->commit($tr);

		/*
		//MELAKUKAN TUTUP DATA PR
		$tr = $db->start_trans();
		$exe = $db->query("execute procedure TUTUP_DETAIL('$kodetrans')", $tr);
		$db->commit($tr);

		//MELAKUKAN TUTUP DATA PR
		$tr = $db->start_trans();
		$exe = $db->query("execute procedure TUTUP_HEADER('$kodetrans')", $tr);
		$db->commit($tr);
		*/
		// panggil fungsi untuk log history
		log_history(
			$kode,
			'SUB RESEP',
			$_POST['KODE']=='' ? 'INSERT' : 'EDIT',
			array(
				array(
					'nama' => 'header',
					'tabel' => 'mrecipe',
					'kode' => 'KODE'
				),
				array(
					'nama' => 'detail',
					'tabel' => 'mrecipedtl',
					'kode' => 'KODE'
				),
			),
			$_SESSION['user']
		);

		echo json_encode(array('success' => true, 'kode' => $kode));
	break;
	//========================= END MASTER DATA SUB RESEP===========================
	
	//========================= MASTER DATA BARANG ===========================
	case 'barang' :
		if ($act=='insert' || $act=='edit') {
			$kode	       = $_POST['KODE'];
			$nama	       = $_POST['NAMA'];
			$tipe	       = $_POST['TIPE'];
			$jenisbarang   = $_POST['KODEJENISBARANG'];
			$kodeperkiraan = $_POST['KODEPERKIRAAN'];
			$barcode       = $_POST['BARCODE'];
			$status		   = isset($_POST['STATUS']) ? $_POST['STATUS'] : 1;
			$lensanonstok  = isset($_POST['LENSANONSTOK']) ? $_POST['LENSANONSTOK'] : 0;

			$a_detail = json_decode($_POST['data_detail']);

			//if ($kode == '') die(json_encode(array('errorMsg' => 'Kode Barang Tidak Boleh Kosong')));
			if ($nama == '') die(json_encode(array('errorMsg' => 'Nama Barang Tidak Boleh Kosong')));
			//cek_valid_data('mkategoribarang', 'kodekategoribarang', $kodekategori, 'Kategori Buku');
			cek_valid_data('mperkiraan', 'kode', $kodeperkiraan, 'Chart Of Account');

			//if ($_POST['KODEKATEGORIBARANG'] == '') die(json_encode(array('errorMsg' => 'Item Category Tidak Boleh Kosong')));
			if ($_POST['SATUAN'] == '') die(json_encode(array('errorMsg' => 'Satuan Tidak Boleh Kosong')));
			//if ($_POST['SATUAN2'])!='' && $_POST['KONVERSI1']==0) die(json_encode(array('errorMsg' => 'Konversi 1 Tidak Boleh Kosong')));
			//if ($_POST['SATUAN2'] == '' && $_POST['KONVERSI1']!=0) die(json_encode(array('errorMsg' => 'Konversi 2 Tidak Boleh Kosong')));
	
			if ($kode=='') {
				//$temp_kode = 'B'.substr($nama, 0, 1);
				//$kode = $temp_kode.get_max_urutan('mbarang', 'kodebarang', $temp_kode, 3);

				// buat generate kode
				if ($lensanonstok == 1) {
					$temp_kode = $jenisbarang . $_POST['KODEBAHAN'];

					$kode = $temp_kode.get_max_urutan('mbarang', 'kodebarang', $temp_kode, 11);
				} else {
					if ($jenisbarang >= 3 and $jenisbarang <= 9) {
						// tanpa kategori

						$temp_kode = $jenisbarang;
						$kode = $temp_kode.get_max_urutan('mbarang', 'kodebarang', $temp_kode, 12);
					} else {
						if (strlen($_POST['KODEKATEGORI']) <> 3) {
							die(json_encode(array('errorMsg' => 'Data Kategori Harus Lengkap')));
						}

						// dengan kategori
						$temp_kode = $jenisbarang . date('ym') . $_POST['KODEKATEGORI'];
						$kode = $temp_kode.get_max_urutan('mbarang', 'kodebarang', $temp_kode, 5);
					}

					/*
					dipindah ke file numberbox.php
					// jika jenis bahan [frame, sunglass, accesories]
					// maka tambahkan barcode
					if (in_array($jenisbarang, array(1, 2, 4))) {
						$sql = "select max(barcode) as max_barcode
								from mbarang";
						$query = $db->query($sql);
						$rs = $db->fetch($query);

						$str = (string) substr($rs->MAX_BARCODE, 0, 1);
						$huruf = range('A', 'Z');
						$angka = substr($rs->MAX_BARCODE, 1, 5);

						if (in_array($str, $huruf)) {
							if ($angka == 9999) {
								$a = array_keys($huruf, $str);

								$barcode = $huruf[$a[0]+1].'0001';
							} else {
								$barcode = $str.substr(($angka + 10001), 1, 5);
							}
						} else {
							$barcode = 'A0001';
						}
					}
					*/
				}
			}

			//CEK APAKAH KODE DAN NAMA SUDAH DIGUNAKAN
			if ($act=='insert'){
				$p = $db->prepare('select kode, nama from mbarang where kode=?');
				$q = $db->execute($p, $kode);
				$r = $db->fetch($q);
				if ($r->KODE!='') {
					die(json_encode(array('errorMsg' => 'Kode Barang Sudah Digunakan Oleh Barang ('.$r->KODE.') '.$r->NAMA.', Kode Barang Tidak Dapat Digunakan')));
				}
				
				$status = 1;
			} else if ($act=='edit'){
				$gambar = $_POST['GAMBAR'];
			}

			$tr = $db->start_trans();
			/*	DANIEL REMARK
			$konversi1    = $_POST['KONVERSI1']=='' ? 0 : $_POST['KONVERSI1'];
			$konversi2    = $_POST['KONVERSI2']=='' ? 0 : $_POST['KONVERSI2'];
			$harga1       = 0;//$_POST['HARGA1']=='' ? 0 : $_POST['HARGA1'];
			$harga2       = 0;//$_POST['HARGA2']=='' ? 0 : $_POST['HARGA2'];
			$harga_beli   = $_POST['HARGABELI']=='' ? 0 : $_POST['HARGABELI'];
			$persentase_margin   = $_POST['PERSENTASEMARGIN']=='' ? 0 : $_POST['PERSENTASEMARGIN'];
			$harga_jual   = $_POST['HARGAJUAL']=='' ? 0 : $_POST['HARGAJUAL'];
			$harga_paket  = $_POST['HARGAPAKET']=='' ? 0 : $_POST['HARGAPAKET'];
			$lama_pesan   = $_POST['LAMAPESAN']=='' ? 0 : $_POST['LAMAPESAN'];
			$kombinasi    = $_POST['KOMBINASIMAKSIMUM']=='' ? 0 : $_POST['KOMBINASIMAKSIMUM'];
			$poin         = $_POST['POIN']=='' ? 0 : $_POST['POIN'];
			$limit_min    = $_POST['LIMITMIN']=='' ? 0 : $_POST['LIMITMIN'];
			$limit_max    = $_POST['LIMITMAX']=='' ? 0 : $_POST['LIMITMAX'];

			$data_values = array (
				$jenisbarang, $_POST['KODEKATEGORI1'], $_POST['KODEKATEGORI2'], $_POST['KODEKATEGORI3'], $_POST['KODEBAHAN'],
				$kode, $_POST['NAMABARANG'], $_POST['TIPE'], $barcode, $_POST['SATUAN'], '',
				'', 1, 1, $_POST['KODESUPPLIER'], $_POST['NAMABARANGSUPPLIER'],
				$harga_beli, $persentase_margin, $harga_jual, $harga_paket, $lensanonstok,
				$_POST['INDEXBIAS'], $_POST['SPHERISMINIMUM'], $_POST['SPHERISMAKSIMUM'], $_POST['CYLINDERMINIMUM'], $_POST['CYLINDERMAKSIMUM'],
				$lama_pesan, $kombinasi, $poin, $limit_min, $limit_max,
				$_POST['KODEPERKIRAAN'], '', $_POST['REMARK'], $_SESSION['user'], date("Y-m-d"),
				$status
			);
			*/
			
			if ($_FILES["FILEGAMBAR"]['name'] != '') {
				// upload gambar
				$target_dir = "../../gambar-barang/";
				$uploadOk = 1;
				$imageFileType = pathinfo($_FILES['FILEGAMBAR']['name'], PATHINFO_EXTENSION);
				$target_file = $target_dir . str_replace('/', '.', $kode) . '.' . $imageFileType;
				$gambar = str_replace('/', '.', $kode) . '.' . $imageFileType;
				// Check if image file is a actual image or fake image

				$check = getimagesize($_FILES["FILEGAMBAR"]["tmp_name"]);
				if ($check !== false) {
					$uploadOk = 1;
				} else {
					die(json_encode(array('errorMsg' => 'File yang Diupload bukan gambar')));
					$uploadOk = 0;
				}

				if ($_POST['GAMBAR'] != '') {
					unlink($target_dir.$_POST['GAMBAR']);
				}
				// Check if file already exists
				if (file_exists($target_file)) {
					//$uploadOk = 0;
					unlink($target_file);
				}
				// Check file size
				if ($_FILES["FILEGAMBAR"]["size"] > 500000) {
					die(json_encode(array('errorMsg' => 'Sorry, your file is too large.')));

					$uploadOk = 0;
				}
				// Allow certain file formats
				if ( ! in_array(strtolower($imageFileType), array('jpg', 'png', 'jpeg', 'gif')) ) {
					die(json_encode(array('errorMsg' => 'Sorry, only JPG, JPEG, PNG & GIF files are allowed.')));

					$uploadOk = 0;
				}
				// Check if $uploadOk is set to 0 by an error
				//if ($uploadOk == 0) {
					//die(json_encode(array('errorMsg' => 'Sorry, your file was not uploaded.')));
				// if everything is ok, try to upload file
				//} else {
					if (move_uploaded_file($_FILES["FILEGAMBAR"]["tmp_name"], $target_file)) {
						//echo "The file ". basename($_FILES["GAMBAR"]["name"]). " has been uploaded.";
					} else {
						die(json_encode(array('errorMsg' => 'Sorry, there was an error uploading your file.')));
					}
				//}
			}
			
			$data_values = array(
				$kode, $barcode, $_POST['KODEKATEGORI'],  $_POST['KODEDEPARTEMEN'], $jenisbarang,
				$kodeperkiraan, $nama, $_POST['SATUAN'], 0, $_POST['QTYMIN'],
				$_POST['QTYMAX'], $_POST['SATUAN2'], $_POST['LIMITMIN'], $_POST['LIMITMAX'], $_POST['STOKIDEAL'],
				$gambar,'','',$_POST['SPESIFIKASI'], $_POST['NOTE'],
				$_POST['VALIDASIKONVERSI'], '', $_POST['DIVISI'], '', 1,
				'','','','', $_SESSION['user'],
				date("Y-m-d"), $status
				
			);
			$query = $db->insert('mbarang', $data_values, $tr);
				
			if (!$query) {
				$db->rollback($tr);
				die(json_encode(array('errorMsg' => 'Simpan Data Gagal')));
			}

			// commit transaction
			$db->commit($tr);

			// panggil fungsi untuk log history
			log_history(
				$kode,
				'MASTER BARANG',
				$act,
				array(
					array(
						'nama'  => 'header',
						'tabel' => 'mbarang',
						'kode'  => 'kode'
					),
				),
				$_SESSION['user']
			);		
			
			echo json_encode(array('success' => true));
		} else if ($act=='delete') {
			$kode = $_POST['id'];

			$p = $db->prepare("select first 1 skip 0 kodebrg from kartustok where kodebrg=?");
			$q = $db->execute($p, $kode);
			$r = $db->fetch($q);

			if ($r->KODE<>'') { die(json_encode(array('errorMsg'=>'Data Barang Tidak Dapat Dihapus, Data Sudah Digunakan Pada Transaksi'))); }

			$q = $db->delete('mbarang', array('kode' => $kode));

			if ($q) {
				// panggil fungsi untuk log history
				log_history(
					$kode,
					'MASTER ITEMS',
					$act,
					array(
						array(
							'nama' => 'header',
							'tabel' => 'mbarang',
							'kode' => 'kode'
						),
					),
					$_SESSION['user']
				);

				echo json_encode(array('success'=>true));
			} else {
				echo json_encode(array('errorMsg'=>'Terdapat Data Error, Data Tidak Dapat Dihapus'));
			}
		}
	break;
	//========================= END MASTER DATA BARANG ===========================

	//========================= MASTER DATA DEPARTEMEN BARANG ===========================
	case 'departemen_barang' :
		if ($act=='insert' || $act=='edit'){
			$kode   = $_POST['KODEDEPARTEMENBARANG'];
			$nama   = $_POST['NAMADEPARTEMENBARANG'];
			$status = $_POST['STATUS'];

			if ($kode=='') {
				$temp_kode = 'K';
				$kode = $temp_kode.get_max_urutan('mdepartemenbarang', 'kode', $temp_kode, 3);
			}

			if ($kode == '') die(json_encode(array('errorMsg' => 'ID Departemen Barang Tidak Boleh Kosong')));
			if ($nama == '') die(json_encode(array('errorMsg' => 'Deskripsi Departemen Barang Tidak Boleh Kosong')));

			//CEK APAKAH KODE DAN NAMA SUDAH DIGUNAKAN
			if ($act=='insert') {
				$p = $db->prepare('select kodedepartemenbarang, namadepartemenbarang from mdepartemenbarang where kodedepartemenbarang=?');
				$q = $db->execute($p, $kode);
				$r = $db->fetch($q);
				if ($r->KODEDEPARTEMENBARANG!='') {
					die(json_encode(array('errorMsg' => 'Kode Departemen Barang Sudah Digunakan Oleh Departemen Barang ('.$r->KODEDEPARTEMENBARANG.') '.$r->NAMADEPARTEMENBARANG.', Kode Tidak Dapat Digunakan')));
				}

				$p = $db->prepare('select kodedepartemenbarang, namadepartemenbarang from mdepartemenbarang where namadepartemenbarang=?');
				$q = $db->execute($p, $nama);
				$r = $db->fetch($q);
				if ($r->NAMADEPARTEMENBARANG!='') {
					die(json_encode(array('errorMsg' => 'Nama Departemen Barang Sudah Digunakan Oleh Departemen Barang ('.$r->KODEDEPARTEMENBARANG.') '.$r->NAMADEPARTEMENBARANG.', Nama Tidak Dapat Digunakan')));
				}
				$status = 1;
			} else if ($act=='edit') {
				$p = $db->prepare('select kodedepartemenbarang, namadepartemenbarang from mdepartemenbarang where kodedepartemenbarang<>? and namadepartemenbarang=?');
				$q = $db->execute($p, array($kode, $nama));
				$r = $db->fetch($q);
				if ($r->NAMADEPARTEMENBARANG!='') {
					die(json_encode(array('errorMsg' => 'Deskripsi Departemen Barang Sudah Digunakan Oleh Departemen Barang ('.$r->KODEDEPARTEMENBARANG.') '.$r->NAMADEPARTEMENBARANG.', Deskripsi Tidak Dapat Digunakan')));
				}
			}

			// start transaction
			$tr = $db->start_trans();

			$data_values = array (
				$kode, $nama, $_POST['PLU'], $_POST['KETERANGAN'], $_SESSION['user'],
				date('Y-m-d'), $status
			);
			$query = $db->insert('mdepartemenbarang', $data_values, $tr);
			if (!$query) {
				// rollback transaction
				$db->rollback($tr);
				die(json_encode(array('errorMsg' => 'Simpan Data Gagal')));
			}

			// commit transaction
			$db->commit($tr);

			// panggil fungsi untuk log history
			log_history(
				$kode,
				'MASTER KATEGORI BARANG',
				$act,
				array(
					array(
						'nama'  => 'header',
						'tabel' => 'mdepartemenbarang',
						'kode'  => 'kodedepartemenbarang'
					),
				),
				$_SESSION['user']
			);

			echo json_encode(array('success' => true));
		} else if ($act=='delete'){
			$kode = $_POST['kodedepartemenbarang'];

			/*$p = $db->prepare('select first 1 skip 0 kodedepartemenmenu as kode from mdepartemenmenu where kodedepartemenmenu=?');
			$q = $db->execute($p, $kode);
			$r = $db->fetch($q);

			if ($r->KODE<>'') { die(json_encode(array('errorMsg'=>'Data Kategori Barang Tidak Dapat Dihapus, Data Sudah Digunakan Pada Transaksi'))); }
			*/
			$q = $db->delete('mdepartemenbarang', array('kodedepartemenbarang' => $kode));

			if ($q) {

				// panggil fungsi untuk log history
				log_history(
					$kode,
					'MASTER KATEGORI BARANG',
					$act,
					array(
						array(
							'nama'  => 'header',
							'tabel' => 'mdepartemenbarang',
							'kode'  => 'kodedepartemenbarang'
						),
					),
					$_SESSION['user']
				);

				echo json_encode(array('success'=>true));
			} else {
				echo json_encode(array('errorMsg'=>'Terdapat Data Error, Data Tidak Dapat Dihapus'));
			}
		}
	break;
	//========================= END MASTER DATA DEPARTEMEN BARANG ===========================
	//========================= MASTER DATA KATEGORI BARANG ===========================
	case 'kategori_barang' :
		if ($act=='insert' || $act=='edit'){
			$kode   = $_POST['KODEKATEGORIBARANG'];
			$nama   = $_POST['NAMAKATEGORIBARANG'];
			$status = $_POST['STATUS'];

			if ($kode=='') {
				$temp_kode = 'K';
				$kode = $temp_kode.get_max_urutan('mkategoribarang', 'kodekategoribarang', $temp_kode, 3);
			}

			if ($kode == '') die(json_encode(array('errorMsg' => 'ID Kategori Barang Tidak Boleh Kosong')));
			if ($nama == '') die(json_encode(array('errorMsg' => 'Deskripsi Kategori Barang Tidak Boleh Kosong')));

			//CEK APAKAH KODE DAN NAMA SUDAH DIGUNAKAN
			if ($act=='insert') {
				$p = $db->prepare('select kodekategoribarang, namakategoribarang from mkategoribarang where kodekategoribarang=?');
				$q = $db->execute($p, $kode);
				$r = $db->fetch($q);
				if ($r->KODEKATEGORIBARANG!='') {
					die(json_encode(array('errorMsg' => 'Kode Kategori Barang Sudah Digunakan Oleh Kategori Barang ('.$r->KODEKATEGORIBARANG.') '.$r->NAMAKATEGORIBARANG.', Kode Tidak Dapat Digunakan')));
				}

				$p = $db->prepare('select kodekategoribarang, namakategoribarang from mkategoribarang where namakategoribarang=?');
				$q = $db->execute($p, $nama);
				$r = $db->fetch($q);
				if ($r->KODEKATEGORIBARANG!='') {
					die(json_encode(array('errorMsg' => 'Nama Kategori Barang Sudah Digunakan Oleh Kategori Barang ('.$r->KODEKATEGORIBARANG.') '.$r->NAMAKATEGORIBARANG.', Nama Tidak Dapat Digunakan')));
				}
				$status = 1;
			} else if ($act=='edit') {
				$p = $db->prepare('select kodekategoribarang, namakategoribarang from mkategoribarang where kodekategoribarang<>? and namakategoribarang=?');
				$q = $db->execute($p, array($kode, $nama));
				$r = $db->fetch($q);
				if ($r->NAMAKATEGORIBARANG!='') {
					die(json_encode(array('errorMsg' => 'Deskripsi Kategori Barang Sudah Digunakan Oleh Kategori Barang ('.$r->KODEKATEGORIBARANG.') '.$r->NAMAKATEGORIBARANG.', Deskripsi Tidak Dapat Digunakan')));
				}
			}

			// start transaction
			$tr = $db->start_trans();

			$data_values = array (
				$kode, $nama, $_SESSION['user'], date('Y-m-d'),$status
			);
			$query = $db->insert('mkategoribarang', $data_values, $tr);
			if (!$query) {
				// rollback transaction
				$db->rollback($tr);
				die(json_encode(array('errorMsg' => 'Simpan Data Gagal')));
			}

			// commit transaction
			$db->commit($tr);

			// panggil fungsi untuk log history
			log_history(
				$kode,
				'MASTER KATEGORI BARANG',
				$act,
				array(
					array(
						'nama'  => 'header',
						'tabel' => 'mkategoribarang',
						'kode'  => 'kodekategoribarang'
					),
				),
				$_SESSION['user']
			);

			echo json_encode(array('success' => true));
		} else if ($act=='delete'){
			$kode = $_POST['kodekategoribarang'];

			/*$p = $db->prepare('select first 1 skip 0 kodedepartemenmenu as kode from mdepartemenmenu where kodedepartemenmenu=?');
			$q = $db->execute($p, $kode);
			$r = $db->fetch($q);

			if ($r->KODE<>'') { die(json_encode(array('errorMsg'=>'Data Kategori Barang Tidak Dapat Dihapus, Data Sudah Digunakan Pada Transaksi'))); }
			*/
			$q = $db->delete('mkategoribarang', array('kodekategoribarang' => $kode));

			if ($q) {

				// panggil fungsi untuk log history
				log_history(
					$kode,
					'MASTER KATEGORI BARANG',
					$act,
					array(
						array(
							'nama'  => 'header',
							'tabel' => 'mkategoribarang',
							'kode'  => 'kodekategoribarang'
						),
					),
					$_SESSION['user']
				);

				echo json_encode(array('success'=>true));
			} else {
				echo json_encode(array('errorMsg'=>'Terdapat Data Error, Data Tidak Dapat Dihapus'));
			}
		}
	break;
	//========================= END MASTER DATA KATEGORI BARANG ===========================

	//========================= MASTER DATA BRAND ===========================
	case 'brand' :
		if ($act=='insert' || $act=='edit'){
			$kode   = $_POST['KODE'];
			$nama   = $_POST['NAMA'];
			$status = $_POST['STATUS'];

			if ($kode=='') {
				$temp_kode = 'K';
				$kode = $temp_kode.get_max_urutan('mbrand', 'kode', $temp_kode, 3);
			}

			if ($kode == '') die(json_encode(array('errorMsg' => 'Kode Brand Tidak Boleh Kosong')));
			if ($nama == '') die(json_encode(array('errorMsg' => 'Nama Brand Tidak Boleh Kosong')));

			//CEK APAKAH KODE DAN NAMA SUDAH DIGUNAKAN
			if ($act=='insert') {
				$p = $db->prepare('select kode, nama from mbrand where kode=?');
				$q = $db->execute($p, $kode);
				$r = $db->fetch($q);
				if ($r->KODE!='') {
					die(json_encode(array('errorMsg' => 'Kode Brand Sudah Digunakan Oleh Brand ('.$r->KODE.') '.$r->NAMA.', ID Tidak Dapat Digunakan')));
				}

				$p = $db->prepare('select kode, nama from mbrand where nama=?');
				$q = $db->execute($p, $nama);
				$r = $db->fetch($q);
				if ($r->NAMA!='') {
					die(json_encode(array('errorMsg' => 'Nama Brand Sudah Digunakan Oleh Brand ('.$r->KODE.') '.$r->NAMA.', Deskripsi Tidak Dapat Digunakan')));
				}
				$status = 1;
			} else if ($act=='edit') {
				$p = $db->prepare('select kode, nama from mbrand where kode<>? and nama=?');
				$q = $db->execute($p, array($kode, $nama));
				$r = $db->fetch($q);
				if ($r->NAMA!='') {
					die(json_encode(array('errorMsg' => 'Nama Brand Sudah Digunakan Oleh Brand ('.$r->KODE.') '.$r->NAMA.', Deskripsi Tidak Dapat Digunakan')));
				}
			}

			// start transaction
			$tr = $db->start_trans();
			$data_values = array (
				$kode, $nama,$_POST['NAMAWARNA'],$_POST['URUTAN'],
				$_SESSION['user'], date("Y-m-d"), $status
			);
			$query = $db->insert('mbrand', $data_values, $tr);
			if (!$query) {
				// rollback transaction
				$db->rollback($tr);
				die(json_encode(array('errorMsg' => 'Simpan Data Gagal')));
			}

			// commit transaction
			$db->commit($tr);

			// panggil fungsi untuk log history
			log_history(
				$kode,
				'MASTER BRAND',
				$act,
				array(
					array(
						'nama'  => 'header',
						'tabel' => 'mbrand',
						'kode'  => 'kode'
					),
				),
				$_SESSION['user']
			);

			echo json_encode(array('success' => true));
		} else if ($act=='delete'){
			$kode = $_POST['kode'];

			//$p = $db->prepare('select first 1 skip 0 kodekategorim as kode from mbarang where kodekategoribarang=?');
			//$q = $db->execute($p, $kode);
			//$r = $db->fetch($q);

			//if ($r->KODE<>'') { die(json_encode(array('errorMsg'=>'Data Kategori Barang Tidak Dapat Dihapus, Data Sudah Digunakan Pada Transaksi'))); }

			$q = $db->delete('mbrand', array('kode' => $kode));

			if ($q) {

				// panggil fungsi untuk log history
				log_history(
					$kode,
					'MASTER BRAND',
					$act,
					array(
						array(
							'nama'  => 'header',
							'tabel' => 'mbrand',
							'kode'  => 'kode'
						),
					),
					$_SESSION['user']
				);

				echo json_encode(array('success'=>true));
			} else {
				echo json_encode(array('errorMsg'=>$kode));
			}
		}
	break;
	//========================= END MASTER DATA BRAND ===========================
	
	//========================= MASTER DATA PROMO ===========================
	case 'promo' :
		if ($act=='delete'){
			$kode   = $_POST['kode'];
			
			$q = $db->delete('mpromo', array('kodepromo' => $kode));

			if ($q) {

				// panggil fungsi untuk log history
				log_history(
					$kode,
					'MASTER PROMO',
					$act,
					array(
						array(
							'nama'  => 'header',
							'tabel' => 'mpromo',
							'kode'  => 'kodepromo'
						),
					),
					$_SESSION['user']
				);

				echo json_encode(array('success'=>true));
			} else {
				echo json_encode(array('errorMsg'=>$kode));
			}
		}
	break;
	
	case 'load_data_promo' :
		$kode = $_POST['kode'];
		$sql   = "select a.kode,a.nama
				  from mmenurecipe a
				  inner join mpromomenurecipe b on a.kode=b.kodemenurecipe
				  where b.kodepromo = '$kode'";
		$query = $db->query($sql);
		$items = array();
		while ($rs = $db->fetch($query)) {
			$items[] = array(
				'kode'		=> $rs->KODE,
				'nama'		=> $rs->NAMA,
			);
		}
		echo json_encode(array(
			'success' => true,
			'detail' => $items,
		));
	break;
	
	case 'simpan_promo' :
		$a_detail  = json_decode($_POST['data_detail']);

		$kode    	 = $_POST['KODEPROMO'];
		$nama 		 = $_POST['NAMAPROMO'];
		$status 	 = $_POST['STATUS'];

		if (count($a_detail)<1) die(json_encode(array('errorMsg' => 'Detail Transaksi Tidak Boleh Kosong')));

		$a_msg = array();

		$mode = $_POST[mode];
		// start transaction
		$tr = $db->start_trans();
		$hari = '';		
		isset($_POST['SENIN'])?$hari.='1':$hari.='0';
		isset($_POST['SELASA'])?$hari.='1':$hari.='0';
		isset($_POST['RABU'])?$hari.='1':$hari.='0';
		isset($_POST['KAMIS'])?$hari.='1':$hari.='0';
		isset($_POST['JUMAT'])?$hari.='1':$hari.='0'; 
		isset($_POST['SABTU'])?$hari.='1':$hari.='0';
		isset($_POST['MINGGU'])?$hari.='1':$hari.='0';
		
		// query header
		$data_values = array (
			$kode,$nama,$_POST['JENIS'],$_POST['AMOUNT'],$_POST['PERIODEAWAL'],
			$_POST['PERIODEAKHIR'], $hari,$_POST['LIMITGRANDTOTAL'],$_POST['KETERANGAN'],
			$_SESSION['user'],date("Y-m-d"), $status
		);
		$exe = $db->insert('mpromo', $data_values, $tr);
		if (!$exe) {
			$db->rollback($tr);
			die(json_encode(array('errorMsg' => 'Simpan Data Gagal <br>Kesalahan Pada Header Data Transaksi')));
		}

		$jmlSP = 0;

		// query detail
		$i = 0;
		$sql = $db->insert('mpromomenurecipe', 2, $tr, true);
		$pr  = $db->prepare($sql, $tr);
		foreach ($a_detail as $item) {
			$data_values = array (
				$kode,$item->kode
			);
			$exe = $db->execute($pr, $data_values);
			if (!$exe) { $db->rollback($tr); die(json_encode(array('errorMsg' => 'Simpan Data Gagal <br>Kesalahan Pada Detail Data Transaksi'))); }
			$i++;
		}
		$db->commit($tr);

		/*
		//MELAKUKAN TUTUP DATA PR
		$tr = $db->start_trans();
		$exe = $db->query("execute procedure TUTUP_DETAIL('$kodetrans')", $tr);
		$db->commit($tr);

		//MELAKUKAN TUTUP DATA PR
		$tr = $db->start_trans();
		$exe = $db->query("execute procedure TUTUP_HEADER('$kodetrans')", $tr);
		$db->commit($tr);
		*/
		// panggil fungsi untuk log history
		log_history(
			$kode,
			'PROMO',
			$_POST['KODE']=='' ? 'INSERT' : 'EDIT',
			array(
				array(
					'nama' => 'header',
					'tabel' => 'mpromo',
					'kode' => 'kodepromo'
				),
				array(
					'nama' => 'detail',
					'tabel' => 'mpromomenurecipe',
					'kode' => 'kodepromo'
				),
			),
			$_SESSION['user']
		);

		echo json_encode(array('success' => true, 'kode' => $kode));
	break;
	//========================= END MASTER DATA PROMO===========================
	
	//========================= MASTER DATA MENU RESEP ===========================
	case 'menu_resep' :
		if ($act=='delete'){
			$kode   = $_POST['kode'];
			//cek di menuresepdtl
			$p = $db->prepare('select first 1 skip 0 kode as kode from mmenurecipedtl where kodemenurecipe=?');
			$q = $db->execute($p, $kode);
			$r = $db->fetch($q);
			if ($r->KODE<>'') { die(json_encode(array('errorMsg'=>'Data Menu Resep Tidak Dapat Dihapus, Data Sudah Digunakan Pada Menu Resep '.$r->KODE))); }
			
			//cek di menuresepselected
			$p = $db->prepare('select first 1 skip 0 kodemenurecipe as kode from mmenurecipeselected where kodemenurecipedtl=?');
			$q = $db->execute($p, $kode);
			$r = $db->fetch($q);
			if ($r->KODE<>'') { die(json_encode(array('errorMsg'=>'Data Menu Resep Tidak Dapat Dihapus, Data Sudah Digunakan Pada Menu Resep Pilihan '.$r->KODE))); }

			$q = $db->delete('mmenurecipe', array('kode' => $kode));
			if ($q) {
				$q = $db->delete('mmenurecipedtl', array('kodemenurecipe' => $kode));
				$q = $db->delete('mmenurecipeselected', array('kodemenurecipedtl' => $kode));
			}
			else{
				echo json_encode(array('errorMsg'=>$kode));
			}
			if ($q) {

				// panggil fungsi untuk log history
				log_history(
					$kode,
					'MASTER MENU RESEP',
					$act,
					array(
						array(
							'nama'  => 'header',
							'tabel' => 'mmenurecipe',
							'kode'  => 'kode'
						),
					),
					$_SESSION['user']
				);

				echo json_encode(array('success'=>true));
			} else {
				echo json_encode(array('errorMsg'=>$kode));
			}
		}
	break;
	
	case 'load_data_menu_resep' :
		$kode = $_POST['kode'];
		$sql   = "select b.*,iif(e.konversi is null,f.konversi,e.konversi) as konversi
				  from mmenurecipe a
				  inner join mmenurecipedtl b on a.kode=b.kode
				  left join mbarang c on b.kodebrg = c.kode
				  left join mrecipe d on b.koderecipe = d.kode
				  left join msatuan e on c.satuan = e.kodesatuan
				  left join msatuan f on d.satuan = f.kodesatuan
				  where a.kode = '$kode'
		          order by b.urutan";
		$query = $db->query($sql);
		$items = array();
		while ($rs = $db->fetch($query)) {
			$items[] = array(
				'kodebrg'		=> $rs->KODEBRG,
				'koderecipe'	=> $rs->KODERECIPE,
				'kodemenurecipe'=> $rs->KODEMENURECIPE,
				'keterangan'	=> $rs->KETERANGAN,
				'jml'			=> $rs->JML,
				'satuan'		=> $rs->SATUAN,
				'jml2'			=> $rs->JML2,
				'satuan2'		=> $rs->SATUAN2,
				'konversi'		=> $rs->KONVERSI,
				'bhnbaku'		=> $rs->HARGA,
				'subtotal'		=> $rs->SUBTOTAL,
			);
		}
		echo json_encode(array(
			'success' => true,
			'detail' => $items,
		));
	break;
	case 'load_data_menu_resep_makanan' :
		$kode = $_POST['kode'];
		$sql   = "select b.*,c.konversi as konversi
				  from mmenurecipe a
				  inner join mmenurecipeselected b on a.kode=b.kodemenurecipe
				  left join msatuan c on c.kodesatuan = b.satuan
				  where a.kode = '$kode' and b.jenis='FOOD'
		          order by b.urutan";
		$query = $db->query($sql);
		$items = array();
		while ($rs = $db->fetch($query)) {
			$items[] = array(
				'kodemenurecipe'=> $rs->KODEMENURECIPEDTL,
				'keterangan'	=> $rs->KETERANGAN,
				'jml'			=> $rs->JML,
				'satuan'		=> $rs->SATUAN,
				'jml2'			=> $rs->JML2,
				'satuan2'		=> $rs->SATUAN2,
				'konversi'		=> $rs->KONVERSI,
				'bhnbaku'		=> $rs->HARGA,
				'subtotal'		=> $rs->SUBTOTAL,
			);
		}
		echo json_encode(array(
			'success' => true,
			'detail' => $items,
		));
	break;
	case 'load_data_menu_resep_minuman' :
		$kode = $_POST['kode'];
		$sql   = "select b.*,c.konversi as konversi
				  from mmenurecipe a
				  inner join mmenurecipeselected b on a.kode=b.kodemenurecipe
				  left join msatuan c on c.kodesatuan = b.satuan
				  where a.kode = '$kode' and b.jenis='DRINK'
		          order by b.urutan";
		$query = $db->query($sql);
		$items = array();
		while ($rs = $db->fetch($query)) {
			$items[] = array(
				'kodemenurecipe'=> $rs->KODEMENURECIPEDTL,
				'keterangan'	=> $rs->KETERANGAN,
				'jml'			=> $rs->JML,
				'satuan'		=> $rs->SATUAN,
				'jml2'			=> $rs->JML2,
				'satuan2'		=> $rs->SATUAN2,
				'konversi'		=> $rs->KONVERSI,
				'bhnbaku'		=> $rs->HARGA,
				'subtotal'		=> $rs->SUBTOTAL,
			);
		}
		echo json_encode(array(
			'success' => true,
			'detail' => $items,
		));
	break;
	
	case 'simpan_menu_resep' :
		$a_detail  = json_decode($_POST['data_detail']);
		$a_detail_makanan  = json_decode($_POST['data_detail_makanan']);
		$a_detail_minuman  = json_decode($_POST['data_detail_minuman']);

		$kode    	 = $_POST['KODE'];
		$nama 		 = $_POST['NAMA'];
		$status 	 = $_POST['STATUS'];
		
		
		if (count($a_detail)<1) die(json_encode(array('errorMsg' => 'Detail Transaksi Tidak Boleh Kosong')));
		
		$a_msg = array();
		
		if ($_FILES["FILEGAMBAR"]['name'] != '') {
			// upload gambar
			$target_dir = "../../gambar-menu/";
			$uploadOk = 1;
			$imageFileType = pathinfo($_FILES['FILEGAMBAR']['name'], PATHINFO_EXTENSION);
			$target_file = $target_dir . str_replace('/', '.', $kode) . '.' . $imageFileType;
			$gambar = str_replace('/', '.', $kode) . '.' . $imageFileType;
			// Check if image file is a actual image or fake image

			$check = getimagesize($_FILES["FILEGAMBAR"]["tmp_name"]);
			if ($check !== false) {
				$uploadOk = 1;
			} else {
				die(json_encode(array('errorMsg' => 'File yang Diupload bukan gambar')));
				$uploadOk = 0;
			}

			if ($_POST['GAMBAR'] != '') {
				unlink($target_dir.$_POST['GAMBAR']);
			}
			// Check if file already exists
			if (file_exists($target_file)) {
				//$uploadOk = 0;
				unlink($target_file);
			}
			// Check file size
			if ($_FILES["FILEGAMBAR"]["size"] > 500000) {
				die(json_encode(array('errorMsg' => 'Sorry, your file is too large.')));

				$uploadOk = 0;
			}
			// Allow certain file formats
			if ( ! in_array(strtolower($imageFileType), array('jpg', 'png', 'jpeg', 'gif')) ) {
				die(json_encode(array('errorMsg' => 'Sorry, only JPG, JPEG, PNG & GIF files are allowed.')));

				$uploadOk = 0;
			}
			// Check if $uploadOk is set to 0 by an error
			//if ($uploadOk == 0) {
				//die(json_encode(array('errorMsg' => 'Sorry, your file was not uploaded.')));
			// if everything is ok, try to upload file
			//} else {
				if (move_uploaded_file($_FILES["FILEGAMBAR"]["tmp_name"], $target_file)) {
					//echo "The file ". basename($_FILES["GAMBAR"]["name"]). " has been uploaded.";
				} else {
					die(json_encode(array('errorMsg' => 'Sorry, there was an error uploading your file.')));
				}
			//}
		}
		
		$mode = $_POST['mode'];
		
		// start transaction
		$tr = $db->start_trans();
		if($_POST['act']=="edit" && $_POST['GAMBAR']!="")$gambar=$_POST['GAMBAR'];
		// query header
		$data_values = array (
			$kode,$nama,$_POST['NAMAFRONT'],$_POST['NAMAPRINTER'],$_POST['PAKET'],
			$_POST['PPN'],$_POST['KODEBRAND'],$_POST['KODEDEPARTEMEN'],$_POST['KODEKATEGORI'],$_POST['KODEPERKIRAAN'],
			$_POST['SATUAN'],$_POST['TOTAL'],$_POST['STANDARDCOST'],$_POST['GRANDTOTAL'],
			$_POST['HARGAJUAL1'],$_POST['HARGAJUAL2'],$_POST['HARGAJUAL3'],
			$_POST['BIAYA1'],$_POST['BIAYA2'],$_POST['BIAYA3'],
			$_POST['PERALATAN'],$_POST['PENANGANAN'],$_POST['CARAMASAK'],$_POST['CARASAJI'],
			$gambar,$_POST['MAXSELECTEDMAKANAN'],$_POST['MAXSELECTEDMINUMAN'],$_POST['KODEPRINTER'],$_POST['PRINT'],
			$_POST['PRINTCHECKER'],$_POST['GELATO'],$_POST['URUTAN'],$_POST['NAMAWARNA'],
			$_SESSION['user'],date("Y-m-d"), $status
		);
		$exe = $db->insert('mmenurecipe', $data_values, $tr);
		if (!$exe) {
			$db->rollback($tr);
			die(json_encode(array('errorMsg' => 'Simpan Data Gagal <br>Kesalahan Pada Header Data Transaksi')));
		}

		$jmlSP = 0;

		// query detail
		$i = 0;
		$sql = $db->insert('mmenurecipedtl', 13, $tr, true);
		$pr  = $db->prepare($sql, $tr);
		foreach ($a_detail as $item) {
			$data_values = array (
				$kode,$_POST['KODEBRAND'],$item->kodebrg,$item->koderecipe,$item->kodemenurecipe,
				$i,$item->keterangan,$item->jml,$item->satuan,$item->jml2,
				$item->satuan2,$item->bhnbaku,$item->subtotal
			);
			$exe = $db->execute($pr, $data_values);
			if (!$exe) { $db->rollback($tr); die(json_encode(array('errorMsg' => 'Simpan Data Gagal <br>Kesalahan Pada Detail Data Transaksi'))); }
			$i++;
		}
		// query detail makanan
		$i = 0;
		$sql = $db->insert('mmenurecipeselected', 12, $tr, true);
		$pr  = $db->prepare($sql, $tr);
		foreach ($a_detail_makanan as $item) {
			$data_values = array (
				$kode,$_POST['KODEBRAND'],'FOOD',$item->kodemenurecipe,
				$i,$item->keterangan,$item->jml,$item->satuan,$item->jml2,
				$item->satuan2,$item->bhnbaku,$item->subtotal
			);
			$exe = $db->execute($pr, $data_values);
			if (!$exe) { $db->rollback($tr); die(json_encode(array('errorMsg' => 'Simpan Data Gagal <br>Kesalahan Pada Detail Data Transaksi'))); }
			$i++;
		}
		// query detail minuman
		$i = 0;
		$sql = $db->insert('mmenurecipeselected', 12, $tr, true);
		$pr  = $db->prepare($sql, $tr);
		foreach ($a_detail_minuman as $item) {
			$data_values = array (
				$kode,$_POST['KODEBRAND'],'DRINK',$item->kodemenurecipe,
				$i,$item->keterangan,$item->jml,$item->satuan,$item->jml2,
				$item->satuan2,$item->bhnbaku,$item->subtotal
			);
			$exe = $db->execute($pr, $data_values);
			if (!$exe) { $db->rollback($tr); die(json_encode(array('errorMsg' => 'Simpan Data Gagal <br>Kesalahan Pada Detail Data Transaksi'))); }
			$i++;
		}
		$db->commit($tr);

		// panggil fungsi untuk log history
		log_history(
			$kode,
			'MENU RESEP',
			$_POST['KODE']=='' ? 'INSERT' : 'EDIT',
			array(
				array(
					'nama' => 'header',
					'tabel' => 'mmenurecipe',
					'kode' => 'KODE'
				),
				array(
					'nama' => 'detail',
					'tabel' => 'mmenurecipedtl',
					'kode' => 'KODE'
				),
			),
			$_SESSION['user']
		);

		echo json_encode(array('success' => true, 'kode' => $kode));
	break;
	//========================= END MASTER DATA MENU RESEP===========================
	//========================= MASTER DATA KATEGORI SUPPLIER ===========================
	case 'kategori_supplier' :
		if ($act=='insert' || $act=='edit'){
			$kode   = $_POST['KODEKATEGORISUPPLIER'];
			$nama   = $_POST['NAMAKATEGORISUPPLIER'];
			$status = $_POST['STATUS'];

			if ($kode=='') {
				$temp_kode = 'K';
				$kode = $temp_kode.get_max_urutan('mkategorisupplier', 'kodekategorisupplier', $temp_kode, 3);
			}

			if ($kode == '') die(json_encode(array('errorMsg' => 'ID Kategori Supplier Tidak Boleh Kosong')));
			if ($nama == '') die(json_encode(array('errorMsg' => 'Deskripsi Kategori Supplier Tidak Boleh Kosong')));

			//CEK APAKAH KODE DAN NAMA SUDAH DIGUNAKAN
			if ($act=='insert') {
				$p = $db->prepare('select kodekategorisupplier, namakategorisupplier from mkategorisupplier where kodekategorisupplier=?');
				$q = $db->execute($p, $kode);
				$r = $db->fetch($q);
				if ($r->KODEKATEGORISUPPLIER!='') {
					die(json_encode(array('errorMsg' => 'Kode Kategori Supplier Sudah Digunakan Oleh Kategori Supplier ('.$r->KODEKATEGORISUPPLIER.') '.$r->NAMAKATEGORISUPPLIER.', Kode Tidak Dapat Digunakan')));
				}

				$p = $db->prepare('select kodekategorisupplier, namakategorisupplier from mkategorisupplier where namakategorisupplier=?');
				$q = $db->execute($p, $nama);
				$r = $db->fetch($q);
				if ($r->KODEKATEGORISUPPLIER!='') {
					die(json_encode(array('errorMsg' => 'Nama Kategori Supplier Sudah Digunakan Oleh Kategori Supplier ('.$r->KODEKATEGORISUPPLIER.') '.$r->NAMAKATEGORISUPPLIER.', Nama Tidak Dapat Digunakan')));
				}
				$status = 1;
			} else if ($act=='edit') {
				$p = $db->prepare('select kodekategorisupplier, namakategorisupplier from mkategorisupplier where kodekategorisupplier<>? and namakategorisupplier=?');
				$q = $db->execute($p, array($kode, $nama));
				$r = $db->fetch($q);
				if ($r->NAMAKATEGORISUPPLIER!='') {
					die(json_encode(array('errorMsg' => 'Deskripsi Kategori Supplier Sudah Digunakan Oleh Kategori Supplier ('.$r->KODEKATEGORISUPPLIER.') '.$r->NAMAKATEGORISUPPLIER.', Deskripsi Tidak Dapat Digunakan')));
				}
			}

			// start transaction
			$tr = $db->start_trans();

			$data_values = array (
				$kode, $nama, $_SESSION['user'], date('Y-m-d'),$status
			);
			$query = $db->insert('mkategorisupplier', $data_values, $tr);
			if (!$query) {
				// rollback transaction
				$db->rollback($tr);
				die(json_encode(array('errorMsg' => 'Simpan Data Gagal')));
			}

			// commit transaction
			$db->commit($tr);

			// panggil fungsi untuk log history
			log_history(
				$kode,
				'MASTER KATEGORI SUPPLIER',
				$act,
				array(
					array(
						'nama'  => 'header',
						'tabel' => 'mkategorisupplier',
						'kode'  => 'kodekategorisupplier'
					),
				),
				$_SESSION['user']
			);

			echo json_encode(array('success' => true));
		} else if ($act=='delete'){
			$kode = $_POST['kodekategorisupplier'];

			/*$p = $db->prepare('select first 1 skip 0 kodedepartemenmenu as kode from mdepartemenmenu where kodedepartemenmenu=?');
			$q = $db->execute($p, $kode);
			$r = $db->fetch($q);

			if ($r->KODE<>'') { die(json_encode(array('errorMsg'=>'Data Kategori Barang Tidak Dapat Dihapus, Data Sudah Digunakan Pada Transaksi'))); }
			*/
			$q = $db->delete('mkategorisupplier', array('kodekategorisupplier' => $kode));

			if ($q) {

				// panggil fungsi untuk log history
				log_history(
					$kode,
					'MASTER KATEGORI SUPPLIER',
					$act,
					array(
						array(
							'nama'  => 'header',
							'tabel' => 'mkategorisupplier',
							'kode'  => 'kodekategorisupplier'
						),
					),
					$_SESSION['user']
				);

				echo json_encode(array('success'=>true));
			} else {
				echo json_encode(array('errorMsg'=>'Terdapat Data Error, Data Tidak Dapat Dihapus'));
			}
		}
	break;
	//========================= END MASTER DATA KATEGORI SUPPLIER ===========================
	//========================= MASTER DATA JENIS BARANG ===========================
	case 'jenis_barang' :
		if ($act=='insert' || $act=='edit'){
			$kode   = $_POST['KODEJENISBARANG'];
			$nama   = $_POST['NAMAJENISBARANG'];
			$status = $_POST['STATUS'];

			if ($kode=='') {
				$temp_kode = 'K';
				$kode = $temp_kode.get_max_urutan('mjenisbarang', 'kodejenisbarang', $temp_kode, 3);
			}

			if ($kode == '') die(json_encode(array('errorMsg' => 'Kode Jenis Barang Tidak Boleh Kosong')));
			if ($nama == '') die(json_encode(array('errorMsg' => 'Nama Jenis Barang Tidak Boleh Kosong')));

			//CEK APAKAH KODE DAN NAMA SUDAH DIGUNAKAN
			if ($act=='insert') {
				$p = $db->prepare('select kodejenisbarang, namajenisbarang from mjenisbarang where kodejenisbarang=?');
				$q = $db->execute($p, $kode);
				$r = $db->fetch($q);
				if ($r->KODEJENISBARANG!='') {
					die(json_encode(array('errorMsg' => 'Kode Jenis Barang Sudah Digunakan Oleh Jenis Barang ('.$r->KODEJENISBARANG.') '.$r->NAMAJENISBARANG.', ID Tidak Dapat Digunakan')));
				}

				$p = $db->prepare('select kodejenisbarang, namajenisbarang from mjenisbarang where namajenisbarang=?');
				$q = $db->execute($p, $nama);
				$r = $db->fetch($q);
				if ($r->NAMAJENISBARANG!='') {
					die(json_encode(array('errorMsg' => 'Nama Jenis Barang Sudah Digunakan Oleh Jenis Barang ('.$r->KODEJENISBARANG.') '.$r->NAMAJENISBARANG.', Deskripsi Tidak Dapat Digunakan')));
				}
				$status = 1;
			} else if ($act=='edit') {
				$p = $db->prepare('select kodejenisbarang, namajenisbarang from mjenisbarang where kodejenisbarang<>? and namajenisbarang=?');
				$q = $db->execute($p, array($kode, $nama));
				$r = $db->fetch($q);
				if ($r->NAMAJENISBARANG!='') {
					die(json_encode(array('errorMsg' => 'Nama Jenis Barang Sudah Digunakan Oleh Jenis Barang ('.$r->KODEJENISBARANG.') '.$r->NAMAJENISBARANG.', Deskripsi Tidak Dapat Digunakan')));
				}
			}

			// start transaction
			$tr = $db->start_trans();

			$data_values = array (
				$kode, $nama, $_SESSION['user'], date("Y-m-d"), $status
			);
			$query = $db->insert('mjenisbarang', $data_values, $tr);
			if (!$query) {
				// rollback transaction
				$db->rollback($tr);
				die(json_encode(array('errorMsg' => 'Simpan Data Gagal')));
			}

			// commit transaction
			$db->commit($tr);

			// panggil fungsi untuk log history
			log_history(
				$kode,
				'MASTER JENIS BARANG',
				$act,
				array(
					array(
						'nama'  => 'header',
						'tabel' => 'mjenisbarang',
						'kode'  => 'kodejenisbarang'
					),
				),
				$_SESSION['user']
			);

			echo json_encode(array('success' => true));
		} else if ($act=='delete'){
			$kode = $_POST['kodejenisbarang'];

			//$p = $db->prepare('select first 1 skip 0 kodekategorim as kode from mbarang where kodekategoribarang=?');
			//$q = $db->execute($p, $kode);
			//$r = $db->fetch($q);

			//if ($r->KODE<>'') { die(json_encode(array('errorMsg'=>'Data Kategori Barang Tidak Dapat Dihapus, Data Sudah Digunakan Pada Transaksi'))); }

			$q = $db->delete('mjenisbarang', array('kodejenisbarang' => $kode));

			if ($q) {

				// panggil fungsi untuk log history
				log_history(
					$kode,
					'MASTER JENIS BARANG',
					$act,
					array(
						array(
							'nama'  => 'header',
							'tabel' => 'mjenisbarang',
							'kode'  => 'kodejenisbarang'
						),
					),
					$_SESSION['user']
				);

				echo json_encode(array('success'=>true));
			} else {
				echo json_encode(array('errorMsg'=>$kode));
			}
		}
	break;
	//========================= END MASTER DATA JENIS BARANG ===========================
	

	//=========================MASTER DATA MODIFIER===========================
	case 'modifier' :
		if ($act=='delete'){
			$kode   = $_POST['kode'];
			//$p = $db->prepare('select first 1 skip 0 kodekategorim as kode from mbarang where kodekategoribarang=?');
			//$q = $db->execute($p, $kode);
			//$r = $db->fetch($q);

			//if ($r->KODE<>'') { die(json_encode(array('errorMsg'=>'Data Kategori Barang Tidak Dapat Dihapus, Data Sudah Digunakan Pada Transaksi'))); }

			$q = $db->delete('mmodifier', array('kode' => $kode));

			if ($q) {

				// panggil fungsi untuk log history
				log_history(
					$kode,
					'MASTER MODIFIER',
					$act,
					array(
						array(
							'nama'  => 'header',
							'tabel' => 'mmodifier',
							'kode'  => 'kode'
						),
					),
					$_SESSION['user']
				);

				echo json_encode(array('success'=>true));
			} else {
				echo json_encode(array('errorMsg'=>$kode));
			}
		}
	break;
	
	case 'load_data_modifier' :
		$kode = $_POST['kode'];
		$brand = $_POST['brand'];
		$sql   = "select c.kode,c.nama
				  from mmodifier a
				  inner join mmodifierdtl b on a.kode=b.kode
				  inner join mbarang c on c.kode = b.kodebrg
				  where a.kode = '$kode' and a.kodebrand = '$brand'";
		$query = $db->query($sql);
		$items = array();
		while ($rs = $db->fetch($query)) {
			$items[] = array(
				'kodebrg'=> $rs->KODE,
				'keterangan'=> $rs->NAMA				
			);
		}
		echo json_encode(array(
			'success' => true,
			'detail' => $items,
		));
	break;
	
	case 'simpan_modifier' :

		$kode    	 = $_POST['KODE'];
		$nama 		 = $_POST['NAMA'];
		$status 	 = $_POST['STATUS'];
		
		
		if ($_POST['JENIS']==0 &&count($a_detail)<1) die(json_encode(array('errorMsg' => 'Detail Transaksi Tidak Boleh Kosong')));
		
		$a_msg = array();

		$mode = $_POST[mode];
		// start transaction
		$tr = $db->start_trans();

		// query header
		$data_values = array (
			$_POST['KODEBRAND'],$kode,$nama,$_POST['NAMAFRONT'],$_POST['NAMAPRINTER'],
			$_POST['NAMAWARNA'],$_POST['KODEPRINTER'],$_POST['JENIS'],
			$_SESSION['user'],date("Y-m-d"),$status
		);
		$exe = $db->insert('mmodifier', $data_values, $tr);
		if (!$exe) {
			$db->rollback($tr);
			die(json_encode(array('errorMsg' => 'Simpan Data Gagal <br>Kesalahan Pada Header Data Transaksi')));
		}
		
		// query detail
		$a_detail  = json_decode($_POST['data_detail']);
		$i = 0;
		$sql = $db->insert('mmodifierdtl', 3, $tr, true);
		$pr  = $db->prepare($sql, $tr);
		foreach ($a_detail as $item) {
			cek_valid_data("mbarang","kode",$item->kodebrg,"Kode Barang");
			$data_values = array (
				$_POST['KODEBRAND'],$kode,$item->kodebrg
			);
			$exe = $db->execute($pr, $data_values);
			if (!$exe) { $db->rollback($tr); die(json_encode(array('errorMsg' => 'Simpan Data Gagal <br>Kesalahan Pada Detail Data Transaksi'))); }
			$i++;
		}
		$db->commit($tr);

		// panggil fungsi untuk log history
		log_history(
			$kode,
			'MODIFIER',
			$_POST['KODE']=='' ? 'INSERT' : 'EDIT',
			array(
				array(
					'nama' => 'header',
					'tabel' => 'mmodifier',
					'kode' => 'KODE'
				),
				array(
					'nama' => 'detail',
					'tabel' => 'mmodifierdtl',
					'kode' => 'KODE'
				),
			),
			$_SESSION['user']
		);

		echo json_encode(array('success' => true, 'kode' => $kode));
	break;
	//========================= END MASTER DATA MODIFIER===========================
	
	//=========================MASTER DATA PRINTER & PRINTER FUNCTION===========================
	case 'printer_function' :
		if ($act=='delete'){
			$kode   = $_POST['kode'];
			//$p = $db->prepare('select first 1 skip 0 kodekategorim as kode from mbarang where kodekategoribarang=?');
			//$q = $db->execute($p, $kode);
			//$r = $db->fetch($q);

			//if ($r->KODE<>'') { die(json_encode(array('errorMsg'=>'Data Kategori Barang Tidak Dapat Dihapus, Data Sudah Digunakan Pada Transaksi'))); }

			$q = $db->delete('mprinter', array('kode' => $kode));

			if ($q) {

				// panggil fungsi untuk log history
				log_history(
					$kode,
					'MASTER PRINTER',
					$act,
					array(
						array(
							'nama'  => 'header',
							'tabel' => 'mprinter',
							'kode'  => 'kode'
						),
					),
					$_SESSION['user']
				);

				echo json_encode(array('success'=>true));
			} else {
				echo json_encode(array('errorMsg'=>$kode));
			}
		}
	break;
	
	case 'load_data_printer' :
		$sql   = "select *
				  from mprinter";
		$query = $db->query($sql);
		$items = array();
		while ($rs = $db->fetch($query)) {
			$items[] = array(
				'KODE'=> $rs->KODE,
				'NAMA'=> $rs->NAMA,
				'TIPEPRINTER'=> $rs->TIPEPRINTER,
				'NAMAPRINTER'=> $rs->NAMAPRINTER,
				'NAMAKOMPUTER'=> $rs->NAMAKOMPUTER,
				'USERENTRY'=> $rs->USERENTRY,
				'TANGGALENTRY'=> $rs->TANGGALENTRY,
				'STATUS'=> $rs->STATUS				
			);
		}
		echo json_encode(array(
			'success' => true,
			'detail' => $items,
		));
	break;
	case 'load_data_printer_function' :
		$sql   = "select a.*,b.namaprinter as NAMAPRINTER,
				  b.nama as NAMA,b.namakomputer as NAMAKOMPUTER
				  from mprinterfunction a
				  inner join mprinter b on b.kode = a.kode 
				  order by a.urutan";
		$query = $db->query($sql);
		$items = array();
		while ($rs = $db->fetch($query)) {
			$items[] = array(
				'KODE'=> $rs->KODE,
				'NAMAPRINTER'=> $rs->NAMAPRINTER,
				'NAMA'=> $rs->NAMA,
				'NAMAKOMPUTER'=> $rs->NAMAKOMPUTER,
				'URUTAN'=> $rs->URUTAN,
				'FUNGSI'=> $rs->FUNGSI,
				'JUMLAHPRINT'=> $rs->JUMLAHPRINT,
				'NAMAKOMPUTERPEMANGGIL'=> $rs->NAMAKOMPUTERPEMANGGIL,
				'USERENTRY'=> $rs->USERENTRY,
				'TANGGALENTRY'=> $rs->TANGGALENTRY				
			);
		}
		echo json_encode(array(
			'success' => true,
			'detail' => $items,
		));
	break;
	case 'cek_delete_printer' :
		$kode = $_POST['kode'];
		
		//cek di menu resep
		$p = $db->prepare('select first 1 skip 0 kode as kode from mmenurecipe where kodeprinter=?');
		$q = $db->execute($p, $kode);
		$r = $db->fetch($q);
		if ($r->KODE<>'') { die(json_encode(array('errorMsg'=>'Data Printer Tidak Dapat Dihapus, Data Sudah Digunakan Pada Menu Resep '.$rs->KODE))); }
		
		//cek di modifier
		$p = $db->prepare('select first 1 skip 0 kode as kode from mmodifier where kodeprinter=?');
		$q = $db->execute($p, $kode);
		$r = $db->fetch($q);
		if ($r->KODE<>'') { die(json_encode(array('errorMsg'=>'Data Printer Tidak Dapat Dihapus, Data Sudah Digunakan Pada Modifier '.$rs->KODE))); }
		
		echo json_encode(array(
			'success' => true,
		));
	break;
	case 'simpan_printer' :
		$a_detail  = json_decode($_POST['data_detail']);
		if (count($a_detail)<1) die(json_encode(array('errorMsg' => 'Detail Printer Tidak Boleh Kosong')));
		
		$a_msg = array();

		$mode = $_POST[mode];
		
		//cek awal kode
		$awal = "A";
		$p = $db->select('mprinter');
		$r = $db->fetch($p);
		if(substr($r->KODE,0,1)=="A"){
			$awal = "B";
		}
		
		//hapus semua isinya dulu
		$tr = $db->start_trans();
		$q = $db->delete('mprinter',array(),$tr);
		
		// query detail
		$i = 0;
		$sql = $db->insert('mprinter', 8, $tr, true);
		$pr  = $db->prepare($sql, $tr);
		foreach ($a_detail as $item) {
			$kode = $awal.str_pad(++$i,2,"0",STR_PAD_LEFT);
			$data_values = array (
				$kode,$item->NAMA,$item->TIPEPRINTER,$item->NAMAPRINTER,$item->NAMAKOMPUTER,
				$_SESSION['user'],date("Y-m-d"),$item->STATUS
			);
			$exe = $db->execute($pr, $data_values);
			
			$db->update("mmenurecipe",
						array('kodeprinter'=>$kode),
						array('kodeprinter'=>$item->KODE),$tr);
			
					
			$db->update("mmodifier",
						array('kodeprinter'=>$kode),
						array('kodeprinter'=>$item->KODE),$tr);
			if (!$exe) { $db->rollback($tr); die(json_encode(array('errorMsg' => 'Simpan Data Gagal <br>Kesalahan Pada Detail Data Transaksi'))); }
		
		}
		$db->commit($tr);
		// panggil fungsi untuk log history
		log_history(
			$kode,
			'PRINTER',
			$_POST['KODE']=='' ? 'INSERT' : 'EDIT',
			array(
				array(
					'nama' => 'header',
					'tabel' => 'mprinter',
					'kode' => 'KODE'
				),
			),
			$_SESSION['user']
		);

		echo json_encode(array('success' => true, 'kode' => $kode));
	break;
	case 'simpan_printer_function' :
		$a_detail  = json_decode($_POST['data_detail']);
		if (count($a_detail)<1) die(json_encode(array('errorMsg' => 'Detail Fungsi Printer Tidak Boleh Kosong')));
		
		$a_msg = array();

		$mode = $_POST[mode];
		
		
		// start transaction 
		//hapus semua isinya dulu
		$tr = $db->start_trans();
		$q = $db->delete('mprinterfunction',array(),$tr);
		$db->commit($tr);
		
		// start transaction
		$tr = $db->start_trans();
		// query detail
		$i = 0;
		$sql = $db->insert('mprinterfunction', 7, $tr, true);
		$pr  = $db->prepare($sql, $tr);
		foreach ($a_detail as $item) {
			$i++;
			$data_values = array (
				$item->KODE,$i,$item->FUNGSI,$item->JUMLAHPRINT,$item->NAMAKOMPUTERPEMANGGIL,
				$_SESSION['user'],date("Y-m-d")
			);
			$exe = $db->execute($pr, $data_values);
			if (!$exe) { $db->rollback($tr); die(json_encode(array('errorMsg' => 'Simpan Data Gagal <br>Kesalahan Pada Detail Data Transaksi'))); }
			
		}
		$db->commit($tr);
		
		$kode = "";
		// panggil fungsi untuk log history
		log_history(
			$kode,
			'PRINTER FUNCTION',
			$_POST['KODE']=='' ? 'INSERT' : 'EDIT',
			array(
				array(
					'nama' => 'header',
					'tabel' => 'mprinterfunction',
					'kode' => 'KODE'
				),
			),
			$_SESSION['user']
		);

		echo json_encode(array('success' => true));
	break;
	//========================= END MASTER DATA PRINTER FUNCTION===========================
	//========================= MASTER DATA KATEGORI SUPPLIER ===========================
	case 'kategori_supplier' :
		if ($act=='insert' || $act=='edit'){
			$kode   = $_POST['KODEKATEGORISUPPLIER'];
			$nama   = $_POST['NAMAKATEGORISUPPLIER'];
			$status = $_POST['STATUS'];

			if ($kode=='') {
				$temp_kode = 'K';
				$kode = $temp_kode.get_max_urutan('mkategorisupplier', 'kodekategorisupplier', $temp_kode, 3);
			}

			if ($kode == '') die(json_encode(array('errorMsg' => 'ID Kategori Supplier Tidak Boleh Kosong')));
			if ($nama == '') die(json_encode(array('errorMsg' => 'Deskripsi Kategori Supplier Tidak Boleh Kosong')));

			//CEK APAKAH KODE DAN NAMA SUDAH DIGUNAKAN
			if ($act=='insert') {
				$p = $db->prepare('select kodekategorisupplier, namakategorisupplier from mkategorisupplier where kodekategorisupplier=?');
				$q = $db->execute($p, $kode);
				$r = $db->fetch($q);
				if ($r->KODEKATEGORISUPPLIER!='') {
					die(json_encode(array('errorMsg' => 'Kode Kategori Supplier Sudah Digunakan Oleh Kategori Supplier ('.$r->KODEKATEGORISUPPLIER.') '.$r->NAMAKATEGORISUPPLIER.', Kode Tidak Dapat Digunakan')));
				}

				$p = $db->prepare('select kodekategorisupplier, namakategorisupplier from mkategorisupplier where namakategorisupplier=?');
				$q = $db->execute($p, $nama);
				$r = $db->fetch($q);
				if ($r->KODEKATEGORISUPPLIER!='') {
					die(json_encode(array('errorMsg' => 'Nama Kategori Supplier Sudah Digunakan Oleh Kategori Supplier ('.$r->KODEKATEGORISUPPLIER.') '.$r->NAMAKATEGORISUPPLIER.', Nama Tidak Dapat Digunakan')));
				}
				$status = 1;
			} else if ($act=='edit') {
				$p = $db->prepare('select kodekategorisupplier, namakategorisupplier from mkategorisupplier where kodekategorisupplier<>? and namakategorisupplier=?');
				$q = $db->execute($p, array($kode, $nama));
				$r = $db->fetch($q);
				if ($r->NAMAKATEGORISUPPLIER!='') {
					die(json_encode(array('errorMsg' => 'Deskripsi Kategori Supplier Sudah Digunakan Oleh Kategori Supplier ('.$r->KODEKATEGORISUPPLIER.') '.$r->NAMAKATEGORISUPPLIER.', Deskripsi Tidak Dapat Digunakan')));
				}
			}

			// start transaction
			$tr = $db->start_trans();

			$data_values = array (
				$kode, $nama, $_SESSION['user'], date('Y-m-d'),$status
			);
			$query = $db->insert('mkategorisupplier', $data_values, $tr);
			if (!$query) {
				// rollback transaction
				$db->rollback($tr);
				die(json_encode(array('errorMsg' => 'Simpan Data Gagal')));
			}

			// commit transaction
			$db->commit($tr);

			// panggil fungsi untuk log history
			log_history(
				$kode,
				'MASTER KATEGORI SUPPLIER',
				$act,
				array(
					array(
						'nama'  => 'header',
						'tabel' => 'mkategorisupplier',
						'kode'  => 'kodekategorisupplier'
					),
				),
				$_SESSION['user']
			);

			echo json_encode(array('success' => true));
		} else if ($act=='delete'){
			$kode = $_POST['kodekategorisupplier'];

			/*$p = $db->prepare('select first 1 skip 0 kodedepartemenmenu as kode from mdepartemenmenu where kodedepartemenmenu=?');
			$q = $db->execute($p, $kode);
			$r = $db->fetch($q);

			if ($r->KODE<>'') { die(json_encode(array('errorMsg'=>'Data Kategori Barang Tidak Dapat Dihapus, Data Sudah Digunakan Pada Transaksi'))); }
			*/
			$q = $db->delete('mkategorisupplier', array('kodekategorisupplier' => $kode));

			if ($q) {

				// panggil fungsi untuk log history
				log_history(
					$kode,
					'MASTER KATEGORI SUPPLIER',
					$act,
					array(
						array(
							'nama'  => 'header',
							'tabel' => 'mkategorisupplier',
							'kode'  => 'kodekategorisupplier'
						),
					),
					$_SESSION['user']
				);

				echo json_encode(array('success'=>true));
			} else {
				echo json_encode(array('errorMsg'=>'Terdapat Data Error, Data Tidak Dapat Dihapus'));
			}
		}
	break;
	//========================= END MASTER DATA KATEGORI SUPPLIER ===========================
	//========================= MASTER DATA JENIS BARANG ===========================
	case 'jenis_barang' :
		if ($act=='insert' || $act=='edit'){
			$kode   = $_POST['KODEJENISBARANG'];
			$nama   = $_POST['NAMAJENISBARANG'];
			$status = $_POST['STATUS'];

			if ($kode=='') {
				$temp_kode = 'K';
				$kode = $temp_kode.get_max_urutan('mjenisbarang', 'kodejenisbarang', $temp_kode, 3);
			}

			if ($kode == '') die(json_encode(array('errorMsg' => 'Kode Jenis Barang Tidak Boleh Kosong')));
			if ($nama == '') die(json_encode(array('errorMsg' => 'Nama Jenis Barang Tidak Boleh Kosong')));

			//CEK APAKAH KODE DAN NAMA SUDAH DIGUNAKAN
			if ($act=='insert') {
				$p = $db->prepare('select kodejenisbarang, namajenisbarang from mjenisbarang where kodejenisbarang=?');
				$q = $db->execute($p, $kode);
				$r = $db->fetch($q);
				if ($r->KODEJENISBARANG!='') {
					die(json_encode(array('errorMsg' => 'Kode Jenis Barang Sudah Digunakan Oleh Jenis Barang ('.$r->KODEJENISBARANG.') '.$r->NAMAJENISBARANG.', ID Tidak Dapat Digunakan')));
				}

				$p = $db->prepare('select kodejenisbarang, namajenisbarang from mjenisbarang where namajenisbarang=?');
				$q = $db->execute($p, $nama);
				$r = $db->fetch($q);
				if ($r->NAMAJENISBARANG!='') {
					die(json_encode(array('errorMsg' => 'Nama Jenis Barang Sudah Digunakan Oleh Jenis Barang ('.$r->KODEJENISBARANG.') '.$r->NAMAJENISBARANG.', Deskripsi Tidak Dapat Digunakan')));
				}
				$status = 1;
			} else if ($act=='edit') {
				$p = $db->prepare('select kodejenisbarang, namajenisbarang from mjenisbarang where kodejenisbarang<>? and namajenisbarang=?');
				$q = $db->execute($p, array($kode, $nama));
				$r = $db->fetch($q);
				if ($r->NAMAJENISBARANG!='') {
					die(json_encode(array('errorMsg' => 'Nama Jenis Barang Sudah Digunakan Oleh Jenis Barang ('.$r->KODEJENISBARANG.') '.$r->NAMAJENISBARANG.', Deskripsi Tidak Dapat Digunakan')));
				}
			}

			// start transaction
			$tr = $db->start_trans();

			$data_values = array (
				$kode, $nama, $_SESSION['user'], date("Y-m-d"), $status
			);
			$query = $db->insert('mjenisbarang', $data_values, $tr);
			if (!$query) {
				// rollback transaction
				$db->rollback($tr);
				die(json_encode(array('errorMsg' => 'Simpan Data Gagal')));
			}

			// commit transaction
			$db->commit($tr);

			// panggil fungsi untuk log history
			log_history(
				$kode,
				'MASTER JENIS BARANG',
				$act,
				array(
					array(
						'nama'  => 'header',
						'tabel' => 'mjenisbarang',
						'kode'  => 'kodejenisbarang'
					),
				),
				$_SESSION['user']
			);

			echo json_encode(array('success' => true));
		} else if ($act=='delete'){
			$kode = $_POST['kodejenisbarang'];

			//$p = $db->prepare('select first 1 skip 0 kodekategorim as kode from mbarang where kodekategoribarang=?');
			//$q = $db->execute($p, $kode);
			//$r = $db->fetch($q);

			//if ($r->KODE<>'') { die(json_encode(array('errorMsg'=>'Data Kategori Barang Tidak Dapat Dihapus, Data Sudah Digunakan Pada Transaksi'))); }

			$q = $db->delete('mjenisbarang', array('kodejenisbarang' => $kode));

			if ($q) {

				// panggil fungsi untuk log history
				log_history(
					$kode,
					'MASTER JENIS BARANG',
					$act,
					array(
						array(
							'nama'  => 'header',
							'tabel' => 'mjenisbarang',
							'kode'  => 'kodejenisbarang'
						),
					),
					$_SESSION['user']
				);

				echo json_encode(array('success'=>true));
			} else {
				echo json_encode(array('errorMsg'=>$kode));
			}
		}
	break;
	//========================= END MASTER DATA JENIS BARANG ===========================
	
	//========================= MASTER DATA SUPPLIER ===========================
	case 'supplier' :
		if ($act=='insert' || $act=='edit') {
			$kode	= $_POST['KODESUPPLIER'];
			$nama	= $_POST['NAMASUPPLIER'];
			$kategori = $_POST['KODEKATEGORISUPPLIER'];
			$status	= isset($_POST['STATUS']) ? $_POST['STATUS'] : 0;

			if ($_POST['NAMASUPPLIER'] == '') die(json_encode(array('errorMsg' => 'Company Name Tidak Boleh Kosong')));

			if ($kode=='') {
				$tgl = date("Y.m.d");

				/*$temp_kode = 'SUP/'.$_SESSION['KODELOKASI'].'/';

				$urutan = get_new_urutan('msupplier', 'kodesupplier', array($temp_kode, substr($tgl, 2, 2)));

				$kode = $temp_kode.$urutan.'/'.substr($tgl, 5, 2).'/'.substr($tgl, 2, 2);*/

				$temp_kode = 'SUPP/'.$_SESSION['KODELOKASI'].'/'.substr($tgl, 2, 2).substr($tgl, 5, 2);

				$urutan = get_max_urutan('msupplier', 'kodesupplier', $temp_kode, 4);

				$kode = $temp_kode.$urutan;
			}

			//CEK APAKAH KODE SUDAH DIGUNAKAN
			if ($act=='insert') {
				$p = $db->prepare('select kodesupplier, namasupplier from msupplier where kodesupplier=?');
				$q = $db->execute($p, $kode);
				$r = $db->fetch($q);
				if ($r->KODESUPPLIER!='') {
					die(json_encode(array('errorMsg' => 'Kode Supplier Sudah Digunakan Oleh Supplier ('.$r->KODESUPPLIER.') '.$r->NAMASUPPLIER.', Kode Tidak Dapat Digunakan')));
				}
				$p = $db->prepare('select kodesupplier, namasupplier from msupplier where namasupplier=?');
				$q = $db->execute($p, $nama);
				$r = $db->fetch($q);
				if ($r->NAMASUPPLIER!='') {
					die(json_encode(array('errorMsg' => 'Nama Supplier Sudah Digunakan Oleh Supplier ('.$r->KODESUPPLIER.') '.$r->NAMASUPPLIER.', Nama Tidak Dapat Digunakan')));
				}
				$status = 1;
			} else if ($act=='edit') {
				$p = $db->prepare('select kodesupplier, namasupplier from msupplier where kodesupplier<>? and namasupplier=?');
				$q = $db->execute($p, array($kode, $nama));
				$r = $db->fetch($q);
				if ($r->NAMASUPPLIER!='') {
					die(json_encode(array('errorMsg' => 'Nama Supplier Sudah Digunakan Oleh Supplier ('.$r->KODESUPPLIER.') '.$r->NAMASUPPLIER.', Nama Tidak Dapat Digunakan')));
				}
			}
			$tr = $db->start_trans();

			$data_values = array (
				$kode, $_POST['NAMASUPPLIER'], $kategori, $_POST['ALAMAT'], $_POST['KOTA'],
				$_POST['PROPINSI'], $_POST['NEGARA'], $_POST['KODEPOS'], $_POST['TELP'],
				$_POST['FAX'], $_POST['EMAIL'], $_POST['WEBSITE'],
				$_POST['CONTACTPERSON'], $_POST['TELPCP'], $_POST['EMAILCP'], $_POST['NPWP'],
				$_POST['KODESYARATBAYAR'], $_POST['NAMABANK'], $_POST['NOREKENING'], $_POST['NAMABENEFICIARY'],
				$_POST['SWIFTCODE'], $_POST['ALAMATBANK'], $_POST['NOMORROUTING'],
				$_POST['NEGARABANK'], $_POST['REMARK'], $_SESSION['user'], date("Y-m-d"),
				$status
			);
			$query = $db->insert('msupplier', $data_values, $tr);
			if (!$query) {
				$db->rollback($tr);
				die(json_encode(array('errorMsg' => 'Simpan Data Gagal')));
			}

			$db->commit($tr);

			// panggil fungsi untuk log history
			log_history(
				$kode,
				'MASTER SUPPLIER',
				$act,
				array(
					array(
						'nama' => 'header',
						'tabel' => 'msupplier',
						'kode' => 'kodesupplier'
					),
				),
				$_SESSION['user']
			);

			echo json_encode(array('success' => true));
		} else if ($act=='delete') {
			$kode = $_POST['id'];

			$p = $db->prepare('select first 1 skip 0 kodesupplier as kode from kartuhutang where kodesupplier=?');
			$q = $db->execute($p, $kode);
			$r = $db->fetch($q);

			if ($r->KODE<>'') { die(json_encode(array('errorMsg'=>'Data Supplier Tidak Dihapus, Data Sudah Digunakan Pada Transaksi'))); }

			$q = $db->delete('msupplier', array('kodesupplier' => $kode));

			if ($q) {

				// panggil fungsi untuk log history
				log_history(
					$kode,
					'MASTER SUPPLIER',
					$act,
					array(
						array(
							'nama' => 'header',
							'tabel' => 'msupplier',
							'kode' => 'kodesupplier'
						),
					),
					$_SESSION['user']
				);

				echo json_encode(array('success'=>true));
			} else {
				echo json_encode(array('errorMsg'=>'Terdapat Data Error, Data Tidak Dapat Dihapus'));
			}
		}
	break;
	//========================= END MASTER DATA SUPPLIER ===========================
	//========================= MASTER DATA SYARAT BAYAR ===========================
	case 'syarat_bayar' :
		if ($act=='insert' || $act=='edit') {
			$kode	       = $_POST['KODESYARATBAYAR'];
			$nama	       = $_POST['NAMASYARATBAYAR'];
			$status	= isset($_POST['STATUS']) ? $_POST['STATUS'] : 0;

			//if ($_POST['KODESYARATBAYAR'] == '') die(json_encode(array('errorMsg' => 'Syarat Bayar Tidak Boleh Kosong')));
			if ($_POST['NAMASYARATBAYAR'] == '') die(json_encode(array('errorMsg' => 'Deskripsi Syarat Bayar Tidak Boleh Kosong')));

			if ($kode=='') {
				$temp_kode = '';//'B'.substr($nama, 0, 1);
				$kode = $temp_kode.get_max_urutan('msyaratbayar', 'kodesyaratbayar', $temp_kode, 2);
			}

			//CEK APAKAH KODE DAN NAMA SUDAH DIGUNAKAN
			if ($act=='insert'){
				$p = $db->prepare('select kodesyaratbayar, namasyaratbayar from msyaratbayar where kodesyaratbayar=?');
				$q = $db->execute($p, $kode);
				$r = $db->fetch($q);
				if ($r->KODESYARATBAYAR!='') {
					die(json_encode(array('errorMsg' => 'Kode Syarat Bayar Sudah Digunakan Oleh Syarat Bayar ('.$r->KODESYARATBAYAR.') '.$r->NAMASYARATBAYAR.', Kode Tidak Dapat Digunakan')));
				}
				$p = $db->prepare('select kodesyaratbayar, namasyaratbayar from msyaratbayar where namasyaratbayar=?');
				$q = $db->execute($p, $nama);
				$r = $db->fetch($q);
				if ($r->NAMASYARATBAYAR!='') {
					die(json_encode(array('errorMsg' => 'Keterangan Syarat Bayar Sudah Digunakan Oleh Syarat Bayar ('.$r->KODESYARATBAYAR.') '.$r->NAMASYARATBAYAR.', Keterangan Tidak Dapat Digunakan')));
				}
				$status = 1;
			} else if ($act=='edit') {
				$p = $db->prepare('select kodesyaratbayar, namasyaratbayar from msyaratbayar where kodesyaratbayar<>? and namasyaratbayar=?');
				$q = $db->execute($p, array($kode, $nama));
				$r = $db->fetch($q);
				if ($r->NAMASYARATBAYAR!='') {
					die(json_encode(array('errorMsg' => 'Keterangan Syarat Bayar Sudah Digunakan Oleh Syarat Bayar ('.$r->KODESYARATBAYAR.') '.$r->NAMASYARATBAYAR.', Deskripsi Tidak Dapat Digunakan')));
				}
			}
			$tr = $db->start_trans();

			$data_values = array (
				$kode, $_POST['NAMASYARATBAYAR'], (isset($_POST['SELISIH']) ? $_POST['SELISIH'] : 0), $_POST['REMARK'], $_SESSION['user'],
				date("Y-m-d"), $status
			);
			$query = $db->insert('msyaratbayar', $data_values, $tr);
			if (!$query) {
				$db->rollback($tr);
				die(json_encode(array('errorMsg' => 'Simpan Data Gagal')));
			}

			// commit transaction
			$db->commit($tr);

			// panggil fungsi untuk log history
			log_history(
				$kode,
				'MASTER Syarat Bayar',
				$act,
				array(
					array(
						'nama'  => 'header',
						'tabel' => 'msyaratbayar',
						'kode'  => 'kodesyaratbayar'
					),
				),
				$_SESSION['user']
			);

			echo json_encode(array('success' => true));
		} else if ($act=='delete') {
			$kode = $_POST['id'];

			$p = $db->prepare('select first 1 skip 0 kodesyaratbayar as kode from tbeli where kodesyaratbayar=?');
			$q = $db->execute($p, $kode);
			$r = $db->fetch($q);

			if ($r->KODE<>'') { die(json_encode(array('errorMsg'=>'Data Syarat Bayar Tidak Dapat Dihapus, Sudah Digunakan Pada Transaksi'))); }

			$q = $db->delete('msyaratbayar', array('kodesyaratbayar' => $kode));

			if ($q) {
				// panggil fungsi untuk log history
				log_history(
					$kode,
					'MASTER Syarat Bayar',
					$act,
					array(
						array(
							'nama'  => 'header',
							'tabel' => 'msyaratbayar',
							'kode'  => 'kodesyaratbayar'
						),
					),
					$_SESSION['user']
				);

				echo json_encode(array('success'=>true));
			} else {
				echo json_encode(array('errorMsg'=>'Terdapat Data Error, Data Tidak Dapat Dihapus'));
			}
		}
	break;
	//========================= END MASTER DATA SYARAT BAYAR ===========================
	
}
?>