<?php

class Api_Model_StartClass extends Zend_Db_Table_Abstract
{
	protected $_name = 'ctp_start_class';
	
	public function save(array $data)
	{
		
		  return $this->insert( array(
				'class_session_id'  => $data['class_session_id'],
				'teacher_id'	    => $data['teacher_id'],
				'master_section_id' =>  $data['master_section_id'],
				'version'			=> 0,
				'date_created'		=> new Zend_Db_Expr('TIMESTAMPADD(MINUTE,1,NOW())'),
				'status'			=> $data['status']
			) );
		
	}
	
	
	/*public function checkTime($master_section_id, $class_session_id)
	{
		$db = Zend_Db_Table::getDefaultAdapter(); 
		
		$q = $db->query("SELECT * FROM  ".$this->_name." 
						WHERE 
						master_section_id= $master_section_id
						AND 
						class_session_id = $class_session_id
						AND 
						DATE_SUB(NOW(), INTERVAL 1 HOUR) < DATE_ADD(date_created, INTERVAL 1 HOUR)
						");
						
		$row = $q->fetchAll();
	
		
		return ( count($row)) ? true: false;
	}
	*/
	public function stopClass($class_session_id)
	{
		$this->update( array('status' => 'STOP'), 
						array(
							'class_session_id=?' => $class_session_id,
							'status=?' => 'START'
							) );
	}
	public function getByClassSessionIdStart($class_session_id)
	{
		$row = $this->fetchAll(
			$this->select()
			->where('class_session_id=?', $class_session_id)
			->where('status=?', 'START')
			->limit(1, 0)
		);
		
	  return ( count($row) )? true:false;
	}
	
	public function getByClassSessionIdStop($class_session_id)
	{
		$row = $this->fetchAll(
			$this->select()
			->where('class_session_id=?', $class_session_id)
			->where('status=?', 'STOP')
			->limit(1, 0)
		);
		
	  return ( count($row) )? true:false;
	}
	
	
	public function updateRow(array $data)
	{
		  $this->update( array(
				
				'status'			=> $data['status'],
				
			), array(
				'class_session_id=?'=> $data['class_session_id']
			));
		
	}
	
	public function getLatest($class_session_id)
	{
		$row = $this->fetchRow(
			$this->select($this, array('status') )
			->where('class_session_id=?', $class_session_id)
			->order('date_created desc')
			->limit(1, 0)
		);
		
	  return ( count($row) )? $row['status'] :false;
	}
	
	public function getLatestByMASTER_SECTION_ID($master_section_id)
	{
		$row = $this->fetchRow(
			$this->select($this, array('status', 'class_session_id') )
			->where('master_section_id=?', $master_section_id)
			->order('date_created desc')
			->limit(1, 0)
		);
		
	  return ( count($row) )? $row : array();
	}
}