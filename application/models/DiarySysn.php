<?php

class Api_Model_DiarySysn extends Zend_Db_Table_Abstract
{
    protected $_name = 'ctp_diary_sysn';
    
    
    public function saveForStudent($student_id, $did)
	{
		  return $this->insert( array(
				'student_id'  			  => $student_id,
				'diary_sysn_time'	      => new Zend_Db_Expr('NOW()'),
				'diary_id'	              => $did,
				'diary_sysn_status'       => 'OFF'
			) );
	}
	
	public function saveForStudentBatch($student_id, $did)
	{
		foreach ( $student_id as $s )
		{
			$values[] = "($s[student_id], NOW(), $did, 'OFF')";
		}
		$value = implode(',', $values);
		$sql = "
		INSERT INTO $this->_name (student_id,diary_sysn_time,diary_id,diary_sysn_status)
		VALUES
		$value
		";
		//print $sql;
		Zend_Db_Table::getDefaultAdapter()->query($sql);
	}
    
    public function saveForTeacher($teacher_id, $did)
	{
		  return $this->insert( array(
				'teacher_id'  			  => $teacher_id,
				'diary_sysn_time'	      => new Zend_Db_Expr('NOW()'),
				'diary_id'	              => $did,
				'diary_sysn_status'       => 'OFF'
	     ) );
	}
    
    public function setONForStudentBatch($diary_id, $student_id)
	{
		$diary_id = implode(',', $diary_id);
		$sql = "
		UPDATE $this->_name SET diary_sysn_status='ON' WHERE student_id=$student_id AND diary_id IN( $diary_id)
		";
		
		Zend_Db_Table::getDefaultAdapter()->query($sql);
	}
    
    public function setONForTeacherBatch($diary_id, $teacher_id)
	{
		$diary_id = implode(',', $diary_id);
		$sql = "
		UPDATE $this->_name SET diary_sysn_status='ON' WHERE teacher_id=$teacher_id AND diary_id IN( $diary_id)
		";
		
		Zend_Db_Table::getDefaultAdapter()->query($sql);
	}
    
}