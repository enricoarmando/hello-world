<?php
class html_table {

	private $tbl_str = '';
	private $tbl_width = 0;
	private $tbl_tr = '';
	private $tbl_td = '';
	private $hr;
	private $hr_str = '';
	private $tbl_colspan = 0;
	private $border = 1;
	
	function set_border($val = 1) {
		$this->border = $val;
	}
	
	function set_hr($val = false) {
		$this->hr = $val;
	}
	
	function set_th ($data_td = array()) {
		$this->tbl_width = 0;
		$count_td = count($data_td);
		$temp_count = 0;
		foreach ($data_td as $item) {
			foreach ($item as $row => $values) {
				if ($row=='colspan')
					$temp_count += $values-1;
			
				if ($row=='width') {
					$this->tbl_width += $values;
					break;
				}
			}
		}
		$this->tbl_colspan = $count_td + $temp_count;
		
		$this->set_td($data_td);
	}
	
	function set_td ($data_td = array()) {
		foreach ($data_td as $item) {
			$prop_td = '';
			$value_td = '';
			
			foreach ($item as $row => $values) {
				if ($row!='' and $row!='values')
					$prop_td .= $row.'=\''.$values.'\' ';

				if ($row=='values')  {
					$value_td .= $values;
				}
			}
			
			$this->tbl_td .= '<td '.$prop_td.'>'.$value_td.'</td>';
		}
	}
	function set_tr ($data_tr = array()) {
		if ($this->tbl_tr!='') {
			$this->tbl_str .= $this->tbl_tr.$this->tbl_td.'</tr>';
			$this->tbl_tr = '';
			$this->tbl_td = '';
		}
		
		$this->tbl_tr .= '<tr ';
		foreach ($data_tr as $row => $values) {
			if ($row!='')
				$this->tbl_tr .= $row.'=\''.$values.'\' ';
		}
		$this->tbl_tr .= '>';
	}
	function line_break($colspan = 0) {
		$this->set_tr();
		$this->set_td(array(array('style'=>'border-right:#FFF 1px solid; border-left:#FFF 1px solid; height:15px', 'colspan'=>$this->tbl_colspan)));
	}
	
	function generate_table () {
		if ($this->tbl_tr!='') {
			$this->tbl_str .= $this->tbl_tr.$this->tbl_td.'</tr>';
			$this->tbl_tr = '';
			$this->tbl_td = '';
		}
		
		if ($this->hr) {
			$this->hr_str = '<hr style="border:0; background-color:black; height:1px; width:'.$this->tbl_width.'px" align="left"/>';
		}
		
		return $this->hr_str.'<table style="width:'.$this->tbl_width.'px; border-collapse:collapse;" border="'.$this->border.'">'.$this->tbl_str.'</table>';		
	}
}
/*
$tbl = new html_table();

$tbl->set_tr();
$tbl->set_th(array(
	array('align'=>'left', 'width'=>'50', 'values'=>'A1'),
	array('align'=>'center', 'width'=>'80', 'values'=>'A2'),
	array('align'=>'right', 'width'=>'100', 'values'=>'A3'),
	array('align'=>'right', 'width'=>'100', 'values'=>'A4'),
	array('align'=>'right', 'width'=>'100', 'values'=>'A5')
));

$tbl->set_tr();
$tbl->set_td(array(
	array('align'=>'left', 'rowspan'=>'2', 'values'=>'1'),
	array('align'=>'center', 'values'=>'2'),
	array('align'=>'right', 'values'=>'3'),
	array('align'=>'right', 'values'=>'3'),
	array('align'=>'right', 'values'=>'3')
));

$tbl->set_tr();
$tbl->set_td(array(
	array('align'=>'center', 'values'=>'2'),
	array('align'=>'right', 'values'=>'3'),
	array('align'=>'right', 'values'=>'3'),
	array('align'=>'right', 'values'=>'3')
));

$tbl->set_tr();
$tbl->set_td(array(
	array('align'=>'left', 'values'=>'1'),
	array('align'=>'center', 'values'=>'2'),
	array('align'=>'right', 'values'=>'3'),
	array('align'=>'right', 'values'=>'3'),
	array('align'=>'right', 'values'=>'3')
));

$tbl->set_tr();
$tbl->set_td(array(
	array('align'=>'right', 'colspan'=>'2', 'values'=>'Total'),
	array('align'=>'right', 'values'=>'3'),
	array('align'=>'right', 'values'=>'3'),
	array('align'=>'right', 'values'=>'3')
));

$tbl->line_break(5);

$tbl->set_tr();
$tbl->set_td(array(
	array('align'=>'left', 'values'=>'1'),
	array('align'=>'center', 'values'=>'2'),
	array('align'=>'right', 'values'=>'3'),
	array('align'=>'right', 'values'=>'3'),
	array('align'=>'right', 'values'=>'3')
));

	// buat table detail
	$tbl_dtl = new html_table();

	$tbl_dtl->set_tr();
	$tbl_dtl->set_th(array(
		array('align'=>'left', 'width'=>'100', 'values'=>'B1'),
		array('align'=>'center', 'width'=>'100', 'values'=>'B2'),
		array('align'=>'right', 'width'=>'100', 'values'=>'B3')
	));

	$tbl_dtl->set_tr();
	$tbl_dtl->set_td(array(
		array('align'=>'left', 'values'=>'10'),
		array('align'=>'center', 'values'=>'11'),
		array('align'=>'right', 'values'=>'12')
	));

$tbl->set_tr();
$tbl->set_td(array(
	array('align'=>'left', 'colspan'=>'5', 'values'=>$tbl_dtl->generate_table())
));

$tbl->set_tr();
$tbl->set_td(array(
	array('align'=>'left', 'values'=>'1'),
	array('align'=>'center', 'values'=>'2'),
	array('align'=>'right', 'values'=>'3'),
	array('align'=>'right', 'values'=>'3'),
	array('align'=>'right', 'values'=>'3')
));

echo $tbl->generate_table();
*/
?>