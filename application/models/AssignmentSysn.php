<?php

class Api_Model_AssignmentSysn extends Zend_Db_Table_Abstract
{
	protected $_name = 'ctp_assignment_sysn';
	
	public function save(array $data)
	{
		  return $this->insert( array(
				'student_id'  			 => $data['student_id'],
				'assignment_sysn_time'	 => new Zend_Db_Expr('NOW()'),
				'assignment_master_id'	 => $data['assignment_master_id'],
				'assignment_sysn_status' => 'OFF'
			) );
	}
        
        public function saveForTeacher($teacher_id, $assignment_id)
	{
		  return $this->insert( array(
				'teacher_id'  		 => $teacher_id,
				'assignment_sysn_time'	 => new Zend_Db_Expr('NOW()'),
				'assignment_master_id'	 => $assignment_id,
				'assignment_sysn_status' => 'OFF'
			) );
	}
	
	public function get($assignement_master_id)
	{
	 return $this->fetchAll(
			$this->select()
			->where('assignment_master_id=?', $assignement_master_id)
		);
	}
	
	public function setON($assignement_master_id, $student_id)
	{
		return $this->update( array(
		'assignment_sysn_status' => 'ON'
		), 
			array('student_id=?' => $student_id, 'assignment_master_id=?' => $assignement_master_id) 
		);
	}
	
	public function setONStudentBatch($assignment_master_id, $student_id)
	{
		$assignment_master_id = implode(',', $assignment_master_id);
		$sql = "
		UPDATE $this->_name SET assignment_sysn_status='ON' WHERE
		student_id=$student_id AND assignment_master_id IN ($assignment_master_id)
		";
		
		Zend_Db_Table::getDefaultAdapter()->query($sql);
	}

   	public function setONTeacherBatch($assignment_master_id, $teacher_id)
	{
		$assignment_master_id = implode(',', $assignment_master_id);
		$sql = "
		UPDATE $this->_name SET assignment_sysn_status='ON' WHERE
		teacher_id=$teacher_id AND assignment_master_id IN ($assignment_master_id)
		";
		
		Zend_Db_Table::getDefaultAdapter()->query($sql);
	}
	
        public function setONTeacher($assignement_master_id, $teacher_id)
	{
		return $this->update( array(
		'assignment_sysn_status' => 'ON'
		), 
			array('teacher_id=?' => $teacher_id, 'assignment_master_id=?' => $assignement_master_id) 
		);
	}

	public function setOFFTeacher($assignement_master_id, $teacher_id)
	{
		return $this->update( array(
		'assignment_sysn_status' => 'OFF'
		), 
			array('teacher_id=?' => $teacher_id, 'assignment_master_id=?' => $assignement_master_id) 
		);
	}
	
	public function Recieved($assignement_master_id)
	{
		$row = $this->fetchAll(
			$this->select()
			->where('assignment_master_id=?', $assignement_master_id)
			->where('assignment_sysn_status=?', 'OFF')
			->limit(1, 0)
		);
		
			
		return (count($row) )? 1: 0;
	}
	
	public function RecievedStudent($assignment_master_id, $student_id)
	{
		$row = $this->fetchAll(
			$this->select()
			->where('assignment_master_id=?', $assignment_master_id)
			->where('student_id=?', $student_id)
			->limit(1, 0)
		);
		
			
		return (count($row) )? $row[0]->assignment_sysn_status: 'NO';
	}
	
}

