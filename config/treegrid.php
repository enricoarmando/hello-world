<?php
session_start();
include "koneksi.php";
include "function.php";

$table = $_REQUEST['table'];

//$db = new DB;

switch ($table) {
	case 'hak_akses' :
		$kode = $_POST['kode'];
		
		// jika browse ada user id
		$sqlx = "select * from (
					 select b.hakakses, b.tambah, b.ubah, b.hapus, b.kodemenu, c.namamenu, c.tipe, c.urutantipe, c.urutan
					 from muser a inner join muserakses b on a.userid=b.userid
					 inner join mmenu c on b.kodemenu = c.kodemenu
					 where a.userid = '$kode'
					 
					 union all
					 
					 select 0 as hakakses, 0 as tambah, 0 as ubah, 0 as hapus, kodemenu, namamenu, tipe, urutantipe, urutan
					 from mmenu where kodemenu not in (select kodemenu from muserakses where userid='$kode')
				 ) order by urutantipe, urutan asc";
		
		// jika browse tidak ada user id
		$sqly = "select kodemenu, namamenu, tipe 
				 from mmenu
				 order by urutantipe, urutan asc";
		
		$query = $db->query(strlen(trim($kode))>0 ? $sqlx : $sqly);
		
		$temp_tipe = '';
		$a_header  = array();
		$a_detail  = array();
		
		while ($rs = $db->fetch($query)) {
			$tipe = str_replace("&","and",$rs->TIPE);
			if ($temp_tipe<>$tipe) {
				$temp_tipe = $tipe;
				
				$a_header[] = array(
					'id' => $tipe,
					'menu' => str_replace("&","and",$rs->TIPE),
					'children' => array(),
				);
			}
			
			if ($rs->NAMAMENU<>'-') {
				$a_detail[] = array(
					'id' => $rs->KODEMENU,
					'menu' => str_replace("&","and",$rs->NAMAMENU),
					'tipe' => str_replace("&","and",$rs->TIPE),
					'hakakses' => strlen(trim($kode))>0 ? $rs->HAKAKSES : 0,
					'tambah' => strlen(trim($kode))>0 ? $rs->TAMBAH : 0,
					'ubah' => strlen(trim($kode))>0 ? $rs->UBAH : 0,
					'hapus' => strlen(trim($kode))>0 ? $rs->HAPUS : 0,
				);
			}
		}
		
		foreach (json_decode(json_encode($a_header)) as $item) {					
			foreach (json_decode(json_encode($a_detail)) as $i) {						
				if ($item->menu==$i->tipe) {
					$item->children[] = $i;
				}
			}
			
			$a_temp[] = $item;
		}
		echo json_encode($a_temp);
	break;
	
	case 'kode_perkiraan' :
		$temp_data = search_data($db, '', 0);
		echo json_encode($temp_data);		
	break;
}
function search_data($db, $parent='', $i=0) {
	$sql = "select kodeperkiraan, namaperkiraan from mperkiraan where induk='$parent' order by kodeperkiraan";
	$query = $db->query($sql);
	$i++;
	$a_data = array();
	while ($rs = $db->fetch($query)) {
		$a_data[] = array(
			'id' => $rs->KODEPERKIRAAN,
			'text' => '&nbsp;&nbsp;'.$rs->KODEPERKIRAAN . ' - ' .$rs->NAMAPERKIRAAN,
			//'state' => 'closed',
			'children' => search_data($db, $rs->KODEPERKIRAAN, $i),
		);
	}
	return $a_data;
}
?>