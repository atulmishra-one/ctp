<?php

class Api_Model_AssessmentNotification extends Zend_Db_Table_Abstract
{
	protected $_name = 'ctp_assessment_notifications';

	
	public function getById($notification_id)
	{
		$row = $this->fetchRow(
			$this->select()
			->where('notification_id=?', $notification_id)
			->order('id desc')
			->limit(1, 0)
		);
		
		return ( count( $row) ) ? $row: array();
	}
}