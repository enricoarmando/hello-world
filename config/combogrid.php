<?php
session_start();
include "koneksi.php";
include "function.php";

$table = $_GET['table'];

$page   = isset($_POST['page']) ? intval($_POST['page']) : 1;
$rows   = isset($_POST['rows']) ? intval($_POST['rows']) : 20;
$order  = isset($_POST['order']) ? strval($_POST['order']) : 'asc';
$offset = ($page-1)*$rows;
$q      = isset($_POST['q']) ? strtoupper($_POST['q']) : '';

//$db = new DB;

switch ($table) {
	case 'bank' :
		$rows = array();
		$sql = "select namabank as nama from mbank";
		$st = $db->prepare($sql);
		$query = $db->execute($st, array($q, $q));
		while ($rs = $db->fetch($query)) {
			$rows[] = $rs;
		}
		echo json_encode($rows);
	break;
	case 'bahan_barang' :
		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'namabahan';
		$status = isset($_GET['status']) && $_GET['status']=='all' ? '' : 'and status=1';

		$rows = array();
		$sql = "select kodebahan as kode, namabahan as nama
				from mbahanbarang
				where (kodebahan like '%'||?||'%' or namabahan like '%'||?||'%')
				order by $sort $order";
		$st = $db->prepare($sql);
		$query = $db->execute($st, array($q, $q));
		while ($rs = $db->fetch($query)) {
			$rows[] = $rs;
		}
		echo json_encode($rows);
	break;
	case 'nama_barang' :
		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'namabarang';
		$status = isset($_GET['status']) && $_GET['status']=='all' ? '' : 'and status=1';

		$rows = array();
		if (strlen($q)>=4){
			$sql = "select distinct namabarang as nama
					from mbarang
					where (namabarang like '%'||?||'%')
						  $status
					order by $sort $order";
		}else{
			$sql = "select first 30 skip 0 distinct
						   namabarang as nama
					from mbarang
					where (namabarang like '%'||?||'%')
						  $status
					order by $sort $order";
		}
		$st = $db->prepare($sql);
		$query = $db->execute($st, array($q, $q));
		while ($rs = $db->fetch($query)) {
			$rows[] = $rs;
		}
		echo json_encode($rows);
	break;
	// browsing SO di transaksi bayar SO
	case 'sales_order_for_bayar' :
		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'a.kodeso';
		//$status = isset($_GET['status']) && $_GET['status']=='all' ? '' : 'and status=1';

		$rows = array();
		$sql = "select first 15 skip 0
					   a.kodeso, a.tgltrans, a.tgljanji, a.kodecustomer, b.namacustomer,
					   a.koderekammedis, d.username as namapegawai_sales, a.kodepegawai_sales,
					   a.nospmanual, a.tipeorder, c.kodemember, c.nokartu, a.jenis,
					   d.username, e.namainstansi, f.namajurubayar, g.namapromo
				from (tso a
				inner join mcustomer b on a.kodecustomer = b.kodecustomer)
				left join mmember c on b.kodecustomer = c.kodecustomer
				left join muser d on a.kodepegawai_sales = d.userid
				left join minstansi e on a.kodeinstansi = e.kodeinstansi
				left join mjurubayar f on a.kodejurubayar = f.kodejurubayar
				left join mpromo g on a.kodepromo = g.kodepromo
				where (a.kodeso like '%'||?||'%' or b.namacustomer like '%'||?||'%') and
					  a.status = 'I' and
					  a.kodelokasi = ?
				order by $sort $order";
		$st = $db->prepare($sql);
		$query = $db->execute($st, array($q, $q, $_SESSION['KODELOKASI']));
		while ($rs = $db->fetch($query)) {
			$rows[] = $rs;
		}
		echo json_encode($rows);
	break;

	// browsing SO di transaksi QC
	case 'sales_order_for_QC' :
		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'a.kodeso';

		$rows = array();
		$sql = "select a.kodeso, a.tgltrans, a.tgljanji, a.kodecustomer, b.namacustomer,
					   a.nospmanual, a.tipeorder, c.kodemember, c.nokartu, a.jenis,
					   e.username as namapegawai_sales, g.username as namapegawai_RO,
					   f.TINDAKAN_R_SPH, f.TINDAKAN_R_CYL, f.TINDAKAN_R_AXIS, f.TINDAKAN_R_PRISM, f.TINDAKAN_R_VA, f.TINDAKAN_R_ADD, f.TINDAKAN_R_PD, f.TINDAKAN_PV,
					   f.TINDAKAN_L_SPH, f.TINDAKAN_L_CYL, f.TINDAKAN_L_AXIS, f.TINDAKAN_L_PRISM, f.TINDAKAN_L_VA, f.TINDAKAN_L_ADD, f.TINDAKAN_L_PD, f.TINDAKAN_SH
				from (((tso a
				inner join mcustomer b on a.kodecustomer = b.kodecustomer)
				left join mmember c on b.kodecustomer = c.kodecustomer)
				left join tqualitycontrol d on a.kodeso = d.kodeso and d.status <> 'D'
				left join muser e on a.kodepegawai_sales = e.userid)
				left join trekammedis f on a.koderekammedis = f.koderekammedis and f.status <> 'D'
				left join muser g on f.RO = g.userid
				where (a.kodeso like '%'||?||'%' or b.namacustomer like '%'||?||'%') and
					   d.kodeqc is null and
					   a.status = 'S' and
					   a.kodelokasi = ?

				union all

				select a.kodeso, a.tgltrans, a.tgljanji, a.kodecustomer, b.namacustomer,
					   a.nospmanual, a.tipeorder, c.kodemember, c.nokartu, a.jenis,
					   e.username as namapegawai_sales, g.username as namapegawai_RO,
					   f.TINDAKAN_R_SPH, f.TINDAKAN_R_CYL, f.TINDAKAN_R_AXIS, f.TINDAKAN_R_PRISM, f.TINDAKAN_R_VA, f.TINDAKAN_R_ADD, f.TINDAKAN_R_PD, f.TINDAKAN_PV,
					   f.TINDAKAN_L_SPH, f.TINDAKAN_L_CYL, f.TINDAKAN_L_AXIS, f.TINDAKAN_L_PRISM, f.TINDAKAN_L_VA, f.TINDAKAN_L_ADD, f.TINDAKAN_L_PD, f.TINDAKAN_SH
				from (((tso a
				inner join mcustomer b on a.kodecustomer = b.kodecustomer)
				left join mmember c on b.kodecustomer = c.kodecustomer)
				left join tqualitycontrol d on a.kodeso = d.kodeso and d.status <> 'D'
				left join muser e on a.kodepegawai_sales = e.userid)
				left join trekammedis f on a.koderekammedis = f.koderekammedis and f.status <> 'D'
				left join muser g on f.RO = g.userid
				inner join ttransfertitipan i on a.kodeso = i.kodeso and i.status = 'S'
				inner join tterimatitipan j on i.kodetransfertitipan = j.kodetransfertitipan and  j.status = 'S'
				where (a.kodeso like '%'||?||'%' or b.namacustomer like '%'||?||'%') and
					  d.kodeqc is null and
					  a.status = 'S' and
					  i.kodelokasitujuan = ?";
		$st = $db->prepare($sql);
		$query = $db->execute($st, array($q, $q, $_SESSION['KODELOKASI'], $q, $q, $_SESSION['KODELOKASI']));
		while ($rs = $db->fetch($query)) {
			$rows[] = $rs;
		}
		echo json_encode($rows);
	break;

	// browsing SO di transaksi SURAT PESANAN
	case 'sales_order_for_SP' :
		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'a.kodeso';
		//$status = isset($_GET['status']) && $_GET['status']=='all' ? '' : 'and status=1';

		$rows = array();
		$sql = "select distinct a.kodeso, a.tgltrans, a.kodecustomer, b.namacustomer,
					   e.TINDAKAN_R_SPH, e.TINDAKAN_R_CYL, e.TINDAKAN_R_AXIS, e.TINDAKAN_R_PRISM, e.TINDAKAN_R_VA, e.TINDAKAN_R_ADD, e.TINDAKAN_R_PD, e.TINDAKAN_PV,
					   e.TINDAKAN_L_SPH, e.TINDAKAN_L_CYL, e.TINDAKAN_L_AXIS, e.TINDAKAN_L_PRISM, e.TINDAKAN_L_VA, e.TINDAKAN_L_ADD, e.TINDAKAN_L_PD, e.TINDAKAN_SH
				from (tso a
				inner join mcustomer b on a.kodecustomer = b.kodecustomer
				inner join tsodtl c on a.kodeso = c.kodeso
				inner join mbarang d on c.kodebarang = d.kodebarang)
				left join trekammedis e on a.koderekammedis = e.koderekammedis and e.status <> 'D'
				left join tpr f on a.kodeso = f.kodeso and f.status <> 'D'
				where (a.kodeso like '%'||?||'%' or b.namacustomer like '%'||?||'%') and
					  f.kodepr is null and
					  a.status = 'S' and
					  (d.kodejenis >= '3' and d.kodejenis <= '8') and
					  a.kodelokasi = ?
				order by $sort $order";
		$st = $db->prepare($sql);
		$query = $db->execute($st, array($q, $q, $_SESSION['KODELOKASI']));
		while ($rs = $db->fetch($query)) {
			$rows[] = $rs;
		}
		echo json_encode($rows);
	break;

	// browsing SO di transaksi Penjualan
	case 'sales_order_for_jual' :
		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'a.kodeso';
		//$status = isset($_GET['status']) && $_GET['status']=='all' ? '' : 'and status=1';

		$rows = array();
		$sql = "select a.kodeso, a.tgltrans, a.tgljanji, a.kodecustomer, b.namacustomer,
					   f.username as namasalesman, g.username as namaro, h.username as namaedger,
					   a.kodepegawai_sales, d.ro as kodepegawai_ro, e.kodepegawai_edger,
					   (a.cash+a.kartukredit+a.kartudebit+a.askes) as downpayment, e.kodeqc,
					   a.nospmanual, a.tipeorder, c.kodemember, c.nokartu, a.jenis, a.kodeinstansi, a.kodejurubayar,
					   d.TINDAKAN_R_SPH, d.TINDAKAN_R_CYL, d.TINDAKAN_R_AXIS, d.TINDAKAN_R_PRISM, d.TINDAKAN_R_VA, d.TINDAKAN_R_ADD, d.TINDAKAN_R_PD, d.TINDAKAN_PV,
					   d.TINDAKAN_L_SPH, d.TINDAKAN_L_CYL, d.TINDAKAN_L_AXIS, d.TINDAKAN_L_PRISM, d.TINDAKAN_L_VA, d.TINDAKAN_L_ADD, d.TINDAKAN_L_PD, d.TINDAKAN_SH
				from (tso a
				inner join mcustomer b on a.kodecustomer = b.kodecustomer)
				left join mmember c on b.kodecustomer = c.kodecustomer
				left join trekammedis d on a.koderekammedis = d.koderekammedis and d.status <> 'D'
				inner join tqualitycontrol e on e.kodeso=a.kodeso and e.status <> 'D'
				left join muser f on a.kodepegawai_sales=f.userid
				left join muser g on d.ro=g.userid
				left join muser h on e.kodepegawai_edger=h.userid
				where (a.kodeso like '%'||?||'%' or b.namacustomer like '%'||?||'%') and
					  a.status = 'Q' and
					  a.kodelokasi = ?
				order by $sort $order";
		$st = $db->prepare($sql);
		$query = $db->execute($st, array($q, $q, $_SESSION['KODELOKASI']));
		while ($rs = $db->fetch($query)) {
			$rows[] = $rs;
		}
		echo json_encode($rows);
	break;

	case 'barang' :
		$data_values = array($q, $q, $q);

		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'a.kodebarang';
		$status = isset($_GET['status']) && $_GET['status']=='all' ? '' : 'and a.status=1';

		if (isset($_GET['jenis'])) {
			if (!is_array($_GET['jenis'])) {
				$temp_sql = 'and d.namajenis=?';
				$data_values[] = trim(strtoupper($_GET['jenis']));
			} else {
				$param = array();

				foreach ($_GET['jenis'] as $item)
					$param[] = '?';

				$temp_sql = 'and (d.namajenis='.implode(' or d.namajenis=', $param).')';
				$data_values = array_merge($data_values, $_GET['jenis']);
			}
		}
		if (strlen($q)>=4) {
			$sql = "select a.kodebarang as kode, a.namabarang as nama, a.tipe,
						   a.namabarangsupplier,a.satuan, a.satuan2, a.satuan3, a.konversi1, a.konversi2, a.hargabeli,
						   a.hargajual, a.hargapaket, b.namasupplier, a.barcode, c.namakategori1,
						   c.namakategori2, c.namakategori3, c.jenisframe, d.namajenis as jenisbarang
					from mbarang a
					left join msupplier b on a.kodesupplier=b.kodesupplier
					left join mjenisframe c on a.kodebarang = c.kodebarang
					left join mjenisbarang d on a.kodejenis = d.kodejenis
					where (a.kodebarang like ?||'%' or a.namabarang||' '||a.tipe like '%'||?||'%' or a.namabarangsupplier like '%'||?||'%')
						  $status $temp_sql
					order by $sort $order";
		} else {
			$sql = "select first 30 skip 0 distinct
						   a.kodebarang as kode, a.namabarang as nama, a.tipe,
						   a.namabarangsupplier,a.satuan, a.satuan2, a.satuan3, a.konversi1, a.konversi2, a.hargabeli,
						   a.hargajual, a.hargapaket, b.namasupplier, a.barcode, c.namakategori1,
						   c.namakategori2, c.namakategori3, c.jenisframe, d.namajenis as jenisbaran
					from mbarang a
					left join msupplier b on a.kodesupplier=b.kodesupplier
					left join mjenisframe c on a.kodebarang = c.kodebarang
					left join mjenisbarang d on a.kodejenis = d.kodejenis
					where (a.kodebarang like ?||'%' or a.namabarang||' '||a.tipe like '%'||?||'%' or a.namabarangsupplier like '%'||?||'%')
						  $status $temp_sql
					order by $sort $order";
		}
		$st = $db->prepare($sql);
		$query = $db->execute($st, $data_values);
		$rows = array();
		while ($rs = $db->fetch($query)) {
			$rows[] = $rs;
		}
		echo json_encode($rows);
	break;

	// browsing barang ketika PO dan sudah ada no PR/SPL
	case 'barang_SPL' :
		$data_values = array($q, $q, $_REQUEST['kodetrans']);

		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'kodebarang';

		$sql = "select B.KODEBARANG as KODE, B.NAMABARANG as NAMA, b.TIPE, B.SATUAN,
					   B.SATUAN2, B.SATUAN3, B.KONVERSI1, B.KONVERSI2,
					   B.HARGABELI, A.SISA, c.NAMASUPPLIER
				from TPRDTL A
				inner join MBARANG B on A.KODEBARANG = B.KODEBARANG
				left join MSUPPLIER C on B.KODESUPPLIER = C.KODESUPPLIER
				where A.SISA > 0 and
					  A.TUTUP = 0 and
					  (B.KODEBARANG like '%' || ? || '%' or B.NAMABARANG like '%' || ? || '%') and
					  A.KODEPR = ?";
		$st = $db->prepare($sql);
		$query = $db->execute($st, $data_values);
		$rows = array();
		while ($rs = $db->fetch($query)) {
			$rows[] = $rs;
		}
		echo json_encode($rows);
	break;

	// browsing barang ketika PENJUALAN dan sedang memilih no SO
	case 'barang_so' :
		$data_values = array($q, $q, $_GET['kodetrans']);

		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'kodebarang';

		$sql = "select B.KODEBARANG as KODE, B.NAMABARANG as NAMA, B.SATUAN,
					   B.SATUAN2, B.SATUAN3, B.KONVERSI1, B.KONVERSI2, B.HARGABELI,
					   A.HARGA AS HARGAJUAL, A.SISA
				from TSODTL A
				inner join MBARANG B on A.KODEBARANG = B.KODEBARANG
				where A.SISA > 0 and
					  A.TUTUP = 0 and
					  (B.KODEBARANG like '%' || ? || '%' or B.NAMABARANG like '%' || ? || '%') and
					  A.KODESO = ?";
		$st = $db->prepare($sql);
		$query = $db->execute($st, $data_values);
		$rows = array();
		while ($rs = $db->fetch($query)) {
			$rows[] = $rs;
		}
		echo json_encode($rows);
	break;

	// browsing barang ketika PEMBELIAN dan sedang memilih no PO
	case 'barang_po' :
		$data_values = array($q, $q, $_GET['kodetrans']);

		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'kodebarang';

		$sql = "select B.KODEBARANG as KODE, B.NAMABARANG as NAMA, B.SATUAN,
					   B.SATUAN2, B.SATUAN3, B.KONVERSI1, B.KONVERSI2,
					   A.HARGA AS HARGABELI, B.HARGAJUAL, A.SISA
				from TPODTL A
				inner join MBARANG B on A.KODEBARANG = B.KODEBARANG
				where A.SISA > 0 and
					  A.TUTUP = 0 and
					  (B.KODEBARANG like '%' || ? || '%' or B.NAMABARANG like '%' || ? || '%') and
					  A.KODEPO = ?";
		$st = $db->prepare($sql);
		$query = $db->execute($st, $data_values);
		$rows = array();
		while ($rs = $db->fetch($query)) {
			$rows[] = $rs;
		}
		echo json_encode($rows);
	break;

	case 'jabatan' :
		$data_values = array($q, $q);

		$sql = "select jabatan
				from mjabatan
				order by jabatan asc";
		$st = $db->prepare($sql);
		$query = $db->execute($st, $data_values);
		$rows = array();
		while ($rs = $db->fetch($query)) {
			$rows[] = $rs;
		}
		echo json_encode($rows);
	break;

	case 'barang_supplier' :
		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'kodebarang';
		$status = isset($_GET['status']) && $_GET['status']=='all' ? '' : 'and status=1';
		$supplier = $_GET[supplier];

		$sql = "select first 30 skip 0
					   a.kodebarang as kode, a.namabarang as nama, a.satuan, a.satuan2,
					   a.satuan3, a.konversi1, a.konversi2, a.hargabeli, a.hargajual
				from mbarang a
				inner join mbarangsupplier b on a.kodebarang = b.kodebarang
				where (a.kodebarang like '%'||?||'%' or a.namabarang like '%'||?||'%') and
					  baranglain = 0
					  $status and
					  b.kodesupplier = '$supplier'
				order by a.$sort $order";
		$st = $db->prepare($sql);
		$query = $db->execute($st, array($q, $q));
		$rows = array();
		while ($rs = $db->fetch($query)) {
			$rows[] = $rs;
		}
		echo json_encode($rows);
	break;

	case 'supplier' :
		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'kodesupplier';
		$status = isset($_GET['status']) && $_GET['status']=='all' ? '' : 'and status=1';

		$sql = "select first 30 skip 0
					   kodesupplier as kode, namasupplier as nama, alamat, kota,
					   telp, kodesyaratbayar, contactperson
				from msupplier
				where (kodesupplier like '%'||?||'%' or namasupplier like '%'||?||'%')
					  $status
				order by $sort $order";
		$st = $db->prepare($sql);
		$query = $db->execute($st, array($q, $q));
		$rows = array();
		while ($rs = $db->fetch($query)) {
			$rows[] = $rs;
		}
		echo json_encode($rows);
	break;

	case 'instansi' :
		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'namainstansi';
		$status = isset($_GET['status']) && $_GET['status']=='all' ? '' : 'and status=1';

		$sql = "select first 30 skip 0
					   kodeinstansi as kode, namainstansi as nama, alamat, kota, telp, fax
				from minstansi
				where (kodeinstansi like '%'||?||'%' or namainstansi like '%'||?||'%')
					  $jenis
					  $status
				order by $sort $order";
		$st = $db->prepare($sql);
		$query = $db->execute($st, array($q, $q));
		$rows = array();
		while ($rs = $db->fetch($query)) {
			$rows[] = $rs;
		}
		echo json_encode($rows);
	break;

	case 'juru_bayar' :
		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'a.namajurubayar';
		$status = isset($_GET['status']) && $_GET['status']=='all' ? '' : 'and a.status=1';
		$instansi = isset($_GET['instansi']) && $_GET['instansi']=='' ? '' : $_GET['instansi'];
		if ($instansi!='') $instansi=" and a.kodeinstansi='$instansi'";

		$sql = "select first 30 skip 0
					   a.kodejurubayar as kode, a.namajurubayar as nama, b.namainstansi, a.alamat, a.kota, a.telp
				from mjurubayar a
				inner join minstansi b on a.kodeinstansi = b.kodeinstansi
				where (a.kodejurubayar like '%'||?||'%' or a.namajurubayar like '%'||?||'%')
					  $jenis
					  $instansi
				$status order by $sort $order";
		$st = $db->prepare($sql);
		$query = $db->execute($st, array($q, $q));
		$rows = array();
		while ($rs = $db->fetch($query)) {
			$rows[] = $rs;
		}
		echo json_encode($rows);
	break;

	case 'marketing' :
		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'kodemarketing';
		$status = $_GET['status']=='all' ? '' : 'and status=1';

		$sql = "select kodemarketing as kode, namamarketing as nama, alamat, kota
				from mmarketing
				where (kodemarketing like '%'||?||'%' or namamarketing like '%'||?||'%')
				$status order by $sort $order";
		$st = $db->prepare($sql);
		$query = $db->execute($st, array($q, $q));
		$rows = array();
		while ($rs = $db->fetch($query)) {
			$rows[] = $rs;
		}
		echo json_encode($rows);
	break;

	case 'customer' :
		if ($_GET['status']=='0') {
			$status=' and a.status=0';
		} else if ($_GET['member']=='all') {
			$status='';
		} else if ($_GET['member']=='yes') {
			$status=' and a.status=2';
		} else if ($_GET['member']=='no') {
			$status=' and a.status=1';
		}

		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'a.kodecustomer';

		//$npwp   = $_GET['npwp']=='' ? '' : 'and a.npwp=\''.$_GET['npwp'].'\'';

		$filter = filter_datagrid($_POST['filterRules']);


		$sql = 'select count(*) as row
				from (
					select a.kodecustomer as kode, a.namacustomer as nama, a.alamat, a.kota,
						   a.telp, a.hp, a.tempatlahir, a.tgllahir, a.jeniskelamin, a.riwayatkesehatan,
						   a.pekerjaan, a.hobi, b.kodemember, b.nokartu, b.diskon
					from mcustomer a
					left join mmember b on a.kodecustomer = b.kodecustomer
					where 1=1 and (a.kodecustomer like \'%\'||?||\'%\' or a.namacustomer like \'%\'||?||\'%\' or b.nokartu like \'%\'||?||\'%\') '.$status.'
					order by '.$sort.' '.$order.'
				) where 1=1 '.$filter->sql;

		$st = $db->prepare($sql);
		$qr = $db->execute($st, array_merge(array($q, $q, $q), $filter->param));
		$r = $db->fetch($qr);
		$result["total"] = $r->ROW;

		$result["rows"] = array();

		$sql = str_replace('count(*) as row', "first $rows skip $offset *", $sql);
		$st = $db->prepare($sql);
		$query = $db->execute($st, array_merge(array($q, $q, $q), $filter->param));
		$rows = array();
		while ($rs = $db->fetch($query)) {
			$result["rows"][] = $rs;
		}
		echo json_encode($result);
	break;

	case 'customer_pelunasan' :
		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'a.kodecustomer';
		$status = isset($_GET['status']) && $_GET['status']=='all' ? '' : 'and a.status=1';
		$instansi = isset($_GET['instansi']) && $_GET['instansi']=='' ? '' : $_GET['instansi'];
		if ($instansi!='') $instansi=" and d.kodeinstansi='$instansi'";

		$st = $db->prepare("select count(*) as row from mcustomer a
							inner join kartupiutang b on a.kodecustomer=b.kodecustomer
							inner join minstansi d on b.kodeinstansi=d.kodeinstansi
							where (a.kodecustomer like '%'||?||'%' or a.namacustomer like '%'||?||'%') $status");
		$qr = $db->execute($st, array($q, $q));
		$r = $db->fetch($qr);
		$result["total"] = $r->ROW;

		$result["rows"] = array();
		$sql = "select first $rows skip $offset
					   distinct a.kodecustomer as kode, a.namacustomer as nama, a.alamat, a.kota,
					   a.telp, a.hp
				from mcustomer a
				inner join kartupiutang b on a.kodecustomer=b.kodecustomer
				inner join minstansi d on b.kodeinstansi=d.kodeinstansi
				where (a.kodecustomer like '%'||?||'%' or a.namacustomer like '%'||?||'%')
					   $status $instansi and b.sisa>0
				order by $sort $order";

		$st = $db->prepare($sql);
		$query = $db->execute($st, array($q, $q));
		$rows = array();
		while ($rs = $db->fetch($query)) {
			$result["rows"][] = $rs;
		}
		echo json_encode($result);
	break;

	case 'pegawai' :
		$sort     = isset($_POST['sort']) ? strval($_POST['sort']) : 'userid';
		$status   = $_GET['status']=='all' ? '' : 'and a.status=1';
		$temp_sql = $_GET['jabatan']<>'' ? ' and '.$_GET['jabatan'].' = 1' : '';

		$sql = "select first 30 skip 0
					   a.userid as kode, a.username as nama, a.alamat, a.kota, a.telp,
					   a.tempatlahir, a.tgllahir, a.jeniskelamin, a.jabatan, a.kodelokasi
				from muser a
				where (a.userid like '%'||?||'%' or a.username like '%'||?||'%')
					   $status and
					   a.kodelokasi = ?
					   $temp_sql
				order by $sort $order";
		$st = $db->prepare($sql);
		$query = $db->execute($st, array($q, $q, $_SESSION['KODELOKASI']));
		$rows = array();
		while ($rs = $db->fetch($query)) {
			$rows[] = $rs;
		}
		echo json_encode($rows);
	break;

	case 'gambar_lensa' :
		$sort     = isset($_POST['sort']) ? strval($_POST['sort']) : 'kodelensa';
		$status   = $_GET['status']=='all' ? '' : 'and status=1';

		$sql = "select first 30 skip 0 *
				from mgambarlensa
				where (kodelensa like '%'||?||'%' or namalensa like '%'||?||'%')
					   $status
				order by urutan";
		$st = $db->prepare($sql);
		$query = $db->execute($st, array($q, $q));
		$rows = array();
		while ($rs = $db->fetch($query)) {
			$rows[] = $rs;
		}
		echo json_encode($rows);
	break;

	case 'lokasi' :
		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'a.kode';
		$status = isset($_GET['status']) && $_GET['status']=='all' ? '' : 'and a.status=1';
		$sql = "select a.kode as kode, a.nama as nama
				from mlokasi a
				where (a.kode like '%'||?||'%' or a.nama like '%'||?||'%')
				$status order by $sort $order";
		$st = $db->prepare($sql);
		$query = $db->execute($st, array($q, $q));
		$rows = array();
		while ($rs = $db->fetch($query)) {
			$rows[] = $rs;
		}
		echo json_encode($rows);
	break;

	case 'semua_lokasi' :
		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'kodelokasi';
		$status = isset($_GET['status']) && $_GET['status']=='all' ? '' : 'and status=1';

		$sql = "select kodelokasi as kode, namalokasi as nama
				from mlokasi
				where (kodelokasi like '%'||?||'%' or namalokasi like '%'||?||'%')
				$status order by $sort $order";
		$st = $db->prepare($sql);
		$query = $db->execute($st, array($q, $q));
		$rows = array();
		while ($rs = $db->fetch($query)) {
			$rows[] = $rs;
		}
		echo json_encode($rows);
	break;

	case 'gudang' :
		$sort   = isset($_POST['sort']) ? strval($_POST['sort']) : 'kodegudang';
		$status = isset($_GET['status']) && $_GET['status']=='all' ? '' : 'and status=1';
		$lokasi = isset($_GET['kodelokasi']) ? strval($_GET['kodelokasi']) : $_SESSION['KODELOKASI'];
		$tempjenis = '';
		if ($_GET['jenis']=='norusak'){
			$tempjenis=' and jenis<>2';
		} else if ($_GET['jenis']!=''){
			$tempjenis=' and jenis='.$_GET['jenis'];
		}


		$sql = "select kodegudang as kode, namagudang as nama, jenis
				from mgudang
				where (kodegudang like '%'||?||'%' or namagudang like '%'||?||'%') and
					  kodelokasi = '$lokasi'
					  $status $tempjenis
				order by $sort $order";

		$st = $db->prepare($sql);
		$query = $db->execute($st, array($q, $q));
		$rows = array();
		while ($rs = $db->fetch($query)) {
			$rows[] = $rs;
		}
		echo json_encode($rows);
	break;

	case 'syarat_bayar' :
		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'kodesyaratbayar';
		$status = isset($_GET['status']) && $_GET['status']=='all' ? '' : 'and status=1';

		$sql = "select kodesyaratbayar as kode, namasyaratbayar as nama, selisih
				from msyaratbayar
				where (kodesyaratbayar like '%'||?||'%' or namasyaratbayar like '%'||?||'%')
					  $status
				order by $sort $order";
		$st = $db->prepare($sql);
		$query = $db->execute($st, array($q, $q));
		$rows = array();
		while ($rs = $db->fetch($query)) {
			$rows[] = $rs;
		}
		echo json_encode($rows);
	break;

	case 'penanggungjawab_returjual' :
		$kodetrans = $_GET['kodetrans'];

		$sql = "select distinct * from (
				select b.userid as kode, b.username as nama
				from tjual a
				inner join muser b on a.kodepegawai_sales = b.userid
				where a.kodejual = ?

				union all

				select b.userid as kode, b.username as nama
				from tjual a
				inner join muser b on a.kodepegawai_ro = b.userid
				where a.kodejual = ?

				union all

				select b.userid as kode, b.username as nama
				from tjual a
				inner join muser b on a.kodepegawai_edger = b.userid
				where a.kodejual = ?

				union all

				select b.userid as kode, b.username as nama
				from tjual a
				inner join muser b on a.kodepegawai_fitting = b.userid
				where a.kodejual = ?

				union all

				select b.userid as kode, b.username as nama
				from tjual a
				inner join muser b on a.kodepegawai_setel = b.userid
				where a.kodejual = ?)";
		$st = $db->prepare($sql);
		$query = $db->execute($st, array($kodetrans, $kodetrans, $kodetrans, $kodetrans, $kodetrans));
		$rows = array();
		while ($rs = $db->fetch($query)) {
			$rows[] = $rs;
		}
		echo json_encode($rows);
	break;

	case 'currency' :
		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'namacurrency';
		$status = isset($_GET['status']) && $_GET['status']=='all' ? '' : 'and status=1';

		$sql = "select kodecurrency as kode, namacurrency as nama, simbol, tanda
			    from mcurrency
				where (kodecurrency like '%'||?||'%' or namacurrency like '%'||?||'%')
					  $status
				order by $sort $order";
		$st = $db->prepare($sql);
		$query = $db->execute($st, array($q, $q));
		$rows = array();
		while ($rs = $db->fetch($query)) {
			$rows[] = $rs;
		}
		echo json_encode($rows);
	break;

	case 'kode_perkiraan' :
		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'a.kode';
		$status = isset($_GET['status']) && $_GET['status']=='all' ? '' : 'and a.status=1';
		$jenis = isset($_POST['jenis']) ? $_POST['jenis'] : '';
		$temp_sql = '';

		if ($jenis=='kas') $temp_sql = 'and a.kasbank=1';
		else if ($jenis=='bank') $temp_sql = 'and a.kasbank=2';
		else if ($jenis=='kas_bank') $temp_sql = 'and a.kasbank!=0';
		else if ($jenis=='detail') $temp_sql = "and a.tipe='DETAIL'";

		$sql = "select first 30 skip 0
					   a.kode, a.namaperkiraan as nama, a.kelompok, a.saldo, a.status
				from mperkiraan a
				inner join mperkiraanlokasi b on a.kode = b.kodeperkiraan
				where (a.kode like '%'||?||'%' or a.namaperkiraan like '%'||?||'%') and
					  b.kodelokasi = ?
					  $temp_sql
					  $status
				order by $sort $order";
		$st = $db->prepare($sql);
		$query = $db->execute($st, array($q, $q, $_SESSION['KODELOKASI']));
		$rows = array();
		while ($rs = $db->fetch($query)) {
			$rows[] = $rs;
		}
		echo json_encode($rows);
	break;

	case 'kode_perkiraan_header' :
		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'kodeperkiraan';
		$status = isset($_GET['status']) && $_GET['status']=='all' ? '' : 'and status=1';

		$sql = "select first 30 skip 0
					   kodeperkiraan as kode, namaperkiraan as nama, kelompok, saldo, status
				from mperkiraan
				where (kodeperkiraan like '%'||?||'%' or namaperkiraan like '%'||?||'%') and
					  tipe = 'HEADER'
					  $status
				order by $sort $order";
		$st = $db->prepare($sql);
		$query = $db->execute($st, array($q, $q, $_SESSION['KODELOKASI']));
		$rows = array();
		while ($rs = $db->fetch($query)) {
			$rows[] = $rs;
		}
		echo json_encode($rows);
	break;

	case 'kode_perkiraan_askes' :
		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'kode';
		$status = isset($_GET['status']) && $_GET['status']=='all' ? '' : 'and a.status=1';
		$jenis = isset($_GET['jenis']) ? $_GET['jenis'] : '';
		$temp_sql = " and a.kasbank!=0 and a.tipe='DETAIL'";

		$sql = "select * from (
					select a.kodeperkiraan as kode, a.namaperkiraan as nama, a.kelompok, a.saldo, a.status
					from mperkiraan a
					inner join mperkiraanlokasi b on a.kodeperkiraan = b.kodeperkiraan
					where (a.kodeperkiraan like '%'||?||'%' or a.namaperkiraan like '%'||?||'%') and
						  b.kodelokasi = ?
						  $temp_sql
						  $status
					union all
					select a.kodeperkiraan as kode, a.namaperkiraan as nama, a.kelompok, a.saldo, a.status
					from mperkiraan a
					inner join mperkiraanlokasi b on a.kodeperkiraan = b.kodeperkiraan
					where (a.kodeperkiraan like '%'||?||'%' or a.namaperkiraan like '%'||?||'%') and
						  b.kodelokasi = ?
						  and a.kodeperkiraan = ?
						  $status
				) order by $sort $order";

		$st = $db->prepare($sql);
		$query = $db->execute($st, array($q, $q, $_SESSION['KODELOKASI'], $q, $q, $_SESSION['KODELOKASI'], '4211'));
		$rows = array();
		while ($rs = $db->fetch($query)) {
			$rows[] = $rs;
		}
		echo json_encode($rows);
	break;

	case 'kode_perkiraan_hutang' :
		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'kode';
		$status = isset($_GET['status']) && $_GET['status']=='all' ? '' : 'and a.status=1';
		$jenis = isset($_GET['jenis']) ? $_GET['jenis'] : '';
		$temp_sql = " and a.kasbank!=0 and a.tipe='DETAIL'";

		$sql = "select * from (
					select a.kodeperkiraan as kode, a.namaperkiraan as nama, a.kelompok, a.saldo, a.status
					from mperkiraan a
					inner join mperkiraanlokasi b on a.kodeperkiraan = b.kodeperkiraan
					where (a.kodeperkiraan like '%'||?||'%' or a.namaperkiraan like '%'||?||'%') and
						  b.kodelokasi = ?
						  $temp_sql
						  $status
					union all
					select a.kodeperkiraan as kode, a.namaperkiraan as nama, a.kelompok, a.saldo, a.status
					from mperkiraan a
					inner join mperkiraanlokasi b on a.kodeperkiraan = b.kodeperkiraan
					where (a.kodeperkiraan like '%'||?||'%' or a.namaperkiraan like '%'||?||'%') and
						  b.kodelokasi = ?
						  and a.kodeperkiraan = ?
						  $status
				) order by $sort $order";

		$st = $db->prepare($sql);
		$query = $db->execute($st, array($q, $q, $_SESSION['KODELOKASI'], $q, $q, $_SESSION['KODELOKASI'], '1133'));
		$rows = array();
		while ($rs = $db->fetch($query)) {
			$rows[] = $rs;
		}
		echo json_encode($rows);
	break;

	case 'promo' :
		$sort    = isset($_POST['sort']) ? strval($_POST['sort']) : 'a.kodepromo';
		$status  = $_GET['status']=='all' ? '' : 'and a.status=1';

		$sql = "select a.kodepromo as kode, a.namapromo as nama, a.persentase, a.amount, a.tglberlakuakhir
				from mpromo a
				inner join mpromolokasi b on a.kodepromo = b.kodepromo
				where (a.kodepromo like '%'||?||'%' or a.namapromo like '%'||?||'%') and
					   ? between a.tglberlakuawal and a.tglberlakuakhir and
					   b.kodelokasi = ?
					   $status
				order by $sort $order ";
		$st = $db->prepare($sql);
		$query = $db->execute($st, array($q, $q, date('Y.m.d'), $_SESSION['KODELOKASI']));
		$rows = array();
		while ($rs = $db->fetch($query)) {
			$rows[] = $rs;
		}
		echo json_encode($rows);
	break;

	case 'user_id' :
		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'userid';

		$sql   = "select userid, username
				  from muser
				  where userid like '%'||?||'%'
				  order by $sort $order";
		$st = $db->prepare($sql);
		$query = $db->execute($st, array($q));
		$rows = array();
		while ($rs = $db->fetch($query)) {
			$rows[] = $rs;
		}
		echo json_encode($rows);
	break;

	case 'PO' :
		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'kodepo';
		$status = isset($_GET['status']) && $_GET['status']=='all' ? '' : 'and status=1';

		$sql = "select first 30 skip 0
					   kodepo, tgltrans, namasupplier, grandtotal
				from tpo
				where status='I'
				order by $sort $order";
		$query = $db->query($sql);
		$rows = array();
		while ($rs = $db->fetch($query)) {
			$rows[] = $rs;
		}
		echo json_encode($rows);
	break;

	case 'rekam_medis' :
		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'a.koderekammedis';
		$customer = isset($_GET['customer']) ? $_GET['customer'] : '';

		$sql = "select A.*, b.username as namapegawai_ro
				from trekammedis a
				left join muser b on a.ro = b.userid
				where a.status = 'I' and
					  a.kodecustomer = '$customer'
				order by a.tgltrans desc";
		$query = $db->query($sql);
		$rows = array();
		while ($rs = $db->fetch($query)) {
			$rows[] = $rs;
		}
		echo json_encode($rows);
	break;

	case 'SO' :
		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'kodeso';
		$status = isset($_GET['status']) && $_GET['status']=='all' ? '' : 'and status=1';

		$sql = "select first 30 skip 0
					   kodeso, tgltrans, namasupplier, grandtotal
				from tso
				where status = 'I'
				order by $sort $order";
		$query = $db->query($sql);
		$rows = array();
		while ($rs = $db->fetch($query)) {
			$rows[] = $rs;
		}
		echo json_encode($rows);
	break;

	// TRANSFER TITIPAN
	// BROWSING NO SO
	case 'sales_order_for_titipan' :
		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'a.kodeso';

		$kodelokasi_asal = $_GET['kodelokasi'];

		$sql = "select distinct B.KODESO as KODETRANS, B.NAMACUSTOMER, B.GRANDTOTAL, B.TGLTRANS
				from TSODTL A
				inner join TSO B on A.KODESO = B.KODESO and B.STATUS <> 'D' AND B.STATUS='S'
				left join TTRANSFERTITIPAN D on B.KODESO = D.KODESO and D.STATUS <> 'D'
				left join TTRANSFERTITIPANDTL C on D.KODETRANSFERTITIPAN = C.KODETRANSFERTITIPAN and A.KODEBARANG = C.KODEBARANG
				where C.KODEBARANG is null and
					  B.KODELOKASI = '$kodelokasi_asal' and
					  (B.NAMACUSTOMER like '%$q%' or A.KODESO like '%$q%')
				order by $sort $order";
		$query = $db->query($sql);
		$rows = array();
		while ($rs = $db->fetch($query)) {
			$rows[] = $rs;
		}
		echo json_encode($rows);
	break;

	// TRANSFER TITIPAN JENIS KIRIM
	// BROWSING NO SO YANG STATUS = 'S'
	case 'kodetrans_so' :
		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'kodeso';

		$kodelokasi_asal = $_GET['kodelokasi'];

		$sql = "select kodeso as kodetrans, tgltrans, namacustomer, grandtotal
				from tso
				where status = 'S' and
					  kodelokasi = '$kodelokasi_asal'
				order by $sort $order";
		$query = $db->query($sql);
		$rows = array();
		while ($rs = $db->fetch($query)) {
			$rows[] = $rs;
		}
		echo json_encode($rows);
	break;

	// TRANSFER TITIPAN JENIS TERIMA
	// BROWSING NO TRANSFER YANG STATUS = 'S'
	case 'kodetrans_kirim_transfer' :
		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'a.kodetransfertitipan';

		$kodelokasi_asal = $_GET['kodelokasi'];

		$sql = "select a.kodetransfertitipan as kodetrans, a.tgltrans, a.kodeso, c.namacustomer
				from ttransfertitipan a
				inner join tso b on a.kodeso=b.kodeso and b.status <> 'D'
				inner join mcustomer c on b.kodecustomer=c.kodecustomer
				where a.status = 'I' and
					  a.kodelokasiasal = '$kodelokasi_asal'
				order by $sort $order";
		$query = $db->query($sql);
		$rows = array();
		while ($rs = $db->fetch($query)) {
			$rows[] = $rs;
		}
		echo json_encode($rows);
	break;

	// TRANSFER TITIPAN JENIS KEMBALI TITIPAN
	// BROWSING NO TRANSFER YANG STATUS = 'S'
	case 'kodetrans_terima_transfer' :
		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'kodeterimatitipan';

		// mencari kodelokasi tujuan
		$kodelokasi_asal   = $_GET['kodelokasi_asal'];

		// mencari kodelokasi asal
		$kodelokasi_tujuan = $_GET['kodelokasi_tujuan'];

		$sql = "select a.kodeterimatitipan as kodetrans, a.tgltrans, c.kodeso, d.namacustomer
				from tterimatitipan a
				inner join ttransfertitipan b on a.kodetransfertitipan=b.kodetransfertitipan and b.status <> 'D'
				inner join tso c on c.kodeso=b.kodeso and c.status <> 'D'
				inner join mcustomer d on d.kodecustomer=c.kodecustomer
				left join tqualitycontrol e on c.kodeso=e.kodeso and e.status <> 'D'
				where a.status = 'S' and
					  a.kodelokasitujuan = '$kodelokasi_asal' and
					  a.kodelokasiasal = '$kodelokasi_tujuan' and
					  e.kodeqc is not null
				order by $sort $order";
		$query = $db->query($sql);
		$rows = array();
		while ($rs = $db->fetch($query)) {
			$rows[] = $rs;
		}
		echo json_encode($rows);
	break;

	// TRANSFER TITIPAN JENIS TERIMA TITIPAN
	// BROWSING NO TRANSFER YANG STATUS = 'S'
	case 'kodetrans_kembali_titipan' :
		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'kodekembalititipan';

		$lokasiasal = $_GET['kodelokasiasal'];
		$lokasitujuan = $_GET['kodelokasitujuan'];

		$sql = "select a.kodekembalititipan as kodetrans, a.tgltrans, d.kodeso, e.namacustomer
				from tkembalititipan a
				inner join tterimatitipan b on a.kodeterimatitipan=b.kodeterimatitipan and b.status <> 'D'
				inner join ttransfertitipan c on b.kodetransfertitipan=c.kodetransfertitipan and c.status <> 'D'
				inner join tso d on c.kodeso=d.kodeso and d.status <> 'D'
				inner join mcustomer e on d.kodecustomer=e.kodecustomer
				where a.status = 'S' and
					  a.kodelokasiasal = '$lokasiasal' and
					  a.kodelokasitujuan = '$lokasitujuan'
				order by $sort $order";
		$query = $db->query($sql);
		$rows = array();
		while ($rs = $db->fetch($query)) {
			$rows[] = $rs;
		}
		echo json_encode($rows);
	break;

	// SKRIP INI UTK MENAMPILKAN DATA KAS YANG DIGUNAKAN DI PELUNASAN PIUTANG
	case 'kodetrans_kas_belum_digunakan_piutang' :
		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'kodekas';
		$ref  = $_POST['referensi'];

		$data_customer = '';
		if (count($ref)>0) {
			$txt = '';
			$i = 0;
			foreach ($ref as $item) {
				$txt .= "or referensi='$item' ";
				$i++;
			}
			if ($i > 1)
				$data_customer = 'or ('. substr($txt, 2) .')';
			else
				$data_customer = 'and ('. substr($txt, 2) .')';

		}

		$sql = "select count(a.kodekas) as row
				from tkas a
				inner join tkasdtl b on a.kodekas=b.kodekas
				where (a.kodekas like '%'||?||'%' or a.nobuktimanual like '%'||?||'%') and
					  a.status = 'S'
					  $data_customer and
					  b.kodeperkiraan = ?";
		$st = $db->prepare($sql);
		$query = $db->execute($st, array($q, $q, $_SESSION['AYATSILANGPIUTANG']));
		$rs = $db->fetch($query);
		$result["total"] = $rs->ROW;
		$items 			 = array();

		$sql = "select first $rows skip $offset *
				from (
					select a.kodekas, a.nobg, a.tgltrans, (a.tgltrans-90) as TglTrans2, a.keterangan,
						   b.amountkurs as total, a.userentry, a.referensi, c.kodecustomer, c.namacustomer
					from tkas a
					inner join tkasdtl b on a.kodekas = b.kodekas
					left join mcustomer c on a.referensi = c.namacustomer and c.status=1
					where (a.kodekas like '%'||?||'%' or a.nobuktimanual like '%'||?||'%') and
						  b.kodeperkiraan = ? and
						  a.status = 'S'
						  $data_customer
				) order by $sort $order";
		$st = $db->prepare($sql);
		$query = $db->execute($st, array($q, $q, $_SESSION['AYATSILANGPIUTANG']));
		while ($rs = $db->fetch($query)) {
			$items[] = $rs;
		}
		$result["rows"] = $items;
		echo json_encode($result);
	break;

	case 'kodetrans_kas_belum_digunakan_hutang' :
		$sort   = isset($_POST['sort']) ? strval($_POST['sort']) : 'kodekas';
		$sql_ref = (isset($_POST['referensi']) and $_POST['referensi']!='') ? "and referensi='".$_POST['referensi']."'" : '';

		$sql = "select count(a.kodekas) as row
				from tkas a
				inner join tkasdtl b on a.kodekas = b.kodekas
				where (a.kodekas like '%'||?||'%' or a.nobuktimanual like '%'||?||'%') and
					  a.status = 'S'
					  $sql_ref and
					  b.kodeperkiraan = ?";
		$st = $db->prepare($sql);
		$query = $db->execute($st, array($q, $q, $_SESSION['AYATSILANGHUTANG']));
		$rs = $db->fetch($query);
		$result["total"] = $rs->ROW;
		$items 			 = array();

		$sql = "select first $rows skip $offset *
				from (
					select a.kodekas, a.nobg, a.tgltrans, (a.tgltrans-90)as tgltrans2, a.keterangan,
						   b.amountkurs as total, a.userentry, a.referensi, c.kodesupplier, c.namasupplier
					from tkas a
					inner join tkasdtl b on a.kodekas = b.kodekas
					left join msupplier c on a.referensi = c.namasupplier and c.status = 1
					where (a.kodekas like '%'||?||'%' or a.nobuktimanual like '%'||?||'%') and
						  b.kodeperkiraan=? and
						  a.status = 'S'
						  $sql_ref
				) order by $sort $order";
		$st = $db->prepare($sql);
		$query = $db->execute($st, array($q, $q, $_SESSION['AYATSILANGHUTANG']));
		while ($rs = $db->fetch($query)) {
			$items[] = $rs;
		}
		$result["rows"] = $items;
		echo json_encode($result);
	break;

	case 'faktur_pajak' :
		$sql = "select first 30 skip 0
					   distinct nofakturpajak as nama
				from tjual
				where nofakturpajak != ''
				order by nofakturpajak asc";
		$query = $db->query($sql);
		$rows = array();
		while ($rs = $db->fetch($query)) {
			$rows[] = $rs;
		}
		echo json_encode($rows);
	break;

	case 'kartu_piutang' :
		$sql_ref = (isset($_POST['kodecustomer']) and $_POST['kodecustomer']!='') ? "and kodecustomer='".$_POST['kodecustomer']."'" : '';

		$sql = "select kodetrans, tgltrans, tgljatuhtempo, sisa
				from kartupiutang
				where 1=1
					  $sql_ref and
					  sisa != 0
				order by kodetrans asc";
		$query = $db->query($sql);
		$rows = array();
		while ($rs = $db->fetch($query)) {
			$rows[] = $rs;
		}
		echo json_encode($rows);
	break;

	// browsing no retur jual di transaksi barang rusak
	case 'kodetrans_returjual' :
		$sql = "select a.namacustomer, a.tgltrans, a.kodereturjual, a.grandtotal,
					   a.kodepenanggungjawab, b.username as namapenanggungjawab
				from treturjual a
				inner join muser b on a.kodepenanggungjawab=b.userid
				where a.kodereturjual like '%'||?||'%' and
					  a.status <>'D' and
					  a.kodelokasi=?";
		$st = $db->prepare($sql);
		$query = $db->execute($st, array($q, $_SESSION['KODELOKASI']));
		$items = array();
		while ($rs = $db->fetch($query)) {
			$items[] = $rs;
		}
		echo json_encode($items);
	break;

	case 'kodetrans_jual' :
		$kode_customer = $_REQUEST['kode_customer'];

		$sql = "select a.kodejual as koderef, a.namacustomer, a.tgltrans,
					   b.kodereturjual, b.grandtotal
				from tjual a
				left join treturjual b on a.kodejual = b.kodejual and b.status <> 'D'
				where a.kodejual like '%'||?||'%' and
					  a.kodecustomer = ? and
					  a.status <>'D'";
		$st = $db->prepare($sql);
		$query = $db->execute($st, array($q, $kode_customer));
		$items = array();
		while ($rs = $db->fetch($query)) {
			$items[] = $rs;
		}
		echo json_encode($items);
	break;

	case 'kodetrans_jual_for_follow_up' :
		$data_param = array($_SESSION['KODELOKASI']);

		$tgl = $newdate = date('Y-m-d', strtotime('-14 days', strtotime(date('Y-m-d'))));

		$sql = "select first 50 skip 0 a.*, c.username as namasalesman, d.username as namaro, b.telp, i.tindakan_keterangan as riwayatkesehatan,
					   e.username as namasetel, f.username as namaedger, g.username as namafitting, h.tipeorder, h.tgltrans as tglpesan, h.tgljanji, j.kodemember,
					   i.TINDAKAN_R_SPH, i.TINDAKAN_R_CYL, i.TINDAKAN_R_AXIS, i.TINDAKAN_R_PRISM, i.TINDAKAN_R_VA, i.TINDAKAN_R_ADD, i.TINDAKAN_R_PD, i.TINDAKAN_PV,
					   i.TINDAKAN_L_SPH, i.TINDAKAN_L_CYL, i.TINDAKAN_L_AXIS, i.TINDAKAN_L_PRISM, i.TINDAKAN_L_VA, i.TINDAKAN_L_ADD, i.TINDAKAN_L_PD, i.TINDAKAN_SH
				from tjual a
				inner join mcustomer b on a.kodecustomer=b.kodecustomer
				left join muser c on a.kodepegawai_sales=c.userid
				left join muser d on a.kodepegawai_ro=d.userid
				left join muser e on a.kodepegawai_setel=e.userid
				left join muser f on a.kodepegawai_edger=f.userid
				left join muser g on a.kodepegawai_edger=g.userid
				left join tso h on a.kodeso=h.kodeso and h.status<>'D'
				left join trekammedis i on h.koderekammedis=i.koderekammedis
				left join mmember j on b.kodecustomer=j.kodecustomer
				where a.kodelokasi=? and
					  a.status <> 'D' and
					  a.tgltrans < '$tgl' and
					  a.kodejual like 'BP%' and
					  (a.kodejual like '$q%' or b.namacustomer like '$q%' or b.kodecustomer like '$q%')
				order by a.tgltrans desc, a.kodejual desc";
		$st = $db->prepare($sql);
		$q = $db->execute($st, $data_param);
		$items = array();
		while ($rs = $db->fetch($q)) {
			$items[] = $rs;
		}

		echo json_encode($items);
	break;

	case 'kodetrans_jual_returjual' :
		//$sort   = isset($_POST['sort']) ? strval($_POST['sort']) : 'kodekas';
		$kode_customer = isset($_POST['kode_customer']) ? "a.kodecustomer='".$_POST['kode_customer']."'" : '';

		$sql = "select count(*) as row
				from (
					select a.kodejual as koderef, a.namacustomer, a.tgltrans, a.grandtotal
					from tjual a
					where a.kodejual like '%'||?||'%' and
						  a.status <>'D'

					union all

					select a.kodereturjual as koderef, a.namacustomer, a.tgltrans, a.grandtotal
					from treturjual a
					where a.kodereturjual like '%'||?||'%' and
						  a.status <>'D'
				)";
		$st = $db->prepare($sql);
		$query = $db->execute($st, array($q, $q));
		$rs = $db->fetch($query);
		$result["total"] = $rs->ROW;

		$sql = "select first $rows skip $offset *
				from (
					select a.kodejual as koderef, a.namacustomer, a.tgltrans, a.grandtotal
					from tjual a
					where a.kodejual like '%'||?||'%' and
						  a.status <>'D'

					union all

					select a.kodereturjual as koderef, a.namacustomer, a.tgltrans, a.grandtotal
					from treturjual a
					where a.kodereturjual like '%'||?||'%' and
						  a.status <>'D'
				) order by tgltrans desc";
		$st = $db->prepare($sql);
		$query = $db->execute($st, array($q, $q));
		$items = array();
		while ($rs = $db->fetch($query)) {
			$items[] = $rs;
		}
		$result["rows"] = $items;
		echo json_encode($result);
	break;

	case 'kodetrans_beli_returbeli' :
		//$sort   = isset($_POST['sort']) ? strval($_POST['sort']) : 'kodekas';

		$sql = "select count(*) as row
				from (
					select a.kodebeli as koderef, a.namasupplier, a.tgltrans, a.grandtotal
					from tbeli a
					where a.kodebeli like '%'||?||'%' and
						  a.status <>'D'

					union all

					select a.kodereturbeli as koderef, a.namasupplier, a.tgltrans, a.grandtotal
					from treturbeli a
					where a.kodereturbeli like '%'||?||'%' and
						  a.status <>'D'
				)";
		$st = $db->prepare($sql);
		$query = $db->execute($st, array($q, $q));
		$rs = $db->fetch($query);
		$result["total"] = $rs->ROW;

		$sql = "select first $rows skip $offset *
				from (
					select a.kodebeli as koderef, a.namasupplier, a.tgltrans, a.grandtotal
					from tbeli a
					where a.kodebeli like '%'||?||'%' and
						  a.status <>'D'

					union all

					select a.kodereturbeli as koderef, a.namasupplier, a.tgltrans, a.grandtotal
					from treturbeli a
					where a.kodereturbeli like '%'||?||'%' and
						  a.status <>'D'
				) order by tgltrans desc";
		$st = $db->prepare($sql);
		$query = $db->execute($st, array($q, $q));
		$items = array();
		while ($rs = $db->fetch($query)) {
			$items[] = $rs;
		}
		$result["rows"] = $items;
		echo json_encode($result);
	break;

	case 'kodetrans_saldo_perkiraan' :
		$items = array();

		$sql = "select *
				from saldoperkiraan a
				where a.kodesaldoperkiraan like '%'||?||'%'
				and a.status <>'D'";
		$st = $db->prepare($sql);
		$query = $db->execute($st, array($q));
		while ($rs = $db->fetch($query)) {
			$items[] = $rs;
		}
		$result["total"] = count($items);
		$result["rows"] = $items;
		echo json_encode($result);
	break;

	case 'kodetrans_transfer' :
		$items = array();

		$sql = "select a.*, b.kodepr, b.catatan as catatanpr, c.kodeso, d.namacustomer
				from ttransfer a
				left join tpr b on a.kodetransreferensi=b.kodepr and b.status <> 'D'
				left join tso c on b.kodeso=c.kodeso and c.status <> 'D'
				left join mcustomer d on c.kodecustomer=d.kodecustomer
				where a.kodetransfer like '%'||?||'%' and
					  a.status = 'S' and
					  a.kodelokasitujuan = ? and
					  a.kodelokasiasal = ?";
		$st = $db->prepare($sql);
		$query = $db->execute($st, array($q, $_GET['kodelokasitujuan'], $_GET['kodelokasiasal']));
		while ($rs = $db->fetch($query)) {
			$items[] = $rs;
		}
		$result["total"] = count($items);
		$result["rows"] = $items;
		echo json_encode($result);
	break;

	case 'kodetrans_penerimaan' :
		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'kodepenerimaan';

		$kodesupplier = $_GET['kodesupplier'];

		$sql = "select distinct a.*
				from tpenerimaan a
				inner join msupplier b on a.kodesupplier = b.kodesupplier
				where a.status='S' and
					  a.kodepenerimaan like '%$q%' and
					  a.kodesupplier = '$kodesupplier'
				order by $sort";
		$query = $db->query($sql);
		$rows = array();
		while ($rs = $db->fetch($query)) {
			$rows[] = $rs;
		}
		echo json_encode($rows);
	break;

	case 'kodetrans_pembelian' :
		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'kodebeli';

		$kodesupplier = $_GET['kodesupplier'];

		// script lama
		/*$sql = "select distinct A.*
				from TBELI A
				inner join MSUPPLIER B on A.KODESUPPLIER = B.KODESUPPLIER
				left join TRETURBELI C on A.KODEBELI = C.KODEBELI and C.STATUS <> 'D'
				where A.STATUS <> 'D' and
					  C.KODERETURBELI is null and
					  a.kodebeli like '%$q%' and
					  a.kodesupplier = '$kodesupplier' and
					  a.kodelokasi = '".$_SESSION['KODELOKASI']."'
				order by $sort";*/

		// browsing beli bisa berulang kali. 27/12/2016
		$sql = "select A.KODEBELI, A.TGLTRANS, A.KODESUPPLIER, A.NAMASUPPLIER, A.GRANDTOTAL, A.NOINVOICESUPPLIER
				from TBELI A
				inner join TBELIDTL B on A.KODEBELI = B.KODEBELI
				left join TRETURBELI C on A.KODEBELI = C.KODEBELI and C.STATUS <> 'D'
				left join TRETURBELIDTL D on C.KODERETURBELI = D.KODERETURBELI and B.KODEBARANG = D.KODEBARANG
				where A.STATUS <> 'D' and
					  C.KODERETURBELI is null and
					  a.kodebeli like '%$q%' and
					  a.kodesupplier = '$kodesupplier' and
					  a.kodelokasi = '".$_SESSION['KODELOKASI']."'
				group by A.KODEBELI, A.TGLTRANS, A.KODESUPPLIER, A.NAMASUPPLIER, A.GRANDTOTAL, A.NOINVOICESUPPLIER

				union all

				select A.KODEBELI, A.TGLTRANS, A.KODESUPPLIER, A.NAMASUPPLIER, A.GRANDTOTAL, A.NOINVOICESUPPLIER
				from TBELI A
				inner join TBELIDTL B on A.KODEBELI = B.KODEBELI
				left join TRETURBELI C on A.KODEBELI = C.KODEBELI and C.STATUS <> 'D'
				left join TRETURBELIDTL D on C.KODERETURBELI = D.KODERETURBELI and B.KODEBARANG = D.KODEBARANG
				where A.STATUS <> 'D' and
					  a.kodebeli like '%$q%' and
					  a.kodesupplier = '$kodesupplier' and
					  a.kodelokasi = '".$_SESSION['KODELOKASI']."'
				group by A.KODEBELI, A.TGLTRANS, A.KODESUPPLIER, A.NAMASUPPLIER, A.GRANDTOTAL, A.NOINVOICESUPPLIER
				having sum(D.JML) < sum(B.JML)";
		$query = $db->query($sql);
		$rows = array();
		while ($rs = $db->fetch($query)) {
			$rows[] = $rs;
		}
		echo json_encode($rows);
	break;

	case 'kodetrans_penjualan' :
		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'kodejual';

		$kodecustomer = $_GET['kodecustomer'];

		$sql = "select a.*
				from tjual a
				inner join mcustomer b on a.kodecustomer = b.kodecustomer
				left join treturjual c on a.kodejual = c.kodejual and c.status <> 'D'
				where a.status<>'D' and
					  c.kodereturjual is null and
					  a.kodejual like '%$q%' and
					  a.kodecustomer = '$kodecustomer' and
					  a.kodelokasi = '".$_SESSION['KODELOKASI']."'
				order by $sort";
		$query = $db->query($sql);
		$rows = array();
		while ($rs = $db->fetch($query)) {
			$rows[] = $rs;
		}
		echo json_encode($rows);
	break;

	case 'alasan' :
		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'alasan';

		$jtrans = $_GET['jtrans'];

		$sql = "select *
				from malasan
				where jenistransaksi = '$jtrans'
				order by $sort";
		$query = $db->query($sql);
		$rows = array();
		while ($rs = $db->fetch($query)) {
			$rows[] = $rs;
		}
		echo json_encode($rows);
	break;

	case 'kodetrans_opname' :
		$sort       = isset($_POST['sort']) ? strval($_POST['sort']) : 'kodeopname';
		$kodegudang = $_GET['kodegudang'];

		$sql = "select distinct a.*
				from topnamestok a
				where (a.status='I' or a.status='S') and
					  a.kodeopname like '%$q%' and
					  a.kodegudang = '$kodegudang'
				order by $sort";
		$query = $db->query($sql);
		$rows = array();
		while ($rs = $db->fetch($query)) {
			$rows[] = $rs;
		}
		echo json_encode($rows);
	break;

	// browsing kodetrans PR ketika transfer
	case 'kodetrans_pr' :
		$sql = "select distinct a.kodepr, a.tgltrans, a.kodeso, b.namacustomer
				from tpr a
				left join mcustomer b on a.kodecustomer = b.kodecustomer
				where (a.status='S' or a.status='I') and
					  a.kodelokasi = '".$_GET['kodelokasi']."'
				order by a.tgltrans desc, a.kodepr";
		$query = $db->query($sql);
		$rows = array();
		while ($rs = $db->fetch($query)) {
			$rows[] = $rs;
		}
		echo json_encode($rows);
	break;

	// browsing kode PO ketika PEMBELIAN
	case 'kodetrans_po' :
		$sql = "select distinct a.*
				from tpo a
				inner join tpodtl b on a.kodepo = b.kodepo
				where b.sisa > 0 and
					  a.status='S' and
					  a.kodepo like '%$q%' and
					  a.kodesupplier='".$_GET['kodesupplier']."'
				order by a.tgltrans asc, a.kodepo";
		$query = $db->query($sql);
		$rows = array();
		while ($rs = $db->fetch($query)) {
			$rows[] = $rs;
		}
		echo json_encode($rows);
	break;

	// browsing kode order retur jual ketika BBM
	case 'kodetrans_order_retur_jual' :
		$temp_sql = $_GET['kodecustomer']!='' ? "and a.kodecustomer='".$_GET['kodecustomer']."'" : '';

		$sql = "select distinct a.*
				from torderreturjual a
				inner join torderreturjualdtl b on a.kodeorderreturjual = b.kodeorderreturjual
				where b.sisa>0 and
					  a.status<>'D'
					  $temp_sql
				order by a.tgltrans asc, a.kodeorderreturjual";
		$query = $db->query($sql);
		$rows = array();
		while ($rs = $db->fetch($query)) {
			$rows[] = $rs;
		}
		echo json_encode($rows);
	break;

	// browsing kode order retur beli ketika BBK
	case 'kodetrans_order_retur_beli' :
		$temp_sql = $_GET['kodesupplier']!='' ? "and a.kodesupplier='".$_GET['kodesupplier']."'" : '';

		$sql = "select distinct a.*
				from torderreturbeli a
				inner join torderreturbelidtl b on a.kodeorderreturbeli = b.kodeorderreturbeli
				where b.sisa>0 and
					  a.status<>'D'
					  $temp_sql
				order by a.tgltrans asc, a.kodeorderreturbeli";
		$query = $db->query($sql);
		$rows = array();
		while ($rs = $db->fetch($query)) {
			$rows[] = $rs;
		}
		echo json_encode($rows);
	break;

	// browsing kode down payment ketika bank masuk/keluar
	case 'kodetrans_downpayment' :
		$sql = '';
		if ($_REQUEST['jenistrans']=='so') {
			$sql = "select a.*, b.namacustomer as namareferensi
					from tdownpayment a
					inner join tso b on a.kodetransreferensi=b.kodeso
					where a.jenistransaksi='SO' and
						  a.status='I' and
						  a.kodedownpayment like '%$q%'";
		} else {
			$sql = "select a.*, b.namasupplier as namareferensi
					from tdownpayment a
					inner join tpo b on a.kodetransreferensi = b.kodepo
					where a.jenistransaksi = 'PO' and
						  a.status = 'I' and
						  a.kodedownpayment like '%$q%'";
		}
		$rows = array();
		$query = $db->query($sql);
		while ($rs = $db->fetch($query)) {
			$rows[] = $rs;
		}

		echo json_encode($rows);
	break;

	case 'tarif_aktiva' :
		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'kodetarif';
		$status = isset($_GET['status']) && $_GET['status']=='all' ? '' : 'and status=1';

		$sql = "select kodetarif as kode, namatarif as nama, persentase
				from mtarifaktiva
				where (kodetarif like '%'||?||'%' or namatarif like '%'||?||'%')
					   $status
				order by $sort $order";
		$st = $db->prepare($sql);
		$query = $db->execute($st, array($q, $q));
		$rows = array();
		while ($rs = $db->fetch($query)) {
			$rows[] = $rs;
		}
		echo json_encode($rows);
	break;

	case 'kodetrans_closing_hpp' :
		$sql = "select *
				from mclosing
				where jenisclosing = 'HPP'
				order by tglawal asc";
		$query = $db->query($sql);
		$rows = array();
		while ($rs = $db->fetch($query)) {
			$rows[] = $rs;
		}
		echo json_encode($rows);
	break;

	case 'kodetrans_closing_akuntansi' :
		$sql = "select *
				from mclosing
				where jenisclosing = 'AKUNTANSI' and
					  kodelokasi = '".$_SESSION['KODELOKASI']."'
				order by tglawal asc";
		$query = $db->query($sql);
		$rows = array();
		while ($rs = $db->fetch($query)) {
			$rows[] = $rs;
		}
		echo json_encode($rows);
	break;

	case 'kodetrans_split_data' :
		$sql = "select *
				from tsplit
				order by tglawal asc";
		$query = $db->query($sql);
		$rows = array();
		while ($rs = $db->fetch($query)) {
			$rows[] = $rs;
		}
		echo json_encode($rows);
	break;

	case 'kodetrans_jual_kredit' :
		$sql = "select A.KODEJUAL, A.TGLTRANS, A.KODEJURUBAYAR, D.NAMAJURUBAYAR, A.GRANDTOTAL, sum(B.AMOUNT) as PELUNASAN
				from TJUAL A
				left join PELUNASANPIUTANGDTL B on A.KODEJUAL = B.KODETRANS
				left join PELUNASANPIUTANG C on B.KODEPELUNASAN = C.KODEPELUNASAN and C.STATUS <> 'D'
				inner join MJURUBAYAR D on A.KODEJURUBAYAR = D.KODEJURUBAYAR
				where A.STATUS <> 'D' and
					  a.kodejual like '%$q%'
				group by A.KODEJUAL, A.TGLTRANS, A.KODEJURUBAYAR, D.NAMAJURUBAYAR, A.GRANDTOTAL
				having sum(B.AMOUNT) - A.GRANDTOTAL < 0 or sum(B.AMOUNT) is null";
		$query = $db->query($sql);
		$rows = array();
		while ($rs = $db->fetch($query)) {
			$rows[] = $rs;
		}
		echo json_encode($rows);
	break;

	case 'selisih_jatuh_tempo' :
		$tgl     = $_POST[tgl];
		$selisih = $_POST[selisih];
		echo selisih_jatuh_tempo($selisih, $tgl);
	break;

	case 'kategori_menu' :
		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'nama';
		$status = isset($_GET['status']) && $_GET['status']=='all' ? '' : 'and status=1';

		$rows = array();
		$sql = "select kodekategorimenu as kode, namakategorimenu as nama
				from mkategorimenu
				where (kodekategorimenu like '%'||?||'%' or namakategorimenu like '%'||?||'%')
					  $status
				order by $sort $order";
		$st = $db->prepare($sql);
		$query = $db->execute($st, array($q, $q, $tipe));
		while ($rs = $db->fetch($query)) {
			$rows[] = $rs;
		}
		echo json_encode($rows);
	break;
	
	case 'satuan' :
		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'namasatuan';
		$status = isset($_GET['status']) && $_GET['status']=='all' ? '' : 'and status=1';

		$rows = array();
		$sql = "select kodesatuan as kode, namasatuan as nama
				from msatuan
				where (kodesatuan like '%'||?||'%' or namasatuan like '%'||?||'%')
					  $status
				order by $sort $order";
		$st = $db->prepare($sql);
		$query = $db->execute($st, array($q, $q, $tipe));
		while ($rs = $db->fetch($query)) {
			$rows[] = $rs;
		}
		echo json_encode($rows);
	break;
	case 'resep_barang' :
		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'nama';
		$status = isset($_GET['status']) && $_GET['status']=='all' ? '' : 'and status=1';

		$rows = array();
		$sql = "select a.kode as kode, a.nama as nama,b.kodesatuan as satuan,
				b.satuan2 as satuan2,b.konversi as konversi
				from mbarang a
				left join msatuan b on a.satuan = b.kodesatuan
				where (kode like '%'||?||'%' or nama like '%'||?||'%')
				order by $sort $order";
		$st = $db->prepare($sql);
		$query = $db->execute($st, array($q, $q, $tipe));
		while ($rs = $db->fetch($query)) {
			$rows[] = $rs;
		}
		echo json_encode($rows);
	break;
	case 'resep_sub_resep' :
		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'nama';
		$status = isset($_GET['status']) && $_GET['status']=='all' ? '' : 'and status=1';

		$rows = array();
		$sql = "select a.kode as kode, a.nama as nama,a.grandtotal as grandtotal,
				b.kodesatuan as satuan,b.satuan2 as satuan2,b.konversi as konversi
				from mrecipe a
				left join msatuan b on a.satuan = b.kodesatuan
				where (a.kode like '%'||?||'%' or a.nama like '%'||?||'%')
				order by $sort $order";
		$st = $db->prepare($sql);
		$query = $db->execute($st, array($q, $q, $tipe));
		while ($rs = $db->fetch($query)) {
			$rows[] = $rs;
		}
		echo json_encode($rows);
	break;
	
	default:
		echo json_encode(array());
	//=================Departemen , Kategori, Jenis, Perkiraan
	case 'departemen_barang' :
		$tipe = $_GET['tipe'];
		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'namadepartemenbarang';
		$status = isset($_GET['status']) && $_GET['status']=='all' ? '' : 'and status=1';

		$rows = array();
		$sql = "select kodedepartemenbarang as kode, namadepartemenbarang as nama
				from mdepartemenbarang
				order by $sort $order";
		$st = $db->prepare($sql);
		$query = $db->execute($st, array($q, $q, $tipe));
		while ($rs = $db->fetch($query)) {
			$rows[] = $rs;
		}
		echo json_encode($rows);
	break;
	
	case 'kategori_barang' :
		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'namakategoribarang';
		$status = isset($_GET['status']) && $_GET['status']=='all' ? '' : 'and status=1';

		$rows = array();
		$sql = "select kodekategoribarang as kode, namakategoribarang as nama
				from mkategoribarang
				order by $sort $order";
		$st = $db->prepare($sql);
		$query = $db->execute($st, array($q, $q));
		while ($rs = $db->fetch($query)) {
			$rows[] = $rs;
		}
		echo json_encode($rows);
	break;
	
	case 'jenis_barang' :
		$tipe = $_GET['tipe'];
		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'namajenisbarang';
		$status = isset($_GET['status']) && $_GET['status']=='all' ? '' : 'and status=1';

		$rows = array();
		$sql = "select kodejenisbarang as kode, namajenisbarang as nama
				from mjenisbarang
				order by $sort $order";
		$st = $db->prepare($sql);
		$query = $db->execute($st, array($q, $q, $tipe));
		while ($rs = $db->fetch($query)) {
			$rows[] = $rs;
		}
		echo json_encode($rows);
	break;
	
	case 'perkiraan' :
		$tipe = $_GET['tipe'];
		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'nama';
		$status = isset($_GET['status']) && $_GET['status']=='all' ? '' : 'and status=1';

		$rows = array();
		$sql = "select kode, namaperkiraan
				from mperkiraan
				order by $sort $order";
		$st = $db->prepare($sql);
		$query = $db->execute($st, array($q, $q, $tipe));
		while ($rs = $db->fetch($query)) {
			$rows[] = $rs;
		}
		echo json_encode($rows);
	break;
	
	case 'promo_menu' :
		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'nama';
		$status = isset($_GET['status']) && $_GET['status']=='all' ? '' : 'and status=1';

		$rows = array();
		$sql = "select kode,nama
				from mmenurecipe
				where (kode like '%'||?||'%' or nama like '%'||?||'%')
				order by $sort $order";
		$st = $db->prepare($sql);
		$query = $db->execute($st, array($q, $q, $tipe));
		while ($rs = $db->fetch($query)) {
			$rows[] = $rs;
		}
		echo json_encode($rows);
	break;
	
	case 'resep_menu_resep' :
		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'nama';
		$status = isset($_GET['status']) && $_GET['status']=='all' ? '' : 'and status=1';

		$rows = array();
		$sql = "select a.kode as kode, a.nama as nama,a.grandtotal as grandtotal,
				b.kodesatuan as satuan,b.satuan2 as satuan2,b.konversi as konversi
				from mmenurecipe a
				left join msatuan b on a.satuan = b.kodesatuan
				where (a.kode like '%'||?||'%' or a.nama like '%'||?||'%')
				order by $sort $order";
		$st = $db->prepare($sql);
		$query = $db->execute($st, array($q, $q, $tipe));
		while ($rs = $db->fetch($query)) {
			$rows[] = $rs;
		}
		echo json_encode($rows);
	break;
	
	case 'departemen_menu' :
		$tipe = $_GET['tipe'];
		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'namadepartemenmenu';
		$status = isset($_GET['status']) && $_GET['status']=='all' ? '' : 'and status=1';

		$rows = array();
		$sql = "select kodedepartemenmenu, namadepartemenmenu
				from mdepartemenmenu
				order by $sort $order";
		$st = $db->prepare($sql);
		$query = $db->execute($st, array($q, $q, $tipe));
		while ($rs = $db->fetch($query)) {
			$rows[] = $rs;
		}
		echo json_encode($rows);
	break;
	case 'brand_menu' :
		$tipe = $_GET['tipe'];
		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'nama';
		$status = isset($_GET['status']) && $_GET['status']=='all' ? '' : 'and status=1';

		$rows = array();
		$sql = "select kode, nama
				from mbrand
				order by $sort $order";
		$st = $db->prepare($sql);
		$query = $db->execute($st, array($q, $q, $tipe));
		while ($rs = $db->fetch($query)) {
			$rows[] = $rs;
		}
		echo json_encode($rows);
	break;
	case 'printer_menu' :
		$tipe = $_GET['tipe'];
		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'nama';
		$status = isset($_GET['status']) && $_GET['status']=='all' ? '' : 'and status=1';

		$rows = array();
		$sql = "select kode, namaprinter
				from mprinter
				order by $sort $order";
		$st = $db->prepare($sql);
		$query = $db->execute($st, array($q, $q, $tipe));
		while ($rs = $db->fetch($query)) {
			$rows[] = $rs;
		}
		echo json_encode($rows);
	break;
	case 'perkiraan_menu' :
		$tipe = $_GET['tipe'];
		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'namaperkiraan';
		$status = isset($_GET['status']) && $_GET['status']=='all' ? '' : 'and status=1';

		$rows = array();
		$sql = "select kode, namaperkiraan
				from mperkiraan
				order by $sort $order";
		$st = $db->prepare($sql);
		$query = $db->execute($st, array($q, $q, $tipe));
		while ($rs = $db->fetch($query)) {
			$rows[] = $rs;
		}
		echo json_encode($rows);
	break;
	case 'printer' :
		$tipe = $_GET['tipe'];
		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'nama';
		$status = isset($_GET['status']) && $_GET['status']=='all' ? '' : 'and status=1';

		$rows = array();
		$sql = "select kode, nama
				from mprinter
				order by $sort $order";
		$st = $db->prepare($sql);
		$query = $db->execute($st, array($q, $q, $tipe));
		while ($rs = $db->fetch($query)) {
			$rows[] = $rs;
		}
		echo json_encode($rows);
	break;
	case 'kategori_supplier' :
		$tipe = $_GET['tipe'];
		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'namasupplier';
		$status = isset($_GET['status']) && $_GET['status']=='all' ? '' : 'and status=1';

		$rows = array();
		$sql = "select kodekategorisupplier, namakategorisupplier
				from mkategorisupplier
				order by $sort $order";
		$st = $db->prepare($sql);
		$query = $db->execute($st, array($q, $q, $tipe));
		while ($rs = $db->fetch($query)) {
			$rows[] = $rs;
		}
		echo json_encode($rows);
	break;
	case 'nama_printer' :
		$printer = printer_list(PRINTER_ENUM_SHARE |PRINTER_ENUM_NAME);
		$rows = array();
		foreach($printer as $item){
			$rows[] = $item;
		}
		echo json_encode($rows);
	break;
	case 'data_printer' :
		$tipe = $_GET['tipe'];
		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'namaprinter';
		$status = isset($_GET['status']) && $_GET['status']=='all' ? '' : 'and status=1';

		$rows = array();
		$sql = "select kode, namaprinter,nama,namakomputer
				from mprinter
				order by $sort $order";
		$st = $db->prepare($sql);
		$query = $db->execute($st, array($q, $q, $tipe));
		while ($rs = $db->fetch($query)) {
			$rows[] = $rs;
		}
		echo json_encode($rows);
	break;
}
?>
