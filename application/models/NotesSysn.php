<?php
class Api_Model_NotesSysn extends Zend_Db_Table_Abstract
{
    protected $_name = 'ctp_notes_sysn';
    
    public function saveForStudent($student_id, $notes_id)
	{
		  return $this->insert( array(
				'student_id'  			  => $student_id,
				'notes_sysn_time'	      => new Zend_Db_Expr('NOW()'),
				'notes_id'	              => $notes_id,
				'notes_sysn_status'       => 'OFF'
			) );
	}
	
	public function saveForStudentBatch($value)
	{
		  $value = implode(',' , $value );
		  $sql = "
		  INSERT INTO $this->_name (student_id, notes_sysn_time, notes_id, notes_sysn_status)
		  VALUES
		  $value
		  ";
		  
		  Zend_Db_Table::getDefaultAdapter()->query($sql);
	}
    
    public function saveForTeacher($teacher_id, $notes_id)
	{
		  return $this->insert( array(
				'teacher_id'  			  => $teacher_id,
				'notes_sysn_time'	      => new Zend_Db_Expr('NOW()'),
				'notes_id'	              => $notes_id,
				'notes_sysn_status'       => 'OFF'
	     ) );
	}
    
	public function saveForTeacherBatch($value)
	{
		$value = implode(',', $value);
		$sql = "
		INSERT INTO $this->_name (teacher_id, notes_sysn_time, notes_id, notes_sysn_status)
		VALUES
		$value
		";
		Zend_Db_Table::getDefaultAdapter()->query($sql);
	}
	
    public function setONForStudentBatch($notes_id, $student_id)
	{
		$notes_id = implode(',', $notes_id);
		
		$sql = "
		UPDATE $this->_name SET notes_sysn_status='ON' WHERE student_id=$student_id AND notes_id IN( $notes_id) 
		";
		
		Zend_Db_Table::getDefaultAdapter()->query($sql);
	}
    
    public function setONForTeacherBatch($notes_id, $teacher_id)
	{
		$notes_id = implode(',', $notes_id);
		
		$sql = "
		UPDATE $this->_name SET notes_sysn_status='ON' WHERE teacher_id=$teacher_id AND notes_id IN( $notes_id) 
		";
		
		Zend_Db_Table::getDefaultAdapter()->query($sql);
	}
}