<?php

class Api_Model_GetSubject extends Zend_Db_Table_Abstract
{
	protected $_name = 'school_cce_subject';
	
	public function getName($subject_id)
	{
		$row = $this->fetchRow(
			$this->select()
			->where('sub_auto_id=?', $subject_id)
			->where('subject_status=?', 'Active')
			->limit(1, 0)
		);
		
		return ( count( $row) ) ? $row->subject_name: '';
	}
	
	public function getByClassId($class_id)
	{
		return $this->fetchAll(
			$this->select()
			->where('class_id=?', $class_id)
			->where('subject_status=?', 'Active')
		);
	}
}