<?php

class Api_Model_WhiteboardSession extends Zend_Db_Table_Abstract
{
	protected $_name = 'ctp_whiteboard_session';
	
	public function get($whiteboard_config_id)
	{
		$row = $this->fetchAll(
			$this->select()
			->where('whiteboard_config_id=?', $whiteboard_config_id)
			->limit(1, 0)
		);
		
		return $row;
	}
	
	public function updateByWcAndTeacher($whiteboard_config_id, $teacher_id)
	{
		//if( ! $this->checkByWcAndTeacher($whiteboard_config_id, $teacher_id) )
		// Implement by Amar in absence of Atul
		if(($this->checkByWcAndTeacher($whiteboard_config_id, $teacher_id))=="")
		{
			$this->save( array(
				'whiteboard_config_id' => $whiteboard_config_id,
				'teacher_id'		   => $teacher_id,
                'status'	           => 'ON'
			));
		}
		else {
		// // Implement by Amar in absence of Atul i.e. 'end_time' => '0000-00-00 00:00:00.000000'
			$this->update( array(
				'start_time' => new Zend_Db_Expr('NOW()'),
 				'end_time' => '0000-00-00 00:00:00.000000',
				'status'	 => 'ON'
			), 
			array('whiteboard_config_id=?' => $whiteboard_config_id, 'teacher_id=?' => $teacher_id) );
		}
	}
	
	public function checkByWcAndTeacher($whiteboard_config_id, $teacher_id)
	{
		/*$row = $this->fetchRow(
			$this->select()
			->where('whiteboard_config_id=?', $whiteboard_config_id)
			->where('teacher_id=?', $teacher_id)
			->limit(1, 0)
		);
		
		return ( count( $row) )? true: false;
		*/
		$sql = "
		SELECT whiteboard_config_id FROM $this->_name WHERE
		whiteboard_config_id=$whiteboard_config_id
		AND
		teacher_id=$teacher_id
		LIMIT 1
		";
		
		$row = Zend_Db_Table::getDefaultAdapter()->query($sql)->fetch();
		return $row['whiteboard_config_id'];
	}
	
	public function getTeacherId($whiteboard_config_id)
	{
		$row = $this->fetchRow(
			$this->select()
			->where('whiteboard_config_id=?', $whiteboard_config_id)
			->where('status=?', 'ON')
			->limit(1, 0)
		);
		
		return ( count( $row) )? $row->teacher_id: 0;
	}
	
	public function isOnStatus($whiteboard_config_id, $teacher_id)
	{
		$row = $this->fetchRow(
			$this->select()
			->where('whiteboard_config_id=?', $whiteboard_config_id)
			->where('teacher_id=?', $teacher_id)
			->where('status=?', 'ON')
			->limit(1, 0)
		);
		
		return ( count( $row) )? true: false;
	}
	
	public function isConfigOn($whiteboard_config_id)
	{
		$row = $this->fetchRow(
			$this->select()
			->where('whiteboard_config_id=?', $whiteboard_config_id)
			->where('status=?', 'ON')
			->limit(1, 0)
		);
		
		return ( count( $row) )? true: false;
	}
	
	public function isTeacherOn($teacher_id)
	{
		$row = $this->fetchRow(
			$this->select()
			->where('teacher_id=?', $teacher_id)
			->where('status=?', 'ON')
			->limit(1, 0)
		);
		
		return ( count( $row) )? true: false;
	}
	
	public function save(array $data)
	{
		$this->insert( array(
			'whiteboard_config_id' => $data['whiteboard_config_id'],
			'teacher_id'           => $data['teacher_id'],
			'start_time'		   => new Zend_Db_Expr('NOW()'),
			'STATUS'			   => 'ON'
		));
	}
	
	public function stopWhiteBoard($whiteboard_config_id, $teacher_id)
	{
		$this->update( array('status' => 'OFF', 'end_time' => new Zend_Db_Expr('NOW()')), 
						array(
							'whiteboard_config_id=?' => $whiteboard_config_id,
							'teacher_id'			 => $teacher_id,
							'status=?' => 'ON'
							) 
						);
	}
	
	public function updateStartTimeAndStatus($whiteboardConfigId, $teacher_id, $status)
	{
		 return $this->update( array(
			'start_time' => new Zend_Db_Expr('NOW()'),
			'status' => $status
		) , array('whiteboard_config_id=?'	=> $whiteboardConfigId, 'teacher_id=?' => $teacher_id )
		);
	}
	
	public function getLastest($whiteboard_config_id)
	{
		$row = $this->fetchRow(
			$this->select()
			->where('whiteboard_config_id=?', $whiteboard_config_id)
			->where('status=?', 'ON')
			->order('start_time DESC')
			->limit(1, 0)
		);
		
		return ( count($row) )? 1 : 0;
	}
	
	public function getLastestIndex($whiteboard_config_id)
	{
		$sql = "
		SELECT whiteboard_session_id FROM $this->_name WHERE whiteboard_config_id=$whiteboard_config_id
		AND status='ON' ORDER BY start_time DESC
		";
		return sizeof( Zend_Db_Table::getDefaultAdapter()->query($sql)->fetchAll() ) ;
	}
}




