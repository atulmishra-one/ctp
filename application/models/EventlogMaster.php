<?php

class Api_Model_EventlogMaster extends Zend_Db_Table_Abstract
{
	protected $_name = 'ctp_event_log_master';
	
	public function getIdByName($name)
	{
		$row = $this->fetchRow(
			$this->select()
			->where('event_name=?', $name)
			->limit(1, 0)
		);
		
		return ( count( $row) ) ? $row->id: 0;
	}
}