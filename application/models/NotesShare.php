<?php

class Api_Model_NotesShare extends Zend_Db_Table_Abstract
{
	protected $_name = 'ctp_notes_share';
	
	public function saveForTeacher($notes_id, $teacher_id)
	{
	
		 	return $this->insert( array(
				'notes_id'	    	 => $notes_id,
				'teacher_id'         => $teacher_id,
				'notes_share_time'   => new Zend_Db_Expr('NOW()')
			) );
	}
	
	public function saveForTeacherBatch($value)
	{
		$value = implode(',', $value);
		$sql = "
		INSERT INTO $this->_name (notes_id,teacher_id, notes_share_time)
		VALUES
		$value
		";
		
		Zend_Db_Table::getDefaultAdapter()->query($sql);
	}
    
    public function saveForStudent($notes_id, $student_id)
	{
	
		 	return $this->insert( array(
				'notes_id'	    	 => $notes_id,
				'student_id'         => $student_id,
				'notes_share_time'   => new Zend_Db_Expr('NOW()')
			) );
	}
	
	public function saveForStudentBatch($value)
	{
		$value = implode(',' , $value);
		$sql = "
		INSERT INTO $this->_name (notes_id, student_id, notes_share_time)
		VALUES
		$value
		";
		//print $sql;
		Zend_Db_Table::getDefaultAdapter()->query($sql);
	}
	
	public function getById($notes_id)
	{
		return $this->fetchAll(
			$this->select()
			->where('notes_id=?', $notes_id)
		);
	}
    
    public function getPostedToTeacher($notes_id)
    {
        return $this->fetchAll(
			$this->select()
			->where('notes_id=?', $notes_id)
            ->where('teacher_id!=?', 0)
		);
    }
    
    public function getPostedToStudent($notes_id)
    {
        return $this->fetchAll(
			$this->select()
			->where('notes_id=?', $notes_id)
            ->where('student_id!=?', 0)
		);
    }
	

	
	
	

	



}











