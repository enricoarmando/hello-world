<form method="post">
	<textarea name="sql" style="width:400px; height:100px"></textarea>
	<input type="submit" value="query">
</form>
<style>
.HEADER {
	font-family: Tahoma, Verdana, Geneva, sans-serif;
	font-weight: bold;
	font-size: 18px;
	color: #000;
	text-align:left;
}
.HEADERPERIODE {
	font-family: Tahoma, arial, sans-serif, Verdana, Geneva ;
	font-weight: bold;
	font-size: 16px;
	color: #000;
	text-align:left;
}
#tabelket{
	font-family:Tahoma, Verdana, Geneva, sans-serif;
	font-size:10px;
	font-weight:bold;
	padding: 2px;
}
.det{
	font-family:Tahoma, Verdana, Geneva, sans-serif;
	font-size:10px;
	padding: 2px;
}
</style>
<?php
include "Koneksi.php";
//$db = new DB;

$sql = $_POST['sql'];
if (strlen($sql)>5) {
	$query = $db->query($sql);
	if (stripos($sql, 'delete')<=0 or stripos($sql, 'update')<=0) {
		$header = true;
		echo '<table width="100%" style="border-collapse:collapse" border="1">';
		while ($rs = $db->fetch($query)) {
			if ($header) {
				echo '<tr>';
				echo '<td id="tabelket" align="center">'.implode('</td> <td id="tabelket" align="center">', array_keys((array) $rs)).'</td>';
				echo '</tr>';
				$header = false;
			}
			echo '<tr>';
			echo '<td class="det">'.implode('</td> <td class="det">', array_values((array)  $rs)).'</td>';
			echo '</tr>';
		}
	}
}
?>

