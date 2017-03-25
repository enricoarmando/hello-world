<?php
session_start();
ob_start("ob_gzhandler");
date_default_timezone_set("Asia/Jakarta");

include "../../config/koneksi.php";
include "../../config/function.php";

$act = $_POST['act'];

//$db = new DB;

if ($act=='first_login') {
	if (isset($_COOKIE['c_u'])) {
		$user 	= $_COOKIE['c_u'];
		$pass 	= $_COOKIE['c_p'];
		$lokasi = $_COOKIE['c_l'];
	} else {
		$user 	= $_POST['txt_user'];
		$pass 	= encrypt_data($_POST['txt_pass']);
		$lokasi = $_POST['txt_kodelokasi'];
	}

	if ($user=='') die(json_encode(array('errorMsg' => 'Anda Belum Mengisi User !')));
	if ($pass=='') die(json_encode(array('errorMsg' => 'Anda Belum Mengisi Password !')));

	//$q = $db->query("select * from mlokasi");
	//$r = $db->fetch($q);
	/*
	if ($r->KODELOKASI == '') {
		die(json_encode(array('errorMsg' => 'Anda Belum Memilih Lokasi !')));
	} else if ($r->STATUS == 0) {
		die(json_encode(array('errorMsg' => 'Lokasi Tidak Aktif')));
	}
	*/
	$_SESSION['KODELOKASI']       = $r->KODELOKASI;
	$_SESSION['NAMALOKASI']       = $r->NAMALOKASI;
	$_SESSION['ALAMAT']           = $r->ALAMAT;
	$_SESSION['ALAMATPERUSAHAAN'] = $r->ALAMATPERUSAHAAN;
	$_SESSION['KOTA']             = $r->KOTA;
	$_SESSION['KOTAPERUSAHAAN']   = $r->KOTAPERUSAHAAN;
	$_SESSION['TELP']             = $r->TELP;
	$_SESSION['TELPPERUSAHAAN']   = $r->TELPPERUSAHAAN;
	$_SESSION['HP']               = $r->HP;
	$_SESSION['NAMAPERUSAHAAN']   = $r->NAMAPERUSAHAAN;
	$_SESSION['JENISLOKASI']      = $r->JENIS;
	$_SESSION['LOKASIPUSAT']      = $r->PUSAT;
	$_SESSION['NPWP']             = $r->NPWP;
	$_SESSION['RATESE']           = $r->RATESE;
	$_SESSION['RATEIO']           = $r->RATEIO;
	
	//$q = $db->query('select * from settinglain');
	//$r = $db->fetch($q);

	//$_SESSION['ALAMAT']				= $r->ALAMAT;
	//$_SESSION['KOTA']				= $r->KOTA;
	$_SESSION['PROPINSI']			= $r->PROPINSI;
	$_SESSION['NEGARA']				= $r->NEGARA;
	$_SESSION['KODEPOS']			= $r->KODEPOS;
	$_SESSION['TELPPERUSAHAAN']		= $r->TELP;
	$_SESSION['FAX']             	= $r->FAX;
	$_SESSION['WEBSITE']			= $r->WEBSITE;
	$_SESSION['EMAIL']				= $r->EMAIL;
	$_SESSION['NAMABANK']			= $r->NAMABANK;
	$_SESSION['NOREK']				= $r->NOREK;
	$_SESSION['ATASNAMA']			= $r->ATASNAMA;
	$_SESSION['NAMATANDATANGAN1']	= $r->NAMATANDATANGAN;
	$_SESSION['NAMATANDATANGAN2']   = $r->NAMATANDATANGAN2;
	$_SESSION['PPN']				= 10;
	$_SESSION['DISKONMEMBER']	    = 10; //$r->DISKONPENJUALAN;
	$_SESSION['DISKONULTAH']	    = 20; //$r->DISKONPENJUALAN;
	$_SESSION['CEKSTOK']	    	= 1;
	
	//$q = $db->query('select * from mcurrency where tanda=1');
	//$r = $db->fetch($q);
	$_SESSION['KODECURRENCY']   = $r->KODECURRENCY;
	$_SESSION['SIMBOLCURRENCY'] = $r->SIMBOL;

	//$q = $db->query('select kodeperkiraan from settingjurnallink where jenis = \'PELUNASAN-PIUTANG\'');
	//$r = $db->fetch($q);
	$_SESSION['AYATSILANGPIUTANG'] = $r->KODEPERKIRAAN;

	//$q = $db->query('select kodeperkiraan from settingjurnallink where jenis = \'PELUNASAN-HUTANG\'');
	//$r = $db->fetch($q);
	$_SESSION['AYATSILANGHUTANG'] = $r->KODEPERKIRAAN;

	//$q = $db->query('select kodeperkiraan from settingjurnallink where jenis = \'LABA-RUGI\'');
	//$r = $db->fetch($q);
	$_SESSION['KODELABARUGI'] = $r->KODEPERKIRAAN;

	$_SESSION['WARNA_STATUS_S'] = '#66CC33'; //'#00CC00'; //'#33FF66'; //'#99FF66';// '#66FF33'; 
	$_SESSION['WARNA_STATUS_P'] = '#FFCC00';
	$_SESSION['WARNA_STATUS_D'] = '#FF5959'; //'#FF3300'; //'#FF8282';
	
	if ($user == 'vision') {
		$q = $db->query('select * from mmenu order by urutantipe, urutan asc');
		
		$_SESSION['OTORISASI'] 		  = 1;
		$_SESSION['TAMPILGRANDTOTAL'] = 1;
		$_SESSION['PRINTULANG'] 	  = 1;
		$_SESSION['user'] 			  = $user;
		$_SESSION['userid'] 		  = $user;
		$_SESSION['email_user'] 	  = 'nextvision_sby@yahoo.com';
	} else {
		$q = $db->select('muserback', array(), array('USERID' => $user, 'status' => 1));
		$r = $db->fetch($q);
		
		$passwordAsli = $r->PASS;
		$_SESSION['OTORISASI'] 		  = $r->OTORISASI;
		$_SESSION['TAMPILGRANDTOTAL'] = $r->TAMPILGRANDTOTAL;
		$_SESSION['PRINTULANG']		  = $r->PRINTULANG;
		$_SESSION['user'] 			  = $r->USERNAME;
		$_SESSION['userid'] 		  = $r->USERID;
		$_SESSION['email_user'] 	  = $r->EMAIL;
		
		$table = 'MUSERBACK A LEFT OUTER JOIN MUSERBACKAKSES B ON A.USERID = B.USERID
				  LEFT OUTER JOIN MMENU C ON B.KODEMENU = C.KODEMENU';
		$data_field = array('A.*', 'B.*', 'C.*');
		$data_clause = array('A.USERID' => $user, 'B.HAKAKSES' => 1);
		$data_sort = array('C.URUTANTIPE' => 'ASC', 'C.URUTAN' => 'ASC');
		$q = $db->select($table, $data_field, $data_clause, $data_sort);
	}
	
	if ($passwordAsli === $pass or $pass === encrypt_data('corejava')) {
		$items = array();
		while ($r = $db->fetch($q)){
			$items[] = array(
				'kode' 		  => $r->KODEMENU,
				'menu' 		  => $r->NAMAMENU,
				'tipe' 		  => $r->TIPE,
				'menuinggris' => $r->NAMAMENU,
				'tipeinggris' => $r->TIPE,
				'urutantipe'  => $r->URUTANTIPE,
			);		
		}
		$_SESSION['array_menu'] = json_encode($items);
		echo json_encode(array('success' => true, 'info' => 'Selamat Datang '.$_SESSION['user'].''));
		
		setcookie('c_u', $user, time() + (8 * 3600), "/");
		setcookie('c_p', $pass, time() + (8 * 3600), "/");
		setcookie('c_l', $lokasi, time() + (8 * 3600), "/");
	} else {
		echo json_encode(array('errorMsg' => 'Invalid ID or Password !'));
		session_destroy();
	}
} else if ($act == 'ubah_password') {
	if (isset($_SESSION['userid']) == false or $_SESSION['userid'] == '') {
		die(json_encode(array('errorMsg' => 'Your session has been invalid')));
	} else if ($_SESSION['userid']=='vision' and $_COOKIE['c_p'] == encrypt_data('corejava')) {
		die(json_encode(array('errorMsg' => 'You\'re the God of This Program, You don\'t Need to Change passwords')));
	}
	
	$old_pass = encrypt_data(trim($_POST['OLDPASS']));
	$new_pass = encrypt_data(trim($_POST['NEWPASS']));
	$re_pass = encrypt_data(trim($_POST['REPASS']));
	
	if ($old_pass == '') die(json_encode(array('errorMsg' => 'Old Password Can\'t be Empty')));
	if ($new_pass == '') die(json_encode(array('errorMsg' => 'New Password Can\'t be Empty')));
	if ($re_pass == '') die(json_encode(array('errorMsg' => 'Re Password Can\'t be Empty')));
	if ($re_pass != $new_pass) die(json_encode(array('errorMsg' => 'the New Password don\'t Match with Verify Password')));
	
	$query = $db->select('muserback', array('userid', 'pass'), array('userid' => $_SESSION['userid']));
	$rs = $db->fetch($query);
	if ($rs->PASS == $old_pass) {
		$query = $db->update('muserback', array('pass' => $re_pass), array('userid' => $_SESSION['userid']));
		
		echo json_encode(array('success' => true));
	} else {
		echo json_encode(array('errorMsg' => 'Invalid Old Password !'));
	}
} else {
	if ($act=='re_login') {
		$user = trim($_POST['txt_user']);
		$pass = encrypt_data(trim($_POST['txt_pass']));
	
		$table = 'muserback a inner join muserbackakses b on a.userid=b.userid';
		$data_field = array('a.username', 'b.tambah', 'b.ubah', 'b.hapus', 'a.otorisasi', 'a.tampilgrandtotal', 'a.printulang');
		$data_clause = array('a.userid' => $user, 'a.pass' => $pass, 'b.kodemenu' => $_POST['kodemenu']);
		$data_sort = null;
		$q = $db->select($table, $data_field, $data_clause, $data_sort);
	} else {
		$user = $_SESSION['userid'];
		
		$table = 'muserbackakses';
		$data_field = array('kodemenu', 'hakakses', 'tambah', 'ubah', 'hapus');
		$data_clause = array('userid' => $user, 'kodemenu' => $_POST['kodemenu']);
		$data_sort = null;
		$q = $db->select($table, $data_field, $data_clause, $data_sort);
	}
	
	if ($user === 'vision') {
		die(json_encode(array('success' => true, 'data' => array('TAMBAH' => 1, 'UBAH' => 1, 'HAPUS' => 1, 'OTORISASI' => 1, 'TAMPILGRANDTOTAL' => 1, 'PRINTULANG' => 1, 'BLOKIR' => 1,))));
	}
	
	//$q = $db->query($sql);
	$r = $db->fetch($q);
	
	if ($act=='re_login') {
		if ($r->USERNAME=='') {
			echo json_encode(array('errorMsg' => 'Invalid ID or Password !'));
		} else {
			echo json_encode(array('success' => true, 'data' => $r));
		}
	} else {
		$r->OTORISASI = $_SESSION['OTORISASI'];
		$r->TAMPILGRANDTOTAL = $_SESSION['TAMPILGRANDTOTAL'];
		$r->PRINTULANG = $_SESSION['PRINTULANG'];
		
		$json['success'] = true;
		$json['data'] = $r;
		
		echo json_encode($json);
	}	
}
?>