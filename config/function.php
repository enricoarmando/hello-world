<?php
function ubah_tgl_mysql($tanggalnya) {
	//merubah format dari dd/mm/yyyy ke yyyy-mm-dd
	if ($tanggalnya!=NULL) {
		$pecah = explode('/', $tanggalnya);
		return $pecah[2] . '-' . $pecah[1] . '-' . $pecah[0];
	}
}
function ubah_tgl_indo($tgl) {
	//merubah format dari yyyy-mm-dd ke dd/mm/yyyy

	return $tgl;
	/*
	if ($tgl!=NULL) {
		return substr($tgl,8,2) . '/' . substr($tgl,5,2) . '/' . substr($tgl,0,4);
	}
	*/
}
function ubah_tgl_firebird($tgl) {
	//merubah format dari dd/mm/yyyy ke yyyy.mm.dd
	return $tgl;
	/*
	if ($tgl!=NULL) {
		return substr($tgl,6,4) . '/' . substr($tgl,3,2) . '/' . substr($tgl,0,2);
	}
	*/
}

function cek_bulan ($id) {
	switch ($id) {
		case '01' : $bulan = 'JANUARI'; break;
		case '02' : $bulan = 'FEBRUARI'; break;
		case '03' : $bulan = 'MARET'; break;
		case '04' : $bulan = 'APRIL'; break;
		case '05' : $bulan = 'MEI'; break;
		case '06' : $bulan = 'JUNI'; break;
		case '07' : $bulan = 'JULI'; break;
		case '08' : $bulan = 'AGUSTUS'; break;
		case '09' : $bulan = 'SEPTEMBER'; break;
		case '10' : $bulan = 'OKTOBER'; break;
		case '11' : $bulan = 'NOVEMBER'; break;
		case '12' : $bulan = 'DESEMBER'; break;
	}
	return $bulan;
}

function cek_tgl ($tgl) {
	if (substr($tgl,0,4) > 1000 and substr($tgl,0,4) < 2100) // format en sistem
		return 'en';
	else if (substr($tgl,6,4) > 1000 and substr($tgl,6,4) < 2100) // format in sistem
		return 'in';
}

function cetak_tgl ($date) {
	if (cek_tgl($date) == 'en') {
		$tgl   = substr($date,8,2);
		$bulan = substr($date,5,2);
		$tahun = substr($date,0,4);
	} else {
		$tgl   = substr($date,0,2);
		$bulan = substr($date,3,2);
		$tahun = substr($date,6,4);
	}

	return $tgl.' '.cek_bulan($bulan).' '.$tahun;
}

function selisih_hari($tgl_awal, $tgl_akhir){
	$selisih = strtotime($tgl_akhir) -  strtotime($tgl_awal);
	$hari = $selisih/(60*60*24);
	//60 detik * 60 menit * 24 jam = 1 hari

	return $hari;
}

function selisih_jatuh_tempo($selisih_hari, $tgl) {
	// FORMAT TANGGAL HARUS YYYY-MM-DD
	//$newdate = date('d/m/Y', strtotime('+'.$selisih_hari.' days', strtotime($tgl)));
	$newdate = date('Y-m-d', strtotime('+'.$selisih_hari.' days', strtotime($tgl)));
	return $newdate;
}

function get_urutan ($get_array) {
	$i = 1;
	if (count($get_array)>0) {
		foreach ($get_array as $item) {
			$array_urutan[] = $item->urutan;
		}

		// urutkan id berdasarkan asc
		sort($array_urutan);

		// ambil urutan paling akhir dan tambahkan 1
		$i = end($array_urutan)+1;
	}
	return $i;
}

function ubah_angka_romawi($n){
	$hasil = "";
	$iromawi = array("","I","II","III","IV","V","VI","VII","VIII","IX","X",
	20=>"XX",30=>"XXX",40=>"XL",50=>"L",60=>"LX",70=>"LXX",80=>"LXXX",
	90=>"XC",100=>"C",200=>"CC",300=>"CCC",400=>"CD",500=>"D",
	600=>"DC",700=>"DCC",800=>"DCCC",900=>"CM",1000=>"M",
	2000=>"MM",3000=>"MMM");

	if(array_key_exists($n,$iromawi)){
		$hasil = $iromawi[$n];
	} else if ($n >= 11 && $n <= 99){
		$i = $n % 10;
		$hasil = $iromawi[$n-$i] . ubah_angka_romawi($n % 10);
	} else if ($n >= 101 && $n <= 999){
		$i = $n % 100;
		$hasil = $iromawi[$n-$i] . ubah_angka_romawi($n % 100);
	} else {
		$i = $n % 1000;
		$hasil = $iromawi[$n-$i] . ubah_angka_romawi($n % 1000);
	}

	return $hasil;
}

class angka_terbilang {
	function baca($n) {
		$this->dasar = array(1 => 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam','tujuh', 'delapan', 'sembilan');
		$this->angka = array(1000000000, 1000000, 1000, 100, 10, 1);
		$this->satuan = array('milyar', 'juta', 'ribu', 'ratus', 'puluh', '');
		$pecah = explode('.', $n);

		$n = $pecah[0];
		$k = $pecah[1];

		if ($k<>'' and (substr($k, 0, 1)<>0 and substr($k, 1, 1)<>0)) {
			$koma = 'koma ';
			if (substr($k, 0, 1)==0) {
				$koma .= 'nol ';
				$k = substr($k, 1, 1);
			}

			$i = 0;
			while ($k != 0) {
				$count = (int)($k/$this->angka[$i]);
				if ($count >= 10) {
					$koma .= $this->baca($count). " ".$this->satuan[$i]." ";
				} else if($count > 0 && $count < 10){
					$koma .= $this->dasar[$count] . " ".$this->satuan[$i]." ";
				}
				$k -= $this->angka[$i] * $count;
				$i++;
			}
			$koma = preg_replace("/satu puluh (\w+)/i", "\\1 belas", $koma);
			$koma = preg_replace("/satu (ribu|ratus|puluh|belas)/i", "se\\1", $koma);
		}
		$i = 0;
		if ($n==0) {
			$str = "nol";
		} else {
			while ($n != 0) {
				$count = (int)($n/$this->angka[$i]);
				if ($count >= 10) {
					$str .= $this->baca($count). " ".$this->satuan[$i]." ";
				} else if($count > 0 && $count < 10){
					$str .= $this->dasar[$count] . " ".$this->satuan[$i]." ";
				}
				$n -= $this->angka[$i] * $count;
				$i++;
			}
			$str = preg_replace("/satu puluh (\w+)/i", "\\1 belas", $str);
			$str = preg_replace("/satu (ribu|ratus|puluh|belas)/i", "se\\1", $str);
		}
		return $str.$koma;
	}
}

function get_max_urutan($table, $field, $temp_kodetrans, $count){
	global $db;
	//$db = new DB;

	$j = 1;
	for ($i=0; $i<$count; $i++) {
		$j .= 0;
	}
	$j++;

	$query = $db->query("select max($field) as kode from $table where $field like '$temp_kodetrans%'");
	$r = $db->fetch($query);

	$urutan = substr($r->KODE, -$count)+$j;
	return substr($urutan, 1);
}

function cek_valid_data ($tabel, $field, $kode, $nama) {
	global $db;
	//$db = new DB;

	if ($kode=='') die(json_encode(array('errorMsg' => 'Harap Input Data '.$nama.' yang Valid')));

	$query = $db->select($tabel, array(' count(*) as data '), array($field => $kode));
	$rs = $db->fetch($query);

	if ($rs->DATA<1) die(json_encode(array('errorMsg' => $nama.' Tidak Terdapat Pada Database')));
}

function cek_valid_datagrid ($tabel, $field, $kode, $nama) {
	global $db;
	//$db = new DB;

	if ($kode=='') die(json_encode(array('isError' => 'Harap Input Data '.$nama.' yang Valid')));

	$query = $db->select($tabel, array(' count(*) as data '), array($field => $kode));
	$rs = $db->fetch($query);

	if ($rs->DATA<1) die(json_encode(array('isError' => true, 'msg' => $nama.' Tidak Terdapat Pada Database')));
}

function get_status($tabel, $field, $kodetrans) {
	global $db;
	//$db = new DB;

	if ($kodetrans=='') die(json_encode(array('errorMsg' => ' Please Choose Transaction ID First')));

	$query = $db->select($tabel, array('status'), array($field => $kodetrans));
	$rs = $db->fetch($query);

	return $rs->STATUS;
}

function cek_pelunasan ($jenis, $kodetrans) {
	global $db;
	//$db = new DB;

	if ($jenis=='piutang'){
		$tabel = 'pelunasanpiutang a inner join pelunasanpiutangdtl b on a.kodepelunasan=b.kodepelunasan';
	}else if ($jenis=='hutang'){
		$tabel = 'pelunasanhutang a inner join pelunasanhutangdtl b on a.kodepelunasan=b.kodepelunasan';
	}else if ($jenis=='hutangkomisi'){
		$tabel = 'pelunasanhutangkomisi a inner join pelunasanhutangkomisidtl b on a.kodepelunasan=b.kodepelunasan';
	}
	$query = $db->select($tabel, array('b.kodetrans', 'a.status'), array('b.kodetrans' => $kodetrans));
	$rs = $db->fetch($query);

	if($rs->KODETRANS<>'' && $rs->STATUS<>'D') {
		if ($jenis=='piutang'){
			die(json_encode(array('errorMsg' => 'Sudah Terdapat Pelunasan Piutang, Transaksi Tidak Dapat Diubah/Dibatalkan')));
		}else if ($jenis=='hutang'){
			die(json_encode(array('errorMsg' => 'Sudah Terdapat Pelunasan Hutang, Transaksi Tidak Dapat Diubah/Dibatalkan')));
		}else if ($jenis=='hutangkomisi'){
			die(json_encode(array('errorMsg' => 'Sudah Terdapat Pembayaran Komisi, Transaksi Tidak Dapat Diubah/Dibatalkan')));
		}
	}
}

function encrypt_data($password) {
	$pjg = strlen($password); $temp = '';
	for ($i=0; $i<$pjg; $i++) {
		$temp .= chr(ord($password[$i]) ^ 18);
	}
	return $temp;
}

function get_harga_jual_terakhir ($kode_cust, $kode_barang, $tgl_trans) {
	global $db;
	//$db = new DB;

	$sql = "select harga from mhargajual where kodecustomer='$kode_cust' and kodebarang='$kode_barang'
			and tglaktif=(select max(tglaktif) from mhargajual where kodecustomer='$kode_cust' and kodebarang='$kode_barang' and tglaktif<='$tgl_trans')";
	$query = $db->query($sql);
	$r = $db->fetch($query);

	return $r->HARGA<>'' ? $r->HARGA : 0;
}

function get_harga_beli_terakhir ($kode_barang, $tgl_trans) {
	global $db;
	//$db = new DB;

   //HARGA BELI MENGACU KE HARGA BELI TERAKHIR PADA LOKASI DC SAJA
	$sql = "select first 1 skip 0 (b.hargakurs-b.discrp1-b.discrp2-b.discrp3-b.discrp4-b.discrp5) as harga, b.satuan
			from tbeli a inner join tbelidtl b on a.kodebeli=b.kodebeli
			where b.kodebarang='$kode_barang' and a.status<>'D'
				  and a.kodelokasi='1001'
				  and a.tgltrans=(
						select max(a.tgltrans)
						from tbeli a inner join tbelidtl b on a.kodebeli=b.kodebeli
						where a.tgltrans<='$tgl_trans'
						and b.kodebarang='$kode_barang' and a.status<>'D'
					)
			order by a.kodebeli desc";
	$query = $db->query($sql);
	$r = $db->fetch($query);

	$harga	= $r->HARGA<>'' ? $r->HARGA : 0;
	$satuan = $r->SATUAN;
	if ($harga>0) {
		$query = $db->query("select satuan, satuan2, satuan3, konversi1, konversi2 from mbarang where kodebarang='$kode_barang'");
		$r = $db->fetch($query);
		if ($satuan==$r->SATUAN) {
			$harga = $harga;
		} else if ($satuan==$r->SATUAN2) {
			$harga = $harga*$r->KONVERSI1;
		} else if ($satuan==$r->SATUAN3) {
			$harga = $harga*($r->KONVERSI1*$r->KONVERSI2);
		}
	}
	return $harga;
}

function cek_periode ($tgl_trans, $jenis) {
	global $db;
	//$db = new DB;

	$q = $db->select('historytanggal', array('kodelokasi', 'status'), array('kodelokasi'=>$_SESSION['KODELOKASI'], 'tanggal'=>$tgl_trans));
	$r = $db->fetch($q);
	if ($r->STATUS == NULL) {
		die(json_encode(array('errorMsg' => 'Transaksi Tidak Bisa di'.$jenis.'<br>Tanggal Transaksi Belum Dibuka<br>Silahkan Hubungi AR yang Bertanggung Jawab')));
	} else if ($r->STATUS == 0) {
		die(json_encode(array('errorMsg' => 'Transaksi Tidak Bisa di'.$jenis.'<br>Tanggal Transaksi Sudah Ditutup<br>Silahkan Hubungi AR yang Bertanggung Jawab')));
	}

	/*
	$q = $db->query('select kodesaldoperkiraan from saldoperkiraan');
	$r = $db->fetch($q);
	if ($r->KODESALDOPERKIRAAN<>'') {
		$q = $db->query("select tgltrans from saldoperkiraan where tgltrans<='$tgl_trans'");
		$r = $db->fetch($q);
		if ($r->TGLTRANS=='') {
			die(json_encode(array('errorMsg' => 'Transaction Can Not be '.$jenis.'<br>The Transaction Date Not Allowed Less Than Beginning Stock Date')));
		}
	}
	$q = $db->query("select max(tglakhir) as tglakhir from mclosing where tglakhir>='$tgl_trans'");
	$r = $db->fetch($q);
	if ($r->TGLAKHIR<>'') {
		die(json_encode(array('errorMsg' => 'Transaction Can Not be '.$jenis.'<br>Existing Last Closing On Date '.ubah_tgl_indo($r->TGLAKHIR))));
	}

	$q = $db->query("select tanggal from historytanggal where tanggal='$tgl_trans'");
	$r = $db->fetch($q);
	if ($r->TANGGAL<>'') {
		die(json_encode(array('errorMsg' => 'Transaction Can Not be '.$jenis.'<br>Date Transaction Already In Close')));
	}
	*/
}

function get_tgl_trans ($table, $field, $kodetrans) {
	global $db;
	//$db = new DB;

	$q = $db->query("select tgltrans from $table where $field='$kodetrans'");
	$r = $db->fetch($q);

	//return ubah_tgl_firebird(ubah_tgl_indo($r->TGLTRANS));
	return $r->TGLTRANS;
}

function send_mail ($table, $column_field, $kodetrans, $jtrans, $kodemenu) {
	global $db;
	//$db = new DB;

	$list_user_penerima = array();
	$list_email_penerima = array();
	$sql = 'select
				distinct c.userid, c.username, c.email
			from
				mmenulink a inner join muserakses b on a.kodemenu=b.kodemenu and b.hakakses=1
				inner join muser c on b.userid=c.userid
			where a.induk=?';
	$pr  = $db->prepare($sql);
	$exe = $db->execute($pr, $kodemenu);
	while ($rs = $db->fetch($exe)) {
		$list_user_penerima[] = $rs->USERNAME;
		$list_email_penerima[] = $rs->EMAIL;
	}

	$sql = 'select * from mmenu where kodemenu=?';
	$pr  = $db->prepare($sql);
	$exe = $db->execute($pr, $kodemenu);
	$rs  = $db->fetch($exe);
	$namamenu = $rs->NAMAMENUINGGRIS;

	$sql = 'select * from '.$table.' where '.$column_field.'=?';
	$pr  = $db->prepare($sql);
	$exe = $db->execute($pr, $kodetrans);
	$rs  = $db->fetch($exe);

	$jam_input = $rs->JAMINPUT;
	$tgl_input = ubah_tgl_firebird($rs->TGLINPUT);

	// email ke pengirim

	$mail = new PHPMailer;
	$mail->isSMTP();
	$mail->Host = 'smtp.gmail.com';
	$mail->SMTPAuth = true;
	$mail->Username = 'canadagreengate2015@gmail.com';
	$mail->Password = 'C4n4d4greengate';
	$mail->SMTPSecure = 'ssl';
	$mail->Port = 465;
	$mail->From = 'canadagreengate2015@gmail.com';
	$mail->FromName = 'Canada Green Gate';

	$mail->addAddress($_SESSION['email_user'], $_SESSION['user']);
	$mail->isHTML(true);
	$mail->Subject = 'Information';

	$body = 'Salam,'.
			'<br><br>'.
			'Anda melakukan '.$jtrans.' data '.$namamenu.
			'<br>'.
			'dengan nomor '.$kodetrans.','.
			'pada tanggal '.$tgl_input.' jam '.$jam_input.
			'<br>'.
			'kepada '.implode(", ", $list_user_penerima).
			'<br><br><br>'.
			'Terima Kasih';
	$mail->Body    = $body;
	if(!$mail->send()) {
		return $mail->ErrorInfo;
	}

	// email ke pengirim
	$mail = new PHPMailer;
	$mail->isSMTP();
	$mail->Host = 'smtp.gmail.com';
	$mail->SMTPAuth = true;
	$mail->Username = 'canadagreengate2015@gmail.com';
	$mail->Password = 'C4n4d4greengate';
	$mail->SMTPSecure = 'ssl';
	$mail->Port = 465;
	$mail->From = 'canadagreengate2015@gmail.com';
	$mail->FromName = 'Canada Green Gate';

	for ($i=0; $i<count($list_user_penerima); $i++) {
		$mail->addAddress($list_email_penerima[$i], $list_user_penerima[$i]);
	}
	$mail->isHTML(true);
	$mail->Subject = 'Information';
	$body = 'Salam,'.
			'<br><br>'.
			'Sdr/i '.$_SESSION['user'].
			'<br>'.
			'melakukan '.$jtrans.' data '.$namamenu.
			'<br>'.
			'dengan nomor '.$kodetrans.','.
			'<br>'.
			'pada tanggal '.$tgl_input.' jam '.$jam_input.
			'<br>'.
			'Harap segera lakukan cek data'.
			'<br><br><br>'.
			'Terima Kasih';
	$mail->Body    = $body;
	$mail->send();

	if(!$mail->send()) {
		return $mail->ErrorInfo;
	} else {
		return true;
	}
}

function kirim_notifikasi ($table, $column_field, $kodetrans, $jtrans, $kodemenu) {
	global $db;
	//$db = new DB;

	$tr = $db->start_trans();

	$data_userid = array();
	$data_username = array();

	$sql = 'select
				distinct c.userid, c.username, c.email
			from
				mmenulink a inner join muserakses b on a.kodemenu=b.kodemenu and b.hakakses=1
				inner join muser c on b.userid=c.userid
			where a.induk=?';
	$pr  = $db->prepare($sql, $tr);
	$exe = $db->execute($pr, $kodemenu);
	while ($rs = $db->fetch($exe)) {
		$data_userid[] = $rs->USERID;
		$data_username[] = $rs->USERNAME;
	}

	$sql = 'select * from mmenu where kodemenu=?';
	$pr  = $db->prepare($sql, $tr);
	$exe = $db->execute($pr, $kodemenu);
	$rs  = $db->fetch($exe);
	$namamenu = $rs->NAMAMENUINGGRIS;

	$sql = 'select * from '.$table.' where '.$column_field.'=?';
	$pr  = $db->prepare($sql, $tr);
	$exe = $db->execute($pr, $kodetrans);
	$rs  = $db->fetch($exe);

	$jam_input = $rs->JAMINPUT;
	$tgl_input = ubah_tgl_firebird($rs->TGLINPUT);

	$sql = $db->insert('tpemberitahuan', 4, $tr, true);
	$pr  = $db->prepare($sql, $tr);

	// ke pengirim
	$ket = 'Salam,'.
			chr(13).chr(13).
		   'Anda melakukan '.$jtrans.' data '.$namamenu.
			chr(13).
			'dengan nomor '.$kodetrans.', pada tanggal '.$tgl_input.' jam '.$jam_input.
			chr(13).
			'kepada '.implode(", ", $data_username).
			chr(13).chr(13).chr(13).
			'Terima Kasih';
	$data_values = array(
		0, $_SESSION['userid'], $ket, 'I'
	);
	$exe = $db->execute($pr, $data_values);

	// ke penerima
	$ket = 'Salam,'.
			chr(13).chr(13).
			'Sdr/i '.$_SESSION['user'].
			chr(13).
			'melakukan '.$jtrans.' data '.$namamenu.
			chr(13).
			'dengan nomor '.$kodetrans.','.
			chr(13).
			'pada tanggal '.$tgl_input.' jam '.$jam_input.
			chr(13).
			'Harap segera lakukan cek data'.
			chr(13).chr(13).chr(13).
			'Terima Kasih';
	for ($i=0; $i<count($data_userid); $i++) {
		$data_values = array(
			0, $data_userid[$i], $ket, 'I'
		);
		$exe = $db->execute($pr, $data_values);
	}

	$db->commit($tr);

	return true;
}

function log_history ($kodetrans, $menu, $act, $data_table, $kasir) {
	global $db;
	//$db = new DB;

	$data_table = json_decode(json_encode($data_table));

	$tr = $db->start_trans();
	$a_data = array();
	if(count($data_table)>0) {
		foreach ($data_table as $item => $value) {
			$sql = 'select * from '.$value->tabel.' where '.$value->kode.' = ?';
			$pr  = $db->prepare($sql, $tr);
			$exe = $db->execute($pr, $kodetrans);
			while ($rs = $db->fetch($exe)) {
				$a_data[$value->nama][] = $rs;
			}
		}
	}

	// GET MAC ADDRESS
	$_IP_ADDRESS = $_SERVER['REMOTE_ADDR'];
	$_PERINTAH = "arp -a $_IP_ADDRESS";
	ob_start();
	system($_PERINTAH);
	$_HASIL = ob_get_contents();
	ob_clean();
	$_PECAH = strstr($_HASIL, $_IP_ADDRESS);
	$_PECAH_STRING = explode($_IP_ADDRESS, str_replace(" ", "", $_PECAH));
	$_MAC = substr($_PECAH_STRING[1], 0, 17);
	//LAST END SCRIPT GET MAC ADDRESS

	$sql = $db->insert('historydata', 10, $tr, true);
	$pr  = $db->prepare($sql, $tr);
	$exe = $db->execute(
		$pr,
		array(
		    '1',
			$kodetrans,
			strtoupper($menu),
			strtoupper($act),
			json_encode(array($a_data)),
			date('Y-m-d'),
			date('H:i:s'),
			$_IP_ADDRESS,
			$_MAC,
			$kasir
		)
	);

	$db->commit($tr);
}

/**
 * This file is part of the array_column library
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @copyright Copyright (c) Ben Ramsey (http://benramsey.com)
 * @license http://opensource.org/licenses/MIT MIT
 */
if (!function_exists('array_column')) {
    /**
     * Returns the values from a single column of the input array, identified by
     * the $columnKey.
     *
     * Optionally, you may provide an $indexKey to index the values in the returned
     * array by the values from the $indexKey column in the input array.
     *
     * @param array $input A multi-dimensional array (record set) from which to pull
     *                     a column of values.
     * @param mixed $columnKey The column of values to return. This value may be the
     *                         integer key of the column you wish to retrieve, or it
     *                         may be the string key name for an associative array.
     * @param mixed $indexKey (Optional.) The column to use as the index/keys for
     *                        the returned array. This value may be the integer key
     *                        of the column, or it may be the string key name.
     * @return array
     */
    function array_column($input = null, $columnKey = null, $indexKey = null)
    {
        // Using func_get_args() in order to check for proper number of
        // parameters and trigger errors exactly as the built-in array_column()
        // does in PHP 5.5.
        $argc = func_num_args();
        $params = func_get_args();
        if ($argc < 2) {
            trigger_error("array_column() expects at least 2 parameters, {$argc} given", E_USER_WARNING);
            return null;
        }
        if (!is_array($params[0])) {
            trigger_error(
                'array_column() expects parameter 1 to be array, ' . gettype($params[0]) . ' given',
                E_USER_WARNING
            );
            return null;
        }
        if (!is_int($params[1])
            && !is_float($params[1])
            && !is_string($params[1])
            && $params[1] !== null
            && !(is_object($params[1]) && method_exists($params[1], '__toString'))
        ) {
            trigger_error('array_column(): The column key should be either a string or an integer', E_USER_WARNING);
            return false;
        }
        if (isset($params[2])
            && !is_int($params[2])
            && !is_float($params[2])
            && !is_string($params[2])
            && !(is_object($params[2]) && method_exists($params[2], '__toString'))
        ) {
            trigger_error('array_column(): The index key should be either a string or an integer', E_USER_WARNING);
            return false;
        }
        $paramsInput = $params[0];
        $paramsColumnKey = ($params[1] !== null) ? (string) $params[1] : null;
        $paramsIndexKey = null;
        if (isset($params[2])) {
            if (is_float($params[2]) || is_int($params[2])) {
                $paramsIndexKey = (int) $params[2];
            } else {
                $paramsIndexKey = (string) $params[2];
            }
        }
        $resultArray = array();
        foreach ($paramsInput as $row) {
            $key = $value = null;
            $keySet = $valueSet = false;
            if ($paramsIndexKey !== null && array_key_exists($paramsIndexKey, $row)) {
                $keySet = true;
                $key = (string) $row[$paramsIndexKey];
            }
            if ($paramsColumnKey === null) {
                $valueSet = true;
                $value = $row;
            } elseif (is_array($row) && array_key_exists($paramsColumnKey, $row)) {
                $valueSet = true;
                $value = $row[$paramsColumnKey];
            }
            if ($valueSet) {
                if ($keySet) {
                    $resultArray[$key] = $value;
                } else {
                    $resultArray[] = $value;
                }
            }
        }
        return $resultArray;
    }
}

function number ($amount, $nofilter = false, $decimal = 2) {
	//if(is_numeric($amount) == false or ($nofilter == false && $_SESSION['TAMPILGRANDTOTAL'] == 0))
	//	$amount = 0;

	return number_format($amount, $decimal, '.', ',');
}

// untuk set value tgl awal filter
define('TGLAWALFILTER', date('Y-m-d', strtotime('-2 days', strtotime('now'))));

// pengecekan stok
function cek_stok ($kodebarang, $tgltrans, $jml, $satuan, $kodelokasi = '', $kodegudang = '') {
	global $db;
	//$db = new DB;

	if ($_SESSION['CEKSTOK'] == 1) {
		// jika gudang dan lokasi kosong
		// maka lokasi dari session dan gudang dari gudang utama
		if ($kodelokasi == '') {
			$kodelokasi = $_SESSION['KODELOKASI'];
		}

		if ($kodegudang == '') {
			// dapatkan gudang utama
			$ex = $db->select('mgudang', array('kodegudang'), array('kodelokasi'=>$kodelokasi, 'jenis'=>1));
			$rs = $db->fetch($ex);

			$kodegudang = $rs->KODEGUDANG;
		}
		/*$pr = $db->prepare('execute procedure GET_KONVERSI_SATUANUTAMA(?, ?)');
		$ex = $db->execute($pr, array($kodebarang, $satuan));
		$r = $db->fetch($ex);
		$jml = $r->KONVERSI * $jml;
		*/

		// sisa data kartustok
		/*$pr = $db->prepare('execute procedure GET_SALDOSTOK(?, ?, ?, ?)');
		$ex = $db->execute($pr, array($kodebarang, $kodelokasi, $kodegudang, $tgltrans));
		$r = $db->fetch($ex);
		$sisa_stok = $r->SALDO;*/

		// sisa data mbarangdtl
		$ex = $db->select('mbarangdtl', array('sum(sisa) as sisa'), array('kodelokasi'=>$kodelokasi, 'kodegudang'=>$kodegudang, 'kodebrg'=>$kodebarang));
		$rs = $db->fetch($ex);
		$sisa_stok_fifo = $rs->SISA == '' ? 0 : $rs->SISA;

		if (/*$jml > $sisa_stok or */$jml > $sisa_stok_fifo) {
			return false;
		} else {
			return true;
		}
	} else {
		return true;
	}
}

return_array_post_filter();

/* untuk memfilter dari serangan xss */
function return_array_post_filter(){
    $return_array = array();

    foreach($_POST as $postKey => $postVar){
		if (is_array($postVar) == false)
			$return_array[$postKey] = post_filter($postVar);
		else
			$return_array[$postKey] = $postVar;
    }

	$_POST = $return_array;
}

function post_filter($data) {
	$data = trim($data);

	//$data = stripslashes($data);
	//$data = htmlspecialchars($data, ENT_NOQUOTES);

	$data = str_replace('<script', '</script', $data);
	$data = str_replace('<?php', '</php', $data);
	$data = str_replace('<? ', '</php', $data);
	$data = str_replace('<?=', '</php=', $data);
	$data = str_replace('<style', '</style', $data);

	$data = str_replace('<SCRIPT', '</SCRIPT', $data);
	$data = str_replace('<?PHP', '</PHP', $data);
	$data = str_replace('<STYLE', '</STYLE', $data);

	return $data;

}

function get_new_urutan($table, $field, $param, $count = 6){
	global $db;
	//$db = new DB;

	$j = 1;
	for ($i=0; $i<$count; $i++) {
		$j .= 0;
	}
	$j++;

	// dapatkan panjang kode/kodetrans
	// lalu tambahkan angka 1
	$ln = strlen($param[0]) + $count;

	$sql = "select right(max(kode), $count) as URUTAN
			from (select substring($field from 1 for $ln) as kode, right($field, 2) as tahun
				  from $table)
			where kode like '%'||?||'%' and tahun = ?";
	$pr  = $db->prepare($sql);
	$exe = $db->execute($pr, $param);
	$rs  = $db->fetch($exe);

	$urutan = $rs->URUTAN + $j;

	return substr($urutan, 1);
}

function cek_pemakaian_stok($kodetrans) {
	//$db = new DB;
	global $db;

	$sql = 'select b.kodebarang, b.namabarang
			from mbarangdtl a
			inner join mbarang b on a.kodebrg = b.kodebarang
			where a.kodetrans = ? and
				  a.jmlout > 0 and
				  a.sisa < a.jmlin';
	$pr = $db->prepare($sql);
	$qr = $db->execute($pr, array($kodetrans));
	while ($rs = $db->fetch($qr)) {
		die(json_encode(array('errorMsg' => 'Ada Barang Yang Telah Digunakan')));
		break;
	}
}

// filter untuk datagrid-row-filter
function filter_datagrid($data) {
	$data = json_decode($data);
	$sql_filter = '';
	$param = array();
	if (count($data) > 0) {
		foreach ($data as $item) {
			$sql_filter .= 'and '.$item->field.' like \'%\'||?||\'%\' ';
			$param[] = $item->value;
		}
		if ($sql_filter <> '') {
			$sql_filter = ' and ('.substr($sql_filter, 3).') ';
		}
	}

	return((object) array('sql'=>$sql_filter, 'param'=>$param));
}

// fungsi ini dijalankan utk mengecek salah satu field di datagrid apakah sesuai dengan master di database
function cek_data($rows, $field, $table) {
	global $db;
	//$db = new DB;

	$jml_item = count($rows);

	$temp_item = array();
	$temp_sql = '';
	for ($i=0; $i<$jml_item; $i++) {
		$row = (array) $rows[$i];

		$temp_item[] = $row[$field];
		$temp_sql .= 'or '.$field.'=? ';
	}

	$sql = 'select count(*) as jml from '.$table.($temp_sql <> '' ? ' where '.substr($temp_sql, 2) : '');

	$p = $db->prepare($sql);
	$e = $db->execute($p, $temp_item);
	$r = $db->fetch($e);

	// remove duplicate data
	$jml = count(array_unique($temp_item));

	if ($r->JML <> $jml) {
		die(json_encode(array('errorMsg' => 'Cek Detil Transaksi, Ada '.$field.' Yang Tidak Sesuai Dengan Data')));
	}
}
?>