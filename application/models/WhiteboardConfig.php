<?php

class Api_Model_WhiteboardConfig extends Zend_Db_Table_Abstract
{
	protected $_name = 'ctp_whiteboard_config';
	
	public function get($whiteboard_config_id)
	{
		$row = $this->fetchAll(
			$this->select()
			->where('whiteboard_config_id=?', $whiteboard_config_id)
			->limit(1, 0)
		);
		
		return $row;
	}
	public function getId($school_id, $class_id, $section_id)
	{
		$row = $this->fetchRow(
			$this->select()
			->where('school_id=?', $school_id)
			->where('class_id=?', $class_id)
			->where('section_id=?', $section_id)
			->limit(1, 0)
		);
		
		return ( sizeof($row)) ? $row->whiteboard_config_id: 0;
	}
	public function getByIp( $whiteboard_config_ip, $school_id)
	{
			 $select = $this->select()->setIntegrityCheck(false);
			 $select->from($this, array('class_id', 'section_id'
			 				) )
					->join('ctp_whiteboard_session', 
					'ctp_whiteboard_session.whiteboard_config_id = ctp_whiteboard_config.whiteboard_config_id')
					->where('ctp_whiteboard_config.whiteboard_config_ip=?', $whiteboard_config_ip)
					->where('ctp_whiteboard_session.status=?', 'ON')
					->where('ctp_whiteboard_config.school_id=?', $school_id);
					
		return $this->fetchRow($select);
	}
	
	public function getByIpSingle( $whiteboard_config_ip, $school_id)
	{
		$sql = "
		SELECT whiteboard_config_id FROM $this->_name WHERE whiteboard_config_ip='$whiteboard_config_ip' 
		AND school_id=$school_id LIMIT 1
		";
		
		return Zend_Db_Table::getDefaultAdapter()->query($sql)->fetch()['whiteboard_config_id'];
	}
	
	
}