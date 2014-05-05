<?php

class Api_Model_ActivityLog extends Zend_Db_Table_Abstract
{
	protected $_name = 'ctp_activity_log';
	
	public function save(array $data)
	{
		return $this->insert( array(
			'user_id'       => (int)$data['user_id'],
			'event_log_id'  => (int)$data['eventMaster_id'],
			'start_time'	=> date('Y-m-d h:i:s', strtotime($data['start_time'])),
			'end_time'		=> $data['end_time'],
			'school_id'		=> (int)$data['school_id'],
			'mode'			=> $data['mode']
		));
	}
	
	public function updateEndTime($end_time, $id)
	{
		$this->update( array(
			'end_time' => $end_time,
			'version' => 1
		), array('id=?' => $id));
	}
}