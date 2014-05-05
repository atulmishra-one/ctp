<?php

class Api_Model_NotificationType extends Zend_Db_Table_Abstract
{
	protected $_name = 'ctp_notification_type';
	
	public function get($type)
	{
		$row = $this->fetchRow(
			$this->select()
			->where('notification_type_type=?', $type)
			->limit(1, 0)
		);
		
		return ( count($row) ) ? $row->notification_type_id:0;
	}
    
    public function getPriority($type){
   	  
         $row = $this->fetchRow(
			$this->select()
			->where('notification_type_type=?', $type)
			->limit(1, 0)
		);
		
		return ( count($row) ) ? strtolower($row->priority): '';
    }
}