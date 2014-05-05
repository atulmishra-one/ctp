<?php

class Api_Model_GetClass extends Zend_Db_Table_Abstract
{
	protected $_name = 'salaah_class';
	
	public function getName($school_id, $class_id)
	{
		$row = $this->fetchRow(
			$this->select()
			->where('class_auto_id=?', $class_id)
			->where('school_auto_id=?', $school_id)
			->where('class_status=?', 'Active')
			->limit(1, 0)
		);
		
		return ( count( $row) ) ? $row->class_name:'';
	}
}