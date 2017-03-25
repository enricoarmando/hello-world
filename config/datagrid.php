<?php
session_start();
include "koneksi.php";
include "function.php";

$table = $_GET['table'];

$field = isset($_POST['field']) ? $_POST['field'] : '';
$value = isset($_POST['value']) ? $_POST['value'] : '';

$page   = isset($_POST['page']) ? intval($_POST['page']) : 1;
$rows   = isset($_POST['rows']) ? intval($_POST['rows']) : 20;
$order  = isset($_POST['order']) ? strval($_POST['order']) : 'asc';
$offset = ($page-1)*$rows;

$temp_tgl_aw = TGLAWALFILTER;

//$db = new DB;

//$txt = $field<>'' ? "and $field like '%'||?||'%'" : '';

$filter = filter_datagrid($_POST['filterRules']);

$result = array('total'=>0, 'rows'=>array());

switch ($table) {
	case 'kategori_customer' :
		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'kodekategoricustomer';

		$st = $db->prepare("select count(*) as row from mkategoricustomer where 1=1 $txt");
		$q = $db->execute($st, $value);
		$r = $db->fetch($q);
		$result["total"] = $r->ROW;

		$sql = "select first $rows skip $offset *
				from mkategoricustomer
				where 1=1 $txt order by $sort $order";
		$st = $db->prepare($sql);
		$q = $db->execute($st, $value);
		while ($rs = $db->fetch($q)) {
			$result["rows"][] = $rs;
		}

		echo json_encode($result);
	break;

	case 'customer' :
		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'kodecustomer';

		$sql = 'select count(*) as row
				from (
					select a.*, b.kodemember
					from mcustomer a
					left join mmember b on a.kodecustomer = b.kodecustomer
					order by '.$sort.' '.$order.'
				) where 1=1 '.$filter->sql;

		$st = $db->prepare($sql);
		$q = $db->execute($st, $filter->param);
		$r  = $db->fetch($q);
		$result["total"] = $r->ROW;

		$sql = str_replace('count(*) as row', "first $rows skip $offset *", $sql)." order by $sort $order";
		$st = $db->prepare($sql);
		$q = $db->execute($st, $filter->param);
		while ($rs = $db->fetch($q)) {
			$result["rows"][] = $rs;
		}

		echo json_encode($result);
	break;

	case 'member' :
		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'kodecustomer';

		$sql = 'select count(*) as row
				from (
					select a.*, b.namacustomer, b.alamat, c.namacustomer as namaupline
					from mmember a
					inner join mcustomer b on a.kodecustomer = b.kodecustomer
					left outer join mcustomer c on a.upline=c.kodecustomer
					order by '.$sort.' '.$order.'
				) where 1=1 '.$filter->sql;

		$st = $db->prepare($sql);
		$q = $db->execute($st, $filter->param);
		$r  = $db->fetch($q);
		$result["total"] = $r->ROW;

		$sql = str_replace('count(*) as row', "first $rows skip $offset *", $sql)." order by $sort $order";
		$st = $db->prepare($sql);
		$q = $db->execute($st, $filter->param);
		while ($rs = $db->fetch($q)) {
			$result["rows"][] = $rs;
		}

		echo json_encode($result);
	break;

	case 'pegawai' :
		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'userid';

		$sql = 'select count(*) as row
				from (
					select a.*, b.namalokasi
					from muser a
					left join mlokasi b on a.kodelokasi=b.kodelokasi
					order by '.$sort.' '.$order.'
				) where 1=1 '.$filter->sql;

		$st = $db->prepare($sql);
		$q = $db->execute($st, $filter->param);
		$r  = $db->fetch($q);
		$result["total"] = $r->ROW;

		$sql = str_replace('count(*) as row', "first $rows skip $offset *", $sql)." order by $sort $order";
		$st = $db->prepare($sql);
		$q = $db->execute($st, $filter->param);
		while ($rs = $db->fetch($q)) {
			$rs->RE_PASS = $rs->PASS;

			$result["rows"][] = $rs;
		}

		echo json_encode($result);
	break;


	case 'supplier' :
		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'kodesupplier';

		$sql = 'select count(*) as row
				from (
					select a.* ,b.namasyaratbayar
					from msupplier a
					left outer join msyaratbayar b on a.kodesyaratbayar = b.kodesyaratbayar
					order by '.$sort.' '.$order.'
				) where 1=1 '.$filter->sql;

		$st = $db->prepare($sql);
		$q = $db->execute($st,  $filter->param);
		$r = $db->fetch($q);
		$result["total"] = $r->ROW;

		$sql = str_replace('count(*) as row', "first $rows skip $offset *", $sql)." order by $sort $order";
		$st = $db->prepare($sql);
		$q = $db->execute($st,  $filter->param);
		while ($rs = $db->fetch($q)) {
			$result["rows"][] = $rs;
		}

		echo json_encode($result);
	break;

	case 'instansi' :
		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'kodeinstansi';

		$sql = 'select count(*) as row
				from minstansi where 1=1 '.$filter->sql;

		$st = $db->prepare($sql);
		$q = $db->execute($st, $filter->param);
		$r = $db->fetch($q);
		$result["total"] = $r->ROW;

		$sql = str_replace('count(*) as row', "first $rows skip $offset *", $sql)." order by $sort $order";
		$st = $db->prepare($sql);
		$q = $db->execute($st, $filter->param);
		while ($rs = $db->fetch($q)) {
			$result["rows"][] = $rs;
		}

		echo json_encode($result);
	break;

	case 'juru_bayar' :
		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'kodejurubayar';

		$sql = 'select count(*) as row
				from (
					select a.*, b.namainstansi
					from mjurubayar a
					inner join minstansi b on a.kodeinstansi=b.kodeinstansi
					order by '.$sort.' '.$order.'
				) where 1=1 '.$filter->sql;

		$st = $db->prepare($sql);
		$q = $db->execute($st, $filter->param);
		$r = $db->fetch($q);
		$result["total"] = $r->ROW;

		$sql = str_replace('count(*) as row', "first $rows skip $offset *", $sql)." order by $sort $order";
		$st = $db->prepare($sql);
		$q = $db->execute($st, $filter->param);
		while ($rs = $db->fetch($q)) {
			$result["rows"][] = $rs;
		}

		echo json_encode($result);
	break;

	case 'ekspedisi' :
		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'kodeekspedisi';

		$st = $db->prepare("select count(*) as row from mekspedisi where 1=1 $txt");
		$q = $db->execute($st, $value);
		$r = $db->fetch($q);
		$result["total"] = $r->ROW;

		$sql = "select first $rows skip $offset *
				from mekspedisi
				where 1=1 $txt
				order by $sort $order";
		$st = $db->prepare($sql);
		$q = $db->execute($st, $value);
		while ($rs = $db->fetch($q)) {
			$result["rows"][] = $rs;
		}

		echo json_encode($result);
	break;

	case 'gambar_lensa' :
		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'kodelensa';

		$st = $db->prepare("select count(*) as row from mgambarlensa where 1=1 $txt");
		$q = $db->execute($st, $value);
		$r = $db->fetch($q);
		$result["total"] = $r->ROW;

		$sql = "select first $rows skip $offset *
				from mgambarlensa
				where 1=1 $txt
				order by $sort $order";
		$st = $db->prepare($sql);
		$q = $db->execute($st, $value);
		while ($rs = $db->fetch($q)) {
			$result["rows"][] = $rs;
		}

		echo json_encode($result);
	break;

	case 'marketing' :
		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'kodemarketing';

		$st = $db->prepare("select count(*) as row from mmarketing where 1=1 $txt");
		$q = $db->execute($st, $value);
		$r = $db->fetch($q);
		$result["total"] = $r->ROW;

		$sql = "select first $rows skip $offset *
				from mmarketing
				where 1=1 $txt
				order by $sort $order";
		$st = $db->prepare($sql);
		$q = $db->execute($st, $value);
		while ($rs = $db->fetch($q)) {
			$result["rows"][] = $rs;
		}

		echo json_encode($result);
	break;

	case 'lokasi' :
		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'kodelokasi';

		$sql = 'select count(*) as row
				from mlokasi
				where 1=1 '.$filter->sql;

		$st = $db->prepare($sql);
		$q = $db->execute($st, $filter->param);
		$r = $db->fetch($q);
		$result["total"] = $r->ROW;

		$sql = str_replace('count(*) as row', "first $rows skip $offset *", $sql)." order by $sort $order";
		$st = $db->prepare($sql);
		$q = $db->execute($st, $filter->param);
		while ($rs = $db->fetch($q)) {
			$result["rows"][] = $rs;
		}

		echo json_encode($result);
	break;

	case 'semua_lokasi' :
		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'kodelokasi';

		$result = array();
		$sql = "select kodelokasi, namalokasi from mlokasi
				where status = 1
				order by $sort $order";
		$q = $db->query($sql);
		while ($rs = $db->fetch($q)) {
			$result[] = $rs;
		}

		echo json_encode($result);
	break;

	case 'lokasi_user' :
		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'kodelokasi';

		$temp_sql = isset($_POST['user']) ? "and a.userid = '".$_POST['user']."'" : '';
		$result = array();

		if ($_POST['user'] == 'vision') {
			$sql = "select kodelokasi, namalokasi
					from mlokasi
					where status = 1
					order by namalokasi";
		} else {
			$sql = "select a.kodelokasi, b.namalokasi
					from muserlokasi a
					inner join mlokasi b on a.kodelokasi = b.kodelokasi
					where b.status = 1
						  $temp_sql
					order by namalokasi";
		}

		$q = $db->query($sql);
		while ($rs = $db->fetch($q)) {
			$result[] = $rs;
		}

		echo json_encode($result);
	break;

	case 'gudang' :
		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'KODEGUDANG';

		$sql = 'select count(*) as row
				from (
					select a.*, b.namalokasi
					from mgudang a
					inner join mlokasi b on a.kodelokasi=b.kodelokasi
					order by '.$sort.' '.$order.'
				) where 1=1 '.$filter->sql;

		$st = $db->prepare($sql);
		$q = $db->execute($st, $filter->param);
		$r = $db->fetch($q);
		$result["total"] = $r->ROW;

		$sql = str_replace('count(*) as row', "first $rows skip $offset *", $sql)." order by $sort $order";
		$st = $db->prepare($sql);
		$q = $db->execute($st, $filter->param);
		while ($rs = $db->fetch($q)) {
			$result["rows"][] = $rs;
		}

		echo json_encode($result);
	break;
	case 'currency' :
		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'kodecurrency';

		$st = $db->prepare("select count(*) as row from mcurrency where 1=1 $txt");
		$q = $db->execute($st, $value);
		$r = $db->fetch($q);
		$result["total"] = $r->ROW;

		$sql = "select first $rows skip $offset *
				from mcurrency
				where 1=1 $txt
				order by $sort $order";
		$st = $db->prepare($sql);
		$q = $db->execute($st, $value);
		while ($rs = $db->fetch($q)) {
			$result["rows"][] = $rs;
		}

		echo json_encode($result);
	break;

	case 'kode_perkiraan' :
		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'kodeperkiraan';

		$sql = 'select count(*) as row
				from mperkiraan
				where 1=1 '.$filter->sql;

		$st = $db->prepare($sql);
		$q = $db->execute($st, $filter->param);
		$r = $db->fetch($q);
		$result["total"] = $r->ROW;

		$sql = str_replace('count(*) as row', "first $rows skip $offset *", $sql)." order by $sort $order";
		$st = $db->prepare($sql);
		$q = $db->execute($st, $filter->param);

		$sql = "select a.kodelokasi, b.namalokasi
				from mperkiraanlokasi a
				inner join mlokasi b on a.kodelokasi = b.kodelokasi
				where a.kodeperkiraan = ?";
		$pr = $db->prepare($sql);
		while ($rs = $db->fetch($q)) {
			$ex = $db->execute($pr, $rs->KODEPERKIRAAN);

			$a_lokasi = array();
			while ($r = $db->fetch($ex)) {
				$a_lokasi[] = array(
					'KODELOKASI' => $r->KODELOKASI,
					'NAMALOKASI' => $r->NAMALOKASI,
				);
			}
			$rs->detail_lokasi = $a_lokasi;

			$result["rows"][] = $rs;
		}

		echo json_encode($result);
	break;

	case 'promo' :
		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'kodepromo';

		$sql = 'select count(*) as row
				from mpromo
				where 1=1 '.$filter->sql;

		$st = $db->prepare($sql);
		$q = $db->execute($st, $filter->param);
		$r = $db->fetch($q);
		$result["total"] = $r->ROW;

		$sql = str_replace('count(*) as row', "first $rows skip $offset *", $sql)." order by $sort $order";
		$st = $db->prepare($sql);
		$q = $db->execute($st, $param->value);

		$sql = 'select a.kodelokasi, b.namalokasi
				from mpromolokasi a
				inner join mlokasi b on a.kodelokasi=b.kodelokasi
				where a.kodepromo = ?';
		$pr = $db->prepare($sql);
		while ($rs = $db->fetch($q)) {
			$ex = $db->execute($pr, $rs->KODEPROMO);
			$a_lokasi = array();
			while ($r = $db->fetch($ex)) {
				$a_lokasi[] = array(
					'KODELOKASI' => $r->KODELOKASI,
					'NAMALOKASI' => $r->NAMALOKASI,
				);
			}
			$rs->detail_lokasi = $a_lokasi;

			$result["rows"][] = $rs;
		}

		echo json_encode($result);
	break;

	case 'alasan' :
		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'jenistransaksi';

		$sql = 'select count(*) as row
				from malasan
				where 1=1 '.$filter->sql;

		$st = $db->prepare($sql);
		$q = $db->execute($st, $filter->param);
		$r = $db->fetch($q);
		$result["total"] = $r->ROW;

		$sql = str_replace('count(*) as row', "first $rows skip $offset *", $sql)." order by $sort $order";
		$st = $db->prepare($sql);
		$q = $db->execute($st, $filter->param);
		while ($rs = $db->fetch($q)) {
			$result["rows"][] = $rs;
		}

		echo json_encode($result);
	break;

	case 'produksi' :
		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'a.kodebarangproduksi';

		$st = $db->prepare("select count(*) as row from mproduksi a where 1=1 $txt");
		$q = $db->execute($st, $value);
		$r = $db->fetch($q);
		$result["total"] = $r->ROW;

		$sql = "select first $rows skip $offset
					a.KODEMPRODUKSI, a.KODEBARANGPRODUKSI, a.NAMABARANGPRODUKSI, a.KETERANGAN, a.SATUAN,
					a.JML, a.HPP, a.CEKTOLERANSI, a.TOLERANSIPENERIMAAN, a.REMARK, a.USERENTRY, a.TGLENTRY, a.STATUS,
					sum(b.subtotal) as totalitem, sum(c.amount) as totalcost
				from mproduksi a
				left join mproduksidtl b on a.kodemproduksi=b.kodemproduksi
				left join mproduksidtlakun c on a.kodemproduksi=c.kodemproduksi
				where 1=1 $txt
				group by a.KODEMPRODUKSI, a.KODEBARANGPRODUKSI, a.NAMABARANGPRODUKSI, a.KETERANGAN, a.SATUAN,
						 a.JML, a.HPP, a.CEKTOLERANSI, a.TOLERANSIPENERIMAAN, a.REMARK, a.USERENTRY, a.TGLENTRY, a.STATUS
				order by $sort $order";
		/*$sql = "select
					first $rows skip $offset *
				from mproduksi
				where 1=1 $txt order by $sort $order";*/
		$st = $db->prepare($sql);
		$q = $db->execute($st, $value);
		while ($rs = $db->fetch($q)) {
			$result["rows"][] = $rs;
		}

		echo json_encode($result);
	break;

	case 'trans_akun_kas' :
		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'a.kodekas';
		$data_param = array($_SESSION['KODELOKASI']);

		if ($_POST['jenistrans']!='') {
			$jenis_kas = "and a.jeniskas=?";
			$data_param[] = $_POST['jenistrans'];
		}
		if ($_POST['kodetrans']!='') {
			$kodetrans = "and a.kodekas like '%'||?||'%'";
			$data_param[] = $_POST['kodetrans'];
		}
		if ($_POST['referensi']!='') {
			$referensi = "and a.referensi like '%'||?||'%'";
			$data_param[] = $_POST['referensi'];
		}

		$tgl_aw = $_POST['tglawal']=='' ? "and a.tgltrans>='$temp_tgl_aw'" : "and a.tgltrans>='".ubah_tgl_firebird($_POST['tglawal'])."'";
		$tgl_ak = $_POST['tglakhir']=='' ? "and a.tgltrans<='".date('d.m.Y')."'" : "and a.tgltrans<='".ubah_tgl_firebird($_POST['tglakhir'])."'";

		$status = count($_POST['status'])>0 ? "and (status='".implode("' or status='", $_POST['status'])."')" : '';

		$i = 0;
		$sql = "select a.kodekas, a.kodelokasi, b.namalokasi, a.nobuktimanual, a.tgltrans, a.tglinput,
					   a.jaminput, a.jeniskas, a.referensi, a.keterangan, a.amount,
					   a.totaldebet, a.totalkredit, a.userentry, a.status
				from tkas a
				inner join mlokasi b on a.kodelokasi = b.kodelokasi
				where a.kodelokasi = ?
					  $jenis_kas
					  $kodetrans
					  $referensi
					  $status
					  $tgl_aw $tgl_ak
				order by tgltrans desc,tglinput desc,jaminput desc";
		$st = $db->prepare($sql);
		$q = $db->execute($st, $data_param);
		while ($rs = $db->fetch($q)) {
			$result["rows"][] = $rs;
			$i++;
		}
		$result["total"] = $i;

		echo json_encode($result);
	break;

	case 'trans_saldo_awal_stok' :
		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'kodesaldostok';
		$data_param = array($_SESSION['KODELOKASI']);

		if ($_POST['kodetrans']!='') {
			$kodetrans = "and a.kodesaldostok like '%'||?||'%'";
			$data_param[] = $_POST['kodetrans'];
		}

		$i = 0;
		$sql = "select a.*, b.namagudang
				from saldostok a
				inner join mgudang b on a.kodegudang=b.kodegudang
			    where a.kodelokasi = ?
					  $kodetrans
				order by a.tgltrans desc,a.tglinput desc,a.jaminput desc";
		$st = $db->prepare($sql);
		$q = $db->execute($st, $data_param);
		while ($rs = $db->fetch($q)) {
			$result["rows"][] = $rs;
		}

		echo json_encode($result);
	break;

	case 'trans_kuitansi' :
		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'kodekuitansi';

		$data_param = array($_SESSION['KODELOKASI'], $value);
		$sql = 'select count(*) as row
				from tkuitansi
				where kodelokasi = ?';
		$st = $db->prepare($sql);
		$q  = $db->execute($st, $data_param);
		$r  = $db->fetch($q);
		$result["total"] = $r->ROW;

		$sql = "select first $rows skip $offset *
 				from tkuitansi
				where kodelokasi = ?
					  $txt
			 	order by tgltrans desc, kodekuitansi asc";
		$st = $db->prepare($sql);
		$q  = $db->execute($st, $data_param);
		while ($rs = $db->fetch($q)) {
			$result["rows"][] = $rs;
		}

		echo json_encode($result);
	break;

	case 'trans_saldo_awal_stok_konsinyasi' :
		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'kodesaldostok';
		$data_param = array();

		if ($_POST['kodetrans']!='') {
			$kodetrans = "and kodesaldostok like '%'||?||'%'";
			$data_param[] = $_POST['kodetrans'];
		}
		if ($_POST['referensi']!='') {
			if ($_POST['jeniskonsinyasi']=='MASUK')
				$referensi = "and namasupplier like '%'||?||'%'";
			else if ($_POST['jeniskonsinyasi']=='KELUAR')
				$referensi = "and namacustomer like '%'||?||'%'";

			$data_param[] = $_POST['referensi'];
		}

		$i = 0;

		if ($_POST['jeniskonsinyasi']=='MASUK'){
			$sql = "select a.*, b.namasupplier as namareferensi
					from saldostokkonsinyasi a
					inner join msupplier b on a.kodereferensi=b.kodesupplier
				    where 1=1 $kodetrans
						  $referensi
					order by a.tgltrans desc,a.tglinput desc,a.jaminput desc";
		} else if ($_POST['jeniskonsinyasi']=='KELUAR') {
			$sql = "select a.*, b.namacustomer as namareferensi
					from saldostokkonsinyasi a
					inner join mcustomer b on a.kodereferensi=b.kodecustomer
				    where 1=1 $kodetrans
						  $referensi
					order by a.tgltrans desc,a.tglinput desc,a.jaminput desc";
		} else {
			$sql = "select * from (
						select a.*, b.namasupplier as namareferensi
						from saldostokkonsinyasi a
						inner join msupplier b on a.kodereferensi=b.kodesupplier
						where 1=1
							  $kodetrans
							  $referensi

						union all

						select a.*, b.namacustomer as namareferensi
						from saldostokkonsinyasi a
						inner join mcustomer b on a.kodereferensi=b.kodecustomer
						where 1=1
							  $kodetrans
							  $referensi
					) order by tgltrans desc,tglinput desc,jaminput desc";

			$data_param = array_merge($data_param, $data_param);
		}
		$st = $db->prepare($sql);
		$q = $db->execute($st, $data_param);
		while ($rs = $db->fetch($q)) {
			$result["rows"][] = $rs;
		}

		echo json_encode($result);
	break;

	case 'trans_opname_stok' :
		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'kodeopname';
		$data_param = array();

		if ($_POST['kodetrans']!='') {
			$kodetrans = "and kodeopname like '%'||?||'%'";
			$data_param[] = $_POST['kodetrans'];
		}
		if ($_POST['lokasi']!='') {
			$lokasi = "and namalokasi  like '%'||?||'%'";
			$data_param[] = $_POST['lokasi'];
		}

		$tgl_aw = $_POST['tglawal']=='' ? "and a.tgltrans>='$temp_tgl_aw'" : "and a.tgltrans>='".ubah_tgl_firebird($_POST['tglawal'])."'";
		$tgl_ak = $_POST['tglakhir']=='' ? "and a.tgltrans<='".date('d.m.Y')."'" : "and a.tgltrans<='".ubah_tgl_firebird($_POST['tglakhir'])."'";

		$i = 0;
		$sql = "select a.*,b.namagudang
				from topnamestok a
				inner join mgudang b on a.kodegudang=b.kodegudang
			    where 1=1
					  $kodetrans
					  $lokasi
					  $tgl_aw $tgl_ak
				order by a.tgltrans desc,a.tglinput desc,a.jaminput desc";
		$st = $db->prepare($sql);
		$q = $db->execute($st, $data_param);
		while ($rs = $db->fetch($q)) {
			$result["rows"][] = $rs;
			$i++;
		}
		$result["total"] = $i;

		echo json_encode($result);
	break;

	case 'trans_pindah_stok' :
		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'kodepindah';
		$data_param = array($_SESSION['KODELOKASI']);

		if ($_POST['kodetrans']!='') {
			$kodetrans = "and kodepindah like '%'||?||'%'";
			$data_param[] = $_POST['kodetrans'];
		}

		$tgl_aw = $_POST['tglawal']=='' ? "and a.tgltrans>='$temp_tgl_aw'" : "and a.tgltrans>='".ubah_tgl_firebird($_POST['tglawal'])."'";
		$tgl_ak = $_POST['tglakhir']=='' ? "and a.tgltrans<='".date('d.m.Y')."'" : "and a.tgltrans<='".ubah_tgl_firebird($_POST['tglakhir'])."'";

		$i = 0;
		$sql = "select a.*, b.namagudang
				from tpindahbarang a
				inner join mgudang b on a.kodegudang=b.kodegudang
			    where a.kodelokasi = ?
					  $kodetrans
					  $tgl_aw $tgl_ak
				order by a.tgltrans desc,a.tglinput desc,a.jaminput desc";
		$st = $db->prepare($sql);
		$q = $db->execute($st, $data_param);
		while ($rs = $db->fetch($q)) {
			$result["rows"][] = $rs;
			$i++;
		}
		$result["total"] = $i;

		echo json_encode($result);
	break;

	case 'trans_pindah_piutang' :
		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'kodepindahpiutang';
		$data_param = array($_SESSION['KODELOKASI']);

		if ($_POST['kodetrans']!='') {
			$kodetrans = "and a.kodepindahpiutang like '%'||?||'%'";
			$data_param[] = $_POST['kodetrans'];
		}

		if ($_POST['lokasiasal']!='') {
			$lokasi = "and a.kodelokasilama like '%'||?||'%'";
			$data_param[] = $_POST['lokasiasal'];
		}

		$tgl_aw = $_POST['tglawal']=='' ? "and a.tgltrans>='$temp_tgl_aw'" : "and a.tgltrans>='".ubah_tgl_firebird($_POST['tglawal'])."'";
		$tgl_ak = $_POST['tglakhir']=='' ? "and a.tgltrans<='".date('d.m.Y')."'" : "and a.tgltrans<='".ubah_tgl_firebird($_POST['tglakhir'])."'";

		$i = 0;
		$sql = "select a.*
				from tpindahpiutang a
			    where 1=1
					  $kodetrans $lokasi
					  $tgl_aw $tgl_ak
				order by a.tgltrans desc,a.tglinput desc,a.jaminput desc";
		$st = $db->prepare($sql);
		$q = $db->execute($st, $data_param);
		while ($rs = $db->fetch($q)) {
			$result["rows"][] = $rs;
			$i++;
		}
		$result["total"] = $i;

		echo json_encode($result);
	break;

	case 'trans_pencairan_kartu_kredit' :
		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'a.kodepelunasan';
		$data_param = array();

		if ($_POST['kodetrans']!='') {
			$kodetrans = "and a.kodepelunasan like '%'||?||'%'";
			$data_param[] = $_POST['kodetrans'];
		}

		$tgl_aw = $_POST['tglawal']=='' ? "and a.tgltrans>='$temp_tgl_aw'" : "and a.tgltrans>='".ubah_tgl_firebird($_POST['tglawal'])."'";
		$tgl_ak = $_POST['tglakhir']=='' ? "and a.tgltrans<='".date('d.m.Y')."'" : "and a.tgltrans<='".ubah_tgl_firebird($_POST['tglakhir'])."'";

		$i = 0;
		$sql = "select a.*
				from pelunasanpiutangkredit a
			    where 1=1
					  $kodetrans
					  $tgl_aw $tgl_ak
				order by a.tgltrans desc,a.tglinput desc,a.jaminput desc";
		$st = $db->prepare($sql);
		$q = $db->execute($st, $data_param);
		while ($rs = $db->fetch($q)) {
			$result["rows"][] = $rs;
			$i++;
		}
		$result["total"] = $i;

		echo json_encode($result);
	break;

	case 'trans_transfer_gudang' :
		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'kodetransfergudang';
		$data_param = array();

		if ($_POST['kodetrans']!='') {
			$kodetrans = "and kodetransfergudang like '%'||?||'%'";
			$data_param[] = $_POST['kodetrans'];
		}
		if ($_POST['lokasi']!='') {
			$lokasi = "and namalokasi  like '%'||?||'%'";
			$data_param[] = $_POST['lokasi'];
		}

		$tgl_aw = $_POST['tglawal']=='' ? "and a.tgltrans>='$temp_tgl_aw'" : "and a.tgltrans>='".ubah_tgl_firebird($_POST['tglawal'])."'";
		$tgl_ak = $_POST['tglakhir']=='' ? "and a.tgltrans<='".date('d.m.Y')."'" : "and a.tgltrans<='".ubah_tgl_firebird($_POST['tglakhir'])."'";

		$i = 0;
		$sql = "select a.*, b.namagudang as namagudangasal, c.namagudang as namagudangtujuan,
		               d.username as namapenanggungjawab
				from ttransfergudang a
				inner join mgudang b on a.kodegudangasal=b.kodegudang
				inner join mgudang c on a.kodegudangtujuan=c.kodegudang
				left join muser d on a.kodepenanggungjawab=d.userid
			    where 1=1
					  $kodetrans
					  $lokasi
					  $tgl_aw $tgl_ak
				order by a.tgltrans desc,a.tglinput desc,a.jaminput desc";
		$st = $db->prepare($sql);
		$q = $db->execute($st, $data_param);
		while ($rs = $db->fetch($q)) {
			$result["rows"][] = $rs;
			$i++;
		}
		$result["total"] = $i;

		echo json_encode($result);
	break;

	case 'trans_rencana_produksi' :
		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'koderencanaproduksi';
		$data_param = array();

		if ($_POST['kodetrans']!='') {
			$kodetrans = "and koderencanaproduksi like '%'||?||'%'";
			$data_param[] = $_POST['kodetrans'];
		}
		if ($_POST['lokasi']!='') {
			$lokasi = "and namalokasi like '%'||?||'%'";
			$data_param[] = $_POST['lokasi'];
		}

		$tgl_aw = $_POST['tglawal']=='' ? "and tgltrans>='$temp_tgl_aw'" : "and tgltrans>='".ubah_tgl_firebird($_POST['tglawal'])."'";
		$tgl_ak = $_POST['tglakhir']=='' ? "and tgltrans<='".date('d.m.Y')."'" : "and tgltrans<='".ubah_tgl_firebird($_POST['tglakhir'])."'";

		$status = count($_POST['status'])>0 ? "and (status='".implode("' or status='", $_POST['status'])."')" : '';

		$i = 0;
		$sql = "select a.*, b.namalokasi, c.namabarangproduksi as namamasterproduksi, c.satuan
				from trencanaproduksi a
				inner join mlokasi b on a.kodelokasi=b.kodelokasi
				inner join mproduksi c on a.kodemasterproduksi=c.kodemproduksi
				where 1=1
					  $tgl_aw $tgl_ak
					  $kodetrans
					  $lokasi
					  $status
				order by tgltrans desc,tglinput desc,jaminput desc";
		$st = $db->prepare($sql);
		$q = $db->execute($st, $data_param);
		while ($rs = $db->fetch($q)) {
			$result["rows"][] = $rs;
			$i++;
		}
		$result["total"] = $i;

		echo json_encode($result);
	break;

	case 'trans_pr' :
		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'kodepr';
		$data_param = array();

		if ($_SESSION['LOKASIPUSAT'] == 0) {
			$lokasi = "and a.kodelokasi = ?";
			$data_param[] = $_SESSION['KODELOKASI'];
		}

		if ($_POST['kodetrans']!='') {
			$kodetrans = "and a.kodepr  like '%'||?||'%'";
			$data_param[] = $_POST['kodetrans'];
		}

		if ($_POST['lokasiterima']!='') {
			$kodelokasiterima = "and a.kodelokasiterima  like '%'||?||'%'";
			$data_param[] = $_POST['lokasiterima'];
		}

		$tgl_aw = $_POST['tglawal']=='' ? "and a.tgltrans>='$temp_tgl_aw'" : "and a.tgltrans>='".ubah_tgl_firebird($_POST['tglawal'])."'";
		$tgl_ak = $_POST['tglakhir']=='' ? "and a.tgltrans<='".date('d.m.Y')."'" : "and a.tgltrans<='".ubah_tgl_firebird($_POST['tglakhir'])."'";

		$status = count($_POST['status'])>0 ? "and (a.status='".implode("' or a.status='", $_POST['status'])."')" : '';

		$i = 0;
		$sql = "select distinct a.*, b.namacustomer,
					   d.TINDAKAN_R_SPH, d.TINDAKAN_R_CYL, d.TINDAKAN_R_AXIS, d.TINDAKAN_R_PRISM, d.TINDAKAN_R_VA, d.TINDAKAN_R_ADD, d.TINDAKAN_R_PD, d.TINDAKAN_PV,
					   d.TINDAKAN_L_SPH, d.TINDAKAN_L_CYL, d.TINDAKAN_L_AXIS, d.TINDAKAN_L_PRISM, d.TINDAKAN_L_VA, d.TINDAKAN_L_ADD, d.TINDAKAN_L_PD, d.TINDAKAN_SH,
					   (select first 1 skip 0 z.namasupplier
						from tprdtl x
						left join mbarang y on x.kodebarang = y.kodebarang
						left join msupplier z on y.kodesupplier = z.kodesupplier
						where x.kodepr = a.kodepr
					   ) as namasupplier, e.kodepo, f.kodetransfer, g.kodeterimatransfer
				from tpr a
				left join mcustomer b on a.kodecustomer = b.kodecustomer
				left join tso c on a.kodeso = c.kodeso
				left join trekammedis d on c.koderekammedis = d.koderekammedis
				left join tpodtl e on a.kodepr=e.kodepr and e.kodepo in (select kodepo from tpo where status<>'D')
				left join ttransfer f on a.kodepr=f.kodetransreferensi and f.status<>'D'
				left join tterimatransfer g on f.kodetransfer=g.kodetransreferensi and g.status<>'D'
				where 1=1 $lokasi
					  $tgl_aw $tgl_ak
					  $kodetrans $kodelokasiterima
					  $status
				order by a.tgltrans desc, a.tglinput desc, a.jaminput desc";
		$st = $db->prepare($sql);
		$q = $db->execute($st, $data_param);

		while ($rs = $db->fetch($q)) {
			$result["rows"][] = $rs;
			$i++;
		}
		$result["total"] = $i;

		echo json_encode($result);
	break;

	case 'trans_penyesuaian_stok' :
		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'kodepenyesuaian';
		$data_param = array($_SESSION['KODELOKASI']);

		if ($_POST['kodetrans']!='') {
			$kodetrans = "and a.kodepenyesuaian  like '%'||?||'%'";
			$data_param[] = $_POST['kodetrans'];
		}

		$tgl_aw = $_POST['tglawal']=='' ? "and a.tgltrans>='$temp_tgl_aw'" : "and tgltrans>='".ubah_tgl_firebird($_POST['tglawal'])."'";
		$tgl_ak = $_POST['tglakhir']=='' ? "and a.tgltrans<='".date('d.m.Y')."'" : "and tgltrans<='".ubah_tgl_firebird($_POST['tglakhir'])."'";

		$i = 0;
		$sql = "select a.*, b.namagudang
				from tpenyesuaian a
				inner join mgudang b on a.kodegudang=b.kodegudang
			    where a.kodelokasi = ?
					  $kodetrans
					  $tgl_aw $tgl_ak
				order by a.tgltrans desc,a.tglinput desc,a.jaminput desc";
		$st = $db->prepare($sql);
		$q = $db->execute($st, $data_param);
		while ($rs = $db->fetch($q)) {
			$result["rows"][] = $rs;
			$i++;
		}
		$result["total"] = $i;

		echo json_encode($result);
	break;

	case 'trans_bukti_barang_masuk' :
		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'a.kodebbm';
		$data_param = array();

		if ($_POST['kodetrans']!='') {
			$kodetrans = "and a.kodebbm like '%'||?||'%'";
			$data_param[] = $_POST['kodetrans'];
		}
		if ($_POST['lokasi']!='') {
			$lokasi = "and a.namalokasi like '%'||?||'%'";
			$data_param[] = $_POST['lokasi'];
		}

		$tgl_aw = $_POST['tglawal']=='' ? "and a.tgltrans>='$temp_tgl_aw'" : "and a.tgltrans>='".ubah_tgl_firebird($_POST['tglawal'])."'";
		$tgl_ak = $_POST['tglakhir']=='' ? "and a.tgltrans<='".date('d.m.Y')."'" : "and a.tgltrans<='".ubah_tgl_firebird($_POST['tglakhir'])."'";
		$status = count($_POST['status'])>0 ? "and (status='".implode("' or status='", $_POST['status'])."')" : '';

		$i = 0;
		$sql = "select distinct * from(
					select distinct a.*, a.jenistransaksi as jenistransaksi2, d.kodesupplier as koderef,
						   d.namasupplier as namaref, b.kodetransreferensi as kodetransreferensi2
					from tbbm a, tbbmdtl b, tpo c, msupplier d
					where a.kodebbm=b.kodebbm and
						  b.kodetransreferensi=c.kodepo and
						  c.kodesupplier=d.kodesupplier and
						  a.jenistransaksi='PO'
						  $kodetrans
						  $lokasi
						  $status
						  $tgl_aw $tgl_ak

					union all

					select distinct a.*, 'PO ASSETS' as jenistransaksi2, d.kodesupplier as koderef,
						   d.namasupplier as namaref, b.kodetransreferensi as kodetransreferensi2
					from tbbm a, tbbmdtl b, tpoaktiva c, msupplier d
					where a.kodebbm=b.kodebbm and
						  b.kodetransreferensi=c.kodepoaktiva and
						  c.kodesupplier=d.kodesupplier and
						  a.jenistransaksi='PO AKTIVA'
						  $kodetrans
						  $lokasi
						  $status
						  $tgl_aw $tgl_ak

					union all

					select distinct a.*, a.jenistransaksi as jenistransaksi2, d.kodecustomer as koderef,
						   d.namacustomer as namaref, b.kodetransreferensi as kodetransreferensi2
					from tbbm a, tbbmdtl b, torderreturjual c, mcustomer d
					where a.kodebbm=b.kodebbm and
						  b.kodetransreferensi=c.kodeorderreturjual and
						  c.kodecustomer=d.kodecustomer and
						  a.jenistransaksi='RETUR JUAL'
						  $kodetrans
						  $lokasi
						  $status
						  $tgl_aw $tgl_ak
				) order by tgltrans desc,tglinput desc,jaminput desc";
		$st = $db->prepare($sql);
		$q = $db->execute($st, array_merge($data_param, $data_param, $data_param));
		while ($rs = $db->fetch($q)) {
			$result["rows"][] = $rs;
			$i++;
		}
		$result["total"] = $i;

		echo json_encode($result);
	break;

	case 'trans_bukti_barang_keluar' :
		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'a.kodebbk';
		$data_param = array();

		if ($_POST['kodetrans']!='') {
			$kodetrans = "and a.kodebbk like '%'||?||'%'";
			$data_param[] = $_POST['kodetrans'];
		}
		if ($_POST['lokasi']!='') {
			$lokasi = "and a.namalokasi like '%'||?||'%'";
			$data_param[] = $_POST['lokasi'];
		}
		$tgl_aw = $_POST['tglawal']=='' ? "and a.tgltrans>='$temp_tgl_aw'" : "and a.tgltrans>='".ubah_tgl_firebird($_POST['tglawal'])."'";
		$tgl_ak = $_POST['tglakhir']=='' ? "and a.tgltrans<='".date('d.m.Y')."'" : "and a.tgltrans<='".ubah_tgl_firebird($_POST['tglakhir'])."'";
		$status = count($_POST['status'])>0 ? "and (status='".implode("' or status='", $_POST['status'])."')" : '';
		/*
		union all
					select distinct a.*,d.kodelokasi as koderef,d.namalokasi as namaref
					from tbbk a, tbbkdtl b, tordertransferout c, mlokasi d
					where 1=1 and a.kodebbk=b.kodebbk and b.KODETRANSREFERENSI=c.kodeordertransferout and c.kodelokasiasal=d.kodelokasi
					$kodetrans  $lokasi $tgl_aw $tgl_ak and a.jenistransaksi='TRANSFER'
		*/

		$i = 0;
		$sql = "select distinct * from(
					select distinct a.*,d.kodecustomer as koderef,d.namacustomer as namaref
                    from tbbk a, tbbkdtl b, tsoapproveppic c, tso d
                    where a.kodebbk=b.kodebbk and
						  b.KODETRANSREFERENSI=c.kodeapproveso and
						  c.kodeso=d.kodeso and
						  a.jenistransaksi='SO'
						  $kodetrans
						  $lokasi
						  $status
						  $tgl_aw $tgl_ak

					union all

					select distinct a.*,d.kodesupplier as koderef,d.namasupplier as namaref
					from tbbk a, tbbkdtl b, torderreturbeli c, msupplier d
					where a.kodebbk=b.kodebbk and
						  b.KODETRANSREFERENSI=c.kodeorderreturbeli and
						  c.kodesupplier=d.kodesupplier and
						  a.jenistransaksi='RETUR BELI'
						  $kodetrans
						  $lokasi
						  $status
						  $tgl_aw $tgl_ak
				) order by tgltrans desc,tglinput desc,jaminput desc";
		$st = $db->prepare($sql);
		$q = $db->execute($st, array_merge($data_param, $data_param));
		while ($rs = $db->fetch($q)) {
			$result["rows"][] = $rs;
			$i++;
		}
		$result["total"] = $i;

		echo json_encode($result);
	break;

	case 'trans_saldo_awal_perkiraan' :
		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'kodesaldoperkiraan';
		$data_param = array($_SESSION['KODELOKASI']);

		if ($_POST['kodetrans']!='') {
			$kodetrans = "and kodesaldoperkiraan like '%'||?||'%'";
			$data_param[] = $_POST['kodetrans'];
		}

		$i = 0;
		$sql = "select *
				from saldoperkiraan
				where 1=1 and kodelokasi=?
					  $kodetrans
				order by tgltrans desc,tglinput desc,jaminput desc";
		$st = $db->prepare($sql);
		$q = $db->execute($st, $data_param);
		while ($rs = $db->fetch($q)) {
			$result["rows"][] = $rs;
			$i++;
		}
		$result["total"] = $i;

		echo json_encode($result);
	break;

	case 'trans_saldo_awal_piutang' :
		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'kodetrans';
		$data_param = array();
		$txt ='';

		if ($_SESSION['LOKASIPUSAT'] == 0) {
			$txt .= " and a.kodelokasi = ?";
			$data_param[] = $_SESSION['KODELOKASI'];
		}else{
			if ($_POST['kodelokasi']!=''){
				$txt .= " and a.kodelokasi = ?";
				$data_param[] = $_POST['kodelokasi'];
			}
		}

		if ($_POST['kodetrans']) {
			$txt .= " and a.kodetrans like '%'||?||'%'";
			$data_param[] = $_POST['kodetrans'];
		}
		if ($_POST['namacustomer']) {
			$txt .= " and b.namacustomer like '%'||?||'%'";
			$data_param[] = $_POST['namacustomer'];
		}
		$sql = "select count(*) as row
				from kartupiutang a
				inner join mcustomer b on a.kodecustomer=b.kodecustomer
				left outer join msyaratbayar c on a.kodesyaratbayar=c.kodesyaratbayar
				left join minstansi d on a.kodeinstansi=d.kodeinstansi
				where keterangan='Saldo Awal Piutang'
					  $txt";
		$st = $db->prepare($sql);
		$q = $db->execute($st, $data_param);
		$r = $db->fetch($q);
		$result["total"] = $r->ROW;

		$sql = "select a.*, b.namacustomer, d.namainstansi, c.namasyaratbayar
				from kartupiutang a
				inner join mcustomer b on a.kodecustomer=b.kodecustomer
				left outer join msyaratbayar c on a.kodesyaratbayar=c.kodesyaratbayar
				left join minstansi d on a.kodeinstansi=d.kodeinstansi
				where keterangan='Saldo Awal Piutang' $txt
				order by $sort $order";
		//echo $sql;
		$st = $db->prepare($sql);
		$q = $db->execute($st, $data_param);
		while ($rs = $db->fetch($q)) {
			$result["rows"][] = $rs;
		}

		echo json_encode($result);
	break;

	case 'trans_saldo_awal_hutang' :
		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'kodetrans';

		if ($field=='kode') {
			$txt = "and a.kodetrans like '%'||?||'%'";
		} else if ($field=='nama') {
			$txt = "and b.namasupplier like '%'||?||'%'";
		}
		$sql = "select count(*) as row
				from kartuhutang a inner join msupplier b on a.kodesupplier=b.kodesupplier
				left outer join msyaratbayar c on a.kodesyaratbayar=c.kodesyaratbayar
				where keterangan='Saldo Awal Hutang' $txt";
		$st = $db->prepare($sql);
		$q = $db->execute($st, $value);
		$r = $db->fetch($q);
		$result["total"] = $r->ROW;

		$sql = "select a.*, b.namasupplier, c.namasyaratbayar
				from kartuhutang a
				inner join msupplier b on a.kodesupplier=b.kodesupplier
				left outer join msyaratbayar c on a.kodesyaratbayar=c.kodesyaratbayar
				where keterangan='Saldo Awal Hutang' $txt
				order by $sort $order";
		$st = $db->prepare($sql);
		$q = $db->execute($st, $value);
		while ($rs = $db->fetch($q)) {
			$result["rows"][] = $rs;
		}

		echo json_encode($result);
	break;

	case 'trans_saldo_awal_hutang_komisi' :
		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'kodetrans';

		if ($field=='kode') {
			$txt = "and a.kodetrans like '%'||?||'%'";
		} else if ($field=='nama') {
			$txt = "and b.namajurubayar like '%'||?||'%'";
		}
		$sql = "select count(*) as row
				from kartuhutangkomisi a
				inner join mjurubayar b on a.kodejurubayar = b.kodejurubayar
				where keterangan='Saldo Awal Hutang' $txt";

		$st = $db->prepare($sql);
		$q = $db->execute($st, $value);
		$r = $db->fetch($q);
		$result["total"] = $r->ROW;

		$sql = "select a.*, b.namajurubayar
				from kartuhutangkomisi a
				inner join mjurubayar b on a.kodejurubayar=b.kodejurubayar
				where keterangan='Saldo Awal Hutang' $txt
				order by $sort $order";

		$st = $db->prepare($sql);
		$q = $db->execute($st, $value);
		while ($rs = $db->fetch($q)) {
			$result["rows"][] = $rs;
		}

		echo json_encode($result);
	break;

	case 'trans_input_saldo_awal_giro' :
		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'nogiro';

		$tgl_aw = $_POST['tglawal']=='' ? "and tgltrans>='$temp_tgl_aw'" : "and tgltrans>='".ubah_tgl_firebird($_POST['tglawal'])."'";
		$tgl_ak = $_POST['tglakhir']=='' ? "and tgltrans<='".date('d.m.Y')."'" : "and tgltrans<='".ubah_tgl_firebird($_POST['tglakhir'])."'";

		$i = 0;
		$sql = "select *
				from mgiro
				where kodememo like 'SAG%'
				order by tglterima desc";
		$q = $db->query($sql);
		while ($rs = $db->fetch($q)) {
			$result["rows"][] = $rs;
			$i++;
		}
		$result["total"] = $i;

		echo json_encode($result);
	break;

	case 'trans_sales_order' :
		//$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'kodeso';
		$data_param = array($_SESSION['KODELOKASI']);

		if ($_POST['kodetrans']!='') {
			$kodetrans = "and a.kodeso like '%'||?||'%'";
			$data_param[] = $_POST['kodetrans'];
		}
		if ($_POST['nama']!='') {
			$nama = "and a.namacustomer like '%'||?||'%'";
			$data_param[] = $_POST['nama'];
		}

		$tgl_aw = $_POST['tglawal']=='' ? "and a.tgltrans>='$temp_tgl_aw'" : "and a.tgltrans>='".ubah_tgl_firebird($_POST['tglawal'])."'";
		$tgl_ak = $_POST['tglakhir']=='' ? "and a.tgltrans<='".date('d.m.Y')."'" : "and a.tgltrans<='".ubah_tgl_firebird($_POST['tglakhir'])."'";
		$status = count($_POST['status'])>0 ? "and (a.status='".implode("' or a.status='", $_POST['status'])."')" : '';

		$i = 0;
		$sql = "select distinct a.*, b.username as namapegawai_sales, c.tgllahir, d.kodemember, e.kodepr,
		               f.kodetransfer,  g.kodeterimatransfer, h.kodetransfertitipan,
					   i.kodeterimatitipan, j.kodeqc, k.kodekembalititipan, l.kodeterimakembalititipan,
					   m.kodejual as kodejual1
				from tso a
				left join muser b on a.kodepegawai_sales = b.userid
				inner join mcustomer c on a.kodecustomer = c.kodecustomer
				left join mmember d on c.kodecustomer = d.kodecustomer
				left join tpr e on e.kodeso=a.kodeso and e.status<>'D'
				left join ttransfer f on f.kodetransreferensi=e.kodepr and f.status<>'D'
				left join tterimatransfer g on g.kodetransreferensi=f.kodetransfer and g.status<>'D'
				left join ttransfertitipan h on h.kodeso=a.kodeso and h.status<>'D'
				left join tterimatitipan i on i.kodetransfertitipan=h.kodetransfertitipan and i.status<>'D'
				left join tqualitycontrol j on j.kodeso=a.kodeso and j.status<>'D'
				left join tkembalititipan k on i.kodeterimatitipan=k.kodeterimatitipan and k.status<>'D'
				left join tterimakembalititipan l on l.kodekembalititipan=k.kodekembalititipan and l.status<>'D'
				left join tjual m on m.kodeso=a.kodeso and m.status<>'D'
				where a.kodelokasi = ?
					  $kodetrans
					  $nama
					  $status
					  $tgl_aw $tgl_ak
				order by a.tgltrans desc,a.tglinput desc,a.jaminput desc";
		$st = $db->prepare($sql);
		$q = $db->execute($st, $data_param);
		while ($rs = $db->fetch($q)) {
			$result["rows"][] = $rs;
			$i++;
		}
		$result["total"] = $i;

		echo json_encode($result);
	break;

	case 'trans_quality_control' :
		//$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'kodeso';
		$data_param = array($_SESSION['KODELOKASI']);

		if ($_POST['kodetrans']!='') {
			$kodetrans = "and a.kodeso like '%'||?||'%'";
			$data_param[] = $_POST['kodetrans'];
		}
		if ($_POST['nama']!='') {
			$nama = "and a.namacustomer like '%'||?||'%'";
			$data_param[] = $_POST['nama'];
		}

		$tgl_aw = $_POST['tglawal']=='' ? "and a.tgltrans>='$temp_tgl_aw'" : "and a.tgltrans>='".ubah_tgl_firebird($_POST['tglawal'])."'";
		$tgl_ak = $_POST['tglakhir']=='' ? "and a.tgltrans<='".date('d.m.Y')."'" : "and a.tgltrans<='".ubah_tgl_firebird($_POST['tglakhir'])."'";
		$status = count($_POST['status'])>0 ? "and (a.status='".implode("' or a.status='", $_POST['status'])."')" : '';

		$i = 0;
		$sql = "select a.*, b.username as namapegawai_edger, c.kodecustomer, d.namacustomer, a.jenisframe, h.namakategori as namaframe,
					   c.tgltrans as tglso, c.tgljanji, e.username as namapegawai_sales, g.username as namapegawai_ro,
					   f.TINDAKAN_R_SPH, f.TINDAKAN_R_CYL, f.TINDAKAN_R_AXIS, f.TINDAKAN_R_PRISM, f.TINDAKAN_R_VA, f.TINDAKAN_R_ADD, f.TINDAKAN_R_PD, f.TINDAKAN_PV,
					   f.TINDAKAN_L_SPH, f.TINDAKAN_L_CYL, f.TINDAKAN_L_AXIS, f.TINDAKAN_L_PRISM, f.TINDAKAN_L_VA, f.TINDAKAN_L_ADD, f.TINDAKAN_L_PD, f.TINDAKAN_SH
				from tqualitycontrol a
				inner join muser b on a.kodepegawai_edger = b.userid
				left join tso c on a.kodeso = c.kodeso and c.status<>'D'
				left join mcustomer d on c.kodecustomer = d.kodecustomer
				left join muser e on c.kodepegawai_sales = e.userid
				left join trekammedis f on c.koderekammedis = f.koderekammedis
				left join muser g on g.userid = f.ro
				left join mkategoribarang h on a.jenisframe=h.kodekategori and h.tipe=1
				where a.kodelokasi = ?
					  $kodetrans
					  $nama
					  $lokasi
					  $status
					  $tgl_aw $tgl_ak
				order by a.tgltrans desc,a.tglinput desc,a.jaminput desc";
		$st = $db->prepare($sql);
		$q = $db->execute($st, $data_param);
		while ($rs = $db->fetch($q)) {
			$result["rows"][] = $rs;
			$i++;
		}
		$result["total"] = $i;

		echo json_encode($result);
	break;

	case 'trans_bayar_so' :
		//$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'kodeso';
		$data_param = array($_SESSION['KODELOKASI']);

		if ($_POST['kodetrans']!='') {
			$kodetrans = "and a.kodeso like '%'||?||'%'";
			$data_param[] = $_POST['kodetrans'];
		}
		if ($_POST['nama']!='') {
			$nama = "and a.namacustomer like '%'||?||'%'";
			$data_param[] = $_POST['nama'];
		}

		$tgl_aw = $_POST['tglawal']=='' ? "and a.tgltrans>='$temp_tgl_aw'" : "and a.tgltrans>='".ubah_tgl_firebird($_POST['tglawal'])."'";
		$tgl_ak = $_POST['tglakhir']=='' ? "and a.tgltrans<='".date('d.m.Y')."'" : "and a.tgltrans<='".ubah_tgl_firebird($_POST['tglakhir'])."'";
		$status = count($_POST['status'])>0 ? "and (a.status='".implode("' or a.status='", $_POST['status'])."')" : '';

		$i = 0;
		$sql = "select a.*, (grandtotal-cash-kartukredit-kartudebit-askes) as sisabayar, b.username as namapegawai_sales, c.kodemember
				from tso a
				left join muser b on a.kodepegawai_sales=b.userid
				left join mmember c on a.kodecustomer = c.kodecustomer
				where a.kodelokasi = ? and
					  (a.status='S' or a.status='P' or a.status='Q')
					  $status
					  $kodetrans
					  $nama $lokasi
					  $tgl_aw $tgl_ak
				order by a.tgltrans desc,a.tglinput desc,a.jaminput desc";
		$st = $db->prepare($sql);
		$q = $db->execute($st, $data_param);
		while ($rs = $db->fetch($q)) {
			$result["rows"][] = $rs;
			$i++;
		}
		$result["total"] = $i;

		echo json_encode($result);
	break;

	case 'trans_konsinyasi' :
		//$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'kodeso';
		$data_param = array();

		if ($_POST['kodetrans']!='') {
			$kodetrans = "and a.kodeso like '%'||?||'%'";
			$data_param[] = $_POST['kodetrans'];
		}
		if ($_POST['nama']!='') {
			$nama = "and a.namareferensi like '%'||?||'%'";
			$data_param[] = $_POST['nama'];
		}
		if ($_POST['lokasi']!='') {
			$lokasi = "and a.namalokasi like '%'||?||'%'";
			$data_param[] = $_POST['lokasi'];
		}

		$tgl_aw = $_POST['tglawal']=='' ? "and a.tgltrans>='$temp_tgl_aw'" : "and a.tgltrans>='".ubah_tgl_firebird($_POST['tglawal'])."'";
		$tgl_ak = $_POST['tglakhir']=='' ? "and a.tgltrans<='".date('d.m.Y')."'" : "and a.tgltrans<='".ubah_tgl_firebird($_POST['tglakhir'])."'";
		$status = count($_POST['status'])>0 ? "and (a.status='".implode("' or a.status='", $_POST['status'])."')" : '';

		$i = 0;
		$sql = "select a.*
				from tkonsinyasi a
				where 1=1
					  $kodetrans
					  $nama
					  $lokasi
					  $status
					  $tgl_aw $tgl_ak
				order by a.tgltrans desc,a.tglinput desc,a.jaminput desc";
		$st = $db->prepare($sql);
		$q = $db->execute($st, $data_param);
		while ($rs = $db->fetch($q)) {
			$result["rows"][] = $rs;
			$i++;
		}
		$result["total"] = $i;

		echo json_encode($result);
	break;

	case 'trans_retur_konsinyasi' :
		//$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'kodeso';
		$data_param = array();

		if ($_POST['kodetrans']!='') {
			$kodetrans = "and a.kodeso like '%'||?||'%'";
			$data_param[] = $_POST['kodetrans'];
		}
		if ($_POST['nama']!='') {
			$nama = "and a.namacustomer like '%'||?||'%'";
			$data_param[] = $_POST['nama'];
		}
		if ($_POST['lokasi']!='') {
			$lokasi = "and a.namalokasi like '%'||?||'%'";
			$data_param[] = $_POST['lokasi'];
		}

		$tgl_aw = $_POST['tglawal']=='' ? "and a.tgltrans>='$temp_tgl_aw'" : "and a.tgltrans>='".ubah_tgl_firebird($_POST['tglawal'])."'";
		$tgl_ak = $_POST['tglakhir']=='' ? "and a.tgltrans<='".date('d.m.Y')."'" : "and a.tgltrans<='".ubah_tgl_firebird($_POST['tglakhir'])."'";
		$status = count($_POST['status'])>0 ? "and (a.status='".implode("' or a.status='", $_POST['status'])."')" : '';

		$i = 0;
		$sql = "select a.*
				from treturkonsinyasi a
				where 1=1 $kodetrans $nama $lokasi $status $tgl_aw $tgl_ak
				order by a.tgltrans desc,a.tglinput desc,a.jaminput desc";
		$st = $db->prepare($sql);
		$q = $db->execute($st, $data_param);
		while ($rs = $db->fetch($q)) {
			$result["rows"][] = $rs;
			$i++;
		}
		$result["total"] = $i;

		echo json_encode($result);
	break;

	case 'trans_sales_order_approve_ppic' :
		//$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'kodeso';
		$data_param = array();

		if ($_POST['kodetrans']!='') {
			$kodetrans = "and a.KODEAPPROVESO like '%'||?||'%'";
			$data_param[] = $_POST['kodetrans'];
		}
		if ($_POST['nama']!='') {
			$nama = "and b.namacustomer like '%'||?||'%'";
			$data_param[] = $_POST['nama'];
		}

		$tgl_aw = $_POST['tglawal']=='' ? "and a.tgltrans>='$temp_tgl_aw'" : "and a.tgltrans>='".ubah_tgl_firebird($_POST['tglawal'])."'";
		$tgl_ak = $_POST['tglakhir']=='' ? "and a.tgltrans<='".date('d.m.Y')."'" : "and a.tgltrans<='".ubah_tgl_firebird($_POST['tglakhir'])."'";
		$status = count($_POST['status'])>0 ? "and (a.status='".implode("' or a.status='", $_POST['status'])."')" : '';

		$i = 0;
		$sql = "select a.*, b.namacustomer, b.kodecustomer
				from tsoapproveppic a
				left join tso b on a.kodeso=b.kodeso
				where 1=1 $kodetrans $nama $status $tgl_aw $tgl_ak order by a.tgltrans desc,a.tglinput desc,a.jaminput desc";

		$st = $db->prepare($sql);
		$q = $db->execute($st, $data_param);
		while ($rs = $db->fetch($q)) {
			$result["rows"][] = $rs;
			$i++;
		}
		$result["total"] = $i;

		echo json_encode($result);
	break;

	case 'trans_purchase_order' :
		//$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'kodepo';
		$data_param = array($_SESSION['KODELOKASI']);

		if ($_POST['kodetrans']!='') {
			$kodetrans = "and a.kodepo like '%'||?||'%'";
			$data_param[] = $_POST['kodetrans'];
		}
		if ($_POST['nama']!='') {
			$nama = "and a.namasupplier like '%'||?||'%'";
			$data_param[] = $_POST['nama'];
		}

		$tgl_aw = $_POST['tglawal']=='' ? "and a.tgltrans>='$temp_tgl_aw'" : "and a.tgltrans>='".ubah_tgl_firebird($_POST['tglawal'])."'";
		$tgl_ak = $_POST['tglakhir']=='' ? "and a.tgltrans<='".date('d.m.Y')."'" : "and a.tgltrans<='".ubah_tgl_firebird($_POST['tglakhir'])."'";
		$status = count($_POST['status'])>0 ? "and (status='".implode("' or a.status='", $_POST['status'])."')" : '';

		$i = 0;
		$sql = "select a.*, (select iif(count(b.kodepr)>0,1,0) as SP from tpodtl b where a.kodepo=b.kodepo and b.kodepr<>'') as SP
				from tpo a
				where a.kodelokasi = ?
					  $kodetrans
					  $status
					  $nama
					  $tgl_aw $tgl_ak
				order by a.tgltrans desc,a.kodepo desc, a.tglinput desc,jaminput desc";
		$st = $db->prepare($sql);
		$q = $db->execute($st, $data_param);
		while ($rs = $db->fetch($q)) {
			$result["rows"][] = $rs;
			$i++;
		}
		$result["total"] = $i;

		echo json_encode($result);
	break;

	case 'trans_penerimaan_barang' :
		//$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'kodepo';
		$data_param = array($_SESSION['KODELOKASI']);

		if ($_POST['kodetrans']!='') {
			$kodetrans = "and a.kodepenerimaan like '%'||?||'%'";
			$data_param[] = $_POST['kodetrans'];
		}

		if ($_POST['kodepo']!='') {
			$kodetrans = "and a.kodepo like '%'||?||'%'";
			$data_param[] = $_POST['kodepo'];
		}

		if ($_POST['nama']!='') {
			$nama = "and a.namasupplier like '%'||?||'%'";
			$data_param[] = $_POST['nama'];
		}

		$tgl_aw = $_POST['tglawal']=='' ? "and a.tgltrans>='$temp_tgl_aw'" : "and a.tgltrans>='".ubah_tgl_firebird($_POST['tglawal'])."'";
		$tgl_ak = $_POST['tglakhir']=='' ? "and a.tgltrans<='".date('d.m.Y')."'" : "and a.tgltrans<='".ubah_tgl_firebird($_POST['tglakhir'])."'";
		$status = count($_POST['status'])>0 ? "and (a.status='".implode("' or a.status='", $_POST['status'])."')" : '';

		$i = 0;
		$sql = "select a.*, (select count(kodebarang) from tpenerimaandtl where kodepenerimaan = a.kodepenerimaan) as banyakbarang,
					   (select sum(jml) from tpenerimaandtl where kodepenerimaan = a.kodepenerimaan) as jumlahbarang
				from tpenerimaan a
				where a.kodelokasi = ?
					  $kodetrans
					  $status
					  $nama
					  $tgl_aw $tgl_ak
				order by a.tgltrans desc, a.tglinput desc, a.jaminput desc";
		$st = $db->prepare($sql);
		$q = $db->execute($st, $data_param);
		while ($rs = $db->fetch($q)) {
			$result["rows"][] = $rs;
			$i++;
		}
		$result["total"] = $i;

		echo json_encode($result);
	break;

	case 'trans_pembelian' :
		//$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'kodebeli';
		$data_param = array($_SESSION['KODELOKASI']);

		if ($_POST['kodetrans']!='') {
			$kodetrans = "and a.kodebeli like '%'||?||'%'";
			$data_param[] = $_POST['kodetrans'];
		}
		if ($_POST['nama']!='') {
			$nama = "and a.namasupplier like '%'||?||'%'";
			$data_param[] = $_POST['nama'];
		}
		if ($_POST['kodetrans_po']!='') {
			$po = "and c.kodepo like '%'||?||'%'";
			$data_param[] = $_POST['kodetrans_po'];
		}

		$tgl_aw = $_POST['tglawal']=='' ? "and a.tgltrans>='$temp_tgl_aw'" : "and a.tgltrans>='".ubah_tgl_firebird($_POST['tglawal'])."'";
		$tgl_ak = $_POST['tglakhir']=='' ? "and a.tgltrans<='".date('d.m.Y')."'" : "and a.tgltrans<='".ubah_tgl_firebird($_POST['tglakhir'])."'";
		$status = count($_POST['status'])>0 ? "and (a.status='".implode("' or a.status='", $_POST['status'])."')" : '';

		$i = 0;
		$sql = "select a.*, b.namaperkiraan as namaperkiraanbiaya, c.kodepo
				from tbeli a
				left join mperkiraan b on a.kodeperkiraanbiaya=b.kodeperkiraan
				left join tpenerimaan c on a.kodepenerimaan = c.kodepenerimaan
				where a.kodelokasi = ?
					  $kodetrans
					  $nama
					  $status
					  $po
					  $tgl_aw $tgl_ak
				order by a.tgltrans desc, a.tglinput desc, a.jaminput desc";
		$st = $db->prepare($sql);
		$q = $db->execute($st, $data_param);
		while ($rs = $db->fetch($q)) {
			$result["rows"][] = $rs;
			$i++;
		}
		$result["total"] = $i;

		echo json_encode($result);
	break;

	case 'trans_pembelian_aktiva' :
		//$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'kodebeli';
		$data_param = array();

		if ($_POST['kodetrans']!='') {
			$kodetrans = "and kodebeliaktiva like '%'||?||'%'";
			$data_param[] = $_POST['kodetrans'];
		}
		if ($_POST['nama']!='') {
			$nama = "and namasupplier like '%'||?||'%'";
			$data_param[] = $_POST['nama'];
		}

		$tgl_aw = $_POST['tglawal']=='' ? "and tgltrans>='$temp_tgl_aw'" : "and tgltrans>='".ubah_tgl_firebird($_POST['tglawal'])."'";
		$tgl_ak = $_POST['tglakhir']=='' ? "and tgltrans<='".date('d.m.Y')."'" : "and tgltrans<='".ubah_tgl_firebird($_POST['tglakhir'])."'";
		$status = count($_POST['status'])>0 ? "and (status='".implode("' or status='", $_POST['status'])."')" : '';

		$i = 0;
		$sql = "select *
				from tbeliaktiva
				where 1=1
					  $kodetrans
					  $nama
					  $status
					  $tgl_aw $tgl_ak
				order by tgltrans desc,tglinput desc,jaminput desc";

		$st = $db->prepare($sql);
		$q = $db->execute($st, $data_param);
		while ($rs = $db->fetch($q)) {
			$result["rows"][] = $rs;
			$i++;
		}
		$result["total"] = $i;

		echo json_encode($result);
	break;

	case 'trans_retur_pembelian' :
		//$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'kodereturbeli';
		$data_param = array($_SESSION['KODELOKASI']);

		if ($_POST['kodetrans']!='') {
			$kodetrans = "and kodereturbeli like '%'||?||'%'";
			$data_param[] = $_POST['kodetrans'];
		}
		if ($_POST['nama']!='') {
			$nama = "and namasupplier like '%'||?||'%'";
			$data_param[] = $_POST['nama'];
		}

		$tgl_aw = $_POST['tglawal']=='' ? "and tgltrans>='$temp_tgl_aw'" : "and tgltrans>='".ubah_tgl_firebird($_POST['tglawal'])."'";
		$tgl_ak = $_POST['tglakhir']=='' ? "and tgltrans<='".date('d.m.Y')."'" : "and tgltrans<='".ubah_tgl_firebird($_POST['tglakhir'])."'";
		$status = count($_POST['status'])>0 ? "and (status='".implode("' or status='", $_POST['status'])."')" : '';

		$i = 0;
		$sql = "select *
				from treturbeli
				where kodelokasi = ?
					  $kodetrans
					  $nama
					  $status
					  $tgl_aw $tgl_ak
				order by tgltrans desc,tglinput desc,jaminput desc";
		$st = $db->prepare($sql);
		$q = $db->execute($st, $data_param);
		while ($rs = $db->fetch($q)) {
			$result["rows"][] = $rs;
			$i++;
		}
		$result["total"] = $i;

		echo json_encode($result);
	break;

	case 'trans_rekam_medis' :
		//$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'kodejual';
		$data_param = array();

		if ($_POST['kodetrans']!='') {
			$kodetrans = "and a.koderekammedis like '%'||?||'%'";
			$data_param[] = $_POST['kodetrans'];
		}
		if ($_POST['nama']!='') {
			$nama = "and b.namacustomer like '%'||?||'%'";
			$data_param[] = $_POST['nama'];
		}
		$tgl_aw = $_POST['tglawal']=='' ? "and a.tgltrans>='$temp_tgl_aw'" : "and a.tgltrans>='".ubah_tgl_firebird($_POST['tglawal'])."'";
		$tgl_ak = $_POST['tglakhir']=='' ? "and a.tgltrans<='".date('d.m.Y')."'" : "and a.tgltrans<='".ubah_tgl_firebird($_POST['tglakhir'])."'";
		$status = count($_POST['status'])>0 ? "and (a.status='".implode("' or a.status='", $_POST['status'])."')" : '';

		$i = 0;
		$sql = "select first 30 skip 0 a.*, b.namacustomer
				from trekammedis a
				inner join mcustomer b on a.kodecustomer=b.kodecustomer
				where 1=1
					  $kodetrans
					  $nama
					  $status
					  $tgl_aw
					  $tgl_ak
				order by a.tgltrans desc, a.tglinput desc, a.jaminput desc";
		$st = $db->prepare($sql);
		$q = $db->execute($st, $data_param);
		while ($rs = $db->fetch($q)) {
			$result["rows"][] = $rs;
			$i++;
		}
		$result["total"] = $i;

		echo json_encode($result);
	break;

	case 'trans_penjualan' :
		//$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'kodejual';
		$data_param = array($_SESSION['KODELOKASI']);

		if ($_POST['kodetrans']!='') {
			$kodetrans = "and a.kodejual like '%'||?||'%'";
			$data_param[] = $_POST['kodetrans'];
		}
		if ($_POST['nama']!='') {
			$nama = "and a.namacustomer like '%'||?||'%'";
			$data_param[] = $_POST['nama'];
		}
		$tgl_aw = $_POST['tglawal']=='' ? "and a.tgltrans>='$temp_tgl_aw'" : "and a.tgltrans>='".ubah_tgl_firebird($_POST['tglawal'])."'";
		$tgl_ak = $_POST['tglakhir']=='' ? "and a.tgltrans<='".date('d.m.Y')."'" : "and a.tgltrans<='".ubah_tgl_firebird($_POST['tglakhir'])."'";
		$status = count($_POST['status'])>0 ? "and (a.status='".implode("' or a.status='", $_POST['status'])."')" : '';

		$i = 0;
		$sql = "select a.*, a.uangmuka as downpayment, (a.grandtotal - a.uangmuka-a.cash-a.kartukredit-a.kartudebit-a.askes) as sisabayar, c.username as namasalesman, d.username as namaro,
					   e.username as namasetel, f.username as namaedger, g.username as namafitting, h.tipeorder, h.tgltrans as tglpesan, h.tgljanji, i.kodemember
				from tjual a 
				left join mcustomer b on a.kodecustomer=b.kodecustomer
				left join muser c on a.kodepegawai_sales=c.userid
				left join muser d on a.kodepegawai_ro=d.userid
				left join muser e on a.kodepegawai_setel=e.userid
				left join muser f on a.kodepegawai_edger=f.userid
				left join muser g on a.kodepegawai_edger=g.userid
				left join tso h on a.kodeso=h.kodeso and h.status<>'D'
				left join mmember i on b.kodecustomer=i.kodecustomer
				where a.kodelokasi=?
					  $kodetrans
					  $nama $tgl_aw $tgl_ak
				order by a.tgltrans desc, a.kodejual desc";
		$st = $db->prepare($sql);
		$q = $db->execute($st, $data_param);
		while ($rs = $db->fetch($q)) {
			$result["rows"][] = $rs;
			$i++;
		}
		$result["total"] = $i;

		echo json_encode($result);
	break;

	case 'trans_follow_up' :
		//$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'kodejual';
		$data_param = array($_SESSION['KODELOKASI']);

		if ($_POST['kodetrans']!='') {
			$kodetrans = "and a.kodejual like '%'||?||'%'";
			$data_param[] = $_POST['kodetrans'];
		}
		if ($_POST['nama']!='') {
			$nama = "and c.namacustomer like '%'||?||'%'";
			$data_param[] = $_POST['nama'];
		}

		$tgl_aw = $_POST['tglawal']=='' ? "and a.tgltrans>='$temp_tgl_aw'" : "and a.tgltrans>='".ubah_tgl_firebird($_POST['tglawal'])."'";
		$tgl_ak = $_POST['tglakhir']=='' ? "and a.tgltrans<='".date('d.m.Y')."'" : "and a.tgltrans<='".ubah_tgl_firebird($_POST['tglakhir'])."'";
		$status = count($_POST['status'])>0 ? "and (a.status='".implode("' or a.status='", $_POST['status'])."')" : '';

		$i = 0;
		$sql = "select a.*, c.namacustomer, b.tgltrans as tgljual
				from tfollowup a
				inner join tjual b on a.kodejual = b.kodejual
				inner join mcustomer c on b.kodecustomer=c.kodecustomer
				where a.kodelokasi=?
					  $kodetrans $nama
					  $tgl_aw $tgl_aK $status
				order by a.tgltrans desc, a.kodejual desc";
		$st = $db->prepare($sql);
		$q = $db->execute($st, $data_param);
		while ($rs = $db->fetch($q)) {
			if (selisih_hari($rs->TGLTRANS, date("Y-m-d"))>14) {
				$result["rows"][] = $rs;
			}

			$i++;
		}
		$result["total"] = $i;

		echo json_encode($result);
	break;

	case 'trans_pemakaian' :
		//$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'kodepakai';
		$data_param = array();

		if ($_POST['kodetrans']!='') {
			$kodetrans = "and a.kodepakai like '%'||?||'%'";
			$data_param[] = $_POST['kodetrans'];
		}
		if ($_POST['lokasi']!='') {
			$kodetrans = "and a.namalokasi like '%'||?||'%'";
			$data_param[] = $_POST['lokasi'];
		}

		$tgl_aw = $_POST['tglawal']=='' ? "and a.tgltrans>='$temp_tgl_aw'" : "and a.tgltrans>='".ubah_tgl_firebird($_POST['tglawal'])."'";
		$tgl_ak = $_POST['tglakhir']=='' ? "and a.tgltrans<='".date('d.m.Y')."'" : "and a.tgltrans<='".ubah_tgl_firebird($_POST['tglakhir'])."'";
		$status = count($_POST['status'])>0 ? "and (a.status='".implode("' or a.status='", $_POST['status'])."')" : '';

		$i = 0;
		$sql = "select a.*, b.namalokasi
				from tpakai a
				left join mlokasi b on a.kodelokasi=b.kodelokasi
				where 1=1
					  $kodetrans
					  $lokasi
					  $status
					  $tgl_aw
					  $tgl_ak
				order by a.tgltrans desc, a.tglinput desc, a.jaminput desc";
		$st = $db->prepare($sql);
		$q = $db->execute($st, $data_param);
		while ($rs = $db->fetch($q)) {
			$result["rows"][] = $rs;
			$i++;
		}
		$result["total"] = $i;

		echo json_encode($result);
	break;

	case 'trans_retur_penjualan' :
		//$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'kodereturjual';
		$data_param = array($_SESSION['KODELOKASI']);

		if ($_POST['kodetrans']!='') {
			$kodetrans = "and a.kodereturjual like '%'||?||'%'";
			$data_param[] = $_POST['kodetrans'];
		}
		if ($_POST['nama']!='') {
			$nama = "and a.namacustomer like '%'||?||'%'";
			$data_param[] = $_POST['nama'];
		}
		if ($_POST['lokasi']!='') {
			$lokasi = "and a.namalokasi like '%'||?||'%'";
			$data_param[] = $_POST['lokasi'];
		}

		$tgl_aw = $_POST['tglawal']=='' ? "and a.tgltrans>='$temp_tgl_aw'" : "and a.tgltrans>='".ubah_tgl_firebird($_POST['tglawal'])."'";
		$tgl_ak = $_POST['tglakhir']=='' ? "and a.tgltrans<='".date('d.m.Y')."'" : "and a.tgltrans<='".ubah_tgl_firebird($_POST['tglakhir'])."'";
		$status = count($_POST['status'])>0 ? "and (a.status='".implode("' or a.status='", $_POST['status'])."')" : '';

		$i = 0;
		$sql = "select a.*, c.username as namapenanggungjawab
				from treturjual a
				inner join mcustomer b on a.kodecustomer=b.kodecustomer
				left join muser c on a.kodepenanggungjawab = c.userid
				where a.kodelokasi=?
					  $kodetrans
					  $nama
					  $status
					  $tgl_aw $tgl_ak
				order by a.tgltrans desc, a.tglinput desc, a.jaminput desc";
		$st = $db->prepare($sql);
		$q = $db->execute($st, $data_param);
		while ($rs = $db->fetch($q)) {
			$result["rows"][] = $rs;
			$i++;
		}
		$result["total"] = $i;

		echo json_encode($result);
	break;

	case 'trans_transfer' :
		//$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'kodetransferin';
		$data_param = array();
		if ($_POST['kodetrans']!='') {
			$kodetrans = "and a.kodetransfer like '%'||?||'%'";
			$data_param[] = $_POST['kodetrans'];
		}
		if ($_POST['kodesp']!='') {
			$kodesp = "and c.kodepr like '%'||?||'%'";
			$data_param[] = $_POST['kodesp'];
		}	
		if ($_POST['kodeso']!='') {		
			$kodeso = "and d.kodeso like '%'||?||'%'";
			$data_param[] = $_POST['kodeso'];
		}	
		
		if ($_POST['lokasiasal']!='') {
			$lokasiasal = "and a.kodelokasiasal =?";
			$data_param[] = $_SESSION['lokasiasal'];
		}else{
			$lokasiasal = "and a.kodelokasiasal =?";
			$data_param[] = $_SESSION['KODELOKASI'];
		}	

		if ($_POST['lokasitujuan']!='') {
			$lokasitujuan = "and a.kodelokasitujuan like '%'||?||'%'";
			$data_param[] = $_POST['lokasitujuan'];
		}

		$tgl_aw = $_POST['tglawal']=='' ? "and a.tgltrans>='$temp_tgl_aw'" : "and a.tgltrans>='".ubah_tgl_firebird($_POST['tglawal'])."'";
		$tgl_ak = $_POST['tglakhir']=='' ? "and a.tgltrans<='".date('d.m.Y')."'" : "and a.tgltrans<='".ubah_tgl_firebird($_POST['tglakhir'])."'";
		$status = count($_POST['status'])>0 ? "and (a.status='".implode("' or a.status='", $_POST['status'])."')" : '';

		$i = 0;
		$sql = "select a.*, d.kodeso, b.kodeterimatransfer, b.tgltrans as tglterimatransfer, b.kodegudangtujuan
				from ttransfer a
				left join tterimatransfer b on a.kodetransfer=b.kodetransreferensi and b.status<>'D'
				left join tpr c on a.kodetransreferensi=c.kodepr and c.status<>'D'
				left join tso d on c.kodeso=d.kodeso and d.status<>'D'
				where 1=1
					  $kodetrans $kodesp $kodeso
					  $lokasiasal
					  $lokasitujuan
					  $status
					  $tgl_aw $tgl_ak
				order by tgltrans desc,kodetransfer desc,tglinput desc,jaminput desc";
		$st = $db->prepare($sql);
		$q = $db->execute($st, $data_param);
		while ($rs = $db->fetch($q)) {
			$result["rows"][] = $rs;
			$i++;
		}
		$result["total"] = $i;

		echo json_encode($result);
	break;

	case 'trans_terima_transfer' :
		//$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'kodetransferin';
		$data_param = array();

		if ($_POST['kodetrans']!='') {
			$kodetrans = "and a.kodeterimatransfer like '%'||?||'%'";
			$data_param[] = $_POST['kodetrans'];
		}
		if ($_POST['lokasiasal']!='') {
			$lokasiasal = "and a.namalokasiasal like '%'||?||'%'";
			$data_param[] = $_POST['lokasiasal'];
		}

		$lokasitujuan = "and a.kodelokasitujuan =?";
		$data_param[] = $_SESSION['KODELOKASI'];

		$tgl_aw = $_POST['tglawal']=='' ? "and a.tgltrans>='$temp_tgl_aw'" : "and a.tgltrans>='".ubah_tgl_firebird($_POST['tglawal'])."'";
		$tgl_ak = $_POST['tglakhir']=='' ? "and a.tgltrans<='".date('d.m.Y')."'" : "and a.tgltrans<='".ubah_tgl_firebird($_POST['tglakhir'])."'";
		$status = count($_POST['status'])>0 ? "and (a.status='".implode("' or a.status='", $_POST['status'])."')" : '';

		$i = 0;
		$sql = "select a.*, b.kodetransreferensi as kodepr, d.kodeso
				from tterimatransfer a
				left join ttransfer b on a.kodetransreferensi=b.kodetransfer and b.status<>'D'
				left join tpr c on b.kodetransreferensi=c.kodepr and c.status<>'D'
				left join tso d on c.kodeso=d.kodeso and d.status<>'D'
				where 1=1
					  $kodetrans
					  $lokasiasal
					  $lokasitujuan
					  $status
					  $tgl_aw $tgl_ak
				order by tgltrans desc,tglinput desc,jaminput desc";
		$st = $db->prepare($sql);
		$q = $db->execute($st, $data_param);
		while ($rs = $db->fetch($q)) {
			$result["rows"][] = $rs;
			$i++;
		}
		$result["total"] = $i;

		echo json_encode($result);
	break;

	case 'trans_transfer_titipan' :
		//$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'kodetransferin';
		$data_param = array($_SESSION['KODELOKASI']);

		if ($_POST['kodetrans']!='') {
			$kodetrans = "and a.kodetransfertitipan like '%'||?||'%'";
			$data_param[] = $_POST['kodetrans'];
		}

		if ($_POST['lokasitujuan']!='') {
			$lokasitujuan = "and a.namalokasitujuan like '%'||?||'%'";
			$data_param[] = $_POST['lokasitujuan'];
		}

		$tgl_aw = $_POST['tglawal']=='' ? "and a.tgltrans>='$temp_tgl_aw'" : "and a.tgltrans>='".ubah_tgl_firebird($_POST['tglawal'])."'";
		$tgl_ak = $_POST['tglakhir']=='' ? "and a.tgltrans<='".date('d.m.Y')."'" : "and a.tgltrans<='".ubah_tgl_firebird($_POST['tglakhir'])."'";
		$status = count($_POST['status'])>0 ? "and (a.status='".implode("' or a.status='", $_POST['status'])."')" : '';

		$i = 0;
		$sql = "select a.*, b.kodeterimatitipan, c.kodekembalititipan, d.kodeterimakembalititipan
				from ttransfertitipan a
				left join tterimatitipan b on a.kodetransfertitipan = b.kodetransfertitipan and b.status <> 'D'
				left join tkembalititipan c on b.kodeterimatitipan = c.kodeterimatitipan and c.status <> 'D'
				left join tterimakembalititipan d on c.kodekembalititipan = d.kodekembalititipan and d.status <> 'D'
				where a.kodelokasiasal=?
					  $kodetrans
					  $lokasitujuan
					  $status
					  $tgl_aw $tgl_ak
				order by a.tgltrans desc,a.tglinput desc,a.jaminput desc";
		$st = $db->prepare($sql);
		$q = $db->execute($st, $data_param);
		while ($rs = $db->fetch($q)) {
			$result["rows"][] = $rs;
			$i++;
		}
		$result["total"] = $i;

		echo json_encode($result);
	break;

	case 'trans_terima_titipan' :
		//$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'kodeterimatitipan';
		$data_param = array($_SESSION['KODELOKASI']);

		if ($_POST['kodetrans']!='') {
			$kodetrans = "and a.kodeterimatitipan like '%'||?||'%'";
			$data_param[] = $_POST['kodetrans'];
		}
		if ($_POST['lokasiasal']!='') {
			$lokasiasal = "and a.namalokasiasal like '%'||?||'%'";
			$data_param[] = $_POST['lokasiasal'];
		}

		$tgl_aw = $_POST['tglawal']=='' ? "and a.tgltrans>='$temp_tgl_aw'" : "and a.tgltrans>='".ubah_tgl_firebird($_POST['tglawal'])."'";
		$tgl_ak = $_POST['tglakhir']=='' ? "and a.tgltrans<='".date('d.m.Y')."'" : "and a.tgltrans<='".ubah_tgl_firebird($_POST['tglakhir'])."'";
		$status = count($_POST['status'])>0 ? "and (a.status='".implode("' or a.status='", $_POST['status'])."')" : '';

		$i = 0;
		$sql = "select a.*, b.kodeso, d.namacustomer
				from tterimatitipan a
				inner join ttransfertitipan b on a.kodetransfertitipan=b.kodetransfertitipan and b.status<>'D'
				inner join tso c on b.kodeso=c.kodeso and c.status<>'D'
				inner join mcustomer d on c.kodecustomer=d.kodecustomer
				where a.kodelokasitujuan = ?
					  $kodetrans
					  $lokasiasal
					  $status
					  $tgl_aw $tgl_ak
				order by a.tgltrans desc,a.tglinput desc,a.jaminput desc";
		$st = $db->prepare($sql);
		$q = $db->execute($st, $data_param);
		while ($rs = $db->fetch($q)) {
			$result["rows"][] = $rs;
			$i++;
		}
		$result["total"] = $i;

		echo json_encode($result);
	break;

	case 'trans_pengembalian_titipan' :
		//$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'kodekembalititipan';
		$data_param = array($_SESSION['KODELOKASI']);

		if ($_POST['kodetrans']!='') {
			$kodetrans = "and a.kodekembalititipan like '%'||?||'%'";
			$data_param[] = $_POST['kodetrans'];
		}
		if ($_POST['lokasitujuan']!='') {
			$lokasitujuan = "and a.namalokasitujuan like '%'||?||'%'";
			$data_param[] = $_POST['lokasitujuan'];
		}

		$tgl_aw = $_POST['tglawal']=='' ? "and a.tgltrans>='$temp_tgl_aw'" : "and a.tgltrans>='".ubah_tgl_firebird($_POST['tglawal'])."'";
		$tgl_ak = $_POST['tglakhir']=='' ? "and a.tgltrans<='".date('d.m.Y')."'" : "and a.tgltrans<='".ubah_tgl_firebird($_POST['tglakhir'])."'";
		$status = count($_POST['status'])>0 ? "and (a.status='".implode("' or a.status='", $_POST['status'])."')" : '';

		$i = 0;
		$sql = "select a.*, c.kodeso, e.namacustomer
				from tkembalititipan a
				inner join tterimatitipan b on a.kodeterimatitipan=b.kodeterimatitipan and b.status<>'D'
				inner join ttransfertitipan c on b.kodetransfertitipan=c.kodetransfertitipan and c.status<>'D'
				inner join tso d on c.kodeso=d.kodeso and d.status<>'D'
				inner join mcustomer e on d.kodecustomer=e.kodecustomer
				where a.kodelokasiasal=?
					  $kodetrans
					  $lokasitujuan
					  $status
					  $tgl_aw $tgl_ak
				order by a.tgltrans desc,a.tglinput desc,a.jaminput desc";
		$st = $db->prepare($sql);
		$q = $db->execute($st, $data_param);
		while ($rs = $db->fetch($q)) {
			$result["rows"][] = $rs;
			$i++;
		}
		$result["total"] = $i;

		echo json_encode($result);
	break;

	case 'trans_terima_kembali_titipan' :
		//$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'kodeterimakembalititipan';
		$data_param = array($_SESSION['KODELOKASI']);

		if ($_POST['kodetrans']!='') {
			$kodetrans = "and a.kodeterimakembalititipan like '%'||?||'%'";
			$data_param[] = $_POST['kodetrans'];
		}
		if ($_POST['lokasiasal']!='') {
			$lokasiasal = "and a.namalokasiasal like '%'||?||'%'";
			$data_param[] = $_POST['lokasiasal'];
		}

		$tgl_aw = $_POST['tglawal']=='' ? "and a.tgltrans>='$temp_tgl_aw'" : "and a.tgltrans>='".ubah_tgl_firebird($_POST['tglawal'])."'";
		$tgl_ak = $_POST['tglakhir']=='' ? "and a.tgltrans<='".date('d.m.Y')."'" : "and a.tgltrans<='".ubah_tgl_firebird($_POST['tglakhir'])."'";
		$status = count($_POST['status'])>0 ? "and (a.status='".implode("' or a.status='", $_POST['status'])."')" : '';

		$i = 0;
		$sql = "select a.*, d.kodeso, f.namacustomer
				from tterimakembalititipan a
				inner join tkembalititipan b on a.kodekembalititipan=b.kodekembalititipan and b.status<>'D'
				inner join tterimatitipan c on b.kodeterimatitipan=c.kodeterimatitipan and c.status<>'D'
				inner join ttransfertitipan d on c.kodetransfertitipan=d.kodetransfertitipan and d.status<>'D'
				inner join tso e on d.kodeso=e.kodeso and e.status<>'D'
				inner join mcustomer f on e.kodecustomer=f.kodecustomer
				where a.kodelokasitujuan=?
					  $kodetrans
					  $lokasiasal
					  $status
					  $tgl_aw $tgl_ak
				order by a.tgltrans desc,a.tglinput desc,a.jaminput desc";
		$st = $db->prepare($sql);
		$q = $db->execute($st, $data_param);
		while ($rs = $db->fetch($q)) {
			$result["rows"][] = $rs;
			$i++;
		}
		$result["total"] = $i;

		echo json_encode($result);
	break;

	case 'trans_order_retur_jual' :
		//$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'kodeorderreturjual';
		$data_param = array();

		if ($_POST['kodetrans']!='') {
			$kodetrans = "and kodeorderreturjual like '%'||?||'%'";
			$data_param[] = $_POST['kodetrans'];
		}
		if ($_POST['nama']!='') {
			$nama = "and namacustomer like '%'||?||'%'";
			$data_param[] = $_POST['nama'];
		}
		if ($_POST['lokasi']!='') {
			$lokasi = "and namacustomer like '%'||?||'%'";
			$data_param[] = $_POST['lokasi'];
		}
		$tgl_aw = $_POST['tglawal']=='' ? "and tgltrans>='$temp_tgl_aw'" : "and tgltrans>='".ubah_tgl_firebird($_POST['tglawal'])."'";
		$tgl_ak = $_POST['tglakhir']=='' ? "and tgltrans<='".date('d.m.Y')."'" : "and tgltrans<='".ubah_tgl_firebird($_POST['tglakhir'])."'";
		$status = count($_POST['status'])>0 ? "and (status='".implode("' or status='", $_POST['status'])."')" : '';

		$i = 0;
		$sql = "select *
				from torderreturjual
				where 1=1
					  $kodetrans
					  $nama
					  $lokasi
					  $status
					  $tgl_aw $tgl_ak
				order by tgltrans desc,tglinput desc,jaminput desc";
		$st = $db->prepare($sql);
		$q = $db->execute($st, $data_param);
		while ($rs = $db->fetch($q)) {
			$result["rows"][] = $rs;
			$i++;
		}
		$result["total"] = $i;

		echo json_encode($result);
	break;

	case 'trans_order_retur_beli' :
		//$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'kodeorderreturbeli';
		$data_param = array();

		if ($_POST['kodetrans']!='') {
			$kodetrans = "and kodeorderreturbeli like '%'||?||'%'";
			$data_param[] = $_POST['kodetrans'];
		}
		if ($_POST['nama']!='') {
			$nama = "and namasupplier like '%'||?||'%'";
			$data_param[] = $_POST['nama'];
		}
		if ($_POST['lokasi']!='') {
			$lokasi = "and namalokasi like '%'||?||'%'";
			$data_param[] = $_POST['lokasi'];
		}

		$tgl_aw = $_POST['tglawal']=='' ? "and tgltrans>='$temp_tgl_aw'" : "and tgltrans>='".ubah_tgl_firebird($_POST['tglawal'])."'";
		$tgl_ak = $_POST['tglakhir']=='' ? "and tgltrans<='".date('d.m.Y')."'" : "and tgltrans<='".ubah_tgl_firebird($_POST['tglakhir'])."'";
		$status = count($_POST['status'])>0 ? "and (status='".implode("' or status='", $_POST['status'])."')" : '';

		$i = 0;
		$sql = "select *
				from torderreturbeli
				where 1=1
					  $kodetrans
					  $nama
					  $lokasi
					  $status
					  $tgl_aw $tgl_ak
				order by tgltrans desc,tglinput desc,jaminput desc";
		$st = $db->prepare($sql);
		$q = $db->execute($st, $data_param);
		while ($rs = $db->fetch($q)) {
			$result["rows"][] = $rs;
			$i++;
		}
		$result["total"] = $i;

		echo json_encode($result);
	break;

	case 'rumus_poin' :
		$i = 0;
		$sql = "select *
				from mrumuspoin
				where jenislokasi = '".$_SESSION['JENISLOKASI']."'
				order by tgltrans desc, tglinput desc, jaminput desc";
		$st = $db->prepare($sql);
		$q = $db->execute($st, array());
		while ($rs = $db->fetch($q)) {
			$result["rows"][] = $rs;
			$i++;
		}
		$result["total"] = $i;

		echo json_encode($result);
	break;

	case 'trans_credit_note' :
		$kodetrans = $_POST['kodetrans']=='' ? '' : "and kodecreditnote like '%'||?||'%'";
		$nama      = $_POST['nama']=='' ? '' : "and namacustomer like '%'||?||'%'";
		$tgl_aw    = $_POST['tglawal']=='' ? "and tgltrans>='$temp_tgl_aw'" : "and tgltrans>='".ubah_tgl_firebird($_POST['tglawal'])."'";
		$tgl_ak    = $_POST['tglakhir']=='' ? "and tgltrans<='".date('d.m.Y')."'" : "and tgltrans<='".ubah_tgl_firebird($_POST['tglakhir'])."'";

		$i = 0;
		$sql = "select a.*, b.namacustomer
				from tcreditnote a
				inner join mcustomer b on a.kodecustomer=b.kodecustomer
				where 1=1 $kodetrans
					  $tgl_aw $tgl_ak $nama
				order by tgltrans desc,tglinput desc,jaminput desc";
		$st = $db->prepare($sql);
		$q = $db->execute($st, array($_POST['kodetrans'], $_POST['nama']));
		while ($rs = $db->fetch($q)) {
			$result["rows"][] = $rs;
			$i++;
		}
		$result["total"] = $i;

		echo json_encode($result);
	break;

	case 'trans_debet_note' :
		$kodetrans = $_POST['kodetrans']=='' ? '' : "and kodedebetnote like '%'||?||'%'";
		$nama      = $_POST['nama']=='' ? '' : "and namasupplier like '%'||?||'%'";
		$tgl_aw    = $_POST['tglawal']=='' ? "and tgltrans>='$temp_tgl_aw'" : "and tgltrans>='".ubah_tgl_firebird($_POST['tglawal'])."'";
		$tgl_ak    = $_POST['tglakhir']=='' ? "and tgltrans<='".date('d.m.Y')."'" : "and tgltrans<='".ubah_tgl_firebird($_POST['tglakhir'])."'";

		$i = 0;
		$sql = "select a.*,b.namasupplier
				from tdebetnote a
				left outer join msupplier b on a.kodesupplier=b.kodesupplier
				where 1=1 $kodetrans
					  $tgl_aw $tgl_ak $nama
				order by tgltrans desc,tglinput desc,jaminput desc";
		$st = $db->prepare($sql);
		$q = $db->execute($st, array($_POST['kodetrans'], $_POST['nama']));
		while ($rs = $db->fetch($q)) {
			$result["rows"][] = $rs;
			$i++;
		}
		$result["total"] = $i;

		echo json_encode($result);
	break;

	case 'trans_down_payment' :
		$data_param = array();
		$temp_sql = '';

		if ($_POST['kodetrans']!='') {
			$temp_sql .= "and a.kodedownpayment like '%'||?||'%'";
			$data_param[] = $_POST['kodetrans'];
		}
		if ($_POST['kodetrans_ref']!='') {
			$temp_sql .= "and a.kodetransreferensi like '%'||?||'%'";
			$data_param[] = $_POST['kodetrans_ref'];
		}
		if ($_POST['nama']!='') {
			$temp_sql1 = "and b.namasupplier like '%'||?||'%'";
			$temp_sql2 = "and b.namacustomer like '%'||?||'%'";
			$data_param[] = $_POST['nama'];
		}

		$temp_sql .= $_POST['tglawal']=='' ? "and a.tgltrans>='$temp_tgl_aw'" : "and a.tgltrans>='".ubah_tgl_firebird($_POST['tglawal'])."'";
		$temp_sql .= $_POST['tglakhir']=='' ? "and a.tgltrans<='".date('d.m.Y')."'" : "and a.tgltrans<='".ubah_tgl_firebird($_POST['tglakhir'])."'";
		$temp_sql .= count($_POST['status'])>0 ? "and (a.status='".implode("' or a.status='", $_POST['status'])."')" : '';

		$i = 0;
		$sql = "select *
				from (
					select a.*, b.namasupplier as namareferensi
					from tdownpayment a
					inner join tpo b on a.kodetransreferensi=b.kodepo
					where a.jenistransaksi='PO'
						  $temp_sql
						  $temp_sql1

					union all

					select a.*, b.namasupplier as namareferensi
					from tdownpayment a
					inner join tpoaktiva b on a.kodetransreferensi=b.kodepoaktiva
					where a.jenistransaksi='PO_AKTIVA'
						  $temp_sql
						  $temp_sql1

					union all

					select a.*, b.namacustomer as namareferensi
					from tdownpayment a
					inner join tso b on a.kodetransreferensi=b.kodeso
					where a.jenistransaksi='SO'
						  $temp_sql
						  $temp_sql2
				) order by tgltrans desc,tglinput desc,jaminput desc";
		$st = $db->prepare($sql);
		$q = $db->execute($st, array_merge($data_param, $data_param));
		while ($rs = $db->fetch($q)) {
			$result["rows"][] = $rs;
			$i++;
		}
		$result["total"] = $i;

		echo json_encode($result);
	break;

	case 'trans_akun_tanda_terima' :
		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'kodetandaterima';

		$kodetrans = $_POST['kodetrans']=='' ? '' : "and kodetandaterima like '%'||?||'%'";
		$tgl_aw    = $_POST['tglawal']=='' ? "and tgltrans>='$temp_tgl_aw'" : "and tgltrans>='".ubah_tgl_firebird($_POST['tglawal'])."'";
		$tgl_ak    = $_POST['tglakhir']=='' ? "and tgltrans<='".date('d.m.Y')."'" : "and tgltrans<='".ubah_tgl_firebird($_POST['tglakhir'])."'";

		$i = 0;
		$sql = "select *
				from ttandaterima
				where 1=1 $kodetrans
					  $tgl_aw $tgl_ak
				order by kodetandaterima desc";
		$st = $db->prepare($sql);
		$q = $db->execute($st, array($_POST['kodetrans']));
		while ($rs = $db->fetch($q)) {
			$result["rows"][] = $rs;
			$i++;
		}
		$result["total"] = $i;
		echo json_encode($result);
	break;

	case 'trans_pelunasan_piutang' :
		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'a.kodepelunasan';
		$data_param = array();
		$lokasi = '';
		if ($_SESSION['LOKASIPUSAT'] == 0) {
			$lokasi = "and a.kodelokasi = ?";
			$data_param[] = $_SESSION['KODELOKASI'];
		}

		if ($_POST['kodetrans']!='') {
			$kodetrans = "and a.kodepelunasan like '%'||?||'%'";
			$data_param[] = $_POST['kodetrans'];
		}

		if ($_POST['instansi']!='') {
			$customer = "and b.namainstansi like '%'||?||'%'";
			$data_param[] = $_POST['instansi'];
		}

		$tgl_aw = $_POST['tglawal']=='' ? "and a.tgltrans>='$temp_tgl_aw'" : "and a.tgltrans>='".ubah_tgl_firebird($_POST['tglawal'])."'";
		$tgl_ak = $_POST['tglakhir']=='' ? "and a.tgltrans<='".date('d.m.Y')."'" : "and a.tgltrans<='".ubah_tgl_firebird($_POST['tglakhir'])."'";
		$status = count($_POST['status'])>0 ? "and (a.status='".implode("' or a.status='", $_POST['status'])."')" : '';

		$i = 0;
		$sql = "select a.total as amountkas, a.*, b.namainstansi
				from pelunasanpiutang a
				left join minstansi b on a.kodeinstansi = b.kodeinstansi
				where 1=1 $lokasi
					  $kodetrans $customer $status
					  $tgl_aw $tgl_ak
				order by a.kodepelunasan desc";
		$st = $db->prepare($sql);
		$q = $db->execute($st, $data_param);
		while ($rs = $db->fetch($q)) {
			$result["rows"][] = $rs;
			$i++;
		}
		$result["total"] = $i;

		echo json_encode($result);
	break;

	case 'trans_pelunasan_piutang_cabang' :
		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'kodepelunasan';
		$data_param = array($_SESSION['KODELOKASI']);

		if ($_POST['kodetrans']!='') {
			$kodetrans = "and kodepelunasan like '%'||?||'%'";
			$data_param[] = $_POST['kodetrans'];
		}

		// lokasi cabang
		if ($_POST['kodelokasi']!='') {
			$kodecabang = "and kodecabang like '%'||?||'%'";
			$data_param[] = $_POST['kodelokasi'];
		}

		$tgl_aw = $_POST['tglawal']=='' ? "and tgltrans>='$temp_tgl_aw'" : "and tgltrans>='".ubah_tgl_firebird($_POST['tglawal'])."'";
		$tgl_ak = $_POST['tglakhir']=='' ? "and tgltrans<='".date('d.m.Y')."'" : "and tgltrans<='".ubah_tgl_firebird($_POST['tglakhir'])."'";
		$status = count($_POST['status'])>0 ? "and (status='".implode("' or status='", $_POST['status'])."')" : '';

		$i = 0;
		$sql = "select total as amountkas, pelunasanpiutangcabang.*
				from pelunasanpiutangcabang
				where kodelokasi = ?
					  $kodetrans $kodecabang $status
					  $tgl_aw $tgl_ak
				order by kodepelunasan desc";
		$st = $db->prepare($sql);
		$q = $db->execute($st, $data_param);
		while ($rs = $db->fetch($q)) {
			$result["rows"][] = $rs;
			$i++;
		}
		$result["total"] = $i;

		echo json_encode($result);
	break;

	case 'trans_pelunasan_hutang' :
		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'a.kodepelunasan';
		$data_param = array($_SESSION['KODELOKASI']);

		if ($_POST['kodetrans']!='') {
			$kodetrans = "and a.kodepelunasan like '%'||?||'%'";
			$data_param[] = $_POST['kodetrans'];
		}

		if ($_POST['supplier']!='') {
			$supplier = "and b.namasupplier like '%'||?||'%'";
			$data_param[] = $_POST['supplier'];
		}

		$tgl_aw = $_POST['tglawal']=='' ? "and a.tgltrans>='$temp_tgl_aw'" : "and a.tgltrans>='".ubah_tgl_firebird($_POST['tglawal'])."'";
		$tgl_ak = $_POST['tglakhir']=='' ? "and a.tgltrans<='".date('d.m.Y')."'" : "and a.tgltrans<='".ubah_tgl_firebird($_POST['tglakhir'])."'";
		$status = count($_POST['status'])>0 ? "and (a.status='".implode("' or a.status='", $_POST['status'])."')" : '';

		$i = 0;
		$sql = "select a.total as amountkas, a.*
				from pelunasanhutang a
				left join msupplier b on a.kodesupplier = b.kodesupplier
				where a.kodelokasi=?
					  $kodetrans $supplier $status
					  $tgl_aw $tgl_ak
				order by a.kodepelunasan desc";
		$st = $db->prepare($sql);
		$q = $db->execute($st, $data_param);
		while ($rs = $db->fetch($q)) {
			$result["rows"][] = $rs;
			$i++;
		}
		$result["total"] = $i;
		echo json_encode($result);
	break;

	case 'trans_pembayaran_komisi' :
		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'a.kodepelunasan';
		$data_param = array();

		if ($_POST['kodetrans']!='') {
			$kodetrans = "and a.kodepelunasan like '%'||?||'%'";
			$data_param[] = $_POST['kodetrans'];
		}

		if ($_POST['jurubayar']!='') {
			$jurubayar = "and a.namajurubayar like '%'||?||'%'";
			$data_param[] = $_POST['jurubayar'];
		}

		$tgl_aw = $_POST['tglawal']=='' ? "and a.tgltrans>='$temp_tgl_aw'" : "and a.tgltrans>='".ubah_tgl_firebird($_POST['tglawal'])."'";
		$tgl_ak = $_POST['tglakhir']=='' ? "and a.tgltrans<='".date('d.m.Y')."'" : "and a.tgltrans<='".ubah_tgl_firebird($_POST['tglakhir'])."'";
		$status = count($_POST['status'])>0 ? "and (a.status='".implode("' or a.status='", $_POST['status'])."')" : '';

		$i = 0;
		$sql = "select a.total as amountkas, a.*, SUBSTRING(a.kodepelunasan from 4 for 4) as kodelokasi
				from pelunasanhutangkomisi a
				left join mjurubayar b on a.kodejurubayar = b.kodejurubayar
				where 1=1 $kodetrans $jurubayar $status
					  $tgl_aw $tgl_ak
				order by a.kodepelunasan desc";
		$st = $db->prepare($sql);
		$q = $db->execute($st, $data_param);
		while ($rs = $db->fetch($q)) {
			$result["rows"][] = $rs;
			$i++;
		}
		$result["total"] = $i;
		echo json_encode($result);
	break;

	case 'trans_ubah_juru_bayar' :
		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'kodeubahjurubayar';
		$sql = "select count(*) as row
				from tubahjurubayar a
				where 1=1 $txt";
		$st = $db->prepare($sql);
		$q  = $db->execute($st, $data_param);
		$r  = $db->fetch($q);
		$result["total"] = $r->ROW;

		$sql = "select first $rows skip $offset
					   a.*, b.namajurubayar as namajurubayarlama, c.namajurubayar as namajurubayarbaru, d.namainstansi
 				from tubahjurubayar a
				inner join mjurubayar b on a.kodejurubayarlama=b.kodejurubayar
				inner join mjurubayar c on a.kodejurubayarbaru=c.kodejurubayar
				inner join minstansi d on c.kodeinstansi=d.kodeinstansi
				where 1=1 $txt
			 	order by $sort $order";
		$st = $db->prepare($sql);
		$q  = $db->execute($st, $data_param);
		while ($rs = $db->fetch($q)) {
			$result["rows"][] = $rs;
		}

		echo json_encode($result);
	break;

	case 'tanggal_aktif_harga_jual' :
		$kode_customer = $_GET[kode_customer];

		$i = 0;
		$sql = "select distinct tglaktif, kodecustomer
				from mhargajual
				where kodecustomer='$kode_customer'
				order by tglaktif desc";
		$q = $db->query($sql);
		while ($rs = $db->fetch($q)) {
			$result["rows"][] = $rs;
			$i++;
		}
		$result["total"] = $i;

		echo json_encode($result);
	break;

	case 'harga_jual' :
		$kode_customer = $_GET['kode_customer'];
		$tgl_aktif = $_GET['tgl_aktif'];

		$i = 0;
		$sql = "select a.*, b.namabarang
				from mhargajual a
				inner join mbarang b on a.kodebarang=b.kodebarang
				where a.kodecustomer='$kode_customer' and
					  a.tglaktif='$tgl_aktif'
				order by tglaktif desc";
		$q = $db->query($sql);
		while ($rs = $db->fetch($q)) {
			$result["rows"][] = $rs;
			$i++;
		}
		$result["total"] = $i;

		echo json_encode($result);
	break;

	case 'trans_akun_piutang_lain' :
		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'kodepiutanglain';

		$kodetrans = $_POST['kodetrans']=='' ? '' : "and kodepiutanglain like '%'||?||'%'";
		$tgl_aw    = $_POST['tglawal']=='' ? "and tgltrans>='$temp_tgl_aw'" : "and tgltrans>='".ubah_tgl_firebird($_POST['tglawal'])."'";
		$tgl_ak    = $_POST['tglakhir']=='' ? "and tgltrans<='".date('d.m.Y')."'" : "and tgltrans<='".ubah_tgl_firebird($_POST['tglakhir'])."'";

		$i = 0;
		$sql = "select *
				from tpiutanglain
				where 1=1 $kodetrans
					  $tgl_aw $tgl_ak
				order by kodepiutanglain desc";
		$st = $db->prepare($sql);
		$q = $db->execute($st, array($_POST['kodetrans']));
		while ($rs = $db->fetch($q)) {
			$result["rows"][] = $rs;
			$i++;
		}
		$result["total"] = $i;

		echo json_encode($result);
	break;

	case 'trans_akun_hutang_lain' :
		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'kodehutanglain';

		$kodetrans = $_POST['kodetrans']=='' ? '' : "and kodehutanglain like '%'||?||'%'";
		$tgl_aw    = $_POST['tglawal']=='' ? "and tgltrans>='$temp_tgl_aw'" : "and tgltrans>='".ubah_tgl_firebird($_POST['tglawal'])."'";
		$tgl_ak    = $_POST['tglakhir']=='' ? "and tgltrans<='".date('d.m.Y')."'" : "and tgltrans<='".ubah_tgl_firebird($_POST['tglakhir'])."'";

		$i = 0;
		$sql = "select *
				from thutanglain
				where 1=1 $kodetrans $tgl_aw $tgl_ak
				order by kodehutanglain desc";
		$st = $db->prepare($sql);
		$q = $db->execute($st, array($_POST['kodetrans']));
		while ($rs = $db->fetch($q)) {
			$result["rows"][] = $rs;
			$i++;
		}
		$result["total"] = $i;

		echo json_encode($result);
	break;

	case 'trans_tutup_tanggal' :
		$q = $db->query("select count(*) as row from historytanggal where 1=1");
		$r = $db->fetch($q);
		$result["total"] = $r->ROW;

		$sql = "select first $rows skip $offset a.*
				from historytanggal a
				where 1=1 order by tanggal desc";
		$q = $db->query($sql);
		while ($rs = $db->fetch($q)) {
			$result["rows"][] = $rs;
		}
		echo json_encode($result);
	break;

	case 'history_data' :
		//$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'jaminput';

		$kodetrans = $_POST['kodetrans']=='' ? '' : "and kodetrans like '%'||?||'%'";
		$nama      = $_POST['nama']=='' ? '' : "and namamenu like '%'||?||'%'";
		$jenis 	   = $_POST['jenis']=='' ? '' : "and jenis like '%'||?||'%'";
		$user 	   = $_POST['user']=='' ? '' : "and userentry like '%'||?||'%'";
		$tgl_aw    = $_POST['tglawal']=='' ? "and tglinput>='$temp_tgl_aw'" : "and tglinput>='".ubah_tgl_firebird($_POST['tglawal'])."'";
		$tgl_ak    = $_POST['tglakhir']=='' ? "and tglinput<='".date('d.m.Y')."'" : "and tglinput<='".ubah_tgl_firebird($_POST['tglakhir'])."'";

		$i = 0;
		$sql = "select *
				from historydata
				where 1=1 $kodetrans
					  $tgl_aw $tgl_ak
					  $nama
					  $jenis $user
				order by tglinput desc, jaminput desc";
		$st = $db->prepare($sql);
		$q = $db->execute($st, array($_POST['kodetrans'], $_POST['nama'], $_POST['jenis'], $_POST['user']));
		while ($rs = $db->fetch($q)) {
			$result["rows"][] = $rs;
			$i++;
		}
		$result["total"] = $i;

		echo json_encode($result);
	break;

	case 'trans_pemberitahuan' :
		$temp_sql = '';
		$temp_sql .= isset($_SESSION['userid']) ? " and kodeuser='".$_SESSION['userid']."'" : '';
		$temp_sql .= isset($_REQUEST['status']) ? " and status='".$_REQUEST['status']."'" : '';

		$q = $db->query("select count(*) as row from tpemberitahuan where 1=1 $temp_sql");
		$r = $db->fetch($q);
		$result["total"] = $r->ROW;

		$sql = "select *
				from tpemberitahuan
				where 1=1 $temp_sql
				order by kodepemberitahuan desc";
		$q = $db->query($sql);
		while ($rs = $db->fetch($q)) {
			$rs->KETERANGAN = eregi_replace(chr(13),"<br>",$rs->KETERANGAN);
			$result["rows"][] = $rs;
		}
		echo json_encode($result);
	break;

	case 'harga_warna' :
		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'kodepo';

		$i = 0;

		$sql = "select a.kodepo, a.tgltrans, b.hargawarna
				from tpo a
				inner join tpodtl b on a.kodepo = b.kodepo
				inner join mbarang c on b.kodebarang = c.kodebarang
				where b.kodebarang = ? and
					  a.status <> 'D'
				order by $sort $order";
		$st = $db->prepare($sql);
		$q = $db->execute($st, $_POST['kodebarang']);
		while ($rs = $db->fetch($q)) {
			$result["rows"][] = $rs;
			$i++;
		}
		$result["total"] = $i;

		echo json_encode($result);
	break;

	case 'harga_beli' :
		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'kodebeli';

		$i = 0;
		$sql = "select a.kodebeli, a.tgltrans, b.harga
				from tbeli a
				inner join tbelidtl b on a.kodebeli = b.kodebeli
				inner join mbarang c on b.kodebarang = c.kodebarang
				where b.kodebarang = ? and
					  a.status <> 'D'
				order by $sort $order";
		$st = $db->prepare($sql);
		$q = $db->execute($st, $_POST['kodebarang']);
		while ($rs = $db->fetch($q)) {
			$result["rows"][] = $rs;
			$i++;
		}
		$result["total"] = $i;

		echo json_encode($result);
	break;
	
	case 'departemen_menu' :
		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'kodedepartemenmenu';

		$st = $db->prepare("select count(*) as row from mdepartemenmenu where 1=1 $txt");
		$q = $db->execute($st, $value);
		$r = $db->fetch($q);
		$result["total"] = $r->ROW;

		$sql = "select first $rows skip $offset *
				from mdepartemenmenu
				where 1=1 $txt
				order by $sort $order";
		$st = $db->prepare($sql);
		$q = $db->execute($st, $value);
		while ($rs = $db->fetch($q)) {
			$result["rows"][] = $rs;
		}

		echo json_encode($result);
	break;
	
	case 'kategori_menu' :
		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'kodekategorimenu';

		$st = $db->prepare("select count(*) as row from mkategorimenu where 1=1 $txt");
		$q = $db->execute($st, $value);
		$r = $db->fetch($q);
		$result["total"] = $r->ROW;

		$sql = "select first $rows skip $offset *
				from mkategorimenu
				where 1=1 $txt
				order by $sort $order";
		$st = $db->prepare($sql);
		$q = $db->execute($st, $value);
		while ($rs = $db->fetch($q)) {
			$result["rows"][] = $rs;
		}

		echo json_encode($result);
	break;
	
	case 'meja' :
		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'nomormeja';

		$st = $db->prepare("select count(*) as row from mmeja where 1=1 $txt");
		$q = $db->execute($st, $value);
		$r = $db->fetch($q);
		$result["total"] = $r->ROW;

		$sql = "select first $rows skip $offset *
				from mmeja
				where 1=1 $txt
				order by $sort $order";
		$st = $db->prepare($sql);
		$q = $db->execute($st, $value);
		while ($rs = $db->fetch($q)) {
			$result["rows"][] = $rs;
		}

		echo json_encode($result);
	break;
	
	case 'voucher' :
		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'kodevoucher';

		$st = $db->prepare("select count(*) as row from mvoucher where 1=1 $txt");
		$q = $db->execute($st, $value);
		$r = $db->fetch($q);
		$result["total"] = $r->ROW;

		$sql = "select first $rows skip $offset *
				from mvoucher
				where 1=1 $txt
				order by $sort $order";
		$st = $db->prepare($sql);
		$q = $db->execute($st, $value);
		while ($rs = $db->fetch($q)) {
			$result["rows"][] = $rs;
		}

		echo json_encode($result);
	break;
	case 'kartu_kredit' :
		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'kodekartukredit';

		$st = $db->prepare("select count(*) as row from mkartukredit where 1=1 $txt");
		$q = $db->execute($st, $value);
		$r = $db->fetch($q);
		$result["total"] = $r->ROW;

		$sql = "select first $rows skip $offset *
				from mkartukredit
				where 1=1 $txt
				order by $sort $order";
		$st = $db->prepare($sql);
		$q = $db->execute($st, $value);
		while ($rs = $db->fetch($q)) {
			$result["rows"][] = $rs;
		}

		echo json_encode($result);
	break;

	case 'discount' :
		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'kode';

		$st = $db->prepare("select count(*) as row from mdiscount where 1=1 $txt");
		$q = $db->execute($st, $value);
		$r = $db->fetch($q);
		$result["total"] = $r->ROW;

		$sql = "select first $rows skip $offset *
				from mdiscount
				where 1=1 $txt
				order by $sort $order";
		$st = $db->prepare($sql);
		$q = $db->execute($st, $value);
		while ($rs = $db->fetch($q)) {
			$result["rows"][] = $rs;
		}

		echo json_encode($result);
	break;
	
	case 'satuan' :
		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'kodesatuan';

		$st = $db->prepare("select count(*) as row from msatuan where 1=1 $txt");
		$q = $db->execute($st, $value);
		$r = $db->fetch($q);
		$result["total"] = $r->ROW;

		$sql = "select first $rows skip $offset *
				from msatuan
				where 1=1 $txt
				order by $sort $order";
		$st = $db->prepare($sql);
		$q = $db->execute($st, $value);
		while ($rs = $db->fetch($q)) {
			$result["rows"][] = $rs;
		}

		echo json_encode($result);
	break;
	case 'sub_resep' :
		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'kode';

		$st = $db->prepare("select count(*) as row from mrecipe where 1=1 $txt");
		$q = $db->execute($st, $value);
		$r = $db->fetch($q);
		$result["total"] = $r->ROW;

		$sql = "select first $rows skip $offset *
				from mrecipe
				where 1=1 $txt
				order by $sort $order";
		$st = $db->prepare($sql);
		$q = $db->execute($st, $value);
		while ($rs = $db->fetch($q)) {
			$result["rows"][] = $rs;
		}

		echo json_encode($result);
	break;
	case 'barang' :
		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'kode';
	/*
	left outer join mjenisbarang b on a.kodejenis=b.kodejenis
	left outer join mbahanbarang c on a.kodebahan=c.kodebahan
	left outer join mperkiraan d on a.kodeperkiraan=d.kodeperkiraan
	left outer join msupplier e on a.kodesupplier=e.kodesupplier
	
		$sql = 'select count(*) as row
				from (
					select a.*, a.satuan as satuan1,
						   b.nama, c.nama, d.nama, e.namaperkiraan as KODEPERKIRAAN2
					from mbarang a
					left outer join mkategori b on a.kodekategori=b.kode
					left outer join mdepartemen c on a.kodedepartemen=c.kode
					left outer join mjenisbarang d on a.kodejenisbarang=d.kode
					left outer join mperkiraan e on a.kodeperkiraan=e.kode
					order by '.$sort.' '.$order.'
				) where 1=1 '.$filter->sql;
		$st = $db->prepare($sql);
		$q = $db->execute($st, $filter->param);
		$r = $db->fetch($q);
		$result["total"] = $r->ROW;*/
		$sql = 'select count(*) as row
				from mbarang where 1=1 '.$filter->sql;
		$sql = str_replace('count(*) as row', "first $rows skip $offset *", $sql)." order by $sort $order";
		$st = $db->prepare($sql);
		$q = $db->execute($st, $filter->param);
		while ($rs = $db->fetch($q)) {
			$result["rows"][] = $rs;
		}

		echo json_encode($result);
	break;
	
	case 'modifier' :
		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'kode';

		$st = $db->prepare("select count(*) as row from mmodifier where 1=1 $txt");
		$q = $db->execute($st, $value);
		$r = $db->fetch($q);
		$result["total"] = $r->ROW;

		$sql = "select first $rows skip $offset *
				from mmodifier
				where 1=1 $txt
				order by $sort $order";
		$st = $db->prepare($sql);
		$q = $db->execute($st, $value);
		while ($rs = $db->fetch($q)) {
			$result["rows"][] = $rs;
		}

		echo json_encode($result);
	break;
	case 'brand' :
		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'kode';

		$st = $db->prepare("select count(*) as row from mbrand where 1=1 $txt");
		$q = $db->execute($st, $value);
		$r = $db->fetch($q);
		$result["total"] = $r->ROW;

		$sql = "select first $rows skip $offset *
				from mbrand
				where 1=1 $txt
				order by $sort $order";
		$st = $db->prepare($sql);
		$q = $db->execute($st, $value);
		while ($rs = $db->fetch($q)) {
			$result["rows"][] = $rs;
		}

		echo json_encode($result);
	break;
	case 'promosi' :
		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'kodepromo';

		$st = $db->prepare("select count(*) as row from mpromo where 1=1 $txt");
		$q = $db->execute($st, $value);
		$r = $db->fetch($q);
		$result["total"] = $r->ROW;

		$sql = "select first $rows skip $offset p.*,
				substring(p.hariaktif from 1 for 1) as SENIN,
				substring(p.hariaktif from 2 for 1) as SELASA,
				substring(p.hariaktif from 3 for 1) as RABU,
				substring(p.hariaktif from 4 for 1) as KAMIS,
				substring(p.hariaktif from 5 for 1) as JUMAT,
				substring(p.hariaktif from 6 for 1) as SABTU,
				substring(p.hariaktif from 7 for 1) as MINGGU
				from mpromo p
				where 1=1 $txt
				order by $sort $order";
		$st = $db->prepare($sql);
		$q = $db->execute($st, $value);
		while ($rs = $db->fetch($q)) {
			$result["rows"][] = $rs;
		}

		echo json_encode($result);
	break;
	
	case 'menu_resep' :
		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'kode';

		$st = $db->prepare("select count(*) as row from mmenurecipe where 1=1 $txt");
		$q = $db->execute($st, $value);
		$r = $db->fetch($q);
		$result["total"] = $r->ROW;

		$sql = "select first $rows skip $offset *
				from mmenurecipe
				where 1=1 $txt
				order by $sort $order";
		$st = $db->prepare($sql);
		$q = $db->execute($st, $value);
		while ($rs = $db->fetch($q)) {
			$result["rows"][] = $rs;
		}

		echo json_encode($result);
	break;
	
	case 'printer' :
		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'kode';

		$st = $db->prepare("select count(*) as row from mprinter where 1=1 $txt");
		$q = $db->execute($st, $value);
		$r = $db->fetch($q);
		$result["total"] = $r->ROW;

		$sql = "select first $rows skip $offset *
				from mprinter
				where 1=1 $txt
				order by $sort $order";
		$st = $db->prepare($sql);
		$q = $db->execute($st, $value);
		while ($rs = $db->fetch($q)) {
			$result["rows"][] = $rs;
		}

		echo json_encode($result);
	break;
	case 'departemen_barang' :
		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'kodedepartemenbarang';

		$st = $db->prepare("select count(*) as row from mdepartemenbarang where 1=1 $txt");
		$q = $db->execute($st, $value);
		$r = $db->fetch($q);
		$result["total"] = $r->ROW;

		$sql = "select first $rows skip $offset *
				from mdepartemenbarang
				where 1=1 $txt
				order by $sort $order";
		$st = $db->prepare($sql);
		$q = $db->execute($st, $value);
		while ($rs = $db->fetch($q)) {
			$result["rows"][] = $rs;
		}

		echo json_encode($result);
	break;
	
	case 'kategori_barang' :
		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'kodekategoribarang';

		$st = $db->prepare("select count(*) as row from mkategoribarang where 1=1 $txt");
		$q = $db->execute($st, $value);
		$r = $db->fetch($q);
		$result["total"] = $r->ROW;

		$sql = "select first $rows skip $offset *
				from mkategoribarang
				where 1=1 $txt
				order by $sort $order";
		$st = $db->prepare($sql);
		$q = $db->execute($st, $value);
		while ($rs = $db->fetch($q)) {
			$result["rows"][] = $rs;
		}

		echo json_encode($result);
	break;
	
	case 'kategori_supplier' :
		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'kodekategorisupplier';

		$st = $db->prepare("select count(*) as row from mkategorisupplier where 1=1 $txt");
		$q = $db->execute($st, $value);
		$r = $db->fetch($q);
		$result["total"] = $r->ROW;

		$sql = "select first $rows skip $offset *
				from mkategorisupplier
				where 1=1 $txt
				order by $sort $order";
		$st = $db->prepare($sql);
		$q = $db->execute($st, $value);
		while ($rs = $db->fetch($q)) {
			$result["rows"][] = $rs;
		}

		echo json_encode($result);
	break;
	case 'jenis_barang' :
		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'kodejenisbarang';

		$st = $db->prepare("select count(*) as row from mjenisbarang where 1=1 $txt");
		$q = $db->execute($st, $value);
		$r = $db->fetch($q);
		$result["total"] = $r->ROW;

		$sql = "select first $rows skip $offset *
				from mjenisbarang
				where 1=1 $txt
				order by $sort $order";
		$st = $db->prepare($sql);
		$q = $db->execute($st, $value);
		while ($rs = $db->fetch($q)) {
			$result["rows"][] = $rs;
		}

		echo json_encode($result);
	break;
	
	case 'syarat_bayar' :
		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'kodesyaratbayar';

		$sql = 'select count(*) as row
				from msyaratbayar
				where 1=1 '.$filter->sql;

		$st = $db->prepare($sql);
		$q = $db->execute($st, $filter->param);
		$r = $db->fetch($q);
		$result["total"] = $r->ROW;

		$sql = str_replace('count(*) as row', "first $rows skip $offset *", $sql)." order by $sort $order";
		$st = $db->prepare($sql);
		$q = $db->execute($st, $filter->param);
		while ($rs = $db->fetch($q)) {
			$result["rows"][] = $rs;
		}

		echo json_encode($result);
	break;
}
?>