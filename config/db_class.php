<?php

class DB {
	private $db = '';
	private $con;
	
	function connect($db, $user, $pass) {
		$this->db = $db;
		$this->con = ibase_connect($db, $user, $pass) or die(json_encode(array('errorMsg' => 'Tidak tersambung dengan server')));
	}
	function get_db(){
		return $this->db;
	}
	
	function close() {
		ibase_close($this->con);
	}
	function start_trans() {
		return ibase_trans($this->con);
	}
	
	function commit($tr) {
		ibase_commit($tr);
		unset($tr);
		return NULL;
	}
	
	function rollback($tr) {
		ibase_rollback($tr);
		unset($tr);
		return NULL;
	}
	
	function cek_trans($tr) {
		return $tr ? 'transaction' : '';
	}
	
	function query($sql, $tr = false) {
		$trans = $this->cek_trans($tr);
		$st = $trans=='transaction' ? ibase_query($tr, $sql) : ibase_query($this->con, $sql);
		if ($st) {
			return $st;
		} else {
			if (DEBUG) die($this->db.' Cek SQLnya : '. $sql);
		}
	}
	function prepare ($sql, $tr = false) {
		$trans = $this->cek_trans($tr);
		$st = $trans=='transaction' ? ibase_prepare($tr, $sql) : ibase_prepare($this->con, $sql);
		if ($st) {
			return $st;
		} else {
			if (DEBUG) die($this->db.' Cek SQLnya : '. $sql);
		}
	}
	function execute ($stmt, $data) {
		if(!is_array($data))
			return ibase_execute($stmt,$data);
		
		// pikirkan fungsi ini
		//$data = array_filter($data);
		
		array_unshift($data,$stmt);
		
		return call_user_func_array('ibase_execute',$data);
	}
	
	function fetch($query) {
		return ibase_fetch_object($query);
	}
	
	/*
	contoh :
	$db->select(
		$tr, 
		'mbarang', 
		array('KODEBARANG', 'NAMABARANG', 'STATUS'), 
		array('KODEBARANG' => 'BRG01'), 
		array('KODEBARANG' => 'ASC', 'NAMABARANG' => 'DESC'), 
		false
	);
	*/
	
	function select($table, $data_field = array(), $data_clause = array(), $data_sort = array(), $tr = false, $build_sql = false) {
		$sql = 'select ';
		
		if (count($data_field)>0) {
			$sql .= implode(', ', $data_field);
		} else {
			$sql .= '*';
		}

		$sql .= ' from '.$table;
		
		if (count($data_clause)>0) {
			$sql .= ' where 1=1 and '. implode('=? and ', array_keys($data_clause)).'=? ';
		}

		if (count($data_sort)) {
			$sql .= ' order by ';
			$temp_sql = '';
			foreach ($data_sort as $key => $value) {
				$temp_sql .= ','.$key.' '.$value;
			}
			$sql .= substr($temp_sql, 1).'';
		}
		if ($build_sql)
			return $sql;
		else
			return $this->execute($this->prepare($sql, $tr), array_values($data_clause));
	}
	
	/*
	Variabel build_sql ini berisi true and false
	True  : Maka fungsi insert hanya mengembalikan script SQL. 
		    Biasanya digunakan ketika insert detail sebuah tabel karena dalam satu prepare bisa dilakukan banyak execute
			Dan variabel data_values berisi banyaknya field dalam suatu tabel
	False : Sebaliknya, script SQL langsung diexecuse dengan memanggil fungsi prepare lalu di execute.
			Variabel data_values berisi data array yang akan diinputkan, datanya harus berurutan dengan field di tabel
	
	contoh :
	$db->insert($tr, 
				'tkasdtl', 
				8, 
				true);
	
	$db->insert($tr, 
				'muser', 
				array('USERNAME', 'PASSWORD', 'EMAIL'), 
				false);
	*/
	function insert($table, $data_values, $tr = false, $build_sql = false) {
		$i = 0;
		$ln = $build_sql ? $data_values : count($data_values);
		
		$sql = 'insert into '.$table.' values (';
		$temp_sql = '';
		while ($i<$ln) {
			$temp_sql .= ',?';
			$i++;
		}
		$sql .= substr($temp_sql, 1).');';
		
		if ($build_sql)
			return $sql;
		else
			return $this->execute($this->prepare($sql, $tr), $data_values);
	}
	
	/*
	contoh :	
	$db->update(
		$tr, 
		'mbarang', 
		array(
			'KODEBARANG' => 'BRG02', 
			'NAMABARANG' => 'BARANG B'
		), 
		array(
			'KODEBARANG' => 'BRG01'
		), 
		true
	);
	
	$db->update(
		$tr, 
		'mbarang', 
		array(
			'KODEBARANG' => 'BRG02', 
			'NAMABARANG' => 'BARANG B'
		), 
		array(
			'KODEBARANG' => 'BRG01'
		), 
		false
	);
	*/
	function update($table, $data_set = array(), $data_clause = array(), $tr = false, $build_sql = false) {
		$sql = 'update '.$table.' set ';
		
		$sql .= implode('=?, ', array_keys($data_set)).'=?';
		
		if (count($data_clause)>0) {
			$sql .= ' where 1=1 and '. implode('=? and ', array_keys($data_clause)).'=? ';
		}

		if ($build_sql)
			return $sql;
		else
			return $this->execute($this->prepare($sql, $tr), array_merge(array_values($data_set), array_values($data_clause)));
	}
	
	function delete($table, $data_clause = array(), $tr = false, $build_sql = false) {
		$sql = 'delete from '.$table;
		
		if (count($data_clause)>0) {
			$sql .= ' where 1=1 and '. implode('=? and ', array_keys($data_clause)).'=? ';
		}
		
		if ($build_sql)
			return $sql;
		else
			return $this->execute($this->prepare($sql, $tr), array_values($data_clause));
	}
}
?>