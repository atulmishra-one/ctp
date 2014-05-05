<?php

class Api_Model_SalaahClass extends Zend_Db_Table_Abstract
{
	protected $_name = 'salaah_class';
	
	public function get($class_auto_id)
	{
		$row = $this->fetchAll(
			$this->select()
			->where('class_auto_id=?', $class_auto_id)
			->limit(1, 0)
		);
		
		return $row;
	}
}